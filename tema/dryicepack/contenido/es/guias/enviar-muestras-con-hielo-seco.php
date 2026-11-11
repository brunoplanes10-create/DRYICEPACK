<?php
/* Guía · Enviar muestras con hielo seco (UN 3373 y UN 1845). Ficha 7 de estrategia/SEO-CONTENIDOS.md (10/11/2026).
 * Responde a «enviar muestras con hielo seco», «hielo seco para transporte de muestras», «UN 3373 etiqueta / embalaje»,
 * «etiqueta hielo seco UN 1845». La compra («hielo seco PARA transporte de muestras») es de /hielo-seco-laboratorios/.
 * B2B, de usted, como las páginas de laboratorio. Cada requisito cita su norma; todas comprobadas el 10/11/2026:
 * - ADR 2025 en castellano, BOE-A-2025-77 (3/01/2025): 2.2.62.1.4, 2.2.62.1.5.8, tabla A (UN 1845), P650 y 5.5.3.
 * - IATA, instrucción de embalaje 650, 67.ª edición (1/01/2026), PDF público en castellano de iata.org.
 * - OMS, Guidance on regulations for the transport of infectious substances 2025-2026 (apartados 4.6.1, 6.2, 7.1,
 *   tabla 3 y tabla 4; anexo 4.5 con la instrucción 954 de OACI para el hielo seco: 200 kg por bulto y kg netos).
 * - Universidad de Oregón, Dry Ice Shipping Guide: 2,27-4,54 kg por cada 24 h y rellenar el aire de la caja.
 * - PanGas (grupo Linde), ficha de los pellets: 1 kg de hielo seco = 0,541 m³ de CO₂.
 * El dato de UPS (2,26-4,53 kg cada 24 h) no se ha podido leer (acceso denegado): fuera.
 * [PENDIENTE: ¿se venden cajas o kits para muestras? Si sí, cambiar «¿Qué hace DryIcePack y qué no?».]
 * [PENDIENTE: requisitos de MRW para bultos con hielo seco (marcas, límites).]
 * [PENDIENTE: ¿Semillas Fitó permite contar su caso con más detalle?]
 * [PENDIENTE: ¿hay consejero de seguridad ADR que revise esta guía antes de publicarla?]
 * Esquema del triple embalaje: pieza .gu-esquema (assets/css/paginas/guia.css), dirección A de los bocetos
 * zz-bocetos/guia-triple-a|b|c.html. Solo transform y opacity; el dibujo es SVG propio, sin imágenes.
 */
