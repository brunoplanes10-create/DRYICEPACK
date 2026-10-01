<?php
/* Política de galetes · català. Reflecteix les galetes que fan servir el tema, el plugin, WooCommerce, WooPayments i Google Analytics 4. */
return array(
	'seo'        => array(
		'titulo'      => 'Política de galetes | DryIcePack',
		'descripcion' => 'Quines galetes fa servir dryicepack.es: tècniques per a la cistella i el pagament, i d\'anàlisi de Google Analytics només si les acceptes. Com canviar-ho.',
	),
	'titulo'     => 'Política de galetes',
	'actualizado'=> 'Darrera actualització: 30 de setembre de 2026',
	'secciones'  => array(
		array( 'Què són', '<p>Les galetes (cookies) són petits fitxers que el web desa al teu navegador. Algunes són imprescindibles perquè la botiga funcioni; d\'altres només es fan servir si les acceptes.</p>' ),
		array( 'Galetes que fem servir', '<table><thead><tr><th>Galeta</th><th>Per a què</th><th>Tipus</th><th>Durada</th></tr></thead><tbody>
<tr><td>wp_woocommerce_session_*</td><td>Recordar la teva cistella</td><td>Tècnica, pròpia</td><td>2 dies</td></tr>
<tr><td>woocommerce_cart_hash, woocommerce_items_in_cart</td><td>Saber si la cistella ha canviat</td><td>Tècnica, pròpia</td><td>Sessió</td></tr>
<tr><td>dip_idioma</td><td>Recordar l\'idioma que has triat</td><td>Tècnica, pròpia</td><td>90 dies</td></tr>
<tr><td>dip_consent</td><td>Recordar la teva elecció sobre les galetes</td><td>Tècnica, pròpia</td><td>180 dies</td></tr>
<tr><td>__stripe_mid, __stripe_sid</td><td>Prevenir el frau en el pagament amb targeta</td><td>Tècnica, de tercers (Stripe)</td><td>1 any i 30 minuts</td></tr>
<tr><td>_ga, _ga_*</td><td>Estadístiques d\'ús del web (Google Analytics 4)</td><td>Anàlisi, de tercers (Google). Només si les acceptes</td><td>2 anys</td></tr>
</tbody></table>' ),
		array( 'Com canviar la teva elecció', '<p>Pots acceptar o rebutjar les galetes d\'anàlisi a l\'avís que apareix en entrar i canviar d\'opinió quan vulguis amb l\'enllaç «Configurar les galetes» del peu de pàgina. Rebutjar-les no afecta la compra.</p><p>També pots esborrar les galetes des de la configuració del teu navegador.</p>' ),
		array( 'Més informació', '<p>Responsable: INDUNOVA IMS S.L. Per a qualsevol dubte, escriu a info@dryicepack.es. Trobaràs més detalls sobre el tractament de dades a la política de privacitat.</p>' ),
	),
);
