# Instalar la página de Halloween en dryicepack.es

La página es una sección más de la web: usa la cabecera, el menú y el pie actuales.
Solo **añade archivos nuevos** al tema hijo; no modifica ninguno de los que ya existen.

## 1. Subir los archivos (Arsys → Administrador de archivos o FTP)

1. Entra en `wp-content/themes/divi-child-dryicepack/`.
2. Sube `halloween-tema-hijo.zip` (está en la carpeta `dist/` del proyecto) y descomprímelo **ahí mismo**.
   Tiene que quedar así:

   ```
   divi-child-dryicepack/
   ├── page-hielo-seco-halloween.php      ← nuevo
   └── assets/
       └── halloween/                     ← nuevo (css, js, img, fonts)
   ```

3. Borra el `.zip` del servidor.

## 2. Crear la página (WordPress → Páginas → Añadir nueva)

- **Título:** Hielo seco para Halloween
- **Enlace permanente (slug):** `hielo-seco-halloween`, exactamente así. WordPress aplica la plantilla solo por este nombre.
- **Contenido:** vacío. No abras el Divi Builder en esta página.
- Guárdala como **borrador** y pulsa **Vista previa**. Tiene que verse la cabecera normal de la web y, debajo, la página negra y naranja con las calabazas.
  - Si ves una página en blanco o la plantilla de Divi, avísame antes de publicar. Puede que el Theme Builder tenga una plantilla de cuerpo global.

**Rank Math** (en la misma página):
- **Título SEO:** `Hielo seco para Halloween | Niebla con entrega 24 h` (51 caracteres)
- **Meta descripción:** `Hielo seco en pellets de 3 mm para hacer niebla en Halloween. Pide antes de las 12:00 del jueves 29/10 y lo recibes el viernes. Caja EPS incluida.` (146 caracteres)
- **Palabra clave principal:** `hielo seco halloween`

El schema de preguntas frecuentes (FAQPage) ya lo pone la plantilla. No añadas el bloque FAQ de Rank Math, para no duplicarlo.

## 3. Añadir "Halloween" al menú (Divi → Theme Builder → cabecera → módulo de código)

El menú es HTML propio dentro de un módulo de código. Estos son los cambios. De paso, los enlaces que hoy redirigen apuntan ya a su dirección final, que es mejor para el SEO.

**Menú de escritorio**, antes:

```html
<nav class="dip-nav" aria-label="Navegación principal">
  <a class="dip-link" href="/que-es/">¿Qué es?</a>
  <a class="dip-link" href="/aplicaciones/">Aplicaciones</a>
  <a class="dip-link" href="/contacto/">Contacto</a>
</nav>
```

Después:

```html
<nav class="dip-nav" aria-label="Navegación principal">
  <a class="dip-link" href="/que-es-el-hielo-seco/">¿Qué es?</a>
  <a class="dip-link" href="/aplicaciones-del-hielo-seco/">Aplicaciones</a>
  <a class="dip-link dip-link--halloween" href="/hielo-seco-halloween/">Halloween</a>
  <a class="dip-link" href="/contacto/">Contacto</a>
</nav>
```

**Píldora "Programar suministro"** (escritorio): cambia `href="/programar-suministro/"` por `href="/programar-suministro-de-hielo-seco/"`.

**Menú móvil**, antes:

```html
<a class="m-link" href="/que-es/">¿Qué es?</a>
<a class="m-link" href="/aplicaciones/">Aplicaciones</a>
<a class="m-link" href="/contacto/">Contacto</a>
<a class="m-link m-pill m-pill--filled" href="/comprar-hielo-seco/">Comprar hielo seco</a>
<a class="m-link m-pill m-pill--outline" href="/programar-suministro/">Programar suministro</a>
```

Después:

```html
<a class="m-link" href="/que-es-el-hielo-seco/">¿Qué es?</a>
<a class="m-link" href="/aplicaciones-del-hielo-seco/">Aplicaciones</a>
<a class="m-link m-link--halloween" href="/hielo-seco-halloween/">Halloween</a>
<a class="m-link" href="/contacto/">Contacto</a>
<a class="m-link m-pill m-pill--filled" href="/producto/hielo-seco/">Comprar hielo seco</a>
<a class="m-link m-pill m-pill--outline" href="/programar-suministro-de-hielo-seco/">Programar suministro</a>
```

**Estilo del enlace:** naranja con un punto que late. Pégalo al final del mismo módulo de código:

```html
<style>
  .dip-link--halloween, .m-link--halloween { display: inline-flex; align-items: center; gap: 7px; color: #c2410c !important; }
  .dip-link--halloween::before, .m-link--halloween::before {
    content: ""; width: 7px; height: 7px; border-radius: 50%; background: #ff6b1a;
    animation: dipHwLatido 1.8s ease-out infinite;
  }
  @keyframes dipHwLatido { 0% { opacity: 1; transform: scale(1); } 70% { opacity: .3; transform: scale(1.8); } 100% { opacity: 1; transform: scale(1); } }
  @media (prefers-reduced-motion: reduce) { .dip-link--halloween::before, .m-link--halloween::before { animation: none; } }
</style>
```

El naranja del texto (`#c2410c`) es más oscuro que el de la página a propósito. Sobre la cabecera blanca tiene que cumplir el contraste mínimo de accesibilidad (5,2:1).

**Comprueba el ancho del menú.** Con un enlace más, mira la cabecera en un ordenador con la ventana entre unos 1000 y 1240 px de ancho. El menú no admite saltos de línea, así que no debe salirse ni montarse sobre las píldoras. Si pasa, avísame y reduzco la separación entre enlaces.

## 4. Caché y publicación

1. **WP Rocket:** si tienes activado "Eliminar CSS no utilizado", añade `halloween.min.css` a los archivos excluidos.
   - Si no lo haces, puede borrar estilos que solo se aplican al hacer scroll y se perderían las animaciones.
   - El script ya va marcado para que "Retrasar JavaScript" no lo retrase.
   - La foto principal va marcada para que la "Carga diferida" (LazyLoad) de WP Rocket no la retrase.
     Para comprobarlo, en el código fuente de la página publicada no debe aparecer `data-lazy-srcset` en la foto de las calabazas.
2. Publica la página y **vacía la caché** de WP Rocket y de Cloudflare.
3. Comprueba en el móvil y en el ordenador: menú, página y botones "Pedir".
   - Los botones "Pedir" llevan a la ficha de producto con el formato y el peso ya elegidos.
4. **Mide la página real** en [PageSpeed Insights](https://pagespeed.web.dev/) (móvil) y pásame el resultado.
   - En local da CLS 0, LCP 2,1 s y Rendimiento 87 de mediana (en ese entorno una página vacía ya se queda en 95–97).
   - En la web real el tiempo de respuesta del servidor (2–11 s hoy) sumará encima: lo arreglaremos en la fase de velocidad del servidor.
5. **Search Console:** Inspección de URL → `https://dryicepack.es/hielo-seco-halloween/` → Solicitar indexación.

## Después del 31/10

La cuenta atrás cambia sola:
- Del 29/10 a las 12:00 al 30/10 a las 12:00, pasa al plazo del sábado.
- Después muestra "Plazo de envío cerrado".
- Desde el 1/11 muestra "Hasta Halloween 2027".

Para 2027 bastará con cambiar las fechas al principio de la plantilla (`$hw_limite_viernes`, `$hw_limite_sabado`, `$hw_fin_campana`) y los textos que las mencionan.
