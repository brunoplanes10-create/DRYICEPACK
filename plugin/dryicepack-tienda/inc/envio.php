<?php
/**
 * Envío: coste por peso, recogida gratis en Mataró, solo península, máximo 250 kg por pedido.
 * Migrado del functions.php de producción (01/09/2026) sin cambiar el resultado de ningún cálculo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_es_recogida( $metodo_id ) {
	return false !== strpos( (string) $metodo_id, 'local_pickup' );
}

/** ¿El cliente ha elegido recoger en Mataró? */
function dip_eligio_recogida() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) return false;
	foreach ( (array) WC()->session->get( 'chosen_shipping_methods' ) as $m ) {
		if ( dip_es_recogida( $m ) ) return true;
	}
	return false;
}

/* ---------- Coste por peso y orden: envío primero, recogida al final ---------- */
add_filter( 'woocommerce_package_rates', static function ( $rates, $package ) {
	$cp = $package['destination']['postcode'] ?? '';
	if ( 'ES' === ( $package['destination']['country'] ?? 'ES' ) && dip_cp_fuera_de_peninsula( $cp ) ) return array();

	$kg     = 0.0;
	$bultos = 0;
	foreach ( (array) ( $package['contents'] ?? array() ) as $linea ) {
		$cantidad = max( 0, (int) ( $linea['quantity'] ?? 0 ) );
		$kg_ud    = dip_kg_producto( $linea['data'] ?? null );
		if ( $kg_ud <= 0 || ! $cantidad ) continue;
		$kg     += $kg_ud * $cantidad;
		$bultos += $cantidad;
	}
	if ( $kg <= 0 ) return $rates;
	if ( $kg > DIP_MAX_KG + 0.0001 ) return array();
	$a_medida = dip_envio_a_medida( $kg, $cp ); // más de 150 kg fuera de la provincia de Barcelona: solo recogida online

	$coste    = dip_coste_transporte( $kg, $bultos );
	$envios   = array();
	$recogida = array();
	foreach ( $rates as $id => $rate ) {
		if ( dip_es_recogida( $rate->get_method_id() ) ) {
			$rate->set_cost( 0 );
			$rate->set_taxes( array() );
			$recogida[ $id ] = $rate;
			continue;
		}
		if ( $a_medida ) continue;
		$rate->set_cost( $coste );
		$rate->set_taxes( class_exists( 'WC_Tax' ) && wc_tax_enabled() ? WC_Tax::calc_shipping_tax( $coste, WC_Tax::get_shipping_tax_rates() ) : array() );
		$envios[ $id ] = $rate;
	}
	return $envios + $recogida;
}, 10001, 2 );

/* La opción marcada por defecto es el envío; la recogida solo si el cliente la elige. */
add_filter( 'woocommerce_shipping_chosen_method', static function ( $defecto, $rates, $elegido ) {
	if ( $elegido && isset( $rates[ $elegido ] ) ) return $elegido;
	foreach ( $rates as $id => $rate ) {
		if ( ! dip_es_recogida( $rate->get_method_id() ) ) return $id;
	}
	return $defecto;
}, 9999, 3 );

/* Nombres claros: la recogida es en nuestra nave, no en una oficina de la agencia. */
add_filter( 'woocommerce_shipping_rate_label', static function ( $label, $rate = null ) {
	if ( ! is_object( $rate ) ) return $label;
	$t = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
	if ( dip_es_recogida( $rate->get_method_id() ) ) return $t['recogida'] ?? 'Recoger en nuestra nave de Mataró · Gratis';
	return $t['envio'] ?? 'Envío por mensajería · por la mañana';
}, 10, 2 );

add_action( 'woocommerce_after_shipping_rate', static function ( $rate ) {
	if ( ! dip_es_recogida( $rate->get_method_id() ) ) return;
	$c = dip_contacto();
	$t = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
	printf(
		'<p class="dip-nota-recogida">%s<br>%s · %s %s · %s</p>',
		esc_html( $t['recogida_nota'] ?? 'No es un punto de la agencia de transporte: se recoge en nuestras instalaciones.' ),
		esc_html( $c['direccion'] ),
		esc_html( $c['cp'] ),
		esc_html( $c['localidad'] ),
		esc_html( $t['recogida_horario'] ?? 'L–V 9:00–18:00, avisando antes' )
	);
} );

