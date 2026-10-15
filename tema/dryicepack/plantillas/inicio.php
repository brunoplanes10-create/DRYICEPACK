<?php
/**
 * Portada (castellano, catalán e inglés). Orden AIDA:
 * atención (hero) → interés (elegir, cómo llega) → deseo (usos, clientes, ficha, empresas, recogida) → objeciones (seguridad, preguntas) → acción.
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
	<div class="niebla niebla--hero" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
	<div class="envoltura i-hero__in">
		<h1 class="t-hero i-hero__titulo" id="i-hero-t"><?php dipt_html( str_replace( '|', '<br>', $c['hero']['titulo'] ) ); ?></h1>
		<p class="entrada i-hero__texto"><?php echo esc_html( str_replace( ' €', "\u{00A0}€", $c['hero']['texto'] ) ); // el importe y el € no se separan ?></p>
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

<!-- I2 · Cinta: línea de ficha técnica montada en el borde inferior del hero (sin la cifra −78,5, que ya tiene la ficha I6).
     Bucle de 60 s en escritorio; en móvil, carril con scroll-snap y sin la copia del bucle. -->
<?php
$cinta = array_values( array_filter( $c['cinta'], static fn( $e ) => false === strpos( $e, '78' ) ) );
$cinta_uno = '';
$cinta_dos = '';
foreach ( $cinta as $e ) {
	$cinta_uno .= '<span class="cinta__elem">' . esc_html( $e ) . '</span>';
	$cinta_dos .= '<span class="cinta__elem cinta__elem--copia">' . esc_html( $e ) . '</span>';
}
?>
<div class="i-cinta"><div class="cinta dato" aria-hidden="true"><div class="cinta__pista"><?php echo $cinta_uno . $cinta_dos; // phpcs:ignore ?></div></div></div>

<!-- I3 · Elegir formato y cantidad con el precio final -->
<section class="i-elegir tono-papel seccion" aria-labelledby="i-elegir-t" data-configurador data-kg="<?php echo esc_attr( $kg_ini ); ?>" data-formato="16mm">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $c['elegir']['titulo'], 't-h2 i-elegir__titulo', 'i-elegir-t', 'cortina' ); // phpcs:ignore ?>
		<div class="i-elegir__paneles" role="radiogroup" aria-label="<?php echo esc_attr( $c['elegir']['formato_aria'] ?? 'Formato' ); ?>">
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
			<div class="i-regla__kilos<?php echo count( $packs ) > 4 ? ' i-regla__kilos--cinco' : ''; ?>" role="radiogroup" aria-label="<?php echo esc_attr( $c['elegir']['cuanto'] ); ?>" style="--n:<?php echo (int) max( 1, count( $packs ) ); ?>;--pos:<?php echo (int) max( 0, array_search( (string) (float) $kg_ini, array_map( static fn( $k ) => (string) (float) $k, array_keys( $packs ) ), true ) ); ?>"><?php // columnas = packs a la venta (4 o 5); --pos: el pack marcado antes de que cargue el JS ?>
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
			<p class="i-regla__recogida" data-t-recogida="<?php echo esc_attr( $c['elegir']['recogida'] ); ?>" data-ticket-recogida><?php echo esc_html( sprintf( $c['elegir']['recogida'], dipt_euros( dipt_precios_recogida()[ (string) $kg_ini ] ?? $p_ini['con_iva'] ) ) ); // precio redondo de recogida con IVA (lo mismo que pinta inicio.js) ?></p>
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

<!-- I5 · Usos: «escenario fijo» (boceto A, elegido el 15/10/2026). Escritorio (≥ 901 px): índice tipográfico y un visor
     fijo (sticky) que sale de «Cómo llega» y cambia de foto con el uso que cruza el centro de la pantalla,
     con el ratón o el foco y con las miniaturas (botones). Móvil: cada uso con su foto a todo el ancho y el nombre
     montado sobre su borde inferior. Movimiento en inicio.js e inicio.css. -->
<?php
$usos_lista = array_values( $c['usos']['lista'] );
$usos_total = sprintf( '%02d', count( $usos_lista ) );
// Encuadre de cada foto (object-position): lo importante de la escena queda dentro en cualquier proporción
$usos_fp = array( 'uso-hosteleria' => '50% 50%', 'uso-transporte' => '62% 50%', 'uso-industria' => '42% 50%', 'uso-laboratorio' => '50% 45%', 'uso-eventos' => '8% 50%' );
?>
<section class="i-usos tono-blanco seccion" aria-labelledby="i-usos-t" data-usos>
	<div class="envoltura i-usos__rejilla">
		<div class="i-usos__indice">
			<div class="i-usos__cabeza">
				<?php echo dipt_titulo( 'h2', $c['usos']['titulo'], 't-h2', 'i-usos-t', 'revela' ); // phpcs:ignore ?>
				<?php echo dipt_enlace( $c['usos']['todos'], dipt_url( 'aplicaciones' ) ); // phpcs:ignore ?>
			</div>
			<ol class="i-usos__lista">
				<?php foreach ( $usos_lista as $i => $u ) : ?>
					<li class="i-usos__uso" data-vivo style="--i:<?php echo (int) $i; ?>">
						<a class="i-usos__enlace" href="<?php echo esc_url( dipt_url( $u[0] ) ); ?>">
							<span class="i-usos__foto" data-cortina data-pausa style="--fp:<?php echo esc_attr( $usos_fp[ $u[3] ] ?? '50% 50%' ); ?>"><span class="i-usos__foto-in"><?php echo dipt_foto( $u[3], '', array( 'sizes' => '100vw' ) ); // phpcs:ignore ?></span></span>
							<span class="i-usos__n dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?> <b>/ <?php echo esc_html( $usos_total ); ?></b></span>
							<span class="i-usos__nombre"><?php echo esc_html( $u[1] ); ?></span>
							<span class="i-usos__texto"><?php echo esc_html( $u[2] ); ?></span>
							<span class="i-usos__flecha" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<div class="i-usos__escenario">
			<div class="i-usos__visor" data-vivo data-pausa>
				<div class="i-usos__marco" data-cortina style="--cortina:var(--c-marino)">
					<div class="i-usos__fotos" aria-hidden="true">
						<?php foreach ( $usos_lista as $i => $u ) : ?>
							<div class="i-usos__capa<?php echo 0 === $i ? ' en-escena' : ''; ?>" style="--fp:<?php echo esc_attr( $usos_fp[ $u[3] ] ?? '50% 50%' ); ?>"><?php echo dipt_foto( $u[3], '', array( 'sizes' => '(min-width: 901px) 48vw, 100vw' ) ); // phpcs:ignore ?></div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="i-usos__tira" role="group" aria-label="<?php echo esc_attr( $c['usos']['tira'] ?? '' ); ?>">
					<?php foreach ( $usos_lista as $i => $u ) : ?>
						<button type="button" class="i-usos__mini" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" style="--i:<?php echo (int) $i; ?>"><?php echo dipt_foto( $u[3], '', array( 'sizes' => '80px' ) ); // phpcs:ignore ?><span class="visually-hidden"><?php echo esc_html( $u[1] ); ?></span></button>
					<?php endforeach; ?>
				</div>
				<p class="i-usos__etiqueta dato" aria-hidden="true">
					<span class="i-usos__rodillo i-usos__rodillo--n"><span class="i-usos__rodillo-pista"><?php foreach ( $usos_lista as $i => $u ) : ?><span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><?php endforeach; ?></span></span>
					<span class="i-usos__etiqueta-total">/ <?php echo esc_html( $usos_total ); ?></span>
					<span class="i-usos__etiqueta-sep"></span>
					<span class="i-usos__rodillo"><span class="i-usos__rodillo-pista"><?php foreach ( $usos_lista as $u ) : ?><span><?php echo esc_html( $u[1] ); ?></span><?php endforeach; ?></span></span>
				</p>
			</div>
		</div>
	</div>
</section>

<!-- I5b · Clientes: banda baja y densa; cuatro logos con su uso entre filetes que crecen; el vapor se aparta al entrar -->
<section class="i-clientes tono-oscuro seccion seccion--densa" aria-labelledby="i-clientes-t">
	<div class="envoltura i-clientes__in">
		<div class="i-clientes__cabeza">
			<?php echo dipt_titulo( 'h2', $c['clientes']['titulo'], 't-h3 i-clientes__titulo', 'i-clientes-t', 'revela' ); // phpcs:ignore ?>
			<p class="suave" data-revela style="--i:1"><?php echo esc_html( $c['clientes']['texto'] ); ?></p>
		</div>
		<ol class="i-clientes__lista">
			<?php foreach ( $c['clientes']['lista'] as $i => $cl ) : ?>
				<li class="i-cliente" data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="i-cliente__logo"><?php echo dipt_logo_cliente( $cl['id'], $cl['alt'] ); // phpcs:ignore ?></span>
					<span class="i-cliente__uso"><?php echo esc_html( $cl['uso'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<!-- I6 · Ficha técnica: la cifra −78,5 °C sangra por la izquierda; la caja en lámina le tapa la base y de ella salen cuatro cotas -->
<?php
$cifra = 'en' === dipt_idioma() ? '−78.5' : '−78,5';
// Punto de cada cota sobre la foto (en % del ancho y del alto) y lado hacia el que sale la línea
$cotas_puntos = array(
	array( 'x' => 52, 'y' => 38, 'lado' => 'izq' ), // vapor: sublima
	array( 'x' => 86, 'y' => 82, 'lado' => 'der' ), // vapor frío al pie de la caja
	array( 'x' => 62, 'y' => 46, 'lado' => 'der' ), // las piezas
	array( 'x' => 36, 'y' => 70, 'lado' => 'izq' ), // la pared de la caja
);
?>
<section class="i-ficha tono-escarcha seccion" aria-labelledby="i-ficha-t">
	<div class="envoltura">
		<div class="i-ficha__cabeza">
			<p class="i-ficha__cifra" aria-hidden="true"><span class="cifra-fija"><span class="cifra-fija__reserva"><?php echo esc_html( $cifra ); ?></span><span data-cuenta="-78.5" data-decimales="1" data-desde="20"><?php echo esc_html( $cifra ); ?></span></span><span class="i-ficha__grados">°C</span></p>
			<?php echo dipt_titulo( 'h2', $c['ficha']['titulo'], 't-h2 i-ficha__titulo', 'i-ficha-t', 'cortina' ); // phpcs:ignore ?>
		</div>
		<div class="i-ficha__plano" data-vivo>
			<figure class="i-ficha__foto foto--lamina foto--3x4" data-cortina>
				<?php echo dipt_foto( 'cenital-caja-nuggets', $c['ficha']['alt'], array( 'sizes' => '(max-width: 999px) 80vw, 26rem' ) ); // phpcs:ignore ?>
				<ol class="i-ficha__puntos" aria-hidden="true">
					<?php foreach ( $c['ficha']['cotas'] as $i => $cota ) : $pt = $cotas_puntos[ $i % 4 ]; ?>
						<li style="--x:<?php echo (int) $pt['x']; ?>;--y:<?php echo (int) $pt['y']; ?>;--i:<?php echo (int) $i; ?>"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></li>
					<?php endforeach; ?>
				</ol>
			</figure>
			<ol class="i-ficha__cotas">
				<?php foreach ( $c['ficha']['cotas'] as $i => $cota ) : $pt = $cotas_puntos[ $i % 4 ]; ?>
					<li class="i-cota i-cota--<?php echo esc_attr( $pt['lado'] ); ?>" style="--x:<?php echo (int) $pt['x']; ?>;--y:<?php echo (int) $pt['y']; ?>;--i:<?php echo (int) $i; ?>">
						<span class="i-cota__linea" aria-hidden="true"></span>
						<span class="i-cota__texto"><span class="i-cota__n dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><strong><?php echo esc_html( $cota[0] ); ?></strong><span><?php echo esc_html( $cota[1] ); ?></span></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<p class="i-ficha__enlace"><?php echo dipt_enlace( $c['ficha']['enlace'], dipt_url( 'que-es' ) ); // phpcs:ignore ?></p>
	</div>
</section>

<!-- I7 · Empresas: foto a sangre por la derecha y, encima, el albarán del pedido programado con sus cuatro semanas -->
<section class="i-empresas tono-papel seccion" aria-labelledby="i-empresas-t">
	<div class="envoltura i-empresas__in">
		<div class="i-empresas__texto">
			<?php echo dipt_titulo( 'h2', $c['empresas']['titulo'], 't-h2', 'i-empresas-t', 'cortina' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $c['empresas']['texto'] ); ?></p>
			<p class="i-empresas__accion" data-revela style="--i:1"><?php echo dipt_boton( $c['empresas']['enlace'], dipt_url( 'empresas' ), 'marino' ); // phpcs:ignore ?></p>
		</div>
		<div class="i-empresas__escena">
			<div class="i-empresas__foto foto--sangre foto--sangre-der" data-cortina>
				<?php echo dipt_foto( 'almacen-caja', '', array( 'sizes' => '(max-width: 900px) 92vw, 40vw' ) ); // phpcs:ignore ?>
			</div>
			<div class="hoja i-albaran" data-vivo>
				<p class="i-albaran__cabeza"><span class="i-albaran__titulo"><?php echo esc_html( $c['empresas']['tarjeta']['titulo'] ); ?></span><span class="dato"><?php echo esc_html( $c['empresas']['tarjeta']['pie'] ); ?></span></p>
				<ol class="i-albaran__filas">
					<?php foreach ( $c['empresas']['tarjeta']['lineas'] as $i => $l ) : ?>
						<li style="--i:<?php echo (int) $i; ?>"><span class="dato" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><span><?php echo esc_html( $l ); ?></span><span class="i-albaran__ok" aria-hidden="true"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span></li>
					<?php endforeach; ?>
				</ol>
				<ol class="i-albaran__semanas" aria-hidden="true">
					<?php for ( $s = 1; $s <= 4; $s++ ) : ?>
						<li style="--i:<?php echo (int) $s; ?>"><span class="dato"><?php echo esc_html( sprintf( $c['empresas']['semana'] ?? '%s', $s ) ); ?></span><span class="i-albaran__casilla"><?php echo dipt_icono( 'check' ); // phpcs:ignore ?></span></li>
					<?php endfor; ?>
				</ol>
			</div>
		</div>
	</div>
</section>

<!-- I8 · Recogida en Mataró: foto a sangre por la izquierda y la tarifa de mostrador sobre el fondo noche -->
<?php
$rec_precios = dipt_precios_recogida();
$rec_filas   = array();
foreach ( array_keys( dipt_packs() ) as $kg ) {
	$k = (string) ( 0 + $kg );
	if ( isset( $rec_precios[ $k ] ) ) $rec_filas[ $k ] = $rec_precios[ $k ];
}
?>
<section class="i-recogida tono-oscuro seccion" aria-labelledby="i-recogida-t" id="recogida">
	<div class="envoltura i-recogida__in">
		<div class="i-recogida__foto foto--sangre foto--sangre-izq" data-cortina>
			<?php echo dipt_foto( 'cajas-furgoneta', $c['recogida']['alt'], array( 'sizes' => '(max-width: 900px) 100vw, 44vw' ) ); // phpcs:ignore ?>
		</div>
		<div class="i-recogida__ficha">
			<?php echo dipt_titulo( 'h2', $c['recogida']['titulo'], 't-h2 i-recogida__titulo', 'i-recogida-t', 'revela' ); // phpcs:ignore ?>
			<p class="i-recogida__texto" data-revela><?php echo esc_html( $c['recogida']['texto'] ); ?></p>
			<div class="i-recogida__zonas">
				<?php if ( $rec_filas ) : ?>
					<div class="i-recogida__tarifa" data-vivo>
						<table class="i-tarifa">
							<caption class="dato"><?php echo esc_html( $c['recogida']['tarifa'] ?? '' ); ?></caption>
							<thead><tr><th scope="col"><?php echo esc_html( dipt_t( 'kg' ) ); ?></th><th scope="col"><?php echo esc_html( dipt_t( 'iva_incl' ) ); ?></th></tr></thead>
							<tbody>
								<?php $f = 0; foreach ( $rec_filas as $kg => $precio ) : ?>
									<tr style="--i:<?php echo (int) $f++; ?>"><th scope="row" class="dato"><?php echo esc_html( $kg . ' kg' ); ?></th><td><?php echo esc_html( dipt_euros( $precio, 0 ) ); ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<p class="i-recogida__efectivo"><?php echo esc_html( $c['recogida']['efectivo'] ?? '' ); ?></p>
					</div>
				<?php endif; ?>
				<div class="i-recogida__nave" data-revela style="--i:1">
					<address class="i-recogida__dir">
						<span class="dato"><?php echo esc_html( $c['recogida']['ficha'] ); ?></span>
						<?php echo esc_html( $con['direccion'] ); ?><br><?php echo esc_html( $con['cp'] . ' ' . $con['localidad'] . ' (' . $con['provincia'] . ')' ); ?>
					</address>
					<p class="i-recogida__cerca"><?php echo esc_html( implode( ' / ', $c['recogida']['cerca'] ) ); ?></p>
					<?php echo dipt_enlace( $c['recogida']['como'], $con['mapa'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- I9 · Seguridad: hoja de seguridad con cabecera marino que sobresale y seis reglas con su pictograma -->
<section class="i-seguridad tono-papel seccion" aria-labelledby="i-seguridad-t">
	<div class="envoltura">
		<div class="i-seguridad__hoja" data-revela>
			<div class="i-seguridad__cabeza tono-marino">
				<div>
					<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-h2', 'i-seguridad-t', 'revela' ); // phpcs:ignore ?>
					<p class="suave"><?php echo esc_html( $c['seguridad']['texto'] ); ?></p>
				</div>
				<p class="i-seguridad__mas"><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
			</div>
			<ul class="i-rombos">
				<?php foreach ( dipt_avisos_seguridad() as $i => $a ) : ?>
					<li data-activa style="--i:<?php echo (int) $i; ?>">
						<span class="i-rombo" aria-hidden="true"><?php echo dipt_icono( $a[0] ); // phpcs:ignore ?></span>
						<strong><?php echo esc_html( $a[1] ); ?></strong>
						<span><?php echo esc_html( $a[2] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>

<!-- I10 · Preguntas: título fijo con la lámina y el contacto; acordeón común (dipt_faq) -->
<section class="i-faq tono-blanco seccion seccion--densa" aria-labelledby="i-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'i-faq-t', 'lado' => array( 'foto', 'contacto' ), 'foto' => 'anadiendo-hielo' ) ); // phpcs:ignore ?>
	</div>
</section>

<!-- I11 · Cierre común: caja de 3 mm, niebla que sube y la fecha real de entrega -->
<?php
dipt_cierre( array(
	'titulo'       => $c['final']['titulo'],
	'id'           => 'i-final-t',
	'entrada'      => 'cortina',
	'foto'         => 'caja-3mm',
	'boton'        => $c['final']['boton'],
	'corte_textos' => array( $c['hero']['corte']['antes'], $c['hero']['corte']['quedan'], $c['hero']['corte']['despues'] ),
	'clase'        => 'i-final',
) );

get_footer();
