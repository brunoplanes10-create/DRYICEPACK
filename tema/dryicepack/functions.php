<?php
/**
 * Tema DryIcePack · "Cadena de frío"
 *
 * Organización:
 *   inc/setup.php        Soportes del tema, menús y limpieza del <head>
 *   inc/datos.php        Datos reales del negocio: productos, tarifa de envío, corte de las 12:00
 *   inc/assets.php       CSS/JS por página, fuente y precargas
 *   inc/componentes.php  Piezas reutilizables: botones, etiqueta de expedición, caja EPS 3D, reloj de corte, FAQ
 *   inc/seo.php          Datos estructurados (FAQPage, LocalBusiness) sin duplicar Rank Math
 *   inc/woocommerce.php  Ajustes de tienda (solo si WooCommerce está activo)
 *
 * La lógica de la tienda (envío, límites, recogida, checkout) vive en el plugin dryicepack-tienda,
 * para que la tienda siga funcionando aunque algún día cambie el tema.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DIPT_VERSION', '1.0.0' );
define( 'DIPT_DIR', get_template_directory() );
define( 'DIPT_URI', get_template_directory_uri() );

require DIPT_DIR . '/inc/setup.php';
require DIPT_DIR . '/inc/datos.php';
require DIPT_DIR . '/inc/assets.php';
require DIPT_DIR . '/inc/componentes.php';
require DIPT_DIR . '/inc/seo.php';

if ( class_exists( 'WooCommerce' ) ) {
	require DIPT_DIR . '/inc/woocommerce.php';
}
