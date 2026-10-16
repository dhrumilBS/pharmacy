# CLAUDE.md — Pharmacy software marketing site (brand name pending)

Marketing site for a browser-based pharmacy management app (brand name not final; the product currently runs on Healthray infrastructure). It targets Indian retail medical stores (primary), pharmacy chains, and hospital pharmacies. Multi-page PHP site with shared partials (no framework, no build step), served by XAMPP. It's written to port easily to WordPress templates later.

## Brand name is not final

- **Working name: "Rxtiva"** (temporary). It lives only in `BRAND` in `partials/config.php`. `logo()`, `page_title()`, the footer and home copy (`$brand` in `index.php`, the tour URL bar, the demo-slip code) all read it, so a rename is a one-line change. Never type the name into pages.
- **Don't rebrand images:** product screenshots stay as rendered.
- **Never show "Healthray"** in page copy, titles, alt text, screenshots or graphics.
- **Logo (final: option C, "blister pack"):** `#logo-mark` in `partials/sprite.php`, also `assets/img/logo-mark.svg` (plus `logo-mark-dark.svg` and `logo-mark-mono.svg`). A deep-navy rounded square with four capsule dots; the teal one is the nearest-expiry batch picked first. The favicon is the same file.
- **Healthray URLs:** they remain only as link targets in `config.php` (app login/signup, help centre, calendar) because those services still live there. Never print them as text.
- **Research docs:** the strategy docs mention Healthray only as the source of verified facts.

## Design choices (final)

The design-variant review is finished and the switcher, `variants.js`, `variants.css`, the losing partials and the logo options A, B, D, E and F were deleted (they remain in git history). The chosen combination, which is what the home page now renders without any switch:

- **Hero:** product-style hero with the demo slip on the right (no screenshot).
- **"Why switch" band:** none.
- **Features:** the tabs with screens only (no live cards).
- **Logo:** C (see Brand name above).
- **"Sound familiar?":** the message inbox (`partials/problems-chat.php`).

## Files

