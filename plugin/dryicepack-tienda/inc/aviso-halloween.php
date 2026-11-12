<?php
/**
 * «Avísame» de la landing de Halloween (11/11/2026; diseño «La hoja de octubre», zz-bocetos/avisame-a.html, elegido por el
 * propietario). Fuera de campaña, quien quiera hielo seco para el próximo Halloween deja su email y le escribimos una sola
 * vez, a principios de octubre, con las fechas para pedirlo.
 *
 * - Consentimiento (art. 21.1 de la LSSI): no son clientes. Con confirmación por email (doble opt-in): solo entra quien
 *   pulsa el botón del enlace; así nadie apunta el email de otro y queda constancia (fecha de alta y de confirmación).
 *   El enlace lleva a una página con un botón (como la baja de las reseñas): los antivirus del correo abren los enlaces y
 *   no deben confirmar por nadie.
 * - Antispam como los formularios (inc/formularios.php): campo trampa, tiempo mínimo firmado, nonce y 5 envíos por hora e IP.
 * - Lista en Dryicepack → Avisos de Halloween, con CSV (y el enlace de baja de cada uno). Baja con un clic.
 * - Sin confirmar en 7 días, se borra (tarea diaria). El 1 de septiembre del año del aviso, la web avisa a la dirección de
 *   avisos de cuántos esperan; si ese email no sale, lo vuelve a intentar al día siguiente. Cómo se manda el aviso (desde
 *   la web o con Brevo o Mailchimp) se decide entonces.
 * - La landing solo está en castellano: textos en castellano.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Año del próximo Halloween: el de este año hasta el 31 de octubre; después, el del año que viene. */
function dip_ah_ano( ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	return (int) $ahora->format( 'Y' ) + ( $ahora->format( 'm-d' ) > '10-31' ? 1 : 0 );
}

function dip_ah_huella( $email ) {
	return wp_hash( 'aviso-halloween|' . strtolower( trim( (string) $email ) ), 'dip_ofertas' );
}

/** $para: 'confirmar' o 'baja' (firmas distintas: el enlace de baja no confirma, ni al revés). */
function dip_ah_firma( $huella, $para ) {
	return substr( wp_hash( 'aviso-halloween-' . $para . '|' . $huella, 'dip_ofertas' ), 0, 16 );
}

function dip_ah_url( $huella, $para ) {
	return add_query_arg( array( 'dip_aviso_' . $para => $huella, 'firma' => dip_ah_firma( $huella, $para ) ), home_url( '/' ) );
}

/** [ huella => [ 'email', 'ano', 'alta' => 'Y-m-d H:i:s', 'confirmado' => '' | 'Y-m-d H:i:s' ] ] */
function dip_ah_lista() {
	$l = get_option( 'dip_aviso_halloween', array() );
	return is_array( $l ) ? $l : array();
}

function dip_ah_guardar( array $l ) {
	update_option( 'dip_aviso_halloween', $l, false );
}

/* =========================================================
 * El formulario: campos ocultos antispam (el tema pinta el resto) y el envío
 * ========================================================= */

function dip_ah_ocultos() {
	$ahora = time();
	return wp_nonce_field( 'dip_aviso_halloween', '_dip_nonce', false, false )
		. '<input type="hidden" name="action" value="dip_aviso_halloween">'
		. '<input type="hidden" name="dip_t" value="' . esc_attr( $ahora . '.' . wp_hash( 'dip_aviso|' . $ahora ) ) . '">'
		. '<input type="hidden" name="dip_origen" value="' . esc_attr( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ) . '">'
		. '<div class="dip-trampa" aria-hidden="true"><label>Web<input type="text" name="web" value="" tabindex="-1" autocomplete="off"></label></div>';
}

function dip_ah_textos() {
	$ano = dip_ah_ano();
	return array(
		'revisa'    => 'Revisa tu email: pulsa el botón del enlace que te hemos enviado para confirmar el aviso.',
		'ya'        => sprintf( 'Ya estás apuntado: te escribiremos a principios de octubre de %d.', $ano ),
		'email'     => 'Escribe un email válido.',
		'caducado'  => 'La página llevaba mucho rato abierta. Recárgala y vuelve a probar.',
		'limite'    => 'Demasiados intentos seguidos. Prueba dentro de una hora.',
		'no_sale'   => 'No hemos podido enviarte el email de confirmación. Prueba otra vez o escríbenos a info@dryicepack.es.',
	);
}

