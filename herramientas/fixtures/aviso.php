<?php
// Prueba local (herramientas/probar-aviso-halloween.mjs): la lista del «Avísame» de Halloween. Solo desde 127.0.0.1.
// Se copia al plugin como zz-aviso.php y se borra al terminar la prueba; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
$zona = wp_timezone();
if ( isset( $_GET['limpiar_ip'] ) ) { // las pruebas envían muchas veces desde 127.0.0.1: se vacía el límite de 5 por hora
	delete_transient( 'dip_aviso_ip_' . md5( function_exists( 'dip_ip_cliente' ) ? dip_ip_cliente() : '127.0.0.1' ) );
	echo wp_json_encode( true );
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
echo wp_json_encode( array( 'ano' => dip_ah_ano(), 'total' => count( dip_ah_lista() ) ) );
