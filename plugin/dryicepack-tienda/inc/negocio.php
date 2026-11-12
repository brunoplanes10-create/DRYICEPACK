<?php
/**
 * Datos y reglas del negocio: una sola fuente para la tienda, el tema y los emails.
 *
 * Tarifa de transporte del 31/08/2026 (igual en toda la península, sin IVA):
 *   hasta 2 kg 8,53 € · hasta 5 kg 10,32 € · hasta 10 kg 13,19 € · cada kg de más 1,12 €
 * Peso facturable = hielo + 1 kg por caja (medido el 31/08/2026), redondeado al alza.
 * Corte a las 12:00 (Madrid). Sale de lunes a viernes y llega al día siguiente antes de las 14:00 (sábado, antes de las 12:00).
 * Sin entregas en domingo ni lunes. Sábado según zona, antes de las 13:00, con la tarifa del Ecommerce de MRW.
 * Horas a elegir entre semana (11/11/2026): antes de las 14:00 (de serie), de las 12:00 o de las 10:00, a precio de MRW.
 * Festivos: la lista manual de Ajustes (Mataró, Cataluña, España) y, por encima, los de MRW (inc/festivos-mrw.php):
 * sin salida si cierra MRW Mataró y sin entrega si cierra MRW en el destino.
 * Recogida en la nave de lunes a sábado, también en festivos (la confirma la empresa), solo en efectivo y a precio redondo
 * con IVA incluido (inc/precios-recogida.php). El sábado, +10 € IVA incluido (sin suplemento el 31/10/2026).
 * Más de 150 kg fuera de la provincia de Barcelona: sin envío online, se prepara a medida (el proveedor solo sirve
 * más de 150 kg dentro de la provincia).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_MAX_KG' ) )                       define( 'DIP_MAX_KG', 250 );
if ( ! defined( 'DIP_PRODUCT_SLUG' ) )                 define( 'DIP_PRODUCT_SLUG', 'hielo-seco' );
if ( ! defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) )  define( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX', 9.70 );
if ( ! defined( 'DIP_CUTOFF_HOUR' ) )                  define( 'DIP_CUTOFF_HOUR', 12 );
if ( ! defined( 'DIP_CUTOFF_MINUTE' ) )                define( 'DIP_CUTOFF_MINUTE', 0 );
// MRW entrega antes de las 14:00 entre semana y antes de las 12:00 el sábado (09/11/2026). Una sola pareja para toda la
// tienda: «Envíos de hoy», «Mi pedido» y los avisos al cliente (inc/envios-hoy.php, inc/mi-pedido.php, inc/avisos.php).
if ( ! defined( 'DIP_HORA_ENTREGA' ) )                 define( 'DIP_HORA_ENTREGA', '14:00' );
// El sábado va con el Ecommerce de MRW: en teoría antes de las 12:00, como muy tarde a las 13:00 → se promete 13:00 (11/11/2026)
if ( ! defined( 'DIP_HORA_ENTREGA_SABADO' ) )          define( 'DIP_HORA_ENTREGA_SABADO', '13:00' );
if ( ! defined( 'DIP_TARIFA_HASTA_2' ) )               define( 'DIP_TARIFA_HASTA_2', 8.53 );
if ( ! defined( 'DIP_TARIFA_HASTA_5' ) )               define( 'DIP_TARIFA_HASTA_5', 10.32 );
if ( ! defined( 'DIP_TARIFA_HASTA_10' ) )              define( 'DIP_TARIFA_HASTA_10', 13.19 );
if ( ! defined( 'DIP_TARIFA_KG_EXTRA' ) )              define( 'DIP_TARIFA_KG_EXTRA', 1.12 );
if ( ! defined( 'DIP_PESO_EMBALAJE' ) )                define( 'DIP_PESO_EMBALAJE', 1.0 );
if ( ! defined( 'DIP_KG_A_MEDIDA' ) )                  define( 'DIP_KG_A_MEDIDA', 150 );
if ( ! defined( 'DIP_DIAS_RESERVA' ) )                 define( 'DIP_DIAS_RESERVA', 90 ); // se puede pedir para cualquier día de los próximos 90
// Recogida en la nave (03/10/2026): precio de la web × IVA redondeado a la baja a múltiplos de 5 €, y sábado +10 € IVA incluido
if ( ! defined( 'DIP_RECOGIDA_REDONDEO' ) )            define( 'DIP_RECOGIDA_REDONDEO', 5 );
if ( ! defined( 'DIP_RECOGIDA_SABADO_CON_IVA' ) )      define( 'DIP_RECOGIDA_SABADO_CON_IVA', 10.00 );

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

/**
 * Zona horaria del negocio: siempre Europe/Madrid (el corte de las 12:00 es hora de Mataró), aunque en
 * Ajustes → Generales de WordPress haya otra (UTC o "UTC+2" fijo). Es la misma que usa el JavaScript del tema.
 */
