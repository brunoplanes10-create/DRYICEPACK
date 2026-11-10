<?php
/**
 * Provincia o ciudad de la península (10/11/2026): la misma plantilla para las provincias y las ciudades grandes, hecha con
 * las piezas de la página de zona (zona.css: z-hero, z-cifras, z-entrega, z-tabla, z-recogida, z-usos, z-faq, z-cerca) y el
 * cierre común. Sin secciones nuevas. Primero la empresa (B2B, de usted) y después la compra online (de tú).
 * L1 título con la próxima entrega real en el lugar · L2 suministro para empresas · L3 sectores en orden de prioridad ·
 * L4 entrega y precios con IVA · L5 seguridad · L6 cerca de aquí · L7 preguntas (FAQPage) · cierre común.
 * Tonos: papel · blanco · escarcha · papel · blanco · noche · escarcha · noche · pie marino.
 * La fecha de entrega es la del checkout para el código postal y la población del lugar (inc/lugares.php); el reloj del tema
 * la repinta en el navegador saltando los días sin reparto de MRW allí (data-sin-reparto), por si la página viene de la caché.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$l = function_exists( 'dipt_lugar_de_pagina' ) ? dipt_lugar_de_pagina( dipt_pagina_actual() ) : null;
if ( ! $l ) {
	get_template_part( 'page' );
	return;
}
$c       = dipt_contenido( 'provincias' );
$con     = dipt_contacto();
$idioma  = dipt_idioma();
$es_prov = 'provincia' === $l['tipo'];
$bcn     = '08' === $l['cp']; // en la provincia de Barcelona sí se envían más de 150 kg por la web
$cat     = $l['catalunya'] && null !== $l['km'] && null !== $l['min']; // recogida en Mataró con la distancia real
$t       = static fn( $s, $extra = array() ) => dipt_lugar_texto( $s, $l, $idioma, $extra );
// Titulares: las palabras cortas se unen a la siguiente (sin "en" al final de línea); cifras con su unidad sin partirse
$sin_cortes = static fn( $s ) => preg_replace( "/(?<=^|[\\s>|\x{00A0}])(el|la|els|les|en|a|al|del|de|y|i|sant|santa|san) (?=\\S)/iu", "\$1\u{00A0}", $s ) ?? $s;
$l_ns       = static fn( $s ) => preg_replace( '/(\d) (€|kg|km|min|mm)(?=[\s.,;:)]|$)/u', "\$1\u{00A0}\$2", (string) $s );

// Entrega en el lugar: primera fecha entre semana y días en que MRW no reparte allí
$ent      = dipt_lugar_entrega( $l );
$fecha    = $ent['fecha'] ? dipt_fecha_larga( $ent['fecha'] ) : '';
$sin_rep  = array_keys( $ent['sin_reparto'] );
$attr_rep = $sin_rep ? ' data-sin-reparto="' . esc_attr( implode( ',', $sin_rep ) ) . '"' : '';
$mrw_dias = array();
foreach ( array_slice( $ent['sin_reparto'], 0, 6, true ) as $ymd => $tipo ) {
	$mrw_dias[] = $t( str_replace( '{dia}', dipt_fecha_larga( $ymd ), $c['entrega']['mrw_dia'][ $tipo ] ?? '{dia}' ) );
}

// Precios de cada pack con IVA, el envío de la mensajería y el total (funciones de la tienda; {pk} es el pack pequeño)
$packs = array();
foreach ( array_keys( dipt_packs() ) as $kg ) {
	$pp = dipt_precio_pack( (float) $kg );
	if ( $pp['con_iva'] <= 0 ) continue;
	$packs[ (string) $kg ] = $pp;
}
$euros   = static fn( $n ) => dipt_lugar_euros( $n, $idioma );
$tarjeta = array_filter( array( (string) dipt_pk() => $packs[ (string) dipt_pk() ] ?? null, '10' => $packs['10'] ?? null ) );

// Municipios de la provincia (los que tienen página, enlazados) o códigos postales de la ciudad
$con_pagina = array();
foreach ( dipt_ciudades() as $k => $o ) {
	if ( $o['provincia_clave'] === $l['provincia_clave'] && dipt_lugar_pagina( $o ) ) $con_pagina[ dipt_lugar_normal( $o['nombre'] ) ] = dipt_url( 'ciudad-' . $k );
}
$municipios = array();
foreach ( $l['municipios'] as $m ) {
	$url          = $con_pagina[ dipt_lugar_normal( $m ) ] ?? '';
	$municipios[] = $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $m ) . '</a>' : esc_html( $m );
}

// Sectores en el orden de prioridad del negocio: transporte, hostelería, laboratorios, industria y fiestas (particulares)
$usos = array();
foreach ( (array) dipt_t( 'usos' ) as $u ) $usos[ $u[0] ] = $u;
$usos = array_filter( array_map( static fn( $k ) => $usos[ $k ] ?? null, array( 'transporte', 'hosteleria', 'laboratorios', 'industria', 'eventos' ) ) );

// Preguntas reales: dónde comprar (la búsqueda con más demanda), empresas, plazo, envío, sábado, más de 150 kg y recogida
$f      = $c['faq'];
$extra  = array( '{wa}' => $con['whatsapp'], '{tel}' => $con['telefono'], '{email}' => $con['email'] );
$envios = array();
foreach ( $packs as $kg => $pp ) $envios[] = str_replace( array( '{kg}', '{envio}' ), array( $kg, $euros( $pp['envio'] ) ), $f['envio_kg'] );
$faq_mrw = $sin_rep ? $t( $f['mrw'], array( '{lista}' => dipt_lugar_lista( array_map( static fn( $d ) => dipt_fecha_larga( $d ), array_slice( $sin_rep, 0, 6 ) ), $idioma ) ) ) : '';
$preguntas = array( $cat ? $f['donde_cat'] : $f['donde'], $f['empresa'], $f['tarda'], $f['envio'], $f['sabado'], $bcn ? $f['medida_bcn'] : $f['medida'] );
if ( $cat ) $preguntas[] = $f['recogida'];
$faq = array();
foreach ( $preguntas as $p ) {
	$faq[] = array( $t( $p[0] ), $l_ns( $t( $p[1], $extra + array( '{mrw}' => $faq_mrw, '{lista}' => dipt_lugar_lista( $envios, $idioma, '; ' ) ) ) ) );
}
dipt_registrar_faq( $faq );

// Migas: Inicio / Zonas / (provincia de la ciudad) / lugar
$migas = array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'zonas' ) ) );
if ( ! $es_prov ) {
	$prov = dipt_provincias()[ $l['provincia_clave'] ] ?? null;
	$kp   = $prov ? dipt_lugar_pagina( $prov ) : null;
	if ( $kp ) $migas[] = array( $prov['provincia_corta'], dipt_url( $kp ) );
}
$migas[] = array( $es_prov ? $l['provincia_corta'] : $l['nombre'], dipt_url( dipt_pagina_actual() ) );

$cerca = dipt_lugar_cercanos( $l );
$fotos = array( 'hosteleria' => 'uso-hosteleria', 'transporte' => 'uso-transporte', 'industria' => 'uso-industria', 'laboratorios' => 'uso-laboratorio', 'eventos' => 'uso-eventos' );
$url_empresa = dipt_url( 'empresas', null, 'alta' );
get_header();
?>

<!-- L1 · Título: el proveedor en el lugar, la compra puntual y la próxima entrega real -->
<section class="z-hero z-hero--lugar tono-papel" aria-labelledby="l-hero-t">
	<div class="envoltura z-hero__in">
		<div>
			<?php echo dipt_migas( $migas ); // phpcs:ignore ?>
			<h1 class="t-hero" id="l-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span><span class="tramo">', $sin_cortes( $t( $c['hero']['titulo'] ) ) ) . '</span>' ); ?></h1>
			<p class="entrada"><?php echo esc_html( $l_ns( $t( $c['hero']['texto'][ $l['tipo'] ] ) ) ); ?></p>
			<div class="z-hero__acciones">
				<?php echo dipt_boton( $c['hero']['empresa'], $url_empresa, 'marino' ); // phpcs:ignore ?>
				<?php echo dipt_boton( $c['hero']['compra'], dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			</div>
		</div>
		<dl class="z-cifras">
			<div data-revela style="--i:0"><dt class="dato"><?php echo esc_html( $t( $c['hero']['datos']['entrega'] ) ); ?></dt><dd data-corte data-t-antes="%s" data-t-despues="%s"<?php echo $attr_rep; // phpcs:ignore -- escapado arriba ?>><span data-corte-texto><?php echo esc_html( $fecha ); ?></span></dd></div>
			<div data-revela style="--i:1"><dt class="dato"><?php echo esc_html( $c['hero']['datos']['pack'] ); ?></dt><dd><?php echo esc_html( $l_ns( $t( '{total:10}' ) ) ); ?></dd></div>
			<?php if ( $es_prov ) : ?>
				<div data-revela style="--i:2"><dt class="dato"><?php echo esc_html( $c['hero']['datos']['cps'] ); ?></dt><dd><?php echo esc_html( $l['cp'] . 'xxx' ); ?></dd></div>
			<?php else : ?>
				<div data-revela style="--i:2"><dt class="dato"><?php echo esc_html( $c['hero']['datos']['provincia'] ); ?></dt><dd><?php echo esc_html( $l['provincia_corta'] ); ?></dd></div>
			<?php endif; ?>
		</dl>
	</div>
</section>

<!-- L2 · Suministro para empresas (usted): contrato, programación, factura mensual y presupuesto -->
<section class="z-entrega tono-blanco seccion" aria-labelledby="l-empresa-t">
	<div class="envoltura z-entrega__in">
		<div class="z-entrega__cab">
			<?php echo dipt_titulo( 'h2', $sin_cortes( $t( $c['empresa']['titulo'] ) ), 't-h2', 'l-empresa-t' ); // phpcs:ignore ?>
			<p class="suave" data-revela><?php echo esc_html( $t( $c['empresa']['texto'] ) ); ?></p>
			<p data-revela style="--i:1"><?php echo dipt_boton( $c['empresa']['boton'], $url_empresa, 'marino' ); // phpcs:ignore ?></p>
		</div>
		<dl class="z-tabla">
			<?php foreach ( array_merge( $c['empresa']['filas'], array( $bcn ? $c['empresa']['barcelona'] : $c['empresa']['medida'] ) ) as $i => $fila ) : ?>
				<div data-revela style="--i:<?php echo (int) $i; ?>"><dt><?php echo esc_html( $l_ns( $t( $fila[0] ) ) ); ?></dt><dd><?php echo esc_html( $l_ns( $t( $fila[1] ) ) ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- L3 · Sectores: tira de fotos en el orden de prioridad (carril con scroll-snap en móvil) -->
<section class="z-usos tono-escarcha seccion seccion--densa" aria-labelledby="l-usos-t">
	<div class="envoltura">
		<?php echo dipt_titulo( 'h2', $sin_cortes( $t( $c['usos']['titulo'] ) ), 't-h2', 'l-usos-t', 'revela' ); // phpcs:ignore ?>
		<?php $intro = dipt_lugar_intro( $l ); if ( $intro ) : ?>
			<p class="entrada z-usos__intro" data-revela><?php echo esc_html( $l_ns( $intro ) ); ?></p>
		<?php endif; ?>
		<ul class="z-usos__tira" data-revela>
			<?php foreach ( $usos as $u ) : ?>
				<li data-activa>
					<a href="<?php echo esc_url( dipt_url( $u[0] ) ); ?>">
						<span class="z-usos__foto foto--sangre"><?php echo dipt_foto( $fotos[ $u[0] ] ?? 'cajas-oscuro', '', array( 'sizes' => '(max-width: 800px) 70vw, 24vw' ) ); // phpcs:ignore ?></span>
						<span class="z-usos__nombre dato"><span><?php echo esc_html( $u[1] ); ?></span><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<!-- L4 · Entrega y precios de la compra online (tú): fecha real, sábado, días sin reparto, packs con IVA y envío -->
<section class="z-entrega tono-papel seccion" aria-labelledby="l-entrega-t">
	<div class="envoltura z-entrega__in">
		<div class="z-entrega__cab">
			<?php echo dipt_titulo( 'h2', $sin_cortes( $t( $c['entrega']['titulo'] ) ), 't-h2', 'l-entrega-t' ); // phpcs:ignore ?>
			<?php if ( $tarjeta ) : ?>
				<div class="z-recogida" data-revela>
					<p class="dato z-recogida__et"><?php echo esc_html( $t( $c['entrega']['tarjeta'] ) ); ?></p>
					<dl>
						<?php foreach ( $tarjeta as $kg => $pp ) : ?><div><dt class="dato"><?php echo esc_html( $kg . "\u{00A0}kg" ); ?></dt><dd><?php echo esc_html( str_replace( ' ', "\u{00A0}", $euros( $pp['total'] ) ) ); ?></dd></div><?php endforeach; ?>
					</dl>
				</div>
			<?php endif; ?>
		</div>
		<dl class="z-tabla">
			<div data-revela><dt><?php echo esc_html( $c['entrega']['pedido'][0] ); ?></dt><dd><span data-corte data-t-antes="<?php echo esc_attr( dipt_t( 'corte_antes' ) ); ?>" data-t-despues="<?php echo esc_attr( dipt_t( 'corte_despues' ) ); ?>"<?php echo $attr_rep; // phpcs:ignore -- escapado arriba ?>><span data-corte-texto><?php echo esc_html( sprintf( dipt_t( 'corte_despues' ), $fecha ) ); ?></span></span>. <?php echo esc_html( $c['entrega']['pedido'][1] ); ?></dd></div>
			<div data-revela style="--i:1"><dt><?php echo esc_html( $c['entrega']['sabado'][0] ); ?></dt><dd><?php echo esc_html( $l_ns( $t( $c['entrega']['sabado'][1] ) ) ); ?></dd></div>
			<?php if ( $mrw_dias ) : ?>
				<div data-revela style="--i:2"><dt><?php echo esc_html( $c['entrega']['mrw'][0] ); ?></dt><dd><?php echo esc_html( $t( $c['entrega']['mrw'][1], array( '{lista}' => dipt_lugar_lista( $mrw_dias, $idioma ) ) ) ); ?></dd></div>
			<?php endif; ?>
			<?php foreach ( $packs as $kg => $pp ) : ?>
				<div data-revela style="--i:3"><dt class="num"><?php echo esc_html( $kg . "\u{00A0}kg" ); ?></dt><dd class="num"><?php echo esc_html( $l_ns( str_replace( array( '{precio}', '{kilo}', '{envio}', '{total}' ), array( $euros( $pp['con_iva'] ), $euros( $pp['kilo'] ), $euros( $pp['envio'] ), $euros( $pp['total'] ) ), $c['entrega']['pack'] ) ) ); ?></dd></div>
			<?php endforeach; ?>
			<div data-revela style="--i:4"><dt><?php echo esc_html( $c['entrega']['tarifa'][0] ); ?></dt><dd><?php echo esc_html( $l_ns( $c['entrega']['tarifa'][1] ) ); ?> <?php echo dipt_enlace( dipt_contenido( 'zonas' )['indice']['envios'] ?? '', dipt_url( 'envios' ) ); // phpcs:ignore ?></dd></div>
			<?php if ( $cat ) : ?>
				<div data-revela style="--i:5"><dt><?php echo esc_html( $c['entrega']['recogida'][0] ); ?></dt><dd><?php echo esc_html( $l_ns( $t( $c['entrega']['recogida'][1] ) ) ); ?></dd></div>
			<?php endif; ?>
			<?php if ( $es_prov && $municipios ) : ?>
				<div data-revela style="--i:6"><dt><?php echo esc_html( $c['entrega']['municipios'][0] ); ?></dt><dd><?php echo dipt_kses( $t( $c['entrega']['municipios'][1], array( '{lista}' => dipt_lugar_lista( $municipios, $idioma ) ) ) ); // phpcs:ignore ?></dd></div>
			<?php elseif ( $es_prov ) : ?>
				<div data-revela style="--i:6"><dt><?php echo esc_html( $c['entrega']['resto'][0] ); ?></dt><dd><?php echo esc_html( $t( $c['entrega']['resto'][1] ) ); ?></dd></div>
			<?php else : ?>
				<div data-revela style="--i:6"><dt><?php echo esc_html( $t( $c['entrega']['cps'][0] ) ); ?></dt><dd class="num"><?php echo esc_html( $t( $c['entrega']['cps'][1], array( '{lista}' => dipt_lugar_lista( $l['cps'], $idioma ) ) ) ); ?></dd></div>
				<div data-revela style="--i:7"><dt><?php echo esc_html( $c['entrega']['resto'][0] ); ?></dt><dd><?php echo esc_html( $t( $c['entrega']['resto'][1] ) ); ?></dd></div>
			<?php endif; ?>
		</dl>
	</div>
</section>

<!-- L5 · Seguridad: los seis avisos comunes y la página completa -->
<section class="z-entrega tono-blanco seccion seccion--densa" aria-labelledby="l-seguridad-t">
	<div class="envoltura z-entrega__in">
		<div class="z-entrega__cab">
			<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-h2', 'l-seguridad-t', 'revela' ); // phpcs:ignore ?>
			<p data-revela><?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
		</div>
		<dl class="z-tabla">
			<?php foreach ( dipt_avisos_seguridad() as $i => $a ) : ?>
				<div data-revela style="--i:<?php echo (int) $i; ?>"><dt><?php echo esc_html( $a[1] ); ?></dt><dd><?php echo esc_html( $a[2] ); ?></dd></div>
			<?php endforeach; ?>
		</dl>
	</div>
</section>

<!-- L6 · Cerca de aquí (noche): provincia, ciudades y provincias vecinas de la comunidad, y el índice de zonas -->
<nav class="z-cerca tono-oscuro" aria-labelledby="l-cerca-t">
	<div class="envoltura">
		<h2 class="dato" id="l-cerca-t"><?php echo esc_html( $t( $c['cerca'] ) ); ?></h2>
		<ul>
			<?php foreach ( $cerca as $e ) : ?><li><a href="<?php echo esc_url( $e[1] ); ?>"><?php echo esc_html( $e[0] ); ?></a></li><?php endforeach; ?>
			<li><a href="<?php echo esc_url( dipt_url( 'zonas' ) ); ?>"><?php echo esc_html( $c['todas'] ); ?></a></li>
			<?php if ( count( $cerca ) < 2 ) : // sin ciudades ni provincias vecinas con página: también los plazos de envío ?>
				<li><a href="<?php echo esc_url( dipt_url( 'envios' ) ); ?>"><?php echo esc_html( dipt_contenido( 'zonas' )['indice']['envios'] ?? '' ); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>
</nav>

<!-- L7 · Preguntas reales del lugar -->
<section class="z-faq tono-escarcha seccion seccion--densa" aria-labelledby="l-faq-t">
	<div class="envoltura z-faq__in">
		<h2 class="t-h3" id="l-faq-t"><?php echo esc_html( dipt_t( 'faq' ) ); ?></h2>
		<div>
			<?php foreach ( $faq as $i => $p ) : ?>
				<details class="z-faq__item"<?php echo 0 === $i ? ' open' : ''; ?>><summary><span><?php echo esc_html( $p[0] ); ?></span><span class="z-faq__signo" aria-hidden="true"></span></summary><p><?php echo esc_html( $p[1] ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
dipt_cierre( array(
	'titulo'      => $sin_cortes( $t( $c['cierre']['titulo'] ) ),
	'foto'        => 'cajas-furgoneta',
	'recorte'     => '35% 50%',
	'secundario'  => array( $c['cierre']['secundario'], dipt_url( 'empresas' ) ),
	'entrega'     => $ent['fecha'],
	'sin_reparto' => $sin_rep,
) );
get_footer();