add_action( 'admin_post_nopriv_dip_aviso_halloween', 'dip_ah_alta' );
add_action( 'admin_post_dip_aviso_halloween', 'dip_ah_alta' );

function dip_ah_alta() {
	$t      = dip_ah_textos();
	$ajax   = ! empty( $_POST['ajax'] );
	$origen = home_url( sanitize_text_field( wp_unslash( $_POST['dip_origen'] ?? '/' ) ) );
	$responder = static function ( $ok, $mensaje ) use ( $ajax, $origen ) {
		if ( $ajax ) wp_send_json( array( 'ok' => $ok, 'mensaje' => $mensaje ), $ok ? 200 : 400 );
		wp_safe_redirect( add_query_arg( 'aviso', $ok ? 'revisa' : 'error', $origen ) . '#aviso' );
		exit;
	};
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_dip_nonce'] ?? '' ) ), 'dip_aviso_halloween' ) ) $responder( false, $t['caducado'] );
	// Robots: rellenan el campo trampa o envían en menos de 3 segundos. Se responde «ok» para no darles pistas.
	$marca    = explode( '.', sanitize_text_field( wp_unslash( $_POST['dip_t'] ?? '' ) ) );
	$firma_ok = 2 === count( $marca ) && hash_equals( wp_hash( 'dip_aviso|' . $marca[0] ), $marca[1] );
	if ( ! empty( $_POST['web'] ) || ! $firma_ok || ( time() - (int) $marca[0] ) < 3 ) $responder( true, $t['revisa'] );
	if ( ( time() - (int) $marca[0] ) > 2 * DAY_IN_SECONDS ) $responder( false, $t['caducado'] );
	$clave_ip = 'dip_aviso_ip_' . md5( function_exists( 'dip_ip_cliente' ) ? dip_ip_cliente() : ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$envios   = (int) get_transient( $clave_ip );
	if ( $envios >= 5 ) $responder( false, $t['limite'] );
	set_transient( $clave_ip, $envios + 1, HOUR_IN_SECONDS );

	$email = strtolower( trim( sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ) ) );
	if ( ! is_email( $email ) ) $responder( false, $t['email'] );
	$huella = dip_ah_huella( $email );
	$lista  = dip_ah_lista();
	if ( ! empty( $lista[ $huella ]['confirmado'] ) ) $responder( true, $t['ya'] );
	$lista[ $huella ] = array( 'email' => $email, 'ano' => dip_ah_ano(), 'alta' => current_time( 'mysql' ), 'confirmado' => '' );
	dip_ah_guardar( $lista );
	$ok = dip_ah_enviar_confirmacion( $email );
	$responder( $ok, $ok ? $t['revisa'] : $t['no_sale'] );
}

function dip_ah_enviar_confirmacion( $email ) {
	$huella = dip_ah_huella( $email );
	$ano    = (int) ( dip_ah_lista()[ $huella ]['ano'] ?? dip_ah_ano() );
	$url    = dip_ah_url( $huella, 'confirmar' );
	$titulo = sprintf( 'Confirma tu aviso de Halloween %d', $ano );
	$parte  = array(
		array( 'p', sprintf( 'Hola: has pedido que te avisemos para Halloween %d.', $ano ), 'grande' ),
		array( 'p', sprintf( 'Pulsa el botón para confirmarlo. Te escribiremos una sola vez, a principios de octubre de %d, con las fechas para pedir el hielo seco y que llegue a tiempo.', $ano ) ),
		array( 'boton', 'Confirmar el aviso', $url ),
		array( 'p', 'Si no lo has pedido tú, no hagas nada: sin confirmar, borramos tu email en 7 días.', 'pequeno' ),
	);
	$html = function_exists( 'dip_email_documento' )
		? dip_email_documento( array( 'idioma' => 'es', 'previo' => $titulo, 'titulo' => $titulo, 'bloques' => $parte ) )
		: '<p>' . esc_html( $parte[0][1] ) . '</p><p>' . esc_html( $parte[1][1] ) . '</p><p><a href="' . esc_url( $url ) . '">Confirmar el aviso</a></p>';
	$c          = dip_contacto();
	$cabeceras  = array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $c['marca'] . ' <' . $c['email'] . '>' );
	return function_exists( 'WC' ) && WC() && WC()->mailer() ? (bool) WC()->mailer()->send( $email, $titulo, $html, $cabeceras ) : (bool) wp_mail( $email, $titulo, $html, $cabeceras );
}

