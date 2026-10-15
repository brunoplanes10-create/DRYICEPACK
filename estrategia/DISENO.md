# Sistema visual de dryicepack.es · catálogo

15/10/2026 · Estado real del tema `dryicepack` tras el rediseño del 13–15/10 (plan: `estrategia/REDISENO-VISUAL.md`).
Sustituye a «Frío en calma» (30/09), que el propietario rechazó. Sirve para no repetir recursos al añadir páginas o secciones.

**Dirección C:** ficha técnica de un proveedor industrial serio (fotos reales, cotas, datos en mono, mucho aire) con un solo momento de espectáculo por pantalla: la niebla. Preferencias del propietario: siempre se mueve algo, nada de aspecto de plantilla o de IA, tipografía seria B2B.

---

## 1. Tokens (`assets/css/base.css`, `:root`)

| Grupo | Valores |
|---|---|
| Color dominante | `--c-noche #0B141C` · `--c-noche-2 #121F2B` · `--c-marino #1F3C55` · `--c-marino-2 #2A5070` |
| Soporte | `--c-papel #F6F8F9` · `--c-escarcha #EEF3F7` · `--c-blanco` · `--c-hielo #B8D0E8` · `--c-hielo-claro #DCE8F3` |
| Texto | `--c-tinta #0F1D29` · `--c-acero #52657A` (sobre claro) · `--c-niebla #9DB2C6` (sobre oscuro, 5,2:1 en marino) |
| Acento | `--c-accion #2BB3E9` **solo** en botones de compra · `--c-senal #B8741A` **solo** en avisos |
| Tipografía | `--f-display` Inter Tight 800 (titulares) · `--f-texto` Inter · `--f-mono` Geist Mono **solo para datos** (cifras, unidades, horas, cotas). Reservas de Arial con métricas (CLS 0). En catalán, `locl` desactivado: «l·l» normal |
| Escala | `--t-gigante` (máx. 3 por página) · `--t-hero` · `--t-h1` · `--t-h2` · `--t-h3` · `--t-entrada` · `--t-base` · `--t-peq` · `--t-dato` |
| Espacio | `--seccion` · `--seccion-densa` (datos, 3–5 rem) · `--seccion-aire` (frases y fotos, 8–14 rem) · `--solape` (3–5 rem) · `--ancho 1360px` · `--margen` |
| Forma | `--radio 4px` · `--radio-grande 8px` (máximo). Píldora 999px solo en botones; círculos solo en iconos y botones redondos |
| Sombras | `--sombra-1/2/3`, multicapa con color noche. Nada de pilas hechas a mano |
| Movimiento | `--d-corta .2s` (microinteracciones) · `--d-media .4s` · `--d-larga .6s` (máximo de una entrada) · `--escalon 70ms` · `--niebla-1/2/3` 18/24/30 s · `--e-salida` · `--e-suave` |

**Tonos de sección:** `.tono-oscuro` (noche) · `.tono-marino` · `.tono-papel` · `.tono-escarcha` · `.tono-blanco` · `.tono-hielo`. Cada uno define `--cortina`.
**Ritmo:** nunca el mismo tono dos secciones seguidas; como mucho dos claras seguidas; la sección antes del pie es noche (cierre) y el pie, marino. Densa y aire, alternando.

**Halloween** tiene su propia hoja (`assets/halloween/`): negro `#0B0B0E`, carbón, naranja `#FF6B1A` solo para lo que se pulsa y blanco vapor. Usa sus propias variables `--hw-*` (radio 8 px).

---

## 2. Piezas comunes (marcado y dónde están)

