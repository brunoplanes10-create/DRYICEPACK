<?php
/**
 * Página de zona (misma plantilla para cada ciudad o comarca, con sus datos reales; rediseño 13/10/2026, plan 2.11):
 * Z1 título con cifras de distancia · Z2 tres pasos en mono y mapa radial con la furgoneta que viaja de Mataró a la zona ·
 * Z3 entrega en la zona con el precio de recogida destacado · Z4 usos en tira de fotos · Z5 preguntas locales ·
 * Z6 zonas cercanas.
 * Tonos: papel · noche · papel · blanco · escarcha · noche · pie marino.
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
// Cifras con su unidad sin partirse («3 kg», «35 €»)
$z_ns      = static fn( $s ) => preg_replace( '/(\d) (€|kg|km|min)(?=[\s.,;:)]|$)/u', "\$1\u{00A0}\$2", (string) $s );
$titulo_h1 = $sin_cortes( $t( $cz['titulo'] ) );
// Precio de recogida destacado (Z3): los mismos marcadores que el texto de la fila «Recogida»
$z_idioma   = dipt_idioma();
$z_recogida = array(
	array( dipt_pk() . "\u{00A0}kg", dipt_valor_marcador( 'recogida:pk', $z_idioma ) ),
	array( "10\u{00A0}kg", dipt_valor_marcador( 'recogida:10', $z_idioma ) ),
);
// Fotos de la tira de usos (Z4)
$z_fotos = array( 'hosteleria' => 'uso-hosteleria', 'transporte' => 'uso-transporte', 'industria' => 'uso-industria', 'laboratorios' => 'uso-laboratorio', 'eventos' => 'uso-eventos', 'empresas' => 'almacen-caja' );
$z_usos  = $cz['usos']['lista'];
$z_usos[] = array( 'empresas', dipt_t( 'menu' )['empresas'] );
get_header();
?>

<!-- Z1 · Título y cifras -->
<section class="z-hero tono-papel" aria-labelledby="z-hero-t">
	<div class="envoltura z-hero__in">
		<div>
			<?php echo dipt_migas( array( array( $cz['migas'][0], dipt_url( 'inicio' ) ), array( $cz['migas'][1], dipt_url( 'zonas' ) ), array( $t( $cz['migas'][2] ), dipt_url( 'zona-' . $clave ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero" id="z-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $titulo_h1 ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $z_ns( $t( $cz['texto'] ) ) ); ?></p>
			<?php echo dipt_boton( $cz['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
		</div>
		<dl class="z-cifras">
			<?php foreach ( $cz['datos'] as $i => $d ) : ?>
				<div data-revela style="--i:<?php echo (int) $i; ?>"><dt class="dato"><?php echo esc_html( $d[0] ); ?></dt><dd><?php echo esc_html( $z_ns( $t( $d[1] ) ) ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- Z2 · De la nave a la zona: tres pasos en mono y el mapa radial con la furgoneta -->
<section class="z-mapa tono-oscuro" aria-label="<?php echo esc_attr( $c['mapa'] ); ?>">
	<div class="envoltura z-mapa__in">
		<div class="z-ruta">
			<ol class="z-ruta__pasos dato">
				<li data-revela style="--i:0"><span class="z-ruta__n">01</span><span class="z-ruta__valor">Mataró</span></li>
				<li data-revela style="--i:1"><span class="z-ruta__n">02</span><span class="z-ruta__valor"><?php echo esc_html( $z_ns( $t( $cz['datos'][0][1] ) ) ); ?> · <?php echo esc_html( $z_ns( $t( $cz['datos'][1][1] ) ) ); ?></span><span class="z-ruta__nota"><?php echo esc_html( $cz['datos'][1][0] ); ?></span></li>
				<li data-revela style="--i:2"><span class="z-ruta__n">03</span><span class="z-ruta__valor"><?php echo esc_html( ucfirst( dipt_zona_nombre( $z ) ) ); ?></span></li>
			</ol>
			<p class="dato z-mapa__fuente"><?php echo esc_html( $cz['fuente'] ); ?></p>
		</div>
		<?php echo dipt_mapa_radial( $destacadas, false, array( 'furgoneta' => true ) ); // phpcs:ignore ?>
	</div>
</section>

<!-- Z3 · Entrega en la zona, con el precio de recogida destacado -->
<section class="z-entrega tono-papel seccion" aria-labelledby="z-entrega-t">
	<div class="envoltura z-entrega__in">
		<div class="z-entrega__cab">
			<?php echo dipt_titulo( 'h2', $sin_cortes( $t( $cz['entrega']['titulo'] ) ), 't-h2', 'z-entrega-t' ); // phpcs:ignore ?>
			<div class="z-recogida" data-revela>
				<p class="dato z-recogida__et"><?php echo esc_html( $t( $cz['entrega']['filas'][2][0] ?? '' ) ); ?> · Mataró</p>
				<dl>
					<?php foreach ( $z_recogida as $r ) : ?><div><dt class="dato"><?php echo esc_html( $r[0] ); ?></dt><dd><?php echo esc_html( str_replace( ' ', "\u{00A0}", $r[1] ) ); ?></dd></div><?php endforeach; ?>
				</dl>
			</div>
		</div>
		<dl class="z-tabla">
			<?php foreach ( $cz['entrega']['filas'] as $i => $f ) : ?>
				<div data-revela style="--i:<?php echo (int) $i; ?>"><dt><?php echo esc_html( $t( $f[0] ) ); ?></dt><dd><?php echo esc_html( $z_ns( $t( $f[1] ) ) ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- Z4 · Usos: tira de fotos de anchos distintos con el nombre en mono (carril con scroll-snap en móvil) -->
<section class="z-usos tono-blanco seccion seccion--densa" aria-labelledby="z-usos-t">
	<div class="envoltura">
		<h2 class="t-h2" id="z-usos-t" data-revela><?php echo esc_html( $sin_cortes( $t( $cz['usos']['titulo'] ) ) ); ?></h2>
		<?php /* La entrada va en la tira entera: en el carril del móvil, las fotos de fuera de pantalla no deben quedar ocultas */ ?>
		<ul class="z-usos__tira" data-revela>
			<?php foreach ( $z_usos as $i => $u ) : ?>
				<li data-activa>
					<a href="<?php echo esc_url( dipt_url( $u[0] ) ); ?>">
						<span class="z-usos__foto foto--sangre"><?php echo dipt_foto( $z_fotos[ $u[0] ] ?? 'cajas-oscuro', '', array( 'sizes' => '(max-width: 800px) 70vw, 24vw' ) ); // phpcs:ignore ?></span>
						<span class="z-usos__nombre dato"><span><?php echo esc_html( $u[1] ); ?></span><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- Z5 · Preguntas locales -->
