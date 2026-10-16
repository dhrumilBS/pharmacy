<?php
/**
 * Inline SVG sprite: the logo mark (#logo-mark) and every icon symbol the
 * templates reference with <use href="#i-…"/>. Port of partials/sprite.php,
 * same symbols and markup.
 *
 * header.php prints it once per page, right after wp_body_open() and before
 * the skip link.
 *
 * $args: none.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;
?>
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <defs><linearGradient id="lm-bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0D2A40" /><stop offset="1" stop-color="#134A6E" /></linearGradient></defs>
    <symbol id="logo-mark" viewBox="0 0 40 40">
      <!-- Blister pack: nearest-expiry capsule picked first (teal) -->
      <rect width="40" height="40" rx="11" fill="url(#lm-bg)" />
      <rect x="6.5" y="6.5" width="27" height="27" rx="8" fill="#fff" fill-opacity=".1" />
      <circle cx="26.25" cy="13.75" r="4.75" fill="#FFFFFF" />
      <circle cx="13.75" cy="26.25" r="4.75" fill="#FFFFFF" />
      <circle cx="26.25" cy="26.25" r="4.75" fill="#FFFFFF" />
      <circle cx="13.75" cy="13.75" r="4.75" fill="#2DD4BF" />
    </symbol>
    <symbol id="i-cross" viewBox="0 0 24 24">
      <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z" />
    </symbol>
    <symbol id="i-check" viewBox="0 0 24 24">
      <path d="M20 6 9 17l-5-5" />
    </symbol>
    <symbol id="i-check-c" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" />
      <path d="m8 12 3 3 5-6" />
    </symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24">
      <path d="M5 12h14M13 6l6 6-6 6" />
    </symbol>
    <symbol id="i-phone" viewBox="0 0 24 24">
      <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />
    </symbol>
    <symbol id="i-chat" viewBox="0 0 24 24">
      <path d="M4 20l1.4-4.2A8 8 0 1 1 8.6 19z" />
      <path d="M9 10h6M9 13.5h4" />
    </symbol>
    <symbol id="i-search" viewBox="0 0 24 24">
      <circle cx="11" cy="11" r="7" />
      <path d="m20 20-3.5-3.5" />
    </symbol>
    <symbol id="i-printer" viewBox="0 0 24 24">
      <path d="M6 9V3h12v6M6 18H4v-7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v7h-2" />
      <path d="M6 14h12v7H6z" />
    </symbol>
    <symbol id="i-clock" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" />
      <path d="M12 7v5l3 2" />
    </symbol>
    <symbol id="i-alert" viewBox="0 0 24 24">
      <path d="M12 3 2 20h20z" />
      <path d="M12 10v4M12 17h.01" />
    </symbol>
    <symbol id="i-receipt" viewBox="0 0 24 24">
      <path d="M6 3h12v18l-3-2-3 2-3-2-3 2z" />
      <path d="M9 8h6M9 12h6M9 16h3" />
    </symbol>
    <symbol id="i-box" viewBox="0 0 24 24">
      <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z" />
      <path d="m4 7.5 8 4.5 8-4.5M12 12v9" />
    </symbol>
    <symbol id="i-truck" viewBox="0 0 24 24">
      <path d="M3 6h11v10H3zM14 9h4l3 3v4h-7" />
      <circle cx="7" cy="18" r="2" />
      <circle cx="17" cy="18" r="2" />
    </symbol>
    <symbol id="i-rupee" viewBox="0 0 24 24">
      <path d="M7 4h11M7 8.5h11M7 4c6 0 6 9 0 9h-1l8 7" />
    </symbol>
    <symbol id="i-chart" viewBox="0 0 24 24">
      <path d="M4 20V4M4 20h16" />
      <path d="M8 16v-4M12 16V8M16 16v-6" />
    </symbol>
    <symbol id="i-shield" viewBox="0 0 24 24">
      <path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z" />
      <path d="m9 12 2 2 4-4" />
    </symbol>
    <symbol id="i-store" viewBox="0 0 24 24">
      <path d="M4 10v10h16V10M3 10l2-6h14l2 6zM9 20v-6h6v6" />
    </symbol>
    <symbol id="i-stores" viewBox="0 0 24 24">
      <path d="M2 11v9h9v-9M1.5 11 3 7h7l1.5 4M13 11v9h9v-9M12.5 11 14 7h7l1.5 4" />
    </symbol>
    <symbol id="i-hospital" viewBox="0 0 24 24">
      <path d="M4 21V5h16v16M2 21h20" />
      <path d="M12 8v6M9 11h6M10 21v-3h4v3" />
    </symbol>
    <symbol id="i-leaf" viewBox="0 0 24 24">
      <path d="M5 19c0-9 6-14 15-14 0 9-5 15-14 15" />
      <path d="M5 19 13 11" />
    </symbol>
    <symbol id="i-file" viewBox="0 0 24 24">
      <path d="M6 3h8l4 4v14H6z" />
      <path d="M14 3v4h4M9 12h6M9 16h6" />
    </symbol>
    <symbol id="i-upload" viewBox="0 0 24 24">
      <path d="M12 15V4M7 9l5-5 5 5M4 15v5h16v-5" />
    </symbol>
    <symbol id="i-users" viewBox="0 0 24 24">
      <circle cx="9" cy="8" r="3.5" />
      <path d="M2.5 20a6.5 6.5 0 0 1 13 0" />
      <path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6" />
    </symbol>
    <symbol id="i-headset" viewBox="0 0 24 24">
      <path d="M4 14v-2a8 8 0 0 1 16 0v2" />
      <path d="M4 14h3v6H5a1 1 0 0 1-1-1zM20 14h-3v6h2a1 1 0 0 0 1-1zM17 20c0 1-2 1.5-4 1.5" />
    </symbol>
    <symbol id="i-screen" viewBox="0 0 24 24">
      <path d="M3 4h18v12H3zM8 20h8M12 16v4" />
    </symbol>
    <symbol id="i-play" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="9" />
      <path d="m10 8 6 4-6 4z" />
    </symbol>
    <symbol id="i-mobile" viewBox="0 0 24 24">
      <path d="M7 2h10v20H7zM11 18h2" />
    </symbol>
    <symbol id="i-sun" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="4" />
      <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
    </symbol>
    <symbol id="i-moon" viewBox="0 0 24 24">
      <path d="M20 14A8 8 0 1 1 10 4a6 6 0 0 0 10 10" />
    </symbol>
    <symbol id="i-menu" viewBox="0 0 24 24">
      <path d="M4 7h16M4 12h16M4 17h16" />
    </symbol>
    <symbol id="i-plus" viewBox="0 0 24 24">
      <path d="M12 5v14M5 12h14" />
    </symbol>
    <symbol id="i-mail" viewBox="0 0 24 24">
      <path d="M3 5h18v14H3z" />
      <path d="m3 6 9 7 9-7" />
    </symbol>
    <symbol id="i-register" viewBox="0 0 24 24">
      <path d="M5 3h12a2 2 0 0 1 2 2v16H7a2 2 0 0 1-2-2z" />
      <path d="M5 17h14M9 7h6M9 11h6" />
    </symbol>
      <symbol id="i-chev" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6" /></symbol>
    <!-- Feature pages: barcode scan, purchase return -->
    <symbol id="i-scan" viewBox="0 0 24 24"><path d="M4 8V5h3M17 5h3v3M20 16v3h-3M7 19H4v-3" /><path d="M8 9v6M11 9v6M14 9v6M17 9v6" /></symbol>
    <symbol id="i-return" viewBox="0 0 24 24"><path d="M9 14 4 9l5-5" /><path d="M4 9h11a5 5 0 0 1 0 10h-3" /></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6 6 18" /></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z" /><circle cx="12" cy="9" r="2.5" /></symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M3 10h18M8 3v4M16 3v4" /></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" /></symbol>
    <symbol id="i-linkedin" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" /><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7" /></symbol>
    <symbol id="i-youtube" viewBox="0 0 24 24"><rect x="2.5" y="6" width="19" height="12" rx="4" /><path d="m10 9.5 4.5 2.5-4.5 2.5z" /></symbol>
    <symbol id="i-instagram" viewBox="0 0 24 24"><rect x="3.5" y="3.5" width="17" height="17" rx="5" /><circle cx="12" cy="12" r="4" /><path d="M17 7v.01" /></symbol>
    <symbol id="i-facebook" viewBox="0 0 24 24"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z" /></symbol>
    <symbol id="i-xsocial" viewBox="0 0 24 24"><path d="M4 4l16 16M20 4 4 20" /></symbol>
  </svg>
