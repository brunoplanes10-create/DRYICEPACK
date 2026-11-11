<?php
/**
 * Avisos al cliente sobre su pedido (09/11/2026): «el cliente debe saber en todo momento cómo va su pedido».
 *
 * - Pedido recibido (emails customer_processing_order y customer_on_hold_order de WooCommerce, con plantilla propia):
 *   arriba el día y la hora de entrega (antes de las 14:00; sábado, antes de las 12:00) o el día, la dirección y el
 *   efectivo de la recogida; «Qué pasa ahora» en 3 pasos; «Ver mi pedido»; la tabla del pedido; el bloque legal; y
 *   las condiciones de venta en PDF adjuntas (inc/condiciones-pdf.php).
 * - Cuatro avisos con dip_aviso_enviar( $pedido, $tipo ):
 *     'camino'     «Tu hielo seco va de camino»: lo llama la pantalla «Envíos de hoy» al marcar el pedido como enviado.
 *     'hoy'        «Hoy llega tu hielo seco»: el día de entrega a las 8:00, pedidos con envío marcados como enviados.
 *     'recogida'   recordatorio la mañana de la recogida, a las 8:00.
 *     'incidencia' «Tu pedido no llega el día previsto»: a mano, desde el pedido (reembolso completo o recoger en Mataró).
 *   Cada aviso guarda en el pedido _dip_aviso_{tipo} con la fecha y deja una nota. No se repite (salvo la incidencia
 *   o con 'forzar'). Cada uno se activa o desactiva en Dryicepack → Ajustes (de serie, activados).
 * - Tarea propia de WP-Cron a las 8:00 de Madrid (evento único que se reprograma solo: no se mueve con el cambio de
 *   hora). Si un email falla: nota en el pedido y un solo aviso por pasada al correo de avisos. Si la tarea lleva más
 *   de DIP_AVISOS_CADUCA horas sin correr: email al correo de avisos (uno al día) y aviso en el escritorio.
 * - Alternativa manual: Acciones del pedido → «Enviar aviso: va de camino / hoy llega / recordatorio de recogida» y
 *   «Avisar de una incidencia en la entrega» (con un mensaje opcional en la pantalla del pedido).
 * - De usted a empresas (casilla de empresa o cuenta de empresa), igual que inc/resenas.php; idioma del pedido.
 * Usa, si existen, dip_url_mi_pedido(), dip_url_seguimiento_mrw() y las claves _dip_mrw_envio y _dip_enviado
 * (inc/envios-hoy.php e inc/mi-pedido.php).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_AVISOS_EVENTO' ) )          define( 'DIP_AVISOS_EVENTO', 'dip_avisos_diarios' );
if ( ! defined( 'DIP_AVISOS_HORA' ) )            define( 'DIP_AVISOS_HORA', '08:00' );   // hora de Madrid de la pasada diaria
if ( ! defined( 'DIP_AVISOS_CADUCA' ) )          define( 'DIP_AVISOS_CADUCA', 26 );      // horas sin pasada antes de avisar (24 h + 2 h de margen de WP-Cron)
// Hora límite de la entrega: DIP_HORA_ENTREGA (14:00) y DIP_HORA_ENTREGA_SABADO (13:00), en inc/negocio.php; la de cada pedido
// (antes de las 10:00 o de las 12:00 si la pagó) con dip_hora_entrega_pedido() de inc/envios-hoy.php
if ( ! defined( 'DIP_AVISOS_LIMITE_RECOGIDA' ) ) define( 'DIP_AVISOS_LIMITE_RECOGIDA', '13:00' ); // si WP-Cron corre más tarde, ya no se manda el recordatorio

/** Tipos de aviso y su nombre en el escritorio. */
function dip_avisos_tipos() {
	return array(
		'camino'     => 'Va de camino',
		'hoy'        => 'Hoy llega',
		'recogida'   => 'Recordatorio de recogida',
		'incidencia' => 'Incidencia en la entrega',
	);
}

/** ¿Está activado este aviso en Dryicepack → Ajustes? De serie, sí. */
function dip_avisos_activo( $tipo ) {
	$v = function_exists( 'dip_ajuste' ) ? dip_ajuste( 'aviso_' . $tipo ) : null;
	return null === $v ? true : (bool) $v;
}

/* =========================================================
 * Datos del pedido
 * ========================================================= */

function dip_avisos_ahora( $ahora = null ) {
	return ( $ahora instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface( $ahora ) : new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
}

/** 'AAAA-MM-DD HH:MM:SS' de Madrid (lo que se guarda en _dip_aviso_{tipo}). */
function dip_avisos_marca( $ahora = null ) {
	return dip_avisos_ahora( $ahora )->format( 'Y-m-d H:i:s' );
}

/** '2026-11-11 08:00:03' → '11/11/2026 8:00' para las notas. */
function dip_avisos_marca_corta( $marca ) {
	$d = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $marca, dip_zona_horaria() );
	return $d ? $d->format( 'j/n/Y G:i' ) : (string) $marca;
}

function dip_avisos_idioma( WC_Order $pedido ) {
	$idioma = (string) $pedido->get_meta( '_dip_idioma' );
	return in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
}

/** De usted: compra de empresa o autónomo (casilla del checkout) o cuenta de empresa. Igual que inc/resenas.php. */
function dip_avisos_de_usted( WC_Order $pedido ) {
	if ( function_exists( 'dip_resenas_de_usted' ) ) return (bool) dip_resenas_de_usted( $pedido );
	if ( (int) $pedido->get_meta( '_dip_empresa' ) ) return true;
	if ( 'cuenta' === (string) $pedido->get_meta( '_dip_canal' ) ) return true;
	return function_exists( 'dip_es_cuenta_empresa' ) && $pedido->get_customer_id() && dip_es_cuenta_empresa( (int) $pedido->get_customer_id() );
}

/** Nombre del saludo: de tú, el nombre de pila; de usted, el campo entero (el checkout tiene un solo campo). */
function dip_avisos_nombre( WC_Order $pedido, $usted = false ) {
	if ( function_exists( 'dip_resenas_nombre' ) ) return (string) dip_resenas_nombre( $pedido, $usted );
	$nombre = trim( (string) preg_replace( '/\s+/u', ' ', (string) $pedido->get_billing_first_name() ) );
	if ( '' === $nombre || $usted ) return $nombre;
	$pila = (string) strtok( $nombre, ' ' );
	return mb_strlen( $pila ) > 2 ? $pila : $nombre;
}

/** «Hola, Laura. Tu hielo seco…» (en inglés, «Hi Laura, your dry ice…»). Sin nombre, solo la frase. */
function dip_avisos_con_saludo( WC_Order $pedido, $frase, $idioma, $usted ) {
	$nombre = dip_avisos_nombre( $pedido, $usted );
	if ( '' === $nombre ) return (string) $frase;
	$hola = sprintf( dip_avisos_textos( $idioma, $usted )['hola'], $nombre );
	if ( 'en' === $idioma ) return $hola . ' ' . mb_strtolower( mb_substr( (string) $frase, 0, 1 ) ) . mb_substr( (string) $frase, 1 );
	return $hola . ' ' . $frase;
}

function dip_avisos_es_recogida( WC_Order $pedido ) {
	if ( function_exists( 'dip_seg_es_recogida' ) ) return (bool) dip_seg_es_recogida( $pedido );
	return 'recogida' === (string) $pedido->get_meta( '_dip_metodo_entrega' );
}

/** Día de entrega o de recogida ('Y-m-d' o ''). */
function dip_avisos_fecha( WC_Order $pedido ) {
	$f = function_exists( 'dip_seg_fecha_pedido' ) ? (string) dip_seg_fecha_pedido( $pedido ) : (string) $pedido->get_meta( '_dip_fecha_entrega' );
	return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f ) ? $f : '';
}

function dip_avisos_es_sabado( $ymd ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	return $d && 6 === (int) $d->format( 'N' );
}

