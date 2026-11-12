<?php
/* Guía · ¿Cuánto dura el hielo seco? Y cómo guardarlo. Ficha 3 de estrategia/SEO-CONTENIDOS.md (10/11/2026).
 * Responde a «cuánto dura el hielo seco» (+ en una hielera, en un congelador, a temperatura ambiente…), «cómo conservar
 * hielo seco en casa», «dónde guardar hielo seco», «sublimación del hielo seco». De tú (todos los públicos).
 * Sin cifras propias: solo física con fuente y cuentas hechas con esos datos (comprobadas el 10/11/2026):
 * - PanGas (grupo Linde), ficha de los pellets: sublima a 194,65 K (−78,5 °C) a 1,013 bar; calor de sublimación 573 kJ/kg
 *   (el 571 kJ/kg que circula no sale en ninguna ficha de fabricante que se haya podido leer); 1 kg = 0,541 m³ de CO₂;
 *   densidad del gas frente al aire 1,528.
 * - Universidad de Oregón, Dry Ice Shipping Guide: 2,27-4,54 kg por cada 24 h de envío; rellenar el aire de la caja.
 * - Cuentas propias: 1 W durante 24 h = 86,4 kJ → 86,4 / 573 = 0,151 kg (unos 150 g); 2,27-4,54 kg/24 h ≈ 15-30 W;
 *   diferencia de temperatura con 20 °C fuera = 98,5 °C y con 35 °C = 113,5 °C (+15 %).
 * - La cifra de UPS (2,26-4,53 kg cada 24 h en EPS) no se ha podido leer (acceso denegado): fuera.
 * - Medición propia (el propietario, 10–11/11/2026): cajas de porexpán cerradas, dentro de la nave de Mataró, pellets de
 *   3 mm, 29 h, pesos CON la caja: pequeña 3,96 → 2,18 kg (−1,78); grande 16,34 → 12,24 kg (−4,10); 30 kg 32,88 → 27,16 kg
 *   (−5,72). Por 24 h, proporcional: 1,5 / 3,4 / 4,7 kg. La caja pequeña de ahora es más gruesa (pierde algo menos).
 *   [PENDIENTE: repetir en verano y con la caja de 5 kg cuando sustituya a la de 3 kg; pesar las cajas vacías.]
 * Esquema de la caja: pieza .gu-esquema (assets/css/paginas/guia.css), la misma que el triple embalaje de la guía de muestras.
 */
