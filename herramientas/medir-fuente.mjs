// Calcula los ajustes (size-adjust, ascent/descent-override) de una fuente de reserva basada en Arial
// para que ocupe lo mismo que Open Sans: así, al cargar la fuente web, el texto no se mueve (CLS ≈ 0).
// Mide en Chrome: ancho de un texto real con Open Sans 500/800 frente a Arial normal/negrita.
// Uso (con el WordPress de prueba en marcha): node herramientas/medir-fuente.mjs
import puppeteer from 'puppeteer-core';
import { resolve } from 'node:path';

const FUENTE = 'http://127.0.0.1:9400/wp-content/themes/prueba/assets/halloween/fonts/open-sans-var.woff2';
const MUESTRA = 'Hielo seco para Halloween: niebla de verdad en tu fiesta. Pellets de 3 mm que hacen niebla densa en cuanto tocan agua caliente, caja EPS lista para usar. ¿Cuánto hielo seco necesito?';

const b = await puppeteer.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true,
  userDataDir: resolve('.tmp/chrome-perfil'),
});
const p = await b.newPage();
await p.goto('http://127.0.0.1:9400/', { waitUntil: 'domcontentloaded' });
const r = await p.evaluate(async (FUENTE, MUESTRA) => {
  const ff = new FontFace('Medida', `url(${FUENTE})`, { weight: '300 800' });
  document.fonts.add(await ff.load());
  const c = document.createElement('canvas').getContext('2d');
  const m = (font) => { c.font = font; return c.measureText(MUESTRA); };
  const os5 = m('500 100px Medida'), os8 = m('800 100px Medida');
  const ar4 = m('400 100px Arial'), ar7 = m('700 100px Arial');
  return {
    anchoOS500: os5.width, anchoOS800: os8.width, anchoArial: ar4.width, anchoArialNegrita: ar7.width,
    ascOS: os5.fontBoundingBoxAscent, descOS: os5.fontBoundingBoxDescent,
  };
}, FUENTE, MUESTRA);
await b.close();

const pct = (x) => (x * 100).toFixed(2) + '%';
for (const [nombre, ancho, anchoArial] of [['normal (300–650)', r.anchoOS500, r.anchoArial], ['negrita (700–800)', r.anchoOS800, r.anchoArialNegrita]]) {
  const sa = ancho / anchoArial;
  console.log(`${nombre}: size-adjust ${pct(sa)} · ascent-override ${pct(r.ascOS / 100 / sa)} · descent-override ${pct(r.descOS / 100 / sa)} · line-gap-override 0%`);
}
console.log('medidas en bruto', JSON.stringify(r));
