/* Pricing page: billing-term switch (1 / 3 / 5 years). Totals = yearly price × years (no term discount today). */
(function () {
  var radios = document.querySelectorAll('input[name=term]');
  if (!radios.length) return;
  var inr = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
  function update(years) {
    Array.prototype.forEach.call(document.querySelectorAll('.term-total'), function (el) {
      var p = +el.getAttribute('data-price');
      el.textContent = years === 1
        ? 'About ' + inr.format(Math.round(p / 12)) + ' a month'
        : 'Billed ' + inr.format(p * years) + ' for ' + years + ' years';
    });
  }
  Array.prototype.forEach.call(radios, function (r) {
    r.addEventListener('change', function () { if (r.checked) update(+r.value); });
  });
})();
