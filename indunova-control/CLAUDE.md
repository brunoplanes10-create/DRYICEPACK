# INDUNOVA Control — convenciones del proyecto

Panel de gestión interno de INDUNOVA IMS S.L. (Mataró): una PWA en la nube que reúne correo, clientes, presupuestos, calendario, trabajos, pedidos, hielo seco, acreditaciones, facturas importadas, cobros, gastos, contratos, documentos y tareas de las tres unidades (INDUNOVA, Dryicepack y Cryocar). Sustituye a Odoo como herramienta de control interno.

- Especificación completa y literal: `docs/PROMPT_ORIGINAL.md`.
- Plan, arquitectura, modelo de datos, costes y riesgos: `PLAN.md`.
- Prototipo de la app (Fase 0): `docs/estilos/propuestas.html`. Es la referencia visual y de comportamiento.
- Marca: `assets/marca/`. Guía para cancelar Odoo: `docs/CANCELAR_ODOO.md`.

Este proyecto es independiente de la web dryicepack.es. De momento vive en la carpeta `indunova-control/` del repo DRYICEPACK; pasará a su propio repositorio privado.

## Cómo trabajamos

- **Andrés no es desarrollador profesional.** Todo lo que tenga que hacer fuera del código (crear cuentas, registrar apps, DNS, claves, variables de entorno) se le explica paso a paso, como si fuera la primera vez, con capturas o rutas exactas de menú.
- **Por fases** (`PLAN.md`, sección 7). Al acabar cada fase: qué se ha hecho, cómo probarlo, qué tiene que hacer Andrés y qué queda pendiente. **Después, parar y esperar su validación.** Nunca avanzar de fase sin aprobación.
- **Presupuesto mínimo.** No añadir servicios de pago, librerías pesadas ni infraestructura extra sin justificarlo y decir el coste mensual.
- **No inventar datos de negocio.** Si falta uno, preguntarlo o dejarlo como `TODO` visible en la interfaz.
- **Comprobar antes de afirmar** cualquier cosa que pueda haber cambiado (precios, planes gratuitos, límites de API, normativa). Citar la fuente oficial y la fecha de consulta.
- **Commits pequeños**, con mensaje en castellano que diga qué cambia.
- Las decisiones que Andrés apruebe se apuntan en `PLAN.md` (sección «Decisiones»). Si chocan con el prompt original, manda la decisión aprobada más reciente.

## Reglas que no se rompen

1. **El panel no emite facturas fiscales ni proformas**, ni usa numeración que se pueda confundir con la de a3. Solo hace **presupuestos**, marcados «No es una factura» y bloqueados al enviarse (cada cambio, una versión nueva). Las facturas se **importan** de a3 y no se modifican: se conservan serie, número, fecha y el archivo original con su hash.
2. **«Datos para a3»** es una ficha para copiar a mano. Nunca alimentar a3 de forma automática sin consultarlo antes con la gestoría (por VeriFactu, el sistema que alimenta automáticamente un programa de facturación pasa a formar parte de él).
3. **Ningún correo ni mensaje sale sin un clic humano.** La IA solo prepara borradores.
4. **Sin conexión con el banco.** No se guardan credenciales bancarias ni contraseñas de plataformas CAE (solo enlaces).
5. **Nunca escribir en WooCommerce ni en Odoo.** Claves de solo lectura.
6. **Salud:** de los reconocimientos médicos solo se guarda «apto / no apto» y la fecha.
7. **Secretos solo en el servidor** (secretos de Supabase y de GitHub). Nunca en el frontend ni en el repositorio. Se entrega un `.env.example` sin valores.
8. **Datos de ejemplo siempre ficticios.** Nunca datos reales en el repositorio, ni en tests, ni en capturas.
9. **RLS en todas las tablas**, con tests que demuestren que un operario y la gestoría no ven lo que no deben.
10. **IBAN y mandatos cifrados** con una clave que no está en la base de datos. En pantalla, solo los cuatro últimos dígitos y solo para administradores.
11. **Nada personal en logs ni en URLs.** A la IA solo se le envía lo necesario.
12. **Cálculos y alertas deterministas y con tests.** La IA solo extrae, clasifica, resume, redacta y prioriza, siempre con confirmación humana y mostrando su nivel de confianza. Si la IA falla o no hay saldo, todo funciona en modo manual.

## Convenciones de código

