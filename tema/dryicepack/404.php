<?php
/**
 * 404 · "Esta página se ha sublimado"
 */
if ( ! defined( 'ABSPATH' ) ) exit;
status_header( 404 );
get_header();
?>
<section class="error404-escena sobre-oscuro" aria-labelledby="e404-titulo">
	<?php echo dipt_niebla( '', 2 ); // phpcs:ignore ?>
	<div class="contenedor error404-escena__in">
		<div class="error404-cifra" aria-hidden="true">
			<span>4</span><span class="error404-pellet"><i></i><i></i><i></i></span><span>4</span>
		</div>
		<p class="antetitulo">Error 404</p>
		<h1 id="e404-titulo">Esta página se ha sublimado</h1>
		<p class="error404-texto">Como el hielo seco: pasó de sólido a gas sin dejar rastro. Seguramente el enlace ha cambiado. Desde aquí llegas a lo que buscabas:</p>
		<div class="botonera">
			<?php echo dipt_boton( 'Comprar hielo seco', dipt_producto_url(), 'senal' ); // phpcs:ignore ?>
			<?php echo dipt_boton( 'Ir al inicio', home_url( '/' ), 'linea-clara' ); // phpcs:ignore ?>
		</div>
		<nav class="error404-enlaces" aria-label="Páginas principales">
			<a href="<?php echo esc_url( home_url( '/que-es-el-hielo-seco/' ) ); ?>">Qué es el hielo seco</a>
			<a href="<?php echo esc_url( home_url( '/aplicaciones-del-hielo-seco/' ) ); ?>">Aplicaciones</a>
			<a href="<?php echo esc_url( home_url( '/envios-y-plazos/' ) ); ?>">Envíos y plazos</a>
			<a href="<?php echo esc_url( home_url( '/programar-suministro-de-hielo-seco/' ) ); ?>">Suministro programado</a>
			<a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contacto</a>
		</nav>
	</div>
</section>
<?php
get_footer();
