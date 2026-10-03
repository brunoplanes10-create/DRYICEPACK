<?php
/* Hielo seco para fiestas y eventos · castellano · tú */
return array(
	'menu' => 'Hielo seco para fiestas y eventos',
	'seo'  => array(
		'titulo'      => 'Hielo seco para fiestas y eventos: niebla baja | DryIcePack',
		'descripcion' => 'Hielo seco para fiestas, bodas y Halloween: niebla por el suelo desde 3 kg. Pide antes de las 12:00 y mañana lo tienes en casa, listo para usar.',
	),
	'migas' => array( 'Inicio', 'Usos', 'Fiestas y eventos' ),
	'hero'  => array(
		'titulo' => 'Hielo seco para fiestas:|niebla por el suelo, <em>mañana en tu puerta.</em>',
		'texto'  => '¿Quieres esa niebla que cubre el suelo en el primer baile, en el escenario o en Halloween? Se hace con agua caliente y hielo seco. Pide antes de las 12:00 y mañana por la mañana lo tienes (de martes a sábado), o elige el día que quieras. Desde 3 kg.',
		'boton'  => 'Hacer mi pedido',
		'alt'    => 'Pareja bailando en una boda sobre una capa de niebla baja de hielo seco',
	),
	'recetas' => array(
		'titulo' => '¿Cuánto hielo seco|<em>necesitas?</em>',
		'lista'  => array(
			array( 'En casa', '3 kg', array( 'Un caldero o una ponchera con doble recipiente', 'Agua caliente', 'Pellets de 3 mm' ), 'Una noche de fiesta pequeña o una mesa de Halloween.' ),
			array( 'Fiesta grande', '10 kg', array( 'Dos o tres recipientes anchos', 'Agua caliente para ir cambiando', 'Pellets de 3 mm' ), 'Niebla por el suelo en varias tandas durante la noche.' ),
			array( 'Bar o evento', '15–20 kg', array( 'Recipientes en varios puntos', 'Agua caliente a mano', 'Pellets de 3 mm' ), 'Un servicio largo, un escenario o varias barras.' ),
		),
		'ingredientes' => 'Necesitas',
	),
	'pasos' => array(
		'titulo' => '¿Cómo se hace la niebla?|<em>En tres pasos.</em>',
		'lista'  => array(
			array( 'Agua caliente', 'En un recipiente ancho y abierto. Cuanto más caliente, más niebla.' ),
			array( 'Pellets, con pinzas', 'Añade unos pocos cada vez, con guantes. Verás la niebla al momento.' ),
			array( 'Repón', 'Cuando baje, cambia el agua por caliente y añade más pellets.' ),
		),
	),
	'halloween' => array(
		'titulo' => 'Halloween cae en sábado este año.',
		'texto'  => 'Pide antes de las 12:00 del jueves 29 de octubre y lo tienes el viernes 30. ¿Lo quieres el mismo sábado? Pídelo antes de las 12:00 del viernes: según zona y con suplemento de 11,74 €.',
		'enlace' => 'Hielo seco para Halloween',
	),
	'seguridad' => array(
		'titulo' => '¿Es seguro|<em>con invitados?</em>',
		'lista'  => array( 'La caja, fuera del alcance de niños, mascotas e invitados.', 'La sala, ventilada: una puerta o una ventana abierta.', 'Nunca dentro de una bebida ni de un recipiente cerrado.', 'Guantes térmicos y pinzas: nunca con la mano.' ),
	),
	'faq' => array(
		'titulo' => 'Preguntas de fiestas y eventos',
		'lista'  => array(
			array( '¿Cuándo lo pido para una boda el sábado?', 'Pídelo cuando quieras y elige que llegue el viernes (último momento para eso: jueves a las 12:00). Para recibirlo el mismo sábado, elige sábado: depende de la zona y lleva un suplemento de 11,74 € (IVA incluido). Cuanto más cerca del evento, mejor: se va sublimando con las horas.' ),
			array( '¿La niebla moja o mancha?', 'No mancha: es vapor de agua condensado y CO₂. Con mucha niebla seguida, el suelo puede quedar húmedo: en una pista de baile, sécalo entre tandas para que nadie resbale.' ),
			array( '¿Salta la alarma de incendios?', 'Los detectores de humo ópticos pueden reaccionar a la niebla densa. Pregúntalo en el local antes del evento.' ),
		),
	),
	// Llamada final: semana de reparto (L y D sin reparto, S según zona) y botón de compra
	'final' => array(
		'titulo'  => '¿Ya tienes fecha?|<em>Pídelo hoy y elige el día.</em>',
		'texto'   => 'Al pagar eliges el día de entrega, con hasta 90 días de antelación. Lo mejor: que llegue el mismo día de la fiesta o el anterior.',
		'boton'   => 'Pedir hielo seco',
		'semana'  => 'Días de entrega',
		'dias'    => array( array( 'L', 'Lunes' ), array( 'M', 'Martes' ), array( 'X', 'Miércoles' ), array( 'J', 'Jueves' ), array( 'V', 'Viernes' ), array( 'S', 'Sábado' ), array( 'D', 'Domingo' ) ),
		'estados' => array( 'si' => 'Reparto por la mañana', 'zona' => 'Según zona, con suplemento', 'no' => 'Sin reparto' ),
		'nota'    => 'Al pagar solo aparecen los días con reparto en tu zona.',
	),
);