$dipt_gm_pin = static function ( $n, $x, $y ) {
	return '<g class="ge-pin" data-n="' . ( $n - 1 ) . '"><rect x="' . ( $x - 14 ) . '" y="' . ( $y - 14 ) . '" width="28" height="28" rx="3"/><text x="' . $x . '" y="' . ( $y + 1 ) . '">' . $n . '</text></g>';
};
$dipt_gm_pellets = '';
foreach ( array( array( 6, 6, 18 ), array( 20, 5, -30 ), array( 33, 9, 64 ), array( 9, 19, -58 ), array( 24, 18, 12 ), array( 36, 26, -14 ), array( 14, 29, 40 ) ) as $dipt_gm_p ) {
	$dipt_gm_pellets .= '<rect class="ge-pellet" x="-5.5" y="-3" width="11" height="6" rx="3" transform="translate(' . $dipt_gm_p[0] . ' ' . $dipt_gm_p[1] . ') rotate(' . $dipt_gm_p[2] . ')"/>';
}
// Corte de un bulto UN 3373 (600 × 480): caja de EPS con tapa apoyada (junta de 10 a cada lado), hielo seco en pellets
// alrededor del secundario, soportes, secundario rígido con tapa de rosca, absorbente y criotubo. Las piezas entran en el
// orden de montaje (data-entra) y cada clave enciende su capa (data-capa = índice de la clave).
$dipt_gm_svg = '<svg class="gu-esquema__svg" viewBox="0 0 600 480" width="600" height="480" role="img" aria-labelledby="gm-t" focusable="false">'
	. '<title id="gm-t">Corte de un bulto de muestras con hielo seco. Dentro del todo, el tubo con la muestra, envuelto en absorbente y metido en un embalaje secundario cerrado con tapa de rosca. Alrededor del secundario, el hielo seco, con soportes que lo sujetan. Por fuera, la caja de EPS, con la tapa apoyada sin precinto para que el CO₂ salga por la junta.</title>'
	. '<defs>'
	. '<pattern id="gm-eps" width="24" height="24" patternUnits="userSpaceOnUse"><rect class="ge-eps-fondo" width="24" height="24"/><g class="ge-bolas"><circle cx="5" cy="5" r="3.2"/><circle cx="15" cy="4" r="2.4"/><circle cx="20" cy="13" r="3.4"/><circle cx="8" cy="15" r="3"/><circle cx="4" cy="22" r="2.2"/><circle cx="15" cy="21" r="2.8"/></g></pattern>'
	. '<pattern id="gm-hielo" width="42" height="34" patternUnits="userSpaceOnUse"><rect class="ge-hielo-fondo" width="42" height="34"/>' . $dipt_gm_pellets . '</pattern>'
	. '<pattern id="gm-abs" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><rect class="ge-blanco" width="7" height="7"/><path class="ge-raya" d="M0 0V7"/></pattern>'
	. '<pattern id="gm-sop" width="9" height="9" patternUnits="userSpaceOnUse"><rect class="ge-sop-fondo" width="9" height="9"/><path class="ge-raya-fina" d="M0 9L9 0"/></pattern>'
	. '</defs>'
	// 3 · hielo seco (después de los soportes y el secundario en el montaje, pero debajo en el dibujo)
	. '<g data-entra style="--e:3"><g class="ge-capa" data-capa="2"><rect x="92" y="154" width="416" height="244" fill="url(#gm-hielo)"/></g></g>'
	// 1 · soportes
	. '<g data-entra style="--e:1"><g class="ge-capa" data-capa="2"><rect class="ge-linea" x="216" y="370" width="168" height="28" fill="url(#gm-sop)"/><rect class="ge-linea" x="168" y="262" width="42" height="22" fill="url(#gm-sop)"/><rect class="ge-linea" x="390" y="262" width="42" height="22" fill="url(#gm-sop)"/></g></g>'
	// 2 · secundario con el primario dentro: baja a su sitio
	. '<g data-entra class="ge-baja" style="--e:2"><g class="ge-capa" data-capa="1"><rect class="ge-linea ge-blanco" x="210" y="204" width="180" height="166" rx="12"/><rect class="ge-linea ge-blanco" x="202" y="186" width="196" height="20" rx="3"/><path class="ge-raya-fina" d="M220 190v12M232 190v12M244 190v12M256 190v12M344 190v12M356 190v12M368 190v12M380 190v12"/></g>'
	. '<g class="ge-capa" data-capa="0"><rect class="ge-linea" x="224" y="218" width="152" height="138" rx="6" fill="url(#gm-abs)"/><path class="ge-linea ge-blanco" d="M280 232V330q0 20 20 20t20-20V232Z"/><path class="ge-liquido" d="M282 292V330q0 18 18 18t18-18V292Z"/><path class="ge-brillo" d="M288 240V326"/><rect class="ge-linea ge-blanco" x="276" y="214" width="48" height="20" rx="3"/></g></g>'
	// 0 · caja de EPS
	. '<g data-entra style="--e:0"><g class="ge-capa" data-capa="3"><path class="ge-linea" d="M60 150H92V398H508V150H540V430H60Z" fill="url(#gm-eps)"/></g></g>'
	// 4 · tapa apoyada, sin precinto
	. '<g data-entra class="ge-baja" style="--e:4"><g class="ge-capa" data-capa="4"><path class="ge-linea" d="M60 106H540V140H504V154H96V140H60Z" fill="url(#gm-eps)"/></g></g>'
	// 5 · el CO₂ sale por la junta (flechas fijas y volutas que se mueven con la página cargada)
	. '<g data-entra style="--e:5"><g class="ge-capa" data-capa="4"><path class="ge-gas" d="M76 145C60 140 40 128 34 104V74M524 145C540 140 560 128 566 104V74M27 82l7-10 7 10M559 82l7-10 7 10"/></g>'
	. '<path class="ge-voluta" style="--v:0" d="M64 132c-6-6 4-12-2-18"/><path class="ge-voluta" style="--v:1;--vx:-6px" d="M50 126c-6-6 4-12-2-18"/><path class="ge-voluta" style="--v:2" d="M536 132c6-6-4-12 2-18"/><path class="ge-voluta" style="--v:1.5;--vx:6px" d="M550 126c6-6-4-12 2-18"/>'
	. '<text class="ge-cota" x="34" y="60" text-anchor="middle">CO₂</text><text class="ge-cota" x="566" y="60" text-anchor="middle">CO₂</text>'
	. '<path class="ge-raya-fina" d="M60 444V464M540 444V464M60 454H540"/><text class="ge-cota" x="300" y="479" text-anchor="middle">≥ 100 mm</text></g>'
	. $dipt_gm_pin( 1, 300, 262 ) . $dipt_gm_pin( 2, 390, 330 ) . $dipt_gm_pin( 3, 140, 340 ) . $dipt_gm_pin( 4, 76, 300 ) . $dipt_gm_pin( 5, 300, 123 )
	. '</svg>';

