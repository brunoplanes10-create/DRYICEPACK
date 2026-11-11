<?php
/**
 * Envíos en MRW con su servicio web SAGEC (11/11/2026): la web crea el envío el día de salida, guarda el número y saca
 * las etiquetas. Sustituye a apuntar el número a mano (inc/envios-hoy.php sigue sirviendo para eso).
 *
 * Servicio web (WSDL de https://sagec.mrw.es/MRWEnvio.asmx?WSDL, descargado el 11/11/2026; pruebas en sagec-test.mrw.es):
 * - TransmEnvio: crea el envío. Cabecera AuthInfo (CodigoFranquicia, CodigoAbonado, CodigoDepartamento, UserName,
 *   Password). Petición: DatosEntrega (Direccion, Nif, Nombre, Telefono, Contacto, ALaAtencionDe, Observaciones) y
 *   DatosServicio (Fecha, Referencia, CodigoServicio, NumeroBultos, Peso, EntregaSabado, TipoMercancia, ServicioEspecial…).
 *   Respuesta: Estado, Mensaje, NumeroSolicitud, NumeroEnvio, Url. Sin DatosRecogida: MRW usa la dirección del abonado.
 * - GetEtiquetaEnvio: etiqueta en PDF (EtiquetaFile, base64) de uno o varios envíos (SeparadorNumerosEnvio).
 * - CancelarEnvio: anula un envío (CancelaEnvio/NumeroEnvioOriginal).
 * Lo que solo da MRW y va en Ajustes → MRW: las claves, los códigos de servicio (urgente 14:00 y sábado), cómo se
 * declara el hielo seco (UN 1845: TipoMercancia o ServicioEspecial) y el tipo de etiqueta. Mientras falte algo, no crea nada.
 *
 * Cuándo (decidido con el propietario el 11/11/2026):
 * - El día de salida a las 8:00, todos los pedidos completos que salen ese día (también los reservados con antelación).
 * - Durante el día, cada pedido que queda listo para salir hoy, al momento (pedido de la web antes de las 12:00, enlace
 *   urgente de inc/urgente.php, «Adelantar a mañana», un pedido creado o cambiado en el escritorio), hasta las 17:30
 *   (Ajustes → Hora límite): el propietario lleva las cajas a MRW, cuya última furgoneta sale a las 18:30.
 * - El aviso «va de camino» al cliente NO sale al crear el envío (dice «ha salido de Mataró con MRW»): sale a las 18:30
 *   (Ajustes → Hora de entrega a MRW), con todos los del día, o antes con «Ya están en MRW: avisar». Hasta entonces el
 *   envío se puede anular y el cliente no recibe nada. Un envío creado después de esa hora avisa al momento.
 * - Si falta algo o hay dudas (dirección, teléfono, kilos, nota del cliente, festivo, más de 150 kg fuera de Barcelona…),
 *   no se crea: el pedido queda «Hacer a mano» con el motivo y llega un email al correo de avisos.
 *
 * Número: _dip_mrw_envio (como «Envíos de hoy») y Advanced Shipment Tracking (_wc_shipment_tracking_items), de donde lo
 * lee la app INDUNOVA Control (el último tracking_number). _dip_enviado se pone al avisar («ha salido»), no al crear.
 * Claves del pedido de este módulo: _dip_sagec_creado ('AAAA-MM-DD HH:MM:SS' de Madrid), _dip_sagec_solicitud,
 * _dip_sagec_huella (datos con los que se creó), _dip_sagec_mano (motivo de «Hacer a mano»), _dip_sagec_revisar
 * (cambió después de crearlo), _dip_sagec_estado (última comprobación, también en simulación y pruebas).
 *
 * Modos (Ajustes → MRW): apagado · simulación (de serie: comprueba y enseña lo que mandaría, sin llamar a MRW) ·
 * pruebas (llama a sagec-test y guarda la respuesta en _dip_sagec_estado, sin número en el pedido ni emails) · real.
 * Las claves solo están en el servidor: en wp-config.php (DIP_MRW_FRANQUICIA, DIP_MRW_ABONADO, DIP_MRW_DEPARTAMENTO,
 * DIP_MRW_USUARIO, DIP_MRW_CLAVE) o en Ajustes → MRW (la contraseña, cifrada con las claves de WordPress y nunca se enseña).
 * Toda tarea que falla avisa por email y tiene alternativa manual: «Crear envíos ahora», «Hacer a mano» y apuntar el
 * número en «Envíos de hoy».
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_SAGEC_URL_REAL' ) )    define( 'DIP_SAGEC_URL_REAL', 'https://sagec.mrw.es/MRWEnvio.asmx' );
if ( ! defined( 'DIP_SAGEC_URL_PRUEBAS' ) ) define( 'DIP_SAGEC_URL_PRUEBAS', 'https://sagec-test.mrw.es/MRWEnvio.asmx' );
if ( ! defined( 'DIP_SAGEC_NS' ) )          define( 'DIP_SAGEC_NS', 'http://www.mrw.es/' );
if ( ! defined( 'DIP_SAGEC_HORA_PASADA' ) ) define( 'DIP_SAGEC_HORA_PASADA', '08:00' ); // el día de salida
if ( ! defined( 'DIP_SAGEC_KG_CAJA' ) )     define( 'DIP_SAGEC_KG_CAJA', 30 );           // como mucho, kilos por caja
if ( ! defined( 'DIP_SAGEC_EN_ESPERA' ) )   define( 'DIP_SAGEC_EN_ESPERA', 'el pedido está «En espera»: el pago no está confirmado' );

/* =========================================================
 * Ajustes y claves (solo en el servidor)
 * ========================================================= */

function dip_sagec_por_defecto() {
	return array(
		'modo'              => 'simulacion',
		'franquicia'        => '',
		'abonado'           => '',
		'departamento'      => '',
		'usuario'           => '',
		'clave'             => '', // cifrada (dip_sagec_cifrar)
		'servicio'          => '', // urgente antes de las 14:00 (lo da MRW)
		'servicio_sabado'   => '', // sábado antes de las 12:00; vacío = el mismo con EntregaSabado
		'tipo_mercancia'    => '',
		'servicio_especial' => '',
		'tipo_etiqueta'     => '',
		'observaciones'     => 'Hielo seco (UN 1845) en caja de poliestireno.',
	);
}

/** Ajustes de MRW con las claves de wp-config.php por encima. La contraseña va descifrada en 'clave'. */
function dip_sagec_config() {
	$c = wp_parse_args( (array) get_option( 'dip_sagec', array() ), dip_sagec_por_defecto() );
	$c['clave'] = dip_sagec_descifrar( (string) $c['clave'] );
	foreach ( dip_sagec_constantes() as $campo => $constante ) {
		if ( defined( $constante ) && '' !== (string) constant( $constante ) ) $c[ $campo ] = (string) constant( $constante );
	}
	if ( ! in_array( $c['modo'], array( 'apagado', 'simulacion', 'pruebas', 'real' ), true ) ) $c['modo'] = 'simulacion';
	return $c;
}

function dip_sagec_constantes() {
	return array( 'franquicia' => 'DIP_MRW_FRANQUICIA', 'abonado' => 'DIP_MRW_ABONADO', 'departamento' => 'DIP_MRW_DEPARTAMENTO', 'usuario' => 'DIP_MRW_USUARIO', 'clave' => 'DIP_MRW_CLAVE' );
}

function dip_sagec_modo() {
	return dip_sagec_config()['modo'];
}

/** Lo que falta para llamar a MRW (lista vacía si está todo). El departamento puede ir vacío. */
function dip_sagec_falta( ?array $c = null ) {
	$c      = $c ?? dip_sagec_config();
	$nombre = array( 'franquicia' => 'código de franquicia', 'abonado' => 'código de abonado', 'usuario' => 'usuario', 'clave' => 'contraseña', 'servicio' => 'código de servicio' );
	$r      = array();
	foreach ( $nombre as $campo => $texto ) {
		if ( '' === trim( (string) $c[ $campo ] ) ) $r[] = $texto;
	}
	return $r;
}

function dip_sagec_clave_cifrado() {
	return hash( 'sha256', ( function_exists( 'wp_salt' ) ? wp_salt( 'auth' ) : '' ) . '|dip_sagec', true );
}

function dip_sagec_cifrar( $texto ) {
	$texto = (string) $texto;
	if ( '' === $texto ) return '';
	if ( ! function_exists( 'openssl_encrypt' ) ) return 'b64:' . base64_encode( $texto );
	$iv = random_bytes( 16 );
	$c  = openssl_encrypt( $texto, 'aes-256-cbc', dip_sagec_clave_cifrado(), OPENSSL_RAW_DATA, $iv );
	return false === $c ? 'b64:' . base64_encode( $texto ) : 'aes:' . base64_encode( $iv . $c );
}

function dip_sagec_descifrar( $guardado ) {
	$guardado = (string) $guardado;
	if ( 0 === strpos( $guardado, 'b64:' ) ) return (string) base64_decode( substr( $guardado, 4 ) );
	if ( 0 !== strpos( $guardado, 'aes:' ) || ! function_exists( 'openssl_decrypt' ) ) return '';
	$bin = (string) base64_decode( substr( $guardado, 4 ) );
	if ( strlen( $bin ) <= 16 ) return '';
	$t = openssl_decrypt( substr( $bin, 16 ), 'aes-256-cbc', dip_sagec_clave_cifrado(), OPENSSL_RAW_DATA, substr( $bin, 0, 16 ) );
	return false === $t ? '' : $t;
}

/** Hora límite para crear envíos y aceptar urgentes ('HH:MM', Ajustes; de serie 17:30). */
function dip_sagec_hora_limite() {
	$h = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'mrw_hora_limite' ) : '';
	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $h ) ? $h : '17:30';
}

/** Hora a la que MRW ya tiene las cajas: sale el aviso «va de camino» ('HH:MM', Ajustes; de serie 18:30). */
function dip_sagec_hora_camino() {
	$h = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'mrw_hora_camino' ) : '';
	return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $h ) ? $h : '18:30';
}

function dip_sagec_minutos( $hora ) {
	list( $h, $m ) = array_map( 'intval', explode( ':', (string) $hora ) + array( 0, 0 ) );
	return $h * 60 + $m;
}

function dip_sagec_minuto_del_dia( DateTimeImmutable $ahora ) {
	$a = $ahora->setTimezone( dip_zona_horaria() );
	return (int) $a->format( 'G' ) * 60 + (int) $a->format( 'i' );
}

function dip_sagec_ahora( ?DateTimeImmutable $ahora = null ) {
	return ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
}

