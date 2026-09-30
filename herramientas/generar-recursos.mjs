// Genera los recursos gráficos propios del tema:
//  - Textura granulada de caja EPS (porexpán) para la caja 3D
//  - Mapa de la península en SVG (Natural Earth 1:50m) con Mataró y ciudades de destino, para las rutas animadas
// Uso: node herramientas/generar-recursos.mjs
import sharp from 'sharp';
import { mkdirSync, writeFileSync, existsSync, readFileSync, statSync } from 'node:fs';

const IMG = 'tema/dryicepack/assets/img';
const PARCIALES = 'tema/dryicepack/partes';
mkdirSync(IMG, { recursive: true });
mkdirSync(PARCIALES, { recursive: true });
const kb = (p) => (statSync(p).size / 1024).toFixed(1) + ' KB';

/* 1 · Textura EPS: bolitas de poliestireno expandido (ruido fino + relieve suave), en mosaico sin costura */
const eps = `<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256">
  <filter id="b" x="0" y="0" width="100%" height="100%">
    <feTurbulence type="fractalNoise" baseFrequency="0.42" numOctaves="2" seed="9" stitchTiles="stitch" result="n"/>
    <feDiffuseLighting in="n" lighting-color="#e9edf0" surfaceScale="0.9" result="l"><feDistantLight azimuth="225" elevation="42"/></feDiffuseLighting>
    <feComponentTransfer><feFuncR type="linear" slope="0.55" intercept="0.44"/><feFuncG type="linear" slope="0.55" intercept="0.45"/><feFuncB type="linear" slope="0.55" intercept="0.47"/></feComponentTransfer>
  </filter>
  <rect width="100%" height="100%" filter="url(#b)"/>
</svg>`;
const epsFile = `${IMG}/eps-textura.webp`;
await sharp(Buffer.from(eps)).webp({ quality: 72, effort: 6 }).toFile(epsFile);
console.log(epsFile, kb(epsFile));

/* 2 · Mapa de la península (Natural Earth 50m: España + Portugal), proyección simple con corrección de latitud */
const cache = '.tmp/ne_50m_countries.geojson';
if (!existsSync(cache)) {
  const url = 'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_50m_admin_0_countries.geojson';
  const r = await fetch(url);
  if (!r.ok) throw new Error('No se pudo descargar Natural Earth: ' + r.status);
  writeFileSync(cache, Buffer.from(await r.arrayBuffer()));
}
const geo = JSON.parse(readFileSync(cache, 'utf8'));
const pais = (iso) => geo.features.find((f) => f.properties.ISO_A3 === iso || f.properties.ADM0_A3 === iso);

// Caja de la península: lon −9,6…3,4 · lat 35,9…43,9
const LON0 = -9.6, LON1 = 3.4, LAT0 = 35.9, LAT1 = 43.9;
const K = Math.cos((40 * Math.PI) / 180); // corrección de latitud media
const W = 1000, H = Math.round((W * (LAT1 - LAT0)) / ((LON1 - LON0) * K));
const px = (lon, lat) => [((lon - LON0) / (LON1 - LON0)) * W, ((LAT1 - lat) / (LAT1 - LAT0)) * H];
const dentro = ([lon, lat]) => lon > LON0 - 0.5 && lon < LON1 + 0.5 && lat > LAT0 - 0.5 && lat < LAT1 + 0.5;

const trazado = (feature) => {
  const polys = feature.geometry.type === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;
  return polys
    .map((poly) => poly[0])
    .filter((anillo) => anillo.some(dentro) && anillo.length > 30)
    // solo la península: fuera Baleares (todas sus coordenadas al este de 1,1° y al sur de 40,2°)
    .filter((anillo) => !anillo.every(([lon, lat]) => lon > 1.1 && lat < 40.2))
    .map((anillo) => 'M' + anillo.map((c) => px(c[0], c[1]).map((v) => v.toFixed(1)).join(' ')).join('L') + 'Z')
    .join('');
};
const espana = trazado(pais('ESP'));
const portugal = trazado(pais('PRT'));

const ciudades = {
  mataro: [2.44, 41.54], madrid: [-3.70, 40.42], valencia: [-0.38, 39.47], sevilla: [-5.98, 37.39],
  bilbao: [-2.93, 43.26], zaragoza: [-0.89, 41.65], coruna: [-8.41, 43.36], malaga: [-4.42, 36.72],
  murcia: [-1.13, 37.99], valladolid: [-4.72, 41.65], barcelona: [2.17, 41.39],
};
const P = Object.fromEntries(Object.entries(ciudades).map(([k, v]) => [k, px(v[0], v[1]).map((n) => +n.toFixed(1))]));

writeFileSync(`${PARCIALES}/mapa-datos.json`, JSON.stringify({ ancho: W, alto: H, espana, portugal, ciudades: P }, null, 1));
console.log(`${PARCIALES}/mapa-datos.json  ${W}x${H}  España ${espana.length} car. · Portugal ${portugal.length} car.`);
