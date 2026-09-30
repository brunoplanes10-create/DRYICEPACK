<?php
/**
 * Cabecera: franja con el reloj de corte en directo + barra principal + menú móvil.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$c = dipt_contacto();

/* Menú principal: el que se asigne en Apariencia → Menús; si no hay, este por defecto. */
$items = array();
if ( has_nav_menu( 'principal' ) ) {
	$loc  = get_nav_menu_locations();
	foreach ( (array) wp_get_nav_menu_items( $loc['principal'] ) as $it ) {
		if ( ! $it->menu_item_parent ) $items[] = array( 'texto' => $it->title, 'url' => $it->url, 'clase' => implode( ' ', (array) $it->classes ) );
	}
} else {
	$items = array(
		array( 'texto' => '¿Qué es?', 'url' => home_url( '/que-es-el-hielo-seco/' ) ),
		array( 'texto' => 'Aplicaciones', 'url' => home_url( '/aplicaciones-del-hielo-seco/' ) ),
		array( 'texto' => 'Envíos', 'url' => home_url( '/envios-y-plazos/' ) ),
		array( 'texto' => 'Contacto', 'url' => home_url( '/contacto/' ) ),
	);
	// Enlace de temporada: Halloween hasta el 1 de noviembre (desaparece solo)
	if ( time() < strtotime( '2026-11-02 00:00:00 Europe/Madrid' ) ) {
		array_splice( $items, 2, 0, array( array( 'texto' => 'Halloween', 'url' => home_url( '/hielo-seco-halloween/' ), 'clase' => 'nav__temporada' ) ) );
	}
}
$ruta_actual = trailingslashit( wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ) ?: '/' );
$es_actual   = static function ( $url ) use ( $ruta_actual ) {
	return trailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' ) === $ruta_actual;
};
$logo = has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : dipt_img( 'logo-dryicepack.webp' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="saltar" href="#contenido">Saltar al contenido</a>

<div class="franja">
	<div class="contenedor franja__in">
		<?php echo dipt_reloj_corte( 'reloj-corte--franja' ); // phpcs:ignore ?>
		<p class="franja__contacto">
			<a href="<?php echo esc_url( $c['telefono_href'] ); ?>"><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><?php echo esc_html( $c['telefono'] ); ?></a>
			<a href="mailto:<?php echo esc_attr( $c['email'] ); ?>"><?php echo dipt_icono( 'email' ); // phpcs:ignore ?><?php echo esc_html( $c['email'] ); ?></a>
			<span><?php echo esc_html( $c['horario'] ); ?></span>
		</p>
	</div>
</div>

<header class="cab" data-cab>
	<div class="contenedor cab__in">
		<a class="cab__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="DryIcePack, ir al inicio">
			<img src="<?php echo esc_url( $logo ); ?>" alt="DryIcePack" width="198" height="44" fetchpriority="low">
		</a>

		<nav class="cab__nav" aria-label="Principal">
			<?php foreach ( $items as $it ) : ?>
				<a class="nav__enlace <?php echo esc_attr( $it['clase'] ?? '' ); ?>" href="<?php echo esc_url( $it['url'] ); ?>"<?php echo $es_actual( $it['url'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it['texto'] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="cab__acciones">
			<a class="btn btn--senal btn--sm cab__comprar" href="<?php echo esc_url( dipt_producto_url() ); ?>" data-iman><span>Comprar hielo seco</span></a>
			<a class="btn btn--linea btn--sm cab__suministro" href="<?php echo esc_url( home_url( '/programar-suministro-de-hielo-seco/' ) ); ?>"><span>Programar suministro</span></a>
			<a class="cab__carrito" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/carrito/' ) ); ?>" aria-label="Ver carrito">
				<?php echo dipt_icono( 'carrito' ); // phpcs:ignore ?><span class="cab__num" data-carrito-num hidden>0</span>
			</a>
			<button class="cab__burger" type="button" aria-expanded="false" aria-controls="menu-movil" data-menu-abrir>
				<?php echo dipt_icono( 'menu' ); // phpcs:ignore ?><span class="oculto">Abrir menú</span>
			</button>
		</div>
	</div>

	<div class="menu-movil" id="menu-movil" data-menu hidden>
		<div class="menu-movil__cab">
			<span class="menu-movil__titulo">Menú</span>
			<button class="menu-movil__cerrar" type="button" data-menu-cerrar><?php echo dipt_icono( 'cerrar' ); // phpcs:ignore ?><span class="oculto">Cerrar menú</span></button>
		</div>
		<nav class="menu-movil__nav" aria-label="Menú móvil">
			<?php foreach ( $items as $i => $it ) : ?>
				<a href="<?php echo esc_url( $it['url'] ); ?>" style="--i:<?php echo (int) $i; ?>"<?php echo $es_actual( $it['url'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it['texto'] ); ?><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></a>
			<?php endforeach; ?>
		</nav>
		<div class="menu-movil__acciones">
			<?php echo dipt_boton( 'Comprar hielo seco', dipt_producto_url(), 'senal' ); // phpcs:ignore ?>
			<?php echo dipt_boton( 'Programar suministro', home_url( '/programar-suministro-de-hielo-seco/' ), 'linea-clara' ); // phpcs:ignore ?>
		</div>
		<p class="menu-movil__contacto"><a href="<?php echo esc_url( $c['telefono_href'] ); ?>"><?php echo esc_html( $c['telefono'] ); ?></a> · <?php echo esc_html( $c['horario'] ); ?></p>
		<?php echo dipt_niebla( 'niebla--menu' ); // phpcs:ignore ?>
	</div>
</header>

<main id="contenido" class="contenido">
