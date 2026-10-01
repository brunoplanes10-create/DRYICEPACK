# Instalación del tema y el plugin nuevos en dryicepack.es

Guía paso a paso para pasar de Divi al tema `dryicepack` y al plugin `dryicepack-tienda`. Se hace desde el panel de WordPress, sin FTP.

**Archivos** (en `dist/`; se generan con `node herramientas/empaquetar.mjs`):

| Archivo | Qué es | Dónde se sube |
|---|---|---|
| `dryicepack-tienda.zip` (59 KB) | Plugin con la lógica de la tienda | Plugins → Añadir nuevo → Subir plugin |
| `dryicepack-tema.zip` (3,2 MB) | Tema: diseño, páginas, textos en ES/CA/EN | Apariencia → Temas → Añadir nuevo → Subir tema |

**Cuándo:**
- Un día laborable a primera hora de la tarde, después del corte de las 12:00. Así no hay pedidos a medio hacer.
- Si no está hecho antes del **20/10**, se deja para el **2/11**, fuera del pico de Halloween (CLAUDE.md).

**Tiempo:** unos 45 minutos con las pruebas.

**Marcha atrás:** se activa otra vez el tema hijo de Divi (paso 12). El plugin se pausa solo y todo vuelve a como estaba.

---

## Antes de empezar

### 0. Copia de seguridad
1. En el panel de Arsys, haz una copia completa: archivos y base de datos.
2. Apunta la fecha y la hora de la copia.
3. Comprueba que no hay pedidos en "Pendiente de pago" de los últimos minutos.

### Datos que faltan
Los textos legales tienen un dato marcado `[PENDIENTE]`: la inscripción en el Registro Mercantil (tomo, folio, hoja e inscripción).

- Lo exige el art. 10 de la LSSI. El aviso legal antiguo en PDF tenía ese campo vacío.
- Está en la escritura de la sociedad o en la gestoría.
- Se escribe en `tema/dryicepack/contenido/{es,ca,en}/legal-aviso-legal.php`, y después se vuelve a generar el .zip.

---

## Instalación

### 1. Subir el plugin
1. Ve a Plugins → Añadir nuevo → **Subir plugin** → `dryicepack-tienda.zip` → Instalar ahora → **Activar**.
2. Mientras el tema hijo de Divi siga activo, el plugin está **en pausa** y no hace nada. No duplica la lógica del `functions.php` del tema hijo, así que activarlo ahora no cambia la web.

### 2. Subir el tema (sin activarlo)
1. Ve a Apariencia → Temas → Añadir nuevo → **Subir tema** → `dryicepack-tema.zip` → Instalar ahora.
2. **No pulses "Activar" todavía.** Con "Vista previa en vivo" puedes ver la portada y las páginas antes del cambio.

### 3. Activar el tema
1. Ve a Apariencia → Temas → Dryicepack → **Activar**.
2. Desde este momento:
   - El tema hijo deja de cargar su `functions.php`.
   - El plugin se pone en marcha: envío por peso, fechas, checkout, factura, cookies, formularios y redirecciones.

### 4. Crear las páginas que faltan
1. Ve a Apariencia → **Páginas Dryicepack** → "Crear las que faltan".
2. Qué crea:
   - Las páginas nuevas: empresas, sectores, zonas, versiones en catalán (/ca/) y en inglés (/en/), y textos legales.
   - Las guías, en castellano (/guias/…), catalán (/ca/guies/…) e inglés (/en/guides/…).
3. Qué conserva: las páginas que ya existen con la misma dirección. Por ejemplo, /que-es-el-hielo-seco/ y /contacto/ se reutilizan y el tema les pone el diseño y el texto nuevos.
4. La tabla de esa pantalla marca en verde cada dirección que ya existe.

### 5. Enlaces permanentes
Ve a Ajustes → Enlaces permanentes:
- **Estructura:** déjala como está ("Nombre de la entrada", `/%postname%/`). Las guías son páginas y ya tienen su dirección.
- **Base de producto:** déjala como está (`/producto/`). La ficha sigue en /producto/hielo-seco/.
- Pulsa **Guardar cambios** aunque no cambies nada: así se regeneran las reglas de direcciones del tema nuevo.

### 6. Ajustes de WooCommerce
- **Precio del pack de 15 kg:** Productos → Hielo seco → Variaciones. En las dos de 15 kg (3 mm y 16 mm), precio **75,90** (sin IVA). Las páginas y la de Halloween leen el precio de WooCommerce y se actualizan solas.
- **WooCommerce → Ajustes → Avanzado → Página de términos y condiciones:** "Condiciones de venta".
- **Ajustes → Privacidad** (de WordPress) → Página de política de privacidad: "Política de privacidad".
- **WooCommerce → Ajustes → Envío → zona España:**
  - Tiene que haber **un** método de envío (el de MRW) y **una** "Recogida local".
  - El plugin calcula el precio del envío por peso. El importe escrito en el método no se usa.
  - Si hay dos métodos de envío, el cliente vería dos opciones con el mismo precio. Deja uno.
