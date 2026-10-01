<?php
/**
 * Datos del negocio para las plantillas.
 * La fuente es el plugin dryicepack-tienda (misma tarifa y mismas fechas que cobra el checkout).
 * Si el plugin no está activo, se usan los datos de respaldo (31/08/2026) para que la web no se rompa.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_contacto() {
	if ( function_exists( 'dip_contacto' ) ) return dip_contacto();
	return array(
		'marca' => 'DryIcePack', 'empresa' => 'INDUNOVA IMS S.L.', 'cif' => 'B66800103',
		'telefono' => '936 73 76 41', 'telefono_href' => 'tel:+34936737641',
		'whatsapp' => '686 980 471', 'whatsapp_num' => '34686980471', 'email' => 'info@dryicepack.es',
		'direccion' => 'Camí Ca La Madrona 19 D', 'cp' => '08304', 'localidad' => 'Mataró', 'provincia' => 'Barcelona',
		'horario' => 'L–V 9:00–18:00',
		'mapa' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Camí Ca La Madrona 19 D, 08304 Mataró' ),
	);
}

function dipt_whatsapp_url( $texto = null ) {
	$texto = $texto ?? dipt_t( 'wa_mensaje' );
	return 'https://wa.me/' . dipt_contacto()['whatsapp_num'] . '?text=' . rawurlencode( $texto );
}

/** Variaciones del producto: [ ['id','kg','formato','precio' (sin IVA)], … ] ordenadas por kilos. */
function dipt_variaciones() {
	static $cache = null;
	if ( null !== $cache ) return $cache;
	$respaldo = array(
		array( 'id' => 1662, 'kg' => 3, 'formato' => '3mm', 'precio' => 29.80 ),
		array( 'id' => 1658, 'kg' => 3, 'formato' => '16mm', 'precio' => 29.80 ),
		array( 'id' => 1659, 'kg' => 10, 'formato' => '3mm', 'precio' => 53.30 ),
		array( 'id' => 1655, 'kg' => 10, 'formato' => '16mm', 'precio' => 53.30 ),
		array( 'id' => 1660, 'kg' => 15, 'formato' => '3mm', 'precio' => 75.90 ),
		array( 'id' => 1656, 'kg' => 15, 'formato' => '16mm', 'precio' => 75.90 ),
		array( 'id' => 1661, 'kg' => 20, 'formato' => '3mm', 'precio' => 98.60 ),
		array( 'id' => 1657, 'kg' => 20, 'formato' => '16mm', 'precio' => 98.60 ),
	);
	if ( ! function_exists( 'wc_get_product' ) ) return $cache = $respaldo;
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	$padre = $post ? wc_get_product( $post->ID ) : null;
	if ( ! $padre || ! $padre->is_type( 'variable' ) ) return $cache = $respaldo;
	$lista = array();
	foreach ( $padre->get_children() as $id ) {
		$v = wc_get_product( $id );
		if ( ! $v || ! $v->is_purchasable() ) continue;
		$formato = (string) $v->get_attribute( 'pa_formato' );
		$kg      = function_exists( 'dip_kg_producto' ) ? dip_kg_producto( $v ) : (float) $v->get_weight();
		if ( $kg <= 0 || '' === $formato ) continue;
		$lista[] = array(
			'id'       => (int) $id,
			'padre'    => (int) $padre->get_id(),
			'kg'       => $kg,
			'formato'  => strtolower( str_replace( ' ', '', $formato ) ),
			'peso'     => (string) ( $v->get_attributes()['pa_peso'] ?? '' ),
			'fslug'    => (string) ( $v->get_attributes()['pa_formato'] ?? '' ),
			'precio'   => (float) wc_get_price_excluding_tax( $v ),
			'stock'    => $v->is_in_stock(),
		);
	}
	usort( $lista, static fn( $a, $b ) => $a['kg'] <=> $b['kg'] ?: strcmp( $a['formato'], $b['formato'] ) );
	return $cache = ( $lista ?: $respaldo );
}

/** Packs únicos (por kilos) con su precio sin IVA: [3 => 29.8, 10 => 53.3, …]. */
function dipt_packs() {
	$packs = array();
	foreach ( dipt_variaciones() as $v ) $packs[ (string) $v['kg'] ] = $v['precio'];
	ksort( $packs, SORT_NUMERIC );
	return $packs;
}

function dipt_factor_iva() {
	return function_exists( 'dip_factor_iva' ) ? dip_factor_iva() : 1.21;
}

