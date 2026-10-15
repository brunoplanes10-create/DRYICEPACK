<?php
/**
 * Contacto (rediseño del 14/10/2026, plan 2.6). Tonos: C1 papel · C2 noche · C3 papel · pie marino.
 * C1 canales en tipografía gigante con el estado "abierto / cerrado" en directo (la hoja sube con el ratón, al tocar
 * y al cruzar el centro de la pantalla) · C2 formulario en una hoja montada sobre la foto · C3 la nave: dirección
 * en grande montada sobre la furgoneta y ficha en mono.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'contacto' );
$con = dipt_contacto();
$enviado = isset( $_GET['enviado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error   = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$hrefs = array(
	'telefono' => array( $con['telefono_href'], $con['telefono'], '' ),
	'whatsapp' => array( dipt_whatsapp_url(), $con['whatsapp'], ' target="_blank" rel="noopener"' ),
	'email'    => array( 'mailto:' . $con['email'], $con['email'], '' ),
);
get_header();
?>

<!-- C1 · Canales en tipografía gigante -->
<section class="c-hero" aria-labelledby="c-hero-t">
	<div class="envoltura">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'contacto' ) ) ) ); // phpcs:ignore ?>
		<div class="c-hero__cabeza">
			<h1 class="t-gigante" id="c-hero-t"><?php echo esc_html( $c['titulo'] ); ?></h1>
			<?php /* El estado cambia de texto con JS (abierto / cerrado, hasta 5 líneas en móvil): los dos textos invisibles en la
			   misma celda reservan el alto del más largo y la lista de canales no salta (CLS 0,017 en Lighthouse) */ ?>
			<div class="c-estado-caja">
				<p class="c-estado" data-estado data-t-abierto="<?php echo esc_attr( $c['estado']['abierto'] ); ?>" data-t-cerrado="<?php echo esc_attr( $c['estado']['cerrado'] ); ?>"><?php echo esc_html( dipt_t( 'horario' ) ); ?></p>
				<span class="c-estado c-estado__reserva" aria-hidden="true"><?php echo esc_html( $c['estado']['cerrado'] ); ?></span>
				<span class="c-estado c-estado__reserva" aria-hidden="true"><?php echo esc_html( $c['estado']['abierto'] ); ?></span>
			</div>
		</div>
		<ul class="c-canales">
			<?php foreach ( $c['canales'] as $i => $canal ) : $h = $hrefs[ $canal[0] ]; ?>
				<li data-revela data-activa style="--i:<?php echo (int) $i; ?>">
					<a href="<?php echo esc_attr( $h[0] ); ?>"<?php echo $h[2]; // phpcs:ignore ?>>
						<span class="c-canales__tipo"><?php echo dipt_icono( $canal[0] ); // phpcs:ignore ?><?php echo esc_html( $canal[1] ); ?></span>
						<span class="c-canales__valor"><?php echo esc_html( $h[1] ); ?></span>
						<span class="c-canales__para"><?php echo esc_html( $canal[2] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- C2 · Formulario: hoja montada sobre la foto (escritorio, 5 rem; móvil, franja de 200 px y 3 rem) -->
<section class="c-form tono-oscuro seccion" aria-labelledby="c-form-t" id="formulario">
	<div class="envoltura c-form__in">
		<div class="c-form__cabeza">
			<?php echo dipt_titulo( 'h2', $c['form']['titulo'], 't-h2', 'c-form-t' ); // phpcs:ignore ?>
			<p class="entrada c-form__texto" data-revela><?php echo esc_html( $c['form']['texto'] ); ?></p>
		</div>
		<div class="c-form__foto foto--sangre foto--sangre-izq" data-cortina>
			<?php echo dipt_foto( 'anadiendo-hielo', $c['form']['alt'] ?? '', array( 'sizes' => '(max-width: 900px) 100vw, 50vw', 'recorte' => '45% 50%' ) ); // phpcs:ignore ?>
		</div>
		<form class="c-lineas hoja hoja--lado" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['form']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['form']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error' ) ); ?>">
			<?php if ( ! empty( $c['form']['linea'] ) ) : ?><p class="c-lineas__linea dato" aria-hidden="true"><?php echo esc_html( $c['form']['linea'] ); ?></p><?php endif; ?>
			<?php echo function_exists( 'dip_formulario_ocultos' ) ? dip_formulario_ocultos( 'contacto' ) : ''; // phpcs:ignore ?>
			<p class="campo"><input id="c-nombre" name="nombre" type="text" autocomplete="name" placeholder=" " required><label for="c-nombre"><?php echo esc_html( $c['form']['nombre'] ); ?></label></p>
			<div class="c-lineas__doble">
				<p class="campo"><input id="c-email" name="email" type="email" autocomplete="email" placeholder=" "><label for="c-email"><?php echo esc_html( $c['form']['email'] ); ?></label></p>
				<p class="campo"><input id="c-tel" name="telefono" type="tel" autocomplete="tel" placeholder=" "><label for="c-tel"><?php echo esc_html( $c['form']['telefono'] ); ?></label></p>
			</div>
			<p class="campo"><textarea id="c-mensaje" name="mensaje" rows="4" placeholder=" " required></textarea><label for="c-mensaje"><?php echo esc_html( $c['form']['mensaje'] ); ?></label></p>
			<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
			<button type="submit" class="boton boton--marino"><span><?php echo esc_html( $c['form']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
			<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok' ) : ( $error ? dipt_t( 'form_error' ) : '' ) ); ?></p>
		</form>
	</div>
</section>

<!-- C3 · La nave: dirección en grande montada sobre la furgoneta (paralaje 0,08) y ficha en mono de dos columnas -->
<section class="c-nave tono-papel seccion" aria-labelledby="c-nave-t">
	<div class="envoltura c-nave__in">
		<div class="c-nave__foto foto--sangre foto--sangre-der">
			<div class="c-nave__marco" data-paralaje="0.08"><?php echo dipt_foto( 'reparto-furgoneta', $c['nave']['alt_reparto'] ?? '', array( 'sizes' => '(max-width: 900px) 100vw, 72vw', 'recorte' => '15% 45%' ) /* el marco del paralaje es un 36 % más alto que la foto; 15 %: el logotipo de la chaqueta queda a la derecha de la dirección (con 40 %, la línea «19 D» lo tapaba) */ ); // phpcs:ignore ?></div>
		</div>
		<div class="c-nave__cabeza">
			<?php echo dipt_titulo( 'h2', $c['nave']['titulo'], 't-h3 c-nave__titulo', 'c-nave-t', 'revela' ); // phpcs:ignore ?>
			<address class="c-nave__dir t-h1" data-revela style="--i:1">
				<span><?php echo esc_html( $con['direccion'] ); ?></span><br><span><?php echo esc_html( $con['cp'] . ' ' . $con['localidad'] ); ?></span><br><span><?php echo esc_html( $con['provincia'] ); ?></span>
			</address>
		</div>
		<div class="c-nave__ficha">
			<dl class="c-nave__lista">
				<div data-revela><dt><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?></dt><dd><?php echo esc_html( $c['nave']['horario'] ); ?></dd></div>
				<div data-revela style="--i:1"><dt><?php echo dipt_icono( 'nave' ); // phpcs:ignore ?></dt><dd><?php echo esc_html( $c['nave']['recoger'] ); ?></dd></div>
			</dl>
			<div class="c-nave__empresa" data-revela style="--i:2">
				<p class="c-nave__etiqueta"><?php echo esc_html( $c['nave']['empresa'] ); ?></p>
				<p><?php echo esc_html( $con['empresa'] ); ?> · CIF <?php echo esc_html( $con['cif'] ); ?></p>
				<p class="c-nave__como"><?php echo dipt_enlace( $c['nave']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
