<?php
/* Seguridad del hielo seco · inglés */
return array(
	'menu' => 'Dry ice safety',
	'seo'  => array(
		'titulo'      => 'Is dry ice toxic? Safety and data sheet | DryIcePack',
		'descripcion' => 'Dry ice isn’t toxic or flammable, but at −78.5 °C it burns skin and in closed spaces it displaces oxygen. Read the rules and download the PDF data sheet.',
	),
	'migas' => array( 'Home', 'Safety' ),
	'hero'  => array(
		'titulo'    => 'Is dry ice|<em>toxic?</em>',
		'respuesta' => 'It isn’t toxic or flammable, but it must never be eaten or drunk: never put a pellet in a drink. Handling it has three risks, and all three can be avoided: it burns the skin because it’s at −78.5 °C, in a closed space the CO₂ displaces oxygen, and in an airtight container the pressure can burst it.',
		'etiqueta'  => array( 'Dry ice', 'Solid CO₂', '−78.5 °C', 'UN 1845' ),
	),
	'riesgos' => array(
		'titulo' => 'Is it dangerous?|<em>Three risks, three precautions.</em>',
		'lista'  => array(
			array( 'Skin contact', 'Cold burn in seconds.', 'Thermal gloves and tongs. Never with bare hands.' ),
			array( 'Closed space', 'CO₂ collects low down and displaces oxygen: dizziness, headache, shortness of breath.', 'Ventilate. No basements or closed cold rooms. In the car, in the boot, with ventilation.' ),
			array( 'Sealed container', 'As it turns to gas, its volume multiplies and the pressure rises until the container bursts.', 'Its own EPS box with the lid not taped shut. Never bottles, food tubs or airtight cool boxes.' ),
		),
		'causa'  => 'What happens',
		'gesto'  => 'What to do',
	),
	'haz' => array(
		'titulo' => 'Do this',
		'lista'  => array( 'Thermal gloves, tongs and, if you handle a lot, goggles.', 'A ventilated space: doors or windows open.', 'Keep it in its EPS box, somewhere cool, away from children and pets.', 'In drinks, always with a double container: the ice stays outside the glass.', 'Let any leftover ice sublimate outdoors.' ),
	),
	'evita' => array(
		'titulo' => 'Avoid this',
		'lista'  => array( 'Touching it without gloves or leaving it within reach of children and pets.', 'Eating it, or dropping pellets into a glass someone is going to drink from.', 'Closed containers, the freezer or the fridge.', 'Carrying it in the passenger compartment: it goes in the boot, with ventilation.', 'Throwing it down the sink, the toilet or into a closed bin.' ),
	),
	'auxilios' => array(
		'titulo' => 'If something happens',
		'lista'  => array(
			array( 'Cold burn', 'Warm the area with lukewarm water, not hot, and don’t rub it. If there are blisters or the skin changes colour, see a doctor.' ),
			array( 'Dizziness or shortness of breath', 'Get into the fresh air and ventilate the space. If it doesn’t improve or someone loses consciousness, call 112.' ),
			array( 'Someone has swallowed it', 'Call 112 or the Poison Information Service (Spain) on 91 562 04 20.' ),
		),
	),
	'fds' => array(
		'titulo' => 'Safety data sheet',
		'texto'  => 'The document your health and safety team, quality team or auditors will ask for: identification, hazards, first aid, handling, protection and transport.',
		'boton'  => 'Download the sheet',
		'archivo'=> '/wp-content/uploads/2026/01/CO2-SOLIDO-FICHA-DE-DATOS-DE-SEGURIDAD-DRYICEPACK-1.pdf',
		'meta'   => 'PDF · Spanish',
	),
	'faq' => array(
		'titulo' => 'More safety questions',
		'lista'  => array(
			array( 'Is it dangerous to breathe in the ‘smoke’?', 'What you see isn’t smoke: it’s water vapour from the air, condensed by the cold, together with CO₂. In a ventilated space it’s harmless; the risk comes in small, closed spaces.' ),
			array( 'Can it be used in drinks and cocktails?', 'For the fog effect, yes, with a double container: the dry ice goes in the outer container and the drink in another. Never a pellet inside a glass someone is going to drink from.' ),
			array( 'Can it explode?', 'Dry ice doesn’t explode. What can burst is an airtight container, because of the gas pressure. That’s why the box is never taped shut.' ),
			array( 'Is it safe with children or pets at home?', 'Yes, if an adult handles it and you keep it out of reach of children and pets. If you’re making fog, put the dry ice where no guest can touch it.' ),
			array( 'How do I carry it in the car?', 'Never in the passenger compartment: in the boot, inside its box and with ventilation, whatever the quantity.' ),
		),
	),
);
