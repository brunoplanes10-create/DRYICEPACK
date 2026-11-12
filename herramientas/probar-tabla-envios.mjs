// «¿Cuánto cuesta cada hora?» de la página de envíos (tema: plantillas/envios.php V3b; plugin: dip_tabla_precios_envio) en el
// WordPress de prueba (wordpress-tema, 9500):
// 1. Cada precio de las cuatro zonas (Mataró, provincia de Barcelona, resto de Cataluña y resto de la península) es el mismo,
//    al céntimo, que calcula el checkout (dip_precio_servicio) para un código postal de esa zona.
// 2. Pestañas: de serie la provincia de Barcelona; con clic y con las flechas se cambia de zona y solo se ve su tabla.
// 3. La nota dice lo que suma estar lejos de una oficina de MRW (10:00 y sábado). Castellano, catalán e inglés.
// 4. Sin scroll lateral a 390 px y, en móvil, cada precio lleva su hora al lado. Capturas en .tmp/tabla/.
// Uso: node herramientas/probar-tabla-envios.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync, mkdirSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const FIXTURE = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-tabla-envios.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
const limpio = (s) => (s || '').replace(/\u00A0/g, ' ').replace(/\s+/g, ' ').trim();
const eurEs = (n) => n == null ? '—' : n.toFixed(2).replace('.', ',') + ' €';
const eurEn = (n) => n == null ? '—' : '€' + n.toFixed(2);
mkdirSync('.tmp/tabla', { recursive: true });
copyFileSync('herramientas/fixtures/tabla-envios.php', FIXTURE);
let esperado;
try { esperado = await (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-tabla-envios.php`)).json(); } finally { rmSync(FIXTURE, { force: true }); }
const ZONAS = ['urbano', 'provincial', 'regional', 'peninsular'];
const SERV = ['14', '12', '10', 'sabado'];

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
try {
  const p = await nav.newPage();
  p.on('pageerror', (e) => errores.push(e.message));
  await p.setViewport({ width: 1440, height: 900 });
  await p.goto(BASE + '/envios-y-plazos/', { waitUntil: 'load', timeout: 120000 });
  const leer = () => p.evaluate(() => {
    const s = document.querySelector('[data-horas]');
    if (!s) return null;
    return {
      titulo: s.querySelector('h2')?.textContent.trim(),
      pestanas: [...s.querySelectorAll('[data-horas-zona]')].map((b) => ({ z: b.getAttribute('data-horas-zona'), sel: b.getAttribute('aria-selected'), tab: b.tabIndex, texto: b.textContent.trim() })),
      visibles: [...s.querySelectorAll('[data-horas-panel]')].filter((x) => !x.hidden).map((x) => x.getAttribute('data-horas-panel')),
      tablas: Object.fromEntries([...s.querySelectorAll('[data-horas-panel]')].map((x) => [x.getAttribute('data-horas-panel'), [...x.querySelectorAll('tbody tr')].map((tr) => [tr.querySelector('th').textContent.trim(), ...[...tr.querySelectorAll('td')].map((td) => td.textContent.trim())])])),
      nota: s.querySelector('.v-horas__nota')?.textContent.trim(),
      barata: s.querySelector('thead .v-horas__barata small')?.textContent.trim(),
    };
  });
  let t = await leer();
  ok(t && t.titulo === '¿Cuánto cuesta cada hora?', 'la sección está en la página de envíos', t && t.titulo);
  ok(t && t.pestanas.length === 4 && t.pestanas.find((x) => x.z === 'provincial')?.sel === 'true' && t.visibles.join() === 'provincial', 'cuatro pestañas; de serie, la provincia de Barcelona y solo su tabla', t && { pestanas: t.pestanas, visibles: t.visibles });
  ok(t && t.barata === 'de serie · la más barata entre semana', 'la de las 14:00, «de serie · la más barata entre semana»', t && t.barata);
  // Precios: los del checkout, al céntimo
  const distintos = [];
  for (const z of ZONAS) {
    esperado.packs.forEach((kg, i) => {
      const fila = t.tablas[z][i];
      SERV.forEach((s, j) => {
        const e = esperado.zonas[z][String(kg)][s];
        const quiero = eurEs(e ? e.con_iva : null);
        if (limpio(fila[j + 1]) !== quiero) distintos.push(`${z} ${kg} kg ${s}: página ${fila[j + 1]}, checkout ${quiero}`);
      });
    });
  }
  ok(!distintos.length, `los ${ZONAS.length * esperado.packs.length * SERV.length} precios son los del checkout, al céntimo`, distintos.slice(0, 5));
  ok(t && limpio(t.nota).includes(eurEs(esperado.lejos['10'])) && limpio(t.nota).includes(eurEs(esperado.lejos.sabado)), `la nota dice lo que suma estar lejos (${eurEs(esperado.lejos['10'])} y ${eurEs(esperado.lejos.sabado)})`, t && t.nota);
  // Pestañas: clic y flechas
  await p.click('[data-horas-zona="peninsular"]');
  t = await leer();
  ok(t.visibles.join() === 'peninsular' && t.pestanas.find((x) => x.z === 'peninsular').sel === 'true', 'con clic: resto de la península', t.visibles);
  await p.focus('[data-horas-zona="peninsular"]');
  await p.keyboard.press('ArrowRight');
  t = await leer();
  const activo = await p.evaluate(() => document.activeElement?.getAttribute('data-horas-zona'));
  ok(t.visibles.join() === 'urbano' && activo === 'urbano', 'con la flecha derecha (desde la última): vuelve a Mataró con el foco', { visibles: t.visibles, activo });
  await p.keyboard.press('ArrowLeft');
  t = await leer();
  ok(t.visibles.join() === 'peninsular', 'con la flecha izquierda: otra vez la península', t.visibles);
  await (await p.$('[data-horas]')).screenshot({ path: '.tmp/tabla/tabla-1440.png' });
  // Móvil
  await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
  await p.goto(BASE + '/envios-y-plazos/', { waitUntil: 'load', timeout: 120000 });
  const movil = await p.evaluate(() => {
    const td = document.querySelector('[data-horas-panel]:not([hidden]) td');
    return { scroll: document.documentElement.scrollWidth > innerWidth, etiqueta: td ? getComputedStyle(td, '::before').content : '' };
  });
  ok(!movil.scroll, '390 px: sin scroll lateral', movil);
  ok(/Antes de las 14:00/.test(movil.etiqueta), '390 px: cada precio lleva su hora al lado', movil.etiqueta);
  await (await p.$('[data-horas]')).screenshot({ path: '.tmp/tabla/tabla-390.png' });
  // Catalán e inglés
  for (const [ruta, titulo, fmt] of [['/ca/enviaments/', 'Quant costa cada hora?', eurEs], ['/en/delivery/', 'How much does each delivery time cost?', eurEn]]) {
    await p.goto(BASE + ruta, { waitUntil: 'load', timeout: 120000 });
    const x = await leer();
    const primero = esperado.zonas.provincial[String(esperado.packs[0])]['14'].con_iva;
    ok(x && x.titulo === titulo && limpio(x.tablas.provincial[0][1]) === fmt(primero), `${ruta}: «${titulo}» y los precios en su formato (${fmt(primero)})`, x && { titulo: x.titulo, primero: x.tablas.provincial[0][1] });
  }
  // Sin JavaScript: las cuatro tablas, cada una con su zona
  const sinJs = await nav.newPage();
  await sinJs.setJavaScriptEnabled(false);
  await sinJs.goto(BASE + '/envios-y-plazos/', { waitUntil: 'load', timeout: 120000 });
  const s = await sinJs.evaluate(() => ({ paneles: [...document.querySelectorAll('[data-horas-panel]')].filter((x) => !x.hidden).length, pestanas: !!document.querySelector('[data-horas-pestanas]:not([hidden])'), captions: [...document.querySelectorAll('.v-horas__tabla caption')].map((c) => c.textContent.trim()) }));
  ok(s.paneles === 4 && !s.pestanas && s.captions.join('|') === 'Mataró|Provincia de Barcelona|Resto de Cataluña|Resto de la península', 'sin JavaScript: las cuatro tablas seguidas, cada una con su zona', s);
  await sinJs.close();
} finally {
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-tabla-envios` : '\nprobar-tabla-envios: todo correcto');
process.exit(fallos ? 1 : 0);
