<?php
/**
 * Qué es el hielo seco (rediseño 13/10/2026, plan 2.9):
 * Q1 hero con foto a sangre que baja sobre Q2 y −78,5 °C que cuenta · Q2 ficha técnica (hoja sobre foto) ·
 * Q3 comparativa con termómetro · Q4 a tamaño real sobre una regla de borde a borde · Q5 de dónde sale (estados del CO₂) ·
 * Q6 dudas con foto fija · cierre común.
 * Tonos: papel · blanco · papel (aire) · noche · escarcha · blanco · cierre noche · pie marino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'que-es' );
dipt_registrar_faq( $c['faq']['lista'] );
dipt_precargar( 'caja-16mm-lateral', '(max-width: 900px) 92vw, 42vw' );
// La cifra en el formato del idioma (coma en es/ca, punto en en): sale de la fila de temperatura de la comparativa
$q_temp  = $c['comparar']['filas'][0];
$q_cifra = trim( str_replace( '°C', '', $q_temp[1] ) );
$q_col   = $c['comparar']['columnas'];
// Cifras con su unidad sin partirse en dos líneas («16 mm», «−78,5 °C», «3 kg»): espacio de no separación
$q_ns = static fn( $s ) => preg_replace( '/(\d) (mm|°C|kg)(?=[\s.,;:)]|$)/u', "\$1\u{00A0}\$2", (string) $s );
get_header();
?>

<!-- Q1 · Hero: texto y foto a sangre que baja sobre la ficha -->
<section class="q-hero tono-papel" aria-labelledby="q-hero-t">
	<div class="envoltura q-hero__in">
		<div class="q-hero__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'que-es' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-hero q-hero__titulo" id="q-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada q-hero__resumen"><?php dipt_html( $c['hero']['resumen'] ); ?></p>
			<p class="suave q-hero__tambien"><?php echo esc_html( $c['hero']['tambien'] ); ?></p>
			<div class="q-hero__acciones">
				<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
				<?php echo dipt_enlace( $c['hero']['seguridad'] ?? dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?>
			</div>
		</div>
		<div class="q-hero__foto">
			<div class="foto--sangre foto--sangre-der foto--4x5">
				<?php echo dipt_foto( 'caja-16mm-lateral', '', array( 'lcp' => true, 'sizes' => '(max-width: 900px) 92vw, 42vw', 'recorte' => '58% 50%' ) ); // phpcs:ignore ?>
			</div>
			<p class="q-hero__cifra" aria-hidden="true"><span class="cifra-fija"><span class="cifra-fija__reserva"><?php echo esc_html( $q_cifra ); ?></span><span data-cuenta="-78.5" data-desde="20" data-decimales="1" data-duracion="1800"><?php echo esc_html( $q_cifra ); ?></span></span><span class="q-hero__unidad">°C</span></p>
		</div>
	</div>
</section>

<!-- Q2 · Ficha técnica: hoja girada montada sobre la foto de los pellets -->
<section class="q-ficha tono-blanco seccion" aria-labelledby="q-ficha-t">
	<div class="envoltura q-ficha__in">
		<div class="q-ficha__foto foto--sangre foto--sangre-izq foto--4x5" data-cortina>
			<?php echo dipt_foto( 'cenital-3mm', '', array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?>
		</div>
		<div class="hoja hoja--lado q-documento" data-revela>
			<header class="q-documento__cab dato"><span>DryIcePack</span><span id="q-ficha-t"><?php echo esc_html( $c['ficha']['titulo'] ); ?></span><span>CO₂</span></header>
			<dl>
				<?php foreach ( $c['ficha']['filas'] as $i => $f ) : ?>
					<div style="--i:<?php echo (int) $i; ?>"><dt><?php echo esc_html( $f[0] ); ?></dt><dd><?php echo esc_html( $q_ns( $f[1] ) ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		</div>
	</div>
</section>

<!-- Q3 · Comparativa: termómetro (lo visual) y tabla (los datos) -->
<section class="q-comparar tono-papel seccion seccion--aire" aria-labelledby="q-comparar-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['comparar']['titulo'], 't-h2 q-comparar__titulo', 'q-comparar-t', 'cortina' ); // phpcs:ignore ?>
		<div class="q-comparar__in">
			<?php /* Escala de 0 °C (arriba) a −80 °C (abajo). Datos del contenido: 0, −18 (congelador, pregunta 2), hasta unos −33 (placas) y −78,5. */ ?>
			<div class="q-termo" data-vivo aria-hidden="true">
				<div class="q-termo__tubo"><span class="q-termo__columna"></span></div>
				<span class="q-termo__rango" style="--t:0;--h:.4125"></span>
				<ol class="q-termo__marcas">
					<li style="--t:0"><span class="dato"><?php echo esc_html( $q_temp[2] ); ?></span><span><?php echo esc_html( $q_col[1] ); ?></span></li>
					<li style="--t:.225"><span class="dato">−18 °C</span><span><?php echo esc_html( $c['comparar']['congelador'] ?? '' ); ?></span></li>
					<li style="--t:.4125"><span class="dato">−33 °C</span><span><?php echo esc_html( $q_col[2] ); ?></span></li>
					<li class="q-termo__seco" style="--t:.98125"><span class="dato"><?php echo esc_html( $q_temp[1] ); ?></span><span><?php echo esc_html( $q_col[0] ); ?></span></li>
				</ol>
			</div>
			<div class="q-tabla-scroll" data-revela>
				<table class="q-tabla">
					<thead><tr><td></td><?php foreach ( $q_col as $i => $col ) : ?><th scope="col"<?php echo 0 === $i ? ' class="q-tabla__destacada"' : ''; ?>><?php echo esc_html( $col ); ?></th><?php endforeach; ?></tr></thead>
					<tbody>
						<?php foreach ( $c['comparar']['filas'] as $fila ) : ?>
							<tr><th scope="row"><?php echo esc_html( $fila[0] ); ?></th><td class="q-tabla__destacada" data-col="<?php echo esc_attr( $q_col[0] ); ?>"><?php echo esc_html( $q_ns( $fila[1] ) ); ?></td><td data-col="<?php echo esc_attr( $q_col[1] ); ?>"><?php echo esc_html( $q_ns( $fila[2] ) ); ?></td><td data-col="<?php echo esc_attr( $q_col[2] ); ?>"><?php echo esc_html( $q_ns( $fila[3] ) ); ?></td></tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<p class="suave q-comparar__nota"><?php echo esc_html( $c['comparar']['nota'] ); ?></p>
	</div>
