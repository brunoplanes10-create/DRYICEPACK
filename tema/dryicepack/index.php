<?php
/**
 * Plantilla de respaldo (listados y cualquier vista sin plantilla propia).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="cabecera-pagina sobre-oscuro">
	<?php echo dipt_niebla( '', 2 ); // phpcs:ignore ?>
	<div class="contenedor">
		<h1><?php echo esc_html( is_search() ? 'Resultados para «' . get_search_query() . '»' : wp_strip_all_tags( get_the_archive_title() ?: get_bloginfo( 'name' ) ) ); ?></h1>
	</div>
</section>
<section class="seccion">
	<div class="contenedor listado" data-condensa="grupo">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<article class="listado__item">
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; the_posts_pagination(); else : ?>
			<p>No hay nada por aquí todavía.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
