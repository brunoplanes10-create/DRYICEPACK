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

/* ---------- Pedir una combinación de packs desde la calculadora de envíos ----------
 * /?dipt_pedido=20x3,15x1&dipt_formato=3mm deja en el carrito exactamente esos packs (3 de 20 kg y 1 de 15 kg)
 * y lleva al pago. Solo packs que existen en la tienda, hasta el máximo por pedido. */
function dipt_url_pedido( array $cajas, $formato = '3mm' ) {
	$grupos = array_count_values( array_map( static fn( $kg ) => (string) ( 0 + $kg ), $cajas ) );
	krsort( $grupos, SORT_NUMERIC );
	$partes = array();
	foreach ( $grupos as $kg => $n ) $partes[] = $kg . 'x' . $n;
	return add_query_arg( array( 'dipt_pedido' => implode( ',', $partes ), 'dipt_formato' => $formato ), home_url( '/' ) );
}

add_action( 'wp_loaded', static function () {
	if ( empty( $_GET['dipt_pedido'] ) || ! function_exists( 'WC' ) || is_admin() ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- como los enlaces "añadir al carrito" de WooCommerce
	if ( function_exists( 'wc_load_cart' ) ) wc_load_cart();
	if ( ! WC()->cart ) return;
	$formato = sanitize_key( wp_unslash( $_GET['dipt_formato'] ?? '3mm' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $formato, array( '3mm', '16mm' ), true ) ) $formato = '3mm';
	$variantes = array();
	foreach ( dipt_variaciones() as $v ) {
		if ( $v['formato'] === $formato ) $variantes[ (string) ( 0 + $v['kg'] ) ] = $v;
	}
	$pedido = array();
	$total  = 0.0;
	foreach ( array_slice( explode( ',', sanitize_text_field( wp_unslash( $_GET['dipt_pedido'] ) ) ), 0, 6 ) as $parte ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! preg_match( '/^(\d+(?:\.\d+)?)x(\d{1,2})$/', trim( $parte ), $m ) ) continue;
		$v = $variantes[ (string) ( 0 + $m[1] ) ] ?? null;
		$n = (int) $m[2];
		if ( ! $v || $n < 1 ) continue;
		$pedido[] = array( $v, $n );
		$total   += $v['kg'] * $n;
	}
	$maximo = defined( 'DIP_MAX_KG' ) ? DIP_MAX_KG : 250;
	if ( ! $pedido || $total > $maximo + 0.0001 ) {
		wp_safe_redirect( dipt_url( 'envios' ) );
		exit;
	}
	// "Pedir 75 kg" significa exactamente eso: el carrito queda con esta combinación
	WC()->cart->empty_cart();
	foreach ( $pedido as list( $v, $n ) ) {
		WC()->cart->add_to_cart( $v['padre'], $n, $v['id'], array( 'attribute_pa_peso' => $v['peso'], 'attribute_pa_formato' => $v['fslug'] ) );
	}
	wp_safe_redirect( wc_get_checkout_url() );
	exit;
}, 25 );

/* ---------- Código postal en la ficha de compra ----------
 * La ficha pregunta por el código postal al escribir 5 cifras y contesta: provincia, si está fuera de la península,
 * si es envío a medida (más de 150 kg fuera de la provincia de Barcelona), primera entrega para ese destino
 * (festivos y cierres de MRW del plugin) y si hay sábado antes. Sin consultas externas: lee lo que guarda el plugin. */
