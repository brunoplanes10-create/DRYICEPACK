<?php
/**
 * Día de entrega (o de recogida) en el checkout, sin plugins de calendario.
 * - Los primeros días salen como casillas; "Otra fecha" abre un calendario propio con cualquier día válido
 *   de los próximos 90 (hay clientes que reservan con un mes de antelación).
 * - Solo se ofrecen días reales: corte de las 12:00, sin domingos ni lunes, sin festivos.
 * - Festivos de MRW: sin salida si cierra MRW Mataró; sin entrega si cierra MRW en la población del cliente
 *   (código postal y población del checkout). Si se salta un día por eso, se avisa debajo de las fechas.
 * - El sábado suma 9,70 € + IVA.
 * - La fecha también se guarda en pedidos hechos con Apple Pay / Google Pay (sale de la sesión).
 * - Se ve en el pedido, en los emails, en la página de gracias, en Mi cuenta y en la lista de pedidos.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Días que se ven como casillas; el resto (hasta DIP_DIAS_RESERVA días vista) se elige en el calendario "Otra fecha".
if ( ! defined( 'DIP_FECHAS_CASILLAS' ) ) define( 'DIP_FECHAS_CASILLAS', 6 );

function dip_metodo_elegido() {
	return dip_eligio_recogida() ? 'recogida' : 'envio';
}

/** Destino a partir de los datos enviados en el checkout clásico (shipping_* o billing_*); si no hay CP, el de la sesión. */
function dip_destino_de_datos( $datos ) {
	$datos   = (array) $datos;
	$destino = array(
		'cp'        => (string) ( ( $datos['shipping_postcode'] ?? '' ) ?: ( $datos['billing_postcode'] ?? '' ) ),
		'poblacion' => (string) ( ( $datos['shipping_city'] ?? '' ) ?: ( $datos['billing_city'] ?? '' ) ),
	);
	return '' === preg_replace( '/\D/', '', $destino['cp'] ) && function_exists( 'dip_destino_actual' ) ? dip_destino_actual() : $destino;
}

/** Destino de un pedido (dirección de envío o, si falta, de facturación); si no tiene CP, el de la sesión. */
function dip_destino_de_pedido( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return function_exists( 'dip_destino_actual' ) ? dip_destino_actual() : null;
	return dip_destino_de_datos( array(
		'shipping_postcode' => $pedido->get_shipping_postcode(),
		'billing_postcode'  => $pedido->get_billing_postcode(),
		'shipping_city'     => $pedido->get_shipping_city(),
		'billing_city'      => $pedido->get_billing_city(),
	) );
}

/**
 * Fecha elegida en esta sesión (validada para el método y el destino) o la primera disponible.
 * $destino null = el del checkout en curso (dip_destino_actual).
 */
function dip_fecha_elegida( $metodo = null, $destino = null ) {
	$metodo  = $metodo ?: dip_metodo_elegido();
	$destino = null === $destino && function_exists( 'dip_destino_actual' ) ? dip_destino_actual() : $destino;
	$fecha   = WC()->session ? (string) WC()->session->get( 'dip_fecha' ) : '';
	if ( $fecha && dip_fecha_es_valida( $fecha, $metodo, $destino ) ) return $fecha;
	foreach ( dip_fechas_disponibles( $metodo, 8, null, $destino ) as $f ) {
		if ( ! $f['sabado'] ) return $f['fecha'];
	}
	return '';
}

/**
 * Mensaje de error de una fecha no válida: festivo de MRW en el destino, fecha pasada o anterior a la primera
 * disponible (corte de las 12:00), o un día lejano sin reparto o fuera de los 90 días.
 */
