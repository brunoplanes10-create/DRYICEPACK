<?php
/* Usos del gel sec (índex) · català */
return array(
	'menu' => 'Usos del gel sec',
	'seo'  => array(
		'titulo'      => 'Per a què serveix el gel sec: usos per sector | DryIcePack',
		'descripcion' => 'Per a què serveix el gel sec en hostaleria, transport, indústria, laboratoris i festes, i quin format demanar: 3 mm o 16 mm. Busca el teu ús i demana\'l.',
	),
	'migas'  => array( 'Inici', 'Usos' ),
	'titulo' => 'Per a què serveix|el gel sec|<em>i quin format demanar.</em>',
	'texto'  => 'Fa dues coses: fred a −78,5 °C que no deixa ni una gota d\'aigua, i boira que es queda enganxada a terra. Busca el teu ús i et diem quin format demanar.',
	'sectores' => array(
		array( 'hosteleria', 'Hostaleria', 'Fum en servir, còctels i fred a la barra que no deixa aigua.', 'uso-hosteleria' ),
		array( 'transporte', 'Transport en fred', 'Que el congelat arribi congelat, sense caixes plenes d\'aigua.', 'uso-transporte' ),
		array( 'industria', 'Indústria', 'Refredar peces i bateries ràpidament, sense aigua ni residus.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratoris', 'Mostres congelades durant l\'enviament (UN 1845) i pràctiques a classe.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Festes i esdeveniments', 'Boira baixa que es queda a terra: casaments, escenaris i Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => '3 mm o 16 mm?|<em>Depèn de l\'ús.</em>',
		'pregunta'=> 'Per a què el vols?',
		'usos'    => array(
			array( 'Boira i efectes', '3mm', 'Els pèl·lets petits fan boira de seguida, i més densa.', 'eventos' ),
			array( 'Còctels i emplatat', '3mm', 'Boira ràpida amb doble recipient, mai dins la copa.', 'hosteleria' ),
			array( 'Congelat en ruta', '16mm', 'Els nuggets aguanten més hores a la caixa, també en viatges llargs.', 'transporte' ),
			array( 'Mostres de laboratori', '3mm', 'Els pèl·lets omplen els buits al voltant dels tubs; per a viatges llargs, 16 mm.', 'laboratorios' ),
			array( 'Refredar peces', '3mm', 'Més contacte amb la superfície: refreda abans.', 'industria' ),
			array( 'Fred a la barra o al bufet', '16mm', 'Aguanta el servei sense haver de reposar tant.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => 'Pèl·lets de 3 mm', '16mm' => 'Nuggets de 16 mm' ),
		'ver'      => 'Veure com es fa servir',
	),
	'otros' => array(
		'titulo' => 'Altres usos del gel sec',
		'lista'  => array(
			array( 'Verema', 'Refredar el raïm acabat de collir abans de premsar-lo.', 'uso-vino' ),
			array( 'Fruita fresca', 'Mantenir el fred a les caixes en el trajecte fins a la central.', 'uso-agricultura' ),
			array( 'Docència', 'Pràctiques de sublimació i demostracions a classe.', 'uso-docencia' ),
			array( 'Laboratori', 'Conservació temporal de reactius durant les sessions.', 'uso-laboratorio-pinzas' ),
			array( 'Neteja criogènica', 'La fa INDUNOVA, l\'empresa de la qual forma part DryIcePack.', 'uso-industria-pala' ),
		),
		'indunova' => 'Neteja criogènica a indunova.es',
	),
);
