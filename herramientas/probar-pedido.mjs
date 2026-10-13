// Pedido completo en el WordPress de prueba: rellena el checkout, elige sábado (o recogida), marca empresa si se pide y paga.
// Comprueba que el pedido se crea (página de gracias) y, en recogida (03/10/2026): total redondo (1 × 20 kg = 115,00 €),
// solo efectivo, el texto «Pago en efectivo al recoger: 115,00 €. Trae el importe exacto: no tenemos cambio.» en el
// checkout y el mismo texto en la página de gracias. Sale con código 1 si algo falla.
// Uso: node herramientas/probar-pedido.mjs [sabado|recogida|normal] [empresa] [cantidad]
import puppeteer from 'puppeteer-core';
const BASE = 'http://127.0.0.1:9500';
const modo = process.argv[2] || 'sabado';
const empresa = process.argv[3] === 'empresa';
const cantidad = Number(process.argv[4] || 1);
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
const limpio = (s) => (s || '').replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
const PRECIO_RECOGIDA_20 = 115; // € IVA incluido: 98,60 × 1,21 = 119,31 → redondeado a la baja a 5 €
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
  total: (document.querySelector('.woocommerce-checkout-review-order-table .order-total .amount') || {}).textContent,
  pagos: [...document.querySelectorAll('.wc_payment_method label')].map((m) => m.textContent.trim()),
  efectivo: (document.querySelector('.payment_method_cod .payment_box') || {}).textContent,
  precioRecogida: (document.querySelector('.dip-metodo--recogida .dip-metodo__precios') || {}).textContent,
  empresa: (document.getElementById('dip_empresa') || {}).checked,
  fiscalesVisibles: [...document.querySelectorAll('.dip-fiscal')].filter((f) => !f.hidden).length,
  boton: document.getElementById('place_order').textContent,
}));
console.log('ANTES DE PAGAR', JSON.stringify(antes, null, 1));
let textoEfectivo = '';
if (modo === 'recogida') {
  // Total redondo con IVA (precio de recogida) y el texto del efectivo con ese mismo total
  const total = PRECIO_RECOGIDA_20 * cantidad;
  const totalTexto = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
  textoEfectivo = `Pago en efectivo al recoger: ${totalTexto}. Trae el importe exacto: no tenemos cambio.`;
  ok(limpio(antes.total) === totalTexto, `recogida: el total del checkout es redondo, ${cantidad} × 20 kg = ${totalTexto}`, antes.total);
  ok(antes.pagos.length === 1 && /Efectivo al recoger/.test(antes.pagos[0]), 'recogida: la única forma de pago es «Efectivo al recoger»', antes.pagos);
  ok(limpio(antes.efectivo) === textoEfectivo, 'recogida: texto del efectivo en el checkout: «' + textoEfectivo + '»', antes.efectivo);
  ok(limpio(antes.precioRecogida) === `En efectivo al recoger: ${totalTexto}, IVA incluido`, 'recogida: el método «Recoger» enseña lo que se paga en efectivo', antes.precioRecogida);
  ok(/Confirmar pedido/.test(antes.boton), 'recogida: el botón dice «Confirmar pedido»', antes.boton);
} else {
  ok(!antes.pagos.some((t) => /Efectivo/.test(t)), modo + ': con envío no se ofrece el efectivo', antes.pagos);
}
await p.evaluate(() => document.getElementById('place_order').click());
try { await p.waitForNavigation({ timeout: 40000 }); } catch (e) {}
await new Promise((r) => setTimeout(r, 1500));
const despues = await p.evaluate(() => ({
  url: location.href,
  errores: [...document.querySelectorAll('.woocommerce-error li, .woocommerce-NoticeGroup li, .woocommerce-error')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  entrega: (document.querySelector('.dip-entrega-pedido') || {}).textContent,
  efectivo: (document.querySelector('.dip-pago-efectivo') || {}).textContent,
  total: (document.querySelector('.woocommerce-order-overview__total .amount, .woocommerce-table--order-details .order-total .amount') || {}).textContent,
  factura: (document.querySelector('.dip-factura-cta') || {}).textContent,
}));
console.log('DESPUÉS', JSON.stringify(despues, null, 1));
ok(/order-received/.test(despues.url) && !despues.errores.length, modo + ': el pedido se crea y llega a la página de gracias', { url: despues.url, errores: despues.errores });
ok(!!despues.entrega, modo + ': la página de gracias enseña la fecha de ' + (modo === 'recogida' ? 'recogida' : 'entrega'), despues.entrega);
if (modo === 'recogida') {
  ok(/^Recogida/.test(limpio(despues.entrega)), 'recogida: la fecha de la página de gracias es de recogida', despues.entrega);
  ok(limpio(despues.efectivo) === textoEfectivo, 'recogida: la página de gracias repite el texto del efectivo con el total', despues.efectivo);
  ok(limpio(despues.total) === textoEfectivo.match(/: (.*?)\./)[1], 'recogida: el total del pedido es el mismo', despues.total);
} else {
  ok(!despues.efectivo, modo + ': sin texto de efectivo en la página de gracias', despues.efectivo);
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-pedido ${modo}` : `\nprobar-pedido ${modo}: todo correcto`);
await nav.close();
process.exit(fallos ? 1 : 0);
