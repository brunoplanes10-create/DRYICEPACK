<?php
/**
 * Pack pequeño (decidido el 03/10/2026).
 * - El de 3 kg se vende hasta que se acaben las cajas EPS de 3 kg: el stock va en las dos variaciones de 3 kg.
 * - Cuando no queda ninguna, entra el de 5 kg a 33,90 € (no antes). Mientras se vende el de 3 kg, el de 5 kg
 *   no se puede comprar ni se ve; después, al revés.
 * - Los textos del tema que recomiendan "3 kg" usan dip_pack_pequeno() y pasan solos a 5 kg ese día.
 * - Al acabarse el 3 kg llega un email al correo de avisos (también si no hay pack de 5 kg creado).
 *   Alternativa manual: Dryicepack → Ajustes → Pack pequeño (siempre 3 kg o siempre 5 kg).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Variaciones publicadas y con precio del producto de la tienda con esos kilos. */
function dip_variaciones_de_kg( $kg ) {
	static $por_kg = null;
	if ( null === $por_kg ) {
		$por_kg = array();
		$post   = get_page_by_path( DIP_PRODUCT_SLUG, OBJECT, 'product' );
		$padre  = $post ? wc_get_product( $post->ID ) : null;
		if ( $padre && $padre->is_type( 'variable' ) ) {
			foreach ( $padre->get_children() as $id ) {
				$v = wc_get_product( $id );
				if ( ! $v || 'publish' !== $v->get_status( 'edit' ) || '' === (string) $v->get_price( 'edit' ) ) continue;
				$por_kg[ (string) ( 0 + dip_kg_producto( $v ) ) ][] = $v;
			}
		}
	}
	return $por_kg[ (string) ( 0 + (float) $kg ) ] ?? array();
}

/** ¿Queda alguna caja de 3 kg a la venta? (alguna variación de 3 kg publicada y con stock) */
function dip_queda_3kg() {
	foreach ( dip_variaciones_de_kg( 3 ) as $v ) {
		if ( $v->is_in_stock() ) return true;
	}
	return false;
}

/** Kilos del pack pequeño que se vende ahora: 3 o 5. */
function dip_pack_pequeno( $recalcular = false ) {
	static $kg = null;
	if ( null !== $kg && ! $recalcular ) return $kg;
	$modo = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'pack_pequeno' ) : 'auto';
	if ( '3' === $modo || '5' === $modo ) return $kg = (int) $modo;
	// Automático: 3 kg mientras quede; 5 kg cuando se acaba, si el pack de 5 kg está creado (si no, el texto sigue en 3 y avisa)
	return $kg = ( ! dip_queda_3kg() && dip_variaciones_de_kg( 5 ) ) ? 5 : 3;
}

/** Línea de estado para Ajustes. */
function dip_pack_pequeno_estado() {
	$hay5 = (bool) dip_variaciones_de_kg( 5 );
	$tres = array();
	foreach ( dip_variaciones_de_kg( 3 ) as $v ) {
		$stock  = $v->managing_stock() ? (int) $v->get_stock_quantity() . ' cajas' : ( $v->is_in_stock() ? 'sin control de stock' : 'agotado' );
		$tres[] = (string) $v->get_attribute( 'pa_formato' ) . ': ' . $stock;
	}
	return 'Ahora se vende el de ' . dip_pack_pequeno() . ' kg. 3 kg: ' . ( $tres ? implode( ' · ', $tres ) : 'no hay variaciones' ) . '. 5 kg: ' . ( $hay5 ? 'creado' : 'no creado todavía (crea las variaciones de 5 kg a 33,90 €; la web las esconde mientras quede 3 kg)' ) . '.';
}

/* La variación que no toca no se puede comprar ni se ve (en el escritorio sí, para pedidos hechos a mano). */
function dip_variacion_fuera_de_temporada( $variacion ) {
	if ( is_admin() && ! wp_doing_ajax() ) return false;
	$kg = (float) dip_kg_producto( $variacion );
	if ( 3.0 !== $kg && 5.0 !== $kg ) return false;
	return (int) $kg !== dip_pack_pequeno();
}
add_filter( 'woocommerce_variation_is_purchasable', static function ( $si, $variacion ) {
	return $si && ! dip_variacion_fuera_de_temporada( $variacion );
}, 10, 2 );
add_filter( 'woocommerce_variation_is_visible', static function ( $si, $id, $padre, $variacion = null ) {
	return $si && ! ( $variacion && dip_variacion_fuera_de_temporada( $variacion ) );
}, 10, 4 );

/* Aviso por email cuando se acaba el 3 kg (una vez; vuelve a avisar si se repone y se acaba otra vez). */
add_action( 'woocommerce_variation_set_stock_status', static function ( $id, $estado, $variacion = null ) {
	$variacion = $variacion ?: wc_get_product( $id );
	if ( ! $variacion || 3.0 !== (float) dip_kg_producto( $variacion ) ) return;
	if ( 'outofstock' !== $estado ) {
		delete_option( 'dip_pack_pequeno_aviso' );
		return;
	}
	if ( dip_queda_3kg_sin( (int) $id ) || get_option( 'dip_pack_pequeno_aviso' ) ) return;
	$hay5   = (bool) dip_variaciones_de_kg( 5 );
	$modo   = (string) dip_ajuste( 'pack_pequeno' );
	$asunto = $hay5 ? 'Dryicepack: se ha acabado el pack de 3 kg, ya se vende el de 5 kg' : 'Dryicepack: se ha acabado el pack de 3 kg y no hay pack de 5 kg';
	$texto  = $hay5
		? "Las dos variaciones de 3 kg están sin stock. Desde ahora la web vende el pack de 5 kg y los textos que recomendaban 3 kg dicen 5 kg.\n\nNo hay que hacer nada. Si quedan cajas de 3 kg, pon el stock en la variación y la web vuelve sola al 3 kg."
		: "Las dos variaciones de 3 kg están sin stock y el pack de 5 kg no está creado: ahora el pack más pequeño de la web es el de 10 kg.\n\nQué hacer: en WooCommerce → Productos → Hielo seco, crea las variaciones de 5 kg (3 mm y 16 mm) a 33,90 € con peso 5 kg. La web las pone a la venta sola.";
	if ( 'auto' !== $modo ) $texto .= "\n\nOjo: en Dryicepack → Ajustes, «Pack pequeño» está fijado en {$modo} kg; el cambio automático no se aplica hasta que lo pongas en «Automático».";
	if ( wp_mail( (string) dip_ajuste( 'email_avisos' ), $asunto, $texto . "\n\nAjustes: " . admin_url( 'admin.php?page=dryicepack' ) ) ) {
		update_option( 'dip_pack_pequeno_aviso', gmdate( 'c' ), false );
	}
}, 10, 3 );

/**
 * ¿Queda 3 kg sin contar una variación? (la que acaba de agotarse puede no estar guardada todavía)
 * Las demás se leen otra vez de la tienda: las que guarda dip_variaciones_de_kg() pueden ser de antes de esta venta
 * (si un mismo pedido se lleva las últimas cajas de 3 mm y de 16 mm, la primera seguiría "con stock" y no avisaría).
 */
function dip_queda_3kg_sin( $id ) {
	foreach ( dip_variaciones_de_kg( 3 ) as $v ) {
		if ( (int) $v->get_id() === (int) $id ) continue;
		$fresca = wc_get_product( $v->get_id() );
		if ( $fresca && $fresca->is_in_stock() ) return true;
	}
	return false;
}