function dip_error_fecha( $fecha, $metodo, $destino ) {
	$t = dip_textos_tienda();
	if ( 'envio' === $metodo && dip_fecha_es_valida( $fecha, $metodo ) && ! dip_fecha_es_valida( $fecha, $metodo, $destino ) ) return $t['fecha_festivo_destino'];
	$primera = dip_fechas_disponibles( $metodo, 1, null, $destino );
	return ( ! $primera || (string) $fecha < $primera[0]['fecha'] ) ? $t['fecha_no_valida'] : $t['fecha_no_disponible'];
}

/**
 * "Sin reparto el viernes 9 de octubre en Valencia: festivo." para los días que no se ofrecen
 * porque MRW cierra en el destino. '' si no se ha saltado ninguno.
 */
function dip_nota_festivos_destino( array $saltadas, $destino, $idioma = null ) {
	$saltadas = array_values( array_unique( array_filter( array_map( 'strval', $saltadas ) ) ) );
	if ( ! $saltadas ) return '';
	sort( $saltadas );
	$idioma = $idioma ?: dip_idioma();
	$t      = dip_textos_tienda( $idioma );
	$lugar  = trim( (string) ( $destino['poblacion'] ?? '' ) );
	if ( '' === $lugar ) {
		// Sin población: la provincia (nombre de WooCommerce si está; si no, el de MRW)
		$cp    = (string) ( $destino['cp'] ?? '' );
		$wc    = function_exists( 'WC' ) && WC() && WC()->countries ? (array) WC()->countries->get_states( 'ES' ) : array();
		$lugar = (string) ( $wc[ dip_provincia_por_cp( $cp ) ] ?? '' );
		if ( '' === $lugar && function_exists( 'dip_mrw_provincia_por_cp' ) ) $lugar = ucwords( strtolower( dip_mrw_provincia_por_cp( $cp ) ) );
	}
	$fechas = array_map( static fn( $f ) => dip_fecha_larga( $f, $idioma ), $saltadas );
	$ultima = array_pop( $fechas );
	$lista  = $fechas ? implode( $t['mrw_coma'], $fechas ) . $t['mrw_y'] . $ultima : $ultima;
	return sprintf( count( $saltadas ) > 1 ? $t['mrw_festivos'] : $t['mrw_festivo'], $lista, $lugar );
}

/* ---------- Guardar en la sesión lo que el cliente va eligiendo ---------- */
add_action( 'woocommerce_checkout_update_order_review', static function ( $post_data ) {
	parse_str( (string) $post_data, $datos );
	if ( isset( $datos['dip_fecha'] ) && WC()->session ) {
		$fecha = sanitize_text_field( $datos['dip_fecha'] );
		WC()->session->set( 'dip_fecha', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) ? $fecha : '' );
	}
} );

/* ---------- Suplemento de sábado ---------- */
add_action( 'woocommerce_cart_calculate_fees', static function ( $carrito ) {
	if ( is_admin() && ! wp_doing_ajax() ) return;
	if ( ! is_checkout() && ! wp_doing_ajax() ) return;
	// Entrega en sábado y también recogida en sábado
	$metodo = dip_metodo_elegido();
	$fecha  = dip_fecha_elegida( $metodo );
	if ( ! $fecha ) return;
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $fecha, dip_zona_horaria() );
	if ( ! $d || 6 !== (int) $d->format( 'N' ) ) return;
	$t = dip_textos_tienda();
	$carrito->add_fee( 'recogida' === $metodo ? $t['suplemento_sabado_recogida'] : $t['suplemento_sabado'], (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX, true );
}, 20 );