/* ---------- Mensajes cuando no hay envío posible ---------- */
function dip_mensaje_sin_envio( $html ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) return $html;
	$t   = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
	$kg  = dip_carrito_kg()['kg'];
	if ( $kg > DIP_MAX_KG + 0.0001 ) {
		return '<p class="dip-aviso">' . esc_html( sprintf( $t['max_kg'] ?? 'El máximo por pedido online es de %d kg. Para más cantidad, pide precio por volumen.', DIP_MAX_KG ) ) . '</p>';
	}
	$cp = WC()->customer ? ( WC()->customer->get_shipping_postcode() ?: WC()->customer->get_billing_postcode() ) : '';
	if ( dip_cp_fuera_de_peninsula( $cp ) ) {
		return '<p class="dip-aviso">' . esc_html( $t['fuera_peninsula'] ?? 'Enviamos solo a la península. Escríbenos y estudiamos tu caso.' ) . '</p>';
	}
	if ( dip_envio_a_medida( $kg, $cp ) && function_exists( 'dip_html_envio_a_medida' ) ) return dip_html_envio_a_medida();
	return $html;
}
add_filter( 'woocommerce_no_shipping_available_html', 'dip_mensaje_sin_envio' );
add_filter( 'woocommerce_cart_no_shipping_available_html', 'dip_mensaje_sin_envio' );

/* ---------- Máximo 250 kg por pedido ---------- */
add_filter( 'woocommerce_add_to_cart_validation', static function ( $ok, $producto_id, $cantidad, $variacion_id = 0 ) {
	if ( ! $ok || ! WC()->cart ) return $ok;
	$producto = wc_get_product( $variacion_id ?: $producto_id );
	$kg_ud    = dip_kg_producto( $producto );
	if ( $kg_ud <= 0 ) return $ok;
	$actual = dip_carrito_kg()['kg'];
	if ( $actual + $kg_ud * max( 1, (int) $cantidad ) > DIP_MAX_KG + 0.0001 ) {
		$t = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
		wc_add_notice( sprintf( $t['max_kg_ahora'] ?? 'El máximo por pedido es de %1$d kg y ya llevas %2$s kg. Para más cantidad, pide precio por volumen.', DIP_MAX_KG, wc_format_localized_decimal( $actual ) ), 'error' );
		return false;
	}
	return $ok;
}, 10, 4 );

add_filter( 'woocommerce_update_cart_validation', static function ( $ok, $clave, $valores, $cantidad ) {
	if ( ! $ok || ! WC()->cart ) return $ok;
	$total = 0.0;
	foreach ( WC()->cart->get_cart() as $k => $linea ) {
		$q      = ( $k === $clave ) ? (int) $cantidad : (int) $linea['quantity'];
		$total += dip_kg_producto( $linea['data'] ?? null ) * max( 0, $q );
	}
	if ( $total > DIP_MAX_KG + 0.0001 ) {
		$t = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
		wc_add_notice( sprintf( $t['max_kg'] ?? 'El máximo por pedido online es de %d kg. Para más cantidad, pide precio por volumen.', DIP_MAX_KG ), 'error' );
		return false;
	}
	return $ok;
}, 10, 4 );

add_action( 'woocommerce_check_cart_items', static function () {
	if ( dip_carrito_kg()['kg'] > DIP_MAX_KG + 0.0001 ) {
		$t = function_exists( 'dip_textos_tienda' ) ? dip_textos_tienda() : array();
		wc_add_notice( sprintf( $t['max_kg'] ?? 'El máximo por pedido online es de %d kg. Para más cantidad, pide precio por volumen.', DIP_MAX_KG ), 'error' );
	}
} );

/* ---------- La tienda es un solo producto: /tienda/ y categorías llevan a la ficha ---------- */
function dip_url_producto() {
	$p = get_page_by_path( DIP_PRODUCT_SLUG, OBJECT, 'product' );
	return $p ? get_permalink( $p ) : home_url( '/producto/' . DIP_PRODUCT_SLUG . '/' );
}
add_action( 'template_redirect', static function () {
	if ( is_admin() ) return;
	if ( ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
		wp_safe_redirect( dip_url_producto(), 301 );
		exit;
	}
}, 9 );
