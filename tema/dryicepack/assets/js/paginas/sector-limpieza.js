/* Hielo seco para limpieza criogénica · K5: el ritmo del suministro programado enciende los días del calendario de ejemplo.
   Semanal: cada semana · quincenal: semanas 1, 3 y 5 · mensual: semanas 1 y 5 (cuatro semanas). Siempre el miércoles.
   Sin JS el calendario enseña el semanal y el selector no se ve. Solo cambia clases: el movimiento lo hace el CSS. */
(function () {
	'use strict';
	var d = document;
	var grupo = d.querySelector('[data-ritmo]');
	var cal = d.querySelector('[data-cal]');
	if (!grupo || !cal) return;
	var botones = Array.prototype.slice.call(grupo.querySelectorAll('button[data-r]'));
	var dias = cal.querySelectorAll('.cr-dia');
	var plantilla = cal.getAttribute('data-alt') || '';
	function poner(r, boton) {
		dias.forEach(function (el) {
			el.classList.toggle('on', el.getAttribute('data-d') === '2' && parseInt(el.getAttribute('data-s'), 10) % r === 0);
		});
		botones.forEach(function (b) { b.setAttribute('aria-pressed', b === boton ? 'true' : 'false'); });
		if (plantilla) cal.setAttribute('aria-label', plantilla.replace('%s', boton.textContent.trim().toLowerCase()));
	}
	botones.forEach(function (b) {
		b.addEventListener('click', function () { poner(parseInt(b.getAttribute('data-r'), 10) || 1, b); });
	});
})();
