<?php
/**
 * Reseñas en Google (09/11/2026): email «¿Qué tal llegó tu hielo seco?» el día después de la entrega o de la recogida.
 *
 * - Tarea diaria de las 9:00 (dip_tarea_diaria): pedidos Procesando o Completados cuya fecha de entrega o de recogida
 *   (_dip_fecha_entrega, AAAA-MM-DD) fue ayer o uno de los 3 días anteriores (por si algún día WP-Cron no corrió).
 * - Se pide a TODOS los que cumplen eso: nunca se filtra por satisfacción (Google lo prohíbe) y no se da nada a cambio.
 * - Un email por pedido (_dip_resena_pedida guarda la fecha del envío) y como mucho uno por dirección de email cada
 *   180 días: un cliente que compra cada semana recibe uno cada 6 meses.
 * - Idioma del pedido (_dip_idioma). De usted a empresas (_dip_empresa o cuenta de empresa); de tú al resto.
 * - Baja desde cada email (enlace firmado y cabecera List-Unsubscribe): se guarda una huella del email (wp_hash), nunca
 *   el email, en la opción dip_resenas_bajas. La baja también quita el bloque de reseña del email de «completado».
 * - Si el email no sale: nota en el pedido y un solo aviso por pasada al correo de avisos. Se reintenta solo los días
 *   siguientes mientras siga dentro de los 4 días. Alternativa manual: Acciones del pedido → «Pedir reseña por email».
 * - Si el enlace de reseñas de Dryicepack → Ajustes está vacío, no se envía nada. Interruptor en el mismo sitio.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_RESENAS_DIAS' ) )      define( 'DIP_RESENAS_DIAS', 4 );        // ayer y los 3 días anteriores
if ( ! defined( 'DIP_RESENAS_INTERVALO' ) ) define( 'DIP_RESENAS_INTERVALO', 180 ); // días mínimos entre dos peticiones al mismo email

/* =========================================================
 * Fechas
 * ========================================================= */

function dip_resenas_hoy() {
	return new DateTimeImmutable( 'today', dip_zona_horaria() );
}

/** Fecha y hora de Madrid en formato 'AAAA-MM-DD HH:MM:SS' (el de _dip_resena_pedida), opcionalmente desplazada. */
function dip_resenas_ahora( $desplazar = '' ) {
	$d = new DateTimeImmutable( 'now', dip_zona_horaria() );
	return ( $desplazar ? $d->modify( $desplazar ) : $d )->format( 'Y-m-d H:i:s' );
}

/** Días de entrega que tocan hoy: ayer y los 3 anteriores. */
function dip_resenas_fechas_ventana() {
	$hoy    = dip_resenas_hoy();
	$fechas = array();
	for ( $i = 1; $i <= DIP_RESENAS_DIAS; $i++ ) $fechas[] = $hoy->modify( "-{$i} days" )->format( 'Y-m-d' );
	return $fechas;
}

/** '2026-11-11 09:00:12' → '11/11/2026' para las notas. */
function dip_resenas_fecha_corta( $mysql ) {
	$d = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $mysql, dip_zona_horaria() );
	return $d ? $d->format( 'j/n/Y' ) : (string) $mysql;
}

/* =========================================================
 * Bajas (sin guardar el email)
 * ========================================================= */

/**
 * Huella del email: no se puede volver atrás. El esquema propio de wp_hash ('dip_resenas') usa la clave secreta
 * guardada en la base de datos (o SECRET_KEY si existe) y no las claves AUTH_* de wp-config: si algún día se
 * regeneran esas claves, las bajas no se pierden.
 */
function dip_resenas_huella( $email ) {
	return wp_hash( 'resena|' . strtolower( trim( (string) $email ) ), 'dip_resenas' );
}

/** Firma del enlace de baja: solo la web puede crear enlaces válidos (nadie puede llenar la lista de basura). */
function dip_resenas_firma( $huella ) {
	return substr( wp_hash( 'resena-baja|' . $huella, 'dip_resenas' ), 0, 16 );
}

/** [ huella => 'AAAA-MM-DD' de la baja ] */
function dip_resenas_bajas() {
	$bajas = get_option( 'dip_resenas_bajas', array() );
	return is_array( $bajas ) ? $bajas : array();
}

function dip_resenas_de_baja( $email ) {
	return '' !== trim( (string) $email ) && isset( dip_resenas_bajas()[ dip_resenas_huella( $email ) ] );
}

function dip_resenas_dar_de_baja( $huella ) {
	$bajas = dip_resenas_bajas();
	if ( isset( $bajas[ $huella ] ) ) return;
	$bajas[ $huella ] = dip_resenas_hoy()->format( 'Y-m-d' );
	update_option( 'dip_resenas_bajas', $bajas, false );
}

function dip_resenas_url_baja_de_huella( $huella, $idioma = 'es', $usted = false ) {
	$args = array( 'dip_resenas_baja' => $huella, 'firma' => dip_resenas_firma( $huella ) );
	if ( in_array( $idioma, array( 'ca', 'en' ), true ) ) $args['idioma'] = $idioma;
	if ( $usted ) $args['usted'] = 1;
	return add_query_arg( $args, home_url( '/' ) );
}

