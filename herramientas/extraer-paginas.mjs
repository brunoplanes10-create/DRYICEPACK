// Extrae el HTML propio de cada página de dryicepack.es (las <section> que hay dentro de Divi)
// para llevarlo tal cual al tema nuevo. Pide las páginas con ?nowprocket para recibir el HTML
// sin las reescrituras de WP Rocket (carga diferida, CSS combinado…).
// Uso: node herramientas/extraer-paginas.mjs   → referencia/paginas/<slug>.html
import { mkdirSync, writeFileSync } from 'node:fs';

const PAGINAS = {
  inicio: '/',
  'que-es-el-hielo-seco': '/que-es-el-hielo-seco/',
  'aplicaciones-del-hielo-seco': '/aplicaciones-del-hielo-seco/',
  'envios-y-plazos': '/envios-y-plazos/',
  'seguridad-del-hielo-seco': '/seguridad-del-hielo-seco/',
  'programar-suministro-de-hielo-seco': '/programar-suministro-de-hielo-seco/',
  contacto: '/contacto/',
  'producto-hielo-seco': '/producto/hielo-seco/',
};
const OUT = 'referencia/paginas';
mkdirSync(OUT, { recursive: true });

for (const [slug, ruta] of Object.entries(PAGINAS)) {
  const res = await fetch(`https://dryicepack.es${ruta}?nowprocket=1`, { headers: { 'User-Agent': 'Mozilla/5.0 Chrome/130' } });
  const html = await res.text();
  writeFileSync(`${OUT}/${slug}.completa.html`, html);

  // Secciones propias: cada <section id="…"> de primer nivel dentro del contenido
  const cuerpo = html.split(/<div id="et-main-area"[^>]*>/)[1] || html;
  const secciones = [];
  const re = /<section\b[^>]*\bid="([^"]+)"[^>]*>/g;
  let m;
  while ((m = re.exec(cuerpo))) {
    const inicio = m.index;
    // Busca el cierre equilibrado de esta <section>
    let prof = 0, i = inicio, fin = -1;
    const tag = /<\/?section\b[^>]*>/g;
    tag.lastIndex = inicio;
    let t;
    while ((t = tag.exec(cuerpo))) {
      prof += t[0][1] === '/' ? -1 : 1;
      if (prof === 0) { fin = t.index + t[0].length; break; }
    }
    if (fin < 0) continue;
    secciones.push({ id: m[1], html: cuerpo.slice(inicio, fin) });
    re.lastIndex = fin;
  }
  // Estilos y scripts en línea que vengan dentro del contenido (fuera de las secciones)
  const contenido = cuerpo.split(/<footer\b|<div class="et-l et-l--footer"/)[0];
  const estilos = (contenido.match(/<style\b[^>]*>[\s\S]*?<\/style>/g) || []).filter((s) => !/et-|divi|wp-block/i.test(s.slice(0, 120)));
  const scripts = (contenido.match(/<script\b(?![^>]*application\/ld\+json)[^>]*>[\s\S]*?<\/script>/g) || []);

  const limpio = secciones.map((s) => s.html).join('\n\n')
    .replace(/<!-- \[et_pb_line_break_holder\] -->/g, '\n')
    .replace(/<!--\s*\/?et_pb[^>]*-->/g, '');
  writeFileSync(`${OUT}/${slug}.html`, limpio);
  writeFileSync(`${OUT}/${slug}.extras.html`, [...estilos, ...scripts].join('\n\n'));
  console.log(`${slug.padEnd(36)} ${String(res.status)} · ${secciones.length} secciones (${secciones.map((s) => s.id).join(', ')}) · ${(limpio.length / 1024).toFixed(0)} KB · estilos en línea: ${estilos.length} · scripts en línea: ${scripts.length}`);
}
