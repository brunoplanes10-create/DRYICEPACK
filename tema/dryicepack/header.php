<?php
/**
 * Cabecera: logo, usos (panel con imagen), empresas, envíos, contacto, teléfono, idioma, carrito y un solo botón (Comprar).
 * En páginas con portada oscura empieza transparente y se vuelve sólida al bajar; se esconde al bajar y vuelve al subir.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$dipt_c      = dipt_contacto();
$dipt_clave  = dipt_pagina_actual();
$dipt_oscuro = dipt_cabecera_sobre_oscuro();
$dipt_menu   = dipt_t( 'menu' );
// Checkout sin distracciones: logo, teléfono y pago seguro. Nada más.
$dipt_enfocado = function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="saltar" href="#contenido"><?php echo esc_html( dipt_t( 'saltar' ) ); ?></a>

<?php if ( $dipt_enfocado ) : ?>
<header class="cabecera cabecera--enfocada es-solida">
	<div class="envoltura cabecera__in">
		<a class="cabecera__logo" href="<?php echo esc_url( dipt_url( 'inicio' ) ); ?>" aria-label="DryIcePack, <?php echo esc_attr( dipt_t( 'inicio' ) ); ?>">
			<img class="logo-oscuro" src="<?php echo esc_url( dipt_img( 'logo-dryicepack.webp' ) ); ?>" alt="DryIcePack" width="721" height="160">
		</a>
		<p class="cabecera__seguro"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 018 0v3"/></svg><?php echo esc_html( dipt_t( 'pago_seguro' ) ); ?></p>
		<a class="cabecera__tel" href="<?php echo esc_attr( $dipt_c['telefono_href'] ); ?>"><?php echo esc_html( $dipt_c['telefono'] ); ?></a>
	</div>
</header>
<main id="contenido" class="contenido">
<?php return; endif; ?>
<header class="cabecera<?php echo $dipt_oscuro ? ' cabecera--sobre-oscuro' : ''; ?>" data-cabecera>
	<div class="envoltura cabecera__in">
		<a class="cabecera__logo" href="<?php echo esc_url( dipt_url( 'inicio' ) ); ?>" aria-label="DryIcePack, <?php echo esc_attr( dipt_t( 'inicio' ) ); ?>">
			<img class="logo-oscuro" src="<?php echo esc_url( dipt_img( 'logo-dryicepack.webp' ) ); ?>" alt="DryIcePack" width="721" height="160">
			<img class="logo-claro" src="<?php echo esc_url( dipt_img( 'logo-dryicepack-blanco.webp' ) ); ?>" alt="" width="400" height="99">
		</a>

		<nav class="menu" id="menu-principal" aria-label="Principal">
			<div class="menu__item" data-submenu>
				<button class="menu__boton-sub" type="button" aria-expanded="false" aria-controls="submenu-usos"><?php echo esc_html( $dipt_menu['usos'] ); ?><?php echo dipt_icono( 'flecha-ab' ); // phpcs:ignore ?></button>
				<div class="submenu" id="submenu-usos">
					<ul class="submenu__lista">
						<?php foreach ( dipt_t( 'usos' ) as $dipt_i => $dipt_u ) : ?>
							<li><a href="<?php echo esc_url( dipt_url( $dipt_u[0] ) ); ?>" data-foto="<?php echo esc_attr( $dipt_u[3] ); ?>">
								<span class="dato">0<?php echo (int) $dipt_i + 1; ?></span>
								<strong><?php echo esc_html( $dipt_u[1] ); ?></strong>
								<small><?php echo esc_html( $dipt_u[2] ); ?></small>
							</a></li>
						<?php endforeach; ?>
					</ul>
					<div class="submenu__foto" aria-hidden="true">
						<?php foreach ( dipt_t( 'usos' ) as $dipt_i => $dipt_u ) : ?>
							<img src="<?php echo esc_url( dipt_foto_url( $dipt_u[3], 480 ) ); ?>" alt="" loading="lazy" width="480" height="320" data-foto-id="<?php echo esc_attr( $dipt_u[3] ); ?>"<?php echo 0 === $dipt_i ? ' class="activa"' : ''; ?>>
						<?php endforeach; ?>
					</div>
					<a class="submenu__todos" href="<?php echo esc_url( dipt_url( 'aplicaciones' ) ); ?>"><span><?php echo esc_html( dipt_t( 'todos_usos' ) ); ?></span><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></a>
				</div>
			</div>
			<a class="menu__enlace" href="<?php echo esc_url( dipt_url( 'empresas' ) ); ?>"<?php echo 'empresas' === $dipt_clave ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $dipt_menu['empresas'] ); ?></a>
			<a class="menu__enlace" href="<?php echo esc_url( dipt_url( 'envios' ) ); ?>"<?php echo 'envios' === $dipt_clave ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $dipt_menu['envios'] ); ?></a>
			<a class="menu__enlace" href="<?php echo esc_url( dipt_url( 'contacto' ) ); ?>"<?php echo 'contacto' === $dipt_clave ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $dipt_menu['contacto'] ); ?></a>
			<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			<div class="menu__extra">
				<a class="tel-grande" href="<?php echo esc_attr( $dipt_c['telefono_href'] ); ?>"><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><?php echo esc_html( $dipt_c['telefono'] ); ?></a>
				<a class="tel-grande" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?>WhatsApp <?php echo esc_html( $dipt_c['whatsapp'] ); ?></a>
				<?php dipt_selector_idioma(); ?>
			</div>
		</nav>

		<div class="cabecera__herramientas">
			<a class="cabecera__tel" href="<?php echo esc_attr( $dipt_c['telefono_href'] ); ?>"><?php echo esc_html( $dipt_c['telefono'] ); ?></a>
			<?php dipt_selector_idioma( 'idiomas--cabecera' ); ?>
			<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
				<a class="cabecera__carrito" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php echo esc_attr( dipt_t( 'carrito' ) ); ?>"><?php echo dipt_icono( 'carrito' ); // phpcs:ignore ?><span data-carrito-n="0">0</span></a>
			<?php endif; ?>
			<?php echo dipt_boton( dipt_t( 'comprar_corto' ), dipt_url( 'producto' ), $dipt_oscuro ? 'hielo' : '' ); // phpcs:ignore ?>
			<button class="cabecera__menu-movil" type="button" aria-expanded="false" aria-controls="menu-principal" data-menu-movil aria-label="<?php echo esc_attr( dipt_t( 'menu_abrir' ) ); ?>"><?php echo dipt_icono( 'menu' ); // phpcs:ignore ?></button>
		</div>
	</div>
</header>
<main id="contenido" class="contenido">
