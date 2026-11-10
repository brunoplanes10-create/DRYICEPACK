# Google Shopping gratis (Merchant Center) · paso a paso

Para el propietario, 11/11/2026. Sin plugin: Google lee el producto de la web (datos estructurados de la ficha: precio con IVA, envío por peso, plazo y sin devolución). Tiempo: 20–30 minutos.

## 1. Crear la cuenta
1. Entra en **merchants.google.com** con la cuenta de Google de DryIcePack (la misma de Search Console).
2. Nombre de la empresa: **DryIcePack** · País: **España** · Zona horaria: **Madrid**.
3. «¿Dónde vendes?»: **en mi web** → `https://dryicepack.es`.
4. **Verificar la web:** elige «Search Console» (ya está verificada): un clic.

## 2. Añadir el producto
- Productos → Añadir productos → **«Añadir productos automáticamente desde tu web»**.
- Google rastrea la ficha `/producto/hielo-seco/` y crea las 8 variantes (3, 10, 15 y 20 kg en 3 mm y 16 mm). Tarda de horas a pocos días.

## 3. Envío (Ajustes → Envío y devoluciones → Envío → Añadir servicio)
- Nombre: **MRW 24 h** · País: **España**.
- **Zonas:** toda España **menos** Baleares (CP 07), Las Palmas (35), Santa Cruz de Tenerife (38), Ceuta (51) y Melilla (52). Si pide «regiones por código postal», crea una región «Península» con los rangos 01000–06999, 08000–34999, 36000–37999 y 39000–50999.
- **Plazo:** hora límite **12:00** · días de preparación **0** · días de tránsito **1** · días laborables **lunes a viernes**.
- **Coste por precio del pedido** (con IVA; es el envío real de un pack, peso facturable = hielo + 1 kg por caja):

| Precio del pedido (IVA incl.) | Envío (IVA incl.) | Pack que corresponde |
|---|---|---|
| 0 – 40,00 € | 12,49 € | 3 kg (36,06 €) |
| 40,01 – 50,00 € | 15,96 € | 5 kg (41,02 €), cuando entre |
| 50,01 – 80,00 € | 17,32 € | 10 kg (64,49 €) |
| 80,01 – 100,00 € | 24,09 € | 15 kg (90,63 €) |
| 100,01 € o más | 30,87 € | 20 kg (119,31 €) |

  Si Google ofrece **«Usar el envío de los datos estructurados de tu web»**, mejor esa opción: cada variante ya lleva su envío exacto.
- El sábado (antes de las 12:00, según zona, +11,74 €) **no** se pone: Google solo enseña el plazo estándar.

## 4. Devoluciones (Ajustes → Envío y devoluciones → Política de devoluciones)
- **No se admiten devoluciones** (producto perecedero: art. 103.d del TRLGDCU).
- Enlace a la política: `https://dryicepack.es/condiciones-de-venta/`.

## 5. Listados gratuitos
- Crecimiento → Gestionar programas → **Listados gratuitos** (o «Mostrar productos en Google»): activado.
- Anuncios de pago: **no** hace falta.

## 6. Qué mirar después
- **Productos → Diagnóstico:** si Google marca algo (p. ej. «producto peligroso» o falta de identificador GTIN). El hielo seco no tiene GTIN: si lo pide, marcar «el producto no tiene GTIN» (es de marca propia).
- Si rechaza el producto por la política de productos peligrosos, pásame el mensaje exacto y vemos si se puede recurrir.
- A las 2–4 semanas: Rendimiento → clics desde Shopping.
