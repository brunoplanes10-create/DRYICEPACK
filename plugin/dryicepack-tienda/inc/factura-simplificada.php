<?php
/**
 * Factura simplificada automática para particulares.
 *
 * Decisión de la empresa (02/10/2026): si el pedido no supera los 400 € (IVA incluido) y el cliente no marca
 * "Compro para una empresa o autónomo", la web expide una factura simplificada y se la envía por email desde
 * info@dryicepack.es con el PDF adjunto. Si marca empresa (o da NIF), no se aplica: factura completa desde la
 * aplicación de la gestoría, como hasta ahora.
 *
 * - Cuándo: una sola vez por pedido y con el pedido pagado. Tarjeta: al pasar a "Procesando". Efectivo al recoger
 *   ('cod'): al pasar a "Completado". Nunca a cuentas de empresa, con NIF, con la casilla de empresa ni por encima
 *   de 400 €. Solo pedidos creados después de poner el módulo en marcha (los anteriores siguen el flujo de antes).
 * - Numeración: serie propia de la web, distinta de las de la gestoría (por defecto "W" + año: W2026-00001),
 *   correlativa y sin huecos. El contador vive en wp_options y avanza con un UPDATE atómico de "compara y cambia":
 *   si dos pedidos llegan a la vez, el segundo vuelve a leer y toma el número siguiente. Cada pedido se reserva
 *   antes con una fila propia (clave única de wp_options) para que dos procesos no lo facturen dos veces.
 *   La serie lleva el año, así que la numeración empieza de nuevo cada 1 de enero.
 * - Contenido: art. 7 del Real Decreto 1619/2012 (número y serie, fecha de expedición, fecha de la operación si es
 *   otra, emisor con NIF, bienes, tipo de IVA con "IVA incluido" y la cuota, total). PDF A4 de una página hecho aquí,
 *   sin librerías, con las fuentes estándar del PDF. Copia en wp-content/uploads/dryicepack-facturas/ (sin acceso
 *   desde la web) para poder reenviarla.
 *   Fecha de la operación: solo si es otro día que la expedición y ya ha pasado (entrega o cobro anticipado, la que
 *   sea antes). Con tarjeta se cobra el día de la factura: una entrega reservada para dentro de un mes no lo es.
 * - Libro: cada número se apunta también en wp_options (dip_fs_libro_W2026-00001) y se descarga por meses en CSV
 *   desde Dryicepack → Ajustes, para el libro registro de la gestoría. Estas facturas ya están expedidas: no se
 *   vuelven a facturar en la aplicación de la gestoría.
 * - El email sustituye al de "¿Necesitas factura?" (inc/factura.php): lleva el enlace a /factura/ para pedir la
 *   factura completa.
 * - Devoluciones y cancelaciones: la web no emite nada. Nota en el pedido y aviso por email para hacer la
 *   rectificativa en la aplicación de la gestoría.
 * - VeriFactu (Real Decreto 1007/2023): este módulo no lo cumple. El 1/1/2027 deja de emitir solo y desde el
 *   1/12/2026 lo avisa en el escritorio y por email.
 * - Si algo falla, avisa al correo de avisos. Alternativa manual: Acciones del pedido → Emitir / Reenviar.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DIP_FS_LIMITE' ) )    define( 'DIP_FS_LIMITE', 400 );          // € IVA incluido (art. 4 RD 1619/2012)
if ( ! defined( 'DIP_FS_FIN' ) )       define( 'DIP_FS_FIN', '2027-01-01' );    // VeriFactu obligatorio para sociedades
if ( ! defined( 'DIP_FS_AVISO_FIN' ) ) define( 'DIP_FS_AVISO_FIN', '2026-12-01' );
if ( ! defined( 'DIP_FS_MAX_LINEAS' ) ) define( 'DIP_FS_MAX_LINEAS', 18 );           // las que caben en la página A4 del PDF

/* =========================================================
 * Ajustes, fechas y formatos
 * ========================================================= */

function dip_fs_activada() {
	return (bool) dip_ajuste( 'factura_simplificada' );
}

/** Letras y números, hasta 6. El año se añade detrás: W → W2026. */
function dip_fs_sanear_prefijo( $prefijo ) {
	return substr( (string) preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $prefijo ) ), 0, 6 );
}

function dip_fs_prefijo() {
	return dip_fs_sanear_prefijo( dip_ajuste( 'serie_simplificada' ) ) ?: 'W';
}

/** Importe máximo: 400 € o el límite de factura completa de los ajustes, si es menor. */
function dip_fs_limite() {
	$limite = (float) dip_ajuste( 'limite_factura' );
	return ( $limite > 0 && $limite < DIP_FS_LIMITE ) ? $limite : (float) DIP_FS_LIMITE;
}

function dip_fs_ahora() {
	return apply_filters( 'dip_fs_ahora', new DateTimeImmutable( 'now', dip_zona_horaria() ) );
}

/** Desde el 1/1/2027 (hora de Madrid) la web ya no emite facturas: VeriFactu. */
function dip_fs_verifactu_parado( $ahora = null ) {
	$ahora = $ahora ?: dip_fs_ahora();
	return $ahora >= new DateTimeImmutable( DIP_FS_FIN . ' 00:00:00', dip_zona_horaria() );
}

function dip_fs_verifactu_cerca( $ahora = null ) {
	$ahora = $ahora ?: dip_fs_ahora();
	return $ahora >= new DateTimeImmutable( DIP_FS_AVISO_FIN . ' 00:00:00', dip_zona_horaria() );
}

function dip_fs_idioma_valido( $idioma ) {
	return in_array( $idioma, array( 'es', 'ca', 'en' ), true ) ? $idioma : 'es';
}

function dip_fs_formatear_numero( $serie, $n ) {
	return $serie . '-' . str_pad( (string) (int) $n, 5, '0', STR_PAD_LEFT );
}

/** 64,49 € (es, ca) · €64.49 (en). Espacio duro entre la cifra y el símbolo. */
function dip_fs_importe( $importe, $idioma = 'es' ) {
	$importe = round( (float) $importe, 2 );
	if ( 'en' === $idioma ) return ( $importe < 0 ? '-' : '' ) . '€' . number_format( abs( $importe ), 2, '.', ',' );
	return number_format( $importe, 2, ',', '.' ) . "\u{00A0}€";
}

/** 21 → "21" · 10,5 → "10,5" (en: "10.5"). */
function dip_fs_porcentaje( $tipo, $idioma = 'es' ) {
	$texto = rtrim( rtrim( number_format( (float) $tipo, 2, '.', '' ), '0' ), '.' );
	return 'en' === $idioma ? $texto : str_replace( '.', ',', $texto );
}

function dip_fs_kilos( $kg, $idioma = 'es' ) {
	return dip_fs_porcentaje( $kg, $idioma );
}

/** 2026-10-03 → 03/10/2026 */
function dip_fs_fecha( $ymd ) {
	$d = DateTimeImmutable::createFromFormat( '!Y-m-d', substr( (string) $ymd, 0, 10 ), dip_zona_horaria() );
	return $d ? $d->format( 'd/m/Y' ) : (string) $ymd;
}

/* =========================================================
 * Textos (ES, CA y EN). Las notas internas del pedido y los avisos van en castellano.
 * ========================================================= */

function dip_fs_textos( $idioma = 'es' ) {
	static $cache = array();
	$idioma = dip_fs_idioma_valido( $idioma );
	if ( isset( $cache[ $idioma ] ) ) return $cache[ $idioma ];
	$textos = array(
		'es' => array(
			'titulo'          => 'Factura simplificada',
			'subtitulo'       => 'IVA incluido · tipo impositivo %s %%',
			'frio'            => "\u{2212}78,5 °C",
			'l_numero'        => 'NÚMERO',
			'l_pedido'        => 'PEDIDO',
			'l_expedicion'    => 'FECHA DE EXPEDICIÓN',
			'l_operacion'     => 'FECHA DE LA OPERACIÓN',
			'nota_entrega'    => 'entrega del pedido',
			'nota_recogida'   => 'recogida en Mataró',
			'nota_pago'       => 'cobro anticipado',
			'l_cliente'       => 'CLIENTE',
			'l_pago'          => 'FORMA DE PAGO',
			'l_emisor'        => 'EMISOR',
			'l_descripcion'   => 'DESCRIPCIÓN',
			'l_cantidad'      => 'CANT.',
			'l_importe'       => 'IMPORTE (IVA INCL.)',
			'domicilio'       => 'Ronda Alfonso X El Sabio, 10, 2º 1ª',
			'cp_ciudad'       => '08301 Mataró (Barcelona)',
			'marca_de'        => 'DryIcePack es una marca de INDUNOVA IMS S.L.',
			'base'            => 'Base imponible',
			'iva'             => 'IVA %s %%',
			'total'           => 'Total (IVA incluido)',
			'nota_iva_1'      => 'IVA incluido en todos los importes.',
			'nota_iva_2'      => 'Tipo impositivo aplicado: %s %%.',
			'nota_iva_3'      => 'Cuota de IVA: %s.',
			'pie_legal'       => 'Factura simplificada según el artículo 7 del Real Decreto 1619/2012.',
			'pie_completa'    => 'Si necesitas factura completa a nombre de una empresa o autónomo, escríbenos a info@dryicepack.es con el número de pedido.',
			'archivo'         => 'Factura-simplificada-%s.pdf',
			'hielo_3'         => 'Hielo seco en pellets de 3 mm',
			'hielo_16'        => 'Hielo seco en nuggets de 16 mm',
			'pack'            => 'Pack de %s kg · caja EPS incluida',
			'envio'           => 'Envío por mensajería MRW',
			'recogida'        => 'Recogida en nuestra nave de Mataró',
			'sabado'          => 'Suplemento de entrega en sábado',
			'sabado_recogida' => 'Suplemento de recogida en sábado',
			'asunto'          => 'Tu factura simplificada %s · DryIcePack',
			'previo'          => 'Pedido %1$s · %2$s IVA incluido. La factura va adjunta en PDF.',
			'h1'              => 'Gracias por tu pedido',
			'hola'            => 'Hola, %s.',
			'hola_sin'        => 'Hola.',
			'intro'           => 'Te enviamos la factura simplificada de tu pedido %s. La tienes adjunta en PDF y resumida aquí abajo.',
			'completa_titulo' => '¿Necesitas factura completa a nombre de una empresa?',
			'completa_texto'  => 'Déjanos la razón social, el NIF/CIF y la dirección fiscal y te la hacemos. Sustituye a esta factura.',
			'completa_enlace' => 'Pídela aquí',
			'seguridad'       => 'Recuerda: guantes térmicos o pinzas, un sitio ventilado y nunca en un recipiente cerrado. Todas las normas:',
			'seguridad_url'   => 'dryicepack.es/seguridad-del-hielo-seco/',
			'dudas'           => '¿Dudas? Llámanos al %1$s, escríbenos por WhatsApp al %2$s o responde a este email. De lunes a viernes, de 9:00 a 18:00.',
			'gracias_hecha'   => 'Ya te hemos enviado por email la factura simplificada %s.',
			'gracias_luego'   => 'Te enviaremos la factura simplificada por email cuando recojas el pedido.',
		),
		'ca' => array(
			'titulo'          => 'Factura simplificada',
			'subtitulo'       => 'IVA inclòs · tipus impositiu %s %%',
			'frio'            => "\u{2212}78,5 °C",
			'l_numero'        => 'NÚMERO',
			'l_pedido'        => 'COMANDA',
			'l_expedicion'    => 'DATA D\'EXPEDICIÓ',
			'l_operacion'     => 'DATA DE L\'OPERACIÓ',
			'nota_entrega'    => 'entrega de la comanda',
			'nota_recogida'   => 'recollida a Mataró',
			'nota_pago'       => 'cobrament anticipat',
			'l_cliente'       => 'CLIENT',
			'l_pago'          => 'FORMA DE PAGAMENT',
			'l_emisor'        => 'EMISSOR',
			'l_descripcion'   => 'DESCRIPCIÓ',
			'l_cantidad'      => 'QUANT.',
			'l_importe'       => 'IMPORT (IVA INCL.)',
			'domicilio'       => 'Ronda Alfonso X El Sabio, 10, 2n 1a',
			'cp_ciudad'       => '08301 Mataró (Barcelona)',
			'marca_de'        => 'DryIcePack és una marca d\'INDUNOVA IMS S.L.',
			'base'            => 'Base imposable',
			'iva'             => 'IVA %s %%',
			'total'           => 'Total (IVA inclòs)',
			'nota_iva_1'      => 'IVA inclòs en tots els imports.',
			'nota_iva_2'      => 'Tipus impositiu aplicat: %s %%.',
			'nota_iva_3'      => 'Quota d\'IVA: %s.',
			'pie_legal'       => 'Factura simplificada segons l\'article 7 del Reial decret 1619/2012.',
			'pie_completa'    => 'Si necessites factura completa a nom d\'una empresa o autònom, escriu-nos a info@dryicepack.es amb el número de comanda.',
			'archivo'         => 'Factura-simplificada-%s.pdf',
			'hielo_3'         => 'Gel sec en pèl·lets de 3 mm',
			'hielo_16'        => 'Gel sec en nuggets de 16 mm',
			'pack'            => 'Pack de %s kg · caixa EPS inclosa',
			'envio'           => 'Enviament per missatgeria MRW',
			'recogida'        => 'Recollida a la nostra nau de Mataró',
			'sabado'          => 'Suplement d\'entrega en dissabte',
			'sabado_recogida' => 'Suplement de recollida en dissabte',
			'asunto'          => 'La teva factura simplificada %s · DryIcePack',
			'previo'          => 'Comanda %1$s · %2$s IVA inclòs. La factura va adjunta en PDF.',
			'h1'              => 'Gràcies per la teva comanda',
			'hola'            => 'Hola, %s.',
			'hola_sin'        => 'Hola.',
			'intro'           => 'T\'enviem la factura simplificada de la comanda %s. La tens adjunta en PDF i resumida aquí sota.',
			'completa_titulo' => 'Necessites factura completa a nom d\'una empresa?',
			'completa_texto'  => 'Deixa\'ns la raó social, el NIF/CIF i l\'adreça fiscal i te la fem. Substitueix aquesta factura.',
			'completa_enlace' => 'Demana-la aquí',
			'seguridad'       => 'Recorda: guants tèrmics o pinces, un lloc ventilat i mai en un recipient tancat. Totes les normes:',
			'seguridad_url'   => 'dryicepack.es/ca/seguretat-del-gel-sec/',
			'dudas'           => 'Dubtes? Truca\'ns al %1$s, escriu-nos per WhatsApp al %2$s o respon aquest correu. De dilluns a divendres, de 9:00 a 18:00.',
			'gracias_hecha'   => 'Ja t\'hem enviat per correu la factura simplificada %s.',
			'gracias_luego'   => 'T\'enviarem la factura simplificada per correu quan recullis la comanda.',
		),
		'en' => array(
			'titulo'          => 'Simplified invoice',
			'subtitulo'       => 'VAT included · VAT rate %s%%',
			'frio'            => "\u{2212}78.5 °C",
			'l_numero'        => 'NUMBER',
			'l_pedido'        => 'ORDER',
			'l_expedicion'    => 'DATE OF ISSUE',
			'l_operacion'     => 'DATE OF SUPPLY',
			'nota_entrega'    => 'order delivery',
			'nota_recogida'   => 'collection in Mataró',
			'nota_pago'       => 'advance payment',
			'l_cliente'       => 'CUSTOMER',
			'l_pago'          => 'PAYMENT METHOD',
			'l_emisor'        => 'ISSUED BY',
			'l_descripcion'   => 'DESCRIPTION',
			'l_cantidad'      => 'QTY',
			'l_importe'       => 'AMOUNT (VAT INCL.)',
			'domicilio'       => 'Ronda Alfonso X El Sabio, 10, 2º 1ª',
			'cp_ciudad'       => '08301 Mataró (Barcelona), Spain',
			'marca_de'        => 'DryIcePack is a brand of INDUNOVA IMS S.L.',
			'base'            => 'Taxable base',
			'iva'             => 'VAT %s%%',
			'total'           => 'Total (VAT included)',
			'nota_iva_1'      => 'VAT included in every amount.',
			'nota_iva_2'      => 'VAT rate applied: %s%%.',
			'nota_iva_3'      => 'VAT amount: %s.',
			'pie_legal'       => 'Simplified invoice under article 7 of Spanish Royal Decree 1619/2012.',
			'pie_completa'    => 'If you need a full invoice in the name of a company or a self-employed person, write to info@dryicepack.es with your order number.',
			'archivo'         => 'Simplified-invoice-%s.pdf',
			'hielo_3'         => 'Dry ice, 3 mm pellets',
			'hielo_16'        => 'Dry ice, 16 mm nuggets',
			'pack'            => '%s kg pack · EPS box included',
			'envio'           => 'Courier delivery (MRW)',
			'recogida'        => 'Collection from our warehouse in Mataró',
			'sabado'          => 'Saturday delivery surcharge',
			'sabado_recogida' => 'Saturday collection surcharge',
			'asunto'          => 'Your simplified invoice %s · DryIcePack',
			'previo'          => 'Order %1$s · %2$s VAT included. The invoice is attached as a PDF.',
			'h1'              => 'Thank you for your order',
			'hola'            => 'Hello %s,',
			'hola_sin'        => 'Hello,',
			'intro'           => 'here is the simplified invoice for your order %s. It is attached as a PDF and summarised below.',
			'completa_titulo' => 'Need a full invoice in a company name?',
			'completa_texto'  => 'Send us the company name, tax ID and billing address and we will issue it. It replaces this invoice.',
			'completa_enlace' => 'Request it here',
			'seguridad'       => 'Remember: thermal gloves or tongs, a ventilated space and never in a sealed container. All the rules:',
			'seguridad_url'   => 'dryicepack.es/en/dry-ice-safety/',
			'dudas'           => 'Questions? Call %1$s, message us on WhatsApp at %2$s or reply to this email. Monday to Friday, 9:00 to 18:00.',
			'gracias_hecha'   => 'We have already emailed you simplified invoice %s.',
			'gracias_luego'   => 'We will email you the simplified invoice when you collect your order.',
		),
	);
	return $cache[ $idioma ] = $textos[ $idioma ];
}

