<?php
/**
 * Pedidos reservados con antelación y festivos de MRW que se conocen tarde.
 *
 * El checkout deja reservar cualquier día válido de los próximos 90 (inc/entrega.php), pero MRW solo publica sus
 * cierres por festivo 21 días antes (inc/festivos-mrw.php). Una vez al día (WP-Cron, hacia las 9:30 de Madrid) y sin
 * ninguna petición a internet (lee la opción 'dip_mrw' ya guardada y la lista manual de festivos), se repasan los
 * pedidos pendientes de entregar (Procesando y En espera) que van por mensajería. Si el día elegido ha resultado ser:
 *  - un día sin salida desde Mataró (el día anterior cierra MRW Mataró o está en la lista manual de festivos), o
 *  - un día sin reparto en el destino (cierra la oficina de MRW de la población del pedido o casi toda su provincia,
 *    o está en la lista manual),
 * se deja una nota en el pedido y se envía un solo email al correo de avisos con todos los pedidos afectados.
 * Un aviso por pedido y fecha: no se repite cada día. Si se cambia el día del pedido, el día nuevo se vuelve a revisar.
 * Solo cuentan los días que MRW ya ha publicado: más allá no se bloquea nada (igual que en el checkout).
 *
 * Alternativa manual (la explica el email): llamar al cliente, acordar otro día y cambiarlo en el pedido, en el campo
 * «Cambiar el día» que este archivo añade a la pantalla del pedido. Botón «Revisar pedidos ahora» en Dryicepack → Ajustes.
 * Si la revisión falla, si el email de aviso no sale o si lleva más de 48 h sin hacerse (WP-Cron parado): aviso en el
 * escritorio y email al correo de avisos (una vez al día mientras dure; otro cuando se arregla).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_SEG_HORA' ) )        define( 'DIP_SEG_HORA', '09:30' ); // hora de Madrid de la revisión diaria
if ( ! defined( 'DIP_SEG_CADUCA' ) )      define( 'DIP_SEG_CADUCA', 48 );    // horas sin revisión antes de avisar
if ( ! defined( 'DIP_SEG_DIAS_PEDIDO' ) ) define( 'DIP_SEG_DIAS_PEDIDO', 180 ); // antigüedad máxima de los pedidos que se miran

/* =========================================================
 * Programación (WP-Cron, una vez al día)
 * ========================================================= */

/** Hora de la primera revisión: hoy a las 9:30 de Madrid si aún no ha pasado; si no, mañana. */
function dip_seg_primera_hora( ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	list( $h, $m ) = array_map( 'intval', explode( ':', DIP_SEG_HORA ) );
	$hora = $ahora->setTime( $h, $m );
	return ( $hora <= $ahora ? $hora->modify( '+1 day' ) : $hora )->getTimestamp();
}

function dip_seg_programar() {
	dip_cron_programar_unico( 'dip_revisar_entregas', 'daily', dip_seg_primera_hora() );
}
add_action( 'init', 'dip_seg_programar' );
add_action( 'dip_revisar_entregas', 'dip_seg_revisar' );

/* =========================================================
 * Qué falla en un día de entrega
 * ========================================================= */

/** "jueves 22 de octubre", con el aviso del suplemento si es sábado. */
function dip_seg_dia( $ymd ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() );
	return dip_fecha_larga( $ymd ) . ( $d && 6 === (int) $d->format( 'N' ) ? ' (con suplemento)' : '' );
}

/** Población del destino o, si no la hay, su provincia. */
function dip_seg_lugar( array $norm ) {
	if ( '' !== (string) $norm['poblacion'] ) return (string) $norm['poblacion'];
	$wc      = function_exists( 'WC' ) ? WC() : null;
	$estados = $wc && isset( $wc->countries ) && $wc->countries ? (array) $wc->countries->get_states( 'ES' ) : array();
	return (string) ( $estados[ dip_provincia_por_cp( $norm['cp'] ) ] ?? '' ) ?: ucwords( strtolower( (string) $norm['provincia'] ) );
}

