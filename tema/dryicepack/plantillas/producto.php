<?php
/**
 * Comprar hielo seco: ficha de producto de WooCommerce (castellano) y páginas de compra en catalán e inglés.
 * P1 configurador (galería fija del formato elegido + formulario con precio final y fecha) · P2 la caja fotografiada con
 * marcadores y cota · P3 comparador 3/16 mm · P4 cuánto necesito (marino) · P5 tarifa de envío en billete ·
 * P6 preguntas plegables en conversación. Barra de compra fija en móvil.
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
// Aviso: círculo con exclamación, mismo trazo que los iconos del tema (lo usan el campo del código postal y la línea de entrega)
$ico_aviso = '<svg class="ico p-ico-aviso" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.2M12 16.3v.2"/></svg>';

// Galería de cada formato: la caja lateral va en P2 (ninguna foto se repite en la página); su hueco lo ocupa la foto de cerca.
// cenital-caja-nuggets es la misma toma que caja-16mm-niebla (la foto grande) y que la caja de P2: también fuera.
$galeria = array();
foreach ( $c['galeria'] as $formato => $fotos ) {
	$fotos = array_values( array_filter( $fotos, static fn( $f ) => ! in_array( $f[0], array( 'caja-' . $formato . '-lateral', 'cenital-caja-nuggets' ), true ) ) );
	if ( count( $fotos ) < 3 && ! empty( $c['galeria_macro'][ $formato ] ) ) $fotos[] = $c['galeria_macro'][ $formato ];
	$galeria[ $formato ] = $fotos;
}
// P2 · marcadores sobre la foto de la caja (posición en % de la foto cuadrada: tapa, hielo, pared) y cota del grosor de la pared
$caja_marcas = array(
	'3mm'  => array( 'marcas' => array( array( 20, 52 ), array( 61, 34 ), array( 42, 66 ) ), 'cota' => array( 89.5, 30.5, 4.5 ) ),
	'16mm' => array( 'marcas' => array( array( 16, 52 ), array( 57, 27 ), array( 40, 62 ) ), 'cota' => array( 82.5, 28, 4.5 ) ),
);
$sizes_galeria = '(max-width: 1000px) 100vw, 44vw';
$GLOBALS['dipt_barra_compra'] = true;
dipt_precargar( $galeria[ $f0 ][0][0], $sizes_galeria );
dipt_registrar_faq( $c['faq']['lista'] );
add_filter( 'body_class', static fn( $b ) => array_merge( $b, array( 'hay-barra-compra' ) ) );

get_header();
if ( function_exists( 'wc_print_notices' ) ) {
	echo '<div class="envoltura p-avisos">';
	wc_print_notices();
	echo '</div>';
}
?>

<!-- P1 · Configurador: en escritorio, galería fija a la izquierda; en móvil, el H1 primero y la foto en carrusel -->
<section class="p-compra tono-papel" aria-labelledby="p-titulo" data-compra data-kg="<?php echo esc_attr( $kg0 ); ?>" data-formato="<?php echo esc_attr( $f0 ); ?>">
	<div class="envoltura p-compra__in">
		<div class="p-cabeza">
			<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'producto' ) ) ) ); // phpcs:ignore ?>
			<h1 class="t-h1 p-cabeza__titulo" id="p-titulo"><?php echo esc_html( $c['titulo'] ); ?></h1>
			<p class="p-cabeza__sub"><?php echo esc_html( $c['sub'] ); ?></p>
		</div>

		<div class="p-galeria" data-galeria>
			<?php foreach ( $galeria as $formato => $fotos ) : $n_fotos = count( $fotos ); ?>
				<div class="p-galeria__grupo<?php echo $formato === $f0 ? ' activo' : ''; ?>" data-galeria-formato="<?php echo esc_attr( $formato ); ?>"<?php echo $formato === $f0 ? '' : ' hidden'; ?>>
					<div class="p-galeria__pista" role="group" tabindex="0" aria-label="<?php echo esc_attr( $c['galeria_t'] ?? '' ); ?>" data-galeria-pista>
						<?php foreach ( $fotos as $i => $foto ) : ?>
							<figure class="p-galeria__foto p-galeria__foto--<?php echo (int) $i + 1; ?>">
								<?php echo dipt_foto( $foto[0], $foto[1], array( 'lcp' => ( 0 === $i && $formato === $f0 ), 'sizes' => 0 === $i ? $sizes_galeria : '(max-width: 1000px) 100vw, 180px' ) ); // phpcs:ignore ?>
							</figure>
						<?php endforeach; ?>
					</div>
					<?php // Dos copias recortadas por el borde de la foto: blanca encima de la foto y tinta por debajo, sobre el papel ?>
					<?php $cifra = esc_html( (string) (int) $formato ) . '<small>mm</small>'; ?>
					<span class="p-galeria__cifra" aria-hidden="true"><span class="p-galeria__cifra-foto"><?php echo $cifra; // phpcs:ignore ?></span><span class="p-galeria__cifra-papel"><?php echo $cifra; // phpcs:ignore ?></span></span>
					<span class="p-galeria__cuenta dato" aria-hidden="true"><span data-galeria-n>1</span>/<?php echo (int) $n_fotos; ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="p-panel">
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
					<?php // Regleta continua: un tramo por pack (4 o 5), separados por filetes, con su marca arriba ?>
					<div class="p-kilos<?php echo count( $packs ) > 4 ? ' p-kilos--cinco' : ''; ?>" style="--n:<?php echo (int) max( 1, count( $packs ) ); ?>">
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
					<?php // El código postal va con el pedido (dipt_cp): el checkout lo recibe escrito, también sin JavaScript ?>
					<div class="p-grupo p-grupo--cp">
						<label class="p-grupo__titulo" for="p-cp"><?php echo esc_html( $c['cp'] ); ?></label>
						<div class="p-cp" data-cp-caja data-cp-estado="">
							<input id="p-cp" name="dipt_cp" type="text" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code" placeholder="<?php echo esc_attr( $c['cp_ph'] ); ?>" aria-describedby="p-cp-nota" data-cp>
							<span class="p-cp__icono" aria-hidden="true"><span class="p-cp__giro"></span><?php echo dipt_icono( 'check', 'p-cp__ok' ) . $ico_aviso; // phpcs:ignore ?></span>
						</div>
						<p class="p-cp__nota" id="p-cp-nota" data-cp-nota<?php foreach ( array( 'ayuda', 'buscando', 'correcto', 'fuera', 'medida', 'no_valido', 'corto' ) as $k ) printf( ' data-t-%s="%s"', esc_attr( str_replace( '_', '-', $k ) ), esc_attr( $c[ 'cp_' . $k ] ?? '' ) ); ?>><?php echo esc_html( $c['cp_ayuda'] ?? '' ); ?></p>
					</div>
				</div>

				<p class="p-llega" data-llega data-t-llega="<?php echo esc_attr( $c['llega'] ); ?>"<?php foreach ( array( 'ok', 'sabado', 'fuera', 'medida', 'error' ) as $k ) printf( ' data-t-%s="%s"', esc_attr( $k ), esc_attr( $c[ 'llega_cp_' . $k ] ?? '' ) ); ?> data-wa="<?php echo esc_url( dipt_whatsapp_url() ); ?>" aria-live="polite">
					<span class="p-llega__ico" aria-hidden="true"><?php echo dipt_icono( 'furgoneta', 'p-llega__base' ) . dipt_icono( 'check', 'p-llega__ok' ) . $ico_aviso; // phpcs:ignore ?></span>
					<span class="p-llega__txt"><span data-llega-texto><?php echo esc_html( sprintf( $c['llega_hoy'], $e0 ? dipt_fecha_larga( $e0['fecha'] ) : '' ) ); ?></span><span class="p-llega__extra" data-llega-extra hidden></span></span>
				</p>

				<dl class="p-precio" aria-live="polite" data-precio>
					<div><dt data-precio-concepto><?php echo esc_html( $c['precio'] . ' · ' . $kg0 . ' kg' ); ?></dt><dd data-precio-pack><?php echo esc_html( dipt_euros( $p0['con_iva'] ) ); ?></dd></div>
					<div><dt><?php echo esc_html( $c['envio'] ); ?></dt><dd data-precio-envio><?php echo esc_html( dipt_euros( $p0['envio'] ) ); ?></dd></div>
					<div class="p-precio__total"><dt><?php echo esc_html( $c['total'] ); ?> <small><?php echo esc_html( $c['iva'] ); ?></small></dt><dd data-precio-total><?php echo esc_html( dipt_euros( $p0['total'] ) ); ?></dd></div>
				</dl>

				<button type="submit" class="boton boton--ancho p-comprar"><span><?php echo esc_html( $c['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
				<p class="p-nota"><?php echo esc_html( $c['recogida'] ); ?></p>
				<p class="p-nota p-nota--max" data-max hidden><?php echo esc_html( $c['max'] ); ?></p>
				<p class="p-nota p-nota--max" data-medida hidden><?php echo esc_html( $c['medida'] ); ?> <a href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener">WhatsApp</a></p>
				<ul class="p-confianza">
					<?php foreach ( $c['confianza'] as $x ) : ?><li><?php echo dipt_icono( 'check' ); // phpcs:ignore ?><?php echo esc_html( $x ); ?></li><?php endforeach; ?>
				</ul>
			</form>
		</div>
	</div>
</section>

<!-- P2 · La caja fotografiada: marcadores 01–03 enlazados con la lista y cota del grosor de la pared -->
<section class="p-caja tono-oscuro seccion" aria-labelledby="p-caja-t">
	<div class="envoltura p-caja__in" data-activa-grupo>
		<div class="p-caja__texto">
			<?php echo dipt_titulo( 'h2', $c['caja']['titulo'], 't-h2', 'p-caja-t' ); // phpcs:ignore ?>
			<ol class="p-caja__capas">
				<?php foreach ( $c['caja']['capas'] as $i => $capa ) : ?>
					<li data-activa data-revela style="--i:<?php echo (int) $i; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><strong><?php echo esc_html( $capa[0] ); ?></strong><span><?php echo esc_html( $capa[1] ); ?></span></li>
				<?php endforeach; ?>
			</ol>
		</div>
		<div class="p-caja__fotos">
			<?php foreach ( $caja_marcas as $formato => $m ) : ?>
				<div class="foto--sangre foto--sangre-der foto--1x1 p-caja__foto" data-caja-formato="<?php echo esc_attr( $formato ); ?>" data-vivo<?php echo $formato === $f0 ? '' : ' hidden'; ?>>
					<?php echo dipt_foto( 'caja-' . $formato . '-lateral', $c['caja_foto'][ $formato ] ?? '', array( 'sizes' => '(max-width: 900px) 100vw, 58vw' ) ); // phpcs:ignore ?>
					<div class="niebla p-caja__niebla" aria-hidden="true"><i></i><i></i></div>
					<?php foreach ( $m['marcas'] as $j => $xy ) : ?>
						<span class="p-marca p-marca--<?php echo (int) $j + 1; ?>" style="--x:<?php echo esc_attr( $xy[0] ); ?>%;--y:<?php echo esc_attr( $xy[1] ); ?>%;--i:<?php echo (int) $j; ?>" aria-hidden="true"><span class="p-marca__linea"></span><span class="p-marca__n">0<?php echo (int) $j + 1; ?></span></span>
					<?php endforeach; ?>
					<span class="cota p-caja__cota" style="--x:<?php echo esc_attr( $m['cota'][0] ); ?>%;--y:<?php echo esc_attr( $m['cota'][1] ); ?>%;--largo:<?php echo esc_attr( $m['cota'][2] ); ?>%" aria-hidden="true"><span class="cota__valor">40 mm</span><span class="cota__linea"></span></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- P3 · Comparador 3 mm / 16 mm -->
<section class="p-comparar tono-papel seccion" aria-labelledby="p-comparar-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['comparar']['titulo'], 't-h2 p-comparar__titulo', 'p-comparar-t', 'revela' ); // phpcs:ignore ?>
		<?php // data-vivo: al entrar (.visto), la línea va del 50 % al 35 % y vuelve una vez, para que se vea que se arrastra ?>
		<div class="p-comparador" data-comparador data-vivo style="--corte:50%">
			<div class="p-comparador__img p-comparador__img--der"><?php echo dipt_foto( 'cenital-16mm', '', array( 'sizes' => '(max-width: 1100px) 100vw, 1100px' ) ); // phpcs:ignore ?></div>
			<div class="p-comparador__img p-comparador__img--izq"><?php echo dipt_foto( 'cenital-3mm', '', array( 'sizes' => '(max-width: 1100px) 100vw, 1100px' ) ); // phpcs:ignore ?></div>
			<span class="p-comparador__marca p-comparador__marca--izq dato" aria-hidden="true"><?php echo esc_html( $c['comparar']['izq'][0] ); ?></span>
			<span class="p-comparador__marca p-comparador__marca--der dato" aria-hidden="true"><?php echo esc_html( $c['comparar']['der'][0] ); ?></span>
			<div class="p-comparador__linea" aria-hidden="true"><span></span></div>
			<input type="range" min="0" max="100" value="50" aria-label="<?php echo esc_attr( $c['comparar']['rango'] ); ?>" data-comparador-rango>
		</div>
		<div class="p-comparar__leyenda">
			<p class="p-comparador__rotulo p-comparador__rotulo--izq" data-revela><strong><?php echo esc_html( $c['comparar']['izq'][0] ); ?></strong><?php echo esc_html( $c['comparar']['izq'][1] ); ?></p>
			<p class="p-comparador__rotulo p-comparador__rotulo--der" data-revela style="--i:1"><strong><?php echo esc_html( $c['comparar']['der'][0] ); ?></strong><?php echo esc_html( $c['comparar']['der'][1] ); ?></p>
		</div>
	</div>
</section>

<!-- P4 · Cuánto necesito (marino) -->
<section class="p-cuanto tono-marino seccion" aria-labelledby="p-cuanto-t">
	<div class="envoltura p-cuanto__in">
		<div class="p-cuanto__cabeza">
			<?php echo dipt_titulo( 'h2', $c['cuanto']['titulo'], 't-h2', 'p-cuanto-t', 'cortina' ); // phpcs:ignore ?>
			<p class="suave" data-revela><?php echo esc_html( $c['cuanto']['texto'] ); ?></p>
			<p data-revela style="--i:1"><?php echo dipt_enlace( $c['cuanto']['calcula'], dipt_whatsapp_url(), array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?></p>
			<?php if ( ! empty( $c['cuanto']['seguridad'] ) ) : // Caldero, ponchera y niebla: antes de usarlo, las reglas de seguridad ?>
				<p data-revela style="--i:2"><?php echo dipt_enlace( $c['cuanto']['seguridad'], dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
			<?php endif; ?>
		</div>
		<ol class="p-escalones">
			<?php foreach ( $c['cuanto']['casos'] as $i => $caso ) : ?>
				<li class="p-escalon p-escalon--<?php echo (int) $i + 1; ?>" data-revela style="--i:<?php echo (int) $i; ?>">
					<?php // La cifra en grande y la unidad aparte, para que "15–20 kg" no parta entre el número y "kg" ?>
					<span class="p-escalon__kg"><?php echo preg_match( '/^(.+?)\s*(kg)$/u', (string) $caso[0], $m_kg ) ? esc_html( $m_kg[1] ) . '<small>' . esc_html( $m_kg[2] ) . '</small>' : esc_html( $caso[0] ); ?></span>
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
		<div class="texto">
			<?php echo dipt_titulo( 'h2', $c['tarifa']['titulo'], 't-h2', 'p-tarifa-t', 'revela' ); // phpcs:ignore ?>
			<p class="entrada suave" data-revela><?php echo esc_html( $c['tarifa']['texto'] ); ?></p>
			<?php if ( ! empty( $c['tarifa']['enlace'] ) ) : ?>
				<p data-revela style="--i:1"><?php echo dipt_enlace( $c['tarifa']['enlace'], dipt_url( 'envios' ) ); // phpcs:ignore ?></p>
			<?php endif; ?>
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

<!-- P6 · Preguntas en conversación: cada pregunta es una burbuja que se abre (la primera, abierta) -->
<section class="p-faq tono-blanco seccion" aria-labelledby="p-faq-t">
	<div class="envoltura p-faq__in">
		<div class="p-faq__cabeza">
			<h2 class="t-h2" id="p-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
			<div class="p-faq__contacto" data-revela style="--i:1">
				<p class="p-faq__otra"><?php echo esc_html( dipt_t( 'faq_otra' ) ); ?></p>
				<a class="p-faq__tel" href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a>
				<a class="p-faq__wa" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?><span>WhatsApp · <?php echo esc_html( $con['whatsapp'] ); ?></span></a>
			</div>
			<?php if ( ! empty( $c['faq']['empresas'] ) ) : ?>
				<p class="p-faq__empresas" data-revela style="--i:2"><?php echo dipt_enlace( $c['faq']['empresas'], dipt_url( 'empresas' ) ); // phpcs:ignore ?></p>
			<?php endif; ?>
		</div>
		<div class="p-chat">
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<details class="p-chat__par" data-revela style="--i:<?php echo (int) min( $i, 3 ); ?>"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary class="p-chat__q"><span><?php echo esc_html( $f[0] ); ?></span><span class="p-chat__mas" aria-hidden="true"></span></summary>
					<p class="p-chat__a"><?php echo esc_html( $f[1] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Barra de compra fija en móvil: aparece al dejar atrás el H1 y se aparta mientras el precio o el botón están en pantalla -->
<div class="p-barra" data-barra-compra>
	<p><span class="dato"><?php echo esc_html( $c['barra'] ); ?></span><strong data-precio-total><?php echo esc_html( dipt_euros( $p0['total'] ) ); ?></strong></p>
	<button type="button" class="boton" data-barra-comprar><span><?php echo esc_html( $c['boton'] ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></button>
</div>

<?php
get_footer();
