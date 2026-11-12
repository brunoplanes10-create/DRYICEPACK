<?php
// Prueba local (bocetos de la tabla de precios de envío por hora y zona): los precios exactos del checkout
// (dip_precio_servicio, con IVA) para cada pack, hora y zona, con un código postal de muestra de cada zona.
// Solo desde 127.0.0.1. Se copia al plugin como zz-tabla-envios.php y se borra al terminar; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
$zonas = array(
	'urbano'     => array( 'cp' => '08301', 'poblacion' => 'Mataró' ),
	'provincial' => array( 'cp' => '08013', 'poblacion' => 'Barcelona' ),
	'regional'   => array( 'cp' => '17001', 'poblacion' => 'Girona' ),
	'peninsular' => array( 'cp' => '28001', 'poblacion' => 'Madrid' ),
);
$iva   = dip_factor_iva();
$packs = array( (float) ( function_exists( 'dipt_pk' ) ? dipt_pk() : 3 ), 10.0, 15.0, 20.0 );
$r     = array( 'iva' => $iva, 'packs' => $packs, 'zonas' => array() );
foreach ( $zonas as $clave => $destino ) {
	foreach ( $packs as $kg ) {
		foreach ( array( '14', '12', '10', 'sabado' ) as $s ) {
			$p = dip_precio_servicio( $s, $kg, 1, $destino );
			$r['zonas'][ $clave ][ (string) $kg ][ $s ] = $p ? array( 'con_iva' => round( $p['neto'] * $iva, 2 ), 'mrw' => $p['mrw'], 'zona' => $p['zona'], 'cerca' => $p['cerca'] ) : null;
		}
	}
}
// Lo que suma estar a más de 25 km de una oficina de MRW (10:00 y sábado), con IVA
$r['lejos'] = array(
	'10'     => round( DIP_SUPLEMENTO_10_LEJOS * $iva, 2 ),
	'sabado' => round( ( DIP_SATURDAY_SURCHARGE_EXCL_TAX - DIP_SABADO_SUPLEMENTO_CERCA ) * $iva, 2 ),
);
echo wp_json_encode( $r );
