<?php
/**
 * SEO en el tema:
 * - Título y descripción de cada página del registro (contenido/*.php → 'seo'), por encima de lo guardado en Rank Math
 *   para que texto y metadatos no se desalineen. Rank Math sigue con canonical, sitemap, robots y Open Graph.
 * - Datos estructurados que Rank Math gratis no genera o no puede saber: LocalBusiness, FAQPage, BreadcrumbList
 *   y Product en las páginas de compra en catalán e inglés.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_seo_actual() {
	$clave = dipt_pagina_actual();
	// Guías (entradas): los metadatos que dipt_crear_guias() guarda para Rank Math, también si Rank Math no está activo
	if ( ! $clave && is_singular( 'post' ) ) {
		$id = get_queried_object_id();
		$t  = (string) get_post_meta( $id, 'rank_math_title', true );
		$d  = (string) get_post_meta( $id, 'rank_math_description', true );
		return ( $t || $d ) && false === strpos( $t . $d, '%' ) ? array( 'titulo' => $t, 'descripcion' => $d ) : null;
	}
	if ( ! $clave ) return null;
	$p = dipt_paginas()[ $clave ];
	if ( 'zona' === $p['plantilla'] && function_exists( 'dipt_seo_zona' ) ) return dipt_seo_zona( $p['zona'] );
	if ( 'legal' === $p['plantilla'] ) return dipt_contenido( 'legal-' . $clave )['seo'] ?? null;
	return dipt_contenido( $p['plantilla'] === 'inicio' ? 'inicio' : $clave )['seo'] ?? null;
}

add_filter( 'rank_math/frontend/title', static function ( $titulo ) {
	$s = dipt_seo_actual();
	return ! empty( $s['titulo'] ) ? $s['titulo'] : $titulo;
}, 50 );
add_filter( 'rank_math/frontend/description', static function ( $desc ) {
	$s = dipt_seo_actual();
	return ! empty( $s['descripcion'] ) ? $s['descripcion'] : $desc;
}, 50 );
foreach ( array( 'rank_math/opengraph/facebook/og_title', 'rank_math/opengraph/twitter/title' ) as $dipt_f ) {
	add_filter( $dipt_f, static function ( $t ) {
		$s = dipt_seo_actual();
		return ! empty( $s['titulo'] ) ? $s['titulo'] : $t;
	}, 50 );
}
foreach ( array( 'rank_math/opengraph/facebook/og_description', 'rank_math/opengraph/twitter/description' ) as $dipt_f ) {
	add_filter( $dipt_f, static function ( $t ) {
		$s = dipt_seo_actual();
		return ! empty( $s['descripcion'] ) ? $s['descripcion'] : $t;
	}, 50 );
}
add_filter( 'rank_math/opengraph/facebook/og_locale', static function ( $l ) {
	return array( 'es' => 'es_ES', 'ca' => 'ca_ES', 'en' => 'en_GB' )[ dipt_idioma() ] ?? $l;
} );

/* Sin Rank Math: el tema pone título y descripción. */
add_filter( 'pre_get_document_title', static function ( $t ) {
	if ( defined( 'RANK_MATH_VERSION' ) ) return $t;
	$s = dipt_seo_actual();
	return ! empty( $s['titulo'] ) ? $s['titulo'] : $t;
}, 50 );
add_action( 'wp_head', static function () {
	if ( defined( 'RANK_MATH_VERSION' ) ) return;
	$s = dipt_seo_actual();
	if ( ! empty( $s['descripcion'] ) ) echo '<meta name="description" content="' . esc_attr( $s['descripcion'] ) . '">' . "\n";
}, 5 );

/* ---------- Datos estructurados ---------- */
function dipt_jsonld( $datos ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

function dipt_schema_negocio() {
	$c = dipt_contacto();
	return array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Store',
		'@id'         => home_url( '/#negocio' ),
		'name'        => 'DryIcePack',
		'legalName'   => $c['empresa'],
		'taxID'       => $c['cif'],
		'description' => 'Venta de hielo seco en pellets de 3 mm y nuggets de 16 mm. Envío por mensajería a la península con entrega al día siguiente por la mañana si se pide antes de las 12:00, y recogida gratis en la nave de Mataró.',
		'url'         => home_url( '/' ),
		'telephone'   => '+34936737641',
		'email'       => $c['email'],
		'image'       => dipt_foto_url( 'caja-16mm-niebla', 1000 ),
		'logo'        => dipt_img( 'logo-dryicepack.webp' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $c['direccion'],
			'postalCode'      => $c['cp'],
			'addressLocality' => $c['localidad'],
			'addressRegion'   => $c['provincia'],
			'addressCountry'  => 'ES',
		),
		'openingHoursSpecification' => array( array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
			'opens'     => '09:00',
			'closes'    => '18:00',
		) ),
		'areaServed'  => array( '@type' => 'Country', 'name' => 'Spain' ),
		'contactPoint' => array( array(
			'@type'             => 'ContactPoint',
			'telephone'         => '+34936737641',
			'contactType'       => 'sales',
			'availableLanguage' => array( 'es', 'ca', 'en' ),
		) ),
		'parentOrganization' => array( '@type' => 'Organization', 'name' => $c['empresa'], 'url' => 'https://indunova.es/' ),
	);
}

add_action( 'wp_footer', static function () {
	$clave = dipt_pagina_actual();

	if ( in_array( $clave, array( 'inicio', 'contacto' ), true ) ) dipt_jsonld( dipt_schema_negocio() );

	$faq = $GLOBALS['dipt_faq_schema'] ?? array();
	if ( count( $faq ) >= 2 ) {
		dipt_jsonld( array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array_map( static fn( $f ) => array( '@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ) ), $faq ),
		) );
	}

	$migas = $GLOBALS['dipt_migas'] ?? array();
	if ( count( $migas ) >= 2 && ! defined( 'RANK_MATH_VERSION' ) ) {
		dipt_jsonld( array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array_map( static fn( $m, $i ) => array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $m[0], 'item' => $m[1] ), $migas, array_keys( $migas ) ),
		) );
	}

	// Páginas de compra en catalán e inglés: son páginas normales, así que el producto lo describe el tema.
	if ( 'producto' === $clave && 'es' !== dipt_idioma() && ! ( function_exists( 'is_product' ) && is_product() ) ) {
		$packs  = dipt_packs();
		$iva    = dipt_factor_iva();
		$precios = array_map( static fn( $p ) => round( $p * $iva, 2 ), $packs );
		dipt_jsonld( array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => 'en' === dipt_idioma() ? 'Dry ice (3 mm pellets and 16 mm nuggets)' : 'Gel sec (pèl·lets de 3 mm i nuggets de 16 mm)',
			'image'       => array( dipt_foto_url( 'caja-16mm-niebla', 1000 ), dipt_foto_url( 'cenital-3mm', 900 ) ),
			'brand'       => array( '@type' => 'Brand', 'name' => 'DryIcePack' ),
			'offers'      => array(
				'@type'         => 'AggregateOffer',
				'priceCurrency' => 'EUR',
				'lowPrice'      => $precios ? min( $precios ) : null,
				'highPrice'     => $precios ? max( $precios ) : null,
				'offerCount'    => count( $precios ),
				'availability'  => 'https://schema.org/InStock',
				'url'           => dipt_url( 'producto' ),
			),
		) );
	}
}, 30 );
