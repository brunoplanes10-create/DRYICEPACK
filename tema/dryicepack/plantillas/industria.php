<?php
/**
 * Industria: N1 cifra gigante sobre foto · N2 usos en plano técnico con coordenadas · N3 escala de kilos ·
 * N4 lo que pide compras (lista de control) · N5 presupuesto en tarjeta oscura · N6 preguntas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'industria' );
$enviado = isset( $_GET['enviado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error   = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-industria', '(max-width: 900px) 100vw, 55vw' );
dipt_registrar_faq( $c['faq']['lista'] );
$fds = dipt_contenido( 'seguridad' )['fds']['archivo'] ?? '';
get_header();
?>

<!-- N1 · Cifra gigante sobre foto -->
<section class="n-hero tono-oscuro" aria-labelledby="n-hero-t">
	<div class="n-hero__foto"><?php echo dipt_foto( 'uso-industria', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 55vw' ) ); // phpcs:ignore ?></div>
	<div class="envoltura n-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'industria' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1" id="n-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
		<?php echo dipt_boton( $c['hero']['boton'], '#presupuesto', 'hielo' ); // phpcs:ignore ?>
		<p class="n-hero__cifra" aria-hidden="true"><span data-cuenta="<?php echo esc_attr( $c['hero']['cifra'] ); ?>" data-desde="0" data-duracion="1800"><?php echo esc_html( $c['hero']['cifra'] ); ?></span><small><?php echo esc_html( $c['hero']['unidad'] ); ?></small></p>
	</div>
</section>

<!-- N2 · Usos en plano técnico -->
<section class="n-plano seccion" aria-labelledby="n-plano-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['usos']['titulo'], 't-h2', 'n-plano-t' ); // phpcs:ignore ?>
		<ul class="n-plano__rejilla">
			<?php foreach ( $c['usos']['lista'] as $i => $u ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="n-plano__coord dato"><?php echo esc_html( $u[0] ); ?></span><h3><?php echo esc_html( $u[1] ); ?></h3><p><?php echo esc_html( $u[2] ); ?></p></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- N3 · Escala de kilos -->
<section class="n-escala tono-marino seccion" aria-labelledby="n-escala-t" data-vivo>
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['escala']['titulo'], 't-h2', 'n-escala-t' ); // phpcs:ignore ?>
		<div class="n-regla">
			<div class="n-regla__barra" aria-hidden="true"><span></span></div>
			<ol class="n-regla__marcas">
				<?php foreach ( $c['escala']['marcas'] as $i => $m ) : ?>
					<li style="--pos:<?php echo esc_attr( min( 100, $m[0] / 400 * 100 ) ); ?>%;--i:<?php echo (int) $i; ?>" data-revela><strong><?php echo esc_html( $m[1] ); ?></strong><span><?php echo esc_html( $m[2] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<!-- N4 · Lo que pide compras -->
<section class="n-compras tono-papel seccion" aria-labelledby="n-compras-t">
	<div class="envoltura n-compras__in">
		<?php echo dipt_titulo( 'h2', $c['compras']['titulo'], 't-h2', 'n-compras-t' ); // phpcs:ignore ?>
		<div class="n-portapapeles" data-revela>
			<span class="n-portapapeles__pinza" aria-hidden="true"></span>
			<ul>
				<?php foreach ( $c['compras']['lista'] as $l ) : ?>
					<li><span class="n-portapapeles__check" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span><strong><?php echo esc_html( $l[0] ); ?></strong><span><?php echo esc_html( $l[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $fds ) : ?><p><?php echo dipt_enlace( $c['compras']['fds'], home_url( $fds ), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p><?php endif; ?>
		</div>
	</div>
</section>

<!-- N5 · Presupuesto -->
<section class="n-form tono-blanco seccion" aria-labelledby="n-form-t" id="presupuesto">
	<div class="envoltura">
		<form class="n-tarjeta tono-oscuro" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['form']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['form']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error' ) ); ?>" id="formulario">
			<?php echo function_exists( 'dip_formulario_ocultos' ) ? dip_formulario_ocultos( 'presupuesto' ) : ''; // phpcs:ignore ?>
			<div class="n-tarjeta__cabeza">
				<h2 class="t-h2" id="n-form-t"><?php echo esc_html( $c['form']['titulo'] ); ?></h2>
				<p><?php echo esc_html( $c['form']['texto'] ); ?></p>
			</div>
			<div class="n-tarjeta__campos">
				<?php
				$tipos = array( 'kg' => 'number', 'fecha' => 'date', 'cp' => 'text', 'email' => 'email', 'telefono' => 'tel' );
				foreach ( $c['form']['campos'] as $nombre => $etq ) :
					$req = in_array( $nombre, array( 'kg', 'nombre', 'telefono' ), true ) ? ' required' : '';
					?>
					<p class="campo"><label for="n-<?php echo esc_attr( $nombre ); ?>"><?php echo esc_html( $etq ); ?></label><input id="n-<?php echo esc_attr( $nombre ); ?>" name="<?php echo esc_attr( $nombre ); ?>" type="<?php echo esc_attr( $tipos[ $nombre ] ?? 'text' ); ?>"<?php echo $req; // phpcs:ignore ?>></p>
				<?php endforeach; ?>
			</div>
			<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
			<button type="submit" class="boton boton--hielo"><span><?php echo esc_html( $c['form']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
			<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok' ) : ( $error ? dipt_t( 'form_error' ) : '' ) ); ?></p>
		</form>
	</div>
</section>

<!-- N6 · Preguntas -->
<section class="n-faq tono-escarcha seccion" aria-labelledby="n-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="n-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<dl class="n-faq__lista">
			<?php foreach ( $c['faq']['lista'] as $f ) : ?>
				<div data-revela><dt><?php echo esc_html( $f[0] ); ?></dt><dd><?php echo esc_html( $f[1] ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<?php
get_footer();
