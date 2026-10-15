# Rediseño visual de toda la web: plan

13/10/2026 · director de arte jefe + ingeniero de rendimiento.
Sale de las cuatro auditorías del 13/10 (portada, compra, sectores, contenido). Las de portada y compra se hicieron leyendo el código, porque el servidor de prueba estuvo caído; hay que confirmarlas con capturas antes de tocar nada (ver punto 4).
Manda la dirección C: Inter Tight + Inter, Geist Mono solo para datos, marino #1F3C55, fondos oscuros, botones en píldora y cian #2BB3E9 solo en compra. La paleta del skill de diseño está anticuada.

---

## 1. Dirección de arte y sistema común

**Dirección de arte:** ficha técnica de un proveedor industrial serio (fotos reales del producto, cotas, datos en mono, mucho aire) con un único momento de espectáculo por pantalla: la niebla. Sobria para compras y logística, viva para quien hace scroll.

### 1.1 Recursos propios y dónde se usan

| Recurso | Regla | Dónde |
|---|---|---|
| **Paleta de 3** | Dominante noche/marino (fondos, marca) · soporte papel/escarcha/blanco · acento cian **solo** en botones de compra. `--c-senal` solo en avisos. Fuera el crema y el marrón como cuarto color (salvo objetos: carta de bar, etiqueta). | Toda la web |
| **Foto a sangre** (`.foto--sangre`) | Opacidad 1, sin radio, rompe la envoltura por un lado. Si lleva texto encima: franja sólida noche al 80 % solo bajo el texto. **Sin degradados.** | Heros y secciones de foto en oscuro |
| **Lámina** (`.foto--lamina`) | Recorte rectangular, `--radio`, borde 1 px tinta, `--sombra-2`, entrada con cortina. | Fondos claros |
| **Hoja sobre foto** | Hoja blanca (albarán, ficha, formulario) que se monta 4–6 rem sobre el borde de una foto. Nunca en dos secciones seguidas ni dos veces en la misma página. | Portada I7, Contacto C2, Laboratorios L4, Qué es Q2, Empresas E6 |
| **Troquel con marco desplazado** | Solo en un sitio. | Hero de Empresas |
| **Foto en círculo** | Solo en un sitio. | Hero de Laboratorios |
| **Rótulos en Geist Mono** | Solo datos de verdad: cifras, unidades, horas, cotas, coordenadas. **Nunca** como antetítulo en mayúsculas. | Toda la web |
| **Cotas de plano** | Línea fina con topes y valor en mono que se dibuja con `scaleX`. Estándar: el corte técnico de Laboratorios L2. | Portada I6, Producto P2, Hostelería H2, Transporte T2, Qué es Q4 |
| **Cifra −78,5 °C** | Una vez por página (con el contador que baja de 20 °C) + la firma del pie. | Portada I6, Qué es Q1, Industria N1, Empresas E1, Aplicaciones A1, Halloween paso 5 |
| **Niebla** | Bucles de 18, 24 y 30 s. Una firma ambiental por página, nunca sobre el LCP, pausada fuera de pantalla y con `.quieto`. Horizontal en heros, **que sube** en los cierres. | Ver cada página |
| **Ritmo de fondos** | Nunca el mismo tono dos secciones seguidas; máximo dos claros seguidos; la sección antes del pie siempre es noche (cierre) y el pie, marino. | Mapa de tonos en cada página |
| **Ritmo de aire** | `.seccion--densa` para datos y calculadoras, `.seccion--aire` para frases y fotos grandes, alternando. | Toda la web |
| **Títulos** | `--t-gigante` en un máximo de 3 momentos por página. La fórmula «frase \| *segunda parte en otro color*» en la mitad de los H2 como mucho. Entrada línea a línea solo en el H1 y un H2 por página. | Toda la web |

**Fuera de la web entera** (lo que el propietario rechazó): rombos de 7 px como viñeta (`.cinta__elem::after`, `.i-cajetin__uso::before`), chips con borde (`.i-recogida__cerca li`, `.z-usos__lista`), pastillas con icono (`.gu-cabeza__meta`, `.lg-cabeza__meta`), puntos que laten (`.c-estado::before/::after`), ojal del marbete (`.e-marbete::before`), puntitos de Halloween (`.hw-chips li::before`, `.hw-subrayado`), barrido de brillo del botón (`.boton::before`), tarjeta fintech con inclinación (`data-inclina`), foto que sigue al cursor (`data-sigue`), `backdrop-filter` de la cabecera.

### 1.2 Cambios transversales (lote 0)

