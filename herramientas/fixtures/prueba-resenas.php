<?php
/**
 * Pruebas de inc/resenas.php (email «¿Qué tal llegó tu hielo seco?») en el WordPress de prueba (puerto 9500).
 *
 * herramientas/probar-resenas.mjs copia este archivo a plugin/dryicepack-tienda/zz-prueba-resenas.php, lo llama por
 * pasos y lo borra siempre (si se quedara allí iría al .zip). Cada paso es una petición nueva porque dip_ajuste()
 * guarda los ajustes en memoria durante toda la petición.
 *  ?paso=1        Email automático desactivado (lo normal fuera de producción): la tarea diaria no manda nada.
 *                 Crea los pedidos de prueba, prepara los del checkout y activa el ajuste para el paso 2.
 *  ?paso=2        Activado: quién lo recibe y quién no, idioma, tú/usted, enlaces, una sola vez, bloque del email de
 *                 «completado», bloqueo de pasadas a la vez, fallo con un solo aviso y acción manual.
 *  ?paso=estado   &email=… ¿está ese email de baja? (para la prueba de la página de baja por HTTP)
 *  ?paso=3        Después de la baja por HTTP (con el ajuste desactivado): huella guardada sin el email, bloquea la
 *                 pasada, la acción manual y el bloque de «completado».
 *  ?paso=4        Sin enlace de reseñas no sale nada. Deja todo como estaba.
 *  ?paso=limpiar  Solo deja todo como estaba (si algo se cortó).
 * No envía emails: un PHPMailer de prueba los guarda (y puede simular un fallo). Los HTML van a /salida/resenas/.
 */
require dirname( __DIR__, 3 ) . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
if ( ! function_exists( 'dip_resenas_pasada' ) || ! function_exists( 'wc_get_orders' ) ) {
	exit( "FALLO: falta WooCommerce o inc/resenas.php\n0 comprobaciones, 1 fallos\n" );
}

/* ---------- Correo de prueba: guarda lo que se enviaría (y falla a propósito si se pide) ---------- */
require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';

class Dip_Prueba_Correo extends WP_PHPMailer {
	public function send() {
		$cabeceras = array();
		foreach ( $this->getCustomHeaders() as $c ) $cabeceras[ $c[0] ] = $c[1];
		// Fallo simulado solo en las peticiones de reseña: el aviso al correo de avisos sí sale
		if ( ! empty( $GLOBALS['dip_prueba_fallar'] ) && isset( $cabeceras['List-Unsubscribe-Post'] ) ) {
			throw new PHPMailer\PHPMailer\Exception( 'fallo simulado por la prueba' );
		}
		$GLOBALS['dip_prueba_correos'][] = array(
			'para'      => array_map( static fn( $a ) => $a[0], $this->getToAddresses() ),
			'asunto'    => $this->Subject,
			'cuerpo'    => $this->Body,
			'alt'       => $this->AltBody,
			'de'        => $this->From,
			'de_nombre' => $this->FromName,
			'responder' => array_map( static fn( $a ) => strtolower( $a[0] ), array_values( $this->getReplyToAddresses() ) ),
			'tipo'      => $this->ContentType,
			'cabeceras' => $cabeceras,
		);
		return true;
	}
}
$GLOBALS['phpmailer']          = new Dip_Prueba_Correo( true );
$GLOBALS['dip_prueba_correos'] = array();
$GLOBALS['dip_prueba_errores'] = array();
$GLOBALS['dip_prueba_fallar']  = false;
add_action( 'wp_mail_failed', static function ( $error ) {
	$GLOBALS['dip_prueba_errores'][] = $error instanceof WP_Error ? $error->get_error_message() : 'error';
} );
// En Playground el remitente por defecto de wp_mail es wordpress@127.0.0.1 y PHPMailer lo rechaza (en producción lo pone
// WP Mail SMTP). Los emails de WooCommerce ponen el suyo con prioridad 10 y mandan sobre este.
add_filter( 'wp_mail_from', static fn() => 'wordpress@dryicepack.test', 1 );

/* ---------- Herramientas ---------- */
$fallos  = 0;
$pruebas = 0;
$ok      = static function ( $cond, $msg, $detalle = null ) use ( &$fallos, &$pruebas ) {
	$pruebas++;
	if ( $cond ) return true;
	$fallos++;
	echo 'FALLO: ' . $msg . ( null === $detalle ? '' : ' → ' . ( is_string( $detalle ) ? $detalle : wp_json_encode( $detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) . "\n";
	return false;
};
$fin = static function () use ( &$fallos, &$pruebas ) {
	echo "{$pruebas} comprobaciones, {$fallos} fallos\n";
	exit;
};

/** Peticiones de reseña capturadas desde la posición $desde. */
function dip_prueba_resenas_capturadas( $desde = 0 ) {
	return array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $desde ), static fn( $c ) => isset( $c['cabeceras']['List-Unsubscribe-Post'] ) ) );
}
function dip_prueba_avisos_capturados( $desde = 0 ) {
	return array_values( array_filter( array_slice( $GLOBALS['dip_prueba_correos'], $desde ), static fn( $c ) => 0 === strpos( (string) $c['asunto'], 'Dryicepack: no ha' ) ) );
}
function dip_prueba_texto( $html ) {
	return html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}
function dip_prueba_estado() {
	$e = get_option( 'dip_prueba_resenas' );
	return is_array( $e ) ? $e : null;
}
function dip_prueba_guardar_estado( array $e ) {
	update_option( 'dip_prueba_resenas', $e, false );
}

/* Sin efectos secundarios al crear y cambiar pedidos: ni emails de WooCommerce, ni factura simplificada, ni stock. */
function dip_prueba_silenciar() {
	foreach ( array_keys( $GLOBALS['wp_filter'] ) as $gancho ) {
		if ( 0 === strpos( (string) $gancho, 'woocommerce_order_status_' ) || in_array( $gancho, array( 'woocommerce_new_order', 'woocommerce_update_order', 'woocommerce_payment_complete' ), true ) ) {
			remove_all_actions( $gancho );
		}
	}
}
dip_prueba_silenciar();

/** Fecha 'Y-m-d' a $n días de hoy (Madrid). */
function dip_prueba_dia( $n ) {
	return dip_resenas_hoy()->modify( ( $n >= 0 ? '+' : '' ) . (int) $n . ' days' )->format( 'Y-m-d' );
}