| File | Role |
|---|---|
| `index.php` | Home page (sections listed under "Page structure") |
| `about.php`, `contact.php`, `support.php`, `demo.php` | Company pages |
| `pricing.php`, `faq.php`, `switch.php` | Pricing (renders live from the public pricing API via `partials/pricing-data.php`: `PRICING_API`/`PRICING_TTL` in `config.php`, 5-minute cache in git-ignored `partials/data/cache/`, falls back to the last good copy and then to the committed snapshot `partials/data/pricing.json`; FAQ and switch pages read prices through the same helpers, so never hard-code a price), FAQ (33 Qs, FAQPage schema for confirmed answers only), switching guide. Styles in `assets/css/pages.css`, term switch and the collapsible comparison-table groups (first open, rest collapsed, "Expand all"; all open without JS) in `assets/js/pricing.js` |
| `features/index.php` | Features overview (the "See all features" link): "start with your problem" index (pain → page), the two groups as module cards (labels from `NAV_FEATURES`, plans live), hospital/chain block, a live plans table, FAQ. Styles are the `fi-` rules at the end of `feature.css` |
| `features/*.php` | Feature pages, all on one template. "Run the counter": `billing`, `expiry`, `inventory`, `purchase`. "Run the business": `reports`, `gst`, `retention`, `security` (related row titled via `related_title`). Each page has its own v2 screen; see `screenshots/v2/README.md` for which screen goes where. They live in `features/`, so they require `__DIR__ . '/../partials/…'`. Data only (`$fp` array); layout in `partials/feature-page.php`, styles in `assets/css/feature.css`. Section order: hero → CTAs + screen → "Sound familiar?" scenes → **leak register** (bill-book style cards, 4 per row: "Leak 01", the leak, a dashed tear line, "How it's plugged", then "Plan: …" on a footer line; tear lines align across a row via CSS subgrid. The 8th card is the worked example with the sum written out, or a plain demo card on `expiry.php`, which also embeds the expiry estimator. The earlier table version is archived, viewable and `noindex`, in `archive/features/v1/`) → how it works → capabilities → practical guide → "check it yourself" demo checklist (stands in for testimonials) → related → FAQ (+ FAQPage/BreadcrumbList schema). Plan badges come live from the pricing API via `feature_plan_label('feat_*')` in `partials/pricing-data.php`; never hard-code a plan name. Worked examples are illustrative and carry a sample tag |
| `blog/index.php`, `blog/<slug>.php` | Blog & guides (registered as `blog` and `guides`; in the Resources menu). Hub: topic pills (`?topic=`, works without JS), featured guide + "Start here" reading path, card grid, tools strip. Posts: post metadata in `partials/data/blog.php` (add a post there + a `blog/<slug>.php`); layout and article components in `partials/blog.php` (`blog_start()` / `blog_end()`, auto TOC from h2s, Article/FAQ/Breadcrumb schema; components `bk_takeaways`, `bk_note` tip/note/caution/example, `bk_table`, `bk_compare`, `bk_sum`, `bk_rule`, `bk_checklist`, `bk_steps`, `bk_cta` inline/feature/panel). Styles `assets/css/blog.css`, behaviour (progress bar, TOC highlight, copy link, print) `assets/js/blog.js`. Covers are crops of the relevant v2 product screen. Pills are allowed here for topic tags, the topic filter and comparison tags. No GST-rate or Schedule H/H1 posts until CA/compliance review |
| `partials/estimator.php`, `assets/css/estimator.css` | Expiry loss estimator, shared by home `#estimator` and `expiry.php` (set `$est_cta`). Behaviour in `site.js` |
| `privacy.php`, `terms.php`, `cookie-policy.php`, `refund-policy.php`, `data-security.php` | Legal drafts: original text, structure informed by reference sites, no brand names. All except data-security are `noindex`. `partials/legal-layout.php` auto-wraps every `[bracketed placeholder]` in `data-confirm`, so `?review` outlines them. Counsel must fill placeholders and draft liability/indemnity |
| `BRAND-NAME-OPTIONS.md` | Brand-name shortlist (Kapstik, MedLekha, DawaSahi, …) with conflict and domain checks. Indicative only; needs registrar and trademark checks |
| `partials/config.php` | **Single source of truth:** contacts, real links, `PAGES` registry, and the `NAV_*` menus used by both header and footer |
| `partials/head.php`, `header.php`, `footer.php` | Shared head, header (mega-menus) and footer (CTA band, 5 columns, mobile bar, scripts) |
| `partials/page-hero.php`, `legal-layout.php`, `demo-form.php`, `sprite.php` | Inner-page hero (faint blister-pack background pattern, on/off for all inner pages with `HERO_PATTERN` in `config.php`, per-page override `$hero['pattern']`), legal page layout (TOC + prose), demo form, SVG icon sprite |
| `assets/css/site.css` | Base tokens and components (extracted from the archived single-file page) |
| `assets/css/layout.css` | Header, mega-menus, footer, CTA band, home hero, inner pages, legal prose |
| `assets/js/site.js` | All behaviour: menus, tabs, FAQ, estimator, form. One IIFE; every block checks its elements exist |
| `assets/img/` | Web JPGs exported from `screenshots/v2/` by `scripts/export-images.ps1`. Don't edit by hand |
| `archive/` | Old versions: `index-2026-10-13.html` (previous single-file page) and `v1/`. Reference only |
| `PHARMACY-ICP-AND-UX-STRATEGY.md` | **Read first for any copy or section change.** §0 lists the verified product facts and plans. Also covers ICP, pains, psychology, selection criteria, objections, section order, nav/footer and tone |
| `PHARMACY-DESIGN-SYSTEM.md` | Color tokens, typography, spacing, components, accessibility bar |
| `PHARMACY-SOFTWARE-PAGES-AND-DEVELOPMENT.md` | Product areas, India regulatory notes (Drugs Rules / H1, GST, DPDP), trust rules, open questions |
| `pharmacy-design-system-editable-fixed.svg`, `pharmacy-design-system-crisp-2x.png` | Design-system poster (vector + 2× PNG). Not used by the page |
| `org-dashboard/*.png` | Screenshots of the real app (sale screen, menus). Reference only, not published |
| `screenshots/` | Website-ready product screens: HTML recreations of the app with dummy data. See `screenshots/README.md`. Edit `src/*.html`, then re-render with `screenshots/render.ps1`. Keep numbers consistent across screens and with the page samples. `dummy-data/` holds CSVs for loading a demo account |
| `PHARMACY-SITE-STRUCTURE.md` | Multi-page sitemap, navigation, page blueprints, image map, SEO keyword map, build phases. Read before adding pages |
| `screenshots/v2/`, `screenshots/v3/` | Newer product-UI designs with dummy data. v3 (`src/style.css`, `render.ps1`) is the current "product shot" dashboard style. Never edit older versions when making new ones |

