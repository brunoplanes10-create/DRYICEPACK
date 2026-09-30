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

	$destino = $mapa[ $ruta ] ?? null;
	if ( ! $destino && preg_match( '#^(es/|ca/)?producto/pack-de-\d+kg$#', $ruta ) ) $destino = '/producto/hielo-seco/';
	if ( ! $destino && preg_match( '#^(es/|ca/)?categoria-producto/#', $ruta ) ) $destino = '/producto/hielo-seco/';
	if ( ! $destino && preg_match( '#^es/(.+)$#', $ruta, $m ) && isset( $mapa[ $m[1] ] ) ) $destino = $mapa[ $m[1] ];
	if ( ! $destino ) return;

	wp_safe_redirect( home_url( $destino ), 301, 'Dryicepack' );
	exit;
}, 1 );

/* ?post_type=product&modalidad=… y ?product=pack-de-… (enlaces viejos con parámetros) */
add_action( 'template_redirect', static function () {
	if ( isset( $_GET['modalidad'] ) || ( isset( $_GET['product'] ) && preg_match( '/^pack-de-\d+kg$/', sanitize_title( wp_unslash( $_GET['product'] ) ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/producto/hielo-seco/' ), 301, 'Dryicepack' );
		exit;
	}
}, 0 );
