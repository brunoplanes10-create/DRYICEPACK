<?php
/**
 * Pedido recibido (pagado o en efectivo al recoger). Plantilla propia (inc/emails.php y inc/avisos.php): arriba lo que
 * importa (día y hora de entrega, o día, dirección y efectivo de la recogida), «Qué pasa ahora», «Ver mi pedido», la
 * tabla del pedido y el bloque legal. Lleva adjuntas las condiciones de venta en PDF (inc/condiciones-pdf.php).
 *
 * @var WC_Order $order
 * @var WC_Email $email
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 */
defined( 'ABSPATH' ) || exit;

dip_avisos_recibido_html( $order, $email ?? null, (bool) ( $sent_to_admin ?? false ), (bool) ( $plain_text ?? false ) );
