# INDUNOVA Control — Prompt para Claude Code

> **Cómo usarlo:** crea una carpeta vacía (por ejemplo `indunova-control`), abre Claude Code dentro y pega todo este texto. No hay nada que rellenar.

---

## 0. Instrucciones para ti, Claude Code

Actúa como ingeniero full-stack sénior y diseñador de producto. Vas a construir desde cero **INDUNOVA Control**, el panel de gestión del grupo familiar INDUNOVA IMS S.L. (Mataró, Barcelona). Debe reunir toda la empresa en un solo sitio, funcionar en la nube, instalarse como app en ordenador y móvil y **sustituir a Odoo** como herramienta de control interno.

Sobre mí (Andrés): no soy desarrollador profesional. Mantendré esto contigo, así que:

- Prioriza lo simple, barato y mantenible. **El presupuesto es el mínimo posible.**
- Cada vez que yo tenga que hacer algo fuera del código (crear cuentas, registrar apps, configurar DNS, variables de entorno), explícamelo paso a paso, como si fuera la primera vez.
- Trabaja por fases (sección 13). Al acabar cada fase, **para y espera mi validación**.
- Guarda una copia literal de este texto en `docs/PROMPT_ORIGINAL.md` para futuras sesiones y crea un `CLAUDE.md` con las convenciones del proyecto.
- No inventes datos de negocio. Si te falta uno, pregúntamelo o déjalo como `TODO` visible en la interfaz.
- Antes de afirmar algo que pueda haber cambiado (precios, condiciones de un plan gratuito, límites de una API), compruébalo en la documentación oficial y dime la fuente.

## 1. Objetivo y principios

1. **Un solo sitio para todo:** correos, clientes, presupuestos, calendario, trabajos, pedidos, hielo seco, acreditaciones, facturas, cobros, gastos, contratos, documentos y tareas.
2. **Nada se apunta dos veces.** Si un dato ya existe (un correo, un pedido web, un PDF de factura), el panel lo lee y lo propone; la persona solo confirma.
3. **Pensado para el gerente (mi padre).** Las acciones de cada día deben estar a un máximo de dos clics, con textos claros y sin jerga. El panel tiene que **detectar los problemas antes de que ocurran** (sección 9).
4. **El panel no emite facturas fiscales.** Las facturas legales se hacen en **a3** (Wolters Kluwer) junto con la gestoría. Desde el 1 de enero de 2027 las sociedades deben usar software de facturación que cumpla VeriFactu (RD 1007/2023), y un programa propio nos convertiría en fabricantes con todas las obligaciones. El panel **importa y controla** las facturas, nunca las crea con numeración fiscal.
5. **Ningún correo sale solo.** La IA puede preparar borradores; enviar siempre requiere un clic humano.
6. **Nadie tiene acceso al banco.** No hay conexión bancaria: los extractos se descargan a mano y se importan.

## 2. La empresa

Una sociedad, **INDUNOVA IMS S.L.**, con tres unidades de negocio. Cada registro del panel (cliente, correo, presupuesto, trabajo, pedido, factura, gasto, tarea, evento) pertenece a una unidad o se reparte entre varias.

| Unidad | Qué hace | Correo | Color en todo el panel |
|---|---|---|---|
| **INDUNOVA** | Limpieza criogénica industrial con hielo seco. Principal fuente de ingresos. Unos 10 años con clientes industriales grandes. Trabajos por toda España, a veces con hotel y desplazamiento largo. Contrato con **TMB Barcelona** a 2 años (ampliable) con consumo de hielo seco cada día laborable. | info@indunova.es | **Amarillo corporativo de INDUNOVA.** Saca el hex exacto del logo o de indunova.es; si no lo encuentras, pídeme el logo. |
| **Dryicepack** | Venta de hielo seco en pellets y bloques a empresas (hostelería, cadenas de sushi, eventos, laboratorios, industria, transporte) y a particulares. Pedidos típicos de 3–10 kg en cajas EPS. Es un canal de venta de la cadena de suministro de INDUNOVA. | info@dryicepack.es | **Azul claro.** Usa el de la marca en dryicepack.es; provisional `#5FCAEC`. |
| **Cryocar** | Limpieza criogénica de vehículos en la nave de Mataró. **Arranca ahora:** acabamos de comprar el elevador. Capacidad: 2–3 vehículos al día, ~6 h facturables al día. Mismo flujo de trabajo que INDUNOVA. | info@cryocar.es | **Azul oscuro** `#1F3449` (Azul Cryocar). |

El amarillo tiene poco contraste sobre blanco: úsalo como relleno o borde con texto oscuro, nunca como color de texto.

**Hielo seco (común a las tres unidades)**
- Proveedor: SOL (SOL France Sucursal en España).
- Consumo anual aproximado: ~45 t en servicios propios, ~120 t para TMB y ~35 t de reventa en Cataluña. La reventa está limitada por nuestra capacidad de transporte, no por falta de demanda.
- El hielo seco sublima: el stock pierde peso cada día.
- Proyecto estratégico: producción propia con una **peletizadora**, comprando CO₂ líquido a granel. En nuestro análisis, la viabilidad depende de que el CO₂ líquido esté por debajo de ~380–400 €/t, con un payback de ~2 años en el escenario base.

**Otros temas abiertos:** posible traslado a una nave mayor zonificada para las tres unidades, consulta sobre registro sanitario (RGSEAA/RSIPAC) y plantillas de contrato de suministro.

