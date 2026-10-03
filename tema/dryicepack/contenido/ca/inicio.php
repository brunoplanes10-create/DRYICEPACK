<?php
/* Portada · català. Les línies dels títols se separen amb "|". */
return array(
	'menu' => 'Inici',
	'seo'  => array(
		'titulo'      => 'Gel sec: compra\'l en línia i rep-lo demà | DryIcePack',
		'descripcion' => 'Gel sec en pèl·lets de 3 mm o nuggets de 16 mm, des de 3 kg. Demana abans de les 12:00 i demà al matí el tens. O recull-lo gratis a la nau de Mataró.',
	),
	'hero' => array(
		'titulo'    => 'Gel sec a la teva porta,|<em>demà al matí.</em>',
		'texto'     => 'Demana abans de les 12:00 i demà al matí el tens (de dimarts a dissabte), o tria el dia que vulguis. En caixa aïllant, des de 3 kg i sense contractes. A prop de Mataró? Recull-lo gratis.',
		'boton'     => 'Fer la comanda',
		'o_llama'   => 'o truca al',
		'alt'       => 'Caixa de gel sec en nuggets de 16 mm, oberta i deixant anar vapor fred',
		'corte'     => array( 'antes' => 'Demana abans de les 12:00 i el tens el %s al matí', 'quedan' => 'Falten %s', 'despues' => 'Si demanes ara, el tens el %s al matí' ),
	),
	'cinta' => array( '−78,5 °C', 'Pèl·lets de 3 mm', 'Nuggets de 16 mm', 'Caixa EPS de 40 mm', 'Hora límit: 12:00', 'Entrega al matí', 'Recollida a Mataró', 'Factura mensual per a empreses', 'De 3 a 250 kg per comanda' ),
	'elegir' => array(
		'titulo'   => 'Saps el que pagues|<em>abans de pagar.</em>',
		'formatos' => array(
			'3mm'  => array( 'nombre' => 'Pèl·lets de 3 mm', 'texto' => 'Refreda més de pressa i fa la boira més densa.', 'para' => 'Còctels, boira, processos', 'foto' => 'cenital-3mm', 'alt' => 'Pèl·lets de gel sec de 3 mm vistos des de dalt' ),
			'16mm' => array( 'nombre' => 'Nuggets de 16 mm', 'texto' => 'Aguanta més hores a la caixa. Per a transport i mostres.', 'para' => 'Transport, mostres, cadena de fred', 'foto' => 'cenital-16mm', 'alt' => 'Nuggets de gel sec de 16 mm vistos des de dalt' ),
		),
		'cuanto'    => 'Quants quilos?',
		'envio'     => 'Enviament',
		'total'     => 'Total amb enviament',
		'recogida'  => 'Si el reculls a Mataró: %s',
		'nota'      => 'IVA inclòs. El total ja inclou l\'enviament: sense sorpreses a l\'últim pas.',
		'boton'     => 'Demanar %s kg',
		'mas'       => 'Més de 20 kg? Suma caixes, fins a 250 kg per comanda. Si en són més de 150 i ets fora de la província de Barcelona, escriu-nos.',
	),
	'llega' => array(
		'titulo' => 'Demanes abans de les 12:00.|<em>Demà al matí el tens.</em>',
		'pasos'  => array(
			array( '12:00', 'Fas la comanda', 'Abans de les 12:00, de dilluns a divendres, excepte festius. Surt de Mataró aquell mateix dia.' ),
			array( 'Avui', 'El preparem', 'En caixa EPS de 40 mm, tancada però no hermètica, i marxa amb la missatgeria.' ),
			array( 'Demà', 'El tens al matí', 'De dimarts a divendres. El dissabte, segons la zona i amb un suplement d\'11,74 €.' ),
		),
		'nota'   => 'Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla. No entreguem ni diumenge ni dilluns.',
		'enlace' => 'Enviaments i terminis',
		'mapa'   => 'Mapa de la península amb rutes de repartiment des de Mataró',
	),
	'usos' => array(
		'titulo' => 'Per a què el necessites?|<em>Tria el teu cas.</em>',
		'lista'  => array(
			array( 'hosteleria', 'Hostaleria', 'Còctels i plats que surten a la sala amb fum.', 'uso-hosteleria' ),
			array( 'transporte', 'Transport en fred', 'Congelat i refrigerat que arriba igual que va sortir.', 'uso-transporte' ),
			array( 'industria', 'Indústria', 'Refredar peces i bateries, assajos i comandes de fins a 250 kg.', 'uso-industria' ),
			array( 'laboratorios', 'Laboratoris', 'Mostres a −78,5 °C i enviaments UN 1845.', 'uso-laboratorio' ),
			array( 'eventos', 'Festes i esdeveniments', 'Boira arran de terra en casaments, escenaris i Halloween.', 'uso-eventos' ),
		),
		'todos'  => 'Tots els usos',
	),
	'clientes' => array(
		'titulo' => 'No ho diem nosaltres.|<em>Ho fan servir ells.</em>',
		'texto'  => 'Quatre clients que ens deixen dir-ho, i per a què el fan servir.',
		'lista'  => array(
			array( 'id' => 'italpizza', 'uso' => 'Transport en fred', 'alt' => 'Logotip d\'Italpizza' ),
			array( 'id' => 'sushitok', 'uso' => 'Fum en emplatar', 'alt' => 'Logotip de Sushitok' ),
			array( 'id' => 'fito', 'uso' => 'Transport de mostres', 'alt' => 'Logotip de Semillas Fitó' ),
			array( 'id' => 'nissan', 'uso' => 'Refredar bateries', 'alt' => 'Logotip de Nissan Formula E Team' ),
		),
	),
	'ficha' => array(
		'titulo' => 'No és gel.|És CO<sub>2</sub> a −78,5 °C.',
		'cotas'  => array(
			array( 'Sublima', 'Passa de sòlid a gas sense fondre\'s. Ni bassals ni caixes amb aigua.' ),
			array( '60 °C més fred', 'Que un congelador domèstic, que treballa a −18 °C.' ),
			array( '3 mm o 16 mm', 'Els petits refreden abans. Els grans duren més.' ),
			array( 'Caixa EPS de 40 mm', 'Aïlla i deixa sortir el gas: mai no va tancada hermèticament.' ),
		),
		'enlace' => 'Què és el gel sec',
		'alt'    => 'Nuggets de gel sec a la seva caixa, vistos des de dalt, amb vapor',
	),
	'empresas' => array(
		'titulo'  => 'Cada setmana la mateixa comanda?|<em>Que no hi hagis de pensar.</em>',
		'texto'   => 'Demanes per WhatsApp o en dos clics. Contracte de subministrament, descompte sobre tarifa segons el volum i una factura al mes, a 30 dies.',
		'tarjeta' => array( 'titulo' => 'Compte d\'empresa', 'lineas' => array( 'Descompte sobre tarifa', 'Factura mensual a 30 dies', 'Comanda per WhatsApp o web', 'Dades fiscals desades' ), 'pie' => 'DryIcePack · Mataró' ),
		'enlace'  => 'Obrir un compte',
	),
	'recogida' => array(
		'titulo'  => 'Ets a prop de Mataró?|Recull-lo <em>gratis.</em>',
		'texto'   => 'Sense esperar la missatgeria. Tries el dia en demanar, et confirmem l\'hora i te\'l tenim preparat a la nau. Gratis de dilluns a divendres; el dissabte, amb un suplement d\'11,74 €. Pagues en efectiu quan el reculls.',
		'ficha'   => 'Nau DryIcePack',
		'como'    => 'Com arribar-hi',
		'alt'     => 'Càrrega d\'una caixa de gel sec a la furgoneta de repartiment',
		'cerca'   => array( 'Mataró', 'Maresme', 'Barcelona', 'Vallès' ),
	),
	'seguridad' => array(
		'titulo' => 'Segur,|si saps <em>com.</em>',
		'texto'  => 'Són sis normes. Llegeix-les abans d\'obrir la caixa.',
	),
	'faq' => array(
		'titulo' => 'El que més|ens <em>pregunten.</em>',
		'lista'  => array(
			array( 'Quant gel sec necessito?', 'Depèn de l\'ús i de quantes hores el necessitis. Per fer boira, com a referència: a casa 3 kg, en una festa gran 10 kg i en un bar o un esdeveniment de 15 a 20 kg. Per a transport o mostres, digues-nos quantes hores i quin volum, i t\'ho calculem.' ),
			array( 'Quan m\'arriba?', 'Si demanes abans de les 12:00 de dilluns a divendres, surt aquell mateix dia i el tens l\'endemà al matí. No repartim ni diumenge ni dilluns. El dissabte, segons la zona i amb un suplement d\'11,74 € (IVA inclòs).' ),
			array( 'Puc demanar per a un altre dia?', 'Sí: en pagar tries qualsevol dia dels pròxims 90 amb repartiment a la teva zona, de dimarts a dissabte (el dissabte, segons la zona i amb suplement). Demana\'l per al dia que el faràs servir: el gel sec es va sublimant amb les hores.' ),
			array( 'El puc recollir en persona?', 'Sí, a la nostra nau de Mataró (Camí Ca La Madrona 19 D), de dilluns a dissabte. De dilluns a divendres és gratis i el dissabte té un suplement d\'11,74 €. Et confirmem l\'hora abans i pagues en efectiu quan el reculls.' ),
			array( 'Quina diferència hi ha entre 3 mm i 16 mm?', 'Els pèl·lets de 3 mm tenen més superfície: refreden més de pressa i fan més boira. Els nuggets de 16 mm tenen més massa per peça i duren més; per això es fan servir en transport i mostres.' ),
			array( 'Feu factura a particulars i a empreses?', 'Sí. Si ets particular i la comanda és de fins a 400 € (IVA inclòs), la factura simplificada t\'arriba per correu electrònic des d\'info@dryicepack.es. Si compres per a una empresa o com a autònom, marca «Compro per a una empresa o autònom» en pagar i escriu la raó social i el NIF: reps la factura completa. Si ja has pagat sense marcar-ho, t\'enviem un enllaç per demanar-la. Els comptes d\'empresa reben una sola factura al mes, a 30 dies.' ),
			array( 'Envieu a les Balears o a les Canàries?', 'No. Només enviem a la península: no a les Balears, les Canàries, Ceuta ni Melilla.' ),
		),
	),
	'final' => array(
		'titulo' => 'El necessites demà?|<em>Demana\'l abans de les 12:00.</em>',
		'boton'  => 'Fer la comanda',
		'wa'     => 'Escriu-nos per WhatsApp',
	),
);
