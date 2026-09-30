/* DryIcePack · movimiento común de toda la web
   - Reloj de corte en directo (hora de Madrid, reglas reales de entrega)
   - Aparición al hacer scroll: se condensa del vapor (solo lo que está por debajo del pliegue)
   - Ambientación solo con la página cargada (.anima) y en la sección visible (.vivo)
   - Botones magnéticos, tarjetas con inclinación 3D y caja EPS que sigue al puntero (solo puntero fino, no en modo ligero)
   - Menú móvil, contador del carrito, pausa con la pestaña oculta
   Todo con transform/opacity. Con "reducir movimiento" (html.quieto) no se anima nada. */
(function () {
  "use strict";
  var d = document, w = window, raiz = d.documentElement;
  var quieto = raiz.classList.contains("quieto");
  var ligero = raiz.classList.contains("ligero");
  var fino = w.matchMedia && w.matchMedia("(hover: hover) and (pointer: fine)").matches;
  var io = "IntersectionObserver" in w;
  var todos = function (sel, ctx) { return Array.prototype.slice.call((ctx || d).querySelectorAll(sel)); };
  var CFG = w.DIPT || {};

  /* ---------- Carga: texturas y ambientación cuando el navegador está libre ---------- */
  var alCargar = function () {
    var ya = function () {
      raiz.classList.add("cargada");
      if (!quieto) raiz.classList.add("anima");
      todos("[data-caja3d]").forEach(function (c) { c.classList.add("abierta"); });
    };
    ("requestIdleCallback" in w) ? w.requestIdleCallback(ya, { timeout: 2000 }) : setTimeout(ya, 500);
  };
  if (d.readyState === "complete") alCargar(); else w.addEventListener("load", alCargar, { once: true });
  d.addEventListener("visibilitychange", function () { raiz.classList.toggle("pausa", d.hidden); });

  /* ---------- Secciones vivas ---------- */
  var vivas = todos(".seccion, .pie, .portada, [data-vivo]");
  if (io) {
    var obsVivo = new IntersectionObserver(function (es) {
      es.forEach(function (e) { e.target.classList.toggle("vivo", e.isIntersecting); });
    }, { rootMargin: "100px 0px" });
    vivas.forEach(function (s) { obsVivo.observe(s); });
  } else vivas.forEach(function (s) { s.classList.add("vivo"); });

  /* ---------- Aparición: se condensa del vapor ---------- */
  if (io && !quieto) {
    var visto = new WeakSet();
    var piezas = function (el) { return el.getAttribute("data-condensa") === "grupo" ? Array.prototype.slice.call(el.children) : [el]; };
    var obsC = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        var el = e.target, primera = !visto.has(el);
        visto.add(el);
        if (e.isIntersecting) {
          obsC.unobserve(el);
          if (primera) return; // ya estaba en pantalla al cargar: no se toca
          piezas(el).forEach(function (p) {
            p.classList.remove("por-condensar");
            setTimeout(function () { p.style.removeProperty("--d"); }, 1300);
          });
        } else if (primera) {
          piezas(el).forEach(function (p, i) {
            if (el.getAttribute("data-condensa") === "grupo") p.style.setProperty("--d", (i * 70) + "ms");
            p.classList.add("por-condensar");
          });
        }
      });
    }, { rootMargin: "0px 0px -10% 0px", threshold: 0.06 });
    todos("[data-condensa]").forEach(function (el) { obsC.observe(el); });
  }

  /* ---------- Reloj de corte (Madrid) ---------- */
  var DIAS = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];
  var MESES = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
  var fmt;
  try { fmt = new Intl.DateTimeFormat("en-GB", { timeZone: "Europe/Madrid", year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23" }); } catch (e) { fmt = null; }
  var ahoraMadrid = function () { // fecha "de reloj de Madrid" guardada como UTC para operar con getUTC*
    var n = new Date();
    if (!fmt) return new Date(Date.UTC(n.getFullYear(), n.getMonth(), n.getDate(), n.getHours(), n.getMinutes(), n.getSeconds()));
    var p = {}; fmt.formatToParts(n).forEach(function (x) { p[x.type] = +x.value; });
    return new Date(Date.UTC(p.year, p.month - 1, p.day, p.hour % 24, p.minute, p.second));
  };
  var diaSem = function (f) { var x = f.getUTCDay(); return x === 0 ? 7 : x; }; // 1 lunes … 7 domingo
  var masDias = function (f, n) { return new Date(f.getTime() + n * 864e5); };
  var mismaFecha = function (a, b) { return a.getUTCFullYear() === b.getUTCFullYear() && a.getUTCMonth() === b.getUTCMonth() && a.getUTCDate() === b.getUTCDate(); };
  var fechaTexto = function (f, hoy) {
    var base = DIAS[f.getUTCDay()] + " " + f.getUTCDate() + " de " + MESES[f.getUTCMonth()];
    return mismaFecha(f, masDias(hoy, 1)) ? "mañana, " + base : "el " + base;
  };
  var calcular = function () {
    var ahora = ahoraMadrid();
    var hoy = new Date(Date.UTC(ahora.getUTCFullYear(), ahora.getUTCMonth(), ahora.getUTCDate()));
    var min = ahora.getUTCHours() * 60 + ahora.getUTCMinutes();
    var corte = ((CFG.corte && CFG.corte.hora) || 12) * 60 + ((CFG.corte && CFG.corte.minuto) || 0);
    var antes = min < corte;
    var sale = (diaSem(hoy) <= 5 && antes) ? hoy : masDias(hoy, 1);
    while (diaSem(sale) > 5) sale = masDias(sale, 1);
    var entrega = masDias(sale, 1), sabado = null;
    if (diaSem(entrega) === 6) { sabado = entrega; entrega = masDias(entrega, 3); }
    var txt = fechaTexto(entrega, hoy);
    if (sabado) txt = "el sábado " + sabado.getUTCDate() + " (según zona, con suplemento) o " + txt;
    return { corteHoy: mismaFecha(sale, hoy), quedan: corte * 60 - (min * 60 + ahora.getUTCSeconds()), entrega: txt };
  };
  var previo = {};
  var dig = function (clave, valor) {
    var cambia = previo[clave] !== undefined && previo[clave] !== valor && !quieto;
    previo[clave] = valor;
    return '<b class="reloj-corte__digito' + (cambia ? " cambia" : "") + '">' + valor + "</b>";
  };
  var pintarReloj = function () {
    var r = calcular();
    var txt;
    if (r.corteHoy) {
      var m = Math.max(0, Math.ceil(r.quedan / 60)), h = Math.floor(m / 60), mm = m % 60;
      var cuenta = (h ? dig("h", h) + " h " : "") + dig("m", mm) + " min";
      txt = "Pide en las próximas " + cuenta + " y lo recibes " + r.entrega;
    } else {
      txt = "Pide ahora y lo recibes " + r.entrega;
    }
    var estrecho = w.matchMedia && w.matchMedia("(max-width: 699px)").matches;
    var corto = r.corteHoy ? "Pide en " + (Math.floor(Math.max(0, Math.ceil(r.quedan / 60)) / 60) ? Math.floor(Math.ceil(r.quedan / 60) / 60) + " h " : "") + (Math.ceil(r.quedan / 60) % 60) + " min · llega " + r.entrega.replace(/ de [a-zé]+$/, "") : "Pide ahora · llega " + r.entrega.replace(/ de [a-zé]+$/, "");
    todos("[data-reloj-texto]").forEach(function (el) {
      el.innerHTML = (estrecho && el.closest(".reloj-corte--franja")) ? corto : txt;
    });
    todos("[data-entrega]").forEach(function (el) {
      var nuevo = r.entrega.charAt(0).toUpperCase() + r.entrega.slice(1);
      if (el.textContent !== nuevo) { el.textContent = nuevo; }
    });
  };
  if (todos("[data-reloj-texto], [data-entrega]").length) {
    pintarReloj();
    setInterval(pintarReloj, 30000);
  }
  w.DIPT_entrega = calcular; // la usan la calculadora y el planificador

  /* ---------- Menú móvil ---------- */
  var menu = d.querySelector("[data-menu]"), abrir = d.querySelector("[data-menu-abrir]");
  if (menu && abrir) {
    var cerrar = function () {
      menu.hidden = true; abrir.setAttribute("aria-expanded", "false");
      d.body.classList.remove("menu-abierto"); abrir.focus();
    };
    abrir.addEventListener("click", function () {
      menu.hidden = false; abrir.setAttribute("aria-expanded", "true");
      d.body.classList.add("menu-abierto");
      var primero = menu.querySelector("a, button"); if (primero) primero.focus();
    });
    todos("[data-menu-cerrar]", menu).forEach(function (b) { b.addEventListener("click", cerrar); });
    d.addEventListener("keydown", function (e) { if (e.key === "Escape" && !menu.hidden) cerrar(); });
  }

  /* ---------- Puntero fino: botones magnéticos, inclinación 3D y caja que sigue al puntero ---------- */
  if (fino && !quieto && !ligero) {
    todos("[data-iman]").forEach(function (b) {
      b.addEventListener("pointermove", function (e) {
        var r = b.getBoundingClientRect();
        b.style.setProperty("--mx", ((e.clientX - r.left) / r.width - 0.5) * 8 + "px");
        b.style.setProperty("--my", ((e.clientY - r.top) / r.height - 0.5) * 6 + "px");
      });
      b.addEventListener("pointerleave", function () { b.style.removeProperty("--mx"); b.style.removeProperty("--my"); });
    });
    todos("[data-inclina]").forEach(function (t) {
      t.addEventListener("pointermove", function (e) {
        var r = t.getBoundingClientRect();
        t.style.setProperty("--iy", ((e.clientX - r.left) / r.width - 0.5) * 7 + "deg");
        t.style.setProperty("--ix", -((e.clientY - r.top) / r.height - 0.5) * 6 + "deg");
      });
      t.addEventListener("pointerleave", function () { t.style.removeProperty("--ix"); t.style.removeProperty("--iy"); });
    });
    todos("[data-caja3d]").forEach(function (c) {
      var zona = c.closest("section") || c;
      var cubo = c.querySelector(".caja3d__cubo");
      var marco = 0;
      zona.addEventListener("pointermove", function (e) {
        if (marco) return;
        marco = requestAnimationFrame(function () {
          marco = 0;
          var r = zona.getBoundingClientRect();
          cubo.style.setProperty("--ry", ((e.clientX - r.left) / r.width - 0.5) * 26 + "deg");
          cubo.style.setProperty("--rx", -((e.clientY - r.top) / r.height - 0.5) * 12 + "deg");
        });
      });
      zona.addEventListener("pointerleave", function () { cubo.style.removeProperty("--rx"); cubo.style.removeProperty("--ry"); });
    });
  }

  /* ---------- Contador del carrito (sin el script pesado de fragmentos de WooCommerce) ---------- */
  var num = d.querySelector("[data-carrito-num]");
  if (num && /woocommerce_items_in_cart=1/.test(d.cookie) && CFG.urls && CFG.urls.ajax) {
    fetch(CFG.urls.ajax + "?action=dipt_carrito", { credentials: "same-origin" })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (j) {
        if (!j || !j.success || !j.data || !j.data.cantidad) return;
        num.textContent = j.data.cantidad; num.hidden = false; num.classList.add("cambia");
        var p = d.querySelector(".acciones-movil__principal");
        if (p && CFG.urls.checkout) { p.href = CFG.urls.checkout; var s = p.querySelector("span"); if (s) s.textContent = "Finalizar"; }
      }).catch(function () {});
  }

  /* ---------- Cifras que cuentan al aparecer ---------- */
  if (io && !quieto) {
    var obsN = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        obsN.unobserve(e.target);
        var el = e.target, fin = parseFloat(el.getAttribute("data-cuenta")), dec = parseInt(el.getAttribute("data-decimales") || "0", 10);
        var ini = parseFloat(el.getAttribute("data-desde") || "0"), t0 = null, dur = 1300;
        var paso = function (t) {
          if (t0 === null) t0 = t;
          var p = Math.min(1, (t - t0) / dur), v = ini + (fin - ini) * (1 - Math.pow(1 - p, 3));
          el.textContent = (v < 0 ? "−" : "") + Math.abs(v).toFixed(dec).replace(".", ",");
          if (p < 1) requestAnimationFrame(paso);
        };
        requestAnimationFrame(paso);
      });
    }, { threshold: 0.6 });
    todos("[data-cuenta]").forEach(function (el) { obsN.observe(el); });
  }
})();