/**
 * Por qué un envío no puede llegar el día $fecha (Y-m-d) a $destino (['cp', 'poblacion']). Sale de Mataró el día anterior.
 * Solo con lo que ya se sabe: festivos de MRW guardados y lista manual. Sin datos de MRW de ese día, no hay motivo.
 *
 * @return array[] [ ['tipo' => 'salida'|'destino', 'texto' => …], … ] o [] si no hay problema.
 */
function dip_seg_motivos( $fecha, $destino, ?DateTimeImmutable $ahora = null ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $fecha, dip_zona_horaria() );
	if ( ! $d ) return array();
	$hoy     = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() )->format( 'Y-m-d' );
	$sale    = $d->modify( '-1 day' );
	$s       = $sale->format( 'Y-m-d' );
	$motivos = array();
	if ( $s >= $hoy ) {
		if ( dip_mrw_cierra_salida( $s ) ) {
			$motivos[] = array( 'tipo' => 'salida', 'texto' => sprintf( 'El %s cierra MRW Mataró: ese día no sale nada de Mataró.', dip_fecha_larga( $s ) ) );
		} elseif ( dip_es_festivo( $sale ) ) {
			$motivos[] = array( 'tipo' => 'salida', 'texto' => sprintf( 'El %s está en la lista de festivos de Dryicepack → Ajustes: ese día no sale nada de Mataró.', dip_fecha_larga( $s ) ) );
		}
	}
	$norm = dip_destino_normalizado( $destino );
	if ( $norm && dip_mrw_cierra( $fecha, $norm['provincia'], $norm['poblacion'] ) ) {
		$motivos[] = array( 'tipo' => 'destino', 'texto' => sprintf( 'MRW no reparte el %s en %s: cierra por festivo.', dip_fecha_larga( $fecha ), dip_seg_lugar( $norm ) ) );
	} elseif ( dip_es_festivo( $d ) ) {
		$motivos[] = array( 'tipo' => 'destino', 'texto' => sprintf( 'El %s está en la lista de festivos de Dryicepack → Ajustes: ese día no se entrega.', dip_fecha_larga( $fecha ) ) );
	}
	return $motivos;
}

/** Días con reparto más cercanos a $fecha para ese destino, hoy: ['antes' => 'Y-m-d' o '', 'despues' => 'Y-m-d' o '']. */
function dip_seg_alternativas( $fecha, $destino, ?DateTimeImmutable $ahora = null ) {
	$ahora   = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$fechas  = array_column( dip_calcular_fechas( 'envio', $ahora, dip_destino_normalizado( $destino ) )['fechas'], 'fecha' ); // sin memoria: datos recién leídos
	$antes   = '';
	$despues = '';
	foreach ( $fechas as $f ) {
		if ( $f < $fecha ) {
			$antes = $f;
		} elseif ( $f > $fecha ) {
			$despues = $f;
			break;
		}
	}
	return array( 'antes' => $antes, 'despues' => $despues );
}

function dip_seg_texto_alternativas( array $alt ) {
	$dias = array_filter( array( $alt['antes'] ? dip_seg_dia( $alt['antes'] ) : '', $alt['despues'] ? dip_seg_dia( $alt['despues'] ) : '' ) );
	return $dias ? implode( ' o ', $dias ) : 'ninguno en los próximos 90 días';
}

/* =========================================================
 * Avisos ya dados (en el pedido) y estado de la revisión (opción)
 * ========================================================= */

/** Avisos de un pedido: ['Y-m-d' => ['nota' => hora, 'email' => hora, 'motivo' => texto], …] */
function dip_seg_avisos_pedido( WC_Order $pedido ) {
	$h = $pedido->get_meta( '_dip_aviso_entrega' );
	return is_array( $h ) ? $h : array();
}

function dip_seg_guardar_avisos_pedido( WC_Order $pedido, array $avisos, $hoy ) {
	foreach ( array_keys( $avisos ) as $f ) {
		if ( (string) $f < $hoy ) unset( $avisos[ $f ] ); // días ya pasados: no hacen falta
	}
	$pedido->update_meta_data( '_dip_aviso_entrega', $avisos );
	$pedido->save_meta_data();
}