/** Datos del emisor (los legales salen de dip_contacto(); el domicilio social, de los textos). */
function dip_fs_emisor( $idioma = 'es' ) {
	$c = dip_contacto();
	$t = dip_fs_textos( $idioma );
	return array(
		'razon'     => $c['empresa'],
		'nif'       => $c['cif'],
		'domicilio' => $t['domicilio'],
		'cp_ciudad' => $t['cp_ciudad'],
		'marca'     => $c['marca'],
		'email'     => $c['email'],
		'telefono'  => ( 'en' === $idioma ? '+34 ' : '' ) . $c['telefono'],
		'whatsapp'  => ( 'en' === $idioma ? '+34 ' : '' ) . $c['whatsapp'],
		'web'       => 'dryicepack.es',
	);
}

/* =========================================================
 * ¿Le toca factura simplificada?
 * ========================================================= */

function dip_fs_nif_pedido( WC_Order $pedido ) {
	foreach ( array( '_billing_nif', '_billing_cif', '_billing_vat', '_billing_dni_nie' ) as $clave ) {
		if ( '' !== trim( (string) $pedido->get_meta( $clave ) ) ) return true;
	}
	return false;
}

/** Tipos de IVA (en %) que lleva el pedido, sin repetir. */
function dip_fs_tipos_iva( WC_Order $pedido ) {
	$tipos = array();
	foreach ( $pedido->get_items( 'tax' ) as $impuesto ) {
		if ( abs( (float) $impuesto->get_tax_total() + (float) $impuesto->get_shipping_tax_total() ) < 0.005 ) continue;
		$tipo = method_exists( $impuesto, 'get_rate_percent' ) ? $impuesto->get_rate_percent() : null;
		if ( ( null === $tipo || '' === $tipo ) && class_exists( 'WC_Tax' ) && method_exists( 'WC_Tax', 'get_rate_percent_value' ) ) {
			$tipo = WC_Tax::get_rate_percent_value( $impuesto->get_rate_id() );
		}
		if ( null !== $tipo && '' !== $tipo ) $tipos[ number_format( (float) $tipo, 2, '.', '' ) ] = (float) $tipo;
	}
	return array_values( $tipos );
}

function dip_fs_tipo_iva( WC_Order $pedido ) {
	$tipos = dip_fs_tipos_iva( $pedido );
	return 1 === count( $tipos ) ? round( $tipos[0], 2 ) : round( ( dip_factor_iva() - 1 ) * 100, 2 );
}

/**
 * '' si al pedido le toca factura simplificada; si no, el motivo (texto para el escritorio).
 * $modo 'auto'     → al cambiar de estado: ajuste activado, pedido pagado y creado después de poner el módulo en marcha.
 *       'previsto' → ¿la tendrá cuando se pague? (para no mandar antes el email "¿Necesitas factura?").
 *       'manual'   → acción "Emitir factura simplificada" del pedido: no mira el ajuste ni la fecha de puesta en marcha.
 */
function dip_fs_motivo( WC_Order $pedido, $modo = 'auto', $con_verifactu = true ) {
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	if ( '' !== $numero ) return 'Ya tiene la factura simplificada ' . $numero . '.';
	if ( $pedido->get_meta( '_dip_factura_emitida' ) ) return 'Ya tiene factura emitida.';
	if ( $con_verifactu && dip_fs_verifactu_parado() ) return 'Desde el 1/1/2027 la web no emite facturas (VeriFactu, Real Decreto 1007/2023): hazla en la aplicación de la gestoría.';
	// Efectivo al recoger: la factura sale el día de la recogida. Si ese día ya es 2027, tampoco saldrá.
	if ( $con_verifactu && 'previsto' === $modo && (string) $pedido->get_meta( '_dip_fecha_entrega' ) >= DIP_FS_FIN ) return 'Se recoge a partir del 1/1/2027: la web ya no emitirá la factura (VeriFactu). Hazla en la aplicación de la gestoría.';
	if ( 'manual' !== $modo && ! dip_fs_activada() ) return 'La factura simplificada automática está desactivada (Dryicepack → Ajustes).';
	if ( 'dip_factura_mensual' === $pedido->get_payment_method() || $pedido->get_meta( '_dip_factura_mensual' ) ) return 'Cuenta de empresa con factura mensual.';
	if ( 'cuenta' === (string) $pedido->get_meta( '_dip_canal' ) || ( function_exists( 'dip_es_cuenta_empresa' ) && dip_es_cuenta_empresa( (int) $pedido->get_customer_id() ) ) ) {
		return 'Pedido de una cuenta de empresa: factura completa.';
	}
	if ( (int) $pedido->get_meta( '_dip_empresa' ) ) return 'Compra para empresa o autónomo (casilla marcada): factura completa.';
	if ( dip_fs_nif_pedido( $pedido ) ) return 'El pedido tiene NIF/CIF: factura completa.';
	// El checkout borra la razón social si no se marca la casilla: si la hay (pedido hecho en el escritorio, por ejemplo), es una empresa
	if ( '' !== trim( (string) $pedido->get_billing_company() ) ) return 'El pedido tiene razón social: factura completa.';
	if ( $pedido->get_meta( '_dip_factura_datos' ) ) return 'El cliente ha pedido factura completa en /factura/.';
	$total = (float) $pedido->get_total();
	if ( $total > dip_fs_limite() + 0.001 ) return 'Más de ' . dip_euros( dip_fs_limite() ) . ' (IVA incluido): factura completa.';
	if ( $total <= 0 ) return 'Pedido sin importe.';
	if ( 'EUR' !== (string) ( $pedido->get_currency() ?: 'EUR' ) ) return 'Pedido en otra moneda: hazla a mano.';
	if ( (float) $pedido->get_total_refunded() > 0 ) return 'El pedido tiene devoluciones: hazla a mano.';
	if ( count( dip_fs_tipos_iva( $pedido ) ) > 1 ) return 'El pedido lleva varios tipos de IVA: hazla a mano.';
	$lineas = dip_fs_lineas( $pedido );
	if ( null === $lineas ) return 'Las líneas del pedido no suman el total (más de un céntimo de diferencia por línea): hazla a mano.';
	if ( count( $lineas ) > DIP_FS_MAX_LINEAS ) return 'El pedido tiene más de ' . DIP_FS_MAX_LINEAS . ' líneas y no caben en la factura: hazla a mano.';
	// La cuota tiene que ser el tipo sobre la base (con el margen de los redondeos por línea). Si no, hay algo sin IVA.
	list( $tipo, $base, $cuota ) = dip_fs_desglose( $pedido );
	if ( abs( round( $base * $tipo / 100, 2 ) - $cuota ) > 0.01 * count( $lineas ) + 0.0001 ) {
		return 'La cuota de IVA del pedido no es el ' . dip_fs_porcentaje( $tipo ) . ' % de la base (¿alguna línea sin IVA?): hazla a mano.';
	}
	if ( ! is_email( (string) $pedido->get_billing_email() ) ) return 'El pedido no tiene un email válido.';
	if ( 'previsto' !== $modo ) {
		$estado = (string) $pedido->get_status();
		$cod    = 'cod' === $pedido->get_payment_method();
		if ( 'completed' !== $estado && ! ( 'processing' === $estado && ! $cod ) ) {
			return ( $cod && 'processing' === $estado ) ? 'Pago en efectivo al recoger: se emite al marcar el pedido como completado.' : 'El pedido todavía no está pagado.';
		}
	}
	if ( 'manual' !== $modo ) {
		$desde  = (int) get_option( 'dip_fs_desde', 0 );
		$creado = $pedido->get_date_created();
		if ( $desde && $creado && $creado->getTimestamp() < $desde ) return 'Pedido anterior a la puesta en marcha de la factura simplificada: se factura como antes.';
	}
	return '';
}

/**
 * ¿Este pedido recibe (o recibirá al recogerlo) la factura simplificada por email? Entonces no hace falta el email
 * "¿Necesitas factura?": el de la factura ya lleva el enlace para pedir la completa.
 */
function dip_fs_sustituye_email_factura( WC_Order $pedido ) {
	if ( '' !== (string) $pedido->get_meta( '_dip_fs_numero' ) ) return true;
	// Pagado y le toca: la factura sale en este mismo cambio de estado (woocommerce_order_status_changed, justo después)
	if ( '' === dip_fs_motivo( $pedido, 'auto' ) ) return true;
	if ( 'cod' === $pedido->get_payment_method() && 'completed' !== $pedido->get_status() ) {
		return '' === dip_fs_motivo( $pedido, 'previsto' );
	}
	return false;
}

/** Frase para la página de gracias ('' si no aplica). */
function dip_fs_texto_gracias( WC_Order $pedido, $idioma = 'es' ) {
	$t      = dip_fs_textos( $idioma );
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	if ( '' !== $numero ) return sprintf( $t['gracias_hecha'], $numero );
	$recoge_y_paga = 'cod' === $pedido->get_payment_method() && 'completed' !== $pedido->get_status();
	return ( $recoge_y_paga && '' === dip_fs_motivo( $pedido, 'previsto' ) ) ? $t['gracias_luego'] : '';
}

/* =========================================================
 * Numeración: contador atómico y reserva del pedido
 * ========================================================= */

/** Último número expedido de una serie (0 si aún no hay ninguno). Lectura directa, sin la caché de opciones. */
function dip_fs_ultimo_numero( $serie ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'dip_fs_contador_' . $serie ) );
}

