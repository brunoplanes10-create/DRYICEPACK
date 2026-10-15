<?php
/**
 * Usos (índice) · rediseño del 13/10/2026 (plan 2.7). Tonos: A1 noche · A2 papel · A3 marino · A4 blanco · cierre noche · pie marino.
 * A1 H1 a la izquierda y dos láminas superpuestas (niebla / frío −78,5 °C) con paralaje contrario y niebla por debajo ·
 * A2 mosaico de sectores con franja sólida y formato habitual en mono (cortina escalonada) ·
 * A3 selector: lista de usos, foto cenital del formato a sangre, ficha solapada y cota que compara 3 y 16 mm ·
 * A4 hoja de contactos con otros usos (alturas distintas, nada en bucle; carril en móvil) · cierre común (cajas-oscuro).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c  = dipt_contenido( 'aplicaciones' );
$en = 'en' === dipt_idioma();
// Titular del cierre: el propio de Usos está [a validar]; se usa el del cierre común de la portada (ya revisado)
$fin = dipt_contenido( 'inicio' )['final'] ?? array();
// Formato habitual de cada sector: sale del propio selector (sin datos nuevos)
$formatos_sector = array();
foreach ( $c['selector']['usos'] as $u ) $formatos_sector[ $u[3] ][ $u[1] ] = true;
$formato_texto = static function ( $claves ) {
	$t = array();
	foreach ( array( '3mm' => '3 mm', '16mm' => '16 mm' ) as $k => $v ) if ( isset( $claves[ $k ] ) ) $t[] = $v;
	return implode( ' · ', $t );
};
$uso0 = $c['selector']['usos'][0];
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'caja-3mm-lateral', '(max-width: 900px) 62vw, 27vw' );
get_header();
?>

<!-- A1 · Hero: H1 y dos láminas superpuestas con paralaje contrario; la niebla pasa por debajo (nunca encima de la imagen principal) -->
<section class="a-hero tono-oscuro" aria-labelledby="a-hero-t">
	<div class="niebla a-hero__niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura a-hero__in">
		<div class="a-hero__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ) ) ); // phpcs:ignore ?>
			<?php echo dipt_titulo( 'h1', $c['titulo'], 't-hero', 'a-hero-t', 'ninguna' ); // phpcs:ignore ?>
			<p class="entrada"><?php echo esc_html( $c['texto'] ); ?></p>
			<?php if ( ! empty( $c['hero_boton'] ) ) echo dipt_boton( $c['hero_boton'], '#formato', 'blanco' ); // phpcs:ignore ?>
		</div>
		<div class="a-hero__laminas">
			<figure class="a-lamina a-lamina--niebla" data-paralaje="-0.05">
				<div class="foto--lamina foto--4x5"><?php echo dipt_foto( 'caja-3mm-lateral', '', array( 'lcp' => true, 'sizes' => '(max-width: 900px) 62vw, 27vw' ) ); // phpcs:ignore ?></div>
				<figcaption class="a-lamina__pie dato"><?php echo esc_html( $c['laminas']['niebla'] ?? '' ); ?><span>3 mm</span></figcaption>
			</figure>
			<figure class="a-lamina a-lamina--frio" data-paralaje="0.07">
				<figcaption class="a-lamina__pie dato"><?php echo esc_html( $c['laminas']['frio'] ?? '' ); ?><span><span class="cifra-fija"><span class="cifra-fija__reserva"><?php echo $en ? '−78.5' : '−78,5'; ?></span><span data-cuenta="-78.5" data-desde="20" data-decimales="1"><?php echo $en ? '−78.5' : '−78,5'; ?></span></span> °C</span></figcaption>
				<div class="foto--lamina foto--1x1"><?php echo dipt_foto( 'nuggets-azul', '', array( 'sizes' => '(max-width: 900px) 46vw, 20vw', 'atributos' => array( 'loading' => 'eager' ) ) ); // phpcs:ignore ?></div>
			</figure>
		</div>
	</div>
</section>

<!-- A2 · Mosaico de sectores: fotos al 100 %, franja sólida abajo y formato habitual en mono -->
<section class="a-sectores tono-papel seccion seccion--densa" aria-label="<?php echo esc_attr( wp_strip_all_tags( str_replace( '|', ' ', $c['titulo'] ) ) ); ?>">
	<div class="envoltura">
		<ul class="a-bento">
			<?php foreach ( $c['sectores'] as $i => $s ) : ?>
				<li class="a-bento__pieza a-bento__pieza--<?php echo (int) $i + 1; ?>" data-cortina data-activa style="--i:<?php echo (int) $i; ?>">
					<a href="<?php echo esc_url( dipt_url( $s[0] ) ); ?>">
						<?php echo dipt_foto( $s[3], '', array( 'sizes' => 0 === $i ? '(max-width: 700px) 100vw, 50vw' : '(max-width: 700px) 50vw, 50vw' ) ); // phpcs:ignore ?>
						<span class="a-bento__franja">
							<span class="a-bento__cab"><span class="dato">0<?php echo (int) $i + 1; ?></span><span class="dato a-bento__formato"><?php echo esc_html( $formato_texto( $formatos_sector[ $s[0] ] ?? array() ) ); ?></span></span>
							<?php
							// La flecha va pegada a la última palabra: si el nombre se parte, nunca se queda sola en una línea
							$a_palabras = explode( ' ', $s[1] );
							$a_ultima   = array_pop( $a_palabras );
							?>
							<strong><?php echo esc_html( $a_palabras ? implode( ' ', $a_palabras ) . ' ' : '' ); ?><span class="a-bento__ultima"><?php echo esc_html( $a_ultima ); ?><?php echo dipt_icono( 'flecha', 'a-bento__ir' ); // phpcs:ignore ?></span></strong>
							<span class="a-bento__desc"><?php echo esc_html( $s[2] ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- A3 · Selector de formato: lista de usos, foto cenital del formato a sangre y ficha solapada con la cota 3 / 16 mm -->
<section class="a-selector tono-marino seccion" id="formato" aria-labelledby="a-selector-t" data-selector data-formato="<?php echo esc_attr( $uso0[1] ); ?>">
	<div class="envoltura a-selector__in">
		<div class="a-selector__lado">
			<?php echo dipt_titulo( 'h2', $c['selector']['titulo'], 't-h2', 'a-selector-t', 'cortina' ); // phpcs:ignore ?>
			<p class="a-selector__pregunta" id="a-selector-p"><?php echo esc_html( $c['selector']['pregunta'] ); ?></p>
			<div class="a-selector__usos" role="radiogroup" aria-labelledby="a-selector-p">
				<?php foreach ( $c['selector']['usos'] as $i => $u ) : ?>
					<button type="button" role="radio" aria-checked="<?php echo 0 === $i ? 'true' : 'false'; ?>" data-uso="<?php echo (int) $i; ?>" data-f="<?php echo esc_attr( $u[1] ); ?>"><span class="dato" aria-hidden="true">0<?php echo (int) $i + 1; ?></span><span class="a-selector__uso"><?php echo esc_html( $u[0] ); ?></span><span class="dato a-selector__f" aria-hidden="true"><?php echo '3mm' === $u[1] ? '3 mm' : '16 mm'; ?></span></button>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="a-selector__escena">
			<div class="a-selector__foto foto--sangre foto--sangre-der" aria-hidden="true">
				<?php echo dipt_foto( 'cenital-3mm', '', array( 'sizes' => '(max-width: 900px) 100vw, 56vw', 'clase' => '3mm' === $uso0[1] ? 'es-activa' : '', 'atributos' => array( 'data-foto-f' => '3mm' ) ) ); // phpcs:ignore ?>
				<?php echo dipt_foto( 'cenital-16mm', '', array( 'sizes' => '(max-width: 900px) 100vw, 56vw', 'clase' => '16mm' === $uso0[1] ? 'es-activa' : '', 'atributos' => array( 'data-foto-f' => '16mm' ) ) ); // phpcs:ignore ?>
			</div>
			<div class="a-selector__ficha">
				<div class="a-selector__resultado" aria-live="polite">
					<?php foreach ( $c['selector']['usos'] as $i => $u ) : ?>
						<div class="a-selector__panel" data-panel="<?php echo (int) $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
							<p class="a-selector__formato"><?php echo dipt_icono( '3mm' === $u[1] ? 'pellet' : 'nugget' ); // phpcs:ignore ?><span><?php echo esc_html( $c['selector']['formatos'][ $u[1] ] ); ?></span></p>
							<p class="a-selector__texto"><?php echo esc_html( $u[2] ); ?></p>
							<?php echo dipt_enlace( $c['selector']['ver'] . ': ' . $u[0], dipt_url( $u[3] ) ); // phpcs:ignore ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="a-selector__cotas" aria-hidden="true">
					<span class="cota a-cota a-cota--3mm"><span class="cota__linea"></span><span class="cota__valor">3 mm</span></span>
					<span class="cota a-cota a-cota--16mm"><span class="cota__linea"></span><span class="cota__valor">16 mm</span></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- A4 · Otros usos: hoja de contactos (alturas distintas, numeración en mono, nada en bucle). En móvil, carril con scroll-snap -->
<section class="a-otros tono-blanco seccion" aria-labelledby="a-otros-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['otros']['titulo'], 't-h2', 'a-otros-t', 'revela' ); // phpcs:ignore ?>
		<ol class="a-contactos" tabindex="0" aria-labelledby="a-otros-t">
			<?php foreach ( $c['otros']['lista'] as $i => $o ) : ?>
				<li class="a-contactos__item" data-revela data-activa style="--i:<?php echo (int) $i; ?>">
					<div class="a-contactos__foto foto--lamina"><?php echo dipt_foto( $o[2], '', array( 'sizes' => '(max-width: 900px) 68vw, 20vw' ) ); // phpcs:ignore ?></div>
					<span class="dato a-contactos__n" aria-hidden="true">0<?php echo (int) $i + 1; ?></span>
					<strong><?php echo esc_html( $o[0] ); ?></strong>
					<span class="a-contactos__desc"><?php echo esc_html( $o[1] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
		<p class="a-otros__enlace"><?php echo dipt_enlace( $c['otros']['indunova'], 'https://indunova.es/', array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<?php
// Cierre común: noche, cajas-oscuro, niebla que sube, fecha real de entrega, compra y enlace a seguridad
dipt_cierre( array(
	'titulo' => $fin['titulo'] ?? '',
	'id'     => 'a-cierre-t',
	'foto'   => 'cajas-oscuro',
) );
get_footer();
