<?php
/**
 * Resumen del pedido en el checkout (sustituye a checkout/review-order.php de WooCommerce).
 * Igual que el original salvo el envío: aquí solo se muestra lo elegido; los botones están en el paso "Entrega".
 */
defined( 'ABSPATH' ) || exit;

$dip_t      = dip_textos_tienda();
$dip_idioma = dip_idioma();
?>
<table class="shop_table woocommerce-checkout-review-order-table dip-resumen">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;
			?>
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<td class="product-name">
					<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
					<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
				<td class="product-total">
					<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
			<?php
		}
		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
			do_action( 'woocommerce_review_order_before_shipping' );
			$dip_paquetes = WC()->shipping()->get_packages();
			$dip_rates    = $dip_paquetes[0]['rates'] ?? array();
			$dip_elegidos = (array) WC()->session->get( 'chosen_shipping_methods' );
			$dip_rate     = $dip_rates[ $dip_elegidos[0] ?? '' ] ?? ( $dip_rates ? reset( $dip_rates ) : null );
			if ( $dip_rate ) :
				$dip_coste = (float) $dip_rate->get_cost() + ( WC()->cart->display_prices_including_tax() ? array_sum( (array) $dip_rate->get_taxes() ) : 0 );
				$dip_metodo = dip_es_recogida( $dip_rate->get_method_id() ) ? 'recogida' : 'envio';
				$dip_fecha  = function_exists( 'dip_fecha_elegida' ) ? dip_fecha_elegida( $dip_metodo ) : '';
				?>
				<tr class="woocommerce-shipping-totals shipping dip-resumen__envio">
					<th><?php echo esc_html( 'recogida' === $dip_metodo ? $dip_t['fecha_recogida'] : $dip_t['fecha_entrega'] ); ?>
						<?php if ( $dip_fecha ) : ?><small><?php echo esc_html( dip_fecha_larga( $dip_fecha, $dip_idioma ) ); ?></small><?php endif; ?></th>
					<td><?php echo $dip_coste > 0 ? wp_kses_post( wc_price( $dip_coste ) ) : esc_html( 'ca' === $dip_idioma ? 'Gratuït' : ( 'en' === $dip_idioma ? 'Free' : 'Gratis' ) ); ?></td>
				</tr>
			<?php endif; ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</tfoot>
</table>
