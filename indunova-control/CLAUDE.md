# INDUNOVA Control — convenciones del proyecto

Panel de gestión interno de INDUNOVA IMS S.L. (Mataró): una PWA en la nube que reúne correo, clientes, presupuestos, calendario, trabajos, pedidos, hielo seco, acreditaciones, facturas importadas, cobros, gastos, contratos, documentos y tareas de las tres unidades (INDUNOVA, Dryicepack y Cryocar). Sustituye a Odoo como herramienta de control interno.

- Especificación completa y literal: `docs/PROMPT_ORIGINAL.md`.
- Plan, arquitectura, modelo de datos, costes y riesgos: `PLAN.md`.
- Propuestas visuales de la Fase 0: `docs/estilos/propuestas.html`.

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
- **Formato:** es-ES. Euros con coma decimal y punto de miles (`1.234,56 €`), fechas `dd/mm/aaaa`, zona horaria `Europe/Madrid`. Los importes se muestran en base imponible por defecto, con opción de verlos con IVA.
- **Dinero:** en la base de datos, céntimos en enteros (`bigint`), nunca coma flotante. Base, IVA, retención y total por separado.
- **Fechas:** `timestamptz` (UTC) para instantes y `date` para días sin hora. Se muestran siempre en Europe/Madrid.
- **Unidad de negocio:** enum `business_unit` (`indunova`, `dryicepack`, `cryocar`). Lo que se reparte entre varias unidades usa una tabla `*_allocations` con porcentajes que suman 100.
- **Colores de unidad:** tokens CSS `--unit-indunova`, `--unit-dryicepack`, `--unit-cryocar` (valores en `PLAN.md`). Siempre acompañados de sus iniciales (IN, DI, CC), nunca solo color. El amarillo nunca como color de texto. En modo oscuro, el azul Cryocar lleva un borde claro.
- **Base de datos:** migraciones SQL versionadas en `supabase/migrations/`. Nunca cambiar el esquema a mano en producción. Claves `uuid`, columnas `created_at`, `updated_at`, `created_by`. Registro de actividad por trigger.
- **Tests:** Vitest para la lógica de cálculo (KPIs, puntuación de clientes, stock y sublimación, márgenes, payback y VAN, conciliación, remesas, alertas); pgTAP para RLS y funciones SQL; Playwright para los flujos clave. Ninguna lógica de cálculo sin test.
- **Interfaz:** mobile-first, con diseño propio para móvil (barra inferior) y para escritorio (barra lateral). Contraste AA, objetivos táctiles de 44 px como mínimo, foco visible y `prefers-reduced-motion`. Estados vacíos que digan qué falta y cómo cargarlo. Cada cifra clave indica su origen y su frescura («a3 vía OneDrive · hace 2 h»).
- **Módulos sin construir:** aparecen en el menú como «Próximamente», nunca como pantallas en blanco.

## Estructura prevista (desde la Fase 1)

```
app/          PWA: React + Vite + TypeScript + Tailwind CSS + shadcn/ui
core/         lógica de cálculo pura en TypeScript, con sus tests
supabase/     migrations/, functions/ (Edge Functions), tests/ (pgTAP), seed.sql (ficticio)
.github/      workflows: comprobaciones y copia nocturna
docs/         PROMPT_ORIGINAL.md, guías de usuario, estilos/
```
