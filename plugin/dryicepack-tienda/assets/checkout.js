/* Checkout Dryicepack: fecha de entrega, datos de empresa, aviso de recogida lejana, botón con el total y pago exprés dentro del bloque de pago. */
(function ($) {
	'use strict';
	if (!$ || !window.DIP_CHECKOUT) return;
	var C = window.DIP_CHECKOUT;
	var $body = $(document.body);

	function importe(texto) {
		var limpio = String(texto || '').replace(/[^\d.,]/g, '');
		if (C.miles) limpio = limpio.split(C.miles).join('');
		if (C.decimal && C.decimal !== '.') limpio = limpio.replace(C.decimal, '.');
		var n = parseFloat(limpio);
		return isNaN(n) ? 0 : n;
	}

	function totalPedido() {
		var el = document.querySelector('.woocommerce-checkout-review-order-table .order-total .woocommerce-Price-amount');
		return el ? { numero: importe(el.textContent), texto: el.textContent.trim() } : { numero: 0, texto: '' };
	}

	/* Datos fiscales: visibles al marcar la casilla; obligatorios y fijos por encima del límite. */
	function datosEmpresa() {
		var casilla = document.getElementById('dip_empresa');
		if (!casilla) return;
		var supera = C.limite > 0 && totalPedido().numero > C.limite;
		if (supera) casilla.checked = true;
		casilla.disabled = false;
		casilla.closest('.form-row').classList.toggle('dip-fijada', supera);
		casilla.setAttribute('aria-disabled', supera ? 'true' : 'false');
		document.querySelectorAll('.dip-fiscal').forEach(function (fila) {
			fila.hidden = !casilla.checked;
			var input = fila.querySelector('input');
			if (input && !fila.classList.contains('dip-fiscal--opcional')) input.required = casilla.checked && input.id !== 'dip_direccion_fiscal';
		});
	}
	$(document).on('change', '#dip_empresa', function () {
		if (this.closest('.form-row').classList.contains('dip-fijada')) this.checked = true;
		datosEmpresa();
	});

	/* Fecha: al cambiarla se recalcula (el sábado suma suplemento). Al repintar el bloque, el foco vuelve al día elegido. */
	var focoFecha = '';
	$(document).on('change', 'input[name="dip_fecha"]', function () {
		if (document.activeElement === this) focoFecha = this.value;
		$body.trigger('update_checkout');
	});

	/* ---------- Calendario "Otra fecha" ----------
	   Cualquier día válido de los próximos 90. Los días salen del servidor (data-dip-cal en .dip-fechas): no hace peticiones.
	   Teclado: flechas (día y semana), Inicio/Fin (semana), RePág/AvPág (mes), Intro o espacio (elegir), Esc (cerrar). */
	var DIA = 864e5;
	var cal = { abierto: false, foco: '', anima: false, dentro: false };
	var FLECHA = '<svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12.5 4.5 7 10l5.5 5.5"/></svg>';
	function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function utc(f) { var p = String(f).split('-'); return Date.UTC(+p[0], +p[1] - 1, +(p[2] || 1)); }
	function ymd(t) { return new Date(t).toISOString().slice(0, 10); }
	function semana(t) { return (new Date(t).getUTCDay() + 6) % 7; } // 0 = lunes … 6 = domingo
	function finDeMes(mes) { return Date.UTC(+mes.slice(0, 4), +mes.slice(5, 7), 0); } // mes = 'AAAA-MM'

	function datosCal() {
		var fs = document.querySelector('.dip-fechas[data-dip-cal]');
		if (!fs) return null;
		if (fs.dipCal) return fs.dipCal;
		var d;
		try { d = JSON.parse(fs.getAttribute('data-dip-cal')); } catch (e) { return null; }
		if (!d || !d.f) return null;
		d.ok = {}; d.f.forEach(function (f) { d.ok[f] = 1; });
		d.cierra = {}; (d.x || []).forEach(function (f) { d.cierra[f] = 1; });
		d.min = (d.f[0] || d.des).slice(0, 7);
		d.max = (d.f.length ? d.f[d.f.length - 1] : d.has).slice(0, 7);
		return (fs.dipCal = d);
	}
	function etiqueta(D, f) {
		var t = utc(f), d = new Date(t), texto = D.n.d[semana(t)] + ' ' + d.getUTCDate() + ' ' + D.n.mde[d.getUTCMonth()];
		if (!D.ok[f]) return texto + ', ' + (D.cierra[f] ? D.t.mrw : D.t.no);
		return semana(t) === 5 ? texto + ', ' + D.sab : texto;
	}
	function limitar(D, t) { return ymd(Math.min(Math.max(t, utc(D.min + '-01')), finDeMes(D.max))); }

	/* Cabecera y tabla se crean al abrir; al cambiar de mes solo cambian el título y los días (el título se anuncia). */
	function pintarCal() {
		var D = datosCal(), caja = document.getElementById('dip-cal'), boton = document.querySelector('.dip-otra-fecha');
		if (!caja || !boton) return;
		if (!D) { boton.hidden = true; return; }
		boton.setAttribute('aria-expanded', cal.abierto ? 'true' : 'false');
		caja.hidden = !cal.abierto;
		if (!cal.abierto) { caja.innerHTML = ''; return; }
		if (!cal.foco || cal.foco.slice(0, 7) < D.min || cal.foco.slice(0, 7) > D.max) cal.foco = D.ok[D.sel] ? D.sel : (D.f[0] || D.des);
		if (!caja.firstChild) {
			var h = '<div class="dip-cal__cabecera"><button type="button" class="dip-cal__nav" data-dip-mes="-1" aria-label="' + esc(D.t.ant) + '">' + FLECHA + '</button>'
				+ '<div class="dip-cal__mes" id="dip-cal-mes" aria-live="polite"></div>'
				+ '<button type="button" class="dip-cal__nav dip-cal__nav--sig" data-dip-mes="1" aria-label="' + esc(D.t.sig) + '">' + FLECHA + '</button></div>'
				+ '<table class="dip-cal__tabla" aria-labelledby="dip-cal-mes"><thead><tr>';
			for (var i = 0; i < 7; i++) h += '<th scope="col"><abbr title="' + esc(D.n.d[i]) + '">' + esc(D.n.dc[i]) + '</abbr></th>';
			caja.innerHTML = h + '</tr></thead><tbody></tbody></table><p class="dip-cal__leyenda">' + esc(D.t.ley) + '</p>'
				+ '<p class="dip-cal__leyenda dip-cal__leyenda--sabado"><span class="dip-cal__marca" aria-hidden="true"></span>' + esc(D.t.ls) + '</p>';
			caja.setAttribute('role', 'group');
			caja.setAttribute('aria-label', D.t.cal);
		}
		caja.classList.toggle('dip-cal--entra', cal.anima);
		cal.anima = false;
		pintarMes(D, caja);
	}
	function pintarMes(D, caja) {
		var mes = cal.foco.slice(0, 7), y = +mes.slice(0, 4), m = +mes.slice(5, 7) - 1;
		var hueco = semana(Date.UTC(y, m, 1)), dias = new Date(finDeMes(mes)).getUTCDate();
		var titulo = D.n.m[m];
		caja.querySelector('.dip-cal__mes').textContent = titulo.charAt(0).toUpperCase() + titulo.slice(1) + ' ' + y;
		caja.querySelector('[data-dip-mes="-1"]').setAttribute('aria-disabled', mes <= D.min ? 'true' : 'false');
		caja.querySelector('[data-dip-mes="1"]').setAttribute('aria-disabled', mes >= D.max ? 'true' : 'false');
		var h = '<tr>' + new Array(hueco + 1).join('<td></td>');
		for (var dia = 1; dia <= dias; dia++) {
			var f = ymd(Date.UTC(y, m, dia)), col = (hueco + dia - 1) % 7, ok = !!D.ok[f];
			if (col === 0 && dia > 1) h += '</tr><tr>';
			h += '<td><button type="button" class="dip-cal__dia' + (ok && col === 5 ? ' dip-cal__dia--sabado' : '') + (f === D.sel ? ' dip-cal__dia--elegido' : '') + '"'
				+ ' data-dip-dia="' + f + '" tabindex="' + (f === cal.foco ? '0' : '-1') + '"' + (ok ? '' : ' aria-disabled="true"') + (f === D.sel ? ' aria-pressed="true"' : '')
				+ ' aria-label="' + esc(etiqueta(D, f)) + '">' + dia + '</button></td>';
		}
		caja.querySelector('tbody').innerHTML = h + new Array((7 - (hueco + dias) % 7) % 7 + 1).join('<td></td>') + '</tr>';
	}
	function enfocarDia() {
		var b = document.querySelector('#dip-cal [data-dip-dia="' + cal.foco + '"]');
		if (b) b.focus({ preventScroll: true });
	}
	function abrirCal() {
		cal.abierto = true; cal.anima = true; cal.foco = '';
		pintarCal();
		var caja = document.getElementById('dip-cal');
		if (caja && caja.scrollIntoView) caja.scrollIntoView({ block: 'nearest' });
		enfocarDia();
	}
	function cerrarCal(devolverFoco) {
		cal.abierto = false;
		pintarCal();
		var b = document.querySelector('.dip-otra-fecha');
		if (devolverFoco && b) b.focus();
	}
	/* Día del calendario: si ya es una casilla, se marca; si no, aparece como casilla (en el sitio de la última) hasta que el servidor repinta. */
	function elegirDia(f) {
		var D = datosCal(), lista = document.querySelector('.dip-fechas__lista');
		if (!D || !lista || !D.ok[f]) return;
		var radio = lista.querySelector('input[name="dip_fecha"][value="' + f + '"]');
		if (!radio) {
			var casillas = lista.querySelectorAll('label.dip-fecha');
			var sobra = lista.querySelector('.dip-fecha--lejana') || (casillas.length >= (D.c || 6) ? casillas[casillas.length - 1] : null);
			var t = utc(f), d = new Date(t), sab = semana(t) === 5, nueva = document.createElement('label');
			nueva.className = 'dip-fecha dip-fecha--lejana' + (sab ? ' dip-fecha--sabado' : '');
			nueva.innerHTML = '<input type="radio" name="dip_fecha" value="' + f + '"><span class="dip-fecha__dia" aria-hidden="true">' + esc(D.n.dc[semana(t)]) + '</span>'
				+ '<span class="dip-fecha__num" aria-hidden="true">' + d.getUTCDate() + '</span><span class="dip-fecha__mes" aria-hidden="true">' + esc(D.n.mc[d.getUTCMonth()]) + '</span>'
				+ (sab ? '<span class="dip-fecha__extra">' + esc(D.sab) + '</span>' : '') + '<span class="screen-reader-text">' + esc(etiqueta(D, f)) + '</span>';
			if (sobra) lista.replaceChild(nueva, sobra); else lista.insertBefore(nueva, lista.querySelector('.dip-otra-fecha'));
			radio = nueva.querySelector('input');
		}
		cerrarCal(false);
		radio.checked = true;
		radio.focus({ preventScroll: true });
		$(radio).trigger('change');
	}

	$(document).on('click', '.dip-otra-fecha', function (e) {
		e.preventDefault();
		if (cal.abierto) cerrarCal(false); else abrirCal();
	});
	$(document).on('click', '#dip-cal [data-dip-mes]', function () {
		var D = datosCal();
		if (!D || this.getAttribute('aria-disabled') === 'true') return;
		var d = new Date(utc(cal.foco)), mes = ymd(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + (+this.getAttribute('data-dip-mes')), 1)).slice(0, 7);
		var primero = D.f.filter(function (f) { return f.slice(0, 7) === mes; })[0];
		cal.foco = primero || limitar(D, utc(mes + '-01'));
		pintarMes(D, document.getElementById('dip-cal'));
	});
	$(document).on('click', '#dip-cal [data-dip-dia]', function () {
		if (this.getAttribute('aria-disabled') !== 'true') elegirDia(this.getAttribute('data-dip-dia'));
	});
	$(document).on('focusin', function (e) {
		var dia = e.target.closest ? e.target.closest('#dip-cal [data-dip-dia]') : null;
		cal.dentro = !!(e.target.closest && e.target.closest('#dip-cal'));
		if (dia && dia.getAttribute('tabindex') !== '0') {
			document.querySelectorAll('#dip-cal [data-dip-dia]').forEach(function (b) { b.setAttribute('tabindex', b === dia ? '0' : '-1'); });
			cal.foco = dia.getAttribute('data-dip-dia');
		}
	});
	$(document).on('keydown', '#dip-cal, .dip-otra-fecha', function (e) {
		var tecla = e.key || (e.originalEvent && e.originalEvent.key), D = datosCal();
		if (!D || !cal.abierto) return;
		if (tecla === 'Escape' || tecla === 'Esc') { e.preventDefault(); cerrarCal(true); return; }
		if (!e.target.closest || !e.target.closest('[data-dip-dia]')) return;
		var t = utc(cal.foco), d = new Date(t), n;
		switch (tecla) {
			case 'ArrowLeft': n = t - DIA; break;
			case 'ArrowRight': n = t + DIA; break;
			case 'ArrowUp': n = t - 7 * DIA; break;
			case 'ArrowDown': n = t + 7 * DIA; break;
			case 'Home': n = t - semana(t) * DIA; break;
			case 'End': n = t + (6 - semana(t)) * DIA; break;
			case 'PageUp': case 'PageDown':
				var m = d.getUTCMonth() + (tecla === 'PageUp' ? -1 : 1);
				n = Date.UTC(d.getUTCFullYear(), m, Math.min(d.getUTCDate(), new Date(Date.UTC(d.getUTCFullYear(), m + 1, 0)).getUTCDate()));
				break;
			default: return;
		}
		e.preventDefault();
		cal.foco = limitar(D, n);
		pintarMes(D, document.getElementById('dip-cal'));
		enfocarDia();
	});

	/* Tras cada actualización del checkout (en el mismo instante en que WooCommerce repinta el bloque, sin parpadeo):
	   el calendario sigue abierto si lo estaba y el foco vuelve al día elegido o al día del calendario. */
	function calendario() {
		pintarCal();
		var perdido = !document.activeElement || document.activeElement === document.body;
		if (perdido && focoFecha) {
			var r = document.querySelector('input[name="dip_fecha"][value="' + focoFecha + '"]');
			if (r) r.focus({ preventScroll: true });
		} else if (perdido && cal.abierto && cal.dentro) {
			enfocarDia();
		}
		focoFecha = '';
	}
	$body.on('updated_checkout', calendario);

	/* Recogida en Mataró con un código postal fuera de Cataluña: aviso. */
	function avisoLejos() {
		var aviso = document.querySelector('[data-dip-aviso-lejos]');
		if (!aviso) return;
		var cp = (document.getElementById('billing_postcode') || {}).value || '';
		cp = cp.replace(/\D/g, '');
		aviso.hidden = cp.length < 2 || C.cataluna.indexOf(cp.slice(0, 2)) !== -1;
	}
	$(document).on('input change', '#billing_postcode', function () {
		this.value = this.value.replace(/\D/g, '').slice(0, 5);
		avisoLejos();
	});

	/* Botón final con el importe: "Pagar 81,81 €". Con efectivo o factura mensual: "Confirmar pedido". */
	function textoBoton() {
		var boton = document.getElementById('place_order');
		if (!boton) return;
		var metodo = $('input[name="payment_method"]:checked').val() || '';
		var total = totalPedido();
		var texto = (metodo === 'cod' || metodo === 'dip_factura_mensual' || !total.texto) ? C.confirmar : C.pagar + ' ' + total.texto;
		boton.textContent = texto;
		boton.value = texto;
		boton.setAttribute('data-value', texto);
	}

	/* Apple Pay / Google Pay de WooPayments: dentro del bloque de pago, nunca arriba del formulario. */
	var SELECTORES_EXPRES = ['#wcpay-express-checkout-element', '#wcpay-express-checkout-wrapper', '.wcpay-express-checkout-wrapper', '#wcpay-payment-request-wrapper', '#wc-stripe-payment-request-wrapper', '#wc-stripe-express-checkout-element'];
	var SEPARADORES = ['#wcpay-express-checkout-button-separator', '#wcpay-payment-request-button-separator', '#wc-stripe-payment-request-button-separator', '#wc-stripe-express-checkout-button-separator'];
	function moverPagoRapido() {
		var destino = document.querySelector('[data-dip-pago-rapido] .dip-pago-rapido__hueco');
		if (!destino) return;
		var movido = false;
		SELECTORES_EXPRES.forEach(function (sel) {
			var el = document.querySelector(sel);
			if (el && !destino.contains(el) && !el.closest('.dip-pago-rapido__hueco')) {
				destino.appendChild(el);
				movido = true;
			}
			if (el) movido = true;
		});
		SEPARADORES.forEach(function (sel) { var s = document.querySelector(sel); if (s) s.style.display = 'none'; });
		var caja = destino.closest('[data-dip-pago-rapido]');
		if (caja) caja.hidden = !movido || !destino.querySelector('iframe, button, .StripeElement, [id*="express"]');
	}
	var observador = new MutationObserver(function () { moverPagoRapido(); });

	function todo() {
		datosEmpresa();
		avisoLejos();
		textoBoton();
		moverPagoRapido();
	}

	$(function () {
		todo();
		var form = document.querySelector('form.checkout');
		if (form) observador.observe(form.parentNode || document.body, { childList: true, subtree: true });
		setTimeout(moverPagoRapido, 1200);
		setTimeout(moverPagoRapido, 3000);
	});
	$body.on('updated_checkout payment_method_selected', function () { setTimeout(todo, 0); });
	$(document).on('change', 'input[name="payment_method"]', function () { setTimeout(textoBoton, 0); });
})(window.jQuery);
