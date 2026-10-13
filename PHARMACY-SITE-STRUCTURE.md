# Healthray Pharmacy — Website Structure

**Goal:** turn Indian pharmacy owners (single stores first, then chains and hospital pharmacies) into **demo bookings** and **free Starter sign-ups**, and rank for the searches they make before buying.
**Built from:** the live app (modules, plans and prices verified 13 Oct 2026; see `PHARMACY-ICP-AND-UX-STRATEGY.md` §0), the ICP research, and the page in `index.html`.
**Status:** proposal for review. URL paths are relative; mount them wherever the marketing site lives (e.g. under healthray.com).

---

## 1. Conversion model

| Level | Action | Where it appears | Destination |
|---|---|---|---|
| Primary | **Book a free demo** | Header, hero, end of every page, pricing (Enterprise) | `/pharmacy-software/demo/` (form) |
| Secondary | **Start free** (Starter plan, free forever) | Header, hero, pricing, feature pages | `https://pharmacy.healthray.com/signup` |
| Assist | **Call** +91 89808 80819 | Header (desktop), sticky mobile bar | `tel:` |
| Micro | Expiry-loss calculator, buyer's checklist PDF, GST guide | Resource pages, feature pages | Calculator page / email capture |
| Existing users | **Log in** | Header, footer | `https://pharmacy.healthray.com/login` |

**Rule:** every page ends with the same demo + start-free block. Each page has only **one** primary CTA style; the free start is always the secondary (outline) button.

---

## 2. Sitemap

```
/pharmacy-software/                          Home (hub) ← index.html today
│
├── features/                                Features overview (all modules, grouped)
│   ├── billing-pos/                         Billing & counter (FEFO, generic search, barcode, shortcuts, payments)
│   ├── expiry-batch-management/             Expiry & batch tracking (expiry report, returns, block expired sale)
│   ├── inventory-reorder/                   Stock, min/max, short book, reorder, purchase orders, non-moving
│   ├── purchase-supplier-management/        CSV / OCR bill import, scheme & free qty, supplier ledger, rate contract
│   ├── gst-billing-accounts/                HSN/GST, GST report, Tally XML, ledgers, vouchers, expenses
│   ├── reports-dashboard/                   Executive dashboard, 25+ reports, WhatsApp daily report
│   ├── customer-retention/                  Bill on WhatsApp, medicine reminders, loyalty cards, online orders
│   └── security-roles/                      Roles, privileges, 2FA, IP whitelisting, activity report
│
├── solutions/
│   ├── retail-medical-store/                Single store (Basic / Advance)
│   ├── pharmacy-chain/                      Multi-store (Enterprise: central admin, transfers, central reports)
│   ├── hospital-pharmacy/                   IPD/discharge, TPA, EMR tokens, ABDM, departments, kits & packages
│   └── new-pharmacy/                        Opening a store: set up items & stock from day one (Starter → Basic)
│
├── pricing/                                 4 plans, feature comparison table, GST note, FAQ
├── switch/                                  Switching from desktop software / Excel / registers (CSV import, parallel run)
├── compare/                                 "How to choose pharmacy software" + category comparison (no competitor bashing)
│
├── resources/
│   ├── expiry-loss-calculator/              Interactive calculator (from the home estimator)
│   ├── buyers-checklist/                    8 questions (PDF download → email capture)
│   ├── guides/gst-for-pharmacies/           GST rates, HSN, invoices for medical stores (reviewed by a CA)
│   ├── guides/expiry-management/            FEFO, expiry returns, near-expiry selling
│   ├── guides/schedule-h-h1-records/        Plain-language record-keeping guide (compliance-reviewed)
│   └── blog/                                Supporting articles (see §6)
│
├── support/                                 Help centre link, phone, walkthroughs, onboarding steps
├── demo/                                    Demo booking form (+ "what happens next")
├── about/  contact/                         Healthray company pages (link out if they already exist)
└── legal/  privacy · terms · refund · data-security
```

That makes **8 feature pages, 4 solution pages, 1 pricing, 1 switch, 1 compare, 6 resources, 4 utility pages**: a site that can be built in phases (§8).

---

## 3. Navigation

### Header (desktop)
`Logo` · **Features ▾** · **Solutions ▾** · **Pricing** · **Switch** · **Resources ▾** · · · `Log in` · `Start free` (outline) · **`Book a free demo`** (solid)

