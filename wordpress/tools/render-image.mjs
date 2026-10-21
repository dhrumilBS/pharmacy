// Renders an HTML file to a PNG at 2x: node render-image.mjs <input.html> <output.png> <width> <height>
import { chromium } from 'playwright-core';
import { pathToFileURL } from 'node:url';
import path from 'node:path';

const [input, output, w, h] = process.argv.slice(2);
const browser = await chromium.launch({ channel: 'msedge' });
const page = await browser.newPage({ viewport: { width: +w, height: +h }, deviceScaleFactor: 2 });
await page.goto(pathToFileURL(path.resolve(input)).href);
await page.evaluate(() => document.fonts.ready);
await page.screenshot({ path: path.resolve(output), clip: { x: 0, y: 0, width: +w, height: +h } });
await browser.close();
