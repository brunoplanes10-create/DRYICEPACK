<?php
/* Comprar (ficha de producto) · castellano */
return array(
	'menu' => 'Comprar hielo seco',
	'seo'  => array(
		'titulo'      => 'Comprar hielo seco: {pk}, 10, 15 y 20 kg | DryIcePack',
		'descripcion' => 'Compra hielo seco desde {pk} kg, en 3 mm o 16 mm, con el envío ya en el total. Pide antes de las 12:00 y llega al día siguiente antes de las 14:00.',
	),
	'migas'   => array( 'Inicio', 'Comprar hielo seco' ),
	'titulo'  => 'Comprar hielo seco sin sorpresas en el envío',
	'sub'     => 'Pide antes de las 12:00, de lunes a jueves, y llega al día siguiente antes de las 14:00. O elige el día que quieras. El total ya lleva el envío.',
	'galeria' => array(
		'3mm'  => array( array( 'caja-3mm', 'Caja de hielo seco en pellets de 3 mm abierta, con vapor' ), array( 'cenital-3mm', 'Pellets de hielo seco de 3 mm vistos desde arriba' ), array( 'caja-3mm-lateral', 'Caja EPS con pellets de 3 mm y la tapa apoyada' ) ),
		'16mm' => array( array( 'caja-16mm-niebla', 'Caja de hielo seco en nuggets de 16 mm abierta, con vapor' ), array( 'cenital-16mm', 'Nuggets de hielo seco de 16 mm vistos desde arriba' ), array( 'cenital-caja-nuggets', 'Caja EPS abierta con nuggets de 16 mm, vista cenital' ) ),
	),
	'formato' => 'Formato',
	'formatos' => array(
		'3mm'  => array( 'Pellets de 3 mm', 'Enfría más rápido. Más niebla.' ),
		'16mm' => array( 'Nuggets de 16 mm', 'Dura más. Transporte y muestras.' ),
	),
	'kilos'     => 'Kilos por caja',
	'cajas'     => 'Cajas',
	'menos'     => 'Una caja menos',
	'mas'       => 'Una caja más',
	'cp'        => 'Tu código postal',
	'cp_ph'     => '08301',
	// Línea bajo el campo: ayuda y estado (una línea corta)
	'cp_ayuda'     => 'Para ver qué día te llega',
	'cp_buscando'  => 'Buscando tu zona…',
	'cp_correcto'  => 'Código postal correcto',
	'cp_fuera'     => 'Fuera de la península',
	'cp_medida'    => 'Envío a medida',
	'cp_no_valido' => 'Código postal no válido',
	'cp_corto'     => 'Faltan cifras: son 5',
	// Línea de entrega con el código postal (%1$s = "08301 · Barcelona", %2$s = fecha); "medida" termina en un enlace a WhatsApp; "fuera", no (no se envía)
	'llega_cp_ok'     => '%1$s — llega el %2$s antes de las 14:00',
	'llega_cp_sabado' => 'O el %1$s antes de las 13:00, según zona, con precio de sábado', // sin %2$s desde el 11/11/2026: el precio de sábado depende del destino y los kilos y se ve al pagar
	'llega_cp_fuera'  => 'solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.',
	'llega_cp_medida' => 'más de 150 kg fuera de la provincia de Barcelona: pídelo por',
	'llega_cp_error'  => 'El código postal %s no existe en España. Revisa las 5 cifras.',
	'llega'     => 'Llega el %s antes de las 14:00',
	'llega_hoy' => 'Si pides antes de las 12:00, llega el %s antes de las 14:00',
	'fuera'     => 'Solo enviamos a la península: no a Baleares, Canarias, Ceuta ni Melilla.',
	'precio'    => 'Hielo seco',
	'envio'     => 'Envío',
	'total'     => 'Total',
	'iva'       => 'IVA incluido',
	'boton'     => 'Hacer mi pedido',
	'recogida'  => 'O recógelo en Mataró, de lunes a sábado: {pk} kg por {recogida:pk} y 10 kg por {recogida:10}, IVA incluido. El sábado, +{sabado_recogida}.{sabado_libre} En efectivo y con el importe exacto.',
	// Con el pack elegido (producto.js): %1$s precio de recogida, %2$s lo que se ahorra frente al envío (estrategia/VENTAS-PSICOLOGIA.md, 5.2)
	'recoge_ahorro' => 'O recógelo en Mataró: %1$s en efectivo, te ahorras %2$s. De lunes a sábado; el sábado, +{sabado_recogida}. Con el importe exacto.',
	// Junto al botón (movimiento.js, data-corte): solo antes del corte de las 12:00 (estrategia/VENTAS-PSICOLOGIA.md, 2.1)
	'corte'     => array( 'antes' => 'Para recibirlo el %s, pide antes de las 12:00.', 'quedan' => 'Quedan %s.', 'despues' => '' ),
	// Aviso de puente (movimiento.js, data-puente): solo si en los próximos 10 días hay días sin reparto por festivo (VENTAS-PSICOLOGIA, 2.2).
	// %1$s los días, %2$s el día de pedirlo, %3$s el día que llega (o la siguiente entrega)
	'puente'    => array(
		'marca'   => 'Festivo',
		'dias'    => array( 'el %s', 'el %1$s y el %2$s', 'del %1$s al %2$s' ),
		'antes'   => '%1$s no hay reparto. Para tenerlo antes, pide el %2$s antes de las 12:00 y llega el %3$s. La recogida en Mataró sí abre.',
		'hoy'     => '%1$s no hay reparto. Para tenerlo antes, pide hoy antes de las 12:00 y llega el %3$s. La recogida en Mataró sí abre.',
		'despues' => '%1$s no hay reparto. La siguiente entrega es el %3$s. La recogida en Mataró sí abre.',
	),
	'max'       => 'Hasta 250 kg por pedido. ¿Necesitas más? Escríbenos y te damos precio y día.',
	'medida'    => 'Más de 150 kg fuera de la provincia de Barcelona: ese envío se prepara a medida. Escríbenos por',
	'confianza' => array( 'Tarjeta, Apple Pay o Google Pay', 'Factura para empresas', 'Sin crear cuenta' ),
	// Información de seguridad del producto junto al botón de compra (Reglamento (UE) 2023/988, art. 19)
	'gpsr' => array(
		'titulo' => 'Fabricante y seguridad del producto',
		'filas'  => array(
			array( 'Fabricante', 'INDUNOVA IMS S.L. (marca DryIcePack) · Ronda Alfonso X El Sabio 10, 2º 1ª, 08301 Mataró · info@dryicepack.es' ),
			array( 'Producto', 'Dióxido de carbono sólido (hielo seco), UN 1845, en pellets de 3 mm o nuggets de 16 mm, en caja EPS.' ),
			array( 'Advertencias', 'A −78,5 °C quema la piel: guantes térmicos o pinzas. Solo en sitios ventilados: el CO₂ desplaza el oxígeno. Nunca en un recipiente cerrado. No se come ni se bebe. Fuera del alcance de niños y mascotas. En coche, en el maletero y ventilando.' ),
		),
		'enlace' => 'Normas de seguridad y ficha de datos',
	),
	'caja' => array(
		'titulo' => 'Así llega|<em>a tu puerta.</em>',
		'capas'  => array(
			array( 'Tapa de EPS', 'Encaja sin cerrar hermético: el gas sale poco a poco.' ),
			array( 'Hielo seco', 'Pellets de 3 mm o nuggets de 16 mm, a −78,5 °C.' ),
			array( 'Caja de EPS de 40 mm', 'Paredes gruesas de poliestireno: aíslan y hacen que el hielo dure más.' ),
		),
	),
	'comparar' => array(
		'titulo' => '¿3 mm o 16 mm?|<em>Arrastra</em> y compara.',
		'izq'    => array( '3 mm', 'Más superficie: enfría antes y hace más niebla. Para cócteles, efectos y procesos.' ),
		'der'    => array( '16 mm', 'Más masa por pieza: aguanta más horas en la caja. Para transporte, muestras y cadena de frío.' ),
		'rango'  => 'Deslizar para comparar 3 mm y 16 mm',
	),
	'cuanto' => array(
		'titulo' => '¿Cuánto|<em>necesito?</em>',
		'texto'  => 'Para niebla, estas cantidades sirven de referencia. ¿Es para transporte o muestras? Dinos cuántas horas y qué volumen, y te lo calculamos.',
		'casos'  => array(
			array( '{pk} kg', 'En casa', 'Un caldero, una ponchera con doble recipiente, una fiesta pequeña.' ),
			array( '10 kg', 'Fiesta grande', 'Niebla por el suelo en varias tandas durante la noche.' ),
			array( '15–20 kg', 'Bar o evento', 'Servicio largo, escenario o varias barras.' ),
		),
		'calcula' => 'Pregúntanos por WhatsApp',
		'seguridad' => 'Antes de usarlo, mira las seis reglas de seguridad',
	),
	'tarifa' => array(
		'titulo' => '¿Cuánto cuesta|<em>el envío?</em>',
		'texto'  => 'La tarifa de la mensajería para la entrega antes de las 14:00, igual en toda la península. Se calcula con el peso del hielo más 1 kg por caja, y la ves antes de pagar. La entrega antes de las 12:00 o de las 10:00, y la del sábado, tienen su precio según el destino y los kilos: lo ves al elegir el día y la hora.',
		'filas'  => array( array( 'Hasta 2 kg', 8.53 ), array( 'Hasta 5 kg', 10.32 ), array( 'Hasta 10 kg', 13.19 ), array( 'Cada kg de más', 1.12 ) ),
		// 11/11/2026: sin fila de sábado. Ya no es un suplemento fijo: depende del destino y los kilos (se ve al elegir el día)
		'extra'  => array( array( 'Recogida en Mataró, sin envío', 0 ) ),
		'nota'   => 'Precios con IVA incluido. La recogida se paga en efectivo y con el importe exacto.',
		'gratis' => '0 €', // fila de la recogida en el talón de envío: no hay coste de envío (el hielo se paga a precio de recogida)
		'enlace' => 'Plazos de entrega, festivos y recogida',
		'zonas'  => 'Envío a toda la península',
	),
	'faq' => array(
		'titulo'   => 'Antes de pagar',
		'empresas' => 'Cuenta de empresa con factura mensual',
		'lista'    => array(
			array( '¿Cuándo llega?', 'Si pides antes de las 12:00 de lunes a jueves, sale ese mismo día y llega al día siguiente antes de las 14:00. Si pides el viernes antes de las 12:00, llega el sábado antes de las 13:00 (según zona, con precio de sábado) o el martes. No repartimos en domingo ni lunes. El día lo eliges al pagar.' ),
			array( '¿Puedo pedir para otro día?', 'Sí: al pagar eliges cualquier día de los próximos 90 con reparto en tu zona, de martes a sábado (el sábado, según zona y con precio de sábado). Pídelo para el día que lo vas a usar: el hielo seco se va sublimando con las horas.' ),
			// Medición propia del 10–11/11/2026 (guía «cuánto dura»). [PENDIENTE: actualizar la frase de 3 kg cuando el pack pequeño sea de 5 kg y se mida su caja.]
			array( '¿Llega el mismo peso que pido?', 'No del todo: el hielo seco se sublima desde que se prepara, también con la caja cerrada. Lo hemos medido en nuestra nave, con las cajas cerradas: en 29 horas, la caja pequeña perdió 1,8 kg, la grande (packs de 15 y 20 kg) 4,1 kg y la de 30 kg 5,7 kg. Si pides 3 kg, te llegan unos 2 kg. Con calor o abriendo la caja se gasta antes. Pídelo para el día que lo vas a usar y, si lo necesitas justo, elige un pack más.' ),
			array( '¿Y si necesito factura?', 'Si eres particular y el pedido es de hasta 400 € (IVA incluido), te llega la factura simplificada por email desde info@dryicepack.es, sin pedirla. Si compras para una empresa o como autónomo, marca la casilla al pagar y pon la razón social y el NIF: recibes la factura completa. Si se te olvida, te enviamos un enlace para pedirla.' ),
			array( '¿Puedo devolverlo?', 'El hielo seco se sublima desde que sale, así que no tiene derecho de desistimiento. Sí tiene garantía legal: si llega mal, te lo reponemos o te devolvemos el dinero, sin coste. Avísanos el mismo día, con fotos si puedes, y lo reponemos antes.' ),
			array( '¿Cómo lo guardo al recibirlo?', 'Con guantes térmicos, nunca con la mano. En su caja, con la tapa puesta pero sin precintar, en un sitio ventilado y fuera del alcance de niños y mascotas. Nunca en el congelador ni en un recipiente hermético: la presión puede reventarlo. No se come ni se bebe. Y si lo llevas en coche, en el maletero y ventilando.' ),
		),
	),
	'barra' => 'Total',
	// Rediseño 14/10/2026 (solo presentación): nombre del carrusel de fotos, foto de cerca que sustituye en la galería a la de la caja
	// (la caja lateral pasa a «Así llega») y texto alternativo de la caja lateral de cada formato
	'galeria_t'     => 'Fotos del formato elegido',
	'galeria_macro' => array(
		'3mm'  => array( 'pellets-ia', 'Pellets de hielo seco de 3 mm, de cerca' ),
		'16mm' => array( 'nuggets-ia', 'Nuggets de hielo seco de 16 mm, de cerca' ),
	),
	'caja_foto'     => array(
		'3mm'  => 'Caja EPS con pellets de 3 mm y la tapa apoyada',
		'16mm' => 'Caja EPS con nuggets de 16 mm y la tapa apoyada',
	),
);
