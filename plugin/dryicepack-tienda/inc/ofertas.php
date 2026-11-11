<?php
/**
 * Ofertas por email a clientes (11/11/2026): la casilla «No quiero recibir avisos ni ofertas de hielo seco por email».
 *
 * - Base legal: art. 21.2 de la LSSI. A quien ha comprado se le pueden enviar emails sobre productos parecidos (hielo seco)
 *   si al dar sus datos pudo decir que no de forma sencilla y gratuita, y si cada email lleva cómo darse de baja.
 *   Por eso la casilla va sin marcar, justo debajo del email del checkout, y solo cuentan los pedidos desde que existe.
 * - Nunca se condiciona la compra ni el seguimiento a aceptar publicidad (art. 6.3 de la LOPDGDD, art. 7.4 del RGPD).
 * - Cada pedido guarda _dip_publicidad ('si' o 'no'). Quien dice que no (en la casilla o con el enlace de baja) entra en
 *   una lista de bajas por huella del email (no se puede leer el email a partir de ella) y no vuelve a salir en la lista,
 *   aunque compre otra vez sin marcarla. Con la sesión iniciada y de baja, la casilla sale ya marcada.
 * - Dryicepack → Clientes para ofertas: la lista y el CSV (con el enlace de baja de cada uno) para la herramienta de envíos.
 * - Página de baja: ?dip_ofertas_baja=HUELLA&firma=…, como la de las reseñas (inc/resenas.php): GET enseña un botón, POST
 *   da de baja (también el «Cancelar suscripción» de Gmail y Apple Mail, RFC 8058).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* =========================================================
 * Huella, firma y lista de bajas
 * ========================================================= */

function dip_ofertas_huella( $email ) {
	return wp_hash( 'ofertas|' . strtolower( trim( (string) $email ) ), 'dip_ofertas' );
}

/** Firma del enlace de baja: solo la web puede crear enlaces válidos. */
function dip_ofertas_firma( $huella ) {
	return substr( wp_hash( 'ofertas-baja|' . $huella, 'dip_ofertas' ), 0, 16 );
}

/** [ huella => 'AAAA-MM-DD' de la baja ] */
function dip_ofertas_bajas() {
	$bajas = get_option( 'dip_ofertas_bajas', array() );
	return is_array( $bajas ) ? $bajas : array();
}

function dip_ofertas_de_baja( $email ) {
	return '' !== trim( (string) $email ) && isset( dip_ofertas_bajas()[ dip_ofertas_huella( $email ) ] );
}

function dip_ofertas_dar_de_baja( $huella ) {
	$bajas = dip_ofertas_bajas();
	if ( isset( $bajas[ $huella ] ) ) return;
	$bajas[ $huella ] = current_time( 'Y-m-d' );
	update_option( 'dip_ofertas_bajas', $bajas, false );
}

function dip_ofertas_url_baja( $email, $idioma = 'es', $usted = false ) {
	$huella = dip_ofertas_huella( $email );
	$args   = array( 'dip_ofertas_baja' => $huella, 'firma' => dip_ofertas_firma( $huella ) );
	if ( in_array( $idioma, array( 'ca', 'en' ), true ) ) $args['idioma'] = $idioma;
	if ( $usted ) $args['usted'] = 1;
	return add_query_arg( $args, home_url( '/' ) );
}

/* =========================================================
 * Checkout: la casilla, debajo del email
 * ========================================================= */

add_filter( 'woocommerce_checkout_fields', static function ( $campos ) {
	$t     = dip_textos_tienda();
	$email = is_user_logged_in() ? (string) wp_get_current_user()->user_email : '';
	$campos['billing']['dip_sin_ofertas'] = array(
		'type'     => 'checkbox',
		'label'    => $t['sin_ofertas'],
		'required' => false,
		'priority' => 15, // entre el email (10) y el teléfono (20)
		'class'    => array( 'form-row-wide', 'dip-casilla-ofertas' ),
		'default'  => $email && dip_ofertas_de_baja( $email ) ? 1 : 0,
	);
	return $campos;
}, 2010 );

