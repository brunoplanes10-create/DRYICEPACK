// Pruebas de la factura simplificada automática (plugin dryicepack-tienda, inc/factura-simplificada.php)
// con PHP 8.3 en WebAssembly, sin WordPress: funciones mínimas de WordPress y WooCommerce simuladas y un $wpdb
// falso sobre SQLite de verdad (las consultas del contador y de la reserva se ejecutan tal cual).
// Cada escenario es una ejecución de PHP nueva (base de datos en memoria vacía). No hace ninguna petición a internet.
// Deja en .tmp/correcciones/factura/ un PDF de ejemplo (ES, CA y EN) y la vista del email, y hace capturas del
// email a 390 y 1440 px en .tmp/correcciones/factura-simplificada/.
// Uso: node herramientas/probar-factura.mjs
import { loadNodeRuntime } from '@php-wasm/node';
import { PHP } from '@php-wasm/universal';
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';

const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/p/inc');
php.mkdir('/b');
php.mkdir('/salida');
php.mkdir('/tmp');
for (const f of ['ajustes.php', 'negocio.php', 'textos.php', 'envio.php', 'entrega.php', 'cuentas.php', 'factura.php', 'factura-simplificada.php']) {
  php.writeFile('/p/inc/' + f, readFileSync('plugin/dryicepack-tienda/inc/' + f));
}

