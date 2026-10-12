# Pharmacy Software — Page and Product Development Brief

**Purpose:** Content architecture and product-page guidance for the Healthray Pharmacy landing page and future product pages.  
**Product:** Healthray Pharmacy, the web app at `https://pharmacy.healthray.com` (sign up at `/signup`, log in at `/login`).  
**Current implementation:** `index.html` is the single landing page. There are no other variants.  
**Audience:** Independent retail pharmacies (primary), pharmacy chains, and hospital/clinic pharmacies in India. The full ICP is in `PHARMACY-ICP-AND-UX-STRATEGY.md`.  
**Verified product facts:** see §0 of `PHARMACY-ICP-AND-UX-STRATEGY.md` (checked against the live app, its pricing API and the `org-dashboard/` screenshots on 13 Oct 2026).

## Product story

Lead with the daily work the software helps organize: billing, inventory, purchases, batch/expiry review, reporting, and multi-store workflows where supported. The strongest page explains a specific task, shows the relevant interface, and answers what the user can do next.

Avoid unsupported superlatives, invented performance numbers, unverified integrations, and claims that the software makes clinical decisions. State product capabilities only after the product team confirms them. Use “illustrative” for prototype screens and sample data.

## Recommended page architecture

| Order | Section | Job and content guidance | Current page |
|---|---|---|---|
| 1 | Header/navigation | Keep brand, a small set of section links, and one clear trial action. Make the mobile menu keyboard accessible. | Header and sticky navigation |
| 2 | Hero | Say who the product is for and what connected workflows it covers. Pair a plain white background with a compact, illustrative dashboard. Use one primary CTA and one lower-commitment explore action. | `#top`, dashboard `#dashboard` |
| 3 | Capability strip | Quickly scan the core categories: billing/POS, inventory, batch/expiry, invoicing, reports, multi-store. Only list verified capabilities. | Capability strip |
| 4 | Daily-work benefits | Connect common friction to a concrete workflow improvement. Keep claims modest and evidence-based. | `#benefits` |
| 5 | Workflow | Show the sequence from purchase to stock to billing to review/reporting. Distinguish supported automation from user action. | `#workflow` |
| 6 | Feature details | Give each module one job-focused explanation and a small contextual preview. Avoid repeating the same generic benefits in every card. | `#features` |
| 7 | Differentiator / AI concept | Demonstrate one clear moment, explain why a suggestion appears, and show that a person remains in control. Identify as concept until real capabilities, data use, and review rules are verified. | `#ai` |
| 8 | Solutions by business type | Explain how the experience may differ for an independent pharmacy versus a multi-store group. Avoid implying plan-specific functionality without confirmation. | `#solutions` |
| 9 | Confidence and evaluation | Help buyers check device compatibility, imports, access, support, and trial terms. This reduces uncertainty without inventing trust badges. | `#trust` |
| 10 | Getting started | Describe only the actual signup, setup, migration, training, and rollout path. Mark unknown steps as questions for the product owner. | `#getting-started` |
| 11 | FAQ | Answer trial, compatibility, invoicing, data transfer, and support questions in plain language. Keep answers consistent with the current offer and product facts. | `#faq` |
| 12 | Final CTA and footer | Repeat the next step, disclose trial terms, provide contact and useful product links, and replace legal placeholders before launch. | `#trial`, `#contact` |

## Product areas to plan for

Status after the 13 Oct 2026 check. Items 1–7 exist in the app; the plan that unlocks each is listed in the ICP doc §0.

- **Item 8:**
  - Roles, 2FA, IP whitelisting, force logout and the activity report all exist.
  - Backup/restore is unverified.
- **Item 9:**
  - Only Schedule H/H1 tagging, doctor name on bills and EMR prescription tokens exist.
  - No interaction checking was found, so don't promote any.

1. **Billing and POS:** product lookup, cart/line items, quantity, price and supported tax details; receipt/print workflow and return handling should be specified by the product team.
2. **Inventory:** item catalog, stock movement, adjustment permissions, location/store, and low-stock review.
3. **Batch and expiry:** batch identifiers, expiry dates, review status, and a clear way to investigate or resolve alerts.
4. **Purchasing and suppliers:** supplier records, purchase entry, receiving and stock update, and reconciliation of differences.
5. **Invoices and accounting:** document formats, tax behavior, exports/integrations, and applicability should be verified for the actual business and deployment.
6. **Reports:** define each metric, date range, source data, and whether values are live, delayed, or illustrative.
7. **Multi-store operations:** store-level permissions, stock visibility, transfers, and consolidated reporting, if supported.
8. **User access and auditability:** roles, change history, account lifecycle, backup/restore, and support access should be described only from confirmed implementation details.
9. **Prescription-related or interaction features:** determine intended use, authoritative data source, warnings, human review process, and regulatory implications with qualified domain/legal stakeholders before promotion or implementation.

## Pharmacy-specific research notes