An older, generic prototype lives in `../pharmacy-chatgpt/`. It's superseded, so don't copy its copy.

## Verifying product facts

Every product claim must be traceable to the real app. Sources, all public and read-only:

- **Pricing and plan limits:** `https://pharmacyapi.healthray.com/api/subscription/pricing/public` (JSON with plans, features, and per-plan statuses). Re-check it before editing pricing, and keep the page in sync with it.
- **Modules and wording:** the app's JS bundle at `https://pharmacy.healthray.com/assets/*.js`.
  - Routes and menu labels live in the `AppShell-*.js` chunk.
  - The in-app walkthrough "benefits" lists are Healthray's own feature copy.
- **Screens:** `org-dashboard/` screenshots.

Logging in is not needed for these checks. Never enter credentials yourself; the user logs in if a screen must be inspected.

## Run / preview

Served by XAMPP Apache: `http://localhost/pharmacy/` (PHP 8.3). Add `?review` to outline every unconfirmed claim. Lint with `D:\xampp\php\php.exe -l file.php`. If you deploy elsewhere, change `BASE` in `partials/config.php`.

To add a page:
1. Copy an inner page (e.g. `about.php`): set `$title`, `$description` and `$page_key`, then include head → header → content → footer.
2. Register it in `PAGES` with `'file' => 'name.php'`. Header and footer links switch from the home-page fallback anchor to the new page automatically.

Images: render screens in `screenshots/v2/` (`render.ps1`), then run `scripts/export-images.ps1` to refresh `assets/img/`. It writes a 1x and an `@2x` JPG at quality 92. Show them with `img_tag()` (config.php), which outputs `srcset` and `sizes`, so the browser resamples once and text stays sharp. Don't hand-write `<img>` tags for product screens, and don't rotate or tilt screenshots, because that blurs them.

**Features screens are vector SVG** (`assets/img/screens/v2-*.svg`, shown with `svg_img()` in `config.php`), not JPG: they stay sharp at any zoom or pixel density. They are built from `screenshots/v2/src/*.html` by `scripts/make-svg-screens-extract.mjs` + `scripts/make-svg-screens.py` (usage in the script header; needs `playwright-core`, Edge, fontTools and brotli). Each SVG holds the screen as HTML in a `foreignObject` with a subsetted Campton font embedded, so it works through `<img>`. After editing a screen's HTML, re-run both scripts. The JPGs from `export-images.ps1` are still used elsewhere.

The in-app browser pane sometimes reports a 0-width viewport. For reliable screenshots use headless Edge:

```bash
"/c/Program Files (x86)/Microsoft/Edge/Application/msedge.exe" --headless=new --disable-gpu --hide-scrollbars --window-size=1440,900 --screenshot=out.png http://localhost/pharmacy/
```

