<?php
/**
 * Cuentas de empresa: clientes que repiten.
 * - Se activan a mano en el perfil del usuario (o con un clic desde una solicitud de /empresas/).
 * - Precio propio: descuento en % sobre la tarifa.
 * - Pago con factura mensual y datos fiscales guardados.
 * - "Repetir pedido" en Mi cuenta y recordatorio de reposición opcional.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_es_cuenta_empresa( $usuario_id = null ) {
	$usuario_id = null === $usuario_id ? get_current_user_id() : (int) $usuario_id;
	return $usuario_id > 0 && '1' === (string) get_user_meta( $usuario_id, 'dip_cuenta_empresa', true );
}

function dip_descuento_cliente( $usuario_id = null ) {
	$usuario_id = null === $usuario_id ? get_current_user_id() : (int) $usuario_id;
	if ( ! dip_es_cuenta_empresa( $usuario_id ) ) return 0.0;
	return min( 60, max( 0, (float) get_user_meta( $usuario_id, 'dip_descuento', true ) ) );
}

/* ---------- Precio propio ---------- */
function dip_aplicar_descuento( $precio ) {
	if ( '' === $precio || null === $precio || is_admin() && ! wp_doing_ajax() ) return $precio;
	$d = dip_descuento_cliente();
	return $d > 0 ? round( (float) $precio * ( 1 - $d / 100 ), 2 ) : $precio;
}
foreach ( array( 'woocommerce_product_get_price', 'woocommerce_product_variation_get_price', 'woocommerce_variation_prices_price' ) as $dip_filtro ) {
	add_filter( $dip_filtro, 'dip_aplicar_descuento', 50 );
}
/* Las cachés de precios de variaciones tienen que distinguir a cada cliente con descuento. */
add_filter( 'woocommerce_get_variation_prices_hash', static function ( $hash ) {
	$hash[] = 'dip_desc_' . dip_descuento_cliente();
	return $hash;
} );

/* ---------- Perfil del usuario en el escritorio ---------- */
function dip_campos_perfil( $usuario ) {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	?>
	<h2>Cuenta de empresa Dryicepack</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">Cuenta de empresa</th>
			<td><label><input type="checkbox" name="dip_cuenta_empresa" value="1" <?php checked( dip_es_cuenta_empresa( $usuario->ID ) ); ?>> Activa: ve su precio, paga con factura mensual y no rellena datos fiscales</label></td>
		</tr>
		<tr>
			<th scope="row"><label for="dip_descuento">Descuento sobre tarifa</label></th>
			<td><input type="number" step="0.5" min="0" max="60" id="dip_descuento" name="dip_descuento" value="<?php echo esc_attr( get_user_meta( $usuario->ID, 'dip_descuento', true ) ); ?>" class="small-text"> %</td>
		</tr>
		<tr>
			<th scope="row"><label for="billing_nif">NIF / CIF</label></th>
			<td><input type="text" id="billing_nif" name="billing_nif" value="<?php echo esc_attr( get_user_meta( $usuario->ID, 'billing_nif', true ) ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row">Recordatorio de reposición</th>
			<td><label><input type="checkbox" name="dip_recordatorio" value="1" <?php checked( '1', get_user_meta( $usuario->ID, 'dip_recordatorio', true ) ); ?>> Ha aceptado recibir un aviso cuando le toca repetir el pedido</label></td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'dip_campos_perfil' );
add_action( 'edit_user_profile', 'dip_campos_perfil' );

function dip_guardar_perfil( $usuario_id ) {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! isset( $_POST['_wpnonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $usuario_id ) ) return;
	update_user_meta( $usuario_id, 'dip_cuenta_empresa', empty( $_POST['dip_cuenta_empresa'] ) ? '0' : '1' );
	update_user_meta( $usuario_id, 'dip_descuento', min( 60, max( 0, (float) str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['dip_descuento'] ?? '0' ) ) ) ) ) );
	update_user_meta( $usuario_id, 'billing_nif', dip_normalizar_nif( wp_unslash( $_POST['billing_nif'] ?? '' ) ) );
	update_user_meta( $usuario_id, 'dip_recordatorio', empty( $_POST['dip_recordatorio'] ) ? '0' : '1' );
}
add_action( 'personal_options_update', 'dip_guardar_perfil' );
add_action( 'edit_user_profile_update', 'dip_guardar_perfil' );