function dip_resenas_url_baja( $email, $idioma = 'es', $usted = false ) {
	return dip_resenas_url_baja_de_huella( dip_resenas_huella( $email ), $idioma, $usted );
}

/* =========================================================
 * Textos (castellano, catalán e inglés; tú y usted)
 * ========================================================= */

function dip_resenas_textos( $idioma = 'es', $usted = false ) {
	$tx = array(
		'es'       => array(
			'asunto'           => '¿Qué tal llegó tu hielo seco?',
			'hola'             => 'Hola, %s: ',
			'hola_sin'         => 'Hola. ',
			'entrega'          => 'el %1$s te entregamos %2$s.',
			'entrega_sin_dia'  => 'hace unos días te entregamos %s.',
			'recogida'         => 'el %1$s recogiste %2$s.',
			'recogida_sin_dia' => 'hace unos días recogiste %s.',
			'kg'               => '%s de hielo seco',
			'kg_sin'           => 'tu pedido de hielo seco',
			'pedir'            => '¿Nos cuentas qué tal en Google? Son 30 segundos y ayuda a otros a encontrarnos.',
			'boton'            => 'Escribir reseña',
			'arreglo'          => 'Y si algo no fue como esperabas, responde a este email y lo arreglamos.',
			'pie'              => '¿No quieres que te pidamos opinión?',
			'baja'             => 'Darte de baja',
		),
		'es_usted' => array(
			'asunto'           => '¿Qué tal llegó su hielo seco?',
			'hola'             => 'Hola, %s: ',
			'hola_sin'         => 'Hola. ',
			'entrega'          => 'el %1$s le entregamos %2$s.',
			'entrega_sin_dia'  => 'hace unos días le entregamos %s.',
			'recogida'         => 'el %1$s recogió %2$s.',
			'recogida_sin_dia' => 'hace unos días recogió %s.',
			'kg'               => '%s de hielo seco',
			'kg_sin'           => 'su pedido de hielo seco',
			'pedir'            => '¿Nos cuenta qué tal en Google? Son 30 segundos y ayuda a otros a encontrarnos.',
			'boton'            => 'Escribir reseña',
			'arreglo'          => 'Y si algo no fue como esperaba, responda a este email y lo arreglamos.',
			'pie'              => '¿No quiere que le pidamos opinión?',
			'baja'             => 'Darse de baja',
		),
		'ca'       => array(
			'asunto'           => 'Com et va arribar el gel sec?',
			'hola'             => 'Hola, %s: ',
			'hola_sin'         => 'Hola. ',
			'entrega'          => 'el %1$s et vam entregar %2$s.',
			'entrega_sin_dia'  => 'fa uns dies et vam entregar %s.',
			'recogida'         => 'el %1$s vas recollir %2$s.',
			'recogida_sin_dia' => 'fa uns dies vas recollir %s.',
			'kg'               => '%s de gel sec',
			'kg_sin'           => 'la teva comanda de gel sec',
			'pedir'            => 'Ens expliques a Google com et va anar? Són 30 segons i ajuda altres persones a trobar-nos.',
			'boton'            => 'Escriure una ressenya',
			'arreglo'          => 'I si alguna cosa no va anar com esperaves, respon aquest correu i ho arreglem.',
			'pie'              => 'No vols que et demanem l\'opinió?',
			'baja'             => 'Donar-te de baixa',
		),
		'ca_usted' => array(
			'asunto'           => 'Com li va arribar el gel sec?',
			'hola'             => 'Hola, %s: ',
			'hola_sin'         => 'Hola. ',
			'entrega'          => 'el %1$s li vam entregar %2$s.',
			'entrega_sin_dia'  => 'fa uns dies li vam entregar %s.',
			'recogida'         => 'el %1$s va recollir %2$s.',
			'recogida_sin_dia' => 'fa uns dies va recollir %s.',
			'kg'               => '%s de gel sec',
			'kg_sin'           => 'la seva comanda de gel sec',
			'pedir'            => 'Ens explica a Google com li va anar? Són 30 segons i ajuda altres persones a trobar-nos.',
			'boton'            => 'Escriure una ressenya',
			'arreglo'          => 'I si alguna cosa no va anar com esperava, respongui aquest correu i ho arreglem.',
			'pie'              => 'No vol que li demanem l\'opinió?',
			'baja'             => 'Donar-se de baixa',
		),
		'en'       => array(
			'asunto'           => 'How did your dry ice arrive?',
			'hola'             => 'Hi %s, ',
			'hola_sin'         => '',
			'entrega'          => 'on %1$s we delivered %2$s to you.',
			'entrega_sin_dia'  => 'a few days ago we delivered %s to you.',
			'recogida'         => 'on %1$s you collected %2$s.',
			'recogida_sin_dia' => 'a few days ago you collected %s.',
			'kg'               => '%s of dry ice',
			'kg_sin'           => 'your dry ice order',
			'pedir'            => 'Would you tell us how it went on Google? It takes 30 seconds and helps others find us.',
			'boton'            => 'Write a review',
			'arreglo'          => 'And if anything was not as you expected, reply to this email and we will put it right.',
			'pie'              => 'Would you rather we did not ask for your feedback?',
			'baja'             => 'Unsubscribe',
		),
	);
	$tx['en_usted']         = $tx['en'];
	$tx['en_usted']['hola'] = 'Hello %s, ';
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	return $tx[ $idioma . ( $usted ? '_usted' : '' ) ];
}

