<?php
/**
 * Provincias y ciudades de la península (10/11/2026): una página por provincia y, en una segunda tanda, por ciudad grande.
 * - Datos: datos/provincias.json y datos/ciudades.json. Mientras no existan, las muestras datos/*-muestra.json.
 * - Registro (inc/paginas.php): 'provincia-<clave>' y 'ciudad-<clave>' en hielo-seco-<clave> y, en Cataluña, también
 *   ca/gel-sec-<clave>. Plantilla plantillas/provincia.php con las piezas de la página de zona (zona.css).
 *   Las zonas de datos/zonas.json mandan: una provincia o ciudad con la misma clave o el mismo nombre no tiene otra página.
 * - Las ciudades solo se registran con el interruptor de Apariencia → Páginas Dryicepack (opción 'dipt_ciudades',
 *   apagado de serie): mientras esté apagado no hay rutas, ni enlaces, ni salen en /zonas/.
 * - Entrega: la misma lista de días que ofrece el checkout para el código postal y la población del lugar (festivos y
 *   cierres de MRW del plugin, inc/festivos-mrw.php). Nunca se consulta a MRW durante la visita.
 * Textos en contenido/{idioma}/provincias.php con marcadores (ver dipt_lugar_texto()).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Datos de un archivo de datos/: el definitivo o, si todavía no existe, la muestra (*-muestra.json). */
function dipt_lugares_json( $nombre ) {
	foreach ( array( $nombre, $nombre . '-muestra' ) as $archivo ) {
		$ruta = DIPT_DIR . '/datos/' . $archivo . '.json';
		if ( ! file_exists( $ruta ) ) continue;
		$datos = json_decode( (string) file_get_contents( $ruta ), true );
		if ( is_array( $datos ) ) return $datos;
	}
	return array();
}

/** Nombre para comparar lugares: "L'Hospitalet de Llobregat" y "Hospitalet de Llobregat" → "hospitalet-de-llobregat". */
function dipt_lugar_normal( $nombre ) {
	$n = strtolower( remove_accents( str_replace( array( '’', '‘' ), "'", (string) $nombre ) ) );
	$n = preg_replace( "/^(?:l'|el |la |els |les |los |las |o |os |as )/", '', $n );
	return trim( (string) preg_replace( '/[^a-z0-9]+/', '-', (string) $n ), '-' );
}

/** Coordenada o número de los datos (null si falta). */
function dipt_lugar_num( $d, $campo, $entero = false ) {
	if ( ! isset( $d[ $campo ] ) || ! is_numeric( $d[ $campo ] ) ) return null;
	return $entero ? (int) round( (float) $d[ $campo ] ) : (float) $d[ $campo ];
}

/**
 * Provincias peninsulares: clave → datos normalizados. 'nombre' es la capital ("Bilbao"), 'oficial' su nombre oficial
 * (el que se busca en los cierres de MRW), 'provincia_corta' el nombre de la provincia en el texto ("Bizkaia").
 * 'pagina': clave de su página (provincia-<clave>, o zona-<clave> si ya tiene página de zona; null si no tiene).
 */
