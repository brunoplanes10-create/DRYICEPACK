/* Eventos: el número fijo cambia al paso que ocupa el centro de la pantalla. */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-pasos]');
	if (!raiz || !('IntersectionObserver' in window)) return;
	var numero = raiz.querySelector('[data-paso-numero]');
	var caja = numero ? numero.parentNode : null;
	var pasos = raiz.querySelectorAll('[data-paso]');
	var io = new IntersectionObserver(function (es) {
		es.forEach(function (e) {
			if (!e.isIntersecting) return;
			pasos.forEach(function (p) { p.classList.toggle('actual', p === e.target); });
			var n = e.target.getAttribute('data-paso');
			if (!numero || numero.textContent === n) return;
			caja.classList.add('cambia');
			setTimeout(function () { numero.textContent = n; caja.classList.remove('cambia'); }, 250);
		});
	}, { rootMargin: '-45% 0px -45% 0px' });
	pasos.forEach(function (p) { io.observe(p); });
})();
