// WordPress de prueba en local (WordPress Playground) con la página de Halloween.
// Monta un tema de pruebas que imita la cabecera y el pie de dryicepack.es y le añade la página.
// Arranca WordPress en http://127.0.0.1:9400
// Uso: node herramientas/servidor-prueba.mjs
import { cpSync, mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { spawn } from 'node:child_process';

const TEMA = '.tmp/tema-prueba';
rmSync(TEMA, { recursive: true, force: true });
mkdirSync(TEMA, { recursive: true });
cpSync('herramientas/tema-prueba-base', TEMA, { recursive: true }); // cabecera y pie de imitación
cpSync('landing-halloween', TEMA, { recursive: true, filter: (src) => !src.endsWith('.md') });

writeFileSync(`${TEMA}/style.css`, `/*\nTheme Name: DryIcePack prueba\nDescription: Tema mínimo para probar plantillas en local.\nVersion: 0.0.1\n*/\n`);
writeFileSync(`${TEMA}/index.php`, `<?php get_header(); while ( have_posts() ) { the_post(); the_title( '<h1>', '</h1>' ); the_content(); } get_footer();\n`);
writeFileSync(`${TEMA}/functions.php`, `<?php add_action( 'after_setup_theme', function () { add_theme_support( 'title-tag' ); } );\n`);

const paginas = ['envios-y-plazos', 'seguridad-del-hielo-seco', 'contacto', 'aplicaciones-del-hielo-seco', 'que-es-el-hielo-seco', 'programar-suministro-de-hielo-seco'];
const blueprint = {
  landingPage: '/hielo-seco-halloween/',
  preferredVersions: { php: '8.2', wp: 'latest' },
  steps: [
    { step: 'setSiteOptions', options: { permalink_structure: '/%postname%/', blogname: 'DryIcePack (prueba)', timezone_string: 'Europe/Madrid' } },
    { step: 'activateTheme', themeFolderName: 'prueba' },
    {
      step: 'runPHP',
      code: `<?php require '/wordpress/wp-load.php';
wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Hielo seco para Halloween', 'post_name' => 'hielo-seco-halloween' ) );
foreach ( ${JSON.stringify(paginas).replace(/"/g, "'")} as $s ) { wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $s, 'post_name' => $s ) ); }
flush_rewrite_rules();`,
    },
  ],
};
writeFileSync('.tmp/blueprint.json', JSON.stringify(blueprint, null, 2));

const npx = process.platform === 'win32' ? 'npx.cmd' : 'npx';
const hijo = spawn(npx, [
  '--yes', '@wp-playground/cli@latest', 'server',
  '--port=9400',
  `--mount=./${TEMA}:/wordpress/wp-content/themes/prueba`,
  '--blueprint=./.tmp/blueprint.json',
], { stdio: 'inherit', shell: process.platform === 'win32' });
hijo.on('exit', (c) => process.exit(c ?? 0));
