<?php
/**
 * SEO en el tema:
 * - Título y descripción de cada página del registro (contenido/*.php → 'seo'), por encima de lo guardado en Rank Math
 *   para que texto y metadatos no se desalineen. Rank Math sigue con canonical, sitemap, robots y Open Graph.
 * - Datos estructurados que Rank Math gratis no genera o no puede saber: LocalBusiness, FAQPage, BreadcrumbList
 *   y Product en las páginas de compra en catalán e inglés.
 * - Product (el de Rank Math en castellano y el del tema en catalán e inglés): una oferta por pack y formato con el envío
 *   real por peso, el plazo y la política de devolución (dipt_schema_ofertas).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_seo_actual() {
	$clave = dipt_pagina_actual();
	// Entradas del blog: los metadatos de Rank Math, también si Rank Math no está activo
	if ( ! $clave && is_singular( 'post' ) ) {
		$id = get_queried_object_id();
		$t  = (string) get_post_meta( $id, 'rank_math_title', true );
		$d  = (string) get_post_meta( $id, 'rank_math_description', true );
		return ( $t || $d ) && false === strpos( $t . $d, '%' ) ? array( 'titulo' => $t, 'descripcion' => $d ) : null;
	}
	if ( ! $clave ) return null;
	$p = dipt_paginas()[ $clave ];
	if ( 'zona' === $p['plantilla'] && function_exists( 'dipt_seo_zona' ) ) return dipt_seo_zona( $p['zona'] );
	if ( 'provincia' === $p['plantilla'] && function_exists( 'dipt_seo_lugar' ) ) return dipt_seo_lugar( $p['lugar'] ?? null );
	if ( 'legal' === $p['plantilla'] ) return dipt_contenido( 'legal-' . $clave )['seo'] ?? null;
	if ( 'guia' === $p['plantilla'] ) return dipt_guia( $p['guia'] )['seo'] ?? null;
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
// BreadcrumbList: lo genera el tema con las mismas migas que se ven (las zonas y las guías no siguen la jerarquía de
// páginas de WordPress, que es la que usaría Rank Math).
add_filter( 'rank_math/json_ld/breadcrumbs_enabled', '__return_false' );

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
		'description' => 'Venta de hielo seco en pellets de 3 mm y nuggets de 16 mm. Envío por mensajería a la península con entrega al día siguiente antes de las 14:00 si se pide antes de las 12:00 de lunes a jueves, y recogida en la nave de Mataró de lunes a sábado, con pago en efectivo.',
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
		'geo'         => array( '@type' => 'GeoCoordinates', 'latitude' => dipt_zona_nave()[0], 'longitude' => dipt_zona_nave()[1] ),
		// España peninsular: sin Baleares, Canarias, Ceuta ni Melilla (la descripción lo dice; aquí, el país)
		'areaServed'  => array( '@type' => 'Country', 'name' => 'ES' ),
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

	// Con Rank Math, el negocio va dentro de su grafo (filtro rank_math/json_ld, abajo): un solo negocio, no dos
	if ( ! defined( 'RANK_MATH_VERSION' ) && in_array( $clave, array( 'inicio', 'contacto' ), true ) ) dipt_jsonld( dipt_schema_negocio() );

	// Guías: Article con fechas reales y la marca como autora y editora
	$pag = $clave ? ( dipt_paginas()[ $clave ] ?? null ) : null;
	if ( $pag && 'guia' === $pag['plantilla'] && is_singular() ) {
		$g = dipt_guia( $pag['guia'] );
		if ( $g ) {
			$org = array( '@type' => 'Organization', '@id' => home_url( defined( 'RANK_MATH_VERSION' ) ? '/#organization' : '/#negocio' ), 'name' => 'DryIcePack', 'url' => home_url( '/' ) );
			dipt_jsonld( array(
				'@context'         => 'https://schema.org',
				'@type'            => 'Article',
				'headline'         => wp_strip_all_tags( (string) $g['titulo'] ),
				'description'      => wp_strip_all_tags( (string) ( $g['seo']['descripcion'] ?? $g['extracto'] ?? '' ) ),
				'image'            => ! empty( $g['foto'] ) ? dipt_foto_url( $g['foto'], 1000 ) : dipt_foto_url( 'caja-16mm-niebla', 1000 ),
				'datePublished'    => get_post_time( 'c', true, get_queried_object_id() ),
				'dateModified'     => get_post_modified_time( 'c', true, get_queried_object_id() ),
				'author'           => $org,
				'publisher'        => $org,
				'mainEntityOfPage' => get_permalink( get_queried_object_id() ),
				'inLanguage'       => array( 'es' => 'es-ES', 'ca' => 'ca-ES', 'en' => 'en-GB' )[ dipt_idioma() ] ?? 'es-ES',
			) );
		}
	}

	$faq = $GLOBALS['dipt_faq_schema'] ?? array();
	if ( count( $faq ) >= 2 ) {
		dipt_jsonld( array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array_map( static fn( $f ) => array( '@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ) ), $faq ),
		) );
	}

	// Siempre el del tema, que coincide con las migas visibles. Rank Math en producción tiene las migas desactivadas
	// (no genera BreadcrumbList) y, si alguien las activa, su lista queda apagada con el filtro de arriba para no duplicarla.
	$migas = $GLOBALS['dipt_migas'] ?? array();
	if ( count( $migas ) >= 2 ) {
		dipt_jsonld( array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array_map( static fn( $m, $i ) => array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $m[0], 'item' => $m[1] ), $migas, array_keys( $migas ) ),
		) );
	}

	// Páginas de compra en catalán e inglés: son páginas normales, así que el producto lo describe el tema.
	if ( 'producto' === $clave && 'es' !== dipt_idioma() && ! ( function_exists( 'is_product' ) && is_product() ) ) {
		dipt_jsonld( array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => 'en' === dipt_idioma() ? 'Dry ice (3 mm pellets and 16 mm nuggets)' : 'Gel sec (pèl·lets de 3 mm i nuggets de 16 mm)',
			'image'       => array( dipt_foto_url( 'caja-16mm-niebla', 1000 ), dipt_foto_url( 'cenital-3mm', 900 ) ),
			'brand'       => array( '@type' => 'Brand', 'name' => 'DryIcePack' ),
			// Una oferta por pack y formato, con envío y devoluciones (dipt_schema_ofertas, abajo)
			'offers'      => dipt_schema_ofertas( dipt_url( 'producto' ) ),
		) );
	}
}, 30 );

/* ---------- Product: una oferta por pack y formato, con envío y devoluciones (10/11/2026) ----------
 * Las fichas de comerciante de Google leen el envío y la devolución en cada Offer (no en un AggregateOffer).
 * - Precio con IVA, el que se ve en la ficha; envío de la mensajería para el peso de ese pack (hielo + 1 kg de caja), con IVA.
 * - Sale el mismo día laborable si se pide antes del corte (12:00, hora de Madrid) y llega al día siguiente: manipulación
 *   0 días y tránsito 1 día, de lunes a viernes. Sin envío a Baleares, Canarias, Ceuta ni Melilla (doesNotShip).
 * - Sin derecho de desistimiento: el hielo seco se sublima (condiciones de venta; «¿Puedo devolverlo?» de la ficha).
 * En castellano el Product lo genera Rank Math: se amplía su entidad (sin crear otra) con sus filtros.
 */
