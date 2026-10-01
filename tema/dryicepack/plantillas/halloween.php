<?php
/**
 * Página Halloween 2026 · /hielo-seco-halloween/
 *
 * Una página más de la web: usa la cabecera, el menú, el pie y la tipografía de dryicepack.es
 * (get_header / get_footer). Solo cambian los colores y la ambientación.
 * WordPress la aplica sola a la página con slug `hielo-seco-halloween`.
 * Funciona en el tema hijo actual y, sin cambios, en el tema nuevo `dryicepack`.
 *
 * Archivos: esta plantilla + assets/halloween/{css,js,img,fonts}/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$hw_dir = get_template_directory() . '/assets/halloween';
$hw_uri = get_template_directory_uri() . '/assets/halloween';
$GLOBALS['dipt_hero_oscuro'] = true;
$hw_img = $hw_uri . '/img';

$hw_ver = static function ( $rel ) use ( $hw_dir ) {
	$path = $hw_dir . '/' . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : '1';
};

/* ---------------------------------------------------------
   Datos (precios y enlaces reales de la tienda)
--------------------------------------------------------- */
$hw_producto = dipt_url( 'producto' );
// La ficha de compra abre con los kilos y el formato ya elegidos (?kg=10&formato=3mm)
$hw_pedir    = static function ( $kg, $formato = '3mm' ) use ( $hw_producto ) {
	return add_query_arg( array( 'kg' => $kg, 'formato' => $formato ), $hw_producto );
};
$hw_tel      = 'tel:+34936737641';
$hw_whatsapp = 'https://wa.me/34686980471?text=' . rawurlencode( 'Hola, necesito hielo seco para Halloween. Es para:' );

// Plazos Halloween 2026 (hora de Madrid; el 29/10 ya es horario de invierno, +01:00)
$hw_limite_viernes = '2026-10-29T12:00:00+01:00';
$hw_limite_sabado  = '2026-10-30T12:00:00+01:00';
$hw_fin_campana    = '2026-11-01T00:00:00+01:00';

$hw_faq = array(
	array(
		'q' => '¿Cuánto dura el hielo seco?',
		'a' => 'En su caja EPS con la tapa puesta, orientativamente entre 24 y 48 horas, según la cantidad, el formato y la temperatura. Cuanto más hielo seco hay en la caja, más aguanta, y el de 16 mm aguanta más que el de 3 mm. Por eso te recomendamos recibirlo la víspera de la fiesta.',
	),
	array(
		'q' => '¿Cuánto hielo seco necesito para Halloween?',
		'a' => 'Orientativamente, 3 kg para una fiesta en casa, 10 kg para una fiesta grande y 15–20 kg para un bar o un evento. Si vas a tener varios puntos de niebla a la vez, sube un escalón.',
	),
	array(
		'q' => '¿Se puede echar hielo seco en una bebida?',
		'a' => 'No. El hielo seco no se ingiere y nunca debe ir dentro de algo que se vaya a beber: vaso, copa, jarra o ponchera. Para cócteles con humo usa doble recipiente: el hielo seco con agua caliente en el recipiente de fuera y la bebida en el de dentro.',
	),
	array(
		'q' => '¿Dónde lo guardo hasta la fiesta?',
		'a' => 'En su caja EPS con la tapa puesta, sin precintar, en un sitio ventilado y fuera del alcance de niños y mascotas. No lo metas en el congelador ni en un recipiente hermético.',
	),
	array(
		'q' => '¿Qué formato hace más niebla?',
		'a' => 'El de 3 mm: al tener más superficie, suelta una niebla más densa y más rápido. El de 16 mm sublima más despacio, dura más y da una niebla más constante.',
	),
	array(
		'q' => '¿Hasta cuándo puedo pedir para Halloween?',
		'a' => 'Para recibirlo el viernes 30 de octubre, pide antes de las 12:00 del jueves 29. Para el sábado 31, pide antes de las 12:00 del viernes 30: la entrega en sábado depende de la zona y tiene un suplemento de 11,74 € (IVA incluido).',
	),
	array(
		'q' => '¿Puedo recogerlo en vuestro almacén?',
		'a' => 'Sí, en Mataró (Camí Ca La Madrona 19 D), de lunes a sábado; el sábado 31 también, con suplemento de 11,74 €. Elige la recogida al finalizar la compra y te confirmamos la hora. Está pensada para clientes del Maresme, el Vallès y el área de Barcelona.',
	),
);

