<?php
/**
 * Páginas sin plantilla propia: carrito, checkout, Mi cuenta, /factura/ y cualquier página creada desde el editor.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$dipt_tienda = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="pag<?php echo $dipt_tienda ? ' pag--tienda' : ''; ?>">
		<div class="envoltura">
			<header class="pag__cabeza">
				<h1 class="t-h1"><?php the_title(); ?></h1>
			</header>
			<div class="pag__cuerpo<?php echo $dipt_tienda ? '' : ' prosa'; ?>">
				<?php the_content(); ?>
			</div>
		</div>
	</section>
	<?php
endwhile;
get_footer();
