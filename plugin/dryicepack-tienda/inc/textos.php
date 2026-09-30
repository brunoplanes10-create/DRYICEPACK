<?php
/**
 * Idioma de la visita (castellano, catalán o inglés) y textos propios de la tienda en los tres idiomas.
 * Los textos de WordPress y WooCommerce salen de sus paquetes oficiales de idioma; aquí solo los nuestros.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** es · ca · en. Por la URL (/ca/, /en/) y, en páginas sin prefijo (carrito, checkout, AJAX), por la cookie de la visita. */
function dip_idioma() {
	static $idioma = null;
	if ( null !== $idioma ) return $idioma;
	$ruta = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $ruta, $base ) ) $ruta = trim( substr( $ruta, strlen( $base ) ), '/' );
	if ( preg_match( '#^(ca|en)(/|$)#', $ruta, $m ) ) return $idioma = $m[1];
	$cookie = isset( $_COOKIE['dip_idioma'] ) ? sanitize_key( wp_unslash( $_COOKIE['dip_idioma'] ) ) : '';
	return $idioma = in_array( $cookie, array( 'ca', 'en' ), true ) ? $cookie : 'es';
}

function dip_locale_de( $idioma ) {
	return array( 'ca' => 'ca', 'en' => 'en_US' )[ $idioma ] ?? 'es_ES';
}

/* WordPress y WooCommerce hablan el idioma de la visita (paquetes de idioma instalados en Ajustes → Generales). */
function dip_filtrar_locale( $locale ) {
	if ( ! empty( $GLOBALS['wp_locale_switcher'] ) && is_locale_switched() ) return $locale; // un switch_to_locale() explícito manda (y antes de que exista el conmutador, no se toca)
	if ( is_admin() && ! wp_doing_ajax() ) return $locale;
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && empty( $_COOKIE['dip_idioma'] ) ) return $locale;
	$idioma = dip_idioma();
	return 'es' === $idioma ? $locale : dip_locale_de( $idioma );
}
add_filter( 'locale', 'dip_filtrar_locale', 20 );
add_filter( 'determine_locale', 'dip_filtrar_locale', 20 );

/* Recordar el idioma de la visita para el carrito, el checkout y los emails. */
add_action( 'template_redirect', static function () {
	if ( is_admin() || wp_doing_ajax() ) return;
	if ( function_exists( 'is_woocommerce' ) && ( is_cart() || is_checkout() || is_account_page() ) ) return;
	if ( is_404() || is_feed() ) return;
	$idioma = dip_idioma();
	$actual = isset( $_COOKIE['dip_idioma'] ) ? sanitize_key( wp_unslash( $_COOKIE['dip_idioma'] ) ) : '';
	if ( $actual === $idioma || headers_sent() ) return;
	if ( 'es' === $idioma && '' === $actual ) return; // castellano por defecto: no hace falta cookie
	setcookie( 'dip_idioma', $idioma, array(
		'expires'  => time() + 90 * DAY_IN_SECONDS,
		'path'     => COOKIEPATH ?: '/',
		'secure'   => is_ssl(),
		'httponly' => false,
		'samesite' => 'Lax',
	) );
	$_COOKIE['dip_idioma'] = $idioma;
}, 1 );

