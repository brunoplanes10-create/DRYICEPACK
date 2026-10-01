/* Comprar: configurador (formato, kilos, cajas, código postal → precio final y fecha), comparador y barra fija en móvil.
   El envío se calcula con la misma tarifa que cobra el checkout (plugin dryicepack-tienda). */
(function () {
	'use strict';
	var CFG = window.DIPT || {};
	var raiz = document.querySelector('[data-compra]');
	if (raiz && CFG.variaciones) iniciarCompra();
	iniciarComparador();

	function iniciarCompra() {
		var form = raiz.querySelector('[data-compra-form]');
		var T = CFG.tarifa || {}, iva = CFG.iva || 1.21, en = CFG.idioma === 'en';
		var estado = { formato: raiz.getAttribute('data-formato'), kg: parseFloat(raiz.getAttribute('data-kg')), cajas: 1, cp: '' };
		var maxKg = T.maxKg || 250;
		var FUERA = ['07', '35', '38', '51', '52'];

		function euros(n) {
			var s = (Math.round(n * 100) / 100).toFixed(2).split('.');
			s[0] = s[0].replace(/\B(?=(\d{3})+(?!\d))/g, en ? ',' : '.');
			return en ? '€' + s.join('.') : s.join(',') + ' €';
		}
		function envio(kg, cajas) {
			if (kg <= 0) return 0;
			var f = Math.ceil(kg + (T.embalaje || 1) * Math.max(1, cajas));
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
		function campo(n, valor) { var el = form.querySelector('[data-campo="' + n + '"]'); if (el) el.value = valor; }

		var inputCajas = form.querySelector('[data-cajas-input]');
		var llega = raiz.querySelector('[data-llega]');
		var llegaTexto = raiz.querySelector('[data-llega-texto]');
		var aviso = raiz.querySelector('[data-max]');

		function pintar() {
			var v = variacion(); if (!v) return;
			var maxCajas = Math.max(1, Math.floor(maxKg / estado.kg));
			if (estado.cajas > maxCajas) estado.cajas = maxCajas;
			inputCajas.max = String(maxCajas);
			inputCajas.value = String(estado.cajas);
			if (aviso) aviso.hidden = estado.cajas < maxCajas;
			var kgTotal = estado.kg * estado.cajas;
			var medida = raiz.querySelector('[data-medida]');
			if (medida) medida.hidden = kgTotal <= 150 || !(aviso && aviso.hidden);
			var pack = v.precio * iva * estado.cajas, env = envio(kgTotal, estado.cajas) * iva;
			raiz.querySelector('[data-precio-concepto]').textContent = raiz.querySelector('[data-precio-concepto]').textContent.split('·')[0].trim() + ' · ' + (estado.cajas > 1 ? estado.cajas + ' × ' : '') + estado.kg + ' kg';
			raiz.querySelector('[data-precio-pack]').textContent = euros(pack);
			raiz.querySelector('[data-precio-envio]').textContent = euros(env);
			document.querySelectorAll('[data-precio-total]').forEach(function (el) { el.textContent = euros(pack + env); });
			campo('padre', v.padre || ''); campo('id', v.id); campo('peso', v.peso || ''); campo('fslug', v.fslug || '');
			// Galería del formato elegido
			document.querySelectorAll('[data-galeria-formato]').forEach(function (g) {
				var on = g.getAttribute('data-galeria-formato') === estado.formato;
				g.hidden = !on; g.classList.toggle('activo', on);
				if (on) g.querySelectorAll('img[loading="lazy"]').forEach(function (img) { img.loading = 'eager'; });
			});
			// Fecha de entrega (misma regla que el checkout) y aviso si el CP no es peninsular
			var cp = estado.cp.replace(/\D/g, '');
			var fuera = cp.length >= 2 && FUERA.indexOf(cp.slice(0, 2)) !== -1;
			llega.classList.toggle('es-fuera', fuera);
			if (fuera) { llegaTexto.textContent = llega.getAttribute('data-t-fuera'); }
			else if (window.dipEntrega) {
				var r = window.dipEntrega.proxima();
				if (r) llegaTexto.textContent = llega.getAttribute('data-t-llega').replace('%s', window.dipEntrega.texto(r.entrega));
			}
		}

		form.addEventListener('change', function (e) {
			var t = e.target;
			if (t.name === 'dipt_formato') estado.formato = t.value;
			if (t.name === 'dipt_kg') estado.kg = parseFloat(t.value);
			if (t.name === 'quantity') estado.cajas = Math.max(1, parseInt(t.value, 10) || 1);
			pintar();
		});
		form.querySelectorAll('[data-cajas]').forEach(function (b) {
			b.addEventListener('click', function () { estado.cajas = Math.max(1, estado.cajas + parseInt(b.getAttribute('data-cajas'), 10)); pintar(); });
		});
		var cp = form.querySelector('[data-cp]');
		if (cp) cp.addEventListener('input', function () { cp.value = cp.value.replace(/\D/g, '').slice(0, 5); estado.cp = cp.value; pintar(); });
		form.addEventListener('submit', function () { if (window.dipMedir) window.dipMedir('add_to_cart', { value: 0, items: [{ item_id: String(form.querySelector('[data-campo="id"]').value), quantity: estado.cajas }] }); });

		// Barra fija en móvil: aparece cuando el botón principal sale de la pantalla
		var barra = document.querySelector('[data-barra-compra]');
		var botonPrincipal = raiz.querySelector('.p-comprar');
		if (barra && botonPrincipal && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (es) { barra.classList.toggle('visible', !es[0].isIntersecting && es[0].boundingClientRect.top < 0); }).observe(botonPrincipal);
			barra.querySelector('[data-barra-comprar]').addEventListener('click', function () { form.requestSubmit ? form.requestSubmit() : form.submit(); });
		}
		pintar();
	}

	function iniciarComparador() {
		var c = document.querySelector('[data-comparador]');
		if (!c) return;
		var r = c.querySelector('[data-comparador-rango]');
		function mover() { c.style.setProperty('--corte', r.value + '%'); }
		r.addEventListener('input', mover);
		mover();
	}
})();