Headless Edge clamps the window to about 500px minimum. To test true phone widths (375px), load the page in a 375px-wide iframe and pass `--allow-file-access-from-files`. Check that `scrollWidth` is at most 375.

## Copy rules (from the ICP doc)

- **Write for a sceptical switcher**: an owner-pharmacist who already uses old desktop software, registers, or a generic billing app. The #1 fear is billing stopping during the switch, not price.
- **Frame value as loss avoided** (expiry, rate differences, lost sales) before growth.
- **Use counter-level words**: strip, batch, distributor bill, scheme, free quantity, udhaar/credit, rush hour. Avoid hype: "AI-powered", "seamless", "revolutionise", "100% compliance".
- **Calm about compliance**: no scare tactics. The app *tags* Schedule H/H1 items; never claim it guarantees compliance or keeps an H1 register (unverified).
- **Name the plan when a feature is plan-gated**, e.g. "Tally XML export (Enterprise)" or "purchase orders (Advance)". See ICP §0.
- **Use Indian number formatting** (`₹8,00,000`, via `Intl.NumberFormat('en-IN')`). Use sentence case for headings and buttons.
- **Label all sample data**: every mock screen or number carries a `.sample-tag`. Never present it as customer results.
- **No fabricated testimonials, logos, ratings or user counts.** The one exception is the trusted-by placeholder strip, which is flagged `data-confirm` and must be replaced before launch.
- **Do not claim** (not found in the app): an owner mobile app, rate-difference alerts, substitute suggestions, rack/shelf locations, offline billing, a 14-day trial, assisted migration, support hours or languages, or automatic GST-rate updates.

## Unconfirmed claims: `data-confirm`

Anything not verified against the app gets the attribute `data-confirm`, and `?review` outlines these elements. Currently flagged:
- offline behaviour
- full data export
- assisted import help
- support hours and languages
- the H1 register
- demo callback time
- the PDF checklist
- privacy and legal links

Rules:
- Never remove `data-confirm` without owner confirmation or verification against the app.
- The offline answer is intentionally a bracketed placeholder. Do not invent one.

## CTA model and real links

- **Book a free demo** (primary):
  - On the home page it's `#demo`; everywhere else it's `demo.php` (via `url('demo')`).
  - Add `data-cta` with `demo` | `quote` (Enterprise pricing) | `checklist`. JS preselects the form's "I'd like to" option and sets the submit label. `demo.php?want=quote` also works.
- **Sales:** phone `+91 97148 74435` (from healthray.com/contact) and the Calendly demo link in `config.php`.
- **Company facts:** Healthray Technologies Pvt. Ltd., Millenium Point, Surat (healthray.com). `contact@healthray.com` was decoded from their footer and is still `data-confirm`.
- **Start free / Choose Basic / Choose Advance**: `https://pharmacy.healthray.com/signup` (free Starter plan; no trial exists).
- **Log in**: `https://pharmacy.healthray.com/login`, in the header and footer.
- **Support**:
  - Phone `+91 89808 80819`, as shown in the app header.
  - Help centre `https://support.healthray.com`.
  - Contact `https://healthray.com/contact/`.
- **No WhatsApp number is verified.** Don't add `wa.me` links until one is supplied.
- **Demo form:** local-only, sends nothing. Do not wire it to an endpoint until privacy notice, consent and data handling are agreed.

## Page structure

Section order is deliberate. Each section answers the reader's next question (ICP doc §9):

Home (`index.php`): `#top` hero → trusted-by marquee → `#tour` → `#problems` → `#estimator` → `#day` → `#features` (7 tabs, each with a v2 screen) → `#switching` → `#solutions` → `#checklist` → `#support` → `#faq` → `#demo`. The home page sets `$hide_cta = true` because it has its own demo section.