function dip_prueba_crear( array $d ) {
	$v = dip_variaciones_de_kg( 10 );
	$p = new WC_Order();
	$p->set_created_via( 'prueba-resenas' );
	$p->set_billing_first_name( $d['nombre'] ?? '' );
	$p->set_billing_email( $d['email'] ?? '' );
	$p->set_billing_country( 'ES' );
	$p->set_billing_postcode( '08013' );
	$p->set_billing_city( 'Barcelona' );
	if ( $v ) $p->add_product( $v[0], (int) ( $d['cantidad'] ?? 1 ) );
	$p->update_meta_data( '_dip_fecha_entrega', $d['fecha'] );
	$p->update_meta_data( '_dip_metodo_entrega', $d['metodo'] ?? 'envio' );
	$p->update_meta_data( '_dip_idioma', $d['idioma'] ?? 'es' );
	$p->update_meta_data( '_dip_empresa', empty( $d['empresa'] ) ? 0 : 1 );
	$p->update_meta_data( '_dip_canal', $d['canal'] ?? 'web' );
	if ( isset( $d['kg'] ) ) $p->update_meta_data( '_dip_kg', $d['kg'] );
	if ( isset( $d['pedida'] ) ) $p->update_meta_data( '_dip_resena_pedida', $d['pedida'] );
	$p->update_meta_data( '_dip_prueba_resenas', 1 );
	$p->set_date_created( time() + (int) ( $d['creado'] ?? 0 ) );
	$p->calculate_totals( false );
	$p->set_status( $d['estado'] ?? 'processing' );
	$p->save();
	return $p->get_id();
}

/** Notas del pedido que contienen $texto (más recientes primero). */
function dip_prueba_notas( $id, $texto ) {
	$r = array();
	foreach ( wc_get_order_notes( array( 'order_id' => $id ) ) as $n ) {
		if ( false !== mb_stripos( (string) $n->content, $texto ) ) $r[] = (string) $n->content;
	}
	return $r;
}

function dip_prueba_cambiar_ajustes( array $cambios ) {
	$a = wp_parse_args( (array) get_option( 'dip_ajustes', array() ), dip_ajustes_por_defecto() );
	update_option( 'dip_ajustes', array_merge( $a, $cambios ) );
}

/** Deja el servidor como estaba: borra los pedidos de prueba, restaura los del checkout y los ajustes. */
function dip_prueba_limpiar() {
	$e = dip_prueba_estado();
	if ( ! $e ) return 'nada que limpiar';
	$borrados = 0;
	$ids      = wc_get_orders( array(
		'type'       => 'shop_order',
		'limit'      => -1,
		'return'     => 'ids',
		'meta_query' => array( array( 'key' => '_dip_prueba_resenas', 'compare' => 'EXISTS' ) ),
	) );
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( $p && $p->get_meta( '_dip_prueba_resenas' ) ) {
			$p->delete( true );
			$borrados++;
		}
	}
	$quitar_notas = static function ( $id ) use ( $e ) {
		foreach ( wc_get_order_notes( array( 'order_id' => $id ) ) as $n ) {
			$cuando = $n->date_created ? $n->date_created->getTimestamp() : 0;
			if ( $cuando >= (int) $e['inicio'] - 5 && false !== mb_stripos( (string) $n->content, 'reseña' ) ) wc_delete_order_note( $n->id );
		}
	};
	foreach ( (array) $e['checkout'] as $id => $orig ) {
		$p = wc_get_order( $id );
		if ( ! $p ) continue;
		$p->update_meta_data( '_dip_fecha_entrega', $orig['fecha'] );
		$p->set_billing_email( $orig['email'] );
		$p->delete_meta_data( '_dip_resena_pedida' );
		$p->set_status( $orig['estado'] );
		$p->save();
		$quitar_notas( $id );
	}
	foreach ( (array) $e['ajenos'] as $id ) {
		$p = wc_get_order( $id );
		if ( ! $p ) continue;
		$p->delete_meta_data( '_dip_resena_pedida' );
		$p->save_meta_data();
		$quitar_notas( $id );
	}
	foreach ( array( 'dip_ajustes' => $e['ajustes'], 'dip_resenas_bajas' => $e['bajas'] ) as $opcion => $orig ) {
		if ( $orig['existe'] ) update_option( $opcion, $orig['valor'], 'dip_ajustes' === $opcion ? null : false );
		else delete_option( $opcion );
	}
	delete_option( 'dip_resenas_en_marcha' );
	delete_option( 'dip_prueba_resenas' );
	return "{$borrados} pedidos de prueba borrados; pedidos del checkout, ajustes y bajas como estaban";
}

$paso = isset( $_GET['paso'] ) ? sanitize_key( wp_unslash( $_GET['paso'] ) ) : '';
$url_resenas = 'https://g.page/r/CTbZyC_fpj4LECE/review';

/* =========================================================
 * Paso «limpiar» y «estado»
 * ========================================================= */
if ( 'limpiar' === $paso ) {
	echo 'Limpieza: ' . dip_prueba_limpiar() . "\n";
	$fin();
}
if ( 'estado' === $paso ) {
	$email = isset( $_GET['email'] ) ? sanitize_email( wp_unslash( $_GET['email'] ) ) : '';
	echo 'BAJA: ' . ( dip_resenas_de_baja( $email ) ? 'si' : 'no' ) . "\n";
	echo 'BAJAS: ' . count( dip_resenas_bajas() ) . "\n";
	$fin();
}

/* =========================================================
 * Paso 1: ajuste desactivado → nada. Preparar los pedidos.
 * ========================================================= */
