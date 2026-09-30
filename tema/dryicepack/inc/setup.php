<?php
/**
 * Soportes del tema, menús y limpieza del <head>.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'after_setup_theme', static function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 88, 'width' => 440, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'responsive-embeds' );

	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 480,
		'single_image_width'    => 900,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'principal' => 'Menú principal (cabecera)',
		'pie'       => 'Menú del pie',
	) );
} );

/* ---------- <head> limpio: fuera lo que no usa esta web ---------- */
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

// Estilos de bloques de Gutenberg: esta web no usa bloques en las páginas del tema
add_action( 'wp_enqueue_scripts', static function () {
	if ( is_singular( 'post' ) ) return; // en entradas de blog sí pueden hacer falta
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}, 100 );

/* ---------- Red de seguridad al quitar Divi ----------
   Algunos textos guardados en la base de datos (la descripción del producto, por ejemplo) llevan
   shortcodes de Divi. Sin Divi se verían como texto ([et_pb_section …]). Aquí se quitan las etiquetas
   y se conserva el contenido que llevan dentro. */
if ( ! function_exists( 'dipt_limpiar_divi' ) ) {
	function dipt_limpiar_divi( $contenido ) {
		if ( ! is_string( $contenido ) || false === strpos( $contenido, '[et_pb_' ) ) return $contenido;
		$contenido = preg_replace( '/\[\/?et_pb_[^\]]*\]/', '', $contenido );
		return trim( $contenido );
	}
}
add_filter( 'the_content', 'dipt_limpiar_divi', 5 );
add_filter( 'woocommerce_short_description', 'dipt_limpiar_divi', 5 );
add_filter( 'the_excerpt', 'dipt_limpiar_divi', 5 );

/* ---------- Clases en <body> para ámbitos de CSS ---------- */
add_filter( 'body_class', static function ( $clases ) {
	if ( is_front_page() ) $clases[] = 'p-inicio';
	if ( is_page() ) {
		$clases[] = 'p-' . sanitize_html_class( get_post_field( 'post_name', get_queried_object_id() ) );
	}
	return $clases;
} );
