/* Página Halloween 2026 · DryIcePack
   - Aparición de secciones al hacer scroll (solo lo que está por debajo del pliegue: nada parpadea al cargar)
   - Ambientación solo con la página cargada y en la sección visible; en pausa con la pestaña oculta
   - Cuenta atrás a la fecha límite (el HTML ya trae la fecha: sin JS se lee igual)
   - Contador de temperatura 20 °C → −78,5 °C y progreso de los 3 pasos
   - Vídeos: solo a la vista, con botón de pausa y sin arrancar solos con "reducir movimiento" */
(function () {
  "use strict";
  var d = document, w = window;
  var raiz = d.getElementById("hw");
  if (!raiz) return;
  var reducido = w.matchMedia && w.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var io = "IntersectionObserver" in w;

  /* ---- La ambientación arranca con la página ya cargada y el navegador libre ----
     .hw-cargada: se pide la textura de niebla (no compite con la foto principal)
     .hw-anima: arrancan las animaciones (no con "reducir movimiento") */
  var arrancar = function () {
    var ya = function () {
      raiz.classList.add("hw-cargada");
      if (!reducido) raiz.classList.add("hw-anima");
    };
    ("requestIdleCallback" in w) ? w.requestIdleCallback(ya, { timeout: 2500 }) : setTimeout(ya, 600);
  };
  if (d.readyState === "complete") arrancar(); else w.addEventListener("load", arrancar, { once: true });

  /* ---- Aparición al hacer scroll ----
     La primera respuesta del observador dice qué está fuera de pantalla: solo eso se oculta
     y aparece al llegar. Lo que ya se ve no se toca (nada parpadea al cargar). */
  if (io && !reducido) {
    var visto = typeof WeakSet !== "undefined" ? new WeakSet() : null;
    var piezasDe = function (el) { return el.getAttribute("data-hw-revela") === "grupo" ? el.children : [el]; };
    var obsRevela = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) {
        var el = e.target, primera = visto && !visto.has(el);
        if (visto) visto.add(el);
        if (e.isIntersecting) {
          obsRevela.unobserve(el);
          if (primera) return; // ya estaba en pantalla al cargar
          Array.prototype.forEach.call(piezasDe(el), function (p) {
            p.classList.remove("hw-pendiente");
            // Quita el retardo escalonado al terminar, para que el hover responda al momento
            setTimeout(function () { p.style.removeProperty("--hw-d"); }, 1200);
          });
        } else if (primera) {
          var grupo = el.getAttribute("data-hw-revela") === "grupo";
          Array.prototype.forEach.call(piezasDe(el), function (p, i) {
            if (grupo) p.style.setProperty("--hw-d", (i * 90) + "ms");
            p.classList.add("hw-pendiente");
          });
        }
      });
    }, { rootMargin: "0px 0px -12% 0px", threshold: 0.08 });
    if (visto) Array.prototype.forEach.call(raiz.querySelectorAll("[data-hw-revela]"), function (el) { obsRevela.observe(el); });
  }

  /* ---- Ambientación solo en la sección que está en pantalla ---- */
  var secciones = raiz.querySelectorAll(".hw-hero, .hw-seccion, .hw-final");
  if (io) {
    var obsVivo = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) { e.target.classList.toggle("hw-vivo", e.isIntersecting); });
    }, { rootMargin: "80px 0px" });
    Array.prototype.forEach.call(secciones, function (s) { obsVivo.observe(s); });
  } else {
    Array.prototype.forEach.call(secciones, function (s) { s.classList.add("hw-vivo"); });
  }
  d.addEventListener("visibilitychange", function () {
    d.documentElement.classList.toggle("hw-pausa", d.hidden);
  });

  /* ---- Fases de la campaña y cuenta atrás ----
     viernes: hasta el jue 29 a las 12:00 · sabado: hasta el vie 30 a las 12:00
     cerrado: hasta el 1/11 · fin: después. La plantilla fija la fase en <html data-hw-fase>
     antes del primer pintado; aquí solo se actualiza si cambia con la página abierta. */
  var caja = raiz.querySelector("[data-hw-cuenta]");
  if (caja) {
    var reloj = caja.querySelector("[data-hw-reloj]");
    var pie = caja.querySelector("[data-hw-reloj-pie]");
    var tViernes = Date.parse(caja.getAttribute("data-limite-viernes"));
    var tSabado = Date.parse(caja.getAttribute("data-limite-sabado"));
    var tTelefono = tSabado + 6 * 3600000; // vie 30 a las 18:00: fin del horario de atención
    var tFin = Date.parse(caja.getAttribute("data-fin"));
    var dos = function (n) { return (n < 10 ? "0" : "") + n; };
    var previo = {};

    var fase = function (ahora) {
      return ahora < tViernes ? "viernes" : ahora < tSabado ? "sabado" : ahora < tFin ? "cerrado" : "fin";
    };
    var aplicarFase = function (f) {
      if (d.documentElement.getAttribute("data-hw-fase") !== f) d.documentElement.setAttribute("data-hw-fase", f);
    };
    var unidad = function (clave, valor, etiqueta) {
      var cambia = previo[clave] !== undefined && previo[clave] !== valor && !reducido;
      previo[clave] = valor;
      return '<span class="hw-reloj__u' + (cambia ? " hw-tic" : "") + '"><b>' + valor + "</b><small>" + etiqueta + "</small></span>";
    };

    var pintar = function () {
      var ahora = Date.now(), f = fase(ahora), objetivo, texto;
      aplicarFase(f);
      if (f === "viernes") {
        objetivo = tViernes;
        texto = "de margen para pedir y recibirlo el viernes 30 por la mañana.";
      } else if (f === "sabado") {
        objetivo = tSabado;
        texto = "de margen para pedir con entrega el sábado 31 (según zona, suplemento de 11,74 € IVA incl.).";
      } else if (f === "cerrado") {
        reloj.textContent = "Plazo de envío cerrado";
        if (ahora < tTelefono) {
          pie.innerHTML = 'Llámanos antes de las 18:00 y vemos si podemos ayudarte: <a href="tel:+34936737641">936 73 76 41</a>.';
          return true; // a las 18:00 cambia el texto
        }
        pie.textContent = "Para otras fechas: pide antes de las 12:00 y lo recibes el siguiente día laborable.";
        return true; // el 1/11 pasa a la fase final
      } else {
        reloj.textContent = "Hasta Halloween 2027";
        pie.textContent = "Mientras tanto, hielo seco para cualquier fiesta con entrega en 24 h laborables.";
        return false;
      }
      var min = Math.max(0, Math.floor((objetivo - ahora) / 60000));
      var dias = Math.floor(min / 1440), horas = Math.floor((min % 1440) / 60), mins = min % 60;
      reloj.innerHTML = (dias ? unidad("d", String(dias), "d") : "") + unidad("h", dos(horas), "h") + unidad("m", dos(mins), "min");
      pie.textContent = texto;
      return true;
    };
    if (reloj && pie && !isNaN(tViernes) && pintar()) {
      // Una vez por minuto basta: sin segundos, sin trabajo continuo
      setTimeout(function tick() { if (pintar()) setTimeout(tick, 60000 - (Date.now() % 60000)); }, 60000 - (Date.now() % 60000));
    }
  }

  /* ---- Contador de temperatura ---- */
  var temp = raiz.querySelector("[data-hw-temp]");
  if (temp && io && !reducido) {
    var desde = 20, hasta = -78.5, dur = 1400;
    var fmt = function (v) { return (v < 0 ? "−" : "") + Math.abs(v).toFixed(1).replace(".", ","); };
    temp.textContent = fmt(desde);
    var obsT = new IntersectionObserver(function (entradas) {
      if (!entradas[0].isIntersecting) return;
      obsT.disconnect();
      var t0 = null;
      var paso = function (t) {
        if (t0 === null) t0 = t;
        var p = Math.min(1, (t - t0) / dur);
        var e = 1 - Math.pow(1 - p, 3);
        temp.textContent = fmt(desde + (hasta - desde) * e);
        if (p < 1) w.requestAnimationFrame(paso);
      };
      w.requestAnimationFrame(paso);
    }, { threshold: 0.6 });
    obsT.observe(temp);
  }

  /* ---- Progreso de los 3 pasos ---- */
  var pasos = raiz.querySelector("[data-hw-pasos]");
  if (pasos && io && !reducido) {
    var items = pasos.querySelectorAll("li");
    var n = items.length;
    pasos.style.setProperty("--hw-progreso", "0");
    var obsP = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) { if (e.isIntersecting) e.target.classList.add("is-on"); });
      var hechos = pasos.querySelectorAll("li.is-on").length;
      pasos.style.setProperty("--hw-progreso", n > 1 ? String(Math.max(0, hechos - 1) / (n - 1)) : "1");
    }, { rootMargin: "0px 0px -35% 0px", threshold: 0.5 });
    Array.prototype.forEach.call(items, function (li) { obsP.observe(li); });
  }

  /* ---- Vídeos reales ----
     En silencio y en bucle. Solo se reproducen con el vídeo a la vista; se pausan al salir o con la pestaña oculta.
     Con "reducir movimiento" o ahorro de datos no arrancan solos: póster con el botón de reproducir en el centro.
     El botón de pausa está siempre visible; si alguien pausa, el vídeo no vuelve a arrancar solo. */
  var figuras = raiz.querySelectorAll("[data-hw-video]");
  if (figuras.length) {
    var html = d.documentElement, red = navigator.connection || {};
    var ahorro = html.classList.contains("ligero") || !!red.saveData || /2g/.test(red.effectiveType || "");
    var solo = io && !reducido && !html.classList.contains("quieto") && !ahorro;
    var vids = [];

    Array.prototype.forEach.call(figuras, function (fig) {
      var v = fig.querySelector("video"), btn = fig.querySelector(".hw-video__btn");
      if (!v || !btn || typeof v.play !== "function") return;
      var txt = btn.querySelector(".hw-video__btn-txt");
      var x = { fig: fig, marco: fig.querySelector(".hw-video__marco") || fig, visible: !io, aMano: false, quiere: false };

      var pintar = function () {
        fig.classList.toggle("hw-video--parado", v.paused);
        if (txt) txt.textContent = v.paused ? "Reproducir" : "Pausar";
      };
      var reproducir = function () {
        var p = v.play();
        if (p && typeof p.catch === "function") p.catch(pintar); // bloqueado (p. ej. ahorro de batería): queda el póster con el botón
      };
      x.decidir = function () {
        if (x.visible && !d.hidden && !x.aMano && (solo || x.quiere)) { if (v.paused) reproducir(); }
        else if (!v.paused) v.pause();
      };

      v.addEventListener("play", pintar);
      v.addEventListener("pause", pintar);
      v.addEventListener("playing", function () { fig.classList.add("hw-video--listo"); });
      // Si el vídeo no carga se queda el póster, y el botón sobra
      (v.querySelector("source") || v).addEventListener("error", function () { btn.hidden = true; fig.classList.remove("hw-video--listo"); });

      // Rótulos sincronizados con el momento del vídeo (data-desde en segundos)
      var rotulos = fig.querySelectorAll("[data-desde]");
      if (rotulos.length > 1) {
        var marcas = Array.prototype.map.call(rotulos, function (li) { return parseFloat(li.getAttribute("data-desde")) || 0; });
        var actual = 0;
        v.addEventListener("timeupdate", function () {
          var i = 0;
          while (i + 1 < marcas.length && v.currentTime >= marcas[i + 1]) i++;
          if (i !== actual) { rotulos[actual].classList.remove("is-on"); rotulos[i].classList.add("is-on"); actual = i; }
        });
      }

      btn.addEventListener("click", function () {
        fig.classList.remove("hw-video--inicio");
        if (v.paused) { x.aMano = false; x.quiere = true; reproducir(); }
        else { x.aMano = true; v.pause(); }
      });

      if (!solo) fig.classList.add("hw-video--inicio");
      fig.classList.add("hw-video--parado");
      btn.hidden = false;
      vids.push(x);
    });

    if (io && vids.length) {
      // Arranca tras 300 ms a la vista: un scroll que solo pasa por encima (p. ej. un enlace a #cantidad) no descarga el vídeo
      var obsV = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (e) {
          vids.forEach(function (x) {
            if (x.marco !== e.target) return;
            clearTimeout(x.espera);
            x.visible = e.isIntersecting;
            if (x.visible) x.espera = setTimeout(x.decidir, 300); else x.decidir();
          });
        });
      }, { threshold: 0.35 });
      vids.forEach(function (x) { obsV.observe(x.marco); });
    }
    d.addEventListener("visibilitychange", function () { vids.forEach(function (x) { x.decidir(); }); });
  }
})();
