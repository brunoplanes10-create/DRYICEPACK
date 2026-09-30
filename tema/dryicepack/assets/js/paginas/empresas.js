/* Empresas: pila de cajas que crece con los kilos (cajas de hasta 20 kg) y notas según el volumen. */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-volumen]');
	if (!raiz) return;
	var rango = raiz.querySelector('[data-volumen-rango]');
	var salida = raiz.querySelector('[data-volumen-kg]');
	var textoCajas = raiz.querySelector('[data-volumen-cajas]');
	var cajas = raiz.querySelectorAll('.e-pila__caja');
	var notas = raiz.querySelectorAll('[data-nota]');
	var POR_CAJA = 20;
	function pintar() {
		var kg = parseInt(rango.value, 10) || 0;
		var n = Math.max(1, Math.ceil(kg / POR_CAJA));
		salida.textContent = kg;
		textoCajas.textContent = (n === 1 ? raiz.getAttribute('data-t-caja') : raiz.getAttribute('data-t-cajas')).replace('%1$s', n).replace('%2$s', kg).replace('%s', kg);
		cajas.forEach(function (c, i) {
			var orden = cajas.length - 1 - i; // se llenan de abajo arriba
			var llena = orden < n;
			c.classList.toggle('llena', llena);
			c.classList.toggle('mas', n > cajas.length && orden === cajas.length - 1);
			c.style.setProperty('--orden', orden);
		});
		notas.forEach(function (li) {
			var t = li.getAttribute('data-nota');
			li.classList.toggle('activa', (t === 'web' && kg <= 250) || (t === 'bcn' && kg > 150) || (t === 'fuera' && kg > 250));
		});
	}
	rango.addEventListener('input', pintar);
	pintar();
})();
