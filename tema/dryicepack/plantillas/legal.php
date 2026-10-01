<?php
/**
 * Textos legales (aviso legal, privacidad, cookies, condiciones de venta): documento con índice fijo.
 * El texto está en contenido/{idioma}/legal-{clave}.php. Si el editor de WordPress tiene contenido en la página, se muestra debajo.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$clave = dipt_pagina_actual();
$c     = dipt_contenido( 'legal-' . $clave );
get_header();
?>
<article class="lg">
	<header class="lg-cabeza">
		<div class="envoltura">
			<?php echo dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo'] ?? get_the_title(), dipt_url( $clave ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1"><?php echo esc_html( $c['titulo'] ?? get_the_title() ); ?></h1>
			<?php if ( ! empty( $c['actualizado'] ) ) : ?><p class="dato suave"><?php echo esc_html( $c['actualizado'] ); ?></p><?php endif; ?>
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
					<?php echo wp_kses_post( $s[1] ); ?>
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
