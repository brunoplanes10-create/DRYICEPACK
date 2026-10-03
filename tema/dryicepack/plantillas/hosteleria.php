<?php
/**
 * Hostelería: H1 foto a sangre con título abajo · H2 doble recipiente con niebla que rebosa ·
 * H3 dos formatos en díptico · H3b cliente en placa de características · H4 un fin de semana en raíl ·
 * H5 "reglas de la casa" en carta de bar · H6 preguntas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'hosteleria' );
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-hosteleria', '100vw' );
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- H1 · Foto a sangre -->
<section class="h-hero tono-oscuro" aria-labelledby="h-hero-t">
	<div class="h-hero__foto"><?php echo dipt_foto( 'uso-hosteleria', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '100vw' ) ); // phpcs:ignore ?></div>
	<div class="envoltura h-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'hosteleria' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1" id="h-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
		<div class="h-hero__pie">
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- H2 · Doble recipiente -->
<section class="h-copa tono-papel seccion" aria-labelledby="h-copa-t">
	<div class="envoltura h-copa__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['copa']['titulo'], 't-h2', 'h-copa-t' ); // phpcs:ignore ?>
			<p class="entrada suave h-copa__texto" data-revela><?php echo esc_html( $c['copa']['texto'] ); ?></p>
			<ol class="h-copa__marcas">
				<?php foreach ( $c['copa']['marcas'] as $i => $m ) : ?>
					<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $m[0] ); ?></strong><span><?php echo esc_html( $m[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<div class="h-escena" aria-hidden="true">
			<div class="h-escena__niebla"><?php for ( $i = 0; $i < 12; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>"></i><?php endfor; ?></div>
			<svg class="h-escena__dibujo" viewBox="0 0 400 400">
				<path class="h-escena__cuenco" d="M40 210 Q40 330 200 330 Q360 330 360 210 Z"/>
				<path class="h-escena__agua" d="M52 240 Q200 262 348 240 Q336 318 200 318 Q64 318 52 240 Z"/>
				<g class="h-escena__pellets"><?php for ( $i = 0; $i < 9; $i++ ) { printf( '<circle cx="%d" cy="%d" r="5"/>', 90 + $i * 27, 270 + ( ( $i * 7 ) % 12 ) ); } ?></g>
				<path class="h-escena__copa" d="M150 90 L250 90 L215 175 L215 250 L240 262 L160 262 L185 250 L185 175 Z"/>
				<path class="h-escena__bebida" d="M160 100 L240 100 L214 160 L186 160 Z"/>
				<text x="365" y="110" class="h-escena__rotulo">1</text><text x="370" y="250" class="h-escena__rotulo">2</text><text x="20" y="190" class="h-escena__rotulo">3</text>
			</svg>
		</div>
	</div>
</section>

<!-- H3 · Díptico de formatos -->
<section class="h-formatos" aria-labelledby="h-formatos-t">
	<h2 class="visually-hidden" id="h-formatos-t"><?php echo esc_html( wp_strip_all_tags( str_replace( '|', ' ', $c['formatos']['titulo'] ) ) ); ?></h2>
	<?php foreach ( array( 'efecto' => 'cenital-3mm', 'frio' => 'nuggets-ia' ) as $clave => $foto ) : $f = $c['formatos'][ $clave ]; ?>
		<article class="h-formatos__hoja h-formatos__hoja--<?php echo esc_attr( $clave ); ?>">
			<?php echo dipt_foto( $foto, '', array( 'sizes' => '(max-width: 800px) 100vw, 50vw' ) ); // phpcs:ignore ?>
			<div class="h-formatos__texto" data-revela>
				<p class="dato"><?php echo esc_html( $f[0] ); ?></p>
				<h3 class="t-h2"><?php echo esc_html( $f[1] ); ?></h3>
				<p><?php echo esc_html( $f[2] ); ?></p>
			</div>
		</article>
	<?php endforeach; ?>
</section>

<!-- H3b · Cliente: placa de características (cliente · uso) -->
<section class="sc-cliente tono-papel" aria-labelledby="h-cliente-t">
	<div class="envoltura sc-cliente__in">
		<?php echo dipt_titulo( 'h2', $c['cliente']['titulo'], 't-h2 sc-cliente__titulo', 'h-cliente-t' ); // phpcs:ignore ?>
		<dl class="sc-placa" data-revela>
			<div class="sc-placa__campo">
				<dt class="dato"><?php echo esc_html( $c['cliente']['etiquetas'][0] ); ?></dt>
				<dd><?php echo dipt_logo_cliente( $c['cliente']['id'], $c['cliente']['alt'] ); // phpcs:ignore ?><span class="sc-placa__nombre dato" aria-hidden="true"><?php echo esc_html( dipt_cliente_nombre( $c['cliente']['id'] ) ); ?></span><span class="sc-placa__detalle"><?php echo esc_html( $c['cliente']['detalle'] ); ?></span></dd>
			</div>
			<div class="sc-placa__campo">
				<dt class="dato"><?php echo esc_html( $c['cliente']['etiquetas'][1] ); ?></dt>
				<dd class="sc-placa__uso"><?php echo esc_html( $c['cliente']['uso'] ); ?></dd>
			</div>
		</dl>
	</div>
</section>

<!-- H4 · Un fin de semana en raíl -->
<section class="h-semana tono-oscuro seccion" aria-labelledby="h-semana-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['semana']['titulo'], 't-h2', 'h-semana-t' ); // phpcs:ignore ?>
		<ol class="h-rail">
			<?php foreach ( $c['semana']['pasos'] as $i => $p ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="h-rail__marca" aria-hidden="true"></span><strong><?php echo esc_html( $p[0] ); ?></strong><span><?php echo esc_html( $p[1] ); ?></span></li>
			<?php endforeach; ?>
		</ol>
		<p><?php echo dipt_enlace( $c['semana']['cuenta'], dipt_url( 'empresas' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<!-- H5 · Reglas de la casa (carta de bar) -->
<section class="h-reglas tono-escarcha seccion" aria-labelledby="h-reglas-t">
	<div class="envoltura">
		<div class="h-carta" data-revela>
			<h2 class="h-carta__titulo" id="h-reglas-t"><?php echo esc_html( $c['reglas']['titulo'] ); ?></h2>
			<p class="h-carta__sub dato">DryIcePack · −78,5 °C</p>
			<ul>
				<?php foreach ( $c['reglas']['lista'] as $r ) : ?>
					<li><span class="h-carta__plato"><?php echo esc_html( $r[0] ); ?></span><span class="h-carta__puntos" aria-hidden="true"></span><span class="h-carta__desc"><?php echo esc_html( $r[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<p class="h-carta__pie"><a href="<?php echo esc_url( dipt_url( 'seguridad' ) ); ?>"><?php echo esc_html( dipt_t( 'seguridad_mas' ) ); ?></a></p>
		</div>
	</div>
</section>

<!-- H6 · Preguntas -->
<section class="h-faq tono-blanco seccion" aria-labelledby="h-faq-t">
	<div class="envoltura h-faq__in">
		<h2 class="t-h3" id="h-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div>
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<details class="h-faq__item" data-revela style="--i:<?php echo (int) $i; ?>">
					<summary><?php echo esc_html( $f[0] ); ?><span aria-hidden="true">+</span></summary>
					<p><?php echo esc_html( $f[1] ); ?></p>
				</details>
			<?php endforeach; ?>
			<p class="h-faq__cta"><?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'producto' ) ); // phpcs:ignore ?></p>
		</div>
	</div>
</section>

<?php
get_footer();
