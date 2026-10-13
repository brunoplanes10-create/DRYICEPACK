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
| 1 | Aviso legal | **Hecho** | /aviso-legal/ en ES, CA y EN: razón social, NIF, domicilio social, nave, email, teléfono y, desde el 07/10/2026, los datos registrales que pide el art. 10 de la LSSI: Registro Mercantil de Barcelona, tomo 45484, folio 53, sección 8, hoja B-489371, inscripción 1.ª (comprobados en el BORME-A-2016-148-08, asiento 322093). |
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
| ~~Inscripción en el Registro Mercantil para el aviso legal~~ | Hecho el 07/10/2026 | Tomo 45484, folio 53, sección 8, hoja B-489371, inscripción 1.ª, en los tres idiomas. |
| Revisión de los textos legales | Gestoría o abogado | Domicilio social (Ronda Alfonso X El Sabio 10) frente al email y teléfono de contacto de la web. Nuevo el 03/10/2026: la recogida se paga a precio redondo con IVA incluido, solo en efectivo y con el importe exacto (condiciones de venta, apartado «Recogida en Mataró»). |
| ~~Precio del pack de 20 kg~~ | Decidido el 03/10/2026 | El 15 kg sube a 74,90 € + IVA (4,99 €/kg) y el 20 kg se queda en 98,60 € (4,93 €/kg): la escala ya baja con cada pack. Hay que cambiarlo en WooCommerce (INSTALACION.md, paso 6). |
| Stock del pack de 3 kg | Propietario | Poner en WooCommerce las 29 cajas EPS de 3 kg que quedan, repartidas entre 3 y 16 mm. Cuando se acaben, la web pasa sola al pack de 5 kg (33,90 €). |
| Cajas de cartón de 20 kg | Propietario | Quedan 8 (packs de 15 y 20 kg) y 24 cajas EPS de 20–30 kg. Pedir cartón antes de Halloween. |
| Bizum | Propietario | Necesita un TPV de Redsys con el banco. Las cuentas de empresa ya pueden pagar con factura mensual. |
| ~~Logos de clientes en la web~~ | Decidido el 01/10/2026 | Permiso confirmado por el propietario en el chat: Nissan Formula E Team, Italpizza, Sushitok y Semillas Fitó (PSG Tech no). |
| ~~Código de descuento de Halloween~~ | Decidido el 01/10/2026 | Sin descuento. La única ventaja de Halloween es la recogida del sábado 31/10 sin suplemento (no es una rebaja de precio). |
| Plan de Odoo | Propietario | La exportación CSV ya funciona. La conexión automática necesita el plan Custom de Odoo Online. |
