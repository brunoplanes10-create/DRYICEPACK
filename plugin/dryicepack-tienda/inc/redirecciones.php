<?php
/**
 * Redirecciones 301 de direcciones antiguas que Google todavía muestra (Search Console, 12 meses a 27/09/2026).
 * Solo actúan si la dirección da 404: nunca tapan una página que exista.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_mapa_redirecciones() {
	$producto = '/producto/hielo-seco/';
	return array(
		// Versión antigua con /es/
		'es'                                  => '/',
		'es/envios-y-plazos'                  => '/envios-y-plazos/',
		'es/comprar-hielo-seco'               => $producto,
		'es/contacto'                         => '/contacto/',
		'es/que-es'                           => '/que-es-el-hielo-seco/',
		'es/aplicaciones'                     => '/aplicaciones-del-hielo-seco/',
		'es/seguridad'                        => '/seguridad-del-hielo-seco/',
		'es/programar-suministro'             => '/empresas/',
		'es/faq'                              => '/envios-y-plazos/',
		'es/category/sin-categoria'           => '/guias/',
		// Versión antigua en catalán
		'ca/que-es'                           => '/ca/que-es-el-gel-sec/',
		'ca/contacto'                         => '/ca/contacte/',
		'ca/envios-y-plazos'                  => '/ca/enviaments/',
		'ca/aplicaciones'                     => '/ca/aplicacions/',
		'ca/comprar-hielo-seco'               => '/ca/comprar-gel-sec/',
		'ca/seguridad'                        => '/ca/seguretat-del-gel-sec/',
		'ca/programar-suministro'             => '/ca/empreses/',
		'ca/category/sin-categoria'           => '/ca/',
		// Atajos y páginas renombradas
		'que-es'                              => '/que-es-el-hielo-seco/',
		'aplicaciones'                        => '/aplicaciones-del-hielo-seco/',
		'seguridad'                           => '/seguridad-del-hielo-seco/',
		'programar-suministro'                => '/empresas/',
		'programar-suministro-de-hielo-seco'  => '/empresas/',
		'comprar-hielo-seco'                  => $producto,
		'tienda'                              => $producto,
		'hola-mundo'                          => '/guias/',
		'category/sin-categoria'              => '/guias/',
		'seguridad-hielo-seco-uso-y-manipulacion-–-dryicepack' => '/seguridad-del-hielo-seco/',
		// Documentos legales que eran PDF (cuando se borren del servidor)
		'wp-content/uploads/2025/10/aviso-legal-1-1.pdf'          => '/aviso-legal/',
		'wp-content/uploads/2025/10/politica-privacidad-2-1.pdf'  => '/politica-de-privacidad/',
		'wp-content/uploads/2025/10/politica-de-cookies-1-1.pdf'  => '/politica-de-cookies/',
		'wp-content/uploads/2025/12/condiciones-de-venta-y-devoluciones-–-dry-ice-pack-1.pdf' => '/condiciones-de-venta/',
	);
}

add_action( 'template_redirect', static function () {
	if ( ! is_404() ) return;
	$ruta = strtolower( rawurldecode( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ) );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	$ruta = trim( $ruta, '/' );
	if ( $base && 0 === strpos( $ruta, $base ) ) $ruta = trim( substr( $ruta, strlen( $base ) ), '/' );
	$mapa = dip_mapa_redirecciones();

	$destino = $mapa[ $ruta ] ?? ( dip_paginas_sobrantes()[ $ruta ] ?? null ); // también cuando las páginas sobrantes se borren
	if ( ! $destino && preg_match( '#^(es/|ca/)?producto/pack-de-\d+kg$#', $ruta ) ) $destino = '/producto/hielo-seco/';
	if ( ! $destino && preg_match( '#^(es/|ca/)?categoria-producto/#', $ruta ) ) $destino = '/producto/hielo-seco/';
	if ( ! $destino && preg_match( '#^es/(.+)$#', $ruta, $m ) && isset( $mapa[ $m[1] ] ) ) $destino = $mapa[ $m[1] ];
	if ( ! $destino ) return;

	wp_safe_redirect( home_url( $destino ), 301, 'Dryicepack' );
	exit;
}, 1 );

/**
 * Páginas que existen en WordPress pero sobran (auditoría del 10/11/2026): duplicados creados por error y la página
 * antigua de «Programar suministro» (HTML roto, formulario viejo, enlace al PDF antiguo). Se redirigen aunque existan,
 * para no depender de que alguien las borre, y salen del sitemap (más abajo). Si se borran, la redirección sigue igual.
 */
