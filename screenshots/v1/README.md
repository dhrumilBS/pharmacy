# Product screenshots — Healthray Pharmacy

Website-ready product screens with **dummy data**, for review before they go into `index.html`.

**Important:** these are **HTML recreations** of the Healthray Pharmacy app. They follow the real menus, field names and layout (checked against `../org-dashboard/` and the app code on 13 Oct 2026), but they are not captures of the live app. Before publishing:
- Compare each one with the real screen.
- Or replace it with a real screenshot taken from a demo account loaded with `dummy-data/`.

The website must still label every screen as *"Sample screen · illustrative data"*.

## The screens

All are rendered at 2× (retina), 3200 px wide.

| File | Shows | Best spot on the website |
|---|---|---|
| `01-sale-billing.png` | Sale screen: 5-item bill, batch picker selecting the nearest-expiry batch (FEFO), OPD patient + doctor, UPI payment, totals | Hero, or *Billing & counter* tab |
| `02-dashboard.png` | Sales/purchase/profit cards, 12-month chart, payment-method collection, stock expiry and stock alert widgets, top brands/users/customers | *A day at your pharmacy*, or a full-width "see the whole business" band |
| `03-stock-expiry-report.png` | Supplier-wise expiry report: 23 batches, ₹18,640 at risk | *Expiry & batch tracking* tab, next to the expiry-loss estimator |
| `04-purchase-csv-import.png` | Purchase bill imported from CSV: 38 rows, free quantity, scheme %, PTR, margin | *Purchase & suppliers* tab, *Switching* section |
| `05-stock-alert-short-book.png` | Stock alert report + short book → create purchase order | *Stock & reorder* tab |
| `06-gst-report.png` | September GST report: rate-wise + HSN-wise (5% / 18% / 0%) | *GST & accounts* tab |
| `07-token-board.png` | Hospital pharmacy token board: EMR prescriptions, IPD/TPA tokens, bill this token | *Patients & hospital pharmacy* tab, *Who it's for → hospital* |

The numbers match each other and the website samples:
- Hero expiry card: 23 batches · ₹18,640.
- GST sample: ₹11,48,320 taxable.
- The same batches (AZ1189, ML0388, SC7731) appear on the sale, dashboard, expiry and token screens.

## Editing and re-rendering

- Sources are in `src/`:
  - one HTML file per screen
  - shared app styling in `app.css`
  - the top bar comes from `nav.js`
- To change data, edit the HTML, then run:

```powershell
.\screenshots\render.ps1          # all screens
.\screenshots\render.ps1 03       # one screen
```

Requirements: Microsoft Edge (headless) and internet access for the Poppins font. Canvas sizes are set in `render.ps1`.

## Dummy data for real screenshots (`dummy-data/`)

To capture the real app instead, use a **separate demo account**, never a customer's account.

1. **`masters.csv`**: add the 3 suppliers, 2 doctors, 5 patients and 3 users (Account Master / Users) by hand.
2. **`items-opening-stock.csv`**: 24 items with batch, expiry, MRP, PTR, stock and min/max.
   - Import it via *Initialization → Add Initial Data → Import Stock*.
   - Match the columns to the app's sample sheet when prompted.
   - The Schedule column is left blank on purpose; the pharmacist should set Schedule H/H1 correctly.
3. Create the sale from `01-sale-billing` (patient Ramesh Patel, Dr. Kavita Shah, UPI). The batch picker will offer the nearest-expiry batch first.
4. Open Reports → Stock Expiry / Stock Alert / GST Report, and the Dashboard, for the other screens.
5. Capture at 1600 px wide, browser zoom 100%, with the 2× device scale if possible.

All names, phone numbers, GSTINs, DL numbers and UHIDs are fictional. Brand names are real medicines, used only as typical counter items.
