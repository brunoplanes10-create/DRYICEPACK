<?php
/**
 * Checkout corto en tres pasos (Contacto → Entrega → Pago).
 * - 6 campos obligatorios: email, teléfono, nombre y apellidos, dirección, código postal y población. Piso opcional.
 * - Provincia desde el código postal y país fijo (España): no se preguntan.
 * - La entrega va a la misma dirección (sin "enviar a otra dirección").
 * - Factura completa: casilla "Compro para una empresa o autónomo" → razón social + NIF/CIF.
 *   Obligatorio siempre por encima del límite de los ajustes (400 € IVA incl.). Las cuentas de empresa lo traen relleno.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Campos ---------- */
add_filter( 'woocommerce_checkout_fields', static function ( $campos ) {
	$t = dip_textos_tienda();
	$b = $campos['billing'] ?? array();

	$nuevo = array(
		'billing_email'      => array_merge( $b['billing_email'] ?? array(), array( 'label' => $t['email'], 'required' => true, 'priority' => 10, 'class' => array( 'form-row-wide', 'dip-campo' ), 'autocomplete' => 'email', 'type' => 'email' ) ),
		'billing_phone'      => array_merge( $b['billing_phone'] ?? array(), array( 'label' => $t['telefono'], 'required' => true, 'priority' => 20, 'class' => array( 'form-row-wide', 'dip-campo' ), 'autocomplete' => 'tel', 'type' => 'tel', 'description' => $t['telefono_ayuda'] ) ),
		'billing_first_name' => array_merge( $b['billing_first_name'] ?? array(), array( 'label' => $t['nombre'], 'required' => true, 'priority' => 30, 'class' => array( 'form-row-wide', 'dip-campo' ), 'autocomplete' => 'name' ) ),
		'billing_address_1'  => array_merge( $b['billing_address_1'] ?? array(), array( 'label' => $t['direccion'], 'placeholder' => $t['direccion_ph'], 'required' => true, 'priority' => 40, 'class' => array( 'form-row-wide', 'dip-campo' ), 'autocomplete' => 'address-line1' ) ),
		'billing_address_2'  => array_merge( $b['billing_address_2'] ?? array(), array( 'label' => $t['piso'], 'placeholder' => '', 'required' => false, 'priority' => 50, 'class' => array( 'form-row-wide', 'dip-campo' ), 'autocomplete' => 'address-line2', 'label_class' => array() ) ),
		'billing_postcode'   => array_merge( $b['billing_postcode'] ?? array(), array( 'label' => $t['cp'], 'required' => true, 'priority' => 60, 'class' => array( 'form-row-first', 'dip-campo', 'update_totals_on_change' ), 'autocomplete' => 'postal-code', 'custom_attributes' => array( 'inputmode' => 'numeric', 'maxlength' => '5', 'pattern' => '[0-9]{5}' ) ) ),
		'billing_city'       => array_merge( $b['billing_city'] ?? array(), array( 'label' => $t['poblacion'], 'required' => true, 'priority' => 70, 'class' => array( 'form-row-last', 'dip-campo' ), 'autocomplete' => 'address-level2' ) ),
		// País: España y oculto (WooCommerce lo necesita para impuestos y zonas de envío).
		'billing_country'    => array_merge( $b['billing_country'] ?? array(), array( 'type' => 'hidden', 'default' => 'ES', 'required' => false, 'priority' => 90, 'class' => array( 'dip-oculto' ), 'label' => '' ) ),
		'dip_empresa'        => array( 'type' => 'checkbox', 'label' => $t['empresa_check'], 'required' => false, 'priority' => 100, 'class' => array( 'form-row-wide', 'dip-casilla-empresa' ) ),
		'billing_company'    => array_merge( $b['billing_company'] ?? array(), array( 'label' => $t['razon_social'], 'required' => false, 'priority' => 110, 'class' => array( 'form-row-first', 'dip-campo', 'dip-fiscal' ), 'autocomplete' => 'organization' ) ),
		'billing_nif'        => array( 'type' => 'text', 'label' => $t['nif'], 'required' => false, 'priority' => 120, 'class' => array( 'form-row-last', 'dip-campo', 'dip-fiscal' ), 'custom_attributes' => array( 'autocapitalize' => 'characters', 'spellcheck' => 'false' ) ),
		'dip_direccion_fiscal' => array( 'type' => 'text', 'label' => $t['direccion_fiscal'], 'required' => false, 'priority' => 130, 'class' => array( 'form-row-wide', 'dip-campo', 'dip-fiscal' ) ),
	);
	$campos['billing'] = $nuevo;
	$campos['shipping'] = array();
	$campos['order']    = array(
		'order_comments' => array_merge( $campos['order']['order_comments'] ?? array(), array( 'type' => 'textarea', 'label' => $t['notas'], 'placeholder' => $t['notas_ph'], 'required' => false, 'class' => array( 'form-row-wide', 'dip-campo', 'dip-notas' ) ) ),
	);
	return $campos;
}, 2000 );

