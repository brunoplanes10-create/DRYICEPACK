<?php
/**
 * Cuerpo de una guía: índice automático de los apartados, tiempo de lectura y compra al final.
 * Lo usan las guías del tema (plantillas/guia.php, en tres idiomas) y las entradas del blog (single.php).
 * Espera $dipt_g = array( 'titulo' => '', 'html' => '', 'fecha' => '' (opcional), 'url' => '' ).
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
// Otras guías del mismo idioma para "Para seguir". Una guía con 'relacionadas' => false no aparece en las demás
// y tampoco las lista: en ella solo salen los enlaces fijos de contenido/{idioma}/guias.php.
$dipt_relacionadas = array();
$dipt_sin_rel      = false;
foreach ( dipt_guias() as $dipt_slug => $dipt_rutas ) {
	if ( empty( $dipt_rutas[ dipt_idioma() ] ) ) continue;
	$dipt_otra  = dipt_guia( $dipt_slug );
	$dipt_fuera = ! $dipt_otra || false === ( $dipt_otra['relacionadas'] ?? null );
	if ( dipt_url( 'guia-' . $dipt_slug ) === $dipt_g['url'] ) {
		$dipt_sin_rel = $dipt_fuera;
		continue;
	}
	if ( ! $dipt_fuera ) $dipt_relacionadas[] = array( dipt_url( 'guia-' . $dipt_slug ), $dipt_otra['titulo'] ?? '' );
}
if ( $dipt_sin_rel ) $dipt_relacionadas = array();
dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ), array( $dipt_g['titulo'], $dipt_g['url'] ) ) );
?>
<article class="gu">
	<header class="gu-cabeza">
		<div class="envoltura">
			<?php echo dipt_migas( $GLOBALS['dipt_migas'] ); // phpcs:ignore ?>
			<h1 class="t-h1"><?php echo esc_html( $dipt_g['titulo'] ); ?></h1>
			<p class="gu-cabeza__meta dato"><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?><span><?php echo esc_html( ( ! empty( $dipt_g['fecha'] ) ? $dipt_g['fecha'] . ' · ' : '' ) . sprintf( $c['lectura'] ?? '%d min', $minutos ) ); ?></span></p>
		</div>
	</header>
	<div class="envoltura gu-cuerpo">
		<?php if ( count( $indice ) > 1 ) : ?>
			<nav class="gu-indice" aria-label="<?php echo esc_attr( $c['indice'] ?? '' ); ?>">
				<p class="dato"><?php echo esc_html( $c['indice'] ?? '' ); ?></p>
				<ol><?php foreach ( $indice as $e ) : ?><li><a href="#<?php echo esc_attr( $e[0] ); ?>"><?php echo esc_html( $e[1] ); ?></a></li><?php endforeach; ?></ol>
			</nav>
		<?php endif; ?>
		<div class="prosa gu-texto"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contenido propio del tema o de la entrada (filtrado por the_content) ?></div>
	</div>
	<?php if ( ! empty( $c['enlaces'] ) ) : ?>
		<nav class="envoltura gu-seguir" aria-labelledby="gu-seguir-t">
			<p class="dato" id="gu-seguir-t"><?php echo esc_html( $c['seguir'] ?? '' ); ?></p>
			<ul>
				<?php foreach ( $c['enlaces'] as $e ) : ?>
					<li><a href="<?php echo esc_url( dipt_url( $e[0] ) ); ?>"><?php echo esc_html( $e[1] ); ?></a></li>
				<?php endforeach; ?>
				<?php foreach ( $dipt_relacionadas as $e ) : // las demás guías del mismo idioma, salvo las que llevan 'relacionadas' => false ?>
					<li><a href="<?php echo esc_url( $e[0] ); ?>"><?php echo esc_html( $e[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>
	<?php /* Tarjeta de compra a caballo entre la página y el pie (fondo partido: papel arriba, noche abajo) */ ?>
	<aside class="gu-compra">
		<div class="envoltura">
			<div class="gu-compra__in tono-marino" data-revela>
				<span class="gu-compra__ico" aria-hidden="true"><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?></span>
				<p class="gu-compra__t t-h2"><?php echo esc_html( $c['cta'] ?? '' ); ?></p>
				<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
			</div>
		</div>
	</aside>
</article>