/** Estado de la revisión. $fresco = true: tal como está ahora en la base de datos (para guardar encima sin pisar nada). */
function dip_seg_estado( $fresco = false ) {
	if ( $fresco && function_exists( 'wp_cache_delete' ) ) wp_cache_delete( 'dip_seguimiento', 'options' );
	return (array) get_option( 'dip_seguimiento', array() );
}

function dip_seg_guardar_estado( array $cambios ) {
	$estado = dip_seg_estado( true );
	if ( empty( $estado['desde'] ) ) $estado['desde'] = time(); // desde cuándo se espera que funcione (si WP-Cron no corre nunca, también se avisa)
	update_option( 'dip_seguimiento', array_merge( $estado, $cambios ), false );
}

/* =========================================================
 * Revisión diaria
 * ========================================================= */

/**
 * Repasa los pedidos pendientes. La llama WP-Cron una vez al día (y el botón «Revisar pedidos ahora»).
 * Devuelve ['revisados' => n, 'avisos' => n, 'sin_enviar' => n] o WP_Error. Nunca llama a MRW.
 */
function dip_seg_revisar( $ahora = null ) {
	$ahora = ( $ahora instanceof DateTimeImmutable ? $ahora : new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	if ( get_transient( 'dip_seg_bloqueo' ) ) return new WP_Error( 'dip_seg_ocupado', 'Ya hay una revisión en marcha. Prueba otra vez en unos minutos.' );
	set_transient( 'dip_seg_bloqueo', 1, 10 * MINUTE_IN_SECONDS );
	try {
		$r       = dip_seg_revisar_pedidos( $ahora );
		$cambios = array( 'ok' => time(), 'error' => '', 'revisados' => $r['revisados'], 'avisos' => $r['avisos'], 'sin_enviar' => $r['sin_enviar'] );
	} catch ( Throwable $e ) {
		$r       = new WP_Error( 'dip_seg', 'La revisión de pedidos ha fallado: ' . $e->getMessage() );
		$cambios = array( 'error' => $r->get_error_message(), 'fallo' => time() );
	}
	dip_seg_guardar_estado( $cambios );
	delete_transient( 'dip_seg_bloqueo' );
	dip_seg_vigilar();
	return $r;
}

/** El trabajo de dip_seg_revisar(): notas en los pedidos afectados y un solo email con todos. */
function dip_seg_revisar_pedidos( DateTimeImmutable $ahora ) {
	dip_mrw_dias( true ); // los festivos de MRW tal como están ahora (pueden haberse actualizado en esta misma petición de WP-Cron)
	$hoy     = $ahora->format( 'Y-m-d' );
	$pedidos = wc_get_orders( array(
		'limit'        => -1,
		'type'         => 'shop_order',
		'status'       => array( 'wc-processing', 'wc-on-hold' ),
		'date_created' => '>=' . $ahora->modify( '-' . (int) DIP_SEG_DIAS_PEDIDO . ' day' )->format( 'Y-m-d' ),
	) );
	$nuevos    = array();
	$revisados = 0;
	foreach ( (array) $pedidos as $pedido ) {
		if ( ! $pedido instanceof WC_Order || dip_es_pedido_de_recogida( $pedido ) ) continue; // la recogida no depende de MRW
		$fecha = (string) $pedido->get_meta( '_dip_fecha_entrega' );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $fecha ) || $fecha < $hoy ) continue;
		$revisados++;
		$hechos = dip_seg_avisos_pedido( $pedido );
		if ( ! empty( $hechos[ $fecha ]['email'] ) ) continue; // ya avisado de este pedido y esta fecha
		$destino = dip_destino_de_pedido( $pedido );
		$motivos = dip_seg_motivos( $fecha, $destino, $ahora );
		if ( ! $motivos ) continue;
		$nuevos[] = array(
			'pedido'       => $pedido,
			'fecha'        => $fecha,
			'destino'      => $destino,
			'motivo'       => implode( ' ', array_column( $motivos, 'texto' ) ),
			'alternativas' => dip_seg_alternativas( $fecha, $destino, $ahora ),
		);
	}
	if ( ! $nuevos ) {
		dip_seg_guardar_estado( array( 'pendientes' => array() ) );
		return array( 'revisados' => $revisados, 'avisos' => 0, 'sin_enviar' => 0 );
	}

	$correo  = (string) dip_ajuste( 'email_avisos' );
	$email   = dip_seg_email( $nuevos );
	$enviado = '' !== $correo && wp_mail( $correo, $email[0], $email[1] );
	foreach ( $nuevos as $a ) {
		$p      = $a['pedido'];
		$hechos = dip_seg_avisos_pedido( $p );
		$h      = (array) ( $hechos[ $a['fecha'] ] ?? array() );
		if ( empty( $h['nota'] ) ) {
			// La nota se deja una sola vez, aunque el email falle y se reintente al día siguiente
			$p->add_order_note( 'Aviso de entrega: ' . $a['motivo'] . ' Días con reparto más cercanos: ' . dip_seg_texto_alternativas( $a['alternativas'] ) . '. '
				. 'Llama al cliente, acordad otro día y cámbialo en este pedido («Cambiar el día»). '
				. ( $enviado ? 'Aviso enviado a ' . $correo . '.' : 'No se ha podido enviar el email de aviso: se reintenta en la próxima revisión.' ) );
			$h['nota']   = time();
			$h['motivo'] = $a['motivo'];
		}
		if ( $enviado ) $h['email'] = time();
		$hechos[ $a['fecha'] ] = $h;
		dip_seg_guardar_avisos_pedido( $p, $hechos, $hoy );
	}
	dip_seg_guardar_estado( array( 'pendientes' => $enviado ? array() : array_map( static fn( $a ) => (string) $a['pedido']->get_order_number(), $nuevos ) ) );
	return array( 'revisados' => $revisados, 'avisos' => $enviado ? count( $nuevos ) : 0, 'sin_enviar' => $enviado ? 0 : count( $nuevos ) );
}

