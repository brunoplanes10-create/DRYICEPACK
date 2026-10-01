<?php
/* Zonas de entrega (índice y plantilla de zona) · inglés. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Delivery areas',
	'seo'  => array(
		'titulo'      => 'Dry ice in Barcelona and nearby: delivery areas | DryIcePack',
		'descripcion' => 'Dry ice in Barcelona, the Maresme, the Vallès and the Baix Llobregat: next-morning delivery or free collection from our warehouse in Mataró.',
	),
	'mapa'   => 'Map with the Mataró warehouse at the centre, rings every 10 km and the delivery areas',
	'indice' => array(
		'migas'   => array( 'Home', 'Delivery areas' ),
		'titulo'  => 'Dry ice|<em>near you.</em>',
		'texto'   => 'We deliver across mainland Spain. Near Barcelona, you can also collect it from our warehouse in Mataró. These are the areas we get the most orders from.',
		'grupos'  => 'By district',
		'resto'   => 'And anywhere in mainland Spain, with delivery the next morning.',
		'envios'  => 'Delivery times and costs',
	),
	'zona' => array(
		'seo_titulo'      => 'Dry ice in {nombre}: next-day delivery | DryIcePack',
		'seo_descripcion' => 'Dry ice in {nombre}: order before 12:00 and it arrives the next morning, or collect it free from our warehouse in Mataró, {km} km away. Order from 3 kg.',
		'seo_descripcion_corta' => 'Dry ice in {nombre}: order before 12:00 and it arrives the next morning, or collect it free in Mataró, {km} km away. From 3 kg.',
		'migas'   => array( 'Home', 'Areas', '{Nombre}' ),
		'titulo'  => 'Dry ice in|<em>{nombre}.</em>',
		'texto'   => 'Order before 12:00 (Spanish time) and we deliver to {nombre} the next morning. Or collect it from our warehouse in Mataró, {km} km away.',
		'boton'   => 'Buy dry ice',
		'datos'   => array(
			array( 'Distance to the warehouse', '{km} km' ),
			array( 'By car, without traffic', 'about {min} min' ),
			array( 'District', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'Delivery in|<em>{nombre}.</em>',
			'filas'  => array(
				array( 'Order before 12:00', 'Arrives the next morning, Tuesday to Friday.' ),
				array( 'Saturday', 'Depending on the postcode, with a €11.74 surcharge.' ),
				array( 'Collection', 'In Mataró, {km} km from {nombre}, Monday to Saturday. Free on weekdays; we confirm the time.' ),
				array( '10 kg delivery', '€17.32 incl. VAT, the same price as anywhere in mainland Spain.' ),
				array( 'Businesses in {comarca}', 'An account with your own price and a monthly invoice.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'What people in {comarca} use it for',
			'lista'  => array( array( 'hosteleria', 'Hospitality' ), array( 'transporte', 'Cold-chain transport' ), array( 'industria', 'Industry' ), array( 'laboratorios', 'Laboratories' ), array( 'eventos', 'Parties and events' ) ),
		),
		'faq'     => array(
			array( 'How long does dry ice take to reach {nombre}?', 'If you order before 12:00, Monday to Friday, it reaches {nombre} the next morning. We don’t deliver on Sunday or Monday.' ),
			array( 'Can I collect it if I’m in {nombre}?', 'Yes. Our warehouse is in Mataró, {km} km away (about {min} min by car without traffic). Collection is free and you pay in cash.' ),
			array( 'Do you deliver on Saturday in {nombre}?', 'It depends on the postcode. There is a €11.74 surcharge (VAT included) and you choose it at checkout.' ),
		),
		'cerca'   => 'Nearby areas',
		'fuente'  => 'Distances and times: OpenStreetMap and OSRM, without traffic.',
	),
);