/* =========================================================
 * SOAP
 * ========================================================= */

function dip_sagec_es_lista( array $a ) {
	return array() === $a || array_keys( $a ) === range( 0, count( $a ) - 1 );
}

/** XML de un nodo: los valores vacíos no se mandan; una lista repite el nodo. */
function dip_sagec_nodo( $nombre, $valor ) {
	if ( is_array( $valor ) ) {
		if ( dip_sagec_es_lista( $valor ) ) {
			$x = '';
			foreach ( $valor as $v ) $x .= dip_sagec_nodo( $nombre, $v );
			return $x;
		}
		$dentro = '';
		foreach ( $valor as $k => $v ) $dentro .= dip_sagec_nodo( (string) $k, $v );
		return '' === $dentro ? '' : '<' . $nombre . '>' . $dentro . '</' . $nombre . '>';
	}
	$valor = (string) $valor;
	if ( '' === $valor ) return '';
	return '<' . $nombre . '>' . htmlspecialchars( $valor, ENT_XML1 | ENT_QUOTES, 'UTF-8' ) . '</' . $nombre . '>';
}

function dip_sagec_sobre( $operacion, array $request, array $c ) {
	$auth = dip_sagec_nodo( 'CodigoFranquicia', $c['franquicia'] )
		. dip_sagec_nodo( 'CodigoAbonado', $c['abonado'] )
		. dip_sagec_nodo( 'CodigoDepartamento', $c['departamento'] )
		. dip_sagec_nodo( 'UserName', $c['usuario'] )
		. dip_sagec_nodo( 'Password', $c['clave'] );
	return '<?xml version="1.0" encoding="utf-8"?>'
		. '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
		. '<soap:Header><AuthInfo xmlns="' . DIP_SAGEC_NS . '">' . $auth . '</AuthInfo></soap:Header>'
		. '<soap:Body><' . $operacion . ' xmlns="' . DIP_SAGEC_NS . '">' . dip_sagec_nodo( 'request', $request ) . '</' . $operacion . '></soap:Body>'
		. '</soap:Envelope>';
}

/**
 * Llama a una operación de SAGEC. $resultado: el nodo de la respuesta (TransmEnvioResult…).
 * @return array{ok: bool, datos: array<string,string>, error: string, http: int}
 *         datos: los hijos del resultado por nombre (Estado, Mensaje, NumeroEnvio…). ok: hay resultado y Estado no es 0.
 */
function dip_sagec_llamar( $operacion, $resultado, array $request, ?array $c = null ) {
	$c   = $c ?? dip_sagec_config();
	$url = 'real' === $c['modo'] ? DIP_SAGEC_URL_REAL : DIP_SAGEC_URL_PRUEBAS;
	$r   = array( 'ok' => false, 'datos' => array(), 'error' => '', 'http' => 0 );
	if ( ! class_exists( 'DOMDocument' ) ) {
		$r['error'] = 'falta la extensión DOM de PHP en el servidor';
		return $r;
	}
	$resp = wp_remote_post( $url, array(
		'timeout' => 30,
		'headers' => array( 'Content-Type' => 'text/xml; charset=utf-8', 'SOAPAction' => '"' . DIP_SAGEC_NS . $operacion . '"' ),
		'body'    => dip_sagec_sobre( $operacion, $request, $c ),
	) );
	if ( is_wp_error( $resp ) ) {
		$r['error'] = 'MRW no responde (' . $resp->get_error_message() . ')';
		return $r;
	}
	$r['http'] = (int) wp_remote_retrieve_response_code( $resp );
	$cuerpo    = (string) wp_remote_retrieve_body( $resp );
	$dom       = new DOMDocument();
	$antes     = libxml_use_internal_errors( true );
	$leido     = '' !== trim( $cuerpo ) && $dom->loadXML( $cuerpo, LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $antes );
	if ( ! $leido ) {
		$r['error'] = 'MRW ha respondido algo que no se puede leer (HTTP ' . $r['http'] . ')';
		return $r;
	}
	$xp    = new DOMXPath( $dom );
	$fallo = $xp->query( '//*[local-name()="Fault"]/*[local-name()="faultstring" or local-name()="Reason"]' );
	if ( $fallo && $fallo->length ) {
		$r['error'] = 'MRW: ' . trim( preg_replace( '/\s+/', ' ', $fallo->item( 0 )->textContent ) );
		return $r;
	}
	$nodo = $xp->query( '//*[local-name()="' . $resultado . '"]' );
	if ( ! $nodo || ! $nodo->length ) {
		$r['error'] = 'MRW no ha devuelto ' . $resultado . ' (HTTP ' . $r['http'] . ')';
		return $r;
	}
	foreach ( $nodo->item( 0 )->childNodes as $hijo ) {
		if ( XML_ELEMENT_NODE === $hijo->nodeType ) $r['datos'][ $hijo->localName ] = trim( $hijo->textContent );
	}
	$estado = (string) ( $r['datos']['Estado'] ?? '' );
	$r['ok'] = '0' !== $estado;
	if ( ! $r['ok'] ) $r['error'] = 'MRW: ' . ( '' !== (string) ( $r['datos']['Mensaje'] ?? '' ) ? $r['datos']['Mensaje'] : 'no lo acepta (Estado 0)' );
	return $r;
}

/* =========================================================
 * Datos del pedido
 * ========================================================= */

/** Calle, número y resto de una línea de dirección: «Carrer Major 12» → [Carrer Major, 12, '']. Sin número claro: todo en la calle. */
function dip_sagec_partir_direccion( $linea ) {
	$linea = trim( (string) preg_replace( '/\s+/u', ' ', (string) $linea ) );
	if ( preg_match( '/^(.+?)[,\s]+(?:n[º°o.]\s*)?(\d{1,4}[A-Za-z]?)\s*(?:,\s*(.+))?$/u', $linea, $m ) && preg_match( '/\pL/u', $m[1] ) ) {
		return array( trim( $m[1], ' ,' ), $m[2], trim( (string) ( $m[3] ?? '' ) ) );
	}
	return array( $linea, '', '' );
}

/** Teléfono para MRW: solo cifras, sin el +34 delante. */
function dip_sagec_telefono( $tel ) {
	$t = preg_replace( '/\D/', '', (string) $tel );
	if ( 11 === strlen( $t ) && 0 === strpos( $t, '34' ) ) $t = substr( $t, 2 );
	if ( 13 === strlen( $t ) && 0 === strpos( $t, '0034' ) ) $t = substr( $t, 4 );
	return $t;
}

/** Datos del envío de un pedido, ya limpios. */
function dip_sagec_datos( WC_Order $p ) {
	$envio    = '' !== trim( $p->get_shipping_address_1() . $p->get_shipping_postcode() );
	$nombre   = trim( (string) preg_replace( '/\s+/u', ' ', $envio ? $p->get_shipping_first_name() . ' ' . $p->get_shipping_last_name() : '' ) );
	$nombre   = $nombre ?: trim( (string) preg_replace( '/\s+/u', ' ', $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() ) );
	$empresa  = trim( (string) ( $envio ? $p->get_shipping_company() : '' ) ) ?: trim( (string) $p->get_billing_company() );
	$linea1   = trim( (string) ( $envio ? $p->get_shipping_address_1() : $p->get_billing_address_1() ) );
	$linea2   = trim( (string) ( $envio ? $p->get_shipping_address_2() : $p->get_billing_address_2() ) );
	$cp       = preg_replace( '/\D/', '', (string) ( $envio ? $p->get_shipping_postcode() : $p->get_billing_postcode() ) );
	$ciudad   = trim( (string) ( $envio ? $p->get_shipping_city() : $p->get_billing_city() ) );
	$pais     = (string) ( $envio ? $p->get_shipping_country() : $p->get_billing_country() );
	$tel      = method_exists( $p, 'get_shipping_phone' ) && '' !== trim( (string) $p->get_shipping_phone() ) ? $p->get_shipping_phone() : $p->get_billing_phone();
	$kg       = function_exists( 'dip_eh_kg' ) ? (float) dip_eh_kg( $p ) : 0.0;
	$cajas    = function_exists( 'dip_eh_cajas' ) ? (int) dip_eh_cajas( $p ) : 0;
	$entrega  = function_exists( 'dip_eh_fecha' ) ? (string) dip_eh_fecha( $p ) : (string) $p->get_meta( '_dip_fecha_entrega' );
	list( $via, $numero, $resto ) = dip_sagec_partir_direccion( $linea1 );
	$resto    = trim( $resto . ( $resto && $linea2 ? ', ' : '' ) . $linea2 );
	$provincia = 5 === strlen( $cp ) && function_exists( 'dip_mrw_provincia_por_cp' ) ? (string) dip_mrw_provincia_por_cp( $cp ) : '';
	return array(
		'nombre'    => $empresa ?: $nombre,
		'atencion'  => $empresa && $nombre && $empresa !== $nombre ? $nombre : '',
		'contacto'  => $nombre,
		'linea1'    => $linea1,
		'via'       => $via,
		'numero'    => $numero,
		'resto'     => $resto,
		'cp'        => $cp,
		'ciudad'    => $ciudad,
		'provincia' => $provincia,
		'pais'      => $pais ?: 'ES',
		'telefono'  => dip_sagec_telefono( $tel ),
		'kg'        => $kg,
		'cajas'     => $cajas,
		'peso'      => $kg > 0 ? (int) ceil( $kg + ( defined( 'DIP_PESO_EMBALAJE' ) ? DIP_PESO_EMBALAJE : 1 ) * max( 1, $cajas ) ) : 0,
		'entrega'   => $entrega,
		'salida'    => function_exists( 'dip_eh_salida_de' ) ? (string) dip_eh_salida_de( $entrega ) : '',
		'sabado'    => function_exists( 'dip_eh_es_sabado' ) && dip_eh_es_sabado( $entrega ),
		'nota'      => trim( (string) $p->get_customer_note() ),
		'referencia' => (string) $p->get_order_number(),
	);
}

/** Huella de lo que manda en el envío: si cambia después de crearlo, hay que revisarlo. */
function dip_sagec_huella( array $d ) {
	return md5( implode( '|', array( $d['entrega'], $d['nombre'], $d['linea1'], $d['resto'], $d['cp'], $d['ciudad'], $d['telefono'], $d['kg'], $d['cajas'] ) ) );
}

/**
 * ¿Se puede crear hoy el envío de este pedido?
 * @return array{estado: string, motivos: string[], datos: array}
 *   estado: listo · mano (falta algo o hay dudas: motivos) · esperar (antes de las 8:00) · hecho (ya tiene número) ·
 *   marcado (ya está en «Hacer a mano») · fuera (no le toca hoy: recogida, otro día, sin pagar…).
 *   $forzar (botón): sin las horas ni la marca de «Hacer a mano»; el resto de comprobaciones, igual.
 */
