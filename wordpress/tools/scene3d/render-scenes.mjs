// Renders the realistic 3D scenes in scene.html to JPGs: node render-scenes.mjs [scene ...]
// Output: wordpress/import/content/images/photos/<scene>.jpg (1600x800). Needs network once for three.js (cdnjs).
import { chromium } from 'playwright-core';
import { pathToFileURL } from 'node:url';
import path from 'node:path';
import fs from 'node:fs';

const here = path.dirname(new URL(import.meta.url).pathname.replace(/^\/([A-Za-z]:)/, '$1'));
const outDir = path.resolve(here, '..', '..', 'import', 'content', 'images', 'photos');
fs.mkdirSync(outDir, { recursive: true });
const all = ['stock-imbalance', 'one-view', 'stock-transfer', 'records-calm', 'calm-rollout'];
const names = process.argv.length > 2 ? process.argv.slice(2) : all;

const browser = await chromium.launch({ channel: 'msedge', args: ['--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist', '--enable-webgl'] });
for (const n of names) {
  const page = await browser.newPage({ viewport: { width: 1600, height: 800 } });
  page.on('pageerror', (e) => console.log('pageerror', n, e.message));
  await page.goto(pathToFileURL(path.join(here, 'scene.html')).href + '?scene=' + n, { waitUntil: 'networkidle' });
  await page.waitForFunction('window.__done === true', null, { timeout: 30000 });
  await page.screenshot({ path: path.join(outDir, n + '.png'), clip: { x: 0, y: 0, width: 1600, height: 800 } });
  console.log('rendered', n);
  await page.close();
}
await browser.close();
