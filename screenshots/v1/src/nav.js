// Injects the pharmacy app top bar. Usage: <script src="nav.js" data-on="Sale"></script>
(function () {
  var on = document.currentScript.getAttribute('data-on');
  var ic = {
    Dashboard: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
    'Account Master': '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    Sale: '<path d="M3 20h18M5 16l4-5 4 3 6-7"/>',
    Purchase: '<path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
    'Item Master': '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
    Initialization: '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2"/>',
    'Pay Bill': '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/>',
    Reports: '<path d="M6 3h9l4 4v14H6z"/><path d="M9 13h6M9 17h6"/>'
  };
  var dd = { Initialization: 1, 'Pay Bill': 1, Reports: 1 };
  var nav = Object.keys(ic).map(function (k) {
    return '<a class="' + (k === on ? 'on ' : '') + (dd[k] ? 'dd' : '') + '"><svg class="i" viewBox="0 0 24 24">' + ic[k] + '</svg>' + k + '</a>';
  }).join('');
  var html = '<header class="top"><div class="logo" style="background:none"><svg viewBox="0 0 40 40" width="34" height="34" aria-hidden="true" style="display:block"><defs><linearGradient id="lmg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2563EB"/><stop offset="1" stop-color="#0F9F9A"/></linearGradient></defs><rect width="40" height="40" rx="11" fill="url(#lmg)"/><rect x="16.5" y="8" width="7" height="24" rx="3.5" fill="#fff"/><rect x="8" y="16.5" width="24" height="7" rx="3.5" fill="#fff"/><path d="M23.5 16.5h5a3.5 3.5 0 0 1 0 7h-5z" fill="#99F6E4"/></svg></div><nav class="nav">' + nav + '</nav>' +
    '<div class="right"><span class="plan">Enterprise · 31-12-2028</span>' +
    '<span class="circle"><svg class="i" viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h6M9 11h6"/></svg></span>' +
    '<span class="pill-btn"><svg class="i" viewBox="0 0 24 24"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>+91 89808 80819</span>' +
    '<span class="circle"><svg class="i" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span>' +
    '<span class="circle"><svg class="i" viewBox="0 0 24 24"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"/><path d="M10 21h4"/></svg></span>' +
    '<span class="avatar">S</span></div></header>';
  document.currentScript.insertAdjacentHTML('beforebegin', html);
})();
