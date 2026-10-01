<?php
/**
 * Zonas de entrega (índice): mapa radial con todas las zonas y lista por comarca.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'zonas' );
$ci = $c['indice'];
$grupos = array();
foreach ( dipt_zonas() as $k => $z ) {
	if ( ! dipt_traducida( 'zona-' . $k, dipt_idioma() ) ) continue;
	$grupos[ $z['comarca'] ][ $k ] = $z;
}
ksort( $grupos );
$GLOBALS['dipt_hero_oscuro'] = true;
get_header();
?>
<section class="zs-hero tono-oscuro" aria-labelledby="zs-hero-t">
	<div class="envoltura zs-hero__in">
		<div>
			<?php echo dipt_migas( array( array( $ci['migas'][0], dipt_url( 'inicio' ) ), array( $ci['migas'][1], dipt_url( 'zonas' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero" id="zs-hero-t"><?php dipt_html( str_replace( '|', '<br>', $ci['titulo'] ) ); ?></h1>
			<p class="entrada"><?php echo esc_html( $ci['texto'] ); ?></p>
		</div>
		<div class="zs-hero__mapa"><?php echo dipt_mapa_radial( array(), true ); // phpcs:ignore ?></div>
	</div>
</section>

<section class="zs-lista tono-papel seccion" aria-labelledby="zs-lista-t">
	<div class="envoltura">
		<h2 class="t-h3" id="zs-lista-t"><?php echo esc_html( $ci['grupos'] ); ?></h2>
		<div class="zs-comarcas">
			<?php foreach ( $grupos as $comarca => $zonas ) : ?>
				<section class="zs-comarca" data-revela>
					<h3><?php echo esc_html( $comarca ); ?></h3>
					<ul>
						<?php foreach ( $zonas as $k => $z ) : ?>
							<li><a href="<?php echo esc_url( dipt_url( 'zona-' . $k ) ); ?>"><span><?php echo esc_html( ucfirst( dipt_zona_nombre( $z ) ) ); ?></span><span class="dato"><?php echo esc_html( $z['km'] . ' km' ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>
		<p class="zs-resto"><?php echo esc_html( $ci['resto'] ); ?> <?php echo dipt_enlace( $ci['envios'], dipt_url( 'envios' ) ); // phpcs:ignore ?></p>
	</div>
</section>
<?php
get_footer();
