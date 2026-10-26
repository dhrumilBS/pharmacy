// Builds the design-system poster as PNG (2x) and SVG (HTML in a foreignObject, fonts and logos embedded).
// Usage: node design-system/build.mjs   (needs playwright-core in wordpress/tools and Edge)
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { createRequire } from 'node:module';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..');
const { chromium } = createRequire(path.join(root, 'wordpress/tools/'))('playwright-core');

const b64 = (f) => fs.readFileSync(f).toString('base64');
const weights = [[300, '', 300], [400, '', 400], [400, 'i', 400], [500, '', 500], [600, '', 600], [700, '', 700], [800, '', 800]];
const face = weights.map(([w, i]) => {
  const file = path.join(root, `assets/fonts/campton/campton-${w}${i}.woff2`);
  return `@font-face{font-family:"Campton";src:url(data:font/woff2;base64,${b64(file)}) format("woff2");font-weight:${w};font-style:${i ? 'italic' : 'normal'}}`;
}).join('\n');
const logo = 'data:image/svg+xml;base64,' + b64(path.join(root, 'assets/img/healthray-logo.svg'));
const logoDark = 'data:image/svg+xml;base64,' + b64(path.join(root, 'assets/img/healthray-logo-dark.svg'));

const html = fs.readFileSync(path.join(here, 'design-system.html'), 'utf8')
  .replace('/*FONTFACE*/', face).replaceAll('"LOGO_DARK"', `"${logoDark}"`).replaceAll('"LOGO"', `"${logo}"`);
const built = path.join(here, 'design-system.built.html');
fs.writeFileSync(built, html);

const browser = await chromium.launch({ channel: 'msedge' });
const page = await browser.newPage({ viewport: { width: 1600, height: 900 }, deviceScaleFactor: 2 });
await page.goto(pathToFileURL(built).href);
await page.evaluate(() => document.fonts.ready);
const height = await page.evaluate(() => document.documentElement.scrollHeight);
await page.screenshot({ path: path.join(here, 'healthray-design-system.png'), fullPage: true });

// SVG: the page body (without <html>/<head> wrappers) inside a foreignObject.
const style = html.match(/<style>([\s\S]*?)<\/style>/)[1];
const body = html.match(/<body>([\s\S]*)<\/body>/)[1].replace(/<br>/g, '<br/>').replace(/<img([^>]*?)>/g, '<img$1/>');
const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="${height}" viewBox="0 0 1600 ${height}"><foreignObject width="1600" height="${height}"><div xmlns="http://www.w3.org/1999/xhtml" class="ds"><style>${style.replace(/body\{/g, '.ds{').replace(/\*\{/, '.ds *{')}</style>${body}</div></foreignObject></svg>`;
fs.writeFileSync(path.join(here, 'healthray-design-system.svg'), svg);
fs.unlinkSync(built);
await browser.close();
console.log('height', height);
