<?php
/**
 * Idioma de la visita y carga de textos.
 * - Castellano sin prefijo; catalán en /ca/…; inglés en /en/….
 * - Los textos de cada página están en contenido/{idioma}/{pagina}.php y devuelven un array.
 *   Si falta la traducción, se usa el castellano.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_idiomas() {
	return array( 'es' => 'Español', 'ca' => 'Català', 'en' => 'English' );
}

function dipt_idioma() {
	if ( function_exists( 'dip_idioma' ) ) return dip_idioma();
	$ruta = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	return preg_match( '#^(ca|en)(/|$)#', $ruta, $m ) ? $m[1] : 'es';
}

/** Textos de una página (o 'comun') en el idioma de la visita. */
function dipt_contenido( $nombre, $idioma = null ) {
	static $cache = array();
	$idioma = $idioma ?: dipt_idioma();
	$clave  = $idioma . '/' . $nombre;
	if ( isset( $cache[ $clave ] ) ) return $cache[ $clave ];
	$archivo = DIPT_DIR . '/contenido/' . $idioma . '/' . sanitize_file_name( $nombre ) . '.php';
	if ( ! file_exists( $archivo ) ) $archivo = DIPT_DIR . '/contenido/es/' . sanitize_file_name( $nombre ) . '.php';
	$datos = file_exists( $archivo ) ? include $archivo : array();
	return $cache[ $clave ] = is_array( $datos ) ? dipt_marcadores( $datos, $idioma ) : array();
}

/**
 * Marcadores de los textos que dependen de la tienda (03/10/2026), para que cambien solos:
 * - {pk}: kilos del pack pequeño que se vende ahora (3 mientras queden cajas de 3 kg; después 5).
 * - {recogida:10}, {recogida:pk}: precio de recogida en Mataró de ese pack, IVA incluido ("60 €", "€60").
 * - {precio:10}, {precio:pk}: precio de la web de ese pack con IVA ("64,49 €").
 * - {sabado_recogida}: suplemento de la recogida en sábado ("10 €"); {sabado_envio}: el del envío ("11,74 €").
 * - {sabado_libre}: " El sábado 31 de octubre, sin suplemento." mientras quede un sábado de recogida sin suplemento; si no, nada.
 * Los marcadores que no son estos ({km}, {nombre}…) se quedan como están.
 */
function dipt_marcadores( $datos, $idioma = null ) {
	if ( is_array( $datos ) ) {
		foreach ( $datos as $k => $v ) $datos[ $k ] = dipt_marcadores( $v, $idioma );
		return $datos;
	}
	if ( ! is_string( $datos ) || false === strpos( $datos, '{' ) ) return $datos;
	return preg_replace_callback( '/\{(pk|recogida:(?:pk|\d+)|precio:(?:pk|\d+)|sabado_recogida|sabado_envio|sabado_libre)\}/', static function ( $m ) use ( $idioma ) {
		return dipt_valor_marcador( $m[1], $idioma ?: dipt_idioma() );
	}, $datos );
}

/** Valor de un marcador de dipt_marcadores(). */
function dipt_valor_marcador( $marcador, $idioma ) {
	$euros = static function ( $n ) use ( $idioma ) {
		$entero = abs( $n - round( $n ) ) < 0.005;
		if ( 'en' === $idioma ) return '€' . number_format( (float) $n, $entero ? 0 : 2, '.', ',' );
		return number_format( (float) $n, $entero ? 0 : 2, ',', '.' ) . ' €';
	};
	if ( 'pk' === $marcador ) return (string) dipt_pk();
	if ( 'sabado_recogida' === $marcador ) return $euros( defined( 'DIP_RECOGIDA_SABADO_CON_IVA' ) ? (float) DIP_RECOGIDA_SABADO_CON_IVA : 10.0 );
	if ( 'sabado_envio' === $marcador ) return $euros( round( ( defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) ? (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX : 9.70 ) * dipt_factor_iva(), 2 ) );
	if ( 'sabado_libre' === $marcador ) return dipt_texto_sabado_libre( $idioma );
	list( $tipo, $kg ) = explode( ':', $marcador );
	$kg = 'pk' === $kg ? dipt_pk() : (int) $kg;
	if ( 'recogida' === $tipo ) {
		$rec = dipt_precios_recogida()[ (string) $kg ] ?? null;
		if ( null === $rec ) { // sin el plugin: misma regla (precio con IVA redondeado a la baja a 5 €)
			$con = (float) ( dipt_packs()[ (string) $kg ] ?? 0 ) * dipt_factor_iva();
			$rec = $con > 0 ? max( 5, floor( round( $con, 2 ) / 5 ) * 5 ) : 0;
		}
		return $euros( $rec );
	}
	return $euros( dipt_precio_pack( $kg )['con_iva'] );
}

/** " El sábado 31 de octubre, sin suplemento." (con espacio delante) mientras quede un sábado de recogida sin suplemento. */
function dipt_texto_sabado_libre( $idioma ) {
	if ( ! function_exists( 'dip_sabados_recogida_sin_suplemento' ) ) return '';
	$hoy = ( new DateTimeImmutable( 'today', new DateTimeZone( 'Europe/Madrid' ) ) )->format( 'Y-m-d' );
	foreach ( dip_sabados_recogida_sin_suplemento() as $f ) {
		if ( $f < $hoy ) continue;
		// Sin el día de la semana: la frase va justo detrás de "El sábado, +10 €" ("31 de octubre", "31 d'octubre", "31 October")
		$fecha = preg_replace( '/^\S+\s+/u', '', dipt_fecha_larga( $f, $idioma ) );
		if ( 'en' === $idioma ) return ' Except ' . $fecha . ': no surcharge.';
		if ( 'ca' === $idioma ) return ' Excepte el ' . $fecha . ': sense suplement.';
		return ' Salvo el ' . $fecha . ': sin suplemento.';
	}
	return '';
}

/** Texto común (cabecera, pie, botones): dipt_t('comprar'). */
function dipt_t( $clave ) {
	$c = dipt_contenido( 'comun' );
	return $c[ $clave ] ?? ( dipt_contenido( 'comun', 'es' )[ $clave ] ?? $clave );
}

/** Imprime HTML de los textos, que pueden llevar <em> y <strong> (vienen de nuestros archivos, no del usuario). */
function dipt_html( $texto ) {
	echo wp_kses( (string) $texto, array( 'em' => array(), 'strong' => array(), 'br' => array(), 'span' => array( 'class' => array() ), 'a' => array( 'href' => array(), 'class' => array() ), 'sub' => array(), 'abbr' => array( 'title' => array() ) ) );
}
