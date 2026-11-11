<?php
// Prueba local (herramientas/probar-formularios.mjs y probar-aviso-halloween.mjs): solicitudes recibidas y límite por IP.
// Solo desde 127.0.0.1. Se copia al plugin como zz-formularios.php y se borra al terminar; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
if ( isset( $_GET['limpiar_ip'] ) ) { // las pruebas envían muchas veces desde 127.0.0.1: se vacía el límite de 5 por hora
	$ip = function_exists( 'dip_ip_cliente' ) ? dip_ip_cliente() : '127.0.0.1';
	delete_transient( 'dip_form_ip_' . md5( $ip ) );
	delete_transient( 'dip_aviso_ip_' . md5( $ip ) );
	echo wp_json_encode( true );
	exit;
}
$marca = sanitize_text_field( wp_unslash( $_GET['marca'] ?? '' ) );
$hay   = $marca ? get_posts( array( 'post_type' => 'dip_solicitud', 'post_status' => 'any', 's' => $marca, 'numberposts' => 1 ) ) : array();
echo wp_json_encode( $hay ? array( 'titulo' => $hay[0]->post_title, 'tipo' => get_post_meta( $hay[0]->ID, '_dip_tipo', true ) ) : null );
