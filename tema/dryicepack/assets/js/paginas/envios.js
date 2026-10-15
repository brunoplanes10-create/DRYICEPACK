/* Envíos: cuenta atrás hasta el corte de las 12:00 (hora de Madrid), calculadora con la tarifa real y código postal
   del mapa de cobertura. Solo transform y opacity; con .quieto o "reducir movimiento", sin animaciones. */
(function () {
	'use strict';
	var CFG = window.DIPT || {}, E = window.dipEntrega;
	var quieto = document.documentElement.classList.contains('quieto') || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

	/* ---------- V1 · Reloj ----------
	   Antes del corte cuenta hasta las 12:00 de hoy. Pasado el corte (o en fin de semana o festivo) cuenta hasta el
	   siguiente corte válido: el día que sale el próximo pedido, que es la víspera de la primera entrega de
	   dipEntrega.proxima() (la misma regla que el checkout: sin domingos ni lunes de entrega, sin festivos ni cierres
	   de MRW Mataró). Debajo, ese día y la hora en mono. Los dígitos que cambian hacen un fundido de 150 ms. */
	var reloj = document.querySelector('[data-cuenta-atras]');
	if (reloj && E) {
		var h = reloj.querySelector('[data-h]'), m = reloj.querySelector('[data-m]'), s = reloj.querySelector('[data-s]');
		var etiqueta = reloj.querySelector('[data-reloj-etiqueta]');
		var hasta = reloj.querySelector('[data-reloj-hasta]');
		var hora = reloj.getAttribute('data-hora') || '12:00';
		var corteH = CFG.corte ? CFG.corte.hora : 12, corteM = CFG.corte ? CFG.corte.minuto : 0;
		var fmt = null;
		try { fmt = new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Madrid', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }); } catch (e) { fmt = null; }
		// Diferencia entre la hora de Madrid y UTC en ese instante (cambia con el horario de verano)
		var desfase = function (ms) {
			if (!fmt) return -new Date(ms).getTimezoneOffset() * 60000;
			var p = {};
			fmt.formatToParts(new Date(ms)).forEach(function (x) { p[x.type] = x.value; });
			return Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour % 24, +p.minute, +p.second) - ms;
		};
		var dos = function (n) { return n < 10 ? '0' + n : String(n); };
		// Fuera de pantalla el reloj no se repinta ni anima; al volver, se pone en hora al momento
		var enPantalla = true;
		var poner = function (el, v) {
			if (!el || el.textContent === v) return;
			el.textContent = v;
			if (!quieto && el.animate) el.animate([{ opacity: 0.25, transform: 'translate3d(0,-.08em,0)' }, { opacity: 1, transform: 'none' }], { duration: 150, easing: 'ease-out' });
		};
		var tic = function () {
			var r = E.proxima();
			if (!r) return;
			var sale = new Date(r.entrega.getTime());
			sale.setUTCDate(sale.getUTCDate() - 1);
			var pared = Date.UTC(sale.getUTCFullYear(), sale.getUTCMonth(), sale.getUTCDate(), corteH, corteM, 0);
			var faltan = Math.max(0, Math.floor((pared - desfase(pared) - Date.now()) / 1000));
			poner(h, dos(Math.floor(faltan / 3600)));
			poner(m, dos(Math.floor(faltan % 3600 / 60)));
			poner(s, dos(faltan % 60));
			etiqueta.textContent = reloj.getAttribute(r.saleHoy ? 'data-t-antes' : 'data-t-despues');
			reloj.classList.toggle('corte-pasado', !r.saleHoy);
			var t = E.texto(sale) + ' · ' + hora;
			if (hasta && hasta.textContent !== t) hasta.textContent = t;
		};
		tic();
		setInterval(function () { if (enPantalla) tic(); }, 1000);
		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (es) {
				enPantalla = es[es.length - 1].isIntersecting;
				if (enPantalla) tic();
			}).observe(reloj);
		}
	}

	/* ---------- V3 · Calculadora ----------
	   Cada posición del deslizador es un pedido real (cajas de 20 kg y el resto en el pack que toque).
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
		// Las cajas que se añaden caen (240 ms) una detrás de otra; las que siguen igual no se mueven
		var dibujar = function (cajas, quietas) {
			pila.textContent = '';
			var nuevas = 0;
			cajas.forEach(function (kg, i) {
				var c = document.createElement('span');
				var nueva = !quietas && antes[i] !== num(kg);
				c.className = 'v-caja v-caja--' + tam(kg) + (nueva ? ' nueva' : '');
				if (nueva) c.style.setProperty('--n', String(nuevas++));
				c.textContent = num(kg);
				pila.appendChild(c);
			});
			antes = cajas.map(num);
		};
		var pedir = calc.querySelector('[data-calc-pedir]');
		var pedirTexto = calc.querySelector('[data-calc-pedir-texto]');
		var formatos = calc.querySelectorAll('[data-calc-formato]');
		/* Botón "Pedir 75 kg": deja en el carrito justo estas cajas (3 de 20 kg + 1 de 15 kg) en el formato elegido y va al pago */
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
		var pintar = function () {
			var i = Math.max(0, Math.min(paradas.length, parseInt(rango.value, 10) || 0));
			var p = paradas[i]; // en la última posición no hay parada: más de 150 kg
			calc.classList.toggle('es-medida', !p);
			if (!p) {
				salidaKg.textContent = calc.getAttribute('data-t-mas-cifra');
				rango.setAttribute('aria-valuetext', calc.getAttribute('data-t-mas'));
				texto.textContent = calc.getAttribute('data-t-medida-cajas');
				dibujar(paradas[paradas.length - 1].cajas, true); // las de 150 kg en contorno: a partir de ahí, a medida
				return;
			}
			salidaKg.textContent = num(p.kg);
			rango.setAttribute('aria-valuetext', p.hielo + ' · ' + p.texto);
			texto.textContent = p.texto;
			dibujar(p.cajas);
			campos.forEach(function (el) { var k = el.getAttribute('data-calc'); if (p[k] != null) el.textContent = p[k]; });
			enlazar(p);
		};
		formatos.forEach(function (f) { f.addEventListener('change', pintar); });
		rango.addEventListener('input', pintar);
		pintar();
	}

	/* ---------- V5 · Código postal en el mapa de cobertura ----------
	   Con 5 cifras pregunta al servidor (admin-ajax · dipt_entrega_cp, el mismo de la ficha de compra) la provincia y
	   la primera entrega para ese destino. Si el servidor no contesta, responde con lo que ya sabe la página:
	   prefijo de provincia, destinos fuera de la península y la fecha general. Los textos son los de la ficha. */
	var caja = document.querySelector('[data-cp]');
	if (caja) {
		var campo = caja.querySelector('[data-cp-campo]');
		var res = caja.querySelector('[data-cp-res]');
		var seccion = caja.closest('[data-cobertura]');
		var FUERA = ['07', '35', '38', '51', '52'];
		var cache = {}, turno = 0, espera = 0;
		var tx = function (k) { return caja.getAttribute('data-t-' + k) || ''; };
		var valido = function (v) { var n = parseInt(v.slice(0, 2), 10); return n >= 1 && n <= 52; };
		var local = function (v) {
			var p = E && E.proxima();
			return { cp: v, provincia: '', fuera: FUERA.indexOf(v.slice(0, 2)) !== -1, entrega: p ? { texto: E.texto(p.entrega) } : null };
		};
		var mostrar = function (estado, r) {
			var t = tx('ayuda');
			if (estado === 'cargando') t = tx('buscando');
			else if (estado === 'corto') t = tx('corto');
			else if (estado === 'error') t = tx('error').replace('%s', campo.value);
			else if (r) {
				var lugar = r.cp + (r.provincia ? ' · ' + r.provincia : '');
				if (r.fuera) t = lugar + ' — ' + tx('fuera');
				else if (r.entrega) t = tx('ok').replace('%1$s', lugar).replace('%2$s', r.entrega.texto);
			}
			caja.setAttribute('data-estado', r ? (r.fuera ? 'fuera' : 'ok') : estado);
			if (seccion) seccion.classList.toggle('es-fuera', !!(r && r.fuera));
			if (estado === 'error') campo.setAttribute('aria-invalid', 'true'); else campo.removeAttribute('aria-invalid');
			if (res.textContent !== t) res.textContent = t;
		};
		var consultar = function (v) {
			var mio = ++turno;
			var url = caja.getAttribute('data-ajax'), nonce = caja.getAttribute('data-nonce');
			if (!url || !nonce || !window.fetch) { mostrar('', local(v)); return; }
			var limite = setTimeout(function () { if (mio === turno) mostrar('', local(v)); }, 8000);
			fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=dipt_entrega_cp&nonce=' + encodeURIComponent(nonce) + '&cp=' + v + '&kg=0&idioma=' + encodeURIComponent(CFG.idioma || 'es') })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					clearTimeout(limite);
					if (mio !== turno) return;
					if (j && j.success && j.data) { cache[v] = j.data; mostrar('', j.data); }
					else if (j && j.data && j.data.codigo === 'cp_no_valido') { cache[v] = null; mostrar('error'); }
					else mostrar('', local(v)); // nonce caducado u otro error: datos de la página
				})
				.catch(function () { clearTimeout(limite); if (mio === turno) mostrar('', local(v)); });
		};
		campo.addEventListener('input', function () {
			var v = campo.value.replace(/\D/g, '').slice(0, 5);
			if (campo.value !== v) campo.value = v;
			clearTimeout(espera); turno++;
			if (v.length < 5) { mostrar(''); return; }
			if (!valido(v)) { mostrar('error'); return; }
			if (v in cache) { if (cache[v]) mostrar('', cache[v]); else mostrar('error'); return; }
			mostrar('cargando');
			espera = setTimeout(function () { consultar(v); }, 250);
		});
		campo.addEventListener('blur', function () { if (campo.value.length > 0 && campo.value.length < 5) mostrar('corto'); });
		campo.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); campo.blur(); } });
	}

	/* ---------- V6 · En móvil las pestañas son un acordeón: tocar la pregunta abierta (×) la cierra ----------
	   Las pestañas comunes (movimiento.js) siempre dejan una abierta. Aquí, antes de su clic (captura) se mira si la
	   pregunta ya estaba abierta y, después (burbuja), se cierra. En escritorio no cambia nada. */
	var pest = document.querySelector('.v-pestanas[data-pestanas]');
	if (pest && window.matchMedia) {
		var movil = window.matchMedia('(max-width: 800px)');
		var abierta = null;
		var tabDe = function (e) { return e.target.closest ? e.target.closest('[role="tab"]') : null; };
		pest.addEventListener('click', function (e) { var t = tabDe(e); abierta = t && t.getAttribute('aria-selected') === 'true' ? t : null; }, true);
		pest.addEventListener('click', function (e) {
			var t = tabDe(e);
			if (!t || t !== abierta || !movil.matches) return;
			var panel = document.getElementById(t.getAttribute('aria-controls'));
			if (panel) panel.hidden = true;
			t.setAttribute('aria-selected', 'false');
		});
		// Al pasar a escritorio sin ninguna abierta, se abre la que tiene el foco del teclado (tabindex 0)
		var alCambiar = function () {
			if (movil.matches || pest.querySelector('[role="tab"][aria-selected="true"]')) return;
			var t = pest.querySelector('[role="tab"][tabindex="0"]');
			if (t) t.click();
		};
		if (movil.addEventListener) movil.addEventListener('change', alCambiar);
	}
})();
