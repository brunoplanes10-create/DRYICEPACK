// Pruebas de los festivos automáticos de MRW (plugin dryicepack-tienda) con PHP 8.3 en WebAssembly, sin WordPress.
// Carga negocio.php, festivos-mrw.php, textos.php, entrega.php y ajustes.php con funciones mínimas de WordPress simuladas.
// Páginas reales de MRW guardadas en herramientas/fixtures/ (9/10/2026: festivo de la Comunidad Valenciana; 8/12/2026: nacional).
// Cada escenario es una ejecución de PHP nueva (sin estado compartido). No hace ninguna petición a internet.
// Al final, el JavaScript del tema (movimiento.js, en Node) con la configuración que le manda tema/dryicepack/inc/datos.php:
// su "llega el …" tiene que coincidir con la primera entrega del checkout.
// Uso: node herramientas/probar-mrw.mjs
import { loadNodeRuntime } from '@php-wasm/node';
import { PHP } from '@php-wasm/universal';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/p/inc');
php.mkdir('/p/datos');
php.mkdir('/f');
php.mkdir('/b');
php.mkdir('/t');
// seguimiento-entregas.php (revisión diaria de pedidos reservados) e informes.php (facturación mensual) solo los cargan sus escenarios
for (const f of ['ajustes.php', 'negocio.php', 'festivos-mrw.php', 'textos.php', 'entrega.php', 'seguimiento-entregas.php', 'informes.php']) {
  php.writeFile('/p/inc/' + f, readFileSync('plugin/dryicepack-tienda/inc/' + f));
}
php.writeFile('/p/datos/mrw-oficinas.json', readFileSync('plugin/dryicepack-tienda/datos/mrw-oficinas.json'));
php.writeFile('/t/datos.php', readFileSync('tema/dryicepack/inc/datos.php'));
const movimientoJs = readFileSync('tema/dryicepack/assets/js/movimiento.js', 'utf8');
php.writeFile('/f/d0910.html', readFileSync('herramientas/fixtures/mrw-2026-10-09.html'));
php.writeFile('/f/d0812.html', readFileSync('herramientas/fixtures/mrw-2026-12-08.html'));

// WordPress mínimo: opciones y transitorios en memoria, correos y peticiones HTTP simuladas
php.writeFile('/b/arranque.php', String.raw`<?php
error_reporting( E_ALL );
set_error_handler( function ( $n, $s, $f, $l ) { echo 'FALLO aviso de PHP: ' . $s . ' en ' . basename( $f ) . ':' . $l . "\n"; return true; } );
define( 'ABSPATH', '/' );
define( 'DIP_TIENDA_DIR', '/p/' );
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'DIP_MRW_PAUSA_MS', 0 );
$GLOBALS['opciones'] = array();
$GLOBALS['transitorios'] = array();
$GLOBALS['correos'] = array();
$GLOBALS['http'] = null;
// Los ganchos se guardan para poder dispararlos en las pruebas del checkout (disparar())
$GLOBALS['ganchos'] = array();
function add_action( $h, $cb = null, ...$r ) { $GLOBALS['ganchos'][ $h ][] = $cb; }
function add_filter( $h, $cb = null, ...$r ) { $GLOBALS['ganchos'][ $h ][] = $cb; }
function disparar( $h, ...$args ) { $r = $args[0] ?? null; foreach ( $GLOBALS['ganchos'][ $h ] ?? array() as $cb ) $r = $cb( ...$args ); return $r; }
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['opciones'] ) ? $GLOBALS['opciones'][ $k ] : $d; }
function update_option( $k, $v, $a = null ) { $GLOBALS['opciones'][ $k ] = $v; $GLOBALS['autoload'][ $k ] = $a; return true; }
function get_transient( $k ) { return $GLOBALS['transitorios'][ $k ] ?? false; }
function set_transient( $k, $v, $t = 0 ) { $GLOBALS['transitorios'][ $k ] = $v; return true; }
function delete_transient( $k ) { unset( $GLOBALS['transitorios'][ $k ] ); return true; }
function wp_timezone() { return new DateTimeZone( 'Europe/Madrid' ); }
function wp_parse_args( $a, $d = array() ) { return array_merge( (array) $d, (array) $a ); }
function wp_parse_url( $u, $c = -1 ) { return parse_url( $u, $c ); }
function home_url( $p = '' ) { return 'http://prueba.local' . $p; }
function admin_url( $p = '' ) { return 'http://prueba.local/wp-admin/' . $p; }
function wp_mail( $to, $asunto, $texto ) { if ( ! empty( $GLOBALS['correo_falla'] ) ) return false; $GLOBALS['correos'][] = array( $to, $asunto, $texto ); return true; }
// WP-Cron como lo guarda WordPress: [hora => [tarea => [md5(args) => ['schedule', 'args']]]]
$GLOBALS['cron'] = array();
function _get_cron_array() { return $GLOBALS['cron']; }
function wp_next_scheduled( $h, $args = array() ) {
	foreach ( $GLOBALS['cron'] as $hora => $tareas ) { if ( isset( $tareas[ $h ][ md5( serialize( $args ) ) ] ) ) return $hora; }
	return false;
}
function wp_schedule_event( $hora, $cada, $h, $args = array() ) {
	$GLOBALS['cron'][ $hora ][ $h ][ md5( serialize( $args ) ) ] = array( 'schedule' => $cada, 'args' => $args );
	ksort( $GLOBALS['cron'] );
	return true;
}
function wp_unschedule_event( $hora, $h, $args = array() ) {
	unset( $GLOBALS['cron'][ $hora ][ $h ][ md5( serialize( $args ) ) ] );
	if ( empty( $GLOBALS['cron'][ $hora ][ $h ] ) ) unset( $GLOBALS['cron'][ $hora ][ $h ] );
	if ( empty( $GLOBALS['cron'][ $hora ] ) ) unset( $GLOBALS['cron'][ $hora ] );
	return true;
}
/** Horas a las que está programada una tarea. */
function horas_cron( $h ) { $l = array(); foreach ( $GLOBALS['cron'] as $hora => $tareas ) { foreach ( (array) ( $tareas[ $h ] ?? array() ) as $e ) $l[] = $hora; } return $l; }
function add_query_arg( $args, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args ); }
function wp_remote_get( $url, $args = array() ) { return call_user_func( $GLOBALS['http'], $url ); }
function wp_remote_retrieve_response_code( $r ) { return $r['response']['code'] ?? 0; }
function wp_remote_retrieve_body( $r ) { return $r['body'] ?? ''; }
class WP_Error {
	public $code; public $message;
	function __construct( $c = '', $m = '' ) { $this->code = $c; $this->message = $m; }
	function get_error_message() { return $this->message; }
	function get_error_code() { return $this->code; }
}
function is_wp_error( $x ) { return $x instanceof WP_Error; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return esc_html( $s ); }
function wp_nonce_url( $u, $a ) { return $u . '&_wpnonce=prueba'; }
function wp_date( $f, $t ) { return ( new DateTimeImmutable( '@' . $t ) )->setTimezone( wp_timezone() )->format( $f ); }
function current_user_can( $c ) { return true; }
function wp_doing_ajax() { return false; }
function remove_accents( $s ) {
	static $m = null;
	if ( null === $m ) {
		$de = preg_split( '//u', 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöùúûüýÿ', -1, PREG_SPLIT_NO_EMPTY );
		$a  = 'AAAAAACEEEEIIIINOOOOOUUUUYaaaaaaceeeeiiiinooooouuuuyy';
		$m  = array();
		foreach ( $de as $i => $c ) $m[ $c ] = $a[ $i ];
	}
	return strtr( (string) $s, $m );
}
require '/p/inc/ajustes.php';
require '/p/inc/negocio.php';
require '/p/inc/festivos-mrw.php';
require '/p/inc/textos.php';
require '/p/inc/entrega.php';

function ok( $condicion, $texto, $detalle = null ) {
	echo ( $condicion ? 'OK   ' : 'FALLO' ) . ' ' . $texto . ( ! $condicion && null !== $detalle ? '  → ' . json_encode( $detalle, JSON_UNESCAPED_UNICODE ) : '' ) . "\n";
}
function fechas( $lista ) { return array_column( $lista, 'fecha' ); }
function salidas( $lista ) { return array_column( $lista, 'sale' ); }
function poner_dias( array $dias ) { $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => $dias, 'ok' => time() ); }
function cuenta_provincias( array $cerradas ) { $c = array(); foreach ( $cerradas as $x ) $c[ $x[0] ] = ( $c[ $x[0] ] ?? 0 ) + 1; ksort( $c ); return $c; }
/** Página de MRW de mentira: [[estado, nombre, provincia], …] y el día marcado con is-sel. */
function pagina_mrw( $ymd, array $oficinas ) {
	list( $y, $m, $d ) = explode( '-', $ymd );
	$h = '<table id="festividadesTb" class="festividades"><tr><td headers="cap1" class="is-sel"><a href=\'MRW_festividades.asp?dia=' . $d . '&mes=' . $m . '&any=' . $y . '\'>' . $d . '</a></td></tr></table><div class="fest-grid">';
	foreach ( $oficinas as $o ) {
		$h .= '<div class="loc loc-festividades is-' . $o[0] . '"><dl class="loc-info"><dt class="stone-semi loc-title"><a href="x" title="MRW">' . $o[1] . '</a></dt>'
			. '<dd class="entry loc-description"><p><strong>Provincia</strong>: ' . $o[2] . "\r\n<br /><em>Esta oficina permanecerá cerrada.</em></p></dd></dl></div>\n";
	}
	return $h . '</div>';
}
/** Respuesta HTTP simulada para dip_mrw_actualizar(): $fn( 'Y-m-d', n.º de día desde hoy ) devuelve las oficinas cerradas. */
function http_mrw( callable $fn ) {
	$hoy = new DateTimeImmutable( 'today', wp_timezone() );
	return function ( $url ) use ( $fn, $hoy ) {
		parse_str( (string) parse_url( $url, PHP_URL_QUERY ), $q );
		$ymd = $q['any'] . '-' . $q['mes'] . '-' . $q['dia'];
		$n   = (int) $hoy->diff( new DateTimeImmutable( $ymd, wp_timezone() ) )->format( '%r%a' );
		return array( 'response' => array( 'code' => 200 ), 'body' => pagina_mrw( $ymd, $fn( $ymd, $n ) ) );
	};
}
// Lunes 5 de octubre de 2026 a las 10:00 (antes del corte). El lunes 12 es festivo nacional (lista manual por defecto).
$AHORA = new DateTimeImmutable( '2026-10-05 10:00', wp_timezone() );
`);

