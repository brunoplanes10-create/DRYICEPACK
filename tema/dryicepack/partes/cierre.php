<?php
/**
 * Cierre común (15/10/2026, boceto v2-cierre-1 «Tres caminos en tipografía»): la última sección antes del pie marino.
 * Escarcha (corta con el pie), el titular de cada página en t-hero y tres caminos como líneas de texto grandes entre
 * filetes de borde a borde. Nada de tarjetas: la línea entera es el enlace.
 *   1. «Comprar hielo seco»: círculo cian y la fecha real de entrega (data-corte, movimiento.js; la fecha en Geist Mono).
 *      La página puede cambiarlo por otro pedido (Industria: «Pedir presupuesto» → #presupuesto).
 *   2. «Hielo seco para empresas» → /empresas/: pedidos programados y una factura al mes, a 30 días.
 *   3. «Hablar con una persona»: el teléfono en grande, WhatsApp y el horario.
 * Debajo, el enlace obligatorio a seguridad. Estilos en base.css; .activo al cruzar el centro (táctil) y .visto al
 * entrar, con [data-activa] (movimiento.js).
 *
 * Uso desde una plantilla:
 *   dipt_cierre( array( 'titulo' => $c['final']['titulo'] ) );
 * o bien get_template_part( 'partes/cierre', null, array( … ) ).
 *
 * Parámetros (todos opcionales salvo 'titulo'):
 *   titulo         Texto del H2. Admite "|" (salto de línea) y <em>. Sin titular no se pinta nada.
 *   id             id del H2 (aria-labelledby). Por defecto 'cierre-t'.
 *   etiqueta       'h2' (por defecto).
 *   entrada        'lineas' (por defecto) · 'cortina' · 'revela' · 'ninguna' (ver dipt_titulo()).
 *   texto          Frase corta bajo el titular (ya revisada en contenido/). Admite <em> y <a>.
 *   usted          true en las páginas B2B: la fecha de entrega va de usted («Si pide ahora, lo tiene el …»).
 *   boton          Nombre del primer camino. Por defecto «Comprar hielo seco» (dipt_t('comprar')).
 *   boton_url      Destino del primer camino. Por defecto la ficha del producto (en la portada, '#elegir').
 *   boton_variante 'hielo' (por defecto: círculo cian, es compra o pedido) · cualquier otro: flecha de línea.
 *   nota           Línea bajo el primer camino cuando no lleva fecha (corte false), p. ej. el plazo del presupuesto.
 *   corte          true (por defecto): bajo el primer camino, la fecha real de entrega.
 *   corte_textos   array( antes, quedan, despues ) con %s para usar otros textos de la fecha (aquí no se usa «quedan»).
 *   secundario     array( texto, url ): enlace extra junto al de seguridad. 'telefono' y 'whatsapp' (los de antes)
 *                  ya están en el tercer camino: se aceptan y se ignoran, como false.
 *   seguridad      true (por defecto): enlace «Seguridad completa y ficha de datos». Solo se quita si la
 *                  página ya enlaza a /seguridad-del-hielo-seco/ con los avisos completos más arriba.
 *   clase          Clases extra para la sección.
 *   foto, foto_alt, recorte  Del cierre anterior (noche con foto): se aceptan y se ignoran.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$dipt_a = wp_parse_args( isset( $args ) && is_array( $args ) ? $args : array(), array(
	'titulo'         => '',
	'id'             => 'cierre-t',
	'etiqueta'       => 'h2',
	'entrada'        => 'lineas',
	'texto'          => '',
	'usted'          => false,
	'boton'          => dipt_t( 'comprar' ),
	'boton_url'      => dipt_url( 'producto' ),
	'boton_variante' => 'hielo',
	'nota'           => '',
	'corte'          => true,
	'corte_textos'   => null,
	'secundario'     => false,
	'seguridad'      => true,
	'clase'          => '',
) );
if ( '' === trim( (string) $dipt_a['titulo'] ) ) return;

$dipt_con = dipt_contacto();
$dipt_id  = (string) $dipt_a['id'];
$dipt_fle = dipt_icono( 'flecha' );

// Fecha real de entrega: el servidor pinta la frase que toca ahora (antes o después del corte de las 12:00) y
// movimiento.js la mantiene al día (la página puede venir de la caché). Solo la fecha va en mono ([data-corte-fecha]).
$dipt_corte = null;
if ( $dipt_a['corte'] ) {
	$dipt_tx = is_array( $dipt_a['corte_textos'] ) ? array_values( $dipt_a['corte_textos'] ) : ( $dipt_a['usted']
		? array( dipt_t( 'corte_antes_usted' ), '', dipt_t( 'corte_despues_usted' ) )
		: array( dipt_t( 'corte_antes' ), '', dipt_t( 'corte_despues' ) ) );
	$dipt_tx    = array_pad( $dipt_tx, 3, '%s' );
	$dipt_e     = dipt_primera_entrega();
	$dipt_hoy   = ( new DateTimeImmutable( 'now', new DateTimeZone( 'Europe/Madrid' ) ) )->format( 'Y-m-d' );
	$dipt_frase = (string) ( $dipt_e && ( $dipt_e['sale'] ?? '' ) === $dipt_hoy ? $dipt_tx[0] : $dipt_tx[2] );
	$dipt_corte = array(
		'antes'   => (string) $dipt_tx[0],
		'despues' => (string) $dipt_tx[2],
		'partes'  => array_pad( explode( '%s', $dipt_frase, 2 ), 2, '' ),
		'fecha'   => $dipt_e ? dipt_fecha_larga( $dipt_e['fecha'] ) : '',
	);
}
$dipt_nota_1 = $dipt_corte || '' !== (string) $dipt_a['nota'];
$dipt_sec    = is_array( $dipt_a['secundario'] ) && count( $dipt_a['secundario'] ) >= 2 ? array_values( $dipt_a['secundario'] ) : null;
?>
<section class="<?php echo esc_attr( trim( 'cierre tono-escarcha ' . $dipt_a['clase'] ) ); ?>" aria-labelledby="<?php echo esc_attr( $dipt_id ); ?>" data-cierre>
	<div class="envoltura">
		<?php echo dipt_titulo( $dipt_a['etiqueta'], $dipt_a['titulo'], 't-hero cierre__titulo', $dipt_id, $dipt_a['entrada'] ); // phpcs:ignore ?>
		<?php if ( $dipt_a['texto'] ) : ?>
			<p class="cierre__texto" data-revela><?php dipt_html( $dipt_a['texto'] ); ?></p>
		<?php endif; ?>
	</div>
	<ul class="cierre__caminos" data-activa-grupo>
		<li class="camino camino--compra" data-activa style="--i:0">
			<span class="camino__trazo" aria-hidden="true"></span>
			<div class="envoltura camino__in">
				<a class="camino__enlace" href="<?php echo esc_url( $dipt_a['boton_url'] ); ?>"<?php echo $dipt_nota_1 ? ' aria-describedby="' . esc_attr( $dipt_id . '-compra' ) . '"' : ''; ?>><span class="camino__nombre"><?php echo esc_html( $dipt_a['boton'] ); ?></span></a>
				<?php if ( $dipt_corte ) : ?>
					<p class="camino__nota" id="<?php echo esc_attr( $dipt_id . '-compra' ); ?>" data-corte data-t-antes="<?php echo esc_attr( $dipt_corte['antes'] ); ?>" data-t-despues="<?php echo esc_attr( $dipt_corte['despues'] ); ?>"><span data-corte-texto><?php echo esc_html( $dipt_corte['partes'][0] ); ?><span class="camino__fecha" data-corte-fecha><?php echo esc_html( $dipt_corte['fecha'] ); ?></span><?php echo esc_html( $dipt_corte['partes'][1] ); ?></span>.</p>
				<?php elseif ( $dipt_nota_1 ) : ?>
					<p class="camino__nota" id="<?php echo esc_attr( $dipt_id . '-compra' ); ?>"><?php echo esc_html( $dipt_a['nota'] ); ?></p>
				<?php endif; ?>
				<span class="camino__accion <?php echo 'hielo' === $dipt_a['boton_variante'] ? 'camino__circulo' : 'camino__flecha'; ?>" aria-hidden="true"><?php echo $dipt_fle; // phpcs:ignore ?></span>
			</div>
		</li>
		<li class="camino camino--empresas" data-activa style="--i:1">
			<span class="camino__trazo" aria-hidden="true"></span>
			<div class="envoltura camino__in">
				<a class="camino__enlace" href="<?php echo esc_url( dipt_url( 'empresas' ) ); ?>" aria-describedby="<?php echo esc_attr( $dipt_id . '-empresas' ); ?>"><span class="camino__nombre"><?php echo esc_html( dipt_t( 'cierre_empresas' ) ); ?></span></a>
				<p class="camino__nota" id="<?php echo esc_attr( $dipt_id . '-empresas' ); ?>"><?php echo esc_html( dipt_t( 'cierre_empresas_nota' ) ); ?></p>
				<span class="camino__accion camino__flecha" aria-hidden="true"><?php echo $dipt_fle; // phpcs:ignore ?></span>
			</div>
		</li>
		<li class="camino camino--persona" data-activa style="--i:2">
			<span class="camino__trazo" aria-hidden="true"></span>
			<div class="envoltura camino__in">
				<p class="camino__nombre"><?php echo esc_html( dipt_t( 'cierre_persona' ) ); ?></p>
				<a class="camino__enlace camino__tel" href="<?php echo esc_attr( $dipt_con['telefono_href'] ); ?>" aria-label="<?php echo esc_attr( sprintf( dipt_t( 'cierre_llamar' ), $dipt_con['telefono'] ) ); ?>"><?php echo esc_html( $dipt_con['telefono'] ); ?></a>
				<p class="camino__nota"><a class="camino__wa" href="<?php echo esc_url( dipt_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( dipt_t( 'wa_etiqueta' ) . ' ' . $dipt_con['whatsapp'] ); ?></a><span class="camino__sep" aria-hidden="true"> · </span><span class="camino__horario"><?php echo esc_html( dipt_t( 'horario' ) ); ?></span></p>
			</div>
		</li>
	</ul>
	<?php if ( $dipt_a['seguridad'] || $dipt_sec ) : ?>
		<div class="envoltura cierre__pie" data-revela style="--i:3">
			<?php if ( $dipt_a['seguridad'] ) : ?>
				<p class="cierre__seguridad"><?php echo dipt_icono( 'guante' ); // phpcs:ignore ?><a href="<?php echo esc_url( dipt_url( 'seguridad' ) ); ?>"><?php echo esc_html( dipt_t( 'seguridad_mas' ) ); ?></a></p>
			<?php endif; ?>
			<?php if ( $dipt_sec ) : ?>
				<p><?php echo dipt_enlace( $dipt_sec[0], $dipt_sec[1] ); // phpcs:ignore ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
