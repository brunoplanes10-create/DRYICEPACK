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

/** Primera tarea diaria: mañana a las 9:00, hora de Madrid. */
function dip_tienda_programar_tarea_diaria() {
	if ( wp_next_scheduled( 'dip_tarea_diaria' ) ) return;
	wp_schedule_event( ( new DateTimeImmutable( 'tomorrow 09:00', new DateTimeZone( 'Europe/Madrid' ) ) )->getTimestamp(), 'daily', 'dip_tarea_diaria' );
}

/*
 * Al activar: reglas de URL de /factura/ y tarea diaria de recordatorios. Al desactivar: se quitan las tareas.
 * Van antes de la pausa: la guía de instalación activa el plugin con el tema hijo de Divi todavía activo,
 * y si se registraran después del "return" de la pausa, WordPress no las llamaría nunca.
 */
register_activation_hook( __FILE__, static function () {
	update_option( 'dip_tienda_flush', 1 );
	dip_tienda_programar_tarea_diaria();
} );
register_deactivation_hook( __FILE__, static function () {
	wp_clear_scheduled_hook( 'dip_tarea_diaria' );
	wp_clear_scheduled_hook( 'dip_mrw_actualizar' );
	wp_clear_scheduled_hook( 'dip_revisar_entregas' );
	flush_rewrite_rules();
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
require_once DIP_TIENDA_DIR . 'inc/festivos-mrw.php'; // festivos de MRW (salida desde Mataró y destino); el tema también los usa
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
	require_once DIP_TIENDA_DIR . 'inc/factura-simplificada.php'; // particulares hasta 400 €: factura simplificada por email (se para el 1/1/2027, VeriFactu)
	require_once DIP_TIENDA_DIR . 'inc/cuentas.php';
	require_once DIP_TIENDA_DIR . 'inc/informes.php';
	require_once DIP_TIENDA_DIR . 'inc/seguimiento-entregas.php'; // revisión diaria: pedidos reservados con antelación que caen en un festivo de MRW
}, 20 );

add_action( 'init', static function () {
	if ( get_option( 'dip_tienda_flush' ) ) {
		delete_option( 'dip_tienda_flush' );
		flush_rewrite_rules();
	}
	// Si el plugin se activó con una versión anterior en pausa, la tarea diaria no llegó a programarse
	dip_tienda_programar_tarea_diaria();
}, 99 );
