# Sistema de diseño · DryIcePack — "Cadena de frío"

Precisión logística a −78,5 °C con el espectáculo del vapor. La web nunca está quieta: siempre hay algo vivo en pantalla
(vapor, rutas, reloj de corte, la caja que respira), pero con respeto: lo que se mueve ayuda a entender o a comprar,
y se apaga para quien lo necesita.

## Mundo del producto (de aquí sale todo)
- **Dominio:** sublimación (sólido → gas, sin líquido) · caja EPS de 40 mm · expedición MRW · etiqueta UN 1845 / Clase 9 ·
  albarán · corte de las 12:00 · peso facturable · pellet 3 mm / nugget 16 mm · niebla que **cae** (el CO₂ pesa más que el aire) · nave de Mataró.
- **Colores reales:** blanco escarcha, gris EPS granulado, azul marino de la marca, negro de las fotos de producto, blanco vapor, amarillo de señalización.
- **Firma:** la información clave se presenta como **etiqueta de expedición real** (UN 1845 · peso neto · −78,5 °C · fecha de entrega),
  y lo que aparece **se condensa del vapor**. La niebla siempre cae hacia abajo.
- **Rechazados:** vídeo de fondo + titular centrado + 2 botones → caja EPS 3D + etiqueta + reloj de corte en directo ·
  rejilla de tarjetas idénticas → composición variada por sector (una destacada + lista) · fila de cifras en cajas iguales → línea del día del pedido.

## Tokens (nombres del mundo, no de plantilla)
| Token | Valor | Uso |
|---|---|---|
| `--noche` | #0F2433 | Fondos oscuros, hero, pie |
| `--marino` | #1F3C55 | Marca: botones secundarios, titulares sobre claro |
| `--marino-hondo` | #16304A | Hover sobre marino |
| `--escarcha` | #F3F7FA | Fondo claro principal |
| `--eps` | #E6EAED | Superficies tipo caja EPS, separadores suaves |
| `--tinta` | #13202C | Texto principal sobre claro |
| `--acero` | #516273 | Texto secundario sobre claro (AA sobre escarcha) |
| `--hielo` | #B7D2E6 | Antetítulos y datos sobre oscuro |
| `--vapor` | #FFFFFF | Texto sobre oscuro, niebla |
| `--senal` | #FFCC25 | **Solo** la acción principal (comprar / pedir). ~10 % de la pantalla como máximo |
| `--senal-viva` | #FFD84D | Hover de la acción |
| `--alerta` | #C2410C | Avisos (texto sobre claro, AA) |

Profundidad: **capas de superficie + sombras suaves en claro, anillo de 1 px en oscuro**. Radio: 6 px controles, 10 px tarjetas, 999 px píldoras.

## Tipografía — Open Sans variable (peso 300–800 · anchura 75–100 %), autoalojada
- Escala 1,25 sobre 17 px: 13 · 17 · 21 · 26 · 33 · 41 · 52 (display 64+ con clamp).
- Titulares 800, tracking −0,02 em. Texto 500, interlineado 1,6. Antetítulo 800 mayúsculas tracking 0,14 em.
- **Etiquetas de expedición y datos:** Open Sans **condensada** (`font-stretch: 75%`) 700–800, mayúsculas, `tabular-nums`.
  Mismo tipo, otra voz: ficha técnica.

## Espaciado
Base 8 px: 8 · 16 · 24 · 32 · 48 · 64 · 96 · 128. Márgenes laterales 16 (móvil) · 28 (tablet) · 32 (escritorio). Ancho máximo 1200.

## Movimiento (sistema)
- **Siempre vivo, nunca en medio:** cada sección tiene un elemento ambiental (vapor, rutas, caja, reloj) que solo se anima
  cuando está en pantalla y con la página ya cargada.
- **Solo transform y opacity** (y `offset-distance` para recorridos). Nada de animar layout.
- **Curvas:** entrada `cubic-bezier(.23, 1, .32, 1)`; movimiento en pantalla `cubic-bezier(.77, 0, .175, 1)`.
- **Duraciones:** pulsación 120 ms · hover 180 ms · aparición 500–700 ms · ambiental 12–40 s.
- **Condensación** (aparecer): opacity 0 → 1 + translateY 16 px + un velo de vapor que se disuelve.
- **Sublimación** (desaparecer): opacity → 0 + translateY −8 px, más rápida que la entrada.
- **Transiciones entre páginas:** View Transitions nativas (la cabecera se queda, el contenido se condensa).
- **Scroll:** animaciones ligadas al scroll con `animation-timeline: view()` donde el navegador lo soporte (mejora progresiva).
- **Puntero fino:** inclinación 3D sutil de tarjetas y botones magnéticos; nunca en táctil.

## Respeto (límites adaptados)
- `prefers-reduced-motion`: todo quieto y visible; se mantienen cambios de opacidad.
- **Modo ligero automático** (≤ 4 núcleos, ≤ 4 GB, ahorro de datos o conexión lenta): sin WebGL, menos partículas, sin inclinación 3D.
- WebGL/3D pesado solo en escritorio capaz y después de cargar. Siempre hay alternativa (foto o CSS).
- Objetivo: Lighthouse móvil ≥ 90, CLS 0, accesibilidad ≥ 95.