| Qué | Cambio | Archivo |
|---|---|---|
| Radios | `--radio: 4px`, `--radio-grande: 8px` (máximo del skill), píldora solo en botones. Fotos a sangre sin radio. | base.css `:root` |
| Sombras | Tokens `--sombra-1/2/3` multicapa con color noche. Sustituyen las pilas hechas a mano de cada hoja. | base.css |
| Duraciones | `--d-larga: .6s`; `[data-lineas]` 0,6 s con escalón de 70 ms; `[data-cortina]` 0,6 s. | base.css |
| Niebla | 18/24/30 s; pausa con IntersectionObserver fuera de pantalla. | base.css, movimiento.js |
| Espaciado | `.seccion--densa: clamp(3rem, 5vw, 5rem)` y `.seccion--aire: clamp(8rem, 15vw, 14rem)`. | base.css |
| Fotos | Clases `.foto--sangre` y `.foto--lamina`. Recortar las marcas de agua horneadas (uso-hosteleria, uso-eventos, uso-agricultura, uso-vino, uso-laboratorio-entrega). | base.css, herramientas/preparar-imagenes.mjs |
| Botones | Sin barrido. Elevación −2 px en 200 ms, `scale(.98)` al pulsar y giro de la flecha. | base.css |
| Hover → móvil | Observador común `data-activa` («activo al centro», rootMargin −45 % arriba y abajo) que pone `.activo`, más `.visto` al entrar. Todo efecto de hover tiene su equivalente así. Quitar `data-inclina` y `data-sigue` cuando se publiquen los usos nuevos. | movimiento.js, base.css |
| Títulos | `dipt_titulo()` admite `entrada` = `lineas` · `cortina` · `revela` · `ninguna`. La regla `h1 .tramo, h2 .tramo` pasa a base.css (hoy está copiada en 5 hojas). | inc/componentes.php, base.css |
| Deslizador | `.regla-rango`: pista de 2 px, marcas en mono, tirador cuadrado con la cifra. Para Envíos V3 y Empresas E4. | base.css |
| Preguntas | `dipt_faq()`: título fijo a la izquierda y acordeón `details/summary` (opacidad + 8 px en 250 ms; el texto sigue en el DOM para el FAQPage). La columna fija admite foto o contacto. | inc/componentes.php, base.css |
| Cierre común | `partes/cierre.php` (nuevo): fondo noche plano, foto distinta en cada página abajo a la derecha, niebla que sube, titular, fecha real de entrega con `data-corte`, botón cian, enlace secundario y enlace a seguridad. | partes/cierre.php, inc/componentes.php, base.css |
| Placa de cliente | «Placa a caballo»: sin sección propia, montada sobre la frontera entre dos secciones (alto reservado, CLS 0), logo a 2×, remaches que aparecen uno a uno. Una sola hoja `paginas/sectores.css`, encolada en las 4 páginas B2B. | assets/css/paginas/sectores.css (nuevo), inc/assets.php |
| Cabecera | Sólida con papel opaco al 100 %, sin `backdrop-filter`. Móvil: `.cabecera__herramientas { margin-left:auto }`, botón **«Comprar»** compacto (40 px, sin flecha) junto al carrito, hamburguesa que pasa a X (200 ms). Menú móvil: tira de miniaturas en «Usos» (scroll-snap, sin hover) y «Comprar» fijo al pie del panel con la fecha de `data-corte`. Radio del submenú al token. | header.php, base.css |
| Pie | Fondo marino (hielo-claro sobre marino: 9,19:1). Filete hielo de 1 px arriba. Cifra −78,5 °C como texto `aria-hidden` (no `content:attr()`) con el contador al entrar, recorte a 0,8 em en móvil para que se vea la coma. Enlaces en 2 columnas en móvil. | footer.php, base.css |
| WhatsApp flotante | Aparece después del hero. En móvil, solo icono de 44 px. Se esconde con el menú abierto y cuando el cierre o el pie están en pantalla; con barra de compra, se acopla a ella. | base.css, movimiento.js |

---

## 2. Página por página

Solo lo que cambia. «Lote 0» = viene resuelto por el punto 1.2. Las propuestas no inventan datos: todo sale de `contenido/es/*.php`, de `dipt_precios_recogida()`, `dipt_primera_entrega()` o de CLAUDE.md.

### 2.0 Reparto de fotos

Regla: ninguna foto se repite dentro de una página ni como hero de dos páginas, y las páginas hermanas (los cinco sectores) no comparten fotos secundarias.

| Página | Fotos |
|---|---|
| Portada | caja-16mm-niebla (hero) · cenital-3mm/16mm (I3) · las 5 uso-* (I5) · cenital-caja-nuggets (I6) · almacen-caja* (I7) · cajas-furgoneta (I8) · anadiendo-hielo (I10) · caja-3mm (I11) |
| Producto | caja-3mm / caja-16mm-niebla según formato (P1) · cenital-3mm/16mm (P1 y P3) · caja-3mm-lateral / caja-16mm-lateral (P2) |
| Envíos | reparto-furgoneta (V1) · cajas-oscuro (V4) |
| Empresas | almacen-caja* (E1, sigue a la portada I7 a propósito) · mosaico actual (E5) · uso-transporte (E6) |
| Contacto | anadiendo-hielo (C2) · reparto-furgoneta (C3) |
| Aplicaciones | caja-3mm-lateral + nuggets-azul (A1) · 5 uso-* (A2) · cenital-3mm/16mm (A3) · uso-vino, uso-agricultura, uso-docencia, uso-laboratorio-pinzas, uso-industria-pala (A4) · cajas-oscuro (cierre) |
| Hostelería | uso-hosteleria · cenital-3mm/16mm (H3) · reparto-furgoneta (H4) · caja-3mm (cierre) |
| Transporte | uso-transporte · caja-16mm-lateral (T2) · cajas-furgoneta (T6) |
| Industria | uso-industria · uso-industria-pala* (N2) · cajas-oscuro (N3) · cenital-caja-nuggets (cierre) |
| Laboratorios | uso-laboratorio · uso-laboratorio-entrega (L4) · nuggets-azul (cierre) |
| Eventos | uso-eventos · caja-3mm-lateral, caja-16mm-niebla, almacen-caja* (V2) · uso-docencia, uso-laboratorio-pinzas, anadiendo-hielo (V3) |
| Qué es | nuggets-ia* o caja-16mm-lateral (Q1) · cenital-3mm (Q2) · pellets-ia/nuggets-ia* o recortes de cenital-* (Q4) · caja-3mm-lateral (Q6) · cajas-furgoneta (cierre) |
| Seguridad | uso-laboratorio-pinzas (S3) · anadiendo-hielo (cierre) |
| Guías | uso-eventos (destacada) · cajas-oscuro · uso-laboratorio-entrega |
| Zonas / zona | cajas-furgoneta (cierre de /zonas/) · 5 uso-* + almacen-caja* (Z4) |

