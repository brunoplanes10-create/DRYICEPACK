<?php
/**
 * Plugin Name:          Dryicepack · Tienda
 * Plugin URI:           https://dryicepack.es/
 * Description:          Lógica de la tienda de dryicepack.es: envío por peso, máximo de 250 kg, corte de las 12:00, festivos, sábado con suplemento, recogida en Mataró, checkout corto, factura después de la compra, cuentas de empresa, formularios sin spam, aviso de cookies con Google Consent Mode, redirecciones y exportación para Odoo.
 * Version:              1.0.0
 * Requires at least:    6.5
 * Requires PHP:         8.0
 * Author:               INDUNOVA IMS S.L.
 * License:              GPL-2.0-or-later
 * Text Domain:          dryicepack-tienda
 * WC requires at least: 8.5
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DIP_TIENDA_VERSION', '1.0.0' );
define( 'DIP_TIENDA_DIR', plugin_dir_path( __FILE__ ) );
define( 'DIP_TIENDA_URL', plugin_dir_url( __FILE__ ) );
define( 'DIP_TIENDA_ARCHIVO', __FILE__ );

/* Compatible con la tabla de pedidos moderna de WooCommerce (HPOS). El checkout es el clásico (shortcode), no el de bloques. */
add_action( 'before_woocommerce_init', static function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', DIP_TIENDA_ARCHIVO, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', DIP_TIENDA_ARCHIVO, false );
	}
} );

/*
 * Mientras el tema hijo antiguo siga activo, su functions.php ya cobra el envío, limita los kilos y filtra los pagos.
 * Si el plugin también lo hiciera, se cobraría dos veces: se queda en pausa y lo avisa.
 */
if ( 'divi-child-dryicepack' === get_option( 'stylesheet' ) ) {
	add_action( 'admin_notices', static function () {
		if ( ! current_user_can( 'activate_plugins' ) ) return;
		echo '<div class="notice notice-warning"><p><strong>Dryicepack · Tienda está en pausa.</strong> El tema hijo antiguo (Divi) sigue activo y ya hace este trabajo. Activa el tema «Dryicepack» y el plugin se pondrá en marcha solo.</p></div>';
	} );
	return;
}

require_once DIP_TIENDA_DIR . 'inc/ajustes.php';
require_once DIP_TIENDA_DIR . 'inc/negocio.php';
require_once DIP_TIENDA_DIR . 'inc/textos.php';
require_once DIP_TIENDA_DIR . 'inc/formularios.php';
require_once DIP_TIENDA_DIR . 'inc/cookies.php';
require_once DIP_TIENDA_DIR . 'inc/redirecciones.php';

add_action( 'plugins_loaded', static function () {
	if ( ! class_exists( 'WooCommerce' ) ) return;
	require_once DIP_TIENDA_DIR . 'inc/envio.php';
	require_once DIP_TIENDA_DIR . 'inc/entrega.php';
	require_once DIP_TIENDA_DIR . 'inc/checkout.php';
	require_once DIP_TIENDA_DIR . 'inc/pagos.php';
	require_once DIP_TIENDA_DIR . 'inc/factura.php';
	require_once DIP_TIENDA_DIR . 'inc/cuentas.php';
	require_once DIP_TIENDA_DIR . 'inc/informes.php';
}, 20 );

/* Al activar: reglas de URL de /factura/ y tarea diaria de recordatorios. */
register_activation_hook( __FILE__, static function () {
	update_option( 'dip_tienda_flush', 1 );
	if ( ! wp_next_scheduled( 'dip_tarea_diaria' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 09:00' ), 'daily', 'dip_tarea_diaria' );
	}
} );
register_deactivation_hook( __FILE__, static function () {
	wp_clear_scheduled_hook( 'dip_tarea_diaria' );
	flush_rewrite_rules();
} );
add_action( 'init', static function () {
	if ( get_option( 'dip_tienda_flush' ) ) {
		delete_option( 'dip_tienda_flush' );
		flush_rewrite_rules();
	}
}, 99 );
