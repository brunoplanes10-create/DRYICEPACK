<?php
/**
 * Laboratorios (rediseño 13/10/2026, plan 2.8): L1 foto en círculo con órbita (en móvil, círculo de 150 px a la derecha
 * y el H1 rodeándolo) · L2 corte técnico de la caja de EPS (no se toca) · L3 anatomía de un paquete con pines numerados
 * enlazados con la lista · placa de cliente a caballo · L4 para centros: foto con tres hojas de servicio en diagonal ·
 * L5 preguntas · cierre común.
 * Tonos: papel · noche · papel · (placa) · marino · blanco · noche (cierre) · pie marino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'laboratorios' );
dipt_precargar( 'uso-laboratorio', '(max-width: 900px) 150px, 38vw' );
get_header();
?>

<!-- L1 · Foto en círculo (LCP: la foto no se anima; solo gira la órbita, después de cargar) -->
<section class="l-hero tono-papel" aria-labelledby="l-hero-t">
	<div class="envoltura l-hero__in">
		<figure class="l-hero__foto" data-pausa><?php echo dipt_foto( 'uso-laboratorio', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 150px, 38vw' ) ); // phpcs:ignore ?><span class="l-hero__orbita" aria-hidden="true"></span></figure>
		<div>
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'laboratorios' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1" id="l-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php
/*
 * L2 · Corte técnico de la caja (dibujo propio, 560 × 516; 1 unidad ≈ 1 mm): paredes de EPS de 40 mm, tapa apoyada con resalte
 * y sin precinto, gradilla con 8 criotubos en el centro y hielo seco abajo, a los lados y encima.
 * Los pellets salen de una rejilla al tresbolillo con desplazamiento y giro pseudoaleatorios (Park–Miller, semilla fija):
 * el dibujo es siempre el mismo. Cavidad útil x 82–478 · y 186–398; gradilla x 175–385 · y 231–341.
 * Tipos: 1, 2 y 3 tumbados (11, 15 y 19 de largo), 4 visto de punta.
 */
