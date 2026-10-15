<?php
/**
 * Guías (índice, rediseño 13/10/2026, plan 2.11): la primera guía destacada con foto grande y el resto en filas
 * con miniatura 4:5 que se descubre al entrar. Primero las guías del tema en el idioma de la página; en castellano,
 * después las entradas del blog que se publiquen.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c        = dipt_contenido( 'guias' );
$idioma   = dipt_idioma();
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
		'foto'    => $g['foto'] ?? '',
	);
}
if ( 'es' === $idioma ) {
	// WP_Query no tiene «post_name__not_in»: se filtra después por el nombre de la entrada. Fuera las guías del tema
	// (ya están arriba) y la entrada de ejemplo de WordPress («Hola, mundo» / «Hello world!»).
	$excluir = array_merge( array_keys( dipt_guias() ), array( 'hola-mundo', 'hello-world' ) );
	$q       = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 30, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	foreach ( $q->posts as $post ) {
		if ( in_array( $post->post_name, $excluir, true ) ) continue;
		$entradas[] = array(
			'url'     => get_permalink( $post ),
			'titulo'  => get_the_title( $post ),
			'resumen' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'meta'    => get_the_date( 'j M Y', $post ) . ' · ' . sprintf( $c['lectura'] ?? '%d min', max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 220 ) ) ),
			'foto'    => '',
		);
	}
}
$destacada = $entradas ? array_shift( $entradas ) : null;
get_header();
?>
<section class="gs-cabeza tono-papel" aria-labelledby="gs-t">
	<div class="envoltura">
		<?php echo dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ) ) ); // phpcs:ignore ?>
		<div class="gs-cabeza__in">
			<h1 class="t-gigante" id="gs-t"><?php echo esc_html( $c['titulo'] ?? 'Guías' ); ?></h1>
			<p class="entrada suave"><?php echo esc_html( $c['texto'] ?? '' ); ?></p>
		</div>
	</div>
</section>
<section class="gs-lista tono-blanco" aria-labelledby="gs-t">
	<div class="envoltura">
		<?php if ( $destacada ) : ?>
			<a class="gs-destacada" href="<?php echo esc_url( $destacada['url'] ); ?>" data-activa>
				<?php if ( $destacada['foto'] ) : ?>
					<?php /* Clase f-{foto}: encuadre propio de cada foto en guias.css (p. ej. uso-eventos sin la máquina de humo).
					   sizes un 36 % mayor que el hueco: ese encuadre amplía la foto 1,36× y, si no, el navegador elige una versión pequeña y se pixela */ ?>
					<span class="gs-destacada__foto foto--lamina foto--4x3" data-cortina><?php echo dipt_foto( $destacada['foto'], '', array( 'sizes' => '(max-width: 900px) 125vw, 76vw', 'clase' => 'f-' . sanitize_html_class( $destacada['foto'] ) ) ); // phpcs:ignore ?></span>
				<?php endif; ?>
				<span class="gs-destacada__texto" data-revela>
					<span class="gs-destacada__n dato" aria-hidden="true">01</span>
					<span class="gs-destacada__titulo"><?php echo esc_html( $destacada['titulo'] ); ?></span>
					<span class="gs-destacada__resumen"><?php echo esc_html( $destacada['resumen'] ); ?></span>
					<span class="gs-destacada__pie"><span class="dato"><?php echo esc_html( $destacada['meta'] ); ?></span><span class="gs-ir" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></span>
				</span>
			</a>
		<?php endif; ?>
		<?php if ( $entradas ) : ?>
			<ol class="gs-entradas" start="2" data-activa-grupo>
				<?php foreach ( $entradas as $n => $e ) : ?>
					<li data-activa data-revela style="--i:<?php echo (int) min( $n, 5 ); ?>">
						<a href="<?php echo esc_url( $e['url'] ); ?>">
							<?php if ( $e['foto'] ) : ?>
								<span class="gs-entradas__foto foto--lamina foto--4x5" data-cortina><?php echo dipt_foto( $e['foto'], '', array( 'sizes' => '(max-width: 800px) 30vw, 10rem', 'clase' => 'f-' . sanitize_html_class( $e['foto'] ) ) ); // phpcs:ignore ?></span>
							<?php else : ?>
								<span class="gs-entradas__foto gs-entradas__foto--vacia foto--4x5" aria-hidden="true"></span>
							<?php endif; ?>
							<span class="gs-entradas__n dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $n + 2 ) ); ?></span>
							<span class="gs-entradas__titulo"><?php echo esc_html( $e['titulo'] ); ?></span>
							<span class="gs-entradas__resumen"><?php echo esc_html( $e['resumen'] ); ?></span>
							<span class="gs-entradas__meta dato"><?php echo esc_html( $e['meta'] ); ?></span>
							<span class="gs-ir gs-entradas__ir" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<?php if ( ! $destacada ) : ?>
			<p><?php echo esc_html( $c['vacio'] ?? '' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
// Cierre común (la sección antes del pie siempre es noche): el titular es el 'cta' ya revisado de las guías,
// partido después de la pregunta. Foto: la furgoneta de reparto (no está en la lista de guías; sin foto, la mitad
// derecha del cierre quedaba vacía en escritorio).
if ( ! empty( $c['cta'] ) ) {
	dipt_cierre( array(
		'titulo'  => preg_replace( '/\?\s+(.+)$/u', '?|<em>$1</em>', (string) $c['cta'], 1 ),
		'entrada' => 'revela',
		'foto'    => 'reparto-furgoneta',
		'recorte' => '50% 50%',
	) );
}
get_footer();