function dip_sagec_revisar( WC_Order $p, ?DateTimeImmutable $ahora = null, $forzar = false ) {
	$ahora = dip_sagec_ahora( $ahora );
	$hoy   = $ahora->format( 'Y-m-d' );
	$r     = array( 'estado' => 'fuera', 'motivos' => array(), 'datos' => array() );
	if ( 'shop_order' !== $p->get_type() || ( function_exists( 'dip_eh_es_recogida' ) && dip_eh_es_recogida( $p ) ) ) return $r;
	if ( ! in_array( $p->get_status(), array( 'processing', 'on-hold' ), true ) ) return $r;
	$d          = dip_sagec_datos( $p );
	$r['datos'] = $d;
	if ( '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) return array_merge( $r, array( 'estado' => 'hecho' ) );
	// Otro día (también con el botón): no se marca nada, solo se dice por qué
	if ( '' === $d['entrega'] ) return array_merge( $r, array( 'motivos' => array( 'el pedido no tiene día de entrega' ) ) );
	if ( $d['salida'] > $hoy ) return array_merge( $r, array( 'motivos' => array( 'no sale hoy: sale el ' . dip_fecha_larga( $d['salida'] ) . '. Si tiene que salir hoy, usa «Adelantar a mañana»' ) ) );
	if ( $d['salida'] < $hoy ) return array_merge( $r, array( 'motivos' => array( 'tenía que salir el ' . dip_fecha_larga( $d['salida'] ) . ': cambia el día de entrega' ) ) );
	if ( ! $forzar && '' !== (string) $p->get_meta( '_dip_sagec_mano' ) ) return array_merge( $r, array( 'estado' => 'marcado' ) );
	if ( ! $forzar && dip_sagec_minuto_del_dia( $ahora ) < dip_sagec_minutos( DIP_SAGEC_HORA_PASADA ) ) return array_merge( $r, array( 'estado' => 'esperar' ) );

	$m = array();
	if ( 'on-hold' === $p->get_status() ) $m[] = DIP_SAGEC_EN_ESPERA; // completo y confirmado: sin pago confirmado, no sale solo
	if ( ! $forzar && dip_sagec_minuto_del_dia( $ahora ) >= dip_sagec_minutos( dip_sagec_hora_limite() ) ) $m[] = 'ha quedado listo después de las ' . dip_sagec_hora_limite() . ', la hora límite para llevarlo a MRW';
	$salida = DateTimeImmutable::createFromFormat( '!Y-m-d', $d['salida'], dip_zona_horaria() );
	if ( $salida && function_exists( 'dip_es_dia_de_salida' ) && ! dip_es_dia_de_salida( $salida ) ) $m[] = 'hoy no es día de salida (fin de semana, festivo o cierre de MRW Mataró)';
	if ( $d['provincia'] && function_exists( 'dip_mrw_cierra' ) && dip_mrw_cierra( $d['entrega'], $d['provincia'], $d['ciudad'] ) ) $m[] = 'MRW no reparte el ' . dip_fecha_larga( $d['entrega'] ) . ' en ' . ( $d['ciudad'] ?: $d['cp'] ) . ' (festivo)';
	if ( 'ES' !== strtoupper( $d['pais'] ) ) $m[] = 'la dirección no es de España';
	if ( '' === $d['linea1'] || ! preg_match( '/\pL/u', $d['linea1'] ) ) $m[] = 'falta la calle';
	if ( 5 !== strlen( $d['cp'] ) ) $m[] = 'el código postal no tiene 5 cifras';
	elseif ( function_exists( 'dip_cp_fuera_de_peninsula' ) && dip_cp_fuera_de_peninsula( $d['cp'] ) ) $m[] = 'el código postal es de ' . dip_cp_fuera_de_peninsula( $d['cp'] ) . ': solo enviamos a la península, este pedido no se envía (llama al cliente: corregir la dirección o reembolso)';
	elseif ( '' === $d['provincia'] ) $m[] = 'el código postal no es de ninguna provincia de MRW';
	if ( '' === $d['ciudad'] ) $m[] = 'falta la población';
	if ( '' === $d['nombre'] ) $m[] = 'falta el nombre del destinatario';
	if ( strlen( $d['telefono'] ) < 9 || strlen( $d['telefono'] ) > 12 ) $m[] = 'el teléfono no es válido (' . ( $d['telefono'] ?: 'vacío' ) . ')';
	if ( $d['kg'] <= 0 || $d['cajas'] < 1 ) $m[] = 'no se saben los kilos o las cajas';
	if ( defined( 'DIP_MAX_KG' ) && $d['kg'] > DIP_MAX_KG + 0.0001 ) $m[] = 'más de ' . DIP_MAX_KG . ' kg';
	if ( $d['cajas'] > 0 && $d['kg'] / $d['cajas'] > DIP_SAGEC_KG_CAJA + 0.0001 ) $m[] = 'más de ' . DIP_SAGEC_KG_CAJA . ' kg por caja';
	if ( function_exists( 'dip_envio_a_medida' ) && dip_envio_a_medida( $d['kg'], $d['cp'] ) ) $m[] = 'más de ' . DIP_KG_A_MEDIDA . ' kg fuera de la provincia de Barcelona: va a medida';
	if ( '' !== $d['nota'] ) $m[] = 'el cliente ha dejado una nota: «' . mb_substr( $d['nota'], 0, 120 ) . '»';
	if ( '' !== (string) $p->get_meta( '_dip_sagec_anulado' ) && ! $forzar ) $m[] = 'ya se anuló un envío de este pedido en MRW';
	return array_merge( $r, array( 'estado' => $m ? 'mano' : 'listo', 'motivos' => $m ) );
}

/** Petición de TransmEnvio de un pedido. */
function dip_sagec_peticion( array $d, ?array $c = null ) {
	$c      = $c ?? dip_sagec_config();
	$salida = DateTimeImmutable::createFromFormat( '!Y-m-d', $d['salida'], dip_zona_horaria() );
	return array(
		'DatosEntrega'  => array(
			'Direccion'     => array(
				'Via'          => mb_substr( $d['via'], 0, 80 ),
				'Numero'       => $d['numero'],
				'Resto'        => mb_substr( $d['resto'], 0, 80 ),
				'CodigoPostal' => $d['cp'],
				'Poblacion'    => mb_substr( $d['ciudad'], 0, 60 ),
				'Provincia'    => $d['provincia'],
			),
			'Nombre'        => mb_substr( $d['nombre'], 0, 80 ),
			'Telefono'      => $d['telefono'],
			'Contacto'      => mb_substr( $d['contacto'], 0, 80 ),
			'ALaAtencionDe' => mb_substr( $d['atencion'], 0, 80 ),
			'Observaciones' => mb_substr( (string) $c['observaciones'], 0, 120 ),
		),
		'DatosServicio' => array(
			'Fecha'            => $salida ? $salida->format( 'd/m/Y' ) : '',
			'Referencia'       => $d['referencia'],
			'EnFranquicia'     => 'N',
			'CodigoServicio'   => $d['sabado'] && '' !== trim( (string) $c['servicio_sabado'] ) ? trim( (string) $c['servicio_sabado'] ) : trim( (string) $c['servicio'] ),
			'NumeroBultos'     => (string) max( 1, (int) $d['cajas'] ),
			'Peso'             => (string) (int) $d['peso'],
			'EntregaSabado'    => $d['sabado'] ? 'S' : 'N',
			'Reembolso'        => 'N',
			'TipoMercancia'    => trim( (string) $c['tipo_mercancia'] ),
			'ServicioEspecial' => trim( (string) $c['servicio_especial'] ),
		),
	);
}

/* =========================================================
 * Crear un envío
 * ========================================================= */

/** Bloqueo atómico (add_option no pisa una opción que ya existe): que la pasada y el aviso al momento no creen dos envíos. */
function dip_sagec_bloquear( $id ) {
	$clave = 'dip_sagec_bloqueo_' . (int) $id;
	if ( add_option( $clave, time(), '', false ) ) return true;
	if ( time() - (int) get_option( $clave ) > 5 * MINUTE_IN_SECONDS ) { // bloqueo viejo (un corte a mitad)
		update_option( $clave, time(), false );
		return true;
	}
	return false;
}

function dip_sagec_desbloquear( $id ) {
	delete_option( 'dip_sagec_bloqueo_' . (int) $id );
}

/**
 * Crea el envío de un pedido en MRW, si le toca hoy y está completo.
 * $origen: texto para la nota ('pasada de las 8:00', 'al momento', 'botón'…). $forzar: botón «Crear en MRW».
 * @return array{estado: string, motivos: string[], numero: string, error: string}
 *   estado: creado · simulado · probado · mano · error (MRW no lo acepta: queda en «Hacer a mano») · esperar · hecho ·
 *   marcado · fuera · apagado · ocupado.
 */
function dip_sagec_crear( WC_Order $p, $origen = 'al momento', $forzar = false, ?DateTimeImmutable $ahora = null ) {
	$c   = dip_sagec_config();
	$res = array( 'estado' => 'apagado', 'motivos' => array(), 'numero' => '', 'error' => '' );
	if ( 'apagado' === $c['modo'] ) return $res;
	$id = (int) $p->get_id();
	if ( ! dip_sagec_bloquear( $id ) ) return array_merge( $res, array( 'estado' => 'ocupado' ) );
	try {
		$p   = wc_get_order( $id ) ?: $p; // tal como está ahora, por si otra petición lo acaba de cambiar
		$rev = dip_sagec_revisar( $p, $ahora, $forzar );
		$res = array_merge( $res, array( 'estado' => $rev['estado'], 'motivos' => $rev['motivos'] ) );
		if ( 'mano' === $rev['estado'] ) {
			dip_sagec_marcar_mano( $p, $rev['motivos'], $origen, $c['modo'] );
			return $res;
		}
		if ( 'listo' !== $rev['estado'] ) return $res;
		$falta = dip_sagec_falta( $c );
		if ( 'simulacion' === $c['modo'] || $falta ) {
			dip_sagec_guardar_estado( $p, array( 'estado' => 'simulado', 'modo' => $c['modo'], 'falta' => $falta, 'peticion' => dip_sagec_resumen( $rev['datos'], $c ) ) );
			return array_merge( $res, array( 'estado' => 'simulado', 'motivos' => $falta ? array( 'faltan datos de MRW en Ajustes: ' . implode( ', ', $falta ) ) : array() ) );
		}
		$r = dip_sagec_llamar( 'TransmEnvio', 'TransmEnvioResult', dip_sagec_peticion( $rev['datos'], $c ), $c );
		$numero = dip_mrw_numero_limpio( (string) ( $r['datos']['NumeroEnvio'] ?? '' ) );
		if ( 'pruebas' === $c['modo'] ) {
			dip_sagec_guardar_estado( $p, array( 'estado' => $r['ok'] && $numero ? 'probado' : 'error', 'modo' => 'pruebas', 'numero' => $numero, 'error' => $r['error'], 'respuesta' => $r['datos'] ) );
			return array_merge( $res, array( 'estado' => $r['ok'] && $numero ? 'probado' : 'error', 'numero' => $numero, 'error' => $r['error'] ) );
		}
		if ( ! $r['ok'] || '' === $numero ) {
			$error = $r['error'] ?: 'MRW no ha devuelto número de envío';
			dip_sagec_marcar_mano( $p, array( $error ), $origen, 'real' );
			return array_merge( $res, array( 'estado' => 'error', 'error' => $error, 'motivos' => array( $error ) ) );
		}
		dip_sagec_guardar_creado( $p, $numero, (string) ( $r['datos']['NumeroSolicitud'] ?? '' ), $rev['datos'], $origen, $ahora );
		return array_merge( $res, array( 'estado' => 'creado', 'numero' => $numero ) );
	} finally {
		dip_sagec_desbloquear( $id );
	}
}

/** Resumen legible de lo que se manda (para la simulación y las notas). */
function dip_sagec_resumen( array $d, array $c ) {
	$pet = dip_sagec_peticion( $d, $c );
	$s   = $pet['DatosServicio'];
	$e   = $pet['DatosEntrega'];
	return sprintf(
		'%s · %s %s%s, %s %s (%s) · tel. %s · %s %s, %s kg facturables · salida %s%s · servicio %s',
		$e['Nombre'],
		$e['Direccion']['Via'],
		$e['Direccion']['Numero'],
		$e['Direccion']['Resto'] ? ', ' . $e['Direccion']['Resto'] : '',
		$e['Direccion']['CodigoPostal'],
		$e['Direccion']['Poblacion'],
		$e['Direccion']['Provincia'],
		$e['Telefono'],
		$s['NumeroBultos'],
		'1' === $s['NumeroBultos'] ? 'caja' : 'cajas',
		$s['Peso'],
		$s['Fecha'],
		'S' === $s['EntregaSabado'] ? ' · entrega en sábado' : '',
		$s['CodigoServicio'] ?: '[falta]'
	);
}

function dip_sagec_guardar_estado( WC_Order $p, array $estado ) {
	$p->update_meta_data( '_dip_sagec_estado', array_merge( array( 'cuando' => time() ), $estado ) );
	$p->save_meta_data();
}

/**
 * «Hacer a mano»: no se crea nada, queda el motivo en el pedido y una nota. En modo real, además, un email al correo de
 * avisos (lo manda quien llama: la pasada junta todos en uno; al momento, uno por pedido).
 */
function dip_sagec_marcar_mano( WC_Order $p, array $motivos, $origen, $modo ) {
	$texto = implode( '; ', array_map( 'strval', $motivos ) );
	dip_sagec_guardar_estado( $p, array( 'estado' => 'mano', 'modo' => $modo, 'motivos' => $motivos ) );
	if ( 'real' !== $modo ) return;
	$antes = (string) $p->get_meta( '_dip_sagec_mano' );
	$p->update_meta_data( '_dip_sagec_mano', $texto );
	$p->save_meta_data();
	if ( $antes === $texto ) return;
	$p->add_order_note( sprintf( 'Envío en MRW sin crear (%s): %s. Hazlo a mano en la web de clientes de MRW y apunta el número en «Envíos de hoy», o corrige el pedido y pulsa «Crear en MRW».', $origen, $texto ) );
	$GLOBALS['dip_sagec_nuevos_mano'][ $p->get_id() ] = $texto;
}

/** Guarda el envío creado: número, Advanced Shipment Tracking (para la app), huella y nota. Sin avisar al cliente. */
function dip_sagec_guardar_creado( WC_Order $p, $numero, $solicitud, array $datos, $origen, ?DateTimeImmutable $ahora = null ) {
	$cuando = dip_sagec_ahora( $ahora );
	$p->update_meta_data( '_dip_mrw_envio', $numero );
	$p->update_meta_data( '_dip_sagec_creado', $cuando->format( 'Y-m-d H:i:s' ) );
	$p->update_meta_data( '_dip_sagec_huella', dip_sagec_huella( $datos ) );
	if ( '' !== $solicitud ) $p->update_meta_data( '_dip_sagec_solicitud', $solicitud );
	$p->delete_meta_data( '_dip_sagec_mano' );
	$p->delete_meta_data( '_dip_sagec_revisar' );
	$p->save_meta_data();
	dip_sagec_guardar_estado( $p, array( 'estado' => 'creado', 'modo' => 'real', 'numero' => $numero ) );
	dip_ast_poner( $p, $numero, $cuando->getTimestamp() );
	$despues = dip_sagec_minuto_del_dia( $cuando ) >= dip_sagec_minutos( dip_sagec_hora_camino() ) && $cuando->format( 'Y-m-d' ) === $datos['salida'];
	$p->add_order_note( sprintf(
		'Envío creado en MRW (%s): número %s, %s, salida el %s. Seguimiento: %s. %s',
		$origen,
		$numero,
		$datos['sabado'] ? 'entrega en sábado antes de las ' . DIP_HORA_ENTREGA_SABADO : 'entrega antes de las ' . DIP_HORA_ENTREGA,
		dip_fecha_larga( $datos['salida'] ),
		dip_url_seguimiento_mrw( $numero ),
		$despues ? 'Ya es más tarde de las ' . dip_sagec_hora_camino() . ': el cliente recibe ahora el aviso «va de camino».' : 'El cliente recibe el aviso «va de camino» a las ' . dip_sagec_hora_camino() . '.'
	) );
	if ( $despues ) dip_sagec_avisar_salida( $p, $cuando );
}

/** Envío creado por la web que todavía no ha salido (aún no se ha avisado al cliente). «Mi pedido» no lo da por enviado. */
function dip_sagec_pendiente_de_salir( WC_Order $p ) {
	return '' !== (string) $p->get_meta( '_dip_sagec_creado' ) && '' === (string) $p->get_meta( '_dip_enviado' );
}

/** Ha salido: _dip_enviado y aviso «va de camino». @return string 'enviado' | 'fallo' | 'ya' */
function dip_sagec_avisar_salida( WC_Order $p, ?DateTimeImmutable $ahora = null ) {
	if ( '' !== (string) $p->get_meta( '_dip_enviado' ) ) return 'ya';
	$p->update_meta_data( '_dip_enviado', dip_sagec_ahora( $ahora )->format( 'Y-m-d H:i:s' ) );
	$p->save_meta_data();
	if ( ! function_exists( 'dip_aviso_enviar' ) ) return 'fallo';
	try {
		$ok = dip_aviso_enviar( $p, 'camino' );
	} catch ( Throwable $e ) {
		$ok = false;
	}
	return $ok || ( function_exists( 'dip_avisos_activo' ) && ! dip_avisos_activo( 'camino' ) ) ? 'enviado' : 'fallo';
}

/* =========================================================
 * Advanced Shipment Tracking (de ahí lee el número la app INDUNOVA Control)
 * ========================================================= */

/**
 * Añade el número al pedido con el formato de Advanced Shipment Tracking / WooCommerce Shipment Tracking:
 * _wc_shipment_tracking_items = [ [tracking_provider, custom_tracking_provider, custom_tracking_link, tracking_number,
 * date_shipped, tracking_id], … ]. Se escribe la meta directamente: sin cambiar el estado del pedido ni enviar el email
 * de AST (el cliente ya recibe «va de camino»). MRW va como transportista propio, con el enlace de seguimiento de mrw.es.
 */
function dip_ast_poner( WC_Order $p, $numero, $cuando = null ) {
	$numero = dip_mrw_numero_limpio( $numero );
	if ( '' === $numero ) return;
	$items = array_values( array_filter( (array) $p->get_meta( '_wc_shipment_tracking_items' ), 'is_array' ) );
	foreach ( $items as $i ) {
		if ( (string) ( $i['tracking_number'] ?? '' ) === $numero ) return; // ya está
	}
	$cuando  = (int) ( $cuando ?: time() );
	$items[] = array(
		'tracking_provider'        => '',
		'custom_tracking_provider' => 'MRW',
		'custom_tracking_link'     => dip_url_seguimiento_mrw( $numero ),
		'tracking_number'          => $numero,
		'date_shipped'             => (string) $cuando,
		'tracking_id'              => md5( $numero . '-' . $cuando ),
		'source'                   => 'dryicepack-tienda',
	);
	$p->update_meta_data( '_wc_shipment_tracking_items', $items );
	$p->save_meta_data();
}

/** Quita un número de Advanced Shipment Tracking (anulado, corregido o borrado). */
function dip_ast_quitar( WC_Order $p, $numero ) {
	$numero = dip_mrw_numero_limpio( $numero );
	$antes  = array_values( array_filter( (array) $p->get_meta( '_wc_shipment_tracking_items' ), 'is_array' ) );
	$items  = array_values( array_filter( $antes, static fn( $i ) => (string) ( $i['tracking_number'] ?? '' ) !== $numero ) );
	if ( count( $items ) === count( $antes ) ) return;
	if ( $items ) $p->update_meta_data( '_wc_shipment_tracking_items', $items );
	else $p->delete_meta_data( '_wc_shipment_tracking_items' );
	$p->save_meta_data();
}

/* =========================================================
 * Pasada de las 8:00, pedidos al momento y aviso de las 18:30
 * ========================================================= */

/** Pedidos con envío que salen hoy (llegan mañana), Procesando o En espera. */
function dip_sagec_pedidos_de_hoy( ?DateTimeImmutable $ahora = null ) {
	$manana = dip_sagec_ahora( $ahora )->setTime( 0, 0 )->modify( '+1 day' )->format( 'Y-m-d' );
	return function_exists( 'dip_eh_pedidos_entrega' ) ? dip_eh_pedidos_entrega( array( $manana ) ) : array();
}

/**
 * Crea los envíos de todos los pedidos que salen hoy. Devuelve el recuento por estado y las líneas para el resultado.
 * Los nuevos «Hacer a mano» van juntos en un email al correo de avisos.
 */
function dip_sagec_pasada( $origen = 'pasada de las 8:00', $forzar = false, ?DateTimeImmutable $ahora = null ) {
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	$cuenta = array();
	$lineas = array();
	foreach ( dip_sagec_pedidos_de_hoy( $ahora ) as $p ) {
		$r = dip_sagec_crear( $p, $origen, $forzar, $ahora );
		$cuenta[ $r['estado'] ] = ( $cuenta[ $r['estado'] ] ?? 0 ) + 1;
		if ( in_array( $r['estado'], array( 'creado', 'probado' ), true ) ) $lineas[] = sprintf( 'Pedido %s: envío %s%s.', $p->get_order_number(), $r['numero'], 'probado' === $r['estado'] ? ' (entorno de pruebas de MRW)' : '' );
		if ( in_array( $r['estado'], array( 'mano', 'error' ), true ) ) $lineas[] = sprintf( 'Pedido %s: hacer a mano (%s).', $p->get_order_number(), implode( '; ', $r['motivos'] ?: array( $r['error'] ) ) );
		if ( 'simulado' === $r['estado'] ) $lineas[] = sprintf( 'Pedido %s: listo para MRW%s.', $p->get_order_number(), $r['motivos'] ? ' (' . implode( '; ', $r['motivos'] ) . ')' : '' );
	}
	dip_sagec_email_mano();
	return array( 'cuenta' => $cuenta, 'lineas' => $lineas );
}

/** Un email con los pedidos que han quedado «Hacer a mano» en esta petición (solo los nuevos y en modo real). */
function dip_sagec_email_mano() {
	$nuevos = (array) ( $GLOBALS['dip_sagec_nuevos_mano'] ?? array() );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	if ( ! $nuevos ) return true;
	$correo = function_exists( 'dip_ajuste' ) ? (string) dip_ajuste( 'email_avisos' ) : '';
	if ( '' === $correo ) return false;
	$n     = count( $nuevos );
	$texto = sprintf( "%s sin crear en MRW. Hay que hacer%s a mano antes de las %s.\n\n", 1 === $n ? 'Este envío ha quedado' : 'Estos envíos han quedado', 1 === $n ? 'lo' : 'los', dip_sagec_hora_limite() );
	foreach ( $nuevos as $id => $motivo ) {
		$p = wc_get_order( $id );
		if ( ! $p ) continue;
		$texto .= ( function_exists( 'dip_eh_linea_email' ) ? dip_eh_linea_email( $p ) : 'Pedido ' . $p->get_order_number() . "\n" ) . '  Motivo: ' . $motivo . "\n";
	}
	$texto .= "\nQué hacer:\n"
		. "1. Si se puede arreglar (dirección, teléfono, kilos), corrige el pedido y pulsa «Crear en MRW» en «Envíos de hoy».\n"
		. "2. Si no, crea el envío en la web de clientes de MRW y apunta el número en «Envíos de hoy»: el cliente recibe el aviso «va de camino».\n"
		. "3. Si no puede salir hoy, llama al cliente.\n\n"
		. 'Envíos de hoy: ' . ( function_exists( 'dip_eh_url' ) ? dip_eh_url() : admin_url( 'admin.php?page=dip-envios-hoy' ) ) . "\n";
	return wp_mail( $correo, sprintf( 'MRW: %d %s para hacer a mano', $n, 1 === $n ? 'envío' : 'envíos' ), $texto );
}

/**
 * A las 18:30 (o con el botón): avisa «va de camino» a los envíos creados por la web que salen hoy. También a los de ayer
 * que se quedaron sin aviso (WP-Cron parado a las 18:30): así sale antes del «hoy llega» de las 8:00.
 */
function dip_sagec_avisar_salidas( ?DateTimeImmutable $ahora = null ) {
	$cuenta = array( 'enviado' => 0, 'fallo' => 0 );
	$fallos = array();
	$hoy    = dip_sagec_ahora( $ahora )->setTime( 0, 0 );
	$lista  = function_exists( 'dip_eh_pedidos_entrega' ) ? dip_eh_pedidos_entrega( array( $hoy->format( 'Y-m-d' ), $hoy->modify( '+1 day' )->format( 'Y-m-d' ) ) ) : array();
	foreach ( $lista as $p ) {
		if ( ! dip_sagec_pendiente_de_salir( $p ) ) continue;
		$r = dip_sagec_avisar_salida( $p, $ahora );
		if ( 'ya' === $r ) continue;
		$cuenta[ $r ]++;
		if ( 'fallo' === $r ) $fallos[] = 'Pedido ' . $p->get_order_number() . ' (' . ( function_exists( 'dip_aviso_ultimo_motivo' ) ? (string) dip_aviso_ultimo_motivo() : '' ) . ')';
	}
	return array( 'cuenta' => $cuenta, 'fallos' => $fallos );
}

/** Tareas programadas: un evento suelto por día a la hora de Madrid (como inc/envios-hoy.php). */
function dip_sagec_tareas() {
	return array( 'dip_sagec_pasada' => DIP_SAGEC_HORA_PASADA, 'dip_sagec_salida' => dip_sagec_hora_camino() );
}

add_action( 'init', static function () {
	if ( ! function_exists( 'dip_eh_proxima' ) ) return;
	if ( ! get_option( 'dip_sagec_desde' ) ) add_option( 'dip_sagec_desde', time(), '', false ); // para no avisar de una pasada que aún no tocaba
	foreach ( dip_sagec_tareas() as $gancho => $hora ) {
		$siguiente = wp_next_scheduled( $gancho );
		$debe      = dip_eh_proxima( $hora );
		// Si se cambia la hora en Ajustes, el evento de hoy se mueve a la hora nueva
		if ( $siguiente && abs( (int) $siguiente - (int) $debe ) > 60 && (int) $siguiente > time() ) {
			wp_unschedule_event( $siguiente, $gancho );
			$siguiente = false;
		}
		if ( ! $siguiente ) wp_schedule_single_event( $debe, $gancho );
	}
} );

add_action( 'dip_sagec_pasada', static function () {
	if ( 'apagado' === dip_sagec_modo() ) return;
	$r = dip_sagec_pasada();
	update_option( 'dip_sagec_ultima', array( 'dia' => dip_sagec_ahora()->format( 'Y-m-d' ), 'cuando' => time(), 'cuenta' => $r['cuenta'] ), false );
} );

add_action( 'dip_sagec_salida', static function () {
	if ( 'real' !== dip_sagec_modo() ) return;
	$r = dip_sagec_avisar_salidas();
	if ( $r['fallos'] ) {
		$correo = (string) dip_ajuste( 'email_avisos' );
		if ( '' !== $correo ) wp_mail( $correo, 'MRW: avisos «va de camino» que no han salido', "No ha salido el aviso «va de camino» de:\n- " . implode( "\n- ", $r['fallos'] ) . "\n\nAvísales por WhatsApp o teléfono, o desde el pedido: Acciones del pedido → Enviar aviso: va de camino.\n" );
	}
} );

/** Un pedido queda listo para salir hoy: se crea al momento (WP-Cron en la siguiente visita; el bloqueo evita dobles). */
function dip_sagec_al_momento( $id ) {
	$id = (int) $id;
	if ( ! $id || 'apagado' === dip_sagec_modo() ) return;
	$p = wc_get_order( $id );
	if ( ! $p ) return;
	$rev = dip_sagec_revisar( $p );
	if ( ! in_array( $rev['estado'], array( 'listo', 'mano' ), true ) ) return;
	if ( ! wp_next_scheduled( 'dip_sagec_uno', array( $id ) ) ) wp_schedule_single_event( time(), 'dip_sagec_uno', array( $id ) );
}

add_action( 'dip_sagec_uno', static function ( $id ) {
	$p = wc_get_order( (int) $id );
	if ( ! $p ) return;
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	dip_sagec_crear( $p, 'al momento' );
	dip_sagec_email_mano();
} );

// Pago confirmado (En espera → Procesando): fuera la marca de «Hacer a mano» que solo era por eso, y al momento
add_action( 'woocommerce_order_status_processing', static function ( $id ) {
	$p = wc_get_order( $id );
	if ( $p && false !== strpos( (string) $p->get_meta( '_dip_sagec_mano' ), DIP_SAGEC_EN_ESPERA ) ) {
		$p->delete_meta_data( '_dip_sagec_mano' );
		$p->save_meta_data();
	}
	dip_sagec_al_momento( $id );
}, 30 );
add_action( 'woocommerce_order_status_on-hold', 'dip_sagec_al_momento', 30 );

/* Al guardar un pedido en el escritorio: si cambió algo de lo que se mandó a MRW, a revisar; si no tiene envío, al momento. */
add_action( 'woocommerce_process_shop_order_meta', static function ( $id ) {
	$p = wc_get_order( $id );
	if ( ! $p || '' === (string) $p->get_meta( '_dip_sagec_creado' ) ) {
		dip_sagec_al_momento( $id );
		return;
	}
	$huella = (string) $p->get_meta( '_dip_sagec_huella' );
	if ( '' === $huella || dip_sagec_huella( dip_sagec_datos( $p ) ) === $huella || '' !== (string) $p->get_meta( '_dip_sagec_revisar' ) ) return;
	$texto = 'el pedido ha cambiado (día, dirección, teléfono o kilos) después de crear el envío ' . $p->get_meta( '_dip_mrw_envio' ) . ' en MRW';
	$p->update_meta_data( '_dip_sagec_revisar', $texto );
	$p->save_meta_data();
	$p->add_order_note( 'Revisar el envío de MRW: ' . $texto . '. Anúlalo en MRW (Acciones del pedido → Anular el envío en MRW) y vuelve a crearlo, o corrígelo en la web de clientes de MRW.' );
	$correo = (string) dip_ajuste( 'email_avisos' );
	if ( '' !== $correo ) wp_mail( $correo, 'MRW: revisar el envío del pedido ' . $p->get_order_number(), 'Revisar: ' . $texto . ".\n\nAbrir el pedido: " . $p->get_edit_order_url() . "\n" );
}, 90 );

/* Pedido cancelado o reembolsado con envío creado y sin salir: aviso para anularlo (no se anula solo). */
add_action( 'woocommerce_order_status_changed', static function ( $id, $de, $a ) {
	if ( ! in_array( $a, array( 'cancelled', 'refunded' ), true ) ) return;
	$p = wc_get_order( $id );
	if ( ! $p || ! dip_sagec_pendiente_de_salir( $p ) ) return;
	$p->add_order_note( 'Este pedido tiene el envío ' . $p->get_meta( '_dip_mrw_envio' ) . ' creado en MRW. Si no va a salir, anúlalo: Acciones del pedido → Anular el envío en MRW.' );
	$correo = (string) dip_ajuste( 'email_avisos' );
	if ( '' !== $correo ) wp_mail( $correo, 'MRW: anula el envío del pedido ' . $p->get_order_number(), 'El pedido ' . $p->get_order_number() . ' está «' . wc_get_order_status_name( $a ) . '» y tiene el envío ' . $p->get_meta( '_dip_mrw_envio' ) . " creado en MRW.\nSi no va a salir, anúlalo desde el pedido: Acciones del pedido → Anular el envío en MRW.\n\n" . $p->get_edit_order_url() . "\n" );
}, 20, 3 );

/* =========================================================
 * Anular y etiquetas
 * ========================================================= */

/** Anula el envío en MRW y lo quita del pedido. @return true|string (motivo del fallo) */
function dip_sagec_anular( WC_Order $p ) {
	$numero = (string) $p->get_meta( '_dip_mrw_envio' );
	if ( '' === $numero || '' === (string) $p->get_meta( '_dip_sagec_creado' ) ) return 'este pedido no tiene un envío creado por la web';
	$c = dip_sagec_config();
	if ( 'real' !== $c['modo'] ) return 'MRW no está en modo real (Ajustes → MRW)';
	$r = dip_sagec_llamar( 'CancelarEnvio', 'CancelarEnvioResult', array( 'CancelaEnvio' => array( 'NumeroEnvioOriginal' => $numero ) ), $c );
	if ( ! $r['ok'] ) return $r['error'] ?: 'MRW no lo ha anulado';
	$avisado = '' !== (string) $p->get_meta( '_dip_aviso_camino' );
	dip_ast_quitar( $p, $numero );
	foreach ( array( '_dip_mrw_envio', '_dip_sagec_creado', '_dip_sagec_huella', '_dip_sagec_solicitud', '_dip_sagec_revisar', '_dip_enviado' ) as $k ) $p->delete_meta_data( $k );
	$p->update_meta_data( '_dip_sagec_anulado', $numero );
	$p->save_meta_data();
	dip_sagec_guardar_estado( $p, array( 'estado' => 'anulado', 'modo' => 'real', 'numero' => $numero ) );
	$p->add_order_note( 'Envío ' . $numero . ' anulado en MRW.' . ( $avisado ? ' El cliente ya había recibido el aviso «va de camino»: avísale.' : ' El cliente no había recibido ningún aviso.' ) . ' Para crear otro: «Crear en MRW» en «Envíos de hoy».' );
	return true;
}

/** PDF con las etiquetas de varios envíos. @return string|WP_Error */
function dip_sagec_etiquetas( array $numeros ) {
	$numeros = array_values( array_unique( array_filter( array_map( static fn( $n ) => str_replace( ' ', '', dip_mrw_numero_limpio( $n ) ), $numeros ) ) ) );
	if ( ! $numeros ) return new WP_Error( 'dip_sagec', 'no hay envíos con número' );
	$c = dip_sagec_config();
	if ( ! in_array( $c['modo'], array( 'real', 'pruebas' ), true ) || dip_sagec_falta( $c ) ) return new WP_Error( 'dip_sagec', 'MRW no está conectado (Ajustes → MRW)' );
	$r = dip_sagec_llamar( 'GetEtiquetaEnvio', 'GetEtiquetaEnvioResult', array(
		'NumeroEnvio'           => implode( ',', $numeros ),
		'SeparadorNumerosEnvio' => ',',
		'TipoEtiquetaEnvio'     => trim( (string) $c['tipo_etiqueta'] ),
		'ReportTopMargin'       => '0',
		'ReportLeftMargin'      => '0',
	), $c );
	$pdf = (string) base64_decode( (string) ( $r['datos']['EtiquetaFile'] ?? '' ), true );
	if ( ! $r['ok'] || 0 !== strpos( $pdf, '%PDF' ) ) return new WP_Error( 'dip_sagec', $r['error'] ?: 'MRW no ha devuelto la etiqueta en PDF' );
	return $pdf;
}

/* =========================================================
 * Escritorio: botones, acciones del pedido y estado
 * ========================================================= */

/** Línea de estado de MRW de un pedido para «Envíos de hoy» y la pantalla del pedido: [clase, texto]. */
function dip_sagec_texto_estado( WC_Order $p ) {
	if ( '' !== (string) $p->get_meta( '_dip_sagec_revisar' ) ) return array( 'mal', 'Revisar en MRW: ' . $p->get_meta( '_dip_sagec_revisar' ) . '.' );
	$creado = (string) $p->get_meta( '_dip_sagec_creado' );
	if ( '' !== $creado && '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) {
		$d = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $creado, dip_zona_horaria() );
		return array( 'bien', 'Creado en MRW' . ( $d ? ' el ' . $d->format( 'j/n \a \l\a\s H:i' ) : '' ) . '. ' . ( dip_sagec_pendiente_de_salir( $p ) ? 'Aviso «va de camino» a las ' . dip_sagec_hora_camino() . '.' : 'Cliente avisado.' ) );
	}
	if ( '' !== (string) $p->get_meta( '_dip_sagec_mano' ) ) return array( 'mal', 'Hacer a mano: ' . $p->get_meta( '_dip_sagec_mano' ) . '.' );
	$e = (array) $p->get_meta( '_dip_sagec_estado' );
	$cuando = ! empty( $e['cuando'] ) ? ' (' . wp_date( 'j/n H:i', (int) $e['cuando'], dip_zona_horaria() ) . ')' : '';
	switch ( (string) ( $e['estado'] ?? '' ) ) {
		case 'simulado':
			return array( 'info', 'Simulación' . $cuando . ': listo para MRW. Mandaría: ' . ( $e['peticion'] ?? '' ) . '.' );
		case 'probado':
			return array( 'info', 'Prueba en MRW' . $cuando . ': aceptado, número de pruebas ' . ( $e['numero'] ?? '' ) . ' (no se guarda en el pedido).' );
		case 'error':
			return array( 'mal', 'Prueba en MRW' . $cuando . ': ' . ( $e['error'] ?? 'no lo acepta' ) . '.' );
		case 'mano':
			return array( 'mal', ( 'real' === ( $e['modo'] ?? '' ) ? 'Hacer a mano' : 'Simulación: quedaría para hacer a mano' ) . $cuando . ': ' . implode( '; ', (array) ( $e['motivos'] ?? array() ) ) . '.' );
		case 'anulado':
			return array( 'info', 'Envío ' . ( $e['numero'] ?? '' ) . ' anulado en MRW' . $cuando . '.' );
	}
	return array( '', '' );
}