**Features mega-menu** (2 columns, each with a one-line benefit):

| Run the counter | Run the business |
|---|---|
| Billing & counter: *bill in seconds, nearest expiry first* | Reports & dashboard: *see the day on WhatsApp* |
| Expiry & batch: *catch expiry before it's a loss* | GST & accounts: *GST report, Tally export* |
| Stock & reorder: *short book → purchase order* | Customer retention: *reminders, loyalty, online orders* |
| Purchase & suppliers: *CSV/OCR bill import* | Security & roles: *2FA, IP whitelisting* |

A right-hand panel in the menu holds the dashboard screenshot (`v3/01-overview.png`) and a "See all features →" link.

**Solutions menu:** Retail medical store · Pharmacy chain · Hospital pharmacy · Opening a new pharmacy.
**Resources menu:** Expiry-loss calculator · Buyer's checklist · Guides · Blog · Help centre.

### Mobile
- Hamburger menu with the same groups as an accordion.
- A sticky bottom bar: **Call · Start free · Book demo**, already built in `index.html`.

### Footer (5 columns)
1. **Brand:** one-line positioning, phone, help centre, Contact Healthray.
2. **Features:** all 8 feature pages.
3. **Solutions:** 4 solution pages, Pricing, Switch.
4. **Resources:** calculator, checklist, 3 guides, blog.
5. **Account & company:** Log in, Sign up free, Support, About Healthray, Contact.

Bottom bar: © Healthray · Privacy · Terms · Refund · Data & security.

---

## 4. Page blueprints

Every page follows the same reader logic: **recognise my problem → see it solved in the product → trust it → act.**

### 4.1 Home (`/pharmacy-software/`)
Already built in `index.html`. Keep the section order:
- hero
- built-for strip
- pains
- expiry estimator
- day timeline
- feature tabs
- switching
- solutions
- buyer's checklist
- support
- pricing
- FAQ
- demo

Hero image: `v3/03-counter-billing.png` or `v3/01-overview.png` (pick after review).

### 4.2 Feature page template (all 8 use it)
| # | Section | Content |
|---|---|---|
| 1 | Hero | Pain-led H1 + one-line fix, demo + start-free CTAs, the feature's v3 screen |
| 2 | The problem in their words | 3 short counter-level scenarios |
| 3 | How it works | 3–4 steps with cropped product fragments (micro-UI style from `concepts/`) |
| 4 | Capabilities | 6–8 confirmed sub-features, each with a **plan badge** (Starter / Basic / Advance / Enterprise) |
| 5 | Related features | 3 cards linking to sibling feature pages (internal links) |
| 6 | Proof | Testimonial slot (only verified quotes) or the relevant calculator |
| 7 | FAQ | 4–6 questions specific to the feature (answers from the confirmed facts) |
| 8 | CTA block | Shared demo + start-free block |

Feature-specific notes:
- **Billing & counter:**
  - FEFO auto-batch, brand/generic search, barcode reader, F2/Ctrl+S.
  - Cash/card/UPI/credit, bill on WhatsApp, patient history & dues.
  - Image: `v3/03-counter-billing.png`.
- **Expiry & batch:**
  - Stock expiry report (item/supplier-wise), FEFO, purchase return + credit note, block expired sales.
  - Embed the expiry-loss calculator.
  - Image: `v3/04-expiry-control.png`.
- **Inventory & reorder:**
  - Min/max, stock alert, short book, reorder processing + PO (Advance), non-moving items, stock adjustment, label print.
  - Image: `v3/06-reorder-shortbook.png`.
- **Purchase & suppliers:**
  - CSV import (column mapping), OCR import (credits), scheme & free qty, PTR/margin, supplier ledger & dues, rate contract (Enterprise).
  - Image: `v3/05-purchase-import.png`.
- **GST & accounts:**
  - HSN/GST from item master, GST + bill-wise tax reports, Tally XML (Enterprise), ledgers, vouchers, expenses (Advance).
  - Image: `v3/07-gst-accounts.png`.
- **Reports & dashboard:**
  - Executive dashboard (Advance), margin/top-selling/collection/operator-wise reports, WhatsApp daily report.
  - Image: `v3/01-overview.png` + `v3/02-sales-performance.png`.
