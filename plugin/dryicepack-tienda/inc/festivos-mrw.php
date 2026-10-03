<?php
/**
 * Festivos de MRW, automáticos.
 *
 * MRW publica, día a día, qué oficinas cierran por festivo (nacional, autonómico o local):
 * https://www.mrw.es/oficina_transporte_urgente/mrw_festividades.asp
 * Dos veces al día (WP-Cron) se consultan los próximos 21 días y se guarda en la opción 'dip_mrw', para cada día,
 * qué oficinas cierran (provincia y población). Durante una visita nunca se llama a MRW: solo se lee esa opción.
 *
 * Con eso:
 * - No hay salida el día que cierra la oficina de Mataró (festivo local, de Cataluña o nacional).
 * - No hay entrega el día que cierra la oficina de la población del cliente, o casi toda su provincia (≥ 60 % de sus oficinas).
 * - Un día que no se ha podido consultar no bloquea nada: manda la lista manual.
 * - El checkout deja reservar hasta 90 días: inc/seguimiento-entregas.php repasa cada día, con estos datos guardados,
 *   los pedidos pendientes y avisa si el día elegido ha resultado ser un cierre de MRW.
 *
 * Plan B (siempre activo): la lista manual de "Festivos sin salida ni entrega" de Dryicepack → Ajustes.
 * Cada comprobación dura como mucho unos 2 minutos: si MRW va lento, se guarda lo leído y cuenta como fallo.
 * Si la consulta falla dos veces seguidas, si la página de MRW cambia o si los datos tienen más de 48 h,
 * se envía un email al correo de avisos y sale un aviso en el escritorio. Cuando vuelve a funcionar, otro email.
 * Funciona en el servidor de la web (WP-Cron): no depende de nadie más.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_MRW_URL' ) )       define( 'DIP_MRW_URL', 'https://www.mrw.es/oficina_transporte_urgente/MRW_festividades.asp' );
if ( ! defined( 'DIP_MRW_DIAS' ) )      define( 'DIP_MRW_DIAS', 21 );     // días que se consultan por delante
if ( ! defined( 'DIP_MRW_CADUCA' ) )    define( 'DIP_MRW_CADUCA', 48 );   // horas sin datos nuevos antes de avisar
if ( ! defined( 'DIP_MRW_PROVINCIA' ) ) define( 'DIP_MRW_PROVINCIA', 0.6 ); // parte de las oficinas cerradas para dar por cerrada la provincia
if ( ! defined( 'DIP_MRW_PAUSA_MS' ) )  define( 'DIP_MRW_PAUSA_MS', 300 ); // pausa entre consultas, para no cargar la web de MRW
if ( ! defined( 'DIP_MRW_LIMITE_S' ) )  define( 'DIP_MRW_LIMITE_S', 120 ); // tiempo máximo de una comprobación: si MRW va lento, se guarda lo leído

/** Provincia como la escribe MRW, en mayúsculas y sin acentos ("CORUÑA" → "CORUNA"). */
function dip_mrw_clave_provincia( $texto ) {
	return strtoupper( trim( preg_replace( '/\s+/', ' ', remove_accents( (string) $texto ) ) ) );
}