/** Textos de la página de baja. */
function dip_resenas_textos_baja( $idioma = 'es', $usted = false ) {
	$tx = array(
		'es'       => array(
			'confirmar_titulo' => '¿Dejamos de pedirte opinión?',
			'confirmar_texto'  => 'Pulsa el botón y no volveremos a pedirte opinión por email. Los emails de tus pedidos (confirmación, entrega y factura) te seguirán llegando.',
			'boton'            => 'Darme de baja',
			'hecho_titulo'     => 'Hecho: ya no te pediremos opinión',
			'hecho_texto'      => 'No volveremos a escribirte para pedirte opinión sobre tus pedidos. Los emails de tus pedidos te seguirán llegando.',
			'no_valido_titulo' => 'Este enlace no es válido',
			'no_valido_texto'  => 'Puede que esté incompleto. Si no quieres que te pidamos opinión, escríbenos a info@dryicepack.es y te damos de baja.',
			'volver'           => 'Ir a dryicepack.es',
		),
		'es_usted' => array(
			'confirmar_titulo' => '¿Dejamos de pedirle opinión?',
			'confirmar_texto'  => 'Pulse el botón y no volveremos a pedirle opinión por email. Los emails de sus pedidos (confirmación, entrega y factura) le seguirán llegando.',
			'boton'            => 'Darme de baja',
			'hecho_titulo'     => 'Hecho: ya no le pediremos opinión',
			'hecho_texto'      => 'No volveremos a escribirle para pedirle opinión sobre sus pedidos. Los emails de sus pedidos le seguirán llegando.',
			'no_valido_titulo' => 'Este enlace no es válido',
			'no_valido_texto'  => 'Puede que esté incompleto. Si no quiere que le pidamos opinión, escríbanos a info@dryicepack.es y tramitamos la baja.',
			'volver'           => 'Ir a dryicepack.es',
		),
		'ca'       => array(
			'confirmar_titulo' => 'Deixem de demanar-te l\'opinió?',
			'confirmar_texto'  => 'Prem el botó i no et tornarem a demanar l\'opinió per correu. Els correus de les teves comandes (confirmació, entrega i factura) et continuaran arribant.',
			'boton'            => 'Donar-me de baixa',
			'hecho_titulo'     => 'Fet: ja no et demanarem l\'opinió',
			'hecho_texto'      => 'No et tornarem a escriure per demanar-te l\'opinió sobre les teves comandes. Els correus de les comandes et continuaran arribant.',
			'no_valido_titulo' => 'Aquest enllaç no és vàlid',
			'no_valido_texto'  => 'Potser està incomplet. Si no vols que et demanem l\'opinió, escriu-nos a info@dryicepack.es i et donem de baixa.',
			'volver'           => 'Anar a dryicepack.es',
		),
		'ca_usted' => array(
			'confirmar_titulo' => 'Deixem de demanar-li l\'opinió?',
			'confirmar_texto'  => 'Premi el botó i no li tornarem a demanar l\'opinió per correu. Els correus de les seves comandes (confirmació, entrega i factura) li continuaran arribant.',
			'boton'            => 'Donar-me de baixa',
			'hecho_titulo'     => 'Fet: ja no li demanarem l\'opinió',
			'hecho_texto'      => 'No li tornarem a escriure per demanar-li l\'opinió sobre les seves comandes. Els correus de les comandes li continuaran arribant.',
			'no_valido_titulo' => 'Aquest enllaç no és vàlid',
			'no_valido_texto'  => 'Potser està incomplet. Si no vol que li demanem l\'opinió, escrigui\'ns a info@dryicepack.es i tramitem la baixa.',
			'volver'           => 'Anar a dryicepack.es',
		),
		'en'       => array(
			'confirmar_titulo' => 'Stop asking for your feedback?',
			'confirmar_texto'  => 'Press the button and we will not ask for your feedback by email again. Emails about your orders (confirmation, delivery and invoice) will still reach you.',
			'boton'            => 'Unsubscribe me',
			'hecho_titulo'     => 'Done: we will not ask for your feedback again',
			'hecho_texto'      => 'We will not email you again to ask about your orders. Emails about your orders will still reach you.',
			'no_valido_titulo' => 'This link is not valid',
			'no_valido_texto'  => 'It may be incomplete. If you would rather we did not ask for your feedback, write to info@dryicepack.es and we will unsubscribe you.',
			'volver'           => 'Go to dryicepack.es',
		),
	);
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	return $tx[ $idioma . ( $usted && 'en' !== $idioma ? '_usted' : '' ) ];
}

/* =========================================================
 * Datos del pedido para el email
 * ========================================================= */

