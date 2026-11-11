<?php
/* Zones d'entrega (índex i plantilla de zona) · català. Marcadors: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zones d\'entrega',
	'seo'  => array(
		// Pàgina nacional (10/11/2026): on arribem. Les condicions i els terminis són d'/ca/enviaments/.
		'titulo'      => 'Gel sec a domicili a tota la península | DryIcePack',
		'descripcion' => 'Gel sec a domicili a tota la península, província per província: demana abans de les 12:00 i arriba l\'endemà abans de les 14:00. Busca la teva ciutat.',
	),
	'mapa'   => 'Mapa amb la nau de Mataró al centre, anells cada 10 km i les zones d\'entrega',
	'indice' => array(
		'migas'   => array( 'Inici', 'Zones d\'entrega' ),
		'titulo'  => 'Gel sec a domicili|<em>a tota la península.</em>',
		'texto'   => 'Demana abans de les 12:00, de dilluns a dijous, i arriba l\'endemà abans de les 14:00 a qualsevol codi postal de la península. O tria el dia que vulguis. Ets a prop de Mataró? Recull-lo a la nau: {pk} kg per {recogida:pk}, IVA inclòs.',
		'provincias' => 'Tota la península, per comunitat autònoma',
		'grupos'  => 'A prop de Mataró, per comarca',
		'resto'   => 'No hi veus la teva ciutat? Arriba igual a qualsevol codi postal de la península, l\'endemà abans de les 14:00, de dimarts a divendres (el dissabte, segons la zona, abans de les 13:00). No enviem a les Balears, les Canàries, Ceuta ni Melilla.',
		'envios'  => 'Veure terminis i preus d\'enviament',
		'faq'     => array(
			array( 'On es pot comprar gel sec a prop meu?', 'En línia, en aquest web, siguis on siguis de la península: demana abans de les 12:00, de dilluns a dijous, i arriba l\'endemà abans de les 14:00 en caixa EPS, o el dia que triïs. Si ets a prop de Mataró, recull-lo a la nostra nau de dilluns a dissabte: {pk} kg per {recogida:pk}, IVA inclòs i en efectiu.' ),
			array( 'Envieu a les Balears, les Canàries, Ceuta o Melilla?', 'No. Només enviem a l\'Espanya peninsular, a qualsevol codi postal de la península.' ),
			array( 'Puc demanar-lo per a un dia concret?', 'Sí: en pagar tries qualsevol dia dels propers 90 amb repartiment a la teva zona, de dimarts a dissabte (el dissabte, segons la zona i amb preu de dissabte).' ),
		),
	),
	// Comunitats autònomes en català (clau: el nom en castellà normalitzat, inc/lugares.php dipt_lugar_normal)
	'ccaa' => array(
		'andalucia' => 'Andalusia', 'aragon' => 'Aragó', 'asturias' => 'Astúries', 'principado-de-asturias' => 'Astúries',
		'cantabria' => 'Cantàbria', 'castilla-y-leon' => 'Castella i Lleó', 'castilla-la-mancha' => 'Castella-la Manxa',
		'cataluna' => 'Catalunya', 'catalunya' => 'Catalunya', 'comunidad-valenciana' => 'Comunitat Valenciana', 'comunitat-valenciana' => 'Comunitat Valenciana',
		'extremadura' => 'Extremadura', 'galicia' => 'Galícia', 'rioja' => 'La Rioja', 'comunidad-de-madrid' => 'Comunitat de Madrid', 'madrid' => 'Comunitat de Madrid',
		'region-de-murcia' => 'Regió de Múrcia', 'murcia' => 'Regió de Múrcia', 'navarra' => 'Navarra', 'comunidad-foral-de-navarra' => 'Navarra',
		'pais-vasco' => 'País Basc', 'euskadi' => 'País Basc',
	),
	'zona' => array(
		'seo_titulo'      => 'Gel sec a {nombre}: abans de les 14:00 | DryIcePack',
		'seo_descripcion' => 'Gel sec a {nombre}: demana abans de les 12:00 i arriba l\'endemà abans de les 14:00. O recull-lo a la nau de Mataró, a {km} km: {pk} kg per {recogida:pk} amb IVA.',
		// Per a noms llargs (la descripció no ha de passar de 155 caràcters)
		'seo_descripcion_corta' => 'Gel sec a {nombre}: demana abans de les 12:00 i arriba l\'endemà abans de les 14:00, en caixa aïllant. O recull-lo a Mataró, a {km} km.',
		'migas'   => array( 'Inici', 'Zones', '{Nombre}' ),
		'titulo'  => 'Gel sec a {nombre},|<em>a la teva porta abans de les 14:00.</em>',
		'texto'   => 'Demana abans de les 12:00, de dilluns a dijous, i arriba a {nombre} l\'endemà abans de les 14:00. O tria el dia que vulguis. Des de {pk} kg i sense contractes. Prefereixes venir a buscar-lo? La nostra nau és a Mataró, a {km} km.',
		'boton'   => 'Fer la comanda',
		'datos'   => array(
			array( 'Distància a la nau', '{km} km' ),
			array( 'En cotxe, sense trànsit', 'uns {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'Quan arriba a {nombre}?|<em>I quant costa.</em>',
			'filas'  => array(
				array( 'Comanda abans de les 12:00', 'Arriba l\'endemà abans de les 14:00, de dimarts a divendres. Diumenge i dilluns no hi ha repartiment.' ),
				array( 'Dissabte', 'Si el teu codi postal té repartiment en dissabte, arriba abans de les 13:00, amb preu de dissabte: el veus en triar el dia. Demana\'l abans de les 12:00 de divendres.' ),
				array( 'Recollida', 'A la nostra nau de Mataró, a {km} km, de dilluns a dissabte. Preu rodó amb IVA inclòs: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}. El dissabte, +{sabado_recogida} (IVA inclòs).{sabado_libre} Tu tries el dia i et confirmem l\'hora. En efectiu i amb l\'import exacte.' ),
				array( 'Enviament de 10 kg', '17,32 € amb IVA, igual que a tota la península. El veus al total abans de pagar: sense sorpreses a l\'últim pas.' ),
				array( 'Per a empreses', 'Compte amb contracte de subministrament, descompte sobre tarifa segons el volum, comandes programades i una sola factura al mes, a 30 dies.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'Per a què el necessites?',
			'lista'  => array( array( 'hosteleria', 'Hostaleria' ), array( 'transporte', 'Transport en fred' ), array( 'industria', 'Indústria' ), array( 'laboratorios', 'Laboratoris' ), array( 'eventos', 'Festes i esdeveniments' ) ),
		),
		'faq'     => array(
			array( 'Quant triga a arribar el gel sec a {nombre}?', 'Si demanes abans de les 12:00 de dilluns a dijous, arriba a {nombre} l\'endemà abans de les 14:00. Si demanes divendres abans de les 12:00, arriba dissabte abans de les 13:00 (si el teu codi postal té repartiment, amb preu de dissabte) o dimarts. Diumenge i dilluns no hi ha repartiment. En pagar només veus els dies disponibles.' ),
			array( 'El puc recollir si soc a {nombre}?', 'Sí. La nostra nau és a Mataró, a {km} km (uns {min} min en cotxe sense trànsit). Tries el dia en demanar, de dilluns a dissabte, i et confirmem l\'hora per telèfon o WhatsApp. Preu rodó amb IVA inclòs, el mateix al web i a la nau: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}. El dissabte, +{sabado_recogida} (IVA inclòs).{sabado_libre} Es paga en efectiu i amb l\'import exacte: no tenim canvi. Al cotxe, porta\'l al maleter i ventilant.' ),
			array( 'Feu entregues en dissabte a {nombre}?', 'Depèn del codi postal. Si la teva zona té repartiment en dissabte, et surt com a opció en pagar, amb el seu preu de dissabte, i arriba abans de les 13:00. Per rebre\'l dissabte, demana abans de les 12:00 de divendres.' ),
		),
		'cerca'   => 'Zones properes',
		'fuente'  => 'Distàncies i temps: OpenStreetMap i OSRM, sense trànsit.',
	),
);
