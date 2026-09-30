<?php
/**
 * Piezas reutilizables de toda la web.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Imagen de la biblioteca de medios: local si existe; si no (entorno de pruebas), la de dryicepack.es. */
function dipt_media( $ruta ) {
	$ruta = '/' . ltrim( $ruta, '/' );
	if ( file_exists( ABSPATH . ltrim( $ruta, '/' ) ) ) return home_url( $ruta );
	return 'https://dryicepack.es' . $ruta;
}
function dipt_img( $archivo ) {
	return DIPT_URI . '/assets/img/' . ltrim( $archivo, '/' );
}

/* ---------- Iconos de línea propios (24×24, trazo 1,6, currentColor) ---------- */
function dipt_icono( $nombre, $clase = '' ) {
	static $p = array(
		'caja'       => '<path d="M3 8l9-4 9 4v9l-9 4-9-4z"/><path d="M3 8l9 4 9-4M12 12v9"/>',
		'pellet'     => '<circle cx="6.5" cy="8" r="1.7"/><circle cx="12" cy="6" r="1.7"/><circle cx="17.5" cy="8.5" r="1.7"/><circle cx="9" cy="13" r="1.7"/><circle cx="15" cy="13.5" r="1.7"/><circle cx="6" cy="18" r="1.7"/><circle cx="12" cy="18.5" r="1.7"/><circle cx="18" cy="18" r="1.7"/>',
		'nugget'     => '<rect x="3.5" y="5" width="9" height="5" rx="2.5" transform="rotate(-18 8 7.5)"/><rect x="11" y="10" width="9" height="5" rx="2.5" transform="rotate(14 15.5 12.5)"/><rect x="4" y="15" width="9" height="5" rx="2.5" transform="rotate(-6 8.5 17.5)"/>',
		'camion'     => '<path d="M2 6.5h11.5v9.5H2zM13.5 9.5h4.2l3.3 3.4v3.1h-7.5"/><circle cx="6" cy="17.6" r="1.9"/><circle cx="17" cy="17.6" r="1.9"/>',
		'reloj'      => '<circle cx="12" cy="12" r="9"/><path d="M12 12V6.5M12 12l3.2 2"/>',
		'guante'     => '<path d="M8.5 21v-5.2L5.7 11.6a1.6 1.6 0 012.6-1.9l1.2 1.4V4.9a1.5 1.5 0 013 0V10V3.9a1.5 1.5 0 013 0V10V5.4a1.5 1.5 0 013 0v8.1c0 2.9-1.3 4.7-3 6V21"/>',
		'ventilar'   => '<path d="M3 8.5h10.5a2.8 2.8 0 10-2.8-2.8M3 15.5h14a2.8 2.8 0 11-2.8 2.8M3 12h7"/>',
		'abierto'    => '<path d="M6 10.5h12v8.5a2 2 0 01-2 2H8a2 2 0 01-2-2z"/><path d="M7 7.5l10-2.8"/><path d="M12 18.5v-5M9.8 15.5l2.2-2.2 2.2 2.2"/>',
		'no-beber'   => '<path d="M7 4h10l-1.2 16H8.2z"/><path d="M4 4l16 16"/>',
		'termometro' => '<path d="M10 14.5V5a2 2 0 014 0v9.5a4 4 0 11-4 0z"/><path d="M12 9v7"/>',
		'matraz'     => '<path d="M9.5 3h5M10.5 3v6.2L5.2 18.3A1.8 1.8 0 006.8 21h10.4a1.8 1.8 0 001.6-2.7l-5.3-9.1V3"/><path d="M7.6 15h8.8"/>',
		'copa'       => '<path d="M5 4h14l-7 8.5zM12 12.5V20M8 20.5h8"/><path d="M15.5 4l2.5-2"/>',
		'fabrica'    => '<path d="M3 21V11l5 3v-3l5 3V7h3l1-4h2l1 4v14z"/><path d="M7 17.5h2M12 17.5h2"/>',
		'casa'       => '<path d="M4 11l8-7 8 7v9a1 1 0 01-1 1h-4.5v-6h-5v6H5a1 1 0 01-1-1z"/>',
		'calendario' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M7.5 14h2M11 14h2M14.5 14h2M7.5 17.5h2"/>',
		'telefono'   => '<path d="M21 16.6v2.8a1.9 1.9 0 01-2.1 1.9A18.8 18.8 0 012.7 5.1 1.9 1.9 0 014.6 3h2.8a1.9 1.9 0 011.9 1.6c.2 1.2.5 2.4 1 3.5a1.9 1.9 0 01-.4 2l-1.2 1.2a15 15 0 005.9 5.9l1.2-1.2a1.9 1.9 0 012-.4c1.1.5 2.3.8 3.5 1a1.9 1.9 0 011.7 1.9z"/>',
		'whatsapp'   => '<path d="M20.5 11.6a8.5 8.5 0 01-12.6 7.4L3.5 20.5l1.5-4.2a8.5 8.5 0 1115.5-4.7z"/><path d="M9 8.6c.3-.6.6-.7 1-.7h.6c.2 0 .5 0 .6.4l.8 1.8c.1.3 0 .6-.1.8l-.5.6c-.1.2-.2.4 0 .6.3.6.9 1.5 1.8 2.2 1.1.9 2 1.1 2.4 1.2.2.1.5 0 .6-.2l.6-.7c.2-.2.5-.3.8-.2l1.8.8c.4.2.5.4.5.7-.1.5-.6 1.3-1.2 1.6-.6.3-1.4.4-2.5.1-1.2-.3-2.7-1-4.2-2.4-1.8-1.7-2.7-3.5-3-4.5-.3-1-.2-1.6 0-2.1z"/>',
		'email'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/>',
		'carrito'    => '<circle cx="9" cy="20" r="1.6"/><circle cx="17" cy="20" r="1.6"/><path d="M3 4h2l2.4 11.2a2 2 0 002 1.6h7.5a2 2 0 002-1.6L21 8H7"/>',
		'flecha'     => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'check'      => '<path d="M4.5 12.5l5 5 10-11"/>',
		'pin'        => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0114 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'cerrar'     => '<path d="M6 6l12 12M18 6L6 18"/>',
		'euro'       => '<path d="M17.5 6.5A7 7 0 007 12a7 7 0 0010.5 5.5M4.5 10.5h9M4.5 13.5h9"/>',
		'repetir'    => '<path d="M4 12a8 8 0 0113.7-5.6L20 8.5M20 4v4.5h-4.5M20 12a8 8 0 01-13.7 5.6L4 15.5M4 20v-4.5h4.5"/>',
	);
	if ( ! isset( $p[ $nombre ] ) ) return '';
	return '<svg class="ico ' . esc_attr( $clase ) . '" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">' . $p[ $nombre ] . '</svg>';
}

