// Casilla «No quiero recibir avisos ni ofertas de hielo seco por email» (plugin, inc/ofertas.php) en el WordPress de prueba
// (servidor wordpress-tema, 9500):
// 1. La casilla está justo debajo del email, sin marcar, con su texto. Capturas a 390 y 1440 px en .tmp/ofertas/.
// 2. Un pedido sin marcarla guarda _dip_publicidad = 'si' y sale en la lista; otro marcándola guarda 'no' y no sale.
// 3. La página de baja: con GET pide confirmar (no da de baja sola); con el botón da de baja y deja de salir en la lista.
//    Con una firma falsa, 400.
// 4. La política de privacidad lo explica en castellano, catalán e inglés.
// Uso: node herramientas/probar-ofertas.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const FIXTURE = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-ofertas.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
const limpio = (s) => (s || '').replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
mkdirSync('.tmp/ofertas', { recursive: true });
copyFileSync('herramientas/fixtures/ofertas.php', FIXTURE);
const datos = async (q = '') => (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-ofertas.php${q}`)).json();
const marca = Date.now().toString(36);
const emailSi = `ofertas-si-${marca}@example.com`;
const emailNo = `ofertas-no-${marca}@example.com`;

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
const v = prod.variations.find((x) => x.attributes.some((at) => at.value === '10kg-de-hielo-seco') && x.attributes.some((at) => at.value === '3mm'));

async function pedido(email, marcar, capturar) {
  const ctx = await nav.createBrowserContext();
  const p = await ctx.newPage();
  p.on('pageerror', (e) => errores.push(email + ': ' + e.message));
  await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await p.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=10kg-de-hielo-seco&attribute_pa_formato=3mm&quantity=1`, { waitUntil: 'networkidle2', timeout: 180000 });
  await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle2', timeout: 180000 });
  const casilla = await p.evaluate(() => {
    const campo = document.getElementById('dip_sin_ofertas_field');
    const email = document.getElementById('billing_email_field');
    const tel = document.getElementById('billing_phone_field');
    return campo ? {
      texto: campo.textContent.replace(/\s+/g, ' ').trim(),
      marcada: document.getElementById('dip_sin_ofertas').checked,
      despuesDelEmail: email && email.nextElementSibling === campo,
      antesDelTelefono: tel && campo.nextElementSibling === tel,
    } : null;
  });
  if (capturar) {
    ok(casilla && casilla.texto === 'No quiero recibir avisos ni ofertas de hielo seco por email.', 'la casilla está con su texto', casilla);
    ok(casilla && !casilla.marcada, 'sin marcar', casilla);
    ok(casilla && casilla.despuesDelEmail && casilla.antesDelTelefono, 'justo debajo del email, antes del teléfono', casilla);
    for (const [ancho, alto] of [[390, 844], [1440, 900]]) {
      await p.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
      await new Promise((r) => setTimeout(r, 500));
      const zona = await p.evaluate(() => { const a = document.getElementById('billing_email_field').getBoundingClientRect(); const b = document.getElementById('billing_phone_field').getBoundingClientRect(); return { x: Math.max(0, a.left - 12), y: a.top + scrollY - 12, width: Math.min(innerWidth - Math.max(0, a.left - 12), a.width + 24), height: b.bottom - a.top + 24 }; });
      await p.screenshot({ path: `.tmp/ofertas/casilla-${ancho}.png`, clip: zona });
      ok(await p.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${ancho} px: sin scroll horizontal`);
    }
    await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  }
  const escribir = async (sel, txt) => { await p.click(sel, { clickCount: 3 }); await p.type(sel, txt); };
  await escribir('#billing_email', email);
  if (marcar) await p.click('#dip_sin_ofertas');
  await escribir('#billing_phone', '600123456');
  await escribir('#billing_first_name', 'Prueba Ofertas');
  await escribir('#billing_address_1', 'Carrer de Mallorca 123');
  await escribir('#billing_postcode', '08013');
  await escribir('#billing_city', 'Barcelona');
  await p.evaluate(() => document.activeElement.blur());
  await p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 30000 }).catch(() => null);
  await new Promise((r) => setTimeout(r, 800));
  const marcadaAlPagar = await p.evaluate(() => document.getElementById('dip_sin_ofertas').checked);
  await p.evaluate(() => document.getElementById('place_order').click());
  try { await p.waitForNavigation({ timeout: 40000 }); } catch (e) { /* la página de gracias puede tardar */ }
  await new Promise((r) => setTimeout(r, 1200));
  const id = Number((p.url().match(/order-received\/(\d+)/) || [])[1] || 0);
  await ctx.close();
  return { id, marcadaAlPagar };
}

try {
  const si = await pedido(emailSi, false, true);
  const no = await pedido(emailNo, true, false);
  ok(si.id > 0 && no.id > 0, 'los dos pedidos se crean', { si, no });
  ok(no.marcadaAlPagar, 'la casilla sigue marcada después de recalcular el checkout');
  const mSi = await datos(`?pedido=${si.id}`);
  const mNo = await datos(`?pedido=${no.id}`);
  ok(mSi && mSi.publicidad === 'si', 'sin marcar: _dip_publicidad = si', mSi);
  ok(mNo && mNo.publicidad === 'no', 'marcada: _dip_publicidad = no', mNo);
  // La lista solo cuenta pedidos Procesando o Completados (aquí se pagan con «cheque» y quedan En espera)
  let enEspera = await datos();
  ok(!enEspera.clientes.some((c) => c.email === emailSi), 'un pedido En espera (sin pagar) aún no cuenta para la lista');
  await datos(`?procesar=${si.id}`);
  await datos(`?procesar=${no.id}`);
  let lista = await datos();
  const enLista = (e) => lista.clientes.some((c) => c.email === e);
  ok(enLista(emailSi), 'el que no la marcó sale en la lista de clientes para ofertas');
  ok(!enLista(emailNo), 'el que la marcó no sale');
  ok(/^\d{4}-\d{2}-\d{2}$/.test(lista.desde), 'se guarda desde cuándo está la casilla', lista.desde);

  // Baja
  const fila = lista.clientes.find((c) => c.email === emailSi);
  const ctx = await nav.createBrowserContext();
  const b = await ctx.newPage();
  await b.setViewport({ width: 390, height: 844, isMobile: true });
  const r1 = await b.goto(fila.baja, { waitUntil: 'networkidle2' });
  const pag1 = await b.evaluate(() => ({ h1: document.querySelector('h1')?.textContent, form: !!document.querySelector('form button'), robots: document.querySelector('meta[name="robots"]')?.content }));
  ok(r1.status() === 200 && pag1.h1 === '¿Dejamos de enviarte ofertas?' && pag1.form && /noindex/.test(pag1.robots), 'el enlace de baja pide confirmar con un botón (abrirlo no da de baja)', { estado: r1.status(), ...pag1 });
  lista = await datos();
  ok(enLista(emailSi), 'solo con abrir el enlace sigue en la lista');
  await b.screenshot({ path: '.tmp/ofertas/baja-390.png' });
  await Promise.all([b.waitForNavigation({ waitUntil: 'networkidle2' }), b.click('form button')]);
  const pag2 = await b.evaluate(() => document.querySelector('h1')?.textContent);
  ok(pag2 === 'Hecho: ya no te enviaremos ofertas', 'con el botón: «Hecho: ya no te enviaremos ofertas»', pag2);
  lista = await datos();
  ok(!enLista(emailSi), 'y ya no sale en la lista');
  const falsa = new URL(fila.baja); falsa.searchParams.set('firma', '0'.repeat(16));
  const r3 = await b.goto(falsa.href, { waitUntil: 'networkidle2' });
  ok(r3.status() === 400 && (await b.evaluate(() => document.querySelector('h1')?.textContent)) === 'Este enlace no es válido', 'con una firma falsa: 400 «Este enlace no es válido»', r3.status());
  await ctx.close();

  // Política de privacidad
  const textos = {
    es: 'Enviarte por email avisos de fechas y ofertas de hielo seco si nos has comprado',
    ca: 'Enviar-te per correu avisos de dates i ofertes de gel sec si ens has comprat',
    en: 'Emailing you date reminders and dry ice offers if you have bought from us',
  };
  const pp = await nav.newPage();
  await pp.goto(BASE + '/politica-de-privacidad/', { waitUntil: 'networkidle2' });
  const alternas = await pp.evaluate(() => Object.fromEntries([...document.querySelectorAll('link[rel="alternate"][hreflang]')].map((l) => [l.hreflang, l.href])));
  for (const [idioma, texto] of Object.entries(textos)) {
    const url = idioma === 'es' ? BASE + '/politica-de-privacidad/' : alternas[idioma];
    if (!url) { ok(false, `política de privacidad en ${idioma}: sin enlace hreflang`); continue; }
    await pp.goto(url, { waitUntil: 'networkidle2' });
    ok((await pp.evaluate(() => document.body.innerText)).includes(texto), `política de privacidad (${idioma}) explica las ofertas a clientes`);
  }
} finally {
  rmSync(FIXTURE, { force: true });
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-ofertas` : '\nprobar-ofertas: todo correcto');
process.exit(fallos ? 1 : 0);
