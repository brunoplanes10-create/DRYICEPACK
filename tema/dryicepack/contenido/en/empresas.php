<?php
/* Empresas (cuenta de empresa y presupuesto por volumen) · inglés */
return array(
	'menu' => 'Business',
	'seo'  => array(
		'titulo'      => 'Dry ice supplier for businesses in Spain | DryIcePack',
		'descripcion' => 'Dry ice for your business at your own price, ordered by WhatsApp or online, with one invoice a month. From 3 kg to over 250 kg. Morning delivery.',
	),
	'migas' => array( 'Home', 'Business' ),
	'hero'  => array(
		'titulo' => 'Dry ice supplier|for businesses that use it|<em>every week.</em>',
		'texto'  => 'Your own price, orders in two clicks or by WhatsApp, and a single invoice each month. No commitment.',
		'boton'  => 'Open an account',
		'tel'    => 'or call sales:',
		'alt'    => 'Worker opening a box of dry ice in the warehouse',
	),
	'pedir' => array(
		'titulo'   => 'Order the way|<em>that suits you.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'One message and it’s done. We confirm the delivery day in the same chat.' ),
			array( 'Online', 'Log in to your account and repeat any previous order in one click.' ),
			array( 'Email', 'For scheduled orders or several delivery addresses.' ),
		),
		'chat'     => array( 'Hi, can you send us 10 kg of 16 mm for Tuesday?', 'Done. It leaves on Monday and arrives on Tuesday morning. It goes on this month’s invoice.' ),
		'repetir'  => array( 'titulo' => 'Your orders', 'filas' => array( array( '10 kg · 16 mm', 'Repeat' ), array( '20 kg · 3 mm', 'Repeat' ), array( '10 kg · 16 mm', 'Repeat' ) ) ),
		'email'    => array( 'To: info@dryicepack.es', 'Subject: Weekly order', 'Good morning. As every Monday, 15 kg of 16 mm to our Rubí warehouse, please. Thank you.' ),
	),
	'ventajas' => array(
		'titulo' => 'What changes|with an <em>account.</em>',
		'lista'  => array(
			array( 'Your own price', 'Based on volume and frequency. Agreed once, so there is no need to negotiate every order.' ),
			array( 'One invoice a month', 'All the month’s orders on one full invoice. Pay by bank transfer or direct debit.' ),
			array( 'Details saved', 'Company name, tax ID and delivery address: you never type them again.' ),
			array( 'Reminders, if you want them', 'If you order at a regular pace, we let you know when it’s due. Switch it off in one click.' ),
			array( 'No commitment', 'Pause or adjust it whenever your activity changes.' ),
		),
	),
	'volumen' => array(
		'titulo'  => 'How much|<em>do you need?</em>',
		'rango'   => 'Kilos per order',
		'cajas'   => '%1$s boxes · %2$s kg',
		'caja'    => '1 box · %s kg',
		'notas'   => array(
			'web'   => 'Up to 250 kg per order, online or through your account.',
			'bcn'   => 'Over 150 kg in the province of Barcelona: delivered to your premises. Ask us about lead times.',
			'fuera' => 'Large volumes outside the province of Barcelona: we assess each case individually.',
		),
	),
	'casos' => array(
		'titulo' => 'What our customers|<em>use it for.</em>',
		'lista'  => array(
			array( 'Motorsport', 'Cooling batteries between test sessions.', 'uso-industria' ),
			array( 'Pallet logistics', 'Keeping the load cold during the journey.', 'cajas-furgoneta' ),
			array( 'Pizza production kitchen', 'Keeping product cold in transit.', 'uso-transporte' ),
			array( 'Seed laboratory', 'Shipping frozen samples.', 'uso-laboratorio-entrega' ),
			array( 'Beer hall', 'Table-side smoke and cocktails every weekend.', 'uso-hosteleria' ),
			array( 'Sushi restaurants', 'Smoke when plating, at every service.', 'nuggets-ia' ),
		),
		'nota'   => 'Real DryIcePack customer cases. We don’t publish their names without permission.',
	),
	'alta' => array(
		'titulo'  => 'Open your account|in <em>one minute.</em>',
		'texto'   => 'Fill in the sentence and we’ll reply with your price, usually the same working day.',
		'frase'   => array(
			'We are', 'empresa', 'your company',
			'and we use dry ice for', 'uso', 'transport, smoke effects…',
			'. We need about', 'kg', '10',
			'kg', 'frecuencia', array( 'every week', 'every 2 weeks', 'every month', 'just once' ),
			', delivered to postcode', 'cp', '08302',
			'. You can call', 'nombre', 'your name',
			'on', 'telefono', '600 000 000',
			'or email', 'email', 'you@company.com',
		),
		'nif'          => 'Tax ID / NIF (optional)',
		'recordatorio' => 'Remind me when it’s time to reorder.',
		'boton'        => 'Get my price',
		'enviando'     => 'Sending…',
	),
	'faq' => array(
		'titulo' => 'Business questions',
		'lista'  => array(
			array( 'Is there a minimum order?', 'Online, from 3 kg. With a business account we adjust it to your usage.' ),
			array( 'How do we pay?', 'With one invoice a month, by bank transfer or direct debit, as agreed when we open the account.' ),
			array( 'Can I change the kilos or the frequency?', 'Yes. Give us 24 to 48 working hours’ notice so we can plan the preparation and the route.' ),
			array( 'Do you issue full invoices?', 'Always, with your tax details. For every order and in the monthly summary.' ),
			array( 'Do you deliver outside Catalonia?', 'Anywhere in mainland Spain by courier, with delivery the next morning. Large orders outside the province of Barcelona are assessed case by case.' ),
			array( 'Do you have a safety data sheet?', 'Yes. You can download it (in Spanish) from the safety page, together with the handling rules.' ),
		),
	),
	'final' => array( 'titulo' => 'Prefer to talk it through?', 'texto' => 'Sales: Monday to Friday, 9:00 to 18:00.' ),
);