/** Provincia de MRW a partir del código postal (península). */
function dip_mrw_provincia_por_cp( $cp ) {
	$cp = preg_replace( '/\D/', '', (string) $cp );
	$mapa = array(
		'01' => 'ALAVA', '02' => 'ALBACETE', '03' => 'ALICANTE', '04' => 'ALMERIA', '05' => 'AVILA', '06' => 'BADAJOZ', '08' => 'BARCELONA',
		'09' => 'BURGOS', '10' => 'CACERES', '11' => 'CADIZ', '12' => 'CASTELLON', '13' => 'CIUDAD REAL', '14' => 'CORDOBA', '15' => 'CORUNA',
		'16' => 'CUENCA', '17' => 'GIRONA', '18' => 'GRANADA', '19' => 'GUADALAJARA', '20' => 'GUIPUZCOA', '21' => 'HUELVA', '22' => 'HUESCA',
		'23' => 'JAEN', '24' => 'LEON', '25' => 'LLEIDA', '26' => 'LA RIOJA', '27' => 'LUGO', '28' => 'MADRID', '29' => 'MALAGA', '30' => 'MURCIA',
		'31' => 'NAVARRA', '32' => 'ORENSE', '33' => 'ASTURIAS', '34' => 'PALENCIA', '36' => 'PONTEVEDRA', '37' => 'SALAMANCA', '39' => 'CANTABRIA',
		'40' => 'SEGOVIA', '41' => 'SEVILLA', '42' => 'SORIA', '43' => 'TARRAGONA', '44' => 'TERUEL', '45' => 'TOLEDO', '46' => 'VALENCIA',
		'47' => 'VALLADOLID', '48' => 'VIZCAYA', '49' => 'ZAMORA', '50' => 'ZARAGOZA',
	);
	return strlen( $cp ) >= 2 ? ( $mapa[ substr( $cp, 0, 2 ) ] ?? '' ) : '';
}

/**
 * Nombre de población normalizado y sus variantes: "Elche/Elx 03201" → ['elche', 'elx'];
 * "Les Franqueses Del V.Alles (Granollers)" → ['granollers', 'franqueses del v alles'];
 * "L’Hospitalet de Llobregat" y "Hospitalet De Llobregat" → ['hospitalet de llobregat'].
 */
function dip_mrw_nombres( $texto ) {
	$texto = str_replace( array( '’', '‘', '´', '`', '·' ), array( "'", "'", "'", "'", '' ), (string) $texto );
	$texto = strtolower( remove_accents( $texto ) );
	$partes = array();
	if ( preg_match_all( '/\(([^)]*)\)/', $texto, $m ) ) {
		foreach ( $m[1] as $dentro ) {
			if ( preg_match( '/[a-z]{3,}/', $dentro ) ) $partes[] = $dentro; // "(Granollers)" es otra población
		}
	}
	$texto  = preg_replace( '/\([^)]*\)/', ' ', $texto );
	$partes = array_merge( $partes, explode( '/', $texto ) );
	$salida = array();
	foreach ( $partes as $p ) {
		$p = preg_replace( '/\d+/', ' ', $p );
		$p = preg_replace( "/\b(l|d)'/", '', $p );
		$p = preg_replace( '/[^a-z ]+/', ' ', $p );
		$p = preg_replace( '/^\s*(el|la|les|els|los|las|lo|o|a|os|as)\s+/', '', $p );
		$p = trim( preg_replace( '/\s+/', ' ', $p ) );
		if ( strlen( $p ) >= 3 ) $salida[] = $p;
	}
	return array_values( array_unique( $salida ) );
}

/**
 * ¿Son la misma población? Igual, o una es el principio de la otra hasta un espacio:
 * "castello" ↔ "castello de la plana", "sant cugat" ↔ "sant cugat del valles". "barcelona" ≠ "badalona".
 */
function dip_mrw_mismo_sitio( array $a, array $b ) {
	foreach ( $a as $x ) {
		foreach ( $b as $y ) {
			if ( $x === $y ) return true;
			if ( strlen( $x ) >= 4 && strlen( $y ) >= 4 && ( 0 === strpos( $y, $x . ' ' ) || 0 === strpos( $x, $y . ' ' ) ) ) return true;
		}
	}
	return false;
}

/** Oficinas internas de MRW (logística, corporativas, burofax): no reparten a clientes, no cuentan como población. */
function dip_mrw_es_interna( $nombre ) {
	return (bool) preg_match( '/^\s*(logistica|corporate|burofax|division|tradeinn)\b/i', remove_accents( (string) $nombre ) );
}

