# Healthray Pharmacy — Design System

**Status:** Current (v3). Matches the live CSS and the poster.  
**Source of truth:** `assets/css/site.css` (tokens), `assets/css/fonts.css` (type), `assets/css/layout.css`, `assets/css/blog.css` (articles). Use this document when extending the site.  
**Last reviewed:** 26 October 2026.

The system is a clear, calm pharmacy-workflow product: one typeface, one action colour (blue), white surfaces with soft fills instead of borders, and dark navy kept for the footer and the closing CTA. Product claims are checked against the live app; items marked `data-confirm` and all demo data still need owner approval.

## Brand assets

- **Poster:** `design-system/healthray-design-system.png` (2×) and `.svg` (fonts and logos embedded). Source `design-system/design-system.html`; rebuild with `node design-system/build.mjs` (needs `playwright-core` in `wordpress/tools` and Edge). The older `pharmacy-design-system-*.svg/png` files are superseded.
- **Logo:** `assets/img/healthray-logo.svg` (red heart, blue wordmark and tagline), shown 52px tall by `logo()` / `rx_logo()`. On dark surfaces use `healthray-logo-dark.svg` (white text). Do not redraw or recolour it.
- **Favicon:** the app's own `favicon.ico` and PNGs (blue heart, red badge).
- **Name:** "Healthray" lives only in `BRAND` (`partials/config.php`) and the WordPress Site Title. Never type it into pages.
- **Product screens:** HTML recreations with dummy data (`screenshots/`). Features screens are vector SVG. Do not rotate or tilt them.

## Colour tokens

Use the CSS variables in `:root` (`site.css`). Never add one-off hex values.

| Token | Value | Use |
|---|---|---|
| Navy 950 | `#081C2C` | Headings |
| Navy 900 | `#0D2A40` | Footer, dark CTA, dark button |
| Navy 800 | `#123B5D` | Brand text, eyebrow |
| Navy 700 | `#174C75` | Secondary dark |
| Navy 600 | `#1D6090` | Supporting tone |
| Blue 700 | `#1D4ED8` | Links, hover, highlighted table text |
| Blue 600 | `#2563EB` | Primary action, focus ring |
| Blue 500 | `#3B82F6` | Charts, small highlights |
| Blue 100 | `#DBEAFE` | Soft emphasis, note line |
| Blue 50 | `#EFF6FF` | Notes, home panels |
| Teal 700 / 600 / 500 | `#0F766E` / `#0F9F9A` / `#14B8A6` | Positive and check states, chart accent |
| Teal 100 / 50 | `#CCFBF1` / `#F0FDFA` | Soft teal fills |
| Text | `#172033` | Body copy |
| Text 2 | `#475569` | Secondary copy |
| Muted | `#64748B` | Labels (check contrast at the actual size) |
| Border / Border 2 | `#E2E8F0` / `#CBD5E1` | Dividers / control edges |
| Surface / Background | `#FFFFFF` / `#F8FAFC` | Canvas / soft sections and cards |
| Success | `#15803D` | Positive state |
| Warning | `#B45309` | Attention state |
| Danger | `#DC2626` | Error state |

### Colour use

- Blue is the action colour. Teal marks positive and check states only; it is not a second brand colour.
- Keep the hero white. The footer and the closing CTA band are the only dark surfaces.
- Status is never shown by colour alone: pair it with a shape and a word (green dot "Stock healthy", amber square "Expires in 30 days", red diamond "Out of stock").
- Pale fills are never used as text colours. Validate each foreground and background pair in context.

## Typography: Campton only

- One typeface for headings, body, labels and numbers: **Campton**, self-hosted WOFF2 in `assets/fonts/campton/` (weights 300, 400, 400 italic, 500, 600, 700, 800). Rules are in `assets/css/fonts.css`; `head.php` preloads 400 and 700.
- **No other webfonts**: no Google Fonts, no `@import`. All font tokens (`--font-head`, `--font-body`, `--font-display`, `--font-eyebrow`, `--mono`) point at Campton.
- The source OTFs are git-ignored (commercial font). Only the subset WOFF2 files are tracked; `scripts/make-webfonts.py` builds them.
- Use `font-variant-numeric: tabular-nums` where a monospace look is wanted.

| Role | Weight | Size and line height | Letter spacing |
|---|---|---|---|
| Display h1 | 700 | `clamp(36px, 4.8–5.2vw, 56–60px)`, 1.06; page hero `clamp(32px, 4.4vw, 48px)`, 1.1 | −0.025em |
| h2 | 700 | `clamp(28px, 3.4vw, 40px)`, 1.2; article h2 `clamp(24px, 2.6vw, 30px)` | −0.015em |
| h3 | 600–700 | 19–22px, 1.25 | −0.01em |
| Body | 400 | 16px, 1.62 (article body 17–18px, 1.7) | 0 |
| Small | 400–500 | 14–15px, 1.5 (minimum 12px, labels only) | 0 |
| Eyebrow | 600 | 12px, uppercase | +0.08em |

- Sentence case for headings and buttons.
- Eyebrows are small uppercase labels, Navy 800, with a 7px blue rounded-square marker (`.eyebrow::before`).
- Use Indian number formatting for figures (`₹8,00,000`, `Intl.NumberFormat('en-IN')`).

## Spacing, radius, elevation

