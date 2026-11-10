<?php
/* Guía · Precio del hielo seco por kilo (ficha 5 de estrategia/SEO-CONTENIDOS.md). B2B primero, de usted.
 * Responde a «cuánto cuesta / cuánto vale el hielo seco», «precio por kilo» y «comprar hielo seco 1 kg». La ficha de producto
 * se queda «comprar hielo seco» y «hielo seco precio». Sin Product en el schema: ya lo tiene la ficha.
 * Todas las cifras salen de la tienda (dipt_packs, dipt_precio_pack, dipt_precios_recogida y la tarifa del plugin) o de los
 * marcadores de inc/idiomas.php: si cambia un precio o entra el pack de 5 kg, la guía cambia sola. Nada de rebajas ni urgencias.
 * Este archivo se incluye varias veces en cada visita (dipt_guias y dipt_guia): sin funciones con nombre y solo variables $dgp_.
 * [PENDIENTE: motivo para no vender menos de {pk} kg, en palabras del propietario (apartado «¿Se puede comprar 1 kg?»)]
 * [PENDIENTE: ¿se puede publicar el rango de descuento de empresa? Mientras tanto: «según volumen y frecuencia», sin porcentajes]
 */
$dgp_e = static function ( $n, $dec = null ) {
	$n = (float) $n;
	if ( null === $dec ) $dec = abs( $n - round( $n ) ) < 0.005 ? 0 : 2;
	return number_format( $n, $dec, ',', '.' ) . ' €';
};
$dgp_dos = static function ( $n ) use ( $dgp_e ) { return $dgp_e( $n, 2 ); };

// Packs que se venden hoy, con su precio, el envío de una caja y la recogida en Mataró
$dgp_rec   = dipt_precios_recogida();
$dgp_filas = array();
foreach ( dipt_packs() as $dgp_kg => $dgp_base ) {
	$dgp_kg = (float) $dgp_kg;
	if ( $dgp_kg <= 0 ) continue;
	$dgp_p = dipt_precio_pack( $dgp_kg );
	// Recogida: la del plugin; sin el plugin, su misma regla (precio con IVA redondeado a la baja a múltiplos de 5 €)
	$dgp_r = isset( $dgp_rec[ (string) ( 0 + $dgp_kg ) ] ) ? (float) $dgp_rec[ (string) ( 0 + $dgp_kg ) ] : max( 5, floor( round( $dgp_p['con_iva'], 2 ) / 5 ) * 5 );
	$dgp_filas[ (string) ( 0 + $dgp_kg ) ] = array( 'kg' => $dgp_kg, 'p' => $dgp_p, 'rec' => $dgp_r );
}
$dgp_pri = reset( $dgp_filas );
$dgp_ult = end( $dgp_filas );
$dgp_10  = $dgp_filas['10'] ?? $dgp_ult;
$dgp_max = $dgp_filas ? max( array_map( static fn( $f ) => (float) $f['p']['kilo'], $dgp_filas ) ) : 0; // dipt_packs() nunca viene vacío (respaldo), pero por si acaso
$dgp_kgt = static fn( $f ) => ( 0 + $f['kg'] ) . ' kg';

// Tabla: una fila por pack. La cota del precio por kilo mide lo que cuesta cada kilo frente al pack más pequeño.
$dgp_tabla = '';
$dgp_i     = 0;
foreach ( $dgp_filas as $dgp_f ) {
	$dgp_p      = $dgp_f['p'];
	$dgp_largo  = $dgp_max > 0 ? round( $dgp_p['kilo'] / $dgp_max * 100, 1 ) : 100;
	$dgp_tabla .= '<tr data-activa><th scope="row">' . $dgp_kgt( $dgp_f ) . '</th>'
		. '<td class="gu-tarifa__kilo" data-t="Precio por kilo"><b>' . $dgp_dos( $dgp_p['kilo'] ) . '</b><small>' . $dgp_dos( $dgp_p['sin_iva'] / $dgp_f['kg'] ) . ' + IVA</small>'
		. '<span class="cota" style="--largo:' . $dgp_largo . '%;--i:' . $dgp_i . '" aria-hidden="true"><span class="cota__linea"></span></span></td>'
		. '<td data-t="El pack"><b>' . $dgp_dos( $dgp_p['con_iva'] ) . '</b><small>' . $dgp_dos( $dgp_p['sin_iva'] ) . ' + IVA</small></td>'
		. '<td data-t="Con envío"><b>' . $dgp_dos( $dgp_p['total'] ) . '</b><small>' . $dgp_dos( $dgp_p['envio'] ) . ' de envío</small><small>' . $dgp_dos( $dgp_p['total'] / $dgp_f['kg'] ) . '/kg</small></td>'
		. '<td data-t="En Mataró"><b>' . $dgp_e( $dgp_f['rec'] ) . '</b><small>' . $dgp_dos( $dgp_f['rec'] / $dgp_f['kg'] ) . '/kg</small></td></tr>';
	$dgp_i++;
}

