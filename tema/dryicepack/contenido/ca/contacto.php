<?php
/* Contacte · català */
return array(
	'menu' => 'Contacte',
	'seo'  => array(
		'titulo'      => 'Contacte: telèfon, WhatsApp i nau a Mataró | DryIcePack',
		'descripcion' => 'Truca al 936 73 76 41 o escriu per WhatsApp al 686 980 471 i et donem preu i dia d\'entrega. O recull la teva comanda a Mataró, de dilluns a dissabte.',
	),
	'migas'  => array( 'Inici', 'Contacte' ),
	'titulo' => 'Parlem.',
	'estado' => array(
		'abierto' => 'Ara som a la nau. Et responem de seguida.',
		'cerrado' => 'Ara hem tancat, però la botiga en línia no. Escriu-nos i et responem el pròxim dia feiner a partir de les 9:00.',
	),
	'canales' => array(
		array( 'telefono', 'Telèfon', 'Per al que és urgent: comandes d\'última hora i dubtes ràpids.' ),
		array( 'whatsapp', 'WhatsApp', 'Demana, envia una foto o repeteix la comanda sense trucar.' ),
		array( 'email', 'Correu electrònic', 'Per a pressupostos, factures i documentació: tot per escrit.' ),
	),
	'form' => array(
		'titulo'  => 'Prefereixes escriure?',
		'texto'   => 'Digues-nos per a què el fas servir, quants quilos necessites i el teu codi postal. Et responem amb el preu i la data d\'entrega, normalment el mateix dia feiner.',
		'nombre'  => 'Nom',
		'email'   => 'Correu electrònic',
		'telefono'=> 'Telèfon',
		'mensaje' => 'Què necessites i per a quan?',
		'boton'   => 'Rebre resposta',
		'enviando'=> 'Enviant…',
		// Redisseny 14/10/2026: línia de dades sobre el formulari i foto de l'esquerra
		'linea'   => 'Ús · kg · codi postal → preu i data',
		'alt'     => 'Guant tèrmic tirant pèl·lets de gel sec en una caixa EPS',
	),
	'nave' => array(
		'titulo'  => 'La nostra nau a Mataró',
		'horario' => 'Atenem de dilluns a divendres, de 9:00 a 18:00',
		'recoger' => 'Recollida de dilluns a dissabte, també festius, a l\'hora que et confirmem. Preu rodó amb IVA inclòs: {pk} kg per {recogida:pk} i 10 kg per {recogida:10}. El dissabte, +{sabado_recogida} (IVA inclòs).{sabado_libre} En efectiu i amb l\'import exacte: no tenim canvi.',
		'como'    => 'Com arribar-hi',
		'empresa' => 'Dades de l\'empresa',
		'alt'     => 'Caixes de gel sec preparades al costat de la furgoneta de repartiment',
		'alt_reparto' => 'Repartidor carregant una caixa de gel sec a la furgoneta',
	),
);
