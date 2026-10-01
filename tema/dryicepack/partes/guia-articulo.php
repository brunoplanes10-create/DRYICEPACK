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
dipt_migas( array( array( dipt_t( 'inicio' ), dipt_url( 'inicio' ) ), array( $c['titulo_corto'] ?? 'Guías', dipt_url( 'guias' ) ), array( $dipt_g['titulo'], $dipt_g['url'] ) ) );
?>
<article class="gu">
	<header class="gu-cabeza">
		<div class="envoltura">
			<?php echo dipt_migas( $GLOBALS['dipt_migas'] ); // phpcs:ignore ?>
			<h1 class="t-h1"><?php echo esc_html( $dipt_g['titulo'] ); ?></h1>
			<p class="dato suave"><?php echo esc_html( ( ! empty( $dipt_g['fecha'] ) ? $dipt_g['fecha'] . ' · ' : '' ) . sprintf( $c['lectura'] ?? '%d min', $minutos ) ); ?></p>
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
				<?php foreach ( dipt_guias() as $slug => $rutas ) : // la otra guía del mismo idioma ?>
					<?php if ( empty( $rutas[ dipt_idioma() ] ) || dipt_url( 'guia-' . $slug ) === $dipt_g['url'] ) continue; ?>
					<li><a href="<?php echo esc_url( dipt_url( 'guia-' . $slug ) ); ?>"><?php echo esc_html( dipt_guia( $slug )['titulo'] ?? '' ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>
	<aside class="gu-compra tono-oscuro">
		<div class="envoltura gu-compra__in">
			<p class="t-h3"><?php echo esc_html( $c['cta'] ?? '' ); ?></p>
			<?php echo dipt_boton( dipt_t( 'comprar' ), dipt_url( 'producto' ), 'hielo' ); // phpcs:ignore ?>
		</div>
	</aside>
</article>