function dipt_provincias() {
	static $lista = null;
	if ( null !== $lista ) return $lista;
	$lista = array();
	$zonas = function_exists( 'dipt_zonas' ) ? dipt_zonas() : array();
	foreach ( (array) ( dipt_lugares_json( 'provincias' )['provincias'] ?? array() ) as $k => $d ) {
		if ( ! is_array( $d ) || empty( $d['ciudad'] ) ) continue;
		$clave = sanitize_title( (string) ( $d['clave'] ?? $k ) );
		$cp    = str_pad( (string) preg_replace( '/\D/', '', (string) ( $d['cp'] ?? '' ) ), 2, '0', STR_PAD_LEFT );
		// Sin envío fuera de la península (Baleares, Canarias, Ceuta, Melilla): sin página
		if ( '' === $clave || 2 !== strlen( $cp ) || in_array( $cp, array( '07', '35', '38', '51', '52' ), true ) ) continue;
		$cp_capital = (string) preg_replace( '/\D/', '', (string) ( $d['cp_capital'] ?? '' ) );
		$pagina     = null;
		if ( isset( $zonas[ $clave ] ) ) $pagina = 'zona-' . $clave;
		elseif ( empty( $d['existe'] ) ) $pagina = 'provincia-' . $clave;
		$lista[ $clave ] = array(
			'tipo'            => 'provincia',
			'clave'           => $clave,
			'nombre'          => (string) $d['ciudad'],
			'oficial'         => (string) ( $d['ciudad_oficial'] ?? $d['ciudad'] ),
			'provincia'       => (string) ( $d['provincia'] ?? $d['ciudad'] ),
			'provincia_corta' => (string) ( $d['provincia_corta'] ?? ( $d['provincia'] ?? $d['ciudad'] ) ),
			'provincia_clave' => $clave,
			'ccaa'            => (string) ( $d['ccaa'] ?? '' ),
			'cp'              => $cp,
			'cps'             => array(),
			'cp_entrega'      => 5 === strlen( $cp_capital ) && 0 === strpos( $cp_capital, $cp ) ? $cp_capital : $cp . '001',
			'municipios'      => array_values( array_filter( array_map( 'strval', (array) ( $d['municipios'] ?? array() ) ), 'strlen' ) ),
			'catalunya'       => ! empty( $d['catalunya'] ),
			'intro'           => is_array( $d['intro'] ?? null ) ? $d['intro'] : array(),
			'km'              => dipt_lugar_num( $d, 'km', true ),
			'min'             => dipt_lugar_num( $d, 'min', true ),
			'lat'             => dipt_lugar_num( $d, 'lat' ),
			'lon'             => dipt_lugar_num( $d, 'lon' ),
			'poblacion'       => (int) dipt_lugar_num( $d, 'poblacion', true ),
			'pagina'          => $pagina,
		);
	}
	return $lista;
}

/** ¿Están activadas las páginas de ciudades? (Apariencia → Páginas Dryicepack; apagado de serie) */
function dipt_ciudades_activas() {
	return (bool) get_option( 'dipt_ciudades', 0 );
}

/**
 * Ciudades grandes con página propia: clave → datos normalizados (mismos campos que una provincia, con 'cps' y la
 * provincia en 'provincia_clave'). Vacío mientras el interruptor esté apagado, salvo con $todas = true.
 * Fuera: las claves y los nombres que ya tienen página de zona o de provincia, y las ciudades sin provincia conocida.
 */
function dipt_ciudades( $todas = false ) {
	static $lista = null;
	if ( null === $lista ) {
		$lista      = array();
		$zonas      = function_exists( 'dipt_zonas' ) ? dipt_zonas() : array();
		$provincias = dipt_provincias();
		$ocupados   = array();
		foreach ( $zonas as $z ) $ocupados[ dipt_lugar_normal( $z['nombre'] ?? '' ) ] = true;
		foreach ( $provincias as $p ) {
			$ocupados[ dipt_lugar_normal( $p['nombre'] ) ]  = true;
			$ocupados[ dipt_lugar_normal( $p['oficial'] ) ] = true;
		}
		foreach ( (array) ( dipt_lugares_json( 'ciudades' )['ciudades'] ?? array() ) as $k => $d ) {
			if ( ! is_array( $d ) || empty( $d['nombre'] ) ) continue;
			$clave = sanitize_title( (string) ( $d['clave'] ?? $k ) );
			$prov  = $provincias[ sanitize_title( (string) ( $d['provincia'] ?? '' ) ) ] ?? null;
			$norma = dipt_lugar_normal( $d['nombre'] );
			if ( '' === $clave || ! $prov || isset( $zonas[ $clave ] ) || isset( $provincias[ $clave ] ) || isset( $lista[ $clave ] ) || isset( $ocupados[ $norma ] ) ) continue;
			$cps = array();
			foreach ( (array) ( $d['cp'] ?? array() ) as $cp ) {
				$cp = (string) preg_replace( '/\D/', '', (string) $cp );
				if ( 5 === strlen( $cp ) && 0 === strpos( $cp, $prov['cp'] ) ) $cps[ $cp ] = $cp;
			}
			if ( ! $cps ) continue;
			sort( $cps );
			$lista[ $clave ] = array(
				'tipo'            => 'ciudad',
				'clave'           => $clave,
				'nombre'          => (string) $d['nombre'],
				'oficial'         => (string) ( $d['nombre_oficial'] ?? $d['nombre'] ),
				'provincia'       => $prov['provincia'],
				'provincia_corta' => $prov['provincia_corta'],
				'provincia_clave' => $prov['clave'],
				'ccaa'            => $prov['ccaa'],
				'cp'              => $prov['cp'],
				'cps'             => array_values( $cps ),
				'cp_entrega'      => reset( $cps ),
				'municipios'      => array(),
				'catalunya'       => $prov['catalunya'] || ! empty( $d['catalunya'] ),
				'intro'           => is_array( $d['intro'] ?? null ) ? $d['intro'] : array(),
				'km'              => dipt_lugar_num( $d, 'km', true ),
				'min'             => dipt_lugar_num( $d, 'min', true ),
				'lat'             => dipt_lugar_num( $d, 'lat' ),
				'lon'             => dipt_lugar_num( $d, 'lon' ),
				'poblacion'       => (int) dipt_lugar_num( $d, 'poblacion', true ),
				'pagina'          => 'ciudad-' . $clave,
			);
			$ocupados[ $norma ] = true; // la misma ciudad con dos claves: solo la primera
		}
	}
	return ( $todas || dipt_ciudades_activas() ) ? $lista : array();
}

