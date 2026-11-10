<?php
/* Política de privacidad · castellano.
   9/11/2026: emails de seguimiento, «Mi pedido», reclamaciones, antifraude (Sift) y destinatarios reales (auditoría M9).
   [PENDIENTE: nombre del proveedor de envío de emails (el que usa WP Mail SMTP) para nombrarlo en «Quién más ve tus datos».]
   [PENDIENTE: revisar la fila de «Mi pedido» cuando el plugin publique la página (qué datos pide para consultarla).] */
return array(
	'seo'        => array(
		'titulo'      => 'Política de privacidad | DryIcePack',
		'descripcion' => 'Qué datos personales tratamos en dryicepack.es, para qué, durante cuánto tiempo y cómo ejercer tus derechos. Responsable: INDUNOVA IMS S.L., Mataró.',
	),
	'titulo'     => 'Política de privacidad',
	'actualizado'=> 'Última actualización: 9 de noviembre de 2026',
	'secciones'  => array(
		array( 'Responsable', '<ul><li><strong>INDUNOVA IMS S.L.</strong> · NIF B66800103</li><li>Ronda Alfonso X El Sabio, 10, 2º 1ª, 08301 Mataró (Barcelona)</li><li>info@dryicepack.es · 936 73 76 41</li></ul>' ),
		array( 'Qué datos tratamos y para qué', '<table><thead><tr><th>Para qué</th><th>Datos</th><th>Base legal</th><th>Cuánto tiempo</th></tr></thead><tbody>
<tr><td>Gestionar tu pedido, el envío y la recogida</td><td>Nombre, email, teléfono, dirección, datos del pedido y día de entrega</td><td>Ejecución del contrato</td><td>Mientras dure la relación y, después, los plazos legales</td></tr>
<tr><td>Avisarte por email de cómo va tu pedido: pedido recibido, va de camino (con el número de seguimiento de MRW), el día de la entrega, recordatorio de la recogida e incidencias</td><td>Nombre, email, teléfono, datos del pedido, día de entrega y número de seguimiento</td><td>Ejecución del contrato</td><td>Los mismos plazos que el pedido</td></tr>
<tr><td>Página «Mi pedido»: consultar el estado, el día de entrega y el seguimiento de tu pedido</td><td>Número de pedido, email, estado, día de entrega y número de seguimiento</td><td>Ejecución del contrato</td><td>Los mismos plazos que el pedido</td></tr>
<tr><td>Atender quejas y reclamaciones</td><td>Nombre, datos de contacto, pedido y lo que nos cuentes</td><td>Obligación legal (normativa de consumo)</td><td>Hasta resolverla y, después, mientras se pueda reclamar</td></tr>
<tr><td>Prevenir el fraude en el pago con tarjeta</td><td>Datos del navegador y del dispositivo, dirección IP y datos de la operación (no vemos ni guardamos el número de la tarjeta)</td><td>Interés legítimo (evitar pagos fraudulentos)</td><td>Los que fijan la pasarela de pago y su servicio antifraude</td></tr>
<tr><td>Emitir la factura</td><td>Razón social, NIF, dirección fiscal e importes</td><td>Obligación legal</td><td>6 años (Código de Comercio) y los plazos fiscales</td></tr>
<tr><td>Enviarte el enlace para pedir factura con tus datos</td><td>Email y número de pedido</td><td>Ejecución del contrato</td><td>Hasta 30 días después del pedido</td></tr>
<tr><td>Responder a formularios, llamadas y mensajes de WhatsApp</td><td>Nombre, email, teléfono y lo que nos cuentes</td><td>Consentimiento y medidas precontractuales</td><td>Hasta resolver la consulta y 1 año más</td></tr>
<tr><td>Cuentas de empresa</td><td>Datos de contacto y fiscales, precio acordado e historial de pedidos</td><td>Ejecución del contrato</td><td>Mientras la cuenta esté activa y los plazos legales</td></tr>
<tr><td>Recordatorio de reposición</td><td>Email e historial de pedidos</td><td>Consentimiento (se activa solo si lo pides y se desactiva con un clic)</td><td>Hasta que te des de baja</td></tr>
<tr><td>Pedirte tu opinión sobre el pedido: después de la entrega podemos enviarte un único email (como mucho uno cada 6 meses)</td><td>Nombre, email, día de entrega y kilos del pedido</td><td>Interés legítimo (relación de cliente). Puedes oponerte con el enlace de cada email o escribiendo a info@dryicepack.es</td><td>Mientras dure la relación de cliente. Si te opones, guardamos solo una huella de tu email, que no permite leerlo, para no volver a escribirte</td></tr>
<tr><td>Medir el uso de la web (Google Analytics)</td><td>Datos de navegación y dirección IP abreviada</td><td>Consentimiento (aviso de cookies)</td><td>Según la política de cookies</td></tr>
</tbody></table>' ),
		array( 'Quién más ve tus datos', '<p>No vendemos ni cedemos tus datos. Solo acceden los proveedores que necesitamos para prestar el servicio, con contrato de encargo de tratamiento:</p><ul><li>Alojamiento web (Arsys) y seguridad y entrega de la web (Cloudflare).</li><li>Pasarela de pago (WooPayments, de Automattic, que usa Stripe): procesa la tarjeta, Apple Pay y Google Pay. Para prevenir el fraude, la página de pago usa además Sift, el servicio antifraude de WooPayments.</li><li>MRW, la mensajería que entrega el pedido: nombre, dirección, teléfono y número de pedido.</li><li>El proveedor de envío de emails de la web, que entrega los correos del pedido y de los formularios.</li><li>Gestoría y aplicación de facturación.</li><li>WhatsApp (Meta), solo si nos escribes por WhatsApp: lo que nos envíes por ahí lo trata también WhatsApp, según sus condiciones.</li><li>Google (Analytics), solo si aceptas las cookies de análisis.</li></ul><p>Algunos de estos proveedores pueden tratar datos fuera del Espacio Económico Europeo con las garantías del RGPD (cláusulas contractuales tipo o decisiones de adecuación).</p>' ),
		array( 'Tus derechos', '<p>Puedes pedir acceso, rectificación, supresión, oposición, limitación y portabilidad de tus datos, y retirar tu consentimiento cuando quieras, escribiendo a info@dryicepack.es con una forma de identificarte.</p><p>Si crees que no hemos atendido bien tu solicitud, puedes reclamar ante la Agencia Española de Protección de Datos (www.aepd.es).</p>' ),
		array( 'Seguridad', '<p>Aplicamos medidas técnicas y organizativas adecuadas: conexión cifrada, accesos restringidos y copias de seguridad. No guardamos datos de tarjetas.</p>' ),
	),
);
