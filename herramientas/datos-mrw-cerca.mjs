// Códigos postales «cerca» de una oficina de MRW (11/11/2026), para los suplementos de MRW que dependen de la distancia
// a la oficina que entrega: sábado 5,45 € (cerca) o 9,70 € (a más de 25 km); antes de las 10:00, +15,30 € a más de 25 km.
//
// Cómo: todas las oficinas de MRW de España (página de cada provincia de mrw.es, con su posición exacta en el enlace
// «MAPA»: maps.google.com/maps?ll=LAT,LNG) y la posición de cada código postal y de cada población dentro de él
// (GeoNames, ES.txt, licencia CC BY 4.0, https://download.geonames.org/export/zip/). Un código postal es «cerca» si TODAS
// sus poblaciones están a menos de UMBRAL_KM en línea recta de alguna oficina (18 km en línea recta ≈ 25 km por carretera:
// margen para no cobrar de menos). Lo demás, y los códigos que no salen en GeoNames, es «lejos»: el plugin cobra el
// suplemento de lejos (decidido con el propietario el 11/11/2026).
//
// Uso: node herramientas/datos-mrw-cerca.mjs ruta/a/ES.txt [--html=carpeta]
//   --html=carpeta guarda (o reutiliza, si ya están) las páginas de MRW, para revisarlas o repetir sin volver a pedirlas.
// Escribe plugin/dryicepack-tienda/datos/mrw-cerca.json y .tmp/mrw-oficinas/oficinas.json (para revisar).
// Se regenera cuando MRW abra o cierre oficinas (una vez al año). Pausa de 2 s entre peticiones a mrw.es.
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join } from 'node:path';

const BASE = 'https://www.mrw.es/oficina_transporte_urgente/MRW_franquicia.asp';
const UA = { headers: { 'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36' } };
const UMBRAL_KM = 18;
const PAUSA_MS = 2000;
const SALIDA = 'plugin/dryicepack-tienda/datos/mrw-cerca.json';
const INTERNAS = /^\s*(logistica|logística|corporate|burofax|division|división|tradeinn|plataforma)\b/i;

const args = process.argv.slice(2);
const geonames = args.find((a) => !a.startsWith('--'));
const carpetaHtml = (args.find((a) => a.startsWith('--html=')) || '').slice(7);
if (!geonames || !existsSync(geonames)) throw new Error('Falta la ruta de ES.txt de GeoNames');
if (carpetaHtml) mkdirSync(carpetaHtml, { recursive: true });

const espera = (ms) => new Promise((r) => setTimeout(r, ms));
const limpiar = (s) => s.replace(/<!--[\s\S]*?-->/g, ' ').replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&#39;|&apos;/g, "'").replace(/&quot;/g, '"').replace(/\s+/g, ' ').trim();

async function pagina(nombre, url) {
  const archivo = carpetaHtml ? join(carpetaHtml, nombre + '.html') : '';
  if (archivo && existsSync(archivo)) return readFileSync(archivo, 'utf8');
  const r = await fetch(url, UA);
  if (!r.ok) throw new Error(`MRW respondió ${r.status} en ${url}`);
  const html = await r.text();
  if (archivo) writeFileSync(archivo, html);
  await espera(PAUSA_MS);
  return html;
}

