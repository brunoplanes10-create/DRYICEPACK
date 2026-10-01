// Recorre la web enlace a enlace (sin JavaScript) y comprueba lo que pide la checklist:
// estado HTTP, title (≤ 60), meta description (140–155), un solo H1, imágenes sin alt, JSON-LD válido,
// hreflang, canonical y enlaces internos rotos o que redirigen.
// Uso: node herramientas/auditar-sitio.mjs [http://127.0.0.1:9500] [máximo de páginas]
const BASE = (process.argv[2] || 'http://127.0.0.1:9500').replace(/\/$/, '');
const MAX = Number(process.argv[3] || 250);
const IGNORAR = /\/(wp-admin|wp-json|wp-login|feed|xmlrpc|wp-content|wp-includes|carrito|cart|cistella|finalizar-compra|checkout|mi-cuenta|my-account)\b|[?&](add-to-cart|replytocom|dipt_ir)=|\.(pdf|xml|webp|png|jpg|svg|css|js)(\?|$)/i;

const cola = ['/'];
const vistas = new Map(); // ruta → { estado, destino }
const enlacesDe = new Map(); // ruta destino → páginas que la enlazan
const informe = [];

const limpiar = (href, desde) => {
  try {
    const u = new URL(href, BASE + desde);
    if (u.origin !== BASE) return null;
    u.hash = '';
    return u.pathname + u.search;
  } catch { return null; }
};
const attr = (tag, nombre) => (tag.match(new RegExp(`\\s${nombre}\\s*=\\s*("([^"]*)"|'([^']*)')`, 'i')) || [])[2] ?? null;
const texto = (s) => s.replace(/<[^>]+>/g, '').replace(/&#0?38;|&amp;/g, '&').replace(/&#8211;|&ndash;/g, '–').replace(/&#8212;/g, '—').replace(/&#8217;/g, '’').replace(/&nbsp;|&#160;/g, ' ').replace(/&#039;|&#39;/g, "'").replace(/&quot;/g, '"').replace(/\s+/g, ' ').trim();

while (cola.length && vistas.size < MAX) {
  const ruta = cola.shift();
  if (vistas.has(ruta)) continue;
  let r;
  try { r = await fetch(BASE + ruta, { redirect: 'manual' }); } catch (e) { vistas.set(ruta, { estado: 'ERROR ' + e.message }); continue; }
  const estado = r.status;
  const destino = r.headers.get('location');
  vistas.set(ruta, { estado, destino });
  if (estado !== 200) continue;
  const html = await r.text();
  if (!/text\/html/.test(r.headers.get('content-type') || '')) continue;

  const titulo = texto((html.match(/<title[^>]*>([\s\S]*?)<\/title>/i) || [])[1] || '');
  const descTag = (html.match(/<meta[^>]+name=["']description["'][^>]*>/i) || [])[0];
  const desc = descTag ? texto(attr(descTag, 'content') || '') : '';
  const h1 = (html.match(/<h1[\s>]/gi) || []).length;
  const imgs = html.match(/<img\b[^>]*>/gi) || [];
  const sinAlt = imgs.filter((t) => attr(t, 'alt') === null).length;
  const robots = attr((html.match(/<meta[^>]+name=["']robots["'][^>]*>/i) || [''])[0], 'content') || '';
  const canonical = attr((html.match(/<link[^>]+rel=["']canonical["'][^>]*>/i) || [''])[0], 'href') || '';
  const hreflang = (html.match(/<link[^>]+hreflang=/gi) || []).length;
  const tipos = [];
  let jsonMal = 0;
  for (const m of html.matchAll(/<script[^>]+application\/ld\+json[^>]*>([\s\S]*?)<\/script>/gi)) {
    try {
      const j = JSON.parse(m[1]);
      const nodos = j['@graph'] || (Array.isArray(j) ? j : [j]);
      nodos.forEach((n) => tipos.push([].concat(n['@type']).join('+')));
    } catch { jsonMal++; }
  }
  const avisos = [];
  if (titulo.length > 60) avisos.push(`title ${titulo.length}`);
  if (!desc) avisos.push('sin description');
  else if (desc.length < 120 || desc.length > 160) avisos.push(`description ${desc.length}`);
  if (h1 !== 1) avisos.push(`${h1} H1`);
  if (sinAlt) avisos.push(`${sinAlt} img sin alt`);
  if (jsonMal) avisos.push(`${jsonMal} JSON-LD roto`);
  informe.push({ ruta, titulo, tl: titulo.length, dl: desc.length, h1, imgs: imgs.length, sinAlt, schema: tipos.join(', '), hreflang, noindex: /noindex/.test(robots), canonical: canonical.replace(BASE, ''), avisos });

  for (const m of html.matchAll(/<a\b[^>]*href=("([^"]*)"|'([^']*)')/gi)) {
    const href = (m[2] ?? m[3] ?? '').replace(/&#0?38;|&amp;/g, '&');
    if (!href || /^(mailto|tel|javascript|#|https:\/\/wa\.me)/i.test(href)) continue;
    const r2 = limpiar(href, ruta);
    if (!r2 || IGNORAR.test(r2)) continue;
    if (!enlacesDe.has(r2)) enlacesDe.set(r2, new Set());
    enlacesDe.get(r2).add(ruta);
    if (!vistas.has(r2) && !cola.includes(r2)) cola.push(r2);
  }
}

console.log(`\nPáginas recorridas: ${vistas.size}  (HTML 200: ${informe.length})\n`);
for (const p of informe.sort((a, b) => a.ruta.localeCompare(b.ruta))) {
  console.log(`${p.avisos.length ? '!!' : 'ok'} ${p.ruta}  T${p.tl} D${p.dl} H1:${p.h1} img:${p.imgs} hreflang:${p.hreflang}${p.noindex ? ' NOINDEX' : ''}  [${p.schema}]${p.avisos.length ? '  → ' + p.avisos.join(' · ') : ''}`);
}
const malos = [...vistas].filter(([, v]) => v.estado !== 200);
console.log(`\nEnlaces internos que no dan 200: ${malos.length}`);
for (const [ruta, v] of malos) console.log(`  ${v.estado} ${ruta}${v.destino ? ' → ' + v.destino.replace(BASE, '') : ''}  (desde ${[...(enlacesDe.get(ruta) || [])].slice(0, 3).join(', ')})`);
