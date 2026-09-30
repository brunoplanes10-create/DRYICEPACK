/* DryIcePack · herramientas interactivas
   - Calculadora: formato + kilos + cajas → precio, envío (misma fórmula que el checkout), total con IVA
     y día de entrega; el botón añade la variación exacta al carrito.
   - Planificador de suministro: frecuencia + kilos → kg al mes, calendario de entregas y enlace a la solicitud de tarifa. */
(function () {
  "use strict";
  var d = document, w = window, CFG = w.DIPT || {};
  var quieto = d.documentElement.classList.contains("quieto");
  var euros = function (n) { return n.toFixed(2).replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " €"; };
  var marcar = function (el, txt) {
    if (!el || el.textContent === txt) return;
    el.textContent = txt;
    if (!quieto) { el.classList.remove("cambia"); void el.offsetWidth; el.classList.add("cambia"); }
  };
  var elegido = function (ctx, nombre) { var i = ctx.querySelector('input[name="' + nombre + '"]:checked'); return i ? i.value : null; };

  /* Coste de envío sin IVA: hielo + 1 kg por caja, redondeado al alza (igual que el checkout) */
  var envio = function (kg, cajas) {
    var t = CFG.tarifa || { hasta2: 8.53, hasta5: 10.32, hasta10: 13.19, kgExtra: 1.12, embalaje: 1 };
    if (kg <= 0) return { coste: 0, facturable: 0 };
    var f = Math.ceil(kg + t.embalaje * Math.max(1, cajas));
    var c = f <= 2 ? t.hasta2 : f <= 5 ? t.hasta5 : f <= 10 ? t.hasta10 : t.hasta10 + (f - 10) * t.kgExtra;
    return { coste: c, facturable: f };
  };

  /* ---------- Calculadora ---------- */
  var calc = d.querySelector("[data-calculadora]");
  if (calc) {
    var raiz = calc.closest("section") || d;
    var variaciones = CFG.variaciones || [];
    var iva = CFG.iva || 1.21;
    var maxKg = (CFG.tarifa && CFG.tarifa.maxKg) || 250;
    var cajas = 1;
    var out = {
      cajas: raiz.querySelector("[data-cajas]"), menos: raiz.querySelector("[data-menos]"), mas: raiz.querySelector("[data-mas]"),
      precio: raiz.querySelector("[data-precio]"), envio: raiz.querySelector("[data-envio]"), total: raiz.querySelector("[data-total]"),
      fact: raiz.querySelector("[data-facturable]"), totalKg: raiz.querySelector("[data-total-kg]"),
      peso: raiz.querySelector(".etiqueta--calcula [data-etq-peso]"), formato: raiz.querySelector(".etiqueta--calcula [data-etq-formato]"),
      comprar: raiz.querySelector("[data-comprar]"), medidor: raiz.querySelector("[data-medidor]"), carga: raiz.querySelector("[data-carga]"),
    };
    var pintar = function (lluvia) {
      var formato = elegido(calc, "dipt-formato") || "3mm";
      var kg = parseFloat(elegido(calc, "dipt-kg") || "10");
      var max = Math.max(1, Math.floor(maxKg / kg));
      cajas = Math.min(Math.max(1, cajas), max);
      var v = variaciones.filter(function (x) { return x.formato === formato && Math.abs(x.kg - kg) < 0.01; })[0];
      var totalKg = kg * cajas;
      var e = envio(totalKg, cajas);
      marcar(out.cajas, String(cajas));
      if (out.menos) out.menos.disabled = cajas <= 1;
      if (out.mas) out.mas.disabled = cajas >= max;
      marcar(out.totalKg, String(totalKg));
      marcar(out.peso, cajas > 1 ? cajas + " × " + kg + " kg" : kg + " kg");
      marcar(out.formato, formato.replace("mm", " mm"));
      if (out.fact) out.fact.textContent = "(" + e.facturable + " kg facturables)";
      if (v) {
        var sub = v.precio * cajas;
        marcar(out.precio, euros(sub) + " + IVA");
        marcar(out.envio, euros(e.coste) + " + IVA");
        marcar(out.total, euros((sub + e.coste) * iva));
        if (out.comprar) {
          // Con WooCommerce: añade la variación exacta y lleva al carrito. Sin él: ficha con el formato y peso elegidos.
          out.comprar.href = CFG.woo
            ? CFG.urls.carrito + (CFG.urls.carrito.indexOf("?") > -1 ? "&" : "?") + "add-to-cart=" + v.id + "&quantity=" + cajas
            : CFG.urls.producto + "?attribute_pa_formato=" + encodeURIComponent(formato) + "&attribute_pa_peso=" + encodeURIComponent(v.peso || kg + "kg-de-hielo-seco");
        }
      }
      if (out.medidor) {
        out.medidor.setAttribute("data-formato", formato);
        out.medidor.style.setProperty("--nivel", Math.max(0.18, Math.min(1, totalKg / 40)).toFixed(3));
        if (lluvia && !quieto) { out.medidor.classList.remove("llueve"); void out.medidor.offsetWidth; out.medidor.classList.add("llueve"); }
      }
    };
    calc.addEventListener("change", function () { pintar(true); });
    if (out.menos) out.menos.addEventListener("click", function () { cajas--; pintar(true); });
    if (out.mas) out.mas.addEventListener("click", function () { cajas++; pintar(true); });
    pintar(false);
  }

  /* ---------- Planificador de suministro ---------- */
  var plan = d.querySelector("[data-planificador]");
  if (plan) {
    var rango = plan.querySelector("[data-plan-rango]"), kgTxt = plan.querySelector("[data-plan-kg]");
    var mesTxt = plan.querySelector("[data-plan-mes]"), entTxt = plan.querySelector("[data-plan-entregas]");
    var cal = plan.querySelector("[data-calendario]"), enlace = plan.querySelector("[data-plan-enlace]");
    var POR_MES = { semanal: 52 / 12, quincenal: 26 / 12, mensual: 1 };
    var PASO = { semanal: 1, quincenal: 2, mensual: 4 };
    var ENTREGAS = { semanal: "4–5", quincenal: "2", mensual: "1" };
    var semanas = [];
    if (cal) {
      for (var s = 0; s < 8; s++) {
        var col = d.createElement("div"); col.className = "calendario__sem";
        col.innerHTML = '<span class="calendario__caja"></span><span>S' + (s + 1) + "</span>";
        cal.appendChild(col); semanas.push(col.firstChild);
      }
    }
    var pintarPlan = function () {
      var frec = elegido(plan, "dipt-frec") || "semanal";
      var formato = elegido(plan, "dipt-plan-formato") || "16mm";
      var kg = parseInt(rango.value, 10);
      marcar(kgTxt, String(kg));
      marcar(mesTxt, String(Math.round(kg * POR_MES[frec])));
      marcar(entTxt, ENTREGAS[frec]);
      semanas.forEach(function (c, i) {
        var toca = i % PASO[frec] === 0;
        c.classList.toggle("entrega", toca);
        c.style.setProperty("--d", (i * 60) + "ms");
      });
      if (enlace && CFG.urls) {
        enlace.href = CFG.urls.suministro + "?frecuencia=" + frec + "&kg=" + kg + "&formato=" + formato + "#formulario";
      }
    };
    plan.addEventListener("input", pintarPlan);
    plan.addEventListener("change", pintarPlan);
    pintarPlan();
  }
})();
