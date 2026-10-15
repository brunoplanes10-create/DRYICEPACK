<?php
/**
 * Envíos y plazos (rediseño del 14/10/2026, plan 2.4). Tonos: V1 noche · V2 papel · V4 escarcha · V3 blanco · V5 marino · V6 blanco.
 * V1 cuenta atrás hasta el corte (pasado el de hoy, hasta el siguiente corte válido) con la furgoneta a sangre ·
 * V2 semana con la vía que se dibuja · V4 recogida: foto a sangre con la dirección y fila de precios de mostrador ·
 * V3 calculadora de envío (regla de rango y cajas que caen; mismos números que el checkout) ·
 * V5 mapa de cobertura con el código postal · V6 dudas: acordeón en móvil y pestañas con panel noche en escritorio.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c   = dipt_contenido( 'envios' );
$cp  = dipt_contenido( 'producto' ); // textos del código postal: los mismos que la ficha de compra
$con = dipt_contacto();
$iva = dipt_factor_iva();
$e0  = dipt_primera_entrega();
$idioma = dipt_idioma();

/*
 * V3 · Paradas de la calculadora. Cada parada es un pedido real: cajas del pack más grande (20 kg) y el resto
 * en el pack más pequeño que lo cubre (3, 10 o 15 kg). El coste sale de dipt_coste_envio(), la misma función
 * que cobra el checkout (peso facturable = hielo + 1 kg por caja, redondeado al alza), y el IVA se redondea
 * como el de WooCommerce. Por encima de 150 kg no hay precio: se prepara a medida.
 */
$t = array_replace_recursive( (array) ( dipt_contenido( 'envios', 'es' )['tarifa'] ?? array() ), (array) ( $c['tarifa'] ?? array() ) );
$v_en      = 'en' === $idioma;
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
/* Marcas de la regla: la primera parada, 50 y 100 kg (si son paradas) y «+150» al final. --en = posición / última posición.
   (150 kg es la penúltima posición: su marca chocaría con la de «+150».) */
$v_total  = count( $v_paradas );
$v_marcas = array();
if ( $v_paradas ) {
	$v_marcas[] = array( 0, $v_num( $v_paradas[0]['kg'] ) );
	foreach ( $v_paradas as $i => $p ) {
		if ( $i > 0 && in_array( (float) $p['kg'], array( 50.0, 100.0 ), true ) ) $v_marcas[] = array( $i, $v_num( $p['kg'] ) );
	}
	$v_marcas[] = array( $v_total, $t['mas_cifra'] );
}
/* Caja dibujada según el pack: la de 15 y 20 kg es la grande; la de 10 kg, mediana; la de 3 kg, pequeña. */
$v_caja = static function ( $kg ) use ( $v_num ) {
	$tam = $kg >= 15 ? 'g' : ( $kg >= 10 ? 'm' : 'p' );
	return '<span class="v-caja v-caja--' . $tam . '">' . esc_html( $v_num( $kg ) ) . '</span>';
};

/* V1 · Hora del corte y día en que sale el próximo pedido (el reloj cuenta hasta ese corte) */
$v_corte_h = defined( 'DIP_CUTOFF_HOUR' ) ? (int) DIP_CUTOFF_HOUR : 12;
$v_corte_m = defined( 'DIP_CUTOFF_MINUTE' ) ? (int) DIP_CUTOFF_MINUTE : 0;
$v_corte   = sprintf( '%d:%02d', $v_corte_h, $v_corte_m );
$v_sale    = $e0 && ! empty( $e0['sale'] ) ? dipt_fecha_larga( $e0['sale'] ) . ' · ' . $v_corte : '';

/* V2 · Nombre corto de cada día (móvil): los del calendario del checkout; si no está el plugin, las 3 primeras letras */
$v_cortos = function_exists( 'dip_nombres_fecha' ) ? (array) ( dip_nombres_fecha( $idioma )['dc'] ?? array() ) : array();

/* V4 · Precios de recogida con IVA de los packs que se venden ahora (los mismos que la portada) */
$v_rec_precios = dipt_precios_recogida();
$v_rec         = array();
foreach ( array_keys( dipt_packs() ) as $kg ) {
	$k = (string) ( 0 + $kg );
	if ( isset( $v_rec_precios[ $k ] ) ) $v_rec[ $k ] = $v_rec_precios[ $k ];
}

