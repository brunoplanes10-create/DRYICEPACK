<?php
/**
 * Diseño común de los emails al cliente (09/11/2026): una sola envoltura para todos.
 *
 * - Sustituye desde el plugin las plantillas de email de WooCommerce (cabecera, pie, estilos, tabla del pedido y el
 *   email de pedido recibido), igual que inc/checkout.php hace con checkout/review-order.php. Así heredan el diseño
 *   todos los emails de WooCommerce y los nuestros: factura simplificada, «¿Necesitas factura?», reseñas, avisos de
 *   entrega y «Confirmar recogida».
 * - Identidad: banda marino #1F3C55 con el logo blanco (PNG retina, assets/img/logo-dryicepack-email.png) y la cifra
 *   −78,5 °C; tarjeta blanca de 600 px; texto oscuro; datos en monoespaciada como una ficha técnica; botón en píldora
 *   marino a prueba de Outlook (VML). Sin cian (no hay botón de compra), sin degradados, sin iconos de librería.
 * - Todo lleva los estilos en línea (WooCommerce, además, pasa a línea los de emails/email-styles.php). El <style>
 *   de la cabecera solo lleva lo que no se puede poner en línea: modo oscuro (prefers-color-scheme) y móvil.
 * - Texto previo (preheader) sin display:none (WooCommerce borra los elementos con display:none al pasar los estilos).
 * - Cada email tiene su versión en texto plano, hecha con los mismos bloques (dip_email_texto()).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Emails de WooCommerce con plantilla propia (pedido recibido). */
if ( ! defined( 'DIP_EMAILS_PROPIOS' ) ) define( 'DIP_EMAILS_PROPIOS', array( 'customer_processing_order', 'customer_on_hold_order' ) );

/* =========================================================
 * Paleta y tipografía
 * ========================================================= */

/** Colores del modo claro (los del oscuro van en dip_email_css()). */
function dip_email_color( $nombre ) {
	static $p = array(
		'fondo'   => '#EEF2F5',
		'tarjeta' => '#FFFFFF',
		'borde'   => '#D5DDE4',
		'linea'   => '#DCE3E9',
		'marino'  => '#1F3C55',
		'hielo'   => '#B8D0E8',
		'tinta'   => '#0F1D29',
		'suave'   => '#52657A',
		'caja'    => '#F3F6F8',
		'blanco'  => '#FFFFFF',
	);
	return $p[ $nombre ] ?? '#0F1D29';
}

/** Fuentes del sistema: Inter si está instalada; si no, Segoe UI, Helvetica o Arial. Datos en monoespaciada. */
function dip_email_fuente( $tipo = 'sans' ) {
	return 'mono' === $tipo
		? "'Geist Mono',SFMono-Regular,Menlo,Consolas,'Liberation Mono','Courier New',monospace"
		: "Inter,'Segoe UI',Helvetica,Arial,sans-serif";
}

/** Estilo en línea a partir de pares: array( 'color' => '#fff', … ) → "color:#fff;…". */
function dip_email_estilo( array $reglas ) {
	$s = '';
	foreach ( $reglas as $k => $v ) {
		if ( null === $v || '' === $v ) continue;
		$s .= $k . ':' . $v . ';';
	}
	return rtrim( $s, ';' );
}

/** Tipografía base de un texto: familia, tamaño, interlineado, peso y color. */
function dip_email_texto_estilo( $tam = 16, $alto = 25, $peso = 400, $color = 'tinta', $familia = 'sans', array $mas = array() ) {
	return dip_email_estilo( array_merge( array(
		'font-family' => dip_email_fuente( $familia ),
		'font-size'   => $tam . 'px',
		'line-height' => $alto . 'px',
		'font-weight' => (string) $peso,
		'color'       => dip_email_color( $color ),
	), $mas ) );
}

/* =========================================================
 * Contexto del email que se está pintando: idioma, texto previo y antetítulo
 * ========================================================= */

/** es · ca · en a partir del idioma de WordPress en este momento (emails sin pedido: cuenta, contraseña). */
function dip_email_idioma_de_locale() {
	$l = function_exists( 'determine_locale' ) ? (string) determine_locale() : 'es_ES';
	if ( 0 === strpos( $l, 'ca' ) ) return 'ca';
	if ( 0 === strpos( $l, 'en' ) ) return 'en';
	return 'es';
}

/**
 * Lee o cambia el contexto. Sin argumentos: el actual (o el de serie). Con un array: lo fija. Con false: lo borra.
 * La cabecera y el pie lo leen; el pie lo borra al terminar para que no pase al email siguiente.
 *
 * @return array{idioma: string, previo: string, antetitulo: string}
 */
function dip_email_contexto( $nuevo = null ) {
	static $ctx = null;
	if ( false === $nuevo ) {
		$ctx = null;
	} elseif ( is_array( $nuevo ) ) {
		$ctx = $nuevo;
	}
	$base = array( 'idioma' => dip_email_idioma_de_locale(), 'previo' => '', 'antetitulo' => '' );
	$r    = array_merge( $base, (array) $ctx );
	$r['idioma'] = in_array( $r['idioma'], array( 'es', 'ca', 'en' ), true ) ? $r['idioma'] : 'es';
	return $r;
}

