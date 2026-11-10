<?php
/**
 * Condiciones de venta en PDF, adjuntas al email de pedido recibido (soporte duradero: TRLGDCU 98.7, Codi de consum
 * 222-2.2). En el idioma del pedido.
 *
 * - Texto: el de la página de condiciones del tema, dipt_contenido( 'legal-condiciones', idioma ), con sus marcadores ya
 *   sustituidos ({pk}, {recogida:10}, {sabado_recogida}, {sabado_libre}…). Sin el tema no hay adjunto y el email dice
 *   dónde leerlas.
 * - PDF A4 de varias páginas hecho aquí, sin librerías: Helvetica y Courier de serie con WinAnsiEncoding (€, ñ, ç, ·,
 *   acentos, ª, º; el signo menos sale como raya corta). Usa la conversión y las métricas de DIP_FS_PDF
 *   (inc/factura-simplificada.php).
 * - Caché en uploads/dryicepack-condiciones/{idioma}-{huella}/: la huella sale del texto final, así que cambia sola si
 *   cambia el texto, la fecha de actualización o un marcador. Al crear una versión nueva se borran las viejas de ese
 *   idioma. Son las condiciones públicas de la web: no llevan datos de clientes.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_CONDICIONES_PDF_VERSION' ) ) define( 'DIP_CONDICIONES_PDF_VERSION', '1' ); // súbela si cambia la maqueta

/** Contenido de las condiciones del tema en ese idioma, o null. */
function dip_condiciones_datos( $idioma ) {
	if ( ! function_exists( 'dipt_contenido' ) ) return null;
	$c = dipt_contenido( 'legal-condiciones', $idioma );
	return ( is_array( $c ) && ! empty( $c['secciones'] ) ) ? $c : null;
}

/** HTML de una sección → [ [tipo, runs], … ] con tipo 'p', 'li' o 'fila'; runs = [ [texto, negrita], … ]. */
function dip_condiciones_html_bloques( $html ) {
	$html    = preg_replace( '#<br\s*/?>#i', "\n", (string) $html );
	$bloques = array();
	if ( preg_match_all( '#<(p|li|tr|h3|h4|dt|dd)\b[^>]*>(.*?)</\1>#is', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $x ) {
			$tag = strtolower( $x[1] );
			if ( 'tr' === $tag ) {
				preg_match_all( '#<(t[hd])\b[^>]*>(.*?)</\1>#is', $x[2], $celdas, PREG_SET_ORDER );
				$cab   = $celdas && 'th' === strtolower( $celdas[0][1] );
				$texto = implode( ' · ', array_map( static fn( $c ) => dip_condiciones_limpio( $c[2] ), $celdas ) );
				if ( '' !== $texto ) $bloques[] = array( 'fila', array( array( $texto, $cab ) ) );
				continue;
			}
			$runs = dip_condiciones_runs( $x[2] );
			if ( $runs ) $bloques[] = array( in_array( $tag, array( 'li', 'dd' ), true ) ? 'li' : 'p', $runs, in_array( $tag, array( 'h3', 'h4', 'dt' ), true ) );
		}
	}
	if ( ! $bloques ) {
		$runs = dip_condiciones_runs( $html );
		if ( $runs ) $bloques[] = array( 'p', $runs );
	}
	return $bloques;
}

/** Texto sin etiquetas, sin entidades y con los espacios normalizados. */
function dip_condiciones_limpio( $html ) {
	$t = html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return trim( (string) preg_replace( '/[ \t\r\f\v\x{00A0}]+/u', ' ', $t ) );
}

