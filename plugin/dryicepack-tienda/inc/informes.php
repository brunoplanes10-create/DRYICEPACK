<?php
/**
 * Informes en Escritorio → Dryicepack:
 * - Facturación mensual: pedidos del mes por cliente (para hacer las facturas en la aplicación de la gestoría).
 * - Exportar a Odoo: archivo CSV con el formato de importación de pedidos de venta de Odoo.
 *
 * Doble facturación: los pedidos que ya tienen factura simplificada de la web (serie W…, inc/factura-simplificada.php)
 * siguen en las dos listas, con su número en la columna "Factura web" y marcados "Ya facturado por la web": no se
 * vuelven a facturar y no suman en "Pendiente de facturar". Si después el cliente pidió factura completa por
 * /factura/, vuelve a estar pendiente: la completa se hace en la gestoría e indica que sustituye a la simplificada.
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

/**
 * Factura que ya hizo la web y si falta hacerla en la aplicación de la gestoría.
 * - Sin factura simplificada: pendiente de facturar.
 * - Con factura simplificada (W…): ya facturado por la web.
 * - Con factura simplificada y factura completa pedida después por /factura/: pendiente (la completa la sustituye).
 *
 * @return array{numero: string, pendiente: bool, estado: string}
 */
function dip_factura_web( WC_Order $p ) {
	$numero = trim( (string) $p->get_meta( '_dip_fs_numero' ) );
	if ( '' === $numero ) return array( 'numero' => '', 'pendiente' => true, 'estado' => 'Pendiente de facturar' );
	if ( $p->get_meta( '_dip_factura_datos' ) ) {
		return array( 'numero' => $numero, 'pendiente' => true, 'estado' => 'Pendiente: factura completa pedida, sustituye a la ' . $numero );
	}
	return array( 'numero' => $numero, 'pendiente' => false, 'estado' => 'Ya facturado por la web' );
}

/** Totales separados: lo que falta facturar ('pendiente') y lo que ya facturó la web ('web'). */
function dip_totales_facturacion( array $pedidos ) {
	$vacio = array( 'pedidos' => 0, 'kg' => 0.0, 'base' => 0.0, 'iva' => 0.0, 'total' => 0.0 );
	$t     = array( 'pendiente' => $vacio, 'web' => $vacio );
	foreach ( $pedidos as $p ) {
		$k = dip_factura_web( $p )['pendiente'] ? 'pendiente' : 'web';
		$t[ $k ]['pedidos']++;
		$t[ $k ]['kg']    += (float) $p->get_meta( '_dip_kg' );
		$t[ $k ]['base']  += round( (float) $p->get_total() - (float) $p->get_total_tax(), 2 );
		$t[ $k ]['iva']   += (float) $p->get_total_tax();
		$t[ $k ]['total'] += (float) $p->get_total();
	}
	return $t;
}

function dip_n_pedidos( $n ) {
	return (int) $n . ( 1 === (int) $n ? ' pedido' : ' pedidos' );
}

/** Filas del CSV de facturación mensual: una por pedido y, al final, los dos totales por separado. */
function dip_filas_facturacion( array $pedidos ) {
	$dec   = static fn( $n ) => wc_format_localized_decimal( round( (float) $n, 2 ) );
	$filas = array( array( 'Cliente', 'NIF/CIF', 'Email', 'Pedido', 'Fecha', 'Entrega', 'Kg', 'Formato', 'Base imponible', 'IVA', 'Total', 'Forma de pago', 'Factura mensual', 'Factura web', 'Estado' ) );
	foreach ( $pedidos as $p ) {
		$fw      = dip_factura_web( $p );
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
			$fw['numero'],
			$fw['estado'],
		);
	}
	if ( $pedidos ) {
		$t = dip_totales_facturacion( $pedidos );
		foreach ( array( 'pendiente' => 'Total pendiente de facturar', 'web' => 'Total ya facturado por la web (no se vuelve a facturar)' ) as $k => $titulo ) {
			$x       = $t[ $k ];
			$filas[] = array( $titulo, '', '', dip_n_pedidos( $x['pedidos'] ), '', '', $dec( $x['kg'] ), '', $dec( $x['base'] ), $dec( $x['iva'] ), $dec( $x['total'] ), '', '', '', '' );
		}
	}
	return $filas;
}

