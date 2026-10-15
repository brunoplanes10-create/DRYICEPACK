/* Usos: selector "¿3 mm o 16 mm?". Al elegir un uso cambia la ficha, la foto cenital del formato (fundido)
   y la cota que se destaca (data-formato en la sección). Teclado: flechas, Inicio y Fin (radiogroup). */
(function () {
	'use strict';
	var raiz = document.querySelector('[data-selector]');
	if (!raiz) return;
	var botones = Array.prototype.slice.call(raiz.querySelectorAll('[data-uso]'));
	var fotos = raiz.querySelectorAll('[data-foto-f]');
	function elegir(b, foco) {
		var f = b.getAttribute('data-f');
		botones.forEach(function (x) { x.setAttribute('aria-checked', x === b ? 'true' : 'false'); x.tabIndex = x === b ? 0 : -1; });
		raiz.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== b.getAttribute('data-uso'); });
		raiz.setAttribute('data-formato', f);
		Array.prototype.forEach.call(fotos, function (im) { im.classList.toggle('es-activa', im.getAttribute('data-foto-f') === f); });
		if (foco) b.focus();
	}
	botones.forEach(function (b, i) {
		b.tabIndex = i === 0 ? 0 : -1;
		b.addEventListener('click', function () {
			elegir(b);
			// En móvil la ficha queda debajo de la foto, fuera de pantalla: se acerca lo justo para ver el resultado
			var ficha = raiz.querySelector('.a-selector__ficha');
			// (su pie queda al 88 % del alto de la pantalla: así también entra la cota, que se dibuja al verse)
			var r = ficha ? ficha.getBoundingClientRect() : null;
			if (r && window.innerWidth <= 900 && r.top > window.innerHeight * 0.75) {
				var quieto = document.documentElement.classList.contains('quieto') || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
				window.scrollBy({ top: r.bottom - window.innerHeight * 0.88, behavior: quieto ? 'auto' : 'smooth' });
			}
		});
		b.addEventListener('keydown', function (e) {
			var n = botones.length;
			var destino = { ArrowRight: i + 1, ArrowDown: i + 1, ArrowLeft: i - 1, ArrowUp: i - 1, Home: 0, End: n - 1 }[e.key];
			if (destino === undefined) return;
			e.preventDefault();
			elegir(botones[(destino + n) % n], true);
		});
	});
})();
