<?php
/**
 * Pruebas del pedido urgente (inc/urgente.php) y del «pack siguiente» (inc/ventas.php) en el WordPress de prueba
 * (puerto 9500), después de la revisión del 12/11/2026: Store API (Apple Pay / Google Pay), enlace desde las 12:00,
 * mensaje del sábado sin suplemento fijo, recogida sin marca, reintento de pago, textos nuevos y el pack siguiente que
 * no deja el carrito sin envío. No manda nada a MRW ni emails.
 *
 * herramientas/probar-urgente.mjs copia este archivo a plugin/dryicepack-tienda/zz-prueba-urgente.php, lo llama y lo borra.
 * Al acabar deja todo como estaba (pedidos de prueba borrados, enlaces urgentes restaurados, carrito vacío).
 * La parte de la Store API define REST_REQUEST: va la última.
 */
require dirname( __DIR__, 3 ) . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
if ( ! function_exists( 'dip_urgente_crear' ) || ! function_exists( 'dip_ventas_siguiente_pack' ) || ! function_exists( 'wc_get_orders' ) ) {
	exit( "FALLO: falta WooCommerce, inc/urgente.php o inc/ventas.php\n0 comprobaciones, 1 fallos\n" );
}
add_filter( 'pre_wp_mail', '__return_false' ); // ningún email sale
add_filter( 'pre_http_request', static fn( $pre, $args, $url ) => false !== strpos( (string) $url, 'mrw.es' ) ? new WP_Error( 'prueba', 'sin MRW en la prueba' ) : $pre, 10, 3 );

