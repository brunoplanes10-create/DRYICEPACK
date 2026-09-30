<?php
/**
 * Aviso de cookies propio (sin plugins): 2 KB de JS, sin peticiones externas.
 * - Google Consent Mode v2: todo denegado por defecto; se actualiza con la decisión.
 * - Google Analytics 4 (ID en los ajustes) solo se carga después de aceptar la analítica.
 * - "Rechazar" está al mismo nivel que "Aceptar" (guía de cookies de la AEPD, 2023).
 * - Cualquier enlace con data-dip-cookies vuelve a abrir el panel (el pie de página lleva uno).
 * - Eventos: clic en teléfono, WhatsApp y email, formulario enviado y compra.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

function dip_hay_analitica() {
	return (bool) dip_ajuste( 'ga4' ) || defined( 'GOOGLESITEKIT_VERSION' );
}

function dip_textos_cookies( $idioma ) {
	$tx = array(
		'es' => array(
			'texto'     => 'Usamos cookies técnicas para que la tienda funcione y, si lo aceptas, cookies de Google Analytics para saber qué páginas se usan y mejorarlas.',
			'aceptar'   => 'Aceptar',
			'rechazar'  => 'Rechazar',
			'config'    => 'Elegir',
			'guardar'   => 'Guardar',
			'tecnicas'  => 'Técnicas (siempre activas): carrito, pago y seguridad.',
			'analitica' => 'Análisis: Google Analytics, datos agregados de uso.',
			'mas'       => 'Política de cookies',
			'titulo'    => 'Cookies',
			'url'       => '/politica-de-cookies/',
		),
		'ca' => array(
			'texto'     => 'Fem servir galetes tècniques perquè la botiga funcioni i, si ho acceptes, galetes de Google Analytics per saber quines pàgines es fan servir i millorar-les.',
			'aceptar'   => 'Acceptar',
			'rechazar'  => 'Rebutjar',
			'config'    => 'Triar',
			'guardar'   => 'Desar',
			'tecnicas'  => 'Tècniques (sempre actives): cistella, pagament i seguretat.',
			'analitica' => "Anàlisi: Google Analytics, dades agregades d'ús.",
			'mas'       => 'Política de galetes',
			'titulo'    => 'Galetes',
			'url'       => '/ca/politica-de-galetes/',
		),
		'en' => array(
			'texto'     => 'We use technical cookies so the shop works and, if you accept, Google Analytics cookies to see which pages are used and improve them.',
			'aceptar'   => 'Accept',
			'rechazar'  => 'Reject',
			'config'    => 'Choose',
			'guardar'   => 'Save',
			'tecnicas'  => 'Technical (always on): basket, payment and security.',
			'analitica' => 'Analytics: Google Analytics, aggregated usage data.',
			'mas'       => 'Cookie policy',
			'titulo'    => 'Cookies',
			'url'       => '/en/cookie-policy/',
		),
	);
	return $tx[ $idioma ] ?? $tx['es'];
}

/* ---------- 1 · Consentimiento por defecto, lo primero del <head> ---------- */
add_action( 'wp_head', static function () {
	if ( is_admin() ) return;
	echo "<script nowprocket data-cfasync=\"false\">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}"
		. "gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',functionality_storage:'granted',security_storage:'granted',wait_for_update:500});"
		. "(function(){try{var m=document.cookie.match(/(?:^|; )dip_consent=([^;]+)/);if(!m)return;var c=JSON.parse(decodeURIComponent(m[1]));"
		. "gtag('consent','update',{analytics_storage:c.a?'granted':'denied'});}catch(e){}})();</script>\n";
}, 0 );

