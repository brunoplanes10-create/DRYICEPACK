// Pruebas de las decisiones del 03/10/2026 en el WordPress de prueba (puerto 9500): pack pequeño (3 → 5 kg),
// precios de recogida redondos y en efectivo, suplemento de sábado de la recogida, factura simplificada de un
// pedido de recogida y GET /wp-json/dip/v1/envio (app INDUNOVA Control).
//
// Cómo funciona: copia herramientas/fixtures/prueba-recogida.php a la carpeta del plugin (está montada en vivo en
// Playground) como zz-prueba-recogida.php, lo llama por HTTP y lo BORRA siempre al acabar (también si algo falla o
// se corta con Ctrl+C): si se quedara allí iría al .zip del plugin. Las comprobaciones están en el PHP; aquí solo
// se lee su resultado y se añaden las cabeceras HTTP reales del endpoint (CORS y caché), que desde dentro de
// WordPress no se pueden ver.
// No arranca ni para el servidor: si no responde, espera a que esté listo (hasta 5 minutos).
// Uso: node herramientas/probar-recogida.mjs
import { copyFileSync, existsSync, unlinkSync, readFileSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const ORIGEN = 'herramientas/fixtures/prueba-recogida.php';
const DESTINO = 'plugin/dryicepack-tienda/zz-prueba-recogida.php';
const URL_PRUEBA = BASE + '/wp-content/plugins/dryicepack-tienda/zz-prueba-recogida.php';
const MARGEN_MS = 10 * 60 * 1000; // la prueba hace cientos de cálculos de carrito: hasta 10 minutos

let fallos = 0;
const ok = (cond, texto, detalle) => {
  if (cond) return true;
  fallos++;
  console.log('FALLO: ' + texto + (detalle === undefined ? '' : ' → ' + (typeof detalle === 'string' ? detalle : JSON.stringify(detalle))));
  return false;
};

// Borrado del archivo temporal pase lo que pase (fin normal, excepción, Ctrl+C o cierre del proceso)
const borrar = () => { try { if (existsSync(DESTINO)) unlinkSync(DESTINO); } catch (e) { console.log('FALLO: no se ha podido borrar ' + DESTINO + ': ' + e.message); fallos++; } };
process.on('exit', borrar);
for (const senal of ['SIGINT', 'SIGTERM', 'SIGHUP']) process.on(senal, () => { borrar(); process.exit(130); });

const pedir = async (url, ms, opciones = {}) => {
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), ms);
  try { return await fetch(url, { ...opciones, signal: ctrl.signal, redirect: 'manual' }); } finally { clearTimeout(t); }
};

// Espera a que el servidor de prueba esté listo (Playground tarda en arrancar y puede responder "WordPress is not ready yet")
const esperarServidor = async () => {
  const limite = Date.now() + 5 * 60 * 1000;
  for (;;) {
    try {
      const r = await pedir(BASE + '/', 180000);
      const html = await r.text();
      if (r.status === 200 && /<html/i.test(html)) return;
      console.log('El servidor todavía no está listo (' + r.status + '); se vuelve a probar en 30 s');
    } catch (e) {
      console.log('El servidor no responde (' + (e.name === 'AbortError' ? 'tiempo agotado' : e.message) + '); se vuelve a probar en 30 s');
    }
    if (Date.now() > limite) throw new Error('El servidor de prueba de ' + BASE + ' no ha respondido en 5 minutos. Arráncalo desde el panel de la app (wordpress-tema).');
    await new Promise((r) => setTimeout(r, 30000));
  }
};

if (!existsSync(ORIGEN)) { console.log('FALLO: falta ' + ORIGEN); process.exit(1); }
if (existsSync(DESTINO)) { console.log('FALLO: ya existe ' + DESTINO + ' (¿otra ejecución a la vez o un resto de una anterior?). Bórralo y repite.'); process.exit(1); }

