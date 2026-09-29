// Traza de rendimiento (móvil, CPU x4) y resumen de qué ocupa el hilo principal.
// Uso: node herramientas/traza.mjs [url]
import puppeteer from 'puppeteer-core';
import { readFileSync, mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

const url = process.argv[2] || 'http://127.0.0.1:9400/hielo-seco-halloween/';
mkdirSync('.tmp', { recursive: true });
const navegador = await puppeteer.launch({
  executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true,
  userDataDir: resolve('.tmp/chrome-perfil'), args: ['--no-first-run'],
});
const p = await navegador.newPage();
await p.setViewport({ width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true });
const cdp = await p.createCDPSession();
await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });
await p.tracing.start({ path: '.tmp/traza.json', categories: ['devtools.timeline', 'disabled-by-default-devtools.timeline'] });
await p.goto(url, { waitUntil: 'load' });
await new Promise((r) => setTimeout(r, 4000));
await p.tracing.stop();
await navegador.close();

const eventos = JSON.parse(readFileSync('.tmp/traza.json', 'utf8')).traceEvents;
const hilo = eventos.find((e) => e.name === 'thread_name' && e.args?.name === 'CrRendererMain');
const tareas = eventos.filter((e) => e.tid === hilo?.tid && e.ph === 'X' && e.name === 'RunTask' && e.dur > 50000);
console.log('Tareas largas (>50 ms):', tareas.length);
for (const t of tareas) {
  const dentro = eventos.filter((e) => e.tid === t.tid && e.ph === 'X' && e.ts >= t.ts && e.ts + (e.dur || 0) <= t.ts + t.dur && e.name !== 'RunTask');
  const suma = {};
  for (const e of dentro) suma[e.name] = (suma[e.name] || 0) + e.dur;
  const top = Object.entries(suma).sort((a, b) => b[1] - a[1]).slice(0, 7).map(([n, d]) => `${n} ${(d / 1000).toFixed(0)}ms`);
  console.log(`  ${(t.dur / 1000).toFixed(0)} ms → ${top.join(' · ')}`);
  const estilos = dentro.filter((e) => e.name === 'UpdateLayoutTree' || e.name === 'Layout').map((e) => `${e.name} ${(e.dur / 1000).toFixed(0)}ms${e.args?.elementCount ? ' (' + e.args.elementCount + ' el.)' : ''}`);
  if (estilos.length) console.log('     ', estilos.slice(0, 6).join(' | '));
}
