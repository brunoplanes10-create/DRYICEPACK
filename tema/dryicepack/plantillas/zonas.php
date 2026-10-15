<?php
/**
 * Zonas de entrega (índice, rediseño 13/10/2026, plan 2.11): mapa radial grande a sangre con barrido de radar,
 * lista por comarca y cierre común con «¿No ves tu zona?».
 * Tonos: hero noche · lista papel · cierre noche · pie marino.
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

<section class="zs-lista tono-papel seccion seccion--densa" aria-labelledby="zs-lista-t">
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
