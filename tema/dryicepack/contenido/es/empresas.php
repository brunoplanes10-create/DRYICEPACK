<?php
/* Empresas (cuenta de empresa y presupuesto por volumen) · castellano · trato de usted */
return array(
	'menu' => 'Empresas',
	'seo'  => array(
		'titulo'      => 'Proveedor de hielo seco para empresas | DryIcePack',
		'descripcion' => 'Hielo seco para su negocio con precio propio, pedido por WhatsApp o en la web y una factura al mes. De 3 kg a más de 250 kg. Entrega por la mañana.',
	),
	'migas' => array( 'Inicio', 'Empresas' ),
	'hero'  => array(
		'titulo' => 'Proveedor de hielo seco|para quien lo usa|<em>cada semana.</em>',
		'texto'  => 'Precio propio, pedido en dos clics o por WhatsApp y una sola factura al mes. Sin permanencia.',
		'boton'  => 'Abrir cuenta',
		'tel'    => 'o llame a ventas:',
		'alt'    => 'Operario abriendo una caja de hielo seco en el almacén',
	),
	'pedir' => array(
		'titulo'   => 'Pida como|<em>le vaya mejor.</em>',
		'canales'  => array(
			array( 'WhatsApp', 'Un mensaje y listo. Le confirmamos el día de entrega por el mismo chat.' ),
			array( 'En la web', 'Entra en su cuenta y repite cualquier pedido anterior con un clic.' ),
			array( 'Email', 'Para pedidos programados o con varias direcciones de entrega.' ),
		),
		'chat'     => array( 'Hola, ¿nos mandáis 10 kg de 16 mm para el martes?', 'Hecho. Sale el lunes y llega el martes por la mañana. Va en la factura del mes.' ),
		'repetir'  => array( 'titulo' => 'Sus pedidos', 'filas' => array( array( '10 kg · 16 mm', 'Repetir' ), array( '20 kg · 3 mm', 'Repetir' ), array( '10 kg · 16 mm', 'Repetir' ) ) ),
		'email'    => array( 'Para: info@dryicepack.es', 'Asunto: Pedido semanal', 'Buenos días: como cada lunes, 15 kg de 16 mm a la nave de Rubí. Gracias.' ),
	),
	'ventajas' => array(
		'titulo' => 'Lo que cambia|con una <em>cuenta.</em>',
		'lista'  => array(
			array( 'Precio propio', 'Según volumen y frecuencia. Se acuerda una vez y no hay que negociarlo en cada pedido.' ),
			array( 'Una factura al mes', 'Todos los pedidos del mes, en una factura completa. Pago por transferencia o domiciliación.' ),
			array( 'Datos guardados', 'Razón social, NIF y dirección de entrega: no se vuelven a escribir.' ),
			array( 'Recordatorio, si lo quiere', 'Si pide con un ritmo fijo, le avisamos cuando toca. Se desactiva con un clic.' ),
			array( 'Sin permanencia', 'Se para o se ajusta cuando cambie su actividad.' ),
		),
	),
	'volumen' => array(
		'titulo'  => '¿Cuánto|<em>necesita?</em>',
		'rango'   => 'Kilos por pedido',
		'cajas'   => '%1$s cajas · %2$s kg',
		'caja'    => '1 caja · %s kg',
		'notas'   => array(
			'web'   => 'Hasta 250 kg por pedido, en la web o con su cuenta.',
			'bcn'   => 'Más de 150 kg en la provincia de Barcelona: entrega en sus instalaciones, consulte el plazo.',
			'fuera' => 'Mucho peso fuera de la provincia de Barcelona: lo estudiamos caso a caso.',
		),
	),
	'casos' => array(
		'titulo' => 'Para qué lo usan|nuestros <em>clientes.</em>',
		'lista'  => array(
			array( 'Automoción de competición', 'Enfriar baterías entre sesiones de pruebas.', 'uso-industria' ),
			array( 'Logística de palés', 'Mantener fría la carga durante el viaje.', 'cajas-furgoneta' ),
			array( 'Obrador de pizza', 'Transporte de producto en frío.', 'uso-transporte' ),
			array( 'Laboratorio de semillas', 'Envío de muestras congeladas.', 'uso-laboratorio-entrega' ),
			array( 'Cervecería', 'Humo en sala y cócteles cada fin de semana.', 'uso-hosteleria' ),
			array( 'Restaurantes de sushi', 'Humo al emplatar, en cada servicio.', 'nuggets-ia' ),
		),
		'nota'   => 'Casos reales de clientes de DryIcePack. No publicamos sus nombres sin permiso.',
	),
	'alta' => array(
		'titulo'  => 'Abra su cuenta|en <em>un minuto.</em>',
		'texto'   => 'Rellene la frase y le contestamos con su precio, normalmente el mismo día laborable.',
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
		'titulo' => 'Preguntas de empresa',
		'lista'  => array(
			array( '¿Hay pedido mínimo?', 'En la web, desde 3 kg. Con cuenta de empresa lo ajustamos a su consumo.' ),
			array( '¿Cómo se paga?', 'Con una factura al mes, por transferencia o domiciliación, según lo que acordemos al abrir la cuenta.' ),
			array( '¿Puedo cambiar kilos o frecuencia?', 'Sí. Avísenos con 24 a 48 horas laborables para organizar la preparación y la ruta.' ),
			array( '¿Emiten factura completa?', 'Siempre, con sus datos fiscales. En cada pedido y en el resumen mensual.' ),
			array( '¿Llegan fuera de Cataluña?', 'A toda la península por mensajería, con entrega al día siguiente por la mañana. Los pedidos de mucho peso fuera de la provincia de Barcelona los estudiamos caso a caso.' ),
			array( '¿Tienen ficha de datos de seguridad?', 'Sí. La puede descargar en la página de seguridad, junto con las normas de manipulación.' ),
		),
	),
	'final' => array( 'titulo' => '¿Prefiere hablarlo?', 'texto' => 'Ventas: de lunes a viernes, de 9:00 a 18:00.' ),
);
