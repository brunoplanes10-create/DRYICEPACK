/* Transporte: planificador de rutas → consumo aproximado al mes. */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-plan]');
	if (!raiz) return;
	var salida = raiz.querySelector('[data-plan-mes]');
	var kg = raiz.querySelector('[data-plan-kg]');
	function pintar() {
		var dias = raiz.querySelectorAll('[data-plan-dia]:checked').length;
		var k = Math.max(0, parseFloat(kg.value) || 0);
		salida.textContent = Math.round(dias * k * 4.3);
	}
	raiz.addEventListener('input', pintar);
	raiz.addEventListener('change', pintar);
	pintar();
})();
