// Datos reales de cada zona de entrega: coordenadas (Nominatim/OpenStreetMap) y distancia y tiempo en coche
// desde la nave de Mataró (OSRM). Uso: node herramientas/datos-zonas.mjs → tema/dryicepack/datos/zonas.json
import { writeFileSync } from 'node:fs';
const UA = { 'User-Agent': 'DryIcePack-web/1.0 (info@dryicepack.es)' };
const espera = (ms) => new Promise((r) => setTimeout(r, ms));
const zonas = [
  // clave, nombre, consulta, tipo, comarca
  ['barcelona', 'Barcelona', 'Barcelona, Catalunya, España', 'ciudad', 'Barcelonès'],
  ['maresme', 'el Maresme', 'Mataró, Catalunya, España', 'comarca', 'Maresme'],
  ['valles-occidental', 'el Vallès Occidental', 'Sabadell, Catalunya, España', 'comarca', 'Vallès Occidental'],
  ['valles-oriental', 'el Vallès Oriental', 'Granollers, Catalunya, España', 'comarca', 'Vallès Oriental'],
  ['baix-llobregat', 'el Baix Llobregat', 'Sant Feliu de Llobregat, Catalunya, España', 'comarca', 'Baix Llobregat'],
  ['mataro', 'Mataró', 'Mataró, Catalunya, España', 'ciudad', 'Maresme'],
  ['badalona', 'Badalona', 'Badalona, Catalunya, España', 'ciudad', 'Barcelonès'],
  ['hospitalet', "L'Hospitalet de Llobregat", "L'Hospitalet de Llobregat, Catalunya, España", 'ciudad', 'Barcelonès'],
  ['sabadell', 'Sabadell', 'Sabadell, Catalunya, España', 'ciudad', 'Vallès Occidental'],
  ['terrassa', 'Terrassa', 'Terrassa, Catalunya, España', 'ciudad', 'Vallès Occidental'],
  ['granollers', 'Granollers', 'Granollers, Catalunya, España', 'ciudad', 'Vallès Oriental'],
  ['rubi', 'Rubí', 'Rubí, Catalunya, España', 'ciudad', 'Vallès Occidental'],
  ['sant-cugat', 'Sant Cugat del Vallès', 'Sant Cugat del Vallès, Catalunya, España', 'ciudad', 'Vallès Occidental'],
  ['martorell', 'Martorell', 'Martorell, Catalunya, España', 'ciudad', 'Baix Llobregat'],
  ['cornella', 'Cornellà de Llobregat', 'Cornellà de Llobregat, Catalunya, España', 'ciudad', 'Baix Llobregat'],
  ['el-prat', 'El Prat de Llobregat', 'El Prat de Llobregat, Catalunya, España', 'ciudad', 'Baix Llobregat'],
  ['mollet', 'Mollet del Vallès', 'Mollet del Vallès, Catalunya, España', 'ciudad', 'Vallès Oriental'],
  ['sant-boi', 'Sant Boi de Llobregat', 'Sant Boi de Llobregat, Catalunya, España', 'ciudad', 'Baix Llobregat'],
];
async function geo(q) {
  const r = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q), { headers: UA });
  const j = await r.json(); await espera(1100);
  return j[0] ? [parseFloat(j[0].lat), parseFloat(j[0].lon)] : null;
}
const nave = (await geo('Camí de Ca la Madrona, Mataró')) || (await geo('Mataró, Catalunya, España'));
console.log('nave', nave);
const salida = { nave, zonas: {} };
for (const [clave, nombre, q, tipo, comarca] of zonas) {
  const c = await geo(q);
  if (!c) { console.log('sin coordenadas', clave); continue; }
  let km = null, min = null;
  try {
    const r = await fetch(`https://router.project-osrm.org/route/v1/driving/${nave[1]},${nave[0]};${c[1]},${c[0]}?overview=false`, { headers: UA });
    const j = await r.json();
    if (j.routes && j.routes[0]) { km = Math.round(j.routes[0].distance / 1000); min = Math.round(j.routes[0].duration / 60); }
  } catch (e) {}
  await espera(600);
  salida.zonas[clave] = { nombre, tipo, comarca, lat: +c[0].toFixed(4), lon: +c[1].toFixed(4), km, min };
  console.log(clave, c, km + ' km', min + ' min');
}
salida.fuente = 'OpenStreetMap (Nominatim) y OSRM, ' + new Date().toISOString().slice(0, 10);
writeFileSync('tema/dryicepack/datos/zonas.json', JSON.stringify(salida, null, 1));
