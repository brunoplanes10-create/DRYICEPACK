<?php
/**
 * Zonas de entrega: la página nacional (10/11/2026) sobre el índice del rediseño del 13/10/2026 (plan 2.11).
 * Mapa radial del área de Mataró a sangre con barrido de radar · toda la península por comunidad autónoma (cada provincia
 * con su página y, si están activadas, sus ciudades) · cerca de Mataró por comarca · preguntas · cierre común con
 * «¿No ves tu ciudad?» (sin envío a Baleares, Canarias, Ceuta ni Melilla).
 * Tonos: hero noche · península papel · comarcas blanco · preguntas escarcha · cierre noche · pie marino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c  = dipt_contenido( 'zonas' );
$ci = $c['indice'];
$grupos = array();
foreach ( dipt_zonas() as $k => $z ) {
	if ( ! dipt_traducida( 'zona-' . $k, dipt_idioma() ) ) continue;
	$grupos[ $z['comarca'] ][ $k ] = $z;
}
ksort( $grupos );
// Península: comunidad autónoma → provincias con página (en catalán y en inglés, la de castellano si no hay otra) → ciudades
$zs_ccaa = array();
if ( function_exists( 'dipt_provincias' ) ) {
	foreach ( dipt_provincias() as $k => $p ) {
		$kp = dipt_lugar_pagina( $p );
		if ( ! $kp ) continue;
		$ciudades = array_filter( dipt_ciudades(), static fn( $o ) => $o['provincia_clave'] === $k && dipt_lugar_pagina( $o ) );
		uasort( $ciudades, static fn( $a, $b ) => $b['poblacion'] <=> $a['poblacion'] ?: strcmp( remove_accents( $a['nombre'] ), remove_accents( $b['nombre'] ) ) );
		$zs_ccaa[ dipt_lugar_ccaa( $p['ccaa'] ) ][ $k ] = array( $p, $kp, $ciudades );
	}
	uksort( $zs_ccaa, static fn( $a, $b ) => strcmp( dipt_lugar_normal( $a ), dipt_lugar_normal( $b ) ) );
	foreach ( $zs_ccaa as &$zs_prov ) uasort( $zs_prov, static fn( $a, $b ) => strcmp( dipt_lugar_normal( $a[0]['provincia_corta'] ), dipt_lugar_normal( $b[0]['provincia_corta'] ) ) );
	unset( $zs_prov );
}
// Preguntas reales de quien busca cerca de casa («dónde comprar hielo seco cerca de mí»)
$zs_faq = (array) ( $ci['faq'] ?? array() );
dipt_registrar_faq( $zs_faq );
// Cifras con su unidad sin partirse en dos líneas («3 kg», «35 €»): espacio de no separación
$zs_ns = static fn( $s ) => preg_replace( '/(\d) (€|kg|km|min)(?=[\s.,;:)]|$)/u', "\$1\u{00A0}\$2", (string) $s );
// Cierre: la primera frase de 'resto' («¿No ves tu zona?») como titular y el resto como texto (los dos ya revisados)
$zs_resto = preg_split( '/(?<=\?)\s+/u', (string) $ci['resto'], 2 );
$GLOBALS['dipt_hero_oscuro'] = true;
get_header();
?>
<section class="zs-hero tono-oscuro" aria-labelledby="zs-hero-t">
	<div class="envoltura zs-hero__in">
		<div class="zs-hero__texto">
			<?php echo dipt_migas( array( array( $ci['migas'][0], dipt_url( 'inicio' ) ), array( $ci['migas'][1], dipt_url( 'zonas' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero" id="zs-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $ci['titulo'] ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $zs_ns( $ci['texto'] ) ); ?></p>
		</div>
		<div class="zs-hero__mapa foto--sangre-der"><?php echo dipt_mapa_radial( array(), true, array( 'barrido' => true ) ); // phpcs:ignore ?></div>
	</div>
</section>

<?php if ( $zs_ccaa ) : ?>
<section class="zs-lista tono-papel seccion seccion--densa" aria-labelledby="zs-peninsula-t">
	<div class="envoltura">
		<h2 class="t-h3" id="zs-peninsula-t"><?php echo esc_html( $ci['provincias'] ?? '' ); ?></h2>
		<div class="zs-comarcas" data-activa-grupo>
			<?php $gi = 0; foreach ( $zs_ccaa as $ccaa => $provincias ) : ?>
				<section class="zs-comarca" data-revela style="--i:<?php echo (int) min( $gi++, 6 ); ?>">
					<h3><?php echo esc_html( $ccaa ); ?></h3>
					<ul>
						<?php foreach ( $provincias as list( $p, $kp, $ciudades ) ) :
							$zs_nombre = $p['provincia_corta'] . ( dipt_lugar_mismo_nombre( $p ) ? '' : ' (' . $p['nombre'] . ')' ); ?>
							<li data-activa><a href="<?php echo esc_url( dipt_url( $kp ) ); ?>"><span><?php echo esc_html( $zs_nombre ); ?></span><span class="dato"><?php echo esc_html( $p['cp'] . 'xxx' ); ?></span></a></li>
							<?php foreach ( $ciudades as $kc => $o ) : ?>
								<li class="zs-ciudad" data-activa><a href="<?php echo esc_url( dipt_url( 'ciudad-' . $kc ) ); ?>"><span><?php echo esc_html( $o['nombre'] ); ?></span><span class="dato"><?php echo esc_html( $o['cp_entrega'] ); ?></span></a></li>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="zs-lista <?php echo $zs_ccaa ? 'tono-blanco' : 'tono-papel'; ?> seccion seccion--densa" aria-labelledby="zs-lista-t">
	<div class="envoltura">
		<h2 class="t-h3" id="zs-lista-t"><?php echo esc_html( $ci['grupos'] ); ?></h2>
		<div class="zs-comarcas" data-activa-grupo><?php /* una sola zona activa a la vez (la que cruza el centro) */ ?>
			<?php foreach ( array_values( $grupos ) as $gi => $zonas ) : $comarca = array_keys( $grupos )[ $gi ]; ?>
				<section class="zs-comarca" data-revela style="--i:<?php echo (int) $gi; ?>">
					<h3><?php echo esc_html( $comarca ); ?></h3>
					<ul>
						<?php foreach ( $zonas as $k => $z ) : ?>
							<li data-activa><a href="<?php echo esc_url( dipt_url( 'zona-' . $k ) ); ?>"><span><?php echo esc_html( ucfirst( dipt_zona_nombre( $z ) ) ); ?></span><span class="dato"><?php echo esc_html( $z['km'] . "\u{00A0}km" ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $zs_resto ) < 2 ) : // si el texto no empieza con una pregunta, se queda aquí como antes ?>
			<p class="zs-resto"><?php echo esc_html( $ci['resto'] ); ?> <?php echo dipt_enlace( $ci['envios'], dipt_url( 'envios' ) ); // phpcs:ignore ?></p>
		<?php endif; ?>
	</div>
</section>

<?php if ( $zs_faq ) : ?>
<section class="z-faq tono-escarcha seccion seccion--densa" aria-labelledby="zs-faq-t">
	<div class="envoltura z-faq__in">
		<h2 class="t-h3" id="zs-faq-t"><?php echo esc_html( dipt_t( 'faq' ) ); ?></h2>
		<div>
			<?php foreach ( $zs_faq as $i => $f ) : ?>
				<details class="z-faq__item"<?php echo 0 === $i ? ' open' : ''; ?>><summary><span><?php echo esc_html( $f[0] ); ?></span><span class="z-faq__signo" aria-hidden="true"></span></summary><p><?php echo esc_html( $zs_ns( $f[1] ) ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<?php
if ( 2 === count( $zs_resto ) ) {
	dipt_cierre( array(
		'titulo'     => $zs_resto[0],
		'texto'      => esc_html( $zs_resto[1] ),
		'foto'       => 'cajas-furgoneta',
		'recorte'    => '35% 50%',
		'secundario' => array( $ci['envios'], dipt_url( 'envios' ) ),
	) );
}
get_footer();
