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
		'resenas_email'     => $produccion ? 1 : 0, // inc/resenas.php: email «¿Qué tal llegó tu hielo seco?» el día después de la entrega
		'limite_factura'    => 400,
		'factura_simplificada' => 1,   // inc/factura-simplificada.php
		'serie_simplificada'   => 'W', // + año: W2026-00001
		'recogida_sabados_sin_suplemento' => '2026-10-31', // sábados de recogida sin los 10 € (Halloween 2026); el envío en sábado lo paga siempre
		'pack_pequeno'         => 'auto', // inc/pack-pequeno.php: 3 kg mientras quede, después 5 kg (03/10/2026)
		// inc/avisos.php: avisos al cliente sobre su pedido (09/11/2026), de serie activados
		'aviso_camino'         => 1,
		'aviso_hoy'            => 1,
		'aviso_recogida'       => 1,
		'aviso_incidencia'     => 1,
		// inc/mrw-sagec.php e inc/urgente.php (11/11/2026): de lunes a viernes las cajas se llevan a MRW, cuya última
		// furgoneta sale a las 18:30; para llegar, hasta las 17:30. El urgente cuesta lo mismo que el sábado.
		'mrw_hora_limite'      => '17:30',
		'mrw_hora_camino'      => '18:30',
		'urgente_importe'      => 9.70,
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
	$ga4     = strtoupper( trim( (string) ( $entrada['ga4'] ?? '' ) ) );
	return array(
		'ga4'            => preg_match( '/^G-[A-Z0-9]{4,}$/', $ga4 ) ? $ga4 : '',
		'email_avisos'   => sanitize_email( $entrada['email_avisos'] ?? '' ) ?: 'info@dryicepack.es',
		'festivos'       => dip_sanear_fechas( (string) ( $entrada['festivos'] ?? '' ) ),
		'recordatorios'  => empty( $entrada['recordatorios'] ) ? 0 : 1,
		'resenas_url'    => esc_url_raw( trim( (string) ( $entrada['resenas_url'] ?? '' ) ) ),
		'resenas_email'  => empty( $entrada['resenas_email'] ) ? 0 : 1,
		'limite_factura' => max( 0, (float) str_replace( ',', '.', (string) ( $entrada['limite_factura'] ?? 400 ) ) ),
		'factura_simplificada' => empty( $entrada['factura_simplificada'] ) ? 0 : 1,
		'serie_simplificada'   => substr( (string) preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) ( $entrada['serie_simplificada'] ?? '' ) ) ), 0, 6 ) ?: 'W',
		'recogida_sabados_sin_suplemento' => dip_sanear_fechas( (string) ( $entrada['recogida_sabados_sin_suplemento'] ?? '' ), true ),
		'pack_pequeno'         => in_array( (string) ( $entrada['pack_pequeno'] ?? '' ), array( 'auto', '3', '5' ), true ) ? (string) $entrada['pack_pequeno'] : 'auto',
		'aviso_camino'         => empty( $entrada['aviso_camino'] ) ? 0 : 1,
		'aviso_hoy'            => empty( $entrada['aviso_hoy'] ) ? 0 : 1,
		'aviso_recogida'       => empty( $entrada['aviso_recogida'] ) ? 0 : 1,
		'aviso_incidencia'     => empty( $entrada['aviso_incidencia'] ) ? 0 : 1,
		'mrw_hora_limite'      => dip_sanear_hora( $entrada['mrw_hora_limite'] ?? '', '17:30' ),
		'mrw_hora_camino'      => dip_sanear_hora( $entrada['mrw_hora_camino'] ?? '', '18:30' ),
		'urgente_importe'      => round( max( 0, (float) str_replace( ',', '.', (string) ( $entrada['urgente_importe'] ?? 9.70 ) ) ), 2 ),
	);
}

/** 'HH:MM' válida o la de serie. */
function dip_sanear_hora( $hora, $defecto ) {
	$hora = trim( (string) $hora );
	if ( preg_match( '/^(\d{1,2}):(\d{2})$/', $hora, $m ) && (int) $m[1] < 24 && (int) $m[2] < 60 ) return sprintf( '%02d:%02d', $m[1], $m[2] );
	return $defecto;
}

