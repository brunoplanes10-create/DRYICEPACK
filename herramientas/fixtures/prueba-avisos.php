<?php
/**
 * Pruebas de los emails al cliente (inc/emails.php, inc/avisos.php, inc/condiciones-pdf.php) en el WordPress de prueba
 * (puerto 9500).
 *
 * herramientas/probar-avisos.mjs copia este archivo a plugin/dryicepack-tienda/zz-prueba-avisos.php, lo llama por pasos y
 * lo borra siempre (si se quedara allí iría al .zip).
 *  ?paso=1  Crea los pedidos de prueba (ES, CA, EN; tú y usted; envío y recogida; sábado; en espera) y pasa cada uno a
 *           Procesando: captura el email de pedido recibido (con el PDF de las condiciones adjunto) y el de la tienda.
 *  ?paso=2  Avisos: «va de camino» (a mano, como «Envíos de hoy»), la pasada de las 8:00 («hoy llega» y recogida, también
 *           en sábado y después de la hora límite), la incidencia, que no se repiten, forzar, y los motivos de un «no».
 *  ?paso=3  Fallos (un solo aviso por pasada), vigilancia de la pasada (más de 26 h sin correr), evento programado a las
 *           8:00, «Pedido completado» que ya no sale, la factura simplificada y «¿Necesitas factura?» que sí, la reseña,
 *           el PDF (caché por idioma) y desactiva el aviso «va de camino» para el paso 4.
 *  ?paso=4  Con el aviso desactivado no sale (salvo a mano, con forzar). Lo deja todo como estaba.
 *  ?paso=limpiar  Solo deja todo como estaba (si algo se cortó).
 * No envía emails: un PHPMailer de prueba los guarda (y puede simular un fallo). Van a /salida/avisos/ (.html, .txt).
 */
require dirname( __DIR__, 3 ) . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
if ( ! function_exists( 'dip_aviso_enviar' ) || ! function_exists( 'wc_get_orders' ) || ! function_exists( 'dip_email_documento' ) ) {
	exit( "FALLO: falta WooCommerce o inc/avisos.php / inc/emails.php\n0 comprobaciones, 1 fallos\n" );
}

/* ---------- Correo de prueba: guarda lo que se enviaría (y falla a propósito si se pide) ---------- */
require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';

class Dip_Prueba_Correo_Avisos extends WP_PHPMailer {
	public function send() {
		$asunto = (string) $this->Subject;
		if ( ! empty( $GLOBALS['dip_prueba_fallar'] ) && preg_match( $GLOBALS['dip_prueba_fallar'], $asunto ) ) {
			throw new PHPMailer\PHPMailer\Exception( 'fallo simulado por la prueba' );
		}
		$adjuntos = array();
		foreach ( $this->getAttachments() as $a ) $adjuntos[] = array( 'ruta' => $a[0], 'nombre' => $a[2] );
		$GLOBALS['dip_prueba_correos'][] = array(
			'para'      => array_map( static fn( $x ) => strtolower( $x[0] ), $this->getToAddresses() ),
			'asunto'    => $asunto,
			'cuerpo'    => (string) $this->Body,
			'alt'       => (string) $this->AltBody,
			'tipo'      => (string) $this->ContentType,
			'responder' => array_map( static fn( $x ) => strtolower( $x[0] ), array_values( $this->getReplyToAddresses() ) ),
			'adjuntos'  => $adjuntos,
		);
		return true;
	}
}
$GLOBALS['phpmailer']          = new Dip_Prueba_Correo_Avisos( true );
$GLOBALS['dip_prueba_correos'] = array();
$GLOBALS['dip_prueba_fallar']  = '';
add_filter( 'wp_mail_from', static fn() => 'wordpress@dryicepack.test', 1 );

