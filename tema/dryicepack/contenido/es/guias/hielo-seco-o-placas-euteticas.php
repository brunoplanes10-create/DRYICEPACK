<?php
/* Guía · Hielo seco o placas eutécticas (ficha 8 de estrategia/SEO-CONTENIDOS.md: «hielo seco o placas eutécticas»,
 * «placas eutécticas para camiones», «acumuladores de frío», «hielo seco o gel refrigerante», «placas de hielo seco»,
 * «hielo seco en gel»). Comparativa B2B, de usted. «Hielo seco vs hielo normal» es de /que-es-el-hielo-seco/: aquí solo se enlaza.
 * DryIcePack no vende placas: se dice. No hay cifras de duración ni de coste por envío inventadas.
 * Fuentes (comprobadas el 10/11/2026):
 * - Polibox, ficha «Placa eutéctica Fresco 0/−6 °C» (110932): 12 h mínimo en horizontal en el congelador; 2,2 kg.
 * - Polibox, ficha «Placa eutéctica Super Fresh 0/−12 °C» (110918): al menos 24 h en horizontal, en un congelador al menos
 *   6 °C más frío que la temperatura de la placa; el líquido, sólido del todo; 4,8 kg; temperatura objetivo de 0 a +4 °C.
 * - Polibox, catálogo Hot&Cold: placas de 0/−6, 0/−12 y 0/−21 °C; GN 1/1 de 4,8 kg (−12 °C) y 5,1 kg (−21 °C).
 * - Carrier Transicold, Vatna 200 H-B: placas eutécticas que se cargan enchufadas por la noche y liberan el frío de día,
 *   para el reparto de congelados y helados.
 * - ADR 2025, sección 5.5.3 (BOE-A-2025-77).
 * [PENDIENTE: ¿algún cliente que cambió de placas a hielo seco y por qué? Si da permiso, contarlo aquí.] */