- **No pricing on the home page** (owner decision). Link to `url('pricing')` instead; don't add prices back to the hero or home sections.
- **Hero is lead-first (CRO):** one goal, a demo call. `partials/hero-lead.php` is the 2-field "demo slip" (name + mobile, with a barcode). Set `$lead_id` before including it. `site.js` (`[data-lead-form]`) validates it, shows the inline thank-you, and prefills `#demo`. It's local-only.
- **Trusted-by marquee** (`partials/trusted-strip.php`): logos are SVG files in `assets/img/trusted/`, shown as `<img>` with the business name in `alt` (for SEO). Logos are picked up automatically from that folder (filename = business name, e.g. `green-cross-pharmacy.svg`; drop real files in, delete the placeholders; no code change). It scrolls continuously and never pauses, including on hover, focus and reduced motion (owner decision; conflicts with WCAG 2.2.2). The names are **invented placeholders** (built by `scripts/make-trusted-logos.ps1`) and the strip is `data-confirm`. Replace the files with real, permissioned customer logos before launch.
- **Hero (final):** copy on the left, the demo slip (`hero-lead.php`, `$lead_id = 'hl1'`) in the right-hand `.hero3-form` column, no product image. The "Switching made safe" section has a "Book a free demo" button (`.switch-cta`, `data-cta="demo"`) and the "Who it's for" cards carry no links.
- **Features scroll:** `[data-scroll-tabs]` (`.feat-track`) makes the tab block sticky at ≥1025px. Scrolling advances the tabs and clicking a tab scrolls to its stretch (`site.js`). Below that width they're normal tabs.
- **Section surfaces** follow styles.refero.design: `.bg-soft` sections and `#demo` become filled, rounded panels (32px radius) and cards use fills, not borders. Problems is an interview-style list (`.pr`), the day section is a shift register (`.shift`), and the estimator is a filled panel with a white result card.
- **Product tour** (`partials/product-tour.php` + `assets/js/tour.js`): a scripted DOM animation in a browser frame, designed at 1200×640 and scaled to fit. It has only two controls, play/pause and full screen; no audio, no captions. It autoplays when on screen (not under reduced motion), restarts from 0 s each time it scrolls back into view (unless the visitor paused it), and loops. There is no video file. Edit the script block in `tour.js` (`at()/move()/click()/type()` in seconds) and keep sample numbers consistent with the screenshots. For review screenshots, `?tour-at=15` freezes it at 15 s.
- **Type: Campton only (final).** No other webfonts are loaded anywhere: no Google Fonts, no `@import`. The source OTFs are in `font/campton/` on the owner's disk only: that folder is **git-ignored** and was purged from git history, because Campton is a commercial font and the repo is public. Only the subset WOFF2 files in `assets/fonts/campton/` are tracked (web fonts cannot be truly encrypted, since browsers must read them; WOFF2 is compressed, subset and not directly installable). `scripts/make-webfonts.py` (fontTools + brotli) converts weights 300–800 plus 400 italic to subset WOFF2 files (~20 KB each) in `assets/fonts/campton/`. `assets/css/fonts.css` holds the `@font-face` rules and points every font token (`--font-head`, `--font-body`, `--font-display`, `--font-eyebrow`, `--mono`) at Campton. `head.php` loads it right after `site.css` and preloads 400 and 700. Use `font-variant-numeric: tabular-nums` where a monospace look is wanted. Eyebrows are small uppercase Campton labels whose marker is a tiny two-tone capsule (`layout.css`). The trusted-by SVG logos keep their own type because they stand in for customer logos.
- **Spacing:** `.section` is 72px (56px at ≤900, 44px at ≤600). Two white sections in a row get a 24px top on home. Home panels use 64px padding.
- **Mobile:** at ≤767px `.container` is full width with 12px side padding, and home panels go full-bleed with 12px padding. Every page was checked at 360/375/390/414/768/1024 with no horizontal overflow; re-check after layout changes.
- **"Sound familiar?"** is a message inbox in `partials/problems-chat.php`, built on ARIA tabs (handled by the generic tabs block in `site.js`) with a brief typing indicator.
- **No pills or decorative capsules** (owner feedback, 15 Oct 2026: the site looked "AI-made"). Prefer ruled lines, real tables, plain numbers and written labels; small radii (8–12px). Sample tags are plain caption text, FAQ topic links are a dotted index line, "Most popular" is a tab on the card's top border. Pill shapes stay only in the eyebrow marker, the estimator slider thumbs and inside product-screen mocks (they imitate the app).
- **Playful details** (keep to these two): the capsule eyebrow marker, and the capsule-shaped estimator slider thumbs (`--fill` set by `site.js`). The inbox typing dots belong to the "Sound familiar?" inbox.
- **Tour cursor:** it moves only by `transform: translate()` (`will-change: transform`). Its shadow is drawn inside the SVG; don't add a CSS `filter` or animate `scale` on `.t-cursor`, because that caused smeared trails. The press effect scales the inner `.t-arrow`. `click()` snaps the tip onto its target if a layout shift left it outside.
- **Features panels:** the screen comes first, then its copy, at all widths. At ≥1025px the tabs sit in a row above the panel.
- **No "India"/"Indian" in marketing copy** (going global). The legal pages were rewritten jurisdiction-neutral on 15 Oct 2026: governing law, hosting region and tax handling are `[bracketed]` for counsel, and regimes such as GDPR, CCPA and India's DPDP appear only as "may include" examples. Plan prices are ₹ on the pricing page; the terms page points to it instead of repeating figures.