if ( '1' === $paso ) {
	echo "== Paso 1: ajustes y email automático desactivado\n";
	if ( dip_prueba_estado() ) echo 'Restos de una prueba anterior: ' . dip_prueba_limpiar() . "\n";

	// Funciones puras
	$ok( 'miércoles 11 de noviembre' === dip_fecha_larga( '2026-11-11', 'es' ), 'fecha larga es', dip_fecha_larga( '2026-11-11', 'es' ) );
	$ok( 'dimecres 11 de novembre' === dip_fecha_larga( '2026-11-11', 'ca' ), 'fecha larga ca', dip_fecha_larga( '2026-11-11', 'ca' ) );
	$ok( 'Wednesday 11 November' === dip_fecha_larga( '2026-11-11', 'en' ), 'fecha larga en', dip_fecha_larga( '2026-11-11', 'en' ) );
	$ok( '10' === dip_resenas_numero( 10 ) && '2,5' === dip_resenas_numero( 2.5 ) && '2.5' === dip_resenas_numero( 2.5, 'en' ) && '100' === dip_resenas_numero( 100 ) && '250' === dip_resenas_numero( 250 ), 'formato de los kilos (10 · 2,5 · 2.5 · 100 · 250)' );
	$ok( 4 === count( dip_resenas_fechas_ventana() ) && dip_prueba_dia( -1 ) === dip_resenas_fechas_ventana()[0] && dip_prueba_dia( -4 ) === dip_resenas_fechas_ventana()[3], 'la ventana es ayer y los 3 días anteriores', dip_resenas_fechas_ventana() );
	$h1 = dip_resenas_huella( 'Cliente@Example.com ' );
	$ok( $h1 === dip_resenas_huella( 'cliente@example.com' ) && preg_match( '/^[a-f0-9]{32}$/', $h1 ) && false === strpos( $h1, 'cliente' ), 'la huella no distingue mayúsculas ni espacios y no contiene el email' );
	$ok( $h1 !== dip_resenas_huella( 'otro@example.com' ), 'dos emails distintos dan huellas distintas' );

	// Ajustes: de serie solo en producción; casilla que se guarda
	$ok( 0 === dip_ajustes_por_defecto()['resenas_email'], 'en la copia de prueba el email automático viene desactivado' );
	$prod = static fn() => 'https://dryicepack.es';
	add_filter( 'home_url', $prod );
	$ok( 1 === dip_ajustes_por_defecto()['resenas_email'], 'en dryicepack.es viene activado de serie' );
	remove_filter( 'home_url', $prod );
	$ok( 1 === dip_sanear_ajustes( array( 'resenas_email' => '1' ) )['resenas_email'] && 0 === dip_sanear_ajustes( array() )['resenas_email'], 'dip_sanear_ajustes guarda la casilla (1) y la desmarcada (0)' );
	$ok( $url_resenas === dip_ajuste( 'resenas_url' ), 'el enlace de reseñas es el de serie', dip_ajuste( 'resenas_url' ) );
	if ( ! $ok( ! dip_ajuste( 'resenas_email' ), 'al empezar, el email automático está desactivado en este servidor' ) ) $fin();

	// Guardar lo que hay que restaurar
	$aj = get_option( 'dip_ajustes', null );
	$bj = get_option( 'dip_resenas_bajas', null );
	$e  = array(
		'inicio'   => time(),
		'ajustes'  => array( 'existe' => null !== $aj, 'valor' => $aj ),
		'bajas'    => array( 'existe' => null !== $bj, 'valor' => $bj ),
		'checkout' => array(),
		'ajenos'   => array(),
		'ids'      => array(),
	);
	dip_prueba_guardar_estado( $e );

	// Pedidos de prueba
	$def = array(
		'A'   => array( 'email' => 'resenas-a@example.com', 'nombre' => 'Laura García Pons', 'fecha' => dip_prueba_dia( -3 ), 'idioma' => 'es', 'kg' => 10, 'creado' => -3600 ),
		'B'   => array( 'email' => 'resenas-b@example.com', 'nombre' => 'Jordi Puig', 'fecha' => dip_prueba_dia( -2 ), 'idioma' => 'ca', 'metodo' => 'recogida', 'kg' => 3, 'estado' => 'completed' ),
		'C'   => array( 'email' => 'resenas-c@example.com', 'nombre' => 'Emma Clarke', 'fecha' => dip_prueba_dia( -4 ), 'idioma' => 'en', 'empresa' => 1, 'kg' => 20 ),
		'D'   => array( 'email' => 'resenas-d@example.com', 'nombre' => 'Marta Soler Vidal', 'fecha' => dip_prueba_dia( -2 ), 'idioma' => 'es', 'metodo' => 'recogida', 'empresa' => 1, 'kg' => 15, 'estado' => 'completed' ),
		'E'   => array( 'email' => 'resenas-e@example.com', 'nombre' => 'Núria Ferrer', 'fecha' => dip_prueba_dia( -3 ), 'idioma' => 'ca', 'canal' => 'cuenta', 'cantidad' => 2 ), // sin _dip_kg: 2 × 10 kg
		'E2'  => array( 'email' => 'resenas-e2@example.com', 'nombre' => '', 'fecha' => dip_prueba_dia( -1 ), 'idioma' => 'en', 'metodo' => 'recogida', 'kg' => 10 ),
		'F'   => array( 'email' => 'resenas-f@example.com', 'nombre' => 'Cancelado', 'fecha' => dip_prueba_dia( -1 ), 'estado' => 'cancelled', 'kg' => 10 ),
		'G'   => array( 'email' => 'resenas-g@example.com', 'nombre' => 'Hace Cinco', 'fecha' => dip_prueba_dia( -5 ), 'kg' => 10 ),
		'H'   => array( 'email' => 'resenas-h@example.com', 'nombre' => 'En Espera', 'fecha' => dip_prueba_dia( -1 ), 'estado' => 'on-hold', 'kg' => 10 ),
		'H2'  => array( 'email' => 'resenas-h2@example.com', 'nombre' => 'Reembolsado', 'fecha' => dip_prueba_dia( -1 ), 'estado' => 'refunded', 'kg' => 10 ),
		'H3'  => array( 'email' => 'resenas-h3@example.com', 'nombre' => 'Pendiente', 'fecha' => dip_prueba_dia( -1 ), 'estado' => 'pending', 'kg' => 10 ),
		'H4'  => array( 'email' => 'resenas-h4@example.com', 'nombre' => 'Fallido', 'fecha' => dip_prueba_dia( -1 ), 'estado' => 'failed', 'kg' => 10 ),
		'I'   => array( 'email' => 'resenas-a@example.com', 'nombre' => 'Laura García Pons', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 3, 'creado' => -60 ), // mismo email que A
		'J'   => array( 'email' => 'resenas-j@example.com', 'nombre' => 'De Baja', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ),
		'K'   => array( 'email' => 'resenas-k@example.com', 'nombre' => 'Hoy', 'fecha' => dip_prueba_dia( 0 ), 'kg' => 10 ),
		'L'   => array( 'email' => '', 'nombre' => 'Sin Email', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ),
		'N'   => array( 'email' => 'resenas-n@example.com', 'nombre' => 'Ya Pedida', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10, 'pedida' => dip_resenas_ahora( '-1 day' ) ),
		'O1'  => array( 'email' => 'resenas-o1@example.com', 'nombre' => 'Cien Dias', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ),
		'O1x' => array( 'email' => 'resenas-o1@example.com', 'nombre' => 'Cien Dias', 'fecha' => dip_prueba_dia( -101 ), 'kg' => 10, 'estado' => 'completed', 'pedida' => dip_resenas_ahora( '-100 days' ), 'creado' => -101 * DAY_IN_SECONDS ),
		'O2'  => array( 'email' => 'resenas-o2@example.com', 'nombre' => 'Pau', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ),
		'O2x' => array( 'email' => 'resenas-o2@example.com', 'nombre' => 'Pau', 'fecha' => dip_prueba_dia( -201 ), 'kg' => 10, 'estado' => 'completed', 'pedida' => dip_resenas_ahora( '-200 days' ), 'creado' => -201 * DAY_IN_SECONDS ),
		'T'   => array( 'email' => 'resenas-t@example.com', 'nombre' => 'Tarde', 'fecha' => dip_prueba_dia( -10 ), 'kg' => 10, 'estado' => 'completed' ),
		'FUT' => array( 'email' => 'resenas-fut@example.com', 'nombre' => 'Futuro', 'fecha' => dip_prueba_dia( 2 ), 'kg' => 10 ),
	);
	foreach ( $def as $letra => $d ) $e['ids'][ $letra ] = dip_prueba_crear( $d );
	$e['def'] = $def;
	echo count( $e['ids'] ) . " pedidos de prueba creados\n";

	// J ya estaba de baja
	dip_resenas_dar_de_baja( dip_resenas_huella( 'resenas-j@example.com' ) );

	// Pedidos reales del checkout (probar-pedido.mjs normal y recogida, de las últimas 3 horas): entrega ayer y Procesando
	$reales = wc_get_orders( array( 'type' => 'shop_order', 'billing_email' => 'prueba@example.com', 'limit' => 20, 'orderby' => 'date', 'order' => 'DESC', 'date_created' => '>' . ( time() - 3 * HOUR_IN_SECONDS ) ) );
	$elegidos = array();
	foreach ( $reales as $p ) {
		$m = 'recogida' === $p->get_meta( '_dip_metodo_entrega' ) ? 'recogida' : 'envio';
		if ( isset( $elegidos[ $m ] ) || $p->get_meta( '_dip_resena_pedida' ) ) continue;
		$elegidos[ $m ] = $p;
	}
	foreach ( $elegidos as $m => $p ) {
		$e['checkout'][ $p->get_id() ] = array( 'fecha' => (string) $p->get_meta( '_dip_fecha_entrega' ), 'estado' => $p->get_status(), 'email' => $p->get_billing_email(), 'metodo' => $m );
		$p->update_meta_data( '_dip_fecha_entrega', dip_prueba_dia( -1 ) );
		if ( 'recogida' === $m ) $p->set_billing_email( 'prueba-recogida@example.com' ); // un email por pedido: si no, el segundo cae en la regla de 180 días
		$p->set_status( 'processing' );
		$p->save();
		echo 'Pedido del checkout #' . $p->get_id() . " ({$m}, estaba «" . $e['checkout'][ $p->get_id() ]['estado'] . '», entrega ' . $e['checkout'][ $p->get_id() ]['fecha'] . ") → entrega ayer, Procesando\n";
	}
	$ok( 2 === count( $elegidos ), 'hay un pedido de envío y uno de recogida hechos por el checkout en las últimas 3 horas (node herramientas/probar-pedido.mjs normal | recogida)', array_keys( $elegidos ) );

	// Otros pedidos que ya hubiera en el servidor dentro de la ventana: se apartan (se marcan como ya pedidos en 2000)
	$todos = wc_get_orders( array(
		'type'       => 'shop_order',
		'status'     => array( 'wc-processing', 'wc-completed' ),
		'limit'      => -1,
		'return'     => 'ids',
		'meta_query' => array( 'relation' => 'AND', array( 'key' => '_dip_fecha_entrega', 'value' => dip_resenas_fechas_ventana(), 'compare' => 'IN' ), array( 'key' => '_dip_resena_pedida', 'compare' => 'NOT EXISTS' ) ),
	) );
	foreach ( $todos as $id ) {
		if ( in_array( $id, $e['ids'], true ) || isset( $e['checkout'][ $id ] ) ) continue;
		$p = wc_get_order( $id );
		$p->update_meta_data( '_dip_resena_pedida', '2000-01-01 00:00:00' );
		$p->save_meta_data();
		$e['ajenos'][] = $id;
	}
	echo count( $e['ajenos'] ) . " pedidos anteriores del servidor apartados de la prueba\n";
	dip_prueba_guardar_estado( $e );

	// Con el ajuste desactivado, la tarea diaria no pide ninguna reseña
	do_action( 'dip_tarea_diaria' );
	$ok( 0 === count( dip_prueba_resenas_capturadas() ), 'con el email automático desactivado, la tarea diaria no manda ninguna petición', count( dip_prueba_resenas_capturadas() ) );
	$ok( '' === (string) wc_get_order( $e['ids']['A'] )->get_meta( '_dip_resena_pedida' ), 'con el ajuste desactivado no se marca ningún pedido' );

	dip_prueba_cambiar_ajustes( array( 'resenas_email' => 1 ) );
	echo "Ajuste activado para el paso 2\n";
	$fin();
}