function dipt_coste_envio( $kg, $cajas = 1 ) {
	if ( function_exists( 'dip_coste_transporte' ) ) return dip_coste_transporte( $kg, $cajas );
	$facturable = ceil( max( 0, (float) $kg ) + max( 1, (int) $cajas ) );
	if ( $kg <= 0 ) return 0.0;
	if ( $facturable <= 2 ) return 8.53;
	if ( $facturable <= 5 ) return 10.32;
	if ( $facturable <= 10 ) return 13.19;
	return round( 13.19 + ( $facturable - 10 ) * 1.12, 2 );
}

/** Precio con IVA de un pack y total puesto en destino con envío (una caja). */
function dipt_precio_pack( $kg ) {
	$packs = dipt_packs();
	$base  = $packs[ (string) $kg ] ?? 0;
	$iva   = dipt_factor_iva();
	return array(
		'sin_iva'   => $base,
		'con_iva'   => round( $base * $iva, 2 ),
		'envio'     => round( dipt_coste_envio( (float) $kg, 1 ) * $iva, 2 ),
		'total'     => round( ( $base + dipt_coste_envio( (float) $kg, 1 ) ) * $iva, 2 ),
		'kilo'      => $kg > 0 ? round( $base * $iva / (float) $kg, 2 ) : 0,
	);
}

/** 81,81 € en castellano y catalán; €81.81 en inglés. */
function dipt_euros( $n, $decimales = 2 ) {
	if ( 'en' === dipt_idioma() ) return '€' . number_format( (float) $n, $decimales, '.', ',' );
	return number_format( (float) $n, $decimales, ',', '.' ) . ' €';
}

/** Primera entrega posible por envío: ['fecha' => 'Y-m-d', 'sale' => 'Y-m-d'] */
function dipt_primera_entrega() {
	if ( function_exists( 'dip_primera_entrega' ) ) return dip_primera_entrega();
	$d = new DateTimeImmutable( 'tomorrow', new DateTimeZone( 'Europe/Madrid' ) );
	while ( in_array( (int) $d->format( 'N' ), array( 1, 6, 7 ), true ) ) $d = $d->modify( '+1 day' );
	return array( 'fecha' => $d->format( 'Y-m-d' ), 'sale' => $d->modify( '-1 day' )->format( 'Y-m-d' ), 'sabado' => false );
}

function dipt_fecha_larga( $ymd, $idioma = null ) {
	$idioma = $idioma ?: dipt_idioma();
	if ( function_exists( 'dip_fecha_larga' ) ) return dip_fecha_larga( $ymd, $idioma );
	return (string) $ymd;
}

/** Configuración para el JavaScript (calculadoras, configurador, reloj del corte). */
function dipt_config_js() {
	return array(
		'idioma'      => dipt_idioma(),
		'variaciones' => array_values( dipt_variaciones() ),
		'tarifa'      => array(
			'hasta2'   => defined( 'DIP_TARIFA_HASTA_2' ) ? DIP_TARIFA_HASTA_2 : 8.53,
			'hasta5'   => defined( 'DIP_TARIFA_HASTA_5' ) ? DIP_TARIFA_HASTA_5 : 10.32,
			'hasta10'  => defined( 'DIP_TARIFA_HASTA_10' ) ? DIP_TARIFA_HASTA_10 : 13.19,
			'kgExtra'  => defined( 'DIP_TARIFA_KG_EXTRA' ) ? DIP_TARIFA_KG_EXTRA : 1.12,
			'embalaje' => defined( 'DIP_PESO_EMBALAJE' ) ? DIP_PESO_EMBALAJE : 1.0,
			'maxKg'    => defined( 'DIP_MAX_KG' ) ? DIP_MAX_KG : 250,
			'sabado'   => defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) ? DIP_SATURDAY_SURCHARGE_EXCL_TAX : 9.70,
		),
		'iva'         => dipt_factor_iva(),
		'corte'       => array( 'hora' => defined( 'DIP_CUTOFF_HOUR' ) ? DIP_CUTOFF_HOUR : 12, 'minuto' => defined( 'DIP_CUTOFF_MINUTE' ) ? DIP_CUTOFF_MINUTE : 0 ),
		'entrega'     => dipt_primera_entrega(),
		'festivos'    => function_exists( 'dip_festivos' ) ? array_keys( dip_festivos() ) : array(),
		'ahora'       => time(),
		'urls'        => array(
			'checkout' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
			'carrito'  => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
			'ajax'     => admin_url( 'admin-ajax.php' ),
		),
	);
}
