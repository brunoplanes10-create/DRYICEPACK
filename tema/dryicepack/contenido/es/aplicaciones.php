<?php
/* Usos del hielo seco (índice) · castellano */
return array(
	'menu' => 'Usos del hielo seco',
	'seo'  => array(
		'titulo'      => 'Para qué sirve el hielo seco: usos por sector | DryIcePack',
		'descripcion' => 'Para qué sirve el hielo seco en hostelería, transporte, industria, laboratorios y fiestas, y qué formato pedir: 3 mm o 16 mm. Busca tu uso y pídelo.',
	),
	'migas'  => array( 'Inicio', 'Usos' ),
	'titulo' => 'Para qué sirve|el hielo seco|<em>y qué formato pedir.</em>',
	'texto'  => 'Hace dos cosas: frío a −78,5 °C que no deja ni una gota de agua, y niebla que se queda pegada al suelo. Busca tu uso y te decimos qué formato pedir.',
	'sectores' => array(
		array( 'hosteleria', 'Hostelería', 'Humo al servir, cócteles y frío en barra que no deja agua.', 'uso-hosteleria' ),
		array( 'transporte', 'Transporte en frío', 'Que el congelado llegue congelado, sin cajas llenas de agua.', 'uso-transporte' ),
		array( 'industria', 'Industria', 'Enfriar piezas y baterías rápidamente, sin agua ni residuos.', 'uso-industria' ),
		array( 'laboratorios', 'Laboratorios', 'Muestras congeladas en el envío (UN 1845) y prácticas en clase.', 'uso-laboratorio-entrega' ),
		array( 'eventos', 'Fiestas y eventos', 'Niebla baja que se queda en el suelo: bodas, escenarios y Halloween.', 'uso-eventos' ),
	),
	'selector' => array(
		'titulo'  => '¿3 mm o 16 mm?|<em>Depende de para qué.</em>',
		'pregunta'=> '¿Para qué lo quieres?',
		'usos'    => array(
			array( 'Niebla y efectos', '3mm', 'Los pellets pequeños hacen niebla enseguida, y más densa.', 'eventos' ),
			array( 'Cócteles y emplatado', '3mm', 'Niebla rápida en doble recipiente, nunca dentro de la copa.', 'hosteleria' ),
			array( 'Congelado en ruta', '16mm', 'Los nuggets aguantan más horas en la caja, también en viajes largos.', 'transporte' ),
			array( 'Muestras de laboratorio', '3mm', 'Los pellets rellenan huecos alrededor de los tubos; para viajes largos, 16 mm.', 'laboratorios' ),
			array( 'Enfriar piezas', '3mm', 'Más contacto con la superficie: enfría antes.', 'industria' ),
			array( 'Frío en barra o buffet', '16mm', 'Aguanta el servicio sin tener que reponer tanto.', 'hosteleria' ),
		),
		'formatos' => array( '3mm' => 'Pellets de 3 mm', '16mm' => 'Nuggets de 16 mm' ),
		'ver'      => 'Ver cómo se usa',
	),
	'otros' => array(
		'titulo' => 'Otros usos del hielo seco',
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