$dipt_k20 = function_exists( 'dipt_precio_pack' ) ? (float) ( dipt_precio_pack( 20 )['kilo'] ?? 0 ) : 0;
return array(
	'slug'     => 'hielo-seco-o-placas-euteticas',
	'foto'     => 'cajas-furgoneta', // foto de la guía (assets/img/fotos): índice y cabecera
	'formato'  => '16mm',
	'titulo'   => 'Hielo seco o placas eutécticas: cuál conviene en cada envío',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'Las placas se congelan antes y tienen que volver; el hielo seco llega listo a −78,5 °C y no vuelve. Cuál conviene para refrigerado, congelado o ultracongelado, y cuándo combinarlos.',
	'seo'      => array(
		'titulo'      => 'Hielo seco o placas eutécticas: cuál usar | DryIcePack',
		'descripcion' => 'Hielo seco a −78,5 °C o placas eutécticas reutilizables: cuál conviene según la temperatura del producto, la ruta y si la caja vuelve. Compárelos.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> para producto refrigerado, placas eutécticas o gel. Para congelado en envíos de ida, que no vuelven, hielo seco. Para −70 u −80 °C, solo hielo seco: está a −78,5 °C. Las placas se reutilizan, pero hay que congelarlas antes y recuperarlas.</p>

<h2>¿Qué diferencia hay entre el hielo seco y una placa eutéctica?</h2>
<p><strong>El hielo seco</strong> es CO<sub>2</sub> sólido a −78,5 °C. No se funde: se sublima, pasa directamente a gas y no deja agua en la caja. Se va gastando por el camino, así que cada envío lleva hielo nuevo. Si quiere saber más, vea <a href="' . esc_url( dipt_url( 'que-es' ) ) . '">qué es el hielo seco</a>.</p>
<p><strong>Una placa eutéctica</strong> es un acumulador de frío: un envase rígido y cerrado con un líquido que se congela a una temperatura fija. Se congela antes en un congelador o una cámara y cede el frío mientras se descongela. Después se vuelve a congelar y se usa otra vez. Hay placas para distintas temperaturas: en el catálogo de Polibox, por ejemplo, de 0/−6, 0/−12 y 0/−21 °C.</p>
<p><strong>El gel refrigerante</strong> es lo mismo que la placa, pero en una bolsa flexible. El «hielo seco en gel» no existe: si lo busca, lo que hay es gel refrigerante. Y «placas de hielo seco» no vendemos: nuestro hielo seco va en pellets de 3 mm y nuggets de 16 mm. Placas eutécticas, tampoco: le contamos las diferencias para que elija bien.</p>

<h2>¿Cuál conviene en cada caso?</h2>
<figure class="gu-compara" data-vivo data-activa-grupo>
<table role="table">
<caption class="visually-hidden">Hielo seco frente a placas eutécticas</caption>
<thead role="rowgroup"><tr role="row"><th role="columnheader" scope="col"><span class="visually-hidden">Criterio</span></th><th role="columnheader" scope="col">Hielo seco</th><th role="columnheader" scope="col">Placas eutécticas</th></tr></thead>
<tbody role="rowgroup">
<tr role="row" data-activa style="--i:0"><th role="rowheader" scope="row">Temperatura</th><td role="cell" data-etiqueta="Hielo seco"><strong>−78,5 °C</strong></td><td role="cell" data-etiqueta="Placas">La de la placa: de 0 a −21 °C, según el modelo</td></tr>
<tr role="row" data-activa style="--i:1"><th role="rowheader" scope="row">Producto</th><td role="cell" data-etiqueta="Hielo seco">Congelado y ultracongelado</td><td role="cell" data-etiqueta="Placas">Refrigerado o congelado, según la placa</td></tr>
<tr role="row" data-activa style="--i:2"><th role="rowheader" scope="row">Preparación</th><td role="cell" data-etiqueta="Hielo seco">Llega listo para usar</td><td role="cell" data-etiqueta="Placas">Congelarlas antes: de 12 a 24 h como mínimo, según el modelo</td></tr>
<tr role="row" data-activa style="--i:3"><th role="rowheader" scope="row">¿Se reutiliza?</th><td role="cell" data-etiqueta="Hielo seco">No: pasa a gas por el camino</td><td role="cell" data-etiqueta="Placas">Sí, si vuelven</td></tr>
<tr role="row" data-activa style="--i:4"><th role="rowheader" scope="row">Peso</th><td role="cell" data-etiqueta="Hielo seco">Los kilos del viaje, que se van gastando</td><td role="cell" data-etiqueta="Placas">De 4,8 a 5,1 kg cada placa GN 1/1, a la ida y a la vuelta</td></tr>
<tr role="row" data-activa style="--i:5"><th role="rowheader" scope="row">Agua en la caja</th><td role="cell" data-etiqueta="Hielo seco">Ninguna: no se funde</td><td role="cell" data-etiqueta="Placas">Ninguna: el líquido va cerrado</td></tr>
<tr role="row" data-activa style="--i:6"><th role="rowheader" scope="row">Normas</th><td role="cell" data-etiqueta="Hielo seco">UN 1845: marca en el bulto (ADR 5.5.3) y límites en avión</td><td role="cell" data-etiqueta="Placas">No sueltan gas: no les afecta la sección 5.5.3 del ADR</td></tr>
<tr role="row" data-activa style="--i:7"><th role="rowheader" scope="row">Lo que cuesta</th><td role="cell" data-etiqueta="Hielo seco">Los kilos de cada envío</td><td role="cell" data-etiqueta="Placas">Las placas, el congelador y traerlas de vuelta</td></tr>
</tbody>
</table>
<figcaption>Placas: fichas y catálogo de Polibox (modelos de 0/−6, 0/−12 y 0/−21 °C). Hielo seco: ADR 2025, sección 5.5.3 (BOE-A-2025-77).</figcaption>
</figure>

<h2>¿Cuándo es mejor el hielo seco y cuándo las placas?</h2>
<ul>
<li><strong>Producto refrigerado</strong> (fruta, lácteos, medicamentos que viajan por encima de 0 °C): placas o gel a la temperatura que pida el producto. El hielo seco puede congelarlo; si lo usa, sepárelo con un cartón y ajuste bien los kilos.</li>
<li><strong>Congelado en reparto con vuelta a la base:</strong> las placas encajan, porque vuelven cada día y se recongelan de noche. Hay vehículos con placas fijas que se cargan enchufados por la noche y ceden el frío durante la jornada, pensados para el reparto de congelados y helados.</li>
<li><strong>Congelado en envíos de ida</strong>, por mensajería, cuando la caja no vuelve: hielo seco. No hay que recuperar nada. Cómo preparar la caja: <a href="' . esc_url( home_url( '/guias/enviar-congelados-con-hielo-seco/' ) ) . '">cómo enviar congelados con hielo seco</a>.</li>
<li><strong>Ultracongelado, de −70 a −80 °C</strong>, como muestras biológicas: solo hielo seco. Ninguna de las placas del catálogo consultado baja de −21 °C. Más en <a href="' . esc_url( dipt_url( 'laboratorios' ) ) . '">hielo seco para laboratorios</a> y en cómo <a href="' . esc_url( home_url( '/guias/enviar-muestras-con-hielo-seco/' ) ) . '">enviar muestras con hielo seco</a>.</li>
<li><strong>Un pico puntual:</strong> una ruta más larga, un día de calor o una avería del equipo de frío. El hielo seco no necesita preparación: pídalo antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00.</li>
</ul>

<h2>¿Se pueden combinar?</h2>
<p>Sí, en congelado: las placas como base de frío y el hielo seco encima para las rutas más largas o los días de más calor. El hielo seco sigue llevando su marca en el bulto y, en el vehículo, su ventilación. Para rutas que se repiten, mire <a href="' . esc_url( dipt_url( 'transporte' ) ) . '">hielo seco para transporte de congelados</a>, con el pedido programado cada semana.</p>

<h2>¿Cuánto cuesta cada opción por envío?</h2>
<p><strong>Con placas</strong>, el gasto está al principio: las placas, el congelador o la cámara para recongelarlas y la logística para que vuelvan. Si la caja no vuelve, la placa se pierde con ella.</p>
<p><strong>Con hielo seco</strong>, paga los kilos de cada envío y nada más: no hay que guardar ni recuperar nada. En DryIcePack, en packs de {pk}, 10, 15 y 20 kg, y el kilo baja con cada pack.' . ( $dipt_k20 > 0 ? ' En el de 20 kg, sale a ' . dipt_euros( $dipt_k20 ) . ' el kilo con IVA, más el envío.' : '' ) . ' Si su consumo es fijo, una <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con pedido programado</a> le da mejor precio y una factura al mes.</p>

<h2>¿Qué precauciones pide el hielo seco?</h2>
<p>Las placas no sueltan gas. El hielo seco sí, y está mucho más frío:</p>
<ul>
<li><strong>Guantes térmicos y pinzas.</strong> A −78,5 °C quema la piel en segundos.</li>
<li><strong>Ventilación.</strong> El CO<sub>2</sub> desplaza el oxígeno: en el vehículo, siempre en la zona de carga y nunca en la cabina. Las normas de cada medio están en <a href="' . esc_url( home_url( '/guias/transporte-de-hielo-seco/' ) ) . '">transporte de hielo seco: ADR, mensajería y avión</a>.</li>
<li><strong>Nada hermético.</strong> En un envase cerrado, la presión del gas puede reventarlo.</li>
<li><strong>No se ingiere</strong> y se guarda fuera del alcance de niños y mascotas.</li>
<li><strong>En el coche, en el maletero y ventilando.</strong></li>
</ul>
<p>Todas las normas: <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco</a>.</p>

<h2>¿De dónde salen estos datos?</h2>
<ul>
<li>Placas: Polibox, fichas de la <a href="https://hosteleria10.com/recursos/pdfs/polibox-ficha-fria-placa-eutetica-gn-1-1.pdf">placa Fresco 0/−6 °C y la placa Super Fresh 0/−12 °C</a> y <a href="https://hosteleria10.com/recursos/pdfs/polibox-catalogo-placa-eutetica-calor-gn-1-1.pdf">catálogo de placas eutécticas</a>.</li>
<li>Vehículos con placas: Carrier Transicold, <a href="https://www.carrier.com/truck-trailer/es/es/products/es-truck-trailer/lcv/vatna-200-h-b/">equipo eutéctico Vatna</a>.</li>
<li>Hielo seco: ADR 2025, sección 5.5.3, <a href="https://www.boe.es/buscar/doc.php?id=BOE-A-2025-77">BOE del 3 de enero de 2025</a>.</li>
</ul>',
);
