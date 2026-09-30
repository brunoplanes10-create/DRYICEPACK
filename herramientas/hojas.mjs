// Captura de página completa (1440 px y 390 px) de varias direcciones y una tira reducida por página para revisar rápido.
// Uso: node herramientas/hojas.mjs http://127.0.0.1:9500/empresas/ http://127.0.0.1:9500/contacto/ …   → .tmp/hojas/
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import { mkdirSync } from 'node:fs';
const OUT = '.tmp/hojas'; mkdirSync(OUT, { recursive: true });
const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true, args: ['--hide-scrollbars'] });
for (const url of process.argv.slice(2)) {
  const nombre = (new URL(url).pathname.replace(/\//g, '-').replace(/^-|-$/g, '') || 'inicio');
  for (const [tipo, vp, ancho] of [['esc', { width: 1440, height: 900 }, 560], ['mov', { width: 390, height: 844, isMobile: true, hasTouch: true }, 300]]) {
    const p = await nav.newPage();
    const errores = [];
    p.on('pageerror', (e) => errores.push(e.message));
    await p.setViewport(vp);
    await p.goto(url, { waitUntil: 'networkidle0', timeout: 90000 });
    await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 300) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 140)); } window.scrollTo(0, 0); await new Promise((r) => setTimeout(r, 800)); });
    const buf = await p.screenshot({ fullPage: true });
    const img = sharp(buf);
    const m = await img.metadata();
    await sharp(buf).resize({ width: ancho }).toFile(`${OUT}/${nombre}-${tipo}.png`);
    console.log(nombre, tipo, m.height + 'px', errores.length ? 'ERRORES: ' + errores.join(' | ') : 'ok');
    await p.close();
  }
}
await nav.close();
