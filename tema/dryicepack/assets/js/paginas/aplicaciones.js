/* Usos: selector "qué formato para lo tuyo". */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-selector]');
	if (!raiz) return;
	var botones = Array.prototype.slice.call(raiz.querySelectorAll('[data-uso]'));
	function elegir(b, foco) {
		botones.forEach(function (x) { x.setAttribute('aria-checked', x === b ? 'true' : 'false'); x.tabIndex = x === b ? 0 : -1; });
		raiz.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== b.getAttribute('data-uso'); });
		if (foco) b.focus();
	}
	botones.forEach(function (b, i) {
		b.tabIndex = i === 0 ? 0 : -1;
		b.addEventListener('click', function () { elegir(b); });
		b.addEventListener('keydown', function (e) {
			var dir = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[e.key];
			if (dir) { e.preventDefault(); elegir(botones[(i + dir + botones.length) % botones.length], true); }
		});
	});
})();