function dip_zona_horaria() {
	static $zona = null;
	return $zona ?: ( $zona = new DateTimeZone( 'Europe/Madrid' ) );
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

/* =========================================================
 * Servicios de entrega (11/11/2026): el cliente elige la hora y paga lo que cobra MRW, sin margen
 * Tarifas de la comercial de MRW Mataró, sin IVA (estrategia/MRW-TARIFAS.md):
 * - Antes de las 14:00 (Urgente 14 Expedición, 0115): el de serie y el más barato, igual en toda la península
 *   (dip_coste_transporte).
 * - Antes de las 12:00 (Urgente 12 Expedición, 0105) y antes de las 10:00 (Urgente 10 Expedición, 0015): por zona;
 *   el de las 10:00 lleva 15,30 € más si se entrega a más de 25 km de la oficina de MRW.
 * - Sábado antes de las 13:00 (Ecommerce, 0800, sale el viernes): por zona y tramos de peso, +2 € por caja desde la 3.ª,
 *   y el suplemento de sábado: 5,45 €, o 9,70 € a más de 25 km de la oficina de MRW. Como mucho 80 kg por envío.
 * «A más de 25 km de la oficina de MRW»: distancia medida de cada código postal a la oficina de MRW más cercana
 * (datos/mrw-cerca.json: posición exacta de las 351 oficinas de mrw.es y de cada población de GeoNames; cerca = todas sus
 * poblaciones a 18 km o menos en línea recta ≈ 25 km por carretera). En la duda, lejos (decidido el 11/11/2026).
 * Zonas desde Mataró (confirmadas con la comercial): urbano = Mataró (08301–08304); provincial = provincia de
 * Barcelona; regional = Girona, Lleida y Tarragona; peninsular = el resto.
 * ========================================================= */

if ( ! defined( 'DIP_SABADO_SUPLEMENTO_CERCA' ) ) define( 'DIP_SABADO_SUPLEMENTO_CERCA', 5.45 );
// El de lejos es DIP_SATURDAY_SURCHARGE_EXCL_TAX (9,70 €): el mismo importe de siempre
if ( ! defined( 'DIP_SABADO_BULTO_EXTRA' ) )      define( 'DIP_SABADO_BULTO_EXTRA', 2.00 ); // por caja, desde la 3.ª
if ( ! defined( 'DIP_SABADO_MAX_KG' ) )           define( 'DIP_SABADO_MAX_KG', 80 );        // kg facturables por envío
if ( ! defined( 'DIP_SUPLEMENTO_10_LEJOS' ) )     define( 'DIP_SUPLEMENTO_10_LEJOS', 15.30 );

/** Servicios por mensajería: hora de entrega y servicio de MRW (el código se puede cambiar en Ajustes → MRW). */
function dip_servicios_envio() {
	return array(
		'14'     => array( 'hora' => DIP_HORA_ENTREGA, 'codigo' => '0115', 'mrw' => 'Urgente 14 Expedición' ),
		'12'     => array( 'hora' => '12:00', 'codigo' => '0105', 'mrw' => 'Urgente 12 Expedición' ),
		'10'     => array( 'hora' => '10:00', 'codigo' => '0015', 'mrw' => 'Urgente 10 Expedición' ),
		'sabado' => array( 'hora' => DIP_HORA_ENTREGA_SABADO, 'codigo' => '0800', 'mrw' => 'Ecommerce (entrega en sábado)' ),
	);
}

/** Servicio válido para un día de entrega: el sábado siempre 'sabado'; entre semana '14' (de serie), '12' o '10'. */
function dip_servicio_para( $servicio, $ymd ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( $d && 6 === (int) $d->format( 'N' ) ) return 'sabado';
	return in_array( (string) $servicio, array( '14', '12', '10' ), true ) ? (string) $servicio : '14';
}

/** Hora límite de entrega de un servicio ('14:00', '12:00', '10:00' o la del sábado). */
function dip_hora_servicio( $servicio ) {
	return dip_servicios_envio()[ (string) $servicio ]['hora'] ?? DIP_HORA_ENTREGA;
}

/** Zona de MRW desde Mataró: 'urbano' | 'provincial' | 'regional' | 'peninsular'. */
function dip_zona_envio( $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	if ( 5 !== strlen( $cp ) ) return 'peninsular';
	if ( in_array( $cp, array( '08301', '08302', '08303', '08304' ), true ) ) return 'urbano';
	$p = substr( $cp, 0, 2 );
	if ( '08' === $p ) return 'provincial';
	if ( in_array( $p, array( '17', '25', '43' ), true ) ) return 'regional';
	return 'peninsular';
}

/**
 * Códigos postales cerca y lejos de una oficina de MRW (datos/mrw-cerca.json, herramientas/datos-mrw-cerca.mjs):
 * cerca = todas las poblaciones del código (GeoNames) a 18 km o menos en línea recta de una oficina de MRW (posición
 * exacta de mrw.es) ≈ 25 km por carretera. ['cerca' => [cp => i], 'lejos' => [cp => i], 'poblaciones' => [PROV => [nombres]]].
 */
function dip_mrw_cerca_datos() {
	static $d = null;
	if ( null !== $d ) return $d;
	$d       = array( 'ok' => false, 'cerca' => array(), 'lejos' => array(), 'poblaciones' => array() );
	$archivo = defined( 'DIP_TIENDA_DIR' ) ? DIP_TIENDA_DIR . 'datos/mrw-cerca.json' : '';
	$j       = $archivo && file_exists( $archivo ) ? json_decode( (string) file_get_contents( $archivo ), true ) : null;
	if ( ! is_array( $j ) || empty( $j['cerca'] ) ) return $d;
	$d['ok']    = true;
	$d['cerca'] = array_flip( str_split( (string) $j['cerca'], 5 ) );
	$d['lejos'] = array_flip( str_split( (string) ( $j['lejos'] ?? '' ), 5 ) );
	foreach ( (array) ( $j['poblaciones'] ?? array() ) as $p => $nombres ) {
		$d['poblaciones'][ str_replace( ' ', '', dip_mrw_clave_provincia( $p ) ) ] = (array) $nombres;
	}
	return $d;
}

/**
 * ¿El destino está a menos de 25 km de la oficina de MRW que entrega? (sábado 5,45 € en vez de 9,70 €; 10:00 sin +15,30 €)
 * - Código postal en la lista «cerca» (distancia medida, con margen): sí. En la lista «lejos»: no.
 * - Código que la lista no conoce (nuevo): sí solo si la población del cliente tiene oficina de MRW; si no, no.
 * En la duda, «lejos»: se cobra el suplemento de lejos y no se pierde dinero (decidido el 11/11/2026).
 */
function dip_cerca_oficina_mrw( $destino ) {
	$cp        = preg_replace( '/\D/', '', (string) ( $destino['cp'] ?? '' ) );
	$poblacion = trim( (string) ( $destino['poblacion'] ?? '' ) );
	if ( 'urbano' === dip_zona_envio( $cp ) ) return true; // Mataró: la oficina de MRW está en la ciudad
	if ( 5 !== strlen( $cp ) || ! function_exists( 'dip_mrw_provincia_por_cp' ) ) return false;
	$d = dip_mrw_cerca_datos();
	if ( $d['ok'] ) {
		if ( isset( $d['cerca'][ $cp ] ) ) return true;
		if ( isset( $d['lejos'][ $cp ] ) ) return false;
		$oficinas = (array) ( $d['poblaciones'][ str_replace( ' ', '', dip_mrw_clave_provincia( dip_mrw_provincia_por_cp( $cp ) ) ) ] ?? array() );
	} else {
		// Sin datos (archivo que falta): las poblaciones con oficina del calendario de festivos de MRW
		$oficinas = function_exists( 'dip_mrw_oficinas' ) ? (array) ( dip_mrw_oficinas()[ dip_mrw_clave_provincia( dip_mrw_provincia_por_cp( $cp ) ) ]['nombres'] ?? array() ) : array();
	}
	$nombres = '' !== $poblacion ? dip_mrw_nombres( $poblacion ) : array();
	if ( ! $nombres ) return false;
	foreach ( $oficinas as $oficina ) {
		if ( function_exists( 'dip_mrw_es_interna' ) && dip_mrw_es_interna( $oficina ) ) continue;
		if ( dip_mrw_mismo_sitio( $nombres, dip_mrw_nombres( $oficina ) ) ) return true;
	}
	return false;
}

/** Tarifas de MRW por zona (sin IVA). Urgentes: [hasta 5 kg, hasta 10 kg, kg adicional]. Sábado: tramos y «cada 5 kg más». */
function dip_tarifas_mrw() {
	$sabado_cerca = array( 6.22, 7.54, 10.33, 12.44, 14.19, 16.63, 19.71, 21.68, 23.87, 2.90 ); // urbano = provincial
	return array(
		'12'     => array(
			'urbano'     => array( 8.30, 16.60, 1.66 ),
			'provincial' => array( 11.05, 22.10, 2.21 ),
			'regional'   => array( 16.09, 32.18, 3.22 ),
			'peninsular' => array( 21.06, 42.12, 4.21 ),
		),
		'10'     => array(
			'urbano'     => array( 8.92, 17.84, 1.78 ),
			'provincial' => array( 12.26, 24.52, 2.45 ),
			'regional'   => array( 24.28, 48.56, 4.86 ),
			'peninsular' => array( 26.53, 53.06, 5.31 ),
		),
		'sabado' => array(
			'tramos'     => array( 2, 5, 10, 15, 20, 25, 30, 35, 40 ),
			'urbano'     => $sabado_cerca,
			'provincial' => $sabado_cerca,
			'regional'   => array( 7.20, 9.37, 11.57, 13.80, 16.02, 19.87, 24.11, 26.53, 29.15, 4.06 ),
			'peninsular' => array( 8.95, 11.71, 14.48, 17.25, 20.02, 24.86, 30.14, 33.14, 36.45, 5.11 ),
		),
	);
}

/** Peso facturable: hielo + 1 kg por caja, redondeado al alza (el mismo de la tarifa y de MRW). */
function dip_peso_facturable( $kg_hielo, $bultos = 1 ) {
	$kg_hielo = max( 0, (float) $kg_hielo );
	return $kg_hielo > 0 ? (int) ceil( $kg_hielo + ( DIP_PESO_EMBALAJE * max( 1, (int) $bultos ) ) ) : 0;
}

/** Tarifa de MRW de un servicio concreto, sin IVA (sin elegir el más barato). null: sábado de más de 80 kg. */
function dip_tarifa_mrw( $servicio, $peso, $bultos, $zona, $cerca ) {
	if ( $peso <= 0 ) return 0.0;
	if ( '14' === $servicio ) {
		if ( $peso <= 2 ) return (float) DIP_TARIFA_HASTA_2;
		if ( $peso <= 5 ) return (float) DIP_TARIFA_HASTA_5;
		if ( $peso <= 10 ) return (float) DIP_TARIFA_HASTA_10;
		return round( (float) DIP_TARIFA_HASTA_10 + ( $peso - 10 ) * (float) DIP_TARIFA_KG_EXTRA, 2 );
	}
	$t = dip_tarifas_mrw()[ $servicio ];
	if ( 'sabado' === $servicio ) {
		if ( $peso > DIP_SABADO_MAX_KG ) return null;
		$fila = $t[ $zona ];
		$neto = null;
		foreach ( $t['tramos'] as $i => $hasta ) {
			if ( $peso <= $hasta ) {
				$neto = $fila[ $i ];
				break;
			}
		}
		if ( null === $neto ) $neto = $fila[8] + ceil( ( $peso - 40 ) / 5 ) * $fila[9]; // más de 40 kg: cada 5 kg (o parte)
		if ( $bultos >= 3 ) $neto += ( $bultos - 2 ) * DIP_SABADO_BULTO_EXTRA;
		$neto += $cerca ? DIP_SABADO_SUPLEMENTO_CERCA : (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX;
		return round( $neto, 2 );
	}
	list( $hasta5, $hasta10, $extra ) = $t[ $zona ];
	$neto = $peso <= 5 ? $hasta5 : ( $peso <= 10 ? $hasta10 : $hasta10 + ( $peso - 10 ) * $extra );
	if ( '10' === $servicio && ! $cerca ) $neto += DIP_SUPLEMENTO_10_LEJOS;
	return round( $neto, 2 );
}

/**
 * Precio del transporte sin IVA de la hora elegida. null si no se puede (sábado de más de 80 kg).
 * Cada hora cuesta lo que cuesta el servicio de MRW más barato que llega a esa hora (sin margen, 11/11/2026): en Mataró
 * y la provincia de Barcelona, con poco peso, el Urgente 12 es más barato que el 14 y entonces «antes de las 14:00» va
 * por el Urgente 12, a su precio. Así la hora de serie (14:00) nunca es más cara que las otras.
 * 'mrw' es el servicio con el que se crea el envío en MRW ('14', '12', '10' o 'sabado').
 * $destino: ['cp' => …, 'poblacion' => …] (sin él: península y lejos de una oficina, el precio más alto).
 *
 * @return array{neto: float, servicio: string, mrw: string, zona: string, peso: int, cerca: bool}|null
 */
function dip_precio_servicio( $servicio, $kg_hielo, $bultos = 1, $destino = null ) {
	$servicio = in_array( (string) $servicio, array( '14', '12', '10', 'sabado' ), true ) ? (string) $servicio : '14';
	$bultos   = max( 1, (int) $bultos );
	$peso     = dip_peso_facturable( $kg_hielo, $bultos );
	$destino  = is_array( $destino ) ? $destino : array();
	$zona     = dip_zona_envio( $destino['cp'] ?? '' );
	$cerca    = dip_cerca_oficina_mrw( $destino );
	$r        = array( 'neto' => 0.0, 'servicio' => $servicio, 'mrw' => $servicio, 'zona' => $zona, 'peso' => $peso, 'cerca' => $cerca );
	if ( $peso <= 0 ) return $r;
	if ( 'sabado' === $servicio ) {
		$neto = dip_tarifa_mrw( 'sabado', $peso, $bultos, $zona, $cerca );
		return null === $neto ? null : array_merge( $r, array( 'neto' => $neto ) );
	}
	// Servicios que llegan a esa hora o antes: 14 → 14, 12, 10 · 12 → 12, 10 · 10 → 10. Con empate, el de la hora pedida.
	$validos = array( '14' => array( '14', '12', '10' ), '12' => array( '12', '10' ), '10' => array( '10' ) )[ $servicio ];
	foreach ( $validos as $s ) {
		$neto = dip_tarifa_mrw( $s, $peso, $bultos, $zona, $cerca );
		if ( $s === $servicio || $neto < $r['neto'] - 0.001 ) {
			$r['neto'] = $neto;
			$r['mrw']  = $s;
		}
	}
	return $r;
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

/** ¿Envío a medida? Más de 150 kg con destino fuera de la provincia de Barcelona: se pide por mensaje. */
function dip_envio_a_medida( $kg, $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	return (float) $kg > DIP_KG_A_MEDIDA + 0.0001 && strlen( $cp ) >= 2 && '08' !== substr( $cp, 0, 2 );
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

/**
 * ¿Sale pedido ese día? De lunes a viernes, no festivo y sin cierre de la oficina de MRW de Mataró.
 * Si ese día no se ha consultado a MRW (más allá de 21 días o MRW caído), solo cuenta la lista manual.
 */
function dip_es_dia_de_salida( DateTimeInterface $dia ) {
	if ( (int) $dia->format( 'N' ) > 5 || dip_es_festivo( $dia ) ) return false;
	return ! ( function_exists( 'dip_mrw_cierra_salida' ) && dip_mrw_cierra_salida( $dia->format( 'Y-m-d' ) ) );
}

/**
 * Días sin salida para el JavaScript del tema ("llega el …"): la lista manual más los días en que cierra
 * MRW Mataró (los próximos 21, los que se han consultado). Lista de 'Y-m-d' ordenada.
 */
function dip_festivos_salida() {
	$lista = array_map( 'strval', array_keys( dip_festivos() ) );
	if ( function_exists( 'dip_mrw_dias' ) && function_exists( 'dip_mrw_cierra_salida' ) ) {
		$hoy = ( new DateTimeImmutable( 'today', dip_zona_horaria() ) )->format( 'Y-m-d' );
		foreach ( array_keys( dip_mrw_dias() ) as $f ) {
			if ( (string) $f >= $hoy && dip_mrw_cierra_salida( (string) $f ) ) $lista[] = (string) $f;
		}
	}
	$lista = array_values( array_unique( $lista ) );
	sort( $lista );
	return $lista;
}

/**
 * Destino del envío listo para los festivos de MRW: ['cp', 'poblacion', 'provincia' de MRW].
 * null si no se sabe (sin código postal de 5 cifras o fuera de la península): entonces no se aplican los festivos del destino.
 */
function dip_destino_normalizado( $destino ) {
	if ( ! is_array( $destino ) || ! function_exists( 'dip_mrw_provincia_por_cp' ) ) return null;
	$cp        = preg_replace( '/\D/', '', (string) ( $destino['cp'] ?? '' ) );
	$provincia = 5 === strlen( $cp ) ? dip_mrw_provincia_por_cp( $cp ) : '';
	if ( '' === $provincia ) return null;
	return array( 'cp' => $cp, 'poblacion' => trim( (string) ( $destino['poblacion'] ?? '' ) ), 'provincia' => $provincia );
}

/** Sábados en que la recogida no lleva suplemento (Ajustes; de serie el 31/10/2026, Halloween). El envío en sábado lo lleva siempre. */
function dip_sabados_recogida_sin_suplemento() {
	static $lista = null;
	if ( null === $lista ) {
		$texto = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'recogida_sabados_sin_suplemento' ) : '';
		$lista = array_values( array_filter( array_map( 'trim', preg_split( '/[\s,;]+/', $texto ) ), static fn( $f ) => (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f ) ) );
	}
	return $lista;
}

/**
 * Suplemento de sábado de un día, o null si no lleva.
 * - Envío: ninguno desde el 11/11/2026 (el sábado tiene su propia tarifa: dip_precio_servicio).
 * - Recogida: 10,00 € IVA incluido, partido en base y cuota que suman justo 10,00 € (8,26 € + 1,74 €);
 *   sin suplemento los sábados de dip_sabados_recogida_sin_suplemento().
 *
 * @return array{neto: float, iva: float, con_iva: float}|null
 */
function dip_suplemento_sabado( $metodo, $ymd ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d || 6 !== (int) $d->format( 'N' ) ) return null;
	$factor = dip_factor_iva();
	// Envío: desde el 11/11/2026 el sábado tiene su propia tarifa (Ecommerce de MRW, con su suplemento dentro):
	// va en el precio del envío (dip_precio_servicio), no en una línea aparte. Solo la recogida lleva suplemento.
	if ( 'recogida' !== $metodo ) return null;
	if ( in_array( (string) $ymd, dip_sabados_recogida_sin_suplemento(), true ) ) return null;
	$con_iva = round( (float) DIP_RECOGIDA_SABADO_CON_IVA, 2 );
	$neto    = round( $con_iva / $factor, 2 );
	return array( 'neto' => $neto, 'iva' => round( $con_iva - $neto, 2 ), 'con_iva' => $con_iva );
}

/** ¿Se puede recoger ese día? De lunes a sábado, también en festivos: la nave no cierra y la recogida se confirma antes. */
function dip_es_dia_de_recogida( DateTimeInterface $dia ) {
	return (int) $dia->format( 'N' ) <= 6;
}

/**
 * Minuto del día del corte (12:00 = 720) para un método. Filtro dip_corte_minutos: el enlace urgente (inc/urgente.php)
 * lo alarga hasta la hora límite para el envío, solo al cliente que tiene el enlace y solo ese día.
 */
function dip_corte_minutos( $metodo = 'envio', ?DateTimeImmutable $ahora = null ) {
	$base = DIP_CUTOFF_HOUR * 60 + DIP_CUTOFF_MINUTE;
	return function_exists( 'apply_filters' ) ? (int) apply_filters( 'dip_corte_minutos', $base, $metodo, $ahora ) : $base;
}

function dip_antes_del_corte( ?DateTimeImmutable $ahora = null, $metodo = 'envio' ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() ); // la hora de Madrid, venga en la zona que venga
	return ( (int) $ahora->format( 'G' ) * 60 + (int) $ahora->format( 'i' ) ) < dip_corte_minutos( $metodo, $ahora );
}

/**
 * Todas las fechas que se pueden elegir, de hoy a DIP_DIAS_RESERVA días vista (el cliente puede reservar
 * para dentro de un mes o más).
 * - Envío: sale un día de salida y llega al día siguiente (martes a sábado). El sábado lleva suplemento.
 *   Con $destino (['cp' => …, 'poblacion' => …]) tampoco se ofrece un día en que MRW cierra en el destino;
 *   esos días quedan en 'saltadas' para avisar al cliente. Sin destino, solo las reglas de salida.
 *   MRW solo se consulta 21 días vista: más allá cuenta la lista manual de festivos.
 * - Recogida: de lunes a sábado (también festivos) desde hoy si es antes del corte. El sábado lleva suplemento.
 *   No depende de MRW ni del destino.
 * Nunca llama a MRW: lee lo guardado por la comprobación automática (inc/festivos-mrw.php).
 *
 * Se calcula UNA vez por petición para cada combinación de método, día (y antes o después del corte) y destino;
 * el checkout, el suplemento de sábado, la validación al pagar y el calendario reutilizan el mismo resultado.
 *
 * @return array{fechas: array[], saltadas: string[], indice: array<string,int>, desde: string, hasta: string}
 *         'fechas' = [ ['fecha' => 'Y-m-d', 'sabado' => bool, 'sale' => 'Y-m-d'], … ] en orden.
 */
function dip_calendario_entrega( $metodo = 'envio', ?DateTimeImmutable $ahora = null, $destino = null ) {
	static $memo = array();
	$ahora   = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$metodo  = 'recogida' === $metodo ? 'recogida' : 'envio';
	$destino = 'envio' === $metodo ? dip_destino_normalizado( $destino ) : null;
	$clave   = $metodo . '|' . $ahora->format( 'Y-m-d' ) . '|' . ( dip_antes_del_corte( $ahora, $metodo ) ? '1' : '0' ) . '|' . ( $destino ? implode( '|', $destino ) : '' );
	if ( ! isset( $memo[ $clave ] ) ) $memo[ $clave ] = dip_calcular_fechas( $metodo, $ahora, $destino );
	return $memo[ $clave ];
}

/** Cálculo de dip_calendario_entrega() sin memoria. $destino ya normalizado (o null). */
function dip_calcular_fechas( $metodo, DateTimeImmutable $ahora, $destino = null ) {
	$hoy      = $ahora->setTime( 0, 0 );
	$hasta    = $hoy->modify( '+' . (int) DIP_DIAS_RESERVA . ' day' )->format( 'Y-m-d' );
	$dia      = $hoy;
	$fechas   = array();
	$saltadas = array();
	if ( 'recogida' === $metodo ) {
		if ( ! dip_antes_del_corte( $ahora, 'recogida' ) ) $dia = $dia->modify( '+1 day' );
		for ( ; $dia->format( 'Y-m-d' ) <= $hasta; $dia = $dia->modify( '+1 day' ) ) {
			if ( ! dip_es_dia_de_recogida( $dia ) ) continue;
			$fechas[] = array( 'fecha' => $dia->format( 'Y-m-d' ), 'sabado' => 6 === (int) $dia->format( 'N' ), 'sale' => $dia->format( 'Y-m-d' ) );
		}
	} else {
		if ( ! dip_es_dia_de_salida( $dia ) || ! dip_antes_del_corte( $ahora, 'envio' ) ) $dia = $dia->modify( '+1 day' );
		for ( ; ; $dia = $dia->modify( '+1 day' ) ) {
			$llega = $dia->modify( '+1 day' );
			$ymd   = $llega->format( 'Y-m-d' );
			if ( $ymd > $hasta ) break;
			if ( ! dip_es_dia_de_salida( $dia ) || dip_es_festivo( $llega ) ) continue;
			// Festivo de MRW en el destino (población o casi toda la provincia): ese día no se reparte allí
			if ( $destino && dip_mrw_cierra( $ymd, $destino['provincia'], $destino['poblacion'] ) ) {
				$saltadas[] = $ymd;
				continue;
			}
			$fechas[] = array( 'fecha' => $ymd, 'sabado' => 6 === (int) $llega->format( 'N' ), 'sale' => $dia->format( 'Y-m-d' ) );
		}
	}
	return array(
		'fechas'   => $fechas,
		'saltadas' => $saltadas,
		'indice'   => array_flip( array_column( $fechas, 'fecha' ) ),
		'desde'    => $hoy->format( 'Y-m-d' ),
		'hasta'    => $hasta,
	);
}

/**
 * Las primeras $cuantas fechas que se pueden elegir ($cuantas = 0: todas, hasta DIP_DIAS_RESERVA días vista).
 * $saltadas: días que MRW no reparte en el destino antes de la última fecha devuelta (para el aviso).
 *
 * @return array[] [ ['fecha' => 'Y-m-d', 'sabado' => bool, 'sale' => 'Y-m-d'], … ]
 */
function dip_fechas_disponibles( $metodo = 'envio', $cuantas = 8, ?DateTimeImmutable $ahora = null, $destino = null, &$saltadas = null ) {
	$cal      = dip_calendario_entrega( $metodo, $ahora, $destino );
	$cuantas  = (int) $cuantas;
	$fechas   = $cuantas > 0 ? array_slice( $cal['fechas'], 0, $cuantas ) : $cal['fechas'];
	$ultima   = ( $cuantas > 0 && $fechas && count( $fechas ) >= $cuantas ) ? $fechas[ count( $fechas ) - 1 ]['fecha'] : null;
	$saltadas = null === $ultima ? $cal['saltadas'] : array_values( array_filter( $cal['saltadas'], static fn( $s ) => $s < $ultima ) );
	return $fechas;
}

/** ¿Se puede elegir ese día? Cualquier fecha válida hasta DIP_DIAS_RESERVA días vista (consulta el cálculo ya hecho). */
function dip_fecha_es_valida( $ymd, $metodo = 'envio', $destino = null, ?DateTimeImmutable $ahora = null ) {
	return isset( dip_calendario_entrega( $metodo, $ahora, $destino )['indice'][ (string) $ymd ] );
}

/** Primera entrega posible por envío (sin contar el sábado, que es opcional y según zona). Sin destino: solo reglas de salida. */
function dip_primera_entrega( ?DateTimeImmutable $ahora = null, $destino = null ) {
	foreach ( dip_fechas_disponibles( 'envio', 10, $ahora, $destino ) as $f ) {
		if ( ! $f['sabado'] ) return $f;
	}
	return null;
}

/**
 * Próximo puente (estrategia/VENTAS-PSICOLOGIA.md, 2.2): días de martes a viernes sin reparto porque son festivo o porque
 * la víspera no sale nada (festivo o cierre de MRW Mataró), si el primero cae en los próximos $vista días. Sirve para avisar
 * con tiempo y con fechas reales. Sin destino: los festivos de MRW en el destino ya los avisa el checkout para ese código
 * postal (dip_nota_festivos_destino). El sábado no cuenta: es opcional y según zona. La misma regla está en el JavaScript
 * del tema (movimiento.js, dipEntrega.puente), que pinta el aviso en la ficha.
 *
 * @return array{dias: string[], antes: ?array, siguiente: ?array}|null 'antes': la última entrega entre semana que aún se
 *         puede pedir antes del puente; 'siguiente': la primera después (entradas de dip_calendario_entrega()).
 */
function dip_puente_proximo( ?DateTimeImmutable $ahora = null, $vista = 10 ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$hoy   = $ahora->setTime( 0, 0 );
	$sin   = static function ( DateTimeImmutable $d ) {
		$n = (int) $d->format( 'N' );
		return $n >= 2 && $n <= 5 && ( dip_es_festivo( $d ) || ! dip_es_dia_de_salida( $d->modify( '-1 day' ) ) );
	};
	$dias = array();
	for ( $i = 1; $i <= (int) $vista && ! $dias; $i++ ) {
		$d = $hoy->modify( '+' . $i . ' day' );
		if ( $sin( $d ) ) $dias[] = $d;
	}
	if ( ! $dias ) return null;
	// Sigue mientras el siguiente día de reparto (de martes a viernes) tampoco tiene entrega
	for ( $d = $dias[0]->modify( '+1 day' ), $j = 0; $j < 30; $d = $d->modify( '+1 day' ), $j++ ) {
		$n = (int) $d->format( 'N' );
		if ( $n < 2 || $n > 5 ) continue;
		if ( ! $sin( $d ) ) break;
		$dias[] = $d;
	}
	$dias    = array_map( static fn( $d ) => $d->format( 'Y-m-d' ), $dias );
	$ultimo  = $dias[ count( $dias ) - 1 ];
	$antes   = null;
	$despues = null;
	foreach ( dip_calendario_entrega( 'envio', $ahora )['fechas'] as $f ) {
		if ( $f['sabado'] ) continue;
		if ( $f['fecha'] < $dias[0] ) {
			$antes = $f;
		} elseif ( $f['fecha'] > $ultimo ) {
			$despues = $f;
			break;
		}
	}
	return array( 'dias' => $dias, 'antes' => $antes, 'siguiente' => $despues );
}

/**
 * Días seguidos como se dicen: «el martes 8 y el miércoles 9 de diciembre», «del viernes 25 al martes 29 de diciembre».
 * El mes solo al final si es el mismo. Textos dias_1, dias_2 y dias_n de dip_textos_tienda().
 */
function dip_texto_dias( array $dias, $idioma = 'es' ) {
	$dias = array_values( array_map( 'strval', $dias ) );
	if ( ! $dias ) return '';
	$n     = dip_nombres_fecha( $idioma );
	$texto = static function ( $ymd, $mes ) use ( $n ) {
		$d = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, dip_zona_horaria() );
		if ( ! $d ) return $ymd;
		return $n['dias'][ (int) $d->format( 'N' ) - 1 ] . ' ' . $d->format( 'j' ) . ( $mes ? ' ' . $n['mde'][ (int) $d->format( 'n' ) - 1 ] : '' );
	};
	$t      = dip_textos_tienda( $idioma );
	$ultimo = $dias[ count( $dias ) - 1 ];
	if ( 1 === count( $dias ) ) return sprintf( $t['dias_1'], $texto( $ultimo, true ) );
	$primero = $texto( $dias[0], substr( $dias[0], 0, 7 ) !== substr( $ultimo, 0, 7 ) );
	return sprintf( 2 === count( $dias ) ? $t['dias_2'] : $t['dias_n'], $primero, $texto( $ultimo, true ) );
}

