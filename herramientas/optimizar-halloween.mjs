// Optimiza las fotos de Halloween a WebP en varios anchos y genera la textura de niebla.
// Uso: node herramientas/optimizar-halloween.mjs
import sharp from 'sharp';
import { mkdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const ORIG = 'FOTOS Y VIDEOS HALLOWEEN DRYICEPACK';
const OUT = 'landing-halloween/assets/halloween/img';
mkdirSync(OUT, { recursive: true });

// crop: fracciones del original { left, top, width, height } (0–1)
const jobs = [
  { src: 'IMG_3517.jpeg', name: 'hero-calabazas', widths: [1024, 1600], quality: 58 },
  { src: 'IMG_3517.jpeg', name: 'hero-calabazas-movil', widths: [480, 780], quality: 55,
    crop: { left: 0.19, top: 0, width: 0.5625, height: 1 } },             // 3:4 centrado en la calabaza grande
  { src: 'IMG_3572 (3).jpeg', name: 'formato-3mm', widths: [480, 800], quality: 62,
    crop: { left: 0.06, top: 0, width: 0.85, height: 1 } },               // ~4:5
  { src: 'IMG_3593 (1).jpeg', name: 'formato-16mm', widths: [480, 800], quality: 62,
    crop: { left: 0, top: 0.04, width: 1, height: 0.9375 } },             // 4:5
  { src: 'IMG_3603 (2).jpeg', name: 'caja-cenital', widths: [480, 800], quality: 62,
    crop: { left: 0, top: 0.026, width: 1, height: 0.947 } },             // 1:1
  { src: 'IMG_3595.jpeg', name: 'niebla-cenital', widths: [800, 1440], quality: 50,
    crop: { left: 0, top: 0.2, width: 1, height: 0.5625 } },              // 4:3 horizontal
];

const kb = (p) => (statSync(p).size / 1024).toFixed(1) + ' KB';

for (const job of jobs) {
  const input = sharp(join(ORIG, job.src)).rotate();
  const meta = await input.metadata();
  // .rotate() aplica EXIF: si viene girada, intercambiamos ancho/alto
  const swap = (meta.orientation || 1) >= 5;
  const W = swap ? meta.height : meta.width;
  const H = swap ? meta.width : meta.height;
  for (const w of job.widths) {
    let img = sharp(join(ORIG, job.src)).rotate();
    if (job.crop) {
      const c = job.crop;
      img = img.extract({
        left: Math.round(c.left * W), top: Math.round(c.top * H),
        width: Math.round(c.width * W), height: Math.round(c.height * H),
      });
    }
    const file = join(OUT, `${job.name}-${w}.webp`);
    const info = await img.resize({ width: w }).webp({ quality: job.quality, effort: 6 }).toFile(file);
    console.log(`${file}  ${info.width}x${info.height}  ${kb(file)}`);
  }
}

// Textura de niebla: ruido fractal blanco con alfa, pensada para desplazarse de ida y vuelta (sin bucle, sin costura).
// Se genera pequeña y se amplía: el reescalado la suaviza sin crear bordes oscuros.
const fogW = 500, fogH = 164;
const fogSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="${fogW}" height="${fogH}">
  <defs>
    <filter id="f" x="0" y="0" width="100%" height="100%">
      <feTurbulence type="fractalNoise" baseFrequency="0.009 0.028" numOctaves="3" seed="7" stitchTiles="stitch"/>
      <feColorMatrix type="matrix" values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 1.5 -0.52"/>
    </filter>
    <linearGradient id="g" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#fff" stop-opacity="0"/>
      <stop offset="0.45" stop-color="#fff" stop-opacity="0.85"/>
      <stop offset="1" stop-color="#fff" stop-opacity="1"/>
    </linearGradient>
    <mask id="m"><rect width="100%" height="100%" fill="url(#g)"/></mask>
  </defs>
  <rect width="100%" height="100%" filter="url(#f)" mask="url(#m)"/>
</svg>`;
const fogFile = join(OUT, 'niebla-capa.webp');
await sharp(Buffer.from(fogSvg)).resize({ width: 720, kernel: 'cubic' })
  .webp({ quality: 40, alphaQuality: 100, effort: 6 }).toFile(fogFile); // alfa sin pérdida: sin escalones y < 30 KB
console.log(`${fogFile}  720x236  ${kb(fogFile)}`);