\* [PENDIENTE: Bruno confirma si son fotos reales: almacen-caja, uso-industria-pala, pellets-ia, nuggets-ia y las uso-* con marca de agua]. Si no lo son, se usa la alternativa indicada o la sección va sin foto: nunca stock.

### 2.1 Portada · inicio.php, inicio.css, contenido/es/inicio.php

Tonos: hero noche (con la cinta dentro) · I3 papel · I4 marino · I5 blanco *aire* · I5b noche *densa* · I6 escarcha · I7 papel · I8 noche · I9 papel · I10 blanco · I11 noche · pie marino.
Movimiento, uno por sección: niebla horizontal · panel · rutas · escenario sticky · filetes que se dibujan · contador + cotas · calendario que se marca · cortina + filas · rombos que giran · barra scaleY · niebla que sube.
No tocar: I4 (mapa y horario).

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| I1 Hero | 4 bloques de texto (~50 palabras); niebla casi quieta. | Una frase de texto [a validar] + la línea del corte con cuenta atrás en mono. Cuarta capa de niebla (0,08) en el borde de la foto, nunca sobre ella (LCP). Termómetro en 1,2 s. | media |
| I2 Cinta | Ticker de plantilla con rombos; repite el hero; banda oscura pegada al hero. | Línea de ficha técnica montada en el borde inferior del hero: etiqueta + valor en mono, separador «/». Sin lo que ya dice el hero ni −78,5. 60 s, se para con foco y `.quieto`; en móvil, scroll-snap sin autoplay. | baja |
| I3 Elegir | En 390 el precio queda a dos pantallas; precio por pack a 0,68 rem; radios blandos; texto solo con hover. | Móvil: paneles 50/50 de 200 px y regla + total en la primera pantalla de la sección. Precio a 0,75 rem sin interletraje negativo (si no cabe, 3 + 2). Radios a los tokens. | media |
| I5 Usos | Sosa; foto que sigue al cursor. | **Hecho con el boceto A «Escenario fijo», elegido por el propietario el 15/10/2026** (antes se había montado el B). Nota original: lo rehace otro equipo (`.tmp/rediseno/bocetos/usos-b-hoja-fotos.png`). Requisito: escenario con foto sticky que cambia con el scroll (`data-activa`), no con el ratón; número gigante en mono superpuesto a la foto; en 390, foto 4:3 con el nombre solapando su borde. Sin filas con filete (son de I4). | alta |
| I5b Clientes | 4 casillas iguales y altas casi vacías; rombos. | Banda baja y densa (~220 px): título a t-h3 a la izquierda y 4 logos con su uso, separados por filetes verticales que crecen con `scaleY`. Se queda el vapor que se aparta. 2 × 2 en 390. | media |
| I6 Ficha | Centrada y simétrica; foto en círculo; la cifra repite el H2. | Cifra −78,5 a t-gigante sangrando por la izquierda; encima, cenital-caja-nuggets 3:4 en lámina tapando su base; 4 cotas que salen de puntos de la foto. 390: marcadores 01–04 sobre la foto y leyenda debajo. H2 sin la cifra [a validar]. | alta |
| I7 Empresas | Tarjeta fintech con degradado, copo y checks; inclinación solo con ratón; sin foto. | almacen-caja* 4:5 a sangre por la derecha y, encima, un **albarán** de pedido programado (filas en mono con los datos actuales) con una tira de 4 semanas que se marca casilla a casilla. CTA «Abrir mi cuenta» en marino (no es compra). Nunca «sin permanencia». | alta |
| I8 Recogida | Muro de 70 palabras; chips; furgoneta que contradice el mensaje; cortina de 1,4 s. | Ficha en dos zonas: frase + **tarifa de mostrador** (tabla de kilos y precio con IVA de `dipt_precios_recogida()`, precio en Inter Tight 800) y «Solo efectivo, importe exacto». Zonas como texto con «/». Foto cajas-furgoneta a sangre. Tono noche y la tarifa directamente sobre el fondo, sin hoja blanca (la hoja sobre foto ya está en I7). | media |
| I9 Seguridad | Los rombos solo reaccionan al hover. | Solo móvil: cada rombo gira de 45° a 0° al entrar (400 ms, escalón 60 ms). Composición intacta. | baja |
| I10 Preguntas | Columna izquierda vacía. | anadiendo-hielo 4:3 en lámina (paralaje 0,06) y «¿Otra pregunta?» con teléfono y WhatsApp debajo del título. Barra marino de 2 px al abrir. | baja |
| I11 Final | Degradado radial; pegado al pie oscuro; sin objeto. | Pasa a ser el cierre común con caja-3mm y niebla que sube. Titular a la izquierda en t-gigante montado sobre la foto. | media |

### 2.2 Producto · producto.php, producto.css, paginas/producto.js

