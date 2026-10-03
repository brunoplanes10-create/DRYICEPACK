// Lista de oficinas de MRW por provincia, sacada del calendario de festividades de varios festivos nacionales
// (esos días salen las oficinas que cierran). La usa el plugin para saber cuántas oficinas tiene cada provincia
// (si cierran casi todas, la provincia entera no reparte) y qué poblaciones tienen oficina propia.
//
// Un solo día puede quedarse corto: MRW lista las oficinas que han dado aviso ese día, y una que falte rebaja
// la cifra de su provincia (y entonces cerrar una o dos oficinas parecería "toda la provincia cerrada").
// Por eso se leen varios días y, por provincia, se guarda el MÁXIMO de oficinas que ha salido en un mismo día
// y la unión de los nombres. Un día con menos de 300 oficinas no se usa (no es festivo en toda España:
// el 1/11/2026 cae en domingo y MRW solo lista 45). Las oficinas sin provincia no cuentan para ninguna.
//
// Se regenera una vez al año, con una pausa de 2 s entre peticiones para no cargar la web de MRW.
// Uso: node herramientas/datos-mrw.mjs [AAAA-MM-DD … | pagina.html …] [--guardar=carpeta]
//   Sin fechas: 12/10, 1/11 y 8/12 de este año. Un .html es una página de MRW ya descargada (sin conexión).
//   --guardar=carpeta guarda las páginas descargadas, para revisarlas o repetir sin volver a pedirlas.
import { writeFileSync, mkdirSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const BASE = 'https://www.mrw.es/oficina_transporte_urgente/MRW_festividades.asp';
const MINIMO = 300; // un festivo nacional cierra más de 300 oficinas en España
const PAUSA_MS = 2000;
const SALIDA = 'plugin/dryicepack-tienda/datos/mrw-oficinas.json';

const args = process.argv.slice(2);
const guardar = (args.find((a) => a.startsWith('--guardar=')) || '').slice(10);
const año = new Date().getFullYear();
let fuentes = args.filter((a) => !a.startsWith('--'));
if (!fuentes.length) fuentes = [`${año}-10-12`, `${año}-11-01`, `${año}-12-08`];
for (const f of fuentes) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(f) && !f.endsWith('.html')) throw new Error(`No entiendo "${f}": escribe AAAA-MM-DD o el nombre de un .html`);
}