## 3. Cómo trabajamos hoy (y qué debe resolver el panel)

### INDUNOVA (y Cryocar, que seguirá el mismo flujo)

1. Llega un correo a info@indunova.es. El gerente lo lee y lo contesta.
2. Si el cliente pide presupuesto, se le envía.
3. Si lo acepta, el trabajo **tiene que quedar en el calendario**.
4. El gerente lo planifica todo: fechas, operarios, material, **hotel** si hace falta, **transporte** (normalmente furgoneta; avión rara vez).
5. Se hace la **orden de trabajo** y los operarios van a trabajar.
6. Las empresas grandes exigen **cursos, permisos y documentación de coordinación de actividades empresariales (CAE)**, muchas veces a través de plataformas. Es lo que más le molesta: quiere tener siempre los cursos vigentes y los permisos aceptados.
7. Al acabar el trabajo, o a final de mes, se envía la factura.

### Dryicepack

- **Pedidos por la web** (WooCommerce en dryicepack.es). Los datos de facturación ya vienen en el pedido.
- **Pedidos por correo** a info@dryicepack.es.
- **Clientes recurrentes con domiciliación SEPA** (mandatos B2B firmados).
- **Clientes recurrentes con una factura mensual** que agrupa sus entregas.
- **Muchos clientes recogen en la nave y pagan en efectivo.**
- Envíos con **MRW**, reparto propio en la zona o recogida en la nave.

### Facturación, contabilidad y banco

- Las facturas reales se hacen en **a3** con la gestoría. Yo guardo los PDF en **carpetas ordenadas de OneDrive** y después los subo a Odoo solo para tener control interno. **El panel sustituye ese paso:** lee los PDF de OneDrive.
- **La gestoría lleva toda la contabilidad e impuestos.** Le pasamos facturas emitidas y gastos.
- Gastos y facturas de proveedor: hoy se suben a Odoo y se envían a la gestoría.
- Cobros: transferencia, domiciliación SEPA, tarjeta en la web y efectivo en la nave.
- Banco: **Banc Sabadell**, sin acceso para el panel ni para nadie más.
- Odoo Online plan Standard (sin API externa) se cancelará cuando el panel lo sustituya (sección 13).
- Usamos **Microsoft 365**: Outlook, calendario y OneDrive.

## 4. Usuarios, roles y permisos

Registro solo por invitación. Email + contraseña con **2FA (TOTP) obligatorio** para administradores y gestoría. Para operarios, 2FA opcional y sesión larga en el móvil.

| Rol | Quién | Qué ve y hace |
|---|---|---|
| `admin` | **aplanes@indunova.es** (Andrés) y **bplanes@indunova.es** (gerente) | Todo. |
| `operario` | Técnicos (se invitan después) | Solo sus trabajos, su calendario, órdenes de trabajo, partes desde el móvil, sus acreditaciones, documentos de sus trabajos, tareas y el botón «Pedir algo a…». Nunca ve importes, márgenes, banco ni otros clientes. |
| `gestoria` | Nuestra gestoría (se invita después) | Solo lectura de facturas emitidas, gastos, contratos que yo marque y la sección «Gestoría» para descargar paquetes. Nada de correos, clientes, banco ni operaciones. |

- Permisos con Row Level Security en base de datos, no solo ocultando botones.
- **«Ver como…»**: un admin puede ver el panel exactamente como lo ve un operario o la gestoría (como el «Ver como Óscar» del vídeo de referencia).
- Registro de actividad: quién creó, modificó o borró qué y cuándo.

## 5. Plataformas: web, escritorio y móvil

- **Una sola app web en la nube** que se instala como **PWA**:
  - **Windows/Mac:** desde Chrome o Edge («Instalar app»). Se abre en su propia ventana y queda en el escritorio y la barra de tareas.
  - **Android:** desde Chrome («Añadir a pantalla de inicio» o «Instalar»).
  - **iPhone:** desde Safari («Compartir → Añadir a pantalla de inicio»).
- **Notificaciones push** en ordenador y móvil para las alertas importantes. En iPhone funcionan con la app instalada en la pantalla de inicio; verifica la versión mínima de iOS y documéntala.
- **Diseño móvil de verdad**, no una versión encogida del escritorio: navegación inferior en el móvil, barra lateral en el escritorio y formularios con botones grandes.
- **Modo sin conexión para operarios:** el parte de trabajo (fotos, horas, kg de hielo, firma) se guarda en el móvil y se sincroniza al recuperar cobertura. En naves industriales suele no haber señal.
- Acceso a la **cámara** para fotos de partes, tickets de gasto y documentos.
- **Opcional, solo si lo pido (Fase 8):** apps de tienda con Capacitor (App Store y Google Play; avísame del coste de las cuentas de desarrollador) e instalador de escritorio con Tauri. Hasta entonces, la PWA basta.

## 6. Arquitectura con coste mínimo

Propuesta por defecto. En la Fase 0 compruébala y, si ves algo mejor para nuestro caso, propónlo con pros, contras y coste mensual real **antes** de cambiarla.

