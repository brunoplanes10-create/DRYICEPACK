<?php
/**
 * Estilos de los emails (DryIcePack). WooCommerce los pasa a línea. Sustituyen a los suyos (inc/emails.php).
 * Nuestras piezas ya llevan sus estilos en línea; esto es para lo que pinta WooCommerce (u otro plugin) dentro del
 * cuerpo: tabla del pedido de los emails que no son nuestros, direcciones, notas, reembolsos, emails a la tienda.
 * Los colores de las piezas en modo oscuro y móvil van en el <style> de la cabecera (dip_email_css()).
 */
defined( 'ABSPATH' ) || exit;

$sans  = dip_email_fuente( 'sans' );
$mono  = dip_email_fuente( 'mono' );
$tinta = dip_email_color( 'tinta' );
$suave = dip_email_color( 'suave' );
$linea = dip_email_color( 'linea' );
$mar   = dip_email_color( 'marino' );
?>
body {
	margin: 0;
	padding: 0;
	background-color: <?php echo esc_attr( dip_email_color( 'fondo' ) ); ?>;
}

#body_content_inner {
	color: <?php echo esc_attr( $tinta ); ?>;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fuentes fijas ?>;
	font-size: 16px;
	line-height: 25px;
	text-align: left;
}

#body_content_inner p {
	margin: 0 0 16px;
}

#body_content_inner h2 {
	color: <?php echo esc_attr( $tinta ); ?>;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 18px;
	line-height: 24px;
	font-weight: 700;
	margin: 28px 0 12px;
	text-align: left;
}

#body_content_inner h3 {
	color: <?php echo esc_attr( $tinta ); ?>;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 16px;
	line-height: 22px;
	font-weight: 700;
	margin: 20px 0 8px;
}

#body_content_inner h2.email-order-detail-heading span,
#body_content_inner h2 span {
	display: block;
	color: <?php echo esc_attr( $suave ); ?>;
	font-family: <?php echo $mono; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 12px;
	font-weight: 500;
	letter-spacing: 0.06em;
}

#body_content_inner a,
.link {
	color: <?php echo esc_attr( $mar ); ?>;
	text-decoration: underline;
}

#body_content_inner ul,
#body_content_inner ol {
	margin: 0 0 16px;
	padding: 0 0 0 20px;
}

/* Tabla del pedido de WooCommerce (emails que no son el de pedido recibido) */
.td {
	color: <?php echo esc_attr( $tinta ); ?>;
	border: 0;
	border-bottom: 1px solid <?php echo esc_attr( $linea ); ?>;
	vertical-align: top;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 15px;
	line-height: 21px;
}

#body_content_inner table.td {
	border: 0;
	border-collapse: collapse;
}

#body_content_inner table.td th,
#body_content_inner table.td td {
	padding: 10px 12px 10px 0;
}

#body_content_inner table.td thead th {
	color: <?php echo esc_attr( $suave ); ?>;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
	font-size: 13px;
	font-weight: 500;
	border-bottom: 2px solid <?php echo esc_attr( $mar ); ?>;
}

#body_content_inner .order-totals th,
#body_content_inner .order-totals td {
	font-weight: 400;
	border-top: 0;
}

#body_content_inner .order-totals-total th,
#body_content_inner .order-totals-total td {
	font-weight: 700;
	font-size: 17px;
	border-top: 2px solid <?php echo esc_attr( $mar ); ?> !important;
}

#body_content_inner .order-totals td,
#body_content_inner .order_item td:last-child {
	font-family: <?php echo $mono; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

#body_content_inner .includes_tax,
#body_content_inner small {
	color: <?php echo esc_attr( $suave ); ?>;
	font-size: 13px;
}

#body_content_inner .wc-item-meta,
#body_content_inner .email-order-item-meta {
	color: <?php echo esc_attr( $suave ); ?>;
	font-size: 13px;
	line-height: 19px;
	list-style: none;
	margin: 4px 0 0;
	padding: 0;
}

#body_content_inner .wc-item-meta li {
	margin: 0;
	padding: 0;
}

#body_content_inner .wc-item-meta li p {
	margin: 0;
}

#body_content_inner .wc-item-meta-label {
	font-weight: 500;
	margin-right: 4px;
}

#body_content_inner .order-item-data td {
	border: 0 !important;
	padding: 0 !important;
}

.text-align-left {
	text-align: left;
}

.text-align-right {
	text-align: right;
}

.font-family {
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

/* Direcciones */
.address {
	color: <?php echo esc_attr( $tinta ); ?>;
	font-style: normal;
	line-height: 22px;
	padding: 12px 0;
	border: 0;
	word-break: break-word;
}

.address-title,
.text {
	color: <?php echo esc_attr( $tinta ); ?>;
	font-family: <?php echo $sans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
}

#addresses td {
	padding: 0 12px 0 0;
	vertical-align: top;
}

.additional-fields {
	list-style: none;
	margin: 0 0 16px;
	padding: 0;
	color: <?php echo esc_attr( $tinta ); ?>;
}

.email-introduction {
	padding-bottom: 8px;
}

.email-additional-content p,
.email-additional-content-aligned p {
	color: <?php echo esc_attr( $suave ); ?>;
	font-size: 14px;
	line-height: 21px;
	text-align: left;
}

img {
	border: 0;
	height: auto;
	max-width: 100%;
	outline: none;
	text-decoration: none;
}

#body_content_inner .order_item img {
	margin-right: 12px;
	vertical-align: middle;
}
<?php
