# Checklist de la web: 20 puntos

**Estado a 01/10/2026:** tema `dryicepack` 2.0.0 y plugin `dryicepack-tienda` 1.0.0, comprobados sobre los .zip de `dist/` en el WordPress de prueba.

Para comprobarlo:
- `node herramientas/auditar-sitio.mjs` recorre todas las páginas (101) desde la portada.
- Lighthouse móvil: resultados en `auditorias/2026-10-01/RESUMEN.md`.

**Estados:**
- **Hecho:** resuelto en el código y verificado.
- **Falta un paso:** resuelto en el código, pero hay que hacer un ajuste en el panel al instalar (el número de paso remite a `INSTALACION.md`).
- **Pendiente:** depende del propietario.

| # | Punto | Estado | Qué hay y cómo se ha comprobado |
|---|---|---|---|
| 1 | Aviso legal | **Hecho, con un dato pendiente** | /aviso-legal/ en ES, CA y EN: razón social, NIF, domicilio social, nave, email y teléfono de la web. Por decisión del propietario (01/10/2026) se publica sin la inscripción en el Registro Mercantil, que el art. 10 de la LSSI pide a las sociedades: conviene añadirla en cuanto se tenga. |
| 2 | Política de privacidad | **Falta un paso** (6) | /politica-de-privacidad/: responsable, finalidades, base legal, plazos, quién más ve los datos (Arsys y Cloudflare, WooPayments/Stripe, la mensajería, la gestoría y Google Analytics solo con consentimiento) y derechos ante la AEPD. Hay que elegirla en Ajustes → Privacidad. |
| 3 | Cookies | **Falta un paso** (8) | Banner con Aceptar, Rechazar y Elegir, los tres igual de visibles. Google Consent Mode v2 empieza en "denegado" y GA4 se carga solo tras aceptar. Fuera la atribución de pedidos de WooCommerce, que ponía cookies de seguimiento sin pedirlas. Política en /politica-de-cookies/. Hay que quitar el código de Analytics de Site Kit. |
| 4 | HTTPS | **Hecho** | La web ya va en HTTPS con Cloudflare. El tema construye todas las direcciones con `home_url()`, sin rutas fijas en http, así que no hay contenido mixto. |
| 5 | Title y meta description | **Hecho** | 101 páginas recorridas. Los titles en castellano tienen ≤ 60 caracteres, con la keyword al principio. Las descripciones tienen 140–155 caracteres, con un dato y una llamada a la acción. Las 18 zonas usan una versión corta si el nombre es largo. Algunas en catalán quedan en 130–139 caracteres. |
| 6 | Datos estructurados | **Falta un paso** (14) | Hay un esquema de cada tipo, todo con datos reales: Store (negocio, NAP, horario), FAQPage (solo preguntas que están en la página), BreadcrumbList y Product + Offer con los precios reales en CA y EN. En castellano el Product lo pone Rank Math. Sin reseñas ni valoraciones inventadas. El JSON-LD es válido en las 101 páginas. Después de instalar, pasar la prueba de resultados enriquecidos. |
| 7 | Sitemap y robots | **Falta un paso** (13–14) | Las páginas nuevas son páginas reales de WordPress, así que Rank Math las mete en su sitemap. No se indexan /factura/, etiquetas ni autores. Faltan dos cosas: mandar a la papelera las páginas antiguas, para que no salgan en el sitemap y actúe la redirección, y reenviar el sitemap a Search Console. |
| 8 | Google Business Profile | **Pendiente** | Lo hace el propietario con su acceso. Los pasos están en `INSTALACION.md`, paso 16: nombre, dirección y teléfono idénticos a la web, productos con precio, fotos reales, enlace de reseñas y publicación de Halloween. |
| 9 | Favicon | **Hecho** | SVG (copo de hielo sobre azul marino), PNG de 32 px y apple-touch-icon de 180 px. Si en Ajustes → Generales hay un "Icono del sitio", manda ese. |
| 10 | Textos alternativos | **Hecho** | 0 imágenes sin `alt` en 101 páginas. Las fotos describen lo que se ve, por ejemplo "Caja de hielo seco en nuggets de 16 mm, abierta y soltando vapor frío". Las decorativas llevan `alt=""`. |
| 11 | Imágenes comprimidas | **Hecho** | Todo en WebP, en 3 o 4 anchos con `srcset`, `sizes`, `width` y `height`. Carga diferida salvo la imagen principal, que lleva `fetchpriority="high"` y precarga. La foto principal pesa ≤ 73 KB en móvil. La versión de 1500 px para pantallas grandes pesa como mucho 135 KB. El logo también tiene `srcset`. |
| 12 | Velocidad | **Falta un paso** (15) | Lighthouse móvil: Rendimiento 87–95, TBT 0–40 ms, CLS ≤ 0,05 (0,001 en la portada). Fuentes propias en WOFF2 con reservas calibradas y sin jQuery fuera de la tienda. El LCP medido (2,0–3,0 s) incluye los 3–4 s de respuesta del servidor de prueba. Lo que queda es el TTFB de Arsys, que se resuelve cacheando el HTML con WP Rocket y Cloudflare (se pregunta antes). |
| 13 | Contraste | **Hecho** | Accesibilidad 100 en las 10 páginas medidas (WCAG AA). Corregido el texto suave sobre azul marino. La cifra gigante del pie es decoración. |
| 14 | Móvil | **Hecho** | Diseño mobile-first, revisado con capturas a 390 px y 1440 px. Botones principales de 56 px de alto (46 px en la cabecera), sin scroll horizontal y con barra de compra fija en la ficha. Checkout en una columna. |
| 15 | Página 404 | **Hecho** | 404 propia con botón de compra y enlaces a inicio, envíos, empresas y contacto. Devuelve estado HTTP 404. Las direcciones antiguas que dan 404 se redirigen con un 301 a su página nueva: 32 direcciones de Search Console, más los patrones antiguos de packs y categorías. |
| 16 | Enlaces rotos | **Hecho** | 0 enlaces internos rotos o con redirección en 101 páginas. El menú enlaza directamente a las direcciones finales, que era el problema 3 de la auditoría del 28/09. |
| 17 | Formularios sin spam | **Hecho** | Campo trampa, tiempo mínimo de 3 s firmado, máximo de 5 envíos por hora y por IP, filtro de enlaces y nonce. Sin captcha ni servicios externos. Cada solicitud se guarda en Dryicepack → Solicitudes y llega por email. |
| 18 | WhatsApp visible | **Hecho** | Botón flotante en todas las páginas menos el checkout, con un mensaje ya escrito. También está en la cabecera, la portada, la ficha, contacto y el pie: 686 980 471. |
| 19 | Analítica | **Falta un paso** (7) | GA4 con Consent Mode. Eventos: `add_to_cart`, `purchase` (con importe), `generate_lead` (formularios), `click_telefono`, `click_whatsapp` y `click_email`. Falta poner el ID de GA4 en Dryicepack → Ajustes. |
| 20 | Un solo CTA | **Hecho** | La cabecera tiene un único botón ("Comprar"). Cada página tiene una acción principal y la secundaria va en texto o en contorno. Los botones tienen como mucho 3 palabras, con verbo. |

## Lo que no depende del código

| Pendiente | Quién | Notas |
|---|---|---|
| Inscripción en el Registro Mercantil para el aviso legal | Propietario / gestoría | Tomo, folio, hoja e inscripción. De momento se publica sin ella. |
| Revisión de los textos legales | Gestoría o abogado | Domicilio social (Ronda Alfonso X El Sabio 10) frente al email y teléfono de contacto de la web |
| Precio del pack de 20 kg | Propietario | 98,60 € + IVA rompe la escala: por kilo sale más caro que el de 15 kg (68,40 €) |
| Bizum | Propietario | Necesita un TPV de Redsys con el banco. Las cuentas de empresa ya pueden pagar con factura mensual. |
| Logos de clientes en la web | Propietario | Solo con permiso escrito de cada cliente |
| Código de descuento de Halloween | Propietario | `[PENDIENTE: código e importe]` |
| Plan de Odoo | Propietario | La exportación CSV ya funciona. La conexión automática necesita el plan Custom de Odoo Online. |
