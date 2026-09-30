<?php
/**
 * Contacto: C1 canales en tipografía gigante con estado "abierto / cerrado" en directo ·
 * C2 formulario de líneas · C3 ficha de la nave con foto y datos de empresa.
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
			<p class="c-estado dato" data-estado data-t-abierto="<?php echo esc_attr( $c['estado']['abierto'] ); ?>" data-t-cerrado="<?php echo esc_attr( $c['estado']['cerrado'] ); ?>"><?php echo esc_html( dipt_t( 'horario' ) ); ?></p>
		</div>
		<ul class="c-canales">
			<?php foreach ( $c['canales'] as $i => $canal ) : $h = $hrefs[ $canal[0] ]; ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
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

<!-- C2 · Formulario de líneas -->
<section class="c-form tono-blanco seccion" aria-labelledby="c-form-t" id="formulario">
	<div class="envoltura c-form__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['form']['titulo'], 't-h2', 'c-form-t' ); // phpcs:ignore ?>
			<p class="entrada suave c-form__texto" data-revela><?php echo esc_html( $c['form']['texto'] ); ?></p>
		</div>
		<form class="c-lineas" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['form']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['form']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error' ) ); ?>">
			<?php echo function_exists( 'dip_formulario_ocultos' ) ? dip_formulario_ocultos( 'contacto' ) : ''; // phpcs:ignore ?>
			<p class="campo"><input id="c-nombre" name="nombre" type="text" autocomplete="name" placeholder=" " required><label for="c-nombre"><?php echo esc_html( $c['form']['nombre'] ); ?></label></p>
			<div class="c-lineas__doble">
				<p class="campo"><input id="c-email" name="email" type="email" autocomplete="email" placeholder=" "><label for="c-email"><?php echo esc_html( $c['form']['email'] ); ?></label></p>
				<p class="campo"><input id="c-tel" name="telefono" type="tel" autocomplete="tel" placeholder=" "><label for="c-tel"><?php echo esc_html( $c['form']['telefono'] ); ?></label></p>
			</div>
			<p class="campo"><textarea id="c-mensaje" name="mensaje" rows="4" placeholder=" " required></textarea><label for="c-mensaje"><?php echo esc_html( $c['form']['mensaje'] ); ?></label></p>
			<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
			<button type="submit" class="boton"><span><?php echo esc_html( $c['form']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
			<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok' ) : ( $error ? dipt_t( 'form_error' ) : '' ) ); ?></p>
		</form>
	</div>
</section>

<!-- C3 · La nave -->
<section class="c-nave tono-oscuro" aria-labelledby="c-nave-t">
	<div class="c-nave__foto" data-cortina style="--cortina: var(--c-noche)"><?php echo dipt_foto( 'cajas-furgoneta', $c['nave']['alt'], array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?></div>
	<div class="c-nave__datos">
		<h2 class="t-h2" id="c-nave-t" data-revela><?php echo esc_html( $c['nave']['titulo'] ); ?></h2>
		<address class="c-nave__dir" data-revela>
			<?php echo esc_html( $con['direccion'] ); ?><br><?php echo esc_html( $con['cp'] . ' ' . $con['localidad'] ); ?><br><?php echo esc_html( $con['provincia'] ); ?>
		</address>
		<dl class="c-nave__lista dato">
			<div><dt><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?></dt><dd><?php echo esc_html( $c['nave']['horario'] ); ?></dd></div>
			<div><dt><?php echo dipt_icono( 'nave' ); // phpcs:ignore ?></dt><dd><?php echo esc_html( $c['nave']['recoger'] ); ?></dd></div>
		</dl>
		<p><?php echo dipt_enlace( $c['nave']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p>
		<div class="c-nave__empresa">
			<p class="dato"><?php echo esc_html( $c['nave']['empresa'] ); ?></p>
			<p><?php echo esc_html( $con['empresa'] ); ?> · CIF <?php echo esc_html( $con['cif'] ); ?></p>
		</div>
	</div>
</section>

<?php
get_footer();