/** Textos propios de la tienda. $idioma null = idioma de la visita. */
function dip_textos_tienda( $idioma = null ) {
	$idioma = $idioma ?: dip_idioma();
	static $cache = array();
	if ( isset( $cache[ $idioma ] ) ) return $cache[ $idioma ];

	$es = array(
		'envio'              => 'Envío por mensajería · llega por la mañana',
		'recogida'           => 'Recoger en nuestra nave de Mataró · Gratis',
		'recogida_nota'      => 'No es una oficina de la agencia: se recoge en nuestras instalaciones.',
		'recogida_horario'   => 'L–V 9:00–18:00, con aviso previo',
		'recogida_lejos'     => 'Has elegido recoger en Mataró (Barcelona) y tu código postal está lejos. Si prefieres que te lo enviemos, elige el envío.',
		'max_kg'             => 'El máximo por pedido online es de %d kg. Para más cantidad, pide precio por volumen.',
		'max_kg_ahora'       => 'El máximo por pedido es de %1$d kg y ya llevas %2$s kg. Para más cantidad, pide precio por volumen.',
		'fuera_peninsula'    => 'Enviamos a la península. Para Baleares, Canarias, Ceuta o Melilla escríbenos y estudiamos tu caso.',
		'paso_contacto'      => 'Contacto',
		'paso_entrega'       => 'Entrega',
		'paso_pago'          => 'Pago',
		'como_recibes'       => '¿Cómo lo recibes?',
		'que_dia'            => '¿Qué día lo necesitas?',
		'que_dia_recogida'   => '¿Qué día pasas a recogerlo?',
		'sabado'             => 'Sábado según zona',
		'sabado_nota'        => 'El reparto en sábado depende del código postal. Si el tuyo no lo tiene, te llamamos antes de enviar.',
		'suplemento_sabado'  => 'Entrega en sábado',
		'corte'              => 'Pedidos antes de las 12:00 salen hoy.',
		'fecha_obligatoria'  => 'Elige el día de entrega.',
		'fecha_no_valida'    => 'Ese día ya no está disponible (a partir de las 12:00 la primera fecha cambia). Elige otro.',
		'fecha_entrega'      => 'Entrega',
		'fecha_recogida'     => 'Recogida',
		'nombre'             => 'Nombre y apellidos',
		'email'              => 'Correo electrónico',
		'telefono'           => 'Teléfono',
		'telefono_ayuda'     => 'El repartidor te llama si no encuentra la dirección.',
		'direccion'          => 'Dirección',
		'direccion_ph'       => 'Calle y número',
		'piso'               => 'Piso, puerta o nave (opcional)',
		'cp'                 => 'Código postal',
		'poblacion'          => 'Población',
		'empresa_check'      => 'Compro para una empresa o autónomo (factura completa)',
		'razon_social'       => 'Razón social',
		'nif'                => 'NIF / CIF',
		'direccion_fiscal'   => 'Dirección fiscal, si es distinta (opcional)',
		'notas'              => 'Indicaciones para la entrega (opcional)',
		'notas_ph'           => 'Horario, acceso a la nave, persona de contacto…',
		'cp_no_valido'       => 'Escribe un código postal de 5 cifras.',
		'telefono_no_valido' => 'Escribe un teléfono de al menos 9 cifras.',
		'datos_fiscales'     => 'Para la factura completa necesitamos la razón social y el NIF/CIF.',
		'datos_fiscales_400' => 'En pedidos de más de %s necesitamos la razón social y el NIF/CIF para la factura.',
		'nif_no_valido'      => 'Revisa el NIF/CIF: no parece correcto.',
		'boton_pagar'        => 'Pagar',
		'boton_confirmar'    => 'Confirmar pedido',
		'perecedero'         => 'El hielo seco se sublima: por ser un producto que se deteriora rápidamente, no tiene derecho de desistimiento (art. 103.d TRLGDCU).',
		'condiciones'        => 'Condiciones de venta',
		'pago_rapido'        => 'O paga con Apple Pay / Google Pay',
		'efectivo'           => 'Efectivo al recoger',
		'efectivo_desc'      => 'En la recogida en Mataró se paga en efectivo en el momento.',
		'factura_mensual'    => 'Factura mensual',
		'factura_mensual_d'  => 'Se añade a la factura de este mes de tu cuenta de empresa.',
		'tienes_cuenta'      => '¿Tienes cuenta de empresa?',
		'entrar'             => 'Entra y no tendrás que rellenar nada.',
		'factura_titulo'     => '¿Necesitas la factura a nombre de tu empresa?',
		'factura_texto'      => 'Deja tus datos fiscales y te la enviamos por email.',
		'factura_boton'      => 'Pedir factura',
		'email_factura_asunto'   => 'Pedido %s: ¿necesitas factura con tus datos?',
		'email_factura_titulo'   => '¿Factura a tu nombre?',
		'email_factura_cuerpo'   => 'Gracias por tu pedido %s. Si necesitas una factura completa a nombre de tu empresa o con tu NIF, déjanos los datos en este enlace y te la enviamos por email. Si no la necesitas, no tienes que hacer nada.',
		'email_factura_boton'    => 'Rellenar datos de facturación',
		'resena'             => '¿Todo bien con el pedido? Tu reseña en Google ayuda a otros a encontrarnos.',
		'resena_boton'       => 'Dejar una reseña',
	);

	$ca = array(
		'envio'              => 'Enviament per missatgeria · arriba al matí',
		'recogida'           => 'Recollir a la nostra nau de Mataró · Gratuït',
		'recogida_nota'      => "No és una oficina de l'agència: es recull a les nostres instal·lacions.",
		'recogida_horario'   => 'Dl–dv 9:00–18:00, avisant abans',
		'recogida_lejos'     => "Has triat recollir a Mataró (Barcelona) i el teu codi postal és lluny. Si prefereixes que te l'enviem, tria l'enviament.",
		'max_kg'             => 'El màxim per comanda en línia és de %d kg. Per a més quantitat, demana preu per volum.',
		'max_kg_ahora'       => 'El màxim per comanda és de %1$d kg i ja en portes %2$s kg. Per a més quantitat, demana preu per volum.',
		'fuera_peninsula'    => "Enviem a la península. Per a les Balears, les Canàries, Ceuta o Melilla, escriu-nos i n'estudiem el cas.",
		'paso_contacto'      => 'Contacte',
		'paso_entrega'       => 'Lliurament',
		'paso_pago'          => 'Pagament',
		'como_recibes'       => 'Com el vols rebre?',
		'que_dia'            => 'Quin dia el necessites?',
		'que_dia_recogida'   => 'Quin dia el passes a recollir?',
		'sabado'             => 'Dissabte segons zona',
		'sabado_nota'        => 'El repartiment en dissabte depèn del codi postal. Si el teu no el té, et truquem abans d’enviar.',
		'suplemento_sabado'  => 'Lliurament en dissabte',
		'corte'              => 'Les comandes abans de les 12:00 surten avui.',
		'fecha_obligatoria'  => 'Tria el dia de lliurament.',
		'fecha_no_valida'    => 'Aquest dia ja no està disponible (a partir de les 12:00 canvia la primera data). Tria’n un altre.',
		'fecha_entrega'      => 'Lliurament',
		'fecha_recogida'     => 'Recollida',
		'nombre'             => 'Nom i cognoms',
		'email'              => 'Correu electrònic',
		'telefono'           => 'Telèfon',
		'telefono_ayuda'     => "El repartidor et truca si no troba l'adreça.",
		'direccion'          => 'Adreça',
		'direccion_ph'       => 'Carrer i número',
		'piso'               => 'Pis, porta o nau (opcional)',
		'cp'                 => 'Codi postal',
		'poblacion'          => 'Població',
		'empresa_check'      => 'Compro per a una empresa o autònom (factura completa)',
		'razon_social'       => 'Raó social',
		'nif'                => 'NIF / CIF',
		'direccion_fiscal'   => 'Adreça fiscal, si és diferent (opcional)',
		'notas'              => 'Indicacions per al lliurament (opcional)',
		'notas_ph'           => 'Horari, accés a la nau, persona de contacte…',
		'cp_no_valido'       => 'Escriu un codi postal de 5 xifres.',
		'telefono_no_valido' => 'Escriu un telèfon d’almenys 9 xifres.',
		'datos_fiscales'     => 'Per a la factura completa necessitem la raó social i el NIF/CIF.',
		'datos_fiscales_400' => 'En comandes de més de %s necessitem la raó social i el NIF/CIF per a la factura.',
		'nif_no_valido'      => 'Revisa el NIF/CIF: no sembla correcte.',
		'boton_pagar'        => 'Pagar',
		'boton_confirmar'    => 'Confirmar la comanda',
		'perecedero'         => 'El gel sec se sublima: com que és un producte que es deteriora ràpidament, no té dret de desistiment (art. 103.d TRLGDCU).',
		'condiciones'        => 'Condicions de venda',
		'pago_rapido'        => 'O paga amb Apple Pay / Google Pay',
		'efectivo'           => 'Efectiu en recollir',
		'efectivo_desc'      => 'A la recollida a Mataró es paga en efectiu en aquell moment.',
		'factura_mensual'    => 'Factura mensual',
		'factura_mensual_d'  => "S'afegeix a la factura d'aquest mes del teu compte d'empresa.",
		'tienes_cuenta'      => "Tens compte d'empresa?",
		'entrar'             => "Entra-hi i no hauràs d'omplir res.",
		'factura_titulo'     => 'Necessites la factura a nom de la teva empresa?',
		'factura_texto'      => "Deixa les teves dades fiscals i te l'enviem per correu.",
		'factura_boton'      => 'Demanar factura',
		'email_factura_asunto'   => 'Comanda %s: necessites factura amb les teves dades?',
		'email_factura_titulo'   => 'Factura a nom teu?',
		'email_factura_cuerpo'   => "Gràcies per la comanda %s. Si necessites una factura completa a nom de la teva empresa o amb el teu NIF, deixa'ns les dades en aquest enllaç i te l'enviem per correu. Si no la necessites, no has de fer res.",
		'email_factura_boton'    => 'Omplir les dades de facturació',
		'resena'             => 'Tot bé amb la comanda? La teva ressenya a Google ajuda altres persones a trobar-nos.',
		'resena_boton'       => 'Deixar una ressenya',
	);

	$en = array(
		'envio'              => 'Courier delivery · arrives in the morning',
		'recogida'           => 'Collect from our warehouse in Mataró · Free',
		'recogida_nota'      => 'This is not a courier depot: you collect from our own premises.',
		'recogida_horario'   => 'Mon–Fri 9:00–18:00, please call ahead',
		'recogida_lejos'     => 'You chose collection in Mataró (Barcelona) and your postcode is far away. If you want it delivered, choose delivery.',
		'max_kg'             => 'The online maximum is %d kg per order. For larger quantities, ask for a volume quote.',
		'max_kg_ahora'       => 'The maximum is %1$d kg per order and your basket already has %2$s kg. For larger quantities, ask for a volume quote.',
		'fuera_peninsula'    => 'We deliver to mainland Spain. For the Balearic or Canary Islands, Ceuta or Melilla, write to us.',
		'paso_contacto'      => 'Contact',
		'paso_entrega'       => 'Delivery',
		'paso_pago'          => 'Payment',
		'como_recibes'       => 'How do you want it?',
		'que_dia'            => 'Which day do you need it?',
		'que_dia_recogida'   => 'Which day will you collect it?',
		'sabado'             => 'Saturday, area-dependent',
		'sabado_nota'        => 'Saturday delivery depends on the postcode. If yours is not covered, we call you before shipping.',
		'suplemento_sabado'  => 'Saturday delivery',
		'corte'              => 'Orders placed before 12:00 ship today.',
		'fecha_obligatoria'  => 'Choose a delivery day.',
		'fecha_no_valida'    => 'That day is no longer available (after 12:00 the first date moves). Please choose another.',
		'fecha_entrega'      => 'Delivery',
		'fecha_recogida'     => 'Collection',
		'nombre'             => 'Full name',
		'email'              => 'Email',
		'telefono'           => 'Phone',
		'telefono_ayuda'     => 'The courier calls you if they cannot find the address.',
		'direccion'          => 'Address',
		'direccion_ph'       => 'Street and number',
		'piso'               => 'Floor, door or unit (optional)',
		'cp'                 => 'Postcode',
		'poblacion'          => 'Town',
		'empresa_check'      => 'I am buying for a company or as self-employed (full invoice)',
		'razon_social'       => 'Company name',
		'nif'                => 'Tax ID (NIF / CIF / VAT)',
		'direccion_fiscal'   => 'Billing address, if different (optional)',
		'notas'              => 'Delivery notes (optional)',
		'notas_ph'           => 'Opening hours, access, contact person…',
		'cp_no_valido'       => 'Enter a 5-digit postcode.',
		'telefono_no_valido' => 'Enter a phone number with at least 9 digits.',
		'datos_fiscales'     => 'For a full invoice we need the company name and tax ID.',
		'datos_fiscales_400' => 'For orders over %s we need the company name and tax ID for the invoice.',
		'nif_no_valido'      => 'Please check the tax ID: it does not look right.',
		'boton_pagar'        => 'Pay',
		'boton_confirmar'    => 'Place order',
		'perecedero'         => 'Dry ice sublimates: as goods that deteriorate rapidly, it has no right of withdrawal (art. 103.d TRLGDCU).',
		'condiciones'        => 'Terms of sale',
		'pago_rapido'        => 'Or pay with Apple Pay / Google Pay',
		'efectivo'           => 'Cash on collection',
		'efectivo_desc'      => 'Collections in Mataró are paid in cash at the time.',
		'factura_mensual'    => 'Monthly invoice',
		'factura_mensual_d'  => "Added to this month's invoice for your business account.",
		'tienes_cuenta'      => 'Have a business account?',
		'entrar'             => 'Log in and you will not need to fill in anything.',
		'factura_titulo'     => 'Need the invoice in your company name?',
		'factura_texto'      => 'Leave your tax details and we will email it to you.',
		'factura_boton'      => 'Request invoice',
		'email_factura_asunto'   => 'Order %s: do you need an invoice with your details?',
		'email_factura_titulo'   => 'Invoice in your name?',
		'email_factura_cuerpo'   => 'Thank you for order %s. If you need a full invoice in your company name or with your tax ID, leave your details at this link and we will email it to you. If you do not need it, there is nothing to do.',
		'email_factura_boton'    => 'Enter billing details',
		'resena'             => 'Everything fine with your order? A Google review helps others find us.',
		'resena_boton'       => 'Leave a review',
	);

	$tablas = array( 'es' => $es, 'ca' => $ca, 'en' => $en );
	return $cache[ $idioma ] = ( $tablas[ $idioma ] ?? $es );
}

