<?php
/**
 * Plugin Name:          Dryicepack · Tienda
 * Plugin URI:           https://dryicepack.es/
 * Description:          Lógica de la tienda de dryicepack.es: envío por peso, máximo de 250 kg, corte de las 12:00, festivos, sábado con suplemento, recogida en Mataró a precio redondo, pack pequeño de 3 o 5 kg, datos para la app INDUNOVA, checkout corto, factura después de la compra, petición de reseña el día después de la entrega, cuentas de empresa, formularios sin spam, aviso de cookies con Google Consent Mode, redirecciones y exportación para Odoo.
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

/*
 * Claves de los pedidos que lee la app INDUNOVA Control (03/10/2026). No las renombres sin avisar:
 * _dip_fecha_entrega, _dip_metodo_entrega, _orddd_lite_timestamp, _dip_idioma, _dip_empresa, _dip_factura_datos,
 * _dip_factura_mensual, _dip_canal, _dip_fs_numero, _dip_factura_emitida, _dip_kg, _dip_cajas.
 * Un pedido con _dip_fs_numero ya tiene factura simplificada de la web: la app no lo manda a «Por facturar».
 * La app también lee GET /wp-json/dip/v1/envio (inc/api-app.php) y copia dip_mrw_nombres() y dip_mrw_mismo_sitio().
 */

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
	foreach ( array( 'dip_eh_aviso_sabado', 'dip_eh_aviso_reparto', 'dip_eh_aviso_sin_numero', 'dip_eh_reintentar' ) as $dip_tarea ) wp_unschedule_hook( $dip_tarea ); // inc/envios-hoy.php
	wp_unschedule_hook( 'dip_avisos_diarios' ); // inc/avisos.php: «Hoy llega» y recordatorio de recogida a las 8:00
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
require_once DIP_TIENDA_DIR . 'inc/api-app.php';      // GET /wp-json/dip/v1/envio para la app INDUNOVA Control (sin datos personales)
require_once DIP_TIENDA_DIR . 'inc/textos.php';
require_once DIP_TIENDA_DIR . 'inc/formularios.php';
require_once DIP_TIENDA_DIR . 'inc/cookies.php';
require_once DIP_TIENDA_DIR . 'inc/redirecciones.php';

add_action( 'plugins_loaded', static function () {
	if ( ! class_exists( 'WooCommerce' ) ) return;
	require_once DIP_TIENDA_DIR . 'inc/envio.php';
	require_once DIP_TIENDA_DIR . 'inc/precios-recogida.php'; // recogida: precio redondo con IVA incluido, en efectivo
	require_once DIP_TIENDA_DIR . 'inc/pack-pequeno.php';     // 3 kg hasta que se acabe; después 5 kg
	require_once DIP_TIENDA_DIR . 'inc/entrega.php';
	require_once DIP_TIENDA_DIR . 'inc/checkout.php';
	require_once DIP_TIENDA_DIR . 'inc/pagos.php';
	require_once DIP_TIENDA_DIR . 'inc/factura.php';
	require_once DIP_TIENDA_DIR . 'inc/factura-simplificada.php'; // particulares hasta 400 €: factura simplificada por email (se para el 1/1/2027, VeriFactu)
	require_once DIP_TIENDA_DIR . 'inc/cuentas.php';
	require_once DIP_TIENDA_DIR . 'inc/informes.php';
	require_once DIP_TIENDA_DIR . 'inc/seguimiento-entregas.php'; // revisión diaria: pedidos reservados con antelación que caen en un festivo de MRW
	require_once DIP_TIENDA_DIR . 'inc/resenas.php';              // email «¿Qué tal llegó tu hielo seco?» el día después de la entrega (reseña en Google)
	require_once DIP_TIENDA_DIR . 'inc/aviso-halloween.php';      // «Avísame» de la landing de Halloween fuera de campaña (con confirmación por email)
	require_once DIP_TIENDA_DIR . 'inc/ofertas.php';              // casilla «No quiero recibir avisos ni ofertas» (art. 21.2 LSSI), lista para ofertas y página de baja
	require_once DIP_TIENDA_DIR . 'inc/envios-hoy.php';           // «Envíos de hoy»: número de MRW, aviso «va de camino» y avisos internos de sábado y sin número
	require_once DIP_TIENDA_DIR . 'inc/mrw-sagec.php';            // envíos en MRW con su servicio web: se crean el día de salida, número en el pedido y en AST, etiquetas
	require_once DIP_TIENDA_DIR . 'inc/urgente.php';              // enlace de pedido urgente (comprar para mañana después de las 12:00) y «Adelantar a mañana»
	require_once DIP_TIENDA_DIR . 'inc/ventas.php';               // técnicas de venta con datos reales: el pack siguiente en el checkout (estrategia/VENTAS-PSICOLOGIA.md)
	require_once DIP_TIENDA_DIR . 'inc/mi-pedido.php';            // «Mi pedido»: estado del pedido sin cuenta, con enlace firmado (/mi-pedido/, /ca/la-meva-comanda/, /en/my-order/)
	require_once DIP_TIENDA_DIR . 'inc/cuenta-cliente.php';       // cuenta opcional después de pagar: la ficha de cliente en la página de gracias
	require_once DIP_TIENDA_DIR . 'inc/avisos-textos.php';        // textos de los emails al cliente (ES/CA/EN, tú y usted)
	require_once DIP_TIENDA_DIR . 'inc/emails.php';               // diseño común de todos los emails (plantillas de WooCommerce sustituidas)
	require_once DIP_TIENDA_DIR . 'inc/avisos.php';               // pedido recibido y avisos: va de camino, hoy llega (8:00), recogida (8:00), incidencia
	require_once DIP_TIENDA_DIR . 'inc/condiciones-pdf.php';      // condiciones de venta en PDF adjuntas al email de pedido recibido
}, 20 );

add_action( 'init', static function () {
	if ( get_option( 'dip_tienda_flush' ) ) {
		delete_option( 'dip_tienda_flush' );
		flush_rewrite_rules();
	}
	// Si el plugin se activó con una versión anterior en pausa, la tarea diaria no llegó a programarse
	dip_tienda_programar_tarea_diaria();
}, 99 );