/** Contexto de un email de WooCommerce: idioma del pedido (al cliente) o castellano (a la tienda). */
function dip_email_contexto_de_wc( WC_Email $email ) {
	$pedido  = $email->object instanceof WC_Order ? $email->object : null;
	$cliente = method_exists( $email, 'is_customer_email' ) ? (bool) $email->is_customer_email() : true;
	$idioma  = ! $cliente ? 'es' : ( $pedido && function_exists( 'dip_avisos_idioma' ) ? dip_avisos_idioma( $pedido ) : dip_email_idioma_de_locale() );
	$ctx     = array(
		'idioma'     => $idioma,
		'previo'     => '',
		'antetitulo' => $pedido ? sprintf( dip_avisos_textos( $idioma )['pedido'], $pedido->get_order_number() ) : '',
	);
	// Pedido recibido: antetítulo y texto previo propios (inc/avisos.php)
	if ( $pedido && $cliente && in_array( $email->id, DIP_EMAILS_PROPIOS, true ) && function_exists( 'dip_avisos_recibido_datos' ) ) {
		$d                 = dip_avisos_recibido_datos( $pedido, $email );
		$ctx['previo']     = $d['previo'];
		$ctx['antetitulo'] = $d['antetitulo'];
	}
	/**
	 * Texto previo (preheader) de un email de WooCommerce.
	 *
	 * @param string        $previo Texto previo.
	 * @param WC_Email      $email  Email.
	 * @param WC_Order|null $pedido Pedido, si lo hay.
	 */
	$ctx['previo'] = (string) apply_filters( 'dip_email_previo', $ctx['previo'], $email, $pedido );
	return $ctx;
}

/* Los emails de WooCommerce fijan su contexto justo antes de pintar la cabecera (WC_Emails::email_header va en la 10). */
add_action( 'woocommerce_email_header', static function ( $titulo, $email = null ) {
	if ( $email instanceof WC_Email ) dip_email_contexto( dip_email_contexto_de_wc( $email ) );
}, 1, 2 );
add_action( 'woocommerce_email_footer', static function () {
	dip_email_contexto( false );
}, 99 );

/* =========================================================
 * Enlaces de la marca
 * ========================================================= */

function dip_email_url_pagina( $clave, $idioma ) {
	if ( function_exists( 'dipt_url' ) ) return dipt_url( $clave, $idioma );
	$rutas = array(
		'seguridad'   => array( 'es' => 'seguridad-del-hielo-seco', 'ca' => 'ca/seguretat-del-gel-sec', 'en' => 'en/dry-ice-safety' ),
		'condiciones' => array( 'es' => 'condiciones-de-venta', 'ca' => 'ca/condicions-de-venda', 'en' => 'en/terms-of-sale' ),
	);
	return home_url( '/' . ( $rutas[ $clave ][ $idioma ] ?? $rutas[ $clave ]['es'] ?? '' ) . '/' );
}

function dip_email_logo_url() {
	return DIP_TIENDA_URL . 'assets/img/logo-dryicepack-email.png';
}

/** "https://wa.me/34686980471". */
function dip_email_url_whatsapp() {
	return 'https://wa.me/' . dip_contacto()['whatsapp_num'];
}

/* =========================================================
 * CSS que no se puede poner en línea: modo oscuro y móvil
 * ========================================================= */

function dip_email_css() {
	return '@media only screen and (max-width:620px){'
		. '.dip-marco{padding:12px 8px !important}'
		. '.dip-pad{padding-left:20px !important;padding-right:20px !important}'
		. '.dip-banda{padding:16px 20px !important}'
		. '.dip-logo{width:136px !important;height:auto !important}'
		. '.dip-h1{font-size:23px !important;line-height:29px !important}'
		. '.dip-ficha-et{width:36% !important}'
		. '.dip-total-valor{font-size:20px !important}'
		. '}'
		. '@media (prefers-color-scheme:dark){'
		. '.dip-fondo{background-color:#0B1620 !important}'
		. '.dip-tarjeta{background-color:#13212E !important;border-color:#24384A !important}'
		. '.dip-cuerpo,.dip-tinta,.dip-cuerpo p,.dip-cuerpo td,.dip-cuerpo th,.dip-cuerpo li,.dip-cuerpo h2,.dip-cuerpo h3,.dip-cuerpo span,.dip-cuerpo div{color:#E8EEF3 !important}'
		. '.dip-cuerpo .dip-suave,.dip-suave,.dip-cuerpo .dip-ficha-et,.dip-ante{color:#A9B8C6 !important}'
		. '.dip-cuerpo a,.dip-enlace{color:#8FC3EA !important}'
		. '.dip-cuerpo .dip-caja,.dip-caja{background-color:#0F1C27 !important;border-left-color:#8FB8DA !important}'
		. '.dip-cuerpo .dip-linea,.dip-linea,.dip-cuerpo td,.dip-cuerpo th{border-color:#24384A !important}'
		. '.dip-cuerpo .dip-num{color:#8FB8DA !important}'
		. '.dip-ante,.dip-marca-borde{border-color:#8FB8DA !important}'
		. '.dip-cuerpo a.dip-boton,a.dip-boton{background-color:#8FB8DA !important;border-color:#8FB8DA !important;color:#0B1620 !important}'
		. '.dip-cuerpo .dip-total,.dip-total{background-color:#1F3C55 !important}'
		. '.dip-cuerpo .dip-total td,.dip-cuerpo .dip-total span,.dip-total td{color:#FFFFFF !important}'
		. '.dip-pie p,.dip-pie td{color:#A9B8C6 !important}'
		. '.dip-pie a{color:#C9D6E2 !important}'
		. '}';
}

