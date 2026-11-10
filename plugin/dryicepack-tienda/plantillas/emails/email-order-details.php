<?php
/**
 * Tabla del pedido en los emails. En el de pedido recibido (customer_processing_order y customer_on_hold_order) es la
 * nuestra: líneas con nombre claro («Hielo seco en pellets de 3 mm · Pack de 10 kg»), importes con IVA, total y forma
 * de pago. En el resto de emails (a la tienda, reembolsos, notas…) se usa la plantilla original de WooCommerce.
 * Los ganchos de antes y después de la tabla se mantienen (instrucciones de pago, plugins).
 *
 * @var WC_Order      $order
 * @var bool          $sent_to_admin
 * @var bool          $plain_text
 * @var WC_Email|null $email
 */
defined( 'ABSPATH' ) || exit;

$email = $email ?? null;

if ( function_exists( 'dip_avisos_tabla_propia' ) && dip_avisos_tabla_propia( $order, (bool) $sent_to_admin, (bool) $plain_text, $email ) ) {
	do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	echo dip_avisos_tabla_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
	do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	return;
}

include dip_email_plantilla_original( 'emails/email-order-details.php' );