add_action( 'wp_ajax_dipt_entrega_cp', 'dipt_ajax_entrega_cp' );
add_action( 'wp_ajax_nopriv_dipt_entrega_cp', 'dipt_ajax_entrega_cp' );
function dipt_ajax_entrega_cp() {
	if ( ! check_ajax_referer( 'dipt_entrega_cp', 'nonce', false ) ) wp_send_json_error( array( 'codigo' => 'nonce' ), 403 );
	$leer   = static fn( $k ) => isset( $_POST[ $k ] ) && is_string( $_POST[ $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : '';
	$cp     = preg_replace( '/\D/', '', $leer( 'cp' ) );
	$kg     = min( 10000.0, max( 0.0, (float) str_replace( ',', '.', $leer( 'kg' ) ) ) );
	$idioma = sanitize_key( $leer( 'idioma' ) );
	if ( ! in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ) $idioma = dipt_idioma();
	$datos = dipt_entrega_para_cp( $cp, $kg, $idioma );
	if ( ! $datos ) wp_send_json_error( array( 'codigo' => 'cp_no_valido', 'cp' => $cp ) );
	// Si el visitante ya tiene sesión de WooCommerce (carrito o cuenta), el checkout ya lo encuentra escrito. Sin sesión no se crea ninguna.
	if ( function_exists( 'wc_load_cart' ) ) wc_load_cart();
	if ( function_exists( 'WC' ) && WC()->session && WC()->session->has_session() ) dipt_guardar_cp_cliente( $cp );
	wp_send_json_success( $datos );
}

/** Datos de entrega para un código postal de 5 cifras y unos kilos. null si el código no existe en España. */
function dipt_entrega_para_cp( $cp, $kg = 0, $idioma = null ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	if ( 5 !== strlen( $cp ) ) return null;
	$prefijo = substr( $cp, 0, 2 );
	if ( (int) $prefijo < 1 || (int) $prefijo > 52 ) return null;
	$idioma  = $idioma ?: dipt_idioma();
	$limite  = defined( 'DIP_KG_A_MEDIDA' ) ? (float) DIP_KG_A_MEDIDA : 150.0;
	$codigo  = function_exists( 'dip_provincia_por_cp' ) ? dip_provincia_por_cp( $cp ) : '';
	$estados = ( function_exists( 'WC' ) && WC()->countries ) ? (array) WC()->countries->get_states( 'ES' ) : array();
	$fuera   = function_exists( 'dip_cp_fuera_de_peninsula' ) ? dip_cp_fuera_de_peninsula( $cp ) : in_array( $prefijo, array( '07', '35', '38', '51', '52' ), true );
	$prov    = html_entity_decode( (string) ( $estados[ $codigo ] ?? ( is_string( $fuera ) ? $fuera : '' ) ), ENT_QUOTES, 'UTF-8' );
	$medida  = function_exists( 'dip_envio_a_medida' ) ? dip_envio_a_medida( $kg, $cp ) : ( $kg > $limite && '08' !== $prefijo );
	$entrega = null;
	$sabado  = null;
	if ( ! $fuera ) {
		if ( function_exists( 'dip_fechas_disponibles' ) ) {
			// La misma lista que ofrece el checkout para ese destino: salta festivos y días en que MRW no reparte allí
			foreach ( dip_fechas_disponibles( 'envio', 8, null, array( 'cp' => $cp, 'poblacion' => '' ) ) as $f ) {
				if ( ! empty( $f['sabado'] ) ) { $sabado = $sabado ?: $f; continue; }
				$entrega = $entrega ?: $f;
				if ( $sabado ) break;
			}
		} else {
			$entrega = dipt_primera_entrega();
		}
	}
	$fecha = static fn( $f ) => $f ? array( 'fecha' => $f['fecha'], 'texto' => dipt_fecha_larga( $f['fecha'], $idioma ) ) : null;
	return array(
		'cp'        => $cp,
		'provincia' => $prov,
		'barcelona' => '08' === $prefijo,
		'fuera'     => (bool) $fuera,
		'medida'    => (bool) $medida,
		'kgMedida'  => $limite,
		'entrega'   => $fecha( $entrega ),
		'sabado'    => $fecha( $sabado ),
	);
}

/** Código postal (y provincia y país) en el cliente de WooCommerce: el checkout lo encuentra escrito. Solo en la sesión. */
function dipt_guardar_cp_cliente( $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	if ( 5 !== strlen( $cp ) || ! function_exists( 'WC' ) || ! WC()->customer ) return;
	$provincia = function_exists( 'dip_provincia_por_cp' ) ? dip_provincia_por_cp( $cp ) : '';
	if ( function_exists( 'dip_provincia_por_cp' ) && '' === $provincia ) return; // no existe
	$cliente = WC()->customer;
	$cliente->set_billing_country( 'ES' );
	$cliente->set_shipping_country( 'ES' );
	$cliente->set_billing_postcode( $cp );
	$cliente->set_shipping_postcode( $cp );
	if ( $provincia ) {
		$cliente->set_billing_state( $provincia );
		$cliente->set_shipping_state( $provincia );
	}
	$cliente->save();
}

/* Al añadir al carrito desde la ficha, el código postal escrito (campo dipt_cp) pasa al checkout: no se escribe dos veces. */
add_action( 'woocommerce_add_to_cart', static function () {
	$cp = isset( $_REQUEST['dipt_cp'] ) && is_string( $_REQUEST['dipt_cp'] ) ? preg_replace( '/\D/', '', wp_unslash( $_REQUEST['dipt_cp'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 5 === strlen( $cp ) && (int) substr( $cp, 0, 2 ) >= 1 && (int) substr( $cp, 0, 2 ) <= 52 ) dipt_guardar_cp_cliente( $cp );
}, 20 );

/* Nonce del endpoint en window.DIPT, solo en la ficha de compra, con el nombre de provincia de cada prefijo (unos 900 bytes):
 * si el servidor tarda, la ficha confirma el código al momento y la fecha exacta del destino llega después. */
add_action( 'wp_enqueue_scripts', static function () {
	if ( ! function_exists( 'dipt_hoja_pagina' ) || 'producto' !== dipt_hoja_pagina() ) return;
	$provincias = array();
	$estados    = ( function_exists( 'WC' ) && WC()->countries ) ? (array) WC()->countries->get_states( 'ES' ) : array();
	if ( $estados && function_exists( 'dip_provincia_por_cp' ) ) {
		for ( $i = 1; $i <= 52; $i++ ) {
			$pre  = sprintf( '%02d', $i );
			$prov = $estados[ dip_provincia_por_cp( $pre . '000' ) ] ?? '';
			if ( $prov ) $provincias[ $pre ] = html_entity_decode( $prov, ENT_QUOTES, 'UTF-8' );
		}
	}
	wp_add_inline_script( 'dipt-pagina', 'window.DIPT=window.DIPT||{};window.DIPT.cpNonce=' . wp_json_encode( wp_create_nonce( 'dipt_entrega_cp' ) ) . ';window.DIPT.cpProvincias=' . wp_json_encode( (object) $provincias ) . ';', 'before' );
}, 21 );

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
	if ( is_cart() || is_checkout() || is_account_page() ) return;
	foreach ( array( 'wc-blocks-style', 'wc-blocks-vendors-style', 'photoswipe', 'photoswipe-default-skin' ) as $h ) wp_dequeue_style( $h );
	// La ficha de compra añade al carrito con un formulario normal (plantillas/producto.php): sin jQuery ni scripts de Woo,
	// que bloqueaban el primer pintado (unos 100 KB y 1 s en Lighthouse móvil)
	foreach ( array( 'wc-cart-fragments', 'woocommerce', 'wc-add-to-cart', 'wc-add-to-cart-variation', 'wc-single-product', 'zoom', 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'wc-photoswipe', 'wc-zoom', 'wc-flexslider', 'wc-jquery-blockui', 'jquery-blockui', 'wc-js-cookie', 'js-cookie' ) as $h ) wp_dequeue_script( $h );
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

/* Sin JavaScript, el formulario de compra envía formato y kilos: aquí se traducen a la variación antes de que WooCommerce añada al carrito. */
add_action( 'wp_loaded', static function () {
	if ( empty( $_GET['add-to-cart'] ) || empty( $_GET['dipt_kg'] ) || empty( $_GET['dipt_formato'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$kg      = (float) wp_unslash( $_GET['dipt_kg'] ); // phpcs:ignore
	$formato = sanitize_key( wp_unslash( $_GET['dipt_formato'] ) ); // phpcs:ignore
	foreach ( dipt_variaciones() as $v ) {
		if ( (float) $v['kg'] !== $kg || $v['formato'] !== $formato || empty( $v['padre'] ) ) continue;
		$_GET['variation_id'] = $_REQUEST['variation_id'] = (string) $v['id'];
		$_GET['attribute_pa_peso'] = $_REQUEST['attribute_pa_peso'] = $v['peso'];
		$_GET['attribute_pa_formato'] = $_REQUEST['attribute_pa_formato'] = $v['fslug'];
		break;
	}
}, 5 );
