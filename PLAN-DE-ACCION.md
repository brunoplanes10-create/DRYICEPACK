# Plan de acción — Dryicepack con Claude Code

## Fase 0 · Preparación (hoy–mañana, ~2 h)
1. Backup completo de dryicepack.es (archivos + base de datos) desde el hosting o con un plugin de backup.
2. Instala **LocalWP** y crea una copia local de la web (importa el backup).
3. Crea un **tema hijo** si no lo hay y activa la copia local con él.
4. Instala Git y Claude Code (app de escritorio o terminal). Inicia sesión.
5. Abre la carpeta del tema hijo (o la raíz del sitio local) en Claude Code y ejecuta `git init` + primer commit.
6. Copia en esa carpeta el contenido de este paquete: `CLAUDE.md` y la carpeta `.claude/`.
7. Instala Lighthouse: `npm install -g lighthouse`. Activa la vista previa del navegador o la extensión de Chrome para que Claude pueda ver la web.
8. En Claude Code: `/plugin` → busca e instala el plugin oficial **frontend-design**.

## Fase 1 · Auditoría (día 2)
Prompt: *"Usa las skills rendimiento y seo. Audita la home, la tienda, una ficha de producto y Envíos y plazos. Dame línea base de Lighthouse, los 10 problemas con más impacto ordenados y un plan. No cambies nada todavía."*
- Revisa especialmente si la web bloquea a bots (errores 503).

## Fase 2 · Halloween PRIMERO (días 3–7) — va contra reloj
Prompt (en modo plan, Shift+Tab): *"Usa la skill halloween. Crea la landing /hielo-seco-halloween/ en la copia local. Primero propón estructura y textos."*
1. Aprobar estructura → textos → diseño → animación de niebla.
2. Pasar `revisor-copy` y `auditor-rendimiento`.
3. Rellenar los `[PENDIENTE]` (cantidades, código, sábado).
4. Subir a producción, solicitar indexación en Search Console, banner en home.
5. Seguir el calendario de la skill hasta el 31 oct.

## Fase 3 · Rendimiento (semana 2)
Prompt: *"Usa la skill rendimiento. Arregla los 3 problemas de mayor impacto de la auditoría, uno a uno, midiendo antes y después."*

## Fase 4 · SEO técnico y de contenido (semanas 2–3)
1. Titles y meta descriptions de todas las páginas clave (ganar CTR).
2. Schema LocalBusiness + Product.
3. Landings locales prioritarias (Barcelona, Madrid, Valencia…).
4. Google Business Profile completo.

## Fase 5 · Identidad visual (semanas 3–5)
Prompt: *"Usa la skill diseno. Propón 3 direcciones visuales para la home con boceto. No implementes hasta que elija."*
Luego home → tienda/fichas → páginas de sector, una a una.

## Fase 6 · Copy página a página (en paralelo a la fase 5)
Cada página rediseñada se reescribe con la skill `copy` y pasa por `revisor-copy`.

## Fase 7 · Animaciones (al final)
Prompt: *"Usa la skill animacion. Añade la niebla del hero y el contador de temperatura. Verifica que Lighthouse no baja."*

## Rutina en cada tarea
Modo plan → aprobar → implementar en local → capturas móvil/escritorio → revisores → commit → subir a producción.
Cuando corrijas lo mismo dos veces, añade la regla a la skill correspondiente.
