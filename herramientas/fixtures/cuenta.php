<?php
// Prueba local (herramientas/probar-cuenta.mjs): la cuenta creada desde la página de gracias. Solo desde 127.0.0.1.
// Se copia al plugin como zz-cuenta.php y se borra al terminar la prueba; no va en el .zip.
if ( ! in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) exit;
// Parche solo para el WordPress de prueba (Playground): su WooCommerce viene sin src/Admin/API/Reports/TimeInterval.php y la
// tabla de clientes de las estadísticas de WooCommerce Admin da un error fatal al cambiar un usuario (ya pasó el 14/10).
// No toca el plugin ni el tema: es un mu-plugin del servidor de prueba que se pone al empezar y se quita al terminar.
// El sistema de archivos de Playground no deja volver a crear un archivo con el mismo nombre después de borrarlo: cada
// pasada usa un nombre nuevo (?parche=NOMBRE) y lo borra al terminar (?quitar=NOMBRE).
if ( isset( $_GET['parche'] ) || isset( $_GET['quitar'] ) ) {
	$nombre = preg_replace( '/[^a-z0-9]/', '', strtolower( (string) ( $_GET['parche'] ?? $_GET['quitar'] ) ) );
	$mu     = '/wordpress/wp-content/mu-plugins/zz-prueba-wc-clientes-' . $nombre . '.php';
	if ( isset( $_GET['parche'] ) ) {
		if ( ! is_dir( dirname( $mu ) ) ) @mkdir( dirname( $mu ), 0777, true );
		@file_put_contents( $mu, "<?php\n// Prueba local (probar-cuenta.mjs): sin la tabla de clientes de WooCommerce Admin (archivo que falta en Playground)\nadd_action( 'init', static function () {\n\t\$c = 'Automattic\\\\WooCommerce\\\\Admin\\\\API\\\\Reports\\\\Customers\\\\DataStore';\n\tforeach ( array( 'woocommerce_new_customer', 'woocommerce_update_customer', 'profile_update' ) as \$h ) remove_action( \$h, array( \$c, 'update_registered_customer' ) );\n\tforeach ( array( 'added_user_meta', 'updated_user_meta' ) as \$h ) remove_action( \$h, array( \$c, 'update_registered_customer_via_last_active' ), 10 );\n}, 99 );\n" );
	} else {
		@unlink( $mu );
	}
	header( 'Content-Type: application/json; charset=utf-8' );
	echo json_encode( file_exists( $mu ) ); // aún sin WordPress cargado
	exit;
}
require '/wordpress/wp-load.php';
header( 'Content-Type: application/json; charset=utf-8' );
if ( isset( $_GET['comprobar'] ) ) { // ¿está activo el parche? (WooCommerce Admin ya no escucha profile_update)
	echo wp_json_encode( false === has_action( 'profile_update', array( 'Automattic\WooCommerce\Admin\API\Reports\Customers\DataStore', 'update_registered_customer' ) ) );
	exit;
}
if ( isset( $_GET['email'] ) ) {
	$u = get_user_by( 'email', sanitize_email( wp_unslash( $_GET['email'] ) ) );
	echo wp_json_encode( $u ? array(
		'id'            => $u->ID,
		'roles'         => array_values( $u->roles ),
		'enlace_clave'  => '' !== (string) $u->user_activation_key, // el enlace para poner la contraseña (email de cuenta nueva)
		'empresa'       => (string) get_user_meta( $u->ID, 'dip_cuenta_empresa', true ),
		'billing_cp'    => (string) get_user_meta( $u->ID, 'billing_postcode', true ),
		'billing_nombre' => (string) get_user_meta( $u->ID, 'billing_first_name', true ),
		'billing_tel'   => (string) get_user_meta( $u->ID, 'billing_phone', true ),
		'juntar'        => (string) get_user_meta( $u->ID, '_dip_juntar_pedidos', true ),
	) : null );
	exit;
}
if ( isset( $_GET['sin_comprobar'] ) ) { // una cuenta de cliente abierta con ese email sin comprobarlo (no con el botón)
	$correo = sanitize_email( wp_unslash( $_GET['sin_comprobar'] ) );
	$id     = email_exists( $correo ) ?: wp_insert_user( array( 'user_login' => 'sc' . substr( md5( $correo ), 0, 12 ), 'user_email' => $correo, 'user_pass' => wp_generate_password( 24 ), 'role' => 'customer' ) );
	echo wp_json_encode( is_wp_error( $id ) ? $id->get_error_message() : (int) $id );
	exit;
}
if ( isset( $_GET['entrar'] ) ) { // la primera vez que entra (como al poner la contraseña)
	$u = get_user_by( 'email', sanitize_email( wp_unslash( $_GET['entrar'] ) ) );
	if ( $u ) do_action( 'wp_login', $u->user_login, $u );
	echo wp_json_encode( (bool) $u );
	exit;
}
$p = wc_get_order( absint( $_GET['pedido'] ?? 0 ) );
echo wp_json_encode( $p ? array(
	'cliente' => $p->get_customer_id(),
	'creada'  => (string) $p->get_meta( '_dip_cuenta_creada' ),
	'notas'   => array_values( array_map( static fn( $n ) => $n->content, wc_get_order_notes( array( 'order_id' => $p->get_id() ) ) ) ),
) : null );