| Pieza | Marcado / función | Dónde |
|---|---|---|
| **Cabecera** | `header.php`. Fija y opaca, sin `backdrop-filter`; se esconde al bajar. Móvil: «Comprar» compacto, hamburguesa que pasa a X, panel noche con tira de miniaturas en «Usos» y «Comprar hielo seco» fijo con la fecha real | Todas |
| **Pie** | `footer.php`. Marino con filete hielo, enlaces en 2 columnas en móvil y −78,5 °C que cuenta (`.cifra-fija`) | Todas |
| **WhatsApp flotante** | `.flotante-wa`. Aparece tras el hero y se esconde con el menú, `[data-cierre]` o el pie. En móvil, solo el icono de 44 px; se acopla a la barra de compra (`.hay-barra-compra`) | Todas |
| **Botón** | `dipt_boton( $texto, $url, 'hielo' \| 'marino' \| 'blanco' )`. Cian = compra; marino = lo que no es compra. −2 px, `scale(.98)` y flecha que gira | Todas |
| **Enlace con flecha** | `dipt_enlace( $texto, $url )`. Subrayado que se dibuja con `scaleX` | Todas |
| **Título** | `dipt_titulo( $etiqueta, 'Línea 1\|<em>línea 2</em>', $clase, $id, 'lineas' \| 'cortina' \| 'revela' \| 'ninguna' )`. `lineas` solo en el H1 y un H2 por página | Todas |
| **Preguntas** | `dipt_faq( $lista, array( 'titulo', 'id', 'lado' => 'contacto' \| 'foto' \| 'nada', 'foto', 'pie' ) )` dentro de `<section class="seccion seccion--densa tono-…"><div class="envoltura">`. `details/summary`, barra de 2 px al abrir y FAQPage sin duplicar | Portada, Empresas, Hostelería, Transporte, Industria, Laboratorios, Eventos |
| **Cierre común** | `dipt_cierre( array( 'titulo', 'foto', 'boton', 'boton_url', 'secundario', 'corte_textos', 'seguridad' ) )` (`partes/cierre.php`). Noche, titular en `t-gigante`, foto a la derecha, fecha real con `data-corte` (reloj y «Quedan…» en su propia línea), niebla que sube y enlace a seguridad | Portada (caja-3mm), Aplicaciones (cajas-oscuro), Hostelería (caja-3mm), Industria (cenital-caja-nuggets), Laboratorios (nuggets-azul), Qué es (cajas-furgoneta), Seguridad (anadiendo-hielo), Guías (reparto-furgoneta), Zonas (cajas-furgoneta) |
| **Placa de cliente a caballo** | `dipt_placa_cliente( $c['cliente'], array( 'id', 'arriba', 'abajo', 'lado', 'grande' ) )` (`sectores.css`, solo en los 4 sectores B2B). Montada sobre la frontera entre dos secciones, con remaches | Hostelería (Sushitok), Transporte (Italpizza), Industria (Nissan, grande), Laboratorios (Semillas Fitó) |
| **Foto a sangre** | `.foto--sangre` + `.foto--sangre-der/-izq`; texto encima con `.foto__franja` (noche al 80 %, sin degradado) | Heros y secciones oscuras |
| **Lámina** | `.foto--lamina` + `.foto--4x3/4x5/3x4/1x1/16x9`, con `data-cortina` | Fondos claros |
| **Hoja sobre foto** | `.hoja` + `.hoja--sobre` / `.hoja--lado`. Una por página como mucho | Portada I7 (albarán), Laboratorios L4, Qué es Q2, Empresas E6, Contacto C2 |
| **Cota de plano** | `<span class="cota" style="--largo:12rem"><span class="cota__linea"></span><span class="cota__valor">40 mm</span></span>` (`.cota--v` vertical) | Portada I6, Producto P2, Aplicaciones A3, Qué es Q4, Hostelería, Transporte (dibujos) |
| **Regla de rango** | `.regla-rango > input[type=range] + output.regla-rango__cifra` + `.regla-rango__marcas` | Envíos V3, Empresas E4 |
| **Cifra que cuenta** | `<span class="cifra-fija"><span class="cifra-fija__reserva">−78,5</span><span data-cuenta="-78.5" data-desde="20" data-decimales="1">−78,5</span></span>`. La caja que cuenta ocupa el hueco reservado (CLS 0) | Pie, Portada I6, Qué es Q1, Industria N1/N3, Empresas E1, Aplicaciones A1, Envíos (reloj) |
| **Fecha real de entrega** | `[data-corte]` con `data-t-antes/quedan/despues` y `dipt_primera_entrega()` en el servidor | Cierres, menú móvil, Portada, Envíos |

