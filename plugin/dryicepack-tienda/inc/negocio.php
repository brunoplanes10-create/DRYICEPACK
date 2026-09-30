<?php
/**
 * Datos y reglas del negocio: una sola fuente para la tienda, el tema y los emails.
 *
 * Tarifa de transporte del 31/08/2026 (igual en toda la península, sin IVA):
 *   hasta 2 kg 8,53 € · hasta 5 kg 10,32 € · hasta 10 kg 13,19 € · cada kg de más 1,12 €
 * Peso facturable = hielo + 1 kg por caja (medido el 31/08/2026), redondeado al alza.
 * Corte a las 12:00 (Madrid). Sale de lunes a viernes y llega al día siguiente por la mañana.
 * Sin entregas en domingo ni lunes. Sábado según zona con suplemento de 9,70 € + IVA.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_MAX_KG' ) )                       define( 'DIP_MAX_KG', 250 );
if ( ! defined( 'DIP_PRODUCT_SLUG' ) )                 define( 'DIP_PRODUCT_SLUG', 'hielo-seco' );
if ( ! defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) )  define( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX', 9.70 );
if ( ! defined( 'DIP_CUTOFF_HOUR' ) )                  define( 'DIP_CUTOFF_HOUR', 12 );
if ( ! defined( 'DIP_CUTOFF_MINUTE' ) )                define( 'DIP_CUTOFF_MINUTE', 0 );
if ( ! defined( 'DIP_TARIFA_HASTA_2' ) )               define( 'DIP_TARIFA_HASTA_2', 8.53 );
if ( ! defined( 'DIP_TARIFA_HASTA_5' ) )               define( 'DIP_TARIFA_HASTA_5', 10.32 );
if ( ! defined( 'DIP_TARIFA_HASTA_10' ) )              define( 'DIP_TARIFA_HASTA_10', 13.19 );
if ( ! defined( 'DIP_TARIFA_KG_EXTRA' ) )              define( 'DIP_TARIFA_KG_EXTRA', 1.12 );
if ( ! defined( 'DIP_PESO_EMBALAJE' ) )                define( 'DIP_PESO_EMBALAJE', 1.0 );

/** Datos de contacto y de empresa (los usa también el tema). */
function dip_contacto() {
	return array(
		'marca'         => 'DryIcePack',
		'empresa'       => 'INDUNOVA IMS S.L.',
		'cif'           => 'B66800103',
		'telefono'      => '936 73 76 41',
		'telefono_href' => 'tel:+34936737641',
		'whatsapp'      => '686 980 471',
		'whatsapp_num'  => '34686980471',
		'email'         => 'info@dryicepack.es',
		'direccion'     => 'Camí Ca La Madrona 19 D',
		'cp'            => '08304',
		'localidad'     => 'Mataró',
		'provincia'     => 'Barcelona',
		'horario'       => 'L–V 9:00–18:00',
		'mapa'          => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'Camí Ca La Madrona 19 D, 08304 Mataró' ),
	);
}

function dip_zona_horaria() {
	return function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'Europe/Madrid' );
}

/* =========================================================
 * Kilos
 * ========================================================= */

/** Kilos de hielo de una unidad de producto (peso del producto o, si falta, el número del atributo o del nombre). */
function dip_kg_producto( $producto ) {
	if ( ! $producto || ! is_object( $producto ) ) return 0.0;
	if ( method_exists( $producto, 'get_weight' ) && (float) $producto->get_weight() > 0 ) return (float) $producto->get_weight();
	$candidatos = array();
	if ( method_exists( $producto, 'get_attribute' ) ) $candidatos[] = $producto->get_attribute( 'pa_peso' );
	if ( method_exists( $producto, 'get_name' ) ) $candidatos[] = $producto->get_name();
	foreach ( $candidatos as $texto ) {
		if ( preg_match( '/(\d+(?:[.,]\d+)?)\s*kg/i', (string) $texto, $m ) ) return (float) str_replace( ',', '.', $m[1] );
	}
	return 0.0;
}

/** Kilos y cajas del carrito (cada unidad de pack es una caja). */
function dip_carrito_kg( $carrito = null ) {
	$carrito = $carrito ?: ( function_exists( 'WC' ) ? WC()->cart : null );
	$kg      = 0.0;
	$cajas   = 0;
	if ( ! $carrito ) return array( 'kg' => 0.0, 'cajas' => 0 );
	foreach ( $carrito->get_cart() as $linea ) {
		$cantidad = max( 0, (int) ( $linea['quantity'] ?? 0 ) );
		$kg_ud    = dip_kg_producto( $linea['data'] ?? null );
		if ( $kg_ud <= 0 || ! $cantidad ) continue;
		$kg    += $kg_ud * $cantidad;
		$cajas += $cantidad;
	}
	return array( 'kg' => $kg, 'cajas' => $cajas );
}

