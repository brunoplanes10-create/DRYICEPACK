// «Repetir el pedido» (inc/ventas.php, 7.3) de punta a punta en el WordPress de prueba (servidor wordpress-tema, 9500):
// 1. Un pedido normal (20 kg · 3 mm a Barcelona) hasta la página de gracias, que da el enlace a «Mi pedido».
// 2. Desde otro navegador sin sesión (como el cliente desde el móvil): «Mi pedido» enseña «Repetir el pedido»; con una
//    firma falsa no se toca nada; el botón lleva al checkout con el mismo pack y la misma dirección, pero sin nombre, email
//    ni teléfono (esa página no los enseña); y el pedido nuevo guarda _dip_repetido_de con una nota.
// Capturas del botón en «Mi pedido» a 390 y 1440 px en .tmp/repetir/. Sale con código 1 si algo falla.
// Uso: node herramientas/probar-repetir.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const FIXTURE = 'plugin/dryicepack-tienda/zz-meta-pedido.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
const limpio = (s) => (s || '').replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
mkdirSync('.tmp/repetir', { recursive: true });
copyFileSync('herramientas/fixtures/meta-pedido.php', FIXTURE);
const meta = async (id) => (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-meta-pedido.php?pedido=${id}`)).json();

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
try {
  // ---------- 1. Pedido original ----------
  const ctxA = await nav.createBrowserContext();
  const a = await ctxA.newPage();
  a.on('pageerror', (e) => errores.push('A: ' + e.message));
  await a.setViewport({ width: 1280, height: 900 });
  const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
  const v = prod.variations.find((x) => x.attributes.some((at) => at.value === '20kg-de-hielo-seco') && x.attributes.some((at) => at.value === '3mm'));
  await a.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=20kg-de-hielo-seco&attribute_pa_formato=3mm&quantity=1`, { waitUntil: 'networkidle2', timeout: 180000 });
  await a.goto(BASE + '/checkout/', { waitUntil: 'networkidle2', timeout: 180000 });
  const espera = (p) => p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 30000 }).catch(() => null);
  const escribir = async (p, sel, txt) => { await p.click(sel, { clickCount: 3 }); await p.type(sel, txt); };
  await escribir(a, '#billing_email', 'repetir@example.com');
  await escribir(a, '#billing_phone', '600123456');
  await escribir(a, '#billing_first_name', 'Laura García Pons');
  await escribir(a, '#billing_address_1', 'Carrer de Mallorca 123');
  await escribir(a, '#billing_address_2', '2º 1ª');
  await escribir(a, '#billing_postcode', '08013');
  await escribir(a, '#billing_city', 'Barcelona');
  await a.evaluate(() => document.activeElement.blur());
  await espera(a); await new Promise((r) => setTimeout(r, 800));
  await a.evaluate(() => document.getElementById('place_order').click());
  try { await a.waitForNavigation({ timeout: 40000 }); } catch (e) { /* la página de gracias puede tardar */ }
  await new Promise((r) => setTimeout(r, 1500));
  const gracias = await a.evaluate(() => ({ url: location.href, mp: document.querySelector('.dip-mp-gracias a')?.href || '' }));
  const original = Number((gracias.url.match(/order-received\/(\d+)/) || [])[1] || 0);
  ok(original > 0 && gracias.mp.includes('firma='), 'pedido original creado y la página de gracias enlaza a «Mi pedido»', gracias);
  await ctxA.close();

  // ---------- 2. Desde otro navegador, sin sesión ----------
  const ctxB = await nav.createBrowserContext();
  const b = await ctxB.newPage();
  b.on('pageerror', (e) => errores.push('B: ' + e.message));
  for (const [ancho, alto] of [[390, 844], [1440, 900]]) {
    await b.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
    await b.goto(gracias.mp, { waitUntil: 'networkidle2', timeout: 180000 });
    const boton = await b.evaluate(() => {
      const el = document.querySelector('.mp-repetir a');
      return el ? { texto: el.textContent.trim(), href: el.getAttribute('href'), nota: document.querySelector('.mp-repetir__nota')?.textContent.trim(), scroll: document.documentElement.scrollWidth > innerWidth } : null;
    });
    if (ancho === 390) {
      ok(boton && boton.texto === 'Repetir el pedido' && /[?&]repetir=1/.test(boton.href) && /firma=/.test(boton.href), '«Mi pedido» enseña «Repetir el pedido» con el enlace firmado', boton);
      ok(boton && boton.nota === 'Con los mismos packs y la misma dirección. Eliges el día al pagar.', 'con la nota de qué hace', boton && boton.nota);
    }
    ok(boton && !boton.scroll, `${ancho} px: sin scroll horizontal`);
    const seccion = await b.$('.mp-pedido');
    await seccion.evaluate((s) => s.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 400));
    await seccion.screenshot({ path: `.tmp/repetir/mi-pedido-${ancho}.png` });
  }
  const repetir = new URL(await b.evaluate(() => document.querySelector('.mp-repetir a').href));

  // Firma falsa: no toca el carrito ni redirige
  const falsa = new URL(repetir); falsa.searchParams.set('firma', 'a'.repeat(64));
  const r404 = await b.goto(falsa.href, { waitUntil: 'networkidle2', timeout: 180000 });
  ok(r404.status() === 404 && !/checkout|finalizar/.test(b.url()), 'con una firma falsa: 404 y sin ir al checkout', { estado: r404.status(), url: b.url() });

  // El botón
  await b.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await b.goto(repetir.href, { waitUntil: 'networkidle2', timeout: 180000 });
  await b.waitForFunction(() => !document.querySelector('.blockUI'), { timeout: 60000 }).catch(() => null);
  await new Promise((r) => setTimeout(r, 1500));
  const co = await b.evaluate(() => ({
    url: location.href,
    aviso: [...document.querySelectorAll('.woocommerce-message, .woocommerce-info')].map((m) => m.textContent.trim()).join(' | '),
    lineas: [...document.querySelectorAll('.woocommerce-checkout-review-order-table .cart_item .product-name')].map((m) => m.textContent.replace(/\s+/g, ' ').trim()),
    campos: Object.fromEntries(['billing_email', 'billing_phone', 'billing_first_name', 'billing_address_1', 'billing_address_2', 'billing_postcode', 'billing_city'].map((n) => [n, document.getElementById(n)?.value ?? null])),
    recogida: !!document.querySelector('.dip-metodo--recogida input:checked'),
    fecha: document.querySelector('input[name="dip_fecha"]:checked')?.value || '',
  }));
  ok(/checkout|finalizar/.test(co.url), 'lleva al checkout', co.url);
  ok(limpio(co.aviso).includes(`Tienes en el carrito lo mismo que en el pedido ${original}, con la misma dirección. Elige el día de entrega y paga.`), 'aviso de lo que ha hecho, con el número del pedido', co.aviso);
  ok(co.lineas.length === 1 && /20/.test(co.lineas[0]) && /3 ?mm/.test(co.lineas[0]), 'el mismo pack: 20 kg · 3 mm', co.lineas);
  ok(co.campos.billing_address_1 === 'Carrer de Mallorca 123' && co.campos.billing_address_2 === '2º 1ª' && co.campos.billing_postcode === '08013' && co.campos.billing_city === 'Barcelona', 'la misma dirección, piso, código postal y población', co.campos);
  ok(co.campos.billing_email === '' && co.campos.billing_phone === '' && co.campos.billing_first_name === '', 'sin nombre, email ni teléfono (la página de «Mi pedido» no los enseña)', co.campos);
  ok(!co.recogida && !!co.fecha, 'envío, como el original, con el primer día ya elegido', { recogida: co.recogida, fecha: co.fecha });

  // Se paga y el pedido nuevo recuerda de cuál viene
  await escribir(b, '#billing_email', 'repetir@example.com');
  await escribir(b, '#billing_phone', '600123456');
  await escribir(b, '#billing_first_name', 'Laura García Pons');
  await b.evaluate(() => document.activeElement.blur());
  await espera(b); await new Promise((r) => setTimeout(r, 800));
  await b.evaluate(() => document.getElementById('place_order').click());
  try { await b.waitForNavigation({ timeout: 40000 }); } catch (e) { /* la página de gracias puede tardar */ }
  await new Promise((r) => setTimeout(r, 1500));
  const nuevo = Number((b.url().match(/order-received\/(\d+)/) || [])[1] || 0);
  ok(nuevo > 0 && nuevo !== original, 'el pedido repetido se crea', b.url());
  const m = nuevo ? await meta(nuevo) : null;
  ok(m && String(m.repetido_de) === String(original), `el pedido nuevo guarda _dip_repetido_de = ${original}`, m && m.repetido_de);
  ok(m && m.notas.some((n) => n === `Repite el pedido ${original} (botón «Repetir el pedido» de «Mi pedido»).`), 'y una nota para el propietario', m && m.notas);
  ok(m && m.lineas.length === 1 && m.lineas[0][1] === 1 && m.cp === '08013' && m.direccion === 'Carrer de Mallorca 123', 'mismo pack, cantidad y dirección en el pedido nuevo', m);
  const mo = original ? await meta(original) : null;
  ok(mo && !mo.repetido_de, 'el original no se marca como repetido', mo && mo.repetido_de);
  await ctxB.close();
} finally {
  rmSync(FIXTURE, { force: true });
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-repetir` : '\nprobar-repetir: todo correcto');
process.exit(fallos ? 1 : 0);
