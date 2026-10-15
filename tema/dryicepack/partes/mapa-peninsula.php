<?php
/**
 * Mapa de la península (Natural Earth 1:50m, sin Baleares) con rutas que se dibujan desde Mataró.
 * Las rutas son arcos: no representan carreteras, solo el destino.
 *
 * Variante 'cobertura' (Envíos, V5 · rediseño del 14/10/2026): mapa estático, sin rutas. Mataró marcado, las ciudades
 * aparecen por orden de distancia (--d de 0 a 1) y los destinos adonde no enviamos (Baleares, Canarias en recuadro,
 * Ceuta y Melilla) van tramados. Las islas son contornos simplificados a mano (lon/lat proyectadas con la misma
 * escala que el mapa): orientan, no son cartografía.
 * $args: titulo (aria-label) · variante ('cobertura') · islas (nombres: baleares, canarias, ceuta, melilla) · no (rótulo)
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$dipt_m = json_decode( (string) file_get_contents( DIPT_DIR . '/partes/mapa-datos.json' ), true );
if ( ! $dipt_m ) return;
$dipt_o     = $dipt_m['ciudades']['mataro'];
$dipt_nombres = array( 'madrid' => 'Madrid', 'valencia' => 'València', 'sevilla' => 'Sevilla', 'bilbao' => 'Bilbao', 'zaragoza' => 'Zaragoza', 'coruna' => 'A Coruña', 'malaga' => 'Málaga', 'murcia' => 'Murcia', 'valladolid' => 'Valladolid' );
$dipt_titulo = $args['titulo'] ?? '';

if ( 'cobertura' === ( $args['variante'] ?? '' ) ) :
	$dipt_islas = wp_parse_args( (array) ( $args['islas'] ?? array() ), array( 'baleares' => 'Baleares', 'canarias' => 'Canarias', 'ceuta' => 'Ceuta', 'melilla' => 'Melilla' ) );
	$dipt_no    = (string) ( $args['no'] ?? '' );
	// Distancia a Mataró de cada ciudad, de 0 a 1: el orden en que aparecen
	$dipt_dist = array();
	foreach ( $dipt_nombres as $dipt_k => $dipt_n ) {
		if ( empty( $dipt_m['ciudades'][ $dipt_k ] ) ) continue;
		$dipt_dist[ $dipt_k ] = hypot( $dipt_m['ciudades'][ $dipt_k ][0] - $dipt_o[0], $dipt_m['ciudades'][ $dipt_k ][1] - $dipt_o[1] );
	}
	$dipt_max = $dipt_dist ? max( $dipt_dist ) : 1;
	?>
<svg class="mapa-peninsula mapa-peninsula--cobertura" viewBox="0 0 1090 890" role="img" aria-label="<?php echo esc_attr( $dipt_titulo ); ?>">
	<defs>
		<pattern id="mapa-trama" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0V7" stroke="currentColor" stroke-width="2.4"/></pattern>
	</defs>
	<path class="mapa-peninsula__pt" d="<?php echo esc_attr( $dipt_m['portugal'] ); ?>"/>
	<path class="mapa-peninsula__es" d="<?php echo esc_attr( $dipt_m['espana'] ); ?>"/>
	<g class="mapa-peninsula__no">
		<path d="M919.3 433.7L930.8 422.6L945.4 411.6L965.4 399.5L985.4 395.5L977 405.6L980.8 411.6L978.5 415.6L992.4 416.6L1006.2 420.6L998.5 435.7L990.1 449.7L973.1 464.8L963.9 455.8L953.1 455.8L948.5 439.7L938.5 439.7L933.1 444.7L923.1 439.7ZM1032.4 391.5L1040.8 385.5L1052.4 384.5L1066.2 394.5L1070.8 404.6L1060.1 409.6L1046.2 402.6L1033.9 399.5ZM832.3 494.9L838.5 483.9L850 482.9L861.6 483.9L863.1 493.9L855.4 504L846.2 507L833.9 504ZM844.7 520L851.6 522L860 525L857.7 528.1L848.5 526.1L844.7 524Z"/>
		<path d="M28.5 768.1L34 769.2L34.9 780.8L30.2 789.7L25.6 778.6ZM17.1 828.9L25.6 825L28.5 830.5L21.3 833.8ZM51.8 806.8L57.3 804L61.5 809.5L55.2 814ZM69.2 794.6L89 783L103 783.6L86.9 798.5L84.8 814L73.4 808.4ZM116.5 809.5L132.6 805.7L134.7 818.9L125.9 829.4L116.5 823.4ZM191.4 774.2L200.3 775.3L197.8 793L184.2 804L171.6 811.2L180 812.3L190.6 794.1ZM199.1 748.8L217.2 747.7L216 762.6L201.2 768.1L198.2 761Z"/>
		<circle cx="329.2" cy="806" r="6"/>
		<circle cx="512.3" cy="864.4" r="6"/>
	</g>
	<rect class="mapa-peninsula__recuadro" x="6" y="732" width="232" height="112" rx="2"/>
	<g class="mapa-peninsula__rotulos-no">
		<text x="1084" y="482" text-anchor="end"><?php echo esc_html( $dipt_islas['baleares'] ); ?></text>
		<text x="14" y="870"><?php echo esc_html( $dipt_islas['canarias'] ); ?></text>
		<text x="343" y="822"><?php echo esc_html( $dipt_islas['ceuta'] ); ?></text>
		<text x="526" y="880"><?php echo esc_html( $dipt_islas['melilla'] ); ?></text>
	</g>
	<?php if ( '' !== $dipt_no ) : ?>
	<g class="mapa-peninsula__leyenda">
		<rect x="760" y="846" width="26" height="26"/>
		<text x="798" y="866"><?php echo esc_html( $dipt_no ); ?></text>
	</g>
	<?php endif; ?>
	<g class="mapa-peninsula__ciudades">
		<?php foreach ( $dipt_dist as $dipt_k => $dipt_d ) : list( $x, $y ) = $dipt_m['ciudades'][ $dipt_k ]; ?>
			<g class="mapa-peninsula__ciudad" style="--d:<?php echo esc_attr( number_format( $dipt_d / $dipt_max, 2, '.', '' ) ); ?>">
				<circle cx="<?php echo esc_attr( $x ); ?>" cy="<?php echo esc_attr( $y ); ?>" r="6"/>
				<text x="<?php echo esc_attr( $x + 13 ); ?>" y="<?php echo esc_attr( $y + 7 ); ?>"><?php echo esc_html( $dipt_nombres[ $dipt_k ] ); ?></text>
			</g>
		<?php endforeach; ?>
	</g>
	<g class="mapa-peninsula__origen">
		<circle class="mapa-peninsula__anillo" cx="<?php echo esc_attr( $dipt_o[0] ); ?>" cy="<?php echo esc_attr( $dipt_o[1] ); ?>" r="22"/>
		<circle cx="<?php echo esc_attr( $dipt_o[0] ); ?>" cy="<?php echo esc_attr( $dipt_o[1] ); ?>" r="10"/>
		<text x="<?php echo esc_attr( $dipt_o[0] - 18 ); ?>" y="<?php echo esc_attr( $dipt_o[1] - 30 ); ?>" text-anchor="end">Mataró</text>
	</g>
</svg>
	<?php
	return;
endif;
?>
<svg class="mapa-peninsula" viewBox="0 0 <?php echo (int) $dipt_m['ancho']; ?> <?php echo (int) $dipt_m['alto']; ?>" role="img" aria-label="<?php echo esc_attr( $dipt_titulo ); ?>">
	<path class="mapa-peninsula__pt" d="<?php echo esc_attr( $dipt_m['portugal'] ); ?>"/>
	<path class="mapa-peninsula__es" d="<?php echo esc_attr( $dipt_m['espana'] ); ?>"/>
	<g class="mapa-peninsula__rutas">
		<?php
		$dipt_i = 0;
		foreach ( $dipt_nombres as $dipt_k => $dipt_n ) :
			if ( empty( $dipt_m['ciudades'][ $dipt_k ] ) ) continue;
			list( $x, $y ) = $dipt_m['ciudades'][ $dipt_k ];
			$mx = ( $dipt_o[0] + $x ) / 2;
			$my = ( $dipt_o[1] + $y ) / 2 - hypot( $x - $dipt_o[0], $y - $dipt_o[1] ) * 0.22;
			printf( '<path d="M%1$.1f %2$.1f Q%3$.1f %4$.1f %5$.1f %6$.1f" pathLength="1" style="--i:%7$d"/>', $dipt_o[0], $dipt_o[1], $mx, $my, $x, $y, $dipt_i );
			$dipt_i++;
		endforeach;
		?>
	</g>
	<g class="mapa-peninsula__ciudades">
		<?php foreach ( $dipt_nombres as $dipt_k => $dipt_n ) : if ( empty( $dipt_m['ciudades'][ $dipt_k ] ) ) continue; list( $x, $y ) = $dipt_m['ciudades'][ $dipt_k ]; ?>
			<circle cx="<?php echo esc_attr( $x ); ?>" cy="<?php echo esc_attr( $y ); ?>" r="5"/>
			<text x="<?php echo esc_attr( $x + 10 ); ?>" y="<?php echo esc_attr( $y + 5 ); ?>"><?php echo esc_html( $dipt_n ); ?></text>
		<?php endforeach; ?>
	</g>
	<g class="mapa-peninsula__origen">
		<circle cx="<?php echo esc_attr( $dipt_o[0] ); ?>" cy="<?php echo esc_attr( $dipt_o[1] ); ?>" r="9"/>
		<circle class="mapa-peninsula__anillo" cx="<?php echo esc_attr( $dipt_o[0] ); ?>" cy="<?php echo esc_attr( $dipt_o[1] ); ?>" r="20"/>
		<text x="<?php echo esc_attr( $dipt_o[0] - 16 ); ?>" y="<?php echo esc_attr( $dipt_o[1] - 26 ); ?>" text-anchor="end">Mataró</text>
	</g>
</svg>