/** Hora límite de la entrega: 14:00; el sábado, 13:00. */
function dip_avisos_hora_limite( $ymd ) {
	return dip_avisos_es_sabado( $ymd ) ? DIP_HORA_ENTREGA_SABADO : DIP_HORA_ENTREGA;
}

function dip_avisos_fecha_larga( $ymd, $idioma ) {
	return '' === (string) $ymd ? '' : dip_fecha_larga( $ymd, $idioma );
}

/** «Miércoles 11 de noviembre» (para la ficha). */
function dip_avisos_fecha_ficha( $ymd, $idioma ) {
	$f = dip_avisos_fecha_larga( $ymd, $idioma );
	return '' === $f ? '' : mb_strtoupper( mb_substr( $f, 0, 1 ) ) . mb_substr( $f, 1 );
}

/** «miércoles 11» (asuntos). */
function dip_avisos_dia_corto( $ymd, $idioma ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	if ( ! $d ) return '';
	return dip_nombres_fecha( $idioma )['dias'][ (int) $d->format( 'N' ) - 1 ] . ' ' . $d->format( 'j' );
}

/** 64,49 € (es, ca) o €64.49 (en). */
function dip_avisos_euros( $importe, $idioma = 'es' ) {
	if ( 'en' === $idioma ) return '€' . number_format( (float) $importe, 2, '.', ',' );
	return number_format( (float) $importe, 2, ',', '.' ) . "\u{00A0}€";
}

/** Kilos de hielo del pedido (_dip_kg o la suma de las líneas). */
function dip_avisos_kg( WC_Order $pedido ) {
	$kg = (float) str_replace( ',', '.', (string) $pedido->get_meta( '_dip_kg' ) );
	if ( $kg > 0 ) return $kg;
	foreach ( $pedido->get_items() as $linea ) $kg += (float) $linea->get_quantity() * (float) dip_kg_producto( $linea->get_product() );
	return $kg;
}

/** «20 kg de hielo seco» (o «hielo seco» si no se saben los kilos). */
function dip_avisos_kg_texto( WC_Order $pedido, $idioma ) {
	$t  = dip_avisos_textos( $idioma );
	$kg = dip_avisos_kg( $pedido );
	if ( $kg <= 0 ) return $t['r_pedido_sin_kg'];
	$sep = 'en' === $idioma ? '.' : ',';
	$num = rtrim( rtrim( number_format( $kg, 2, $sep, '' ), '0' ), $sep );
	return sprintf( $t['kg_hielo'], $num . "\u{00A0}kg" );
}

/**
 * Dirección de entrega: «Carrer de Mallorca 123, 2º 1ª, 08013 Barcelona». Con $salto = "\n", el código postal y la
 * población van en otra línea (ficha).
 */
function dip_avisos_direccion( WC_Order $pedido, $salto = ', ' ) {
	$calle = trim( (string) ( $pedido->get_shipping_address_1() ?: $pedido->get_billing_address_1() ) );
	$piso  = trim( (string) ( $pedido->get_shipping_address_1() ? $pedido->get_shipping_address_2() : $pedido->get_billing_address_2() ) );
	$cp    = trim( (string) ( $pedido->get_shipping_postcode() ?: $pedido->get_billing_postcode() ) );
	$pobl  = trim( (string) ( $pedido->get_shipping_city() ?: $pedido->get_billing_city() ) );
	$linea = implode( ', ', array_filter( array( $calle, $piso ) ) );
	return implode( $salto, array_filter( array( $linea, trim( $cp . ' ' . $pobl ) ) ) );
}

/** «dryicepack.es/condiciones-de-venta/» (sin https://) para leerla en el texto. */
function dip_avisos_url_corta( $url ) {
	return (string) preg_replace( '#^https?://(www\.)?#i', '', (string) $url );
}

/** «Camí Ca La Madrona 19 D, 08304 Mataró». */
function dip_avisos_direccion_nave() {
	$c = dip_contacto();
	return $c['direccion'] . ', ' . $c['cp'] . ' ' . $c['localidad'];
}

/** Recogida pagada en efectivo al recoger. */
function dip_avisos_efectivo( WC_Order $pedido ) {
	return dip_avisos_es_recogida( $pedido ) && 'cod' === $pedido->get_payment_method();
}

/** «Pago en efectivo al recoger: 60,00 €. Trae el importe exacto: no tenemos cambio.» (la frase del plugin; de usted, «Traiga»). */
function dip_avisos_texto_efectivo( WC_Order $pedido, $idioma, $usted ) {
	if ( ! dip_avisos_efectivo( $pedido ) ) return '';
	if ( ! $usted && function_exists( 'dip_texto_pago_efectivo' ) ) {
		$frase = (string) dip_texto_pago_efectivo( $pedido, $idioma );
		if ( '' !== $frase ) return $frase;
	}
	$importe = function_exists( 'dip_texto_euros' ) ? dip_texto_euros( $pedido->get_total() ) : dip_avisos_euros( $pedido->get_total(), $idioma );
	return sprintf( dip_avisos_textos( $idioma, true )['efectivo_usted'], $importe );
}

/** Enlace firmado a «Mi pedido» (inc/mi-pedido.php) o '' si todavía no existe. */
function dip_avisos_url_pedido( WC_Order $pedido ) {
	return function_exists( 'dip_url_mi_pedido' ) ? (string) dip_url_mi_pedido( $pedido ) : '';
}

/** Número de envío de MRW: el que se pasa al enviar el aviso o el guardado en el pedido (_dip_mrw_envio). */
function dip_avisos_mrw( WC_Order $pedido, array $extra = array() ) {
	$n = trim( (string) ( $extra['mrw'] ?? '' ) );
	return '' !== $n ? $n : trim( (string) $pedido->get_meta( '_dip_mrw_envio' ) );
}

function dip_avisos_url_mrw( $numero ) {
	return ( '' !== (string) $numero && function_exists( 'dip_url_seguimiento_mrw' ) ) ? (string) dip_url_seguimiento_mrw( $numero ) : '';
}

/** Hora acordada para la recogida (campo del pedido, inc/entrega.php) o ''. */
function dip_avisos_hora_recogida( WC_Order $pedido ) {
	return trim( (string) $pedido->get_meta( '_dip_hora_recogida' ) );
}

/** Teléfono y WhatsApp con prefijo en inglés. */
function dip_avisos_telefonos( $idioma ) {
	$c   = dip_contacto();
	$pre = 'en' === $idioma ? '+34 ' : '';
	return array( $pre . $c['telefono'], $pre . $c['whatsapp'] );
}

/* =========================================================
 * Pedido recibido (customer_processing_order y customer_on_hold_order)
 * ========================================================= */

/** ¿Es el email «En espera» (pago por confirmar)? */
function dip_avisos_en_espera( WC_Order $pedido, $email = null ) {
	if ( $email instanceof WC_Email ) return 'customer_on_hold_order' === $email->id;
	return 'on-hold' === $pedido->get_status();
}

function dip_avisos_recibido_asunto( WC_Order $pedido, $espera = false ) {
	$idioma = dip_avisos_idioma( $pedido );
	$t      = dip_avisos_textos( $idioma, dip_avisos_de_usted( $pedido ) );
	$n      = $pedido->get_order_number();
	$fecha  = dip_avisos_fecha( $pedido );
	if ( $espera ) return sprintf( $t['r_asunto_espera'], $n );
	if ( '' === $fecha ) return sprintf( $t['r_asunto'], $n );
	return sprintf( dip_avisos_es_recogida( $pedido ) ? $t['r_asunto_recogida'] : $t['r_asunto_envio'], $n, dip_avisos_dia_corto( $fecha, $idioma ) );
}

/**
 * Todo lo del email de pedido recibido, en bloques (inc/emails.php los pinta en HTML y en texto):
 * idioma, usted, asunto, titulo, antetitulo, previo, arriba (antes de la tabla), tabla, abajo (bloque legal).
 */
