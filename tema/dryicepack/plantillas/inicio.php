<?php
/**
 * Portada (castellano, catalán e inglés). Orden AIDA:
 * atención (hero) → interés (elegir, cómo llega) → deseo (usos, ficha, empresas, recogida) → objeciones (seguridad, preguntas) → acción.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'inicio' );
$con = dipt_contacto();
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'caja-16mm-niebla', '(max-width: 900px) 100vw, 58vw' );
dipt_registrar_faq( $c['faq']['lista'] );

// Selección inicial del configurador: 10 kg de 16 mm
$packs   = dipt_packs();
$iva     = dipt_factor_iva();
$kg_ini  = isset( $packs['10'] ) ? 10 : (int) array_key_first( $packs );
$p_ini   = dipt_precio_pack( $kg_ini );
$var_ini = null;
foreach ( dipt_variaciones() as $v ) {
	if ( (float) $v['kg'] === (float) $kg_ini && '16mm' === $v['formato'] ) $var_ini = $v;
}
$url_pedir = dipt_url( 'producto' );
if ( $var_ini && ! empty( $var_ini['padre'] ) ) {
	$url_pedir = add_query_arg( array( 'add-to-cart' => $var_ini['padre'], 'variation_id' => $var_ini['id'], 'attribute_pa_peso' => $var_ini['peso'], 'attribute_pa_formato' => $var_ini['fslug'], 'quantity' => 1, 'dipt_ir' => 'checkout' ), home_url( '/' ) );
}

get_header();
?>

<!-- I1 · Hero "cámara oscura" -->
<section class="i-hero tono-oscuro" aria-labelledby="i-hero-t">
	<div class="i-hero__foto">
		<?php echo dipt_foto( 'caja-16mm-niebla', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 58vw' ) ); // phpcs:ignore ?>
	</div>
	<div class="niebla niebla--hero" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura i-hero__in">
		<h1 class="t-hero i-hero__titulo" id="i-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<p class="entrada i-hero__texto"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
		<div class="i-hero__acciones">
			<?php echo dipt_boton( $c['hero']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			<p class="i-hero__tel"><?php echo esc_html( $c['hero']['o_llama'] ); ?> <a href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a></p>
		</div>
		<p class="i-hero__corte dato" data-corte data-t-antes="<?php echo esc_attr( $c['hero']['corte']['antes'] ); ?>" data-t-quedan="<?php echo esc_attr( $c['hero']['corte']['quedan'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['corte']['despues'] ); ?>">
			<?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?>
			<span data-corte-texto><?php $e = dipt_primera_entrega(); echo esc_html( sprintf( $c['hero']['corte']['despues'], $e ? dipt_fecha_larga( $e['fecha'] ) : '' ) ); ?></span>
			<span class="i-hero__quedan" data-corte-quedan hidden></span>
		</p>
	</div>
	<div class="i-hero__escala" aria-hidden="true">
		<span class="i-hero__escala-marca"><span>−78,5</span></span>
	</div>
</section>

<!-- I2 · Cinta de datos -->
<div class="i-cinta tono-marino"><?php echo dipt_cinta( $c['cinta'], 'dato' ); // phpcs:ignore ?></div>

<!-- I3 · Elegir formato y cantidad con el precio final -->
<section class="i-elegir tono-papel seccion" aria-labelledby="i-elegir-t" data-configurador data-kg="<?php echo esc_attr( $kg_ini ); ?>" data-formato="16mm">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['elegir']['titulo'], 't-h2 i-elegir__titulo', 'i-elegir-t' ); // phpcs:ignore ?>
		<div class="i-elegir__paneles" role="radiogroup" aria-label="Formato">
			<?php foreach ( $c['elegir']['formatos'] as $f => $fo ) : ?>
				<button type="button" class="i-panel<?php echo '16mm' === $f ? ' activo' : ''; ?>" role="radio" aria-checked="<?php echo '16mm' === $f ? 'true' : 'false'; ?>" data-formato="<?php echo esc_attr( $f ); ?>" data-revela style="--i:<?php echo '16mm' === $f ? 1 : 0; ?>">
					<span class="i-panel__foto"><?php echo dipt_foto( $fo['foto'], $fo['alt'], array( 'sizes' => '(max-width: 900px) 100vw, 50vw' ) ); // phpcs:ignore ?></span>
					<span class="i-panel__cuerpo">
						<span class="i-panel__mm dato"><?php echo esc_html( str_replace( 'mm', ' mm', $f ) ); ?></span>
						<span class="i-panel__nombre"><?php echo esc_html( $fo['nombre'] ); ?></span>
						<span class="i-panel__texto"><?php echo esc_html( $fo['texto'] ); ?></span>
						<span class="i-panel__para dato"><?php echo esc_html( $fo['para'] ); ?></span>
					</span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="i-regla" data-revela>
			<p class="i-regla__pregunta"><?php echo esc_html( $c['elegir']['cuanto'] ); ?></p>
			<div class="i-regla__kilos" role="radiogroup" aria-label="<?php echo esc_attr( $c['elegir']['cuanto'] ); ?>">
				<?php foreach ( $packs as $kg => $precio ) : $pp = dipt_precio_pack( (float) $kg ); ?>
					<button type="button" role="radio" class="i-kilo<?php echo (float) $kg === (float) $kg_ini ? ' activo' : ''; ?>" aria-checked="<?php echo (float) $kg === (float) $kg_ini ? 'true' : 'false'; ?>" data-kg="<?php echo esc_attr( $kg ); ?>">
						<span class="i-kilo__n"><?php echo esc_html( $kg ); ?></span><span class="i-kilo__u">kg</span>
						<span class="i-kilo__precio dato"><?php echo esc_html( dipt_euros( $pp['con_iva'] ) ); ?></span>
					</button>
				<?php endforeach; ?>
				<span class="i-regla__barra" aria-hidden="true"><span></span></span>
			</div>
			<dl class="i-ticket" aria-live="polite">
				<div><dt data-ticket-pack><?php echo esc_html( $kg_ini . ' kg · 16 mm' ); ?></dt><dd data-ticket-precio><?php echo esc_html( dipt_euros( $p_ini['con_iva'] ) ); ?></dd></div>
				<div><dt><?php echo esc_html( $c['elegir']['envio'] ); ?></dt><dd data-ticket-envio><?php echo esc_html( dipt_euros( $p_ini['envio'] ) ); ?></dd></div>
				<div class="i-ticket__total"><dt><?php echo esc_html( $c['elegir']['total'] ); ?></dt><dd data-ticket-total><?php echo esc_html( dipt_euros( $p_ini['total'] ) ); ?></dd></div>
			</dl>
			<p class="i-regla__recogida" data-t-recogida="<?php echo esc_attr( $c['elegir']['recogida'] ); ?>" data-ticket-recogida><?php echo esc_html( sprintf( $c['elegir']['recogida'], dipt_euros( $p_ini['con_iva'] ) ) ); ?></p>
			<div class="i-regla__accion">
				<a class="boton" href="<?php echo esc_url( $url_pedir ); ?>" data-pedir data-t-boton="<?php echo esc_attr( $c['elegir']['boton'] ); ?>"><span><?php echo esc_html( sprintf( $c['elegir']['boton'], $kg_ini ) ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></a>
				<p class="i-regla__nota suave"><?php echo esc_html( $c['elegir']['nota'] ); ?><br><?php echo esc_html( $c['elegir']['mas'] ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- I4 · Cómo llega: mapa con rutas desde Mataró y tres horas -->
<section class="i-llega tono-marino seccion" aria-labelledby="i-llega-t" data-vivo>
	<div class="envoltura i-llega__in">
		<div class="i-llega__mapa">
			<?php get_template_part( 'partes/mapa-peninsula', null, array( 'titulo' => $c['llega']['mapa'] ) ); ?>
		</div>
		<div class="i-llega__texto">
			<?php echo dipt_titulo( 'h2', $c['llega']['titulo'], 't-h2', 'i-llega-t' ); // phpcs:ignore ?>
			<ol class="i-llega__pasos">
				<?php foreach ( $c['llega']['pasos'] as $i => $p ) : ?>
					<li data-revela style="--i:<?php echo (int) $i; ?>">
						<span class="i-llega__hora dato"><?php echo esc_html( $p[0] ); ?></span>
						<span class="i-llega__paso"><strong><?php echo esc_html( $p[1] ); ?></strong><?php echo esc_html( $p[2] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="i-llega__nota suave"><?php echo esc_html( $c['llega']['nota'] ); ?></p>
			<?php echo dipt_enlace( $c['llega']['enlace'], dipt_url( 'envios' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- I5 · Usos: índice editorial con imagen que sigue al cursor -->
<section class="i-usos tono-blanco seccion" aria-labelledby="i-usos-t" data-sigue>
	<div class="envoltura">
		<div class="i-usos__cabeza">
			<?php echo dipt_titulo( 'h2', $c['usos']['titulo'], 't-h2', 'i-usos-t' ); // phpcs:ignore ?>
			<?php echo dipt_enlace( $c['usos']['todos'], dipt_url( 'aplicaciones' ) ); // phpcs:ignore ?>
		</div>
		<ol class="i-usos__lista">
			<?php foreach ( $c['usos']['lista'] as $i => $u ) : ?>
				<li data-sigue-foto="<?php echo esc_attr( $u[3] ); ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<a href="<?php echo esc_url( dipt_url( $u[0] ) ); ?>">
						<span class="i-usos__n dato">0<?php echo (int) $i + 1; ?></span>
						<span class="i-usos__miniatura" aria-hidden="true"><img src="<?php echo esc_url( dipt_foto_url( $u[3], 480 ) ); ?>" alt="" loading="lazy" width="480" height="320"></span>
						<span class="i-usos__nombre"><?php echo esc_html( $u[1] ); ?></span>
						<span class="i-usos__texto"><?php echo esc_html( $u[2] ); ?></span>
						<span class="i-usos__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<div class="i-usos__visor" data-sigue-visor aria-hidden="true">
		<?php foreach ( $c['usos']['lista'] as $i => $u ) : ?>
			<img src="<?php echo esc_url( dipt_foto_url( $u[3], 900 ) ); ?>" alt="" loading="lazy" width="900" height="600" data-id="<?php echo esc_attr( $u[3] ); ?>"<?php echo 0 === $i ? ' class="activa"' : ''; ?>>
		<?php endforeach; ?>
	</div>
</section>

<!-- I6 · Ficha técnica: −78,5 °C con cotas de plano -->
<section class="i-ficha tono-escarcha seccion" aria-labelledby="i-ficha-t">
	<div class="envoltura">
		<p class="i-ficha__cifra t-gigante" aria-hidden="true"><span data-cuenta="-78.5" data-decimales="1" data-desde="20">−78,5</span><span class="i-ficha__grados">°C</span></p>
		<?php echo dipt_titulo( 'h2', $c['ficha']['titulo'], 't-h3 i-ficha__titulo', 'i-ficha-t' ); // phpcs:ignore ?>
		<div class="i-ficha__plano">
			<ul class="i-ficha__cotas">
				<?php foreach ( $c['ficha']['cotas'] as $i => $cota ) : ?>
					<li class="i-cota i-cota--<?php echo (int) $i + 1; ?>" data-revela="<?php echo $i % 2 ? 'der' : 'izq'; ?>" style="--i:<?php echo (int) $i; ?>">
						<strong><?php echo esc_html( $cota[0] ); ?></strong><span><?php echo esc_html( $cota[1] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<figure class="i-ficha__foto" data-revela="zoom">
				<?php echo dipt_foto( 'cenital-caja-nuggets', $c['ficha']['alt'], array( 'sizes' => '(max-width: 900px) 80vw, 34vw' ) ); // phpcs:ignore ?>
			</figure>
		</div>
		<p class="i-ficha__enlace"><?php echo dipt_enlace( $c['ficha']['enlace'], dipt_url( 'que-es' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<!-- I7 · Empresas: tarjeta de cliente que se inclina -->
<section class="i-empresas tono-oscuro seccion" aria-labelledby="i-empresas-t">
	<div class="envoltura i-empresas__in">
		<div class="i-empresas__texto">
			<?php echo dipt_titulo( 'h2', $c['empresas']['titulo'], 't-h2', 'i-empresas-t' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $c['empresas']['texto'] ); ?></p>
			<p data-revela style="--i:1"><?php echo dipt_enlace( $c['empresas']['enlace'], dipt_url( 'empresas' ) ); // phpcs:ignore ?></p>
		</div>
		<div class="i-empresas__escena" data-revela="zoom">
			<div class="i-tarjeta" data-inclina>
				<span class="i-tarjeta__copo" aria-hidden="true"><?php echo dipt_icono( 'copo' ); // phpcs:ignore ?></span>
				<p class="i-tarjeta__titulo"><?php echo esc_html( $c['empresas']['tarjeta']['titulo'] ); ?></p>
				<ul class="i-tarjeta__lineas">
					<?php foreach ( $c['empresas']['tarjeta']['lineas'] as $l ) : ?>
						<li><?php echo dipt_icono( 'check' ); // phpcs:ignore ?><?php echo esc_html( $l ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p class="i-tarjeta__pie dato"><span><?php echo esc_html( $c['empresas']['tarjeta']['pie'] ); ?></span><span>−78,5 °C</span></p>
			</div>
		</div>
	</div>
</section>

<!-- I8 · Recogida en Mataró: foto a sangre con ficha solapada -->
<section class="i-recogida" aria-labelledby="i-recogida-t" id="recogida">
	<div class="i-recogida__foto" data-cortina>
		<?php echo dipt_foto( 'reparto-furgoneta', $c['recogida']['alt'], array( 'sizes' => '100vw' ) ); // phpcs:ignore ?>
	</div>
	<div class="envoltura">
		<div class="i-recogida__ficha tono-papel" data-revela>
			<?php echo dipt_titulo( 'h2', $c['recogida']['titulo'], 't-h3', 'i-recogida-t' ); // phpcs:ignore ?>
			<p><?php echo esc_html( $c['recogida']['texto'] ); ?></p>
			<address class="i-recogida__dir">
				<span class="dato"><?php echo esc_html( $c['recogida']['ficha'] ); ?></span>
				<?php echo esc_html( $con['direccion'] ); ?><br><?php echo esc_html( $con['cp'] . ' ' . $con['localidad'] . ' (' . $con['provincia'] . ')' ); ?>
			</address>
			<ul class="i-recogida__cerca dato" aria-label="Zonas cercanas">
				<?php foreach ( $c['recogida']['cerca'] as $z ) : ?><li><?php echo esc_html( $z ); ?></li><?php endforeach; ?>
			</ul>
			<?php echo dipt_enlace( $c['recogida']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<!-- I9 · Seguridad: seis rombos de pictograma -->
<section class="i-seguridad tono-papel seccion" aria-labelledby="i-seguridad-t">
	<div class="envoltura">
		<div class="i-seguridad__cabeza">
			<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-h2', 'i-seguridad-t' ); // phpcs:ignore ?>
			<p class="suave" data-revela><?php echo esc_html( $c['seguridad']['texto'] ); ?></p>
		</div>
		<ul class="i-rombos">
			<?php foreach ( dipt_avisos_seguridad() as $i => $a ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="i-rombo" aria-hidden="true"><?php echo dipt_icono( $a[0] ); // phpcs:ignore ?></span>
					<strong><?php echo esc_html( $a[1] ); ?></strong>
					<span><?php echo esc_html( $a[2] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="i-seguridad__mas"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<!-- I10 · Preguntas: título fijo y acordeón -->
<section class="i-faq tono-blanco seccion" aria-labelledby="i-faq-t">
	<div class="envoltura i-faq__in">
		<div class="i-faq__cabeza">
			<?php echo dipt_titulo( 'h2', $c['faq']['titulo'], 't-h2', 'i-faq-t' ); // phpcs:ignore ?>
		</div>
		<div class="i-faq__lista">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<details class="i-pregunta" data-revela style="--i:<?php echo (int) $i; ?>"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><span class="dato">0<?php echo (int) $i + 1; ?></span><span class="i-pregunta__q"><?php echo esc_html( $f[0] ); ?></span><span class="i-pregunta__mas" aria-hidden="true"></span></summary>
					<div class="i-pregunta__a"><p><?php echo esc_html( $f[1] ); ?></p></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- I11 · Final: niebla a pantalla completa -->
<section class="i-final tono-oscuro" aria-labelledby="i-final-t">
	<div class="niebla niebla--final" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="envoltura i-final__in">
		<?php echo dipt_titulo( 'h2', $c['final']['titulo'], 't-gigante', 'i-final-t' ); // phpcs:ignore ?>
		<p class="dato i-final__corte" data-corte data-t-antes="<?php echo esc_attr( $c['hero']['corte']['antes'] ); ?>" data-t-quedan="<?php echo esc_attr( $c['hero']['corte']['quedan'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['corte']['despues'] ); ?>"><span data-corte-texto></span></p>
		<div class="i-final__acciones">
			<?php echo dipt_boton( $c['final']['boton'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			<a class="tel-grande" href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a>
			<a class="enlace-flecha" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><span><?php echo esc_html( $c['final']['wa'] ); ?></span><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></a>
		</div>
	</div>
</section>

<?php
get_footer();
