// Step 1 of 2: reads each screenshots/v2/src/NN-*.html in Edge and dumps its markup + CSS as JSON.
// See scripts/make-svg-screens.py for step 2 and the full usage.
import { chromium } from 'playwright-core';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';
const SRC = fileURLToPath(new URL('../screenshots/v2/src/', import.meta.url)).split(String.fromCharCode(92)).join('/');
const OUT = process.argv[2];
const browser = await chromium.launch({ channel: 'msedge' });
const names = fs.readdirSync(SRC).filter(f => /^\d\d-.*\.html$/.test(f));
const result = {};
for (const f of names) {
  const p = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await p.route('**/*', r => {
    const u = r.request().url();
    if (u.startsWith('file:')) return r.continue();
    return r.abort();
  });
  await p.goto('file:///' + SRC + f);
  await p.waitForTimeout(300);
  const data = await p.evaluate(async () => {
    let css = '';
    for (const s of document.styleSheets) {
      try { for (const r of s.cssRules) { if (r.type !== CSSRule.IMPORT_RULE) css += r.cssText + '\n'; } } catch (e) { /* skip */ }
    }
    const ser = new XMLSerializer();
    let html = '';
    for (const n of document.body.childNodes) {
      if (n.nodeType === 1 && n.tagName === 'SCRIPT') continue;
      html += ser.serializeToString(n);
    }
    return { css, html, text: document.body.innerText };
  });
  result[f.replace('.html', '')] = data;
  await p.close();
}
fs.writeFileSync(OUT, JSON.stringify(result));
console.log(Object.keys(result).map(k => k + ': css ' + result[k].css.length + ' html ' + result[k].html.length).join('\n'));
await browser.close();