/* ---------- Emails: al cliente en el idioma del pedido, a la tienda siempre en castellano ---------- */
$GLOBALS['dip_locale_email'] = false;
function dip_email_cambiar_idioma( $locale ) {
	if ( $GLOBALS['dip_locale_email'] ) return;
	if ( determine_locale() === $locale ) return;
	$GLOBALS['dip_locale_email'] = switch_to_locale( $locale );
}
foreach ( array( 'customer_processing_order', 'customer_completed_order', 'customer_on_hold_order', 'customer_invoice', 'customer_note', 'customer_refunded_order' ) as $dip_id_email ) {
	add_filter( 'woocommerce_email_recipient_' . $dip_id_email, static function ( $destino, $pedido ) {
		if ( $pedido instanceof WC_Order ) {
			$idioma = (string) $pedido->get_meta( '_dip_idioma' );
			dip_email_cambiar_idioma( dip_locale_de( in_array( $idioma, array( 'ca', 'en' ), true ) ? $idioma : 'es' ) );
		}
		return $destino;
	}, 99, 2 );
}
foreach ( array( 'new_order', 'cancelled_order', 'failed_order' ) as $dip_id_email ) {
	add_filter( 'woocommerce_email_recipient_' . $dip_id_email, static function ( $destino ) {
		dip_email_cambiar_idioma( 'es_ES' );
		return $destino;
	}, 99 );
}
add_action( 'woocommerce_email_sent', static function () {
	if ( $GLOBALS['dip_locale_email'] ) {
		restore_previous_locale();
		$GLOBALS['dip_locale_email'] = false;
	}
} );