/* =========================================================
 * Confirmar y darse de baja: ?dip_aviso_confirmar=…&firma=… y ?dip_aviso_baja=…&firma=…
 * GET enseña un botón; POST hace el cambio. Misma página que la baja de las reseñas (inc/resenas.php).
 * ========================================================= */

function dip_ah_textos_pagina( $para ) {
	$ano = dip_ah_ano();
	if ( 'confirmar' === $para ) {
		return array(
			'confirmar_titulo' => sprintf( '¿Confirmas el aviso de Halloween %d?', $ano ),
			'confirmar_texto'  => sprintf( 'Pulsa el botón y te escribiremos una sola vez, a principios de octubre de %d, con las fechas para pedir el hielo seco y que llegue a tiempo.', $ano ),
			'boton'            => 'Confirmar el aviso',
			'hecho_titulo'     => sprintf( 'Hecho: te avisaremos para Halloween %d', $ano ),
			'hecho_texto'      => sprintf( 'Te escribiremos a principios de octubre de %d. Si cambias de idea, el email lleva el enlace para darte de baja.', $ano ),
			'no_valido_titulo' => 'Este enlace no es válido',
			'no_valido_texto'  => 'Puede que esté incompleto o que haya caducado: los avisos sin confirmar se borran a los 7 días. Vuelve a apuntarte en la página de Halloween.',
			'volver'           => 'Ir a dryicepack.es',
		);
	}
	return array(
		'confirmar_titulo' => '¿Quitamos tu aviso de Halloween?',
		'confirmar_texto'  => 'Pulsa el botón y no te escribiremos para Halloween. Borramos tu email de la lista.',
		'boton'            => 'Quitar el aviso',
		'hecho_titulo'     => 'Hecho: aviso quitado',
		'hecho_texto'      => 'No te escribiremos para Halloween. Tu email ya no está en la lista.',
		'no_valido_titulo' => 'Este enlace no es válido',
		'no_valido_texto'  => 'Puede que esté incompleto. Si no quieres el aviso, escríbenos a info@dryicepack.es y lo quitamos.',
		'volver'           => 'Ir a dryicepack.es',
	);
}

add_action( 'template_redirect', 'dip_ah_pagina', -1 );

