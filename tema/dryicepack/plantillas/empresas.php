<?php
/**
 * Empresas (rediseño del 14/10/2026, plan 2.5). Tonos: E1 papel · E2 noche · E3 papel (aire) · E4 marino (densa) ·
 * E5 blanco · E6 escarcha · E7 papel + franja noche · pie marino.
 * E1 hero con foto troquelada y la etiqueta −78,5 °C que cuenta · E2 un móvil fijo cuya pantalla cambia con cada paso
 * (en móvil, carrusel «1/3») · E3 ventajas en lista gigante · E4 palé de 5 × 3 cajas que se llena con los kilos ·
 * E5 mosaico de usos reales con el logotipo de cada cliente · E6 alta en una frase (hoja sobre la foto) ·
 * E7 preguntas (dipt_faq) y franja con el teléfono.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'empresas' );
$con = dipt_contacto();
dipt_precargar( 'almacen-caja', '(max-width: 900px) 100vw, 40vw' );
$enviado = isset( $_GET['enviado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error   = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

/* E2 · Las tres pantallas (decorativas: el texto de cada paso va aparte). Se pintan en el móvil fijo (escritorio)
   y dentro de cada paso del carrusel (móvil). */
$e_pantalla = static function ( $n ) use ( $c ) {
	ob_start();
	if ( 0 === $n ) : ?>
		<div class="e-pantalla e-pantalla--wa">
			<p class="e-pantalla__barra e-pantalla__barra--wa"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?> DryIcePack</p>
			<p class="e-burbuja e-burbuja--yo"><?php echo esc_html( $c['pedir']['chat'][0] ); ?></p>
			<p class="e-burbuja"><?php echo esc_html( $c['pedir']['chat'][1] ); ?></p>
		</div>
	<?php elseif ( 1 === $n ) : ?>
		<div class="e-pantalla e-pantalla--web">
			<p class="e-pantalla__barra"><?php echo esc_html( $c['pedir']['repetir']['titulo'] ); ?></p>
			<?php foreach ( $c['pedir']['repetir']['filas'] as $i => $f ) : ?>
				<p class="e-pedidos__fila"><span><?php echo esc_html( $f[0] ); ?></span><span class="e-pedidos__boton<?php echo 0 === $i ? ' activo' : ''; ?>"><?php echo dipt_icono( 'repetir' ); // phpcs:ignore ?><?php echo esc_html( $f[1] ); ?></span></p>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="e-pantalla e-pantalla--email">
			<p class="e-pantalla__barra"><?php echo dipt_icono( 'email' ); // phpcs:ignore ?> Email</p>
			<?php foreach ( $c['pedir']['email'] as $i => $l ) : ?><p class="e-carta__l<?php echo 2 === $i ? ' e-carta__cuerpo' : ''; ?>"><?php echo esc_html( $l ); ?></p><?php endforeach; ?>
		</div>
	<?php endif;
	return ob_get_clean();
};

/* E4 · Palé: cuenta de empresa, hasta 30 kg por caja; 400 kg = 14 cajas en un palé de 15 */
$e_por_caja = 30;
$e_kg       = 60;
$e_min      = 3;
$e_max      = 400;
$e_cajas    = (int) max( 1, ceil( $e_kg / $e_por_caja ) );
$e_en       = static fn( $v ) => number_format( ( $v - $e_min ) / ( $e_max - $e_min ), 4, '.', '' );
$e_caja_svg = '<svg viewBox="0 0 48 44" focusable="false"><path class="e-caja__cuerpo" d="M5 14h38v28H5z"/><path class="e-caja__tapa" d="M2 6.5h44V14H2z"/><path class="e-caja__raya" d="M15 26h18"/></svg>';

/* E7 · Teléfono por grupos: 936 / 73 / 76 / 41 */
$e_tel = preg_split( '/\s+/', trim( (string) $con['telefono'] ) );
get_header();
?>

