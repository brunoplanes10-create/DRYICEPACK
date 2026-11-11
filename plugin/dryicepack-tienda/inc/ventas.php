<?php
/**
 * Técnicas de venta con datos reales (estrategia/VENTAS-PSICOLOGIA.md, 11/11/2026). Nada de urgencias, existencias ni
 * opiniones falsas: cada cifra sale de la tienda (precios, tarifas de MRW, calendario).
 *
 * 5.1 El pack siguiente en el checkout. Con un solo pack en el carrito (1 o más cajas iguales), una línea debajo del
 *     producto: «Con 15 kg, el kilo con envío te sale a 7,65 € en vez de 8,18 €: son 32,83 € más», y un botón que cambia
 *     el pack (mismo formato y mismo número de cajas) sin salir del checkout. Solo si el kilo de verdad baja.
 *     - Envío: el precio de la hora y el destino elegidos (dip_precio_servicio), con IVA.
 *     - Recogida: los precios redondos de recogida en efectivo (inc/precios-recogida.php); el sábado suma lo mismo a los dos.
 *     - No se ofrece si el pack siguiente no se puede (sin existencias, más de 250 kg, sábado de más de 80 kg).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * El pack siguiente del carrito, o null.
 * @return array{clave: string, padre: int, siguiente: WC_Product_Variation, cajas: int, kg: float, kg_siguiente: float,
 *               total: float, total_siguiente: float, kilo: float, kilo_siguiente: float, dif: float, extra: array}|null
 */
function dip_ventas_siguiente_pack() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) return null;
	$lineas = WC()->cart->get_cart();
	if ( 1 !== count( $lineas ) ) return null;
	$clave = (string) array_key_first( $lineas );
	$linea = $lineas[ $clave ];
	$v     = $linea['data'] ?? null;
	if ( ! $v instanceof WC_Product_Variation ) return null;
	$padre = wc_get_product( $v->get_parent_id() );
	$kg    = (float) dip_kg_producto( $v );
	$cajas = max( 1, (int) $linea['quantity'] );
	if ( ! $padre || $kg <= 0 ) return null;
	$formato = (string) ( $v->get_variation_attributes()['attribute_pa_formato'] ?? '' );
	$mejor   = null;
	foreach ( $padre->get_children() as $id ) {
		$s = wc_get_product( $id );
		if ( ! $s instanceof WC_Product_Variation || ! $s->is_purchasable() || ! $s->is_in_stock() ) continue;
		if ( (string) ( $s->get_variation_attributes()['attribute_pa_formato'] ?? '' ) !== $formato ) continue;
		$skg = (float) dip_kg_producto( $s );
		if ( $skg > $kg + 0.001 && ( ! $mejor || $skg < $mejor['kg'] ) ) $mejor = array( 'p' => $s, 'kg' => $skg );
	}
	if ( ! $mejor || $mejor['kg'] * $cajas > DIP_MAX_KG + 0.001 ) return null;

	$iva = dip_factor_iva();
	if ( 'recogida' === dip_metodo_elegido() ) {
		$total   = dip_precio_recogida_producto( $v ) * $cajas;
		$total_s = dip_precio_recogida_producto( $mejor['p'] ) * $cajas;
	} else {
		$fecha    = dip_fecha_elegida( 'envio' );
		$servicio = dip_servicio_elegido( $fecha );
		$destino  = function_exists( 'dip_destino_actual' ) ? dip_destino_actual() : null;
		$env      = dip_precio_servicio( $servicio, $kg * $cajas, $cajas, $destino );
		$env_s    = dip_precio_servicio( $servicio, $mejor['kg'] * $cajas, $cajas, $destino );
		if ( ! $env || ! $env_s ) return null; // sábado de más de 80 kg
		$total   = round( (float) wc_get_price_including_tax( $v ), 2 ) * $cajas + round( $env['neto'] * $iva, 2 );
		$total_s = round( (float) wc_get_price_including_tax( $mejor['p'] ), 2 ) * $cajas + round( $env_s['neto'] * $iva, 2 );
	}
	$kilo   = $total / ( $kg * $cajas );
	$kilo_s = $total_s / ( $mejor['kg'] * $cajas );
	if ( round( $kilo_s, 2 ) >= round( $kilo, 2 ) ) return null; // solo si el kilo baja
	$fuera = array_flip( array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' ) );
	return array(
		'clave'           => $clave,
		'padre'           => (int) $padre->get_id(),
		'siguiente'       => $mejor['p'],
		'cajas'           => $cajas,
		'kg'              => $kg,
		'kg_siguiente'    => $mejor['kg'],
		'total'           => round( $total, 2 ),
		'total_siguiente' => round( $total_s, 2 ),
		'kilo'            => round( $kilo, 2 ),
		'kilo_siguiente'  => round( $kilo_s, 2 ),
		'dif'             => round( $total_s - $total, 2 ),
		'extra'           => array_diff_key( $linea, $fuera ), // datos propios de la línea (p. ej. el código postal de la ficha)
	);
}

