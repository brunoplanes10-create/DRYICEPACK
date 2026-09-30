<?php
/**
 * Factura después de la compra.
 * 1. Al pagar, si el pedido no trae datos fiscales, sale un email aparte "¿Necesitas factura con tus datos?".
 * 2. El enlace lleva a /factura/?pedido=ID&clave=CLAVE_DEL_PEDIDO (la clave secreta que WooCommerce ya usa en "pedido recibido").
 * 3. El cliente deja razón social, NIF/CIF y dirección fiscal: se guardan en el pedido, se anota y se avisa a info@.
 * El mismo botón aparece en la página de gracias. La factura la emite la aplicación de la gestoría (la web no factura).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_url_factura( WC_Order $pedido ) {
	$pagina = get_page_by_path( 'factura' );
	$base   = $pagina ? get_permalink( $pagina ) : home_url( '/factura/' );
	return add_query_arg( array( 'pedido' => $pedido->get_id(), 'clave' => $pedido->get_order_key() ), $base );
}

function dip_pedido_tiene_datos_fiscales( WC_Order $pedido ) {
	return '' !== (string) $pedido->get_meta( '_billing_nif' ) || $pedido->get_meta( '_dip_factura_datos' );
}

/* Crear la página /factura/ si no existe (noindex por el tema). */
add_action( 'admin_init', static function () {
	if ( get_option( 'dip_pagina_factura_creada' ) ) return;
	if ( ! get_page_by_path( 'factura' ) ) {
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => 'factura',
			'post_title'   => 'Datos de facturación',
			'post_content' => '[dip_factura]',
		) );
	}
	update_option( 'dip_pagina_factura_creada', 1 );
} );

/* ---------- Email "¿Necesitas factura?" ---------- */
function dip_enviar_email_factura( $pedido_id ) {
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido || $pedido->get_meta( '_dip_email_factura' ) ) return;
	if ( dip_pedido_tiene_datos_fiscales( $pedido ) || $pedido->get_meta( '_dip_factura_mensual' ) ) return;
	$email = $pedido->get_billing_email();
	if ( ! is_email( $email ) ) return;

	$idioma = (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es';
	$t      = dip_textos_tienda( $idioma );
	$numero = $pedido->get_order_number();
	$url    = dip_url_factura( $pedido );
	$cuerpo = '<p>' . esc_html( sprintf( $t['email_factura_cuerpo'], $numero ) ) . '</p>'
		. '<p style="margin:24px 0"><a href="' . esc_url( $url ) . '" style="display:inline-block;padding:14px 22px;background:#1F3C55;color:#ffffff;text-decoration:none;border-radius:999px;font-weight:600">' . esc_html( $t['email_factura_boton'] ) . '</a></p>'
		. '<p style="font-size:13px;color:#56677A">' . esc_html( $url ) . '</p>';

	$mailer = WC()->mailer();
	$html   = $mailer->wrap_message( $t['email_factura_titulo'], $cuerpo );
	$mailer->send( $email, sprintf( $t['email_factura_asunto'], $numero ), $html );
	$pedido->update_meta_data( '_dip_email_factura', current_time( 'mysql' ) );
	$pedido->save_meta_data();
}
add_action( 'woocommerce_order_status_processing', 'dip_enviar_email_factura', 30 );
add_action( 'woocommerce_order_status_completed', 'dip_enviar_email_factura', 30 );

/* ---------- Página de gracias ---------- */
add_action( 'woocommerce_thankyou', static function ( $pedido_id ) {
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido || dip_pedido_tiene_datos_fiscales( $pedido ) || $pedido->get_meta( '_dip_factura_mensual' ) ) return;
	$t = dip_textos_tienda();
	printf(
		'<aside class="dip-factura-cta"><div><h2>%s</h2><p>%s</p></div><a class="button dip-boton" href="%s">%s</a></aside>',
		esc_html( $t['factura_titulo'] ),
		esc_html( $t['factura_texto'] ),
		esc_url( dip_url_factura( $pedido ) ),
		esc_html( $t['factura_boton'] )
	);
}, 5 );

/* ---------- Formulario ---------- */
add_shortcode( 'dip_factura', 'dip_shortcode_factura' );