$dipt_gd_pin = static function ( $n, $x, $y ) {
	return '<g class="ge-pin" data-n="' . ( $n - 1 ) . '"><rect x="' . ( $x - 14 ) . '" y="' . ( $y - 14 ) . '" width="28" height="28" rx="3"/><text x="' . $x . '" y="' . ( $y + 1 ) . '">' . $n . '</text></g>';
};
$dipt_gd_nuggets = '';
foreach ( array( array( 12, 10, 15 ), array( 36, 9, -25 ), array( 54, 20, 70 ), array( 18, 30, -60 ), array( 40, 33, 10 ), array( 12, 44, 40 ), array( 54, 43, -15 ) ) as $dipt_gd_p ) {
	$dipt_gd_nuggets .= '<rect class="ge-pellet" x="-10" y="-6.5" width="20" height="13" rx="6.5" transform="translate(' . $dipt_gd_p[0] . ' ' . $dipt_gd_p[1] . ') rotate(' . $dipt_gd_p[2] . ')"/>';
}
// Flecha de calor ondulada que entra en la caja: $d es el trazo, $p la punta; --cx/--cy, hacia dónde avanza al moverse.
$dipt_gd_calor = static function ( $d, $p, $cx, $cy, $v ) {
	return '<g class="ge-calor" style="--cx:' . $cx . 'px;--cy:' . $cy . 'px;--v:' . $v . '"><path d="' . $d . '"/><path d="' . $p . '"/></g>';
};
// Corte de la caja de EPS (600 × 480, las mismas medidas que el triple embalaje): nuggets de 16 mm en el fondo, aire
// encima, tapa apoyada con la junta abierta, el calor entrando por paredes, base y tapa, y el CO₂ saliendo por la junta.
$dipt_gd_svg = '<svg class="gu-esquema__svg" viewBox="0 0 600 480" width="600" height="480" role="img" aria-labelledby="gd-t" focusable="false">'
	. '<title id="gd-t">Corte de una caja de EPS de 40 mm con nuggets de hielo seco en el fondo y aire encima. El calor entra por las paredes, la base y la tapa; el CO₂ sale por la junta de la tapa, que va apoyada sin precinto.</title>'
	. '<defs>'
	. '<pattern id="gd-eps" width="24" height="24" patternUnits="userSpaceOnUse"><rect class="ge-eps-fondo" width="24" height="24"/><g class="ge-bolas"><circle cx="5" cy="5" r="3.2"/><circle cx="15" cy="4" r="2.4"/><circle cx="20" cy="13" r="3.4"/><circle cx="8" cy="15" r="3"/><circle cx="4" cy="22" r="2.2"/><circle cx="15" cy="21" r="2.8"/></g></pattern>'
	. '<pattern id="gd-hielo" width="66" height="54" patternUnits="userSpaceOnUse"><rect class="ge-hielo-fondo" width="66" height="54"/>' . $dipt_gd_nuggets . '</pattern>'
	. '</defs>'
	// Aire de dentro (clave 4) y hielo seco (clave 5)
	// (los rótulos van fuera de las capas: no se atenúan y mantienen el contraste)
	. '<g data-entra style="--e:1"><g class="ge-capa" data-capa="3"><rect class="ge-aire" x="92" y="154" width="416" height="104"/></g>'
	. '<g class="ge-capa" data-capa="4"><path class="ge-linea" d="M92 398V256q26-12 52-4t52-2 52 2 52-4 52 2 52-2 52 4 52-2V398Z" fill="url(#gd-hielo)"/></g>'
	. '<text class="ge-rotulo" x="300" y="206" text-anchor="middle">aire</text><rect class="ge-placa" x="104" y="352" width="116" height="30" rx="2"/><text class="ge-cota" x="162" y="374" text-anchor="middle">−78,5 °C</text></g>'
	// Caja de EPS de 40 mm (clave 1) con su cota
	. '<g data-entra style="--e:0"><g class="ge-capa" data-capa="0"><path class="ge-linea" d="M60 150H92V398H508V150H540V430H60Z" fill="url(#gd-eps)"/></g><path class="ge-raya-fina" d="M508 440V464M540 440V464M508 454H540"/><text class="ge-cota" x="524" y="479" text-anchor="middle">40 mm</text></g>'
	// Tapa apoyada (clave 3) y el CO₂ que sale por la junta
	. '<g data-entra class="ge-baja" style="--e:2"><g class="ge-capa" data-capa="2"><path class="ge-linea" d="M60 106H540V140H504V154H96V140H60Z" fill="url(#gd-eps)"/><path class="ge-gas ge-gas--suave" d="M76 145C60 140 40 128 34 104V74M524 145C540 140 560 128 566 104V74M27 82l7-10 7 10M559 82l7-10 7 10"/></g></g>'
	. '<g data-entra style="--e:4"><path class="ge-voluta" style="--v:0" d="M64 132c-6-6 4-12-2-18"/><path class="ge-voluta" style="--v:1;--vx:-6px" d="M50 126c-6-6 4-12-2-18"/><path class="ge-voluta" style="--v:2" d="M536 132c6-6-4-12 2-18"/><path class="ge-voluta" style="--v:1.5;--vx:6px" d="M550 126c6-6-4-12 2-18"/>'
	. '<text class="ge-cota" x="34" y="60" text-anchor="middle">CO₂</text><text class="ge-cota" x="566" y="60" text-anchor="middle">CO₂</text></g>'
	// El calor entra por las seis caras (clave 2): paredes, base y tapa
	. '<g data-entra style="--e:3"><g class="ge-capa" data-capa="1">'
	. $dipt_gd_calor( 'M6 290q7-8 14 0t14 0 14 0', 'M42 283l8 7-8 7', 6, 0, 0 )
	. $dipt_gd_calor( 'M6 350q7-8 14 0t14 0 14 0', 'M42 343l8 7-8 7', 6, 0, 1.2 )
	. $dipt_gd_calor( 'M594 290q-7-8-14 0t-14 0-14 0', 'M558 283l-8 7 8 7', -6, 0, .6 )
	. $dipt_gd_calor( 'M594 350q-7-8-14 0t-14 0-14 0', 'M558 343l-8 7 8 7', -6, 0, 1.8 )
	. $dipt_gd_calor( 'M220 476q-8-7 0-14t0-14', 'M213 456l7-8 7 8', 0, -6, .3 )
	. $dipt_gd_calor( 'M380 476q-8-7 0-14t0-14', 'M373 456l7-8 7 8', 0, -6, 1.5 )
	. $dipt_gd_calor( 'M200 50q8 7 0 14t0 14 0 14', 'M193 86l7 8 7-8', 0, 6, .9 )
	. $dipt_gd_calor( 'M400 50q8 7 0 14t0 14 0 14', 'M393 86l7 8 7-8', 0, 6, 2.1 )
	. '</g></g>'
	. $dipt_gd_pin( 1, 76, 330 ) . $dipt_gd_pin( 2, 300, 462 ) . $dipt_gd_pin( 3, 300, 123 ) . $dipt_gd_pin( 4, 300, 232 ) . $dipt_gd_pin( 5, 300, 330 )
	. '</svg>';