/**
 * Nombres de días y meses (es, ca, en), empezando por el lunes y por enero (índice 0). Los usan las fechas
 * de PHP y el calendario del checkout (JavaScript), para que digan lo mismo.
 * 'mde' = el mes como va detrás del número: "de octubre", "d'octubre", "October".
 */
function dip_nombres_fecha( $idioma = 'es' ) {
	static $cache = array();
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	if ( isset( $cache[ $idioma ] ) ) return $cache[ $idioma ];
	$n = array(
		'es' => array(
			'dias'  => array( 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo' ),
			'meses' => array( 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' ),
			'dc'    => array( 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom' ),
			'mc'    => array( 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic' ),
		),
		'ca' => array(
			'dias'  => array( 'dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres', 'dissabte', 'diumenge' ),
			'meses' => array( 'gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre' ),
			'dc'    => array( 'dl.', 'dt.', 'dc.', 'dj.', 'dv.', 'ds.', 'dg.' ),
			'mc'    => array( 'gen.', 'febr.', 'març', 'abr.', 'maig', 'juny', 'jul.', 'ag.', 'set.', 'oct.', 'nov.', 'des.' ),
		),
		'en' => array(
			'dias'  => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
			'meses' => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
			'dc'    => array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ),
			'mc'    => array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ),
		),
	);
	$r        = $n[ $idioma ];
	$r['mde'] = array();
	foreach ( $r['meses'] as $i => $mes ) {
		if ( 'en' === $idioma ) $r['mde'][] = $mes;
		elseif ( 'ca' === $idioma ) $r['mde'][] = ( in_array( $i + 1, array( 4, 8, 10 ), true ) ? "d'" : 'de ' ) . $mes;
		else $r['mde'][] = 'de ' . $mes;
	}
	return $cache[ $idioma ] = $r;
}

/** "jueves 2 de octubre" en el idioma pedido (es, ca, en). */
function dip_fecha_larga( $ymd, $idioma = 'es' ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d ) return (string) $ymd;
	$n = dip_nombres_fecha( $idioma );
	return $n['dias'][ (int) $d->format( 'N' ) - 1 ] . ' ' . $d->format( 'j' ) . ' ' . $n['mde'][ (int) $d->format( 'n' ) - 1 ];
}

/** [ 'jue', '2', 'oct' ] para las casillas de fecha. */
function dip_fecha_corta( $ymd, $idioma = 'es' ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d ) return array( '', '', '' );
	$n = dip_nombres_fecha( $idioma );
	return array( $n['dc'][ (int) $d->format( 'N' ) - 1 ], $d->format( 'j' ), $n['mc'][ (int) $d->format( 'n' ) - 1 ] );
}

