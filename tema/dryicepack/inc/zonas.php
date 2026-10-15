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

/**
 * Rellena una plantilla con los datos de la zona. {nombre} es la forma de mitad de frase y {Nombre} la de inicio.
 * Contracciones solo entre la preposición y {nombre}, aunque haya "|" o "<em>" en medio (se conserva el separador):
 * - Catalán: el artículo del topónimo va en minúscula a mitad de frase y se contrae siempre:
 *   "a el Maresme" → "al Maresme", "de El Prat" → "del Prat", "per a el" → "per al", "per el" → "pel",
 *   "a|<em>L'Hospitalet" → "a|<em>l'Hospitalet".
 * - Castellano (RAE): se contrae el artículo en minúscula ("al Maresme", "del Vallès"), pero no el que forma parte
 *   del nombre propio con mayúscula: "a El Prat de Llobregat", "de El Prat de Llobregat", "a L'Hospitalet".
 * - Inglés: dipt_zona_nombre() ya quita el artículo.
 * $idioma: por defecto, el de la visita.
 */
function dipt_zona_texto( $plantilla, $z, $idioma = null ) {
	$idioma = $idioma ?: dipt_idioma();
	$nombre = dipt_zona_nombre( $z, $idioma );
	$medio  = $nombre;
	if ( 'ca' === $idioma ) {
		$medio = preg_replace_callback( "/^(?:El|Els|La|Les)(?= )|^L(?=['’])/u", static fn( $m ) => strtolower( $m[0] ), $nombre );
	}
	if ( 'en' !== $idioma && preg_match( '/^(el|els) (.+)$/us', $medio, $art ) ) {
		$contraccion = array(
			'a'   => array( 'el' => 'al', 'els' => 'als' ),
			'de'  => array( 'el' => 'del', 'els' => 'dels' ),
			'per' => array( 'el' => 'pel', 'els' => 'pels' ),
		);
		if ( 'es' === $idioma ) $contraccion = array( 'a' => array( 'el' => 'al' ), 'de' => array( 'el' => 'del' ) );
		$plantilla = preg_replace_callback(
			'/(?<![\p{L}\p{N}])(a|A|de|De|per|Per)((?:\s|\||<em>)+)\{nombre\}/u',
			static function ( $m ) use ( $contraccion, $art ) {
				$prep = strtolower( $m[1] );
				if ( empty( $contraccion[ $prep ][ $art[1] ] ) ) return $m[0];
				$junta = $contraccion[ $prep ][ $art[1] ];
				if ( $m[1] !== $prep ) $junta = ucfirst( $junta );
				return $junta . $m[2] . $art[2];
			},
			(string) $plantilla
		);
	}
	return strtr( (string) $plantilla, array(
		'{nombre}'  => $medio,
		'{Nombre}'  => ucfirst( $nombre ),
		'{km}'      => (string) $z['km'],
		'{min}'     => (string) $z['min'],
		'{comarca}' => $z['comarca'],
	) );
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
 * $destacadas: claves de las zonas que se resaltan (vacío en el índice). La primera es la zona de la página.
 * $etiquetas:  true pinta el nombre de todas las zonas. Los que no se pisan con otro (de mayor a menor prioridad,
 *              en el orden de datos/zonas.json) llevan .radar__punto--rotulo y se ven siempre; el resto, al pasar o tocar.
 * $opc:        barrido (bool) línea de radar que gira en 20 s · furgoneta (bool) furgoneta que viaja de Mataró a la
 *              primera zona destacada al entrar (coordenadas en --fx / --fy).
 * Los puntos aparecen por distancia a la nave (--d de 0 a 1). Todo con [data-vivo] (.visto) y en su estado final con .quieto.
 */
function dipt_mapa_radial( array $destacadas = array(), $etiquetas = true, array $opc = array() ) {
	$opc    = wp_parse_args( $opc, array( 'barrido' => false, 'furgoneta' => false ) );
	$nave   = dipt_zona_nave();
	$escala = 6.2; // píxeles por km
	$cx = 600; $cy = 140; // Mataró a la derecha: todas las zonas quedan al oeste y al suroeste
	$html = '<svg class="radar" viewBox="0 0 720 500" role="img" data-vivo aria-label="' . esc_attr( dipt_contenido( 'zonas' )['mapa'] ?? '' ) . '">';
	foreach ( array( 10, 20, 30, 40, 50 ) as $km ) {
		$html .= sprintf( '<circle class="radar__anillo" cx="%d" cy="%d" r="%.1f"/><text class="radar__km" x="%d" y="%.1f">%d km</text>', $cx, $cy, $km * $escala, $cx + 4, $cy - $km * $escala - 4, $km );
	}
	if ( $opc['barrido'] ) {
		// Sector de 24° detrás de la línea, en color plano (sin degradado), que gira alrededor de la nave
		$r  = 52 * $escala;
		$a2 = deg2rad( -24 );
		$html .= sprintf( '<g class="radar__barrido" data-pausa><path class="radar__estela" d="M%d %d L%.1f %d A%.1f %.1f 0 0 0 %.1f %.1f Z"/><line x1="%d" y1="%d" x2="%.1f" y2="%d"/></g>', $cx, $cy, $cx + $r, $cy, $r, $r, $cx + $r * cos( $a2 ), $cy + $r * sin( $a2 ), $cx, $cy, $cx + $r, $cy );
	}
	// Posiciones primero: un rótulo no puede pisar ni otro rótulo ni el punto de otra zona
	$puntos  = array();
	$ocupado = array( array( $cx - 14, $cy - 14, $cx + 80, $cy + 14 ) ); // la nave y su rótulo
	foreach ( dipt_zonas() as $clave => $z ) {
		if ( 'comarca' === $z['tipo'] || 'maresme' === $clave || 'mataro' === $clave ) continue;
		$dx = ( $z['lon'] - $nave[1] ) * 111.32 * cos( deg2rad( $nave[0] ) ) * $escala;
		$dy = -( $z['lat'] - $nave[0] ) * 110.57 * $escala;
		$puntos[ $clave ] = array( $z, $dx, $dy );
		$ocupado[]        = array( $cx + $dx - 7, $cy + $dy - 7, $cx + $dx + 7, $cy + $dy + 7 );
	}
	$destino = null;
	foreach ( $puntos as $clave => list( $z, $dx, $dy ) ) {
		$x  = $cx + $dx;
		$y  = $cy + $dy;
		$on = in_array( $clave, $destacadas, true );
		if ( $destacadas && $clave === $destacadas[0] ) $destino = array( $dx, $dy );
		$nombre = dipt_zona_nombre( $z );
		// ¿Cabe el rótulo sin pisar otro? (13 px de mono ≈ 8 px por carácter)
		$caja  = array( $x + 8, $y - 12, $x + 14 + mb_strlen( $nombre ) * 8, $y + 6 ); // empieza después de su propio punto
		$libre = $caja[2] < 716;
		foreach ( $ocupado as $o ) {
			if ( $caja[0] < $o[2] && $caja[2] > $o[0] && $caja[1] < $o[3] && $caja[3] > $o[1] ) { $libre = false; break; }
		}
		if ( $etiquetas && $libre ) $ocupado[] = $caja;
		$clases = 'radar__punto' . ( $on ? ' es-actual' : '' ) . ( $etiquetas && $libre ? ' radar__punto--rotulo' : '' );
		$html  .= sprintf( '<g class="%s" style="--d:%.2f"><line x1="%d" y1="%d" x2="%.1f" y2="%.1f"/><circle cx="%.1f" cy="%.1f" r="%d"/>', $clases, min( 1, $z['km'] / 60 ), $cx, $cy, $x, $y, $x, $y, $on ? 9 : 5 );
		if ( $etiquetas || $on ) $html .= sprintf( '<text x="%.1f" y="%.1f">%s</text>', $x + 10, $y + 4, esc_html( $nombre ) );
		$html .= '</g>';
	}
	$html .= sprintf( '<g class="radar__nave"><circle cx="%d" cy="%d" r="11"/><text x="%d" y="%d">Mataró</text></g>', $cx, $cy, $cx + 16, $cy + 5 );
	if ( $opc['furgoneta'] && $destino ) {
		// Furgoneta sobre la nave (mirando al oeste) que viaja hasta la zona: el destino va en variables CSS
		$html .= sprintf( '<g class="radar__furgo" style="--fx:%.1fpx;--fy:%.1fpx"><g transform="translate(%d %d)"><rect x="-17" y="-15" width="34" height="30" rx="4"/><g transform="translate(12 -12) scale(-1 1)">%s</g></g></g>', $destino[0], $destino[1], $cx, $cy - 34, dipt_icono( 'furgoneta' ) );
	}
	return $html . '</svg>';
}
