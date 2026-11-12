// Pruebas del email «¿Qué tal llegó tu hielo seco?» (inc/resenas.php) en el WordPress de prueba (puerto 9500).
//
// Antes: node herramientas/probar-pedido.mjs normal  y  node herramientas/probar-pedido.mjs recogida
// (la prueba usa también esos dos pedidos reales del checkout, de las últimas 3 horas).
//
// Cómo funciona: copia herramientas/fixtures/prueba-resenas.php a la carpeta del plugin (montada en vivo en Playground)
// como zz-prueba-resenas.php, lo llama por pasos (1, 2, 3, 4) y lo BORRA siempre al acabar, también si algo falla o se
// corta con Ctrl+C: si se quedara allí iría al .zip del plugin. Entre el paso 2 y el 3 prueba por HTTP la página de baja
// (GET con botón, POST que da de baja, «Cancelar suscripción» de RFC 8058, enlace mal firmado, sin caché, sin el email).
// Los emails capturados (HTML) y las páginas de baja quedan en .tmp/resenas/.
// No arranca ni para el servidor: si no responde, espera a que esté listo (hasta 5 minutos).
// Uso: node herramientas/probar-resenas.mjs
import { copyFileSync, existsSync, unlinkSync, mkdirSync, readdirSync, renameSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const BASE = 'http://127.0.0.1:9500';
const ORIGEN = 'herramientas/fixtures/prueba-resenas.php';
const DESTINO = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-prueba-resenas.php';
const URL_PRUEBA = BASE + '/wp-content/plugins/dryicepack-tienda/zz-prueba-resenas.php';
const SALIDA_PG = '.tmp/pg/resenas'; // /salida/resenas dentro de Playground
const SALIDA = '.tmp/resenas';

let fallos = 0;
const ok = (cond, texto, detalle) => {
  console.log((cond ? 'OK    ' : 'FALLO ') + texto + (!cond && detalle !== undefined ? ' → ' + (typeof detalle === 'string' ? detalle : JSON.stringify(detalle)) : ''));
  if (!cond) fallos++;
  return cond;
};

const borrar = () => { try { if (existsSync(DESTINO)) unlinkSync(DESTINO); } catch (e) { console.log('FALLO: no se ha podido borrar ' + DESTINO + ': ' + e.message); fallos++; } };
process.on('exit', borrar);
for (const senal of ['SIGINT', 'SIGTERM', 'SIGHUP']) process.on(senal, () => { borrar(); process.exit(130); });

const pedir = async (url, ms = 180000, opciones = {}) => {
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), ms);
  try { return await fetch(url, { ...opciones, signal: ctrl.signal, redirect: 'manual' }); } finally { clearTimeout(t); }
};

const esperarServidor = async () => {
  const limite = Date.now() + 5 * 60 * 1000;
  for (;;) {
    try {
      const r = await pedir(BASE + '/');
      if (r.status === 200 && /<html/i.test(await r.text())) return;
      console.log('El servidor todavía no está listo (' + r.status + '); se vuelve a probar en 30 s');
    } catch (e) {
      console.log('El servidor no responde (' + e.message + '); se vuelve a probar en 30 s');
    }
    if (Date.now() > limite) throw new Error('El servidor de prueba de ' + BASE + ' no ha respondido en 5 minutos.');
    await new Promise((r) => setTimeout(r, 30000));
  }
};

/** Un paso del fixture: imprime su salida y cuenta sus fallos. Devuelve el texto. */
const paso = async (n, extra = '') => {
  const r = await pedir(URL_PRUEBA + '?paso=' + n + extra, 600000);
  const texto = await r.text();
  process.stdout.write(texto.endsWith('\n') ? texto : texto + '\n');
  ok(r.status === 200, `paso ${n}: responde 200`, r.status);
  ok(!/<(html|body|br)\b/i.test(texto.slice(0, 400)), `paso ${n}: salida en texto plano (sin error de PHP en HTML)`);
  const total = texto.match(/^(\d+) comprobaciones, (\d+) fallos$/m);
  if (ok(!!total, `paso ${n}: llega al final ("N comprobaciones, M fallos")`)) {
    ok(Number(total[2]) === 0, `paso ${n}: ${total[1]} comprobaciones de PHP, ${total[2]} fallos`);
  }
  return texto;
};

