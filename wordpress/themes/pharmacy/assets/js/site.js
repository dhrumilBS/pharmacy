/* Pharmacy site — shared site behaviour. Vanilla, no dependencies. Every block checks its elements exist. */
(function () {
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  // Review mode: ?review outlines unconfirmed claims
  if (/[?&]review\b/.test(location.search)) document.documentElement.classList.add('review');
  $$('[data-year]').forEach(function (el) { el.textContent = new Date().getFullYear(); });

  /* ---------- Header: mega-menus + mobile nav ---------- */
  var menuBtns = $$('.hd-has > .hd-link');
  function closeMenus(except) {
    menuBtns.forEach(function (b) {
      if (b === except) return;
      b.setAttribute('aria-expanded', 'false');
      var p = document.getElementById(b.getAttribute('aria-controls')); if (p) p.hidden = true;
    });
  }
  menuBtns.forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.stopPropagation();
      var open = b.getAttribute('aria-expanded') === 'true';
      closeMenus(b);
      b.setAttribute('aria-expanded', String(!open));
      document.getElementById(b.getAttribute('aria-controls')).hidden = open;
    });
  });
  document.addEventListener('click', function (e) { if (!e.target.closest('.hd-has')) closeMenus(); });

  var burger = $('.hd-burger'), nav = $('#hd-nav');
  function setNav(open) {
    if (!burger) return;
    nav.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    burger.querySelector('use').setAttribute('href', open ? '#i-x' : '#i-menu');
  }
  if (burger) burger.addEventListener('click', function () { setNav(!nav.classList.contains('open')); });
  if (nav) nav.addEventListener('click', function (e) { if (e.target.closest('a')) { setNav(false); closeMenus(); } });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var openBtn = menuBtns.filter(function (b) { return b.getAttribute('aria-expanded') === 'true'; })[0];
    if (openBtn) { closeMenus(); openBtn.focus(); return; }
    if (nav && nav.classList.contains('open')) { setNav(false); burger.focus(); }
  });

  /* ---------- Expiry estimator (Indian number format) ---------- */
  var p = $('#purchase'), x = $('#expiry');
  if (p && x) {
    var inr = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
    var calc = function () {
      var monthly = +p.value * (+x.value / 100);
      $('#purchase-out').textContent = inr.format(+p.value);
      $('#expiry-out').textContent = (+x.value).toFixed(1) + '%';
      $('#loss-month').textContent = inr.format(monthly);
      $('#loss-year').textContent = inr.format(monthly * 12);
      $('#loss-half').textContent = inr.format(monthly * 6);
      // Filled part of each slider track (home.css draws it from --fill)
      [p, x].forEach(function (r) { r.style.setProperty('--fill', ((r.value - r.min) / (r.max - r.min) * 100) + '%'); });
    };
    p.addEventListener('input', calc); x.addEventListener('input', calc); calc();
  }

  /* ---------- Tabs (WAI-ARIA pattern) ---------- */
  $$('[role=tablist]').forEach(function (list) {
    var tabs = $$('[role=tab]', list);
    function select(tab, focus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on);
        t.tabIndex = on ? 0 : -1;
        document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
      });
      if (focus) tab.focus();
    }
    list._select = select;   // used by the scroll-driven features block below
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { select(t); });
      t.addEventListener('keydown', function (e) {
        var n = null;
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') n = tabs[0];
        if (e.key === 'End') n = tabs[tabs.length - 1];
        if (n) { e.preventDefault(); select(n, true); }
      });
    });
  });

  /* ---------- Feature tabs follow the scroll (wide screens) ----------
     The tab block sticks inside a tall [data-scroll-tabs] track (home.css); scrolling through the track
     advances the tabs, and clicking a tab scrolls smoothly to its stretch of the track. Below 1025px the
     tabs behave as normal tabs. */
  var track = $('[data-scroll-tabs]');
  if (track && $('[role=tablist]', track)) {
    var tlist = $('[role=tablist]', track), stabs = $$('[role=tab]', tlist), n = stabs.length;
    var wide = window.matchMedia('(min-width: 1025px)');
    var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var STICK = 100, current = -1, queued = false, lockTo = null, lockTimer = 0;
    var geo = function () {
      var box = track.getBoundingClientRect(), block = $('.tabs', track).offsetHeight;
      return { top: box.top + window.pageYOffset - STICK, len: Math.max(1, track.offsetHeight - block) };
    };
    var follow = function () {
      queued = false;
      if (!wide.matches || track.offsetParent === null) return;
      var g = geo(), i = Math.floor((window.pageYOffset - g.top) / g.len * n);
      i = Math.max(0, Math.min(n - 1, i));
      if (lockTo !== null) { if (i !== lockTo) return; lockTo = null; }   // ignore tabs passed during a click-scroll
      if (i !== current) { current = i; tlist._select(stabs[i]); }
    };
    window.addEventListener('scroll', function () { if (!queued) { queued = true; requestAnimationFrame(follow); } }, { passive: true });
    window.addEventListener('resize', follow);
    stabs.forEach(function (t, i) {
      t.addEventListener('click', function () {
        if (!wide.matches) return;
        var g = geo();
        current = i; lockTo = i;
        clearTimeout(lockTimer); lockTimer = setTimeout(function () { lockTo = null; }, 1500);
        window.scrollTo({ top: g.top + (i + .5) / n * g.len, behavior: calm ? 'auto' : 'smooth' });
      });
    });
    follow();
  }

  /* ---------- FAQ accordion ---------- */
  $$('.faq-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      var open = b.getAttribute('aria-expanded') === 'true';
      b.setAttribute('aria-expanded', String(!open));
      document.getElementById(b.getAttribute('aria-controls')).hidden = open;
    });
  });

  /* ---------- Demo form: intent from CTA + local validation (sends nothing) ---------- */
  var form = $('#demo-form'), want = $('#f-want');
  if (want) {
    // The scripted form's options have the values demo / quote / checklist; the Contact Form 7 form
    // posts the option text, so an option is matched by its value or by a word in its text.
    var labels = { demo: 'Book my free demo', quote: 'Get Enterprise pricing', checklist: 'Send me the checklist' };
    var words = { demo: 'demo', quote: 'enterprise', checklist: 'checklist' };
    var find = function (key) {
      var hit = null;
      Array.prototype.forEach.call(want.options, function (o) {
        if (!hit && (o.value === key || (o.value + ' ' + o.text).toLowerCase().indexOf(words[key]) > -1)) hit = o;
      });
      return hit;
    };
    var sync = function () {
      var l = $('#submit-label'), sel = want.options[want.selectedIndex], k = null;
      Object.keys(words).forEach(function (w) { if (!k && sel && find(w) === sel) k = w; });
      if (l && k) { if (l.tagName === 'INPUT') l.value = labels[k]; else l.textContent = labels[k]; }
    };
    var fromUrl = (location.search.match(/[?&]want=(\w+)/) || [])[1];
    if (fromUrl && words[fromUrl] && find(fromUrl)) want.value = find(fromUrl).value;
    $$('[data-cta]').forEach(function (a) {
      a.addEventListener('click', function () {
        var v = a.getAttribute('data-cta'), o = words[v] ? find(v) : null;
        if (o) want.value = o.value;
        sync();
      });
    });
    want.addEventListener('change', sync); sync();
  }
  /* ---------- Hero demo slip: 2 fields, inline thank-you, prefills #demo (sends nothing) ---------- */
  var phoneOk = function (v) { return /^[6-9][0-9]{9}$/.test(v.replace(/\D/g, '').slice(-10)); };
  $$('[data-lead-form]').forEach(function (slip) {
    slip.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = slip.elements.name, phone = slip.elements.phone, first = null;
      [[name, name.value.trim() !== ''], [phone, phoneOk(phone.value)]].forEach(function (c) {
        c[0].closest('.fld').classList.toggle('invalid', !c[1]);
        c[0].setAttribute('aria-invalid', String(!c[1]));
        if (!c[1] && !first) first = c[0];
      });
      if (first) { first.focus(); return; }
      var n = name.value.trim().split(/\s+/)[0];
      $('[data-lead-name]', slip).textContent = ', ' + n;
      slip.classList.add('is-done');
      var done = $('.slip-done', slip); done.hidden = false; done.focus();
      // Carry the details into the full form so "Add your store details" doesn't ask twice
      var fn = $('#f-name'), fp = $('#f-phone');
      if (fn && !fn.value) fn.value = name.value.trim();
      if (fp && !fp.value) fp.value = phone.value.replace(/\D/g, '').slice(-10);
    });
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var first = null;
      $$('input[required]', form).forEach(function (inp) {
        var ok = inp.value.trim() !== '' && (!inp.pattern || new RegExp('^' + inp.pattern + '$').test(inp.value.replace(/\D/g, '').slice(-10)));
        inp.closest('.fld').classList.toggle('invalid', !ok);
        inp.setAttribute('aria-invalid', String(!ok));
        if (!ok && !first) first = inp;
      });
      if (first) { first.focus(); return; }
      form.hidden = true;
      var s = $('#form-success'); s.hidden = false; s.focus();
    });
  }
})();
