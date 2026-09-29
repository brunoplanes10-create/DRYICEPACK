// Genera las versiones minificadas (.min.css / .min.js) de la página de Halloween.
// Uso: node herramientas/compilar.mjs
import { buildSync } from 'esbuild';
import { statSync } from 'node:fs';

const BASE = 'landing-halloween/assets/halloween';
const archivos = [
  [`${BASE}/css/halloween.css`, `${BASE}/css/halloween.min.css`],
  [`${BASE}/js/halloween.js`, `${BASE}/js/halloween.min.js`],
];

for (const [origen, destino] of archivos) {
  buildSync({
    entryPoints: [origen], outfile: destino, minify: true, bundle: false,
    target: ['chrome100', 'safari15', 'firefox100'],
    legalComments: 'none', logLevel: 'error',
  });
  const kb = (p) => (statSync(p).size / 1024).toFixed(1);
  console.log(`${destino}: ${kb(origen)} KB → ${kb(destino)} KB`);
}