php.writeFile('/b/arranque.php', String.raw`<?php
error_reporting( E_ALL );
set_error_handler( function ( $n, $s, $f, $l ) { echo 'FALLO aviso de PHP: ' . $s . ' en ' . basename( $f ) . ':' . $l . "\n"; return true; } );
define( 'ABSPATH', '/' );
define( 'DIP_TIENDA_DIR', '/p/' );
define( 'DIP_TIENDA_URL', 'http://prueba.local/wp-content/plugins/dryicepack-tienda/' );
define( 'DIP_TIENDA_VERSION', '1.0.0' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
$GLOBALS['opciones'] = array();
$GLOBALS['transitorios'] = array();
$GLOBALS['correos'] = array();
$GLOBALS['wc_correos'] = array();
$GLOBALS['ganchos'] = array();
$GLOBALS['pedidos'] = array();
$GLOBALS['bd_meta'] = array();
$GLOBALS['usuarios'] = array();

// Ganchos con prioridad, como WordPress
function add_action( $h, $f, $p = 10, $n = 1 ) { $GLOBALS['ganchos'][ $h ][ $p ][] = $f; return true; }
function add_filter( $h, $f, $p = 10, $n = 1 ) { return add_action( $h, $f, $p, $n ); }
function remove_action( $h, $f, $p = 10 ) {
	foreach ( $GLOBALS['ganchos'][ $h ] ?? array() as $pr => $lista ) foreach ( $lista as $i => $g ) if ( $g === $f ) unset( $GLOBALS['ganchos'][ $h ][ $pr ][ $i ] );
	return true;
}
function do_action( $h, ...$a ) { $g = $GLOBALS['ganchos'][ $h ] ?? array(); ksort( $g ); foreach ( $g as $lista ) foreach ( $lista as $f ) $f( ...$a ); }
function apply_filters( $h, $v, ...$a ) {
	if ( 'dip_fs_ahora' === $h && isset( $GLOBALS['ahora'] ) ) return $GLOBALS['ahora'];
	$g = $GLOBALS['ganchos'][ $h ] ?? array(); ksort( $g );
	foreach ( $g as $lista ) foreach ( $lista as $f ) $v = $f( $v, ...$a );
	return $v;
}
function add_shortcode( ...$a ) {}
function register_setting( ...$a ) {}
function add_menu_page( ...$a ) {}
function add_submenu_page( ...$a ) {}
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['opciones'] ) ? $GLOBALS['opciones'][ $k ] : $d; }
function update_option( $k, $v, $a = null ) { $GLOBALS['opciones'][ $k ] = $v; return true; }
function get_transient( $k ) { return $GLOBALS['transitorios'][ $k ] ?? false; }
function set_transient( $k, $v, $t = 0 ) { $GLOBALS['transitorios'][ $k ] = $v; return true; }
function delete_transient( $k ) { unset( $GLOBALS['transitorios'][ $k ] ); return true; }
function wp_timezone() { return new DateTimeZone( 'Europe/Madrid' ); }
function wp_parse_args( $a, $d = array() ) { return array_merge( (array) $d, (array) $a ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function home_url( $p = '' ) { return 'http://prueba.local' . $p; }
function admin_url( $p = '' ) { return 'http://prueba.local/wp-admin/' . $p; }
function add_query_arg( $args, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args ); }
function wp_nonce_url( $u, $a ) { return $u . '&_wpnonce=prueba'; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return esc_html( $s ); }
function esc_textarea( $s ) { return esc_html( $s ); }
function esc_url_raw( $s ) { return (string) $s; }
function sanitize_email( $s ) { return trim( (string) $s ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
function wp_unslash( $s ) { return $s; }
function is_email( $s ) { return (bool) filter_var( (string) $s, FILTER_VALIDATE_EMAIL ); }
function wp_strip_all_tags( $s ) { return trim( strip_tags( (string) $s ) ); }
function absint( $n ) { return abs( (int) $n ); }
function wp_date( $f, $t, $z = null ) { return ( new DateTimeImmutable( '@' . $t ) )->setTimezone( $z ?: wp_timezone() )->format( $f ); }
function current_time( $t ) { return gmdate( 'Y-m-d H:i:s' ); }
function current_user_can( $c ) { return true; }
function wp_doing_ajax() { return false; }
function is_admin() { return false; }
function get_current_user_id() { return 0; }
function get_user_meta( $id, $k, $single = false ) { return $GLOBALS['usuarios'][ $id ][ $k ] ?? ''; }
function get_page_by_path( $p ) { return null; }
function get_permalink( $p ) { return ''; }
function wp_hash( $s ) { return md5( $s ); }
function checked( $a, $b = true, $echo = true ) { $r = (string) $a === (string) $b ? ' checked="checked"' : ''; if ( $echo ) echo $r; return $r; }
function settings_fields( $g ) {}
function wp_nonce_field( ...$a ) { echo '<input type="hidden" name="_wpnonce" value="prueba">'; }
function submit_button( $t = '' ) { echo '<button>' . $t . '</button>'; }
function get_current_screen() { return $GLOBALS['pantalla'] ?? null; }
function remove_accents( $s ) { return strtr( (string) $s, array( 'ł' => 'l', 'Ł' => 'L', 'ő' => 'o', 'ű' => 'u', 'č' => 'c', 'ř' => 'r', 'ș' => 's', 'ț' => 't' ) ); }
$GLOBALS['clave_n'] = 0;
function wp_generate_password( $n = 12, $s = true ) { return substr( str_repeat( 'abcdefghijkl', 3 ) . ( ++$GLOBALS['clave_n'] ), -$n ); }
function get_temp_dir() { return '/tmp/'; }
function wp_mkdir_p( $d ) { return is_dir( $d ) || mkdir( $d, 0777, true ); }
function wp_upload_dir( $t = null, $c = true ) { return array( 'basedir' => '/subidas', 'baseurl' => 'http://prueba.local/wp-content/uploads', 'error' => false ); }
function trailingslashit( $s ) { return rtrim( $s, '/\\' ) . '/'; }

// Correo: guarda lo enviado, con el contenido de los adjuntos en el momento de enviar y la versión en texto
function wp_mail( $to, $asunto, $mensaje, $cabeceras = '', $adjuntos = array() ) {
	$m = new stdClass();
	$m->AltBody = '';
	do_action( 'phpmailer_init', $m );
	$adj = array();
	foreach ( (array) $adjuntos as $a ) $adj[ basename( $a ) ] = file_exists( $a ) ? file_get_contents( $a ) : null;
	if ( ! empty( $GLOBALS['correo_falla'] ) && ( true === $GLOBALS['correo_falla'] || $GLOBALS['correo_falla'] === $to ) ) return false;
	$GLOBALS['correos'][] = array( 'to' => $to, 'asunto' => $asunto, 'mensaje' => $mensaje, 'cabeceras' => (array) $cabeceras, 'adj' => $adj, 'rutas' => (array) $adjuntos, 'alt' => $m->AltBody );
	return true;
}

// $wpdb sobre SQLite en memoria, con una tabla de opciones como la de WordPress (option_name único)
class FakeWpdb {
	public $options = 'wp_options';
	public $pdo;
	public $suprimir = false;
	public $antes_update = null;
	public $antes_insert = null;
	function __construct() {
		$this->pdo = new PDO( 'sqlite::memory:' );
		$this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->pdo->exec( "CREATE TABLE wp_options (option_id INTEGER PRIMARY KEY AUTOINCREMENT, option_name TEXT NOT NULL UNIQUE, option_value TEXT NOT NULL, autoload TEXT NOT NULL DEFAULT 'yes')" );
	}
	function prepare( $sql, ...$args ) {
		if ( 1 === count( $args ) && is_array( $args[0] ) ) $args = $args[0];
		$i = 0;
		return preg_replace_callback( '/%[sd]/', function ( $m ) use ( &$i, $args ) { $v = $args[ $i++ ]; return '%d' === $m[0] ? (string) (int) $v : $this->pdo->quote( (string) $v ); }, $sql );
	}
	function query( $sql ) {
		$sql = ltrim( $sql );
		if ( $this->antes_update && 0 === stripos( $sql, 'UPDATE' ) ) { $f = $this->antes_update; $this->antes_update = null; $f( $this ); }
		if ( $this->antes_insert && 0 === stripos( $sql, 'INSERT' ) ) { $f = $this->antes_insert; $this->antes_insert = null; $f( $this ); }
		try { return $this->pdo->exec( $sql ); }
		catch ( PDOException $e ) { if ( ! $this->suprimir ) echo 'FALLO error SQL: ' . $e->getMessage() . "\n"; return false; }
	}
	function get_var( $sql ) { $v = $this->pdo->query( $sql )->fetchColumn(); return false === $v ? null : $v; }
	function get_col( $sql ) { return $this->pdo->query( $sql )->fetchAll( PDO::FETCH_COLUMN ); }
	function suppress_errors( $s = true ) { $a = $this->suprimir; $this->suprimir = $s; return $a; }
	function opcion( $nombre ) { return $this->get_var( $this->prepare( 'SELECT option_value FROM wp_options WHERE option_name = %s', $nombre ) ); }
	function poner( $nombre, $valor ) { $this->pdo->exec( $this->prepare( 'INSERT OR REPLACE INTO wp_options (option_name, option_value, autoload) VALUES (%s, %s, %s)', $nombre, $valor, 'off' ) ); }
}
$GLOBALS['wpdb'] = new FakeWpdb();

// WooCommerce mínimo
class FechaWC { public $ts; function __construct( $ts ) { $this->ts = $ts; } function getTimestamp() { return $this->ts; } }
class Producto {
	public $peso; public $formato; public $nombre;
	function __construct( $peso, $formato, $nombre = '' ) { $this->peso = $peso; $this->formato = $formato; $this->nombre = $nombre; }
	function get_weight() { return $this->peso; }
	function get_attribute( $a ) { return 'pa_formato' === $a ? $this->formato : ''; }
	function get_name() { return $this->nombre; }
}
class Linea {
	public $d;
	function __construct( array $d ) { $this->d = $d; }
	function get_product() { return $this->d['producto'] ?? null; }
	function get_name() { return $this->d['nombre'] ?? ''; }
	function get_quantity() { return $this->d['cantidad'] ?? 1; }
	function get_total() { return $this->d['total'] ?? 0; }
	function get_total_tax() { return $this->d['iva'] ?? 0; }
	function get_meta( $k ) { return $this->d['meta'][ $k ] ?? ''; }
	function get_method_id() { return $this->d['metodo'] ?? ''; }
	function get_tax_total() { return $this->d['iva_lineas'] ?? 0; }
	function get_shipping_tax_total() { return $this->d['iva_envio'] ?? 0; }
	function get_rate_percent() { return $this->d['tipo'] ?? null; }
	function get_rate_id() { return 1; }
}
class WC_Order {
	public $id; public $meta; public $notas = array(); public $d;
	function __construct( $id, array $d ) { $this->id = $id; $this->d = $d; $this->meta = $d['meta'] ?? array(); }
	function get_id() { return $this->id; }
	function get_meta( $k ) { return $this->meta[ $k ] ?? ''; }
	function update_meta_data( $k, $v ) { $this->meta[ $k ] = $v; }
	function delete_meta_data( $k ) { unset( $this->meta[ $k ] ); }
	function save_meta_data() { $GLOBALS['bd_meta'][ $this->id ] = $this->meta; }
	function read_meta_data( $forzar = false ) { if ( $forzar && isset( $GLOBALS['bd_meta'][ $this->id ] ) ) $this->meta = $GLOBALS['bd_meta'][ $this->id ]; }
	function add_order_note( $n ) { $this->notas[] = $n; }
	function get_items( $tipo = 'line_item' ) { return $this->d['items'][ $tipo ] ?? array(); }
	function get_total() { return $this->d['total']; }
	function get_total_tax() { return $this->d['iva'] ?? 0; }
	function get_total_refunded() { return $this->d['devuelto'] ?? 0; }
	function get_amount() { return $this->d['importe'] ?? 0; }
	function get_currency() { return 'EUR'; }
	function get_payment_method() { return $this->d['pago'] ?? 'woocommerce_payments'; }
	function get_payment_method_title() { return $this->d['pago_titulo'] ?? 'Tarjeta'; }
	function get_status() { return $this->d['estado'] ?? 'processing'; }
	function has_status( $s ) { return in_array( $this->get_status(), (array) $s, true ); }
	function get_billing_email() { return $this->d['email'] ?? 'laura@example.com'; }
	function get_billing_first_name() { return $this->d['nombre'] ?? 'Laura García Pons'; }
	function get_billing_last_name() { return ''; }
	function get_billing_company() { return $this->d['razon'] ?? ''; }
	// Pagado: la fecha indicada o, si está en un estado pagado, ahora (como WooCommerce al pasar a "Procesando")
	function get_date_paid() {
		if ( isset( $this->d['pagado'] ) ) return new FechaWC( $this->d['pagado'] );
		return in_array( $this->get_status(), array( 'processing', 'completed' ), true ) ? new FechaWC( $GLOBALS['ahora']->getTimestamp() ) : null;
	}
	function get_billing_postcode() { return '08013'; }
	function get_billing_city() { return 'Barcelona'; }
	function get_customer_id() { return $this->d['cliente'] ?? 0; }
	function get_order_number() { return (string) $this->id; }
	function get_order_key() { return 'wc_order_clave' . $this->id; }
	function get_date_created() { return new FechaWC( $this->d['creado'] ?? strtotime( '2026-10-02 10:00:00 Europe/Madrid' ) ); }
}
function wc_get_order( $id ) { return $GLOBALS['pedidos'][ $id ] ?? false; }
function wc_price( $n, $a = array() ) { return '<span>' . dip_euros( $n ) . '</span>'; }
class CorreoWC {
	function wrap_message( $t, $c ) { return '<h1>' . $t . '</h1>' . $c; }
	function send( $a, $asunto, $html ) { $GLOBALS['wc_correos'][] = array( $a, $asunto, $html ); return true; }
}
class TiendaWC { function mailer() { return new CorreoWC(); } }
function WC() { return new TiendaWC(); }

require '/p/inc/ajustes.php';
require '/p/inc/negocio.php';
require '/p/inc/textos.php';
require '/p/inc/envio.php';
require '/p/inc/entrega.php';
require '/p/inc/cuentas.php';
require '/p/inc/factura.php';
require '/p/inc/factura-simplificada.php';

function ok( $condicion, $texto, $detalle = null ) {
	echo ( $condicion ? 'OK   ' : 'FALLO' ) . ' ' . $texto . ( ! $condicion && null !== $detalle ? '  → ' . json_encode( $detalle, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE ) : '' ) . "\n";
}
function ahora( $s ) { $GLOBALS['ahora'] = new DateTimeImmutable( $s, wp_timezone() ); }
/** Linea de hielo seco: kg, formato y precio sin IVA por pack (IVA 21 % redondeado por línea, como WooCommerce). */
function hielo( $kg, $mm, $precio, $cantidad = 1 ) {
	$total = round( $precio * $cantidad, 2 );
	return new Linea( array( 'producto' => new Producto( $kg, $mm . 'mm' ), 'nombre' => 'Hielo seco - ' . $kg . ' kg, ' . $mm . 'mm', 'cantidad' => $cantidad, 'total' => $total, 'iva' => round( $total * 0.21, 2 ) ) );
}
/**
 * Pedido de prueba. Por defecto: 1 pack de 10 kg de 3 mm (53,30 + IVA) y envío de 11 kg facturables (14,31 + IVA):
 * 64,49 + 17,32 = 81,81 € con 14,20 € de IVA. Pagado con tarjeta y en "Procesando".
 */
function pedido( $id, array $o = array() ) {
	$lineas = $o['lineas'] ?? array( hielo( 10, 3, 53.30 ) );
	$envio  = array_key_exists( 'envio', $o ) ? $o['envio'] : 14.31;
	$items  = array( 'line_item' => $lineas, 'shipping' => array(), 'fee' => array(), 'tax' => array() );
	if ( null !== $envio ) $items['shipping'][] = new Linea( array( 'metodo' => $envio > 0 ? 'flat_rate' : 'local_pickup', 'nombre' => 'Envío', 'total' => $envio, 'iva' => round( $envio * 0.21, 2 ) ) );
	if ( ! empty( $o['sabado'] ) ) $items['fee'][] = new Linea( array( 'nombre' => 'recogida' === ( $o['metodo'] ?? '' ) ? 'Recogida en sábado' : 'Entrega en sábado', 'total' => 9.70, 'iva' => 2.04 ) );
	if ( ! empty( $o['cargo_sin_iva'] ) ) $items['fee'][] = new Linea( array( 'nombre' => 'Cargo sin IVA', 'total' => $o['cargo_sin_iva'], 'iva' => 0 ) );
	$iva_l = 0; $iva_e = 0; $total = 0;
	foreach ( $items['line_item'] as $l ) { $iva_l += $l->get_total_tax(); $total += $l->get_total() + $l->get_total_tax(); }
	foreach ( array_merge( $items['shipping'], $items['fee'] ) as $l ) { $iva_e += $l->get_total_tax(); $total += $l->get_total() + $l->get_total_tax(); }
	$con_iva = $o['con_iva'] ?? true;
	if ( $con_iva ) $items['tax'][] = new Linea( array( 'tipo' => 21.0, 'iva_lineas' => round( $iva_l, 2 ), 'iva_envio' => round( $iva_e, 2 ) ) );
	$meta = array( '_dip_idioma' => $o['idioma'] ?? 'es', '_dip_empresa' => empty( $o['empresa'] ) ? 0 : 1, '_dip_fecha_entrega' => $o['entrega'] ?? '2026-10-03', '_dip_metodo_entrega' => $o['metodo'] ?? 'envio' );
	if ( ! empty( $o['nif'] ) ) $meta['_billing_nif'] = $o['nif'];
	$meta = array_merge( $meta, $o['meta'] ?? array() );
	$d = array(
		'items' => $items, 'meta' => $meta,
		'total' => $o['total'] ?? round( $total, 2 ),
		'iva' => $con_iva ? ( $o['iva'] ?? round( $iva_l + $iva_e, 2 ) ) : 0,
	);
	foreach ( array( 'estado', 'pago', 'pago_titulo', 'email', 'nombre', 'cliente', 'creado', 'devuelto', 'razon', 'pagado' ) as $k ) if ( isset( $o[ $k ] ) ) $d[ $k ] = $o[ $k ];
	return $GLOBALS['pedidos'][ $id ] = new WC_Order( $id, $d );
}
/**
 * Cambio de estado como lo hace WooCommerce: primero woocommerce_order_status_{estado} (ahí va el email
 * «¿Necesitas factura?», prioridad 30) y después woocommerce_order_status_changed (ahí sale la factura).
 * Un pedido de prueba que está "en espera" o pendiente se queda así (para probar que no se factura sin pagar).
 */
function pasar( $p, $a ) {
	$de = $p->d['estado'] ?? 'pending';
	if ( ! in_array( $p->d['estado'] ?? '', array( 'on-hold', 'pending', 'failed', 'cancelled' ), true ) ) $p->d['estado'] = $a;
	do_action( 'woocommerce_order_status_' . $a, $p->get_id(), $p );
	do_action( 'woocommerce_order_status_changed', $p->get_id(), $de, $a, $p );
}
/** ¿Está este texto en el PDF? (en Windows-1252 y con los paréntesis escapados, como lo escribe el generador) */
function en_pdf( $pdf, $texto ) { return false !== strpos( $pdf, strtr( DIP_FS_PDF::win( $texto ), array( '\\' => '\\\\', '(' => '\\(', ')' => '\\)' ) ) ); }
function correos_a( $a ) { return array_values( array_filter( $GLOBALS['correos'], static fn( $c ) => $c['to'] === $a ) ); }
function contador( $serie = 'W2026' ) { return $GLOBALS['wpdb']->opcion( 'dip_fs_contador_' . $serie ); }

// Jueves 2 de octubre de 2026, 16:30 (Madrid). Módulo en marcha desde el 1 de octubre.
ahora( '2026-10-02 16:30:00' );
$GLOBALS['opciones']['dip_fs_desde'] = strtotime( '2026-10-01 00:00:00 Europe/Madrid' );
`);

