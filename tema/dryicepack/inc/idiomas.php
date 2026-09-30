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
	return $cache[ $clave ] = is_array( $datos ) ? $datos : array();
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