/* 82vw y no 44vw: la foto ocupa el 42 % del ancho pero todo el alto del hero (recorte «cover»); con 44vw cargaba la
   de 900 px estirada un 30 % en 1440. La de 1400 pesa 36 KB (≤ 120 KB del hero). */
dipt_precargar( 'reparto-furgoneta', '(max-width: 900px) 100vw, 82vw' );
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_registrar_faq( $c['faq']['lista'] );
get_header();
?>

<!-- V1 · Cuenta atrás hasta el corte, con la furgoneta a sangre por la derecha -->
<section class="v-hero tono-oscuro" aria-labelledby="v-hero-t" data-cuenta-atras data-t-antes="<?php echo esc_attr( $c['hero']['reloj']['antes'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['reloj']['despues'] ); ?>" data-hora="<?php echo esc_attr( $v_corte ); ?>">
	<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>
	<div class="v-hero__foto foto--sangre">
		<?php echo dipt_foto( 'reparto-furgoneta', $c['hero']['alt'] ?? '', array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 82vw', 'recorte' => '12% 50%' ) ); // 12 %: el logotipo de la chaqueta entero en el recorte vertical de 1024 y 1440 (con 30 % salía «YICEPACK») phpcs:ignore ?>
	</div>
	<div class="envoltura v-hero__in">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'envios' ) ) ) ); // phpcs:ignore ?>
		<h1 class="t-h1 v-hero__titulo" id="v-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<div class="v-reloj" aria-live="off">
			<p class="v-reloj__etiqueta dato" data-reloj-etiqueta><?php echo esc_html( $c['hero']['reloj']['antes'] ); ?></p>
			<p class="v-reloj__cifras" data-reloj-cifras aria-hidden="true"><span data-h>00</span><i>:</i><span data-m>00</span><i>:</i><span data-s>00</span></p>
			<p class="v-reloj__hasta dato" data-reloj-hasta aria-hidden="true"><?php echo esc_html( $v_sale ); ?></p>
		</div>
		<p class="entrada v-hero__llega" data-corte data-t-antes="<?php echo esc_attr( $c['hero']['llega']['antes'] ); ?>" data-t-despues="<?php echo esc_attr( $c['hero']['llega']['despues'] ); ?>"><span data-corte-texto><?php echo esc_html( sprintf( $c['hero']['llega']['despues'], $e0 ? dipt_fecha_larga( $e0['fecha'] ) : '' ) ); ?></span></p>
		<p class="v-hero__texto suave"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
	</div>
</section>

<!-- V2 · La semana: de qué día pides a qué día llega -->
<section class="v-semana tono-papel seccion seccion--densa" aria-labelledby="v-semana-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['semana']['titulo'], 't-h2 v-semana__titulo', 'v-semana-t' ); // phpcs:ignore ?>
		<div class="v-calendario" role="table" aria-label="<?php echo esc_attr( wp_strip_all_tags( str_replace( '|', ' ', $c['semana']['titulo'] ) ) ); ?>">
			<div class="v-calendario__cab" role="row">
				<?php foreach ( $c['semana']['dias'] as $i => $dia ) : $corto = $v_cortos[ $i ] ?? mb_substr( $dia, 0, 3 ); ?>
					<span role="columnheader" class="<?php echo $i >= 5 ? 'finde' : ''; ?>"><span class="v-dia-largo"><?php echo esc_html( $dia ); ?></span><span class="v-dia-corto" aria-hidden="true"><?php echo esc_html( $corto ); ?></span></span>
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

