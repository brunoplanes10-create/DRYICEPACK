<?php
/**
 * Plantilla de reserva (archivos, búsquedas). Las guías usan plantillas/guias.php y single.php.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="pag">
	<div class="envoltura">
		<header class="pag__cabeza"><h1 class="t-h1"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ?: get_bloginfo( 'name' ) ) ); ?></h1></header>
		<div class="pag__cuerpo prosa">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<article><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
			<?php endwhile; the_posts_pagination(); else : ?>
				<p><?php echo esc_html( dipt_t( 'no_encontrada' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