- **Expiry and batch data matter operationally.** India’s Drugs Rules include record-keeping provisions for specified categories, including a separate Schedule H1 register with stated particulars and a three-year retention period. This is a narrow statutory provision, not a blanket software requirement for every medicine or pharmacy. Confirm the current applicable rules and licensed workflow with a qualified compliance reviewer before making product requirements or marketing claims. [CDSCO Rules PDF](https://cdsco.gov.in/opencms/resources/UploadCDSCOWeb/2022/drug_rules/Drugs%20Rules%2C%201945.pdf)
- **Invoice behavior needs configuration discovery.** GST invoice requirements are specified by Rule 46 and e-invoicing applies only to taxpayers/transactions covered by current notifications and rules. Do not imply that every pharmacy needs e-invoicing or a QR code. Validate the customer’s registration, transaction type, turnover applicability, and current system behavior with a tax professional. [GST e-Invoice portal: mandatory invoice fields](https://einvoice6.gst.gov.in/content/e-invoice-printing-process-mandatory-fields-modes-of-irn-generation/)
- **Personal data requires deliberate handling.** The DPDP Act establishes a framework for processing digital personal data, and the Act itself makes commencement subject to government notification. The product team should verify currently effective provisions and rules with counsel; collect only necessary signup data, state purposes clearly, set retention/access controls, and connect forms to an approved privacy notice. [MeitY: Digital Personal Data Protection Act](https://www.meity.gov.in/content/digital-personal-data-protection-act-2023-dpdp-act)
- **Accessibility belongs in the build process.** Use WCAG 2.2 AA as the target: check contrast, keyboard operation, focus, semantic structure, text resizing, and responsive behavior. W3C specifies at least 4.5:1 contrast for normal text and 3:1 for large text under criterion 1.4.3. [W3C WCAG 2.2](https://www.w3.org/TR/WCAG22/)

These notes are product-design inputs, not legal or clinical advice. Regulations and applicability can change; check current official materials before release.

## Content and trust rules

- Label every fake dashboard amount, chart, customer, testimonial, and AI output as a sample or concept. Never present prototype numbers as customer results.
- Prefer concrete verbs: record, review, find, compare, reconcile, confirm. Avoid phrases like “fully automate compliance” unless independently substantiated and approved.
- For alerts, explain the reason, source/rule, scope, and next action. Let users inspect details and decide; do not use color alone to communicate severity.
- There is no 14-day trial; the offer is the free Starter plan (10 sales bills/month, 1 user). Keep plan names, prices and limits synchronized across hero, pricing, FAQ and switching, and re-check them against `https://pharmacyapi.healthray.com/api/subscription/pricing/public` before each release.
- State product limitations, hardware support, data import, integrations, pricing, support, and retention accurately. If unknown, mark them internally as open questions rather than publishing guesses.

## Development and release workflow

1. **Confirm facts:** product owner signs off on modules, trial terms, markets, supported devices, integrations, data handling, and AI statements.
2. **Keep structure semantic:** use landmarks, one page-level heading, nested section headings, real buttons/links, and labeled forms.
3. **Build reusable patterns:** centralize tokens, buttons, section heading patterns, tags, dashboard UI, and responsive rules. Avoid one-off card treatment for every block.
4. **Keep interactions honest:** connect CTA/forms to real endpoints only after privacy, consent, validation, error/success behavior, and data ownership are agreed. The current form is local prototype behavior and sends nothing.
5. **Review responsive states:** inspect desktop, tablet, and phone widths; include keyboard and zoom checks. Check menu, dashboard preview, tables, dialogs, FAQ, and footer.
6. **Check accessibility:** contrast, focus visibility, labels, dialog behavior, keyboard path, reduced motion, and non-color status cues.
7. **Use realistic test data safely:** do not use identifiable patient data. Create synthetic pharmacy, product, batch, invoice, and alert records.
8. **Release with traceability:** record who approved claims and terms, identify analytics/third-party services, replace legal placeholders, and retain a rollback copy of the approved page.

## Open questions before production

Answered on 13 Oct 2026 from the live app:
- **Brand:** Healthray Pharmacy.
- **Modules and plans:** see ICP doc §0.
- **Pricing:** published; there is no trial, only the free Starter plan.
- **Import:** CSV and OCR.
- **Accounting:** Tally XML export on Enterprise.
- **Security:** roles, 2FA and IP whitelisting.

Still open:
- The official logo file to use on the site. The page currently uses a cross mark.
- How billing behaves offline or when the connection drops.
- Whether a Schedule H1 register report exists.
- Whether a full data export is available when a customer leaves.
- Support hours, languages, and a WhatsApp sales or support number.
- Whether Healthray helps with data import, and what happens after a demo request (callback time, owner).
- Backups, hosting location, incident process, privacy notice and legal page URLs.
- The production endpoint for the demo form, and its consent text.
- Exact supported printer and scanner models.

## Research references

- Central Drugs Standard Control Organization, [Drugs Rules, 1945](https://cdsco.gov.in/opencms/resources/UploadCDSCOWeb/2022/drug_rules/Drugs%20Rules%2C%201945.pdf).
- GST e-Invoice portal, [Invoice printing process and mandatory fields](https://einvoice6.gst.gov.in/content/e-invoice-printing-process-mandatory-fields-modes-of-irn-generation/).
- Ministry of Electronics and Information Technology, [Digital Personal Data Protection Act, 2023](https://www.meity.gov.in/content/digital-personal-data-protection-act-2023-dpdp-act).
- W3C, [Web Content Accessibility Guidelines (WCAG) 2.2](https://www.w3.org/TR/WCAG22/).