<!-- V4 · Recogida en Mataró: foto a sangre con la dirección y la fila de precios de mostrador -->
<section class="v-recogida tono-escarcha seccion" aria-labelledby="v-recogida-t" id="recogida">
	<div class="envoltura v-recogida__in">
		<div class="v-recogida__foto foto--sangre foto--sangre-izq" data-cortina>
			<?php echo dipt_foto( 'cajas-oscuro', $c['recogida']['alt_cajas'] ?? '', array( 'sizes' => '(max-width: 900px) 100vw, 58vw' ) ); // phpcs:ignore ?>
			<p class="foto__franja v-recogida__dir dato"><?php echo esc_html( $con['direccion'] ); ?><br><?php echo esc_html( $con['cp'] . ' ' . $con['localidad'] ); ?></p>
		</div>
		<div class="v-recogida__ficha">
			<?php echo dipt_titulo( 'h2', $c['recogida']['titulo'], 't-h2', 'v-recogida-t', 'cortina' ); // phpcs:ignore ?>
			<dl class="v-ficha">
				<?php foreach ( $c['recogida']['lista'] as $i => $l ) : ?>
					<div data-revela style="--i:<?php echo (int) $i; ?>"><dt class="dato"><?php echo esc_html( $l[0] ); ?></dt><dd><?php echo esc_html( $l[1] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
			<?php echo dipt_enlace( $c['recogida']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
		</div>
	</div>
	<?php if ( $v_rec ) : ?>
		<div class="envoltura">
			<dl class="v-precios" data-vivo>
				<?php $i = 0; foreach ( $v_rec as $kg => $precio ) : ?>
					<div style="--i:<?php echo (int) $i++; ?>"><dt class="dato"><?php echo esc_html( $v_num( $kg ) . ' ' . dipt_t( 'kg' ) ); ?></dt><dd><?php echo esc_html( dipt_euros( $precio, 0 ) ); ?></dd></div>
				<?php endforeach; ?>
				<div class="v-precios__pago" style="--i:<?php echo (int) $i; ?>"><dt class="dato"><?php echo esc_html( dipt_t( 'iva_incl' ) ); ?></dt><dd><?php echo esc_html( $c['recogida']['efectivo'] ?? '' ); ?></dd></div>
			</dl>
		</div>
	<?php endif; ?>
</section>

<!-- V3 · Calculadora de envío: kilos → cajas como en la tienda → desglose del precio (o "a medida" por encima de 150 kg) -->
<?php if ( $v_p ) : ?>
<?php
// JSON entre comillas simples: ' & < > " salen como \u00XX (JSON_HEX_*), así que no puede cerrar el atributo ni abrir etiquetas,
// y el atributo pesa la mitad que con &quot;.
$v_json = wp_json_encode( $v_paradas, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG );
?>
<section class="v-tarifa tono-blanco seccion seccion--densa" aria-labelledby="v-tarifa-t" data-calc-envio data-paradas='<?php echo $v_json; // phpcs:ignore WordPress.Security.EscapeOutput -- JSON con JSON_HEX_* ?>' data-t-mas="<?php echo esc_attr( $t['mas'] ); ?>" data-t-mas-cifra="<?php echo esc_attr( $t['mas_cifra'] ); ?>" data-t-medida-cajas="<?php echo esc_attr( $t['medida']['cajas'] ); ?>">
	<div class="envoltura v-tarifa__in">
		<div class="v-tarifa__mando">
			<?php echo dipt_titulo( 'h2', $t['titulo'], 't-h2', 'v-tarifa-t', 'revela' ); // phpcs:ignore ?>
			<div class="v-tarifa__controles">
				<label class="v-tarifa__etiqueta" for="v-kg"><span class="dato"><?php echo esc_html( $t['kg'] ); ?></span> <span class="v-tarifa__kg"><output for="v-kg" data-calc-kg><?php echo esc_html( $v_num( $v_p['kg'] ) ); ?></output> kg</span></label>
				<div class="regla-rango v-regla">
					<input id="v-kg" type="range" min="0" max="<?php echo (int) $v_total; ?>" step="1" value="<?php echo (int) $v_ini; ?>" aria-valuetext="<?php echo esc_attr( $v_p['hielo'] . ' · ' . $v_p['texto'] ); ?>" data-calc-rango>
					<ol class="regla-rango__marcas" aria-hidden="true">
						<?php foreach ( $v_marcas as $m ) : ?><li style="--en:<?php echo esc_attr( number_format( $v_total ? $m[0] / $v_total : 0, 4, '.', '' ) ); ?>"><?php echo esc_html( $m[1] ); ?></li><?php endforeach; ?>
					</ol>
				</div>
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
					<a class="boton v-pedir__boton" href="<?php echo esc_url( dipt_url_pedido( $v_p['cajas'], '3mm' ) ); ?>" rel="nofollow" data-calc-pedir data-t-boton="<?php echo esc_attr( $t['pedir']['boton'] ); ?>" data-base="<?php echo esc_url( home_url( '/' ) ); ?>"><span data-calc-pedir-texto><?php echo esc_html( sprintf( $t['pedir']['boton'], $v_num( $v_p['kg'] ) ) ); ?></span><span class="boton__flecha"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span></a>
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

<!-- V5 · Cobertura: mapa propio (península sí; islas, Ceuta y Melilla no) y código postal con la fecha del destino -->
<section class="v-cobertura tono-marino seccion" aria-labelledby="v-cobertura-t" data-cobertura>
	<div class="envoltura v-cobertura__in">
		<div class="v-cobertura__texto">
			<?php echo dipt_titulo( 'h2', $c['cobertura']['titulo'], 't-h2', 'v-cobertura-t', 'revela' ); // phpcs:ignore ?>
			<div class="v-cp" data-cp
				data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'dipt_entrega_cp' ) ); ?>"
				data-t-ayuda="<?php echo esc_attr( $cp['cp_ayuda'] ?? '' ); ?>"
				data-t-buscando="<?php echo esc_attr( $cp['cp_buscando'] ?? '' ); ?>"
				data-t-corto="<?php echo esc_attr( $cp['cp_corto'] ?? '' ); ?>"
				data-t-ok="<?php echo esc_attr( $cp['llega_cp_ok'] ?? '' ); ?>"
				data-t-fuera="<?php echo esc_attr( $cp['llega_cp_fuera'] ?? '' ); ?>"
				data-t-error="<?php echo esc_attr( $cp['llega_cp_error'] ?? '' ); ?>"
				data-t-llega="<?php echo esc_attr( $cp['llega'] ?? '' ); ?>">
				<label class="v-cp__etiqueta dato" for="v-cp"><?php echo esc_html( $cp['cp'] ?? '' ); ?></label>
				<input class="v-cp__campo" id="v-cp" type="text" inputmode="numeric" maxlength="5" pattern="[0-9]{5}" autocomplete="postal-code" placeholder="<?php echo esc_attr( $cp['cp_ph'] ?? '' ); ?>" aria-describedby="v-cp-res" data-cp-campo>
				<p class="v-cp__res" id="v-cp-res" aria-live="polite" data-cp-res><?php echo esc_html( $cp['cp_ayuda'] ?? '' ); ?></p>
			</div>
			<div class="v-cobertura__dos">
				<div class="v-cobertura__si" data-revela><span class="v-cobertura__marca" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span><h3><?php echo esc_html( $c['cobertura']['si']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['si']['texto'] ); ?></p></div>
				<div class="v-cobertura__no" data-revela style="--i:1"><span class="v-cobertura__marca" aria-hidden="true"><?php echo dipt_icono( 'cerrar' ); // phpcs:ignore ?></span><h3><?php echo esc_html( $c['cobertura']['no']['titulo'] ); ?></h3><p><?php echo esc_html( $c['cobertura']['no']['texto'] ); ?></p></div>
			</div>
			<p><?php echo dipt_enlace( $c['cobertura']['zonas'], dipt_url( 'zonas' ) ); // phpcs:ignore ?></p>
		</div>
		<div class="v-cobertura__mapa" data-vivo>
			<?php get_template_part( 'partes/mapa-peninsula', null, array( 'variante' => 'cobertura', 'titulo' => $c['cobertura']['mapa'] ?? '', 'islas' => $c['cobertura']['islas'] ?? array(), 'no' => $c['cobertura']['mapa_no'] ?? '' ) ); ?>
		</div>
	</div>
