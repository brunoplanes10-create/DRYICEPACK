<?php
/* Guía · Transporte de hielo seco: coche, furgoneta, mensajería y avión (ficha 2 de estrategia/SEO-CONTENIDOS.md:
 * «transporte de hielo seco», «cómo transportar hielo seco», «hielo seco mercancía peligrosa», «hielo seco ADR»,
 * «hielo seco clase 9», «etiqueta hielo seco UN 1845», «hielo seco en avión», «hielo seco en el coche»).
 * No ataca «hielo seco PARA transporte» (comprar: /hielo-seco-transporte/, que se enlaza). De usted.
 * Fuentes normativas (todas comprobadas el 10/11/2026):
 * - ADR 2025, sección 5.5.3: «Texto enmendado de los Anejos A y B del ADR (ADR 2025)», BOE núm. 3, 3/1/2025, BOE-A-2025-77.
 *   5.5.3.1.1 (al UN 1845 no se le aplica ninguna otra disposición del ADR), 5.5.3.1.5, 5.5.3.2.4 (formación),
 *   5.5.3.3.3 y su nota (bien ventilado: CO₂ < 0,5 % y O₂ > 19,5 %), 5.5.3.4 (marca del bulto), 5.5.3.5 (hielo suelto, 30 mm),
 *   5.5.3.6 (marca de advertencia, 150 × 250 mm) y 5.5.3.7 (documentos).
 * - IATA, Reglamentación sobre mercancías peligrosas, 67.ª edición (1/1/2026), tabla 2.3.A: 2,5 kg por persona con aprobación del operador.
 * - IATA, Lista de comprobación para la aceptación de hielo seco, 68.ª edición (1/1/2027): instrucción 954, 200 kg por bulto, marcas y clase 9.
 * - AESA, información de seguridad para pasajeros (el hielo seco figura entre los artículos peligrosos).
 * - Messer, Pocket Guide Seguridad 04 «Transporte seguro de hielo seco»: efectos del CO₂ (1 %, 3–5 %, >10 %) y expansión de 845:1.
 * SP 551 no se cita: el texto de la sección 5.5.3 no la menciona para el UN 1845.
 * [PENDIENTE: requisitos de MRW para bultos con hielo seco.]
 * [PENDIENTE: ¿las cajas de DryIcePack salen marcadas con UN 1845 y peso neto? Foto real para esta guía.]
 * [PENDIENTE: revisión por un consejero de seguridad ADR antes de darla por definitiva.]
 * [PENDIENTE: etiqueta UN 1845 imprimible (recurso descargable de la ficha 2).] */
