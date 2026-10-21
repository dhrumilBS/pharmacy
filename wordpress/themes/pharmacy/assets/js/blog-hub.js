/* Blog hub: switch topic or page without a page refresh. A click on a topic or a page number fetches that URL and swaps the
   hub in place (heading, articles, address bar and back button follow). Without JavaScript the links are plain links. */
(function () {
  var hub = document.querySelector('.b-hub');
  if (!hub || !window.fetch || !window.DOMParser || !window.history || !history.pushState) return;

  var cache = {};
  var busy = 0;

  var swap = function (html, url, push) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var next = doc.querySelector('.b-hub');
    if (!next) { window.location.href = url; return; }
    hub.innerHTML = next.innerHTML;
    if (doc.title) document.title = doc.title;
    if (push) history.pushState({ hub: 1 }, '', url);
    hub.classList.remove('is-loading');
    var bar = hub.querySelector('.b-filter-sec');
    if (bar && bar.getBoundingClientRect().top < 0) bar.scrollIntoView({ block: 'start' });
    var h = hub.querySelector('h1');
    if (h) { h.setAttribute('tabindex', '-1'); }
  };

  var load = function (url, push) {
    var ticket = ++busy;
    hub.classList.add('is-loading');
    var get = cache[url] ? Promise.resolve(cache[url]) : fetch(url, { credentials: 'same-origin' }).then(function (r) {
      if (!r.ok) throw new Error('bad status');
      return r.text();
    });
    get.then(function (html) {
      cache[url] = html;
      if (ticket === busy) swap(html, url, push);
    }).catch(function () { window.location.href = url; });
  };

  hub.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('a.b-topic, .b-pager a.b-page') : null;
    if (!a || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.classList.contains('is-on')) { e.preventDefault(); return; }
    if (a.rel === 'external' || a.target === '_blank') return;
    e.preventDefault();
    load(a.href, true);
  });

  history.replaceState({ hub: 1 }, '', window.location.href);
  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.hub) load(window.location.href, false);
  });
})();
