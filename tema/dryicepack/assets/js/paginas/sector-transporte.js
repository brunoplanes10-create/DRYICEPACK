/* Transporte: planificador de rutas → consumo por semana (días × kg por ruta) y al mes (por semana × 4). Números enteros. */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-plan]');
	if (!raiz) return;
	var semana = raiz.querySelector('[data-plan-semana]');
	var mes = raiz.querySelector('[data-plan-mes]');
	var kg = raiz.querySelector('[data-plan-kg]');
	function pintar() {
		var dias = raiz.querySelectorAll('[data-plan-dia]:checked').length;
		var k = Math.max(0, Math.round(parseFloat(kg.value) || 0));
		var porSemana = dias * k;
		if (semana) semana.textContent = porSemana;
		if (mes) mes.textContent = porSemana * 4;
	}
	raiz.addEventListener('input', pintar);
	raiz.addEventListener('change', pintar);
	pintar();
})();
