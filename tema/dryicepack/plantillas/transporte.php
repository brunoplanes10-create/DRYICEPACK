<?php
/**
 * Transporte en frío: T1 título partido con foto vertical y datos · T2 caja en sección con el frío bajando ·
 * T3 tres reglas en fichas escalonadas · T3b cliente en placa de características · T4 planificador de rutas (consumo por semana y al mes, 4 semanas) ·
 * T5 preguntas en rejilla · T6 llamada final con hoja de ruta.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'transporte' );
// Planificador: valores de partida (L, X, V y 20 kg por ruta). El JS recalcula con la misma regla: por semana = días × kg; al mes = por semana × 4.
$plan_dias   = array( 0, 2, 4 );
$plan_kg     = 20;
$plan_semana = count( $plan_dias ) * $plan_kg;
dipt_precargar( 'uso-transporte', '(max-width: 900px) 100vw, 45vw' );
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- T1 · Título partido con foto vertical (bloque "t-portada": .t-hero es la clase tipográfica de base.css) -->
<section class="t-portada" aria-labelledby="t-hero-t">
	<div class="envoltura t-portada__in">
		<div class="t-portada__texto">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'transporte' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1" id="t-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
		<figure class="t-portada__foto"><?php echo dipt_foto( 'uso-transporte', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 45vw', 'recorte' => '40% 50%' ) ); // phpcs:ignore ?></figure>
	</div>
	<div class="envoltura"><ul class="t-portada__datos dato"><?php foreach ( $c['hero']['datos'] as $d ) : ?><li><?php echo esc_html( $d ); ?></li><?php endforeach; ?></ul></div>
</section>

<!-- T2 · Caja en sección -->
<section class="t-caja tono-oscuro seccion" aria-labelledby="t-caja-t" data-vivo>
	<div class="envoltura t-caja__in">
		<div>
			<h2 class="t-h2" id="t-caja-t" data-revela><?php dipt_html( $c['caja']['titulo'] ); ?></h2>
			<p class="entrada" data-revela><?php echo esc_html( $c['caja']['texto'] ); ?></p>
		</div>
		<div class="t-seccion" aria-hidden="true">
			<div class="t-seccion__caja">
				<div class="t-seccion__hielo"><?php for ( $i = 0; $i < 14; $i++ ) : ?><b></b><?php endfor; ?></div>
				<div class="t-seccion__flechas"><?php for ( $i = 0; $i < 5; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>"></i><?php endfor; ?></div>
				<div class="t-seccion__producto"><span></span><span></span><span></span></div>
			</div>
			<ul class="t-seccion__marcas dato"><?php foreach ( $c['caja']['marcas'] as $i => $m ) : ?><li class="m<?php echo (int) $i + 1; ?>"><?php echo esc_html( $m ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>

<!-- T3 · Tres reglas en fichas escalonadas -->
<section class="t-reglas tono-papel seccion" aria-labelledby="t-reglas-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['reglas']['titulo'], 't-h2', 't-reglas-t' ); // phpcs:ignore ?>
		<ol class="t-escalera">
			<?php foreach ( $c['reglas']['lista'] as $i => $r ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="t-escalera__n"><?php echo (int) $i + 1; ?></span><h3><?php echo esc_html( $r[0] ); ?></h3><p><?php echo esc_html( $r[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- T3b · Cliente: placa de características (cliente · uso) -->
<section class="sc-cliente tono-marino" aria-labelledby="t-cliente-t">
	<div class="envoltura sc-cliente__in">
		<?php echo dipt_titulo( 'h2', $c['cliente']['titulo'], 't-h2 sc-cliente__titulo', 't-cliente-t' ); // phpcs:ignore ?>
		<dl class="sc-placa" data-revela>
			<div class="sc-placa__campo">
				<dt class="dato"><?php echo esc_html( $c['cliente']['etiquetas'][0] ); ?></dt>
				<dd><?php echo dipt_logo_cliente( $c['cliente']['id'], $c['cliente']['alt'] ); // phpcs:ignore ?><span class="sc-placa__nombre dato" aria-hidden="true"><?php echo esc_html( dipt_cliente_nombre( $c['cliente']['id'] ) ); ?></span><span class="sc-placa__detalle"><?php echo esc_html( $c['cliente']['detalle'] ); ?></span></dd>
			</div>
			<div class="sc-placa__campo">
				<dt class="dato"><?php echo esc_html( $c['cliente']['etiquetas'][1] ); ?></dt>
				<dd class="sc-placa__uso"><?php echo esc_html( $c['cliente']['uso'] ); ?></dd>
			</div>
		</dl>
	</div>
</section>

<!-- T4 · Planificador de rutas -->
<section class="t-plan tono-blanco seccion" aria-labelledby="t-plan-t" data-plan>
	<div class="envoltura t-plan__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['plan']['titulo'], 't-h2', 't-plan-t' ); // phpcs:ignore ?>
			<p class="suave t-plan__texto" data-revela><?php echo esc_html( $c['plan']['texto'] ); ?></p>
		</div>
		<div class="t-plan__panel">
			<fieldset class="t-plan__dias"><legend class="visually-hidden"><?php echo esc_html( wp_strip_all_tags( str_replace( '|', ' ', $c['plan']['titulo'] ) ) ); ?></legend>
				<?php foreach ( $c['plan']['dias'] as $i => $d ) : ?>
					<label><input type="checkbox" value="1" data-plan-dia<?php checked( in_array( $i, $plan_dias, true ) ); ?>><span><?php echo esc_html( $d ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<label class="t-plan__kg"><input type="number" min="3" max="250" step="1" value="<?php echo (int) $plan_kg; ?>" inputmode="numeric" data-plan-kg><span><?php echo esc_html( $c['plan']['kg'] ); ?></span></label>
			<dl class="t-plan__resultado">
				<div class="t-plan__fila">
					<dt><span class="dato"><?php echo esc_html( $c['plan']['semana'] ?? '' ); ?></span><small class="dato"><?php echo esc_html( $c['plan']['semana_calc'] ?? '' ); ?></small></dt>
					<dd><output data-plan-semana><?php echo (int) $plan_semana; ?></output> kg</dd>
				</div>
				<div class="t-plan__fila t-plan__fila--mes">
					<dt><span class="dato"><?php echo esc_html( $c['plan']['mes'] ); ?></span><small class="dato"><?php echo esc_html( $c['plan']['mes_calc'] ?? '' ); ?></small></dt>
					<dd><output data-plan-mes><?php echo (int) ( $plan_semana * 4 ); ?></output> kg</dd>
				</div>
			</dl>
			<p class="dato suave"><?php echo esc_html( $c['plan']['nota'] ); ?></p>
			<?php if ( ! empty( $c['plan']['lunes'] ) ) : // Los lunes no hay reparto: sábado (según zona) o recogida en Mataró ?>
				<p class="t-plan__lunes"><?php echo dipt_icono( 'calendario' ); // phpcs:ignore ?><span><?php echo esc_html( $c['plan']['lunes'] ); ?></span></p>
			<?php endif; ?>
			<?php echo dipt_boton( $c['plan']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- T5 · Preguntas en rejilla -->
<section class="t-faq tono-escarcha seccion" aria-labelledby="t-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="t-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="t-faq__rejilla">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<article data-revela style="--i:<?php echo (int) $i; ?>"><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></article>
			<?php endforeach; ?>
		</div>
		<p class="t-faq__seg"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<?php if ( ! empty( $c['final'] ) ) : $f = $c['final']; ?>
<!-- T6 · Final: hoja de ruta de Mataró a su almacén (la furgoneta llega al entrar la sección) -->
<section class="t-final tono-oscuro seccion" aria-labelledby="t-final-t" data-vivo>
	<div class="envoltura t-final__in">
		<div class="t-final__texto">
			<?php echo dipt_titulo( 'h2', $f['titulo'], 't-h2', 't-final-t' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $f['texto'] ); ?></p>
			<div class="t-final__acciones" data-revela style="--i:1">
				<?php echo dipt_boton( $f['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
				<?php if ( ! empty( $f['wa'] ) ) echo dipt_enlace( $f['wa'], dipt_whatsapp_url( $f['wa_mensaje'] ?? null ), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
			</div>
		</div>
		<div class="t-final__ruta" data-revela="der">
			<p class="t-final__carga dato" aria-hidden="true"><?php echo esc_html( $f['carga'] ?? '' ); ?></p>
			<div class="t-final__via" aria-hidden="true">
				<i class="t-final__nodo"></i>
				<span class="t-final__vehiculo"><?php echo dipt_icono( 'furgoneta' ); // phpcs:ignore ?></span>
				<i class="t-final__nodo t-final__nodo--fin"></i>
			</div>
			<ol class="t-final__paradas">
				<?php foreach ( (array) ( $f['ruta'] ?? array() ) as $parada ) : ?>
					<li><strong><?php echo esc_html( $parada[0] ); ?></strong><span class="dato"><?php echo esc_html( $parada[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
