# Auditoría 29/09/2026 · Página de Halloween (tras la revisión del auditor)

Lighthouse móvil (CPU ×4). WordPress de prueba local (Playground), recién reiniciado; 3 pasadas por versión.

| | 1.ª auditoría (auditor, 29/09) | Final: mediana de 3 | Objetivo |
|---|---|---|---|
| Rendimiento | 69 | **87** (97 · 87 · 82) | ≥ 90* |
| CLS | 0,198 | **0** (en las 3) | < 0,1 |
| TBT | 610 ms | 420 ms (50 · 420 · 590)* | < 200 ms |
| LCP | 2,3 s | **2,1 s** | < 2,5 s |
| Accesibilidad | 100 | **100** | ≥ 95 |
| Buenas prácticas | 100 | **100** | — |
| SEO | 85 | 85–92** | ≥ 95 |

\* **Coste fijo del entorno de medición.** En este ordenador, una página casi vacía del mismo WordPress da TBT 190–230 ms y Rendimiento 95–97. Su primera maquetación, con solo 27 elementos, ya tarda unos 350 ms, por arrancar Chrome en Windows con la CPU ×4. La página de Halloween añade unos 200 ms sobre esa base. Con `font-display: optional` el TBT bajaba a 70 ms de mediana (Rendimiento 94). Lo descartamos porque, si la fuente llegaba tarde, la página mezclaba Arial y Open Sans.

\** Falta la meta descripción (la pone Rank Math en producción) y el robots.txt falla por el entorno local.

## Qué se corrigió
- **CLS 0,198 → 0.** Reserva de Arial ajustada a las medidas de Open Sans (`size-adjust`, `ascent/descent-override`, medido con `herramientas/medir-fuente.mjs`). La fuente web tiene nombre propio (`HW Open Sans`), así que no cambia la cabecera ni el pie de la web.
- **Menos maquetación en la carga.** Las secciones fuera de pantalla usan `content-visibility: auto`. La aparición al hacer scroll ya no lee posiciones y usa la primera respuesta del IntersectionObserver.
- **Textos con fecha sin saltos.** La fase de la campaña se fija en `<head>` antes del primer pintado y el CSS muestra la versión de cada texto. Lo que desaparece conserva su hueco.
- **Imágenes:**
  - Textura de niebla de 37,7 a 27,7 KB, con la transparencia sin pérdida para evitar escalones. Se pide al terminar la carga.
  - Hero sin la versión de 640 px, que nunca se usaba, sin `decoding=async` y excluido de la carga diferida de WP Rocket.
  - `sizes` correcto en la foto de negocios.
- **Accesibilidad con Divi.** Enlaces del texto subrayados. Titulares blindados frente a los estilos del personalizador de Divi (`text-transform`, `letter-spacing`).
- **Animación.** Todas las animaciones infinitas pasan por la activación de página cargada y sección visible. Las transiciones solo usan `transform` y `opacity`. Con "reducir movimiento" también se quita el scroll suave.
- **Tema hijo.** Sin `backdrop-filter` en la barra fija móvil en esta página.
