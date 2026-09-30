<?php
/**
 * Formularios propios (contacto, cuenta de empresa, presupuesto por volumen) protegidos contra spam sin captcha:
 * campo trampa, tiempo mínimo de relleno firmado, nonce, límite por IP y filtro de enlaces.
 * Cada envío se guarda en Escritorio → Dryicepack → Solicitudes y llega por email a la dirección de avisos.
 * Desde una solicitud de cuenta se crea el cliente con un clic.
 *
 * El tema pinta el HTML con dip_formulario_ocultos( $tipo ) y los nombres de campo de dip_formularios_tipos().
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_formularios_tipos() {
	return array(
		'contacto'    => array(
			'titulo' => 'Contacto',
			'campos' => array( 'nombre' => 'Nombre', 'email' => 'Email', 'telefono' => 'Teléfono', 'mensaje' => 'Mensaje' ),
			'oblig'  => array( 'nombre', 'mensaje' ),
		),
		'cuenta'      => array(
			'titulo' => 'Cuenta de empresa',
			'campos' => array( 'empresa' => 'Empresa', 'nif' => 'NIF/CIF', 'nombre' => 'Persona de contacto', 'email' => 'Email', 'telefono' => 'Teléfono', 'cp' => 'Código postal de entrega', 'uso' => 'Uso', 'kg' => 'Kilos por pedido', 'frecuencia' => 'Frecuencia', 'mensaje' => 'Comentarios', 'recordatorio' => 'Acepta recordatorio de reposición' ),
			'oblig'  => array( 'empresa', 'nombre', 'email', 'telefono' ),
		),
		'presupuesto' => array(
			'titulo' => 'Presupuesto por volumen',
			'campos' => array( 'empresa' => 'Empresa', 'nombre' => 'Nombre', 'email' => 'Email', 'telefono' => 'Teléfono', 'cp' => 'Código postal de entrega', 'kg' => 'Kilos', 'fecha' => 'Fecha necesaria', 'uso' => 'Uso', 'mensaje' => 'Detalles' ),
			'oblig'  => array( 'nombre', 'telefono', 'kg' ),
		),
	);
}

/* ---------- Registro de solicitudes (no público) ---------- */
add_action( 'init', static function () {
	register_post_type( 'dip_solicitud', array(
		'labels'          => array( 'name' => 'Solicitudes', 'singular_name' => 'Solicitud', 'menu_name' => 'Solicitudes', 'edit_item' => 'Solicitud' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'dryicepack',
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
		'supports'        => array( 'title', 'editor' ),
		'show_in_rest'    => false,
	) );
} );

/* ---------- Campos ocultos anti-spam que el tema mete en cada formulario ---------- */
function dip_formulario_ocultos( $tipo ) {
	$ahora = time();
	$firma = wp_hash( 'dip_form|' . $tipo . '|' . $ahora );
	$html  = wp_nonce_field( 'dip_form_' . $tipo, '_dip_nonce', false, false );
	$html .= '<input type="hidden" name="action" value="dip_formulario">';
	$html .= '<input type="hidden" name="dip_tipo" value="' . esc_attr( $tipo ) . '">';
	$html .= '<input type="hidden" name="dip_t" value="' . esc_attr( $ahora . '.' . $firma ) . '">';
	$html .= '<input type="hidden" name="dip_origen" value="' . esc_attr( wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ) . '">';
	$html .= '<div class="dip-trampa" aria-hidden="true"><label>Web<input type="text" name="web" value="" tabindex="-1" autocomplete="off"></label></div>';
	return $html;
}

function dip_ip_cliente() {
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? ( $_SERVER['REMOTE_ADDR'] ?? '' ); // Cloudflare delante
	return filter_var( wp_unslash( $ip ), FILTER_VALIDATE_IP ) ?: '0.0.0.0';
}

/* ---------- Envío ---------- */
add_action( 'admin_post_nopriv_dip_formulario', 'dip_procesar_formulario' );
add_action( 'admin_post_dip_formulario', 'dip_procesar_formulario' );

function dip_procesar_formulario() {
	$tipos  = dip_formularios_tipos();
	$tipo   = sanitize_key( wp_unslash( $_POST['dip_tipo'] ?? '' ) );
	$ajax   = ! empty( $_POST['ajax'] );
	$origen = esc_url_raw( home_url( sanitize_text_field( wp_unslash( $_POST['dip_origen'] ?? '/' ) ) ) );
	$idioma = dip_idioma_desde_ruta( (string) wp_parse_url( $origen, PHP_URL_PATH ) );
	$tx     = dip_textos_formularios( $idioma );

	$responder = static function ( $ok, $mensaje, $errores = array() ) use ( $ajax, $origen ) {
		if ( $ajax ) {
			wp_send_json( array( 'ok' => $ok, 'mensaje' => $mensaje, 'errores' => $errores ), $ok ? 200 : 400 );
		}
		$url = add_query_arg( $ok ? array( 'enviado' => 1 ) : array( 'error' => 1 ), $origen );
		wp_safe_redirect( $url . '#formulario' );
		exit;
	};

	if ( ! isset( $tipos[ $tipo ] ) ) $responder( false, $tx['error'] );
	if ( ! isset( $_POST['_dip_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_dip_nonce'] ) ), 'dip_form_' . $tipo ) ) $responder( false, $tx['caducado'] );

	// Robots: rellenan el campo trampa o envían en menos de 3 segundos. Se responde "ok" para no darles pistas.
	$marca = explode( '.', sanitize_text_field( wp_unslash( $_POST['dip_t'] ?? '' ) ) );
	$firma_ok = 2 === count( $marca ) && hash_equals( wp_hash( 'dip_form|' . $tipo . '|' . $marca[0] ), $marca[1] );
	if ( ! empty( $_POST['web'] ) || ! $firma_ok || ( time() - (int) $marca[0] ) < 3 ) $responder( true, $tx['ok'] );
	if ( ( time() - (int) $marca[0] ) > 2 * DAY_IN_SECONDS ) $responder( false, $tx['caducado'] );

	// Máximo 5 envíos por hora y por IP.
	$clave_ip = 'dip_form_ip_' . md5( dip_ip_cliente() );
	$envios   = (int) get_transient( $clave_ip );
	if ( $envios >= 5 ) $responder( false, $tx['limite'] );

	$def    = $tipos[ $tipo ];
	$datos  = array();
	foreach ( $def['campos'] as $campo => $etiqueta ) {
		$valor = wp_unslash( $_POST[ $campo ] ?? '' );
		$valor = 'mensaje' === $campo ? sanitize_textarea_field( $valor ) : sanitize_text_field( $valor );
		$datos[ $campo ] = mb_substr( trim( $valor ), 0, 'mensaje' === $campo ? 3000 : 200 );
	}
	$errores = array();
	foreach ( $def['oblig'] as $campo ) {
		if ( '' === $datos[ $campo ] ) $errores[ $campo ] = $tx['falta'];
	}
	if ( '' === $datos['email'] && '' === ( $datos['telefono'] ?? '' ) ) $errores['email'] = $tx['contacto'];
	if ( '' !== $datos['email'] && ! is_email( $datos['email'] ) ) $errores['email'] = $tx['email'];
	if ( empty( $_POST['privacidad'] ) ) $errores['privacidad'] = $tx['privacidad'];
	if ( preg_match_all( '#https?://|www\.#i', implode( ' ', $datos ) ) > 2 ) $responder( true, $tx['ok'] ); // spam de enlaces
	if ( $errores ) $responder( false, $tx['revisa'], $errores );

	set_transient( $clave_ip, $envios + 1, HOUR_IN_SECONDS );

	$titulo = sprintf( '%s · %s', $def['titulo'], $datos['empresa'] ?? '' ?: $datos['nombre'] );
	$lineas = array();
	foreach ( $def['campos'] as $campo => $etiqueta ) {
		if ( '' !== $datos[ $campo ] ) $lineas[] = $etiqueta . ': ' . $datos[ $campo ];
	}
	$lineas[] = 'Página: ' . $origen;
	$lineas[] = 'Idioma: ' . $idioma;
	$texto    = implode( "\n", $lineas );

	$id = wp_insert_post( array(
		'post_type'    => 'dip_solicitud',
		'post_status'  => 'private',
		'post_title'   => $titulo,
		'post_content' => $texto,
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_dip_tipo', $tipo );
		update_post_meta( $id, '_dip_datos', $datos );
	}

	$cabeceras = array();
	if ( is_email( $datos['email'] ) ) $cabeceras[] = 'Reply-To: ' . $datos['nombre'] . ' <' . $datos['email'] . '>';
	wp_mail( dip_ajuste( 'email_avisos' ), '[Web] ' . $titulo, $texto . "\n\nVer en la web: " . admin_url( 'post.php?post=' . (int) $id . '&action=edit' ), $cabeceras );

	$responder( true, $tx['ok'] );
}