/* ---------- Herramientas ---------- */
$GLOBALS['dip_p_fallos']  = 0;
$GLOBALS['dip_p_pruebas'] = 0;
function ok( $cond, $msg, $detalle = null ) {
	$GLOBALS['dip_p_pruebas']++;
	if ( $cond ) return true;
	$GLOBALS['dip_p_fallos']++;
	echo 'FALLO: ' . $msg . ( null === $detalle ? '' : ' → ' . ( is_string( $detalle ) ? $detalle : wp_json_encode( $detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) . "\n";
	return false;
}
function fin() {
	echo $GLOBALS['dip_p_pruebas'] . ' comprobaciones, ' . $GLOBALS['dip_p_fallos'] . " fallos\n";
	exit;
}
function texto_de( $html ) {
	$html = preg_replace( '#<head\b.*?</head>#is', '', (string) $html );
	return trim( preg_replace( '/\s+/u', ' ', str_replace( "\u{00A0}", ' ', html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) ) );
}
function correos_a( $email, $desde = 0 ) {
	return array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $desde ), static fn( $c ) => in_array( strtolower( $email ), $c['para'], true ) ) );
}
function n_correos() {
	return count( $GLOBALS['dip_prueba_correos'] );
}
function guardar( $nombre, array $c ) {
	$dir = '/salida/avisos';
	if ( ! is_dir( '/salida' ) ) return;
	wp_mkdir_p( $dir );
	if ( false === @file_put_contents( $dir . '/' . $nombre . '.html', $c['cuerpo'] ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors
		@mkdir( $dir, 0777, true ); // phpcs:ignore WordPress.PHP.NoSilencedErrors -- Playground puede creer que la carpeta existe
		file_put_contents( $dir . '/' . $nombre . '.html', $c['cuerpo'] );
	}
	file_put_contents( $dir . '/' . $nombre . '.txt', 'Asunto: ' . $c['asunto'] . "\nPara: " . implode( ', ', $c['para'] ) . "\nAdjuntos: " . implode( ', ', array_column( $c['adjuntos'], 'nombre' ) ) . "\n\n" . $c['alt'] );
	$GLOBALS['dip_p_asuntos'][ $nombre ] = $c['asunto'];
}
function notas( $id, $texto ) {
	$r = array();
	foreach ( wc_get_order_notes( array( 'order_id' => $id ) ) as $n ) {
		if ( false !== strpos( $n->content, $texto ) ) $r[] = $n->content;
	}
	return $r;
}

/** Revisión común de un email al cliente: asunto, sin «¡» ni emojis, envoltura, texto plano y pie. */
function revisar( $nombre, array $c, $idioma, array $frases = array() ) {
	$txt = texto_de( $c['cuerpo'] );
	ok( mb_strlen( $c['asunto'] ) <= 45, "{$nombre}: asunto de 45 caracteres o menos (" . mb_strlen( $c['asunto'] ) . ')', $c['asunto'] );
	ok( false === strpos( $c['asunto'] . $txt . $c['alt'], '¡' ) && false === strpos( $c['asunto'] . $txt, '!' ), "{$nombre}: sin signos de exclamación" );
	ok( ! preg_match( '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $c['asunto'] . $txt . $c['alt'] ), "{$nombre}: sin emojis" );
	ok( false === stripos( $c['cuerpo'], '#2BB3E9' ), "{$nombre}: sin el cian de compra" );
	ok( false === stripos( $c['cuerpo'], 'gradient' ), "{$nombre}: sin degradados" );
	ok( false !== strpos( $c['cuerpo'], 'logo-dryicepack-email.png' ) && false !== strpos( $c['cuerpo'], 'alt="DryIcePack"' ), "{$nombre}: logo PNG con alt" );
	ok( (bool) preg_match( '/max-width:\s*600px/', $c['cuerpo'] ) && false !== strpos( $c['cuerpo'], 'prefers-color-scheme' ) && false !== strpos( $c['cuerpo'], 'max-width:620px' ), "{$nombre}: 600 px, modo oscuro y móvil" );
	ok( false === stripos( $c['cuerpo'], 'display:none' ) && false === stripos( $c['cuerpo'], 'display: none' ), "{$nombre}: el texto previo no usa display:none" );
	ok( 'text/html' === $c['tipo'] && strlen( trim( $c['alt'] ) ) > 80, "{$nombre}: HTML con versión en texto", mb_substr( $c['alt'], 0, 80 ) );
	ok( false !== strpos( $c['alt'], 'INDUNOVA IMS S.L.' ) && false !== strpos( $c['alt'], 'info@dryicepack.es' ), "{$nombre}: el texto lleva el pie" );
	$pie = array(
		'es' => array( 'INDUNOVA IMS S.L.', '936 73 76 41', 'WhatsApp 686 980 471', 'info@dryicepack.es', 'L–V 9:00–18:00', 'Seguridad del hielo seco', 'Condiciones de venta' ),
		'ca' => array( 'INDUNOVA IMS S.L.', '936 73 76 41', 'WhatsApp 686 980 471', 'info@dryicepack.es', 'Dl–dv 9:00–18:00', 'Seguretat del gel sec', 'Condicions de venda' ),
		'en' => array( 'INDUNOVA IMS S.L.', '+34 936 73 76 41', 'WhatsApp +34 686 980 471', 'info@dryicepack.es', 'Mon–Fri 9:00–18:00', 'Dry ice safety', 'Terms of sale' ),
	)[ $idioma ];
	$faltan = array_values( array_filter( $pie, static fn( $f ) => false === strpos( $txt, $f ) ) );
	ok( ! $faltan, "{$nombre}: pie completo ({$idioma})", $faltan );
	ok( (bool) preg_match( '#href="[^"]*' . preg_quote( array( 'es' => 'seguridad-del-hielo-seco', 'ca' => 'seguretat-del-gel-sec', 'en' => 'dry-ice-safety' )[ $idioma ], '#' ) . '#', $c['cuerpo'] ), "{$nombre}: enlace a seguridad en su idioma" );
	foreach ( $frases as $f ) ok( false !== strpos( $txt, $f ), "{$nombre}: dice «{$f}»", mb_substr( $txt, 0, 700 ) );
	if ( 'en' === $idioma ) ok( ! preg_match( '/\b(Hola|Pedido|Gracias|hielo)\b/u', $txt ), "{$nombre}: nada en castellano" );
	if ( 'ca' === $idioma ) ok( ! preg_match( '/\b(Pedido|Gracias|hielo seco)\b/u', $txt ), "{$nombre}: nada en castellano" );
	return $txt;
}

/* ---------- Pedidos de prueba ---------- */
function variacion( $kg, $formato = '3mm' ) {
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	$padre = $post ? wc_get_product( $post->ID ) : null;
	if ( ! $padre ) return null;
	foreach ( $padre->get_children() as $id ) {
		$v = wc_get_product( $id );
		if ( ! $v ) continue;
		$a = $v->get_attributes();
		if ( ( $a['pa_peso'] ?? '' ) === $kg . 'kg-de-hielo-seco' && ( $a['pa_formato'] ?? '' ) === $formato ) return $v;
	}
	return null;
}

/** Crea un pedido (Pendiente, sin emails todavía). $d: clave, nombre, email, idioma, empresa, metodo, fecha, kg, cantidad, formato, sabado, pago, factura. */
function crear( array $d ) {
	$v        = variacion( $d['kg'] ?? 10, $d['formato'] ?? '3mm' );
	$recoge   = 'recogida' === ( $d['metodo'] ?? 'envio' );
	$cantidad = (int) ( $d['cantidad'] ?? 1 );
	$p        = new WC_Order();
	$p->set_created_via( 'prueba-avisos' );
	$dir = array(
		'first_name' => $d['nombre'],
		'last_name'  => '',
		'company'    => $d['empresa'] ?? '',
		'address_1'  => $d['calle'] ?? 'Carrer de Mallorca 123',
		'address_2'  => $d['piso'] ?? '2º 1ª',
		'postcode'   => $d['cp'] ?? '08013',
		'city'       => $d['ciudad'] ?? 'Barcelona',
		'state'      => 'B',
		'country'    => 'ES',
	);
	$p->set_address( $dir + array( 'email' => $d['email'], 'phone' => '600123456' ), 'billing' );
	$p->set_address( $dir, 'shipping' );
	$p->add_product( $v, $cantidad );
	if ( $recoge && function_exists( 'dip_precio_recogida_de' ) ) {
		// Precio redondo de recogida (inc/precios-recogida.php): base = precio con IVA / 1,21, sin redondear
		foreach ( $p->get_items() as $item ) {
			$base = dip_precio_recogida_de( (float) $v->get_price( 'edit' ) ) / dip_factor_iva() * $cantidad;
			$item->set_subtotal( $base );
			$item->set_total( $base );
		}
	}
	$envio = new WC_Order_Item_Shipping();
	if ( $recoge ) {
		$envio->set_props( array( 'method_title' => 'Recogida en Mataró', 'method_id' => 'local_pickup', 'total' => 0 ) );
	} else {
		$envio->set_props( array( 'method_title' => 'Envío MRW', 'method_id' => 'flat_rate', 'total' => dip_coste_transporte( (float) ( $d['kg'] ?? 10 ) * $cantidad, $cantidad ) ) );
	}
	$p->add_item( $envio );
	if ( ! empty( $d['sabado'] ) ) {
		$fee = new WC_Order_Item_Fee();
		$fee->set_props( array(
			'name'      => $recoge ? 'Suplemento de recogida en sábado' : 'Suplemento de entrega en sábado',
			'tax_class' => '',
			'tax_status' => 'taxable',
			'total'     => $recoge ? round( DIP_RECOGIDA_SABADO_CON_IVA / dip_factor_iva(), 2 ) : DIP_SATURDAY_SURCHARGE_EXCL_TAX,
		) );
		if ( $recoge ) $fee->add_meta_data( '_dip_con_iva', wc_format_decimal( DIP_RECOGIDA_SABADO_CON_IVA, 2 ), true ); // 10,00 € justos, como el checkout (inc/entrega.php)
		$p->add_item( $fee );
	}
	$pago = $d['pago'] ?? ( $recoge ? 'cod' : 'cheque' );
	$p->set_payment_method( $pago );
	$p->set_payment_method_title( 'cod' === $pago ? 'Efectivo al recoger' : 'Tarjeta' );
	$p->update_meta_data( '_dip_fecha_entrega', $d['fecha'] );
	$p->update_meta_data( '_dip_metodo_entrega', $recoge ? 'recogida' : 'envio' );
	$p->update_meta_data( '_dip_idioma', $d['idioma'] ?? 'es' );
	$p->update_meta_data( '_dip_empresa', empty( $d['empresa'] ) ? 0 : 1 );
	if ( ! empty( $d['empresa'] ) ) $p->update_meta_data( '_billing_nif', $d['nif'] ?? 'B60123457' );
	$p->update_meta_data( '_dip_canal', 'web' );
	$p->update_meta_data( '_dip_kg', (float) ( $d['kg'] ?? 10 ) * $cantidad );
	$p->update_meta_data( '_dip_prueba_avisos', $d['clave'] );
	if ( empty( $d['factura'] ) ) {
		$p->update_meta_data( '_dip_factura_emitida', 1 ); // sin factura simplificada (no gasta números de la serie)
		$p->update_meta_data( '_dip_email_factura', 'prueba' ); // ni «¿Necesitas factura?»
	}
	if ( ! empty( $d['hora'] ) ) $p->update_meta_data( '_dip_hora_recogida', $d['hora'] );
	if ( ! empty( $d['nota'] ) ) $p->set_customer_note( $d['nota'] );
	$p->calculate_totals( true );
	$p->set_status( 'pending' );
	$p->save();
	return $p;
}

function pedido( $clave ) {
	$ids = wc_get_orders( array( 'limit' => 1, 'return' => 'ids', 'meta_key' => '_dip_prueba_avisos', 'meta_value' => $clave, 'type' => 'shop_order' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	return $ids ? wc_get_order( $ids[0] ) : null;
}

function limpiar() {
	$ids = wc_get_orders( array( 'limit' => -1, 'return' => 'ids', 'meta_key' => '_dip_prueba_avisos', 'meta_compare' => 'EXISTS', 'type' => 'shop_order', 'status' => array_keys( wc_get_order_statuses() ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( $p ) $p->delete( true );
	}
	$copia = get_option( 'dip_prueba_avisos_copia' );
	if ( is_array( $copia ) ) {
		update_option( 'dip_ajustes', $copia['ajustes'], true );
		if ( null === $copia['estado'] ) delete_option( 'dip_avisos_estado' );
		else update_option( 'dip_avisos_estado', $copia['estado'], false );
		delete_option( 'dip_prueba_avisos_copia' );
	}
	delete_option( 'dip_avisos_en_marcha' );
	return count( $ids );
}

// Días de la prueba (futuros y sin festivos): miércoles, jueves y sábado
const DIA_ENVIO    = '2026-12-02';
const DIA_RECOGIDA = '2026-12-03';
const DIA_SABADO   = '2026-12-05';
function a_las( $ymd, $hora ) {
	return new DateTimeImmutable( $ymd . ' ' . $hora, dip_zona_horaria() );
}

$GLOBALS['dip_p_asuntos'] = array();
$paso = isset( $_GET['paso'] ) ? sanitize_key( wp_unslash( $_GET['paso'] ) ) : '';

if ( 'limpiar' === $paso ) {
	echo 'Pedidos de prueba borrados: ' . limpiar() . "\n";
	fin();
}

/* Clientes: idioma, trato, nombre */
$clientes = array(
	'es-tu'    => array( 'idioma' => 'es', 'nombre' => 'Laura García Pons' ),
	'es-usted' => array( 'idioma' => 'es', 'nombre' => 'Marta Soler Vidal', 'empresa' => 'Laboratorio Soler S.L.' ),
	'ca-tu'    => array( 'idioma' => 'ca', 'nombre' => 'Jordi Puig Serra', 'cp' => '17001', 'ciudad' => 'Girona', 'calle' => 'Carrer Nou 8' ),
	'ca-usted' => array( 'idioma' => 'ca', 'nombre' => 'Núria Ferrer Camps', 'empresa' => 'Càtering Ferrer S.L.', 'cp' => '08301', 'ciudad' => 'Mataró', 'calle' => 'Riera 20' ),
	'en-tu'    => array( 'idioma' => 'en', 'nombre' => 'Emma Clarke', 'cp' => '28014', 'ciudad' => 'Madrid', 'calle' => 'Calle de Alcalá 45', 'piso' => '3º B' ),
	'en-usted' => array( 'idioma' => 'en', 'nombre' => 'Oliver Hughes', 'empresa' => 'Hughes Events S.L.', 'cp' => '46001', 'ciudad' => 'València', 'calle' => 'Carrer de Colón 10', 'piso' => '' ),
);

/* =========================================================
 * Paso 1: pedidos y email de pedido recibido
 * ========================================================= */
if ( '1' === $paso ) {
	limpiar();
	update_option( 'dip_prueba_avisos_copia', array( 'ajustes' => get_option( 'dip_ajustes', array() ), 'estado' => get_option( 'dip_avisos_estado', null ) ), false );
	$pedidos = array();
	foreach ( $clientes as $k => $c ) {
		$pedidos[ 'envio-' . $k ]    = $c + array( 'clave' => 'envio-' . $k, 'email' => "envio-{$k}@example.com", 'metodo' => 'envio', 'fecha' => DIA_ENVIO, 'kg' => 10, 'cantidad' => 2, 'formato' => '3mm' );
		$pedidos[ 'recogida-' . $k ] = $c + array( 'clave' => 'recogida-' . $k, 'email' => "recogida-{$k}@example.com", 'metodo' => 'recogida', 'fecha' => DIA_RECOGIDA, 'kg' => 10, 'cantidad' => 1, 'formato' => '16mm' );
	}
	$pedidos['envio-sabado']    = $clientes['es-tu'] + array( 'clave' => 'envio-sabado', 'email' => 'envio-sabado@example.com', 'metodo' => 'envio', 'fecha' => DIA_SABADO, 'kg' => 20, 'cantidad' => 1, 'sabado' => 1, 'nota' => 'Dejar en conserjería si no estoy.' );
	$pedidos['recogida-sabado'] = $clientes['es-tu'] + array( 'clave' => 'recogida-sabado', 'email' => 'recogida-sabado@example.com', 'metodo' => 'recogida', 'fecha' => DIA_SABADO, 'kg' => 15, 'cantidad' => 1, 'sabado' => 1, 'hora' => '10:30' );
	$pedidos['espera']          = $clientes['es-tu'] + array( 'clave' => 'espera', 'email' => 'espera@example.com', 'metodo' => 'envio', 'fecha' => DIA_ENVIO, 'kg' => 10, 'cantidad' => 1 );
	$pedidos['sin-enviar']      = $clientes['es-tu'] + array( 'clave' => 'sin-enviar', 'email' => 'sin-enviar@example.com', 'metodo' => 'envio', 'fecha' => DIA_ENVIO, 'kg' => 10, 'cantidad' => 1 );
	$pedidos['grande']          = $clientes['es-tu'] + array( 'clave' => 'grande', 'email' => 'grande@example.com', 'metodo' => 'envio', 'fecha' => DIA_ENVIO, 'kg' => 20, 'cantidad' => 5, 'factura' => 1 );
	$pedidos['fs-recogida']     = $clientes['es-tu'] + array( 'clave' => 'fs-recogida', 'email' => 'fs-recogida@example.com', 'metodo' => 'recogida', 'fecha' => DIA_RECOGIDA, 'kg' => 10, 'cantidad' => 1, 'factura' => 1 );

	foreach ( $pedidos as $clave => $d ) {
		$antes = n_correos();
		$p     = crear( $d );
		ok( $p instanceof WC_Order && $p->get_id(), "{$clave}: pedido creado" );
		$p->set_status( 'espera' === $clave ? 'on-hold' : 'processing' );
		$p->save();
		$cli = array_values( array_filter( correos_a( $d['email'], $antes ), static fn( $x ) => false === stripos( $x['asunto'], 'necesitas factura' ) ) );
		if ( 'grande' === $clave ) ok( 2 === count( correos_a( $d['email'], $antes ) ), 'grande: además, «¿Necesitas factura?» (más de 400 €)' );
		if ( ! ok( 1 === count( $cli ), "{$clave}: un email al cliente al pasar a " . ( 'espera' === $clave ? 'En espera' : 'Procesando' ), array_column( $cli, 'asunto' ) ) ) continue;
		$c      = $cli[0];
		$idioma = $d['idioma'];
		$nombre = 'recibido-' . $clave;
		guardar( $nombre, $c );
		$frases = array();
		$es_rec = 'recogida' === $d['metodo'];
		$usted  = ! empty( $d['empresa'] );
		if ( 'es' === $idioma ) {
			$frases = $es_rec ? array( 'Recogida', 'Camí Ca La Madrona 19 D, 08304 Mataró', 'Qué pasa ahora', 'Información legal', 'art. 103.d TRLGDCU', 'garantía legal', 'Reclamaciones: info@dryicepack.es', 'Ronda Alfonso X El Sabio 10, 2º 1ª, 08301 Mataró', 'sin perjuicio de los derechos que la ley reconoce al consumidor, incluida la indemnización de daños y perjuicios' )
				: array( 'antes de las ' . ( DIA_SABADO === $d['fecha'] ? '13:00' : '14:00' ), 'Qué pasa ahora', 'Resumen del pedido', 'Información legal', 'art. 103.d TRLGDCU', 'Si no llega ese día', 'reembolso completo o recogerlo en Mataró', 'Las condiciones de venta van adjuntas en PDF' );
		} elseif ( 'ca' === $idioma ) {
			$frases = $es_rec ? array( 'Recull', 'Què passa ara', 'Informació legal' ) : array( 'abans de les 14:00', 'Què passa ara', 'Resum de la comanda', 'Les condicions de venda van adjuntes en PDF' );
		} else {
			$frases = $es_rec ? array( 'Collect it on', 'What happens next', 'Legal information' ) : array( 'before 14:00', 'What happens next', 'Order summary', 'The terms of sale are attached as a PDF' );
		}
		$txt = revisar( $nombre, $c, $idioma, $frases );
		// Asunto y título
		$dia = dip_avisos_dia_corto( $d['fecha'], $idioma );
		ok( 'espera' === $clave ? false !== strpos( $c['asunto'], 'confirmamos el pago' ) : false !== strpos( $c['asunto'], $dia ), "{$clave}: el asunto dice el día ({$dia})", $c['asunto'] );
		ok( false !== strpos( $c['asunto'], (string) $p->get_order_number() ), "{$clave}: el asunto lleva el número de pedido", $c['asunto'] );
		// Recogida: el texto del efectivo del plugin, al céntimo
		if ( $es_rec ) {
			$frase = dip_avisos_texto_efectivo( $p, $idioma, $usted );
			ok( '' !== $frase && false !== strpos( $txt, str_replace( "\u{00A0}", ' ', html_entity_decode( $frase, ENT_QUOTES, 'UTF-8' ) ) ), "{$clave}: dice «{$frase}»", mb_substr( $txt, 0, 500 ) );
			if ( ! $usted && 'es' === $idioma ) ok( false !== strpos( $frase, 'Trae el importe exacto: no tenemos cambio.' ), "{$clave}: reutiliza dip_texto_pago_efectivo()", $frase );
			if ( $usted && 'es' === $idioma ) ok( false !== strpos( $frase, 'Traiga el importe exacto' ), "{$clave}: de usted, «Traiga el importe exacto»", $frase );
		}
		if ( 'recogida-sabado' === $clave ) ok( false !== strpos( $txt, '10:30' ), 'recogida-sabado: la hora acordada (10:30) en la ficha' );
		if ( 'envio-sabado' === $clave ) ok( false !== strpos( $txt, 'antes de las 13:00' ) && false !== strpos( $txt, 'Suplemento de entrega en sábado' ) && false !== strpos( $txt, 'Tus notas: Dejar en conserjería si no estoy.' ), 'envio-sabado: 13:00, suplemento en la tabla y nota del cliente' );
		if ( 'espera' === $clave ) ok( false !== strpos( $txt, 'Estamos confirmando el pago' ), 'espera: dice que se está confirmando el pago' );
		// Tú y usted
		if ( 'es' === $idioma && $usted ) ok( ! preg_match( '/\b(tu pedido|Recógelo|te avisamos|eliges)\b/u', $txt . $c['alt'] ), "{$clave}: de usted, sin tú", mb_substr( $txt, 0, 400 ) );
		if ( 'es' === $idioma && ! $usted ) ok( ! preg_match( '/\b(su pedido|Recójalo|le avisamos)\b/u', $txt . $c['alt'] ), "{$clave}: de tú, sin usted" );
		if ( $usted && 'es' === $idioma ) ok( false !== strpos( $txt, 'Laboratorio Soler S.L.' ) && false !== strpos( $txt, 'B60123457' ), "{$clave}: factura a la empresa con su NIF en la ficha" );
		// Sin la línea «Entrega: …» de inc/entrega.php (ya va arriba)
		ok( false === strpos( $txt, ( 'es' === $idioma ? 'Entrega: ' : ( 'ca' === $idioma ? 'Entrega: ' : 'Delivery: ' ) ) . dip_fecha_larga( $d['fecha'], $idioma ) . ' ' ), "{$clave}: sin la línea repetida de inc/entrega.php" );
		// Adjunto: las condiciones en PDF en su idioma
		$pdf = dip_avisos_textos( $idioma )['pdf_archivo'];
		$adj = array_column( $c['adjuntos'], 'nombre' );
		ok( in_array( $pdf, $adj, true ), "{$clave}: adjunta {$pdf}", $adj );
		foreach ( $c['adjuntos'] as $a ) {
			if ( $a['nombre'] === $pdf ) ok( is_readable( $a['ruta'] ) && 0 === strpos( (string) file_get_contents( $a['ruta'], false, null, 0, 8 ), '%PDF-1.' ), "{$clave}: el adjunto es un PDF" );
		}
		// Botón «Ver mi pedido» (enlace firmado de inc/mi-pedido.php) y a prueba de Outlook
		if ( function_exists( 'dip_url_mi_pedido' ) ) {
			ok( false !== strpos( $c['cuerpo'], 'v:roundrect' ) && false !== strpos( $c['cuerpo'], esc_url( dip_url_mi_pedido( $p ) ) ), "{$clave}: botón «Ver mi pedido» con VML para Outlook" );
		}
		// El email a la tienda también lleva la envoltura nueva (en castellano)
		$tienda = array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $antes ), static fn( $x ) => ! in_array( strtolower( $d['email'] ), $x['para'], true ) && false !== stripos( $x['asunto'], (string) $p->get_order_number() ) ) );
		if ( 'envio-es-tu' === $clave && $tienda ) {
			guardar( 'tienda-nuevo-pedido', $tienda[0] );
			ok( false !== strpos( $tienda[0]['cuerpo'], 'logo-dryicepack-email.png' ) && false !== strpos( texto_de( $tienda[0]['cuerpo'] ), 'Condiciones de venta' ), 'email a la tienda (nuevo pedido) con la envoltura común, en castellano' );
		}
	}
	// Plantilla de texto plano (si se elige «Texto plano» en WooCommerce): mismo contenido que la versión alternativa
	$p     = pedido( 'envio-ca-tu' );
	$email = WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order'];
	$email->object = $p;
	$plano = wc_get_template_html( 'emails/plain/customer-processing-order.php', array( 'order' => $p, 'email_heading' => '', 'additional_content' => '', 'sent_to_admin' => false, 'plain_text' => true, 'email' => $email ) );
	ok( false !== strpos( $plano, 'Arriba el dimecres 2 de desembre abans de les 14:00' ) && false !== strpos( $plano, 'QUÈ PASSA ARA' ) && false !== strpos( $plano, 'Condicions de venda' ), 'plantilla de texto plano en catalán', mb_substr( $plano, 0, 300 ) );
	fin();
}

/* =========================================================
 * Paso 2: avisos
 * ========================================================= */
if ( '2' === $paso ) {
	// «Va de camino»: lo que hace «Envíos de hoy» al guardar el número de MRW
	foreach ( $clientes as $k => $c ) {
		$p = pedido( 'envio-' . $k );
		if ( ! ok( (bool) $p, "envio-{$k}: existe" ) ) continue;
		$p->update_meta_data( '_dip_mrw_envio', '0283014796' . substr( (string) $p->get_id(), -3 ) );
		$p->update_meta_data( '_dip_enviado', '2026-12-01 11:05:00' );
		$p->save_meta_data();
		$antes = n_correos();
		$r     = dip_aviso_enviar( $p, 'camino' );
		ok( true === $r, "camino {$k}: dip_aviso_enviar devuelve true", dip_aviso_ultimo_motivo() );
		$cli = correos_a( 'envio-' . $k . '@example.com', $antes );
		if ( ! ok( 1 === count( $cli ), "camino {$k}: un email" ) ) continue;
		$frases = array( 'es' => array( 'Llega el miércoles 2 de diciembre antes de las 14:00', 'Envío MRW', $p->get_meta( '_dip_mrw_envio' ), '¿No va a haber nadie?','WhatsApp al 686 980 471', 'No se come ni se bebe' ), 'ca' => array( 'Arriba el dimecres 2 de desembre abans de les 14:00', 'Enviament MRW', 'No hi haurà ningú?', 'No es menja ni es beu' ), 'en' => array( 'Arrives on Wednesday 2 December before 14:00', 'MRW tracking', 'Will nobody be in?', 'It is not for eating or drinking' ) )[ $c['idioma'] ];
		$txt = revisar( 'camino-' . $k, $cli[0], $c['idioma'], $frases );
		guardar( 'camino-' . $k, $cli[0] );
		if ( function_exists( 'dip_url_seguimiento_mrw' ) ) ok( false !== strpos( $cli[0]['cuerpo'], esc_url( dip_url_seguimiento_mrw( $p->get_meta( '_dip_mrw_envio' ) ) ) ), "camino {$k}: enlace de seguimiento de MRW" );
		ok( (bool) preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) wc_get_order( $p->get_id() )->get_meta( '_dip_aviso_camino' ) ), "camino {$k}: guarda _dip_aviso_camino con la fecha" );
		ok( (bool) notas( $p->get_id(), 'Aviso «Va de camino» enviado por email' ), "camino {$k}: nota en el pedido" );
		if ( 'es-usted' === $k ) ok( false !== strpos( $txt, 'Hola, Marta Soler Vidal.' ) && false !== strpos( $txt, 'Su hielo seco' ) && false === strpos( $txt, 'Tu hielo seco' ), 'camino es-usted: saludo con el nombre completo y de usted' );
		if ( 'es-tu' === $k ) ok( false !== strpos( $txt, 'Hola, Laura. Tu hielo seco ha salido de Mataró con MRW.' ), 'camino es-tu: «Hola, Laura. Tu hielo seco ha salido…»', mb_substr( $txt, 0, 300 ) );
		if ( 'en-tu' === $k ) ok( false !== strpos( $txt, 'Hi Emma, your dry ice has left Mataró with MRW.' ), 'camino en-tu: «Hi Emma, your dry ice…»' );
	}
	// No se repite; con forzar, sí
	$p     = pedido( 'envio-es-tu' );
	$antes = n_correos();
	ok( false === dip_aviso_enviar( $p, 'camino' ) && 0 === strpos( dip_aviso_ultimo_motivo(), 'ya se envió el ' ) && n_correos() === $antes, 'camino: no se repite', dip_aviso_ultimo_motivo() );
	ok( true === dip_aviso_enviar( $p, 'camino', array( 'forzar' => true ) ) && n_correos() === $antes + 1, 'camino: con forzar sale otra vez' );
	// Motivos de un «no»
	ok( false === dip_aviso_enviar( pedido( 'recogida-es-tu' ), 'camino' ) && false !== strpos( dip_aviso_ultimo_motivo(), 'se recoge en Mataró' ), 'camino: no a un pedido de recogida', dip_aviso_ultimo_motivo() );
	ok( false === dip_aviso_enviar( pedido( 'envio-es-tu' ), 'recogida', array( 'forzar' => true ) ) && false !== strpos( dip_aviso_ultimo_motivo(), 'va por mensajería' ), 'recogida: no a un pedido con envío', dip_aviso_ultimo_motivo() );
	ok( false === dip_aviso_enviar( pedido( 'envio-es-tu' ), 'nada' ) && false !== strpos( dip_aviso_ultimo_motivo(), 'desconocido' ), 'tipo desconocido' );
	ok( false === dip_aviso_enviar( pedido( 'sin-enviar' ), 'hoy', array( 'ahora' => a_las( DIA_ENVIO, '08:00' ) ) ) && false !== strpos( dip_aviso_ultimo_motivo(), 'no está marcado como enviado' ), 'hoy: no si no está marcado como enviado', dip_aviso_ultimo_motivo() );
	ok( false === dip_aviso_enviar( pedido( 'envio-es-tu' ), 'hoy', array( 'ahora' => a_las( '2026-12-01', '08:00' ) ) ) && false !== strpos( dip_aviso_ultimo_motivo(), 'la entrega no es hoy' ), 'hoy: no si la entrega no es hoy', dip_aviso_ultimo_motivo() );
	$c1 = pedido( 'envio-ca-tu' );
	$c1->set_status( 'cancelled' );
	$c1->save();
	ok( false === dip_aviso_enviar( wc_get_order( $c1->get_id() ), 'incidencia', array( 'forzar' => true ) ) && false !== stripos( dip_aviso_ultimo_motivo(), 'cancel' ), 'ningún aviso a un pedido cancelado', dip_aviso_ultimo_motivo() );
	$c1->set_status( 'processing' );
	$c1->save();

	// Pasada de las 8:00 del miércoles: «Hoy llega» a los enviados con entrega ese día; el que no está enviado, no
	$sabado = pedido( 'envio-sabado' );
	$sabado->update_meta_data( '_dip_mrw_envio', '0283014796999' );
	$sabado->update_meta_data( '_dip_enviado', '2026-12-04 10:40:00' );
	$sabado->save_meta_data();
	$antes = n_correos();
	$r     = dip_avisos_pasada( a_las( DIA_ENVIO, '08:00' ) );
	$hoy   = array_map( static fn( $k ) => pedido( 'envio-' . $k )->get_id(), array_keys( $clientes ) );
	ok( ! array_diff( $hoy, $r['hoy'] ) && ! in_array( pedido( 'sin-enviar' )->get_id(), $r['hoy'], true ), 'pasada del miércoles: «Hoy llega» a los 6 pedidos enviados y no al que no está enviado', $r );
	foreach ( $clientes as $k => $c ) {
		$cli = correos_a( 'envio-' . $k . '@example.com', $antes );
		if ( ! ok( 1 === count( $cli ), "hoy {$k}: un email" ) ) continue;
		$frases = array( 'es' => array( 'hielo seco antes de las 14:00', 'Asegúr', 'Carrer de Mallorca 123', 'recibirlo?' ), 'ca' => array( 'gel sec abans de les 14:00', 'Assegur', 'rebre?' ), 'en' => array( 'arrives today before 14:00', 'make sure someone is at the address', 'Unable to take delivery?' ) )[ $c['idioma'] ];
		revisar( 'hoy-' . $k, $cli[0], $c['idioma'], array_filter( $frases ) );
		guardar( 'hoy-' . $k, $cli[0] );
	}
	$antes = n_correos();
	$r2    = dip_avisos_pasada( a_las( DIA_ENVIO, '08:10' ) );
	ok( ! $r2['hoy'] && n_correos() === $antes, 'segunda pasada el mismo día: no repite «Hoy llega»', $r2 );

	// Recogida del jueves a las 8:00 y pasada tardía (después de las 13:00: no se manda)
	$antes = n_correos();
	$r     = dip_avisos_pasada( a_las( DIA_RECOGIDA, '13:30' ) );
	ok( ! $r['recogida'] && n_correos() === $antes && (bool) preg_grep( '/después de las 13:00/', $r['nota'] ), 'pasada a las 13:30: no manda el recordatorio de recogida', $r );
	$r = dip_avisos_pasada( a_las( DIA_RECOGIDA, '08:00' ) );
	foreach ( $clientes as $k => $c ) {
		$cli = correos_a( 'recogida-' . $k . '@example.com', $antes );
		if ( ! ok( 1 === count( $cli ), "recogida {$k}: un email" ) ) continue;
		$frases = array( 'es' => array( 'Camí Ca La Madrona 19 D, 08304 Mataró', 'importe exacto', 'Cómo llegar', '936 73 76 41', '686 980 471', 'maletero' ), 'ca' => array( 'Camí Ca La Madrona 19 D, 08304 Mataró', 'import exacte', 'Com arribar-hi', 'maleter' ), 'en' => array( 'Camí Ca La Madrona 19 D, 08304 Mataró', 'exact amount', 'How to get there', 'boot' ) )[ $c['idioma'] ];
		$txt = revisar( 'recogida-' . $k, $cli[0], $c['idioma'], $frases );
		ok( false !== strpos( $cli[0]['cuerpo'], 'google.com/maps' ), "recogida {$k}: enlace al mapa" );
		ok( false !== strpos( $txt, str_replace( "\u{00A0}", ' ', html_entity_decode( dip_avisos_texto_efectivo( pedido( 'recogida-' . $k ), $c['idioma'], ! empty( $c['empresa'] ) ), ENT_QUOTES, 'UTF-8' ) ) ), "recogida {$k}: importe exacto en efectivo" );
		guardar( 'recogida-' . $k, $cli[0] );
	}
	// Sábado: «Hoy llega» antes de las 13:00 y la recogida con su suplemento y la hora acordada
	$antes = n_correos();
	$r     = dip_avisos_pasada( a_las( DIA_SABADO, '08:00' ) );
	$c     = correos_a( 'envio-sabado@example.com', $antes );
	if ( ok( 1 === count( $c ), 'hoy sábado: un email' ) ) {
		revisar( 'hoy-sabado', $c[0], 'es', array( 'Hoy llega tu hielo seco antes de las 13:00' ) );
		guardar( 'hoy-es-tu-sabado', $c[0] );
	}
	$c = correos_a( 'recogida-sabado@example.com', $antes );
	if ( ok( 1 === count( $c ), 'recogida sábado: un email' ) ) {
		revisar( 'recogida-sabado', $c[0], 'es', array( '10:30', 'Incluye el suplemento de recogida en sábado: 10,00 € (IVA incluido).', 'Si al final no puedes venir, avísanos' ) );
		guardar( 'recogida-es-tu-sabado', $c[0] );
	}
	// Pasada del sábado a las 13:30: «Hoy llega» ya no (límite 13:00 desde el 11/11/2026)
	$x = pedido( 'espera' );
	$x->update_meta_data( '_dip_fecha_entrega', DIA_SABADO );
	$x->update_meta_data( '_dip_enviado', '2026-12-04 10:00:00' );
	$x->save_meta_data();
	$r = dip_avisos_pasada( a_las( DIA_SABADO, '13:30' ) );
	ok( ! $r['hoy'] && (bool) preg_grep( '/después de las 13:00/', $r['nota'] ), 'pasada del sábado a las 13:30: no manda «Hoy llega» (límite 13:00)', $r );

	// Incidencia (a mano): reembolso completo o recogida con el envío devuelto
	foreach ( $clientes as $k => $c ) {
		$p     = pedido( 'envio-' . $k );
		$antes = n_correos();
		ok( true === dip_aviso_enviar( $p, 'incidencia', array( 'forzar' => true, 'manual' => true ) ), "incidencia {$k}: sale", dip_aviso_ultimo_motivo() );
		$cli = correos_a( 'envio-' . $k . '@example.com', $antes );
		if ( ! ok( 1 === count( $cli ), "incidencia {$k}: un email" ) ) continue;
		$frases = array( 'es' => array( 'Reembolso completo', 'Recogerlo en Mataró', 'te devolvemos el envío', 'Sin perjuicio de los derechos que la ley reconoce al consumidor, incluida la indemnización de daños y perjuicios.' ), 'ca' => array( 'Reemborsament complet', 'Recollir-la a Mataró', 'Sense perjudici dels drets' ), 'en' => array( 'Full refund', 'Collect it in Mataró', 'without prejudice' ) )[ $c['idioma'] ];
		if ( 'es-usted' === $k ) $frases[2] = 'le devolvemos el envío';
		$txt = revisar( 'incidencia-' . $k, $cli[0], $c['idioma'], $frases );
		ok( false === stripos( $txt, 'culpa' ) && ! preg_match( '/MRW (no|ha fallado|perdi)/u', $txt ), "incidencia {$k}: sin culpar a MRW" );
		ok( ! preg_match( '/otro día|un altre dia|another day/u', $txt ), "incidencia {$k}: sin la opción «recibirlo otro día»" );
		guardar( 'incidencia-' . $k, $cli[0] );
	}
	// Incidencia del sábado con mensaje: se quedan los 10 € de la recogida en sábado y se devuelve la diferencia (1,74 €)
	$p = pedido( 'envio-sabado' );
	$p->update_meta_data( '_dip_incidencia_mensaje', 'MRW no tiene reparto en sábado en tu código postal.' );
	$p->save_meta_data();
	$antes = n_correos();
	ok( true === dip_aviso_enviar( $p, 'incidencia', array( 'manual' => true, 'forzar' => true ) ), 'incidencia sábado: sale' );
	ok( true === dip_aviso_enviar( wc_get_order( $p->get_id() ), 'incidencia', array( 'mensaje' => '' ) ), 'incidencia: se puede repetir (sin forzar)' );
	$c = correos_a( 'envio-sabado@example.com', $antes );
	if ( ok( 2 === count( $c ), 'incidencia sábado: dos emails' ) ) {
		$envio = dip_avisos_importe_envio( $p );
		$sup   = dip_avisos_suplemento_envio( $p );
		$nb    = static fn( $s ) => str_replace( "\u{00A0}", ' ', $s );
		$txt   = revisar( 'incidencia-sabado', $c[0], 'es', array( 'MRW no tiene reparto en sábado en tu código postal.', $nb( 'te devolvemos el envío y el suplemento de sábado: ' . dip_avisos_euros( $envio + $sup ) ), 'del envío que pagaste nos quedamos los 10,00 € de la recogida en sábado y te devolvemos el resto: ' . $nb( dip_avisos_euros( $envio + $sup - 10 ) ) ) );
		ok( abs( $sup - 11.74 ) < 0.001, 'el suplemento de envío en sábado del pedido es 11,74 €', $sup );
		guardar( 'incidencia-es-tu-sabado', $c[0] );
		ok( false === strpos( texto_de( $c[1]['cuerpo'] ), 'MRW no tiene reparto' ), 'el mensaje no pasa a la incidencia siguiente' );
		ok( (bool) notas( $p->get_id(), "Mensaje: MRW no tiene reparto en sábado" ), 'la nota del pedido guarda el mensaje' );
	}
	$nb = static fn( $s ) => str_replace( "\u{00A0}", ' ', $s );
	// Incidencia de un pedido urgente: «te devolvemos el envío» incluye la línea «Pedido urgente» (9,70 € + IVA)
	$u   = crear( $clientes['es-tu'] + array( 'clave' => 'incidencia-urgente', 'email' => 'incidencia-urgente@example.com', 'metodo' => 'envio', 'fecha' => DIA_ENVIO, 'kg' => 10, 'cantidad' => 1 ) );
	$fee = new WC_Order_Item_Fee();
	$fee->set_props( array( 'name' => 'Pedido urgente', 'tax_class' => '', 'tax_status' => 'taxable', 'total' => 9.70 ) );
	$fee->add_meta_data( '_dip_urgente', 1, true );
	$u->add_item( $fee );
	$u->calculate_totals( true );
	$u->set_status( 'processing' ); // como los demás (sale el de pedido recibido; se cuenta después)
	$u->save();
	$u     = wc_get_order( $u->get_id() );
	$antes = n_correos();
	ok( true === dip_aviso_enviar( $u, 'incidencia', array( 'forzar' => true, 'manual' => true ) ), 'incidencia urgente: sale', dip_aviso_ultimo_motivo() );
	$c = correos_a( 'incidencia-urgente@example.com', $antes );
	if ( ok( 1 === count( $c ), 'incidencia urgente: un email' ) ) {
		$urg = dip_avisos_suplemento_urgente( $u );
		ok( abs( $urg - 11.74 ) < 0.001, 'el suplemento «Pedido urgente» del pedido es 11,74 € (IVA incluido)', $urg );
		revisar( 'incidencia-urgente', $c[0], 'es', array( $nb( 'te devolvemos el envío y el suplemento «Pedido urgente»: ' . dip_avisos_euros( dip_avisos_importe_envio( $u ) + $urg ) ) ) );
	}
	// Incidencia de un sábado de los pedidos nuevos (el sábado va en la tarifa, sin línea de suplemento): «del envío que
	// pagaste», nunca «del suplemento»
	$s     = crear( $clientes['es-tu'] + array( 'clave' => 'incidencia-sabado-nuevo', 'email' => 'incidencia-sabado-nuevo@example.com', 'metodo' => 'envio', 'fecha' => DIA_SABADO, 'kg' => 10, 'cantidad' => 1 ) );
	$s->set_status( 'processing' );
	$s->save();
	$s     = wc_get_order( $s->get_id() );
	$antes = n_correos();
	ok( true === dip_aviso_enviar( $s, 'incidencia', array( 'forzar' => true, 'manual' => true ) ), 'incidencia sábado (tarifa nueva): sale', dip_aviso_ultimo_motivo() );
	$c = correos_a( 'incidencia-sabado-nuevo@example.com', $antes );
	if ( ok( 1 === count( $c ), 'incidencia sábado (tarifa nueva): un email' ) ) {
		$envio = dip_avisos_importe_envio( $s );
		$txt   = revisar( 'incidencia-sabado-nuevo', $c[0], 'es', array( $nb( 'te devolvemos el envío: ' . dip_avisos_euros( $envio ) ), 'del envío que pagaste nos quedamos los 10,00 € de la recogida en sábado y te devolvemos el resto: ' . $nb( dip_avisos_euros( $envio - 10 ) ) ) );
		ok( false === stripos( $txt, 'suplemento' ), 'incidencia sábado (tarifa nueva): no habla de ningún suplemento', $txt );
	}
	fin();
}

/* =========================================================
 * Paso 3: fallos, vigilancia, evento, completado, factura, reseña, PDF
 * ========================================================= */
if ( '3' === $paso ) {
	// Un email que falla: nota en el pedido y UN aviso al correo de avisos con todos los de la pasada
	foreach ( array( 'recogida-es-tu', 'recogida-ca-tu' ) as $k ) {
		$p = pedido( $k );
		$p->delete_meta_data( '_dip_aviso_recogida' );
		$p->update_meta_data( '_dip_fecha_entrega', '2026-12-09' );
		$p->save_meta_data();
	}
	$GLOBALS['dip_prueba_fallar'] = '/hoy lo recoges|avui la reculls/u';
	$antes = n_correos();
	$r     = dip_avisos_pasada( a_las( '2026-12-09', '08:00' ) );
	$GLOBALS['dip_prueba_fallar'] = '';
	$aviso = array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $antes ), static fn( $c ) => in_array( 'info@dryicepack.es', $c['para'], true ) ) );
	ok( 2 === count( $r['fallo'] ), 'pasada con dos emails que fallan: dos fallos', $r );
	ok( 1 === count( $aviso ) && 'Dryicepack: no han salido 2 avisos a clientes' === $aviso[0]['asunto'] && false !== strpos( $aviso[0]['cuerpo'], 'fallo simulado por la prueba' ) && false !== strpos( $aviso[0]['cuerpo'], 'Enviar aviso' ), 'un solo aviso al correo de avisos con los dos y la alternativa manual', array_column( $aviso, 'asunto' ) );
	if ( $aviso ) file_put_contents( '/salida/avisos/fallo-aviso-interno.txt', 'Asunto: ' . $aviso[0]['asunto'] . "\n\n" . $aviso[0]['cuerpo'] );
	ok( (bool) notas( pedido( 'recogida-es-tu' )->get_id(), 'No se ha podido enviar el aviso «Recordatorio de recogida»' ), 'nota en el pedido del fallo' );
	ok( '' === (string) pedido( 'recogida-es-tu' )->get_meta( '_dip_aviso_recogida' ), 'si falla, no se marca como enviado (se puede reintentar)' );
	// Fuera de una pasada (p. ej. «Envíos de hoy»): el aviso sale al final de la petición (shutdown) o con dip_avisos_avisar_fallos()
	$GLOBALS['dip_prueba_fallar'] = '/va de camino|ja és en camí/u';
	$p = pedido( 'sin-enviar' );
	$antes = n_correos();
	ok( false === dip_aviso_enviar( $p, 'camino' ) && 0 === strpos( dip_aviso_ultimo_motivo(), 'no ha salido el email' ), 'camino que falla: false y motivo' );
	$GLOBALS['dip_prueba_fallar'] = '';
	ok( false !== has_action( 'shutdown', 'dip_avisos_avisar_fallos' ), 'el aviso del fallo queda para el final de la petición' );
	dip_avisos_avisar_fallos();
	ok( 1 === count( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $antes ), static fn( $c ) => 'Dryicepack: no ha salido 1 aviso al cliente' === $c['asunto'] ) ), 'un aviso por el fallo suelto' );

	// Vigilancia: más de 26 h sin pasada → email (uno al día) y aviso en el escritorio; cuando vuelve, otro email
	$copia = get_option( 'dip_avisos_estado', array() );
	update_option( 'dip_avisos_estado', array( 'desde' => time() - 5 * DAY_IN_SECONDS, 'ok' => time() - 30 * HOUR_IN_SECONDS, 'avisado' => 0, 'error' => '' ), false );
	$antes = n_correos();
	dip_avisos_vigilar();
	dip_avisos_vigilar();
	$v = array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $antes ), static fn( $c ) => in_array( 'info@dryicepack.es', $c['para'], true ) ) );
	ok( 1 === count( $v ) && false !== strpos( $v[0]['asunto'], 'no están saliendo' ) && false !== strpos( $v[0]['cuerpo'], '30 horas' ) && false !== strpos( $v[0]['cuerpo'], 'Enviar aviso: hoy llega' ), 'más de 26 h sin pasada: un email (no dos) con la alternativa manual', array_column( $v, 'asunto' ) );
	if ( $v ) file_put_contents( '/salida/avisos/vigilancia-aviso-interno.txt', 'Asunto: ' . $v[0]['asunto'] . "\n\n" . $v[0]['cuerpo'] );
	wp_set_current_user( 1 );
	ob_start();
	dip_avisos_aviso_escritorio();
	$aviso_escritorio = ob_get_clean();
	wp_set_current_user( 0 );
	ok( false !== strpos( $aviso_escritorio, 'Avisos al cliente.' ) && false !== strpos( $aviso_escritorio, '30 horas' ), 'aviso en el escritorio', mb_substr( wp_strip_all_tags( $aviso_escritorio ), 0, 300 ) );
	update_option( 'dip_avisos_estado', array_merge( get_option( 'dip_avisos_estado' ), array( 'ok' => time() ) ), false );
	$antes = n_correos();
	dip_avisos_vigilar();
	ok( 1 === count( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $antes ), static fn( $c ) => false !== strpos( $c['asunto'], 'vuelven a salir' ) ) ), 'cuando vuelve a correr, un email «vuelven a salir»' );
	update_option( 'dip_avisos_estado', $copia, false );

	// Evento propio a las 8:00 de Madrid
	$t = wp_next_scheduled( DIP_AVISOS_EVENTO );
	ok( $t && '08:00' === ( new DateTimeImmutable( '@' . $t ) )->setTimezone( dip_zona_horaria() )->format( 'H:i' ), 'evento dip_avisos_diarios programado a las 8:00 de Madrid', $t ? wp_date( 'Y-m-d H:i', $t, dip_zona_horaria() ) : 'sin programar' );
	$prox = dip_avisos_proxima( a_las( '2027-03-27', '09:00' ) ); // el día antes del cambio de hora
	ok( '2027-03-28 08:00' === ( new DateTimeImmutable( '@' . $prox ) )->setTimezone( dip_zona_horaria() )->format( 'Y-m-d H:i' ), 'con el cambio de hora sigue a las 8:00 de Madrid' );
	ok( false !== strpos( dip_avisos_resumen_admin(), 'Próxima:' ), 'resumen en Ajustes', dip_avisos_resumen_admin() );

	// «Pedido completado» de WooCommerce: ya no sale; la factura simplificada (efectivo al recoger) y «¿Necesitas factura?», sí
	$p     = pedido( 'fs-recogida' );
	$antes = n_correos();
	$p->set_status( 'completed' );
	ok( '' === dip_fs_motivo( $p, 'auto' ), 'fs-recogida: le toca la factura simplificada al completarlo', dip_fs_motivo( $p, 'auto' ) );
	$p->save();
	$c  = correos_a( 'fs-recogida@example.com', $antes );
	$as = array_column( $c, 'asunto' );
	ok( ! preg_grep( '/complet|ha sido completado|is now complete/iu', $as ), 'al completar: no sale «Pedido completado»', $as );
	$fs = array_values( array_filter( $c, static fn( $x ) => false !== strpos( $x['asunto'], 'factura simplificada' ) ) );
	if ( ok( 1 === count( $fs ), 'al completar: sale la factura simplificada', $as ) ) {
		guardar( 'factura-simplificada-es-tu', $fs[0] );
		ok( false !== strpos( $fs[0]['cuerpo'], 'logo-dryicepack-email.png' ) && false !== strpos( texto_de( $fs[0]['cuerpo'] ), 'Base imponible' ) && false !== strpos( texto_de( $fs[0]['cuerpo'] ), 'Pídela aquí' ), 'factura simplificada con la envoltura común', mb_substr( texto_de( $fs[0]['cuerpo'] ), 0, 300 ) );
		ok( (bool) preg_grep( '/\.pdf$/', array_column( $fs[0]['adjuntos'], 'nombre' ) ), 'factura simplificada con su PDF adjunto' );
	}
	ok( ! WC()->mailer()->get_emails()['WC_Email_Customer_Completed_Order']->is_enabled(), 'WC_Email_Customer_Completed_Order desactivado' );
	$g  = pedido( 'grande' );
	$cg = array_values( array_filter( $GLOBALS['dip_prueba_correos'], static fn( $x ) => in_array( 'grande@example.com', $x['para'], true ) ) );
	// El de «¿Necesitas factura?» salió en el paso 1 (petición distinta): se comprueba con la marca del pedido y se vuelve a pintar
	ok( '' !== (string) $g->get_meta( '_dip_email_factura' ), 'pedido de más de 400 €: salió «¿Necesitas factura?» al pasar a Procesando' );
	$g->delete_meta_data( '_dip_email_factura' );
	$g->save_meta_data();
	$antes = n_correos();
	$g->set_status( 'completed' );
	$g->save();
	$cg = correos_a( 'grande@example.com', $antes );
	$nf = array_values( array_filter( $cg, static fn( $x ) => false !== strpos( $x['asunto'], 'necesitas factura' ) ) );
	if ( ok( 1 === count( $nf ) && 1 === count( $cg ), 'al completar: solo «¿Necesitas factura?» (sin «Pedido completado»)', array_column( $cg, 'asunto' ) ) ) {
		revisar( 'necesitas-factura', $nf[0], 'es', array( 'Rellenar datos de facturación' ) );
		guardar( 'necesitas-factura-es-tu', $nf[0] );
	}
	// Reseña: hereda la envoltura (se pinta sin enviar)
	if ( function_exists( 'dip_resenas_email' ) ) {
		foreach ( array( 'envio-es-tu' => 'es', 'envio-ca-usted' => 'ca', 'envio-en-tu' => 'en' ) as $k => $i ) {
			$e = dip_resenas_email( pedido( $k ) );
			$c   = array( 'para' => array( 'x' ), 'asunto' => $e['asunto'], 'cuerpo' => dip_email_en_linea( $e['html'] ), 'alt' => $e['texto'], 'tipo' => 'text/html', 'adjuntos' => array() );
			$txt = texto_de( $c['cuerpo'] );
			ok( false !== strpos( $c['cuerpo'], 'logo-dryicepack-email.png' ) && false !== strpos( $c['cuerpo'], 'v:roundrect' ) && false !== strpos( $txt, array( 'es' => 'Condiciones de venta', 'ca' => 'Condicions de venda', 'en' => 'Terms of sale' )[ $i ] ), "reseña {$k}: envoltura común con botón a prueba de Outlook y pie en su idioma" );
			ok( false !== strpos( $c['cuerpo'], esc_url( (string) dip_ajuste( 'resenas_url' ) ) ) && (bool) preg_match( '#dip_resenas_baja=#', $c['cuerpo'] ), "reseña {$k}: botón a Google y enlace de baja" );
			guardar( 'resena-' . $k, $c );
		}
	}
	// «Confirmar recogida» (inc/entrega.php): envoltura común en el idioma del pedido
	$p     = pedido( 'recogida-ca-tu' );
	$antes = n_correos();
	dip_enviar_confirmacion_recogida( $p );
	$c = correos_a( 'recogida-ca-tu@example.com', $antes );
	if ( ok( 1 === count( $c ), 'Confirmar recogida: un email' ) ) {
		ok( false !== strpos( $c[0]['cuerpo'], 'logo-dryicepack-email.png' ) && false !== strpos( texto_de( $c[0]['cuerpo'] ), 'Condicions de venda' ), 'Confirmar recogida: envoltura común con el pie en catalán' );
		guardar( 'confirmar-recogida-ca-tu', $c[0] );
	}

	// PDF de las condiciones: uno por idioma, en caché
	foreach ( array( 'es', 'ca', 'en' ) as $i ) {
		$ruta = dip_condiciones_pdf_ruta( $i );
		if ( ! ok( '' !== $ruta && is_readable( $ruta ), "PDF {$i}: existe", $ruta ) ) continue;
		$pdf = (string) file_get_contents( $ruta );
		ok( 0 === strpos( $pdf, '%PDF-1.4' ) && false !== strpos( $pdf, '%%EOF' ) && preg_match( '#/Count (\d+)#', $pdf, $m ) && (int) $m[1] >= 2, "PDF {$i}: PDF válido de varias páginas", $m[1] ?? '' );
		ok( basename( $ruta ) === dip_avisos_textos( $i )['pdf_archivo'] && false !== strpos( $ruta, 'dryicepack-condiciones/' . $i . '-' ), "PDF {$i}: nombre y carpeta de caché por idioma", $ruta );
		ok( false !== strpos( $pdf, '/Lang (' . array( 'es' => 'es-ES', 'ca' => 'ca-ES', 'en' => 'en-GB' )[ $i ] . ')' ), "PDF {$i}: idioma del documento" );
		copy( $ruta, '/salida/avisos/condiciones-' . $i . '.pdf' );
	}
	$antes_m = filemtime( dip_condiciones_pdf_ruta( 'es' ) );
	ok( $antes_m === filemtime( dip_condiciones_pdf_ruta( 'es' ) ), 'PDF: se reutiliza la copia en caché' );

	// Para el paso 4: «va de camino» desactivado
	$a = (array) get_option( 'dip_ajustes', array() );
	$a['aviso_camino'] = 0;
	update_option( 'dip_ajustes', $a, true );
	fin();
}

