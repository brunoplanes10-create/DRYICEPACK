<?php
/**
 * Zonas de entrega: una página por ciudad o comarca con datos propios (distancia y tiempo en coche hasta la nave,
 * posición en el mapa, zonas vecinas). Datos en datos/zonas.json (herramientas/datos-zonas.mjs, OpenStreetMap + OSRM).
 * Textos en contenido/{idioma}/zonas.php con marcadores {nombre}, {km}, {min}, {comarca}.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dipt_zonas() {
	static $zonas = null;
	if ( null !== $zonas ) return $zonas;
	$zonas   = array();
	$archivo = DIPT_DIR . '/datos/zonas.json';
	$datos   = file_exists( $archivo ) ? json_decode( (string) file_get_contents( $archivo ), true ) : null;
	if ( empty( $datos['zonas'] ) ) return $zonas;
	foreach ( $datos['zonas'] as $clave => $z ) {
		$z['es'] = 'hielo-seco-' . $clave;
		$z['ca'] = 'ca/gel-sec-' . $clave;
		$z['en'] = 'barcelona' === $clave ? 'en/dry-ice-barcelona' : null;
		$zonas[ $clave ] = $z;
	}
	return $zonas;
}

function dipt_zona_nave() {
	$archivo = DIPT_DIR . '/datos/zonas.json';
	$datos   = file_exists( $archivo ) ? json_decode( (string) file_get_contents( $archivo ), true ) : null;
	return $datos['nave'] ?? array( 41.5534, 2.4528 );
}

/** Nombre en el idioma: "el Maresme" → "el Maresme" (ca), "Maresme" (en). */
function dipt_zona_nombre( $z, $idioma = null ) {
	$idioma = $idioma ?: dipt_idioma();
	$n      = $z['nombre'];
	if ( 'en' === $idioma ) $n = preg_replace( '/^(el|la|els|les) /i', '', $n );
	return $n;
}

function dipt_zona_texto( $plantilla, $z ) {
	$texto = strtr( (string) $plantilla, array(
		'{nombre}'  => dipt_zona_nombre( $z ),
		'{Nombre}'  => ucfirst( dipt_zona_nombre( $z ) ),
		'{km}'      => (string) $z['km'],
		'{min}'     => (string) $z['min'],
		'{comarca}' => $z['comarca'],
	) );
	// Contracciones con el artículo del nombre: "a el Maresme" → "al Maresme", "de El Prat" → "del Prat" (castellano y catalán)
	if ( 'en' !== dipt_idioma() ) {
		$texto = preg_replace( array( '/\ba [eE]l /u', '/\bde [eE]l /u', '/\bA [eE]l /u', '/\bDe [eE]l /u' ), array( 'al ', 'del ', 'Al ', 'Del ' ), $texto );
	}
	return $texto;
}

function dipt_seo_zona( $clave ) {
	$z = dipt_zonas()[ $clave ] ?? null;
	if ( ! $z ) return null;
	$c      = dipt_contenido( 'zonas' );
	$titulo = dipt_zona_texto( $c['zona']['seo_titulo'], $z );
	// Nombres largos: primero fuera la marca, después lo que va tras los dos puntos (máximo 60 caracteres)
	if ( mb_strlen( $titulo ) > 60 ) $titulo = preg_replace( '/\s*\|\s*DryIcePack$/u', '', $titulo );
	if ( mb_strlen( $titulo ) > 60 ) $titulo = preg_replace( '/:.*$/u', '', $titulo );
	// Descripción: 140–155 caracteres; los nombres largos usan la versión corta
	$descripcion = dipt_zona_texto( $c['zona']['seo_descripcion'], $z );
	if ( mb_strlen( $descripcion ) > 155 && ! empty( $c['zona']['seo_descripcion_corta'] ) ) $descripcion = dipt_zona_texto( $c['zona']['seo_descripcion_corta'], $z );
	return array(
		'titulo'      => $titulo,
		'descripcion' => $descripcion,
	);
}

/** Distancia en línea recta entre dos puntos (km), para ordenar zonas vecinas. */
function dipt_distancia( $a, $b ) {
	$r    = 6371;
	$dlat = deg2rad( $b[0] - $a[0] );
	$dlon = deg2rad( $b[1] - $a[1] );
	$h    = sin( $dlat / 2 ) ** 2 + cos( deg2rad( $a[0] ) ) * cos( deg2rad( $b[0] ) ) * sin( $dlon / 2 ) ** 2;
	return 2 * $r * asin( sqrt( $h ) );
}

/**
 * Mapa radial: la nave en el centro, anillos cada 10 km y cada zona en su posición real (proyección simple).
 * $destacadas: claves de las zonas que se resaltan (vacío en el índice).
 */
function dipt_mapa_radial( array $destacadas = array(), $etiquetas = true ) {
	$nave  = dipt_zona_nave();
	$escala = 6.2; // píxeles por km
	$cx = 600; $cy = 140; // Mataró a la derecha: todas las zonas quedan al oeste y al suroeste
	$html = '<svg class="radar" viewBox="0 0 720 500" role="img" aria-label="' . esc_attr( dipt_contenido( 'zonas' )['mapa'] ?? '' ) . '">';
	foreach ( array( 10, 20, 30, 40, 50 ) as $km ) {
		$html .= sprintf( '<circle class="radar__anillo" cx="%d" cy="%d" r="%.1f"/><text class="radar__km" x="%d" y="%.1f">%d km</text>', $cx, $cy, $km * $escala, $cx + 4, $cy - $km * $escala - 4, $km );
	}
	foreach ( dipt_zonas() as $clave => $z ) {
		if ( 'comarca' === $z['tipo'] || 'maresme' === $clave || 'mataro' === $clave ) continue;
		$dx = ( $z['lon'] - $nave[1] ) * 111.32 * cos( deg2rad( $nave[0] ) ) * $escala;
		$dy = -( $z['lat'] - $nave[0] ) * 110.57 * $escala;
		$x  = $cx + $dx;
		$y  = $cy + $dy;
		$on = in_array( $clave, $destacadas, true );
		$html .= sprintf( '<g class="radar__punto%s"><line x1="%d" y1="%d" x2="%.1f" y2="%.1f"/><circle cx="%.1f" cy="%.1f" r="%d"/>', $on ? ' es-actual' : '', $cx, $cy, $x, $y, $x, $y, $on ? 9 : 5 );
		if ( $etiquetas || $on ) $html .= sprintf( '<text x="%.1f" y="%.1f">%s</text>', $x + 10, $y + 4, esc_html( dipt_zona_nombre( $z ) ) );
		$html .= '</g>';
	}
	$html .= sprintf( '<g class="radar__nave"><circle cx="%d" cy="%d" r="11"/><text x="%d" y="%d">Mataró</text></g>', $cx, $cy, $cx + 16, $cy + 5 );
	return $html . '</svg>';
}