/* Cada pedido guarda lo que eligió; si dice que no, va a la lista de bajas */
add_action( 'woocommerce_checkout_create_order', static function ( $pedido, $datos ) {
	$no = ! empty( $datos['dip_sin_ofertas'] );
	$pedido->update_meta_data( '_dip_publicidad', $no ? 'no' : 'si' );
	if ( $no ) dip_ofertas_dar_de_baja( dip_ofertas_huella( $pedido->get_billing_email() ) );
	add_option( 'dip_ofertas_desde', current_time( 'Y-m-d' ), '', false ); // primer pedido con la casilla (no cambia después)
}, 10, 2 );

/* =========================================================
 * La lista: Dryicepack → Clientes para ofertas
 * ========================================================= */

/**
 * Clientes a los que se puede escribir: pedidos Procesando o Completados con _dip_publicidad = 'si', sin los que están de
 * baja. Una fila por email (la del pedido más reciente). Como mucho los 5.000 pedidos más recientes.
 */
function dip_ofertas_clientes() {
	$ids = wc_get_orders( array(
		'type'       => 'shop_order',
		'status'     => array( 'wc-processing', 'wc-completed' ),
		'limit'      => 5000,
		'orderby'    => 'date',
		'order'      => 'DESC',
		'return'     => 'ids',
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_dip_publicidad', 'value' => 'si' ),
		),
	) );
	$bajas     = dip_ofertas_bajas();
	$provincia = function_exists( 'WC' ) && WC()->countries ? (array) WC()->countries->get_states( 'ES' ) : array();
	$filas     = array();
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( ! $p instanceof WC_Order ) continue;
		$email = strtolower( trim( (string) $p->get_billing_email() ) );
		if ( ! is_email( $email ) || isset( $bajas[ dip_ofertas_huella( $email ) ] ) ) continue;
		if ( isset( $filas[ $email ] ) ) {
			$filas[ $email ]['pedidos']++;
			continue;
		}
		$idioma = (string) $p->get_meta( '_dip_idioma' );
		$idioma = in_array( $idioma, array( 'ca', 'en' ), true ) ? $idioma : 'es';
		$usted  = function_exists( 'dip_resenas_de_usted' ) && dip_resenas_de_usted( $p );
		$fecha  = $p->get_date_created();
		$filas[ $email ] = array(
			'email'         => $email,
			'nombre'        => trim( $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() ),
			'empresa'       => (string) $p->get_billing_company(),
			'idioma'        => $idioma,
			'trato'         => $usted ? 'usted' : 'tú',
			'provincia'     => (string) ( $provincia[ $p->get_billing_state() ] ?? $p->get_billing_state() ),
			'cp'            => (string) $p->get_billing_postcode(),
			'ultimo_pedido' => $fecha ? $fecha->date( 'Y-m-d' ) : '',
			'pedidos'       => 1,
			'baja'          => dip_ofertas_url_baja( $email, $idioma, $usted ),
		);
	}
	return $filas;
}

add_action( 'admin_menu', static function () {
	add_submenu_page( 'dryicepack', 'Clientes para ofertas', 'Clientes para ofertas', 'manage_woocommerce', 'dip-ofertas', 'dip_ofertas_pantalla' );
}, 20 );