/** Problemas de la creación automática (para «Envíos de hoy» y el escritorio). */
function dip_sagec_problemas( ?DateTimeImmutable $ahora = null ) {
	$ahora = dip_sagec_ahora( $ahora );
	if ( 'apagado' === dip_sagec_modo() ) return array();
	$r      = array();
	$ultima = (array) get_option( 'dip_sagec_ultima', array() );
	$hoy    = $ahora->setTime( 0, 0 );
	$margen = dip_sagec_minutos( DIP_SAGEC_HORA_PASADA ) + 60;
	list( $hp, $mp ) = array_map( 'intval', explode( ':', DIP_SAGEC_HORA_PASADA ) );
	$tocaba = $hoy->setTime( $hp, $mp )->getTimestamp() >= (int) get_option( 'dip_sagec_desde', 0 ); // ya estaba instalado a las 8:00
	if ( $tocaba && function_exists( 'dip_es_dia_de_salida' ) && dip_es_dia_de_salida( $hoy ) && dip_sagec_minuto_del_dia( $ahora ) >= $margen && ( $ultima['dia'] ?? '' ) !== $hoy->format( 'Y-m-d' ) ) {
		$r[] = 'La creación de envíos en MRW de las ' . DIP_SAGEC_HORA_PASADA . ' no se ha hecho hoy (puede que WP-Cron no se esté ejecutando). Pulsa «Crear envíos ahora» en «Envíos de hoy».';
	}
	if ( 'real' === dip_sagec_modo() && dip_sagec_falta() ) $r[] = 'MRW está en modo real pero faltan datos en Ajustes → MRW: ' . implode( ', ', dip_sagec_falta() ) . '.';
	return $r;
}