/** Asunto y texto del email al correo de avisos con los pedidos afectados. */
function dip_seg_email( array $avisos ) {
	$n      = count( $avisos );
	$asunto = 1 === $n
		? 'Dryicepack: cambiar el día de entrega del pedido ' . $avisos[0]['pedido']->get_order_number() . ' (festivo)'
		: 'Dryicepack: cambiar el día de entrega de ' . $n . ' pedidos (festivos)';
	$texto = ( 1 === $n ? 'Este pedido se reservó' : 'Estos pedidos se reservaron' )
		. ' antes de que MRW publicara sus festivos (los publica 21 días antes) y, tal como ' . ( 1 === $n ? 'está, no llegará' : 'están, no llegarán' ) . " el día elegido.\n";
	foreach ( $avisos as $a ) {
		$p      = $a['pedido'];
		$nombre = trim( $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() ) ?: trim( (string) $p->get_billing_company() );
		$lugar  = trim( (string) ( $a['destino']['cp'] ?? '' ) . ' ' . (string) ( $a['destino']['poblacion'] ?? '' ) );
		$sale   = ( new DateTimeImmutable( $a['fecha'], dip_zona_horaria() ) )->modify( '-1 day' )->format( 'Y-m-d' );
		$texto .= "\nPedido " . $p->get_order_number() . ( $nombre ? ' · ' . $nombre : '' ) . ( $lugar ? ' · ' . $lugar : '' ) . "\n"
			. 'Entrega elegida: ' . dip_seg_dia( $a['fecha'] ) . ' (sale de Mataró el ' . dip_fecha_larga( $sale ) . ")\n"
			. 'Motivo: ' . $a['motivo'] . "\n"
			. 'Días con reparto más cercanos: ' . dip_seg_texto_alternativas( $a['alternativas'] ) . "\n"
			. 'Teléfono: ' . ( $p->get_billing_phone() ?: '—' ) . ' · Email: ' . ( $p->get_billing_email() ?: '—' ) . "\n"
			. 'Abrir el pedido: ' . $p->get_edit_order_url() . "\n";
	}
	$texto .= "\nQué hacer (a mano):\n"
		. "1. Llama al cliente (o escríbele por WhatsApp), explícale que ese día no se puede entregar y acordad otro día.\n"
		. "2. Abre el pedido, elige el día nuevo en «Cambiar el día» (debajo de la dirección de envío) y pulsa «Actualizar». El cambio queda anotado en el pedido.\n"
		. "3. Cambiar el día no avisa al cliente ni cambia el importe: si el día nuevo es sábado (o deja de serlo), el suplemento de sábado no se ajusta solo.\n\n"
		. "Este aviso sale una sola vez por pedido y día. La web revisa cada mañana los pedidos pendientes con los festivos de MRW ya guardados (Dryicepack → Ajustes).\n";
	return array( $asunto, $texto );
}