/* ---------- 2 · Panel y lógica, al final de la página ---------- */
add_action( 'wp_footer', static function () {
	if ( is_admin() || ! dip_hay_analitica() ) return;
	$tx  = dip_textos_cookies( dip_idioma() );
	$ga4 = (string) dip_ajuste( 'ga4' );
	?>
	<div class="dip-cookies" id="dip-cookies" role="dialog" aria-modal="false" aria-labelledby="dip-cookies-t" hidden>
		<p class="dip-cookies__t" id="dip-cookies-t"><?php echo esc_html( $tx['titulo'] ); ?></p>
		<p class="dip-cookies__texto"><?php echo esc_html( $tx['texto'] ); ?> <a href="<?php echo esc_url( home_url( $tx['url'] ) ); ?>"><?php echo esc_html( $tx['mas'] ); ?></a></p>
		<div class="dip-cookies__opciones" hidden>
			<label><input type="checkbox" checked disabled> <?php echo esc_html( $tx['tecnicas'] ); ?></label>
			<label><input type="checkbox" data-dip-a> <?php echo esc_html( $tx['analitica'] ); ?></label>
		</div>
		<div class="dip-cookies__botones">
			<button type="button" data-dip-c="rechazar"><?php echo esc_html( $tx['rechazar'] ); ?></button>
			<button type="button" data-dip-c="config"><?php echo esc_html( $tx['config'] ); ?></button>
			<button type="button" data-dip-c="guardar" hidden><?php echo esc_html( $tx['guardar'] ); ?></button>
			<button type="button" data-dip-c="aceptar"><?php echo esc_html( $tx['aceptar'] ); ?></button>
		</div>
	</div>
	<style>
		.dip-cookies{position:fixed;z-index:9990;left:16px;bottom:16px;max-width:400px;padding:20px 20px 16px;background:var(--c-noche,#0B141C);color:var(--c-hielo-claro,#E6EEF5);border:1px solid rgba(184,208,232,.22);border-radius:14px;box-shadow:0 24px 60px -20px rgba(0,0,0,.55);font:400 14px/1.5 var(--f-texto,system-ui,sans-serif)}
		.dip-cookies[hidden]{display:none}
		.dip-cookies__t{margin:0 0 6px;font:400 22px/1.1 var(--f-display,Georgia,serif);color:#fff}
		.dip-cookies__texto{margin:0 0 14px}
		.dip-cookies a{color:#B8D0E8;text-underline-offset:3px}
		.dip-cookies__opciones{display:grid;gap:8px;margin:0 0 14px;font-size:13px}
		.dip-cookies__opciones[hidden]{display:none}
		.dip-cookies__opciones input{accent-color:#B8D0E8;margin-right:6px}
		.dip-cookies__botones{display:flex;flex-wrap:wrap;gap:8px}
		.dip-cookies button{flex:1 1 auto;min-height:44px;padding:10px 16px;border-radius:999px;border:1px solid rgba(184,208,232,.45);background:transparent;color:#fff;font:600 14px/1 var(--f-texto,system-ui,sans-serif);cursor:pointer}
		.dip-cookies button[hidden]{display:none}
		.dip-cookies button:hover{border-color:#B8D0E8}
		.dip-cookies button:focus-visible{outline:2px solid #B8D0E8;outline-offset:2px}
		@media (max-width:520px){.dip-cookies{left:8px;right:8px;bottom:8px;max-width:none}}
	</style>
	<script nowprocket data-cfasync="false">
	(function(){
		var GA=<?php echo wp_json_encode( $ga4 ); ?>,caja=document.getElementById('dip-cookies');
		function leer(){var m=document.cookie.match(/(?:^|; )dip_consent=([^;]+)/);if(!m)return null;try{return JSON.parse(decodeURIComponent(m[1]));}catch(e){return null;}}
		function cargarGA(){if(!GA||window.dipGA)return;window.dipGA=1;var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(GA);document.head.appendChild(s);gtag('js',new Date());gtag('config',GA,{anonymize_ip:true});}
		function guardar(a){var v={a:a?1:0,v:1,t:Date.now()};document.cookie='dip_consent='+encodeURIComponent(JSON.stringify(v))+';path=/;max-age='+(60*60*24*180)+';samesite=lax'+(location.protocol==='https:'?';secure':'');gtag('consent','update',{analytics_storage:a?'granted':'denied'});if(a)cargarGA();caja.hidden=true;}
		function abrir(){caja.hidden=false;var b=caja.querySelector('[data-dip-c="aceptar"]');if(b)b.focus({preventScroll:true});}
		var c=leer();
		if(c){if(c.a)cargarGA();}else{setTimeout(abrir,600);}
		caja.addEventListener('click',function(e){var b=e.target.closest('[data-dip-c]');if(!b)return;var accion=b.getAttribute('data-dip-c');
			if(accion==='aceptar')guardar(true);
			else if(accion==='rechazar')guardar(false);
			else if(accion==='config'){caja.querySelector('.dip-cookies__opciones').hidden=false;b.hidden=true;caja.querySelector('[data-dip-c="guardar"]').hidden=false;var x=leer();caja.querySelector('[data-dip-a]').checked=!!(x&&x.a);}
			else if(accion==='guardar')guardar(caja.querySelector('[data-dip-a]').checked);});
		document.addEventListener('click',function(e){var a=e.target.closest('[data-dip-cookies]');if(a){e.preventDefault();abrir();}});
		/* Medición de contactos: solo si hay consentimiento (gtag sin GA cargado no envía nada). */
		window.dipMedir=function(n,p){var x=leer();if(x&&x.a&&window.gtag)gtag('event',n,p||{});};
		document.addEventListener('click',function(e){var a=e.target.closest('a[href]');if(!a)return;var h=a.getAttribute('href')||'';
			if(h.indexOf('tel:')===0)dipMedir('click_telefono',{link_url:h});
			else if(/wa\.me|whatsapp/.test(h))dipMedir('click_whatsapp',{link_url:h});
			else if(h.indexOf('mailto:')===0)dipMedir('click_email',{link_url:h});});
	})();
	</script>
	<?php
}, 50 );

/* ---------- 3 · Compra (página de gracias, una sola vez por pedido) ---------- */
add_action( 'woocommerce_thankyou', static function ( $pedido_id ) {
	$pedido = wc_get_order( $pedido_id );
	if ( ! $pedido || $pedido->get_meta( '_dip_ga_compra' ) || ! dip_ajuste( 'ga4' ) ) return;
	$items = array();
	foreach ( $pedido->get_items() as $l ) {
		$items[] = array( 'item_id' => (string) $l->get_variation_id() ?: (string) $l->get_product_id(), 'item_name' => $l->get_name(), 'quantity' => $l->get_quantity(), 'price' => round( (float) $l->get_subtotal() / max( 1, $l->get_quantity() ), 2 ) );
	}
	$datos = array(
		'transaction_id' => (string) $pedido->get_order_number(),
		'value'          => (float) $pedido->get_total(),
		'tax'            => (float) $pedido->get_total_tax(),
		'shipping'       => (float) $pedido->get_shipping_total(),
		'currency'       => $pedido->get_currency(),
		'items'          => $items,
	);
	echo '<script nowprocket>window.addEventListener("load",function(){if(window.dipMedir)dipMedir("purchase",' . wp_json_encode( $datos ) . ');});</script>';
	$pedido->update_meta_data( '_dip_ga_compra', 1 );
	$pedido->save_meta_data();
}, 99 );
