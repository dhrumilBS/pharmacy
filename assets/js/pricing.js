/* Pricing page: billing-term switch. Terms, years and discounts come from the live pricing API (rendered by pricing.php).
   Total = yearly price × years, less the term's discount_percent. Keep in step with term_total() in pricing.php. */
(function () {
  /* Comparison table: collapsible feature groups. Without JS every group stays open.
     First group starts open, the rest collapsed; the group row's per-plan "n/total" shows what's inside. */
  var table = document.querySelector('.pc[data-collapse]');
  if (table) {
    var toggles = Array.prototype.slice.call(table.querySelectorAll('.pc-toggle'));
    var all = document.querySelector('.pc-all');
    var setOpen = function (b, open) {
      b.setAttribute('aria-expanded', String(open));
      document.getElementById(b.getAttribute('aria-controls')).hidden = !open;
    };
    var syncAll = function () {
      if (!all) return;
      var anyClosed = toggles.some(function (b) { return b.getAttribute('aria-expanded') !== 'true'; });
      all.setAttribute('aria-expanded', String(!anyClosed));
      all.textContent = anyClosed ? 'Expand all' : 'Collapse all';
    };
    toggles.forEach(function (b, i) {
      setOpen(b, i === 0);
      b.addEventListener('click', function (e) {
        e.stopPropagation();
        setOpen(b, b.getAttribute('aria-expanded') !== 'true');
        syncAll();
      });
      /* clicking the plan counts in the group row toggles too (mouse convenience; the button stays the keyboard target) */
      b.closest('tr').addEventListener('click', function () { b.click(); });
    });
    if (all) {
      all.hidden = false;
      all.addEventListener('click', function () {
        var open = all.getAttribute('aria-expanded') !== 'true';
        toggles.forEach(function (b) { setOpen(b, open); });
        syncAll();
      });
    }
    syncAll();
  }

  var radios = document.querySelectorAll('input[name=term]');
  if (!radios.length) return;
  var inr = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
  function update(years, discount) {
    Array.prototype.forEach.call(document.querySelectorAll('.term-total'), function (el) {
      var total = +el.getAttribute('data-price') * years * (1 - discount / 100);
      el.textContent = years === 1
        ? 'About ' + inr.format(Math.round(total / 12)) + ' a month'
        : 'Billed ' + inr.format(Math.round(total)) + ' for ' + years + ' years';
    });
  }
  Array.prototype.forEach.call(radios, function (r) {
    r.addEventListener('change', function () {
      if (r.checked) update(+r.value || 1, +r.getAttribute('data-discount') || 0);
    });
  });
})();
