/* Campaign attribution for the booking forms.
   Keeps this visit's UTM parameters and click ids, the landing page and the
   referrer, and writes them into the hidden fields of every Contact Form 7 form (the names HandL's
   form tags use: utm_source, gclid, handl_landing_page ...). HandL's own script
   needs jQuery, which this theme does not load, so this does the same job in plain JavaScript.
   Consent: when TrustLayer Consent is active nothing is stored until the visitor allows the
   "analytics" category (same rule as its HandL integration); without it, values are kept at once.
   Storage is localStorage, key rx_attr, 90 days. */
(function () {
  var KEY = 'rx_attr', DAYS = 90, CATEGORY = 'analytics';
  var UTM = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
  var CLICK = ['gclid', 'fbclid', 'msclkid'];

  function read() {
    try {
      var d = JSON.parse(localStorage.getItem(KEY) || 'null');
      if (d && d.ts && Date.now() - d.ts < DAYS * 864e5) return d;
    } catch (e) { /* storage blocked */ }
    return null;
  }
  function write(d) { try { localStorage.setItem(KEY, JSON.stringify(d)); } catch (e) { /* storage blocked */ } }

  function capture() {
    var q = new URLSearchParams(location.search), now = {}, any = false;
    UTM.concat(CLICK).forEach(function (k) { var v = q.get(k); if (v) { now[k] = v.slice(0, 200); any = true; } });
    var d = read() || { ts: Date.now(), last: {}, landing: location.origin + location.pathname, ref: document.referrer || '' };
    if (any) d.last = now;
    write(d);
    return d;
  }

  function fill(d) {
    d = d || read();
    if (!d) return;
    var values = { handl_landing_page: d.landing, handl_original_ref: d.ref, handl_ref: document.referrer };
    UTM.concat(CLICK).forEach(function (k) {
      values[k] = d.last && d.last[k];
    });
    Object.keys(values).forEach(function (name) {
      if (!values[name]) return;
      var fields = document.getElementsByName(name);
      for (var i = 0; i < fields.length; i++) {
        if (fields[i].tagName === 'INPUT' && fields[i].type === 'hidden') fields[i].value = values[name];
      }
    });
  }

  var started = false;
  function start() { if (started) return; started = true; fill(capture()); }

  function onConsent(state) { if (state && state.categories && state.categories[CATEGORY]) start(); }
  document.addEventListener('tlc:consent', function (e) { onConsent(e.detail); });
  if (window.TrustLayer && window.TrustLayer.ready) {
    window.TrustLayer.ready(function (t) {
      var s = t.get();
      if (s ? s.categories && s.categories[CATEGORY] : t.has(CATEGORY)) start();
    });
  } else if (!document.querySelector('script[src*="trustlayer-consent"]')) {
    start();
  }
  // Forms printed after consent, or re-rendered: fill again when a visitor reaches a form
  document.addEventListener('focusin', function (e) { if (started && e.target.closest && e.target.closest('form.wpcf7-form')) fill(); });
})();
