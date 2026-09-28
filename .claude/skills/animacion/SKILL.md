---
name: animacion
description: Animaciones e interacciones de Dryicepack — niebla, microinteracciones, scroll y transiciones con buen rendimiento y accesibilidad. Úsala al añadir cualquier movimiento a la web.
---

# Animación Dryicepack

## Filosofía
El movimiento cuenta la historia del producto: **el frío que se desliza y la niebla que cae**. Pocas animaciones, bien hechas, con propósito. Si una animación no ayuda a entender o a pulsar, sobra.

## Reglas técnicas (obligatorias)
- Anima solo `transform` y `opacity`. Nunca `width`, `height`, `top`, `left`, `margin` ni `box-shadow` animados.
- Prioridad de herramientas: **CSS puro** → Web Animations API → una librería ligera solo si está justificada (y pregunta antes de añadirla).
- `IntersectionObserver` para animaciones al hacer scroll; nada de escuchar `scroll` directamente.
- Nada de animación en el elemento LCP (el hero principal debe pintarse ya visible). Se pueden animar elementos secundarios alrededor.
- Duraciones: microinteracciones 150–250 ms; entradas de sección 400–600 ms; ambientales (niebla) lentas, 8–20 s en bucle.
- Curvas: `cubic-bezier(0.2, 0.8, 0.2, 1)` para entradas; nunca `linear` salvo en bucles ambientales.
- Sin saltos de diseño: reserva el espacio de todo lo que aparezca (CLS = 0).

## Accesibilidad
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```
- Ningún contenido depende de una animación para ser visible o entendible.
- Nada que parpadee más de 3 veces por segundo.

## Catálogo de firmas de marca
1. **Niebla del hero**: 2–3 capas SVG o PNG muy ligeras (< 30 KB en total) de vapor que se desplazan lentamente en horizontal con `transform: translateX` y opacidad variable. Pausada si la pestaña no está visible.
2. **Contador de temperatura**: la cifra "−78,5 °C" baja desde 20 °C al entrar en pantalla (una sola vez, ~1,2 s).
3. **Pellets que caen**: pequeños puntos SVG que caen y "subliman" (se desvanecen) al pasar sobre la sección de formatos.
4. **Botón de compra**: al pasar el ratón, ligera elevación (`translateY(-2px)`) y un velo de vapor sutil; al pulsar, escala 0,98.
5. **Pasos "cómo funciona"**: aparición escalonada (60–80 ms entre elementos).

## Verificación
- Graba o captura antes/después.
- Lighthouse móvil tras añadir animaciones: si el Rendimiento baja más de 3 puntos o aparece CLS, revierte o simplifica.
- Prueba con `prefers-reduced-motion` activado.