const escenarios = {
  'Se emite al pagar con tarjeta': String.raw`
    $p = pedido( 1001 );
    pasar( $p, 'processing' );
    ok( 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'primera factura de la serie: W2026-00001', $p->get_meta( '_dip_fs_numero' ) );
    ok( '1' === contador(), 'el contador de la serie W2026 queda en 1', contador() );
    ok( '2026-10-02 16:30:00' === $p->get_meta( '_dip_fs_fecha' ), 'guarda la fecha de expedición', $p->get_meta( '_dip_fs_fecha' ) );
    $d = $p->get_meta( '_dip_fs_datos' );
    ok( is_array( $d ) && 81.81 === $d['total'] && 67.61 === $d['base'] && 14.2 === $d['cuota'] && 21.0 === (float) $d['tipo_iva'], 'guarda el desglose: base 67,61 + IVA 21 % 14,20 = 81,81', $d );
    $c = correos_a( 'laura@example.com' );
    ok( 1 === count( $c ), 'un email al cliente', array_column( $GLOBALS['correos'], 'asunto' ) );
    ok( 'Tu factura simplificada W2026-00001 · DryIcePack' === ( $c[0]['asunto'] ?? '' ), 'asunto: ' . ( $c[0]['asunto'] ?? '' ) );
    ok( in_array( 'From: DryIcePack <info@dryicepack.es>', $c[0]['cabeceras'], true ) && in_array( 'Content-Type: text/html; charset=UTF-8', $c[0]['cabeceras'], true ), 'desde DryIcePack <info@dryicepack.es>, en HTML', $c[0]['cabeceras'] );
    $adj = $c[0]['adj'];
    ok( array( 'Factura-simplificada-W2026-00001.pdf' ) === array_keys( $adj ) && 0 === strpos( (string) reset( $adj ), '%PDF-1.4' ), 'PDF adjunto: Factura-simplificada-W2026-00001.pdf', array_keys( $adj ) );
    ok( ! file_exists( $c[0]['rutas'][0] ) && ! is_dir( dirname( $c[0]['rutas'][0] ) ), 'el archivo temporal se borra después de enviar' );
    ok( ! $GLOBALS['wc_correos'], 'no sale además el email «¿Necesitas factura?»', array_column( $GLOBALS['wc_correos'], 1 ) );
    $rel = $p->get_meta( '_dip_fs_archivo' );
    ok( 0 === strpos( $rel, 'dryicepack-facturas/2026/W2026-00001-' ) && file_get_contents( '/subidas/' . $rel ) === reset( $adj ), 'copia guardada en uploads/dryicepack-facturas/2026/ con nombre no adivinable, igual que el adjunto', $rel );
    ok( false !== strpos( file_get_contents( '/subidas/dryicepack-facturas/.htaccess' ), 'Deny from all' ) && '' === file_get_contents( '/subidas/dryicepack-facturas/index.php' ), 'carpeta protegida: .htaccess "Deny from all" e index.php vacío' );
    ok( (bool) preg_grep( '/^Factura simplificada W2026-00001 expedida: 81,81 € IVA incluido \(base 67,61 € \+ IVA 21 % 14,20 €\)\.$/u', $p->notas ), 'nota en el pedido con el desglose', $p->notas );
    ok( (bool) preg_grep( '/^Enviada la factura simplificada W2026-00001 a laura@example.com con el PDF adjunto\.$/', $p->notas ), 'nota de envío', $p->notas );
    ok( $p->get_meta( '_dip_fs_enviada' ) > 0 && 'laura@example.com' === $p->get_meta( '_dip_fs_enviada_a' ), 'anota cuándo y a quién se envió' );
    ok( null === $GLOBALS['wpdb']->opcion( 'dip_fs_emitiendo_1001' ), 'libera la reserva del pedido' );
    // Una sola vez: el pedido pasa después a completado
    $p->d['estado'] = 'completed';
    pasar( $p, 'completed' );
    ok( 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ) && '1' === contador() && 1 === count( correos_a( 'laura@example.com' ) ), 'al completar no sale otra factura ni otro email' );
    dip_fs_emitir( $p, 'manual' );
    ok( '1' === contador(), 'la acción manual tampoco emite otra' );
    ok( 'Ya te hemos enviado por email la factura simplificada W2026-00001.' === dip_fs_texto_gracias( $p, 'es' ), 'página de gracias: ' . dip_fs_texto_gracias( $p, 'es' ) );
  `,

  'Cuándo NO se emite': String.raw`
    $casos = array(
      'casilla de empresa marcada'           => array( 'empresa' => 1, 'nif' => 'B66800103' ),
      'con NIF/CIF'                          => array( 'nif' => '12345678Z' ),
      'más de 400 € (400,01 €)'              => array( 'total' => 400.01 ),
      'cuenta de empresa'                    => array( 'cliente' => 7 ),
      'cuenta de empresa (canal en el pedido)' => array( 'meta' => array( '_dip_canal' => 'cuenta' ) ),
      'factura mensual'                      => array( 'pago' => 'dip_factura_mensual' ),
      'pidió factura completa en /factura/'  => array( 'meta' => array( '_dip_factura_datos' => array( 'nif' => 'B66800103' ) ) ),
      'ya tiene factura emitida'             => array( 'meta' => array( '_dip_factura_emitida' => 1 ) ),
      'con devoluciones'                     => array( 'devuelto' => 5 ),
      'sin pagar (en espera)'                => array( 'estado' => 'on-hold' ),
      'efectivo al recoger, aún sin recoger' => array( 'pago' => 'cod', 'metodo' => 'recogida', 'envio' => 0 ),
      'creado antes de poner en marcha el módulo' => array( 'creado' => strtotime( '2026-09-30 12:00 Europe/Madrid' ) ),
      'email del cliente no válido'          => array( 'email' => 'laura.example.com' ),
      'con razón social (sin casilla ni NIF)' => array( 'razon' => 'Bar Sol SL' ),
      'las líneas no suman el total'         => array( 'total' => 90.00 ),
      'una línea sin IVA (cuota ≠ 21 % de la base)' => array( 'cargo_sin_iva' => 10.00 ),
      'más de 18 líneas (no caben en el PDF)' => array( 'lineas' => array_map( static fn() => hielo( 3, 3, 1.00 ), range( 1, 18 ) ) ),
    );
    $GLOBALS['usuarios'][7] = array( 'dip_cuenta_empresa' => '1' );
    $id = 2000;
    foreach ( $casos as $nombre => $o ) {
      $p = pedido( ++$id, $o );
      pasar( $p, 'processing' );
      ok( '' === $p->get_meta( '_dip_fs_numero' ) && '' !== dip_fs_motivo( $p ), 'no se emite: ' . $nombre . ' → «' . dip_fs_motivo( $p ) . '»', $p->get_meta( '_dip_fs_numero' ) );
    }
    ok( null === contador() && ! correos_a( 'laura@example.com' ), 'ningún número gastado y ningún email de factura' );
    $p = pedido( 2100, array( 'lineas' => array( hielo( 20, 3, 330.58 ) ), 'envio' => null ) );
    pasar( $p, 'processing' );
    ok( 400.0 === (float) $p->get_total() && 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'exactamente 400,00 € sí se emite (no supera los 400 €)', dip_fs_motivo( $p ) );
    ok( 330.58 === $p->get_meta( '_dip_fs_datos' )['base'], '400,00 € IVA incluido: base 330,58 €' );
    // Redondeo del IVA sobre el total (WooCommerce): el pedido suma 1 céntimo más que sus líneas → va a la línea mayor
    $r = pedido( 2101, array( 'total' => 81.82, 'iva' => 14.21 ) );
    pasar( $r, 'processing' );
    $dr = $r->get_meta( '_dip_fs_datos' );
    ok( 'W2026-00002' === $r->get_meta( '_dip_fs_numero' ) && array( 64.50, 17.32 ) === array_column( $dr['lineas'], 'importe' ) && 81.82 === $dr['total'] && 14.21 === $dr['cuota'], '1 céntimo de redondeo: las líneas suman el total (64,50 + 17,32 = 81,82)', $dr );
    // Pedido de antes de la puesta en marcha: recibe el email de siempre y se puede emitir a mano
    $viejo = $GLOBALS['pedidos'][2012];
    ok( 1 === count( array_filter( $GLOBALS['wc_correos'], static fn( $c ) => false !== strpos( $c[1], 'Pedido 2012' ) ) ), 'el pedido anterior a la puesta en marcha recibe el email «¿Necesitas factura?»', array_column( $GLOBALS['wc_correos'], 1 ) );
    $acciones = apply_filters( 'woocommerce_order_actions', array(), $viejo );
    ok( isset( $acciones['dip_fs_emitir'] ), 'y en el pedido aparece «Emitir factura simplificada (email)»', $acciones );
    ob_start(); do_action( 'woocommerce_admin_order_data_after_billing_address', $viejo ); $caja = ob_get_clean();
    ok( false !== strpos( $caja, 'comprueba que no está ya facturado en la aplicación de la gestoría' ), 'el pedido anterior avisa de que puede estar ya facturado en la gestoría', $caja );
    do_action( 'woocommerce_order_action_dip_fs_emitir', $viejo );
    ok( 'W2026-00003' === $viejo->get_meta( '_dip_fs_numero' ) && (bool) preg_grep( '/Emitida desde Acciones del pedido/', $viejo->notas ), 'emitida a mano: W2026-00003', $viejo->notas );
    $acciones = apply_filters( 'woocommerce_order_actions', array(), $viejo );
    ok( isset( $acciones['dip_fs_reenviar'] ) && ! isset( $acciones['dip_fs_emitir'] ), 'después: «Reenviar factura simplificada (email)»', $acciones );
    $empresa = $GLOBALS['pedidos'][2001];
    $acciones = apply_filters( 'woocommerce_order_actions', array(), $empresa );
    ok( ! isset( $acciones['dip_fs_emitir'] ) && ! isset( $acciones['dip_fs_reenviar'] ), 'a una compra de empresa no se le ofrece emitirla' );
    do_action( 'woocommerce_order_action_dip_fs_emitir', $empresa );
    ok( '' === $empresa->get_meta( '_dip_fs_numero' ) && (bool) preg_grep( '/^Factura simplificada no emitida: Compra para empresa/u', $empresa->notas ), 'y si se fuerza, no la emite y lo anota', $empresa->notas );
    ok( '' === dip_fs_texto_gracias( $empresa, 'es' ), 'página de gracias de una empresa: sin frase de factura simplificada' );
  `,

  'Efectivo al recoger: al completar': String.raw`
    $p = pedido( 3001, array( 'pago' => 'cod', 'pago_titulo' => 'Efectivo al recoger', 'metodo' => 'recogida', 'envio' => 0, 'entrega' => '2026-10-02', 'nombre' => 'Jordi' ) );
    pasar( $p, 'processing' );
    ok( '' === $p->get_meta( '_dip_fs_numero' ), 'al hacer el pedido ("Procesando") todavía no: no ha pagado' );
    ok( ! $GLOBALS['wc_correos'], 'tampoco sale «¿Necesitas factura?»: la factura llegará al recoger', array_column( $GLOBALS['wc_correos'], 1 ) );
    ok( 'Te enviaremos la factura simplificada por email cuando recojas el pedido.' === dip_fs_texto_gracias( $p, 'es' ), 'página de gracias: ' . dip_fs_texto_gracias( $p, 'es' ) );
    $p->d['estado'] = 'completed';
    pasar( $p, 'completed' );
    ok( 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'al completar (ha pagado en efectivo): W2026-00001' );
    $d = $p->get_meta( '_dip_fs_datos' );
    ok( 1 === count( $d['lineas'] ) && 'hielo' === $d['lineas'][0]['tipo'], 'la recogida gratis no es una línea de la factura', $d['lineas'] );
    ok( '' === $d['fecha_operacion'], 'recoge el mismo día que se expide: sin fecha de operación aparte', $d['fecha_operacion'] );
    ok( 'Efectivo al recoger' === $d['pago'], 'forma de pago: Efectivo al recoger' );
    ok( 1 === count( correos_a( 'laura@example.com' ) ) && ! $GLOBALS['wc_correos'], 'un solo email: la factura' );
    // Recogió ayer y el pedido se marca hoy como completado: la operación es la recogida (ayer)
    $q = pedido( 3002, array( 'pago' => 'cod', 'pago_titulo' => 'Efectivo al recoger', 'metodo' => 'recogida', 'envio' => 0, 'entrega' => '2026-10-01' ) );
    pasar( $q, 'processing' );
    $q->d['estado'] = 'completed';
    pasar( $q, 'completed' );
    $e = $q->get_meta( '_dip_fs_datos' );
    ok( '2026-10-01' === $e['fecha_operacion'] && 'entrega' === $e['operacion_por'], 'recogida el 01/10, completado el 02/10: fecha de la operación 01/10', $e );
    $m = correos_a( 'laura@example.com' )[1]['mensaje'] ?? '';
    ok( false !== strpos( $m, '01/10/2026 · recogida en Mataró' ), 'el email dice «01/10/2026 · recogida en Mataró»' );
  `,

  'Numeración correlativa': String.raw`
    foreach ( array( 4001, 4002, 4003 ) as $id ) { $p = pedido( $id ); pasar( $p, 'processing' ); }
    $n = array_map( static fn( $id ) => $GLOBALS['pedidos'][ $id ]->get_meta( '_dip_fs_numero' ), array( 4001, 4002, 4003 ) );
    ok( array( 'W2026-00001', 'W2026-00002', 'W2026-00003' ) === $n, 'tres pedidos seguidos: 00001, 00002 y 00003', $n );
    ok( 'W2026-00004' === dip_fs_numero_previsto(), 'número siguiente previsto (ajustes): W2026-00004', dip_fs_numero_previsto() );
    // Otro proceso se adelanta entre la lectura y el UPDATE: el contador vale 5, el otro deja 7
    $GLOBALS['wpdb']->poner( 'dip_fs_contador_X2026', '5' );
    $GLOBALS['wpdb']->antes_update = static function ( $db ) { $db->pdo->exec( "UPDATE wp_options SET option_value = '7' WHERE option_name = 'dip_fs_contador_X2026'" ); };
    ok( 8 === dip_fs_siguiente_numero( 'X2026' ) && '8' === contador( 'X2026' ), 'si otro pedido se adelanta, vuelve a leer y toma el siguiente libre (8), sin repetir', contador( 'X2026' ) );
    // Primera factura de una serie con dos procesos a la vez: el otro crea el contador justo antes
    $GLOBALS['wpdb']->antes_insert = static function ( $db ) { $db->poner( 'dip_fs_contador_Y2026', '1' ); };
    ok( 2 === dip_fs_siguiente_numero( 'Y2026' ), 'contador nuevo creado a la vez por otro proceso: este toma el 2', contador( 'Y2026' ) );
    // Reserva del pedido: otro proceso lo está facturando
    $GLOBALS['wpdb']->poner( 'dip_fs_emitiendo_4010', (string) time() );
    $p = pedido( 4010 );
    ok( false === dip_fs_emitir( $p ) && '' === $p->get_meta( '_dip_fs_numero' ) && '3' === contador(), 'pedido reservado por otro proceso: no se emite ni se gasta número' );
    $GLOBALS['wpdb']->poner( 'dip_fs_emitiendo_4010', (string) ( time() - 700 ) );
    ok( 'W2026-00004' === dip_fs_emitir( $p ) && null === $GLOBALS['wpdb']->opcion( 'dip_fs_emitiendo_4010' ), 'reserva de hace más de 10 minutos (proceso cortado): se libera y se emite W2026-00004' );
    // El otro proceso ya la emitió y guardó en la base de datos, pero este objeto aún no lo sabe
    $p = pedido( 4011 );
    $GLOBALS['bd_meta'][4011] = $p->meta + array( '_dip_fs_numero' => 'W2026-00005' );
    $GLOBALS['wpdb']->poner( 'dip_fs_contador_W2026', '5' );
    ok( false === dip_fs_emitir( $p ) && '5' === contador() && 'W2026-00005' === $p->get_meta( '_dip_fs_numero' ), 'emitida por otro proceso mientras tanto: vuelve a leer el pedido y no emite otra', array( contador(), $p->get_meta( '_dip_fs_numero' ) ) );
  `,

  'Numeración por año y serie configurable': {
    antes: "define( 'DIP_FS_FIN', '2030-01-01' );", // sin la parada de VeriFactu, para probar el cambio de año
    codigo: String.raw`
    ahora( '2026-12-31 23:59:30' );
    $a = pedido( 5001 ); pasar( $a, 'processing' );
    ahora( '2027-01-01 00:00:10' );
    $b = pedido( 5002 ); pasar( $b, 'processing' );
    $c = pedido( 5003 ); pasar( $c, 'processing' );
    ok( 'W2026-00001' === $a->get_meta( '_dip_fs_numero' ) && 'W2027-00001' === $b->get_meta( '_dip_fs_numero' ) && 'W2027-00002' === $c->get_meta( '_dip_fs_numero' ), '31/12 23:59 → W2026-00001; 1/1 00:00 → W2027-00001, W2027-00002 (empieza de nuevo cada año, hora de Madrid)', array( $a->get_meta( '_dip_fs_numero' ), $b->get_meta( '_dip_fs_numero' ), $c->get_meta( '_dip_fs_numero' ) ) );
    ok( 0 === strpos( $b->get_meta( '_dip_fs_archivo' ), 'dryicepack-facturas/2027/' ), 'el PDF de 2027 va a la carpeta 2027' );
  `,
  },

  'Serie configurable en los ajustes': String.raw`
    $GLOBALS['opciones']['dip_ajustes'] = array( 'serie_simplificada' => 'web-1' );
    $p = pedido( 5101 ); pasar( $p, 'processing' );
    ok( 'WEB12026-00001' === $p->get_meta( '_dip_fs_numero' ), 'serie "web-1" → WEB1 + año: WEB12026-00001', $p->get_meta( '_dip_fs_numero' ) );
    $s = dip_sanear_ajustes( array( 'serie_simplificada' => 'w-1!', 'email_avisos' => 'info@dryicepack.es' ) );
    ok( 'W1' === $s['serie_simplificada'] && 0 === $s['factura_simplificada'], 'saneado: "w-1!" → "W1"; casilla sin marcar → desactivada', $s );
    $s = dip_sanear_ajustes( array( 'serie_simplificada' => '', 'factura_simplificada' => '1' ) );
    ok( 'W' === $s['serie_simplificada'] && 1 === $s['factura_simplificada'], 'serie vacía → "W"; casilla marcada → activada', $s );
    $def = dip_ajustes_por_defecto();
    ok( 1 === $def['factura_simplificada'] && 'W' === $def['serie_simplificada'], 'por defecto: activada y serie W' );
    ob_start(); dip_pantalla_ajustes(); $html = ob_get_clean();
    ok( false !== strpos( $html, 'name="dip_ajustes[factura_simplificada]"' ) && false !== strpos( $html, 'name="dip_ajustes[serie_simplificada]"' ), 'ajustes: interruptor y serie' );
    ok( (bool) preg_match( '/id="dip-siguiente" type="text" class="regular-text code" value="WEB12026-00002" readonly/', $html ), 'ajustes: siguiente factura WEB12026-00002, solo lectura' );
    ok( false !== strpos( $html, 'VeriFactu' ), 'ajustes: avisa de la parada por VeriFactu' );
    ok( false !== strpos( $html, 'name="action" value="dip_fs_libro"' ) && false !== strpos( $html, 'value="2026-09"' ) && false !== strpos( $html, 'no las vuelvas a facturar' ), 'ajustes: descarga del libro de facturas (mes anterior por defecto)' );
  `,

  'Ajuste desactivado': String.raw`
    $GLOBALS['opciones']['dip_ajustes'] = array( 'factura_simplificada' => 0 );
    $p = pedido( 5201 ); pasar( $p, 'processing' );
    ok( '' === $p->get_meta( '_dip_fs_numero' ) && null === contador(), 'desactivada en los ajustes: no se emite' );
    ok( 1 === count( $GLOBALS['wc_correos'] ) && 'Pedido 5201: ¿necesitas factura con tus datos?' === $GLOBALS['wc_correos'][0][1], 'y sale el email «¿Necesitas factura?» de siempre', array_column( $GLOBALS['wc_correos'], 1 ) );
    $acciones = apply_filters( 'woocommerce_order_actions', array(), $p );
    ok( isset( $acciones['dip_fs_emitir'] ), 'se puede emitir a mano desde el pedido' );
  `,

  'Contenido legal y cálculo del IVA incluido': String.raw`
    $p = pedido( 6001, array( 'sabado' => 1, 'entrega' => '2026-10-03', 'nombre' => 'Begoña Muñoz Ibáñez' ) );
    pasar( $p, 'processing' );
    $d   = $p->get_meta( '_dip_fs_datos' );
    $pdf = dip_fs_pdf_de_pedido( $p );
    ok( 93.55 === $d['total'] && 16.24 === $d['cuota'] && 77.31 === $d['base'], 'con sábado: 64,49 + 17,32 + 11,74 = 93,55 € (base 77,31 + IVA 16,24)', array( $d['total'], $d['base'], $d['cuota'] ) );
    ok( abs( $d['base'] + $d['cuota'] - $d['total'] ) < 0.001, 'base + cuota = total' );
    $tipos = array_column( $d['lineas'], 'tipo' );
    ok( array( 'hielo', 'envio', 'sabado' ) === $tipos, 'líneas: hielo, envío y suplemento de sábado', $tipos );
    ok( array( 64.49, 17.32, 11.74 ) === array_column( $d['lineas'], 'importe' ), 'importes por línea con IVA: 64,49 · 17,32 · 11,74', array_column( $d['lineas'], 'importe' ) );
    ok( '' === $d['fecha_operacion'] && '2026-10-02' === $d['fecha_expedicion'], 'pagado hoy (02/10) con entrega el 03/10: fecha de expedición 02/10 y sin fecha de operación aparte (el IVA se devenga al cobrar)', $d );
    ok( ! en_pdf( $pdf, 'FECHA DE LA OPERACIÓN' ) && ! en_pdf( $pdf, '03/10/2026' ), 'el PDF no presenta la entrega futura como fecha de la operación' );
    $exige = array(
      'título "Factura simplificada"'     => 'Factura simplificada',
      'número y serie'                    => 'W2026-00001',
      'fecha de expedición'               => 'FECHA DE EXPEDICIÓN',
      'fecha de expedición (valor)'       => '02/10/2026',
      'razón social del emisor'           => 'INDUNOVA IMS S.L.',
      'NIF del emisor'                    => 'NIF B66800103',
      'domicilio social'                  => 'Ronda Alfonso X El Sabio, 10, 2º 1ª',
      'bienes: formato'                   => 'Hielo seco en pellets de 3 mm',
      'bienes: kilos'                     => 'Pack de 10 kg · caja EPS incluida',
      'envío'                             => 'Envío por mensajería MRW',
      'suplemento de sábado'              => 'Suplemento de entrega en sábado',
      'tipo impositivo'                   => 'IVA 21 %',
      'mención IVA incluido'              => 'IVA incluido en todos los importes.',
      'cuota desglosada'                  => "Cuota de IVA: 16,24\u{00A0}€.",
      'base imponible'                    => "77,31\u{00A0}€",
      'importe total'                     => "93,55\u{00A0}€",
      'total con la mención'              => 'Total (IVA incluido)',
      'referencia legal'                  => 'artículo 7 del Real Decreto 1619/2012',
    );
    $faltan = array();
    foreach ( $exige as $que => $texto ) if ( ! en_pdf( $pdf, $texto ) ) $faltan[] = $que . ': ' . $texto;
    ok( ! $faltan, 'el PDF lleva todo el contenido del art. 7 del RD 1619/2012 (' . count( $exige ) . ' comprobaciones)', $faltan );
    // Caracteres especiales en Windows-1252
    $esperado = array( 'ñ' => "Bego\xF1a Mu\xF1oz Ib\xE1\xF1ez", '€' => "93,55\xA0\x80", 'ó' => "Matar\xF3", '·' => "Pack de 10 kg \xB7 caja", '−78,5 °C' => "\x9678,5 \xB0C", 'º ª' => "2\xBA 1\xAA", 'Ó' => "EXPEDICI\xD3N" );
    $mal = array();
    foreach ( $esperado as $c => $bytes ) if ( false === strpos( $pdf, $bytes ) ) $mal[] = $c;
    ok( ! $mal, 'caracteres especiales en WinAnsi: ñ, €, ó, ·, − (raya corta), °, º, ª, Ó', $mal );
    ok( false === strpos( $pdf, "\xC3" ) && false === strpos( $pdf, "\xE2\x82\xAC" ), 'ni rastro de UTF-8 sin convertir dentro del PDF' );
    file_put_contents( '/salida/ejemplo.pdf', $pdf );
    // Cobrado ayer (transferencia que se marca hoy como pagada) y entrega dentro de un mes: fecha de la operación = cobro
    $a = pedido( 6004, array( 'creado' => strtotime( '2026-10-01 09:00 Europe/Madrid' ), 'pagado' => strtotime( '2026-10-01 12:00 Europe/Madrid' ), 'entrega' => '2026-11-03' ) );
    pasar( $a, 'processing' );
    $da  = $a->get_meta( '_dip_fs_datos' );
    $pda = dip_fs_pdf_de_pedido( $a );
    ok( '2026-10-01' === $da['fecha_operacion'] && 'pago' === $da['operacion_por'], 'cobrado el 01/10, factura el 02/10, entrega el 03/11: fecha de la operación 01/10 (cobro anticipado)', $da );
    ok( en_pdf( $pda, 'FECHA DE LA OPERACIÓN' ) && en_pdf( $pda, '01/10/2026' ) && en_pdf( $pda, 'cobro anticipado' ) && ! en_pdf( $pda, '03/11/2026' ), 'el PDF lo dice: FECHA DE LA OPERACIÓN 01/10/2026 · cobro anticipado' );
    file_put_contents( '/salida/ejemplo-operacion.pdf', $pda );
    // Sin impuestos calculados por WooCommerce, mismo resultado a partir del 21 %
    $q = pedido( 6002, array( 'con_iva' => false, 'total' => 81.81 ) );
    pasar( $q, 'processing' );
    $e = $q->get_meta( '_dip_fs_datos' );
    ok( 67.61 === $e['base'] && 14.2 === $e['cuota'] && 21.0 === (float) $e['tipo_iva'], 'sin impuestos en WooCommerce: 81,81 / 1,21 → base 67,61 + IVA 14,20', $e );
    // 16 mm y producto que no es hielo seco
    $l16 = dip_fs_texto_linea( array( 'tipo' => 'hielo', 'mm' => 16, 'kg' => 20 ), 'es' );
    ok( array( 'Hielo seco en nuggets de 16 mm', 'Pack de 20 kg · caja EPS incluida' ) === $l16, '16 mm: ' . implode( ' / ', $l16 ) );
    ok( array( 'Guantes térmicos', '' ) === dip_fs_texto_linea( array( 'tipo' => 'producto', 'nombre' => 'Guantes térmicos' ), 'es' ), 'otro producto: su nombre' );
    $r = pedido( 6003, array( 'lineas' => array( hielo( 3, 3, 29.80, 2 ), hielo( 15, 16, 75.90 ) ) ) );
    pasar( $r, 'processing' );
    $f = $r->get_meta( '_dip_fs_datos' )['lineas'];
    ok( 2 === $f[0]['cantidad'] && 3.0 === (float) $f[0]['kg'] && 3 === $f[0]['mm'] && 15.0 === (float) $f[1]['kg'] && 16 === $f[1]['mm'], 'varias líneas: 2 × 3 kg de 3 mm y 1 × 15 kg de 16 mm', $f );
  `,

  'PDF válido': String.raw`
    $p = pedido( 7001, array( 'sabado' => 1 ) );
    pasar( $p, 'processing' );
    $pdf = dip_fs_pdf_de_pedido( $p );
    ok( 0 === strpos( $pdf, "%PDF-1.4\n" ) && "%%EOF\n" === substr( $pdf, -6 ), 'empieza por %PDF-1.4 y acaba en %%EOF' );
    ok( preg_match( '/startxref\n(\d+)\n%%EOF\n$/', $pdf, $m ) && 0 === strpos( substr( $pdf, (int) $m[1] ), "xref\n0 10\n" ), 'startxref apunta a la tabla xref (9 objetos)', $m[1] ?? null );
    preg_match_all( '/^(\d{10}) 00000 n $/m', $pdf, $o );
    $mal = array();
    foreach ( $o[1] as $i => $off ) if ( 0 !== strpos( substr( $pdf, (int) $off ), ( $i + 1 ) . ' 0 obj' ) ) $mal[] = $i + 1;
    ok( 9 === count( $o[1] ) && ! $mal, 'cada entrada de la xref apunta a su objeto', $mal );
    ok( (bool) preg_match( '/trailer\n<< \/Size 10 \/Root 1 0 R \/Info 9 0 R >>/', $pdf ), 'trailer con Size, Root e Info' );
    ok( preg_match( '/<< \/Length (\d+) >>\nstream\n/', $pdf, $l, PREG_OFFSET_CAPTURE ) && "\nendstream" === substr( $pdf, $l[0][1] + strlen( $l[0][0] ) + (int) $l[1][0], 10 ), 'la longitud del contenido es exacta' );
    ok( 4 === preg_match_all( '/\/Encoding \/WinAnsiEncoding/', $pdf ) && false !== strpos( $pdf, '/BaseFont /Helvetica-Bold' ) && false !== strpos( $pdf, '/BaseFont /Courier' ), 'fuentes estándar (Helvetica y Courier) con WinAnsiEncoding, sin incrustar' );
    ok( false !== strpos( $pdf, '/MediaBox [0 0 595.28 841.89]' ) && 1 === preg_match_all( '/\/Type \/Page\b/', $pdf ), 'una página A4' );
    ok( false !== strpos( $pdf, '/Title <FEFF' ) && false !== strpos( $pdf, '/CreationDate (D:20261002163000)' ), 'título en UTF-16 y fecha de creación = expedición' );
    ok( $pdf === dip_fs_pdf( $p->get_meta( '_dip_fs_datos' ) ), 'rehecho con los datos guardados sale idéntico (para reenviar o descargar)' );
    ok( false !== strpos( $pdf, '(Total \\(IVA incluido\\)) Tj' ), 'paréntesis del texto escapados: (Total \\(IVA incluido\\)) Tj' );
    unlink( '/subidas/' . $p->get_meta( '_dip_fs_archivo' ) );
    ok( $pdf === dip_fs_pdf_de_pedido( $p ), 'si se borra la copia guardada, se rehace igual' );
    $w = DIP_FS_PDF::ancho( 'Hola', 'F1', 10 );
    ok( abs( $w - 20.56 ) < 0.001 && abs( DIP_FS_PDF::ancho( 'a', 'F3', 10 ) - 6 ) < 0.001 && abs( DIP_FS_PDF::ancho( 'Año', 'F2', 10 ) - 19.44 ) < 0.001, 'métricas AFM: "Hola" en Helvetica 10 = 20,56 pt; "Año" en negrita = 19,44 pt; Courier = 6 pt por letra', $w );
    ok( 'Ma' . "\xB7" === DIP_FS_PDF::win( 'Ma·' ) && 'Lodz' === DIP_FS_PDF::win( 'Łodz' ) && '?' === DIP_FS_PDF::win( '中' ), 'conversión: · se mantiene, Ł → L, lo imposible → ?' );
    $r = DIP_FS_PDF::recortar( str_repeat( 'Hielo seco ', 20 ), 'F2', 10, 100 );
    ok( DIP_FS_PDF::ancho( $r, 'F2', 10 ) <= 100 && '…' === mb_substr( $r, -1 ), 'textos largos se recortan con "…"' );
  `,

  'Idiomas (CA y EN)': String.raw`
    $ca = pedido( 8001, array( 'idioma' => 'ca', 'nombre' => 'Núria' ) );
    pasar( $ca, 'processing' );
    $en = pedido( 8002, array( 'idioma' => 'en', 'nombre' => 'Emma' ) );
    pasar( $en, 'processing' );
    $c = $GLOBALS['correos'];
    ok( 'La teva factura simplificada W2026-00001 · DryIcePack' === $c[0]['asunto'] && 'Your simplified invoice W2026-00002 · DryIcePack' === $c[1]['asunto'], 'asuntos: ' . $c[0]['asunto'] . ' / ' . $c[1]['asunto'] );
    ok( array( 'Factura-simplificada-W2026-00001.pdf' ) === array_keys( $c[0]['adj'] ) && array( 'Simplified-invoice-W2026-00002.pdf' ) === array_keys( $c[1]['adj'] ), 'nombre del adjunto en su idioma', array( array_keys( $c[0]['adj'] ), array_keys( $c[1]['adj'] ) ) );
    $pca = reset( $c[0]['adj'] ); $pen = reset( $c[1]['adj'] );
    $faltan = array();
    foreach ( array( 'DATA D\'EXPEDICIÓ', 'Gel sec en pèl·lets de 3 mm', 'Pack de 10 kg · caixa EPS inclosa', 'Enviament per missatgeria MRW', 'Total (IVA inclòs)', "81,81\u{00A0}€", 'Ronda Alfonso X El Sabio, 10, 2n 1a' ) as $t ) if ( ! en_pdf( $pca, $t ) ) $faltan[] = 'ca: ' . $t;
    foreach ( array( 'Simplified invoice', 'DATE OF ISSUE', 'Dry ice, 3 mm pellets', '10 kg pack · EPS box included', 'Total (VAT included)', '€81.81', "\u{2212}78.5 °C" ) as $t ) if ( ! en_pdf( $pen, $t ) ) $faltan[] = 'en: ' . $t;
    ok( ! $faltan, 'PDF en catalán y en inglés', $faltan );
    ok( false !== strpos( $c[0]['mensaje'], '<html lang="ca">' ) && false !== strpos( $c[0]['mensaje'], 'Gràcies per la teva comanda' ) && false !== strpos( $c[0]['mensaje'], 'Hola, Núria. T&#039;enviem la factura simplificada de la comanda 8001.' ), 'email en catalán' );
    ok( false !== strpos( $c[1]['mensaje'], '<html lang="en">' ) && false !== strpos( $c[1]['mensaje'], 'Hello Emma, here is the simplified invoice for your order 8002.' ) && false !== strpos( $c[1]['mensaje'], '€81.81' ), 'email en inglés' );
    ok( false !== strpos( $c[0]['mensaje'], 'dryicepack.es/ca/seguretat-del-gel-sec/' ) && false !== strpos( $c[1]['mensaje'], 'dryicepack.es/en/dry-ice-safety/' ), 'enlace de seguridad en su idioma' );
    ok( false !== strpos( $c[0]['mensaje'], 'Entrega: dissabte 3 d&#039;octubre' ) && false !== strpos( $c[1]['mensaje'], 'Delivery: Saturday 3 October' ), 'fecha de entrega en su idioma' );
    file_put_contents( '/salida/email-ca.html', $c[0]['mensaje'] );
    file_put_contents( '/salida/email-en.html', $c[1]['mensaje'] );
    file_put_contents( '/salida/ejemplo-ca.pdf', $pca );
    file_put_contents( '/salida/ejemplo-en.pdf', $pen );
    // Mismas claves en los tres idiomas y mismos marcadores (%s, %1$s…)
    $marcas = static function ( $t ) { preg_match_all( '/%(?:\d\$)?[sd%]/', (string) $t, $m ); sort( $m[0] ); return $m[0]; };
    $es = dip_fs_textos( 'es' );
    foreach ( array( 'ca', 'en' ) as $i ) {
      $t = dip_fs_textos( $i ); $mal = array();
      foreach ( $es as $k => $v ) if ( ! isset( $t[ $k ] ) || $marcas( $v ) !== $marcas( $t[ $k ] ) ) $mal[] = $k;
      ok( ! $mal && count( $t ) === count( $es ), "$i: mismas claves y marcadores que el castellano", $mal );
    }
    ok( false === strpos( implode( ' ', dip_fs_textos( 'ca' ) ), '’' ), 'CA: apóstrofo recto, como el resto de la tienda' );
  `,

  'Email: contenido y vista': String.raw`
    $p = pedido( 9001, array( 'sabado' => 1, 'nombre' => 'Laura' ) );
    pasar( $p, 'processing' );
    $m = correos_a( 'laura@example.com' )[0];
    $h = $m['mensaje'];
    $exige = array(
      'cabecera con la marca'       => '>DRYICEPACK</td>',
      'cabecera azul noche'         => 'background:#0B141C',
      'gracias'                     => 'Gracias por tu pedido',
      'saludo'                      => 'Hola, Laura. Te enviamos la factura simplificada de tu pedido 9001.',
      'fecha de entrega'            => 'Entrega: sábado 3 de octubre',
      'número'                      => 'W2026-00001',
      'líneas'                      => 'Suplemento de entrega en sábado',
      'base'                        => 'Base imponible',
      'IVA'                         => 'IVA 21 %',
      'total destacado'             => 'background:#1F3C55',
      'total'                       => "93,55\u{00A0}€",
      'factura completa'            => '¿Necesitas factura completa a nombre de una empresa?',
      'enlace seguro a /factura/'   => 'href="http://prueba.local/factura/?pedido=9001&amp;clave=wc_order_clave9001"',
      'texto del enlace'            => 'Pídela aquí',
      'seguridad'                   => 'href="https://dryicepack.es/seguridad-del-hielo-seco/"',
      'contacto'                    => '936 73 76 41',
      'emisor en el pie'            => 'INDUNOVA IMS S.L. · NIF B66800103',
    );
    $faltan = array();
    foreach ( $exige as $que => $t ) if ( false === strpos( $h, $t ) ) $faltan[] = $que;
    ok( ! $faltan, 'el email lleva todo (' . count( $exige ) . ' comprobaciones)', $faltan );
    ok( false === strpos( $h, '#2BB3E9' ), 'sin el cian de compra (no es un botón de compra)' );
    ok( false !== strpos( $m['alt'], 'Pídela aquí: http://prueba.local/factura/?pedido=9001&clave=wc_order_clave9001' ) && false !== strpos( $m['alt'], 'Total (IVA incluido): 93,55 €' ), 'versión en texto con el enlace y el total', $m['alt'] );
    ok( 0 === substr_count( strip_tags( preg_replace( '/<!doctype[^>]*>/i', '', $h ) ) . $m['alt'], '!' ), 'sin signos de exclamación' );
    file_put_contents( '/salida/email.html', $h );
  `,

  'Fallo del email y reenvío': String.raw`
    $GLOBALS['correo_falla'] = 'laura@example.com';
    $p = pedido( 9101 );
    pasar( $p, 'processing' );
    ok( 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'el número se guarda aunque falle el email (sin huecos)' );
    ok( 0 === strpos( (string) $p->get_meta( '_dip_fs_error' ), 'No se pudo enviar el email' ), 'queda marcado el error en el pedido' );
    $aviso = correos_a( 'info@dryicepack.es' );
    ok( 1 === count( $aviso ) && 'Dryicepack: revisar la factura simplificada del pedido 9101' === $aviso[0]['asunto'] && false !== strpos( $aviso[0]['mensaje'], '«Reenviar factura simplificada»' ), 'aviso por email a info@ con la alternativa manual', $aviso );
    ok( (bool) preg_grep( '/No se ha podido enviar por email la factura simplificada W2026-00001 a laura@example.com\. Aviso enviado a info@dryicepack.es\./', $p->notas ), 'nota en el pedido', $p->notas );
    ob_start(); dip_fs_columna( 'dip_fs', $p ); $col = ob_get_clean();
    ok( false !== strpos( $col, 'W2026-00001' ) && false !== strpos( $col, 'Sin enviar' ), 'lista de pedidos: número y "Sin enviar"', $col );
    $GLOBALS['correo_falla'] = false;
    do_action( 'woocommerce_order_action_dip_fs_reenviar', $p );
    ok( 1 === count( correos_a( 'laura@example.com' ) ) && '' === $p->get_meta( '_dip_fs_error' ) && (bool) preg_grep( '/^Reenviada la factura simplificada W2026-00001/', $p->notas ), 'Acciones del pedido → Reenviar: sale y se quita el error' );
    ok( '1' === contador(), 'reenviar no gasta otro número' );
    ob_start(); do_action( 'woocommerce_admin_order_data_after_billing_address', $p ); $caja = ob_get_clean();
    ok( false !== strpos( $caja, 'W2026-00001' ) && false !== strpos( $caja, 'Ver el PDF' ) && false !== strpos( $caja, 'admin-post.php?action=dip_fs_pdf&amp;pedido=9101' ) && false !== strpos( $caja, 'Enviada a laura@example.com' ), 'pedido en el escritorio: número, PDF y envío', $caja );
    ob_start(); dip_fs_columna( 'dip_fs', $p ); $col = ob_get_clean();
    ok( false !== strpos( $col, 'W2026-00001' ) && false === strpos( $col, 'Sin enviar' ), 'lista de pedidos: número' );
    $cols = apply_filters( 'manage_woocommerce_page_wc-orders_columns', array( 'order_number' => 'Pedido', 'order_total' => 'Total', 'wc_actions' => 'Acciones' ) );
    ok( array( 'order_number', 'order_total', 'dip_fs', 'wc_actions' ) === array_keys( $cols ), 'columna "Factura" detrás del total', array_keys( $cols ) );
    // Todo falla, también el aviso
    $GLOBALS['correo_falla'] = true;
    $q = pedido( 9102 );
    pasar( $q, 'processing' );
    ok( (bool) preg_grep( '/No se ha podido enviar el aviso por email\.$/', $q->notas ), 'si tampoco sale el aviso, queda en la nota del pedido', $q->notas );
  `,

  'Devoluciones y cancelaciones': String.raw`
    $p = pedido( 9201 );
    pasar( $p, 'processing' );
    $GLOBALS['correos'] = array();
    $GLOBALS['pedidos'][9901] = new WC_Order( 9901, array( 'total' => -17.32, 'importe' => 17.32, 'items' => array() ) );
    $p->d['devuelto'] = 17.32;
    do_action( 'woocommerce_order_refunded', 9201, 9901 );
    $a = correos_a( 'info@dryicepack.es' );
    ok( 1 === count( $a ) && 'Rectificativa pendiente: W2026-00001 (pedido 9201)' === $a[0]['asunto'], 'reembolso parcial: aviso a info@', array_column( $a, 'asunto' ) );
    ok( false !== stripos( $a[0]['mensaje'], 'hay que emitir una factura rectificativa de la W2026-00001 desde la aplicación de la gestoría' ) && false !== strpos( $a[0]['mensaje'], '17,32 €' ), 'el aviso dice qué hay que hacer y el importe', $a[0]['mensaje'] );
    ok( (bool) preg_grep( '/^Reembolso parcial en un pedido con factura simplificada \(17,32 €\)\. Hay que emitir una factura rectificativa de la W2026-00001/u', $p->notas ), 'nota en el pedido', $p->notas );
    do_action( 'woocommerce_order_refunded', 9201, 9901 );
    ok( 1 === count( correos_a( 'info@dryicepack.es' ) ), 'el mismo reembolso no avisa dos veces' );
    // Reembolso del resto: WooCommerce cambia el estado y después avisa del reembolso → un solo aviso
    $GLOBALS['pedidos'][9902] = new WC_Order( 9902, array( 'total' => -64.49, 'importe' => 64.49, 'items' => array() ) );
    $p->d['devuelto'] = 81.81;
    do_action( 'woocommerce_order_status_refunded', 9201, $p );
    do_action( 'woocommerce_order_refunded', 9201, 9902 );
    $a = correos_a( 'info@dryicepack.es' );
    ok( 2 === count( $a ) && false !== strpos( $a[1]['mensaje'], 'reembolsado (81,81 €)' ), 'reembolso total: un solo aviso más', array_column( $a, 'mensaje' ) );
    do_action( 'woocommerce_order_status_cancelled', 9201, $p );
    ok( 3 === count( correos_a( 'info@dryicepack.es' ) ) && false !== strpos( correos_a( 'info@dryicepack.es' )[2]['mensaje'], 'cancelado' ), 'cancelación: aviso' );
    ok( '1' === contador() && 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'no se emite nada automáticamente (ni rectificativa ni número nuevo)' );
    ob_start(); dip_fs_columna( 'dip_fs', $p ); $col = ob_get_clean();
    ok( false !== strpos( $col, 'Rectificar' ), 'lista de pedidos: "Rectificar"' );
    $sin = pedido( 9202, array( 'empresa' => 1, 'nif' => 'B66800103' ) );
    do_action( 'woocommerce_order_status_cancelled', 9202, $sin );
    ok( 3 === count( correos_a( 'info@dryicepack.es' ) ) && ! $sin->notas, 'un pedido sin factura simplificada no avisa de nada' );
    // Se edita el pedido en el escritorio y cambia el total: la factura ya no lo refleja
    $t = pedido( 9203 );
    pasar( $t, 'processing' );
    $antes = count( correos_a( 'info@dryicepack.es' ) );
    do_action( 'woocommerce_order_after_calculate_totals', true, $t );
    ok( $antes === count( correos_a( 'info@dryicepack.es' ) ), 'recalcular sin cambiar el total: sin aviso' );
    $t->d['total'] = 93.55;
    do_action( 'woocommerce_order_after_calculate_totals', true, $t );
    do_action( 'woocommerce_order_after_calculate_totals', true, $t );
    $a = correos_a( 'info@dryicepack.es' );
    ok( $antes + 1 === count( $a ) && false !== strpos( end( $a )['mensaje'], 'ha cambiado después de expedir la factura simplificada: de 81,81 € a 93,55 €' ), 'el total cambia (81,81 → 93,55 €): un aviso de rectificativa', array_column( $a, 'mensaje' ) );
  `,

  'Libro de facturas (CSV para la gestoría)': String.raw`
    foreach ( array( 9401, 9402, 9403 ) as $id ) { $p = pedido( $id ); pasar( $p, 'processing' ); }
    $q = pedido( 9404, array( 'nombre' => '=HIPERVINCULO("x")' ) ); pasar( $q, 'processing' );
    ok( 4 === count( $GLOBALS['wpdb']->get_col( "SELECT option_name FROM wp_options WHERE option_name LIKE 'dip_fs_libro_%'" ) ), 'cada factura deja su fila en el libro (wp_options)' );
    // Simulaciones: una fila perdida (hueco), una rectificativa pendiente y un pedido borrado
    $GLOBALS['wpdb']->pdo->exec( "DELETE FROM wp_options WHERE option_name = 'dip_fs_libro_W2026-00002'" );
    $GLOBALS['pedidos'][9401]->update_meta_data( '_dip_fs_rectificar', array( 'cancelado' => 1 ) );
    unset( $GLOBALS['pedidos'][9403] );
    $l = dip_fs_libro( '2026-10' );
    ok( 'Serie' === $l[0][0] && 'Observaciones' === $l[0][11] && 12 === count( $l[0] ), 'cabecera del CSV (12 columnas)', $l[0] );
    ok( array( 'W2026', 'W2026-00001', '02/10/2026', '', '9401', 'Laura García Pons', '67,61', '21', '14,20', '81,81', 'Tarjeta', 'Rectificativa pendiente' ) === $l[1], 'fila: serie, número, fechas, pedido, cliente, base, tipo, cuota, total, pago y observaciones', $l[1] );
    ok( 'W2026-00002' === $l[2][1] && false !== strpos( $l[2][11], 'sin factura en el libro' ), 'señala el número que falta (W2026-00002)', $l[2] );
    ok( 'W2026-00003' === $l[3][1] && false !== strpos( $l[3][11], 'Pedido borrado' ), 'un pedido borrado sigue en el libro', $l[3] );
    ok( "'=HIPERVINCULO(\"x\")" === $l[4][5], 'un nombre que empieza por "=" no se ejecuta como fórmula en Excel', $l[4][5] );
    ok( array( 'Total', '3 facturas', '', '', '', '', '202,83', '', '42,60', '245,43', '', '' ) === $l[5], 'fila de totales: 3 facturas, 245,43 €', $l[5] );
    ok( 1 === count( dip_fs_libro( '2026-09' ) ), 'otro mes: solo la cabecera' );
    // Mes siguiente: el primer número del mes enlaza con el último del anterior (sin falsos huecos)
    ahora( '2026-11-02 10:00:00' );
    $n = pedido( 9405, array( 'creado' => strtotime( '2026-11-02 09:00 Europe/Madrid' ), 'entrega' => '2026-11-03' ) ); pasar( $n, 'processing' );
    $l = dip_fs_libro( '2026-11' );
    ok( 3 === count( $l ) && 'W2026-00005' === $l[1][1], 'noviembre: W2026-00005 sin huecos delante', $l );
  `,

  'VeriFactu: aviso el 1/12/2026 y parada el 1/1/2027': String.raw`
    ahora( '2026-11-30 23:00:00' );
    dip_fs_vigilar_verifactu();
    ok( ! $GLOBALS['correos'], '30/11/2026: todavía sin aviso' );
    ahora( '2026-12-01 09:00:00' );
    dip_fs_vigilar_verifactu();
    $a = correos_a( 'info@dryicepack.es' );
    ok( 1 === count( $a ) && 'Dryicepack: la factura simplificada automática se para el 1 de enero de 2027' === $a[0]['asunto'], '1/12/2026: email de aviso', array_column( $a, 'asunto' ) );
    ok( false !== strpos( $a[0]['mensaje'], 'Real Decreto 1007/2023' ) && false !== strpos( $a[0]['mensaje'], 'aplicación de la gestoría' ), 'el aviso explica VeriFactu y la salida (aplicación de la gestoría o adaptar el módulo)' );
    $GLOBALS['transitorios'] = array();
    dip_fs_vigilar_verifactu();
    ok( 1 === count( correos_a( 'info@dryicepack.es' ) ), 'no se repite' );
    $GLOBALS['pantalla'] = (object) array( 'id' => 'dashboard' );
    ob_start(); do_action( 'admin_notices' ); $n = ob_get_clean();
    ok( false !== strpos( $n, 'notice-warning' ) && false !== strpos( $n, 'se para el 1/1/2027 (VeriFactu)' ), 'aviso en el escritorio desde el 1/12', $n );
    $GLOBALS['pantalla'] = (object) array( 'id' => 'edit-post' );
    ob_start(); do_action( 'admin_notices' ); $n = ob_get_clean();
    ok( '' === $n, 'no molesta en pantallas que no son de pedidos ni de Dryicepack' );
    ahora( '2026-12-15 10:00:00' );
    $p = pedido( 9301, array( 'creado' => strtotime( '2026-12-15 09:00 Europe/Madrid' ), 'entrega' => '2026-12-16' ) );
    pasar( $p, 'processing' );
    ok( 'W2026-00001' === $p->get_meta( '_dip_fs_numero' ), 'en diciembre sigue emitiendo' );
    // Efectivo con la recogida ya en 2027: no se le promete la factura simplificada y recibe «¿Necesitas factura?»
    $GLOBALS['wc_correos'] = array();
    $r = pedido( 9303, array( 'pago' => 'cod', 'metodo' => 'recogida', 'envio' => 0, 'entrega' => '2027-01-02', 'creado' => strtotime( '2026-12-15 09:30 Europe/Madrid' ) ) );
    pasar( $r, 'processing' );
    ok( '' === dip_fs_texto_gracias( $r, 'es' ) && 1 === count( $GLOBALS['wc_correos'] ), 'efectivo con recogida el 02/01/2027: sin promesa de factura simplificada y con «¿Necesitas factura?»', array_column( $GLOBALS['wc_correos'], 1 ) );

    ahora( '2027-01-01 00:00:01' );
    $GLOBALS['transitorios'] = array();
    dip_fs_vigilar_verifactu();
    $a = correos_a( 'info@dryicepack.es' );
    ok( 2 === count( $a ) && 'Dryicepack: la factura simplificada automática está parada (VeriFactu)' === $a[1]['asunto'], '1/1/2027: email de parada', array_column( $a, 'asunto' ) );
    $GLOBALS['wc_correos'] = array();
    $q = pedido( 9302, array( 'creado' => strtotime( '2027-01-01 00:00:00 Europe/Madrid' ), 'entrega' => '2027-01-05' ) );
    pasar( $q, 'processing' );
    ok( '' === $q->get_meta( '_dip_fs_numero' ) && '1' === contador(), '1/1/2027: ya no emite' );
    ok( (bool) preg_grep( '/^Sin factura simplificada automática: desde el 1\/1\/2027/u', $q->notas ), 'lo anota en el pedido', $q->notas );
    ok( 1 === count( $GLOBALS['wc_correos'] ) && false !== strpos( $GLOBALS['wc_correos'][0][1], 'necesitas factura' ), 'y el cliente recibe «¿Necesitas factura?» como antes', array_column( $GLOBALS['wc_correos'], 1 ) );
    $acciones = apply_filters( 'woocommerce_order_actions', array(), $q );
    ok( ! isset( $acciones['dip_fs_emitir'] ), 'tampoco se ofrece emitirla a mano' );
    do_action( 'woocommerce_order_action_dip_fs_emitir', $q );
    ok( '' === $q->get_meta( '_dip_fs_numero' ), 'ni aunque se fuerce la acción' );
    $antes = count( correos_a( 'laura@example.com' ) );
    do_action( 'woocommerce_order_action_dip_fs_reenviar', $p );
    ok( $antes + 1 === count( correos_a( 'laura@example.com' ) ), 'las facturas ya emitidas se pueden reenviar en 2027' );
    $GLOBALS['pantalla'] = (object) array( 'id' => 'woocommerce_page_wc-orders' );
    ob_start(); do_action( 'admin_notices' ); $n = ob_get_clean();
    ok( false !== strpos( $n, 'notice-error' ) && false !== strpos( $n, 'parada desde el 1/1/2027' ), 'aviso de parada en la pantalla de pedidos', $n );
  `,
};

