# Cancelar Odoo sin perder nada

**Para hacerlo ya, sin esperar al panel.** Lleva unos 45 minutos. Lo que descargues se importará en el panel en la Fase 3 (clientes y productos).

**Por qué hay prisa:**
- Odoo **se renueva solo** si no avisas **por escrito, al menos 30 días antes** de que acabe el periodo contratado.
- Cuando termina, la base de datos queda desactivada **unas 3 semanas** y después **se destruye**.

**El orden importa:** fecha → descarga → comprobación → aviso. Nunca al revés.

---

## 1. Mira cuándo se renueva

1. Abre **odoo.com/my/subscriptions** e inicia sesión con la cuenta con la que se contrató Odoo.
2. Abre vuestra suscripción y apunta **la fecha en que termina el periodo** (o la de la próxima factura).
3. **Resta 30 días.** Esa es la fecha límite para avisar.
4. Ponla en tu calendario con un aviso una semana antes.
5. **Mándame la fecha.** Con ella ajusto el calendario de la migración.

> Si la fecha límite ya ha pasado o cae en los próximos días, salta al paso 5 después de hacer el 2. La descarga es lo primero.

## 2. Descarga la copia completa

1. Abre **odoo.com/my/databases** e inicia sesión como administrador de la base de datos.
2. Junto a vuestra base, pulsa el **engranaje** y luego **«Download Backup»**.
3. Se descarga un archivo ZIP. Guárdalo con un nombre claro, por ejemplo `odoo-copia-completa-2026-10-01.zip`.
4. **Si el botón está gris**, la base es demasiado grande para descargarla desde ahí. Pídesela al soporte de Odoo en **odoo.com/help**.

**Comprueba el ZIP (2 minutos):**
1. Ábrelo con doble clic, sin descomprimirlo.
2. Dentro tiene que haber:
   - `dump.sql`: los datos;
   - una carpeta `filestore`: los archivos adjuntos (PDF, fotos…);
   - `manifest.json`.
3. **Si falta la carpeta `filestore`, dímelo antes de seguir.** La documentación de Odoo no dice expresamente que la copia incluya los adjuntos.

## 3. Exporta cada lista a Excel

El ZIP es la copia de seguridad; los Excel son lo que el panel sabe leer. Haz esto con **Contactos**, **Productos**, **Facturas de cliente**, **Facturas de proveedor** y **Pagos**. Repítelo con cualquier otra app que uséis (Ventas, Compras, CRM, Proyecto…).

1. Abre la app y cambia a la **vista de lista** (el icono de las rayas).
2. Borra los filtros de la barra de búsqueda para que salgan todos los registros.
3. Marca la **casilla de la cabecera** para seleccionar todo. Si aparece **«Seleccionar todo»** (cuando hay más de una página), púlsalo.
4. Pulsa **«Acción» → «Exportar»**.
5. Marca la casilla de la **exportación compatible con la importación** («I want to update data (import-compatible export)»).
6. Elige el formato **XLSX** y pulsa **«Exportar»**.
7. Guarda cada archivo con la lista y la fecha: `odoo-contactos-2026-10-01.xlsx`, `odoo-productos-2026-10-01.xlsx`…

**Facturas en PDF.** En la lista de facturas, selecciónalas todas y pulsa **«Imprimir» → «Export ZIP»**. Si alguna factura se **emitió desde Odoo**, ese PDF es el original: hay que conservarlo **al menos 6 años**, y **hasta 10** en algunos casos. Confírmalo con la gestoría.

## 4. Guarda dos copias fuera de Odoo

Junta el ZIP completo, los Excel y los ZIP de facturas en una carpeta, por ejemplo **«Odoo · copia final»**:

- **Copia 1:** en el Microsoft 365 **de la empresa**, no en el OneDrive personal (ver la propuesta P8 de `PLAN.md`).
- **Copia 2:** en el disco externo.

Tienen datos reales de clientes:
- **Nunca** en GitHub ni en el repositorio del panel.
- No los mandes por correo ni por WhatsApp.
- Para la importación de la Fase 3, te diré cómo pasármelos de forma segura.

## 5. Avisa por escrito de que no renováis

1. Escribe a Odoo **antes de la fecha límite del paso 1**. Cuanto antes, mejor.
2. El contrato solo exige que el aviso sea **«por escrito»**; no dice por qué canal. Usa una de estas dos vías, en este orden:
   - el formulario de soporte, **odoo.com/help**, eligiendo el asunto de suscripción o facturación;
   - una respuesta al correo de vuestro comercial o de facturación de Odoo.
3. Texto que puedes copiar:

   > Asunto: Aviso de no renovación de la suscripción
   >
   > Buenos días:
   >
   > Les comunicamos que INDUNOVA IMS S.L. no desea renovar la suscripción de Odoo asociada a la base de datos `[nombre de la base]` cuando finalice el periodo actual, el `[fecha del paso 1]`. Les rogamos que confirmen la recepción de este aviso.
   >
   > Un saludo,
   > `[nombre y cargo]`

4. **Guarda su respuesta** (captura o PDF) junto a la copia final.

> **Importante:** en odoo.com/my/databases existe el botón **«Delete»**. **No lo uses.** Borra la base **en el acto y sin vuelta atrás**. Con el aviso basta: podéis seguir usando Odoo hasta el último día pagado.

## 6. La última semana

1. **Repite los pasos 2, 3 y 4** con los datos del último día, para no perder nada de lo que se haya apuntado después de la primera descarga.
2. Comprueba que Odoo no os cobra el periodo siguiente.

## 7. Después

- La base queda desactivada **unas 3 semanas** y luego se destruye. En las copias de seguridad de Odoo pueden quedar datos **hasta 12 meses**. Esto viene de su política de privacidad, que solo he podido leer en extractos del buscador.
- Si el panel aún no tiene Clientes (Fase 3), mientras tanto os quedan los Excel y a3.
- En la Fase 3, el panel importa clientes y productos desde esos Excel. Las facturas se importan desde a3.

---

## Fuentes (consultadas el 01/10/2026)

- **Renovación y aviso de 30 días:** Odoo Enterprise Subscription Agreement, apartado 1, «Term of the Agreement»: «automatically renewed for an equal Term, unless either party provides a written notice of termination minimum 30 days before the end of the Term». <https://www.odoo.com/documentation/19.0/legal/terms/enterprise.html> (leído en github.com/odoo/documentation).
- **Gestor de bases de datos, «Download Backup» y «Delete»:** <https://www.odoo.com/documentation/19.0/administration/odoo_online.html> (github.com/odoo/documentation).
- **Exportar a Excel:** <https://www.odoo.com/documentation/19.0/applications/essentials/export_import_data.html> (github.com/odoo/documentation).
- **Contenido del ZIP** (`dump.sql`, `filestore/`, `manifest.json`): código de Odoo 19.0, función `dump_db` (github.com/odoo/odoo).
- **Plazos tras cancelar (3 semanas; 12 meses en copias):** política de privacidad de Odoo, <https://www.odoo.com/privacy> (solo extractos del buscador).
- **Conservación de facturas:** Código de Comercio, art. 30 (6 años), y Ley General Tributaria (prescripción de 4 años, hasta 10 con bases negativas o deducciones pendientes). Detalle en `PLAN.md`, apartado 3.8.
