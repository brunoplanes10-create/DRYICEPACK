<?php
/**
 * Mapa de la península (Natural Earth 1:50m, sin Baleares) con rutas que se dibujan desde Mataró.
 * Las rutas son arcos: no representan carreteras, solo el destino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$dipt_m = json_decode( (string) file_get_contents( DIPT_DIR . '/partes/mapa-datos.json' ), true );
if ( ! $dipt_m ) return;
$dipt_o     = $dipt_m['ciudades']['mataro'];
$dipt_nombres = array( 'madrid' => 'Madrid', 'valencia' => 'València', 'sevilla' => 'Sevilla', 'bilbao' => 'Bilbao', 'zaragoza' => 'Zaragoza', 'coruna' => 'A Coruña', 'malaga' => 'Málaga', 'murcia' => 'Murcia', 'valladolid' => 'Valladolid' );
$dipt_titulo = $args['titulo'] ?? '';
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