- **Frontend:** React + Vite + TypeScript + Tailwind CSS + shadcn/ui, como SPA/PWA. Gráficos con Recharts. Calendario con una librería madura (por ejemplo FullCalendar).
- **Backend:** Supabase en **región UE**: Postgres, Auth (2FA), Row Level Security, Edge Functions para toda lógica con secretos (Microsoft Graph, WooCommerce, IA), y `pg_cron` para tareas programadas.
- **Hosting del frontend:** un plan gratuito que **permita uso comercial** (por ejemplo Cloudflare Pages). Vercel Hobby no permite uso comercial. Verifica las condiciones actuales.
- **Archivos:** en **nuestro OneDrive/SharePoint** de Microsoft 365, a través de Microsoft Graph. Ya lo pagamos, los documentos siguen siendo nuestros y no gastamos el almacenamiento limitado de Supabase. En Supabase, solo metadatos y miniaturas.
- **Dominio:** `panel.indunova.es`. Los DNS están en Arsys; guíame para crear el registro.
- **Límites del plan gratuito de Supabase:** verifica tamaño de base de datos, pausa por inactividad y ausencia de copias automáticas, y documenta cómo nos afectan. Implementa una **copia nocturna propia**: GitHub Actions hace un `pg_dump` cifrado y lo sube a una carpeta de OneDrive, con instrucciones de restauración probadas. Dime cuándo convendría pasar a un plan de pago y cuánto costaría.
- **Repositorio privado en GitHub** desde el primer día, con commits pequeños y descriptivos.
- **Objetivo de coste:** 0 € de infraestructura más el consumo de IA (sección 10), con un límite mensual de gasto configurado. Haz una estimación mensual realista.
- Convenciones: código en inglés, interfaz en **castellano**, formato es-ES, euros, fechas dd/mm/aaaa, zona horaria Europe/Madrid. Importes en base imponible por defecto, con opción de verlos con IVA.

## 7. Integraciones

### 7.1 Microsoft 365 (Microsoft Graph)

- Guíame para **registrar la aplicación en Microsoft Entra ID** con la cuenta de administrador de Microsoft 365 (gratis).
- **Buzones:** info@indunova.es, info@dryicepack.es e info@cryocar.es, más aplanes@ y bplanes@ si los activo en Ajustes.
- Restringe el acceso de la app **solo a esos buzones** con el control de acceso de aplicaciones de Exchange Online (RBAC for Applications o el mecanismo vigente; verifícalo). Usa los permisos mínimos.
- **Correo:** leer, crear **borradores** en Outlook y enviar solo tras un clic, desde el buzón de la unidad correspondiente. Notificaciones de cambio de Graph con sondeo de respaldo cada pocos minutos.
- **Calendario:** el panel es la fuente de verdad de trabajos y entregas, y los publica en un calendario compartido de Outlook (con categorías de color por unidad) para verlos en el móvil. Las reuniones de Outlook se leen y aparecen en el calendario del panel.
- **OneDrive/SharePoint:** estructura automática de carpetas (`/INDUNOVA Control/{Unidad}/{Cliente}/{Trabajo o Pedido}/…`). Lectura de las carpetas donde guardo los PDF de a3. La ruta es configurable en Ajustes; pregúntame en la Fase 0 cómo las tengo organizadas.
- Guarda solo metadatos y resúmenes de los correos; el cuerpo completo se pide a Graph cuando se abre.

### 7.2 a3 (facturas emitidas)

- Por defecto, el panel **vigila las carpetas de OneDrive** donde guardo los PDF de a3. Cada PDF nuevo se lee con IA (número, fecha, cliente, NIF, conceptos resumidos, base, IVA, retención si la hay, total, vencimiento, forma de pago) y se proponen la unidad y el trabajo o pedido al que pertenece. Yo confirmo con un clic. Detecta duplicados por número de factura.
- En la Fase 0, investiga si a3 permite **exportar facturas a Excel/CSV** o tiene **API**. Si es así, añádelo como segundo adaptador, sin quitar el de PDF.
- Para facilitarme el trabajo en a3: en cada trabajo o pedido pendiente de facturar, una ficha **«Datos para a3»** con todo listo para copiar (cliente, NIF, dirección, conceptos, cantidades, precios). Si a3 permite importar facturas desde Excel, genera ese archivo.

### 7.3 WooCommerce (dryicepack.es)

- API REST con clave **de solo lectura**, más un webhook de pedido nuevo hacia una Edge Function (con sondeo de respaldo).
- Los pedidos web entran solos en Pedidos, con datos de cliente y de facturación. Se cruza el cliente por email o NIF.
- No escribas nunca en WooCommerce.

### 7.4 Banco Sabadell (sin conexión)

- Importación manual de extractos descargados de Sabadell: **Norma 43**, Excel o CSV. Comprueba los formatos que ofrece Sabadell para empresas.
- Conciliación automática con facturas por importe, fecha, referencia y nombre, más conciliación manual cómoda.

### 7.5 Migración desde Odoo (plan Standard, sin API)

- Importador de las **exportaciones de Odoo** (Excel/CSV desde las vistas de lista): clientes y contactos, productos y servicios, facturas registradas, proveedores y pagos. Incluye previsualización, mapeo de columnas, validación y detección de duplicados.
- Los PDF de facturas ya están en OneDrive y se vinculan con el importador de la sección 7.2.
- Antes de cancelar Odoo, guíame para descargar todo lo que haga falta conservar (exportaciones completas y la copia de base de datos si el plan lo permite). Las facturas deben conservarse durante años; confirma el plazo legal con fuentes oficiales.

## 8. Barra lateral: menú completo

Barra lateral a la izquierda (inspirada en el panel de referencia «Mission Control»): logo y nombre arriba, grupos plegables y, abajo, el usuario con su foto, su rol y el botón de salir. En el móvil se convierte en una barra inferior con 4–5 accesos y un botón «Más». Cada rol ve solo lo suyo. Los módulos aún no construidos aparecen como «Próximamente».

