<?php
/* Empreses (compte d'empresa i pressupost per volum) · català · tracte de vostè */
return array(
	'menu' => 'Empreses',
	'seo'  => array(
		'titulo'      => 'Proveïdor de gel sec per a empreses | DryIcePack',
		'descripcion' => 'Proveïdor de gel sec per al seu negoci: contracte de subministrament, descompte sobre tarifa segons el volum i una factura al mes, a 30 dies. Demani preu.',
	),
	'migas' => array( 'Inici', 'Empreses' ),
	'hero'  => array(
		'titulo' => 'Proveïdor de gel sec:|<em>cada setmana,|sense haver-lo de demanar.</em>',
		'texto'  => 'Ens diu quants quilos i cada quant, i li arriba al matí. Contracte de subministrament, descompte sobre tarifa segons el volum i la freqüència, i una factura al mes, a 30 dies.',
		'boton'  => 'Obrir un compte',
		'tel'    => 'o truqui a vendes:',
		'alt'    => 'Operari obrint una caixa de gel sec al magatzem',
	),
	'pedir' => array(
		'titulo'   => 'Com es demana?|<em>Com li vagi millor.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'Un missatge amb els quilos i el dia, i fet. Li confirmem l\'entrega pel mateix xat.' ),
			array( 'Al web', 'Entri al seu compte i repeteixi qualsevol comanda amb un clic, sense tornar a omplir res.' ),
			array( 'Correu electrònic', 'Per programar una comanda fixa, que s\'escriu una sola vegada, o per a entregues a diverses adreces.' ),
		),
		'chat'     => array( 'Hola, ens envieu 10 kg de 16 mm per a dimarts?', 'Fet. Surt dilluns i arriba dimarts al matí. Va a la factura del mes.' ),
		'repetir'  => array( 'titulo' => 'Les seves comandes', 'filas' => array( array( '10 kg · 16 mm', 'Repetir' ), array( '20 kg · 3 mm', 'Repetir' ), array( '10 kg · 16 mm', 'Repetir' ) ) ),
		'email'    => array( 'Per a: info@dryicepack.es', 'Assumpte: Comanda programada', 'Bon dia: a partir d\'ara, 15 kg de 16 mm cada dimarts a la nau de Rubí. Gràcies.' ),
	),
	'ventajas' => array(
		'titulo' => 'Què hi guanya|amb un <em>compte d\'empresa?</em>',
		'lista'  => array(
			array( 'Descompte sobre tarifa', 'Segons el volum i la freqüència. S\'acorda en obrir el compte i s\'aplica a cada comanda.' ),
			array( 'Una factura al mes', 'Totes les comandes del mes en una sola factura completa, a 30 dies, per domiciliació SEPA o transferència. Menys papers per a vostè i per a la seva gestoria.' ),
			array( 'Dades desades', 'Raó social, NIF i adreces d\'entrega es desen una vegada. No els ha de tornar a escriure.' ),
			array( 'Comanda programada o avís', 'Si sempre demana el mateix, programem la comanda i li arriba sola. Si prefereix decidir cada vegada, l\'avisem quan toca.' ),
			array( 'Contracte de subministrament', 'Quilos, freqüència, descompte i forma de pagament, per escrit. Canvia la temporada? Ho ajustem amb vostè.' ),
		),
	),
	'volumen' => array(
		'titulo'  => 'Quant|<em>en necessita?</em>',
		'rango'   => 'Quilos per comanda',
		'cajas'   => '%1$s caixes · %2$s kg',
		'caja'    => '1 caixa · %s kg',
		'notas'   => array(
			'web'   => 'Fins a 150 kg: al web, amb el seu compte o per WhatsApp, amb enviament a tota l\'Espanya peninsular.',
			'bcn'   => 'Més de 150 kg i fins a 250 kg: a la província de Barcelona li ho portem a les seves instal·lacions; fora, l\'enviament es prepara a mida i surt més car. Escrigui\'ns i li donem preu i dia.',
			'fuera' => 'Més de 250 kg per comanda: escrigui\'ns i li donem preu i dia.',
		),
	),
	'casos' => array(
		'titulo' => 'Qui ja el fa servir?|<em>De la Fórmula E al sushi.</em>',
		'lista'  => array(
			// Amb client: només fotos reals del producte. Sense client: l'ús, sense més dades.
			array( 'Automoció de competició', 'Refredar bateries ràpidament.', 'caja-16mm-lateral', 'nissan' ),
			array( 'Transport de palets', '', 'cajas-furgoneta' ),
			array( 'Pizza congelada', 'Transport en fred.', 'nuggets-azul', 'italpizza' ),
			array( 'Empresa de llavors', 'Transport de mostres.', 'cenital-caja-nuggets', 'fito' ),
			array( 'Fum i còctels', '', 'uso-hosteleria' ),
			array( 'Restaurants de sushi', 'Efecte de fum en emplatar.', 'anadiendo-hielo', 'sushitok' ),
		),
		'logos'  => array(
			'nissan'    => 'Logotip de Nissan Formula E Team',
			'italpizza' => 'Logotip d\'Italpizza',
			'fito'      => 'Logotip de Semillas Fitó',
			'sushitok'  => 'Logotip de Sushitok',
		),
		'nota'   => 'Clients reals de DryIcePack, publicats amb el seu permís.',
	),
	'alta' => array(
		'titulo'  => 'Obri el seu compte|<em>amb una sola frase.</em>',
		'texto'   => 'Completi-la i li responem amb el seu preu, normalment el mateix dia feiner.',
		'frase'   => array(
			'Som', 'empresa', 'la seva empresa',
			'i fem servir gel sec per a', 'uso', 'transport, fum…',
			'. Necessitem uns', 'kg', '10',
			'kg', 'frecuencia', array( 'cada setmana', 'cada 15 dies', 'cada mes', 'una sola vegada' ),
			', amb entrega al codi postal', 'cp', '08302',
			'. Podeu trucar a', 'nombre', 'el seu nom',
			'al', 'telefono', '600 000 000',
			'o escriure a', 'email', 'correu@empresa.cat',
		),
		'nif'          => 'NIF/CIF (opcional)',
		'recordatorio' => 'Vull que m\'avisin quan em toqui repetir la comanda.',
		'boton'        => 'Demanar el preu',
		'enviando'     => 'Enviant…',
	),
	'faq' => array(
		'titulo' => 'El que pregunten les empreses',
		'lista'  => array(
			array( 'Hi ha comanda mínima?', 'Al web, des de 3 kg. Amb compte d\'empresa, l\'ajustem al que vostè consumeix.' ),
			array( 'Com es paga?', 'Amb una sola factura al mes, a 30 dies, per domiciliació SEPA sempre que es pugui, o per transferència.' ),
			array( 'Cal signar un contracte?', 'Per al compte d\'empresa, sí: un contracte de subministrament de gel sec amb els quilos, la freqüència, el descompte sobre tarifa i la forma de pagament. Per comprar al web no cal: demani i pagui al moment, com qualsevol client.' ),
			array( 'Quin preu tindré?', 'Un descompte sobre la tarifa del web, segons el seu volum i la seva freqüència. S\'acorda en obrir el compte. Completi la frase de més amunt i li responem, normalment el mateix dia feiner.' ),
			array( 'Puc canviar quilos o freqüència?', 'Sí, quan ho necessiti. Avisi\'ns amb antelació per WhatsApp, telèfon o correu electrònic i ho ajustem.' ),
			array( 'Emeten factura completa?', 'Sí, sempre, amb les seves dades fiscals. Amb compte d\'empresa rep una sola factura al mes amb totes les comandes.' ),
			array( 'Arriben fora de Catalunya?', 'Sí, a tota la península per missatgeria. Si demana abans de les 12:00, arriba l\'endemà al matí, de dimarts a dissabte (el dissabte, segons la zona i amb un suplement d\'11,74 €, IVA inclòs). Més de 150 kg fora de la província de Barcelona: escrigui\'ns i li preparem l\'enviament a mida.' ),
			array( 'Tenen fitxa de dades de seguretat?', 'Sí. La pot descarregar a la pàgina de seguretat, juntament amb les normes de manipulació per al seu equip.' ),
		),
	),
	'final' => array( 'titulo' => 'Prefereix parlar-ne amb una persona?', 'texto' => 'Vendes, de dilluns a divendres de 9:00 a 18:00. Fora d\'aquest horari, escrigui\'ns per WhatsApp al 686 980 471.' ),
);