/** Oficinas de la página de una provincia: [{ codigo, nombre, cp, poblacion, provincia, lat, lng }] */
function oficinas(html, provincia) {
  const r = [];
  const trozos = html.split('<!--Datos franquicia-->').slice(1);
  for (const t of trozos) {
    const nombre = (t.match(/<!--Nombre Franquicia-->([\s\S]*?)<\/strong>/) || [])[1];
    const codigo = (t.match(/<!--Codigo franquicia-->\s*-\s*(\d{5})/) || [])[1];
    const dir = (t.match(/<!--Dirección franquicia-->\s*<p>([\s\S]*?)<\/p>/) || [])[1];
    const ll = t.match(/maps\?ll=(-?\d+\.\d+),(-?\d+\.\d+)/);
    if (!nombre || !codigo) continue;
    const d = dir ? limpiar(dir) : '';
    const cp = (d.match(/-\s*(\d{5})\s+([^(]+?)\s*\(/) || []);
    // Código postal español: el de la dirección («- 08301 Mataró (…)», «…, 08150 Parets…»); si no, el del nombre
    // («Barcelona 08007»). Los portugueses son «5000-471» y no cuentan.
    const cpEs = (d.match(/(?<![\d-])(\d{5})(?![\d-])/) || limpiar(nombre).match(/(?<![\d-])(\d{5})(?![\d-])/) || [])[1] || '';
    r.push({
      codigo,
      nombre: limpiar(nombre),
      direccion: d,
      cp: cp[1] || cpEs,
      portugal: /(?<!\d)\d{4}-\d{3}(?!\d)/.test(d),
      poblacion: (cp[2] || '').trim(),
      provincia,
      lat: ll ? Number(ll[1]) : null,
      lng: ll ? Number(ll[2]) : null,
    });
  }
  return r;
}

const rad = (g) => (g * Math.PI) / 180;
function km(a, b) {
  const dLat = rad(b.lat - a.lat), dLng = rad(b.lng - a.lng);
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(dLng / 2) ** 2;
  return 2 * 6371 * Math.asin(Math.sqrt(h));
}

// 1. GeoNames: posición de cada población de cada código postal (península; las islas, Ceuta y Melilla no se envían)
const FUERA = new Set(['07', '35', '38', '51', '52']);
const lugares = new Map(); // cp → [{ lat, lng, nombre }]
for (const linea of readFileSync(geonames, 'utf8').split(/\r?\n/)) {
  const c = linea.split('\t');
  if (c.length < 11 || c[0] !== 'ES' || !/^\d{5}$/.test(c[1]) || FUERA.has(c[1].slice(0, 2))) continue;
  const lat = Number(c[9]), lng = Number(c[10]);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) continue;
  if (!lugares.has(c[1])) lugares.set(c[1], []);
  lugares.get(c[1]).push({ lat, lng, nombre: c[2] });
}
console.log(`GeoNames: ${lugares.size} códigos postales de la península`);

// 2. MRW: lista de provincias y sus oficinas
const portada = await pagina('espana', BASE + '?franquicias_mrw_en=Espana');
const provincias = [...new Set([...portada.matchAll(/\?provincia=([A-Z%0-9ÑÁÉÍÓÚÜ .'-]+)"/g)].map((m) => m[1]))];
if (provincias.length < 45) throw new Error(`La página de MRW ha cambiado: solo ${provincias.length} provincias`);
const todas = [];
const leerProvincia = async (p) => {
  const html = await pagina('prov-' + p.replace(/[^A-Z0-9]/gi, '_'), BASE + '?provincia=' + p);
  const lista = oficinas(html, decodeURIComponent(p));
  if (!lista.length) console.log(`AVISO: ninguna oficina en ${p}`);
  todas.push(...lista);
  return lista;
};
for (const p of provincias) await leerProvincia(p);

// Provincias peninsulares (las de dip_mrw_provincia_por_cp() del plugin, festivos-mrw.php). En la portada de mrw.es,
// «Vizcaya» enlaza a ?provincia=VILAREAL (Vila Real, Portugal; 12/11/2026): por eso, a cada provincia que se quede sin
// oficinas se le pide también su página por el nombre (?provincia=VIZCAYA), y si sigue vacía el script para.
const PROV_CP = {
  '01': 'ALAVA', '02': 'ALBACETE', '03': 'ALICANTE', '04': 'ALMERIA', '05': 'AVILA', '06': 'BADAJOZ', '08': 'BARCELONA',
  '09': 'BURGOS', '10': 'CACERES', '11': 'CADIZ', '12': 'CASTELLON', '13': 'CIUDAD REAL', '14': 'CORDOBA', '15': 'CORUNA',
  '16': 'CUENCA', '17': 'GIRONA', '18': 'GRANADA', '19': 'GUADALAJARA', '20': 'GUIPUZCOA', '21': 'HUELVA', '22': 'HUESCA',
  '23': 'JAEN', '24': 'LEON', '25': 'LLEIDA', '26': 'LA RIOJA', '27': 'LUGO', '28': 'MADRID', '29': 'MALAGA', '30': 'MURCIA',
  '31': 'NAVARRA', '32': 'ORENSE', '33': 'ASTURIAS', '34': 'PALENCIA', '36': 'PONTEVEDRA', '37': 'SALAMANCA', '39': 'CANTABRIA',
  '40': 'SEGOVIA', '41': 'SEVILLA', '42': 'SORIA', '43': 'TARRAGONA', '44': 'TERUEL', '45': 'TOLEDO', '46': 'VALENCIA',
  '47': 'VALLADOLID', '48': 'VIZCAYA', '49': 'ZAMORA', '50': 'ZARAGOZA',
};
// Provincias de España que no son de la península (sus oficinas no reparten a la península, pero son de España)
const ISLAS_Y_CIUDADES = ['BALEARES', 'PALMASLAS', 'TENERIFE', 'CEUTA', 'MELILLA'];
const clave = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase().replace(/[^A-Z]/g, '');
const ESPANA = new Set([...Object.values(PROV_CP), ...ISLAS_Y_CIUDADES].map(clave));
// Oficina de España: de una página de provincia española y sin código postal portugués
const deEspana = (o) => ESPANA.has(clave(o.provincia)) && !o.portugal;
const tieneOficinas = (lista, pref, nombre) => lista.some((o) => deEspana(o) && (o.cp.slice(0, 2) === pref || clave(o.provincia) === clave(nombre)));
for (const [pref, nombre] of Object.entries(PROV_CP)) {
  if (tieneOficinas(todas, pref, nombre)) continue;
  console.log(`AVISO: ${nombre} sin oficinas en la portada de MRW; se pide ?provincia=${nombre}`);
  await leerProvincia(nombre);
  if (!tieneOficinas(todas, pref, nombre)) throw new Error(`La provincia ${nombre} (${pref}xxx) se queda sin oficinas de MRW: revisar la página de MRW`);
}
const fueraDeEspana = todas.filter((o) => !deEspana(o));
if (fueraDeEspana.length) console.log(`Descartadas ${fueraDeEspana.length} oficinas de fuera de España: ${fueraDeEspana.map((o) => o.nombre + ' (' + o.provincia + ')').join(', ')}`);
// Posición: la del enlace «MAPA»; si falta, la de su código postal en GeoNames
const sinPos = [];
for (const o of todas) {
  if (o.lat !== null) continue;
  const l = lugares.get(o.cp);
  if (l) { o.lat = l[0].lat; o.lng = l[0].lng; o.posicion = 'cp'; } else sinPos.push(o);
}
const reparto = todas.filter((o) => o.lat !== null && deEspana(o) && !INTERNAS.test(o.nombre) && !FUERA.has(o.cp.slice(0, 2)));
console.log(`MRW: ${todas.length} oficinas en ${provincias.length} provincias; ${reparto.length} de reparto en la península con posición; ${sinPos.length} sin posición`);
if (reparto.length < 300) throw new Error('Demasiado pocas oficinas: la página de MRW ha cambiado');
for (const [pref, nombre] of Object.entries(PROV_CP)) {
  if (!tieneOficinas(reparto, pref, nombre)) throw new Error(`La provincia ${nombre} (${pref}xxx) no tiene oficinas de reparto con posición: revisar la página de MRW`);
}

// 3. Cada código postal: la distancia de la población más lejana a su oficina más cercana
const cerca = [];
const lejos = [];
const dist = {};
for (const [cp, ls] of lugares) {
  let peor = 0;
  for (const l of ls) {
    let mejor = Infinity;
    for (const o of reparto) { const d = km(l, o); if (d < mejor) mejor = d; }
    if (mejor > peor) peor = mejor;
  }
  dist[cp] = Math.round(peor * 10) / 10;
  if (peor <= UMBRAL_KM) cerca.push(cp); else lejos.push(cp);
}
cerca.sort();
lejos.sort();
console.log(`Cerca (≤ ${UMBRAL_KM} km en línea recta): ${cerca.length} de ${lugares.size} códigos postales`);

// Poblaciones con oficina, por provincia (para los códigos que no salen en GeoNames)
const poblaciones = {};
for (const o of reparto) {
  const p = o.provincia.toUpperCase();
  (poblaciones[p] = poblaciones[p] || []).push(o.poblacion || o.nombre);
}
for (const p of Object.keys(poblaciones)) poblaciones[p] = [...new Set(poblaciones[p])].sort();

writeFileSync(SALIDA, JSON.stringify({
  generado: new Date().toISOString().slice(0, 10),
  criterio: `Código postal «cerca» si todas sus poblaciones (GeoNames) están a ${UMBRAL_KM} km o menos en línea recta de una oficina de MRW (mrw.es, posición del enlace MAPA). Lo demás, lejos.`,
  fuentes: ['https://www.mrw.es/oficina_transporte_urgente/MRW_franquicia.asp', 'GeoNames ES.txt (CC BY 4.0), https://download.geonames.org/export/zip/'],
  umbral_km: UMBRAL_KM,
  oficinas: reparto.length,
  codigos: lugares.size,
  cerca: cerca.join(''), // códigos de 5 cifras seguidos
  lejos: lejos.join(''), // el resto de los que conoce GeoNames (un código que no está en ninguna lista es desconocido)
  poblaciones,
}));
mkdirSync('.tmp/mrw-oficinas', { recursive: true });
writeFileSync('.tmp/mrw-oficinas/oficinas.json', JSON.stringify({ oficinas: todas, sin_posicion: sinPos, distancias: dist }, null, 1));
console.log(`Escrito ${SALIDA}`);
