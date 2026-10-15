<?php
/**
 * 404: la cifra se deshace en niebla, como el hielo seco al sublimarse. Salidas a lo que se busca más (y las guías).
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$GLOBALS['dipt_hero_oscuro'] = true;
$dipt_t404     = dipt_t( 'error404' );
$dipt_enlaces  = (array) $dipt_t404['enlaces'];
$dipt_guias_c  = dipt_contenido( 'guias' );
if ( ! in_array( 'guias', array_column( $dipt_enlaces, 0 ), true ) ) $dipt_enlaces[] = array( 'guias', $dipt_guias_c['titulo_corto'] ?? 'Guías' );
get_header();
?>
<section class="e404 tono-oscuro" aria-labelledby="e404-t">
	<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura e404__in">
		<p class="e404__cifra" aria-hidden="true" data-pausa><?php /* data-pausa: el bucle se para fuera de pantalla */ ?><span>4</span><span>0</span><span>4</span></p>
		<h1 class="t-h2" id="e404-t"><?php dipt_html( $dipt_t404['titulo'] ); ?></h1>
		<p class="entrada"><?php echo esc_html( $dipt_t404['texto'] ); ?></p>
		<ul class="e404__salidas">
			<li><?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?></li>
			<?php foreach ( $dipt_enlaces as $dipt_e ) : ?>
				<li><?php echo dipt_enlace( $dipt_e[1], dipt_url( $dipt_e[0] ) ); // phpcs:ignore ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