/** Tramos de texto con su negrita (<strong>, <b>). Un salto de línea dentro se conserva como "\n". */
function dip_condiciones_runs( $html ) {
	$partes  = preg_split( '#(</?(?:strong|b)\b[^>]*>)#i', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$negrita = false;
	$runs    = array();
	foreach ( (array) $partes as $p ) {
		if ( preg_match( '#^<(/?)(?:strong|b)\b#i', $p, $m ) ) {
			$negrita = '' === $m[1];
			continue;
		}
		$t = html_entity_decode( wp_strip_all_tags( $p ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$t = preg_replace( '/[ \t\r\f\v\x{00A0}]+/u', ' ', $t );
		if ( '' !== $t ) $runs[] = array( $t, $negrita );
	}
	// Sin espacios sobrantes al principio y al final del párrafo
	if ( $runs ) {
		$runs[0][0] = ltrim( $runs[0][0] );
		$u          = count( $runs ) - 1;
		$runs[ $u ][0] = rtrim( $runs[ $u ][0] );
	}
	return array_values( array_filter( $runs, static fn( $r ) => '' !== $r[0] ) );
}

/** Todo el documento en bloques: título, actualizado y secciones. */
function dip_condiciones_documento( $idioma ) {
	$c = dip_condiciones_datos( $idioma );
	if ( ! $c ) return null;
	$secciones = array();
	foreach ( (array) $c['secciones'] as $s ) {
		$titulo = is_array( $s ) ? (string) ( $s[0] ?? ( $s['titulo'] ?? '' ) ) : '';
		$html   = is_array( $s ) ? (string) ( $s[1] ?? ( $s['texto'] ?? '' ) ) : (string) $s;
		$secciones[] = array( 'titulo' => dip_condiciones_limpio( $titulo ), 'bloques' => dip_condiciones_html_bloques( $html ) );
	}
	return array(
		'titulo'      => dip_condiciones_limpio( $c['titulo'] ?? '' ),
		'actualizado' => dip_condiciones_limpio( $c['actualizado'] ?? '' ),
		'secciones'   => $secciones,
	);
}

/** PDF de las condiciones (binario) o '' si no hay texto o falta el generador. */
function dip_condiciones_pdf( $idioma ) {
	$doc = dip_condiciones_documento( $idioma );
	if ( ! $doc || ! class_exists( 'DIP_FS_PDF' ) ) return '';
	$t   = dip_avisos_textos( $idioma );
	$c   = dip_contacto();
	$pdf = new DIP_Condiciones_PDF();
	$col = DIP_Condiciones_PDF::COLORES;
	$x0  = 56;
	$x1  = 539.28;
	$xt  = $x0 + 26;  // texto de las secciones, alineado con el título (el número queda a la izquierda)
	$fin = 72;        // por debajo, página nueva

	// Primera página: banda marino con la marca y la cifra de la casa
	$pdf->rect( 0, 785.89, 595.28, 56, $col['marino'] );
	$pdf->texto( $x0, 808, 'DRYICEPACK', 'F2', 15, $col['blanco'], 'izq', 2.2 );
	$pdf->texto( $x1, 808.5, $t['frio'], 'F3', 9, $col['hielo'], 'der', 0.4 );
	$y = 742;
	foreach ( DIP_FS_PDF::partir( $doc['titulo'], 'F2', 24, $x1 - $x0 ) as $l ) {
		$pdf->texto( $x0, $y, $l, 'F2', 24, $col['tinta'] );
		$y -= 28;
	}
	if ( '' !== $doc['actualizado'] ) {
		$pdf->texto( $x0, $y + 6, $doc['actualizado'], 'F3', 8.5, $col['acero'], 'izq', 0.2 );
		$y -= 14;
	}
	$pdf->texto( $x0, $y + 6, $c['empresa'] . ' · NIF ' . $c['cif'] . ' · dryicepack.es · ' . $c['email'] . ' · ' . $c['telefono'], 'F1', 8.5, $col['acero'] );
	$y -= 14;
	$pdf->linea( $x0, $y, $x1, $y, 1, $col['marino'] );
	$y -= 26;

	$cabecera_pagina = static function () use ( $pdf, $col, $x0, $x1, $doc ) {
		$pdf->texto( $x0, 806, 'DRYICEPACK', 'F2', 8.5, $col['marino'], 'izq', 1.4 );
		$pdf->texto( $x1, 806, $doc['titulo'], 'F1', 8.5, $col['acero'], 'der' );
		$pdf->linea( $x0, 798, $x1, 798, 0.5, $col['linea'] );
		return 772;
	};
	$sitio = static function ( $alto ) use ( &$y, $pdf, $fin, $cabecera_pagina ) {
		if ( $y - $alto >= $fin ) return;
		$pdf->nueva_pagina();
		$y = $cabecera_pagina();
	};

	foreach ( $doc['secciones'] as $i => $s ) {
		// Título de la sección con su número; nunca solo al pie de una página
		$lineas_t = DIP_FS_PDF::partir( $s['titulo'], 'F2', 11.5, $x1 - $xt );
		$sitio( 16 * count( $lineas_t ) + 40 );
		$pdf->texto( $x0, $y, sprintf( '%02d', $i + 1 ), 'F4', 10, $col['marino'] );
		foreach ( $lineas_t as $l ) {
			$pdf->texto( $xt, $y, $l, 'F2', 11.5, $col['tinta'] );
			$y -= 16;
		}
		$y -= 2;
		foreach ( $s['bloques'] as $b ) {
			$sangria = 'li' === $b[0] ? 12 : 0;
			$tam     = 'fila' === $b[0] ? 8.5 : 9.5;
			$alto    = 'fila' === $b[0] ? 12 : 13.6;
			$runs    = $b[1];
			if ( ! empty( $b[2] ) ) $runs = array_map( static fn( $r ) => array( $r[0], true ), $runs ); // subtítulo: todo en negrita
			$lineas = DIP_Condiciones_PDF::lineas( $runs, $tam, $x1 - $xt - $sangria );
			foreach ( $lineas as $n => $linea ) {
				$sitio( $alto );
				if ( 0 === $n && 'li' === $b[0] ) $pdf->texto( $xt + 2, $y, "\u{2022}", 'F1', $tam, $col['marino'] );
				$pdf->linea_texto( $xt + $sangria, $y, $linea, $tam, $col['tinta'] );
				$y -= $alto;
			}
			$y -= 'li' === $b[0] ? 2 : 5;
		}
		$y -= 12;
	}

	// Pie de cada página (ahora que se sabe cuántas hay)
	$total = $pdf->paginas();
	$pie   = $c['empresa'] . ' · NIF ' . $c['cif'] . ' · Ronda Alfonso X El Sabio 10, 2º 1ª, 08301 Mataró · ' . $c['email'];
	for ( $p = 0; $p < $total; $p++ ) {
		$pdf->en_pagina( $p );
		$pdf->linea( $x0, 52, $x1, 52, 0.5, $col['linea'] );
		$pdf->texto( $x0, 40, DIP_FS_PDF::recortar( $pie, 'F1', 7, 380 ), 'F1', 7, $col['acero'] );
		$pdf->texto( $x1, 40, sprintf( $t['pdf_pagina'], $p + 1, $total ), 'F3', 7, $col['acero'], 'der' );
	}
	return $pdf->salida( array(
		'titulo' => $doc['titulo'] . ' · DryIcePack',
		'autor'  => $c['empresa'],
		'idioma' => array( 'es' => 'es-ES', 'ca' => 'ca-ES', 'en' => 'en-GB' )[ $idioma ] ?? 'es-ES',
	) );
}

/** Carpeta de la caché ('' si no se puede crear). */
function dip_condiciones_carpeta() {
	$subidas = wp_upload_dir( null, false );
	if ( ! empty( $subidas['error'] ) || empty( $subidas['basedir'] ) ) return '';
	$dir = rtrim( $subidas['basedir'], '/\\' ) . '/dryicepack-condiciones';
	if ( ! wp_mkdir_p( $dir ) ) return '';
	if ( ! file_exists( $dir . '/index.php' ) ) @file_put_contents( $dir . '/index.php', '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	return $dir;
}

/**
 * Ruta del PDF de las condiciones en ese idioma, creándolo si hace falta. '' si no se puede (sin tema, sin generador o
 * sin permiso de escritura): entonces el email no lleva adjunto y dice dónde leerlas.
 */
function dip_condiciones_pdf_ruta( $idioma ) {
	static $memo = array();
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	if ( isset( $memo[ $idioma ] ) ) return $memo[ $idioma ];
	$doc = dip_condiciones_documento( $idioma );
	$dir = $doc ? dip_condiciones_carpeta() : '';
	if ( ! $doc || '' === $dir || ! class_exists( 'DIP_FS_PDF' ) ) return $memo[ $idioma ] = '';
	$huella  = substr( md5( DIP_CONDICIONES_PDF_VERSION . '|' . $idioma . '|' . wp_json_encode( $doc ) ), 0, 12 );
	$carpeta = $dir . '/' . $idioma . '-' . $huella;
	$ruta    = $carpeta . '/' . dip_avisos_textos( $idioma )['pdf_archivo'];
	if ( is_readable( $ruta ) && filesize( $ruta ) > 0 ) return $memo[ $idioma ] = $ruta;
	$pdf = dip_condiciones_pdf( $idioma );
	if ( '' === $pdf || ! wp_mkdir_p( $carpeta ) || false === @file_put_contents( $ruta, $pdf ) ) return $memo[ $idioma ] = ''; // phpcs:ignore WordPress.PHP.NoSilencedErrors
	// Versiones anteriores del mismo idioma: fuera
	foreach ( (array) glob( $dir . '/' . $idioma . '-*', GLOB_ONLYDIR ) as $vieja ) {
		if ( $vieja === $carpeta ) continue;
		foreach ( (array) glob( $vieja . '/*' ) as $f ) @unlink( $f ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		@rmdir( $vieja ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	return $memo[ $idioma ] = $ruta;
}

/* Adjunto del email de pedido recibido (pagado y «En espera»), en el idioma del pedido. */
add_filter( 'woocommerce_email_attachments', static function ( $adjuntos, $id = '', $objeto = null ) {
	if ( ! in_array( $id, DIP_EMAILS_PROPIOS, true ) || ! $objeto instanceof WC_Order ) return $adjuntos;
	$ruta = dip_condiciones_pdf_ruta( dip_avisos_idioma( $objeto ) );
	if ( '' === $ruta ) return $adjuntos;
	$adjuntos   = is_array( $adjuntos ) ? $adjuntos : array_filter( array( $adjuntos ) );
	$adjuntos[] = $ruta;
	return $adjuntos;
}, 20, 3 );

/**
 * PDF de varias páginas con las 14 fuentes estándar: F1 Helvetica, F2 Helvetica-Bold, F3 Courier, F4 Courier-Bold.
 * La conversión a WinAnsiEncoding y las métricas son las de DIP_FS_PDF.
 */
class DIP_Condiciones_PDF {
	const FUENTES = array( 'F1' => 'Helvetica', 'F2' => 'Helvetica-Bold', 'F3' => 'Courier', 'F4' => 'Courier-Bold' );
	const COLORES = array(
		'marino' => '0.122 0.235 0.333',
		'tinta'  => '0.059 0.114 0.161',
		'acero'  => '0.322 0.396 0.478',
		'hielo'  => '0.722 0.816 0.91',
		'linea'  => '0.784 0.827 0.867',
		'blanco' => '1 1 1',
	);

	private $paginas = array( array() );
	private $actual  = 0;

	public function nueva_pagina() {
		$this->paginas[] = array();
		$this->actual    = count( $this->paginas ) - 1;
	}

	public function en_pagina( $n ) {
		$this->actual = max( 0, min( (int) $n, count( $this->paginas ) - 1 ) );
	}

	public function paginas() {
		return count( $this->paginas );
	}

	private static function n( $valor ) {
		$texto = rtrim( rtrim( sprintf( '%.3F', (float) $valor ), '0' ), '.' );
		return ( '' === $texto || '-0' === $texto ) ? '0' : $texto;
	}

	private static function escapar( $win ) {
		return strtr( $win, array( '\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => ' ', "\n" => ' ' ) );
	}

	private static function utf16( $texto ) {
		$hex = 'FEFF';
		foreach ( DIP_FS_PDF::puntos( $texto ) as $p ) {
			if ( $p > 0xFFFF ) {
				$p   -= 0x10000;
				$hex .= sprintf( '%04X%04X', 0xD800 | ( $p >> 10 ), 0xDC00 | ( $p & 0x3FF ) );
			} else {
				$hex .= sprintf( '%04X', $p );
			}
		}
		return '<' . $hex . '>';
	}

	private function op( $op ) {
		$this->paginas[ $this->actual ][] = $op;
	}

	public function texto( $x, $y, $texto, $fuente = 'F1', $tam = 10, $color = '0 0 0', $alinear = 'izq', $tracking = 0 ) {
		$win = DIP_FS_PDF::win( $texto );
		if ( '' === $win ) return;
		$ancho = DIP_FS_PDF::ancho( $texto, $fuente, $tam, $tracking );
		if ( 'der' === $alinear ) $x -= $ancho;
		elseif ( 'centro' === $alinear ) $x -= $ancho / 2;
		$this->op( 'BT /' . $fuente . ' ' . self::n( $tam ) . ' Tf ' . $color . ' rg ' . self::n( $tracking ) . ' Tc ' . self::n( $x ) . ' ' . self::n( $y ) . ' Td (' . self::escapar( $win ) . ') Tj ET' );
	}

	public function rect( $x, $y, $ancho, $alto, $color ) {
		$this->op( $color . ' rg ' . self::n( $x ) . ' ' . self::n( $y ) . ' ' . self::n( $ancho ) . ' ' . self::n( $alto ) . ' re f' );
	}

	public function linea( $x1, $y1, $x2, $y2, $grosor, $color ) {
		$this->op( $color . ' RG ' . self::n( $grosor ) . ' w ' . self::n( $x1 ) . ' ' . self::n( $y1 ) . ' m ' . self::n( $x2 ) . ' ' . self::n( $y2 ) . ' l S' );
	}

	/**
	 * Parte en líneas un párrafo con tramos de negrita. Devuelve [ [ [texto, negrita], … ], … ] (una lista de tramos por
	 * línea, con los espacios ya puestos). Un "\n" dentro del texto fuerza un salto.
	 */
	public static function lineas( array $runs, $tam, $max ) {
		$palabras = array(); // [texto, negrita, espacio_antes, salto_antes]
		$espacio  = false;
		$salto    = false;
		foreach ( $runs as $r ) {
			$trozos = preg_split( '/(\n| )/u', (string) $r[0], -1, PREG_SPLIT_DELIM_CAPTURE );
			foreach ( (array) $trozos as $tz ) {
				if ( "\n" === $tz ) {
					$salto = true;
					continue;
				}
				if ( ' ' === $tz ) {
					$espacio = true;
					continue;
				}
				if ( '' === $tz ) continue;
				// Las unidades no se separan de su número: «35 €», «16 mm», «−78,5 °C»
				$u = count( $palabras ) - 1;
				if ( $u >= 0 && $espacio && ! $salto && preg_match( '/^(€|kg|mm|°C|%|h)[\p{P}]*$/u', $tz ) && preg_match( '/\d[\p{P}]*$/u', $palabras[ $u ][0] ) ) {
					$palabras[ $u ][0] .= "\u{00A0}" . $tz;
					$espacio = false;
					continue;
				}
				$palabras[] = array( $tz, (bool) $r[1], $espacio, $salto );
				$espacio    = false;
				$salto      = false;
			}
		}
		$lineas = array();
		$linea  = array();
		$ancho  = 0.0;
		foreach ( $palabras as $p ) {
			$f   = $p[1] ? 'F2' : 'F1';
			$w   = DIP_FS_PDF::ancho( $p[0], $f, $tam );
			$sep = ( $linea && $p[2] ) ? DIP_FS_PDF::ancho( ' ', $f, $tam ) : 0;
			if ( $linea && ( $p[3] || $ancho + $sep + $w > $max ) ) {
				$lineas[] = $linea;
				$linea    = array();
				$ancho    = 0.0;
				$sep      = 0;
			}
			$linea[] = array( ( $sep ? ' ' : '' ) . $p[0], $p[1] );
			$ancho  += $sep + $w;
		}
		if ( $linea ) $lineas[] = $linea;
		// Tramos seguidos con la misma fuente, juntos (menos operaciones en el PDF)
		foreach ( $lineas as $i => $l ) {
			$junta = array();
			foreach ( $l as $tr ) {
				$u = count( $junta ) - 1;
				if ( $u >= 0 && $junta[ $u ][1] === $tr[1] ) $junta[ $u ][0] .= $tr[0];
				else $junta[] = $tr;
			}
			$lineas[ $i ] = $junta;
		}
		return $lineas;
	}

	/** Pinta una línea de tramos (Helvetica y Helvetica-Bold) desde x. */
	public function linea_texto( $x, $y, array $tramos, $tam, $color ) {
		foreach ( $tramos as $tr ) {
			$f = $tr[1] ? 'F2' : 'F1';
			$this->texto( $x, $y, $tr[0], $f, $tam, $color );
			$x += DIP_FS_PDF::ancho( $tr[0], $f, $tam );
		}
	}

	/** El documento: catálogo (con el idioma), páginas, fuentes, contenidos, información, xref y trailer. */
	public function salida( array $info = array() ) {
		$n_paginas = count( $this->paginas );
		$objetos   = array();
		$objetos[1] = '<< /Type /Catalog /Pages 2 0 R /Lang (' . preg_replace( '/[^A-Za-z-]/', '', (string) ( $info['idioma'] ?? 'es-ES' ) ) . ') /ViewerPreferences << /DisplayDocTitle true >> >>';
		$fuentes    = '';
		$id         = 3;
		foreach ( self::FUENTES as $clave => $nombre ) {
			$objetos[ $id ] = '<< /Type /Font /Subtype /Type1 /BaseFont /' . $nombre . ' /Encoding /WinAnsiEncoding >>';
			$fuentes       .= '/' . $clave . ' ' . $id . ' 0 R ';
			$id++;
		}
		$kids = array();
		foreach ( $this->paginas as $ops ) {
			$contenido      = implode( "\n", $ops );
			$pagina_id      = $id;
			$contenido_id   = $id + 1;
			$objetos[ $pagina_id ]    = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /Font << ' . $fuentes . '>> /ProcSet [/PDF /Text] >> /Contents ' . $contenido_id . ' 0 R >>';
			$objetos[ $contenido_id ] = '<< /Length ' . strlen( $contenido ) . " >>\nstream\n" . $contenido . "\nendstream";
			$kids[] = $pagina_id . ' 0 R';
			$id    += 2;
		}
		$objetos[2] = '<< /Type /Pages /Kids [' . implode( ' ', $kids ) . '] /Count ' . $n_paginas . ' >>';
		$fecha      = gmdate( 'YmdHis' );
		$objetos[ $id ] = '<< /Title ' . self::utf16( $info['titulo'] ?? '' ) . ' /Author ' . self::utf16( $info['autor'] ?? '' ) . ' /Creator (DryIcePack) /Producer (DryIcePack) /CreationDate (D:' . $fecha . 'Z) >>';
		$info_id    = $id;
		ksort( $objetos );

		$pdf     = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
		$offsets = array();
		foreach ( $objetos as $n => $cuerpo ) {
			$offsets[ $n ] = strlen( $pdf );
			$pdf          .= $n . " 0 obj\n" . $cuerpo . "\nendobj\n";
		}
		$xref = strlen( $pdf );
		$pdf .= "xref\n0 " . ( count( $objetos ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $offsets as $offset ) $pdf .= sprintf( "%010d 00000 n \n", $offset );
		$pdf .= "trailer\n<< /Size " . ( count( $objetos ) + 1 ) . ' /Root 1 0 R /Info ' . $info_id . " 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
		return $pdf;
	}
}
