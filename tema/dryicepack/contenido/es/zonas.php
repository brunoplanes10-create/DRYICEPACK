<?php
/* Zonas de entrega (índice y plantilla de zona) · castellano. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zonas de entrega',
	'seo'  => array(
		'titulo'      => 'Hielo seco cerca de Barcelona: zonas de entrega | DryIcePack',
		'descripcion' => 'Hielo seco en Barcelona, el Maresme, el Vallès y el Baix Llobregat: pide antes de las 12:00 y mañana lo tienes, o recógelo gratis en Mataró. Busca tu zona.',
	),
	'mapa'   => 'Mapa con la nave de Mataró en el centro, anillos cada 10 km y las zonas de entrega',
	'indice' => array(
		'migas'   => array( 'Inicio', 'Zonas de entrega' ),
		'titulo'  => 'Hielo seco cerca de ti,|<em>mañana por la mañana.</em>',
		'texto'   => 'Pide antes de las 12:00 y mañana por la mañana lo tienes (de martes a sábado), o elige el día que quieras. ¿Estás cerca de Mataró? Recógelo gratis. Busca tu zona y mira a cuántos kilómetros te queda nuestra nave.',
		'grupos'  => 'Por comarca',
		'resto'   => '¿No ves tu zona? Si está en la península, también te llega al día siguiente por la mañana. Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.',
		'envios'  => 'Ver plazos y precios de envío',
	),
	'zona' => array(
		'seo_titulo'      => 'Hielo seco en {nombre}: mañana en tu puerta | DryIcePack',
		'seo_descripcion' => 'Hielo seco en {nombre}: pide antes de las 12:00 y mañana por la mañana lo tienes en caja aislante. O recógelo gratis en Mataró, a {km} km. Desde 3 kg.',
		// Para nombres largos (la descripción no debe pasar de 155 caracteres)
		'seo_descripcion_corta' => 'Hielo seco en {nombre}: pide antes de las 12:00 y mañana por la mañana lo tienes. O recógelo gratis en Mataró, a {km} km. Desde 3 kg.',
		'migas'   => array( 'Inicio', 'Zonas', '{Nombre}' ),
		'titulo'  => 'Hielo seco en {nombre},|<em>mañana en tu puerta.</em>',
		'texto'   => 'Pide antes de las 12:00 y mañana por la mañana lo tienes en {nombre} (de martes a sábado), o elige el día que quieras. Desde 3 kg y sin contratos. ¿Prefieres venir a buscarlo? Nuestra nave está en Mataró, a {km} km.',
		'boton'   => 'Hacer mi pedido',
		'datos'   => array(
			array( 'Distancia a la nave', '{km} km' ),
			array( 'En coche, sin tráfico', 'unos {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => '¿Cuándo llega a {nombre}?|<em>Y cuánto cuesta.</em>',
			'filas'  => array(
				array( 'Pedido antes de las 12:00', 'Lo tienes al día siguiente por la mañana, de martes a viernes. Domingo y lunes no hay reparto.' ),
				array( 'Sábado', 'Si tu código postal tiene reparto en sábado, con un suplemento de 11,74 € (IVA incluido). Pídelo antes de las 12:00 del viernes.' ),
				array( 'Recogida', 'En nuestra nave de Mataró, a {km} km, de lunes a sábado. Gratis entre semana; el sábado, con suplemento. Tú eliges el día y te confirmamos la hora.' ),
				array( 'Envío de 10 kg', '17,32 € con IVA, igual que en toda la península. Lo ves en el total antes de pagar: sin sorpresas en el último paso.' ),
				array( 'Para empresas', 'Cuenta con contrato de suministro, descuento sobre tarifa según volumen, pedidos programados y una sola factura al mes, a 30 días.' ),
			),
		),
		'usos'    => array(
			'titulo' => '¿Para qué lo necesitas?',
			'lista'  => array( array( 'hosteleria', 'Hostelería' ), array( 'transporte', 'Transporte en frío' ), array( 'industria', 'Industria' ), array( 'laboratorios', 'Laboratorios' ), array( 'eventos', 'Fiestas y eventos' ) ),
		),
		'faq'     => array(
			array( '¿Cuánto tarda en llegar el hielo seco a {nombre}?', 'Si pides antes de las 12:00 de lunes a jueves, lo tienes en {nombre} al día siguiente por la mañana. Si pides el viernes, llega el sábado (si tu código postal tiene reparto, con suplemento) o el martes. Domingo y lunes no hay reparto. Al pagar solo ves los días disponibles.' ),
			array( '¿Puedo recogerlo si estoy en {nombre}?', 'Sí. Nuestra nave está en Mataró, a {km} km (unos {min} min en coche sin tráfico). Eliges el día al pedir, de lunes a sábado, y te confirmamos la hora por teléfono o WhatsApp. Entre semana es gratis y se paga en efectivo al recogerlo. En el coche, llévalo en el maletero y ventilando.' ),
			array( '¿Hacéis entregas en sábado en {nombre}?', 'Depende del código postal. Si tu zona tiene reparto en sábado, te sale como opción al pagar, con un suplemento de 11,74 € (IVA incluido). Para recibirlo el sábado, pide antes de las 12:00 del viernes.' ),
		),
		'cerca'   => 'Zonas cercanas',
		'fuente'  => 'Distancias y tiempos: OpenStreetMap y OSRM, sin tráfico.',
	),
);