</section>

<!-- V6 · Dudas: pestañas con panel noche en escritorio; en móvil, cada respuesta se abre debajo de su pregunta -->
<section class="v-faq tono-blanco seccion seccion--densa" aria-labelledby="v-faq-t">
	<div class="envoltura">
		<h2 class="t-h2" id="v-faq-t" data-revela><?php echo esc_html( $c['faq']['titulo'] ); ?></h2>
		<div class="v-pestanas" data-pestanas>
			<div class="v-pestanas__lista" role="tablist" aria-labelledby="v-faq-t">
				<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
					<button type="button" role="tab" id="v-tab-<?php echo (int) $i; ?>" aria-controls="v-panel-<?php echo (int) $i; ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>" style="--fila:<?php echo (int) $i * 2 + 1; ?>"><span class="dato">0<?php echo (int) $i + 1; ?></span><span class="v-pestanas__texto"><?php echo esc_html( $f[0] ); ?></span><span class="v-pestanas__signo" aria-hidden="true"></span></button>
				<?php endforeach; ?>
			</div>
			<?php foreach ( $c['faq']['lista'] as $i => $f ) : ?>
				<div class="v-pestanas__panel" role="tabpanel" id="v-panel-<?php echo (int) $i; ?>" aria-labelledby="v-tab-<?php echo (int) $i; ?>" style="--fila:<?php echo (int) $i * 2 + 2; ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
					<p class="v-pestanas__q" aria-hidden="true"><?php echo esc_html( $f[0] ); ?></p>
					<p><?php echo esc_html( $f[1] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_footer();
