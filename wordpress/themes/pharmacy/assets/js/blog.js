/* Blog post behaviour: reading progress bar, current section in the table of contents, copy link, print checklist,
   and a collapsed table of contents on small screens. Each block checks its elements exist. */
(function () {
  var article = document.querySelector('.b-article');
  if (!article) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Reading progress (the bar is decorative; aria-hidden in the markup) */
  var bar = document.querySelector('.b-progress');
  if (bar) {
    var tick = function () {
      var r = article.getBoundingClientRect(), total = r.height - window.innerHeight * .6;
      var p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 1;
      bar.style.setProperty('--p', p.toFixed(3));
    };
    window.addEventListener('scroll', tick, { passive: true });
    window.addEventListener('resize', tick);
    tick();
  }

  /* Table of contents: mark the section being read */
  var links = Array.prototype.slice.call(document.querySelectorAll('.b-toc a'));
  if (links.length && 'IntersectionObserver' in window) {
    var byId = {};
    links.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        links.forEach(function (a) { a.classList.remove('is-on'); a.removeAttribute('aria-current'); });
        var a = byId[en.target.id];
        if (a) { a.classList.add('is-on'); a.setAttribute('aria-current', 'location'); }
      });
    }, { rootMargin: '-90px 0px -70% 0px' });
    Object.keys(byId).forEach(function (id) { var h = document.getElementById(id); if (h) io.observe(h); });
  }

  /* Collapse the table of contents on small screens (open on desktop, where it sits in the sidebar) */
  var toc = document.querySelector('.b-toc');
  if (toc && window.matchMedia('(max-width: 1100px)').matches) {
    toc.removeAttribute('open');
    toc.addEventListener('click', function (e) { if (e.target.tagName === 'A') toc.removeAttribute('open'); });
  }

  /* Copy link */
  var copy = document.querySelector('[data-copy]');
  if (copy) {
    var label = copy.querySelector('span');
    copy.addEventListener('click', function () {
      var url = location.origin + copy.getAttribute('data-copy');
      var done = function () { label.textContent = 'Link copied'; setTimeout(function () { label.textContent = 'Copy link'; }, 2000); };
      if (navigator.clipboard) navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link', url); });
      else window.prompt('Copy this link', url);
    });
  }

  /* Print a checklist (print styles keep only the article) */
  Array.prototype.forEach.call(document.querySelectorAll('[data-print]'), function (b) {
    b.addEventListener('click', function () { window.print(); });
  });

  if (reduce && bar) bar.style.display = 'none';
})();
