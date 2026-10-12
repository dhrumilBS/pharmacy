# Healthray Pharmacy — Blue Design System

**Status:** Working design-system documentation for the landing-page prototype.  
**Source of truth:** `index.html`; use this guide when extending it.  
**Last reviewed:** 13 October 2026.

This system aims for a clear, calm pharmacy-workflow product: blue carries the brand and primary actions, white gives operational content room to breathe, and dark navy is reserved for emphasis and the footer. The current page is a review prototype. Product claims were checked against the live app on 13 Oct 2026, but the items marked `data-confirm` and all demo data still need owner approval.

## Brand assets

- Design-system poster: `pharmacy-design-system-editable-fixed.svg` (editable vector) and `pharmacy-design-system-crisp-2x.png` (2× raster export). The poster still carries the old working title "Pharmacy Management"; update it to "Healthray Pharmacy" when you next edit it.
- Real product screenshots (reference only, not published): `org-dashboard/`.
- The page uses a placeholder cross mark. Swap in the official Healthray logo once its file is supplied.
- Keep the cross mark and wordmark proportions intact. Do not redraw the mark with emoji or mix in unrelated icon styles.
- Product screenshots and dashboard panels in the page are illustrative HTML/CSS previews. Treat them as mockups, not evidence of shipped functionality.

## Color tokens

Use the CSS variables in `index.html` rather than introducing one-off colors. Hex values below are the current prototype values.

| Token | Value | Intended use |
|---|---|---|
| Navy 950 | `#081C2C` | Strongest headings and deep surfaces |
| Navy 900 | `#0D2A40` | Footer and dark CTA surfaces |
| Navy 800 | `#123B5D` | Brand text, dashboard headings |
| Navy 700 | `#174C75` | Secondary dark emphasis |
| Navy 600 | `#1D6090` | Supporting brand tone |
| Blue 700 | `#1D4ED8` | Link and hover color |
| Blue 600 | `#2563EB` | Primary actions and brand accent |
| Blue 500 | `#3B82F6` | Small highlights and charts |
| Blue 100 | `#DBEAFE` | Soft emphasis |
| Blue 50 | `#EFF6FF` | Eyebrows and quiet blue fills |
| Teal 700 | `#0F766E` | Supporting positive/check indicators |
| Teal 600 | `#0F9F9A` | Secondary data visualization accent |
| Teal 500 | `#14B8A6` | Small chart accent |
| Teal 100 / 50 | `#CCFBF1` / `#F0FDFA` | Soft teal fills |
| Text | `#172033` | Main body copy |
| Text 2 | `#475569` | Secondary body copy |
| Muted | `#64748B` | Supporting labels; verify contrast at actual size |
| Border / Border 2 | `#E2E8F0` / `#CBD5E1` | Dividers and control edges |
| Surface / background | `#FFFFFF` / `#F8FAFC` | Main canvas and occasional subtle grouping |
| Success | `#15803D` | Positive state; pair with a label or icon |
| Warning | `#B45309` | Attention state; pair with a label or icon |
| Danger | `#DC2626` | Error state; pair with a label or icon |

### Color use

- Keep the hero white. Prefer white or transparent section backgrounds; use pale blue sparingly for small labels, controls, or contained product examples.
- Keep the footer visibly darker than the page. Use light text and distinct link styling there.
- Blue is the primary action color. Teal is a supporting signal, not a second brand color competing for attention.
- Never encode status by color alone. Include readable text, a shape, or an icon as well.
- Do not use the pale fills as text colors. Validate each foreground/background pair in context.

## Typography

- **Headings and brand:** Plus Jakarta Sans, with Inter and system sans-serif fallbacks.
- **Body and dashboard UI:** Inter, with Plus Jakarta Sans and system sans-serif fallbacks.
- Load the font files once at the document level; preserve sensible fallbacks if the external font source is unavailable.
- Use sentence case for headings and buttons. Keep labels short and specific; avoid marketing copy inside operational controls.
- Body baseline: 16px with about 1.62 line height. Secondary explanatory copy can be 17–18px with generous leading.
- Hero heading: fluid `clamp()` sizing, strong navy, tight but readable line height. Use blue emphasis on a short phrase only.
- Small dashboard labels may be compact because they are illustrative. Real product UI must remain readable at its intended display size and zoom.