/* =========================================================
 * Cabecera y pie
 * ========================================================= */

/**
 * Principio del documento hasta el contenedor del cuerpo, con el antetítulo y el título (h1) si los hay.
 * Lo usan la plantilla emails/email-header.php y dip_email_documento().
 */
function dip_email_cabecera( $titulo = '' ) {
	$ctx    = dip_email_contexto();
	$idioma = $ctx['idioma'];
	$t      = dip_avisos_textos( $idioma );
	$titulo = trim( wp_strip_all_tags( (string) $titulo ) );
	$previo = trim( wp_strip_all_tags( (string) $ctx['previo'] ) );
	$ante   = trim( wp_strip_all_tags( (string) $ctx['antetitulo'] ) );

	$h  = '<!DOCTYPE html>';
	$h .= '<html lang="' . esc_attr( $idioma ) . '" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">';
	$h .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
	$h .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
	$h .= '<meta name="x-apple-disable-message-reformatting">';
	$h .= '<meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">';
	$h .= '<meta name="color-scheme" content="light dark"><meta name="supported-color-schemes" content="light dark">';
	$h .= '<title>' . esc_html( $titulo ?: 'DryIcePack' ) . '</title>';
	$h .= '<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->';
	$h .= '<style type="text/css">' . dip_email_css() . '</style>';
	$h .= '</head>';
	$h .= '<body class="dip-fondo" style="margin:0;padding:0;width:100%;background-color:' . dip_email_color( 'fondo' ) . ';-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%">';
	if ( '' !== $previo ) {
		// Oculto sin display:none; el relleno evita que el programa de correo enseñe detrás el principio del cuerpo
		$h .= '<div class="dip-previo" style="max-height:0;max-width:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:' . dip_email_color( 'fondo' ) . ';opacity:0">'
			. esc_html( $previo ) . str_repeat( '&#8199;&#847; ', 40 ) . '</div>';
	}
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" id="outer_wrapper" class="dip-fondo" bgcolor="' . dip_email_color( 'fondo' ) . '" style="width:100%;background-color:' . dip_email_color( 'fondo' ) . '">';
	$h .= '<tr><td align="center" class="dip-marco" style="padding:32px 12px">';
	$h .= '<!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dip-tarjeta" bgcolor="' . dip_email_color( 'tarjeta' ) . '" style="max-width:600px;width:100%;background-color:' . dip_email_color( 'tarjeta' ) . ';border:1px solid ' . dip_email_color( 'borde' ) . ';border-radius:6px;border-collapse:separate">';
	// Banda marino: logo y la cifra de la casa
	$h .= '<tr><td class="dip-banda" bgcolor="' . dip_email_color( 'marino' ) . '" style="background-color:' . dip_email_color( 'marino' ) . ';padding:20px 40px;border-radius:5px 5px 0 0">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
	$h .= '<td style="vertical-align:middle"><a href="' . esc_url( home_url( 'es' === $idioma ? '/' : '/' . $idioma . '/' ) ) . '" style="text-decoration:none;color:#FFFFFF">'
		. '<img class="dip-logo" src="' . esc_url( dip_email_logo_url() ) . '" width="160" height="40" alt="DryIcePack" style="display:block;width:160px;height:auto;border:0;outline:none;text-decoration:none;' . dip_email_texto_estilo( 18, 24, 700, 'blanco' ) . '"></a></td>';
	$h .= '<td align="right" style="vertical-align:middle;white-space:nowrap;' . dip_email_texto_estilo( 12, 16, 500, 'hielo', 'mono', array( 'letter-spacing' => '0.06em' ) ) . '">' . esc_html( $t['frio'] ) . '</td>';
	$h .= '</tr></table></td></tr>';
	// Cuerpo
	$h .= '<tr><td class="dip-pad" id="body_content" style="padding:36px 40px 8px;text-align:left">';
	if ( '' !== $ante ) {
		$h .= '<p class="dip-ante" style="margin:0 0 14px;padding:0 0 0 10px;border-left:2px solid ' . dip_email_color( 'marino' ) . ';' . dip_email_texto_estilo( 13, 18, 500, 'suave', 'sans' ) . '">' . esc_html( $ante ) . '</p>';
	}
	if ( '' !== $titulo ) {
		// Sin viudas: la última palabra va con la anterior («las 14:00» no se separa)
		$h .= '<h1 class="dip-h1 dip-tinta" style="margin:0 0 18px;' . dip_email_texto_estilo( 28, 34, 700, 'tinta', 'sans', array( 'letter-spacing' => '-0.01em' ) ) . '">' . esc_html( (string) preg_replace( '/ (\S+)$/u', "\u{00A0}$1", $titulo ) ) . '</h1>';
	}
	$h .= '<div id="body_content_inner" class="dip-cuerpo" style="text-align:left;' . dip_email_texto_estilo( 16, 25 ) . '">';
	return $h;
}