let fallos = 0, total = 0;
function contar(salida) {
  for (const linea of salida.trim().split('\n').filter(Boolean)) {
    console.log('  ' + linea);
    if (/^(OK|FALLO)/.test(linea)) total++;
    if (/^FALLO/.test(linea) || !/^(OK|FALLO)/.test(linea)) fallos++;
  }
}
async function ejecutar(antes, codigo) {
  try {
    return (await php.run({ code: `<?php ${antes}\nrequire '/b/arranque.php';\n${codigo}` })).text;
  } catch (e) {
    return 'FALLO error fatal: ' + String(e.message).split('\n').filter((l) => /Fatal|Uncaught|line|linea|\.php/.test(l)).slice(0, 4).join(' | ');
  }
}
for (const [nombre, esc] of Object.entries(escenarios)) {
  console.log('\n— ' + nombre);
  const { antes = '', codigo } = typeof esc === 'string' ? { codigo: esc } : esc;
  contar(await ejecutar(antes, codigo));
}

/* ---------- Ejemplos para revisar a ojo: PDF y email ---------- */
const DIR = '.tmp/correcciones/factura';
mkdirSync(DIR, { recursive: true });
for (const f of ['ejemplo.pdf', 'ejemplo-ca.pdf', 'ejemplo-en.pdf', 'ejemplo-operacion.pdf', 'email.html', 'email-ca.html', 'email-en.html']) {
  if (php.fileExists('/salida/' + f)) writeFileSync(`${DIR}/${f}`, php.readFileAsBuffer('/salida/' + f));
}
console.log(`\nEjemplos en ${DIR}/ (ejemplo.pdf, email.html y sus versiones en catalán e inglés)`);

