<?php
/* Zonas de entrega (índice y plantilla de zona) · castellano. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zonas de entrega',
	'seo'  => array(
		'titulo'      => 'Hielo seco cerca de Barcelona: zonas de entrega | DryIcePack',
		'descripcion' => 'Hielo seco en Barcelona, el Maresme, el Vallès y el Baix Llobregat: entrega al día siguiente por la mañana o recogida gratis en nuestra nave de Mataró.',
	),
	'mapa'   => 'Mapa con la nave de Mataró en el centro, anillos cada 10 km y las zonas de entrega',
	'indice' => array(
		'migas'   => array( 'Inicio', 'Zonas de entrega' ),
		'titulo'  => 'Hielo seco|<em>cerca de ti.</em>',
		'texto'   => 'Enviamos a toda la península. Cerca de Barcelona, además, puedes recogerlo en nuestra nave de Mataró. Estas son las zonas donde más nos piden.',
		'grupos'  => 'Por comarca',
		'resto'   => 'Y a toda la península, con entrega al día siguiente por la mañana.',
		'envios'  => 'Envíos y plazos',
	),
	'zona' => array(
		'seo_titulo'      => 'Hielo seco en {nombre}: entrega mañana | DryIcePack',
		'seo_descripcion' => 'Hielo seco en {nombre}: pide antes de las 12:00 y llega al día siguiente por la mañana, o recógelo gratis en nuestra nave de Mataró, a {km} km. Desde 3 kg.',
		// Para nombres largos (la descripción no debe pasar de 155 caracteres)
		'seo_descripcion_corta' => 'Hielo seco en {nombre}: pide antes de las 12:00 y llega al día siguiente por la mañana, o recógelo gratis en Mataró, a {km} km. Desde 3 kg.',
		'migas'   => array( 'Inicio', 'Zonas', '{Nombre}' ),
		'titulo'  => 'Hielo seco en|<em>{nombre}.</em>',
		'texto'   => 'Pide antes de las 12:00 y te lo llevamos a {nombre} al día siguiente por la mañana. O recógelo en nuestra nave de Mataró, a {km} km.',
		'boton'   => 'Comprar hielo seco',
		'datos'   => array(
			array( 'Distancia a la nave', '{km} km' ),
			array( 'En coche, sin tráfico', 'unos {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => 'Entrega en|<em>{nombre}.</em>',
			'filas'  => array(
				array( 'Pedido antes de las 12:00', 'Llega al día siguiente por la mañana, de martes a viernes.' ),
				array( 'Sábado', 'Según el código postal, con suplemento de 11,74 €.' ),
				array( 'Recogida', 'Gratis en Mataró, de lunes a viernes de 9:00 a 18:00, a {km} km de {nombre}.' ),
				array( 'Envío de 10 kg', '17,32 € con IVA, el mismo precio que en toda la península.' ),
				array( 'Empresas de {comarca}', 'Cuenta con precio propio y factura mensual.' ),
			),
		),
		'usos'    => array(
			'titulo' => 'Para qué lo piden en {comarca}',
			'lista'  => array( array( 'hosteleria', 'Hostelería' ), array( 'transporte', 'Transporte en frío' ), array( 'industria', 'Industria' ), array( 'laboratorios', 'Laboratorios' ), array( 'eventos', 'Fiestas y eventos' ) ),
		),
		'faq'     => array(
			array( '¿Cuánto tarda en llegar el hielo seco a {nombre}?', 'Si pides antes de las 12:00 de lunes a viernes, llega a {nombre} al día siguiente por la mañana. No repartimos en domingo ni lunes.' ),
			array( '¿Puedo recogerlo si estoy en {nombre}?', 'Sí. Nuestra nave está en Mataró, a {km} km (unos {min} min en coche sin tráfico). La recogida es gratis y se paga en efectivo.' ),
			array( '¿Hacéis entregas en sábado en {nombre}?', 'Según el código postal. Tiene un suplemento de 11,74 € (IVA incluido) y lo eliges al pagar.' ),
		),
		'cerca'   => 'Zonas cercanas',
		'fuente'  => 'Distancias y tiempos: OpenStreetMap y OSRM, sin tráfico.',
	),
);
