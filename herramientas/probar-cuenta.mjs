// Cuenta de cliente opcional después de pagar (plugin, inc/cuenta-cliente.php) en el WordPress de prueba (wordpress-tema, 9500):
// 1. Un pedido sin cuenta: la página de gracias enseña la ficha de cliente (datos del pedido, sello «Sin guardar») y el
//    botón «Crear mi cuenta» con su flecha (texto nunca pegado al borde). Capturas a 390 y 1440 px en .tmp/cuenta/.
// 2. El botón crea la cuenta (cliente normal, no de empresa), con el enlace para poner la contraseña, los datos del pedido
//    para el próximo checkout y el pedido junto a la cuenta; la página dice «Guardada» y que revise su email.
// 3. Otro pedido con el mismo email: «Ya tienes cuenta con este email» en vez de la ficha.
// 4. Sin la clave del pedido no se crea nada.
// Uso: node herramientas/probar-cuenta.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const FIXTURE = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-cuenta.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
mkdirSync('.tmp/cuenta', { recursive: true });
copyFileSync('herramientas/fixtures/cuenta.php', FIXTURE);
const datos = async (q) => (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-cuenta.php${q}`)).json();
const marca = Date.now().toString(36);
const email = `cuenta-${marca}@example.com`;
const emailFalso = `cuenta-falsa-${marca}@example.com`;

// El WooCommerce del WordPress de prueba viene incompleto (falta un archivo de sus estadísticas): parche temporal solo allí
const parche = 'p' + Date.now().toString(36);
await datos('?parche=' + parche);
ok((await datos('?comprobar=1')) === true, 'parche temporal del WordPress de prueba puesto y activo');
const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
const v = prod.variations.find((x) => x.attributes.some((at) => at.value === '10kg-de-hielo-seco') && x.attributes.some((at) => at.value === '3mm'));

async function pedido(correo) {
  const ctx = await nav.createBrowserContext();
  const p = await ctx.newPage();
  p.on('pageerror', (e) => errores.push(correo + ': ' + e.message));
  await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await p.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=10kg-de-hielo-seco&attribute_pa_formato=3mm&quantity=1`, { waitUntil: 'networkidle2', timeout: 180000 });
  await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle2', timeout: 180000 });
  const escribir = async (sel, txt) => { await p.click(sel, { clickCount: 3 }); await p.type(sel, txt); };
  await escribir('#billing_email', correo);
  await escribir('#billing_phone', '600123456');
  await escribir('#billing_first_name', 'Laura García Pons');
  await escribir('#billing_address_1', 'Carrer de Mallorca 123');
  await escribir('#billing_address_2', '2º 1ª');
  await escribir('#billing_postcode', '08013');
  await escribir('#billing_city', 'Barcelona');
  await p.evaluate(() => document.activeElement.blur());
  await p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 30000 }).catch(() => null);
  await new Promise((r) => setTimeout(r, 800));
  await p.evaluate(() => document.getElementById('place_order').click());
  try { await p.waitForNavigation({ timeout: 40000 }); } catch (e) { /* la página de gracias puede tardar */ }
  await new Promise((r) => setTimeout(r, 1200));
  return { ctx, p, id: Number((p.url().match(/order-received\/(\d+)/) || [])[1] || 0) };
}
const ficha = (p) => p.evaluate(() => {
  const cc = document.querySelector('.dip-cc');
  const b = cc?.querySelector('button.boton');
  let hueco = null;
  if (b) { const r = b.getBoundingClientRect(); const rg = document.createRange(); rg.selectNodeContents(b); const cs = [...rg.getClientRects()].filter((x) => x.width > .5); hueco = { izq: Math.round(Math.min(...cs.map((x) => x.left)) - r.left), der: Math.round(r.right - Math.max(...cs.map((x) => x.right))) }; }
  return cc ? {
    filas: Object.fromEntries([...cc.querySelectorAll('.dip-cc__filas div')].map((d) => [d.querySelector('dt').textContent.trim(), d.querySelector('dd').textContent.trim()])),
    sello: cc.querySelector('.dip-cc__sello')?.textContent.trim(),
    titulo: cc.querySelector('h2')?.textContent.trim(),
    boton: b ? b.textContent.trim() : null,
    flecha: !!b?.querySelector('.boton__flecha'),
    hueco,
    nota: cc.querySelector('.dip-cc__nota')?.textContent.trim() || '',
    hecho: cc.querySelector('.dip-cc__hecho')?.textContent.trim() || '',
    ya: document.querySelector('.dip-cc-ya')?.textContent.trim() || '',
    scroll: document.documentElement.scrollWidth > innerWidth,
  } : { ya: document.querySelector('.dip-cc-ya')?.textContent.trim() || '' };
});

