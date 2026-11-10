<?php
/* Zonas de entrega (índice y plantilla de zona) · castellano. Marcadores: {nombre} {Nombre} {km} {min} {comarca} */
return array(
	'menu' => 'Zonas de entrega',
	'seo'  => array(
		// Página nacional (10/11/2026): dónde llegamos. Las condiciones y los plazos son de /envios-y-plazos/.
		'titulo'      => 'Hielo seco a domicilio en toda la península | DryIcePack',
		'descripcion' => 'Hielo seco a domicilio en toda la península, provincia a provincia: pide antes de las 12:00 y llega al día siguiente antes de las 14:00. Busca tu ciudad.',
	),
	'mapa'   => 'Mapa con la nave de Mataró en el centro, anillos cada 10 km y las zonas de entrega',
	'indice' => array(
		'migas'   => array( 'Inicio', 'Zonas de entrega' ),
		'titulo'  => 'Hielo seco a domicilio|<em>en toda la península.</em>',
		'texto'   => 'Pide antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00 a cualquier código postal de la península. O elige el día que quieras. ¿Estás cerca de Mataró? Recógelo en la nave: {pk} kg por {recogida:pk}, IVA incluido.',
		'provincias' => 'Toda la península, por comunidad autónoma',
		'grupos'  => 'Cerca de Mataró, por comarca',
		'resto'   => '¿No ves tu ciudad? Llega igual a cualquier código postal de la península, al día siguiente antes de las 14:00, de martes a viernes (el sábado, según zona, antes de las 12:00). No enviamos a Baleares, Canarias, Ceuta ni Melilla.',
		'envios'  => 'Ver plazos y precios de envío',
		// Preguntas reales (Search Console: «donde comprar hielo seco cerca de mi», 377 impresiones en 12 meses)
		'faq'     => array(
			array( '¿Dónde comprar hielo seco cerca de mí?', 'Online, en esta web, estés donde estés de la península: pide antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00 en caja EPS, o el día que elijas. Si estás cerca de Mataró, recógelo en nuestra nave de lunes a sábado: {pk} kg por {recogida:pk}, IVA incluido y en efectivo.' ),
			array( '¿Enviáis a Baleares, Canarias, Ceuta o Melilla?', 'No. Solo enviamos a la España peninsular, a cualquier código postal de la península.' ),
			array( '¿Puedo pedirlo para un día concreto?', 'Sí: al pagar eliges cualquier día de los próximos 90 con reparto en tu zona, de martes a sábado (el sábado, según zona y con suplemento de {sabado_envio}, IVA incluido).' ),
		),
	),
	'zona' => array(
		'seo_titulo'      => 'Hielo seco en {nombre}: antes de las 14:00 | DryIcePack',
		'seo_descripcion' => 'Hielo seco en {nombre}: pide antes de las 12:00 y llega al día siguiente antes de las 14:00. O recógelo en Mataró, a {km} km: {pk} kg por {recogida:pk} con IVA.',
		// Para nombres largos (la descripción no debe pasar de 155 caracteres)
		'seo_descripcion_corta' => 'Hielo seco en {nombre}: pide antes de las 12:00 y llega al día siguiente antes de las 14:00. O recógelo en la nave de Mataró, a {km} km.',
		'migas'   => array( 'Inicio', 'Zonas', '{Nombre}' ),
		'titulo'  => 'Hielo seco en {nombre},|<em>en tu puerta antes de las 14:00.</em>',
		'texto'   => 'Pide antes de las 12:00, de lunes a jueves, y llega a {nombre} al día siguiente antes de las 14:00. O elige el día que quieras. Desde {pk} kg y sin contratos. ¿Prefieres venir a buscarlo? Nuestra nave está en Mataró, a {km} km.',
		'boton'   => 'Hacer mi pedido',
		'datos'   => array(
			array( 'Distancia a la nave', '{km} km' ),
			array( 'En coche, sin tráfico', 'unos {min} min' ),
			array( 'Comarca', '{comarca}' ),
		),
		'entrega' => array(
			'titulo' => '¿Cuándo llega a {nombre}?|<em>Y cuánto cuesta.</em>',
			'filas'  => array(
				array( 'Pedido antes de las 12:00', 'Llega al día siguiente antes de las 14:00, de martes a viernes. Domingo y lunes no hay reparto.' ),
				array( 'Sábado', 'Si tu código postal tiene reparto en sábado, llega antes de las 12:00, con un suplemento de {sabado_envio} (IVA incluido). Pídelo antes de las 12:00 del viernes.' ),
				array( 'Recogida', 'En nuestra nave de Mataró, a {km} km, de lunes a sábado. Precio redondo con IVA incluido: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}. El sábado, +{sabado_recogida} (IVA incluido).{sabado_libre} Tú eliges el día y te confirmamos la hora. En efectivo y con el importe exacto.' ),
				array( 'Envío de 10 kg', '17,32 € con IVA, igual que en toda la península. Lo ves en el total antes de pagar: sin sorpresas en el último paso.' ),
				array( 'Para empresas', 'Cuenta con contrato de suministro, descuento sobre tarifa según volumen, pedidos programados y una sola factura al mes, a 30 días.' ),
			),
		),
		'usos'    => array(
			'titulo' => '¿Para qué lo necesitas?',
			'lista'  => array( array( 'hosteleria', 'Hostelería' ), array( 'transporte', 'Transporte en frío' ), array( 'industria', 'Industria' ), array( 'laboratorios', 'Laboratorios' ), array( 'eventos', 'Fiestas y eventos' ) ),
		),
		'faq'     => array(
			array( '¿Cuánto tarda en llegar el hielo seco a {nombre}?', 'Si pides antes de las 12:00 de lunes a jueves, llega a {nombre} al día siguiente antes de las 14:00. Si pides el viernes, llega el sábado antes de las 12:00 (si tu código postal tiene reparto, con suplemento) o el martes. Domingo y lunes no hay reparto. Al pagar solo ves los días disponibles.' ),
			array( '¿Puedo recogerlo si estoy en {nombre}?', 'Sí. Nuestra nave está en Mataró, a {km} km (unos {min} min en coche sin tráfico). Eliges el día al pedir, de lunes a sábado, y te confirmamos la hora por teléfono o WhatsApp. Precio redondo con IVA incluido, el mismo en la web y en la nave: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}. El sábado, +{sabado_recogida} (IVA incluido).{sabado_libre} Se paga en efectivo y con el importe exacto: no tenemos cambio. En el coche, llévalo en el maletero y ventilando.' ),
			array( '¿Hacéis entregas en sábado en {nombre}?', 'Depende del código postal. Si tu zona tiene reparto en sábado, te sale como opción al pagar, con un suplemento de {sabado_envio} (IVA incluido), y llega antes de las 12:00. Para recibirlo el sábado, pide antes de las 12:00 del viernes.' ),
		),
		'cerca'   => 'Zonas cercanas',
		'fuente'  => 'Distancias y tiempos: OpenStreetMap y OSRM, sin tráfico.',
	),
);
