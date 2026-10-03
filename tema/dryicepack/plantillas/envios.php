<?php
/**
 * Envíos y plazos: V1 cuenta atrás del corte a segundos · V2 semana con flechas de pedido a entrega ·
 * V3 calculadora de envío con desglose (cajas como en la tienda) · V4 recogida con foto y lista de ficha ·
 * V5 cobertura sí / consulta · V6 preguntas en pestañas.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'envios' );
$con = dipt_contacto();
$iva = dipt_factor_iva();
$e0  = dipt_primera_entrega();

/*
 * V3 · Paradas de la calculadora. Cada parada es un pedido real: cajas del pack más grande (20 kg) y el resto
 * en el pack más pequeño que lo cubre (3, 10 o 15 kg). El coste sale de dipt_coste_envio(), la misma función
 * que cobra el checkout (peso facturable = hielo + 1 kg por caja, redondeado al alza), y el IVA se redondea
 * como el de WooCommerce. Por encima de 150 kg no hay precio: se prepara a medida.
 */
$t = array_replace_recursive( (array) ( dipt_contenido( 'envios', 'es' )['tarifa'] ?? array() ), (array) ( $c['tarifa'] ?? array() ) );
$v_en      = 'en' === dipt_idioma();
$v_num     = static fn( $n ) => $v_en ? (string) ( 0 + $n ) : str_replace( '.', ',', (string) ( 0 + $n ) );
$v_packs   = array_map( 'floatval', array_keys( dipt_packs() ) );
sort( $v_packs, SORT_NUMERIC );
$v_mayor   = $v_packs ? max( $v_packs ) : 20.0;
$v_limite  = defined( 'DIP_KG_A_MEDIDA' ) ? (float) DIP_KG_A_MEDIDA : 150.0;
$v_emb     = defined( 'DIP_PESO_EMBALAJE' ) ? (float) DIP_PESO_EMBALAJE : 1.0;
$v_tarifa  = array(
	2  => defined( 'DIP_TARIFA_HASTA_2' ) ? DIP_TARIFA_HASTA_2 : 8.53,
	5  => defined( 'DIP_TARIFA_HASTA_5' ) ? DIP_TARIFA_HASTA_5 : 10.32,
	10 => defined( 'DIP_TARIFA_HASTA_10' ) ? DIP_TARIFA_HASTA_10 : 13.19,
);
$v_kg_extra = defined( 'DIP_TARIFA_KG_EXTRA' ) ? DIP_TARIFA_KG_EXTRA : 1.12;
$v_pct      = round( ( $iva - 1 ) * 100, 2 );
$v_iva_txt  = sprintf( $t['filas']['iva'], $v_num( $v_pct ) . ( $v_en ? '%' : ' %' ) );

$v_paradas = array();
$v_vistos  = array();
for ( $k = 1; $k <= $v_limite; $k++ ) {
	$cajas = $v_mayor > 0 ? array_fill( 0, (int) floor( $k / $v_mayor ), $v_mayor ) : array();
	$resto = $k - $v_mayor * count( $cajas );
	if ( $resto > 0.0001 ) {
		foreach ( $v_packs as $p ) {
			if ( $p >= $resto - 0.0001 ) { $cajas[] = $p; break; }
		}
	}
	$kg = array_sum( $cajas );
	if ( ! $cajas || $kg > $v_limite + 0.0001 || isset( $v_vistos[ (string) $kg ] ) ) continue;
	$v_vistos[ (string) $kg ] = true;
	$n     = count( $cajas );
	$f     = (int) ceil( $kg + $v_emb * $n );
	$sin   = (float) dipt_coste_envio( $kg, $n );
	$iva_e = round( $sin * ( $iva - 1 ), 2 );
	// "2 cajas de 20 kg + 1 de 10 kg"
	$grupos = array_count_values( array_map( static fn( $x ) => (string) ( 0 + $x ), $cajas ) );
	krsort( $grupos, SORT_NUMERIC );
	$desglose = '';
	foreach ( $grupos as $peso => $cuantas ) {
		$desglose .= '' === $desglose ? sprintf( 1 === $cuantas ? $t['caja_una'] : $t['caja_varias'], $cuantas, $v_num( $peso ) ) : sprintf( $t['caja_resto'], $cuantas, $v_num( $peso ) );
	}
	// De dónde sale el precio sin IVA: tramo fijo o "13,19 € hasta 10 kg + 36 kg × 1,12 €"
	if ( $f > 10 ) $tarifa = sprintf( $t['extra'], dipt_euros( $v_tarifa[10] ), $f - 10, dipt_euros( $v_kg_extra ) );
	else $tarifa = sprintf( $t['tramo'], $f <= 2 ? 2 : ( $f <= 5 ? 5 : 10 ) );
	$tarifa = preg_replace( '/(\d) (€|kg)/u', "\$1\u{00A0}\$2", $tarifa ); // la cifra y su unidad no se separan al cortar la línea
	$v_paradas[] = array(
		'kg'     => 0 + $kg,
		'cajas'  => array_map( static fn( $x ) => 0 + $x, $cajas ),
		'texto'  => $desglose,
		'hielo'  => $v_num( $kg ) . ' kg',
		'emb'    => '+ ' . $v_num( $v_emb * $n ) . ' kg',
		'peso'   => $f . ' kg',
		'tarifa' => $tarifa,
		'sin'    => dipt_euros( $sin ),
		'iva'    => dipt_euros( $iva_e ),
		'total'  => dipt_euros( $sin + $iva_e ),
	);
}
usort( $v_paradas, static fn( $a, $b ) => $a['kg'] <=> $b['kg'] );
$v_ini = 0;
foreach ( $v_paradas as $i => $p ) {
	if ( 10.0 === (float) $p['kg'] ) $v_ini = $i;
}
$v_p = $v_paradas[ $v_ini ] ?? null;
/* Caja dibujada según el pack: la de 15 y 20 kg es la grande; la de 10 kg, mediana; la de 3 kg, pequeña. */
$v_caja = static function ( $kg ) use ( $v_num ) {
	$tam = $kg >= 15 ? 'g' : ( $kg >= 10 ? 'm' : 'p' );
	return '<span class="v-caja v-caja--' . $tam . '">' . esc_html( $v_num( $kg ) ) . '</span>';
};
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

