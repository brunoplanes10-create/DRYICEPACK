// Pedido completo en el WordPress de prueba: rellena el checkout, elige sábado (o recogida), marca empresa si se pide y paga.
// Uso: node herramientas/probar-pedido.mjs [sabado|recogida|normal] [empresa]
import puppeteer from 'puppeteer-core';
const BASE = 'http://127.0.0.1:9500';
const modo = process.argv[2] || 'sabado';
const empresa = process.argv[3] === 'empresa';
const cantidad = Number(process.argv[4] || 1);
const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const p = await nav.newPage();
const errores = [];
p.on('pageerror', (e) => errores.push('JS: ' + e.message));
await p.setViewport({ width: 1280, height: 900 });
const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
const v = prod.variations.find((x) => x.attributes.some((a) => a.value === '20kg-de-hielo-seco') && x.attributes.some((a) => a.value === '3mm'));
await p.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=20kg-de-hielo-seco&attribute_pa_formato=3mm&quantity=${cantidad}`, { waitUntil: 'networkidle2' });
await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle2' });
const esperaAjax = () => p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 30000 }).catch(() => null);
const escribir = async (sel, txt) => { await p.click(sel, { clickCount: 3 }); await p.type(sel, txt); };
await escribir('#billing_email', 'prueba@example.com');
await escribir('#billing_phone', '600123456');
await escribir('#billing_first_name', 'Laura García Pons');
await escribir('#billing_address_1', 'Carrer de Mallorca 123');
await escribir('#billing_postcode', '08013');
await escribir('#billing_city', 'Barcelona');
await p.evaluate(() => document.activeElement.blur());
await esperaAjax(); await new Promise((r) => setTimeout(r, 800));
if (modo === 'recogida') {
  const e1 = esperaAjax(); await p.evaluate(() => document.querySelector('.dip-metodo--recogida input').click()); await e1;
  await new Promise((r) => setTimeout(r, 800));
} else if (modo === 'sabado') {
  const e2 = esperaAjax(); await p.evaluate(() => document.querySelector('.dip-fecha--sabado input').click()); await e2;
  await new Promise((r) => setTimeout(r, 800));
}
if (empresa) {
  const casilla = await p.$('#dip_empresa');
  const marcada = await p.evaluate((c) => c.checked, casilla);
  if (!marcada) await p.click('#dip_empresa');
  await escribir('#billing_company', 'Laboratorio Prueba SL');
  await escribir('#billing_nif', 'B66800103');
}
const antes = await p.evaluate(() => ({
  resumen: [...document.querySelectorAll('.woocommerce-checkout-review-order-table tfoot tr')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  pagos: [...document.querySelectorAll('.wc_payment_method label')].map((m) => m.textContent.trim()),
  empresa: (document.getElementById('dip_empresa') || {}).checked,
  fiscalesVisibles: [...document.querySelectorAll('.dip-fiscal')].filter((f) => !f.hidden).length,
  boton: document.getElementById('place_order').textContent,
}));
console.log('ANTES DE PAGAR', JSON.stringify(antes, null, 1));
await p.evaluate(() => document.getElementById('place_order').click());
try { await p.waitForNavigation({ timeout: 40000 }); } catch (e) {}
await new Promise((r) => setTimeout(r, 1500));
const despues = await p.evaluate(() => ({
  url: location.href,
  errores: [...document.querySelectorAll('.woocommerce-error li, .woocommerce-NoticeGroup li, .woocommerce-error')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  entrega: (document.querySelector('.dip-entrega-pedido') || {}).textContent,
  factura: (document.querySelector('.dip-factura-cta') || {}).textContent,
}));
console.log('DESPUÉS', JSON.stringify(despues, null, 1));
console.log(errores.length ? errores.join('\n') : 'Sin errores de JS');
await nav.close();
