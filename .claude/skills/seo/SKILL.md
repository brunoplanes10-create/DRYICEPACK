---
name: seo
description: SEO de dryicepack.es — keywords, títulos, meta descripciones, schema, enlazado interno y SEO local. Úsala al crear o editar cualquier página, entrada o ficha de producto.
---

# SEO Dryicepack

## Situación de partida
- Muchas páginas en **página 2** de Google y CTR muy bajo (~0,9 %). El problema principal es posicionar y conseguir clics, no tráfico de marca.
- Prioridad: **long-tail transaccional y local** antes que keywords genéricas. Google Business Profile es parte de la estrategia.

## Mapa de intención (una página = una intención principal)
| Intención | Ejemplos de keyword | Página destino |
|---|---|---|
| Compra genérica | comprar hielo seco, hielo seco precio, hielo seco online | Home / tienda |
| Compra local | hielo seco Barcelona, hielo seco Madrid, hielo seco Valencia… | Landings por ciudad/zona |
| Envío | hielo seco envío 24h, hielo seco a domicilio | Envíos y plazos |
| Uso eventos | hielo seco para fiestas, efecto niebla hielo seco, hielo seco bodas | Ocio y eventos |
| Estacional | hielo seco Halloween | Landing Halloween (ver skill `halloween`) |
| Informacional | qué es el hielo seco, cuánto dura el hielo seco, es peligroso el hielo seco | Guías / blog → enlazan a compra |
| B2B | proveedor hielo seco, suministro hielo seco hostelería, hielo seco laboratorio | Aplicaciones por sector |

Antes de crear una página nueva, comprueba que no canibaliza a otra existente. Si dos páginas atacan la misma keyword, propón fusionar o diferenciar.

## Checklist por página
1. **Title** ≤ 60 caracteres: keyword principal al principio + beneficio + marca. Ej.: `Hielo seco en Barcelona | Entrega en 24 h – DryIcePack`.
2. **Meta description** 140–155 caracteres, con un dato concreto y una llamada a la acción. Escrita para ganar el clic (el CTR es nuestro punto débil).
3. **Un solo H1**, con la keyword. H2 que respondan preguntas reales del cliente.
4. **URL** corta, en minúsculas, con guiones, sin fechas.
5. **Primer párrafo**: responde a la intención en las dos primeras frases.
6. **Enlazado interno**: mínimo 3 enlaces a páginas relacionadas con anchor descriptivo (nunca "haz clic aquí"). Toda página informacional enlaza a una de compra.
7. **Imágenes**: nombre de archivo descriptivo, `alt` útil, WebP/AVIF, `width` y `height` definidos.
8. **FAQ** al final si hay dudas frecuentes, con schema FAQPage solo si las preguntas son reales.

## Schema (JSON-LD)
- Home y páginas locales: `LocalBusiness` / `Organization` con dirección real de Mataró, teléfono y área de servicio (España peninsular).
- Fichas de producto: `Product` + `Offer` con precio real y disponibilidad. Nunca inventes `aggregateRating` ni reseñas.
- Guías: `Article` o `HowTo` cuando encaje.
- `BreadcrumbList` en todas las páginas interiores.
- Si el sitio ya usa un plugin SEO (Yoast / Rank Math), no dupliques schema: amplía el del plugin o desactiva la parte que choque, y avisa antes.

## SEO técnico a vigilar
- La web debe responder 200 a rastreadores. Si un firewall, CDN o plugin de seguridad devuelve 503/403 a bots, es **prioridad máxima**: Google no puede posicionar lo que no puede leer.
- Sitemap XML limpio, sin páginas de prueba ni `noindex`.
- Canonical correcto en cada página; sin contenido duplicado entre variaciones de producto.
- Core Web Vitals en verde (ver skill `rendimiento`).

## Cómo entregar el trabajo
Al terminar, muestra una tabla: página · keyword principal · title · meta description · cambios hechos. Así Andrés puede revisarlo de un vistazo.