/** Final del documento: pie con la empresa, el contacto y los enlaces a seguridad y condiciones. Borra el contexto. */
function dip_email_pie() {
	$ctx    = dip_email_contexto();
	$idioma = $ctx['idioma'];
	$t      = dip_avisos_textos( $idioma );
	$c      = dip_contacto();
	$a      = static fn( $url, $texto ) => '<a href="' . esc_url( $url ) . '" style="color:' . dip_email_color( 'suave' ) . ';text-decoration:underline">' . esc_html( $texto ) . '</a>';
	$tel    = ( 'en' === $idioma ? '+34 ' : '' ) . $c['telefono'];
	$wa     = ( 'en' === $idioma ? '+34 ' : '' ) . $c['whatsapp'];

	$h  = '</div></td></tr>';
	$h .= '<tr><td class="dip-pad dip-pie" style="padding:24px 40px 30px">';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="dip-linea" style="border-top:1px solid ' . dip_email_color( 'linea' ) . ';padding-top:20px">';
	$h .= '<p style="margin:0 0 8px;' . dip_email_texto_estilo( 13, 18, 500, 'suave', 'sans' ) . '">' . esc_html( $c['marca'] . ' · ' . $c['empresa'] . ' · ' . $c['localidad'] ) . '</p>';
	$h .= '<p style="margin:0 0 6px;' . dip_email_texto_estilo( 13, 20, 400, 'suave' ) . '">'
		. $a( $c['telefono_href'], $tel ) . ' · ' . $a( dip_email_url_whatsapp(), sprintf( $t['pie_whatsapp'], $wa ) ) . ' · '
		. $a( 'mailto:' . $c['email'], $c['email'] ) . ' · ' . esc_html( $t['pie_horario'] ) . '</p>';
	$h .= '<p style="margin:0;' . dip_email_texto_estilo( 13, 20, 400, 'suave' ) . '">'
		. $a( dip_email_url_pagina( 'seguridad', $idioma ), $t['pie_seguridad'] ) . ' · ' . $a( dip_email_url_pagina( 'condiciones', $idioma ), $t['pie_condiciones'] ) . '</p>';
	$h .= '</td></tr></table></td></tr>';
	$h .= '</table>';
	$h .= '<!--[if mso]></td></tr></table><![endif]-->';
	$h .= '</td></tr></table></body></html>';
	dip_email_contexto( false );
	return $h;
}

/** Pie en texto plano. */
function dip_email_pie_texto( $idioma ) {
	$t   = dip_avisos_textos( $idioma );
	$c   = dip_contacto();
	$pre = 'en' === $idioma ? '+34 ' : '';
	return "-- \n" . $c['marca'] . ' · ' . $c['empresa'] . ' · ' . $c['localidad'] . "\n"
		. $pre . $c['telefono'] . ' · ' . sprintf( $t['pie_whatsapp'], $pre . $c['whatsapp'] ) . ' · ' . $c['email'] . ' · ' . $t['pie_horario'] . "\n"
		. sprintf( $t['enlace_texto'], $t['pie_seguridad'], dip_email_url_pagina( 'seguridad', $idioma ) ) . "\n"
		. sprintf( $t['enlace_texto'], $t['pie_condiciones'], dip_email_url_pagina( 'condiciones', $idioma ) ) . "\n";
}

/* =========================================================
 * Piezas del cuerpo (todas con estilos en línea)
 * ========================================================= */

/**
 * Texto escapado en el que una cifra no se separa de su unidad al cortar la línea: «3 mm», «10 kg», «−78,5 °C», «60,00 €».
 */
function dip_email_nb( $texto ) {
	// También el piso: «2º 1ª» no se separa
	return esc_html( (string) preg_replace( array( '/(\d) (mm|kg|°C|€|%|h\b)/u', '/(\d[ºª]) (\d+[ºª])/u' ), "$1\u{00A0}$2", (string) $texto ) );
}

/** Enlace con el color de la marca. */
function dip_email_enlace( $url, $texto ) {
	return '<a class="dip-enlace" href="' . esc_url( $url ) . '" style="color:' . dip_email_color( 'marino' ) . ';text-decoration:underline">' . esc_html( $texto ) . '</a>';
}

/** Botón en píldora: VML para Outlook en Windows y un enlace normal para el resto. */
function dip_email_boton( $texto, $url ) {
	$texto = (string) $texto;
	$ancho = max( 200, (int) ceil( mb_strlen( $texto ) * 9.5 ) + 64 );
	$vml   = '<!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . esc_url( $url ) . '" style="height:48px;v-text-anchor:middle;width:' . $ancho . 'px" arcsize="50%" stroke="f" fillcolor="' . dip_email_color( 'marino' ) . '"><w:anchorlock/><center style="color:#FFFFFF;font-family:Segoe UI,Arial,sans-serif;font-size:16px;font-weight:600">' . esc_html( $texto ) . '</center></v:roundrect><![endif]-->';
	$a     = '<!--[if !mso]><!--><a class="dip-boton" href="' . esc_url( $url ) . '" style="display:inline-block;background-color:' . dip_email_color( 'marino' ) . ';border:1px solid ' . dip_email_color( 'marino' ) . ';border-radius:999px;padding:13px 28px;text-decoration:none;mso-hide:all;' . dip_email_texto_estilo( 16, 20, 600, 'blanco' ) . '">' . esc_html( $texto ) . '</a><!--<![endif]-->';
	return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:4px 0 28px"><tr><td>' . $vml . $a . '</td></tr></table>';
}

/** Antetítulo pequeño en monoespaciada (dentro del cuerpo). */
function dip_email_rotulo( $texto, $margen = '0 0 10px' ) {
	return '<p class="dip-suave" style="margin:' . $margen . ';' . dip_email_texto_estilo( 13, 18, 500, 'suave', 'sans' ) . '">' . esc_html( $texto ) . '</p>';
}

