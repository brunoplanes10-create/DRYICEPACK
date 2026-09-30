# Diseño · "Frío en calma"

30/09/2026 · Sustituye a la dirección "Cadena de frío" (rechazada el 29/09).

## De dónde sale

- **Referencias Wix Explore** (11 webs estudiadas en `referencia/webs/capturas/hoja.jpg`):
  - La imagen manda y hay muy poco texto.
  - Tipografía grande y editorial (serifas en The Nomads y Sonja van Dülmen).
  - Composiciones asimétricas con imágenes que se solapan (Festela, Akaivyc).
  - Un único objeto animado sobre negro (Conqr).
  - Campos de color a sangre (The Robin Collective).
- **Google Research (2012):** las webs con poca complejidad visual y estructura reconocible se juzgan más bonitas en 50 ms. Por eso cada sección cuenta una sola idea, pero con una composición propia.
- **Fotos:**
  - Reales del producto: caja EPS con niebla sobre fondo oscuro y vista cenital de 3 y 16 mm.
  - Las de ChatGPT que ya tiene la web (sectores).
  - Los vídeos llevan calabazas: solo para Halloween.

## Sistema

| Pieza | Decisión |
|---|---|
| Titulares | **Instrument Serif** 400 (y cursiva para el énfasis). Grande, interlineado corto, en minúscula de frase. |
| Texto e interfaz | **Geist** 300–700. |
| Datos técnicos | **Geist Mono**: temperaturas, milímetros, kilos, horas y precios en ficha técnica. |
| Color | Noche `#0B141C`, marino de marca `#1F3C55`, hielo del copo del logo `#B8D0E8`, escarcha `#EEF3F7`, papel `#F6F8F9`, tinta `#0F1D29`, acero `#56677A`. Sin degradados salvo la niebla. |
| Botón principal | Píldora: hielo sobre oscuro y marino sobre claro. Uno por pantalla. |
| Movimiento | Siempre hay algo moviéndose, despacio: niebla, cinta de datos, parallax, contadores, rutas dibujándose, imágenes que siguen al cursor. Solo `transform` y `opacity`. Con "reducir movimiento" todo se queda quieto. |
| Prohibido | Etiquetas-píldora encima de los títulos, puntos que laten, tres tarjetas iguales, glassmorfismo, degradados morados, iconos de librería. |

## Orden de cada página de venta

Basado en AIDA (atención, interés, deseo, acción), en las pruebas de atención y desplazamiento de Nielsen Norman Group (lo que está arriba recibe la mayor parte de la atención) y en Baymard para fichas y checkout.

1. **Atención.** Qué es, para quién y el botón, sin hacer scroll.
2. **Interés.** Cómo funciona o el problema que resuelve, con un dato concreto.
3. **Deseo.**
   - Elegir: formato, cantidad y precio final.
   - Pruebas: usos reales y casos.
   - Ventaja propia: recogida en Mataró, cuenta de empresa.
4. **Objeciones.** Seguridad, envío y preguntas frecuentes.
5. **Acción.** Llamada final, la misma que arriba.

## Catálogo de secciones

Cada sección tiene una composición distinta y no se repite en ninguna otra página. Las plantillas de zona, de guía y de texto legal se repiten dentro de su familia a propósito: son la misma pieza con otros datos.

| Código | Página | Sección | Composición |
|---|---|---|---|
| I1 | Inicio | Hero | Cámara oscura: foto real a sangre con niebla en capas y titular serif gigante; reloj del corte en mono |
| I2 | Inicio | Cinta | Datos técnicos desfilando en continuo |
| I3 | Inicio | Elegir | Dos paneles 3 mm / 16 mm que se expanden y regla de kilos con total en directo |
| I4 | Inicio | Cómo llega | Mapa peninsular con rutas que se dibujan desde Mataró y tres horas grandes |
| I5 | Inicio | Usos | Índice editorial numerado con imagen que sigue al cursor |
| I6 | Inicio | −78,5 °C | Cifra gigante que baja contando, con cotas de plano técnico alrededor |
| I7 | Inicio | Empresas | Tarjeta de cliente que se inclina con el cursor y texto a un lado |
| I8 | Inicio | Recogida | Foto a sangre con ficha de dirección solapada y mini mapa del Maresme |
| I9 | Inicio | Seguridad | Cuatro rombos de pictograma en fila |
| I10 | Inicio | Preguntas | Título fijo a la izquierda y acordeón a la derecha |
| I11 | Inicio | Final | Niebla a pantalla completa, pregunta grande y teléfono |
| P1 | Producto | Configurador | Galería apilada y panel de compra fijo con total con envío y fecha |
| P2 | Producto | La caja | Despiece de la caja EPS que se separa al hacer scroll |
| P3 | Producto | 3 o 16 mm | Comparador deslizante antes/después |
| P4 | Producto | Precio por kilo | Escalera de barras con el precio por kilo |
| P5 | Producto | Envío | Tarifa en forma de billete con cortes por peso |
| P6 | Producto | Preguntas | Conversación en burbujas |
| E1 | Empresas | Hero | Foto vertical recortada y titular a dos alturas |
| E2 | Empresas | Cómo piden | Tres pantallas: WhatsApp, "repetir pedido" y email, en carrusel |
| E3 | Empresas | Ventajas | Lista numerada gigante con líneas que se dibujan |
| E4 | Empresas | Volumen | Pila de cajas que crece con los kilos |
| E5 | Empresas | Usos reales | Mosaico de textos e imágenes de tamaños distintos |
| E6 | Empresas | Alta | Formulario en frase para completar |
| E7 | Empresas | Preguntas | Dos columnas de pregunta y respuesta abiertas |
| … | (resto) | | Se completa al construir cada página |