/** Oficinas de MRW por provincia (datos/mrw-oficinas.json, herramientas/datos-mrw.mjs). */
function dip_mrw_oficinas() {
	static $datos = null;
	if ( null !== $datos ) return $datos;
	$archivo = DIP_TIENDA_DIR . 'datos/mrw-oficinas.json';
	$json    = file_exists( $archivo ) ? json_decode( (string) file_get_contents( $archivo ), true ) : null;
	$datos   = array();
	foreach ( (array) ( $json['provincias'] ?? array() ) as $provincia => $info ) {
		$datos[ dip_mrw_clave_provincia( $provincia ) ] = $info;
	}
	return $datos;
}

/** ¿Está cerrada casi toda la provincia? (festivo nacional, autonómico o provincial) */
function dip_mrw_provincia_cerrada( $cerradas_en_provincia, $provincia ) {
	$oficinas = (int) ( dip_mrw_oficinas()[ $provincia ]['oficinas'] ?? 0 );
	return $oficinas > 0 && $cerradas_en_provincia / $oficinas >= DIP_MRW_PROVINCIA;
}

/** Provincias cerradas enteras en una lista de oficinas cerradas [[provincia, nombre], …]. */
function dip_mrw_provincias_cerradas( array $cerradas ) {
	$cuenta = array();
	foreach ( $cerradas as $c ) {
		$p = (string) ( $c[0] ?? '' );
		if ( '' !== $p ) $cuenta[ $p ] = ( $cuenta[ $p ] ?? 0 ) + 1;
	}
	$salida = array();
	foreach ( $cuenta as $provincia => $n ) {
		if ( dip_mrw_provincia_cerrada( $n, $provincia ) ) $salida[] = $provincia;
	}
	sort( $salida );
	return $salida;
}

/* =========================================================
 * Consulta a MRW
 * ========================================================= */

/** Texto de la página de MRW limpio y en UTF-8 (la página mezcla UTF-8 y Latin-1). */
function dip_mrw_texto( $texto ) {
	$texto = html_entity_decode( (string) $texto, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $texto, 'UTF-8' ) ) $texto = mb_convert_encoding( $texto, 'UTF-8', 'Windows-1252' );
	$limpio = preg_replace( '/\s+/u', ' ', $texto );
	return trim( null === $limpio ? $texto : $limpio );
}

/**
 * Lee la página de festividades de un día. Devuelve ['cerradas' => [[PROVINCIA, nombre], …], 'oficinas' => n] o WP_Error.
 * Cada oficina es <div class="loc loc-festividades is-cerrada|is-abierta"> … loc-title"><a …>NOMBRE</a> … <strong>Provincia</strong>: PROVINCIA
 */
function dip_mrw_analizar( $html, $ymd = '' ) {
	$html = (string) $html;
	if ( false === strpos( $html, 'festividadesTb' ) ) {
		return new WP_Error( 'dip_mrw_formato', 'La página de MRW ha cambiado: no aparece el calendario de festividades.' );
	}
	// MRW marca el día pedido con "is-sel" si cae en el mes que enseña: si marca otro, no ha hecho caso de la fecha
	if ( $ymd && preg_match( '#class="[^"]*\bis-sel\b[^"]*"[^>]*>\s*<a[^>]*\bdia=(\d{1,2})&(?:amp;)?mes=(\d{1,2})&(?:amp;)?any=(\d{4})#', $html, $sel ) ) {
		$marcado = sprintf( '%04d-%02d-%02d', $sel[3], $sel[2], $sel[1] );
		if ( $marcado !== $ymd ) return new WP_Error( 'dip_mrw_dia', "MRW ha devuelto el día {$marcado} en lugar del {$ymd}." );
	}
	$bloques = explode( 'loc-festividades is-', $html );
	array_shift( $bloques );
	$cerradas = array();
	$malos    = 0;
	foreach ( $bloques as $b ) {
		if ( ! preg_match( '/^([a-z]+)/', $b, $estado )
			|| ! preg_match( '#loc-title[^>]*>\s*<a[^>]*>([^<]*)</a>#', $b, $nombre )
			|| ! preg_match( '#Provincia\s*</strong>\s*:\s*([^<\r\n]*)#', $b, $provincia ) ) {
			$malos++;
			continue;
		}
		if ( 'cerrada' !== $estado[1] ) continue;
		$cerradas[] = array( dip_mrw_clave_provincia( dip_mrw_texto( $provincia[1] ) ), dip_mrw_texto( $nombre[1] ) );
	}
	if ( $malos && $malos * 2 >= count( $bloques ) ) {
		return new WP_Error( 'dip_mrw_formato', 'La página de MRW ha cambiado: no se entiende la lista de oficinas.' );
	}
	return array( 'cerradas' => $cerradas, 'oficinas' => count( $bloques ) - $malos );
}