function dip_avisos_recibido_datos( WC_Order $pedido, $email = null ) {
	$idioma   = dip_avisos_idioma( $pedido );
	$usted    = dip_avisos_de_usted( $pedido );
	$t        = dip_avisos_textos( $idioma, $usted );
	$n        = $pedido->get_order_number();
	$espera   = dip_avisos_en_espera( $pedido, $email );
	$recoge   = dip_avisos_es_recogida( $pedido );
	$efectivo = dip_avisos_efectivo( $pedido );
	$fecha    = dip_avisos_fecha( $pedido );
	$hora     = function_exists( 'dip_hora_entrega_pedido' ) && $fecha ? dip_hora_entrega_pedido( $pedido ) : dip_avisos_hora_limite( $fecha );
	$larga    = dip_avisos_fecha_larga( $fecha, $idioma );
	$total    = dip_avisos_euros( $pedido->get_total(), $idioma );
	$nombre   = dip_avisos_nombre( $pedido, $usted );
	$kg       = dip_avisos_kg_texto( $pedido, $idioma );

	if ( '' === $fecha ) {
		$titulo = $t['r_titulo'];
	} else {
		$titulo = $recoge ? sprintf( $t['r_titulo_recogida'], $larga ) : sprintf( $t['r_titulo_envio'], $larga, $hora );
	}
	if ( $recoge ) {
		$previo = '' === $fecha ? '' : ( $efectivo ? sprintf( $t['r_previo_recogida_efectivo'], $larga, $total ) : sprintf( $t['r_previo_recogida'], $larga ) );
	} else {
		$previo = '' === $fecha ? '' : sprintf( $t['r_previo_envio'], $larga, $hora, $total );
	}

	// Arriba: lo que importa
	$arriba   = array();
	$arriba[] = array( 'p', $nombre ? sprintf( $t['r_gracias'], $nombre, $kg ) : sprintf( $t['r_gracias_sin'], $kg ), 'grande' );
	if ( $espera ) $arriba[] = array( 'p', $t['r_espera'] );
	$filas = array();
	if ( $recoge ) {
		$arriba[] = array( 'p', $t['r_hora_recogida'] );
		$pago     = dip_avisos_texto_efectivo( $pedido, $idioma, $usted );
		if ( '' !== $pago ) $arriba[] = array( 'p', $pago, 'fuerte' );
		$hora_rec = dip_avisos_hora_recogida( $pedido );
		$filas[]  = array( $t['f_recogida'], dip_avisos_fecha_ficha( $fecha, $idioma ) );
		$filas[]  = array( $t['f_hora'], '' !== $hora_rec ? $hora_rec : $t['r_hora_pendiente'] );
		$filas[]  = array( $t['f_donde'], dip_avisos_direccion_nave(), dip_contacto()['mapa'], $t['mapa'] );
		$filas[]  = $efectivo ? array( $t['f_efectivo'], sprintf( $t['v_importe_exacto'], $total ) ) : array( $t['f_total'], sprintf( $t['v_iva'], $total ) );
	} else {
		$filas[] = array( $t['f_entrega'], '' === $fecha ? '' : dip_avisos_fecha_ficha( $fecha, $idioma ) . "\n" . sprintf( $t['antes_de'], $hora ) );
		$filas[] = array( $t['f_direccion'], dip_avisos_direccion( $pedido, "\n" ) );
		$filas[] = array( $t['f_total'], sprintf( $t['v_iva'], $total ) );
	}
	$empresa = trim( (string) $pedido->get_billing_company() );
	$nif     = trim( (string) $pedido->get_meta( '_billing_nif' ) );
	if ( '' !== $empresa ) $filas[] = array( $t['f_factura'], $empresa . ( '' !== $nif ? "\n" . $nif : '' ) );
	$arriba[] = array( 'ficha', $filas );
	$arriba[] = array(
		'pasos',
		$t['r_pasos_titulo'],
		$recoge
			? array( $t['r_paso_recogida_1'], $t['r_paso_recogida_2'], $efectivo ? $t['r_paso_recogida_3_efectivo'] : $t['r_paso_recogida_3'] )
			: array( $t['r_paso_envio_1'], sprintf( $t['r_paso_envio_2'], $hora ), $t['r_paso_envio_3'] ),
	);
	$url = dip_avisos_url_pedido( $pedido );
	if ( '' !== $url ) $arriba[] = array( 'boton', $t['ver_pedido'], $url );

	// Abajo: bloque legal (soporte duradero, TRLGDCU 98.7)
	$legal = array();
	if ( '' !== $fecha ) {
		$legal[] = $recoge ? sprintf( $efectivo ? $t['l_recogida_efectivo'] : $t['l_recogida'], $larga ) : sprintf( $t['l_entrega'], $larga, $hora );
	}
	$legal[] = $t['l_desistimiento'];
	$legal[] = $t['l_derechos'];
	$cond    = dip_email_url_pagina( 'condiciones', $idioma );
	$pdf     = function_exists( 'dip_condiciones_pdf_ruta' ) ? dip_condiciones_pdf_ruta( $idioma ) : '';
	$legal[] = sprintf( '' !== $pdf ? $t['l_condiciones_pdf'] : $t['l_condiciones'], dip_avisos_url_corta( $cond ) );
	$legal[] = $t['l_reclamaciones'];

	return array(
		'idioma'     => $idioma,
		'usted'      => $usted,
		'asunto'     => dip_avisos_recibido_asunto( $pedido, $espera ),
		'titulo'     => $titulo,
		'antetitulo' => sprintf( $espera ? $t['r_antetitulo_espera'] : $t['r_antetitulo'], $n ),
		'previo'     => $previo,
		'arriba'     => $arriba,
		'tabla'      => dip_avisos_tabla_bloque( $pedido, $idioma, $usted ),
		'abajo'      => array( array( 'legal', $t['l_titulo'], $legal ) ),
	);
}

