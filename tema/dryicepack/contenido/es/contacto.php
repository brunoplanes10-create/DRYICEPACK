<?php
/* Contacto · castellano */
return array(
	'menu' => 'Contacto',
	'seo'  => array(
		'titulo'      => 'Contacto: teléfono, WhatsApp y nave en Mataró | DryIcePack',
		'descripcion' => 'Llama al 936 73 76 41 o escribe por WhatsApp al 686 980 471 y te damos precio y día de entrega. O recoge tu pedido en Mataró, de lunes a sábado.',
	),
	'migas'  => array( 'Inicio', 'Contacto' ),
	'titulo' => 'Hablemos.',
	'estado' => array(
		'abierto' => 'Ahora estamos en la nave. Te contestamos enseguida.',
		'cerrado' => 'Ahora estamos cerrados, pero la tienda online no. Escríbenos y te contestamos el próximo día laborable desde las 9:00.',
	),
	'canales' => array(
		array( 'telefono', 'Teléfono', 'Para lo urgente: pedidos de última hora y dudas rápidas.' ),
		array( 'whatsapp', 'WhatsApp', 'Pide, manda una foto o repite tu pedido sin llamar.' ),
		array( 'email', 'Email', 'Para presupuestos, facturas y documentación: todo por escrito.' ),
	),
	'form' => array(
		'titulo'  => '¿Prefieres escribir?',
		'texto'   => 'Dinos para qué lo usas, cuántos kilos necesitas y tu código postal. Te contestamos con el precio y la fecha de entrega, normalmente el mismo día laborable.',
		'nombre'  => 'Nombre',
		'email'   => 'Email',
		'telefono'=> 'Teléfono',
		'mensaje' => '¿Qué necesitas y para cuándo?',
		'boton'   => 'Recibir respuesta',
		'enviando'=> 'Enviando…',
		// Rediseño 14/10/2026: línea de datos sobre el formulario y foto de la izquierda
		'linea'   => 'Uso · kg · código postal → precio y fecha',
		'alt'     => 'Guante térmico echando pellets de hielo seco en una caja EPS',
	),
	'nave' => array(
		'titulo'  => 'Nuestra nave en Mataró',
		'horario' => 'Atendemos de lunes a viernes, de 9:00 a 18:00',
		'recoger' => 'Recogida de lunes a sábado, también festivos, a la hora que te confirmamos. Precio redondo con IVA incluido: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}. El sábado, +{sabado_recogida} (IVA incluido).{sabado_libre} En efectivo y con el importe exacto: no tenemos cambio.',
		'como'    => 'Cómo llegar',
		'empresa' => 'Datos de la empresa',
		'alt'     => 'Cajas de hielo seco preparadas junto a la furgoneta de reparto',
		'alt_reparto' => 'Repartidor cargando una caja de hielo seco en la furgoneta',
	),
);