add_action( 'admin_notices', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$pantalla = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $pantalla && ! empty( $GLOBALS['dip_eh_pagina'] ) && $pantalla->id === $GLOBALS['dip_eh_pagina'] ) return; // «Envíos de hoy» ya los enseña
	foreach ( dip_sagec_problemas() as $problema ) {
		printf( '<div class="notice notice-error"><p><strong>MRW.</strong> %s</p></div>', esc_html( $problema ) );
	}
} );

/* Botones de «Envíos de hoy» (admin-post) */
add_action( 'admin_post_dip_sagec_accion', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_sagec_accion' );
	$que    = isset( $_POST['que'] ) ? sanitize_key( wp_unslash( $_POST['que'] ) ) : '';
	$id     = isset( $_POST['pedido'] ) ? absint( $_POST['pedido'] ) : 0;
	$volver = isset( $_POST['volver'] ) ? esc_url_raw( wp_unslash( $_POST['volver'] ) ) : '';
	$hecho  = array( 'tipo' => 'notice-success', 'texto' => '', 'lineas' => array() );
	if ( 'crear_todos' === $que ) {
		$r = dip_sagec_pasada( 'botón «Crear envíos ahora»' );
		update_option( 'dip_sagec_ultima', array( 'dia' => dip_sagec_ahora()->format( 'Y-m-d' ), 'cuando' => time(), 'cuenta' => $r['cuenta'] ), false );
		$hecho['texto']  = $r['lineas'] ? 'MRW: hecho.' : 'MRW: no había ningún envío nuevo que crear hoy.';
		$hecho['lineas'] = $r['lineas'];
		if ( ! empty( $r['cuenta']['mano'] ) || ! empty( $r['cuenta']['error'] ) ) $hecho['tipo'] = 'notice-warning';
	} elseif ( 'crear' === $que && $id && ( $p = wc_get_order( $id ) ) ) {
		$p->delete_meta_data( '_dip_sagec_mano' );
		$p->save_meta_data();
		$GLOBALS['dip_sagec_nuevos_mano'] = array();
		$r = dip_sagec_crear( $p, 'botón «Crear en MRW»', true );
		$GLOBALS['dip_sagec_nuevos_mano'] = array(); // quien pulsa el botón ya ve el motivo: sin email
		$hecho['texto'] = array(
			'creado'   => 'Pedido ' . $p->get_order_number() . ': envío ' . $r['numero'] . ' creado en MRW.',
			'probado'  => 'Pedido ' . $p->get_order_number() . ': MRW (pruebas) lo acepta con el número ' . $r['numero'] . '.',
			'simulado' => 'Pedido ' . $p->get_order_number() . ': simulación, listo para MRW.',
			'hecho'    => 'Pedido ' . $p->get_order_number() . ': ya tenía número de MRW.',
			'apagado'  => 'MRW está apagado (Ajustes → MRW).',
			'ocupado'  => 'Pedido ' . $p->get_order_number() . ': se está creando ahora mismo. Vuelve a cargar en un momento.',
		)[ $r['estado'] ] ?? 'Pedido ' . $p->get_order_number() . ': no se ha creado. ' . implode( '; ', $r['motivos'] ?: array( $r['error'] ) ) . '.';
		if ( ! in_array( $r['estado'], array( 'creado', 'probado', 'simulado', 'hecho' ), true ) ) $hecho['tipo'] = 'notice-warning';
	} elseif ( 'avisar' === $que ) {
		$r = dip_sagec_avisar_salidas();
		$hecho['texto'] = sprintf( 'Avisos «va de camino» enviados: %d.', $r['cuenta']['enviado'] );
		if ( $r['fallos'] ) {
			$hecho['tipo']   = 'notice-warning';
			$hecho['lineas'] = array_map( static fn( $f ) => $f . ': no ha salido. Avísale por WhatsApp o teléfono.', $r['fallos'] );
		}
	}
	set_transient( 'dip_eh_resultado_' . get_current_user_id(), $hecho, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( $volver ?: ( function_exists( 'dip_eh_url' ) ? dip_eh_url() : admin_url() ) );
	exit;
} );