const cabecerasSinCache = (r, nombre) => {
  const cc = r.headers.get('cache-control') || '';
  ok(/no-store/.test(cc) && /no-cache/.test(cc), `${nombre}: Cache-Control sin caché`, cc);
  ok(/noindex/.test(r.headers.get('x-robots-tag') || ''), `${nombre}: X-Robots-Tag noindex`, r.headers.get('x-robots-tag'));
  ok((r.headers.get('referrer-policy') || '') === 'no-referrer', `${nombre}: Referrer-Policy no-referrer`, r.headers.get('referrer-policy'));
};

if (!existsSync(ORIGEN)) { console.log('FALLO: falta ' + ORIGEN); process.exit(1); }
if (existsSync(DESTINO)) { console.log('FALLO: ya existe ' + DESTINO + ' (¿otra ejecución a la vez o un resto?). Bórralo y repite.'); process.exit(1); }

try {
  await esperarServidor();
  rmSync(SALIDA_PG, { recursive: true, force: true });
  copyFileSync(ORIGEN, DESTINO);

  await paso(1);
  const salida2 = await paso(2);
  const dato = (clave) => (salida2.match(new RegExp('^' + clave + ': (.+)$', 'm')) || [])[1];
  const bajaB = dato('URL_BAJA_B'), emailB = dato('EMAIL_B');
  const bajaD = dato('URL_BAJA_D'), emailD = dato('EMAIL_D');
  mkdirSync(SALIDA, { recursive: true });

  if (ok(!!bajaB && !!bajaD, 'el paso 2 da los enlaces de baja de B (catalán, tú) y D (castellano, usted)')) {
    const estado = async (email) => (await (await pedir(URL_PRUEBA + '?paso=estado&email=' + encodeURIComponent(email))).text());

    // GET: página con botón; no da de baja (los antivirus del correo abren los enlaces)
    let r = await pedir(bajaB);
    let html = await r.text();
    writeFileSync(join(SALIDA, 'baja-1-confirmar-ca-tu.html'), html);
    ok(r.status === 200, 'GET baja B: 200', r.status);
    ok(html.includes('lang="ca"') && html.includes('Deixem de demanar-te l&#039;opinió?') && html.includes('Donar-me de baixa'), 'GET baja B: pregunta en catalán, de tú, con el botón', html.slice(0, 600));
    ok(/<form method="post"/.test(html), 'GET baja B: el botón envía un POST');
    ok(!html.toLowerCase().includes(emailB.toLowerCase()) && !html.includes(emailB.split('@')[0]), 'GET baja B: la página no enseña el email');
    ok(/name="robots" content="noindex/.test(html), 'GET baja B: meta robots noindex');
    cabecerasSinCache(r, 'GET baja B');
    ok(!r.headers.get('set-cookie'), 'GET baja B: no pone cookies', r.headers.get('set-cookie'));
    ok(/BAJA: no/.test(await estado(emailB)), 'después del GET, B todavía NO está de baja');

    // Enlace mal firmado (GET y POST): 400 y no da de baja a nadie
    const mal = bajaB.replace(/firma=([a-f0-9])/, (m, c) => 'firma=' + (c === 'a' ? 'b' : 'a'));
    const bajasAntes = Number(((await estado(emailB)).match(/BAJAS: (\d+)/) || [])[1]);
    r = await pedir(mal);
    html = await r.text();
    writeFileSync(join(SALIDA, 'baja-0-no-valido-ca-tu.html'), html);
    ok(r.status === 400 && html.includes('Aquest enllaç no és vàlid') && !/<form/.test(html), 'GET con la firma cambiada: 400, «enllaç no vàlid» y sin botón', r.status);
    r = await pedir(mal, 180000, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'List-Unsubscribe=One-Click' });
    await r.text();
    ok(r.status === 400, 'POST con la firma cambiada: 400', r.status);
    r = await pedir(BASE + '/?dip_resenas_baja=' + 'f'.repeat(32) + '&firma=0123456789abcdef', 180000, { method: 'POST' });
    await r.text();
    ok(r.status === 400, 'POST con una huella inventada: 400', r.status);
    ok(Number(((await estado(emailB)).match(/BAJAS: (\d+)/) || [])[1]) === bajasAntes, 'los enlaces mal firmados no añaden nada a la lista de bajas');

    // POST desde el botón de la página: da de baja
    r = await pedir(bajaB, 180000, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'List-Unsubscribe=One-Click' });
    html = await r.text();
    writeFileSync(join(SALIDA, 'baja-2-hecho-ca-tu.html'), html);
    ok(r.status === 200 && html.includes('Fet: ja no et demanarem l&#039;opinió'), 'POST baja B: «Fet: ja no et demanarem l\'opinió»', html.slice(0, 600));
    ok(!html.toLowerCase().includes(emailB.toLowerCase()), 'POST baja B: la página no enseña el email');
    cabecerasSinCache(r, 'POST baja B');
    ok(/BAJA: si/.test(await estado(emailB)), 'después del POST, B está de baja');
    r = await pedir(bajaB);
    html = await r.text();
    ok(r.status === 200 && html.includes('Fet:') && !/<form/.test(html), 'GET otra vez el enlace de B: ya dice «Fet» y no enseña el botón');

    // «Cancelar suscripción» de Gmail / Apple Mail (RFC 8058): POST directo al enlace de la cabecera List-Unsubscribe
    r = await pedir(bajaD, 180000, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'List-Unsubscribe=One-Click' });
    html = await r.text();
    writeFileSync(join(SALIDA, 'baja-3-hecho-es-usted.html'), html);
    ok(r.status === 200 && html.includes('lang="es"') && html.includes('Hecho: ya no le pediremos opinión'), 'POST RFC 8058 baja D: «Hecho: ya no le pediremos opinión» (castellano, usted)', html.slice(0, 600));
    ok(/BAJA: si/.test(await estado(emailD)), 'después del POST RFC 8058, D está de baja');
    const confirmarD = await (await pedir(bajaD.replace(/&usted=1/, '').replace(/dip_resenas_baja=[a-f0-9]+&firma=[a-f0-9]+/, 'dip_resenas_baja=' + 'e'.repeat(32) + '&firma=' + 'e'.repeat(16)))).text();
    ok(confirmarD.includes('Este enlace no es válido'), 'castellano de tú: «Este enlace no es válido»');
    const en = await (await pedir(BASE + '/?dip_resenas_baja=x&firma=y&idioma=en')).text();
    ok(en.includes('This link is not valid') && en.includes('lang="en"'), 'inglés: «This link is not valid»');
  }

  await paso(3);
  await paso(4);
} catch (e) {
  ok(false, 'excepción: ' + e.message);
} finally {
  // Limpieza por si un paso se cortó a medias (no hace nada si el paso 4 ya dejó todo como estaba)
  try { if (existsSync(DESTINO)) await paso('limpiar'); } catch (e) { ok(false, 'limpieza: ' + e.message); }
  borrar();
}

// Emails capturados por el fixture → .tmp/resenas/
if (existsSync(SALIDA_PG)) {
  mkdirSync(SALIDA, { recursive: true });
  for (const f of readdirSync(SALIDA_PG)) renameSync(join(SALIDA_PG, f), join(SALIDA, f));
  rmSync(SALIDA_PG, { recursive: true, force: true });
}
if (existsSync(SALIDA)) console.log('\nEmails y páginas de baja en ' + SALIDA + '/: ' + readdirSync(SALIDA).sort().join(', '));
if (existsSync(DESTINO)) { console.log('FALLO: ' + DESTINO + ' sigue en la carpeta del plugin'); fallos++; }
console.log(fallos ? `\n${fallos} fallo(s) en probar-resenas` : '\nprobar-resenas: todo correcto');
process.exit(fallos ? 1 : 0);
