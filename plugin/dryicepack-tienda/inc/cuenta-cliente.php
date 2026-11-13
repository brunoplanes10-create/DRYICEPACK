<?php
/**
 * Cuenta de cliente opcional, después de pagar (11/11/2026). En la página de gracias, debajo de la factura: «Guarda tu ficha
 * y la próxima vez no tendrás que volver a escribir tus datos» (no «pagas sin escribir nada»: la tarjeta se vuelve a
 * escribir). Nunca antes de pagar: comprar sigue siendo sin cuenta.
 *
 * - Solo en pedidos sin cuenta y si ese email no tiene ya una. Si la tiene: «Ya tienes cuenta con este email» y entrar.
 *   Si esa cuenta es de email comprobado (creada con este botón, que manda el enlace de la contraseña a ese email, o
 *   cuenta de empresa creada por el propietario), el pedido se marca para pasar a la cuenta cuando entre: «entra para ver
 *   todos tus pedidos». Si no (p. ej. una cuenta que alguien abrió con ese email sin comprobarlo), no se le pasa ningún
 *   pedido y el texto solo dice que la próxima vez entre y tendrá sus datos puestos.
 * - El botón crea una cuenta normal de cliente (no de empresa: esas las crea el propietario) con los datos del pedido y
 *   WooCommerce le manda el email de cuenta nueva con el enlace para poner la contraseña. No entra solo: aunque alguien
 *   viera el enlace de la página de gracias, sin ese email no puede entrar en la cuenta.
 * - Sus pedidos con ese email (este y los anteriores) pasan a la cuenta la primera vez que pone la contraseña o entra, no
 *   antes: WooCommerce solo enseña la página de gracias de un pedido con cuenta a quien ha entrado, y al crearla aún no
 *   tiene contraseña (se quedaría sin ver su pedido).
 * - Comprueba la clave del pedido y un nonce. No cambia los ajustes de WooCommerce: solo en esta petición, el usuario y la
 *   contraseña se generan solos (es lo que hace que el email lleve el enlace para ponerla).
 * - Diseño «La ficha de cliente» (zz-bocetos/cuenta-b.html), elegido el 11/11/2026. Estilos en el tema (paginas/tienda.css).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_cc_textos( $idioma = 'es', $usted = false ) {
	$tx = array(
		'es'       => array(
			'ficha'    => 'Ficha de cliente',
			'pedido'   => 'Pedido %s',
			'cliente'  => 'Cliente',
			'email'    => 'Email',
			'direccion' => 'Dirección',
			'ultimo'   => 'Último pedido',
			'sello_no' => 'Sin guardar',
			'sello_si' => 'Guardada',
			'titulo'   => 'Guarda tu ficha y la próxima vez no tendrás que volver a escribir tus datos.',
			'titulo_hecho' => 'Ficha guardada: la próxima vez, entra con tu cuenta y tus datos saldrán ya puestos.',
			'ventajas' => array( 'Tus datos, ya puestos al pagar', 'Todos tus pedidos en un sitio', 'Repetir un pedido con un clic' ),
			'boton'    => 'Crear mi cuenta',
			'nota'     => 'Te enviamos un email para poner la contraseña. Sin ella, nadie puede entrar en tu cuenta.',
			'hecho'    => 'Hecho. Revisa tu email para poner la contraseña.',
			'ya_tiene' => 'Ya tienes cuenta con este email: entra para ver todos tus pedidos.',
			'ya_tiene_datos' => 'Ya tienes cuenta con este email. Entra con ella la próxima vez y tus datos saldrán ya puestos al pagar.',
			'entrar'   => 'Entrar',
		),
		'es_usted' => array(
			'titulo'   => 'Guarde su ficha y la próxima vez no tendrá que volver a escribir sus datos.',
			'titulo_hecho' => 'Ficha guardada: la próxima vez, entre con su cuenta y sus datos saldrán ya puestos.',
			'ventajas' => array( 'Sus datos, ya puestos al pagar', 'Todos sus pedidos en un sitio', 'Repetir un pedido con un clic' ),
			'nota'     => 'Le enviamos un email para poner la contraseña. Sin ella, nadie puede entrar en su cuenta.',
			'hecho'    => 'Hecho. Revise su email para poner la contraseña.',
			'ya_tiene' => 'Ya tiene cuenta con este email: entre para ver todos sus pedidos.',
			'ya_tiene_datos' => 'Ya tiene cuenta con este email. Entre con ella la próxima vez y sus datos saldrán ya puestos al pagar.',
		),
		'ca'       => array(
			'ficha'    => 'Fitxa de client',
			'pedido'   => 'Comanda %s',
			'cliente'  => 'Client',
			'email'    => 'Correu',
			'direccion' => 'Adreça',
			'ultimo'   => 'Darrera comanda',
			'sello_no' => 'Sense desar',
			'sello_si' => 'Desada',
			'titulo'   => 'Desa la teva fitxa i la propera vegada no hauràs de tornar a escriure les teves dades.',
			'titulo_hecho' => 'Fitxa desada: la propera vegada, entra amb el teu compte i les teves dades sortiran ja posades.',
			'ventajas' => array( 'Les teves dades, ja posades en pagar', 'Totes les teves comandes en un lloc', 'Repetir una comanda amb un clic' ),
			'boton'    => 'Crear el meu compte',
			'nota'     => 'T\'enviem un correu per posar la contrasenya. Sense ella, ningú no pot entrar al teu compte.',
			'hecho'    => 'Fet. Mira el correu per posar la contrasenya.',
			'ya_tiene' => 'Ja tens compte amb aquest correu: entra per veure totes les teves comandes.',
			'ya_tiene_datos' => 'Ja tens compte amb aquest correu. Entra-hi la propera vegada i les teves dades sortiran ja posades en pagar.',
			'entrar'   => 'Entrar',
		),
		'ca_usted' => array(
			'titulo'   => 'Desi la seva fitxa i la propera vegada no haurà de tornar a escriure les seves dades.',
			'titulo_hecho' => 'Fitxa desada: la propera vegada, entri amb el seu compte i les seves dades sortiran ja posades.',
			'ventajas' => array( 'Les seves dades, ja posades en pagar', 'Totes les seves comandes en un lloc', 'Repetir una comanda amb un clic' ),
			'nota'     => 'Li enviem un correu per posar la contrasenya. Sense ella, ningú no pot entrar al seu compte.',
			'hecho'    => 'Fet. Miri el correu per posar la contrasenya.',
			'ya_tiene' => 'Ja té compte amb aquest correu: entri per veure totes les seves comandes.',
			'ya_tiene_datos' => 'Ja té compte amb aquest correu. Entri-hi la propera vegada i les seves dades sortiran ja posades en pagar.',
		),
		'en'       => array(
			'ficha'    => 'Customer record',
			'pedido'   => 'Order %s',
			'cliente'  => 'Customer',
			'email'    => 'Email',
			'direccion' => 'Address',
			'ultimo'   => 'Last order',
			'sello_no' => 'Not saved',
			'sello_si' => 'Saved',
			'titulo'   => 'Save your details and next time you won’t have to type them again.',
			'titulo_hecho' => 'Details saved: next time, log in and they will already be filled in at checkout.',
			'ventajas' => array( 'Your details, already filled in at checkout', 'All your orders in one place', 'Reorder with one click' ),
			'boton'    => 'Create my account',
			'nota'     => 'We will email you a link to set your password. Without it, nobody can get into your account.',
			'hecho'    => 'Done. Check your email to set your password.',
			'ya_tiene' => 'You already have an account with this email: log in to see all your orders.',
			'ya_tiene_datos' => 'You already have an account with this email. Log in next time and your details will already be filled in at checkout.',
			'entrar'   => 'Log in',
		),
	);
	$idioma = in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
	$t      = $tx[ $idioma ];
	if ( $usted && isset( $tx[ $idioma . '_usted' ] ) ) $t = array_merge( $t, $tx[ $idioma . '_usted' ] );
	return $t;
}

/** 'ofrecer' (pedido sin cuenta y email sin cuenta), 'ya_tiene' (el email ya tiene cuenta) o '' (no se dice nada). */
function dip_cc_estado( WC_Order $p ) {
	if ( $p->get_customer_id() || in_array( $p->get_status(), array( 'failed', 'pending', 'cancelled' ), true ) ) return '';
	$email = (string) $p->get_billing_email();
	if ( ! is_email( $email ) ) return '';
	return email_exists( $email ) ? 'ya_tiene' : 'ofrecer';
}