/* Etiquetas en PDF (GET con nonce): ?action=dip_sagec_etiquetas&pedidos=1,2,3 */
add_action( 'admin_post_dip_sagec_etiquetas', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_sagec_etiquetas' );
	$ids     = array_filter( array_map( 'absint', explode( ',', isset( $_GET['pedidos'] ) ? sanitize_text_field( wp_unslash( $_GET['pedidos'] ) ) : '' ) ) );
	$numeros = array();
	foreach ( $ids as $id ) {
		$p = wc_get_order( $id );
		if ( $p && '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) $numeros[] = (string) $p->get_meta( '_dip_mrw_envio' );
	}
	$pdf = dip_sagec_etiquetas( $numeros );
	if ( is_wp_error( $pdf ) ) wp_die( esc_html( 'No se han podido sacar las etiquetas: ' . $pdf->get_error_message() . '. Sácalas en la web de clientes de MRW.' ), 502 );
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: inline; filename="etiquetas-mrw-' . dip_sagec_ahora()->format( 'Y-m-d' ) . '.pdf"' );
	header( 'Content-Length: ' . strlen( $pdf ) );
	echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- PDF
	exit;
} );

function dip_sagec_url_etiquetas( array $ids ) {
	return wp_nonce_url( add_query_arg( array( 'action' => 'dip_sagec_etiquetas', 'pedidos' => implode( ',', array_map( 'intval', $ids ) ) ), admin_url( 'admin-post.php' ) ), 'dip_sagec_etiquetas' );
}