<!-- E1 · Hero con foto vertical troquelada -->
<section class="e-hero" aria-labelledby="e-hero-t">
	<div class="envoltura e-hero__in">
		<div class="e-hero__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'empresas' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1 e-hero__titulo" id="e-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<div class="e-hero__acciones">
				<?php echo dipt_boton( $c['hero']['boton'], '#alta', 'marino' ); // phpcs:ignore ?>
				<p><?php echo esc_html( $c['hero']['tel'] ); ?> <a class="tel-grande" href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a></p>
			</div>
		</div>
		<figure class="e-hero__foto">
			<?php echo dipt_foto( 'almacen-caja', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 40vw' ) ); // phpcs:ignore ?>
			<figcaption class="e-hero__sello dato" aria-hidden="true"><span><span class="cifra-fija"><span class="cifra-fija__reserva"><?php echo esc_html( 'en' === dipt_idioma() ? '−78.5' : '−78,5' ); ?></span><span data-cuenta="-78.5" data-desde="20" data-decimales="1" data-duracion="1800"><?php echo esc_html( 'en' === dipt_idioma() ? '−78.5' : '−78,5' ); ?></span></span> °C</span><span>B2B</span></figcaption>
		</figure>
	</div>
</section>

<!-- E2 · Cómo se pide: un móvil fijo cuya pantalla cambia con cada paso (data-activa); en móvil, carrusel «1/3» -->
<section class="e-pedir tono-oscuro seccion" aria-labelledby="e-pedir-t">
	<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura e-pedir__in">
		<?php echo dipt_titulo( 'h2', $c['pedir']['titulo'], 't-h2 e-pedir__titulo', 'e-pedir-t' ); // phpcs:ignore ?>
		<div class="e-pasos" data-activa-grupo>
			<div class="e-telefono" aria-hidden="true">
				<div class="e-telefono__marco">
					<?php for ( $i = 0; $i < 3; $i++ ) : ?><div class="e-telefono__pantalla"><?php echo $e_pantalla( $i ); // phpcs:ignore ?></div><?php endfor; ?>
				</div>
			</div>
			<ol class="e-pasos__lista" data-carrusel tabindex="0" aria-labelledby="e-pedir-t">
				<?php foreach ( $c['pedir']['canales'] as $i => $canal ) : ?>
					<li class="e-paso" data-activa>
						<div class="e-paso__pantalla" aria-hidden="true"><?php echo $e_pantalla( $i ); // phpcs:ignore ?></div>
						<span class="e-paso__n dato" aria-hidden="true">0<?php echo (int) $i + 1; ?></span>
						<h3><?php echo esc_html( $canal[0] ); ?></h3>
						<p><?php echo esc_html( $canal[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="e-pasos__contador dato" aria-hidden="true"><span data-carrusel-n>1</span>/<?php echo (int) count( $c['pedir']['canales'] ); ?></p>
		</div>
	</div>
</section>

<!-- E3 · Ventajas en lista numerada gigante -->
<section class="e-ventajas tono-papel seccion seccion--aire" aria-labelledby="e-ventajas-t">
	<div class="envoltura e-ventajas__in">
		<div class="e-ventajas__cabeza"><?php echo dipt_titulo( 'h2', $c['ventajas']['titulo'], 't-h2', 'e-ventajas-t', 'revela' ); // phpcs:ignore ?></div>
		<ol class="e-ventajas__lista">
			<?php foreach ( $c['ventajas']['lista'] as $i => $v ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="e-ventajas__n" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
					<h3><?php echo esc_html( $v[0] ); ?></h3>
					<p><?php echo esc_html( $v[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- E4 · Palé de 5 × 3 cajas que se llena con los kilos (cuenta de empresa: hasta 30 kg por caja) -->
<section class="e-volumen tono-marino seccion seccion--densa" aria-labelledby="e-volumen-t" data-volumen data-por-caja="<?php echo (int) $e_por_caja; ?>" data-t-cajas="<?php echo esc_attr( $c['volumen']['cajas'] ); ?>" data-t-caja="<?php echo esc_attr( $c['volumen']['caja'] ); ?>">
	<div class="envoltura e-volumen__in">
		<div class="e-volumen__control">
			<?php echo dipt_titulo( 'h2', $c['volumen']['titulo'], 't-h2', 'e-volumen-t', 'revela' ); // phpcs:ignore ?>
			<label class="e-volumen__etiqueta dato" for="e-kg"><?php echo esc_html( $c['volumen']['rango'] ); ?></label>
			<p class="e-volumen__cifra"><output for="e-kg" data-volumen-kg><?php echo (int) $e_kg; ?></output><span>kg</span></p>
			<div class="regla-rango e-regla">
				<input class="e-volumen__rango" id="e-kg" type="range" min="<?php echo (int) $e_min; ?>" max="<?php echo (int) $e_max; ?>" step="1" value="<?php echo (int) $e_kg; ?>" data-volumen-rango>
				<ol class="regla-rango__marcas" aria-hidden="true">
					<?php foreach ( array( $e_min, 150, 250, $e_max ) as $m ) : ?><li style="--en:<?php echo esc_attr( $e_en( $m ) ); ?>"><?php echo (int) $m; ?></li><?php endforeach; ?>
				</ol>
			</div>
			<p class="e-volumen__cajas dato" data-volumen-cajas><?php echo esc_html( 1 === $e_cajas ? sprintf( $c['volumen']['caja'], $e_kg ) : sprintf( $c['volumen']['cajas'], $e_cajas, $e_kg ) ); ?></p>
			<ul class="e-volumen__notas">
				<li data-nota="web" class="activa"><?php echo esc_html( $c['volumen']['notas']['web'] ); ?></li>
				<li data-nota="bcn"><?php echo esc_html( $c['volumen']['notas']['bcn'] ); ?></li>
				<li data-nota="fuera"><?php echo esc_html( $c['volumen']['notas']['fuera'] ); ?></li>
			</ul>
		</div>
		<div class="e-pale" aria-hidden="true">
			<div class="e-pale__cajas">
				<?php
				for ( $i = 0; $i < 15; $i++ ) :
					$orden = ( 2 - intdiv( $i, 5 ) ) * 5 + $i % 5; // se llena de abajo arriba y de izquierda a derecha
					?>
					<span class="e-caja<?php echo $orden < $e_cajas ? ' llena' : ''; ?>" style="--orden:<?php echo (int) $orden; ?>"><span class="e-caja__vacia"><?php echo $e_caja_svg; // phpcs:ignore ?></span><span class="e-caja__llena"><?php echo $e_caja_svg; // phpcs:ignore ?></span></span>
				<?php endfor; ?>
			</div>
			<div class="e-pale__base"><i></i><i></i><i></i></div>
		</div>
	</div>
</section>

<!-- E5 · Usos reales en mosaico: los clientes que salen con su nombre llevan el logotipo en un marbete -->
<section class="e-casos tono-blanco seccion" aria-labelledby="e-casos-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['casos']['titulo'], 't-h2 e-casos__titulo', 'e-casos-t', 'cortina' ); // phpcs:ignore ?>
		<ul class="e-mosaico">
			<?php foreach ( $c['casos']['lista'] as $i => $caso ) : $cliente = $caso[3] ?? ''; ?>
				<li class="e-mosaico__pieza e-mosaico__pieza--<?php echo (int) $i + 1; ?>" data-revela data-activa style="--i:<?php echo (int) $i; ?>">
					<?php
					// Ancho real de cada pieza (la 6 llega a todo el ancho entre 901 y 1200 px): con 40vw para todas, la pieza ancha
					// cargaba la foto de 480 px estirada al doble
					$e_tam = array( 0 => '(max-width: 900px) 100vw, 46vw', 1 => '(max-width: 900px) 50vw, 46vw', 2 => '(max-width: 900px) 50vw, 46vw', 3 => '(max-width: 900px) 50vw, 30vw', 4 => '(max-width: 900px) 50vw, 60vw', 5 => '(max-width: 900px) 100vw, 92vw' );
					echo dipt_foto( $caso[2], '', array( 'sizes' => $e_tam[ $i ] ?? '(max-width: 900px) 100vw, 46vw' ) ); // phpcs:ignore
					?>
					<?php if ( $cliente ) : ?><span class="e-marbete"><?php echo dipt_logo_cliente( $cliente, $c['casos']['logos'][ $cliente ] ?? dipt_cliente_nombre( $cliente ) ); // phpcs:ignore ?></span><?php endif; ?>
					<div class="e-mosaico__franja"><strong><?php echo esc_html( $caso[0] ); ?></strong><?php if ( '' !== $caso[1] ) : ?><span><?php echo esc_html( $caso[1] ); ?></span><?php endif; ?></div>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="e-casos__nota suave"><?php echo esc_html( $c['casos']['nota'] ); ?></p>
	</div>
</section>

<!-- E6 · Alta en una frase: la hoja se monta sobre la foto (escritorio); en móvil, campos apilados con contador -->
<section class="e-alta tono-escarcha seccion" aria-labelledby="e-alta-t" id="alta">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['alta']['titulo'], 't-h2', 'e-alta-t', 'revela' ); // phpcs:ignore ?>
		<p class="entrada suave e-alta__texto"><?php echo esc_html( $c['alta']['texto'] ); ?></p>
		<div class="e-alta__mesa">
			<div class="e-alta__foto foto--sangre foto--sangre-der" data-cortina>
				<?php echo dipt_foto( 'uso-transporte', $c['alta']['alt'] ?? '', array( 'sizes' => '(max-width: 900px) 100vw, 70vw', 'recorte' => '62% 50%' ) /* 70vw: foto alta y estrecha con recorte «cover»; con 40vw cargaba la de 480 px estirada 2,4 veces */ ); // phpcs:ignore ?>
			</div>
			<form class="e-frase hoja" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['alta']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['alta']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error_usted' ) ); ?>" id="formulario" data-frase>
				<?php echo function_exists( 'dip_formulario_ocultos' ) ? dip_formulario_ocultos( 'cuenta' ) : ''; // phpcs:ignore ?>
				<?php $f = $c['alta']['frase']; ?>
				<p class="e-frase__cuenta dato" aria-hidden="true"><span data-frase-n>0</span>/<?php echo (int) floor( count( $f ) / 3 ); ?></p>
				<p class="e-frase__texto">
					<?php
					$f_n = count( $f );
					for ( $i = 0; $i < $f_n; $i += 3 ) {
						$campo = $f[ $i + 1 ];
						$ph    = $f[ $i + 2 ];
						$id    = 'e-f-' . $campo;
						// La puntuación que abre un trozo (". Necesitamos…") se esconde en móvil, donde cada trozo es la etiqueta de su campo
						$trozo = (string) $f[ $i ];
						$punto = '';
						if ( preg_match( '/^([.,;:]\s*)(.*)$/u', $trozo, $m ) ) { $punto = $m[1]; $trozo = $m[2]; }
						/* Cada trozo es la etiqueta visible de su campo (<label for>): al tocarlo se enfoca el campo y el lector de
						   pantalla lee lo mismo que se ve. El que va detrás de los kilos ("kg") es su unidad: en móvil se queda en la
						   fila de la cifra, y el desplegable que le sigue lleva su propia etiqueta oculta («Cada cuánto»). */
						$unidad = $i > 0 && 'kg' === $f[ $i - 2 ] && '' === $punto;
						$tag    = is_array( $ph ) ? 'span' : 'label';
						echo '<' . $tag . ' class="e-frase__trozo' . ( $unidad ? ' e-frase__trozo--unidad' : '' ) . '" id="' . esc_attr( $id ) . '-t"' . ( 'label' === $tag ? ' for="' . esc_attr( $id ) . '"' : '' ) . '>' . ( '' !== $punto ? '<span class="e-frase__punto">' . esc_html( $punto ) . '</span>' : '' ) . esc_html( $trozo ) . '</' . $tag . '> ';
						echo '<span class="frase__hueco frase__hueco--' . esc_attr( $campo ) . '">';
						if ( is_array( $ph ) ) {
							echo '<label class="visually-hidden" for="' . esc_attr( $id ) . '">' . esc_html( $c['alta']['frecuencia'] ?? $campo ) . '</label>';
							echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $campo ) . '">';
							foreach ( $ph as $op ) echo '<option>' . esc_html( $op ) . '</option>';
							echo '</select>';
						} else {
							$tipo = array( 'email' => 'email', 'telefono' => 'tel', 'kg' => 'number', 'cp' => 'text' )[ $campo ] ?? 'text';
							$extra = 'kg' === $campo ? ' min="1" inputmode="numeric"' : ( 'cp' === $campo ? ' inputmode="numeric" maxlength="5"' : '' );
							$auto  = array( 'empresa' => 'organization', 'nombre' => 'name', 'telefono' => 'tel', 'email' => 'email', 'cp' => 'postal-code' )[ $campo ] ?? 'off';
							$req   = in_array( $campo, array( 'empresa', 'nombre', 'telefono', 'email' ), true ) ? ' required' : '';
							// Los kilos se leen con su unidad: "Necesitamos unos … kg"
							$nombre = 'kg' === $campo && isset( $f[ $i + 4 ] ) ? ' aria-labelledby="' . esc_attr( $id . '-t e-f-' . $f[ $i + 4 ] . '-t' ) . '"' : '';
							echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $campo ) . '" type="' . esc_attr( $tipo ) . '" placeholder="' . esc_attr( $ph ) . '" autocomplete="' . esc_attr( $auto ) . '"' . $extra . $req . $nombre . '>'; // phpcs:ignore
						}
						// Sin espacio delante de la puntuación del trozo siguiente ("08302." y no "08302 .")
						echo '</span>' . ( preg_match( '/^[.,;:]/u', (string) ( $f[ $i + 3 ] ?? '' ) ) ? '' : ' ' );
					}
					?>
				</p>
				<div class="e-frase__pie">
					<p class="campo campo--corto"><label for="e-f-nif"><?php echo esc_html( $c['alta']['nif'] ); ?></label><input id="e-f-nif" name="nif" type="text" autocomplete="off"></p>
					<label class="casilla"><input type="checkbox" name="recordatorio" value="Sí"> <?php echo esc_html( $c['alta']['recordatorio'] ); ?></label>
					<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad_usted' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
					<button type="submit" class="boton boton--marino"><span><?php echo esc_html( $c['alta']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
					<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok_usted' ) : ( $error ? dipt_t( 'form_error_usted' ) : '' ) ); ?></p>
				</div>
			</form>
		</div>
	</div>
</section>

<!-- E7 · Preguntas (dipt_faq: título fijo y acordeón) -->
<section class="e-faq tono-papel seccion seccion--densa" aria-labelledby="e-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'e-faq-t', 'lado' => 'nada' ) ); // phpcs:ignore ?>
	</div>
</section>

<!-- E7b · Franja noche con el teléfono de ventas en grande, por grupos -->
<section class="e-final tono-oscuro" aria-labelledby="e-final-t" data-cierre>
	<div class="envoltura e-final__in">
		<h2 class="t-h3 e-final__titulo" id="e-final-t"><?php echo esc_html( $c['final']['titulo'] ); ?></h2>
		<a class="e-final__tel" href="<?php echo esc_attr( $con['telefono_href'] ); ?>" aria-label="<?php echo esc_attr( $con['telefono'] ); ?>" data-vivo><?php foreach ( $e_tel as $i => $g ) : ?><span style="--i:<?php echo (int) $i; ?>"><?php echo esc_html( $g ); ?></span> <?php endforeach; ?></a>
		<p class="e-final__texto"><?php echo esc_html( $c['final']['texto'] ); ?></p>
	</div>
</section>

<?php
get_footer();