## Layout and spacing

- Maximum content width: `1200px`; use the `.container` pattern, which retains 24px minimum side gutters.
- Standard section vertical padding: `96px`; compact sections: `64px`. Adjust by section purpose and viewport, not by adding arbitrary local margins.
- Use a consistent 8px rhythm for component spacing where practical.
- Standard section headings are capped near 720px; keep the text measure short enough to scan.
- Hero uses two columns on wide screens, with copy first and a right-aligned dashboard preview. The desktop dashboard preview is intentionally reduced; at 900px and below it returns to normal scale and stacks below the copy.
- Responsive breakpoints in the current page: 1100px (layout refinements), 900px (single-column hero and AI layouts), 767px (decorative adjustments), 600px (mobile component sizing), and 420px (compact navigation).
- Avoid horizontal overflow, clipped CTAs, or transforms that create large unused layout gaps. Check the smallest supported viewport after changing the hero.

## Components

### Buttons and links

- Primary: blue fill, white text, clear action label, minimum 48px target height in main content.
- Secondary: white fill, navy text, visible border; use for the next most important action.
- Dark: navy fill, white text; reserve for dark-surface contexts.
- Links should be visually distinguishable and use descriptive labels. Hover is an enhancement, not the only signifier.

### Cards and surfaces

- Use cards when they group a task, comparison, or distinct data set. Avoid putting every paragraph inside a bordered box.
- Current radii: 8px small, 12px medium, 16px large, 24px extra-large, 30px feature surface.
- Use subtle borders and restrained shadows. Prioritize hierarchy, whitespace, and alignment over decoration.

### Dashboard previews

- Use the same restrained navy/blue palette and a neutral white surface.
- Label sample values and illustrative screens clearly. Avoid fabricated customer results, endorsements, or claims of live AI behavior.
- Use stable data labels and consistent number formatting; make alerts explain what needs review and who decides the next step.

### Icons and motion

- Use the page's inline SVG symbol set (`<use href="#i-…">`) for interface icons. Keep icon stroke/weight consistent and decorative icons hidden from screen readers.
- Keep motion brief and optional. Respect `prefers-reduced-motion`; no essential meaning should depend on animation.

## Accessibility and quality bar

- Target WCAG 2.2 AA for the page. Normal text needs at least 4.5:1 contrast; large text needs at least 3:1. For AAA contrast, use 7:1 for normal text and 4.5:1 for large text. Check actual rendered colors, including hover, focus, disabled, and tinted surfaces.
- Provide a visible keyboard focus indicator, semantic headings, labels for form controls, accessible names for icon-only buttons, and sensible keyboard order.
- Use semantic landmarks and controls; ensure dialogs can be opened, used, and closed with keyboard and assistive technology.
- Keep text resizable and layouts usable at narrow widths and browser zoom.
- WCAG is a testable conformance standard, not a guarantee that every user need is met. See the references below.

## Practical review checklist

- [ ] Hero remains white and the dashboard balances with the copy on desktop.
- [ ] Sections use restrained surfaces; footer is clearly darker and links remain legible.
- [ ] Text, controls, and focus states pass contrast checks in their actual states.
- [ ] Navigation, FAQs, modals, forms, and in-page links work with mouse and keyboard.
- [ ] Mobile menu, CTA, dashboard, tables, and footer do not overflow at narrow widths.
- [ ] Sample data, capability statements, and legal text are clearly marked for approval.

## References

- [W3C Web Content Accessibility Guidelines 2.2](https://www.w3.org/TR/WCAG22/) — normative accessibility success criteria.
- [W3C: Understanding Contrast Minimum](https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html) — contrast thresholds and rationale.