function dip_idioma_desde_ruta( $ruta ) {
	$ruta = trim( (string) $ruta, '/' );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $ruta, $base ) ) $ruta = trim( substr( $ruta, strlen( $base ) ), '/' );
	return preg_match( '#^(ca|en)(/|$)#', $ruta, $m ) ? $m[1] : 'es';
}

function dip_textos_formularios( $idioma ) {
	$tx = array(
		'es' => array( 'ok' => 'Recibido. Te respondemos en horario laboral, normalmente el mismo día.', 'error' => 'No se ha podido enviar. Llámanos al 936 73 76 41.', 'caducado' => 'La página llevaba mucho rato abierta. Recárgala y vuelve a enviar.', 'limite' => 'Has enviado varios formularios seguidos. Llámanos al 936 73 76 41 o escríbenos por WhatsApp.', 'falta' => 'Falta este dato.', 'contacto' => 'Déjanos un email o un teléfono.', 'email' => 'Revisa el email.', 'privacidad' => 'Acepta la política de privacidad para enviar.', 'revisa' => 'Revisa los campos marcados.' ),
		'ca' => array( 'ok' => 'Rebut. Et responem en horari laboral, normalment el mateix dia.', 'error' => "No s'ha pogut enviar. Truca'ns al 936 73 76 41.", 'caducado' => 'La pàgina portava molta estona oberta. Recarrega-la i torna a enviar.', 'limite' => "Has enviat diversos formularis seguits. Truca'ns al 936 73 76 41 o escriu-nos per WhatsApp.", 'falta' => 'Falta aquesta dada.', 'contacto' => "Deixa'ns un correu o un telèfon.", 'email' => 'Revisa el correu.', 'privacidad' => 'Accepta la política de privacitat per enviar.', 'revisa' => 'Revisa els camps marcats.' ),
		'en' => array( 'ok' => 'Received. We reply during working hours, usually the same day.', 'error' => 'It could not be sent. Call us on +34 936 73 76 41.', 'caducado' => 'This page was open for a long time. Reload it and send again.', 'limite' => 'You have sent several forms in a row. Call +34 936 73 76 41 or message us on WhatsApp.', 'falta' => 'This field is required.', 'contacto' => 'Leave an email or a phone number.', 'email' => 'Please check the email.', 'privacidad' => 'Accept the privacy policy to send.', 'revisa' => 'Please check the highlighted fields.' ),
	);
	return $tx[ $idioma ] ?? $tx['es'];
}

