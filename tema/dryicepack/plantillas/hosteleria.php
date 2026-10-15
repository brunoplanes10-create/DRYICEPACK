<?php
/**
 * Hostelería · rediseño del 13/10/2026 (plan 2.8). Tonos: H1 noche (foto) · H2 papel · H3 noche · placa (noche → papel) ·
 * H4 papel · H5 escarcha · H6 marino · cierre noche · pie marino.
 * H1 foto a sangre con título abajo (sin cambios) · H2 doble recipiente en corte técnico (lenguaje de L2): cada paso de la
 * lista enciende su pieza (data-activa) y la niebla rebosa (firma de la página) · H3 díptico 7/5 con la cifra «3 → 16 mm»
 * en contorno sobre la junta · placa de cliente a caballo · H4 un fin de semana: reparto-furgoneta con paralaje y panel noche
 * solapado · H5 «reglas de la casa» en carta de bar (sin cambios) · H6 preguntas (dipt_faq) · cierre común (caja-3mm).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'hosteleria' );
$fin = $c['final'] ?? array();
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-hosteleria', '100vw' );

/*
 * H2 · Corte del doble recipiente (dibujo propio, 520 × 440). Recipiente exterior de x 96–424 con pared de 8, agua desde
 * y 318 y pellets de 3 mm en el fondo; la copa se apoya en el fondo y su boca queda por encima del borde (y 200).
 * Los pellets salen de una fila con desplazamiento pseudoaleatorio fijo (el dibujo es siempre el mismo) y dejan libre el pie de la copa.
 */
$hc_pellets = '';
$hc_s       = 2026;
$hc_r       = static function () use ( &$hc_s ) {
	$hc_s = ( $hc_s * 16807 ) % 2147483647;
	return $hc_s / 2147483647;
};
for ( $hc_f = 0; $hc_f < 2; $hc_f++ ) {
	for ( $hc_x = 118 + $hc_f * 5; $hc_x < 404; $hc_x += 9.5 ) {
		if ( $hc_x > 214 && $hc_x < 306 ) continue; // pie de la copa
		$hc_pellets .= sprintf( '<circle cx="%.1f" cy="%.1f" r="3.4"/>', $hc_x + ( $hc_r() - 0.5 ) * 3, 376 - $hc_f * 6.5 + ( $hc_r() - 0.5 ) * 2.5 );
	}
}
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