return array(
	'slug'     => 'enviar-muestras-con-hielo-seco',
	'foto'     => 'uso-laboratorio-pinzas', // foto de la guía (assets/img/fotos): índice y cabecera
	'titulo'   => 'Enviar muestras con hielo seco: triple embalaje, UN 3373 y UN 1845',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'Cómo se monta el triple embalaje, dónde va el hielo seco y qué marcas lleva el bulto por carretera y en avión, con cada requisito citado de su norma: ADR 2025, IATA y OMS.',
	'seo'      => array(
		'titulo'      => 'Enviar muestras con hielo seco: UN 3373 y 1845 | DryIcePack',
		'descripcion' => 'Cómo enviar muestras con hielo seco: triple embalaje UN 3373, el hielo fuera del secundario y, en avión, UN 1845 con los kg netos. Revise la lista y envíe.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> una muestra de categoría B (UN 3373) viaja en triple embalaje: recipiente primario, embalaje secundario y embalaje exterior. El hielo seco va fuera del embalaje secundario y la caja exterior tiene que dejar salir el CO<sub>2</sub>. En avión, el bulto lleva además UN 1845, los kilos netos de hielo seco y la etiqueta de clase 9.</p>

<p>Esta guía resume las normas vigentes en noviembre de 2026 y dice de dónde sale cada requisito: el ADR 2025 (carretera), la reglamentación de IATA (avión) y la guía de la OMS. No sustituye a la formación del expedidor ni a su consejero de seguridad. Antes de enviar, confirme con su transportista qué acepta.</p>

<h2>¿Su muestra es de categoría A, B o exenta?</h2>
<p>La clasificación la hace el expedidor, normalmente el responsable del laboratorio, y tiene que poder justificarla (OMS, apartado 4.6.1).</p>
<ul>
<li><strong>Categoría A (UN 2814 o UN 2900).</strong> Puede causar una incapacidad permanente o una enfermedad mortal o potencialmente mortal (ADR, 2.2.62.1.4.1). Lleva otra instrucción de embalaje, la P620, y queda fuera de esta guía.</li>
<li><strong>Categoría B (UN 3373).</strong> Una materia infecciosa que no cumple los criterios de la A (ADR, 2.2.62.1.4.2). Su nombre en el bulto es «Materia biológica, categoría B». Es la que explica esta guía.</li>
<li><strong>Muestra exenta.</strong> Muestras humanas o animales con un riesgo mínimo de contener patógenos, a juicio de un profesional: historial, síntomas y situación local. Viajan en triple embalaje básico con la indicación «Muestra humana exenta» o «Muestra animal exenta» (ADR, 2.2.62.1.5.8).</li>
</ul>

<h2>¿Cómo se monta el triple embalaje?</h2>
<p>La instrucción de embalaje P650 pide tres componentes: recipiente primario, embalaje secundario y embalaje exterior. Por carretera, uno de los dos últimos tiene que ser rígido (ADR, P650); en avión, el exterior (IATA, instrucción 650).</p>
<figure class="gu-esquema" data-revela data-activa-grupo><div class="gu-esquema__lienzo" data-pausa>' . $dipt_gm_svg . '</div>
<ol class="gu-esquema__claves">
<li data-activa><strong>Recipiente primario</strong> estanco. Si la muestra es líquida, envuelto en absorbente capaz de empapar todo su contenido.</li>
<li data-activa><strong>Embalaje secundario</strong> estanco. Con líquidos, el primario o el secundario aguanta 95&nbsp;kPa sin fugas. Los dos resisten el frío del hielo seco.</li>
<li data-activa><strong>Hielo seco</strong> fuera del secundario, con soportes que lo mantienen en su sitio cuando el hielo se sublima.</li>
<li data-activa><strong>Embalaje exterior</strong> robusto, con una cara de al menos 100&nbsp;×&nbsp;100&nbsp;mm. El bulto aguanta una caída de 1,2&nbsp;m.</li>
<li data-activa><strong>Tapa sin precinto.</strong> El CO<sub>2</sub> tiene que poder salir: nada de cinta en todas las juntas.</li>
</ol>
<figcaption>Triple embalaje P650 con hielo seco. Fuentes: ADR 2025 (P650) e IATA, instrucción de embalaje 650.</figcaption></figure>
<ul>
<li>Entre el embalaje secundario y el exterior va una lista detallada del contenido (IATA, instrucción 650).</li>
<li>En avión, cada recipiente primario con líquido lleva 1&nbsp;L como máximo, y el embalaje exterior, 4&nbsp;L o 4&nbsp;kg. El hielo seco no cuenta en ese límite (IATA, instrucción 650). Por carretera no hay límite por bulto (OMS, apartado 6.2).</li>
<li>El fabricante del embalaje tiene que darle instrucciones claras para llenarlo y cerrarlo. Sígalas (ADR, P650).</li>
</ul>

<h2>¿Dónde va el hielo seco dentro del bulto?</h2>
<ul>
<li><strong>Fuera del embalaje secundario,</strong> dentro del embalaje exterior o en un sobreembalaje. Nunca dentro del secundario (IATA, instrucción 650).</li>
<li><strong>Con soportes interiores</strong> que mantengan el secundario en su sitio cuando el hielo se haya sublimado (IATA, instrucción 650).</li>
<li><strong>En un embalaje que deje salir el CO<sub>2</sub>,</strong> para que la presión no lo reviente (IATA, instrucción 650). Nunca en un recipiente con tapa de rosca ni en una bolsa de plástico cerrada (Universidad de Oregón).</li>
<li><strong>Con el menor aire posible alrededor.</strong> Si queda hueco, rellénelo con papel arrugado o relleno de embalar: el hielo se sublima más despacio (Universidad de Oregón).</li>
<li><strong>¿3 mm o 16 mm?</strong> Los pellets de 3 mm rellenan los huecos entre tubos; los nuggets de 16 mm aguantan más en viajes largos. Lo explicamos en <a href="' . esc_url( dipt_url( 'laboratorios' ) ) . '">hielo seco para laboratorios y transporte de muestras</a>.</li>
</ul>
<p>El primario y el secundario tienen que mantener su integridad a la temperatura del hielo seco, −78,5&nbsp;°C, y también si se pierde el frío (ADR, P650).</p>

<h2>¿Qué marcas y etiquetas lleva el bulto?</h2>
<table class="gu-matriz">
<thead><tr><th>Marca o etiqueta</th><th>Carretera (ADR)</th><th>Avión (IATA)</th></tr></thead>
<tbody>
<tr><td>Rombo «UN 3373» de al menos 50&nbsp;×&nbsp;50&nbsp;mm (línea de 2&nbsp;mm, cifras de 6&nbsp;mm) y, al lado, el nombre «Materia biológica, categoría B» en letras de 6&nbsp;mm, todo en la misma cara</td><td>Sí</td><td>Sí</td></tr>
<tr><td>«DIÓXIDO DE CARBONO SÓLIDO» seguido de «AGENTE REFRIGERANTE», en castellano y también en inglés, francés o alemán</td><td>Sí</td><td>—</td></tr>
<tr><td>UN 1845, «Dry ice» o «Carbon dioxide, solid» y los kilos netos de hielo seco</td><td>—</td><td>Sí</td></tr>
<tr><td>Etiqueta de clase 9 (mercancías peligrosas varias)</td><td>No</td><td>Sí</td></tr>
</tbody>
</table>
<p>«—»: esa norma no lo pide. Fuentes: ADR, P650 y 5.5.3.4; IATA, instrucción 650; OMS, tabla 4 (etiqueta de clase 9) y anexo 4.5 (instrucción 954). Si el mismo bulto puede ir por carretera y en avión, puede juntar las dos marcas. La OMS pone este ejemplo para el refrigerante: <span class="dato">UN1845 CARBON DIOXIDE, SOLID AS COOLANT Net 3 kg</span>.</p>
<h3>En avión, además</h3>
<ul>
<li>Nombre y dirección del expedidor y del destinatario en cada bulto, y nombre y teléfono de una persona responsable en la guía aérea o en el bulto (IATA, instrucción 650).</li>
<li>En la guía aérea: «UN 3373», «BIOLOGICAL SUBSTANCE, CATEGORY B» y el número de bultos (IATA, instrucción 650). Y para el hielo seco, UN 1845, su nombre, el número de bultos y los kilos netos de cada uno (instrucción 954).</li>
<li>No hace falta la declaración del expedidor de mercancías peligrosas (IATA, instrucción 650).</li>
<li>Como máximo, 200&nbsp;kg de hielo seco por bulto, y el expedidor acuerda cada envío con la compañía para que se sigan sus procedimientos de ventilación (instrucción 954).</li>
<li>Con sobreembalaje: la palabra «Overpack» en letras de al menos 12&nbsp;mm y las marcas de los bultos repetidas por fuera, salvo que se vean (IATA, instrucción 650).</li>
<li>Pasajeros y tripulación no pueden llevar sustancias infecciosas ni en el equipaje ni encima (IATA, instrucción 650).</li>
</ul>

<h2>¿Y si lleva las muestras en su propio vehículo?</h2>
<p>Al hielo seco el ADR solo le aplica su apartado 5.5.3 (ADR, tabla A, UN 1845):</p>
<ul>
<li>Los bultos con hielo seco van en un vehículo bien ventilado: con menos del 0,5&nbsp;% de CO<sub>2</sub> y más del 19,5&nbsp;% de oxígeno en el aire (ADR, 5.5.3.3.3).</li>
<li>Si la zona de carga no comunica con la cabina, o es isoterma o frigorífica y está separada de ella, no hace falta ventilar, pero sí la marca de advertencia de asfixia en cada acceso, salvo que no haya un riesgo real de asfixia (ADR, 5.5.3.1.5, 5.5.3.3.3 y 5.5.3.6).</li>
<li>Quien manipule o transporte esos vehículos tiene que estar formado según su función (ADR, 5.5.3.2.4).</li>
<li>En un coche, nunca en el habitáculo: en el maletero y ventilando.</li>
</ul>
<p>Las normas para coche, furgoneta, mensajería y avión están en <a href="' . esc_url( home_url( '/guias/transporte-de-hielo-seco/' ) ) . '">transporte de hielo seco: ADR, mensajería y avión</a>. Para el frío de una ruta de reparto, vea <a href="' . esc_url( dipt_url( 'transporte' ) ) . '">hielo seco para transporte en frío</a>.</p>

<h2>¿Cuánto hielo seco pone y qué día envía?</h2>
<p>Depende de las horas de viaje, de la caja y de la temperatura de fuera. La Universidad de Oregón recomienda poner de 2,27 a 4,54&nbsp;kg de hielo seco por cada 24&nbsp;h de envío y seguir lo que indique el fabricante de la caja. Qué hace que dure más o menos lo explicamos en <a href="' . esc_url( home_url( '/guias/cuanto-dura-el-hielo-seco/' ) ) . '">cuánto dura el hielo seco y cómo guardarlo</a>.</p>
<ul>
<li>Confirme con el destinatario que alguien lo recibe ese día y tenga en cuenta festivos y cierres (Universidad de Oregón).</li>
<li>Un bulto que pasa el fin de semana en un almacén necesita mucho más hielo: mejor que salga a principio de semana.</li>
<li>Pida el hielo seco para el día en que prepara el envío. Si lo pide antes de las 12:00, le llega al día siguiente antes de las 14:00, de martes a viernes; el sábado, según zona, antes de las 13:00 y con precio de sábado, que ve al elegir el día. También puede reservarlo para cualquier día de los próximos 90 o recogerlo en nuestra nave de Mataró.</li>
</ul>

<h2>¿Qué precauciones de seguridad hay que tomar?</h2>
<ul>
<li><strong>Guantes térmicos y pinzas.</strong> A −78,5&nbsp;°C, el hielo seco quema la piel.</li>
<li><strong>Prepare el bulto en un sitio ventilado.</strong> Cada kilo de hielo seco da unos 541&nbsp;litros de CO<sub>2</sub> (ficha de PanGas, grupo Linde), y el CO<sub>2</sub> desplaza el oxígeno. Nunca en una cámara frigorífica ni en una sala cerrada sin ventilación.</li>
<li><strong>Nada hermético.</strong> Ni el bulto ni el recipiente donde lo guarde: la presión del gas puede reventarlo.</li>
<li><strong>No en el ultracongelador.</strong> En un congelador cerrado el gas genera presión y puede dañar el equipo. Guárdelo en su caja de EPS, en un sitio ventilado.</li>
<li><strong>No se ingiere.</strong> Nunca dentro de una bebida.</li>
<li><strong>Fuera del alcance de niños y mascotas</strong> y de quien no sepa manipularlo.</li>
<li><strong>En coche, en el maletero y ventilando.</strong> Nunca en el habitáculo.</li>
</ul>
<p>Las normas completas y la ficha de datos de seguridad en PDF, para su servicio de prevención, están en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco y ficha de datos de seguridad</a>.</p>

<h2>¿Qué hace DryIcePack y qué no?</h2>
<p>Le servimos el hielo seco: <a href="' . esc_url( dipt_url( 'producto' ) ) . '">pellets de 3 mm o nuggets de 16 mm, en caja de EPS de 40 mm</a>, cerrada pero no hermética. La clasificación de la muestra, el embalaje para muestras y el envío los prepara su laboratorio con su transportista.</p>
<p>Si envía muestras cada semana, abra una <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con pedido fijo y factura mensual</a>: el mismo día de la semana, sin tener que pedirlo, y una factura al mes a 30 días.</p>

<h2>¿Qué hay que revisar antes de cerrar la caja?</h2>
<ul>
<li>La muestra está clasificada (A, B o exenta) y la decisión, documentada.</li>
<li>Primario estanco y, si es líquido, con absorbente para todo su contenido.</li>
<li>Secundario estanco y cerrado. La lista del contenido, entre el secundario y el exterior.</li>
<li>El hielo seco, fuera del secundario, con soportes y sin huecos de aire.</li>
<li>La caja exterior deja salir el gas: tapa sin precinto y sin cinta en todas las juntas.</li>
<li>Rombo UN 3373 y «Materia biológica, categoría B» en la misma cara.</li>
<li>Marca del hielo seco: «DIÓXIDO DE CARBONO SÓLIDO, AGENTE REFRIGERANTE» por carretera; UN 1845, nombre, kilos netos y etiqueta de clase 9 en avión.</li>
<li>En avión, una persona responsable con su teléfono, en la guía aérea o en el bulto.</li>
<li>El destinatario sabe qué día llega y alguien lo recibe.</li>
</ul>

<h2>¿De dónde sale cada dato?</h2>
<ul>
<li><a href="https://www.boe.es/buscar/doc.php?id=BOE-A-2025-77" rel="noopener">ADR 2025 en castellano, BOE núm. 3, de 3 de enero de 2025</a>: clasificación (2.2.62), instrucción de embalaje P650 y hielo seco (5.5.3).</li>
<li><a href="https://www.iata.org/contentassets/b08040a138dc4442a4f066e6fb99fe2a/dgr-67-es-pi650.pdf" rel="noopener">IATA, instrucción de embalaje 650, 67.ª edición, de 1 de enero de 2026</a>.</li>
<li><a href="https://www.who.int/publications/i/item/9789240120822" rel="noopener">OMS, Guidance on regulations for the transport of infectious substances 2025-2026</a>: clasificación, marcas y la instrucción 954 de OACI para el hielo seco.</li>
<li><a href="https://safety.uoregon.edu/sites/default/files/dry_ice_shipping_guide.pdf" rel="noopener">Universidad de Oregón, Dry Ice Shipping Guide</a>: kilos por cada 24 h y cómo rellenar la caja.</li>
<li><a href="https://static.prd.echannel.linde.com/wcsstore/CH_REC_Industrial_Ntl_Store/datasheets/fr_CH/pangas_ps_glace-carbonique-en-pellets-3mm_f.pdf" rel="noopener">PanGas (grupo Linde), ficha técnica de los pellets de hielo seco</a>: litros de gas por kilo.</li>
</ul>
<p>Revisado el 10 de noviembre de 2026. Las normas cambian: el ADR, cada dos años, y la reglamentación de IATA, cada año.</p>',
);
