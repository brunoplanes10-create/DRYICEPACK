<?php
/**
 * Informes en Escritorio → Dryicepack:
 * - Facturación mensual: pedidos del mes por cliente (para hacer las facturas en la aplicación de la gestoría).
 * - Exportar a Odoo: archivo CSV con el formato de importación de pedidos de venta de Odoo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', static function () {
	add_submenu_page( 'dryicepack', 'Facturación mensual', 'Facturación mensual', 'manage_woocommerce', 'dryicepack-facturacion', 'dip_pantalla_facturacion' );
	add_submenu_page( 'dryicepack', 'Exportar a Odoo', 'Exportar a Odoo', 'manage_woocommerce', 'dryicepack-odoo', 'dip_pantalla_odoo' );
} );

/** Pedidos pagados o por facturar entre dos fechas (Y-m-d, incluidas). */
function dip_pedidos_periodo( $desde, $hasta ) {
	return wc_get_orders( array(
		'limit'        => -1,
		'status'       => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
		'date_created' => $desde . '...' . $hasta,
		'orderby'      => 'date',
		'order'        => 'ASC',
	) );
}

function dip_nombre_cliente( WC_Order $p ) {
	$empresa = trim( $p->get_billing_company() );
	return $empresa ?: trim( $p->get_billing_first_name() . ' ' . $p->get_billing_last_name() );
}

function dip_mes_elegido() {
	$mes = isset( $_GET['mes'] ) ? sanitize_text_field( wp_unslash( $_GET['mes'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! preg_match( '/^\d{4}-\d{2}$/', $mes ) ) $mes = wp_date( 'Y-m', strtotime( 'first day of last month' ) );
	return $mes;
}

/* ---------- Descargas (antes de pintar nada) ---------- */
add_action( 'admin_init', static function () {
	if ( empty( $_GET['dip_descarga'] ) || ! current_user_can( 'manage_woocommerce' ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	check_admin_referer( 'dip_descarga' );
	$tipo = sanitize_key( wp_unslash( $_GET['dip_descarga'] ) );

	if ( 'facturacion' === $tipo ) {
		$mes     = dip_mes_elegido();
		$pedidos = dip_pedidos_periodo( $mes . '-01', wp_date( 'Y-m-t', strtotime( $mes . '-01' ) ) );
		$filas   = array( array( 'Cliente', 'NIF/CIF', 'Email', 'Pedido', 'Fecha', 'Entrega', 'Kg', 'Formato', 'Base imponible', 'IVA', 'Total', 'Forma de pago', 'Factura mensual' ) );
		foreach ( $pedidos as $p ) {
			$filas[] = array(
				dip_nombre_cliente( $p ),
				(string) $p->get_meta( '_billing_nif' ),
				$p->get_billing_email(),
				$p->get_order_number(),
				$p->get_date_created() ? wp_date( 'd/m/Y', $p->get_date_created()->getTimestamp() ) : '',
				(string) $p->get_meta( '_dip_fecha_entrega' ),
				wc_format_localized_decimal( (float) $p->get_meta( '_dip_kg' ) ),
				(string) $p->get_meta( '_dip_formatos' ),
				wc_format_localized_decimal( round( (float) $p->get_total() - (float) $p->get_total_tax(), 2 ) ),
				wc_format_localized_decimal( (float) $p->get_total_tax() ),
				wc_format_localized_decimal( (float) $p->get_total() ),
				$p->get_payment_method_title(),
				$p->get_meta( '_dip_factura_mensual' ) ? 'Sí' : 'No',
			);
		}
		dip_enviar_csv( 'facturacion-' . $mes . '.csv', $filas );
	}

	if ( 'odoo' === $tipo ) {
		$desde = sanitize_text_field( wp_unslash( $_GET['desde'] ?? '' ) );
		$hasta = sanitize_text_field( wp_unslash( $_GET['hasta'] ?? '' ) );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $desde ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $hasta ) ) wp_die( 'Fechas no válidas.' );
		// Nombres técnicos de Odoo: la importación los reconoce en cualquier idioma.
		$filas = array( array( 'id', 'partner_id', 'client_order_ref', 'date_order', 'commitment_date', 'note', 'order_line/product_id', 'order_line/name', 'order_line/product_uom_qty', 'order_line/price_unit' ) );
		foreach ( dip_pedidos_periodo( $desde, $hasta ) as $p ) {
			$primera = true;
			$cab     = array(
				'__import__.woo_' . $p->get_id(),
				dip_nombre_cliente( $p ),
				'WEB-' . $p->get_order_number(),
				$p->get_date_created() ? wp_date( 'Y-m-d H:i:s', $p->get_date_created()->getTimestamp() ) : '',
				(string) $p->get_meta( '_dip_fecha_entrega' ),
				trim( sprintf( 'Web · %s · %s kg · %s · %s', $p->get_payment_method_title(), wc_format_localized_decimal( (float) $p->get_meta( '_dip_kg' ) ), (string) $p->get_meta( '_billing_nif' ), $p->get_customer_note() ), ' ·' ),
			);
			$lineas = array();
			foreach ( $p->get_items() as $l ) {
				$lineas[] = array( $l->get_name(), $l->get_name(), $l->get_quantity(), round( (float) $l->get_subtotal() / max( 1, $l->get_quantity() ), 4 ) );
			}
			foreach ( $p->get_items( 'shipping' ) as $l ) {
				if ( (float) $l->get_total() > 0 ) $lineas[] = array( 'Transporte', $l->get_name(), 1, round( (float) $l->get_total(), 4 ) );
			}
			foreach ( $p->get_items( 'fee' ) as $l ) {
				$lineas[] = array( $l->get_name(), $l->get_name(), 1, round( (float) $l->get_total(), 4 ) );
			}
			foreach ( $lineas as $linea ) {
				$filas[] = array_merge( $primera ? $cab : array_fill( 0, count( $cab ), '' ), $linea );
				$primera = false;
			}
		}
		dip_enviar_csv( 'odoo-pedidos-' . $desde . '-a-' . $hasta . '.csv', $filas );
	}
} );

function dip_enviar_csv( $nombre, array $filas ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $nombre ) . '"' );
	$salida = fopen( 'php://output', 'w' );
	fwrite( $salida, "\xEF\xBB\xBF" ); // BOM: Excel abre bien los acentos
	foreach ( $filas as $fila ) fputcsv( $salida, $fila, ';' );
	fclose( $salida );
	exit;
}

