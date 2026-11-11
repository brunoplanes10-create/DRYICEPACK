<?php
/* Guía · Cómo enviar congelados con hielo seco (ficha 1 de estrategia/SEO-CONTENIDOS.md: «hielo seco para envíos»,
 * «envío con hielo seco», «hielo seco para enviar paquetes», «hielo seco para transportar alimentos», «caja de hielo seco»).
 * B2B, de usted. No ataca «hielo seco para transporte de congelados» (es de /hielo-seco-transporte/: se enlaza).
 * Fuentes: ADR 2025, sección 5.5.3 (BOE-A-2025-77, 3/1/2025) y Universidad de Míchigan, Packaging Requirements for Dry Ice (2017).
 * Medición propia (10–11/11/2026, ver guía cuanto-dura-el-hielo-seco.php): cajas cerradas, en la nave, pellets de 3 mm,
 * 29 h, pérdida 1,78 / 4,10 / 5,72 kg (pequeña / grande / 30 kg). [PENDIENTE: repetir en verano y con 16 mm.]
 * [PENDIENTE: ¿MRW admite que un cliente reenvíe con hielo seco? ¿Con qué marcas y límites?]
 * [PENDIENTE: ¿se venden cajas EPS sueltas? Si es que sí, ofrecerlas en el paso 1.]
 * [PENDIENTE: ¿el CO₂ tiene certificado de calidad alimentaria? Mientras no se confirme, nunca «apto para alimentos»:
 *  el texto pide que el producto vaya envasado y que el hielo seco no toque el alimento.] */