/* ---------- Botones en píldora ---------- */
function dipt_boton( $texto, $url, $variante = 'senal', $extra = array() ) {
	$attr = '';
	foreach ( $extra as $k => $v ) $attr .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	$flecha = in_array( $variante, array( 'senal', 'marino' ), true ) ? '<span class="btn__flecha">' . dipt_icono( 'flecha' ) . '</span>' : '';
	return '<a class="btn btn--' . esc_attr( $variante ) . '" href="' . esc_url( $url ) . '" data-iman' . $attr . '><span>' . esc_html( $texto ) . '</span>' . $flecha . '</a>';
}

/* ---------- Reloj de corte (texto de servidor + actualización en directo con JS) ---------- */
function dipt_texto_entrega( $e = null ) {
	$e   = $e ?: dipt_entrega();
	$txt = dipt_fecha_texto( $e['entrega'] );
	if ( $e['sabado_opcional'] ) {
		$sab = $e['sale']->modify( '+1 day' );
		return 'el sábado ' . (int) $sab->format( 'j' ) . ' (según zona, con suplemento) o ' . $txt;
	}
	return $txt;
}
function dipt_reloj_corte( $clase = '' ) {
	$e    = dipt_entrega();
	$pref = $e['corte_hoy'] ? 'Pide antes de las 12:00 y lo recibes ' : 'Pide hoy y lo recibes ';
	return '<p class="reloj-corte ' . esc_attr( $clase ) . '" data-reloj-corte><span class="reloj-corte__pulso" aria-hidden="true"></span>'
		. '<span class="reloj-corte__texto" data-reloj-texto>' . esc_html( $pref . dipt_texto_entrega( $e ) ) . '</span></p>';
}

