<?php
/* Zones d'entrega (índex i plantilla de zona) · català. Marcadors: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zones d\'entrega',
	'seo'  => array(
		'titulo'      => 'Gel sec a prop de Barcelona: zones d\'entrega | DryIcePack',
		'descripcion' => 'Gel sec a Barcelona, al Maresme, al Vallès i al Baix Llobregat: demana abans de les 12:00 i demà el tens, o recull-lo gratis a Mataró. Busca la teva zona.',
	),
	'mapa'   => 'Mapa amb la nau de Mataró al centre, anells cada 10 km i les zones d\'entrega',
	'indice' => array(
		'migas'   => array( 'Inici', 'Zones d\'entrega' ),
		'titulo'  => 'Gel sec a prop teu,|<em>demà al matí.</em>',
		'texto'   => 'Demana abans de les 12:00 i demà al matí el tens (de dimarts a dissabte), o tria el dia que vulguis. Ets a prop de Mataró? Recull-lo gratis. Busca la teva zona i mira a quants quilòmetres tens la nostra nau.',
		'grupos'  => 'Per comarca',
		'resto'   => 'No hi veus la teva zona? Si és a la península, també t\'arriba l\'endemà al matí. Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.',
		'envios'  => 'Veure terminis i preus d\'enviament',
	),
	'zona' => array(
		'seo_titulo'      => 'Gel sec a {nombre}: demà a la teva porta | DryIcePack',
		'seo_descripcion' => 'Gel sec a {nombre}: demana abans de les 12:00 i demà al matí el tens en caixa aïllant. O recull-lo gratis a la nau de Mataró, a {km} km. Des de 3 kg.',
		'seo_descripcion_corta' => 'Gel sec a {nombre}: demana abans de les 12:00 i demà al matí el tens en caixa aïllant. O recull-lo gratis a Mataró, a {km} km. Des de 3 kg.',
		'migas'   => array( 'Inici', 'Zones', '{Nombre}' ),
		'titulo'  => 'Gel sec a {nombre},|<em>demà a la teva porta.</em>',
		'texto'   => 'Demana abans de les 12:00 i demà al matí el tens a {nombre} (de dimarts a dissabte), o tria el dia que vulguis. Des de 3 kg i sense contractes. Prefereixes venir a buscar-lo? La nostra nau és a Mataró, a {km} km.',
		'boton'   => 'Fer la comanda',
		'datos'   => array(
			array( 'Distància a la nau', '{km} km' ),
			array( 'En cotxe, sense trànsit', 'uns {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'Quan arriba a {nombre}?|<em>I quant costa.</em>',
			'filas'  => array(
				array( 'Comanda abans de les 12:00', 'El tens l\'endemà al matí, de dimarts a divendres. Diumenge i dilluns no hi ha repartiment.' ),
				array( 'Dissabte', 'Si el teu codi postal té repartiment en dissabte, amb un suplement d\'11,74 € (IVA inclòs). Demana\'l abans de les 12:00 de divendres.' ),
				array( 'Recollida', 'A la nostra nau de Mataró, a {km} km, de dilluns a dissabte. Gratis entre setmana; el dissabte, amb suplement. Tu tries el dia i et confirmem l\'hora.' ),
				array( 'Enviament de 10 kg', '17,32 € amb IVA, igual que a tota la península. El veus al total abans de pagar: sense sorpreses a l\'últim pas.' ),
				array( 'Per a empreses', 'Compte amb contracte de subministrament, descompte sobre tarifa segons el volum, comandes programades i una sola factura al mes, a 30 dies.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'Per a què el necessites?',
			'lista'  => array( array( 'hosteleria', 'Hostaleria' ), array( 'transporte', 'Transport en fred' ), array( 'industria', 'Indústria' ), array( 'laboratorios', 'Laboratoris' ), array( 'eventos', 'Festes i esdeveniments' ) ),
		),
		'faq'     => array(
			array( 'Quant triga a arribar el gel sec a {nombre}?', 'Si demanes abans de les 12:00 de dilluns a dijous, el tens a {nombre} l\'endemà al matí. Si demanes divendres, arriba dissabte (si el teu codi postal té repartiment, amb suplement) o dimarts. Diumenge i dilluns no hi ha repartiment. En pagar només veus els dies disponibles.' ),
			array( 'El puc recollir si soc a {nombre}?', 'Sí. La nostra nau és a Mataró, a {km} km (uns {min} min en cotxe sense trànsit). Tries el dia en demanar, de dilluns a dissabte, i et confirmem l\'hora per telèfon o WhatsApp. Entre setmana és gratis i es paga en efectiu quan el reculls. Al cotxe, porta\'l al maleter i ventilant.' ),
			array( 'Feu entregues en dissabte a {nombre}?', 'Depèn del codi postal. Si la teva zona té repartiment en dissabte, et surt com a opció en pagar, amb un suplement d\'11,74 € (IVA inclòs). Per rebre\'l dissabte, demana abans de les 12:00 de divendres.' ),
		),
		'cerca'   => 'Zones properes',
		'fuente'  => 'Distàncies i temps: OpenStreetMap i OSRM, sense trànsit.',
	),
);