function dip_resenas_idioma( WC_Order $pedido ) {
	$idioma = (string) $pedido->get_meta( '_dip_idioma' );
	return in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
}

/** De usted: compra de empresa o autónomo (casilla del checkout) o cuenta de empresa. */
function dip_resenas_de_usted( WC_Order $pedido ) {
	if ( (int) $pedido->get_meta( '_dip_empresa' ) ) return true;
	if ( 'cuenta' === (string) $pedido->get_meta( '_dip_canal' ) ) return true;
	return function_exists( 'dip_es_cuenta_empresa' ) && $pedido->get_customer_id() && dip_es_cuenta_empresa( (int) $pedido->get_customer_id() );
}

/** Kilos de hielo del pedido: _dip_kg o, si falta, la suma de las líneas. */
function dip_resenas_kg( WC_Order $pedido ) {
	$kg = (float) str_replace( ',', '.', (string) $pedido->get_meta( '_dip_kg' ) );
	if ( $kg > 0 ) return $kg;
	foreach ( $pedido->get_items() as $linea ) {
		$kg += (float) $linea->get_quantity() * (float) dip_kg_producto( $linea->get_product() );
	}
	return $kg;
}

/** 10 → "10", 2.5 → "2,5" (en: "2.5"). */
function dip_resenas_numero( $n, $idioma = 'es' ) {
	$sep = 'en' === $idioma ? '.' : ',';
	return rtrim( rtrim( number_format( (float) $n, 2, $sep, '' ), '0' ), $sep );
}

/**
 * Nombre para el saludo. El checkout tiene un solo campo («Laura García Pons»): de tú, solo el nombre de pila;
 * de usted, el campo entero.
 */
function dip_resenas_nombre( WC_Order $pedido, $usted = false ) {
	$nombre = trim( (string) preg_replace( '/\s+/u', ' ', (string) $pedido->get_billing_first_name() ) );
	if ( '' === $nombre || $usted || '' !== trim( (string) $pedido->get_billing_last_name() ) ) return $nombre;
	$pila = (string) strtok( $nombre, ' ' );
	return ( mb_strlen( $pila ) > 2 && '.' !== substr( $pila, -1 ) ) ? $pila : $nombre;
}

/**
 * Partes del email de un pedido, con los textos ya resueltos (idioma, trato, nombre, día y kilos) y sin HTML:
 *   asunto · titulo · saludo · cuerpo[] · boton{texto,url} · cierre[] · pie{texto,enlace,url} · idioma · usted.
 * La envoltura (cómo se pintan) va aparte, en dip_resenas_html() y dip_resenas_texto_plano().
 */
function dip_resenas_partes( WC_Order $pedido ) {
	$idioma = dip_resenas_idioma( $pedido );
	$usted  = dip_resenas_de_usted( $pedido );
	$t      = dip_resenas_textos( $idioma, $usted );
	$fecha  = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	$dia    = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) ? dip_fecha_larga( $fecha, $idioma ) : '';
	$clave  = ( 'recogida' === (string) $pedido->get_meta( '_dip_metodo_entrega' ) ? 'recogida' : 'entrega' ) . ( $dia ? '' : '_sin_dia' );
	$kg     = dip_resenas_kg( $pedido );
	$cuanto = $kg > 0 ? sprintf( $t['kg'], dip_resenas_numero( $kg, $idioma ) . "\u{00A0}kg" ) : $t['kg_sin'];
	$frase  = $dia ? sprintf( $t[ $clave ], $dia, $cuanto ) : sprintf( $t[ $clave ], $cuanto );
	$nombre = dip_resenas_nombre( $pedido, $usted );
	$saludo = $nombre ? sprintf( $t['hola'], $nombre ) . $frase : $t['hola_sin'] . ( function_exists( 'mb_strtoupper' ) ? mb_strtoupper( mb_substr( $frase, 0, 1 ) ) . mb_substr( $frase, 1 ) : ucfirst( $frase ) );
	return array(
		'asunto' => $t['asunto'],
		'titulo' => $t['asunto'],
		'saludo' => $saludo,
		'cuerpo' => array( $t['pedir'] ),
		'boton'  => array( 'texto' => $t['boton'], 'url' => (string) dip_ajuste( 'resenas_url' ) ),
		'cierre' => array( $t['arreglo'] ),
		'pie'    => array( 'texto' => $t['pie'], 'enlace' => $t['baja'], 'url' => dip_resenas_url_baja( (string) $pedido->get_billing_email(), $idioma, $usted ) ),
		'idioma' => $idioma,
		'usted'  => $usted,
	);
}

/**
 * Envoltura HTML del email a partir de sus partes: plantilla de email de WooCommerce (cabecera y pie de la tienda)
 * con el cuerpo dentro. Para cambiar el diseño, basta con cambiar esta función: los textos no se tocan.
 */