/* =========================================================
 * Si la revisión falla o no se hace: email y aviso en el escritorio
 * ========================================================= */

/** Problema de la revisión (falla o no se hace). '' si todo va bien. */
function dip_seg_problema() {
	$e      = dip_seg_estado();
	$ok     = (int) ( $e['ok'] ?? 0 );
	$desde  = (int) ( $e['desde'] ?? 0 );
	$limite = DIP_SEG_CADUCA * HOUR_IN_SECONDS;
	if ( ! empty( $e['error'] ) ) return (string) $e['error'];
	if ( $ok && time() - $ok > $limite ) {
		return sprintf( 'La última revisión fue hace %d horas: puede que WP-Cron no se esté ejecutando.', (int) floor( ( time() - $ok ) / HOUR_IN_SECONDS ) );
	}
	if ( ! $ok && $desde && time() - $desde > $limite ) return 'La revisión todavía no se ha hecho nunca: puede que WP-Cron no se esté ejecutando.';
	return '';
}

function dip_seg_vigilar() {
	$estado   = dip_seg_estado( true );
	$problema = dip_seg_problema();
	$avisado  = (int) ( $estado['avisado'] ?? 0 );
	$correo   = (string) dip_ajuste( 'email_avisos' );
	$ajustes  = admin_url( 'admin.php?page=dryicepack#dip-seg' );
	$cambios  = array();
	if ( empty( $estado['desde'] ) ) $cambios['desde'] = time();
	if ( $problema && time() - $avisado > DAY_IN_SECONDS ) {
		$enviado = wp_mail(
			$correo,
			'Dryicepack: no se pueden revisar los pedidos reservados',
			"La web no ha podido comprobar si los pedidos reservados con antelación caen en un festivo de MRW.\n\nMotivo: {$problema}\n\n"
			. "Mientras tanto, revísalo a mano:\n"
			. "1. En Dryicepack → Ajustes, mira la tabla «Festivos de MRW» (días sin salida desde Mataró y provincias cerradas). Los cierres de cada población están en " . DIP_MRW_URL . "\n"
			. "2. En WooCommerce → Pedidos, la columna «Entrega» dice el día de cada pedido. Si alguno cae en un cierre del destino, o el día anterior no sale nada de Mataró, llama al cliente y cambia el día en el pedido («Cambiar el día»).\n"
			. "3. Pulsa «Revisar pedidos ahora» en {$ajustes}. Si sigue fallando, avisa a quien mantiene la web.\n\n"
			. 'Este aviso se repite una vez al día mientras dure el problema.'
		);
		if ( $enviado ) $cambios['avisado'] = time();
	} elseif ( ! $problema && $avisado ) {
		// Solo se da por avisado si el email sale: si falla, se reintenta en la próxima revisión
		if ( wp_mail( $correo, 'Dryicepack: la revisión de pedidos reservados vuelve a funcionar', "La revisión diaria de los pedidos reservados vuelve a funcionar. No hay que hacer nada.\n\n{$ajustes}" ) ) {
			$cambios['avisado'] = 0;
		}
	}
	if ( $cambios ) dip_seg_guardar_estado( $cambios );
}

// Si WP-Cron deja de ejecutarse, el aviso sale igualmente al entrar al escritorio (como mucho, una comprobación por hora)
add_action( 'admin_init', static function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_woocommerce' ) ) return;
	if ( ! get_transient( 'dip_seg_vigilado' ) ) {
		set_transient( 'dip_seg_vigilado', 1, HOUR_IN_SECONDS );
		dip_seg_vigilar();
	}
} );

