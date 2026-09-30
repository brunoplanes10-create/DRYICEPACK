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

	/* Fecha: al cambiarla se recalcula (el sábado suma suplemento). */
	$(document).on('change', 'input[name="dip_fecha"]', function () { $body.trigger('update_checkout'); });

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