/* =========================================================
 * Paso 4: aviso desactivado y limpieza
 * ========================================================= */
if ( '4' === $paso ) {
	$p = pedido( 'envio-en-usted' );
	ok( false === dip_avisos_activo( 'camino' ), 'el ajuste desactiva «va de camino»' );
	ok( false === dip_aviso_enviar( $p, 'camino', array( 'mrw' => '1' ) ) && false !== strpos( dip_aviso_ultimo_motivo(), 'desactivado' ), 'desactivado: no sale', dip_aviso_ultimo_motivo() );
	$antes = n_correos();
	do_action( 'woocommerce_order_action_dip_aviso_camino', $p );
	ok( 1 === count( correos_a( 'envio-en-usted@example.com', $antes ) ), 'desactivado: a mano (Acciones del pedido) sí sale' );
	$acc = apply_filters( 'woocommerce_order_actions', array(), $p );
	ok( isset( $acc['dip_aviso_camino'], $acc['dip_aviso_hoy'], $acc['dip_aviso_incidencia'] ) && ! isset( $acc['dip_aviso_recogida'] ), 'acciones del pedido con envío', array_keys( $acc ) );
	$acc = apply_filters( 'woocommerce_order_actions', array(), pedido( 'recogida-es-tu' ) );
	ok( isset( $acc['dip_aviso_recogida'] ) && ! isset( $acc['dip_aviso_camino'] ), 'acciones del pedido de recogida', array_keys( $acc ) );
	// Asuntos (todos de 45 caracteres o menos): se imprimen para el resumen
	echo 'Pedidos de prueba borrados: ' . limpiar() . "\n";
	ok( 1 === (int) ( ( (array) get_option( 'dip_ajustes', array() ) )['aviso_camino'] ?? 1 ), 'ajustes restaurados' );
	fin();
}

echo "Paso desconocido\n";
fin();
