<?php
/**
 * CSS y JS: base común + la hoja de cada plantilla. Versiones .min si existen (herramientas/compilar-tema.mjs).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_asset( $rel ) {
	$min = preg_replace( '/\.(css|js)$/', '.min.$1', $rel );
	return file_exists( DIPT_DIR . '/' . $min ) ? $min : $rel;
}
function dipt_ver( $rel ) {
	$p = DIPT_DIR . '/' . $rel;
	return file_exists( $p ) ? (string) filemtime( $p ) : DIPT_VERSION;
}

/** Nombre de la hoja propia de lo que se ve. */
function dipt_hoja_pagina() {
	if ( is_404() ) return 'error404';
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) return 'tienda';
	$clave = dipt_pagina_actual();
	if ( $clave ) {
		$plantilla = dipt_paginas()[ $clave ]['plantilla'] ?? '';
		if ( in_array( $plantilla, array( 'hosteleria', 'transporte', 'industria', 'laboratorios', 'eventos' ), true ) ) return 'sector-' . $plantilla;
		return $plantilla;
	}
	if ( is_singular( 'post' ) ) return 'guia';
	if ( is_home() || is_archive() ) return 'guias';
	return 'pagina';
}

add_action( 'wp_enqueue_scripts', static function () {
	$base = dipt_asset( 'assets/css/base.css' );
	wp_enqueue_style( 'dipt-base', DIPT_URI . '/' . $base, array(), dipt_ver( $base ) );

	$hoja = dipt_hoja_pagina();
	$css  = dipt_asset( "assets/css/paginas/{$hoja}.css" );
	if ( file_exists( DIPT_DIR . '/' . $css ) ) wp_enqueue_style( 'dipt-pagina', DIPT_URI . '/' . $css, array( 'dipt-base' ), dipt_ver( $css ) );

	$js = dipt_asset( 'assets/js/movimiento.js' );
	wp_enqueue_script( 'dipt-movimiento', DIPT_URI . '/' . $js, array(), dipt_ver( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script( 'dipt-movimiento', 'window.DIPT=' . wp_json_encode( dipt_config_js() ) . ';', 'before' );

	$propio = dipt_asset( "assets/js/paginas/{$hoja}.js" );
	if ( file_exists( DIPT_DIR . '/' . $propio ) ) {
		wp_enqueue_script( 'dipt-pagina', DIPT_URI . '/' . $propio, array( 'dipt-movimiento' ), dipt_ver( $propio ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
}, 20 );

/* Los scripts propios son pequeños y pintan datos en directo: que WP Rocket no los retrase. */
add_filter( 'script_loader_tag', static function ( $tag, $handle ) {
	if ( 0 === strpos( $handle, 'dipt-' ) && false === strpos( $tag, 'nowprocket' ) ) $tag = str_replace( '<script ', '<script nowprocket ', $tag );
	return $tag;
}, 20, 2 );

/* ---------- <head>: fuentes precargadas, color del navegador y clases antes del primer pintado ---------- */
add_action( 'wp_head', static function () {
	foreach ( array( 'instrument-serif.woff2', 'geist.woff2' ) as $f ) {
		printf( '<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n", esc_url( DIPT_URI . '/assets/fonts/' . $f ) );
	}
	echo '<meta name="theme-color" content="#0B141C">' . "\n";
	// js: hay JavaScript · quieto: el visitante pidió menos movimiento · ligero: equipo modesto o ahorro de datos
	echo "<script nowprocket data-cfasync=\"false\">(function(d,n){var h=d.documentElement,c=n.connection||{};h.classList.add('js');"
		. "if(matchMedia('(prefers-reduced-motion: reduce)').matches)h.classList.add('quieto');"
		. "if(c.saveData||/2g/.test(c.effectiveType||''))h.classList.add('ligero');})(document,navigator);</script>\n";
}, 1 );

/* Precarga de la imagen principal de cada página (LCP). Las plantillas la declaran con dipt_precargar(). */
function dipt_precargar( $nombre, $sizes = '100vw' ) {
	$GLOBALS['dipt_precarga'] = array( $nombre, $sizes );
}
add_action( 'wp_head', static function () {
	if ( empty( $GLOBALS['dipt_precarga'] ) ) return;
	list( $nombre, $sizes ) = $GLOBALS['dipt_precarga'];
	$m = dipt_fotos_manifiesto()[ $nombre ] ?? null;
	if ( ! $m ) return;
	$srcset = implode( ', ', array_map( static fn( $w ) => DIPT_URI . "/assets/img/fotos/{$nombre}-{$w}.webp {$w}w", $m['anchos'] ) );
	printf( '<link rel="preload" as="image" type="image/webp" imagesrcset="%s" imagesizes="%s" fetchpriority="high">' . "\n", esc_attr( $srcset ), esc_attr( $sizes ) );
}, 3 );

/* Favicon del tema si no hay "Icono del sitio" en Ajustes → Generales. */
add_action( 'wp_head', static function () {
	if ( has_site_icon() ) return;
	printf( '<link rel="icon" href="%1$s" type="image/svg+xml"><link rel="icon" href="%2$s" sizes="32x32"><link rel="apple-touch-icon" href="%3$s">' . "\n",
		esc_url( dipt_img( 'favicon.svg' ) ), esc_url( dipt_img( 'favicon-32.png' ) ), esc_url( dipt_img( 'apple-touch-icon.png' ) ) );
}, 4 );