function dip_paginas_sobrantes() {
	// Una versión anterior de «Crear las que faltan» creó páginas vacías con el slug sin prefijo (estrategia/SEO-CONTENIDOS.md, 7.2)
	$zonas = array( 'barcelona', 'badalona', 'cornella', 'el-prat', 'granollers', 'martorell', 'mataro', 'mollet', 'rubi', 'sabadell', 'sant-boi', 'sant-cugat', 'terrassa', 'maresme', 'baix-llobregat', 'valles-occidental', 'valles-oriental', 'transporte', 'hosteleria', 'industria', 'laboratorios', 'eventos', 'halloween' );
	$mapa  = array();
	foreach ( $zonas as $z ) $mapa[ $z ] = '/hielo-seco-' . $z . '/';
	return $mapa + array(
		'lhospitalet'                        => '/hielo-seco-hospitalet/',
		'seguridad'                          => '/seguridad-del-hielo-seco/',
		'como-hacer-niebla-con-hielo-seco'   => '/guias/como-hacer-niebla-con-hielo-seco/',
		'hielo-seco-para-gorros-frios'       => '/guias/hielo-seco-gorros-frios-quimioterapia/',
		'factura-2'                          => '/factura/',
		'programar-suministro-de-hielo-seco' => '/empresas/',
		'donde-comprar-hielo-seco'           => '/guias/donde-comprar-hielo-seco/',
	);
}
add_action( 'template_redirect', static function () {
	// La categoría de entradas de serie (la web no tiene blog): /category/sin-categoria/ y /ca/category/sin-categoria/
	if ( is_category() && in_array( (string) get_queried_object()->slug, array( 'sin-categoria', 'uncategorized' ), true ) ) {
		wp_safe_redirect( home_url( '/guias/' ), 301, 'Dryicepack' );
		exit;
	}
	if ( ! is_page() ) return;
	$pagina = get_queried_object();
	if ( ! $pagina instanceof WP_Post || $pagina->post_parent ) return; // solo las de primer nivel: /guias/donde-comprar-hielo-seco/ es la buena
	$destino = dip_paginas_sobrantes()[ $pagina->post_name ] ?? null;
	if ( ! $destino ) return;
	wp_safe_redirect( home_url( $destino ), 301, 'Dryicepack' );
	exit;
}, 1 );

/**
 * Fuera del sitemap de Rank Math y sin indexar: las páginas sobrantes, la de la tienda de WooCommerce (redirige a la
 * ficha), el carrito, el pago, la cuenta y /factura/ (solo sirve con el enlace firmado del email).
 */
function dip_pagina_sin_indexar( $pagina ) {
	if ( ! $pagina instanceof WP_Post || 'page' !== $pagina->post_type ) return false;
	if ( ! $pagina->post_parent && isset( dip_paginas_sobrantes()[ $pagina->post_name ] ) ) return true;
	if ( 'factura' === $pagina->post_name && ! $pagina->post_parent ) return true;
	if ( function_exists( 'wc_get_page_id' ) ) {
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $wc ) {
			if ( (int) wc_get_page_id( $wc ) === (int) $pagina->ID ) return true;
		}
	}
	return false;
}
add_filter( 'rank_math/sitemap/entry', static function ( $url, $tipo, $objeto ) {
	return ( 'post' === $tipo && dip_pagina_sin_indexar( $objeto ) ) ? false : $url;
}, 10, 3 );
/*
 * Sitemap de productos: la tienda (/comprar-hielo-seco/) redirige a la ficha, así que fuera; y la ficha entra aunque en
 * producción conserve un «noindex» antiguo guardado en Rank Math (el tema ya fuerza index en la página, pero el sitemap
 * de Rank Math lee el dato guardado y la dejaba fuera: 10/11/2026). Solo se añaden los productos que Rank Math excluye.
 */
add_filter( 'rank_math/sitemap/post_type_archive_link', static function ( $enlace, $tipo ) {
	return 'product' === $tipo ? false : $enlace;
}, 10, 2 );
add_filter( 'rank_math/sitemap/product_content', static function ( $xml ) {
	$ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'fields'         => 'ids',
		'posts_per_page' => 20,
		'meta_query'     => array( array( 'key' => 'rank_math_robots', 'value' => 'noindex', 'compare' => 'LIKE' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	foreach ( $ids as $id ) {
		$xml .= "\t<url>\n\t\t<loc>" . esc_url( get_permalink( $id ) ) . "</loc>\n\t\t<lastmod>" . esc_html( get_post_modified_time( 'c', true, $id ) ) . "</lastmod>\n\t</url>\n";
	}
	return $xml;
} );

add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	if ( is_page() && dip_pagina_sin_indexar( get_queried_object() ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );

/* ?post_type=product&modalidad=… y ?product=pack-de-… (enlaces viejos con parámetros) */
add_action( 'template_redirect', static function () {
	if ( isset( $_GET['modalidad'] ) || ( isset( $_GET['product'] ) && preg_match( '/^pack-de-\d+kg$/', sanitize_title( wp_unslash( $_GET['product'] ) ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/producto/hielo-seco/' ), 301, 'Dryicepack' );
		exit;
	}
}, 0 );
