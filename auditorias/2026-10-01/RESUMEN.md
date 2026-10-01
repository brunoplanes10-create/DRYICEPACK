# Auditoría 01/10/2026 · Tema `dryicepack` 2.0.0 + plugin `dryicepack-tienda` 1.0.0 (paquetes .zip)

Lighthouse 13.5 móvil (Moto G simulado, CPU ×4), sobre los .zip de `dist/` montados en el WordPress de prueba local (Playground).
Se mide lo que se va a subir: CSS y JS minificados.

**Aviso sobre el TTFB.** El servidor de prueba (PHP en WebAssembly) tarda 3–4,5 s en responder cada página. Lighthouse lo
traslada al LCP. En Arsys con la caché de WP Rocket y Cloudflare (objetivo TTFB < 0,8 s) el LCP baja en la misma medida.
El retraso propio de la página (render delay del elemento LCP) es de 0,3 s en la portada.

## Antes / después

| Página | Antes (1.ª pasada) | Después | LCP antes → después | CLS antes → después |
|---|---|---|---|---|
| Portada | 73–76 / 97 / 100 / 92 | **87–88 / 100 / 100 / 92** | 3,1 s → 3,0 s* | 0,033 → **0,001** |
| Producto | 75 / 97 / 100 / 92 | **90–91 / 100 / 100 / 92** | 4,1 s → 2,7 s* | 0 → 0 |
| Halloween | — | 90–91 / 100 / 100 / 92 | → 2,6 s* | 0,107 → **0,05** (héroe: 0) |
| Hostelería | 88–89 / 96 / 100 / 92 | **94 / 100 / 100 / 92** | 2,2 s | 0,024 → 0,001 |
| Zona Barcelona | 90–92 / 96 / 100 / 92 | **95 / 100 / 100 / 92** | 2,0 s | 0,001 |
| Transporte | — | 95 / 100 / 100 / 92 | 2,1 s | 0,001 |
| Empresas | — | 91 / 100 / 100 / 92 | 2,7 s* | 0 |
| Contacto | — | 92 / 100 / 100 / 92 | 2,1 s | 0,015 |
| /ca/ y /en/ | — | 87 / 100 / 100 / 92 | 3,0 s* | 0,02 |

Orden de las notas: Rendimiento / Accesibilidad / Buenas prácticas / SEO. TBT 0–40 ms en todas.
\* Con 3–4 s de TTFB del entorno local.

**SEO 92 en lugar de 100:** solo falla `robots.txt`, que en el entorno local no existe. En producción lo genera Rank Math.

## Qué se corrigió
- **Ficha de producto sin jQuery.** La compra usa un formulario normal, así que fuera del carrito, el checkout y Mi cuenta no se cargan jQuery, jquery-migrate ni los scripts de WooCommerce (unos 100 KB que bloqueaban el pintado). Probado: ficha → checkout con 10 kg 16 mm = 81,81 €.
- **Fuentes de reserva recalibradas.** Instrument Serif mide el 75,5 % de Georgia, no el 86 %, y el titular de Halloween saltaba de 3 a 2 líneas al cargar la fuente. Georgia y Times tienen ahora su propia escala, y Arial pasa al 101,3 % para Geist. Con y sin fuentes web, los héroes de portada, Halloween, hostelería, empresas, producto, qué es y zonas quedan al píxel.
- **Niebla del héroe en móvil** en una caja de altura fija: ya no se mueve si el héroe crece (CLS 0,027 → 0).
- **Contraste:** texto suave sobre azul marino (`.tono-marino .suave`). La cifra gigante «−78,5 °C» del pie es ahora decoración dibujada con CSS (`content`) y oculta a lectores de pantalla.
- **Imágenes:** logo con `srcset` (280/400/721 px), y la foto de la ficha tiene una versión de 800 px (antes saltaba de 640 a 1000).
- **Metadatos:** titles ≤ 60 y descriptions 140–155 en las páginas en castellano. Las zonas con nombre largo usan una descripción corta. Halloween tiene meta propia y las guías usan su meta también sin Rank Math.
- **Recorrido completo** (`herramientas/auditar-sitio.mjs`): 101 páginas, todas con un H1, todas las imágenes con `alt`, JSON-LD válido y 0 enlaces internos rotos o con redirección.
