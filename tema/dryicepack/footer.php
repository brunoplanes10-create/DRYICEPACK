<?php
/**
 * Pie: niebla que cae, mapa de enlaces, contacto y legales + barra de acciones fija en móvil.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$c = dipt_contacto();
$legal = array(
	'Aviso legal'  => '/wp-content/uploads/2025/10/AVISO-LEGAL-1-1.pdf',
	'Privacidad'   => '/wp-content/uploads/2025/10/POLITICA-PRIVACIDAD-2-1.pdf',
	'Cookies'      => '/wp-content/uploads/2025/10/POLITICA-DE-COOKIES-1-1.pdf',
	'Condiciones'  => '/wp-content/uploads/2025/12/Condiciones-De-Venta-Y-Devoluciones-%E2%80%93-Dry-Ice-Pack-1.pdf',
);
$hay_carrito = function_exists( 'WC' ) && WC()->cart && WC()->cart->get_cart_contents_count() > 0;
?>
</main>

<footer class="pie" data-vivo>
	<?php echo dipt_niebla( 'niebla--pie', 2 ); // phpcs:ignore ?>
	<div class="contenedor pie__in">
		<div class="pie__marca">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="pie__logo" aria-label="DryIcePack, inicio">
				<img src="<?php echo esc_url( dipt_img( 'logo-dryicepack-blanco.webp' ) ); ?>" alt="DryIcePack" width="200" height="50" loading="lazy">
			</a>
			<p class="pie__lema">Hielo seco a −78,5 °C, preparado en Mataró y en tu puerta en 24 h en toda la península.</p>
			<?php echo dipt_reloj_corte( 'reloj-corte--pie' ); // phpcs:ignore ?>
		</div>

		<nav class="pie__col" aria-label="Hielo seco">
			<p class="pie__titulo">Hielo seco</p>
			<a href="<?php echo esc_url( dipt_producto_url() ); ?>">Comprar hielo seco</a>
			<a href="<?php echo esc_url( home_url( '/que-es-el-hielo-seco/' ) ); ?>">Qué es el hielo seco</a>
			<a href="<?php echo esc_url( home_url( '/aplicaciones-del-hielo-seco/' ) ); ?>">Aplicaciones por sector</a>
			<a href="<?php echo esc_url( home_url( '/seguridad-del-hielo-seco/' ) ); ?>">Seguridad</a>
		</nav>

		<nav class="pie__col" aria-label="Servicio">
			<p class="pie__titulo">Servicio</p>
			<a href="<?php echo esc_url( home_url( '/programar-suministro-de-hielo-seco/' ) ); ?>">Suministro programado</a>
			<a href="<?php echo esc_url( home_url( '/envios-y-plazos/' ) ); ?>">Envíos y plazos</a>
			<a href="<?php echo esc_url( home_url( '/envios-y-plazos/#recogida-almacen' ) ); ?>">Recogida en Mataró</a>
			<a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>">Contacto</a>
		</nav>

		<div class="pie__col pie__contacto">
			<p class="pie__titulo">Hablemos</p>
			<a href="<?php echo esc_url( $c['telefono_href'] ); ?>"><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><?php echo esc_html( $c['telefono'] ); ?></a>
			<a href="<?php echo esc_url( $c['whatsapp_href'] ); ?>" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?>WhatsApp</a>
			<a href="mailto:<?php echo esc_attr( $c['email'] ); ?>"><?php echo dipt_icono( 'email' ); // phpcs:ignore ?><?php echo esc_html( $c['email'] ); ?></a>
			<p><?php echo dipt_icono( 'pin' ); // phpcs:ignore ?><?php echo esc_html( $c['direccion'] . ', ' . $c['cp'] . ' ' . $c['localidad'] ); ?></p>
			<p><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?><?php echo esc_html( $c['horario'] ); ?></p>
		</div>
	</div>

	<div class="contenedor pie__legal">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $c['empresa'] ); ?> · <?php echo esc_html( $c['cif'] ); ?></p>
		<nav aria-label="Legal">
			<?php foreach ( $legal as $texto => $ruta ) : ?>
				<a href="<?php echo esc_url( dipt_media( $ruta ) ); ?>" rel="noopener"><?php echo esc_html( $texto ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</footer>

<nav class="acciones-movil" aria-label="Acciones rápidas">
	<a href="<?php echo esc_url( $c['whatsapp_href'] ); ?>" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?><span>WhatsApp</span></a>
	<a href="<?php echo esc_url( $c['telefono_href'] ); ?>"><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><span>Llamar</span></a>
	<?php if ( $hay_carrito && function_exists( 'wc_get_checkout_url' ) ) : ?>
		<a class="acciones-movil__principal" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php echo dipt_icono( 'carrito' ); // phpcs:ignore ?><span>Finalizar</span></a>
	<?php else : ?>
		<a class="acciones-movil__principal" href="<?php echo esc_url( dipt_producto_url() ); ?>"><?php echo dipt_icono( 'carrito' ); // phpcs:ignore ?><span>Comprar</span></a>
	<?php endif; ?>
</nav>

<?php wp_footer(); ?>
</body>
</html>
