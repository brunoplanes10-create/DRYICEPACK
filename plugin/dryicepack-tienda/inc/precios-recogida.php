<?php
/**
 * Precio de recogida en la nave (decidido el 03/10/2026): el mismo en la web y en Mataró.
 * - Precio redondo con IVA incluido: el precio de la web × IVA, redondeado a la baja a múltiplos de 5 €.
 *   3 kg 35 € · 5 kg 40 € · 10 kg 60 € · 15 kg 90 € · 20 kg 115 €. Se calcula solo: si cambia el precio de la web, cambia este.
 * - Se paga en efectivo y con el importe exacto, así que el total tiene que salir justo al céntimo con cualquier cantidad.
 *   La tienda guarda los precios sin IVA. En recogida, cada pack pasa a valer el precio redondo dividido por el IVA,
 *   sin redondear (35 / 1,21 = 28,9256…): WooCommerce calcula la cuota sobre esa base y base + cuota da 35,00 € justos,
 *   también con varias cajas y varios packs (la parte de céntimo nunca cae en 0,5). Se hace así, y no poniendo
 *   "precios con IVA incluido" en toda la tienda, para que la ficha, las calculadoras y el resto de la web no cambien.
 * - Solo en el carrito y mientras el método elegido es la recogida; al volver al envío se recupera el precio de la web.
 * - El suplemento de sábado de la recogida (10 € IVA incluido) está en inc/entrega.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Precio redondo de recogida (IVA incluido) para un precio de la web sin IVA. 0 si no hay precio. */
function dip_precio_recogida_de( $precio_web_sin_iva ) {
	$con_iva = round( (float) $precio_web_sin_iva * dip_factor_iva(), 2 );
	if ( $con_iva <= 0 ) return 0.0;
	$paso = max( 1, (int) DIP_RECOGIDA_REDONDEO );
	$redondo = floor( $con_iva / $paso + 1e-9 ) * $paso;
	return (float) ( $redondo > 0 ? $redondo : $con_iva ); // un precio de menos de 5 € no se queda en 0
}

/** Precio de la web sin IVA de un producto, leído de la tienda (no del carrito, que en recogida ya lo lleva cambiado). */
function dip_precio_web( $producto ) {
	static $memo = array();
	if ( ! $producto || ! is_object( $producto ) || ! method_exists( $producto, 'get_id' ) ) return 0.0;
	$id = (int) $producto->get_id();
	if ( ! isset( $memo[ $id ] ) ) {
		$tienda      = $id ? wc_get_product( $id ) : null;
		$memo[ $id ] = (float) ( $tienda ? $tienda->get_price( 'edit' ) : $producto->get_price( 'edit' ) );
	}
	return $memo[ $id ];
}

/** Precio de recogida (IVA incluido) de un producto del carrito o de la tienda. */
function dip_precio_recogida_producto( $producto ) {
	return dip_precio_recogida_de( dip_precio_web( $producto ) );
}

/**
 * Precios de recogida con IVA de los packs que se venden ahora, por kilos: array( '3' => 35.0, '10' => 60.0, … ).
 * Para el checkout, el tema y la app. Vacío si la tienda no tiene el producto.
 */
function dip_precios_recogida() {
	static $precios = null;
	if ( null !== $precios ) return $precios;
	$precios = array();
	$post    = get_page_by_path( DIP_PRODUCT_SLUG, OBJECT, 'product' );
	$padre   = $post ? wc_get_product( $post->ID ) : null;
	if ( ! $padre || ! $padre->is_type( 'variable' ) ) return $precios;
	foreach ( $padre->get_children() as $id ) {
		$v = wc_get_product( $id );
		if ( ! $v || ! $v->is_purchasable() ) continue;
		$kg = (string) ( 0 + dip_kg_producto( $v ) );
		if ( '0' === $kg || isset( $precios[ $kg ] ) ) continue;
		$p = dip_precio_recogida_de( $v->get_price( 'edit' ) );
		if ( $p > 0 ) $precios[ $kg ] = $p;
	}
	ksort( $precios, SORT_NUMERIC );
	return $precios;
}

/**
 * En recogida, cada línea del carrito pasa a la base del precio redondo; si no, vuelve al precio de la web.
 * Se restaura siempre porque en una misma petición el carrito se calcula varias veces (al cargar la sesión con
 * la recogida elegida y después con el envío): sin esto, un envío podría salir al precio de recogida.
 */
add_action( 'woocommerce_before_calculate_totals', static function ( $carrito ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ! $carrito || ! function_exists( 'dip_eligio_recogida' ) ) return;
	$recoge = dip_eligio_recogida();
	$factor = dip_factor_iva();
	dip_recogida_aplicada( $recoge );
	foreach ( $carrito->get_cart() as $linea ) {
		$producto = $linea['data'] ?? null;
		if ( ! $producto || ! is_object( $producto ) || dip_kg_producto( $producto ) <= 0 ) continue;
		$web = dip_precio_web( $producto );
		if ( $web <= 0 ) continue;
		$producto->set_price( $recoge ? dip_precio_recogida_de( $web ) / $factor : $web );
	}
}, 20 );

/** Método con el que se pusieron los precios en el último cálculo del carrito (true = recogida; null = ninguno). */
function dip_recogida_aplicada( $valor = null ) {
	static $aplicada = null;
	if ( null !== $valor ) $aplicada = (bool) $valor;
	return $aplicada;
}

/*
 * WooCommerce elige el método DENTRO del cálculo, después de poner los precios: si el elegido deja de estar entre
 * las tarifas (p. ej. más de 150 kg fuera de la provincia de Barcelona, donde solo queda la recogida), cambia el método
 * de la sesión y el total saldría con los precios del otro método. En ese caso se calcula otra vez (una sola).
 */
add_action( 'woocommerce_after_calculate_totals', static function ( $carrito ) {
	static $repitiendo = false;
	if ( $repitiendo || ( is_admin() && ! wp_doing_ajax() ) || ! $carrito || ! function_exists( 'dip_eligio_recogida' ) ) return;
	$aplicada = dip_recogida_aplicada();
	if ( null === $aplicada || dip_eligio_recogida() === $aplicada ) return;
	$repitiendo = true;
	try {
		$carrito->calculate_totals();
	} finally {
		$repitiendo = false;
	}
}, 1 );

/** "3 kg 35,00 € · 10 kg 60,00 € · …" para la pantalla de Ajustes. */
function dip_texto_precios_recogida_admin() {
	$partes = array();
	foreach ( dip_precios_recogida() as $kg => $precio ) $partes[] = str_replace( '.', ',', (string) $kg ) . ' kg ' . dip_euros( $precio );
	return $partes ? implode( ' · ', $partes ) . ' (IVA incluido)' : 'No se encuentra el producto «' . DIP_PRODUCT_SLUG . '» en la tienda.';
}

/** Total de la recogida con IVA del carrito actual (packs a precio de recogida; sin suplemento de sábado). */
function dip_total_recogida_carrito() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) return 0.0;
	$total = 0.0;
	foreach ( WC()->cart->get_cart() as $linea ) {
		$total += dip_precio_recogida_producto( $linea['data'] ?? null ) * max( 0, (int) ( $linea['quantity'] ?? 0 ) );
	}
	return round( $total, 2 );
}
