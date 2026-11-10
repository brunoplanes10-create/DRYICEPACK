<?php
/**
 * Hielo seco para limpieza criogénica (10/11/2026). Uso B2B hermano de Industria, de usted.
 * Firma propia: «la pasada». Una franja de hollín que el chorro de pellets deja limpia (solo transform y opacity):
 * en el hero, tres carriles de una placa de ensayo que dejan a la vista la ficha; entre kilos y suministro, una franja
 * que se limpia una vez al entrar. Bocetos elegidos (zz-bocetos/limpieza-*.html): hero C · calibre A · llega A ·
 * kilos A · suministro A · INDUNOVA A · seguridad A (con el dibujo de la nave rehecho).
 * Secciones: K1 hero con placa de ensayo · K2 por qué 3 mm (dibujo con cota sobre la foto) · K3 reloj de 26 horas ·
 * K4 tarifa en filas que llevan a la compra · franja · K5 suministro con calendario · K6 con máquina o sin ella (INDUNOVA) ·
 * K7 seguridad con la nave en corte · K8 preguntas · cierre común.
 * Tonos: noche · papel · noche · blanco · (franja) · escarcha · marino · noche · blanco · noche (cierre) · pie marino.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c = dipt_contenido( 'limpieza' );
$GLOBALS['dipt_hero_oscuro'] = true;
dipt_precargar( 'uso-industria', '(max-width: 900px) 100vw, 46vw' );

// La compra abre con pellets de 3 mm y el pack mayor que se vende ahora (?kg=20&formato=3mm)
$cr_packs  = dipt_packs();
$cr_mayor  = $cr_packs ? max( array_map( 'floatval', array_keys( $cr_packs ) ) ) : 20;
$cr_pedir  = static fn( $kg ) => add_query_arg( array( 'kg' => 0 + $kg, 'formato' => '3mm' ), dipt_url( 'producto' ) );

// La pasada: franja sucia que se mueve entera (un solo transform); el frente lleva los pellets que llegan en diagonal
$cr_pasada = static function ( $texto_html, $clase = 'pasada--bucle', $extra = '' ) {
	$pellets = '';
	foreach ( array( 18, 42, 66, 30, 78, 54, 10, 88 ) as $k => $y ) $pellets .= '<i style="--y:' . $y . ';--k:' . $k . '"></i>';
	return '<div class="pasada ' . esc_attr( $clase ) . '"' . $extra . ' aria-hidden="true"><div class="pasada__texto">' . $texto_html . '</div>'
		. '<div class="pasada__sucio"><span class="pasada__frente">' . $pellets . '</span></div></div>';
};
get_header();
?>

<!-- K1 · Hero: texto a la izquierda; foto (LCP, no se anima) con la placa de ensayo montada encima -->
<section class="cr-hero tono-oscuro" aria-labelledby="cr-hero-t">
	<div class="envoltura">
		<?php echo dipt_migas( array( array( $c['migas'][0], dipt_url( 'inicio' ) ), array( $c['migas'][1], dipt_url( 'aplicaciones' ) ), array( $c['migas'][2], dipt_url( 'limpieza' ) ) ) ); // phpcs:ignore ?>
		<div class="cr-hero__rej">
			<div class="cr-hero__texto">
				<h1 class="t-h1" id="cr-hero-t"><?php dipt_html( '<span class="tramo">' . str_replace( '|', '</span> <span class="tramo">', $c['hero']['titulo'] ) . '</span>' ); // con espacio entre tramos: el texto se lee seguido ?></h1>
				<p class="entrada"><?php echo esc_html( $c['hero']['texto'] ); ?></p>
				<div class="cr-hero__acciones">
					<?php echo dipt_boton( $c['hero']['boton'], $cr_pedir( $cr_mayor ), 'hielo' ); // phpcs:ignore ?>
					<?php echo dipt_enlace( $c['hero']['enlace'], '#suministro' ); // phpcs:ignore ?>
				</div>
			</div>
			<div class="cr-hero__mesa">
				<div class="cr-hero__foto"><?php echo dipt_foto( 'uso-industria', $c['hero']['alt'], array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 46vw', 'recorte' => '24% 45%' ) ); // phpcs:ignore ?></div>
				<div class="cr-placa" data-pausa>
					<?php foreach ( $c['hero']['placa'] as $i => $p ) : ?>
						<?php echo $cr_pasada( '<span>' . esc_html( $p[0] ) . '</span><b>' . esc_html( $p[1] ) . '</b>', 'pasada--bucle', ' style="--n:' . (int) $i . '"' ); // phpcs:ignore ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- K2 · Por qué 3 mm: el dibujo de los pellets con la cota del diámetro, montado sobre la foto real -->
<section class="cr-calibre tono-papel seccion" aria-labelledby="cr-calibre-t">
	<div class="envoltura cr-calibre__rej">
		<div class="cr-calibre__texto">
			<?php echo dipt_titulo( 'h2', $c['calibre']['titulo'], 't-h2', 'cr-calibre-t' ); // phpcs:ignore ?>
			<?php foreach ( $c['calibre']['texto'] as $i => $t ) : ?>
				<p data-revela style="--i:<?php echo (int) $i; ?>"><?php dipt_html( $t ); ?></p>
			<?php endforeach; ?>
			<p class="cr-calibre__no" data-revela style="--i:2"><b class="dato"><?php echo esc_html( $c['calibre']['no'][0] ); ?></b><span><?php echo esc_html( $c['calibre']['no'][1] ); ?></span></p>
		</div>
		<div class="cr-calibre__lamina">
			<div class="cr-calibre__foto foto--lamina" data-cortina><?php echo dipt_foto( 'cenital-3mm', $c['calibre']['foto'], array( 'sizes' => '(max-width: 900px) 92vw, 50vw' ) ); // phpcs:ignore ?></div>
			<figure class="cr-dibujo" data-vivo>
				<svg viewBox="0 0 320 132" width="320" height="132" role="img" aria-labelledby="cr-dibujo-t" focusable="false">
					<title id="cr-dibujo-t"><?php echo esc_html( $c['calibre']['dibujo'] ); ?></title>
					<g class="cr-dibujo__p" style="--i:0"><g transform="translate(14 44) rotate(-8)"><rect class="cr-p" width="118" height="34" rx="17"/><ellipse class="cr-c" cx="101" cy="17" rx="10" ry="17"/></g></g>
					<g class="cr-dibujo__p" style="--i:1"><g transform="translate(150 16) rotate(13)"><rect class="cr-p" width="94" height="34" rx="17"/><ellipse class="cr-c" cx="77" cy="17" rx="10" ry="17"/></g></g>
					<g class="cr-dibujo__p" style="--i:2"><g transform="translate(96 84) rotate(3)"><rect class="cr-p" width="138" height="34" rx="17"/><ellipse class="cr-c" cx="121" cy="17" rx="10" ry="17"/></g></g>
					<?php // Cota del diámetro en el extremo del pellet de abajo (34 de alto = 3 mm): aparece al entrar ?>
					<g class="cr-dibujo__cota" transform="translate(96 84) rotate(3)"><path d="M142 0H162M142 34H162"/><path d="M156 2V32"/><path d="M153 6l3-4 3 4M153 28l3 4 3-4"/></g>
					<text class="cr-dibujo__valor" x="264" y="113"><?php echo esc_html( $c['calibre']['cota'] ); ?></text>
				</svg>
			</figure>
		</div>
	</div>
</section>

<!-- K3 · Pida hoy, limpie mañana: línea de 26 horas que se llena al entrar y la caja que la recorre -->
<section class="cr-llega tono-oscuro seccion" aria-labelledby="cr-llega-t">
	<div class="envoltura">
		<div class="cr-llega__cab">
			<?php echo dipt_titulo( 'h2', $c['llega']['titulo'], 't-h2', 'cr-llega-t', 'cortina' ); // phpcs:ignore ?>
			<p class="entrada" data-revela><?php echo esc_html( $c['llega']['texto'] ); ?></p>
		</div>
		<div class="cr-linea" data-vivo aria-hidden="true"><span class="cr-linea__lleno"></span><span class="cr-linea__mueve"><span class="cr-linea__caja"><?php echo dipt_icono( 'caja' ); // phpcs:ignore ?></span></span></div>
		<ol class="cr-hitos">
			<?php foreach ( $c['llega']['hitos'] as $i => $h ) : ?>
				<li data-revela style="--i:<?php echo (int) $i; ?>">
					<span class="cr-hitos__cifra"><?php echo esc_html( $h[0] ); ?><?php if ( $h[1] ) : ?><small> <?php echo esc_html( $h[1] ); ?></small><?php endif; ?></span>
					<strong><?php echo esc_html( $h[2] ); ?></strong>
					<span class="cr-hitos__texto"><?php echo esc_html( $h[3] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
		<div class="cr-notas">
			<?php foreach ( $c['llega']['notas'] as $i => $n ) : ?>
				<p data-revela style="--i:<?php echo (int) $i; ?>"><b class="dato"><?php echo esc_html( $n[0] ); ?></b><?php dipt_html( $n[1] ); ?></p>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- K4 · Kilos por pedido: una fila por pack que se vende ahora (precios de la tienda); cada fila abre la compra con 3 mm -->
<section class="cr-kilos tono-blanco seccion" aria-labelledby="cr-kilos-t">
	<div class="envoltura cr-kilos__rej">
		<div class="cr-kilos__lado">
			<?php echo dipt_titulo( 'h2', $c['kilos']['titulo'], 't-h2', 'cr-kilos-t', 'revela' ); // phpcs:ignore ?>
			<p data-revela><?php echo esc_html( $c['kilos']['texto'] ); ?></p>
		</div>
		<div>
			<ol class="cr-filas" data-activa-grupo>
				<?php
				foreach ( array_keys( $cr_packs ) as $i => $kg ) :
					$p = dipt_precio_pack( $kg );
					if ( $p['sin_iva'] <= 0 ) continue;
					?>
					<li class="cr-fila" data-activa data-revela style="--i:<?php echo (int) $i; ?>">
						<a href="<?php echo esc_url( $cr_pedir( $kg ) ); ?>">
							<span class="visually-hidden"><?php echo esc_html( $c['kilos']['pedir'] ); ?></span>
							<span class="cr-fila__kg"><?php echo esc_html( 0 + $kg ); ?><small><?php echo esc_html( $c['kilos']['kg'] ); ?></small></span>
							<span class="cr-fila__p"><?php echo esc_html( dipt_euros( $p['sin_iva'] ) . ' ' . $c['kilos']['mas_iva'] ); ?><small><?php echo esc_html( sprintf( $c['kilos']['con_iva'], dipt_euros( $p['con_iva'] ) ) ); ?></small></span>
							<span class="cr-fila__p cr-fila__p--kilo"><?php echo esc_html( dipt_euros( $p['sin_iva'] / (float) $kg ) ); ?><small><?php echo esc_html( $c['kilos']['kilo'] ); ?></small></span>
							<span class="cr-fila__ir" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="cr-kilos__envio"><?php echo esc_html( $c['kilos']['envio'] ); ?></p>
			<div class="cr-limites">
				<?php foreach ( $c['kilos']['limites'] as $i => $l ) : ?>
					<p data-revela style="--i:<?php echo (int) $i; ?>"><strong><?php echo esc_html( $l[0] ); ?></strong><?php dipt_html( $l[1] ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<?php // Franja entre kilos y suministro: la pasada la limpia una vez al entrar (decorativa; sin JS se ve limpia) ?>
<?php echo $cr_pasada( '<span>' . implode( '</span><span>', array_map( 'esc_html', $c['franja'] ) ) . '</span>', 'pasada--una cr-franja', ' data-vivo' ); // phpcs:ignore ?>

<!-- K5 · Suministro programado: se elige el ritmo y se encienden los días de entrega de un calendario de ejemplo -->
<section class="cr-sumi tono-escarcha seccion" aria-labelledby="cr-sumi-t" id="suministro">
	<div class="envoltura cr-sumi__rej">
		<div>
			<?php echo dipt_titulo( 'h2', $c['suministro']['titulo'], 't-h2', 'cr-sumi-t', 'revela' ); // phpcs:ignore ?>
			<p class="entrada cr-sumi__texto" data-revela><?php echo esc_html( $c['suministro']['texto'] ); ?></p>
			<?php
			$cr_ritmos = $c['suministro']['ritmos'];
			$cr_r0     = (int) array_key_first( $cr_ritmos );
			?>
			<div class="cr-ritmo" role="group" aria-label="<?php echo esc_attr( $c['suministro']['ritmo_t'] ); ?>" data-ritmo>
				<?php foreach ( $cr_ritmos as $r => $nombre ) : ?>
					<button type="button" data-r="<?php echo (int) $r; ?>" aria-pressed="<?php echo $r === $cr_r0 ? 'true' : 'false'; ?>"><?php echo esc_html( $nombre ); ?></button>
				<?php endforeach; ?>
			</div>
			<div class="cr-cal" data-vivo data-cal role="img" aria-label="<?php echo esc_attr( sprintf( $c['suministro']['cal_alt'], function_exists( 'mb_strtolower' ) ? mb_strtolower( $cr_ritmos[ $cr_r0 ] ) : strtolower( $cr_ritmos[ $cr_r0 ] ) ) ); ?>" data-alt="<?php echo esc_attr( $c['suministro']['cal_alt'] ); ?>">
				<?php foreach ( $c['suministro']['dias'] as $d ) : ?><b><?php echo esc_html( $d ); ?></b><?php endforeach; ?>
				<?php
				for ( $s = 0; $s < 5; $s++ ) {
					for ( $d = 0; $d < 6; $d++ ) {
						$on = 2 === $d && 0 === $s % $cr_r0;
						echo '<span class="cr-dia' . ( $on ? ' on' : '' ) . '" data-s="' . (int) $s . '" data-d="' . (int) $d . '" style="--s:' . (int) $s . '">' . ( 2 === $d ? dipt_icono( 'caja' ) : '' ) . '</span>'; // phpcs:ignore
					}
				}
				?>
			</div>
			<p class="cr-cal__nota dato"><?php echo esc_html( $c['suministro']['cal_nota'] ); ?></p>
		</div>
		<div>
			<ul class="cr-cond">
				<?php foreach ( $c['suministro']['condiciones'] as $i => $l ) : ?>
					<li data-revela style="--i:<?php echo (int) $i; ?>"><strong><?php echo esc_html( $l[0] ); ?></strong><span><?php echo esc_html( $l[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<div class="cr-sumi__acciones">
				<?php echo dipt_boton( $c['suministro']['boton'], dipt_url( 'empresas', null, 'alta' ), 'marino' ); // phpcs:ignore ?>
				<?php echo dipt_enlace( $c['suministro']['enlace'], dipt_url( 'empresas' ) ); // phpcs:ignore ?>
			</div>
		</div>
	</div>
</section>

<!-- K6 · Con máquina o sin ella: una línea que se parte en dos; la segunda rama lleva al servicio de INDUNOVA -->
<section class="cr-indu tono-marino seccion" aria-labelledby="cr-indu-t">
	<div class="envoltura cr-indu__rej">
		<div class="cr-indu__foto foto--lamina" data-cortina><?php echo dipt_foto( 'uso-industria-pala', $c['indunova']['alt'], array( 'sizes' => '(max-width: 900px) 92vw, 40vw', 'recorte' => '62% 50%' ) ); // phpcs:ignore ?></div>
		<div>
			<?php echo dipt_titulo( 'h2', $c['indunova']['titulo'], 't-h2', 'cr-indu-t', 'cortina' ); // phpcs:ignore ?>
			<div class="cr-via" data-vivo data-pausa>
				<svg class="cr-via__trazo" viewBox="0 0 40 300" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="M2 0V300M2 140C2 185 38 185 38 230V300"/></svg>
				<span class="cr-via__punto" aria-hidden="true"></span>
				<?php foreach ( $c['indunova']['ramas'] as $i => $r ) : ?>
					<div class="cr-rama" data-revela style="--i:<?php echo (int) $i * 2; ?>">
						<p class="cr-rama__pre dato"><?php echo esc_html( $r[0] ); ?></p>
						<h3><?php echo esc_html( $r[1] ); ?></h3>
						<p><?php echo esc_html( $r[2] ); ?></p>
						<?php if ( 1 === $i ) : ?>
							<?php echo dipt_enlace( $c['indunova']['enlace'], $c['indunova']['url'], array( 'target' => '_blank', 'rel' => 'noopener' ) ); // phpcs:ignore ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- K7 · Seguridad: la nave en corte (el CO₂ sube desde el suelo; al abrir la puerta, el aire lo saca) y las reglas -->
<section class="cr-seg tono-oscuro seccion" aria-labelledby="cr-seg-t">
	<div class="envoltura cr-seg__rej">
		<figure class="cr-nave" data-pausa aria-hidden="true">
			<svg viewBox="0 0 640 420" width="640" height="420" focusable="false">
				<defs>
					<pattern id="cr-rayado" width="9" height="9" patternUnits="userSpaceOnUse" patternTransform="rotate(-45)"><path d="M0 0V9" class="cr-nave__raya"/></pattern>
					<clipPath id="cr-hueco"><rect x="580" y="236" width="44" height="146"/></clipPath>
				</defs>
				<text class="cr-nave__rotulo" x="40" y="40"><?php echo esc_html( $c['seguridad']['corte']['nave'] ); ?></text>
				<g class="cr-nave__co2"><rect x="22" y="252" width="580" height="128" fill="url(#cr-rayado)"/></g>
				<g class="cr-nave__nivel"><path d="M22 252H560"/><text x="34" y="244"><?php echo esc_html( $c['seguridad']['corte']['co2'] ); ?></text></g>
				<path class="cr-nave__obra" d="M20 380V96L320 52 620 96V236M620 380H640M0 380H20M20 380H620"/>
				<?php // Máquina: tolva, cuerpo y ruedas; manguera hasta la pistola; la pieza que se limpia ?>
				<g class="cr-nave__maq"><path d="M150 286h76l-12 26h-52z"/><rect x="156" y="312" width="64" height="58" rx="3"/><circle cx="168" cy="374" r="6"/><circle cx="208" cy="374" r="6"/><path class="cr-manguera" d="M220 340c40 0 40-36 96-36"/><path d="M316 296h30l6 6h10v6h-10l-6 6h-30z"/><rect x="452" y="282" width="56" height="98"/><path d="M452 300h56M452 320h56"/></g>
				<g class="cr-nave__chorro"><?php for ( $k = 0; $k < 7; $k++ ) : ?><circle cx="370" cy="<?php echo 300 + ( $k % 3 ) * 4; ?>" r="2.6" style="--k:<?php echo (int) $k; ?>"/><?php endfor; ?></g>
				<g clip-path="url(#cr-hueco)"><rect class="cr-nave__puerta" x="584" y="236" width="36" height="146"/></g>
				<g class="cr-nave__aire"><path d="M560 300h60m-12-8 12 8-12 8M560 330h70m-12-8 12 8-12 8M560 360h60m-12-8 12 8-12 8"/><text x="600" y="220" text-anchor="end"><?php echo esc_html( $c['seguridad']['corte']['puerta'] ); ?></text></g>
			</svg>
		</figure>
		<div>
			<?php echo dipt_titulo( 'h2', $c['seguridad']['titulo'], 't-h2', 'cr-seg-t', 'revela' ); // phpcs:ignore ?>
			<p class="entrada cr-seg__texto" data-revela><?php echo esc_html( $c['seguridad']['texto'] ); ?></p>
			<ul class="cr-reglas">
				<?php foreach ( $c['seguridad']['reglas'] as $i => $r ) : ?>
					<li data-revela style="--i:<?php echo (int) $i; ?>"><?php echo dipt_icono( $r[0] ); // phpcs:ignore ?><strong><?php echo esc_html( $r[1] ); ?></strong><span><?php echo esc_html( $r[2] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
			<p class="cr-seg__enlace"><?php echo dipt_enlace( $c['seguridad']['enlace'], dipt_url( 'seguridad' ) ); // phpcs:ignore ?></p>
		</div>
	</div>
</section>

<!-- K8 · Preguntas (FAQPage) -->
<section class="cr-faq seccion seccion--densa tono-blanco" aria-labelledby="cr-faq-t">
	<div class="envoltura">
		<?php echo dipt_faq( $c['faq']['lista'], array( 'titulo' => $c['faq']['titulo'], 'id' => 'cr-faq-t' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php
// Cierre común, de usted: la compra con 3 mm y la fecha real de entrega. La página ya enlaza a seguridad en K7, pero el
// cierre mantiene su enlace (seguridad completa y ficha), como en las demás páginas de uso.
dipt_cierre( array(
	'titulo'       => $c['final']['titulo'] ?? '',
	'id'           => 'cr-cierre-t',
	'foto'         => 'pellets-ia',
	'recorte'      => '50% 55%',
	'boton'        => $c['hero']['boton'],
	'boton_url'    => $cr_pedir( $cr_mayor ),
	'corte_textos' => $c['final']['corte'] ?? array( dipt_t( 'corte_antes' ), dipt_t( 'corte_quedan' ), dipt_t( 'corte_despues' ) ),
) );
get_footer();