function dip_textos_factura( $idioma ) {
	$tx = array(
		'es' => array(
			'no_valido'  => 'Este enlace no es válido o ha caducado. Escríbenos a info@dryicepack.es con tu número de pedido y te ayudamos.',
			'pedido'     => 'Pedido %1$s del %2$s · %3$s',
			'intro'      => 'Rellena los datos que deben aparecer en la factura. Te la enviamos por email.',
			'razon'      => 'Razón social o nombre completo',
			'nif'        => 'NIF / CIF',
			'direccion'  => 'Dirección fiscal',
			'cp'         => 'Código postal',
			'poblacion'  => 'Población',
			'email'      => 'Email para recibir la factura',
			'enviar'     => 'Enviar datos',
			'gracias'    => 'Datos recibidos. Te enviaremos la factura del pedido %s por email.',
			'ya'         => 'Ya tenemos estos datos. Si hay algo que corregir, cámbialo y vuelve a enviarlo.',
			'falta'      => 'Rellena todos los campos.',
			'nif_mal'    => 'Revisa el NIF/CIF: no parece correcto.',
			'privacidad' => 'Usamos estos datos solo para emitir tu factura. Responsable: INDUNOVA IMS S.L. Más información en la política de privacidad.',
		),
		'ca' => array(
			'no_valido'  => "Aquest enllaç no és vàlid o ha caducat. Escriu-nos a info@dryicepack.es amb el número de comanda i t'ajudem.",
			'pedido'     => 'Comanda %1$s del %2$s · %3$s',
			'intro'      => "Omple les dades que han de sortir a la factura. Te l'enviem per correu.",
			'razon'      => 'Raó social o nom complet',
			'nif'        => 'NIF / CIF',
			'direccion'  => 'Adreça fiscal',
			'cp'         => 'Codi postal',
			'poblacion'  => 'Població',
			'email'      => 'Correu per rebre la factura',
			'enviar'     => 'Enviar les dades',
			'gracias'    => 'Dades rebudes. T’enviarem la factura de la comanda %s per correu.',
			'ya'         => 'Ja tenim aquestes dades. Si cal corregir alguna cosa, canvia-la i torna-la a enviar.',
			'falta'      => 'Omple tots els camps.',
			'nif_mal'    => 'Revisa el NIF/CIF: no sembla correcte.',
			'privacidad' => 'Fem servir aquestes dades només per emetre la factura. Responsable: INDUNOVA IMS S.L. Més informació a la política de privacitat.',
		),
		'en' => array(
			'no_valido'  => 'This link is not valid or has expired. Write to info@dryicepack.es with your order number and we will help.',
			'pedido'     => 'Order %1$s of %2$s · %3$s',
			'intro'      => 'Enter the details that must appear on the invoice. We will email it to you.',
			'razon'      => 'Company name or full name',
			'nif'        => 'Tax ID (NIF / CIF / VAT)',
			'direccion'  => 'Billing address',
			'cp'         => 'Postcode',
			'poblacion'  => 'Town',
			'email'      => 'Email to receive the invoice',
			'enviar'     => 'Send details',
			'gracias'    => 'Details received. We will email you the invoice for order %s.',
			'ya'         => 'We already have these details. If anything needs correcting, change it and send again.',
			'falta'      => 'Please fill in every field.',
			'nif_mal'    => 'Please check the tax ID: it does not look right.',
			'privacidad' => 'We use these details only to issue your invoice. Controller: INDUNOVA IMS S.L. More in the privacy policy.',
		),
	);
	return $tx[ $idioma ] ?? $tx['es'];
}