/** Botones de MRW para «Envíos de hoy»: crear todos, avisar ahora y etiquetas del día. */
function dip_sagec_html_botones( array $pedidos, $volver ) {
	$modo = dip_sagec_modo();
	if ( 'apagado' === $modo ) return;
	$con  = array_values( array_filter( $pedidos, static fn( $p ) => '' !== (string) $p->get_meta( '_dip_sagec_creado' ) && '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) );
	$sin  = array_values( array_filter( $pedidos, static fn( $p ) => dip_sagec_pendiente_de_salir( $p ) ) );
	$nombres = array( 'simulacion' => 'Simulación: comprueba y enseña lo que mandaría, sin llamar a MRW.', 'pruebas' => 'Pruebas: llama al entorno de pruebas de MRW, sin guardar números ni avisar.', 'real' => 'Conectado a MRW.' );
	$falta = dip_sagec_falta();
	echo '<div class="dip-eh__mrw"><p class="dip-eh__mrw-modo"><strong>MRW.</strong> ' . esc_html( $nombres[ $modo ] . ' Se crean solos a las ' . DIP_SAGEC_HORA_PASADA . ' y al momento hasta las ' . dip_sagec_hora_limite() . '; el aviso «va de camino» sale a las ' . dip_sagec_hora_camino() . '.' . ( $falta ? ' Para conectarlo faltan en Dryicepack → Ajustes → MRW: ' . implode( ', ', $falta ) . '.' : '' ) ) . '</p>';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="dip-eh__mrw-form">';
	echo '<input type="hidden" name="action" value="dip_sagec_accion"><input type="hidden" name="volver" value="' . esc_attr( $volver ) . '">';
	wp_nonce_field( 'dip_sagec_accion' );
	echo '<button type="submit" class="button" name="que" value="crear_todos">' . esc_html( 'Crear envíos ahora' ) . '</button> ';
	if ( $sin && 'real' === $modo ) echo '<button type="submit" class="button" name="que" value="avisar" onclick="return confirm(\'' . esc_js( '¿Ya están las cajas en MRW? Los clientes reciben ahora el aviso «va de camino».' ) . '\')">' . esc_html( 'Ya están en MRW: avisar' ) . '</button> ';
	if ( $con ) echo '<a class="button button-primary" href="' . esc_url( dip_sagec_url_etiquetas( array_map( static fn( $p ) => $p->get_id(), $con ) ) ) . '" target="_blank" rel="noopener">' . esc_html( sprintf( 'Imprimir etiquetas (%d)', count( $con ) ) ) . '</a>';
	echo '</form></div>';
}

/** Estado y botón de un pedido en «Envíos de hoy». */
function dip_sagec_html_pedido( WC_Order $p, $volver ) {
	if ( 'apagado' === dip_sagec_modo() ) return;
	list( $clase, $texto ) = dip_sagec_texto_estado( $p );
	if ( '' === $texto ) $texto = dip_sagec_minuto_del_dia( dip_sagec_ahora() ) < dip_sagec_minutos( DIP_SAGEC_HORA_PASADA ) ? 'Se crea en MRW a las ' . DIP_SAGEC_HORA_PASADA . '.' : 'Todavía sin crear en MRW.';
	echo '<p class="dip-eh-p__mrw' . ( $clase ? ' dip-eh-p__mrw--' . esc_attr( $clase ) : '' ) . '">' . esc_html( $texto );
	$creado = '' !== (string) $p->get_meta( '_dip_sagec_creado' ) && '' !== (string) $p->get_meta( '_dip_mrw_envio' );
	if ( $creado ) {
		echo ' <a href="' . esc_url( dip_sagec_url_etiquetas( array( $p->get_id() ) ) ) . '" target="_blank" rel="noopener">' . esc_html( 'Etiqueta' ) . '</a>';
	} elseif ( '' === (string) $p->get_meta( '_dip_mrw_envio' ) ) {
		// Botón suelto: el formulario de los números ya envuelve la lista, así que va con form="" a uno propio
		$form = 'dip-sagec-' . (int) $p->get_id();
		echo ' <button type="submit" class="button button-small" form="' . esc_attr( $form ) . '">' . esc_html( 'Crear en MRW' ) . '</button>';
		$GLOBALS['dip_sagec_forms'][ $form ] = (int) $p->get_id();
	}
	echo '</p>';
}

/** Formularios de los botones «Crear en MRW» (fuera del formulario de los números). */
function dip_sagec_html_forms( $volver ) {
	foreach ( (array) ( $GLOBALS['dip_sagec_forms'] ?? array() ) as $form => $id ) {
		echo '<form id="' . esc_attr( $form ) . '" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" hidden>';
		echo '<input type="hidden" name="action" value="dip_sagec_accion"><input type="hidden" name="que" value="crear"><input type="hidden" name="pedido" value="' . (int) $id . '"><input type="hidden" name="volver" value="' . esc_attr( $volver ) . '">';
		wp_nonce_field( 'dip_sagec_accion', '_wpnonce', false );
		echo '</form>';
	}
	$GLOBALS['dip_sagec_forms'] = array();
}

/* Acciones del pedido: crear en MRW y anular el envío */
add_filter( 'woocommerce_order_actions', static function ( $acciones, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : ( $GLOBALS['theorder'] ?? null );
	if ( ! $pedido instanceof WC_Order || 'apagado' === dip_sagec_modo() || ( function_exists( 'dip_eh_es_recogida' ) && dip_eh_es_recogida( $pedido ) ) ) return $acciones;
	if ( '' === (string) $pedido->get_meta( '_dip_mrw_envio' ) ) $acciones['dip_sagec_crear'] = 'Crear el envío en MRW';
	if ( '' !== (string) $pedido->get_meta( '_dip_sagec_creado' ) ) $acciones['dip_sagec_anular'] = 'Anular el envío en MRW';
	return $acciones;
}, 20, 2 );

add_action( 'woocommerce_order_action_dip_sagec_crear', static function ( $pedido ) {
	$pedido->delete_meta_data( '_dip_sagec_mano' );
	$pedido->save_meta_data();
	$r = dip_sagec_crear( $pedido, 'Acciones del pedido', true );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	if ( ! in_array( $r['estado'], array( 'creado', 'probado', 'simulado', 'hecho' ), true ) && class_exists( 'WC_Admin_Meta_Boxes' ) ) {
		WC_Admin_Meta_Boxes::add_error( 'No se ha creado el envío en MRW: ' . implode( '; ', $r['motivos'] ?: array( $r['error'] ?: $r['estado'] ) ) . '.' );
	}
} );

add_action( 'woocommerce_order_action_dip_sagec_anular', static function ( $pedido ) {
	$r = dip_sagec_anular( $pedido );
	if ( true !== $r && class_exists( 'WC_Admin_Meta_Boxes' ) ) WC_Admin_Meta_Boxes::add_error( 'No se ha anulado el envío en MRW: ' . $r . '. Anúlalo en la web de clientes de MRW.' );
} );

/* En la pantalla del pedido, debajo del número de MRW */
add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order || 'apagado' === dip_sagec_modo() || ( function_exists( 'dip_eh_es_recogida' ) && dip_eh_es_recogida( $pedido ) ) ) return;
	list( $clase, $texto ) = dip_sagec_texto_estado( $pedido );
	if ( '' === $texto ) return;
	echo '<p class="dip-sagec-pedido" style="' . esc_attr( 'mal' === $clase ? 'color:#b32d2e' : '' ) . '">' . esc_html( $texto );
	if ( '' !== (string) $pedido->get_meta( '_dip_sagec_creado' ) ) echo ' <a href="' . esc_url( dip_sagec_url_etiquetas( array( $pedido->get_id() ) ) ) . '" target="_blank" rel="noopener">' . esc_html( 'Etiqueta' ) . '</a>';
	echo '</p>';
}, 31 );