/** Número que llevará la próxima factura (para los ajustes; no reserva nada). */
function dip_fs_numero_previsto() {
	$serie = dip_fs_prefijo() . dip_fs_ahora()->format( 'Y' );
	return dip_fs_formatear_numero( $serie, dip_fs_ultimo_numero( $serie ) + 1 );
}

/**
 * Siguiente número de la serie, reservado. "Compara y cambia": el UPDATE solo cambia la fila si el contador
 * sigue valiendo lo que se leyó. Si otro pedido se adelanta, no cambia nada y se vuelve a leer. Funciona igual en
 * MySQL y en SQLite, sin bloqueos que se queden colgados. 0 si la base de datos no responde.
 */
function dip_fs_siguiente_numero( $serie ) {
	global $wpdb;
	$clave = 'dip_fs_contador_' . $serie;
	for ( $intento = 0; $intento < 30; $intento++ ) {
		$actual = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $clave ) );
		if ( null === $actual ) {
			// Primera factura de la serie (o del año): se crea el contador. Si otro proceso lo crea antes, falla y se repite.
			$antes = $wpdb->suppress_errors( true );
			$ok    = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')", $clave, '1' ) );
			$wpdb->suppress_errors( $antes );
			if ( $ok ) return 1;
		} else {
			$n     = (int) $actual + 1;
			$filas = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", (string) $n, $clave, (string) $actual ) );
			if ( 1 === (int) $filas ) return $n;
		}
		usleep( 10000 * ( $intento + 1 ) );
	}
	return 0;
}

/**
 * Reserva el pedido mientras se le pone número (fila propia en wp_options, cuya clave es única: el segundo
 * INSERT falla). Si una reserva tiene más de 10 minutos, el proceso que la hizo se cortó: se libera.
 */
function dip_fs_reservar_pedido( $pedido_id ) {
	global $wpdb;
	$clave = 'dip_fs_emitiendo_' . (int) $pedido_id;
	for ( $intento = 0; $intento < 2; $intento++ ) {
		$antes = $wpdb->suppress_errors( true );
		$ok    = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')", $clave, (string) time() ) );
		$wpdb->suppress_errors( $antes );
		if ( $ok ) return true;
		$viejas = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value < %s", $clave, (string) ( time() - 600 ) ) );
		if ( ! $viejas ) return false;
	}
	return false;
}

function dip_fs_liberar_pedido( $pedido_id ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", 'dip_fs_emitiendo_' . (int) $pedido_id ) );
}

/* =========================================================
 * Libro de facturas expedidas: una fila por número en wp_options ("dip_fs_libro_W2026-00001").
 * Es la lista que necesita la gestoría (libro registro y declaración del IVA). Sigue ahí aunque se borre el pedido.
 * ========================================================= */

function dip_fs_registrar( array $datos, $pedido_id ) {
	global $wpdb;
	$fila = array_intersect_key( $datos, array_flip( array( 'numero', 'serie', 'n', 'expedida', 'fecha_operacion', 'pedido', 'cliente', 'pago', 'tipo_iva', 'base', 'cuota', 'total' ) ) );
	$fila['pedido_id'] = (int) $pedido_id;
	$antes = $wpdb->suppress_errors( true );
	$ok    = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %s, 'off')", 'dip_fs_libro_' . $datos['numero'], (string) json_encode( $fila, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE ) ) );
	$wpdb->suppress_errors( $antes );
	return (bool) $ok;
}

/** Todas las filas del libro, ordenadas por serie y número. */
function dip_fs_libro_filas() {
	global $wpdb;
	$filas = array();
	foreach ( (array) $wpdb->get_col( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'dip_fs_libro_%'" ) as $json ) {
		$r = json_decode( (string) $json, true );
		if ( is_array( $r ) && isset( $r['numero'], $r['serie'], $r['n'] ) ) $filas[ (string) $r['numero'] ] = $r;
	}
	uasort( $filas, static fn( $a, $b ) => strcmp( (string) $a['serie'], (string) $b['serie'] ) ?: (int) $a['n'] <=> (int) $b['n'] );
	return $filas;
}

/**
 * Libro de un mes (AAAA-MM) para el CSV: una fila por factura expedida ese mes, los números que faltan en medio
 * (no debería haber ninguno) y una fila de totales.
 */
function dip_fs_libro( $mes ) {
	$todas = dip_fs_libro_filas();
	$dec   = static fn( $n ) => number_format( (float) $n, 2, ',', '' );
	$texto = static fn( $s ) => preg_match( '/^[=+\-@\t\r]/', (string) $s ) ? "'" . $s : (string) $s; // que Excel no lo lea como fórmula
	$salida = array( array( 'Serie', 'Número', 'Fecha de expedición', 'Fecha de la operación', 'Pedido', 'Cliente', 'Base imponible', 'Tipo de IVA (%)', 'Cuota de IVA', 'Total (IVA incluido)', 'Forma de pago', 'Observaciones' ) );
	$suma   = array( 0.0, 0.0, 0.0 );
	$cuenta = 0;
	$previo = array();
	foreach ( $todas as $r ) {
		if ( 0 !== strpos( (string) ( $r['expedida'] ?? '' ), $mes ) ) continue;
		$serie = (string) $r['serie'];
		$n     = (int) $r['n'];
		// Huecos: entre la factura anterior de la serie (de este mes o de antes) y esta
		$desde = $previo[ $serie ] ?? 0;
		if ( ! $desde ) {
			$desde = $n - 1;
			while ( $desde >= 1 && ! isset( $todas[ dip_fs_formatear_numero( $serie, $desde ) ] ) ) $desde--;
		}
		for ( $k = $desde + 1; $k < $n; $k++ ) {
			$salida[] = array( $serie, dip_fs_formatear_numero( $serie, $k ), '', '', '', '', '', '', '', '', '', 'Número sin factura en el libro: revisar' );
		}
		$previo[ $serie ] = $n;

		$obs    = array();
		$pedido = wc_get_order( (int) ( $r['pedido_id'] ?? 0 ) );
		if ( ! $pedido ) {
			$obs[] = 'Pedido borrado en la web (la factura sigue expedida)';
		} else {
			if ( $pedido->get_meta( '_dip_fs_rectificar' ) ) $obs[] = 'Rectificativa pendiente';
			if ( $pedido->get_meta( '_dip_factura_datos' ) ) $obs[] = 'El cliente ha pedido factura completa, que la sustituye';
			if ( $pedido->get_meta( '_dip_fs_error' ) ) $obs[] = 'Email al cliente sin enviar';
		}
		$salida[] = array(
			$serie,
			(string) $r['numero'],
			dip_fs_fecha( (string) $r['expedida'] ),
			empty( $r['fecha_operacion'] ) ? '' : dip_fs_fecha( (string) $r['fecha_operacion'] ),
			(string) ( $r['pedido'] ?? '' ),
			$texto( $r['cliente'] ?? '' ),
			$dec( $r['base'] ?? 0 ),
			dip_fs_porcentaje( $r['tipo_iva'] ?? 21 ),
			$dec( $r['cuota'] ?? 0 ),
			$dec( $r['total'] ?? 0 ),
			$texto( $r['pago'] ?? '' ),
			implode( '. ', $obs ),
		);
		$suma[0] += (float) ( $r['base'] ?? 0 );
		$suma[1] += (float) ( $r['cuota'] ?? 0 );
		$suma[2] += (float) ( $r['total'] ?? 0 );
		$cuenta++;
	}
	if ( $cuenta ) $salida[] = array( 'Total', $cuenta . ( 1 === $cuenta ? ' factura' : ' facturas' ), '', '', '', '', $dec( $suma[0] ), '', $dec( $suma[1] ), $dec( $suma[2] ), '', '' );
	return $salida;
}

/* Descarga del libro de un mes (Dryicepack → Ajustes). */
add_action( 'admin_post_dip_fs_libro', static function () {
	check_admin_referer( 'dip_fs_libro' );
	if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'No tienes permiso para descargar el libro de facturas.', 403 );
	$mes = isset( $_GET['mes'] ) ? sanitize_text_field( wp_unslash( $_GET['mes'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- comprobado arriba
	if ( ! preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $mes ) ) $mes = dip_fs_ahora()->format( 'Y-m' );
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="facturas-simplificadas-' . $mes . '.csv"' );
	$salida = fopen( 'php://output', 'w' );
	fwrite( $salida, "\xEF\xBB\xBF" ); // BOM: Excel abre bien los acentos
	foreach ( dip_fs_libro( $mes ) as $fila ) fputcsv( $salida, $fila, ';', '"', '' );
	fclose( $salida );
	exit;
} );

/* =========================================================
 * Datos de la factura (se guardan en el pedido: el PDF se puede rehacer igual)
 * ========================================================= */

/**
 * Líneas de la factura con su importe IVA incluido. WooCommerce redondea el IVA línea a línea o sobre el total
 * (según sus ajustes), así que las líneas pueden no sumar el total por algún céntimo: si la diferencia es como mucho
 * de 1 céntimo por línea, se suma a la línea de mayor importe. null si es mayor (algo raro en el pedido) o si no hay líneas.
 */
function dip_fs_lineas( WC_Order $pedido ) {
	$lineas = array();
	foreach ( $pedido->get_items() as $item ) {
		$producto = $item->get_product();
		$nombre   = trim( wp_strip_all_tags( (string) $item->get_name() ) );
		$kg       = dip_kg_producto( $producto );
		if ( $kg <= 0 && preg_match( '/(\d+(?:[.,]\d+)?)\s*kg/i', $nombre, $m ) ) $kg = (float) str_replace( ',', '.', $m[1] );
		$formato = ( $producto && method_exists( $producto, 'get_attribute' ) ) ? (string) $producto->get_attribute( 'pa_formato' ) : '';
		if ( '' === $formato ) $formato = (string) $item->get_meta( 'pa_formato' );
		$mm = preg_match( '/(\d+)\s*mm/i', $formato . ' ' . $nombre, $m ) ? (int) $m[1] : 0;
		$lineas[] = array(
			'tipo'     => in_array( $mm, array( 3, 16 ), true ) ? 'hielo' : 'producto',
			'nombre'   => $nombre,
			'kg'       => $kg,
			'mm'       => $mm,
			'cantidad' => max( 1, (int) $item->get_quantity() ),
			'importe'  => round( (float) $item->get_total() + (float) $item->get_total_tax(), 2 ),
		);
	}
	foreach ( $pedido->get_items( 'shipping' ) as $item ) {
		$importe = round( (float) $item->get_total() + (float) $item->get_total_tax(), 2 );
		if ( abs( $importe ) < 0.005 ) continue; // la recogida gratis no es una línea de la factura
		$lineas[] = array( 'tipo' => dip_es_recogida( $item->get_method_id() ) ? 'recogida' : 'envio', 'nombre' => (string) $item->get_name(), 'cantidad' => 1, 'importe' => $importe );
	}
	foreach ( $pedido->get_items( 'fee' ) as $item ) {
		$importe = round( (float) $item->get_total() + (float) $item->get_total_tax(), 2 );
		if ( abs( $importe ) < 0.005 ) continue;
		$nombre = (string) $item->get_name();
		$tipo   = 'cargo';
		if ( preg_match( '/s[áa]bado|dissabte|saturday/iu', $nombre ) ) $tipo = preg_match( '/recogida|recollida|collection/iu', $nombre ) ? 'sabado_recogida' : 'sabado';
		$lineas[] = array( 'tipo' => $tipo, 'nombre' => $nombre, 'cantidad' => 1, 'importe' => $importe );
	}
	if ( ! $lineas ) return null;

	$total = round( (float) $pedido->get_total(), 2 );
	$dif   = round( $total - array_sum( array_column( $lineas, 'importe' ) ), 2 );
	if ( abs( $dif ) >= 0.005 ) {
		if ( abs( $dif ) > 0.01 * count( $lineas ) + 0.0001 ) return null;
		$mayor = 0;
		foreach ( $lineas as $i => $l ) {
			if ( $l['importe'] > $lineas[ $mayor ]['importe'] ) $mayor = $i;
		}
		$lineas[ $mayor ]['importe'] = round( $lineas[ $mayor ]['importe'] + $dif, 2 );
	}
	return $lineas;
}

/**
 * Fecha de la operación (art. 7.1.c del RD 1619/2012): la de la entrega o la del cobro anticipado, la que sea antes,
 * y solo si ya ha pasado y es otro día que la expedición. Una entrega futura no es la fecha de la operación: con
 * tarjeta, el IVA se devenga al cobrar (art. 75.Dos de la Ley del IVA), y el cobro es el día de la factura.
 * Devuelve [ 'AAAA-MM-DD' o '', 'entrega' o 'pago' ].
 */
function dip_fs_fecha_operacion( WC_Order $pedido, DateTimeImmutable $ahora ) {
	$hoy     = $ahora->format( 'Y-m-d' );
	$fechas  = array();
	$entrega = substr( (string) $pedido->get_meta( '_dip_fecha_entrega' ), 0, 10 );
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $entrega ) ) $fechas['entrega'] = $entrega;
	$pagado = method_exists( $pedido, 'get_date_paid' ) ? $pedido->get_date_paid() : null;
	if ( $pagado ) $fechas['pago'] = ( new DateTimeImmutable( '@' . $pagado->getTimestamp() ) )->setTimezone( dip_zona_horaria() )->format( 'Y-m-d' );
	$fechas = array_filter( $fechas, static fn( $f ) => $f <= $hoy ); // lo que ya ha pasado (o es hoy)
	if ( ! $fechas ) return array( '', '' );
	asort( $fechas ); // si coinciden, primero la entrega
	$fecha = reset( $fechas );
	return $fecha === $hoy ? array( '', '' ) : array( $fecha, (string) key( $fechas ) );
}