**Header:**
- Features ▾ (mega-menu, 2 groups + promo image), Solutions ▾, Pricing, Switch, Resources ▾.
- Then Log in · Start free · Book a free demo.
- Below 1024px it becomes a full-screen menu. Don't put `backdrop-filter` on `.hd` at that size: it breaks the fixed menu panel.

**Footer:** brand/contacts/social + Features · Solutions · Resources · Company, then legal links.

Pages not built yet (features, solutions, pricing, switch, resources) are listed in `PAGES` with a `fallback` anchor on the home page. Build them in the order in `PHARMACY-SITE-STRUCTURE.md` §8.

## Code conventions

- **Tokens:** use the CSS custom properties in `:root` (navy/blue/teal scale). Don't add one-off hex values. Blue is the action color. Teal marks positive/check states only.
- **Layout:** `.container` is max 1200px. Sections use `.section` or `.section-tight`, with `.bg-soft` for alternating backgrounds. The hero stays white; the footer is the darkest surface.
- **Breakpoints:** 1100 / 900 / 767 / 600 / 420px. `.price-grid` is 4 columns, 2 at ≤1100px, and 1 at ≤600px.
- **Icons:** use the sprite in `partials/sprite.php`, `<svg class="icon" aria-hidden="true"><use href="#i-name"/></svg>`. Add new symbols there. Don't use emoji or icon fonts.
- **PHP:** escape all output with `e()`. Build links with `url('key')` and `asset('path')`, never hard-coded paths. Keep page files thin: data at the top, markup below.
- **Legal pages:** keep the draft notice and `[brackets]` until Healthray legal/finance signs off. Never invent refund terms, retention periods, a grievance officer, or hosting details.
- **Accessibility (WCAG 2.2 AA):**
  - Feature tabs follow the WAI-ARIA tabs pattern (arrow, Home and End keys; roving `tabindex`).
  - FAQ buttons use `aria-expanded` plus `hidden` panels.
  - The mobile menu closes on Escape.
  - Status is never shown by color alone.
  - Respect `prefers-reduced-motion`.
- **JS:** a single vanilla IIFE in `assets/js/site.js`, loaded with `defer`. ES5-style is fine. No frameworks. Guard every block with an element-exists check.
