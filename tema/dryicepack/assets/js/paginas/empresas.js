/* Empresas: palé que se llena con los kilos, contador del carrusel «1/3» (móvil) y contador de campos de la frase.
   Cuenta de empresa: cada caja lleva hasta 30 kg (30 kg = 1 caja, 31 kg = 2 cajas).
   Notas, una encendida cada vez: hasta 150 kg · más de 150 y hasta 250 kg · más de 250 kg. */
(function () {
	'use strict';

	/* ---------- E4 · Palé ---------- */
	var raiz = document.querySelector('[data-volumen]');
	if (raiz) {
		var rango = raiz.querySelector('[data-volumen-rango]');
		var salida = raiz.querySelector('[data-volumen-kg]');
		var textoCajas = raiz.querySelector('[data-volumen-cajas]');
		var cajas = raiz.querySelectorAll('.e-caja');
		var notas = raiz.querySelectorAll('[data-nota]');
		var POR_CAJA = parseInt(raiz.getAttribute('data-por-caja'), 10) || 30;
		var antes = -1;
		var pintar = function () {
			var kg = parseInt(rango.value, 10) || 0;
			var n = Math.max(1, Math.ceil(kg / POR_CAJA));
			salida.textContent = kg;
			textoCajas.textContent = (n === 1 ? raiz.getAttribute('data-t-caja') : raiz.getAttribute('data-t-cajas')).replace('%1$s', n).replace('%2$s', kg).replace('%s', kg);
			// Las cajas que entran caen una detrás de otra (18 ms de escalón desde la primera nueva); las que salen, sin retraso
			var desde = antes < 0 ? n : Math.min(antes, n);
			cajas.forEach(function (c) {
				var orden = parseInt(c.style.getPropertyValue('--orden'), 10) || 0;
				c.style.setProperty('--retraso', Math.max(0, orden - desde) * 18 + 'ms');
				c.classList.toggle('llena', orden < n);
				c.classList.toggle('mas', n > cajas.length && orden === cajas.length - 1);
			});
			antes = n;
			var tramo = kg <= 150 ? 'web' : kg <= 250 ? 'bcn' : 'fuera';
			notas.forEach(function (li) { li.classList.toggle('activa', li.getAttribute('data-nota') === tramo); });
		};
		rango.addEventListener('input', pintar);
		pintar();
	}

	/* ---------- E2 · Carrusel en móvil: «1/3» según la tarjeta que se ve ---------- */
	var carrusel = document.querySelector('[data-carrusel]');
	var cuenta = document.querySelector('[data-carrusel-n]');
	if (carrusel && cuenta && 'IntersectionObserver' in window) {
		var pasos = Array.prototype.slice.call(carrusel.children);
		var io = new IntersectionObserver(function (es) {
			es.forEach(function (e) {
				if (e.isIntersecting && e.intersectionRatio >= 0.6) cuenta.textContent = String(pasos.indexOf(e.target) + 1);
			});
		}, { root: carrusel, threshold: [0.6] });
		pasos.forEach(function (p) { io.observe(p); });
	}

	/* ---------- E6 · Campos rellenos de la frase: «3/8» ---------- */
	var frase = document.querySelector('[data-frase]');
	var n = frase && frase.querySelector('[data-frase-n]');
	if (frase && n) {
		var campos = frase.querySelectorAll('.e-frase__texto input, .e-frase__texto select');
		// El desplegable de la frecuencia siempre tiene un valor: cuenta solo cuando el cliente lo ha tocado (antes, «1/8» sin escribir nada)
		var tocados = [];
		var contar = function (e) {
			if (e && e.target && e.target.tagName === 'SELECT' && tocados.indexOf(e.target) === -1) tocados.push(e.target);
			var k = 0;
			campos.forEach(function (c) {
				if (c.tagName === 'SELECT' ? tocados.indexOf(c) !== -1 : String(c.value).trim() !== '') k++;
			});
			n.textContent = String(k);
		};
		frase.addEventListener('input', contar);
		frase.addEventListener('change', contar);
		contar();
	}
})();
