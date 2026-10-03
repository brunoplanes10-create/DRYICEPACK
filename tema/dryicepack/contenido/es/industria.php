<?php
/* Hielo seco industrial · castellano · usted */
return array(
	'menu' => 'Hielo seco industrial',
	'seo'  => array(
		'titulo'      => 'Hielo seco industrial: volumen y fecha cerrada | DryIcePack',
		'descripcion' => 'Hielo seco industrial para enfriar piezas y baterías, ensayos y montaje por contracción. Hasta 250 kg por pedido y factura completa. Pida presupuesto.',
	),
	'migas' => array( 'Inicio', 'Usos', 'Industria' ),
	'hero'  => array(
		'titulo' => 'Hielo seco industrial|en volumen y <em>el día acordado.</em>',
		'texto'  => 'Para enfriar piezas y baterías, ensayos y montaje por contracción. Usted nos dice kilos y fecha; nosotros le contestamos con precio, normalmente el mismo día laborable. Con factura completa y ficha de datos de seguridad.',
		'temperatura' => 'Temperatura del hielo seco',
		'boton'  => 'Pedir presupuesto',
		'alt'    => 'Técnico trabajando con hielo seco en una planta industrial',
	),
	'usos' => array(
		'titulo' => '¿Para qué lo usa|<em>la industria?</em>',
		'lista'  => array(
			array( 'A1', 'Enfriar baterías y componentes', 'Bajan de temperatura rápido entre pruebas o antes de manipularlos: menos tiempo parado.' ),
			array( 'A2', 'Montaje por contracción', 'La pieza enfriada encoge lo justo para encajar sin forzar.' ),
			array( 'B1', 'Ensayos a baja temperatura', 'Comprobar cómo responden materiales y equipos a temperaturas de hasta −78,5 °C.' ),
			array( 'B2', 'Producto sensible al calor', 'Adhesivos, resinas o componentes que no pueden calentarse en el envío.' ),
			array( 'C1', 'Limpieza criogénica', 'Limpieza sin agua ni residuos. Es un servicio de INDUNOVA.' ),
			array( 'C2', 'Paradas y mantenimiento', 'Lo programamos con sus revisiones: llega el día de la parada, sin pedirlo cada vez.' ),
		),
	),
	'escala' => array(
		'titulo' => '¿Cuánto le podemos|<em>servir?</em>',
		'marcas' => array(
			array( 3, 'Desde 3 kg', 'Para pruebas y pedidos pequeños.' ),
			array( 150, 'Hasta 150 kg', 'Pedido online a toda la península. Fuera de la provincia de Barcelona, más kilos por mensaje y a medida.' ),
			array( 250, 'Hasta 250 kg', 'El máximo por pedido. Online, en la provincia de Barcelona. Más de 200 kg: consúltenos, a veces lo llevamos nosotros.' ),
			array( 400, 'Suministro programado', 'Cada semana, cada 15 días o cada mes, con mejor precio.' ),
		),
	),
	'compras' => array(
		'titulo' => 'Lo que pide|<em>su departamento de compras.</em>',
		'lista'  => array(
			array( 'Factura completa', 'Con sus datos fiscales, en cada pedido. Con cuenta de empresa, una sola al mes con todos los pedidos.' ),
			array( 'Ficha de datos de seguridad', 'En PDF y en castellano, para su servicio de prevención.' ),
			array( 'Datos de empresa para el alta de proveedor', 'INDUNOVA IMS S.L. · CIF B66800103 · Mataró (Barcelona).' ),
			array( 'Fecha de entrega confirmada', 'Se elige al pedir. Para volumen, la acordamos por teléfono.' ),
		),
		'fds'    => 'Ficha de datos de seguridad',
	),
	'form' => array(
		'titulo'   => 'Pida presupuesto',
		'texto'    => 'Díganos kilos, fecha y código postal. Le contestamos con precio y día de entrega, normalmente el mismo día laborable.',
		'campos'   => array( 'kg' => 'Kilos', 'fecha' => 'Fecha que lo necesita', 'cp' => 'Código postal de entrega', 'uso' => 'Uso', 'empresa' => 'Empresa', 'nombre' => 'Nombre', 'telefono' => 'Teléfono', 'email' => 'Email' ),
		'boton'    => 'Pedir mi presupuesto',
		'enviando' => 'Enviando…',
	),
	'cliente' => array(
		'titulo'    => 'Quién lo usa|<em>y para qué.</em>',
		'etiquetas' => array( 'Cliente', 'Uso' ),
		'id'        => 'nissan',
		'detalle'   => 'Equipo de Nissan en la Fórmula E',
		'uso'       => 'Enfriar baterías rápidamente',
		'alt'       => 'Logotipo de Nissan Formula E Team',
	),
	'faq' => array(
		'titulo' => 'Preguntas de industria',
		'lista'  => array(
			array( '¿3 mm o 16 mm para enfriar piezas?', 'Normalmente 3 mm: más superficie en contacto y enfría antes. Para mantener el frío muchas horas, 16 mm.' ),
			array( '¿Pueden entregar un día concreto?', 'Sí. En la web elige el día al pagar: de martes a viernes, y el sábado según zona. Para pedidos de volumen, lo acordamos por teléfono.' ),
			array( '¿Hacen limpieza criogénica?', 'La hace INDUNOVA, la empresa de la que forma parte DryIcePack. Le ponemos en contacto.' ),
		),
	),
);