Arriba del todo, siempre visibles: **selector de unidad** (Todas / INDUNOVA / Dryicepack / Cryocar), **selector de periodo**, **buscador global** (clientes, trabajos, pedidos, facturas, documentos, correos), botón **«+ Nuevo»** (presupuesto, trabajo, pedido, gasto, tarea, lead) y **estado de las conexiones** (Outlook, Calendario, OneDrive, WooCommerce, IA) con la hora de la última actualización.

```
INDUNOVA Control
─────────────────────────
Hoy                      ← inicio («Buenos días, …»)
Tareas
Asistente IA

COMERCIAL
  Bandeja de entrada
  Leads
  Presupuestos
  Clientes
  Formularios

OPERACIÓN
  Calendario
  Trabajos
  Pedidos Dryicepack
  Hielo seco
  Acreditaciones y CAE
  Flota y maquinaria

FINANZAS
  Facturación
  Cobros y banco
  Gastos
  Contratos
  Gestoría

ANÁLISIS
  Analytics
  Proyectos

SISTEMA
  Documentos
  Memoria
  Equipo
  Ajustes y datos
─────────────────────────
[foto] Nombre · Rol     Salir
```

### 8.1 Hoy (inicio)

- Saludo con nombre y fecha.
- **Fila de KPIs, grande:**
  - Facturado este mes vs objetivo, con % y comparación con el mismo mes del año anterior.
  - Facturado en el año vs objetivo.
  - Pendiente de cobro, con lo vencido en rojo.
  - **Pendiente de facturar:** trabajos terminados y pedidos entregados sin factura.
  - Trabajos esta semana.
  - Correos sin contestar.
  - Días de stock de hielo seco.
  - Por unidad y total, según el selector. Objetivos editables en Ajustes.
- **Plan del día**, generado cada mañana a las 05:00 y recalculado al abrir. Sale de las alertas de la sección 9, de los compromisos detectados y de la agenda. Cada línea lleva etiqueta (Correo, Presupuesto, Planificación, CAE, Cobro, Pedido, Compromiso), color de unidad, motivo y acción directa. Se puede marcar como hecho, posponer o convertir en tarea.
- **Agenda de hoy y mañana:** trabajos, entregas, recogidas y reuniones.
- **Preparación de reuniones y visitas:** para cada cita, los últimos correos con ese cliente, el último presupuesto, los compromisos abiertos, notas y unos puntos a tratar generados por IA.
- **Compromisos abiertos:** lo prometido a clientes en correos (con fecha y estado).
- **Lo que tienes abierto:** contadores con enlace (presupuestos sin respuesta, cuestionarios sin responder, leads sin mover, pedidos por preparar).
- **Desde ayer:** actividad relevante.
- **Notas rápidas.**
- **Accesos grandes para el gerente:** «Nuevo presupuesto», «Planificar trabajo», «Ver semana» y «Lo que falta».
- Para operarios, la misma página muestra sus trabajos de hoy y mañana, con dirección, hotel y documentos.

### 8.2 Tareas

- Tareas personales y compartidas: responsable, fecha, prioridad, unidad, vínculo a cliente, trabajo o pedido, comentarios, adjuntos, checklist y recurrencia.
- Vistas: «Lo mío», «Hoy», «Esta semana», «Por unidad» y «Atrasadas».
- Botón **«Pedir algo a…»** para pedir algo a otra persona (por ejemplo, un operario al gerente), con conversación dentro de la tarea.
- **Registro de decisiones:** qué se decidió, cuándo, quién y por qué.

### 8.3 Asistente IA

Chat en castellano con acceso de **solo lectura** a los datos del panel, según el rol de quien pregunta. Ejemplos: «¿Cuánto hielo llevó TMB en marzo?», «¿Qué clientes de Dryicepack no piden desde hace un mes?». Puede **crear borradores** (correo, presupuesto, tarea) que el usuario revisa. Usa la Memoria (sección 8.22) como contexto.

### 8.4 Bandeja de entrada

- Los tres buzones info@ (y los personales, si los activo) en una sola bandeja, con el color de su unidad.
- La IA clasifica cada correo: solicitud de presupuesto, pedido, consulta, cliente existente, proveedor o factura recibida, CAE o plataforma, administración o gestoría, publicidad. También lo vincula al cliente y resume el hilo.
- Indicador **«Sin contestar desde…»** y filtro de pendientes.
- Acciones de un clic: crear lead, presupuesto, pedido, gasto (con el adjunto) o tarea; responder con borrador de IA (que se crea en Outlook y se envía tras revisarlo).
- Detecta compromisos («os lo envío el jueves») y los lleva a Compromisos.

### 8.5 Leads

- Kanban: Nuevo → Contactado → Visita/Reunión → Presupuesto enviado → Ganado / Perdido / No interesa.
- Origen: formulario web, correo, teléfono, WhatsApp o recomendación. Incluye unidad, valor estimado, próximo paso y fecha.
- Se crean solos desde la Bandeja (los formularios de las tres webs llegan por correo).
- Aviso de leads parados más tiempo del habitual.

### 8.6 Presupuestos

- Creación con un **catálogo editable de servicios y productos**:
  - INDUNOVA: horas, m², kg de hielo, desplazamiento, dietas, hotel.
  - Dryicepack: kg, formato (pellets o bloques), cajas, envío.
  - Cryocar: catálogo de servicios con precio.
