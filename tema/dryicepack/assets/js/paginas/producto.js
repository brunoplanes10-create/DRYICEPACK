/* Comprar: configurador (formato, kilos, cajas, código postal → precio final y fecha), comparador y barra fija en móvil.
   El envío se calcula con la misma tarifa que cobra el checkout (plugin dryicepack-tienda).
   Código postal: con 5 cifras se pregunta al servidor (admin-ajax · dipt_entrega_cp) la provincia, la primera entrega
   para ese destino (festivos y cierres de MRW) y los avisos. Si tarda más de 1,5 s, la ficha confirma el código con
   los datos que ya tiene (provincia por prefijo y fecha general) y la fecha exacta sustituye a la general al llegar.
   El código se guarda en sessionStorage y va con el pedido (campo dipt_cp) para que el checkout lo reciba escrito. */
(function () {
	'use strict';
	var CFG = window.DIPT || {};
	var raiz = document.querySelector('[data-compra]');
	if (raiz && CFG.variaciones) iniciarCompra();
	iniciarComparador();

	function iniciarCompra() {
		var form = raiz.querySelector('[data-compra-form]');
		var T = CFG.tarifa || {}, iva = CFG.iva || 1.21, en = CFG.idioma === 'en';
		var estado = { formato: raiz.getAttribute('data-formato'), kg: parseFloat(raiz.getAttribute('data-kg')), cajas: 1 };
		var maxKg = T.maxKg || 250;
		var FUERA = ['07', '35', '38', '51', '52'];
		var KG_MEDIDA = 150;

		function euros(n) {
			var s = (Math.round(n * 100) / 100).toFixed(2).split('.');
			s[0] = s[0].replace(/\B(?=(\d{3})+(?!\d))/g, en ? ',' : '.');
			return en ? '€' + s.join('.') : s.join(',') + ' €';
		}
		function envio(kg, cajas) {
			if (kg <= 0) return 0;
			var f = Math.ceil(kg + (T.embalaje || 1) * Math.max(1, cajas));
			if (f <= 2) return T.hasta2; if (f <= 5) return T.hasta5; if (f <= 10) return T.hasta10;
			return Math.round((T.hasta10 + (f - 10) * T.kgExtra) * 100) / 100;
		}
		function variacion() {
			for (var i = 0; i < CFG.variaciones.length; i++) {
				var v = CFG.variaciones[i];
				if (Number(v.kg) === estado.kg && v.formato === estado.formato) return v;
			}
			return null;
		}
		function campo(n, valor) { var el = form.querySelector('[data-campo="' + n + '"]'); if (el) el.value = valor; }
		function kgTotal() { return estado.kg * estado.cajas; }

		var inputCajas = form.querySelector('[data-cajas-input]');
		var llega = raiz.querySelector('[data-llega]');
		var llegaTexto = raiz.querySelector('[data-llega-texto]');
		var llegaExtra = raiz.querySelector('[data-llega-extra]');
		var aviso = raiz.querySelector('[data-max]');
		var notaMedida = raiz.querySelector('[data-medida]');

		function pintar() {
			var v = variacion(); if (!v) return;
			var maxCajas = Math.max(1, Math.floor(maxKg / estado.kg));
			if (estado.cajas > maxCajas) estado.cajas = maxCajas;
			inputCajas.max = String(maxCajas);
			inputCajas.value = String(estado.cajas);
			if (aviso) aviso.hidden = estado.cajas < maxCajas;
			var pack = v.precio * iva * estado.cajas, env = envio(kgTotal(), estado.cajas) * iva;
			raiz.querySelector('[data-precio-concepto]').textContent = raiz.querySelector('[data-precio-concepto]').textContent.split('·')[0].trim() + ' · ' + (estado.cajas > 1 ? estado.cajas + ' × ' : '') + estado.kg + ' kg';
			raiz.querySelector('[data-precio-pack]').textContent = euros(pack);
			raiz.querySelector('[data-precio-envio]').textContent = euros(env);
			document.querySelectorAll('[data-precio-total]').forEach(function (el) { el.textContent = euros(pack + env); });
			campo('padre', v.padre || ''); campo('id', v.id); campo('peso', v.peso || ''); campo('fslug', v.fslug || '');
			// Galería del formato elegido
			document.querySelectorAll('[data-galeria-formato]').forEach(function (g) {
				var on = g.getAttribute('data-galeria-formato') === estado.formato;
				g.hidden = !on; g.classList.toggle('activo', on);
				if (on) g.querySelectorAll('img[loading="lazy"]').forEach(function (img) { img.loading = 'eager'; });
			});
			// Los kilos cambian el aviso de envío a medida: se recalcula sin volver a preguntar al servidor
			pintarCp();
		}

		/* ---------- Código postal ---------- */
		var cpInput = form.querySelector('[data-cp]');
		var cpCaja = raiz.querySelector('[data-cp-caja]');
		var cpNota = raiz.querySelector('[data-cp-nota]');
		var cp = { res: null, error: '', cargando: false, cache: {}, espera: 0, provisional: 0, limite: 0, ctrl: null, turno: 0, pintado: '' };
		var CLAVE = 'dipt_cp';

		function tx(el, k) { return (el && el.getAttribute('data-t-' + k)) || ''; }
		function prefijoValido(v) { var n = parseInt(v.slice(0, 2), 10); return n >= 1 && n <= 52; }
		function esMedida(r) { return !!r && !r.fuera && !r.barcelona && kgTotal() > (r.kgMedida || KG_MEDIDA) + 0.0001; }
		function guardar(v) { try { if (v) sessionStorage.setItem(CLAVE, v); else sessionStorage.removeItem(CLAVE); } catch (e) { /* sin almacenamiento: no pasa nada */ } }
		function leer() { try { return sessionStorage.getItem(CLAVE) || ''; } catch (e) { return ''; } }

		// Respaldo con lo que ya tiene la página: provincia por prefijo (la manda el servidor) y fecha general del reloj del tema
		function local(v) {
			if (!prefijoValido(v)) return null;
			var pre = v.slice(0, 2), p = window.dipEntrega && window.dipEntrega.proxima();
			return {
				cp: v, provincia: (CFG.cpProvincias || {})[pre] || '', barcelona: pre === '08', fuera: FUERA.indexOf(pre) !== -1, kgMedida: KG_MEDIDA,
				entrega: p ? { fecha: '', texto: window.dipEntrega.texto(p.entrega) } : null, sabado: null, local: true
			};
		}

		function recibir(v, r, final) {
			if (cpInput.value !== v) return; // ya ha escrito otro código
			if (final) { clearTimeout(cp.provisional); clearTimeout(cp.limite); }
			cp.cargando = false;
			cp.res = r;
			cp.error = r ? '' : 'no_valido';
			guardar(r ? v : '');
			pintarCp();
		}

		function consultar(v) {
			var turno = ++cp.turno;
			if (cp.ctrl) cp.ctrl.abort();
			if (!CFG.cpNonce || !CFG.urls || !CFG.urls.ajax || !window.fetch) { recibir(v, local(v), true); return; }
			var ctrl = cp.ctrl = 'AbortController' in window ? new AbortController() : null;
			// Si el servidor tarda, se confirma ya con los datos locales; la respuesta, si llega, los afina
			cp.provisional = setTimeout(function () { if (turno === cp.turno && cp.cargando) recibir(v, local(v), false); }, 1250);
			cp.limite = setTimeout(function () { if (turno !== cp.turno) return; if (ctrl) ctrl.abort(); if (cp.cargando) recibir(v, local(v), true); }, 20000);
			var datos = 'action=dipt_entrega_cp&nonce=' + encodeURIComponent(CFG.cpNonce) + '&cp=' + v + '&kg=' + encodeURIComponent(kgTotal()) + '&idioma=' + encodeURIComponent(CFG.idioma || 'es');
			fetch(CFG.urls.ajax, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: datos, signal: ctrl ? ctrl.signal : undefined })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					if (turno !== cp.turno) return;
					if (j && j.success && j.data) { cp.cache[v] = j.data; recibir(v, j.data, true); }
					else if (j && j.data && j.data.codigo === 'cp_no_valido') { cp.cache[v] = null; recibir(v, null, true); }
					else if (cp.cargando || (cp.res && cp.res.local)) recibir(v, local(v), true); // nonce caducado u otro error: datos locales
				})
				.catch(function () { if (turno === cp.turno && (cp.cargando || (cp.res && cp.res.local))) recibir(v, local(v), true); });
		}

		function alEscribir() {
			var v = cpInput.value.replace(/\D/g, '').slice(0, 5);
			if (cpInput.value !== v) cpInput.value = v;
			clearTimeout(cp.espera); clearTimeout(cp.provisional); clearTimeout(cp.limite);
			cp.turno++; if (cp.ctrl) { cp.ctrl.abort(); cp.ctrl = null; }
			cp.res = null; cp.error = ''; cp.cargando = false;
			if (v.length === 5) {
				if (!prefijoValido(v)) cp.error = 'no_valido';
				else if (v in cp.cache) { cp.res = cp.cache[v]; cp.error = cp.res ? '' : 'no_valido'; }
				else { cp.cargando = true; cp.espera = setTimeout(function () { consultar(v); }, 250); }
			}
			guardar(cp.res ? v : '');
			pintarCp();
		}

		function pintarCp() {
			if (!cpInput || !cpCaja) return;
			var r = cp.res, medida = esMedida(r);
			var e = cp.cargando ? 'cargando' : cp.error ? 'error' : r ? (r.fuera || medida ? 'aviso' : 'ok') : '';
			cpCaja.setAttribute('data-cp-estado', e);
			if (e === 'error') cpInput.setAttribute('aria-invalid', 'true'); else cpInput.removeAttribute('aria-invalid');
			var nota = tx(cpNota, 'ayuda');
			if (e === 'cargando') nota = tx(cpNota, 'buscando');
			else if (e === 'error') nota = tx(cpNota, cp.error === 'corto' ? 'corto' : 'no-valido');
			else if (r && r.fuera) nota = tx(cpNota, 'fuera');
			else if (medida) nota = tx(cpNota, 'medida');
			else if (r) nota = r.provincia || tx(cpNota, 'correcto');
			if (cpNota && cpNota.textContent !== nota) cpNota.textContent = nota;
			// Con un código válido el aviso de más de 150 kg va en la línea de entrega (y en la provincia de Barcelona no aplica)
			if (notaMedida) notaMedida.hidden = kgTotal() <= KG_MEDIDA || !(aviso && aviso.hidden) || !!r;
			pintarLlega(e, r, medida);
		}

		// Línea de entrega: fecha general sin código, fecha del destino con código, o aviso con enlace a WhatsApp
		function pintarLlega(e, r, medida) {
			llega.classList.toggle('cargando', e === 'cargando');
			if (e === 'cargando') return; // mientras busca, se queda el texto anterior atenuado
			var tipo = '', texto = '', extra = '', wa = false;
			var lugar = r ? r.cp + (r.provincia ? ' · ' + r.provincia : '') : '';
			if (e === 'error' && cp.error === 'no_valido') { tipo = 'aviso'; texto = tx(llega, 'error').replace('%s', cpInput.value); }
			// Fuera de la península no se envía: el aviso lo dice y no lleva enlace a WhatsApp
			else if (r && r.fuera) { tipo = 'aviso'; texto = lugar + ' — ' + tx(llega, 'fuera'); }
			else if (medida) { tipo = 'aviso'; texto = lugar + ' — ' + tx(llega, 'medida'); wa = true; }
			else if (r && r.entrega) {
				tipo = 'ok';
				texto = tx(llega, 'ok').replace('%1$s', lugar).replace('%2$s', r.entrega.texto);
				// El sábado solo se ofrece si llega antes que el primer día laborable (según zona y con suplemento)
				// Fecha corta ("sábado 3", "dissabte 3", "Saturday 3"): cabe en una línea también a 390 px
				if (r.sabado && r.entrega.fecha && r.sabado.fecha < r.entrega.fecha) extra = tx(llega, 'sabado').replace('%1$s', r.sabado.texto.split(' ').slice(0, 2).join(' ')).replace('%2$s', euros((T.sabado || 9.7) * iva));
			} else {
				var p = window.dipEntrega && window.dipEntrega.proxima();
				texto = p ? tx(llega, 'llega').replace('%s', window.dipEntrega.texto(p.entrega)) : llegaTexto.textContent;
			}
			llega.classList.toggle('es-ok', tipo === 'ok');
			llega.classList.toggle('es-aviso', tipo === 'aviso');
			// Solo se toca el texto si cambia: la región aria-live no repite lo mismo a cada clic en los kilos
			var clave = texto + '|' + extra + '|' + wa;
			if (clave === cp.pintado) return;
			cp.pintado = clave;
			llegaTexto.textContent = wa ? texto + ' ' : texto;
			if (wa) {
				var a = document.createElement('a');
				a.href = llega.getAttribute('data-wa'); a.target = '_blank'; a.rel = 'noopener'; a.textContent = 'WhatsApp';
				llegaTexto.appendChild(a);
			}
			if (llegaExtra) { llegaExtra.textContent = extra; llegaExtra.hidden = !extra; }
		}

		form.addEventListener('change', function (e) {
			var t = e.target;
			if (t.name === 'dipt_formato') estado.formato = t.value;
			if (t.name === 'dipt_kg') estado.kg = parseFloat(t.value);
			if (t.name === 'quantity') estado.cajas = Math.max(1, parseInt(t.value, 10) || 1);
			if (t !== cpInput) pintar();
		});
		form.querySelectorAll('[data-cajas]').forEach(function (b) {
			b.addEventListener('click', function () { estado.cajas = Math.max(1, estado.cajas + parseInt(b.getAttribute('data-cajas'), 10)); pintar(); });
		});
		if (cpInput) {
			cpInput.addEventListener('input', alEscribir);
			cpInput.addEventListener('blur', function () {
				var v = cpInput.value;
				if (v.length > 0 && v.length < 5) { cp.error = 'corto'; pintarCp(); }
			});
			// Enter (o "Ir" en el teclado del móvil) en el código postal no hace el pedido: cierra el teclado y deja ver la fecha
			cpInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); cpInput.blur(); } });
		}
		form.addEventListener('submit', function () {
			// Un código que no existe no viaja al checkout (uno que aún se está comprobando, sí: el servidor lo valida)
			if (cpInput && (cpInput.value.length !== 5 || cp.error)) cpInput.disabled = true;
			if (window.dipMedir) window.dipMedir('add_to_cart', { value: 0, items: [{ item_id: String(form.querySelector('[data-campo="id"]').value), quantity: estado.cajas }] });
		});
		// Al volver atrás desde el checkout, el campo vuelve a estar activo
		window.addEventListener('pageshow', function () { if (cpInput) cpInput.disabled = false; });

		// Barra fija en móvil: aparece cuando el botón principal sale de la pantalla
		var barra = document.querySelector('[data-barra-compra]');
		var botonPrincipal = raiz.querySelector('.p-comprar');
		if (barra && botonPrincipal && 'IntersectionObserver' in window) {
			new IntersectionObserver(function (es) { barra.classList.toggle('visible', !es[0].isIntersecting && es[0].boundingClientRect.top < 0); }).observe(botonPrincipal);
			barra.querySelector('[data-barra-comprar]').addEventListener('click', function () { form.requestSubmit ? form.requestSubmit() : form.submit(); });
		}
		pintar();
		// El código de esta visita (o el que rellena el navegador) se comprueba al cargar
		if (cpInput) {
			var guardado = leer();
			if (!cpInput.value && /^\d{5}$/.test(guardado)) cpInput.value = guardado;
			if (cpInput.value) alEscribir();
		}
	}

	function iniciarComparador() {
		var c = document.querySelector('[data-comparador]');
		if (!c) return;
		var r = c.querySelector('[data-comparador-rango]');
		function mover() { c.style.setProperty('--corte', r.value + '%'); }
		r.addEventListener('input', mover);
		mover();
	}
})();