/** La plantilla emails/customer-processing-order.php (y la de «En espera»): cabecera, bloques, tabla, legal y pie. */
function dip_avisos_recibido_html( WC_Order $pedido, $email = null, $a_admin = false, $texto_plano = false ) {
	$d = dip_avisos_recibido_datos( $pedido, $email );
	do_action( 'woocommerce_email_header', $d['titulo'], $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	echo dip_email_bloques_html( $d['arriba'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	// La línea «Entrega: …» de inc/entrega.php sobra aquí: el día ya va arriba
	add_filter( 'dip_email_linea_entrega', '__return_false' );
	do_action( 'woocommerce_email_order_details', $pedido, $a_admin, $texto_plano, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	remove_filter( 'dip_email_linea_entrega', '__return_false' );
	do_action( 'woocommerce_email_order_meta', $pedido, $a_admin, $texto_plano, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	echo dip_email_bloques_html( $d['abajo'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	do_action( 'woocommerce_email_footer', $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
}

/** El mismo email en texto (plantilla de texto plano y versión alternativa del HTML). */
function dip_avisos_recibido_texto( WC_Order $pedido, $email = null ) {
	$d = dip_avisos_recibido_datos( $pedido, $email );
	return dip_email_texto( array(
		'idioma'     => $d['idioma'],
		'antetitulo' => $d['antetitulo'],
		'titulo'     => $d['titulo'],
		'bloques'    => array_merge( $d['arriba'], array( $d['tabla'] ), $d['abajo'] ),
	) );
}

/** ¿La tabla del pedido de este email es la nuestra? Solo en el de pedido recibido al cliente, en HTML. */
function dip_avisos_tabla_propia( $pedido, $a_admin, $texto_plano, $email ) {
	return $pedido instanceof WC_Order && ! $a_admin && ! $texto_plano && $email instanceof WC_Email && in_array( $email->id, DIP_EMAILS_PROPIOS, true );
}

/**
 * Bloque 'lineas' con las líneas del pedido, IVA incluido: las mismas que la factura simplificada (nombre claro del
 * formato y del pack). Si no salen (otro producto, céntimos que no cuadran), las del pedido tal cual.
 */
function dip_avisos_tabla_bloque( WC_Order $pedido, $idioma, $usted ) {
	$t      = dip_avisos_textos( $idioma, $usted );
	$filas  = array();
	$lineas = function_exists( 'dip_fs_lineas' ) ? dip_fs_lineas( $pedido ) : null;
	if ( is_array( $lineas ) && function_exists( 'dip_fs_texto_linea' ) ) {
		foreach ( $lineas as $l ) {
			list( $titulo, $detalle ) = dip_fs_texto_linea( $l, $idioma );
			$filas[] = array( 'titulo' => $titulo, 'detalle' => $detalle, 'importe' => dip_avisos_euros( $l['importe'], $idioma ), 'cantidad' => (int) ( $l['cantidad'] ?? 1 ) );
		}
	} else {
		foreach ( $pedido->get_items() as $item ) {
			$filas[] = array( 'titulo' => wp_strip_all_tags( (string) $item->get_name() ), 'detalle' => '', 'importe' => dip_avisos_euros( (float) $item->get_total() + (float) $item->get_total_tax(), $idioma ), 'cantidad' => (int) $item->get_quantity() );
		}
		foreach ( array_merge( $pedido->get_items( 'shipping' ), $pedido->get_items( 'fee' ) ) as $item ) {
			$importe = (float) $item->get_total() + (float) $item->get_total_tax();
			if ( abs( $importe ) < 0.005 ) continue;
			$filas[] = array( 'titulo' => wp_strip_all_tags( (string) $item->get_name() ), 'detalle' => '', 'importe' => dip_avisos_euros( $importe, $idioma ), 'cantidad' => 1 );
		}
	}
	$notas = array( sprintf( $t['r_iva'], dip_avisos_euros( $pedido->get_total_tax(), $idioma ) ) );
	$pago  = 'cod' === $pedido->get_payment_method() ? $t['pago_efectivo'] : trim( wp_strip_all_tags( (string) $pedido->get_payment_method_title() ) );
	if ( '' !== $pago ) $notas[] = sprintf( $t['r_pago'], $pago );
	$nota = trim( (string) $pedido->get_customer_note() );
	if ( '' !== $nota ) $notas[] = sprintf( $t['r_notas'], $nota );
	return array( 'lineas', $t['r_resumen'], $filas, array(), array( $t['r_total'], dip_avisos_euros( $pedido->get_total(), $idioma ) ), $notas );
}

function dip_avisos_tabla_html( WC_Order $pedido ) {
	return dip_email_bloques_html( array( dip_avisos_tabla_bloque( $pedido, dip_avisos_idioma( $pedido ), dip_avisos_de_usted( $pedido ) ) ) );
}

/* Asuntos propios (sin «¡»): «Pedido 1234: llega el miércoles 11», «Pedido 1234: recógelo el jueves 12». */
add_filter( 'woocommerce_email_subject_customer_processing_order', static function ( $asunto, $pedido = null ) {
	return $pedido instanceof WC_Order ? dip_avisos_recibido_asunto( $pedido, false ) : $asunto;
}, 20, 2 );
add_filter( 'woocommerce_email_subject_customer_on_hold_order', static function ( $asunto, $pedido = null ) {
	return $pedido instanceof WC_Order ? dip_avisos_recibido_asunto( $pedido, true ) : $asunto;
}, 20, 2 );

/* =========================================================
 * Los cuatro avisos
 * ========================================================= */

/** Suplemento de envío en sábado del pedido, IVA incluido (0 si no lo lleva). */
function dip_avisos_suplemento_envio( WC_Order $pedido ) {
	$suma = 0.0;
	foreach ( $pedido->get_items( 'fee' ) as $item ) {
		$nombre = (string) $item->get_name();
		if ( preg_match( '/s[áa]bado|dissabte|saturday/iu', $nombre ) && ! preg_match( '/recogida|recollida|collection/iu', $nombre ) ) {
			$suma += (float) $item->get_total() + (float) $item->get_total_tax();
		}
	}
	return round( $suma, 2 );
}

/** Envío del pedido, IVA incluido. */
function dip_avisos_importe_envio( WC_Order $pedido ) {
	$suma = 0.0;
	foreach ( $pedido->get_items( 'shipping' ) as $item ) $suma += (float) $item->get_total() + (float) $item->get_total_tax();
	return round( $suma, 2 );
}

/**
 * Asunto, texto previo, antetítulo, título y bloques de un aviso.
 * $extra: 'mrw' (número de envío), 'mensaje' (incidencia: texto de la tienda para el cliente).
 */
function dip_avisos_partes( WC_Order $pedido, $tipo, array $extra = array() ) {
	$idioma = dip_avisos_idioma( $pedido );
	$usted  = dip_avisos_de_usted( $pedido );
	$t      = dip_avisos_textos( $idioma, $usted );
	$n      = $pedido->get_order_number();
	$fecha  = dip_avisos_fecha( $pedido );
	$hora   = function_exists( 'dip_hora_entrega_pedido' ) && $fecha ? dip_hora_entrega_pedido( $pedido ) : dip_avisos_hora_limite( $fecha );
	$larga  = dip_avisos_fecha_larga( $fecha, $idioma );
	$ficha  = dip_avisos_fecha_ficha( $fecha, $idioma );
	$url    = dip_avisos_url_pedido( $pedido );
	list( $tel, $wa ) = dip_avisos_telefonos( $idioma );
	$segur  = array( $t['seguridad_enlace'], dip_email_url_pagina( 'seguridad', $idioma ) );
	$boton  = array( 'boton', $t['ver_pedido'], $url );
	$p      = array( 'idioma' => $idioma, 'usted' => $usted );

	switch ( $tipo ) {
		case 'camino':
			$mrw = dip_avisos_mrw( $pedido, $extra );
			$p  += array(
				'asunto'     => sprintf( $t['c_asunto'], $n ),
				'antetitulo' => sprintf( $t['c_antetitulo'], $n ),
				'titulo'     => '' !== $fecha ? sprintf( $t['c_titulo'], $larga, $hora ) : $t['c_intro'],
				'previo'     => '' === $fecha ? $t['c_intro'] : ( '' !== $mrw ? sprintf( $t['c_previo'], $mrw, $larga, $hora ) : sprintf( $t['c_previo_sin'], $larga, $hora ) ),
				'bloques'    => array(
					array( 'p', dip_avisos_con_saludo( $pedido, $t['c_intro'], $idioma, $usted ), 'grande' ),
					array( 'ficha', array(
						array( $t['f_mrw'], $mrw, dip_avisos_url_mrw( $mrw ), $t['seguir'] ),
						array( $t['f_llega'], '' === $fecha ? '' : $ficha . "\n" . sprintf( $t['antes_de'], $hora ) ),
						array( $t['f_direccion'], dip_avisos_direccion( $pedido, "\n" ) ),
					) ),
					array( 'caja', $t['nadie_titulo'], array( sprintf( $t['nadie'], $wa, $tel ) ) ),
					array( 'p', $t['seguridad_linea'], 'normal', $segur ),
					$boton,
				),
			);
			break;

		case 'hoy':
			$mrw = dip_avisos_mrw( $pedido, $extra );
			$dir = dip_avisos_direccion( $pedido );
			$p  += array(
				'asunto'     => sprintf( $t['h_asunto'], $n ),
				'antetitulo' => sprintf( $t['h_antetitulo'], $n ),
				'titulo'     => sprintf( $t['h_titulo'], $hora ),
				'previo'     => sprintf( $t['h_previo'], $hora, $dir ),
				'bloques'    => array(
					array( 'p', dip_avisos_con_saludo( $pedido, $t['h_intro'], $idioma, $usted ), 'grande' ),
					array( 'ficha', array(
						array( $t['f_direccion'], dip_avisos_direccion( $pedido, "\n" ) ),
						array( $t['f_llega'], '' === $fecha ? '' : $ficha . "\n" . sprintf( $t['antes_de'], $hora ) ),
						array( $t['f_mrw'], $mrw, dip_avisos_url_mrw( $mrw ), $t['seguir'] ),
					) ),
					array( 'caja', $t['no_puedes_titulo'], array( sprintf( $t['no_puedes'], $wa, $tel ) ) ),
					array( 'p', $t['seguridad_linea'], 'normal', $segur ),
					$boton,
				),
			);
			break;

		case 'recogida':
			$efectivo = dip_avisos_efectivo( $pedido );
			$total    = dip_avisos_euros( $pedido->get_total(), $idioma );
			$hora_rec = dip_avisos_hora_recogida( $pedido );
			$bloques  = array( array( 'p', dip_avisos_con_saludo( $pedido, $t['g_intro'], $idioma, $usted ), 'grande' ) );
			$pago     = dip_avisos_texto_efectivo( $pedido, $idioma, $usted );
			if ( '' !== $pago ) $bloques[] = array( 'p', $pago, 'fuerte' );
			$bloques[] = array( 'ficha', array(
				array( $t['f_recogida'], $ficha ),
				array( $t['f_hora'], '' !== $hora_rec ? $hora_rec : $t['g_hora_pendiente'] ),
				array( $t['f_donde'], dip_avisos_direccion_nave(), dip_contacto()['mapa'], $t['mapa'] ),
				$efectivo ? array( $t['f_efectivo'], sprintf( $t['v_importe_exacto'], $total ) ) : array( $t['f_total'], sprintf( $t['v_iva'], $total ) ),
			) );
			if ( dip_avisos_es_sabado( $fecha ) ) {
				$sup       = dip_suplemento_sabado( 'recogida', $fecha );
				$bloques[] = array( 'p', $sup ? sprintf( $t['g_sabado'], dip_avisos_euros( $sup['con_iva'], $idioma ) ) : $t['g_sabado_libre'], 'suave' );
			}
			$bloques[] = array( 'p', $t['g_coche'], 'normal', $segur );
			$bloques[] = array( 'caja', '', array_values( array_filter( array( sprintf( $t['g_cambio'], $tel, $wa ), $efectivo ? $t['g_no_puedes'] : '' ) ) ) );
			$bloques[] = $boton;
			$p        += array(
				'asunto'     => sprintf( $t['g_asunto'], $n ),
				'antetitulo' => sprintf( $t['g_antetitulo'], $n ),
				'titulo'     => $t['g_titulo'],
				'previo'     => $efectivo ? sprintf( $t['g_previo_efectivo'], $total ) : $t['g_previo'],
				'bloques'    => $bloques,
			);
			break;

		case 'incidencia':
			$pagado  = round( (float) $pedido->get_total() - (float) $pedido->get_total_refunded(), 2 );
			$envio   = dip_avisos_importe_envio( $pedido );
			$sup     = dip_avisos_suplemento_envio( $pedido );
			$devolver = $envio + $sup; // todo lo que pagó por el envío (desde el 11/11/2026 el sábado va en la tarifa, sin línea aparte)
			$opcion2  = array( sprintf( $sup > 0 ? $t['i_op2_sabado'] : $t['i_op2'], dip_avisos_euros( $devolver, $idioma ) ) );
			if ( $devolver > 0 && dip_avisos_es_sabado( $fecha ) ) {
				// Si lo recoge el mismo sábado, del envío nos quedamos los 10 € de la recogida en sábado y se devuelve el resto
				$rec = dip_suplemento_sabado( 'recogida', $fecha );
				$se  = $rec ? min( $devolver, (float) $rec['con_iva'] ) : 0.0;
				if ( $se > 0 ) $opcion2[] = sprintf( $t['i_op2_mismo_sabado'], dip_avisos_euros( $se, $idioma ), dip_avisos_euros( $devolver - $se, $idioma ) );
			}
			$mensaje = trim( (string) ( $extra['mensaje'] ?? $pedido->get_meta( '_dip_incidencia_mensaje' ) ) );
			$bloques = array( array( 'p', dip_avisos_con_saludo( $pedido, '' !== $fecha ? sprintf( $t['i_intro'], $larga ) : $t['i_intro_sin'], $idioma, $usted ), 'grande' ) );
			if ( '' !== $mensaje ) $bloques[] = array( 'caja', $t['i_mensaje'], preg_split( '/\R+/u', $mensaje ) );
			$bloques[] = array( 'opciones', array(
				array( 'titulo' => $t['i_op1_titulo'], 'textos' => array( sprintf( $t['i_op1'], dip_avisos_euros( $pagado, $idioma ) ) ) ),
				array( 'titulo' => $t['i_op2_titulo'], 'textos' => $opcion2 ),
			) );
			$bloques[] = array( 'p', sprintf( $t['i_responder'], $wa, $tel ), 'fuerte' );
			$bloques[] = array( 'ficha', array(
				array( $t['f_prevista'], $ficha ),
				array( $t['f_pagado'], sprintf( $t['v_iva'], dip_avisos_euros( $pagado, $idioma ) ) ),
			) );
			$bloques[] = $boton;
			$bloques[] = array( 'p', $t['i_derechos'], 'pequeno' );
			$p        += array(
				'asunto'     => sprintf( $t['i_asunto'], $n ),
				'antetitulo' => sprintf( $t['i_antetitulo'], $n ),
				'titulo'     => $t['i_titulo'],
				'previo'     => $t['i_previo'],
				'bloques'    => $bloques,
			);
			break;
	}
	return $p;
}

/** Último motivo por el que dip_aviso_enviar() no envió (para quien la llama y para las notas). */
function dip_aviso_ultimo_motivo( $motivo = null ) {
	static $ultimo = '';
	if ( null !== $motivo ) $ultimo = (string) $motivo;
	return $ultimo;
}

/** '' si el aviso se puede enviar; si no, el motivo (en castellano, para las notas del pedido). */
function dip_avisos_motivo_no( WC_Order $pedido, $tipo, array $extra = array() ) {
	$tipos = dip_avisos_tipos();
	if ( ! isset( $tipos[ $tipo ] ) ) return 'tipo de aviso desconocido («' . $tipo . '»)';
	$forzar = ! empty( $extra['forzar'] );
	if ( ! $forzar && ! dip_avisos_activo( $tipo ) ) return 'el aviso «' . $tipos[ $tipo ] . '» está desactivado en Dryicepack → Ajustes';
	$estado = $pedido->get_status();
	if ( in_array( $estado, array( 'cancelled', 'refunded', 'failed', 'pending', 'checkout-draft', 'trash' ), true ) ) {
		return 'el pedido está «' . wc_get_order_status_name( $estado ) . '»';
	}
	if ( ! is_email( (string) $pedido->get_billing_email() ) ) return 'el pedido no tiene un email válido';
	$recoge = dip_avisos_es_recogida( $pedido );
	if ( 'recogida' === $tipo && ! $recoge ) return 'el pedido va por mensajería, no se recoge en Mataró';
	if ( 'recogida' !== $tipo && $recoge ) return 'el pedido se recoge en Mataró, no va por mensajería';
	$ya = (string) $pedido->get_meta( '_dip_aviso_' . $tipo );
	if ( ! $forzar && 'incidencia' !== $tipo && '' !== $ya ) return 'ya se envió el ' . dip_avisos_marca_corta( $ya );
	if ( $forzar ) return '';
	$hoy   = dip_avisos_ahora( $extra['ahora'] ?? null )->format( 'Y-m-d' );
	$fecha = dip_avisos_fecha( $pedido );
	if ( 'hoy' === $tipo ) {
		if ( $fecha !== $hoy ) return 'la entrega no es hoy (' . ( $fecha ?: 'sin día' ) . ')';
		if ( '' === trim( (string) $pedido->get_meta( '_dip_enviado' ) ) ) return 'el pedido no está marcado como enviado';
		if ( '' !== trim( (string) $pedido->get_meta( '_dip_entregado' ) ) ) return 'el pedido ya está entregado';
	}
	if ( 'recogida' === $tipo ) {
		if ( $fecha !== $hoy ) return 'la recogida no es hoy (' . ( $fecha ?: 'sin día' ) . ')';
		if ( 'completed' === $estado ) return 'el pedido ya está completado (recogido)';
	}
	return '';
}

/**
 * Envía un aviso al cliente. Devuelve true si ha salido.
 * $tipo: 'camino' | 'hoy' | 'recogida' | 'incidencia'.
 * $extra: 'forzar' (lo envía aunque ya se enviara o esté desactivado), 'manual' (desde Acciones del pedido),
 *         'mrw' (número de envío), 'mensaje' (incidencia), 'ahora' (DateTimeInterface, para las pruebas).
 * Guarda _dip_aviso_{tipo} con la fecha (Madrid, 'AAAA-MM-DD HH:MM:SS') y deja una nota en el pedido.
 * Si no sale: nota en el pedido y aviso al correo de avisos (uno por pasada o por petición).
 * El motivo de un «no» está en dip_aviso_ultimo_motivo().
 */
function dip_aviso_enviar( WC_Order $pedido, string $tipo, array $extra = array() ): bool {
	$tipos  = dip_avisos_tipos();
	$motivo = dip_avisos_motivo_no( $pedido, $tipo, $extra );
	dip_aviso_ultimo_motivo( $motivo );
	if ( '' !== $motivo ) {
		if ( ! empty( $extra['manual'] ) ) $pedido->add_order_note( 'No se ha enviado el aviso «' . ( $tipos[ $tipo ] ?? $tipo ) . '»: ' . $motivo . '.' );
		return false;
	}
	$mensaje = 'incidencia' === $tipo ? trim( (string) ( $extra['mensaje'] ?? $pedido->get_meta( '_dip_incidencia_mensaje' ) ) ) : '';
	if ( 'incidencia' === $tipo ) $extra['mensaje'] = $mensaje;
	$p     = dip_avisos_partes( $pedido, $tipo, $extra );
	$email = (string) $pedido->get_billing_email();
	$error = '';
	$ok    = dip_avisos_mandar( $email, $p, $error );
	if ( ! $ok ) {
		$motivo = 'no ha salido el email' . ( $error ? ' (' . $error . ')' : '' );
		dip_aviso_ultimo_motivo( $motivo );
		$pedido->add_order_note( 'No se ha podido enviar el aviso «' . $tipos[ $tipo ] . '» a ' . $email . ': ' . $motivo . '. '
			. 'Para enviarlo a mano: Acciones del pedido → ' . dip_avisos_nombre_accion( $tipo ) . ' → Actualizar.' );
		dip_avisos_registrar_fallo( $pedido, $tipo, $motivo );
		return false;
	}
	$pedido->update_meta_data( '_dip_aviso_' . $tipo, dip_avisos_marca( $extra['ahora'] ?? null ) );
	if ( 'incidencia' === $tipo ) $pedido->delete_meta_data( '_dip_incidencia_mensaje' ); // el siguiente aviso no hereda el mensaje
	$pedido->save_meta_data();
	$mrw = in_array( $tipo, array( 'camino', 'hoy' ), true ) ? dip_avisos_mrw( $pedido, $extra ) : '';
	$pedido->add_order_note( sprintf(
		'Aviso «%s» enviado por email a %s (%s; %s, de %s)%s.%s',
		$tipos[ $tipo ],
		$email,
		empty( $extra['manual'] ) ? 'automático' : 'a mano, desde el pedido',
		array( 'es' => 'castellano', 'ca' => 'catalán', 'en' => 'inglés' )[ $p['idioma'] ],
		$p['usted'] ? 'usted' : 'tú',
		'' !== $mrw ? ', envío MRW ' . $mrw : '',
		'' !== $mensaje ? "\nMensaje: " . $mensaje : ''
	) );
	return true;
}

/** Nombre de la acción manual de cada aviso (para las notas y los avisos internos). */
function dip_avisos_nombre_accion( $tipo ) {
	$n = array(
		'camino'     => '«Enviar aviso: va de camino»',
		'hoy'        => '«Enviar aviso: hoy llega»',
		'recogida'   => '«Enviar aviso: recordatorio de recogida»',
		'incidencia' => '«Avisar de una incidencia en la entrega»',
	);
	return $n[ $tipo ] ?? '«' . $tipo . '»';
}

/**
 * Manda el email con la envoltura común por el correo de WooCommerce (pasa los estilos a línea), con versión en texto
 * y respuesta a info@. $error recibe el motivo si falla.
 */
function dip_avisos_mandar( $para, array $p, &$error = '' ) {
	$html  = dip_email_documento( array( 'idioma' => $p['idioma'], 'previo' => $p['previo'], 'antetitulo' => $p['antetitulo'], 'titulo' => $p['titulo'], 'bloques' => $p['bloques'] ) );
	$texto = dip_email_texto( array( 'idioma' => $p['idioma'], 'antetitulo' => $p['antetitulo'], 'titulo' => $p['titulo'], 'bloques' => $p['bloques'] ) );
	$c     = dip_contacto();
	$cab   = array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . $c['marca'] . ' <' . $c['email'] . '>' );
	$error = '';
	$capturar    = static function ( $fallo ) use ( &$error ) {
		$error = $fallo instanceof WP_Error ? $fallo->get_error_message() : 'error desconocido';
	};
	$alternativo = static function ( $phpmailer ) use ( $texto ) {
		$phpmailer->AltBody = $texto; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	};
	add_action( 'wp_mail_failed', $capturar );
	add_action( 'phpmailer_init', $alternativo, 30 );
	try {
		$ok = (bool) WC()->mailer()->send( $para, $p['asunto'], $html, $cab );
	} catch ( Throwable $e ) {
		$ok    = false;
		$error = $e->getMessage();
	}
	remove_action( 'phpmailer_init', $alternativo, 30 );
	remove_action( 'wp_mail_failed', $capturar );
	return $ok;
}

/* =========================================================
 * Fallos: un solo aviso al correo de avisos por pasada (o por petición)
 * ========================================================= */

function dip_avisos_registrar_fallo( WC_Order $pedido, $tipo, $motivo ) {
	$GLOBALS['dip_avisos_fallos'][] = array( 'id' => $pedido->get_id(), 'tipo' => $tipo, 'motivo' => $motivo );
	if ( empty( $GLOBALS['dip_avisos_en_pasada'] ) && ! has_action( 'shutdown', 'dip_avisos_avisar_fallos' ) ) {
		add_action( 'shutdown', 'dip_avisos_avisar_fallos' ); // fuera de la pasada (p. ej. «Envíos de hoy»): uno al final de la petición
	}
}

/** Manda el aviso de los fallos acumulados (si los hay) y los borra. */
function dip_avisos_avisar_fallos() {
	$fallos = (array) ( $GLOBALS['dip_avisos_fallos'] ?? array() );
	$GLOBALS['dip_avisos_fallos'] = array();
	if ( ! $fallos ) return false;
	$tipos  = dip_avisos_tipos();
	$lineas = array();
	foreach ( $fallos as $f ) {
		$p = wc_get_order( $f['id'] );
		if ( $p ) $lineas[] = sprintf( "- Pedido %s · %s · «%s» · %s\n  %s", $p->get_order_number(), $p->get_billing_email(), $tipos[ $f['tipo'] ] ?? $f['tipo'], $f['motivo'], $p->get_edit_order_url() );
	}
	$n = count( $lineas );
	if ( ! $n ) return false;
	$asunto = 1 === $n ? 'Dryicepack: no ha salido 1 aviso al cliente' : sprintf( 'Dryicepack: no han salido %d avisos a clientes', $n );
	$texto  = "No se han podido enviar estos avisos de pedido al cliente:\n\n" . implode( "\n", $lineas )
		. "\n\nQué hacer:\n"
		. "1. Comprueba que la web envía correo (WP Mail SMTP → Herramientas → prueba de email).\n"
		. "2. Envíalo a mano: abre el pedido → Acciones del pedido → «Enviar aviso: …» (o «Avisar de una incidencia en la entrega») → Actualizar.\n"
		. "3. Si es urgente (hoy llega o hoy recoge), llama al cliente o escríbele por WhatsApp.\n";
	return wp_mail( (string) dip_ajuste( 'email_avisos' ), $asunto, $texto );
}

/* =========================================================
 * Pasada diaria a las 8:00 (WP-Cron): «Hoy llega» y recordatorio de recogida
 * ========================================================= */

/** Próxima pasada: hoy a las 8:00 de Madrid si aún no ha pasado; si no, mañana. */
function dip_avisos_proxima( $ahora = null ) {
	$ahora = dip_avisos_ahora( $ahora );
	list( $h, $m ) = array_map( 'intval', explode( ':', DIP_AVISOS_HORA ) );
	$hora = $ahora->setTime( $h, $m );
	return ( $hora <= $ahora ? $hora->modify( '+1 day' ) : $hora )->getTimestamp();
}

/** Un evento único cada vez (se reprograma al correr): así va siempre a las 8:00 de Madrid, también con el cambio de hora. */
function dip_avisos_programar() {
	if ( wp_next_scheduled( DIP_AVISOS_EVENTO ) ) return;
	wp_schedule_single_event( dip_avisos_proxima(), DIP_AVISOS_EVENTO );
	$estado = dip_avisos_estado( true );
	if ( empty( $estado['desde'] ) ) dip_avisos_guardar_estado( array( 'desde' => time() ) );
}
add_action( 'init', 'dip_avisos_programar' );

add_action( DIP_AVISOS_EVENTO, static function () {
	wp_schedule_single_event( dip_avisos_proxima(), DIP_AVISOS_EVENTO ); // la de mañana, antes de nada
	dip_avisos_pasada();
} );

function dip_avisos_estado( $fresco = false ) {
	if ( $fresco && function_exists( 'wp_cache_delete' ) ) wp_cache_delete( 'dip_avisos_estado', 'options' );
	return (array) get_option( 'dip_avisos_estado', array() );
}

function dip_avisos_guardar_estado( array $cambios ) {
	$e = dip_avisos_estado( true );
	if ( empty( $e['desde'] ) ) $e['desde'] = time();
	update_option( 'dip_avisos_estado', array_merge( $e, $cambios ), false );
}

/** Minutos desde medianoche de una hora 'HH:MM'. */
function dip_avisos_minutos( $hhmm ) {
	list( $h, $m ) = array_map( 'intval', explode( ':', (string) $hhmm ) );
	return $h * 60 + $m;
}

/**
 * Pedidos de hoy para un aviso: con envío ('hoy', marcados como enviados) o de recogida ('recogida'), sin el aviso.
 *
 * @return WC_Order[]
 */
function dip_avisos_pedidos_de_hoy( $tipo, $hoy ) {
	$meta = array(
		'relation' => 'AND',
		array( 'key' => '_dip_fecha_entrega', 'value' => $hoy ),
		array( 'key' => '_dip_metodo_entrega', 'value' => 'hoy' === $tipo ? 'envio' : 'recogida' ),
		array( 'key' => '_dip_aviso_' . $tipo, 'compare' => 'NOT EXISTS' ),
	);
	if ( 'hoy' === $tipo ) $meta[] = array( 'key' => '_dip_enviado', 'compare' => 'EXISTS' );
	return wc_get_orders( array(
		'type'       => 'shop_order',
		'status'     => 'hoy' === $tipo ? array( 'wc-processing', 'wc-on-hold', 'wc-completed' ) : array( 'wc-processing', 'wc-on-hold' ),
		'limit'      => 500,
		'orderby'    => 'date',
		'order'      => 'ASC',
		'meta_query' => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	) );
}

/**
 * La pasada: «Hoy llega» (antes de la hora límite de entrega) y el recordatorio de recogida (antes de las 13:00).
 * Devuelve [ 'hoy' => [ids], 'recogida' => [ids], 'omitido' => [id => motivo], 'fallo' => [id => motivo], 'nota' => [] ].
 */
function dip_avisos_pasada( $ahora = null ) {
	$ahora = dip_avisos_ahora( $ahora );
	$r     = array( 'hoy' => array(), 'recogida' => array(), 'omitido' => array(), 'fallo' => array(), 'nota' => array() );
	// Una pasada a la vez (WP-Cron puede lanzar la tarea dos veces seguidas): nadie recibe el email dos veces
	if ( ! add_option( 'dip_avisos_en_marcha', time(), '', false ) ) {
		if ( time() - (int) get_option( 'dip_avisos_en_marcha' ) < 15 * MINUTE_IN_SECONDS ) {
			$r['nota'][] = 'Ya había una pasada en marcha.';
			return $r;
		}
		update_option( 'dip_avisos_en_marcha', time(), false );
	}
	$GLOBALS['dip_avisos_en_pasada'] = true;
	$error = '';
	try {
		$hoy     = $ahora->format( 'Y-m-d' );
		$minutos = (int) $ahora->format( 'G' ) * 60 + (int) $ahora->format( 'i' );
		foreach ( array( 'hoy', 'recogida' ) as $tipo ) {
			if ( ! dip_avisos_activo( $tipo ) ) {
				$r['nota'][] = 'Aviso «' . dip_avisos_tipos()[ $tipo ] . '» desactivado.';
				continue;
			}
			$limite = 'hoy' === $tipo ? dip_avisos_hora_limite( $hoy ) : DIP_AVISOS_LIMITE_RECOGIDA;
			if ( $minutos >= dip_avisos_minutos( $limite ) ) {
				$r['nota'][] = 'La pasada ha corrido a las ' . $ahora->format( 'G:i' ) . ', después de las ' . $limite . ': no se manda «' . dip_avisos_tipos()[ $tipo ] . '».';
				continue;
			}
			foreach ( dip_avisos_pedidos_de_hoy( $tipo, $hoy ) as $pedido ) {
				if ( dip_aviso_enviar( $pedido, $tipo, array( 'ahora' => $ahora ) ) ) {
					$r[ $tipo ][] = $pedido->get_id();
				} else {
					$m = dip_aviso_ultimo_motivo();
					$r[ 0 === strpos( $m, 'no ha salido' ) ? 'fallo' : 'omitido' ][ $pedido->get_id() ] = $m;
				}
			}
		}
	} catch ( Throwable $e ) {
		$error = 'La pasada de avisos ha fallado: ' . $e->getMessage();
	} finally {
		$GLOBALS['dip_avisos_en_pasada'] = false;
		delete_option( 'dip_avisos_en_marcha' );
	}
	dip_avisos_avisar_fallos();
	dip_avisos_guardar_estado( array(
		'ok'       => $error ? (int) ( dip_avisos_estado( true )['ok'] ?? 0 ) : time(),
		'error'    => $error,
		'hoy'      => count( $r['hoy'] ),
		'recogida' => count( $r['recogida'] ),
		'fallos'   => count( $r['fallo'] ),
	) );
	dip_avisos_vigilar();
	return $r;
}

/* =========================================================
 * Si la pasada no corre: email (uno al día) y aviso en el escritorio
 * ========================================================= */

/** Problema de la pasada ('' si va bien). */
function dip_avisos_problema() {
	$e      = dip_avisos_estado();
	$ok     = (int) ( $e['ok'] ?? 0 );
	$desde  = (int) ( $e['desde'] ?? 0 );
	$limite = DIP_AVISOS_CADUCA * HOUR_IN_SECONDS;
	if ( ! empty( $e['error'] ) ) return (string) $e['error'];
	if ( $ok && time() - $ok > $limite ) {
		return sprintf( 'La pasada de las 8:00 de los avisos al cliente no corre desde hace %d horas: puede que WP-Cron no se esté ejecutando.', (int) floor( ( time() - $ok ) / HOUR_IN_SECONDS ) );
	}
	if ( ! $ok && $desde && time() - $desde > $limite ) return 'La pasada de las 8:00 de los avisos al cliente no ha corrido nunca: puede que WP-Cron no se esté ejecutando.';
	return '';
}

function dip_avisos_vigilar() {
	$estado   = dip_avisos_estado( true );
	$problema = dip_avisos_problema();
	$avisado  = (int) ( $estado['avisado'] ?? 0 );
	$correo   = (string) dip_ajuste( 'email_avisos' );
	$ajustes  = admin_url( 'admin.php?page=dryicepack#dip-avisos' );
	if ( $problema && time() - $avisado > DAY_IN_SECONDS ) {
		$ok = wp_mail(
			$correo,
			'Dryicepack: los avisos de «Hoy llega» y de recogida no están saliendo',
			"La web no ha mandado hoy los avisos automáticos de las 8:00 («Hoy llega tu hielo seco» y el recordatorio de recogida).\n\nMotivo: {$problema}\n\n"
			. "Mientras tanto, a mano:\n"
			. "1. En WooCommerce → Pedidos, la columna «Entrega» dice el día de cada pedido. Abre los de hoy.\n"
			. "2. Con envío y ya enviados: Acciones del pedido → «Enviar aviso: hoy llega» → Actualizar.\n"
			. "3. De recogida: Acciones del pedido → «Enviar aviso: recordatorio de recogida» → Actualizar.\n"
			. "4. Si sigue igual mañana, avisa a quien mantiene la web (WP-Cron o la tarea programada del hosting).\n\n"
			. "Estado: {$ajustes}\n\nEste aviso se repite una vez al día mientras dure el problema."
		);
		if ( $ok ) dip_avisos_guardar_estado( array( 'avisado' => time() ) );
	} elseif ( ! $problema && $avisado ) {
		if ( wp_mail( $correo, 'Dryicepack: los avisos de las 8:00 vuelven a salir', "La pasada de los avisos al cliente vuelve a funcionar. No hay que hacer nada.\n\n{$ajustes}" ) ) {
			dip_avisos_guardar_estado( array( 'avisado' => 0 ) );
		}
	}
}

// Si WP-Cron se para, el aviso sale igualmente al entrar al escritorio (como mucho, una comprobación por hora)
add_action( 'admin_init', static function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_woocommerce' ) || get_transient( 'dip_avisos_vigilado' ) ) return;
	set_transient( 'dip_avisos_vigilado', 1, HOUR_IN_SECONDS );
	dip_avisos_vigilar();
} );

/** Aviso en el escritorio si la pasada de las 8:00 no corre o falla. */
function dip_avisos_aviso_escritorio() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$problema = dip_avisos_problema();
	if ( ! $problema ) return;
	printf(
		'<div class="notice notice-error"><p><strong>Avisos al cliente.</strong> %s Mientras tanto, envía a mano «Hoy llega» y los recordatorios de recogida desde Acciones del pedido. <a href="%s">Ver el estado</a></p></div>',
		esc_html( $problema ),
		esc_url( admin_url( 'admin.php?page=dryicepack#dip-avisos' ) )
	);
}
add_action( 'admin_notices', 'dip_avisos_aviso_escritorio' );

/** Resumen para Dryicepack → Ajustes. */
function dip_avisos_resumen_admin() {
	$e         = dip_avisos_estado();
	$ok        = (int) ( $e['ok'] ?? 0 );
	$siguiente = wp_next_scheduled( DIP_AVISOS_EVENTO );
	$partes    = array();
	$partes[]  = $ok ? sprintf( 'Última pasada de las 8:00: %s · «Hoy llega»: %d · recogidas: %d · fallos: %d.', wp_date( 'j/n/Y H:i', $ok ), (int) ( $e['hoy'] ?? 0 ), (int) ( $e['recogida'] ?? 0 ), (int) ( $e['fallos'] ?? 0 ) ) : 'La pasada de las 8:00 todavía no ha corrido.';
	if ( $siguiente ) $partes[] = 'Próxima: ' . wp_date( 'j/n/Y H:i', (int) $siguiente ) . '.';
	$problema = dip_avisos_problema();
	if ( $problema ) $partes[] = $problema;
	return implode( ' ', $partes );
}

/* =========================================================
 * Escritorio: acciones del pedido, mensaje de incidencia y avisos ya enviados
 * ========================================================= */

add_filter( 'woocommerce_order_actions', static function ( $acciones, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : ( isset( $GLOBALS['theorder'] ) && $GLOBALS['theorder'] instanceof WC_Order ? $GLOBALS['theorder'] : null );
	if ( ! $pedido ) return $acciones;
	if ( dip_avisos_es_recogida( $pedido ) ) {
		$acciones['dip_aviso_recogida'] = 'Enviar aviso: recordatorio de recogida';
	} else {
		$acciones['dip_aviso_camino'] = 'Enviar aviso: va de camino';
		$acciones['dip_aviso_hoy']    = 'Enviar aviso: hoy llega';
		if ( dip_avisos_activo( 'incidencia' ) ) $acciones['dip_aviso_incidencia'] = 'Avisar de una incidencia en la entrega';
	}
	return $acciones;
}, 30, 2 );

foreach ( array_keys( dip_avisos_tipos() ) as $dip_tipo_aviso ) {
	add_action( 'woocommerce_order_action_dip_aviso_' . $dip_tipo_aviso, static function ( $pedido ) use ( $dip_tipo_aviso ) {
		if ( ! $pedido instanceof WC_Order ) return;
		$extra = array( 'forzar' => true, 'manual' => true );
		if ( 'incidencia' === $dip_tipo_aviso ) $extra['mensaje'] = (string) $pedido->get_meta( '_dip_incidencia_mensaje' );
		dip_aviso_enviar( $pedido, $dip_tipo_aviso, $extra );
	} );
}
unset( $dip_tipo_aviso );

/* Mensaje opcional para el aviso de incidencia y avisos ya enviados, debajo de la dirección de envío. */
add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$enviados = array();
	foreach ( dip_avisos_tipos() as $tipo => $nombre ) {
		$f = (string) $pedido->get_meta( '_dip_aviso_' . $tipo );
		if ( '' !== $f ) $enviados[] = $nombre . ' ' . dip_avisos_marca_corta( $f );
	}
	echo '<p class="form-field form-field-wide"><strong>' . esc_html( 'Avisos al cliente:' ) . '</strong> ' . esc_html( $enviados ? implode( ' · ', $enviados ) : 'ninguno todavía' ) . '</p>';
	if ( dip_avisos_es_recogida( $pedido ) || ! dip_avisos_activo( 'incidencia' ) ) return;
	printf(
		'<p class="form-field form-field-wide"><label for="dip_incidencia_mensaje">%s</label><textarea id="dip_incidencia_mensaje" name="dip_incidencia_mensaje" rows="3" placeholder="%s">%s</textarea><span class="description">%s</span></p>',
		esc_html( 'Mensaje para el aviso de incidencia (opcional)' ),
		esc_attr( 'p. ej. MRW no tiene reparto en sábado en tu código postal.' ),
		esc_textarea( (string) $pedido->get_meta( '_dip_incidencia_mensaje' ) ),
		esc_html( 'Va dentro del email si eliges «Avisar de una incidencia en la entrega» en Acciones del pedido. Escríbelo en el idioma del cliente y sin culpar a nadie.' )
	);
}, 30 );

/* Guardar el mensaje antes de que WooCommerce ejecute la acción del pedido (prioridad 50). */
add_action( 'woocommerce_process_shop_order_meta', static function ( $pedido_id ) {
	if ( ! isset( $_POST['dip_incidencia_mensaje'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verificó el nonce del pedido
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido ) return;
	$mensaje = trim( sanitize_textarea_field( wp_unslash( $_POST['dip_incidencia_mensaje'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( '' === $mensaje ) {
		$pedido->delete_meta_data( '_dip_incidencia_mensaje' );
	} else {
		$pedido->update_meta_data( '_dip_incidencia_mensaje', $mensaje );
	}
	$pedido->save_meta_data();
}, 10 );
