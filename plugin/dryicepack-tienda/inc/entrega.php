<?php
/**
 * Día de entrega (o de recogida) en el checkout, sin plugins de calendario.
 * - Solo se ofrecen días reales: corte de las 12:00, sin domingos ni lunes, sin festivos.
 * - El sábado suma 9,70 € + IVA.
 * - La fecha también se guarda en pedidos hechos con Apple Pay / Google Pay (sale de la sesión).
 * - Se ve en el pedido, en los emails, en la página de gracias, en Mi cuenta y en la lista de pedidos.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_metodo_elegido() {
	return dip_eligio_recogida() ? 'recogida' : 'envio';
}

/** Fecha elegida en esta sesión (validada para el método actual) o la primera disponible. */
function dip_fecha_elegida( $metodo = null ) {
	$metodo = $metodo ?: dip_metodo_elegido();
	$fecha  = WC()->session ? (string) WC()->session->get( 'dip_fecha' ) : '';
	if ( $fecha && dip_fecha_es_valida( $fecha, $metodo ) ) return $fecha;
	foreach ( dip_fechas_disponibles( $metodo, 8 ) as $f ) {
		if ( ! $f['sabado'] ) return $f['fecha'];
	}
	return '';
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
	$fecha    = dip_fecha_elegida( $metodo );
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
	$cp = WC()->customer ? ( WC()->customer->get_shipping_postcode() ?: WC()->customer->get_billing_postcode() ) : '';
	if ( dip_envio_a_medida( $kg, $cp ) ) echo dip_html_envio_a_medida(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en la función
	if ( 'recogida' === $metodo ) {
		echo '<p class="dip-aviso dip-aviso--lejos" data-dip-aviso-lejos hidden>' . esc_html( $t['recogida_lejos'] ) . '</p>';
	}

	$fechas = dip_fechas_disponibles( $metodo, 6 );
	echo '<fieldset class="dip-fechas"><legend class="dip-leyenda">' . esc_html( 'recogida' === $metodo ? $t['que_dia_recogida'] : $t['que_dia'] ) . '</legend><div class="dip-fechas__lista">';
	foreach ( $fechas as $f ) {
		$corta = dip_fecha_corta( $f['fecha'], $idioma );
		$extra = $f['sabado'] ? '+' . wp_strip_all_tags( wc_price( DIP_SATURDAY_SURCHARGE_EXCL_TAX * $iva ) ) : '';
		printf(
			'<label class="dip-fecha%1$s"><input type="radio" name="dip_fecha" value="%2$s"%3$s><span class="dip-fecha__dia" aria-hidden="true">%4$s</span><span class="dip-fecha__num" aria-hidden="true">%5$s</span><span class="dip-fecha__mes" aria-hidden="true">%6$s</span>%7$s<span class="screen-reader-text">%8$s</span></label>',
			$f['sabado'] ? ' dip-fecha--sabado' : '',
			esc_attr( $f['fecha'] ),
			checked( $f['fecha'], $fecha, false ),
			esc_html( $corta[0] ),
			esc_html( $corta[1] ),
			esc_html( $corta[2] ),
			$extra ? '<span class="dip-fecha__extra">' . esc_html( $extra ) . '</span>' : '',
			esc_html( dip_fecha_larga( $f['fecha'], $idioma ) . ( $extra ? ' (' . $extra . ')' : '' ) )
		);
	}
	echo '</div>';
	$hay_sabado = (bool) array_filter( $fechas, static fn( $f ) => $f['sabado'] );
	echo '<p class="dip-fechas__nota">';
	if ( 'recogida' === $metodo ) {
		echo esc_html( $t['recogida_confirmar'] );
	} else {
		echo esc_html( $t['corte'] );
		if ( $hay_sabado ) echo ' ' . esc_html( $t['sabado_nota'] );
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
	if ( ! dip_fecha_es_valida( $fecha, $metodo ) ) {
		$errores->add( 'dip_fecha', $t['fecha_no_valida'] );
	}
}, 20, 2 );

/* Checkout por Store API (algunos botones de pago exprés): misma comprobación. */
add_action( 'woocommerce_store_api_checkout_update_order_from_request', static function ( $pedido ) {
	$metodo = dip_metodo_elegido();
	$fecha  = WC()->session ? (string) WC()->session->get( 'dip_fecha' ) : '';
	if ( ! $fecha ) $fecha = dip_fecha_elegida( $metodo );
	if ( ! $fecha || ! dip_fecha_es_valida( $fecha, $metodo ) ) {
		if ( class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'dip_fecha', esc_html( dip_textos_tienda()['fecha_no_valida'] ), 400 );
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
	$metodo = dip_es_recogida( (string) ( $datos['shipping_method'][0] ?? '' ) ) ? 'recogida' : dip_metodo_elegido();
	$fecha  = isset( $_POST['dip_fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['dip_fecha'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! $fecha || ! dip_fecha_es_valida( $fecha, $metodo ) ) $fecha = dip_fecha_elegida( $metodo );
	if ( $fecha ) dip_guardar_entrega( $pedido, $fecha, $metodo );
}, 10, 2 );

/* Red de seguridad: cualquier pedido creado sin fecha (pago exprés, otras vías) la toma de la sesión. */
add_action( 'woocommerce_new_order', static function ( $pedido_id, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( ! $pedido || $pedido->get_meta( '_dip_fecha_entrega' ) || is_admin() && ! wp_doing_ajax() ) return;
	if ( ! function_exists( 'WC' ) || ! WC()->session ) return;
	$metodo = dip_metodo_elegido();
	$fecha  = dip_fecha_elegida( $metodo );
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
		. '<p>' . esc_html( $t['recogida_lista_seguridad'] ) . '</p>'
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
	echo esc_html( ( $recoge ? 'Recoge ' : '' ) . ( $d ? wp_date( 'D j M', $d->getTimestamp() ) : $fecha ) );
	if ( $recoge ) echo '<br><small>' . esc_html( $pedido->get_meta( '_dip_recogida_confirmada' ) ? 'Confirmada' : 'Sin confirmar' ) . '</small>';
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'dip_columna_entrega', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'dip_columna_entrega', 10, 2 );