$lc_hielo = '';
$lc_s     = 1845;
$lc_r     = static function () use ( &$lc_s ) {
	$lc_s = ( $lc_s * 16807 ) % 2147483647;
	return $lc_s / 2147483647;
};
$lc_mitad = array( 1 => 7, 2 => 9, 3 => 11, 4 => 4.5 ); // media longitud, con la cara elíptica
for ( $lc_f = 0, $lc_y = 191.8; $lc_y < 398; $lc_f++, $lc_y += 11.6 ) {
	for ( $lc_x = 82 + ( $lc_f % 2 ? 12.6 : 6.3 ); $lc_x < 478; $lc_x += 12.6 ) {
		$lc_q = $lc_r();
		$lc_t = $lc_q < 0.16 ? 4 : ( $lc_q < 0.45 ? 1 : ( $lc_q < 0.8 ? 2 : 3 ) );
		$lc_a = (int) floor( $lc_r() * 180 );
		if ( $lc_y > 345 && 4 !== $lc_t && $lc_r() < 0.6 ) {
			$lc_a = ( (int) floor( $lc_r() * 50 ) + 155 ) % 180; // en el fondo, más tumbados
		}
		$lc_cx = $lc_x + ( $lc_r() - 0.5 ) * 7;
		$lc_cy = $lc_y + ( $lc_r() - 0.5 ) * 6;
		$lc_rad = deg2rad( $lc_a );
		$lc_ex  = abs( $lc_mitad[ $lc_t ] * cos( $lc_rad ) ) + 4.5 * abs( sin( $lc_rad ) );
		$lc_ey  = abs( $lc_mitad[ $lc_t ] * sin( $lc_rad ) ) + 4.5 * abs( cos( $lc_rad ) );
		$lc_cx  = min( max( $lc_cx, 82 + $lc_ex ), 478 - $lc_ex );
		$lc_cy  = min( max( $lc_cy, 186 + $lc_ey ), 398 - $lc_ey );
		// Si pisa la gradilla, se empuja hacia fuera por el lado que menos penetra
		if ( $lc_cx + $lc_ex > 175 && $lc_cx - $lc_ex < 385 && $lc_cy + $lc_ey > 231 && $lc_cy - $lc_ey < 341 ) {
			$lc_p = null;
			foreach ( array( array( $lc_cx + $lc_ex - 175, -1, 0 ), array( 385 - ( $lc_cx - $lc_ex ), 1, 0 ), array( $lc_cy + $lc_ey - 231, 0, -1 ), array( 341 - ( $lc_cy - $lc_ey ), 0, 1 ) ) as $lc_o ) {
				if ( null === $lc_p || $lc_o[0] < $lc_p[0] ) {
					$lc_p = $lc_o;
				}
			}
			if ( $lc_p[0] > 8 ) {
				continue;
			}
			$lc_cx += $lc_p[1] * $lc_p[0];
			$lc_cy += $lc_p[2] * $lc_p[0];
			if ( $lc_cx - $lc_ex < 82 || $lc_cx + $lc_ex > 478 || $lc_cy - $lc_ey < 186 || $lc_cy + $lc_ey > 398 ) {
				continue;
			}
		}
		$lc_hielo .= '<use href="#lc-p' . $lc_t . '" transform="translate(' . (int) round( $lc_cx ) . ' ' . (int) round( $lc_cy ) . ')rotate(' . $lc_a . ')"/>';
	}
}
$lc_tubos = '';
for ( $i = 0; $i < 8; $i++ ) {
	$cx        = 196 + 24 * $i;
	$lc_tubos .= '<path class="lc-tubo" d="M' . ( $cx - 8 ) . ' 254V318q0 8 8 8t8-8V254Z"/>'
		. '<path class="lc-muestra" d="M' . ( $cx - 7 ) . ' 292V318q0 7 7 7t7-7V292Z"/>'
		. '<path class="lc-brillo" d="M' . ( $cx - 4.5 ) . ' 258V314"/>'
		. '<rect class="lc-tapon' . ( 1 === $i % 3 ? ' lc-tapon--b' : '' ) . '" x="' . ( $cx - 10 ) . '" y="238" width="20" height="16" rx="2"/>'
		. '<path class="lc-estria" d="M' . ( $cx - 5 ) . ' 241v10M' . $cx . ' 241v10M' . ( $cx + 5 ) . ' 241v10"/>';
}
// Cuerpo de un pellet tumbado de largo $l, centrado en el origen, con la cara del extremo a la vista
$lc_pellet = static function ( $l ) {
	$h = $l / 2;
	return '<path class="lc-cuerpo" d="M' . ( 2 - $h ) . ' -4.5H' . $h . 'A3 4.5 0 0 1 ' . $h . ' 4.5H' . ( 2 - $h ) . 'Q' . ( -$h ) . ' 4.5 ' . ( -$h ) . ' 2.5V-2.5Q' . ( -$h ) . ' -4.5 ' . ( 2 - $h ) . ' -4.5Z"/><ellipse class="lc-cara" cx="' . $h . '" rx="3" ry="4.5"/>';
};
$lc_cota = $c['caja']['cotas'] ?? array( '', '', '', '' );
?>
<!-- L2 · Corte de la caja: muestras en el centro, hielo seco alrededor -->
<section class="l-caja tono-oscuro seccion" aria-labelledby="l-caja-t">
	<div class="envoltura l-caja__in">
		<figure class="l-corte" data-revela="zoom">
			<svg class="l-corte__svg" viewBox="0 0 560 516" width="560" height="516" role="img" aria-labelledby="lc-t" focusable="false">
				<title id="lc-t"><?php echo esc_html( $c['caja']['corte_alt'] ?? '' ); ?></title>
				<defs>
					<pattern id="lc-eps" width="34" height="34" patternUnits="userSpaceOnUse"><rect class="lc-eps-fondo" width="34" height="34"/><g class="lc-bolas"><circle cx="5" cy="6" r="3.6"/><circle cx="15.5" cy="4" r="2.8"/><circle cx="26" cy="7.5" r="4.2"/><circle cx="9" cy="16" r="3"/><circle cx="20" cy="15" r="4"/><circle cx="30.5" cy="19" r="2.6"/><circle cx="4" cy="26.5" r="4"/><circle cx="14.5" cy="27" r="3.2"/><circle cx="25" cy="27.5" r="3.8"/></g></pattern>
					<g id="lc-p1"><?php echo $lc_pellet( 11 ); // phpcs:ignore ?></g>
					<g id="lc-p2"><?php echo $lc_pellet( 15 ); // phpcs:ignore ?></g>
					<g id="lc-p3"><?php echo $lc_pellet( 19 ); // phpcs:ignore ?></g>
					<g id="lc-p4"><circle class="lc-cuerpo" cx="1.4" r="4.5"/><circle class="lc-cara" r="4.5"/></g>
				</defs>
				<rect class="lc-int" x="80" y="168" width="400" height="232"/>
				<g><?php echo $lc_hielo; // phpcs:ignore ?></g>
				<g class="lc-gradilla"><rect x="180" y="270" width="200" height="6"/><rect x="180" y="326" width="200" height="10"/><rect x="180" y="270" width="6" height="66"/><rect x="374" y="270" width="6" height="66"/></g>
				<g><?php echo $lc_tubos; // phpcs:ignore ?></g>
				<path class="lc-eps" d="M40 168H80V400H480V168H520V440H40Z"/>
				<path class="lc-eps" d="M40 124H520V164H476V180H84V164H40Z"/>
				<path class="lc-gas" d="M62 166H34c-9 0-14-7-14-16v-30M498 166H526c9 0 14-7 14-16v-30"/>
				<path class="lc-flecha" d="M14 124l6-7 6 7M534 124l6-7 6 7"/>
				<path class="lc-halo" d="M64 142V58M340 246V58M446 384V482"/>
				<path class="lc-guia" d="M64 142V58M340 246V58M446 384V482M40 444V480M80 444V480M40 471H80M36 475l8-8M76 475l8-8"/>
				<circle class="lc-punto" cx="64" cy="142" r="3"/><circle class="lc-punto" cx="340" cy="246" r="3"/><circle class="lc-punto" cx="446" cy="384" r="3"/>
				<text class="lc-cota" x="540" y="104" text-anchor="middle">CO₂</text>
				<text class="lc-cota" x="40" y="46"><?php echo esc_html( $lc_cota[0] ); ?></text>
				<text class="lc-cota" x="332" y="46"><?php echo esc_html( $lc_cota[1] ); ?></text>
				<text class="lc-cota" x="40" y="502"><?php echo esc_html( $lc_cota[2] ); ?></text>
				<text class="lc-cota" x="520" y="502" text-anchor="end"><?php echo esc_html( $lc_cota[3] ); ?></text>
			</svg>
		</figure>
		<div>
			<h2 class="t-h2" id="l-caja-t" data-revela><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['caja']['titulo'] ) . '</span>' ); ?></h2>
			<p class="entrada l-caja__texto" data-revela><?php echo esc_html( $c['caja']['texto'] ); ?></p>
			<ul class="l-caja__leyenda dato"><?php foreach ( $c['caja']['leyenda'] as $i => $l ) : ?><li class="l<?php echo (int) $i + 1; ?>"><?php echo esc_html( $l ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>

<?php
// L3 · Pines sobre la caja: cada número señala la marca de la lista con el mismo número (1 UN, 2 nombre, 3 peso, 4 etiqueta de clase 9, 5 ranura de salida del gas).
// Cada pin va dentro de la pieza que señala: se queda pegado a ella con cualquier ancho y cualquier idioma.
$l_marcas = $c['etiqueta']['marcas'];
$l_pin    = static function ( $n ) {
	return '<span class="l-pin l-pin--' . (int) $n . '">' . (int) $n . '</span>';
};
?>
<!-- L3 · Anatomía de un paquete: pines numerados sobre la caja enlazados con la lista (el que cruza el centro de la pantalla se enciende) -->
<section class="l-etiqueta tono-papel seccion" aria-labelledby="l-etiqueta-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['etiqueta']['titulo'], 't-h2 l-etiqueta__titulo', 'l-etiqueta-t' ); // phpcs:ignore ?>
		<div class="l-paquete" data-activa-grupo>
			<div class="l-paquete__escena">
				<div class="l-paquete__caja" aria-hidden="true">
					<span class="l-paquete__ranura"><?php echo $l_pin( 5 ); // phpcs:ignore ?></span>
					<span class="l-paquete__un"><?php echo $l_pin( 1 ); // phpcs:ignore ?>UN 1845</span>
					<span class="l-paquete__nombre"><?php echo $l_pin( 2 ); // phpcs:ignore ?><?php echo esc_html( $l_marcas[1][0] ?? '' ); ?></span>
					<span class="l-paquete__peso"><?php echo $l_pin( 3 ); // phpcs:ignore ?><?php echo esc_html( $c['etiqueta']['peso_caja'] ?? '' ); ?></span>
					<span class="l-paquete__rombo"><?php echo $l_pin( 4 ); // phpcs:ignore ?><svg viewBox="0 0 100 100" focusable="false"><path class="l-r-borde" d="M50 3 97 50 50 97 3 50Z"/><path class="l-r-raya" d="M26 27V50M34 19V50M42 11V50M50 6V50M58 11V50M66 19V50M74 27V50"/><path class="l-r-linea" d="M8 50H92"/><text x="50" y="80" text-anchor="middle">9</text></svg></span>
				</div>
			</div>
			<ol class="l-paquete__marcas">
				<?php foreach ( $l_marcas as $i => $m ) : ?>
					<li class="m<?php echo (int) $i + 1; ?>" data-activa data-revela style="--i:<?php echo (int) $i; ?>"><strong><?php echo esc_html( $m[0] ); ?></strong><span><?php echo esc_html( $m[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<p class="l-etiqueta__aviso"><?php echo esc_html( $c['etiqueta']['aviso'] ); ?></p>
	</div>
</section>

<?php echo dipt_placa_cliente( $c['cliente'], array( 'id' => 'l-cliente-t', 'arriba' => 'papel', 'abajo' => 'marino', 'lado' => 'izq' ) ); // phpcs:ignore ?>

<!-- L4 · Para centros: la entrega en el laboratorio, con tres hojas de servicio montadas en diagonal sobre la foto -->
<section class="l-centros tono-marino seccion" aria-labelledby="l-centros-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['centros']['titulo'], 't-h2 l-centros__titulo', 'l-centros-t', 'cortina' ); // phpcs:ignore ?>
		<div class="l-centros__mesa">
			<div class="l-centros__foto foto--sangre foto--sangre-izq" data-cortina><?php echo dipt_foto( 'uso-laboratorio-entrega', '', array( 'sizes' => '(max-width: 900px) 100vw, 62vw', 'recorte' => '40% 50%' ) ); // phpcs:ignore ?></div>
			<ol class="l-hojas">
				<?php
				$ic = array( 'calendario', 'factura', 'descarga' );
				$nl = count( $c['centros']['lista'] );
				foreach ( $c['centros']['lista'] as $i => $l ) :
					?>
					<li class="hoja l-hoja l-hoja--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i * 2; ?>">
						<p class="l-hoja__cabeza" aria-hidden="true"><span class="dato"><?php echo esc_html( sprintf( '%02d / %02d', $i + 1, $nl ) ); ?></span><?php echo dipt_icono( $ic[ $i ] ?? 'check' ); // phpcs:ignore ?></p>
						<h3><?php echo esc_html( $l[0] ); ?></h3>
						<p><?php echo esc_html( $l[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<!-- L5 · Preguntas -->
<section class="l-faq seccion seccion--densa tono-blanco" aria-labelledby="l-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'l-faq-t' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php
// Cierre común: compra online con la fecha real de entrega, de usted. El enlace a seguridad va en el cierre.
dipt_cierre( array(
	'titulo'       => $c['final']['titulo'] ?? '',
	'id'           => 'l-cierre-t',
	'foto'         => 'nuggets-azul',
	'recorte'      => '45% 55%',
	'corte_textos' => $c['final']['corte'] ?? array( dipt_t( 'corte_antes' ), dipt_t( 'corte_quedan' ), dipt_t( 'corte_despues' ) ),
) );
get_footer();