/* =========================================================
 * Transporte
 * ========================================================= */

/** Coste de transporte sin IVA. Misma función para el checkout, la ficha, el carrito y las calculadoras del tema. */
function dip_coste_transporte( $kg_hielo, $bultos = 1 ) {
	$kg_hielo = max( 0, (float) $kg_hielo );
	$bultos   = max( 1, (int) $bultos );
	if ( $kg_hielo <= 0 ) return 0.0;
	$facturable = ceil( $kg_hielo + ( DIP_PESO_EMBALAJE * $bultos ) );
	if ( $facturable <= 2 ) return (float) DIP_TARIFA_HASTA_2;
	if ( $facturable <= 5 ) return (float) DIP_TARIFA_HASTA_5;
	if ( $facturable <= 10 ) return (float) DIP_TARIFA_HASTA_10;
	return round( (float) DIP_TARIFA_HASTA_10 + ( ( $facturable - 10 ) * (float) DIP_TARIFA_KG_EXTRA ), 2 );
}

/** Tipo de IVA general (21 % si WooCommerce no dice otra cosa). */
function dip_factor_iva() {
	if ( class_exists( 'WC_Tax' ) && function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() ) {
		foreach ( (array) WC_Tax::get_rates_for_tax_class( '' ) as $t ) {
			if ( isset( $t->tax_rate_country ) && in_array( $t->tax_rate_country, array( 'ES', '' ), true ) ) {
				return 1 + ( (float) $t->tax_rate / 100 );
			}
		}
	}
	return 1.21;
}

/** Prefijos de código postal fuera de la península. */
function dip_cp_fuera_de_peninsula( $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	if ( strlen( $cp ) < 2 ) return false;
	$fuera = array( '07' => 'Illes Balears', '35' => 'Las Palmas', '38' => 'Santa Cruz de Tenerife', '51' => 'Ceuta', '52' => 'Melilla' );
	return $fuera[ substr( $cp, 0, 2 ) ] ?? false;
}

/** Código de provincia de WooCommerce (ES) a partir del código postal. */
function dip_provincia_por_cp( $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	if ( strlen( $cp ) < 2 ) return '';
	$mapa = array(
		'01' => 'VI', '02' => 'AB', '03' => 'A', '04' => 'AL', '05' => 'AV', '06' => 'BA', '07' => 'PM', '08' => 'B', '09' => 'BU',
		'10' => 'CC', '11' => 'CA', '12' => 'CS', '13' => 'CR', '14' => 'CO', '15' => 'C', '16' => 'CU', '17' => 'GI', '18' => 'GR',
		'19' => 'GU', '20' => 'SS', '21' => 'H', '22' => 'HU', '23' => 'J', '24' => 'LE', '25' => 'L', '26' => 'LO', '27' => 'LU',
		'28' => 'M', '29' => 'MA', '30' => 'MU', '31' => 'NA', '32' => 'OR', '33' => 'O', '34' => 'P', '35' => 'GC', '36' => 'PO',
		'37' => 'SA', '38' => 'TF', '39' => 'S', '40' => 'SG', '41' => 'SE', '42' => 'SO', '43' => 'T', '44' => 'TE', '45' => 'TO',
		'46' => 'V', '47' => 'VA', '48' => 'BI', '49' => 'ZA', '50' => 'Z', '51' => 'CE', '52' => 'ML',
	);
	return $mapa[ substr( $cp, 0, 2 ) ] ?? '';
}

/* =========================================================
 * Días de salida y de entrega
 * ========================================================= */

function dip_festivos() {
	static $lista = null;
	if ( null === $lista ) {
		$texto = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'festivos' ) : '';
		$lista = array_flip( array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', $texto ) ) ) );
	}
	return $lista;
}

function dip_es_festivo( DateTimeInterface $dia ) {
	return isset( dip_festivos()[ $dia->format( 'Y-m-d' ) ] );
}

/** ¿Sale pedido ese día? De lunes a viernes y no festivo. */
function dip_es_dia_de_salida( DateTimeInterface $dia ) {
	return (int) $dia->format( 'N' ) <= 5 && ! dip_es_festivo( $dia );
}

function dip_antes_del_corte( ?DateTimeImmutable $ahora = null ) {
	$ahora = $ahora ?: new DateTimeImmutable( 'now', dip_zona_horaria() );
	return ( (int) $ahora->format( 'G' ) * 60 + (int) $ahora->format( 'i' ) ) < ( DIP_CUTOFF_HOUR * 60 + DIP_CUTOFF_MINUTE );
}

