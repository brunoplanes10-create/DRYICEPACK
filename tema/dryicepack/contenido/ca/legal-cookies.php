<?php
/* Política de galetes · català. Reflecteix les galetes que fan servir el tema, el plugin, WooCommerce, WooPayments (Stripe i Sift)
   i Google Analytics 4, que només carrega l'avís de galetes del plugin després d'acceptar (l'etiqueta de Site Kit es desactiva).
   [PENDIENTE: durada real de __ssid (Sift) i si Stripe mostra hCaptcha: comprovar-ho al checkout de producció.] */
return array(
	'seo'        => array(
		'titulo'      => 'Política de galetes | DryIcePack',
		'descripcion' => 'Quines galetes fa servir dryicepack.es: tècniques per a la cistella i el pagament, i d\'anàlisi de Google Analytics només si les acceptes. Com canviar-ho.',
	),
	'titulo'     => 'Política de galetes',
	'actualizado'=> 'Darrera actualització: 9 de novembre de 2026',
	'secciones'  => array(
		array( 'Qui les fa servir', '<p>Aquest web i el seu avís de galetes són d\'INDUNOVA IMS S.L. (DryIcePack) · NIF B66800103 · Ronda Alfonso X El Sabio, 10, 2n 1a, 08301 Mataró (Barcelona) · info@dryicepack.es.</p>' ),
		array( 'Què són', '<p>Les galetes (cookies) són petits fitxers que el web desa al teu navegador. Algunes són imprescindibles perquè la botiga funcioni; d\'altres només es fan servir si les acceptes.</p>' ),
		array( 'Galetes que fem servir', '<table><thead><tr><th>Galeta</th><th>Per a què</th><th>Tipus</th><th>Durada</th></tr></thead><tbody>
<tr><td>wp_woocommerce_session_*</td><td>Recordar la teva cistella</td><td>Tècnica, pròpia</td><td>2 dies</td></tr>
<tr><td>woocommerce_cart_hash, woocommerce_items_in_cart</td><td>Saber si la cistella ha canviat</td><td>Tècnica, pròpia</td><td>Sessió</td></tr>
<tr><td>dip_idioma</td><td>Recordar l\'idioma que has triat</td><td>Tècnica, pròpia</td><td>90 dies</td></tr>
<tr><td>dip_consent</td><td>Recordar la teva elecció sobre les galetes</td><td>Tècnica, pròpia</td><td>180 dies</td></tr>
<tr><td>__stripe_mid, __stripe_sid</td><td>Prevenir el frau en el pagament amb targeta</td><td>Tècnica, de tercers (Stripe)</td><td>1 any i 30 minuts</td></tr>
<tr><td>__ssid</td><td>Prevenir el frau en el pagament. Només a la pàgina de pagament</td><td>Tècnica, de tercers (Sift, el servei antifrau de WooPayments)</td><td>Persistent: la fixa Sift</td></tr>
<tr><td>_ga, _ga_*</td><td>Estadístiques d\'ús del web (Google Analytics 4)</td><td>Anàlisi, de tercers (Google). Només si les acceptes</td><td>2 anys</td></tr>
</tbody></table><p>A la pàgina de pagament, la passarel·la també carrega els scripts de Stripe i de Sift (cdn.sift.com i hexagon-analytics.com) per comprovar que el pagament és legítim. Si detecta un intent sospitós, Stripe pot mostrar una comprovació d\'hCaptcha, que fa servir les seves pròpies galetes tècniques mentre dura.</p><p>No fem servir galetes de publicitat ni de xarxes socials. Google Analytics només es carrega si acceptes les galetes d\'anàlisi.</p>' ),
		array( 'Com canviar la teva elecció', '<p>Pots acceptar o rebutjar les galetes d\'anàlisi a l\'avís que apareix en entrar i canviar d\'opinió quan vulguis amb l\'enllaç «Configurar les galetes» del peu de pàgina, també a la pàgina de pagament. Rebutjar-les no afecta la compra.</p><p>També pots esborrar les galetes des de la configuració del teu navegador.</p>' ),
		array( 'Més informació', '<p>Per a qualsevol dubte, escriu a info@dryicepack.es. Qui més tracta les teves dades i com pots exercir els teus drets, a la política de privacitat.</p>' ),
	),
);