/* ---------- Etiqueta de expedición (la firma de la marca) ---------- */
function dipt_etiqueta( $args = array() ) {
	$a = wp_parse_args( $args, array(
		'peso'    => '3 a 20 kg',
		'formato' => '3 mm · 16 mm',
		'clase'   => '',
		'datos'   => '', // atributos data-* extra (calculadora)
	) );
	return '<figure class="etiqueta ' . esc_attr( $a['clase'] ) . '" ' . $a['datos'] . '>'
		. '<div class="etiqueta__franja"><span class="etiqueta__un">UN 1845</span>'
		. '<span class="etiqueta__nombre">Hielo seco<small>Dióxido de carbono sólido</small></span>'
		. '<span class="etiqueta__clase" aria-hidden="true"><i>9</i></span></div>'
		. '<dl class="etiqueta__datos">'
		. '<div><dt>Peso neto</dt><dd data-etq-peso>' . esc_html( $a['peso'] ) . '</dd></div>'
		. '<div><dt>Formato</dt><dd data-etq-formato>' . esc_html( $a['formato'] ) . '</dd></div>'
		. '<div><dt>Temperatura</dt><dd>−78,5 °C</dd></div>'
		. '<div class="etiqueta__entrega"><dt>Entrega</dt><dd data-entrega>' . esc_html( dipt_texto_entrega() ) . '</dd></div>'
		. '</dl>'
		. '<figcaption class="etiqueta__pie"><span class="etiqueta__barras" aria-hidden="true"></span><span>DRYICEPACK · MATARÓ · 24 H</span></figcaption>'
		. '</figure>';
}

/* ---------- Caja EPS en 3D (CSS puro; el JS solo la inclina con el puntero) ---------- */
function dipt_caja_eps( $clase = '' ) {
	$interior = dipt_img( 'caja-cenital-480.webp' );
	return '<div class="caja3d ' . esc_attr( $clase ) . '" data-caja3d aria-hidden="true">'
		. '<div class="caja3d__escena"><div class="caja3d__cubo">'
		. '<i class="c c--base"></i><i class="c c--atras"></i><i class="c c--izq"></i><i class="c c--der"></i>'
		. '<i class="c c--dentro" style="background-image:url(' . esc_url( $interior ) . ')"></i>'
		. '<i class="c c--frente"><b class="c__rotulo">DRYICEPACK</b></i>'
		. '<i class="c c--tapa"></i>'
		. '</div></div>'
		. '<div class="caja3d__vapor"><i></i><i></i><i></i><i></i><i></i></div>'
		. '<div class="caja3d__caida"><i></i><i></i><i></i></div>'
		. '<div class="caja3d__sombra"></div>'
		. '</div>';
}

/* ---------- Niebla ambiental ---------- */
function dipt_niebla( $clase = '', $capas = 2 ) {
	return '<div class="niebla ' . esc_attr( $clase ) . '" aria-hidden="true">' . str_repeat( '<i></i>', max( 1, (int) $capas ) ) . '</div>';
}

/* ---------- Preguntas frecuentes (+ registro para el schema FAQPage) ---------- */
function dipt_faq( $preguntas, $id = 'preguntas' ) {
	$GLOBALS['dipt_faq_schema'] = array_merge( $GLOBALS['dipt_faq_schema'] ?? array(), $preguntas );
	$html = '<div class="faq" id="' . esc_attr( $id ) . '-lista">';
	foreach ( $preguntas as $i => $f ) {
		$html .= '<details class="faq__item"' . ( 0 === $i ? ' open' : '' ) . '><summary><span>' . esc_html( $f['q'] ) . '</span><i class="faq__mas" aria-hidden="true"></i></summary>'
			. '<div class="faq__resp">' . wp_kses_post( $f['html'] ?? esc_html( $f['a'] ) ) . '</div></details>';
	}
	return $html . '</div>';
}
