<?php
/**
 * Envíos y plazos: V1 cuenta atrás del corte a segundos · V2 semana con flechas de pedido a entrega ·
 * V3 calculadora de envío con escalones de tarifa · V4 recogida con foto y lista de ficha ·
 * V5 cobertura sí / consulta · V6 preguntas en pestañas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'envios' );
$con = dipt_contacto();
$iva = dipt_factor_iva();
$e0  = dipt_primera_entrega();
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- V1 · Cuenta atrás del corte -->
<section class="v-hero tono-oscuro" aria-labelledby="v-hero-t" data-cuenta-atras data-t-antes="<?php echo esc_attr( $c['hero']['reloj']['antes'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['reloj']['despues'] ); ?>">
	<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura v-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'envios' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1 v-hero__titulo" id="v-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<div class="v-reloj" aria-live="off">
			<p class="v-reloj__etiqueta dato" data-reloj-etiqueta><?php echo esc_html( $c['hero']['reloj']['antes'] ); ?></p>
			<p class="v-reloj__cifras" data-reloj-cifras aria-hidden="true"><span data-h>00</span><i>:</i><span data-m>00</span><i>:</i><span data-s>00</span></p>
		</div>
		<p class="entrada v-hero__llega" data-corte data-t-antes="<?php echo esc_attr( $c['hero']['llega']['antes'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['llega']['despues'] ); ?>"><span data-corte-texto><?php echo esc_html( sprintf( $c['hero']['llega']['despues'], $e0 ? dipt_fecha_larga( $e0['fecha'] ) : '' ) ); ?></span></p>
		<p class="v-hero__texto suave"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
	</div>
</section>

<!-- V2 · La semana: de qué día pides a qué día llega -->
<section class="v-semana tono-papel seccion" aria-labelledby="v-semana-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['semana']['titulo'], 't-h2 v-semana__titulo', 'v-semana-t' ); // phpcs:ignore ?>
		<div class="v-calendario" role="table" aria-label="<?php echo esc_attr( wp_strip_all_tags( str_replace( '|', ' ', $c['semana']['titulo'] ) ) ); ?>">
			<div class="v-calendario__cab" role="row">
				<?php foreach ( $c['semana']['dias'] as $i => $dia ) : ?>
					<span role="columnheader" class="<?php echo $i >= 5 ? 'finde' : ''; ?>"><?php echo esc_html( $dia ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $c['semana']['filas'] as $i => $f ) : ?>
				<div class="v-calendario__fila" role="row" data-revela style="--i:<?php echo (int) $i; ?>;--desde:<?php echo (int) $f[0] + 1; ?>;--hasta:<?php echo (int) $f[1] + 1; ?>">
					<span class="v-calendario__pide" role="cell"><span class="visually-hidden"><?php echo esc_html( $c['semana']['pides'] . ': ' . $c['semana']['dias'][ $f[0] ] ); ?></span><?php echo dipt_icono( 'carrito' ); // phpcs:ignore ?></span>
					<span class="v-calendario__viaje" aria-hidden="true"></span>
					<span class="v-calendario__llega<?php echo 5 === $f[1] ? ' es-sabado' : ''; ?>" role="cell"><span class="visually-hidden"><?php echo esc_html( $c['semana']['llega'] . ': ' . $c['semana']['dias'][ $f[1] ] ); ?></span><?php echo dipt_icono( 'caja' ); // phpcs:ignore ?></span>
					<?php if ( $f[2] ) : ?><span class="v-calendario__nota dato" role="cell"><?php echo esc_html( $f[2] ); ?></span><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="v-semana__notas">
			<p><?php echo esc_html( $c['semana']['finde'] ); ?></p>
			<p class="suave"><?php echo esc_html( $c['semana']['festivos'] ); ?></p>
		</div>
	</div>
</section>

<!-- V3 · Calculadora de envío -->
<section class="v-tarifa tono-blanco seccion" aria-labelledby="v-tarifa-t" data-calc-envio data-t-peso="<?php echo esc_attr( $c['tarifa']['peso'] ); ?>">
	<div class="envoltura v-tarifa__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['tarifa']['titulo'], 't-h2', 'v-tarifa-t' ); // phpcs:ignore ?>
			<div class="v-tarifa__controles">
				<label class="dato" for="v-kg"><?php echo esc_html( $c['tarifa']['kg'] ); ?> <output data-calc-kg>10</output> kg</label>
				<input id="v-kg" type="range" min="1" max="250" value="10" data-calc-rango>
				<label class="dato" for="v-cajas"><?php echo esc_html( $c['tarifa']['cajas'] ); ?></label>
				<input id="v-cajas" type="number" min="1" max="50" value="1" inputmode="numeric" data-calc-cajas>
			</div>
			<p class="suave v-tarifa__nota"><?php echo esc_html( $c['tarifa']['nota'] ); ?></p>
		</div>
		<div class="v-tarifa__resultado">
			<p class="v-tarifa__coste"><span class="dato"><?php echo esc_html( $c['tarifa']['coste'] ); ?></span><strong data-calc-coste><?php echo esc_html( dipt_euros( dipt_coste_envio( 10, 1 ) * $iva ) ); ?></strong></p>
			<p class="dato v-tarifa__peso" data-calc-peso><?php echo esc_html( sprintf( $c['tarifa']['peso'], 11 ) ); ?></p>
			<ol class="v-escalera" aria-hidden="true">
				<?php
				$tramos = array( 8.53, 10.32, 13.19, 13.19 + 1.12 * 5 );
				foreach ( $c['tarifa']['tramos'] as $i => $t ) :
					?>
					<li data-tramo="<?php echo (int) $i; ?>" style="--h:<?php echo esc_attr( round( $tramos[ $i ] / 20 * 100 ) ); ?>%"><span><?php echo esc_html( $t ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>

<!-- V4 · Recogida en Mataró -->
<section class="v-recogida tono-escarcha seccion" aria-labelledby="v-recogida-t" id="recogida">
	<div class="envoltura v-recogida__in">
		<figure class="v-recogida__foto" data-revela="zoom"><?php echo dipt_foto( 'uso-industria-pala', $c['recogida']['alt'], array( 'sizes' => '(max-width: 900px) 100vw, 45vw' ) ); // phpcs:ignore ?></figure>
		<div>
			<?php echo dipt_titulo( 'h2', $c['recogida']['titulo'], 't-h2', 'v-recogida-t' ); // phpcs:ignore ?>
			<dl class="v-ficha">
				<?php foreach ( $c['recogida']['lista'] as $i => $l ) : ?>
					<div data-revela style="--i:<?php echo (int) $i; ?>"><dt class="dato"><?php echo esc_html( $l[0] ); ?></dt><dd><?php echo esc_html( $l[1] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<?php echo dipt_enlace( $c['recogida']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- V5 · Cobertura: sí / consúltanos -->
<section class="v-cobertura tono-marino seccion" aria-labelledby="v-cobertura-t">
	<div class="envoltura">
		<h2 class="t-h2" id="v-cobertura-t" data-revela><?php echo esc_html( $c['cobertura']['titulo'] ); ?></h2>
		<div class="v-cobertura__dos">
			<div class="v-cobertura__si" data-revela="izq"><span class="v-cobertura__marca" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span><h3><?php echo esc_html( $c['cobertura']['si']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['si']['texto'] ); ?></p></div>
			<div class="v-cobertura__no" data-revela="der"><span class="v-cobertura__marca" aria-hidden="true">?</span><h3><?php echo esc_html( $c['cobertura']['no']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['no']['texto'] ); ?></p></div>
		</div>
		<p><?php echo dipt_enlace( $c['cobertura']['zonas'], dipt_url( 'zonas' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<!-- V6 · Preguntas en pestañas -->
<section class="v-faq tono-blanco seccion" aria-labelledby="v-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="v-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="v-pestanas" data-pestanas>
			<div class="v-pestanas__lista" role="tablist" aria-labelledby="v-faq-t">
				<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
					<button type="button" role="tab" id="v-tab-<?php echo (int) $i; ?>" aria-controls="v-panel-<?php echo (int) $i; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><?php echo esc_html( $f[0] ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<div class="v-pestanas__panel" role="tabpanel" id="v-panel-<?php echo (int) $i; ?>" aria-labelledby="v-tab-<?php echo (int) $i; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
					<p class="v-pestanas__q"><?php echo esc_html( $f[0] ); ?></p>
					<p><?php echo esc_html( $f[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