/* ---------- Escritorio: columnas y "crear cuenta de empresa" ---------- */
add_filter( 'manage_dip_solicitud_posts_columns', static function () {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Solicitud', 'dip_tipo' => 'Tipo', 'dip_contacto' => 'Contacto', 'date' => 'Fecha' );
} );
add_action( 'manage_dip_solicitud_posts_custom_column', static function ( $columna, $id ) {
	$datos = (array) get_post_meta( $id, '_dip_datos', true );
	if ( 'dip_tipo' === $columna ) echo esc_html( dip_formularios_tipos()[ get_post_meta( $id, '_dip_tipo', true ) ]['titulo'] ?? '' );
	if ( 'dip_contacto' === $columna ) echo esc_html( trim( ( $datos['telefono'] ?? '' ) . ' ' . ( $datos['email'] ?? '' ) ) );
}, 10, 2 );

add_filter( 'post_row_actions', static function ( $acciones, $post ) {
	if ( 'dip_solicitud' !== $post->post_type || 'cuenta' !== get_post_meta( $post->ID, '_dip_tipo', true ) ) return $acciones;
	if ( get_post_meta( $post->ID, '_dip_usuario', true ) ) {
		$acciones['dip_cuenta'] = '<a href="' . esc_url( get_edit_user_link( (int) get_post_meta( $post->ID, '_dip_usuario', true ) ) ) . '">Ver cliente</a>';
		return $acciones;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=dip_crear_cuenta&solicitud=' . $post->ID ), 'dip_crear_cuenta_' . $post->ID );
	$acciones['dip_cuenta'] = '<a href="' . esc_url( $url ) . '">Crear cuenta de empresa</a>';
	return $acciones;
}, 10, 2 );

add_action( 'admin_post_dip_crear_cuenta', static function () {
	$id = absint( $_GET['solicitud'] ?? 0 );
	if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'dip_crear_cuenta_' . $id ) ) wp_die( 'Sin permiso.' );
	$datos = (array) get_post_meta( $id, '_dip_datos', true );
	$email = sanitize_email( $datos['email'] ?? '' );
	if ( ! is_email( $email ) ) wp_die( 'La solicitud no tiene un email válido.' );

	$usuario = get_user_by( 'email', $email );
	if ( ! $usuario ) {
		$usuario_id = function_exists( 'wc_create_new_customer' )
			? wc_create_new_customer( $email, '', '', array( 'first_name' => $datos['nombre'] ?? '' ) )
			: wp_create_user( $email, wp_generate_password( 20 ), $email );
		if ( is_wp_error( $usuario_id ) ) wp_die( esc_html( $usuario_id->get_error_message() ) );
	} else {
		$usuario_id = $usuario->ID;
	}
	update_user_meta( $usuario_id, 'dip_cuenta_empresa', '1' );
	update_user_meta( $usuario_id, 'billing_company', $datos['empresa'] ?? '' );
	update_user_meta( $usuario_id, 'billing_nif', dip_normalizar_nif_simple( $datos['nif'] ?? '' ) );
	update_user_meta( $usuario_id, 'billing_phone', $datos['telefono'] ?? '' );
	update_user_meta( $usuario_id, 'billing_email', $email );
	update_user_meta( $usuario_id, 'billing_first_name', $datos['nombre'] ?? '' );
	update_user_meta( $usuario_id, 'billing_postcode', $datos['cp'] ?? '' );
	update_user_meta( $usuario_id, 'billing_country', 'ES' );
	update_user_meta( $usuario_id, 'dip_recordatorio', empty( $datos['recordatorio'] ) ? '0' : '1' );
	update_post_meta( $id, '_dip_usuario', $usuario_id );
	wp_safe_redirect( get_edit_user_link( $usuario_id ) . '#dip_descuento' );
	exit;
} );

function dip_normalizar_nif_simple( $valor ) {
	return preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $valor ) );
}
