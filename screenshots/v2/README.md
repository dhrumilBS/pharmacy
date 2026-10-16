# v2 — new product UI screens

A new visual design for the Healthray Pharmacy screens, used for marketing graphics. The functions and data match the real app (FEFO batch selection, OPD/IPD patient, doctor, payment methods, GST-inclusive totals, WhatsApp bill sharing, keyboard shortcuts), but the layout is not a copy of it. It follows the website design system: navy/blue/teal, Plus Jakarta Sans + Inter. The earlier recreations in `../` are left unchanged.

| File | What |
|---|---|
| `src/ui.css` | Shared v2 UI styles |
| `src/01-counter-billing.html` → `01-counter-billing.png` | Counter billing screen (1440×900 @2×). Used on home features tab, `features/billing` |
| `src/02-dashboard.html` | Dashboard. Home reports tab, `features/reports`, about, header menu |
| `src/03-expiry.html` | Stock expiry report. Home expiry tab, `features/expiry` |
| `src/04-purchase-import.html` | Distributor bill CSV import. Home purchase tab, `features/purchase` |
| `src/05-reorder.html` | Stock alert and short book. Home stock tab, `features/inventory` |
| `src/06-gst.html` | GST report. Home GST tab, `features/gst` |
| `src/07-hospital.html` | Hospital token board. Home patients tab, `features/index` |
| `src/08-retention.html` | Patient profile: history, reminders, loyalty card, online orders, ledger, bill sharing. `features/retention` |
| `src/09-security.html` | Users and security: roles, OTP login, IP whitelist, force logout, activity report. `features/security`, `data-security` |
| `src/10-initial-import.html` | Add initial data: items and opening stock from CSV, columns matched, rows flagged. `switch` |

Web versions are vector SVGs in `assets/img/screens/` (see `scripts/make-svg-screens.py`). The script rebuilds every screen; restore unchanged ones from git so approved screens don't churn.

Re-render:
```powershell
.\screenshots\v2\render.ps1              # everything
.\screenshots\v2\render.ps1 -Only hero   # one file
```

All data is dummy and matches `../dummy-data/`. The graphic carries an "Illustrative screen · sample data" note, bottom right.
