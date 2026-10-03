<?php
/* Usos del hielo seco (índice) · inglés */
return array(
	'menu' => 'Uses of dry ice',
	'seo'  => array(
		'titulo'      => 'What is dry ice used for? Uses by sector | DryIcePack',
		'descripcion' => 'What dry ice is used for in hospitality, transport, industry, labs and parties, and which format to order: 3 mm or 16 mm. Find your use and order it.',
	),
	'migas'  => array( 'Home', 'Uses' ),
	'titulo' => 'What dry ice|is used for|<em>and which format to order.</em>',
	'texto'  => 'It does two things: cold at −78.5 °C that leaves not a drop of water, and fog that clings to the floor. Find your use and we’ll tell you which format to order.',
	'sectores' => array(
		array( 'hosteleria', 'Hospitality', 'Smoke when serving, cocktails, and cooling behind the bar that leaves no water.', 'uso-hosteleria' ),
		array( 'transporte', 'Cold-chain transport', 'Frozen goods that arrive frozen, with no boxes full of water.', 'uso-transporte' ),
		array( 'industria', 'Industry', 'Cooling parts and batteries fast, with no water or residue.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratories', 'Frozen samples in transit (UN 1845) and practical classes.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Parties and events', 'Low fog that stays on the floor: weddings, stages and Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => '3 mm or 16 mm?|<em>It depends on what it’s for.</em>',
		'pregunta'=> 'What do you need it for?',
		'usos'    => array(
			array( 'Fog and effects', '3mm', 'Small pellets make fog straight away, and it’s thicker.', 'eventos' ),
			array( 'Cocktails and plating', '3mm', 'Quick fog in a double container, never inside the glass.', 'hosteleria' ),
			array( 'Frozen goods on the road', '16mm', 'Nuggets last longer in the box, even on long journeys.', 'transporte' ),
			array( 'Lab samples', '3mm', 'Pellets fill the gaps around the tubes; for long journeys, 16 mm.', 'laboratorios' ),
			array( 'Cooling parts', '3mm', 'More contact with the surface: it cools faster.', 'industria' ),
			array( 'Cold on the bar or buffet', '16mm', 'Lasts through the service without constant topping up.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => '3 mm pellets', '16mm' => '16 mm nuggets' ),
		'ver'      => 'See how it’s used',
	),
	'otros' => array(
		'titulo' => 'Other uses of dry ice',
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
