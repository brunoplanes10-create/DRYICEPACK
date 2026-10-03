<?php
/* Gel sec per a festes i esdeveniments · català · tu */
return array(
	'menu' => 'Gel sec per a festes i esdeveniments',
	'seo'  => array(
		'titulo'      => 'Gel sec per a festes i esdeveniments: boira baixa',
		'descripcion' => 'Gel sec per a festes, casaments i Halloween: boira arran de terra des de 3 kg. Demana abans de les 12:00 i demà el tens a casa, a punt per fer servir.',
	),
	'migas' => array( 'Inici', 'Usos', 'Festes i esdeveniments' ),
	'hero'  => array(
		'titulo' => 'Gel sec per a festes:|boira arran de terra, <em>demà a la teva porta.</em>',
		'texto'  => 'Vols aquella boira que cobreix el terra en el primer ball, a l\'escenari o a Halloween? Es fa amb aigua calenta i gel sec. Demana abans de les 12:00 i demà al matí el tens (de dimarts a dissabte), o tria el dia que vulguis. Des de 3 kg.',
		'boton'  => 'Fer la comanda',
		'alt'    => 'Parella ballant en un casament sobre una capa de boira baixa de gel sec',
	),
	'recetas' => array(
		'titulo' => 'Quant gel sec|<em>necessites?</em>',
		'lista'  => array(
			array( 'A casa', '3 kg', array( 'Un calder o una ponxera amb doble recipient', 'Aigua calenta', 'Pèl·lets de 3 mm' ), 'Una nit de festa petita o una taula de Halloween.' ),
			array( 'Festa gran', '10 kg', array( 'Dos o tres recipients amples', 'Aigua calenta per anar canviant', 'Pèl·lets de 3 mm' ), 'Boira arran de terra en diverses tandes durant la nit.' ),
			array( 'Bar o esdeveniment', '15–20 kg', array( 'Recipients en diversos punts', 'Aigua calenta a mà', 'Pèl·lets de 3 mm' ), 'Un servei llarg, un escenari o diverses barres.' ),
		),
		'ingredientes' => 'Necessites',
	),
	'pasos' => array(
		'titulo' => 'Com es fa la boira?|<em>En tres passos.</em>',
		'lista'  => array(
			array( 'Aigua calenta', 'En un recipient ample i obert. Com més calenta, més boira.' ),
			array( 'Pèl·lets, amb pinces', 'Afegeix-ne uns quants cada vegada, amb guants. Veuràs la boira a l\'instant.' ),
			array( 'Recarrega', 'Quan baixi, canvia l\'aigua per aigua calenta i afegeix-hi més pèl·lets.' ),
		),
	),
	'halloween' => array(
		'titulo' => 'Aquest any, Halloween cau en dissabte.',
		'texto'  => 'Demana abans de les 12:00 del dijous 29 d\'octubre i el tens divendres 30. El vols el mateix dissabte? Demana\'l abans de les 12:00 de divendres: segons la zona i amb un suplement d\'11,74 €.',
		'enlace' => 'Gel sec per a Halloween',
	),
	'seguridad' => array(
		'titulo' => 'És segur|<em>amb convidats?</em>',
		'lista'  => array( 'La caixa, fora de l\'abast de nens, mascotes i convidats.', 'La sala, ventilada: una porta o una finestra oberta.', 'Mai dins una beguda ni dins un recipient tancat.', 'Guants tèrmics i pinces: mai amb la mà.' ),
	),
	'faq' => array(
		'titulo' => 'Preguntes de festes i esdeveniments',
		'lista'  => array(
			array( 'Quan l\'he de demanar per a un casament en dissabte?', 'Demana\'l quan vulguis i tria que arribi divendres (l\'últim moment per fer-ho: dijous a les 12:00). Per rebre\'l el mateix dissabte, tria dissabte: depèn de la zona i porta un suplement d\'11,74 € (IVA inclòs). Com més a prop de l\'esdeveniment, millor: es va sublimant amb les hores.' ),
			array( 'La boira mulla o taca?', 'No taca: és vapor d\'aigua condensat i CO₂. Amb molta boira seguida, el terra pot quedar humit: en una pista de ball, eixuga\'l entre tandes perquè ningú no rellisqui.' ),
			array( 'Salta l\'alarma d\'incendis?', 'Els detectors de fum òptics poden reaccionar a la boira densa. Pregunta-ho al local abans de l\'esdeveniment.' ),
		),
	),
	// Crida final: setmana de repartiment (Dl i Dg sense repartiment, Ds segons la zona) i botó de compra
	'final' => array(
		'titulo'  => 'Ja tens data?|<em>Demana\'l avui i tria el dia.</em>',
		'texto'   => 'En pagar tries el dia d\'entrega, amb fins a 90 dies d\'antelació. El millor: que arribi el mateix dia de la festa o el dia abans.',
		'boton'   => 'Demanar gel sec',
		'semana'  => 'Dies d\'entrega',
		'dias'    => array( array( 'Dl', 'Dilluns' ), array( 'Dt', 'Dimarts' ), array( 'Dc', 'Dimecres' ), array( 'Dj', 'Dijous' ), array( 'Dv', 'Divendres' ), array( 'Ds', 'Dissabte' ), array( 'Dg', 'Diumenge' ) ),
		'estados' => array( 'si' => 'Repartiment al matí', 'zona' => 'Segons la zona, amb suplement', 'no' => 'Sense repartiment' ),
		'nota'    => 'En pagar només hi apareixen els dies amb repartiment a la teva zona.',
	),
);
