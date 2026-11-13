<?php
/**
 * Pruebas de los envíos en MRW (inc/mrw-sagec.php) y del pedido urgente (inc/urgente.php) en el WordPress de prueba
 * (puerto 9500), con pedidos de verdad y MRW simulado: ninguna petición sale a internet (pre_http_request responde como
 * el servicio web SAGEC, con los nombres del WSDL del 11/11/2026). No envía emails: un PHPMailer de prueba los guarda.
 *
 * herramientas/probar-sagec.mjs copia este archivo a plugin/dryicepack-tienda/zz-prueba-sagec.php, lo llama y lo borra.
 * Al acabar deja todo como estaba (pedidos de prueba borrados, ajustes de MRW y enlaces urgentes restaurados).
 */
require dirname( __DIR__, 3 ) . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
if ( ! function_exists( 'dip_sagec_crear' ) || ! function_exists( 'dip_urgente_crear' ) || ! function_exists( 'wc_get_orders' ) ) {
	exit( "FALLO: falta WooCommerce, inc/mrw-sagec.php o inc/urgente.php\n0 comprobaciones, 1 fallos\n" );
}

/* ---------- Correo de prueba ---------- */
require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';
class Dip_Prueba_Correo_Sagec extends WP_PHPMailer {
	public function send() {
		$GLOBALS['correos'][] = array(
			'para'   => array_map( static fn( $x ) => strtolower( $x[0] ), $this->getToAddresses() ),
			'asunto' => (string) $this->Subject,
			'texto'  => (string) ( $this->AltBody ?: $this->Body ),
		);
		return true;
	}
}
$GLOBALS['phpmailer'] = new Dip_Prueba_Correo_Sagec( true );
$GLOBALS['correos']   = array();
add_filter( 'wp_mail_from', static fn() => 'wordpress@dryicepack.test', 1 );

/* ---------- MRW simulado ---------- */
$GLOBALS['peticiones'] = array();
$GLOBALS['respuesta']  = 'ok';
$GLOBALS['sagec_n']    = 0;
add_filter( 'pre_http_request', static function ( $pre, $args, $url ) {
	if ( false === strpos( (string) $url, 'mrw.es/MRWEnvio.asmx' ) ) return $pre;
	$accion = trim( (string) ( $args['headers']['SOAPAction'] ?? '' ), '"' );
	$op     = substr( $accion, strrpos( $accion, '/' ) + 1 );
	$GLOBALS['peticiones'][] = array( 'url' => $url, 'op' => $op, 'cuerpo' => (string) $args['body'] );
	$m = $GLOBALS['respuesta'];
	if ( 'caido' === $m ) return new WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out' );
	if ( 'fault' === $m ) {
		$cuerpo = '<soap:Fault><faultcode>soap:Server</faultcode><faultstring>Server was unable to process request. ---&gt; Usuario o contraseña incorrectos</faultstring></soap:Fault>';
	} else {
		$ok = 'ok' === $m;
		switch ( $op ) {
			case 'TransmEnvio':
				$n      = $ok ? sprintf( '0285%09d', ++$GLOBALS['sagec_n'] ) : '';
				$r      = $ok ? '<Estado>1</Estado><Mensaje /><NumeroSolicitud>SOL' . $GLOBALS['sagec_n'] . '</NumeroSolicitud><NumeroEnvio>' . $n . '</NumeroEnvio><Url>https://sagec.mrw.es/x</Url>' : '<Estado>0</Estado><Mensaje>El código postal no corresponde a la población</Mensaje>';
				$cuerpo = '<TransmEnvioResponse xmlns="http://www.mrw.es/"><TransmEnvioResult>' . $r . '</TransmEnvioResult></TransmEnvioResponse>';
				break;
			case 'GetEtiquetaEnvio':
				$cuerpo = '<GetEtiquetaEnvioResponse xmlns="http://www.mrw.es/"><GetEtiquetaEnvioResult><Estado>1</Estado><Mensaje /><EtiquetaFile>' . base64_encode( "%PDF-1.4\n% etiqueta de prueba\n" ) . '</EtiquetaFile></GetEtiquetaEnvioResult></GetEtiquetaEnvioResponse>';
				break;
			case 'CancelarEnvio':
				$cuerpo = '<CancelarEnvioResponse xmlns="http://www.mrw.es/"><CancelarEnvioResult><Estado>1</Estado><Mensaje>Envío anulado</Mensaje><NumeroSolicitud>C1</NumeroSolicitud><NumeroEnvio /></CancelarEnvioResult></CancelarEnvioResponse>';
				break;
			default:
				$cuerpo = '<' . $op . 'Response xmlns="http://www.mrw.es/"><' . $op . 'Result><Estado>1</Estado><Mensaje /></' . $op . 'Result></' . $op . 'Response>';
		}
	}
	return array(
		'headers'  => array(),
		'body'     => '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>' . $cuerpo . '</soap:Body></soap:Envelope>',
		'response' => array( 'code' => 'fault' === $m ? 500 : 200, 'message' => 'OK' ),
		'cookies'  => array(),
		'filename' => null,
	);
}, 10, 3 );

