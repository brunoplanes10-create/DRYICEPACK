<?php
/* Envíos y plazos · inglés */
return array(
	'menu' => 'Delivery times and costs',
	'seo'  => array(
		'titulo'      => 'Dry ice delivery in 24 h across mainland Spain | DryIcePack',
		'descripcion' => 'Order before 12:00 and your dry ice leaves today and arrives tomorrow morning. Rates by weight, Saturday delivery by area and free collection in Mataró.',
	),
	'migas'  => array( 'Home', 'Delivery times and costs' ),
	'hero'   => array(
		'titulo'   => 'Dry ice delivery:|<em>times and costs.</em>',
		'reloj'    => array( 'antes' => 'Time left until today’s cutoff', 'despues' => 'Today’s cutoff has passed' ),
		'llega'    => array( 'antes' => 'Order now for delivery on the morning of %s.', 'despues' => 'Order now for delivery on the morning of %s.' ),
		'texto'    => 'Orders placed before 12:00 (Spanish time), Monday to Friday, leave that day and arrive the next morning.',
	),
	'semana' => array(
		'titulo' => 'Which day it arrives,|depending on <em>when you order.</em>',
		'dias'   => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
		'pides'  => 'You order before 12:00',
		'llega'  => 'Arrives',
		'filas'  => array(
			array( 0, 1, '' ),
			array( 1, 2, '' ),
			array( 2, 3, '' ),
			array( 3, 4, '' ),
			array( 4, 5, 'Saturday depending on area, +€11.74. Otherwise, Tuesday.' ),
		),
		'finde'  => 'Orders placed on Saturday or Sunday, or on Friday after 12:00, leave on Monday and arrive on Tuesday.',
		'festivos' => 'No orders leave on Catalan public holidays. At checkout, you only see the days you can choose.',
	),
	'tarifa' => array(
		'titulo' => 'Move the kilos,|see <em>the delivery cost.</em>',
		'kg'     => 'Kilos of ice',
		'cajas'  => 'Boxes',
		'peso'   => 'Weight charged by the courier: %s kg',
		'coste'  => 'Delivery',
		'nota'   => 'VAT included. The chargeable weight is the ice plus 1 kg per box, rounded up. Maximum 250 kg per online order.',
		'tramos' => array( 'up to 2 kg', 'up to 5 kg', 'up to 10 kg', 'over 10 kg' ),
	),
	'recogida' => array(
		'titulo' => 'Collection|in <em>Mataró.</em>',
		'lista'  => array(
			array( 'Where', 'Camí Ca La Madrona 19 D, 08304 Mataró (Barcelona). It’s our own warehouse, not a courier depot.' ),
			array( 'When', 'Monday to Saturday, including public holidays. Choose the day at checkout and we confirm the time by phone or WhatsApp. Saturday has a €11.74 surcharge (VAT included).' ),
			array( 'Cost', 'Free. You only pay for the ice, in cash on collection.' ),
			array( 'How to carry it', 'In the boot, with ventilation. Never inside a closed car.' ),
		),
		'alt'    => 'Worker with a scoop of dry ice pellets in the warehouse',
		'como'   => 'Get directions',
	),
	'cobertura' => array(
		'titulo' => 'Do you deliver to my area?',
		'si'     => array( 'titulo' => 'Yes, the next morning', 'texto' => 'All of mainland Spain: from Girona to Huelva and from A Coruña to Murcia.' ),
		'no'     => array( 'titulo' => 'Ask us', 'texto' => 'Balearic Islands, Canary Islands, Ceuta, Melilla and outside Spain. Dry ice travels as dangerous goods (UN 1845) and we can’t guarantee 24-hour delivery.' ),
		'zonas'  => 'See delivery areas near Barcelona',
	),
	'faq' => array(
		'titulo' => 'Delivery questions',
		'lista'  => array(
			array( 'How is it packed?', 'In an EPS box with 40 mm walls, closed with its lid but not airtight, so the CO₂ can escape during the journey.' ),
			array( 'Will the full weight I ordered arrive?', 'Dry ice sublimates from the moment it leaves the warehouse, so it loses some weight in transit. That’s why it leaves the day it’s packed and arrives the next morning: the shortest possible time on the road.' ),
			array( 'What if I’m not in when it arrives?', 'The courier makes its usual redelivery attempts. Let us know as soon as possible so we can track the parcel: the ice won’t wait.' ),
			array( 'Can I ask for a specific time?', 'Deliveries are in the morning and the exact time depends on the courier’s route. If you need a fixed time, collection in Mataró gives you full control.' ),
			array( 'Do you offer same-day delivery?', 'Not by courier. If you’re near Mataró, you can collect it the same day if you order before 12:00.' ),
		),
	),
);