function dip_ofertas_pantalla() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$filas = dip_ofertas_clientes();
	$desde = (string) get_option( 'dip_ofertas_desde', '' );
	echo '<div class="wrap"><h1>Clientes para ofertas</h1>';
	echo '<p style="max-width:760px">Clientes que han comprado con la casilla «No quiero recibir avisos ni ofertas de hielo seco por email» sin marcar'
		. ( $desde ? ' (está en el checkout desde el ' . esc_html( date_i18n( 'j/n/Y', strtotime( $desde ) ) ) . ')' : ' (aún no hay ningún pedido con la casilla)' )
		. '. Se les puede escribir sobre hielo seco: fechas para pedir, puentes, Halloween, Nochevieja y ofertas (artículo 21.2 de la LSSI). No salen los que dijeron que no, los que se han dado de baja ni los pedidos de antes de la casilla.</p>';
	echo '<p style="max-width:760px"><strong>Cada email tiene que llevar un enlace para darse de baja:</strong> el de la columna «baja» del CSV (quien se da de baja ahí ya no vuelve a salir en esta lista) o el de la herramienta de envíos.</p>';
	printf(
		'<p><a class="button button-primary" href="%1$s">Descargar CSV (%2$d)</a> <span style="margin-left:1em">Bajas guardadas: %3$d</span></p>',
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dip_ofertas_csv' ), 'dip_ofertas_csv' ) ),
		count( $filas ),
		count( dip_ofertas_bajas() )
	);
	if ( $filas ) {
		echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>Email</th><th>Nombre</th><th>Empresa</th><th>Idioma</th><th>Provincia</th><th>Último pedido</th><th>Pedidos</th></tr></thead><tbody>';
		foreach ( array_slice( $filas, 0, 200 ) as $f ) {
			printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%d</td></tr>', esc_html( $f['email'] ), esc_html( $f['nombre'] ), esc_html( $f['empresa'] ), esc_html( $f['idioma'] . ' · ' . $f['trato'] ), esc_html( $f['provincia'] ), esc_html( $f['ultimo_pedido'] ), (int) $f['pedidos'] );
		}
		echo '</tbody></table>';
		if ( count( $filas ) > 200 ) echo '<p>Se ven los 200 primeros; el CSV los lleva todos.</p>';
	}
	echo '</div>';
}

/* CSV para Excel en español y para Brevo o Mailchimp: UTF-8 con BOM y punto y coma */
add_action( 'admin_post_dip_ofertas_csv', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Sin permiso.', 403 );
	check_admin_referer( 'dip_ofertas_csv' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="clientes-ofertas-' . current_time( 'Y-m-d' ) . '.csv"' );
	$salida = fopen( 'php://output', 'w' );
	fwrite( $salida, "\xEF\xBB\xBF" );
	fputcsv( $salida, array( 'email', 'nombre', 'empresa', 'idioma', 'trato', 'provincia', 'cp', 'ultimo_pedido', 'pedidos', 'baja' ), ';' );
	foreach ( dip_ofertas_clientes() as $f ) fputcsv( $salida, array_values( $f ), ';' );
	fclose( $salida );
	exit;
} );

/* =========================================================
 * Página de baja: ?dip_ofertas_baja=HUELLA&firma=…[&idioma=ca|en][&usted=1]
 * ========================================================= */

