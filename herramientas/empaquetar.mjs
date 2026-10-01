// Genera los dos archivos para subir a WordPress:
//   dist/dryicepack-tema.zip      → Apariencia → Temas → Añadir nuevo → Subir tema
//   dist/dryicepack-tienda.zip    → Plugins → Añadir nuevo → Subir plugin
// Copia cada carpeta a .tmp/empaquetar, crea allí las versiones .min del CSS y el JS (el tema y el plugin las usan
// si existen) y comprime con el tar.exe de Windows: zip con rutas "/" que entiende el servidor Linux de Arsys.
// La carpeta de trabajo queda sin .min, así lo que se prueba en local es siempre el código fuente.
// Uso: node herramientas/empaquetar.mjs
import { transform } from 'esbuild';
import { execFileSync } from 'node:child_process';
import { mkdirSync, rmSync, statSync, existsSync, readdirSync, cpSync, readFileSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';

const TMP = resolve('.tmp/empaquetar');
const tar = existsSync('C:/Windows/System32/tar.exe') ? 'C:/Windows/System32/tar.exe' : 'tar';
mkdirSync('dist', { recursive: true });

async function minificar(dir) {
  const fuentes = [];
  const recorrer = (d) => { for (const f of readdirSync(d, { withFileTypes: true })) { const p = join(d, f.name); if (f.isDirectory()) recorrer(p); else if (/\.(css|js)$/.test(f.name) && !/\.min\.(css|js)$/.test(f.name)) fuentes.push(p); } };
  recorrer(dir);
  let antes = 0, despues = 0;
  for (const f of fuentes) {
    const css = f.endsWith('.css');
    const codigo = readFileSync(f, 'utf8');
    // transform no resuelve @import ni url(): las rutas relativas quedan igual; un @import propio apunta a su .min
    const entrada = css ? codigo.replace(/@import\s+(url\()?["']?([\w\-/]+)\.css["']?\)?/g, '@import "$2.min.css"') : codigo;
    const { code } = await transform(entrada, { loader: css ? 'css' : 'js', minify: true, legalComments: 'none', target: css ? 'chrome90' : 'es2017' });
    writeFileSync(f.replace(/\.(css|js)$/, '.min.$1'), code);
    antes += codigo.length; despues += code.length;
  }
  return `${fuentes.length} archivos CSS/JS, ${(antes / 1024).toFixed(0)} KB → ${(despues / 1024).toFixed(0)} KB`;
}

for (const [carpeta, nombre, zip] of [['tema', 'dryicepack', 'dist/dryicepack-tema.zip'], ['plugin', 'dryicepack-tienda', 'dist/dryicepack-tienda.zip']]) {
  rmSync(TMP, { recursive: true, force: true });
  const copia = join(TMP, nombre);
  cpSync(resolve(carpeta, nombre), copia, { recursive: true, filter: (p) => !/\.min\.(css|js)$/.test(p) || /halloween/.test(p) });
  const resumen = await minificar(join(copia, 'assets'));
  const salida = resolve(zip);
  rmSync(salida, { force: true });
  execFileSync(tar, ['-a', '-c', '-f', salida, '-C', TMP, nombre], { stdio: 'inherit' });
  console.log(`${zip}  ${(statSync(salida).size / 1024).toFixed(0)} KB  (${resumen})`);
}
rmSync(TMP, { recursive: true, force: true });