function dip_pedido_desde_enlace() {
	$id    = isset( $_GET['pedido'] ) ? absint( $_GET['pedido'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- la clave del pedido hace de credencial
	$clave = isset( $_GET['clave'] ) ? sanitize_text_field( wp_unslash( $_GET['clave'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $id || '' === $clave ) return null;
	$pedido = wc_get_order( $id );
	if ( ! $pedido || ! hash_equals( (string) $pedido->get_order_key(), $clave ) ) return null;
	return $pedido;
}

/* Procesar el envío antes de pintar la página (patrón POST → redirección → GET). */
add_action( 'template_redirect', static function () {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['dip_factura'] ) ) return;
	$pedido = dip_pedido_desde_enlace();
	if ( ! $pedido ) return;
	if ( ! isset( $_POST['_dip_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_dip_nonce'] ) ), 'dip_factura_' . $pedido->get_id() ) ) return;
	if ( ! empty( $_POST['web'] ) ) return; // trampa para robots

	$idioma = (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es';
	$tx     = dip_textos_factura( $idioma );
	$datos  = array(
		'razon'     => sanitize_text_field( wp_unslash( $_POST['razon'] ?? '' ) ),
		'nif'       => dip_normalizar_nif( wp_unslash( $_POST['nif'] ?? '' ) ),
		'direccion' => sanitize_text_field( wp_unslash( $_POST['direccion'] ?? '' ) ),
		'cp'        => preg_replace( '/[^0-9A-Za-z -]/', '', (string) wp_unslash( $_POST['cp'] ?? '' ) ),
		'poblacion' => sanitize_text_field( wp_unslash( $_POST['poblacion'] ?? '' ) ),
		'email'     => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
	);
	$error = '';
	if ( in_array( '', $datos, true ) || ! is_email( $datos['email'] ) ) $error = $tx['falta'];
	elseif ( ! dip_nif_valido( $datos['nif'] ) ) $error = $tx['nif_mal'];

	if ( $error ) {
		set_transient( 'dip_factura_error_' . $pedido->get_id(), array( 'error' => $error, 'datos' => $datos ), 10 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'e', 1, dip_url_factura( $pedido ) ) . '#factura' );
		exit;
	}

	$datos['fecha'] = current_time( 'mysql' );
	$pedido->update_meta_data( '_dip_factura_datos', $datos );
	foreach ( array( '_billing_nif', '_billing_dni_nie', '_billing_cif', '_billing_vat' ) as $clave ) $pedido->update_meta_data( $clave, $datos['nif'] );
	$pedido->set_billing_company( $datos['razon'] );
	$pedido->add_order_note( sprintf( "Datos de factura recibidos por /factura/:\n%s\n%s\n%s, %s %s\nEnviar a: %s", $datos['razon'], $datos['nif'], $datos['direccion'], $datos['cp'], $datos['poblacion'], $datos['email'] ) );
	$pedido->save();

	$aviso = dip_ajuste( 'email_avisos' );
	$admin = admin_url( 'post.php?post=' . $pedido->get_id() . '&action=edit' );
	if ( class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$admin = admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $pedido->get_id() );
	}
	wp_mail(
		$aviso,
		sprintf( 'Factura pedida · pedido %s · %s', $pedido->get_order_number(), $datos['razon'] ),
		sprintf( "El cliente del pedido %s pide factura completa con estos datos:\n\nRazón social: %s\nNIF/CIF: %s\nDirección fiscal: %s, %s %s\nEnviar la factura a: %s\n\nTotal del pedido: %s\nPedido: %s\n", $pedido->get_order_number(), $datos['razon'], $datos['nif'], $datos['direccion'], $datos['cp'], $datos['poblacion'], $datos['email'], wp_strip_all_tags( wc_price( $pedido->get_total() ) ), $admin ),
		array( 'Reply-To: ' . $datos['email'] )
	);

	wp_safe_redirect( add_query_arg( 'ok', 1, dip_url_factura( $pedido ) ) . '#factura' );
	exit;
} );