/* ---------- Herramientas ---------- */
$GLOBALS['fallos']  = 0;
$GLOBALS['pruebas'] = 0;
function ok( $cond, $msg, $detalle = null ) {
	$GLOBALS['pruebas']++;
	if ( $cond ) return true;
	$GLOBALS['fallos']++;
	echo 'FALLO: ' . $msg . ( null === $detalle ? '' : ' → ' . ( is_string( $detalle ) ? $detalle : wp_json_encode( $detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ) ) . "\n";
	return false;
}
function a_las( $ymd_hora ) {
	return new DateTimeImmutable( $ymd_hora, dip_zona_horaria() );
}
function peticiones_de( WC_Order $p, $op = 'TransmEnvio' ) {
	return array_values( array_filter( $GLOBALS['peticiones'], static fn( $r ) => $r['op'] === $op && false !== strpos( $r['cuerpo'], '<Referencia>' . $p->get_order_number() . '</Referencia>' ) ) );
}
function correos_a( $email, $desde = 0 ) {
	return array_values( array_filter( array_slice( $GLOBALS['correos'], $desde ), static fn( $c ) => in_array( strtolower( $email ), $c['para'], true ) ) );
}
function fresco( WC_Order $p ) {
	return wc_get_order( $p->get_id() );
}
function ast( WC_Order $p ) {
	return array_values( array_map( static fn( $i ) => (string) ( $i['tracking_number'] ?? '' ), array_filter( (array) fresco( $p )->get_meta( '_wc_shipment_tracking_items' ), 'is_array' ) ) );
}
function notas( WC_Order $p, $texto ) {
	return array_values( array_filter( wc_get_order_notes( array( 'order_id' => $p->get_id() ) ), static fn( $n ) => false !== strpos( $n->content, $texto ) ) );
}
function variacion( $kg ) {
	$post  = get_page_by_path( 'hielo-seco', OBJECT, 'product' );
	$padre = $post ? wc_get_product( $post->ID ) : null;
	foreach ( $padre ? $padre->get_children() : array() as $id ) {
		$v = wc_get_product( $id );
		$a = $v ? $v->get_attributes() : array();
		if ( ( $a['pa_peso'] ?? '' ) === $kg . 'kg-de-hielo-seco' && ( $a['pa_formato'] ?? '' ) === '3mm' ) return $v;
	}
	return null;
}
/** Pedido de prueba. $d: clave, nombre, empresa, telefono, calle, piso, cp, ciudad, fecha, kg, cantidad, metodo, nota, estado. */
function crear( array $d ) {
	$recoge = 'recogida' === ( $d['metodo'] ?? 'envio' );
	$p      = new WC_Order();
	$p->set_created_via( 'prueba-sagec' );
	$dir = array(
		'first_name' => $d['nombre'] ?? 'Laura García Pons',
		'last_name'  => '',
		'company'    => $d['empresa'] ?? '',
		'address_1'  => $d['calle'] ?? 'Carrer de Mallorca 123',
		'address_2'  => $d['piso'] ?? '2º 1ª',
		'postcode'   => $d['cp'] ?? '08013',
		'city'       => $d['ciudad'] ?? 'Barcelona',
		'state'      => dip_provincia_por_cp( $d['cp'] ?? '08013' ),
		'country'    => 'ES',
	);
	$p->set_address( $dir + array( 'email' => $d['clave'] . '@sagec.test', 'phone' => $d['telefono'] ?? '600123456' ), 'billing' );
	$p->set_address( $dir, 'shipping' );
	$p->add_product( variacion( $d['kg'] ?? 10 ), (int) ( $d['cantidad'] ?? 1 ) );
	$envio = new WC_Order_Item_Shipping();
	$envio->set_props( $recoge ? array( 'method_title' => 'Recogida en Mataró', 'method_id' => 'local_pickup', 'total' => 0 ) : array( 'method_title' => 'Envío MRW', 'method_id' => 'flat_rate', 'total' => 13.19 ) );
	$p->add_item( $envio );
	$p->set_payment_method( 'cheque' );
	$p->update_meta_data( '_dip_fecha_entrega', $d['fecha'] ?? '2026-12-03' );
	$p->update_meta_data( '_dip_metodo_entrega', $recoge ? 'recogida' : 'envio' );
	$p->update_meta_data( '_dip_idioma', 'es' );
	$p->update_meta_data( '_dip_kg', (float) ( $d['kg'] ?? 10 ) * (int) ( $d['cantidad'] ?? 1 ) );
	$p->update_meta_data( '_dip_cajas', (int) ( $d['cantidad'] ?? 1 ) );
	$p->update_meta_data( '_dip_factura_emitida', 1 );
	$p->update_meta_data( '_dip_email_factura', 'prueba' );
	$p->update_meta_data( '_dip_prueba_sagec', $d['clave'] );
	if ( ! empty( $d['nota'] ) ) $p->set_customer_note( $d['nota'] );
	$p->calculate_totals( true );
	$p->set_status( $d['estado'] ?? 'processing' );
	$p->save();
	return fresco( $p );
}
function limpiar() {
	$ids = wc_get_orders( array( 'limit' => -1, 'return' => 'ids', 'meta_key' => '_dip_prueba_sagec', 'meta_compare' => 'EXISTS', 'type' => 'shop_order', 'status' => array_keys( wc_get_order_statuses() ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( $p ) $p->delete( true );
		delete_option( 'dip_sagec_bloqueo_' . $id );
	}
	$copia = get_option( 'dip_prueba_sagec_copia' );
	if ( is_array( $copia ) ) {
		foreach ( array( 'dip_sagec', 'dip_urgentes', 'dip_sagec_ultima', 'dip_sagec_aviso_falta', 'dip_sagec_avisado' ) as $k ) {
			if ( null === $copia[ $k ] ) delete_option( $k );
			else update_option( $k, $copia[ $k ], false );
		}
		delete_option( 'dip_prueba_sagec_copia' );
	}
	foreach ( array_keys( (array) _get_cron_array() ) as $t ) wp_clear_scheduled_hook( 'dip_sagec_uno' );
	foreach ( array( 'dip_sagec_pasada_en_curso', 'dip_prueba_cerrojo' ) as $k ) delete_option( $k );
	unset( $_COOKIE['dip_urgente'], $_POST['dip_mrw_envio'], $_POST['dip_mrw_envio_antes'] );
	return count( $ids );
}

if ( isset( $_GET['limpiar'] ) ) {
	echo 'Pedidos de prueba borrados: ' . limpiar() . "\n";
	exit;
}
limpiar(); // por si una prueba anterior se cortó
update_option( 'dip_prueba_sagec_copia', array( 'dip_sagec' => get_option( 'dip_sagec', null ), 'dip_urgentes' => get_option( 'dip_urgentes', null ), 'dip_sagec_ultima' => get_option( 'dip_sagec_ultima', null ), 'dip_sagec_aviso_falta' => get_option( 'dip_sagec_aviso_falta', null ), 'dip_sagec_avisado' => get_option( 'dip_sagec_avisado', null ) ), false );

try {
	$avisos = (string) dip_ajuste( 'email_avisos' );
	$conf   = array(
		'modo' => 'real', 'franquicia' => '02850', 'abonado' => '123456', 'departamento' => '', 'usuario' => 'PRUEBAWS',
		'clave' => dip_sagec_cifrar( 'Secreta&<1>' ), 'servicio' => '0110', 'servicio_sabado' => '', 'tipo_mercancia' => '',
		'servicio_especial' => '', 'tipo_etiqueta' => '', 'observaciones' => 'Hielo seco (UN 1845) en caja de poliestireno.',
		'en_franquicia' => 'N', 'confirmado' => '1',
	);
	update_option( 'dip_sagec', $conf, false );

	/* ---------- Claves ---------- */
	$guardada = (string) get_option( 'dip_sagec' )['clave'];
	ok( 0 === strpos( $guardada, 'aes:' ) && false === strpos( $guardada, 'Secreta' ), 'la contraseña se guarda cifrada', $guardada );
	ok( 'Secreta&<1>' === dip_sagec_config()['clave'], 'y se lee descifrada' );
	ok( array() === dip_sagec_falta(), 'con todo puesto no falta nada', dip_sagec_falta() );
	ok( array( 'código de servicio' ) === dip_sagec_falta( array_merge( dip_sagec_config(), array( 'servicio' => '' ) ) ), 'sin código de servicio, falta' );
	// UN 1845, etiqueta y «en franquicia» (revisión 25): sin la confirmación de MRW, el modo real no crea nada
	$sin_conf = dip_sagec_falta( array_merge( dip_sagec_config(), array( 'confirmado' => '' ) ) );
	ok( 1 === count( $sin_conf ) && false !== strpos( $sin_conf[0], 'UN 1845' ), 'sin la confirmación de MRW, falta', $sin_conf );
	ok( array() === dip_sagec_falta( array_merge( dip_sagec_config(), array( 'confirmado' => '' ) ), true ), 'para pruebas y etiquetas basta con las claves' );
	ok( '' === ( dip_sagec_por_defecto()['confirmado'] ?? 'x' ) && 'N' === ( dip_sagec_por_defecto()['en_franquicia'] ?? '' ), 'de serie: sin confirmar y sin «en franquicia»' );
	// Contraseña que ya no se descifra (claves de WordPress cambiadas): falta y se dice (revisión 4)
	update_option( 'dip_sagec', array_merge( $conf, array( 'clave' => 'aes:' . base64_encode( str_repeat( 'x', 36 ) ) /* 20 bytes cifrados: no es un bloque de AES, nunca se descifra */ ) ), false );
	ok( dip_sagec_clave_ilegible() && (bool) array_filter( dip_sagec_falta(), static fn( $t ) => false !== strpos( $t, 'ya no se puede leer' ) ), 'contraseña ilegible: falta y lo dice', dip_sagec_falta() );
	update_option( 'dip_sagec', $conf, false );
	ok( ! dip_sagec_clave_ilegible(), 'la contraseña buena se lee' );
	// Código vacío: el de serie de ese servicio, nunca el de las 14:00 (revisión 30)
	ok( '0015' === dip_sagec_codigo( '10', array_merge( dip_sagec_config(), array( 'servicio_10' => '' ) ) ) && '0105' === dip_sagec_codigo( '12', array_merge( dip_sagec_config(), array( 'servicio_12' => '' ) ) ) && '0800' === dip_sagec_codigo( 'sabado', array_merge( dip_sagec_config(), array( 'servicio_sabado' => '' ) ) ), 'código vacío: el de serie de ese servicio' );
	// Cerrojo atómico (revisión 21)
	ok( dip_sagec_cerrojo( 'dip_prueba_cerrojo' ) && ! dip_sagec_cerrojo( 'dip_prueba_cerrojo' ), 'cerrojo: el segundo no lo coge' );
	global $wpdb;
	$wpdb->update( $wpdb->options, array( 'option_value' => (string) ( time() - 900 ) ), array( 'option_name' => 'dip_prueba_cerrojo' ) );
	ok( dip_sagec_cerrojo( 'dip_prueba_cerrojo', 600 ), 'cerrojo viejo (proceso cortado): se coge' );
	dip_sagec_soltar( 'dip_prueba_cerrojo' );
	ok( 0 === dip_sagec_cerrojo_desde( 'dip_prueba_cerrojo' ), 'cerrojo soltado' );
	ok( dip_sagec_cerrojo( 'dip_sagec_pasada_en_curso', 600 ), 'otra pasada en marcha' );
	$r = dip_sagec_pasada( 'prueba', false, a_las( '2026-12-02 09:00' ) );
	ok( ! empty( $r['ocupada'] ) && ! dip_sagec_pasada_hecha( $r ), 'con otra pasada en marcha no se hace una segunda ni cuenta como hecha', $r );
	dip_sagec_soltar( 'dip_sagec_pasada_en_curso' );
	ok( ! dip_sagec_pasada_hecha( array( 'cuenta' => array( 'esperar' => 3 ) ) ) && dip_sagec_pasada_hecha( array( 'cuenta' => array() ) ) && dip_sagec_pasada_hecha( array( 'cuenta' => array( 'esperar' => 1, 'hecho' => 2 ) ) ), 'una pasada con todo «esperar» (antes de las 8:00) no cuenta como hecha (revisión 31)' );

	/* ---------- Dirección y teléfono ---------- */
	$casos = array(
		'Carrer de Mallorca 123'     => array( 'Carrer de Mallorca', '123', '' ),
		'Calle Mayor, 5, 3º 2ª'      => array( 'Calle Mayor', '5', '3º 2ª' ),
		'Avinguda 11 de Setembre 45' => array( 'Avinguda 11 de Setembre', '45', '' ),
		'C/ Pujades 77B'             => array( 'C/ Pujades', '77B', '' ),
		'Plaza España s/n'           => array( 'Plaza España s/n', '', '' ),
		'Calle Mayor 5 3º 2ª'        => array( 'Calle Mayor 5 3º 2ª', '', '' ),
		'Calle Alcalá, nº 45'        => array( 'Calle Alcalá', '45', '' ),
	);
	foreach ( $casos as $linea => $esperado ) ok( $esperado === dip_sagec_partir_direccion( $linea ), "dirección «{$linea}»", dip_sagec_partir_direccion( $linea ) );
	ok( '600123456' === dip_sagec_telefono( '+34 600 12 34 56' ) && '600123456' === dip_sagec_telefono( '0034600123456' ) && '936737641' === dip_sagec_telefono( '936 73 76 41' ), 'teléfono sin +34 ni espacios' );

	/* ---------- Pedidos del miércoles 2/12 (llegan el jueves 3/12) ---------- */
	$bien     = crear( array( 'clave' => 'bien' ) );
	$empresa  = crear( array( 'clave' => 'empresa', 'nombre' => 'Marta Soler Vidal', 'empresa' => 'Laboratorio Soler S.L.', 'kg' => 20, 'cantidad' => 2, 'calle' => 'Calle de Alcalá 45', 'piso' => '', 'cp' => '28014', 'ciudad' => 'Madrid' ) );
	$sin_tel  = crear( array( 'clave' => 'sintel', 'telefono' => '' ) );
	$con_nota = crear( array( 'clave' => 'nota', 'nota' => 'Entregar en la puerta de atrás' ) );
	$recogida = crear( array( 'clave' => 'recogida', 'metodo' => 'recogida' ) );
	$otro     = crear( array( 'clave' => 'otro', 'fecha' => '2026-12-04' ) );
	$pend     = crear( array( 'clave' => 'pendiente', 'estado' => 'pending' ) );
	$canarias = crear( array( 'clave' => 'canarias', 'cp' => '35001', 'ciudad' => 'Las Palmas de Gran Canaria' ) );
	$mucho    = crear( array( 'clave' => 'mucho', 'kg' => 20, 'cantidad' => 8, 'cp' => '28014', 'ciudad' => 'Madrid' ) );
	$GLOBALS['correos'] = array();

	// Antes de las 8:00 no se crea nada
	$r = dip_sagec_pasada( 'prueba 7:30', false, a_las( '2026-12-02 07:30' ) );
	ok( ! peticiones_de( $bien ) && ! peticiones_de( $empresa ), 'a las 7:30 no se llama a MRW', $r['cuenta'] );
	ok( '' === (string) fresco( $bien )->get_meta( '_dip_sagec_mano' ) && '' === (string) fresco( $sin_tel )->get_meta( '_dip_sagec_mano' ), 'ni se marca nada' );

	// La pasada de las 8:00
	$r = dip_sagec_pasada( 'pasada de las 8:00', false, a_las( '2026-12-02 08:00' ) );
	$b = fresco( $bien );
	$n = (string) $b->get_meta( '_dip_mrw_envio' );
	ok( 1 === count( peticiones_de( $bien ) ) && '' !== $n, 'pedido completo: creado en MRW con número', array( $n, $r['lineas'] ) );
	$x = peticiones_de( $bien )[0] ?? array( 'cuerpo' => '', 'url' => '' );
	ok( DIP_SAGEC_URL_REAL === $x['url'], 'modo real: llama a sagec.mrw.es', $x['url'] );
	foreach ( array( '<CodigoFranquicia>02850</CodigoFranquicia>', '<CodigoAbonado>123456</CodigoAbonado>', '<UserName>PRUEBAWS</UserName>', '<Password>Secreta&amp;&lt;1&gt;</Password>',
		'<Fecha>02/12/2026</Fecha>', '<Referencia>' . $b->get_order_number() . '</Referencia>', '<CodigoServicio>0110</CodigoServicio>', '<NumeroBultos>1</NumeroBultos>', '<Peso>11</Peso>',
		'<EntregaSabado>N</EntregaSabado>', '<EnFranquicia>N</EnFranquicia>', '<Reembolso>N</Reembolso>', '<Via>Carrer de Mallorca</Via>', '<Numero>123</Numero>', '<Resto>2º 1ª</Resto>',
		'<CodigoPostal>08013</CodigoPostal>', '<Poblacion>Barcelona</Poblacion>', '<Provincia>BARCELONA</Provincia>', '<Telefono>600123456</Telefono>', '<Nombre>Laura García Pons</Nombre>',
		'<Observaciones>Hielo seco (UN 1845) en caja de poliestireno.</Observaciones>' ) as $trozo ) {
		ok( false !== strpos( $x['cuerpo'], $trozo ), 'la petición lleva ' . $trozo );
	}
	ok( false === strpos( $x['cuerpo'], '<DatosRecogida>' ) && false === strpos( $x['cuerpo'], '<TipoMercancia>' ) && false === strpos( $x['cuerpo'], '<Notificaciones>' ), 'sin recogida (MRW usa la del abonado), sin campos vacíos y sin avisos de MRW al cliente' );
	$dom = new DOMDocument();
	ok( @$dom->loadXML( $x['cuerpo'] ), 'la petición es XML válido' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	ok( array( $n ) === ast( $bien ), 'el número está en Advanced Shipment Tracking (lo lee la app)', ast( $bien ) );
	$item = (array) ( fresco( $bien )->get_meta( '_wc_shipment_tracking_items' )[0] ?? array() );
	ok( 'MRW' === ( $item['custom_tracking_provider'] ?? '' ) && false !== strpos( (string) ( $item['custom_tracking_link'] ?? '' ), 'track.asp?envio=' . $n ) && ! empty( $item['tracking_id'] ), 'con transportista MRW y enlace de seguimiento', $item );
	ok( '' === (string) $b->get_meta( '_dip_enviado' ) && dip_sagec_pendiente_de_salir( $b ), 'creado pero aún sin salir' );
	ok( 'processing' === $b->get_status(), 'el estado del pedido no cambia' );
	ok( (bool) notas( $bien, 'Envío creado en MRW (pasada de las 8:00): número ' . $n ), 'nota en el pedido' );
	ok( ! array_filter( correos_a( 'bien@sagec.test' ), static fn( $c ) => false !== strpos( $c['asunto'], 'va de camino' ) ), 'el cliente no recibe «va de camino» todavía' );
	if ( function_exists( 'dip_mp_situacion' ) && function_exists( 'dip_mp_textos' ) ) {
		ok( 'preparando' === dip_mp_situacion( $b, 'es', dip_mp_textos( 'es', false ) )['fase'], '«Mi pedido» lo da por «preparando», no «en camino»', dip_mp_situacion( $b, 'es', dip_mp_textos( 'es', false ) )['fase'] );
	}
	$e  = fresco( $empresa );
	$xe = peticiones_de( $empresa )[0]['cuerpo'] ?? '';
	ok( '' !== (string) $e->get_meta( '_dip_mrw_envio' ), 'empresa: creado' );
	foreach ( array( '<Nombre>Laboratorio Soler S.L.</Nombre>', '<ALaAtencionDe>Marta Soler Vidal</ALaAtencionDe>', '<Contacto>Marta Soler Vidal</Contacto>', '<NumeroBultos>2</NumeroBultos>', '<Peso>42</Peso>', '<Provincia>MADRID</Provincia>', '<Via>Calle de Alcalá</Via>', '<Numero>45</Numero>' ) as $trozo ) {
		ok( false !== strpos( $xe, $trozo ), 'empresa: la petición lleva ' . $trozo );
	}
	ok( false === strpos( $xe, '<Resto>' ), 'empresa: sin piso, sin Resto' );
	$mano = array( 'sintel' => array( $sin_tel, 'el teléfono no es válido' ), 'nota' => array( $con_nota, 'el cliente ha dejado una nota: «Entregar en la puerta de atrás»' ), 'canarias' => array( $canarias, 'Las Palmas' ), 'mucho' => array( $mucho, 'va a medida' ) );
	foreach ( $mano as $clave => list( $p, $motivo ) ) {
		$f = fresco( $p );
		ok( ! peticiones_de( $p ) && '' === (string) $f->get_meta( '_dip_mrw_envio' ), "{$clave}: no se crea" );
		ok( false !== strpos( (string) $f->get_meta( '_dip_sagec_mano' ), $motivo ), "{$clave}: «Hacer a mano» con el motivo", (string) $f->get_meta( '_dip_sagec_mano' ) );
		ok( (bool) notas( $p, 'Envío en MRW sin crear' ), "{$clave}: nota en el pedido" );
	}
	foreach ( array( 'recogida' => $recogida, 'otro día' => $otro, 'pendiente de pago' => $pend ) as $clave => $p ) {
		$f = fresco( $p );
		ok( ! peticiones_de( $p ) && '' === (string) $f->get_meta( '_dip_sagec_mano' ) && '' === (string) $f->get_meta( '_dip_mrw_envio' ), "{$clave}: ni se crea ni se marca" );
	}
	$aviso = correos_a( $avisos );
	ok( 1 === count( $aviso ) && 'MRW: 4 envíos para hacer a mano' === $aviso[0]['asunto'], 'un solo email al correo de avisos con los 4', array_column( $aviso, 'asunto' ) );
	ok( isset( $aviso[0] ) && false !== strpos( $aviso[0]['texto'], 'Motivo: el teléfono no es válido' ) && false !== strpos( $aviso[0]['texto'], 'antes de las 17:30' ), 'el email dice qué hacer y por qué', $aviso[0]['texto'] ?? '' );

	// En espera (pago sin confirmar): a mano; al confirmarse el pago, se quita la marca
	$espera = crear( array( 'clave' => 'espera', 'estado' => 'on-hold' ) );
	$r      = dip_sagec_crear( $espera, 'pasada', false, a_las( '2026-12-02 08:00' ) );
	ok( 'mano' === $r['estado'] && false !== strpos( (string) fresco( $espera )->get_meta( '_dip_sagec_mano' ), 'En espera' ) && ! peticiones_de( $espera ), 'En espera: no se crea, «Hacer a mano»', $r );
	fresco( $espera )->update_status( 'processing' );
	ok( '' === (string) fresco( $espera )->get_meta( '_dip_sagec_mano' ), 'pago confirmado: se quita la marca' );
	ok( 'creado' === dip_sagec_crear( fresco( $espera ), 'pasada', false, a_las( '2026-12-02 09:00' ) )['estado'], 'y ya se crea' );

	// Otra pasada: no repite nada
	$antes_p = count( $GLOBALS['peticiones'] );
	$antes_c = count( $GLOBALS['correos'] );
	dip_sagec_pasada( 'otra pasada', false, a_las( '2026-12-02 08:05' ) );
	ok( count( $GLOBALS['peticiones'] ) === $antes_p && count( $GLOBALS['correos'] ) === $antes_c, 'otra pasada: ni llamadas ni emails nuevos' );

	// Bloqueo: otra petición creándolo a la vez
	$tarde = crear( array( 'clave' => 'tarde', 'calle' => 'Riera 20', 'cp' => '08301', 'ciudad' => 'Mataró' ) );
	add_option( 'dip_sagec_bloqueo_' . $tarde->get_id(), time(), '', false );
	ok( 'ocupado' === dip_sagec_crear( $tarde, 'prueba', false, a_las( '2026-12-02 12:30' ) )['estado'], 'si otra petición lo está creando, no se crea dos veces' );
	delete_option( 'dip_sagec_bloqueo_' . $tarde->get_id() );

	// Después de las 17:30, al momento: a mano. Con el botón, sí.
	$r = dip_sagec_crear( $tarde, 'al momento', false, a_las( '2026-12-02 17:45' ) );
	ok( 'mano' === $r['estado'] && false !== strpos( implode( ' ', $r['motivos'] ), 'después de las 17:30' ), 'a las 17:45, al momento: hacer a mano', $r );
	$r = dip_sagec_crear( $tarde, 'botón «Crear en MRW»', true, a_las( '2026-12-02 17:50' ) );
	ok( 'creado' === $r['estado'] && '' === (string) fresco( $tarde )->get_meta( '_dip_sagec_mano' ), 'con el botón se crea y se quita la marca', $r );
	ok( ! array_filter( correos_a( 'tarde@sagec.test' ), static fn( $c ) => false !== strpos( $c['asunto'], 'va de camino' ) ), 'creado antes de las 18:30: el cliente aún no recibe «va de camino»' );
	ok( 'fuera' === dip_sagec_crear( $otro, 'botón', true, a_las( '2026-12-02 10:00' ) )['estado'] && '' === (string) fresco( $otro )->get_meta( '_dip_sagec_mano' ), 'el botón en un pedido de otro día no crea ni marca nada' );

	// 18:30: «va de camino»
	$desde = count( $GLOBALS['correos'] );
	$r     = dip_sagec_avisar_salidas( a_las( '2026-12-02 18:30' ) );
	foreach ( array( 'bien' => $bien, 'empresa' => $empresa, 'tarde' => $tarde ) as $clave => $p ) {
		$c = correos_a( $clave . '@sagec.test', $desde );
		ok( 1 === count( $c ) && false !== strpos( $c[0]['asunto'], 'va de camino' ), "{$clave}: a las 18:30 recibe «va de camino»", array_column( $c, 'asunto' ) );
		ok( isset( $c[0] ) && false !== strpos( $c[0]['texto'], (string) fresco( $p )->get_meta( '_dip_mrw_envio' ) ), "{$clave}: con su número de MRW" );
		ok( '' !== (string) fresco( $p )->get_meta( '_dip_enviado' ) && ! dip_sagec_pendiente_de_salir( fresco( $p ) ), "{$clave}: queda enviado" );
	}
	ok( 4 === $r['cuenta']['enviado'] && ! $r['fallos'], 'cuatro avisos (con el de En espera ya pagado), sin fallos', $r );
	if ( function_exists( 'dip_mp_situacion' ) ) ok( 'camino' === dip_mp_situacion( fresco( $bien ), 'es', dip_mp_textos( 'es', false ) )['fase'], '«Mi pedido» ya lo da por «en camino»' );
	$desde = count( $GLOBALS['correos'] );
	dip_sagec_avisar_salidas( a_las( '2026-12-02 18:45' ) );
	ok( count( $GLOBALS['correos'] ) === $desde, 'el aviso no se repite' );

	// Creado después de las 18:30: aviso al momento
	$noche = crear( array( 'clave' => 'noche' ) );
	$desde = count( $GLOBALS['correos'] );
	ok( 'creado' === dip_sagec_crear( $noche, 'botón', true, a_las( '2026-12-02 19:00' ) )['estado'], 'a las 19:00, con el botón: creado' );
	ok( 1 === count( correos_a( 'noche@sagec.test', $desde ) ), 'y el cliente recibe «va de camino» al momento' );

	// MRW no lo acepta, falla la conexión o las claves
	foreach ( array( 'error' => 'El código postal no corresponde a la población', 'fault' => 'Usuario o contraseña incorrectos', 'caido' => 'MRW no responde' ) as $modo => $texto ) {
		$GLOBALS['respuesta'] = $modo;
		$p     = crear( array( 'clave' => 'mal-' . $modo ) );
		$desde = count( $GLOBALS['correos'] );
		$GLOBALS['dip_sagec_nuevos_mano'] = array();
		$r = dip_sagec_crear( $p, 'al momento', false, a_las( '2026-12-02 09:00' ) );
		dip_sagec_email_mano();
		ok( 'error' === $r['estado'] && false !== strpos( (string) fresco( $p )->get_meta( '_dip_sagec_mano' ), $texto ), "MRW {$modo}: «Hacer a mano» con el mensaje", array( $r, fresco( $p )->get_meta( '_dip_sagec_mano' ) ) );
		ok( '' === (string) fresco( $p )->get_meta( '_dip_mrw_envio' ) && ! ast( $p ), "MRW {$modo}: sin número" );
		ok( 1 === count( correos_a( $avisos, $desde ) ), "MRW {$modo}: email al correo de avisos" );
	}
	$GLOBALS['respuesta'] = 'ok';

	// Sábado: sale el viernes 4/12 con «entrega en sábado» y el código del sábado
	update_option( 'dip_sagec', array_merge( $conf, array( 'servicio_sabado' => '0120' ) ), false );
	$sabado = crear( array( 'clave' => 'sabado', 'fecha' => '2026-12-05' ) );
	ok( 'creado' === dip_sagec_crear( $sabado, 'pasada', false, a_las( '2026-12-04 08:00' ) )['estado'], 'sábado: creado el viernes' );
	$xs = peticiones_de( $sabado )[0]['cuerpo'] ?? '';
	ok( false !== strpos( $xs, '<EntregaSabado>S</EntregaSabado>' ) && false !== strpos( $xs, '<CodigoServicio>0120</CodigoServicio>' ) && false !== strpos( $xs, '<Fecha>04/12/2026</Fecha>' ), 'sábado: entrega en sábado, código del sábado y salida el viernes' );
	update_option( 'dip_sagec', $conf, false );

	// Sábado de más de 80 kg: el Ecommerce no lo admite en una expedición
	$sabado_g = crear( array( 'clave' => 'sabado-grande', 'fecha' => '2026-12-05', 'kg' => 20, 'cantidad' => 4 ) );
	$r        = dip_sagec_crear( $sabado_g, 'pasada', false, a_las( '2026-12-04 08:00' ) );
	ok( 'mano' === $r['estado'] && false !== strpos( implode( ' ', $r['motivos'] ), '80 kg' ) && ! peticiones_de( $sabado_g ), 'sábado de 84 kg: hacer a mano (Ecommerce, 80 kg por envío)', $r );
	$conf_def = dip_sagec_por_defecto();
	ok( '0115' === $conf_def['servicio'] && '0800' === $conf_def['servicio_sabado'], 'de serie: 0115 entre semana y 0800 el sábado' );

	// Etiquetas
	$nb  = (string) fresco( $bien )->get_meta( '_dip_mrw_envio' );
	$ne  = (string) fresco( $empresa )->get_meta( '_dip_mrw_envio' );
	$pdf = dip_sagec_etiquetas( array( $nb, $ne ) );
	ok( is_string( $pdf ) && 0 === strpos( $pdf, '%PDF' ), 'etiquetas: PDF', is_wp_error( $pdf ) ? $pdf->get_error_message() : '' );
	$xl = end( $GLOBALS['peticiones'] );
	ok( 'GetEtiquetaEnvio' === $xl['op'] && false !== strpos( $xl['cuerpo'], '<NumeroEnvio>' . $nb . ',' . $ne . '</NumeroEnvio>' ) && false !== strpos( $xl['cuerpo'], '<SeparadorNumerosEnvio>,</SeparadorNumerosEnvio>' ) && false !== strpos( $xl['cuerpo'], '<ReportTopMargin>0</ReportTopMargin>' ), 'etiquetas: las dos en una petición' );
	ok( is_wp_error( dip_sagec_etiquetas( array() ) ), 'etiquetas sin números: error' );

	// Anular
	$desde = count( $GLOBALS['correos'] );
	ok( true === dip_sagec_anular( fresco( $empresa ) ), 'anular: MRW lo anula' );
	$e = fresco( $empresa );
	ok( '' === (string) $e->get_meta( '_dip_mrw_envio' ) && ! ast( $empresa ) && $ne === (string) $e->get_meta( '_dip_sagec_anulado' ), 'anular: sin número, fuera de AST y apuntado' );
	ok( false !== strpos( end( $GLOBALS['peticiones'] )['cuerpo'], '<CancelaEnvio><NumeroEnvioOriginal>' . $ne . '</NumeroEnvioOriginal></CancelaEnvio>' ), 'anular: CancelarEnvio con el número' );
	ok( (bool) notas( $empresa, 'ya había recibido el aviso «va de camino»: avísale' ), 'anular: la nota avisa de que el cliente ya tenía el aviso' );
	ok( 'mano' === dip_sagec_crear( $e, 'pasada', false, a_las( '2026-12-02 09:00' ) )['estado'], 'anulado: la pasada no lo vuelve a crear sola' );
	ok( is_string( dip_sagec_anular( fresco( $sin_tel ) ) ), 'anular un pedido sin envío de la web: no hace nada' );

	// Cambia el pedido después de crearlo
	$desde = count( $GLOBALS['correos'] );
	$b     = fresco( $bien );
	$b->set_shipping_address_1( 'Carrer de Provença 200' );
	$b->save();
	do_action( 'woocommerce_process_shop_order_meta', $b->get_id(), get_post( $b->get_id() ) );
	ok( false !== strpos( (string) fresco( $bien )->get_meta( '_dip_sagec_revisar' ), 'ha cambiado' ), 'dirección cambiada después de crearlo: a revisar' );
	ok( 1 === count( correos_a( $avisos, $desde ) ), 'y email al correo de avisos' );
	do_action( 'woocommerce_process_shop_order_meta', $b->get_id(), get_post( $b->get_id() ) );
	ok( 1 === count( correos_a( $avisos, $desde ) ), 'guardar otra vez no repite el email' );

	// Cancelado con el envío creado y sin salir
	$cancelar = crear( array( 'clave' => 'cancelar' ) );
	dip_sagec_crear( $cancelar, 'pasada', false, a_las( '2026-12-02 09:00' ) );
	$desde = count( $GLOBALS['correos'] );
	fresco( $cancelar )->update_status( 'cancelled' );
	ok( (bool) notas( $cancelar, 'Si no va a salir, anúlalo' ) && 1 === count( correos_a( $avisos, $desde ) ), 'cancelado con envío sin salir: nota y email para anularlo' );

	// Número a mano en «Envíos de hoy»: también en AST
	dip_eh_guardar_numero( fresco( $sin_tel ), '0285 111 222', false );
	ok( array( '0285 111 222' ) === ast( $sin_tel ) && '' === (string) fresco( $sin_tel )->get_meta( '_dip_sagec_mano' ), 'número a mano: en AST y sin «Hacer a mano»', ast( $sin_tel ) );
	dip_eh_guardar_numero( fresco( $sin_tel ), '0285333444', false );
	ok( array( '0285333444' ) === ast( $sin_tel ), 'número corregido: en AST solo el nuevo', ast( $sin_tel ) );
	dip_eh_guardar_numero( fresco( $sin_tel ), '', false );
	ok( array() === ast( $sin_tel ), 'número borrado: fuera de AST' );

	// Pantalla abierta desde antes de que la web cree el envío (revisiones 0, 1 y 3)
	$viejo = crear( array( 'clave' => 'viejo' ) );
	ok( 'creado' === dip_sagec_crear( $viejo, 'pasada de las 8:00', false, a_las( '2026-12-02 08:00' ) )['estado'], 'pantalla vieja: la web crea el envío' );
	$nv = (string) fresco( $viejo )->get_meta( '_dip_mrw_envio' );
	$r  = dip_eh_guardar_numero( fresco( $viejo ), '', true, 'Envíos de hoy', '' );
	ok( 'desfasado' === $r['cambio'] && $nv === (string) fresco( $viejo )->get_meta( '_dip_mrw_envio' ) && array( $nv ) === ast( $viejo ) && '' !== (string) fresco( $viejo )->get_meta( '_dip_sagec_creado' ), '«Guardar» con el campo vacío de una página abierta antes: no borra nada', $r );
	$r = dip_eh_guardar_numero( fresco( $viejo ), '0285777888', true, 'Envíos de hoy', '' );
	ok( 'desfasado' === $r['cambio'] && $nv === (string) fresco( $viejo )->get_meta( '_dip_mrw_envio' ), 'ni lo cambia', $r );
	$r = dip_eh_guardar_numero( fresco( $viejo ), '', false, 'Envíos de hoy', $nv );
	ok( 'protegido' === $r['cambio'] && $nv === (string) fresco( $viejo )->get_meta( '_dip_mrw_envio' ), 'vaciar el campo no borra un envío creado por la web', $r );
	ok( 'protegido' === dip_eh_guardar_numero( fresco( $viejo ), '', false )['cambio'], 'tampoco sin el campo oculto' );
	// La ficha del pedido abierta antes: «Actualizar» no borra el número ni crea otro envío
	$admin         = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	$antes_usuario = get_current_user_id();
	ok( ! empty( $admin ), 'hay un administrador para probar la ficha del pedido' );
	wp_set_current_user( (int) ( $admin[0] ?? 0 ) );
	$antes_p                     = count( peticiones_de( $viejo ) );
	$_POST['dip_mrw_envio']       = '';
	$_POST['dip_mrw_envio_antes'] = '';
	do_action( 'woocommerce_process_shop_order_meta', $viejo->get_id(), get_post( $viejo->get_id() ) );
	ok( $nv === (string) fresco( $viejo )->get_meta( '_dip_mrw_envio' ) && ! wp_next_scheduled( 'dip_sagec_uno', array( $viejo->get_id() ) ) && count( peticiones_de( $viejo ) ) === $antes_p, 'ficha abierta antes de crearlo: «Actualizar» no borra el número ni crea otro envío' );
	// Un número apuntado a mano y borrado en la ficha: se borra, pero no se vuelve a crear solo en la misma petición
	$mano_f = crear( array( 'clave' => 'mano-ficha' ) );
	dip_eh_guardar_numero( fresco( $mano_f ), '0285 555 666', false );
	$_POST['dip_mrw_envio']       = '';
	$_POST['dip_mrw_envio_antes'] = '0285 555 666';
	do_action( 'woocommerce_process_shop_order_meta', $mano_f->get_id(), get_post( $mano_f->get_id() ) );
	ok( '' === (string) fresco( $mano_f )->get_meta( '_dip_mrw_envio' ) && ! empty( $GLOBALS['dip_eh_borrados'][ $mano_f->get_id() ] ) && ! wp_next_scheduled( 'dip_sagec_uno', array( $mano_f->get_id() ) ), 'número a mano borrado en la ficha: se borra y no se programa otro envío' );
	unset( $_POST['dip_mrw_envio'], $_POST['dip_mrw_envio_antes'] );
	wp_set_current_user( $antes_usuario );
	// Número de la web cambiado por uno hecho a mano (revisión 29)
	$r = dip_eh_guardar_numero( fresco( $viejo ), '0285999000', false, 'Envíos de hoy', $nv );
	$f = fresco( $viejo );
	ok( 'cambiado' === $r['cambio'] && $nv === $r['web'] && '' === (string) $f->get_meta( '_dip_sagec_creado' ) && array( '0285999000' ) === ast( $viejo ), 'número de la web cambiado a mano: ya no consta como creado por la web', $r );
	ok( ! isset( apply_filters( 'woocommerce_order_actions', array(), $f )['dip_sagec_anular'] ), 'y «Anular el envío en MRW» ya no sale (anularía el nuevo)' );
	ok( (bool) notas( $viejo, 'sigue activo en MRW' ), 'la nota dice que el de la web sigue activo en MRW' );

	// Dos pasadas a la vez: la segunda tiene la copia de antes (revisión 21)
	$doble = crear( array( 'clave' => 'doble' ) );
	$copia = fresco( $doble );
	dip_sagec_crear( fresco( $doble ), 'pasada A', false, a_las( '2026-12-02 09:00' ) );
	$r = dip_sagec_crear( $copia, 'pasada B', true, a_las( '2026-12-02 09:00' ) );
	ok( 'hecho' === $r['estado'] && 1 === count( peticiones_de( $doble ) ), 'otra pasada con la copia de antes: no crea un segundo envío', $r );

	// «Hacer a mano» de un día y el pedido pasa a otro (revisión 5)
	$cambia = crear( array( 'clave' => 'cambia-dia', 'telefono' => '' ) );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	ok( 'mano' === dip_sagec_crear( $cambia, 'pasada', false, a_las( '2026-12-02 08:00' ) )['estado'], 'sin teléfono: a mano' );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	$c2 = fresco( $cambia );
	$c2->update_meta_data( '_dip_fecha_entrega', '2026-12-04' );
	$c2->save();
	$desde = count( $GLOBALS['correos'] );
	$r     = dip_sagec_crear( fresco( $cambia ), 'pasada de las 8:00', false, a_las( '2026-12-03 08:00' ) );
	dip_sagec_email_mano();
	ok( 'mano' === $r['estado'] && 1 === count( correos_a( $avisos, $desde ) ), 'cambiado a otro día: ese día se vuelve a mirar y avisa', $r );
	ok( 'marcado' === dip_sagec_crear( fresco( $cambia ), 'pasada', false, a_las( '2026-12-03 08:05' ) )['estado'], 'el mismo día, ya marcado' );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();

	// Línea añadida en el escritorio: kilos y cajas de las líneas, no los del checkout (revisión 6)
	$lineas = crear( array( 'clave' => 'lineas', 'kg' => 20 ) );
	$l      = fresco( $lineas );
	$l->add_product( variacion( 20 ), 1 );
	$l->save();
	$d = dip_sagec_datos( fresco( $lineas ) );
	ok( 40.0 === (float) $d['kg'] && 2 === (int) $d['cajas'] && 42 === (int) $d['peso'], 'dos packs de 20 kg: 40 kg, 2 cajas y 42 kg facturables', array( $d['kg'], $d['cajas'], $d['peso'] ) );

	// Número apuntado en el cuadro de Advanced Shipment Tracking (revisión 26)
	$con_ast = crear( array( 'clave' => 'con-ast' ) );
	$ca      = fresco( $con_ast );
	$ca->update_meta_data( '_wc_shipment_tracking_items', array( array( 'tracking_provider' => 'mrw', 'tracking_number' => '0285123123', 'date_shipped' => (string) time() ) ) );
	$ca->save();
	$r = dip_sagec_crear( fresco( $con_ast ), 'pasada', false, a_las( '2026-12-02 09:00' ) );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	ok( 'mano' === $r['estado'] && ! peticiones_de( $con_ast ) && false !== strpos( implode( ' ', $r['motivos'] ), 'Advanced Shipment Tracking' ), 'con número en AST: no se crea otro envío', $r );

	// Anulado y vuelto a crear después del «va de camino»: el cliente recibe el número nuevo (revisión 28)
	$desde = count( $GLOBALS['correos'] );
	$r     = dip_sagec_crear( fresco( $empresa ), 'botón «Crear en MRW»', true, a_las( '2026-12-02 19:00' ) );
	$ne2   = (string) fresco( $empresa )->get_meta( '_dip_mrw_envio' );
	$c     = correos_a( 'empresa@sagec.test', $desde );
	ok( 'creado' === $r['estado'] && 1 === count( $c ) && '' !== $ne2 && false !== strpos( $c[0]['texto'], $ne2 ), 'anulado y vuelto a crear a las 19:00: el aviso sale otra vez con el número nuevo', array( $r, array_column( $c, 'asunto' ) ) );

	// «Crear envíos ahora» antes de las 8:00 (revisión 31)
	$temprano = crear( array( 'clave' => 'temprano' ) );
	ok( 'esperar' === dip_sagec_crear( $temprano, 'pasada', false, a_las( '2026-12-02 07:30' ) )['estado'], 'a las 7:30, sin el botón: espera a las 8:00' );
	ok( 'creado' === dip_sagec_crear( fresco( $temprano ), 'botón «Crear envíos ahora»', 'hora', a_las( '2026-12-02 07:30' ) )['estado'], 'con «Crear envíos ahora» a las 7:30: se crea' );

	// Modos: simulación, pruebas y apagado
	update_option( 'dip_sagec', array_merge( $conf, array( 'modo' => 'simulacion' ) ), false );
	$sim   = crear( array( 'clave' => 'sim' ) );
	$antes = count( $GLOBALS['peticiones'] );
	$r     = dip_sagec_crear( $sim, 'pasada', false, a_las( '2026-12-02 09:00' ) );
	$est   = (array) fresco( $sim )->get_meta( '_dip_sagec_estado' );
	ok( 'simulado' === $r['estado'] && count( $GLOBALS['peticiones'] ) === $antes && '' === (string) fresco( $sim )->get_meta( '_dip_mrw_envio' ), 'simulación: no llama a MRW ni pone número' );
	ok( false !== strpos( (string) ( $est['peticion'] ?? '' ), 'Carrer de Mallorca 123, 2º 1ª, 08013 Barcelona (BARCELONA)' ) && false !== strpos( (string) ( $est['peticion'] ?? '' ), 'salida 02/12/2026' ), 'simulación: enseña lo que mandaría', $est );
	update_option( 'dip_sagec', array_merge( $conf, array( 'modo' => 'pruebas' ) ), false );
	$desde = count( $GLOBALS['correos'] );
	$r     = dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 09:00' ) );
	ok( 'probado' === $r['estado'] && DIP_SAGEC_URL_PRUEBAS === end( $GLOBALS['peticiones'] )['url'], 'pruebas: llama a sagec-test', $r );
	ok( '' === (string) fresco( $sim )->get_meta( '_dip_mrw_envio' ) && ! ast( $sim ) && count( $GLOBALS['correos'] ) === $desde, 'pruebas: ni número en el pedido, ni AST, ni emails' );
	update_option( 'dip_sagec', array_merge( $conf, array( 'modo' => 'apagado' ) ), false );
	ok( 'apagado' === dip_sagec_crear( fresco( $sim ), 'pasada', true, a_las( '2026-12-02 09:00' ) )['estado'], 'apagado: no hace nada' );
	update_option( 'dip_sagec', array_merge( $conf, array( 'modo' => 'real', 'servicio' => '' ) ), false );
	$antes = count( $GLOBALS['peticiones'] );
	$desde = count( $GLOBALS['correos'] );
	delete_option( 'dip_sagec_aviso_falta' );
	$GLOBALS['dip_sagec_falta_aviso'] = array();
	$r = dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 09:00' ) );
	dip_sagec_email_mano();
	ok( 'falta' === $r['estado'] && count( $GLOBALS['peticiones'] ) === $antes, 'real sin código de servicio: no manda nada', $r );
	ok( '' === (string) fresco( $sim )->get_meta( '_dip_sagec_mano' ), 'y no lo deja en «Hacer a mano» (al completar Ajustes se crea solo)' );
	$aviso = correos_a( $avisos, $desde );
	ok( 1 === count( $aviso ) && false !== strpos( $aviso[0]['asunto'], 'faltan datos' ) && false !== strpos( $aviso[0]['texto'], 'código de servicio' ), 'real sin datos: email al correo de avisos (revisión 4)', array_column( $aviso, 'asunto' ) );
	$desde = count( $GLOBALS['correos'] );
	dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 10:00' ) );
	dip_sagec_email_mano();
	ok( ! correos_a( $avisos, $desde ), 'como mucho un email al día' );
	ok( (bool) array_filter( dip_sagec_problemas( a_las( '2026-12-02 09:00' ) ), static fn( $t ) => false !== strpos( $t, 'código de servicio' ) ), 'y avisa en el escritorio' );
	delete_option( 'dip_sagec_avisado' );
	$desde = count( $GLOBALS['correos'] );
	ok( dip_sagec_vigilar( a_las( '2026-12-02 09:00' ) ) && 1 === count( correos_a( $avisos, $desde ) ), 'el vigilante manda el email «la creación de envíos no funciona»' );
	ok( ! dip_sagec_vigilar( a_las( '2026-12-02 09:30' ) ), 'y no lo repite el mismo día' );
	// Sin la confirmación de MRW (UN 1845, etiqueta, en franquicia): real no crea; simulación igual que siempre (revisión 25)
	update_option( 'dip_sagec', array_merge( $conf, array( 'confirmado' => '' ) ), false );
	$antes = count( $GLOBALS['peticiones'] );
	ok( 'falta' === dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 09:00' ) )['estado'] && count( $GLOBALS['peticiones'] ) === $antes, 'real sin la confirmación de MRW: no manda nada' );
	update_option( 'dip_sagec', array_merge( $conf, array( 'confirmado' => '', 'modo' => 'simulacion' ) ), false );
	ok( 'simulado' === dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 09:00' ) )['estado'] && count( $GLOBALS['peticiones'] ) === $antes, 'simulación sin confirmar: igual que siempre' );
	update_option( 'dip_sagec', array_merge( $conf, array( 'confirmado' => '', 'modo' => 'pruebas' ) ), false );
	ok( 'probado' === dip_sagec_crear( fresco( $sim ), 'pasada', false, a_las( '2026-12-02 09:00' ) )['estado'], 'pruebas sin confirmar: llama a sagec-test' );
	update_option( 'dip_sagec', array_merge( $conf, array( 'en_franquicia' => 'S' ) ), false );
	ok( false !== strpos( dip_sagec_sobre( 'TransmEnvio', dip_sagec_peticion( dip_sagec_datos( fresco( $sim ) ) ), dip_sagec_config() ), '<EnFranquicia>S</EnFranquicia>' ), '«en franquicia» sale del ajuste' );
	update_option( 'dip_sagec', $conf, false );

	/* ---------- Pedido urgente ---------- */
	$e = dip_urgente_crear( 9.70, 'Bar La Riera', a_las( '2026-12-02 14:00' ) );
	ok( is_array( $e ) && '2026-12-03' === $e['entrega'] && '2026-12-02' === $e['dia'], 'enlace a las 14:00 del miércoles: abre el jueves', $e );
	ok( is_array( $e ) && wp_date( 'H:i', (int) $e['caduca'], dip_zona_horaria() ) === '17:30' && 32 === strlen( $e['codigo'] ), 'caduca a las 17:30 y el código es largo' );
	ok( is_wp_error( dip_urgente_crear( 9.70, '', a_las( '2026-12-02 17:40' ) ) ), 'a las 17:40 ya no se puede crear' );
	ok( is_wp_error( dip_urgente_crear( 9.70, '', a_las( '2026-12-05 10:00' ) ) ), 'en sábado no se puede crear (no sale nada)' );
	ok( is_wp_error( dip_urgente_crear( 9.70, '', a_las( '2026-12-08 10:00' ) ) ), 'en festivo (8/12) no se puede crear' );
	$msg = dip_urgente_mensaje( $e );
	ok( false !== strpos( $msg, 'jueves 3 de diciembre' ) && false !== strpos( $msg, '17:30' ) && false !== strpos( $msg, '11,74 €' ) && false !== strpos( $msg, '?dip_urgente=' . $e['codigo'] ), 'el mensaje para el cliente dice día, hora límite, suplemento y enlace', $msg );
	ok( false === strpos( $msg, '!' ), 'sin signos de exclamación' );

	$sin = dip_fechas_disponibles( 'envio', 1, a_las( '2026-12-02 14:00' ) );
	ok( '2026-12-04' === ( $sin[0]['fecha'] ?? '' ), 'sin enlace, a las 14:00 la primera entrega es el viernes', $sin );
	ok( 720 === dip_corte_minutos( 'envio', a_las( '2026-12-02 14:00' ) ), 'sin enlace, el corte es a las 12:00' );
	$_COOKIE['dip_urgente'] = $e['codigo'];
	$con = dip_fechas_disponibles( 'envio', 1, a_las( '2026-12-02 14:00' ) );
	ok( '2026-12-03' === ( $con[0]['fecha'] ?? '' ), 'con el enlace, a las 14:00 la primera entrega es el jueves', $con );
	ok( 1050 === dip_corte_minutos( 'envio', a_las( '2026-12-02 14:00' ) ) && 720 === dip_corte_minutos( 'recogida', a_las( '2026-12-02 14:00' ) ), 'con el enlace: corte a las 17:30 para el envío, la recogida igual' );
	ok( 720 === dip_corte_minutos( 'envio', a_las( '2026-12-03 14:00' ) ), 'el enlace solo vale el día en que se creó' );
	ok( dip_fecha_es_valida( '2026-12-03', 'envio', null, a_las( '2026-12-02 14:00' ) ), 'el jueves se puede elegir (validación del checkout)' );
	ok( ! dip_fecha_es_valida( '2026-12-03', 'envio', null, a_las( '2026-12-02 17:40' ) ), 'a las 17:40 el jueves ya no se puede elegir' );
	ok( dip_urgente_hace_falta( '2026-12-03', $e, a_las( '2026-12-02 14:00' ) ) && ! dip_urgente_hace_falta( '2026-12-03', $e, a_las( '2026-12-02 11:00' ) ) && ! dip_urgente_hace_falta( '2026-12-04', $e, a_las( '2026-12-02 14:00' ) ), 'el suplemento solo se cobra después de las 12:00 y para el día del enlace' );
	$l = dip_urgente_lista();
	$l[ $e['codigo'] ]['pedido'] = 999999;
	update_option( 'dip_urgentes', $l, false );
	ok( null === dip_urgente_activo(), 'enlace usado por otro pedido: ya no vale' );
	$l[ $e['codigo'] ]['pedido']  = 0;
	$l[ $e['codigo'] ]['anulado'] = time();
	update_option( 'dip_urgentes', $l, false );
	ok( null === dip_urgente_activo() && 'anulado' === dip_urgente_estado( $l[ $e['codigo'] ] ), 'enlace anulado: ya no vale' );
	unset( $_COOKIE['dip_urgente'] );
	$_COOKIE['dip_urgente'] = str_repeat( 'a', 32 );
	ok( null === dip_urgente_activo(), 'un código inventado no vale' );
	unset( $_COOKIE['dip_urgente'] );

	// «Adelantar a mañana»
	$adelantar = crear( array( 'clave' => 'adelantar', 'fecha' => '2026-12-09' ) );
	ok( true === dip_urgente_adelantar( $adelantar, a_las( '2026-12-02 15:00' ) ), 'adelantar a las 15:00' );
	$a = fresco( $adelantar );
	ok( '2026-12-03' === (string) $a->get_meta( '_dip_fecha_entrega' ) && '' !== (string) $a->get_meta( '_dip_mrw_envio' ), 'adelantado al jueves y creado en MRW', array( $a->get_meta( '_dip_fecha_entrega' ), $a->get_meta( '_dip_mrw_envio' ) ) );
	ok( (bool) notas( $adelantar, 'Entrega adelantada del miércoles 9 de diciembre al jueves 3 de diciembre' ), 'nota con el día de antes y el nuevo' );
	$adelantar2 = crear( array( 'clave' => 'adelantar2', 'fecha' => '2026-12-09' ) );
	ok( is_string( dip_urgente_adelantar( $adelantar2, a_las( '2026-12-02 17:45' ) ) ) && '2026-12-09' === (string) fresco( $adelantar2 )->get_meta( '_dip_fecha_entrega' ), 'a las 17:45 ya no se adelanta' );

	/* ---------- Garantía de la hora pagada: devolver el envío entero ---------- */
	$tarde10 = crear( array( 'clave' => 'tarde10' ) );
	$tarde10->update_meta_data( '_dip_servicio', '10' );
	$tarde10->save();
	$envio_total = round( (float) $tarde10->get_shipping_total() + (float) $tarde10->get_shipping_tax(), 2 );
	$acciones    = apply_filters( 'woocommerce_order_actions', array(), fresco( $tarde10 ) );
	ok( isset( $acciones['dip_devolver_envio'] ) && false !== strpos( $acciones['dip_devolver_envio'], 'antes de las 10:00' ), 'acción «Devolver el envío entero (no llegó antes de las 10:00)»', $acciones );
	ok( true === dip_devolver_envio( fresco( $tarde10 ) ), 'devolver el envío: hecho' );
	$f = fresco( $tarde10 );
	ok( abs( (float) $f->get_total_refunded() - $envio_total ) < 0.01 && $envio_total > 13, 'se devuelve el envío entero con IVA (' . $envio_total . ' €)', (float) $f->get_total_refunded() );
	ok( '' !== (string) $f->get_meta( '_dip_envio_devuelto' ) && (bool) notas( $tarde10, 'no llegó antes de las 10:00' ), 'queda apuntado y con nota' );
	ok( (bool) notas( $tarde10, 'devuélvelo a mano' ), 'con una forma de pago que no devuelve sola (cheque), la nota dice que se haga a mano' );
	ok( is_string( dip_devolver_envio( fresco( $tarde10 ) ) ), 'no se devuelve dos veces' );
	ok( ! isset( apply_filters( 'woocommerce_order_actions', array(), fresco( $tarde10 ) )['dip_devolver_envio'] ), 'y la acción ya no sale' );
	ok( ! isset( apply_filters( 'woocommerce_order_actions', array(), fresco( $recogida ) )['dip_devolver_envio'] ), 'en una recogida no sale' );
	// Pedido de antes del 11/11 con el cargo «Entrega en sábado»: también se devuelve, como promete el email de incidencia (revisión 27)
	$sab_viejo = crear( array( 'clave' => 'sabado-antiguo', 'fecha' => '2026-12-05' ) );
	$fee       = new WC_Order_Item_Fee();
	$fee->set_name( 'Entrega en sábado' );
	$fee->set_total( 9.70 );
	$fee->set_tax_status( 'taxable' );
	$vs = fresco( $sab_viejo );
	$vs->add_item( $fee );
	$vs->calculate_totals( true );
	$vs->save();
	$vs       = fresco( $sab_viejo );
	$esperado = round( (float) $vs->get_shipping_total() + (float) $vs->get_shipping_tax() + array_sum( array_map( static fn( $i ) => (float) $i->get_total() + (float) $i->get_total_tax(), $vs->get_items( 'fee' ) ) ), 2 );
	ok( true === dip_devolver_envio( $vs ), 'devolver el envío con el cargo de sábado: hecho' );
	ok( abs( (float) fresco( $sab_viejo )->get_total_refunded() - $esperado ) < 0.01 && $esperado > 22, 'se devuelven el envío y el cargo «Entrega en sábado» (' . $esperado . ' €)', (float) fresco( $sab_viejo )->get_total_refunded() );
	// Pedido urgente: el suplemento se devuelve con el envío, como promete el email de incidencia (dip_avisos_suplemento_urgente)
	$urg_dev = crear( array( 'clave' => 'urgente-devolver' ) );
	$fee     = new WC_Order_Item_Fee();
	$fee->set_name( 'Pedido urgente' );
	$fee->set_total( 9.70 );
	$fee->set_tax_status( 'taxable' );
	$fee->add_meta_data( '_dip_urgente', '1', true );
	$vu = fresco( $urg_dev );
	$vu->add_item( $fee );
	$vu->calculate_totals( true );
	$vu->save();
	$vu       = fresco( $urg_dev );
	$esperado = round( (float) $vu->get_shipping_total() + (float) $vu->get_shipping_tax() + array_sum( array_map( static fn( $i ) => (float) $i->get_total() + (float) $i->get_total_tax(), $vu->get_items( 'fee' ) ) ), 2 );
	ok( true === dip_devolver_envio( $vu ) && abs( (float) fresco( $urg_dev )->get_total_refunded() - $esperado ) < 0.01 && $esperado > 22, 'devolver el envío de un pedido urgente: también el suplemento «Pedido urgente» (' . $esperado . ' €)', (float) fresco( $urg_dev )->get_total_refunded() );

	/* ---------- Pasada programada ---------- */
	ok( false !== wp_next_scheduled( 'dip_sagec_pasada' ) && false !== wp_next_scheduled( 'dip_sagec_salida' ), 'las tareas de las 8:00 y las 18:30 están programadas' );
	$h = wp_date( 'H:i', (int) wp_next_scheduled( 'dip_sagec_pasada' ), dip_zona_horaria() );
	ok( '08:00' === $h, 'la pasada es a las 8:00 de Madrid', $h );
	$h = wp_date( 'H:i', (int) wp_next_scheduled( 'dip_sagec_salida' ), dip_zona_horaria() );
	ok( '18:30' === $h, 'el aviso es a las 18:30 de Madrid', $h );
} catch ( Throwable $t ) {
	ok( false, 'excepción: ' . $t->getMessage() . ' en ' . basename( $t->getFile() ) . ':' . $t->getLine() );
} finally {
	limpiar();
}
echo $GLOBALS['pruebas'] . ' comprobaciones, ' . $GLOBALS['fallos'] . " fallos\n";
