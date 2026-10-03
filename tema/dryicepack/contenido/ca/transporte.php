<?php
/* Gel sec per al transport en fred · català · vostè */
return array(
	'menu' => 'Gel sec per al transport',
	'seo'  => array(
		'titulo'      => 'Gel sec per al transport de congelats | DryIcePack',
		'descripcion' => 'Gel sec per al transport de congelats i refrigerats: a −78,5 °C i sense aigua a la caixa. Demani abans de les 12:00 i el tindrà demà al matí.',
	),
	'migas' => array( 'Inici', 'Usos', 'Transport en fred' ),
	'hero'  => array(
		'titulo' => 'Gel sec per al transport de congelats,|<em>demà al seu magatzem i sense aigua a la caixa.</em>',
		'texto'  => 'A −78,5 °C, passa de sòlid a gas sense fondre\'s i manté el fred durant la ruta. Demani abans de les 12:00 i demà al matí el té (de dimarts a dissabte), o triï el dia que vulgui. Amb compte d\'empresa, una factura al mes.',
		'boton'  => 'Obrir un compte',
		'datos'  => array( '−78,5 °C', '16 mm', 'Sense aigua', 'Factura mensual' ),
		'alt'    => 'Preparació d\'una comanda d\'aliments congelats en caixa isotèrmica amb gel sec',
	),
	'caja' => array(
		'titulo' => 'On va el gel sec? <em>A dalt.</em>',
		'texto'  => 'L\'aire fred pesa més i baixa. Amb el gel sec damunt de la càrrega, el fred cau sobre tot el producte.',
		'marcas' => array( 'Gel sec a sobre', 'Producte', 'Caixa aïllant sense tancament hermètic', 'El fred baixa' ),
	),
	'reglas' => array(
		'titulo' => 'Tres normes|<em>perquè arribi fred.</em>',
		'lista'  => array(
			array( 'Damunt de la càrrega', 'Mai només al fons. En palets, a la part de dalt o al buit superior.' ),
			array( 'Envàs que respiri', 'Caixa o cambra aïllada, però no hermètica: el CO₂ ha de sortir o la pressió la pot rebentar.' ),
			array( 'Quilos segons les hores', 'No és el mateix un repartiment urbà de 3 hores que un enviament de 24 h. Digui\'ns la ruta i li direm quants quilos.' ),
		),
	),
	'plan' => array(
		'titulo' => 'Les mateixes rutes cada setmana?|<em>Que no hi hagi de pensar.</em>',
		'texto'  => 'Marqui els dies de ruta i els quilos de cada sortida. Amb aquest consum li donem el seu preu i deixem la comanda programada: cada setmana, cada 15 dies o cada mes.',
		'dias'   => array( 'Dl', 'Dt', 'Dc', 'Dj', 'Dv' ),
		'kg'     => 'kg per ruta',
		'semana'      => 'Per setmana',
		'semana_calc' => 'Dies × kg per ruta',
		'mes'         => 'Al mes',
		'mes_calc'    => 'Per setmana × 4',
		'nota'        => 'Càlcul orientatiu. Al mes, comptant 4 setmanes.',
		'lunes'       => 'Els dilluns no hi ha repartiment: per a la ruta del dilluns, triï l\'entrega del dissabte (segons la zona, amb suplement) o reculli\'l el mateix dilluns a Mataró.',
		'boton'  => 'Demanar el preu',
	),
	'cliente' => array(
		'titulo'    => 'Qui el fa servir|<em>i per a què.</em>',
		'etiquetas' => array( 'Client', 'Ús' ),
		'id'        => 'italpizza',
		'detalle'   => 'Marca de pizza congelada',
		'uso'       => 'Transport en fred',
		'alt'       => 'Logotip d\'Italpizza',
	),
	'faq' => array(
		'titulo' => 'Preguntes de transport en fred',
		'lista'  => array(
			array( '3 mm o 16 mm per al transport?', '16 mm: més massa per peça, així que aguanta més hores en ruta. Els de 3 mm refreden més de pressa i serveixen per baixar la temperatura al començament.' ),
			array( 'El puc portar a la cabina de la furgoneta?', 'No. Sempre a la zona de càrrega i ventilant. A la cabina el CO₂ s\'acumula i desplaça l\'oxigen que respira el conductor.' ),
			array( 'Serveix per a producte refrigerat, no congelat?', 'Sí, amb compte: separi el gel sec del producte amb cartó o una safata perquè no el congeli. L\'ajudem a ajustar la quantitat.' ),
			array( 'Poden entregar cada setmana a la mateixa hora?', 'El mateix dia cada setmana, sí: amb una comanda programada arriba al matí sense que l\'hagi de demanar. L\'hora exacta depèn de la ruta de la missatgeria. Si necessita una hora fixa, reculli-la a Mataró a l\'hora que li confirmem.' ),
		),
	),
	// Crida final: full de ruta (de Mataró al seu magatzem) i botó al formulari de compte d'empresa, amb WhatsApp com a alternativa
	'final' => array(
		'titulo'     => 'Quants quilos per ruta?|<em>Digui\'ns la ruta i li donem preu.</em>',
		'texto'      => 'Amb els dies de sortida i els quilos de cada ruta calculem el seu descompte sobre tarifa. Li responem normalment el mateix dia feiner.',
		'boton'      => 'Demanar el preu',
		'wa'         => 'O escrigui\'ns per WhatsApp',
		'wa_mensaje' => 'Hola, necessitem gel sec per a les nostres rutes de repartiment. Ens doneu preu?',
		'ruta'       => array( array( 'Mataró', 'Comanda abans de les 12:00' ), array( 'El seu magatzem', 'Al matí, de dimarts a dissabte' ) ),
		'carga'      => '16 mm · −78,5 °C',
	),
);
