<?php
/**
 * Ajustes de presentación de WooCommerce (la lógica de negocio está en el plugin dryicepack-tienda).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* Contador del carrito sin el script de fragmentos de WooCommerce (lo pide movimiento.js solo si hay carrito) */
add_action( 'wp_ajax_dipt_carrito', 'dipt_ajax_carrito' );
add_action( 'wp_ajax_nopriv_dipt_carrito', 'dipt_ajax_carrito' );
function dipt_ajax_carrito() {
	if ( function_exists( 'wc_load_cart' ) ) wc_load_cart();
	$n = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	wp_send_json_success( array( 'cantidad' => $n ) );
}

/* Envoltorios propios en lugar de los del tema por defecto */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action( 'woocommerce_before_main_content', static function () {
	echo '<section class="seccion seccion--tienda"><div class="contenedor tienda">';
}, 10 );
add_action( 'woocommerce_after_main_content', static function () {
	echo '</div></section>';
}, 10 );

/* Sin estilos de WooCommerce fuera de la tienda */
add_action( 'wp_enqueue_scripts', static function () {
	$tienda = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
	if ( $tienda ) return;
	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style' ) as $h ) wp_dequeue_style( $h );
	foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'sourcebuster-js', 'wc-order-attribution' ) as $h ) wp_dequeue_script( $h );
}, 99 );
