<?php
/* Hielo seco industrial · inglés */
return array(
	'menu' => 'Industrial dry ice',
	'seo'  => array(
		'titulo'      => 'Industrial dry ice: volume and fixed dates | DryIcePack',
		'descripcion' => 'Industrial dry ice for cooling parts and batteries, low-temperature testing and shrink fitting. Up to 250 kg per order, with a full invoice. Get a quote.',
	),
	'migas' => array( 'Home', 'Uses', 'Industry' ),
	'hero'  => array(
		'titulo' => 'Industrial dry ice|in volume, <em>on the agreed day.</em>',
		'texto'  => 'For cooling parts and batteries, testing and shrink fitting. You tell us the kilos and the date; we reply with a price, usually the same working day. With a full invoice and a safety data sheet.',
		'temperatura' => 'Dry ice temperature',
		'boton'  => 'Get a quote',
		'alt'    => 'Technician working with dry ice in an industrial plant',
	),
	'usos' => array(
		'titulo' => 'What does industry|<em>use it for?</em>',
		'lista'  => array(
			array( 'A1', 'Cooling batteries and components', 'They cool down fast between tests or before handling: less downtime.' ),
			array( 'A2', 'Shrink fitting', 'The cooled part shrinks just enough to fit without forcing it.' ),
			array( 'B1', 'Low-temperature testing', 'Checking how materials and equipment respond to temperatures down to −78.5 °C.' ),
			array( 'B2', 'Heat-sensitive products', 'Adhesives, resins or components that must not warm up in transit.' ),
			array( 'C1', 'Dry ice blasting', 'Cleaning with no water or residue. A service from INDUNOVA.' ),
			array( 'C2', 'Shutdowns and maintenance', 'We schedule it around your inspections: it arrives on shutdown day, without you ordering each time.' ),
		),
	),
	'escala' => array(
		'titulo' => 'How much can we|<em>supply you?</em>',
		'marcas' => array(
			array( 3, 'From 3 kg', 'For trials and small orders.' ),
			array( 150, 'Up to 150 kg', 'Online orders anywhere in mainland Spain. Outside the province of Barcelona, more kilos by message, arranged individually.' ),
			array( 250, 'Up to 250 kg', 'The maximum per order. Online, in the province of Barcelona. Over 200 kg: ask us, as we sometimes deliver it ourselves.' ),
			array( 400, 'Scheduled supply', 'Weekly, fortnightly or monthly, at a better price.' ),
		),
	),
	'compras' => array(
		'titulo' => 'What your|<em>purchasing department asks for.</em>',
		'lista'  => array(
			array( 'Full invoice', 'With your tax details, for every order. With a business account, a single one a month covering all your orders.' ),
			array( 'Safety data sheet', 'PDF, in Spanish, for your health and safety team.' ),
			array( 'Company details for supplier registration', 'INDUNOVA IMS S.L. · CIF B66800103 · Mataró (Barcelona).' ),
			array( 'Confirmed delivery date', 'Chosen when you order. For volume orders, we agree it by phone.' ),
		),
		'fds'    => 'Safety data sheet',
	),
	'form' => array(
		'titulo'   => 'Request a quote',
		'texto'    => 'Tell us the kilos, the date and the postcode. We’ll reply with a price and a delivery day, usually the same working day.',
		'campos'   => array( 'kg' => 'Kilos', 'fecha' => 'Date you need it', 'cp' => 'Delivery postcode', 'uso' => 'Use', 'empresa' => 'Company', 'nombre' => 'Name', 'telefono' => 'Phone', 'email' => 'Email' ),
		'boton'    => 'Get my quote',
		'enviando' => 'Sending…',
	),
	'cliente' => array(
		'titulo'    => 'Who uses it|<em>and what for.</em>',
		'etiquetas' => array( 'Customer', 'Use' ),
		'id'        => 'nissan',
		'detalle'   => 'Nissan’s Formula E team',
		'uso'       => 'Cooling batteries fast',
		'alt'       => 'Nissan Formula E Team logo',
	),
	'faq' => array(
		'titulo' => 'Industry questions',
		'lista'  => array(
			array( '3 mm or 16 mm for cooling parts?', 'Usually 3 mm: more surface in contact, so it cools faster. To hold the cold for many hours, 16 mm.' ),
			array( 'Can you deliver on a specific day?', 'Yes. Online, you choose the day at checkout: Tuesday to Friday, and Saturday depending on the area. For volume orders, we agree it by phone.' ),
			array( 'Do you offer dry ice blasting?', 'It’s done by INDUNOVA, the company DryIcePack belongs to. We’ll put you in touch.' ),
		),
	),
);
