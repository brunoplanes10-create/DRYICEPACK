<?php
/**
 * Cierre común (lote 0 · plan 1.2): la última sección antes del pie marino.
 * Fondo noche plano, foto distinta en cada página abajo a la derecha, niebla que sube, titular grande,
 * fecha real de entrega (data-corte), botón cian, enlace secundario y enlace a seguridad.
 *
 * Uso desde una plantilla:
 *   dipt_cierre( array( 'titulo' => $c['final']['titulo'], 'foto' => 'caja-3mm' ) );
 * o bien get_template_part( 'partes/cierre', null, array( … ) ).
 *
 * Parámetros (todos opcionales salvo 'titulo'):
 *   titulo       Texto del H2. Admite "|" (salto de línea) y <em>. Sin titular no se pinta nada.
 *   id           id del H2 (aria-labelledby). Por defecto 'cierre-t'.
 *   etiqueta     'h2' (por defecto).
 *   entrada      'lineas' (por defecto) · 'cortina' · 'revela' · 'ninguna' (ver dipt_titulo()).
 *   texto        Frase corta bajo el titular (ya revisada en contenido/). Admite <em> y <a>.
 *   foto         Nombre de la foto en assets/img/fotos/fotos.json. Sin foto, el titular ocupa todo el ancho.
 *   foto_alt     Texto alternativo ('' si es decorativa, lo normal aquí).
 *   recorte      object-position de la foto, p. ej. '50% 40%'.
 *   boton        Texto del botón cian. Por defecto «Comprar hielo seco» (dipt_t('comprar')).
 *   boton_url    Destino del botón. Por defecto la ficha del producto.
 *   boton_variante 'hielo' (cian, compra; por defecto) · 'blanco' · 'marino' (si no es compra).
 *   secundario   'telefono' (por defecto: teléfono grande) · 'whatsapp' · array( texto, url ) · false.
 *   corte        true (por defecto) pinta «Pide antes de las 12:00 y lo tienes el …» con la fecha real.
 *   corte_textos array( antes, quedan, despues ) con %s, para usar otros textos (p. ej. de usted).
 *   seguridad    true (por defecto): enlace «Seguridad completa y ficha de datos». Solo se quita si la
 *                página ya enlaza a /seguridad-del-hielo-seco/ con los avisos completos más arriba.
 *   clase        Clases extra para la sección.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$dipt_a = wp_parse_args( isset( $args ) && is_array( $args ) ? $args : array(), array(
	'titulo'         => '',
	'id'             => 'cierre-t',
	'etiqueta'       => 'h2',
	'entrada'        => 'lineas',
	'texto'          => '',
	'foto'           => '',
	'foto_alt'       => '',
	'recorte'        => '',
	'boton'          => dipt_t( 'comprar' ),
	'boton_url'      => dipt_url( 'producto' ),
	'boton_variante' => 'hielo',
	'secundario'     => 'telefono',
	'corte'          => true,
	'corte_textos'   => array( dipt_t( 'corte_antes' ), dipt_t( 'corte_quedan' ), dipt_t( 'corte_despues' ) ),
	'seguridad'      => true,
	'clase'          => '',
) );
if ( '' === trim( (string) $dipt_a['titulo'] ) ) return;

$dipt_con      = dipt_contacto();
$dipt_con_foto = $dipt_a['foto'] && dipt_foto_url( $dipt_a['foto'] );
$dipt_clases   = trim( 'cierre tono-oscuro ' . ( $dipt_con_foto ? 'cierre--foto ' : 'cierre--solo ' ) . $dipt_a['clase'] );
?>
<section class="<?php echo esc_attr( $dipt_clases ); ?>" aria-labelledby="<?php echo esc_attr( $dipt_a['id'] ); ?>" data-cierre>
	<div class="niebla niebla--sube" aria-hidden="true"><i></i><i></i><i></i></div>
	<?php if ( $dipt_con_foto ) : ?>
		<div class="cierre__foto foto--sangre" data-cortina>
			<?php echo dipt_foto( $dipt_a['foto'], $dipt_a['foto_alt'], array( 'sizes' => '(max-width: 900px) 78vw, 46vw', 'recorte' => $dipt_a['recorte'] ) ); // phpcs:ignore ?>
		</div>
	<?php endif; ?>
	<div class="envoltura cierre__in">
		<?php echo dipt_titulo( $dipt_a['etiqueta'], $dipt_a['titulo'], 't-gigante cierre__titulo', $dipt_a['id'], $dipt_a['entrada'] ); // phpcs:ignore ?>
		<?php if ( $dipt_a['texto'] ) : ?>
			<p class="entrada cierre__texto" data-revela><?php dipt_html( $dipt_a['texto'] ); ?></p>
		<?php endif; ?>
		<?php if ( $dipt_a['corte'] ) :
			list( $dipt_t_antes, $dipt_t_quedan, $dipt_t_despues ) = array_pad( (array) $dipt_a['corte_textos'], 3, '%s' );
			$dipt_e = dipt_primera_entrega();
			?>
			<p class="cierre__corte dato" data-corte data-t-antes="<?php echo esc_attr( $dipt_t_antes ); ?>" data-t-quedan="<?php echo esc_attr( $dipt_t_quedan ); ?>" data-t-despues="<?php echo esc_attr( $dipt_t_despues ); ?>">
				<span class="cierre__reloj" aria-hidden="true"><?php echo dipt_icono( 'reloj' ); // phpcs:ignore ?></span>
				<span data-corte-texto><?php echo esc_html( sprintf( $dipt_t_despues, $dipt_e ? dipt_fecha_larga( $dipt_e['fecha'] ) : '' ) ); ?></span>
				<span class="cierre__quedan" data-corte-quedan hidden></span>
			</p>
		<?php endif; ?>
		<div class="cierre__acciones">
			<?php echo dipt_boton( $dipt_a['boton'], $dipt_a['boton_url'], $dipt_a['boton_variante'] ); // phpcs:ignore ?>
			<?php if ( 'telefono' === $dipt_a['secundario'] ) : ?>
				<a class="tel-grande cierre__tel" href="<?php echo esc_attr( $dipt_con['telefono_href'] ); ?>"><?php echo dipt_icono( 'telefono' ); // phpcs:ignore ?><?php echo esc_html( $dipt_con['telefono'] ); ?></a>
			<?php elseif ( 'whatsapp' === $dipt_a['secundario'] ) : ?>
				<a class="tel-grande cierre__tel" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo dipt_icono( 'whatsapp' ); // phpcs:ignore ?>WhatsApp <?php echo esc_html( $dipt_con['whatsapp'] ); ?></a>
			<?php elseif ( is_array( $dipt_a['secundario'] ) && count( $dipt_a['secundario'] ) >= 2 ) : ?>
				<?php echo dipt_enlace( $dipt_a['secundario'][0], $dipt_a['secundario'][1] ); // phpcs:ignore ?>
			<?php endif; ?>
		</div>
		<?php if ( $dipt_a['seguridad'] ) : ?>
			<p class="cierre__seguridad"><?php echo dipt_icono( 'guante' ); // phpcs:ignore ?><a href="<?php echo esc_url( dipt_url( 'seguridad' ) ); ?>"><?php echo esc_html( dipt_t( 'seguridad_mas' ) ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
