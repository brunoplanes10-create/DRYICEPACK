<?php
/**
 * Idioma de la visita (castellano, catalán o inglés) y textos propios de la tienda en los tres idiomas.
 * Los textos de WordPress y WooCommerce salen de sus paquetes oficiales de idioma; aquí solo los nuestros.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * es · ca · en.
 * - Páginas de contenido: manda la dirección (/ca/…, /en/…; sin prefijo es castellano).
 * - Carrito, checkout, Mi cuenta, /factura/ y peticiones AJAX/REST (no tienen versión por idioma):
 *   el idioma de la última página de contenido visitada, guardado en la cookie dip_idioma.
 */
function dip_idioma() {
	static $idioma = null;
	if ( null !== $idioma ) return $idioma;
	$ruta = dip_ruta_actual();
	if ( preg_match( '#^(ca|en)(/|$)#', $ruta, $m ) ) return $idioma = $m[1];
	$usa_cookie = wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || isset( $_GET['wc-ajax'] ) || dip_es_ruta_de_tienda( $ruta ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $usa_cookie ) return $idioma = 'es';
	$cookie = isset( $_COOKIE['dip_idioma'] ) ? sanitize_key( wp_unslash( $_COOKIE['dip_idioma'] ) ) : '';
	return $idioma = in_array( $cookie, array( 'ca', 'en' ), true ) ? $cookie : 'es';
}

/** Ruta pedida sin la carpeta de instalación ni barras: "ca/empreses". */
function dip_ruta_actual() {
	$ruta = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $ruta, $base ) ) $ruta = trim( substr( $ruta, strlen( $base ) ), '/' );
	return rawurldecode( $ruta );
}

