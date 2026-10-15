<?php
/**
 * Seguridad (rediseño 13/10/2026, plan 2.10): S1 respuesta directa con etiqueta de transporte · S2 riesgo → qué pasa → qué hacer ·
 * S3 haz / evita en pantalla partida con la foto montada sobre la costura · S4 primeros auxilios con el 112 fijo ·
 * S5 ficha de datos (PDF) que se endereza · S6 preguntas abiertas · cierre común.
 * Tonos: papel · blanco · hielo | noche · papel · escarcha · blanco · cierre noche · pie marino.
 * Todos los avisos se muestran completos: aquí no se quita ni se acorta ninguno.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'seguridad' );
dipt_registrar_faq( array_merge( array( array( wp_strip_all_tags( str_replace( '|', ' ', $c['hero']['titulo'] ) ), $c['hero']['respuesta'] ) ), $c['faq']['lista'] ) );
get_header();
?>

<!-- S1 · Respuesta directa y etiqueta -->
<section class="s-hero tono-papel" aria-labelledby="s-hero-t">
	<div class="envoltura s-hero__in">
		<div>
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'seguridad' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero s-hero__titulo" id="s-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="s-hero__respuesta"><?php echo esc_html( $c['hero']['respuesta'] ); ?></p>
		</div>
		<div class="s-etiqueta" aria-hidden="true" data-vivo>
			<div class="s-etiqueta__rombo"><span class="s-etiqueta__rayas"></span><span class="s-etiqueta__nueve">9</span></div>
			<ul class="s-etiqueta__datos dato"><?php foreach ( $c['hero']['etiqueta'] as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>

<!-- S2 · Riesgo → qué pasa → qué hacer -->
<section class="s-riesgos tono-blanco seccion" aria-labelledby="s-riesgos-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['riesgos']['titulo'], 't-h2 s-riesgos__titulo', 's-riesgos-t' ); // phpcs:ignore ?>
		<div class="s-flujo" role="table">
			<div class="s-flujo__cab" role="row"><span role="columnheader"></span><span role="columnheader" class="dato"><?php echo esc_html( $c['riesgos']['causa'] ); ?></span><span role="columnheader" class="dato"><?php echo esc_html( $c['riesgos']['gesto'] ); ?></span></div>
			<?php $iconos = array( 'guante', 'ventilar', 'hermetico' ); foreach ( $c['riesgos']['lista'] as $i => $r ) : ?>
				<div class="s-flujo__fila" role="row" data-revela style="--i:<?php echo (int) $i; ?>">
					<span role="rowheader" class="s-flujo__riesgo"><?php echo dipt_icono( $iconos[ $i ] ); // phpcs:ignore ?><?php echo esc_html( $r[0] ); ?></span>
					<span role="cell" class="s-flujo__pasa"><?php echo esc_html( $r[1] ); ?></span>
					<span role="cell" class="s-flujo__gesto"><?php echo esc_html( $r[2] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- S3 · Haz esto / evita esto, con la foto montada sobre la costura -->
<section class="s-dos" aria-label="<?php echo esc_attr( $c['haz']['titulo'] . ' · ' . $c['evita']['titulo'] ); ?>">
	<div class="s-dos__haz tono-hielo">
		<h2 class="t-h2" data-revela><?php echo esc_html( $c['haz']['titulo'] ); ?></h2>
		<ul><?php foreach ( $c['haz']['lista'] as $i => $l ) : ?><li data-revela style="--i:<?php echo (int) $i; ?>"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?><span><?php echo esc_html( $l ); ?></span></li><?php endforeach; ?></ul>
	</div>
	<div class="s-dos__foto foto--sangre foto--3x4" data-cortina>
		<?php echo dipt_foto( 'uso-laboratorio-pinzas', '', array( 'sizes' => '(max-width: 800px) 62vw, 22vw', 'recorte' => '30% 50%' ) ); // phpcs:ignore ?>
	</div>
	<div class="s-dos__evita tono-oscuro">
		<h2 class="t-h2" data-revela><?php echo esc_html( $c['evita']['titulo'] ); ?></h2>
		<ul><?php foreach ( $c['evita']['lista'] as $i => $l ) : ?><li data-revela style="--i:<?php echo (int) $i; ?>"><?php echo dipt_icono( 'cerrar' ); // phpcs:ignore ?><span><?php echo esc_html( $l ); ?></span></li><?php endforeach; ?></ul>
	</div>
</section>

<!-- S4 · Si pasa algo: el 112 fijo a la izquierda en escritorio, botón de llamada a lo ancho en móvil -->
<section class="s-auxilios tono-papel seccion" aria-labelledby="s-auxilios-t">
	<div class="envoltura s-auxilios__in">
		<div class="s-auxilios__cabeza">
			<h2 class="t-h2" id="s-auxilios-t" data-revela><?php echo esc_html( $c['auxilios']['titulo'] ); ?></h2>
			<a class="s-auxilios__112" href="tel:112" data-revela><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><span>112</span></a>
		</div>
		<ol class="s-auxilios__lista">
			<?php foreach ( $c['auxilios']['lista'] as $i => $a ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><h3><?php echo esc_html( $a[0] ); ?></h3><p><?php echo esc_html( $a[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- S5 · Ficha de datos de seguridad: hoja con sus apartados y una segunda detrás; se endereza al entrar -->
<section class="s-fds tono-escarcha seccion" aria-labelledby="s-fds-t">
	<div class="envoltura s-fds__in">
		<a class="s-pdf" href="<?php echo esc_url( home_url( $c['fds']['archivo'] ) ); ?>" target="_blank" rel="noopener" data-vivo aria-label="<?php echo esc_attr( $c['fds']['boton'] ); ?>">
			<span class="s-pdf__hoja" aria-hidden="true">
				<span class="s-pdf__cab dato"><span>FDS</span><span>CO₂ · UN 1845</span></span>
				<?php foreach ( (array) ( $c['fds']['apartados'] ?? array() ) as $i => $ap ) : ?>
					<span class="s-pdf__ap" style="--i:<?php echo (int) $i; ?>"><span class="dato"><?php echo esc_html( $ap[0] ); ?></span><span><?php echo esc_html( $ap[1] ); ?></span></span>
				<?php endforeach; ?>
			</span>
		</a>
		<div>
			<h2 class="t-h2" id="s-fds-t" data-revela><?php echo esc_html( $c['fds']['titulo'] ); ?></h2>
			<p class="entrada suave" data-revela><?php echo esc_html( $c['fds']['texto'] ); ?></p>
			<p class="s-fds__accion" data-revela><?php echo dipt_boton( $c['fds']['boton'], home_url( $c['fds']['archivo'] ), 'marino', array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?><span class="dato suave"><?php echo esc_html( $c['fds']['meta'] ); ?></span></p>
		</div>
	</div>
</section>

<!-- S6 · Preguntas abiertas (las respuestas se ven sin pulsar: son avisos) -->
<section class="s-faq tono-blanco seccion" aria-labelledby="s-faq-t">
	<div class="envoltura" data-activa-grupo><?php /* un solo filete activo a la vez (el último que cruzó el centro) */ ?>
		<?php echo dipt_titulo( 'h2', $c['faq']['titulo'], 't-h2 s-faq__titulo', 's-faq-t', 'cortina' ); // phpcs:ignore ?>
		<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
			<article class="s-faq__item" data-activa>
				<span class="dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
				<h3 class="t-h3"><?php echo esc_html( $f[0] ); ?></h3>
				<p><?php echo esc_html( $f[1] ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<?php
// Cierre común. Sin el enlace a seguridad: es esta misma página.
if ( ! empty( $c['final']['titulo'] ) ) {
	dipt_cierre( array(
		'titulo'    => $c['final']['titulo'],
		'entrada'   => 'revela', // la entrada línea a línea ya la usa el H2 de S2
		'foto'      => 'anadiendo-hielo',
		'recorte'   => '40% 50%',
		'seguridad' => false,
	) );
}
get_footer();