function dip_shortcode_factura() {
	$pedido = dip_pedido_desde_enlace();
	$idioma = $pedido ? ( (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es' ) : dip_idioma();
	$tx     = dip_textos_factura( $idioma );
	if ( ! $pedido ) return '<div class="dip-factura dip-factura--error" id="factura"><p>' . esc_html( $tx['no_valido'] ) . '</p></div>';

	$guardado = (array) $pedido->get_meta( '_dip_factura_datos' );
	$previo   = get_transient( 'dip_factura_error_' . $pedido->get_id() );
	$valores  = array(
		'razon'     => $guardado['razon'] ?? ( $pedido->get_billing_company() ?: $pedido->get_formatted_billing_full_name() ),
		'nif'       => $guardado['nif'] ?? (string) $pedido->get_meta( '_billing_nif' ),
		'direccion' => $guardado['direccion'] ?? trim( $pedido->get_billing_address_1() . ' ' . $pedido->get_billing_address_2() ),
		'cp'        => $guardado['cp'] ?? $pedido->get_billing_postcode(),
		'poblacion' => $guardado['poblacion'] ?? $pedido->get_billing_city(),
		'email'     => $guardado['email'] ?? $pedido->get_billing_email(),
	);
	if ( is_array( $previo ) && isset( $_GET['e'] ) ) $valores = array_merge( $valores, $previo['datos'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$fecha = $pedido->get_date_created() ? wp_date( 'd/m/Y', $pedido->get_date_created()->getTimestamp() ) : '';
	ob_start();
	?>
	<div class="dip-factura" id="factura">
		<p class="dip-factura__pedido"><?php echo esc_html( sprintf( $tx['pedido'], $pedido->get_order_number(), $fecha, wp_strip_all_tags( wc_price( $pedido->get_total() ) ) ) ); ?></p>
		<?php if ( isset( $_GET['ok'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<p class="dip-factura__ok" role="status"><?php echo esc_html( sprintf( $tx['gracias'], $pedido->get_order_number() ) ); ?></p>
		<?php elseif ( is_array( $previo ) && isset( $_GET['e'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<p class="dip-factura__error" role="alert"><?php echo esc_html( $previo['error'] ); ?></p>
		<?php elseif ( $guardado ) : ?>
			<p class="dip-factura__nota"><?php echo esc_html( $tx['ya'] ); ?></p>
		<?php else : ?>
			<p class="dip-factura__nota"><?php echo esc_html( $tx['intro'] ); ?></p>
		<?php endif; ?>
		<form method="post" class="dip-factura__form" novalidate>
			<?php wp_nonce_field( 'dip_factura_' . $pedido->get_id(), '_dip_nonce' ); ?>
			<input type="hidden" name="dip_factura" value="1">
			<p class="dip-trampa" aria-hidden="true"><label>Web <input type="text" name="web" tabindex="-1" autocomplete="off"></label></p>
			<?php
			$campos = array(
				'razon'     => array( $tx['razon'], 'organization', 'text', 'ancho' ),
				'nif'       => array( $tx['nif'], 'off', 'text', 'medio' ),
				'direccion' => array( $tx['direccion'], 'street-address', 'text', 'ancho' ),
				'cp'        => array( $tx['cp'], 'postal-code', 'text', 'tercio' ),
				'poblacion' => array( $tx['poblacion'], 'address-level2', 'text', 'dos-tercios' ),
				'email'     => array( $tx['email'], 'email', 'email', 'ancho' ),
			);
			foreach ( $campos as $nombre => $c ) {
				printf(
					'<p class="dip-factura__campo dip-factura__campo--%5$s"><label for="dip-f-%1$s">%2$s</label><input id="dip-f-%1$s" name="%1$s" type="%4$s" value="%6$s" autocomplete="%3$s" required></p>',
					esc_attr( $nombre ),
					esc_html( $c[0] ),
					esc_attr( $c[1] ),
					esc_attr( $c[2] ),
					esc_attr( $c[3] ),
					esc_attr( (string) $valores[ $nombre ] )
				);
			}
			?>
			<p class="dip-factura__privacidad"><?php echo esc_html( $tx['privacidad'] ); ?></p>
			<button type="submit" class="dip-boton"><?php echo esc_html( $tx['enviar'] ); ?></button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/* La página de factura no se indexa ni se cachea (lleva datos del pedido). */
add_filter( 'wp_robots', static function ( $robots ) {
	if ( is_page( 'factura' ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );
add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	if ( is_page( 'factura' ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'nofollow';
	}
	return $robots;
} );
add_action( 'template_redirect', static function () {
	if ( is_page( 'factura' ) ) {
		nocache_headers();
		if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // WP Rocket
	}
}, 0 );

/* ---------- Petición de reseña en el email de pedido completado ---------- */
add_action( 'woocommerce_email_after_order_table', static function ( $pedido, $a_admin, $texto_plano, $email ) {
	if ( $a_admin || ! $email || 'customer_completed_order' !== $email->id ) return;
	$url = (string) dip_ajuste( 'resenas_url' );
	if ( ! $url ) return;
	$t = dip_textos_tienda( (string) $pedido->get_meta( '_dip_idioma' ) ?: 'es' );
	if ( $texto_plano ) {
		echo "\n" . esc_html( $t['resena'] ) . ' ' . esc_url( $url ) . "\n";
		return;
	}
	printf( '<p style="margin:24px 0 8px">%s</p><p><a href="%s">%s</a></p>', esc_html( $t['resena'] ), esc_url( $url ), esc_html( $t['resena_boton'] ) );
}, 20, 4 );
