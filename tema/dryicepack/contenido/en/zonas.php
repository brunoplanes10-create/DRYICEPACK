<?php
/* Zonas de entrega (índice y plantilla de zona) · inglés. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Delivery areas',
	'seo'  => array(
		// National page (10/11/2026): where we deliver. Terms and delivery times are on /en/delivery/.
		'titulo'      => 'Dry ice delivered across mainland Spain | DryIcePack',
		'descripcion' => 'Dry ice delivered anywhere in mainland Spain, province by province: order by 12:00 and it arrives the next day before 14:00. Find your city or province.',
	),
	'mapa'   => 'Map with the Mataró warehouse at the centre, rings every 10 km and the delivery areas',
	'indice' => array(
		'migas'   => array( 'Home', 'Delivery areas' ),
		'titulo'  => 'Dry ice delivered|<em>across mainland Spain.</em>',
		'texto'   => 'Order before 12:00 (Spanish time), Monday to Thursday, and it arrives the next day before 14:00 at any postcode in mainland Spain. Or pick any day you like. Near Mataró? Collect it instead: {pk} kg for {recogida:pk}, VAT included.',
		'provincias' => 'Mainland Spain, by region',
		'grupos'  => 'Near Mataró, by district',
		'resto'   => 'Can’t see your city? It still arrives at any postcode in mainland Spain the next day before 14:00, Tuesday to Friday (on Saturday, depending on the area, before 12:00). We don’t deliver to the Balearic or Canary Islands, Ceuta or Melilla.',
		'envios'  => 'See delivery times and prices',
		'faq'     => array(
			array( 'Where can I buy dry ice near me?', 'Online, here, anywhere in mainland Spain: order before 12:00 (Spanish time), Monday to Thursday, and it arrives the next day before 14:00 in an EPS box, or on the day you choose. Near Mataró? Collect it from our warehouse, Monday to Saturday: {pk} kg for {recogida:pk}, VAT included, cash only.' ),
			array( 'Do you deliver to the Balearic or Canary Islands, Ceuta or Melilla?', 'No. We only deliver to mainland Spain, to any postcode on the mainland.' ),
			array( 'Can I order for a specific day?', 'Yes: at checkout, choose any day in the next 90 with delivery in your area, Tuesday to Saturday (Saturday depending on the area, with a {sabado_envio} surcharge, VAT included).' ),
		),
	),
	// Regions in English (key: the Spanish name, normalised by dipt_lugar_normal in inc/lugares.php)
	'ccaa' => array(
		'andalucia' => 'Andalusia', 'aragon' => 'Aragon', 'asturias' => 'Asturias', 'principado-de-asturias' => 'Asturias',
		'cantabria' => 'Cantabria', 'castilla-y-leon' => 'Castile and León', 'castilla-la-mancha' => 'Castilla–La Mancha',
		'cataluna' => 'Catalonia', 'catalunya' => 'Catalonia', 'comunidad-valenciana' => 'Valencian Community', 'comunitat-valenciana' => 'Valencian Community',
		'extremadura' => 'Extremadura', 'galicia' => 'Galicia', 'rioja' => 'La Rioja', 'comunidad-de-madrid' => 'Community of Madrid', 'madrid' => 'Community of Madrid',
		'region-de-murcia' => 'Region of Murcia', 'murcia' => 'Region of Murcia', 'navarra' => 'Navarre', 'comunidad-foral-de-navarra' => 'Navarre',
		'pais-vasco' => 'Basque Country', 'euskadi' => 'Basque Country',
	),
	'zona' => array(
		'seo_titulo'      => 'Dry ice in {nombre}: delivered before 14:00 | DryIcePack',
		'seo_descripcion' => 'Dry ice in {nombre}: order before 12:00 and it arrives the next day before 14:00. Or collect it in Mataró, {km} km away: {pk} kg for {recogida:pk}, VAT included.',
		// For long names (the description must not exceed 155 characters)
		'seo_descripcion_corta' => 'Dry ice in {nombre}: order before 12:00 and it arrives the next day before 14:00. Or collect it from our warehouse in Mataró, {km} km away.',
		'migas'   => array( 'Home', 'Areas', '{Nombre}' ),
		'titulo'  => 'Dry ice in {nombre},|<em>at your door before 14:00.</em>',
		'texto'   => 'Order before 12:00 (Spanish time), Monday to Thursday, and it arrives in {nombre} the next day before 14:00. Or pick any day you like. From {pk} kg, no contracts. Would you rather collect it? Our warehouse is in Mataró, {km} km away.',
		'boton'   => 'Place my order',
		'datos'   => array(
			array( 'Distance to the warehouse', '{km} km' ),
			array( 'By car, without traffic', 'about {min} min' ),
			array( 'District', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'When does it reach {nombre}?|<em>And how much is delivery?</em>',
			'filas'  => array(
				array( 'Order before 12:00', 'It arrives the next day before 14:00, Tuesday to Friday. No deliveries on Sunday or Monday.' ),
				array( 'Saturday', 'If your postcode gets Saturday delivery, it arrives before 12:00, with a {sabado_envio} surcharge (VAT included). Order before 12:00 on Friday.' ),
				array( 'Collection', 'At our warehouse in Mataró, {km} km away, Monday to Saturday. Whole-euro prices, VAT included: {pk} kg for {recogida:pk} and 10 kg for {recogida:10}. On Saturday, +{sabado_recogida} (VAT included).{sabado_libre} You choose the day and we confirm the time. Cash only, exact amount.' ),
				array( '10 kg delivery', '€17.32 incl. VAT, the same as anywhere in mainland Spain. You see it in the total before you pay: no surprises at the last step.' ),
				array( 'For businesses', 'An account with a supply contract, a discount on list prices based on volume, scheduled orders and a single invoice a month, payable within 30 days.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'What do you need it for?',
			'lista'  => array( array( 'hosteleria', 'Hospitality' ), array( 'transporte', 'Cold-chain transport' ), array( 'industria', 'Industry' ), array( 'laboratorios', 'Laboratories' ), array( 'eventos', 'Parties and events' ) ),
		),
		'faq'     => array(
			array( 'How long does dry ice take to reach {nombre}?', 'If you order before 12:00, Monday to Thursday, it arrives in {nombre} the next day before 14:00. Order on Friday and it arrives on Saturday before 12:00 (if your postcode gets Saturday delivery, with a surcharge) or on Tuesday. We don’t deliver on Sunday or Monday. At checkout, you only see the days available.' ),
			array( 'Can I collect it if I’m in {nombre}?', 'Yes. Our warehouse is in Mataró, {km} km away (about {min} min by car without traffic). Choose the day when you order, Monday to Saturday, and we confirm the time by phone or WhatsApp. Whole-euro prices, VAT included, the same online and at the warehouse: {pk} kg for {recogida:pk} and 10 kg for {recogida:10}. On Saturday, +{sabado_recogida} (VAT included).{sabado_libre} You pay in cash, with the exact amount: we can’t give change. In the car, carry it in the boot, with ventilation.' ),
			array( 'Do you deliver on Saturday in {nombre}?', 'It depends on the postcode. If your area gets Saturday delivery, you’ll see it as an option at checkout, with a {sabado_envio} surcharge (VAT included), and it arrives before 12:00. To have it on Saturday, order before 12:00 on Friday.' ),
		),
		'cerca'   => 'Nearby areas',
		'fuente'  => 'Distances and times: OpenStreetMap and OSRM, without traffic.',
	),
);
