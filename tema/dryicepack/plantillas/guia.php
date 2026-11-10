<?php
/**
 * Guía del tema (contenido/{idioma}/guias/*.php), en castellano, catalán e inglés con su propia dirección.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$dipt_clave = dipt_pagina_actual();
$dipt_datos = dipt_guia( dipt_paginas()[ $dipt_clave ]['guia'] ?? '' );
get_header();
if ( $dipt_datos ) {
	$dipt_g = array(
		'titulo' => $dipt_datos['titulo'],
		'html'   => $dipt_datos['contenido'],
		'url'    => dipt_url( $dipt_clave ),
	);
	if ( ! empty( $dipt_datos['cta'] ) ) $dipt_g['cta'] = $dipt_datos['cta']; // cierre propio (de usted en las guías B2B)
	if ( isset( $dipt_datos['aviso'] ) ) $dipt_g['aviso'] = $dipt_datos['aviso']; // aviso de seguridad del margen (índice en contenido/comun.php)
	include DIPT_DIR . '/partes/guia-articulo.php';
}
get_footer();
