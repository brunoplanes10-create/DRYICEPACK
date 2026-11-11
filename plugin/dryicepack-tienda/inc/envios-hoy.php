<?php
/**
 * Envíos de hoy (09/11/2026): lo que sale hoy por MRW, el número de cada envío y el aviso «va de camino» al cliente.
 *
 * Por qué: un sábado falló una entrega porque la caja llevaba la cinta de urgente 14:00 de lunes y no la de sábado
 * antes de las 12:00. MRW no lo sabía y nadie avisó al cliente hasta que llamó. El número de MRW solo estaba en la
 * etiqueta: no hay acceso a los servicios web de MRW, solo a su web de clientes.
 * Desde el 11/11/2026 la web crea los envíos con el servicio web de MRW (inc/mrw-sagec.php, con sus botones y su estado
 * en esta pantalla) y el enlace de pedido urgente (inc/urgente.php) también vive aquí. Apuntar el número a mano sigue
 * siendo la alternativa para los «Hacer a mano» y mientras MRW no esté conectado.
 *
 * 1. Pantalla «Envíos de hoy» (Dryicepack → Envíos de hoy, manage_woocommerce), pensada para el móvil en la nave:
 *    pedidos con envío, Procesando o En espera, cuyo día de salida es ayer, hoy o mañana. El día de salida es el
 *    anterior al de entrega (inc/negocio.php, dip_calcular_fechas); la fecha sale de _dip_fecha_entrega o, en los
 *    pedidos del tema antiguo, de _orddd_lite_timestamp (dip_seg_fecha_pedido). Los de sábado, destacados.
 *    «Guardar y avisar a los clientes» guarda _dip_mrw_envio (número) y _dip_enviado (fecha y hora de Madrid,
 *    'AAAA-MM-DD HH:MM:SS', la primera vez), deja una nota y llama a dip_aviso_enviar( $pedido, 'camino' )
 *    (inc/avisos.php). El mismo campo, con la misma lógica, está en la pantalla de cada pedido.
 * 2. Avisos internos al correo de avisos, solo si hay pedidos. WP-Cron con eventos sueltos a la hora exacta de Madrid
 *    (cada uno programa el del día siguiente, así no se corren una hora con el cambio de hora):
 *    - 10:00 del día de salida de los pedidos de sábado: lista y recordatorio de cinta, servicio y código postal.
 *    - Sábado a las 9:00: los pedidos del día con su número y enlace de MRW; los que no tienen número, primero.
 *    - Cada día a las 18:00: los pedidos que salían hoy y siguen sin número de MRW.
 *    Si un email no sale, se reintenta una vez a los 30 minutos. Si sigue sin salir, o si un aviso lleva más de 26 h
 *    sin comprobarse (WP-Cron parado): aviso en el escritorio y email (una vez al día; otro cuando se arregla).
 *    Alternativa manual: la pantalla «Envíos de hoy» enseña lo mismo.
 * 3. dip_url_seguimiento_mrw(): enlace a la web pública de MRW con el número (comprobado en mrw.es el 09/11/2026).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_EH_HORA_SABADO' ) )      define( 'DIP_EH_HORA_SABADO', '10:00' );     // día de salida de los pedidos de sábado
if ( ! defined( 'DIP_EH_HORA_REPARTO' ) )     define( 'DIP_EH_HORA_REPARTO', '09:00' );    // sábado, antes del reparto
if ( ! defined( 'DIP_EH_HORA_SIN_NUMERO' ) )  define( 'DIP_EH_HORA_SIN_NUMERO', '18:00' ); // pedidos de hoy sin número de MRW
if ( ! defined( 'DIP_EH_CADUCA' ) )           define( 'DIP_EH_CADUCA', 26 );               // horas sin comprobar antes de avisar
// DIP_HORA_ENTREGA (14:00) y DIP_HORA_ENTREGA_SABADO (12:00) están en inc/negocio.php: una sola pareja para toda la tienda
if ( ! defined( 'DIP_MRW_SEGUIMIENTO' ) )     define( 'DIP_MRW_SEGUIMIENTO', 'https://www.mrw.es/seguimiento/' );

/* =========================================================
 * Número y seguimiento de MRW
 * ========================================================= */

/**
 * Número de MRW tal como se apunta: letras, cifras, «/», «-» y espacios (lo que acepta el formulario de seguimiento
 * de mrw.es, hasta 35 caracteres). MRW no publica el formato: no se valida más.
 */
function dip_mrw_numero_limpio( $numero ) {
	$n = preg_replace( '/[^A-Za-z0-9\/\- ]/', '', (string) preg_replace( '/\s+/', ' ', (string) $numero ) );
	return substr( trim( (string) preg_replace( '/ {2,}/', ' ', (string) $n ) ), 0, 35 );
}

/**
 * Página de seguimiento de MRW de un envío. mrw.es/seguimiento/track.asp?envio=NÚMERO guarda el número y lleva al
 * resultado (es adonde MRW redirige sus enlaces antiguos MRW_resultados_consultas.asp?modo=nacional&envio=…;
 * comprobado el 09/11/2026). Sin número: la página de seguimiento, donde se escribe a mano.
 */
function dip_url_seguimiento_mrw( $numero ): string {
	$n = str_replace( ' ', '', dip_mrw_numero_limpio( $numero ) );
	return '' === $n ? DIP_MRW_SEGUIMIENTO : 'https://www.mrw.es/seguimiento/track.asp?envio=' . rawurlencode( $n );
}

/* =========================================================
 * Datos de los pedidos
 * ========================================================= */

function dip_eh_ahora() {
	return new DateTimeImmutable( 'now', dip_zona_horaria() );
}

function dip_eh_dia( $ymd ) {
	return DateTimeImmutable::createFromFormat( '!Y-m-d', (string) $ymd, dip_zona_horaria() ) ?: null;
}

function dip_eh_es_sabado( $ymd ) {
	$d = dip_eh_dia( $ymd );
	return $d && 6 === (int) $d->format( 'N' );
}

/** Hora límite de entrega de MRW para ese día: 14:00; el sábado, 12:00. */
function dip_hora_limite_entrega( $ymd ) {
	return dip_eh_es_sabado( $ymd ) ? DIP_HORA_ENTREGA_SABADO : DIP_HORA_ENTREGA;
}

/** Día de salida de Mataró (el anterior a la entrega) o ''. */
function dip_eh_salida_de( $entrega ) {
	$d = dip_eh_dia( $entrega );
	return $d ? $d->modify( '-1 day' )->format( 'Y-m-d' ) : '';
}

function dip_eh_fecha( WC_Order $p ) {
	return function_exists( 'dip_seg_fecha_pedido' ) ? dip_seg_fecha_pedido( $p ) : (string) $p->get_meta( '_dip_fecha_entrega' );
}

function dip_eh_es_recogida( WC_Order $p ) {
	if ( function_exists( 'dip_seg_es_recogida' ) ) return dip_seg_es_recogida( $p );
	return 'recogida' === (string) $p->get_meta( '_dip_metodo_entrega' );
}