function dip_ofertas_textos_baja( $idioma = 'es', $usted = false ) {
	$tx = array(
		'es'       => array(
			'confirmar_titulo' => '¿Dejamos de enviarte ofertas?',
			'confirmar_texto'  => 'Pulsa el botón y no te enviaremos más avisos ni ofertas por email. Los emails de tus pedidos (confirmación, entrega y factura) te seguirán llegando.',
			'boton'            => 'Darme de baja',
			'hecho_titulo'     => 'Hecho: ya no te enviaremos ofertas',
			'hecho_texto'      => 'No volveremos a escribirte con avisos ni ofertas. Los emails de tus pedidos te seguirán llegando.',
			'no_valido_titulo' => 'Este enlace no es válido',
			'no_valido_texto'  => 'Puede que esté incompleto. Si no quieres recibir ofertas, escríbenos a info@dryicepack.es y te damos de baja.',
			'volver'           => 'Ir a dryicepack.es',
		),
		'es_usted' => array(
			'confirmar_titulo' => '¿Dejamos de enviarle ofertas?',
			'confirmar_texto'  => 'Pulse el botón y no le enviaremos más avisos ni ofertas por email. Los emails de sus pedidos (confirmación, entrega y factura) le seguirán llegando.',
			'boton'            => 'Darme de baja',
			'hecho_titulo'     => 'Hecho: ya no le enviaremos ofertas',
			'hecho_texto'      => 'No volveremos a escribirle con avisos ni ofertas. Los emails de sus pedidos le seguirán llegando.',
			'no_valido_titulo' => 'Este enlace no es válido',
			'no_valido_texto'  => 'Puede que esté incompleto. Si no quiere recibir ofertas, escríbanos a info@dryicepack.es y tramitamos la baja.',
			'volver'           => 'Ir a dryicepack.es',
		),
		'ca'       => array(
			'confirmar_titulo' => 'Deixem d\'enviar-te ofertes?',
			'confirmar_texto'  => 'Prem el botó i no t\'enviarem més avisos ni ofertes per correu. Els correus de les teves comandes (confirmació, entrega i factura) et continuaran arribant.',
			'boton'            => 'Donar-me de baixa',
			'hecho_titulo'     => 'Fet: ja no t\'enviarem ofertes',
			'hecho_texto'      => 'No et tornarem a escriure amb avisos ni ofertes. Els correus de les comandes et continuaran arribant.',
			'no_valido_titulo' => 'Aquest enllaç no és vàlid',
			'no_valido_texto'  => 'Potser està incomplet. Si no vols rebre ofertes, escriu-nos a info@dryicepack.es i et donem de baixa.',
			'volver'           => 'Anar a dryicepack.es',
		),
		'ca_usted' => array(
			'confirmar_titulo' => 'Deixem d\'enviar-li ofertes?',
			'confirmar_texto'  => 'Premi el botó i no li enviarem més avisos ni ofertes per correu. Els correus de les seves comandes (confirmació, entrega i factura) li continuaran arribant.',
			'boton'            => 'Donar-me de baixa',
			'hecho_titulo'     => 'Fet: ja no li enviarem ofertes',
			'hecho_texto'      => 'No li tornarem a escriure amb avisos ni ofertes. Els correus de les comandes li continuaran arribant.',
			'no_valido_titulo' => 'Aquest enllaç no és vàlid',
			'no_valido_texto'  => 'Potser està incomplet. Si no vol rebre ofertes, escrigui\'ns a info@dryicepack.es i tramitem la baixa.',
			'volver'           => 'Anar a dryicepack.es',
		),
		'en'       => array(
			'confirmar_titulo' => 'Stop sending you offers?',
			'confirmar_texto'  => 'Press the button and we will not send you any more news or offers by email. Emails about your orders (confirmation, delivery and invoice) will still reach you.',
			'boton'            => 'Unsubscribe me',
			'hecho_titulo'     => 'Done: no more offers',
			'hecho_texto'      => 'We will not email you news or offers again. Emails about your orders will still reach you.',
			'no_valido_titulo' => 'This link is not valid',
			'no_valido_texto'  => 'It may be incomplete. If you do not want to receive offers, write to info@dryicepack.es and we will unsubscribe you.',
			'volver'           => 'Go to dryicepack.es',
		),
	);
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	return $tx[ $idioma . ( $usted && 'en' !== $idioma ? '_usted' : '' ) ];
}

add_action( 'template_redirect', 'dip_ofertas_pagina_baja', -1 );

function dip_ofertas_pagina_baja() {
	if ( ! isset( $_GET['dip_ofertas_baja'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- la firma hace de credencial
	$huella = strtolower( sanitize_text_field( wp_unslash( $_GET['dip_ofertas_baja'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$firma  = isset( $_GET['firma'] ) ? sanitize_text_field( wp_unslash( $_GET['firma'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$idioma = isset( $_GET['idioma'] ) ? sanitize_key( wp_unslash( $_GET['idioma'] ) ) : 'es'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$idioma = in_array( $idioma, array( 'ca', 'en' ), true ) ? $idioma : 'es';
	$usted  = ! empty( $_GET['usted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$valido = (bool) preg_match( '/^[a-f0-9]{32}$/', $huella ) && hash_equals( dip_ofertas_firma( $huella ), $firma );

	if ( ! $valido ) {
		$estado = 'no_valido';
	} elseif ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		dip_ofertas_dar_de_baja( $huella );
		$estado = 'hecho';
	} else {
		$estado = isset( dip_ofertas_bajas()[ $huella ] ) ? 'hecho' : 'confirmar';
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // WP Rocket
	nocache_headers();
	status_header( 'no_valido' === $estado ? 400 : 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Referrer-Policy: no-referrer' );
	$accion = $valido ? add_query_arg( array_filter( array( 'dip_ofertas_baja' => $huella, 'firma' => $firma, 'idioma' => 'es' === $idioma ? '' : $idioma, 'usted' => $usted ? 1 : '' ) ), home_url( '/' ) ) : '';
	echo dip_resenas_html_baja( $estado, $idioma, $usted, $accion, dip_ofertas_textos_baja( $idioma, $usted ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	exit;
}
