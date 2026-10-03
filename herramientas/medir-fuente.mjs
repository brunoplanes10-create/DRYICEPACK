// Calcula los ajustes (size-adjust, ascent/descent-override) de una fuente de reserva basada en Arial
// para que ocupe lo mismo que la fuente web: así, al cargarla, el texto no se mueve (CLS ≈ 0).
// Mide en Chrome el ancho de textos reales de la web con la fuente web frente a Arial normal y negrita.
// Uso (con el WordPress de prueba en marcha en el puerto 9500): node herramientas/medir-fuente.mjs
import puppeteer from 'puppeteer-core';

const BASE = 'http://127.0.0.1:9500/wp-content/themes/dryicepack/assets/fonts/';
const FUENTES = [
  // [nombre, archivo, peso normal, peso negrita]
  ['Inter', 'inter.woff2', 400, 700],
  ['Inter Tight', 'inter-tight.woff2', 500, 800],
];
const MUESTRAS = [
  'Hielo seco en tu puerta, mañana por la mañana.',
  'Pide antes de las 12:00 y mañana lo tienes, en caja aislante y listo para usar. Desde 3 kg y sin contratos.',
  'Pellets de 3 mm y nuggets de 16 mm. Recogida en Mataró de lunes a sábado. ¿Cuánto hielo seco necesito?',
  'Proveedor de hielo seco para quien lo usa cada semana. Transporte en frío, laboratorios e industria.',
];

const b = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const p = await b.newPage();
await p.goto('http://127.0.0.1:9500/', { waitUntil: 'domcontentloaded', timeout: 120000 });
for (const [nombre, archivo, normal, negrita] of FUENTES) {
  const r = await p.evaluate(async (url, MUESTRAS, normal, negrita) => {
    const ff = new FontFace('Medida', `url(${url})`, { weight: '100 900' });
    document.fonts.add(await ff.load());
    const c = document.createElement('canvas').getContext('2d');
    const ancho = (font) => MUESTRAS.reduce((s, t) => { c.font = font; return s + c.measureText(t).width; }, 0);
    c.font = `${normal} 100px Medida`;
    const m = c.measureText('Hg');
    return {
      n: ancho(`${normal} 100px Medida`) / ancho('400 100px Arial'),
      b: ancho(`${negrita} 100px Medida`) / ancho('700 100px Arial'),
      asc: m.fontBoundingBoxAscent, desc: m.fontBoundingBoxDescent,
    };
  }, BASE + archivo, MUESTRAS, normal, negrita);
  const pct = (x) => (x * 100).toFixed(2) + '%';
  for (const [tipo, sa] of [[`normal (Arial, para ${normal})`, r.n], [`negrita (Arial Bold, para ${negrita})`, r.b]]) {
    console.log(`${nombre} · ${tipo}: size-adjust: ${pct(sa)}; ascent-override: ${pct(r.asc / 100 / sa)}; descent-override: ${pct(r.desc / 100 / sa)}; line-gap-override: 0%;`);
  }
}
await b.close();