/** ¿Es el carrito, el checkout, Mi cuenta o /factura/ (o algo dentro de ellos, como pedido-recibido)? */
function dip_es_ruta_de_tienda( $ruta ) {
	static $rutas = null;
	if ( null === $rutas ) {
		$rutas = array( 'factura' );
		foreach ( array( 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id' ) as $opcion ) {
			$id = (int) get_option( $opcion );
			if ( $id > 0 && function_exists( 'get_page_uri' ) && get_post( $id ) ) $rutas[] = trim( (string) get_page_uri( $id ), '/' );
		}
		$rutas = array_filter( $rutas );
	}
	foreach ( $rutas as $r ) {
		if ( $ruta === $r || 0 === strpos( $ruta, $r . '/' ) ) return true;
	}
	return false;
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
	if ( is_page( 'factura' ) ) return;
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

	// Suplemento de sábado con IVA, del mismo dato que cobra el checkout (9,70 € + IVA = 11,74 €)
	$sabado    = round( ( defined( 'DIP_SATURDAY_SURCHARGE_EXCL_TAX' ) ? (float) DIP_SATURDAY_SURCHARGE_EXCL_TAX : 9.70 ) * ( function_exists( 'dip_factor_iva' ) ? dip_factor_iva() : 1.21 ), 2 );
	$sabado_es = function_exists( 'dip_euros' ) ? dip_euros( $sabado ) : number_format( $sabado, 2, ',', '.' ) . ' €';
	$sabado_en = '€' . number_format( $sabado, 2, '.', ',' );
	$entero    = (string) (int) floor( $sabado );
	$de_ca     = preg_match( '/^1{1,2}$/', $entero ) ? 'd\'' : 'de ';             // "d'11,74 €" (onze), "de 12,10 €" (dotze)
	$a_en      = preg_match( '/^(8|11|18|8\d|8\d\d)$/', $entero ) ? 'an' : 'a'; // "an €11.74" (eleven), "a €12.10" (twelve)

	$es = array(
		'envio'              => 'Envío por mensajería · llega por la mañana',
		'recogida'           => 'Recoger en nuestra nave de Mataró',
		'recogida_nota'      => 'No es una oficina de la agencia: se recoge en nuestras instalaciones.',
		'recogida_horario'   => 'De lunes a sábado · te confirmamos la hora',
		'recogida_confirmar' => sprintf( 'Te llamamos o escribimos por WhatsApp para confirmar la hora antes de que vengas. El sábado lleva un suplemento de %s (IVA incluido).', $sabado_es ),
		'suplemento_sabado_recogida' => 'Recogida en sábado',
		'a_medida_titulo'    => 'Más de 150 kg fuera de la provincia de Barcelona.',
		'a_medida_texto'     => 'Este envío lo preparamos a medida y sale más caro: escríbenos y te decimos precio y día. Si lo recoges en Mataró, puedes pedirlo ya y te confirmamos el día de recogida.',
		'a_medida_whatsapp'  => 'Hola, necesito más de 150 kg de hielo seco fuera de la provincia de Barcelona. Código postal: ',
		'recogida_lista_asunto'    => 'Recogida de tu pedido %s: día y hora',
		'recogida_lista_hola'      => 'Hola, %s. Tu hielo seco estará listo en nuestra nave de Mataró:',
		'recogida_lista_mapa'      => 'Cómo llegar',
		'recogida_lista_pago'      => 'Se paga en efectivo al recogerlo: %s.',
		'recogida_lista_seguridad' => 'Para el viaje: la caja va en el maletero y con el coche ventilado. No la abras en un sitio cerrado y no toques el hielo con la piel. Usa guantes térmicos o pinzas. Todas las normas: dryicepack.es/seguridad-del-hielo-seco/',
		'recogida_lista_cambio'    => 'Si necesitas cambiar la hora, llámanos al %1$s o escríbenos por WhatsApp al %2$s.',
		'recogida_lejos'     => 'Has elegido recoger en Mataró (Barcelona) y tu código postal está lejos. Si prefieres que te lo enviemos, elige el envío.',
		'max_kg'             => 'El máximo por pedido online es de %d kg. Para más cantidad, pide precio por volumen.',
		'max_kg_ahora'       => 'El máximo por pedido es de %1$d kg y ya llevas %2$s kg. Para más cantidad, pide precio por volumen.',
		'fuera_peninsula'    => 'Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.',
		'paso_contacto'      => 'Contacto',
		'paso_entrega'       => 'Entrega',
		'paso_pago'          => 'Pago',
		'como_recibes'       => '¿Cómo lo recibes?',
		'que_dia'            => '¿Qué día lo necesitas?',
		'que_dia_recogida'   => '¿Qué día pasas a recogerlo?',
		'otra_fecha'         => 'Otra fecha',
		'otra_fecha_aria'    => 'Elegir otra fecha en el calendario',
		'cal_envio'          => 'Calendario de entrega',
		'cal_recogida'       => 'Calendario de recogida',
		'cal_anterior'       => 'Mes anterior',
		'cal_siguiente'      => 'Mes siguiente',
		'cal_no_disponible'  => 'no disponible',
		'cal_festivo_mrw'    => 'MRW no reparte en tu zona (festivo)',
		'cal_leyenda_envio'  => 'Puedes pedirlo para cualquier día de los próximos %d. Sin reparto en domingo, lunes ni festivos.',
		'cal_leyenda_recogida' => 'Puedes recogerlo cualquier día de los próximos %d, de lunes a sábado.',
		'cal_sabado_envio'   => 'Sábado según zona: +%s (IVA incluido).',
		'cal_sabado_recogida' => 'Sábado: +%s (IVA incluido).',
		'sabado'             => 'Sábado según zona',
		'sabado_nota'        => 'El reparto en sábado depende del código postal. Si el tuyo no lo tiene, te llamamos antes de enviar.',
		'suplemento_sabado'  => 'Entrega en sábado',
		'corte'              => 'Pide antes de las 12:00 para no perder la primera fecha.',
		'fecha_obligatoria'  => 'Elige el día de entrega.',
		'fecha_no_valida'    => 'Ese día ya no está disponible (a partir de las 12:00 la primera fecha cambia). Elige otro.',
		'fecha_no_disponible' => 'Ese día no está disponible. Elige otro en el calendario.',
		'fecha_festivo_destino' => 'MRW no reparte ese día en tu zona (festivo). Elige otro.',
		'mrw_festivo'        => 'MRW no reparte el %1$s en %2$s (festivo).',
		'mrw_festivos'       => 'MRW no reparte el %1$s en %2$s (festivos).',
		'mrw_y'              => ' y el ',
		'mrw_coma'           => ', el ',
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
		'recogida'           => 'Recollir a la nostra nau de Mataró',
		'recogida_nota'      => 'No és una oficina de l\'agència: es recull a les nostres instal·lacions.',
		'recogida_horario'   => 'De dilluns a dissabte · et confirmem l\'hora',
		'recogida_confirmar' => sprintf( 'Et truquem o t\'escrivim per WhatsApp per confirmar l\'hora abans que vinguis. El dissabte té un suplement %s%s (IVA inclòs).', $de_ca, $sabado_es ),
		'suplemento_sabado_recogida' => 'Recollida en dissabte',
		'a_medida_titulo'    => 'Més de 150 kg fora de la província de Barcelona.',
		'a_medida_texto'     => 'Aquest enviament el preparem a mida i surt més car: escriu-nos i et diem preu i dia. Si el reculls a Mataró, ja el pots demanar i et confirmem el dia de recollida.',
		'a_medida_whatsapp'  => 'Hola, necessito més de 150 kg de gel sec fora de la província de Barcelona. Codi postal: ',
		'recogida_lista_asunto'    => 'Recollida de la teva comanda %s: dia i hora',
		'recogida_lista_hola'      => 'Hola, %s. El teu gel sec estarà a punt a la nostra nau de Mataró:',
		'recogida_lista_mapa'      => 'Com arribar-hi',
		'recogida_lista_pago'      => 'Es paga en efectiu en recollir-lo: %s.',
		'recogida_lista_seguridad' => 'Per al viatge: la caixa va al maleter i amb el cotxe ventilat. No l\'obris en un lloc tancat i no toquis el gel amb la pell. Fes servir guants tèrmics o pinces. Totes les normes: dryicepack.es/ca/seguretat-del-gel-sec/',
		'recogida_lista_cambio'    => 'Si has de canviar l\'hora, truca\'ns al %1$s o escriu-nos per WhatsApp al %2$s.',
		'recogida_lejos'     => 'Has triat recollir a Mataró (Barcelona) i el teu codi postal és lluny. Si prefereixes que te l\'enviem, tria l\'enviament.',
		'max_kg'             => 'El màxim per comanda en línia és de %d kg. Per a quantitats més grans, demana preu per volum.',
		'max_kg_ahora'       => 'El màxim per comanda és de %1$d kg i ja en tens %2$s kg a la cistella. Per a quantitats més grans, demana preu per volum.',
		'fuera_peninsula'    => 'Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.',
		'paso_contacto'      => 'Contacte',
		'paso_entrega'       => 'Entrega',
		'paso_pago'          => 'Pagament',
		'como_recibes'       => 'Com el vols rebre?',
		'que_dia'            => 'Quin dia el necessites?',
		'que_dia_recogida'   => 'Quin dia el passes a recollir?',
		'otra_fecha'         => 'Una altra data',
		'otra_fecha_aria'    => 'Triar una altra data al calendari',
		'cal_envio'          => 'Calendari d\'entrega',
		'cal_recogida'       => 'Calendari de recollida',
		'cal_anterior'       => 'Mes anterior',
		'cal_siguiente'      => 'Mes següent',
		'cal_no_disponible'  => 'no disponible',
		'cal_festivo_mrw'    => 'MRW no reparteix a la teva zona (festiu)',
		'cal_leyenda_envio'  => 'Pots demanar-lo per a qualsevol dia dels pròxims %d. Sense repartiment en diumenge, dilluns ni festius.',
		'cal_leyenda_recogida' => 'Pots recollir-lo qualsevol dia dels pròxims %d, de dilluns a dissabte.',
		'cal_sabado_envio'   => 'Dissabte segons la zona: +%s (IVA inclòs).',
		'cal_sabado_recogida' => 'Dissabte: +%s (IVA inclòs).',
		'sabado'             => 'Dissabte segons la zona',
		'sabado_nota'        => 'El repartiment en dissabte depèn del codi postal. Si el teu no el té, et truquem abans d\'enviar.',
		'suplemento_sabado'  => 'Entrega en dissabte',
		'corte'              => 'Demana abans de les 12:00 per no perdre la primera data.',
		'fecha_obligatoria'  => 'Tria el dia d\'entrega.',
		'fecha_no_valida'    => 'Aquest dia ja no està disponible (a partir de les 12:00 canvia la primera data). Tria\'n un altre.',
		'fecha_no_disponible' => 'Aquest dia no està disponible. Tria\'n un altre al calendari.',
		'fecha_festivo_destino' => 'MRW no reparteix aquest dia a la teva zona (festiu). Tria\'n un altre.',
		'mrw_festivo'        => 'MRW no reparteix el %1$s a %2$s (festiu).',
		'mrw_festivos'       => 'MRW no reparteix el %1$s a %2$s (festius).',
		'mrw_y'              => ' i el ',
		'mrw_coma'           => ', el ',
		'fecha_entrega'      => 'Entrega',
		'fecha_recogida'     => 'Recollida',
		'nombre'             => 'Nom i cognoms',
		'email'              => 'Correu electrònic',
		'telefono'           => 'Telèfon',
		'telefono_ayuda'     => 'El repartidor et truca si no troba l\'adreça.',
		'direccion'          => 'Adreça',
		'direccion_ph'       => 'Carrer i número',
		'piso'               => 'Pis, porta o nau (opcional)',
		'cp'                 => 'Codi postal',
		'poblacion'          => 'Població',
		'empresa_check'      => 'Compro per a una empresa o autònom (factura completa)',
		'razon_social'       => 'Raó social',
		'nif'                => 'NIF / CIF',
		'direccion_fiscal'   => 'Adreça fiscal, si és diferent (opcional)',
		'notas'              => 'Indicacions per a l\'entrega (opcional)',
		'notas_ph'           => 'Horari, accés a la nau, persona de contacte…',
		'cp_no_valido'       => 'Escriu un codi postal de 5 xifres.',
		'telefono_no_valido' => 'Escriu un telèfon d\'almenys 9 xifres.',
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
		'factura_mensual_d'  => 'S\'afegeix a la factura d\'aquest mes del teu compte d\'empresa.',
		'tienes_cuenta'      => 'Tens compte d\'empresa?',
		'entrar'             => 'Entra-hi i no hauràs d\'omplir res.',
		'factura_titulo'     => 'Necessites la factura a nom de la teva empresa?',
		'factura_texto'      => 'Deixa les teves dades fiscals i te l\'enviem per correu.',
		'factura_boton'      => 'Demanar factura',
		'email_factura_asunto'   => 'Comanda %s: necessites factura amb les teves dades?',
		'email_factura_titulo'   => 'Factura a nom teu?',
		'email_factura_cuerpo'   => 'Gràcies per la comanda %s. Si necessites una factura completa a nom de la teva empresa o amb el teu NIF, deixa\'ns les dades en aquest enllaç i te l\'enviem per correu. Si no la necessites, no has de fer res.',
		'email_factura_boton'    => 'Omplir les dades de facturació',
		'resena'             => 'Tot bé amb la comanda? La teva ressenya a Google ajuda altres persones a trobar-nos.',
		'resena_boton'       => 'Deixar una ressenya',
	);

	$en = array(
		'envio'              => 'Courier delivery · arrives in the morning',
		'recogida'           => 'Collect from our warehouse in Mataró',
		'recogida_nota'      => 'This is not a courier depot: you collect from our own premises.',
		'recogida_horario'   => 'Monday to Saturday · we confirm the time',
		'recogida_confirmar' => sprintf( 'We call you or message you on WhatsApp to confirm the time before you come. Saturday has %s %s surcharge (VAT included).', $a_en, $sabado_en ),
		'suplemento_sabado_recogida' => 'Saturday collection',
		'a_medida_titulo'    => 'Over 150 kg outside the province of Barcelona.',
		'a_medida_texto'     => 'We arrange this delivery individually and it costs more: message us and we will give you a price and a day. If you collect in Mataró, you can order now and we will confirm the collection day.',
		'a_medida_whatsapp'  => 'Hello, I need more than 150 kg of dry ice outside the province of Barcelona. Postcode: ',
		'recogida_lista_asunto'    => 'Collection of your order %s: day and time',
		'recogida_lista_hola'      => 'Hello %s, your dry ice will be ready at our warehouse in Mataró:',
		'recogida_lista_mapa'      => 'How to get there',
		'recogida_lista_pago'      => 'Payment is in cash on collection: %s.',
		'recogida_lista_seguridad' => 'For the journey: put the box in the boot and keep the car ventilated. Do not open it in a closed space and do not touch the ice with bare skin. Use thermal gloves or tongs. All the rules: dryicepack.es/en/dry-ice-safety/',
		'recogida_lista_cambio'    => 'If you need to change the time, call us on %1$s or message us on WhatsApp at %2$s.',
		'recogida_lejos'     => 'You chose collection in Mataró (Barcelona) and your postcode is far away. If you want it delivered, choose delivery.',
		'max_kg'             => 'The online maximum is %d kg per order. For larger quantities, ask for a volume quote.',
		'max_kg_ahora'       => 'The maximum is %1$d kg per order and your basket already has %2$s kg. For larger quantities, ask for a volume quote.',
		'fuera_peninsula'    => 'We only deliver to mainland Spain: not to the Balearic Islands, the Canary Islands, Ceuta or Melilla.',
		'paso_contacto'      => 'Contact',
		'paso_entrega'       => 'Delivery',
		'paso_pago'          => 'Payment',
		'como_recibes'       => 'How do you want it?',
		'que_dia'            => 'Which day do you need it?',
		'que_dia_recogida'   => 'Which day will you collect it?',
		'otra_fecha'         => 'Another date',
		'otra_fecha_aria'    => 'Choose another date from the calendar',
		'cal_envio'          => 'Delivery calendar',
		'cal_recogida'       => 'Collection calendar',
		'cal_anterior'       => 'Previous month',
		'cal_siguiente'      => 'Next month',
		'cal_no_disponible'  => 'not available',
		'cal_festivo_mrw'    => 'MRW does not deliver to your area (public holiday)',
		'cal_leyenda_envio'  => 'You can order for any day in the next %d. No deliveries on Sundays, Mondays or public holidays.',
		'cal_leyenda_recogida' => 'You can collect on any day in the next %d, Monday to Saturday.',
		'cal_sabado_envio'   => 'Saturday, area-dependent: +%s (VAT included).',
		'cal_sabado_recogida' => 'Saturday: +%s (VAT included).',
		'sabado'             => 'Saturday, area-dependent',
		'sabado_nota'        => 'Saturday delivery depends on the postcode. If yours is not covered, we call you before shipping.',
		'suplemento_sabado'  => 'Saturday delivery',
		'corte'              => 'Order before 12:00 to keep the earliest date.',
		'fecha_obligatoria'  => 'Choose a delivery day.',
		'fecha_no_valida'    => 'That day is no longer available (after 12:00 the earliest date changes). Please choose another.',
		'fecha_no_disponible' => 'That day is not available. Please choose another from the calendar.',
		'fecha_festivo_destino' => 'MRW does not deliver to your area that day (public holiday). Please choose another.',
		'mrw_festivo'        => 'MRW does not deliver on %1$s in %2$s (public holiday).',
		'mrw_festivos'       => 'MRW does not deliver on %1$s in %2$s (public holidays).',
		'mrw_y'              => ' and ',
		'mrw_coma'           => ', ',
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
		'perecedero'         => 'Dry ice sublimates: as a product that deteriorates rapidly, it is excluded from the right of withdrawal (art. 103.d TRLGDCU).',
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