/* La ficha, en la página de gracias, después de «Ver mi pedido» (4) y de la factura (5) */
add_action( 'woocommerce_thankyou', static function ( $pedido_id ) {
	$p = wc_get_order( $pedido_id );
	if ( ! $p instanceof WC_Order ) return;
	$creada = isset( $_GET['dip_cuenta'] ) && 'creada' === $_GET['dip_cuenta'] && $p->get_meta( '_dip_cuenta_creada' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo cambia el texto
	$estado = $creada ? 'creada' : dip_cc_estado( $p );
	if ( 'ya_tiene' === $estado ) {
		// Sus pedidos de invitado pasan a la cuenta cuando entre (no antes: WooCommerce no enseña la página de gracias de
		// un pedido con cuenta a quien no ha entrado). Solo en cuentas de email comprobado: si no, cambia el texto.
		$usuario = get_user_by( 'email', (string) $p->get_billing_email() );
		if ( dip_cc_email_comprobado( $usuario ) ) {
			update_user_meta( $usuario->ID, '_dip_juntar_pedidos', 1 );
		} else {
			$estado = 'ya_tiene_datos';
		}
	}
	if ( $estado ) echo dip_cc_html( $p, $estado ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado dentro
}, 6 );

/**
 * ¿Cuenta con el email comprobado? La creada con el botón de la página de gracias (la contraseña se pone desde el enlace
 * que le llega a ese email) o la de empresa (la crea el propietario). Nunca las del equipo de la tienda.
 */
function dip_cc_email_comprobado( $usuario ) {
	if ( ! $usuario instanceof WP_User || user_can( $usuario, 'manage_woocommerce' ) ) return false;
	return (bool) get_user_meta( $usuario->ID, '_dip_cuenta_gracias', true ) || '1' === (string) get_user_meta( $usuario->ID, 'dip_cuenta_empresa', true );
}

function dip_cc_html( WC_Order $p, $estado ) {
	$idioma = dip_mp_idioma( $p );
	$usted  = dip_mp_de_usted( $p );
	$t      = dip_cc_textos( $idioma, $usted );
	if ( 'ya_tiene' === $estado || 'ya_tiene_datos' === $estado ) {
		return sprintf(
			'<p class="dip-cc-ya">%1$s <a class="enlace-flecha" href="%2$s"><span>%3$s</span>%4$s</a></p>',
			esc_html( $t[ $estado ] ),
			esc_url( wc_get_page_permalink( 'myaccount' ) ),
			esc_html( $t['entrar'] ),
			dip_mp_icono( 'flecha' )
		);
	}
	$tm     = dip_mp_textos( $idioma, $usted );
	$lineas = array();
	foreach ( $p->get_items() as $l ) $lineas[] = ( (int) $l->get_quantity() > 1 ? (int) $l->get_quantity() . "\u{00A0}× " : '' ) . dip_mp_nombre_linea( $l, $idioma, $tm );
	$filas = array(
		$t['cliente']   => trim( $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() ),
		$t['email']     => (string) $p->get_billing_email(),
		$t['direccion'] => implode( ' · ', array_filter( array( trim( $p->get_billing_address_1() . ( '' !== trim( $p->get_billing_address_2() ) ? ', ' . trim( $p->get_billing_address_2() ) : '' ) ), trim( $p->get_billing_postcode() . ' ' . $p->get_billing_city() ) ) ) ),
		$t['ultimo']    => implode( ', ', $lineas ),
	);
	$hecha = 'creada' === $estado;
	$h     = '<aside class="dip-cc' . ( $hecha ? ' dip-cc--hecha' : '' ) . '" aria-labelledby="dip-cc-t" data-revela>';
	$h    .= '<figure class="dip-cc__ficha" aria-label="' . esc_attr( $t['ficha'] ) . '">';
	$h    .= '<p class="dip-cc__cab"><span>' . esc_html( $t['ficha'] ) . '</span><span class="dip-cc__num">' . esc_html( sprintf( $t['pedido'], $p->get_order_number() ) ) . '</span></p><dl class="dip-cc__filas">';
	foreach ( $filas as $dt => $dd ) {
		if ( '' !== trim( (string) $dd ) ) $h .= '<div><dt>' . esc_html( $dt ) . '</dt><dd>' . esc_html( $dd ) . '</dd></div>';
	}
	$h .= '</dl><span class="dip-cc__sello">' . esc_html( $hecha ? $t['sello_si'] : $t['sello_no'] ) . '</span></figure>';
	$h .= '<div class="dip-cc__texto"><h2 id="dip-cc-t">' . esc_html( $hecha ? $t['titulo_hecho'] : $t['titulo'] ) . '</h2><ul class="dip-cc__lista">';
	foreach ( $t['ventajas'] as $v ) $h .= '<li>' . esc_html( $v ) . '</li>';
	$h .= '</ul>';
	if ( $hecha ) {
		$h .= '<p class="dip-cc__hecho" role="status">' . esc_html( $t['hecho'] ) . '</p>';
	} else {
		$h .= '<form class="dip-cc__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
			. '<input type="hidden" name="action" value="dip_cuenta_gracias">'
			. '<input type="hidden" name="pedido" value="' . (int) $p->get_id() . '">'
			. '<input type="hidden" name="clave" value="' . esc_attr( $p->get_order_key() ) . '">'
			. wp_nonce_field( 'dip_cuenta_gracias_' . $p->get_id(), 'dip_nonce', false, false )
			. '<button type="submit" class="boton boton--marino"><span>' . esc_html( $t['boton'] ) . '</span><span class="boton__flecha">' . dip_mp_icono( 'flecha' ) . '</span></button>'
			. '</form><p class="dip-cc__nota">' . esc_html( $t['nota'] ) . '</p>';
	}
	return $h . '</div></aside>';
}

/* El botón: crea la cuenta y vuelve a la página de gracias */
add_action( 'admin_post_nopriv_dip_cuenta_gracias', 'dip_cc_crear' );
add_action( 'admin_post_dip_cuenta_gracias', 'dip_cc_crear' ); // no «dip_crear_cuenta»: es la del escritorio (inc/formularios.php)

function dip_cc_crear() {
	$id     = isset( $_POST['pedido'] ) ? absint( $_POST['pedido'] ) : 0;
	$clave  = isset( $_POST['clave'] ) ? sanitize_text_field( wp_unslash( $_POST['clave'] ) ) : '';
	$nonce  = isset( $_POST['dip_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['dip_nonce'] ) ) : '';
	$p      = $id ? wc_get_order( $id ) : null;
	$volver = $p instanceof WC_Order ? $p->get_checkout_order_received_url() : home_url( '/' );
	if ( ! $p instanceof WC_Order || ! $p->key_is_valid( $clave ) || ! wp_verify_nonce( $nonce, 'dip_cuenta_gracias_' . $id ) || 'ofrecer' !== dip_cc_estado( $p ) ) {
		wp_safe_redirect( $volver );
		exit;
	}
	// El email de cuenta nueva, en el idioma del pedido
	$idioma = dip_mp_idioma( $p );
	if ( 'es' !== $idioma && function_exists( 'dip_locale_de' ) ) switch_to_locale( dip_locale_de( $idioma ) );
	$si = static fn() => 'yes';
	add_filter( 'pre_option_woocommerce_registration_generate_password', $si );
	add_filter( 'pre_option_woocommerce_registration_generate_username', $si );
	$cliente = wc_create_new_customer( (string) $p->get_billing_email(), '', '', array( 'first_name' => $p->get_billing_first_name(), 'last_name' => $p->get_billing_last_name() ) );
	remove_filter( 'pre_option_woocommerce_registration_generate_password', $si );
	remove_filter( 'pre_option_woocommerce_registration_generate_username', $si );
	if ( is_wp_error( $cliente ) ) {
		$p->add_order_note( 'No se ha podido crear la cuenta desde la página de gracias: ' . $cliente->get_error_message() );
		wp_safe_redirect( $volver );
		exit;
	}
	// Los datos del pedido, para que la próxima vez salgan puestos al pagar
	$c = new WC_Customer( $cliente );
	foreach ( array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'postcode', 'city', 'state', 'country', 'phone', 'email' ) as $k ) {
		$c->{"set_billing_{$k}"}( (string) $p->{"get_billing_{$k}"}() );
		if ( ! in_array( $k, array( 'phone', 'email', 'company' ), true ) ) $c->{"set_shipping_{$k}"}( (string) $p->{"get_billing_{$k}"}() );
	}
	$c->save();
	update_user_meta( $cliente, '_dip_juntar_pedidos', 1 ); // los pedidos pasan a la cuenta al entrar por primera vez (abajo)
	update_user_meta( $cliente, '_dip_cuenta_gracias', current_time( 'mysql' ) ); // email comprobado: sus pedidos de invitado de después también pasan
	$p = wc_get_order( $id );
	$p->update_meta_data( '_dip_cuenta_creada', current_time( 'mysql' ) );
	$p->add_order_note( 'El cliente ha creado su cuenta desde la página de gracias. Le llega el email para poner la contraseña.' );
	$p->save();
	wp_safe_redirect( add_query_arg( 'dip_cuenta', 'creada', $volver ) . '#dip-cc-t' );
	exit;
}

/*
 * La primera vez que pone la contraseña o entra, sus pedidos de invitado con ese email (este y los anteriores) pasan a la
 * cuenta: los ve en «Mi cuenta» con «Repetir». Una vez por marca: la pone el botón al crear la cuenta y la vuelve a poner
 * la página de gracias de cada pedido que haga después sin entrar («Ya tienes cuenta… entra para ver todos tus pedidos»).
 */
function dip_cc_juntar_pedidos( $usuario ) {
	if ( ! $usuario instanceof WP_User || ! get_user_meta( $usuario->ID, '_dip_juntar_pedidos', true ) ) return;
	delete_user_meta( $usuario->ID, '_dip_juntar_pedidos' );
	if ( function_exists( 'wc_update_new_customer_past_orders' ) ) wc_update_new_customer_past_orders( $usuario->ID );
}
add_action( 'password_reset', static function ( $usuario ) {
	dip_cc_juntar_pedidos( $usuario );
} );
add_action( 'wp_login', static function ( $login, $usuario ) {
	dip_cc_juntar_pedidos( $usuario );
}, 10, 2 );
