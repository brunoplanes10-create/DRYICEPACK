<?php
/**
 * Textos legales (aviso legal, privacidad, cookies, condiciones de venta): documento con índice fijo.
 * El texto está en contenido/{idioma}/legal-{clave}.php. Si el editor de WordPress tiene contenido en la página, se muestra debajo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$clave = dipt_pagina_actual();
$c     = dipt_contenido( 'legal-' . $clave );

/*
 * Tablas (finalidades de la privacidad, cookies): cada celda lleva el nombre de su columna en data-label y la tabla va
 * dentro de una región con scroll y foco visible. En móvil (legal.css) cada fila se apila como una ficha con esas etiquetas,
 * así la 4.ª columna no queda cortada a 390 px. Se aplica después de wp_kses_post (que quitaría tabindex).
 */
$dipt_tablas = static function ( $html, $nombre ) {
	return preg_replace_callback( '#<table>(.*?)</table>#s', static function ( $t ) use ( $nombre ) {
		preg_match_all( '#<th[^>]*>(.*?)</th>#s', $t[1], $th );
		$cab = array_map( static fn( $x ) => trim( wp_strip_all_tags( $x ) ), $th[1] );
		$cuerpo = preg_replace_callback( '#<tr>(.*?)</tr>#s', static function ( $tr ) use ( $cab ) {
			$i = 0;
			return '<tr>' . preg_replace_callback( '#<td>#', static function () use ( &$i, $cab ) {
				return '<td data-label="' . esc_attr( $cab[ $i++ ] ?? '' ) . '">';
			}, $tr[1] ) . '</tr>';
		}, $t[1] );
		return '<div class="lg-tabla" role="region" tabindex="0" aria-label="' . esc_attr( $nombre ) . '"><table>' . $cuerpo . '</table></div>';
	}, $html );
};
get_header();
?>
<article class="lg">
	<header class="lg-cabeza">
		<div class="envoltura">
			<?php echo dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo'] ?? get_the_title(), dipt_url( $clave ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1"><?php echo esc_html( $c['titulo'] ?? get_the_title() ); ?></h1>
			<?php if ( ! empty( $c['actualizado'] ) ) : ?><p class="lg-cabeza__meta dato"><span><?php echo esc_html( $c['actualizado'] ); ?></span></p><?php endif; ?>
		</div>
	</header>
	<div class="envoltura lg-cuerpo">
		<nav class="lg-indice" aria-label="<?php echo esc_attr( $c['titulo'] ?? '' ); ?>">
			<ol>
				<?php foreach ( (array) ( $c['secciones'] ?? array() ) as $i => $s ) : ?>
					<li><a href="#s<?php echo (int) $i + 1; ?>"><?php echo esc_html( $s[0] ); ?></a></li>
				<?php endforeach; ?>
			</ol>
		</nav>
		<div class="prosa lg-texto">
			<?php foreach ( (array) ( $c['secciones'] ?? array() ) as $i => $s ) : ?>
				<section id="s<?php echo (int) $i + 1; ?>">
					<h2><span class="dato"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php echo esc_html( $s[0] ); ?></h2>
					<?php echo $dipt_tablas( wp_kses_post( $s[1] ), $s[0] ); // phpcs:ignore -- HTML ya filtrado por wp_kses_post; data-label con esc_attr ?>
				</section>
			<?php endforeach; ?>
			<?php
			while ( have_posts() ) :
				the_post();
				if ( '' !== trim( (string) get_the_content() ) ) the_content();
			endwhile;
			?>
		</div>
	</div>
</article>
<?php
get_footer();
