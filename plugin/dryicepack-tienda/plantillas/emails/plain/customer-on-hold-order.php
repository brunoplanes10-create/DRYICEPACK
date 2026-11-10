<?php
/**
 * Pedido recibido con el pago pendiente, en texto plano. Mismo contenido que la versión HTML (inc/avisos.php).
 *
 * @var WC_Order $order
 * @var WC_Email $email
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 */
defined( 'ABSPATH' ) || exit;

echo dip_avisos_recibido_texto( $order, $email ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- texto plano: WooCommerce le quita las etiquetas
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin ?? false, true, $email ?? null ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
