<?php
/**
 * Fiestas y eventos (rediseño 13/10/2026, plan 2.8): V1 foto centrada con niebla en capas (sin zoom: es el LCP) ·
 * V2 tres fichas de cantidad, de altura creciente, con la foto de lo que llega · V3 escenario sticky de tres pasos
 * (tres fotos que se funden según el paso; en móvil, cada paso con su foto) · V4 aviso de Halloween (septiembre y octubre) ·
 * V5 hoja de advertencia 2 × 2 con pictogramas · V6 preguntas · V7 final en noche con la semana de reparto.
 * Tonos: noche · papel · noche · (Halloween) · blanco · escarcha · noche · pie marino. Sin placa de cliente.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'eventos' );
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-eventos', '100vw' );
$hoy = current_time( 'Y-m-d' );
$temporada_halloween = $hoy >= current_time( 'Y' ) . '-09-01' && $hoy <= current_time( 'Y' ) . '-10-31' && dipt_traducida( 'halloween', dipt_idioma() );
$ev_temp = 'en' === dipt_idioma() ? '−78.5' : '−78,5';
get_header();
?>

<!-- V1 · Foto centrada con niebla en capas (la foto es el LCP: no se anima; la niebla va encima, después de cargar) -->
<section class="ev-hero tono-oscuro" aria-labelledby="ev-hero-t">
	<div class="ev-hero__foto"><?php echo dipt_foto( 'uso-eventos', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '100vw', 'recorte' => '50% 40%' ) ); // phpcs:ignore ?></div>
	<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura ev-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'eventos' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1" id="ev-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
		<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
		<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
	</div>
</section>

<?php
// V2 · La foto de lo que llega en cada ficha; la altura crece con la cantidad
$ev_fotos = array( 'caja-3mm-lateral', 'caja-16mm-niebla', 'almacen-caja' );
?>
<!-- V2 · Cantidades: tres fichas de altura creciente que suben desde la base (en móvil, un carril) -->
<section class="ev-recetas tono-papel seccion" aria-labelledby="ev-recetas-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['recetas']['titulo'], 't-h2 ev-recetas__titulo', 'ev-recetas-t', 'cortina' ); // phpcs:ignore ?>
		<div class="ev-recetario">
			<?php foreach ( $c['recetas']['lista'] as $i => $r ) : ?>
				<article class="ev-receta ev-receta--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i * 2; ?>">
					<div class="ev-receta__foto"><?php echo dipt_foto( $ev_fotos[ $i ] ?? 'caja-3mm-lateral', '', array( 'sizes' => '(max-width: 900px) 78vw, 30vw' ) ); // phpcs:ignore ?></div>
					<div class="ev-receta__cuerpo">
						<p class="ev-receta__kg"><?php echo esc_html( $r[1] ); ?></p>
						<h3><?php echo esc_html( $r[0] ); ?></h3>
						<p class="ev-receta__para"><?php echo esc_html( $r[3] ); ?></p>
						<p class="ev-receta__etq"><?php echo esc_html( $c['recetas']['ingredientes'] ); ?></p>
						<ul><?php foreach ( $r[2] as $ing ) : ?><li><?php echo esc_html( $ing ); ?></li><?php endforeach; ?></ul>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// V3 · Una foto por paso: agua caliente, pellets con pinzas, reponer
$ev_pasos_fotos = array( 'uso-docencia', 'uso-laboratorio-pinzas', 'anadiendo-hielo' );
$ev_pasos       = $c['pasos']['lista'];
$ev_n           = count( $ev_pasos );
?>
<!-- V3 · Escenario sticky: las fotos se funden con el paso que cruza el centro de la pantalla (data-activa-grupo) -->
<section class="ev-pasos tono-oscuro seccion" aria-labelledby="ev-pasos-t">
	<div class="envoltura ev-pasos__in" data-activa-grupo>
		<div class="ev-pasos__fijo">
			<?php echo dipt_titulo( 'h2', $c['pasos']['titulo'], 't-h2', 'ev-pasos-t' ); // phpcs:ignore ?>
			<div class="ev-escena foto--sangre foto--sangre-izq" aria-hidden="true">
				<?php foreach ( $ev_pasos as $i => $p ) : ?>
					<div class="ev-escena__foto ev-escena__foto--<?php echo (int) $i + 1; ?>"><?php echo dipt_foto( $ev_pasos_fotos[ $i ] ?? 'anadiendo-hielo', '', array( 'sizes' => '44vw' ) ); // phpcs:ignore ?></div>
				<?php endforeach; ?>
				<p class="ev-escena__numero">
					<?php foreach ( $ev_pasos as $i => $p ) : ?><span class="ev-escena__n ev-escena__n--<?php echo (int) $i + 1; ?>"><?php echo (int) $i + 1; ?></span><?php endforeach; ?>
					<small>/<?php echo (int) $ev_n; ?></small>
				</p>
			</div>
		</div>
		<ol class="ev-pasos__lista">
			<?php foreach ( $ev_pasos as $i => $p ) : ?>
				<li class="ev-paso" data-activa>
					<div class="ev-paso__foto" aria-hidden="true"><?php echo dipt_foto( $ev_pasos_fotos[ $i ] ?? 'anadiendo-hielo', '', array( 'sizes' => '92vw' ) ); // phpcs:ignore ?></div>
					<span class="ev-paso__n dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d / %02d', $i + 1, $ev_n ) ); ?></span>
					<h3><?php echo esc_html( $p[0] ); ?></h3>
					<p><?php echo esc_html( $p[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<?php if ( $temporada_halloween ) : ?>
<!-- V4 · Halloween (septiembre y octubre; desaparece el 1 de noviembre): miniatura de la landing y la fecha límite en mono -->
<section class="ev-halloween" aria-labelledby="ev-halloween-t">
	<div class="envoltura ev-halloween__in">
		<a class="ev-halloween__mini" href="<?php echo esc_url( dipt_url( 'halloween' ) ); ?>" tabindex="-1" aria-hidden="true"><img src="<?php echo esc_url( DIPT_URI . '/assets/halloween/img/hero-calabazas-movil-480.webp' ); ?>" width="480" height="640" alt="" loading="lazy" decoding="async"></a>
		<div class="ev-halloween__texto">
			<?php if ( ! empty( $c['halloween']['fecha'] ) ) : ?><p class="ev-halloween__fecha dato"><?php echo esc_html( $c['halloween']['fecha'] ); ?></p><?php endif; ?>
			<h2 class="t-h3" id="ev-halloween-t"><?php echo esc_html( $c['halloween']['titulo'] ); ?></h2>
			<p><?php echo esc_html( $c['halloween']['texto'] ); ?></p>
			<?php echo dipt_enlace( $c['halloween']['enlace'], dipt_url( 'halloween' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
// V5 · Pictograma de cada aviso (iconos propios del tema), en el orden del contenido: niños, ventilación, bebida y recipiente cerrado, guantes
$ev_pictos = array( 'nino', 'ventilar', 'no-beber', 'guante' );
?>
<!-- V5 · Con invitados: hoja de advertencia 2 × 2 con cabecera marino y el enlace a la seguridad completa -->
<section class="ev-publico tono-blanco seccion" aria-labelledby="ev-publico-t">
	<div class="envoltura ev-publico__in">
		<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-hero', 'ev-publico-t', 'revela' ); // phpcs:ignore ?>
		<div class="ev-aviso" data-revela>
			<p class="ev-aviso__cabeza" aria-hidden="true"><span class="ev-aviso__senal"><?php echo dipt_icono( 'copo' ); // phpcs:ignore ?></span><span class="dato">CO<sub>2</sub> · <?php echo esc_html( $ev_temp ); ?> °C</span></p>
			<ul class="ev-aviso__lista">
				<?php foreach ( $c['seguridad']['lista'] as $i => $l ) : ?>
					<li style="--i:<?php echo (int) $i; ?>"><span class="ev-aviso__picto" aria-hidden="true"><?php echo dipt_icono( $ev_pictos[ $i ] ?? 'guante' ); // phpcs:ignore ?></span><span><?php echo esc_html( $l ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<p class="ev-aviso__pie"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
		</div>
	</div>
</section>

<!-- V6 · Preguntas -->
<section class="ev-faq seccion seccion--densa tono-escarcha" aria-labelledby="ev-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'ev-faq-t' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php if ( ! empty( $c['final'] ) ) : $f = $c['final']; $estado_dia = array( 'no', 'si', 'si', 'si', 'si', 'zona', 'no' ); // De lunes a domingo ?>
<!-- V7 · Final en noche (el pie es marino): la semana de reparto (lunes y domingo sin reparto, sábado según zona) y botón de compra -->
<section class="ev-final tono-oscuro seccion" aria-labelledby="ev-final-t" data-cierre>
	<div class="envoltura ev-final__in">
		<div class="ev-final__texto">
			<?php echo dipt_titulo( 'h2', $f['titulo'], 't-h2', 'ev-final-t', 'cortina' ); // phpcs:ignore ?>
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