- **Customer retention:** WhatsApp/SMS/email bill sharing (credits), medicine reminders (Basic+), loyalty cards, online orders for pick-up/delivery.
- **Security & roles:** roles & privileges (Basic+), 2FA, IP whitelisting, force logout, activity report (Advance).

### 4.3 Solution pages
| Page | Hero promise | Must cover | Plan focus | Image |
|---|---|---|---|---|
| Retail medical store | "Faster bills, less expiry loss, clean GST — for one store" | Counter, expiry, purchase import, GST, WhatsApp report | Basic ₹4,999 / Advance ₹9,999 | `v3/03` |
| Pharmacy chain | "Every store on the same rules" | Central admin & linked outlets, transfers, central/multi-store reports, roles, Tally | Enterprise (custom) | `v3/01`, `v3/02` |
| Hospital pharmacy | "From doctor's prescription to pharmacy bill, connected" | EMR tokens & token board, IPD/discharge, TPA/payer companies, deposits, departments, kits/packages, ABDM/ABHA | Enterprise | `v3/08-hospital-pharmacy.png` |
| New pharmacy | "Start right on day one — free" | Starter plan, item/stock import, walkthroughs, upgrade path | Starter → Basic | `v3/05` |

### 4.4 Pricing (`/pricing/`)
1. **Plan cards (4):** Starter free · Basic ₹4,999/yr · Advance ₹9,999/yr (popular) · Enterprise custom. Note "+18% GST · 1/3/5-year terms · switch or cancel anytime".
2. **Full comparison table** generated from the public pricing API, grouped like the app:
   - Sales/Return
   - Purchase/Return
   - Staff management
   - Reports & accounting
   - Multi-store
   - Add-ons
3. **Credits explainer:** what uses WhatsApp/SMS/email/OCR credits.
4. **Pricing FAQ:** GST, renewal, upgrade/downgrade, what happens at the Starter limit, payment methods.
5. **CTA block.**

> Keep prices pulled from (or checked against) `https://pharmacyapi.healthray.com/api/subscription/pricing/public` before every release.

### 4.5 Switch (`/switch/`)
The #1 objection page.
- **Hero:** "Change software without stopping a single bill."
- **4 steps:** sign up → CSV import → walkthroughs → go live / upgrade.
- **"What the CSV can carry"** table, plus a sample-sheet download.
- **Run-both-in-parallel** explainer.
- **"Moving from…" tabs:** desktop software · general billing app · Excel/registers. Name the categories, not competitor brands, unless legal approves.
- **FAQ** and CTA.

### 4.6 Compare (`/compare/`)
An honest buyer guide.
- The 8-question checklist with our answers.
- A category comparison: desktop licence software vs general billing apps vs Healthray Pharmacy, on criteria the ICP ranks (staff learning time, FEFO, data import, renewal pricing, support, GST, multi-store).
- No unverifiable claims about named competitors.

### 4.7 Resources
- **Expiry-loss calculator:**
  - The home estimator, full page.
  - Shareable result ("₹X a year").
  - CTA "See your real expiry report in a demo".
- **Buyer's checklist:**
  - Gated PDF: name, phone, store, city.
  - Feeds the demo pipeline.
- **Guides:**
  - Long-form, evergreen.
  - Each links to 2 feature pages and the demo.
  - GST and Schedule H/H1 guides need CA/compliance review before publishing.

### 4.8 Demo (`/demo/`)
- **Form:** name, mobile, store/hospital name, city, current software, store type, intent.
- **"What happens next":** 3 steps.
- **Phone fallback.**
- **No navigation distractions:** minimal header.

---

## 5. Image map (v3 screens → pages)

| Screen | Primary page | Also used on |
|---|---|---|
| `v3/01-overview.png` | Reports & dashboard | Home (option), Features overview, mega-menu, Chain |
| `v3/02-sales-performance.png` | Reports & dashboard | Chain, Pricing (Advance column) |
| `v3/03-counter-billing.png` | Billing & counter | Home hero (option), Retail store |
| `v3/04-expiry-control.png` | Expiry & batch | Calculator, Expiry guide |
| `v3/05-purchase-import.png` | Purchase & suppliers | Switch, New pharmacy |
| `v3/06-reorder-shortbook.png` | Inventory & reorder | Retail store |
| `v3/07-gst-accounts.png` | GST & accounts | GST guide, Compare |
| `v3/08-hospital-pharmacy.png` | Hospital pharmacy | Solutions menu |

