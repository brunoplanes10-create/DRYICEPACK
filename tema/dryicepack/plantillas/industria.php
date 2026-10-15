<?php
/**
 * Industria (rediseño 13/10/2026, plan 2.8): N1 −78,5 °C en contorno junto a la foto · N2 usos en un plano de 4 columnas
 * con la foto en una celda 2 × 2 y «+» que se dibujan en cada cruce · N3 cinta métrica de kilos (3 / 150 / 250) ·
 * placa de cliente a caballo (grande) · N4 expediente para compras · N5 presupuesto en noche a todo el ancho ·
 * N6 preguntas · cierre común.
 * Tonos: noche · escarcha · marino · (placa) · papel · noche · blanco · noche (cierre) · pie marino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'industria' );
$enviado = isset( $_GET['enviado'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$error   = isset( $_GET['error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-industria', '(max-width: 900px) 100vw, 50vw' );
$fds = dipt_contenido( 'seguridad' )['fds']['archivo'] ?? '';
// −78,5 en castellano y catalán, −78.5 en inglés (el contador de movimiento.js escribe con el mismo criterio)
$n_temp = 'en' === dipt_idioma() ? '−78.5' : '−78,5';
get_header();
?>

<!-- N1 · −78,5 °C en contorno junto a la foto (la foto es el LCP: no se anima) -->
<section class="n-hero tono-oscuro" aria-labelledby="n-hero-t">
	<div class="envoltura n-hero__migas">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'industria' ) ) ) ); // phpcs:ignore ?>
	</div>
	<div class="n-hero__foto"><?php echo dipt_foto( 'uso-industria', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?></div>
	<div class="envoltura n-hero__in">
		<h1 class="t-h1" id="n-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
		<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
		<?php echo dipt_boton( $c['hero']['boton'], '#presupuesto', 'hielo' ); // phpcs:ignore ?>
		<?php // Firma de marca: −78,5 °C en contorno, que baja desde 20 °C. La cifra final reserva el ancho (CLS 0). ?>
		<p class="n-hero__cifra">
			<span class="n-hero__temp" aria-hidden="true"><span class="cifra-fija n-hero__num"><span class="cifra-fija__reserva"><?php echo esc_html( $n_temp ); ?></span><span data-cuenta="-78.5" data-desde="20" data-decimales="1" data-duracion="1800"><?php echo esc_html( $n_temp ); ?></span></span><span class="n-hero__grados">°C</span></span>
			<small><span class="visually-hidden"><?php echo esc_html( $n_temp ); ?> °C: </span><?php echo esc_html( $c['hero']['temperatura'] ?? '' ); ?></small>
		</p>
	</div>
</section>

<!-- N2 · Usos en un plano: título y foto dentro de la retícula; «+» que se dibujan en los cruces -->
<section class="n-plano tono-escarcha seccion" aria-labelledby="n-plano-t">
	<div class="envoltura">
		<div class="n-plano__rejilla">
			<div class="n-plano__titulo"><?php echo dipt_titulo( 'h2', $c['usos']['titulo'], 't-h2', 'n-plano-t', 'revela' ); // phpcs:ignore ?></div>
			<div class="n-plano__foto" data-cortina><?php echo dipt_foto( 'uso-industria-pala', '', array( 'sizes' => '(max-width: 900px) 92vw, 46vw', 'recorte' => '42% 50%' ) ); // phpcs:ignore ?></div>
			<?php foreach ( $c['usos']['lista'] as $i => $u ) : ?>
				<article class="n-uso" data-activa data-revela style="--i:<?php echo (int) $i; ?>">
					<details class="n-uso__d" open>
						<summary><span class="n-uso__coord dato"><?php echo esc_html( $u[0] ); ?></span><h3><?php echo esc_html( $u[1] ); ?></h3><span class="n-uso__signo" aria-hidden="true"></span></summary>
						<p><?php echo esc_html( $u[2] ); ?></p>
					</details>
					<i class="n-plano__cruz" aria-hidden="true"></i>
				</article>
			<?php endforeach; ?>
		</div>
		<?php
		// En móvil (2 columnas) cada uso se pliega a su título y se abre al tocarlo. Antes de pintar: sin saltos (CLS 0).
		// En escritorio todo abierto y el resumen fuera del tabulador (no pliega). Si cambia el ancho (giro de la tableta),
		// se vuelve a aplicar: nunca queda un uso cerrado sin forma de abrirlo.
		?>
		<script nowprocket data-cfasync="false">(function(){var m=matchMedia('(max-width: 600px)');function f(){document.querySelectorAll('.n-uso__d').forEach(function(d){d.open=!m.matches;d.firstElementChild.tabIndex=m.matches?0:-1;});}f();if(m.addEventListener)m.addEventListener('change',f);})();</script>
	</div>
</section>

<?php
// N3 · Cinta métrica de 0 a 400: una marca cada 10 y una más larga cada 50 (dibujo propio, sin degradados).
// Cada hito se coloca en su punto de la cinta (--en = kg / 400); el último (400) es el suministro programado.
$n_marcas  = $c['escala']['marcas'];
$n_ticks_h = '';
$n_ticks_v = '';
for ( $k = 10; $k < 400; $k += 10 ) {
	$l          = 0 === $k % 50 ? 14 : 7;
	$n_ticks_h .= '<path d="M' . $k . ' 0V' . $l . '"/>';
	$n_ticks_v .= '<path d="M0 ' . $k . 'H' . $l . '"/>';
}
?>
<!-- N3 · Cinta métrica de kilos: la foto de las cajas sale por debajo de la cinta -->
<section class="n-escala tono-marino seccion" aria-labelledby="n-escala-t">
	<div class="envoltura">
		<div class="n-escala__cabeza">
			<?php echo dipt_titulo( 'h2', $c['escala']['titulo'], 't-h2', 'n-escala-t', 'cortina' ); // phpcs:ignore ?>
			<div class="n-escala__foto foto--sangre foto--sangre-der foto--4x3" data-cortina><?php echo dipt_foto( 'cajas-oscuro', '', array( 'sizes' => '(max-width: 900px) 92vw, 44vw' ) ); // phpcs:ignore ?></div>
		</div>
		<div class="n-cinta">
			<div class="n-cinta__banda" aria-hidden="true">
				<span class="n-cinta__lleno"></span>
				<svg class="n-cinta__marcas-h" viewBox="0 0 400 24" preserveAspectRatio="none" focusable="false"><?php echo str_replace( '/>', ' vector-effect="non-scaling-stroke"/>', $n_ticks_h ); // phpcs:ignore ?></svg>
				<svg class="n-cinta__marcas-v" viewBox="0 0 24 400" preserveAspectRatio="none" focusable="false"><?php echo str_replace( '/>', ' vector-effect="non-scaling-stroke"/>', $n_ticks_v ); // phpcs:ignore ?></svg>
			</div>
			<ol class="n-cinta__lista">
				<?php foreach ( $n_marcas as $i => $m ) : $kg = (int) $m[0]; ?>
					<li class="n-cinta__marca n-cinta__marca--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>; --en:<?php echo esc_attr( (string) round( min( 400, $kg ) / 400, 4 ) ); ?>">
						<?php if ( $kg < 400 ) : ?>
							<span class="n-cinta__cifra" aria-hidden="true"><span class="cifra-fija"><span class="cifra-fija__reserva"><?php echo esc_html( $kg ); ?></span><span data-cuenta="<?php echo esc_attr( $kg ); ?>" data-desde="0" data-duracion="1200"><?php echo esc_html( $kg ); ?></span></span><small>kg</small></span>
						<?php else : ?>
							<span class="n-cinta__cifra n-cinta__cifra--icono" aria-hidden="true"><?php echo dipt_icono( 'repetir' ); // phpcs:ignore ?></span>
						<?php endif; ?>
						<strong><?php echo esc_html( $m[1] ); ?></strong>
						<span class="n-cinta__texto"><?php echo esc_html( $m[2] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<?php echo dipt_placa_cliente( $c['cliente'], array( 'id' => 'n-cliente-t', 'arriba' => 'marino', 'abajo' => 'papel', 'grande' => true ) ); // phpcs:ignore ?>

<!-- N4 · Lo que pide compras: expediente (hoja con borde tinta y la ficha de datos de seguridad detrás) -->
<section class="n-compras tono-papel seccion" aria-labelledby="n-compras-t">
	<div class="envoltura n-compras__in">
		<?php echo dipt_titulo( 'h2', $c['compras']['titulo'], 't-h2', 'n-compras-t', 'revela' ); // phpcs:ignore ?>
		<div class="n-expediente" data-revela>
			<span class="n-expediente__detras" aria-hidden="true"><span class="dato"><?php echo esc_html( $c['compras']['fds'] ); ?></span></span>
			<div class="n-expediente__hoja">
				<ul>
					<?php foreach ( $c['compras']['lista'] as $i => $l ) : ?>
						<li style="--i:<?php echo (int) $i; ?>"><span class="n-expediente__check" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span><strong><?php echo esc_html( $l[0] ); ?></strong><span><?php echo esc_html( $l[1] ); ?></span></li>
					<?php endforeach; ?>
				</ul>
				<?php if ( $fds ) : ?><p class="n-expediente__fds"><?php echo dipt_enlace( $c['compras']['fds'], home_url( $fds ), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p><?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- N5 · Presupuesto: noche a todo el ancho -->
<section class="n-form tono-oscuro seccion" aria-labelledby="n-form-t" id="presupuesto">
	<div class="envoltura">
		<form class="n-tarjeta" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-form-dip data-enviando="<?php echo esc_attr( $c['form']['enviando'] ); ?>" data-enviar="<?php echo esc_attr( $c['form']['boton'] ); ?>" data-error="<?php echo esc_attr( dipt_t( 'form_error_usted' ) ); ?>" id="formulario">
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
			<label class="casilla"><input type="checkbox" name="privacidad" value="1" required> <span><?php echo wp_kses( sprintf( dipt_t( 'form_privacidad_usted' ), esc_url( dipt_url( 'privacidad' ) ) ), array( 'a' => array( 'href' => array() ) ) ); ?></span></label>
			<button type="submit" class="boton boton--hielo"><span><?php echo esc_html( $c['form']['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
			<p class="aviso-form<?php echo $enviado ? ' aviso-form--ok' : ( $error ? ' aviso-form--error' : '' ); ?>" data-form-aviso role="status" tabindex="-1"<?php echo ( $enviado || $error ) ? '' : ' hidden'; ?>><?php echo esc_html( $enviado ? dipt_t( 'form_ok_usted' ) : ( $error ? dipt_t( 'form_error_usted' ) : '' ) ); ?></p>
		</form>
	</div>
</section>

<!-- N6 · Preguntas -->
<section class="n-faq seccion seccion--densa tono-blanco" aria-labelledby="n-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'n-faq-t' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php
// Cierre común: pide presupuesto (no es la compra online de la ficha). El enlace a seguridad va en el cierre.
dipt_cierre( array(
	'titulo'    => $c['final']['titulo'] ?? '',
	'id'        => 'n-cierre-t',
	'foto'      => 'cenital-caja-nuggets',
	'recorte'   => '50% 60%',
	'boton'     => $c['hero']['boton'],
	'boton_url' => '#presupuesto',
	'corte'     => false,
) );
get_footer();