return array(
	'slug'     => 'cuanto-dura-el-hielo-seco',
	'foto'     => 'caja-16mm-niebla', // foto de la guía (assets/img/fotos): índice y cabecera
	'formato'  => '16mm', // si tiene que durar: acceso directo en la nota al margen
	'titulo'   => '¿Cuánto dura el hielo seco? De qué depende y cómo guardarlo',
	'extracto' => 'El hielo seco no se derrite: se sublima. Qué hace que dure más o menos (la caja, los kilos, 3 o 16 mm, las aperturas, el calor de fuera) y cómo guardarlo sin riesgos.',
	'seo'      => array(
		'titulo'      => '¿Cuánto dura el hielo seco? Y cómo guardarlo | DryIcePack',
		'descripcion' => 'El hielo seco se sublima a −78,5 °C: en nuestra prueba, una caja cerrada perdió 4,1 kg en 29 horas. Mira de qué depende cuánto dura y cómo guardarlo.',
	),
	'contenido' => '<p class="resumen"><strong>Respuesta corta:</strong> el hielo seco no se derrite, se sublima: pasa de sólido a gas a −78,5 °C. Dura lo que tarda en llegarle el calor. En su caja de EPS cerrada se gasta despacio; al aire, mucho más rápido; en agua caliente, en forma de niebla. Lo hemos medido: en 29 horas, nuestra caja grande cerrada perdió 4,1&nbsp;kg y la pequeña 1,8&nbsp;kg. Por eso se pide para el día en que se va a usar.</p>

<h2>¿Por qué se gasta si no se derrite?</h2>
<p>A presión normal, el <a href="' . esc_url( dipt_url( 'que-es' ) ) . '">hielo seco, que es CO<sub>2</sub> sólido</a>, pasa directamente de sólido a gas a −78,5 °C (ficha de PanGas, grupo Linde). Todo lo que tiene alrededor está más caliente, también un congelador de casa, que trabaja a −18 °C. Ese calor le llega sin parar y lo va convirtiendo en gas.</p>
<ul>
<li>Para pasar 1&nbsp;kg de sólido a gas hacen falta 573&nbsp;kJ (PanGas).</li>
<li>Cada vatio de calor que le llega durante un día (86,4&nbsp;kJ) se lleva unos 150&nbsp;g de hielo seco.</li>
<li>Cada kilo se convierte en unos 541&nbsp;litros de CO<sub>2</sub> (PanGas). Por eso nunca va en un recipiente cerrado.</li>
</ul>
<p>La regla que sale de aquí: el hielo seco dura lo que tarda en entrarle el calor. Todo lo que frena el calor lo hace durar más.</p>

<h2>¿De qué depende que dure más o menos?</h2>
<p>Se ve en el corte de una caja:</p>
<figure class="gu-esquema" data-revela data-activa-grupo><div class="gu-esquema__lienzo" data-pausa>' . $dipt_gd_svg . '</div>
<ol class="gu-esquema__claves">
<li data-activa><strong>Paredes de EPS de 40&nbsp;mm.</strong> Frenan el calor: cuanto más gruesas y aislantes, menos entra.</li>
<li data-activa><strong>El calor entra por las seis caras.</strong> Cada vatio que entra durante un día se lleva unos 150&nbsp;g de hielo seco.</li>
<li data-activa><strong>Tapa sin precinto.</strong> El gas sale por la junta y la presión no sube.</li>
<li data-activa><strong>El aire de dentro.</strong> Cuanto más hueco queda, antes se gasta: rellénalo con papel arrugado.</li>
<li data-activa><strong>Los kilos.</strong> En la misma caja entra el mismo calor con 3 que con 20&nbsp;kg: con más hielo, dura más.</li>
</ol>
<figcaption>Corte de una caja de EPS con hielo seco. Datos: ficha de PanGas (grupo Linde) y Universidad de Oregón; las cuentas son nuestras.</figcaption></figure>
<ul>
<li><strong>La caja.</strong> Paredes gruesas y aislantes dejan pasar menos calor. Nuestras cajas son de EPS de 40 mm por lado y van incluidas en el pedido.</li>
<li><strong>Los kilos.</strong> El calor que entra por las paredes es el mismo haya poco o mucho hielo dentro: con más kilos, tarda más en gastarse.</li>
<li><strong>El formato.</strong> Un pellet de 3 mm es más de cinco veces más fino que un nugget de 16 mm: por cada kilo, deja mucha más superficie al calor. Al aire o en agua, los pellets se van antes. Dentro de la caja manda el calor que entra por las paredes, pero si tiene que durar, elige nuggets de 16 mm.</li>
<li><strong>Las aperturas.</strong> El CO<sub>2</sub> pesa una vez y media lo que el aire (densidad relativa de 1,528, según PanGas): al abrir, el gas frío cae fuera y entra aire templado. Abre lo justo y cierra enseguida.</li>
<li><strong>La temperatura de fuera.</strong> El calor entra en proporción a la diferencia de temperatura. Con 20&nbsp;°C fuera, la diferencia con el hielo seco es de 98,5&nbsp;°C; con 35&nbsp;°C, de 113,5&nbsp;°C: un 15&nbsp;% más de calor. Al sol, mucho más. Siempre a la sombra.</li>
<li><strong>El aire de dentro.</strong> Si sobra hueco, rellénalo con papel arrugado: la Universidad de Oregón lo recomienda para que se sublime más despacio.</li>
</ul>

<h2>¿Cuánto dura según dónde lo tengas?</h2>
<table class="gu-tabla-texto">
<thead><tr><th>Dónde</th><th>Qué pasa</th></tr></thead>
<tbody>
<tr><td>En agua caliente</td><td>Es donde menos dura: el agua le da calor por toda su superficie. Es la niebla.</td></tr>
<tr><td>Al aire, suelto</td><td>Se gasta rápido, y los pellets de 3 mm antes que los nuggets de 16 mm.</td></tr>
<tr><td>En una nevera portátil</td><td>Sirve si no cierra hermética. Si tiene junta de goma y cierres a presión, no la cierres del todo.</td></tr>
<tr><td>En su caja de EPS, cerrada</td><td>Es donde más dura: 40 mm de aislante por lado, y la tapa deja salir el gas.</td></tr>
<tr><td>En la nevera o el congelador de casa</td><td>Nunca. A −18 °C sigue sublimándose, el gas se acumula dentro y puede dañar el aparato.</td></tr>
</tbody>
</table>

<h2>¿Cuánto pierde una caja en un día? Lo hemos medido</h2>
<p>Pesamos tres de nuestras cajas de porexpán al cerrarlas y otra vez 29 horas después, sin abrirlas, dentro de nuestra nave de Mataró y con pellets de 3&nbsp;mm (noviembre de 2026). Los pesos incluyen la caja:</p>
<p class="sello-medido"><span class="sello-medido__marca">Medido por DryIcePack</span> 10–11/11/2026 · nave de Mataró · pellets de 3&nbsp;mm · cajas cerradas · 29&nbsp;h</p>
<table>
<thead><tr><th>Caja</th><th>Al cerrar</th><th>A las 29&nbsp;h</th><th>Perdió</th><th>Unos por cada 24&nbsp;h</th></tr></thead>
<tbody>
<tr><td>Pequeña (pack de 3&nbsp;kg)</td><td>3,96&nbsp;kg</td><td>2,18&nbsp;kg</td><td>1,78&nbsp;kg</td><td>1,5&nbsp;kg</td></tr>
<tr><td>Grande (packs de 15 y 20&nbsp;kg)</td><td>16,34&nbsp;kg</td><td>12,24&nbsp;kg</td><td>4,10&nbsp;kg</td><td>3,4&nbsp;kg</td></tr>
<tr><td>De 30&nbsp;kg (empresas)</td><td>32,88&nbsp;kg</td><td>27,16&nbsp;kg</td><td>5,72&nbsp;kg</td><td>4,7&nbsp;kg</td></tr>
</tbody>
</table>
<p>Tres cosas que se ven en la tabla:</p>
<ul>
<li><strong>La caja grande pierde más kilos, pero una parte menor de lo que lleva.</strong> Por eso los packs pequeños son los que más notan el viaje: de un pack de 3&nbsp;kg llegan unos 2&nbsp;kg.</li>
<li><strong>Es una medida de invierno, a la sombra y sin abrir.</strong> Con calor, al sol o abriendo la caja, se gasta antes. Los nuggets de 16&nbsp;mm aguantan algo más que los pellets de 3&nbsp;mm.</li>
<li><strong>Cuadra con la pauta publicada:</strong> la Universidad de Oregón recomienda contar de 2,27 a 4,54&nbsp;kg por cada 24&nbsp;h de envío en una caja aislante. Nuestras cajas grandes están en esa franja o un poco por encima. Con la regla de antes, es como tener entre 15 y 30&nbsp;W de calor entrando sin parar.</li>
</ul>
<p>Si lo necesitas justo para un día, pide el pack siguiente. Si lo necesitas para varios días, escríbenos por <a href="' . esc_url( dipt_url( 'contacto' ) ) . '">teléfono, WhatsApp o email</a> con el día de uso y te decimos qué pack pedir. Si lo que envías son congelados, mira <a href="' . esc_url( home_url( '/guias/enviar-congelados-con-hielo-seco/' ) ) . '">cómo enviar congelados con hielo seco: caja, kilos y marca</a>; si son muestras, <a href="' . esc_url( home_url( '/guias/enviar-muestras-con-hielo-seco/' ) ) . '">enviar muestras con hielo seco: triple embalaje y etiquetas</a>.</p>

<h2>¿Cómo se guarda el hielo seco en casa?</h2>
<ul>
<li><strong>En su caja de EPS,</strong> con la tapa puesta pero sin precinto ni cinta en todas las juntas.</li>
<li><strong>En un sitio fresco, a la sombra y ventilado,</strong> donde no duerma nadie.</li>
<li><strong>Nunca en un espacio cerrado sin ventilación:</strong> ni en un armario, ni en un baño sin ventana, ni en un coche cerrado. El CO<sub>2</sub> desplaza el oxígeno.</li>
<li><strong>Nunca en un recipiente hermético:</strong> un tarro, una botella o una tartera pueden reventar con la presión del gas.</li>
<li><strong>Nunca en la nevera ni en el congelador de casa.</strong></li>
<li><strong>Fuera del alcance de niños y mascotas.</strong></li>
<li><strong>Siempre con guantes térmicos y pinzas,</strong> nunca con la mano: a −78,5 °C quema la piel.</li>
<li><strong>No se come ni se bebe.</strong> En bebidas, siempre con doble recipiente: nunca un pellet dentro de la copa.</li>
<li><strong>En el coche, en el maletero y ventilando.</strong> Nunca en el habitáculo.</li>
</ul>
<p>Todas las normas y la ficha de datos de seguridad están en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco: cómo manipularlo</a>.</p>

<h2>¿Qué hago con el que sobra?</h2>
<p>Déjalo sublimar al aire libre o en un sitio ventilado, fuera del alcance de niños y mascotas. Nunca al fregadero, al váter ni a una papelera cerrada.</p>

<h2>¿Cuándo lo pido para que me dure lo justo?</h2>
<p>Para el día en que lo vas a usar o el anterior, no antes: pagarías kilos que se van a sublimar.</p>
<ul>
<li>Si pides antes de las 12:00, llega al día siguiente antes de las 14:00, de martes a viernes. El sábado, según zona, antes de las 13:00 y con precio de sábado: lo ves al elegir el día.</li>
<li>Puedes reservarlo para cualquier día de los próximos 90.</li>
<li>O recógelo en nuestra nave de Mataró, de lunes a sábado: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}, IVA incluido. El sábado, +{sabado_recogida}. Se paga en efectivo y con el importe exacto.</li>
</ul>
<p>Elige formato y kilos en <a href="' . esc_url( dipt_url( 'producto' ) ) . '">comprar hielo seco en pellets de 3 mm o nuggets de 16 mm</a>. Los días de reparto y la recogida, en <a href="' . esc_url( dipt_url( 'envios' ) ) . '">envíos, plazos y recogida en Mataró</a>, y los kilos para una fiesta, en <a href="' . esc_url( dipt_url( 'eventos' ) ) . '">hielo seco para fiestas: cuánto pedir</a>.</p>

<h2>¿De dónde salen los datos?</h2>
<ul>
<li><a href="https://static.prd.echannel.linde.com/wcsstore/CH_REC_Industrial_Ntl_Store/datasheets/fr_CH/pangas_ps_glace-carbonique-en-pellets-3mm_f.pdf" rel="noopener">PanGas (grupo Linde), ficha técnica de los pellets de hielo seco</a>: sublima a −78,5 °C a 1,013 bar, necesita 573 kJ por kilo, cada kilo da 0,541 m³ de gas y el gas pesa 1,528 veces lo que el aire.</li>
<li><a href="https://safety.uoregon.edu/sites/default/files/dry_ice_shipping_guide.pdf" rel="noopener">Universidad de Oregón, Dry Ice Shipping Guide</a>: kilos por cada 24 h de envío y relleno de los huecos.</li>
<li>Las cuentas (150 g por vatio y día, de 15 a 30 W, un 15 % más de calor con 35 °C) son nuestras, hechas con esos datos.</li>
<li>Las pesadas de la tabla son nuestras: tres cajas de porexpán cerradas, en la nave de Mataró, en noviembre de 2026. Las repetiremos en verano.</li>
</ul>',
);
