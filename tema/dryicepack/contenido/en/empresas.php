<?php
/* Empresas (cuenta de empresa y presupuesto por volumen) · inglés */
return array(
	'menu' => 'Business',
	'seo'  => array(
		'titulo'      => 'Dry ice supplier for businesses in Spain | DryIcePack',
		'descripcion' => 'Dry ice supplier for your business: a supply contract, a discount on list prices by volume and one invoice a month on 30-day terms. Ask for your price.',
	),
	'migas' => array( 'Home', 'Business' ),
	'hero'  => array(
		'titulo' => 'Dry ice supplier:|<em>every week,|without having to reorder.</em>',
		'texto'  => 'Tell us how many kilos and how often, and it arrives in the morning. We sign a supply contract with a discount on list prices based on volume and frequency, and invoice you once a month, payable within 30 days.',
		'boton'  => 'Open my account',
		'tel'    => 'or call sales:',
		'alt'    => 'Worker opening a box of dry ice in the warehouse',
	),
	'pedir' => array(
		'titulo'   => 'How do you order?|<em>Whichever way suits you.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'One message with the kilos and the day, and it’s done. We confirm the delivery in the same chat.' ),
			array( 'Online', 'Log in to your account and repeat any order in one click, without filling anything in again.' ),
			array( 'Email', 'To set up a standing order, written just once, or for deliveries to several addresses.' ),
		),
		'chat'     => array( 'Hi, can you send us 10 kg of 16 mm for Tuesday?', 'Done. It leaves on Monday and arrives on Tuesday morning. It goes on this month’s invoice.' ),
		'repetir'  => array( 'titulo' => 'Your orders', 'filas' => array( array( '10 kg · 16 mm', 'Repeat' ), array( '20 kg · 3 mm', 'Repeat' ), array( '10 kg · 16 mm', 'Repeat' ) ) ),
		'email'    => array( 'To: info@dryicepack.es', 'Subject: Standing order', 'Good morning. From now on, 15 kg of 16 mm every Tuesday to our Rubí warehouse, please. Thank you.' ),
	),
	'ventajas' => array(
		'titulo' => 'What do you gain|with a <em>business account?</em>',
		'lista'  => array(
			array( 'Discount on list prices', 'Based on volume and frequency. Agreed when you open the account and applied to every order.' ),
			array( 'One invoice a month', 'All the month’s orders on one full invoice, payable within 30 days by SEPA direct debit or bank transfer. Less paperwork for you and your accountant.' ),
			array( 'Details saved', 'Company name, tax ID and delivery addresses are saved once. You never type them again.' ),
			array( 'Standing order or reminder', 'If you always order the same, we schedule it and it just arrives. If you’d rather decide each time, we let you know when it’s due.' ),
			array( 'Supply contract', 'Kilos, frequency, discount and payment method, in writing. Has the season changed? We adjust it with you.' ),
		),
	),
	'volumen' => array(
		'titulo'  => 'How much|<em>do you need?</em>',
		'rango'   => 'Kilos per order',
		'cajas'   => '%1$s boxes · %2$s kg',
		'caja'    => '1 box · %s kg',
		'notas'   => array(
			'web'   => 'Up to 150 kg: online, through your account or by WhatsApp, delivered anywhere in mainland Spain.',
			'bcn'   => 'Over 150 kg and up to 250 kg: in the province of Barcelona we deliver to your premises; elsewhere, delivery is arranged individually and costs more. Message us and we’ll give you a price and a day.',
			'fuera' => 'Over 250 kg per order: message us and we’ll give you a price and a day.',
		),
	),
	'casos' => array(
		'titulo' => 'Who uses it already?|<em>From Formula E to sushi.</em>',
		'lista'  => array(
			// Con cliente: solo fotos reales del producto. Sin cliente: el uso, sin más datos.
			array( 'Motorsport', 'Cooling batteries fast.', 'caja-16mm-lateral', 'nissan' ),
			array( 'Pallet transport', '', 'cajas-furgoneta' ),
			array( 'Frozen pizza', 'Cold-chain transport.', 'nuggets-azul', 'italpizza' ),
			array( 'Seed company', 'Sample transport.', 'cenital-caja-nuggets', 'fito' ),
			array( 'Smoke and cocktails', '', 'uso-hosteleria' ),
			array( 'Sushi restaurants', 'Smoke effect when plating.', 'anadiendo-hielo', 'sushitok' ),
		),
		'logos'  => array(
			'nissan'    => 'Nissan Formula E Team logo',
			'italpizza' => 'Italpizza logo',
			'fito'      => 'Semillas Fitó logo',
			'sushitok'  => 'Sushitok logo',
		),
		'nota'   => 'Real DryIcePack customers, published with their permission.',
	),
	'alta' => array(
		'titulo'  => 'Open your account|<em>with a single sentence.</em>',
		'texto'   => 'Complete it and we’ll reply with your price, usually the same working day.',
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
		'titulo' => 'What businesses ask us',
		'lista'  => array(
			array( 'Is there a minimum order?', 'Online, from 3 kg. With a business account, we adjust it to what you use.' ),
			array( 'How do we pay?', 'With a single invoice a month, payable within 30 days, by SEPA direct debit where possible or by bank transfer.' ),
			array( 'Do we need to sign a contract?', 'For a business account, yes: a dry ice supply contract setting out the kilos, the frequency, the discount on list prices and the payment method. To buy online you don’t need one: order and pay there and then, like any customer.' ),
			array( 'What price will we get?', 'A discount on the online list prices, based on your volume and frequency. It’s agreed when you open the account. Fill in the sentence above and we’ll reply, usually the same working day.' ),
			array( 'Can I change the kilos or the frequency?', 'Yes, whenever you need. Let us know in advance by WhatsApp, phone or email and we’ll adjust it.' ),
			array( 'Do you issue full invoices?', 'Yes, always, with your tax details. With a business account you get a single invoice a month covering all your orders.' ),
			array( 'Do you deliver outside Catalonia?', 'Yes, anywhere in mainland Spain by courier. If you order before 12:00 (Spanish time), it arrives the next morning, Tuesday to Saturday (Saturday depending on the area, with an €11.74 surcharge, VAT included). Over 150 kg outside the province of Barcelona: message us and we’ll arrange it individually.' ),
			array( 'Do you have a safety data sheet?', 'Yes. You can download it (in Spanish) from the safety page, together with the handling rules for your team.' ),
		),
	),
	'final' => array( 'titulo' => 'Prefer to talk to a person?', 'texto' => 'Sales: Monday to Friday, 9:00 to 18:00. Outside those hours, message us on WhatsApp at +34 686 980 471.' ),
);
