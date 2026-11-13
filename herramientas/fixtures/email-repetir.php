<?php
// Prueba local (herramientas/probar-repetir.mjs): «Repetir el pedido» y la baja de ofertas en el email del día después
// (inc/resenas.php), según la casilla de ofertas del pedido. Pedidos en memoria: no se guarda nada salvo una baja de prueba.
// Solo desde 127.0.0.1. Se copia al plugin como zz-email-repetir.php y se borra al terminar; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
$marca = preg_replace( '/[^a-z0-9]/', '', strtolower( (string) ( $_GET['marca'] ?? 'x' ) ) );
if ( isset( $_GET['comprobar'] ) ) { // ¿de baja de opiniones y de ofertas? (después del POST de un clic a la cabecera)
	$email = sanitize_email( wp_unslash( $_GET['comprobar'] ) );
	echo wp_json_encode( array( 'resenas' => dip_resenas_de_baja( $email ), 'ofertas' => dip_ofertas_de_baja( $email ) ) );
	exit;
}
$casos = array(
	'casilla_sin_marcar' => array( 'si', false ),
	'casilla_marcada'    => array( 'no', false ),
	'antes_de_la_casilla' => array( '', false ),
	'baja_despues'       => array( 'si', true ),
);
$r = array();
foreach ( $casos as $nombre => list( $publicidad, $baja ) ) {
	$email = "email-repetir-{$nombre}-{$marca}@example.com";
	$p     = new WC_Order(); // en memoria
	$p->set_billing_email( $email );
	$p->set_billing_first_name( 'Laura' );
	$p->update_meta_data( '_dip_fecha_entrega', current_time( 'Y-m-d' ) );
	$p->update_meta_data( '_dip_idioma', 'es' );
	if ( '' !== $publicidad ) $p->update_meta_data( '_dip_publicidad', $publicidad );
	if ( $baja ) dip_ofertas_dar_de_baja( dip_ofertas_huella( $email ) );
	$e = dip_resenas_email( $p );
	$r[ $nombre ] = array(
		'repetir'      => false !== strpos( $e['html'], 'Repetir el pedido' ) && false !== strpos( $e['html'], 'repetir=1' ),
		'baja_ofertas' => false !== strpos( $e['html'], 'dip_ofertas_baja=' ),
		'texto'        => false !== strpos( $e['texto'], 'repetir=1' ) && false !== strpos( $e['texto'], 'dip_ofertas_baja=' ),
		'baja_resenas' => false !== strpos( $e['html'], 'dip_resenas_baja=' ),
		// Cabecera List-Unsubscribe: con «Repetir», baja de opiniones y de ofertas a la vez
		'email'        => $email,
		'url_baja'     => $e['url_baja'],
		'baja_todo'    => ! empty( $e['baja_todo'] ) && false !== strpos( $e['url_baja'], 'firma_ofertas=' ),
	);
}
echo wp_json_encode( $r );
