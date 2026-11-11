// Botones con el texto pegado al borde (11/11/2026: «asegúrate de que no te pase nunca»).
// Recorre la web enlace a enlace en el WordPress de prueba (o la URL que se le pase), abre cada página a 390 y a 1440 px
// y mide, en cada botón con caja visible (.boton, .hw-btn, .button, button, input de envío), el hueco entre el texto
// (o la flecha) y su borde: al menos 12 px a cada lado; con la flecha del tema (.boton__flecha) a la derecha, 4 px.
// También el checkout con un pack en el carrito y, si se pasan, archivos sueltos (p. ej. los bocetos de zz-bocetos).
// Sale con código 1 si encuentra alguno.
// Uso: node herramientas/revisar-botones.mjs [http://127.0.0.1:9500] [máximo de páginas] [rutas extra separadas por comas]
import puppeteer from 'puppeteer-core';

const BASE = (process.argv[2] || 'http://127.0.0.1:9500').replace(/\/$/, '');
const MAX = Number(process.argv[3] || 80);
const EXTRA = (process.argv[4] || '').split(',').filter(Boolean);
const IGNORAR = /\/(wp-admin|wp-json|wp-login|feed|xmlrpc|wp-content|wp-includes|carrito|cart|cistella|finalizar-compra|checkout|mi-cuenta|my-account)\b|[?&](add-to-cart|replytocom|dipt_ir|dipt_pedido)=|\.(pdf|xml|webp|png|jpg|svg|css|js)(\?|$)/i;

// 1. Las páginas, siguiendo los enlaces desde la portada (sin JavaScript, como el auditor)
const cola = ['/'];
const vistas = new Set();
const paginas = [];
while (cola.length && paginas.length < MAX) {
  const ruta = cola.shift();
  if (vistas.has(ruta)) continue;
  vistas.add(ruta);
  let r;
  try { r = await fetch(BASE + ruta, { redirect: 'manual' }); } catch { continue; }
  if (r.status !== 200 || !/text\/html/.test(r.headers.get('content-type') || '')) continue;
  paginas.push(ruta);
  const html = await r.text();
  for (const m of html.matchAll(/<a\s[^>]*href=["']([^"'#]+)["']/gi)) {
    try {
      const u = new URL(m[1], BASE + ruta);
      if (u.origin !== BASE) continue;
      const r2 = u.pathname + u.search;
      if (!IGNORAR.test(r2) && !vistas.has(r2) && !cola.includes(r2)) cola.push(r2);
    } catch { /* enlace raro */ }
  }
}
paginas.push(...EXTRA);