---

## 3. Recursos de movimiento

Reglas: solo `transform` y `opacity`; un IntersectionObserver por tipo (`movimiento.js`); nada animado en el LCP; con `.quieto` o `prefers-reduced-motion` todo queda visible y en su estado final; nada que dependa solo del hover (`data-activa` es su equivalente en móvil).

| Recurso | Cómo | Duración |
|---|---|---|
| Entrada | `data-revela` (`izq`, `der`, `zoom`; escalón `--i`) → `.visto` | 0,6 s |
| Título línea a línea | `data-lineas` (vía `dipt_titulo`) | 0,6 s + 70 ms |
| Cortina | `data-cortina` (foto, hacia arriba) · `data-cortina="texto"` | 0,6 s |
| Activo al centro | `data-activa` → `.activo`; grupo `data-activa-grupo` (uno activo, `--activo`, evento `dip:activa`) | — |
| Paralaje | `data-paralaje="0.08"` (JS) o `animation-timeline: view()` (CSS, Portada I5 e I10) | continuo |
| Contador | `data-cuenta` + `data-desde` | 1,8–2,2 s |
| Niebla horizontal | `<div class="niebla" aria-hidden="true"><i></i><i></i><i></i></div>` (hasta 4 capas) | 18/24/30 s |
| Niebla que sube | `.niebla.niebla--sube` (solo en el cierre común) | 24/30 s |
| Bucle propio | contenedor con `data-pausa` (se para fuera de pantalla) | 15–30 s si es niebla |

**Firma de movimiento de cada página** (una por página; no repetir en una página hermana):

| Página | Firma ambiental | Pieza central y movimiento propio |
|---|---|---|
| Portada | Niebla en 4 capas en el hero + cinta de ficha (60 s) | Usos en escenario fijo (boceto A, 15/10) con respiración de 24–26 s; termómetro del hero (1,2 s, fijado por el plan); albarán con semanas que se marcan; rombos que giran en móvil |
| Producto | Niebla en el borde de la caja (P2) | Galería fija, marcadores 01–03 con líneas, comparador con gesto 50 → 35 → 50 %, talón del billete que se separa |
| Envíos | Niebla del hero | Reloj hasta el siguiente corte (fundido de 150 ms), vías del calendario, cajas que caen (240 ms), mapa estático de cobertura |
| Empresas | Niebla tras el móvil (E2) | Móvil fijo cuya pantalla cambia con `data-activa`, palé 5 × 3 con cajas que caen (18 ms de escalón), teléfono por grupos |
| Contacto | — (página corta) | Hojas de canal que suben al cruzar el centro; formulario como hoja; furgoneta con paralaje |
| Aplicaciones | Niebla bajo las dos láminas del hero | Paralaje contrario de las láminas, cota 3/16 mm que compara |
| Hostelería | Niebla que rebosa del doble recipiente (bucle de 15 s) | Cada paso enciende su pieza del dibujo; díptico 3 → 16 mm |
| Transporte | Chevrons de frío que bajan (2,4 s) | Regla «3 h → 24 h» que se llena; hoja de ruta |
| Industria | «+» de la retícula que se dibujan | Cinta métrica 3/150/250 con contador; expediente con checks escalonados |
| Laboratorios | Órbita del círculo del hero (40 s) | Pines numerados enlazados con la lista; hojas de servicio en diagonal |
| Eventos | Niebla en 3 capas del hero (sin zoom) | Escenario fijo con fundido de 3 fotos |
| Qué es | — (vapor en la foto) | Termómetro que baja, regla de laboratorio con piezas que caen, pellet que baja la escalera |
| Seguridad | Balanceo único de la etiqueta de clase 9 | Checks y aspas escalonados, FDS que se endereza |
| Guías / guía | — | Miniaturas con cortina; índice que marca el apartado activo |
| Zonas / zona | Barrido de radar (20 s) | Puntos por distancia; furgoneta que viaja de Mataró a la zona |
| 404 | Cifra que sublima (7 s, vaivén) | — |
| Halloween | Niebla propia (18/24/30 s) y vídeos | Escala de 3 paradas con indicador que se desliza (200 ms) |