/* ---------- Pantallas ---------- */
function dip_pantalla_facturacion() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$mes     = dip_mes_elegido();
	$pedidos = dip_pedidos_periodo( $mes . '-01', wp_date( 'Y-m-t', strtotime( $mes . '-01' ) ) );
	$grupos  = array();
	foreach ( $pedidos as $p ) {
		$clave = ( $p->get_customer_id() ? 'u' . $p->get_customer_id() : 'e' . strtolower( $p->get_billing_email() ) );
		$grupos[ $clave ]['nombre']    = dip_nombre_cliente( $p );
		$grupos[ $clave ]['nif']       = (string) $p->get_meta( '_billing_nif' ) ?: ( $grupos[ $clave ]['nif'] ?? '' );
		$grupos[ $clave ]['mensual']   = ( $grupos[ $clave ]['mensual'] ?? false ) || (bool) $p->get_meta( '_dip_factura_mensual' );
		$grupos[ $clave ]['pedidos'][] = $p;
	}
	uasort( $grupos, static fn( $a, $b ) => ( $b['mensual'] <=> $a['mensual'] ) ?: strcasecmp( $a['nombre'], $b['nombre'] ) );
	$descarga = wp_nonce_url( add_query_arg( array( 'page' => 'dryicepack-facturacion', 'mes' => $mes, 'dip_descarga' => 'facturacion' ), admin_url( 'admin.php' ) ), 'dip_descarga' );
	?>
	<div class="wrap">
		<h1>Facturación mensual</h1>
		<form method="get" style="margin:12px 0">
			<input type="hidden" name="page" value="dryicepack-facturacion">
			<label>Mes <input type="month" name="mes" value="<?php echo esc_attr( $mes ); ?>"></label>
			<?php submit_button( 'Ver', 'secondary', '', false ); ?>
			<a class="button button-primary" href="<?php echo esc_url( $descarga ); ?>">Descargar CSV</a>
		</form>
		<p>Primero las cuentas de empresa con factura mensual. Cada pedido indica si ya pagó (tarjeta, efectivo) o va a la factura del mes.</p>
		<?php if ( ! $grupos ) : ?>
			<p>No hay pedidos en este mes.</p>
		<?php endif; ?>
		<?php foreach ( $grupos as $g ) : ?>
			<?php
			$total = array_sum( array_map( static fn( $p ) => (float) $p->get_total(), $g['pedidos'] ) );
			$kg    = array_sum( array_map( static fn( $p ) => (float) $p->get_meta( '_dip_kg' ), $g['pedidos'] ) );
			?>
			<h2 style="margin-top:28px"><?php echo esc_html( $g['nombre'] ); ?> <?php if ( $g['nif'] ) echo '<small>· ' . esc_html( $g['nif'] ) . '</small>'; ?> <?php if ( $g['mensual'] ) echo '<span style="background:#1F3C55;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px">Factura mensual</span>'; ?></h2>
			<table class="widefat striped">
				<thead><tr><th>Pedido</th><th>Fecha</th><th>Entrega</th><th>Kg</th><th>Pago</th><th style="text-align:right">Total</th></tr></thead>
				<tbody>
				<?php foreach ( $g['pedidos'] as $p ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( $p->get_edit_order_url() ); ?>">#<?php echo esc_html( $p->get_order_number() ); ?></a></td>
						<td><?php echo esc_html( $p->get_date_created() ? wp_date( 'd/m/Y', $p->get_date_created()->getTimestamp() ) : '' ); ?></td>
						<td><?php echo esc_html( (string) $p->get_meta( '_dip_fecha_entrega' ) ); ?></td>
						<td><?php echo esc_html( wc_format_localized_decimal( (float) $p->get_meta( '_dip_kg' ) ) ); ?></td>
						<td><?php echo esc_html( $p->get_payment_method_title() ); ?></td>
						<td style="text-align:right"><?php echo wp_kses_post( wc_price( $p->get_total() ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot><tr><th colspan="3">Total del mes</th><th><?php echo esc_html( wc_format_localized_decimal( $kg ) ); ?> kg</th><th></th><th style="text-align:right"><?php echo wp_kses_post( wc_price( $total ) ); ?></th></tr></tfoot>
			</table>
		<?php endforeach; ?>
	</div>
	<?php
}

function dip_pantalla_odoo() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) return;
	$desde = wp_date( 'Y-m-d', strtotime( '-7 days' ) );
	$hasta = wp_date( 'Y-m-d' );
	?>
	<div class="wrap">
		<h1>Exportar pedidos a Odoo</h1>
		<p>Genera un archivo con los pedidos de la web en el formato de importación de <strong>Ventas → Pedidos</strong> de Odoo.</p>
		<ol>
			<li>Elige las fechas y descarga el archivo.</li>
			<li>En Odoo: Ventas → Pedidos → icono de engranaje → <strong>Importar registros</strong> → sube el archivo.</li>
			<li>La primera vez, empareja cada producto con el tuyo de Odoo (hielo seco por formato, «Transporte» y «Entrega en sábado»). Odoo recuerda el emparejamiento.</li>
			<li>Cada pedido lleva un identificador propio (<code>woo_</code> + número): si importas dos veces el mismo, Odoo lo actualiza en lugar de duplicarlo.</li>
		</ol>
		<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<input type="hidden" name="page" value="dryicepack-odoo">
			<input type="hidden" name="dip_descarga" value="odoo">
			<?php wp_nonce_field( 'dip_descarga', '_wpnonce', false ); ?>
			<label>Desde <input type="date" name="desde" value="<?php echo esc_attr( $desde ); ?>"></label>
			<label style="margin-left:12px">Hasta <input type="date" name="hasta" value="<?php echo esc_attr( $hasta ); ?>"></label>
			<?php submit_button( 'Descargar archivo para Odoo', 'primary', '', false, array( 'style' => 'margin-left:12px' ) ); ?>
		</form>
	</div>
	<?php
}