/* =========================================================
 * Ajustes → MRW (formulario propio: la contraseña no pasa por options.php ni se vuelve a enseñar)
 * ========================================================= */

function dip_sagec_html_ajustes() {
	$c      = wp_parse_args( (array) get_option( 'dip_sagec', array() ), dip_sagec_por_defecto() );
	$conf   = dip_sagec_config();
	$const  = dip_sagec_constantes();
	$hecho  = isset( $_GET['dip_sagec'] ) ? sanitize_key( wp_unslash( $_GET['dip_sagec'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo muestra un aviso
	$prueba = get_transient( 'dip_sagec_prueba_' . get_current_user_id() );
	$campo  = static function ( $clave, $etiqueta, $ayuda = '', $tipo = 'text' ) use ( $c, $const ) {
		$en_config = isset( $const[ $clave ] ) && defined( $const[ $clave ] ) && '' !== (string) constant( $const[ $clave ] );
		echo '<tr><th scope="row"><label for="dip-sagec-' . esc_attr( $clave ) . '">' . esc_html( $etiqueta ) . '</label></th><td>';
		if ( $en_config ) {
			echo '<input id="dip-sagec-' . esc_attr( $clave ) . '" type="text" class="regular-text" value="' . esc_attr( 'En wp-config.php (' . $const[ $clave ] . ')' ) . '" readonly>';
		} elseif ( 'password' === $tipo ) {
			echo '<input id="dip-sagec-' . esc_attr( $clave ) . '" name="dip_sagec[' . esc_attr( $clave ) . ']" type="password" class="regular-text" autocomplete="new-password" value="" placeholder="' . esc_attr( '' !== (string) $c['clave'] ? 'Guardada (escribe otra para cambiarla)' : '' ) . '">';
			if ( '' !== (string) $c['clave'] ) echo ' <label><input type="checkbox" name="dip_sagec[borrar_clave]" value="1"> ' . esc_html( 'Borrarla' ) . '</label>';
		} else {
			echo '<input id="dip-sagec-' . esc_attr( $clave ) . '" name="dip_sagec[' . esc_attr( $clave ) . ']" type="text" class="regular-text" autocomplete="off" value="' . esc_attr( (string) $c[ $clave ] ) . '">';
		}
		if ( $ayuda ) echo '<p class="description">' . esc_html( $ayuda ) . '</p>';
		echo '</td></tr>';
	};
	?>
	<h2 id="dip-mrw-sagec">MRW: crear los envíos desde la web</h2>
	<?php
	if ( 'ok' === $hecho ) echo '<div class="notice notice-success inline"><p>' . esc_html( 'Ajustes de MRW guardados.' ) . '</p></div>';
	if ( is_array( $prueba ) ) {
		delete_transient( 'dip_sagec_prueba_' . get_current_user_id() );
		echo '<div class="notice ' . esc_attr( $prueba['ok'] ? 'notice-success' : 'notice-error' ) . ' inline"><p>' . esc_html( $prueba['texto'] ) . '</p></div>';
	}
	foreach ( dip_sagec_problemas() as $problema ) echo '<div class="notice notice-error inline"><p>' . esc_html( $problema ) . '</p></div>';
	?>
	<p>El día de salida, a las <?php echo esc_html( DIP_SAGEC_HORA_PASADA ); ?>, la web crea en MRW los envíos completos de ese día; después, cada pedido nuevo para hoy al momento, hasta la hora límite. Guarda el número en el pedido y en Advanced Shipment Tracking (de ahí lo lee la app INDUNOVA Control). Si falta algo o hay dudas, no crea nada: el pedido queda «Hacer a mano» y llega un email al correo de avisos. Los códigos de servicio, el tipo de etiqueta y cómo se declara el hielo seco (UN 1845) te los tiene que dar MRW: mientras falte algo, no se manda nada.</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="dip_sagec_ajustes">
		<?php wp_nonce_field( 'dip_sagec_ajustes' ); ?>
		<table class="form-table" role="presentation">
			<tr><th scope="row"><label for="dip-sagec-modo">Modo</label></th><td>
				<select id="dip-sagec-modo" name="dip_sagec[modo]">
					<option value="simulacion" <?php selected( $c['modo'], 'simulacion' ); ?>>Simulación: comprueba los pedidos y enseña lo que mandaría, sin llamar a MRW</option>
					<option value="pruebas" <?php selected( $c['modo'], 'pruebas' ); ?>>Pruebas: entorno de pruebas de MRW, sin guardar números ni avisar a nadie</option>
					<option value="real" <?php selected( $c['modo'], 'real' ); ?>>Real: crea los envíos en MRW</option>
					<option value="apagado" <?php selected( $c['modo'], 'apagado' ); ?>>Apagado: los números se apuntan a mano en «Envíos de hoy»</option>
				</select>
				<p class="description">Empieza en simulación unos días y mira «Envíos de hoy»: cada pedido dice qué mandaría o por qué quedaría para hacer a mano. Con las claves de pruebas de MRW, pasa a «Pruebas». Cuando todo cuadre, a «Real».</p></td></tr>
			<?php
			$campo( 'franquicia', 'Código de franquicia', 'Lo da MRW (tu franquicia, Mataró).' );
			$campo( 'abonado', 'Código de abonado', 'Tu número de cliente de MRW.' );
			$campo( 'departamento', 'Departamento', 'Solo si MRW te da uno. Si no, vacío.' );
			$campo( 'usuario', 'Usuario del servicio web', 'El de SAGEC (no el de la web de clientes, si MRW te da otro).' );
			$campo( 'clave', 'Contraseña', 'Se guarda cifrada y no se vuelve a enseñar. También se puede poner en wp-config.php: define( \'DIP_MRW_CLAVE\', \'…\' ); y lo mismo con DIP_MRW_FRANQUICIA, DIP_MRW_ABONADO, DIP_MRW_DEPARTAMENTO y DIP_MRW_USUARIO.', 'password' );
			$campo( 'servicio', 'Código de servicio (entre semana)', 'El de tu contrato para el urgente antes de las 14:00. Pídeselo a MRW: sin él no se crea nada.' );
			$campo( 'servicio_sabado', 'Código de servicio del sábado', 'Solo si el sábado antes de las 12:00 va con otro código. Si es el mismo con «entrega en sábado», vacío.' );
			$campo( 'tipo_mercancia', 'Tipo de mercancía', 'Lo que MRW te diga para el hielo seco (UN 1845). Vacío si no hace falta.' );
			$campo( 'servicio_especial', 'Servicio especial', 'Solo si MRW pide marcar el hielo seco así. Vacío si no.' );
			$campo( 'tipo_etiqueta', 'Tipo de etiqueta', 'El que te diga MRW para el PDF de las etiquetas. Vacío: el de serie.' );
			$campo( 'observaciones', 'Observaciones para el repartidor', 'Van en todos los envíos (120 caracteres como mucho).' );
			?>
		</table>
		<?php submit_button( 'Guardar los ajustes de MRW', 'primary', 'guardar', false ); ?>
		<?php if ( ! dip_sagec_falta( $conf ) ) : ?> <button type="submit" class="button" name="probar" value="1"><?php echo esc_html( 'Guardar y probar la conexión' ); ?></button><?php endif; ?>
	</form>
	<?php
}

add_action( 'admin_post_dip_sagec_ajustes', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_sagec_ajustes' );
	$e     = isset( $_POST['dip_sagec'] ) && is_array( $_POST['dip_sagec'] ) ? wp_unslash( $_POST['dip_sagec'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea abajo, campo a campo
	$antes = wp_parse_args( (array) get_option( 'dip_sagec', array() ), dip_sagec_por_defecto() );
	$nuevo = $antes;
	$nuevo['modo'] = in_array( (string) ( $e['modo'] ?? '' ), array( 'apagado', 'simulacion', 'pruebas', 'real' ), true ) ? (string) $e['modo'] : 'simulacion';
	foreach ( array( 'franquicia', 'abonado', 'departamento', 'usuario', 'servicio', 'servicio_sabado', 'tipo_mercancia', 'servicio_especial', 'tipo_etiqueta' ) as $k ) {
		if ( array_key_exists( $k, $e ) ) $nuevo[ $k ] = substr( trim( sanitize_text_field( (string) $e[ $k ] ) ), 0, 40 );
	}
	if ( array_key_exists( 'observaciones', $e ) ) $nuevo['observaciones'] = mb_substr( trim( sanitize_text_field( (string) $e['observaciones'] ) ), 0, 120 );
	if ( ! empty( $e['borrar_clave'] ) ) $nuevo['clave'] = '';
	elseif ( '' !== (string) ( $e['clave'] ?? '' ) ) $nuevo['clave'] = dip_sagec_cifrar( (string) $e['clave'] ); // tal cual: una contraseña puede llevar cualquier carácter
	update_option( 'dip_sagec', $nuevo, false );
	if ( ! empty( $_POST['probar'] ) ) {
		// Consulta de solo lectura (puntos MRW del código postal de la nave): comprueba las claves sin crear nada
		$c = dip_sagec_config();
		if ( 'real' !== $c['modo'] ) $c['modo'] = 'pruebas';
		$r = dip_sagec_llamar( 'GetPointsByCP', 'GetPointsByCPResult', array( 'Point' => array( 'CodigoPostal' => dip_contacto()['cp'] ) ), $c );
		set_transient( 'dip_sagec_prueba_' . get_current_user_id(), array(
			'ok'    => $r['ok'],
			'texto' => ( 'real' === $c['modo'] ? 'MRW (real): ' : 'MRW (entorno de pruebas): ' ) . ( $r['ok'] ? 'responde y acepta las claves' . ( '' !== (string) ( $r['datos']['Mensaje'] ?? '' ) ? ' (' . $r['datos']['Mensaje'] . ')' : '' ) . '.' : 'no ha funcionado: ' . $r['error'] . '.' ),
		), 5 * MINUTE_IN_SECONDS );
	}
	wp_safe_redirect( add_query_arg( 'dip_sagec', 'ok', admin_url( 'admin.php?page=dryicepack' ) ) . '#dip-mrw-sagec' );
	exit;
} );
