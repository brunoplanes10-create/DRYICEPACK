<?php
/**
 * Pie: frase de marca, enlaces, contacto y la cifra −78,5 °C como firma. WhatsApp flotante en toda la web.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$dipt_c = dipt_contacto();
if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url() ) : ?>
</main>
<footer class="pie-enfocado">
	<div class="envoltura">
		<span><?php echo esc_html( dipt_t( 'derechos' ) ); ?></span>
		<nav aria-label="Legal"><?php foreach ( dipt_t( 'legales' ) as $dipt_e ) : ?><a href="<?php echo esc_url( dipt_url( $dipt_e[0] ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $dipt_e[1] ); ?></a><?php endforeach; ?></nav>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
<?php return; endif; ?>
</main>

<footer class="pie tono-oscuro">
	<div class="envoltura">
		<div class="pie__arriba">
			<p class="pie__frase" data-revela><?php dipt_html( dipt_t( 'pie_frase' ) ); ?></p>
			<nav class="pie__col" aria-labelledby="pie-tienda">
				<h2 id="pie-tienda"><?php echo esc_html( dipt_t( 'pie_tienda' ) ); ?></h2>
				<ul>
					<?php foreach ( dipt_t( 'pie_enlaces' ) as $dipt_e ) : ?>
						<li><a href="<?php echo esc_url( dipt_url( $dipt_e[0], null, $dipt_e[2] ?? '' ) ); ?>"><?php echo esc_html( $dipt_e[1] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<nav class="pie__col" aria-labelledby="pie-servicio">
				<h2 id="pie-servicio"><?php echo esc_html( dipt_t( 'pie_servicio' ) ); ?></h2>
				<ul>
					<?php foreach ( dipt_t( 'pie_servicios' ) as $dipt_e ) : ?>
						<li><a href="<?php echo esc_url( dipt_url( $dipt_e[0], null, $dipt_e[2] ?? '' ) ); ?>"><?php echo esc_html( $dipt_e[1] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="pie__col">
				<h2><?php echo esc_html( dipt_t( 'pie_contacto' ) ); ?></h2>
				<div class="pie__contacto">
					<a class="tel-grande" href="<?php echo esc_attr( $dipt_c['telefono_href'] ); ?>"><?php echo esc_html( $dipt_c['telefono'] ); ?></a>
					<a href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener">WhatsApp <?php echo esc_html( $dipt_c['whatsapp'] ); ?></a>
					<a href="mailto:<?php echo esc_attr( $dipt_c['email'] ); ?>"><?php echo esc_html( $dipt_c['email'] ); ?></a>
					<address style="font-style:normal"><a href="<?php echo esc_url( $dipt_c['mapa'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $dipt_c['direccion'] ); ?><br><?php echo esc_html( $dipt_c['cp'] . ' ' . $dipt_c['localidad'] . ' (' . $dipt_c['provincia'] . ')' ); ?></a></address>
					<span class="suave"><?php echo esc_html( dipt_t( 'horario' ) ); ?></span>
					<?php dipt_selector_idioma(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php /* Firma gráfica, no texto: se dibuja con CSS (content) para que no cuente como texto de bajo contraste */ ?>
	<p class="pie__cifra" aria-hidden="true" data-paralaje="-0.12"><span data-cifra="−78,5"></span><span data-cifra="°C"></span></p>
	<div class="envoltura">
		<div class="pie__abajo">
			<span><?php echo esc_html( dipt_t( 'derechos' ) ); ?></span>
			<nav aria-label="Legal">
				<?php foreach ( dipt_t( 'legales' ) as $dipt_e ) : ?>
					<a href="<?php echo esc_url( dipt_url( $dipt_e[0] ) ); ?>"><?php echo esc_html( $dipt_e[1] ); ?></a>
				<?php endforeach; ?>
				<button type="button" data-dip-cookies><?php echo esc_html( dipt_t( 'config_cookies' ) ); ?></button>
			</nav>
		</div>
	</div>
</footer>

<a class="flotante-wa" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( dipt_t( 'wa_aria' ) ); ?>">
	<?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?><span><?php echo esc_html( dipt_t( 'wa_etiqueta' ) ); ?></span>
</a>
<?php wp_footer(); ?>
</body>
</html>
