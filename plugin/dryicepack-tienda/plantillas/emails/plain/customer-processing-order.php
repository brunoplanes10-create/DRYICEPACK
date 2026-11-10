<?php
/**
 * Pedido recibido en texto plano (si en WooCommerce → Ajustes → Emails se elige «Texto plano» o «Multiparte»).
 * Mismo contenido que la versión HTML (inc/avisos.php).
 *
 * @var WC_Order $order
 * @var WC_Email $email
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 */
defined( 'ABSPATH' ) || exit;

echo dip_avisos_recibido_texto( $order, $email ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- texto plano: WooCommerce le quita las etiquetas
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin ?? false, true, $email ?? null ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
