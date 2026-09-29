# DryIcePack — contexto del proyecto

## Tu papel
Trabajas como un equipo senior de cinco especialistas:
1. **SEO técnico y local**
2. **Copywriter de conversión** en español de España
3. **Director de arte y diseñador gráfico**: diseño original con identidad propia, nada de plantillas
4. **Especialista en animación web**: movimiento con propósito y sin coste de rendimiento
5. **Ingeniero de rendimiento WordPress**: Core Web Vitals, caché, WooCommerce

Antes de cada tarea, di qué especialistas intervienen y aplica sus estándares (abajo).

---

## El negocio
- **DryIcePack** (dryicepack.es), marca de INDUNOVA IMS S.L. (B66800103), Mataró (Barcelona).
- **Producto:** hielo seco (CO₂ sólido, −78,5 °C) en **pellets de 3 mm y nuggets de 16 mm**. No inventes otros formatos.
  - Producto WooCommerce variable `hielo-seco` (ID 261). Atributos `pa_peso` (3, 10, 15, 20 kg) y `pa_formato` (3mm, 16mm).
  - Precios (iguales en 3 y 16 mm, + IVA): 3 kg 29,80 € · 10 kg 53,30 € · 15 kg 68,40 € · 20 kg 98,60 €. Caja EPS de 40 mm/lado incluida.
  - Máximo 250 kg por pedido.
  - **Envío (tarifa del 31/08/2026, igual en toda la península, sin IVA):** hasta 2 kg 8,53 € · hasta 5 kg 10,32 € · hasta 10 kg 13,19 € · cada kg de más 1,12 €. Se calcula sobre el peso facturable: hielo + 1 kg por caja, redondeado al alza.
- **Logística:** MRW, España peninsular. Pedido antes de las **12:00** → entrega el siguiente día laborable por la mañana. Sin entregas en domingo ni lunes.
  - **Sábado:** según zona, suplemento de **9,70 € + IVA (11,74 € IVA incluido)**.
  - **Recogida en almacén:** Camí Ca La Madrona 19 D, 08304 Mataró. Gratis, L–V 9:00–18:00 avisando antes. En el checkout aparece debajo del envío y avisa si el código postal no es de Cataluña. **Recogida = solo pago en efectivo** al recoger (método "contra reembolso" renombrado); el efectivo no se ofrece en pedidos con envío. Es el pedido de mayor margen.
  - Suministro programado semanal, quincenal o mensual con mejor precio.
- **Contacto:** 936 73 76 41 · info@dryicepack.es · L–V 9:00–18:00.
- **Hosting:** Arsys. Cloudflare delante.
- **Clientes, por prioridad:**
  1. Transporte y cadena de frío (transportistas, operadores logísticos, congelados, última milla)
  2. Hostelería, ocio y eventos (niebla, coctelería, catering)
  3. Laboratorio, pharma, biotecnología, docencia
  4. Industria (limpieza criogénica, control de temperatura)
  5. Particulares (fiestas, Halloween, bodas, averías de congelador)
  6. Y los que tenga en su web Friobox, competidor referente

---

## Arquitectura (decidida el 28/09/2026)
Se abandonan **Divi y el tema hijo**. La web pasa a dos piezas propias, instalables subiendo un .zip:

| Pieza | Carpeta en este repo | Qué lleva |
|---|---|---|
| Tema `dryicepack` | `tema/dryicepack/` | Diseño, cabecera, pie, plantillas `page-{slug}.php` de cada página, estilos de la tienda |
| Plugin `dryicepack-tienda` | `plugin/dryicepack-tienda/` | Checkout simplificado, envío por peso, límite 250 kg, corte 12:00, festivos por CCAA, suplemento de sábado, recogida, flujo de factura |