/**
 * Fechas que se pueden elegir.
 * - Envío: sale un día de salida y llega al día siguiente (martes a sábado). El sábado lleva suplemento.
 * - Recogida: días de salida (lunes a viernes no festivos) desde hoy si es antes del corte.
 *
 * @return array[] [ ['fecha' => 'Y-m-d', 'sabado' => bool, 'sale' => 'Y-m-d'], … ]
 */
function dip_fechas_disponibles( $metodo = 'envio', $cuantas = 8, ?DateTimeImmutable $ahora = null ) {
	$ahora  = $ahora ?: new DateTimeImmutable( 'now', dip_zona_horaria() );
	$dia    = $ahora->setTime( 0, 0 );
	if ( ! dip_es_dia_de_salida( $dia ) || ! dip_antes_del_corte( $ahora ) ) $dia = $dia->modify( '+1 day' );
	$fechas = array();
	for ( $i = 0; $i < 60 && count( $fechas ) < $cuantas; $i++, $dia = $dia->modify( '+1 day' ) ) {
		if ( ! dip_es_dia_de_salida( $dia ) ) continue;
		if ( 'recogida' === $metodo ) {
			$fechas[] = array( 'fecha' => $dia->format( 'Y-m-d' ), 'sabado' => false, 'sale' => $dia->format( 'Y-m-d' ) );
			continue;
		}
		$llega = $dia->modify( '+1 day' );
		if ( dip_es_festivo( $llega ) ) continue;
		$fechas[] = array(
			'fecha'  => $llega->format( 'Y-m-d' ),
			'sabado' => 6 === (int) $llega->format( 'N' ),
			'sale'   => $dia->format( 'Y-m-d' ),
		);
	}
	return $fechas;
}

function dip_fecha_es_valida( $ymd, $metodo = 'envio' ) {
	foreach ( dip_fechas_disponibles( $metodo, 40 ) as $f ) {
		if ( $f['fecha'] === $ymd ) return true;
	}
	return false;
}

/** Primera entrega posible por envío (sin contar el sábado, que es opcional y según zona). */
function dip_primera_entrega( ?DateTimeImmutable $ahora = null ) {
	foreach ( dip_fechas_disponibles( 'envio', 10, $ahora ) as $f ) {
		if ( ! $f['sabado'] ) return $f;
	}
	return null;
}

/** "jueves 2 de octubre" en el idioma pedido (es, ca, en). */
function dip_fecha_larga( $ymd, $idioma = 'es' ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d ) return (string) $ymd;
	$n = (int) $d->format( 'N' );
	$j = (int) $d->format( 'j' );
	$m = (int) $d->format( 'n' );
	if ( 'ca' === $idioma ) {
		$dias  = array( 1 => 'dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres', 'dissabte', 'diumenge' );
		$meses = array( 1 => 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre' );
		$de    = in_array( $m, array( 4, 8, 10 ), true ) ? "d'" : 'de ';
		return $dias[ $n ] . ' ' . $j . ' ' . $de . $meses[ $m ];
	}
	if ( 'en' === $idioma ) {
		return $d->format( 'l j F' );
	}
	$dias  = array( 1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo' );
	$meses = array( 1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' );
	return $dias[ $n ] . ' ' . $j . ' de ' . $meses[ $m ];
}

/** [ 'jue', '2', 'oct' ] para las casillas de fecha. */
function dip_fecha_corta( $ymd, $idioma = 'es' ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d ) return array( '', '', '' );
	$dias  = array(
		'es' => array( 1 => 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom' ),
		'ca' => array( 1 => 'dl.', 'dt.', 'dc.', 'dj.', 'dv.', 'ds.', 'dg.' ),
		'en' => array( 1 => 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ),
	);
	$meses = array(
		'es' => array( 1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic' ),
		'ca' => array( 1 => 'gen.', 'febr.', 'març', 'abr.', 'maig', 'juny', 'jul.', 'ag.', 'set.', 'oct.', 'nov.', 'des.' ),
		'en' => array( 1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ),
	);
	$i = isset( $dias[ $idioma ] ) ? $idioma : 'es';
	return array( $dias[ $i ][ (int) $d->format( 'N' ) ], $d->format( 'j' ), $meses[ $i ][ (int) $d->format( 'n' ) ] );
}

/** Precio formateado a la española sin depender de WooCommerce: 81,81 € */
function dip_euros( $importe ) {
	return number_format( (float) $importe, 2, ',', '.' ) . ' €';
}
