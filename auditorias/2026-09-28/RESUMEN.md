# Auditoría 28/09/2026 · Página de Halloween

Lighthouse móvil (CPU ×4, red simulada). La página de Halloween se midió en el WordPress de prueba local (Playground).
Su TTFB (~1–2 s) es del simulador PHP y no es representativo de producción.

| | Web actual · /aplicaciones-del-hielo-seco/ | Halloween · 1.ª versión | Halloween · final |
|---|---|---|---|
| Rendimiento | 60 | 78 | **97** |
| Accesibilidad | 92 | 100 | **100** |
| Buenas prácticas | 96 | 100 | **100** |
| SEO | 100 | 92* | 92* |
| LCP | 6,1 s | 2,3 s | **2,4 s** |
| TBT | 70 ms | 690 ms | **0 ms** |
| CLS | 0,192 | 0,007 | **0** |
| TTFB | 3,7 s | 2,3 s (local) | 1,1 s (local) |

\* Solo falta la meta descripción: en producción la pone Rank Math.

**Qué bajó el TBT de 690 a 0 ms:**
- Las animaciones de partes internas de SVG (alas, burbujas, olas, vapor) obligaban a recalcular el diseño en cada fotograma. Ahora cada pieza es una capa `<svg>` propia que se anima entera, en la tarjeta gráfica.
- La ambientación (niebla, brasas, murciélagos, vela) solo existe cuando la página ya ha cargado, y solo en la sección que está en pantalla.
- La luz de vela ya no usa `mix-blend-mode`.
- El JS agrupa las lecturas de posición: sin recálculos repetidos.
- CSS y JS minificados: 31,6 → 24,8 KB y 6,9 → 3,8 KB.