$e = dip_prueba_estado();
if ( ! $e ) {
	echo "FALLO: no hay estado de la prueba: lanza antes el paso 1\n";
	$fallos++;
	$fin();
}
$ids = $e['ids'];
$ped = static fn( $letra ) => wc_get_order( $ids[ $letra ] );

/* =========================================================
 * Paso 2: ajuste activado
 * ========================================================= */
if ( '2' === $paso ) {
	echo "== Paso 2: email automático activado\n";
	if ( ! $ok( 1 === (int) dip_ajuste( 'resenas_email' ), 'el ajuste está activado' ) ) $fin();

	// Bloque del email de «completado» antes de la pasada: A y K van a recibir el email → sin bloque; T (hace 10 días) → con bloque
	$ok( ! dip_resenas_bloque_completado( $ped( 'A' ) ), 'completado: sin bloque de reseña si el email del día siguiente todavía va a llegar (entrega hace 3 días)' );
	$ok( ! dip_resenas_bloque_completado( $ped( 'K' ) ), 'completado: sin bloque si la entrega es hoy (el email sale mañana)' );
	$ok( dip_resenas_bloque_completado( $ped( 'T' ) ), 'completado: con bloque si el pedido ya no va a recibir el email (entrega hace 10 días)' );
	$ok( ! dip_resenas_bloque_completado( $ped( 'J' ) ), 'completado: sin bloque para un email dado de baja' );

	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'dip_tarea_diaria' );
	$enviadas = dip_prueba_resenas_capturadas( $antes );
	$por_email = array();
	foreach ( $enviadas as $c ) foreach ( $c['para'] as $a ) $por_email[ strtolower( $a ) ][] = $c;

	// Quién la recibe
	$checkout = array();
	foreach ( (array) $e['checkout'] as $id => $orig ) $checkout[ $orig['metodo'] ] = wc_get_order( $id );
	$si = array( 'A', 'B', 'C', 'D', 'E', 'E2', 'O2' );
	$no = array( 'F' => 'solo se pide en Procesando', 'G' => 'no fue entre ayer', 'H' => 'solo se pide en Procesando', 'H2' => 'solo se pide en Procesando', 'H3' => 'solo se pide en Procesando', 'H4' => 'solo se pide en Procesando', 'I' => 'ya se le pidió opinión', 'J' => 'dado de baja', 'K' => 'no fue entre ayer', 'L' => 'email válido', 'N' => 'ya se pidió la reseña', 'O1' => 'ya se le pidió opinión', 'T' => 'no fue entre ayer', 'FUT' => 'no fue entre ayer' );
	foreach ( $si as $l ) {
		$p = $ped( $l );
		$ok( 1 === count( $por_email[ $p->get_billing_email() ] ?? array() ), "{$l}: recibe una petición", count( $por_email[ $p->get_billing_email() ] ?? array() ) );
		$ok( (bool) preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $p->get_meta( '_dip_resena_pedida' ) ), "{$l}: _dip_resena_pedida guarda la fecha", (string) $p->get_meta( '_dip_resena_pedida' ) );
		$ok( (bool) dip_prueba_notas( $p->get_id(), 'Petición de reseña enviada por email a ' . $p->get_billing_email() . ' (automática' ), "{$l}: nota en el pedido" );
	}
	foreach ( $checkout as $m => $p ) {
		$ok( 1 === count( $por_email[ $p->get_billing_email() ] ?? array() ), "checkout {$m} #" . $p->get_id() . ': recibe una petición' );
		$ok( '' !== (string) $p->get_meta( '_dip_resena_pedida' ), "checkout {$m}: queda marcado" );
	}
	foreach ( $no as $l => $motivo ) {
		$p       = $ped( $l );
		$email_l = strtolower( (string) $p->get_billing_email() );
		// I comparte email con A: ese email recibe una sola petición (la de A, el pedido más antiguo)
		if ( 'I' !== $l ) $ok( 0 === count( $por_email[ $email_l ] ?? array() ), "{$l}: no recibe la petición", count( $por_email[ $email_l ] ?? array() ) );
		if ( 'N' !== $l ) $ok( '' === (string) $p->get_meta( '_dip_resena_pedida' ), "{$l}: no queda marcado" );
		$m = dip_resenas_motivo_no( $p );
		$ok( false !== mb_strpos( $m, $motivo ), "{$l}: motivo «{$motivo}»", $m );
	}
	$repetidos = array_filter( $por_email, static fn( $l ) => count( $l ) > 1 );
	$ok( ! $repetidos, 'ningún email recibe dos peticiones en la misma pasada', array_keys( $repetidos ) );
	$ok( count( $enviadas ) === count( $si ) + count( $checkout ), 'número total de peticiones de la pasada', count( $enviadas ) );

	// Contenido de cada email
	$esperado = array(
		'A'  => array( '¿Qué tal llegó tu hielo seco?', 'Hola, Laura: el ' . dip_fecha_larga( dip_prueba_dia( -3 ), 'es' ) . " te entregamos 10\u{00A0}kg de hielo seco.", '¿Nos cuentas qué tal en Google? Son 30 segundos y ayuda a otros a encontrarnos.', 'Escribir reseña', 'Y si algo no fue como esperabas, responde a este email y lo arreglamos.', '¿No quieres que te pidamos opinión?', 'Darte de baja', 'es', false ),
		'B'  => array( 'Com et va arribar el gel sec?', 'Hola, Jordi: el ' . dip_fecha_larga( dip_prueba_dia( -2 ), 'ca' ) . " vas recollir 3\u{00A0}kg de gel sec.", 'Ens expliques a Google com et va anar? Són 30 segons i ajuda altres persones a trobar-nos.', 'Escriure una ressenya', 'I si alguna cosa no va anar com esperaves, respon aquest correu i ho arreglem.', 'No vols que et demanem l\'opinió?', 'Donar-te de baixa', 'ca', false ),
		'C'  => array( 'How did your dry ice arrive?', 'Hello Emma Clarke, on ' . dip_fecha_larga( dip_prueba_dia( -4 ), 'en' ) . " we delivered 20\u{00A0}kg of dry ice to you.", 'Would you tell us how it went on Google? It takes 30 seconds and helps others find us.', 'Write a review', 'And if anything was not as you expected, reply to this email and we will put it right.', 'Would you rather we did not ask for your feedback?', 'Unsubscribe', 'en', true ),
		'D'  => array( '¿Qué tal llegó su hielo seco?', 'Hola, Marta Soler Vidal: el ' . dip_fecha_larga( dip_prueba_dia( -2 ), 'es' ) . " recogió 15\u{00A0}kg de hielo seco.", '¿Nos cuenta qué tal en Google? Son 30 segundos y ayuda a otros a encontrarnos.', 'Escribir reseña', 'Y si algo no fue como esperaba, responda a este email y lo arreglamos.', '¿No quiere que le pidamos opinión?', 'Darse de baja', 'es', true ),
		'E'  => array( 'Com li va arribar el gel sec?', 'Hola, Núria Ferrer: el ' . dip_fecha_larga( dip_prueba_dia( -3 ), 'ca' ) . " li vam entregar 20\u{00A0}kg de gel sec.", 'Ens explica a Google com li va anar? Són 30 segons i ajuda altres persones a trobar-nos.', 'Escriure una ressenya', 'I si alguna cosa no va anar com esperava, respongui aquest correu i ho arreglem.', 'No vol que li demanem l\'opinió?', 'Donar-se de baixa', 'ca', true ),
		'E2' => array( 'How did your dry ice arrive?', 'On ' . dip_fecha_larga( dip_prueba_dia( -1 ), 'en' ) . " you collected 10\u{00A0}kg of dry ice.", 'Would you tell us how it went on Google?', 'Write a review', 'reply to this email and we will put it right.', 'Would you rather we did not ask for your feedback?', 'Unsubscribe', 'en', false ),
		'O2' => array( '¿Qué tal llegó tu hielo seco?', 'Hola, Pau: el ' . dip_fecha_larga( dip_prueba_dia( -1 ), 'es' ) . " te entregamos 10\u{00A0}kg de hielo seco.", '¿Nos cuentas qué tal en Google?', 'Escribir reseña', 'responde a este email', '¿No quieres que te pidamos opinión?', 'Darte de baja', 'es', false ),
	);
	foreach ( $checkout as $m => $p ) {
		$kg = dip_resenas_numero( dip_resenas_kg( $p ) ) . "\u{00A0}kg";
		$esperado[ 'checkout-' . $m ] = array( '¿Qué tal llegó tu hielo seco?', 'Hola, Laura: el ' . dip_fecha_larga( dip_prueba_dia( -1 ), 'es' ) . ( 'recogida' === $m ? " recogiste {$kg} de hielo seco." : " te entregamos {$kg} de hielo seco." ), '¿Nos cuentas qué tal en Google?', 'Escribir reseña', 'responde a este email', '¿No quieres que te pidamos opinión?', 'Darte de baja', 'es', false );
		$ok( (float) $p->get_meta( '_dip_kg' ) > 0, "checkout {$m}: el pedido trae _dip_kg del checkout", $p->get_meta( '_dip_kg' ) );
	}
	$tienda    = WC()->mailer()->get_emails()['WC_Email_Customer_Completed_Order']; // remitente de los emails de la tienda
	$de        = $tienda->get_from_address();
	$de_nombre = $tienda->get_from_name();
	$urls_baja = array();
	$carpeta   = is_dir( '/salida' ) ? '/salida/resenas' : '';
	if ( $carpeta ) wp_mkdir_p( $carpeta );
	foreach ( $esperado as $clave => $x ) {
		$p     = 0 === strpos( $clave, 'checkout-' ) ? $checkout[ substr( $clave, 9 ) ] : $ped( $clave );
		$email = strtolower( (string) $p->get_billing_email() );
		$c     = ( $por_email[ $email ] ?? array() )[0] ?? null;
		if ( ! $ok( null !== $c, "{$clave}: hay email capturado" ) ) continue;
		$txt = dip_prueba_texto( $c['cuerpo'] );
		$ok( $x[0] === $c['asunto'], "{$clave}: asunto", $c['asunto'] );
		foreach ( array_slice( $x, 1, 6 ) as $frase ) $ok( false !== strpos( $txt, $frase ), "{$clave}: dice «{$frase}»", mb_substr( preg_replace( '/\s+/u', ' ', $txt ), 0, 600 ) );
		$ok( false !== strpos( $c['cuerpo'], 'href="' . $url_resenas . '"' ), "{$clave}: el botón lleva al enlace de reseñas de Google" );
		$ok( 'text/html' === $c['tipo'] && false !== strpos( (string) $c['alt'], $url_resenas ), "{$clave}: HTML con versión en texto", array( $c['tipo'], mb_substr( (string) $c['alt'], 0, 80 ) ) );
		$ok( array( 'info@dryicepack.es' ) === $c['responder'], "{$clave}: Reply-To info@dryicepack.es", $c['responder'] );
		$ok( $de === $c['de'] && $de_nombre === $c['de_nombre'], "{$clave}: remitente el de la tienda ({$de_nombre} <{$de}>)", array( $c['de_nombre'], $c['de'] ) );
		$ok( false !== strpos( $c['cuerpo'], '<table' ) && false !== strpos( $c['cuerpo'], 'style=' ), "{$clave}: va dentro de la plantilla de WooCommerce con estilos en línea" );
		if ( 'en' === $x[7] ) $ok( false === strpos( $txt, 'Hola' ), "{$clave}: nada en castellano" );
		if ( ! $x[8] && 'es' === $x[7] ) $ok( false === strpos( $txt, 'su hielo' ) && false === strpos( $txt, 'responda' ), "{$clave}: de tú, sin usted" );
		if ( $x[8] && 'es' === $x[7] ) $ok( false === strpos( $txt, 'tu hielo' ) && false === strpos( $txt, 'responde a' ), "{$clave}: de usted, sin tú" );
		// Enlace de baja: firmado, en el idioma y trato del pedido, sin el email
		if ( $ok( (bool) preg_match( '#href="([^"]*dip_resenas_baja=[^"]*)"#', $c['cuerpo'], $mm ), "{$clave}: lleva enlace de baja" ) ) {
			$url = html_entity_decode( $mm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
			$ok( ( $q['dip_resenas_baja'] ?? '' ) === dip_resenas_huella( $email ) && hash_equals( dip_resenas_firma( $q['dip_resenas_baja'] ?? '' ), (string) ( $q['firma'] ?? '' ) ), "{$clave}: el enlace de baja lleva la huella y una firma válida" );
			$ok( ( 'es' === $x[7] ? ! isset( $q['idioma'] ) : $x[7] === ( $q['idioma'] ?? '' ) ) && $x[8] === ! empty( $q['usted'] ), "{$clave}: idioma y trato en el enlace de baja", $q );
			$ok( false === stripos( $url, $email ) && false === stripos( $url, rawurlencode( $email ) ) && false === stripos( $url, strtok( $email, '@' ) ), "{$clave}: el enlace de baja no contiene el email" );
			// Con el bloque comercial («Repetir el pedido» y la baja de ofertas), el botón de baja del programa de correo da
			// de baja de las dos cosas: la misma URL de baja de opiniones con la huella de ofertas y su firma (hallazgo 11)
			$cab         = (string) ( $c['cabeceras']['List-Unsubscribe'] ?? '' );
			$con_ofertas = false !== strpos( $c['cuerpo'], 'dip_ofertas_baja=' );
			$ok( false !== strpos( $cab, '<' . $url . ( $con_ofertas ? '&ofertas=' . dip_ofertas_huella( $email ) . '&firma_ofertas=' : '>' ) ) && 'List-Unsubscribe=One-Click' === ( $c['cabeceras']['List-Unsubscribe-Post'] ?? '' ), "{$clave}: cabeceras List-Unsubscribe y List-Unsubscribe-Post" . ( $con_ofertas ? ' (baja de opiniones y de ofertas)' : '' ), $c['cabeceras'] );
			$urls_baja[ $clave ] = $url;
		}
		if ( $carpeta ) {
			$nombre = sprintf( '%s-%s-%s-%s.html', strtolower( $clave ), $x[7], $x[8] ? 'usted' : 'tu', 'recogida' === $p->get_meta( '_dip_metodo_entrega' ) ? 'recogida' : 'envio' );
			file_put_contents( $carpeta . '/' . $nombre, $c['cuerpo'] );
		}
	}

	// Una sola vez: otra pasada (también al día siguiente) no repite
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'dip_tarea_diaria' );
	$ok( 0 === count( dip_prueba_resenas_capturadas( $antes ) ), 'una segunda pasada no vuelve a enviar nada', count( dip_prueba_resenas_capturadas( $antes ) ) );

	// Bloque del email de «completado» después de la pasada
	$completado = WC()->mailer()->get_emails()['WC_Email_Customer_Completed_Order'] ?? null;
	$bloque     = static function ( $p ) use ( $completado ) {
		ob_start();
		do_action( 'woocommerce_email_after_order_table', $p, false, false, $completado );
		return dip_prueba_texto( (string) ob_get_clean() );
	};
	$frase_bloque = dip_textos_tienda( 'es' )['resena'];
	$ok( ! dip_resenas_bloque_completado( $ped( 'A' ) ) && false === strpos( $bloque( $ped( 'A' ) ), $frase_bloque ), 'completado: sin bloque en un pedido que ya recibió el email' );
	$ok( ! dip_resenas_bloque_completado( $ped( 'I' ) ), 'completado: sin bloque si a ese email se le pidió opinión hace menos de 180 días (otro pedido)' );
	$ok( false !== strpos( $bloque( $ped( 'T' ) ), $frase_bloque ), 'completado: el bloque sale en el email de un pedido que no lo va a recibir', mb_substr( $bloque( $ped( 'T' ) ), 0, 200 ) );
	$ok( false === strpos( $bloque( $ped( 'J' ) ), $frase_bloque ), 'completado: sin bloque para un email dado de baja (email real)' );

	// Dos pasadas a la vez: la segunda no hace nada
	$s1 = dip_prueba_crear( array( 'email' => 'resenas-s1@example.com', 'nombre' => 'Fallo Uno', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ) );
	$s2 = dip_prueba_crear( array( 'email' => 'resenas-s2@example.com', 'nombre' => 'Fallo Dos', 'fecha' => dip_prueba_dia( -2 ), 'kg' => 20, 'idioma' => 'ca' ) );
	add_option( 'dip_resenas_en_marcha', time(), '', false );
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	$r     = dip_resenas_pasada();
	$ok( ! $r['enviado'] && 0 === count( dip_prueba_resenas_capturadas( $antes ) ), 'con otra pasada en marcha (hace menos de 15 min) no se envía nada' );
	$ok( false !== get_option( 'dip_resenas_en_marcha' ), 'y no se quita el bloqueo de la otra pasada' );
	delete_option( 'dip_resenas_en_marcha' );

	// Fallo de wp_mail: nota en cada pedido y UN aviso con los dos
	$GLOBALS['dip_prueba_fallar'] = true;
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	$r     = dip_resenas_pasada();
	$GLOBALS['dip_prueba_fallar'] = false;
	$ok( isset( $r['fallo'][ $s1 ], $r['fallo'][ $s2 ] ) && ! $r['enviado'], 'con wp_mail fallando, los dos pedidos quedan como fallo', $r );
	$avisos = dip_prueba_avisos_capturados( $antes );
	$ok( 1 === count( $avisos ), 'un solo email de aviso por pasada', array( count( $avisos ), $GLOBALS['dip_prueba_errores'] ) );
	if ( $avisos ) {
		$a = $avisos[0];
		$ok( array( strtolower( (string) dip_ajuste( 'email_avisos' ) ) ) === array_map( 'strtolower', $a['para'] ), 'el aviso va al correo de avisos', $a['para'] );
		$ok( 'Dryicepack: no han salido 2 peticiones de reseña' === $a['asunto'], 'asunto del aviso', $a['asunto'] );
		$ok( false !== strpos( $a['cuerpo'], wc_get_order( $s1 )->get_order_number() ) && false !== strpos( $a['cuerpo'], wc_get_order( $s2 )->get_order_number() ) && false !== strpos( $a['cuerpo'], 'fallo simulado por la prueba' ), 'el aviso lista los dos pedidos y el error' );
		$ok( false !== strpos( $a['cuerpo'], 'Pedir reseña por email' ) && false !== strpos( $a['cuerpo'], 'mañana a las 9:00' ), 'el aviso explica la alternativa manual y el reintento' );
		if ( $carpeta ) file_put_contents( $carpeta . '/aviso-fallo.txt', 'Para: ' . implode( ', ', $a['para'] ) . "\nAsunto: " . $a['asunto'] . "\n\n" . $a['cuerpo'] );
	}
	foreach ( array( $s1, $s2 ) as $s ) {
		$ok( '' === (string) wc_get_order( $s )->get_meta( '_dip_resena_pedida' ), "#{$s}: tras el fallo no queda marcado (se reintenta)" );
		$ok( (bool) dip_prueba_notas( $s, 'No se ha podido enviar la petición de reseña' ), "#{$s}: nota del fallo en el pedido" );
	}
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	$r     = dip_resenas_pasada();
	$ok( isset( $r['enviado'][ $s1 ], $r['enviado'][ $s2 ] ) && 2 === count( dip_prueba_resenas_capturadas( $antes ) ) && 0 === count( dip_prueba_avisos_capturados( $antes ) ), 'al día siguiente, con el correo arreglado, salen las dos y sin aviso' );

	// Acción manual
	$acciones = apply_filters( 'woocommerce_order_actions', array(), $ped( 'G' ) );
	$ok( 'Pedir reseña por email' === ( $acciones['dip_pedir_resena'] ?? '' ), 'Acciones del pedido incluye «Pedir reseña por email»', $acciones );
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'woocommerce_order_action_dip_pedir_resena', $ped( 'G' ) );
	$ok( 1 === count( dip_prueba_resenas_capturadas( $antes ) ) && '' !== (string) $ped( 'G' )->get_meta( '_dip_resena_pedida' ), 'manual: sale para un pedido de hace 5 días (fuera de la ventana)' );
	$ok( (bool) dip_prueba_notas( $ids['G'], '(a mano, desde el pedido' ), 'manual: nota «a mano»' );
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'woocommerce_order_action_dip_pedir_resena', $ped( 'G' ) );
	$ok( 0 === count( dip_prueba_resenas_capturadas( $antes ) ) && (bool) dip_prueba_notas( $ids['G'], 'No se ha pedido la reseña: ya se pidió la reseña de este pedido' ), 'manual: no repite en el mismo pedido y lo explica en una nota' );
	foreach ( array( 'I' => 'ya se le pidió opinión', 'J' => 'dado de baja', 'F' => 'solo se pide en Procesando', 'FUT' => 'todavía no ha llegado' ) as $l => $motivo ) {
		$antes = count( $GLOBALS['dip_prueba_correos'] );
		do_action( 'woocommerce_order_action_dip_pedir_resena', $ped( $l ) );
		$ok( 0 === count( dip_prueba_resenas_capturadas( $antes ) ) && (bool) dip_prueba_notas( $ids[ $l ], $motivo ), "manual en {$l}: no sale y la nota dice «{$motivo}»", dip_prueba_notas( $ids[ $l ], 'No se ha pedido' ) );
	}

	// Resumen de Ajustes
	$ok( (bool) preg_match( '/^Enviados los últimos 30 días: (\d+) · Clientes dados de baja: (\d+)$/u', dip_resenas_resumen_admin(), $rm ) && (int) $rm[1] >= count( $si ) + count( $checkout ) + 3, 'resumen de Ajustes con los enviados de los últimos 30 días', dip_resenas_resumen_admin() );

	// Para la prueba de la página de baja por HTTP (B: catalán de tú; D: castellano de usted)
	foreach ( array( 'B', 'D' ) as $l ) {
		if ( isset( $urls_baja[ $l ] ) ) echo "URL_BAJA_{$l}: {$urls_baja[ $l ]}\nEMAIL_{$l}: " . $ped( $l )->get_billing_email() . "\n";
	}
	if ( $carpeta ) echo "EMAILS_EN: {$carpeta}\n";
	dip_prueba_cambiar_ajustes( array( 'resenas_email' => 0 ) );
	echo "Ajuste desactivado para el paso 3\n";
	$fin();
}

