<?php
/**
 * Piezas comunes: fotos con srcset, iconos propios, botones, títulos que suben línea a línea,
 * pregunta/respuesta con datos estructurados y cinta de datos.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- Fotos del tema (assets/img/fotos, generadas por herramientas/preparar-imagenes.mjs) ---------- */
function dipt_fotos_manifiesto() {
	static $m = null;
	if ( null === $m ) {
		$archivo = DIPT_DIR . '/assets/img/fotos/fotos.json';
		$m       = file_exists( $archivo ) ? (array) json_decode( (string) file_get_contents( $archivo ), true ) : array();
	}
	return $m;
}

/**
 * <img> con srcset, ancho y alto reales (sin saltos de página) y carga diferida salvo que sea la imagen principal.
 * $opc: sizes, clase, lcp (bool), atributos (array), recorte ('50% 30%' → object-position)
 */
function dipt_foto( $nombre, $alt, $opc = array() ) {
	$m = dipt_fotos_manifiesto()[ $nombre ] ?? null;
	if ( ! $m ) return '';
	$anchos = $m['anchos'];
	$srcset = implode( ', ', array_map( static fn( $w ) => DIPT_URI . "/assets/img/fotos/{$nombre}-{$w}.webp {$w}w", $anchos ) );
	$mayor  = end( $anchos );
	$medio  = $anchos[ min( 1, count( $anchos ) - 1 ) ];
	$alto   = (int) round( $m['h'] * ( $mayor / $m['w'] ) );
	$lcp    = ! empty( $opc['lcp'] );
	$attrs  = array(
		'src'      => DIPT_URI . "/assets/img/fotos/{$nombre}-{$medio}.webp",
		'srcset'   => $srcset,
		'sizes'    => $opc['sizes'] ?? '100vw',
		'width'    => $mayor,
		'height'   => $alto,
		'alt'      => $alt,
		'decoding' => 'async',
	);
	if ( $lcp ) {
		$attrs['fetchpriority'] = 'high';
		$attrs['loading']       = 'eager';
		$attrs['data-no-lazy']  = '1'; // WP Rocket: no diferir la imagen principal
	} else {
		$attrs['loading'] = 'lazy';
	}
	if ( ! empty( $opc['clase'] ) ) $attrs['class'] = $opc['clase'];
	if ( ! empty( $opc['recorte'] ) ) $attrs['style'] = 'object-position:' . $opc['recorte'];
	foreach ( (array) ( $opc['atributos'] ?? array() ) as $k => $v ) $attrs[ $k ] = $v;
	$html = '<img';
	foreach ( $attrs as $k => $v ) $html .= ' ' . $k . '="' . esc_attr( (string) $v ) . '"';
	return $html . '>';
}

/** URL directa de una foto en un ancho (para fondos o precargas). */
function dipt_foto_url( $nombre, $ancho = null ) {
	$m = dipt_fotos_manifiesto()[ $nombre ] ?? null;
	if ( ! $m ) return '';
	$ancho = $ancho && in_array( $ancho, $m['anchos'], true ) ? $ancho : $m['anchos'][ min( 1, count( $m['anchos'] ) - 1 ) ];
	return DIPT_URI . "/assets/img/fotos/{$nombre}-{$ancho}.webp";
}

function dipt_img( $archivo ) {
	return DIPT_URI . '/assets/img/' . ltrim( $archivo, '/' );
}

