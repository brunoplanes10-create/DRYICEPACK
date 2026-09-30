<?php
/**
 * CSS/JS: base común + uno por página. Versiones minificadas si existen (herramientas/compilar-tema.mjs).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Ruta relativa del archivo a servir: prefiere .min.css/.min.js si existe. */
function dipt_asset( $rel ) {
	$min = preg_replace( '/\.(css|js)$/', '.min.$1', $rel );
	return file_exists( DIPT_DIR . '/' . $min ) ? $min : $rel;
}
function dipt_ver( $rel ) {
	$p = DIPT_DIR . '/' . $rel;
	return file_exists( $p ) ? (string) filemtime( $p ) : DIPT_VERSION;
}

/** Hoja de estilo propia de la página actual (si existe). */
function dipt_css_pagina() {
	if ( is_front_page() ) return 'inicio';
	if ( is_404() ) return 'error404';
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) return 'tienda';
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( file_exists( DIPT_DIR . "/assets/css/paginas/{$slug}.css" ) ) return "paginas/{$slug}";
	}
	return '';
}

add_action( 'wp_enqueue_scripts', static function () {
	$base = dipt_asset( 'assets/css/base.css' );
	wp_enqueue_style( 'dipt-base', DIPT_URI . '/' . $base, array(), dipt_ver( $base ) );

	$pag = dipt_css_pagina();
	if ( $pag ) {
		$css = dipt_asset( "assets/css/{$pag}.css" );
		if ( file_exists( DIPT_DIR . '/' . $css ) ) {
			wp_enqueue_style( 'dipt-pagina', DIPT_URI . '/' . $css, array( 'dipt-base' ), dipt_ver( $css ) );
		}
	}

	$js = dipt_asset( 'assets/js/movimiento.js' );
	wp_enqueue_script( 'dipt-movimiento', DIPT_URI . '/' . $js, array(), dipt_ver( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_add_inline_script( 'dipt-movimiento', 'window.DIPT=' . wp_json_encode( dipt_config_js() ) . ';', 'before' );

	// Piezas interactivas (calculadora, planificador, caja 3D): solo donde se usan
	if ( is_front_page() || is_page( array( 'programar-suministro-de-hielo-seco' ) ) || ( function_exists( 'is_product' ) && is_product() ) ) {
		$herr = dipt_asset( 'assets/js/herramientas.js' );
		wp_enqueue_script( 'dipt-herramientas', DIPT_URI . '/' . $herr, array( 'dipt-movimiento' ), dipt_ver( $herr ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
}, 20 );

// Los scripts propios son pequeños y pintan datos en directo (reloj de corte): que WP Rocket no los retrase.
add_filter( 'script_loader_tag', static function ( $tag, $handle ) {
	if ( 0 === strpos( $handle, 'dipt-' ) && false === strpos( $tag, 'nowprocket' ) ) {
		$tag = str_replace( '<script ', '<script nowprocket ', $tag );
	}
	return $tag;
}, 20, 2 );

/* ---------- <head>: fuente precargada, modo ligero y color del navegador ---------- */
add_action( 'wp_head', static function () {
	printf( '<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n", esc_url( DIPT_URI . '/assets/fonts/open-sans-var.woff2' ) );
	echo '<meta name="theme-color" content="#0F2433">' . "\n";
	// Modo ligero y JS disponible, antes del primer pintado (evita saltos y animaciones pesadas en equipos modestos)
	echo "<script nowprocket data-cfasync=\"false\">(function(d,n){var h=d.documentElement,c=n.connection||{};h.classList.add('js');"
		. "if((n.hardwareConcurrency&&n.hardwareConcurrency<=4)||(n.deviceMemory&&n.deviceMemory<=4)||c.saveData||/2g/.test(c.effectiveType||''))h.classList.add('ligero');"
		. "if(matchMedia('(prefers-reduced-motion: reduce)').matches)h.classList.add('quieto');})(document,navigator);</script>\n";
}, 1 );
