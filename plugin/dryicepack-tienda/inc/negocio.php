<?php
/**
 * Datos y reglas del negocio: una sola fuente para la tienda, el tema y los emails.
 *
 * Tarifa de transporte del 31/08/2026 (igual en toda la península, sin IVA):
 *   hasta 2 kg 8,53 € · hasta 5 kg 10,32 € · hasta 10 kg 13,19 € · cada kg de más 1,12 €
 * Peso facturable = hielo + 1 kg por caja (medido el 31/08/2026), redondeado al alza.
 * Corte a las 12:00 (Madrid). Sale de lunes a viernes y llega al día siguiente por la mañana.
 * Sin entregas en domingo ni lunes. Sábado según zona con suplemento de 9,70 € + IVA.
 * Festivos: la lista manual de Ajustes (Mataró, Cataluña, España) y, por encima, los de MRW (inc/festivos-mrw.php):
 * sin salida si cierra MRW Mataró y sin entrega si cierra MRW en el destino.
 * Recogida en la nave de lunes a sábado, también en festivos (la confirma la empresa). El sábado lleva el mismo suplemento.
 * Más de 150 kg fuera de la provincia de Barcelona: sin envío online, se prepara a medida (el proveedor solo sirve
 * más de 150 kg dentro de la provincia).
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
if ( ! defined( 'DIP_KG_A_MEDIDA' ) )                  define( 'DIP_KG_A_MEDIDA', 150 );
if ( ! defined( 'DIP_DIAS_RESERVA' ) )                 define( 'DIP_DIAS_RESERVA', 90 ); // se puede pedir para cualquier día de los próximos 90

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

/** ¿Se puede recoger ese día? De lunes a sábado, también en festivos: la nave no cierra y la recogida se confirma antes. */
function dip_es_dia_de_recogida( DateTimeInterface $dia ) {
	return (int) $dia->format( 'N' ) <= 6;
}

function dip_antes_del_corte( ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() ); // la hora de Madrid, venga en la zona que venga
	return ( (int) $ahora->format( 'G' ) * 60 + (int) $ahora->format( 'i' ) ) < ( DIP_CUTOFF_HOUR * 60 + DIP_CUTOFF_MINUTE );
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
	$clave   = $metodo . '|' . $ahora->format( 'Y-m-d' ) . '|' . ( dip_antes_del_corte( $ahora ) ? '1' : '0' ) . '|' . ( $destino ? implode( '|', $destino ) : '' );
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
		if ( ! dip_antes_del_corte( $ahora ) ) $dia = $dia->modify( '+1 day' );
		for ( ; $dia->format( 'Y-m-d' ) <= $hasta; $dia = $dia->modify( '+1 day' ) ) {
			if ( ! dip_es_dia_de_recogida( $dia ) ) continue;
			$fechas[] = array( 'fecha' => $dia->format( 'Y-m-d' ), 'sabado' => 6 === (int) $dia->format( 'N' ), 'sale' => $dia->format( 'Y-m-d' ) );
		}
	} else {
		if ( ! dip_es_dia_de_salida( $dia ) || ! dip_antes_del_corte( $ahora ) ) $dia = $dia->modify( '+1 day' );
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
