// Comprueba la sintaxis de todos los .php del tema y del plugin con PHP 8.3 (WebAssembly, sin instalar PHP).
// Uso: node herramientas/lint-php.mjs
import { loadNodeRuntime } from '@php-wasm/node';
import { PHP } from '@php-wasm/universal';
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join } from 'node:path';

const raices = ['tema/dryicepack', 'plugin/dryicepack-tienda'];
const archivos = [];
const recorrer = (d) => { for (const f of readdirSync(d)) { const p = join(d, f); if (statSync(p).isDirectory()) recorrer(p); else if (p.endsWith('.php')) archivos.push(p); } };
raices.forEach(recorrer);

const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
let errores = 0;
for (const a of archivos) {
  php.writeFile('/tmp/a.php', readFileSync(a, 'utf8'));
  const r = await php.run({ code: `<?php try { $t = token_get_all(file_get_contents('/tmp/a.php'), TOKEN_PARSE); echo 'OK'; } catch (\Throwable $e) { echo 'ERR linea ' . $e->getLine() . ': ' . $e->getMessage(); }` });
  const txt = r.text.trim();
  if (txt !== 'OK') { errores++; console.log('✗', a, txt); }
}
console.log(errores ? `${errores} archivo(s) con errores de sintaxis` : `Sintaxis correcta en ${archivos.length} archivos`);
process.exit(errores ? 1 : 0);