function dipt_schema_devolucion() {
	return array(
		'@type'                => 'MerchantReturnPolicy',
		'applicableCountry'    => 'ES',
		'returnPolicyCategory' => 'https://schema.org/MerchantReturnNotPermitted',
		'merchantReturnLink'   => dipt_url( 'condiciones' ),
	);
}

function dipt_schema_envio( $kg ) {
	$coste = round( dipt_coste_envio( (float) $kg, 1 ) * dipt_factor_iva(), 2 );
	$corte = sprintf( '%02d:%02d:00', defined( 'DIP_CUTOFF_HOUR' ) ? (int) DIP_CUTOFF_HOUR : 12, defined( 'DIP_CUTOFF_MINUTE' ) ? (int) DIP_CUTOFF_MINUTE : 0 )
		. ( new DateTimeImmutable( 'now', new DateTimeZone( 'Europe/Madrid' ) ) )->format( 'P' );
	$dias  = array_map( static fn( $d ) => 'https://schema.org/' . $d, array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ) );
	$fuera = array();
	foreach ( array( '07', '35', '38', '51', '52' ) as $pre ) { // Baleares, Las Palmas, Santa Cruz de Tenerife, Ceuta y Melilla (dip_cp_fuera_de_peninsula)
		$fuera[] = array( '@type' => 'DefinedRegion', 'addressCountry' => 'ES', 'postalCodeRange' => array( '@type' => 'PostalCodeRangeSpecification', 'postalCodeBegin' => $pre . '000', 'postalCodeEnd' => $pre . '999' ) );
	}
	return array(
		array(
			'@type'               => 'OfferShippingDetails',
			'shippingRate'        => array( '@type' => 'MonetaryAmount', 'value' => number_format( $coste, 2, '.', '' ), 'currency' => 'EUR' ),
			'shippingDestination' => array( '@type' => 'DefinedRegion', 'addressCountry' => 'ES' ),
			'deliveryTime'        => array(
				'@type'        => 'ShippingDeliveryTime',
				'cutoffTime'   => $corte,
				'businessDays' => array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $dias ),
				'handlingTime' => array( '@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY' ),
				'transitTime'  => array( '@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 1, 'unitCode' => 'DAY' ),
			),
		),
		array(
			'@type'               => 'OfferShippingDetails',
			'doesNotShip'         => true,
			'shippingDestination' => $fuera,
		),
	);
}

