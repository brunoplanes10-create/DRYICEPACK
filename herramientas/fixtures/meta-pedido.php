<?php
// Prueba local (herramientas/probar-repetir.mjs): datos de un pedido para comprobarlos desde fuera. Solo desde 127.0.0.1.
// Se copia al plugin como zz-meta-pedido.php y se borra al terminar la prueba; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
$p = wc_get_order( absint( $_GET['pedido'] ?? 0 ) );
if ( ! $p ) {
	echo 'null';
	exit;
}
echo wp_json_encode( array(
	'repetido_de' => (string) $p->get_meta( '_dip_repetido_de' ),
	'notas'       => array_values( array_map( static fn( $n ) => $n->content, wc_get_order_notes( array( 'order_id' => $p->get_id() ) ) ) ),
	'lineas'      => array_values( array_map( static fn( $l ) => array( $l->get_name(), (int) $l->get_quantity() ), $p->get_items() ) ),
	'cp'          => $p->get_billing_postcode(),
	'direccion'   => $p->get_billing_address_1(),
	'envio'       => array_values( array_map( static fn( $e ) => $e->get_method_id(), $p->get_items( 'shipping' ) ) ),
) );
