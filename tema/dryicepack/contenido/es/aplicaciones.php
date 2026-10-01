<?php
/* Usos del hielo seco (índice) · castellano */
return array(
	'menu' => 'Usos del hielo seco',
	'seo'  => array(
		'titulo'      => 'Para qué sirve el hielo seco: usos por sector | DryIcePack',
		'descripcion' => 'Usos del hielo seco en hostelería, transporte en frío, industria, laboratorios y eventos. Qué formato elegir (3 o 16 mm) y cuánto pedir en cada caso.',
	),
	'migas'  => array( 'Inicio', 'Usos' ),
	'titulo' => 'Para qué sirve|el <em>hielo seco.</em>',
	'texto'  => 'Frío muy intenso sin agua, o niebla que cae y se queda en el suelo. Estos son los usos de quienes nos compran.',
	'sectores' => array(
		array( 'hosteleria', 'Hostelería', 'Humo al servir, cócteles, frío en barra y en buffet.', 'uso-hosteleria' ),
		array( 'transporte', 'Transporte en frío', 'Congelado y refrigerado en ruta, palés y última milla.', 'uso-transporte' ),
		array( 'industria', 'Industria', 'Enfriar piezas y baterías, ensayos y pedidos de volumen.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratorios', 'Muestras congeladas, envíos UN 1845, prácticas.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Fiestas y eventos', 'Niebla baja en bodas, escenarios y Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => '¿Qué formato|<em>para lo tuyo?</em>',
		'pregunta'=> 'Elige el uso',
		'usos'    => array(
			array( 'Niebla y efectos', '3mm', 'Los pellets pequeños sueltan niebla enseguida.', 'eventos' ),
			array( 'Cócteles y emplatado', '3mm', 'Niebla rápida en doble recipiente, nunca dentro de la copa.', 'hosteleria' ),
			array( 'Congelado en ruta', '16mm', 'Los nuggets duran más horas en la caja.', 'transporte' ),
			array( 'Muestras de laboratorio', '3mm', 'Los pellets rellenan huecos alrededor de los tubos; para viajes largos, 16 mm.', 'laboratorios' ),
			array( 'Enfriar piezas', '3mm', 'Más contacto con la superficie: enfría antes.', 'industria' ),
			array( 'Frío en barra o buffet', '16mm', 'Aguanta el servicio sin tener que reponer tanto.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => 'Pellets de 3 mm', '16mm' => 'Nuggets de 16 mm' ),
		'ver'      => 'Ver el uso',
	),
	'otros' => array(
		'titulo' => 'Otros usos',
		'lista'  => array(
			array( 'Vendimia', 'Enfriar la uva recién cortada antes de la prensa.', 'uso-vino' ),
			array( 'Fruta fresca', 'Mantener el frío en cajas camino de la central.', 'uso-agricultura' ),
			array( 'Docencia', 'Prácticas de sublimación y demostraciones en clase.', 'uso-docencia' ),
			array( 'Laboratorio', 'Conservación temporal de reactivos durante las sesiones.', 'uso-laboratorio-pinzas' ),
			array( 'Limpieza criogénica', 'La hace INDUNOVA, la empresa de la que forma parte DryIcePack.', 'uso-industria-pala' ),
		),
		'indunova' => 'Limpieza criogénica en indunova.es',
	),
);
