<?php
/**
 * Pedido urgente (11/11/2026): un cliente pide a las 14:00 si hay hielo para mañana y el propietario dice que sí.
 *
 * - En «Envíos de hoy» el propietario crea un enlace urgente y se lo manda él, por donde le haya contactado el cliente
 *   (copiar, WhatsApp o email). Nada es automático: sin enlace, nadie compra para mañana después de las 12:00.
 * - El enlace sirve para un solo pedido, solo hoy y hasta la hora límite (Ajustes, de serie 17:30: después ya no se llega a
 *   la última furgoneta de MRW, que sale a las 18:30). Abre la entrega de mañana en el checkout aunque hayan pasado las
 *   12:00. Todo lo demás se sigue aplicando: festivos, cierres de MRW en el destino, sábado con su suplemento, 250 kg.
 * - Suplemento «Pedido urgente», como el del sábado (decidido el 11/11/2026): 9,70 € + IVA = 11,74 € con IVA (Ajustes;
 *   en cada enlace se puede cambiar o poner a 0). Se ve antes de pagar, en el pedido, en los emails y en la factura.
 *   Solo se cobra si hace falta el enlace: antes de las 12:00 o para otro día, no.
 * - Solo envío: la recogida en la nave no cambia (su precio es redondo y en efectivo).
 * - El pedido queda marcado (_dip_urgente) y, al pagarse, se crea al momento en MRW (inc/mrw-sagec.php).
 * - «Adelantar a mañana» (Acciones del pedido): para el cliente que ya compró eligiendo otro día. Cambia el día, lo anota
 *   y crea el envío en MRW. No cobra el suplemento (el pedido ya está pagado).
 *
 * Cómo funciona: ?dip_urgente=CÓDIGO guarda una cookie (HttpOnly) y lleva a la ficha del producto. Mientras la cookie
 * vale, el corte del envío pasa de las 12:00 a la hora límite (filtro dip_corte_minutos de inc/negocio.php) y la página no
 * se guarda en caché (DONOTCACHEPAGE). Los enlaces se guardan en la opción dip_urgentes (sin autoload), 7 días.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_FEE_URGENTE' ) ) define( 'DIP_FEE_URGENTE', 'dip-urgente' );

/* =========================================================
 * Enlaces
 * ========================================================= */

function dip_urgente_lista() {
	if ( function_exists( 'wp_cache_delete' ) ) wp_cache_delete( 'dip_urgentes', 'options' );
	$l = get_option( 'dip_urgentes', array() );
	return is_array( $l ) ? $l : array();
}

function dip_urgente_guardar_lista( array $l ) {
	$limite = time() - 7 * DAY_IN_SECONDS;
	$l      = array_filter( $l, static fn( $e ) => (int) ( $e['creado'] ?? 0 ) >= $limite );
	update_option( 'dip_urgentes', $l, false );
}

