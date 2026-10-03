<?php
/* Empresas (cuenta de empresa y presupuesto por volumen) · castellano · trato de usted */
return array(
	'menu' => 'Empresas',
	'seo'  => array(
		'titulo'      => 'Proveedor de hielo seco para empresas | DryIcePack',
		'descripcion' => 'Proveedor de hielo seco para su negocio: contrato de suministro, descuento sobre tarifa según volumen y una sola factura al mes, a 30 días. Pida su precio.',
	),
	'migas' => array( 'Inicio', 'Empresas' ),
	'hero'  => array(
		'titulo' => 'Proveedor de hielo seco:|<em>cada semana,|sin tener que pedirlo.</em>',
		'texto'  => 'Nos dice cuántos kilos y cada cuánto, y le llega por la mañana. Firmamos un contrato de suministro con descuento sobre tarifa según volumen y frecuencia, y le facturamos una vez al mes, a 30 días.',
		'boton'  => 'Abrir mi cuenta',
		'tel'    => 'o llame a ventas:',
		'alt'    => 'Operario abriendo una caja de hielo seco en el almacén',
	),
	'pedir' => array(
		'titulo'   => '¿Cómo se pide?|<em>Como le vaya mejor.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'Un mensaje con los kilos y el día, y listo. Le confirmamos la entrega por el mismo chat.' ),
			array( 'En la web', 'Entre en su cuenta y repita cualquier pedido con un clic, sin volver a rellenar nada.' ),
			array( 'Email', 'Para programar un pedido fijo, que se escribe una sola vez, o para entregas en varias direcciones.' ),
		),
		'chat'     => array( 'Hola, ¿nos mandáis 10 kg de 16 mm para el martes?', 'Hecho. Sale el lunes y llega el martes por la mañana. Va en la factura del mes.' ),
		'repetir'  => array( 'titulo' => 'Sus pedidos', 'filas' => array( array( '10 kg · 16 mm', 'Repetir' ), array( '20 kg · 3 mm', 'Repetir' ), array( '10 kg · 16 mm', 'Repetir' ) ) ),
		'email'    => array( 'Para: info@dryicepack.es', 'Asunto: Pedido programado', 'Buenos días: a partir de ahora, 15 kg de 16 mm cada martes en la nave de Rubí. Gracias.' ),
	),
	'ventajas' => array(
		'titulo' => '¿Qué gana|con una <em>cuenta de empresa?</em>',
		'lista'  => array(
			array( 'Descuento sobre tarifa', 'Según volumen y frecuencia. Se acuerda al abrir la cuenta y se aplica en cada pedido.' ),
			array( 'Una factura al mes', 'Todos los pedidos del mes en una sola factura completa, a 30 días, por domiciliación SEPA o transferencia. Menos papeles para usted y para su gestoría.' ),
			array( 'Datos guardados', 'Razón social, NIF y direcciones de entrega se guardan una vez. No los vuelve a escribir.' ),
			array( 'Pedido programado o aviso', 'Si pide siempre lo mismo, se lo programamos y llega solo. Si prefiere decidir cada vez, le avisamos cuando toca.' ),
			array( 'Contrato de suministro', 'Kilos, frecuencia, descuento y forma de pago, por escrito. ¿Cambia la temporada? Lo ajustamos con usted.' ),
		),
	),
	'volumen' => array(
		'titulo'  => '¿Cuánto|<em>necesita?</em>',
		'rango'   => 'Kilos por pedido',
		'cajas'   => '%1$s cajas · %2$s kg',
		'caja'    => '1 caja · %s kg',
		'notas'   => array(
			'web'   => 'Hasta 150 kg: en la web, con su cuenta o por WhatsApp, con envío a toda la España peninsular.',
			'bcn'   => 'Más de 150 kg y hasta 250 kg: en la provincia de Barcelona se lo llevamos a sus instalaciones; fuera, el envío se prepara a medida y sale más caro. Escríbanos y le damos precio y día.',
			'fuera' => 'Más de 250 kg por pedido: escríbanos y le damos precio y día.',
		),
	),
	'casos' => array(
		'titulo' => '¿Quién lo usa ya?|<em>De la Fórmula E al sushi.</em>',
		'lista'  => array(
			// Con cliente: solo fotos reales del producto. Sin cliente: el uso, sin más datos.
			array( 'Automoción de competición', 'Enfriar baterías rápidamente.', 'caja-16mm-lateral', 'nissan' ),
			array( 'Transporte de palés', '', 'cajas-furgoneta' ),
			array( 'Pizza congelada', 'Transporte en frío.', 'nuggets-azul', 'italpizza' ),
			array( 'Empresa de semillas', 'Transporte de muestras.', 'cenital-caja-nuggets', 'fito' ),
			array( 'Humo y cócteles', '', 'uso-hosteleria' ),
			array( 'Restaurantes de sushi', 'Efecto humo al emplatar.', 'anadiendo-hielo', 'sushitok' ),
		),
		'logos'  => array(
			'nissan'    => 'Logotipo de Nissan Formula E Team',
			'italpizza' => 'Logotipo de Italpizza',
			'fito'      => 'Logotipo de Semillas Fitó',
			'sushitok'  => 'Logotipo de Sushitok',
		),
		'nota'   => 'Clientes reales de DryIcePack, publicados con su permiso.',
	),
	'alta' => array(
		'titulo'  => 'Abra su cuenta|<em>con una sola frase.</em>',
		'texto'   => 'Complétela y le contestamos con su precio, normalmente el mismo día laborable.',
		'frase'   => array(
			'Somos', 'empresa', 'su empresa',
			'y usamos hielo seco para', 'uso', 'transporte, humo…',
			'. Necesitamos unos', 'kg', '10',
			'kg', 'frecuencia', array( 'cada semana', 'cada 15 días', 'cada mes', 'una sola vez' ),
			', con entrega en el código postal', 'cp', '08302',
			'. Pueden llamar a', 'nombre', 'su nombre',
			'al', 'telefono', '600 000 000',
			'o escribir a', 'email', 'correo@empresa.es',
		),
		'nif'          => 'NIF/CIF (opcional)',
		'recordatorio' => 'Quiero que me avisen cuando me toque repetir el pedido.',
		'boton'        => 'Pedir mi precio',
		'enviando'     => 'Enviando…',
	),
	'faq' => array(
		'titulo' => 'Lo que preguntan las empresas',
		'lista'  => array(
			array( '¿Hay pedido mínimo?', 'En la web, desde 3 kg. Con cuenta de empresa, lo ajustamos a lo que usted consume.' ),
			array( '¿Cómo se paga?', 'Con una sola factura al mes, a 30 días, por domiciliación SEPA siempre que se pueda, o por transferencia.' ),
			array( '¿Hay que firmar un contrato?', 'Para la cuenta de empresa, sí: un contrato de suministro de hielo seco con los kilos, la frecuencia, el descuento sobre tarifa y la forma de pago. Para comprar en la web no hace falta: pida y pague al momento, como cualquier cliente.' ),
			array( '¿Qué precio tendré?', 'Un descuento sobre la tarifa de la web, según su volumen y su frecuencia. Se acuerda al abrir la cuenta. Complete la frase de arriba y le contestamos, normalmente el mismo día laborable.' ),
			array( '¿Puedo cambiar kilos o frecuencia?', 'Sí, cuando lo necesite. Avísenos con antelación por WhatsApp, teléfono o email y lo ajustamos.' ),
			array( '¿Emiten factura completa?', 'Sí, siempre, con sus datos fiscales. Con cuenta de empresa recibe una sola factura al mes con todos los pedidos.' ),
			array( '¿Llegan fuera de Cataluña?', 'Sí, a toda la península por mensajería. Si pide antes de las 12:00, llega al día siguiente por la mañana, de martes a sábado (el sábado, según zona y con un suplemento de 11,74 €, IVA incluido). Más de 150 kg fuera de la provincia de Barcelona: escríbanos y se lo preparamos a medida.' ),
			array( '¿Tienen ficha de datos de seguridad?', 'Sí. Puede descargarla en la página de seguridad, junto con las normas de manipulación para su equipo.' ),
		),
	),
	'final' => array( 'titulo' => '¿Prefiere hablarlo con una persona?', 'texto' => 'Ventas, de lunes a viernes de 9:00 a 18:00. Fuera de ese horario, escríbanos por WhatsApp al 686 980 471.' ),
);