/** Texto del aviso del escritorio ('' si no hay nada que avisar). */
function dip_seg_texto_aviso() {
	$problema = dip_seg_problema();
	if ( $problema ) return rtrim( $problema, '. ' ) . '. Mientras tanto, revisa a mano los pedidos con entrega en los próximos 21 días.';
	$pendientes = array_filter( (array) ( dip_seg_estado()['pendientes'] ?? array() ) );
	if ( ! $pendientes ) return '';
	return sprintf(
		'No se ha podido enviar el email de aviso: %s %s la entrega en un festivo. Llama al cliente y cambia el día en el pedido. El email se reintenta en la próxima revisión.',
		1 === count( $pendientes ) ? 'el pedido ' . reset( $pendientes ) : 'los pedidos ' . implode( ', ', $pendientes ),
		1 === count( $pendientes ) ? 'tiene' : 'tienen'
	);
}

add_action( 'admin_notices', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$texto = dip_seg_texto_aviso();
	if ( ! $texto ) return;
	printf(
		'<div class="notice notice-error"><p><strong>Pedidos reservados y festivos de MRW.</strong> %s <a href="%s">Ver y revisar ahora</a></p></div>',
		esc_html( $texto ),
		esc_url( admin_url( 'admin.php?page=dryicepack#dip-seg' ) )
	);
} );

/* =========================================================
 * Estado en Dryicepack → Ajustes y botón «Revisar pedidos ahora»
 * ========================================================= */

