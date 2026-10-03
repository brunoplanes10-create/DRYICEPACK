<?php
/* Hielo seco para transporte en frío · castellano · usted */
return array(
	'menu' => 'Hielo seco para transporte',
	'seo'  => array(
		'titulo'      => 'Hielo seco para transporte de congelados | DryIcePack',
		'descripcion' => 'Hielo seco para transporte de congelados y refrigerados: a −78,5 °C y sin agua en la caja. Pida antes de las 12:00 y llega mañana por la mañana.',
	),
	'migas' => array( 'Inicio', 'Usos', 'Transporte en frío' ),
	'hero'  => array(
		'titulo' => 'Hielo seco para transporte de congelados,|<em>mañana en su almacén y sin agua en la caja.</em>',
		'texto'  => 'A −78,5 °C, pasa de sólido a gas sin fundirse y mantiene el frío durante la ruta. Pida antes de las 12:00 y mañana por la mañana lo tiene (de martes a sábado), o elija el día que quiera. Con cuenta de empresa, una factura al mes.',
		'boton'  => 'Abrir mi cuenta',
		'datos'  => array( '−78,5 °C', '16 mm', 'Sin agua', 'Factura mensual' ),
		'alt'    => 'Preparación de un pedido de alimentos congelados en caja isotérmica con hielo seco',
	),
	'caja' => array(
		'titulo' => '¿Dónde va el hielo seco? <em>Arriba.</em>',
		'texto'  => 'El aire frío pesa más y baja. Con el hielo seco encima de la carga, el frío cae sobre todo el producto.',
		'marcas' => array( 'Hielo seco encima', 'Producto', 'Caja aislante sin cierre hermético', 'El frío baja' ),
	),
	'reglas' => array(
		'titulo' => 'Tres reglas|<em>para que llegue frío.</em>',
		'lista'  => array(
			array( 'Encima de la carga', 'Nunca solo en el fondo. En palés, en la parte de arriba o en el hueco superior.' ),
			array( 'Envase que respire', 'Caja o cámara aislada, pero no hermética: el CO₂ tiene que salir o la presión puede reventarla.' ),
			array( 'Kilos según las horas', 'No es lo mismo un reparto urbano de 3 horas que un envío de 24 h. Díganos la ruta y le decimos cuántos kilos.' ),
		),
	),
	'plan' => array(
		'titulo' => '¿Las mismas rutas cada semana?|<em>Que no tenga que acordarse.</em>',
		'texto'  => 'Marque los días de ruta y los kilos de cada salida. Con ese consumo le damos su precio y dejamos el pedido programado: cada semana, cada 15 días o cada mes.',
		'dias'   => array( 'L', 'M', 'X', 'J', 'V' ),
		'kg'     => 'kg por ruta',
		'semana'      => 'Por semana',
		'semana_calc' => 'Días × kg por ruta',
		'mes'         => 'Al mes',
		'mes_calc'    => 'Por semana × 4',
		'nota'        => 'Cálculo orientativo. Al mes, contando 4 semanas.',
		'lunes'       => 'Los lunes no hay reparto: para la ruta del lunes, elija la entrega del sábado (según zona, con suplemento) o recójalo el mismo lunes en Mataró.',
		'boton'  => 'Pedir mi precio',
	),
	'cliente' => array(
		'titulo'    => 'Quién lo usa|<em>y para qué.</em>',
		'etiquetas' => array( 'Cliente', 'Uso' ),
		'id'        => 'italpizza',
		'detalle'   => 'Marca de pizza congelada',
		'uso'       => 'Transporte en frío',
		'alt'       => 'Logotipo de Italpizza',
	),
	'faq' => array(
		'titulo' => 'Preguntas de transporte en frío',
		'lista'  => array(
			array( '¿3 mm o 16 mm para transporte?', '16 mm: más masa por pieza, así que aguanta más horas en ruta. Los 3 mm enfrían más rápido y sirven para bajar la temperatura al empezar.' ),
			array( '¿Puedo llevarlo en la cabina de la furgoneta?', 'No. Siempre en la zona de carga y ventilando. En la cabina el CO₂ se acumula y desplaza el oxígeno que respira el conductor.' ),
			array( '¿Sirve para producto refrigerado, no congelado?', 'Sí, con cuidado: separe el hielo seco del producto con cartón o una bandeja para que no lo congele. Le ayudamos a ajustar la cantidad.' ),
			array( '¿Pueden entregar cada semana a la misma hora?', 'El mismo día cada semana, sí: con un pedido programado llega por la mañana sin que tenga que pedirlo. La hora exacta depende de la ruta de la mensajería. Si necesita una hora fija, recójalo en Mataró a la hora que le confirmemos.' ),
		),
	),
	// Llamada final: hoja de ruta (de Mataró a su almacén) y botón al formulario de cuenta de empresa, con WhatsApp como alternativa
	'final' => array(
		'titulo'     => '¿Cuántos kilos por ruta?|<em>Díganos la ruta y le damos precio.</em>',
		'texto'      => 'Con los días de salida y los kilos de cada ruta calculamos su descuento sobre tarifa. Le contestamos normalmente el mismo día laborable.',
		'boton'      => 'Pedir mi precio',
		'wa'         => 'O escríbanos por WhatsApp',
		'wa_mensaje' => 'Hola, necesitamos hielo seco para nuestras rutas de reparto. ¿Nos dais precio?',
		'ruta'       => array( array( 'Mataró', 'Pedido antes de las 12:00' ), array( 'Su almacén', 'Por la mañana, de martes a sábado' ) ),
		'carga'      => '16 mm · −78,5 °C',
	),
);