/** Párrafo. $estilo: normal · grande · fuerte · suave · pequeno. $enlace: [texto, url] al final. */
function dip_email_parrafo( $texto, $estilo = 'normal', $enlace = null ) {
	$tipos = array(
		'normal'  => array( 16, 25, 400, 'tinta', '0 0 16px' ),
		'grande'  => array( 18, 27, 400, 'tinta', '0 0 18px' ),
		'fuerte'  => array( 16, 25, 600, 'tinta', '0 0 16px' ),
		'suave'   => array( 14, 21, 400, 'suave', '0 0 14px' ),
		'pequeno' => array( 12, 18, 400, 'suave', '0 0 12px' ),
	);
	$e     = $tipos[ $estilo ] ?? $tipos['normal'];
	$clase = 'suave' === $e[3] ? ' class="dip-suave"' : '';
	$extra = $enlace ? ' ' . dip_email_enlace( $enlace[1], $enlace[0] ) : '';
	return '<p' . $clase . ' style="margin:' . $e[4] . ';' . dip_email_texto_estilo( $e[0], $e[1], $e[2], $e[3] ) . '">' . dip_email_nb( $texto ) . $extra . '</p>';
}

/**
 * Ficha técnica: etiqueta en monoespaciada pequeña y valor en monoespaciada.
 * $filas: [ [etiqueta, valor, url_opcional, texto_del_enlace_opcional], … ]
 */
function dip_email_ficha( array $filas ) {
	$filas = array_values( array_filter( $filas, static fn( $f ) => is_array( $f ) && '' !== trim( (string) ( $f[1] ?? '' ) ) ) );
	if ( ! $filas ) return '';
	$h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dip-marca-borde" style="border-collapse:collapse;border-top:2px solid ' . dip_email_color( 'marino' ) . ';margin:6px 0 26px">';
	foreach ( $filas as $f ) {
		$enlace = ! empty( $f[2] ) ? '<br>' . '<a class="dip-enlace" href="' . esc_url( $f[2] ) . '" style="color:' . dip_email_color( 'marino' ) . ';text-decoration:underline;' . dip_email_estilo( array( 'font-family' => dip_email_fuente( 'sans' ), 'font-size' => '14px', 'font-weight' => '500' ) ) . '">' . esc_html( (string) ( $f[3] ?? $f[2] ) ) . '</a>' : '';
		$h .= '<tr>'
			. '<td class="dip-ficha-et dip-linea" valign="top" style="width:32%;padding:11px 12px 11px 0;border-bottom:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;' . dip_email_texto_estilo( 13, 20, 500, 'suave', 'sans' ) . '">' . esc_html( (string) $f[0] ) . '</td>'
			. '<td class="dip-linea dip-tinta" valign="top" style="padding:11px 0;border-bottom:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;' . dip_email_texto_estilo( 14, 20, 500, 'tinta', 'mono' ) . '">' . nl2br( dip_email_nb( $f[1] ) ) . $enlace . '</td>'
			. '</tr>';
	}
	return $h . '</table>';
}

/** Recuadro con borde marino a la izquierda: título, párrafos y un enlace opcional [texto, url]. */
function dip_email_caja( $titulo, array $textos, $enlace = null ) {
	$h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dip-caja" bgcolor="' . dip_email_color( 'caja' ) . '" style="background-color:' . dip_email_color( 'caja' ) . ';border-left:3px solid ' . dip_email_color( 'marino' ) . ';margin:0 0 24px"><tr><td style="padding:16px 20px">';
	if ( '' !== (string) $titulo ) $h .= '<p style="margin:0 0 6px;' . dip_email_texto_estilo( 15, 22, 700 ) . '">' . esc_html( (string) $titulo ) . '</p>';
	$n = count( $textos );
	foreach ( array_values( $textos ) as $i => $tx ) {
		$ultimo = $i === $n - 1 && ! $enlace;
		$h .= '<p style="margin:0 0 ' . ( $ultimo ? '0' : '8px' ) . ';' . dip_email_texto_estilo( 15, 22 ) . '">' . dip_email_nb( $tx ) . '</p>';
	}
	if ( $enlace ) $h .= '<p style="margin:0;' . dip_email_texto_estilo( 15, 22, 600 ) . '">' . dip_email_enlace( $enlace[1], $enlace[0] ) . '</p>';
	return $h . '</td></tr></table>';
}

/** «Qué pasa ahora»: pasos numerados 01, 02, 03 en monoespaciada, separados por líneas finas. */
function dip_email_pasos( $titulo, array $pasos ) {
	$h = dip_email_rotulo( $titulo );
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 28px">';
	foreach ( array_values( $pasos ) as $i => $paso ) {
		$h .= '<tr>'
			. '<td class="dip-num dip-linea" valign="top" style="width:40px;padding:12px 0;border-top:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;' . dip_email_texto_estilo( 13, 22, 600, 'marino', 'mono' ) . '">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</td>'
			. '<td class="dip-linea" valign="top" style="padding:12px 0;border-top:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;' . dip_email_texto_estilo( 15, 22 ) . '">' . dip_email_nb( $paso ) . '</td>'
			. '</tr>';
	}
	return $h . '</table>';
}

