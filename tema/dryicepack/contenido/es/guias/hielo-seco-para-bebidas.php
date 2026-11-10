<?php
/* Guía · Hielo seco para bebidas: cómo usarlo sin riesgo (ficha 6 de estrategia/SEO-CONTENIDOS.md). De tú.
 * Responde a «hielo seco para bebidas», «¿se puede consumir hielo seco en bebidas?», «hielo seco para tragos» y «bebidas con
 * hielo seco». Hostelería se queda «hielo seco para cócteles» y «hostelería» (de usted): esta guía enlaza allí.
 * Seguridad: ninguna receta con el hielo seco dentro de la bebida. Siempre doble recipiente.
 * Pieza propia: lámina de montajes (.gu-plano--fila en guia.css; bocetos zz-bocetos/guia-montajes-a|b|c.html, elegido el A).
 * Dibujos de .tmp/guias/svg.mjs: todos a la misma escala (250 de alto), el ancho de cada uno va en --w.
 * [PENDIENTE: foto real de un montaje con doble recipiente (la ponchera de Halloween puede servir) para la cabecera]
 */
return array(
	'slug'     => 'hielo-seco-para-bebidas',
	'foto'     => 'uso-hosteleria', // foto de la guía (assets/img/fotos): índice y cabecera
	'formato'  => '3mm', // formato que recomienda la guía: acceso directo en la nota al margen
	'titulo'   => 'Hielo seco para bebidas: cómo usarlo sin riesgo',
	'aviso'    => 3, // nota al margen: «No se come ni se bebe» (contenido/comun.php → seguridad)
	'extracto' => 'El hielo seco nunca va dentro de la copa. El efecto de niebla se hace con doble recipiente: tres montajes, qué necesitas, cuánto pedir y lo que no hay que hacer.',
	'seo'      => array(
		'titulo'      => 'Hielo seco para bebidas: cómo usarlo sin riesgo | DryIcePack',
		'descripcion' => 'Hielo seco para bebidas: nunca dentro de la copa, siempre con doble recipiente y pellets de 3 mm. Mira los 3 montajes y lo que no hay que hacer.',
	),
	'contenido' => '<p class="resumen"><strong>Respuesta corta:</strong> el hielo seco no se echa dentro de la bebida. No se come ni se bebe: un pellet en la copa quema por dentro. El efecto de niebla se hace con <strong>doble recipiente</strong>: el hielo seco, con un poco de agua tibia, en un recipiente de fuera; la bebida, en otro dentro. Hay niebla y nadie lo toca.</p>

<h2>¿Se puede echar hielo seco en una bebida?</h2>
<p>No. Aunque circulen vídeos y recetas con el pellet dentro de la copa, el hielo seco no se ingiere:</p>
<ul>
<li><strong>Quema por frío.</strong> Está a −78,5 °C: en la boca, en la garganta o en el estómago hace lo mismo que en la piel.</li>
<li><strong>Se convierte en gas.</strong> Cada kilo da unos 541 litros de CO<sub>2</sub> (ficha de PanGas, grupo Linde). Dentro del cuerpo, esa presión puede causar lesiones graves.</li>
<li><strong>Se queda en el fondo.</strong> Se hunde, porque es más denso que la bebida, y con la niebla no se ve: es fácil tragárselo en el último sorbo.</li>
</ul>
<p>Tampoco vale esperar a que «se deshaga»: desde fuera no se sabe si queda un trozo. La regla es sencilla: ni un pellet en lo que se bebe.</p>

<h2>¿Cómo se hace la niebla sin tocar la bebida?</h2>
<p>Con doble recipiente o con el hielo seco en un recipiente aparte. La niebla es el vapor de agua del aire que el frío condensa: sale aunque el hielo seco no toque la bebida. Tres montajes que funcionan:</p>
<figure class="gu-plano gu-plano--fila" data-pausa>
<ul class="gu-plano__fila" data-activa-grupo>
<li class="gu-plano__no" data-revela data-activa style="--w:150;--i:0"><svg class="pl-svg" viewBox="0 0 150 250" width="150" height="250" focusable="false" aria-hidden="true"><path class="pl-suelo" d="M4 236H146"/><g class="pl-dentro"><path class="pl-vidrio" d="M33 70H117L79 126V226L95 232V236H55V232L71 226V126Z"/><path class="pl-bebida" d="M43 77H107L77 112H73Z"/><g class="pl-pellets pl-pellets--mal"><circle cx="70" cy="104" r="3.2"/><circle cx="78.5" cy="104" r="3.2"/><circle cx="74.2" cy="97.5" r="3.2"/></g></g><path class="pl-aspa" d="M26 56L124 154M124 56L26 154"/></svg><p class="gu-plano__pie"><strong>Así no</strong>Ni un pellet dentro de la copa.</p></li>
<li data-revela data-activa style="--w:200;--i:1"><svg class="pl-svg" viewBox="0 0 200 250" width="200" height="250" focusable="false" aria-hidden="true"><path class="pl-suelo" d="M4 236H196"/><g class="pl-fuera"><path class="pl-agua" d="M46 204H154L152.5 228Q152 233 147 233H53Q48 233 47.5 228Z"/><g class="pl-pellets"><circle cx="52" cy="226" r="2.6"/><circle cx="62.2" cy="225.5" r="2.6"/><circle cx="69.8" cy="226.2" r="2.6"/><circle cx="129.1" cy="225.8" r="2.6"/><circle cx="137.5" cy="226.4" r="2.6"/><circle cx="147.5" cy="225.7" r="2.6"/><circle cx="56.6" cy="218.1" r="2.6"/><circle cx="64.6" cy="218.3" r="2.6"/><circle cx="74" cy="219.2" r="2.6"/><circle cx="133.6" cy="218.8" r="2.6"/><circle cx="142.2" cy="218.8" r="2.6"/></g><path class="pl-pared" d="M38 124L47 228Q48 236 56 236H144Q152 236 153 228L162 124"/></g><g class="pl-dentro"><path class="pl-vidrio" d="M58 66H142L104 122V222L120 228V232H80V228L96 222V122Z"/><path class="pl-bebida" d="M68 73H132L102 108H98Z"/></g><g class="pl-humo"><path style="--k:0" d="M36 124c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:1" d="M36 124c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:2" d="M36 124c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:3" d="M164 124c6 1 10 7 9 14s-4 10 1 18"/><path style="--k:4" d="M164 124c6 1 10 7 9 14s-4 10 1 18"/><path style="--k:5" d="M164 124c6 1 10 7 9 14s-4 10 1 18"/></g></svg><p class="gu-plano__pie"><span class="dato">01</span><strong>Copa en un bol</strong>Agua tibia y pellets en el bol de fuera.</p></li>
<li data-revela data-activa style="--w:300;--i:2"><svg class="pl-svg" viewBox="0 0 300 250" width="300" height="250" focusable="false" aria-hidden="true"><path class="pl-suelo" d="M4 236H296"/><g class="pl-fuera"><path class="pl-agua" d="M34 176H266L260.5 212Q257 230 232 230H68Q43 230 39.5 212Z"/><g class="pl-pellets"><circle cx="45.5" cy="223.1" r="2.6"/><circle cx="54.2" cy="224.1" r="2.6"/><circle cx="62.6" cy="224.7" r="2.6"/><circle cx="72.1" cy="224.2" r="2.6"/><circle cx="79.6" cy="223.4" r="2.6"/><circle cx="89" cy="223.3" r="2.6"/><circle cx="207.1" cy="223" r="2.6"/><circle cx="216.5" cy="224" r="2.6"/><circle cx="225.2" cy="223.4" r="2.6"/><circle cx="233" cy="224.3" r="2.6"/><circle cx="240.7" cy="223.6" r="2.6"/><circle cx="251" cy="223.5" r="2.6"/><circle cx="49.9" cy="216.8" r="2.6"/><circle cx="58" cy="216.3" r="2.6"/><circle cx="67.3" cy="217.8" r="2.6"/><circle cx="75.5" cy="217.9" r="2.6"/><circle cx="84.2" cy="216.7" r="2.6"/><circle cx="92.4" cy="217.3" r="2.6"/><circle cx="210.1" cy="217.4" r="2.6"/><circle cx="220.2" cy="216.3" r="2.6"/><circle cx="227" cy="216.7" r="2.6"/><circle cx="237.4" cy="217.6" r="2.6"/><circle cx="244.4" cy="217.6" r="2.6"/><circle cx="252.8" cy="216.8" r="2.6"/></g><path class="pl-pared" d="M24 112L40 214Q44 236 68 236H232Q256 236 260 214L276 112"/></g><g class="pl-dentro"><path class="pl-vidrio" d="M132 198L126 230H174L168 198Z"/><path class="pl-vidrio" d="M62 92Q64 202 150 202Q236 202 238 92Z"/><path class="pl-bebida" d="M70 104Q74 192 150 192Q226 192 230 104Z"/><path class="pl-cazo" d="M232 28L200 132"/><path class="pl-cazo pl-cazo--copa" d="M184 132Q186 146 199 146Q212 146 214 132Z"/></g><g class="pl-humo"><path style="--k:0" d="M22 112c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:1" d="M22 112c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:2" d="M22 112c-6 1 -10 7 -9 14s4 10 -1 18"/><path style="--k:3" d="M278 112c6 1 10 7 9 14s-4 10 1 18"/><path style="--k:4" d="M278 112c6 1 10 7 9 14s-4 10 1 18"/><path style="--k:5" d="M278 112c6 1 10 7 9 14s-4 10 1 18"/></g></svg><p class="gu-plano__pie"><span class="dato">02</span><strong>Ponchera en un cuenco</strong>Se sirve solo de la ponchera de dentro.</p></li>
<li data-revela data-activa style="--w:300;--i:3"><svg class="pl-svg" viewBox="0 0 300 250" width="300" height="250" focusable="false" aria-hidden="true"><path class="pl-mesa" d="M4 236H296"/><g class="pl-dentro"><path class="pl-vidrio" d="M22 150V232Q22 236 26 236H46Q50 236 50 232V150Z"/><path class="pl-bebida" d="M25 166H47V232H25Z"/><path class="pl-vidrio" d="M60 160H100L84 184V228L94 233V236H66V233L76 228V184Z"/><path class="pl-bebida" d="M67 165H93L81 179H79Z"/><path class="pl-vidrio" d="M200 160H240L224 184V228L234 233V236H206V233L216 228V184Z"/><path class="pl-bebida" d="M207 165H233L221 179H219Z"/><path class="pl-vidrio" d="M250 150V232Q250 236 254 236H274Q278 236 278 232V150Z"/><path class="pl-bebida" d="M253 166H275V232H253Z"/></g><g class="pl-fuera"><path class="pl-agua" d="M111 214H189L187 230Q186.5 233 183 233H117Q113.5 233 113 230Z"/><g class="pl-pellets"><circle cx="117.9" cy="227.5" r="2.6"/><circle cx="125.7" cy="227.8" r="2.6"/><circle cx="133.5" cy="228.9" r="2.6"/><circle cx="141.9" cy="227.9" r="2.6"/><circle cx="150.7" cy="227.9" r="2.6"/><circle cx="157.3" cy="229" r="2.6"/><circle cx="165.3" cy="228.6" r="2.6"/><circle cx="173.1" cy="227.9" r="2.6"/><circle cx="182.2" cy="228.4" r="2.6"/><circle cx="122.3" cy="220.5" r="2.6"/><circle cx="129.4" cy="221.2" r="2.6"/><circle cx="136.8" cy="221.8" r="2.6"/><circle cx="145.7" cy="221.9" r="2.6"/><circle cx="154.3" cy="221.8" r="2.6"/><circle cx="161.8" cy="221.2" r="2.6"/><circle cx="169.7" cy="220" r="2.6"/><circle cx="178.3" cy="220.6" r="2.6"/></g><path class="pl-pared" d="M106 200L111 230Q112 236 118 236H182Q188 236 189 230L194 200"/></g><g class="pl-humo"><path class="pl-rueda pl-rueda--izq" style="--k:0" d="M104 226c-12 -5 -24 3 -38 -1"/><path class="pl-rueda pl-rueda--der" style="--k:1" d="M196 221c12 -5 24 3 38 -1"/><path class="pl-rueda pl-rueda--izq" style="--k:2" d="M104 216c-12 -5 -24 3 -38 -1"/><path class="pl-rueda pl-rueda--der" style="--k:3" d="M196 226c12 -5 24 3 38 -1"/><path class="pl-rueda pl-rueda--izq" style="--k:4" d="M104 221c-12 -5 -24 3 -38 -1"/><path class="pl-rueda pl-rueda--der" style="--k:5" d="M196 216c12 -5 24 3 38 -1"/></g></svg><p class="gu-plano__pie"><span class="dato">03</span><strong>Bandeja entre las copas</strong>La niebla rueda por la mesa.</p></li>
</ul>
<figcaption class="gu-plano__leyenda">En azul marino, lo que se bebe. El hielo seco, en blanco, va siempre en un recipiente aparte, con agua tibia.</figcaption>
</figure>
<h3>Copa dentro de un bol</h3>
<p>La copa, con la bebida, dentro de un bol o una cubitera más ancha y más baja que ella. Mejor que no sea un vaso: el agua de fuera lleva hielo seco y nadie debe confundirla con una bebida. En el bol, un dedo de agua tibia y unos pellets de 3 mm. La niebla rebosa y cae por fuera. Para beber, se saca la copa.</p>
<h3>Ponchera dentro de un cuenco</h3>
<p>La ponchera, dentro de un cuenco o un caldero más grande. Entre las dos, agua tibia y pellets. La niebla sale por los bordes y el ponche se sirve con cazo, solo de la ponchera de dentro.</p>
<h3>Bandeja entre las copas</h3>
<p>Una bandeja honda con agua tibia y pellets, en el centro de la mesa o de la barra, y las copas alrededor, nunca dentro. La niebla rueda por la mesa y pasa entre ellas. Ponla donde nadie meta la mano.</p>

<h2>¿Qué necesitas y cuánto pedir?</h2>
<ul>
<li><strong>Pellets de 3 mm.</strong> Son pequeños y hacen niebla al momento.</li>
<li><strong>Dos recipientes que encajen:</strong> el de fuera, más ancho y sin tapa.</li>
<li><strong>Agua tibia</strong> para el recipiente de fuera.</li>
<li><strong>Guantes térmicos y unas pinzas.</strong></li>
</ul>
<table>
<thead><tr><th>Para</th><th>Cantidad orientativa</th></tr></thead>
<tbody>
<tr><td>Unas copas o una ponchera en una fiesta en casa</td><td>{pk} kg</td></tr>
<tr><td>Una fiesta grande, con niebla en varias tandas</td><td>10 kg</td></tr>
<tr><td>Un bar, una barra de evento o un servicio largo</td><td>15–20 kg</td></tr>
</tbody>
</table>
<p>Pídelo para el día de la fiesta o el anterior: el hielo seco se sublima con las horas' . ( file_exists( __DIR__ . '/cuanto-dura-el-hielo-seco.php' ) ? ' (lo explicamos en <a href="' . esc_url( home_url( '/guias/cuanto-dura-el-hielo-seco/' ) ) . '">cuánto dura el hielo seco</a>)' : '' ) . '. Si pides antes de las 12:00, de lunes a jueves, llega al día siguiente antes de las 14:00.</p>

<h2>¿Cómo se monta, paso a paso?</h2>
<ol>
<li><strong>Prepara el recipiente de fuera.</strong> Un dedo de agua tibia y, con guantes y pinzas, unos pocos pellets. Todavía sin la copa.</li>
<li><strong>Mete la copa o la ponchera, ya servida.</strong> Su borde tiene que quedar por encima del de fuera. La niebla sale al momento.</li>
<li><strong>Repón lejos de la bebida.</strong> Cuando la niebla baje, saca la copa antes de añadir pellets, y agua tibia si se ha enfriado. En la ponchera, échalos por el lado de fuera, pegados a la pared del cuenco. Nunca por encima de lo que se bebe.</li>
</ol>

<h2>¿Qué no hay que hacer nunca?</h2>
<ul>
<li>Echar un pellet en la copa, en la jarra o en la ponchera de la que se sirve.</li>
<li>Meterlo en una coctelera, una botella o un termo cerrados: la presión del gas puede reventarlos.</li>
<li>Decorar la copa con un trozo de hielo seco en el borde o encima.</li>
<li>Tocarlo con la mano: siempre guantes térmicos y pinzas.</li>
<li>Dejar la caja o los recipientes al alcance de niños, invitados o mascotas.</li>
<li>Usarlo en un sitio pequeño y cerrado: el CO<sub>2</sub> desplaza el oxígeno. Ventila.</li>
<li>Guardarlo en la nevera o en el congelador: va en su caja, con la tapa sin precintar.</li>
<li>Llevarlo en el habitáculo del coche: en el maletero y ventilando.</li>
</ul>
<p>Todas las normas y la ficha de datos de seguridad, en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco: riesgos y primeros auxilios</a>.</p>

<h2>¿Qué hago si alguien se lo traga o se quema?</h2>
<p><strong>Si se lo ha tragado, llama enseguida al 112</strong> o al Servicio de Información Toxicológica (91 562 04 20).</p>
<ul>
<li><strong>Si lo ha tocado con la mano:</strong> templa la zona con agua tibia, no caliente, y no frotes. Si salen ampollas o la piel cambia de color, al médico.</li>
<li><strong>Si se marea o le falta el aire:</strong> llévalo al aire libre y ventila. Si no mejora o pierde el conocimiento, llama al 112.</li>
</ul>

<h2>¿Tienes un bar o un restaurante?</h2>
<p>En barra, la regla es la misma: doble recipiente, y la caja de hielo seco detrás de la barra, nunca al alcance del público. Qué formato usar en sala y cómo pedir para el fin de semana, en <a href="' . esc_url( dipt_url( 'hosteleria' ) ) . '">hielo seco para cócteles y hostelería</a>.</p>
<p>Si lo usas cada semana, con una <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con pedido programado</a> lo repites por WhatsApp y pagas con una factura al mes. ¿Lo que buscas es niebla por el suelo? Mira <a href="' . esc_url( home_url( '/guias/como-hacer-niebla-con-hielo-seco/' ) ) . '">cómo hacer niebla con hielo seco</a> y <a href="' . esc_url( dipt_url( 'eventos' ) ) . '">hielo seco para fiestas y eventos</a>.</p>',
);