- **WooCommerce → Ajustes → Pagos:**
  - **WooPayments** activo. En "Pago exprés", Apple Pay y Google Pay solo en el checkout: el plugin los coloca dentro del bloque de pago, nunca arriba.
  - **Contra reembolso** activo. El plugin lo muestra como "Efectivo al recoger" y **solo** cuando el cliente elige recoger en Mataró.
  - **Factura mensual** activo. Solo lo ven las cuentas de empresa aprobadas.

### 7. Ajustes del plugin
Ve a menú **Dryicepack → Ajustes**:

| Campo | Qué poner |
|---|---|
| Google Analytics 4 | Viene puesto: `G-G1272ZSS41`. Se carga **solo** si el visitante acepta las cookies |
| Correo de avisos | info@dryicepack.es (facturas pedidas, solicitudes de cuenta y presupuestos) |
| Festivos sin salida ni entrega | Vienen cargados los de Cataluña de 2026 y 2027. Añade los locales de Mataró, uno por línea (AAAA-MM-DD) |
| Enlace para reseñas de Google | Viene puesto (tu enlace de g.page). Va en el email de pedido completado |
| Factura completa obligatoria desde | 400 € (lo acordado con la gestoría) |
| Recordatorio de reposición | Actívalo cuando quieras. Avisa a las cuentas de empresa que lo aceptaron cuando les toca repetir el pedido, con un enlace para darse de baja |

### 8. Site Kit (Google Analytics)
Como el ID de GA4 ya está en el plugin, en Site Kit desactiva que inserte su código de Analytics: Site Kit → Ajustes → Analytics → Editar → código de seguimiento: **No**.
- Si no lo desactivas, Analytics se carga dos veces y cada visita se cuenta doble. Además, el código de Site Kit no espera al banner de cookies del plugin.
- Search Console y los informes de Site Kit siguen funcionando igual.
- Si hay un plugin de cookies anterior (Complianz u otro), desactívalo: el banner ya lo pone el plugin.

### 9. Idiomas
- **Catalán.** Para que los textos de WooCommerce (carrito, checkout, emails) salgan en catalán:
  1. Ve a Ajustes → Generales → Idioma del sitio → **Català** → Guardar. Así WordPress descarga el paquete.
  2. Vuelve a poner **Español** → Guardar.
  3. Ve a Escritorio → Actualizaciones → **Actualizar traducciones**. Así se descarga también el catalán de WooCommerce.
- **Inglés.** Viene con WordPress, no hay que instalar nada.

### 10. Plugins que sobran
Desactívalos **después** de las pruebas del paso 11 (y con la tienda funcionando):

| Plugin | Por qué sobra |
|---|---|
| Order Delivery Date (Lite) | El plugin pone las fechas de entrega y de recogida. Sigue guardando la fecha en el mismo campo, así que los pedidos antiguos y nuevos se ven igual |
| Checkout Field Editor (ThemeHigh) | Si sigue activo, pisa los campos del checkout nuevo |
| Flying Pages | WP Rocket ya hace la precarga, y Flying Pages precarga páginas que no hacen falta |
| Flexible Shipping | Solo si el método de envío de la zona **no** es suyo. Si lo es, crea antes un "Precio fijo" llamado "Envío MRW" y borra el suyo (el precio lo pone el plugin) |

**Dos semanas después**, con todo estable:
- Elimina **Divi** y el **tema hijo** (Apariencia → Temas).
- Elimina **WP File Manager**, que es un riesgo de seguridad.

### 11. Pruebas (en el móvil y en el ordenador)
1. **Páginas:** portada, /producto/hielo-seco/, /empresas/, /envios-y-plazos/, /contacto/, /hielo-seco-halloween/, /hielo-seco-barcelona/, /ca/ y /en/. Que se vean con el diseño nuevo.
2. **Pedido con envío:** 10 kg, 3 mm.
   - El total debe ser 81,81 € (64,49 € + envío 17,32 €, IVA incluido).
   - Elige fecha y paga con tarjeta. Usa WooPayments en modo prueba o un pedido real que después reembolsas.
3. **Pedido con recogida:**
   - Tiene que aparecer solo "Efectivo al recoger", sin coste de envío de lunes a viernes. El sábado suma 11,74 €.
   - Se puede elegir de lunes a sábado, también los festivos.
   - Con un código postal de fuera de Cataluña sale un aviso.
   - En el pedido (WooCommerce → Pedidos), escribe la hora en "Hora de recogida" y elige **Acciones del pedido → Confirmar recogida al cliente (email)**. Al cliente le llega el día, la hora, la dirección, el importe en efectivo y los avisos para el coche. La lista de pedidos marca "Confirmada" o "Sin confirmar".
