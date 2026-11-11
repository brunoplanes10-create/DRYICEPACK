// Formularios del tema (contacto, cuenta de empresa y presupuesto de industria) enviados como un cliente, con JavaScript,
// en el WordPress de prueba (wordpress-tema, 9500). 11/11/2026: desde el 30/09 el envío sin recargar iba a
// «[object HTMLInputElement]» (el campo oculto name="action" tapaba form.action) y el cliente veía «No se ha podido
// enviar»; esta prueba lo vigila. Comprueba el aviso de «enviado» y que la solicitud llega al escritorio (Solicitudes).
// Uso: node herramientas/probar-formularios.mjs
import puppeteer from 'puppeteer-core';
import { copyFileSync, rmSync } from 'node:fs';

const BASE = 'http://127.0.0.1:9500';
const FIXTURE = 'plugin/dryicepack-tienda/zz-formularios.php';
let fallos = 0;
const ok = (cond, texto, detalle) => { if (!cond) fallos++; console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto + (!cond && detalle !== undefined ? ' → ' + JSON.stringify(detalle) : '')); return cond; };
copyFileSync('herramientas/fixtures/formularios.php', FIXTURE);
const datos = async (q) => (await fetch(`${BASE}/wp-content/plugins/dryicepack-tienda/zz-formularios.php${q}`)).json();
const PAGINAS = [['contacto', '/contacto/'], ['cuenta', '/empresas/'], ['presupuesto', '/hielo-seco-industria/']];

const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
try {
  await datos('?limpiar_ip=1');
  for (const [tipo, ruta] of PAGINAS) {
    const marca = `prueba-${tipo}-${Date.now().toString(36)}`;
    const p = await nav.newPage();
    p.on('pageerror', (e) => errores.push(`${tipo}: ${e.message}`));
    const fallidas = [];
    p.on('response', (r) => { if (r.status() >= 400 && r.request().method() === 'POST') fallidas.push(`${r.status()} ${r.url()}`); });
    await p.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    await p.goto(BASE + ruta, { waitUntil: 'load', timeout: 120000 });
    await new Promise((r) => setTimeout(r, 3500)); // el antispam pide al menos 3 s
    const rellenado = await p.evaluate((marca) => {
      const f = document.querySelector('form[data-form-dip]');
      if (!f) return null;
      for (const e of f.elements) {
        if (!e.name || e.type === 'hidden' || e.closest('.dip-trampa')) continue;
        if (e.tagName === 'SELECT') { if (e.options.length > 1) e.selectedIndex = 1; continue; }
        if (e.type === 'checkbox') { if (e.name === 'privacidad') e.checked = true; continue; }
        if (e.type === 'radio') { if (!f.querySelector(`[name="${e.name}"]:checked`)) e.checked = true; continue; }
        const valor = { email: 'formulario@example.com', tel: '600123456', number: '50', date: '2026-12-15' }[e.type]
          ?? (e.tagName === 'TEXTAREA' ? `Mensaje de prueba ${marca}` : { nif: 'B66800103', cp: '08013', kg: '50', empresa: `Empresa ${marca}`, nombre: `Nombre ${marca}` }[e.name] ?? 'Prueba');
        e.value = valor;
      }
      return { campos: [...f.elements].filter((e) => e.name && e.type !== 'hidden').map((e) => e.name), valido: f.checkValidity() };
    }, marca);
    if (!ok(rellenado && rellenado.valido, `${tipo}: formulario en ${ruta}, relleno y válido`, rellenado)) { await p.close(); continue; }
    await p.evaluate(() => document.querySelector('form[data-form-dip]').requestSubmit()); // lo mismo que pulsar el botón
    await p.waitForFunction(() => /aviso-form--(ok|error)/.test(document.querySelector('form[data-form-dip] [data-form-aviso]')?.className || ''), { timeout: 60000 }).catch(() => null);
    const aviso = await p.evaluate(() => { const a = document.querySelector('form[data-form-dip] [data-form-aviso]'); return a ? { clase: a.className, texto: a.textContent.trim() } : null; });
    ok(aviso && /aviso-form--ok/.test(aviso.clase), `${tipo}: el cliente ve que se ha enviado («${aviso && aviso.texto}»)`, { aviso, fallidas });
    const s = await datos(`?marca=${encodeURIComponent(marca)}`);
    ok(s && s.tipo === tipo, `${tipo}: la solicitud llega a Dryicepack → Solicitudes`, s);
    await p.close();
  }
} finally {
  rmSync(FIXTURE, { force: true });
  await nav.close();
}
ok(!errores.length, 'sin errores de JS', errores);
console.log(fallos ? `\n${fallos} fallo(s) en probar-formularios` : '\nprobar-formularios: todo correcto');
process.exit(fallos ? 1 : 0);
