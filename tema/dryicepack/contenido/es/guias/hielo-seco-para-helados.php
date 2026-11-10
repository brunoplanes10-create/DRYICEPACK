<?php
/* Guía · Hielo seco para helados (ficha 10 de estrategia/SEO-CONTENIDOS.md, como guía y no como landing). De usted.
 * Responde a «hielo seco para helados», «para mantener / transportar / vender helados» y «helados de hielo seco» (que no se hacen:
 * no se ingiere). Sin clientes heladeros inventados y sin kilos por jornada propios: solo la pauta de envíos de la Universidad
 * de Oregón (2,27–4,54 kg por cada 24 h, la misma que cita la guía de duración) y la densidad del CO2 de la ficha de PanGas.
 * La cifra de UPS se quitó (10/11/2026): su página no se pudo leer y no se puede comprobar.
 * Sin «apto para alimentos»: el hielo seco no toca el helado (cartón en medio, envase cerrado).
 * [PENDIENTE: confirmar con un heladero cómo se monta en un carrito de helado a bola (cubetas abiertas): por ahora la guía
 *  habla solo de helado envasado en la venta ambulante]
 * Pieza: corte de la caja de reparto (.gu-plano--corte en guia.css), con el lenguaje de la lámina de montajes de la guía de bebidas.
 * Este archivo se incluye varias veces en cada visita: sin funciones con nombre y solo variables $dgh_.
 * [PENDIENTE: ¿hay clientes heladeros? Kilos por jornada y por ruta, y permiso para citarlos]
 * [PENDIENTE: ¿el CO2 tiene certificado de calidad alimentaria? Hasta saberlo, no decir «apto para alimentos»]
 */
// Envíos por mensajería: la guía de congelados (otra ficha) si ya está publicada; si no, la página de transporte
$dgh_congelados = file_exists( __DIR__ . '/enviar-congelados-con-hielo-seco.php' )
	? '<a href="' . esc_url( home_url( '/guias/enviar-congelados-con-hielo-seco/' ) ) . '">cómo enviar congelados con hielo seco</a>'
	: '<a href="' . esc_url( dipt_url( 'transporte' ) ) . '">hielo seco para transporte de congelados</a>';
// Guía de duración (otra ficha), si ya está publicada
$dgh_dura = file_exists( __DIR__ . '/cuanto-dura-el-hielo-seco.php' ) ? ' Qué lo hace durar más, en <a href="' . esc_url( home_url( '/guias/cuanto-dura-el-hielo-seco/' ) ) . '">cuánto dura el hielo seco</a>.' : '';