<!-- V3 · Calculadora de envío: kilos → cajas como en la tienda → desglose del precio (o "a medida" por encima de 150 kg) -->
<?php if ( $v_p ) : ?>
<?php
// JSON entre comillas simples: ' & < > " salen como \u00XX (JSON_HEX_*), así que no puede cerrar el atributo ni abrir etiquetas,
// y el atributo pesa la mitad que con &quot;.
$v_json = wp_json_encode( $v_paradas, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG );
?>
<section class="v-tarifa tono-blanco seccion" aria-labelledby="v-tarifa-t" data-calc-envio data-paradas='<?php echo $v_json; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON con JSON_HEX_* ?>'data-t-mas="<?php echo esc_attr( $t['mas'] ); ?>" data-t-mas-cifra="<?php echo esc_attr( $t['mas_cifra'] ); ?>" data-t-medida-cajas="<?php echo esc_attr( $t['medida']['cajas'] ); ?>">
	<div class="envoltura v-tarifa__in">
		<div class="v-tarifa__mando">
			<?php echo dipt_titulo( 'h2', $t['titulo'], 't-h2', 'v-tarifa-t' ); // phpcs:ignore ?>
			<div class="v-tarifa__controles">
				<label class="v-tarifa__etiqueta" for="v-kg"><span class="dato"><?php echo esc_html( $t['kg'] ); ?></span> <span class="v-tarifa__kg"><output for="v-kg" data-calc-kg><?php echo esc_html( $v_num( $v_p['kg'] ) ); ?></output> kg</span></label>
				<input id="v-kg" type="range" min="0" max="<?php echo (int) count( $v_paradas ); ?>" step="1" value="<?php echo (int) $v_ini; ?>" aria-valuetext="<?php echo esc_attr( $v_p['hielo'] . ' · ' . $v_p['texto'] ); ?>" data-calc-rango>
				<p class="v-tarifa__escala dato" aria-hidden="true"><span><?php echo esc_html( $v_paradas[0]['hielo'] ); ?></span><span><?php echo esc_html( $t['mas'] ); ?></span></p>
			</div>
			<div class="v-envio-cajas">
				<p class="dato v-envio-cajas__titulo"><?php echo esc_html( $t['cajas_titulo'] ); ?></p>
				<div class="v-cajas" data-calc-cajas aria-hidden="true"><?php foreach ( $v_p['cajas'] as $kg ) echo $v_caja( $kg ); // phpcs:ignore ?></div>
				<p class="v-envio-cajas__texto" data-calc-texto><?php echo esc_html( $v_p['texto'] ); ?></p>
			</div>
			<p class="suave v-tarifa__nota"><?php echo esc_html( $t['nota'] ); ?></p>
		</div>
		<div class="v-tarifa__resultado">
			<div class="v-tarifa__vista v-tarifa__vista--precio" data-vista-precio>
				<p class="v-tarifa__coste"><span class="dato"><?php echo esc_html( $t['coste_iva'] ); ?></span><strong data-calc="total" aria-live="polite"><?php echo esc_html( $v_p['total'] ); ?></strong></p>
				<dl class="v-recibo">
					<div><dt><?php echo esc_html( $t['filas']['hielo'] ); ?></dt><dd data-calc="hielo"><?php echo esc_html( $v_p['hielo'] ); ?></dd></div>
					<div><dt><?php echo esc_html( $t['filas']['embalaje'] ); ?></dt><dd data-calc="emb"><?php echo esc_html( $v_p['emb'] ); ?></dd></div>
					<div class="v-recibo__suma"><dt><?php echo esc_html( $t['filas']['peso'] ); ?> <small><?php echo esc_html( $t['filas']['redondeo'] ); ?></small></dt><dd data-calc="peso"><?php echo esc_html( $v_p['peso'] ); ?></dd></div>
					<div><dt><?php echo esc_html( $t['filas']['sin_iva'] ); ?> <small data-calc="tarifa"><?php echo esc_html( $v_p['tarifa'] ); ?></small></dt><dd data-calc="sin"><?php echo esc_html( $v_p['sin'] ); ?></dd></div>
					<div><dt><?php echo esc_html( $v_iva_txt ); ?></dt><dd data-calc="iva"><?php echo esc_html( $v_p['iva'] ); ?></dd></div>
				</dl>
				<?php /* Pedir justo esta combinación: se deja en el carrito tal cual y se va al pago (inc/woocommerce.php, dipt_pedido) */ ?>
				<div class="v-pedir" data-calc-pedir-caja>
					<fieldset class="v-pedir__formato">
						<legend class="dato"><?php echo esc_html( $t['pedir']['formato'] ); ?></legend>
						<?php foreach ( array( '3mm', '16mm' ) as $v_i => $v_f ) : ?>
							<label><input type="radio" name="v-formato" value="<?php echo esc_attr( $v_f ); ?>"<?php checked( 0, $v_i ); ?> data-calc-formato><span><?php echo esc_html( $t['pedir'][ 'f' . $v_f ] ); ?></span></label>
						<?php endforeach; ?>
					</fieldset>
					<a class="boton v-pedir__boton" href="<?php echo esc_url( dipt_url_pedido( $v_p['cajas'], '3mm' ) ); ?>" data-calc-pedir data-t-boton="<?php echo esc_attr( $t['pedir']['boton'] ); ?>" data-base="<?php echo esc_url( home_url( '/' ) ); ?>"><span data-calc-pedir-texto><?php echo esc_html( sprintf( $t['pedir']['boton'], $v_num( $v_p['kg'] ) ) ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></a>
					<p class="v-pedir__nota"><?php echo esc_html( $t['pedir']['nota'] ); ?></p>
				</div>
			</div>
			<div class="v-tarifa__vista v-tarifa__vista--medida" data-vista-medida>
				<p class="dato v-medida__etiqueta"><?php echo esc_html( $t['mas'] ); ?></p>
				<p class="v-medida__titulo"><?php echo esc_html( $t['medida']['titulo'] ); ?></p>
				<p class="v-medida__texto"><?php echo esc_html( $t['medida']['texto'] ); ?></p>
				<?php echo dipt_boton( $t['medida']['boton'], dipt_whatsapp_url( $t['medida']['wa'] ), 'blanco', array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
				<p class="dato v-medida__tel"><?php echo esc_html( $t['medida']['tel'] ); ?> <a href="<?php echo esc_attr( $con['telefono_href'] ); ?>"><?php echo esc_html( $con['telefono'] ); ?></a></p>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

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

<!-- V5 · Cobertura: sí (península) / no (Baleares, Canarias, Ceuta y Melilla) -->
<section class="v-cobertura tono-marino seccion" aria-labelledby="v-cobertura-t">
	<div class="envoltura">
		<h2 class="t-h2" id="v-cobertura-t" data-revela><?php echo esc_html( $c['cobertura']['titulo'] ); ?></h2>
		<div class="v-cobertura__dos">
			<div class="v-cobertura__si" data-revela="izq"><span class="v-cobertura__marca" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span><h3><?php echo esc_html( $c['cobertura']['si']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['si']['texto'] ); ?></p></div>
			<div class="v-cobertura__no" data-revela="der"><span class="v-cobertura__marca" aria-hidden="true"><?php echo dipt_icono( 'cerrar' ); // phpcs:ignore ?></span><h3><?php echo esc_html( $c['cobertura']['no']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['no']['texto'] ); ?></p></div>
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
