// Prepara los dos vídeos de la página de Halloween para la web y sus pósteres.
// Uso: node herramientas/preparar-videos-halloween.mjs
//
// Originales (no van a Git): FOTOS Y VIDEOS HALLOWEEN DRYICEPACK/IMG_3496.MOV y IMG_3531.MOV (iPhone 14, 1080p 30 fps).
// Salida: tema/dryicepack/assets/halloween/video/
//   como-hacer-niebla.mp4 (+ -poster.webp)  vertical 720×1280, 6 s
//     IMG_3496: mano con guante echa pellets de 3 mm en una calabaza vaciada y sin tapa; la niebla sale por la boca,
//     se extiende por la mesa y cae por el borde. Se corta en el segundo 6 (después se ve el suelo del almacén y cinta
//     adhesiva) y se funde a negro los últimos 0,4 s para que el bucle no dé un salto brusco.
//   calabazas-niebla.mp4 (+ -poster.webp)   horizontal 1280×720, 8 s
//     IMG_3531: plano fijo de tres calabazas iluminadas con niebla a ras de mesa. Bucle sin costura: el último segundo
//     se funde con el primero (8 s de 9).
// Las dos: H.264 perfil main, yuv420p, +faststart, sin audio y sin metadatos (los originales llevan la ubicación GPS).
// El póster es el primer fotograma del propio MP4: al empezar a reproducirse no hay salto.
import { createRequire } from 'node:module';
import { execFileSync } from 'node:child_process';
import { mkdirSync, statSync, rmSync } from 'node:fs';
import { join } from 'node:path';
import sharp from 'sharp';

const require = createRequire(import.meta.url);
const FFMPEG = require('ffmpeg-static');
const ORIG = 'FOTOS Y VIDEOS HALLOWEEN DRYICEPACK';
const OUT = 'tema/dryicepack/assets/halloween/video';
const TMP = '.tmp/halloween-videos';
mkdirSync(OUT, { recursive: true });
mkdirSync(TMP, { recursive: true });

// x264 común. CRF por vídeo: el de "cómo" (CRF 25, ~1,6 MB) necesita bits para el detalle de la niebla;
// el de las calabazas es un plano fijo y suave, y con CRF 26 se queda en ~0,7 MB sin pérdida visible.
const x264 = (crf) => [
  '-c:v', 'libx264', '-preset', 'slower', '-profile:v', 'main', '-level:v', '3.1', '-pix_fmt', 'yuv420p',
  '-crf', String(crf), '-maxrate', '3.2M', '-bufsize', '6.4M',
  '-g', '60', '-keyint_min', '30', '-sc_threshold', '0',
  '-an', '-sn', '-dn', '-map_metadata', '-1', '-map_chapters', '-1',
  '-movflags', '+faststart', '-fflags', '+bitexact', '-flags:v', '+bitexact',
];

const trabajos = [
  {
    nombre: 'como-hacer-niebla',
    src: 'IMG_3496.MOV',
    crf: 25,
    // El giro del iPhone (−90°) lo aplica ffmpeg solo antes del filtro: sale vertical.
    entrada: ['-t', '6.0'],
    filtro: ['-vf', 'scale=720:1280:flags=lanczos,fade=t=out:st=5.6:d=0.4,format=yuv420p'],
    poster: { calidad: 52 },
  },
  {
    nombre: 'calabazas-niebla',
    src: 'IMG_3531.MOV',
    crf: 26,
    entrada: [],
    // Bucle sin costura: [8 s → 9 s] fundido sobre [0 s → 1 s], seguido de [1 s → 8 s]. Duración final: 8 s.
    filtro: ['-filter_complex',
      '[0:v]fps=30,scale=1280:720:flags=lanczos,split[a][b];' +
      '[a]trim=start=8:end=9,setpts=PTS-STARTPTS[cola];' +
      '[b]trim=start=0:end=8,setpts=PTS-STARTPTS[cuerpo];' +
      '[cola][cuerpo]xfade=transition=fade:duration=1:offset=0,format=yuv420p[v]',
      '-map', '[v]'],
    poster: { calidad: 50 },
  },
];

const kb = (p) => (statSync(p).size / 1024).toFixed(1) + ' KB';
const mb = (p) => (statSync(p).size / 1048576).toFixed(2) + ' MB';

for (const t of trabajos) {
  const mp4 = join(OUT, `${t.nombre}.mp4`);
  execFileSync(FFMPEG, [
    '-hide_banner', '-loglevel', 'error', '-y',
    ...t.entrada, '-i', join(ORIG, t.src),
    ...t.filtro, ...x264(t.crf), mp4,
  ], { stdio: 'inherit' });

  // Póster: primer fotograma del MP4 ya codificado
  const png = join(TMP, `${t.nombre}-poster.png`);
  execFileSync(FFMPEG, ['-hide_banner', '-loglevel', 'error', '-y', '-i', mp4, '-frames:v', '1', png], { stdio: 'inherit' });
  const webp = join(OUT, `${t.nombre}-poster.webp`);
  const info = await sharp(png).webp({ quality: t.poster.calidad, effort: 6, smartSubsample: true }).toFile(webp);
  rmSync(png);

  console.log(`${mp4}  ${mb(mp4)}`);
  console.log(`${webp}  ${info.width}x${info.height}  ${kb(webp)}`);
}
