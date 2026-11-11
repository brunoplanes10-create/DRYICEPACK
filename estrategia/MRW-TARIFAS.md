# Tarifas de MRW para INDUNOVA (2026)

Transcrito el 11/11/2026 de lo que pasó la comercial de MRW Mataró (franquicia 00606, abonado 020144):
`INDUNOVA URGENTES (2).pdf` («Oferta Expediciones», julio de 2026), `INDUNOVA SABADOS ECOMMERCE (1).pdf` («Oferta
e-Commerce») y la imagen del precio nuevo del Urgente 14. Precios **sin IVA**. Oferta válida hasta el 31/12/2026; el
1 de enero suben con el IPC. Volumen estimado en la oferta: 10 envíos al mes.

## Qué servicio se usa (decidido por el propietario el 11/11/2026)

| Envío | Servicio | Código MRW |
|---|---|---|
| Lunes a jueves (llega martes a viernes), **el de serie y el más barato** | Urgente 14 Expedición | 0115 |
| Opción que puede elegir el cliente | Urgente 12 Expedición | 0105 |
| Opción que puede elegir el cliente | Urgente 10 Expedición | 0015 |
| Sábado (sale el viernes) | Ecommerce, con «entrega en sábado» | 0800 |

En la web de clientes de MRW la casilla «entrega en sábado» no se marca sola en ningún servicio; la web la manda
siempre en los envíos de sábado (`EntregaSabado = S`).

## Urgente 14 Expedición (0115): precio nuevo, igual en toda la península

| Peso | España peninsular | Portugal peninsular |
|---|---|---|
| Hasta 2 kg | 8,53 € | 10,37 € |
| Hasta 5 kg | 10,32 € | 12,15 € |
| Hasta 10 kg | 13,19 € | 15,02 € |
| Kg adicional | 1,12 € | 1,37 € |

Es la tarifa de envío de la web (`inc/negocio.php`). Sustituye a la tabla de 14 h del PDF (por zonas: Urbano 7,65 €
hasta 5 kg, Provincial 9,63 €, Regional-Limítrofe 13,71 €, Peninsular 17,51 €…), que ya no vale.

## Urgente 12 Expedición (0105)

| Peso | Urbano | Provincial | Regional-Limítrofe | España peninsular |
|---|---|---|---|---|
| Hasta 1–5 kg | 8,30 € | 11,05 € | 16,09 € | 21,06 € |
| Hasta 6–10 kg | 16,60 € | 22,10 € | 32,18 € | 42,12 € |
| Kg adicional | 1,66 € | 2,21 € | 3,22 € | 4,21 € |

(Sin columna de Portugal.)

## Urgente 10 Expedición (0015)

| Peso | Urbano | Provincial | Regional-Limítrofe | España peninsular | Portugal peninsular |
|---|---|---|---|---|---|
| Hasta 1–5 kg | 8,92 € | 12,26 € | 24,28 € | 26,53 € | 26,53 € |
| Hasta 6–10 kg | 17,84 € | 24,52 € | 48,56 € | 53,06 € | 53,06 € |
| Kg adicional | 1,78 € | 2,45 € | 4,86 € | 5,31 € | 5,31 € |

**Suplemento por entrega antes de las 10:00 a más de 25 km: 15,30 €.**

(En el PDF también está el Mañana 8:30, que no se usa.)

## Condiciones de los Urgentes (Expedición)
- Peso medio mínimo por bulto: 2 kg (si no, se cobran los kilos que faltan).
- Máximo 250 kg por expedición y 80 kg por bulto. Suplementos: bulto de más de 40 kg, 17,25 €; de más de 60 kg, 23,60 €.
- Medida máxima por bulto: 300 cm (alto + largo + ancho). Bultos de 151–200 cm, 201–250 cm y 251–300 cm, con tarifa propia.
- Incluye 2 intentos de entrega.
- Reembolsos: 6 % (mínimo 2 €, máximo 50 €). Confirmación inmediata de recogida o entrega: 5,94 € por expedición.

## Ecommerce (0800), «Peninsular Start Up», entrega en domicilio

| Peso | Nacional | Regional-Limítrofe | Provincial | Urbano |
|---|---|---|---|---|
| Hasta 2 kg | 8,95 € | 7,20 € | 6,22 € | 6,22 € |
| Hasta 5 kg | 11,71 € | 9,37 € | 7,54 € | 7,54 € |
| Hasta 10 kg | 14,48 € | 11,57 € | 10,33 € | 10,33 € |
| Hasta 15 kg | 17,25 € | 13,80 € | 12,44 € | 12,44 € |
| Hasta 20 kg | 20,02 € | 16,02 € | 14,19 € | 14,19 € |
| Hasta 25 kg | 24,86 € | 19,87 € | 16,63 € | 16,63 € |
| Hasta 30 kg | 30,14 € | 24,11 € | 19,71 € | 19,71 € |
| Hasta 35 kg | 33,14 € | 26,53 € | 21,68 € | 21,68 € |
| Hasta 40 kg | 36,45 € | 29,15 € | 23,87 € | 23,87 € |
| Cada 5 kg más | +5,11 € | +4,06 € | +2,90 € | +2,90 € |

(También hay tarifas de entrega en oficina MRW, MRW Point, franja horaria, marketplaces, canje y ultrarrápido, que no se usan.)

## Condiciones del Ecommerce
- **Entrega en sábado:** suplemento de **5,45 €** por expedición, o de **9,70 €** en poblaciones a más de 25 km de la
  oficina que hace la entrega, salvo excepciones (consultar con la oficina). No existe para envíos internacionales, Ceuta,
  Melilla ni Gibraltar.
- Multibulto: máximo **80 kg por expedición**; **+2 € por bulto a partir del 3.º** (incluido). Peso máximo por bulto: 80 kg
  (más de 40 kg, +17,25 €; más de 60 kg, +23,60 €).
- Cobra el peso real, no el volumétrico. Medidas de más de 150 cm: 151–200 cm 5,17 €, 201–250 cm 14,50 €, 251–300 cm 25,80 €.
- Incluye 1 intento de entrega y entrega en el punto MRW más próximo.
- No incluye seguro, ni suplemento de combustible, ni tasa de energía.
- En campaña, MRW no garantiza la entrega puntual de los servicios económicos (Ecommerce). Recomienda los premium para asegurar el plazo.
- Las tarifas dependen del volumen comprometido a partir del tercer mes.

## Por confirmar con la comercial
- Qué es exactamente Urbano, Provincial y Regional-Limítrofe desde Mataró. Propuesta: Urbano = Mataró;
  Provincial = provincia de Barcelona; Regional-Limítrofe = resto de Cataluña (Girona, Lleida y Tarragona; Barcelona solo
  limita con ellas).
- A qué hora entrega el Ecommerce el sábado. La web promete «antes de las 12:00».
- Cómo saber si un destino está a más de 25 km de su oficina MRW (sábado: 5,45 € o 9,70 €; 10:00: +15,30 €).
- Si se aplica el suplemento de combustible a los Urgentes.