function dip_resenas_html( array $p ) {
	// Envoltura común de los emails (inc/emails.php): cabecera marino, botón a prueba de Outlook y pie de la marca
	if ( function_exists( 'dip_email_documento' ) ) {
		return dip_email_documento( array(
			'idioma'  => $p['idioma'],
			'previo'  => implode( ' ', (array) $p['cuerpo'] ),
			'titulo'  => $p['titulo'],
			'bloques' => array_merge(
				array( array( 'p', $p['saludo'], 'grande' ) ),
				array_map( static fn( $x ) => array( 'p', $x ), (array) $p['cuerpo'] ),
				array( array( 'boton', $p['boton']['texto'], $p['boton']['url'] ) ),
				array_map( static fn( $x ) => array( 'p', $x ), (array) $p['cierre'] ),
				array( array( 'p', $p['pie']['texto'], 'pequeno', array( $p['pie']['enlace'], $p['pie']['url'] ) ) )
			),
		) );
	}
	$parrafos = static fn( $lista ) => implode( '', array_map( static fn( $x ) => '<p>' . esc_html( $x ) . '</p>', (array) $lista ) );
	$cuerpo   = '<p>' . esc_html( $p['saludo'] ) . '</p>'
		. $parrafos( $p['cuerpo'] )
		. '<p style="margin:24px 0"><a href="' . esc_url( $p['boton']['url'] ) . '" style="display:inline-block;padding:14px 22px;background:#1F3C55;color:#ffffff;text-decoration:none;border-radius:999px;font-weight:600">' . esc_html( $p['boton']['texto'] ) . '</a></p>'
		. $parrafos( $p['cierre'] )
		. '<p style="margin:32px 0 0;font-size:12px;color:#56677A">' . esc_html( $p['pie']['texto'] ) . ' <a href="' . esc_url( $p['pie']['url'] ) . '" style="color:#56677A">' . esc_html( $p['pie']['enlace'] ) . '</a></p>';
	return WC()->mailer()->wrap_message( $p['titulo'], $cuerpo );
}

/** Versión en texto del email (para los programas de correo que no muestran HTML). */
function dip_resenas_texto_plano( array $p ) {
	return $p['saludo'] . "\n\n" . implode( "\n\n", (array) $p['cuerpo'] ) . "\n" . $p['boton']['texto'] . ': ' . $p['boton']['url']
		. "\n\n" . implode( "\n\n", (array) $p['cierre'] ) . "\n\n-- \n" . $p['pie']['texto'] . ' ' . $p['pie']['enlace'] . ': ' . $p['pie']['url'] . "\n";
}

/** Asunto, HTML ya envuelto, texto plano y enlace de baja del email de un pedido. */
function dip_resenas_email( WC_Order $pedido ) {
	$p = dip_resenas_partes( $pedido );
	return array(
		'asunto'   => $p['asunto'],
		'html'     => dip_resenas_html( $p ),
		'texto'    => dip_resenas_texto_plano( $p ),
		'url_baja' => $p['pie']['url'],
		'idioma'   => $p['idioma'],
		'usted'    => $p['usted'],
	);
}

/* =========================================================
 * ¿Se puede pedir? y envío
 * ========================================================= */

/** Última petición a ese email en los últimos 180 días (sin contar el pedido $excluir): [ pedido, fecha ] o null. */
function dip_resenas_ultima_peticion( $email, $excluir = 0 ) {
	if ( ! is_email( $email ) ) return null;
	$ids = wc_get_orders( array(
		'type'          => 'shop_order',
		'billing_email' => $email,
		'exclude'       => $excluir ? array( (int) $excluir ) : array(),
		'limit'         => 1,
		'return'        => 'ids',
		'meta_query'    => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_dip_resena_pedida', 'value' => dip_resenas_ahora( '-' . DIP_RESENAS_INTERVALO . ' days' ), 'compare' => '>=' ),
		),
	) );
	$otro = $ids ? wc_get_order( $ids[0] ) : null;
	return $otro ? array( 'pedido' => $otro->get_order_number(), 'fecha' => (string) $otro->get_meta( '_dip_resena_pedida' ) ) : null;
}

/**
 * '' si a este pedido se le puede pedir la reseña ahora; si no, el motivo (va a la nota del pedido en la acción manual).
 * $manual (Acciones del pedido): no mira la ventana de 4 días, pero sí todo lo demás (también la baja), y no envía
 * antes del día de entrega.
 */
function dip_resenas_motivo_no( WC_Order $pedido, $manual = false ) {
	if ( '' === trim( (string) dip_ajuste( 'resenas_url' ) ) ) return 'no hay enlace de reseñas en Dryicepack → Ajustes';
	if ( ! in_array( $pedido->get_status(), array( 'processing', 'completed' ), true ) ) {
		return 'el pedido está «' . wc_get_order_status_name( $pedido->get_status() ) . '» (solo se pide en Procesando o Completado)';
	}
	$email = (string) $pedido->get_billing_email();
	if ( ! is_email( $email ) ) return 'el pedido no tiene un email válido';
	$pedida = (string) $pedido->get_meta( '_dip_resena_pedida' );
	if ( '' !== $pedida ) return 'ya se pidió la reseña de este pedido el ' . dip_resenas_fecha_corta( $pedida );
	if ( dip_resenas_de_baja( $email ) ) return 'el cliente se ha dado de baja de las peticiones de opinión';
	$fecha = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	if ( $manual ) {
		if ( '' !== $fecha && $fecha > dip_resenas_hoy()->format( 'Y-m-d' ) ) return 'la entrega o recogida es el ' . $fecha . ' y todavía no ha llegado';
	} elseif ( ! in_array( $fecha, dip_resenas_fechas_ventana(), true ) ) {
		return 'la entrega o recogida no fue entre ayer y hace ' . DIP_RESENAS_DIAS . ' días';
	}
	$otra = dip_resenas_ultima_peticion( $email, $pedido->get_id() );
	if ( $otra ) {
		return sprintf( 'a este email ya se le pidió opinión el %s (pedido %s): como mucho una vez cada %d días', dip_resenas_fecha_corta( $otra['fecha'] ), $otra['pedido'], DIP_RESENAS_INTERVALO );
	}
	return '';
}