/* Sin formulario de "enviar a otra dirección": se entrega donde dice la dirección. */
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false', 50 );
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

/* Sin la palabra "(opcional)" pegada a las etiquetas: la etiqueta ya lo dice cuando hace falta. */
add_filter( 'woocommerce_form_field', static function ( $html ) {
	return is_checkout() ? preg_replace( '#\s*<span class="optional">.*?</span>#i', '', $html ) : $html;
}, 20 );

/* El teléfono es obligatorio también para el JavaScript de direcciones de WooCommerce. */
add_filter( 'woocommerce_get_country_locale_default', static function ( $campos ) {
	$campos['phone']['required'] = true;
	return $campos;
}, 9999 );
add_filter( 'woocommerce_get_country_locale', static function ( $locale ) {
	foreach ( $locale as $pais => $campos ) {
		if ( isset( $campos['state'] ) ) $locale[ $pais ]['state']['required'] = false;
	}
	$locale['ES']['state']['required'] = false;
	$locale['ES']['state']['hidden']   = true;
	return $locale;
}, 9999 );

/* ---------- Datos enviados: provincia desde el CP, país, NIF normalizado ---------- */
add_filter( 'woocommerce_checkout_posted_data', static function ( $datos ) {
	$cp                          = preg_replace( '/\D/', '', (string) ( $datos['billing_postcode'] ?? '' ) );
	$datos['billing_postcode']   = $cp;
	$datos['billing_country']    = 'ES';
	$datos['billing_state']      = dip_provincia_por_cp( $cp );
	$datos['billing_last_name']  = $datos['billing_last_name'] ?? '';
	$datos['billing_nif']        = dip_normalizar_nif( $datos['billing_nif'] ?? '' );
	$datos['billing_company']    = trim( (string) ( $datos['billing_company'] ?? '' ) );
	// Los datos fiscales cuentan si marcó la casilla o si el pedido supera el límite (entonces la casilla va marcada y fija).
	$limite               = (float) dip_ajuste( 'limite_factura' );
	$supera               = $limite > 0 && WC()->cart && (float) WC()->cart->get_total( 'edit' ) > $limite;
	$datos['dip_empresa'] = ( ! empty( $datos['dip_empresa'] ) || $supera ) ? 1 : 0;
	if ( ! $datos['dip_empresa'] ) {
		$datos['billing_company']      = '';
		$datos['billing_nif']          = '';
		$datos['dip_direccion_fiscal'] = '';
	}
	foreach ( array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state' ) as $k ) {
		$datos[ 'shipping_' . $k ] = $datos[ 'billing_' . $k ] ?? '';
	}
	return $datos;
} );

function dip_normalizar_nif( $valor ) {
	return preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $valor ) );
}

/** DNI, NIE o CIF español con su dígito de control; los NIF-IVA de otros países de la UE se aceptan si tienen forma de serlo. */
function dip_nif_valido( $nif ) {
	$nif = dip_normalizar_nif( $nif );
	if ( preg_match( '/^ES([A-Z0-9]{9})$/', $nif, $m ) ) $nif = $m[1];
	$letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
	if ( preg_match( '/^(\d{8})([A-Z])$/', $nif, $m ) ) return $letras[ (int) $m[1] % 23 ] === $m[2];
	if ( preg_match( '/^([XYZ])(\d{7})([A-Z])$/', $nif, $m ) ) {
		$num = (int) ( strpos( 'XYZ', $m[1] ) . $m[2] );
		return $letras[ $num % 23 ] === $m[3];
	}
	if ( preg_match( '/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', $nif, $m ) ) {
		$pares = 0;
		$impares = 0;
		for ( $i = 0; $i < 7; $i++ ) {
			$d = (int) $m[2][ $i ];
			if ( 0 === $i % 2 ) {
				$x = $d * 2;
				$impares += intdiv( $x, 10 ) + $x % 10;
			} else {
				$pares += $d;
			}
		}
		$control = ( 10 - ( ( $pares + $impares ) % 10 ) ) % 10;
		$letra   = 'JABCDEFGHI'[ $control ];
		if ( in_array( $m[1], array( 'A', 'B', 'E', 'H' ), true ) ) return (string) $control === $m[3];
		if ( in_array( $m[1], array( 'K', 'P', 'Q', 'S', 'N', 'W' ), true ) ) return $letra === $m[3];
		return (string) $control === $m[3] || $letra === $m[3];
	}
	// NIF-IVA de otro país de la UE (2 letras + 8 a 12 caracteres)
	return (bool) preg_match( '/^(AT|BE|BG|CY|CZ|DE|DK|EE|EL|FI|FR|HR|HU|IE|IT|LT|LU|LV|MT|NL|PL|PT|RO|SE|SI|SK)[A-Z0-9]{8,12}$/', $nif );
}