/* Con el interruptor apagado, una página de ciudad que ya estaba creada en WordPress responde 404 (no una página vacía). */
add_action( 'template_redirect', static function () {
	if ( is_admin() || ! is_page() || dipt_ciudades_activas() ) return;
	$ruta = get_page_uri( get_queried_object_id() );
	foreach ( array_keys( dipt_ciudades( true ) ) as $clave ) {
		if ( 'hielo-seco-' . $clave !== $ruta && 'ca/gel-sec-' . $clave !== $ruta ) continue;
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}
} );

/* …y tampoco sale en el sitemap de Rank Math. */
add_filter( 'rank_math/sitemap/entry', static function ( $url, $tipo = '', $objeto = null ) {
	if ( dipt_ciudades_activas() || ! ( $objeto instanceof WP_Post ) || 'page' !== $objeto->post_type ) return $url;
	$ruta = get_page_uri( $objeto );
	foreach ( array_keys( dipt_ciudades( true ) ) as $clave ) {
		if ( 'hielo-seco-' . $clave === $ruta || 'ca/gel-sec-' . $clave === $ruta ) return false;
	}
	return $url;
}, 10, 3 );

function dipt_lugar( $tipo, $clave ) {
	$lista = 'ciudad' === $tipo ? dipt_ciudades() : dipt_provincias();
	return $lista[ $clave ] ?? null;
}

/** Lugar de una página del registro ('provincia-madrid', 'ciudad-getafe'), o null. */
function dipt_lugar_de_pagina( $clave_pagina ) {
	$p = dipt_paginas()[ (string) $clave_pagina ] ?? null;
	if ( empty( $p['lugar'] ) ) return null;
	return dipt_lugar( $p['lugar'][0], $p['lugar'][1] );
}

/** Clave de la página registrada de un lugar (null si no tiene: ciudades apagadas, ruta ocupada…). */
function dipt_lugar_pagina( $l ) {
	$k = is_array( $l ) ? ( $l['pagina'] ?? null ) : null;
	return $k && isset( dipt_paginas()[ $k ] ) ? $k : null;
}

/** ¿La capital se llama como la provincia? ("Sevilla", "Girona": sí; "Bilbao" / "Bizkaia": no) */
function dipt_lugar_mismo_nombre( array $l ) {
	$p = dipt_lugar_normal( $l['provincia_corta'] );
	return dipt_lugar_normal( $l['nombre'] ) === $p || dipt_lugar_normal( $l['oficial'] ) === $p;
}

/** Comunidad autónoma en el idioma (contenido/{idioma}/zonas.php → 'ccaa'; si no está, como viene en los datos). */
function dipt_lugar_ccaa( $ccaa, $idioma = null ) {
	$mapa = dipt_contenido( 'zonas', $idioma ?: dipt_idioma() )['ccaa'] ?? array();
	return (string) ( $mapa[ dipt_lugar_normal( $ccaa ) ] ?? $ccaa );
}