/* =========================================================
 * Paso 3: después de la baja por HTTP (ajuste desactivado)
 * ========================================================= */
if ( '3' === $paso ) {
	echo "== Paso 3: la baja bloquea (con el email automático desactivado)\n";
	$ok( ! dip_ajuste( 'resenas_email' ), 'el ajuste está desactivado' );
	$bajas_orig = is_array( $e['bajas']['valor'] ) ? count( $e['bajas']['valor'] ) : 0;
	$ok( dip_resenas_de_baja( 'resenas-b@example.com' ) && dip_resenas_de_baja( 'RESENAS-D@example.com' ), 'B (botón de la página) y D (RFC 8058) están de baja' );
	$ok( count( dip_resenas_bajas() ) === $bajas_orig + 3, 'en la lista hay justo 3 bajas nuevas (J, B y D): los enlaces mal firmados no añaden nada', count( dip_resenas_bajas() ) );
	$ok( false === strpos( (string) maybe_serialize( get_option( 'dip_resenas_bajas' ) ), '@' ), 'la opción dip_resenas_bajas no guarda ningún email' );
	$ok( ! array_key_exists( 'dip_resenas_bajas', wp_load_alloptions() ), 'dip_resenas_bajas no se carga en cada página (autoload desactivado)' );

	$b2 = dip_prueba_crear( array( 'email' => 'resenas-b@example.com', 'nombre' => 'Jordi Puig', 'fecha' => dip_prueba_dia( -1 ), 'idioma' => 'ca', 'kg' => 10 ) );
	$r  = dip_resenas_pasada();
	$ok( isset( $r['omitido'][ $b2 ] ) && false !== mb_strpos( $r['omitido'][ $b2 ], 'dado de baja' ), 'la pasada salta un pedido nuevo de un email dado de baja', $r['omitido'][ $b2 ] ?? null );
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'woocommerce_order_action_dip_pedir_resena', wc_get_order( $b2 ) );
	$ok( 0 === count( dip_prueba_resenas_capturadas( $antes ) ) && (bool) dip_prueba_notas( $b2, 'dado de baja' ), 'la acción manual respeta la baja' );
	$completado = WC()->mailer()->get_emails()['WC_Email_Customer_Completed_Order'] ?? null;
	$bloque     = static function ( $p ) use ( $completado ) {
		ob_start();
		do_action( 'woocommerce_email_after_order_table', $p, false, false, $completado );
		return dip_prueba_texto( (string) ob_get_clean() );
	};
	$ok( false === strpos( $bloque( wc_get_order( $b2 ) ), dip_textos_tienda( 'es' )['resena'] ) && false === strpos( $bloque( wc_get_order( $b2 ) ), dip_textos_tienda( 'ca' )['resena'] ), 'completado: la baja quita el bloque también con el email automático desactivado' );
	$ok( false !== strpos( $bloque( $ped( 'A' ) ), dip_textos_tienda( 'es' )['resena'] ), 'completado: con el email automático desactivado, el bloque sale como antes (alternativa)' );
	dip_prueba_cambiar_ajustes( array( 'resenas_email' => 1, 'resenas_url' => '' ) );
	echo "Enlace de reseñas vaciado para el paso 4\n";
	$fin();
}