const escenarios = {
  'Nombres de población': String.raw`
    $casa = fn( $a, $b ) => dip_mrw_mismo_sitio( dip_mrw_nombres( $a ), dip_mrw_nombres( $b ) );
    ok( $casa( "L'Hospitalet de Llobregat", 'Hospitalet De Llobregat' ), "L'Hospitalet de Llobregat casa con Hospitalet De Llobregat", dip_mrw_nombres( "L'Hospitalet de Llobregat" ) );
    ok( $casa( 'L’Hospitalet de Llobregat', 'Hospitalet De Llobregat' ), 'con apóstrofo tipográfico también' );
    ok( $casa( 'Granollers', 'Les Franqueses Del V.Alles (Granollers)' ), 'Granollers casa con Les Franqueses Del V.Alles (Granollers)' );
    ok( $casa( 'Elx', 'Elche/Elx 03201' ), 'Elx casa con Elche/Elx 03201' );
    ok( $casa( 'Elche', 'Elche/Elx 03201' ), 'Elche casa con Elche/Elx 03201' );
    ok( ! $casa( 'Barcelona', 'Badalona' ), 'Barcelona NO casa con Badalona' );
    ok( $casa( 'Sant Boi de Llobregat', 'Sant Boi De Llobregat08013' ), 'Sant Boi de Llobregat casa con Sant Boi De Llobregat08013' );
    ok( $casa( 'Castelló de la Plana', 'Castellon/Castello 12006' ), 'Castelló de la Plana casa con Castellon/Castello 12006' );
    ok( $casa( 'Mataró', 'Mataro' ), 'Mataró casa con Mataro' );
    ok( $casa( "Móra d'Ebre", "Mora D'ebre" ), "Móra d'Ebre casa con Mora D'ebre" );
    ok( $casa( 'Sant Cugat', 'Sant Cugat Del Valles' ), 'Sant Cugat casa con Sant Cugat Del Valles' );
    ok( ! $casa( 'Santa Coloma de Cervelló', 'Santa Coloma De Gramanet' ), 'Santa Coloma de Cervelló NO casa con Santa Coloma De Gramanet' );
    ok( ! $casa( 'Bergara', 'Berga' ), 'Bergara NO casa con Berga' );
    ok( array() === dip_mrw_nombres( '84012' ), 'una oficina que solo es un número no da nombres' );
    ok( dip_mrw_es_interna( 'Logistica (Barcelona)' ) && ! dip_mrw_es_interna( 'Barcelona 08020' ), 'Logistica (Barcelona) es interna; Barcelona 08020 no' );
    ok( 'CORUNA' === dip_mrw_provincia_por_cp( '15001' ) && 'BARCELONA' === dip_mrw_provincia_por_cp( '08304' ) && '' === dip_mrw_provincia_por_cp( '07001' ), 'provincia de MRW por código postal (Baleares fuera)' );
  `,

  'Análisis de las páginas de MRW': String.raw`
    $r = dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-09' );
    ok( ! is_wp_error( $r ), '9/10: la página se entiende', is_wp_error( $r ) ? $r->get_error_message() : null );
    $c = cuenta_provincias( $r['cerradas'] );
    ok( array( 'ALICANTE' => 11, 'CASTELLON' => 4, 'MADRID' => 1, 'VALENCIA' => 23 ) === $c, '9/10: cerradas por provincia ALICANTE 11, CASTELLON 4, MADRID 1, VALENCIA 23', $c );
    ok( 39 === count( $r['cerradas'] ) && 44 === $r['oficinas'], '9/10: 39 cerradas de 44 oficinas con aviso (5 abiertas no cuentan)', array( count( $r['cerradas'] ), $r['oficinas'] ) );
    $p = dip_mrw_provincias_cerradas( $r['cerradas'] );
    ok( array( 'ALICANTE', 'CASTELLON', 'VALENCIA' ) === $p, '9/10: provincias enteras VALENCIA, ALICANTE y CASTELLON (Madrid no)', $p );
    ok( in_array( array( 'MADRID', 'Leganes' ), $r['cerradas'], true ), '9/10: Leganés cerrada (festivo local)' );
    ok( ! in_array( 'Sant Feliu De Llobregat', array_column( $r['cerradas'], 1 ), true ), '9/10: Sant Feliu de Llobregat sale "abierta" y no cuenta' );
    $mal = dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-10' );
    ok( is_wp_error( $mal ) && 'dip_mrw_dia' === $mal->get_error_code(), 'si MRW devuelve otro día que el pedido, es un error', is_wp_error( $mal ) ? $mal->get_error_message() : $mal );

    $r = dip_mrw_analizar( file_get_contents( '/f/d0812.html' ), '2026-12-08' );
    ok( ! is_wp_error( $r ) && 431 === count( $r['cerradas'] ), '8/12: 431 oficinas cerradas', is_wp_error( $r ) ? $r->get_error_message() : count( $r['cerradas'] ) );
    $c = cuenta_provincias( $r['cerradas'] );
    ok( 32 === ( $c['BARCELONA'] ?? 0 ) && 63 === ( $c['MADRID'] ?? 0 ) && 6 === ( $c['CORUNA'] ?? 0 ), '8/12: Barcelona 32, Madrid 63, Coruña 6 (CORUÑA → CORUNA)', array_intersect_key( $c, array_flip( array( 'BARCELONA', 'MADRID', 'CORUNA' ) ) ) );
    $peninsula = array();
    foreach ( range( 1, 50 ) as $n ) { $prov = dip_mrw_provincia_por_cp( sprintf( '%02d001', $n ) ); if ( $prov ) $peninsula[] = $prov; }
    $enteras = dip_mrw_provincias_cerradas( $r['cerradas'] );
    ok( ! array_diff( $peninsula, $enteras ), '8/12: las ' . count( $peninsula ) . ' provincias peninsulares, cerradas enteras', array_values( array_diff( $peninsula, $enteras ) ) );

    $e = dip_mrw_analizar( '<html><body>Mantenimiento</body></html>', '2026-10-09' );
    ok( is_wp_error( $e ) && 'dip_mrw_formato' === $e->get_error_code(), 'página sin calendario: error de formato' );
    $e = dip_mrw_analizar( '<table id="festividadesTb"></table><div class="loc loc-festividades is-cerrada"><p>otra cosa</p></div><div class="loc loc-festividades is-cerrada"><p>nada</p></div>' );
    ok( is_wp_error( $e ), 'bloques de oficina que no se entienden: error de formato' );
  `,

  'dip_mrw_cierra': String.raw`
    $d0910 = dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-09' )['cerradas'];
    $d0812 = dip_mrw_analizar( file_get_contents( '/f/d0812.html' ), '2026-12-08' )['cerradas'];
    poner_dias( array(
      '2026-10-09' => $d0910,
      '2026-10-14' => array(),
      '2026-10-15' => array( array( 'BARCELONA', 'Logistica (Barcelona)' ) ),
      '2026-12-08' => $d0812,
    ) );
    ok( dip_mrw_cierra( '2026-10-09', 'VALENCIA', 'Xàtiva' ), 'provincia entera: Xàtiva (Valencia) cerrada el 9/10' );
    ok( dip_mrw_cierra( '2026-10-09', 'VALENCIA', 'Un pueblo sin oficina' ), 'provincia entera: cualquier población de Valencia' );
    ok( dip_mrw_cierra( '2026-10-09', 'ALICANTE', '' ), 'provincia entera: sin población también' );
    ok( dip_mrw_cierra( '2026-10-09', 'MADRID', 'Leganés' ), 'población concreta: Leganés cerrada el 9/10' );
    ok( ! dip_mrw_cierra( '2026-10-09', 'MADRID', 'Madrid' ) && ! dip_mrw_cierra( '2026-10-09', 'MADRID', 'Getafe' ), 'población concreta: Madrid y Getafe abiertas el 9/10' );
    ok( ! dip_mrw_cierra( '2026-10-09', 'MADRID', '' ), 'Madrid sin población: no se bloquea por un festivo local' );
    ok( ! dip_mrw_cierra( '2026-10-09', 'BARCELONA', 'Sant Feliu de Llobregat' ), 'oficina "abierta": no bloquea' );
    ok( ! dip_mrw_cierra( '2026-10-09', 'BARCELONA', 'Mataró' ) && ! dip_mrw_cierra_salida( '2026-10-09' ), 'el 9/10 sale desde Mataró' );
    ok( ! dip_mrw_cierra( '2026-10-08', 'VALENCIA', 'Valencia' ), 'día no consultado: no bloquea' );
    ok( ! dip_mrw_cierra( '2026-10-14', 'VALENCIA', 'Valencia' ), 'día consultado sin cierres: no bloquea' );
    ok( ! dip_mrw_cierra( '2026-10-15', 'BARCELONA', 'Barcelona' ), 'una oficina interna (Logistica (Barcelona)) no cierra Barcelona' );
    ok( dip_mrw_cierra_salida( '2026-12-08' ) && dip_mrw_cierra( '2026-12-08', 'CORUÑA', 'A Coruña' ), '8/12: sin salida desde Mataró y A Coruña cerrada (provincia con Ñ)' );
  `,

  'Fechas de envío sin datos de MRW': String.raw`
    $base = fechas( dip_fechas_disponibles( 'envio', 8, $AHORA ) );
    ok( array( '2026-10-06', '2026-10-07', '2026-10-08', '2026-10-09', '2026-10-10', '2026-10-14', '2026-10-15', '2026-10-16' ) === $base, 'sin datos de MRW: martes 6 a sábado 10 y, tras el festivo del 12, miércoles 14 a viernes 16 (solo lista manual)', $base );
    $leg = fechas( dip_fechas_disponibles( 'envio', 8, $AHORA, array( 'cp' => '28911', 'poblacion' => 'Leganés' ) ) );
    ok( $base === $leg, 'sin datos de MRW, con destino: las mismas fechas (nunca se bloquea por falta de datos)', $leg );
  `,

  'Fechas de envío: MRW Mataró cierra el martes': String.raw`
    poner_dias( array(
      '2026-10-06' => array( array( 'BARCELONA', 'Mataro' ) ),           // martes: cierra MRW Mataró
      '2026-10-07' => array( array( 'MADRID', 'Leganes' ) ),             // miércoles: festivo local en Leganés
      '2026-10-09' => dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-09' )['cerradas'], // viernes: Comunidad Valenciana
    ) );
    $sin_destino = dip_fechas_disponibles( 'envio', 8, $AHORA );
    ok( ! in_array( '2026-10-06', salidas( $sin_destino ), true ), 'MRW Mataró cierra el martes 6: ese día no sale nada', salidas( $sin_destino ) );
    ok( ! in_array( '2026-10-07', fechas( $sin_destino ), true ) && in_array( '2026-10-06', fechas( $sin_destino ), true ), '…así que no se ofrece el miércoles 7, y el martes 6 (sale el lunes) sí', fechas( $sin_destino ) );
    ok( in_array( '2026-10-09', fechas( $sin_destino ), true ), 'sin destino no se aplican los festivos del destino (viernes 9 disponible)' );
  `,

  'Primera entrega: MRW Mataró cierra hoy': String.raw`
    poner_dias( array( '2026-10-05' => array( array( 'BARCELONA', 'Mataro' ) ), '2026-10-07' => array( array( 'MADRID', 'Leganes' ) ) ) );
    $p = dip_primera_entrega( $AHORA );
    ok( $p && '2026-10-07' === $p['fecha'] && '2026-10-06' === $p['sale'], 'lunes 5 sin salida: la primera entrega es el miércoles 7 (sin destino, el festivo de Leganés no cuenta)', $p );
  `,

  'Fechas de envío: destino': String.raw`
    poner_dias( array(
      '2026-10-07' => array( array( 'MADRID', 'Leganes' ) ),
      '2026-10-09' => dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-09' )['cerradas'],
    ) );
    $leganes  = array( 'cp' => '28911', 'poblacion' => 'Leganés' );
    $saltadas = null;
    $f = dip_fechas_disponibles( 'envio', 6, $AHORA, $leganes, $saltadas );
    ok( ! in_array( '2026-10-07', fechas( $f ), true ), 'MRW cierra en Leganés el miércoles 7: no se ofrece', fechas( $f ) );
    ok( array( '2026-10-07', '2026-10-09' ) === $saltadas, 'quedan anotados para avisar el miércoles 7 y el viernes 9 (la página real de MRW también cierra Leganés el 9/10)', $saltadas );
    ok( ! in_array( '2026-10-09', fechas( $f ), true ) && in_array( '2026-10-10', fechas( $f ), true ), 'a Leganés: sin viernes 9, con sábado 10', fechas( $f ) );
    ok( in_array( '2026-10-08', fechas( $f ), true ), 'el jueves 8 (no consultado en estos datos) sí se ofrece: sin datos no se bloquea' );
    ok( in_array( '2026-10-07', fechas( dip_fechas_disponibles( 'envio', 6, $AHORA ) ), true ), 'sin destino, el miércoles 7 sí se ofrece' );
    ok( in_array( '2026-10-07', fechas( dip_fechas_disponibles( 'envio', 6, $AHORA, array( 'cp' => '28901', 'poblacion' => 'Getafe' ) ) ), true ), 'a Getafe, el miércoles 7 sí se ofrece' );
    ok( in_array( '2026-10-07', fechas( dip_fechas_disponibles( 'envio', 6, $AHORA, array( 'cp' => '289', 'poblacion' => 'Leganés' ) ) ), true ), 'con un código postal a medias no se aplica el destino' );
    $s = null;
    $v = dip_fechas_disponibles( 'envio', 6, $AHORA, array( 'cp' => '46001', 'poblacion' => '' ), $s );
    ok( ! in_array( '2026-10-09', fechas( $v ), true ) && array( '2026-10-09' ) === $s, 'Valencia capital sin población: el viernes 9 (festivo autonómico) no se ofrece', array( fechas( $v ), $s ) );
    ok( ! dip_fecha_es_valida( '2026-10-07', 'envio', $leganes, $AHORA ) && dip_fecha_es_valida( '2026-10-07', 'envio', null, $AHORA ), 'dip_fecha_es_valida: el 7 no vale para Leganés y sí sin destino' );
    $p = dip_primera_entrega( $AHORA );
    ok( $p && '2026-10-06' === $p['fecha'], 'dip_primera_entrega() sin destino: martes 6', $p );
  `,

  'Fechas lejanas: cualquier día hasta 90 días vista': String.raw`
    $cal = dip_calendario_entrega( 'envio', $AHORA );
    ok( '2026-10-05' === $cal['desde'] && '2027-01-03' === $cal['hasta'], 'límite: hoy + 90 días (lunes 5/10 → domingo 3/1/2027)', array( $cal['desde'], $cal['hasta'] ) );
    ok( count( $cal['fechas'] ) > 55 && count( dip_fechas_disponibles( 'envio', 0, $AHORA ) ) === count( $cal['fechas'] ), 'dip_fechas_disponibles( …, 0 ) devuelve todas: ' . count( $cal['fechas'] ) . ' fechas' );
    ok( dip_fecha_es_valida( '2026-11-04', 'envio', null, $AHORA ), 'miércoles 4/11 (a 30 días): se puede elegir' );
    $s = $cal['fechas'][ $cal['indice']['2026-11-07'] ?? -1 ] ?? null;
    ok( $s && $s['sabado'] && '2026-11-06' === $s['sale'], 'sábado 7/11: se puede elegir, sale el viernes 6 y lleva suplemento', $s );
    ok( ! dip_fecha_es_valida( '2026-11-08', 'envio', null, $AHORA ) && ! dip_fecha_es_valida( '2026-11-09', 'envio', null, $AHORA ), 'domingo 8/11 y lunes 9/11: no' );
    ok( ! dip_fecha_es_valida( '2026-12-08', 'envio', null, $AHORA ) && ! dip_fecha_es_valida( '2026-12-09', 'envio', null, $AHORA ) && dip_fecha_es_valida( '2026-12-10', 'envio', null, $AHORA ), 'festivo del 8/12 (lista manual): ni ese día ni el 9 (no sale el 8); el 10 sí' );
    ok( ! dip_fecha_es_valida( '2026-12-25', 'envio', null, $AHORA ) && ! dip_fecha_es_valida( '2026-12-26', 'envio', null, $AHORA ) && dip_fecha_es_valida( '2026-12-29', 'envio', null, $AHORA ), 'Navidad y Sant Esteve: no; martes 29/12 sí' );
    ok( dip_fecha_es_valida( '2026-12-31', 'envio', null, $AHORA ) && ! dip_fecha_es_valida( '2027-01-02', 'envio', null, $AHORA ), 'jueves 31/12 sí; sábado 2/1 no (el 1/1 no sale nada)' );
    $jueves = new DateTimeImmutable( '2026-10-01 10:00', wp_timezone() ); // límite: miércoles 30/12
    ok( dip_fecha_es_valida( '2026-12-30', 'envio', null, $jueves ) && ! dip_fecha_es_valida( '2026-12-31', 'envio', null, $jueves ), 'desde el jueves 1/10: el 30/12 (día 90) sí, el 31/12 (día 91) no' );
    ok( dip_fecha_es_valida( '2027-01-01', 'recogida', null, $AHORA ) && dip_fecha_es_valida( '2027-01-02', 'recogida', null, $AHORA ), 'recogida: 1/1 (festivo) y sábado 2/1 (día 89) sí' );
    ok( ! dip_fecha_es_valida( '2027-01-03', 'recogida', null, $AHORA ) && ! dip_fecha_es_valida( '2027-01-04', 'recogida', null, $AHORA ), 'recogida: domingo 3/1 no; lunes 4/1 (día 91) tampoco' );
    ok( dip_fecha_es_valida( '2026-11-09', 'recogida', null, $AHORA ) && ! dip_fecha_es_valida( '2026-11-08', 'recogida', null, $AHORA ), 'recogida lejana: lunes 9/11 sí, domingo 8/11 no' );
  `,

  'Fechas lejanas con festivos de MRW (los próximos 21 días)': String.raw`
    poner_dias( array(
      '2026-10-21' => array( array( 'MADRID', 'Leganes' ) ),   // miércoles: festivo local en Leganés
      '2026-10-27' => array( array( 'BARCELONA', 'Mataro' ) ), // martes: cierra MRW Mataró
    ) );
    $leganes = array( 'cp' => '28911', 'poblacion' => 'Leganés' );
    $getafe  = array( 'cp' => '28901', 'poblacion' => 'Getafe' );
    ok( ! dip_fecha_es_valida( '2026-10-21', 'envio', $leganes, $AHORA ) && dip_fecha_es_valida( '2026-10-21', 'envio', $getafe, $AHORA ), 'miércoles 21/10: no a Leganés, sí a Getafe' );
    ok( in_array( '2026-10-21', dip_calendario_entrega( 'envio', $AHORA, $leganes )['saltadas'], true ), 'el calendario sabe que ese día MRW no reparte en Leganés (para decirlo)' );
    ok( ! dip_fecha_es_valida( '2026-10-28', 'envio', $getafe, $AHORA ) && dip_fecha_es_valida( '2026-10-27', 'envio', $getafe, $AHORA ), 'MRW Mataró cierra el martes 27: no llega el 28; el 27 (sale el lunes) sí' );
    ok( dip_fecha_es_valida( '2026-11-18', 'envio', $leganes, $AHORA ), 'más allá de 21 días (sin datos de MRW): solo la lista manual' );
    $s = null;
    dip_fechas_disponibles( 'envio', 6, $AHORA, $leganes, $s );
    ok( array() === $s, 'el aviso debajo de las casillas solo habla de los días que se saltan entre ellas (el 21 queda lejos)', $s );
    $t = microtime( true );
    for ( $i = 0; $i < 300; $i++ ) dip_fecha_es_valida( '2026-11-07', 'envio', $leganes, $AHORA );
    $memo = microtime( true ) - $t;
    $norm = dip_destino_normalizado( $leganes );
    $t = microtime( true );
    for ( $i = 0; $i < 30; $i++ ) dip_calcular_fechas( 'envio', $AHORA, $norm );
    $sin = ( microtime( true ) - $t ) * 10;
    ok( $memo * 5 < $sin, sprintf( 'se calcula una vez por petición: 300 comprobaciones en %.1f ms; sin memoria serían unos %.0f ms', $memo * 1000, $sin * 1000 ) );
  `,

  'Fecha lejana en el checkout: sesión, suplemento, validación, Store API, pedido y HTML': String.raw`
    eval( 'namespace Automattic\WooCommerce\StoreApi\Exceptions; class RouteException extends \Exception { function __construct( $c, $m, $s = 400 ) { parent::__construct( $m ); } }' );
    class Sesion { public $d = array(); function get( $k ) { return $this->d[ $k ] ?? null; } function set( $k, $v ) { $this->d[ $k ] = $v; } }
    class Cliente { function get_shipping_postcode() { return ''; } function get_billing_postcode() { return '08013'; } function get_shipping_city() { return ''; } function get_billing_city() { return 'Barcelona'; } }
    class Tarifa { public $id; function __construct( $id ) { $this->id = $id; } function get_method_id() { return explode( ':', $this->id )[0]; } function get_cost() { return false !== strpos( $this->id, 'local' ) ? 0 : 13.19; } function get_taxes() { return array(); } }
    class Envios { function get_packages() { return array( array( 'rates' => array( 'flat_rate:1' => new Tarifa( 'flat_rate:1' ), 'local_pickup:2' => new Tarifa( 'local_pickup:2' ) ) ) ); } }
    class Cesta { public $fees = array(); function get_cart() { return array(); } function add_fee( $n, $i, $t = false ) { $this->fees[] = array( $n, round( $i, 2 ) ); } }
    class Tienda { public $session, $customer, $cart; function __construct() { $this->session = new Sesion(); $this->customer = new Cliente(); $this->cart = new Cesta(); } function shipping() { return new Envios(); } }
    class Errores { public $e = array(); function add( $c, $m ) { $this->e[ $c ] = $m; } }
    class WC_Order {
      public $meta = array(); public $cp;
      function __construct( $cp = '08013' ) { $this->cp = $cp; }
      function get_shipping_postcode() { return ''; } function get_billing_postcode() { return $this->cp; }
      function get_shipping_city() { return ''; } function get_billing_city() { return ''; }
      function update_meta_data( $k, $v ) { $this->meta[ $k ] = $v; } function get_meta( $k ) { return $this->meta[ $k ] ?? ''; } function save_meta_data() {}
    }
    $GLOBALS['wc'] = new Tienda();
    function WC() { return $GLOBALS['wc']; }
    function is_admin() { return false; }
    function is_checkout() { return true; }
    function dip_es_recogida( $id ) { return false !== strpos( (string) $id, 'local_pickup' ); }
    function dip_eligio_recogida() { foreach ( (array) WC()->session->get( 'chosen_shipping_methods' ) as $m ) if ( dip_es_recogida( $m ) ) return true; return false; }
    function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
    function wp_unslash( $s ) { return $s; }
    function checked( $a, $b, $eco = true ) { return (string) $a === (string) $b ? " checked='checked'" : ''; }
    function wc_price( $n ) { return '<span class="amount">' . number_format( (float) $n, 2, ',', '.' ) . '&nbsp;€</span>'; }
    function wp_strip_all_tags( $s ) { return trim( strip_tags( $s ) ); }
    function wp_kses_post( $s ) { return $s; }
    function wp_json_encode( $d, $f = 0 ) { return json_encode( $d, $f ); }
    function wc_cart_totals_shipping_html() {}

    // Fechas a partir de HOY (la prueba usa la hora real, como el checkout)
    $destino = array( 'cp' => '08013', 'poblacion' => 'Barcelona' );
    $cal     = dip_calendario_entrega( 'envio', null, $destino );
    $en28    = ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( '+28 day' )->format( 'Y-m-d' );
    $sabado  = null; $miercoles = null;
    foreach ( $cal['fechas'] as $f ) {
      if ( $f['fecha'] < $en28 ) continue;
      if ( ! $sabado && $f['sabado'] ) $sabado = $f['fecha'];
      if ( ! $miercoles && '3' === ( new DateTimeImmutable( $f['fecha'] ) )->format( 'N' ) ) $miercoles = $f['fecha'];
    }
    $domingo = ( new DateTimeImmutable( $sabado ) )->modify( '+1 day' )->format( 'Y-m-d' );
    $fuera   = ( new DateTimeImmutable( $cal['hasta'] ) )->modify( '+1 day' );
    while ( (int) $fuera->format( 'N' ) < 2 || (int) $fuera->format( 'N' ) > 5 ) $fuera = $fuera->modify( '+1 day' );
    $fuera = $fuera->format( 'Y-m-d' );
    // 1. El checkout guarda en la sesión la fecha elegida
    WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate:1' ) );
    disparar( 'woocommerce_checkout_update_order_review', 'billing_postcode=08013&dip_fecha=' . $sabado );
    ok( $sabado === WC()->session->get( 'dip_fecha' ) && $sabado === dip_fecha_elegida(), 'sesión: el sábado lejano queda elegido (sábado ' . $sabado . ', miércoles ' . $miercoles . ', fuera de plazo ' . $fuera . ')', WC()->session->d );

    // 2. Suplemento de sábado
    $cesta = new Cesta();
    disparar( 'woocommerce_cart_calculate_fees', $cesta );
    ok( array( array( 'Entrega en sábado', 9.7 ) ) === $cesta->fees, 'suplemento de sábado (9,70 € + IVA) con la fecha lejana', $cesta->fees );
    WC()->session->set( 'dip_fecha', $miercoles );
    $cesta = new Cesta();
    disparar( 'woocommerce_cart_calculate_fees', $cesta );
    ok( ! $cesta->fees && $miercoles === dip_fecha_elegida(), 'miércoles lejano: sin suplemento', $cesta->fees );
    WC()->session->set( 'dip_fecha', $fuera );
    $primera = array_values( array_filter( dip_fechas_disponibles( 'envio', 8, null, $destino ), static fn( $f ) => ! $f['sabado'] ) )[0]['fecha'];
    ok( $primera === dip_fecha_elegida(), 'fecha a más de 90 días en la sesión: se cambia por la primera disponible (' . $primera . ')', dip_fecha_elegida() );

    // 3. Validación al pagar
    $datos = array( 'shipping_method' => array( 'flat_rate:1' ), 'billing_postcode' => '08013', 'billing_city' => 'Barcelona' );
    $valida = function ( $fecha, $datos ) { $_POST['dip_fecha'] = $fecha; $e = new Errores(); disparar( 'woocommerce_after_checkout_validation', $datos, $e ); return $e->e; };
    ok( array() === $valida( $sabado, $datos ) && array() === $valida( $miercoles, $datos ), 'al pagar: el sábado y el miércoles lejanos se aceptan' );
    ok( isset( $valida( $domingo, $datos )['dip_fecha'] ) && isset( $valida( $fuera, $datos )['dip_fecha'] ), 'al pagar: un domingo o un día a más de 90 días, error' );

    // 4. Pedido del checkout clásico: se guarda la fecha lejana
    $_POST['dip_fecha'] = $sabado;
    $p = new WC_Order();
    disparar( 'woocommerce_checkout_create_order', $p, $datos );
    ok( $sabado === $p->get_meta( '_dip_fecha_entrega' ) && 'envio' === $p->get_meta( '_dip_metodo_entrega' ) && (string) ( new DateTimeImmutable( $sabado, dip_zona_horaria() ) )->getTimestamp() === $p->get_meta( '_orddd_lite_timestamp' ), 'pedido: fecha, método y marca de Order Delivery Date guardados', $p->meta );

    // 5. Store API (Apple Pay / Google Pay): la fecha sale de la sesión
    WC()->session->set( 'dip_fecha', $sabado );
    $p = new WC_Order();
    disparar( 'woocommerce_store_api_checkout_update_order_from_request', $p );
    ok( $sabado === $p->get_meta( '_dip_fecha_entrega' ), 'Store API: guarda el sábado lejano', $p->meta );
    WC()->session->set( 'dip_fecha', $domingo );
    $error = '';
    try { disparar( 'woocommerce_store_api_checkout_update_order_from_request', new WC_Order() ); } catch ( \Exception $e ) { $error = $e->getMessage(); }
    ok( dip_textos_tienda( 'es' )['fecha_no_disponible'] === $error, 'Store API: un domingo lejano se rechaza sin hablar del corte de las 12:00 (' . $error . ')', $error );
    WC()->session->set( 'dip_fecha', '2020-01-07' );
    $error = '';
    try { disparar( 'woocommerce_store_api_checkout_update_order_from_request', new WC_Order() ); } catch ( \Exception $e ) { $error = $e->getMessage(); }
    ok( dip_textos_tienda( 'es' )['fecha_no_valida'] === $error, 'una fecha ya pasada sí explica el corte de las 12:00', $error );

    // 6. HTML del bloque de entrega con la fecha lejana elegida
    WC()->session->set( 'dip_fecha', $sabado );
    $html = dip_html_entrega();
    preg_match( '/data-dip-cal="([^"]+)"/', $html, $m );
    $json = json_decode( html_entity_decode( $m[1] ?? '', ENT_QUOTES, 'UTF-8' ), true );
    ok( $json && $sabado === $json['sel'] && in_array( $sabado, $json['f'], true ) && $json['f'] === array_column( $cal['fechas'], 'fecha' ), 'data-dip-cal: todas las fechas válidas (' . count( $json['f'] ?? array() ) . ') y la elegida', $json ? array_slice( $json, 2, 4 ) : $html );
    ok( $json && '+11,74' . "\u{00A0}" . '€' === $json['sab'] && 6 === $json['c'] && 7 === count( $json['n']['d'] ) && 12 === count( $json['n']['mde'] ), 'data-dip-cal: suplemento, casillas y nombres de días y meses', $json ? array( $json['sab'], $json['c'] ) : null );
    preg_match_all( '/<label class="dip-fecha[^"]*"><input type="radio" name="dip_fecha" value="([\d-]+)"( checked)?/', $html, $c );
    ok( 6 === count( $c[1] ) && $sabado === $c[1][5] && ' checked' === $c[2][5] && 1 === substr_count( $html, "checked='checked'" ) - 1, 'casillas: 5 primeras + el sábado lejano marcado', $c[1] );
    ok( false !== strpos( $html, 'dip-fecha--lejana' ) && false !== strpos( $html, 'class="dip-otra-fecha" aria-expanded="false" aria-controls="dip-cal"' ) && false !== strpos( $html, 'id="dip-cal" hidden' ), 'botón "Otra fecha" y hueco del calendario' );
    WC()->session->set( 'dip_fecha', '' );
    $html = dip_html_entrega();
    preg_match_all( '/name="dip_fecha" value="([\d-]+)"/', $html, $c );
    ok( $c[1] === array_column( dip_fechas_disponibles( 'envio', 6, null, $destino ), 'fecha' ) && false === strpos( $html, 'dip-fecha--lejana' ), 'sin fecha lejana: las 6 primeras fechas', $c[1] );

    // 7. Recogida: lunes lejano sí, sábado lejano con suplemento de recogida
    WC()->session->set( 'chosen_shipping_methods', array( 'local_pickup:2' ) );
    $r = dip_calendario_entrega( 'recogida' );
    $lunes = null; $sabado_r = null;
    foreach ( $r['fechas'] as $f ) {
      if ( $f['fecha'] < $en28 ) continue;
      if ( ! $lunes && '1' === ( new DateTimeImmutable( $f['fecha'] ) )->format( 'N' ) ) $lunes = $f['fecha'];
      if ( ! $sabado_r && $f['sabado'] ) $sabado_r = $f['fecha'];
    }
    WC()->session->set( 'dip_fecha', $sabado_r );
    $cesta = new Cesta();
    disparar( 'woocommerce_cart_calculate_fees', $cesta );
    ok( array( array( 'Recogida en sábado', 9.7 ) ) === $cesta->fees, 'recogida el sábado lejano ' . $sabado_r . ': suplemento de recogida', $cesta->fees );
    $datos_r = array( 'shipping_method' => array( 'local_pickup:2' ), 'billing_postcode' => '28911' );
    ok( $lunes && array() === $valida( $lunes, $datos_r ) && isset( $valida( ( new DateTimeImmutable( $lunes ) )->modify( '-1 day' )->format( 'Y-m-d' ), $datos_r )['dip_fecha'] ), 'recogida: lunes lejano ' . $lunes . ' sí, el domingo anterior no' );
    $_POST['dip_fecha'] = $lunes;
    $p = new WC_Order( '28911' );
    disparar( 'woocommerce_checkout_create_order', $p, $datos_r );
    ok( $lunes === $p->get_meta( '_dip_fecha_entrega' ) && 'recogida' === $p->get_meta( '_dip_metodo_entrega' ), 'pedido de recogida con el lunes lejano', $p->meta );
  `,

  'Recogida: no depende de MRW': String.raw`
    poner_dias( array(
      '2026-10-05' => array( array( 'BARCELONA', 'Mataro' ) ),
      '2026-10-06' => array( array( 'BARCELONA', 'Mataro' ) ),
      '2026-10-07' => array( array( 'MADRID', 'Leganes' ) ),
      '2026-10-12' => dip_mrw_analizar( file_get_contents( '/f/d0812.html' ), '2026-12-08' )['cerradas'], // como si el 12 cerrara toda España
    ) );
    $esperado = array( '2026-10-05', '2026-10-06', '2026-10-07', '2026-10-08', '2026-10-09', '2026-10-10', '2026-10-12', '2026-10-13' );
    $sin = dip_fechas_disponibles( 'recogida', 8, $AHORA );
    $con = dip_fechas_disponibles( 'recogida', 8, $AHORA, array( 'cp' => '28911', 'poblacion' => 'Leganés' ) );
    ok( $esperado === fechas( $sin ) && $sin === $con, 'la recogida ofrece lunes a sábado (también festivos) aunque MRW cierre, con o sin destino', array( fechas( $sin ), fechas( $con ) ) );
  `,

  'Corte de las 12:00, fines de semana y zona horaria': String.raw`
    $m = fn( $s ) => new DateTimeImmutable( $s, wp_timezone() );
    ok( dip_antes_del_corte( $m( '2026-10-05 11:59' ) ) && ! dip_antes_del_corte( $m( '2026-10-05 12:00' ) ), 'corte: 11:59 dentro de plazo, 12:00 fuera' );
    $utc = new DateTimeImmutable( '2026-10-05 10:30', new DateTimeZone( 'UTC' ) ); // 12:30 en Madrid (horario de verano)
    ok( ! dip_antes_del_corte( $utc ), 'una hora en UTC se pasa a Madrid: 10:30 UTC son las 12:30, fuera de plazo' );
    ok( fechas( dip_fechas_disponibles( 'envio', 3, $utc ) ) === fechas( dip_fechas_disponibles( 'envio', 3, $m( '2026-10-05 12:30' ) ) ), 'mismas fechas con la hora en UTC que en Madrid' );
    ok( 'Europe/Madrid' === dip_zona_horaria()->getName(), 'zona horaria del negocio: Europe/Madrid' );
    $f = dip_fechas_disponibles( 'envio', 3, $m( '2026-10-08 11:00' ) );
    ok( array( '2026-10-09', '2026-10-10', '2026-10-14' ) === fechas( $f ) && $f[1]['sabado'] && '2026-10-09' === $f[1]['sale'], 'jueves 11:00: viernes 9, sábado 10 (sale el viernes, con suplemento) y miércoles 14 (lunes 12 festivo)', $f );
    ok( array( '2026-10-10', '2026-10-14', '2026-10-15' ) === fechas( dip_fechas_disponibles( 'envio', 3, $m( '2026-10-08 12:00' ) ) ), 'jueves 12:00: ya no llega el viernes' );
    ok( '2026-10-14' === dip_primera_entrega( $m( '2026-10-08 12:00' ) )['fecha'], 'jueves 12:00: la primera entrega sin sábado es el miércoles 14' );
    ok( array( '2026-10-10', '2026-10-14' ) === fechas( dip_fechas_disponibles( 'envio', 2, $m( '2026-10-09 11:00' ) ) ), 'viernes 11:00: sábado 10 y después miércoles 14' );
    ok( array( '2026-10-14' ) === fechas( dip_fechas_disponibles( 'envio', 1, $m( '2026-10-10 09:00' ) ) ) && array( '2026-10-14' ) === fechas( dip_fechas_disponibles( 'envio', 1, $m( '2026-10-11 09:00' ) ) ), 'sábado y domingo: la primera entrega es el miércoles 14 (el lunes 12 no sale)' );
    ok( array( '2026-10-27', '2026-10-28' ) === fechas( dip_fechas_disponibles( 'envio', 2, $m( '2026-10-23 13:00' ) ) ), 'viernes 23/10 a las 13:00 (cambio de hora el domingo 25): martes 27 y miércoles 28' );
    $malas = array();
    foreach ( dip_fechas_disponibles( 'envio', 40, $AHORA ) as $x ) {
      $llega = new DateTimeImmutable( $x['fecha'] );
      $sale  = new DateTimeImmutable( $x['sale'] );
      if ( in_array( (int) $llega->format( 'N' ), array( 1, 7 ), true ) || (int) $sale->format( 'N' ) > 5 || $sale->modify( '+1 day' )->format( 'Y-m-d' ) !== $x['fecha'] || $x['sabado'] !== ( 6 === (int) $llega->format( 'N' ) ) ) $malas[] = $x;
    }
    ok( ! $malas, '40 fechas seguidas: nunca llega en domingo ni lunes, sale de lunes a viernes y llega al día siguiente', $malas );
  `,

  'Corte de las 12:00 con MRW Mataró cerrado': String.raw`
    poner_dias( array( '2026-10-06' => array( array( 'BARCELONA', 'Mataro' ) ) ) );
    $tarde = new DateTimeImmutable( '2026-10-05 13:00', wp_timezone() );
    $f = dip_fechas_disponibles( 'envio', 3, $tarde );
    ok( array( '2026-10-08', '2026-10-09', '2026-10-10' ) === fechas( $f ) && '2026-10-07' === $f[0]['sale'], 'lunes 13:00 y MRW Mataró cerrado el martes: primera salida el miércoles 7, llega el jueves 8', $f );
  `,

  'Lista para el tema (dip_festivos_salida)': String.raw`
    $hoy  = new DateTimeImmutable( 'today', wp_timezone() );
    $ayer = $hoy->modify( '-1 day' )->format( 'Y-m-d' );
    $en3  = $hoy->modify( '+3 day' )->format( 'Y-m-d' );
    $en4  = $hoy->modify( '+4 day' )->format( 'Y-m-d' );
    poner_dias( array(
      $ayer => array( array( 'BARCELONA', 'Mataro' ) ),
      $en3  => array( array( 'BARCELONA', 'Mataro' ) ),
      $en4  => array( array( 'MADRID', 'Leganes' ) ),
    ) );
    $l = dip_festivos_salida();
    ok( in_array( $en3, $l, true ), 'incluye el día en que cierra MRW Mataró (' . $en3 . ')', $l );
    ok( ! in_array( $ayer, $l, true ) && ! in_array( $en4, $l, true ), 'no incluye días pasados ni festivos de otras poblaciones' );
    ok( in_array( '2026-12-25', $l, true ) && in_array( '2027-01-06', $l, true ), 'mantiene la lista manual' );
    $ordenada = $l; sort( $ordenada );
    ok( $ordenada === $l && count( $l ) === count( array_unique( $l ) ), 'ordenada y sin repetidos' );
  `,

  'Nota de días sin reparto (ES/CA/EN)': String.raw`
    $leganes = array( 'cp' => '28911', 'poblacion' => 'Leganés' );
    $n = dip_nota_festivos_destino( array( '2026-10-07' ), $leganes, 'es' );
    ok( 'MRW no reparte el miércoles 7 de octubre en Leganés (festivo).' === $n, 'ES: ' . $n );
    $n = dip_nota_festivos_destino( array( '2026-10-07' ), $leganes, 'ca' );
    ok( "MRW no reparteix el dimecres 7 d'octubre a Leganés (festiu)." === $n, 'CA: ' . $n );
    $n = dip_nota_festivos_destino( array( '2026-10-07' ), $leganes, 'en' );
    ok( 'MRW does not deliver on Wednesday 7 October in Leganés (public holiday).' === $n, 'EN: ' . $n );
    $n = dip_nota_festivos_destino( array( '2026-10-09', '2026-10-07' ), array( 'cp' => '46001', 'poblacion' => 'València' ), 'es' );
    ok( 'MRW no reparte el miércoles 7 de octubre y el viernes 9 de octubre en València (festivos).' === $n, 'ES, dos días: ' . $n );
    $n = dip_nota_festivos_destino( array( '2026-10-09' ), array( 'cp' => '46001', 'poblacion' => '' ), 'es' );
    ok( 'MRW no reparte el viernes 9 de octubre en Valencia (festivo).' === $n, 'ES, sin población (provincia cerrada): ' . $n );
    ok( '' === dip_nota_festivos_destino( array(), $leganes, 'es' ), 'sin días saltados, sin nota' );
    foreach ( array( 'es', 'ca', 'en' ) as $i ) {
      $t = dip_textos_tienda( $i );
      ok( isset( $t['fecha_festivo_destino'], $t['mrw_festivo'], $t['mrw_festivos'], $t['mrw_y'], $t['mrw_coma'] ), "textos de MRW en $i" );
    }
  `,

  'Comprobación automática (WP-Cron) y avisos por email': String.raw`
    $GLOBALS['http'] = http_mrw( function ( $ymd, $n ) {
      if ( 2 === $n ) return array( array( 'cerrada', 'Mataro', 'BARCELONA' ), array( 'abierta', 'Badalona', 'BARCELONA' ) );
      return array( array( 'cerrada', 'Leganes', 'MADRID' ) );
    } );
    $r = dip_mrw_actualizar();
    $e = get_option( 'dip_mrw' );
    $hoy = new DateTimeImmutable( 'today', wp_timezone() );
    ok( 21 === $r && 21 === count( $e['dias'] ) && 0 === $e['fallos'] && $e['ok'] > 0, 'lee 21 días y los guarda', is_wp_error( $r ) ? $r->get_error_message() : $r );
    ok( dip_mrw_cierra_salida( $hoy->modify( '+2 day' )->format( 'Y-m-d' ) ) && ! dip_mrw_cierra_salida( $hoy->modify( '+1 day' )->format( 'Y-m-d' ) ), 'detecta el cierre de MRW Mataró dentro de 2 días' );
    ok( ! get_transient( 'dip_mrw_bloqueo' ), 'libera el bloqueo al terminar' );
    ok( ! $GLOBALS['correos'], 'si todo va bien, no manda emails' );
    ob_start(); dip_mrw_html_estado(); $html = ob_get_clean();
    ok( false !== strpos( $html, 'Comprobar ahora' ) && false !== strpos( $html, 'No (cierra MRW Mataró)' ), 'la pantalla de estado muestra el botón y el día sin salida' );
  `,

  'MRW caído: se conservan los datos y se avisa': String.raw`
    $hoy     = new DateTimeImmutable( 'today', wp_timezone() );
    $manana  = $hoy->modify( '+1 day' )->format( 'Y-m-d' );
    $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => array( $manana => array( array( 'BARCELONA', 'Mataro' ) ) ), 'ok' => time() - 3600, 'fallos' => 0 );
    $GLOBALS['http'] = function ( $url ) { return new WP_Error( 'http_request_failed', 'cURL error 28: timeout' ); };
    $r = dip_mrw_actualizar();
    ok( is_wp_error( $r ) && false !== strpos( $r->get_error_message(), 'timeout' ), 'primer fallo: devuelve el error', is_wp_error( $r ) ? $r->get_error_message() : $r );
    ok( 1 === get_option( 'dip_mrw' )['fallos'] && ! $GLOBALS['correos'], 'primer fallo: aún no avisa' );
    ok( isset( get_option( 'dip_mrw' )['dias'][ $manana ] ), 'conserva los días que ya tenía' );
    dip_mrw_actualizar();
    ok( 1 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][0][1], 'no se pueden comprobar' ) && 'info@dryicepack.es' === $GLOBALS['correos'][0][0], 'segundo fallo seguido: email a info@dryicepack.es', $GLOBALS['correos'] );
    ok( false !== strpos( $GLOBALS['correos'][0][2], 'lista manual' ), 'el email explica la alternativa manual' );
    ok( '' !== dip_mrw_problema(), 'el escritorio muestra el aviso' );
    dip_mrw_actualizar();
    ok( 1 === count( $GLOBALS['correos'] ), 'tercer fallo el mismo día: no repite el email' );
    $GLOBALS['http'] = http_mrw( fn( $ymd, $n ) => array( array( 'cerrada', 'Leganes', 'MADRID' ) ) );
    dip_mrw_actualizar();
    ok( 2 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][1][1], 'vuelven' ) && '' === dip_mrw_problema(), 'cuando vuelve a funcionar: email de que se ha resuelto' );
  `,

  'Aviso del escritorio durante la comprobación (sin email repetido)': String.raw`
    $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => array(), 'ok' => time() - 3600, 'fallos' => 1, 'error' => 'x', 'avisado' => time() - 2 * 86400, 'desde' => time() - 86400 * 30 );
    // Mientras se consulta MRW, el escritorio (dip_mrw_vigilar en admin_init) manda el aviso y lo anota en la opción
    $GLOBALS['http'] = function ( $url ) {
      if ( empty( $GLOBALS['ya'] ) ) {
        $GLOBALS['ya'] = 1;
        $GLOBALS['opciones']['dip_mrw']['avisado'] = time();
        $GLOBALS['correos'][] = array( 'info@dryicepack.es', 'Dryicepack: no se pueden comprobar los festivos de MRW', '(desde el escritorio)' );
      }
      return new WP_Error( 'http_request_failed', 'cURL error 28: timeout' );
    };
    dip_mrw_actualizar();
    ok( 1 === count( $GLOBALS['correos'] ), 'el aviso anotado mientras se consultaba MRW no se pisa: un solo email', array_column( $GLOBALS['correos'], 2 ) );
    ok( 2 === get_option( 'dip_mrw' )['fallos'], 'y el fallo sí se cuenta' );
  `,

  'Email de "vuelve a funcionar" si el primero falla': String.raw`
    $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => array(), 'ok' => time() - 3600, 'fallos' => 2, 'error' => 'x', 'avisado' => time() - 3600, 'desde' => time() - 86400 * 30 );
    $GLOBALS['http'] = http_mrw( fn( $ymd, $n ) => array( array( 'cerrada', 'Leganes', 'MADRID' ) ) );
    $GLOBALS['correo_falla'] = true;
    dip_mrw_actualizar();
    ok( '' === dip_mrw_problema() && get_option( 'dip_mrw' )['avisado'] > 0, 'vuelve a funcionar pero el email falla: queda pendiente' );
    $GLOBALS['correo_falla'] = false;
    dip_mrw_vigilar();
    ok( 1 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][0][1], 'vuelven' ) && 0 === get_option( 'dip_mrw' )['avisado'], 'en la siguiente comprobación sale el email de que vuelve a funcionar', $GLOBALS['correos'] );
    dip_mrw_vigilar();
    ok( 1 === count( $GLOBALS['correos'] ), '…y no se repite' );
  `,

  'Opción guardada: sin autoload y sin crecer': String.raw`
    $hoy  = new DateTimeImmutable( 'today', wp_timezone() );
    $dias = array();
    for ( $i = 1; $i <= 60; $i++ ) $dias[ $hoy->modify( "-{$i} day" )->format( 'Y-m-d' ) ] = array( array( 'BARCELONA', 'Mataro' ) );
    $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => $dias, 'ok' => time() - 3600 );
    $GLOBALS['http'] = http_mrw( fn( $ymd, $n ) => array( array( 'cerrada', 'Leganes', 'MADRID' ) ) );
    dip_mrw_actualizar();
    $e = get_option( 'dip_mrw' );
    ok( 21 === count( $e['dias'] ) && min( array_keys( $e['dias'] ) ) === $hoy->format( 'Y-m-d' ), 'los días pasados se borran: quedan los 21 de hoy en adelante', count( $e['dias'] ) );
    ok( false === ( $GLOBALS['autoload']['dip_mrw'] ?? null ), 'se guarda sin autoload' );
    $claves = array_keys( $e ); sort( $claves );
    ok( array( 'comprobado', 'desde', 'dias', 'error', 'fallos', 'ok' ) === $claves, 'solo guarda los campos previstos', $claves );
  `,

  'Página de MRW cambiada o con errores': String.raw`
    $hoy    = new DateTimeImmutable( 'today', wp_timezone() );
    $manana = $hoy->modify( '+1 day' )->format( 'Y-m-d' );
    $previo = array( 'dias' => array( $manana => array( array( 'BARCELONA', 'Mataro' ) ) ), 'ok' => time(), 'fallos' => 0 );
    $GLOBALS['opciones']['dip_mrw'] = $previo;
    $GLOBALS['http'] = http_mrw( fn( $ymd, $n ) => array() );
    $r = dip_mrw_actualizar();
    ok( is_wp_error( $r ) && false !== strpos( $r->get_error_message(), 'ningún festivo' ), '21 días sin ningún cierre: se da por página cambiada', is_wp_error( $r ) ? $r->get_error_message() : $r );
    ok( array( $manana => array( array( 'BARCELONA', 'Mataro' ) ) ) === get_option( 'dip_mrw' )['dias'], '…y no sustituye los datos buenos por días vacíos' );
    $GLOBALS['http'] = function ( $url ) { return array( 'response' => array( 'code' => 503 ), 'body' => '' ); };
    $r = dip_mrw_actualizar();
    ok( is_wp_error( $r ) && false !== strpos( $r->get_error_message(), '503' ), 'respuesta 503: error con el código', is_wp_error( $r ) ? $r->get_error_message() : $r );
    set_transient( 'dip_mrw_bloqueo', 1 );
    $r = dip_mrw_actualizar();
    ok( is_wp_error( $r ) && 'dip_mrw_ocupado' === $r->get_error_code(), 'dos comprobaciones a la vez: la segunda espera' );
    $GLOBALS['opciones']['dip_mrw'] = array( 'desde' => time() - 50 * 3600 );
    ok( false !== strpos( dip_mrw_problema(), 'nunca' ), 'si WP-Cron no ha llegado a ejecutarse en 48 h, también avisa' );
  `,

  // Límite de 1 s (en la web, 120 s) y un MRW que tarda 0,3 s por día
  'MRW lento: límite de tiempo de la comprobación': {
    antes: "define( 'DIP_MRW_LIMITE_S', 1 );",
    codigo: String.raw`
    $hoy  = new DateTimeImmutable( 'today', wp_timezone() );
    $en15 = $hoy->modify( '+15 day' )->format( 'Y-m-d' );
    $ok0  = time() - 3600;
    $GLOBALS['opciones']['dip_mrw'] = array( 'dias' => array( $en15 => array( array( 'BARCELONA', 'Mataro' ) ) ), 'ok' => $ok0, 'fallos' => 0, 'desde' => time() - 86400 * 30 );
    $rapido = http_mrw( fn( $ymd, $n ) => array( array( 'cerrada', 'Leganes', 'MADRID' ) ) );
    $GLOBALS['peticiones'] = 0;
    $GLOBALS['http'] = function ( $url ) use ( $rapido ) {
      $GLOBALS['peticiones']++;
      $t = microtime( true );
      while ( microtime( true ) - $t < 0.3 ) {} // MRW tarda
      return $rapido( $url );
    };
    $t0  = microtime( true );
    $r   = dip_mrw_actualizar();
    $dur = microtime( true ) - $t0;
    $e   = get_option( 'dip_mrw' );
    $n   = $GLOBALS['peticiones'];
    $msg = is_wp_error( $r ) ? $r->get_error_message() : $r;
    ok( is_wp_error( $r ) && 0 === strpos( $msg, 'MRW responde despacio: solo se han leído ' . $n . ' días' ), 'al llegar al límite se para y lo dice: "' . $msg . '"', $msg );
    ok( $n >= 2 && $n < 21 && $dur < 2.5, 'no espera a los 21 días: ' . $n . ' consultas en ' . round( $dur, 1 ) . ' s con un límite de 1 s', array( $n, $dur ) );
    $leidos = array_filter( $e['dias'], static fn( $c ) => array( array( 'MADRID', 'Leganes' ) ) === $c );
    ok( count( $leidos ) === $n && isset( $leidos[ $hoy->format( 'Y-m-d' ) ] ), 'guarda los ' . $n . ' días leídos', array_keys( $e['dias'] ) );
    ok( array( array( 'BARCELONA', 'Mataro' ) ) === ( $e['dias'][ $en15 ] ?? null ), 'y conserva los que ya tenía y no ha llegado a leer (día +15)' );
    ok( 1 === $e['fallos'] && $ok0 === $e['ok'] && 0 === strpos( $e['error'], 'MRW responde despacio' ), 'cuenta como fallo, no como comprobación correcta', $e );
    ok( ! get_transient( 'dip_mrw_bloqueo' ) && ! $GLOBALS['correos'], 'libera el bloqueo y al primer fallo aún no avisa' );
    dip_mrw_actualizar();
    ok( 1 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][0][2], 'MRW responde despacio' ), 'dos veces seguidas: email de aviso con el motivo', array_column( $GLOBALS['correos'], 2 ) );
  `,
  },

  'WP-Cron: una sola comprobación programada': String.raw`
    $GLOBALS['cron'] = array();
    wp_schedule_event( time() + 30, 'daily', 'dip_tarea_diaria' );
    dip_mrw_programar();
    ok( 1 === count( horas_cron( 'dip_mrw_actualizar' ) ) && 'twicedaily' === reset( $GLOBALS['cron'][ horas_cron( 'dip_mrw_actualizar' )[0] ]['dip_mrw_actualizar'] )['schedule'], 'sin ninguna programada: programa una, dos veces al día', $GLOBALS['cron'] );
    dip_mrw_programar();
    dip_mrw_programar();
    ok( 1 === count( horas_cron( 'dip_mrw_actualizar' ) ), 'las visitas siguientes no añaden otra' );
    // Dos visitas a la vez (o restos de antes): tres programadas
    $primera = horas_cron( 'dip_mrw_actualizar' )[0];
    wp_schedule_event( $primera + 500, 'twicedaily', 'dip_mrw_actualizar' );
    wp_schedule_event( $primera - 200, 'twicedaily', 'dip_mrw_actualizar' );
    ok( 3 === count( horas_cron( 'dip_mrw_actualizar' ) ), '(preparación: tres programadas)' );
    dip_mrw_programar();
    ok( array( $primera - 200 ) === horas_cron( 'dip_mrw_actualizar' ), 'con tres programadas deja solo una, la más próxima', horas_cron( 'dip_mrw_actualizar' ) );
    ok( 1 === count( horas_cron( 'dip_tarea_diaria' ) ), 'no toca las demás tareas' );
    dip_mrw_programar();
    ok( array( $primera - 200 ) === horas_cron( 'dip_mrw_actualizar' ), 'y no vuelve a programar otra' );
  `,

  'Textos de la tienda (ES/CA/EN)': String.raw`
    $es = dip_textos_tienda( 'es' ); $ca = dip_textos_tienda( 'ca' ); $en = dip_textos_tienda( 'en' );
    ok( 'Recoger en nuestra nave de Mataró' === $es['recogida'] && 'Recollir a la nostra nau de Mataró' === $ca['recogida'] && 'Collect from our warehouse in Mataró' === $en['recogida'], 'recogida sin "Gratis" (lo dice la columna del precio y el sábado tiene suplemento)' );
    ok( str_ends_with( $es['recogida_confirmar'], 'El sábado lleva un suplemento de 11,74 € (IVA incluido).' ), 'ES: ' . $es['recogida_confirmar'] );
    ok( str_ends_with( $ca['recogida_confirmar'], "El dissabte té un suplement d'11,74 € (IVA inclòs)." ), 'CA: ' . $ca['recogida_confirmar'] );
    ok( str_ends_with( $en['recogida_confirmar'], 'Saturday has an €11.74 surcharge (VAT included).' ), 'EN: ' . $en['recogida_confirmar'] );
    ok( 'Pide antes de las 12:00 para no perder la primera fecha.' === $es['corte'] && 'Demana abans de les 12:00 per no perdre la primera data.' === $ca['corte'] && 'Order before 12:00 to keep the earliest date.' === $en['corte'], 'el corte ya no promete "salen hoy" (falso en fin de semana y festivos)' );
    ok( 0 === strpos( $es['fecha_festivo_destino'], 'MRW no reparte' ) && 0 === strpos( $ca['fecha_festivo_destino'], 'MRW no reparteix' ) && 0 === strpos( $en['fecha_festivo_destino'], 'MRW does not deliver' ), 'el error de festivo en destino dice que es MRW quien no reparte' );
    ok( 'Recogida de tu pedido 1234: día y hora' === sprintf( $es['recogida_lista_asunto'], '1234' ) && 'Hola, Marta. Tu hielo seco estará listo en nuestra nave de Mataró:' === sprintf( $es['recogida_lista_hola'], 'Marta' ), 'email de recogida: asunto con día y hora, saludo con coma' );
    ok( 'Hola, Marta. El teu gel sec estarà a punt a la nostra nau de Mataró:' === sprintf( $ca['recogida_lista_hola'], 'Marta' ) && 'Recollida de la teva comanda 1234: dia i hora' === sprintf( $ca['recogida_lista_asunto'], '1234' ), 'CA: asunto y saludo' );
    $seg = array( 'es' => array( 'guantes', 'dryicepack.es/seguridad-del-hielo-seco/' ), 'ca' => array( 'guants', 'dryicepack.es/ca/seguretat-del-gel-sec/' ), 'en' => array( 'gloves', 'dryicepack.es/en/dry-ice-safety/' ) );
    $mal = array();
    foreach ( $seg as $i => $buscar ) foreach ( $buscar as $b ) if ( false === strpos( dip_textos_tienda( $i )['recogida_lista_seguridad'], $b ) ) $mal[] = "$i: $b";
    ok( ! $mal, 'email de recogida: guantes y página de seguridad en los tres idiomas', $mal );
    ok( false !== strpos( $es['a_medida_texto'], 'sale más caro' ) && false !== strpos( $es['a_medida_texto'], 'te confirmamos el día de recogida' ), 'a medida: avisa de que sale más caro' );
    $todo_ca = implode( ' ', $ca );
    ok( false === strpos( $todo_ca, '’' ) && false === stripos( $todo_ca, 'lliurament' ), 'CA: apóstrofo recto y "entrega" (no "lliurament"), como el resto del tema' );
    // Mismas claves y mismos marcadores (%s, %1$s…) que el castellano: si no, sprintf fallaría en un idioma
    $marcas = static function ( $t ) { preg_match_all( '/%(?:\d\$)?[sd]/', (string) $t, $m ); sort( $m[0] ); return $m[0]; };
    foreach ( array( 'ca' => $ca, 'en' => $en ) as $i => $t ) {
      $ke = array_keys( $es ); $kt = array_keys( $t ); sort( $ke ); sort( $kt );
      $distintas = array();
      foreach ( $es as $k => $v ) if ( $marcas( $v ) !== $marcas( $t[ $k ] ?? '' ) ) $distintas[] = $k;
      ok( $ke === $kt && ! $distintas, "$i: mismas claves y marcadores que el castellano", array( array_diff( $ke, $kt ), array_diff( $kt, $ke ), $distintas ) );
    }
  `,

  'Email "Recogida de tu pedido" (ES, CA y EN)': String.raw`
    class WC_Order {
      public $meta; public $notas = array();
      function __construct( $meta ) { $this->meta = $meta; }
      function get_meta( $k ) { return $this->meta[ $k ] ?? ''; }
      function update_meta_data( $k, $v ) { $this->meta[ $k ] = $v; }
      function save_meta_data() {}
      function add_order_note( $n ) { $this->notas[] = $n; }
      function get_payment_method() { return 'cod'; }
      function get_total() { return 81.81; }
      function get_currency() { return 'EUR'; }
      function get_billing_first_name() { return 'Marta Soler'; }
      function get_order_number() { return '1234'; }
      function get_billing_email() { return 'marta@example.com'; }
    }
    class Correo {
      function wrap_message( $titulo, $cuerpo ) { return $cuerpo; }
      function send( $a, $asunto, $html ) { $GLOBALS['enviados'][] = array( $a, $asunto, $html ); return true; }
    }
    class Tienda { function mailer() { return new Correo(); } }
    function WC() { return new Tienda(); }
    function wc_price( $n, $args = array() ) { return '<span class="amount">' . dip_euros( $n ) . '</span>'; }
    function wp_strip_all_tags( $s ) { return trim( strip_tags( $s ) ); }
    $envia = function ( $idioma ) {
      $GLOBALS['enviados'] = array();
      $p = new WC_Order( array( '_dip_idioma' => $idioma, '_dip_fecha_entrega' => '2026-10-10', '_dip_hora_recogida' => '10:30', '_dip_metodo_entrega' => 'recogida' ) );
      dip_enviar_confirmacion_recogida( $p );
      return array( $GLOBALS['enviados'][0] ?? array( '', '', '' ), $p );
    };
    list( $m, $p ) = $envia( 'es' );
    ok( 'Recogida de tu pedido 1234: día y hora' === $m[1], 'ES asunto: ' . $m[1] );
    ok( false !== strpos( $m[2], 'Hola, Marta Soler. Tu hielo seco estará listo' ) && false !== strpos( $m[2], 'sábado 10 de octubre · 10:30' ), 'ES: saludo con coma, día y hora', $m[2] );
    ok( false !== strpos( $m[2], 'Usa guantes térmicos o pinzas. Todas las normas: <a href="https://dryicepack.es/seguridad-del-hielo-seco/">dryicepack.es/seguridad-del-hielo-seco/</a>' ), 'ES: guantes y enlace a la página de seguridad que se puede pulsar', $m[2] );
    ok( false !== strpos( $m[2], 'Se paga en efectivo al recogerlo: 81,81 €.' ) && $p->get_meta( '_dip_recogida_confirmada' ), 'ES: importe en efectivo y recogida anotada como confirmada' );
    list( $m ) = $envia( 'ca' );
    ok( 'Recollida de la teva comanda 1234: dia i hora' === $m[1] && false !== strpos( $m[2], 'Hola, Marta Soler. El teu gel sec' ) && false !== strpos( $m[2], 'href="https://dryicepack.es/ca/seguretat-del-gel-sec/"' ), 'CA: asunto, saludo y enlace', array( $m[1], $m[2] ) );
    list( $m ) = $envia( 'en' );
    ok( 'Collection of your order 1234: day and time' === $m[1] && false !== strpos( $m[2], 'Hello Marta Soler, your dry ice' ) && false !== strpos( $m[2], 'href="https://dryicepack.es/en/dry-ice-safety/"' ), 'EN: asunto, saludo y enlace', array( $m[1], $m[2] ) );
  `,

  'Fuera de la península: solo península, sin invitar a escribir': String.raw`
    $es = dip_textos_tienda( 'es' ); $ca = dip_textos_tienda( 'ca' ); $en = dip_textos_tienda( 'en' );
    ok( 'Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.' === $es['fuera_peninsula'], 'ES: ' . $es['fuera_peninsula'] );
    ok( 'Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.' === $ca['fuera_peninsula'], 'CA: ' . $ca['fuera_peninsula'] );
    ok( 'We only deliver to mainland Spain: not to the Balearic Islands, the Canary Islands, Ceuta or Melilla.' === $en['fuera_peninsula'], 'EN: ' . $en['fuera_peninsula'] );
    $mal = array();
    foreach ( array( 'es' => $es, 'ca' => $ca, 'en' => $en ) as $i => $t ) {
      foreach ( $t as $k => $v ) {
        if ( preg_match( '/balear|canari|canàri|canary|ceuta|melilla|islas|islands|illes/iu', (string) $v ) && preg_match( '/escr[ií]b|escriu|write|message|consult|ask us|estudi|contact/iu', (string) $v ) ) $mal[] = $i . '.' . $k;
      }
    }
    ok( ! $mal, 'ningún texto de la tienda invita a pedir para Baleares, Canarias, Ceuta o Melilla', $mal );
  `,

  'Pedidos reservados con antelación: revisión diaria de festivos de MRW': String.raw`
    require '/p/inc/seguimiento-entregas.php';
    class WC_Order {
      public $meta; public $notas = array(); public $d;
      function __construct( $id, $d ) {
        $this->d    = $d + array( 'id' => $id, 'cp' => '08013', 'ciudad' => 'Barcelona', 'estado' => 'processing', 'recoge' => false );
        $this->meta = array( '_dip_fecha_entrega' => $d['fecha'], '_dip_metodo_entrega' => empty( $d['recoge'] ) ? 'envio' : 'recogida' );
      }
      function get_meta( $k ) { return $this->meta[ $k ] ?? ''; }
      function update_meta_data( $k, $v ) { $this->meta[ $k ] = $v; }
      function save_meta_data() {}
      function add_order_note( $n ) { $this->notas[] = $n; }
      function get_order_number() { return (string) $this->d['id']; }
      function get_shipping_postcode() { return ''; } function get_billing_postcode() { return $this->d['cp']; }
      function get_shipping_city() { return ''; } function get_billing_city() { return $this->d['ciudad']; }
      function get_billing_first_name() { return 'Marta'; } function get_billing_last_name() { return 'Soler'; } function get_billing_company() { return ''; }
      function get_billing_phone() { return '600 11 22 33'; } function get_billing_email() { return 'marta@example.com'; }
      function get_edit_order_url() { return 'http://prueba.local/wp-admin/post.php?post=' . $this->d['id'] . '&action=edit'; }
      function has_shipping_method( $m ) { return ! empty( $this->d['recoge'] ); }
    }
    function wc_get_orders( $args ) {
      if ( ! empty( $GLOBALS['explota'] ) ) throw new RuntimeException( 'la base de datos no responde' );
      $GLOBALS['consultas'][] = $args;
      return array_values( array_filter( $GLOBALS['pedidos'], static fn( $p ) => in_array( 'wc-' . $p->d['estado'], $args['status'], true ) ) );
    }
    function wc_get_order( $id ) { return $GLOBALS['pedidos'][ $id ] ?? false; }
    function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
    function sanitize_key( $s ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $s ) ); }
    function wp_unslash( $s ) { return $s; }
    $GLOBALS['peticiones'] = 0;
    $GLOBALS['http'] = function ( $url ) { $GLOBALS['peticiones']++; return new WP_Error( 'x', 'no tendría que llamar a MRW' ); };
    $dias = array(
      '2026-10-09' => dip_mrw_analizar( file_get_contents( '/f/d0910.html' ), '2026-10-09' )['cerradas'], // viernes: Comunidad Valenciana
      '2026-10-21' => array( array( 'MADRID', 'Leganes' ) ),   // miércoles: festivo local en Leganés
      '2026-10-26' => array( array( 'BARCELONA', 'Mataro' ) ), // lunes: cierra MRW Mataró
    );
    poner_dias( $dias );
    $GLOBALS['pedidos'] = array();
    $nuevo = static function ( $id, $d ) { return $GLOBALS['pedidos'][ $id ] = new WC_Order( $id, $d ); };
    $p101 = $nuevo( 101, array( 'fecha' => '2026-10-21', 'cp' => '28911', 'ciudad' => 'Leganés' ) );
    $p102 = $nuevo( 102, array( 'fecha' => '2026-10-21', 'cp' => '28901', 'ciudad' => 'Getafe' ) );
    $p103 = $nuevo( 103, array( 'fecha' => '2026-10-27' ) );                                               // sale el lunes 26: cierra MRW Mataró
    $p104 = $nuevo( 104, array( 'fecha' => '2026-11-18', 'cp' => '28911', 'ciudad' => 'Leganés' ) );        // más allá de los 21 días de MRW
    $p105 = $nuevo( 105, array( 'fecha' => '2026-10-21', 'cp' => '28911', 'ciudad' => 'Leganés', 'recoge' => true ) );
    $p106 = $nuevo( 106, array( 'fecha' => '2026-10-21', 'cp' => '28911', 'ciudad' => 'Leganés', 'estado' => 'completed' ) );
    $p107 = $nuevo( 107, array( 'fecha' => '2026-10-09', 'cp' => '46001', 'ciudad' => '', 'estado' => 'on-hold' ) );
    $p108 = $nuevo( 108, array( 'fecha' => '2026-10-02', 'cp' => '28911', 'ciudad' => 'Leganés' ) );        // ya pasado
    $p109 = $nuevo( 109, array( 'fecha' => '2026-12-09' ) );                                               // sale el 8/12, festivo de la lista manual

    // 1. Primera revisión
    $r = dip_seg_revisar( $AHORA );
    ok( is_array( $r ) && 6 === $r['revisados'] && 4 === $r['avisos'] && 0 === $r['sin_enviar'], 'revisa 6 pedidos con envío pendiente (sin recogidas, completados ni fechas pasadas) y avisa de 4', $r );
    ok( 0 === $GLOBALS['peticiones'], 'no hace ninguna petición a MRW: solo lee los festivos guardados' );
    ok( array( 'wc-processing', 'wc-on-hold' ) === ( $GLOBALS['consultas'][0]['status'] ?? null ), 'busca los pedidos Procesando y En espera', $GLOBALS['consultas'][0] ?? null );
    ok( 1 === count( $GLOBALS['correos'] ) && 'info@dryicepack.es' === $GLOBALS['correos'][0][0] && 'Dryicepack: cambiar el día de entrega de 4 pedidos (festivos)' === $GLOBALS['correos'][0][1], 'un solo email al correo de avisos con los 4 pedidos', array_column( $GLOBALS['correos'], 1 ) );
    $email = $GLOBALS['correos'][0][2] ?? '';
    $falta = array();
    foreach ( array(
      "\nPedido 101 · Marta Soler · 28911 Leganés\n",
      'Entrega elegida: miércoles 21 de octubre (sale de Mataró el martes 20 de octubre)',
      'Motivo: MRW no reparte el miércoles 21 de octubre en Leganés: cierra por festivo.',
      'Días con reparto más cercanos: martes 20 de octubre o jueves 22 de octubre',
      'Teléfono: 600 11 22 33 · Email: marta@example.com',
      'Abrir el pedido: http://prueba.local/wp-admin/post.php?post=101&action=edit',
      "\nPedido 103 ",
      'Motivo: El lunes 26 de octubre cierra MRW Mataró: ese día no sale nada de Mataró.',
      "\nPedido 107 ",
      'Motivo: MRW no reparte el viernes 9 de octubre en Valencia: cierra por festivo.',
      "\nPedido 109 ",
      'Motivo: El martes 8 de diciembre está en la lista de festivos de Dryicepack → Ajustes: ese día no sale nada de Mataró.',
      '1. Llama al cliente',
      'elige el día nuevo en «Cambiar el día»',
      'el suplemento de sábado no se ajusta solo',
    ) as $b ) if ( false === strpos( $email, $b ) ) $falta[] = $b;
    ok( ! $falta, 'el email da pedido, cliente, teléfono, motivo, días alternativos, enlace y la alternativa manual (llamar y cambiar el día)', $falta );
    $sobra = array_filter( array( 102, 104, 105, 106, 108 ), static fn( $n ) => false !== strpos( $email, "\nPedido " . $n . ' ' ) );
    ok( ! $sobra, 'no avisa de Getafe (abierto), de fechas sin datos de MRW, de recogidas, de pedidos completados ni de fechas pasadas', array_values( $sobra ) );
    ok( 1 === count( $p101->notas ) && 0 === strpos( $p101->notas[0], 'Aviso de entrega: MRW no reparte el miércoles 21 de octubre en Leganés' ) && false !== strpos( $p101->notas[0], 'Aviso enviado a info@dryicepack.es.' ), 'nota en el pedido con el motivo y el aviso enviado', $p101->notas );
    ok( ! $p102->notas && ! $p104->notas && ! $p105->notas && ! $p106->notas && ! $p108->notas, 'sin notas en los pedidos que no tienen problema' );
    ok( 1 === count( $p103->notas ) && 1 === count( $p107->notas ) && 1 === count( $p109->notas ), 'una nota en cada pedido afectado' );
    ok( false !== strpos( $p103->notas[0], 'sábado 24 de octubre (con suplemento) o miércoles 28 de octubre' ), 'días alternativos con el sábado marcado (suplemento)', $p103->notas[0] );

    // 2. Al día siguiente (y los siguientes): ni email ni nota repetidos
    $r = dip_seg_revisar( $AHORA );
    dip_seg_revisar( $AHORA->modify( '+1 day' ) );
    ok( is_array( $r ) && 0 === $r['avisos'] && 1 === count( $GLOBALS['correos'] ) && 1 === count( $p101->notas ), 'un aviso por pedido y fecha: no se repite cada día', array( $r, count( $GLOBALS['correos'] ) ) );

    // 3. MRW publica un cierre nuevo (Getafe el 21): se detecta en la siguiente revisión, con los datos recién guardados
    $dias['2026-10-21'][] = array( 'MADRID', 'Getafe' );
    poner_dias( $dias );
    $r = dip_seg_revisar( $AHORA );
    ok( is_array( $r ) && 1 === $r['avisos'] && 2 === count( $GLOBALS['correos'] ) && 'Dryicepack: cambiar el día de entrega del pedido 102 (festivo)' === $GLOBALS['correos'][1][1], 'un cierre publicado después (Getafe el 21/10) se avisa al revisar de nuevo', array_column( $GLOBALS['correos'], 1 ) );

    // 4. Alternativa manual: cambiar el día en el pedido
    ok( dip_seg_cambiar_fecha( $p101, '2026-10-22', $AHORA ) && '2026-10-22' === $p101->get_meta( '_dip_fecha_entrega' ) && (string) ( new DateTimeImmutable( '2026-10-22', dip_zona_horaria() ) )->getTimestamp() === $p101->get_meta( '_orddd_lite_timestamp' ), 'cambiar el día: se guarda la fecha nueva (también la marca de Order Delivery Date)', $p101->meta );
    ok( 'Día de entrega cambiado en el escritorio: miércoles 21 de octubre → jueves 22 de octubre. El cliente no recibe ningún aviso automático.' === end( $p101->notas ), 'cambiar el día deja una nota en el pedido', end( $p101->notas ) );
    ok( ! dip_seg_cambiar_fecha( $p101, '2026-10-22', $AHORA ) && ! dip_seg_cambiar_fecha( $p101, '22/10/2026', $AHORA ) && ! dip_seg_cambiar_fecha( $p101, '2026-02-30', $AHORA ) && ! dip_seg_cambiar_fecha( $p101, '', $AHORA ), 'la misma fecha, otro formato, una fecha imposible o vacío: no cambia nada' );
    dip_seg_cambiar_fecha( $p104, '2026-10-21', $AHORA );
    ok( false !== strpos( end( $p104->notas ), 'Ojo: MRW no reparte el miércoles 21 de octubre en Leganés' ), 'si el día nuevo también es un cierre, la nota lo dice', end( $p104->notas ) );
    dip_seg_cambiar_fecha( $p102, '2026-10-25', $AHORA );
    ok( false !== strpos( end( $p102->notas ), 'Ojo: ese día no está entre los que ofrece la web' ), 'un domingo: la nota avisa de que no es un día de la web', end( $p102->notas ) );
    $antes = count( $GLOBALS['correos'] );
    $r = dip_seg_revisar( $AHORA );
    ok( is_array( $r ) && 1 === $r['avisos'] && $antes + 1 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][ $antes ][1], 'pedido 104' ), 'el día nuevo se vuelve a revisar: avisa del 104 (21/10) y ya no del 101 (22/10)', array_column( $GLOBALS['correos'], 1 ) );

    // 5. Pantalla del pedido: campo «Cambiar el día», aviso y guardado
    ob_start(); disparar( 'woocommerce_admin_order_data_after_shipping_address', $p104 ); $html = ob_get_clean();
    ok( false !== strpos( $html, '<input type="date" id="dip_cambiar_fecha" name="dip_cambiar_fecha" value="2026-10-21">' ) && false !== strpos( $html, 'Aviso: MRW no reparte el miércoles 21 de octubre en Leganés' ), 'el pedido muestra el campo «Cambiar el día» y el aviso', $html );
    ob_start(); disparar( 'woocommerce_admin_order_data_after_shipping_address', $p105 ); $html = ob_get_clean();
    ok( false !== strpos( $html, 'Cambiar el día de recogida' ) && false === strpos( $html, 'Aviso:' ), 'en una recogida: «Cambiar el día de recogida», sin aviso de MRW', $html );
    $_POST['dip_cambiar_fecha'] = '2026-10-23';
    $notas = count( $p104->notas );
    disparar( 'woocommerce_process_shop_order_meta', 104 );
    ok( '2026-10-23' === $p104->get_meta( '_dip_fecha_entrega' ) && $notas + 1 === count( $p104->notas ), 'al guardar el pedido se aplica el día nuevo', $p104->meta );
    $_POST['dip_cambiar_fecha'] = '2026-10-23';
    disparar( 'woocommerce_process_shop_order_meta', 104 );
    ok( $notas + 1 === count( $p104->notas ), 'guardar el pedido sin tocar el día no deja notas' );
    unset( $_POST['dip_cambiar_fecha'] );

    // 6. El email falla: nota una sola vez, aviso en el escritorio y reintento al día siguiente
    $GLOBALS['correo_falla'] = true;
    $p110 = $nuevo( 110, array( 'fecha' => '2026-10-21', 'cp' => '28911', 'ciudad' => 'Leganés' ) );
    $antes = count( $GLOBALS['correos'] );
    $r = dip_seg_revisar( $AHORA );
    ok( is_array( $r ) && 1 === $r['sin_enviar'] && 0 === $r['avisos'] && 1 === count( $p110->notas ) && false !== strpos( $p110->notas[0], 'No se ha podido enviar el email de aviso' ), 'email caído: la nota lo dice', array( $r, $p110->notas ) );
    $aviso = dip_seg_texto_aviso();
    ok( false !== strpos( $aviso, 'el pedido 110 tiene la entrega en un festivo' ), 'aviso en el escritorio con el número de pedido: ' . $aviso );
    dip_seg_revisar( $AHORA );
    ok( 1 === count( $p110->notas ), 'mientras falla, la nota no se repite' );
    $GLOBALS['correo_falla'] = false;
    dip_seg_revisar( $AHORA );
    ok( $antes + 1 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][ $antes ][1], 'pedido 110' ) && 1 === count( $p110->notas ) && '' === dip_seg_texto_aviso(), 'cuando vuelve el email: sale el aviso del 110, sin otra nota, y se quita el aviso del escritorio', array_column( $GLOBALS['correos'], 1 ) );
    dip_seg_revisar( $AHORA );
    ok( $antes + 1 === count( $GLOBALS['correos'] ), '…y no se repite' );

    // 7. La revisión falla: email con la alternativa manual, una vez al día, y otro cuando se arregla
    $GLOBALS['explota'] = true;
    $antes = count( $GLOBALS['correos'] );
    $r = dip_seg_revisar( $AHORA );
    ok( is_wp_error( $r ) && false !== strpos( $r->get_error_message(), 'la base de datos no responde' ) && ! get_transient( 'dip_seg_bloqueo' ), 'si la revisión falla, devuelve el error y libera el bloqueo', is_wp_error( $r ) ? $r->get_error_message() : $r );
    $m = $GLOBALS['correos'][ $antes ] ?? array( '', '', '' );
    ok( 'Dryicepack: no se pueden revisar los pedidos reservados' === $m[1] && false !== strpos( $m[2], 'la base de datos no responde' ) && false !== strpos( $m[2], 'revísalo a mano' ) && false !== strpos( $m[2], '«Cambiar el día»' ) && false !== strpos( $m[2], 'Revisar pedidos ahora' ), 'email de fallo con el motivo y la revisión a mano', $m );
    ok( false !== strpos( dip_seg_texto_aviso(), 'ha fallado' ), 'aviso en el escritorio: ' . dip_seg_texto_aviso() );
    dip_seg_revisar( $AHORA );
    ok( $antes + 1 === count( $GLOBALS['correos'] ), 'el email de fallo no se repite el mismo día' );
    $GLOBALS['explota'] = false;
    dip_seg_revisar( $AHORA );
    ok( $antes + 2 === count( $GLOBALS['correos'] ) && false !== strpos( $GLOBALS['correos'][ $antes + 1 ][1], 'vuelve a funcionar' ) && '' === dip_seg_problema(), 'cuando se arregla: email de que vuelve a funcionar', array_column( $GLOBALS['correos'], 1 ) );

    // 8. Dos revisiones a la vez y estado en Dryicepack → Ajustes
    set_transient( 'dip_seg_bloqueo', 1 );
    $r = dip_seg_revisar( $AHORA );
    ok( is_wp_error( $r ) && 'dip_seg_ocupado' === $r->get_error_code(), 'dos revisiones a la vez: la segunda espera' );
    delete_transient( 'dip_seg_bloqueo' );
    ob_start(); dip_mrw_html_estado(); $html = ob_get_clean();
    ok( false !== strpos( $html, 'id="dip-seg"' ) && false !== strpos( $html, 'Revisar pedidos ahora' ) && false !== strpos( $html, 'Última revisión' ) && false !== strpos( $html, 'MRW publica sus festivos 21 días antes' ), 'Dryicepack → Ajustes: estado de la revisión y botón «Revisar pedidos ahora»' );
    $e = get_option( 'dip_seguimiento' );
    ok( false === ( $GLOBALS['autoload']['dip_seguimiento'] ?? null ) && ! empty( $e['ok'] ) && '' === $e['error'], 'el estado se guarda sin autoload', $e );
  `,

  'Revisión de pedidos: WP-Cron una vez al día y aviso si no se ejecuta': String.raw`
    require '/p/inc/seguimiento-entregas.php';
    $m = fn( $s ) => new DateTimeImmutable( $s, wp_timezone() );
    ok( $m( '2026-10-06 09:30' )->getTimestamp() === dip_seg_primera_hora( $m( '2026-10-05 10:00' ) ) && $m( '2026-10-05 09:30' )->getTimestamp() === dip_seg_primera_hora( $m( '2026-10-05 08:00' ) ), 'la primera revisión es a las 9:30 de Madrid (hoy si aún no han pasado; si no, mañana)' );
    $GLOBALS['cron'] = array();
    dip_mrw_programar();
    dip_seg_programar();
    dip_seg_programar();
    $h = horas_cron( 'dip_revisar_entregas' );
    ok( 1 === count( $h ) && 'daily' === reset( $GLOBALS['cron'][ $h[0] ]['dip_revisar_entregas'] )['schedule'] && '09:30' === wp_date( 'H:i', $h[0] ), 'programada una sola vez, a diario, a las 9:30', $GLOBALS['cron'] );
    wp_schedule_event( $h[0] + 600, 'daily', 'dip_revisar_entregas' );
    dip_seg_programar();
    ok( array( $h[0] ) === horas_cron( 'dip_revisar_entregas' ) && 1 === count( horas_cron( 'dip_mrw_actualizar' ) ), 'si queda repetida, deja solo la primera (y no toca la de MRW)', $GLOBALS['cron'] );

    $GLOBALS['opciones']['dip_seguimiento'] = array( 'ok' => time() - 49 * 3600, 'desde' => time() - 30 * 86400, 'error' => '' );
    ok( false !== strpos( dip_seg_problema(), 'hace 49 horas' ) && false !== strpos( dip_seg_problema(), 'WP-Cron' ), 'más de 48 h sin revisión: ' . dip_seg_problema() );
    dip_seg_vigilar();
    dip_seg_vigilar();
    ok( 1 === count( $GLOBALS['correos'] ) && 'Dryicepack: no se pueden revisar los pedidos reservados' === $GLOBALS['correos'][0][1] && false !== strpos( $GLOBALS['correos'][0][2], 'hace 49 horas' ), 'email al correo de avisos, una vez al día', array_column( $GLOBALS['correos'], 1 ) );
    $GLOBALS['opciones']['dip_seguimiento'] = array( 'desde' => time() - 50 * 3600 );
    ok( false !== strpos( dip_seg_problema(), 'nunca' ), 'si no se ha hecho nunca en 48 h, también avisa: ' . dip_seg_problema() );
    $GLOBALS['opciones']['dip_seguimiento'] = array( 'desde' => time() - 3600 );
    ok( '' === dip_seg_problema(), 'recién instalado: sin aviso' );
  `,

  'Facturación mensual y Odoo: pedidos ya facturados por la web': String.raw`
    require '/p/inc/informes.php';
    class Fecha { public $t; function __construct( $t ) { $this->t = $t; } function getTimestamp() { return $this->t; } }
    class Linea {
      public $n, $q, $s;
      function __construct( $n, $q, $s ) { $this->n = $n; $this->q = $q; $this->s = $s; }
      function get_name() { return $this->n; } function get_quantity() { return $this->q; } function get_subtotal() { return $this->s; } function get_total() { return $this->s; }
    }
    class WC_Order {
      public $d;
      function __construct( $d ) { $this->d = $d + array( 'empresa' => '', 'cliente' => 0, 'meta' => array(), 'pago' => 'Tarjeta', 'nota' => '' ); }
      function get_id() { return $this->d['id']; } function get_order_number() { return (string) $this->d['id']; }
      function get_meta( $k ) { return $this->d['meta'][ $k ] ?? ''; }
      function get_billing_company() { return $this->d['empresa']; } function get_billing_first_name() { return $this->d['nombre']; } function get_billing_last_name() { return $this->d['apellido']; }
      function get_billing_email() { return $this->d['email']; } function get_customer_id() { return $this->d['cliente']; }
      function get_date_created() { return new Fecha( ( new DateTimeImmutable( $this->d['fecha'] . ' 10:00', wp_timezone() ) )->getTimestamp() ); }
      function get_total() { return $this->d['total']; } function get_total_tax() { return round( $this->d['total'] - $this->d['total'] / 1.21, 2 ); }
      function get_payment_method_title() { return $this->d['pago']; } function get_customer_note() { return $this->d['nota']; }
      function get_edit_order_url() { return 'http://prueba.local/wp-admin/post.php?post=' . $this->d['id']; }
      function get_items( $tipo = 'line_item' ) { return 'line_item' === $tipo ? array( new Linea( 'Hielo seco 10 kg · 3 mm', 1, 53.3 ) ) : ( 'shipping' === $tipo ? array( new Linea( 'Envío', 1, 13.19 ) ) : array() ); }
    }
    function wc_format_localized_decimal( $v ) { return str_replace( '.', ',', (string) $v ); }
    function wc_price( $n ) { return '<span class="amount">' . number_format( (float) $n, 2, ',', '.' ) . '&nbsp;€</span>'; }
    function wp_kses_post( $s ) { return $s; }
    function submit_button( $t, $c = '', $n = '', $w = true, $o = array() ) { echo '<button>' . $t . '</button>'; }
    $pedidos = array(
      new WC_Order( array( 'id' => 201, 'nombre' => 'Marta', 'apellido' => 'Soler', 'email' => 'marta@example.com', 'cliente' => 7, 'fecha' => '2026-10-03', 'total' => 80.62, 'meta' => array( '_dip_fs_numero' => 'W2026-00001', '_dip_kg' => 10 ) ) ),
      new WC_Order( array( 'id' => 202, 'nombre' => 'Jordi', 'apellido' => 'Puig', 'empresa' => 'Frío Rápido S.L.', 'email' => 'compras@friorapido.es', 'cliente' => 9, 'fecha' => '2026-10-05', 'total' => 129.03, 'pago' => 'Factura mensual', 'meta' => array( '_billing_nif' => 'B12345678', '_dip_factura_mensual' => 1, '_dip_kg' => 20 ) ) ),
      new WC_Order( array( 'id' => 203, 'nombre' => 'Pau', 'apellido' => 'Vidal', 'email' => 'pau@example.com', 'fecha' => '2026-10-07', 'total' => 52.02, 'meta' => array( '_dip_fs_numero' => 'W2026-00002', '_dip_factura_datos' => array( 'razon' => 'Pau Vidal Eventos', 'nif' => '12345678Z' ), '_billing_nif' => '12345678Z', '_dip_kg' => 3 ) ) ),
      new WC_Order( array( 'id' => 204, 'nombre' => 'Ana', 'apellido' => 'Ruiz', 'email' => 'ana@example.com', 'fecha' => '2026-10-08', 'total' => 36.06, 'pago' => 'Efectivo al recoger', 'meta' => array( '_dip_kg' => 3 ) ) ),
      new WC_Order( array( 'id' => 205, 'nombre' => 'Marta', 'apellido' => 'Soler', 'email' => 'marta@example.com', 'cliente' => 7, 'fecha' => '2026-10-20', 'total' => 48.32, 'meta' => array( '_dip_fs_numero' => 'W2026-00003', '_dip_kg' => 3 ) ) ),
    );

    // CSV de facturación mensual
    $f = dip_filas_facturacion( $pedidos );
    ok( 15 === count( $f[0] ) && 'Factura web' === $f[0][13] && 'Estado' === $f[0][14] && 'Factura mensual' === $f[0][12], 'CSV: columnas «Factura web» y «Estado» al final (las de antes no se mueven)', $f[0] );
    ok( array( 'W2026-00001', 'Ya facturado por la web' ) === array_slice( $f[1], 13 ) && array( '', 'Pendiente de facturar' ) === array_slice( $f[2], 13 ), 'CSV: el pedido con factura simplificada lleva su número y «Ya facturado por la web»; el de empresa, «Pendiente de facturar»', array( $f[1], $f[2] ) );
    ok( array( 'W2026-00002', 'Pendiente: factura completa pedida, sustituye a la W2026-00002' ) === array_slice( $f[3], 13 ), 'CSV: con factura completa pedida después, vuelve a estar pendiente y dice a qué factura sustituye', $f[3] );
    ok( 8 === count( $f ) && 'Total pendiente de facturar' === $f[6][0] && '3 pedidos' === $f[6][3] && '217,11' === $f[6][10] && '26' === $f[6][6], 'CSV: total pendiente de facturar sin los ya facturados (3 pedidos, 217,11 €, 26 kg)', $f[6] ?? null );
    ok( 'Total ya facturado por la web (no se vuelve a facturar)' === $f[7][0] && '2 pedidos' === $f[7][3] && '128,94' === $f[7][10], 'CSV: total ya facturado por la web aparte (2 pedidos, 128,94 €)', $f[7] ?? null );
    ok( 5 === count( array_filter( array_slice( $f, 1, 5 ), static fn( $x ) => preg_match( '/^20\d$/', $x[3] ) ) ), 'CSV: no se borra ningún pedido de la lista' );
    ok( 1 === count( dip_filas_facturacion( array() ) ), 'CSV de un mes sin pedidos: solo la cabecera' );

    // Pantalla «Facturación mensual»
    ob_start(); dip_pintar_facturacion( '2026-10', $pedidos ); $h = ob_get_clean();
    ok( false !== strpos( $h, '<th>Factura web</th>' ) && 2 === substr_count( $h, 'class="dip-fw-web"' ) && 3 === substr_count( $h, 'class="dip-fw-pendiente"' ), 'pantalla: columna «Factura web» y 2 filas marcadas como ya facturadas' );
    ok( 1 === preg_match( '#<p class="dip-fw-explica">.*«Ya facturado por la web».*no la vuelvas a hacer en la aplicación de la gestoría.*«Pendiente de facturar».*</p>#u', $h ), 'pantalla: una línea explica qué significa «Ya facturado por la web»' );
    ok( 1 === preg_match( '#Pendiente de facturar</strong></th><td>3 pedidos</td><td>26 kg</td><td[^>]*><strong><span class="amount">217,11&nbsp;€#u', $h ) && 1 === preg_match( '#Ya facturado por la web</th><td>2 pedidos</td><td>13 kg</td><td[^>]*><span class="amount">128,94&nbsp;€#u', $h ), 'pantalla: resumen con el pendiente de facturar separado de lo ya facturado por la web' );
    $pos = array_map( static fn( $n ) => strpos( $h, '<h2 style="margin-top:28px">' . $n ), array( 'Frío Rápido S.L.', 'Ana Ruiz', 'Pau Vidal', 'Marta Soler' ) );
    ok( ! in_array( false, $pos, true ) && $pos === array_values( array_unique( $pos ) ) && $pos[0] < $pos[1] && $pos[1] < $pos[2] && $pos[2] < $pos[3], 'pantalla: primero la factura mensual, después lo pendiente y al final el cliente que ya facturó todo la web', $pos );
    ok( 1 === substr_count( $h, 'Total del mes' ) && 4 === substr_count( $h, '<th colspan="3">Pendiente de facturar</th>' ), 'pantalla: cada cliente (4) con su «Pendiente de facturar»; «Total del mes» solo donde hay pedidos ya facturados' );
    ok( 5 === preg_match_all( '#>\#20[1-5]</a>#', $h ), 'pantalla: están los 5 pedidos' );

    // CSV para Odoo
    $o = dip_filas_odoo( $pedidos );
    ok( 'Factura web' === $o[0][6] && 11 === count( $o ) && ! array_filter( $o, static fn( $x ) => 11 !== count( $x ) ), 'Odoo: columna «Factura web» y todas las filas con las mismas columnas', $o[0] );
    ok( '__import__.woo_201' === $o[1][0] && 'W2026-00001' === $o[1][6] && false !== strpos( $o[1][5], 'Ya facturado por la web: W2026-00001, no facturar otra vez' ) && '' === $o[2][6], 'Odoo: el número W… en la primera línea del pedido y aviso en la nota', array( $o[1], $o[2] ) );
    ok( '' === $o[3][6] && false === strpos( $o[3][5], 'facturado' ) && 'W2026-00002' === $o[5][6] && false !== strpos( $o[5][5], 'Pendiente: factura completa pedida' ), 'Odoo: el pedido sin factura web va limpio; el de factura completa pedida dice que está pendiente', array( $o[3], $o[5] ) );
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

/* ---------- JavaScript del tema: "llega el …" con la configuración de dipt_config_js() ---------- */
/** Primera entrega que calcula movimiento.js con esa configuración y el reloj parado en ts (segundos). */
function entregaJs(config, ts) {
  const fijo = ts * 1000;
  class FechaFija extends Date { constructor(...a) { super(...(a.length ? a : [fijo])); } static now() { return fijo; } }
  const nada = () => {};
  const lista = () => [];
  const ctx = {
    DIPT: config, Date: FechaFija, setTimeout: nada, setInterval: nada, addEventListener: nada, console,
    matchMedia: () => ({ matches: false }),
    document: { readyState: 'complete', cookie: '', querySelector: () => null, querySelectorAll: lista, addEventListener: nada,
      documentElement: { classList: { contains: () => false, add: nada, remove: nada, toggle: nada } } },
  };
  ctx.window = ctx;
  vm.runInNewContext(movimientoJs, ctx);
  const r = ctx.dipEntrega.proxima();
  return r ? r.entrega.toISOString().slice(0, 10) : null;
}
console.log('\n— Tema: "llega el …" (movimiento.js) igual que el checkout');
const casosJs = [
  { texto: 'lunes 10:00 y MRW Mataró cierra el martes: sale el lunes y llega el martes', hora: '10:00', cierra: 1, antes: true },
  { texto: 'lunes 10:00 y MRW Mataró cierra el lunes: sale el martes y llega el miércoles', hora: '10:00', cierra: 0 },
  { texto: 'lunes 13:00 y MRW Mataró cierra el martes: sale el miércoles y llega el jueves', hora: '13:00', cierra: 1 },
];
for (const c of casosJs) {
  const salida = await ejecutar('', String.raw`
    function dipt_idioma() { return 'es'; }
    require '/t/datos.php';
    $lunes = ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( 'next monday' );
    // Un lunes sin festivos de la lista manual de lunes a jueves (p. ej., el 12/10 lo es): si no, el caso no prueba nada y falla
    $semana = static fn( $l ) => array_map( static fn( $i ) => $l->modify( '+' . $i . ' day' )->format( 'Y-m-d' ), range( 0, 3 ) );
    while ( array_intersect( $semana( $lunes ), array_map( 'strval', array_keys( dip_festivos() ) ) ) ) $lunes = $lunes->modify( '+7 day' );
    $ahora = new DateTimeImmutable( $lunes->format( 'Y-m-d' ) . ' ${c.hora}', wp_timezone() );
    $cierra = $lunes->modify( '+${c.cierra} day' )->format( 'Y-m-d' );
    poner_dias( array( $cierra => array( array( 'BARCELONA', 'Mataro' ) ) ) );
    $p = dip_primera_entrega( $ahora );
    echo json_encode( array( 'config' => dipt_config_js(), 'ts' => $ahora->getTimestamp(), 'php' => $p ? $p['fecha'] : null, 'cierra' => $cierra ) );
  `);
  let d;
  try { d = JSON.parse(salida); } catch { contar('FALLO ' + c.texto + ': PHP no devuelve la configuración  → ' + salida.slice(0, 300)); continue; }
  const manualYMrw = [...new Set([...d.config.festivos, ...d.config.sinSalida])].sort();
  const separado = d.config.sinSalida.includes(d.cierra) && !d.config.festivos.includes(d.cierra);
  const js = entregaJs(d.config, d.ts);
  const lineas = [`${js === d.php && separado ? 'OK   ' : 'FALLO'} ${c.texto}: checkout ${d.php}, JS ${js}${separado ? '' : ' (el cierre de MRW no va en sinSalida)'}`];
  if (c.antes) {
    // Como antes (cierres de MRW Mataró mezclados con los festivos): el JS saltaba el día de llegada que el checkout ofrece
    const viejo = entregaJs({ ...d.config, festivos: manualYMrw, sinSalida: [] }, d.ts);
    lineas.push(`${viejo !== d.php ? 'OK   ' : 'FALLO'} con la lista mezclada de antes, el JS habría dicho ${viejo} en vez de ${d.php}`);
  }
  contar(lineas.join('\n'));
}

console.log(`\n${total - fallos} de ${total} comprobaciones correctas${fallos ? ` · ${fallos} con fallo` : ''}`);
process.exit(fallos ? 1 : 0);