Every screen is labelled on-page as *"Sample screen · illustrative data"*.

---

## 6. SEO keyword map (India)

Search volumes have not been pulled yet. Validate with Ahrefs/GSC before writing, and keep one primary keyword per page.

| Page | Primary keyword | Supporting keywords |
|---|---|---|
| Home | pharmacy management software | pharmacy software India, medical store software |
| Billing & counter | medical store billing software | pharmacy billing software, pharmacy POS software |
| Expiry & batch | pharmacy expiry management software | batch tracking pharmacy, medicine expiry alert software |
| Inventory & reorder | pharmacy inventory management software | medical store stock management, short book software |
| Purchase & suppliers | pharmacy purchase management software | import distributor bill, pharmacy OCR bill entry |
| GST & accounts | GST billing software for medical store | pharmacy accounting software, Tally export pharmacy |
| Reports & dashboard | pharmacy reports software | medical store sales report |
| Retail store | software for medical shop | chemist shop software |
| Pharmacy chain | pharmacy chain management software | multi-store pharmacy software |
| Hospital pharmacy | hospital pharmacy management software | IPD pharmacy billing, pharmacy software with EMR |
| Pricing | pharmacy software price | medical store software price, free pharmacy software |
| Switch | (brand-neutral) change pharmacy software | migrate pharmacy data |
| Calculator | medicine expiry loss | — |
| Guides | GST on medicines, Schedule H1 register, FEFO | — |

Technical basics:
- One H1 per page.
- `SoftwareApplication` + `Offer` schema on Pricing.
- `FAQPage` schema on FAQ blocks.
- `BreadcrumbList` on all inner pages.
- Descriptive alt text on product screens.
- The app's `noindex` subdomain stays out of the marketing sitemap.

---

## 7. Internal linking & conversion paths

- **Owner-pharmacist (Google):** "medical store billing software" → Billing page → Expiry page → Pricing → Start free / Demo.
- **Switcher:** Home → Switch → Compare/Checklist (email captured) → Demo.
- **Chain ops head:** Solutions → Pharmacy chain → Reports & dashboard → Demo (Enterprise).
- **Hospital admin:** Solutions → Hospital pharmacy → Demo. Cross-link to Healthray's EMR/HMS pages.
- **Content reader:** GST guide → GST & accounts page → Pricing.

Rules:
- Every feature page links to 3 siblings plus its solution page.
- Every guide links to 2 feature pages plus the demo.
- Breadcrumbs everywhere.

---

## 8. Build phases

**Progress (13 Oct 2026):**
- **Built:** shared header/footer (PHP partials), Home (`index.php`), About, Contact, Support, Demo, and the legal drafts (Privacy, Terms, Refund, Data & security).
- **Not built yet:** every other page. Its links fall back to home-page sections via `PAGES` in `partials/config.php`.

| Phase | Pages | Why first |
|---|---|---|
| 1 | Home (done), Pricing, Demo, Billing, Expiry & batch, Switch | Covers the top pains, the #1 objection, and both CTAs |
| 2 | Inventory, Purchase, GST & accounts, Retail store, Hospital pharmacy | Completes the core modules and the second segment |
| 3 | Reports, Customer retention, Security, Pharmacy chain, New pharmacy, Compare | Depth and mid-funnel |
| 4 | Calculator page, Checklist PDF, 3 guides, blog | SEO and lead capture |

---

## 9. Measurement

- **Primary KPIs:** demo form submissions, Start-free clicks (sign-ups via the app), call clicks.
- **Secondary KPIs:** calculator completions, checklist downloads, pricing-page visits → CTA rate, scroll depth to the Switch section.
- **Tagging:**
  - Add `data-cta` values per button type, as already used in `index.html`.
  - Add UTM parameters on links to `pharmacy.healthray.com/signup`, so sign-ups can be attributed.

## 10. Open items before build
- Official Healthray logo file for the marketing site.
- A WhatsApp sales number.
- Support hours and languages.
- Offline behaviour.
- Demo form endpoint, privacy notice and consent text.
- The final domain/path for the marketing pages.
- Verified testimonials.
- CA review for the GST guide and compliance review for the Schedule H/H1 guide.