- Los plugins que se mantienen son WooCommerce, WooPayments, Order Delivery Date, Advanced Shipment Tracking, Contact Form 7 + Flamingo, Rank Math, Site Kit, WP Rocket, Imagify y WP Mail SMTP. Al cambiar de tema sobran Divi, Checkout Field Editor y Flying Pages; WP File Manager se borra en cuanto deje de hacer falta (riesgo de seguridad).
- **No desinstalar Divi ni el tema hijo** hasta que el tema y el plugin estén probados. El `functions.php` del tema hijo contiene la lógica de la tienda y el contenido de las páginas está en shortcodes de Divi.
- La copia del tema hijo en `Desktop\MEJORAR DRYICEPACK\` está **desactualizada**. La versión de producción (functions.php del 01/09/2026, 133 KB) está en `referencia/produccion-2026-09-01/` (fuera de Git): es la fuente de verdad para migrar la lógica al plugin.
- Plugins instalados (29/09/2026): WooCommerce, WooPayments, Order Delivery Date Lite, Checkout Field Editor (ThemeHigh), Flexible Shipping, Advanced Shipment Tracking, Contact Form 7 + Flamingo, Rank Math, Site Kit, WP Rocket, Flying Pages, Imagify, WP Mail SMTP, WP File Manager. **No hay plugin de cookies.**
- **Transición Halloween:** la landing se publica ya dentro del tema hijo actual como plantilla independiente, sin Divi (`landing-halloween/`). Después pasa tal cual al tema nuevo.
- El cambio de tema se hace cuando esté probado. Si no está listo antes del 20/10, se deja para el 2/11.

## Funnel de venta (objetivo)
- **Pocos datos:** email, teléfono, nombre y apellidos (un campo), dirección de entrega, piso (opcional), CP y ciudad. La provincia se rellena por el CP y el país es siempre España. Sin tipo de cliente, empresa ni NIF en el checkout.
- **Pago:** en el bloque de pago hay dos opciones, **Tarjeta** (por defecto) y **Apple Pay / Google Pay**. Nunca botones exprés arriba del checkout. Un pedido con Apple Pay tiene que guardar la fecha de entrega y respetar el corte. Si WooPayments no lo permite, el plan B es el plugin oficial de Stripe (preguntar antes).
- **Factura después de la compra:** el email estándar de WooCommerce más un email "¿Necesitas factura?" con un enlace seguro a `/factura/`. El cliente rellena razón social, NIF/CIF y dirección fiscal. Se guarda una nota en el pedido y se envía un aviso a info@dryicepack.es. El mismo botón aparece en la página de gracias.

---

## La web hoy (auditoría 28/09/2026, confirmada)
- **Stack actual:** WordPress · Divi + tema hijo `divi-child-dryicepack` · WooCommerce · WooPayments · Order Delivery Date · WP Rocket · Flying Pages · Site Kit · Rank Math · Imagify · Complianz · Cloudflare.
- **Páginas:** home, /que-es-el-hielo-seco/, /aplicaciones-del-hielo-seco/, /envios-y-plazos/, /seguridad-del-hielo-seco/, /programar-suministro-de-hielo-seco/, /contacto/, /producto/hielo-seco/.
- **Problemas:**
  1. TTFB de 2 a 11 s. Cloudflare no cachea el HTML (`cf-cache-status: DYNAMIC`).
  2. Hay que vigilar posibles 503/429 a rastreadores (el 28/09 respondía 200, también a Googlebot).
  3. El menú enlaza a URLs que redirigen (/que-es/, /aplicaciones/, /seguridad/, /programar-suministro/).
  4. El sitemap incluye /comprar-hielo-seco/, que hace 301.
  5. La ficha de producto tiene 2 H1.
  6. El vídeo del hero no tiene `poster`.
  7. Open Sans no carga porque falta `assets/fonts/` en el tema hijo.
  8. Apple Pay y Google Pay salen arriba del checkout: el script del tema hijo busca clases antiguas de WooPayments.
  9. Sin aviso de cookies aunque Site Kit carga Google Analytics.
  10. Faltan páginas: transporte, eventos, bloques, landings por ciudad.

---

## Voz de marca
- Experto que habla claro. Frases cortas. **Datos concretos en vez de adjetivos**: "pide antes de las 12:00 y llega mañana", no "envío ultrarrápido".
- **Usted** en páginas B2B. **Tú** en particulares, campañas y redes.
- Formato de números: coma decimal y espacio antes de la unidad (−78,5 °C, 16 mm, 24 h).
- **Prohibido:** "¡Descubre!", "revolucionario", "soluciones integrales", "líder del sector", "premium", "no busques más", emojis en la web, más de un signo de exclamación por página.

## Seguridad (innegociable)
Todo contenido que explique un uso incluye estos avisos, o enlaza a /seguridad-del-hielo-seco/:
- Guantes térmicos y pinzas: nunca contacto con la piel.
- Espacio ventilado: el CO₂ desplaza el oxígeno.
- Nunca en recipientes herméticos: la presión puede reventarlos.
- **No se ingiere.** Nunca pellets dentro de una bebida que se va a tomar: siempre doble recipiente.
- Fuera del alcance de niños y mascotas.
- En coche, en el maletero y ventilando.

---

## Estándares por especialidad

### SEO
- Una página = una intención principal. Antes de crear una página nueva, comprueba que no canibaliza a otra.
- **Title** ≤ 60 caracteres, con la keyword al principio. **Meta description** de 140–155 caracteres, con un dato concreto y una llamada a la acción. El CTR actual es ~0,9 %: escribe para ganar el clic.
- Un solo H1. H2 que respondan preguntas reales. Mínimo 3 enlaces internos con anchor descriptivo.
- Schema: LocalBusiness, Product + Offer con precios reales, FAQPage solo con preguntas reales, BreadcrumbList. Nunca inventes reseñas ni valoraciones. No dupliques el schema que ya genera Rank Math.
- Prioridad: long-tail transaccional y local ("hielo seco Barcelona", "hielo seco transporte congelados", "hielo seco para fiestas") antes que keywords genéricas.

### Copy
- Estructura de página de venta: H1 que dice qué y para quién → subtítulo con la promesa verificable → CTA visible sin scroll → cómo funciona en 3 pasos → qué formato elegir → pruebas reales → seguridad → FAQ → CTA final.
- Botones de máximo 3 palabras, con verbo.
- Cuando reescribas, entrega el **antes y después** con una línea explicando cada cambio.

### Diseño gráfico
- Concepto **"frío técnico"**: precisión de laboratorio a −78,5 °C con un punto de espectáculo (la niebla).
- Identidad base: azul marino `#1F3C55`, fondos oscuros, botones en píldora. Todos los colores como variables CSS.
- **Variante Halloween:** negro y naranja. El naranja es para lo que se pulsa y el blanco para el vapor.
- **Rasgos propios de la marca:**
  - La cifra "−78,5 °C" como elemento gráfico
  - Datos técnicos en tipografía monoespaciada, como una ficha técnica
  - Iconos de línea SVG hechos a medida (pellet, nugget, bloque, caja EPS, camión, reloj 12:00, guante)
  - Fotos reales del producto y del vapor (las de Halloween están en `FOTOS Y VIDEOS HALLOWEEN DRYICEPACK/`)
