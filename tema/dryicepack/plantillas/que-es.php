<?php
/**
 * Qué es el hielo seco: Q1 hero con sublimación animada · Q2 ficha técnica · Q3 comparativa ·
 * Q4 formatos a tamaño real con regla · Q5 de dónde sale (proceso) · Q6 dudas en fichas deslizables · franja final.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'que-es' );
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- Q1 · Hero con sublimación -->
<section class="q-hero" aria-labelledby="q-hero-t">
	<div class="envoltura q-hero__in">
		<div>
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'que-es' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero q-hero__titulo" id="q-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada q-hero__resumen"><?php dipt_html( $c['hero']['resumen'] ); ?></p>
			<p class="suave q-hero__tambien"><?php echo esc_html( $c['hero']['tambien'] ); ?></p>
		</div>
		<div class="q-sublima" aria-hidden="true">
			<div class="q-sublima__vapor"><?php for ( $i = 0; $i < 16; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>;--x:<?php echo (int) ( ( $i * 37 ) % 100 ); ?>%"></i><?php endfor; ?></div>
			<div class="q-sublima__bloque">
				<?php
				// Montón de nuggets: filas cada vez más cortas, posiciones y giros fijos (sin aleatoriedad en cada carga)
				$n = 0;
				for ( $fila = 0; $fila < 5; $fila++ ) {
					$cuantos = 9 - 2 * $fila;
					for ( $k = 0; $k < $cuantos; $k++, $n++ ) {
						$x = 50 + ( $k - ( $cuantos - 1 ) / 2 ) * 10.5 + ( ( $n * 13 ) % 5 ) - 2;
						$y = $fila * 15 + ( ( $n * 7 ) % 4 );
						printf( '<b style="left:%.1f%%;bottom:%.1f%%;--r:%ddeg"></b>', $x, $y, ( ( $n * 53 ) % 150 ) - 75 );
					}
				}
				?>
			</div>
			<p class="q-sublima__rotulo dato"><span>sólido</span><span>→</span><span>gas</span></p>
		</div>
	</div>
</section>

<!-- Q2 · Ficha técnica -->
<section class="q-ficha tono-blanco seccion" aria-labelledby="q-ficha-t">
	<div class="envoltura">
		<div class="q-documento" data-revela>
			<header class="q-documento__cab dato"><span>DryIcePack</span><span id="q-ficha-t"><?php echo esc_html( $c['ficha']['titulo'] ); ?></span><span>CO₂</span></header>
			<dl>
				<?php foreach ( $c['ficha']['filas'] as $f ) : ?>
					<div><dt><?php echo esc_html( $f[0] ); ?></dt><dd><?php echo esc_html( $f[1] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</div>
	</div>
</section>

<!-- Q3 · Comparativa -->
<section class="q-comparar tono-papel seccion" aria-labelledby="q-comparar-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['comparar']['titulo'], 't-h2 q-comparar__titulo', 'q-comparar-t' ); // phpcs:ignore ?>
		<div class="q-tabla-scroll" data-revela>
			<table class="q-tabla">
				<thead><tr><td></td><?php foreach ( $c['comparar']['columnas'] as $i => $col ) : ?><th scope="col"<?php echo 0 === $i ? ' class="q-tabla__destacada"' : ''; ?>><?php echo esc_html( $col ); ?></th><?php endforeach; ?></tr></thead>
				<tbody>
					<?php foreach ( $c['comparar']['filas'] as $fila ) : ?>
						<tr><th scope="row"><?php echo esc_html( $fila[0] ); ?></th><td class="q-tabla__destacada"><?php echo esc_html( $fila[1] ); ?></td><td><?php echo esc_html( $fila[2] ); ?></td><td><?php echo esc_html( $fila[3] ); ?></td></tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="dato suave q-comparar__nota"><?php echo esc_html( $c['comparar']['nota'] ); ?></p>
	</div>
</section>

<!-- Q4 · A tamaño real -->
<section class="q-tamano tono-oscuro seccion" aria-labelledby="q-tamano-t">
	<div class="envoltura q-tamano__in">
		<div>
			<h2 class="t-h2" id="q-tamano-t" data-revela><?php dipt_html( $c['tamano']['titulo'] ); ?></h2>
			<p class="suave" data-revela><?php echo esc_html( $c['tamano']['texto'] ); ?></p>
		</div>
		<div class="q-regla">
			<?php foreach ( array( '3mm', '16mm' ) as $f ) : $t = $c['tamano'][ $f ]; ?>
				<figure class="q-regla__pieza q-regla__pieza--<?php echo esc_attr( $f ); ?>" data-revela>
					<div class="q-regla__muestra" aria-hidden="true"><span class="q-regla__forma"></span></div>
					<figcaption><strong><?php echo esc_html( $t[0] ); ?></strong><span class="dato"><?php echo esc_html( $t[1] ); ?></span><span><?php echo esc_html( $t[2] ); ?></span></figcaption>
				</figure>
			<?php endforeach; ?>
			<div class="q-regla__escala" aria-hidden="true"><?php for ( $i = 0; $i <= 40; $i++ ) : ?><i class="<?php echo 0 === $i % 10 ? 'cm' : ( 0 === $i % 5 ? 'medio' : '' ); ?>"><?php echo 0 === $i % 10 ? '<span>' . (int) ( $i / 10 ) . '</span>' : ''; ?></i><?php endfor; ?></div>
		</div>
	</div>
</section>

<!-- Q5 · De dónde sale -->
<section class="q-origen tono-escarcha seccion" aria-labelledby="q-origen-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['origen']['titulo'], 't-h2', 'q-origen-t' ); // phpcs:ignore ?>
		<ol class="q-proceso">
			<?php $iconos = array( 'fabrica', 'probeta', 'niebla', 'pellet' ); foreach ( $c['origen']['pasos'] as $i => $p ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="q-proceso__icono" aria-hidden="true"><?php echo dipt_icono( $iconos[ $i ] ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $p[0] ); ?></h3>
					<p><?php echo esc_html( $p[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<p class="q-origen__nota"><?php echo esc_html( $c['origen']['nota'] ); ?></p>
	</div>
</section>

<!-- Q6 · Dudas en fichas deslizables -->
<section class="q-faq tono-blanco seccion" aria-labelledby="q-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="q-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
	</div>
	<ul class="q-fichas" tabindex="0" aria-labelledby="q-faq-t">
		<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
			<li class="q-fichas__ficha" data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></li>
		<?php endforeach; ?>
	</ul>
</section>

<!-- Franja final -->
<section class="q-final tono-marino" aria-label="<?php echo esc_attr( $c['final']['titulo'] ); ?>">
	<div class="envoltura q-final__in">
		<p class="t-h3"><?php echo esc_html( $c['final']['titulo'] ); ?></p>
		<p class="q-final__enlaces"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
		<?php echo dipt_boton( $c['final']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
	</div>
</section>

<?php
get_footer();
