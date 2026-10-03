<?php
/* Zonas de entrega (índice y plantilla de zona) · inglés. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Delivery areas',
	'seo'  => array(
		'titulo'      => 'Dry ice in Barcelona and nearby: delivery areas | DryIcePack',
		'descripcion' => 'Dry ice in Barcelona, the Maresme, the Vallès and the Baix Llobregat: order before 12:00 and have it tomorrow or collect it free in Mataró. Find your area.',
	),
	'mapa'   => 'Map with the Mataró warehouse at the centre, rings every 10 km and the delivery areas',
	'indice' => array(
		'migas'   => array( 'Home', 'Delivery areas' ),
		'titulo'  => 'Dry ice near you,|<em>tomorrow morning.</em>',
		'texto'   => 'Order before 12:00 (Spanish time) and you’ll have it tomorrow morning (Tuesday to Saturday), or pick any day you like. Near Mataró? Collect it free. Find your area and see how far it is from our warehouse.',
		'grupos'  => 'By district',
		'resto'   => 'Can’t see your area? If it’s in mainland Spain, it still arrives the next morning. We only deliver to mainland Spain: not to the Balearic or Canary Islands, Ceuta or Melilla.',
		'envios'  => 'See delivery times and prices',
	),
	'zona' => array(
		'seo_titulo'      => 'Dry ice in {nombre}: at your door tomorrow | DryIcePack',
		'seo_descripcion' => 'Dry ice in {nombre}: order before 12:00 and you’ll have it tomorrow morning in an insulated box. Or collect it free in Mataró, {km} km away. From 3 kg.',
		'seo_descripcion_corta' => 'Dry ice in {nombre}: order before 12:00 and you’ll have it tomorrow morning. Or collect it free in Mataró, {km} km away. From 3 kg.',
		'migas'   => array( 'Home', 'Areas', '{Nombre}' ),
		'titulo'  => 'Dry ice in {nombre},|<em>at your door tomorrow.</em>',
		'texto'   => 'Order before 12:00 (Spanish time) and you’ll have it in {nombre} tomorrow morning (Tuesday to Saturday), or pick any day you like. From 3 kg, no contracts. Would you rather collect it? Our warehouse is in Mataró, {km} km away.',
		'boton'   => 'Place my order',
		'datos'   => array(
			array( 'Distance to the warehouse', '{km} km' ),
			array( 'By car, without traffic', 'about {min} min' ),
			array( 'District', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'When does it reach {nombre}?|<em>And how much is delivery?</em>',
			'filas'  => array(
				array( 'Order before 12:00', 'It’s with you the next morning, Tuesday to Friday. No deliveries on Sunday or Monday.' ),
				array( 'Saturday', 'If your postcode gets Saturday delivery, there is an €11.74 surcharge (VAT included). Order before 12:00 on Friday.' ),
				array( 'Collection', 'At our warehouse in Mataró, {km} km away, Monday to Saturday. Free on weekdays; Saturday has a surcharge. You choose the day and we confirm the time.' ),
				array( '10 kg delivery', '€17.32 incl. VAT, the same as anywhere in mainland Spain. You see it in the total before you pay: no surprises at the last step.' ),
				array( 'For businesses', 'An account with a supply contract, a discount on list prices based on volume, scheduled orders and a single invoice a month, payable within 30 days.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'What do you need it for?',
			'lista'  => array( array( 'hosteleria', 'Hospitality' ), array( 'transporte', 'Cold-chain transport' ), array( 'industria', 'Industry' ), array( 'laboratorios', 'Laboratories' ), array( 'eventos', 'Parties and events' ) ),
		),
		'faq'     => array(
			array( 'How long does dry ice take to reach {nombre}?', 'If you order before 12:00, Monday to Thursday, you’ll have it in {nombre} the next morning. Order on Friday and it arrives on Saturday (if your postcode gets Saturday delivery, with a surcharge) or on Tuesday. We don’t deliver on Sunday or Monday. At checkout, you only see the days available.' ),
			array( 'Can I collect it if I’m in {nombre}?', 'Yes. Our warehouse is in Mataró, {km} km away (about {min} min by car without traffic). Choose the day when you order, Monday to Saturday, and we confirm the time by phone or WhatsApp. It’s free on weekdays and you pay in cash on collection. In the car, carry it in the boot, with ventilation.' ),
			array( 'Do you deliver on Saturday in {nombre}?', 'It depends on the postcode. If your area gets Saturday delivery, you’ll see it as an option at checkout, with an €11.74 surcharge (VAT included). To have it on Saturday, order before 12:00 on Friday.' ),
		),
		'cerca'   => 'Nearby areas',
		'fuente'  => 'Distances and times: OpenStreetMap and OSRM, without traffic.',
	),
);