<!-- H2 · Doble recipiente: corte técnico; cada paso de la lista enciende su pieza del dibujo -->
<section class="h-copa tono-papel seccion" aria-labelledby="h-copa-t">
	<div class="envoltura h-copa__in" data-activa-grupo>
		<div class="h-copa__texto">
			<?php echo dipt_titulo( 'h2', $c['copa']['titulo'], 't-h2', 'h-copa-t', 'cortina' ); // phpcs:ignore ?>
			<p class="entrada suave h-copa__intro" data-revela><?php echo esc_html( $c['copa']['texto'] ); ?></p>
			<ol class="h-copa__marcas">
				<?php foreach ( $c['copa']['marcas'] as $i => $m ) : ?>
					<li data-activa data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $m[0] ); ?></strong><span><?php echo esc_html( $m[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<figure class="h-corte" aria-hidden="true">
			<div class="h-corte__lienzo"><?php // Lienzo del tamaño exacto del dibujo: la niebla se coloca en las coordenadas del recipiente ?>
			<svg class="h-corte__svg" viewBox="0 0 520 440" width="520" height="440" focusable="false">
				<path class="hc-suelo" d="M24 392H496"/>
				<!-- 02 · Recipiente exterior: pared, agua y pellets de 3 mm -->
				<g class="hc-pieza hc-p2">
					<path class="hc-agua" d="M104 318H416V358Q416 384 390 384H130Q104 384 104 358Z"/>
					<path class="hc-superficie" d="M104 318H416"/>
					<g class="hc-pellets"><?php echo $hc_pellets; // phpcs:ignore ?></g>
					<path class="hc-pared" d="M96 200V360Q96 392 128 392H392Q424 392 424 360V200H416V358Q416 384 390 384H130Q104 384 104 358V200Z"/>
				</g>
				<!-- 01 · La copa con la bebida: se apoya en el fondo y la boca queda por encima del borde -->
				<g class="hc-pieza hc-p1">
					<path class="hc-copa" d="M196 128H324L270 250V374L296 382V384H224V382L250 374V250Z"/>
					<path class="hc-bebida" d="M207 140H313L283 208H237Z"/>
				</g>
				<!-- 03 · La niebla rebosa y cae por fuera -->
				<g class="hc-pieza hc-p3">
					<path class="hc-caida" d="M98 194C80 194 70 204 68 224V300M422 194C440 194 450 204 452 224V300"/>
					<path class="hc-flecha" d="M62 294l6 8 6-8M446 294l6 8 6-8"/>
				</g>
				<!-- Cotas: halo oscuro, guía fina, punto y número en mono (como el corte de Laboratorios) -->
				<path class="hc-halo" d="M300 160L336 96H462M160 370L96 424H40M68 250V96H44"/>
				<path class="hc-guia" d="M300 160L336 96H462M160 370L96 424H40M68 250V96H44"/>
				<circle class="hc-punto hc-punto--1" cx="300" cy="160" r="3.5"/>
				<circle class="hc-punto hc-punto--2" cx="160" cy="370" r="3.5"/>
				<circle class="hc-punto hc-punto--3" cx="68" cy="250" r="3.5"/>
				<text class="hc-cota hc-cota--1" x="468" y="101">01</text>
				<text class="hc-cota hc-cota--2" x="34" y="429" text-anchor="end">02</text>
				<text class="hc-cota hc-cota--3" x="38" y="101" text-anchor="end">03</text>
				<text class="hc-cota hc-cota--mm" x="106" y="438">3 mm</text><?php // bajo el codo de la guía: en móvil (cota a 22 unidades) no se pega al «02» ?>
			</svg>
			<!-- Niebla que rebosa (firma de la página): 16 volutas que nacen dentro del recipiente, asoman por los dos bordes (pares a
			     la izquierda, impares a la derecha; --k = cuánto se separan de la pared) y caen por fuera. Se pausa fuera de pantalla. -->
			<div class="h-corte__niebla" data-pausa><?php for ( $i = 0; $i < 16; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>;--k:<?php echo (int) floor( $i / 2 ); ?>;--lado:<?php echo $i % 2 ? 1 : -1; ?>"></i><?php endfor; ?></div>
			</div>
		</figure>
	</div>
</section>

<!-- H3 · Díptico de formatos: 7/5, fotos al 100 %, la derecha más abajo y la cifra en contorno sobre la junta -->
<section class="h-formatos tono-oscuro seccion" aria-labelledby="h-formatos-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['formatos']['titulo'], 't-h2 h-formatos__titulo', 'h-formatos-t', 'revela' ); // phpcs:ignore ?>
		<div class="h-diptico">
			<?php
			$hd_fotos = array( 'efecto' => 'cenital-3mm', 'frio' => 'cenital-16mm' );
			foreach ( $hd_fotos as $clave => $foto ) :
				$f = $c['formatos'][ $clave ];
				?>
				<article class="h-diptico__hoja h-diptico__hoja--<?php echo esc_attr( $clave ); ?>">
					<div class="h-diptico__foto foto--sangre foto--sangre-<?php echo 'efecto' === $clave ? 'izq' : 'der'; ?>" data-cortina>
						<?php echo dipt_foto( $foto, '', array( 'sizes' => 'efecto' === $clave ? '(max-width: 800px) 100vw, 60vw' : '(max-width: 800px) 100vw, 44vw' ) ); // phpcs:ignore ?>
						<div class="foto__franja h-diptico__franja">
							<p class="h-diptico__para"><?php echo esc_html( $f[0] ); ?></p>
							<h3 class="h-diptico__formato"><?php echo esc_html( $f[1] ); ?></h3>
							<p class="h-diptico__texto"><?php echo esc_html( $f[2] ); ?></p>
						</div>
					</div>
				</article>
				<?php if ( 'efecto' === $clave ) : ?>
					<p class="h-diptico__cifra" aria-hidden="true" data-revela="zoom">3<span class="h-diptico__a">→</span>16<span class="h-diptico__mm"> mm</span></p>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php // H3b · Cliente: placa a caballo entre el díptico (noche) y el fin de semana (papel) ?>
<?php echo dipt_placa_cliente( $c['cliente'], array( 'id' => 'h-cliente-t', 'arriba' => 'noche', 'abajo' => 'papel' ) ); // phpcs:ignore ?>

<!-- H4 · Un fin de semana: reparto-furgoneta al 58 % con paralaje y el panel noche solapado con los cuatro días -->
<section class="h-semana tono-papel seccion" aria-labelledby="h-semana-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['semana']['titulo'], 't-h2 h-semana__titulo', 'h-semana-t', 'revela' ); // phpcs:ignore ?>
		<div class="h-semana__escena">
			<div class="h-semana__foto foto--sangre foto--sangre-izq" data-cortina>
				<div class="h-semana__capa" data-paralaje="0.04"><?php echo dipt_foto( 'reparto-furgoneta', '', array( 'sizes' => '(max-width: 900px) 100vw, 62vw' ) ); // phpcs:ignore ?></div>
			</div>
			<div class="h-semana__panel tono-oscuro" data-revela>
				<ol class="h-dias">
					<?php foreach ( $c['semana']['pasos'] as $i => $p ) : ?>
						<li data-revela style="--i:<?php echo (int) $i + 2; ?>"><span class="dato h-dias__n" aria-hidden="true">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $p[0] ); ?></strong><span class="h-dias__texto"><?php echo esc_html( $p[1] ); ?></span></li>
					<?php endforeach; ?>
				</ol>
				<p class="h-semana__cuenta"><?php echo dipt_enlace( $c['semana']['cuenta'], dipt_url( 'empresas' ) ); // phpcs:ignore ?></p>
			</div>
		</div>
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
<section class="h-faq tono-marino seccion" aria-labelledby="h-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'h-faq-t' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php
// Cierre común: noche, caja-3mm, niebla que sube, fecha real de entrega (de usted), compra y enlace a seguridad
dipt_cierre( array(
	'titulo'       => $fin['titulo'] ?? '',
	'id'           => 'h-cierre-t',
	'foto'         => 'caja-3mm',
	'boton'        => $c['hero']['boton'],
	'corte_textos' => $fin['corte'] ?? array( dipt_t( 'corte_antes' ), dipt_t( 'corte_quedan' ), dipt_t( 'corte_despues' ) ),
) );
get_footer();
