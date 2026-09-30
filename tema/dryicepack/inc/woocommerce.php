<?php
/**
 * WooCommerce con el diseño del tema.
 * La ficha de producto usa plantillas/producto.php (el mismo configurador que las páginas de compra en catalán e inglés).
 * Carrito, checkout y Mi cuenta usan page.php con las hojas de estilo del tema (tienda.css). La lógica está en el plugin.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Contador del carrito sin el script de fragmentos de WooCommerce. */
add_action( 'wp_ajax_dipt_carrito', 'dipt_ajax_carrito' );
add_action( 'wp_ajax_nopriv_dipt_carrito', 'dipt_ajax_carrito' );
function dipt_ajax_carrito() {
	if ( function_exists( 'wc_load_cart' ) ) wc_load_cart();
	wp_send_json_success( array( 'cantidad' => WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0 ) );
}

/* "Pedir 10 kg" desde cualquier página: añade al carrito y va directo al checkout. */
add_filter( 'woocommerce_add_to_cart_redirect', static function ( $url ) {
	if ( isset( $_REQUEST['dipt_ir'] ) && 'checkout' === $_REQUEST['dipt_ir'] ) return wc_get_checkout_url(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return $url;
}, 20 );

/* Sin estilos de WooCommerce: el tema dibuja todo. Fuera de la tienda, tampoco sus scripts. */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_action( 'wp_enqueue_scripts', static function () {
	// Atribución de pedidos (cookies de seguimiento): no se usa y así no hace falta pedir consentimiento para ella.
	foreach ( array( 'sourcebuster-js', 'wc-order-attribution' ) as $h ) wp_dequeue_script( $h );
	$tienda = is_cart() || is_checkout() || is_account_page() || ( function_exists( 'is_product' ) && is_product() ) || 'producto' === dipt_pagina_actual();
	if ( $tienda ) return;
	foreach ( array( 'wc-blocks-style', 'wc-blocks-vendors-style' ) as $h ) wp_dequeue_style( $h );
	foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'wc-add-to-cart-variation', 'jquery-blockui', 'js-cookie' ) as $h ) wp_dequeue_script( $h );
}, 99 );
add_filter( 'woocommerce_order_attribution_allow_tracking', '__return_false' );

/* Nombre del pack más claro en carrito, checkout y emails: "Hielo seco · 10 kg · 16 mm". */
add_filter( 'woocommerce_cart_item_name', static function ( $nombre, $item ) {
	$p = $item['data'] ?? null;
	if ( ! $p || ! $p->is_type( 'variation' ) ) return $nombre;
	$kg = function_exists( 'dip_kg_producto' ) ? dip_kg_producto( $p ) : 0;
	$f  = (string) $p->get_attribute( 'pa_formato' );
	$base = 'en' === dipt_idioma() ? 'Dry ice' : ( 'ca' === dipt_idioma() ? 'Gel sec' : 'Hielo seco' );
	return $kg ? esc_html( $base . ' · ' . wc_format_localized_decimal( $kg ) . ' kg · ' . str_replace( 'mm', ' mm', strtolower( $f ) ) ) : $nombre;
}, 20, 2 );

/* Página de la ficha y páginas de tienda: sin barra lateral ni envoltorios por defecto. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/* Carrito vacío con salida clara. */
add_filter( 'wc_empty_cart_message', static function () {
	return esc_html( array( 'es' => 'Tu carrito está vacío.', 'ca' => 'La cistella és buida.', 'en' => 'Your basket is empty.' )[ dipt_idioma() ] ?? 'Tu carrito está vacío.' );
} );
add_filter( 'woocommerce_return_to_shop_redirect', static function () {
	return dipt_url( 'producto' );
} );
add_filter( 'woocommerce_return_to_shop_text', static function () {
	return dipt_t( 'comprar' );
} );

/* El producto se indexa siempre (en producción llegó a tener noindex guardado). */
add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$robots['index'] = 'index';
		unset( $robots['noindex'] );
	}
	return $robots;
}, 9999 );
