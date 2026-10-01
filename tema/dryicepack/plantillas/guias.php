<?php
/**
 * Guías (índice del blog): lista editorial con número grande, título y resumen; la primera, destacada con imagen.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'guias' );
$q = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'ignore_sticky_posts' => true ) );
get_header();
?>
<section class="gs-cabeza" aria-labelledby="gs-t">
	<div class="envoltura">
		<?php echo dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-gigante" id="gs-t"><?php echo esc_html( $c['titulo'] ?? 'Guías' ); ?></h1>
		<p class="entrada suave"><?php echo esc_html( $c['texto'] ?? '' ); ?></p>
	</div>
</section>
<section class="gs-lista tono-blanco seccion" aria-labelledby="gs-t">
	<div class="envoltura">
		<?php if ( $q->have_posts() ) : ?>
			<ol class="gs-entradas">
				<?php $n = 0; while ( $q->have_posts() ) : $q->the_post(); $n++; ?>
					<li data-revela style="--i:<?php echo (int) min( $n, 6 ); ?>">
						<a href="<?php the_permalink(); ?>">
							<span class="gs-entradas__n"><?php echo esc_html( sprintf( '%02d', $n ) ); ?></span>
							<span class="gs-entradas__titulo"><?php the_title(); ?></span>
							<span class="gs-entradas__resumen"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></span>
							<span class="gs-entradas__meta dato"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?> · <?php echo esc_html( max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) ) . ' min' ); ?></span>
						</a>
					</li>
				<?php endwhile; wp_reset_postdata(); ?>
			</ol>
		<?php else : ?>
			<p><?php echo esc_html( $c['vacio'] ?? '' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
