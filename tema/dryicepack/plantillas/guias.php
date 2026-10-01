<?php
/**
 * Guías (índice): lista editorial con número grande, título y resumen.
 * Primero las guías del tema en el idioma de la página; en castellano, después las entradas del blog que se publiquen.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c       = dipt_contenido( 'guias' );
$idioma  = dipt_idioma();
$entradas = array();
foreach ( dipt_guias() as $slug => $rutas ) {
	if ( empty( $rutas[ $idioma ] ) ) continue;
	$g = dipt_guia( $slug );
	if ( ! $g ) continue;
	$entradas[] = array(
		'url'     => dipt_url( 'guia-' . $slug ),
		'titulo'  => $g['titulo'],
		'resumen' => $g['extracto'] ?? '',
		'meta'    => sprintf( $c['lectura'] ?? '%d min', max( 1, (int) round( str_word_count( wp_strip_all_tags( $g['contenido'] ) ) / 220 ) ) ),
	);
}
if ( 'es' === $idioma ) {
	$q = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'ignore_sticky_posts' => true, 'post_name__not_in' => array_keys( dipt_guias() ) ) );
	foreach ( $q->posts as $post ) {
		$entradas[] = array(
			'url'     => get_permalink( $post ),
			'titulo'  => get_the_title( $post ),
			'resumen' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'meta'    => get_the_date( 'j M Y', $post ) . ' · ' . sprintf( $c['lectura'] ?? '%d min', max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 220 ) ) ),
		);
	}
}
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
		<?php if ( $entradas ) : ?>
			<ol class="gs-entradas">
				<?php foreach ( $entradas as $n => $e ) : ?>
					<li data-revela style="--i:<?php echo (int) min( $n + 1, 6 ); ?>">
						<a href="<?php echo esc_url( $e['url'] ); ?>">
							<span class="gs-entradas__n"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>
							<span class="gs-entradas__titulo"><?php echo esc_html( $e['titulo'] ); ?></span>
							<span class="gs-entradas__resumen"><?php echo esc_html( $e['resumen'] ); ?></span>
							<span class="gs-entradas__meta dato"><?php echo esc_html( $e['meta'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php else : ?>
			<p><?php echo esc_html( $c['vacio'] ?? '' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
