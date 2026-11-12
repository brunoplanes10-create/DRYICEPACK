// Pruebas de los emails al cliente (inc/emails.php, inc/avisos.php, inc/condiciones-pdf.php) en el WordPress de prueba
// (puerto 9500): pedido recibido, «va de camino», «hoy llega», recogida, incidencia, fallos, vigilancia, evento de las
// 8:00, «Pedido completado» que ya no sale, factura simplificada, «¿Necesitas factura?», reseña y PDF de condiciones.
//
// Cómo funciona: copia herramientas/fixtures/prueba-avisos.php a la carpeta del plugin como zz-prueba-avisos.php, lo
// llama por pasos (1, 2, 3, 4) y lo BORRA siempre al acabar, también si algo falla o se corta con Ctrl+C.
// Los emails capturados (HTML y texto) y los PDF quedan en .tmp/avisos/; las capturas (390 y 600 px, claro y oscuro)
// en .tmp/avisos/capturas/ y la primera página de cada PDF en .tmp/avisos/pdf/ (pdftoppm, si está instalado).
// No arranca ni para el servidor: si no responde, espera a que esté listo (hasta 5 minutos).
// Uso: node herramientas/probar-avisos.mjs [--sin-capturas]
import { copyFileSync, existsSync, unlinkSync, mkdirSync, readdirSync, renameSync, rmSync } from 'node:fs';
import { join } from 'node:path';
import { execFileSync } from 'node:child_process';

const BASE = 'http://127.0.0.1:9500';
const ORIGEN = 'herramientas/fixtures/prueba-avisos.php';
const DESTINO = (process.env.DIP_PLUGIN_DIR || 'plugin/dryicepack-tienda') + '/zz-prueba-avisos.php';
const URL_PRUEBA = BASE + '/wp-content/plugins/dryicepack-tienda/zz-prueba-avisos.php';
const SALIDA_PG = '.tmp/pg/avisos'; // /salida/avisos dentro de Playground
const SALIDA = '.tmp/avisos';
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';

let fallos = 0;
const ok = (cond, texto, detalle) => {
  console.log((cond ? 'OK    ' : 'FALLO ') + texto + (!cond && detalle !== undefined ? ' → ' + (typeof detalle === 'string' ? detalle : JSON.stringify(detalle)) : ''));
  if (!cond) fallos++;
  return cond;
};

const borrar = () => { try { if (existsSync(DESTINO)) unlinkSync(DESTINO); } catch (e) { console.log('FALLO: no se ha podido borrar ' + DESTINO + ': ' + e.message); fallos++; } };
process.on('exit', borrar);
for (const senal of ['SIGINT', 'SIGTERM', 'SIGHUP']) process.on(senal, () => { borrar(); process.exit(130); });

const pedir = async (url, ms = 180000) => {
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), ms);
  try { return await fetch(url, { signal: ctrl.signal, redirect: 'manual' }); } finally { clearTimeout(t); }
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