---

## 4. Mapa de tonos real (15/10/2026, medido en 1440 px)

| Página | Secuencia (→ pie marino) |
|---|---|
| Portada | noche (hero + cinta) · papel · marino · blanco · noche · escarcha · papel · noche · papel · blanco · noche (cierre) |
| Producto | papel · noche · papel · marino · escarcha · blanco |
| Empresas | papel · noche · papel · marino · blanco · escarcha · papel + franja noche |
| Envíos | noche · papel · escarcha · blanco · marino · blanco (panel noche) |
| Contacto | papel · noche · papel |
| Aplicaciones | noche · papel · marino · blanco · noche (cierre) |
| Hostelería | noche · papel · noche · placa · papel · escarcha · marino · noche (cierre) |
| Transporte | papel · noche · papel · placa · blanco · marino · noche (final propio) |
| Industria | noche · escarcha · marino · placa · papel · noche · blanco · noche (cierre) |
| Laboratorios | papel · noche · papel · placa · marino · blanco · noche (cierre) |
| Eventos | noche · papel · noche · banda Halloween · blanco · escarcha · noche |
| Qué es | papel · blanco · papel · noche · escarcha · blanco · noche (cierre) |
| Seguridad | papel · blanco · hielo / noche · papel · escarcha · blanco · noche (cierre) |
| Guías | papel · blanco · noche (cierre) |
| Zonas | noche · papel · noche (cierre) |
| Zona | papel · noche · papel · blanco · escarcha · noche |

Excepciones aceptadas por el plan: Producto, Envíos y Contacto terminan en claro (no llevan cierre común); Transporte usa su propio final.

---

## 5. Excepciones conocidas

- `dipt_faq()` no se usa en Producto P6 (burbujas de chat), Qué es Q6 y Seguridad S6 (preguntas como titulares, sin acordeón, a petición del plan) ni en Zona Z5 (acordeón propio «+» → «−»).
- Degradados que quedan: viñetas de legibilidad de los heros de la Portada y Hostelería, tramas de rayas (sábado del calendario, etiqueta de clase 9, factura), máscaras del billete y de la esquina cóncava de la portada, los de Halloween (anteriores al rediseño) y las volutas de la niebla que rebosa en Hostelería (`radial-gradient`, nuevas). No añadir más.
- Duraciones fuera de la regla, decididas: termómetro del hero de la portada 1,2 s; línea de progreso de los pasos de Halloween 0,7 s (sigue al scroll, no es una entrada); spinner del producto 0,75 s.
- Rótulos en mono y mayúsculas permitidos solo como etiqueta de un dato (reloj, kilos, código postal, «Por semana / Al mes»); nunca como antetítulo de sección.

---

## 6. Prohibido (rechazado por el propietario)

Puntitos en una etiqueta y puntos que laten · pastillas con icono y chips con borde · rombos como viñeta · antetítulos en mono mayúsculas · tarjetas simétricas con icono arriba · foto que sigue al cursor (`data-sigue`, código borrado el 15/10) · tarjeta que se inclina (`data-inclina`, borrado el 15/10) · barrido de brillo del botón · ojal del marbete · `backdrop-filter` · degradados nuevos (y nunca morado-azul) · radios de más de 8 px salvo píldoras · fotos de stock · iconos de librería.

## 7. Al añadir una sección nueva

1. Elegir un tono que respete el ritmo de la página (tabla 4) y `densa` o `aire`.
2. Elegir un recurso de movimiento que no esté en la sección de al lado ni en la página hermana (tabla 3).
3. Usar las piezas comunes (tabla 2) antes de dibujar una propia; si hace falta algo genérico, va a `base.css` / `componentes.php`, no a la hoja de la página.
4. Comprobar con `.quieto`, a 390 px sin hover y con `node .tmp/rediseno/final/buscar-codigo.mjs` (transiciones, duraciones, radios, degradados, `backdrop-filter`).
