<?php
/**
 * Guía (entrada del blog): lectura cómoda con índice automático de los apartados, tiempo de lectura y compra al final.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$c = dipt_contenido( 'guias' );
get_header();
while ( have_posts() ) :
	the_post();
	$html   = apply_filters( 'the_content', get_the_content() );
	$indice = array();
	// Anclas en los h2 para el índice lateral
	$html = preg_replace_callback( '#<h2([^>]*)>(.*?)</h2>#s', static function ( $m ) use ( &$indice ) {
		$id       = 'a-' . sanitize_title( wp_strip_all_tags( $m[2] ) );
		$indice[] = array( $id, wp_strip_all_tags( $m[2] ) );
		return '<h2' . $m[1] . ' id="' . esc_attr( $id ) . '">' . $m[2] . '</h2>';
	}, $html );
	$minutos = max( 1, (int) round( str_word_count( wp_strip_all_tags( $html ) ) / 220 ) );
	dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ), array( get_the_title(), get_permalink() ) ) );
	?>
	<article class="gu">
		<header class="gu-cabeza">
			<div class="envoltura">
				<?php echo dipt_migas( $GLOBALS['dipt_migas'] ); // phpcs:ignore ?>
				<h1 class="t-h1"><?php the_title(); ?></h1>
				<p class="dato suave"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?> · <?php echo esc_html( sprintf( $c['lectura'] ?? '%d min', $minutos ) ); ?></p>
			</div>
		</header>
		<div class="envoltura gu-cuerpo">
			<?php if ( count( $indice ) > 1 ) : ?>
				<nav class="gu-indice" aria-label="<?php echo esc_attr( $c['indice'] ?? '' ); ?>">
					<p class="dato"><?php echo esc_html( $c['indice'] ?? '' ); ?></p>
					<ol><?php foreach ( $indice as $e ) : ?><li><a href="#<?php echo esc_attr( $e[0] ); ?>"><?php echo esc_html( $e[1] ); ?></a></li><?php endforeach; ?></ol>
				</nav>
			<?php endif; ?>
			<div class="prosa gu-texto"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenido de la entrada filtrado por the_content ?></div>
		</div>
		<aside class="gu-compra tono-oscuro">
			<div class="envoltura gu-compra__in">
				<p class="t-h3"><?php echo esc_html( $c['cta'] ?? '' ); ?></p>
				<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			</div>
		</aside>
	</article>
	<?php
endwhile;
get_footer();
