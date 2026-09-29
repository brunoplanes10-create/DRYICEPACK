// Genera dist/halloween-tema-hijo.zip para descomprimir dentro de wp-content/themes/divi-child-dryicepack/
// Contiene: page-hielo-seco-halloween.php + assets/halloween/ (con las versiones .min ya compiladas).
// Usa el tar.exe de Windows, que crea el zip con rutas "/" (compatibles con el servidor Linux de Arsys).
// Uso: node herramientas/compilar.mjs && node herramientas/empaquetar-halloween.mjs
import { execFileSync } from 'node:child_process';
import { mkdirSync, rmSync, statSync, existsSync } from 'node:fs';
import { resolve } from 'node:path';

const salida = resolve('dist/halloween-tema-hijo.zip');
mkdirSync('dist', { recursive: true });
rmSync(salida, { force: true });

for (const f of ['css/halloween.min.css', 'js/halloween.min.js']) {
  if (!existsSync(`landing-halloween/assets/halloween/${f}`)) throw new Error(`Falta ${f}: ejecuta antes herramientas/compilar.mjs`);
}

const tar = existsSync('C:/Windows/System32/tar.exe') ? 'C:/Windows/System32/tar.exe' : 'tar';
execFileSync(tar, ['-a', '-c', '-f', salida, '-C', 'landing-halloween', 'page-hielo-seco-halloween.php', 'assets'], { stdio: 'inherit' });
console.log(`${salida}  ${(statSync(salida).size / 1024).toFixed(0)} KB`);
console.log(execFileSync(tar, ['-t', '-f', salida]).toString());