/* ---------- Validación ---------- */
add_action( 'woocommerce_after_checkout_validation', static function ( $datos, $errores ) {
	$t = dip_textos_tienda();
	if ( ! preg_match( '/^\d{5}$/', (string) ( $datos['billing_postcode'] ?? '' ) ) ) {
		$errores->add( 'billing_postcode', $t['cp_no_valido'] );
	} elseif ( dip_cp_fuera_de_peninsula( $datos['billing_postcode'] ) ) {
		$errores->add( 'billing_postcode', $t['fuera_peninsula'] );
	}
	if ( strlen( preg_replace( '/\D/', '', (string) ( $datos['billing_phone'] ?? '' ) ) ) < 9 ) {
		$errores->add( 'billing_phone', $t['telefono_no_valido'] );
	}

	$limite  = (float) dip_ajuste( 'limite_factura' );
	$total   = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0.0;
	$supera  = $limite > 0 && $total > $limite;
	$empresa = ! empty( $datos['dip_empresa'] );
	if ( ! $empresa && ! $supera ) return;

	if ( '' === trim( (string) ( $datos['billing_company'] ?? '' ) ) || '' === (string) ( $datos['billing_nif'] ?? '' ) ) {
		$errores->add( 'billing_company', $supera ? sprintf( $t['datos_fiscales_400'], wp_strip_all_tags( wc_price( $limite ) ) ) : $t['datos_fiscales'] );
		return;
	}
	if ( ! dip_nif_valido( $datos['billing_nif'] ) ) {
		$errores->add( 'billing_nif', $t['nif_no_valido'] );
	}
}, 10, 2 );

/* ---------- Datos del pedido para facturar, medir y exportar a Odoo ---------- */
add_action( 'woocommerce_checkout_create_order', static function ( $pedido, $datos ) {
	$nif = (string) ( $datos['billing_nif'] ?? '' );
	if ( $nif ) {
		foreach ( array( '_billing_nif', '_billing_dni_nie', '_billing_cif', '_billing_vat' ) as $clave ) $pedido->update_meta_data( $clave, $nif );
	}
	$pedido->update_meta_data( '_dip_empresa', empty( $datos['dip_empresa'] ) ? 0 : 1 );
	if ( ! empty( $datos['dip_direccion_fiscal'] ) ) $pedido->update_meta_data( '_dip_direccion_fiscal', sanitize_text_field( $datos['dip_direccion_fiscal'] ) );
	dip_guardar_resumen_pedido( $pedido );
}, 20, 2 );

/** Kilos, cajas, formatos, canal, idioma y provincia: para el resumen mensual y la exportación a Odoo. */
function dip_guardar_resumen_pedido( WC_Order $pedido ) {
	$kg       = 0.0;
	$cajas    = 0;
	$formatos = array();
	foreach ( $pedido->get_items() as $linea ) {
		$producto = $linea->get_product();
		$cantidad = (int) $linea->get_quantity();
		$kg_ud    = dip_kg_producto( $producto );
		$kg      += $kg_ud * $cantidad;
		$cajas   += $kg_ud > 0 ? $cantidad : 0;
		$formato  = $producto ? (string) $producto->get_attribute( 'pa_formato' ) : '';
		if ( $formato ) $formatos[ $formato ] = ( $formatos[ $formato ] ?? 0 ) + $kg_ud * $cantidad;
	}
	$pedido->update_meta_data( '_dip_kg', $kg );
	$pedido->update_meta_data( '_dip_cajas', $cajas );
	$pedido->update_meta_data( '_dip_formatos', implode( ', ', array_map( static fn( $f, $k ) => $f . ': ' . wc_format_localized_decimal( $k ) . ' kg', array_keys( $formatos ), $formatos ) ) );
	$pedido->update_meta_data( '_dip_idioma', dip_idioma() );
	$pedido->update_meta_data( '_dip_canal', function_exists( 'dip_es_cuenta_empresa' ) && dip_es_cuenta_empresa( $pedido->get_customer_id() ) ? 'cuenta' : 'web' );
}

