// WordPress de prueba (Playground) con el TEMA NUEVO + WooCommerce + copia del producto real.
// Arranca en http://127.0.0.1:9500   ·   Uso: node herramientas/servidor-tema.mjs
import { writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { spawn } from 'node:child_process';

mkdirSync('.tmp', { recursive: true });
const paginas = [
  ['que-es-el-hielo-seco', 'Qué es el hielo seco'], ['aplicaciones-del-hielo-seco', 'Aplicaciones del hielo seco'],
  ['envios-y-plazos', 'Envíos y plazos'], ['seguridad-del-hielo-seco', 'Seguridad del hielo seco'],
  ['programar-suministro-de-hielo-seco', 'Programar suministro de hielo seco'], ['contacto', 'Contacto'],
  ['hielo-seco-halloween', 'Hielo seco para Halloween'],
];

const php = `<?php
require '/wordpress/wp-load.php';
update_option( 'permalink_structure', '/%postname%/' );
update_option( 'blogname', 'DryIcePack (prueba)' );
update_option( 'timezone_string', 'Europe/Madrid' );
foreach ( ${JSON.stringify(paginas).replace(/"/g, "'").replace(/\[/g, 'array(').replace(/\]/g, ')')} as $p ) {
  if ( ! get_page_by_path( $p[0] ) ) wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $p[0], 'post_title' => $p[1] ) );
}
if ( class_exists( 'WooCommerce' ) ) {
  update_option( 'woocommerce_default_country', 'ES:B' );
  update_option( 'woocommerce_currency', 'EUR' );
  update_option( 'woocommerce_currency_pos', 'right_space' );
  update_option( 'woocommerce_price_decimal_sep', ',' );
  update_option( 'woocommerce_price_thousand_sep', '.' );
  update_option( 'woocommerce_calc_taxes', 'yes' );
  update_option( 'woocommerce_prices_include_tax', 'no' );
  update_option( 'woocommerce_tax_display_shop', 'excl' );
  update_option( 'woocommerce_tax_display_cart', 'incl' );
  update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
  global $wpdb;
  if ( ! $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_tax_rates" ) ) {
    WC_Tax::_insert_tax_rate( array( 'tax_rate_country' => 'ES', 'tax_rate' => '21.0000', 'tax_rate_name' => 'IVA', 'tax_rate_priority' => 1, 'tax_rate_compound' => 0, 'tax_rate_shipping' => 1, 'tax_rate_order' => 0, 'tax_rate_class' => '' ) );
  }
  // Carrito y checkout clásicos (como en producción)
  foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]' ) as $k => $sc ) {
    $id = wc_get_page_id( $k );
    if ( $id > 0 ) wp_update_post( array( 'ID' => $id, 'post_content' => $sc ) );
  }
  // Atributos y producto variable "hielo-seco" con las 8 variaciones reales (precio sin IVA y peso)
  foreach ( array( 'peso' => 'Peso', 'formato' => 'Formato' ) as $slug => $nombre ) {
    if ( ! wc_attribute_taxonomy_id_by_name( 'pa_' . $slug ) ) wc_create_attribute( array( 'name' => $nombre, 'slug' => $slug, 'type' => 'select' ) );
    register_taxonomy( 'pa_' . $slug, 'product', array( 'hierarchical' => false ) );
  }
  $pesos = array( '3kg-de-hielo-seco' => array( '3kg de Hielo Seco', 3, '29.80' ), '10kg-de-hielo-seco' => array( '10kg de Hielo Seco', 10, '53.30' ), '15kg-de-hielo-seco' => array( '15kg de Hielo Seco', 15, '68.40' ), '20kg-de-hielo-seco' => array( '20kg de Hielo Seco', 20, '98.60' ) );
  foreach ( $pesos as $s => $v ) if ( ! term_exists( $s, 'pa_peso' ) ) wp_insert_term( $v[0], 'pa_peso', array( 'slug' => $s ) );
  foreach ( array( '3mm', '16mm' ) as $f ) if ( ! term_exists( $f, 'pa_formato' ) ) wp_insert_term( $f, 'pa_formato', array( 'slug' => $f ) );
  if ( ! get_page_by_path( 'hielo-seco', OBJECT, 'product' ) ) {
    $p = new WC_Product_Variable();
    $p->set_name( 'Hielo Seco' ); $p->set_slug( 'hielo-seco' ); $p->set_status( 'publish' );
    $p->set_short_description( 'Hielo seco en pellets de 3 mm y nuggets de 16 mm, en caja EPS de 40 mm.' );
    $atr = array();
    foreach ( array( 'pa_peso' => array_keys( $pesos ), 'pa_formato' => array( '3mm', '16mm' ) ) as $tax => $slugs ) {
      $a = new WC_Product_Attribute();
      $a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) ); $a->set_name( $tax );
      $a->set_options( array_map( static fn( $s ) => get_term_by( 'slug', $s, $tax )->term_id, $slugs ) );
      $a->set_visible( true ); $a->set_variation( true );
      $atr[] = $a;
    }
    $p->set_attributes( $atr );
    $id = $p->save();
    foreach ( $pesos as $s => $v ) foreach ( array( '3mm', '16mm' ) as $f ) {
      $var = new WC_Product_Variation();
      $var->set_parent_id( $id ); $var->set_attributes( array( 'pa_peso' => $s, 'pa_formato' => $f ) );
      $var->set_regular_price( $v[2] ); $var->set_weight( (string) $v[1] ); $var->set_stock_status( 'instock' );
      $var->save();
    }
    WC_Product_Variable::sync( $id );
  }
  // Zona de envío España con tarifa plana (el plugin la recalcula por peso) y recogida local
  $zonas = WC_Shipping_Zones::get_zones();
  if ( ! $zonas ) {
    $z = new WC_Shipping_Zone(); $z->set_zone_name( 'España' ); $z->add_location( 'ES', 'country' ); $z->save();
    $z->add_shipping_method( 'flat_rate' ); $z->add_shipping_method( 'local_pickup' );
  }
  update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes', 'title' => 'Contra reembolso' ) );
}
flush_rewrite_rules();
echo 'ok';
`;
writeFileSync('.tmp/tema-configurar.php', php);

const blueprint = {
  landingPage: '/',
  preferredVersions: { php: '8.2', wp: 'latest' },
  steps: [
    { step: 'installPlugin', pluginData: { resource: 'wordpress.org/plugins', slug: 'woocommerce' }, options: { activate: true } },
    { step: 'activateTheme', themeFolderName: 'dryicepack' },
    { step: 'runPHP', code: php },
  ],
};
writeFileSync('.tmp/blueprint-tema.json', JSON.stringify(blueprint, null, 2));

const npx = process.platform === 'win32' ? 'npx.cmd' : 'npx';
const args = ['--yes', '@wp-playground/cli@latest', 'server', '--port=9500',
  '--mount=./tema/dryicepack:/wordpress/wp-content/themes/dryicepack',
  '--blueprint=./.tmp/blueprint-tema.json'];
if (existsSync('plugin/dryicepack-tienda')) args.push('--mount=./plugin/dryicepack-tienda:/wordpress/wp-content/plugins/dryicepack-tienda');
const hijo = spawn(npx, args, { stdio: 'inherit', shell: process.platform === 'win32' });
hijo.on('exit', (c) => process.exit(c ?? 0));
