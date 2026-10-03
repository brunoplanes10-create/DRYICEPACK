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
	// GA4 y reseñas de serie solo en la web real: las copias de prueba no envían datos a Analytics
	$produccion = false !== strpos( (string) wp_parse_url( home_url(), PHP_URL_HOST ), 'dryicepack.es' );
	return array(
		'ga4'               => $produccion ? 'G-G1272ZSS41' : '',
		'email_avisos'      => 'info@dryicepack.es',
		'festivos'          => implode( "\n", dip_festivos_por_defecto() ),
		'recordatorios'     => 0,
		'resenas_url'       => 'https://g.page/r/CTbZyC_fpj4LECE/review',
		'limite_factura'    => 400,
		'factura_simplificada' => 1,   // inc/factura-simplificada.php
		'serie_simplificada'   => 'W', // + año: W2026-00001
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
		'factura_simplificada' => empty( $entrada['factura_simplificada'] ) ? 0 : 1,
		'serie_simplificada'   => substr( (string) preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) ( $entrada['serie_simplificada'] ?? '' ) ) ), 0, 6 ) ?: 'W',
	);
}

function dip_pantalla_ajustes() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$a = wp_parse_args( (array) get_option( 'dip_ajustes', array() ), dip_ajustes_por_defecto() );
	$mrw_hecho = isset( $_GET['dip_mrw_hecho'] ) ? sanitize_key( wp_unslash( $_GET['dip_mrw_hecho'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo muestra un aviso
	?>
	<div class="wrap">
		<h1>Dryicepack · Ajustes</h1>
		<?php
		// Resultado del botón "Comprobar ahora" de los festivos de MRW
		if ( 'ok' === $mrw_hecho ) {
			$dias = count( (array) ( ( (array) get_option( 'dip_mrw', array() ) )['dias'] ?? array() ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( 'Festivos de MRW comprobados: ' . $dias . ' días consultados.' ) . '</p></div>';
		} elseif ( 'error' === $mrw_hecho ) {
			$error = (string) ( ( (array) get_option( 'dip_mrw', array() ) )['error'] ?? '' );
			echo '<div class="notice notice-error"><p><strong>No se han podido comprobar los festivos de MRW.</strong> ' . esc_html( $error ) . ' La web sigue usando la lista manual de festivos.</p></div>';
		} elseif ( 'ocupado' === $mrw_hecho ) {
			echo '<div class="notice notice-warning"><p>' . esc_html( 'Ya hay una comprobación de festivos de MRW en marcha. Espera unos minutos y vuelve a cargar esta página.' ) . '</p></div>';
		}
		?>
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
						<p class="description">Recibe los formularios, las solicitudes de factura, las altas de cuenta de empresa y los avisos de la factura simplificada (fallos, rectificativas pendientes y VeriFactu).</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-festivos">Festivos sin salida ni entrega</label></th>
					<td><textarea id="dip-festivos" name="dip_ajustes[festivos]" rows="10" cols="20" class="code"><?php echo esc_textarea( $a['festivos'] ); ?></textarea>
						<p class="description">Una fecha por línea (AAAA-MM-DD). Vienen cargados los de Cataluña de 2026 y 2027. Añade los locales de Mataró de 2027 cuando se publiquen. Esta lista manda siempre; además, la web consulta los festivos de MRW (más abajo).</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-limite">Factura completa obligatoria desde</label></th>
					<td><input id="dip-limite" name="dip_ajustes[limite_factura]" type="number" step="1" min="0" class="small-text" value="<?php echo esc_attr( $a['limite_factura'] ); ?>"> € (IVA incluido)
						<p class="description">Por encima de este importe el checkout pide razón social y NIF/CIF siempre.</p></td>
				</tr>
				<tr>
					<th scope="row">Factura simplificada automática</th>
					<td><label><input name="dip_ajustes[factura_simplificada]" type="checkbox" value="1" <?php checked( $a['factura_simplificada'], 1 ); ?>> Emitir la factura simplificada y enviarla por email desde info@dryicepack.es</label>
						<p class="description">Pedidos de particulares de hasta 400 € (IVA incluido), sin la casilla de empresa, sin NIF ni razón social y sin cuenta de empresa. Con tarjeta sale al pagar; con efectivo al recoger, al marcar el pedido como completado. Sustituye al email «¿Necesitas factura?» y lleva el enlace para pedir la factura completa.<br><strong>Se para sola el 1/1/2027:</strong> desde esa fecha toda factura tiene que salir de un sistema VeriFactu (Real Decreto 1007/2023) y este módulo no lo es.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-serie">Serie de la web</label></th>
					<td><input id="dip-serie" name="dip_ajustes[serie_simplificada]" type="text" class="small-text" maxlength="6" pattern="[A-Za-z0-9]{1,6}" value="<?php echo esc_attr( $a['serie_simplificada'] ); ?>"> + año
						<p class="description">Letras y números (hasta 6). Se le añade el año: W → W2026-00001. Tiene que ser distinta de las series de la aplicación de la gestoría. Si la cambias, la serie nueva empieza en el 1: no la cambies a mitad de año sin hablarlo con la gestoría.</p></td>
				</tr>
				<?php if ( function_exists( 'dip_fs_numero_previsto' ) ) : ?>
				<tr>
					<th scope="row"><label for="dip-siguiente">Siguiente factura</label></th>
					<td><input id="dip-siguiente" type="text" class="regular-text code" value="<?php echo esc_attr( dip_fs_numero_previsto() ); ?>" readonly>
						<p class="description">Solo lectura. El número se asigna solo, por orden y sin saltos, cuando sale cada factura: no se puede cambiar a mano para que no haya huecos ni repetidos.</p></td>
				</tr>
				<?php endif; ?>
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
		<?php if ( function_exists( 'dip_fs_libro' ) ) : ?>
		<h2>Libro de facturas simplificadas</h2>
		<p>Las facturas de la serie de la web (<?php echo esc_html( dip_fs_prefijo() ); ?> + año) ya están expedidas: <strong>no las vuelvas a facturar en la aplicación de la gestoría</strong>. Cada mes, descarga el libro y pásaselo a la gestoría para el libro registro de facturas expedidas y la declaración del IVA. Lleva base, cuota y total de cada factura, y avisa de las rectificativas pendientes.</p>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="dip_fs_libro">
			<?php wp_nonce_field( 'dip_fs_libro', '_wpnonce', false ); ?>
			<label for="dip-libro-mes">Mes</label>
			<input id="dip-libro-mes" type="month" name="mes" value="<?php echo esc_attr( dip_fs_ahora()->modify( 'first day of last month' )->format( 'Y-m' ) ); ?>">
			<?php submit_button( 'Descargar CSV', 'secondary', '', false ); ?>
		</form>
		<?php endif; ?>
		<?php if ( function_exists( 'dip_mrw_html_estado' ) ) dip_mrw_html_estado(); ?>
	</div>
	<?php
}
