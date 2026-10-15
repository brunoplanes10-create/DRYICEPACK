<?php
/**
 * Transporte en frío · rediseño del 13/10/2026 (plan 2.8). Tonos: T1 papel · T2 noche · T3 papel · placa (papel → blanco) ·
 * T4 blanco · T5 marino · T6 noche (es su cierre) · pie marino.
 * T1 título partido con foto vertical y datos (sin cambios) · T2 caja en sección al estilo del corte de L2 (trama EPS, cotas)
 * con el frío que baja en chevrons (firma de la página) y una lámina de caja-16mm-lateral · T3 tres reglas: título fijo y
 * escalera, con la regla «3 h → 24 h» que se llena · placa de cliente a caballo · T4 planificador de rutas (sin cambios:
 * consumo por semana y al mes, enteros) · T5 preguntas (dipt_faq) · T6 final: texto, cajas-furgoneta a sangre y hoja de ruta solapada.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'transporte' );
// Planificador: valores de partida (L, X, V y 20 kg por ruta). El JS recalcula con la misma regla: por semana = días × kg; al mes = por semana × 4.
$plan_dias   = array( 0, 2, 4 );
$plan_kg     = 20;
$plan_semana = count( $plan_dias ) * $plan_kg;
dipt_precargar( 'uso-transporte', '(max-width: 900px) 100vw, 45vw' );

/*
 * T2 · Corte de la caja (dibujo propio, 560 × 480; 1 unidad ≈ 1 mm): paredes de EPS de 40 mm, tapa apoyada sin cierre hermético,
 * nuggets de 16 mm arriba (y 122–172) y el producto abajo (y 244–398). Los nuggets salen de una rejilla con giro
 * pseudoaleatorio fijo (Park–Miller, semilla fija): el dibujo es siempre el mismo.
 */
$tc_hielo = '';
$tc_s     = 1608;
$tc_r     = static function () use ( &$tc_s ) {
	$tc_s = ( $tc_s * 16807 ) % 2147483647;
	return $tc_s / 2147483647;
};
for ( $tc_f = 0, $tc_y = 134; $tc_y < 172; $tc_f++, $tc_y += 15 ) {
	for ( $tc_x = 100 + ( $tc_f % 2 ? 15 : 0 ); $tc_x < 462; $tc_x += 30 ) {
		$tc_a       = (int) round( ( $tc_r() - 0.5 ) * 50 );
		$tc_hielo .= sprintf( '<rect x="-13" y="-8" width="26" height="16" rx="8" transform="translate(%.1f %.1f) rotate(%d)"/>', $tc_x + ( $tc_r() - 0.5 ) * 6, $tc_y + ( $tc_r() - 0.5 ) * 4, $tc_a );
	}
}
$tc_cajas = '';
foreach ( array( 244, 322 ) as $tc_fy ) {
	foreach ( array( 92, 222, 352 ) as $tc_fx ) {
		$tc_cajas .= sprintf( '<rect x="%d" y="%d" width="116" height="72" rx="2"/><path d="M%d %dH%d"/>', $tc_fx, $tc_fy, $tc_fx + 12, $tc_fy + 22, $tc_fx + 104 );
	}
}
get_header();
?>