- **Rhythm:** 8px. Scale in use: 8, 12, 16, 24, 32, 48, 64, 72.
- **Sections:** `.section` is 72px (56px at ≤900, 44px at ≤600). Two white sections in a row get a 24px top on home. Home panels use 64px padding.
- **Container:** max 1200px with 24px gutters. At ≤767px it is full width with 12px side padding and home panels go full-bleed.
- **Radius:** 8px (`--r-sm`, buttons and fields), 12px (`--r-md`, tables and images), 16px (`--r-lg`, cards and notes), 24px (`--r-xl`, feature surfaces), 32px (home panels and `#demo`).
- **Shadow:** `--shadow-sm` `0 1px 2px rgba(8,28,44,.06)`; `--shadow-md` `0 10px 30px -12px rgba(8,28,44,.18)`; `--shadow-lg` `0 30px 60px -24px rgba(8,28,44,.28)`. Prefer fills over shadows.
- **Breakpoints:** 1100 / 900 / 767 / 600 / 420px. `.price-grid` is 4 columns, 2 at ≤1100 and 1 at ≤600. Every page is checked at 360, 375, 390, 414, 768 and 1024 with no horizontal overflow.

## Components

### Buttons and links

- **Primary** `.btn-primary`: Blue 600 fill, white text, hover Blue 700. One per view.
- **Secondary** `.btn-secondary`: white fill, Navy 800 text, Border 2 outline.
- **Dark** `.btn-dark` and **light** `.btn-light` / `.btn-ghost-light` for dark surfaces.
- **Text link:** Blue 700, with an arrow for "read more".
- Height 48px in content, `.btn-sm` 40px. Radius 8px. Weight 600. Sentence-case labels.
- Links must be distinguishable without hover and have descriptive labels.

### Form fields

- Label above (14px, 600), 48px field, 1.5px Border 2 edge, 8px radius.
- **Focus:** Blue 600 edge plus a 3px ring (`:focus-visible` outline `3px solid var(--blue-600)`, 2px offset).
- **Error:** Danger edge plus a written message under the field. Helper text is 13px Text 2.
- Forms are local-only until privacy, consent and data handling are agreed.

### Surfaces

- **Cards** sit on a fill (`--bg` `#F8FAFC`, 16px radius), not a border.
- **Home panels** (`.bg-soft`, `#demo`, estimator): filled, rounded 32px.
- **Notes** (tip, note, caution, example, verdict): Blue 50 fill with a 1px Blue 100 inset line; no icon and no label.
- **Short version** box: `--bg` fill with an uppercase Navy 800 heading and a bullet list.
- **Dark band:** Navy 900, light text, ghost button. Footer and closing CTA only.

### Tables (articles)

- Blog and WordPress tables use CSS variables `--tb-head`, `--tb-zebra`, `--tb-line`, `--tb-hl-head`, `--tb-hl`, set per post by a `.tb-N` body class. Five palettes: 1–4 are slate-blue tints, 5 is plain grey. Default: head `#E4EAF2`, zebra `#F4F7FB`, line `#D7E0EC`.
- Highlighted column: Blue 600/700 text on a light blue fill, no border.
- Maximum four columns. Rounded 12px, 1px inset line.

### Status and sample data

- Status markers combine a shape (dot, square, diamond) with a word and a colour.
- Every mock screen or number carries a `.sample-tag`: plain caption text (12px, 600, Muted), not a pill.

### Icons and motion

- Icons come from the SVG sprite in `partials/sprite.php` (`<svg class="icon" aria-hidden="true"><use href="#i-name"/></svg>`). One stroke weight. No emoji or icon fonts.
- Motion is brief and optional. Respect `prefers-reduced-motion`; no meaning depends on animation. The home trusted-by marquee is the one owner-approved exception.
- The only playful details: the eyebrow marker and the capsule estimator slider thumbs. The tour cursor moves by `transform: translate()` only.

## Rules

**Do**
- Use tokens from `:root` only.
- Use ruled lines, real tables, plain numbers and written labels.
- Label every sample number and screen.
- Name the plan when a feature is plan-gated (`feature_plan_label()`, never hard-coded).
- Keep focus rings and 4.5:1 text contrast.

**Don't**
- Add pills or decorative capsules (owner feedback, 15 Oct 2026: the site looked "AI-made"). Pill shapes remain only in the eyebrow marker, the estimator thumbs and inside product-screen mocks.
- Load a second webfont.
- Use emoji or icon fonts.
- Rotate or tilt screenshots.
- Show status by colour alone.
- Invent counts, ratings or testimonials, or claim features not found in the app (see `PHARMACY-ICP-AND-UX-STRATEGY.md` §0).

## Accessibility and quality bar

- Target WCAG 2.2 AA. Normal text needs 4.5:1 contrast, large text 3:1.
- Visible keyboard focus everywhere; semantic headings and landmarks; labels on form controls; accessible names on icon-only buttons.
- Tabs follow the WAI-ARIA tabs pattern (arrow, Home and End keys, roving `tabindex`). FAQ buttons use `aria-expanded` with `hidden` panels. The mobile menu closes on Escape.
- Text stays resizable; layouts stay usable at narrow widths and 200% zoom.
- WCAG is a testable standard, not a guarantee that every user need is met.

## Review checklist

- [ ] Only tokens from `:root`; no new hex values.
- [ ] Campton only; no extra font requests.
- [ ] No pills, emoji or icon fonts.
- [ ] Sample data labelled; unverified claims carry `data-confirm`.
- [ ] Text, controls and focus states pass contrast in their actual states.
- [ ] Navigation, FAQ, tabs, forms and in-page links work by keyboard.
- [ ] No horizontal overflow at 360–414, 768 and 1024px.

## References

- [W3C Web Content Accessibility Guidelines 2.2](https://www.w3.org/TR/WCAG22/)
- [W3C: Understanding Contrast Minimum](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html)