function dip_urgente_importe_defecto() {
	$v = function_exists( 'dip_ajuste' ) ? dip_ajuste( 'urgente_importe' ) : null;
	return null === $v ? (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX : max( 0, (float) $v );
}

function dip_urgente_url( $codigo ) {
	return add_query_arg( 'dip_urgente', rawurlencode( (string) $codigo ), home_url( '/' ) );
}

/**
 * Día que abre un enlace creado ahora: la primera entrega con salida hoy. '' si hoy no sale nada o ya es tarde.
 * Mismo cálculo que el checkout (dip_calcular_fechas) con el corte en la hora límite.
 */
function dip_urgente_entrega_de_hoy( ?DateTimeImmutable $ahora = null ) {
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$GLOBALS['dip_urgente_forzar'] = true;
	try {
		$cal = dip_calcular_fechas( 'envio', $ahora );
	} finally {
		unset( $GLOBALS['dip_urgente_forzar'] );
	}
	$hoy = $ahora->format( 'Y-m-d' );
	foreach ( $cal['fechas'] as $f ) {
		if ( $f['sale'] === $hoy ) return $f['fecha'];
		if ( $f['sale'] > $hoy ) break;
	}
	return '';
}

/** Crea un enlace. @return array|WP_Error la entrada (con 'codigo'). */
function dip_urgente_crear( $importe, $nota = '', ?DateTimeImmutable $ahora = null ) {
	$ahora   = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	$limite  = dip_sagec_hora_limite();
	if ( dip_sagec_minuto_del_dia( $ahora ) >= dip_sagec_minutos( $limite ) ) return new WP_Error( 'dip_urgente', 'Ya son más de las ' . $limite . ': hoy no llega a la última furgoneta de MRW.' );
	$entrega = dip_urgente_entrega_de_hoy( $ahora );
	if ( '' === $entrega ) return new WP_Error( 'dip_urgente', 'Hoy no sale ningún envío (fin de semana, festivo o cierre de MRW Mataró): no se puede crear un enlace urgente.' );
	list( $h, $m ) = array_map( 'intval', explode( ':', $limite ) );
	$usuario = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
	$codigo  = bin2hex( random_bytes( 16 ) );
	$e       = array(
		'codigo'  => $codigo,
		'creado'  => $ahora->getTimestamp(),
		'caduca'  => $ahora->setTime( $h, $m )->getTimestamp(),
		'dia'     => $ahora->format( 'Y-m-d' ),
		'entrega' => $entrega,
		'importe' => round( max( 0, (float) $importe ), 2 ),
		'nota'    => mb_substr( trim( (string) $nota ), 0, 80 ),
		'quien'   => $usuario && $usuario->exists() ? $usuario->display_name : '',
		'pedido'  => 0,
		'anulado' => 0,
	);
	$l            = dip_urgente_lista();
	$l[ $codigo ] = $e;
	dip_urgente_guardar_lista( $l );
	return $e;
}

/** Estado de un enlace: activo · usado · caducado · anulado. */
function dip_urgente_estado( array $e, ?DateTimeImmutable $ahora = null ) {
	$t = ( $ahora ?: new DateTimeImmutable( 'now' ) )->getTimestamp();
	if ( ! empty( $e['anulado'] ) ) return 'anulado';
	if ( ! empty( $e['pedido'] ) ) return 'usado';
	return $t < (int) $e['caduca'] ? 'activo' : 'caducado';
}

/**
 * Enlace de este visitante si vale ahora (cookie), o null. No vale en el escritorio, en la API REST ni en WP-Cron.
 * Uno ya usado sigue valiendo para su propio pedido mientras se paga (un pago rechazado se reintenta).
 */
function dip_urgente_activo( ?DateTimeImmutable $ahora = null ) {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_cron() ) return null;
	$codigo = isset( $_COOKIE['dip_urgente'] ) ? preg_replace( '/[^a-f0-9]/', '', (string) wp_unslash( $_COOKIE['dip_urgente'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se limpia aquí
	if ( 32 !== strlen( $codigo ) ) return null;
	$e = dip_urgente_lista()[ $codigo ] ?? null;
	if ( ! is_array( $e ) ) return null;
	$estado = dip_urgente_estado( $e, $ahora );
	if ( 'usado' === $estado ) {
		$espera = function_exists( 'WC' ) && WC()->session ? (int) WC()->session->get( 'order_awaiting_payment' ) : 0;
		if ( $espera && $espera === (int) $e['pedido'] && ( $ahora ?: new DateTimeImmutable( 'now' ) )->getTimestamp() < (int) $e['caduca'] ) return $e;
		return null;
	}
	return 'activo' === $estado ? $e : null;
}

/* El corte del envío pasa a la hora límite mientras vale el enlace (solo ese día) */
add_filter( 'dip_corte_minutos', static function ( $minutos, $metodo = 'envio', $ahora = null ) {
	if ( 'envio' !== $metodo ) return $minutos;
	if ( ! empty( $GLOBALS['dip_urgente_forzar'] ) ) return max( (int) $minutos, dip_sagec_minutos( dip_sagec_hora_limite() ) );
	$e = dip_urgente_activo();
	if ( ! $e ) return $minutos;
	$dia = ( $ahora instanceof DateTimeInterface ? DateTimeImmutable::createFromInterface( $ahora ) : new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() )->format( 'Y-m-d' );
	if ( $dia !== $e['dia'] ) return $minutos;
	list( $h, $m ) = array_map( 'intval', explode( ':', wp_date( 'H:i', (int) $e['caduca'], dip_zona_horaria() ) ) );
	return max( (int) $minutos, $h * 60 + $m );
}, 10, 3 );

/** ¿Este día solo se puede elegir gracias al enlace? (es el día del enlace y ya pasó el corte normal) */
function dip_urgente_hace_falta( $fecha, ?array $e = null, ?DateTimeImmutable $ahora = null ) {
	$e = $e ?? dip_urgente_activo();
	if ( ! $e || (string) $fecha !== (string) $e['entrega'] ) return false;
	return dip_sagec_minuto_del_dia( dip_sagec_ahora( $ahora ) ) >= DIP_CUTOFF_HOUR * 60 + DIP_CUTOFF_MINUTE;
}

/* ---------- Entrada por el enlace ---------- */
add_action( 'template_redirect', static function () {
	if ( ! isset( $_GET['dip_urgente'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- el código del enlace es la credencial
	$codigo = preg_replace( '/[^a-f0-9]/', '', strtolower( sanitize_text_field( wp_unslash( $_GET['dip_urgente'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$e      = dip_urgente_lista()[ $codigo ] ?? null;
	$post   = get_page_by_path( DIP_PRODUCT_SLUG, OBJECT, 'product' );
	$ficha  = $post ? get_permalink( $post ) : home_url( '/' );
	$vale   = is_array( $e ) && 'activo' === dip_urgente_estado( $e );
	nocache_headers();
	if ( $vale ) {
		setcookie( 'dip_urgente', $codigo, array( 'expires' => (int) $e['caduca'], 'path' => COOKIEPATH ?: '/', 'domain' => COOKIE_DOMAIN ?: '', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
		$_COOKIE['dip_urgente'] = $codigo;
	}
	wp_safe_redirect( add_query_arg( 'urgente', $vale ? '1' : 'caducado', $ficha ) );
	exit;
}, 0 );

/* Con el enlace activo, ninguna página se guarda en caché (WP Rocket respeta DONOTCACHEPAGE) */
add_action( 'template_redirect', static function () {
	if ( ! dip_urgente_activo() && ! isset( $_GET['urgente'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! defined( 'DONOTCACHEPAGE' ) ) define( 'DONOTCACHEPAGE', true );
	nocache_headers();
}, 1 );

/* ---------- Textos para el cliente ---------- */

/** Texto del enlace activo en el checkout y en la ficha, o ''. */
function dip_urgente_nota_cliente( $idioma = null ) {
	$e = dip_urgente_activo();
	if ( ! $e || ! dip_urgente_hace_falta( $e['entrega'], $e ) ) return '';
	$idioma = $idioma ?: ( function_exists( 'dip_idioma' ) ? dip_idioma() : 'es' );
	$t      = dip_textos_tienda( $idioma );
	return sprintf( $t['urgente_nota'], dip_fecha_larga( $e['entrega'], $idioma ), dip_hora_limite_entrega( $e['entrega'] ), dip_texto_euros( dip_urgente_con_iva( (float) $e['importe'] ) ), wp_date( 'H:i', (int) $e['caduca'], dip_zona_horaria() ) );
}

function dip_urgente_con_iva( $neto ) {
	return round( (float) $neto * dip_factor_iva(), 2 );
}

/** «+11,74 €» en la casilla del día urgente ('' en los demás). */
function dip_urgente_extra_fecha( $fecha ) {
	$e = dip_urgente_activo();
	if ( ! $e || ! dip_urgente_hace_falta( $fecha, $e ) || (float) $e['importe'] <= 0 ) return '';
	return '+' . dip_texto_euros( dip_urgente_con_iva( (float) $e['importe'] ) );
}

/* Ficha del producto: bienvenida o enlace caducado (la ficha imprime los avisos de WooCommerce) */
add_action( 'template_redirect', static function () {
	if ( ! isset( $_GET['urgente'] ) || ! function_exists( 'wc_add_notice' ) || ! function_exists( 'WC' ) || ! WC()->session ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$t = dip_textos_tienda();
	if ( '1' === $_GET['urgente'] && ( $e = dip_urgente_activo() ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! dip_urgente_hace_falta( $e['entrega'], $e ) ) return; // antes de las 12:00 se pide como siempre
		wc_add_notice( dip_urgente_nota_cliente(), 'notice' );
	} elseif ( 'caducado' === $_GET['urgente'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wc_add_notice( sprintf( $t['urgente_caducado'], dip_contacto()['telefono'], dip_contacto()['whatsapp'], dip_sagec_hora_limite() ), 'error' );
	}
}, 5 );

/* ---------- Suplemento en el carrito y en el pedido ---------- */
add_action( 'woocommerce_cart_calculate_fees', static function ( $carrito ) {
	if ( is_admin() && ! wp_doing_ajax() ) return;
	if ( ! is_checkout() && ! wp_doing_ajax() ) return;
	$e = dip_urgente_activo();
	if ( ! $e || (float) $e['importe'] <= 0 || 'envio' !== dip_metodo_elegido() ) return;
	if ( ! dip_urgente_hace_falta( dip_fecha_elegida( 'envio' ), $e ) ) return;
	$carrito->fees_api()->add_fee( array(
		'id'      => DIP_FEE_URGENTE,
		'name'    => dip_textos_tienda()['suplemento_urgente'],
		'amount'  => (float) $e['importe'],
		'taxable' => true,
	) );
}, 25 );

add_action( 'woocommerce_checkout_create_order_fee_item', static function ( $item, $clave, $fee ) {
	if ( DIP_FEE_URGENTE === ( $fee->id ?? '' ) ) $item->add_meta_data( '_dip_urgente', 1, true );
}, 10, 3 );

/** Marca el pedido urgente y gasta el enlace (si el pedido lleva el suplemento o es para el día del enlace después de las 12:00). */
function dip_urgente_marcar_pedido( $pedido ) {
	if ( ! $pedido instanceof WC_Order || '' !== (string) $pedido->get_meta( '_dip_urgente' ) ) return;
	$e = dip_urgente_activo();
	if ( ! $e ) return;
	$con_fee = false;
	foreach ( $pedido->get_items( 'fee' ) as $item ) {
		if ( $item->get_meta( '_dip_urgente' ) ) $con_fee = true;
	}
	if ( ! $con_fee && ! dip_urgente_hace_falta( (string) $pedido->get_meta( '_dip_fecha_entrega' ), $e ) ) return;
	$pedido->update_meta_data( '_dip_urgente', array( 'codigo' => substr( $e['codigo'], 0, 8 ), 'creado' => (int) $e['creado'], 'quien' => $e['quien'], 'nota' => $e['nota'], 'importe' => (float) $e['importe'] ) );
	$pedido->save_meta_data();
	$l = dip_urgente_lista();
	if ( isset( $l[ $e['codigo'] ] ) ) {
		$l[ $e['codigo'] ]['pedido'] = (int) $pedido->get_id();
		dip_urgente_guardar_lista( $l );
	}
	$pedido->add_order_note( sprintf(
		'Pedido urgente: hecho con el enlace urgente creado%s el %s%s. Entrega el %s. Suplemento: %s (IVA incluido).',
		$e['quien'] ? ' por ' . $e['quien'] : '',
		wp_date( 'j/n \a \l\a\s H:i', (int) $e['creado'], dip_zona_horaria() ),
		$e['nota'] ? ' («' . $e['nota'] . '»)' : '',
		dip_fecha_larga( $e['entrega'] ),
		$con_fee ? dip_euros( dip_urgente_con_iva( (float) $e['importe'] ) ) : '0,00 €'
	) );
}
add_action( 'woocommerce_checkout_order_created', 'dip_urgente_marcar_pedido', 20 );
add_action( 'woocommerce_store_api_checkout_order_processed', 'dip_urgente_marcar_pedido', 20 );

/* =========================================================
 * Escritorio: panel en «Envíos de hoy»
 * ========================================================= */

function dip_urgente_mensaje( array $e, $canal = 'whatsapp' ) {
	$dia   = dip_fecha_larga( $e['entrega'] );
	$hora  = dip_hora_limite_entrega( $e['entrega'] );
	$hasta = wp_date( 'H:i', (int) $e['caduca'], dip_zona_horaria() );
	$sup   = (float) $e['importe'] > 0 ? ' Lleva un suplemento de pedido urgente de ' . dip_euros( dip_urgente_con_iva( (float) $e['importe'] ) ) . ' (IVA incluido).' : '';
	$sab   = dip_eh_es_sabado( $e['entrega'] ) ? ' La entrega en sábado lleva su suplemento de ' . dip_euros( round( DIP_SATURDAY_SURCHARGE_EXCL_TAX * dip_factor_iva(), 2 ) ) . '.' : '';
	return 'Hola. Con este enlace puedes pedir tu hielo seco para el ' . $dia . ', con entrega antes de las ' . $hora . ': ' . dip_urgente_url( $e['codigo'] ) . "\n"
		. 'Sirve para un pedido y hasta las ' . $hasta . ' de hoy.' . $sup . $sab . "\n"
		. 'DryIcePack · ' . dip_contacto()['telefono'];
}

/** Panel «Pedido urgente para mañana» (solo en el día de hoy). */
function dip_urgente_html_panel() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$ahora   = new DateTimeImmutable( 'now', dip_zona_horaria() );
	$hoy     = $ahora->format( 'Y-m-d' );
	$entrega = dip_urgente_entrega_de_hoy( $ahora );
	$tarde   = dip_sagec_minuto_del_dia( $ahora ) >= dip_sagec_minutos( dip_sagec_hora_limite() );
	$enlaces = array_filter( dip_urgente_lista(), static fn( $e ) => ( $e['dia'] ?? '' ) === $hoy );
	$nuevo   = get_transient( 'dip_urgente_nuevo_' . get_current_user_id() );
	if ( $nuevo ) delete_transient( 'dip_urgente_nuevo_' . get_current_user_id() );
	$importe = dip_urgente_importe_defecto();
	?>
	<section class="dip-eh-urg" aria-labelledby="dip-eh-urg-t">
		<h2 id="dip-eh-urg-t">Pedido urgente para mañana</h2>
		<?php if ( $tarde ) : ?>
			<p>Ya son más de las <?php echo esc_html( dip_sagec_hora_limite() ); ?>: hoy no llega a la última furgoneta de MRW. No se pueden crear enlaces urgentes.</p>
		<?php elseif ( '' === $entrega ) : ?>
			<p>Hoy no sale ningún envío (fin de semana, festivo o cierre de MRW Mataró): no se pueden crear enlaces urgentes.</p>
		<?php else : ?>
			<p>Para un cliente que te pide después de las <?php echo esc_html( sprintf( '%02d:%02d', DIP_CUTOFF_HOUR, DIP_CUTOFF_MINUTE ) ); ?>. Con el enlace puede comprar para el <strong><?php echo esc_html( dip_fecha_larga( $entrega ) ); ?></strong> hasta las <?php echo esc_html( dip_sagec_hora_limite() ); ?>. Sirve para un pedido. Mándaselo tú, por donde te haya contactado.</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dip-eh-urg__form">
				<input type="hidden" name="action" value="dip_urgente_crear">
				<?php wp_nonce_field( 'dip_urgente_crear' ); ?>
				<p><label for="dip-urg-nota">Para quién (opcional)</label>
					<input id="dip-urg-nota" name="nota" type="text" maxlength="80" class="regular-text" placeholder="Nombre o empresa"></p>
				<p><label for="dip-urg-importe">Suplemento, sin IVA</label>
					<input id="dip-urg-importe" name="importe" type="number" min="0" step="0.01" class="small-text" value="<?php echo esc_attr( number_format( $importe, 2, '.', '' ) ); ?>"> €
					<span class="description"><?php echo esc_html( '= ' . dip_euros( dip_urgente_con_iva( $importe ) ) . ' con IVA. 0 para no cobrarlo.' ); ?></span></p>
				<p><button type="submit" class="button button-primary">Crear enlace urgente</button></p>
			</form>
		<?php endif; ?>
		<?php if ( $enlaces ) : ?>
			<ul class="dip-eh-urg__lista">
				<?php foreach ( array_reverse( $enlaces, true ) as $e ) : ?>
					<?php
					$estado = dip_urgente_estado( $e );
					$p      = ! empty( $e['pedido'] ) ? wc_get_order( (int) $e['pedido'] ) : null;
					$abrir  = is_array( $nuevo ) && ( $nuevo['codigo'] ?? '' ) === $e['codigo'];
					?>
					<li class="dip-eh-urg__enlace dip-eh-urg__enlace--<?php echo esc_attr( $estado ); ?>">
						<p><strong><?php echo esc_html( $e['nota'] ?: 'Enlace' ); ?></strong> · <?php echo esc_html( wp_date( 'H:i', (int) $e['creado'], dip_zona_horaria() ) . ' · ' . dip_euros( dip_urgente_con_iva( (float) $e['importe'] ) ) . ' con IVA · ' ); ?>
							<?php
							if ( 'usado' === $estado ) echo $p ? 'Usado: <a href="' . esc_url( $p->get_edit_order_url() ) . '">' . esc_html( 'pedido ' . $p->get_order_number() ) . '</a>' : esc_html( 'Usado' );
							else echo esc_html( array( 'activo' => 'Sin usar, caduca a las ' . wp_date( 'H:i', (int) $e['caduca'], dip_zona_horaria() ), 'caducado' => 'Caducado', 'anulado' => 'Anulado' )[ $estado ] );
							?>
						</p>
						<?php if ( 'activo' === $estado ) : ?>
							<details<?php echo $abrir ? ' open' : ''; ?>>
								<summary>Mandar el enlace</summary>
								<p><input type="text" class="large-text code dip-eh-urg__url" readonly value="<?php echo esc_attr( dip_urgente_url( $e['codigo'] ) ); ?>" aria-label="Enlace urgente"></p>
								<p class="dip-eh-urg__botones">
									<button type="button" class="button" data-dip-copiar="<?php echo esc_attr( dip_urgente_mensaje( $e ) ); ?>">Copiar mensaje</button>
									<a class="button" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( dip_urgente_mensaje( $e ) ) ); ?>" target="_blank" rel="noopener">WhatsApp</a>
									<a class="button" href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( 'Tu pedido urgente de hielo seco' ) . '&body=' . rawurlencode( dip_urgente_mensaje( $e ) ) ); ?>">Email</a>
								</p>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="dip_urgente_anular"><input type="hidden" name="codigo" value="<?php echo esc_attr( $e['codigo'] ); ?>">
									<?php wp_nonce_field( 'dip_urgente_anular' ); ?>
									<button type="submit" class="button-link button-link-delete">Anular este enlace</button>
								</form>
							</details>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<script>
			document.querySelectorAll('[data-dip-copiar]').forEach(function (b) {
				b.addEventListener('click', function () {
					var t = b.getAttribute('data-dip-copiar');
					var hecho = function () { b.textContent = 'Copiado'; setTimeout(function () { b.textContent = 'Copiar mensaje'; }, 2000); };
					if (navigator.clipboard) navigator.clipboard.writeText(t).then(hecho, function () { window.prompt('Copia el mensaje:', t); });
					else window.prompt('Copia el mensaje:', t);
				});
			});
			</script>
		<?php endif; ?>
	</section>
	<?php
}

add_action( 'admin_post_dip_urgente_crear', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_urgente_crear' );
	$importe = isset( $_POST['importe'] ) ? (float) str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['importe'] ) ) ) : dip_urgente_importe_defecto();
	$nota    = isset( $_POST['nota'] ) ? sanitize_text_field( wp_unslash( $_POST['nota'] ) ) : '';
	$e       = dip_urgente_crear( $importe, $nota );
	if ( is_wp_error( $e ) ) {
		set_transient( 'dip_eh_resultado_' . get_current_user_id(), array( 'tipo' => 'notice-error', 'texto' => $e->get_error_message(), 'lineas' => array() ), 5 * MINUTE_IN_SECONDS );
	} else {
		set_transient( 'dip_urgente_nuevo_' . get_current_user_id(), array( 'codigo' => $e['codigo'] ), 5 * MINUTE_IN_SECONDS );
		set_transient( 'dip_eh_resultado_' . get_current_user_id(), array( 'tipo' => 'notice-success', 'texto' => 'Enlace urgente creado: mándaselo al cliente (abajo, «Mandar el enlace»).', 'lineas' => array() ), 5 * MINUTE_IN_SECONDS );
	}
	wp_safe_redirect( dip_eh_url() . '#dip-eh-urg-t' );
	exit;
} );

add_action( 'admin_post_dip_urgente_anular', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( esc_html( 'No tienes permiso para hacer esto.' ), 403 );
	check_admin_referer( 'dip_urgente_anular' );
	$codigo = isset( $_POST['codigo'] ) ? preg_replace( '/[^a-f0-9]/', '', sanitize_text_field( wp_unslash( $_POST['codigo'] ) ) ) : '';
	$l      = dip_urgente_lista();
	if ( isset( $l[ $codigo ] ) ) {
		$l[ $codigo ]['anulado'] = time();
		dip_urgente_guardar_lista( $l );
	}
	wp_safe_redirect( dip_eh_url() . '#dip-eh-urg-t' );
	exit;
} );

/* =========================================================
 * «Adelantar a mañana»: el cliente compró con otro día y sale hoy
 * ========================================================= */

/** Cambia la entrega a la de hoy (si se puede) y crea el envío. @return true|string motivo */
function dip_urgente_adelantar( WC_Order $p, ?DateTimeImmutable $ahora = null ) {
	if ( dip_eh_es_recogida( $p ) ) return 'el pedido se recoge en Mataró';
	if ( ! in_array( $p->get_status(), array( 'processing', 'on-hold' ), true ) ) return 'el pedido no está Procesando ni En espera';
	if ( '' !== (string) $p->get_meta( '_dip_mrw_envio' ) ) return 'ya tiene número de MRW';
	$ahora = ( $ahora ?: new DateTimeImmutable( 'now' ) )->setTimezone( dip_zona_horaria() );
	if ( dip_sagec_minuto_del_dia( $ahora ) >= dip_sagec_minutos( dip_sagec_hora_limite() ) ) return 'ya son más de las ' . dip_sagec_hora_limite();
	$nueva = dip_urgente_entrega_de_hoy( $ahora );
	if ( '' === $nueva ) return 'hoy no sale ningún envío';
	$antes = (string) dip_eh_fecha( $p );
	if ( $antes === $nueva ) return 'ya es para el ' . dip_fecha_larga( $nueva );
	if ( $antes && $antes < $nueva ) return 'la entrega era el ' . dip_fecha_larga( $antes ) . ', antes que mañana';
	$d = dip_sagec_datos( $p );
	if ( $d['provincia'] && dip_mrw_cierra( $nueva, $d['provincia'], $d['ciudad'] ) ) return 'MRW no reparte el ' . dip_fecha_larga( $nueva ) . ' en ' . ( $d['ciudad'] ?: $d['cp'] ) . ' (festivo)';
	dip_guardar_entrega( $p, $nueva, 'envio' );
	$p->save();
	$usuario = wp_get_current_user();
	$p->add_order_note( sprintf(
		'Entrega adelantada del %s al %s (%s). Sin suplemento de urgente: el pedido ya estaba pagado.%s',
		$antes ? dip_fecha_larga( $antes ) : 'sin día',
		dip_fecha_larga( $nueva ),
		$usuario && $usuario->exists() ? $usuario->display_name : 'Acciones del pedido',
		dip_eh_es_sabado( $nueva ) ? ' Es sábado: el cliente no ha pagado el suplemento de sábado.' : ''
	) );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	dip_sagec_crear( $p, '«Adelantar a mañana»', true, $ahora );
	$GLOBALS['dip_sagec_nuevos_mano'] = array();
	return true;
}

add_filter( 'woocommerce_order_actions', static function ( $acciones, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : ( $GLOBALS['theorder'] ?? null );
	if ( ! $pedido instanceof WC_Order || dip_eh_es_recogida( $pedido ) || '' !== (string) $pedido->get_meta( '_dip_mrw_envio' ) ) return $acciones;
	$nueva = dip_urgente_entrega_de_hoy();
	if ( '' !== $nueva && (string) dip_eh_fecha( $pedido ) > $nueva ) $acciones['dip_urgente_adelantar'] = 'Adelantar a mañana (' . dip_fecha_larga( $nueva ) . ') y crear el envío';
	return $acciones;
}, 21, 2 );

add_action( 'woocommerce_order_action_dip_urgente_adelantar', static function ( $pedido ) {
	$r = dip_urgente_adelantar( $pedido );
	if ( true !== $r && class_exists( 'WC_Admin_Meta_Boxes' ) ) WC_Admin_Meta_Boxes::add_error( 'No se ha adelantado: ' . $r . '.' );
} );