// 2. La medida, dentro de la página
const medir = () => {
  const malos = [];
  for (const b of document.querySelectorAll('.boton, .hw-btn, .button, button, input[type="submit"], input[type="button"]')) {
    if (b.closest('[hidden], [aria-hidden="true"]')) continue;
    const cs = getComputedStyle(b);
    if (cs.display === 'none' || cs.visibility === 'hidden' || +cs.opacity === 0) continue;
    const r = b.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) continue;
    const texto = ((b.textContent || '') + (b.value || '')).replace(/\s+/g, ' ').trim();
    if (!texto) continue; // solo icono
    // Sin caja (ni fondo, ni borde, ni sombra): parece un enlace y no tiene borde que tocar
    const caja = cs.backgroundColor !== 'rgba(0, 0, 0, 0)' || parseFloat(cs.borderLeftWidth) > 0 || parseFloat(cs.borderRightWidth) > 0 || cs.backgroundImage !== 'none' || cs.boxShadow !== 'none';
    if (!caja) continue;
    let izq, der;
    if (b.tagName === 'INPUT') {
      izq = parseFloat(cs.paddingLeft); der = parseFloat(cs.paddingRight);
    } else {
      const rango = document.createRange();
      rango.selectNodeContents(b);
      const cajas = [...rango.getClientRects()].filter((x) => x.width > .5 && x.height > .5);
      if (!cajas.length) continue;
      izq = Math.min(...cajas.map((x) => x.left)) - r.left;
      der = r.right - Math.max(...cajas.map((x) => x.right));
    }
    const flecha = !!b.querySelector(':scope > .boton__flecha');
    if (izq < 12 || der < (flecha ? 4 : 12)) {
      malos.push({ texto: texto.slice(0, 40), clase: (b.className && b.className.baseVal === undefined ? b.className : '').slice(0, 60), izquierda: Math.round(izq), derecha: Math.round(der) });
    }
  }
  return malos;
};

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const problemas = [];
async function revisar(p, etiqueta) {
  for (const m of await p.evaluate(medir)) problemas.push(`${etiqueta}  «${m.texto}» (${m.clase || 'sin clase'}): ${m.izquierda} px a la izquierda, ${m.derecha} px a la derecha`);
}
const VISTAS = [[390, 844], [1440, 900]];
// Dos páginas a la vez. Se espera a la carga (no a que la red se calle: los vídeos en bucle no la dejan callar nunca)
console.log(`Páginas encontradas: ${paginas.length}. Revisando a 390 y 1440 px…`);
const pendientes = [...paginas];
let hechas = 0;
// Sin fotos ni vídeos (no cambian el relleno de un botón y saturan el servidor de prueba); sí CSS, JS y fuentes
async function paginaLigera() {
  const p = await nav.newPage();
  await p.setRequestInterception(true);
  p.on('request', (q) => (['image', 'media'].includes(q.resourceType()) ? q.abort() : q.continue()));
  return p;
}
await Promise.all(Array.from({ length: 2 }, async () => {
  const p = await paginaLigera();
  while (pendientes.length) {
    const ruta = pendientes.shift();
    for (const [ancho, alto] of VISTAS) {
      try {
        await p.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
        await p.goto(/^https?:/.test(ruta) ? ruta : BASE + ruta, { waitUntil: 'load', timeout: 45000 });
        await new Promise((r) => setTimeout(r, 600));
        await revisar(p, `${ancho} ${ruta}`);
      } catch (e) { problemas.push(`${ancho} ${ruta}: no se pudo abrir (${e.message.split('\n')[0]})`); }
    }
    if (++hechas % 10 === 0) console.log(`  ${hechas} de ${paginas.length}`);
  }
  await p.close();
}));

// 3. El checkout, con un pack en el carrito y el código postal escrito (botón de pagar, pack siguiente, etc.)
try {
  const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
  const v = prod.variations.find((x) => x.attributes.some((a) => a.value === '10kg-de-hielo-seco') && x.attributes.some((a) => a.value === '3mm'));
  for (const [ancho, alto] of VISTAS) {
    const ctx = await nav.createBrowserContext();
    const p = await ctx.newPage();
    await p.setViewport({ width: ancho, height: alto, isMobile: ancho < 768, hasTouch: ancho < 768 });
    await p.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=10kg-de-hielo-seco&attribute_pa_formato=3mm&quantity=1`, { waitUntil: 'load', timeout: 90000 });
    await p.goto(BASE + '/checkout/', { waitUntil: 'load', timeout: 90000 });
    await p.evaluate(() => { const e = document.getElementById('billing_postcode'); if (e) { e.value = '08013'; e.dispatchEvent(new Event('change', { bubbles: true })); } if (window.jQuery) jQuery(document.body).trigger('update_checkout'); });
    await p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 60000 }).catch(() => null);
    await new Promise((r) => setTimeout(r, 1500));
    await revisar(p, `${ancho} /checkout/`);
    await ctx.close();
  }
} catch (e) { problemas.push('checkout: no se pudo revisar (' + e.message.split('\n')[0] + ')'); }
await nav.close();

console.log(`Páginas revisadas: ${paginas.length} (+ checkout), a 390 y 1440 px`);
if (problemas.length) {
  console.log(`\nBotones con el texto pegado al borde: ${problemas.length}`);
  for (const x of problemas) console.log('  ' + x);
  process.exit(1);
}
console.log('Ningún botón con el texto pegado al borde');
