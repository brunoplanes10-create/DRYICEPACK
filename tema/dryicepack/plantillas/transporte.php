<?php
/**
 * Transporte en frío: T1 título partido con foto vertical y datos · T2 caja en sección con el frío bajando ·
 * T3 tres reglas en fichas escalonadas · T4 planificador de rutas (consumo al mes) · T5 preguntas en rejilla.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'transporte' );
dipt_precargar( 'uso-transporte', '(max-width: 900px) 100vw, 45vw' );
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- T1 · Título partido con foto vertical -->
<section class="t-hero" aria-labelledby="t-hero-t">
	<div class="envoltura t-hero__in">
		<div class="t-hero__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'transporte' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1" id="t-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
		<figure class="t-hero__foto"><?php echo dipt_foto( 'uso-transporte', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 45vw', 'recorte' => '40% 50%' ) ); // phpcs:ignore ?></figure>
	</div>
	<ul class="t-hero__datos dato envoltura"><?php foreach ( $c['hero']['datos'] as $d ) : ?><li><?php echo esc_html( $d ); ?></li><?php endforeach; ?></ul>
</section>

<!-- T2 · Caja en sección -->
<section class="t-caja tono-oscuro seccion" aria-labelledby="t-caja-t" data-vivo>
	<div class="envoltura t-caja__in">
		<div>
			<h2 class="t-h2" id="t-caja-t" data-revela><?php dipt_html( $c['caja']['titulo'] ); ?></h2>
			<p class="entrada" data-revela><?php echo esc_html( $c['caja']['texto'] ); ?></p>
		</div>
		<div class="t-seccion" aria-hidden="true">
			<div class="t-seccion__caja">
				<div class="t-seccion__hielo"><?php for ( $i = 0; $i < 14; $i++ ) : ?><b></b><?php endfor; ?></div>
				<div class="t-seccion__flechas"><?php for ( $i = 0; $i < 5; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>"></i><?php endfor; ?></div>
				<div class="t-seccion__producto"><span></span><span></span><span></span></div>
			</div>
			<ul class="t-seccion__marcas dato"><?php foreach ( $c['caja']['marcas'] as $i => $m ) : ?><li class="m<?php echo (int) $i + 1; ?>"><?php echo esc_html( $m ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>

<!-- T3 · Tres reglas en fichas escalonadas -->
<section class="t-reglas tono-papel seccion" aria-labelledby="t-reglas-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['reglas']['titulo'], 't-h2', 't-reglas-t' ); // phpcs:ignore ?>
		<ol class="t-escalera">
			<?php foreach ( $c['reglas']['lista'] as $i => $r ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="t-escalera__n"><?php echo (int) $i + 1; ?></span><h3><?php echo esc_html( $r[0] ); ?></h3><p><?php echo esc_html( $r[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- T4 · Planificador de rutas -->
<section class="t-plan tono-blanco seccion" aria-labelledby="t-plan-t" data-plan>
	<div class="envoltura t-plan__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['plan']['titulo'], 't-h2', 't-plan-t' ); // phpcs:ignore ?>
			<p class="suave t-plan__texto" data-revela><?php echo esc_html( $c['plan']['texto'] ); ?></p>
		</div>
		<div class="t-plan__panel">
			<fieldset class="t-plan__dias"><legend class="visually-hidden"><?php echo esc_html( wp_strip_all_tags( str_replace( '|', ' ', $c['plan']['titulo'] ) ) ); ?></legend>
				<?php foreach ( $c['plan']['dias'] as $i => $d ) : ?>
					<label><input type="checkbox" value="1" data-plan-dia<?php checked( in_array( $i, array( 0, 2, 4 ), true ) ); ?>><span><?php echo esc_html( $d ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<label class="t-plan__kg"><input type="number" min="3" max="250" value="20" inputmode="numeric" data-plan-kg><span><?php echo esc_html( $c['plan']['kg'] ); ?></span></label>
			<p class="t-plan__resultado"><span class="dato"><?php echo esc_html( $c['plan']['mes'] ); ?></span><strong><output data-plan-mes>258</output> kg</strong></p>
			<p class="dato suave"><?php echo esc_html( $c['plan']['nota'] ); ?></p>
			<?php echo dipt_boton( $c['plan']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- T5 · Preguntas en rejilla -->
<section class="t-faq tono-escarcha seccion" aria-labelledby="t-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="t-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="t-faq__rejilla">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<article data-revela style="--i:<?php echo (int) $i; ?>"><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></article>
			<?php endforeach; ?>
		</div>
		<p class="t-faq__seg"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<?php
get_footer();
