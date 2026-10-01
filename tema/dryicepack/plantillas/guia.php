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
	include DIPT_DIR . '/partes/guia-articulo.php';
}
get_footer();