/* ---------- Checkout de una cuenta: casilla de empresa marcada y datos rellenos ---------- */
add_filter( 'woocommerce_checkout_get_value', static function ( $valor, $campo ) {
	if ( 'dip_empresa' === $campo && dip_es_cuenta_empresa() ) return 1;
	return $valor;
}, 10, 2 );

/* ---------- Mi cuenta: repetir pedido ---------- */
add_filter( 'woocommerce_valid_order_statuses_for_order_again', static function ( $estados ) {
	return array_unique( array_merge( (array) $estados, array( 'completed', 'processing' ) ) );
} );

function dip_url_repetir( WC_Order $pedido ) {
	return wp_nonce_url( add_query_arg( 'order_again', $pedido->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' );
}

add_action( 'woocommerce_account_dashboard', static function () {
	$usuario = get_current_user_id();
	$pedidos = wc_get_orders( array( 'customer_id' => $usuario, 'limit' => 5, 'status' => array( 'wc-processing', 'wc-completed' ), 'orderby' => 'date', 'order' => 'DESC' ) );
	$idioma  = dip_idioma();
	$tx      = array(
		'es' => array( 'titulo' => 'Tus últimos pedidos', 'repetir' => 'Repetir', 'nada' => 'Aún no hay pedidos en esta cuenta.', 'cuenta' => 'Cuenta de empresa activa', 'desc' => 'Descuento sobre tarifa: %s %%', 'pago' => 'Pago con factura mensual.', 'wa' => 'También puedes pedir por WhatsApp' ),
		'ca' => array( 'titulo' => 'Les teves últimes comandes', 'repetir' => 'Repetir', 'nada' => 'Encara no hi ha comandes en aquest compte.', 'cuenta' => "Compte d'empresa actiu", 'desc' => 'Descompte sobre tarifa: %s %%', 'pago' => 'Pagament amb factura mensual.', 'wa' => 'També pots demanar per WhatsApp' ),
		'en' => array( 'titulo' => 'Your latest orders', 'repetir' => 'Reorder', 'nada' => 'No orders on this account yet.', 'cuenta' => 'Business account active', 'desc' => 'Discount on list price: %s%%', 'pago' => 'Paid by monthly invoice.', 'wa' => 'You can also order by WhatsApp' ),
	)[ $idioma ] ?? array();
	echo '<section class="dip-cuenta">';
	if ( dip_es_cuenta_empresa( $usuario ) ) {
		$d = dip_descuento_cliente( $usuario );
		echo '<p class="dip-cuenta__estado"><strong>' . esc_html( $tx['cuenta'] ) . '</strong>';
		if ( $d > 0 ) echo ' · ' . esc_html( sprintf( $tx['desc'], wc_format_localized_decimal( $d ) ) );
		echo ' · ' . esc_html( $tx['pago'] ) . '</p>';
	}
	echo '<h2>' . esc_html( $tx['titulo'] ) . '</h2>';
	if ( ! $pedidos ) {
		echo '<p>' . esc_html( $tx['nada'] ) . '</p>';
	} else {
		echo '<ul class="dip-cuenta__pedidos">';
		foreach ( $pedidos as $p ) {
			$kg    = (float) $p->get_meta( '_dip_kg' );
			$fecha = $p->get_date_created() ? wp_date( 'd/m/Y', $p->get_date_created()->getTimestamp() ) : '';
			printf(
				'<li><span class="dip-cuenta__num">%s</span><span>%s</span><span>%s</span><span>%s</span><a class="button dip-boton" href="%s">%s</a></li>',
				esc_html( '#' . $p->get_order_number() ),
				esc_html( $fecha ),
				esc_html( $kg > 0 ? wc_format_localized_decimal( $kg ) . ' kg' : '' ),
				wp_kses_post( wc_price( $p->get_total() ) ),
				esc_url( dip_url_repetir( $p ) ),
				esc_html( $tx['repetir'] )
			);
		}
		echo '</ul>';
	}
	$c = dip_contacto();
	printf( '<p class="dip-cuenta__wa"><a href="%s" target="_blank" rel="noopener">%s · %s</a></p>', esc_url( 'https://wa.me/' . $c['whatsapp_num'] ), esc_html( $tx['wa'] ), esc_html( $c['whatsapp'] ) );
	echo '</section>';
}, 5 );

/* ---------- Recordatorio de reposición (tarea diaria, desactivado por defecto) ---------- */
add_action( 'dip_tarea_diaria', static function () {
	if ( ! dip_ajuste( 'recordatorios' ) ) return;
	$usuarios = get_users( array( 'meta_query' => array( 'relation' => 'AND', array( 'key' => 'dip_cuenta_empresa', 'value' => '1' ), array( 'key' => 'dip_recordatorio', 'value' => '1' ) ), 'fields' => array( 'ID', 'user_email' ), 'number' => 500 ) );
	foreach ( $usuarios as $u ) {
		$pedidos = wc_get_orders( array( 'customer_id' => $u->ID, 'limit' => 6, 'status' => array( 'wc-processing', 'wc-completed' ), 'orderby' => 'date', 'order' => 'DESC' ) );
		if ( count( $pedidos ) < 2 ) continue;
		$fechas = array_values( array_filter( array_map( static fn( $p ) => $p->get_date_created() ? $p->get_date_created()->getTimestamp() : 0, $pedidos ) ) );
		$huecos = array();
		for ( $i = 0; $i < count( $fechas ) - 1; $i++ ) $huecos[] = ( $fechas[ $i ] - $fechas[ $i + 1 ] ) / DAY_IN_SECONDS;
		$ritmo  = max( 3, (int) round( array_sum( $huecos ) / count( $huecos ) ) );
		$desde  = ( time() - $fechas[0] ) / DAY_IN_SECONDS;
		$avisado = (int) get_user_meta( $u->ID, 'dip_ultimo_recordatorio', true );
		if ( $desde < $ritmo || $avisado > $fechas[0] ) continue;

		$ultimo = $pedidos[0];
		$idioma = (string) $ultimo->get_meta( '_dip_idioma' ) ?: 'es';
		$tx     = array(
			'es' => array( 'Asunto' => '¿Repetimos tu pedido de hielo seco?', 'titulo' => '¿Repetimos el pedido?', 'cuerpo' => 'Sueles pedir cada %d días y tu último pedido fue el %s. Si lo necesitas otra vez, lo repites con un clic. Si pides antes de las 12:00, sale hoy.', 'boton' => 'Repetir pedido', 'baja' => 'No quiero más avisos' ),
			'ca' => array( 'Asunto' => 'Repetim la comanda de gel sec?', 'titulo' => 'Repetim la comanda?', 'cuerpo' => 'Acostumes a demanar cada %d dies i la darrera comanda va ser el %s. Si el tornes a necessitar, la repeteixes amb un clic. Si la fas abans de les 12:00, surt avui.', 'boton' => 'Repetir la comanda', 'baja' => 'No vull més avisos' ),
			'en' => array( 'Asunto' => 'Shall we repeat your dry ice order?', 'titulo' => 'Reorder?', 'cuerpo' => 'You usually order every %d days and your last order was on %s. If you need it again, reorder in one click. Orders placed before 12:00 ship today.', 'boton' => 'Reorder', 'baja' => 'Stop these reminders' ),
		)[ $idioma ] ?? array();
		$baja   = add_query_arg( array( 'dip_baja' => $u->ID, 't' => wp_hash( 'baja|' . $u->ID ) ), home_url( '/' ) );
		$cuerpo = '<p>' . esc_html( sprintf( $tx['cuerpo'], $ritmo, wp_date( 'd/m/Y', $fechas[0] ) ) ) . '</p>'
			. '<p style="margin:24px 0"><a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '" style="display:inline-block;padding:14px 22px;background:#1F3C55;color:#fff;text-decoration:none;border-radius:999px;font-weight:600">' . esc_html( $tx['boton'] ) . '</a></p>'
			. '<p style="font-size:12px"><a href="' . esc_url( $baja ) . '">' . esc_html( $tx['baja'] ) . '</a></p>';
		$mailer = WC()->mailer();
		$mailer->send( $u->user_email, $tx['Asunto'], $mailer->wrap_message( $tx['titulo'], $cuerpo ) );
		update_user_meta( $u->ID, 'dip_ultimo_recordatorio', time() );
	}
} );

/* Baja del recordatorio con un clic (enlace firmado). */
add_action( 'template_redirect', static function () {
	if ( empty( $_GET['dip_baja'] ) || empty( $_GET['t'] ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$id = absint( $_GET['dip_baja'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! hash_equals( wp_hash( 'baja|' . $id ), sanitize_text_field( wp_unslash( $_GET['t'] ) ) ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	update_user_meta( $id, 'dip_recordatorio', '0' );
	wp_die( esc_html__( 'Hecho: no recibirás más recordatorios de reposición.', 'dryicepack-tienda' ), 'Dryicepack', array( 'response' => 200 ) );
} );