// Tarifa de la mensajería (plugin; respaldo del 31/08/2026), sin IVA
$dgp_t5  = defined( 'DIP_TARIFA_HASTA_5' ) ? DIP_TARIFA_HASTA_5 : 10.32;
$dgp_t10 = defined( 'DIP_TARIFA_HASTA_10' ) ? DIP_TARIFA_HASTA_10 : 13.19;
$dgp_tkg = defined( 'DIP_TARIFA_KG_EXTRA' ) ? DIP_TARIFA_KG_EXTRA : 1.12;
$dgp_ivp = (int) round( ( dipt_factor_iva() - 1 ) * 100 );

// Recogida de todos los packs: «3 kg por 35 €, 10 kg por 60 €, 15 kg por 90 € y 20 kg por 115 €»
$dgp_lista_rec = array_map( static fn( $f ) => ( 0 + $f['kg'] ) . ' kg por ' . number_format( (float) $f['rec'], 0, ',', '.' ) . ' €', array_values( $dgp_filas ) );
$dgp_ult_rec   = array_pop( $dgp_lista_rec );
$dgp_lista_rec = ( $dgp_lista_rec ? implode( ', ', $dgp_lista_rec ) . ' y ' : '' ) . $dgp_ult_rec;

// Otras guías (otras fichas): solo se enlazan si ya están publicadas en el tema
$dgp_guia = static fn( $slug, $texto ) => file_exists( __DIR__ . '/' . $slug . '.php' ) ? '<a href="' . esc_url( home_url( '/guias/' . $slug . '/' ) ) . '">' . $texto . '</a>' : '';
$dgp_dura = $dgp_guia( 'cuanto-dura-el-hielo-seco', 'cuánto dura el hielo seco y cómo guardarlo' );
$dgp_dura = $dgp_dura ? ' ¿Cuánto le va a durar? Lo explicamos en ' . $dgp_dura . '.' : '';
$dgp_env  = array_filter( array( $dgp_guia( 'enviar-congelados-con-hielo-seco', 'cómo enviar congelados' ), $dgp_guia( 'enviar-muestras-con-hielo-seco', 'cómo enviar muestras con hielo seco' ) ) );
$dgp_env  = $dgp_env ? '<p>¿Lo usa para envíos? Cuántos kilos pide cada caja, en ' . implode( ' y en ', $dgp_env ) . '.</p>' : '';

