---
name: diseno
description: Dirección de arte y diseño gráfico original de Dryicepack — sistema visual, composición, iconografía e ilustración SVG. Úsala al diseñar o rediseñar cualquier página, sección, banner o componente.
---

# Diseño gráfico Dryicepack

## Concepto de marca: "Frío técnico"
Dryicepack es precisión a −78,5 °C. El diseño transmite **frío, limpieza y confianza técnica**, con un punto de espectáculo (la niebla del hielo seco es visualmente única). Referencias de sensación: etiquetado de laboratorio, fichas técnicas industriales, fotografía de producto sobre fondo oscuro con vapor.

## Paleta (tokens CSS)
Define todo como variables en `:root` del tema hijo; nunca colores sueltos en el código.
```css
--color-noche: #0B1622;      /* fondo oscuro principal, secciones hero */
--color-hielo: #EAF4FA;      /* fondo claro */
--color-blanco: #FFFFFF;
--color-acero: #5B6B7A;      /* texto secundario */
--color-tinta: #13202C;      /* texto principal sobre claro */
--color-criogenico: #3FB6E8; /* acento: enlaces, iconos, detalles */
--color-accion: #FF6A2B;     /* SOLO botones de compra y CTAs */
```
- El naranja de acción contrasta con toda la gama fría: úsalo solo para lo que queremos que se pulse.
- Contraste mínimo WCAG AA (4,5:1 en texto normal). Comprueba cada combinación.
- Si Andrés ya tiene logo y colores de marca, esos mandan: adapta esta paleta a ellos y pregunta antes.

## Tipografía
- Titulares: una sans geométrica o grotesca con carácter (p. ej. *Space Grotesk* o *Manrope*), peso 600–700, interletraje ligeramente negativo en tamaños grandes.
- Texto: *Inter* o la del sistema. 16–18 px, interlineado 1,6.
- Datos técnicos (−78,5 °C, 3 mm, 24 h): fuente monoespaciada o tabular, como en una ficha técnica. Es un rasgo de identidad.
- Máximo 2 familias. Autoalojadas en WOFF2, con `font-display: swap`, solo los pesos usados.

## Composición
- Retícula de 12 columnas, ancho máximo 1200 px, márgenes laterales de 16 px en móvil.
- Escala de espaciado de 8 px (8, 16, 24, 32, 48, 64, 96).
- Mucho aire. Secciones alternando fondo `noche` y `hielo` para dar ritmo.
- Diseño **mobile-first**: la mayoría de pedidos urgentes se hacen desde el móvil.

## Qué hace el diseño original (y no genérico)
- **Elementos propios**: la cifra "−78,5 °C" como elemento gráfico gigante; etiquetas tipo ficha técnica; divisores con forma de pellets; iconos de línea hechos a medida en SVG (caja EPS, pellet 3 mm, nugget 16 mm, camión 24 h, reloj 12:00, guante).
- **Ilustración SVG propia** para conceptos (cómo funciona, sectores) en lugar de imágenes de stock.
- **Fotografía real** del producto y del vapor cuando exista. Si no hay, deja un hueco marcado `[FOTO PENDIENTE: descripción]` en vez de meter stock genérico.

## Prohibido (el "look IA genérico")
- Degradados morado-azul, blobs difusos, glassmorphism por todas partes.
- Iconos de librería mezclados de estilos distintos.
- Fotos de stock de gente sonriendo a un portátil o copos de nieve navideños.
- Tarjetas idénticas en rejilla de 3 como única solución de maquetación.
- Sombras enormes y esquinas muy redondeadas en todo. Radio máximo 8 px.

## Proceso
1. Antes de diseñar, haz captura del estado actual (móvil y escritorio).
2. Propón **2–3 direcciones** con una descripción corta y un boceto (HTML estático o captura). Andrés elige.
3. Implementa la elegida como componentes reutilizables en el tema hijo.
4. Captura después y compara. Revisa jerarquía: ¿se entiende qué vendemos y dónde pulsar en 3 segundos?
