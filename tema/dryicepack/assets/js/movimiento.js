/* Dryicepack · movimiento e interfaz comunes a toda la web.
   Aparición al entrar en pantalla, cabecera, menús, parallax, contadores, reloj del corte de las 12:00,
   imagen que sigue al cursor, tarjeta que se inclina, carrito y formularios sin recargar.
   Solo transform y opacity. Con "reducir movimiento" (clase .quieto) no se anima nada. */
(function () {
	'use strict';
	var d = document, h = d.documentElement, w = window;
	var CFG = w.DIPT || {};
	var quieto = h.classList.contains('quieto');
	var raf = w.requestAnimationFrame || function (f) { return setTimeout(f, 16); };

	/* ---------- Animaciones ambientales solo cuando la página ya ha cargado (no compiten con el LCP) ---------- */
	function activar() { if (!quieto) h.classList.add('anima'); }
	if (d.readyState === 'complete') setTimeout(activar, 200); else w.addEventListener('load', function () { setTimeout(activar, 200); }, { once: true });

	/* ---------- Aparición al entrar en pantalla ---------- */
	var aparecen = d.querySelectorAll('[data-revela], [data-lineas], [data-cortina], [data-vivo]');
	if ('IntersectionObserver' in w && !quieto) {
		var io = new IntersectionObserver(function (entradas) {
			entradas.forEach(function (e) {
				if (!e.isIntersecting) return;
				e.target.classList.add('visto');
				io.unobserve(e.target);
			});
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
		aparecen.forEach(function (el) { io.observe(el); });
	} else {
		aparecen.forEach(function (el) { el.classList.add('visto'); });
	}

	/* ---------- Cabecera: sólida al bajar, se esconde al bajar y vuelve al subir ---------- */
	var cab = d.querySelector('[data-cabecera]');
	var ultimoY = 0, pendiente = false;
	function alDesplazar() {
		var y = w.scrollY || 0;
		if (cab) {
			cab.classList.toggle('es-solida', y > 24);
			var bajando = y > ultimoY && y > 260 && !h.classList.contains('menu-abierto') && !cab.querySelector('.menu__item.abierto');
			cab.classList.toggle('se-esconde', bajando);
		}
		ultimoY = y;
		pendiente = false;
		paralaje();
	}
	w.addEventListener('scroll', function () { if (!pendiente) { pendiente = true; raf(alDesplazar); } }, { passive: true });

	/* ---------- Menú móvil ---------- */
	var botonMenu = d.querySelector('[data-menu-movil]');
	if (botonMenu) {
		botonMenu.addEventListener('click', function () {
			var abierto = h.classList.toggle('menu-abierto');
			botonMenu.setAttribute('aria-expanded', abierto ? 'true' : 'false');
		});
		d.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && h.classList.contains('menu-abierto')) { h.classList.remove('menu-abierto'); botonMenu.setAttribute('aria-expanded', 'false'); botonMenu.focus(); }
		});
	}

	/* ---------- Submenú "Usos": clic, teclado y paso del ratón en escritorio; la foto cambia con cada uso ---------- */
	d.querySelectorAll('[data-submenu]').forEach(function (item) {
		var boton = item.querySelector('button');
		var timer;
		function abrir(v) { item.classList.toggle('abierto', v); boton.setAttribute('aria-expanded', v ? 'true' : 'false'); }
		boton.addEventListener('click', function () { abrir(!item.classList.contains('abierto')); });
		if (w.matchMedia('(hover: hover) and (min-width: 901px)').matches) {
			item.addEventListener('mouseenter', function () { clearTimeout(timer); abrir(true); });
			item.addEventListener('mouseleave', function () { timer = setTimeout(function () { abrir(false); }, 180); });
		}
		d.addEventListener('click', function (e) { if (!item.contains(e.target)) abrir(false); });
		item.addEventListener('keydown', function (e) { if (e.key === 'Escape') { abrir(false); boton.focus(); } });
		item.querySelectorAll('[data-foto]').forEach(function (a) {
			function mostrar() {
				item.querySelectorAll('.submenu__foto img').forEach(function (img) { img.classList.toggle('activa', img.getAttribute('data-foto-id') === a.getAttribute('data-foto')); });
			}
			a.addEventListener('mouseenter', mostrar);
			a.addEventListener('focus', mostrar);
		});
	});

	/* ---------- Parallax suave: data-paralaje="0.15" (fracción del desplazamiento) ---------- */
	var capas = quieto ? [] : Array.prototype.slice.call(d.querySelectorAll('[data-paralaje]'));
	var visibles = new Set();
	if (capas.length && 'IntersectionObserver' in w) {
		var ioP = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) visibles.add(e.target); else visibles.delete(e.target); }); }, { rootMargin: '20% 0px' });
		capas.forEach(function (c) { ioP.observe(c); });
	}
	function paralaje() {
		if (!visibles.size) return;
		var alto = w.innerHeight;
		visibles.forEach(function (el) {
			var r = el.getBoundingClientRect();
			var centro = r.top + r.height / 2 - alto / 2;
			var f = parseFloat(el.getAttribute('data-paralaje')) || 0.1;
			el.style.transform = 'translate3d(0,' + (centro * f).toFixed(1) + 'px,0)';
		});
	}

	/* ---------- Contadores: data-cuenta="-78.5" data-decimales="1" ---------- */
	function formatear(n, dec) {
		var s = Math.abs(n).toFixed(dec).replace('.', ',');
		return (n < 0 ? '−' : '') + s;
	}
	var contadores = d.querySelectorAll('[data-cuenta]');
	if (contadores.length && 'IntersectionObserver' in w) {
		var ioC = new IntersectionObserver(function (es) {
			es.forEach(function (e) {
				if (!e.isIntersecting) return;
				ioC.unobserve(e.target);
				var el = e.target, fin = parseFloat(el.getAttribute('data-cuenta')), dec = parseInt(el.getAttribute('data-decimales') || '0', 10);
				var inicio = parseFloat(el.getAttribute('data-desde') || '0');
				if (quieto) { el.textContent = formatear(fin, dec); return; }
				var t0 = performance.now(), dur = parseInt(el.getAttribute('data-duracion') || '2200', 10);
				(function paso(t) {
					var p = Math.min(1, (t - t0) / dur), suave = 1 - Math.pow(1 - p, 4);
					el.textContent = formatear(inicio + (fin - inicio) * suave, dec);
					if (p < 1) raf(paso);
				})(t0);
			});
		}, { threshold: 0.4 });
		contadores.forEach(function (c) { ioC.observe(c); });
	}

	/* ---------- Fechas reales de entrega (misma regla que el checkout): corte 12:00 Madrid, sin domingos ni lunes, sin festivos ---------- */
	var DIAS = { es: ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'], ca: ['diumenge', 'dilluns', 'dimarts', 'dimecres', 'dijous', 'divendres', 'dissabte'], en: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] };
	var MESES = { es: ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'], ca: ['gener', 'febrer', 'març', 'abril', 'maig', 'juny', 'juliol', 'agost', 'setembre', 'octubre', 'novembre', 'desembre'], en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] };
	var idioma = CFG.idioma || 'es';
	var festivos = CFG.festivos || [];
	function ahoraMadrid() {
		var p = {};
		try {
			new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Madrid', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })
				.formatToParts(new Date()).forEach(function (x) { p[x.type] = x.value; });
			return { y: +p.year, m: +p.month, d: +p.day, h: +p.hour % 24, mi: +p.minute, s: +p.second };
		} catch (e) { var n = new Date(); return { y: n.getFullYear(), m: n.getMonth() + 1, d: n.getDate(), h: n.getHours(), mi: n.getMinutes(), s: n.getSeconds() }; }
	}
	function clave(f) { return f.getUTCFullYear() + '-' + ('0' + (f.getUTCMonth() + 1)).slice(-2) + '-' + ('0' + f.getUTCDate()).slice(-2); }
	function esFestivo(f) { return festivos.indexOf(clave(f)) !== -1; }
	function saleEseDia(f) { var n = f.getUTCDay(); return n >= 1 && n <= 5 && !esFestivo(f); }
	function antesCorte(a) { var c = CFG.corte || { hora: 12, minuto: 0 }; return a.h * 60 + a.mi < c.hora * 60 + c.minuto; }
	/* Devuelve { entrega: Date, saleHoy: bool } */
	function proximaEntrega() {
		var a = ahoraMadrid();
		var dia = new Date(Date.UTC(a.y, a.m - 1, a.d));
		var saleHoy = saleEseDia(dia) && antesCorte(a);
		if (!saleHoy) dia.setUTCDate(dia.getUTCDate() + 1);
		for (var i = 0; i < 40; i++, dia.setUTCDate(dia.getUTCDate() + 1)) {
			if (!saleEseDia(dia)) continue;
			var llega = new Date(dia.getTime()); llega.setUTCDate(llega.getUTCDate() + 1);
			if (llega.getUTCDay() === 6 || esFestivo(llega)) continue; // el sábado es opcional y con suplemento
			return { entrega: llega, saleHoy: saleHoy && i === 0 };
		}
		return null;
	}
	function fechaTexto(f) {
		var dias = DIAS[idioma] || DIAS.es, meses = MESES[idioma] || MESES.es;
		if (idioma === 'en') return dias[f.getUTCDay()] + ' ' + f.getUTCDate() + ' ' + meses[f.getUTCMonth()];
		if (idioma === 'ca') { var m = f.getUTCMonth(); return dias[f.getUTCDay()] + ' ' + f.getUTCDate() + ([3, 7, 9].indexOf(m) !== -1 ? " d'" : ' de ') + meses[m]; }
		return dias[f.getUTCDay()] + ' ' + f.getUTCDate() + ' de ' + meses[f.getUTCMonth()];
	}
	w.dipEntrega = { proxima: proximaEntrega, texto: fechaTexto, ahora: ahoraMadrid };

	/* Reloj del corte: [data-corte] con data-t-antes, data-t-quedan y data-t-despues (%s = fecha o tiempo) */
	function pintarCorte() {
		var r = proximaEntrega(); if (!r) return;
		var a = ahoraMadrid();
		d.querySelectorAll('[data-corte]').forEach(function (el) {
			var fecha = fechaTexto(r.entrega);
			var dest = el.querySelector('[data-corte-texto]') || el;
			var quedan = el.querySelector('[data-corte-quedan]');
			if (r.saleHoy) {
				dest.textContent = (el.getAttribute('data-t-antes') || '%s').replace('%s', fecha);
				if (quedan) {
					var min = (CFG.corte ? CFG.corte.hora : 12) * 60 - (a.h * 60 + a.mi);
					var hh = Math.floor(min / 60), mm = min % 60;
					quedan.textContent = (el.getAttribute('data-t-quedan') || '%s').replace('%s', (hh ? hh + ' h ' : '') + mm + ' min');
					quedan.hidden = false;
				}
			} else {
				dest.textContent = (el.getAttribute('data-t-despues') || '%s').replace('%s', fecha);
				if (quedan) quedan.hidden = true;
			}
			el.classList.add('corte-listo');
		});
	}
	if (d.querySelector('[data-corte]')) { pintarCorte(); setInterval(pintarCorte, 30000); }

	/* ---------- Imagen que sigue al cursor en listas índice: [data-sigue] con hijos [data-sigue-foto] ---------- */
	d.querySelectorAll('[data-sigue]').forEach(function (lista) {
		var visor = lista.querySelector('[data-sigue-visor]');
		if (!visor || quieto || !w.matchMedia('(hover: hover) and (min-width: 901px)').matches) return;
		var x = 0, y = 0, tx = 0, ty = 0, activo = false;
		function mover() {
			tx += (x - tx) * 0.14; ty += (y - ty) * 0.14;
			visor.style.transform = 'translate3d(' + tx.toFixed(1) + 'px,' + ty.toFixed(1) + 'px,0)';
			if (activo) raf(mover);
		}
		lista.addEventListener('mousemove', function (e) { var r = lista.getBoundingClientRect(); x = e.clientX - r.left; y = e.clientY - r.top; });
		lista.addEventListener('mouseenter', function (e) { var r = lista.getBoundingClientRect(); x = tx = e.clientX - r.left; y = ty = e.clientY - r.top; activo = true; visor.classList.add('activo'); raf(mover); });
		lista.addEventListener('mouseleave', function () { activo = false; visor.classList.remove('activo'); });
		lista.querySelectorAll('[data-sigue-foto]').forEach(function (fila) {
			fila.addEventListener('mouseenter', function () {
				var id = fila.getAttribute('data-sigue-foto');
				visor.querySelectorAll('img').forEach(function (img) { img.classList.toggle('activa', img.getAttribute('data-id') === id); });
			});
		});
	});

	/* ---------- Tarjeta que se inclina con el cursor: [data-inclina] ---------- */
	d.querySelectorAll('[data-inclina]').forEach(function (t) {
		if (quieto || !w.matchMedia('(hover: hover)').matches) return;
		var marco = t.parentElement;
		marco.addEventListener('mousemove', function (e) {
			var r = marco.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - 0.5, py = (e.clientY - r.top) / r.height - 0.5;
			t.style.transform = 'rotateY(' + (px * 14).toFixed(2) + 'deg) rotateX(' + (-py * 12).toFixed(2) + 'deg)';
			t.style.setProperty('--brillo-x', ((px + 0.5) * 100).toFixed(0) + '%');
			t.style.setProperty('--brillo-y', ((py + 0.5) * 100).toFixed(0) + '%');
		});
		marco.addEventListener('mouseleave', function () { t.style.transform = ''; });
	});

	/* ---------- Contador del carrito (solo si hay algo en el carrito) ---------- */
	var burbuja = d.querySelector('[data-carrito-n]');
	if (burbuja && /(?:^|; )woocommerce_items_in_cart=1/.test(d.cookie) && CFG.urls) {
		fetch(CFG.urls.ajax + '?action=dipt_carrito', { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			var n = j && j.data ? j.data.cantidad : 0;
			burbuja.textContent = n; burbuja.setAttribute('data-carrito-n', String(n));
		}).catch(function () {});
	}

	/* ---------- Formularios del tema: envío sin recargar (con recarga normal si no hay JS) ---------- */
	d.querySelectorAll('form[data-form-dip]').forEach(function (f) {
		var aviso = f.querySelector('[data-form-aviso]');
		var boton = f.querySelector('[type="submit"]');
		f.addEventListener('submit', function (e) {
			if (!w.fetch || !w.FormData) return;
			e.preventDefault();
			var datos = new FormData(f); datos.append('ajax', '1');
			f.querySelectorAll('.campo--error').forEach(function (c) { c.classList.remove('campo--error'); var m = c.querySelector('.campo__error'); if (m) m.remove(); });
			if (boton) { boton.disabled = true; boton.setAttribute('data-texto', boton.textContent); boton.querySelector('span') ? boton.querySelector('span').textContent = f.getAttribute('data-enviando') || '…' : 0; }
			fetch(f.action, { method: 'POST', body: datos, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
				if (aviso) { aviso.hidden = false; aviso.className = 'aviso-form ' + (j.ok ? 'aviso-form--ok' : 'aviso-form--error'); aviso.textContent = j.mensaje; }
				if (j.ok) {
					f.classList.add('enviado');
					f.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), textarea').forEach(function (i) { i.value = ''; });
					if (w.dipMedir) w.dipMedir('generate_lead', { form: datos.get('dip_tipo') });
				} else if (j.errores) {
					Object.keys(j.errores).forEach(function (n) {
						var input = f.querySelector('[name="' + n + '"]'); if (!input) return;
						var campo = input.closest('.campo, .casilla, .frase__hueco') || input.parentNode;
						campo.classList.add('campo--error');
						var m = d.createElement('span'); m.className = 'campo__error'; m.textContent = j.errores[n]; campo.appendChild(m);
					});
				}
			}).catch(function () {
				if (aviso) { aviso.hidden = false; aviso.className = 'aviso-form aviso-form--error'; aviso.textContent = f.getAttribute('data-error') || 'Error'; }
			}).finally(function () {
				if (boton) { boton.disabled = false; var s = boton.querySelector('span'); if (s) s.textContent = f.getAttribute('data-enviar') || s.textContent; }
				if (aviso) aviso.focus && aviso.focus();
			});
		});
	});

	alDesplazar();
})();