4. **Más de 150 kg fuera de la provincia de Barcelona:** con un código postal de Madrid (28001) y 160 kg, el envío desaparece y sale el aviso con WhatsApp, teléfono y email. Con un código postal 08… se puede comprar hasta 250 kg.
5. **Sábado:** elige un sábado como fecha de entrega. Debe sumar 11,74 € (IVA incluido).
6. **Más de 400 €:** sin razón social ni NIF no deja pagar. Con B66800103 sí.
7. **Factura:** en la página de gracias, el botón "¿Necesitas factura?" abre /factura/. Rellénalo y comprueba que llega el aviso a info@ y que queda la nota en el pedido.
8. **Cookies:** el banner tiene Aceptar, Rechazar y Elegir. Con "Rechazar", Analytics no se carga (en el navegador: F12 → Red → filtrar "gtag").
9. **Formularios:** envía uno de contacto y comprueba que llega al email de avisos y a Dryicepack → Solicitudes.
10. **Idioma:** cambia a CA y a EN desde el selector. Después vuelve a una página en castellano: debe salir en castellano.
11. **WP Rocket:** vacía la caché (WP Rocket → Vaciar caché) y repite la prueba 2.
    - Si el selector de fechas no responde, ve a WP Rocket → Archivos → Retrasar la ejecución de JavaScript → Exclusiones.
    - Añade `dryicepack-tienda` y `jquery`.

### 12. Si algo va mal
Ve a Apariencia → Temas → activar **Divi Child DryIcePack**:
- El plugin detecta el tema hijo y se pone en pausa.
- La web vuelve a estar exactamente como antes.
- Las páginas nuevas creadas en el paso 4 no molestan: Divi las mostraría vacías, pero nadie llega a ellas desde el menú antiguo.

---

## Después de la instalación

### 13. Limpieza de direcciones antiguas
- **Mueve a la papelera** la página "Programar suministro de hielo seco" (/programar-suministro-de-hielo-seco/).
  - Su contenido está ahora en /empresas/.
  - Mientras la página exista, la redirección del plugin no actúa: nunca tapa una página que existe.
- Si /comprar-hielo-seco/ es una página (el sitemap la lista), muévela también a la papelera. El plugin la redirige a la ficha.
- Borra la entrada de ejemplo "Hola mundo", si existe.
- **PDFs legales antiguos:** en Medios, bórralos tú. Hay 4: aviso legal, privacidad, cookies y condiciones de venta. Al borrarlos, sus direcciones redirigen a las páginas legales nuevas.

### 14. Rank Math y Search Console
1. Ve a Rank Math → Sitemap y abre `/sitemap_index.xml`.
   - Tienen que salir las páginas nuevas (también /ca/ y /en/).
   - No tienen que salir /comprar-hielo-seco/ ni /programar-suministro-de-hielo-seco/.
2. En Search Console → Sitemaps, envía `sitemap_index.xml` otra vez.
3. En Search Console → Inspección de URLs, pide la indexación de:
   - /hielo-seco-halloween/
   - /empresas/
   - /hielo-seco-barcelona/
   - /hielo-seco-transporte/
   - /hielo-seco-hosteleria/
   - /ca/
4. **Titles y descripciones:** el tema las pone en sus páginas, por encima de lo guardado en Rank Math. Así el texto de la página y el de Google no se desalinean. Rank Math sigue haciendo el canonical, el sitemap, los robots y Open Graph.
5. **Datos estructurados:** pasa la portada y la ficha por la [prueba de resultados enriquecidos](https://search.google.com/test/rich-results). Tienen que salir Store, FAQPage, BreadcrumbList y Product, sin errores.

### 15. Velocidad (fase siguiente; se pregunta antes de tocar)
El tema ya da 87–95 en Lighthouse móvil con un servidor lento. Lo que queda es el **TTFB de Arsys (2–11 s)**: hay que cachear el HTML.

Son cambios en WP Rocket y Cloudflare: se revisan juntos antes de aplicarlos (CLAUDE.md, regla 3).
- Precarga de caché de WP Rocket.
- Regla de Cloudflare "Cache Everything" que excluya carrito, checkout, mi cuenta, /factura/ y las cookies de WooCommerce.

### 16. Google Business Profile
- **Nombre, dirección y teléfono** exactamente como en la web: DryIcePack · Camí Ca La Madrona 19 D, 08304 Mataró · 936 73 76 41.
- **Horario:** lunes a viernes, 9:00–18:00.
- **Categoría principal:** la más específica que ofrezca Google para hielo seco o hielo.
- **Zona de servicio:** provincia de Barcelona. **Web:** https://dryicepack.es/.
- **Productos:** los 4 packs (3, 10, 15 y 20 kg) con su precio y enlace a la ficha (`/producto/hielo-seco/?kg=10`).
- **Fotos reales:** la nave, las cajas, los pellets y los nuggets. Una por semana mejor que veinte de golpe.
- **Reseñas:**
  - Copia el enlace "Pedir reseñas" en Dryicepack → Ajustes (paso 7).
  - Pide reseña a los clientes habituales: Italpizza, Sushitok, PSG Tech y Semillas Fitó, con su permiso.
- **Publicaciones:** la de Halloween entre el 5 y el 11/10 (calendario de CLAUDE.md).