function dip_seg_html_estado() {
	$e         = dip_seg_estado();
	$ok        = (int) ( $e['ok'] ?? 0 );
	$siguiente = wp_next_scheduled( 'dip_revisar_entregas' );
	$boton     = wp_nonce_url( admin_url( 'admin.php?page=dryicepack&dip_seg=revisar' ), 'dip_seg' );
	$hecho     = isset( $_GET['dip_seg_hecho'] ) ? sanitize_key( wp_unslash( $_GET['dip_seg_hecho'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo muestra un aviso
	$problema  = dip_seg_texto_aviso();
	echo '<h2 id="dip-seg">Pedidos reservados y festivos de MRW (revisión diaria)</h2>';
	if ( 'ok' === $hecho && ! $problema ) {
		echo '<div class="notice notice-success inline"><p>' . esc_html( sprintf( 'Revisión hecha: %d pedidos con envío pendiente, %d avisos nuevos.', (int) ( $e['revisados'] ?? 0 ), (int) ( $e['avisos'] ?? 0 ) ) ) . '</p></div>';
	} elseif ( 'ocupado' === $hecho ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html( 'Ya hay una revisión en marcha. Espera unos minutos y vuelve a cargar esta página.' ) . '</p></div>';
	}
	echo '<p class="description">' . esc_html( 'Se puede reservar hasta ' . (int) DIP_DIAS_RESERVA . ' días, pero MRW publica sus festivos 21 días antes. Cada mañana la web repasa los pedidos pendientes (Procesando y En espera) que van por mensajería y, si el día elegido ha resultado ser un cierre de MRW o un festivo de la lista, deja una nota en el pedido y avisa a ' . (string) dip_ajuste( 'email_avisos' ) . '. Un aviso por pedido y día.' ) . '</p>';
	echo '<p>' . esc_html( $ok ? sprintf( 'Última revisión: %s · %d pedidos con envío pendiente · %d avisos.', wp_date( 'j/n/Y H:i', $ok ), (int) ( $e['revisados'] ?? 0 ), (int) ( $e['avisos'] ?? 0 ) ) : 'Todavía no se ha hecho ninguna revisión.' );
	if ( $siguiente ) echo ' ' . esc_html( 'Próxima: ' . wp_date( 'j/n/Y H:i', (int) $siguiente ) . '.' );
	if ( $problema ) echo ' <strong style="color:#b32d2e">' . esc_html( $problema ) . '</strong>';
	echo ' <a class="button" href="' . esc_url( $boton ) . '">Revisar pedidos ahora</a></p>';
}

add_action( 'admin_init', static function () {
	if ( ! isset( $_GET['dip_seg'] ) || 'revisar' !== $_GET['dip_seg'] || ! current_user_can( 'manage_woocommerce' ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- se verifica abajo
	check_admin_referer( 'dip_seg' );
	$r     = dip_seg_revisar();
	$hecho = is_wp_error( $r ) ? ( 'dip_seg_ocupado' === $r->get_error_code() ? 'ocupado' : 'error' ) : 'ok';
	wp_safe_redirect( add_query_arg( 'dip_seg_hecho', $hecho, admin_url( 'admin.php?page=dryicepack' ) ) . '#dip-seg' );
	exit;
} );

/* =========================================================
 * «Cambiar el día» en la pantalla del pedido (la alternativa manual)
 * ========================================================= */

/** Cambia el día de entrega (o de recogida) desde el escritorio y lo anota en el pedido. true si lo cambia. */
function dip_seg_cambiar_fecha( WC_Order $pedido, $nueva, ?DateTimeImmutable $ahora = null ) {
	$nueva = trim( (string) $nueva );
	$antes = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	$d     = DateTimeImmutable::createFromFormat( '!Y-m-d', $nueva, dip_zona_horaria() );
	if ( '' === $nueva || $nueva === $antes || ! $d || $d->format( 'Y-m-d' ) !== $nueva ) return false;
	$metodo = (string) $pedido->get_meta( '_dip_metodo_entrega' ) ?: ( $pedido->has_shipping_method( 'local_pickup' ) ? 'recogida' : 'envio' );
	dip_guardar_entrega( $pedido, $nueva, $metodo );
	$pedido->save_meta_data();
	$nota    = sprintf( '%s cambiado en el escritorio: %s → %s.', 'recogida' === $metodo ? 'Día de recogida' : 'Día de entrega', $antes ? dip_fecha_larga( $antes ) : 'sin día', dip_fecha_larga( $nueva ) );
	$destino = 'envio' === $metodo ? dip_destino_de_pedido( $pedido ) : null;
	$motivos = 'envio' === $metodo ? dip_seg_motivos( $nueva, $destino, $ahora ) : array();
	if ( $motivos ) {
		$nota .= ' Ojo: ' . implode( ' ', array_column( $motivos, 'texto' ) );
	} elseif ( ! dip_fecha_es_valida( $nueva, $metodo, $destino, $ahora ) ) {
		$nota .= ' Ojo: ese día no está entre los que ofrece la web (corte de las 12:00, domingo, lunes o festivo).';
	}
	$pedido->add_order_note( $nota . ' El cliente no recibe ningún aviso automático.' );
	return true;
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$fecha  = (string) $pedido->get_meta( '_dip_fecha_entrega' );
	$recoge = dip_es_pedido_de_recogida( $pedido );
	$aviso  = $fecha ? (string) ( dip_seg_avisos_pedido( $pedido )[ $fecha ]['motivo'] ?? '' ) : '';
	printf(
		'<p class="form-field form-field-wide"><label for="dip_cambiar_fecha">%s</label><input type="date" id="dip_cambiar_fecha" name="dip_cambiar_fecha" value="%s"></p>',
		esc_html( $recoge ? 'Cambiar el día de recogida' : 'Cambiar el día' ),
		esc_attr( $fecha )
	);
	if ( $aviso ) echo '<p style="color:#b32d2e"><strong>' . esc_html( 'Aviso: ' . $aviso ) . '</strong> ' . esc_html( 'Llama al cliente y cambia el día.' ) . '</p>';
}, 20 );

/* Antes de que WooCommerce ejecute las acciones del pedido (prioridad 50). WooCommerce ya verificó el nonce del pedido. */
add_action( 'woocommerce_process_shop_order_meta', static function ( $pedido_id ) {
	if ( ! isset( $_POST['dip_cambiar_fecha'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$pedido = wc_get_order( $pedido_id );
	if ( $pedido ) dip_seg_cambiar_fecha( $pedido, sanitize_text_field( wp_unslash( $_POST['dip_cambiar_fecha'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
}, 10 );
