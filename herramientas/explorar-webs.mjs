// Estudio de referencias: recoge webs de la galería de Wix Explore y captura cada una
// (escritorio arriba y a media página, móvil arriba) + tipografías y recursos de animación que usan.
// Uso: node herramientas/explorar-webs.mjs [numero]   → referencia/webs/
import puppeteer from 'puppeteer-core';
import sharp from 'sharp';
import { mkdirSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const N = parseInt(process.argv[2] || '20', 10);
const OUT = 'referencia/webs';
mkdirSync(OUT, { recursive: true });
const espera = (ms) => new Promise((r) => setTimeout(r, ms));

const nav = await puppeteer.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true,
  userDataDir: resolve('.tmp/chrome-explorar'), args: ['--no-first-run', '--disable-blink-features=AutomationControlled'],
  defaultViewport: { width: 1440, height: 900 },
});

/* 1 · Direcciones de la galería (clic real en cada "Explora este sitio") */
const galeria = await nav.newPage();
await galeria.goto('https://es.wix.com/explore/websites', { waitUntil: 'networkidle2', timeout: 90000 });
try { await galeria.evaluate(() => { const b = [...document.querySelectorAll('button')].find((x) => /Rechazar todas/.test(x.textContent)); if (b) b.click(); }); } catch (e) {}
await espera(1500);

const sitios = [];
for (let pagina = 1; sitios.length < N && pagina <= 4; pagina++) {
  if (pagina > 1) {
    const ok = await galeria.evaluate((p) => {
      const b = [...document.querySelectorAll('button, a, span')].find((x) => x.textContent.trim() === String(p) && x.offsetParent);
      if (b) { b.click(); return true; } return false;
    }, pagina);
    if (!ok) break;
    await espera(3500);
  }
  const total = await galeria.evaluate(() => [...document.querySelectorAll('button')].filter((b) => /Explora este sitio/.test(b.textContent)).length);
  for (let i = 0; i < total && sitios.length < N; i++) {
    const nombre = await galeria.evaluate((i) => {
      const b = [...document.querySelectorAll('button')].filter((x) => /Explora este sitio/.test(x.textContent))[i];
      b.scrollIntoView({ block: 'center' });
      const card = b.closest('[id^="comp-"]')?.parentElement?.closest('[id^="comp-"]');
      return (card?.innerText || '').split('\n').map((s) => s.trim()).filter((s) => s && !/Explora este sitio/.test(s))[0] || '';
    }, i);
    await espera(400);
    const botones = await galeria.$$('xpath/.//button[contains(., "Explora este sitio")]');
    const btn = botones[i];
    if (!btn) continue;
    // La web se abre en otra pestaña; en modo headless solo se ve su proceso (…/_partials/wix-thunderbolt/…),
    // de donde se saca la dirección de la web.
    const nueva = new Promise((res) => {
      const t = setTimeout(() => { nav.off('targetcreated', oyente); res(null); }, 10000);
      const oyente = (tg) => {
        const u = tg.url() || '';
        if (!u.includes('/_partials/') || u.includes('es.wix.com')) return;
        clearTimeout(t); nav.off('targetcreated', oyente);
        res(u.split('/_partials/')[0] + '/');
      };
      nav.on('targetcreated', oyente);
    });
    try {
      const bb = await btn.boundingBox();
      if (bb) await galeria.mouse.click(bb.x + bb.width / 2, bb.y + bb.height / 2); else await btn.click();
    } catch (e) { continue; }
    const url = await nueva;
    for (const pg of await nav.pages()) { if (pg !== galeria && pg.url() !== 'about:blank') { try { await pg.close(); } catch (e) {} } }
    if (url && !sitios.some((s) => s.url === url)) { sitios.push({ nombre, url }); console.log(sitios.length, nombre, url); }
  }
}
await galeria.close();
writeFileSync(`${OUT}/sitios.json`, JSON.stringify(sitios, null, 2));

/* 2 · Captura y análisis de cada web */
const informe = [];
for (const [i, s] of sitios.entries()) {
  const id = String(i + 1).padStart(2, '0');
  const p = await nav.newPage();
  try {
    await p.setViewport({ width: 1440, height: 900 });
    await p.goto(s.url, { waitUntil: 'networkidle2', timeout: 60000 });
    await espera(3500);
    await p.screenshot({ path: `${OUT}/${id}-a-arriba.png` });
    const alto = await p.evaluate(() => document.documentElement.scrollHeight);
    await p.evaluate((y) => window.scrollTo(0, y), Math.round(alto * 0.33)); await espera(2200);
    await p.screenshot({ path: `${OUT}/${id}-b-medio.png` });
    await p.evaluate((y) => window.scrollTo(0, y), Math.round(alto * 0.62)); await espera(2200);
    await p.screenshot({ path: `${OUT}/${id}-c-abajo.png` });
    const datos = await p.evaluate(() => {
      const fam = (sel) => { const e = document.querySelector(sel); return e ? getComputedStyle(e).fontFamily.split(',')[0].replace(/["']/g, '') + ' ' + getComputedStyle(e).fontWeight + ' ' + getComputedStyle(e).fontSize : null; };
      const fuentes = [...document.fonts].filter((f) => f.status === 'loaded').map((f) => f.family.replace(/["']/g, ''));
      const conAnim = [...document.querySelectorAll('*')].filter((e) => { const c = getComputedStyle(e); return c.animationName !== 'none' || (c.transitionDuration !== '0s' && c.transitionProperty !== 'none'); }).length;
      return { h1: fam('h1'), h2: fam('h2'), p: fam('p'), fuentes: [...new Set(fuentes)].slice(0, 8), videos: document.querySelectorAll('video').length, lottie: !!document.querySelector('[class*="lottie"], lottie-player'), conAnim, alto: document.documentElement.scrollHeight };
    });
    // Móvil
    await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true, deviceScaleFactor: 1 });
    await p.goto(s.url, { waitUntil: 'networkidle2', timeout: 60000 }); await espera(3000);
    await p.screenshot({ path: `${OUT}/${id}-d-movil.png` });
    informe.push({ id, ...s, ...datos });
    console.log(id, s.nombre, JSON.stringify(datos));
  } catch (e) { console.log(id, s.nombre, 'error', e.message); informe.push({ id, ...s, error: e.message }); }
  await p.close();
}
writeFileSync(`${OUT}/informe.json`, JSON.stringify(informe, null, 2));

/* 3 · Hojas de contacto (miniaturas) para revisar de un vistazo */
const hoja = async (sufijo, salida, ancho = 480, alto = 300) => {
  const ok = informe.filter((x) => !x.error);
  if (!ok.length) return;
  const cols = 4, filas = Math.ceil(ok.length / cols);
  const piezas = [];
  for (const [k, x] of ok.entries()) {
    const buf = await sharp(`${OUT}/${x.id}-${sufijo}.png`).resize(ancho, alto, { fit: 'cover', position: 'top' }).toBuffer();
    piezas.push({ input: buf, left: (k % cols) * (ancho + 8), top: Math.floor(k / cols) * (alto + 8) });
  }
  await sharp({ create: { width: cols * (ancho + 8), height: filas * (alto + 8), channels: 3, background: '#222' } }).composite(piezas).png().toFile(salida);
};
await hoja('a-arriba', `${OUT}/hoja-arriba.png`);
await hoja('b-medio', `${OUT}/hoja-medio.png`);
await hoja('d-movil', `${OUT}/hoja-movil.png`, 195, 422);
await nav.close();
console.log('listo:', informe.length, 'webs');
