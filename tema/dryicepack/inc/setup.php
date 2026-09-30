<?php
/**
 * Soportes del tema y limpieza del <head>.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_setup_theme', static function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 480, 'single_image_width' => 900 ) );
	add_image_size( 'dipt-guia', 1200, 750, true );
} );

/* ---------- <head> limpio ---------- */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
add_filter( 'emoji_svg_url', '__return_false' );

/* Estilos de bloques: solo en las guías (entradas), que son las únicas que se escriben con el editor. */
add_action( 'wp_enqueue_scripts', static function () {
	if ( is_singular( 'post' ) ) return;
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $h ) wp_dequeue_style( $h );
}, 100 );

/* ---------- Red de seguridad al quitar Divi ----------
   Textos guardados con shortcodes de Divi ([et_pb_section …]) se verían como texto: se quitan las etiquetas y queda el contenido. */
function dipt_limpiar_divi( $contenido ) {
	if ( ! is_string( $contenido ) || false === strpos( $contenido, '[et_pb_' ) ) return $contenido;
	return trim( preg_replace( '/\[\/?et_pb_[^\]]*\]/', '', $contenido ) );
}
add_filter( 'the_content', 'dipt_limpiar_divi', 5 );
add_filter( 'woocommerce_short_description', 'dipt_limpiar_divi', 5 );
add_filter( 'the_excerpt', 'dipt_limpiar_divi', 5 );

/* ---------- Clases en <body> ---------- */
add_filter( 'body_class', static function ( $clases ) {
	$clave = dipt_pagina_actual();
	if ( $clave ) $clases[] = 'p-' . sanitize_html_class( $clave );
	$clases[] = 'idioma-' . dipt_idioma();
	if ( dipt_cabecera_sobre_oscuro() ) $clases[] = 'con-hero-oscuro';
	return $clases;
} );

/* Idioma del documento: <html lang="ca"> en las páginas en catalán, "en" en inglés. */
add_filter( 'language_attributes', static function ( $salida ) {
	$lang = array( 'es' => 'es-ES', 'ca' => 'ca', 'en' => 'en' )[ dipt_idioma() ] ?? 'es-ES';
	return preg_replace( '/lang="[^"]*"/', 'lang="' . $lang . '"', $salida ) ?: 'lang="' . $lang . '"';
} );

/* Las etiquetas y autores no aportan nada al buscador. */
add_filter( 'wp_robots', static function ( $robots ) {
	if ( is_tag() || is_author() || is_date() || is_search() ) $robots['noindex'] = true;
	return $robots;
} );