/** "a, b y c" (castellano: "e" delante de i-/hi-), "a, b i c", "a, b and c". $separador: '; ' si los elementos llevan comas. */
function dipt_lugar_lista( array $items, $idioma = null, $separador = ', ' ) {
	$idioma = $idioma ?: dipt_idioma();
	$items  = array_values( array_filter( array_map( 'strval', $items ), 'strlen' ) );
	if ( count( $items ) < 2 ) return $items[0] ?? '';
	$ultimo = array_pop( $items );
	$y      = array( 'ca' => ' i ', 'en' => ' and ' )[ $idioma ] ?? ' y ';
	if ( 'es' === $idioma && preg_match( '/^(?:i|í|hi|hí)(?!e)/iu', wp_strip_all_tags( $ultimo ) ) ) $y = ' e ';
	return implode( $separador, $items ) . $y . $ultimo;
}

/** Importe en el idioma pedido: "12,49 €" (es, ca) o "€12.49" (en). */
function dipt_lugar_euros( $n, $idioma ) {
	if ( 'en' === $idioma ) return '€' . number_format( (float) $n, 2, '.', ',' );
	return number_format( (float) $n, 2, ',', '.' ) . ' €';
}

/**
 * Rellena un texto con los datos del lugar. Marcadores:
 * {nombre} {Nombre} capital o ciudad, con las contracciones de las zonas (dipt_zona_texto: "a l'Hospitalet", "del Vendrell")
 * {provincia} nombre corto de la provincia · {ccaa} comunidad autónoma · {cp} "41" · {cp_resto} "41xxx" · {km} {min}
 * {y_prov} " y Bizkaia" (solo provincias con otro nombre que la capital) · {y_toda} " y toda Bizkaia" / " y toda su provincia"
 * {y_toda_de} " y de toda Bizkaia" / " y de toda su provincia" (en las ciudades, los tres vacíos)
 * {fecha} próxima entrega en el lugar · {envio:N} {total:N} {kilo:N} envío, total y precio por kilo del pack de N kg (pk: el
 * pack pequeño) · y los de dipt_marcadores(): {pk} {precio:N} {recogida:N} {sabado_envio} {sabado_recogida} {sabado_libre}.
 * $extra: más sustituciones [ '{x}' => 'valor' ].
 */
function dipt_lugar_texto( $plantilla, array $l, $idioma = null, array $extra = array() ) {
	$idioma = $idioma ?: dipt_idioma();
	$texto  = (string) dipt_marcadores( (string) $plantilla, $idioma );
	if ( false === strpos( $texto, '{' ) ) return $texto;
	$m      = dipt_contenido( 'provincias', $idioma )['marcadores'] ?? array();
	$prov   = 'provincia' === $l['tipo'];
	$misma  = dipt_lugar_mismo_nombre( $l );
	$texto  = strtr( $texto, $extra + array(
		'{y_prov}'    => $prov && ! $misma ? (string) ( $m['y_prov'] ?? '' ) : '',
		'{y_toda}'    => $prov ? (string) ( $m[ $misma ? 'y_toda_su' : 'y_toda' ] ?? '' ) : '',
		'{y_toda_de}' => $prov ? (string) ( $m[ $misma ? 'y_toda_de_su' : 'y_toda_de' ] ?? '' ) : '',
	) );
	if ( false !== strpos( $texto, '{fecha}' ) ) {
		$e     = dipt_lugar_entrega( $l );
		$texto = str_replace( '{fecha}', $e['fecha'] ? dipt_fecha_larga( $e['fecha'], $idioma ) : '', $texto );
	}
	$texto = strtr( $texto, array(
		'{provincia}' => $l['provincia_corta'],
		'{ccaa}'      => dipt_lugar_ccaa( $l['ccaa'], $idioma ),
		'{cp_resto}'  => $l['cp'] . 'xxx',
		'{cp}'        => $l['cp'],
	) );
	$texto = (string) preg_replace_callback( '/\{(envio|total|kilo):(pk|\d+)\}/', static function ( $x ) use ( $idioma ) {
		$p = dipt_precio_pack( 'pk' === $x[2] ? dipt_pk() : (int) $x[2] );
		return dipt_lugar_euros( $p[ 'kilo' === $x[1] ? 'kilo' : $x[1] ], $idioma );
	}, $texto );
	return dipt_zona_texto( $texto, array( 'nombre' => $l['nombre'], 'km' => (string) ( $l['km'] ?? '' ), 'min' => (string) ( $l['min'] ?? '' ), 'comarca' => '' ), $idioma );
}