/* =========================================================
 * Paso 4: sin enlace de reseñas no sale nada; limpieza
 * ========================================================= */
if ( '4' === $paso ) {
	echo "== Paso 4: sin enlace de reseñas\n";
	$ok( '' === (string) dip_ajuste( 'resenas_url' ) && 1 === (int) dip_ajuste( 'resenas_email' ), 'enlace vacío y email automático activado' );
	$z     = dip_prueba_crear( array( 'email' => 'resenas-z@example.com', 'nombre' => 'Sin Enlace', 'fecha' => dip_prueba_dia( -1 ), 'kg' => 10 ) );
	$antes = count( $GLOBALS['dip_prueba_correos'] );
	do_action( 'dip_tarea_diaria' );
	$ok( 0 === count( dip_prueba_resenas_capturadas( $antes ) ) && '' === (string) wc_get_order( $z )->get_meta( '_dip_resena_pedida' ), 'sin enlace, la tarea diaria no envía nada' );
	$ok( ! isset( apply_filters( 'woocommerce_order_actions', array(), wc_get_order( $z ) )['dip_pedir_resena'] ), 'sin enlace, la acción manual no aparece' );
	$r = dip_resenas_enviar( wc_get_order( $z ), true );
	$ok( 'omitido' === $r['estado'] && false !== strpos( $r['motivo'], 'no hay enlace' ), 'sin enlace, enviar a mano tampoco sale', $r );
	echo 'Limpieza: ' . dip_prueba_limpiar() . "\n";
	$ok( ! dip_prueba_estado() && ! get_option( 'dip_resenas_en_marcha' ), 'el servidor queda como estaba' );
	$fin();
}

echo "FALLO: paso desconocido\n";
$fallos++;
$fin();