function dip_eh_kg( WC_Order $p ) {
	if ( function_exists( 'dip_resenas_kg' ) ) return (float) dip_resenas_kg( $p );
	$kg = (float) str_replace( ',', '.', (string) $p->get_meta( '_dip_kg' ) );
	if ( $kg > 0 ) return $kg;
	foreach ( $p->get_items() as $l ) $kg += (float) $l->get_quantity() * (float) dip_kg_producto( $l->get_product() );
	return $kg;
}

function dip_eh_cajas( WC_Order $p ) {
	$cajas = (int) $p->get_meta( '_dip_cajas' );
	if ( $cajas > 0 ) return $cajas;
	foreach ( $p->get_items() as $l ) {
		if ( dip_kg_producto( $l->get_product() ) > 0 ) $cajas += (int) $l->get_quantity();
	}
	return $cajas;
}

/** "20 kg · 1 caja" */
function dip_eh_texto_kg( WC_Order $p ) {
	$kg    = dip_eh_kg( $p );
	$cajas = dip_eh_cajas( $p );
	$num   = rtrim( rtrim( number_format( $kg, 2, ',', '' ), '0' ), ',' );
	return ( $kg > 0 ? $num . ' kg' : '— kg' ) . ' · ' . $cajas . ( 1 === $cajas ? ' caja' : ' cajas' );
}

function dip_eh_cliente( WC_Order $p ) {
	$nombre  = trim( preg_replace( '/\s+/u', ' ', $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() ) );
	$empresa = trim( (string) $p->get_billing_company() );
	if ( $empresa && $nombre && $empresa !== $nombre ) return $empresa . ' (' . $nombre . ')';
	return $empresa ?: ( $nombre ?: '—' );
}

function dip_eh_lugar( WC_Order $p ) {
	return trim( ( $p->get_shipping_postcode() ?: $p->get_billing_postcode() ) . ' ' . ( $p->get_shipping_city() ?: $p->get_billing_city() ) );
}

/** "9/11 a las 11:32" de _dip_enviado ('' si no está). */
function dip_eh_texto_enviado( WC_Order $p ) {
	$env = (string) $p->get_meta( '_dip_enviado' );
	$d   = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $env, dip_zona_horaria() );
	return $d ? $d->format( 'j/n' ) . ' a las ' . $d->format( 'H:i' ) : '';
}

/**
 * Pedidos con envío (no recogida) cuya entrega es uno de los días $fechas ('Y-m-d'), ordenados por día y número.
 * La consulta mira _dip_fecha_entrega y, para los pedidos del tema antiguo, la marca de Order Delivery Date Lite
 * (cualquier hora de ese día en Madrid); después se comprueba cada uno con la misma regla que el resto del plugin.
 */
function dip_eh_pedidos_entrega( array $fechas, array $estados = array( 'wc-processing', 'wc-on-hold' ) ) {
	$fechas = array_values( array_unique( array_filter( array_map( 'strval', $fechas ), static fn( $f ) => null !== dip_eh_dia( $f ) ) ) );
	if ( ! $fechas || ! function_exists( 'wc_get_orders' ) ) return array();
	$meta = array( 'relation' => 'OR', array( 'key' => '_dip_fecha_entrega', 'value' => $fechas, 'compare' => 'IN' ) );
	foreach ( $fechas as $f ) {
		$d      = dip_eh_dia( $f );
		$meta[] = array( 'key' => '_orddd_lite_timestamp', 'value' => array( $d->getTimestamp(), $d->modify( '+1 day' )->getTimestamp() - 1 ), 'compare' => 'BETWEEN', 'type' => 'NUMERIC' );
	}
	$pedidos = wc_get_orders( array(
		'type'       => 'shop_order',
		'status'     => $estados,
		'limit'      => -1,
		'meta_query' => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
	) );
	$r = array();
	foreach ( (array) $pedidos as $p ) {
		if ( ! $p instanceof WC_Order || dip_eh_es_recogida( $p ) ) continue;
		if ( in_array( dip_eh_fecha( $p ), $fechas, true ) ) $r[] = $p;
	}
	usort( $r, static fn( $a, $b ) => array( dip_eh_fecha( $a ), $a->get_id() ) <=> array( dip_eh_fecha( $b ), $b->get_id() ) );
	return $r;
}

/* =========================================================
 * Guardar el número y avisar al cliente
 * ========================================================= */

/** ¿Ha salido el aviso? dip_aviso_enviar() puede devolver true/false, WP_Error o ['estado' => 'enviado'…]. null: sin respuesta. */
function dip_eh_aviso_resultado( $r ) {
	if ( null === $r ) return array( 'sin_respuesta', '' );
	if ( is_wp_error( $r ) ) return array( 'fallo', $r->get_error_message() );
	if ( is_array( $r ) ) {
		if ( isset( $r['estado'] ) ) return 'enviado' === $r['estado'] ? array( 'enviado', '' ) : array( 'fallo', (string) ( $r['motivo'] ?? $r['estado'] ) );
		if ( array_key_exists( 'ok', $r ) ) return $r['ok'] ? array( 'enviado', '' ) : array( 'fallo', (string) ( $r['error'] ?? '' ) );
		return array( $r ? 'enviado' : 'fallo', '' );
	}
	return $r ? array( 'enviado', '' ) : array( 'fallo', '' );
}

/**
 * Guarda el número de MRW de un pedido y, si es nuevo o ha cambiado y $avisar, avisa al cliente («va de camino»).
 * Número vacío en un pedido que lo tenía: se borran el número y la hora de envío.
 *
 * @return array{cambio: string, aviso: string, error: string} cambio: igual|nuevo|cambiado|borrado.
 *         aviso: no|enviado|fallo|sin_modulo|sin_respuesta.
 */