try {
  await esperarServidor();

  // 1) Las comprobaciones de PHP dentro de WordPress (cada "FALLO:" es un fallo; la última línea da el total)
  copyFileSync(ORIGEN, DESTINO);
  const inicio = Date.now();
  let salida = '';
  try {
    const r = await pedir(URL_PRUEBA, MARGEN_MS);
    salida = await r.text();
    ok(r.status === 200, 'la prueba de PHP responde 200', r.status);
  } catch (e) {
    ok(false, 'la prueba de PHP no ha respondido', e.name === 'AbortError' ? 'más de 10 minutos' : e.message);
  } finally {
    borrar();
  }
  process.stdout.write(salida.endsWith('\n') ? salida : salida + '\n');
  console.log(`(${((Date.now() - inicio) / 1000).toFixed(1)} s)`);
  ok(!/<(html|body|b>|br)/i.test(salida.slice(0, 400)), 'la salida es texto plano (no una página de WordPress ni un error de PHP en HTML)');
  const total = salida.match(/^(\d+) comprobaciones, (\d+) fallos$/m);
  if (ok(!!total, 'la prueba de PHP llega al final y da su recuento ("N comprobaciones, M fallos")')) {
    const fallosPhp = Number(total[2]);
    const marcados = (salida.match(/^FALLO:/gm) || []).length;
    ok(fallosPhp === 0, `${total[2]} fallo(s) en las comprobaciones de PHP`);
    ok(marcados === fallosPhp, 'las líneas "FALLO:" coinciden con el recuento', { lineas: marcados, recuento: fallosPhp });
    ok(Number(total[1]) >= 500, 'se han hecho al menos 500 comprobaciones (si bajan mucho, algo se ha saltado)', total[1]);
  }

  // 2) Cabeceras reales del endpoint de la app (por HTTP, con la URL bonita y con ?rest_route=)
  for (const url of [BASE + '/wp-json/dip/v1/envio', BASE + '/?rest_route=/dip/v1/envio']) {
    try {
      const r = await pedir(url, 180000, { headers: { Origin: 'https://app.indunova.es' } });
      const texto = await r.text();
      let json = null;
      try { json = JSON.parse(texto); } catch (e) { /* se comprueba abajo */ }
      const corto = url.replace(BASE, '');
      ok(r.status === 200, `GET ${corto} responde 200`, r.status);
      ok(json && json.version >= 1 && json.mrw && typeof json.mrw.dias === 'object', `GET ${corto} devuelve el JSON de la app`, texto.slice(0, 200));
      ok(r.headers.get('access-control-allow-origin') === '*', `GET ${corto}: Access-Control-Allow-Origin: *`, r.headers.get('access-control-allow-origin'));
      ok(!r.headers.has('access-control-allow-credentials'), `GET ${corto}: sin Access-Control-Allow-Credentials`, r.headers.get('access-control-allow-credentials'));
      ok(/public,\s*max-age=600/.test(r.headers.get('cache-control') || ''), `GET ${corto}: Cache-Control: public, max-age=600`, r.headers.get('cache-control'));
      ok(!/no-cache|no-store/.test(r.headers.get('cache-control') || '') && !r.headers.has('pragma'), `GET ${corto}: sin no-cache ni Pragma`, { cache: r.headers.get('cache-control'), pragma: r.headers.get('pragma') });
      ok(/application\/json/.test(r.headers.get('content-type') || ''), `GET ${corto}: Content-Type JSON`, r.headers.get('content-type'));
      ok(!r.headers.get('set-cookie'), `GET ${corto}: no pone cookies`, r.headers.get('set-cookie'));
    } catch (e) {
      ok(false, `GET ${url} ha fallado`, e.message);
    }
  }
  const post = await pedir(BASE + '/?rest_route=/dip/v1/envio', 180000, { method: 'POST' });
  ok([404, 405].includes(post.status), 'POST al endpoint de la app no está permitido (404 o 405)', post.status);
} catch (e) {
  ok(false, 'excepción: ' + e.message);
} finally {
  borrar();
}

if (existsSync(DESTINO)) { console.log('FALLO: ' + DESTINO + ' sigue en la carpeta del plugin'); fallos++; }
console.log(fallos ? `\n${fallos} fallo(s) en probar-recogida` : '\nprobar-recogida: todo correcto');
process.exit(fallos ? 1 : 0);