/** Opciones numeradas (incidencia): [ ['titulo' => …, 'textos' => […]], … ]. */
function dip_email_opciones( array $opciones ) {
	$h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 24px">';
	foreach ( array_values( $opciones ) as $i => $o ) {
		$cuerpo = '<p style="margin:0 0 4px;' . dip_email_texto_estilo( 16, 24, 700 ) . '">' . dip_email_nb( $o['titulo'] ) . '</p>';
		foreach ( (array) $o['textos'] as $tx ) $cuerpo .= '<p style="margin:0 0 6px;' . dip_email_texto_estilo( 15, 22 ) . '">' . dip_email_nb( $tx ) . '</p>';
		$h .= '<tr>'
			. '<td class="dip-num dip-linea" valign="top" style="width:40px;padding:14px 0 10px;border-top:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;' . dip_email_texto_estilo( 13, 24, 600, 'marino', 'mono' ) . '">' . esc_html( sprintf( '%02d', $i + 1 ) ) . '</td>'
			. '<td class="dip-linea" valign="top" style="padding:14px 0 10px;border-top:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top">' . $cuerpo . '</td>'
			. '</tr>';
	}
	return $h . '</table>';
}

/** Bloque legal: recuadro gris con texto pequeño. */
function dip_email_legal( $titulo, array $textos ) {
	$h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dip-caja" bgcolor="' . dip_email_color( 'caja' ) . '" style="background-color:' . dip_email_color( 'caja' ) . ';border-radius:4px;margin:8px 0 12px"><tr><td style="padding:18px 20px 8px">';
	$h .= dip_email_rotulo( $titulo, '0 0 10px' );
	foreach ( $textos as $tx ) $h .= '<p class="dip-suave" style="margin:0 0 10px;' . dip_email_texto_estilo( 13, 20, 400, 'suave' ) . '">' . dip_email_nb( $tx ) . '</p>';
	return $h . '</td></tr></table>';
}

/**
 * Tabla de líneas con importes (pedido recibido y factura simplificada).
 * $filas: [ ['titulo' => …, 'detalle' => …, 'importe' => '64,49 €', 'cantidad' => 1], … ]
 * $sumas: [ [etiqueta, valor], … ] encima del total. $total: [etiqueta, valor]. $notas: textos pequeños debajo.
 */
function dip_email_lineas( $rotulo, array $filas, array $sumas, array $total, array $notas = array(), $rotulo_importe = '' ) {
	$h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:0">';
	$h .= '<tr><td class="dip-marca-borde dip-suave" style="padding:0 12px 8px 0;border-bottom:2px solid ' . dip_email_color( 'marino' ) . ';' . dip_email_texto_estilo( 13, 18, 500, 'suave', 'sans' ) . '">' . esc_html( (string) $rotulo ) . '</td>'
		. '<td align="right" class="dip-marca-borde dip-suave" style="padding:0 0 8px;border-bottom:2px solid ' . dip_email_color( 'marino' ) . ';white-space:nowrap;' . dip_email_texto_estilo( 13, 18, 500, 'suave', 'sans' ) . '">' . esc_html( (string) $rotulo_importe ) . '</td></tr>';
	foreach ( $filas as $f ) {
		$cant = (int) ( $f['cantidad'] ?? 1 );
		$h .= '<tr><td class="dip-linea" valign="top" style="padding:14px 12px 14px 0;border-bottom:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top">'
			. '<div style="' . dip_email_texto_estilo( 15, 21, 600 ) . '">' . ( $cant > 1 ? esc_html( $cant . "\u{00A0}× " ) : '' ) . dip_email_nb( $f['titulo'] ) . '</div>'
			. ( ! empty( $f['detalle'] ) ? '<div class="dip-suave" style="' . dip_email_texto_estilo( 13, 19, 400, 'suave' ) . '">' . dip_email_nb( $f['detalle'] ) . '</div>' : '' )
			. '</td><td class="dip-linea" align="right" valign="top" style="padding:14px 0;border-bottom:1px solid ' . dip_email_color( 'linea' ) . ';vertical-align:top;white-space:nowrap;' . dip_email_texto_estilo( 15, 21, 500, 'tinta', 'mono' ) . '">' . esc_html( (string) $f['importe'] ) . '</td></tr>';
	}
	foreach ( $sumas as $s ) {
		$h .= '<tr><td class="dip-suave" style="padding:10px 12px 0 0;' . dip_email_texto_estilo( 14, 20, 400, 'suave' ) . '">' . esc_html( (string) $s[0] ) . '</td>'
			. '<td align="right" style="padding:10px 0 0;white-space:nowrap;' . dip_email_texto_estilo( 14, 20, 400, 'tinta', 'mono' ) . '">' . esc_html( (string) $s[1] ) . '</td></tr>';
	}
	$h .= '</table>';
	$h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dip-total" bgcolor="' . dip_email_color( 'marino' ) . '" style="margin:14px 0 0;background-color:' . dip_email_color( 'marino' ) . ';border-radius:4px"><tr>'
		. '<td style="padding:14px 18px;' . dip_email_texto_estilo( 15, 22, 700, 'blanco' ) . '">' . esc_html( (string) $total[0] ) . '</td>'
		. '<td align="right" class="dip-total-valor" style="padding:14px 18px;white-space:nowrap;' . dip_email_texto_estilo( 22, 26, 700, 'blanco', 'mono' ) . '">' . esc_html( (string) $total[1] ) . '</td>'
		. '</tr></table>';
	foreach ( $notas as $n ) {
		if ( '' === trim( (string) $n ) ) continue;
		$h .= '<p class="dip-suave" style="margin:8px 0 0;' . dip_email_texto_estilo( 13, 19, 400, 'suave' ) . '">' . esc_html( (string) $n ) . '</p>';
	}
	return '<div style="margin:0 0 28px">' . $h . '</div>';
}