/** "entrega del pedido", "recogida en Mataró" o "cobro anticipado", junto a la fecha de la operación. */
function dip_fs_nota_operacion( array $datos, array $t ) {
	if ( 'pago' === ( $datos['operacion_por'] ?? '' ) ) return $t['nota_pago'];
	return 'recogida' === ( $datos['metodo'] ?? '' ) ? $t['nota_recogida'] : $t['nota_entrega'];
}

/** IVA incluido: base = total − cuota. Si WooCommerce no calcula impuestos, se saca del tipo (21 %). [tipo, base, cuota, total] */
function dip_fs_desglose( WC_Order $pedido ) {
	$total = round( (float) $pedido->get_total(), 2 );
	$tipo  = dip_fs_tipo_iva( $pedido );
	$cuota = round( (float) $pedido->get_total_tax(), 2 );
	if ( $cuota <= 0 ) {
		$base  = round( $total / ( 1 + $tipo / 100 ), 2 );
		$cuota = round( $total - $base, 2 );
	} else {
		$base = round( $total - $cuota, 2 );
	}
	return array( $tipo, $base, $cuota, $total );
}

function dip_fs_datos_factura( WC_Order $pedido, DateTimeImmutable $ahora ) {
	$lineas = dip_fs_lineas( $pedido ) ?: array();
	list( $tipo, $base, $cuota, $total ) = dip_fs_desglose( $pedido );

	list( $operacion, $operacion_por ) = dip_fs_fecha_operacion( $pedido, $ahora );
	$metodo = 'recogida' === $pedido->get_meta( '_dip_metodo_entrega' ) ? 'recogida' : 'envio';
	$emisor = dip_fs_emisor( 'es' );
	return array(
		'version'          => 1,
		'expedida'         => $ahora->format( 'Y-m-d H:i:s' ),
		'fecha_expedicion' => $ahora->format( 'Y-m-d' ),
		'fecha_operacion'  => $operacion,
		'operacion_por'    => $operacion_por,
		'metodo'           => $metodo,
		'idioma'           => dip_fs_idioma_valido( (string) $pedido->get_meta( '_dip_idioma' ) ),
		'emisor'           => array( 'razon' => $emisor['razon'], 'nif' => $emisor['nif'] ),
		'cliente'          => trim( $pedido->get_billing_first_name() . ' ' . $pedido->get_billing_last_name() ),
		'pedido'           => (string) $pedido->get_order_number(),
		'pago'             => trim( wp_strip_all_tags( (string) $pedido->get_payment_method_title() ) ),
		'lineas'           => $lineas,
		'tipo_iva'         => $tipo,
		'base'             => $base,
		'cuota'            => $cuota,
		'total'            => $total,
	);
}

/** [título, detalle] de una línea en el idioma de la factura. */
function dip_fs_texto_linea( array $l, $idioma = 'es' ) {
	$t = dip_fs_textos( $idioma );
	switch ( $l['tipo'] ?? '' ) {
		case 'hielo':
			$kg = (float) ( $l['kg'] ?? 0 );
			return array( 16 === (int) ( $l['mm'] ?? 0 ) ? $t['hielo_16'] : $t['hielo_3'], $kg > 0 ? sprintf( $t['pack'], dip_fs_kilos( $kg, $idioma ) ) : '' );
		case 'envio':
		case 'recogida':
		case 'sabado':
		case 'sabado_recogida':
			return array( $t[ $l['tipo'] ], '' );
	}
	return array( (string) ( $l['nombre'] ?? '' ), '' );
}

/* =========================================================
 * Emisión
 * ========================================================= */

/**
 * Expide la factura simplificada del pedido si le toca, guarda el PDF y la envía. Devuelve el número o false.
 * $modo: 'auto' (cambio de estado) o 'manual' (acción del pedido).
 */
function dip_fs_emitir( $pedido, $modo = 'auto' ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido );
	if ( ! $pedido instanceof WC_Order ) return false;

	$motivo = dip_fs_motivo( $pedido, $modo );
	if ( '' !== $motivo ) {
		if ( 'manual' === $modo ) {
			$pedido->add_order_note( 'Factura simplificada no emitida: ' . $motivo );
		} elseif ( dip_fs_verifactu_parado() && '' === dip_fs_motivo( $pedido, $modo, false ) && ! $pedido->get_meta( '_dip_fs_nota_verifactu' ) ) {
			$pedido->update_meta_data( '_dip_fs_nota_verifactu', 1 );
			$pedido->save_meta_data();
			$pedido->add_order_note( 'Sin factura simplificada automática: desde el 1/1/2027 la web no emite facturas (VeriFactu, Real Decreto 1007/2023). Hazla en la aplicación de la gestoría. El cliente recibe el email «¿Necesitas factura?».' );
		}
		return false;
	}

	$id = $pedido->get_id();
	if ( ! dip_fs_reservar_pedido( $id ) ) return false; // otro proceso la está emitiendo ahora mismo
	try {
		$pedido->read_meta_data( true ); // lo que otro proceso haya guardado mientras tanto
		if ( '' !== dip_fs_motivo( $pedido, $modo ) ) return false;
		$ahora = dip_fs_ahora();
		$datos = dip_fs_datos_factura( $pedido, $ahora );
		$serie = dip_fs_prefijo() . $ahora->format( 'Y' );
		$n     = dip_fs_siguiente_numero( $serie );
		if ( $n < 1 ) {
			dip_fs_avisar_fallo( $pedido, 'No se ha podido reservar un número de factura: la base de datos no responde. No se ha emitido nada.' );
			return false;
		}
		$numero = dip_fs_formatear_numero( $serie, $n );
		$datos  = array( 'numero' => $numero, 'serie' => $serie, 'n' => $n ) + $datos;
		// El número se apunta en el libro y en el pedido en cuanto existe: si después falla el PDF o el email, no queda un hueco
		dip_fs_registrar( $datos, $id );
		$pedido->update_meta_data( '_dip_fs_numero', $numero );
		$pedido->update_meta_data( '_dip_fs_fecha', $datos['expedida'] );
		$pedido->update_meta_data( '_dip_fs_datos', $datos );
		$pedido->save_meta_data();
	} finally {
		dip_fs_liberar_pedido( $id );
	}

	$pedido->add_order_note( sprintf(
		'Factura simplificada %1$s expedida: %2$s IVA incluido (base %3$s + IVA %4$s %% %5$s).%6$s',
		$numero,
		dip_euros( $datos['total'] ),
		dip_euros( $datos['base'] ),
		dip_fs_porcentaje( $datos['tipo_iva'] ),
		dip_euros( $datos['cuota'] ),
		'manual' === $modo ? ' Emitida desde Acciones del pedido.' : ''
	) );
	$pdf = dip_fs_pdf( $datos );
	if ( ! dip_fs_guardar_pdf( $pedido, $pdf ) ) {
		dip_fs_avisar_fallo( $pedido, 'No se ha podido guardar la copia del PDF de la factura ' . $numero . ' en wp-content/uploads/dryicepack-facturas/. La factura se envía igualmente y el PDF se vuelve a generar al descargarlo.' );
	}
	dip_fs_enviar( $pedido, $pdf );
	return $numero;
}

/*
 * Tarjeta: al pasar a "Procesando" (pagado). Efectivo al recoger: al pasar a "Completado".
 * En woocommerce_order_status_changed, que WooCommerce lanza después de sus emails del cambio de estado:
 * así el cliente recibe primero "Hemos recibido tu pedido" y después la factura.
 */
add_action( 'woocommerce_order_status_changed', static function ( $pedido_id, $de = '', $a = '', $pedido = null ) {
	if ( ! in_array( $a, array( 'processing', 'completed' ), true ) ) return;
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( ! $pedido || ( 'processing' === $a && 'cod' === $pedido->get_payment_method() ) ) return;
	try {
		dip_fs_emitir( $pedido, 'auto' );
	} catch ( Throwable $e ) {
		// Un error aquí no puede romper el pago ni el cambio de estado (WooCommerce solo recoge Exception, no Error): se avisa
		error_log( 'Dryicepack, factura simplificada del pedido ' . $pedido_id . ': ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		try {
			dip_fs_avisar_fallo( $pedido, 'Error inesperado al emitir la factura simplificada: ' . $e->getMessage() . '.' );
		} catch ( Throwable $e2 ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
		}
	}
}, 20, 4 );

/* Fecha de puesta en marcha: los pedidos anteriores no reciben factura automática (ya los factura la gestoría). */
add_action( 'init', static function () {
	if ( false === get_option( 'dip_fs_desde', false ) ) update_option( 'dip_fs_desde', time(), true );
} );

/* =========================================================
 * PDF: archivo guardado, temporal y descarga
 * ========================================================= */