/** Ofertas del Product: una por pack y formato que se vende ahora (dipt_variaciones: con stock y comprables). */
function dipt_schema_ofertas( $url, $vendedor = null ) {
	$base     = array( 'es' => 'Hielo seco', 'ca' => 'Gel sec', 'en' => 'Dry ice' )[ dipt_idioma() ] ?? 'Hielo seco';
	$politica = dipt_schema_devolucion();
	$ofertas  = array();
	foreach ( dipt_variaciones() as $v ) {
		$p = dipt_precio_pack( (float) $v['kg'] );
		if ( $p['con_iva'] <= 0 ) continue;
		$precio = number_format( $p['con_iva'], 2, '.', '' );
		$o      = array(
			'@type'                   => 'Offer',
			'name'                    => $base . ' · ' . ( 0 + $v['kg'] ) . ' kg · ' . ( '16mm' === $v['formato'] ? '16 mm' : '3 mm' ),
			'price'                   => $precio,
			'priceCurrency'           => 'EUR',
			'priceSpecification'      => array( '@type' => 'UnitPriceSpecification', 'price' => $precio, 'priceCurrency' => 'EUR', 'valueAddedTaxIncluded' => true ),
			'availability'            => 'https://schema.org/InStock',
			'itemCondition'           => 'https://schema.org/NewCondition',
			'url'                     => $url,
			'shippingDetails'         => dipt_schema_envio( (float) $v['kg'] ),
			'hasMerchantReturnPolicy' => $politica,
		);
		if ( $vendedor ) $o['seller'] = $vendedor;
		$ofertas[] = $o;
	}
	return $ofertas;
}

function dipt_schema_es_producto( $entidad ) {
	return is_array( $entidad ) && in_array( 'Product', (array) ( $entidad['@type'] ?? '' ), true );
}

/** La entidad Product de Rank Math con las ofertas del tema (conserva su vendedor y su URL). Solo una vez. */
function dipt_schema_ampliar_producto( $entidad ) {
	if ( ! dipt_schema_es_producto( $entidad ) ) return $entidad;
	$o = is_array( $entidad['offers'] ?? null ) ? $entidad['offers'] : array();
	if ( isset( $o[0]['shippingDetails'] ) ) return $entidad;
	$ofertas = dipt_schema_ofertas( ! empty( $o['url'] ) ? (string) $o['url'] : dipt_url( 'producto' ), $o['seller'] ?? null );
	if ( $ofertas ) $entidad['offers'] = $ofertas;
	// Nombre y descripción reales (Rank Math usaba el title guardado, «Comprar Hielo Seco – DryIcePack», y una descripción antigua)
	$entidad['name']  = 'Hielo seco (pellets de 3 mm y nuggets de 16 mm)';
	$entidad['brand'] = array( '@type' => 'Brand', 'name' => 'DryIcePack' );
	$s = dipt_seo_actual();
	if ( ! empty( $s['descripcion'] ) ) $entidad['description'] = wp_strip_all_tags( (string) $s['descripcion'] );
	return $entidad;
}

/** Rank Math: su Organization pasa a ser el negocio del tema (mismo @id, para no romper sus referencias) y la portada, WebPage. */
function dipt_schema_unificar_negocio( $nodo ) {
	if ( ! is_array( $nodo ) ) return $nodo;
	$tipos = (array) ( $nodo['@type'] ?? '' );
	if ( array_intersect( $tipos, array( 'Organization', 'LocalBusiness', 'Store', 'Corporation' ) ) && false !== strpos( (string) ( $nodo['@id'] ?? '' ), '#organization' ) ) {
		$negocio = dipt_schema_negocio();
		unset( $negocio['@context'], $negocio['@id'] );
		return array_merge( $nodo, $negocio );
	}
	if ( in_array( 'AboutPage', $tipos, true ) && is_front_page() ) $nodo['@type'] = 'WebPage';
	return $nodo;
}
add_filter( 'rank_math/snippet/rich_snippet_product_entity', 'dipt_schema_ampliar_producto', 20 );
add_filter( 'rank_math/json_ld', static function ( $datos ) {
	if ( ! is_array( $datos ) ) return $datos;
	foreach ( $datos as $k => $nodo ) {
		if ( dipt_schema_es_producto( $nodo ) ) $datos[ $k ] = dipt_schema_ampliar_producto( $nodo );
		else $datos[ $k ] = dipt_schema_unificar_negocio( $nodo );
	}
	if ( isset( $datos['@graph'] ) && is_array( $datos['@graph'] ) ) {
		foreach ( $datos['@graph'] as $k => $nodo ) {
			if ( dipt_schema_es_producto( $nodo ) ) $datos['@graph'][ $k ] = dipt_schema_ampliar_producto( $nodo );
			else $datos['@graph'][ $k ] = dipt_schema_unificar_negocio( $nodo );
		}
	}
	return $datos;
}, 99 );
