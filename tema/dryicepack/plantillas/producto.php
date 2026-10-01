<?php
/**
 * Comprar hielo seco: ficha de producto de WooCommerce (castellano) y páginas de compra en catalán e inglés.
 * P1 configurador con precio final y fecha · P2 despiece de la caja · P3 comparador 3/16 mm · P4 cuánto necesito ·
 * P5 tarifa de envío en billete · P6 preguntas en conversación. Barra de compra fija en móvil.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c     = dipt_contenido( 'producto' );
$con   = dipt_contacto();
$packs = dipt_packs();
$iva   = dipt_factor_iva();
$kg0   = isset( $packs['10'] ) ? 10 : (float) array_key_first( $packs );
$f0    = '16mm';
// Selección desde un enlace: ?kg=10&formato=3mm (página de Halloween, guías)
$kg_url = isset( $_GET['kg'] ) ? (string) (float) wp_unslash( $_GET['kg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$f_url  = isset( $_GET['formato'] ) ? sanitize_key( wp_unslash( $_GET['formato'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( '' !== $kg_url && isset( $packs[ $kg_url ] ) ) $kg0 = (float) $kg_url;
if ( in_array( $f_url, array( '3mm', '16mm' ), true ) ) $f0 = $f_url;
$v0    = null;
foreach ( dipt_variaciones() as $v ) if ( (float) $v['kg'] === (float) $kg0 && $f0 === $v['formato'] ) $v0 = $v;
$p0    = dipt_precio_pack( $kg0 );
$e0    = dipt_primera_entrega();
$GLOBALS['dipt_barra_compra'] = true;
dipt_precargar( $c['galeria'][ $f0 ][0][0], '(max-width: 900px) 100vw, 55vw' );
dipt_registrar_faq( $c['faq']['lista'] );
add_filter( 'body_class', static fn( $b ) => array_merge( $b, array( 'hay-barra-compra' ) ) );

get_header();
if ( function_exists( 'wc_print_notices' ) ) {
	echo '<div class="envoltura p-avisos">';
	wc_print_notices();
	echo '</div>';
}
?>

<!-- P1 · Configurador -->
<section class="p-compra" aria-labelledby="p-titulo" data-compra data-kg="<?php echo esc_attr( $kg0 ); ?>" data-formato="<?php echo esc_attr( $f0 ); ?>">
	<div class="envoltura p-compra__in">
		<div class="p-galeria" data-galeria>
			<?php foreach ( $c['galeria'] as $formato => $fotos ) : ?>
				<div class="p-galeria__grupo<?php echo $formato === $f0 ? ' activo' : ''; ?>" data-galeria-formato="<?php echo esc_attr( $formato ); ?>"<?php echo $formato === $f0 ? '' : ' hidden'; ?>>
					<?php foreach ( $fotos as $i => $foto ) : ?>
						<figure class="p-galeria__foto p-galeria__foto--<?php echo (int) $i + 1; ?>">
							<?php echo dipt_foto( $foto[0], $foto[1], array( 'lcp' => ( 0 === $i && $formato === $f0 ), 'sizes' => 0 === $i ? '(max-width: 900px) 100vw, 55vw' : '(max-width: 900px) 50vw, 27vw' ) ); // phpcs:ignore ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="p-panel">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'producto' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1 p-panel__titulo" id="p-titulo"><?php echo esc_html( $c['titulo'] ); ?></h1>
			<p class="p-panel__sub"><?php echo esc_html( $c['sub'] ); ?></p>

			<form class="p-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-compra-form>
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $v0['padre'] ?? '' ); ?>" data-campo="padre">
				<input type="hidden" name="variation_id" value="<?php echo esc_attr( $v0['id'] ?? '' ); ?>" data-campo="id">
				<input type="hidden" name="attribute_pa_peso" value="<?php echo esc_attr( $v0['peso'] ?? '' ); ?>" data-campo="peso">
				<input type="hidden" name="attribute_pa_formato" value="<?php echo esc_attr( $v0['fslug'] ?? '' ); ?>" data-campo="fslug">
				<input type="hidden" name="dipt_ir" value="checkout">

				<fieldset class="p-grupo">
					<legend class="p-grupo__titulo"><?php echo esc_html( $c['formato'] ); ?></legend>
					<div class="p-formatos">
						<?php foreach ( $c['formatos'] as $formato => $fo ) : ?>
							<label class="p-formato">
								<input type="radio" name="dipt_formato" value="<?php echo esc_attr( $formato ); ?>"<?php checked( $formato, $f0 ); ?>>
								<span class="p-formato__icono" aria-hidden="true"><?php echo dipt_icono( '3mm' === $formato ? 'pellet' : 'nugget' ); // phpcs:ignore ?></span>
								<span class="p-formato__nombre"><?php echo esc_html( $fo[0] ); ?></span>
								<span class="p-formato__texto"><?php echo esc_html( $fo[1] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<fieldset class="p-grupo">
					<legend class="p-grupo__titulo"><?php echo esc_html( $c['kilos'] ); ?></legend>
					<div class="p-kilos">
						<?php foreach ( $packs as $kg => $precio ) : ?>
							<label class="p-kilo">
								<input type="radio" name="dipt_kg" value="<?php echo esc_attr( $kg ); ?>"<?php checked( (float) $kg, (float) $kg0 ); ?>>
								<span class="p-kilo__n"><?php echo esc_html( $kg ); ?><small>kg</small></span>
								<span class="p-kilo__precio"><?php echo esc_html( dipt_euros( $precio * $iva ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<div class="p-fila">
					<div class="p-grupo p-grupo--cajas">
						<label class="p-grupo__titulo" for="p-cajas"><?php echo esc_html( $c['cajas'] ); ?></label>
						<div class="p-cajas">
							<button type="button" data-cajas="-1" aria-label="<?php echo esc_attr( $c['menos'] ); ?>">−</button>
							<input id="p-cajas" name="quantity" type="number" inputmode="numeric" min="1" max="83" value="1" data-cajas-input>
							<button type="button" data-cajas="1" aria-label="<?php echo esc_attr( $c['mas'] ); ?>">+</button>
						</div>
					</div>
					<div class="p-grupo p-grupo--cp">
						<label class="p-grupo__titulo" for="p-cp"><?php echo esc_html( $c['cp'] ); ?></label>
						<input id="p-cp" type="text" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code" placeholder="<?php echo esc_attr( $c['cp_ph'] ); ?>" data-cp>
					</div>
				</div>

				<p class="p-llega" data-llega data-t-llega="<?php echo esc_attr( $c['llega'] ); ?>" data-t-fuera="<?php echo esc_attr( $c['fuera'] ); ?>" aria-live="polite">
					<?php echo dipt_icono( 'furgoneta' ); // phpcs:ignore ?><span data-llega-texto><?php echo esc_html( sprintf( $c['llega_hoy'], $e0 ? dipt_fecha_larga( $e0['fecha'] ) : '' ) ); ?></span>
				</p>

				<dl class="p-precio" aria-live="polite">
					<div><dt data-precio-concepto><?php echo esc_html( $c['precio'] . ' · ' . $kg0 . ' kg' ); ?></dt><dd data-precio-pack><?php echo esc_html( dipt_euros( $p0['con_iva'] ) ); ?></dd></div>
					<div><dt><?php echo esc_html( $c['envio'] ); ?></dt><dd data-precio-envio><?php echo esc_html( dipt_euros( $p0['envio'] ) ); ?></dd></div>
					<div class="p-precio__total"><dt><?php echo esc_html( $c['total'] ); ?> <small><?php echo esc_html( $c['iva'] ); ?></small></dt><dd data-precio-total><?php echo esc_html( dipt_euros( $p0['total'] ) ); ?></dd></div>
				</dl>

				<button type="submit" class="boton boton--ancho p-comprar"><span><?php echo esc_html( $c['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
				<p class="p-nota"><?php echo esc_html( $c['recogida'] ); ?></p>
				<p class="p-nota p-nota--max" data-max hidden><?php echo esc_html( $c['max'] ); ?></p>
				<p class="p-nota p-nota--max" data-medida hidden><?php echo esc_html( $c['medida'] ); ?> <a href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener">WhatsApp</a></p>
				<ul class="p-confianza dato">
					<?php foreach ( $c['confianza'] as $x ) : ?><li><?php echo dipt_icono( 'check' ); // phpcs:ignore ?><?php echo esc_html( $x ); ?></li><?php endforeach; ?>
				</ul>
			</form>
		</div>
	</div>
</section>

<!-- P2 · Despiece de la caja -->
<section class="p-caja tono-oscuro seccion" aria-labelledby="p-caja-t" data-vivo>
	<div class="envoltura p-caja__in">
		<?php echo dipt_titulo( 'h2', $c['caja']['titulo'], 't-h2', 'p-caja-t' ); // phpcs:ignore ?>
		<div class="p-despiece" aria-hidden="true">
			<svg class="p-despiece__capa p-despiece__tapa" viewBox="0 0 400 140"><path d="M200 10 L380 70 L200 130 L20 70 Z" fill="#E6EEF5"/><path d="M20 70 L200 130 L200 140 L20 80 Z" fill="#B8C7D6"/><path d="M380 70 L200 130 L200 140 L380 80 Z" fill="#CAD6E2"/></svg>
			<svg class="p-despiece__capa p-despiece__hielo" viewBox="0 0 400 150"><?php for ( $i = 0; $i < 46; $i++ ) { $x = 60 + ( ( $i * 53 ) % 280 ); $y = 30 + ( ( $i * 37 ) % 80 ); printf( '<rect x="%d" y="%d" width="22" height="10" rx="5" transform="rotate(%d %d %d)" fill="#F4F8FB" stroke="#B8D0E8" stroke-width="1"/>', $x, $y, ( $i * 29 ) % 180, $x + 11, $y + 5 ); } ?></svg>
			<svg class="p-despiece__capa p-despiece__caja" viewBox="0 0 400 300"><path d="M20 70 L200 130 L200 290 L20 230 Z" fill="#C3D0DC"/><path d="M380 70 L200 130 L200 290 L380 230 Z" fill="#D6E0E9"/><path d="M200 10 L380 70 L200 130 L20 70 Z" fill="#8FA4B8"/><path d="M200 30 L346 78 L200 118 L54 78 Z" fill="#4E6A83"/></svg>
		</div>
		<ol class="p-caja__capas">
			<?php foreach ( $c['caja']['capas'] as $i => $capa ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $capa[0] ); ?></strong><span><?php echo esc_html( $capa[1] ); ?></span></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- P3 · Comparador 3 mm / 16 mm -->
<section class="p-comparar tono-papel seccion" aria-labelledby="p-comparar-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['comparar']['titulo'], 't-h2 p-comparar__titulo', 'p-comparar-t' ); // phpcs:ignore ?>
		<div class="p-comparador" data-comparador style="--corte:50%">
			<div class="p-comparador__img p-comparador__img--der"><?php echo dipt_foto( 'cenital-16mm', '', array( 'sizes' => '(max-width: 1100px) 100vw, 1100px' ) ); // phpcs:ignore ?></div>
			<div class="p-comparador__img p-comparador__img--izq"><?php echo dipt_foto( 'cenital-3mm', '', array( 'sizes' => '(max-width: 1100px) 100vw, 1100px' ) ); // phpcs:ignore ?></div>
			<div class="p-comparador__linea" aria-hidden="true"><span><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></div>
			<p class="p-comparador__rotulo p-comparador__rotulo--izq"><strong><?php echo esc_html( $c['comparar']['izq'][0] ); ?></strong><?php echo esc_html( $c['comparar']['izq'][1] ); ?></p>
			<p class="p-comparador__rotulo p-comparador__rotulo--der"><strong><?php echo esc_html( $c['comparar']['der'][0] ); ?></strong><?php echo esc_html( $c['comparar']['der'][1] ); ?></p>
			<input type="range" min="0" max="100" value="50" aria-label="<?php echo esc_attr( $c['comparar']['rango'] ); ?>" data-comparador-rango>
		</div>
	</div>
</section>

<!-- P4 · Cuánto necesito -->
<section class="p-cuanto tono-blanco seccion" aria-labelledby="p-cuanto-t">
	<div class="envoltura p-cuanto__in">
		<div class="p-cuanto__cabeza">
			<?php echo dipt_titulo( 'h2', $c['cuanto']['titulo'], 't-h2', 'p-cuanto-t' ); // phpcs:ignore ?>
			<p class="suave" data-revela><?php echo esc_html( $c['cuanto']['texto'] ); ?></p>
			<p data-revela style="--i:1"><?php echo dipt_enlace( $c['cuanto']['calcula'], dipt_whatsapp_url(), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p>
		</div>
		<ol class="p-escalones">
			<?php foreach ( $c['cuanto']['casos'] as $i => $caso ) : ?>
				<li class="p-escalon p-escalon--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="p-escalon__kg"><?php echo esc_html( $caso[0] ); ?></span>
					<strong><?php echo esc_html( $caso[1] ); ?></strong>
					<span><?php echo esc_html( $caso[2] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- P5 · Tarifa de envío en billete -->
<section class="p-tarifa tono-escarcha seccion" aria-labelledby="p-tarifa-t">
	<div class="envoltura p-tarifa__in">
		<div>
			<?php echo dipt_titulo( 'h2', $c['tarifa']['titulo'], 't-h2', 'p-tarifa-t' ); // phpcs:ignore ?>
			<p class="entrada suave" data-revela><?php echo esc_html( $c['tarifa']['texto'] ); ?></p>
		</div>
		<div class="p-billete" data-revela="der">
			<div class="p-billete__cuerpo">
				<p class="p-billete__cab dato"><span>DryIcePack · Mataró</span><span>→ Península</span></p>
				<table>
					<?php foreach ( $c['tarifa']['filas'] as $fila ) : ?>
						<tr><th scope="row"><?php echo esc_html( $fila[0] ); ?></th><td><?php echo esc_html( dipt_euros( $fila[1] * $iva ) ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>
			<div class="p-billete__talon">
				<?php foreach ( $c['tarifa']['extra'] as $fila ) : ?>
					<p><span><?php echo esc_html( $fila[0] ); ?></span><strong><?php echo esc_html( $fila[1] > 0 ? '+' . dipt_euros( $fila[1] * $iva ) : $c['tarifa']['gratis'] ); ?></strong></p>
				<?php endforeach; ?>
				<p class="dato"><?php echo esc_html( $c['tarifa']['nota'] ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- P6 · Preguntas en conversación -->
<section class="p-faq tono-blanco seccion" aria-labelledby="p-faq-t">
	<div class="envoltura p-faq__in">
		<h2 class="t-h2" id="p-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="p-chat">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<div class="p-chat__par" data-revela style="--i:<?php echo (int) $i; ?>">
					<p class="p-chat__q"><?php echo esc_html( $f[0] ); ?></p>
					<p class="p-chat__a"><?php echo esc_html( $f[1] ); ?></p>
				</div>
			<?php endforeach; ?>
			<p class="p-chat__sigue"><a href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?><?php echo esc_html( $con['whatsapp'] ); ?></a> · <a href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a></p>
		</div>
	</div>
</section>

<!-- Barra de compra fija en móvil -->
<div class="p-barra" data-barra-compra>
	<p><span class="dato"><?php echo esc_html( $c['barra'] ); ?></span><strong data-precio-total><?php echo esc_html( dipt_euros( $p0['total'] ) ); ?></strong></p>
	<button type="button" class="boton" data-barra-comprar><span><?php echo esc_html( $c['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
</div>

<?php
get_footer();
