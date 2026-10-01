<?php
/* Envíos y plazos · castellano */
return array(
	'menu' => 'Envíos y plazos',
	'seo'  => array(
		'titulo'      => 'Envío de hielo seco en 24 h a toda la península | DryIcePack',
		'descripcion' => 'Pide antes de las 12:00 y el hielo seco sale hoy y llega mañana por la mañana. Tarifa por peso, sábado según zona y recogida gratis en Mataró.',
	),
	'migas'  => array( 'Inicio', 'Envíos y plazos' ),
	'hero'   => array(
		'titulo'   => 'Envíos y plazos|del <em>hielo seco.</em>',
		'reloj'    => array( 'antes' => 'Tiempo para el corte de hoy', 'despues' => 'El corte de hoy ya ha pasado' ),
		'llega'    => array( 'antes' => 'Si pides ahora, llega el %s por la mañana.', 'despues' => 'Si pides ahora, llega el %s por la mañana.' ),
		'texto'    => 'Lo que entra antes de las 12:00, de lunes a viernes, sale ese día y llega al siguiente por la mañana.',
	),
	'semana' => array(
		'titulo' => 'Qué día llega,|según <em>cuándo pidas.</em>',
		'dias'   => array( 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo' ),
		'pides'  => 'Pides antes de las 12:00',
		'llega'  => 'Llega',
		'filas'  => array(
			array( 0, 1, '' ),
			array( 1, 2, '' ),
			array( 2, 3, '' ),
			array( 3, 4, '' ),
			array( 4, 5, 'Sábado según zona, +11,74 €. Si no, el martes.' ),
		),
		'finde'  => 'Pedidos del sábado y el domingo, o del viernes después de las 12:00: salen el lunes y llegan el martes.',
		'festivos' => 'Los festivos de Cataluña no salen pedidos. En el pago solo aparecen los días que se pueden elegir.',
	),
	'tarifa' => array(
		'titulo' => 'Mueve los kilos,|mira <em>el envío.</em>',
		'kg'     => 'Kilos de hielo',
		'cajas'  => 'Cajas',
		'peso'   => 'Peso que factura la mensajería: %s kg',
		'coste'  => 'Envío',
		'nota'   => 'IVA incluido. El peso facturable es el hielo más 1 kg por caja, redondeado hacia arriba. Máximo 250 kg por pedido online.',
		'tramos' => array( 'hasta 2 kg', 'hasta 5 kg', 'hasta 10 kg', 'más de 10 kg' ),
	),
	'recogida' => array(
		'titulo' => 'Recogida|en <em>Mataró.</em>',
		'lista'  => array(
			array( 'Dónde', 'Camí Ca La Madrona 19 D, 08304 Mataró (Barcelona). Es nuestra nave, no una oficina de la mensajería.' ),
			array( 'Cuándo', 'De lunes a sábado, también festivos. Elige el día al pagar y te confirmamos la hora por teléfono o WhatsApp. El sábado tiene un suplemento de 11,74 € (IVA incluido).' ),
			array( 'Cuánto', 'Gratis. Pagas solo el hielo, en efectivo al recogerlo.' ),
			array( 'Cómo llevarlo', 'En el maletero y con ventilación. Nunca en el habitáculo cerrado.' ),
		),
		'alt'    => 'Operario con un cazo de pellets de hielo seco en el almacén',
		'como'   => 'Cómo llegar',
	),
	'cobertura' => array(
		'titulo' => '¿Llega a mi zona?',
		'si'     => array( 'titulo' => 'Sí, al día siguiente por la mañana', 'texto' => 'Toda la España peninsular: de Girona a Huelva y de A Coruña a Murcia.' ),
		'no'     => array( 'titulo' => 'Consúltanos', 'texto' => 'Illes Balears, Canarias, Ceuta, Melilla y fuera de España. El hielo seco viaja como mercancía peligrosa (UN 1845) y no podemos asegurar la entrega en 24 h.' ),
		'zonas'  => 'Ver zonas de entrega cerca de Barcelona',
	),
	'faq' => array(
		'titulo' => 'Dudas de envío',
		'lista'  => array(
			array( '¿Cómo va embalado?', 'En caja de EPS de 40 mm de pared, cerrada con su tapa pero no hermética, para que el CO₂ pueda salir durante el viaje.' ),
			array( '¿Llega el mismo peso que pido?', 'El hielo seco se sublima desde que sale de la nave, así que durante el viaje pierde algo de peso. Por eso sale el mismo día que se prepara y llega a la mañana siguiente: el menor tiempo posible en camino.' ),
			array( '¿Y si no estoy cuando llega?', 'La mensajería aplica sus reintentos. Avísanos cuanto antes para seguir el envío: el hielo no espera.' ),
			array( '¿Puedo pedir una hora concreta?', 'El reparto es por la mañana y la hora exacta la marca la ruta de la mensajería. Si necesitas una hora fija, la recogida en Mataró te da control total.' ),
			array( '¿Hacéis envíos urgentes el mismo día?', 'No por mensajería. Si estás cerca de Mataró, puedes recogerlo el mismo día si pides antes de las 12:00.' ),
		),
	),
);