<section class="z-faq tono-escarcha seccion seccion--densa" aria-labelledby="z-faq-t">
	<div class="envoltura z-faq__in">
		<h2 class="t-h3" id="z-faq-t"><?php echo esc_html( dipt_t( 'faq' ) ); ?></h2>
		<div>
			<?php foreach ( $faq as $i => $f ) : ?>
				<details class="z-faq__item"<?php echo 0 === $i ? ' open' : ''; ?>><summary><span><?php echo esc_html( $f[0] ); ?></span><span class="z-faq__signo" aria-hidden="true"></span></summary><p><?php echo esc_html( $z_ns( $f[1] ) ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $cerca ) : // En inglés solo existe Barcelona: sin zonas traducidas cerca, no se pinta un bloque vacío ?>
<!-- Z6 · Zonas cercanas (noche: la sección antes del pie marino) -->
<nav class="z-cerca tono-oscuro" aria-labelledby="z-cerca-t">
	<div class="envoltura">
		<h2 class="dato" id="z-cerca-t"><?php echo esc_html( $cz['cerca'] ); ?></h2>
		<ul><?php foreach ( $cerca as $k => $d ) : $o = dipt_zonas()[ $k ]; ?><li><a href="<?php echo esc_url( dipt_url( 'zona-' . $k ) ); ?>"><?php echo esc_html( ucfirst( dipt_zona_nombre( $o ) ) ); ?></a></li><?php endforeach; ?></ul>
	</div>
</nav>
<?php else : // Sin zonas cercanas traducidas (inglés): la misma franja noche antes del pie, con el índice de zonas y los envíos (textos ya existentes) ?>
<nav class="z-cerca tono-oscuro" aria-labelledby="z-cerca-t">
	<div class="envoltura">
		<h2 class="dato" id="z-cerca-t"><?php echo esc_html( $cz['migas'][1] ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( dipt_url( 'zonas' ) ); ?>"><?php echo esc_html( $c['indice']['migas'][1] ); ?></a></li>
			<li><a href="<?php echo esc_url( dipt_url( 'envios' ) ); ?>"><?php echo esc_html( $c['indice']['envios'] ); ?></a></li>
		</ul>
	</div>
</nav>
<?php endif; ?>

<?php
get_footer();
