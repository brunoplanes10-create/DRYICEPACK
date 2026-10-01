<?php
/**
 * Laboratorios: L1 foto en círculo con título al lado · L2 vista cenital de la caja (tubos rodeados) ·
 * L3 anatomía de un paquete con hielo seco · L4 para centros (tres columnas con línea) · L5 preguntas numeradas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'laboratorios' );
dipt_precargar( 'uso-laboratorio', '(max-width: 900px) 80vw, 38vw' );
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- L1 · Foto en círculo -->
<section class="l-hero" aria-labelledby="l-hero-t">
	<div class="envoltura l-hero__in">
		<figure class="l-hero__foto"><?php echo dipt_foto( 'uso-laboratorio', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 80vw, 38vw' ) ); // phpcs:ignore ?><span class="l-hero__orbita" aria-hidden="true"></span></figure>
		<div>
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'laboratorios' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1" id="l-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
			<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'empresas', null, 'alta' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- L2 · Vista cenital: rodear las muestras -->
<section class="l-caja tono-oscuro seccion" aria-labelledby="l-caja-t">
	<div class="envoltura l-caja__in">
		<div class="l-cenital" aria-hidden="true" data-revela="zoom">
			<div class="l-cenital__pared">
				<div class="l-cenital__hielo"><?php for ( $i = 0; $i < 90; $i++ ) : ?><b style="--r:<?php echo (int) ( ( $i * 41 ) % 180 ); ?>deg"></b><?php endfor; ?></div>
				<div class="l-cenital__rejilla"><?php for ( $i = 0; $i < 12; $i++ ) : ?><i style="--i:<?php echo (int) $i; ?>"></i><?php endfor; ?></div>
			</div>
		</div>
		<div>
			<h2 class="t-h2" id="l-caja-t" data-revela><?php dipt_html( str_replace( '|', '<br>', $c['caja']['titulo'] ) ); ?></h2>
			<p class="entrada l-caja__texto" data-revela><?php echo esc_html( $c['caja']['texto'] ); ?></p>
			<ul class="l-caja__leyenda dato"><?php foreach ( $c['caja']['leyenda'] as $i => $l ) : ?><li class="l<?php echo (int) $i + 1; ?>"><?php echo esc_html( $l ); ?></li><?php endforeach; ?></ul>
		</div>
	</div>
</section>

<!-- L3 · Anatomía de un paquete -->
<section class="l-etiqueta tono-papel seccion" aria-labelledby="l-etiqueta-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['etiqueta']['titulo'], 't-h2 l-etiqueta__titulo', 'l-etiqueta-t' ); // phpcs:ignore ?>
		<div class="l-paquete">
			<div class="l-paquete__caja" aria-hidden="true">
				<span class="l-paquete__un">UN 1845</span>
				<span class="l-paquete__nombre">Hielo seco</span>
				<span class="l-paquete__peso">Peso neto: 4 kg</span>
				<span class="l-paquete__rombo"><span>9</span></span>
				<span class="l-paquete__ranura"></span>
			</div>
			<ol class="l-paquete__marcas">
				<?php foreach ( $c['etiqueta']['marcas'] as $i => $m ) : ?>
					<li class="m<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>"><strong><?php echo esc_html( $m[0] ); ?></strong><span><?php echo esc_html( $m[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<p class="l-etiqueta__aviso"><?php echo esc_html( $c['etiqueta']['aviso'] ); ?></p>
	</div>
</section>

<!-- L4 · Para centros -->
<section class="l-centros tono-blanco seccion" aria-labelledby="l-centros-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['centros']['titulo'], 't-h2', 'l-centros-t' ); // phpcs:ignore ?>
		<ul class="l-columnas">
			<?php $ic = array( 'calendario', 'factura', 'descarga' ); foreach ( $c['centros']['lista'] as $i => $l ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><?php echo dipt_icono( $ic[ $i ] ); // phpcs:ignore ?><h3><?php echo esc_html( $l[0] ); ?></h3><p><?php echo esc_html( $l[1] ); ?></p></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- L5 · Preguntas numeradas -->
<section class="l-faq tono-escarcha seccion" aria-labelledby="l-faq-t">
	<div class="envoltura">
		<h2 class="t-h3" id="l-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<ol class="l-faq__lista">
			<?php foreach ( $c['faq']['lista'] as $f ) : ?>
				<li data-revela><h3><?php echo esc_html( $f[0] ); ?></h3><p><?php echo esc_html( $f[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
		<p><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<?php
get_footer();