return array(
	'slug'     => 'precio-del-hielo-seco',
	'foto'     => 'caja-16mm-lateral', // foto de la guía (assets/img/fotos): índice y cabecera
	'titulo'   => '¿Cuánto cuesta el hielo seco? Precio por kilo, envío y recogida',
	'cta'      => '¿Lo necesita pronto? Pídalo antes de las 12:00.',
	'extracto' => 'El kilo baja con el tamaño del pack. Precios con y sin IVA, el envío, la recogida en Mataró y qué hace bajar el precio en una cuenta de empresa.',
	'seo'      => array(
		'titulo'      => 'Precio del hielo seco por kilo: qué incluye | DryIcePack',
		// Con IVA (la cifra que paga un particular), 143 caracteres hoy y también con el pack de 5 kg
		'descripcion' => 'Precio del hielo seco: el kilo va de ' . $dgp_dos( $dgp_pri['p']['kilo'] ) . ' en el pack de ' . $dgp_kgt( $dgp_pri ) . ' a ' . $dgp_dos( $dgp_ult['p']['kilo'] ) . ' en el de ' . $dgp_kgt( $dgp_ult ) . ', IVA incluido. Mire qué incluye y cuánto suma el envío.',
	),
	'contenido' => '<p class="resumen"><strong>En resumen:</strong> el precio del hielo seco va de ' . $dgp_dos( $dgp_pri['p']['kilo'] ) . ' el kilo en el pack de ' . $dgp_kgt( $dgp_pri ) . ' a ' . $dgp_dos( $dgp_ult['p']['kilo'] ) . ' en el de ' . $dgp_kgt( $dgp_ult ) . ', IVA incluido (de ' . $dgp_dos( $dgp_pri['p']['sin_iva'] / $dgp_pri['kg'] ) . ' a ' . $dgp_dos( $dgp_ult['p']['sin_iva'] / $dgp_ult['kg'] ) . ' + IVA). Es el mismo en pellets de 3 mm y en nuggets de 16 mm, y lleva la caja de EPS. El envío se suma aparte, según el peso. Si lo recoge en Mataró, no hay envío.</p>

<h2>¿Cuánto cuesta cada pack?</h2>
<p>Son los precios de la tienda. En cada fila tiene el precio del kilo, el del pack, el total con el envío a domicilio y el precio si lo recoge en nuestra nave de Mataró.</p>
<div class="gu-tarifa" data-revela>
<table>
<caption>Hielo seco en pellets de 3 mm o nuggets de 16 mm, al mismo precio, con la caja de EPS de 40 mm incluida.</caption>
<thead><tr><th scope="col">Pack</th><th scope="col">Precio por kilo</th><th scope="col">Precio del pack</th><th scope="col">Con envío a domicilio</th><th scope="col">Recogida en Mataró</th></tr></thead>
<tbody data-activa-grupo>' . $dgp_tabla . '</tbody>
<tfoot><tr><td colspan="5">Precios con IVA salvo donde dice «+ IVA». Envío de una caja a la península: no enviamos a Baleares, Canarias, Ceuta ni Melilla. La recogida se paga en efectivo, con el importe exacto.</td></tr></tfoot>
</table>
</div>
<p>Para pedir, elija formato y kilos en la página de <a href="' . esc_url( dipt_url( 'producto' ) ) . '">compra de hielo seco en 3 mm o 16 mm</a>: ve el total con el envío antes de pagar.</p>

<h2>¿Qué incluye el precio?</h2>
<ul>
<li><strong>El hielo seco</strong>, en pellets de 3 mm o en nuggets de 16 mm. Los dos formatos cuestan lo mismo.</li>
<li><strong>La caja de EPS de 40 mm</strong>, con su tapa. Aísla el hielo seco y frena la sublimación hasta que lo use.</li>
<li><strong>El IVA (' . $dgp_ivp . ' %)</strong> se suma al precio del pack. En la tabla tiene las dos cifras.</li>
<li><strong>El envío va aparte</strong> y se calcula con el peso del hielo más 1 kg por caja. La tarifa es la misma en toda la península: hasta 5 kg, ' . $dgp_dos( $dgp_t5 ) . ' + IVA; hasta 10 kg, ' . $dgp_dos( $dgp_t10 ) . ' + IVA; cada kilo de más, ' . $dgp_dos( $dgp_tkg ) . ' + IVA.</li>
</ul>
<p>Si pide antes de las 12:00, de lunes a jueves, llega al día siguiente antes de las 14:00. La entrega en sábado, según zona, lleva un suplemento de {sabado_envio}, IVA incluido. Días, festivos y sábados, en <a href="' . esc_url( dipt_url( 'envios' ) ) . '">envíos y plazos de entrega</a>.</p>

<h2>¿Por qué baja el precio por kilo?</h2>
<p>Porque una parte del coste va por caja, no por kilo: la caja de EPS y el envío. Cuantos más kilos lleva la caja, menos le toca a cada uno. Con el envío y el IVA, el kilo pasa de ' . $dgp_dos( $dgp_pri['p']['total'] / $dgp_pri['kg'] ) . ' en el pack de ' . $dgp_kgt( $dgp_pri ) . ' a ' . $dgp_dos( $dgp_ult['p']['total'] / $dgp_ult['kg'] ) . ' en el de ' . $dgp_kgt( $dgp_ult ) . '.</p>
<p>Cuatro formas de pagar menos por kilo:</p>
<ol>
<li><strong>Un pack más grande.</strong> El salto mayor está entre el pack de ' . $dgp_kgt( $dgp_pri ) . ' y el de ' . $dgp_kgt( $dgp_10 ) . ': el kilo pasa de ' . $dgp_dos( $dgp_pri['p']['kilo'] ) . ' a ' . $dgp_dos( $dgp_10['p']['kilo'] ) . ', IVA incluido.</li>
<li><strong>Todo en un mismo pedido.</strong> El envío se calcula con el peso total: por encima de 10 kg, cada kilo de más suma ' . $dgp_dos( $dgp_tkg ) . ' + IVA. Dos pedidos pagan dos envíos.</li>
<li><strong>Recogerlo en Mataró.</strong> Sin envío y con un precio redondo, IVA incluido: {recogida:10} los 10 kg.</li>
<li><strong>Una cuenta de empresa.</strong> Si lo usa cada semana, cada quince días o cada mes, el contrato de suministro lleva un descuento sobre tarifa según volumen y frecuencia.</li>
</ol>

<h2>¿Hay precio para empresas?</h2>
<p>Sí. Si compra hielo seco a menudo, abra una <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa con suministro programado</a>:</p>
<ul>
<li><strong>Descuento sobre tarifa</strong> según volumen y frecuencia, por escrito en un contrato de suministro.</li>
<li><strong>Suministro programado:</strong> el mismo pedido cada semana, cada quince días o cada mes, el día acordado.</li>
<li><strong>Una factura al mes</strong>, a 30 días, por domiciliación SEPA o transferencia.</li>
<li><strong>Datos guardados:</strong> razón social, NIF y direcciones de entrega, una sola vez.</li>
</ul>
<p>En un pedido suelto también tiene factura completa: al pagar, marque «Compro para una empresa o autónomo» y escriba la razón social y el NIF. En la web, hasta 250 kg por pedido. Con más de 150 kg fuera de la provincia de Barcelona, el envío se prepara a medida y sale más caro. Para eso, o para más de 250 kg, escríbanos: le contestamos con precio y día, normalmente el mismo día laborable.</p>
' . $dgp_env

. '
<h2>¿Cuánto cuesta recogerlo en Mataró?</h2>
<p>En nuestra nave (Camí Ca La Madrona 19 D, Mataró) el precio es redondo, con IVA incluido, y no hay envío: ' . $dgp_lista_rec . '. Es el mismo precio si lo reserva en la web o si lo pide en la nave.</p>
<ul>
<li><strong>De lunes a sábado</strong>, también en festivos. Elija el día al pedir y le confirmamos la hora por teléfono o WhatsApp.</li>
<li><strong>El sábado, +{sabado_recogida}</strong>, IVA incluido.{sabado_libre}</li>
<li><strong>Solo en efectivo y con el importe exacto:</strong> no tenemos cambio.</li>
</ul>
<p>Cómo llegar y qué zonas quedan cerca: <a href="' . esc_url( dipt_url( 'zona-mataro' ) ) . '">recogida de hielo seco en Mataró</a>.</p>

<h2>¿Se puede comprar 1 kg de hielo seco?</h2>
<p>No. El pack más pequeño es de {pk} kg: {precio:pk} con IVA, o {recogida:pk} si lo recoge en Mataró. Si necesita poco, pídalo para el mismo día que lo va a usar: el hielo seco se sublima con las horas. Lo que le sobre, déjelo sublimar al aire libre, fuera del alcance de niños y mascotas. Nunca al fregadero, al váter ni a una papelera cerrada.' . $dgp_dura . '</p>

<h2>¿Cómo se comparan precios de hielo seco?</h2>
<p>El precio del kilo, solo, dice poco. Compare con estos cinco datos:</p>
<ul>
<li><strong>Con IVA o sin IVA.</strong> Unas tarifas lo incluyen y otras no.</li>
<li><strong>Con el envío.</strong> Lo que cuenta es el total que paga por tenerlo en su puerta.</li>
<li><strong>Con la caja.</strong> ¿Llega en caja aislante? ¿De qué grosor? La nuestra es de EPS de 40 mm.</li>
<li><strong>Con el día de entrega.</strong> El hielo seco se sublima desde que se prepara: lo que importa son los kilos que tiene el día que lo usa. Pídalo para ese día.</li>
<li><strong>Con la factura.</strong> Si compra para una empresa, que le den factura completa y, si compra a menudo, una al mes.</li>
</ul>

<h2>¿Qué precauciones hay que tener al recibirlo?</h2>
<p>El hielo seco está a −78,5 °C. Al recibirlo y mientras lo use:</p>
<ul>
<li><strong>Guantes térmicos y pinzas.</strong> Nunca lo toque con la mano.</li>
<li><strong>Un sitio ventilado:</strong> el CO<sub>2</sub> desplaza el oxígeno.</li>
<li><strong>En su caja, con la tapa sin precintar.</strong> En un recipiente hermético, la presión puede reventarlo. Nunca en el congelador ni en la nevera.</li>
<li><strong>No se come ni se bebe.</strong> En bebidas, siempre con doble recipiente.</li>
<li><strong>Fuera del alcance de niños y mascotas.</strong></li>
<li><strong>En el coche, en el maletero</strong> y ventilando.</li>
</ul>
<p>Todas las normas y la ficha de datos de seguridad, en <a href="' . esc_url( dipt_url( 'seguridad' ) ) . '">cómo manipular el hielo seco con seguridad</a>.</p>',
);