- Cálculo de **coste previsto y margen**.
- PDF con la marca de la unidad, guardado en OneDrive y **enviado desde el buzón de esa unidad** tras un clic.
- Estados: borrador, enviado, aceptado, rechazado, caducado. Versiones y fecha de validez.
- Seguimiento automático: sin respuesta tras N días (configurable), aparece en el Plan del día con un borrador de recordatorio.
- Aceptación opcional por enlace (el cliente pone su nombre y acepta).
- **Al aceptar, se crea el Trabajo y el panel exige ponerle fecha.** Si se queda sin fecha, sale como alerta.
- Es un presupuesto, no una factura: sin numeración fiscal.

### 8.7 Clientes

- Ficha completa:
  - Datos fiscales, contactos y unidades con las que trabaja.
  - Facturado, pendiente y vencido.
  - **Puntuación de cliente** (puntualidad de pago, volumen, frecuencia y margen), con explicación de cómo se calcula.
  - Condiciones: forma de pago, facturación mensual o por pedido, mandato SEPA.
  - **Requisitos de acceso y CAE** (plataforma que usa y cursos que exige).
- Pestañas: correos recientes, presupuestos, trabajos, pedidos, facturas, cobros, contratos, documentos, notas y compromisos.
- Lista con búsqueda, filtros por unidad, estado y puntuación, y exportación.

### 8.8 Formularios

- Formularios públicos por enlace, con protección antispam:
  - **Alta de cliente B2B** (datos fiscales y datos del mandato SEPA).
  - **Solicitud de servicio o visita técnica** (superficie, tipo de suciedad, accesos, horarios, electricidad o compresor disponible, requisitos de seguridad y CAE).
  - **Encuesta de satisfacción** tras el trabajo.
- Las respuestas se guardan en la ficha del cliente o del lead. Se ve quién no ha respondido y se le puede enviar un recordatorio.

### 8.9 Calendario

- **Calendario conjunto** de las tres unidades con sus colores: INDUNOVA en amarillo, Dryicepack en azul claro y Cryocar en azul oscuro.
- Vistas: mes, semana, día, lista y **por recurso** (operarios, vehículos, máquinas y el elevador de Cryocar).
- Muestra: trabajos, entregas y recogidas de Dryicepack (incluidas las recurrentes), visitas, reuniones de Outlook, caducidades de CAE, ITV y revisiones.
- Arrastrar y soltar para replanificar.
- **Avisos de conflicto:** operario o vehículo en dos sitios, operario sin acreditación válida para ese cliente, elevador ocupado, capacidad de Cryocar superada.
- Sincronizado con Outlook (sección 7.1).

### 8.10 Trabajos (órdenes de trabajo de INDUNOVA y Cryocar)

- Estados: Aceptado (sin fecha) → Planificado → En preparación → En curso → Terminado → Facturado → Cobrado.
- **Checklist de preparación**, con semáforo:
  - Fechas, operarios, vehículo y máquinas.
  - Hielo seco necesario (reserva o pedido a SOL).
  - **Acreditaciones CAE válidas** para cada operario y cliente, y **permiso de trabajo aceptado**.
  - **Hotel:** nombre, dirección, fechas, número de reserva, coste y enlace. El panel no reserva; solo registra.
  - **Transporte:** furgoneta, tren o avión, con horarios.
  - Dietas, EPIs y documentación que hay que llevar.
- **Orden de trabajo en PDF** para los operarios: dirección, contacto en planta, horario, instrucciones, riesgos, hotel y documentos.
- **Parte de trabajo desde el móvil:** horas, kg de hielo usados, fotos de antes y después, incidencias, firma del cliente y conformidad. Funciona sin conexión.
- **Costes reales frente a presupuesto:** horas, hielo, hotel, desplazamiento y gastos vinculados. Da el margen real del trabajo.
- Al terminar, pasa a **Pendiente de facturar** con su ficha «Datos para a3». Los clientes con facturación mensual (como TMB) se agrupan a final de mes.
- **TMB:** servicio recurrente diario con órdenes generadas automáticamente y registro de kg por día.

### 8.11 Pedidos Dryicepack

- Canales:
  - Web (automático desde WooCommerce).
  - Correo (borrador creado por IA desde info@dryicepack.es).
  - Teléfono o WhatsApp (alta rápida).
  - **Recurrentes** (programación semanal o mensual que genera los pedidos).
  - **Venta en la nave** (alta en 20 segundos, cobro en efectivo).
- Datos: cliente, kg, formato (tamaño de pellet o bloque), cajas EPS, tipo de entrega (MRW con número de seguimiento, reparto propio o recogida), fecha, estado (nuevo, preparado, enviado, entregado, incidencia), forma de pago y tipo de facturación (inmediata, mensual agrupada o ya facturada).
- **Incidencias MRW:** tipo, coste, reclamación y estado.
- **Hoja de reparto propio** por día, ordenada por zona.
- Fin de mes: lista de **facturación mensual agrupada** por cliente, con «Datos para a3».

### 8.12 Hielo seco

- **Stock:**
  - Entradas: compras a SOL (fecha, kg, €/kg, transporte).
  - Salidas por destino: trabajos de INDUNOVA, TMB, pedidos de Dryicepack y Cryocar.
  - **Sublimación estimada**, con % diario configurable y ajustes por pesaje.