const espera = (ms) => new Promise((r) => setTimeout(r, ms));
const limpiar = (s) => s.replace(/&amp;/g, '&').replace(/&#39;|&apos;/g, "'").replace(/&quot;/g, '"').replace(/\s+/g, ' ').trim();

/** Oficinas de una página: { dia (el que marca MRW, si lo marca), oficinas: [[PROVINCIA, nombre], …] } */
function analizar(html) {
  if (!html.includes('festividadesTb')) throw new Error('La página de MRW ha cambiado: no encuentro el calendario');
  const sel = html.match(/class="[^"]*\bis-sel\b[^"]*"[^>]*>\s*<a[^>]*\bdia=(\d{1,2})&(?:amp;)?mes=(\d{1,2})&(?:amp;)?any=(\d{4})/);
  const dia = sel ? `${sel[3]}-${sel[2].padStart(2, '0')}-${sel[1].padStart(2, '0')}` : '';
  const re = /loc-festividades is-(\w+)[\s\S]*?loc-title">\s*<a[^>]*>([^<]*)<\/a>[\s\S]*?Provincia<\/strong>:\s*([^<\n]*)/g;
  const oficinas = [];
  for (let x; (x = re.exec(html)); ) oficinas.push([limpiar(x[3]), limpiar(x[2])]);
  return { dia, oficinas };
}

const lecturas = [];
const descartados = [];
let pedidas = 0;
for (const fuente of fuentes) {
  let html, url, dia;
  const urlDe = (ymd) => { const [a, m, d] = ymd.split('-'); return `${BASE}?dia=${d}&mes=${m}&any=${a}`; };
  if (fuente.endsWith('.html')) {
    html = readFileSync(fuente, 'utf8');
    // Copia guardada con --guardar (mrw-AAAA-MM-DD.html): la fecha sale del nombre
    const f = fuente.match(/(\d{4}-\d{2}-\d{2})\.html$/);
    dia = f ? f[1] : undefined;
    url = dia ? urlDe(dia) : fuente;
  } else {
    dia = fuente;
    url = urlDe(dia);
    if (pedidas++) await espera(PAUSA_MS);
    const r = await fetch(url, { headers: { 'User-Agent': 'DryIcePack/1.0 (+https://dryicepack.es; info@dryicepack.es)' } });
    if (!r.ok) { descartados.push({ dia, motivo: `MRW respondió ${r.status}` }); console.warn(`${dia}: MRW respondió ${r.status}, no se usa`); continue; }
    html = await r.text();
    if (guardar) { mkdirSync(guardar, { recursive: true }); writeFileSync(join(guardar, `mrw-${dia}.html`), html); }
  }
  const p = analizar(html);
  if (dia && p.dia && p.dia !== dia) { descartados.push({ dia, motivo: `MRW devolvió el ${p.dia}` }); console.warn(`${dia}: MRW ha devuelto el ${p.dia}, no se usa`); continue; }
  dia = dia || p.dia || fuente;
  if (p.oficinas.length < MINIMO) {
    descartados.push({ dia, motivo: `solo ${p.oficinas.length} oficinas: no es festivo en toda España` });
    console.warn(`${dia}: solo ${p.oficinas.length} oficinas (menos de ${MINIMO}), no se usa`);
    continue;
  }
  lecturas.push({ dia, fuente: url, oficinas: p.oficinas });
  console.log(`${dia}: ${p.oficinas.length} oficinas`);
}
if (!lecturas.length) throw new Error('Ningún día con oficinas suficientes: ¿son festivos nacionales?');

// Por provincia: el máximo de oficinas de un mismo día y la unión de los nombres
// (un nombre repetido el mismo día, como "Logistica (Madrid)", son varias oficinas: se guarda tantas veces como más haya salido)
const provincias = {};
const sinProvincia = new Set();
for (const l of lecturas) {
  const delDia = {};
  for (const [prov, nombre] of l.oficinas) {
    if (prov) (delDia[prov] ??= []).push(nombre);
    else sinProvincia.add(nombre);
  }
  for (const [prov, nombres] of Object.entries(delDia)) {
    const p = (provincias[prov] ??= { oficinas: 0, nombres: [] });
    p.oficinas = Math.max(p.oficinas, nombres.length);
    const veces = {};
    for (const n of nombres) {
      veces[n] = (veces[n] || 0) + 1;
      if (p.nombres.filter((x) => x === n).length < veces[n]) p.nombres.push(n);
    }
  }
}
const ordenadas = Object.fromEntries(Object.keys(provincias).sort().map((k) => [k, provincias[k]]));
const total = Object.values(ordenadas).reduce((s, p) => s + p.oficinas, 0);

// Comparación con el archivo anterior, para revisar el cambio
let anterior = {};
try { anterior = JSON.parse(readFileSync(SALIDA, 'utf8')).provincias || {}; } catch { /* primera vez */ }
const cambios = Object.entries(ordenadas)
  .filter(([k, p]) => (anterior[k]?.oficinas ?? 0) !== p.oficinas)
  .map(([k, p]) => `${k} ${anterior[k]?.oficinas ?? 0} → ${p.oficinas}`);

mkdirSync('plugin/dryicepack-tienda/datos', { recursive: true });
writeFileSync(SALIDA, JSON.stringify({
  generado: new Date().toISOString().slice(0, 10),
  criterio: 'Por provincia, el máximo de oficinas listadas en un mismo festivo nacional y la unión de los nombres',
  dias: lecturas.map((l) => ({ dia: l.dia, oficinas: l.oficinas.length, fuente: l.fuente })),
  descartados,
  sin_provincia: [...sinProvincia],
  total,
  provincias: ordenadas,
}, null, 1) + '\n');
console.log(`${total} oficinas en ${Object.keys(ordenadas).length} provincias (${lecturas.map((l) => l.dia).join(', ')}) → ${SALIDA}`);
if (sinProvincia.size) console.log(`Sin provincia (no cuentan): ${[...sinProvincia].join(', ')}`);
console.log(cambios.length ? `Cambian ${cambios.length} provincias: ${cambios.join(' · ')}` : 'Ninguna provincia cambia respecto al archivo anterior');
