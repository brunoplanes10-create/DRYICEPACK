<?php
/**
 * Pedido recibido con el pago pendiente de confirmar («En espera»). Misma plantilla que el pedido pagado, con la línea
 * «Estamos confirmando el pago» (inc/avisos.php).
 *
 * @var WC_Order $order
 * @var WC_Email $email
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 */
defined( 'ABSPATH' ) || exit;

dip_avisos_recibido_html( $order, $email ?? null, (bool) ( $sent_to_admin ?? false ), (bool) ( $plain_text ?? false ) );