/** Lee un día del calendario de MRW. Devuelve lo mismo que dip_mrw_analizar(). Solo lo llama dip_mrw_actualizar(). */
function dip_mrw_leer_dia( DateTimeInterface $dia ) {
	$url = add_query_arg( array( 'dia' => $dia->format( 'd' ), 'mes' => $dia->format( 'm' ), 'any' => $dia->format( 'Y' ) ), DIP_MRW_URL );
	$r   = wp_remote_get( $url, array(
		'timeout'             => 15,
		'redirection'         => 3,
		'limit_response_size' => 3 * 1024 * 1024,
		'user-agent'          => 'DryIcePack/1.0 (+https://dryicepack.es; info@dryicepack.es)',
	) );
	if ( is_wp_error( $r ) ) return new WP_Error( 'dip_mrw_red', 'No se puede conectar con MRW: ' . $r->get_error_message() );
	$codigo = (int) wp_remote_retrieve_response_code( $r );
	if ( 200 !== $codigo ) return new WP_Error( 'dip_mrw_http', 'MRW respondió con el código ' . $codigo . '.' );
	return dip_mrw_analizar( wp_remote_retrieve_body( $r ), $dia->format( 'Y-m-d' ) );
}

/** La opción 'dip_mrw' tal como está ahora en la base de datos (no la copia que esta petición leyó al empezar). */
function dip_mrw_estado_actual() {
	if ( function_exists( 'wp_cache_delete' ) ) wp_cache_delete( 'dip_mrw', 'options' );
	return (array) get_option( 'dip_mrw', array() );
}

/**
 * Actualiza los próximos 21 días. La llama WP-Cron dos veces al día (y el botón "Comprobar ahora").
 * Como mucho DIP_MRW_LIMITE_S segundos (más la última consulta, 15 s como máximo): si MRW va lento, se guardan
 * los días leídos hasta entonces y cuenta como fallo (dos seguidos mandan el email de aviso).
 */
