<?php
/**
 * Tema Dryicepack · "Frío en calma"
 *
 *   inc/setup.php        Soportes del tema y limpieza del <head>
 *   inc/idiomas.php      Idioma de la visita y textos comunes (castellano, catalán, inglés)
 *   inc/zonas.php        Zonas de entrega: datos reales (distancia, tiempo, mapa) para las páginas por ciudad y comarca
 *   inc/paginas.php      Registro de páginas: plantilla, traducciones, hreflang y creación de las que falten
 *   inc/datos.php        Datos del negocio (vienen del plugin dryicepack-tienda; aquí solo hay respaldo)
 *   inc/componentes.php  Imágenes, iconos, botones y piezas comunes
 *   inc/assets.php       CSS y JS por página, fuentes precargadas
 *   inc/seo.php          Títulos, descripciones y datos estructurados
 *   inc/woocommerce.php  Ficha de producto, carrito, checkout y Mi cuenta con el diseño del tema
 *
 * Los textos de cada página están en contenido/{es,ca,en}/*.php y las plantillas en plantillas/.
 * La lógica de la tienda (envío, fechas, factura, cuentas de empresa) está en el plugin dryicepack-tienda.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DIPT_VERSION', '2.0.0' );
define( 'DIPT_DIR', get_template_directory() );
define( 'DIPT_URI', get_template_directory_uri() );

require DIPT_DIR . '/inc/setup.php';
require DIPT_DIR . '/inc/idiomas.php';
require DIPT_DIR . '/inc/zonas.php';
require DIPT_DIR . '/inc/paginas.php';
require DIPT_DIR . '/inc/datos.php';
require DIPT_DIR . '/inc/componentes.php';
require DIPT_DIR . '/inc/assets.php';
require DIPT_DIR . '/inc/seo.php';

if ( class_exists( 'WooCommerce' ) ) {
	require DIPT_DIR . '/inc/woocommerce.php';
}