/* ---------------------------------------------------------
   Recursos de esta página (la cabecera y el pie cargan los suyos)
--------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', static function () use ( $hw_dir, $hw_uri, $hw_ver ) {
	// Versión minificada si existe (se genera con herramientas/compilar.mjs)
	$css = file_exists( $hw_dir . '/css/halloween.min.css' ) ? 'css/halloween.min.css' : 'css/halloween.css';
	$js  = file_exists( $hw_dir . '/js/halloween.min.js' ) ? 'js/halloween.min.js' : 'js/halloween.js';
	wp_enqueue_style( 'dip-halloween', $hw_uri . '/' . $css, array(), $hw_ver( $css ) );
	wp_enqueue_script( 'dip-halloween', $hw_uri . '/' . $js, array(), $hw_ver( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
}, 20 );

// El script es pequeño y pinta la cuenta atrás: que WP Rocket no lo retrase.
add_filter( 'script_loader_tag', static function ( $tag, $handle ) {
	if ( 'dip-halloween' === $handle && false === strpos( $tag, 'nowprocket' ) ) {
		$tag = str_replace( '<script ', '<script nowprocket ', $tag );
	}
	return $tag;
}, 20, 2 );

add_filter( 'body_class', static function ( $clases ) {
	$clases[] = 'dip-page-halloween';
	return $clases;
} );

// Fase de la campaña (viernes / sabado / cerrado / fin) en <html data-hw-fase>, antes del primer pintado.
// El CSS muestra la versión de cada texto que toca; halloween.js la actualiza si cambia con la página abierta.
add_action( 'wp_head', static function () use ( $hw_limite_viernes, $hw_limite_sabado, $hw_fin_campana ) {
	printf(
		'<script nowprocket data-cfasync="false">(function(){var t=Date.now(),f=t<%1$d?"viernes":t<%2$d?"sabado":t<%3$d?"cerrado":"fin";document.documentElement.setAttribute("data-hw-fase",f);})();</script>' . "\n",
		(int) ( strtotime( $hw_limite_viernes ) * 1000 ),
		(int) ( strtotime( $hw_limite_sabado ) * 1000 ),
		(int) ( strtotime( $hw_fin_campana ) * 1000 )
	);
}, 2 );

// Precarga: foto del hero (móvil y escritorio). La tipografía la precarga el tema.
add_action( 'wp_head', static function () use ( $hw_img ) {
	printf(
		'<link rel="preload" as="image" type="image/webp" media="(max-width: 699px)" imagesrcset="%1$s/hero-calabazas-movil-480.webp 480w, %1$s/hero-calabazas-movil-780.webp 780w" imagesizes="100vw" fetchpriority="high">' . "\n" .
		'<link rel="preload" as="image" type="image/webp" media="(min-width: 700px)" imagesrcset="%1$s/hero-calabazas-1024.webp 1024w, %1$s/hero-calabazas-1600.webp 1600w" imagesizes="(min-width: 1100px) 64vw, 100vw" fetchpriority="high">' . "\n",
		esc_url( $hw_img )
	);
}, 1 );

// Schema FAQPage con las mismas preguntas que se ven en la página.
add_action( 'wp_head', static function () use ( $hw_faq ) {
	$items = array();
	foreach ( $hw_faq as $f ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => $f['q'],
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ),
		);
	}
	$schema = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items );
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 30 );

/* ---------------------------------------------------------
   Ilustraciones SVG propias
   Cada ilustración = dibujo base estático + capas animadas.
   Cada capa es un <svg> propio que se anima entero (transform/opacity):
   así el navegador lo mueve en la tarjeta gráfica sin recalcular el diseño.
--------------------------------------------------------- */
$hw_ilus = static function ( $nombre ) {
	$vb   = 'viewBox="0 0 120 96" aria-hidden="true" focusable="false"';
	$capa = static function ( $clase, $contenido ) use ( $vb ) {
		return '<svg class="hw-ilus__capa ' . $clase . '" ' . $vb . '>' . $contenido . '</svg>';
	};
	$dibujos = array(
		// Caldero: burbujas que suben y niebla que rebosa por el borde
		'caldero'  => array(
			'<path class="hw-t" d="M14 45h92"/><path class="hw-t" d="M20 45c0 24 16 36 40 36s40-12 40-36"/><path class="hw-t" d="M32 79l-7 10M88 79l7 10"/>',
			$capa( 'hw-i-niebla', '<path class="hw-n" d="M16 40c5-7 13-7 17-2 4-6 13-7 18-2 5-5 15-4 18 2 5-3 13-2 17 3 5-2 11 0 13 5"/>' )
			. $capa( 'hw-i-derrame', '<path class="hw-n" d="M20 45c-5 5-3 11-9 15"/>' )
			. $capa( 'hw-i-derrame hw-i-derrame--2', '<path class="hw-n" d="M100 45c5 5 3 11 9 15"/>' )
			. $capa( 'hw-i-burbuja', '<circle class="hw-b" cx="48" cy="41" r="3"/>' )
			. $capa( 'hw-i-burbuja hw-i-burbuja--2', '<circle class="hw-b" cx="64" cy="40" r="2.2"/>' )
			. $capa( 'hw-i-burbuja hw-i-burbuja--3', '<circle class="hw-b" cx="74" cy="42" r="2.6"/>' ),
		),
		// Niebla por el suelo: capas de niebla que se arrastran y una calabaza pequeña
		'suelo'    => array(
			'<path class="hw-t" d="M6 86h108"/><path class="hw-t" d="M84 86c-9 0-13-5-13-11s5-10 13-10 13 4 13 10-4 11-13 11z"/><path class="hw-t" d="M84 65c0-3 1-5 4-6"/>',
			$capa( 'hw-i-ojos', '<path class="hw-o" d="M79 73l2 2-3 1zM89 73l-2 2 3 1z"/>' )
			. $capa( 'hw-i-onda hw-i-onda--3', '<path class="hw-n" d="M-28 52 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0"/>' )
			. $capa( 'hw-i-onda hw-i-onda--2', '<path class="hw-n" d="M-28 64 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0"/>' )
			. $capa( 'hw-i-onda hw-i-onda--1', '<path class="hw-n" d="M-28 76 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0 c10-7 18 7 28 0"/>' ),
		),
		// Ponchera con doble recipiente: vapor que sube sin tocar la bebida
		'ponchera' => array(
			'<path class="hw-t" d="M8 58h104c0 18-22 28-52 28S8 76 8 58z"/><path class="hw-t hw-c" d="M34 36h52c0 16-11 26-26 26S34 52 34 36z"/><path class="hw-l" d="M38 44h44"/>',
			$capa( 'hw-i-niebla', '<path class="hw-n" d="M12 56c6-6 14-4 18 0 4-5 12-6 17-1M73 55c5-5 13-4 17 1 4-4 12-5 18 0"/>' )
			. $capa( 'hw-i-vapor', '<path class="hw-n" d="M50 30c-4-4 4-7 0-12"/>' )
			. $capa( 'hw-i-vapor hw-i-vapor--2', '<path class="hw-n" d="M62 29c-4-4 4-7 0-12"/>' )
			. $capa( 'hw-i-vapor hw-i-vapor--3', '<path class="hw-n" d="M74 30c-4-4 4-7 0-12"/>' ),
		),
		// Photocall: flash que destella y niebla a los pies
		'foto'     => array(
			'<path class="hw-t" d="M22 30h76a4 4 0 0 1 4 4v38a4 4 0 0 1-4 4H22a4 4 0 0 1-4-4V34a4 4 0 0 1 4-4z"/><path class="hw-t" d="M44 30l5-8h22l5 8"/><circle class="hw-t" cx="60" cy="52" r="12"/><circle class="hw-t" cx="60" cy="52" r="5"/>',
			$capa( 'hw-i-flash', '<path class="hw-f" d="M92 16l2 6 6 2-6 2-2 6-2-6-6-2 6-2z"/>' )
			. $capa( 'hw-i-onda hw-i-onda--1', '<path class="hw-n" d="M-28 88 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0 c10-6 18 6 28 0"/>' ),
		),
	);
	if ( ! isset( $dibujos[ $nombre ] ) ) return '';
	list( $base, $capas ) = $dibujos[ $nombre ];
	return '<div class="hw-ilus hw-ilus--' . esc_attr( $nombre ) . '"><svg class="hw-ilus__base" ' . $vb . ' width="120" height="96">' . $base . '</svg>' . $capas . '</div>';
};

