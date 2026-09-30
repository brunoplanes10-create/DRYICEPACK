<?php
/**
 * Datos reales del negocio en un solo sitio.
 *
 * - Contacto y nave.
 * - Productos: se leen de WooCommerce (producto variable "hielo-seco"); si WooCommerce no está, se usan los de respaldo.
 * - Envío: si el plugin dryicepack-tienda (o el tema hijo antiguo) define dip_coste_transporte(), se usa esa misma función
 *   que cobra el checkout; si no, la tarifa de respaldo (31/08/2026). Así la calculadora nunca dice otra cosa que el checkout.
 * - Entrega: corte a las 12:00 (hora de Madrid), se envía de lunes a viernes, se entrega al día siguiente por la mañana,
 *   sin entregas en domingo ni lunes; el sábado solo según zona y con suplemento.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_contacto() {
	return array(
		'telefono'       => '936 73 76 41',
		'telefono_href'  => 'tel:+34936737641',
		'email'          => 'info@dryicepack.es',
		'whatsapp_href'  => 'https://wa.me/34686980471?text=' . rawurlencode( 'Hola, necesito hielo seco. Mi uso es:' ),
		'horario'        => 'L–V · 9:00–18:00',
		'direccion'      => 'Camí Ca La Madrona 19 D',
		'cp'             => '08304',
		'localidad'      => 'Mataró',
		'provincia'      => 'Barcelona',
		'empresa'        => 'INDUNOVA IMS S.L.',
		'cif'            => 'B66800103',
	);
}

/* ---------- Productos ---------- */
function dipt_producto_url() {
	return home_url( '/producto/hielo-seco/' );
}

/**
 * Variaciones del producto: [ ['id'=>1662,'kg'=>3,'formato'=>'3mm','precio'=>29.80], … ] (precio sin IVA).
 */
function dipt_variaciones() {
	static $cache = null;
	if ( null !== $cache ) return $cache;

	$respaldo = array(
		array( 'id' => 1662, 'kg' => 3,  'formato' => '3mm',  'precio' => 29.80 ),
		array( 'id' => 1658, 'kg' => 3,  'formato' => '16mm', 'precio' => 29.80 ),
		array( 'id' => 1659, 'kg' => 10, 'formato' => '3mm',  'precio' => 53.30 ),
		array( 'id' => 1655, 'kg' => 10, 'formato' => '16mm', 'precio' => 53.30 ),
		array( 'id' => 1660, 'kg' => 15, 'formato' => '3mm',  'precio' => 68.40 ),
		array( 'id' => 1656, 'kg' => 15, 'formato' => '16mm', 'precio' => 68.40 ),
		array( 'id' => 1661, 'kg' => 20, 'formato' => '3mm',  'precio' => 98.60 ),
		array( 'id' => 1657, 'kg' => 20, 'formato' => '16mm', 'precio' => 98.60 ),
	);

	if ( ! function_exists( 'wc_get_product' ) ) return $cache = $respaldo;

	$padre = null;
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	if ( $post ) $padre = wc_get_product( $post->ID );
	if ( ! $padre || ! $padre->is_type( 'variable' ) ) return $cache = $respaldo;

	$lista = array();
	foreach ( $padre->get_children() as $id ) {
		$v = wc_get_product( $id );
		if ( ! $v || ! $v->is_purchasable() ) continue;
		$atr     = $v->get_attributes();
		$peso    = isset( $atr['pa_peso'] ) ? $atr['pa_peso'] : '';
		$formato = isset( $atr['pa_formato'] ) ? $atr['pa_formato'] : '';
		$kg      = (float) $v->get_weight();
		if ( $kg <= 0 && preg_match( '/(\d+(?:[.,]\d+)?)/', (string) $peso, $m ) ) $kg = (float) str_replace( ',', '.', $m[1] );
		if ( $kg <= 0 || '' === $formato ) continue;
		$lista[] = array(
			'id'      => (int) $id,
			'kg'      => $kg,
			'formato' => (string) $formato,
			'peso'    => (string) $peso,
			'precio'  => (float) wc_get_price_excluding_tax( $v ),
			'stock'   => $v->is_in_stock(),
		);
	}
	usort( $lista, static fn( $a, $b ) => $a['kg'] <=> $b['kg'] );
	return $cache = ( $lista ? $lista : $respaldo );
}

/* ---------- IVA ---------- */
function dipt_factor_iva() {
	if ( class_exists( 'WC_Tax' ) && function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() ) {
		$tasas = WC_Tax::get_rates_for_tax_class( '' );
		foreach ( (array) $tasas as $t ) {
			if ( isset( $t->tax_rate_country ) && in_array( $t->tax_rate_country, array( 'ES', '' ), true ) ) {
				return 1 + ( (float) $t->tax_rate / 100 );
			}
		}
	}
	return 1.21;
}

