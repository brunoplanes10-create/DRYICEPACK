<?php
/*
 * Hielo seco para limpieza criogénica · castellano · usted (10/11/2026)
 * Ficha 4 de estrategia/SEO-CONTENIDOS.md. Página de uso B2B, hermana de Industria.
 * Palabra principal: «hielo seco para limpieza criogénica» (+ pellets 3 mm, para máquina de limpieza, limpiar motores y moldes).
 * No ataca «limpieza criogénica» a secas: es el servicio de INDUNOVA (indunova.es). Las dos webs se enlazan.
 * Dato del propietario (10/11/2026): los pellets de 3 mm sirven para máquinas de limpieza criogénica.
 *
 * [PENDIENTE: consumo típico de una máquina de limpieza criogénica en kg por hora (dato de INDUNOVA). Hasta tenerlo,
 *  la pregunta del consumo no da cifra.]
 * [PENDIENTE: ¿el pellet sale recién hecho el mismo día del envío? Si el propietario lo confirma, añadirlo a «Cómo llega»
 *  y a la meta (la ficha decía «preparados el mismo día que salen»; hasta entonces no se dice).]
 * [PENDIENTE: ¿algún cliente con máquina propia que se pueda citar? Si lo hay, placa de cliente como en Industria.]
 */
return array(
	'menu' => 'Hielo seco para limpieza criogénica',
	'seo'  => array(
		'titulo'      => 'Hielo seco para limpieza criogénica: 3 mm | DryIcePack',
		'descripcion' => 'Pellets de hielo seco de 3 mm para máquinas de limpieza criogénica, en caja de EPS de 40 mm. Pida antes de las 12:00 y llegan al día siguiente.',
	),
	'migas' => array( 'Inicio', 'Usos', 'Limpieza criogénica' ),
	'hero'  => array(
		'titulo' => 'Hielo seco para|limpieza criogénica:|<em>pellets de 3 mm.</em>',
		'texto'  => 'Para empresas con máquina propia, contratistas de limpieza industrial, talleres y detailing. Pida antes de las 12:00 y llega al día siguiente antes de las 14:00, en caja de EPS de 40 mm.',
		'boton'  => 'Pedir pellets',
		'enlace' => 'Suministro programado',
		// Quien busca el servicio, una máquina o más información va a INDUNOVA (decisión del propietario, 11/11/2026)
		'indunova' => '¿Busca el servicio o una máquina? INDUNOVA',
		'alt'    => 'Limpieza criogénica de un engranaje con una pistola de chorreo de hielo seco',
		// Placa de ensayo (decorativa: los mismos datos están en el texto)
		'placa'  => array( array( 'Calibre', '3 mm' ), array( 'Temperatura', '−78,5 °C' ), array( 'Al chocar', 'pasa a gas' ) ),
	),
	'calibre' => array(
		'titulo' => '¿Por qué pellets|<em>de 3 mm?</em>',
		'texto'  => array(
			'<strong>Es el calibre habitual de las máquinas de limpieza criogénica.</strong> El pellet entra por la tolva, el aire a presión lo lanza por la pistola y, al chocar, pasa a gas: la suciedad se suelta y el hielo seco no deja agua ni residuo.',
			'Se usa para limpiar moldes, motores, líneas de producción alimentaria, maquinaria de imprenta, vanos motor y bajos de coche. Compruebe en el manual de su máquina qué calibre admite; si tiene dudas, díganos el modelo.',
		),
		'no'     => array( '16 mm', 'Para la máquina, pida 3 mm. Los nuggets de 16 mm están pensados para mantener el frío en el transporte.' ),
		'foto'   => 'Pellets de hielo seco de 3 mm en su caja de EPS, vistos desde arriba',
		'dibujo' => 'Dibujo de tres pellets de hielo seco con la cota del diámetro: 3 mm',
		'cota'   => 'Ø 3 mm',
	),
	'llega' => array(
		'titulo' => 'Pida hoy,|<em>limpie mañana.</em>',
		'texto'  => 'De lunes a jueves, antes de las 12:00: sale ese día y llega al día siguiente antes de las 14:00. O elija al pagar el día de la limpieza.',
		// cifra, unidad, título, texto
		'hitos'  => array(
			array( '12:00', '', 'Pida antes', 'Elija pellets de 3 mm y los kilos.' ),
			array( '40', 'mm', 'Sale ese día', 'En caja de EPS de 40 mm, con la tapa sin precintar.' ),
			array( '14:00', '', 'Llega mañana antes', 'A su nave, su taller o la obra, en la península.' ),
		),
		'notas'  => array(
			array( 'Sábado', 'Antes de las 13:00, según zona, y como mucho 80 kg por pedido, contando las cajas. Precio de sábado: lo ve al elegir el día. Pídalo antes de las 12:00 del viernes.' ),
			array( 'Otro día', 'Cualquiera de los próximos 90 con reparto en su zona. Festivos y sábados, en <a href="' . esc_url( dipt_url( 'envios' ) ) . '">envíos y plazos de entrega</a>.' ),
			array( 'Recogida', 'En la nave de Mataró, de lunes a sábado (el sábado, +{sabado_recogida}). Solo en efectivo y con el importe exacto.' ),
		),
	),
	'kilos' => array(
		'titulo'  => '¿Cuántos kilos|<em>por pedido?</em>',
		'texto'   => 'Los packs de la tienda, en una caja o en varias: hasta 250 kg por pedido. El mismo precio en 3 y 16 mm, con la caja de EPS incluida.',
		'kg'      => 'kg',
		'mas_iva' => '+ IVA',
		'con_iva' => '%s con IVA',
		'kilo'    => 'el kilo + IVA',
		'pedir'   => 'Pedir en pellets de 3 mm:',
		'envio'   => 'Más el envío, según el peso: lo ve antes de pagar. Si lo recoge en Mataró, sin envío.',
		'limites' => array(
			array( '150 kg', 'Más de 150 kg fuera de la provincia de Barcelona: el envío se prepara a medida y sale más caro. Escríbanos por <a href="' . esc_url( dipt_whatsapp_url( 'Hola, necesito más de 150 kg de pellets de 3 mm para limpieza criogénica. Código postal:' ) ) . '">WhatsApp</a>, llámenos al <a href="' . esc_attr( dipt_contacto()['telefono_href'] ) . '">' . esc_html( dipt_contacto()['telefono'] ) . '</a> o escriba a <a href="mailto:' . esc_attr( dipt_contacto()['email'] ) . '">' . esc_html( dipt_contacto()['email'] ) . '</a>.' ),
			array( '250 kg', 'Máximo por pedido online. ¿Más? Le damos precio y día, normalmente el mismo día laborable.' ),
		),
	),
	// Franja que separa kilos y suministro: la pasada la deja limpia al entrar (decorativa)
	'franja' => array( 'Sin agua', 'Sin productos químicos', 'El CO₂ pasa a gas' ),
	'suministro' => array(
		'titulo'     => 'Suministro programado|<em>para su máquina.</em>',
		'texto'      => 'Si limpia cada semana, cada quince días o cada mes, le llevamos los pellets el día acordado, sin pedirlos cada vez.',
		'ritmo_t'    => 'Ritmo de entrega',
		'ritmos'     => array( 1 => 'Semanal', 2 => 'Quincenal', 4 => 'Mensual' ),
		'dias'       => array( 'L', 'M', 'X', 'J', 'V', 'S' ),
		// %s: semanal, quincenal o mensual (en minúscula). Describe el calendario, que es un ejemplo.
		'cal_alt'    => 'Ejemplo de calendario con entrega %s, los miércoles.',
		'cal_nota'   => 'Ejemplo: entrega los miércoles. El día lo acordamos con usted.',
		'condiciones' => array(
			array( 'Contrato de suministro', 'Kilos, ritmo y precio, por escrito.' ),
			array( 'Domiciliación SEPA', 'Si puede ser; si no, transferencia.' ),
			array( 'Una factura al mes', 'A 30 días, con todos los pedidos del mes.' ),
			array( 'Mejor precio', 'Descuento sobre tarifa según volumen y frecuencia.' ),
		),
		'boton'      => 'Abrir cuenta',
		'enlace'     => 'Cómo funciona la cuenta de empresa',
	),
	'indunova' => array(
		'titulo' => '¿Con máquina|<em>o sin ella?</em>',
		'ramas'  => array(
			array( '¿Tiene máquina?', 'Le servimos los pellets.', 'De 3 mm, en caja de EPS de 40 mm, para el día que limpia.' ),
			array( '¿No tiene máquina?', 'INDUNOVA hace el servicio.', 'La empresa de la que forma parte DryIcePack limpia en sus instalaciones, normalmente sin desmontar la maquinaria. También alquila y vende máquinas. Para el servicio, una máquina o más información, hable con INDUNOVA.' ),
		),
		'boton'  => 'Ir a INDUNOVA',
		'enlace' => 'Servicio de limpieza criogénica de INDUNOVA',
		'url'    => 'https://indunova.es/servicio-de-limpieza-criogenica/',
		'alt'    => 'Operario con gafas y guantes térmicos cargando pellets de hielo seco con una pala',
	),
	'seguridad' => array(
		'titulo' => 'Seguridad en la|<em>limpieza criogénica.</em>',
		'texto'  => 'Al limpiar, todo el hielo seco pasa a gas. El CO₂ pesa más que el aire: en un sitio cerrado se acumula abajo y desplaza el oxígeno.',
		'reglas' => array(
			array( 'ventilar', 'Ventile el espacio', 'Puertas abiertas o extracción. Nunca en fosos, sótanos ni cámaras cerradas sin ventilar.' ),
			array( 'guante', 'Guantes térmicos al cargar la tolva', 'A −78,5 °C quema la piel: con pala o pinzas, nunca con la mano.' ),
			array( 'gafas', 'Gafas y protección auditiva', 'Y el resto del equipo que indique el fabricante de su máquina.' ),
			array( 'hermetico', 'Nada hermético', 'Los pellets, en su caja con la tapa sin precintar: en un recipiente cerrado, la presión puede reventarlo.' ),
			array( 'furgoneta', 'En la furgoneta, en la zona de carga', 'Separada de la cabina y ventilando. En coche, en el maletero.' ),
			array( 'nino', 'Lejos de niños y mascotas', 'Y no se come ni se bebe.' ),
		),
		'corte'  => array( 'co2' => 'CO₂', 'puerta' => 'Puerta abierta', 'nave' => 'Nave, en corte' ),
		'enlace' => 'Normas de seguridad y ficha de datos del hielo seco',
	),
	'faq' => array(
		'titulo' => 'Preguntas de|<em>limpieza criogénica</em>',
		'lista'  => array(
			array( '¿Los pellets de 3 mm sirven para mi máquina?', 'Es el calibre habitual de las máquinas de limpieza criogénica. Aun así, compruebe en el manual de la suya qué calibre admite. Si tiene dudas, díganos el modelo por WhatsApp o por teléfono.' ),
			// [PENDIENTE: consumo típico en kg por hora, dato de INDUNOVA. Si lo da, se añade aquí como referencia, citando que depende de la máquina.]
			array( '¿Cuánto hielo seco gasta una máquina por hora?', 'Depende de la máquina, de la presión de trabajo y de lo que limpie: el consumo lo indica el fabricante. Díganos su máquina y cuántas horas va a limpiar, y le ayudamos a calcular el pedido.' ),
			array( '¿Puedo pedirlo para el día de la limpieza?', 'Sí. Al pagar elige cualquier día de los próximos 90 con reparto en su zona, de martes a sábado (el sábado, según zona, con precio de sábado y como mucho 80 kg por pedido, contando las cajas). Pídalo para ese día: el hielo seco se sublima con las horas.' ),
			array( '¿Cuánto aguantan los pellets en la caja?', 'Depende de la temperatura y de las veces que se abra. Los pellets de 3 mm se subliman antes que los nuggets: guárdelos en su caja, con la tapa sin precintar, y no la abra hasta cargar la tolva. Más detalles en <a href="' . esc_url( dipt_url( 'guia-cuanto-dura-el-hielo-seco' ) ) . '">cuánto dura el hielo seco y cómo guardarlo</a>.' ),
			array( '¿Pueden servir más de 150 kg?', 'En la web, hasta 250 kg por pedido. Fuera de la provincia de Barcelona, a partir de 150 kg el envío se prepara a medida: escríbanos por WhatsApp, llámenos o mándenos un email. ¿Más de 250 kg? Le damos precio y día, normalmente el mismo día laborable.' ),
			array( '¿Me hacen factura completa?', 'Sí. Al pagar, marque «Compro para una empresa o autónomo» y escriba la razón social y el NIF. Con <a href="' . esc_url( dipt_url( 'empresas' ) ) . '">cuenta de empresa</a>, una sola factura al mes, a 30 días.' ),
			array( '¿Hacen ustedes la limpieza?', 'DryIcePack vende el hielo seco. La limpieza la hace INDUNOVA, la empresa de la que forma parte DryIcePack, en sus instalaciones: <a href="https://indunova.es/servicio-de-limpieza-criogenica/">servicio de limpieza criogénica de INDUNOVA</a>.' ),
			array( '¿Alquilan o venden máquinas de limpieza criogénica?', 'DryIcePack vende el hielo seco. Las máquinas, en alquiler o en venta, las ofrece INDUNOVA, la empresa de la que forma parte DryIcePack: <a href="https://indunova.es/servicio-de-limpieza-criogenica/">limpieza criogénica y máquinas en INDUNOVA</a>. Allí le informan también del servicio.' ),
			array( '¿Y para enfriar piezas o montar por contracción?', 'Es otro uso, con los mismos pellets de 3 mm: lo explicamos en <a href="' . esc_url( dipt_url( 'industria' ) ) . '">hielo seco industrial para enfriar piezas y baterías</a>.' ),
		),
	),
	'final' => array(
		'titulo' => '¿Limpia mañana?|<em>Pida antes de las 12:00.</em>',
		'corte'  => array( 'Pida antes de las 12:00 y llega el %s antes de las 14:00', 'Quedan %s para pedir hoy', 'Si pide ahora, llega el %s antes de las 14:00' ),
	),
);
