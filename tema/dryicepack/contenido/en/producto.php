<?php
/* Comprar (ficha de producto) · inglés */
return array(
	'menu' => 'Buy dry ice',
	'seo'  => array(
		'titulo'      => 'Buy dry ice online: {pk}, 10, 15 and 20 kg | DryIcePack',
		'descripcion' => 'Buy dry ice from {pk} kg, as 3 mm pellets or 16 mm nuggets, with delivery in the total. Order before 12:00 and it arrives the next day before 14:00.',
	),
	'migas'   => array( 'Home', 'Buy dry ice' ),
	'titulo'  => 'Buy dry ice with no surprise delivery costs',
	'sub'     => 'Order before 12:00 (Spanish time), Monday to Thursday, and it arrives the next day before 14:00. Or pick any day you like. The total already includes delivery.',
	'galeria' => array(
		'3mm'  => array( array( 'caja-3mm', 'Open box of 3 mm dry ice pellets, with vapour' ), array( 'cenital-3mm', 'Top view of 3 mm dry ice pellets' ), array( 'caja-3mm-lateral', 'EPS box of 3 mm pellets with the lid propped against it' ) ),
		'16mm' => array( array( 'caja-16mm-niebla', 'Open box of 16 mm dry ice nuggets, with vapour' ), array( 'cenital-16mm', 'Top view of 16 mm dry ice nuggets' ), array( 'cenital-caja-nuggets', 'Open EPS box of 16 mm nuggets, seen from above' ) ),
	),
	'formato' => 'Format',
	'formatos' => array(
		'3mm'  => array( '3 mm pellets', 'Cools faster. More fog.' ),
		'16mm' => array( '16 mm nuggets', 'Lasts longer. Transport and samples.' ),
	),
	'kilos'     => 'Kilos per box',
	'cajas'     => 'Boxes',
	'menos'     => 'Remove a box',
	'mas'       => 'Add a box',
	'cp'        => 'Your postcode',
	'cp_ph'     => '08301',
	// Line under the field: help and status (one short line)
	'cp_ayuda'     => 'To see which day it arrives',
	'cp_buscando'  => 'Finding your area…',
	'cp_correcto'  => 'Postcode OK',
	'cp_fuera'     => 'Outside mainland Spain',
	'cp_medida'    => 'Custom delivery',
	'cp_no_valido' => 'Postcode not valid',
	'cp_corto'     => 'Missing digits: 5 needed',
	// Delivery line with the postcode (%1$s = "08301 · Barcelona", %2$s = date); "medida" ends with a WhatsApp link; "fuera" doesn't (no delivery)
	'llega_cp_ok'     => '%1$s — arrives on %2$s before 14:00',
	'llega_cp_sabado' => 'Or %1$s before 13:00, in some areas, at the Saturday rate', // no %2$s since 11/11/2026: the Saturday price depends on the destination and the kilos
	'llega_cp_fuera'  => 'we only deliver to mainland Spain: not to the Balearic or Canary Islands, Ceuta or Melilla.',
	'llega_cp_medida' => 'over 150 kg outside the province of Barcelona: order via',
	'llega_cp_error'  => 'Postcode %s does not exist in Spain. Check the 5 digits.',
	'llega'     => 'It arrives on %s before 14:00',
	'llega_hoy' => 'Order before 12:00 (Spanish time) and it arrives on %s before 14:00',
	'fuera'     => 'We only deliver to mainland Spain: not to the Balearic or Canary Islands, Ceuta or Melilla.',
	'precio'    => 'Dry ice',
	'envio'     => 'Delivery',
	'total'     => 'Total',
	'iva'       => 'VAT included',
	'boton'     => 'Place my order',
	'recogida'  => 'Or collect it in Mataró, Monday to Saturday: {pk} kg for {recogida:pk} and 10 kg for {recogida:10}, VAT included. On Saturday, +{sabado_recogida}.{sabado_libre} Cash only, exact amount.',
	'recoge_ahorro' => 'Or collect it in Mataró: %1$s in cash, you save %2$s. Monday to Saturday; on Saturday, +{sabado_recogida}. Exact amount only.',
	'corte'     => array( 'antes' => 'To get it on %s, order before 12:00 (Spanish time).', 'quedan' => 'Time left: %s.', 'despues' => '' ),
	'max'       => 'Up to 250 kg per order. Need more? Message us and we’ll give you a price and a day.',
	'medida'    => 'Over 150 kg outside the province of Barcelona: that delivery is arranged individually. Message us on',
	'confianza' => array( 'Card, Apple Pay or Google Pay', 'Invoices for businesses', 'No account needed' ),
	// Información de seguridad del producto junto al botón de compra (Reglamento (UE) 2023/988, art. 19)
	'gpsr' => array(
		'titulo' => 'Manufacturer and product safety',
		'filas'  => array(
			array( 'Manufacturer', 'INDUNOVA IMS S.L. (DryIcePack brand) · Ronda Alfonso X El Sabio 10, 2º 1ª, 08301 Mataró, Spain · info@dryicepack.es' ),
			array( 'Product', 'Solid carbon dioxide (dry ice), UN 1845, as 3 mm pellets or 16 mm nuggets, in an EPS box.' ),
			array( 'Warnings', 'At −78.5 °C it burns the skin: use thermal gloves or tongs. Only in ventilated spaces: CO₂ displaces oxygen. Never in a closed container. Never eat or drink it. Keep out of reach of children and pets. In the car, in the boot and with ventilation.' ),
		),
		'enlace' => 'Safety rules and data sheet',
	),
	'caja' => array(
		'titulo' => 'How it arrives|<em>at your door.</em>',
		'capas'  => array(
			array( 'EPS lid', 'Fits without an airtight seal: the gas escapes little by little.' ),
			array( 'Dry ice', '3 mm pellets or 16 mm nuggets, at −78.5 °C.' ),
			array( '40 mm EPS box', 'Thick polystyrene walls: they insulate and make the ice last longer.' ),
		),
	),
	'comparar' => array(
		'titulo' => '3 mm or 16 mm?|<em>Drag</em> to compare.',
		'izq'    => array( '3 mm', 'More surface area: cools faster and makes more fog. For cocktails, effects and processes.' ),
		'der'    => array( '16 mm', 'More mass per piece: lasts longer in the box. For transport, samples and the cold chain.' ),
		'rango'  => 'Slide to compare 3 mm and 16 mm',
	),
	'cuanto' => array(
		'titulo' => 'How much|<em>do I need?</em>',
		'texto'  => 'For fog, use these quantities as a guide. Is it for transport or samples? Tell us how many hours and what volume, and we’ll work it out for you.',
		'casos'  => array(
			array( '{pk} kg', 'At home', 'A cauldron, a punch bowl with a double container, a small party.' ),
			array( '10 kg', 'Big party', 'Fog across the floor in several rounds through the night.' ),
			array( '15–20 kg', 'Bar or event', 'A long service, a stage or several bars.' ),
		),
		'calcula' => 'Ask us on WhatsApp',
		'seguridad' => 'Before you use it, read the six safety rules',
	),
	'tarifa' => array(
		'titulo' => 'What does|<em>delivery cost?</em>',
		'texto'  => 'The courier’s rate for delivery before 14:00, the same across mainland Spain. It’s based on the weight of the ice plus 1 kg per box, and you see it before you pay. Delivery before 12:00 or before 10:00, and on Saturday, has its own price depending on the destination and the kilos: you see it when you choose the day and the time.',
		'filas'  => array( array( 'Up to 2 kg', 8.53 ), array( 'Up to 5 kg', 10.32 ), array( 'Up to 10 kg', 13.19 ), array( 'Each extra kg', 1.12 ) ),
		// 11/11/2026: no Saturday row. It is no longer a fixed surcharge: it depends on the destination and the kilos (shown when choosing the day)
		'extra'  => array( array( 'Collection in Mataró, no courier', 0 ) ),
		'nota'   => 'Prices include VAT. Collection is paid in cash, with the exact amount.',
		'gratis' => '€0', // collection row of the delivery table: no delivery cost (the ice is paid at the collection price)
		'enlace' => 'Delivery times, holidays and collection',
		'zonas'  => 'Delivery across mainland Spain',
	),
	'faq' => array(
		'titulo'   => 'Before you pay',
		'empresas' => 'Business account with monthly invoicing',
		'lista'    => array(
			array( 'When will it arrive?', 'If you order before 12:00, Monday to Thursday, it leaves the same day and arrives the next day before 14:00. Order before 12:00 on Friday and it arrives on Saturday before 13:00 (depending on the area, at the Saturday rate) or on Tuesday. We don’t deliver on Sunday or Monday. You choose the day at checkout.' ),
			array( 'Can I order for another day?', 'Yes: at checkout you can choose any day in the next 90 with delivery to your area, Tuesday to Saturday (Saturday depending on the area, at the Saturday rate). Order it for the day you’ll use it: dry ice sublimates over time.' ),
			// Own measurement, 10–11/11/2026. [PENDIENTE: update the 3 kg sentence when the small pack becomes 5 kg.]
			array( 'Will I receive the weight I ordered?', 'Not quite: dry ice sublimates from the moment it is packed, even in a closed box. We measured it in our warehouse with the boxes closed: over 29 hours the small box lost 1.8 kg, the large one (15 and 20 kg) 4.1 kg and the 30 kg box 5.7 kg. Order 3 kg and about 2 kg arrives. In hot weather, or if the box is opened, it goes faster. Order it for the day you’ll use it and, if you need an exact amount, choose a bigger box.' ),
			array( 'What if I need an invoice?', 'If you’re an individual and the order is up to €400 (VAT included), the simplified invoice reaches you by email from info@dryicepack.es, without asking. If you’re buying for a company or as self-employed, tick the box at checkout and enter the company name and tax ID: you get the full invoice. If you forget, we’ll send you a link to request it.' ),
			array( 'Can I return it?', 'Dry ice sublimates from the moment it leaves us, so there is no right of withdrawal. It does have a legal guarantee: if it arrives in poor condition, we replace it or refund you, at no cost. Let us know the same day, with photos if you can, and we’ll replace it sooner.' ),
			array( 'How do I store it when it arrives?', 'With thermal gloves, never with bare hands. In its box, with the lid on but not taped shut, somewhere ventilated and out of reach of children and pets. Never in the freezer or an airtight container: the pressure can burst it. It must never be eaten or drunk. And if you take it by car, put it in the boot and keep the car ventilated.' ),
		),
	),
	'barra' => 'Total',
	// Redesign 14/10/2026 (presentation only)
	'galeria_t'     => 'Photos of the chosen size',
	'galeria_macro' => array(
		'3mm'  => array( 'pellets-ia', '3 mm dry ice pellets, close up' ),
		'16mm' => array( 'nuggets-ia', '16 mm dry ice nuggets, close up' ),
	),
	'caja_foto'     => array(
		'3mm'  => 'EPS box of 3 mm pellets with the lid propped against it',
		'16mm' => 'EPS box of 16 mm nuggets with the lid propped against it',
	),
);
