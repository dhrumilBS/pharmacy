/* Thank-you page: greets the visitor by first name when Lead Guard left it in
   sessionStorage just before the redirect. Without it (direct visit, storage
   blocked) the heading simply has no name. */
(function () {
  var slot = document.querySelector('[data-thanks-name]');
  if (!slot) return;
  try {
    var sent = JSON.parse(sessionStorage.getItem('lead_guard_sent') || 'null');
    var name = sent && typeof sent.name === 'string' ? sent.name.trim() : '';
    if (name) slot.textContent = ', ' + name;
  } catch (e) { /* storage blocked or not JSON: no greeting */ }
})();
