# v2 — new product UI screens

A new visual design for the Healthray Pharmacy screens, used for marketing graphics. The functions and data match the real app (FEFO batch selection, OPD/IPD patient, doctor, payment methods, GST-inclusive totals, WhatsApp bill sharing, keyboard shortcuts), but the layout is not a copy of it. It follows the website design system: navy/blue/teal, Plus Jakarta Sans + Inter. The earlier recreations in `../` are left unchanged.

| File | What |
|---|---|
| `src/ui.css` | Shared v2 UI styles |
| `src/01-counter-billing.html` → `01-counter-billing.png` | Counter billing screen (1440×900 @2×) |

Re-render:
```powershell
.\screenshots\v2\render.ps1              # everything
.\screenshots\v2\render.ps1 -Only hero   # one file
```

All data is dummy and matches `../dummy-data/`. The graphic carries an "Illustrative screen · sample data" note, bottom right.
