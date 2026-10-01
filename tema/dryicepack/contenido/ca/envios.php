<?php
/* Enviaments i terminis · català */
return array(
	'menu' => 'Enviaments i terminis',
	'seo'  => array(
		'titulo'      => 'Enviament de gel sec en 24 h a la península | DryIcePack',
		'descripcion' => 'Demana abans de les 12:00 i el gel sec surt avui i arriba demà al matí. Tarifa per pes, dissabte segons la zona i recollida gratuïta a Mataró.',
	),
	'migas'  => array( 'Inici', 'Enviaments i terminis' ),
	'hero'   => array(
		'titulo'   => 'Enviaments i terminis|del <em>gel sec.</em>',
		'reloj'    => array( 'antes' => 'Temps per a l\'hora límit d\'avui', 'despues' => 'L\'hora límit d\'avui ja ha passat' ),
		'llega'    => array( 'antes' => 'Si demanes ara, arriba el %s al matí.', 'despues' => 'Si demanes ara, arriba el %s al matí.' ),
		'texto'    => 'El que entra abans de les 12:00, de dilluns a divendres, surt aquell dia i arriba l\'endemà al matí.',
	),
	'semana' => array(
		'titulo' => 'Quin dia arriba,|segons <em>quan demanis.</em>',
		'dias'   => array( 'Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres', 'Dissabte', 'Diumenge' ),
		'pides'  => 'Demanes abans de les 12:00',
		'llega'  => 'Arriba',
		'filas'  => array(
			array( 0, 1, '' ),
			array( 1, 2, '' ),
			array( 2, 3, '' ),
			array( 3, 4, '' ),
			array( 4, 5, 'Dissabte segons la zona, +11,74 €. Si no, dimarts.' ),
		),
		'finde'  => 'Les comandes de dissabte i diumenge, o de divendres després de les 12:00, surten dilluns i arriben dimarts.',
		'festivos' => 'Els dies festius de Catalunya no s\'envien comandes. En el pagament només apareixen els dies que es poden triar.',
	),
	'tarifa' => array(
		'titulo' => 'Mou els quilos,|mira <em>l\'enviament.</em>',
		'kg'     => 'Quilos de gel',
		'cajas'  => 'Caixes',
		'peso'   => 'Pes que factura la missatgeria: %s kg',
		'coste'  => 'Enviament',
		'nota'   => 'IVA inclòs. El pes facturable és el gel més 1 kg per caixa, arrodonit a l\'alça. Màxim 250 kg per comanda en línia.',
		'tramos' => array( 'fins a 2 kg', 'fins a 5 kg', 'fins a 10 kg', 'més de 10 kg' ),
	),
	'recogida' => array(
		'titulo' => 'Recollida|a <em>Mataró.</em>',
		'lista'  => array(
			array( 'On', 'Camí Ca La Madrona 19 D, 08304 Mataró (Barcelona). És la nostra nau, no una oficina de la missatgeria.' ),
			array( 'Quan', 'De dilluns a dissabte, també festius. Tria el dia en pagar i et confirmem l\'hora per telèfon o WhatsApp. El dissabte té un suplement d\'11,74 € (IVA inclòs).' ),
			array( 'Quant', 'Gratis. Només pagues el gel, en efectiu quan el recculls.' ),
			array( 'Com portar-lo', 'Al maleter i amb ventilació. Mai a l\'habitacle tancat.' ),
		),
		'alt'    => 'Operari amb un cassó de pèl·lets de gel sec al magatzem',
		'como'   => 'Com arribar-hi',
	),
	'cobertura' => array(
		'titulo' => 'Arriba a la meva zona?',
		'si'     => array( 'titulo' => 'Sí, l\'endemà al matí', 'texto' => 'Tota l\'Espanya peninsular: de Girona a Huelva i de la Corunya a Múrcia.' ),
		'no'     => array( 'titulo' => 'Consulta\'ns', 'texto' => 'Illes Balears, Canàries, Ceuta, Melilla i fora d\'Espanya. El gel sec viatja com a mercaderia perillosa (UN 1845) i no podem garantir l\'entrega en 24 h.' ),
		'zonas'  => 'Veure les zones d\'entrega a prop de Barcelona',
	),
	'faq' => array(
		'titulo' => 'Dubtes sobre l\'enviament',
		'lista'  => array(
			array( 'Com va embalat?', 'En caixa d\'EPS amb parets de 40 mm, tancada amb la tapa però no hermètica, perquè el CO₂ pugui sortir durant el viatge.' ),
			array( 'Arriba el mateix pes que demano?', 'El gel sec se sublima des que surt de la nau, així que durant el viatge perd una mica de pes. Per això surt el mateix dia que es prepara i arriba l\'endemà al matí: el mínim temps possible en camí.' ),
			array( 'I si no hi soc quan arriba?', 'La missatgeria ho torna a intentar. Avisa\'ns com més aviat millor per seguir l\'enviament: el gel no espera.' ),
			array( 'Puc demanar una hora concreta?', 'El repartiment és al matí i l\'hora exacta la marca la ruta de la missatgeria. Si necessites una hora fixa, la recollida a Mataró et dona control total.' ),
			array( 'Feu enviaments urgents el mateix dia?', 'No per missatgeria. Si ets a prop de Mataró, el pots recollir el mateix dia si demanes abans de les 12:00.' ),
		),
	),
);