$dipt_k20 = function_exists( 'dipt_precio_pack' ) ? (float) ( dipt_precio_pack( 20 )['kilo'] ?? 0 ) : 0;
return array(
	'slug'     => 'enviar-congelados-con-hielo-seco',
	'foto'     => 'uso-transporte', // foto de la guía (assets/img/fotos): índice y cabecera
	'formato'  => '16mm', // nuggets: más horas de frío en el envío
	'titulo'   => 'Cómo enviar congelados con hielo seco: caja, kilos y marca',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'Dónde va el hielo seco en la caja, cómo calcular los kilos para 24 o 48 horas de viaje y qué marca lleva el bulto. Para tiendas de congelados, carnicerías y catering que envían por mensajería.',
	'seo'      => array(
		'titulo'      => 'Hielo seco para envíos: kilos, caja y etiqueta | DryIcePack',
		'descripcion' => 'Cómo enviar congelados por mensajería con hielo seco: dónde va el hielo, cuántos kilos calcular y qué marca lleva la caja. Pida antes de las 12:00.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> el producto sale ya congelado y envasado, el hielo seco va encima, los huecos se rellenan y la caja aislante se cierra sin precintar. Los kilos dependen de las horas de viaje: calcúlelos con margen y, mejor aún, mídalos con un envío de prueba. Por fuera, la marca «DIÓXIDO DE CARBONO SÓLIDO, AGENTE REFRIGERANTE».</p>

<h2>¿Cómo se prepara la caja, capa a capa?</h2>
<p>De abajo arriba y en este orden. Prepárela en un sitio ventilado, con guantes térmicos y una pala o unas pinzas.</p>
<figure class="gu-corte" data-vivo data-pausa>
<svg viewBox="0 0 320 256" role="img" aria-label="Corte de la caja: el producto envasado abajo, el hielo seco encima, el relleno en los huecos y la tapa apoyada sin precintar">
<defs><pattern id="gu-corte-grano" width="7" height="7" patternUnits="userSpaceOnUse"><circle class="gu-corte__grano" cx="1.6" cy="1.6" r=".75"/><circle class="gu-corte__grano" cx="5" cy="4.6" r=".6"/></pattern><pattern id="gu-corte-pel" width="12" height="10" patternUnits="userSpaceOnUse"><circle class="gu-corte__pel" cx="3.5" cy="3.5" r="2.6"/><circle class="gu-corte__pel" cx="9.5" cy="7.5" r="2"/></pattern><pattern id="gu-corte-rel" width="16" height="10" patternUnits="userSpaceOnUse"><path class="gu-corte__rel" d="M0 7 L4 3 L7 6 L11 2 L16 6"/></pattern></defs>
<g class="gu-corte__capa" style="--i:0"><rect class="gu-corte__linea" x="12" y="44" width="296" height="194" rx="1"/><rect class="gu-corte__eps" x="22" y="70" width="276" height="160"/><rect x="22" y="70" width="276" height="160" fill="url(#gu-corte-grano)"/><rect class="gu-corte__hueco" x="40" y="70" width="240" height="142"/><path class="gu-corte__linea" d="M22 246h18M22 242v8M40 242v8"/><text class="gu-corte__cota" x="46" y="249">40 mm</text></g>
<g class="gu-corte__capa" style="--i:1"><rect class="gu-corte__prod" x="46" y="162" width="118" height="46" rx="2"/><rect class="gu-corte__prod2" x="170" y="162" width="104" height="46" rx="2"/></g>
<g class="gu-corte__capa" style="--i:2"><rect class="gu-corte__hielo" x="41" y="138" width="238" height="22"/><rect x="41" y="138" width="238" height="22" fill="url(#gu-corte-pel)"/></g>
<g class="gu-corte__capa" style="--i:3"><rect x="41" y="98" width="238" height="38" fill="url(#gu-corte-rel)"/></g>
<g class="gu-corte__capa" style="--i:4"><rect class="gu-corte__eps" x="16" y="48" width="288" height="16"/><rect x="16" y="48" width="288" height="16" fill="url(#gu-corte-grano)"/></g>
<g class="gu-corte__gas gu-corte__gas--izq"><path d="M30 66c-6 0-8 4-14 4s-6-3-12-1"/><path d="M28 72c-5 0-6 3-11 3"/></g>
<g class="gu-corte__gas gu-corte__gas--der"><path d="M290 66c6 0 8 4 14 4s6-3 12-1"/><path d="M292 72c5 0 6 3 11 3"/></g>
<g class="gu-corte__pin" style="--i:0"><circle cx="31" cy="196" r="9"/><text x="31" y="196">1</text></g>
<g class="gu-corte__pin" style="--i:1"><circle cx="105" cy="185" r="9"/><text x="105" y="185">2</text></g>
<g class="gu-corte__pin" style="--i:2"><circle cx="160" cy="149" r="9"/><text x="160" y="149">3</text></g>
<g class="gu-corte__pin" style="--i:3"><circle cx="210" cy="117" r="9"/><text x="210" y="117">4</text></g>
<g class="gu-corte__pin" style="--i:4"><circle cx="250" cy="56" r="9"/><text x="250" y="56">5</text></g>
</svg>
<ol>
<li><span class="gu-corte__n" aria-hidden="true">1</span><span><strong>Caja aislante dentro de una de cartón.</strong> Cuanto más gruesas las paredes, menos hielo se gasta: las cajas de EPS en las que servimos el hielo seco tienen 40 mm.</span></li>
<li><span class="gu-corte__n" aria-hidden="true">2</span><span><strong>Producto ya congelado y envasado.</strong> El hielo seco mantiene el frío; no está para congelar desde cero. Y nunca toca el alimento.</span></li>
<li><span class="gu-corte__n" aria-hidden="true">3</span><span><strong>Hielo seco encima.</strong> El aire frío baja: desde arriba enfría toda la carga.</span></li>
<li><span class="gu-corte__n" aria-hidden="true">4</span><span><strong>Relleno en los huecos.</strong> Papel o cartón arrugado: menos aire dentro, menos sublimación, y la carga no se mueve cuando el hielo se gasta.</span></li>
<li><span class="gu-corte__n" aria-hidden="true">5</span><span><strong>Tapa apoyada, sin cinta en las juntas.</strong> El gas tiene que salir. La cinta, solo en la caja de cartón y en el centro de la solapa.</span></li>
</ol>
</figure>
<p>Si el producto es refrigerado y no congelado, ponga un cartón entre el hielo seco y el producto para que no se congele, o valore otra opción: le contamos las diferencias en <a href="' . esc_url( home_url( '/guias/hielo-seco-o-placas-euteticas/' ) ) . '">hielo seco o placas eutécticas</a>. Para rutas de reparto con furgoneta propia, vea <a href="' . esc_url( dipt_url( 'transporte' ) ) . '">hielo seco para transporte de congelados</a>.</p>

<h2>¿Cuántos kilos de hielo seco necesita cada envío?</h2>
<p>Depende de cuatro cosas: las horas desde que cierra la caja hasta que el cliente la abre, lo que aísla la caja, lo llena que va y el calor que haga fuera. Lo hemos medido en nuestras cajas de porexpán, cerradas, a la sombra y en invierno:</p>
<table>
<thead><tr><th>Caja</th><th>Se gastó en 29 h</th><th>Unos por cada 24 h</th></tr></thead>
<tbody>
<tr><td>Pequeña (pack de 3 kg)</td><td>1,8 kg</td><td>1,5 kg</td></tr>
<tr><td>Grande (packs de 15 y 20 kg)</td><td>4,1 kg</td><td>3,4 kg</td></tr>
<tr><td>De 30 kg (empresas)</td><td>5,7 kg</td><td>4,7 kg</td></tr>
</tbody>
</table>
<p class="gu-fuente">Medición de DryIcePack en su nave de Mataró (noviembre de 2026), con pellets de 3 mm y las cajas sin abrir. Con calor o al sol se gasta más. Cuadra con la referencia publicada de la Universidad de Míchigan, <a href="https://ehs.umich.edu/wp-content/uploads/2018/04/Pkg-Req-Dry-Ice.pdf">Packaging Requirements for Dry Ice</a> (2017): de 5 a 10 libras (2,3 a 4,5 kg) cada 24 horas en una nevera aislante.</p>
<p><strong>Súmele margen.</strong> Lo que se gasta por el camino no puede ser todo lo que lleva: el producto tiene que llegar con hielo seco todavía encima. Y cuente los retrasos: un día más en un almacén son 24 horas más de consumo.</p>
<p><strong>Lo más fiable es medir su caja.</strong> Pésela al cerrarla y pida al cliente que la pese al abrirla, o envíese una a usted mismo. La diferencia es lo que gasta su caja en su ruta. Si prefiere no hacer cuentas, <a href="' . esc_url( dipt_url( 'contacto' ) ) . '">díganos la ruta y el tamaño de la caja</a> y le decimos cuántos kilos pedir. Qué hace que dure más o menos lo explicamos en <a href="' . esc_url( home_url( '/guias/cuanto-dura-el-hielo-seco/' ) ) . '">cuánto dura el hielo seco y cómo guardarlo</a>.</p>

<h2>¿Pellets de 3 mm o nuggets de 16 mm?</h2>
<p>Para envíos de 24 a 48 horas, <strong>nuggets de 16 mm</strong>: tienen más masa por pieza y duran más horas. Los <strong>pellets de 3 mm</strong> enfrían antes y rellenan mejor los huecos: sirven para envíos cortos. Los dos están a −78,5 °C y llegan en caja de EPS de 40 mm, en packs de {pk}, 10, 15 y 20 kg.' . ( $dipt_k20 > 0 ? ' En el de 20 kg, el kilo sale a ' . dipt_euros( $dipt_k20 ) . ' con IVA, más el envío.' : '' ) . '</p>
<p><a href="' . esc_url( add_query_arg( 'formato', '16mm', dipt_url( 'producto' ) ) ) . '">Comprar hielo seco en nuggets de 16 mm</a></p>

<h2>¿Qué marca lleva la caja por fuera?</h2>
<p>Por carretera, el hielo seco (número ONU 1845) solo tiene que cumplir la sección 5.5.3 del ADR 2025, el acuerdo europeo de mercancías peligrosas por carretera. Para un bulto, eso es una marca duradera, legible y bien visible (5.5.3.4):</p>
<ul>
<li><strong>Si el hielo seco enfría otra mercancía</strong>, como sus congelados: «DIÓXIDO DE CARBONO SÓLIDO, AGENTE REFRIGERANTE».</li>
<li><strong>Si lo que envía es el propio hielo seco:</strong> «DIÓXIDO DE CARBONO SÓLIDO» o «HIELO SECO».</li>
</ul>
<p>El ADR pide la marca en el idioma del país de origen y también en inglés, francés o alemán, salvo que un acuerdo entre los países del transporte diga otra cosa. El peso neto de hielo seco no lo exige el ADR por carretera, pero sí el avión: si su paquete puede volar, o si su mensajería lo pide, añádalo.</p>
<p><strong>Antes de contratar, pregunte a su mensajería</strong> si acepta bultos con hielo seco y qué le pide: cada empresa pone sus condiciones. Lo que exige cada medio, del coche al avión, está en la guía de <a href="' . esc_url( home_url( '/guias/transporte-de-hielo-seco/' ) ) . '">transporte de hielo seco: ADR, mensajería y avión</a>.</p>

<h2>¿Qué errores hacen que llegue descongelado?</h2>
<ul>
<li><strong>El hielo seco debajo del producto.</strong> El frío baja: desde abajo solo enfría el fondo de la caja.</li>
<li><strong>Producto sin congelar.</strong> El hielo seco se gasta en congelarlo y no llega para el viaje.</li>
<li><strong>Una caja medio vacía.</strong> El aire de dentro acelera la sublimación: rellene los huecos.</li>
<li><strong>Los kilos justos.</strong> Sin margen, cualquier retraso deja la caja sin frío.</li>
<li><strong>Salir justo antes del fin de semana.</strong> Si su mensajería no reparte en sábado, un envío del viernes pasa el fin de semana en un almacén. Envíe a principio de semana o cuente esas horas.</li>
<li><strong>Precintar la caja aislante o cerrar el hielo en una bolsa.</strong> El gas no sale, la presión sube y el envase puede abrirse o reventar.</li>
</ul>

<h2>¿Qué tiene que hacer quien lo recibe?</h2>
<p>Meta una nota dentro de la caja con estas cuatro líneas:</p>
<ol>
<li><strong>Abra la caja en un sitio ventilado y con guantes:</strong> el hielo seco quema la piel.</li>
<li><strong>Pase el producto al congelador enseguida.</strong> El hielo seco, nunca dentro del congelador.</li>
<li><strong>Deje que el hielo que sobre se sublime al aire libre</strong>, fuera del alcance de niños y mascotas. Nunca al fregadero, al váter ni a una papelera cerrada.</li>
<li><strong>El hielo seco no se come ni se bebe.</strong></li>
</ol>

<h2>¿Envía cada semana?</h2>
<p>Si prepara envíos todas las semanas, no tiene que pedir el hielo seco cada vez. Con una <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con pedido programado</a>, se lo dejamos programado cada semana, cada 15 días o cada mes, con mejor precio y una factura al mes. Pida antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00 en toda la península. Si está cerca de Mataró, también puede recogerlo en nuestra nave, de lunes a sábado.</p>

<h2>¿Qué precauciones hay que tomar al preparar los envíos?</h2>
<ul>
<li><strong>Guantes térmicos y pinzas o pala.</strong> A −78,5 °C quema la piel en segundos: nunca con la mano.</li>
<li><strong>Un sitio ventilado.</strong> El CO<sub>2</sub> desplaza el oxígeno: no prepare las cajas en una cámara cerrada ni en un sótano.</li>
<li><strong>Nada hermético.</strong> Ni bolsas cerradas, ni tápers, ni neveras con cierre: la presión del gas puede reventarlos.</li>
<li><strong>No se ingiere.</strong> El hielo seco nunca toca el alimento: el producto va envasado.</li>
<li><strong>Fuera del alcance de niños y mascotas</strong>, también las cajas ya preparadas.</li>
<li><strong>En el coche o la furgoneta, nunca en la cabina:</strong> en el maletero o en la zona de carga, y ventilando.</li>
</ul>
<p>Todas las normas, los primeros auxilios y la ficha de datos de seguridad están en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">seguridad del hielo seco</a>.</p>',
);
