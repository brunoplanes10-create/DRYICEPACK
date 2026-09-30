// Capturas de página completa (móvil 390 px emulado y escritorio 1440 px) con el Chrome instalado.
// Uso: node herramientas/capturas.mjs [url] [carpeta-salida]
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import { mkdirSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';

const url = process.argv[2] || 'http://127.0.0.1:9400/hielo-seco-halloween/';
const out = resolve(process.argv[3] || '.tmp/capturas');
mkdirSync(out, { recursive: true });

const executablePath = [
  'C:/Program Files/Google/Chrome/Application/chrome.exe',
  'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
].find(existsSync);
if (!executablePath) throw new Error('No encuentro Chrome');

const navegador = await puppeteer.launch({
  executablePath, headless: true,
  userDataDir: resolve('.tmp/chrome-perfil'),
  args: ['--no-first-run', '--hide-scrollbars'],
});

const vistas = [
  ['movil', { width: 390, height: 844, deviceScaleFactor: 1, isMobile: true, hasTouch: true }],
  ['escritorio', { width: 1440, height: 900, deviceScaleFactor: 1 }],
];

for (const [nombre, viewport] of vistas) {
  const pagina = await navegador.newPage();
  await pagina.setViewport(viewport);
  await pagina.goto(url, { waitUntil: 'networkidle0' });
  // Recorre la página para disparar las animaciones de entrada y la carga diferida
  await pagina.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 400) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 160)); }
    window.scrollTo(0, 0); await new Promise((r) => setTimeout(r, 1500));
  });
  // Primera pantalla (lo que se ve sin hacer scroll)
  await pagina.screenshot({ path: `${out}/${nombre}-pantalla.png` });
  // Página completa troceada en tramos legibles
  const completa = `${out}/${nombre}-completa.png`;
  await pagina.screenshot({ path: completa, fullPage: true });
  const { height } = await sharp(completa).metadata();
  const tramo = nombre === 'movil' ? 1400 : 1100;
  let i = 0;
  for (let y = 0; y < height; y += tramo, i++) {
    await sharp(completa).extract({ left: 0, top: y, width: viewport.width, height: Math.min(tramo, height - y) })
      .toFile(`${out}/${nombre}-${String(i + 1).padStart(2, '0')}.png`);
  }
  console.log(`${nombre}: ${height}px en ${i} tramos`);
  await pagina.close();
}
await navegador.close();
