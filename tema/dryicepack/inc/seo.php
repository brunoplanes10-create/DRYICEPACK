<?php
/**
 * Datos estructurados que Rank Math (gratis) no genera:
 *  - FAQPage con las preguntas que se ven en cada página (dipt_faq las registra).
 *  - LocalBusiness de la nave de Mataró en inicio y contacto (SEO local y GEO: buscadores y asistentes de IA
 *    necesitan dirección, horario, zona de servicio y contacto coherentes).
 * Rank Math sigue encargándose de títulos, descripciones, canonical, sitemap y migas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_jsonld( $datos ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

add_action( 'wp_footer', static function () {
	/* FAQPage: una por página, con las preguntas visibles */
	$faq = $GLOBALS['dipt_faq_schema'] ?? array();
	if ( count( $faq ) >= 2 ) {
		dipt_jsonld( array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array_map( static fn( $f ) => array(
				'@type'          => 'Question',
				'name'           => $f['q'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ),
			), $faq ),
		) );
	}

	/* LocalBusiness: inicio y contacto */
	if ( ! ( is_front_page() || is_page( 'contacto' ) ) ) return;
	if ( apply_filters( 'dipt_desactivar_localbusiness', false ) ) return;
	$c = dipt_contacto();
	dipt_jsonld( array(
		'@context'    => 'https://schema.org',
		'@type'       => array( 'LocalBusiness', 'Store' ),
		'@id'         => home_url( '/#empresa' ),
		'name'        => 'DryIcePack',
		'legalName'   => $c['empresa'],
		'taxID'       => $c['cif'],
		'description' => 'Venta y suministro de hielo seco en pellets de 3 mm y nuggets de 16 mm, con entrega en 24 h en la España peninsular y recogida en la nave de Mataró.',
		'url'         => home_url( '/' ),
		'telephone'   => '+34 936 73 76 41',
		'email'       => $c['email'],
		'image'       => dipt_img( 'caja-cenital-800.webp' ),
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
		'areaServed'  => array( '@type' => 'Country', 'name' => 'España (península)' ),
		'makesOffer'  => array(
			array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Product', 'name' => 'Hielo seco en pellets de 3 mm' ) ),
			array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Product', 'name' => 'Hielo seco en nuggets de 16 mm' ) ),
			array( '@type' => 'Offer', 'itemOffered' => array( '@type' => 'Service', 'name' => 'Suministro programado de hielo seco (semanal, quincenal o mensual)' ) ),
		),
	) );
}, 30 );