/** Fechas AAAA-MM-DD válidas, sin repetir y ordenadas, una por línea. $solo_sabados: descarta los demás días. */
function dip_sanear_fechas( $texto, $solo_sabados = false ) {
	$fechas = array();
	foreach ( preg_split( '/[\s,;]+/', (string) $texto ) as $f ) {
		$f = trim( $f );
		$d = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f ) ? DateTimeImmutable::createFromFormat( '!Y-m-d', $f ) : false;
		if ( ! $d || $d->format( 'Y-m-d' ) !== $f ) continue;
		if ( $solo_sabados && 6 !== (int) $d->format( 'N' ) ) continue;
		$fechas[] = $f;
	}
	$fechas = array_values( array_unique( $fechas ) );
	sort( $fechas );
	return implode( "\n", $fechas );
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
						<p class="description">ID de medición (empieza por G-). Se carga solo cuando el visitante acepta las cookies de análisis.</p>
						<p class="description">La etiqueta de Google Analytics de Site Kit está bloqueada por este plugin (se cargaba sin consentimiento). Para seguir viendo los datos en Site Kit, pon aquí el mismo ID de medición que usa Site Kit (G-…)<?php echo function_exists( 'dip_sitekit_id_medicion' ) && '' !== dip_sitekit_id_medicion() ? esc_html( ': ' . dip_sitekit_id_medicion() ) : ''; ?>.</p></td>
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
					<th scope="row">Precios de recogida</th>
					<td><p><strong><?php echo esc_html( function_exists( 'dip_texto_precios_recogida_admin' ) ? dip_texto_precios_recogida_admin() : '—' ); ?></strong></p>
						<p class="description">Se calculan solos: precio de la web × IVA, redondeado a la baja a múltiplos de <?php echo (int) DIP_RECOGIDA_REDONDEO; ?> €, IVA incluido. Son los mismos en la web y en la nave, en efectivo y con el importe exacto. Si cambias el precio de un pack en WooCommerce, cambia también el de recogida.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-sabados-libres">Sábados de recogida sin suplemento</label></th>
					<td><textarea id="dip-sabados-libres" name="dip_ajustes[recogida_sabados_sin_suplemento]" rows="3" cols="20" class="code"><?php echo esc_textarea( $a['recogida_sabados_sin_suplemento'] ); ?></textarea>
						<p class="description">Una fecha por línea (AAAA-MM-DD), solo sábados. Esos días la recogida no lleva los <?php echo esc_html( dip_euros( DIP_RECOGIDA_SABADO_CON_IVA ) ); ?> (IVA incluido). El envío en sábado sigue con su suplemento. De serie: el 31/10/2026 (Halloween).</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-pack-pequeno">Pack pequeño</label></th>
					<td><select id="dip-pack-pequeno" name="dip_ajustes[pack_pequeno]">
							<option value="auto" <?php selected( $a['pack_pequeno'], 'auto' ); ?>>Automático: 3 kg mientras haya stock, después 5 kg</option>
							<option value="3" <?php selected( $a['pack_pequeno'], '3' ); ?>>Siempre 3 kg</option>
							<option value="5" <?php selected( $a['pack_pequeno'], '5' ); ?>>Siempre 5 kg</option>
						</select>
						<p class="description"><?php echo esc_html( function_exists( 'dip_pack_pequeno_estado' ) ? dip_pack_pequeno_estado() : '' ); ?></p>
						<p class="description">Las cajas de 3 kg se acaban: pon el stock en las dos variaciones de 3 kg. Mientras quede alguna, la web no vende el de 5 kg. Cuando se acaban las dos, entra solo el de 5 kg y los textos que recomiendan 3 kg pasan a 5 kg. Te llega un email al correo de avisos. Para forzarlo a mano, elige 3 o 5 kg aquí.</p></td>
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
						<p class="description">Ficha de Google → Pedir reseñas → Copiar enlace. Lo usa el email del día después de la entrega (abajo). Si lo dejas vacío, no se pide reseña.</p></td>
				</tr>
				<tr>
					<th scope="row">Reseña por email</th>
					<td><label><input name="dip_ajustes[resenas_email]" type="checkbox" value="1" <?php checked( $a['resenas_email'], 1 ); ?>> Pedir reseña por email el día después de la entrega</label>
						<p class="description">Email «¿Qué tal llegó tu hielo seco?» a las 9:00 del día siguiente a la entrega o a la recogida (pedidos Procesando o Completados), en el idioma del pedido y de usted a empresas. Se pide a todos los clientes, sin filtrar y sin nada a cambio, como exige Google. Como mucho uno por email cada 6 meses, con enlace de baja. Si no sale, llega un aviso al correo de avisos. A mano: en el pedido, Acciones del pedido → Pedir reseña por email.</p>
						<?php if ( function_exists( 'dip_resenas_resumen_admin' ) ) : ?><p class="description"><strong><?php echo esc_html( dip_resenas_resumen_admin() ); ?></strong></p><?php endif; ?></td>
				</tr>
				<tr id="dip-avisos">
					<th scope="row">Avisos al cliente</th>
					<td><fieldset>
						<label><input name="dip_ajustes[aviso_camino]" type="checkbox" value="1" <?php checked( $a['aviso_camino'], 1 ); ?>> «Va de camino», con el número de MRW, al guardarlo en «Envíos de hoy»</label><br>
						<label><input name="dip_ajustes[aviso_hoy]" type="checkbox" value="1" <?php checked( $a['aviso_hoy'], 1 ); ?>> «Hoy llega» a las 8:00 del día de entrega (solo pedidos ya enviados)</label><br>
						<label><input name="dip_ajustes[aviso_recogida]" type="checkbox" value="1" <?php checked( $a['aviso_recogida'], 1 ); ?>> Recordatorio de recogida a las 8:00 del día de recogida (dirección, hora e importe en efectivo)</label><br>
						<label><input name="dip_ajustes[aviso_incidencia]" type="checkbox" value="1" <?php checked( $a['aviso_incidencia'], 1 ); ?>> «Avisar de una incidencia en la entrega» en Acciones del pedido (reembolso completo o recoger en Mataró)</label>
						</fieldset>
						<p class="description">En el idioma del pedido y de usted a empresas. Cada aviso sale una vez por pedido y queda anotado. Si uno no sale, llega un aviso al correo de avisos; si la pasada de las 8:00 deja de correr, también. A mano, siempre: en el pedido, Acciones del pedido → «Enviar aviso: va de camino / hoy llega / recordatorio de recogida».</p>
						<p class="description"><strong>El email «Pedido completado» de WooCommerce no se envía</strong> (aunque en WooCommerce → Ajustes → Emails salga activado): el cliente ya sabe cómo va su pedido con estos avisos. La factura simplificada y el email «¿Necesitas factura?» siguen saliendo al completar el pedido, porque no dependen de él.</p>
						<?php if ( function_exists( 'dip_avisos_resumen_admin' ) ) : ?><p class="description"><strong><?php echo esc_html( dip_avisos_resumen_admin() ); ?></strong></p><?php endif; ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-mrw-limite">Hora límite para MRW</label></th>
					<td><input id="dip-mrw-limite" name="dip_ajustes[mrw_hora_limite]" type="time" style="width:8em" value="<?php echo esc_attr( $a['mrw_hora_limite'] ); ?>">
						<p class="description">Hasta esta hora la web crea al momento los envíos que salen hoy y sirven los enlaces de pedido urgente. Después ya no se llega a la última furgoneta de MRW. De serie, 17:30.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-mrw-camino">Hora de entrega a MRW</label></th>
					<td><input id="dip-mrw-camino" name="dip_ajustes[mrw_hora_camino]" type="time" style="width:8em" value="<?php echo esc_attr( $a['mrw_hora_camino'] ); ?>">
						<p class="description">A esta hora MRW ya tiene las cajas: los clientes de los envíos creados por la web reciben el aviso «va de camino». Si las llevas antes, en «Envíos de hoy» está «Ya están en MRW: avisar». De serie, 18:30.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="dip-urgente">Suplemento de pedido urgente</label></th>
					<td><input id="dip-urgente" name="dip_ajustes[urgente_importe]" type="number" min="0" step="0.01" class="small-text" value="<?php echo esc_attr( number_format( (float) $a['urgente_importe'], 2, '.', '' ) ); ?>"> € sin IVA
						<p class="description">Lo paga quien compra para mañana después de las 12:00 con el enlace urgente que le mandas desde «Envíos de hoy». De serie, como el sábado: 9,70 € + IVA (<?php echo esc_html( dip_euros( round( 9.70 * dip_factor_iva(), 2 ) ) ); ?>). En cada enlace lo puedes cambiar o poner a 0.</p></td>
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
		<?php if ( function_exists( 'dip_sagec_html_ajustes' ) ) dip_sagec_html_ajustes(); ?>
	</div>
	<?php
}
