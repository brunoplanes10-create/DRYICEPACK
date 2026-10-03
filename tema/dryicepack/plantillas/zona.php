<?php
/**
 * Página de zona (misma plantilla para cada ciudad o comarca, con sus datos reales):
 * Z1 título con cifras de distancia · Z2 mapa radial con la zona resaltada · Z3 entrega en la zona ·
 * Z4 usos · Z5 preguntas locales · Z6 zonas cercanas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$clave = dipt_paginas()[ dipt_pagina_actual() ]['zona'] ?? '';
$z     = dipt_zonas()[ $clave ] ?? null;
if ( ! $z ) {
	get_template_part( 'page' );
	return;
}
$c  = dipt_contenido( 'zonas' );
$cz = $c['zona'];
$t  = static fn( $s ) => dipt_zona_texto( $s, $z );
$faq = array_map( static fn( $f ) => array( $t( $f[0] ), $t( $f[1] ) ), $cz['faq'] );
dipt_registrar_faq( $faq );

// Resaltar la zona (o todas las ciudades de la comarca) y buscar las cercanas
$destacadas = array( $clave );
if ( 'comarca' === $z['tipo'] ) {
	foreach ( dipt_zonas() as $k => $o ) if ( 'ciudad' === $o['tipo'] && $o['comarca'] === $z['comarca'] ) $destacadas[] = $k;
}
$cerca = array();
foreach ( dipt_zonas() as $k => $o ) {
	if ( $k === $clave || ! dipt_traducida( 'zona-' . $k, dipt_idioma() ) ) continue;
	$cerca[ $k ] = dipt_distancia( array( $z['lat'], $z['lon'] ), array( $o['lat'], $o['lon'] ) );
}
asort( $cerca );
$cerca = array_slice( $cerca, 0, 6, true );
// Titulares: las palabras cortas (en, el, de, a, sant...) se unen a la siguiente con un espacio de no separación,
// para que el topónimo no se parta en "El / Prat" ni quede "en" al final de una línea. Solo en los títulos.
$sin_cortes = static fn( $s ) => preg_replace( "/(?<=^|[\\s>|\x{00A0}])(el|la|els|les|en|a|al|del|de|sant|santa|y|i|in) (?=\\S)/iu", "\$1\u{00A0}", $s ) ?? $s;
$titulo_h1  = $sin_cortes( $t( $cz['titulo'] ) );
get_header();
?>

<!-- Z1 · Título y cifras -->
<section class="z-hero" aria-labelledby="z-hero-t">
	<div class="envoltura z-hero__in">
		<div>
			<?php echo dipt_migas( array( array( $cz['migas'][0], dipt_url( 'inicio' ) ), array( $cz['migas'][1], dipt_url( 'zonas' ) ), array( $t( $cz['migas'][2] ), dipt_url( 'zona-' . $clave ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero" id="z-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $titulo_h1 ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $t( $cz['texto'] ) ); ?></p>
			<?php echo dipt_boton( $cz['boton'], dipt_url( 'producto' ) ); // phpcs:ignore ?>
		</div>
		<dl class="z-cifras">
			<?php foreach ( $cz['datos'] as $i => $d ) : ?>
				<div data-revela style="--i:<?php echo (int) $i; ?>"><dt class="dato"><?php echo esc_html( $d[0] ); ?></dt><dd><?php echo esc_html( $t( $d[1] ) ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- Z2 · Mapa radial -->
<section class="z-mapa tono-oscuro" aria-label="<?php echo esc_attr( $c['mapa'] ); ?>">
	<div class="envoltura">
		<?php echo dipt_mapa_radial( $destacadas, false ); // phpcs:ignore ?>
		<p class="dato z-mapa__fuente"><?php echo esc_html( $cz['fuente'] ); ?></p>
	</div>
</section>

<!-- Z3 · Entrega en la zona -->
<section class="z-entrega tono-papel seccion" aria-labelledby="z-entrega-t">
	<div class="envoltura z-entrega__in">
		<?php echo dipt_titulo( 'h2', $sin_cortes( $t( $cz['entrega']['titulo'] ) ), 't-h2', 'z-entrega-t' ); // phpcs:ignore ?>
		<dl class="z-tabla">
			<?php foreach ( $cz['entrega']['filas'] as $f ) : ?>
				<div data-revela><dt><?php echo esc_html( $t( $f[0] ) ); ?></dt><dd><?php echo esc_html( $t( $f[1] ) ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- Z4 · Usos -->
<section class="z-usos tono-blanco seccion" aria-labelledby="z-usos-t">
	<div class="envoltura">
		<h2 class="t-h3" id="z-usos-t"><?php echo esc_html( $sin_cortes( $t( $cz['usos']['titulo'] ) ) ); ?></h2>
		<ul class="z-usos__lista">
			<?php foreach ( $cz['usos']['lista'] as $u ) : ?><li><a href="<?php echo esc_url( dipt_url( $u[0] ) ); ?>"><?php echo esc_html( $u[1] ); ?><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></a></li><?php endforeach; ?>
			<li><a href="<?php echo esc_url( dipt_url( 'empresas' ) ); ?>"><?php echo esc_html( dipt_t( 'menu' )['empresas'] ); ?><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></a></li>
		</ul>
	</div>
</section>

<!-- Z5 · Preguntas locales -->
<section class="z-faq tono-escarcha seccion" aria-labelledby="z-faq-t">
	<div class="envoltura z-faq__in">
		<h2 class="t-h3" id="z-faq-t"><?php echo esc_html( dipt_t( 'faq' ) ); ?></h2>
		<div>
			<?php foreach ( $faq as $f ) : ?>
				<details class="z-faq__item"><summary><?php echo esc_html( $f[0] ); ?></summary><p><?php echo esc_html( $f[1] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Z6 · Zonas cercanas -->
<nav class="z-cerca tono-marino" aria-labelledby="z-cerca-t">
	<div class="envoltura">
		<h2 class="dato" id="z-cerca-t"><?php echo esc_html( $cz['cerca'] ); ?></h2>
		<ul><?php foreach ( $cerca as $k => $d ) : $o = dipt_zonas()[ $k ]; ?><li><a href="<?php echo esc_url( dipt_url( 'zona-' . $k ) ); ?>"><?php echo esc_html( ucfirst( dipt_zona_nombre( $o ) ) ); ?></a></li><?php endforeach; ?></ul>
	</div>
</nav>

<?php
get_footer();