Tonos: P1 papel · P2 noche · P3 papel · P4 marino · P5 escarcha · P6 blanco · pie.

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| P1 Configurador | Hueco de 500 px bajo la galería; en 390 el precio llega a 1700 px; sin CTA en la primera pantalla; casillas de tienda genérica. | Escritorio: galería sticky (foto 4:5 del formato elegido + dos miniaturas que se solapan 24 px) y cifra «3 mm / 16 mm» en mono gigante sobresaliendo de la foto; fundido de 400 ms al cambiar. Móvil: H1 primero y una foto 4:3 en carrusel scroll-snap «1/3». Kilos como regleta continua con filetes. | alta |
| P2 La caja | Caja isométrica de clip-art. | Foto caja-3mm-lateral / caja-16mm-lateral a sangre con marcadores cuadrados 01–03 y líneas que se dibujan; cota «40 mm» en la pared; niebla al 30 % solo en el borde. | alta |
| P3 Comparador | Rótulos translúcidos genéricos; tirador de plugin. | Rótulos fuera de la foto o en franja mono; tirador de barra; al entrar, la línea va del 50 % al 35 % y vuelve una vez (600 ms). | baja |
| P4 Cuánto | Escalones pálidos; racha de claros. | Sección en marino; cifras {pk}/10/15–20 kg a gran tamaño sobre los escalones; barra de proporción (`scaleX`) que ya existe. Sin fotos (el presupuesto de fotos de la página está en P1–P3). | media |
| P5 Billete | Bien. | Solo: el talón se separa 6 px y vuelve al entrar (500 ms). | baja |
| P6 Preguntas | Muro de burbujas abiertas (~2400 px); columna izquierda vacía. | Burbujas-botón con `details` (la primera abierta). Columna izquierda: «¿Otra pregunta?» con teléfonos y cuenta de empresa en grande, sin foto. | media |
| Barra fija móvil | Aparece tarde; WhatsApp encima. | Visible al salir el H1, oculta mientras `.p-precio` está en pantalla. WhatsApp acoplado (lote 0). | media |

### 2.3 Checkout · tienda.css, plugin/dryicepack-tienda/assets/checkout.css (solo CSS)

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| Aviso de efectivo (recogida) | El mensaje clave va en letra pequeña gris; sin estilo en la página de gracias. | Bloque de aviso (`.dip-aviso`: filete de 3 px en señal), importe en mono a 1,4 rem, «importe exacto» en negrita; entra una vez (220 ms). Sobre `.dip-pago-efectivo` y la `.payment_box` del efectivo. Texto sin cambios. | alta |
| Pasos | Tres tarjetas idénticas de 28 px de radio. | Sin tarjetas: regla de 2 px y número 01–03 en mono grande; paso activo en marino; el número pasa a check cuando el paso es válido (200 ms). Móvil: resumen como barra plegable «Total … · Ver detalle». | media |
| Resumen | Sin foto del producto. | Miniatura de 72 px del formato (`dipt_foto`) junto al nombre; fundido de 150 ms en el total al cambiar. Toca `inc/woocommerce.php` (presentación, sin lógica). | media |
| Transiciones | checkout.css anima colores. | Solo `transform`, también en el plugin. | baja |
| Cupón y «añadido al carrito» | Ruido. | Es un ajuste de WooCommerce: [PENDIENTE: preguntar antes]. | — |

No tocar: métodos de entrega, calendario, botón Pagar.

### 2.4 Envíos · envios.php, envios.css, paginas/envios.js, partes/mapa-peninsula.php

Tonos: V1 noche · V2 papel · V4 (sube aquí) escarcha · V3 blanco *densa* · V5 marino · V6 blanco con panel noche · pie.

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| V1 Hero | Fuera de horario enseña 00:00:00 apagado; degradado genérico. | Pasado el corte, cuenta hacia el siguiente corte válido con su fecha (`dipt_primera_entrega()`) [texto a validar]. reparto-furgoneta a sangre por la derecha con niebla a 18–26 s. Los dígitos cambian con fundido de 150 ms. | alta |
| V4 Recogida | Repite el troquel de Empresas; foto con aspecto generado; layout previsible. Se mueve entre V2 y V3. | cajas-oscuro a sangre (60 %) con la dirección en mono y una **fila de precios** de recogida (kilos arriba, precio en Inter Tight 800 debajo, filetes que se dibujan) + «Efectivo · importe exacto». Distinta de la tabla de la portada. | media |
| V5 Cobertura | Texto para una pregunta geográfica. | Mapa propio en versión estática (Mataró, islas tramadas «No enviamos», ciudades que aparecen por distancia) + campo de CP con el endpoint y los textos de la ficha. | media |
| V6 Dudas | En 390 la respuesta queda fuera de pantalla; título pequeño. | Móvil: acordeón. Escritorio: pestañas con panel noche. Título a t-h2. | media |
| V2 Semana | Días a 0,6 rem en 390. | Iniciales a 0,75 rem mínimo; cada fila dibuja su vía al entrar. | baja |
| V3 Calculadora | Deslizador nativo; cajas diminutas. | `.regla-rango` (lote 0); cajas al doble que caen al añadirse (240 ms). | baja |

### 2.5 Empresas · empresas.php, empresas.css, paginas/empresas.js