function dip_mrw_actualizar() {
	if ( get_transient( 'dip_mrw_bloqueo' ) ) return new WP_Error( 'dip_mrw_ocupado', 'Ya hay una comprobación en marcha. Prueba otra vez en unos minutos.' );
	set_transient( 'dip_mrw_bloqueo', 1, 5 * MINUTE_IN_SECONDS );
	if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 180 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- algunos hostings lo desactivan

	$hoy    = new DateTimeImmutable( 'today', dip_zona_horaria() );
	$inicio = microtime( true );
	$nuevos = array();
	$error  = '';
	for ( $i = 0; $i < DIP_MRW_DIAS; $i++ ) {
		if ( microtime( true ) - $inicio >= DIP_MRW_LIMITE_S ) {
			$leidos = count( $nuevos );
			$error  = sprintf( 'MRW responde despacio: solo se han leído %d %s de %d en %d segundos.', $leidos, 1 === $leidos ? 'día' : 'días', DIP_MRW_DIAS, (int) DIP_MRW_LIMITE_S );
			break;
		}
		$dia = $hoy->modify( "+{$i} day" );
		$res = dip_mrw_leer_dia( $dia );
		if ( is_wp_error( $res ) ) {
			$error = $res->get_error_message();
			break;
		}
		$nuevos[ $dia->format( 'Y-m-d' ) ] = $res['cerradas'];
		if ( DIP_MRW_PAUSA_MS > 0 && $i < DIP_MRW_DIAS - 1 ) usleep( DIP_MRW_PAUSA_MS * 1000 ); // sin prisa: 21 consultas en unos segundos
	}
	// Comprobación de sentido común: en tres semanas siempre hay algún festivo local en algún sitio
	if ( ! $error && 0 === array_sum( array_map( 'count', $nuevos ) ) ) {
		$error  = 'MRW no muestra ningún festivo en 21 días: puede que la página haya cambiado.';
		$nuevos = array(); // mejor conservar lo que había que guardar 21 días vacíos
	}
	// La opción se lee justo antes de guardar, no al empezar: la consulta tarda y, mientras, el aviso del escritorio
	// (dip_mrw_vigilar) puede haber anotado un email. Si se pisara, el email se repetiría.
	$estado = dip_mrw_estado_actual();
	// Los días leídos sustituyen a los anteriores; si falla a medias, se conservan los días que ya se tenían
	$dias = array_merge( (array) ( $estado['dias'] ?? array() ), $nuevos );
	$desde_hoy = $hoy->format( 'Y-m-d' );
	foreach ( array_keys( $dias ) as $f ) {
		if ( $f < $desde_hoy ) unset( $dias[ $f ] );
	}
	ksort( $dias );

	$estado['dias']       = $dias;
	$estado['comprobado'] = time();
	if ( empty( $estado['desde'] ) ) $estado['desde'] = time();
	if ( $error ) {
		$estado['fallos'] = (int) ( $estado['fallos'] ?? 0 ) + 1;
		$estado['error']  = $error;
	} else {
		$estado['fallos'] = 0;
		$estado['error']  = '';
		$estado['ok']     = time();
	}
	update_option( 'dip_mrw', $estado, false );
	delete_transient( 'dip_mrw_bloqueo' );
	dip_mrw_vigilar();
	return $error ? new WP_Error( 'dip_mrw', $error ) : count( $nuevos );
}

add_action( 'dip_mrw_actualizar', 'dip_mrw_actualizar' );

/**
 * Una sola tarea de WP-Cron programada para $tarea ($cada: 'twicedaily', 'daily'…; $primera: hora de la primera vez).
 * Si dos visitas a la vez la programan dos veces, o quedó repetida de antes, se deja la primera y se borran las demás.
 * También la usa la revisión diaria de pedidos reservados (inc/seguimiento-entregas.php).
 */
function dip_cron_programar_unico( $tarea, $cada, $primera ) {
	$eventos = array(); // [[hora, argumentos], …]
	if ( function_exists( '_get_cron_array' ) ) {
		foreach ( (array) _get_cron_array() as $hora => $tareas ) {
			foreach ( (array) ( $tareas[ $tarea ] ?? array() ) as $evento ) {
				$eventos[] = array( (int) $hora, (array) ( $evento['args'] ?? array() ) );
			}
		}
	}
	if ( count( $eventos ) > 1 ) {
		usort( $eventos, static fn( $a, $b ) => $a[0] <=> $b[0] );
		foreach ( array_slice( $eventos, 1 ) as $e ) wp_unschedule_event( $e[0], $tarea, $e[1] );
		return;
	}
	if ( ! $eventos && ! wp_next_scheduled( $tarea ) ) wp_schedule_event( (int) $primera, $cada, $tarea );
}

/** Una sola comprobación de MRW programada (dos veces al día). */
function dip_mrw_programar() {
	dip_cron_programar_unico( 'dip_mrw_actualizar', 'twicedaily', time() + 60 );
}
add_action( 'init', 'dip_mrw_programar' );

