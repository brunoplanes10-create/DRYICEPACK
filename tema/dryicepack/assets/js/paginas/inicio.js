/* Portada: configurador de formato y kilos con el precio final (misma fórmula de envío que el checkout). */
(function () {
	'use strict';
	var CFG = window.DIPT || {};
	var raiz = document.querySelector('[data-configurador]');
	if (!raiz || !CFG.variaciones) return;

	var estado = { formato: raiz.getAttribute('data-formato') || '16mm', kg: parseFloat(raiz.getAttribute('data-kg')) || 10 };
	var T = CFG.tarifa || {};
	var iva = CFG.iva || 1.21;
	var decimal = CFG.idioma === 'en' ? '.' : ',';

	function euros(n) {
		var s = (Math.round(n * 100) / 100).toFixed(2).split('.');
		var miles = CFG.idioma === 'en' ? ',' : '.';
		s[0] = s[0].replace(/\B(?=(\d{3})+(?!\d))/g, miles);
		return CFG.idioma === 'en' ? '€' + s.join('.') : s.join(decimal) + ' €';
	}
	function envio(kg, cajas) {
		if (kg <= 0) return 0;
		var f = Math.ceil(kg + (T.embalaje || 1) * Math.max(1, cajas || 1));
		if (f <= 2) return T.hasta2; if (f <= 5) return T.hasta5; if (f <= 10) return T.hasta10;
		return Math.round((T.hasta10 + (f - 10) * T.kgExtra) * 100) / 100;
	}
	function variacion() {
		for (var i = 0; i < CFG.variaciones.length; i++) {
			var v = CFG.variaciones[i];
			if (Number(v.kg) === estado.kg && v.formato === estado.formato) return v;
		}
		return null;
	}

	var paneles = raiz.querySelectorAll('.i-panel');
	var kilos = raiz.querySelectorAll('.i-kilo');
	var barra = raiz.querySelector('.i-regla__barra');
	var boton = raiz.querySelector('[data-pedir]');

	function pintar() {
		var v = variacion(); if (!v) return;
		var precio = v.precio * iva, env = envio(estado.kg, 1) * iva;
		raiz.querySelector('[data-ticket-pack]').textContent = estado.kg + ' kg · ' + estado.formato.replace('mm', ' mm');
		raiz.querySelector('[data-ticket-precio]').textContent = euros(precio);
		raiz.querySelector('[data-ticket-envio]').textContent = euros(env);
		raiz.querySelector('[data-ticket-total]').textContent = euros(precio + env);
		var rec = raiz.querySelector('[data-ticket-recogida]');
		if (rec) rec.textContent = (rec.getAttribute('data-t-recogida') || '%s').replace('%s', euros(precio));
		if (boton) {
			var span = boton.querySelector('span');
			if (span) span.textContent = (boton.getAttribute('data-t-boton') || '%s').replace('%s', String(estado.kg).replace('.', decimal));
			if (v.padre) {
				var u = new URL(window.location.origin + '/');
				u.searchParams.set('add-to-cart', v.padre);
				u.searchParams.set('variation_id', v.id);
				u.searchParams.set('attribute_pa_peso', v.peso);
				u.searchParams.set('attribute_pa_formato', v.fslug);
				u.searchParams.set('quantity', '1');
				u.searchParams.set('dipt_ir', 'checkout');
				boton.href = u.toString();
			}
		}
		paneles.forEach(function (p) { var on = p.getAttribute('data-formato') === estado.formato; p.classList.toggle('activo', on); p.setAttribute('aria-checked', on ? 'true' : 'false'); });
		kilos.forEach(function (k, i) {
			var on = parseFloat(k.getAttribute('data-kg')) === estado.kg;
			k.classList.toggle('activo', on); k.setAttribute('aria-checked', on ? 'true' : 'false');
			if (on && barra) barra.style.setProperty('--pos', i);
		});
	}

	paneles.forEach(function (p) { p.addEventListener('click', function () { estado.formato = p.getAttribute('data-formato'); pintar(); }); });
	kilos.forEach(function (k) { k.addEventListener('click', function () { estado.kg = parseFloat(k.getAttribute('data-kg')); pintar(); }); });
	/* Flechas del teclado dentro de cada grupo, como en un grupo de radios */
	[paneles, kilos].forEach(function (grupo) {
		grupo.forEach(function (el, i) {
			el.addEventListener('keydown', function (e) {
				var dir = e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1 : e.key === 'ArrowLeft' || e.key === 'ArrowUp' ? -1 : 0;
				if (!dir) return;
				e.preventDefault();
				var sig = grupo[(i + dir + grupo.length) % grupo.length];
				sig.focus(); sig.click();
			});
		});
	});
	pintar();
})();