// Murciélago en silueta (el aleteo se anima sobre el elemento entero, no sobre partes del SVG)
$hw_murcielago = '<i class="hw-aleteo"><svg viewBox="0 0 64 30" aria-hidden="true" focusable="false"><path d="M31 13C25 5 15 3 3 7c5 2 7 5 7 8 3-2 6-1 8 2 2-3 6-3 9 0z"/><path d="M33 13c6-8 16-10 28-6-5 2-7 5-7 8-3-2-6-1-8 2-2-3-6-3-9 0z"/><path d="M29 11l1-3 1.5 2h1l1.5-2 1 3c.6 4-.4 8-3 10-2.6-2-3.6-6-3-10z"/></svg></i>';

get_header();
?>
<main id="hw" class="hw">

	<!-- 1 · HERO -->
	<section class="hw-hero" aria-labelledby="hw-h1">
		<picture class="hw-hero__img">
			<source media="(max-width: 699px)" type="image/webp"
				srcset="<?php echo esc_url( $hw_img ); ?>/hero-calabazas-movil-480.webp 480w, <?php echo esc_url( $hw_img ); ?>/hero-calabazas-movil-780.webp 780w"
				sizes="100vw" width="780" height="1040">
			<img src="<?php echo esc_url( $hw_img ); ?>/hero-calabazas-1024.webp"
				srcset="<?php echo esc_url( $hw_img ); ?>/hero-calabazas-1024.webp 1024w, <?php echo esc_url( $hw_img ); ?>/hero-calabazas-1600.webp 1600w"
				sizes="(min-width: 1100px) 64vw, 100vw" width="1600" height="1200"
				alt="Tres calabazas talladas e iluminadas sobre una mesa, con niebla de hielo seco a ras de la superficie"
				fetchpriority="high" data-no-lazy="1" class="skip-lazy">
		</picture>

		<!-- Ambientación: luz de vela, niebla, brasas y murciélagos (decorativo) -->
		<div class="hw-ambiente" aria-hidden="true">
			<span class="hw-vela"></span><span class="hw-vela hw-vela--2"></span>
			<div class="hw-niebla"><span class="hw-niebla__capa"></span><span class="hw-niebla__capa hw-niebla__capa--2"></span><span class="hw-niebla__capa hw-niebla__capa--3"></span></div>
			<div class="hw-brasas"><?php for ( $i = 1; $i <= 12; $i++ ) : ?><span style="--i:<?php echo (int) $i; ?>"></span><?php endfor; ?></div>
			<div class="hw-murcielagos">
				<span class="hw-murcielago hw-murcielago--1"><span><?php echo $hw_murcielago; // phpcs:ignore ?></span></span>
				<span class="hw-murcielago hw-murcielago--2"><span><?php echo $hw_murcielago; // phpcs:ignore ?></span></span>
				<span class="hw-murcielago hw-murcielago--3"><span><?php echo $hw_murcielago; // phpcs:ignore ?></span></span>
			</div>
		</div>

		<div class="hw-wrap hw-hero__cuerpo">
			<p class="hw-antetitulo">Halloween 2026 · Efecto niebla</p>
			<h1 id="hw-h1">Hielo seco para Halloween: niebla de verdad en tu fiesta</h1>
			<span class="hw-subrayado" aria-hidden="true"></span>
			<p class="hw-entrada">Pellets de hielo seco de 3 mm que hacen niebla densa en cuanto tocan agua caliente. Pide antes de las 12:00 y lo recibes el siguiente día laborable por la mañana, en caja EPS y listo para usar.</p>
			<div class="hw-botones">
				<a class="hw-btn" href="<?php echo esc_url( $hw_pedir( 3 ) ); ?>">Pedir hielo seco</a>
				<a class="hw-btn hw-btn--linea" href="#cantidad">Elegir cantidad</a>
			</div>
			<ul class="hw-chips">
				<li>Entrega en 24 h laborables</li>
				<li>Pellets 3 mm / nuggets 16 mm</li>
				<li>Caja EPS incluida</li>
			</ul>
			<p class="hw-ticket hw-solo-abierto"><span class="hw-ticket__et"><span class="hw-f-v">Último pedido para el viernes 30</span><span class="hw-f-s">Último pedido para el sábado 31 (según zona)</span></span><span class="hw-ticket__fecha"><span class="hw-f-v">Jue 29/10 · 12:00</span><span class="hw-f-s">Vie 30/10 · 12:00</span></span></p>
		</div>
	</section>

	<!-- 2 · IDEAS -->
	<section class="hw-seccion hw-seccion--bruma" id="ideas" aria-labelledby="hw-ideas-t">
		<div class="hw-bruma" aria-hidden="true"><span></span></div>
		<div class="hw-wrap">
			<header class="hw-cabecera" data-hw-revela>
				<p class="hw-antetitulo">Ideas</p>
				<h2 id="hw-ideas-t">4 formas de usar hielo seco en Halloween</h2>
				<p>El hielo seco pasa de sólido a gas sin derretirse. Con agua caliente suelta una niebla blanca y pesada que cae y se arrastra por el suelo.</p>
			</header>
			<ol class="hw-ideas" data-hw-revela="grupo">
				<li>
					<div class="hw-ideas__ilus"><?php echo $hw_ilus( 'caldero' ); // phpcs:ignore ?></div>
					<h3>Caldero de bruja</h3>
					<p>Un caldero con agua caliente y unos cuantos pellets. La niebla rebosa y cae por los lados. Es decoración: de ese caldero no se sirve nada.</p>
				</li>
				<li>
					<div class="hw-ideas__ilus"><?php echo $hw_ilus( 'suelo' ); // phpcs:ignore ?></div>
					<h3>Niebla por el suelo</h3>
					<p>La niebla pesa más que el aire y se queda a ras de suelo. Ponla en la entrada o en la pista, nunca en espacios pequeños o cerrados. Ventila y mantén lejos a niños y mascotas: el CO₂ se acumula abajo.</p>
				</li>
				<li>
					<div class="hw-ideas__ilus"><?php echo $hw_ilus( 'ponchera' ); // phpcs:ignore ?></div>
					<h3>Ponchera con doble recipiente</h3>
					<p>Un bol grande con agua caliente y hielo seco y, dentro, la ponchera con la bebida. La niebla sale alrededor y la bebida nunca toca el hielo seco.</p>
				</li>
				<li>
					<div class="hw-ideas__ilus"><?php echo $hw_ilus( 'foto' ); // phpcs:ignore ?></div>
					<h3>Entrada o photocall</h3>
					<p>Una cubeta con agua caliente y pellets detrás del photocall o junto a la puerta, fuera del paso y del alcance de los niños. Las fotos salen con niebla a los pies.</p>
				</li>
			</ol>
			<p class="hw-nota">Antes de probar cualquiera, lee las <a href="#seguridad">6 reglas de seguridad</a>. Más usos, de la coctelería a los eventos, en <a href="<?php echo esc_url( home_url( '/aplicaciones-del-hielo-seco/' ) ); ?>">aplicaciones del hielo seco</a>.</p>
		</div>
	</section>

	<!-- 3 · FORMATO -->
	<section class="hw-seccion hw-seccion--alt" id="formato" aria-labelledby="hw-formato-t">
		<div class="hw-wrap">
			<header class="hw-cabecera" data-hw-revela>
				<p class="hw-antetitulo">Formato</p>
				<h2 id="hw-formato-t">¿3 mm o 16 mm? Para niebla, 3 mm</h2>
				<p>Mismo precio en los dos formatos. Cambia cómo sale la niebla.</p>
			</header>
			<div class="hw-formatos" data-hw-revela="grupo">
				<article class="hw-formato hw-formato--destacado">
					<div class="hw-formato__foto">
						<img src="<?php echo esc_url( $hw_img ); ?>/formato-3mm-480.webp"
							srcset="<?php echo esc_url( $hw_img ); ?>/formato-3mm-480.webp 480w, <?php echo esc_url( $hw_img ); ?>/formato-3mm-800.webp 800w"
							sizes="(min-width: 1100px) 240px, (min-width: 700px) 45vw, 40vw" width="480" height="599" loading="lazy" decoding="async"
							alt="Caja EPS abierta llena de pellets de hielo seco de 3 mm soltando vapor">
						<span class="hw-formato__vaho" aria-hidden="true"></span>
					</div>
					<div class="hw-formato__txt">
						<p class="hw-sello">Recomendado para Halloween</p>
						<h3>Pellets de 3 mm</h3>
						<p>Más superficie en contacto con el agua: niebla más densa y al momento, aunque se gasta antes. El formato para calderos, ponchera y niebla por el suelo.</p>
						<dl class="hw-datos">
							<div><dt>Niebla</dt><dd>Inmediata y densa</dd></div>
							<div><dt>Ideal para</dt><dd>Calderos, ponchera, suelo</dd></div>
						</dl>
						<a class="hw-btn hw-btn--bloque hw-formato__btn" href="<?php echo esc_url( $hw_pedir( 3, '3mm' ) ); ?>">Pedir en 3 mm</a>
					</div>
				</article>
				<article class="hw-formato">
					<div class="hw-formato__foto">
						<img src="<?php echo esc_url( $hw_img ); ?>/formato-16mm-480.webp"
							srcset="<?php echo esc_url( $hw_img ); ?>/formato-16mm-480.webp 480w, <?php echo esc_url( $hw_img ); ?>/formato-16mm-800.webp 800w"
							sizes="(min-width: 1100px) 240px, (min-width: 700px) 45vw, 40vw" width="480" height="600" loading="lazy" decoding="async"
							alt="Caja EPS abierta con nuggets de hielo seco de 16 mm y vapor cayendo por los lados">
						<span class="hw-formato__vaho" aria-hidden="true"></span>
					</div>
					<div class="hw-formato__txt">
						<p class="hw-sello hw-sello--linea">Para que dure</p>
						<h3>Nuggets de 16 mm</h3>
						<p>Sublima más despacio y aguanta más en la caja. Elígelo si la fiesta es larga o quieres una niebla más constante.</p>
						<dl class="hw-datos">
							<div><dt>Niebla</dt><dd>Más constante</dd></div>
							<div><dt>Ideal para</dt><dd>Fiestas largas, eventos</dd></div>
						</dl>
						<a class="hw-btn hw-btn--bloque hw-btn--linea hw-formato__btn" href="<?php echo esc_url( $hw_pedir( 3, '16mm' ) ); ?>">Pedir en 16 mm</a>
					</div>
				</article>
			</div>
		</div>
	</section>

	<!-- 4 · CANTIDAD -->
	<section class="hw-seccion" id="cantidad" aria-labelledby="hw-cantidad-t">
		<div class="hw-wrap">
			<header class="hw-cabecera" data-hw-revela>
				<p class="hw-antetitulo">Cantidad</p>
				<h2 id="hw-cantidad-t">¿Cuánto hielo seco necesito?</h2>
				<p>Cantidades orientativas para una noche de niebla. Si vas a tener varios puntos de niebla a la vez, sube un escalón.</p>
			</header>
			<ul class="hw-packs" data-hw-revela="grupo">
				<li class="hw-pack">
					<p class="hw-pack__uso">En casa</p>
					<p class="hw-pack__kg"><span class="hw-pack__num">3</span> kg</p>
					<p class="hw-pack__desc">Un caldero o una ponchera para una fiesta en casa.</p>
					<p class="hw-pack__precio"><?php echo esc_html( dipt_euros( dipt_precio_pack( 3 )['con_iva'] ) ); ?> <small>IVA incl. · <?php echo esc_html( dipt_euros( dipt_precio_pack( 3 )['sin_iva'] ) ); ?> + IVA</small></p>
					<a class="hw-btn hw-btn--bloque" href="<?php echo esc_url( $hw_pedir( 3 ) ); ?>">Pedir 3 kg</a>
				</li>
				<li class="hw-pack">
					<p class="hw-pack__uso">Fiesta grande</p>
					<p class="hw-pack__kg"><span class="hw-pack__num">10</span> kg</p>
					<p class="hw-pack__desc">Varios puntos de niebla o una fiesta de varias horas.</p>
					<p class="hw-pack__precio"><?php echo esc_html( dipt_euros( dipt_precio_pack( 10 )['con_iva'] ) ); ?> <small>IVA incl. · <?php echo esc_html( dipt_euros( dipt_precio_pack( 10 )['sin_iva'] ) ); ?> + IVA</small></p>
					<a class="hw-btn hw-btn--bloque" href="<?php echo esc_url( $hw_pedir( 10 ) ); ?>">Pedir 10 kg</a>
				</li>
				<li class="hw-pack hw-pack--doble">
					<p class="hw-pack__uso">Bar o evento</p>
					<p class="hw-pack__kg"><span class="hw-pack__num">15–20</span> kg</p>
					<p class="hw-pack__desc">Para un local o un evento con varios puntos de niebla.</p>
					<div class="hw-pack__opciones">
						<div>
							<p class="hw-pack__precio"><b>15 kg</b> <?php echo esc_html( dipt_euros( dipt_precio_pack( 15 )['con_iva'] ) ); ?> <small>IVA incl. · <?php echo esc_html( dipt_euros( dipt_precio_pack( 15 )['sin_iva'] ) ); ?> + IVA</small></p>
							<a class="hw-btn hw-btn--bloque" href="<?php echo esc_url( $hw_pedir( 15 ) ); ?>">Pedir 15 kg</a>
						</div>
						<div>
							<p class="hw-pack__precio"><b>20 kg</b> <?php echo esc_html( dipt_euros( dipt_precio_pack( 20 )['con_iva'] ) ); ?> <small>IVA incl. · <?php echo esc_html( dipt_euros( dipt_precio_pack( 20 )['sin_iva'] ) ); ?> + IVA</small></p>
							<a class="hw-btn hw-btn--bloque hw-btn--linea" href="<?php echo esc_url( $hw_pedir( 20 ) ); ?>">Pedir 20 kg</a>
						</div>
					</div>
				</li>
			</ul>
			<p class="hw-nota">Caja EPS incluida. El envío depende del peso y del destino, y lo ves antes de pagar. Hasta 250 kg por pedido.</p>
		</div>
	</section>

	<!-- 5 · CÓMO HACER NIEBLA -->
	<section class="hw-seccion hw-seccion--alt hw-seccion--bruma" id="como" aria-labelledby="hw-como-t">
		<div class="hw-bruma" aria-hidden="true"><span></span></div>
		<div class="hw-wrap hw-como">
			<div class="hw-como__intro" data-hw-revela>
				<p class="hw-antetitulo">Paso a paso</p>
				<h2 id="hw-como-t">Cómo hacer niebla en 3 pasos</h2>
				<p class="hw-temp"><span aria-hidden="true"><span class="hw-temp__num" data-hw-temp>−78,5</span><span class="hw-temp__u">°C</span></span><span class="hw-oculto">−78,5 °C</span></p>
				<p class="hw-temp__pie">Es la temperatura del hielo seco. Por eso, siempre con guantes y pinzas.</p>
			</div>
			<ol class="hw-pasos" data-hw-pasos>
				<li>
					<h3>Prepara un recipiente abierto</h3>
					<p>Caldero, cubeta o bol resistente, con agua caliente. Nunca un recipiente cerrado.</p>
				</li>
				<li>
					<h3>Añade los pellets con pinzas</h3>
					<p>Con guantes térmicos, nunca con la mano. La niebla sale al momento: cuanto más caliente el agua, más niebla.</p>
				</li>
				<li>
					<h3>Repón y ventila</h3>
					<p>Cuando el agua se enfría, la niebla baja: cambia el agua por otra caliente y añade más pellets. Mantén el espacio ventilado.</p>
				</li>
			</ol>
		</div>
	</section>

	<!-- 6 · FECHA LÍMITE -->
	<section class="hw-seccion" id="fecha" aria-labelledby="hw-fecha-t">
		<div class="hw-wrap hw-fecha">
			<div class="hw-fecha__cuenta" data-hw-revela
				data-hw-cuenta
				data-limite-viernes="<?php echo esc_attr( $hw_limite_viernes ); ?>"
				data-limite-sabado="<?php echo esc_attr( $hw_limite_sabado ); ?>"
				data-fin="<?php echo esc_attr( $hw_fin_campana ); ?>">
				<p class="hw-antetitulo">Fecha límite</p>
				<h2 id="hw-fecha-t">¿Hasta cuándo puedo pedir?</h2>
				<p class="hw-reloj" data-hw-reloj>Jue 29/10 · 12:00</p>
				<p class="hw-reloj__pie" data-hw-reloj-pie>Último pedido para recibirlo el viernes 30 por la mañana.</p>
				<a class="hw-btn" href="<?php echo esc_url( $hw_pedir( 3 ) ); ?>">Pedir hielo seco</a>
			</div>
			<ol class="hw-linea" data-hw-revela="grupo">
				<li>
					<p class="hw-linea__cuando">Jue 29/10 · 12:00</p>
					<p>Último pedido para recibirlo el viernes 30 por la mañana.</p>
				</li>
				<li>
					<p class="hw-linea__cuando">Vie 30/10 · mañana</p>
					<p>Entrega con MRW en tu dirección, en caja EPS.</p>
				</li>
				<li>
					<p class="hw-linea__cuando">Sáb 31/10</p>
					<p>Entrega en sábado según zona, con suplemento de 11,74 € (IVA incl.). Pide antes de las 12:00 del viernes 30. Confirma tu zona en <a href="<?php echo esc_url( home_url( '/envios-y-plazos/' ) ); ?>">envíos y plazos</a> o llámanos al <a href="<?php echo esc_url( $hw_tel ); ?>">936 73 76 41</a>.</p>
				</li>
				<li>
					<p class="hw-linea__cuando">Recogida en Mataró</p>
					<p>De lunes a sábado, con hora confirmada. Para Maresme, Vallès y área de Barcelona.</p>
				</li>
			</ol>
			<p class="hw-nota hw-fecha__nota">No lo pidas con muchos días de antelación: el hielo seco sublima poco a poco. Lo ideal es recibirlo la víspera de la fiesta. ¿Tu fiesta es el sábado por la noche y lo recibes el viernes? Elige 16 mm, que aguanta más, o entrega en sábado si tu zona la tiene.</p>
		</div>
	</section>

	<!-- 7 · SEGURIDAD -->
	<section class="hw-seccion hw-seccion--seguridad" id="seguridad" aria-labelledby="hw-seg-t">
		<div class="hw-wrap">
			<header class="hw-cabecera" data-hw-revela>
				<p class="hw-antetitulo">Seguridad</p>
				<h2 id="hw-seg-t">6 reglas antes de empezar</h2>
			</header>
			<ol class="hw-reglas" data-hw-revela="grupo">
				<li><b>Guantes térmicos y pinzas.</b> Nunca lo toques con la piel: a −78,5 °C quema.</li>
				<li><b>Espacio ventilado.</b> El CO₂ desplaza el oxígeno y se acumula a ras de suelo.</li>
				<li><b>Nunca en recipientes cerrados.</b> La presión puede reventarlos.</li>
				<li><b>No se come ni se bebe.</b> Nunca pellets dentro de algo que se vaya a beber (vaso, copa, jarra o ponchera): usa siempre doble recipiente.</li>
				<li><b>Lejos de niños y mascotas.</b> Guárdalo donde no lleguen.</li>
				<li><b>En el coche, en el maletero.</b> Y con una ventanilla abierta durante el trayecto.</li>
			</ol>
			<a class="hw-enlace-flecha" href="<?php echo esc_url( home_url( '/seguridad-del-hielo-seco/' ) ); ?>">Guía completa de seguridad del hielo seco</a>
		</div>
	</section>

	<!-- 8 · NEGOCIOS (B2B, de usted) -->
	<section class="hw-seccion hw-seccion--alt" id="negocios" aria-labelledby="hw-b2b-t">
		<div class="hw-wrap hw-b2b">
			<div class="hw-b2b__foto" data-hw-revela>
				<img src="<?php echo esc_url( $hw_img ); ?>/caja-cenital-480.webp"
					srcset="<?php echo esc_url( $hw_img ); ?>/caja-cenital-480.webp 480w, <?php echo esc_url( $hw_img ); ?>/caja-cenital-800.webp 800w"
					sizes="(min-width: 1100px) 440px, (min-width: 540px) 480px, calc(100vw - 32px)" width="480" height="480" loading="lazy" decoding="async"
					alt="Caja EPS vista desde arriba, llena de nuggets de hielo seco y rodeada de vapor">
				<span class="hw-formato__vaho" aria-hidden="true"></span>
			</div>
			<div class="hw-b2b__txt" data-hw-revela>
				<p class="hw-antetitulo">Bares, discotecas y organizadores</p>
				<h2 id="hw-b2b-t">¿Tiene un local o organiza un evento de Halloween?</h2>
				<p>Díganos cuántos puntos de niebla va a tener y le orientamos sobre la cantidad. Entrega el siguiente día laborable en la península o recogida en nuestro almacén de Mataró (Maresme, Vallès y área de Barcelona).</p>
				<ul class="hw-lista">
					<li>Pedidos de hasta 250 kg</li>
					<li>Mismo precio en 3 mm y 16 mm</li>
					<li>Suministro programado semanal, quincenal o mensual, con mejor precio</li>
				</ul>
				<div class="hw-botones">
					<a class="hw-btn" href="<?php echo esc_url( $hw_tel ); ?>">Llamar 936 73 76 41</a>
					<a class="hw-btn hw-btn--linea" href="<?php echo esc_url( $hw_whatsapp ); ?>" rel="noopener">Escribir por WhatsApp</a>
				</div>
				<p class="hw-nota">L–V, 9:00–18:00 · <a href="<?php echo esc_url( dipt_url( 'empresas' ) ); ?>">Programar suministro de hielo seco</a></p>
			</div>
		</div>
	</section>

	<!-- 9 · PREGUNTAS -->
	<section class="hw-seccion" id="preguntas" aria-labelledby="hw-faq-t">
		<div class="hw-wrap hw-faq">
			<header class="hw-cabecera" data-hw-revela>
				<p class="hw-antetitulo">Preguntas frecuentes</p>
				<h2 id="hw-faq-t">Preguntas frecuentes sobre hielo seco para Halloween</h2>
			</header>
			<div class="hw-faq__lista" data-hw-revela>
				<?php foreach ( $hw_faq as $f ) : ?>
					<details>
						<summary><?php echo esc_html( $f['q'] ); ?></summary>
						<p><?php echo esc_html( $f['a'] ); ?></p>
					</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- 10 · CTA FINAL -->
	<section class="hw-final" aria-labelledby="hw-final-t">
		<img class="hw-final__img" src="<?php echo esc_url( $hw_img ); ?>/niebla-cenital-800.webp"
			srcset="<?php echo esc_url( $hw_img ); ?>/niebla-cenital-800.webp 800w, <?php echo esc_url( $hw_img ); ?>/niebla-cenital-1440.webp 1440w"
			sizes="100vw" width="1440" height="1080" loading="lazy" decoding="async" alt="">
		<div class="hw-ambiente" aria-hidden="true">
			<div class="hw-niebla hw-niebla--final"><span class="hw-niebla__capa"></span><span class="hw-niebla__capa hw-niebla__capa--2"></span></div>
			<div class="hw-murcielagos hw-murcielagos--final">
				<span class="hw-murcielago hw-murcielago--2"><span><?php echo $hw_murcielago; // phpcs:ignore ?></span></span>
			</div>
		</div>
		<div class="hw-wrap hw-final__cuerpo" data-hw-revela>
			<h2 id="hw-final-t"><span class="hw-f-v">Niebla de verdad, lista para el viernes 30</span><span class="hw-f-s">Niebla de verdad para tu Halloween</span><span class="hw-f-c">Niebla de verdad para tu próxima fiesta</span></h2>
			<p><span class="hw-f-v">Pide antes de las 12:00 del jueves 29 de octubre.</span><span class="hw-f-s">Para el sábado 31, pide antes de las 12:00 del viernes 30 (según zona).</span><span class="hw-f-c">Pide antes de las 12:00 y lo recibes el siguiente día laborable.</span> Te llega en caja EPS, listo para usar. Antes de empezar, lee la <a href="<?php echo esc_url( home_url( '/seguridad-del-hielo-seco/' ) ); ?>">guía de seguridad</a>.</p>
			<div class="hw-botones">
				<a class="hw-btn" href="<?php echo esc_url( $hw_pedir( 3 ) ); ?>">Pedir hielo seco</a>
				<a class="hw-btn hw-btn--linea" href="<?php echo esc_url( $hw_tel ); ?>">Llamar</a>
			</div>
		</div>
	</section>

</main>
<?php
get_footer();
