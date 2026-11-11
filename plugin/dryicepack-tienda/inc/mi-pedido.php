<?php
/**
 * «Mi pedido» (09/11/2026): cómo va el pedido, sin cuenta, con un enlace firmado que no caduca mientras exista el pedido.
 *
 * - dip_url_mi_pedido( $pedido ): /mi-pedido/, /ca/la-meva-comanda/ o /en/my-order/ (idioma del pedido, _dip_idioma)
 *   con ?pedido=ID&firma=HMAC. La firma es wp_hash() del id y de la clave del pedido (order key) con un esquema propio:
 *   solo la web puede crearla y no depende de las claves AUTH_* de wp-config.
 * - Rutas virtuales del plugin (sin página en WordPress ni reglas de reescritura que vaciar): el filtro 'request'
 *   reconoce la dirección y la página se pinta dentro del tema (get_header/get_footer), que decide el idioma por la URL
 *   (dip_idioma: /ca/… y /en/…). Si el enlace llega con otro idioma, redirige al del pedido.
 * - Nunca se guarda en caché ni se indexa: DONOTCACHEPAGE (WP Rocket), nocache_headers, Cache-Control private, no-store
 *   (Cloudflare), X-Robots-Tag y meta robots noindex, Referrer-Policy no-referrer (la firma no sale hacia MRW, WhatsApp
 *   ni el mapa) y Google Analytics sin la parte de la dirección que lleva la firma.
 * - Firma mala o pedido que no existe: 404 amable que no dice si el pedido existe.
 * - Contenido: estado como línea de tiempo (envío: recibido · enviado con MRW · entregado; recogida: recibido · hora
 *   confirmada · recogido), día y hora límite (antes de las 14:00; sábado, 12:00) o datos de la recogida, número de MRW
 *   con su enlace, lo pedido y el total, qué hacer si hay un problema (las dos opciones de las condiciones de venta),
 *   WhatsApp con el número de pedido, teléfono, factura y seguridad. Textos en inc/mi-pedido-textos.php.
 * - «Ver mi pedido» en la página de gracias.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/mi-pedido-textos.php';

/* =========================================================
 * Enlace firmado
 * ========================================================= */

/** Dirección de la página en cada idioma (sin barras). */
function dip_mp_rutas() {
	return array( 'es' => 'mi-pedido', 'ca' => 'ca/la-meva-comanda', 'en' => 'en/my-order' );
}

/** Idioma de la ruta pedida si es la de «Mi pedido»; '' si no lo es. */
function dip_mp_idioma_ruta() {
	static $idioma = null;
	if ( null !== $idioma ) return $idioma;
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! function_exists( 'dip_ruta_actual' ) ) return '';
	$ruta = dip_ruta_actual();
	$encontrado = array_search( $ruta, dip_mp_rutas(), true );
	return $idioma = false === $encontrado ? '' : (string) $encontrado;
}

function dip_mp_activo() {
	return '' !== dip_mp_idioma_ruta();
}

function dip_mp_idioma( WC_Order $pedido ) {
	$idioma = (string) $pedido->get_meta( '_dip_idioma' );
	return in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
}

/** De usted a empresas: la misma regla que el email de reseñas (casilla de empresa, cuenta de empresa). */
function dip_mp_de_usted( WC_Order $pedido ) {
	if ( function_exists( 'dip_resenas_de_usted' ) ) return dip_resenas_de_usted( $pedido );
	if ( (int) $pedido->get_meta( '_dip_empresa' ) || 'cuenta' === (string) $pedido->get_meta( '_dip_canal' ) ) return true;
	return function_exists( 'dip_es_cuenta_empresa' ) && $pedido->get_customer_id() && dip_es_cuenta_empresa( (int) $pedido->get_customer_id() );
}

function dip_mp_firma( WC_Order $pedido ) {
	return wp_hash( 'mi-pedido|' . $pedido->get_id() . '|' . $pedido->get_order_key(), 'dip_mi_pedido' );
}

/** Enlace a «Mi pedido» en el idioma del pedido. */
function dip_url_mi_pedido( WC_Order $pedido ): string {
	$ruta = dip_mp_rutas()[ dip_mp_idioma( $pedido ) ];
	return add_query_arg( array( 'pedido' => $pedido->get_id(), 'firma' => dip_mp_firma( $pedido ) ), home_url( '/' . $ruta . '/' ) );
}

