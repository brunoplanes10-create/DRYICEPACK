// Prueba del checkout en el WordPress de prueba (puerto 9500): añade un pack, abre el checkout,
// comprueba campos, fechas y totales, y hace capturas en móvil y escritorio.
// Uso: node herramientas/probar-checkout.mjs [peso] [formato]   (p. ej. 10kg-de-hielo-seco 16mm)
import puppeteer from 'puppeteer-core';
import { mkdirSync } from 'node:fs';
const BASE = 'http://127.0.0.1:9500';
const peso = process.argv[2] || '10kg-de-hielo-seco';
const formato = process.argv[3] || '16mm';
const OUT = '.tmp/capturas-checkout'; mkdirSync(OUT, { recursive: true });
const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true, args: ['--no-first-run'] });
const p = await nav.newPage();
const errores = [];
p.on('pageerror', (e) => errores.push('JS: ' + e.message));
p.on('console', (m) => { if (m.type() === 'error') errores.push('consola: ' + m.text()); });
await p.setViewport({ width: 1440, height: 900 });
const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
const vars = { padre: prod.id, v: prod.variations.map((x) => ({ id: x.id, a: Object.fromEntries(x.attributes.map((a) => ['attribute_' + (a.name.toLowerCase().includes('peso') ? 'pa_peso' : 'pa_formato'), a.value])) })) };
const v = vars.v.find((x) => x.a.attribute_pa_peso === peso && x.a.attribute_pa_formato === formato);
if (!v) { console.log('No encuentro la variación', peso, formato, JSON.stringify(vars)); process.exit(1); }
await p.goto(`${BASE}/?add-to-cart=${vars.padre}&variation_id=${v.id}&attribute_pa_peso=${peso}&attribute_pa_formato=${formato}&quantity=1`, { waitUntil: 'networkidle2' });
await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle2' });
await new Promise((r) => setTimeout(r, 1500));
const info = await p.evaluate(() => ({
  campos: [...document.querySelectorAll('form.checkout .form-row')].filter((r) => r.offsetParent).map((r) => (r.querySelector('label') || {}).textContent?.trim().replace(/\s+/g, ' ')),
  metodos: [...document.querySelectorAll('.dip-metodo')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  fechas: [...document.querySelectorAll('.dip-fecha')].map((m) => m.textContent.trim().replace(/\s+/g, ' ') + (m.querySelector('input').checked ? ' ✓' : '')),
  resumen: [...document.querySelectorAll('.woocommerce-checkout-review-order-table tr')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  pagos: [...document.querySelectorAll('.wc_payment_method label')].map((m) => m.textContent.trim()),
  boton: (document.getElementById('place_order') || {}).textContent,
  avisos: [...document.querySelectorAll('.woocommerce-error li, .woocommerce-message, .woocommerce-info')].map((m) => m.textContent.trim()),
}));
console.log(JSON.stringify(info, null, 1));
await p.screenshot({ path: `${OUT}/checkout-1440.png`, fullPage: true });
await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
await p.reload({ waitUntil: 'networkidle2' });
await p.screenshot({ path: `${OUT}/checkout-390.png`, fullPage: true });
console.log(errores.length ? errores.join('\n') : 'Sin errores de JS');
await nav.close();
