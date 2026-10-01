<?php
/**
 * Fiestas y eventos: V1 foto con zoom lento y título centrado · V2 recetas de niebla en hojas de recetario ·
 * V3 tres pasos con número fijo que cambia al bajar · V4 aviso de Halloween (solo en temporada) ·
 * V5 con público, así · V6 preguntas en tres columnas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'eventos' );
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-eventos', '100vw' );
dipt_registrar_faq( $c['faq']['lista'] );
$hoy = current_time( 'Y-m-d' );
$temporada_halloween = $hoy >= current_time( 'Y' ) . '-09-01' && $hoy <= current_time( 'Y' ) . '-10-31' && dipt_traducida( 'halloween', dipt_idioma() );
get_header();
?>

<!-- V1 · Foto con zoom lento -->
<section class="ev-hero tono-oscuro" aria-labelledby="ev-hero-t">
	<div class="ev-hero__foto"><?php echo dipt_foto( 'uso-eventos', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '100vw' ) ); // phpcs:ignore ?></div>
	<div class="niebla" aria-hidden="true"><i></i><i></i></div>
	<div class="envoltura ev-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'eventos' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1" id="ev-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
		<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
	</div>
</section>

<!-- V2 · Recetas de niebla -->
<section class="ev-recetas tono-papel seccion" aria-labelledby="ev-recetas-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['recetas']['titulo'], 't-h2 ev-recetas__titulo', 'ev-recetas-t' ); // phpcs:ignore ?>
		<div class="ev-recetario">
			<?php foreach ( $c['recetas']['lista'] as $i => $r ) : ?>
				<article class="ev-receta ev-receta--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<p class="ev-receta__kg"><?php echo esc_html( $r[1] ); ?></p>
					<h3><?php echo esc_html( $r[0] ); ?></h3>
					<p class="ev-receta__para"><?php echo esc_html( $r[3] ); ?></p>
					<p class="dato ev-receta__etq"><?php echo esc_html( $c['recetas']['ingredientes'] ); ?></p>
					<ul><?php foreach ( $r[2] as $ing ) : ?><li><?php echo esc_html( $ing ); ?></li><?php endforeach; ?></ul>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- V3 · Tres pasos con número fijo -->
<section class="ev-pasos tono-oscuro seccion" aria-labelledby="ev-pasos-t" data-pasos>
	<div class="envoltura ev-pasos__in">
		<div class="ev-pasos__fijo">
			<?php echo dipt_titulo( 'h2', $c['pasos']['titulo'], 't-h2', 'ev-pasos-t' ); // phpcs:ignore ?>
			<p class="ev-pasos__numero" aria-hidden="true"><span data-paso-numero>1</span><small>/3</small></p>
		</div>
		<ol class="ev-pasos__lista">
			<?php foreach ( $c['pasos']['lista'] as $i => $p ) : ?>
				<li data-paso="<?php echo (int) $i + 1; ?>"><h3><?php echo esc_html( $p[0] ); ?></h3><p><?php echo esc_html( $p[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<?php if ( $temporada_halloween ) : ?>
<!-- V4 · Halloween (septiembre y octubre) -->
<section class="ev-halloween" aria-labelledby="ev-halloween-t">
	<div class="envoltura ev-halloween__in">
		<h2 class="t-h3" id="ev-halloween-t"><?php echo esc_html( $c['halloween']['titulo'] ); ?></h2>
		<p><?php echo esc_html( $c['halloween']['texto'] ); ?></p>
		<?php echo dipt_enlace( $c['halloween']['enlace'], dipt_url( 'halloween' ) ); // phpcs:ignore ?>
	</div>
</section>
<?php endif; ?>

<!-- V5 · Con público, así -->
<section class="ev-publico tono-blanco seccion" aria-labelledby="ev-publico-t">
	<div class="envoltura ev-publico__in">
		<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-hero', 'ev-publico-t' ); // phpcs:ignore ?>
		<ul class="ev-publico__lista">
			<?php foreach ( $c['seguridad']['lista'] as $i => $l ) : ?><li data-revela style="--i:<?php echo (int) $i; ?>"><?php echo esc_html( $l ); ?></li><?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- V6 · Preguntas en tres columnas -->
<section class="ev-faq tono-escarcha seccion" aria-labelledby="ev-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="ev-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="ev-faq__columnas">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<article data-revela style="--i:<?php echo (int) $i; ?>"><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></article>
			<?php endforeach; ?>
		</div>
		<p class="ev-faq__seg"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<?php
get_footer();