/* ---------- Iconos de línea propios (24 × 24, trazo 1,5, redondeado) ---------- */
function dipt_icono( $nombre, $clase = '' ) {
	static $p = array(
		'pellet'     => '<circle cx="6" cy="7.5" r="1.6"/><circle cx="11.5" cy="5.5" r="1.6"/><circle cx="17.5" cy="7" r="1.6"/><circle cx="8.5" cy="12.5" r="1.6"/><circle cx="14.5" cy="12" r="1.6"/><circle cx="5.5" cy="17.5" r="1.6"/><circle cx="11.5" cy="18" r="1.6"/><circle cx="18" cy="17" r="1.6"/>',
		'nugget'     => '<rect x="3" y="4.5" width="10" height="5" rx="2.5" transform="rotate(-16 8 7)"/><rect x="10.5" y="10" width="10" height="5" rx="2.5" transform="rotate(12 15.5 12.5)"/><rect x="3.5" y="15" width="10" height="5" rx="2.5" transform="rotate(-5 8.5 17.5)"/>',
		'caja'       => '<path d="M3.5 9h17v11.5h-17z"/><path d="M2.5 5.5h19V9h-19z"/><path d="M8 13h8"/>',
		'furgoneta'  => '<path d="M2.5 7h12v10h-12zM14.5 10h4l3 3.5V17h-7"/><circle cx="6.5" cy="17.8" r="1.8"/><circle cx="17.5" cy="17.8" r="1.8"/>',
		'reloj'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'guante'     => '<path d="M8.5 21v-5.2L5.7 11.6a1.6 1.6 0 012.6-1.9l1.2 1.4V4.9a1.5 1.5 0 013 0V10V3.9a1.5 1.5 0 013 0V10V5.4a1.5 1.5 0 013 0v8.1c0 2.9-1.3 4.7-3 6V21"/>',
		'ventilar'   => '<path d="M3 8.5h10.5a2.8 2.8 0 10-2.8-2.8M3 15.5h14a2.8 2.8 0 11-2.8 2.8M3 12h7"/>',
		'hermetico'  => '<rect x="6" y="9" width="12" height="11" rx="2"/><path d="M8.5 9V6.5a3.5 3.5 0 017 0V9"/><path d="M4 4l16 16"/>',
		'no-beber'   => '<path d="M7 4h10l-1.2 16H8.2z"/><path d="M4 4l16 16"/>',
		'nino'       => '<circle cx="12" cy="6" r="2.5"/><path d="M8 21v-6l-2-2 3-4h6l3 4-2 2v6M12 13v8"/>',
		'coche'      => '<path d="M3 16v-4l2.5-5h13l2.5 5v4z"/><path d="M3 12h18"/><circle cx="7" cy="16.5" r="1.8"/><circle cx="17" cy="16.5" r="1.8"/>',
		'termometro' => '<path d="M10 14.5V5a2 2 0 014 0v9.5a4 4 0 11-4 0z"/><path d="M12 9v7"/>',
		'probeta'    => '<path d="M9 3h6M10 3v7L5.3 18.4A1.7 1.7 0 006.8 21h10.4a1.7 1.7 0 001.5-2.6L14 10V3"/><path d="M7.5 15h9"/>',
		'copa'       => '<path d="M5 4h14l-7 8.5zM12 12.5V20M8 20.5h8"/>',
		'nave'       => '<path d="M3 21V10l9-6 9 6v11z"/><path d="M8 21v-7h8v7M8 17.5h8"/>',
		'fabrica'    => '<path d="M3 21V11l5 3v-3l5 3V7h3l1-4h2l1 4v14z"/><path d="M7 17.5h2M12 17.5h2"/>',
		'bateria'    => '<rect x="3" y="7" width="16" height="10" rx="2"/><path d="M21 10.5v3M7 12h3M8.5 10.5v3"/>',
		'palet'      => '<path d="M3 17h18M3 20h18M5 17v3M12 17v3M19 17v3"/><path d="M5 8h14v9H5zM5 12.5h14M12 8v9"/>',
		'calendario' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
		'telefono'   => '<path d="M21 16.6v2.8a1.9 1.9 0 01-2.1 1.9A18.8 18.8 0 012.7 5.1 1.9 1.9 0 014.6 3h2.8a1.9 1.9 0 011.9 1.6c.2 1.2.5 2.4 1 3.5a1.9 1.9 0 01-.4 2l-1.2 1.2a15 15 0 005.9 5.9l1.2-1.2a1.9 1.9 0 012-.4c1.1.5 2.3.8 3.5 1a1.9 1.9 0 011.7 1.9z"/>',
		'whatsapp'   => '<path d="M20.5 11.6a8.5 8.5 0 01-12.6 7.4L3.5 20.5l1.5-4.2a8.5 8.5 0 1115.5-4.7z"/><path d="M9 8.6c.3-.6.6-.7 1-.7h.6c.2 0 .5 0 .6.4l.8 1.8c.1.3 0 .6-.1.8l-.5.6c-.1.2-.2.4 0 .6.3.6.9 1.5 1.8 2.2 1.1.9 2 1.1 2.4 1.2.2.1.5 0 .6-.2l.6-.7c.2-.2.5-.3.8-.2l1.8.8c.4.2.5.4.5.7-.1.5-.6 1.3-1.2 1.6-.6.3-1.4.4-2.5.1-1.2-.3-2.7-1-4.2-2.4-1.8-1.7-2.7-3.5-3-4.5-.3-1-.2-1.6 0-2.1z"/>',
		'email'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/>',
		'carrito'    => '<path d="M3 4h2l2.2 10.5a1.8 1.8 0 001.8 1.5h8a1.8 1.8 0 001.8-1.4L20.5 8H6.1"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17" cy="20" r="1.3"/>',
		'flecha'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'flecha-ab'  => '<path d="M6 9l6 6 6-6"/>',
		'check'      => '<path d="M4.5 12.5l5 5 10-11"/>',
		'pin'        => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0114 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'menu'       => '<path d="M4 8h16M4 16h16"/>',
		'cerrar'     => '<path d="M6 6l12 12M18 6L6 18"/>',
		'repetir'    => '<path d="M4 12a8 8 0 0113.7-5.6L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 01-13.7 5.6L4 15.5M4 20v-4.5h4.5"/>',
		'factura'    => '<path d="M6 3h9l4 4v14H6z"/><path d="M15 3v4h4M9 11h7M9 14.5h7M9 18h4"/>',
		'euro'       => '<path d="M17.5 6.5A7 7 0 007 12a7 7 0 0010.5 5.5M4.5 10.5h9M4.5 13.5h9"/>',
		'niebla'     => '<path d="M3 9h11a3 3 0 10-3-3M3 13h15M5 17h9a3 3 0 11-3 3"/>',
		'copo'       => '<path d="M12 2v20M3.3 7l17.4 10M3.3 17L20.7 7"/><path d="M9.5 3.5L12 6l2.5-2.5M9.5 20.5L12 18l2.5 2.5M3 10.3l3.4.9-.9 3.4M21 13.7l-3.4-.9.9-3.4M5.5 5.8l.9 3.4-3.4.9M18.5 18.2l-.9-3.4 3.4-.9"/>',
		'descarga'   => '<path d="M12 3v12M7 10l5 5 5-5M4 20h16"/>',
		'mapa'       => '<path d="M9 4L3 6.5v13.5L9 17.5l6 2.5 6-2.5V4L15 6.5z"/><path d="M9 4v13.5M15 6.5V20"/>',
	);
	if ( ! isset( $p[ $nombre ] ) ) return '';
	return '<svg class="ico ' . esc_attr( $clase ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' . $p[ $nombre ] . '</svg>';
}

/* ---------- Botones ---------- */
function dipt_boton( $texto, $url, $variante = '', $extra = array() ) {
	$attr = '';
	foreach ( $extra as $k => $v ) $attr .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	$clase = 'boton' . ( $variante ? ' boton--' . $variante : '' );
	return '<a class="' . esc_attr( $clase ) . '" href="' . esc_url( $url ) . '"' . $attr . '><span>' . esc_html( $texto ) . '</span><span class="boton__flecha">' . dipt_icono( 'flecha' ) . '</span></a>';
}

function dipt_enlace( $texto, $url, $extra = array() ) {
	$attr = '';
	foreach ( $extra as $k => $v ) $attr .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	return '<a class="enlace-flecha" href="' . esc_url( $url ) . '"' . $attr . '><span>' . esc_html( $texto ) . '</span>' . dipt_icono( 'flecha' ) . '</a>';
}

/**
 * Título que sube línea a línea. Las líneas se separan con "|" en el texto; admite <em>.
 * dipt_titulo( 'h2', 'Hielo seco,|mañana', 't-h2' )
 */
function dipt_titulo( $etiqueta, $texto, $clase = 't-h2', $id = '' ) {
	$lineas = explode( '|', (string) $texto );
	$html   = '';
	foreach ( $lineas as $i => $l ) {
		$html .= '<span class="linea"><span style="--l:' . (int) $i . '">' . wp_kses( trim( $l ), array( 'em' => array(), 'br' => array(), 'sub' => array(), 'span' => array( 'class' => array() ) ) ) . '</span></span>';
	}
	$id_attr = $id ? ' id="' . esc_attr( $id ) . '"' : '';
	// Lectores de pantalla: el título entero, sin cortes
	return '<' . $etiqueta . ' class="' . esc_attr( $clase ) . '" data-lineas' . $id_attr . ' aria-label="' . esc_attr( wp_strip_all_tags( str_replace( '|', ' ', $texto ) ) ) . '">' . $html . '</' . $etiqueta . '>';
}

/* ---------- Preguntas frecuentes: se registran para el FAQPage de la página ---------- */
function dipt_registrar_faq( array $preguntas ) {
	foreach ( $preguntas as $f ) {
		$GLOBALS['dipt_faq_schema'][] = array( 'q' => wp_strip_all_tags( $f[0] ), 'a' => wp_strip_all_tags( $f[1] ) );
	}
}

/* ---------- Cinta de datos (se duplica el contenido para un bucle sin costuras) ---------- */
function dipt_cinta( array $elementos, $clase = '' ) {
	$uno = '';
	foreach ( $elementos as $e ) $uno .= '<span class="cinta__elem">' . esc_html( $e ) . '</span>';
	return '<div class="cinta ' . esc_attr( $clase ) . '" aria-hidden="true"><div class="cinta__pista">' . $uno . $uno . '</div></div>';
}

/* ---------- Migas de pan (con datos estructurados en seo.php) ---------- */
function dipt_migas( array $tramos ) {
	$GLOBALS['dipt_migas'] = $tramos;
	$html = '<nav class="migas" aria-label="' . esc_attr( dipt_t( 'migas' ) ) . '"><ol>';
	$ultimo = count( $tramos ) - 1;
	foreach ( $tramos as $i => $t ) {
		$html .= '<li>' . ( $i < $ultimo ? '<a href="' . esc_url( $t[1] ) . '">' . esc_html( $t[0] ) . '</a>' : '<span aria-current="page">' . esc_html( $t[0] ) . '</span>' ) . '</li>';
	}
	return $html . '</ol></nav>';
}

/* ---------- Seguridad: los seis avisos innegociables (enlazan a la página de seguridad) ---------- */
function dipt_avisos_seguridad() {
	$c = dipt_contenido( 'comun' );
	return $c['seguridad'] ?? array();
}

/* ---------- Cabecera sobre portada oscura (la plantilla lo declara antes de get_header) ---------- */
function dipt_cabecera_sobre_oscuro() {
	return ! empty( $GLOBALS['dipt_hero_oscuro'] );
}

/* ---------- Selector de idioma: enlaza a la misma página en cada idioma (o a su portada si no existe) ---------- */
function dipt_selector_idioma( $clase = '' ) {
	$clave  = dipt_pagina_actual() ?: 'inicio';
	$actual = dipt_idioma();
	echo '<nav class="idiomas ' . esc_attr( $clase ) . '" aria-label="' . esc_attr( dipt_t( 'idioma' ) ) . '">';
	foreach ( dipt_idiomas() as $i => $nombre ) {
		$url = dipt_traducida( $clave, $i ) ? dipt_url( $clave, $i ) : dipt_url( 'inicio', $i );
		if ( $i === $actual ) {
			echo '<span aria-current="true" lang="' . esc_attr( $i ) . '" title="' . esc_attr( $nombre ) . '">' . esc_html( $i ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $url ) . '" hreflang="' . esc_attr( $i ) . '" lang="' . esc_attr( $i ) . '" title="' . esc_attr( $nombre ) . '">' . esc_html( $i ) . '</a>';
		}
	}
	echo '</nav>';
}
