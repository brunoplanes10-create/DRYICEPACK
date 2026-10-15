<?php
/**
 * Cuerpo de una guía (rediseño 13/10/2026, plan 2.11): cabecera con la foto de la guía a sangre que pisa el cuerpo,
 * índice con el apartado activo, notas al margen (aviso de seguridad y acceso a la compra), «Para seguir» con la
 * siguiente guía y compra al final. Lo usan las guías del tema (plantillas/guia.php, en tres idiomas) y las entradas
 * del blog (single.php). Espera $dipt_g = array( 'titulo' => '', 'html' => '', 'fecha' => '' (opcional), 'url' => '' ).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$c      = dipt_contenido( 'guias' );
$html   = (string) $dipt_g['html'];
$indice = array();
// Anclas en los h2 para el índice lateral
$html = preg_replace_callback( '#<h2([^>]*)>(.*?)</h2>#s', static function ( $m ) use ( &$indice ) {
	$id       = 'a-' . sanitize_title( wp_strip_all_tags( $m[2] ) );
	$indice[] = array( $id, wp_strip_all_tags( $m[2] ) );
	return '<h2' . $m[1] . ' id="' . esc_attr( $id ) . '">' . $m[2] . '</h2>';
}, $html );
$minutos = max( 1, (int) round( str_word_count( wp_strip_all_tags( $html ) ) / 220 ) );
// Cada apartado (desde su h2 hasta el siguiente) en su <section data-activa>: el índice marca el que cruza el centro
$dipt_trozos = preg_split( '#(?=<h2[\s>])#', $html );
$dipt_cuerpo = '';
foreach ( (array) $dipt_trozos as $dipt_k => $dipt_trozo ) {
	if ( '' === trim( $dipt_trozo ) ) continue;
	$dipt_cuerpo .= 0 === strpos( ltrim( $dipt_trozo ), '<h2' ) ? '<section class="gu-ap" data-activa>' . $dipt_trozo . '</section>' : '<div class="gu-intro">' . $dipt_trozo . '</div>';
}
// Otras guías del mismo idioma para "Para seguir". Una guía con 'relacionadas' => false no aparece en las demás
// y tampoco las lista: en ella solo salen los enlaces fijos de contenido/{idioma}/guias.php.
$dipt_relacionadas = array();
$dipt_sin_rel      = false;
$dipt_esta         = null; // datos de esta guía (foto, formato); null en las entradas del blog
foreach ( dipt_guias() as $dipt_slug => $dipt_rutas ) {
	if ( empty( $dipt_rutas[ dipt_idioma() ] ) ) continue;
	$dipt_otra  = dipt_guia( $dipt_slug );
	$dipt_fuera = ! $dipt_otra || false === ( $dipt_otra['relacionadas'] ?? null );
	if ( dipt_url( 'guia-' . $dipt_slug ) === $dipt_g['url'] ) {
		$dipt_sin_rel = $dipt_fuera;
		$dipt_esta    = $dipt_otra;
		continue;
	}
	if ( ! $dipt_fuera ) $dipt_relacionadas[] = array( dipt_url( 'guia-' . $dipt_slug ), $dipt_otra['titulo'] ?? '', $dipt_otra['foto'] ?? '' );
}
if ( $dipt_sin_rel ) $dipt_relacionadas = array();
$dipt_siguiente = $dipt_relacionadas ? array_shift( $dipt_relacionadas ) : null;
$dipt_foto_g    = $dipt_esta['foto'] ?? '';
$dipt_formato   = $dipt_esta['formato'] ?? '';
$dipt_aviso     = dipt_avisos_seguridad()[0] ?? null; // guantes y pinzas
dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ), array( $dipt_g['titulo'], $dipt_g['url'] ) ) );
?>
<article class="gu<?php echo $dipt_foto_g ? ' gu--foto' : ''; ?>">
	<header class="gu-cabeza tono-escarcha">
		<div class="envoltura gu-cabeza__in">
			<div class="gu-cabeza__texto">
				<?php echo dipt_migas( $GLOBALS['dipt_migas'] ); // phpcs:ignore ?>
				<h1 class="t-h1"><?php echo esc_html( $dipt_g['titulo'] ); ?></h1>
				<p class="gu-cabeza__meta dato"><?php echo esc_html( ( ! empty( $dipt_g['fecha'] ) ? $dipt_g['fecha'] . ' · ' : '' ) . sprintf( $c['lectura'] ?? '%d min', $minutos ) ); ?></p>
			</div>
			<?php if ( $dipt_foto_g ) : ?>
				<div class="gu-cabeza__foto foto--sangre foto--sangre-der foto--4x3">
					<?php echo dipt_foto( $dipt_foto_g, '', array( 'lcp' => true, 'sizes' => '(max-width: 900px) 100vw, 64vw', /* 64vw y no 46vw: el encuadre f-uso-eventos amplía la foto 1,36× y con 46vw salía pixelada */ 'clase' => 'f-' . sanitize_html_class( $dipt_foto_g ) ) ); // phpcs:ignore -- f-{foto}: encuadre propio de la foto en guia.css ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<div class="envoltura gu-cuerpo">
		<?php if ( count( $indice ) > 1 ) : ?>
			<nav class="gu-indice" aria-label="<?php echo esc_attr( $c['indice'] ?? '' ); ?>">
				<p class="dato"><?php echo esc_html( $c['indice'] ?? '' ); ?></p>
				<ol><?php foreach ( $indice as $e ) : ?><li><a href="#<?php echo esc_attr( $e[0] ); ?>"><?php echo esc_html( $e[1] ); ?></a></li><?php endforeach; ?></ol>
			</nav>
		<?php endif; ?>
		<div class="prosa gu-texto" data-activa-grupo><?php echo $dipt_cuerpo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenido propio del tema o de la entrada (filtrado por the_content) ?></div>
		<aside class="gu-margen">
			<?php if ( $dipt_aviso ) : ?>
				<div class="gu-nota gu-nota--aviso" data-revela>
					<span class="gu-nota__ico" aria-hidden="true"><?php echo dipt_icono( $dipt_aviso[0] ); // phpcs:ignore ?></span>
					<p><strong><?php echo esc_html( $dipt_aviso[1] ); ?></strong> <?php echo esc_html( $dipt_aviso[2] ); ?></p>
					<?php echo dipt_enlace( dipt_t( 'seguridad_mas' ), dipt_url( 'seguridad' ) ); // phpcs:ignore ?>
				</div>
			<?php endif; ?>
			<div class="gu-nota gu-nota--compra" data-revela style="--i:1">
				<?php if ( $dipt_formato ) : ?><p class="gu-nota__formato" aria-hidden="true"><?php echo esc_html( str_replace( 'mm', ' mm', $dipt_formato ) ); ?></p><?php endif; ?>
				<?php echo dipt_boton( dipt_t( 'comprar' ), $dipt_formato ? add_query_arg( 'formato', $dipt_formato, dipt_url( 'producto' ) ) : dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			</div>
		</aside>
	</div>
	<?php if ( ! empty( $c['enlaces'] ) || $dipt_siguiente ) : ?>
		<nav class="envoltura gu-seguir" aria-labelledby="gu-seguir-t">
			<p class="dato" id="gu-seguir-t"><?php echo esc_html( $c['seguir'] ?? '' ); ?></p>
			<div class="gu-seguir__in">
				<?php if ( $dipt_siguiente ) : ?>
					<a class="gu-siguiente" href="<?php echo esc_url( $dipt_siguiente[0] ); ?>" data-activa>
						<?php if ( $dipt_siguiente[2] ) : ?><span class="gu-siguiente__foto foto--lamina foto--4x3" data-cortina><?php echo dipt_foto( $dipt_siguiente[2], '', array( 'sizes' => '(max-width: 900px) 40vw, 16rem', 'clase' => 'f-' . sanitize_html_class( $dipt_siguiente[2] ) ) ); // phpcs:ignore ?></span><?php endif; ?>
						<span class="gu-siguiente__titulo"><?php echo esc_html( $dipt_siguiente[1] ); ?></span>
						<span class="gu-siguiente__ir" aria-hidden="true"><?php echo dipt_icono( 'flecha' ); // phpcs:ignore ?></span>
					</a>
				<?php endif; ?>
				<ul>
					<?php foreach ( (array) ( $c['enlaces'] ?? array() ) as $e ) : ?>
						<li><a href="<?php echo esc_url( dipt_url( $e[0] ) ); ?>"><?php echo esc_html( $e[1] ); ?></a></li>
					<?php endforeach; ?>
					<?php foreach ( $dipt_relacionadas as $e ) : // las demás guías del mismo idioma, salvo las que llevan 'relacionadas' => false ?>
						<li><a href="<?php echo esc_url( $e[0] ); ?>"><?php echo esc_html( $e[1] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</nav>
	<?php endif; ?>
	<?php /* Tarjeta de compra a caballo entre la página y el pie (fondo partido: papel arriba, marino abajo, como el pie) */ ?>
	<aside class="gu-compra">
		<div class="envoltura">
			<div class="gu-compra__in tono-oscuro" data-revela>
				<span class="gu-compra__ico" aria-hidden="true"><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?></span>
				<p class="gu-compra__t t-h2"><?php echo esc_html( $c['cta'] ?? '' ); ?></p>
				<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			</div>
		</div>
	</aside>
</article>