<!-- T1 · Título partido con foto vertical (bloque "t-portada": .t-hero es la clase tipográfica de base.css) -->
<section class="t-portada" aria-labelledby="t-hero-t">
	<div class="envoltura t-portada__in">
		<div class="t-portada__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'transporte' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1" id="t-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
		<figure class="t-portada__foto"><?php echo dipt_foto( 'uso-transporte', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 45vw', 'recorte' => '40% 50%' ) ); // phpcs:ignore ?></figure>
	</div>
	<div class="envoltura"><ul class="t-portada__datos dato"><?php foreach ( $c['hero']['datos'] as $d ) : ?><li><?php echo esc_html( $d ); ?></li><?php endforeach; ?></ul></div>
</section>

<!-- T2 · Caja en sección: corte técnico con trama EPS, cotas y el frío que baja en chevrons -->
<section class="t-caja tono-oscuro seccion" aria-labelledby="t-caja-t">
	<div class="envoltura t-caja__in">
		<div class="t-caja__texto">
			<h2 class="t-h2" id="t-caja-t" data-revela><?php dipt_html( $c['caja']['titulo'] ); ?></h2>
			<p class="entrada" data-revela><?php echo esc_html( $c['caja']['texto'] ); ?></p>
		</div>
		<figure class="t-corte" data-revela="zoom">
			<svg class="t-corte__svg" viewBox="0 0 560 480" width="560" height="480" aria-hidden="true" focusable="false">
				<defs>
					<pattern id="tc-eps" width="34" height="34" patternUnits="userSpaceOnUse"><rect class="tc-eps-fondo" width="34" height="34"/><g class="tc-bolas"><circle cx="6" cy="5" r="3.4"/><circle cx="17" cy="6.5" r="3"/><circle cx="27.5" cy="4.5" r="3.8"/><circle cx="11" cy="15.5" r="3.6"/><circle cx="23" cy="16" r="2.8"/><circle cx="31" cy="23" r="3.2"/><circle cx="5" cy="25.5" r="3.4"/><circle cx="16" cy="27" r="3.9"/><circle cx="26" cy="29" r="2.7"/></g></pattern>
				</defs>
				<rect class="tc-int" x="80" y="110" width="400" height="290"/>
				<g class="tc-hielo"><?php echo $tc_hielo; // phpcs:ignore ?></g>
				<g class="tc-producto"><?php echo $tc_cajas; // phpcs:ignore ?></g>
				<path class="tc-eps" d="M40 110H80V400H480V110H520V440H40Z"/>
				<path class="tc-eps" d="M36 64H524V102H478V118H82V102H36Z"/>
				<!-- Cotas: halo oscuro, guía fina, punto y número en mono -->
				<path class="tc-halo" d="M300 150V30M330 360V452M500 106L540 58V30M120 206H30"/>
				<path class="tc-guia" d="M300 150V30M330 360V452M500 106L540 58V30M120 206H30M480 446V470M520 446V470M480 461H520M476 465l8-8M516 465l8-8"/>
				<circle class="tc-punto" cx="300" cy="150" r="3.5"/><circle class="tc-punto" cx="330" cy="360" r="3.5"/><circle class="tc-punto" cx="500" cy="106" r="3.5"/><circle class="tc-punto" cx="120" cy="206" r="3.5"/>
				<text class="tc-cota" x="300" y="22" text-anchor="middle">01</text>
				<text class="tc-cota" x="330" y="472" text-anchor="middle">02</text>
				<text class="tc-cota" x="540" y="22" text-anchor="middle">03</text>
				<text class="tc-cota" x="24" y="211" text-anchor="end">04</text>
				<text class="tc-cota" x="470" y="476" text-anchor="end">40 mm</text>
			</svg>
			<!-- El frío baja (firma de la página): cinco columnas de chevrons entre el hielo y el producto, en bucle de 2,4 s -->
			<div class="t-corte__frio" aria-hidden="true" data-pausa><?php for ( $i = 0; $i < 5; $i++ ) : ?><span style="--i:<?php echo (int) $i; ?>"><i></i><i></i></span><?php endfor; ?></div>
		</figure>
		<ol class="t-caja__leyenda">
			<?php foreach ( $c['caja']['marcas'] as $i => $m ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="t-caja__n dato" aria-hidden="true">0<?php echo (int) $i + 1; ?></span><?php echo esc_html( $m ); ?></li>
			<?php endforeach; ?>
		</ol>
		<div class="t-caja__lamina foto--lamina foto--4x3" data-cortina><?php echo dipt_foto( 'caja-16mm-lateral', '', array( 'sizes' => '(max-width: 900px) 60vw, 22vw', 'recorte' => '50% 55%' ) ); // phpcs:ignore ?></div>
	</div>
</section>

<!-- T3 · Tres reglas: título fijo a la izquierda y escalera a la derecha -->
<section class="t-reglas tono-papel seccion" aria-labelledby="t-reglas-t">
	<div class="envoltura t-reglas__in">
		<div class="t-reglas__lado"><?php echo dipt_titulo( 'h2', $c['reglas']['titulo'], 't-h2', 't-reglas-t', 'cortina' ); // phpcs:ignore ?></div>
		<ol class="t-escalera">
			<?php foreach ( $c['reglas']['lista'] as $i => $r ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="t-escalera__n" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
					<h3><?php echo esc_html( $r[0] ); ?></h3>
					<p><?php echo esc_html( $r[1] ); ?></p>
					<?php if ( 2 === $i ) : // Kilos según las horas: la regla se llena de 3 h a 24 h al entrar (decorativa: el texto ya lo dice) ?>
						<div class="t-regla" aria-hidden="true"><span class="dato">3 h</span><span class="t-regla__pista"><i></i></span><span class="dato">24 h</span></div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<?php // T3b · Cliente: placa a caballo entre las reglas (papel) y el planificador (blanco) ?>
<?php echo dipt_placa_cliente( $c['cliente'], array( 'id' => 't-cliente-t', 'arriba' => 'papel', 'abajo' => 'blanco', 'lado' => 'izq' ) ); // phpcs:ignore ?>

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
					<label><input type="checkbox" value="1" data-plan-dia<?php checked( in_array( $i, $plan_dias, true ) ); ?>><span><?php echo esc_html( $d ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<label class="t-plan__kg"><input type="number" min="3" max="250" step="1" value="<?php echo (int) $plan_kg; ?>" inputmode="numeric" data-plan-kg><span><?php echo esc_html( $c['plan']['kg'] ); ?></span></label>
			<dl class="t-plan__resultado">
				<div class="t-plan__fila">
					<dt><span class="dato"><?php echo esc_html( $c['plan']['semana'] ?? '' ); ?></span><small class="dato"><?php echo esc_html( $c['plan']['semana_calc'] ?? '' ); ?></small></dt>
					<dd><output data-plan-semana><?php echo (int) $plan_semana; ?></output> kg</dd>
				</div>
				<div class="t-plan__fila t-plan__fila--mes">
					<dt><span class="dato"><?php echo esc_html( $c['plan']['mes'] ); ?></span><small class="dato"><?php echo esc_html( $c['plan']['mes_calc'] ?? '' ); ?></small></dt>
					<dd><output data-plan-mes><?php echo (int) ( $plan_semana * 4 ); ?></output> kg</dd>
				</div>
			</dl>
			<p class="dato suave"><?php echo esc_html( $c['plan']['nota'] ); ?></p>
			<?php if ( ! empty( $c['plan']['lunes'] ) ) : // Los lunes no hay reparto: sábado (según zona) o recogida en Mataró ?>
				<p class="t-plan__lunes"><?php echo dipt_icono( 'calendario' ); // phpcs:ignore ?><span><?php echo esc_html( $c['plan']['lunes'] ); ?></span></p>
			<?php endif; ?>
			<?php echo dipt_boton( $c['plan']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- T5 · Preguntas (acordeón común) con el enlace a seguridad al pie -->
<section class="t-faq tono-marino seccion" aria-labelledby="t-faq-t">
	<div class="envoltura">
		<?php
		echo dipt_faq( $c['faq']['lista'], array( // phpcs:ignore
			'titulo' => $c['faq']['titulo'],
			'id'     => 't-faq-t',
			'pie'    => dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ),
		) );
		?>
	</div>
</section>

<?php if ( ! empty( $c['final'] ) ) : $f = $c['final']; ?>
<!-- T6 · Final (cierre propio): texto a la izquierda, cajas-furgoneta a sangre y la hoja de ruta solapada (la furgoneta llega al entrar) -->
<section class="t-final tono-oscuro seccion" aria-labelledby="t-final-t" data-vivo data-cierre>
	<div class="envoltura t-final__in">
		<div class="t-final__texto">
			<?php echo dipt_titulo( 'h2', $f['titulo'], 't-h2', 't-final-t', 'cortina' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $f['texto'] ); ?></p>
			<div class="t-final__acciones" data-revela style="--i:1">
				<?php echo dipt_boton( $f['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
				<?php if ( ! empty( $f['wa'] ) ) echo dipt_enlace( $f['wa'], dipt_whatsapp_url( $f['wa_mensaje'] ?? null ), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
			</div>
		</div>
		<div class="t-final__lado">
			<div class="t-final__foto foto--sangre foto--sangre-der" data-cortina><?php echo dipt_foto( 'cajas-furgoneta', '', array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?></div>
			<div class="t-final__ruta hoja" data-revela>
				<p class="t-final__carga dato" aria-hidden="true"><?php echo esc_html( $f['carga'] ?? '' ); ?></p>
				<div class="t-final__via" aria-hidden="true">
					<i class="t-final__nodo"></i>
					<span class="t-final__vehiculo"><?php echo dipt_icono( 'furgoneta' ); // phpcs:ignore ?></span>
					<i class="t-final__nodo t-final__nodo--fin"></i>
				</div>
				<ol class="t-final__paradas">
					<?php foreach ( (array) ( $f['ruta'] ?? array() ) as $parada ) : ?>
						<li><strong><?php echo esc_html( $parada[0] ); ?></strong><span class="dato"><?php echo esc_html( $parada[1] ); ?></span></li>
					<?php endforeach; ?>
				</ol>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