/* =========================================================
 * Bloques: el mismo contenido en HTML y en texto plano
 * ========================================================= */

/**
 * Cuerpo HTML a partir de bloques:
 *   ['p', texto, estilo?, [texto_enlace, url]?] · ['ficha', filas] · ['boton', texto, url] · ['caja', titulo, textos, enlace?]
 *   ['pasos', titulo, pasos] · ['opciones', opciones] · ['legal', titulo, textos] · ['rotulo', texto]
 *   ['lineas', rotulo, filas, sumas, total, notas, rotulo_importe] · ['html', html, texto]
 */
function dip_email_bloques_html( array $bloques ) {
	$h = '';
	foreach ( $bloques as $b ) {
		if ( ! is_array( $b ) || empty( $b[0] ) ) continue;
		switch ( $b[0] ) {
			case 'p':
				$h .= dip_email_parrafo( $b[1], $b[2] ?? 'normal', $b[3] ?? null );
				break;
			case 'ficha':
				$h .= dip_email_ficha( (array) $b[1] );
				break;
			case 'boton':
				if ( ! empty( $b[2] ) ) $h .= dip_email_boton( $b[1], $b[2] );
				break;
			case 'caja':
				$h .= dip_email_caja( $b[1], (array) $b[2], $b[3] ?? null );
				break;
			case 'pasos':
				$h .= dip_email_pasos( $b[1], (array) $b[2] );
				break;
			case 'opciones':
				$h .= dip_email_opciones( (array) $b[1] );
				break;
			case 'legal':
				$h .= dip_email_legal( $b[1], (array) $b[2] );
				break;
			case 'rotulo':
				$h .= dip_email_rotulo( $b[1] );
				break;
			case 'lineas':
				$h .= dip_email_lineas( $b[1], (array) $b[2], (array) ( $b[3] ?? array() ), (array) $b[4], (array) ( $b[5] ?? array() ), (string) ( $b[6] ?? '' ) );
				break;
			case 'html':
				$h .= (string) $b[1];
				break;
		}
	}
	return $h;
}

/** Los mismos bloques en texto plano. */
function dip_email_bloques_texto( array $bloques, $idioma = 'es' ) {
	$t      = dip_avisos_textos( $idioma );
	$may    = static fn( $s ) => function_exists( 'mb_strtoupper' ) ? mb_strtoupper( (string) $s ) : strtoupper( (string) $s );
	$partes = array();
	foreach ( $bloques as $b ) {
		if ( ! is_array( $b ) || empty( $b[0] ) ) continue;
		switch ( $b[0] ) {
			case 'p':
				$partes[] = (string) $b[1] . ( ! empty( $b[3] ) ? "\n" . sprintf( $t['enlace_texto'], $b[3][0], $b[3][1] ) : '' );
				break;
			case 'ficha':
				$l = array();
				foreach ( (array) $b[1] as $f ) {
					if ( '' === trim( (string) ( $f[1] ?? '' ) ) ) continue;
					$l[] = $f[0] . ': ' . str_replace( "\n", ' · ', (string) $f[1] ) . ( ! empty( $f[2] ) ? "\n  " . sprintf( $t['enlace_texto'], $f[3] ?? $f[0], $f[2] ) : '' );
				}
				if ( $l ) $partes[] = implode( "\n", $l );
				break;
			case 'boton':
				if ( ! empty( $b[2] ) ) $partes[] = sprintf( $t['enlace_texto'], $b[1], $b[2] );
				break;
			case 'caja':
				$partes[] = trim( ( '' !== (string) $b[1] ? $may( $b[1] ) . "\n" : '' ) . implode( "\n", (array) $b[2] ) . ( ! empty( $b[3] ) ? "\n" . sprintf( $t['enlace_texto'], $b[3][0], $b[3][1] ) : '' ) );
				break;
			case 'pasos':
				$l = array( $may( $b[1] ) );
				foreach ( array_values( (array) $b[2] ) as $i => $p ) $l[] = ( $i + 1 ) . '. ' . $p;
				$partes[] = implode( "\n", $l );
				break;
			case 'opciones':
				$l = array();
				foreach ( array_values( (array) $b[1] ) as $i => $o ) $l[] = ( $i + 1 ) . '. ' . $o['titulo'] . "\n   " . implode( "\n   ", (array) $o['textos'] );
				$partes[] = implode( "\n", $l );
				break;
			case 'legal':
				$partes[] = $may( $b[1] ) . "\n" . implode( "\n", (array) $b[2] );
				break;
			case 'rotulo':
				$partes[] = $may( $b[1] );
				break;
			case 'lineas':
				$l = array( $may( $b[1] ) );
				foreach ( (array) $b[2] as $f ) {
					$cant = (int) ( $f['cantidad'] ?? 1 );
					$l[]  = '- ' . ( $cant > 1 ? $cant . ' × ' : '' ) . $f['titulo'] . ( ! empty( $f['detalle'] ) ? ' (' . $f['detalle'] . ')' : '' ) . ': ' . $f['importe'];
				}
				foreach ( (array) ( $b[3] ?? array() ) as $s ) $l[] = $s[0] . ': ' . $s[1];
				$l[] = $b[4][0] . ': ' . $b[4][1];
				foreach ( (array) ( $b[5] ?? array() ) as $n ) {
					if ( '' !== trim( (string) $n ) ) $l[] = $n;
				}
				$partes[] = implode( "\n", $l );
				break;
			case 'html':
				if ( ! empty( $b[2] ) ) $partes[] = (string) $b[2];
				break;
		}
	}
	return str_replace( "\u{00A0}", ' ', implode( "\n\n", array_filter( $partes, static fn( $p ) => '' !== trim( (string) $p ) ) ) );
}