/* =========================================================
 * Avisos: email al correo de avisos y aviso en el escritorio
 * ========================================================= */

/** ¿Hay un problema? Devuelve el texto del problema o ''. */
function dip_mrw_problema() {
	$estado = (array) get_option( 'dip_mrw', array() );
	$ok     = (int) ( $estado['ok'] ?? 0 );
	$fallos = (int) ( $estado['fallos'] ?? 0 );
	$desde  = (int) ( $estado['desde'] ?? 0 );
	$limite = DIP_MRW_CADUCA * HOUR_IN_SECONDS;
	if ( $fallos >= 2 ) return (string) ( $estado['error'] ?? '' ) ?: 'La consulta a MRW ha fallado.';
	if ( $ok && time() - $ok > $limite ) {
		return sprintf( 'La última comprobación correcta fue hace %d horas: puede que WP-Cron no se esté ejecutando.', (int) floor( ( time() - $ok ) / HOUR_IN_SECONDS ) );
	}
	if ( ! $ok && $desde && time() - $desde > $limite ) return 'Todavía no se ha podido comprobar nunca: puede que WP-Cron no se esté ejecutando.';
	return '';
}

function dip_mrw_vigilar() {
	$estado   = (array) get_option( 'dip_mrw', array() );
	$problema = dip_mrw_problema();
	$avisado  = (int) ( $estado['avisado'] ?? 0 );
	$destino  = (string) dip_ajuste( 'email_avisos' );
	$ajustes  = admin_url( 'admin.php?page=dryicepack' );
	$cambios  = array();
	if ( empty( $estado['desde'] ) ) {
		$cambios['desde'] = time(); // desde cuándo se espera tener datos (si WP-Cron no corre nunca, también se avisa)
	}
	if ( $problema && time() - $avisado > DAY_IN_SECONDS ) {
		$enviado = wp_mail(
			$destino,
			'Dryicepack: no se pueden comprobar los festivos de MRW',
			"La web no ha podido leer el calendario de festivos de MRW.\n\nMotivo: {$problema}\n\n"
			. "Mientras tanto, la web sigue funcionando con la lista manual de festivos (Dryicepack → Ajustes → Festivos sin salida ni entrega). "
			. "Lo que no puede saber sola son los festivos locales de Mataró que no estén en la lista y los del destino.\n\n"
			. "Qué hacer:\n1. Mira los próximos días en " . DIP_MRW_URL . "\n2. Si hay festivos que afecten a vuestras salidas (Mataró, Cataluña o toda España), añádelos a mano en {$ajustes}\n"
			. "3. Pulsa \"Comprobar ahora\" en esa misma pantalla. Si sigue fallando, avisa a quien mantiene la web: puede que MRW haya cambiado su página.\n\n"
			. "Este aviso se repite una vez al día mientras dure el problema."
		);
		if ( $enviado ) $cambios['avisado'] = time();
	} elseif ( ! $problema && $avisado ) {
		// Solo se da por avisado si el email sale: si falla, se reintenta en la próxima comprobación
		if ( wp_mail( $destino, 'Dryicepack: los festivos de MRW vuelven a comprobarse', "La comprobación automática de festivos de MRW vuelve a funcionar. No hay que hacer nada.\n\n{$ajustes}" ) ) {
			$cambios['avisado'] = 0;
		}
	}
	// Se guardan solo estos campos, sobre la opción tal como está ahora (el email puede tardar unos segundos)
	if ( $cambios ) update_option( 'dip_mrw', array_merge( dip_mrw_estado_actual(), $cambios ), false );
}
// Si WP-Cron deja de ejecutarse (sin visitas), el aviso sale igualmente al entrar al escritorio
add_action( 'admin_init', static function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_woocommerce' ) ) return;
	if ( ! get_transient( 'dip_mrw_vigilado' ) ) {
		set_transient( 'dip_mrw_vigilado', 1, HOUR_IN_SECONDS );
		dip_mrw_vigilar();
	}
} );
add_action( 'admin_notices', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$problema = dip_mrw_problema();
	if ( ! $problema ) return;
	printf(
		'<div class="notice notice-error"><p><strong>Festivos de MRW sin comprobar.</strong> %s La web usa la lista manual de festivos mientras tanto. <a href="%s">Ver y comprobar ahora</a></p></div>',
		esc_html( $problema ),
		esc_url( admin_url( 'admin.php?page=dryicepack' ) )
	);
} );