return array(
	'slug'     => 'hielo-seco-para-helados',
	'foto'     => 'reparto-furgoneta', // foto de la guía (assets/img/fotos): índice y cabecera
	'formato'  => '16mm', // formato que recomienda la guía: acceso directo en la nota al margen
	'titulo'   => 'Hielo seco para helados: reparto, eventos y venta ambulante',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'Cómo mantener el helado congelado en el reparto, en una feria o en un puesto de venta con nuggets de 16 mm: cómo se coloca en la caja, cuánto pedir y lo que no se debe hacer.',
	'seo'      => array(
		'titulo'      => 'Hielo seco para helados: transporte y venta | DryIcePack',
		'descripcion' => 'Hielo seco para que el helado llegue congelado: reparto, eventos y venta ambulante, en nuggets de 16 mm. Pida antes de las 12:00 y llega al día siguiente.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> el hielo seco mantiene el helado congelado donde no llega el congelador: en la furgoneta de reparto, en una feria o en un puesto de venta. Va en la caja <strong>encima del helado, con un cartón en medio</strong>, porque el frío baja. Para que dure más horas, nuggets de 16 mm. Y nunca toca lo que se sirve: el hielo seco no se come.</p>

<h2>¿Para qué usa hielo seco una heladería?</h2>
<ul>
<li><strong>Reparto</strong> a domicilio, a restaurantes o a otras tiendas: el helado aguanta congelado toda la ruta si lleva hielo seco para esas horas.</li>
<li><strong>Ferias, eventos y venta ambulante</strong> de helado envasado (polos, tarrinas): donde no hay enchufe para un congelador, o como apoyo al que hay.</li>
<li><strong>Envíos por mensajería</strong> a otra ciudad, en caja aislante. Lo explicamos en ' . $dgh_congelados . '.</li>
<li><strong>Una avería del arcón o de la vitrina,</strong> hasta que llegue el técnico. Tape cada cubeta con su tapa o con film, ponga encima un cartón y, sobre él, el hielo seco. La tapa del arcón, apoyada, sin cerrar herméticamente. El local, ventilado, y el hielo seco fuera del alcance del público.</li>
</ul>

<h2>¿Cómo se mantiene el helado congelado en la caja?</h2>
<p>De abajo arriba: helado, cartón, hielo seco y tapa. El CO<sub>2</sub> frío pesa una vez y media lo que el aire (ficha de PanGas, grupo Linde) y baja, así que el hielo seco va siempre encima.</p>
<figure class="gu-plano gu-plano--corte" data-pausa data-revela><svg class="pl-svg pl-svg--caja" viewBox="0 66 400 224" width="400" height="224" focusable="false" aria-hidden="true"><path class="pl-suelo" d="M10 272H390"/><path class="pl-eps" d="M60 100H340V272H60Z"/><path class="pl-hueco" d="M74 114H326V258H74Z"/><g class="pl-capa pl-capa--helado"><path class="pl-helado" d="M88 198H156L152 258H92Z"/><path class="pl-vidrio" d="M86 192H158V198H86Z"/><path class="pl-helado" d="M166 198H234L230 258H170Z"/><path class="pl-vidrio" d="M164 192H236V198H164Z"/><path class="pl-helado" d="M244 198H312L308 258H248Z"/><path class="pl-vidrio" d="M242 192H314V198H242Z"/></g><g class="pl-capa pl-capa--carton"><path class="pl-carton" d="M78 178H322V186H78Z"/></g><g class="pl-capa pl-capa--hielo"><g class="pl-nuggets"><rect x="75.7" y="161" width="16" height="8" rx="4" transform="rotate(9 83.7 165)"/><rect x="96.4" y="162.9" width="16" height="8" rx="4" transform="rotate(21 104.4 166.9)"/><rect x="113.2" y="160.6" width="16" height="8" rx="4" transform="rotate(4 121.2 164.6)"/><rect x="132" y="163.3" width="16" height="8" rx="4" transform="rotate(24 140 167.3)"/><rect x="150.8" y="160.6" width="16" height="8" rx="4" transform="rotate(-25 158.8 164.6)"/><rect x="167.5" y="161.5" width="16" height="8" rx="4" transform="rotate(-9 175.5 165.5)"/><rect x="185.3" y="161.6" width="16" height="8" rx="4" transform="rotate(-2 193.3 165.6)"/><rect x="203.9" y="161" width="16" height="8" rx="4" transform="rotate(-22 211.9 165)"/><rect x="224" y="161.5" width="16" height="8" rx="4" transform="rotate(-23 232 165.5)"/><rect x="240.8" y="162.2" width="16" height="8" rx="4" transform="rotate(-5 248.8 166.2)"/><rect x="261.6" y="162.1" width="16" height="8" rx="4" transform="rotate(-1 269.6 166.1)"/><rect x="281.1" y="162.4" width="16" height="8" rx="4" transform="rotate(-18 289.1 166.4)"/><rect x="298.2" y="162.5" width="16" height="8" rx="4" transform="rotate(13 306.2 166.5)"/><rect x="83.6" y="151" width="16" height="8" rx="4" transform="rotate(-17 91.6 155)"/><rect x="102.8" y="152" width="16" height="8" rx="4" transform="rotate(15 110.8 156)"/><rect x="122.9" y="150.9" width="16" height="8" rx="4" transform="rotate(-16 130.9 154.9)"/><rect x="139.9" y="152.6" width="16" height="8" rx="4" transform="rotate(15 147.9 156.6)"/><rect x="160.6" y="152.7" width="16" height="8" rx="4" transform="rotate(-3 168.6 156.7)"/><rect x="177.2" y="150.6" width="16" height="8" rx="4" transform="rotate(-19 185.2 154.6)"/><rect x="195" y="152.8" width="16" height="8" rx="4" transform="rotate(14 203 156.8)"/><rect x="214.5" y="152.9" width="16" height="8" rx="4" transform="rotate(-21 222.5 156.9)"/><rect x="233.9" y="152.3" width="16" height="8" rx="4" transform="rotate(-10 241.9 156.3)"/><rect x="252.7" y="151.5" width="16" height="8" rx="4" transform="rotate(12 260.7 155.5)"/><rect x="270.5" y="153.4" width="16" height="8" rx="4" transform="rotate(-16 278.5 157.4)"/><rect x="289.3" y="152.7" width="16" height="8" rx="4" transform="rotate(8 297.3 156.7)"/><rect x="75.8" y="140.9" width="16" height="8" rx="4" transform="rotate(-7 83.8 144.9)"/><rect x="96.2" y="142.2" width="16" height="8" rx="4" transform="rotate(-13 104.2 146.2)"/><rect x="111.4" y="143.1" width="16" height="8" rx="4" transform="rotate(-1 119.4 147.1)"/><rect x="132.5" y="140.8" width="16" height="8" rx="4" transform="rotate(14 140.5 144.8)"/><rect x="148.3" y="141.7" width="16" height="8" rx="4" transform="rotate(-6 156.3 145.7)"/><rect x="167.9" y="142" width="16" height="8" rx="4" transform="rotate(-1 175.9 146)"/><rect x="188.7" y="140.7" width="16" height="8" rx="4" transform="rotate(17 196.7 144.7)"/><rect x="207" y="142.5" width="16" height="8" rx="4" transform="rotate(23 215 146.5)"/><rect x="224.6" y="142.5" width="16" height="8" rx="4" transform="rotate(-17 232.6 146.5)"/><rect x="243.8" y="141.2" width="16" height="8" rx="4" transform="rotate(3 251.8 145.2)"/><rect x="262.3" y="141.8" width="16" height="8" rx="4" transform="rotate(21 270.3 145.8)"/><rect x="279.6" y="141.9" width="16" height="8" rx="4" transform="rotate(-19 287.6 145.9)"/><rect x="299.6" y="141.3" width="16" height="8" rx="4" transform="rotate(-3 307.6 145.3)"/><rect x="85.9" y="132.5" width="16" height="8" rx="4" transform="rotate(-20 93.9 136.5)"/><rect x="104" y="132.6" width="16" height="8" rx="4" transform="rotate(-21 112 136.6)"/><rect x="121.6" y="132.3" width="16" height="8" rx="4" transform="rotate(11 129.6 136.3)"/><rect x="141.8" y="131.4" width="16" height="8" rx="4" transform="rotate(-20 149.8 135.4)"/><rect x="158.1" y="131" width="16" height="8" rx="4" transform="rotate(-23 166.1 135)"/><rect x="175.5" y="131.7" width="16" height="8" rx="4" transform="rotate(-3 183.5 135.7)"/><rect x="196.7" y="132.4" width="16" height="8" rx="4" transform="rotate(9 204.7 136.4)"/><rect x="215.3" y="133.3" width="16" height="8" rx="4" transform="rotate(19 223.3 137.3)"/><rect x="231.9" y="131.3" width="16" height="8" rx="4" transform="rotate(16 239.9 135.3)"/><rect x="251.6" y="131.1" width="16" height="8" rx="4" transform="rotate(-15 259.6 135.1)"/><rect x="269.5" y="132.7" width="16" height="8" rx="4" transform="rotate(-15 277.5 136.7)"/><rect x="289.6" y="132.9" width="16" height="8" rx="4" transform="rotate(4 297.6 136.9)"/></g></g><g class="pl-capa pl-capa--tapa"><path class="pl-eps" d="M54 80H346V98H54Z"/></g><g class="pl-humo"><path style="--k:0" d="M55 99c-6 1 -9 7 -8 14s4 10 -1 18"/><path style="--k:2" d="M55 99c-6 1 -9 7 -8 14s4 10 -1 18"/><path style="--k:4" d="M55 99c-6 1 -9 7 -8 14s4 10 -1 18"/><path style="--k:1" d="M345 99c6 1 9 7 8 14s-4 10 1 18"/><path style="--k:3" d="M345 99c6 1 9 7 8 14s-4 10 1 18"/><path style="--k:5" d="M345 99c6 1 9 7 8 14s-4 10 1 18"/></g><path class="pl-halo" d="M318 89H362"/><path class="pl-guia" d="M318 89H362"/><circle class="pl-punto" cx="318" cy="89" r="3.5"/><text class="pl-num" x="370" y="94">4</text><path class="pl-halo" d="M292 146H362"/><path class="pl-guia" d="M292 146H362"/><circle class="pl-punto" cx="292" cy="146" r="3.5"/><text class="pl-num" x="370" y="151">3</text><path class="pl-halo" d="M312 182H362"/><path class="pl-guia" d="M312 182H362"/><circle class="pl-punto" cx="312" cy="182" r="3.5"/><text class="pl-num" x="370" y="187">2</text><path class="pl-halo" d="M290 228H362"/><path class="pl-guia" d="M290 228H362"/><circle class="pl-punto" cx="290" cy="228" r="3.5"/><text class="pl-num" x="370" y="233">1</text></svg><figcaption class="gu-plano__leyenda">Caja de reparto en corte. En azul marino, el helado en su envase; encima, el hielo seco en nuggets de 16 mm.</figcaption></figure>
<ol>
<li><strong>El helado abajo, ya congelado y en su envase cerrado.</strong> El hielo seco mantiene el frío: cargue el helado congelado, no blando.</li>
<li><strong>Un cartón encima del helado.</strong> A −78,5 °C, el hielo seco enfría lo que toca muy por debajo de su temperatura de venta. El cartón evita el contacto.</li>
<li><strong>El hielo seco encima, en nuggets de 16 mm,</strong> repartido por toda la superficie. Rellene los huecos con papel o cartón: con menos aire dentro, el frío dura más.</li>
<li><strong>La tapa puesta, sin cinta en todas las juntas.</strong> El hielo seco pasa a gas y el gas tiene que salir: en una caja hermética, la presión puede reventarla.</li>
</ol>
<p>Use una caja aislante, como las de EPS.</p>

<h2>¿3 mm o 16 mm?</h2>
<p><strong>Nuggets de 16 mm.</strong> Tienen más masa por pieza y aguantan más horas en la caja, que es lo que pide un reparto o una jornada de venta. Los pellets de 3 mm enfrían antes, pero también se acaban antes: sirven para trayectos cortos y para el efecto de niebla.</p>

<h2>¿Cuánto hielo seco necesito?</h2>
<p>Depende de las horas, del tamaño de la caja, de cuántas veces se abra y del calor que haga. Como referencia para envíos, la <a href="https://safety.uoregon.edu/sites/default/files/dry_ice_shipping_guide.pdf" rel="noopener">Universidad de Oregón</a> recomienda de 2,27 a 4,54 kg de hielo seco por cada 24 horas de viaje en una caja aislante. Es una pauta general, no una medida de nuestras cajas. Un puesto que abre la caja en cada venta gasta más.' . $dgh_dura . '</p>
<p>Escríbanos por WhatsApp o desde la página de <a href="' . esc_url( dipt_url( 'contacto' ) ) . '">contacto</a> con las horas, la caja y cuántas veces se abre, y se lo calculamos. Los packs de la tienda son de {pk}, 10, 15 y 20 kg. Con una cuenta de empresa, cada caja lleva hasta 30 kg.</p>

<h2>¿Cuándo hay que pedirlo?</h2>
<ul>
<li><strong>Para el día que lo va a usar o el anterior.</strong> El hielo seco se sublima con las horas: pedirlo antes es pagar kilos que no llegará a usar.</li>
<li><strong>Antes de las 12:00, de lunes a jueves:</strong> llega al día siguiente antes de las 14:00. Si pide el viernes antes de las 12:00, llega el sábado antes de las 12:00, según zona y con un suplemento de {sabado_envio} (IVA incluido), o el martes. Domingo y lunes no hay reparto.</li>
<li><strong>¿Feria el domingo o el lunes?</strong> Recójalo en nuestra nave de Mataró el sábado o el mismo lunes: puede recogerlo de lunes a sábado, también en festivos, y la hora se la confirmamos por teléfono o WhatsApp. {pk} kg por {recogida:pk} y 10 kg por {recogida:10}, IVA incluido, en efectivo y con el importe exacto. El sábado, +{sabado_recogida}.</li>
<li><strong>En temporada:</strong> con una cuenta de empresa se lo programamos cada semana, cada quince días o cada mes, con descuento sobre tarifa según volumen y una factura al mes.</li>
</ul>
<p>Días, festivos y sábados, en <a href="' . esc_url( dipt_url( 'envios' ) ) . '">envíos y plazos de entrega</a>. La recogida, en <a href="' . esc_url( dipt_url( 'zona-mataro' ) ) . '">hielo seco en Mataró</a>. Las condiciones de la cuenta, en <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con suministro programado</a>.</p>

<h2>¿Qué no se debe hacer?</h2>
<ul>
<li><strong>No se come.</strong> Nunca un trozo de hielo seco en el helado, en la copa ni como decoración con humo.</li>
<li><strong>Nunca helado hecho con hielo seco.</strong> Hay recetas que lo mezclan con la nata: no las siga. El hielo seco no se ingiere y un trozo sin sublimar quema por dentro.</li>
<li><strong>Que no toque el helado</strong> ni un envase abierto: siempre el cartón en medio.</li>
<li><strong>Nada hermético:</strong> ni neveras que cierren herméticamente ni bolsas cerradas. La presión puede reventarlas.</li>
<li><strong>Con guantes térmicos y pinzas.</strong> Nunca con la mano.</li>
<li><strong>Fuera del alcance del público, de niños y de mascotas.</strong> En un puesto, la caja detrás del mostrador.</li>
<li><strong>En la furgoneta, en la zona de carga</strong>, separada de la cabina, y abra las puertas un momento antes de meterse dentro: el CO<sub>2</sub> desplaza el oxígeno. En un coche, en el maletero y ventilando.</li>
<li><strong>Para guardarlo, ni congelador ni nevera:</strong> en su caja, con la tapa sin precintar.</li>
</ul>
<p>En un kiosco o una caseta, deje una puerta o una ventana abierta. Para la hostelería en general, mire <a href="' . esc_url( dipt_url( 'hosteleria' ) ) . '">hielo seco para hostelería</a>. Todas las normas, en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">cómo manipular el hielo seco con seguridad</a>.</p>',
);
