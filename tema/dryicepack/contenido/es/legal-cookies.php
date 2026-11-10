<?php
/* Política de cookies · castellano. Refleja las cookies que usan el tema, el plugin, WooCommerce, WooPayments (Stripe y Sift)
   y Google Analytics 4, que solo carga el aviso de cookies del plugin tras aceptar (la etiqueta de Site Kit se desactiva).
   [PENDIENTE: duración real de __ssid (Sift) y si Stripe muestra hCaptcha: comprobarlo en el checkout de producción.] */
return array(
	'seo'        => array(
		'titulo'      => 'Política de cookies | DryIcePack',
		'descripcion' => 'Qué cookies usa dryicepack.es: técnicas para el carrito y el pago, y de análisis de Google Analytics solo si las aceptas. Cómo cambiar tu elección.',
	),
	'titulo'     => 'Política de cookies',
	'actualizado'=> 'Última actualización: 9 de noviembre de 2026',
	'secciones'  => array(
		array( 'Quién las usa', '<p>Esta web y su aviso de cookies son de INDUNOVA IMS S.L. (DryIcePack) · NIF B66800103 · Ronda Alfonso X El Sabio, 10, 2º 1ª, 08301 Mataró (Barcelona) · info@dryicepack.es.</p>' ),
		array( 'Qué son', '<p>Las cookies son pequeños archivos que la web guarda en tu navegador. Algunas son imprescindibles para que la tienda funcione; otras solo se usan si las aceptas.</p>' ),
		array( 'Cookies que usamos', '<table><thead><tr><th>Cookie</th><th>Para qué</th><th>Tipo</th><th>Duración</th></tr></thead><tbody>
<tr><td>wp_woocommerce_session_*</td><td>Recordar tu carrito</td><td>Técnica, propia</td><td>2 días</td></tr>
<tr><td>woocommerce_cart_hash, woocommerce_items_in_cart</td><td>Saber si el carrito ha cambiado</td><td>Técnica, propia</td><td>Sesión</td></tr>
<tr><td>dip_idioma</td><td>Recordar el idioma que has elegido</td><td>Técnica, propia</td><td>90 días</td></tr>
<tr><td>dip_consent</td><td>Recordar tu elección sobre cookies</td><td>Técnica, propia</td><td>180 días</td></tr>
<tr><td>__stripe_mid, __stripe_sid</td><td>Prevenir el fraude en el pago con tarjeta</td><td>Técnica, de terceros (Stripe)</td><td>1 año y 30 minutos</td></tr>
<tr><td>__ssid</td><td>Prevenir el fraude en el pago. Solo en la página de pago</td><td>Técnica, de terceros (Sift, el servicio antifraude de WooPayments)</td><td>Persistente: la fija Sift</td></tr>
<tr><td>_ga, _ga_*</td><td>Estadísticas de uso de la web (Google Analytics 4)</td><td>Análisis, de terceros (Google). Solo si aceptas</td><td>2 años</td></tr>
</tbody></table><p>En la página de pago, la pasarela también carga los scripts de Stripe y de Sift (cdn.sift.com y hexagon-analytics.com) para comprobar que el pago es legítimo. Si detecta un intento sospechoso, Stripe puede mostrar una comprobación de hCaptcha, que usa sus propias cookies técnicas mientras dura.</p><p>No usamos cookies de publicidad ni de redes sociales. Google Analytics solo se carga si aceptas las cookies de análisis.</p>' ),
		array( 'Cómo cambiar tu elección', '<p>Puedes aceptar o rechazar las cookies de análisis en el aviso que aparece al entrar y cambiar de opinión cuando quieras con el enlace «Configurar cookies» del pie de página, también en la página de pago. Rechazarlas no afecta a la compra.</p><p>También puedes borrar las cookies desde la configuración de tu navegador.</p>' ),
		array( 'Más información', '<p>Para cualquier duda, escribe a info@dryicepack.es. Quién más trata tus datos y cómo ejercer tus derechos, en la política de privacidad.</p>' ),
	),
);