/**
 * Pide la reseña de un pedido. Devuelve [ 'estado' => 'enviado' | 'omitido' | 'fallo', 'motivo' => '…' ].
 * Plantilla de email de WooCommerce, remitente el de la tienda (WooCommerce → Ajustes → Emails), respuesta a info@.
 */
function dip_resenas_enviar( WC_Order $pedido, $manual = false ) {
	$motivo = dip_resenas_motivo_no( $pedido, $manual );
	if ( '' !== $motivo ) return array( 'estado' => 'omitido', 'motivo' => $motivo );

	$email     = (string) $pedido->get_billing_email();
	$e         = dip_resenas_email( $pedido );
	$c         = dip_contacto();
	$cabeceras = array(
		'Content-Type: text/html; charset=UTF-8',
		'Reply-To: ' . $c['marca'] . ' <' . $c['email'] . '>',
		// Gmail, Outlook y Apple Mail enseñan su propio botón de baja (RFC 2369 y RFC 8058)
		'List-Unsubscribe: <' . $e['url_baja'] . '>, <mailto:' . $c['email'] . '?subject=Baja%20opiniones>',
		'List-Unsubscribe-Post: List-Unsubscribe=One-Click',
	);
	$error       = '';
	$capturar    = static function ( $fallo ) use ( &$error ) {
		$error = $fallo instanceof WP_Error ? $fallo->get_error_message() : 'error desconocido';
	};
	$alternativo = static function ( $phpmailer ) use ( $e ) {
		$phpmailer->AltBody = $e['texto']; // versión en texto para los programas de correo que no muestran HTML
	};
	add_action( 'wp_mail_failed', $capturar );
	add_action( 'phpmailer_init', $alternativo );
	$ok = (bool) WC()->mailer()->send( $email, $e['asunto'], $e['html'], $cabeceras ); // send() pasa los estilos a línea
	remove_action( 'phpmailer_init', $alternativo );
	remove_action( 'wp_mail_failed', $capturar );

	if ( ! $ok ) {
		$motivo = 'no ha salido el email' . ( $error ? ' (' . $error . ')' : '' );
		$pedido->add_order_note( 'No se ha podido enviar la petición de reseña a ' . $email . ': ' . $motivo . '. '
			. ( $manual ? 'Vuelve a intentarlo más tarde.' : 'La web lo reintenta sola los días siguientes, hasta ' . DIP_RESENAS_DIAS . ' días después de la entrega. A mano: Acciones del pedido → Pedir reseña por email.' ) );
		return array( 'estado' => 'fallo', 'motivo' => $motivo );
	}
	$pedido->update_meta_data( '_dip_resena_pedida', dip_resenas_ahora() );
	$pedido->save_meta_data();
	$pedido->add_order_note( sprintf(
		'Petición de reseña enviada por email a %s (%s; %s, de %s).',
		$email,
		$manual ? 'a mano, desde el pedido' : 'automática, el día después de la entrega',
		array( 'es' => 'castellano', 'ca' => 'catalán', 'en' => 'inglés' )[ $e['idioma'] ],
		$e['usted'] ? 'usted' : 'tú'
	) );
	return array( 'estado' => 'enviado', 'motivo' => '' );
}

/**
 * Pasada diaria: pide la reseña a los pedidos que tocan. Devuelve [ 'enviado' => [ id => '' ], 'omitido' => [ id => motivo ],
 * 'fallo' => [ id => motivo ] ]. Si algún email falla, UN aviso al correo de avisos con todos.
 */
