<?php
/**
 * Empresas: E1 hero con foto vertical · E2 tres formas de pedir (pantallas) · E3 ventajas en lista gigante ·
 * E4 pila de cajas que crece con los kilos · E5 mosaico de usos reales · E6 alta en una frase · E7 preguntas abiertas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'empresas' );
$con = dipt_contacto();
dipt_precargar( 'almacen-caja', '(max-width: 900px) 100vw, 40vw' );
dipt_registrar_faq( $c['faq']['lista'] );
$enviado = isset( $_GET['enviado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error   = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
get_header();
?>

<!-- E1 · Hero con foto vertical recortada -->
<section class="e-hero" aria-labelledby="e-hero-t">
	<div class="envoltura e-hero__in">
		<div class="e-hero__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'empresas' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1 e-hero__titulo" id="e-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<div class="e-hero__acciones">
				<?php echo dipt_boton( $c['hero']['boton'], '#alta' ); // phpcs:ignore ?>
				<p><?php echo esc_html( $c['hero']['tel'] ); ?> <a class="tel-grande" href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a></p>
			</div>
		</div>
		<figure class="e-hero__foto">
			<?php echo dipt_foto( 'almacen-caja', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 40vw' ) ); // phpcs:ignore ?>
			<figcaption class="e-hero__sello dato" aria-hidden="true"><span>−78,5 °C</span><span>B2B</span></figcaption>
		</figure>
	</div>
</section>

<!-- E2 · Tres formas de pedir -->
<section class="e-pedir tono-oscuro seccion" aria-labelledby="e-pedir-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['pedir']['titulo'], 't-h2 e-pedir__titulo', 'e-pedir-t' ); // phpcs:ignore ?>
		<div class="e-pantallas">
			<article class="e-pantalla e-pantalla--wa" data-revela style="--i:0">
				<div class="e-movil" aria-hidden="true">
					<p class="e-movil__barra"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?> DryIcePack</p>
					<p class="e-burbuja e-burbuja--yo"><?php echo esc_html( $c['pedir']['chat'][0] ); ?></p>
					<p class="e-burbuja"><?php echo esc_html( $c['pedir']['chat'][1] ); ?></p>
				</div>
				<h3><?php echo esc_html( $c['pedir']['canales'][0][0] ); ?></h3>
				<p><?php echo esc_html( $c['pedir']['canales'][0][1] ); ?></p>
			</article>
			<article class="e-pantalla e-pantalla--web" data-revela style="--i:1">
				<div class="e-navegador" aria-hidden="true">
					<p class="e-navegador__barra"><i></i><i></i><i></i></p>
					<p class="e-navegador__titulo"><?php echo esc_html( $c['pedir']['repetir']['titulo'] ); ?></p>
					<?php foreach ( $c['pedir']['repetir']['filas'] as $i => $f ) : ?>
						<p class="e-navegador__fila"><span><?php echo esc_html( $f[0] ); ?></span><span class="e-navegador__boton<?php echo 0 === $i ? ' activo' : ''; ?>"><?php echo dipt_icono( 'repetir' ); // phpcs:ignore ?><?php echo esc_html( $f[1] ); ?></span></p>
					<?php endforeach; ?>
				</div>
				<h3><?php echo esc_html( $c['pedir']['canales'][1][0] ); ?></h3>
				<p><?php echo esc_html( $c['pedir']['canales'][1][1] ); ?></p>
			</article>
			<article class="e-pantalla e-pantalla--email" data-revela style="--i:2">
				<div class="e-carta" aria-hidden="true">
					<?php foreach ( $c['pedir']['email'] as $i => $l ) : ?><p class="e-carta__l<?php echo 2 === $i ? ' e-carta__cuerpo' : ''; ?>"><?php echo esc_html( $l ); ?></p><?php endforeach; ?>
				</div>
				<h3><?php echo esc_html( $c['pedir']['canales'][2][0] ); ?></h3>
				<p><?php echo esc_html( $c['pedir']['canales'][2][1] ); ?></p>
			</article>
		</div>
	</div>
</section>

<!-- E3 · Ventajas en lista numerada gigante -->
<section class="e-ventajas tono-papel seccion" aria-labelledby="e-ventajas-t">
	<div class="envoltura e-ventajas__in">
		<div class="e-ventajas__cabeza"><?php echo dipt_titulo( 'h2', $c['ventajas']['titulo'], 't-h2', 'e-ventajas-t' ); // phpcs:ignore ?></div>
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

<!-- E4 · Pila de cajas que crece con los kilos -->
<section class="e-volumen tono-marino seccion" aria-labelledby="e-volumen-t" data-volumen data-t-cajas="<?php echo esc_attr( $c['volumen']['cajas'] ); ?>" data-t-caja="<?php echo esc_attr( $c['volumen']['caja'] ); ?>">
	<div class="envoltura e-volumen__in">
		<div class="e-volumen__control">
			<?php echo dipt_titulo( 'h2', $c['volumen']['titulo'], 't-h2', 'e-volumen-t' ); // phpcs:ignore ?>
			<label class="e-volumen__etiqueta dato" for="e-kg"><?php echo esc_html( $c['volumen']['rango'] ); ?></label>
			<p class="e-volumen__cifra"><output for="e-kg" data-volumen-kg>60</output><span>kg</span></p>
			<input class="e-volumen__rango" id="e-kg" type="range" min="3" max="400" step="1" value="60" data-volumen-rango>
			<p class="e-volumen__cajas dato" data-volumen-cajas><?php echo esc_html( sprintf( $c['volumen']['cajas'], 3, 60 ) ); ?></p>
			<ul class="e-volumen__notas">
				<li data-nota="web"><?php echo esc_html( $c['volumen']['notas']['web'] ); ?></li>
				<li data-nota="bcn"><?php echo esc_html( $c['volumen']['notas']['bcn'] ); ?></li>
				<li data-nota="fuera"><?php echo esc_html( $c['volumen']['notas']['fuera'] ); ?></li>
			</ul>
		</div>
		<div class="e-pila" aria-hidden="true">
			<?php for ( $i = 0; $i < 20; $i++ ) : ?><span class="e-pila__caja" style="--i:<?php echo (int) $i; ?>"></span><?php endfor; ?>
		</div>
	</div>
</section>

<!-- E5 · Usos reales en mosaico -->
<section class="e-casos tono-blanco seccion" aria-labelledby="e-casos-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['casos']['titulo'], 't-h2 e-casos__titulo', 'e-casos-t' ); // phpcs:ignore ?>
		<ul class="e-mosaico">
			<?php foreach ( $c['casos']['lista'] as $i => $caso ) : ?>
				<li class="e-mosaico__pieza e-mosaico__pieza--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<?php echo dipt_foto( $caso[2], '', array( 'sizes' => '(max-width: 900px) 100vw, 40vw' ) ); // phpcs:ignore ?>
					<div><strong><?php echo esc_html( $caso[0] ); ?></strong><span><?php echo esc_html( $caso[1] ); ?></span></div>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="e-casos__nota suave dato"><?php echo esc_html( $c['casos']['nota'] ); ?></p>
	</div>
</section>

<!-- E6 · Alta en una frase -->
<section class="e-alta tono-escarcha seccion" aria-labelledby="e-alta-t" id="alta">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['alta']['titulo'], 't-h2', 'e-alta-t' ); // phpcs:ignore ?>
		<p class="entrada suave e-alta__texto"><?php echo esc_html( $c['alta']['texto'] ); ?></p>
		<form class="e-frase" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['alta']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['alta']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error' ) ); ?>" id="formulario">
			<?php echo function_exists( 'dip_formulario_ocultos' ) ? dip_formulario_ocultos( 'cuenta' ) : ''; // phpcs:ignore ?>
			<p class="e-frase__texto">
				<?php
				$f = $c['alta']['frase'];
				for ( $i = 0; $i < count( $f ); $i += 3 ) {
					echo '<span class="e-frase__trozo">' . esc_html( $f[ $i ] ) . '</span> ';
					$campo = $f[ $i + 1 ];
					$ph    = $f[ $i + 2 ];
					$id    = 'e-f-' . $campo;
					echo '<span class="frase__hueco frase__hueco--' . esc_attr( $campo ) . '">';
					echo '<label class="visually-hidden" for="' . esc_attr( $id ) . '">' . esc_html( is_array( $ph ) ? $campo : $ph ) . '</label>';
					if ( is_array( $ph ) ) {
						echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $campo ) . '">';
						foreach ( $ph as $op ) echo '<option>' . esc_html( $op ) . '</option>';
						echo '</select>';
					} else {
						$tipo = array( 'email' => 'email', 'telefono' => 'tel', 'kg' => 'number', 'cp' => 'text' )[ $campo ] ?? 'text';
						$extra = 'kg' === $campo ? ' min="1" inputmode="numeric"' : ( 'cp' === $campo ? ' inputmode="numeric" maxlength="5"' : '' );
						$auto  = array( 'empresa' => 'organization', 'nombre' => 'name', 'telefono' => 'tel', 'email' => 'email', 'cp' => 'postal-code' )[ $campo ] ?? 'off';
						$req   = in_array( $campo, array( 'empresa', 'nombre', 'telefono', 'email' ), true ) ? ' required' : '';
						echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $campo ) . '" type="' . esc_attr( $tipo ) . '" placeholder="' . esc_attr( $ph ) . '" autocomplete="' . esc_attr( $auto ) . '"' . $extra . $req . '>'; // phpcs:ignore
					}
					echo '</span> ';
				}
				?>
			</p>
			<div class="e-frase__pie">
				<p class="campo campo--corto"><label for="e-f-nif"><?php echo esc_html( $c['alta']['nif'] ); ?></label><input id="e-f-nif" name="nif" type="text" autocomplete="off"></p>
				<label class="casilla"><input type="checkbox" name="recordatorio" value="Sí"> <?php echo esc_html( $c['alta']['recordatorio'] ); ?></label>
				<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
				<button type="submit" class="boton"><span><?php echo esc_html( $c['alta']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
				<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok' ) : ( $error ? dipt_t( 'form_error' ) : '' ) ); ?></p>
			</div>
		</form>
	</div>
</section>

<!-- E7 · Preguntas abiertas en dos columnas -->
<section class="e-faq tono-blanco seccion" aria-labelledby="e-faq-t">
	<div class="envoltura">
		<h2 class="t-h3 e-faq__titulo" id="e-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<dl class="e-faq__lista">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<div data-revela style="--i:<?php echo (int) $i % 2; ?>"><dt><?php echo esc_html( $f[0] ); ?></dt><dd><?php echo esc_html( $f[1] ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
		<div class="e-faq__final">
			<p class="t-h3"><?php echo esc_html( $c['final']['titulo'] ); ?></p>
			<a class="tel-grande" href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a>
			<p class="suave"><?php echo esc_html( $c['final']['texto'] ); ?></p>
		</div>
	</div>
</section>

<?php
get_footer();
