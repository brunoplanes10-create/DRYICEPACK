<?php
/**
 * Entrada del blog: mismo diseño que las guías del tema (partes/guia-articulo.php).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
while ( have_posts() ) :
	the_post();
	$dipt_g = array(
		'titulo' => get_the_title(),
		'html'   => apply_filters( 'the_content', get_the_content() ),
		'fecha'  => get_the_date( 'j F Y' ),
		'url'    => get_permalink(),
	);
	include DIPT_DIR . '/partes/guia-articulo.php';
endwhile;
get_footer();
