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

  /* Table of contents: mark the section being read (the sidebar list and the small-screen list both follow) */
  var links = Array.prototype.slice.call(document.querySelectorAll('.b-toc a'));
  if (links.length) {
    var heads = [];
    links.forEach(function (a) {
      var h = document.getElementById(a.getAttribute('href').slice(1));
      if (h && heads.indexOf(h) === -1) heads.push(h);
    });
    var current = null;
    var mark = function () {
      var id = null;
      heads.forEach(function (h) { if (h.getBoundingClientRect().top <= 140) id = h.id; });
      if (id === current) return;
      current = id;
      links.forEach(function (a) {
        var on = id !== null && a.getAttribute('href') === '#' + id;
        a.classList.toggle('is-on', on);
        if (on) a.setAttribute('aria-current', 'location'); else a.removeAttribute('aria-current');
      });
    };
    window.addEventListener('scroll', mark, { passive: true });
    window.addEventListener('resize', mark);
    mark();
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
