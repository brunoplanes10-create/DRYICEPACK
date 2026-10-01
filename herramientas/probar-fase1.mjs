// Prueba de los cambios del 01/10/2026 en el WordPress de prueba (puerto 9500):
// - recogida de lunes a sábado con suplemento el sábado
// - más de 150 kg fuera de la provincia de Barcelona: sin envío online y aviso con contacto
// - precio del pack de 15 kg y guías en tres idiomas
// Uso: node herramientas/probar-fase1.mjs
import puppeteer from 'puppeteer-core';
const BASE = 'http://127.0.0.1:9500';
const nav = await puppeteer.launch({ executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe', headless: true });
const errores = [];
const ok = (cond, texto) => console.log((cond ? 'OK   ' : 'FALLO') + ' ' + texto);

const prod = (await (await fetch(BASE + '/wp-json/wc/store/v1/products?slug=hielo-seco')).json())[0];
const variacion = (peso, formato) => prod.variations.find((x) => x.attributes.some((a) => a.value === peso) && x.attributes.some((a) => a.value === formato));

async function checkout(peso, cantidad, cp, ciudad) {
  const ctx = await nav.createBrowserContext();
  const p = await ctx.newPage();
  p.on('pageerror', (e) => errores.push(e.message));
  await p.setViewport({ width: 1280, height: 900 });
  const v = variacion(peso, '3mm');
  await p.goto(`${BASE}/?add-to-cart=${prod.id}&variation_id=${v.id}&attribute_pa_peso=${peso}&attribute_pa_formato=3mm&quantity=${cantidad}`, { waitUntil: 'networkidle2', timeout: 120000 });
  await p.goto(BASE + '/checkout/', { waitUntil: 'networkidle2', timeout: 120000 });
  const esperar = () => p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 90000 }).catch(() => null);
  await p.evaluate((cp, ciudad) => {
    const f = (id, v) => { const e = document.getElementById(id); if (e) { e.value = v; e.dispatchEvent(new Event('change', { bubbles: true })); } };
    f('billing_postcode', cp); f('billing_city', ciudad);
    jQuery(document.body).trigger('update_checkout');
  }, cp, ciudad);
  await esperar(); await new Promise((r) => setTimeout(r, 1500));
  return { p, ctx };
}
const leer = (p) => p.evaluate(() => ({
  metodos: [...document.querySelectorAll('.dip-metodo')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  fechas: [...document.querySelectorAll('.dip-fecha')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
  nota: (document.querySelector('.dip-fechas__nota') || {}).textContent || '',
  medida: (document.querySelector('.dip-aviso--medida') || {}).textContent || '',
  resumen: [...document.querySelectorAll('.woocommerce-checkout-review-order-table tr')].map((m) => m.textContent.trim().replace(/\s+/g, ' ')),
}));

// 1. Recogida: lunes a sábado, sábado con suplemento
{
  const { p, ctx } = await checkout('10kg-de-hielo-seco', 1, '08304', 'Mataró');
  const recoger = await p.$('.dip-metodo--recogida input');
  const esperar = p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 90000 }).catch(() => null);
  await recoger.click(); await esperar; await new Promise((r) => setTimeout(r, 1500));
  let d = await leer(p);
  console.log('Recogida, fechas:', d.fechas.join(' | '));
  ok(d.fechas.some((f) => /sáb|sab/i.test(f) && /11,74/.test(f)), 'la recogida ofrece sábado con +11,74 €');
  ok(!d.fechas.some((f) => /dom/i.test(f)), 'la recogida no ofrece domingo');
  ok(/confirmar la hora/i.test(d.nota), 'nota de confirmación de la hora');
  // Elegir el sábado y ver el suplemento en el resumen
  const sab = await p.$$eval('.dip-fecha', (ls) => ls.findIndex((l) => /sáb/i.test(l.textContent)));
  if (sab >= 0) {
    const esp = p.waitForResponse((r) => r.url().includes('update_order_review'), { timeout: 90000 }).catch(() => null);
    await p.evaluate((i) => document.querySelectorAll('.dip-fecha input')[i].click(), sab); await esp; await new Promise((r) => setTimeout(r, 1500));
    d = await leer(p);
    ok(d.resumen.some((t) => /Recogida en sábado/.test(t)), 'suplemento "Recogida en sábado" en el resumen: ' + (d.resumen.find((t) => /sábado/.test(t)) || 'no aparece'));
  }
  await ctx.close();
}

// 2. 160 kg a Madrid: solo recogida + aviso; a Barcelona: envío
{
  const { p, ctx } = await checkout('20kg-de-hielo-seco', 8, '28001', 'Madrid');
  const d = await leer(p);
  console.log('160 kg Madrid, métodos:', d.metodos.join(' | '));
  ok(d.metodos.length === 1 && /Recoger/.test(d.metodos[0]), 'a Madrid con 160 kg solo queda la recogida');
  ok(/150 kg/.test(d.medida) && /WhatsApp/.test(d.medida), 'aviso de más de 150 kg con WhatsApp');
  await ctx.close();
}
{
  const { p, ctx } = await checkout('20kg-de-hielo-seco', 8, '08001', 'Barcelona');
  const d = await leer(p);
  ok(d.metodos.some((m) => /mensajer/i.test(m)) && !d.medida, '160 kg a Barcelona: envío disponible y sin aviso');
  await ctx.close();
}
{
  const { p, ctx } = await checkout('20kg-de-hielo-seco', 7, '28001', 'Madrid');
  const d = await leer(p);
  ok(d.metodos.some((m) => /mensajer/i.test(m)), '140 kg a Madrid: envío disponible');
  await ctx.close();
}

// 3. Precio de 15 kg y guías
const ficha = await (await fetch(BASE + '/hielo-seco-halloween/')).text();
ok(/15 kg<\/b> 91,84/.test(ficha), 'Halloween muestra 15 kg a 91,84 € IVA incl.');
for (const u of ['/guias/', '/ca/guies/', '/en/guides/', '/guias/como-hacer-niebla-con-hielo-seco/', '/ca/guies/com-fer-boira-amb-gel-sec/', '/en/guides/how-to-make-fog-with-dry-ice/', '/guias/donde-comprar-hielo-seco/', '/ca/guies/on-comprar-gel-sec/', '/en/guides/where-to-buy-dry-ice/']) {
  const r = await fetch(BASE + u); const h = await r.text();
  const t = (h.match(/<title>([^<]*)/) || [])[1] || '';
  const hl = (h.match(/hreflang=/g) || []).length;
  const enlaces = (h.match(/class="envoltura gu-seguir"[\s\S]*?<\/nav>/) || [''])[0].match(/<a /g)?.length || 0;
  ok(r.status === 200 && /<h1/.test(h), `${u} ${r.status} · ${t} · hreflang ${hl}${u.split('/').length > 4 ? ' · enlaces "para seguir" ' + enlaces : ''}`);
}
console.log(errores.length ? 'Errores JS: ' + errores.join(' | ') : 'Sin errores de JS');
await nav.close();
