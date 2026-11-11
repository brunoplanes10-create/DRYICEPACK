<?php
/**
 * Datos de envío para la app INDUNOVA Control: GET /wp-json/dip/v1/envio (decidido el 03/10/2026; la app se estrena el 9/11).
 * - Público, solo lectura y sin datos personales: tarifas, reglas de días, festivos de salida y cierres de MRW guardados.
 *   CORS abierto (*) y caché de 10 minutos.
 * - Todo sale de las mismas constantes y funciones que usa el checkout: si cambia una regla, cambia aquí también.
 * - mrw.dias lleva todos los días consultados, también los que no tienen cierres (listas vacías). Un día que no está = sin datos.
 *   'provincias': código de 2 cifras de cada provincia cerrada entera (≥ DIP_MRW_PROVINCIA de sus oficinas).
 *   'poblaciones': el resto de oficinas cerradas que cuentan para dip_mrw_cierra(), con dip_mrw_nombres() ya aplicado.
 * - La app copia dip_mrw_nombres() y dip_mrw_mismo_sitio() tal cual (inc/festivos-mrw.php): si cambian, hay que avisar.
 * - Si cambia el formato de esta respuesta, sube 'version' y avisa: la app lo lee.
 * - Versión 3 (11/11/2026): 'servicios' (hora de entrega a elegir: 14 de serie, 12, 10 y sábado; código de MRW y tarifas
 *   por zona, sin IVA) y 'recogida.horario' (por día de la semana, 1 = lunes). Lo de antes no cambia, salvo que el sábado
 *   se entrega antes de las 13:00 y ya no lleva un suplemento fijo: 'sabado.suplemento_sin_iva' (9,70 €) es ahora el
 *   suplemento de sábado de MRW a más de 25 km de su oficina (a menos, 5,45 €), que va dentro de la tarifa del sábado.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_API_VERSION' ) ) define( 'DIP_API_VERSION', 3 );

add_action( 'rest_api_init', static function () {
	register_rest_route( 'dip/v1', '/envio', array(
		'methods'             => 'GET',
		'callback'            => 'dip_api_envio',
		'permission_callback' => '__return_true',
	) );
} );

/** Código de provincia de 2 cifras a partir del nombre de provincia de MRW ("BARCELONA" → "08"). '' si no es de la península. */
function dip_api_codigo_provincia( $provincia ) {
	static $codigos = null;
	if ( null === $codigos ) {
		$codigos = array();
		for ( $i = 1; $i <= 52; $i++ ) {
			$codigo = sprintf( '%02d', $i );
			$nombre = dip_mrw_provincia_por_cp( $codigo . '000' );
			if ( '' !== $nombre ) $codigos[ $nombre ] = $codigo;
		}
	}
	return $codigos[ dip_mrw_clave_provincia( $provincia ) ] ?? '';
}

/** Un día de MRW como lo lee la app: provincias cerradas enteras y oficinas cerradas del resto. */
function dip_api_dia_mrw( array $cerradas ) {
	$enteras     = dip_mrw_provincias_cerradas( $cerradas );
	$provincias  = array();
	foreach ( $enteras as $p ) {
		$codigo = dip_api_codigo_provincia( $p );
		if ( '' !== $codigo ) $provincias[] = $codigo;
	}
	$poblaciones = array();
	foreach ( $cerradas as $c ) {
		$p = (string) ( $c[0] ?? '' );
		if ( in_array( $p, $enteras, true ) || dip_mrw_es_interna( $c[1] ?? '' ) ) continue; // como dip_mrw_cierra()
		$codigo  = dip_api_codigo_provincia( $p );
		$nombres = dip_mrw_nombres( $c[1] ?? '' );
		if ( '' === $codigo || ! $nombres ) continue;
		$poblaciones[] = array( 'provincia' => $codigo, 'nombres' => $nombres );
	}
	sort( $provincias );
	return array( 'provincias' => array_values( array_unique( $provincias ) ), 'poblaciones' => $poblaciones );
}