/** Filas del CSV para Odoo: cabecera del pedido en la primera línea y una línea por producto, transporte y suplemento. */
function dip_filas_odoo( array $pedidos ) {
	// Nombres técnicos de Odoo: la importación los reconoce en cualquier idioma. "Factura web" no es un campo de Odoo: no se importa.
	$filas = array( array( 'id', 'partner_id', 'client_order_ref', 'date_order', 'commitment_date', 'note', 'Factura web', 'order_line/product_id', 'order_line/name', 'order_line/product_uom_qty', 'order_line/price_unit' ) );
	foreach ( $pedidos as $p ) {
		$fw      = dip_factura_web( $p );
		$primera = true;
		$cab     = array(
			'__import__.woo_' . $p->get_id(),
			dip_nombre_cliente( $p ),
			'WEB-' . $p->get_order_number(),
			$p->get_date_created() ? wp_date( 'Y-m-d H:i:s', $p->get_date_created()->getTimestamp() ) : '',
			(string) $p->get_meta( '_dip_fecha_entrega' ),
			trim( sprintf( 'Web · %s · %s kg · %s · %s', $p->get_payment_method_title(), wc_format_localized_decimal( (float) $p->get_meta( '_dip_kg' ) ), (string) $p->get_meta( '_billing_nif' ), $p->get_customer_note() ), ' ·' )
				. ( $fw['numero'] ? ' · ' . ( $fw['pendiente'] ? $fw['estado'] : 'Ya facturado por la web: ' . $fw['numero'] . ', no facturar otra vez' ) : '' ),
			$fw['numero'],
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
	return $filas;
}

/* ---------- Descargas (antes de pintar nada) ---------- */
add_action( 'admin_init', static function () {
	if ( empty( $_GET['dip_descarga'] ) || ! current_user_can( 'manage_woocommerce' ) ) return; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	check_admin_referer( 'dip_descarga' );
	$tipo = sanitize_key( wp_unslash( $_GET['dip_descarga'] ) );

	if ( 'facturacion' === $tipo ) {
		$mes = dip_mes_elegido();
		dip_enviar_csv( 'facturacion-' . $mes . '.csv', dip_filas_facturacion( dip_pedidos_periodo( $mes . '-01', wp_date( 'Y-m-t', strtotime( $mes . '-01' ) ) ) ) );
	}

	if ( 'odoo' === $tipo ) {
		$desde = sanitize_text_field( wp_unslash( $_GET['desde'] ?? '' ) );
		$hasta = sanitize_text_field( wp_unslash( $_GET['hasta'] ?? '' ) );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $desde ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $hasta ) ) wp_die( 'Fechas no válidas.' );
		dip_enviar_csv( 'odoo-pedidos-' . $desde . '-a-' . $hasta . '.csv', dip_filas_odoo( dip_pedidos_periodo( $desde, $hasta ) ) );
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
	$mes = dip_mes_elegido();
	dip_pintar_facturacion( $mes, dip_pedidos_periodo( $mes . '-01', wp_date( 'Y-m-t', strtotime( $mes . '-01' ) ) ) );
}

/** Pantalla "Facturación mensual" con los pedidos del mes ya cargados. */
function dip_pintar_facturacion( $mes, array $pedidos ) {
	$grupos = array();
	foreach ( $pedidos as $p ) {
		$clave = ( $p->get_customer_id() ? 'u' . $p->get_customer_id() : 'e' . strtolower( $p->get_billing_email() ) );
		$grupos[ $clave ]['nombre']    = dip_nombre_cliente( $p );
		$grupos[ $clave ]['nif']       = (string) $p->get_meta( '_billing_nif' ) ?: ( $grupos[ $clave ]['nif'] ?? '' );
		$grupos[ $clave ]['mensual']   = ( $grupos[ $clave ]['mensual'] ?? false ) || (bool) $p->get_meta( '_dip_factura_mensual' );
		$grupos[ $clave ]['pendiente'] = ( $grupos[ $clave ]['pendiente'] ?? false ) || dip_factura_web( $p )['pendiente'];
		$grupos[ $clave ]['pedidos'][] = $p;
	}
	// Cuentas con factura mensual, después los clientes con algo pendiente y al final los que ya facturó todo la web
	uasort( $grupos, static fn( $a, $b ) => ( $b['mensual'] <=> $a['mensual'] ) ?: ( $b['pendiente'] <=> $a['pendiente'] ) ?: strcasecmp( $a['nombre'], $b['nombre'] ) );
	$descarga = wp_nonce_url( add_query_arg( array( 'page' => 'dryicepack-facturacion', 'mes' => $mes, 'dip_descarga' => 'facturacion' ), admin_url( 'admin.php' ) ), 'dip_descarga' );
	$total    = dip_totales_facturacion( $pedidos );
	$apagado  = 'color:#646970';
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
		<p class="dip-fw-explica"><strong>«Ya facturado por la web»</strong>: la web ya hizo la factura simplificada (el número W… de la columna «Factura web») y se la envió al cliente; no la vuelvas a hacer en la aplicación de la gestoría y no suma en «Pendiente de facturar».</p>
		<?php if ( ! $grupos ) : ?>
			<p>No hay pedidos en este mes.</p>
		<?php else : ?>
			<table class="widefat dip-fw-resumen" style="max-width:640px">
				<tbody>
					<tr><th scope="row"><strong>Pendiente de facturar</strong></th><td><?php echo esc_html( dip_n_pedidos( $total['pendiente']['pedidos'] ) ); ?></td><td><?php echo esc_html( wc_format_localized_decimal( round( $total['pendiente']['kg'], 2 ) ) ); ?> kg</td><td style="text-align:right"><strong><?php echo wp_kses_post( wc_price( $total['pendiente']['total'] ) ); ?></strong></td></tr>
					<tr style="<?php echo esc_attr( $apagado ); ?>"><th scope="row">Ya facturado por la web</th><td><?php echo esc_html( dip_n_pedidos( $total['web']['pedidos'] ) ); ?></td><td><?php echo esc_html( wc_format_localized_decimal( round( $total['web']['kg'], 2 ) ) ); ?> kg</td><td style="text-align:right"><?php echo wp_kses_post( wc_price( $total['web']['total'] ) ); ?></td></tr>
				</tbody>
			</table>
		<?php endif; ?>
		<?php foreach ( $grupos as $g ) : ?>
			<?php $t = dip_totales_facturacion( $g['pedidos'] ); ?>
			<h2 style="margin-top:28px"><?php echo esc_html( $g['nombre'] ); ?> <?php if ( $g['nif'] ) echo '<small>· ' . esc_html( $g['nif'] ) . '</small>'; ?> <?php if ( $g['mensual'] ) echo '<span style="background:#1F3C55;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px">Factura mensual</span>'; ?></h2>
			<div class="dip-fw-tabla" style="overflow-x:auto">
			<table class="widefat striped">
				<thead><tr><th>Pedido</th><th>Fecha</th><th>Entrega</th><th>Kg</th><th>Pago</th><th>Factura web</th><th style="text-align:right">Total</th></tr></thead>
				<tbody>
				<?php foreach ( $g['pedidos'] as $p ) : ?>
					<?php $fw = dip_factura_web( $p ); ?>
					<tr class="<?php echo esc_attr( $fw['pendiente'] ? 'dip-fw-pendiente' : 'dip-fw-web' ); ?>"<?php echo $fw['pendiente'] ? '' : ' style="' . esc_attr( $apagado ) . '"'; ?>>
						<td><a href="<?php echo esc_url( $p->get_edit_order_url() ); ?>">#<?php echo esc_html( $p->get_order_number() ); ?></a></td>
						<td><?php echo esc_html( $p->get_date_created() ? wp_date( 'd/m/Y', $p->get_date_created()->getTimestamp() ) : '' ); ?></td>
						<td><?php echo esc_html( (string) $p->get_meta( '_dip_fecha_entrega' ) ); ?></td>
						<td><?php echo esc_html( wc_format_localized_decimal( (float) $p->get_meta( '_dip_kg' ) ) ); ?></td>
						<td><?php echo esc_html( $p->get_payment_method_title() ); ?></td>
						<td><?php echo $fw['numero'] ? esc_html( $fw['numero'] ) . '<br><small>' . ( $fw['pendiente'] ? '<strong>' . esc_html( $fw['estado'] ) . '</strong>' : esc_html( $fw['estado'] ) ) . '</small>' : '—'; ?></td>
						<td style="text-align:right"><?php echo wp_kses_post( wc_price( $p->get_total() ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr><th colspan="3">Pendiente de facturar</th><th><?php echo esc_html( wc_format_localized_decimal( round( $t['pendiente']['kg'], 2 ) ) ); ?> kg</th><th colspan="2"></th><th style="text-align:right"><?php echo wp_kses_post( wc_price( $t['pendiente']['total'] ) ); ?></th></tr>
					<?php if ( $t['web']['pedidos'] ) : ?>
						<tr style="<?php echo esc_attr( $apagado ); ?>"><th colspan="3">Ya facturado por la web</th><th><?php echo esc_html( wc_format_localized_decimal( round( $t['web']['kg'], 2 ) ) ); ?> kg</th><th colspan="2"></th><th style="text-align:right"><?php echo wp_kses_post( wc_price( $t['web']['total'] ) ); ?></th></tr>
						<tr><th colspan="3">Total del mes</th><th><?php echo esc_html( wc_format_localized_decimal( round( $t['pendiente']['kg'] + $t['web']['kg'], 2 ) ) ); ?> kg</th><th colspan="2"></th><th style="text-align:right"><?php echo wp_kses_post( wc_price( $t['pendiente']['total'] + $t['web']['total'] ) ); ?></th></tr>
					<?php endif; ?>
				</tfoot>
			</table>
			</div>
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
			<li>La columna <strong>«Factura web»</strong> lleva el número de la factura simplificada que ya hizo la web (W…) y no se importa: déjala sin emparejar. Esos pedidos ya están facturados (también lo dice la nota del pedido): no hagas otra factura desde Odoo.</li>
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