function dip_ah_pagina() {
	$para = isset( $_GET['dip_aviso_confirmar'] ) ? 'confirmar' : ( isset( $_GET['dip_aviso_baja'] ) ? 'baja' : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- la firma hace de credencial
	if ( '' === $para ) return;
	$huella = strtolower( sanitize_text_field( wp_unslash( $_GET[ 'dip_aviso_' . $para ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$firma  = isset( $_GET['firma'] ) ? sanitize_text_field( wp_unslash( $_GET['firma'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$lista  = dip_ah_lista();
	$valido = (bool) preg_match( '/^[a-f0-9]{32}$/', $huella ) && hash_equals( dip_ah_firma( $huella, $para ), $firma );
	if ( 'confirmar' === $para && $valido && ! isset( $lista[ $huella ] ) ) $valido = false; // borrado (más de 7 días sin confirmar)

	if ( ! $valido ) {
		$estado = 'no_valido';
	} elseif ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		if ( 'confirmar' === $para ) {
			if ( empty( $lista[ $huella ]['confirmado'] ) ) $lista[ $huella ]['confirmado'] = current_time( 'mysql' );
		} else {
			unset( $lista[ $huella ] );
		}
		dip_ah_guardar( $lista );
		$estado = 'hecho';
	} else {
		$hecho  = 'confirmar' === $para ? ! empty( $lista[ $huella ]['confirmado'] ) : ! isset( $lista[ $huella ] );
		$estado = $hecho ? 'hecho' : 'confirmar';
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // WP Rocket
	nocache_headers();
	status_header( 'no_valido' === $estado ? 400 : 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Referrer-Policy: no-referrer' );
	echo dip_resenas_html_baja( $estado, 'es', false, $valido ? dip_ah_url( $huella, $para ) : '', dip_ah_textos_pagina( $para ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	exit;
}

/* =========================================================
 * Tarea diaria: borrar lo que no se confirma en 7 días y avisar el 1 de septiembre
 * ========================================================= */

// do_action() sin argumentos pasa una cadena vacía: la fecha solo la pasan las pruebas
add_action( 'dip_tarea_diaria', static function () {
	dip_ah_tarea_diaria();
} );

function dip_ah_tarea_diaria( ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$lista = dip_ah_lista();
	$antes = count( $lista );
	$limite = $ahora->modify( '-7 days' )->format( 'Y-m-d H:i:s' );
	$lista = array_filter( $lista, static fn( $x ) => ! empty( $x['confirmado'] ) || (string) ( $x['alta'] ?? '' ) >= $limite );
	if ( count( $lista ) !== $antes ) dip_ah_guardar( $lista );

	// El 1 de septiembre del año del aviso (o el primer día después en que salga el email), una sola vez por año
	$ano = (int) $ahora->format( 'Y' );
	if ( $ahora->format( 'm-d' ) < '09-01' || $ahora->format( 'm-d' ) > '10-31' ) return;
	if ( (int) get_option( 'dip_aviso_halloween_recordado', 0 ) >= $ano ) return;
	$esperan = count( array_filter( $lista, static fn( $x ) => ! empty( $x['confirmado'] ) && (int) ( $x['ano'] ?? 0 ) === $ano ) );
	if ( ! $esperan ) return;
	$texto = sprintf(
		"Hay %d personas esperando el aviso de Halloween %d (confirmado por email).\n\n"
		. "Se les prometió una sola vez, a principios de octubre, con las fechas para pedir. Antes de escribirles:\n"
		. "1. Pon las fechas de %d en la landing (plantillas/halloween.php).\n"
		. "2. Descarga la lista en el escritorio: Dryicepack → Avisos de Halloween (CSV con el enlace de baja de cada uno).\n"
		. "3. Mándales el aviso desde la herramienta de envíos (o pide que lo mande la web).\n\n%s",
		$esperan, $ano, $ano, admin_url( 'admin.php?page=dip-aviso-halloween' )
	);
	if ( wp_mail( (string) dip_ajuste( 'email_avisos' ), sprintf( 'Dryicepack: %d esperan el aviso de Halloween %d', $esperan, $ano ), $texto ) ) {
		update_option( 'dip_aviso_halloween_recordado', $ano, false );
	}
}

/* =========================================================
 * Dryicepack → Avisos de Halloween
 * ========================================================= */

add_action( 'admin_menu', static function () {
	add_submenu_page( 'dryicepack', 'Avisos de Halloween', 'Avisos de Halloween', 'manage_woocommerce', 'dip-aviso-halloween', 'dip_ah_pantalla' );
}, 21 );

function dip_ah_pantalla() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$lista = dip_ah_lista();
	$si    = array_filter( $lista, static fn( $x ) => ! empty( $x['confirmado'] ) );
	echo '<div class="wrap"><h1>Avisos de Halloween</h1>';
	echo '<p style="max-width:760px">Quien se apunta en la página de Halloween fuera de campaña. Solo valen los confirmados por email: se les prometió escribirles una sola vez, a principios de octubre, con las fechas para pedir. Los que no confirman en 7 días se borran solos. El 1 de septiembre la web te recuerda cuántos esperan.</p>';
	printf(
		'<p><strong>Confirmados: %1$d</strong> · Sin confirmar: %2$d <a class="button button-primary" style="margin-left:1em" href="%3$s">Descargar CSV (%1$d)</a></p>',
		count( $si ),
		count( $lista ) - count( $si ),
		esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dip_aviso_halloween_csv' ), 'dip_aviso_halloween_csv' ) )
	);
	if ( $si ) {
		echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Email</th><th>Para Halloween</th><th>Se apuntó</th><th>Confirmó</th></tr></thead><tbody>';
		foreach ( array_slice( $si, 0, 300 ) as $x ) printf( '<tr><td>%s</td><td>%d</td><td>%s</td><td>%s</td></tr>', esc_html( $x['email'] ), (int) $x['ano'], esc_html( $x['alta'] ), esc_html( $x['confirmado'] ) );
		echo '</tbody></table>';
	}
	echo '</div>';
}

add_action( 'admin_post_dip_aviso_halloween_csv', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Sin permiso.', 403 );
	check_admin_referer( 'dip_aviso_halloween_csv' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="avisos-halloween-' . current_time( 'Y-m-d' ) . '.csv"' );
	$salida = fopen( 'php://output', 'w' );
	fwrite( $salida, "\xEF\xBB\xBF" );
	fputcsv( $salida, array( 'email', 'halloween', 'alta', 'confirmado', 'baja' ), ';' );
	foreach ( dip_ah_lista() as $huella => $x ) {
		if ( empty( $x['confirmado'] ) ) continue;
		fputcsv( $salida, array( $x['email'], (int) $x['ano'], $x['alta'], $x['confirmado'], dip_ah_url( $huella, 'baja' ) ), ';' );
	}
	fclose( $salida );
	exit;
} );