/* Pedidos que no pasan por el checkout clásico (pago exprés por Store API): también llevan su resumen. */
add_action( 'woocommerce_new_order', static function ( $pedido_id, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( ! $pedido || '' !== (string) $pedido->get_meta( '_dip_kg' ) || ( is_admin() && ! wp_doing_ajax() ) ) return;
	dip_guardar_resumen_pedido( $pedido );
	$pedido->save_meta_data();
}, 30, 2 );

/* ---------- Resumen del pedido sin los botones de envío (el envío se elige en el paso 2) ---------- */
add_filter( 'woocommerce_locate_template', static function ( $plantilla, $nombre ) {
	if ( 'checkout/review-order.php' === $nombre ) return DIP_TIENDA_DIR . 'plantillas/review-order.php';
	return $plantilla;
}, 20, 2 );

/* ---------- Pasos, botón y avisos legales ---------- */
add_action( 'woocommerce_checkout_before_customer_details', static function () {
	$t = dip_textos_tienda();
	echo '<section class="dip-paso dip-paso--contacto" aria-labelledby="dip-paso-contacto"><h2 class="dip-paso__titulo" id="dip-paso-contacto"><span class="dip-paso__n">1</span>' . esc_html( $t['paso_contacto'] ) . '</h2>';
}, 1 );
add_action( 'woocommerce_checkout_after_customer_details', static function () {
	echo '</section>';
}, 1 );

add_action( 'woocommerce_review_order_before_payment', static function () {
	$t = dip_textos_tienda();
	echo '<h2 class="dip-paso__titulo dip-paso__titulo--pago"><span class="dip-paso__n">3</span>' . esc_html( $t['paso_pago'] ) . '</h2>';
} );

add_filter( 'woocommerce_order_button_text', static function () {
	return dip_textos_tienda()['boton_confirmar'];
}, 20 );

add_action( 'woocommerce_review_order_before_submit', static function () {
	$t   = dip_textos_tienda();
	$id  = function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0;
	$url = $id ? get_permalink( $id ) : home_url( '/condiciones-de-venta/' );
	printf( '<p class="dip-perecedero">%s <a href="%s" target="_blank" rel="noopener">%s</a></p>', esc_html( $t['perecedero'] ), esc_url( $url ), esc_html( $t['condiciones'] ) );
	echo '<div class="dip-pago-rapido" data-dip-pago-rapido hidden><p class="dip-pago-rapido__titulo">' . esc_html( $t['pago_rapido'] ) . '</p><div class="dip-pago-rapido__hueco"></div></div>';
}, 5 );

/* Aviso de cuenta de empresa arriba del checkout, solo si no ha entrado. */
add_action( 'woocommerce_before_checkout_form', static function () {
	if ( is_user_logged_in() ) return;
	$t = dip_textos_tienda();
	printf(
		'<p class="dip-cuenta-aviso">%s <a href="%s">%s</a></p>',
		esc_html( $t['tienes_cuenta'] ),
		esc_url( add_query_arg( 'redirect_to', rawurlencode( wc_get_checkout_url() ), wc_get_page_permalink( 'myaccount' ) ) ),
		esc_html( $t['entrar'] )
	);
}, 4 );
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

/* ---------- JavaScript y estilos del checkout ---------- */
add_action( 'wp_enqueue_scripts', static function () {
	if ( ! is_checkout() || is_wc_endpoint_url() ) return;
	// Versión .min si existe (el paquete .zip la lleva; herramientas/empaquetar.mjs)
	$js  = file_exists( DIP_TIENDA_DIR . 'assets/checkout.min.js' ) ? 'assets/checkout.min.js' : 'assets/checkout.js';
	$css = file_exists( DIP_TIENDA_DIR . 'assets/checkout.min.css' ) ? 'assets/checkout.min.css' : 'assets/checkout.css';
	$v   = DIP_TIENDA_VERSION . '.' . filemtime( DIP_TIENDA_DIR . $js );
	wp_enqueue_script( 'dip-checkout', DIP_TIENDA_URL . $js, array( 'jquery', 'wc-checkout' ), $v, array( 'in_footer' => true ) );
	$t = dip_textos_tienda();
	wp_localize_script( 'dip-checkout', 'DIP_CHECKOUT', array(
		'limite'       => (float) dip_ajuste( 'limite_factura' ),
		'pagar'        => $t['boton_pagar'],
		'confirmar'    => $t['boton_confirmar'],
		'cataluna'     => array( '08', '17', '25', '43' ),
		'decimal'      => wc_get_price_decimal_separator(),
		'miles'        => wc_get_price_thousand_separator(),
	) );
	wp_enqueue_style( 'dip-checkout', DIP_TIENDA_URL . $css, array(), DIP_TIENDA_VERSION . '.' . filemtime( DIP_TIENDA_DIR . $css ) );
}, 30 );
