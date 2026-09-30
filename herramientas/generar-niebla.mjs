// Textura de niebla que se repite en horizontal sin costuras (feTurbulence con stitchTiles) y se desvanece arriba y abajo.
// Uso: node herramientas/generar-niebla.mjs  → tema/dryicepack/assets/img/niebla-capa.webp
import sharp from 'sharp';
const W = 1200, H = 300;
const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
  <defs>
    <filter id="f" x="0" y="0" width="100%" height="100%" color-interpolation-filters="sRGB">
      <feTurbulence type="fractalNoise" baseFrequency="0.0042 0.0125" numOctaves="4" seed="11" stitchTiles="stitch" result="n"/>
      <feColorMatrix in="n" type="matrix" values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  1.35 0 0 0 -0.42"/>
    </filter>
    <linearGradient id="g" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#fff" stop-opacity="0"/>
      <stop offset=".35" stop-color="#fff" stop-opacity="1"/>
      <stop offset=".75" stop-color="#fff" stop-opacity="1"/>
      <stop offset="1" stop-color="#fff" stop-opacity="0"/>
    </linearGradient>
    <mask id="m"><rect width="${W}" height="${H}" fill="url(#g)"/></mask>
  </defs>
  <rect width="${W}" height="${H}" filter="url(#f)" mask="url(#m)"/>
</svg>`;
// El desenfoque no da la vuelta al borde: se desenfoca con copias a los lados y se recorta el centro.
const tile = await sharp(Buffer.from(svg)).png().toBuffer();
const triple = await sharp({ create: { width: W * 3, height: H, channels: 4, background: { r: 0, g: 0, b: 0, alpha: 0 } } })
  .composite([{ input: tile, left: 0, top: 0 }, { input: tile, left: W, top: 0 }, { input: tile, left: W * 2, top: 0 }]).png().toBuffer();
const suave = await sharp(triple).blur(3).extract({ left: W, top: 0, width: W, height: H }).raw().toBuffer();
// librsvg no respeta stitchTiles: se hace la baldosa continua mezclando con una copia desplazada media baldosa
// (en los bordes manda la copia desplazada, en el centro la original).
const C = 4, sin = Buffer.alloc(W * H * C);
for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) {
  const m = 1 - Math.abs((x / (W - 1)) * 2 - 1); // 0 en los bordes, 1 en el centro
  const w = m * m * (3 - 2 * m);
  const xs = (x + W / 2) % W;
  for (let k = 0; k < C; k++) sin[(y * W + x) * C + k] = Math.round(suave[(y * W + x) * C + k] * w + suave[(y * W + xs) * C + k] * (1 - w));
}
const info = await sharp(sin, { raw: { width: W, height: H, channels: C } }).webp({ quality: 82, alphaQuality: 100, effort: 6 }).toFile('tema/dryicepack/assets/img/niebla-capa.webp');
console.log('niebla-capa.webp', Math.round(info.size / 1024) + ' KB', info.width + 'x' + info.height);
