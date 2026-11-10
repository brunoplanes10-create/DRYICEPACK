<?php
/* Política de cookies · inglés. Refleja las cookies que usan el tema, el plugin, WooCommerce, WooPayments (Stripe y Sift)
   y Google Analytics 4, que solo carga el aviso de cookies del plugin tras aceptar (la etiqueta de Site Kit se desactiva).
   [PENDIENTE: duración real de __ssid (Sift) y si Stripe muestra hCaptcha: comprobarlo en el checkout de producción.] */
return array(
	'seo'        => array(
		'titulo'      => 'Cookie policy | DryIcePack',
		'descripcion' => 'Which cookies dryicepack.es uses: technical ones for the basket and payment, and Google Analytics only if you accept them. How to change your choice.',
	),
	'titulo'     => 'Cookie policy',
	'actualizado'=> 'Last updated: 9 November 2026',
	'secciones'  => array(
		array( 'Who uses them', '<p>This website and its cookie notice belong to INDUNOVA IMS S.L. (DryIcePack) · Tax ID (NIF) B66800103 · Ronda Alfonso X El Sabio, 10, 2º 1ª, 08301 Mataró (Barcelona) · info@dryicepack.es.</p>' ),
		array( 'What they are', '<p>Cookies are small files that the website stores in your browser. Some are essential for the shop to work; others are only used if you accept them.</p>' ),
		array( 'Cookies we use', '<table><thead><tr><th>Cookie</th><th>Purpose</th><th>Type</th><th>Duration</th></tr></thead><tbody>
<tr><td>wp_woocommerce_session_*</td><td>Remember your basket</td><td>Technical, first-party</td><td>2 days</td></tr>
<tr><td>woocommerce_cart_hash, woocommerce_items_in_cart</td><td>Detect whether your basket has changed</td><td>Technical, first-party</td><td>Session</td></tr>
<tr><td>dip_idioma</td><td>Remember the language you chose</td><td>Technical, first-party</td><td>90 days</td></tr>
<tr><td>dip_consent</td><td>Remember your cookie choice</td><td>Technical, first-party</td><td>180 days</td></tr>
<tr><td>__stripe_mid, __stripe_sid</td><td>Prevent card payment fraud</td><td>Technical, third-party (Stripe)</td><td>1 year and 30 minutes</td></tr>
<tr><td>__ssid</td><td>Prevent payment fraud. Only on the checkout page</td><td>Technical, third-party (Sift, the WooPayments fraud prevention service)</td><td>Persistent: set by Sift</td></tr>
<tr><td>_ga, _ga_*</td><td>Website usage statistics (Google Analytics 4)</td><td>Analytics, third-party (Google). Only if you accept</td><td>2 years</td></tr>
</tbody></table><p>On the checkout page, the payment gateway also loads Stripe and Sift scripts (cdn.sift.com and hexagon-analytics.com) to check that the payment is genuine. If it detects a suspicious attempt, Stripe may show an hCaptcha check, which uses its own technical cookies while it lasts.</p><p>We do not use advertising or social media cookies. Google Analytics only loads if you accept analytics cookies.</p>' ),
		array( 'How to change your choice', '<p>You can accept or reject analytics cookies in the notice that appears when you arrive, and change your mind at any time with the ‘Cookie settings’ link in the footer, also on the checkout page. Rejecting them does not affect your purchase.</p><p>You can also delete cookies in your browser settings.</p>' ),
		array( 'More information', '<p>If you have any questions, write to info@dryicepack.es. Who else processes your data and how to exercise your rights are explained in the privacy policy.</p>' ),
	),
);
