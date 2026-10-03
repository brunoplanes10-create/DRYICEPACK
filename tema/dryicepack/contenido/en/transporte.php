<?php
/* Hielo seco para transporte en frío · inglés */
return array(
	'menu' => 'Dry ice for transport',
	'seo'  => array(
		'titulo'      => 'Dry ice for cold-chain and frozen transport | DryIcePack',
		'descripcion' => 'Dry ice for frozen and chilled transport: it’s at −78.5 °C and leaves no water in the box. Order before 12:00 and it arrives tomorrow morning.',
	),
	'migas' => array( 'Home', 'Uses', 'Cold-chain transport' ),
	'hero'  => array(
		'titulo' => 'Dry ice for frozen transport:|<em>tomorrow at your warehouse,</em>|<em>no meltwater.</em>',
		'texto'  => 'At −78.5 °C, it turns to gas without melting and keeps the load cold all along the route. Order before 12:00 (Spanish time) and you’ll have it tomorrow morning (Tuesday to Saturday), or choose any day you like.',
		'boton'  => 'Open my account',
		'datos'  => array( '−78.5 °C', '16 mm', 'No water', 'Monthly invoice' ),
		'alt'    => 'Packing an order of frozen food in an insulated box with dry ice',
	),
	'caja' => array(
		'titulo' => 'Where does the dry ice go? <em>On top.</em>',
		'texto'  => 'Cold air is heavier and sinks. With the dry ice on top of the load, the cold air falls over all the goods.',
		'marcas' => array( 'Dry ice on top', 'Product', 'Insulated box, not airtight', 'The cold sinks' ),
	),
	'reglas' => array(
		'titulo' => 'Three rules|<em>so it arrives cold.</em>',
		'lista'  => array(
			array( 'On top of the load', 'Never only at the bottom. On pallets, on top or in the gap above the load.' ),
			array( 'Packaging that breathes', 'An insulated box or compartment, but never airtight: the CO₂ has to escape, or the pressure can burst it.' ),
			array( 'Kilos to match the hours', 'A 3-hour city round is not the same as a 24 h shipment. Tell us the route and we’ll tell you how many kilos you need.' ),
		),
	),
	'plan' => array(
		'titulo' => 'The same routes every week?|<em>We’ll remember them for you.</em>',
		'texto'  => 'Tick your route days and the kilos for each run. From that usage we give you your price and set up a scheduled order: weekly, fortnightly or monthly.',
		'dias'   => array( 'Mo', 'Tu', 'We', 'Th', 'Fr' ),
		'kg'     => 'kg per route',
		'semana'      => 'Per week',
		'semana_calc' => 'Days × kg per route',
		'mes'         => 'Per month',
		'mes_calc'    => 'Per week × 4',
		'nota'        => 'Estimate only. Per month, counting 4 weeks.',
		'lunes'       => 'There are no deliveries on Mondays: for your Monday route, choose Saturday delivery (depending on the area, with a surcharge) or collect it in Mataró that Monday.',
		'boton'  => 'Get my price',
	),
	'cliente' => array(
		'titulo'    => 'Who uses it|<em>and what for.</em>',
		'etiquetas' => array( 'Customer', 'Use' ),
		'id'        => 'italpizza',
		'detalle'   => 'Frozen pizza brand',
		'uso'       => 'Cold-chain transport',
		'alt'       => 'Italpizza logo',
	),
	'faq' => array(
		'titulo' => 'Cold-chain transport questions',
		'lista'  => array(
			array( '3 mm or 16 mm for transport?', '16 mm: more mass per piece, so it lasts longer on the road. 3 mm cools faster and helps bring the temperature down at the start.' ),
			array( 'Can I carry it in the van cab?', 'No. Always in the load area, with ventilation. In the cab, the CO₂ builds up and displaces the oxygen the driver breathes.' ),
			array( 'Does it work for chilled, not frozen, products?', 'Yes, with care: separate the dry ice from the product with cardboard or a tray so it doesn’t freeze it. We’ll help you get the quantity right.' ),
			array( 'Can you deliver every week at the same time?', 'On the same day every week, yes: with a scheduled order it arrives in the morning without you having to ask. The exact time depends on the courier’s route. If you need a fixed time, collect it in Mataró at the time we confirm with you.' ),
		),
	),
	// Final call to action: route sheet (from Mataró to your warehouse) and button to the business account form, with WhatsApp as an alternative
	'final' => array(
		'titulo'     => 'How many kilos per route?|<em>Tell us the route and we’ll give you a price.</em>',
		'texto'      => 'From your departure days and the kilos per route, we work out your discount on list prices. We usually reply the same working day.',
		'boton'      => 'Get my price',
		'wa'         => 'Or message us on WhatsApp',
		'wa_mensaje' => 'Hi, we need dry ice for our delivery routes. Could you give us a price?',
		'ruta'       => array( array( 'Mataró', 'Order before 12:00' ), array( 'Your warehouse', 'In the morning, Tuesday to Saturday' ) ),
		'carga'      => '16 mm · −78.5 °C',
	),
);
