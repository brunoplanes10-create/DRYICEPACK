<?php
/*
 * Provincias y ciudades de la península (plantillas/provincia.php) · castellano.
 * Primero la empresa (de usted, como las páginas B2B); la entrega, los precios y las preguntas de la compra online, de tú.
 * Marcadores (inc/lugares.php, dipt_lugar_texto): {nombre} {Nombre} {provincia} {ccaa} {cp} {cp_resto} {km} {min} {fecha}
 * {y_prov} {y_toda} {y_toda_de} {envio:N} {total:N} {kilo:N} y los de la tienda: {pk} {precio:N} {recogida:N}
 * {sabado_recogida} {sabado_libre}. Nunca cifras fijas de precios: salen de la tienda. El envío en sábado ya no tiene
 * suplemento fijo (11/11/2026): su precio depende del destino y los kilos y se ve al pagar.
 */
return array(
	'marcadores' => array(
		'y_prov'       => ' y {provincia}',
		'y_toda'       => ' y toda {provincia}',
		'y_toda_su'    => ' y toda su provincia',
		'y_toda_de'    => ' y de toda {provincia}',
		'y_toda_de_su' => ' y de toda su provincia',
	),
	// Se usa el primero que cabe: título ≤ 60 caracteres y descripción de 140 a 155 (inc/lugares.php, dipt_seo_lugar)
	'seo' => array(
		'titulos' => array(
			'Hielo seco en {nombre}{y_prov}: proveedor para empresas | DryIcePack',
			'Hielo seco en {nombre}{y_prov}: proveedor para empresas',
			'Hielo seco en {nombre}: proveedor para empresas',
			'Hielo seco en {nombre}: para empresas',
			'Hielo seco en {nombre}',
		),
		'descripciones' => array(
			'Hielo seco en {nombre}{y_prov} para empresas: suministro programado y entrega al día siguiente antes de las 14:00 si pide antes de las 12:00. Pida presupuesto.',
			'Hielo seco en {nombre}{y_prov}: suministro programado y, con pedido antes de las 12:00, entrega al día siguiente antes de las 14:00. Pida precio.',
			'Hielo seco en {nombre} para empresas: suministro programado y entrega al día siguiente antes de las 14:00 si pide antes de las 12:00. Pida presupuesto.',
			'Hielo seco en {nombre}: suministro programado y, con pedido antes de las 12:00, entrega al día siguiente antes de las 14:00. Pida precio.',
			'Hielo seco en {nombre}: suministro programado y entrega al día siguiente antes de las 14:00 si pide antes de las 12:00. Pida precio.',
			'Hielo seco en {nombre}: suministro programado para empresas y entrega antes de las 14:00. Pida presupuesto.',
		),
	),
	'migas' => array( 'Inicio', 'Zonas' ),
	// L1 · Título: primero el negocio, con la compra puntual a la vista
	'hero' => array(
		'titulo' => 'Proveedor de hielo seco|<em>en {nombre}{y_toda}.</em>',
		'texto'  => array(
			'provincia' => 'Suministro programado para empresas y compra puntual online, desde {pk} kg. Con pedido antes de las 12:00, de lunes a jueves, llega a {nombre} al día siguiente antes de las 14:00, en caja EPS.',
			'ciudad'    => 'En {nombre} ({provincia}): suministro programado para empresas y compra puntual online, desde {pk} kg. Con pedido antes de las 12:00, de lunes a jueves, llega al día siguiente antes de las 14:00, en caja EPS.',
		),
		'empresa' => 'Pedir presupuesto',
		'compra'  => 'Comprar online',
		'datos'   => array(
			'entrega'   => 'Próxima entrega en {nombre}',
			'pack'      => '10 kg con envío, IVA incl.',
			'cps'       => 'Códigos postales',
			'provincia' => 'Provincia',
		),
	),
	// L2 · Empresas (usted)
	'empresa' => array(
		'titulo' => 'Suministro para empresas|<em>de {nombre}{y_toda_de}.</em>',
		'texto'  => 'Transporte en frío, hostelería, laboratorios e industria: díganos cuántos kilos necesita y cada cuánto, y le contestamos con su precio, normalmente el mismo día laborable.',
		'boton'  => 'Pedir presupuesto',
		'filas'  => array(
			array( 'Suministro programado', 'Semanal, quincenal o mensual, con mejor precio que la compra puntual. Le llega el día acordado antes de las 14:00.' ),
			array( 'Contrato de suministro', 'Firmamos un contrato de suministro de hielo seco y, si puede ser, con domiciliación SEPA.' ),
			array( 'Una factura al mes', 'Todos los pedidos del mes en una sola factura, a 30 días.' ),
			array( 'Cajas de hasta 30 kg', 'Cada caja lleva hasta 30 kg, en pellets de 3 mm o nuggets de 16 mm.' ),
		),
		// Fuera de la provincia de Barcelona, más de 150 kg no se envían por la web (el proveedor solo sirve más de 150 kg en Barcelona)
		'medida'    => array( 'Más de 150 kg por pedido', 'Fuera de la provincia de Barcelona no se envía por la web: se prepara a medida y con otro precio. Escríbanos por WhatsApp, teléfono o email.' ),
		'barcelona' => array( 'Más de 250 kg por pedido', 'Le damos precio y día a medida. Escríbanos por WhatsApp, teléfono o email.' ),
	),
	// L3 · Sectores, en el orden de prioridad del negocio
	'usos' => array(
		'titulo' => 'Hielo seco por sectores|<em>en {nombre}.</em>',
	),
	// Párrafo de usos cuando el lugar no tiene intro propia (inc/lugares.php, dipt_lugar_intro): solo lo que dicen las páginas de cada sector
	'intro_auto' => array(
		'aperturas' => array(
			'Desde Mataró enviamos hielo seco a {nombre} para {lista}.',
			'A {nombre} llega el mismo hielo seco para usos muy distintos: {lista}.',
			'En {nombre} puede recibir hielo seco para {lista}.',
		),
		'sectores' => array(
			'transporte'   => array( 'el transporte en frío de congelados', 'la cadena de frío en el reparto de congelados' ),
			'hosteleria'   => array( 'cócteles y platos con humo en hostelería', 'los cócteles con humo de la hostelería' ),
			'laboratorios' => array( 'laboratorios que conservan muestras a −78,5 °C', 'el envío de muestras de laboratorio a −78,5 °C' ),
			'industria'    => array( 'el enfriamiento de piezas y baterías en la industria', 'el enfriamiento de piezas en ensayos industriales' ),
			'eventos'      => array( 'fiestas y eventos con niebla por el suelo', 'la niebla por el suelo en eventos' ),
		),
		'cierres' => array(
			'grande'  => 'Para empresas con consumo fijo, suministro programado semanal, quincenal o mensual.',
			'pequena' => 'Para un pedido puntual, desde {pk} kg y con el envío calculado antes de pagar.',
		),
	),
	// L4 · Entrega y precios de la compra online (tú)
	'entrega' => array(
		'titulo'  => '¿Cuándo llega a {nombre}?|<em>Y cuánto cuesta.</em>',
		'tarjeta' => 'Puesto en {nombre} · IVA incl.',
		'pedido'  => array( 'Pedido antes de las 12:00', 'Reparto de martes a viernes: domingo y lunes no hay.' ),
		'sabado'  => array( 'Sábado', 'Antes de las 13:00, según zona: lo comprobamos antes de enviar. Precio de sábado: lo ves al elegir el día. Pídelo antes de las 12:00 del viernes.' ),
		'mrw'     => array( 'Días sin reparto', 'MRW no reparte {lista}. Al pagar solo te salen los días con reparto.' ),
		'mrw_dia' => array( 'provincia' => 'el {dia} en toda la provincia', 'local' => 'el {dia} en {nombre}' ),
		'pack'    => '{precio} ({kilo}/kg) + envío {envio} = {total}',
		'tarifa'  => array( 'Cómo se calcula el envío', 'Con la tarifa de la mensajería, igual en toda la península: el peso del hielo más 1 kg por caja. Lo ves en el total antes de pagar.' ),
		'recogida' => array( 'Recogida en Mataró', 'A {km} km de {nombre} (unos {min} min en coche, sin tráfico), de lunes a sábado. Precio redondo con IVA incluido: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}. El sábado, +{sabado_recogida} (IVA incluido).{sabado_libre} Tú eliges el día y te confirmamos la hora. En efectivo y con el importe exacto.' ),
		'municipios' => array( 'También enviamos a', '{lista} y al resto de la provincia (códigos postales {cp_resto}).' ),
		'resto'      => array( 'También enviamos a', 'Toda la provincia de {provincia} (códigos postales {cp_resto}).' ),
		'cps'        => array( 'Códigos postales de {nombre}', '{lista}.' ),
	),
	// L5 · Seguridad: los seis avisos comunes (contenido/es/comun.php) y el enlace a la página completa
	'seguridad' => array(
		'titulo' => 'Antes de abrir la caja:|<em>seis reglas.</em>',
	),
	// L6 · Cerca de aquí
	'cerca'  => 'Cerca de {nombre}',
	'todas'  => 'Todas las zonas de entrega',
	// L7 · Preguntas reales (FAQPage). {mrw}: los próximos días sin reparto de MRW, si los hay.
	'faq' => array(
		'donde'    => array( '¿Dónde comprar hielo seco en {nombre}?', 'Online, en esta web: eliges pellets de 3 mm o nuggets de 16 mm, desde {pk} kg, y llega a {nombre} en caja EPS antes de las 14:00 del día que elijas (si pides antes de las 12:00, de lunes a jueves, al día siguiente). Para empresas, también con suministro programado y factura mensual.' ),
		'donde_cat' => array( '¿Dónde comprar hielo seco en {nombre}?', 'Online, en esta web: eliges pellets de 3 mm o nuggets de 16 mm, desde {pk} kg, y llega a {nombre} en caja EPS antes de las 14:00 del día que elijas (si pides antes de las 12:00, de lunes a jueves, al día siguiente). O recógelo en nuestra nave de Mataró, a {km} km, de lunes a sábado.' ),
		'empresa'  => array( '¿Cómo lo pide una empresa de {nombre}?', 'Con una cuenta de empresa: contrato de suministro, pedidos programados (semanales, quincenales o mensuales) y una sola factura al mes, a 30 días. Díganos cuántos kilos y cada cuánto, y le contestamos con su precio, normalmente el mismo día laborable.' ),
		'tarda'    => array( '¿Cuánto tarda en llegar el hielo seco a {nombre}?', 'Si pides antes de las 12:00 de lunes a jueves, llega a {nombre} al día siguiente antes de las 14:00. Si pides el viernes antes de las 12:00, llega el martes, o el sábado antes de las 13:00 si tu código postal tiene reparto en sábado (con precio de sábado). Domingo y lunes no hay reparto. Al pagar solo ves los días con reparto en tu zona.{mrw}' ),
		'mrw'      => ' Próximos días sin reparto de MRW en {nombre}: {lista}.',
		'envio'    => array( '¿Cuánto cuesta el envío a {nombre}?', 'Lo mismo que a cualquier punto de la península, según el peso (IVA incluido): {lista}. Se calcula con el peso del hielo más 1 kg por caja y lo ves en el total antes de pagar.' ),
		'envio_kg' => '{kg} kg, {envio}',
		'sabado'   => array( '¿Entregáis en sábado en {nombre}?', 'Según la zona. MRW no publica qué códigos postales tienen reparto en sábado: lo comprobamos antes de enviar. Si lo hay, llega antes de las 13:00, con precio de sábado: lo ves al elegir el día. Para recibirlo el sábado, pide antes de las 12:00 del viernes.' ),
		'medida'   => array( '¿Puedo pedir más de 150 kg?', 'Sí, pero no por la web. Fuera de la provincia de Barcelona, los pedidos de más de 150 kg se preparan a medida y con otro precio: escríbenos por WhatsApp ({wa}), llámanos al {tel} o mándanos un email a {email} y te decimos precio y día.' ),
		'medida_bcn' => array( '¿Puedo pedir más de 150 kg?', 'Sí. En la provincia de Barcelona puedes pedir hasta 250 kg por la web. Para más de 250 kg, escríbenos por WhatsApp ({wa}), llámanos al {tel} o mándanos un email a {email} y te damos precio y día.' ),
		'recogida' => array( '¿Puedo recogerlo en Mataró si estoy en {nombre}?', 'Sí. Nuestra nave está en Mataró, a {km} km de {nombre} (unos {min} min en coche sin tráfico). Eliges el día al pedir, de lunes a sábado, y te confirmamos la hora por teléfono o WhatsApp. Precio redondo con IVA incluido, el mismo en la web y en la nave: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}. El sábado, +{sabado_recogida} (IVA incluido).{sabado_libre} Se paga en efectivo y con el importe exacto: no tenemos cambio. En el coche, llévalo en el maletero y ventilando.' ),
	),
	// Cierre común (partes/cierre.php)
	'cierre' => array(
		'titulo'     => 'Hielo seco en {nombre},|<em>antes de las 14:00.</em>',
		'secundario' => 'Suministro para empresas',
	),
);