/* =========================================================
 * Documento completo (emails propios) y su texto plano
 * ========================================================= */

/**
 * Email completo con la envoltura común.
 * $args: idioma, previo, antetitulo, titulo y bloques (o 'cuerpo' con HTML ya hecho).
 */
function dip_email_documento( array $args ) {
	$args = array_merge( array( 'idioma' => 'es', 'previo' => '', 'antetitulo' => '', 'titulo' => '', 'bloques' => array(), 'cuerpo' => null ), $args );
	dip_email_contexto( array( 'idioma' => $args['idioma'], 'previo' => $args['previo'], 'antetitulo' => $args['antetitulo'] ) );
	$cuerpo = null !== $args['cuerpo'] ? (string) $args['cuerpo'] : dip_email_bloques_html( (array) $args['bloques'] );
	return dip_email_cabecera( $args['titulo'] ) . $cuerpo . dip_email_pie();
}

/** Versión en texto del mismo email. */
function dip_email_texto( array $args ) {
	$args   = array_merge( array( 'idioma' => 'es', 'antetitulo' => '', 'titulo' => '', 'bloques' => array(), 'texto' => null ), $args );
	$cuerpo = null !== $args['texto'] ? (string) $args['texto'] : dip_email_bloques_texto( (array) $args['bloques'], $args['idioma'] );
	$cab    = trim( (string) $args['titulo'] );
	if ( '' !== trim( (string) $args['antetitulo'] ) ) $cab .= "\n" . $args['antetitulo'];
	return str_replace( "\u{00A0}", ' ', ( '' !== $cab ? $cab . "\n\n" : '' ) . $cuerpo . "\n\n" . dip_email_pie_texto( $args['idioma'] ) );
}

/** Pasa a línea los estilos (los de email-styles.php) para un email que no sale por WooCommerce (p. ej. con wp_mail). */
function dip_email_en_linea( $html ) {
	if ( ! class_exists( 'WC_Email' ) ) return $html;
	try {
		return ( new WC_Email() )->style_inline( $html );
	} catch ( Throwable $e ) {
		return $html;
	}
}

/* =========================================================
 * Plantillas de WooCommerce sustituidas desde el plugin
 * ========================================================= */

add_filter( 'woocommerce_locate_template', static function ( $plantilla, $nombre ) {
	static $propias = array(
		'emails/email-header.php',
		'emails/email-footer.php',
		'emails/email-styles.php',
		'emails/email-order-details.php',
		'emails/customer-processing-order.php',
		'emails/customer-on-hold-order.php',
		'emails/plain/customer-processing-order.php',
		'emails/plain/customer-on-hold-order.php',
	);
	return in_array( $nombre, $propias, true ) ? DIP_TIENDA_DIR . 'plantillas/' . $nombre : $plantilla;
}, 20, 2 );

/** Ruta de la plantilla original de WooCommerce (o la del tema, si la tuviera). */
function dip_email_plantilla_original( $nombre ) {
	$tema = locate_template( array( trailingslashit( WC()->template_path() ) . $nombre ) );
	return $tema ?: WC()->plugin_path() . '/templates/' . $nombre;
}

/**
 * Versión en texto (AltBody) de los emails de pedido recibido cuando WooCommerce los manda solo en HTML.
 * Va en phpmailer_init después de WC_Email::handle_multipart (prioridad 10), que la vacía.
 */
add_filter( 'woocommerce_mail_callback_params', static function ( $params, $email = null ) {
	$GLOBALS['dip_email_alt'] = '';
	if ( ! $email instanceof WC_Email || ! in_array( $email->id, DIP_EMAILS_PROPIOS, true ) || ! $email->object instanceof WC_Order ) return $params;
	if ( 'html' !== $email->get_email_type() || ! function_exists( 'dip_avisos_recibido_texto' ) ) return $params;
	$GLOBALS['dip_email_alt'] = dip_avisos_recibido_texto( $email->object, $email );
	return $params;
}, 10, 2 );
add_action( 'phpmailer_init', static function ( $phpmailer ) {
	if ( empty( $GLOBALS['dip_email_alt'] ) ) return;
	$phpmailer->AltBody     = (string) $GLOBALS['dip_email_alt']; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$GLOBALS['dip_email_alt'] = '';
}, 20 );
add_action( 'woocommerce_email_sent', static function () {
	$GLOBALS['dip_email_alt'] = '';
} );

/*
 * El email de «Pedido completado» de WooCommerce no se envía (decisión del 09/11/2026): el cliente ya sabe cómo va su
 * pedido por los avisos (va de camino, hoy llega, recogida). La factura simplificada (inc/factura-simplificada.php) y
 * «¿Necesitas factura?» (inc/factura.php) cuelgan del cambio de estado, no de este email: siguen saliendo.
 */
add_filter( 'woocommerce_email_enabled_customer_completed_order', '__return_false', 20 );