</section>

<!-- Q4 · A tamaño real: dos macros y las piezas sobre una regla de laboratorio de borde a borde -->
<section class="q-tamano tono-oscuro seccion" aria-labelledby="q-tamano-t">
	<div class="envoltura q-tamano__in">
		<div class="q-tamano__cab">
			<?php echo dipt_titulo( 'h2', $c['tamano']['titulo'], 't-h2', 'q-tamano-t', 'lineas' ); // phpcs:ignore ?>
			<p class="suave" data-revela><?php echo esc_html( $c['tamano']['texto'] ); ?></p>
		</div>
		<div class="q-macros">
			<?php
			$q_macros = array( '3mm' => array( 'pellets-ia', '50% 60%' ), '16mm' => array( 'nuggets-ia', '50% 50%' ) );
			$i        = 0;
			foreach ( $q_macros as $f => $foto ) :
				$t = $c['tamano'][ $f ];
				?>
				<figure class="q-macro q-macro--<?php echo esc_attr( $f ); ?>" data-revela style="--i:<?php echo (int) $i++; ?>">
					<div class="q-macro__foto foto--lamina foto--4x5" data-cortina><?php echo dipt_foto( $foto[0], '', array( 'sizes' => '(max-width: 900px) 46vw, 24vw', 'recorte' => $foto[1] ) ); // phpcs:ignore ?></div>
					<figcaption><strong><?php echo esc_html( $t[0] ); ?></strong><span class="dato"><?php echo esc_html( $t[1] ); ?></span><span class="q-macro__texto"><?php echo esc_html( $t[2] ); ?></span></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="q-regla" data-vivo aria-hidden="true">
		<div class="envoltura q-regla__piezas">
			<div class="q-regla__pieza q-regla__pieza--3mm">
				<span class="cota" style="--largo:3mm"><span class="cota__valor"><?php echo esc_html( $c['tamano']['3mm'][0] ); ?></span><span class="cota__linea"></span></span>
				<span class="q-regla__forma"></span>
			</div>
			<div class="q-regla__pieza q-regla__pieza--16mm">
				<span class="cota" style="--largo:16mm"><span class="cota__valor"><?php echo esc_html( $c['tamano']['16mm'][0] ); ?></span><span class="cota__linea"></span></span>
				<span class="q-regla__forma"></span>
			</div>
		</div>
		<?php /* Regla en milímetros reales (1 mm = 3,7795 px CSS): trazo cada mm, medio cada 5 y largo con número cada cm.
		   El 0 cae justo bajo el borde izquierdo de la pieza de 3 mm (misma envoltura); los trazos siguen hasta los dos bordes
		   de la pantalla (la sección recorta lo que sobra) y los números solo van del 0 hacia la derecha. */ ?>
		<div class="envoltura">
			<svg class="q-regla__escala" width="100%" height="46" focusable="false">
				<defs><pattern id="q-mm" width="37.795" height="46" patternUnits="userSpaceOnUse">
					<?php for ( $m = 0; $m < 10; $m++ ) : $alto = 0 === $m ? 20 : ( 5 === $m ? 13 : 8 ); ?><rect x="<?php echo esc_attr( number_format( $m * 3.7795, 3, '.', '' ) ); ?>" y="0" width="1" height="<?php echo (int) $alto; ?>"/><?php endfor; ?>
				</pattern></defs>
				<rect x="-3000" y="0" width="9000" height="1"/>
				<rect x="-3023.6" y="0" width="9000" height="46" fill="url(#q-mm)"/>
				<?php for ( $cm = 0; $cm <= 70; $cm++ ) : ?><text x="<?php echo esc_attr( number_format( $cm * 37.795 + 3, 2, '.', '' ) ); ?>" y="38"><?php echo (int) $cm; ?></text><?php endfor; ?>
			</svg>
		</div>
	</div>