Tonos: E1 papel · E2 noche · E3 papel *aire* · E4 marino *densa* · E5 blanco · E6 escarcha · E7 papel + franja noche · pie.
No tocar: E3 (solo la línea a 600 ms).

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| E4 Volumen | Pila de cuadrados que parece un esqueleto de carga. | Palé 5 × 3 con el icono propio de caja EPS; cajas llenas que caen (240 ms, escalón 18 ms) y vacías en contorno discontinuo. `.regla-rango` con marcas en 150 y 250 kg. | alta |
| E2 Cómo piden | Tres columnas iguales con objeto arriba. | Un móvil grande sticky y tres pasos a la derecha: la pantalla cambia (chat → «Sus pedidos» → email) con `data-activa`. Fuera los tres puntos. Móvil: carrusel al 85 % «1/3». Niebla: la firma de esta página. | media |
| E6 Alta | En 390 la frase con huecos no se rellena con el pulgar. | Móvil: trozos de frase como etiquetas de campos apilados y contador «3/8». Escritorio: uso-transporte que sobresale 4 rem de la hoja. | media |
| E7 Preguntas | Texto plano; el teléfono se pierde. | `dipt_faq()` y franja con el teléfono gigante en blanco por grupos (936 / 73 / 76 / 41, 80 ms). | media |
| E1 Hero | Foto dudosa. | almacen-caja* o alternativa; la etiqueta −78,5 cuenta una vez. | baja |
| E5 Mosaico | Ojal (lote 0); fotos al 72 %; degradado. | Fotos al 100 % con franja sólida bajo el texto; marbete rectangular. | baja |

### 2.6 Contacto · contacto.php, contacto.css

Tonos: C1 papel · C2 noche · C3 papel · pie.

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| C2 Formulario | Columna izquierda vacía; todo blanco. | anadiendo-hielo a sangre por la izquierda con cortina; hoja del formulario solapada 5 rem; línea en mono «Uso · kg · código postal → precio y fecha». Móvil: franja de 200 px y hoja montada 3 rem. | alta |
| C1 Canales | Punto que late; hoja solo con hover. | Estado como línea en mono con la hora real [texto a validar]; la hoja sube también con `:active` y al entrar. | media |
| C3 La nave | 50/50 previsible; oscuro pegado al pie. | Papel; dirección a t-h1 montada sobre reparto-furgoneta (paralaje 0,08); ficha en mono de 2 columnas. | media |

### 2.7 Aplicaciones · aplicaciones.php, aplicaciones.css, paginas/aplicaciones.js, contenido/es/aplicaciones.php

Tonos: A1 noche · A2 papel · A3 marino · A4 blanco · cierre noche · pie.

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| A1 Hero | Collage de tablero de inspiración que repite las fotos del mosaico; sin CTA. | H1 a la izquierda; dos láminas superpuestas (caja-3mm-lateral «niebla», nuggets-azul «frío · −78,5 °C») con paralaje contrario y niebla de 25 s por debajo. CTA «Elegir formato» que baja a A3. | alta |
| Cierre | No existe: ni CTA ni seguridad. | Cierre común (cajas-oscuro) [titular a validar]. | alta |
| A2 Mosaico | Degradado y flecha en círculo en las 5 piezas. | Franja sólida en el 34 % inferior, formato habitual en mono, entrada con cortina escalonada. 390: hostelería a lo ancho y el resto 2 × 2. | media |
| A3 Selector | Sin foto. | cenital-3mm/16mm que se sale por la derecha, ficha solapada y cota que compara 3 y 16 mm (`scaleX`). | media |
| A4 Otros usos | Cinta infinita ilegible (mismo recurso que la portada). | Hoja de contactos: 5 fotos de alturas distintas con numeración en mono; nada en bucle. 390: carril scroll-snap. | media |

### 2.8 Los cinco sectores: que no sean cinco copias

Los heros ya son distintos y se quedan: foto a sangre (hostelería), título partido (transporte), cifra en contorno (industria), círculo con órbita (laboratorios), foto centrada (eventos). Comparten solo tres piezas comunes de utilidad: placa de cliente a caballo, `dipt_faq()` y el cierre. Todo lo demás es propio:

| Sector | Firma ambiental (una) | Pieza central | Placa a caballo entre |
|---|---|---|---|
| Hostelería | niebla que rebosa (H2) | díptico 7/5 con cifra «3 → 16 mm» | H3 y H4 |
| Transporte | frío que baja en chevrons (T2) | planificador (ya está) | T3 y T4 |
| Industria | «+» de la retícula que se dibujan | cinta métrica de kilos | N3 y N4 (versión grande) |
| Laboratorios | CO₂ por la junta (L2) + órbita | corte técnico (ya está) | L3 y L4 |
| Eventos | niebla en capas del hero (sin zoom) | escenario sticky de 3 pasos | — (no hay placa) |

Cada hoja de sector pierde su copia del CSS de la placa y de `.tramo` (pasan a sectores.css y base.css).

**Hostelería** · hosteleria.php, sector-hosteleria.css. No tocar: H1, H5.

| Sección | Propuesta | Prio |
|---|---|---|
| H3b Placa | Placa a caballo, logo de Sushitok a 2×. | alta |
| H2 Doble recipiente | Redibujar con el lenguaje de L2 (corte en línea hielo, panel rectangular, cotas en mono); cada paso de la lista enciende su pieza con `data-activa`. | media |
| H3 Díptico | 7/5 asimétrico, fotos al 100 %, la derecha 80 px más abajo, cifra en contorno sobre la junta, franjas sólidas. | media |
| H4 Fin de semana | reparto-furgoneta al 58 % con paralaje y panel noche solapado con los 4 días en Inter Tight 800. | media |
| H6 + cierre | `dipt_faq()` + cierre común (caja-3mm). | baja |