/** Carpeta protegida de las facturas ('' si no se puede crear). */
function dip_fs_carpeta() {
	$subidas = wp_upload_dir( null, false );
	if ( ! empty( $subidas['error'] ) || empty( $subidas['basedir'] ) ) return '';
	$dir = rtrim( $subidas['basedir'], '/\\' ) . '/dryicepack-facturas';
	if ( ! wp_mkdir_p( $dir ) ) return '';
	if ( ! file_exists( $dir . '/index.php' ) ) @file_put_contents( $dir . '/index.php', '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		@file_put_contents( $dir . '/.htaccess', "# Facturas de clientes: sin acceso desde la web\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n" ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	return $dir;
}

/** Guarda el PDF en uploads/dryicepack-facturas/AAAA/ con un nombre que no se puede adivinar. */
function dip_fs_guardar_pdf( WC_Order $pedido, $pdf ) {
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	$datos  = (array) $pedido->get_meta( '_dip_fs_datos' );
	$dir    = dip_fs_carpeta();
	if ( '' === $numero || '' === $dir || '' === (string) $pdf ) return false;
	$anio = substr( (string) ( $datos['fecha_expedicion'] ?? '' ), 0, 4 );
	$anio = preg_match( '/^\d{4}$/', $anio ) ? $anio : dip_fs_ahora()->format( 'Y' );
	if ( ! wp_mkdir_p( $dir . '/' . $anio ) ) return false;
	if ( ! file_exists( $dir . '/' . $anio . '/index.php' ) ) @file_put_contents( $dir . '/' . $anio . '/index.php', '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$relativa = 'dryicepack-facturas/' . $anio . '/' . $numero . '-' . wp_generate_password( 12, false ) . '.pdf';
	$subidas  = wp_upload_dir( null, false );
	if ( false === @file_put_contents( rtrim( $subidas['basedir'], '/\\' ) . '/' . $relativa, $pdf ) ) return false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
	$pedido->update_meta_data( '_dip_fs_archivo', $relativa );
	$pedido->save_meta_data();
	return true;
}

/** PDF de la factura del pedido: la copia guardada o, si no está, rehecho con los datos guardados. */
function dip_fs_pdf_de_pedido( WC_Order $pedido ) {
	$relativa = (string) $pedido->get_meta( '_dip_fs_archivo' );
	if ( $relativa && false === strpos( $relativa, '..' ) ) {
		$subidas = wp_upload_dir( null, false );
		$ruta    = rtrim( (string) $subidas['basedir'], '/\\' ) . '/' . $relativa;
		if ( is_readable( $ruta ) ) return (string) file_get_contents( $ruta );
	}
	$datos = $pedido->get_meta( '_dip_fs_datos' );
	return is_array( $datos ) ? dip_fs_pdf( $datos ) : '';
}

/** Copia temporal con un nombre claro para adjuntarla ("Factura-simplificada-W2026-00001.pdf"). Se borra al enviar. */
function dip_fs_archivo_temporal( $nombre, $contenido ) {
	if ( '' === (string) $contenido ) return '';
	$dir = rtrim( get_temp_dir(), '/\\' ) . '/dip-factura-' . wp_generate_password( 10, false );
	if ( ! wp_mkdir_p( $dir ) ) return '';
	$ruta = $dir . '/' . preg_replace( '/[^A-Za-z0-9._-]/', '-', (string) $nombre );
	return false !== @file_put_contents( $ruta, $contenido ) ? $ruta : ''; // phpcs:ignore WordPress.PHP.NoSilencedErrors
}

function dip_fs_url_admin_pedido( WC_Order $pedido ) {
	if ( class_exists( \Automattic\WooCommerce\Utilities\OrderUtil::class ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
		return admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $pedido->get_id() );
	}
	return admin_url( 'post.php?post=' . $pedido->get_id() . '&action=edit' );
}

function dip_fs_url_pdf( WC_Order $pedido ) {
	return wp_nonce_url( admin_url( 'admin-post.php?action=dip_fs_pdf&pedido=' . $pedido->get_id() ), 'dip_fs_pdf_' . $pedido->get_id() );
}

/* Descarga del PDF desde el escritorio (solo quien gestiona pedidos). */
add_action( 'admin_post_dip_fs_pdf', static function () {
	$id = isset( $_GET['pedido'] ) ? absint( $_GET['pedido'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- se comprueba abajo
	check_admin_referer( 'dip_fs_pdf_' . $id );
	if ( ! current_user_can( 'edit_shop_orders' ) && ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'No tienes permiso para ver facturas.', 403 );
	$pedido = wc_get_order( $id );
	$numero = $pedido ? (string) $pedido->get_meta( '_dip_fs_numero' ) : '';
	if ( '' === $numero ) wp_die( 'Este pedido no tiene factura simplificada.', 404 );
	$pdf    = dip_fs_pdf_de_pedido( $pedido );
	$datos  = (array) $pedido->get_meta( '_dip_fs_datos' );
	$nombre = sprintf( dip_fs_textos( $datos['idioma'] ?? 'es' )['archivo'], $numero );
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: inline; filename="' . $nombre . '"' );
	header( 'Content-Length: ' . strlen( $pdf ) );
	echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- PDF binario
	exit;
} );

/* =========================================================
 * Email al cliente
 * ========================================================= */

/** Envía (o reenvía) la factura al email del pedido, desde DryIcePack <info@dryicepack.es>, con el PDF adjunto. */
function dip_fs_enviar( WC_Order $pedido, $pdf = null, $reenvio = false ) {
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	$datos  = $pedido->get_meta( '_dip_fs_datos' );
	if ( '' === $numero || ! is_array( $datos ) ) return false;
	$email = (string) $pedido->get_billing_email();
	if ( ! is_email( $email ) ) {
		dip_fs_avisar_fallo( $pedido, 'El pedido no tiene un email válido: no se ha podido enviar la factura ' . $numero . '.' );
		return false;
	}
	$idioma  = dip_fs_idioma_valido( $datos['idioma'] ?? 'es' );
	$t       = dip_fs_textos( $idioma );
	$c       = dip_contacto();
	$pdf     = null !== $pdf ? (string) $pdf : dip_fs_pdf_de_pedido( $pedido );
	$adjunto = dip_fs_archivo_temporal( sprintf( $t['archivo'], $numero ), $pdf );
	$texto   = dip_fs_texto_email( $pedido, $datos, $idioma );
	$alternativo = static function ( $phpmailer ) use ( $texto ) {
		$phpmailer->AltBody = $texto; // versión en texto para los programas de correo que no muestran HTML
	};
	add_action( 'phpmailer_init', $alternativo );
	$ok = wp_mail(
		$email,
		sprintf( $t['asunto'], $numero ),
		dip_fs_html_email( $pedido, $datos, $idioma ),
		array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $c['marca'] . ' <' . $c['email'] . '>',
			'Reply-To: ' . $c['marca'] . ' <' . $c['email'] . '>',
		),
		$adjunto ? array( $adjunto ) : array()
	);
	remove_action( 'phpmailer_init', $alternativo );
	if ( $adjunto ) {
		@unlink( $adjunto ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		@rmdir( dirname( $adjunto ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	if ( ! $ok ) {
		$pedido->update_meta_data( '_dip_fs_error', 'No se pudo enviar el email (' . dip_fs_ahora()->format( 'd/m/Y H:i' ) . ').' );
		$pedido->save_meta_data();
		dip_fs_avisar_fallo( $pedido, sprintf( 'No se ha podido enviar por email la factura simplificada %s a %s.', $numero, $email ) );
		return false;
	}
	$pedido->update_meta_data( '_dip_fs_enviada', time() );
	$pedido->update_meta_data( '_dip_fs_enviada_a', $email );
	$pedido->delete_meta_data( '_dip_fs_error' );
	$pedido->save_meta_data();
	$pedido->add_order_note( sprintf( '%s la factura simplificada %s a %s%s.', $reenvio ? 'Reenviada' : 'Enviada', $numero, $email, $adjunto ? ' con el PDF adjunto' : ' sin PDF adjunto (no se pudo crear el archivo temporal; el email lleva la factura completa en el texto)' ) );
	return true;
}

/** Email HTML con la marca: cabecera azul noche, ficha de la factura, líneas y total destacado. Estilos en línea. */
function dip_fs_html_email( WC_Order $pedido, array $datos, $idioma = 'es' ) {
	$t       = dip_fs_textos( $idioma );
	$e       = dip_fs_emisor( $idioma );
	$numero  = (string) $datos['numero'];
	$nombre  = trim( (string) $pedido->get_billing_first_name() );
	$entrega = function_exists( 'dip_texto_entrega' ) ? dip_texto_entrega( $pedido, $idioma ) : '';
	$url     = function_exists( 'dip_url_factura' ) ? dip_url_factura( $pedido ) : '';
	$pct     = dip_fs_porcentaje( $datos['tipo_iva'], $idioma );
	$sans    = "font-family:Inter,'Helvetica Neue',Helvetica,Arial,sans-serif";
	$titular = "font-family:'Inter Tight',Inter,'Helvetica Neue',Helvetica,Arial,sans-serif";
	$mono    = "font-family:'Geist Mono',SFMono-Regular,Menlo,Consolas,'Courier New',monospace";
	$h       = static fn( $s ) => esc_html( (string) $s );
	$saludo  = $nombre ? sprintf( $t['hola'], $nombre ) : $t['hola_sin'];

	// Ficha de la factura (datos técnicos en monoespaciada)
	$ficha = array(
		array( $t['l_numero'], $numero ),
		array( $t['l_expedicion'], dip_fs_fecha( $datos['fecha_expedicion'] ) ),
		array( $t['l_pedido'], $datos['pedido'] ),
	);
	if ( $datos['fecha_operacion'] ) $ficha[] = array( $t['l_operacion'], dip_fs_fecha( $datos['fecha_operacion'] ) . ' · ' . dip_fs_nota_operacion( $datos, $t ) );
	$html_ficha = '';
	foreach ( $ficha as $f ) {
		$html_ficha .= '<tr><td style="padding:6px 0;' . $mono . ';font-size:11px;line-height:16px;letter-spacing:.06em;color:#52657A;vertical-align:top;width:46%">' . $h( $f[0] ) . '</td>'
			. '<td style="padding:6px 0;' . $mono . ';font-size:14px;line-height:18px;font-weight:600;color:#0F1D29;vertical-align:top">' . $h( $f[1] ) . '</td></tr>';
	}

	// Líneas de la factura
	$html_lineas = '';
	foreach ( (array) $datos['lineas'] as $l ) {
		list( $titulo, $detalle ) = dip_fs_texto_linea( $l, $idioma );
		$cantidad = (int) ( $l['cantidad'] ?? 1 );
		$html_lineas .= '<tr><td style="padding:14px 12px 14px 0;border-bottom:1px solid #DCE8F3;vertical-align:top">'
			. '<div style="' . $sans . ';font-size:15px;line-height:21px;font-weight:600;color:#0F1D29">' . ( $cantidad > 1 ? $h( $cantidad . ' × ' ) : '' ) . $h( $titulo ) . '</div>'
			. ( $detalle ? '<div style="' . $sans . ';font-size:13px;line-height:19px;color:#52657A">' . $h( $detalle ) . '</div>' : '' )
			. '</td><td align="right" style="padding:14px 0;border-bottom:1px solid #DCE8F3;vertical-align:top;white-space:nowrap;' . $sans . ';font-size:15px;line-height:21px;color:#0F1D29">' . $h( dip_fs_importe( $l['importe'], $idioma ) ) . '</td></tr>';
	}
	$fila_suma = static function ( $etiqueta, $valor ) use ( $sans, $h ) {
		return '<tr><td style="padding:10px 12px 0 0;' . $sans . ';font-size:14px;line-height:20px;color:#52657A">' . $h( $etiqueta ) . '</td>'
			. '<td align="right" style="padding:10px 0 0;white-space:nowrap;' . $sans . ';font-size:14px;line-height:20px;color:#0F1D29">' . $h( $valor ) . '</td></tr>';
	};
	$html_lineas .= $fila_suma( $t['base'], dip_fs_importe( $datos['base'], $idioma ) );
	$html_lineas .= $fila_suma( sprintf( $t['iva'], $pct ), dip_fs_importe( $datos['cuota'], $idioma ) );

	$seguridad_url = 'https://' . $t['seguridad_url'];
	ob_start();
	?>
<!doctype html>
<html lang="<?php echo esc_attr( $idioma ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title><?php echo $h( sprintf( $t['asunto'], $numero ) ); ?></title>
</head>
<body style="margin:0;padding:0;background:#EEF3F7;-webkit-text-size-adjust:100%">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#EEF3F7"><?php echo $h( sprintf( $t['previo'], $datos['pedido'], dip_fs_importe( $datos['total'], $idioma ) ) ); ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EEF3F7">
<tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background:#FFFFFF;border:1px solid #C8D3DD;border-radius:6px;overflow:hidden">
	<tr><td style="background:#0B141C;padding:22px 32px">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
			<td style="<?php echo esc_attr( $titular ); ?>;font-size:18px;line-height:24px;font-weight:800;letter-spacing:.18em;color:#FFFFFF">DRYICEPACK</td>
			<td align="right" style="<?php echo esc_attr( $mono ); ?>;font-size:12px;line-height:24px;letter-spacing:.04em;color:#B8D0E8;white-space:nowrap"><?php echo $h( $t['frio'] ); ?></td>
		</tr></table>
	</td></tr>
	<tr><td style="padding:32px 32px 8px">
		<p style="margin:0 0 8px;<?php echo esc_attr( $mono ); ?>;font-size:12px;line-height:18px;letter-spacing:.06em;text-transform:uppercase;color:#52657A"><?php echo $h( $t['titulo'] . ' · ' . $numero ); ?></p>
		<h1 style="margin:0 0 16px;<?php echo esc_attr( $titular ); ?>;font-size:28px;line-height:34px;font-weight:800;color:#0F1D29"><?php echo $h( $t['h1'] ); ?></h1>
		<p style="margin:0;<?php echo esc_attr( $sans ); ?>;font-size:16px;line-height:25px;color:#0F1D29"><?php echo $h( $saludo . ' ' . sprintf( $t['intro'], $datos['pedido'] ) ); ?></p>
		<?php if ( $entrega ) : ?>
		<p style="margin:12px 0 0;<?php echo esc_attr( $sans ); ?>;font-size:16px;line-height:25px;font-weight:600;color:#0F1D29"><?php echo $h( $entrega ); ?></p>
		<?php endif; ?>
	</td></tr>
	<tr><td style="padding:16px 32px 8px">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#EEF3F7;border-radius:4px">
			<tr><td style="padding:12px 20px">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><?php echo $html_ficha; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba ?></table>
			</td></tr>
		</table>
	</td></tr>
	<tr><td style="padding:16px 32px 0">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td style="padding:0 12px 8px 0;border-bottom:2px solid #1F3C55;<?php echo esc_attr( $mono ); ?>;font-size:11px;line-height:16px;letter-spacing:.06em;color:#52657A"><?php echo $h( $t['l_descripcion'] ); ?></td>
				<td align="right" style="padding:0 0 8px;border-bottom:2px solid #1F3C55;<?php echo esc_attr( $mono ); ?>;font-size:11px;line-height:16px;letter-spacing:.06em;color:#52657A;white-space:nowrap"><?php echo $h( $t['l_importe'] ); ?></td>
			</tr>
			<?php echo $html_lineas; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba ?>
		</table>
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:16px;background:#1F3C55;border-radius:4px">
			<tr>
				<td style="padding:16px 20px;<?php echo esc_attr( $sans ); ?>;font-size:15px;line-height:22px;font-weight:700;color:#FFFFFF"><?php echo $h( $t['total'] ); ?></td>
				<td align="right" style="padding:16px 20px;<?php echo esc_attr( $titular ); ?>;font-size:24px;line-height:28px;font-weight:800;color:#FFFFFF;white-space:nowrap"><?php echo $h( dip_fs_importe( $datos['total'], $idioma ) ); ?></td>
			</tr>
		</table>
		<p style="margin:8px 0 0;<?php echo esc_attr( $sans ); ?>;font-size:13px;line-height:19px;color:#52657A"><?php echo $h( $t['nota_iva_1'] . ' ' . sprintf( $t['nota_iva_2'], $pct ) ); ?></p>
	</td></tr>
	<?php if ( $url ) : ?>
	<tr><td style="padding:24px 32px 0">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F6F8F9;border-left:3px solid #1F3C55">
			<tr><td style="padding:16px 20px">
				<p style="margin:0 0 4px;<?php echo esc_attr( $sans ); ?>;font-size:15px;line-height:22px;font-weight:700;color:#0F1D29"><?php echo $h( $t['completa_titulo'] ); ?></p>
				<p style="margin:0 0 10px;<?php echo esc_attr( $sans ); ?>;font-size:14px;line-height:21px;color:#0F1D29"><?php echo $h( $t['completa_texto'] ); ?></p>
				<p style="margin:0;<?php echo esc_attr( $sans ); ?>;font-size:15px;line-height:22px;font-weight:700"><a href="<?php echo esc_url( $url ); ?>" style="color:#1F3C55;text-decoration:underline"><?php echo $h( $t['completa_enlace'] ); ?> &rarr;</a></p>
			</td></tr>
		</table>
	</td></tr>
	<?php endif; ?>
	<tr><td style="padding:24px 32px 32px">
		<p style="margin:0 0 10px;<?php echo esc_attr( $sans ); ?>;font-size:13px;line-height:20px;color:#52657A"><?php echo $h( $t['seguridad'] ); ?> <a href="<?php echo esc_url( $seguridad_url ); ?>" style="color:#1F3C55"><?php echo $h( $t['seguridad_url'] ); ?></a></p>
		<p style="margin:0;<?php echo esc_attr( $sans ); ?>;font-size:13px;line-height:20px;color:#52657A"><?php echo $h( sprintf( $t['dudas'], $e['telefono'], $e['whatsapp'] ) ); ?></p>
	</td></tr>
	<tr><td style="background:#0B141C;padding:20px 32px;<?php echo esc_attr( $sans ); ?>;font-size:12px;line-height:19px;color:#9DB2C6">
		<?php echo $h( $datos['emisor']['razon'] . ' · NIF ' . $datos['emisor']['nif'] ); ?><br>
		<?php echo $h( $e['domicilio'] . ', ' . $e['cp_ciudad'] ); ?><br>
		<?php echo $h( $t['marca_de'] ); ?> · <a href="https://dryicepack.es/" style="color:#B8D0E8">dryicepack.es</a>
	</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/** La misma información en texto (parte alternativa del email). */
function dip_fs_texto_email( WC_Order $pedido, array $datos, $idioma = 'es' ) {
	$t      = dip_fs_textos( $idioma );
	$e      = dip_fs_emisor( $idioma );
	$nombre = trim( (string) $pedido->get_billing_first_name() );
	$pct    = dip_fs_porcentaje( $datos['tipo_iva'], $idioma );
	$entrega = function_exists( 'dip_texto_entrega' ) ? dip_texto_entrega( $pedido, $idioma ) : '';
	$url    = function_exists( 'dip_url_factura' ) ? dip_url_factura( $pedido ) : '';
	$sale   = array();
	$sale[] = ( $nombre ? sprintf( $t['hola'], $nombre ) : $t['hola_sin'] ) . ' ' . sprintf( $t['intro'], $datos['pedido'] );
	if ( $entrega ) $sale[] = $entrega;
	$sale[] = '';
	$sale[] = $t['titulo'] . ' ' . $datos['numero'];
	$sale[] = $t['l_expedicion'] . ': ' . dip_fs_fecha( $datos['fecha_expedicion'] );
	if ( $datos['fecha_operacion'] ) $sale[] = $t['l_operacion'] . ': ' . dip_fs_fecha( $datos['fecha_operacion'] ) . ' (' . dip_fs_nota_operacion( $datos, $t ) . ')';
	$sale[] = $t['l_pedido'] . ': ' . $datos['pedido'];
	$sale[] = '';
	foreach ( (array) $datos['lineas'] as $l ) {
		list( $titulo, $detalle ) = dip_fs_texto_linea( $l, $idioma );
		$sale[] = '- ' . ( (int) $l['cantidad'] > 1 ? (int) $l['cantidad'] . ' × ' : '' ) . $titulo . ( $detalle ? ' (' . $detalle . ')' : '' ) . ': ' . dip_fs_importe( $l['importe'], $idioma );
	}
	$sale[] = '';
	$sale[] = $t['base'] . ': ' . dip_fs_importe( $datos['base'], $idioma );
	$sale[] = sprintf( $t['iva'], $pct ) . ': ' . dip_fs_importe( $datos['cuota'], $idioma );
	$sale[] = $t['total'] . ': ' . dip_fs_importe( $datos['total'], $idioma );
	$sale[] = '';
	if ( $url ) {
		$sale[] = $t['completa_titulo'] . ' ' . $t['completa_texto'];
		$sale[] = $t['completa_enlace'] . ': ' . $url;
		$sale[] = '';
	}
	$sale[] = $t['seguridad'] . ' https://' . $t['seguridad_url'];
	$sale[] = sprintf( $t['dudas'], $e['telefono'], $e['whatsapp'] );
	$sale[] = '';
	$sale[] = $datos['emisor']['razon'] . ' · NIF ' . $datos['emisor']['nif'] . ' · ' . $e['domicilio'] . ', ' . $e['cp_ciudad'];
	return str_replace( "\u{00A0}", ' ', implode( "\n", $sale ) );
}

/* =========================================================
 * Avisos al correo de avisos
 * ========================================================= */

/** Algo ha fallado: email al correo de avisos y nota en el pedido, con la alternativa manual. */
function dip_fs_avisar_fallo( WC_Order $pedido, $problema ) {
	$aviso = (string) dip_ajuste( 'email_avisos' );
	$texto = $problema . "\n\n"
		. 'Pedido ' . $pedido->get_order_number() . ': ' . dip_fs_url_admin_pedido( $pedido ) . "\n\n"
		. "Qué hacer: abre el pedido y, en «Acciones del pedido», elige «Reenviar factura simplificada» (o «Emitir factura simplificada» si todavía no tiene número). Si tampoco funciona, haz la factura en la aplicación de la gestoría.\n";
	$ok = $aviso && wp_mail( $aviso, 'Dryicepack: revisar la factura simplificada del pedido ' . $pedido->get_order_number(), $texto );
	$pedido->add_order_note( 'Factura simplificada: ' . $problema . ( $ok ? ' Aviso enviado a ' . $aviso . '.' : ' No se ha podido enviar el aviso por email.' ) );
	return $ok;
}

/**
 * Devolución o cancelación de un pedido con factura simplificada: la web no emite rectificativas.
 * Nota en el pedido y aviso por email, una vez por cada hecho ($clave).
 */
function dip_fs_aviso_rectificativa( WC_Order $pedido, $clave, $que ) {
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	if ( '' === $numero ) return false;
	$hechos = $pedido->get_meta( '_dip_fs_rectificar' );
	$hechos = is_array( $hechos ) ? $hechos : array();
	if ( isset( $hechos[ $clave ] ) ) return false;
	$hechos[ $clave ] = time();
	$pedido->update_meta_data( '_dip_fs_rectificar', $hechos );
	$pedido->save_meta_data();

	$datos = (array) $pedido->get_meta( '_dip_fs_datos' );
	$nota  = $que . ' Hay que emitir una factura rectificativa de la ' . $numero . ' desde la aplicación de la gestoría. La web no emite rectificativas.';
	$texto = $nota . "\n\n"
		. 'Factura simplificada: ' . $numero . ' del ' . dip_fs_fecha( $datos['fecha_expedicion'] ?? '' ) . ', ' . dip_euros( $datos['total'] ?? 0 ) . " IVA incluido.\n"
		. 'Pedido ' . $pedido->get_order_number() . ': ' . dip_fs_url_admin_pedido( $pedido ) . "\n";
	$aviso = (string) dip_ajuste( 'email_avisos' );
	$ok    = $aviso && wp_mail( $aviso, 'Rectificativa pendiente: ' . $numero . ' (pedido ' . $pedido->get_order_number() . ')', $texto );
	$pedido->add_order_note( $nota . ( $ok ? '' : ' (No se ha podido enviar el aviso por email.)' ) );
	return true;
}

/*
 * Reembolso total: WooCommerce cambia el estado a "Reembolsado" y después avisa del reembolso. Los dos usan la
 * clave 'reembolso-total' para avisar una sola vez. Un reembolso parcial avisa con su importe.
 */
add_action( 'woocommerce_order_status_refunded', static function ( $pedido_id, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( ! $pedido ) return;
	$devuelto = (float) $pedido->get_total_refunded();
	dip_fs_aviso_rectificativa( $pedido, 'reembolso-total', 'Pedido con factura simplificada reembolsado' . ( $devuelto > 0 ? ' (' . dip_euros( $devuelto ) . ')' : '' ) . '.' );
}, 20, 2 );
add_action( 'woocommerce_order_refunded', static function ( $pedido_id, $reembolso_id = 0 ) {
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido ) return;
	$reembolso = $reembolso_id ? wc_get_order( $reembolso_id ) : null;
	$importe   = $reembolso ? abs( (float) $reembolso->get_amount() ) : 0.0;
	$total     = (float) $pedido->get_total() - (float) $pedido->get_total_refunded() <= 0.005;
	dip_fs_aviso_rectificativa(
		$pedido,
		$total ? 'reembolso-total' : 'reembolso-' . (int) $reembolso_id,
		( $total ? 'Pedido con factura simplificada reembolsado' : 'Reembolso parcial en un pedido con factura simplificada' ) . ( $importe > 0 ? ' (' . dip_euros( $importe ) . ')' : '' ) . '.'
	);
}, 20, 2 );
add_action( 'woocommerce_order_status_cancelled', static function ( $pedido_id, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido_id );
	if ( $pedido ) dip_fs_aviso_rectificativa( $pedido, 'cancelado', 'Pedido con factura simplificada cancelado.' );
}, 20, 2 );
/* El pedido se edita en el escritorio y su total cambia ("Recalcular"): la factura ya expedida no lo refleja. */
add_action( 'woocommerce_order_after_calculate_totals', static function ( $con_impuestos, $pedido = null ) {
	if ( ! $pedido instanceof WC_Order || '' === (string) $pedido->get_meta( '_dip_fs_numero' ) ) return; // los reembolsos no son WC_Order
	$datos = (array) $pedido->get_meta( '_dip_fs_datos' );
	$antes = round( (float) ( $datos['total'] ?? 0 ), 2 );
	$ahora = round( (float) $pedido->get_total(), 2 );
	if ( abs( $ahora - $antes ) < 0.005 ) return;
	dip_fs_aviso_rectificativa( $pedido, 'total-' . number_format( $ahora, 2, '.', '' ), sprintf( 'El total del pedido ha cambiado después de expedir la factura simplificada: de %s a %s.', dip_euros( $antes ), dip_euros( $ahora ) ) );
}, 20, 2 );

/* =========================================================
 * VeriFactu: aviso desde el 1/12/2026 y parada el 1/1/2027
 * ========================================================= */

function dip_fs_texto_verifactu( $parado ) {
	if ( $parado ) {
		return array(
			'Dryicepack: la factura simplificada automática está parada (VeriFactu)',
			"Desde el 1 de enero de 2027 la web ya no emite facturas simplificadas: no cumple VeriFactu (Real Decreto 1007/2023), que exige a todo programa que emite facturas una huella encadenada, un código QR y el envío de cada factura a la AEAT.\n\n"
			. "Los pedidos nuevos vuelven al flujo anterior: el cliente recibe el email «¿Necesitas factura?» y la factura se hace en la aplicación de la gestoría. Las facturas ya emitidas se pueden seguir descargando y reenviando desde cada pedido.\n\n"
			. "Para quitar este aviso, desactiva «Factura simplificada automática» en Dryicepack → Ajustes.\n",
		);
	}
	return array(
		'Dryicepack: la factura simplificada automática se para el 1 de enero de 2027',
		"Desde el 1 de enero de 2027, todo programa que emite facturas tiene que cumplir VeriFactu (Real Decreto 1007/2023): cada factura lleva una huella encadenada con la anterior y un código QR, y se envía a la AEAT.\n\n"
		. "El módulo de factura simplificada de la web no cumple esos requisitos. Por eso, el 1/1/2027 deja de emitir facturas solo y la web vuelve al flujo anterior: el cliente recibe el email «¿Necesitas factura?» y la factura se hace en la aplicación de la gestoría.\n\n"
		. "Qué hay que hacer antes del 1/1/2027:\n"
		. "1. Decidir con la gestoría si las facturas simplificadas de la web pasan a su aplicación (que ya cumple VeriFactu) o si se adapta este módulo.\n"
		. "2. Comprobar que las facturas de la serie de la web están registradas en la contabilidad (Dryicepack → Ajustes muestra el número siguiente).\n"
		. "3. Cuando esté decidido, desactivar «Factura simplificada automática» en Dryicepack → Ajustes.\n",
	);
}

/** Email al correo de avisos: uno desde el 1/12/2026 y otro el 1/1/2027. Si el email falla, lo vuelve a intentar al cabo de 6 horas. */
function dip_fs_vigilar_verifactu() {
	if ( ! dip_fs_activada() || ! dip_fs_verifactu_cerca() ) return;
	$parado = dip_fs_verifactu_parado();
	$clave  = $parado ? 'parado' : 'previo';
	$hechos = (array) get_option( 'dip_fs_avisos_verifactu', array() );
	if ( ! empty( $hechos[ $clave ] ) || get_transient( 'dip_fs_verifactu_intento' ) ) return;
	set_transient( 'dip_fs_verifactu_intento', 1, 6 * HOUR_IN_SECONDS );
	list( $asunto, $texto ) = dip_fs_texto_verifactu( $parado );
	if ( wp_mail( (string) dip_ajuste( 'email_avisos' ), $asunto, $texto ) ) {
		$hechos[ $clave ] = time();
		update_option( 'dip_fs_avisos_verifactu', $hechos, false );
	}
}
add_action( 'dip_tarea_diaria', 'dip_fs_vigilar_verifactu' );
add_action( 'admin_init', 'dip_fs_vigilar_verifactu' );

/* Aviso en el escritorio, en las pantallas de pedidos y en las de Dryicepack (mientras el ajuste siga activado). */
add_action( 'admin_notices', static function () {
	if ( ! current_user_can( 'manage_woocommerce' ) || ! dip_fs_activada() || ! dip_fs_verifactu_cerca() ) return;
	$pantalla = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $pantalla && ! preg_match( '/^(dashboard|toplevel_page_dryicepack|dryicepack_page_|woocommerce_page_wc-orders|edit-shop_order|shop_order)/', (string) $pantalla->id ) ) return;
	$parado = dip_fs_verifactu_parado();
	printf(
		'<div class="notice %1$s"><p><strong>%2$s</strong></p><p>%3$s</p></div>',
		$parado ? 'notice-error' : 'notice-warning',
		esc_html( $parado ? 'Factura simplificada automática parada desde el 1/1/2027 (VeriFactu).' : 'La factura simplificada automática se para el 1/1/2027 (VeriFactu).' ),
		esc_html( $parado
			? 'La web ya no emite facturas: no cumple el Real Decreto 1007/2023 (huella encadenada, código QR y envío a la AEAT). Los pedidos vuelven al email «¿Necesitas factura?» y la factura se hace en la aplicación de la gestoría. Desactiva «Factura simplificada automática» en Dryicepack → Ajustes para quitar este aviso.'
			: 'Desde esa fecha todo programa que emite facturas tiene que cumplir VeriFactu (Real Decreto 1007/2023: huella encadenada, código QR y envío a la AEAT) y este módulo no lo cumple. Antes del 1/1/2027, pasa las facturas simplificadas a la aplicación de la gestoría o adapta el módulo.' )
	);
} );

/* =========================================================
 * Escritorio: pedido, acciones y lista de pedidos
 * ========================================================= */

add_action( 'woocommerce_admin_order_data_after_billing_address', static function ( $pedido ) {
	if ( ! $pedido instanceof WC_Order ) return;
	$numero = (string) $pedido->get_meta( '_dip_fs_numero' );
	echo '<div class="dip-fs-pedido" style="clear:both;padding-top:8px"><h3>Factura simplificada</h3>';
	if ( '' === $numero ) {
		$motivo = dip_fs_motivo( $pedido, 'manual' );
		$desde  = (int) get_option( 'dip_fs_desde', 0 );
		$creado = $pedido->get_date_created();
		$viejo  = $desde && $creado && $creado->getTimestamp() < $desde;
		if ( '' !== $motivo ) {
			$texto = 'No se emite: ' . $motivo;
		} elseif ( $viejo ) {
			$texto = 'Sin emitir. Pedido anterior a la factura simplificada automática: antes de emitirla en «Acciones del pedido», comprueba que no está ya facturado en la aplicación de la gestoría.';
		} else {
			$texto = 'Sin emitir. Se puede emitir en «Acciones del pedido» → «Emitir factura simplificada».';
		}
		echo '<p>' . esc_html( $texto ) . '</p></div>';
		return;
	}
	$datos   = (array) $pedido->get_meta( '_dip_fs_datos' );
	$enviada = (int) $pedido->get_meta( '_dip_fs_enviada' );
	$error   = (string) $pedido->get_meta( '_dip_fs_error' );
	printf(
		'<p><strong style="font-family:Consolas,Menlo,monospace">%1$s</strong> · %2$s · %3$s IVA incluido<br><a href="%4$s" target="_blank" rel="noopener">Ver el PDF</a></p>',
		esc_html( $numero ),
		esc_html( 'expedida el ' . dip_fs_fecha( $datos['fecha_expedicion'] ?? '' ) ),
		esc_html( dip_euros( $datos['total'] ?? 0 ) ),
		esc_url( dip_fs_url_pdf( $pedido ) )
	);
	if ( $enviada ) echo '<p>' . esc_html( 'Enviada a ' . $pedido->get_meta( '_dip_fs_enviada_a' ) . ' el ' . wp_date( 'd/m/Y H:i', $enviada, dip_zona_horaria() ) . '.' ) . '</p>';
	if ( $error ) echo '<p style="color:#b32d2e">' . esc_html( $error . ' Usa «Reenviar factura simplificada» en «Acciones del pedido».' ) . '</p>';
	if ( $pedido->get_meta( '_dip_fs_rectificar' ) ) echo '<p style="color:#b32d2e">' . esc_html( 'Rectificativa pendiente: hazla desde la aplicación de la gestoría.' ) . '</p>';
	echo '</div>';
} );

add_filter( 'woocommerce_order_actions', static function ( $acciones, $pedido = null ) {
	$pedido = $pedido instanceof WC_Order ? $pedido : ( isset( $GLOBALS['theorder'] ) && $GLOBALS['theorder'] instanceof WC_Order ? $GLOBALS['theorder'] : null );
	if ( ! $pedido ) return $acciones;
	if ( '' !== (string) $pedido->get_meta( '_dip_fs_numero' ) ) {
		$acciones['dip_fs_reenviar'] = 'Reenviar factura simplificada (email)';
	} elseif ( '' === dip_fs_motivo( $pedido, 'manual' ) ) {
		$acciones['dip_fs_emitir'] = 'Emitir factura simplificada (email)';
	}
	return $acciones;
}, 20, 2 );
add_action( 'woocommerce_order_action_dip_fs_reenviar', static function ( $pedido ) {
	if ( $pedido instanceof WC_Order ) dip_fs_enviar( $pedido, null, true );
} );
add_action( 'woocommerce_order_action_dip_fs_emitir', static function ( $pedido ) {
	if ( $pedido instanceof WC_Order ) dip_fs_emitir( $pedido, 'manual' );
} );

/* Columna "Factura" en la lista de pedidos (tablas modernas y antiguas). */
foreach ( array( 'manage_woocommerce_page_wc-orders_columns', 'manage_edit-shop_order_columns' ) as $dip_filtro ) {
	add_filter( $dip_filtro, static function ( $columnas ) {
		$nuevas = array();
		foreach ( $columnas as $clave => $nombre ) {
			$nuevas[ $clave ] = $nombre;
			if ( 'order_total' === $clave ) $nuevas['dip_fs'] = 'Factura';
		}
		if ( ! isset( $nuevas['dip_fs'] ) ) $nuevas['dip_fs'] = 'Factura';
		return $nuevas;
	}, 20 );
}
function dip_fs_columna( $columna, $pedido ) {
	if ( 'dip_fs' !== $columna ) return;
	$pedido = $pedido instanceof WC_Order ? $pedido : wc_get_order( $pedido );
	$numero = $pedido ? (string) $pedido->get_meta( '_dip_fs_numero' ) : '';
	if ( '' === $numero ) {
		echo '<span aria-hidden="true">—</span>';
		return;
	}
	echo '<span style="font-family:Consolas,Menlo,monospace;font-size:12px" title="Factura simplificada">' . esc_html( $numero ) . '</span>';
	if ( $pedido->get_meta( '_dip_fs_rectificar' ) ) echo '<br><small style="color:#b32d2e">Rectificar</small>';
	elseif ( $pedido->get_meta( '_dip_fs_error' ) ) echo '<br><small style="color:#b32d2e">Sin enviar</small>';
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'dip_fs_columna', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'dip_fs_columna', 10, 2 );

/* =========================================================
 * PDF mínimo: una página A4, fuentes estándar (Helvetica y Courier) con WinAnsiEncoding
 * ========================================================= */

function dip_fs_pdf( array $datos, $idioma = null ) {
	$idioma = dip_fs_idioma_valido( $idioma ?: ( $datos['idioma'] ?? 'es' ) );
	$t      = dip_fs_textos( $idioma );
	$e      = dip_fs_emisor( $idioma );
	$pdf    = new DIP_FS_PDF();
	$col    = array(
		'noche'    => '0.043 0.078 0.11',
		'marino'   => '0.122 0.235 0.333',
		'tinta'    => '0.059 0.114 0.161',
		'acero'    => '0.322 0.396 0.478',
		'hielo'    => '0.722 0.816 0.91',
		'escarcha' => '0.933 0.953 0.969',
		'linea'    => '0.784 0.827 0.867',
		'blanco'   => '1 1 1',
	);
	$x0  = 56;
	$x1  = 539.28;
	$xd  = 318;      // columna derecha
	$xc  = 420;      // columna de cantidades
	$pct = dip_fs_porcentaje( $datos['tipo_iva'], $idioma );
	$etiqueta = static function ( $x, $y, $texto, $alinear = 'izq' ) use ( $pdf, $col ) {
		$pdf->texto( $x, $y, $texto, 'F3', 7, $col['acero'], $alinear, 0.3 );
	};

	// Cabecera: banda azul noche con la marca y la cifra de la casa
	$pdf->rect( 0, 785.89, 595.28, 56, $col['noche'] );
	$pdf->texto( $x0, 808, 'DRYICEPACK', 'F2', 15, $col['blanco'], 'izq', 2.2 );
	$pdf->texto( $x1, 808.5, $t['frio'], 'F3', 9, $col['hielo'], 'der', 0.4 );

	// Título
	$pdf->texto( $x0, 738, $t['titulo'], 'F2', 24, $col['tinta'] );
	$pdf->texto( $x0, 720, sprintf( $t['subtitulo'], $pct ), 'F1', 10, $col['acero'] );

	// Ficha: número, pedido y fechas
	$etiqueta( $xd, 744, $t['l_numero'] );
	$pdf->texto( $xd, 730, $datos['numero'], 'F4', 11, $col['tinta'] );
	$etiqueta( $xd + 120, 744, $t['l_pedido'] );
	$pdf->texto( $xd + 120, 730, $datos['pedido'], 'F4', 11, $col['tinta'] );
	$etiqueta( $xd, 710, $t['l_expedicion'] );
	$pdf->texto( $xd, 696, dip_fs_fecha( $datos['fecha_expedicion'] ), 'F4', 11, $col['tinta'] );
	if ( $datos['fecha_operacion'] ) {
		$etiqueta( $xd + 120, 710, $t['l_operacion'] );
		$pdf->texto( $xd + 120, 696, dip_fs_fecha( $datos['fecha_operacion'] ), 'F4', 11, $col['tinta'] );
		$pdf->texto( $xd + 120, 685, dip_fs_nota_operacion( $datos, $t ), 'F1', 7.5, $col['acero'] );
	}
	if ( '' !== (string) $datos['cliente'] ) {
		$etiqueta( $xd, 664, $t['l_cliente'] );
		$pdf->texto( $xd, 650, DIP_FS_PDF::recortar( $datos['cliente'], 'F2', 10, $x1 - $xd ), 'F2', 10, $col['tinta'] );
	}
	if ( '' !== (string) $datos['pago'] ) {
		$etiqueta( $xd, 630, $t['l_pago'] );
		$pdf->texto( $xd, 617, DIP_FS_PDF::recortar( $datos['pago'], 'F1', 9.5, $x1 - $xd ), 'F1', 9.5, $col['tinta'] );
	}

	// Emisor
	$etiqueta( $x0, 690, $t['l_emisor'] );
	$pdf->texto( $x0, 676, $datos['emisor']['razon'], 'F2', 10, $col['tinta'] );
	$pdf->texto( $x0, 663, 'NIF ' . $datos['emisor']['nif'], 'F1', 9, $col['tinta'] );
	$pdf->texto( $x0, 651, $e['domicilio'], 'F1', 9, $col['tinta'] );
	$pdf->texto( $x0, 639, $e['cp_ciudad'], 'F1', 9, $col['tinta'] );
	$pdf->texto( $x0, 625, $t['marca_de'], 'F1', 8, $col['acero'] );
	$pdf->texto( $x0, 614, $e['email'] . ' · ' . $e['telefono'], 'F1', 8, $col['acero'] );

	// Líneas
	$pdf->linea( $x0, 594, $x1, 594, 0.75, $col['linea'] );
	$etiqueta( $x0, 574, $t['l_descripcion'] );
	$etiqueta( $xc, 574, $t['l_cantidad'], 'der' );
	$etiqueta( $x1, 574, $t['l_importe'], 'der' );
	$pdf->linea( $x0, 566, $x1, 566, 1, $col['marino'] );
	$y        = 566;
	$lineas   = (array) $datos['lineas'];
	$compacta = count( $lineas ) > 8;
	foreach ( $lineas as $l ) {
		list( $titulo, $detalle ) = dip_fs_texto_linea( $l, $idioma );
		if ( $compacta && $detalle ) {
			$titulo .= ' · ' . $detalle;
			$detalle = '';
		}
		$alto = $detalle ? 32 : ( $compacta ? 20 : 24 );
		$base = $y - ( $compacta ? 13 : 15 );
		$tam  = $compacta ? 9 : 10;
		$pdf->texto( $x0, $base, DIP_FS_PDF::recortar( $titulo, 'F2', $tam, $xc - $x0 - 40 ), 'F2', $tam, $col['tinta'] );
		if ( $detalle ) $pdf->texto( $x0, $base - 12, DIP_FS_PDF::recortar( $detalle, 'F1', 8.5, $xc - $x0 - 40 ), 'F1', 8.5, $col['acero'] );
		$pdf->texto( $xc, $base, (string) (int) $l['cantidad'], 'F1', $tam, $col['tinta'], 'der' );
		$pdf->texto( $x1, $base, dip_fs_importe( $l['importe'], $idioma ), 'F1', $tam, $col['tinta'], 'der' );
		$y -= $alto;
		$pdf->linea( $x0, $y, $x1, $y, 0.5, $col['linea'] );
	}

	// Totales a la derecha; a la izquierda, la nota del IVA
	$y -= 24;
	$pdf->rect( $x0, $y - 62, 236, 76, $col['escarcha'] );
	$pdf->texto( $x0 + 14, $y - 14, $t['nota_iva_1'], 'F2', 8.5, $col['tinta'] );
	$pdf->texto( $x0 + 14, $y - 29, sprintf( $t['nota_iva_2'], $pct ), 'F1', 8.5, $col['acero'] );
	$pdf->texto( $x0 + 14, $y - 43, sprintf( $t['nota_iva_3'], dip_fs_importe( $datos['cuota'], $idioma ) ), 'F1', 8.5, $col['acero'] );

	$pdf->texto( $xd, $y, $t['base'], 'F1', 9.5, $col['acero'] );
	$pdf->texto( $x1, $y, dip_fs_importe( $datos['base'], $idioma ), 'F1', 9.5, $col['tinta'], 'der' );
	$pdf->texto( $xd, $y - 16, sprintf( $t['iva'], $pct ), 'F1', 9.5, $col['acero'] );
	$pdf->texto( $x1, $y - 16, dip_fs_importe( $datos['cuota'], $idioma ), 'F1', 9.5, $col['tinta'], 'der' );
	$pdf->rect( $xd, $y - 62, $x1 - $xd, 34, $col['marino'] );
	$pdf->texto( $xd + 12, $y - 49, $t['total'], 'F2', 10, $col['blanco'] );
	$pdf->texto( $x1 - 12, $y - 50.5, dip_fs_importe( $datos['total'], $idioma ), 'F2', 15, $col['blanco'], 'der' );

	// Pie
	$pdf->linea( $x0, 104, $x1, 104, 0.5, $col['linea'] );
	$yy = 90;
	foreach ( DIP_FS_PDF::partir( $t['pie_legal'] . ' ' . $t['pie_completa'], 'F1', 7.5, $x1 - $x0 ) as $texto ) {
		$pdf->texto( $x0, $yy, $texto, 'F1', 7.5, $col['acero'] );
		$yy -= 10.5;
	}
	$pdf->texto( $x0, 54, $datos['emisor']['razon'] . ' · NIF ' . $datos['emisor']['nif'] . ' · ' . $e['domicilio'] . ' · ' . $e['cp_ciudad'] . ' · ' . $e['web'], 'F1', 7, $col['acero'] );

	return $pdf->salida( array(
		'titulo' => $t['titulo'] . ' ' . $datos['numero'],
		'autor'  => $datos['emisor']['razon'],
		'fecha'  => (string) ( $datos['expedida'] ?? $datos['fecha_expedicion'] ),
	) );
}

/**
 * Generador de PDF de una página con las 14 fuentes estándar (no hace falta incrustar nada).
 * Los textos llegan en UTF-8 y se pasan a WinAnsiEncoding (Windows-1252): €, ñ, acentos, ·, °.
 * El signo menos (−) no existe en esa codificación: sale como raya corta (–).
 */
class DIP_FS_PDF {
	const FUENTES = array( 'F1' => 'Helvetica', 'F2' => 'Helvetica-Bold', 'F3' => 'Courier', 'F4' => 'Courier-Bold' );

	private $ops = array();

	/** Códigos de Unicode del texto (UTF-8). */
	public static function puntos( $texto ) {
		$texto = (string) $texto;
		if ( '' === $texto ) return array();
		if ( ! preg_match_all( '/./us', $texto, $m ) ) {
			// No es UTF-8 válido: se queda con lo que sea ASCII
			return array_values( array_filter( array_map( 'ord', str_split( $texto ) ), static fn( $c ) => $c < 128 ) );
		}
		$puntos = array();
		foreach ( $m[0] as $c ) {
			$o = ord( $c[0] );
			if ( $o < 0x80 ) $puntos[] = $o;
			elseif ( $o < 0xE0 ) $puntos[] = ( ( $o & 0x1F ) << 6 ) | ( ord( $c[1] ) & 0x3F );
			elseif ( $o < 0xF0 ) $puntos[] = ( ( $o & 0x0F ) << 12 ) | ( ( ord( $c[1] ) & 0x3F ) << 6 ) | ( ord( $c[2] ) & 0x3F );
			else $puntos[] = ( ( $o & 0x07 ) << 18 ) | ( ( ord( $c[1] ) & 0x3F ) << 12 ) | ( ( ord( $c[2] ) & 0x3F ) << 6 ) | ( ord( $c[3] ) & 0x3F );
		}
		return $puntos;
	}

	/** UTF-8 → Windows-1252 (WinAnsiEncoding). Lo que no existe se translitera o queda como "?". */
	public static function win( $texto ) {
		static $especiales = array(
			0x20AC => 128, 0x201A => 130, 0x0192 => 131, 0x201E => 132, 0x2026 => 133, 0x2020 => 134, 0x2021 => 135,
			0x02C6 => 136, 0x2030 => 137, 0x0160 => 138, 0x2039 => 139, 0x0152 => 140, 0x017D => 142, 0x2018 => 145,
			0x2019 => 146, 0x201C => 147, 0x201D => 148, 0x2022 => 149, 0x2013 => 150, 0x2014 => 151, 0x02DC => 152,
			0x2122 => 153, 0x0161 => 154, 0x203A => 155, 0x0153 => 156, 0x017E => 158, 0x0178 => 159,
			0x2212 => 150, 0x2010 => 45, 0x2011 => 45, 0x2012 => 150, 0x202F => 32, 0x2009 => 32, 0x200A => 32,
			0x2007 => 32, 0x2032 => 39, 0x2033 => 34, 0x00AD => 45,
		);
		$salida = '';
		foreach ( self::puntos( $texto ) as $p ) {
			if ( $p >= 32 && $p < 127 ) $salida .= chr( $p );
			elseif ( $p < 32 || 127 === $p ) $salida .= ' ';
			elseif ( $p >= 0xA0 && $p <= 0xFF ) $salida .= chr( $p );
			elseif ( isset( $especiales[ $p ] ) ) $salida .= chr( $especiales[ $p ] );
			else {
				$letra = function_exists( 'remove_accents' ) ? remove_accents( self::utf8( $p ) ) : '';
				$salida .= ( '' !== $letra && preg_match( '/^[\x20-\x7E]+$/', $letra ) ) ? $letra : '?';
			}
		}
		return $salida;
	}

	private static function utf8( $p ) {
		if ( $p < 0x80 ) return chr( $p );
		if ( $p < 0x800 ) return chr( 0xC0 | ( $p >> 6 ) ) . chr( 0x80 | ( $p & 0x3F ) );
		if ( $p < 0x10000 ) return chr( 0xE0 | ( $p >> 12 ) ) . chr( 0x80 | ( ( $p >> 6 ) & 0x3F ) ) . chr( 0x80 | ( $p & 0x3F ) );
		return chr( 0xF0 | ( $p >> 18 ) ) . chr( 0x80 | ( ( $p >> 12 ) & 0x3F ) ) . chr( 0x80 | ( ( $p >> 6 ) & 0x3F ) ) . chr( 0x80 | ( $p & 0x3F ) );
	}

	/** Ancho de un carácter de Windows-1252 en milésimas de em (métricas AFM de Adobe). */
	private static function ancho_letra( $codigo, $fuente ) {
		if ( 'F3' === $fuente || 'F4' === $fuente ) return 600;
		static $ascii = null;
		if ( null === $ascii ) {
			$ascii = array(
				'F1' => array_map( 'intval', explode( ',', '278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,278,278,584,584,584,556,1015,667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,278,278,278,469,556,333,556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,334,260,334,584' ) ),
				'F2' => array_map( 'intval', explode( ',', '278,333,474,556,556,889,722,238,333,333,389,584,278,333,278,278,556,556,556,556,556,556,556,556,556,556,333,333,584,584,584,611,975,722,722,722,722,667,611,778,722,278,556,722,611,833,722,778,667,778,722,667,611,722,667,944,667,667,611,333,278,333,584,556,333,556,611,556,611,556,333,611,611,278,278,556,278,889,611,611,611,611,389,556,333,611,556,778,556,556,500,389,280,389,584' ) ),
			);
		}
		$negrita = 'F2' === $fuente ? 1 : 0;
		if ( $codigo >= 32 && $codigo <= 126 ) return $ascii[ $negrita ? 'F2' : 'F1' ][ $codigo - 32 ];
		static $altos = array(
			128 => array( 556, 556 ), 130 => array( 222, 278 ), 132 => array( 333, 500 ), 133 => array( 1000, 1000 ),
			145 => array( 222, 278 ), 146 => array( 222, 278 ), 147 => array( 333, 500 ), 148 => array( 333, 500 ),
			149 => array( 350, 350 ), 150 => array( 556, 556 ), 151 => array( 1000, 1000 ), 153 => array( 1000, 1000 ),
			160 => array( 278, 278 ), 161 => array( 333, 333 ), 170 => array( 370, 370 ), 171 => array( 556, 556 ),
			176 => array( 400, 400 ), 183 => array( 278, 278 ), 186 => array( 365, 365 ), 187 => array( 556, 556 ),
			191 => array( 611, 611 ), 199 => array( 722, 722 ), 209 => array( 722, 722 ), 215 => array( 584, 584 ),
			216 => array( 778, 778 ), 221 => array( 667, 667 ), 231 => array( 500, 556 ), 241 => array( 556, 611 ),
			248 => array( 611, 611 ), 253 => array( 500, 556 ), 255 => array( 500, 556 ),
		);
		if ( isset( $altos[ $codigo ] ) ) return $altos[ $codigo ][ $negrita ];
		if ( $codigo >= 192 && $codigo <= 197 ) return $negrita ? 722 : 667; // À-Å
		if ( $codigo >= 200 && $codigo <= 203 ) return 667;                    // È-Ë
		if ( $codigo >= 204 && $codigo <= 207 ) return 278;                    // Ì-Ï
		if ( $codigo >= 210 && $codigo <= 214 ) return 778;                    // Ò-Ö
		if ( $codigo >= 217 && $codigo <= 220 ) return 722;                    // Ù-Ü
		if ( $codigo >= 224 && $codigo <= 229 ) return 556;                    // à-å
		if ( $codigo >= 232 && $codigo <= 235 ) return 556;                    // è-ë
		if ( $codigo >= 236 && $codigo <= 239 ) return 278;                    // ì-ï
		if ( ( $codigo >= 242 && $codigo <= 246 ) || ( $codigo >= 249 && $codigo <= 252 ) ) return $negrita ? 611 : 556; // ò-ö, ù-ü
		return 556;
	}

	private static function ancho_win( $win, $fuente, $tam, $tracking = 0 ) {
		$suma = 0;
		$n    = strlen( $win );
		for ( $i = 0; $i < $n; $i++ ) $suma += self::ancho_letra( ord( $win[ $i ] ), $fuente );
		return $suma * $tam / 1000 + $tracking * max( 0, $n - 1 );
	}

	/** Ancho en puntos de un texto UTF-8. */
	public static function ancho( $texto, $fuente = 'F1', $tam = 10, $tracking = 0 ) {
		return self::ancho_win( self::win( $texto ), $fuente, $tam, $tracking );
	}

	/** Corta el texto con "…" para que quepa en $max puntos. */
	public static function recortar( $texto, $fuente, $tam, $max ) {
		$texto = (string) $texto;
		if ( self::ancho( $texto, $fuente, $tam ) <= $max ) return $texto;
		$letras = preg_split( '//u', $texto, -1, PREG_SPLIT_NO_EMPTY ) ?: str_split( $texto );
		while ( $letras && self::ancho( implode( '', $letras ) . '…', $fuente, $tam ) > $max ) array_pop( $letras );
		return rtrim( implode( '', $letras ) ) . '…';
	}

	/** Parte el texto en líneas de $max puntos como mucho. */
	public static function partir( $texto, $fuente, $tam, $max ) {
		$lineas = array();
		$actual = '';
		foreach ( preg_split( '/\s+/u', trim( (string) $texto ) ) as $palabra ) {
			$prueba = '' === $actual ? $palabra : $actual . ' ' . $palabra;
			if ( '' !== $actual && self::ancho( $prueba, $fuente, $tam ) > $max ) {
				$lineas[] = $actual;
				$actual   = $palabra;
			} else {
				$actual = $prueba;
			}
		}
		if ( '' !== $actual ) $lineas[] = $actual;
		return $lineas;
	}

	private static function n( $valor ) {
		$texto = rtrim( rtrim( sprintf( '%.3F', (float) $valor ), '0' ), '.' );
		return ( '' === $texto || '-0' === $texto ) ? '0' : $texto;
	}

	private static function escapar( $win ) {
		return strtr( $win, array( '\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => ' ', "\n" => ' ' ) );
	}

	/** Cadena de texto del PDF en UTF-16BE (para el título y el autor del documento). */
	private static function utf16( $texto ) {
		$hex = 'FEFF';
		foreach ( self::puntos( $texto ) as $p ) {
			if ( $p > 0xFFFF ) {
				$p  -= 0x10000;
				$hex .= sprintf( '%04X%04X', 0xD800 | ( $p >> 10 ), 0xDC00 | ( $p & 0x3FF ) );
			} else {
				$hex .= sprintf( '%04X', $p );
			}
		}
		return '<' . $hex . '>';
	}

	public function texto( $x, $y, $texto, $fuente = 'F1', $tam = 10, $color = '0 0 0', $alinear = 'izq', $tracking = 0 ) {
		$win = self::win( $texto );
		if ( '' === $win ) return;
		$ancho = self::ancho_win( $win, $fuente, $tam, $tracking );
		if ( 'der' === $alinear ) $x -= $ancho;
		elseif ( 'centro' === $alinear ) $x -= $ancho / 2;
		$this->ops[] = 'BT /' . $fuente . ' ' . self::n( $tam ) . ' Tf ' . $color . ' rg ' . self::n( $tracking ) . ' Tc ' . self::n( $x ) . ' ' . self::n( $y ) . ' Td (' . self::escapar( $win ) . ') Tj ET';
	}

	public function rect( $x, $y, $ancho, $alto, $color ) {
		$this->ops[] = $color . ' rg ' . self::n( $x ) . ' ' . self::n( $y ) . ' ' . self::n( $ancho ) . ' ' . self::n( $alto ) . ' re f';
	}

	public function linea( $x1, $y1, $x2, $y2, $grosor, $color ) {
		$this->ops[] = $color . ' RG ' . self::n( $grosor ) . ' w ' . self::n( $x1 ) . ' ' . self::n( $y1 ) . ' m ' . self::n( $x2 ) . ' ' . self::n( $y2 ) . ' l S';
	}

	/** El documento completo: catálogo, página, fuentes, contenido, información, tabla xref y trailer. */
	public function salida( array $info = array() ) {
		$objetos = array(
			1 => '<< /Type /Catalog /Pages 2 0 R >>',
			2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		);
		$fuentes = '';
		$id      = 4;
		foreach ( self::FUENTES as $clave => $nombre ) {
			$objetos[ $id ] = '<< /Type /Font /Subtype /Type1 /BaseFont /' . $nombre . ' /Encoding /WinAnsiEncoding >>';
			$fuentes       .= '/' . $clave . ' ' . $id . ' 0 R ';
			$id++;
		}
		$contenido      = implode( "\n", $this->ops );
		$objetos[ $id ] = '<< /Length ' . strlen( $contenido ) . " >>\nstream\n" . $contenido . "\nendstream";
		$objetos[3]     = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /Font << ' . $fuentes . '>> /ProcSet [/PDF /Text] >> /Contents ' . $id . ' 0 R >>';
		$id++;
		$fecha          = preg_replace( '/\D/', '', (string) ( $info['fecha'] ?? '' ) );
		$objetos[ $id ] = '<< /Title ' . self::utf16( $info['titulo'] ?? '' ) . ' /Author ' . self::utf16( $info['autor'] ?? '' ) . ' /Creator (DryIcePack) /Producer (DryIcePack)' . ( $fecha ? ' /CreationDate (D:' . str_pad( substr( $fecha, 0, 14 ), 14, '0' ) . ')' : '' ) . ' >>';
		$info_id        = $id;
		ksort( $objetos );

		$pdf     = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
		$offsets = array();
		foreach ( $objetos as $n => $cuerpo ) {
			$offsets[ $n ] = strlen( $pdf );
			$pdf          .= $n . " 0 obj\n" . $cuerpo . "\nendobj\n";
		}
		$xref = strlen( $pdf );
		$pdf .= "xref\n0 " . ( count( $objetos ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $offsets as $offset ) $pdf .= sprintf( "%010d 00000 n \n", $offset );
		$pdf .= "trailer\n<< /Size " . ( count( $objetos ) + 1 ) . ' /Root 1 0 R /Info ' . $info_id . " 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
		return $pdf;
	}
}
