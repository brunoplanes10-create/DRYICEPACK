<?php
/* Empreses (compte d'empresa i pressupost per volum) · català · tracte de vostè */
return array(
	'menu' => 'Empreses',
	'seo'  => array(
		'titulo'      => 'Proveïdor de gel sec per a empreses | DryIcePack',
		'descripcion' => 'Gel sec per al seu negoci: preu propi, comanda per WhatsApp o al web i una sola factura al mes. Entrega l\'endemà al matí. Demani el seu preu.',
	),
	'migas' => array( 'Inici', 'Empreses' ),
	'hero'  => array(
		'titulo' => 'Proveïdor de gel sec|per a qui el fa servir|<em>cada setmana.</em>',
		'texto'  => 'Preu propi, comanda en dos clics o per WhatsApp i una sola factura al mes. Sense permanència.',
		'boton'  => 'Obrir compte',
		'tel'    => 'o truqui a vendes:',
		'alt'    => 'Operari obrint una caixa de gel sec al magatzem',
	),
	'pedir' => array(
		'titulo'   => 'Demani com|<em>li vagi millor.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'Un missatge i fet. Li confirmem el dia d\'entrega pel mateix xat.' ),
			array( 'Al web', 'Entri al seu compte i repeteixi qualsevol comanda anterior amb un clic.' ),
			array( 'Correu electrònic', 'Per a comandes programades o amb diverses adreces d\'entrega.' ),
		),
		'chat'     => array( 'Hola, ens envieu 10 kg de 16 mm per a dimarts?', 'Fet. Surt dilluns i arriba dimarts al matí. Va a la factura del mes.' ),
		'repetir'  => array( 'titulo' => 'Les seves comandes', 'filas' => array( array( '10 kg · 16 mm', 'Repetir' ), array( '20 kg · 3 mm', 'Repetir' ), array( '10 kg · 16 mm', 'Repetir' ) ) ),
		'email'    => array( 'Per a: info@dryicepack.es', 'Assumpte: Comanda setmanal', 'Bon dia: com cada dilluns, 15 kg de 16 mm a la nau de Rubí. Gràcies.' ),
	),
	'ventajas' => array(
		'titulo' => 'El que canvia|amb un <em>compte.</em>',
		'lista'  => array(
			array( 'Preu propi', 'Segons el volum i la freqüència. S\'acorda una vegada i no cal negociar-lo a cada comanda.' ),
			array( 'Una factura al mes', 'Totes les comandes del mes, en una factura completa. Pagament per transferència o domiciliació.' ),
			array( 'Dades desades', 'Raó social, NIF i adreça d\'entrega: no s\'han de tornar a escriure.' ),
			array( 'Recordatori, si el vol', 'Si demana amb un ritme fix, l\'avisem quan toca. Es desactiva amb un clic.' ),
			array( 'Sense permanència', 'S\'atura o s\'ajusta quan canviï la seva activitat.' ),
		),
	),
	'volumen' => array(
		'titulo'  => 'Quant|<em>en necessita?</em>',
		'rango'   => 'Quilos per comanda',
		'cajas'   => '%1$s caixes · %2$s kg',
		'caja'    => '1 caixa · %s kg',
		'notas'   => array(
			'web'   => 'Fins a 250 kg per comanda, al web o amb el seu compte.',
			'bcn'   => 'Més de 150 kg a la província de Barcelona: entrega a les seves instal·lacions, consulti el termini.',
			'fuera' => 'Molt pes fora de la província de Barcelona: ho estudiem cas per cas.',
		),
	),
	'casos' => array(
		'titulo' => 'Per a què el fan servir|els nostres <em>clients.</em>',
		'lista'  => array(
			array( 'Automoció de competició', 'Refredar bateries entre sessions de proves.', 'uso-industria' ),
			array( 'Logística de palets', 'Mantenir freda la càrrega durant el viatge.', 'cajas-furgoneta' ),
			array( 'Obrador de pizza', 'Transport de producte en fred.', 'uso-transporte' ),
			array( 'Laboratori de llavors', 'Enviament de mostres congelades.', 'uso-laboratorio-entrega' ),
			array( 'Cerveseria', 'Fum a la sala i còctels cada cap de setmana.', 'uso-hosteleria' ),
			array( 'Restaurants de sushi', 'Fum en emplatar, a cada servei.', 'nuggets-ia' ),
		),
		'nota'   => 'Casos reals de clients de DryIcePack. No publiquem els seus noms sense permís.',
	),
	'alta' => array(
		'titulo'  => 'Obri el seu compte|en <em>un minut.</em>',
		'texto'   => 'Ompli la frase i li responem amb el seu preu, normalment el mateix dia feiner.',
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
		'boton'        => 'Demanar el meu preu',
		'enviando'     => 'Enviant…',
	),
	'faq' => array(
		'titulo' => 'Preguntes d\'empresa',
		'lista'  => array(
			array( 'Hi ha comanda mínima?', 'Al web, des de 3 kg. Amb compte d\'empresa l\'ajustem al seu consum.' ),
			array( 'Com es paga?', 'Amb una factura al mes, per transferència o domiciliació, segons el que acordem en obrir el compte.' ),
			array( 'Puc canviar quilos o freqüència?', 'Sí. Avisi\'ns amb 24 a 48 hores laborables per organitzar la preparació i la ruta.' ),
			array( 'Emeten factura completa?', 'Sempre, amb les seves dades fiscals. A cada comanda i en el resum mensual.' ),
			array( 'Arriben fora de Catalunya?', 'A tota la península per missatgeria, amb entrega l\'endemà al matí. Les comandes de molt pes fora de la província de Barcelona les estudiem cas per cas.' ),
			array( 'Tenen fitxa de dades de seguretat?', 'Sí. La pot descarregar a la pàgina de seguretat, juntament amb les normes de manipulació.' ),
		),
	),
	'final' => array( 'titulo' => 'Prefereix parlar-ne?', 'texto' => 'Vendes: de dilluns a divendres, de 9:00 a 18:00.' ),
);
