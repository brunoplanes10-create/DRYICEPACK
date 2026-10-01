<?php
/* Zones d'entrega (índex i plantilla de zona) · català. Marcadors: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zones d\'entrega',
	'seo'  => array(
		'titulo'      => 'Gel sec a prop de Barcelona: zones d\'entrega | DryIcePack',
		'descripcion' => 'Gel sec a Barcelona, al Maresme, al Vallès i al Baix Llobregat: entrega l\'endemà al matí o recollida gratuïta a la nostra nau de Mataró. Des de 3 kg.',
	),
	'mapa'   => 'Mapa amb la nau de Mataró al centre, anells cada 10 km i les zones d\'entrega',
	'indice' => array(
		'migas'   => array( 'Inici', 'Zones d\'entrega' ),
		'titulo'  => 'Gel sec|<em>a prop teu.</em>',
		'texto'   => 'Enviem a tota la península. A prop de Barcelona, a més, el pots recollir a la nostra nau de Mataró. Aquestes són les zones on més ens en demanen.',
		'grupos'  => 'Per comarca',
		'resto'   => 'I a tota la península, amb entrega l\'endemà al matí.',
		'envios'  => 'Enviaments i terminis',
	),
	'zona' => array(
		'seo_titulo'      => 'Gel sec a {nombre}: entrega demà | DryIcePack',
		'seo_descripcion' => 'Gel sec a {nombre}: demana abans de les 12:00 i arriba l\'endemà al matí, o recull-lo gratis a la nostra nau de Mataró, a {km} km. Comandes des de 3 kg.',
		'seo_descripcion_corta' => 'Gel sec a {nombre}: demana abans de les 12:00 i arriba l\'endemà al matí, o recull-lo gratis a la nau de Mataró, a {km} km. Des de 3 kg.',
		'migas'   => array( 'Inici', 'Zones', '{Nombre}' ),
		'titulo'  => 'Gel sec a|<em>{nombre}.</em>',
		'texto'   => 'Demana abans de les 12:00 i te\'l portem a {nombre} l\'endemà al matí. O recull-lo a la nostra nau de Mataró, a {km} km.',
		'boton'   => 'Comprar gel sec',
		'datos'   => array(
			array( 'Distància a la nau', '{km} km' ),
			array( 'En cotxe, sense trànsit', 'uns {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'Entrega a|<em>{nombre}.</em>',
			'filas'  => array(
				array( 'Comanda abans de les 12:00', 'Arriba l\'endemà al matí, de dimarts a divendres.' ),
				array( 'Dissabte', 'Segons el codi postal, amb un suplement d\'11,74 €.' ),
				array( 'Recollida', 'Gratis a Mataró, de dilluns a divendres de 9:00 a 18:00, a {km} km de {nombre}.' ),
				array( 'Enviament de 10 kg', '17,32 € amb IVA, el mateix preu que a tota la península.' ),
				array( 'Empreses del {comarca}', 'Compte amb preu propi i factura mensual.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'Per a què el demanen al {comarca}',
			'lista'  => array( array( 'hosteleria', 'Hostaleria' ), array( 'transporte', 'Transport en fred' ), array( 'industria', 'Indústria' ), array( 'laboratorios', 'Laboratoris' ), array( 'eventos', 'Festes i esdeveniments' ) ),
		),
		'faq'     => array(
			array( 'Quant triga a arribar el gel sec a {nombre}?', 'Si demanes abans de les 12:00 de dilluns a divendres, arriba a {nombre} l\'endemà al matí. No repartim ni diumenge ni dilluns.' ),
			array( 'El puc recollir si soc a {nombre}?', 'Sí. La nostra nau és a Mataró, a {km} km (uns {min} min en cotxe sense trànsit). La recollida és gratuïta i es paga en efectiu.' ),
			array( 'Feu entregues en dissabte a {nombre}?', 'Segons el codi postal. Té un suplement d\'11,74 € (IVA inclòs) i el tries en pagar.' ),
		),
		'cerca'   => 'Zones properes',
		'fuente'  => 'Distàncies i temps: OpenStreetMap i OSRM, sense trànsit.',
	),
);