- **Previsión semanal:** kg necesarios según trabajos planificados, TMB y pedidos recurrentes, frente al stock. Alerta para pedir a SOL con antelación.
- Evolución del coste por kg y consumo real frente a la previsión anual (45 / 120 / 35 t, editables).
- **Calculadora de la peletizadora:**
  - Supuestos: inversión, precio del CO₂ líquido, rendimiento CO₂ → hielo, energía, personal, mantenimiento y financiación.
  - Resultados: coste €/kg propio frente a comprado, ahorro anual, payback, VAN a 5 años y precio de CO₂ de equilibrio.
  - Usa el consumo real de los últimos 12 meses. Los escenarios se guardan y se comparan.

### 8.13 Acreditaciones y CAE

Es el dolor de cabeza del gerente; tiene que ser lo más fácil posible.

- **Documentación de empresa** con caducidad:
  - Seguro de responsabilidad civil y recibo de pago.
  - Certificados de estar al corriente con la AEAT y la Seguridad Social.
  - Documentos de cotización de trabajadores.
  - Plan de prevención, evaluación de riesgos y modalidad preventiva.
  - Lo que se añada.
- **Por trabajador:**
  - Cursos de PRL (básico y específicos como espacios confinados, altura, plataformas elevadoras o carretilla, configurables).
  - Formación e información de riesgos y entrega de EPIs.
  - **Aptitud médica: solo fecha y apto/no apto.** Nunca diagnósticos ni datos de salud.
  - Documento de identidad, con caducidad.
- **Por cliente:** qué documentación exige, en qué **plataforma CAE** la gestiona (y un enlace) y el **estado de validación de cada documento y trabajador** (pendiente, subido, validado, rechazado, caducado). También los **permisos de trabajo** de cada trabajo.
- **No guardes contraseñas de las plataformas**; solo enlaces.
- **Matriz de semáforo** trabajador × cliente.
- Alertas a 60, 30 y 15 días. Al planificar, aviso si alguien no está acreditado.
- **Alta en segundos:** subes el PDF o la foto y la IA extrae el tipo, el trabajador, la fecha de emisión y la caducidad.
- Plan de renovación de cursos, con proveedor y coste.

### 8.14 Flota y maquinaria

- Vehículos: ITV, seguro, mantenimiento, kilómetros y costes.
- Máquinas de limpieza criogénica, compresores y **elevador de Cryocar**: revisiones, horas de uso y averías.
- Reservas visibles en el calendario y alertas de vencimientos.

### 8.15 Facturación

- Facturas emitidas importadas de a3 (sección 7.2), con estado de cobro: pendiente, parcial, cobrada, vencida o devuelta.
- **Pendiente de facturar** (trabajos y pedidos) con «Datos para a3».
- Facturación por unidad, cliente, mes, trimestre y año, frente a objetivo y año anterior.
- Descarga de facturas individuales o por lotes (mes, trimestre, año).

### 8.16 Cobros y banco

- Importación de extractos de Sabadell y conciliación (sección 7.4).
- **Remesas SEPA:**
  - Lista de recibos que se van a domiciliar, con su mandato.
  - Si en la Fase 0 confirmamos que no los genera ya a3 ni el banco, genera el **fichero XML SEPA** (verifica el esquema y la versión que acepta Sabadell) para que yo lo suba a mano.
  - Control de devoluciones.
- **Caja de efectivo:** cobros en la nave, ingresos en banco y arqueo con descuadres.
- Previsión de tesorería sencilla: cobros esperados frente a pagos fijos.
- IBAN de clientes y mandatos **cifrados en reposo** y visibles solo para administradores.

### 8.17 Gastos

- Entrada por **foto desde el móvil**, PDF, carpeta de OneDrive o correo de proveedor detectado en la Bandeja.
- La IA extrae proveedor, NIF, fecha, base, IVA, total y categoría.
- Asignación a unidad (o reparto en %) y, si toca, a un trabajo (hotel, gasoil, peajes), para que cuente como coste del trabajo.
- **Gastos fijos y suscripciones** (alquiler, seguros, software, leasing…) frente a variables. Gráficos por mes, categoría y unidad.
- Estado «enviado a la gestoría».

### 8.18 Contratos

- Por cliente y proveedor: contratos de suministro, TMB, mandatos SEPA, seguros, alquiler, leasing y SOL.
- Fechas clave, renovación, preaviso y alertas.
- **Plantillas de contrato** con variables que generan el documento rellenado.

### 8.19 Gestoría

- Paquete por periodo (mes, trimestre o año): **ZIP** con facturas emitidas, gastos y un **Excel resumen** (emitidas y recibidas por unidad, con IVA) y la caja.
- Checklist de cierre de trimestre: todo importado, conciliado y asignado.
- Marca de «enviado». El rol `gestoria` lo descarga directamente.

### 8.20 Analytics

- Rentabilidad por unidad, cliente, tipo de servicio y trabajo.
- Facturación mensual apilada por unidad (24 meses).
- Embudo comercial: tasa de aceptación de presupuestos y tiempo medio de respuesta a correos.
- **Concentración de clientes** (lo que suman los 5 y 10 primeros).
- Previsión: presupuestos ponderados más recurrentes.
- Dryicepack: kg, ticket medio y canales.
- Cryocar: ocupación frente a capacidad.
- Fase 8: web, SEO y anuncios (Search Console, GA4, Perfil de Empresa de Google y Ads), solo lectura.

### 8.21 Proyectos

Fichas con estado, próximos pasos, documentos y decisiones: peletizadora, nave nueva, registro sanitario RGSEAA/RSIPAC, lanzamiento de Cryocar, web de Dryicepack y los que añadamos.

