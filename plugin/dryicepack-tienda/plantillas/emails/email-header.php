<?php
/**
 * Cabecera común de todos los emails (DryIcePack). Sustituye a la de WooCommerce (inc/emails.php).
 * El idioma, el texto previo y el antetítulo salen del contexto del email (dip_email_contexto()).
 *
 * @var string $email_heading
 */
defined( 'ABSPATH' ) || exit;

echo dip_email_cabecera( isset( $email_heading ) ? (string) $email_heading : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado dentro
