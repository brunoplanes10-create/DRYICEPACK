<?php
/* Portada · castellano. Las líneas de los títulos se separan con "|". */
return array(
	'menu' => 'Inicio',
	'seo'  => array(
		'titulo'      => 'Hielo seco: compra online y recíbelo mañana | DryIcePack',
		'descripcion' => 'Hielo seco en pellets de 3 mm o nuggets de 16 mm, desde 3 kg. Pide antes de las 12:00 y mañana por la mañana lo tienes. O recógelo gratis en Mataró.',
	),
	'hero' => array(
		'titulo'    => 'Hielo seco en tu puerta,|<em>mañana por la mañana.</em>',
		'texto'     => 'Pide antes de las 12:00 y mañana por la mañana lo tienes (de martes a sábado), o elige el día que quieras. En caja aislante, desde 3 kg y sin contratos. ¿Cerca de Mataró? Recógelo gratis.',
		'boton'     => 'Hacer mi pedido',
		'o_llama'   => 'o llama al',
		'alt'       => 'Caja de hielo seco en nuggets de 16 mm, abierta y soltando vapor frío',
		'corte'     => array( 'antes' => 'Pide antes de las 12:00 y lo tienes el %s por la mañana', 'quedan' => 'Quedan %s', 'despues' => 'Si pides ahora, lo tienes el %s por la mañana' ),
	),
	'cinta' => array( '−78,5 °C', 'Pellets de 3 mm', 'Nuggets de 16 mm', 'Caja EPS de 40 mm', 'Corte a las 12:00', 'Entrega por la mañana', 'Recogida en Mataró', 'Factura mensual para empresas', 'De 3 a 250 kg por pedido' ),
	'elegir' => array(
		'titulo'   => 'Sabes lo que pagas|<em>antes de pagar.</em>',
		'formatos' => array(
			'3mm'  => array( 'nombre' => 'Pellets de 3 mm', 'texto' => 'Enfría más rápido y hace la niebla más densa.', 'para' => 'Cócteles, niebla, procesos', 'foto' => 'cenital-3mm', 'alt' => 'Pellets de hielo seco de 3 mm vistos desde arriba' ),
			'16mm' => array( 'nombre' => 'Nuggets de 16 mm', 'texto' => 'Aguanta más horas en la caja. Para transporte y muestras.', 'para' => 'Transporte, muestras, cadena de frío', 'foto' => 'cenital-16mm', 'alt' => 'Nuggets de hielo seco de 16 mm vistos desde arriba' ),
		),
		'cuanto'    => '¿Cuántos kilos?',
		'envio'     => 'Envío',
		'total'     => 'Total con envío',
		'recogida'  => 'Si lo recoges en Mataró: %s',
		'nota'      => 'IVA incluido. El total ya incluye el envío: sin sorpresas en el último paso.',
		'boton'     => 'Pedir %s kg',
		'mas'       => '¿Más de 20 kg? Suma cajas, hasta 250 kg por pedido. Si son más de 150 kg y estás fuera de la provincia de Barcelona, escríbenos.',
	),
	'llega' => array(
		'titulo' => 'Pides antes de las 12:00.|<em>Mañana por la mañana lo tienes.</em>',
		'pasos'  => array(
			array( '12:00', 'Haces tu pedido', 'Antes de las 12:00, de lunes a viernes, salvo festivos. Sale de Mataró ese mismo día.' ),
			array( 'Hoy', 'Lo preparamos', 'En caja EPS de 40 mm, cerrada pero no hermética, y se va con la mensajería.' ),
			array( 'Mañana', 'Lo tienes por la mañana', 'De martes a viernes. El sábado, según zona y con suplemento de 11,74 €.' ),
		),
		'nota'   => 'Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla. Sin entregas en domingo ni lunes.',
		'enlace' => 'Envíos y plazos',
		'mapa'   => 'Mapa de la península con rutas de reparto desde Mataró',
	),
	'usos' => array(
		'titulo' => '¿Para qué lo necesitas?|<em>Elige tu caso.</em>',
		'lista'  => array(
			array( 'hosteleria', 'Hostelería', 'Cócteles y platos que salen a sala con humo.', 'uso-hosteleria' ),
			array( 'transporte', 'Transporte en frío', 'Congelado y refrigerado que llega igual que salió.', 'uso-transporte' ),
			array( 'industria', 'Industria', 'Enfriar piezas y baterías, ensayos y pedidos de hasta 250 kg.', 'uso-industria' ),
			array( 'laboratorios', 'Laboratorios', 'Muestras a −78,5 °C y envíos UN 1845.', 'uso-laboratorio' ),
			array( 'eventos', 'Fiestas y eventos', 'Niebla por el suelo en bodas, escenarios y Halloween.', 'uso-eventos' ),
		),
		'todos'  => 'Todos los usos',
	),
	'clientes' => array(
		'titulo' => 'No lo decimos nosotros.|<em>Lo usan ellos.</em>',
		'texto'  => 'Cuatro clientes que nos dejan decirlo, y para qué lo usan.',
		'lista'  => array(
			array( 'id' => 'italpizza', 'uso' => 'Transporte en frío', 'alt' => 'Logotipo de Italpizza' ),
			array( 'id' => 'sushitok', 'uso' => 'Humo al emplatar', 'alt' => 'Logotipo de Sushitok' ),
			array( 'id' => 'fito', 'uso' => 'Transporte de muestras', 'alt' => 'Logotipo de Semillas Fitó' ),
			array( 'id' => 'nissan', 'uso' => 'Enfriar baterías', 'alt' => 'Logotipo de Nissan Formula E Team' ),
		),
	),
	'ficha' => array(
		'titulo' => 'No es hielo.|Es CO<sub>2</sub> a −78,5 °C.',
		'cotas'  => array(
			array( 'Sublima', 'Pasa de sólido a gas sin fundirse. Ni charcos ni cajas con agua.' ),
			array( '60 °C más frío', 'Que un congelador doméstico, que trabaja a −18 °C.' ),
			array( '3 mm o 16 mm', 'Los pequeños enfrían antes. Los grandes duran más.' ),
			array( 'Caja EPS de 40 mm', 'Aísla y deja salir el gas: nunca va cerrada hermética.' ),
		),
		'enlace' => 'Qué es el hielo seco',
		'alt'    => 'Nuggets de hielo seco en su caja, vistos desde arriba, con vapor',
	),
	'empresas' => array(
		'titulo'  => '¿Cada semana el mismo pedido?|<em>Que no tengas que acordarte.</em>',
		'texto'   => 'Pides por WhatsApp o en dos clics. Contrato de suministro, descuento sobre tarifa según volumen y una factura al mes, a 30 días.',
		'tarjeta' => array( 'titulo' => 'Cuenta de empresa', 'lineas' => array( 'Descuento sobre tarifa', 'Factura mensual a 30 días', 'Pedido por WhatsApp o web', 'Datos fiscales guardados' ), 'pie' => 'DryIcePack · Mataró' ),
		'enlace'  => 'Abrir mi cuenta',
	),
	'recogida' => array(
		'titulo'  => '¿Estás cerca de Mataró?|Recógelo <em>gratis.</em>',
		'texto'   => 'Sin esperar a la mensajería. Eliges el día al pedir, te confirmamos la hora y te lo tenemos preparado en la nave. Gratis de lunes a viernes; el sábado, con suplemento de 11,74 €. Pagas en efectivo al recogerlo.',
		'ficha'   => 'Nave DryIcePack',
		'como'    => 'Cómo llegar',
		'alt'     => 'Carga de una caja de hielo seco en la furgoneta de reparto',
		'cerca'   => array( 'Mataró', 'Maresme', 'Barcelona', 'Vallès' ),
	),
	'seguridad' => array(
		'titulo' => 'Seguro,|si sabes <em>cómo.</em>',
		'texto'  => 'Son seis reglas. Léelas antes de abrir la caja.',
	),
	'faq' => array(
		'titulo' => 'Lo que más|nos <em>preguntan.</em>',
		'lista'  => array(
			array( '¿Cuánto hielo seco necesito?', 'Depende del uso y de cuántas horas lo necesites. Para niebla, como referencia: en casa 3 kg, en una fiesta grande 10 kg y en un bar o un evento de 15 a 20 kg. Para transporte o muestras, dinos cuántas horas y qué volumen, y te lo calculamos.' ),
			array( '¿Cuándo me llega?', 'Si pides antes de las 12:00 de lunes a viernes, sale ese mismo día y lo tienes al día siguiente por la mañana. No repartimos en domingo ni lunes. El sábado, según zona y con un suplemento de 11,74 € (IVA incluido).' ),
			array( '¿Puedo pedir para otro día?', 'Sí: al pagar eliges cualquier día de los próximos 90 con reparto en tu zona, de martes a sábado (el sábado, según zona y con suplemento). Pídelo para el día que lo vas a usar: el hielo seco se va sublimando con las horas.' ),
			array( '¿Puedo recogerlo en persona?', 'Sí, en nuestra nave de Mataró (Camí Ca La Madrona 19 D), de lunes a sábado. De lunes a viernes es gratis y el sábado lleva un suplemento de 11,74 €. Te confirmamos la hora antes y pagas en efectivo al recogerlo.' ),
			array( '¿Qué diferencia hay entre 3 mm y 16 mm?', 'Los pellets de 3 mm tienen más superficie: enfrían más rápido y hacen más niebla. Los nuggets de 16 mm tienen más masa por pieza y duran más, por eso se usan en transporte y muestras.' ),
			array( '¿Hacéis factura a particulares y a empresas?', 'Sí. Si eres particular y el pedido es de hasta 400 € (IVA incluido), la factura simplificada te llega por email desde info@dryicepack.es. Si compras para una empresa o como autónomo, marca «Compro para una empresa o autónomo» al pagar y escribe la razón social y el NIF: recibes la factura completa. Si ya has pagado sin marcarlo, te enviamos un enlace para pedirla. Las cuentas de empresa reciben una sola factura al mes, a 30 días.' ),
			array( '¿Enviáis a Baleares o Canarias?', 'No. Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.' ),
		),
	),
	'final' => array(
		'titulo' => '¿Lo necesitas mañana?|<em>Pídelo antes de las 12:00.</em>',
		'boton'  => 'Hacer mi pedido',
		'wa'     => 'Escríbenos por WhatsApp',
	),
);