/* ---------- Envío ---------- */
function dipt_tarifa_envio() {
	return array(
		'hasta2'   => defined( 'DIP_TARIFA_HASTA_2' ) ? (float) DIP_TARIFA_HASTA_2 : 8.53,
		'hasta5'   => defined( 'DIP_TARIFA_HASTA_5' ) ? (float) DIP_TARIFA_HASTA_5 : 10.32,
		'hasta10'  => defined( 'DIP_TARIFA_HASTA_10' ) ? (float) DIP_TARIFA_HASTA_10 : 13.19,
		'kgExtra'  => defined( 'DIP_TARIFA_KG_EXTRA' ) ? (float) DIP_TARIFA_KG_EXTRA : 1.12,
		'embalaje' => defined( 'DIP_PESO_EMBALAJE' ) ? (float) DIP_PESO_EMBALAJE : 1.0,
		'maxKg'    => defined( 'DIP_MAX_KG' ) ? (int) DIP_MAX_KG : 250,
		'sabado'   => defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) ? (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX : 9.70,
	);
}

/** Coste de envío sin IVA para $kg de hielo en $cajas cajas (misma fórmula que el checkout). */
function dipt_coste_envio( $kg, $cajas = 1 ) {
	if ( function_exists( 'dip_coste_transporte' ) ) return (float) dip_coste_transporte( $kg, $cajas );
	$t          = dipt_tarifa_envio();
	$kg         = max( 0, (float) $kg );
	if ( $kg <= 0 ) return 0.0;
	$facturable = ceil( $kg + $t['embalaje'] * max( 1, (int) $cajas ) );
	if ( $facturable <= 2 ) return $t['hasta2'];
	if ( $facturable <= 5 ) return $t['hasta5'];
	if ( $facturable <= 10 ) return $t['hasta10'];
	return $t['hasta10'] + ( $facturable - 10 ) * $t['kgExtra'];
}

/* ---------- Entrega estimada ---------- */
function dipt_zona() {
	return new DateTimeZone( 'Europe/Madrid' );
}

/**
 * Calcula el siguiente corte y la entrega estimada.
 * Devuelve: ['sale' => DateTime (día de salida), 'corte_hoy' => bool, 'corte' => DateTime|null (hoy 12:00 si aplica),
 *            'entrega' => DateTime, 'sabado_opcional' => bool]
 */
function dipt_entrega( ?DateTimeImmutable $ahora = null ) {
	$ahora = $ahora ?: new DateTimeImmutable( 'now', dipt_zona() );
	$dia   = (int) $ahora->format( 'N' ); // 1 lunes … 7 domingo
	$antes = ( (int) $ahora->format( 'G' ) * 60 + (int) $ahora->format( 'i' ) ) < 12 * 60;

	// Día de salida: hoy si es laborable y antes de las 12:00; si no, el siguiente laborable (lunes a viernes)
	$sale = ( $dia <= 5 && $antes ) ? $ahora->setTime( 0, 0 ) : $ahora->setTime( 0, 0 )->modify( '+1 day' );
	while ( (int) $sale->format( 'N' ) > 5 ) $sale = $sale->modify( '+1 day' );

	$entrega  = $sale->modify( '+1 day' );
	$sabado   = false;
	if ( 6 === (int) $entrega->format( 'N' ) ) { // sale el viernes: sábado según zona o martes
		$sabado  = true;
		$entrega = $entrega->modify( '+3 days' );
	}
	$corte_hoy = ( $sale->format( 'Y-m-d' ) === $ahora->format( 'Y-m-d' ) );

	return array(
		'sale'            => $sale,
		'corte_hoy'       => $corte_hoy,
		'corte'           => $corte_hoy ? $ahora->setTime( 12, 0 ) : null,
		'entrega'         => $entrega,
		'sabado_opcional' => $sabado,
	);
}

/** "mañana, jueves 1 de octubre" · "el martes 6 de octubre" */
function dipt_fecha_texto( DateTimeImmutable $fecha, ?DateTimeImmutable $ahora = null ) {
	$ahora  = $ahora ?: new DateTimeImmutable( 'now', dipt_zona() );
	$dias   = array( 1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo' );
	$meses  = array( 1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' );
	$base   = $dias[ (int) $fecha->format( 'N' ) ] . ' ' . (int) $fecha->format( 'j' ) . ' de ' . $meses[ (int) $fecha->format( 'n' ) ];
	$manana = $ahora->setTime( 0, 0 )->modify( '+1 day' )->format( 'Y-m-d' );
	return ( $fecha->format( 'Y-m-d' ) === $manana ) ? 'mañana, ' . $base : 'el ' . $base;
}

/** Configuración que necesita el JS (calculadora, reloj de corte, planificador). */
function dipt_config_js() {
	$t = dipt_tarifa_envio();
	return array(
		'variaciones' => array_values( dipt_variaciones() ),
		'tarifa'      => $t,
		'iva'         => dipt_factor_iva(),
		'corte'       => array( 'hora' => 12, 'minuto' => 0, 'zona' => 'Europe/Madrid' ),
		'urls'        => array(
			'producto'   => dipt_producto_url(),
			'carrito'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/carrito/' ),
			'checkout'   => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/finalizar-compra/' ),
			'suministro' => home_url( '/programar-suministro-de-hielo-seco/' ),
			'ajax'       => admin_url( 'admin-ajax.php' ),
		),
		'woo'         => class_exists( 'WooCommerce' ),
	);
}
