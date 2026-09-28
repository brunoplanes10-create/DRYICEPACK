---
name: rendimiento
description: Rendimiento web de dryicepack.es — Core Web Vitals, Lighthouse, imágenes, fuentes, JS/CSS y caché en WordPress. Úsala al auditar, al optimizar y antes de dar por terminado cualquier cambio.
---

# Rendimiento Dryicepack

## Objetivos
| Métrica | Objetivo (móvil) |
|---|---|
| LCP | < 2,5 s |
| INP | < 200 ms |
| CLS | < 0,1 (ideal 0) |
| Lighthouse Rendimiento | ≥ 90 |
| Peso total home | < 1,5 MB |
| JS total | < 300 KB comprimido |

## Método: medir → cambiar → medir
1. **Línea base**: Lighthouse móvil de home, tienda, una ficha de producto y una landing. Guarda los resultados en `auditorias/AAAA-MM-DD/`.
2. Identifica los 3 problemas con más impacto (no arregles 20 cosas a la vez).
3. Arregla uno, vuelve a medir y haz commit.
4. Presenta una tabla antes/después.

Comando de referencia (con Lighthouse CLI instalado):
```bash
npx lighthouse http://dryicepack.local --preset=perf --form-factor=mobile --output=html --output-path=auditorias/home.html
```

## Checklist WordPress
**Imágenes**
- WebP/AVIF, tamaños responsive (`srcset`), `width`/`height` siempre.
- `loading="lazy"` en todo lo que no se ve al principio; **nunca** en la imagen LCP, que lleva `fetchpriority="high"`.
- Nada de imágenes de más de 200 KB. Hero ≤ 120 KB.

**Fuentes**
- Autoalojadas WOFF2, solo pesos usados, `preload` de la principal, `font-display: swap`.
- Eliminar Google Fonts cargadas por el tema o plugins si ya se autoalojan.

**CSS/JS**
- Quitar CSS y JS de plugins en páginas donde no se usan (p. ej. scripts de WooCommerce fuera de tienda/carrito/checkout, formularios de contacto solo donde hay formulario).
- JS no crítico con `defer`. CSS crítico inline en el hero si compensa.
- Si hay constructor visual (Elementor, Divi…), revisa widgets y animaciones pesadas del propio constructor.

**Servidor y caché**
- Caché de página, compresión Brotli/Gzip, HTTP/2 o HTTP/3, caché de navegador para estáticos.
- Revisa que ningún plugin de seguridad o CDN bloquee a Googlebot/Lighthouse (errores 503/403).
- No cambies configuración de servidor ni instales plugins de caché sin preguntar.

**Terceros**
- Inventario de scripts externos (analytics, chat, píxeles). Cada uno debe justificar su peso. Cargar chats y píxeles tras la interacción o con retraso.

## Límite
Rendimiento manda sobre decoración: si una mejora de diseño o animación rompe los objetivos, se simplifica.