/**
 * Entrega en el lugar, con la misma regla que el checkout para su código postal y su población:
 * 'fecha' primera entrega entre semana (Y-m-d) · 'sin_reparto' días en que MRW no reparte allí [Y-m-d => 'provincia'|'local']
 * (solo los que el plugin ya ha consultado, 21 días vista). Sin el plugin: la fecha general del tema y ningún día.
 */
function dipt_lugar_entrega( array $l ) {
	static $memo = array();
	$clave = $l['cp_entrega'] . '|' . $l['oficial'];
	if ( isset( $memo[ $clave ] ) ) return $memo[ $clave ];
	$r = array( 'fecha' => '', 'sin_reparto' => array() );
	if ( function_exists( 'dip_calendario_entrega' ) ) {
		$cal = dip_calendario_entrega( 'envio', null, array( 'cp' => $l['cp_entrega'], 'poblacion' => $l['oficial'] ) );
		foreach ( (array) ( $cal['fechas'] ?? array() ) as $f ) {
			if ( empty( $f['sabado'] ) ) { $r['fecha'] = (string) $f['fecha']; break; }
		}
		$prov = function_exists( 'dip_mrw_provincia_por_cp' ) ? dip_mrw_provincia_por_cp( $l['cp_entrega'] ) : '';
		foreach ( (array) ( $cal['saltadas'] ?? array() ) as $ymd ) {
			$entera = $prov && function_exists( 'dip_mrw_cierra' ) && dip_mrw_cierra( (string) $ymd, $prov, '' );
			$r['sin_reparto'][ (string) $ymd ] = $entera ? 'provincia' : 'local';
		}
	} else {
		$e          = dipt_primera_entrega();
		$r['fecha'] = (string) ( $e['fecha'] ?? '' );
	}
	return $memo[ $clave ] = $r;
}

/**
 * Párrafo de usos del lugar: la intro propia de los datos o, si está vacía, una frase con los sectores (transporte primero,
 * el resto en un orden que cambia de un lugar a otro) y un cierre según el tamaño. Sin datos que no estén en los textos.
 */
function dipt_lugar_intro( array $l, $idioma = null ) {
	$idioma = $idioma ?: dipt_idioma();
	$propia = trim( (string) ( $l['intro'][ $idioma ] ?? '' ) );
	if ( '' !== $propia ) return dipt_lugar_texto( $propia, $l, $idioma );
	$a = dipt_contenido( 'provincias', $idioma )['intro_auto'] ?? array();
	if ( empty( $a['aperturas'] ) || empty( $a['sectores'] ) ) return '';
	$h     = abs( (int) crc32( $l['tipo'] . '-' . $l['clave'] ) );
	$resto = array( 'hosteleria', 'laboratorios', 'industria', 'eventos' );
	$giro  = $h % count( $resto );
	$orden = array_merge( array( 'transporte' ), array_slice( $resto, $giro ), array_slice( $resto, 0, $giro ) );
	$items = array();
	foreach ( $orden as $i => $s ) {
		$op = array_values( (array) ( $a['sectores'][ $s ] ?? array() ) );
		if ( $op ) $items[] = $op[ ( $h >> ( $i + 3 ) ) % count( $op ) ];
	}
	$aperturas = array_values( (array) $a['aperturas'] );
	$frase     = str_replace( '{lista}', dipt_lugar_lista( $items, $idioma ), $aperturas[ ( $h >> 1 ) % count( $aperturas ) ] );
	$cierre    = ( 'provincia' === $l['tipo'] || $l['poblacion'] >= 150000 ) ? ( $a['cierres']['grande'] ?? '' ) : ( $a['cierres']['pequena'] ?? '' );
	return dipt_lugar_texto( trim( $frase . ' ' . $cierre ), $l, $idioma );
}

