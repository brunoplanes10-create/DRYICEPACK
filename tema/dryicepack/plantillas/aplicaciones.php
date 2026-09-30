<?php
/**
 * Usos (índice): A1 collage de imágenes a distintas velocidades · A2 mosaico de sectores ·
 * A3 selector "qué formato para lo tuyo" · A4 otros usos en cinta que avanza sola.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'aplicaciones' );
get_header();
?>

<!-- A1 · Collage -->
<section class="a-hero" aria-labelledby="a-hero-t">
	<div class="a-hero__collage" aria-hidden="true">
		<?php
		$capas = array( array( 'uso-hosteleria', '0.12' ), array( 'uso-transporte', '-0.08' ), array( 'uso-laboratorio', '0.18' ), array( 'uso-eventos', '-0.14' ), array( 'uso-industria', '0.06' ) );
		foreach ( $capas as $i => $capa ) :
			?>
			<figure class="a-hero__img a-hero__img--<?php echo (int) $i + 1; ?>" data-paralaje="<?php echo esc_attr( $capa[1] ); ?>"><img src="<?php echo esc_url( dipt_foto_url( $capa[0], 480 ) ); ?>" alt="" width="480" height="320" <?php echo $i < 2 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>></figure>
		<?php endforeach; ?>
	</div>
	<div class="envoltura a-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-hero" id="a-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['titulo'] ) ); ?></h1>
		<p class="entrada"><?php echo esc_html( $c['texto'] ); ?></p>
	</div>
</section>

<!-- A2 · Mosaico de sectores -->
<section class="a-sectores tono-blanco seccion" aria-label="<?php echo esc_attr( wp_strip_all_tags( str_replace( '|', ' ', $c['titulo'] ) ) ); ?>">
	<div class="envoltura">
		<ul class="a-bento">
			<?php foreach ( $c['sectores'] as $i => $s ) : ?>
				<li class="a-bento__pieza a-bento__pieza--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<a href="<?php echo esc_url( dipt_url( $s[0] ) ); ?>">
						<?php echo dipt_foto( $s[3], '', array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?>
						<span class="a-bento__texto"><span class="dato">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $s[1] ); ?></strong><span><?php echo esc_html( $s[2] ); ?></span></span>
						<span class="a-bento__flecha" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- A3 · Selector de formato -->
<section class="a-selector tono-marino seccion" aria-labelledby="a-selector-t" data-selector>
	<div class="envoltura a-selector__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['selector']['titulo'], 't-h2', 'a-selector-t' ); // phpcs:ignore ?>
			<p class="dato a-selector__pregunta"><?php echo esc_html( $c['selector']['pregunta'] ); ?></p>
			<div class="a-selector__usos" role="radiogroup" aria-label="<?php echo esc_attr( $c['selector']['pregunta'] ); ?>">
				<?php foreach ( $c['selector']['usos'] as $i => $u ) : ?>
					<button type="button" role="radio" aria-checked="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-uso="<?php echo (int) $i; ?>"><?php echo esc_html( $u[0] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="a-selector__resultado" aria-live="polite">
			<?php foreach ( $c['selector']['usos'] as $i => $u ) : ?>
				<div class="a-selector__panel" data-panel="<?php echo (int) $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
					<span class="a-selector__icono" aria-hidden="true"><?php echo dipt_icono( '3mm' === $u[1] ? 'pellet' : 'nugget' ); // phpcs:ignore ?></span>
					<p class="a-selector__formato"><?php echo esc_html( $c['selector']['formatos'][ $u[1] ] ); ?></p>
					<p><?php echo esc_html( $u[2] ); ?></p>
					<?php echo dipt_enlace( $c['selector']['ver'] . ': ' . $u[0], dipt_url( $u[3] ) ); // phpcs:ignore ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- A4 · Otros usos en cinta -->
<section class="a-otros tono-papel seccion" aria-labelledby="a-otros-t">
	<div class="envoltura"><h2 class="t-h2" id="a-otros-t" data-revela><?php echo esc_html( $c['otros']['titulo'] ); ?></h2></div>
	<div class="a-cinta">
		<ul class="a-cinta__pista">
			<?php foreach ( array_merge( $c['otros']['lista'], $c['otros']['lista'] ) as $i => $o ) : ?>
				<li<?php echo $i >= count( $c['otros']['lista'] ) ? ' aria-hidden="true"' : ''; ?>><img src="<?php echo esc_url( dipt_foto_url( $o[2], 480 ) ); ?>" alt="" width="480" height="320" loading="lazy"><strong><?php echo esc_html( $o[0] ); ?></strong><span><?php echo esc_html( $o[1] ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div class="envoltura"><p class="a-otros__enlace"><?php echo dipt_enlace( $c['otros']['indunova'], 'https://indunova.es/', array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p></div>
</section>

<?php
get_footer();