### 8.22 Documentos y Memoria

- **Documentos:** todo lo de cada cliente, trabajo, pedido o proyecto, guardado en OneDrive con estructura automática y buscador. Los PDF generados (presupuestos, órdenes y partes firmados) se archivan solos.
- **Memoria:** base de conocimiento de la empresa en páginas editables (tarifas, procedimientos, seguridad con hielo seco, respuestas tipo, información de cada unidad). La usan el Asistente y los borradores de la IA.

### 8.23 Equipo

Invitar usuarios, asignar rol, clientes y trabajos, «Ver como…» y actividad por persona.

### 8.24 Ajustes y datos

- Datos de la empresa: IBAN y, si aplica, identificador de acreedor SEPA.
- Unidades y colores, objetivos de facturación (mensuales y anuales por unidad), catálogo y precios, plantillas (presupuesto, orden de trabajo, correos).
- Carpetas de OneDrive, buzones activos y conexiones (estado y botón «Probar»).
- Umbrales de alertas y módulos activables (por ejemplo, registro de jornada).
- Importaciones (Odoo, CSV, extractos), exportación total, copias de seguridad, registro de actividad y **consumo de IA del mes**.

## 9. Alertas: lo que no se puede escapar

Piensa como el gerente: identifica lo que puede salir mal y avísalo a tiempo. Cada alerta debe tener umbral configurable, enlace directo a la acción y presencia en el Plan del día. Las críticas, además, llegan como notificación push.

1. Correo de cliente sin contestar tras más de 24 h laborables.
2. Presupuesto enviado sin respuesta tras N días.
3. **Presupuesto aceptado sin fecha en el calendario.**
4. Trabajo en menos de 7 días con la preparación incompleta (hotel, transporte, operarios, hielo, permisos).
5. **Operario asignado sin acreditación válida** para ese cliente, o que caduca antes de que termine el trabajo.
6. Documento o curso que caduca en 60, 30 o 15 días; permiso de trabajo pendiente de validar en la plataforma CAE.
7. Trabajo terminado sin facturar tras 3 días; a fin de mes, clientes de facturación mensual pendientes.
8. Factura vencida sin cobrar o devolución SEPA.
9. Pedido web sin preparar; incidencia MRW abierta.
10. Cliente recurrente que no pide desde hace más de lo habitual.
11. **Stock de hielo seco insuficiente** para lo planificado esa semana.
12. ITV, seguro o revisión de vehículo o máquina próxima.
13. Contrato próximo a vencer (TMB incluido).
14. PDF de a3 en OneDrive sin importar; gasto sin unidad; mes sin extracto bancario importado.
15. Caja de efectivo descuadrada.
16. Capacidad de Cryocar superada o elevador doblemente reservado.

## 10. Inteligencia artificial

- **Proveedor:** API de Anthropic (Claude). **Importante:** mi suscripción Claude Pro cubre usar Claude Code para construir esto, pero **no incluye la API**, que se paga aparte por uso desde la Console. Guíame para crear la cuenta, la clave y un **límite de gasto mensual**.
- **Modelos:**
  - Haiku para extracción y clasificación (PDF, tickets, correos, documentos CAE).
  - Sonnet para el Plan del día, la preparación de reuniones y el Asistente.
  - **Batch API** para los procesos nocturnos, que es más barata.
  - Verifica los modelos y precios vigentes y deja el modelo configurable.
- **Funciones:**
  1. Lectura automática de facturas de a3, gastos, tickets y documentos CAE.
  2. Clasificación de correos, vínculo con cliente, resumen de hilo y borradores de respuesta.
  3. Detección de compromisos en correos enviados.
  4. **Plan del día a las 05:00.**
  5. Preparación de reuniones y visitas.
  6. Asistente de consulta y redacción, con Memoria.
- **Siempre con confirmación humana** antes de guardar datos extraídos o enviar nada. Muestra el nivel de confianza y deja corregirlo.
- Envía a la IA solo los datos necesarios. Registra el consumo (tokens y € estimados) por función y por día, visible en Ajustes.
- Si la IA falla o no hay saldo, el panel sigue funcionando en modo manual.

## 11. Diseño

- **Propón tú el estilo** en la Fase 0 con 2–3 propuestas visuales (capturas o una página de muestra), para elegir. Debe ser sobrio, rápido y legible; referencia de estructura, el panel «Mission Control» de los vídeos (tarjetas limpias, KPIs grandes, barra lateral clara). Colores de unidad según la sección 2. Modo claro y oscuro.
- Escritorio y móvil diseñados por separado (sección 5).
- Tablas con búsqueda, filtros, orden y exportación a Excel/CSV.
- Estados vacíos útiles (qué falta y cómo cargarlo), nunca pantallas en blanco.
- Cada cifra clave indica su origen y frescura (por ejemplo, «a3 vía OneDrive · hace 2 h»).
- Accesibilidad: contraste AA, objetivos táctiles grandes y texto legible para el gerente.

## 12. Seguridad, privacidad y copias

- **RGPD:** datos de clientes, particulares y trabajadores. Base de datos en la UE, minimización de datos y nada personal en logs ni URLs. Añade una página de información de privacidad interna y un registro de las integraciones que tratan datos.
- Secretos solo en el servidor (Edge Functions). Nunca en el frontend ni en el repositorio. Entrega un `.env.example`.
- RLS en todas las tablas, **con tests** que prueben que un operario y la gestoría no ven lo que no deben.
- IBAN y mandatos cifrados. Nada de datos de salud. Ninguna contraseña de terceros.
- Copia nocturna a OneDrive (sección 6), con una restauración probada y documentada.
- Datos de ejemplo siempre ficticios. Nunca datos reales en el repositorio.