/**
 * Enlaces "cerca de aquí": [ [texto, url], … ].
 * Ciudad: su provincia y de 3 a 5 ciudades de la misma provincia (por distancia si hay coordenadas; si no, por población).
 * Provincia: sus ciudades con página (por población) y las otras provincias de su comunidad autónoma.
 */
function dipt_lugar_cercanos( array $l ) {
	$enlaces = array();
	if ( 'ciudad' === $l['tipo'] ) {
		$prov = dipt_provincias()[ $l['provincia_clave'] ] ?? null;
		$kp   = $prov ? dipt_lugar_pagina( $prov ) : null;
		if ( $kp ) $enlaces[] = array( $prov['provincia_corta'], dipt_url( $kp ) );
		$otras = array();
		foreach ( dipt_ciudades() as $k => $o ) {
			if ( $k === $l['clave'] || $o['provincia_clave'] !== $l['provincia_clave'] || ! dipt_lugar_pagina( $o ) ) continue;
			$otras[ $k ] = ( null !== $l['lat'] && null !== $o['lat'] ) ? dipt_distancia( array( $l['lat'], $l['lon'] ), array( $o['lat'], $o['lon'] ) ) : - $o['poblacion'];
		}
		asort( $otras );
		foreach ( array_slice( array_keys( $otras ), 0, 5 ) as $k ) $enlaces[] = array( dipt_ciudades()[ $k ]['nombre'], dipt_url( 'ciudad-' . $k ) );
		return $enlaces;
	}
	$ciudades = array_filter( dipt_ciudades(), static fn( $o ) => $o['provincia_clave'] === $l['clave'] && dipt_lugar_pagina( $o ) );
	uasort( $ciudades, static fn( $a, $b ) => $b['poblacion'] <=> $a['poblacion'] );
	foreach ( $ciudades as $k => $o ) $enlaces[] = array( $o['nombre'], dipt_url( 'ciudad-' . $k ) );
	foreach ( dipt_provincias() as $k => $o ) {
		if ( $k === $l['clave'] || $o['ccaa'] !== $l['ccaa'] ) continue;
		$kp = dipt_lugar_pagina( $o );
		if ( $kp ) $enlaces[] = array( $o['provincia_corta'], dipt_url( $kp ) );
	}
	return $enlaces;
}

/** El primer texto que cabe: con $min, el primero entre $min y $max; si ninguno, el más largo que no pasa de $max. */
function dipt_lugar_elegir( array $textos, $max, $min = 0 ) {
	$textos = array_values( array_unique( array_filter( array_map( 'trim', $textos ), 'strlen' ) ) );
	foreach ( $textos as $x ) {
		$n = mb_strlen( $x );
		if ( $n <= $max && $n >= $min ) return $x;
	}
	$caben = array_filter( $textos, static fn( $x ) => mb_strlen( $x ) <= $max );
	if ( $caben ) {
		usort( $caben, static fn( $a, $b ) => mb_strlen( $b ) <=> mb_strlen( $a ) );
		return $caben[0];
	}
	usort( $textos, static fn( $a, $b ) => mb_strlen( $a ) <=> mb_strlen( $b ) );
	return $textos[0] ?? '';
}

/** Título (≤ 60) y descripción (140–155) de la página de un lugar: [tipo, clave] del registro. */
function dipt_seo_lugar( $lugar, $idioma = null ) {
	$l = is_array( $lugar ) ? dipt_lugar( $lugar[0] ?? '', $lugar[1] ?? '' ) : null;
	if ( ! $l ) return null;
	$idioma = $idioma ?: dipt_idioma();
	$s      = dipt_contenido( 'provincias', $idioma )['seo'] ?? array();
	$rellena = static fn( $lista ) => array_map( static fn( $x ) => wp_strip_all_tags( dipt_lugar_texto( $x, $l, $idioma ) ), (array) $lista );
	return array(
		'titulo'      => dipt_lugar_elegir( $rellena( $s['titulos'] ?? array() ), 60 ),
		'descripcion' => dipt_lugar_elegir( $rellena( $s['descripciones'] ?? array() ), 155, 140 ),
	);
}