**Transporte** · transporte.php, sector-transporte.css. No tocar: T1, T4.

| Sección | Propuesta | Prio |
|---|---|---|
| T3b Placa | Placa a caballo (Italpizza). | alta |
| T2 Caja en sección | Redibujo al estilo L2 con trama EPS y cotas; chevrons del frío en bucle de 2,4 s; lámina pequeña caja-16mm-lateral. | media |
| T6 Final | Pasa a noche: texto a la izquierda, cajas-furgoneta a sangre a la derecha, hoja de ruta solapada 60 px. Es su cierre (no se añade el común). | media |
| T3 Reglas | Título sticky y escalera a la derecha; regla «3 h → 24 h» que se llena. | baja |
| T5 | `dipt_faq()`. | baja |

**Industria** · industria.php, sector-industria.css. No tocar: N5 (solo pasa a noche a todo el ancho).

| Sección | Propuesta | Prio |
|---|---|---|
| N3b Placa | Placa a caballo grande, logo de Nissan a 2,5×. | alta |
| N1 Hero móvil | Por debajo de 900 px, foto en banda de 200 px a opacidad 1 y H1 solapado 32 px. | media |
| N2 Usos | Plano de 4 columnas con una celda 2 × 2 de foto (uso-industria-pala*); «+» que se dibujan; 390: 2 columnas con `details`. | media |
| N3 Escala | Cinta métrica: 3 / 150 / 250 en t-gigante con contador, marcas cada 10 kg, cajas-oscuro saliendo por abajo. Móvil: regla vertical. | media |
| N4 Compras | Expediente: hoja A4 con borde tinta y segunda hoja «Ficha de datos de seguridad» detrás; checks escalonados. Fuera pinza y beige. | baja |
| N6 + cierre | `dipt_faq()` + cierre común (cenital-caja-nuggets). | baja |

**Laboratorios** · laboratorios.php, sector-laboratorios.css. No tocar: L2.

| Sección | Propuesta | Prio |
|---|---|---|
| L1 Hero móvil | Círculo de 150 px a la derecha, solapado sobre el H1; CTA dentro de los primeros 700 px. | alta |
| L3b Placa | Placa a caballo (Semillas Fitó). | alta |
| L3 Anatomía | Pines numerados sobre la caja enlazados con la lista (`data-activa`, scale 1,18). Sin inclinación. | media |
| L4 Centros | uso-laboratorio-entrega con cortina y tres hojas de servicio solapadas en diagonal. | media |
| L5 + cierre | `dipt_faq()` + cierre común (nuggets-azul). | baja |

**Eventos** · eventos.php, sector-eventos.css, paginas/sector-eventos.js. No tocar: V7 (solo pasa a noche, por el pie marino).

| Sección | Propuesta | Prio |
|---|---|---|
| V3 Tres pasos | Escenario sticky con 3 fotos que se funden según el paso; pasos a 45 vh; opacidad mínima 0,6 (hoy 0,35 incumple AA). 390: cada paso con su foto. | alta |
| V1 Hero | Fuera el zoom de la imagen LCP; tercera capa de niebla; recorte «50% 40%» para esconder el rótulo de la máquina. | media |
| V2 Recetas | Tres fichas de altura creciente con la foto de lo que llega; sin celo ni giro; suben desde la base. 390: carril. | media |
| V5 Seguridad | Hoja de advertencia 2 × 2 con pictogramas propios y cabecera marino; enlace a seguridad. | media |
| V4 Halloween | Miniatura de la landing + «JUE 29/10 · 12:00» en mono. Se retira el 1/11. | baja |
| V6 | `dipt_faq()`. | baja |

### 2.9 Qué es · que-es.php, que-es.css

Tonos: Q1 papel · Q2 blanco · Q3 papel *aire* · Q4 noche · Q5 escarcha · Q6 blanco · cierre noche.

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| Q1 Hero | Dibujo de cápsulas; sin compra visible. | Foto vertical 4:5 a sangre que baja 4–5 rem sobre Q2, vapor encima, cifra −78,5 °C con contador montada en su esquina. «Comprar hielo seco» + «Normas de seguridad». | alta |
| Q4 Tamaño real | Dos cajas discontinuas casi vacías. | Regla de laboratorio de borde a borde con las piezas a tamaño real encima y dos macros 4:5 con cota; la regla entra en horizontal y las piezas caen. | alta |
| Q2 Ficha | Hoja sola en un mar blanco; seguida de otra tabla. | Hoja girada −1,5° sobre cenital-3mm; filas escalonadas a 60 ms. | media |
| Q3 Comparativa | Tabla cortada en 390. | Termómetro vertical con 0 / −18 / «hasta unos −33» / −78,5 °C (datos del contenido); la marca del hielo seco baja al entrar. | media |
| Q5 Origen | Iconos en cuadrado blanco (tarjeta con icono arriba). | Estado del CO₂ en mono y número grande; un pellet baja la escalera una vez. Nota al pie sin caja. | media |
| Q6 Dudas | Cuatro tarjetas iguales. | Foto caja-3mm-lateral sticky y preguntas como titulares con filetes; sin acordeón. | media |
| Cierre | Franja fina. | Cierre común (cajas-furgoneta) [titular a validar]. | media |

### 2.10 Seguridad · seguridad.php, seguridad.css, contenido/es/seguridad.php

