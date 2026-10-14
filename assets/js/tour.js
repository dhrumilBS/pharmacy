/* Product tour: a scripted, pausable timeline over the DOM in partials/product-tour.php.
   Two controls only: play/pause and full screen. Autoplays (muted by nature) when on screen, unless the
   visitor prefers reduced motion or paused it themselves. Loops after the end card. Vanilla, no dependencies. */
(function () {
  var player = document.querySelector('[data-tour]');
  if (!player) return;
  var fit = player.querySelector('.tour-fit'), stage = player.querySelector('.tour-stage');
  var endCard = player.querySelector('.tour-end'), bar = player.querySelector('.tour-progress i');
  var btnPlay = player.querySelector('[data-tour-play]'), btnFs = player.querySelector('[data-tour-fs]');
  var W = 1200, H = 640, scale = 1, initial = stage.innerHTML;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function q(s) { return stage.querySelector(s); }
  function qa(s) { return Array.prototype.slice.call(stage.querySelectorAll(s)); }
  var money = function (n) { return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
  var ease = function (p) { return p < .5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2; };

  /* ---------- Fit the 1200×640 stage into whatever box it has (page or full screen) ---------- */
  function layout() {
    scale = Math.min(fit.clientWidth / W, fit.clientHeight / H) || 1;
    stage.style.transform = 'scale(' + scale + ')';
  }
  if (window.ResizeObserver) new ResizeObserver(layout).observe(fit); else window.addEventListener('resize', layout);
  layout();

  /* ---------- Cursor ---------- */
  var cur = { x: 760, y: 560 };
  // (x, y) is the arrow tip. Clamp so the whole 28px cursor always stays inside the stage (the fit box clips)
  function setCursor(x, y) {
    x = Math.max(4, Math.min(W - 26, x)); y = Math.max(4, Math.min(H - 32, y));
    cur.x = x; cur.y = y; var c = q('.t-cursor'); if (c) c.style.transform = 'translate(' + x + 'px,' + y + 'px)';
  }
  // Centre of an element in stage coordinates. The scale is read from the stage's live rect, so it is
  // never stale (resize, full screen) and matches the rects it divides.
  function pos(sel, dx, dy) {
    var el = q(sel); if (!el) return { x: cur.x, y: cur.y };
    var r = el.getBoundingClientRect(), s = stage.getBoundingClientRect(), k = s.width / W || scale;
    return { x: (r.left - s.left + r.width / 2) / k + (dx || 0), y: (r.top - s.top + r.height / 2) / k + (dy || 0) };
  }

  /* ---------- Timeline DSL: at(seconds, duration, fn(progress, state)) ---------- */
  var T = [];
  function at(t, d, fn) { T.push({ t: t * 1000, d: d * 1000, fn: fn }); }
  function cls(t, sel, name, on) { at(t, 0, function () { qa(sel).forEach(function (el) { el.classList.toggle(name, on !== false); }); }); }
  function show(t, sel) { cls(t, sel, 'on', true); }
  function hide(t, sel) { cls(t, sel, 'on', false); }
  function text(t, sel, s) { at(t, 0, function () { var el = q(sel); if (el) el.textContent = s; }); }
  var SCENES = { dash: ['Dashboard', '/dashboard'], bill: ['New sale', '/sales/new'], buy: ['Purchase import', '/purchase/import'], exp: ['Expiry report', '/reports/stock-expiry'] };
  function scene(t, name) {
    at(t, 0, function () {
      stage.setAttribute('data-scene', name);
      q('[data-tab-title]').textContent = SCENES[name][0]; q('[data-url]').textContent = SCENES[name][1];
    });
  }
  // Human-ish move: eased, with a slight arc. The target is re-measured every frame, so the tip still
  // lands on it if it is sliding in (scene change, dropdown, row-in) or the player is resized mid-move.
  function move(t, d, sel, dx, dy) {
    at(t, d, function (p, st) {
      if (!st.from) st.from = { x: cur.x, y: cur.y };
      st.to = pos(sel, dx, dy);
      var e = ease(p), arc = Math.sin(Math.PI * p) * Math.min(28, Math.abs(st.to.x - st.from.x) * .08);
      setCursor(st.from.x + (st.to.x - st.from.x) * e, st.from.y + (st.to.y - st.from.y) * e - arc);
    });
  }
  function click(t, sel) {
    at(t, 0, function () {
      var c = q('.t-cursor'), r = q('.t-ripple'), el = sel && q(sel);
      // Snap onto the target at the moment of the click, so a late layout shift can never make it click beside it
      // (only if the tip is outside the target; deliberate off-centre aims such as inside the search box are kept)
      if (el) {
        var er = el.getBoundingClientRect(), sr = stage.getBoundingClientRect(), k = sr.width / W || scale;
        var x0 = (er.left - sr.left) / k, y0 = (er.top - sr.top) / k, x1 = x0 + er.width / k, y1 = y0 + er.height / k;
        if (cur.x < x0 + 2 || cur.x > x1 - 2 || cur.y < y0 + 2 || cur.y > y1 - 2) { var tp = pos(sel); setCursor(tp.x, tp.y); }
      }
      c.classList.add('down');
      r.style.left = cur.x + 'px'; r.style.top = cur.y + 'px';
      r.classList.remove('go'); void r.offsetWidth; r.classList.add('go');
      if (el) el.classList.add('press');
    });
    at(t + .16, 0, function () { q('.t-cursor').classList.remove('down'); var el = sel && q(sel); if (el) el.classList.remove('press'); });
  }
  function type(t, d, key, s) {
    at(t, d, function (p) { var el = q('[data-typed="' + key + '"]'); el.textContent = s.slice(0, Math.round(s.length * p)); });
  }
  function count(t, d, sel, a, b) { at(t, d, function (p) { q(sel).textContent = money(a + (b - a) * ease(p)); }); }
  function toast(t, s, hold) {
    at(t, 0, function () { var el = q('[data-toast]'); el.textContent = s; el.classList.add('on'); });
    at(t + hold, 0, function () { q('[data-toast]').classList.remove('on'); });
  }
  function drag(t, d, fileSel, toSel) {
    at(t, d, function (p, st) {
      if (!st.from) {
        st.from = { x: cur.x, y: cur.y }; st.to = pos(toSel, 0, 6);
        st.f = q(fileSel); st.f.classList.add('drag');
      }
      var e = ease(p), dx = (st.to.x - st.from.x) * e, dy = (st.to.y - st.from.y) * e;
      setCursor(st.from.x + dx, st.from.y + dy);
      st.f.style.transform = 'translate(' + dx + 'px,' + dy + 'px) rotate(' + (-3 + 5 * e) + 'deg)';
    });
  }

  /* ---------- Script (seconds) ---------- */
  scene(0, 'dash');
  move(.6, 1.4, '.t-new', -10, 2);
  click(2.2, '.t-new');
  scene(2.6, 'bill');
  // Scan the first item with the barcode reader
  show(3.1, '.t-scanner');
  cls(3.9, '.t-scanner', 'scan');
  cls(4.8, '.t-scanner', 'beep');
  show(4.9, '.t-row1'); cls(4.9, '.t-empty', 'gone');
  text(4.9, '.t-items', '1 item'); count(4.9, .5, '.t-total', 0, 128.40);
  hide(5.7, '.t-scanner');
  // Search the second item; the nearest-expiry batch is picked
  move(5.8, 1.0, '.t-search', -140, 0);
  click(6.9, '.t-search'); cls(6.9, '.t-search', 'focus');
  type(7.2, 1.0, 'search', 'dolo 6');
  show(8.3, '.t-dd');
  move(8.6, .7, '.dd-pick', -60, 0);
  click(9.4, '.dd-pick');
  hide(9.7, '.t-dd'); show(9.7, '.t-batches');
  cls(10.3, '.bt-pick', 'flash');
  hide(11.4, '.t-batches'); cls(11.4, '.t-search', 'focus', false); type(11.4, .01, 'search', '');
  show(11.5, '.t-row2'); text(11.5, '.t-items', '2 items'); count(11.5, .6, '.t-total', 128.40, 159.31);
  // Quantity 2
  move(12.3, .8, '.t-qty', 0, 0);
  click(13.2, '.t-qty');
  text(13.5, '.t-qty', '2'); text(13.5, '.t-amt2', '₹61.82'); count(13.5, .5, '.t-total', 159.31, 190.22);
  // Patient
  move(14.1, .8, '.t-patient', -40, 0);
  click(15.0, '.t-patient'); cls(15.0, '.t-patient', 'focus');
  type(15.2, .8, 'patient', 'Ramesh');
  show(16.1, '.t-pchip');
  move(16.4, .5, '.t-pchip', -30, 0);
  click(17.0, '.t-pchip');
  type(17.1, .01, 'patient', 'Ramesh Patel'); hide(17.2, '.t-pchip'); cls(17.2, '.t-patient', 'focus', false);
  // Pay by UPI, save, print + share
  move(17.5, .7, '.t-upi', 0, 0);
  click(18.3, '.t-upi'); cls(18.3, '.t-upi', 'sel');
  move(18.7, .8, '.t-save', -30, 0);
  click(19.6, '.t-save');
  show(19.9, '.t-receipt');
  toast(20.1, 'Bill INV-26-04813 saved · shared on WhatsApp', 2.2);
  hide(22.1, '.t-receipt');
  // Distributor bill: drag the CSV in
  scene(22.6, 'buy');
  show(23.1, '.t-file');
  move(23.4, .8, '.t-file', -40, 0);
  cls(24.3, '.t-cursor', 'grab');
  drag(24.4, 1.4, '.t-file', '.t-drop');
  cls(25.0, '.t-drop', 'hot');
  cls(25.9, '.t-cursor', 'grab', false); cls(25.9, '.t-file', 'gone'); cls(25.9, '.t-drop', 'hot', false); cls(25.9, '.t-drop', 'busy');
  at(26.0, 1.4, function (p) { q('.t-prog i').style.width = (p * 100) + '%'; });
  [0, 1, 2, 3, 4].forEach(function (i) { at(27.4 + i * .22, 0, function () { qa('.t-prow')[i].classList.add('on'); }); });
  cls(27.5, '.t-drop', 'done'); text(27.5, '.t-droplabel', 'SKP-2026-4471.csv imported');
  show(28.7, '.t-psum');
  move(28.9, 1.0, '.t-psum', 120, -6);       // rest on the summary line (it sits near the stage's bottom edge)
  // Expiry report: 60 days, then draft a purchase return
  scene(30.0, 'exp');
  move(30.6, .9, '.t-f60', 0, 0);
  click(31.6, '.t-f60'); cls(31.6, '.t-f60', 'sel');
  [0, 1, 2, 3].forEach(function (i) { at(31.9 + i * .2, 0, function () { qa('.t-erow')[i].classList.add('on'); }); });
  show(32.8, '.t-esum');
  move(33.6, .9, '.t-ret', -10, 0);
  click(34.6, '.t-ret');
  toast(34.8, 'Purchase return drafted · Shree Krishna Pharma', 2.0);
  move(35.6, 1.2, '.t-esum', 260, 0);        // drift to the "at risk" total, inside the frame
  at(37.0, 0, function () { endCard.hidden = false; player.classList.add('is-ended'); });
  var TOTAL = 30000;

  /* ---------- Player ---------- */
  var elapsed = 0, state = [], playing = false, raf = 0, last = 0, userPaused = false;
  function reset() {
    stage.innerHTML = initial;
    state = T.map(function () { return {}; });
    elapsed = 0; endCard.hidden = true; player.classList.remove('is-ended');
    stage.setAttribute('data-scene', 'dash');
    setCursor(760, 560);
  }
  function tick() {
    for (var i = 0; i < T.length; i++) {
      var s = T[i], st = state[i];
      if (st.done || elapsed < s.t) continue;
      var p = s.d ? Math.min(1, (elapsed - s.t) / s.d) : 1;
      s.fn(p, st);
      if (p >= 1) st.done = true;
    }
    bar.style.transform = 'scaleX(' + Math.min(1, elapsed / TOTAL) + ')';
  }
  function frame(now) {
    var dt = now - last; last = now;
    elapsed += Math.min(dt, 64);           // a long frame (tab switch) never skips scenes
    if (elapsed >= TOTAL) reset();
    tick();
    raf = requestAnimationFrame(frame);
  }
  function play() {
    if (playing) return;
    playing = true; player.classList.add('is-playing');
    btnPlay.setAttribute('aria-label', 'Pause product tour');
    last = performance.now(); raf = requestAnimationFrame(frame);
  }
  function pause() {
    if (!playing) return;
    playing = false; player.classList.remove('is-playing');
    btnPlay.setAttribute('aria-label', 'Play product tour');
    cancelAnimationFrame(raf);
  }
  btnPlay.addEventListener('click', function () {
    if (playing) { userPaused = true; pause(); } else { userPaused = false; play(); }
  });

  /* Full screen (with a fixed-overlay fallback where the API is missing, e.g. iPhone Safari) */
  function fsEl() { return document.fullscreenElement || document.webkitFullscreenElement; }
  function isFs() { return fsEl() === player || player.classList.contains('is-fs-fallback'); }
  function syncFs() {
    var on = isFs();
    player.classList.toggle('is-fs', on);
    btnFs.setAttribute('aria-label', on ? 'Exit full screen' : 'Full screen');
    layout();
  }
  btnFs.addEventListener('click', function () {
    if (isFs()) {
      if (fsEl()) (document.exitFullscreen || document.webkitExitFullscreen).call(document);
      else { player.classList.remove('is-fs-fallback'); document.body.classList.remove('tour-lock'); syncFs(); }
    } else {
      var req = player.requestFullscreen || player.webkitRequestFullscreen;
      if (req) req.call(player); else { player.classList.add('is-fs-fallback'); document.body.classList.add('tour-lock'); syncFs(); }
      userPaused = false; play();
    }
  });
  document.addEventListener('fullscreenchange', syncFs);
  document.addEventListener('webkitfullscreenchange', syncFs);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && player.classList.contains('is-fs-fallback')) { player.classList.remove('is-fs-fallback'); document.body.classList.remove('tour-lock'); syncFs(); }
  });

  /* Autoplay while on screen; stop when scrolled away or the tab is hidden */
  // Each time the tour scrolls back into view it starts again from 0 s (unless the visitor paused it themselves,
  // in which case it waits where they left it).
  var visible = false, leftView = false;
  function auto() {
    if (visible && !userPaused && !reduce && !document.hidden) {
      if (leftView) { leftView = false; reset(); tick(); }
      play();
    } else if (!isFs()) pause();
  }
  if (window.IntersectionObserver) {
    new IntersectionObserver(function (en) {
      var r = en[0].intersectionRatio;
      if (r === 0 && elapsed > 0) leftView = true;   // fully off screen: restart next time it's seen
      visible = r >= .4; auto();
    }, { threshold: [0, .4, .8] }).observe(player);
  }
  document.addEventListener('visibilitychange', auto);

  // End-card CTA: jump to the hero form and put the cursor in the name field
  endCard.querySelector('[data-focus-lead]').addEventListener('click', function (e) {
    var input = Array.prototype.filter.call(document.querySelectorAll('[data-lead-form] input[name=name]'), function (i) { return i.offsetParent; })[0];
    if (!input) return;
    e.preventDefault();
    if (isFs()) btnFs.click();
    input.scrollIntoView({ block: 'center', behavior: reduce ? 'auto' : 'smooth' });
    setTimeout(function () { input.focus({ preventScroll: true }); }, reduce ? 0 : 450);
  });

  reset(); tick();

  // QA: ?tour-at=15 freezes the tour at 15 s (for screenshots and review); no effect otherwise
  var at0 = location.search.match(/[?&]tour-at=([\d.]+)/);
  if (at0) {
    userPaused = true;
    var target = Math.min(+at0[1] * 1000, TOTAL - 1);
    while (elapsed < target) { elapsed += 50; tick(); }
  }
})();
