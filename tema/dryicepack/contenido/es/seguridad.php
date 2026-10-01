<?php
/* Seguridad del hielo seco · castellano */
return array(
	'menu' => 'Seguridad del hielo seco',
	'seo'  => array(
		'titulo'      => '¿El hielo seco es tóxico? Seguridad y ficha | DryIcePack',
		'descripcion' => 'El hielo seco no es tóxico ni inflamable, pero a −78,5 °C quema y, sin ventilación, desplaza el oxígeno. Normas de uso, primeros auxilios y ficha de datos.',
	),
	'migas' => array( 'Inicio', 'Seguridad' ),
	'hero'  => array(
		'titulo'    => '¿El hielo seco|<em>es tóxico?</em>',
		'respuesta' => 'No es tóxico ni inflamable. Tiene tres riesgos, y los tres se evitan: quema la piel porque está a −78,5 °C, en un sitio cerrado el CO₂ desplaza el oxígeno, y en un envase hermético la presión puede reventarlo.',
		'etiqueta'  => array( 'Hielo seco', 'CO₂ sólido', '−78,5 °C', 'UN 1845' ),
	),
	'riesgos' => array(
		'titulo' => 'Tres riesgos,|<em>tres gestos.</em>',
		'lista'  => array(
			array( 'Contacto con la piel', 'Quemadura por frío en segundos.', 'Guantes térmicos y pinzas. Nunca con la mano.' ),
			array( 'Sitio cerrado', 'El CO₂ se acumula abajo y desplaza el oxígeno: mareo, dolor de cabeza, ahogo.', 'Ventilar. Nada de sótanos, cámaras cerradas ni coches sin ventilación.' ),
			array( 'Envase cerrado', 'Al pasar a gas, el volumen se multiplica y la presión sube hasta reventar el envase.', 'Su caja de EPS con la tapa sin precintar. Nunca botellas, tarteras ni neveras herméticas.' ),
		),
		'causa'  => 'Qué pasa',
		'gesto'  => 'Qué hacer',
	),
	'haz' => array(
		'titulo' => 'Haz esto',
		'lista'  => array( 'Guantes térmicos, pinzas y, si manipulas mucho, gafas.', 'Espacio ventilado: puertas o ventanas abiertas.', 'Guárdalo en su caja de EPS, en un sitio fresco.', 'En bebidas, siempre con doble recipiente: el hielo fuera de la copa.', 'Deja que el sobrante se sublime al aire libre.' ),
	),
	'evita' => array(
		'titulo' => 'Evita esto',
		'lista'  => array( 'Tocarlo con la mano o meterlo en la boca.', 'Pellets dentro de la copa que se va a beber.', 'Recipientes cerrados, el congelador o la nevera.', 'Llevarlo en el habitáculo del coche sin ventilar.', 'Tirarlo al fregadero, al váter o a una papelera cerrada.' ),
	),
	'auxilios' => array(
		'titulo' => 'Si pasa algo',
		'lista'  => array(
			array( 'Quemadura por frío', 'Templa la zona con agua tibia, no caliente, y no frotes. Si hay ampollas o la piel cambia de color, ve al médico.' ),
			array( 'Mareo o falta de aire', 'Sal al aire libre y ventila el espacio. Si no mejora o alguien pierde el conocimiento, llama al 112.' ),
			array( 'Alguien lo ha tragado', 'Llama al 112 o al Servicio de Información Toxicológica (91 562 04 20).' ),
		),
	),
	'fds' => array(
		'titulo' => 'Ficha de datos de seguridad',
		'texto'  => 'La documentación oficial del producto para prevención de riesgos, calidad y auditorías: identificación, peligros, primeros auxilios, manipulación, protección y transporte.',
		'boton'  => 'Descargar la ficha (PDF)',
		'archivo'=> '/wp-content/uploads/2026/01/CO2-SOLIDO-FICHA-DE-DATOS-DE-SEGURIDAD-DRYICEPACK-1.pdf',
		'meta'   => 'PDF · castellano',
	),
	'faq' => array(
		'titulo' => 'Más preguntas de seguridad',
		'lista'  => array(
			array( '¿Es peligroso respirar el "humo"?', 'Lo que ves no es humo: es vapor de agua del aire condensado por el frío, junto con CO₂. En un sitio ventilado no pasa nada; el riesgo aparece en espacios pequeños y cerrados.' ),
			array( '¿Se puede usar en bebidas y cócteles?', 'Para el efecto de niebla, sí, con doble recipiente: el hielo seco va en el recipiente exterior y la bebida en otro. Nunca un pellet dentro de la copa que se va a beber.' ),
			array( '¿Puede explotar?', 'El hielo seco no explota. Lo que puede reventar es un recipiente hermético por la presión del gas. Por eso la caja no se precinta.' ),
			array( '¿Es seguro con niños en casa?', 'Sí, si lo manipula un adulto y queda fuera de su alcance. Para la niebla, el hielo seco no debe estar al alcance del público.' ),
			array( '¿Cómo se lleva en el coche?', 'En el maletero, dentro de su caja y con algo de ventilación. En cantidades grandes, nunca en el habitáculo cerrado.' ),
		),
	),
);