</section>

<!-- Q5 · De dónde sale: escalera de estados del CO₂ (el pellet baja los peldaños una vez) -->
<section class="q-origen tono-escarcha seccion" aria-labelledby="q-origen-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['origen']['titulo'], 't-h2', 'q-origen-t', 'revela' ); // phpcs:ignore ?>
		<ol class="q-proceso" data-vivo>
			<?php foreach ( $c['origen']['pasos'] as $i => $p ) : ?>
				<li style="--i:<?php echo (int) $i; ?>">
					<span class="q-proceso__pellet" aria-hidden="true"></span>
					<span class="q-proceso__n" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
					<?php if ( ! empty( $c['origen']['estados'][ $i ] ) ) : ?><span class="q-proceso__estado dato">CO₂ · <?php echo esc_html( $c['origen']['estados'][ $i ] ); ?></span><?php endif; ?>
					<h3><?php echo esc_html( $p[0] ); ?></h3>
					<p><?php echo esc_html( $p[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
		<p class="q-origen__nota" data-revela><?php echo esc_html( $c['origen']['nota'] ); ?></p>
	</div>
</section>

<!-- Q6 · Dudas: foto fija a la izquierda y preguntas como titulares (sin acordeón) -->
<section class="q-faq tono-blanco seccion" aria-labelledby="q-faq-t">
	<div class="envoltura q-faq__in">
		<div class="q-faq__lado">
			<?php echo dipt_titulo( 'h2', $c['faq']['titulo'], 't-h2', 'q-faq-t', 'revela' ); // phpcs:ignore ?>
			<div class="q-faq__foto foto--lamina foto--4x5" data-cortina><?php echo dipt_foto( 'caja-3mm-lateral', '', array( 'sizes' => '(max-width: 900px) 190vw, 44vw', 'recorte' => '50% 50%' ) ); // phpcs:ignore -- detalle de pellets y vapor: se acerca 1,9× en que-es.css (la caja entera ya es la foto del hero); sizes a juego para que no se pixele ?></div>
		</div>
		<ol class="q-faq__lista" data-activa-grupo>
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<li class="q-faq__item" data-activa><span class="dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><h3 class="t-h3"><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $q_ns( $f[1] ) ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<?php
// Cierre común: el titular es el que ya tenía la página ([a validar] en el plan: no se cambia). Va a t-h1 (es una frase larga).
dipt_cierre( array(
	'titulo'  => $c['final']['titulo'],
	'entrada' => 'revela',
	'foto'    => 'cajas-furgoneta',
	'recorte' => '30% 50%',
	'boton'   => $c['final']['boton'],
	'clase'   => 'q-cierre',
) );
get_footer();
