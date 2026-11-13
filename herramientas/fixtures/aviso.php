<?php
// Prueba local (herramientas/probar-aviso-halloween.mjs): la lista del «Avísame» de Halloween. Solo desde 127.0.0.1.
// Se copia al plugin como zz-aviso.php y se borra al terminar la prueba; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
$zona = wp_timezone();
if ( isset( $_GET['limpiar_ip'] ) ) { // las pruebas envían muchas veces desde 127.0.0.1: se vacía el límite de 5 por hora
	delete_transient( 'dip_aviso_ip_' . md5( function_exists( 'dip_ip_cliente' ) ? dip_ip_cliente() : '127.0.0.1' ) );
	delete_transient( 'dip_aviso_envios_' . gmdate( 'YmdH' ) ); // y el tope de 40 por hora de toda la web (hora en curso)
	echo wp_json_encode( true );
	exit;
}
if ( isset( $_GET['pasada'] ) ) { // un confirmado de un Halloween que ya pasó (se apuntó el año anterior)
	$email = strtolower( sanitize_email( wp_unslash( $_GET['pasada'] ) ) );
	$l     = dip_ah_lista();
	$l[ dip_ah_huella( $email ) ] = array( 'email' => $email, 'ano' => dip_ah_ano() - 1, 'alta' => ( new DateTimeImmutable( '-400 days', $zona ) )->format( 'Y-m-d H:i:s' ), 'confirmado' => ( new DateTimeImmutable( '-399 days', $zona ) )->format( 'Y-m-d H:i:s' ) );
	dip_ah_guardar( $l );
	echo wp_json_encode( true );
	exit;
}
if ( isset( $_GET['enviados'] ) ) { // una alta sin confirmar a la que ya se mandaron 2 emails hace 1 hora
	$email = strtolower( sanitize_email( wp_unslash( $_GET['enviados'] ) ) );
	$l     = dip_ah_lista();
	$l[ dip_ah_huella( $email ) ] = array( 'email' => $email, 'ano' => dip_ah_ano_alta(), 'alta' => '2000-01-01 00:00:00', 'confirmado' => '', 'enviado' => gmdate( 'Y-m-d H:i:s', time() - HOUR_IN_SECONDS ), 'envios' => 2 );
	dip_ah_guardar( $l );
	echo wp_json_encode( true );
	exit;
}
if ( isset( $_GET['correo'] ) ) { // el email de confirmación, capturado (no sale): ¿lleva el enlace de baja?
	$email = strtolower( sanitize_email( wp_unslash( $_GET['correo'] ) ) );
	$html  = '';
	add_filter( 'pre_wp_mail', static function ( $nulo, $atts ) use ( &$html ) { $html = (string) $atts['message']; return true; }, 10, 2 );
	dip_ah_enviar_confirmacion( $email );
	$baja = dip_ah_url( dip_ah_huella( $email ), 'baja' );
	// El email pasa por el CSS en línea de WooCommerce, que escribe los & de los enlaces como &amp;
	echo wp_json_encode( array( 'baja' => false !== strpos( html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' ), $baja ), 'quitar' => false !== strpos( $html, 'Quitar el aviso' ) ) );
	exit;
}
if ( isset( $_GET['textos_ya'] ) ) { // «ya estás apuntado» de un registro de este año y de uno cuyo octubre ya empezó
	echo wp_json_encode( array( 'este' => dip_ah_textos( dip_ah_ano_alta() )['ya'], 'empezado' => dip_ah_textos( dip_ah_ano_alta() - 1 )['ya'] ) );
	exit;
}
if ( isset( $_GET['csv'] ) ) { // los emails del CSV de hoy
	echo wp_json_encode( array_column( dip_ah_filas_csv(), 0 ) );
	exit;
}
if ( isset( $_GET['noviembre'] ) ) { // la tarea diaria el 1/11 del año del aviso: los confirmados de ese año se borran
	$ano   = dip_ah_ano();
	$copia = dip_ah_lista();
	$conf  = static fn() => count( array_filter( dip_ah_lista(), static fn( $x ) => ! empty( $x['confirmado'] ) && (int) $x['ano'] === $ano ) );
	$antes = $conf();
	$record = get_option( 'dip_aviso_halloween_recordado', 0 );
	add_filter( 'pre_wp_mail', '__return_true' ); // el recordatorio de septiembre no sale
	dip_ah_tarea_diaria( new DateTimeImmutable( $ano . '-10-31 09:00', $zona ) );
	$el31 = $conf();
	dip_ah_tarea_diaria( new DateTimeImmutable( $ano . '-11-01 09:00', $zona ) );
	$el1 = $conf();
	dip_ah_guardar( $copia ); // se deja como estaba
	update_option( 'dip_aviso_halloween_recordado', $record, false );
	echo wp_json_encode( array( 'el31' => $el31, 'antes' => $antes, 'el1' => $el1, 'ano_alta_oct' => dip_ah_ano_alta( new DateTimeImmutable( $ano . '-10-02 09:00', $zona ) ), 'ano_alta_sep' => dip_ah_ano_alta( new DateTimeImmutable( $ano . '-09-30 09:00', $zona ) ) ) );
	exit;
}
if ( isset( $_GET['email'] ) ) {
	$email  = strtolower( sanitize_email( wp_unslash( $_GET['email'] ) ) );
	$huella = dip_ah_huella( $email );
	$x      = dip_ah_lista()[ $huella ] ?? null;
	echo wp_json_encode( array( 'entrada' => $x, 'confirmar' => dip_ah_url( $huella, 'confirmar' ), 'baja' => dip_ah_url( $huella, 'baja' ) ) );
	exit;
}
if ( isset( $_GET['vieja'] ) ) { // una alta sin confirmar de hace 8 días
	$email = strtolower( sanitize_email( wp_unslash( $_GET['vieja'] ) ) );
	$l     = dip_ah_lista();
	$l[ dip_ah_huella( $email ) ] = array( 'email' => $email, 'ano' => dip_ah_ano(), 'alta' => ( new DateTimeImmutable( '-8 days', $zona ) )->format( 'Y-m-d H:i:s' ), 'confirmado' => '' );
	dip_ah_guardar( $l );
	echo wp_json_encode( true );
	exit;
}
if ( isset( $_GET['tarea'] ) ) { // la tarea diaria el 1/9 del año del aviso, con el email capturado (no sale)
	$ano     = dip_ah_ano();
	$asuntos = array();
	add_filter( 'pre_wp_mail', static function ( $nulo, $atts ) use ( &$asuntos ) { $asuntos[] = $atts['subject']; return true; }, 10, 2 );
	update_option( 'dip_aviso_halloween_recordado', 0, false );
	dip_ah_tarea_diaria( new DateTimeImmutable( $ano . '-08-31 09:00', $zona ) ); // el día antes: nada
	$antes = $asuntos;
	dip_ah_tarea_diaria( new DateTimeImmutable( $ano . '-09-01 09:00', $zona ) );
	dip_ah_tarea_diaria( new DateTimeImmutable( $ano . '-09-02 09:00', $zona ) ); // el día después: no repite
	$recordado = (int) get_option( 'dip_aviso_halloween_recordado', 0 );
	update_option( 'dip_aviso_halloween_recordado', 0, false );
	echo wp_json_encode( array( 'antes' => $antes, 'asuntos' => $asuntos, 'recordado' => $recordado, 'ano' => $ano ) );
	exit;
}
if ( isset( $_GET['limpiar'] ) ) { // solo la limpieza de 7 días, con la fecha de hoy
	dip_ah_tarea_diaria();
	echo wp_json_encode( true );
	exit;
}
echo wp_json_encode( array( 'ano' => dip_ah_ano(), 'ano_alta' => dip_ah_ano_alta(), 'total' => count( dip_ah_lista() ) ) );
