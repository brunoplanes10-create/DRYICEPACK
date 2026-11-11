<?php
// Prueba local (herramientas/probar-ofertas.mjs): lo que ha guardado la casilla de ofertas y la lista. Solo desde 127.0.0.1.
// Se copia al plugin como zz-ofertas.php y se borra al terminar la prueba; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
// En el WordPress de prueba se paga con «cheque» y el pedido queda En espera; en la web real, con tarjeta, queda Procesando
if ( isset( $_GET['procesar'] ) ) {
	$p = wc_get_order( absint( $_GET['procesar'] ) );
	if ( $p ) $p->update_status( 'processing', 'Prueba local (probar-ofertas.mjs): como un pago con tarjeta.' );
	echo wp_json_encode( $p ? $p->get_status() : null );
	exit;
}
if ( isset( $_GET['pedido'] ) ) {
	$p = wc_get_order( absint( $_GET['pedido'] ) );
	echo wp_json_encode( $p ? array( 'publicidad' => (string) $p->get_meta( '_dip_publicidad' ), 'email' => $p->get_billing_email() ) : null );
	exit;
}
echo wp_json_encode( array(
	'clientes' => array_values( array_map( static fn( $f ) => array( 'email' => $f['email'], 'baja' => $f['baja'], 'idioma' => $f['idioma'] ), dip_ofertas_clientes() ) ),
	'bajas'    => count( dip_ofertas_bajas() ),
	'desde'    => (string) get_option( 'dip_ofertas_desde', '' ),
) );
