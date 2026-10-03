<?php
/**
 * Fiestas y eventos: V1 foto con zoom lento y título centrado · V2 recetas de niebla en hojas de recetario ·
 * V3 tres pasos con número fijo que cambia al bajar · V4 aviso de Halloween (solo en temporada) ·
 * V5 con público, así · V6 preguntas en tres columnas · V7 llamada final con la semana de reparto.
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
		<h1 class="t-h1" id="ev-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
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

<?php if ( ! empty( $c['final'] ) ) : $f = $c['final']; $estado_dia = array( 'no', 'si', 'si', 'si', 'si', 'zona', 'no' ); // De lunes a domingo ?>
<!-- V7 · Final: la semana de reparto (lunes y domingo sin reparto, sábado según zona) y botón de compra -->
<section class="ev-final tono-marino seccion" aria-labelledby="ev-final-t">
	<div class="envoltura ev-final__in">
		<div class="ev-final__texto">
			<?php echo dipt_titulo( 'h2', $f['titulo'], 't-h2', 'ev-final-t' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $f['texto'] ); ?></p>
			<div data-revela style="--i:1"><?php echo dipt_boton( $f['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?></div>
		</div>
		<div class="ev-final__calendario">
			<ol class="ev-final__semana" aria-label="<?php echo esc_attr( $f['semana'] ); ?>">
				<?php foreach ( $f['dias'] as $i => $dia ) : $estado = $estado_dia[ $i ] ?? 'si'; ?>
					<li class="ev-final__dia ev-final__dia--<?php echo esc_attr( $estado ); ?>" data-revela style="--i:<?php echo (int) $i; ?>">
						<span aria-hidden="true"><?php echo esc_html( $dia[0] ); ?></span>
						<span class="visually-hidden"><?php echo esc_html( $dia[1] . ': ' . ( $f['estados'][ $estado ] ?? '' ) ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<ul class="ev-final__leyenda dato" aria-hidden="true">
				<?php foreach ( array( 'si', 'zona', 'no' ) as $estado ) : ?>
					<li class="ev-final__clave ev-final__clave--<?php echo esc_attr( $estado ); ?>"><?php echo esc_html( $f['estados'][ $estado ] ?? '' ); ?></li>
				<?php endforeach; ?>
			</ul>
			<p class="ev-final__nota"><?php echo esc_html( $f['nota'] ); ?></p>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