/* =========================================================
 * Uso en las fechas (solo lee la opción guardada: nunca llama a MRW)
 * ========================================================= */

/**
 * Días consultados: ['Y-m-d' => [[PROVINCIA, nombre], …], …]. Se leen una vez por petición;
 * $recargar = true los vuelve a leer de la base de datos (la revisión diaria de pedidos, que puede ir
 * en la misma petición de WP-Cron que la consulta a MRW).
 */
function dip_mrw_dias( $recargar = false ) {
	static $dias = null;
	if ( null === $dias || $recargar ) {
		$dias = (array) ( ( $recargar ? dip_mrw_estado_actual() : (array) get_option( 'dip_mrw', array() ) )['dias'] ?? array() );
		$GLOBALS['dip_mrw_version'] = (int) ( $GLOBALS['dip_mrw_version'] ?? 0 ) + 1; // invalida la memoria de dip_mrw_cierra()
	}
	return $dias;
}

/** Oficinas cerradas un día (null si ese día no se ha consultado). */
function dip_mrw_cerradas( $ymd ) {
	$dias = dip_mrw_dias();
	return array_key_exists( $ymd, $dias ) ? (array) $dias[ $ymd ] : null;
}

/** ¿Cierra MRW ese día en esa población (o en casi toda su provincia)? Sin datos de ese día: no. */
function dip_mrw_cierra( $ymd, $provincia, $poblacion = '' ) {
	static $memo = array();
	dip_mrw_dias(); // antes de la clave: la versión de los datos forma parte de ella
	$provincia = dip_mrw_clave_provincia( $provincia );
	$clave     = (int) ( $GLOBALS['dip_mrw_version'] ?? 0 ) . '|' . $ymd . '|' . $provincia . '|' . $poblacion;
	if ( isset( $memo[ $clave ] ) ) return $memo[ $clave ];
	$memo[ $clave ] = false;

	$cerradas = dip_mrw_cerradas( $ymd );
	if ( ! $cerradas || '' === $provincia ) return false;
	$en_prov = array_values( array_filter( $cerradas, static fn( $c ) => ( $c[0] ?? '' ) === $provincia ) );
	if ( ! $en_prov ) return false;
	// Casi toda la provincia cerrada: festivo nacional, autonómico o provincial
	if ( dip_mrw_provincia_cerrada( count( $en_prov ), $provincia ) ) return $memo[ $clave ] = true;
	// La oficina de la población del cliente
	$buscadas = dip_mrw_nombres( $poblacion );
	if ( ! $buscadas ) return false;
	foreach ( $en_prov as $c ) {
		if ( dip_mrw_es_interna( $c[1] ?? '' ) ) continue;
		if ( dip_mrw_mismo_sitio( $buscadas, dip_mrw_nombres( $c[1] ?? '' ) ) ) return $memo[ $clave ] = true;
	}
	return false;
}

/** Salida desde Mataró: MRW no recoge el día que cierra su oficina de Mataró. */
function dip_mrw_cierra_salida( $ymd ) {
	return dip_mrw_cierra( $ymd, 'BARCELONA', 'Mataró' );
}

/** Destino de la sesión del checkout (código postal y población), si se conoce. */
function dip_destino_actual() {
	if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->customer ) return array( 'cp' => '', 'poblacion' => '' );
	$c = WC()->customer;
	return array(
		'cp'        => (string) ( $c->get_shipping_postcode() ?: $c->get_billing_postcode() ),
		'poblacion' => (string) ( $c->get_shipping_city() ?: $c->get_billing_city() ),
	);
}

