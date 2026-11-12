// «Avísame» de la landing de Halloween fuera de campaña (plugin, inc/aviso-halloween.php; diseño «La hoja de octubre»)
// en el WordPress de prueba (wordpress-tema, 9500):
// 1. La sección #aviso está fuera de campaña: la hoja del octubre del próximo Halloween (el 31 con aro, «Te escribimos»),
//    el formulario y el enlace del hero. Capturas a 390 y 1440 px en .tmp/aviso/.
// 2. El alta queda sin confirmar; la confirmación pide pulsar un botón (abrir el enlace no confirma); otra alta con el mismo
//    email dice que ya está apuntado; la baja lo quita; una firma falsa da 400; el campo trampa no apunta a nadie.
// 3. Tarea diaria: lo que no se confirma en 7 días se borra; el 1 de septiembre avisa una vez de cuántos esperan.
// Uso: node herramientas/probar-aviso-halloween.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const LANDING = BASE + '/hielo-seco-halloween/';
const FIXTURE = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-aviso.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
mkdirSync('.tmp/aviso', { recursive: true });
copyFileSync('herramientas/fixtures/aviso.php', FIXTURE);
const datos = async (q = '') => (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-aviso.php${q}`)).json();
const marca = Date.now().toString(36);
const email = `aviso-${marca}@example.com`;

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
try {
  await datos('?limpiar_ip=1');
  const { ano } = await datos();
  const p = await nav.newPage();
  p.on('pageerror', (e) => errores.push(e.message));
  // 1. La sección
  for (const [ancho, alto] of [[390, 844], [1440, 900]]) {
    await p.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
    await p.goto(LANDING + '?v=' + marca + ancho, { waitUntil: 'networkidle2', timeout: 180000 });
    const s = await p.evaluate(() => {
      const a = document.getElementById('aviso');
      return a ? {
        ante: a.querySelector('.hw-antetitulo')?.textContent.trim(),
        h2: a.querySelector('h2')?.textContent.trim(),
        dias: a.querySelectorAll('.hw-aviso__dias span:not(.hw-aviso__dsem):not(.hw-aviso__hueco)').length,
        anillo: !!a.querySelector('.hw-aviso__31 .hw-aviso__anillo'),
        pestana: a.querySelector('.hw-aviso__pestana')?.textContent.trim(),
        ticket: document.querySelector('.hw-hero a.hw-ticket--aviso')?.getAttribute('href'),
        ticketTexto: document.querySelector('.hw-hero a.hw-ticket--aviso')?.textContent.replace(/\s+/g, ' ').trim(),
        scroll: document.documentElement.scrollWidth > innerWidth,
      } : null;
    });
    if (ancho === 390) {
      ok(s && s.ante === `Halloween ${ano} · Domingo 31 de octubre` || (s && new RegExp(`^Halloween ${ano} · \\S+ 31 de octubre$`).test(s.ante)), `antetítulo con el año y el día: «${s && s.ante}»`, s);
      ok(s && s.h2 === '¿Lo quieres para el próximo Halloween? Te avisamos.', 'titular', s && s.h2);
      ok(s && s.dias === 31 && s.anillo && /Te escribimos/.test(s.pestana), 'la hoja: 31 días, aro en el 31 y «Te escribimos»', s);
      ok(s && s.ticket === '#aviso' && s.ticketTexto === `¿Lo quieres para Halloween ${ano}? Te avisamos →`, 'el hero lleva al aviso', s);
    }
    ok(s && !s.scroll, `${ancho} px: sin scroll horizontal`);
    const sec = await p.$('#aviso');
    await sec.evaluate((e) => e.scrollIntoView({ block: 'center' }));
    await new Promise((r) => setTimeout(r, 1600));
    await sec.screenshot({ path: `.tmp/aviso/aviso-${ancho}.png` });
  }

  // 2. Alta
  await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await p.goto(LANDING + '?v=alta' + marca, { waitUntil: 'networkidle2', timeout: 180000 });
  await new Promise((r) => setTimeout(r, 3500)); // el antispam pide al menos 3 s
  // Centrado en pantalla, como lo ve quien hace scroll (en el borde lo tapa la cabecera fija)
  await p.evaluate(() => document.querySelector('[data-hw-aviso]').scrollIntoView({ block: 'center' }));
  await new Promise((r) => setTimeout(r, 400));
  await p.type('#hw-aviso-email', email);
  const respuesta = p.waitForResponse((r) => r.url().includes('admin-post.php'), { timeout: 60000 });
  await p.click('[data-hw-aviso] button');
  const r1 = await respuesta;
  await p.waitForFunction(() => { const e = document.querySelector('[data-hw-aviso-estado]'); return e && !e.hidden && e.textContent.trim(); }, { timeout: 30000 }).catch(() => null);
  const tras = await p.evaluate(() => ({ estado: document.querySelector('[data-hw-aviso-estado]')?.textContent.trim(), visible: !document.querySelector('[data-hw-aviso-estado]')?.hidden, form: !document.querySelector('[data-hw-aviso]')?.hidden }));
  const correoOk = r1.status() === 200;
  ok(tras.visible && (correoOk ? tras.estado.startsWith('Revisa tu email') && !tras.form : tras.estado.startsWith('No hemos podido enviarte el email')), `respuesta en la página sin recargar (${correoOk ? 'email enviado' : 'el WordPress de prueba no manda emails'})`, tras);
  let x = await datos(`?email=${encodeURIComponent(email)}`);
  ok(x.entrada && x.entrada.email === email && !x.entrada.confirmado && x.entrada.ano === ano, 'queda apuntado sin confirmar, para el año que toca', x.entrada);

  // Confirmar: abrir el enlace no confirma; el botón sí
  const c = await nav.newPage();
  const g = await c.goto(x.confirmar, { waitUntil: 'networkidle2' });
  ok(g.status() === 200 && (await c.evaluate(() => document.querySelector('h1')?.textContent)) === `¿Confirmas el aviso de Halloween ${ano}?`, 'el enlace de confirmación enseña un botón', g.status());
  x = await datos(`?email=${encodeURIComponent(email)}`);
  ok(x.entrada && !x.entrada.confirmado, 'solo con abrirlo no se confirma');
  await Promise.all([c.waitForNavigation({ waitUntil: 'networkidle2' }), c.click('form button')]);
  ok((await c.evaluate(() => document.querySelector('h1')?.textContent)) === `Hecho: te avisaremos para Halloween ${ano}`, 'con el botón: confirmado');
  x = await datos(`?email=${encodeURIComponent(email)}`);
  ok(x.entrada && /^\d{4}-\d{2}-\d{2} /.test(x.entrada.confirmado), 'queda la fecha de confirmación', x.entrada);
  await c.screenshot({ path: '.tmp/aviso/confirmado-390.png' });

  // Otra alta con el mismo email
  const r2 = await p.evaluate(async (correo) => {
    const f = document.querySelector('[data-hw-aviso]');
    const d = new FormData(f); d.set('email', correo); d.set('ajax', '1');
    const r = await fetch(f.getAttribute('action'), { method: 'POST', body: d, credentials: 'same-origin' });
    return r.json();
  }, email);
  ok(r2.ok && r2.mensaje === `Ya estás apuntado: te escribiremos a principios de octubre de ${ano}.`, 'otra alta con el mismo email: «Ya estás apuntado»', r2);

  // Campo trampa: responde «ok» pero no apunta a nadie
  const trampa = `trampa-${marca}@example.com`;
  await p.evaluate(async (correo) => {
    const f = document.querySelector('[data-hw-aviso]');
    const d = new FormData(f); d.set('email', correo); d.set('web', 'http://spam.example'); d.set('ajax', '1');
    await fetch(f.getAttribute('action'), { method: 'POST', body: d, credentials: 'same-origin' });
  }, trampa);
  ok(!(await datos(`?email=${encodeURIComponent(trampa)}`)).entrada, 'el campo trampa no apunta a nadie');

  // 3. Tarea diaria: el 1/9 avisa una vez de cuántos esperan
  const t = await datos('?tarea=1');
  ok(!t.antes.length && t.asuntos.length === 1 && new RegExp(`^Dryicepack: \\d+ esperan el aviso de Halloween ${ano}$`).test(t.asuntos[0]) && t.recordado === ano, 'el 1 de septiembre, un solo email a la dirección de avisos', t);
  // Sin confirmar en 7 días: se borra
  const vieja = `viejo-${marca}@example.com`;
  await datos(`?vieja=${encodeURIComponent(vieja)}`);
  await datos('?limpiar=1');
  ok(!(await datos(`?email=${encodeURIComponent(vieja)}`)).entrada && !!(await datos(`?email=${encodeURIComponent(email)}`)).entrada, 'lo que lleva 8 días sin confirmar se borra; lo confirmado se queda');

  // Baja
  const b = await nav.newPage();
  await b.goto(x.baja, { waitUntil: 'networkidle2' });
  ok((await b.evaluate(() => document.querySelector('h1')?.textContent)) === '¿Quitamos tu aviso de Halloween?', 'el enlace de baja enseña un botón');
  await Promise.all([b.waitForNavigation({ waitUntil: 'networkidle2' }), b.click('form button')]);
  ok((await b.evaluate(() => document.querySelector('h1')?.textContent)) === 'Hecho: aviso quitado', 'con el botón: aviso quitado');
  ok(!(await datos(`?email=${encodeURIComponent(email)}`)).entrada, 'y ya no está en la lista');
  const falsa = new URL(x.baja); falsa.searchParams.set('firma', '0'.repeat(16));
  const r3 = await b.goto(falsa.href, { waitUntil: 'networkidle2' });
  ok(r3.status() === 400, 'con una firma falsa: 400', r3.status());
} finally {
  rmSync(FIXTURE, { force: true });
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-aviso-halloween` : '\nprobar-aviso-halloween: todo correcto');
process.exit(fallos ? 1 : 0);