/** Precio formateado a la española sin depender de WooCommerce: 81,81 € */
function dip_euros( $importe ) {
	return number_format( (float) $importe, 2, ',', '.' ) . ' €';
}

/**
 * Tabla de precios del envío por hora y zona para la página de envíos (11/11/2026): con IVA, un pack por pedido, con el
 * mismo cálculo que el checkout (dip_precio_servicio) y un código postal de muestra de cada zona, cerca de una oficina de
 * MRW. Lo que suma estar a más de 25 km de una oficina (10:00 y sábado) va aparte, en 'lejos'.
 *
 * @param float[] $packs Kilos de cada pack (3 o 5, 10, 15, 20).
 * @return array{zonas: array<string, array<string, array<string, ?float>>>, lejos: array{10: float, sabado: float}}
 *         zonas[urbano|provincial|regional|peninsular][kg][14|12|10|sabado] = euros con IVA (null si no se puede).
 */
function dip_tabla_precios_envio( array $packs ) {
	$muestras = array(
		'urbano'     => array( 'cp' => '08301', 'poblacion' => 'Mataró' ),
		'provincial' => array( 'cp' => '08013', 'poblacion' => 'Barcelona' ),
		'regional'   => array( 'cp' => '17001', 'poblacion' => 'Girona' ),
		'peninsular' => array( 'cp' => '28001', 'poblacion' => 'Madrid' ),
	);
	$iva = dip_factor_iva();
	$r   = array(
		'zonas' => array(),
		'lejos' => array(
			'10'     => round( DIP_SUPLEMENTO_10_LEJOS * $iva, 2 ),
			'sabado' => round( ( (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX - DIP_SABADO_SUPLEMENTO_CERCA ) * $iva, 2 ),
		),
	);
	foreach ( $muestras as $zona => $destino ) {
		foreach ( $packs as $kg ) {
			foreach ( array( '14', '12', '10', 'sabado' ) as $servicio ) {
				$p = dip_precio_servicio( $servicio, (float) $kg, 1, $destino );
				$r['zonas'][ $zona ][ (string) ( 0 + $kg ) ][ $servicio ] = $p ? round( $p['neto'] * $iva, 2 ) : null;
			}
		}
	}
	return $r;
}