function dip_resenas_pasada() {
	$resultado = array( 'enviado' => array(), 'omitido' => array(), 'fallo' => array() );
	if ( '' === trim( (string) dip_ajuste( 'resenas_url' ) ) ) return $resultado;
	// Una pasada a la vez (WP-Cron puede lanzar la tarea dos veces seguidas): nadie recibe el email dos veces
	if ( ! add_option( 'dip_resenas_en_marcha', time(), '', false ) ) {
		if ( time() - (int) get_option( 'dip_resenas_en_marcha' ) < 15 * MINUTE_IN_SECONDS ) return $resultado;
		update_option( 'dip_resenas_en_marcha', time(), false );
	}
	try {
		$pedidos = wc_get_orders( array(
			'type'       => 'shop_order',
			'status'     => array( 'wc-processing', 'wc-completed' ),
			'limit'      => 300,
			'orderby'    => 'date',
			'order'      => 'ASC', // si un cliente tiene dos pedidos en la ventana, el email habla del primero
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'AND',
				array( 'key' => '_dip_fecha_entrega', 'value' => dip_resenas_fechas_ventana(), 'compare' => 'IN' ),
				array( 'key' => '_dip_resena_pedida', 'compare' => 'NOT EXISTS' ),
			),
		) );
		foreach ( $pedidos as $pedido ) {
			$r = dip_resenas_enviar( $pedido );
			$resultado[ $r['estado'] ][ $pedido->get_id() ] = $r['motivo'];
		}
		if ( $resultado['fallo'] ) dip_resenas_avisar_fallos( $resultado['fallo'] );
	} finally {
		delete_option( 'dip_resenas_en_marcha' );
	}
	return $resultado;
}

/** Un solo email al correo de avisos con los pedidos cuya petición no ha salido. */
function dip_resenas_avisar_fallos( array $fallos ) {
	$lineas = array();
	foreach ( $fallos as $id => $motivo ) {
		$p = wc_get_order( $id );
		if ( $p ) $lineas[] = sprintf( "- Pedido %s · %s · %s\n  %s", $p->get_order_number(), $p->get_billing_email(), $motivo, $p->get_edit_order_url() );
	}
	$n = count( $lineas );
	if ( ! $n ) return false;
	$asunto = 1 === $n ? 'Dryicepack: no ha salido 1 petición de reseña' : sprintf( 'Dryicepack: no han salido %d peticiones de reseña', $n );
	$texto  = "Hoy no se han podido enviar estos emails «¿Qué tal llegó tu hielo seco?»:\n\n" . implode( "\n", $lineas )
		. "\n\nQué hacer:\n"
		. "1. Comprueba que la web envía correo (WP Mail SMTP → Herramientas → prueba de email).\n"
		. '2. La web lo vuelve a intentar sola mañana a las 9:00, hasta ' . DIP_RESENAS_DIAS . " días después de la entrega.\n"
		. "3. Para enviarlo a mano: abre el pedido → Acciones del pedido → «Pedir reseña por email» → Actualizar.\n";
	return wp_mail( (string) dip_ajuste( 'email_avisos' ), $asunto, $texto );
}

/* Cada mañana a las 9:00 (tarea diaria del plugin), si está activado en Dryicepack → Ajustes. */
add_action( 'dip_tarea_diaria', static function () {
	if ( dip_ajuste( 'resenas_email' ) ) dip_resenas_pasada();
}, 20 );

/* ---------- Alternativa manual: Acciones del pedido → «Pedir reseña por email» ---------- */
add_filter( 'woocommerce_order_actions', static function ( $acciones ) {
	if ( '' !== trim( (string) dip_ajuste( 'resenas_url' ) ) ) $acciones['dip_pedir_resena'] = 'Pedir reseña por email';
	return $acciones;
}, 20 );

add_action( 'woocommerce_order_action_dip_pedir_resena', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$r = dip_resenas_enviar( $pedido, true );
	if ( 'omitido' === $r['estado'] ) $pedido->add_order_note( 'No se ha pedido la reseña: ' . $r['motivo'] . '.' );
} );

/* =========================================================
 * Bloque de reseña del email de «pedido completado» (inc/factura.php)
 * ========================================================= */

/**
 * ¿Sale el bloque? Nunca a un email dado de baja. Con el email del día siguiente activado, tampoco si este pedido ya
 * lo recibió, si a ese email se le pidió opinión hace menos de 180 días o si todavía le va a llegar (entrega de hace
 * 3 días o menos, o futura): así nadie recibe dos peticiones. Sí sale cuando el pedido se completa tarde.
 */
function dip_resenas_bloque_completado( WC_Order $pedido ) {
	$email = (string) $pedido->get_billing_email();
	if ( dip_resenas_de_baja( $email ) ) return false;
	if ( ! dip_ajuste( 'resenas_email' ) ) return true;
	if ( '' !== (string) $pedido->get_meta( '_dip_resena_pedida' ) ) return false;
	if ( dip_resenas_ultima_peticion( $email, $pedido->get_id() ) ) return false;
	$fecha  = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	$limite = dip_resenas_hoy()->modify( '-' . ( DIP_RESENAS_DIAS - 1 ) . ' days' )->format( 'Y-m-d' );
	return ! ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) && $fecha >= $limite );
}

/* =========================================================
 * Página de baja: ?dip_resenas_baja=HUELLA&firma=…[&idioma=ca|en][&usted=1]
 * GET enseña un botón (los antivirus del correo abren los enlaces y no deben dar de baja a nadie); POST da de baja
 * (el botón de la página y el «Cancelar suscripción» de Gmail o Apple Mail, RFC 8058). No se guarda en caché ni
 * enseña el email.
 * ========================================================= */
add_action( 'template_redirect', 'dip_resenas_pagina_baja', -1 );

