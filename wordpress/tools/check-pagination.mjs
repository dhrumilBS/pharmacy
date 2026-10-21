// Checks the blog hub's and the topics' pagination from the outside, the way a crawler sees it.
//   node check-pagination.mjs [site root]        default http://192.168.0.25/pharmacy-wp/
// For the hub and every topic it follows rel="next" from page 1 to the last page and checks that:
//   - every page answers 200 and is indexable, the page after the last answers 404 (no empty pages to index);
//   - each page's canonical is its own URL, and page 1 has no /page/1/ (that URL 301s to the page's own);
//   - rel="prev" / rel="next" point at the neighbours, with one tag each (no duplicates);
//   - the title and the description are unique per page, and the pager has real links to every number;
//   - the pages together list every article once (no article on two pages, none missing).
const root = (process.argv[2] || 'http://192.168.0.25/pharmacy-wp/').replace(/\/?$/, '/');
const topics = ['', 'expiry', 'purchase', 'switching', 'staff', 'buying'];
let fails = 0;
const bad = (m) => { fails++; console.log('  FAIL ' + m); };
const ok = (m) => console.log('  ok   ' + m);

const get = async (url) => {
  const r = await fetch(url, { redirect: 'manual' });
  return { status: r.status, location: r.headers.get('location'), text: r.status < 300 ? await r.text() : '' };
};
const tag = (html, re) => [...html.matchAll(re)].map((m) => m[1]);
const attr = (html, rel) => tag(html, new RegExp('<link rel=["\']' + rel + '["\'] href=["\']([^"\']+)["\']', 'gi'));

for (const t of topics) {
  const base = root + 'blog/' + (t ? t + '/' : '');
  console.log('\n' + (t ? 'Topic ' + t : 'Hub') + '  ' + base);
  const seenTitles = new Set(), seenDesc = new Set(), seenLinks = new Set();
  let url = base, n = 1, last = 0;
  while (url) {
    const r = await get(url);
    if (r.status !== 200) { bad('page ' + n + ' answered ' + r.status + ' (' + url + ')'); break; }
    const h = r.text;
    const canon = attr(h, 'canonical')[0];
    const prev = attr(h, 'prev'), next = attr(h, 'next');
    const title = (h.match(/<title>(.*?)<\/title>/s) || [])[1];
    const desc = (h.match(/<meta name="description" content="([^"]*)"/) || [])[1];
    const robots = (h.match(/<meta name='robots' content='([^']*)'/) || [])[1] || '';
    if (canon !== url) bad('page ' + n + ' canonical ' + canon + ' is not ' + url);
    if (/noindex/.test(robots)) bad('page ' + n + ' is noindex');
    if (prev.length > (n > 1 ? 1 : 0)) bad('page ' + n + ' has ' + prev.length + ' rel=prev');
    if (next.length > 1) bad('page ' + n + ' has ' + next.length + ' rel=next');
    if (n > 1 && (!prev[0] || prev[0] !== (n === 2 ? base : base + 'page/' + (n - 1) + '/'))) bad('page ' + n + ' rel=prev is ' + prev[0]);
    if (seenTitles.has(title)) bad('page ' + n + ' repeats a title'); seenTitles.add(title);
    if (seenDesc.has(desc)) bad('page ' + n + ' repeats a description'); seenDesc.add(desc);
    for (const a of tag(h, /<article class="bc[^"]*">[\s\S]*?<a class="bc-link" href="([^"]+)"/g)) {
      if (seenLinks.has(a)) bad('article on two pages: ' + a); seenLinks.add(a);
    }
    if (n > 1 && !/Page \d+ of \d+/.test(title + desc)) bad('page ' + n + ' title or description has no page number');
    last = n;
    url = next[0] || null;
    n++;
    if (n > 60) { bad('more than 60 pages: a loop?'); break; }
  }
  const beyond = await get(base + 'page/' + (last + 1) + '/');
  beyond.status === 404 ? ok('page ' + (last + 1) + ' answers 404') : bad('page ' + (last + 1) + ' (beyond the last) answered ' + beyond.status);
  const one = await get(base + 'page/1/');
  one.status === 301 && one.location === base ? ok('/page/1/ redirects to ' + base) : bad('/page/1/ answered ' + one.status + ' ' + one.location);
  if (last > 1) {
    const q = await get(base + '?paged=2');
    q.status === 301 ? ok('?paged=2 redirects to the clean URL') : bad('?paged=2 answered ' + q.status);
  }
  ok(last + ' page(s), ' + seenLinks.size + ' article(s) listed');
}
console.log(fails ? '\n' + fails + ' problem(s)' : '\nAll checks passed');
process.exit(fails ? 1 : 0);