/** Fecha y hora en ISO 8601 con la hora de Madrid ("2026-10-03T19:00:00+02:00"). */
function dip_api_iso( $timestamp ) {
	return ( new DateTimeImmutable( '@' . (int) $timestamp ) )->setTimezone( dip_zona_horaria() )->format( 'c' );
}

/** La respuesta (también la usan las pruebas). */
function dip_api_datos_envio() {
	$estado = (array) get_option( 'dip_mrw', array() );
	$hoy    = ( new DateTimeImmutable( 'today', dip_zona_horaria() ) )->format( 'Y-m-d' );
	$dias   = array();
	foreach ( (array) ( $estado['dias'] ?? array() ) as $fecha => $cerradas ) {
		if ( (string) $fecha < $hoy ) continue;
		$dias[ (string) $fecha ] = dip_api_dia_mrw( (array) $cerradas );
	}
	ksort( $dias );
	$leido = (int) ( $estado['ok'] ?? 0 ) ?: (int) ( $estado['comprobado'] ?? 0 );

	return array(
		'version'  => DIP_API_VERSION,
		'generado' => dip_api_iso( time() ),
		'iva'      => round( dip_factor_iva() - 1, 4 ),
		'tarifa'   => array(
			'hasta_2'                => (float) DIP_TARIFA_HASTA_2,
			'hasta_5'                => (float) DIP_TARIFA_HASTA_5,
			'hasta_10'               => (float) DIP_TARIFA_HASTA_10,
			'kg_extra'               => (float) DIP_TARIFA_KG_EXTRA,
			'peso_embalaje_por_caja' => (float) DIP_PESO_EMBALAJE,
			'redondeo'               => 'al alza',
		),
		'sabado'   => array(
			'suplemento_sin_iva'       => (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX, // a más de 25 km de la oficina de MRW
			'suplemento_cerca_sin_iva' => (float) DIP_SABADO_SUPLEMENTO_CERCA,     // a menos (versión 3)
			'hora'                     => DIP_HORA_ENTREGA_SABADO,
			'max_kg'                   => (int) DIP_SABADO_MAX_KG,
		),
		// Versión 3 (11/11/2026): horas de entrega a elegir y sus tarifas de MRW, sin IVA. Cada hora se cobra al precio del
		// servicio de MRW más barato que llega a esa hora (dip_precio_servicio). Zonas desde Mataró: urbano (08301–08304),
		// provincial (08), regional (17, 25, 43), peninsular. 'cerca' = la población tiene oficina de MRW (dip_cerca_oficina_mrw).
		'servicios' => dip_api_servicios(),
		'recogida' => array(
			'dias'                      => array( 1, 2, 3, 4, 5, 6 ),
			'tambien_festivos'          => true,
			'redondeo_con_iva'          => (int) DIP_RECOGIDA_REDONDEO,
			'suplemento_sabado_con_iva' => (float) DIP_RECOGIDA_SABADO_CON_IVA,
			'pago'                      => 'efectivo',
			// Versión 3 (11/11/2026): horario de la nave por día de la semana (1 = lunes); la hora exacta la confirma la empresa
			'horario'                   => array( '1' => array( '09:00', '18:00' ), '2' => array( '09:00', '18:00' ), '3' => array( '09:00', '18:00' ), '4' => array( '09:00', '18:00' ), '5' => array( '09:00', '18:00' ), '6' => array( '09:00', '13:00' ) ),
			// Versión 2 (15/10/2026): sábados de recogida sin los 10 € (de serie el 31/10/2026), los que quedan
			'sabados_sin_suplemento'    => array_values( array_filter( dip_sabados_recogida_sin_suplemento(), static fn( $f ) => $f >= $hoy ) ),
		),
		'max_kg'                 => (int) DIP_MAX_KG,
		'max_kg_fuera_barcelona' => (int) DIP_KG_A_MEDIDA,
		'reservas_dias'          => (int) DIP_DIAS_RESERVA,
		'corte'                  => array( 'hora' => (int) DIP_CUTOFF_HOUR, 'minuto' => (int) DIP_CUTOFF_MINUTE, 'zona' => dip_zona_horaria()->getName() ),
		'dias_salida'            => array( 1, 2, 3, 4, 5 ),
		'dias_entrega'           => array( 2, 3, 4, 5, 6 ),
		'festivos_salida'        => dip_festivos_salida(),
		'mrw'                    => array(
			'leido'            => $leido ? dip_api_iso( $leido ) : null,
			'desde'            => $dias ? (string) array_key_first( $dias ) : null,
			'hasta'            => $dias ? (string) array_key_last( $dias ) : null,
			'umbral_provincia' => (float) DIP_MRW_PROVINCIA,
			'dias'             => $dias ?: new stdClass(), // objeto vacío, no lista, cuando no hay días
		),
	);
}

/** Servicios de entrega para la app (versión 3): hora, código de MRW (Ajustes → MRW) y tarifa. */
function dip_api_servicios() {
	$conf = function_exists( 'dip_sagec_config' ) ? dip_sagec_config() : array();
	$t    = dip_tarifas_mrw();
	$r    = array();
	foreach ( dip_servicios_envio() as $clave => $s ) {
		$r[ $clave ] = array( 'hora' => $s['hora'], 'codigo_mrw' => function_exists( 'dip_sagec_codigo' ) && $conf ? dip_sagec_codigo( $clave, $conf ) : $s['codigo'], 'servicio_mrw' => $s['mrw'] );
	}
	$r['14']['tarifa'] = array( 'igual_en_toda_la_peninsula' => true, 'hasta_2' => (float) DIP_TARIFA_HASTA_2, 'hasta_5' => (float) DIP_TARIFA_HASTA_5, 'hasta_10' => (float) DIP_TARIFA_HASTA_10, 'kg_extra' => (float) DIP_TARIFA_KG_EXTRA );
	foreach ( array( '12', '10' ) as $clave ) {
		$r[ $clave ]['tarifa'] = array();
		foreach ( $t[ $clave ] as $zona => $v ) $r[ $clave ]['tarifa'][ $zona ] = array( 'hasta_5' => $v[0], 'hasta_10' => $v[1], 'kg_extra' => $v[2] );
	}
	$r['10']['suplemento_lejos'] = (float) DIP_SUPLEMENTO_10_LEJOS;
	$r['sabado']['tarifa']       = array( 'tramos_kg' => $t['sabado']['tramos'] );
	foreach ( array( 'urbano', 'provincial', 'regional', 'peninsular' ) as $zona ) {
		$r['sabado']['tarifa'][ $zona ] = array( 'tramos' => array_slice( $t['sabado'][ $zona ], 0, 9 ), 'cada_5_kg_mas' => $t['sabado'][ $zona ][9] );
	}
	$r['sabado']['bulto_extra_desde_el_3'] = (float) DIP_SABADO_BULTO_EXTRA;
	$r['sabado']['suplemento_cerca']       = (float) DIP_SABADO_SUPLEMENTO_CERCA;
	$r['sabado']['suplemento_lejos']       = (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX;
	$r['sabado']['max_kg']                 = (int) DIP_SABADO_MAX_KG;
	return $r;
}

function dip_api_envio() {
	$respuesta = new WP_REST_Response( dip_api_datos_envio(), 200 );
	$respuesta->header( 'Cache-Control', 'public, max-age=600' );
	return $respuesta;
}

/*
 * CORS abierto: WordPress responde con el origen de la petición y "credentials"; esta ruta es pública y sin cookies,
 * así que va con "*" (lo pidió la app). Se aplica después de las cabeceras de WordPress (prioridad 10).
 */
add_filter( 'rest_pre_serve_request', static function ( $servido, $resultado, $peticion ) {
	if ( $peticion instanceof WP_REST_Request && '/dip/v1/envio' === $peticion->get_route() && ! headers_sent() ) {
		header( 'Access-Control-Allow-Origin: *' );
		header_remove( 'Access-Control-Allow-Credentials' );
		header( 'Cache-Control: public, max-age=600' );
		header_remove( 'Pragma' );
		header_remove( 'Expires' );
	}
	return $servido;
}, 20, 3 );
