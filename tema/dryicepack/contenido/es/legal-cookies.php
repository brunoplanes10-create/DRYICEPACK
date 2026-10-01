<?php
/* Política de cookies · castellano. Refleja las cookies que usan el tema, el plugin, WooCommerce, WooPayments y Google Analytics 4. */
return array(
	'seo'        => array(
		'titulo'      => 'Política de cookies | DryIcePack',
		'descripcion' => 'Qué cookies usa dryicepack.es: técnicas para el carrito y el pago, y de análisis de Google Analytics solo si las aceptas. Cómo cambiar tu elección.',
	),
	'titulo'     => 'Política de cookies',
	'actualizado'=> 'Última actualización: 30 de septiembre de 2026',
	'secciones'  => array(
		array( 'Qué son', '<p>Las cookies son pequeños archivos que la web guarda en tu navegador. Algunas son imprescindibles para que la tienda funcione; otras solo se usan si las aceptas.</p>' ),
		array( 'Cookies que usamos', '<table><thead><tr><th>Cookie</th><th>Para qué</th><th>Tipo</th><th>Duración</th></tr></thead><tbody>
<tr><td>wp_woocommerce_session_*</td><td>Recordar tu carrito</td><td>Técnica, propia</td><td>2 días</td></tr>
<tr><td>woocommerce_cart_hash, woocommerce_items_in_cart</td><td>Saber si el carrito ha cambiado</td><td>Técnica, propia</td><td>Sesión</td></tr>
<tr><td>dip_idioma</td><td>Recordar el idioma que has elegido</td><td>Técnica, propia</td><td>90 días</td></tr>
<tr><td>dip_consent</td><td>Recordar tu elección sobre cookies</td><td>Técnica, propia</td><td>180 días</td></tr>
<tr><td>__stripe_mid, __stripe_sid</td><td>Prevenir el fraude en el pago con tarjeta</td><td>Técnica, de terceros (Stripe)</td><td>1 año y 30 minutos</td></tr>
<tr><td>_ga, _ga_*</td><td>Estadísticas de uso de la web (Google Analytics 4)</td><td>Análisis, de terceros (Google). Solo si aceptas</td><td>2 años</td></tr>
</tbody></table>' ),
		array( 'Cómo cambiar tu elección', '<p>Puedes aceptar o rechazar las cookies de análisis en el aviso que aparece al entrar y cambiar de opinión cuando quieras con el enlace «Configurar cookies» del pie de página. Rechazarlas no afecta a la compra.</p><p>También puedes borrar las cookies desde la configuración de tu navegador.</p>' ),
		array( 'Más información', '<p>Responsable: INDUNOVA IMS S.L. Para cualquier duda, escribe a info@dryicepack.es. Más detalles sobre el tratamiento de datos en la política de privacidad.</p>' ),
	),
);