/* ---------- Capturas del email a 390 y 1440 px ---------- */
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
if (existsSync(CHROME) && existsSync(`${DIR}/email.html`)) {
  const { default: puppeteer } = await import('puppeteer-core');
  const CAP = '.tmp/correcciones/factura-simplificada';
  mkdirSync(CAP, { recursive: true });
  const nav = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await nav.newPage();
  for (const [archivo, sufijo] of [['email.html', ''], ['email-ca.html', '-ca'], ['email-en.html', '-en']]) {
    for (const ancho of [390, 1440]) {
      if (sufijo && ancho === 1440) continue;
      await p.setViewport({ width: ancho, height: 900, deviceScaleFactor: 1 });
      await p.goto('file:///' + process.cwd().replace(/\\/g, '/') + `/${DIR}/${archivo}`, { waitUntil: 'load' });
      const desborda = await p.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
      await p.screenshot({ path: `${CAP}/email${sufijo}-${ancho}.png`, fullPage: true });
      contar(`${desborda ? 'FALLO' : 'OK   '} email${sufijo} a ${ancho} px sin scroll horizontal`);
    }
  }
  await nav.close();
  console.log(`Capturas en ${CAP}/`);
}

console.log(`\n${total - fallos} de ${total} comprobaciones correctas${fallos ? ` · ${fallos} con fallo` : ''}`);
process.exit(fallos ? 1 : 0);