## 13. Fases

Cada fase termina desplegada y probada. Me dices qué has hecho, cómo probarlo, qué tengo que hacer yo y qué queda pendiente. **Después, paras.**

1. **Fase 0, descubrimiento (sin código):**
   - Verifica las suposiciones de las secciones 6, 7 y 10 (planes gratuitos, a3, Sabadell, SEPA, Graph y precios de IA).
   - Pregúntame en un solo mensaje lo imprescindible (por ejemplo, cómo tengo organizadas las carpetas de OneDrive o el logo de INDUNOVA).
   - Propón 2–3 estilos visuales.
   - Crea `PLAN.md` (arquitectura, modelo de datos, coste mensual estimado, riesgos) y `CLAUDE.md`. **Espera mi aprobación.**
2. **Fase 1, base:** proyecto, despliegue en `panel.indunova.es`, PWA instalable (Windows, Mac, Android e iPhone), autenticación por invitación con 2FA, roles y RLS, barra lateral completa (con «Próximamente»), Hoy básico, Tareas con «Pedir algo a…» y Calendario conjunto con colores. *Resultado: mi padre y yo la instalamos en PC y móvil y compartimos tareas y calendario.*
3. **Fase 2, datos reales:** conexión con Microsoft 365 (OneDrive) y Documentos; migración desde Odoo (clientes y productos); Facturación con importación de PDF de a3 por IA; KPIs reales en Hoy con objetivos.
4. **Fase 3, comercial:** Bandeja (tres buzones), Leads, Presupuestos (PDF y envío), Clientes completo y Formularios.
5. **Fase 4, operación:** Trabajos con preparación, logística y orden de trabajo; parte móvil sin conexión para operarios; Acreditaciones y CAE; Flota y maquinaria; sincronización del calendario con Outlook.
6. **Fase 5, Dryicepack y hielo:** Pedidos (WooCommerce, correo, recurrentes, nave), MRW, reparto, Hielo seco con previsión y calculadora de la peletizadora, y TMB.
7. **Fase 6, finanzas:** Cobros y banco (Norma 43, conciliación, remesas, caja), Gastos con foto, Contratos y Gestoría (rol `gestoria`). *Al acabar, checklist para cancelar Odoo sin perder nada.*
8. **Fase 7, inteligencia y análisis:** Plan del día a las 05:00, compromisos, preparación de reuniones, Asistente y Memoria, Analytics, Proyectos, notificaciones push y un resumen por correo diario o semanal.
9. **Fase 8, opcional y solo si lo pido:** analítica web y de anuncios, registro de jornada (confirmando requisitos con la gestoría), apps de tienda (Capacitor), instalador de escritorio (Tauri) y WhatsApp Business.

En todas las fases:

- Tests de toda la lógica de cálculo: KPIs, puntuación de clientes, stock y sublimación, márgenes, payback/VAN, conciliación, remesas y alertas.
- Migraciones de base de datos versionadas.
- Revisión en móvil real, no solo en el emulador.

## 14. Entregables

- Repositorio privado con `README.md` técnico, `PLAN.md`, `CLAUDE.md` y `docs/PROMPT_ORIGINAL.md`.
- **`GUIA_USUARIO.md`** en castellano y sin tecnicismos, pensada para el gerente: instalar la app en el PC y el móvil, entrar con 2FA, el día a día (presupuesto → calendario → trabajo → factura) y qué significa cada alerta.
- **`GUIA_OPERARIOS.md`** de una página: instalar la app y hacer un parte.
- **`DESPLIEGUE.md`:** desplegar, variables de entorno, restaurar una copia, añadir usuarios, renovar claves y qué hacer si se cae una integración.

## 15. Criterios de aceptación

- Mi padre y yo entramos con 2FA desde PCs y móviles distintos y vemos lo mismo al momento.
- La app se instala en Windows, Mac, Android e iPhone y se abre en su propia ventana; las notificaciones llegan.
- Un operario hace un parte sin cobertura y se sincroniza al volver la señal; no ve importes ni otros clientes.
- La gestoría solo ve y descarga lo suyo.
- La facturación por unidad de un mes de prueba cuadra con a3 (documenta el procedimiento de comprobación).
- Un correo de solicitud de presupuesto se convierte en lead, presupuesto, trabajo con fecha, pendiente de facturar, factura importada y cobro conciliado, sin escribir dos veces el mismo dato.
- Todas las alertas de la sección 9 se pueden provocar con datos de ejemplo y aparecen en el Plan del día.
- Ninguna clave ni dato real en el repositorio; RLS probado; copia nocturna restaurada al menos una vez.
- Coste mensual real documentado y dentro de lo estimado.

## 16. Qué no hacer

- **No emitir facturas fiscales** ni numeración que pueda confundirse con la de a3.
- No enviar correos ni mensajes sin un clic humano.
- No conectar con el banco ni guardar credenciales bancarias o de plataformas CAE.
- No escribir en WooCommerce ni en Odoo.
- No guardar datos de salud más allá de «apto/no apto» y la fecha.
- No añadir servicios de pago, librerías pesadas ni infraestructura extra sin justificarlo y decirme el coste.
- No avanzar de fase sin mi aprobación.