- **Idiomas:** código, tablas, columnas, funciones y ramas en inglés. Interfaz, textos de usuario, documentación y commits en castellano.
- **Formato:** es-ES. Euros con coma decimal y punto de miles (`1.234,56 €`), fechas `dd/mm/aaaa`, zona horaria `Europe/Madrid`. Los importes se muestran en base imponible por defecto, con opción de verlos con IVA. Ojo: `Intl.NumberFormat('es-ES')` no agrupa los números de cuatro cifras (da `6980`); usar siempre `useGrouping: 'always'` para que salga `6.980`.
- **Dinero:** en la base de datos, céntimos en enteros (`bigint`), nunca coma flotante. Base, IVA, retención y total por separado.
- **Fechas:** `timestamptz` (UTC) para instantes y `date` para días sin hora. Se muestran siempre en Europe/Madrid.
- **Unidad de negocio:** enum `business_unit` (`indunova`, `dryicepack`, `cryocar`). Lo que se reparte entre varias unidades usa una tabla `*_allocations` con porcentajes que suman 100.
- **Colores de unidad** (decisión D4): tokens CSS `--unit-indunova` `#E5D61A` (texto encima `#1F1C04`), `--unit-dryicepack` `#B8D0E8` (texto `#1F3C55`) y `--unit-cryocar` `#1F3449` (texto blanco). Siempre acompañados de sus iniciales (IN, DI, CC), nunca solo color. El amarillo nunca como color de texto. En Escarcha, Dryicepack lleva un borde fino; en Grafito, Cryocar lleva un borde claro.
- **Estilos:** Escarcha (claro), Grafito (oscuro) y Automático (sigue el sistema; por defecto). Cada persona elige el suyo en Ajustes y se guarda en `user_preferences`. Todos los colores son tokens CSS: ningún color suelto en los componentes.
- **Marca:** archivos en `assets/marca/`. `indunova-logo.svg` (nombre en gris `#5A5757`) sobre fondos claros, `indunova-logo-negativo.svg` (nombre en blanco) sobre oscuros, `indunova-isotipo.svg` (amarillo) para el icono de la app y el favicon. No se redibuja ni se recolorea el logo. La animación de entrada solo usa `transform` y `opacity`, y se salta con un toque.
- **Base de datos:** migraciones SQL versionadas en `supabase/migrations/`. Nunca cambiar el esquema a mano en producción. Claves `uuid`, columnas `created_at`, `updated_at`, `created_by`. Registro de actividad por trigger.
- **Tests:** Vitest para la lógica de cálculo (KPIs, puntuación de clientes, stock y sublimación, márgenes, payback y VAN, conciliación, remesas, alertas); pgTAP para RLS y funciones SQL; Playwright para los flujos clave. Ninguna lógica de cálculo sin test.
- **Navegación por espacios** (decisión D1, `PLAN.md` 9.1): entrada animada con el logo y cuatro iconos (Global, INDUNOVA, Dryicepack, Cryocar). Dentro de cada espacio, poco y bien separado: una tarjeta con dos cifras, la acción principal y una lista corta de secciones. Nada de filas de diez cifras. Lo que se apunta en una unidad se refleja en Global, que no tiene datos propios: suma los de las tres. El prototipo de referencia es `docs/estilos/propuestas.html`.
- **Interfaz:** mobile-first; en el ordenador, el mismo esquema centrado y más amplio. Contraste AA, objetivos táctiles de 44 px como mínimo, foco visible y `prefers-reduced-motion`. Estados vacíos que digan qué falta y cómo cargarlo. Cada cifra clave indica su origen y su frescura («a3 vía OneDrive · hace 2 h»).
- **Módulos sin construir:** aparecen en el menú como «Próximamente», nunca como pantallas en blanco.
- **Personalización** (decisión D3, `PLAN.md` 2.5): todo formulario nace con **campos propios** (columna `custom` jsonb validada por trigger contra `custom_field_definitions`, con índice GIN). Las **listas de opciones** salen de `option_lists` y `option_items`, nunca fijas en el código; solo son enums de código los valores de los que dependen cálculos o permisos. Ningún cálculo, alerta ni política RLS depende de un campo propio. Quitar un campo lo oculta (`archived_at`), nunca borra valores. Un campo propio hereda los permisos de su ficha: lo confidencial va en las tablas de importes. Crear campos y editar listas es cosa de administradores; el estilo, de cada persona.

## Estructura prevista (desde la Fase 1)

```
app/          PWA: React + Vite + TypeScript + Tailwind CSS + shadcn/ui
core/         lógica de cálculo pura en TypeScript, con sus tests
supabase/     migrations/, functions/ (Edge Functions), tests/ (pgTAP), seed.sql (ficticio)
.github/      workflows: comprobaciones y copia nocturna
docs/         PROMPT_ORIGINAL.md, guías de usuario, estilos/
```
