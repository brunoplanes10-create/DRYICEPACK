/* Empresas: pila de cajas que crece con los kilos y una nota según el volumen.
   Cuenta de empresa: cada caja lleva hasta 30 kg (30 kg = 1 caja, 31 kg = 2 cajas).
   Notas, una encendida cada vez: hasta 150 kg · más de 150 y hasta 250 kg · más de 250 kg. */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-volumen]');
	if (!raiz) return;
	var rango = raiz.querySelector('[data-volumen-rango]');
	var salida = raiz.querySelector('[data-volumen-kg]');
	var textoCajas = raiz.querySelector('[data-volumen-cajas]');
	var cajas = raiz.querySelectorAll('.e-pila__caja');
	var notas = raiz.querySelectorAll('[data-nota]');
	var POR_CAJA = parseInt(raiz.getAttribute('data-por-caja'), 10) || 30;
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
		var tramo = kg <= 150 ? 'web' : kg <= 250 ? 'bcn' : 'fuera';
		notas.forEach(function (li) { li.classList.toggle('activa', li.getAttribute('data-nota') === tramo); });
	}
	rango.addEventListener('input', pintar);
	pintar();
})();