/** Pedido del enlace si la firma es buena; null si no (sin decir por qué). */
function dip_mp_pedido_del_enlace() {
	$id    = isset( $_GET['pedido'] ) ? absint( $_GET['pedido'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- la firma hace de credencial
	$firma = isset( $_GET['firma'] ) ? strtolower( sanitize_text_field( wp_unslash( $_GET['firma'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $id || ! preg_match( '/^[a-f0-9]{32,64}$/', $firma ) ) return null;
	$pedido = wc_get_order( $id );
	if ( ! $pedido instanceof WC_Order || 'shop_order' !== $pedido->get_type() ) return null;
	if ( in_array( $pedido->get_status(), array( 'trash', 'checkout-draft', 'auto-draft' ), true ) ) return null;
	return hash_equals( dip_mp_firma( $pedido ), $firma ) ? $pedido : null;
}

/* =========================================================
 * Ruta virtual y cabeceras
 * ========================================================= */

/* La dirección de «Mi pedido» no busca ninguna página de WordPress: se queda con una sola variable propia. */
add_filter( 'request', static function ( $vars ) {
	$idioma = dip_mp_idioma_ruta();
	return '' === $idioma ? $vars : array( 'dip_mi_pedido' => $idioma );
} );

/* Ni portada, ni blog, ni 404: una página propia (el tema no le pone plantilla ni hoja de otra página). */
add_action( 'parse_query', static function ( $q ) {
	if ( ! dip_mp_activo() || ! $q->is_main_query() ) return;
	$q->is_home     = false;
	$q->is_404      = false;
	$q->is_archive  = false;
	$q->is_singular = false;
	$q->is_page     = false;
} );
add_filter( 'posts_pre_query', static function ( $posts, $q ) {
	return dip_mp_activo() && $q->is_main_query() ? array() : $posts;
}, 10, 2 );
add_filter( 'pre_handle_404', static function ( $hecho ) {
	return dip_mp_activo() ? true : $hecho;
} );
add_filter( 'redirect_canonical', static function ( $url ) {
	return dip_mp_activo() ? false : $url;
} );

function dip_mp_cabeceras() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // WP Rocket
	nocache_headers();
	header( 'Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0' ); // también Cloudflare
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Referrer-Policy: no-referrer' ); // la firma no viaja a MRW, WhatsApp ni Google Maps
}

add_filter( 'wp_robots', static function ( $robots ) {
	return dip_mp_activo() ? array( 'noindex' => true, 'nofollow' => true ) : $robots;
}, 99 );
add_filter( 'rank_math/frontend/robots', static function ( $robots ) {
	return dip_mp_activo() ? array( 'index' => 'noindex', 'follow' => 'nofollow' ) : $robots;
}, 99 );
add_filter( 'rank_math/frontend/canonical', static function ( $url ) {
	return dip_mp_activo() ? '' : $url;
}, 99 );

function dip_mp_titulo_doc() {
	$t = dip_mp_textos( dip_mp_idioma_ruta() );
	return ( empty( $GLOBALS['dip_mp_pedido'] ) ? $t['no_titulo'] : $t['titulo_doc'] ) . ' · DryIcePack';
}
add_filter( 'pre_get_document_title', static function ( $titulo ) {
	return dip_mp_activo() ? dip_mp_titulo_doc() : $titulo;
}, 99 );
add_filter( 'rank_math/frontend/title', static function ( $titulo ) {
	return dip_mp_activo() ? dip_mp_titulo_doc() : $titulo;
}, 99 );

add_filter( 'body_class', static function ( $clases ) {
	if ( dip_mp_activo() ) $clases[] = 'dip-mi-pedido';
	return $clases;
} );

/* Google Analytics (solo si el visitante lo aceptó, inc/cookies.php): la dirección sin ?pedido=…&firma=… */
add_action( 'wp_head', static function () {
	if ( dip_mp_activo() ) echo "<script nowprocket data-cfasync=\"false\">if(window.gtag)gtag('set',{page_location:location.origin+location.pathname,page_referrer:''});</script>\n";
}, 1 );

add_action( 'wp_enqueue_scripts', static function () {
	if ( dip_mp_activo() ) {
		$css = 'assets/mi-pedido.css';
		wp_enqueue_style( 'dip-mi-pedido', DIP_TIENDA_URL . $css, wp_style_is( 'dipt-base', 'registered' ) ? array( 'dipt-base' ) : array(), (string) @filemtime( DIP_TIENDA_DIR . $css ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return;
	}
	// Página de gracias: solo el enlace «Ver mi pedido», unas líneas
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && wp_style_is( 'dipt-base', 'enqueued' ) ) {
		wp_add_inline_style( 'dipt-base', '.dip-mp-gracias{display:flex;flex-wrap:wrap;align-items:baseline;gap:.4rem 1.2rem;margin:1.5rem 0;padding:1rem 0;border-block:1px solid var(--c-linea-fuerte,#C8D3DD)}.dip-mp-gracias span{max-width:34rem}.dip-mp-gracias .enlace-flecha{font-weight:600;color:var(--c-marino,#1F3C55)}' );
	}
}, 30 );

/* Responde antes que nadie: valida el enlace, pone las cabeceras y pinta la página dentro del tema. */
add_action( 'template_redirect', 'dip_mp_responder', 1 );

function dip_mp_responder() {
	$idioma_url = dip_mp_idioma_ruta();
	if ( '' === $idioma_url ) return;
	dip_mp_cabeceras();
	$pedido = dip_mp_pedido_del_enlace();
	if ( $pedido && dip_mp_idioma( $pedido ) !== $idioma_url ) {
		wp_safe_redirect( dip_url_mi_pedido( $pedido ), 302 ); // siempre en el idioma del pedido
		exit;
	}
	$GLOBALS['dip_mp_pedido'] = $pedido;
	status_header( $pedido ? 200 : 404 );
	get_header();
	echo $pedido ? dip_mp_html( $pedido ) : dip_mp_html_no_valido( $idioma_url ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	get_footer();
	exit;
}

/* =========================================================
 * Datos del pedido
 * ========================================================= */

function dip_mp_euros( $importe, $idioma ) {
	$importe = round( (float) $importe, 2 );
	return 'en' === $idioma ? '€' . number_format( $importe, 2, '.', ',' ) : number_format( $importe, 2, ',', '.' ) . "\u{00A0}€";
}

/** 10 → "10", 2.5 → "2,5" (en: "2.5"). */
function dip_mp_num( $n, $idioma ) {
	$sep = 'en' === $idioma ? '.' : ',';
	return rtrim( rtrim( number_format( (float) $n, 2, $sep, '' ), '0' ), $sep );
}

/** "lunes 9 de noviembre, 10:42" de una marca de tiempo. */
function dip_mp_fecha_hora( $ts, $idioma, array $t ) {
	$d = ( new DateTimeImmutable( '@' . (int) $ts ) )->setTimezone( dip_zona_horaria() );
	return sprintf( $t['fecha_hora'], dip_fecha_larga( $d->format( 'Y-m-d' ), $idioma ), $d->format( 'H:i' ) );
}

function dip_mp_dia_de_ts( $ts ) {
	return ( new DateTimeImmutable( '@' . (int) $ts ) )->setTimezone( dip_zona_horaria() )->format( 'Y-m-d' );
}

/** Marca de tiempo de _dip_enviado ('AAAA-MM-DD HH:MM:SS' de Madrid) o 0. */
function dip_mp_ts_enviado( WC_Order $p ) {
	$d = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $p->get_meta( '_dip_enviado' ), dip_zona_horaria() );
	return $d ? $d->getTimestamp() : 0;
}

/**
 * Entregado o recogido: _dip_entregado (marca de tiempo o 'AAAA-MM-DD HH:MM:SS' de Madrid, si alguien lo marca) o, si no,
 * el pedido Completado (fecha en que se completó). 0 si todavía no.
 */
function dip_mp_ts_entregado( WC_Order $p ) {
	$marca = trim( (string) $p->get_meta( '_dip_entregado' ) );
	if ( '' !== $marca ) {
		if ( ctype_digit( $marca ) ) return (int) $marca;
		$d = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $marca, dip_zona_horaria() ) ?: DateTimeImmutable::createFromFormat( '!Y-m-d', $marca, dip_zona_horaria() );
		if ( $d ) return $d->getTimestamp();
	}
	if ( 'completed' !== $p->get_status() ) return 0;
	return $p->get_date_completed() ? $p->get_date_completed()->getTimestamp() : ( $p->get_date_modified() ? $p->get_date_modified()->getTimestamp() : time() );
}

/**
 * Situación del pedido: qué fase, qué titular, qué frase y qué pasos. Todo con los textos ya resueltos.
 *
 * @return array{fase: string, h1: string, entrada: string, cifra: array|null, pasos: array[]}
 */
function dip_mp_situacion( WC_Order $p, $idioma, array $t ) {
	$recoge  = dip_eh_es_recogida( $p );
	$fecha   = dip_eh_fecha( $p );
	$dia     = $fecha ? dip_fecha_larga( $fecha, $idioma ) : '';
	$limite  = $fecha ? dip_hora_entrega_pedido( $p ) : DIP_HORA_ENTREGA;
	$estado  = $p->get_status();
	$hoy     = dip_eh_ahora()->format( 'Y-m-d' );
	$creado  = $p->get_date_created() ? $p->get_date_created()->getTimestamp() : time();
	$fin     = dip_mp_ts_entregado( $p );
	$enviado = dip_mp_ts_enviado( $p );
	$numero  = (string) $p->get_meta( '_dip_mrw_envio' );
	$c       = dip_contacto();
	$nave    = $c['direccion'] . ', ' . $c['cp'] . ' ' . $c['localidad'];
	$paso    = static fn( $titulo, $detalle, $como ) => array( 'titulo' => $titulo, 'detalle' => $detalle, 'estado' => $como );
	$r       = array( 'fase' => '', 'h1' => '', 'entrada' => '', 'cifra' => null, 'pasos' => array() );

	if ( 'cancelled' === $estado ) return array_merge( $r, array( 'fase' => 'cancelado', 'h1' => $t['h1_cancelado'], 'entrada' => $t['cancelado'] ) );
	if ( 'refunded' === $estado ) return array_merge( $r, array( 'fase' => 'reembolsado', 'h1' => $t['h1_reembolsado'], 'entrada' => $t['reembolsado'] ) );
	if ( in_array( $estado, array( 'pending', 'failed' ), true ) ) return array_merge( $r, array( 'fase' => 'sin_pagar', 'h1' => $t['h1_sin_pagar'], 'entrada' => $t['sin_pagar'] ) );

	$recibido = $paso( $t['paso_recibido'], dip_mp_fecha_hora( $creado, $idioma, $t ), 'hecho' );

	if ( $recoge ) {
		$confirmada = (int) $p->get_meta( '_dip_recogida_confirmada' );
		$hora       = trim( (string) $p->get_meta( '_dip_hora_recogida' ) );
		$el_dia     = $dia ? sprintf( $t['el_dia'], $dia ) : '';
		if ( $fin ) {
			$r['fase']    = 'recogido';
			$r['h1']      = $t['h1_recogido'];
			$r['entrada'] = $t['despues'];
		} elseif ( $confirmada ) {
			$r['fase']    = 'confirmada';
			$r['h1']      = $dia ? sprintf( $t['h1_esperamos'], $dia ) : $t['h1_reservado'];
			$r['entrada'] = $hora ? sprintf( $t['recoge_hora'], $hora, $nave ) : $nave . '.';
			if ( preg_match( '/^\d{1,2}:\d{2}$/', $hora ) ) $r['cifra'] = array( 'pre' => $t['cifra_hora'], 'valor' => $hora );
		} else {
			$r['fase']    = 'reservado';
			$r['h1']      = $t['h1_reservado'];
			$r['entrada'] = $dia ? sprintf( $t['recoge_sin_hora'], $dia ) : $t['confirmaremos'] . '.';
		}
		$r['pasos'] = array(
			$recibido,
			$confirmada ? $paso( $t['paso_hora'], trim( ( $dia ? $dia : '' ) . ( $hora ? ' · ' . $hora : '' ), ' ·' ), 'hecho' ) : $paso( $t['paso_hora'], $t['confirmaremos'], $fin ? 'hecho' : 'ahora' ),
			$fin ? $paso( $t['paso_recogido'], sprintf( $t['el_dia'], dip_fecha_larga( dip_mp_dia_de_ts( $fin ), $idioma ) ), 'hecho' ) : $paso( $t['paso_recogido'], $el_dia, $confirmada ? 'ahora' : 'pendiente' ),
		);
		return $r;
	}

	// Un envío creado por la web (inc/mrw-sagec.php) tiene número desde las 8:00, pero no ha salido hasta que se avisa
	$salio    = $enviado || ( '' !== $numero && ! ( function_exists( 'dip_sagec_pendiente_de_salir' ) && dip_sagec_pendiente_de_salir( $p ) ) );
	$previsto = $dia ? sprintf( $t['previsto'], $dia, $limite ) : '';
	if ( $fin ) {
		$r['fase']    = 'entregado';
		$r['h1']      = $t['h1_entregado'];
		$r['entrada'] = $t['despues'];
	} elseif ( $fecha && $fecha < $hoy ) {
		$r['fase']    = $salio ? 'enviado' : 'retraso';
		$r['h1']      = $salio ? $t['h1_enviado'] : $t['h1_preparando'];
		$r['entrada'] = sprintf( $t['debia'], $dia, $limite );
	} else {
		$r['fase']    = $salio ? 'camino' : 'preparando';
		$r['h1']      = $salio ? $t['h1_camino'] : $t['h1_preparando'];
		$r['entrada'] = $dia ? sprintf( $t['llega'], $dia, $limite ) : '';
		if ( $dia ) $r['cifra'] = array( 'pre' => $t['cifra_antes'], 'valor' => $limite );
	}
	$salida   = $fecha ? dip_eh_salida_de( $fecha ) : '';
	$r['pasos'] = array(
		$recibido,
		$salio
			? $paso( $t['paso_enviado'], $enviado ? dip_mp_fecha_hora( $enviado, $idioma, $t ) : '', 'hecho' )
			: $paso( $t['paso_enviado'], $salida ? sprintf( $t['sale_el'], dip_fecha_larga( $salida, $idioma ) ) : '', $fin ? 'hecho' : 'ahora' ),
		$fin
			? $paso( $t['paso_entregado'], sprintf( $t['el_dia'], dip_fecha_larga( dip_mp_dia_de_ts( $fin ), $idioma ) ), 'hecho' )
			: $paso( $t['paso_entregado'], $previsto, $salio ? 'ahora' : 'pendiente' ),
	);
	return $r;
}

/** Líneas de lo pedido: [ ['texto', 'detalle', 'importe'], … ] y el total. */
function dip_mp_lineas( WC_Order $p, $idioma, array $t ) {
	$lineas = array();
	foreach ( $p->get_items() as $l ) {
		$producto = $l->get_product();
		$kg       = $producto ? (float) dip_kg_producto( $producto ) : 0.0;
		$formato  = $producto ? (string) $producto->get_attribute( 'pa_formato' ) : (string) $l->get_meta( 'pa_formato' );
		$nombre   = '';
		if ( $kg > 0 ) {
			$f      = false !== strpos( $formato, '16' ) ? $t['formato_16'] : ( false !== strpos( $formato, '3' ) ? $t['formato_3'] : '' );
			$nombre = $f ? sprintf( $t['linea'], dip_mp_num( $kg, $idioma ), $f ) : dip_mp_num( $kg, $idioma ) . ' kg';
		}
		$lineas[] = array(
			'texto'   => ( (int) $l->get_quantity() > 1 ? (int) $l->get_quantity() . "\u{00A0}× " : '' ) . ( $nombre ?: $l->get_name() ),
			'importe' => dip_mp_euros( (float) $l->get_total() + (float) $l->get_total_tax(), $idioma ),
		);
	}
	$envio    = (float) $p->get_shipping_total() + (float) $p->get_shipping_tax();
	$recoge   = dip_eh_es_recogida( $p );
	$lineas[] = array( 'texto' => $recoge ? $t['recogida_linea'] : $t['envio_mrw'], 'importe' => $envio > 0 ? dip_mp_euros( $envio, $idioma ) : $t['sin_coste'] );
	foreach ( $p->get_fees() as $fee ) {
		$lineas[] = array( 'texto' => $fee->get_name(), 'importe' => dip_mp_euros( (float) $fee->get_total() + (float) $fee->get_total_tax(), $idioma ) );
	}
	$descuento = (float) $p->get_discount_total() + (float) $p->get_discount_tax();
	if ( $descuento > 0 ) $lineas[] = array( 'texto' => $t['descuento'], 'importe' => '−' . dip_mp_euros( $descuento, $idioma ) );
	return $lineas;
}

/* =========================================================
 * HTML
 * ========================================================= */

function dip_mp_icono( $nombre ) {
	return function_exists( 'dipt_icono' ) ? dipt_icono( $nombre ) : '';
}

function dip_mp_url_tema( $clave, $idioma, $respaldo ) {
	return function_exists( 'dipt_url' ) ? dipt_url( $clave, $idioma ) : home_url( $respaldo );
}

/** Botón de WhatsApp y teléfono, con el horario. */
function dip_mp_html_contacto( array $t, $mensaje ) {
	$c  = dip_contacto();
	$wa = 'https://wa.me/' . $c['whatsapp_num'] . '?text=' . rawurlencode( $mensaje );
	return '<div class="mp-contacto">'
		. '<a class="boton boton--marino" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener noreferrer"><span>' . esc_html( $t['whatsapp'] ) . '</span><span class="boton__flecha">' . dip_mp_icono( 'whatsapp' ) . '</span></a>'
		. '<a class="tel-grande" href="' . esc_attr( $c['telefono_href'] ) . '" aria-label="' . esc_attr( sprintf( $t['llamar'], $c['telefono'] ) ) . '">' . dip_mp_icono( 'telefono' ) . esc_html( $c['telefono'] ) . '</a>'
		. '<p class="mp-contacto__horario">' . esc_html( $t['horario'] ) . '</p>'
		. '</div>';
}

function dip_mp_html( WC_Order $p ) {
	$idioma = dip_mp_idioma( $p );
	$usted  = dip_mp_de_usted( $p );
	$t      = dip_mp_textos( $idioma, $usted );
	$s      = dip_mp_situacion( $p, $idioma, $t );
	$recoge = dip_eh_es_recogida( $p );
	$fecha  = dip_eh_fecha( $p );
	$numero = (string) $p->get_meta( '_dip_mrw_envio' );
	if ( function_exists( 'dip_sagec_pendiente_de_salir' ) && dip_sagec_pendiente_de_salir( $p ) ) $numero = ''; // aún en la nave: el número sale con el aviso
	$c      = dip_contacto();
	$creado = $p->get_date_created() ? wp_date( 'j/n/Y', $p->get_date_created()->getTimestamp(), dip_zona_horaria() ) : '';
	$activo = ! in_array( $s['fase'], array( 'cancelado', 'reembolsado', 'sin_pagar' ), true );
	$fuerte = static fn( $x ) => wp_kses( $x, array( 'strong' => array() ) );

	ob_start();
	?>
	<section class="mp mp--<?php echo esc_attr( $s['fase'] ); ?>" aria-labelledby="mp-titulo">
		<div class="envoltura mp__in">
			<header class="mp-cabeza">
				<p class="mp-ref dato"><?php echo esc_html( sprintf( $t['ref'], $p->get_order_number(), $creado ) ); ?></p>
				<h1 class="t-h1" id="mp-titulo"><?php echo esc_html( $s['h1'] ); ?></h1>
				<?php if ( $s['entrada'] ) : ?><p class="entrada"><?php echo esc_html( $s['entrada'] ); ?></p><?php endif; ?>
				<?php if ( $s['cifra'] ) : ?>
					<p class="mp-cifra" aria-hidden="true"><span class="mp-cifra__pre dato"><?php echo esc_html( $s['cifra']['pre'] ); ?></span><span class="mp-cifra__valor"><?php echo esc_html( $s['cifra']['valor'] ); ?></span></p>
				<?php endif; ?>
			</header>

			<?php if ( $activo ) : ?>
			<div class="hoja mp-hoja">
				<h2 class="mp-etiqueta dato"><?php echo esc_html( $t['linea_titulo'] ); ?></h2>
				<ol class="mp-pasos">
					<?php foreach ( $s['pasos'] as $paso ) : ?>
						<li class="mp-paso mp-paso--<?php echo esc_attr( $paso['estado'] ); ?>"<?php echo 'ahora' === $paso['estado'] ? ' aria-current="step"' : ''; ?>>
							<span class="mp-paso__marca" aria-hidden="true"></span>
							<div>
								<p class="mp-paso__titulo"><?php echo esc_html( $paso['titulo'] ); ?> <span class="screen-reader-text">(<?php echo esc_html( $t[ 'sr_' . $paso['estado'] ] ); ?>)</span></p>
								<?php if ( '' !== $paso['detalle'] ) : ?><p class="mp-paso__dato dato"><?php echo esc_html( $paso['detalle'] ); ?></p><?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>

				<dl class="mp-datos">
					<?php if ( $recoge ) : ?>
						<div><dt><?php echo esc_html( $t['dt_recogida'] ); ?></dt><dd><?php
							$hora = trim( (string) $p->get_meta( '_dip_hora_recogida' ) );
							echo esc_html( trim( ( $fecha ? dip_fecha_larga( $fecha, $idioma ) : '' ) . ' · ' . ( $hora && $p->get_meta( '_dip_recogida_confirmada' ) ? $hora : $t['hora_por_confirmar'] ), ' ·' ) );
						?></dd></div>
						<div><dt><?php echo esc_html( $t['dt_donde'] ); ?></dt><dd><?php echo esc_html( $c['direccion'] . ', ' . $c['cp'] . ' ' . $c['localidad'] ); ?><br><a class="enlace-flecha" href="<?php echo esc_url( $c['mapa'] ); ?>" target="_blank" rel="noopener noreferrer"><span><?php echo esc_html( $t['mapa'] ); ?></span><?php echo dip_mp_icono( 'flecha' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo ?></a></dd></div>
						<?php if ( 'cod' === $p->get_payment_method() ) : ?>
							<div><dt><?php echo esc_html( $t['dt_importe'] ); ?></dt><dd><strong><?php echo esc_html( sprintf( $t['efectivo'], dip_mp_euros( $p->get_total(), $idioma ) ) ); ?></strong></dd></div>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( $fecha ) : ?>
							<div><dt><?php echo esc_html( $t['dt_entrega'] ); ?></dt><dd><?php echo esc_html( sprintf( $t['dd_entrega'], dip_fecha_larga( $fecha, $idioma ), dip_hora_entrega_pedido( $p ) ) ); ?></dd></div>
						<?php endif; ?>
						<?php
						$direccion = trim( implode( ', ', array_filter( array( trim( ( $p->get_shipping_address_1() ?: $p->get_billing_address_1() ) . ' ' . ( $p->get_shipping_address_2() ?: $p->get_billing_address_2() ) ), dip_eh_lugar( $p ) ) ) ) );
						if ( $direccion ) :
							?>
							<div><dt><?php echo esc_html( $t['dt_direccion'] ); ?></dt><dd><?php echo esc_html( $direccion ); ?></dd></div>
						<?php endif; ?>
						<div><dt><?php echo esc_html( $t['dt_mrw'] ); ?></dt><dd>
							<?php if ( $numero ) : ?>
								<span class="mp-mrw"><?php echo esc_html( $numero ); ?></span><br><a class="enlace-flecha" href="<?php echo esc_url( dip_url_seguimiento_mrw( $numero ) ); ?>" target="_blank" rel="noopener noreferrer"><span><?php echo esc_html( $t['mrw_seguir'] ); ?></span><?php echo dip_mp_icono( 'flecha' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo ?></a>
							<?php else : ?>
								<span class="mp-suave"><?php echo esc_html( $t['mrw_pendiente'] ); ?></span>
							<?php endif; ?>
						</dd></div>
						<?php if ( $fecha && dip_eh_es_sabado( $fecha ) && in_array( $s['fase'], array( 'preparando', 'retraso' ), true ) ) : ?>
							<div class="mp-datos__nota"><dt class="screen-reader-text"><?php echo esc_html( $t['dt_entrega'] ); ?></dt><dd><?php echo esc_html( $t['sabado_nota'] ); ?></dd></div>
						<?php endif; ?>
					<?php endif; ?>
				</dl>
			</div>
			<?php endif; ?>

			<section class="mp-pedido" aria-labelledby="mp-pedido-t">
				<h2 id="mp-pedido-t"><?php echo esc_html( $t['h2_pedido'] ); ?></h2>
				<table class="mp-tabla">
					<tbody>
						<?php foreach ( dip_mp_lineas( $p, $idioma, $t ) as $l ) : ?>
							<tr><th scope="row"><?php echo esc_html( $l['texto'] ); ?></th><td><?php echo esc_html( $l['importe'] ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr><th scope="row"><?php echo esc_html( $t['total'] ); ?> <small><?php echo esc_html( $t['iva'] ); ?></small></th><td><?php echo esc_html( dip_mp_euros( $p->get_total(), $idioma ) ); ?></td></tr>
					</tfoot>
				</table>
			</section>

			<section class="mp-problema" aria-labelledby="mp-problema-t">
				<?php if ( $activo && ! $recoge && ! in_array( $s['fase'], array( 'entregado' ), true ) ) : ?>
					<h2 id="mp-problema-t"><?php echo esc_html( $t['h2_problema'] ); ?></h2>
					<p><?php echo esc_html( $t['problema_intro'] ); ?></p>
					<ol class="mp-opciones">
						<li><span><?php echo $fuerte( $t['opcion_reembolso'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
						<li><span><?php echo $fuerte( $t['opcion_recoger'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( $fecha && dip_eh_es_sabado( $fecha ) ) : ?> <?php echo esc_html( sprintf( $t['opcion_sabado'], dip_mp_euros_redondo( DIP_RECOGIDA_SABADO_CON_IVA, $idioma ) ) ); ?><?php endif; ?></span></li>
					</ol>
					<p class="mp-legal"><?php echo esc_html( $t['problema_legal'] ); ?> <a href="<?php echo esc_url( dip_mp_url_tema( 'condiciones', $idioma, '/condiciones-de-venta/' ) ); ?>"><?php echo esc_html( $t['condiciones'] ); ?></a></p>
				<?php elseif ( $activo && $recoge && 'cod' === $p->get_payment_method() && 'recogido' !== $s['fase'] ) : ?>
					<h2 id="mp-problema-t"><?php echo esc_html( $t['h2_no_puedes'] ); ?></h2>
					<p><?php echo esc_html( $t['no_puedes'] ); ?></p>
				<?php else : ?>
					<h2 id="mp-problema-t"><?php echo esc_html( $t['h2_dudas'] ); ?></h2>
				<?php endif; ?>
				<?php echo dip_mp_html_contacto( $t, sprintf( $t['whatsapp_msg'], $p->get_order_number() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado dentro ?>
				<?php echo dip_mp_html_enlaces( $p, $idioma, $t ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado dentro ?>
			</section>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/** "10 €" / "€10" (sin decimales si es redondo), como los marcadores del tema. */
function dip_mp_euros_redondo( $importe, $idioma ) {
	$n = (float) $importe;
	if ( abs( $n - round( $n ) ) >= 0.005 ) return dip_mp_euros( $n, $idioma );
	return 'en' === $idioma ? '€' . number_format( $n, 0, '.', ',' ) : number_format( $n, 0, ',', '.' ) . "\u{00A0}€";
}

/** Factura (si se puede pedir) y seguridad, al final. */
function dip_mp_html_enlaces( WC_Order $p, $idioma, array $t ) {
	$enlaces = array();
	$con_datos = function_exists( 'dip_pedido_tiene_datos_fiscales' ) && dip_pedido_tiene_datos_fiscales( $p );
	if ( function_exists( 'dip_url_factura' ) && ! $p->get_meta( '_dip_factura_mensual' ) && ! $con_datos && ! in_array( $p->get_status(), array( 'cancelled', 'pending', 'failed' ), true ) ) {
		$enlaces[] = array( (string) $p->get_meta( '_dip_fs_numero' ) ? $t['factura_completa'] : $t['factura'], dip_url_factura( $p ) );
	}
	$html = '<div class="mp-enlaces">';
	foreach ( $enlaces as $e ) {
		$html .= '<a class="enlace-flecha" href="' . esc_url( $e[1] ) . '"><span>' . esc_html( $e[0] ) . '</span>' . dip_mp_icono( 'flecha' ) . '</a>';
	}
	$html .= '<p class="mp-seguridad"><span>' . esc_html( $t['seguridad_resumen'] ) . '</span> <a class="enlace-flecha" href="' . esc_url( dip_mp_url_tema( 'seguridad', $idioma, '/seguridad-del-hielo-seco/' ) ) . '"><span>' . esc_html( $t['seguridad'] ) . '</span>' . dip_mp_icono( 'flecha' ) . '</a></p>';
	return $html . '</div>';
}

/** Enlace con firma mala o pedido que no existe: no dice nada del pedido. */
function dip_mp_html_no_valido( $idioma ) {
	$t = dip_mp_textos( $idioma );
	return '<section class="mp mp--no" aria-labelledby="mp-titulo"><div class="envoltura mp__in">'
		. '<header class="mp-cabeza"><p class="mp-ref dato">DryIcePack</p>'
		. '<h1 class="t-h1" id="mp-titulo">' . esc_html( $t['no_titulo'] ) . '</h1>'
		. '<p class="entrada">' . esc_html( $t['no_texto'] ) . '</p>'
		. dip_mp_html_contacto( $t, $t['whatsapp_generico'] )
		. '<p class="mp-no__inicio"><a class="enlace-flecha" href="' . esc_url( dip_mp_url_tema( 'inicio', $idioma, '/' ) ) . '"><span>' . esc_html( $t['no_inicio'] ) . '</span>' . dip_mp_icono( 'flecha' ) . '</a></p>'
		. '</header></div></section>';
}

/* =========================================================
 * Página de gracias: «Ver mi pedido» (antes del bloque de factura, sin repetir lo que ya enseña WooCommerce)
 * ========================================================= */
add_action( 'woocommerce_thankyou', static function ( $pedido_id ) {
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido instanceof WC_Order || in_array( $pedido->get_status(), array( 'failed', 'pending', 'cancelled' ), true ) ) return;
	$t = dip_mp_textos( dip_mp_idioma( $pedido ), dip_mp_de_usted( $pedido ) );
	printf(
		'<p class="dip-mp-gracias"><span>%s</span> <a class="enlace-flecha" href="%s"><span>%s</span>%s</a></p>',
		esc_html( $t['gracias'] ),
		esc_url( dip_url_mi_pedido( $pedido ) ),
		esc_html( $t['gracias_boton'] ),
		dip_mp_icono( 'flecha' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo
	);
}, 4 );
