<?php
/* Usos del hielo seco (índice) · inglés */
return array(
	'menu' => 'Uses of dry ice',
	'seo'  => array(
		'titulo'      => 'What is dry ice used for? Uses by sector | DryIcePack',
		'descripcion' => 'Dry ice uses in hospitality, cold-chain transport, industry, laboratories and events. See which format to choose, 3 mm or 16 mm, and how much to order.',
	),
	'migas'  => array( 'Home', 'Uses' ),
	'titulo' => 'What dry ice|is <em>used for.</em>',
	'texto'  => 'Intense cold with no water, or fog that sinks and stays on the floor. This is how our customers use it.',
	'sectores' => array(
		array( 'hosteleria', 'Hospitality', 'Smoke when serving, cocktails, cold behind the bar and on the buffet.', 'uso-hosteleria' ),
		array( 'transporte', 'Cold-chain transport', 'Frozen and chilled goods on the road, pallets and last-mile delivery.', 'uso-transporte' ),
		array( 'industria', 'Industry', 'Cooling parts and batteries, testing and bulk orders.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratories', 'Frozen samples, UN 1845 shipments, practical classes.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Parties and events', 'Low-lying fog at weddings, on stage and at Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => 'Which format|<em>for your use?</em>',
		'pregunta'=> 'Choose the use',
		'usos'    => array(
			array( 'Fog and effects', '3mm', 'Small pellets release fog straight away.', 'eventos' ),
			array( 'Cocktails and plating', '3mm', 'Quick fog in a double container, never inside the glass.', 'hosteleria' ),
			array( 'Frozen goods on the road', '16mm', 'Nuggets last more hours in the box.', 'transporte' ),
			array( 'Lab samples', '3mm', 'Pellets fill the gaps around the tubes; for long journeys, 16 mm.', 'laboratorios' ),
			array( 'Cooling parts', '3mm', 'More contact with the surface: it cools faster.', 'industria' ),
			array( 'Cold on the bar or buffet', '16mm', 'Lasts through the service without constant topping up.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => '3 mm pellets', '16mm' => '16 mm nuggets' ),
		'ver'      => 'See this use',
	),
	'otros' => array(
		'titulo' => 'Other uses',
		'lista'  => array(
			array( 'Grape harvest', 'Cooling freshly picked grapes before pressing.', 'uso-vino' ),
			array( 'Fresh fruit', 'Keeping boxes cold on the way to the packing house.', 'uso-agricultura' ),
			array( 'Teaching', 'Sublimation practicals and classroom demonstrations.', 'uso-docencia' ),
			array( 'Laboratory', 'Keeping reagents cold for the length of a session.', 'uso-laboratorio-pinzas' ),
			array( 'Dry ice blasting', 'Carried out by INDUNOVA, the company DryIcePack belongs to.', 'uso-industria-pala' ),
		),
		'indunova' => 'Dry ice blasting at indunova.es',
	),
);
