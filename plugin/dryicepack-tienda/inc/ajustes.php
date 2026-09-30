<?php
/**
 * Ajustes del plugin (Escritorio → Dryicepack → Ajustes) y menú de administración.
 * Todo lo que puede cambiar sin tocar código vive aquí: festivos, Google Analytics, correos y recordatorios.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/** Festivos por defecto: nacionales + Cataluña (DOGC) + locales de Mataró conocidos. Se amplían desde los ajustes. */
function dip_festivos_por_defecto() {
	return array(
		// 2026 (Cataluña, Ordre EMT/…/2025) · locales de Mataró: 25/05 y 27/07
		'2026-10-12', '2026-12-08', '2026-12-25', '2026-12-26',
		// 2027 (Cataluña, Ordre EMT/52/2026, DOGC 9637 de 1/4/2026). Los locales de Mataró de 2027 se publican a final de 2026.
		'2027-01-01', '2027-01-06', '2027-03-26', '2027-03-29', '2027-05-01', '2027-06-24',
		'2027-09-11', '2027-10-12', '2027-11-01', '2027-12-06', '2027-12-08', '2027-12-25',
	);
}

function dip_ajustes_por_defecto() {
	return array(
		'ga4'               => '',
		'email_avisos'      => 'info@dryicepack.es',
		'festivos'          => implode( "\n", dip_festivos_por_defecto() ),
		'recordatorios'     => 0,
		'resenas_url'       => '',
		'limite_factura'    => 400,
	);
}

function dip_ajuste( $clave ) {
	static $ajustes = null;
	if ( null === $ajustes ) {
		$ajustes = wp_parse_args( (array) get_option( 'dip_ajustes', array() ), dip_ajustes_por_defecto() );
	}
	return $ajustes[ $clave ] ?? null;
}

/* ---------- Menú ---------- */
add_action( 'admin_menu', static function () {
	add_menu_page( 'Dryicepack', 'Dryicepack', 'manage_woocommerce', 'dryicepack', 'dip_pantalla_ajustes', 'dashicons-admin-generic', 56 );
	add_submenu_page( 'dryicepack', 'Ajustes', 'Ajustes', 'manage_woocommerce', 'dryicepack', 'dip_pantalla_ajustes' );
}, 9 );

add_action( 'admin_init', static function () {
	register_setting( 'dip_ajustes', 'dip_ajustes', array(
		'type'              => 'array',
		'sanitize_callback' => 'dip_sanear_ajustes',
		'default'           => dip_ajustes_por_defecto(),
	) );
} );

function dip_sanear_ajustes( $entrada ) {
	$entrada = (array) $entrada;
	$fechas  = array();
	foreach ( preg_split( '/[\s,;]+/', (string) ( $entrada['festivos'] ?? '' ) ) as $f ) {
		$f = trim( $f );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f ) && strtotime( $f ) ) $fechas[] = $f;
	}
	$fechas = array_values( array_unique( $fechas ) );
	sort( $fechas );
	$ga4 = strtoupper( trim( (string) ( $entrada['ga4'] ?? '' ) ) );
	return array(
		'ga4'            => preg_match( '/^G-[A-Z0-9]{4,}$/', $ga4 ) ? $ga4 : '',
		'email_avisos'   => sanitize_email( $entrada['email_avisos'] ?? '' ) ?: 'info@dryicepack.es',
		'festivos'       => implode( "\n", $fechas ),
		'recordatorios'  => empty( $entrada['recordatorios'] ) ? 0 : 1,
		'resenas_url'    => esc_url_raw( trim( (string) ( $entrada['resenas_url'] ?? '' ) ) ),
		'limite_factura' => max( 0, (float) str_replace( ',', '.', (string) ( $entrada['limite_factura'] ?? 400 ) ) ),
	);
}

function dip_pantalla_ajustes() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$a = wp_parse_args( (array) get_option( 'dip_ajustes', array() ), dip_ajustes_por_defecto() );
	?>
	<div class="wrap">
		<h1>Dryicepack · Ajustes</h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'dip_ajustes' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="dip-ga4">Google Analytics 4</label></th>
					<td><input id="dip-ga4" name="dip_ajustes[ga4]" type="text" class="regular-text" value="<?php echo esc_attr( $a['ga4'] ); ?>" placeholder="G-XXXXXXXXXX">
						<p class="description">ID de medición (empieza por G-). Se carga solo cuando el visitante acepta las cookies de análisis. Si lo pones aquí, desactiva en Site Kit la opción «Colocar el código de Analytics» para no medir dos veces.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-email">Correo de avisos</label></th>
					<td><input id="dip-email" name="dip_ajustes[email_avisos]" type="email" class="regular-text" value="<?php echo esc_attr( $a['email_avisos'] ); ?>">
						<p class="description">Recibe los formularios, las solicitudes de factura y las altas de cuenta de empresa.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-festivos">Festivos sin salida ni entrega</label></th>
					<td><textarea id="dip-festivos" name="dip_ajustes[festivos]" rows="10" cols="20" class="code"><?php echo esc_textarea( $a['festivos'] ); ?></textarea>
						<p class="description">Una fecha por línea (AAAA-MM-DD). Vienen cargados los de Cataluña de 2026 y 2027. Añade los locales de Mataró de 2027 cuando se publiquen.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-limite">Factura completa obligatoria desde</label></th>
					<td><input id="dip-limite" name="dip_ajustes[limite_factura]" type="number" step="1" min="0" class="small-text" value="<?php echo esc_attr( $a['limite_factura'] ); ?>"> € (IVA incluido)
						<p class="description">Por encima de este importe el checkout pide razón social y NIF/CIF siempre.</p></td>
				</tr>
				<tr>
					<th scope="row">Recordatorio de reposición</th>
					<td><label><input name="dip_ajustes[recordatorios]" type="checkbox" value="1" <?php checked( $a['recordatorios'], 1 ); ?>> Enviar a las cuentas de empresa que lo aceptaron un aviso cuando les toca repetir el pedido</label></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-resenas">Enlace para reseñas de Google</label></th>
					<td><input id="dip-resenas" name="dip_ajustes[resenas_url]" type="url" class="regular-text" value="<?php echo esc_attr( $a['resenas_url'] ); ?>" placeholder="https://g.page/r/…/review">
						<p class="description">Ficha de Google → Pedir reseñas → Copiar enlace. Aparece en el email de pedido completado.</p></td>
				</tr>
			</table>
			<?php submit_button( 'Guardar ajustes' ); ?>
		</form>
	</div>
	<?php
}