/* ---------- Bloque "Entrega": método + día. Se repinta con cada actualización del checkout. ---------- */
function dip_html_entrega() {
	$t        = dip_textos_tienda();
	$idioma   = dip_idioma();
	$paquetes = WC()->shipping() ? WC()->shipping()->get_packages() : array();
	$rates    = $paquetes[0]['rates'] ?? array();
	$elegidos = (array) ( WC()->session ? WC()->session->get( 'chosen_shipping_methods' ) : array() );
	$elegido  = $elegidos[0] ?? '';
	if ( $rates && ! isset( $rates[ $elegido ] ) ) $elegido = array_key_first( $rates );
	$metodo   = dip_es_recogida( $elegido ) ? 'recogida' : 'envio';
	$destino  = dip_destino_actual();
	$fecha    = dip_fecha_elegida( $metodo, $destino );
	$iva      = dip_factor_iva();

	ob_start();
	echo '<div class="dip-entrega">';
	if ( ! $rates ) {
		echo '<div class="dip-entrega__vacio">';
		wc_cart_totals_shipping_html(); // mensaje de WooCommerce o el nuestro (fuera de la península, más de 250 kg)
		echo '</div></div>';
		return ob_get_clean();
	}

	echo '<fieldset class="dip-metodos"><legend class="dip-leyenda">' . esc_html( $t['como_recibes'] ) . '</legend>';
	foreach ( $rates as $id => $rate ) {
		$es_recogida = dip_es_recogida( $rate->get_method_id() );
		$coste       = (float) $rate->get_cost() + array_sum( (array) $rate->get_taxes() );
		printf(
			'<label class="dip-metodo%1$s"><input type="radio" name="shipping_method[0]" data-index="0" value="%2$s" class="shipping_method"%3$s><span class="dip-metodo__icono" aria-hidden="true">%4$s</span><span class="dip-metodo__texto"><strong>%5$s</strong>%6$s</span><span class="dip-metodo__precio">%7$s</span></label>',
			$es_recogida ? ' dip-metodo--recogida' : '',
			esc_attr( $id ),
			checked( $id, $elegido, false ),
			$es_recogida ? dip_svg_nave() : dip_svg_furgoneta(),
			esc_html( $es_recogida ? $t['recogida'] : $t['envio'] ),
			$es_recogida ? '<small>' . esc_html( dip_contacto()['direccion'] . ' · ' . dip_contacto()['localidad'] . ' · ' . $t['recogida_horario'] ) . '</small>' : '',
			$coste > 0 ? wp_kses_post( wc_price( $coste ) ) : esc_html( 'ca' === $idioma ? 'Gratuït' : ( 'en' === $idioma ? 'Free' : 'Gratis' ) )
		);
	}
	echo '</fieldset>';
	// Más de 150 kg fuera de la provincia de Barcelona: el envío se prepara a medida (solo queda la recogida)
	$kg = dip_carrito_kg()['kg'];
	if ( dip_envio_a_medida( $kg, $destino['cp'] ) ) echo dip_html_envio_a_medida(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función
	if ( 'recogida' === $metodo ) {
		echo '<p class="dip-aviso dip-aviso--lejos" data-dip-aviso-lejos hidden>' . esc_html( $t['recogida_lejos'] ) . '</p>';
	}

	// Días: los primeros como casillas (los más pedidos) y "Otra fecha", que abre un calendario con cualquier día
	// válido de los próximos DIP_DIAS_RESERVA (assets/checkout.js). La lista completa va en data-dip-cal: el
	// calendario no hace peticiones. Todo sale del mismo cálculo (dip_calendario_entrega), hecho una vez.
	$saltadas = array();
	$fechas   = dip_fechas_disponibles( $metodo, DIP_FECHAS_CASILLAS, null, $destino, $saltadas );
	$cal      = dip_calendario_entrega( $metodo, null, $destino );
	if ( $fecha && ! in_array( $fecha, array_column( $fechas, 'fecha' ), true ) && isset( $cal['indice'][ $fecha ] ) ) {
		// Día elegido en el calendario: se ve como una casilla más, marcada, en el sitio de la última
		$fechas   = array_slice( $fechas, 0, DIP_FECHAS_CASILLAS - 1 );
		$fechas[] = $cal['fechas'][ $cal['indice'][ $fecha ] ] + array( 'lejana' => true );
	}
	$sab    = html_entity_decode( wp_strip_all_tags( wc_price( DIP_SATURDAY_SURCHARGE_EXCL_TAX * $iva ) ), ENT_QUOTES, 'UTF-8' );
	$nombre = dip_nombres_fecha( $idioma );
	$datos  = array(
		'f'   => array_keys( $cal['indice'] ),                    // días que se pueden elegir
		'x'   => 'envio' === $metodo ? $cal['saltadas'] : array(), // días que MRW no reparte en el destino
		'sel' => $fecha,
		'des' => $cal['desde'],
		'has' => $cal['hasta'],
		'sab' => '+' . $sab,
		'c'   => (int) DIP_FECHAS_CASILLAS,
		'n'   => array( 'd' => $nombre['dias'], 'dc' => $nombre['dc'], 'm' => $nombre['meses'], 'mc' => $nombre['mc'], 'mde' => $nombre['mde'] ),
		't'   => array(
			'cal' => 'recogida' === $metodo ? $t['cal_recogida'] : $t['cal_envio'],
			'ant' => $t['cal_anterior'],
			'sig' => $t['cal_siguiente'],
			'no'  => $t['cal_no_disponible'],
			'mrw' => $t['cal_festivo_mrw'],
			'ley' => sprintf( 'recogida' === $metodo ? $t['cal_leyenda_recogida'] : $t['cal_leyenda_envio'], (int) DIP_DIAS_RESERVA ),
			'ls'  => sprintf( 'recogida' === $metodo ? $t['cal_sabado_recogida'] : $t['cal_sabado_envio'], $sab ),
		),
	);
	printf(
		'<fieldset class="dip-fechas" data-dip-cal="%1$s"><legend class="dip-leyenda">%2$s</legend><div class="dip-fechas__lista">',
		esc_attr( wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ),
		esc_html( 'recogida' === $metodo ? $t['que_dia_recogida'] : $t['que_dia'] )
	);
	foreach ( $fechas as $f ) {
		$corta = dip_fecha_corta( $f['fecha'], $idioma );
		$extra = $f['sabado'] ? '+' . $sab : '';
		printf(
			'<label class="dip-fecha%1$s"><input type="radio" name="dip_fecha" value="%2$s"%3$s><span class="dip-fecha__dia" aria-hidden="true">%4$s</span><span class="dip-fecha__num" aria-hidden="true">%5$s</span><span class="dip-fecha__mes" aria-hidden="true">%6$s</span>%7$s<span class="screen-reader-text">%8$s</span></label>',
			( $f['sabado'] ? ' dip-fecha--sabado' : '' ) . ( empty( $f['lejana'] ) ? '' : ' dip-fecha--lejana' ),
			esc_attr( $f['fecha'] ),
			checked( $f['fecha'], $fecha, false ),
			esc_html( $corta[0] ),
			esc_html( $corta[1] ),
			esc_html( $corta[2] ),
			$extra ? '<span class="dip-fecha__extra">' . esc_html( $extra ) . '</span>' : '',
			esc_html( dip_fecha_larga( $f['fecha'], $idioma ) . ( $extra ? ' (' . $extra . ')' : '' ) )
		);
	}
	printf(
		'<button type="button" class="dip-otra-fecha" aria-expanded="false" aria-controls="dip-cal" aria-label="%1$s"><span class="dip-fecha__icono" aria-hidden="true">%2$s</span><span class="dip-fecha__otra" aria-hidden="true">%3$s</span></button>',
		esc_attr( $t['otra_fecha_aria'] ),
		dip_svg_calendario(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo
		esc_html( $t['otra_fecha'] )
	);
	echo '</div><div class="dip-cal" id="dip-cal" hidden></div>';
	$hay_sabado = (bool) array_filter( $fechas, static fn( $f ) => $f['sabado'] );
	echo '<p class="dip-fechas__nota">';
	if ( 'recogida' === $metodo ) {
		echo esc_html( $t['recogida_confirmar'] );
	} else {
		echo esc_html( $t['corte'] );
		if ( $hay_sabado ) echo ' ' . esc_html( $t['sabado_nota'] );
		$nota_mrw = dip_nota_festivos_destino( $saltadas, $destino, $idioma );
		if ( $nota_mrw ) echo ' ' . esc_html( $nota_mrw );
	}
	echo '</p></fieldset></div>';
	return ob_get_clean();
}

/** Aviso con WhatsApp, teléfono y email para pedidos de más de 150 kg fuera de la provincia de Barcelona. */
function dip_html_envio_a_medida() {
	$t = dip_textos_tienda();
	$c = dip_contacto();
	$w = 'https://wa.me/' . $c['whatsapp_num'] . '?text=' . rawurlencode( $t['a_medida_whatsapp'] );
	return '<div class="dip-aviso dip-aviso--medida"><p><strong>' . esc_html( $t['a_medida_titulo'] ) . '</strong> ' . esc_html( $t['a_medida_texto'] ) . '</p><p>'
		. '<a href="' . esc_url( $w ) . '" target="_blank" rel="noopener">WhatsApp ' . esc_html( $c['whatsapp'] ) . '</a> · '
		. '<a href="' . esc_url( $c['telefono_href'] ) . '">' . esc_html( $c['telefono'] ) . '</a> · '
		. '<a href="mailto:' . esc_attr( $c['email'] ) . '">' . esc_html( $c['email'] ) . '</a></p></div>';
}

function dip_svg_furgoneta() {
	return '<svg viewBox="0 0 32 32" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M3 9h16v13H3zM19 13h6l4 5v4H19z"/><circle cx="9" cy="23.5" r="2.5" fill="var(--dip-fondo,#fff)"/><circle cx="24" cy="23.5" r="2.5" fill="var(--dip-fondo,#fff)"/></svg>';
}
function dip_svg_nave() {
	return '<svg viewBox="0 0 32 32" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M4 27V13l12-7 12 7v14z"/><path d="M11 27v-8h10v8M11 23h10"/></svg>';
}
/** Calendario de línea (mismo trazo que la furgoneta y la nave), con el día elegido marcado. */
function dip_svg_calendario() {
	return '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" focusable="false"><path d="M5 8h22v19H5zM5 13.5h22M11 5v5M21 5v5"/><path d="M9.5 18h2M15 18h2M20.5 18h2M9.5 22.5h2M15 22.5h2"/><rect x="19.5" y="20.5" width="4" height="4" fill="currentColor" stroke="none"/></svg>';
}

add_action( 'woocommerce_checkout_after_customer_details', static function () {
	$t = dip_textos_tienda();
	echo '<section class="dip-paso dip-paso--entrega" aria-labelledby="dip-paso-entrega"><h2 class="dip-paso__titulo" id="dip-paso-entrega"><span class="dip-paso__n">2</span>' . esc_html( $t['paso_entrega'] ) . '</h2>';
	echo dip_html_entrega(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML construido y escapado arriba
	echo '</section>';
} );

add_filter( 'woocommerce_update_order_review_fragments', static function ( $fragmentos ) {
	$fragmentos['.dip-entrega'] = dip_html_entrega();
	return $fragmentos;
} );

/* ---------- Validar al pagar (el corte puede haber pasado mientras rellenaba) ---------- */
add_action( 'woocommerce_after_checkout_validation', static function ( $datos, $errores ) {
	$t      = dip_textos_tienda();
	$metodo = dip_es_recogida( (string) ( $datos['shipping_method'][0] ?? '' ) ) ? 'recogida' : dip_metodo_elegido();
	$fecha  = isset( $_POST['dip_fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['dip_fecha'] ) ) : (string) ( WC()->session ? WC()->session->get( 'dip_fecha' ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce ya verificó el nonce del checkout
	if ( '' === $fecha ) {
		$errores->add( 'dip_fecha', $t['fecha_obligatoria'] );
		return;
	}
	$destino = dip_destino_de_datos( $datos );
	if ( ! dip_fecha_es_valida( $fecha, $metodo, $destino ) ) {
		$errores->add( 'dip_fecha', dip_error_fecha( $fecha, $metodo, $destino ) );
	}
}, 20, 2 );

/* Checkout por Store API (algunos botones de pago exprés): misma comprobación, con la dirección del pedido. */
add_action( 'woocommerce_store_api_checkout_update_order_from_request', static function ( $pedido ) {
	$metodo  = dip_metodo_elegido();
	$destino = dip_destino_de_pedido( $pedido );
	$fecha   = WC()->session ? (string) WC()->session->get( 'dip_fecha' ) : '';
	if ( ! $fecha ) $fecha = dip_fecha_elegida( $metodo, $destino );
	if ( ! $fecha || ! dip_fecha_es_valida( $fecha, $metodo, $destino ) ) {
		if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			$mensaje = $fecha ? dip_error_fecha( $fecha, $metodo, $destino ) : dip_textos_tienda()['fecha_no_valida'];
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'dip_fecha', esc_html( $mensaje ), 400 );
		}
		return;
	}
	dip_guardar_entrega( $pedido, $fecha, $metodo );
} );

/* ---------- Guardar en el pedido ---------- */
function dip_guardar_entrega( WC_Order $pedido, $fecha, $metodo ) {
	$pedido->update_meta_data( '_dip_fecha_entrega', $fecha );
	$pedido->update_meta_data( '_dip_metodo_entrega', $metodo );
	// Clave que usaba Order Delivery Date Lite: así la fecha se sigue viendo si el plugin sigue instalado.
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $fecha, dip_zona_horaria() );
	if ( $d ) $pedido->update_meta_data( '_orddd_lite_timestamp', (string) $d->getTimestamp() );
}

add_action( 'woocommerce_checkout_create_order', static function ( $pedido, $datos ) {
	$metodo  = dip_es_recogida( (string) ( $datos['shipping_method'][0] ?? '' ) ) ? 'recogida' : dip_metodo_elegido();
	$fecha   = isset( $_POST['dip_fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['dip_fecha'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$destino = dip_destino_de_datos( $datos );
	if ( ! $fecha || ! dip_fecha_es_valida( $fecha, $metodo, $destino ) ) $fecha = dip_fecha_elegida( $metodo, $destino );
	if ( $fecha ) dip_guardar_entrega( $pedido, $fecha, $metodo );
}, 10, 2 );

/* Red de seguridad: cualquier pedido creado sin fecha (pago exprés, otras vías) la toma de la sesión. */
add_action( 'woocommerce_new_order', static function ( $pedido_id, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( ! $pedido || $pedido->get_meta( '_dip_fecha_entrega' ) || is_admin() && ! wp_doing_ajax() ) return;
	if ( ! function_exists( 'WC' ) || ! WC()->session ) return;
	$metodo = dip_metodo_elegido();
	$fecha  = dip_fecha_elegida( $metodo, dip_destino_de_pedido( $pedido ) );
	if ( ! $fecha ) return;
	dip_guardar_entrega( $pedido, $fecha, $metodo );
	$pedido->save_meta_data();
}, 20, 2 );

/* ---------- Mostrar la fecha ---------- */
function dip_texto_entrega( WC_Order $pedido, $idioma = null ) {
	$fecha = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	if ( ! $fecha ) return '';
	$idioma = $idioma ?: ( (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es' );
	$t      = dip_textos_tienda( $idioma );
	$tipo   = 'recogida' === $pedido->get_meta( '_dip_metodo_entrega' ) ? $t['fecha_recogida'] : $t['fecha_entrega'];
	return $tipo . ': ' . dip_fecha_larga( $fecha, $idioma );
}

function dip_es_pedido_de_recogida( WC_Order $pedido ) {
	return 'recogida' === $pedido->get_meta( '_dip_metodo_entrega' );
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $pedido ) {
	$texto = dip_texto_entrega( $pedido, 'es' );
	if ( $texto ) echo '<p><strong>' . esc_html( $texto ) . '</strong></p>';
	if ( ! dip_es_pedido_de_recogida( $pedido ) ) return;
	// Recogida: la confirma la empresa. Hora para el email "Tu pedido está listo" (Acciones del pedido → Confirmar recogida).
	$confirmada = (string) $pedido->get_meta( '_dip_recogida_confirmada' );
	printf(
		'<p class="form-field form-field-wide"><label for="dip_hora_recogida">Hora de recogida (para el email al cliente)</label><input type="text" id="dip_hora_recogida" name="dip_hora_recogida" value="%s" placeholder="p. ej. 10:30 o entre 16:00 y 18:00"></p><p>%s</p>',
		esc_attr( (string) $pedido->get_meta( '_dip_hora_recogida' ) ),
		$confirmada ? esc_html( 'Recogida confirmada al cliente el ' . wp_date( 'j/n/Y H:i', (int) $confirmada ) . '.' ) : '<em>' . esc_html( 'Recogida sin confirmar: escribe la hora y elige "Confirmar recogida al cliente" en Acciones del pedido.' ) . '</em>'
	);
} );

/* Guardar la hora antes de que WooCommerce ejecute la acción del pedido (prioridad 50). */
add_action( 'woocommerce_process_shop_order_meta', static function ( $pedido_id ) {
	if ( ! isset( $_POST['dip_hora_recogida'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verificó el nonce del pedido
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido ) return;
	$pedido->update_meta_data( '_dip_hora_recogida', sanitize_text_field( wp_unslash( $_POST['dip_hora_recogida'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$pedido->save_meta_data();
}, 10 );

add_filter( 'woocommerce_order_actions', static function ( $acciones, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : ( isset( $GLOBALS['theorder'] ) && $GLOBALS['theorder'] instanceof WC_Order ? $GLOBALS['theorder'] : null );
	if ( $pedido && dip_es_pedido_de_recogida( $pedido ) ) $acciones['dip_confirmar_recogida'] = 'Confirmar recogida al cliente (email)';
	return $acciones;
}, 10, 2 );

add_action( 'woocommerce_order_action_dip_confirmar_recogida', 'dip_enviar_confirmacion_recogida' );

/** Email "Tu pedido está listo para recoger" en el idioma del pedido, con día, hora, dirección e importe en efectivo. */
function dip_enviar_confirmacion_recogida( WC_Order $pedido ) {
	$idioma = (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es';
	$t      = dip_textos_tienda( $idioma );
	$c      = dip_contacto();
	$fecha  = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	$hora   = trim( (string) $pedido->get_meta( '_dip_hora_recogida' ) );
	$cuando = ( $fecha ? dip_fecha_larga( $fecha, $idioma ) : '' ) . ( $hora ? ' · ' . $hora : '' );
	$pago   = 'cod' === $pedido->get_payment_method() ? sprintf( $t['recogida_lista_pago'], wp_strip_all_tags( wc_price( $pedido->get_total(), array( 'currency' => $pedido->get_currency() ) ) ) ) : '';
	$cuerpo = '<p>' . esc_html( sprintf( $t['recogida_lista_hola'], $pedido->get_billing_first_name() ) ) . '</p>'
		. '<p style="font-size:18px"><strong>' . esc_html( $cuando ) . '</strong></p>'
		. '<p>' . esc_html( $c['direccion'] . ', ' . $c['cp'] . ' ' . $c['localidad'] ) . '<br><a href="' . esc_url( $c['mapa'] ) . '">' . esc_html( $t['recogida_lista_mapa'] ) . '</a></p>'
		. ( $pago ? '<p>' . esc_html( $pago ) . '</p>' : '' )
		// La dirección de la página de seguridad ("dryicepack.es/…") se puede pulsar
		. '<p>' . preg_replace( '#\b(dryicepack\.es/[a-z0-9/-]+)#', '<a href="https://$1">$1</a>', esc_html( $t['recogida_lista_seguridad'] ) ) . '</p>'
		. '<p>' . esc_html( sprintf( $t['recogida_lista_cambio'], $c['telefono'], $c['whatsapp'] ) ) . '</p>';
	$mailer = WC()->mailer();
	$asunto = sprintf( $t['recogida_lista_asunto'], $pedido->get_order_number() );
	$html   = $mailer->wrap_message( $asunto, $cuerpo );
	$ok     = $mailer->send( $pedido->get_billing_email(), $asunto, $html );
	if ( $ok ) {
		$pedido->update_meta_data( '_dip_recogida_confirmada', (string) time() );
		$pedido->save_meta_data();
	}
	$pedido->add_order_note( $ok ? 'Recogida confirmada al cliente por email: ' . $cuando . '.' : 'No se pudo enviar el email de confirmación de recogida.' );
}

add_action( 'woocommerce_email_after_order_table', static function ( $pedido, $a_admin, $texto_plano ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$texto = dip_texto_entrega( $pedido, $a_admin ? 'es' : null );
	if ( ! $texto ) return;
	// Al cliente que recoge: se le avisa de que confirmamos la hora antes
	$nota = ( ! $a_admin && dip_es_pedido_de_recogida( $pedido ) ) ? dip_textos_tienda( (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es' )['recogida_confirmar'] : '';
	if ( $texto_plano ) {
		echo "\n" . esc_html( $texto ) . "\n" . ( $nota ? esc_html( $nota ) . "\n" : '' );
		return;
	}
	echo '<p style="margin:16px 0;font-size:16px"><strong>' . esc_html( $texto ) . '</strong>' . ( $nota ? '<br><span style="font-size:14px">' . esc_html( $nota ) . '</span>' : '' ) . '</p>';
}, 5, 3 );

add_action( 'woocommerce_order_details_after_order_table', static function ( $pedido ) {
	$texto = dip_texto_entrega( $pedido );
	if ( ! $texto ) return;
	echo '<p class="dip-entrega-pedido">' . esc_html( $texto );
	if ( dip_es_pedido_de_recogida( $pedido ) ) echo '<br><small>' . esc_html( dip_textos_tienda()['recogida_confirmar'] ) . '</small>';
	echo '</p>';
}, 5 );

/* Columna "Entrega" en la lista de pedidos (tablas modernas y antiguas). */
foreach ( array( 'manage_woocommerce_page_wc-orders_columns', 'manage_edit-shop_order_columns' ) as $dip_filtro ) {
	add_filter( $dip_filtro, static function ( $columnas ) {
		$nuevas = array();
		foreach ( $columnas as $clave => $nombre ) {
			$nuevas[ $clave ] = $nombre;
			if ( 'order_date' === $clave ) $nuevas['dip_entrega'] = 'Entrega';
		}
		return $nuevas;
	} );
}
function dip_columna_entrega( $columna, $pedido ) {
	if ( 'dip_entrega' !== $columna ) return;
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido );
	if ( ! $pedido ) return;
	$fecha = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	if ( ! $fecha ) {
		echo '—';
		return;
	}
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $fecha, dip_zona_horaria() );
	$recoge = dip_es_pedido_de_recogida( $pedido );
	echo esc_html( ( $recoge ? 'Recoge ' : '' ) . ( $d ? wp_date( 'D j M', $d->getTimestamp(), dip_zona_horaria() ) : $fecha ) ); // misma zona que la fecha: si WordPress tuviera otra, saldría el día anterior
	if ( $recoge ) echo '<br><small>' . esc_html( $pedido->get_meta( '_dip_recogida_confirmada' ) ? 'Confirmada' : 'Sin confirmar' ) . '</small>';
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'dip_columna_entrega', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'dip_columna_entrega', 10, 2 );