function dip_eh_guardar_numero( WC_Order $pedido, $numero, $avisar = true, $donde = 'Envíos de hoy' ) {
	$nuevo   = dip_mrw_numero_limpio( $numero );
	$antes   = (string) $pedido->get_meta( '_dip_mrw_envio' );
	$usuario = wp_get_current_user();
	$quien   = $usuario && $usuario->exists() ? ', ' . $usuario->display_name : '';
	if ( $nuevo === $antes ) return array( 'cambio' => 'igual', 'aviso' => 'no', 'error' => '' );

	if ( '' === $nuevo ) {
		$web = '' !== (string) $pedido->get_meta( '_dip_sagec_creado' );
		foreach ( array( '_dip_mrw_envio', '_dip_enviado', '_dip_sagec_creado', '_dip_sagec_huella', '_dip_sagec_revisar' ) as $k ) $pedido->delete_meta_data( $k );
		$pedido->save_meta_data();
		if ( function_exists( 'dip_ast_quitar' ) ) dip_ast_quitar( $pedido, $antes );
		$pedido->add_order_note( sprintf( 'Número de MRW borrado (%s%s): era %s. El cliente no recibe ningún aviso.%s', $donde, $quien, $antes, $web ? ' Lo había creado la web en MRW: si no va a salir, anúlalo también en la web de clientes de MRW.' : '' ) );
		return array( 'cambio' => 'borrado', 'aviso' => 'no', 'error' => '' );
	}

	$pedido->update_meta_data( '_dip_mrw_envio', $nuevo );
	if ( '' === (string) $pedido->get_meta( '_dip_enviado' ) ) $pedido->update_meta_data( '_dip_enviado', dip_eh_ahora()->format( 'Y-m-d H:i:s' ) );
	$pedido->delete_meta_data( '_dip_sagec_mano' ); // ya está hecho a mano
	$pedido->save_meta_data();
	// También en Advanced Shipment Tracking: de ahí lee el número la app INDUNOVA Control (inc/mrw-sagec.php)
	if ( function_exists( 'dip_ast_poner' ) ) {
		if ( $antes ) dip_ast_quitar( $pedido, $antes );
		dip_ast_poner( $pedido, $nuevo );
	}

	$aviso = 'no';
	$error = '';
	if ( $avisar ) {
		if ( ! function_exists( 'dip_aviso_enviar' ) ) {
			$aviso = 'sin_modulo';
		} else {
			// Número corregido (o borrado y vuelto a poner) después de avisar: el aviso sale otra vez con el número bueno,
			// si el aviso está activado. Solo se llega aquí si el número es nuevo o ha cambiado.
			$extra = array();
			if ( '' !== (string) $pedido->get_meta( '_dip_aviso_camino' ) && ( ! function_exists( 'dip_avisos_activo' ) || dip_avisos_activo( 'camino' ) ) ) $extra['forzar'] = true;
			try {
				list( $aviso, $error ) = dip_eh_aviso_resultado( dip_aviso_enviar( $pedido, 'camino', $extra ) );
				if ( 'fallo' === $aviso && '' === $error && function_exists( 'dip_aviso_ultimo_motivo' ) ) $error = (string) dip_aviso_ultimo_motivo();
			} catch ( Throwable $e ) {
				$aviso = 'fallo';
				$error = $e->getMessage();
			}
		}
	}
	$texto_aviso = array(
		'no'            => 'Sin avisar al cliente.',
		'enviado'       => 'Aviso «va de camino» enviado al cliente.',
		'sin_respuesta' => 'Aviso «va de camino» lanzado (el módulo de avisos no dice si ha salido).',
		'fallo'         => 'No se ha podido avisar al cliente' . ( $error ? ' (' . $error . ')' : '' ) . ': avísale por WhatsApp o teléfono.',
		'sin_modulo'    => 'No se ha avisado al cliente: el aviso automático «va de camino» todavía no está instalado.',
	)[ $aviso ];
	$pedido->add_order_note( sprintf(
		'%s (%s%s): %s. Seguimiento: %s. %s',
		$antes ? 'Número de MRW cambiado' : 'Enviado con MRW',
		$donde,
		$quien,
		$antes ? $antes . ' → ' . $nuevo : 'número ' . $nuevo,
		dip_url_seguimiento_mrw( $nuevo ),
		$texto_aviso
	) );
	return array( 'cambio' => $antes ? 'cambiado' : 'nuevo', 'aviso' => $aviso, 'error' => $error );
}

/* =========================================================
 * Pantalla «Envíos de hoy»
 * ========================================================= */