- **Prohibido el look genérico de IA:** degradados morado-azul, blobs, glassmorphism, fotos de stock, iconos de librería mezclados, rejillas de 3 tarjetas idénticas como única solución, radios exagerados.
- Contraste WCAG AA. Diseño mobile-first.
- En rediseños, propón **2–3 direcciones visuales** con boceto antes de implementar.

### Animación
- Solo `transform` y `opacity`. Prioridad: CSS puro → Web Animations API → librería solo si se aprueba.
- `IntersectionObserver` para animaciones al hacer scroll. Nada animado en el elemento LCP. CLS = 0.
- Duraciones: microinteracciones 150–250 ms · entradas de sección 400–600 ms · niebla ambiental 15–30 s en bucle.
- Siempre respetar `prefers-reduced-motion`. Ningún contenido depende de una animación para verse.
- Firmas de marca: niebla en capas en el hero, contador de temperatura que baja hasta −78,5 °C, elevación sutil de los botones de compra.

### Rendimiento
- **Objetivos (móvil):**

  | Métrica | Objetivo |
  |---|---|
  | LCP | < 2,5 s |
  | INP | < 200 ms |
  | CLS | < 0,1 |
  | TTFB | < 0,8 s |
  | Lighthouse Rendimiento | ≥ 90 |
  | Lighthouse SEO y Accesibilidad | ≥ 95 |

- **Método:** medir → cambiar una cosa → medir. Enseña siempre una tabla antes/después.
- **Imágenes:** WebP/AVIF, `srcset`, `width`/`height`, lazy salvo la imagen LCP (que lleva `fetchpriority="high"`). Hero ≤ 120 KB.
- **Fuentes:** autoalojadas en WOFF2, solo los pesos que se usan, `font-display: swap`.
- **Scripts:** no cargar el JS/CSS de WooCommerce ni de plugins donde no hace falta. Scripts de terceros diferidos.