No tocar: S2 (solo la flecha también con `.visto`).

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| S3 Haz / evita | Plana y sin una sola foto. | uso-laboratorio-pinzas vertical montada sobre la costura, con cortina; checks y aspas escalonados. | alta |
| S4 112 | Crema y marrón fuera de paleta. | Papel; 112 en `--c-senal` (comprobar AA a tamaño grande), sticky en escritorio; botón de llamada a lo ancho en 390. | media |
| S5 FDS | Barras grises de esqueleto; solo hover. | Apartados reales numerados en mono, segunda hoja detrás, se endereza al entrar. | media |
| S6 + cierre | Sin cierre; comillas rectas en "humo". | Cierre común (anadiendo-hielo) y «humo». | media |
| S1 móvil | Etiqueta de ~450 px sola. | 220 px montada sobre el final de la respuesta; balanceo único al entrar. | baja |

### 2.11 Guías, guía, zonas, zona, legal, 404

| Página · sección | Problema | Propuesta | Archivos | Prio |
|---|---|---|---|---|
| Guías · lista | Índice sin imágenes; sale «Hello world!» (`post_name__not_in` no existe en WP_Query). | Primera guía destacada con foto grande; el resto con miniatura fija 4:5 que se descubre al entrar. Clave `foto` en cada guía. Arreglar el filtro (`post__not_in` por ID o filtrar después). | guias.php, guias.css, contenido/es/guias/*.php | alta |
| Guía · cabecera | Pastilla «2 min»; sin foto. | Foto de la guía a sangre que pisa el cuerpo; meta en mono sin caja. | partes/guia-articulo.php, guia.css | alta |
| Guía · cuerpo | Tercio derecho vacío; índice sin estado. | Notas al margen (aviso de guante, acceso «Comprar 3 mm»); apartado activo con `data-activa`; resumen sin caja. | partes/guia-articulo.php, guia.css | media |
| Guía · para seguir | Sin jerarquía. | Siguiente guía con foto; enlaces fijos en lista pequeña. | ídem | baja |
| Zonas · hero | Mapa pequeño, nombres solo con hover, «35 €» partido. | Mapa grande a sangre, barrido de radar de 20 s, puntos por distancia, nombres grandes visibles y el resto al tocar. Espacio de no separación. | zonas.php, zonas.css, inc/zonas.php | media |
| Zonas · final | «¿No ves tu zona?» perdido. | Cierre común (cajas-furgoneta, «Ver envíos»). | zonas.php | baja |
| Zona · Z4 usos | Sección enorme con pastillas. | Tira de fotos de anchos distintos con el nombre en mono; scroll-snap en móvil. | zona.php, zona.css | alta |
| Zona · Z2 mapa | Mitad vacía y quieto. | Tres pasos en mono a la izquierda; furgoneta que viaja de Mataró a la zona (coordenadas de PHP en variables CSS). | zona.php, zona.css, inc/zonas.php | media |
| Zona · Z3, Z5 | — | Precio de recogida destacado; acordeón con «+»→«−»; `.seccion--densa`. | zona.css | baja |
| Legal · cabecera | Pastilla de fecha. | Línea en mono sin caja. | legal.css | baja |
| 404 | Cifra muy tenue en móvil. | Opacidad mínima 0,3; añadir «Guías». | error404.css, 404.php | baja |

### 2.12 Halloween · halloween.php, assets/halloween/css/halloween.css, assets/halloween/js/halloween.js

Va contra reloj y es independiente del resto (tiene su hoja). **Congelada desde el 26/10.** No tocar: pasos 5, 6, 7, 9 y 10 (como mucho, subir la opacidad de la foto del 10).

| Sección | Problema | Propuesta | Prio |
|---|---|---|---|
| 1 Hero | Pastillas con puntito, barra naranja y antetítulos repetidos. | Línea de ficha en mono con filetes verticales; el ticket «Último pedido» como único destacado; antetítulo solo aquí. | alta |
| 4 Cuánto | Tres tarjetas iguales. | Una escala con tres paradas (casa {pk} kg · fiesta 10 · bar 15–20) en radiogroup; la parada elegida muestra precio y botón; indicador que se desliza (200 ms). Vertical en 390. | alta |
| 2 Ideas | Caldero como caja vacía. | Foto niebla-cenital con el icono neón como sello. | media |
| 3 Formato | Dos fotos de caja casi iguales. | Macros cerradas (pellets-ia/nuggets-ia* o recortes de cenital-*), la de 3 mm más grande y solapada. | media |
| 8 B2B | Foto gris que repite la sección 3. | uso-eventos o uso-hosteleria a sangre con el texto montado. | media |

---

## 3. Orden de trabajo en lotes

Cada lote es dueño exclusivo de sus archivos. Los lotes de página **no tocan** base.css, componentes.php, movimiento.js, header.php ni footer.php: el lote 0 deja hechas todas las piezas genéricas (observadores, `dipt_faq()`, cierre, placa, deslizador, clases de foto). Si un lote de página necesita algo genérico, lo pide al lote 0.

| Lote | Cuándo | Archivos (exclusivos) | Contenido |
|---|---|---|---|
| **H · Halloween** | Ya, en paralelo con el 0. Termina antes del 25/10. | plantillas/halloween.php, assets/halloween/** | 2.12 |
| **0 · Sistema** | Primero, una sola persona | base.css, inc/componentes.php, inc/assets.php, assets/js/movimiento.js, header.php, footer.php, partes/cierre.php (nuevo), assets/css/paginas/sectores.css (nuevo), herramientas/preparar-imagenes.mjs, assets/img/fotos/ | 1.2 completo |
| **1 · Portada** | Tras el 0 | plantillas/inicio.php, paginas/inicio.css, contenido/es/inicio.php, assets/js/paginas/inicio.js | 2.1. I5 lo integra el equipo de usos dentro de este lote (mismos archivos: coordinar) |
| **2a · Producto + checkout** | Tras el 0 | producto.php, producto.css, paginas/producto.js, tienda.css, plugin/…/assets/checkout.css, inc/woocommerce.php | 2.2, 2.3 |
| **2b · Envíos, empresas, contacto** | Tras el 0 | envios.*, empresas.*, contacto.*, partes/mapa-peninsula.php | 2.4–2.6 |
| **3a · Aplicaciones, hostelería, transporte** | Tras el 0 | aplicaciones.*, contenido/es/aplicaciones.php, hosteleria.php, sector-hosteleria.css, transporte.php, sector-transporte.* | 2.7, 2.8 |
| **3b · Industria, laboratorios, eventos** | Tras el 0 | industria.php, sector-industria.css, laboratorios.php, sector-laboratorios.css, eventos.php, sector-eventos.* | 2.8 |
| **4 · Contenido** | Tras el 0 | que-es.*, seguridad.*, contenido/es/seguridad.php, guias.*, guia.*, partes/guia-articulo.php, contenido/es/guias/*, zonas.*, zona.*, inc/zonas.php, legal.css, error404.css, 404.php | 2.9–2.11 |

Lotes 1 a 4 en paralelo. Dentro de cada lote, primero las filas «alta». Un commit por sección que funcione (lo hace quien coordina). Los textos marcados [a validar] no se cambian hasta tener el OK del propietario: se dejan con el texto actual.

**Textos que necesitan OK** (antes/después en estrategia/COPY-ANTES-DESPUES.md): párrafo del hero de la portada a una frase · H2 de la ficha («No es hielo. Es CO₂ sólido.») · frase de clientes · contador de Envíos fuera de horario · estado de Contacto · titulares de los cierres de Qué es y Aplicaciones.

---

## 4. Verificación

**Antes de empezar** (las auditorías de portada y compra se hicieron sin servidor): capturas de partida con los scripts ya preparados, con el servidor de 127.0.0.1:9500 arrancado desde el panel (no se arranca ni se reinicia desde los lotes).

| Grupo | Script | Salida |
|---|---|---|
| Portada | `node .tmp/rediseno/auditoria/portada-capturar.mjs` | .tmp/rediseno/auditoria/portada/ |
| Compra y checkout (envío y recogida) | `node <scratchpad>/capturar-compra.mjs` | .tmp/rediseno/auditoria/compra/ |
| Sectores | `node .tmp/rediseno/auditoria/sectores-capturar.mjs` | .tmp/rediseno/auditoria/sectores/ |
| Contenido, cabecera, menú, pie | `node .tmp/rediseno/auditoria/contenido-capturar.mjs` | .tmp/rediseno/auditoria/contenido/ |

**Después de cada lote**, para cada página tocada:

| Prueba | Criterio |
|---|---|
| Capturas 390 y 1440 (primera pantalla, scroll lento, página entera) | Comparadas con las de partida, en la misma carpeta con sufijo `-despues` |
| Lighthouse móvil | Accesibilidad ≥ 95 · SEO ≥ 95 · Rendimiento ≥ 90 · CLS < 0,1 (objetivo 0) · LCP < 2,5 s. Tabla antes/después. Lighthouse no está en el proyecto: [PENDIENTE: OK para instalarlo como devDependency o usar el de Chrome DevTools] |
| Reducción de movimiento | Con `.quieto` todo se ve, nada queda a medias (contadores en su valor final, furgoneta en destino, pellet en el último peldaño) |
| Móvil sin hover | Cada efecto se ve con el scroll o al tocar |
| Contraste | AA en cada combinación nueva (pie marino, 112 en señal, pasos atenuados ≥ 0,6) |
| Búsqueda en el código | Ninguna transición ni animación que no sea `transform`/`opacity`; ninguna duración de entrada > 600 ms; niebla 15–30 s; sin `backdrop-filter`, sin degradados nuevos, sin radios > 8 px salvo píldora |
| Peso | Hero ≤ 120 KB; fotos nuevas por `dipt_foto()` con `srcset`, `width`/`height` y lazy (salvo LCP con `fetchpriority="high"`); ninguna librería nueva; un solo IntersectionObserver compartido por tipo |
| Ritmo | Mapa de tonos de la página cumplido; ningún recurso de movimiento repetido en secciones seguidas (se apunta en el catálogo de estrategia/DISENO.md) |

**Lo que no se toca:**
- Textos y datos ya revisados (COPY-ANTES-DESPUES.md), precios, plazos, H1, titles, metas y schema. Las preguntas cambian de aspecto, no de texto.
- Lógica de la tienda: PHP del plugin, cálculo de envío y recogida, corte, festivos, factura, métodos de pago, claves de los pedidos que lee la app y la API `/dip/v1/envio`. En el checkout solo CSS (y la miniatura del resumen).
- Ajustes de WooCommerce, WooPayments, WP Rocket y Cloudflare sin preguntar.
- Halloween desde el 26/10.
- Avisos de seguridad: se pueden mover y rediseñar, nunca quitar ni acortar.