return array(
	'slug'     => 'transporte-de-hielo-seco',
	'foto'     => 'reparto-furgoneta', // foto de la guía (assets/img/fotos): índice y cabecera
	'titulo'   => 'Transporte de hielo seco: coche, furgoneta, mensajería y avión',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'En coche, en el maletero y ventilando. En furgoneta, lo que pide el ADR 2025. Por mensajería, la marca del bulto. En avión, hasta 2,5 kg por pasajero con permiso de la aerolínea y la instrucción 954 de la IATA para la carga.',
	'seo'      => array(
		'titulo'      => 'Transporte de hielo seco: coche, avión y ADR | DryIcePack',
		'descripcion' => '¿Se puede llevar hielo seco en el coche o en el avión? En el maletero y ventilando; en avión, hasta 2,5 kg con permiso de la aerolínea. Lea las normas.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> el hielo seco (UN 1845) se puede llevar en coche, en furgoneta, por mensajería y en avión. La regla de siempre: que el gas pueda salir y que no se acumule donde respira alguien. En carretera manda la sección 5.5.3 del ADR 2025; en avión, la reglamentación de la IATA, con un máximo de 2,5 kg por pasajero y solo con permiso de la aerolínea.</p>

<h2>¿El hielo seco es mercancía peligrosa?</h2>
<p>Sí. Es dióxido de carbono sólido, con el número ONU 1845, y en avión viaja con la etiqueta de clase 9. No es tóxico ni inflamable: el peligro es el gas. Al sublimarse ocupa más de 845 veces su volumen y desplaza el oxígeno. Según la guía de seguridad de Messer, con un 1 % de CO<sub>2</sub> en el aire ya puede dar somnolencia; del 3 al 5 %, síntomas parecidos a ir bebido, y por encima del 10 %, puede ser mortal.</p>
<p>En carretera, el ADR 2025 lo trata aparte: al hielo seco solo se le aplica la sección 5.5.3 y ninguna otra disposición del ADR (5.5.3.1.1). Si el vehículo solo lleva hielo seco, no hacen falta placas naranjas. Si lleva otras mercancías peligrosas, a esas se les aplica todo lo demás.</p>

<h2>¿Cómo se transporta según el medio?</h2>
<figure class="gu-compara" data-vivo data-activa-grupo>
<table role="table">
<caption class="visually-hidden">Cómo se transporta el hielo seco en cada medio</caption>
<thead role="rowgroup"><tr role="row"><th role="columnheader" scope="col">Medio</th><th role="columnheader" scope="col">Dónde va</th><th role="columnheader" scope="col">Qué pide</th></tr></thead>
<tbody role="rowgroup">
<tr role="row" data-activa style="--i:0"><th role="rowheader" scope="row">Coche</th><td role="cell" data-etiqueta="Dónde va">En el maletero, dentro de su caja</td><td role="cell" data-etiqueta="Qué pide">Ventilar. Nunca en el habitáculo</td></tr>
<tr role="row" data-activa style="--i:1"><th role="rowheader" scope="row">Furgoneta o camión</th><td role="cell" data-etiqueta="Dónde va">En la zona de carga</td><td role="cell" data-etiqueta="Qué pide">Bien ventilada. Si la carga no comunica con la cabina, marca de ATENCIÓN en cada puerta (ADR 5.5.3)</td></tr>
<tr role="row" data-activa style="--i:2"><th role="rowheader" scope="row">Mensajería</th><td role="cell" data-etiqueta="Dónde va">En una caja aislante que deja salir el gas</td><td role="cell" data-etiqueta="Qué pide">Marca «DIÓXIDO DE CARBONO SÓLIDO» (ADR 5.5.3.4) y las condiciones de cada mensajería</td></tr>
<tr role="row" data-activa style="--i:3"><th role="rowheader" scope="row">Avión, como carga</th><td role="cell" data-etiqueta="Dónde va">Bultos de hasta 200 kg de hielo seco</td><td role="cell" data-etiqueta="Qué pide">UN 1845, peso neto, remitente y destinatario y etiqueta de clase 9 (IATA, instrucción 954)</td></tr>
<tr role="row" data-activa style="--i:4"><th role="rowheader" scope="row">Avión, con el pasajero</th><td role="cell" data-etiqueta="Dónde va">Hasta 2,5 kg por persona, en la maleta o en cabina</td><td role="cell" data-etiqueta="Qué pide">Permiso de la aerolínea (IATA, tabla 2.3.A)</td></tr>
</tbody>
</table>
<figcaption>Carretera: ADR 2025, sección 5.5.3 (BOE-A-2025-77). Avión: IATA, 67.ª y 68.ª ediciones de su reglamentación de mercancías peligrosas.</figcaption>
</figure>

<h2>¿Se puede llevar hielo seco en el coche?</h2>
<p>Sí, en el maletero y ventilando:</p>
<ul>
<li><strong>Dentro de su caja</strong>, con la tapa puesta y sin precintar.</li>
<li><strong>Nunca en el habitáculo</strong>, tampoco a los pies del copiloto.</li>
<li><strong>Si el maletero da al habitáculo</strong>, como en un familiar o un SUV, lleve las ventanillas algo abiertas y la ventilación con aire de fuera, no en recirculación.</li>
<li><strong>Trayecto directo:</strong> no lo deje en el coche aparcado y cerrado.</li>
<li><strong>Si nota dolor de cabeza, mareo o falta de aire</strong>, pare, salga del coche y ventílelo.</li>
</ul>
<p>Si lo recoge en nuestra nave de Mataró, el precio es redondo y con IVA incluido: {pk} kg por {recogida:pk} y 20 kg por {recogida:20}. Para la vuelta, lo mismo: en el maletero y ventilando. Horarios y días en <a href="' . esc_url( dipt_url( 'envios' ) ) . '">envíos, plazos y recogida en Mataró</a>.</p>

<h2>¿Qué pide el ADR en una furgoneta o un camión?</h2>
<p>Lo que pide la sección 5.5.3 del ADR 2025, punto por punto:</p>
<ul>
<li><strong>En la zona de carga, nunca en la cabina.</strong> Es la primera precaución de la guía de seguridad de Messer.</li>
<li><strong>Vehículo bien ventilado</strong> (5.5.3.3.3). El ADR llama «bien ventilado» a un espacio con menos del 0,5 % de CO<sub>2</sub> y más del 19,5 % de oxígeno.</li>
<li><strong>Si la zona de carga no comunica con la cabina</strong>, o es una caja isoterma o frigorífica separada de ella, no hace falta ventilarla, pero cada puerta lleva la marca de advertencia de asfixia: «ATENCIÓN» y el nombre del producto, «DIÓXIDO DE CARBONO SÓLIDO», en un rótulo de al menos 150 × 250 mm. Se quita cuando el vehículo se ha ventilado y descargado (5.5.3.6).</li>
<li><strong>Documentos:</strong> si el vehículo no se ha ventilado del todo antes del transporte, los documentos de transporte, como la carta de porte CMR, llevan «UN 1845, DIÓXIDO DE CARBONO SÓLIDO, AGENTE DE REFRIGERACIÓN» (5.5.3.7).</li>
<li><strong>Hielo seco suelto, sin caja:</strong> nunca en contacto con el metal del vehículo. Al menos 30 mm de separación, por ejemplo con tablones o palés, y la carga sujeta para cuando el hielo se haya gastado (5.5.3.5).</li>
<li><strong>Formación:</strong> quien carga y quien conduce tiene que estar formado según su responsabilidad (5.5.3.2.4).</li>
</ul>
<p>La marca y los documentos no se aplican si no hay un riesgo real de asfixia en el vehículo. Esa evaluación la hacen quienes intervienen en el transporte, según los kilos, las horas de viaje, el tipo de envase y los límites de concentración de gas (5.5.3.1.5). Si reparte congelados en ruta, le interesa también <a href="' . esc_url( dipt_url( 'transporte' ) ) . '">hielo seco para transporte de congelados</a>.</p>

<h2>¿Se puede enviar hielo seco por mensajería?</h2>
<p>Sí, si la mensajería lo acepta. El bulto lleva una marca duradera, legible y bien visible, según el ADR 2025 (5.5.3.4):</p>
<ul>
<li><strong>Si el hielo seco enfría otra mercancía:</strong> «DIÓXIDO DE CARBONO SÓLIDO, AGENTE REFRIGERANTE».</li>
<li><strong>Si se envía el propio hielo seco:</strong> «DIÓXIDO DE CARBONO SÓLIDO» o «HIELO SECO».</li>
</ul>
<p>Y siempre en una caja aislante que deje salir el gas, sin precintar todas las juntas. Cada mensajería pone sus condiciones: peso neto, etiquetas propias o un acuerdo previo. Pregúntelo antes de contratar. Cómo preparar la caja, cuántos kilos poner y qué hacer con los retrasos está en <a href="' . esc_url( home_url( '/guias/enviar-congelados-con-hielo-seco/' ) ) . '">cómo enviar congelados con hielo seco</a>.</p>
<p>Si nos lo compra a nosotros, el envío hasta su puerta lo hacemos nosotros con MRW: pida antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00.</p>

<h2>¿Se puede llevar hielo seco en el avión?</h2>
<h3>Con usted, como pasajero</h3>
<p>La tabla 2.3.A de la IATA (67.ª edición, en vigor desde el 1 de enero de 2026) lo permite con condiciones:</p>
<ul>
<li><strong>Hasta 2,5 kg por persona</strong>, en la maleta facturada o en el equipaje de mano.</li>
<li><strong>Solo para conservar productos perecederos</strong>, en un envase que deje salir el gas.</li>
<li><strong>Con la aprobación de la aerolínea.</strong> Sin ella, no se puede llevar.</li>
<li><strong>La maleta facturada va marcada</strong> con «hielo seco» o «dióxido de carbono, sólido» y el peso neto, o con la indicación de que hay 2,5 kg o menos.</li>
</ul>
<p>La AESA incluye el hielo seco entre los artículos peligrosos en su información para pasajeros, y cada aerolínea puede poner más límites. Pida el permiso al reservar y lléveselo por escrito.</p>
<h3>Como carga aérea</h3>
<p>Según la lista de comprobación de la IATA para aceptar hielo seco (68.ª edición, en vigor desde el 1 de enero de 2027):</p>
<ul>
<li><strong>Hasta 200 kg de hielo seco por bulto.</strong></li>
<li><strong>Embalaje según la instrucción 954</strong>, que deje salir el gas.</li>
<li><strong>Marcas:</strong> «UN1845», «Carbon dioxide, solid» o «Dry ice», el nombre y la dirección del remitente y del destinatario y el peso neto de hielo seco en kilos.</li>
<li><strong>Etiqueta de clase 9.</strong></li>
</ul>
<p>La aerolínea o su transitario le dirán qué documentos y qué formación piden. Para muestras de laboratorio, vea cómo <a href="' . esc_url( home_url( '/guias/enviar-muestras-con-hielo-seco/' ) ) . '">enviar muestras con hielo seco: UN 3373 y UN 1845</a> y el <a href="' . esc_url( dipt_url( 'laboratorios' ) ) . '">hielo seco para laboratorios</a>.</p>

<h2>¿Qué precauciones hay que tomar al abrir la zona de carga?</h2>
<ul>
<li><strong>Abra y espere.</strong> El CO<sub>2</sub> se acumula abajo y sale al abrir: no entre enseguida ni se quede delante de la carga.</li>
<li><strong>Ventile antes de entrar</strong> y compruebe que nadie se queda dentro al cerrar.</li>
<li><strong>Guantes térmicos y pinzas.</strong> A −78,5 °C quema la piel en segundos.</li>
<li><strong>Nada hermético.</strong> En un envase cerrado, la presión del gas puede reventarlo.</li>
<li><strong>No se ingiere</strong> y se guarda fuera del alcance de niños y mascotas.</li>
</ul>
<p>Primeros auxilios y ficha de datos de seguridad: <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco</a>.</p>

<h2>¿De dónde salen estos datos?</h2>
<ul>
<li>ADR 2025, sección 5.5.3: <a href="https://www.boe.es/buscar/doc.php?id=BOE-A-2025-77">texto enmendado de los anejos A y B del ADR</a>, BOE del 3 de enero de 2025.</li>
<li>IATA, <a href="https://www.iata.org/contentassets/6fea26dd84d24b26a7a1fd5788561d6e/dgr-67-es-2.3.a.pdf">tabla 2.3.A de mercancías peligrosas para pasajeros</a>, 67.ª edición.</li>
<li>IATA, <a href="https://www.iata.org/contentassets/b08040a138dc4442a4f066e6fb99fe2a/es_form_dryice.pdf">lista de comprobación para la aceptación de hielo seco</a>, 68.ª edición.</li>
<li>AESA, <a href="https://www.seguridadaerea.gob.es/es/ambitos/security/security/pasajeros">información de seguridad para pasajeros</a>.</li>
<li>Messer, <a href="https://www.messer.es/documents/742554/3832742/Gu%C3%ADa+seguridad+-+Hielo+seco.pdf/63161b0a-8546-627c-4947-07a4e1e39d1e">guía de bolsillo «Transporte seguro de hielo seco»</a>.</li>
</ul>
<p>Esta guía resume la norma vigente en noviembre de 2026. No sustituye a su consejero de seguridad ni a las condiciones de su transportista o de su aerolínea.</p>',
);