---

## Reglas de trabajo
1. **Planifica antes de ejecutar.** Para cualquier cambio de más de un archivo, presenta el plan y espera mi OK.
2. Código solo en el **tema `dryicepack`**, el **plugin `dryicepack-tienda`** y, mientras dure la transición, la **landing de Halloween**. Nunca edites plugins de terceros ni el core de WordPress.
3. **Pregunta antes de:** instalar o cambiar plugins, tocar la base de datos, o cambiar ajustes de WP Rocket, Cloudflare, WooPayments o el hosting.
4. Antes de modificar algo en producción, enséñame el diff.
5. Tras cada cambio: capturas a 390 px y 1440 px, y Lighthouse móvil. Si alguna nota baja, dilo y propón cómo arreglarlo.
6. **No inventes datos** (precios, plazos, reseñas, cifras, certificaciones). Si falta uno, pon `[PENDIENTE: …]` y pregúntame.
7. Haz commit en Git después de cada cambio que funcione, con mensaje en español. Repositorio: `brunoplanes10-create/DRYICEPACK`.
8. Al terminar cada tarea, resumen corto:
   - qué cambió
   - archivos tocados
   - métricas antes/después
   - qué queda pendiente
9. Si te corrijo lo mismo dos veces, propón añadir la regla a este archivo.
10. Las fotos y vídeos originales no se suben a Git. Solo las versiones optimizadas.

---

## Prioridades (en este orden)
1. **Campaña de Halloween**: va contra reloj, ver abajo.
2. **Tema `dryicepack` + plugin `dryicepack-tienda`** con el funnel nuevo, probados antes de retirar Divi.
3. **Velocidad del servidor y caché**: TTFB, WP Rocket, Cloudflare, bloqueo de bots.
4. **SEO técnico**: se resuelve en gran parte con el tema nuevo (menú sin redirecciones, un H1, poster del vídeo, fuentes). Sitemap limpio.
5. **Bloques** en la tienda y página de **transporte y cadena de frío**.
6. **Titles y meta descriptions** de todas las páginas + landings por ciudad (Barcelona, Madrid, Valencia…).
7. **Animaciones**, al final.

---

## Campaña Halloween 2026
- **Página:** landing `/hielo-seco-halloween/`. H1: "Hielo seco para Halloween: niebla de verdad en tu fiesta".
- **Keywords:** hielo seco Halloween, hielo seco para fiestas, efecto niebla Halloween, cócteles con humo, comprar hielo seco [ciudad].
- **Secciones:**
  1. Ideas: caldero de bruja, niebla por el suelo, ponchera con doble recipiente, entrada o photocall
  2. Formato: 3 mm recomendado para niebla, 16 mm si tiene que durar
  3. Cantidad orientativa (validada): casa 3 kg · fiesta grande 10 kg · bar o evento 15–20 kg
  4. Cómo hacer niebla en 3 pasos
  5. Fecha límite con cuenta atrás
  6. Seguridad bien visible
  7. Bloque B2B para bares, discotecas y organizadores
  8. FAQ con schema
  9. CTA final
- **Fecha límite:** Halloween cae en sábado. Pedido antes de las **12:00 del jueves 29/10** → entrega el viernes 30. Para el sábado 31: pedido antes de las 12:00 del viernes 30, según zona y con suplemento de 11,74 € (IVA incl.). También recogida en Mataró.
- **Promoción:** código de descuento `[PENDIENTE: código e importe]`. Sin urgencias falsas: la única urgencia es la fecha logística.
- **Estética:** negro y naranja, niebla animada y fotos reales. Oscuro y elegante, sin gore.
- **Calendario:**

  | Fecha | Acción |
  |---|---|
  | Hasta el 4/10 | Landing publicada, indexación pedida en Search Console, banner en la home |
  | 5–11/10 | Emails B2B y publicación en Google Business Profile |
  | 12–25/10 | Email a clientes y redes |
  | 26–29/10 | Recordatorio con fecha límite en barra superior y email |
  | Después del 31/10 | Quitar urgencias y dejar la landing viva para 2027 |
