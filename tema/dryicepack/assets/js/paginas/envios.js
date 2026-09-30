/* Envíos: cuenta atrás al corte de las 12:00 (hora de Madrid) y calculadora con la tarifa real. */
(function () {
	'use strict';
	var CFG = window.DIPT || {}, E = window.dipEntrega;
	var reloj = document.querySelector('[data-cuenta-atras]');
	if (reloj && E) {
		var h = reloj.querySelector('[data-h]'), m = reloj.querySelector('[data-m]'), s = reloj.querySelector('[data-s]');
		var etiqueta = reloj.querySelector('[data-reloj-etiqueta]');
		var dos = function (n) { return ('0' + n).slice(-2); };
		var tic = function () {
			var r = E.proxima(), a = E.ahora();
			var corte = (CFG.corte ? CFG.corte.hora : 12) * 3600;
			var faltan = r && r.saleHoy ? corte - (a.h * 3600 + a.mi * 60 + a.s) : 0;
			if (faltan > 0) {
				h.textContent = dos(Math.floor(faltan / 3600)); m.textContent = dos(Math.floor(faltan % 3600 / 60)); s.textContent = dos(faltan % 60);
				etiqueta.textContent = reloj.getAttribute('data-t-antes');
				reloj.classList.remove('corte-pasado');
			} else {
				h.textContent = m.textContent = s.textContent = '00';
				etiqueta.textContent = reloj.getAttribute('data-t-despues');
				reloj.classList.add('corte-pasado');
			}
		};
		tic();
		setInterval(tic, 1000);
	}

	var calc = document.querySelector('[data-calc-envio]');
	if (calc && CFG.tarifa) {
		var T = CFG.tarifa, iva = CFG.iva || 1.21, en = CFG.idioma === 'en';
		var rango = calc.querySelector('[data-calc-rango]'), cajas = calc.querySelector('[data-calc-cajas]');
		var euros = function (n) {
			var p = (Math.round(n * 100) / 100).toFixed(2).split('.');
			p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, en ? ',' : '.');
			return en ? '€' + p.join('.') : p.join(',') + ' €';
		};
		var pintar = function () {
			var kg = parseInt(rango.value, 10) || 1;
			var n = Math.max(1, parseInt(cajas.value, 10) || 1, Math.ceil(kg / 20));
			cajas.value = n;
			var f = Math.ceil(kg + (T.embalaje || 1) * n);
			var coste = f <= 2 ? T.hasta2 : f <= 5 ? T.hasta5 : f <= 10 ? T.hasta10 : T.hasta10 + (f - 10) * T.kgExtra;
			calc.querySelector('[data-calc-kg]').textContent = kg;
			calc.querySelector('[data-calc-coste]').textContent = euros(coste * iva);
			calc.querySelector('[data-calc-peso]').textContent = calc.getAttribute('data-t-peso').replace('%s', f);
			var tramo = f <= 2 ? 0 : f <= 5 ? 1 : f <= 10 ? 2 : 3;
			calc.querySelectorAll('[data-tramo]').forEach(function (li) { li.classList.toggle('activo', +li.getAttribute('data-tramo') === tramo); });
		};
		rango.addEventListener('input', function () { cajas.value = Math.ceil((parseInt(rango.value, 10) || 1) / 20); pintar(); });
		cajas.addEventListener('input', pintar);
		pintar();
	}
})();
