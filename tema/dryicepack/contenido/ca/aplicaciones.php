<?php
/* Usos del gel sec (índex) · català */
return array(
	'menu' => 'Usos del gel sec',
	'seo'  => array(
		'titulo'      => 'Per a què serveix el gel sec: usos per sector | DryIcePack',
		'descripcion' => 'Usos del gel sec en hostaleria, transport en fred, indústria, laboratoris i esdeveniments. Mira quin format triar i quant en cal demanar en cada cas.',
	),
	'migas'  => array( 'Inici', 'Usos' ),
	'titulo' => 'Per a què serveix|el <em>gel sec.</em>',
	'texto'  => 'Fred molt intens sense aigua, o boira que cau i es queda a terra. Aquests són els usos dels qui ens compren.',
	'sectores' => array(
		array( 'hosteleria', 'Hostaleria', 'Fum en servir, còctels, fred a la barra i al bufet.', 'uso-hosteleria' ),
		array( 'transporte', 'Transport en fred', 'Congelat i refrigerat en ruta, palets i última milla.', 'uso-transporte' ),
		array( 'industria', 'Indústria', 'Refredar peces i bateries, assajos i comandes de volum.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratoris', 'Mostres congelades, enviaments UN 1845, pràctiques.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Festes i esdeveniments', 'Boira baixa en casaments, escenaris i Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => 'Quin format|<em>per al teu cas?</em>',
		'pregunta'=> 'Tria l\'ús',
		'usos'    => array(
			array( 'Boira i efectes', '3mm', 'Els pèl·lets petits fan boira de seguida.', 'eventos' ),
			array( 'Còctels i emplatat', '3mm', 'Boira ràpida amb doble recipient, mai dins la copa.', 'hosteleria' ),
			array( 'Congelat en ruta', '16mm', 'Els nuggets duren més hores a la caixa.', 'transporte' ),
			array( 'Mostres de laboratori', '3mm', 'Els pèl·lets omplen els buits al voltant dels tubs; per a viatges llargs, 16 mm.', 'laboratorios' ),
			array( 'Refredar peces', '3mm', 'Més contacte amb la superfície: refreda abans.', 'industria' ),
			array( 'Fred a la barra o al bufet', '16mm', 'Aguanta el servei sense haver de reposar tant.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => 'Pèl·lets de 3 mm', '16mm' => 'Nuggets de 16 mm' ),
		'ver'      => 'Veure l\'ús',
	),
	'otros' => array(
		'titulo' => 'Altres usos',
		'lista'  => array(
			array( 'Verema', 'Refredar el raïm acabat de collir abans de premsar-lo.', 'uso-vino' ),
			array( 'Fruita fresca', 'Mantenir el fred a les caixes camí de la central.', 'uso-agricultura' ),
			array( 'Docència', 'Pràctiques de sublimació i demostracions a classe.', 'uso-docencia' ),
			array( 'Laboratori', 'Conservació temporal de reactius durant les sessions.', 'uso-laboratorio-pinzas' ),
			array( 'Neteja criogènica', 'La fa INDUNOVA, l\'empresa de la qual forma part DryIcePack.', 'uso-industria-pala' ),
		),
		'indunova' => 'Neteja criogènica a indunova.es',
	),
);