/** Dirección de la pantalla: $dia 'ayer' | 'hoy' | 'manana', o un día de salida concreto ($fecha 'Y-m-d', para los emails). */
function dip_eh_url( $dia = '', $fecha = '' ) {
	$args = array( 'page' => 'dip-envios-hoy' );
	if ( in_array( $dia, array( 'ayer', 'manana' ), true ) ) $args['dia'] = $dia;
	if ( dip_eh_fecha_valida( $fecha ) ) $args = array( 'page' => 'dip-envios-hoy', 'fecha' => $fecha );
	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

function dip_eh_fecha_valida( $fecha ) {
	$d = dip_eh_dia( $fecha );
	return $d && $d->format( 'Y-m-d' ) === (string) $fecha;
}

/** 'ayer' | 'hoy' | 'manana' → día de salida. */
function dip_eh_dia_salida( $clave, ?DateTimeImmutable $ahora = null ) {
	$hoy = ( $ahora ?: dip_eh_ahora() )->setTimezone( dip_zona_horaria() )->setTime( 0, 0 );
	return 'ayer' === $clave ? $hoy->modify( '-1 day' ) : ( 'manana' === $clave ? $hoy->modify( '+1 day' ) : $hoy );
}

add_action( 'admin_menu', static function () {
	$GLOBALS['dip_eh_pagina'] = add_submenu_page( 'dryicepack', 'Envíos de hoy', 'Envíos de hoy', 'manage_woocommerce', 'dip-envios-hoy', 'dip_eh_pantalla' );
}, 20 );

add_action( 'admin_enqueue_scripts', static function ( $gancho ) {
	if ( empty( $GLOBALS['dip_eh_pagina'] ) || $gancho !== $GLOBALS['dip_eh_pagina'] ) return;
	$css = 'assets/envios-hoy.css';
	wp_enqueue_style( 'dip-envios-hoy', DIP_TIENDA_URL . $css, array(), (string) @filemtime( DIP_TIENDA_DIR . $css ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
} );

function dip_eh_pantalla() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$clave   = isset( $_GET['dia'] ) ? sanitize_key( wp_unslash( $_GET['dia'] ) ) : 'hoy'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo elige qué día se ve
	$clave   = in_array( $clave, array( 'ayer', 'manana' ), true ) ? $clave : 'hoy';
	$fecha   = isset( $_GET['fecha'] ) ? sanitize_text_field( wp_unslash( $_GET['fecha'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$salida  = dip_eh_fecha_valida( $fecha ) ? dip_eh_dia( $fecha ) : dip_eh_dia_salida( $clave );
	foreach ( array( 'ayer', 'hoy', 'manana' ) as $k ) {
		if ( dip_eh_dia_salida( $k )->format( 'Y-m-d' ) === $salida->format( 'Y-m-d' ) ) $clave = $k; // un día concreto que es ayer, hoy o mañana
	}
	if ( dip_eh_fecha_valida( $fecha ) && $fecha !== dip_eh_dia_salida( $clave )->format( 'Y-m-d' ) ) $clave = '';
	$s       = $salida->format( 'Y-m-d' );
	$entrega = $salida->modify( '+1 day' )->format( 'Y-m-d' );
	$pedidos = dip_eh_pedidos_entrega( array( $entrega ) );
	$kg      = array_sum( array_map( 'dip_eh_kg', $pedidos ) );
	$cajas   = array_sum( array_map( 'dip_eh_cajas', $pedidos ) );
	$sabado  = dip_eh_es_sabado( $entrega );
	$hecho   = get_transient( 'dip_eh_resultado_' . get_current_user_id() );
	if ( $hecho ) delete_transient( 'dip_eh_resultado_' . get_current_user_id() );
	$nombres = array( 'ayer' => 'Ayer', 'hoy' => 'Hoy', 'manana' => 'Mañana' );
	$corta   = static function ( DateTimeImmutable $d ) {
		$c = dip_fecha_corta( $d->format( 'Y-m-d' ) );
		return $c[0] . ' ' . $c[1] . ' ' . $c[2];
	};
	?>
	<div class="wrap dip-eh">
		<h1>Envíos de hoy</h1>
		<nav class="dip-eh__dias" aria-label="Día de salida">
			<?php foreach ( $nombres as $k => $n ) : ?>
				<a href="<?php echo esc_url( dip_eh_url( $k ) ); ?>"<?php echo $k === $clave ? ' aria-current="page"' : ''; ?>><strong><?php echo esc_html( $n ); ?></strong><span><?php echo esc_html( $corta( dip_eh_dia_salida( $k ) ) ); ?></span></a>
			<?php endforeach; ?>
		</nav>
		<?php
		if ( is_array( $hecho ) ) {
			echo '<div class="notice ' . esc_attr( $hecho['tipo'] ) . ' dip-eh__resultado"><p>' . esc_html( $hecho['texto'] ) . '</p>';
			foreach ( (array) ( $hecho['lineas'] ?? array() ) as $l ) echo '<p>' . esc_html( $l ) . '</p>';
			echo '</div>';
		}
		foreach ( dip_eh_problemas() as $problema ) echo '<div class="notice notice-error"><p>' . esc_html( $problema ) . '</p></div>';
		if ( function_exists( 'dip_sagec_problemas' ) ) {
			foreach ( dip_sagec_problemas() as $problema ) echo '<div class="notice notice-error"><p>' . esc_html( $problema ) . '</p></div>';
		}
		$volver = '' === $clave ? dip_eh_url( '', $s ) : dip_eh_url( $clave );
		?>
		<p class="dip-eh__resumen">
			<?php
			if ( ! $pedidos ) {
				$sin_salida = function_exists( 'dip_es_dia_de_salida' ) && ! dip_es_dia_de_salida( $salida );
				echo esc_html( sprintf( 'El %s no sale ningún pedido con envío%s.', dip_fecha_larga( $s ), $sin_salida ? ' (no es día de salida: fin de semana, festivo o cierre de MRW Mataró)' : '' ) );
			} else {
				echo esc_html( sprintf(
					'Salen el %1$s: %2$d %3$s · %4$s kg · %5$d %6$s. Llegan el %7$s, antes de las %8$s.',
					dip_fecha_larga( $s ),
					count( $pedidos ),
					1 === count( $pedidos ) ? 'pedido' : 'pedidos',
					rtrim( rtrim( number_format( $kg, 2, ',', '' ), '0' ), ',' ),
					$cajas,
					1 === $cajas ? 'caja' : 'cajas',
					dip_fecha_larga( $entrega ),
					dip_hora_limite_entrega( $entrega )
				) );
			}
			?>
		</p>
		<?php if ( $pedidos && 'hoy' === $clave && function_exists( 'dip_sagec_html_botones' ) ) dip_sagec_html_botones( $pedidos, $volver ); ?>
		<?php if ( $pedidos && $sabado ) : ?>
			<p class="dip-eh__sabado"><strong>Sábado antes de las 12:00:</strong> cinta y servicio de sábado de MRW · comprobar con MRW que el CP tiene reparto en sábado. Si no lo tiene, llama hoy al cliente: reembolso completo o recogerlo en Mataró.</p>
		<?php endif; ?>
		<?php if ( $pedidos ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dip-eh__form">
				<input type="hidden" name="action" value="dip_envios_hoy">
				<input type="hidden" name="dia" value="<?php echo esc_attr( $clave ); ?>">
				<?php if ( '' === $clave ) : ?><input type="hidden" name="fecha" value="<?php echo esc_attr( $s ); ?>"><?php endif; ?>
				<?php wp_nonce_field( 'dip_envios_hoy' ); ?>
				<ol class="dip-eh__lista">
					<?php foreach ( $pedidos as $p ) : dip_eh_html_pedido( $p, $volver ); endforeach; ?>
				</ol>
				<p class="dip-eh__ayuda"><?php echo esc_html( function_exists( 'dip_sagec_modo' ) && 'real' === dip_sagec_modo() ? 'Los envíos que crea la web ya tienen su número. Apunta aquí solo los que hayas hecho a mano en la web de clientes de MRW: al guardar, cada cliente con un número nuevo o cambiado recibe el aviso «va de camino» con su enlace de seguimiento.' : 'El número de MRW está en la etiqueta de cada caja y en la web de clientes de MRW. Al guardar, cada cliente con un número nuevo o cambiado recibe el aviso «va de camino» con su enlace de seguimiento.' ); ?></p>
				<p class="dip-eh__enviar"><button type="submit" class="button button-primary button-hero">Guardar y avisar a los clientes</button></p>
			</form>
			<?php if ( function_exists( 'dip_sagec_html_forms' ) ) dip_sagec_html_forms( $volver ); ?>
		<?php endif; ?>
		<?php if ( 'hoy' === $clave && function_exists( 'dip_urgente_html_panel' ) ) dip_urgente_html_panel(); ?>
		<?php dip_eh_html_estado(); ?>
	</div>
	<?php
}

function dip_eh_html_pedido( WC_Order $p, $volver = '' ) {
	$id     = (int) $p->get_id();
	$fecha  = dip_eh_fecha( $p );
	$sabado = dip_eh_es_sabado( $fecha );
	$numero = (string) $p->get_meta( '_dip_mrw_envio' );
	$envio  = dip_eh_texto_enviado( $p );
	$tel    = preg_replace( '/[^0-9+]/', '', (string) $p->get_billing_phone() );
	?>
	<li class="dip-eh-p<?php echo $sabado ? ' dip-eh-p--sabado' : ''; ?><?php echo $numero ? ' dip-eh-p--hecho' : ''; ?>">
		<p class="dip-eh-p__cabeza"><a href="<?php echo esc_url( $p->get_edit_order_url() ); ?>">Pedido <?php echo esc_html( $p->get_order_number() ); ?></a> <span><?php echo esc_html( dip_eh_cliente( $p ) ); ?></span></p>
		<?php if ( $sabado ) : ?>
			<p class="dip-eh-p__sabado"><strong>Sábado antes de las 12:00:</strong> cinta y servicio de sábado de MRW · comprobar con MRW que el CP tiene reparto en sábado.<?php if ( $tel ) : ?> <a href="<?php echo esc_attr( 'tel:' . $tel ); ?>">Llamar al cliente</a><?php endif; ?></p>
		<?php endif; ?>
		<p class="dip-eh-p__datos"><span><?php echo esc_html( dip_eh_texto_kg( $p ) ); ?></span><span><?php echo esc_html( dip_eh_lugar( $p ) ?: '—' ); ?></span><span><?php echo esc_html( 'Entrega: ' . ( $fecha ? dip_fecha_larga( $fecha ) . ', antes de las ' . dip_hora_limite_entrega( $fecha ) : 'sin día' ) ); ?></span><?php if ( '' !== (string) $p->get_meta( '_dip_urgente' ) ) : ?><span class="dip-eh-p__urgente">Urgente</span><?php endif; ?></p>
		<?php if ( function_exists( 'dip_sagec_html_pedido' ) && dip_eh_salida_de( $fecha ) === dip_eh_ahora()->format( 'Y-m-d' ) ) dip_sagec_html_pedido( $p, $volver ); ?>
		<label class="dip-eh-p__etiqueta" for="dip-mrw-<?php echo (int) $id; ?>">Número de MRW</label>
		<input class="dip-eh-p__campo" id="dip-mrw-<?php echo (int) $id; ?>" name="dip_mrw[<?php echo (int) $id; ?>]" type="text" inputmode="numeric" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="35" value="<?php echo esc_attr( $numero ); ?>">
		<?php if ( $numero ) : ?>
			<p class="dip-eh-p__estado"><?php echo esc_html( $envio ? 'Enviado el ' . $envio : 'Con número' ); ?> · <a href="<?php echo esc_url( dip_url_seguimiento_mrw( $numero ) ); ?>" target="_blank" rel="noopener noreferrer">Seguir en MRW</a></p>
		<?php endif; ?>
	</li>
	<?php
}

/* «Guardar y avisar a los clientes» (POST → redirección → GET con el resultado) */
add_action( 'admin_post_dip_envios_hoy', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_envios_hoy' );
	$dia     = isset( $_POST['dia'] ) ? sanitize_key( wp_unslash( $_POST['dia'] ) ) : 'hoy';
	$fecha   = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : '';
	$numeros = isset( $_POST['dip_mrw'] ) && is_array( $_POST['dip_mrw'] ) ? wp_unslash( $_POST['dip_mrw'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanea abajo, uno a uno
	$cuenta  = array( 'nuevo' => 0, 'cambiado' => 0, 'borrado' => 0, 'avisados' => 0 );
	$lineas  = array();
	$sin_mod = false;
	foreach ( $numeros as $id => $numero ) {
		$pedido = wc_get_order( absint( $id ) );
		if ( ! $pedido instanceof WC_Order || 'shop_order' !== $pedido->get_type() || dip_eh_es_recogida( $pedido ) ) continue;
		$r = dip_eh_guardar_numero( $pedido, sanitize_text_field( (string) $numero ) );
		if ( 'igual' === $r['cambio'] ) continue;
		$cuenta[ $r['cambio'] ]++;
		if ( in_array( $r['aviso'], array( 'enviado', 'sin_respuesta' ), true ) ) $cuenta['avisados']++;
		if ( 'sin_modulo' === $r['aviso'] ) $sin_mod = true;
		if ( 'fallo' === $r['aviso'] ) $lineas[] = sprintf( 'Pedido %s: el aviso al cliente no ha salido%s. Avísale por WhatsApp o teléfono.', $pedido->get_order_number(), $r['error'] ? ' (' . $r['error'] . ')' : '' );
	}
	$guardados = $cuenta['nuevo'] + $cuenta['cambiado'];
	if ( ! $guardados && ! $cuenta['borrado'] ) {
		$texto = 'No había nada nuevo que guardar.';
	} else {
		$texto = sprintf( 'Guardado: %d %s', $guardados, 1 === $guardados ? 'número de MRW' : 'números de MRW' )
			. ( $cuenta['borrado'] ? sprintf( ' y %d %s', $cuenta['borrado'], 1 === $cuenta['borrado'] ? 'borrado' : 'borrados' ) : '' )
			. ( $guardados ? sprintf( '. Clientes avisados: %d.', $cuenta['avisados'] ) : '.' );
	}
	if ( $sin_mod ) $lineas[] = 'El aviso «va de camino» al cliente todavía no está instalado (falta dip_aviso_enviar en inc/avisos.php): los números están guardados, pero los clientes no han recibido ningún email.';
	set_transient( 'dip_eh_resultado_' . get_current_user_id(), array(
		'tipo'   => $lineas ? 'notice-warning' : 'notice-success',
		'texto'  => $texto,
		'lineas' => $lineas,
	), 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( dip_eh_url( $dia, $fecha ) );
	exit;
} );

/* ---------- El mismo campo en la pantalla de cada pedido (debajo de la dirección de envío) ---------- */
add_action( 'woocommerce_admin_order_data_after_shipping_address', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$mi_pedido = function_exists( 'dip_url_mi_pedido' ) ? dip_url_mi_pedido( $pedido ) : '';
	if ( dip_eh_es_recogida( $pedido ) ) {
		if ( $mi_pedido ) echo '<p><a href="' . esc_url( $mi_pedido ) . '" target="_blank" rel="noopener">' . esc_html( 'Abrir «Mi pedido» del cliente' ) . '</a></p>';
		return;
	}
	$numero = (string) $pedido->get_meta( '_dip_mrw_envio' );
	$envio  = dip_eh_texto_enviado( $pedido );
	echo '<div class="dip-mrw-pedido">';
	printf(
		'<p class="form-field form-field-wide"><label for="dip_mrw_envio">%s</label><input type="text" id="dip_mrw_envio" name="dip_mrw_envio" value="%s" inputmode="numeric" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="35" style="font-size:16px"></p>',
		esc_html( 'Número de MRW' ),
		esc_attr( $numero )
	);
	printf(
		'<p><label><input type="checkbox" name="dip_mrw_avisar" value="1" checked> %s</label></p>',
		esc_html( 'Al guardar un número nuevo o cambiado, avisar al cliente por email («va de camino»)' )
	);
	$partes = array();
	if ( $numero ) {
		if ( $envio ) $partes[] = esc_html( 'Enviado el ' . $envio );
		$partes[] = '<a href="' . esc_url( dip_url_seguimiento_mrw( $numero ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( 'Seguir en MRW' ) . '</a>';
	}
	if ( $mi_pedido ) $partes[] = '<a href="' . esc_url( $mi_pedido ) . '" target="_blank" rel="noopener">' . esc_html( 'Abrir «Mi pedido» del cliente' ) . '</a>';
	if ( $partes ) echo '<p>' . implode( ' · ', $partes ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba
	echo '</div>';
}, 30 );

/* Antes de que WooCommerce ejecute las acciones del pedido (prioridad 50). WooCommerce ya verificó el nonce del pedido. */
add_action( 'woocommerce_process_shop_order_meta', static function ( $pedido_id ) {
	if ( ! isset( $_POST['dip_mrw_envio'] ) || ! current_user_can( 'edit_shop_orders' ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido || dip_eh_es_recogida( $pedido ) ) return;
	$r = dip_eh_guardar_numero( $pedido, sanitize_text_field( wp_unslash( $_POST['dip_mrw_envio'] ) ), ! empty( $_POST['dip_mrw_avisar'] ), 'pantalla del pedido' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( in_array( $r['aviso'], array( 'fallo', 'sin_modulo' ), true ) && class_exists( 'WC_Admin_Meta_Boxes' ) ) {
		WC_Admin_Meta_Boxes::add_error( 'sin_modulo' === $r['aviso']
			? 'Número de MRW guardado, pero el cliente no ha recibido ningún aviso: el aviso «va de camino» todavía no está instalado.'
			: 'Número de MRW guardado, pero el aviso al cliente no ha salido' . ( $r['error'] ? ' (' . $r['error'] . ')' : '' ) . '. Avísale por WhatsApp o teléfono.' );
	}
}, 20 );

/* =========================================================
 * Avisos internos (WP-Cron)
 * ========================================================= */

function dip_eh_tareas() {
	return array(
		'dip_eh_aviso_sabado'     => array( 'hora' => DIP_EH_HORA_SABADO, 'nombre' => 'Pedidos para el sábado' ),
		'dip_eh_aviso_reparto'    => array( 'hora' => DIP_EH_HORA_REPARTO, 'nombre' => 'Reparto del sábado' ),
		'dip_eh_aviso_sin_numero' => array( 'hora' => DIP_EH_HORA_SIN_NUMERO, 'nombre' => 'Pedidos de hoy sin número de MRW' ),
	);
}

/** Próxima vez que toca $hora ('HH:MM', Madrid): hoy si aún no ha pasado; si no, mañana (misma regla que dip_seg_primera_hora). */
function dip_eh_proxima( $hora, ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	list( $h, $m ) = array_map( 'intval', explode( ':', (string) $hora ) + array( 0, 0 ) );
	$cuando = $ahora->setTime( $h, $m );
	return ( $cuando <= $ahora ? $cuando->modify( '+1 day' ) : $cuando )->getTimestamp();
}

/** Un evento suelto por aviso; cuando se ejecuta, la siguiente visita programa el del día siguiente a la misma hora de Madrid. */
function dip_eh_programar() {
	foreach ( dip_eh_tareas() as $gancho => $t ) {
		if ( ! wp_next_scheduled( $gancho ) ) wp_schedule_single_event( dip_eh_proxima( $t['hora'] ), $gancho );
	}
	if ( empty( dip_eh_estado()['desde'] ) ) dip_eh_guardar_estado( array() );
}
add_action( 'init', 'dip_eh_programar' );

foreach ( array_keys( dip_eh_tareas() ) as $dip_eh_gancho ) {
	add_action( $dip_eh_gancho, static function () use ( $dip_eh_gancho ) {
		dip_eh_ejecutar( $dip_eh_gancho );
	} );
}
unset( $dip_eh_gancho );
add_action( 'dip_eh_reintentar', static function ( $tarea ) {
	if ( isset( dip_eh_tareas()[ (string) $tarea ] ) ) dip_eh_ejecutar( (string) $tarea, true );
} );

/** Estado: ['desde' => ts, 'avisado' => ts, 'tareas' => [gancho => ['ok' => ts, 'enviado_dia' => 'Y-m-d', 'enviado' => ts, 'fallo' => […]]]]. */
function dip_eh_estado() {
	if ( function_exists( 'wp_cache_delete' ) ) wp_cache_delete( 'dip_envios_hoy', 'options' ); // tal como está ahora (WP-Cron y visitas a la vez)
	$e = get_option( 'dip_envios_hoy', array() );
	return is_array( $e ) ? $e : array();
}

function dip_eh_guardar_estado( array $cambios, $tarea = '' ) {
	$e = dip_eh_estado();
	if ( empty( $e['desde'] ) ) $e['desde'] = time();
	if ( $tarea ) {
		$e['tareas'][ $tarea ] = array_filter( array_merge( (array) ( $e['tareas'][ $tarea ] ?? array() ), $cambios ), static fn( $v ) => null !== $v );
	} else {
		$e = array_merge( $e, $cambios );
	}
	update_option( 'dip_envios_hoy', $e, false );
}

/**
 * Ejecuta un aviso: prepara el email y lo manda al correo de avisos si hay pedidos (una vez al día por aviso).
 * Devuelve 'enviado' | 'sin_pedidos' | 'ya_enviado' | 'fallo'.
 */
function dip_eh_ejecutar( $tarea, $reintento = false, ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: dip_eh_ahora() )->setTimezone( dip_zona_horaria() );
	$hoy   = $ahora->format( 'Y-m-d' );
	$antes = (array) ( dip_eh_estado()['tareas'][ $tarea ] ?? array() );
	try {
		$email = dip_eh_preparar( $tarea, $ahora );
	} catch ( Throwable $e ) {
		$email = array( 'error' => $e->getMessage() );
	}

	if ( isset( $email['error'] ) ) {
		$res     = 'fallo';
		$cambios = array( 'ok' => time(), 'fallo' => array( 'cuando' => time(), 'asunto' => dip_eh_tareas()[ $tarea ]['nombre'], 'error' => 'no se ha podido preparar: ' . $email['error'] ) );
	} elseif ( ! $email ) {
		$res     = 'sin_pedidos';
		$cambios = array( 'ok' => time(), 'fallo' => null );
	} elseif ( ( $antes['enviado_dia'] ?? '' ) === $hoy ) {
		$res     = 'ya_enviado';
		$cambios = array( 'ok' => time() );
	} else {
		$error    = '';
		$capturar = static function ( $fallo ) use ( &$error ) {
			$error = $fallo instanceof WP_Error ? $fallo->get_error_message() : 'error desconocido';
		};
		$correo = (string) dip_ajuste( 'email_avisos' );
		add_action( 'wp_mail_failed', $capturar );
		$ok = '' !== $correo && wp_mail( $correo, $email['asunto'], $email['texto'] );
		remove_action( 'wp_mail_failed', $capturar );
		if ( $ok ) {
			$res     = 'enviado';
			$cambios = array( 'ok' => time(), 'enviado_dia' => $hoy, 'enviado' => time(), 'fallo' => null );
		} else {
			$res     = 'fallo';
			$cambios = array( 'ok' => time(), 'fallo' => array( 'cuando' => time(), 'asunto' => $email['asunto'], 'error' => $error ?: ( '' === $correo ? 'no hay correo de avisos' : '' ) ) );
			if ( ! $reintento ) wp_schedule_single_event( time() + 30 * MINUTE_IN_SECONDS, 'dip_eh_reintentar', array( $tarea ) );
		}
	}
	dip_eh_guardar_estado( $cambios, $tarea );
	dip_eh_vigilar();
	return $res;
}

/** Asunto y texto del aviso que toca ahora, o null si no hay pedidos (o no es el día). */
function dip_eh_preparar( $tarea, DateTimeImmutable $ahora ) {
	$hoy = $ahora->setTime( 0, 0 );
	if ( 'dip_eh_aviso_sabado' === $tarea ) {
		$sabado = $hoy->modify( '+1 day' );
		if ( 6 !== (int) $sabado->format( 'N' ) ) return null;
		$pedidos = dip_eh_pedidos_entrega( array( $sabado->format( 'Y-m-d' ) ) );
		return $pedidos ? dip_eh_email_sabado( $hoy->format( 'Y-m-d' ), $sabado->format( 'Y-m-d' ), $pedidos ) : null;
	}
	if ( 'dip_eh_aviso_reparto' === $tarea ) {
		if ( 6 !== (int) $hoy->format( 'N' ) ) return null;
		// También los Completados: puede que ya se marcaran al enviarlos
		$pedidos = dip_eh_pedidos_entrega( array( $hoy->format( 'Y-m-d' ) ), array( 'wc-processing', 'wc-on-hold', 'wc-completed' ) );
		return $pedidos ? dip_eh_email_reparto( $hoy->format( 'Y-m-d' ), $pedidos ) : null;
	}
	if ( 'dip_eh_aviso_sin_numero' === $tarea ) {
		$entrega = $hoy->modify( '+1 day' )->format( 'Y-m-d' );
		$pedidos = array_values( array_filter( dip_eh_pedidos_entrega( array( $entrega ) ), static fn( $p ) => '' === (string) $p->get_meta( '_dip_mrw_envio' ) ) );
		return $pedidos ? dip_eh_email_sin_numero( $hoy->format( 'Y-m-d' ), $entrega, $pedidos ) : null;
	}
	return null;
}

/** Una línea por pedido para los emails internos. */
function dip_eh_linea_email( WC_Order $p, $con_mrw = false ) {
	$numero = (string) $p->get_meta( '_dip_mrw_envio' );
	$texto  = 'Pedido ' . $p->get_order_number() . ' · ' . dip_eh_cliente( $p ) . ' · ' . ( dip_eh_lugar( $p ) ?: 'sin código postal' ) . ' · ' . dip_eh_texto_kg( $p ) . "\n";
	if ( $con_mrw && $numero ) $texto .= '  MRW ' . $numero . ' · Seguimiento: ' . dip_url_seguimiento_mrw( $numero ) . "\n";
	return $texto . '  Teléfono: ' . ( $p->get_billing_phone() ?: '—' ) . ' · Abrir el pedido: ' . $p->get_edit_order_url() . "\n";
}

function dip_eh_email_sabado( $salida, $sabado, array $pedidos ) {
	$n      = count( $pedidos );
	$asunto = sprintf( 'Pedidos para el sábado %s: %d', preg_replace( '/^\S+\s+/u', '', dip_fecha_larga( $sabado ) ), $n );
	$texto  = sprintf( "Hoy, %s, %s con entrega el %s, antes de las %s.\n\n", dip_fecha_larga( $salida ), 1 === $n ? 'sale 1 pedido' : 'salen ' . $n . ' pedidos', dip_fecha_larga( $sabado ), DIP_HORA_ENTREGA_SABADO )
		. "EN CADA CAJA\n"
		. '- Cinta y servicio de sábado de MRW, antes de las ' . DIP_HORA_ENTREGA_SABADO . ' (no la de urgente ' . DIP_HORA_ENTREGA . ").\n"
		. "- Comprueba con MRW que el código postal tiene reparto en sábado.\n"
		. '- Si un código postal no tiene reparto en sábado, llama hoy al cliente y que elija: reembolso completo de todo lo que pagó, o recogerlo en la nave de Mataró'
		. ' (si lo recoge el mismo sábado, del suplemento de sábado nos quedamos ' . dip_euros( DIP_RECOGIDA_SABADO_CON_IVA ) . " y le devolvemos la diferencia).\n\n"
		. "PEDIDOS\n";
	foreach ( $pedidos as $p ) $texto .= dip_eh_linea_email( $p );
	$texto .= function_exists( 'dip_sagec_modo' ) && 'real' === dip_sagec_modo()
		? "\nLa web crea estos envíos en MRW con «entrega en sábado»: comprueba que la etiqueta de cada caja lo dice. Los que queden «Hacer a mano» están en «Envíos de hoy»: " . dip_eh_url( '', $salida ) . "\n"
		: "\nCuando salgan, apunta el número de MRW de cada caja en «Envíos de hoy» y pulsa «Guardar y avisar a los clientes»: " . dip_eh_url( '', $salida ) . "\n";
	return array( 'asunto' => $asunto, 'texto' => $texto, 'pedidos' => array_map( static fn( $p ) => $p->get_order_number(), $pedidos ) );
}

function dip_eh_email_reparto( $sabado, array $pedidos ) {
	$sin    = array_values( array_filter( $pedidos, static fn( $p ) => '' === (string) $p->get_meta( '_dip_mrw_envio' ) ) );
	$con    = array_values( array_filter( $pedidos, static fn( $p ) => '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) );
	$n      = count( $pedidos );
	$asunto = sprintf( 'Reparto de hoy, %s: %d %s', dip_fecha_larga( $sabado ), $n, 1 === $n ? 'pedido' : 'pedidos' ) . ( $sin ? sprintf( ' (%d sin número de MRW)', count( $sin ) ) : '' );
	$texto  = 'MRW reparte hoy ' . ( 1 === $n ? 'este pedido' : 'estos pedidos' ) . ' antes de las ' . DIP_HORA_ENTREGA_SABADO . ".\n\n";
	if ( $sin ) {
		$texto .= "SIN NÚMERO DE MRW: búscalo en la web de clientes de MRW y apúntalo en «Envíos de hoy»\n";
		foreach ( $sin as $p ) $texto .= dip_eh_linea_email( $p );
		$texto .= "\n";
	}
	if ( $con ) {
		$texto .= "CON NÚMERO DE MRW\n";
		foreach ( $con as $p ) $texto .= dip_eh_linea_email( $p, true );
		$texto .= "\n";
	}
	$texto .= 'Si alguna caja no sale en reparto, llama al cliente antes de las ' . DIP_HORA_ENTREGA_SABADO . " y ofrécele reembolso completo o recogerlo en Mataró (Acciones del pedido → Avisar de una incidencia).\n\n"
		. 'Envíos de hoy: ' . dip_eh_url( '', dip_eh_salida_de( $sabado ) ) . "\n";
	return array( 'asunto' => $asunto, 'texto' => $texto, 'pedidos' => array_map( static fn( $p ) => $p->get_order_number(), $pedidos ) );
}

function dip_eh_email_sin_numero( $salida, $entrega, array $pedidos ) {
	$n      = count( $pedidos );
	$asunto = sprintf( 'Pedidos de hoy sin número de MRW: %d', $n );
	$texto  = sprintf( "%s hoy, %s, y todavía no %s número de MRW. Sin número, el cliente no recibe el aviso de que va de camino ni puede seguirlo.\n\n", 1 === $n ? 'Este pedido salía' : 'Estos pedidos salían', dip_fecha_larga( $salida ), 1 === $n ? 'tiene' : 'tienen' );
	foreach ( $pedidos as $p ) $texto .= dip_eh_linea_email( $p ) . '  Entrega: ' . dip_fecha_larga( $entrega ) . ', antes de las ' . dip_hora_limite_entrega( $entrega ) . "\n";
	$texto .= "\nQué hacer:\n"
		. '1. ' . ( 1 === $n ? 'Si ya ha salido' : 'Si ya salieron' ) . ": apunta el número de MRW (está en la etiqueta y en la web de clientes de MRW) en «Envíos de hoy» y pulsa «Guardar y avisar a los clientes».\n"
		. '2. ' . ( 1 === $n ? 'Si no ha salido' : 'Si alguno no ha salido' ) . ": llama hoy al cliente y ofrécele reembolso completo o recogerlo en Mataró.\n\n"
		. 'Envíos de hoy: ' . dip_eh_url( '', $salida ) . "\n";
	return array( 'asunto' => $asunto, 'texto' => $texto, 'pedidos' => array_map( static fn( $p ) => $p->get_order_number(), $pedidos ) );
}

/* =========================================================
 * Si un aviso no sale o no se comprueba: email y aviso en el escritorio
 * ========================================================= */

/** Problemas de los avisos (lista de textos; vacía si todo va bien). */
function dip_eh_problemas() {
	$e      = dip_eh_estado();
	$limite = DIP_EH_CADUCA * HOUR_IN_SECONDS;
	$r      = array();
	foreach ( dip_eh_tareas() as $gancho => $t ) {
		$estado = (array) ( $e['tareas'][ $gancho ] ?? array() );
		$ok     = (int) ( $estado['ok'] ?? 0 );
		if ( $ok && time() - $ok > $limite ) {
			$r[] = sprintf( 'El aviso «%s» lleva %d horas sin comprobarse: puede que WP-Cron no se esté ejecutando.', $t['nombre'], (int) floor( ( time() - $ok ) / HOUR_IN_SECONDS ) );
		} elseif ( ! $ok && ! empty( $e['desde'] ) && time() - (int) $e['desde'] > $limite ) {
			$r[] = sprintf( 'El aviso «%s» no se ha comprobado nunca: puede que WP-Cron no se esté ejecutando.', $t['nombre'] );
		}
		if ( ! empty( $estado['fallo'] ) ) {
			$f   = (array) $estado['fallo'];
			$r[] = sprintf( 'El email «%s» del %s no ha salido%s.', (string) ( $f['asunto'] ?? $t['nombre'] ), wp_date( 'j/n/Y \a \l\a\s H:i', (int) ( $f['cuando'] ?? time() ), dip_zona_horaria() ), empty( $f['error'] ) ? '' : ' (' . $f['error'] . ')' );
		}
	}
	return $r;
}

function dip_eh_vigilar() {
	$e         = dip_eh_estado();
	$problemas = dip_eh_problemas();
	$avisado   = (int) ( $e['avisado'] ?? 0 );
	$correo    = (string) dip_ajuste( 'email_avisos' );
	if ( $problemas && time() - $avisado > DAY_IN_SECONDS ) {
		$ok = '' !== $correo && wp_mail(
			$correo,
			'Dryicepack: los avisos de envíos no funcionan',
			"La web no ha podido enviar o comprobar los avisos de envíos (pedidos de sábado, reparto del sábado y pedidos sin número de MRW).\n\n"
			. '- ' . implode( "\n- ", $problemas ) . "\n\n"
			. "Mientras tanto, revísalo a mano cada día en «Envíos de hoy»: " . dip_eh_url() . "\n"
			. "- Los pedidos de sábado salen destacados: cinta y servicio de sábado de MRW y comprobar con MRW que el código postal tiene reparto en sábado.\n"
			. "- Apunta el número de MRW de cada envío y pulsa «Guardar y avisar a los clientes».\n"
			. "Si sigue fallando, comprueba que la web envía correo (WP Mail SMTP → Herramientas) y avisa a quien mantiene la web.\n\n"
			. 'Este aviso se repite una vez al día mientras dure el problema.'
		);
		if ( $ok ) dip_eh_guardar_estado( array( 'avisado' => time() ) );
	} elseif ( ! $problemas && $avisado ) {
		if ( '' !== $correo && wp_mail( $correo, 'Dryicepack: los avisos de envíos vuelven a funcionar', "Los avisos de envíos vuelven a funcionar. No hay que hacer nada.\n\n" . dip_eh_url() ) ) {
			dip_eh_guardar_estado( array( 'avisado' => 0 ) );
		}
	}
}

// Si WP-Cron deja de ejecutarse, el aviso sale igualmente al entrar al escritorio (como mucho, una comprobación por hora)
add_action( 'admin_init', static function () {
	if ( wp_doing_ajax() || ! current_user_can( 'manage_woocommerce' ) || get_transient( 'dip_eh_vigilado' ) ) return;
	set_transient( 'dip_eh_vigilado', 1, HOUR_IN_SECONDS );
	dip_eh_vigilar();
} );

add_action( 'admin_notices', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$pantalla = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $pantalla && ! empty( $GLOBALS['dip_eh_pagina'] ) && $pantalla->id === $GLOBALS['dip_eh_pagina'] ) return; // la pantalla ya los enseña
	$problemas = dip_eh_problemas();
	if ( ! $problemas ) return;
	printf(
		'<div class="notice notice-error"><p><strong>Avisos de envíos.</strong> %s Mientras tanto, revisa a mano «Envíos de hoy». <a href="%s">Abrir Envíos de hoy</a></p></div>',
		esc_html( implode( ' ', $problemas ) ),
		esc_url( dip_eh_url() )
	);
} );

/** Estado de los tres avisos al pie de la pantalla. */
function dip_eh_html_estado() {
	$e = dip_eh_estado();
	echo '<h2 class="dip-eh__titulo-estado">Avisos al correo de avisos</h2><ul class="dip-eh__estado">';
	$cuando = array( 'dip_eh_aviso_sabado' => 'el día de salida de los pedidos de sábado, a las ' . DIP_EH_HORA_SABADO, 'dip_eh_aviso_reparto' => 'el sábado a las ' . DIP_EH_HORA_REPARTO, 'dip_eh_aviso_sin_numero' => 'cada día a las ' . DIP_EH_HORA_SIN_NUMERO );
	foreach ( dip_eh_tareas() as $gancho => $t ) {
		$estado    = (array) ( $e['tareas'][ $gancho ] ?? array() );
		$siguiente = wp_next_scheduled( $gancho );
		$partes    = array();
		$partes[]  = ! empty( $estado['ok'] ) ? 'Última comprobación: ' . wp_date( 'j/n H:i', (int) $estado['ok'], dip_zona_horaria() ) : 'Todavía no se ha comprobado';
		if ( ! empty( $estado['enviado'] ) ) $partes[] = 'último email: ' . wp_date( 'j/n H:i', (int) $estado['enviado'], dip_zona_horaria() );
		if ( $siguiente ) $partes[] = 'próxima: ' . wp_date( 'j/n H:i', (int) $siguiente, dip_zona_horaria() );
		printf( '<li><strong>%s</strong> (%s, solo si hay pedidos). %s.</li>', esc_html( $t['nombre'] ), esc_html( $cuando[ $gancho ] ), esc_html( implode( ' · ', $partes ) ) );
	}
	echo '</ul><p class="description">' . esc_html( 'Van a ' . (string) dip_ajuste( 'email_avisos' ) . ' (Dryicepack → Ajustes). Si uno no sale, se reintenta a los 30 minutos y, si sigue sin salir o lleva más de ' . (int) DIP_EH_CADUCA . ' h sin comprobarse, aparece un aviso en el escritorio y llega un email.' ) . '</p>';
}