/* =========================================================
 * Pantalla de estado (Dryicepack → Ajustes)
 * ========================================================= */

function dip_mrw_html_estado() {
	$estado    = (array) get_option( 'dip_mrw', array() );
	$dias      = (array) ( $estado['dias'] ?? array() );
	$ok        = (int) ( $estado['ok'] ?? 0 );
	$prob      = dip_mrw_problema();
	$boton     = wp_nonce_url( admin_url( 'admin.php?page=dryicepack&dip_mrw=comprobar' ), 'dip_mrw' );
	$siguiente = wp_next_scheduled( 'dip_mrw_actualizar' );
	echo '<h2 id="dip-mrw">Festivos de MRW (automático)</h2>';
	echo '<p class="description">Dos veces al día la web consulta en mrw.es qué oficinas cierran los próximos ' . (int) DIP_MRW_DIAS . ' días. '
		. 'No hay salida si cierra MRW Mataró y no se ofrece la entrega si cierra la oficina de la población del cliente o casi toda su provincia. '
		. 'Los días que no se han podido consultar se rigen solo por la lista manual de arriba.</p>';
	echo '<p>' . esc_html( $ok ? 'Última comprobación correcta: ' . wp_date( 'j/n/Y H:i', $ok ) . '. Días consultados: ' . count( $dias ) . '.' : 'Todavía no se ha comprobado.' );
	if ( $siguiente ) echo ' ' . esc_html( 'Próxima: ' . wp_date( 'j/n/Y H:i', (int) $siguiente ) . '.' );
	if ( $prob ) echo ' <strong style="color:#b32d2e">' . esc_html( $prob ) . '</strong>';
	echo ' <a class="button" href="' . esc_url( $boton ) . '">Comprobar ahora</a></p>';
	// Días con cierres, para revisar de un vistazo
	$filas = array();
	foreach ( $dias as $f => $cerradas ) {
		$cerradas = (array) $cerradas;
		if ( ! $cerradas ) continue;
		$filas[] = array( $f, count( $cerradas ), dip_mrw_provincias_cerradas( $cerradas ), dip_mrw_cierra_salida( $f ) );
	}
	if ( $filas ) {
		echo '<table class="widefat striped" style="max-width:820px"><thead><tr><th>Día</th><th>Oficinas de MRW cerradas</th><th>Provincias enteras</th><th>Salida desde Mataró</th></tr></thead><tbody>';
		foreach ( $filas as $x ) {
			printf(
				'<tr><td>%s</td><td>%d</td><td>%s</td><td>%s</td></tr>',
				esc_html( dip_fecha_larga( $x[0] ) ),
				(int) $x[1],
				esc_html( $x[2] ? implode( ', ', $x[2] ) : '—' ),
				$x[3] ? '<strong>No (cierra MRW Mataró)</strong>' : 'Sí'
			);
		}
		echo '</tbody></table>';
	}
	// Revisión diaria de los pedidos reservados con antelación (inc/seguimiento-entregas.php)
	if ( function_exists( 'dip_seg_html_estado' ) ) dip_seg_html_estado();
}

add_action( 'admin_init', static function () {
	if ( ! isset( $_GET['dip_mrw'] ) || 'comprobar' !== $_GET['dip_mrw'] || ! current_user_can( 'manage_woocommerce' ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- se verifica abajo
	check_admin_referer( 'dip_mrw' );
	$r     = dip_mrw_actualizar();
	$hecho = is_wp_error( $r ) ? ( 'dip_mrw_ocupado' === $r->get_error_code() ? 'ocupado' : 'error' ) : 'ok';
	wp_safe_redirect( add_query_arg( 'dip_mrw_hecho', $hecho, admin_url( 'admin.php?page=dryicepack' ) ) . '#dip-mrw' );
	exit;
} );