function dip_resenas_pagina_baja() {
	if ( ! isset( $_GET['dip_resenas_baja'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- la firma hace de credencial
	$huella = strtolower( sanitize_text_field( wp_unslash( $_GET['dip_resenas_baja'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$firma  = isset( $_GET['firma'] ) ? sanitize_text_field( wp_unslash( $_GET['firma'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$idioma = isset( $_GET['idioma'] ) ? sanitize_key( wp_unslash( $_GET['idioma'] ) ) : 'es'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$idioma = in_array( $idioma, array( 'ca', 'en' ), true ) ? $idioma : 'es';
	$usted  = ! empty( $_GET['usted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$valido = (bool) preg_match( '/^[a-f0-9]{32}$/', $huella ) && hash_equals( dip_resenas_firma( $huella ), $firma );

	if ( ! $valido ) {
		$estado = 'no_valido';
	} elseif ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		dip_resenas_dar_de_baja( $huella );
		$estado = 'hecho';
	} else {
		$estado = isset( dip_resenas_bajas()[ $huella ] ) ? 'hecho' : 'confirmar';
	}

	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true ); // WP Rocket
	nocache_headers();
	status_header( 'no_valido' === $estado ? 400 : 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'Referrer-Policy: no-referrer' ); // la firma no viaja a otras páginas
	echo dip_resenas_html_baja( $estado, $idioma, $usted, $valido ? dip_resenas_url_baja_de_huella( $huella, $idioma, $usted ) : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	exit;
}

/** Página sencilla, sin el tema (no hace falta cargar nada más) y con los colores de la marca. $t: otros textos (inc/ofertas.php). */
function dip_resenas_html_baja( $estado, $idioma, $usted, $accion, ?array $t = null ) {
	$t      = $t ?: dip_resenas_textos_baja( $idioma, $usted );
	$titulo = $t[ $estado . '_titulo' ];
	$inicio = home_url( 'es' === $idioma ? '/' : '/' . $idioma . '/' );
	$boton  = 'confirmar' === $estado
		? '<form method="post" action="' . esc_url( $accion ) . '"><input type="hidden" name="List-Unsubscribe" value="One-Click"><button type="submit">' . esc_html( $t['boton'] ) . '</button></form>'
		: '';
	return '<!doctype html><html lang="' . esc_attr( $idioma ) . '"><head><meta charset="utf-8">'
		. '<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">'
		. '<title>' . esc_html( $titulo . ' · DryIcePack' ) . '</title><style>'
		. ':root{--fondo:#F2F5F7;--caja:#FFFFFF;--tinta:#0F1D29;--suave:#52657A;--marino:#1F3C55;--sobre-marino:#FFFFFF;--linea:#D5DDE4}'
		. '@media (prefers-color-scheme:dark){:root{--fondo:#0B1620;--caja:#13212E;--tinta:#E8EEF3;--suave:#A9B8C6;--marino:#8FB8DA;--sobre-marino:#0B1620;--linea:#24384A}}'
		. '*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px;background:var(--fondo);color:var(--tinta);font:16px/1.55 Inter,"Helvetica Neue",Helvetica,Arial,sans-serif}'
		. 'main{width:100%;max-width:480px;background:var(--caja);border:1px solid var(--linea);border-radius:12px;padding:32px 28px}'
		. '.marca{margin:0 0 20px;font:600 12px/1 "Geist Mono",SFMono-Regular,Menlo,Consolas,monospace;letter-spacing:.12em;text-transform:uppercase;color:var(--suave)}'
		. 'h1{margin:0 0 12px;font-size:22px;line-height:1.25}p{margin:0 0 20px}'
		. 'button{font:inherit;font-weight:600;padding:12px 22px;border:0;border-radius:999px;background:var(--marino);color:var(--sobre-marino);cursor:pointer;transition:transform .15s,opacity .15s}'
		. 'button:hover{transform:translateY(-1px)}button:focus-visible,a:focus-visible{outline:2px solid var(--marino);outline-offset:3px}'
		. 'form{margin:0 0 20px}a{color:var(--marino)}.volver{margin:0;font-size:14px}'
		. '@media (prefers-reduced-motion:reduce){button{transition:none}button:hover{transform:none}}'
		. '</style></head><body><main>'
		. '<p class="marca">DryIcePack</p>'
		. '<h1>' . esc_html( $titulo ) . '</h1>'
		. '<p>' . esc_html( $t[ $estado . '_texto' ] ) . '</p>'
		. $boton
		. '<p class="volver"><a href="' . esc_url( $inicio ) . '">' . esc_html( $t['volver'] ) . '</a></p>'
		. '</main></body></html>';
}

/* ---------- Dryicepack → Ajustes: resumen ---------- */
function dip_resenas_resumen_admin() {
	$ids = wc_get_orders( array(
		'type'       => 'shop_order',
		'limit'      => -1,
		'return'     => 'ids',
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array( 'key' => '_dip_resena_pedida', 'value' => dip_resenas_ahora( '-30 days' ), 'compare' => '>=' ),
		),
	) );
	return sprintf( 'Enviados los últimos 30 días: %d · Clientes dados de baja: %d', count( $ids ), count( dip_resenas_bajas() ) );
}
