// Pruebas de los envíos en MRW (inc/mrw-sagec.php) y del pedido urgente (inc/urgente.php) en el WordPress de prueba
// (puerto 9500), con MRW simulado: ninguna petición sale a internet.
// Copia herramientas/fixtures/prueba-sagec.php a la carpeta del plugin como zz-prueba-sagec.php, lo llama y lo BORRA
// siempre al acabar (si se quedara allí iría al .zip). No arranca el servidor: si no responde, espera hasta 5 minutos.
// Uso: node herramientas/probar-sagec.mjs
import { copyFileSync, existsSync, unlinkSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const ORIGEN = 'herramientas/fixtures/prueba-sagec.php';
const DESTINO = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-prueba-sagec.php';
const URL_PRUEBA = BASE + '/wp-content/plugins/dryicepack-tienda/zz-prueba-sagec.php';

const borrar = () => { try { if (existsSync(DESTINO)) unlinkSync(DESTINO); } catch (e) { console.log('FALLO: no se ha podido borrar ' + DESTINO + ': ' + e.message); process.exitCode = 1; } };
process.on('exit', borrar);
for (const senal of ['SIGINT', 'SIGTERM', 'SIGHUP']) process.on(senal, () => { borrar(); process.exit(130); });

const pedir = async (url, ms = 300000) => {
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), ms);
  try { return await fetch(url, { signal: ctrl.signal }); } finally { clearTimeout(t); }
};

for (let i = 0; ; i++) {
  try { const r = await pedir(BASE + '/', 30000); if (r.ok && (await r.text()).includes('<html')) break; } catch { /* aún arrancando */ }
  if (i > 30) { console.log('FALLO: el WordPress de prueba no responde en ' + BASE); process.exit(1); }
  await new Promise((r) => setTimeout(r, 10000));
}

copyFileSync(ORIGEN, DESTINO);
const r = await pedir(URL_PRUEBA);
const texto = await r.text();
borrar();
console.log(texto.trim());
const m = texto.match(/(\d+) comprobaciones, (\d+) fallos/);
if (!m) { console.log('FALLO: la prueba no ha terminado (HTTP ' + r.status + ')'); process.exit(1); }
console.log(m[2] === '0' ? `\n${m[1]} de ${m[1]} comprobaciones correctas` : `\n${m[2]} fallos de ${m[1]} comprobaciones`);
process.exit(m[2] === '0' ? 0 : 1);
