<?php
/**
 * Página genérica (páginas sin plantilla propia: legales, carrito/checkout de WooCommerce, etc.).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
while ( have_posts() ) :
	the_post();
	$es_tienda = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	?>
	<section class="cabecera-pagina sobre-oscuro<?php echo $es_tienda ? ' cabecera-pagina--compacta' : ''; ?>">
		<?php echo dipt_niebla( '', 2 ); // phpcs:ignore ?>
		<div class="contenedor">
			<h1><?php the_title(); ?></h1>
			<?php if ( $es_tienda ) echo dipt_reloj_corte( 'reloj-corte--cabecera' ); // phpcs:ignore ?>
		</div>
	</section>
	<section class="seccion seccion--contenido">
		<div class="contenedor contenido-libre">
			<?php the_content(); ?>
		</div>
	</section>
	<?php
endwhile;
get_footer();