/* ---------- Herramientas ---------- */
$GLOBALS['fallos']  = 0;
$GLOBALS['pruebas'] = 0;
function ok( $cond, $msg, $detalle = null ) {
	$GLOBALS['pruebas']++;
	if ( $cond ) return true;
	$GLOBALS['fallos']++;
	echo 'FALLO: ' . $msg . ( null === $detalle ? '' : ' → ' . ( is_string( $detalle ) ? $detalle : wp_json_encode( $detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) . "\n";
	return false;
}
function a_las( $ymd_hora ) {
	return new DateTimeImmutable( $ymd_hora, dip_zona_horaria() );
}
function fresco( WC_Order $p ) {
	return wc_get_order( $p->get_id() );
}
function variacion( $kg ) {
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	$padre = $post ? wc_get_product( $post->ID ) : null;
	foreach ( $padre ? $padre->get_children() : array() as $id ) {
		$v = wc_get_product( $id );
		$a = $v ? $v->get_attributes() : array();
		if ( ( $a['pa_peso'] ?? '' ) === $kg . 'kg-de-hielo-seco' && ( $a['pa_formato'] ?? '' ) === '3mm' ) return $v;
	}
	return null;
}
/** Pedido de prueba sin pagar. $d: clave, metodo (envio/recogida), fecha, urgente (línea «Pedido urgente»), estado. */
function crear( array $d ) {
	$recoge = 'recogida' === ( $d['metodo'] ?? 'envio' );
	$p      = new WC_Order();
	$p->set_created_via( 'prueba-urgente' );
	$dir = array( 'first_name' => 'Laura García Pons', 'last_name' => '', 'address_1' => 'Carrer de Mallorca 123', 'postcode' => '08013', 'city' => 'Barcelona', 'state' => 'B', 'country' => 'ES' );
	$p->set_address( $dir + array( 'email' => $d['clave'] . '@urgente.test', 'phone' => '600123456' ), 'billing' );
	$p->set_address( $dir, 'shipping' );
	$v = variacion( 10 );
	if ( $v ) $p->add_product( $v, 1 );
	$envio = new WC_Order_Item_Shipping();
	$envio->set_props( $recoge ? array( 'method_title' => 'Recogida en Mataró', 'method_id' => 'local_pickup', 'total' => 0 ) : array( 'method_title' => 'Envío MRW', 'method_id' => 'flat_rate', 'total' => 13.19 ) );
	$p->add_item( $envio );
	if ( ! empty( $d['urgente'] ) ) {
		$fee = new WC_Order_Item_Fee();
		$fee->set_props( array( 'name' => 'Pedido urgente', 'total' => 9.70, 'tax_status' => 'taxable' ) );
		$fee->add_meta_data( '_dip_urgente', 1, true );
		$p->add_item( $fee );
	}
	$p->update_meta_data( '_dip_fecha_entrega', $d['fecha'] ?? '2026-12-03' );
	$p->update_meta_data( '_dip_metodo_entrega', $recoge ? 'recogida' : 'envio' );
	$p->update_meta_data( '_dip_prueba_urgente', $d['clave'] );
	$p->calculate_totals( true );
	$p->set_status( $d['estado'] ?? 'pending' );
	$p->save();
	return fresco( $p );
}
function enlace( $importe, $cuando ) {
	$e = dip_urgente_crear( $importe, 'prueba', a_las( $cuando ) );
	return is_array( $e ) ? $e : null;
}
function limpiar() {
	$ids = wc_get_orders( array( 'limit' => -1, 'return' => 'ids', 'meta_key' => '_dip_prueba_urgente', 'meta_compare' => 'EXISTS', 'type' => 'shop_order', 'status' => array_keys( wc_get_order_statuses() ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( $p ) $p->delete( true );
	}
	$copia = get_option( 'dip_prueba_urgente_copia' );
	if ( is_array( $copia ) ) {
		if ( null === $copia['dip_urgentes'] ) delete_option( 'dip_urgentes' );
		else update_option( 'dip_urgentes', $copia['dip_urgentes'], false );
		delete_option( 'dip_prueba_urgente_copia' );
	}
	unset( $_COOKIE['dip_urgente'] );
	if ( function_exists( 'WC' ) && WC()->session ) {
		foreach ( array( 'dip_urgente', 'order_awaiting_payment', 'store_api_draft_order', 'dip_fecha', 'chosen_shipping_methods' ) as $k ) WC()->session->set( $k, null );
	}
	if ( function_exists( 'WC' ) && WC()->cart ) WC()->cart->empty_cart();
	return count( $ids );
}

limpiar(); // por si una prueba anterior se cortó
update_option( 'dip_prueba_urgente_copia', array( 'dip_urgentes' => get_option( 'dip_urgentes', null ) ), false );

try {
	/* ---------- El enlace solo se crea desde las 12:00 ---------- */
	$antes = dip_urgente_crear( 9.70, '', a_las( '2026-12-02 11:00' ) );
	ok( is_wp_error( $antes ) && false !== strpos( $antes->get_error_message(), 'sin enlace' ), 'a las 11:00 no se crea (se pide sin enlace)', is_wp_error( $antes ) ? $antes->get_error_message() : $antes );
	ok( is_array( dip_urgente_crear( 9.70, '', a_las( '2026-12-02 12:00' ) ) ), 'a las 12:00 en punto ya se crea' );

	/* ---------- Mensaje del viernes: sin suplemento de sábado fijo ---------- */
	$vie = enlace( 0, '2026-12-04 14:00' );
	ok( $vie && '2026-12-05' === $vie['entrega'], 'enlace del viernes a las 14:00: abre el sábado', $vie );
	if ( $vie ) {
		$msg = dip_urgente_mensaje( $vie );
		ok( false === strpos( $msg, '11,74' ) && false !== strpos( $msg, 'propio precio de envío, según zona y peso' ), 'el mensaje del sábado no promete 11,74 €: su propio precio según zona y peso', $msg );
		ok( false === strpos( $msg, 'suplemento' ), 'enlace a 0 €: el mensaje no habla de suplementos', $msg );
	}

	/* ---------- Textos nuevos en los tres idiomas ---------- */
	foreach ( array( 'es', 'ca', 'en' ) as $idioma ) {
		$t = dip_textos_tienda( $idioma );
		ok( isset( $t['urgente_nota_sin'] ) && 1 === substr_count( $t['urgente_nota_sin'], '%3$s' ) && false === stripos( $t['urgente_nota_sin'], 'suplement' ) && false === stripos( $t['urgente_nota_sin'], 'surcharge' ), $idioma . ': nota urgente sin suplemento (enlace a 0 €)' );
		foreach ( array( 'repetido_recogida', 'repetido_parcial', 'repetido_parcial_recogida' ) as $k ) {
			ok( isset( $t[ $k ] ) && 1 === substr_count( $t[ $k ], '%s' ), $idioma . ': texto ' . $k );
		}
		ok( false === stripos( $t['repetido_recogida'], 'misma dirección' ) && false === stripos( $t['repetido_recogida'], 'mateixa adreça' ) && false === stripos( $t['repetido_recogida'], 'same address' ), $idioma . ': repetir una recogida no habla de la dirección' );
	}

	/* ---------- Recogida: no se marca ni gasta el enlace ---------- */
	$e1 = enlace( 9.70, '2026-12-02 14:00' );
	$_COOKIE['dip_urgente'] = $e1['codigo'];
	$rec = crear( array( 'clave' => 'recogida', 'metodo' => 'recogida', 'urgente' => true ) );
	dip_urgente_marcar_pedido( $rec );
	ok( empty( fresco( $rec )->get_meta( '_dip_urgente' ) ) && 0 === (int) dip_urgente_lista()[ $e1['codigo'] ]['pedido'], 'una recogida no queda marcada como urgente ni gasta el enlace' );
	$env = crear( array( 'clave' => 'envio', 'urgente' => true ) );
	dip_urgente_marcar_pedido( $env );
	ok( is_array( fresco( $env )->get_meta( '_dip_urgente' ) ) && (int) $env->get_id() === (int) dip_urgente_lista()[ $e1['codigo'] ]['pedido'], 'un envío con el suplemento sí: marcado y enlace usado' );

	/* ---------- Reintento de pago: el enlace usado vale para su pedido sin pagar ---------- */
	if ( WC()->session ) {
		ok( null === dip_urgente_activo(), 'usado y sin pedido en la sesión: no vale' );
		WC()->session->set( 'order_awaiting_payment', $env->get_id() );
		ok( is_array( dip_urgente_activo() ), 'usado, con su pedido pendiente en la sesión (checkout clásico): vale' );
		WC()->session->set( 'order_awaiting_payment', null );
		WC()->session->set( 'store_api_draft_order', $env->get_id() );
		ok( is_array( dip_urgente_activo() ), 'y con su pedido en la Store API (store_api_draft_order): vale' );
		$pagado = fresco( $env );
		$pagado->set_status( 'processing' );
		$pagado->save();
		ok( null === dip_urgente_activo(), 'con el pedido ya pagado: no vale' );
		$pagado->set_status( 'failed' );
		$pagado->save();
		ok( is_array( dip_urgente_activo() ), 'pago rechazado (fallido): vuelve a valer' );

		// Al retomarlo sin el suplemento y para otro día ya no es urgente: pierde la marca y el enlace queda libre
		$sin = fresco( $env );
		foreach ( $sin->get_items( 'fee' ) as $id => $item ) $sin->remove_item( $id );
		$sin->update_meta_data( '_dip_fecha_entrega', '2026-12-09' );
		$sin->calculate_totals( true );
		$sin->save();
		dip_urgente_marcar_pedido( fresco( $sin ) );
		ok( empty( fresco( $sin )->get_meta( '_dip_urgente' ) ) && 0 === (int) dip_urgente_lista()[ $e1['codigo'] ]['pedido'], 'retomado sin suplemento y para otro día: sin marca y enlace libre' );
		WC()->session->set( 'store_api_draft_order', null );

		// El código también se lee de la sesión (pago exprés con Cart-Token, sin cookies)
		unset( $_COOKIE['dip_urgente'] );
		ok( null === dip_urgente_activo(), 'sin cookie ni sesión: no vale' );
		WC()->session->set( 'dip_urgente', $e1['codigo'] );
		ok( is_array( dip_urgente_activo() ), 'sin cookie, con el código en la sesión: vale' );
		WC()->session->set( 'dip_urgente', str_repeat( 'a', 32 ) );
		ok( null === dip_urgente_activo(), 'un código inventado en la sesión no vale' );
		WC()->session->set( 'dip_urgente', null );
	} else {
		ok( false, 'no hay sesión de WooCommerce en la prueba' );
	}
	unset( $_COOKIE['dip_urgente'] );

	/* ---------- El pack siguiente no deja el carrito sin envío ---------- */
	$v15 = variacion( 15 );
	if ( WC()->cart && WC()->customer && $v15 && variacion( 20 ) ) {
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $v15->get_parent_id(), 8, $v15->get_id(), $v15->get_variation_attributes() );
		// Sin 8 cajas de 15 y de 20 kg en la tienda de prueba, las dos comprobaciones de abajo pasarían sin probar nada
		ok( 1 === count( WC()->cart->get_cart() ) && variacion( 20 )->has_enough_stock( 8 ), 'la tienda de prueba tiene 8 cajas de 15 kg en el carrito y 8 de 20 kg en stock (si no, el pack siguiente no se prueba)' );
		foreach ( array( 'billing', 'shipping' ) as $tipo ) {
			WC()->customer->{"set_{$tipo}_postcode"}( '46001' );
			WC()->customer->{"set_{$tipo}_city"}( 'València' );
		}
		ok( null === dip_ventas_siguiente_pack(), '8 × 15 kg a Valencia: no ofrece 8 × 20 kg (160 kg, más de 150 kg fuera de Barcelona)' );
		foreach ( array( 'billing', 'shipping' ) as $tipo ) {
			WC()->customer->{"set_{$tipo}_postcode"}( '' );
			WC()->customer->{"set_{$tipo}_city"}( '' );
		}
		ok( null === dip_ventas_siguiente_pack(), 'sin código postal todavía: tampoco (pasaría de 150 kg)' );
		WC()->cart->empty_cart();
	} else {
		ok( false, 'no hay carrito o faltan los packs de 15 y 20 kg en la tienda de prueba' );
	}

	/* ---------- Store API (va la última: define REST_REQUEST) ---------- */
	$e2 = enlace( 9.70, '2026-12-02 14:00' );
	$_COOKIE['dip_urgente'] = $e2['codigo'];
	if ( ! defined( 'REST_REQUEST' ) ) define( 'REST_REQUEST', true );
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	$_SERVER['REQUEST_URI'] = '/wp-json/dip/v1/envio';
	ok( ! dip_urgente_es_store_api() && null === dip_urgente_activo() && 720 === dip_corte_minutos( 'envio', a_las( '2026-12-02 14:00' ) ), 'REST que no es de la tienda (dip/v1/envio): el enlace no vale y el corte sigue a las 12:00' );
	$_SERVER['REQUEST_URI'] = '/wp-json/wc/store/v1/checkout';
	ok( dip_urgente_es_store_api() && is_array( dip_urgente_activo() ), 'Store API (/wc/store/v1/checkout): el enlace vale' );
	ok( 1050 === dip_corte_minutos( 'envio', a_las( '2026-12-02 14:00' ) ) && dip_fecha_es_valida( '2026-12-03', 'envio', null, a_las( '2026-12-02 14:00' ) ), 'Store API: corte a las 17:30 y el jueves se puede elegir (Apple Pay / Google Pay)' );
	$_SERVER['REQUEST_URI'] = $uri;
} catch ( Throwable $t ) {
	ok( false, 'excepción: ' . $t->getMessage() . ' en ' . basename( $t->getFile() ) . ':' . $t->getLine() );
} finally {
	limpiar();
}
echo $GLOBALS['pruebas'] . ' comprobaciones, ' . $GLOBALS['fallos'] . " fallos\n";
