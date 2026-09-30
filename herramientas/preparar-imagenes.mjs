// Prepara las imágenes del tema (WebP en varios anchos) a partir de las fotos originales,
// que no se suben a Git: FOTOS Y VIDEOS HALLOWEEN DRYICEPACK/ y .tmp/fuentes/ (descargadas de dryicepack.es).
// Uso: node herramientas/preparar-imagenes.mjs
import sharp from 'sharp';
import { mkdirSync, existsSync, writeFileSync } from 'node:fs';

const OUT = 'tema/dryicepack/assets/img/fotos';
mkdirSync(OUT, { recursive: true });
const H = 'FOTOS Y VIDEOS HALLOWEEN DRYICEPACK/';
const F = '.tmp/fuentes/';

// [nombre, origen, anchos, recorte {left, top, width, height} en píxeles del original ya girado, ajuste]
const lista = [
  // Fotos reales del producto
  ['caja-16mm-niebla', H + 'IMG_3591 (3).jpeg', [640, 1000, 1500], { left: 180, top: 700, width: 2843, height: 2700 }, 'oscura'],
  ['caja-3mm', H + 'IMG_3576.jpeg', [640, 1000], { left: 0, top: 900, width: 3024, height: 2900 }, 'oscura'],
  ['caja-16mm-lateral', F + 'HIELO-SECO-16MM.jpeg', [480, 900, 1400], null, 'oscura'],
  ['caja-3mm-lateral', F + 'HIELO-SECO-3MM.jpeg', [480, 900, 1400], null, 'oscura'],
  ['cenital-16mm', F + '16MM-ARRIBA.jpeg', [480, 900, 1400], null, 'oscura'],
  ['cenital-3mm', F + '3MM-ARRIBA.jpeg', [480, 900, 1400], null, 'oscura'],
  ['cenital-caja-nuggets', H + 'IMG_3593 (1).jpeg', [480, 900, 1400], null, 'oscura'],
  // Imágenes de sector que ya usa la web
  ['uso-hosteleria', F + 'ChatGPT-Image-25-nov-2025-10_50_52-1.png', [480, 900, 1400]],
  ['uso-eventos', F + 'ChatGPT-Image-25-nov-2025-10_02_21-1.png', [480, 900, 1400]],
  ['uso-transporte', F + 'ChatGPT-Image-21-nov-2025-13_08_51-1.png', [480, 900, 1400]],
  ['uso-laboratorio', F + 'ChatGPT-Image-23-nov-2025-14_51_32-1.png', [480, 900]],
  ['uso-laboratorio-entrega', F + 'ChatGPT-Image-23-nov-2025-13_27_35-1.png', [480, 900, 1400]],
  ['uso-laboratorio-pinzas', F + 'ChatGPT-Image-24-nov-2025-09_11_36-1-1.png', [480, 900, 1400]],
  ['uso-industria', F + 'ChatGPT-Image-24-nov-2025-11_02_45-2.png', [480, 900, 1400]],
  ['uso-industria-pala', F + 'ChatGPT-Image-2-dic-2025-10_56_12-1-1.png', [480, 900, 1400]],
  ['uso-agricultura', F + 'ChatGPT-Image-24-nov-2025-12_35_14-1.png', [480, 900, 1400]],
  ['uso-vino', F + 'ChatGPT-Image-24-nov-2025-12_58_50-1.png', [480, 900, 1400]],
  ['uso-docencia', F + 'ChatGPT-Image-25-nov-2025-15_03_04-1.png', [480, 900, 1400]],
  ['reparto-furgoneta', F + 'ChatGPT-Image-21-nov-2025-13_05_22-1.png', [480, 900, 1400]],
  ['cajas-furgoneta', F + 'ChatGPT-Image-7-nov-2025-13_48_11-1.png', [480, 900, 1400]],
  ['almacen-caja', F + 'ChatGPT-Image-3-dic-2025-08_51_42-1-1.png', [480, 900]],
  ['anadiendo-hielo', F + 'Anadiendo-hielo-seco-en-caja-eps.webp', [480, 662]],
  ['nuggets-azul', F + 'hielo-seco-nuggets-16mm.webp', [480, 721]],
  ['cajas-oscuro', F + 'hombre-cargando-una-caja-eps-en-furgoneta.webp', [480, 662]],
  ['pellets-ia', F + 'ChatGPT-Image-1-dic-2025-13_32_23-1.png', [480, 900, 1400]],
  ['nuggets-ia', F + 'ChatGPT-Image-1-dic-2025-13_15_17-1.png', [480, 900, 1400]],
];

const manifiesto = {};
for (const [nombre, origen, anchos, recorte, ajuste] of lista) {
  if (!existsSync(origen)) { console.log('Falta', origen); continue; }
  let base = sharp(origen).rotate();
  if (recorte) base = sharp(await base.toBuffer()).extract(recorte);
  if (ajuste === 'oscura') base = base.modulate({ saturation: 0.82 }).linear(1.04, -4);
  const buf = await base.toBuffer();
  const meta = await sharp(buf).metadata();
  manifiesto[nombre] = { w: meta.width, h: meta.height, anchos: [] };
  for (const w of anchos) {
    const ancho = Math.min(w, meta.width);
    const salida = `${OUT}/${nombre}-${ancho}.webp`;
    const info = await sharp(buf).resize({ width: ancho }).webp({ quality: ancho >= 1400 ? 64 : 70, effort: 6, smartSubsample: true }).toFile(salida);
    manifiesto[nombre].anchos.push(ancho);
    console.log(salida, Math.round(info.size / 1024) + ' KB');
  }
}
writeFileSync('tema/dryicepack/assets/img/fotos/fotos.json', JSON.stringify(manifiesto, null, 1));
console.log('Listo:', Object.keys(manifiesto).length, 'imágenes');
