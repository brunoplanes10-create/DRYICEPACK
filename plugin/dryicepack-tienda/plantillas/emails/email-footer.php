<?php
/**
 * Pie común de todos los emails (DryIcePack): empresa, teléfono, WhatsApp, email, horario y enlaces a seguridad y a
 * condiciones de venta. Sustituye al de WooCommerce (inc/emails.php); el «Texto del pie» de WooCommerce → Ajustes →
 * Emails no se usa.
 */
defined( 'ABSPATH' ) || exit;

echo dip_email_pie(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