try {
  // 1. La ficha
  const a = await pedido(email);
  ok(a.id > 0, 'pedido sin cuenta creado', a.p.url());
  let f = await ficha(a.p);
  ok(f.filas && f.filas.Cliente === 'Laura García Pons' && f.filas.Email === email && f.filas['Dirección'] === 'Carrer de Mallorca 123, 2º 1ª · 08013 Barcelona' && f.filas['Último pedido'] === '10 kg · pellets de 3 mm', 'la ficha lleva los datos del pedido', f.filas);
  ok(f.sello === 'Sin guardar' && f.titulo === 'Guarda tu ficha y la próxima vez pagas sin escribir nada.', 'sello «Sin guardar» y el titular', { sello: f.sello, titulo: f.titulo });
  ok(f.boton === 'Crear mi cuenta' && f.flecha, 'botón «Crear mi cuenta» con su flecha', f);
  ok(f.hueco && f.hueco.izq >= 12 && f.hueco.der >= 4, 'el texto del botón no toca el borde', f.hueco);
  ok(f.nota === 'Te enviamos un email para poner la contraseña. Sin ella, nadie puede entrar en tu cuenta.', 'la nota de la contraseña', f.nota);
  for (const [ancho, alto] of [[390, 844], [1440, 900]]) {
    await a.p.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
    const cc = await a.p.$('.dip-cc');
    await cc.evaluate((e) => e.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 1800));
    await cc.screenshot({ path: `.tmp/cuenta/ficha-${ancho}.png` });
    ok(!(await a.p.evaluate(() => document.documentElement.scrollWidth > innerWidth)), `${ancho} px: sin scroll horizontal`);
  }
  ok(!(await datos(`?email=${encodeURIComponent(email)}`)), 'antes de pulsar no hay cuenta');

  // 2. El botón
  await a.p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await Promise.all([a.p.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }), a.p.click('.dip-cc button.boton')]);
  await new Promise((r) => setTimeout(r, 1500));
  f = await ficha(a.p);
  ok(/dip_cuenta=creada/.test(a.p.url()), 'vuelve a la página de gracias', a.p.url());
  ok(f.sello === 'Guardada' && f.titulo === 'Ficha guardada: la próxima vez pagas sin escribir nada.' && f.hecho === 'Hecho. Revisa tu email para poner la contraseña.' && f.boton === null, 'sello «Guardada», titular de hecho, «revisa tu email» y sin botón', f);
  await (await a.p.$('.dip-cc')).screenshot({ path: '.tmp/cuenta/guardada-390.png' });
  const u = await datos(`?email=${encodeURIComponent(email)}`);
  ok(u && u.roles.includes('customer') && !u.empresa, 'cuenta normal de cliente (no de empresa)', u);
  ok(u && u.enlace_clave, 'con el enlace para poner la contraseña (email de cuenta nueva)', u);
  ok(u && u.billing_cp === '08013' && u.billing_nombre === 'Laura García Pons' && u.billing_tel === '600123456', 'con los datos del pedido para el próximo checkout', u);
  let m = await datos(`?pedido=${a.id}`);
  ok(m && u && m.cliente === 0 && m.creada && u.juntar === '1', 'el pedido sigue sin cuenta hasta que entra (si no, WooCommerce le pediría entrar para ver su página de gracias)', { m, u });
  ok(m && m.notas.some((n) => n.startsWith('El cliente ha creado su cuenta desde la página de gracias')), 'con una nota en el pedido', m && m.notas);
  await a.p.reload({ waitUntil: 'networkidle2' });
  ok((await ficha(a.p)).sello === 'Guardada', 'al recargar la página de gracias, la ficha sigue ahí («Guardada»)');
  await datos(`?entrar=${encodeURIComponent(email)}`);
  m = await datos(`?pedido=${a.id}`);
  ok(m && u && m.cliente === u.id, 'la primera vez que entra, el pedido pasa a su cuenta', m);
  ok(!(await datos(`?email=${encodeURIComponent(email)}`)).juntar, 'y solo una vez');
  await a.ctx.close();

  // 3. Otro pedido con el mismo email
  const b = await pedido(email);
  f = await ficha(b.p);
  ok(!f.filas && f.ya.startsWith('Ya tienes cuenta con este email'), '«Ya tienes cuenta con este email» en vez de la ficha', f);
  await b.ctx.close();

  // 4. Sin la clave del pedido no se crea nada
  const c = await pedido(emailFalso);
  const intento = await c.p.evaluate(async () => {
    const form = document.querySelector('.dip-cc__form');
    const fd = new FormData(form);
    fd.set('clave', 'wc_order_falsa');
    const r = await fetch(form.action, { method: 'POST', body: new URLSearchParams(fd), redirect: 'manual' });
    return r.type;
  });
  ok(!(await datos(`?email=${encodeURIComponent(emailFalso)}`)), 'con una clave falsa no se crea la cuenta', intento);
  await c.ctx.close();
} finally {
  await datos('?quitar=' + parche).catch(() => null);
  rmSync(FIXTURE, { force: true });
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-cuenta` : '\nprobar-cuenta: todo correcto');
process.exit(fallos ? 1 : 0);