/* La línea, debajo del producto en el resumen del checkout (se repinta con cada cambio de día, hora o dirección) */
add_action( 'woocommerce_review_order_after_cart_contents', static function () {
	$s = dip_ventas_siguiente_pack();
	if ( ! $s ) return;
	$t      = dip_textos_tienda();
	$num    = static fn( $kg ) => rtrim( rtrim( number_format( (float) $kg, 1, ',', '' ), '0' ), ',' );
	$pack   = 1 === $s['cajas'] ? $num( $s['kg_siguiente'] ) . ' kg' : sprintf( $t['siguiente_cajas'], $s['cajas'], $num( $s['kg_siguiente'] ) );
	$recoge = 'recogida' === dip_metodo_elegido();
	printf(
		'<tr class="dip-siguiente"><td colspan="2"><p class="dip-siguiente__texto">%1$s</p><button type="button" class="dip-siguiente__boton" data-dip-pack="%2$s" data-dip-nonce="%3$s">%4$s</button></td></tr>',
		esc_html( sprintf( $recoge ? $t['siguiente_recogida'] : $t['siguiente_envio'], $pack, dip_texto_euros( $s['kilo_siguiente'] ), dip_texto_euros( $s['kilo'] ), dip_texto_euros( $s['dif'] ) ) ),
		esc_attr( (string) $s['siguiente']->get_id() ),
		esc_attr( wp_create_nonce( 'dip_cambiar_pack' ) ),
		esc_html( sprintf( $t['siguiente_boton'], $num( $s['kg_siguiente'] ) . ' kg' ) )
	);
} );

/* El botón: cambia el pack del carrito por el siguiente (mismo formato y cajas) y el checkout se recalcula */
add_action( 'wc_ajax_dip_cambiar_pack', static function () {
	check_ajax_referer( 'dip_cambiar_pack', 'nonce' );
	$s  = dip_ventas_siguiente_pack();
	$id = isset( $_POST['pack'] ) ? absint( $_POST['pack'] ) : 0;
	if ( ! $s || $id !== (int) $s['siguiente']->get_id() ) wp_send_json_error( array( 'motivo' => 'el carrito ha cambiado' ), 409 );
	$cart = WC()->cart;
	$cart->remove_cart_item( $s['clave'] );
	$nueva = $cart->add_to_cart( $s['padre'], $s['cajas'], $id, $s['siguiente']->get_variation_attributes(), $s['extra'] );
	if ( ! $nueva ) {
		$cart->restore_cart_item( $s['clave'] );
		wp_send_json_error( array( 'motivo' => 'no se ha podido cambiar' ), 500 );
	}
	wp_send_json_success( array( 'kg' => $s['kg_siguiente'] ) );
} );
