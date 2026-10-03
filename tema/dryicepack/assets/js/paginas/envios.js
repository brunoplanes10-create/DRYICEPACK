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

	/* Calculadora: cada posición del deslizador es un pedido real (cajas de 20 kg y el resto en el pack que toque).
	   Los importes vienen calculados del servidor con la misma función que cobra el checkout; aquí solo se pintan.
	   La última posición es "más de 150 kg": sin precio, se prepara a medida. */
	var calc = document.querySelector('[data-calc-envio]');
	var paradas = null;
	try { paradas = calc && JSON.parse(calc.getAttribute('data-paradas')); } catch (e) { paradas = null; }
	if (calc && paradas && paradas.length) {
		var rango = calc.querySelector('[data-calc-rango]');
		var salidaKg = calc.querySelector('[data-calc-kg]');
		var pila = calc.querySelector('[data-calc-cajas]');
		var texto = calc.querySelector('[data-calc-texto]');
		var campos = calc.querySelectorAll('[data-calc]');
		var num = function (n) { return CFG.idioma === 'en' ? String(n) : String(n).replace('.', ','); };
		var tam = function (kg) { return kg >= 15 ? 'g' : kg >= 10 ? 'm' : 'p'; };
		var antes = (function () { var a = []; pila.querySelectorAll('.v-caja').forEach(function (c) { a.push(c.textContent); }); return a; })();
		var dibujar = function (cajas, quietas) {
			pila.textContent = '';
			cajas.forEach(function (kg, i) {
				var c = document.createElement('span');
				c.className = 'v-caja v-caja--' + tam(kg) + (!quietas && antes[i] !== num(kg) ? ' nueva' : '');
				c.textContent = num(kg);
				pila.appendChild(c);
			});
			antes = cajas.map(num);
		};
		var pintar = function () {
			var i = Math.max(0, Math.min(paradas.length, parseInt(rango.value, 10) || 0));
			var p = paradas[i]; // en la última posición no hay parada: más de 150 kg
			calc.classList.toggle('es-medida', !p);
			if (!p) {
				salidaKg.textContent = calc.getAttribute('data-t-mas-cifra');
				rango.setAttribute('aria-valuetext', calc.getAttribute('data-t-mas'));
				texto.textContent = calc.getAttribute('data-t-medida-cajas');
				dibujar(paradas[paradas.length - 1].cajas, true); // las de 150 kg en fantasma: a partir de ahí, a medida
				return;
			}
			salidaKg.textContent = num(p.kg);
			rango.setAttribute('aria-valuetext', p.hielo + ' · ' + p.texto);
			texto.textContent = p.texto;
			dibujar(p.cajas);
			campos.forEach(function (el) { var k = el.getAttribute('data-calc'); if (p[k] != null) el.textContent = p[k]; });
			enlazar(p);
		};
		/* Botón "Pedir 75 kg": deja en el carrito justo estas cajas (3 de 20 kg + 1 de 15 kg) en el formato elegido y va al pago */
		var pedir = calc.querySelector('[data-calc-pedir]');
		var pedirTexto = calc.querySelector('[data-calc-pedir-texto]');
		var formatos = calc.querySelectorAll('[data-calc-formato]');
		var enlazar = function (p) {
			if (!pedir || !p) return;
			var grupos = {};
			p.cajas.forEach(function (kg) { grupos[kg] = (grupos[kg] || 0) + 1; });
			var partes = Object.keys(grupos).sort(function (a, b) { return b - a; }).map(function (kg) { return kg + 'x' + grupos[kg]; });
			var formato = '3mm';
			formatos.forEach(function (f) { if (f.checked) formato = f.value; });
			pedir.href = pedir.getAttribute('data-base') + '?dipt_pedido=' + encodeURIComponent(partes.join(',')) + '&dipt_formato=' + formato;
			pedirTexto.textContent = pedir.getAttribute('data-t-boton').replace('%s', num(p.kg));
		};
		formatos.forEach(function (f) { f.addEventListener('change', pintar); });
		rango.addEventListener('input', pintar);
		pintar();
	}
})();