const paso = async (n) => {
  const r = await pedir(URL_PRUEBA + '?paso=' + n, 900000);
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

if (!existsSync(ORIGEN)) { console.log('FALLO: falta ' + ORIGEN); process.exit(1); }
if (existsSync(DESTINO)) { console.log('FALLO: ya existe ' + DESTINO + ' (¿otra ejecución a la vez o un resto?). Bórralo y repite.'); process.exit(1); }

try {
  await esperarServidor();
  // Se vacía sin borrar la carpeta: si se borra, Playground puede seguir creyendo que existe y no escribir nada en ella
  mkdirSync(SALIDA_PG, { recursive: true });
  for (const f of readdirSync(SALIDA_PG)) rmSync(join(SALIDA_PG, f), { recursive: true, force: true });
  copyFileSync(ORIGEN, DESTINO);
  for (const n of [1, 2, 3, 4]) await paso(n);
} catch (e) {
  ok(false, 'excepción: ' + e.message);
} finally {
  try { if (existsSync(DESTINO)) await paso('limpiar'); } catch (e) { ok(false, 'limpieza: ' + e.message); }
  borrar();
}

// Emails y PDF capturados por el fixture → .tmp/avisos/
mkdirSync(SALIDA, { recursive: true });
if (existsSync(SALIDA_PG)) {
  for (const f of readdirSync(SALIDA_PG)) renameSync(join(SALIDA_PG, f), join(SALIDA, f));
}
const htmls = readdirSync(SALIDA).filter((f) => f.endsWith('.html')).sort();
console.log(`\n${htmls.length} emails en ${SALIDA}/`);
ok(htmls.length >= 52, `se han guardado los 52 emails de ejemplo (${htmls.length})`);

// Primera página de cada PDF (pdftoppm) para revisarla a ojo
const pdfs = readdirSync(SALIDA).filter((f) => f.endsWith('.pdf'));
if (pdfs.length) {
  mkdirSync(join(SALIDA, 'pdf'), { recursive: true });
  for (const f of pdfs) {
    try {
      execFileSync('pdftoppm', ['-png', '-r', '110', '-f', '1', '-l', '2', join(SALIDA, f), join(SALIDA, 'pdf', f.replace(/\.pdf$/, ''))]);
      const info = execFileSync('pdfinfo', [join(SALIDA, f)]).toString();
      const fuentes = execFileSync('pdffonts', [join(SALIDA, f)]).toString();
      ok(/Pages:\s+\d+/.test(info), `${f}: pdfinfo lo abre (${(info.match(/Pages:\s+(\d+)/) || [])[1]} páginas)`);
      ok(!/Syntax Error|Error/i.test(fuentes) && /Helvetica/.test(fuentes), `${f}: fuentes estándar (Helvetica, Courier) sin errores`);
      const texto = execFileSync('pdftotext', ['-enc', 'UTF-8', '-l', '3', join(SALIDA, f), '-']).toString();
      ok(/[áéíóúñçàèò]/i.test(texto) && /€/.test(texto) && !/\?\?/.test(texto), `${f}: acentos y € se leen bien (pdftotext)`, texto.slice(0, 200));
    } catch (e) {
      console.log(`(no se ha podido pasar ${f} a imagen: ${e.message.split('\n')[0]})`);
    }
  }
}

// Capturas: todos a 600 px; los de castellano de tú, también a 390 px y en modo oscuro
if (!process.argv.includes('--sin-capturas') && existsSync(CHROME) && htmls.length) {
  const { default: puppeteer } = await import('puppeteer-core');
  const CAP = join(SALIDA, 'capturas');
  rmSync(CAP, { recursive: true, force: true });
  mkdirSync(CAP, { recursive: true });
  const nav = await puppeteer.launch({ executablePath: CHROME, headless: true, userDataDir: join(SALIDA, 'chrome') });
  const p = await nav.newPage();
  const principales = /(-es-tu|-sabado|espera|tienda|necesitas|confirmar)/;
  for (const archivo of htmls) {
    const nombre = archivo.replace(/\.html$/, '');
    const modos = [[600, 'light']];
    if (principales.test(nombre)) modos.push([390, 'light'], [600, 'dark'], [390, 'dark']);
    for (const [ancho, esquema] of modos) {
      await p.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: esquema }]);
      await p.setViewport({ width: ancho, height: 900, deviceScaleFactor: 1 });
      await p.goto('file:///' + process.cwd().replace(/\\/g, '/') + '/' + SALIDA + '/' + archivo, { waitUntil: 'networkidle0', timeout: 60000 });
      const desborda = await p.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      ok(!desborda, `${nombre} a ${ancho} px (${esquema}) sin scroll horizontal`);
      await p.screenshot({ path: join(CAP, `${nombre}-${ancho}${esquema === 'dark' ? '-oscuro' : ''}.png`), fullPage: true });
    }
  }
  await nav.close();
  console.log(`Capturas en ${CAP}/`);
}

if (existsSync(DESTINO)) { console.log('FALLO: ' + DESTINO + ' sigue en la carpeta del plugin'); fallos++; }
console.log(fallos ? `\n${fallos} fallo(s) en probar-avisos` : '\nprobar-avisos: todo correcto');
process.exit(fallos ? 1 : 0);
