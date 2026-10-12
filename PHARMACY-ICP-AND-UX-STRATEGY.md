# Pharmacy Management Software — ICP, Buyer Psychology & UX Strategy

**Purpose:** The reader-first foundation for the landing page (`index.html`) and every future product page. Every section, headline, CTA and footer link on the page traces back to something in this document.
**Market:** Retail pharmacies (medical stores / chemists) in India, from single counters to small chains.
**Product:** Healthray Pharmacy (`https://pharmacy.healthray.com`), a browser-based app from Healthray.
**Last reviewed:** 13 October 2026.
**Status:** Strategy draft. Product facts below were checked against the live app on 13 Oct 2026 (§0). Anything still unverified is marked **[confirm]** and needs product-owner sign-off before launch.

---

## 0. Verified product facts (13 Oct 2026)

Sources: the app's public JavaScript bundle (routes, menus, in-app walkthrough copy), the public pricing endpoint `https://pharmacyapi.healthray.com/api/subscription/pricing/public`, and dashboard screenshots in `org-dashboard/`. Re-check these before every content change, because pricing and plans come from the live API.

**Plans** (INR, exclusive of 18% GST; billed for 1, 3 or 5 years; "switch or cancel anytime"):

| Plan | Price | Limits | Highlights |
|---|---|---|---|
| Starter | Free forever | 10 sales + 25 purchase bills/month, 1 user | Core sales & purchase, CSV import, basic reports |
| Basic | ₹4,999/year (≈ ₹417/month) | Unlimited bills, 3 users | Role management, label print, GST/margin/non-moving/top-selling reports, patient & supplier vouchers, medicine reminders, stock adjustment |
| Advance (popular) | ₹9,999/year (≈ ₹833/month) | Unlimited bills, 10 users | + Purchase orders, reorder processing, executive dashboard, drafts, lock invoices, discount verification, bill attachment, 2FA, IP whitelisting, activity report, expense management |
| Enterprise | Custom | Unlimited users & bills | + Multi-store central admin & linked outlets, central reports, Tally XML export, supplier rate contracts, MRD, OCR credits, priority support |

Message credits (WhatsApp/SMS/email bill sharing, WhatsApp daily report, OCR import) vary by plan.

**Confirmed capabilities:**
- **Sale screen:**
  - FEFO batch auto-selection.
  - Search by brand or generic name, or with a USB barcode reader.
  - Cash, card, UPI or credit.
  - OPD/IPD patients with UHID search, doctor name and GST number on the bill.
  - Save draft, Done, or Save & print.
  - Shortcuts: F2 for search, Ctrl+S to save.
- **Expiry and stock:**
  - Stock expiry report, item-wise or supplier-wise.
  - A setting that controls whether expired items can be sold.
  - Min/max stock, stock alert and short book.
  - Non-moving items, label/barcode printing, stock transfer.
- **Purchase:**
  - CSV import with column mapping; OCR import (uses credits).
  - Scheme and free quantity recorded; PTR and margin auto-calculated.
  - Purchase credit notes; supplier ledger and dues.
- **Accounts and reporting:**
  - HSN/GST from the item master; GST report and bill & date-wise tax report.
  - Tally report.
  - Account, patient and supplier ledgers; expenses; vouchers.
  - Collection and operator-wise collection reports; 25+ reports in all.
- **Hospital pharmacy:**
  - IPD/discharge billing; payer and TPA companies; patient deposits and dues.
  - Departments; patient kits and packages; MRD.
  - Healthray EMR prescriptions arrive as tokens on a token board.
  - ABDM/ABHA integration.
- **Customer retention:** medicine reminders, loyalty cards, online sales orders (pick-up or delivery).
- **Items:**
  - Schedule H/H1 drug tagging.
  - LASA, high-risk, antimicrobial and DPCO flags.
- **Onboarding:**
  - Sign up with an email OTP.
  - "Add Initial Data" CSV import of items and opening stock, with a sample sheet and line-by-line errors.
  - In-app walkthroughs for sales, purchase, item master and account master.
- **Support:** phone +91 89808 80819 (shown in the app header) and a help centre at support.healthray.com.

**Not found in the app; don't claim:** a native owner mobile app, a dedicated Schedule H1 register report, rate-difference alerts, substitute suggestions, offline billing, a 14-day trial, assisted data migration, support hours or languages, or automatic GST-rate updates.

---

## 1. The niche in one paragraph

An Indian medical store carries roughly 3,000–8,000 SKUs, and every one has a batch number, an expiry date, an MRP, a GST rate and often a scheme (free quantity) from the distributor. A typical store handles 50–200 bills a day, mostly in two or three rush windows, with one owner-pharmacist and two to four staff. Margins are thin (most of the money is made or lost on purchase rate, scheme and expiry), and the regulator and the GST portal both expect clean records. Most stores already use *something* — a paper register, a generic billing app, or a desktop licence package (Marg, Swil, Busy, GoFrugal, RetailGraph and similar) bought through a local dealer years ago. So the page is usually not talking to a first-time buyer. **It is talking to a switcher who is tired, sceptical and scared of the switch.**

> Sources that informed this: competitor pages (GoFrugal pharmacy page), comparison blogs (Accountune, Codingclave — vendor-written, weighed accordingly), Trustpilot reviews of desktop pharmacy ERPs, distributor/expiry return documentation. Numbers above are industry ranges from those sources, not our claims.

---

## 2. Ideal customer profile (ICP)

### Primary ICP — "Owner at the counter"
| Attribute | Detail |
|---|---|
| Business | Independent retail medical store, 1–2 counters, ₹8 lakh – ₹60 lakh monthly sales |
| Location | Tier-2/3 cities and city neighbourhoods; also tier-1 residential areas |
| Decision maker | Owner, often also the registered pharmacist (or the owner's son/daughter taking over) |
| Age band | 30–55; the second-generation owner (25–35) is the most likely *initiator* of a switch |
| Current setup | Desktop licence software from a local dealer (most common), Excel/registers, or a generic billing app |
| Device reality | A Windows PC at the counter, a thermal or dot-matrix printer, a barcode scanner (sometimes), an Android phone in the pocket all day |
| Language | Comfortable reading English UI labels; speaks Hindi/regional language with staff; prefers phone/WhatsApp over email |
| Budget mindset | Will pay yearly if value is visible; hates surprise AMC and "upgrade" charges |

### Secondary ICP — "Growing chain owner / operations head"
3–25 outlets, maybe a central warehouse. Cares about stock visibility across stores, transfers, central purchasing, staff controls and consolidated reports. Longer sales cycle, wants a demo with their own data, asks about user roles and audit trail.

### Tertiary — Hospital / clinic pharmacy
In-house pharmacy of a nursing home or clinic. Cares about linking to doctor prescriptions/IPD, but buying is driven by hospital administration. Give them a "Solutions" entry and a route to a conversation; don't build the page around them.

### Anti-ICP (don't optimise copy for)
Wholesale distributors / C&F agents (different workflow), online-only e-pharmacies, and buyers looking for free software only.

---

## 3. Jobs to be done

| When… | I want to… | So that… |
|---|---|---|
| a customer is waiting at the counter in the evening rush | find the medicine, pick the right batch and print the bill in seconds | the queue moves and nobody leaves for the shop next door |
| a distributor delivery arrives with a 40-line bill | enter it fast with batch, expiry, rate and free quantity | stock is right and I can check the rate I was promised |
| I walk past the shelf | know which batches expire in the next 30/60/90 days | I can push them, return them to the distributor in time, or stop reordering |
| it's month-end | have GST-ready sales and purchase data for my CA | filing doesn't become a weekend of fixing entries |
| an inspector or auditor asks | show the Schedule H/H1 register and purchase records | there's no panic and no penalty **[confirm register support]** |
| I'm not in the shop | see today's sales, cash and low stock on my phone | I can trust my staff and still have a life |
| I'm thinking of switching | know my item master, stock and old data come with me | I don't lose years of records or a week of business |

---

## 4. Pain points (ranked by emotional weight, not feature order)

1. **Expiry loss — the silent leak.** Strips sitting behind faster-moving boxes, found only when they're dead stock. Owners often can't put a number on it, which makes it worse. *Emotional trigger: "money I already paid is rotting on my shelf."*
2. **Counter rush and slow billing.** Staff searching for the item, the batch, the substitute; the printer jamming; the customer leaving. *Trigger: embarrassment + lost sale.*
3. **Purchase entry drudgery.** Every distributor bill typed line by line at night, scheme/free quantity mistakes, rate differences never caught. *Trigger: exhaustion; "I pay more than I should and don't even know."*
4. **Stock never matches.** Software says 5, shelf says 2. Loss, theft or wrong entries — nobody knows which. *Trigger: distrust of staff and of the software.*
5. **Compliance anxiety.** GST rate changes (e.g. the 12% slab removal in Sept 2025), invoice formats, Schedule H/H1 records, drug inspector visits. *Trigger: fear, the strongest motivator but one we must address calmly, never with scare tactics.*
6. **Credit (udhaar) and distributor dues in notebooks.** Regular customers on account, distributor payments, cheque dates. *Trigger: cash flow blindness.*
7. **Being chained to the counter.** No visibility unless physically present. *Trigger: lack of freedom.*

### Frustrations with *current* software (the switching story)
- "The software is old and looks it; only one person in the shop knows how to use it."
- "Every update/renewal costs money and the dealer takes days to come."
- "Support picks up, but they don't understand my problem." Trustpilot reviews of desktop ERPs show a mixed support picture, with complaints about post-sale responsiveness and renewal-driven pricing.
- "I can't see anything on my phone."
- "Data is on one PC. If it dies, my shop dies." (backup fear)
- "Too many features I don't need, the ones I need are hidden in menus."

### Frustrations with *general billing apps*
- No batch/expiry depth, no FEFO picking, no medicine-specific search (salt/generic, substitute), no distributor scheme handling.

---

## 5. Mindset & psychology

| Trait | What it means for the page |
|---|---|
| **Loss-averse more than gain-seeking** | Frame value as *money you stop losing* (expiry, rate differences, missed sales) before *growth*. Offer a simple expiry-loss estimator using *their* numbers. |
| **Risk-averse about switching** | The #1 objection is not price, it's "what if billing stops on day one?" A whole section on migration, parallel running and training. |
| **Trusts people, not websites** | Show real humans and real channels: phone, WhatsApp, local language support **[confirm]**. Primary CTA is a demo with a person, not just a self-serve signup. |
| **Word-of-mouth & peer proof** | Testimonials from *similar* stores (city, store size) matter more than logos. **Use only verified ones; slots are left empty until we have them.** |
| **Practical, time-poor, reads on phone** | Most first visits come from mobile, in between customers. Short blocks, scannable headings, sticky Call/WhatsApp/Demo bar on mobile. |
| **Price-sensitive but not cheapest-seeking** | They'll pay for reliability. Hidden costs (AMC, per-user fees, "data migration charges") destroy trust. State what's included. |
| **Sceptical of hype** | "AI-powered", "revolutionary" and "100% compliance" read as sales talk. Use plain verbs and concrete counter scenes. |
| **Pride in their shop** | Respectful tone. They are professionals running a regulated business, not "shopkeepers who need fixing." |

**Tone of voice:** Plain, warm, specific, counter-level. Say "strip", "batch", "distributor bill", "scheme", "udhaar", "rush hour". Avoid "synergy", "seamless", "revolutionise", "unlock". One idea per sentence.

---

## 6. Selection criteria (how they actually decide)

Ranked from interviews/reviews patterns and competitor FAQ emphasis:

1. **Billing speed & ease for staff** — can a new staff member bill on day one? Keyboard shortcuts, fast search by name/salt, barcode.
2. **Batch & expiry handling** — FEFO suggestion at billing, near-expiry list, expiry return to distributor.
3. **Data migration from current software** — item master, stock, customers, suppliers, outstanding.
4. **Support quality & language** — response time, phone/WhatsApp, remote help, someone who understands pharmacy.
5. **Total cost & transparency** — yearly price, renewal price, per-user / per-device / hardware costs.
6. **GST correctness & accountant friendliness** — current rates, reports/exports for the CA, Tally export **[confirm]**.
7. **Works when the internet doesn't** — offline billing or fast recovery **[confirm]**.
8. **Data safety** — backup, who owns the data, can I export it if I leave.
9. **Hardware compatibility** — existing printer, barcode scanner, PC.
10. **Mobile visibility** — owner app/dashboard.
11. **Growth path** — second store, central purchase, transfers.

**Page implication:** Criteria 1–5 must be answered *above the FAQ*, in the main flow. 6–11 can live in features + FAQ.

---

## 7. Objections → answers (copy bank)

| Objection | Where it's answered | Answer direction |
|---|---|---|
| "Switching will stop my billing." | Switching section, FAQ | Set up on the free Starter plan while the old system keeps running; import items and stock by CSV; go live on your chosen day. Assisted import **[confirm]** |
| "My staff won't learn it." | Features (billing), Switching, Support | Batch fills in by itself (FEFO), keyboard shortcuts, built-in walkthroughs for each module |
| "It'll be costly later." | Pricing, FAQ | Prices are published: Starter free, Basic ₹4,999/yr, Advance ₹9,999/yr, plus 18% GST; 1/3/5-year terms; switch or cancel anytime |
| "What if the internet goes down?" | FAQ, Buyer checklist | It's a browser app. **[confirm offline behaviour — do not publish until known]** |
| "Is my data safe / mine?" | Checklist, FAQ | Per-user logins and roles; 2FA, IP whitelisting and force logout on Advance+. Full data export **[confirm]** |
| "Will GST be right?" | Features (accounts), FAQ | HSN and GST rate come from the item master; GST report; Tally XML export on Enterprise only |
| "Does it work with my printer/scanner?" | FAQ | Thermal and A4/A5 bill formats, barcode label printing, USB barcode readers |

---

## 8. Buying journey & CTA strategy

1. **Trigger** — expiry write-off, a bad support experience, a renewal invoice, a second store opening, the next-gen owner joining.
2. **Search** — "medical store billing software", "pharmacy software with expiry alert", "Marg alternative", asks peers/distributor reps.
3. **Evaluate** — 2–4 shortlisted, watches a demo, asks for price on WhatsApp.
4. **Decide** — after a demo *with their own items*, and a clear migration plan.

**CTA hierarchy**
- **Primary:** *Book a free demo* (human, matches trust psychology; captures name, store, phone, city, current software).
- **Secondary:** *Start free*, which links to `https://pharmacy.healthray.com/signup` (free Starter plan; suits the self-serve, younger owner).
- **Existing customers:** *Log in*, linking to `https://pharmacy.healthray.com/login` in the header and footer.
- **Ever-present low-friction:** *Call* (+91 89808 80819) and *Start free*, in a sticky bar on mobile. A WhatsApp sales number is not verified yet **[confirm]**; add it when available, because this ICP prefers WhatsApp.
- **Lead magnet (mid-funnel):** *Pharmacy software buyer's checklist* and the expiry-loss estimator.

---

## 9. Site structure (information architecture)

### Primary navigation (max 5 items + CTA)
| Item | Why it's there |
|---|---|
| **Features** (Billing, Expiry & stock, Purchase, GST & accounts, Reports) | Module scanners want to confirm their must-haves fast |
| **Who it's for** (Single store, Chains, Hospital pharmacy) | Self-identification; chains need to see they're covered |
| **Switching** | The #1 objection deserves a top-level door |
| **Pricing** | Hidden pricing = distrust in this market |
| **FAQ** | Practical questions (offline, printer, data) |
| *Log in* · *Start free* · CTA: **Book a free demo** | Existing users need a way in; new users choose self-serve or human |

Rationale for *not* adding: "About", "Blog", "Careers" belong in the footer — they don't help a buyer decide.

### Landing page section order (and the reader's question each answers)
| # | Section | Reader's question | Design note |
|---|---|---|---|
| 1 | Header | "Where am I, what can I do?" | Sticky, white, 5 links, demo CTA, phone |
| 2 | Hero | "Is this for a store like mine, and what will it fix?" | Owner-voice headline about counter speed + expiry money; billing screen mock showing batch/expiry pick; demo + "Start free" CTAs; 3 reassurance chips (free Starter plan, CSV stock import, paid plans from ₹4,999/yr) |
| 3 | Built-for strip | "Is this for my type of pharmacy?" | Store types, not fake logos |
| 4 | "Sound familiar?" pain grid | "Do they understand my day?" | 6 pains in their words → the fix in one line |
| 5 | Expiry-loss estimator | "How much am I actually losing?" | Interactive; uses their numbers; honest framing |
| 6 | A day at your pharmacy | "How would it fit my routine?" | Time-of-day tabs: Opening, Counter rush, Distributor delivery, Closing |
| 7 | Features by module | "Does it have what I need?" | 7 tabs with screen previews: billing, expiry, stock, purchase, GST, patients & hospital pharmacy, reports & control. Plan-gated features name their plan |
| 8 | Switching made safe | "Will switching hurt?" | 4 steps (sign up → CSV import → walkthroughs → go live/upgrade), what the CSV can carry, "run both in parallel" |
| 9 | Who it's for | "Will it grow with me?" | Single store / Chain / Hospital pharmacy cards |
| 10 | Buyer's checklist | "How do I compare vendors?" | Honest questions to ask any vendor + our answer; positions us as the trustworthy guide |
| 11 | Support | "Who helps me when it breaks at 8 pm?" | Phone, help centre, in-app walkthroughs, shortcuts; hours and languages **[confirm]** |
| 12 | Pricing | "What will it really cost?" | 4 real plans with published prices, GST note, 1/3/5-year terms |
| 13 | FAQ | Remaining practical doubts | Accordion, 8–10 Qs |
| 14 | Final CTA + demo form | "OK, what next?" | Short form, phone-first, what happens after you submit |
| 15 | Footer | "Where's everything else?" | See below |

### Footer structure
- **Brand column:** one-line positioning, phone +91 89808 80819, help centre, Contact Healthray.
- **Product:** Billing & counter, Expiry & batch tracking, Stock & reorder, Purchase & suppliers, GST & accounts, Hospital pharmacy, Pricing.
- **Solutions:** Single medical store, Pharmacy chains, Hospital & clinic pharmacy, Online orders & delivery.
- **Switching & guides:** Switching from other software, CSV data import, Buyer's checklist, Expiry loss estimator, FAQ.
- **Account & help:** Log in, Sign up free, Help centre, About Healthray, Contact.
- **Bottom bar:** © Healthray, Privacy, Terms, Refund policy, Data & security. Legal links are placeholders **[confirm URLs]**.

Footer is dark navy (design system), links are real labels a buyer would search for (also an SEO asset).

---

## 10. UX rules derived from the ICP

1. **Mobile-first reading.** Sticky mobile bar: Call · WhatsApp · Book demo. Thumb-reachable, 48px targets.
2. **Show the counter, not a generic dashboard.** The hero visual is a billing screen with batch/expiry selection — the moment they recognise as "that's my problem".
3. **Label every sample.** All amounts in previews say "Sample data". No fabricated customer numbers.
4. **Numbers in Indian format** (₹1,25,000, lakh) — signals "built for us".
5. **One idea per block, scannable headings**, max ~60ch text measure.
6. **Never use fear as a weapon.** Compliance copy is calm and factual.
7. **Forms ask only what a callback needs:** name, phone, store name, city, current software. Explain what happens next and when.
8. **Accessibility:** WCAG 2.2 AA, keyboard-accessible tabs/accordion/menu, visible focus, non-colour status cues, reduced motion.

---

## 11. Messaging hierarchy

- **Core promise:** *Faster bills at the counter. Less money lost to expiry. Records you can trust.*
- **Proof pillars:** (1) Built for medicine: FEFO batches, expiry report, generic name, scheme, Schedule H/H1 tagging. (2) Low-risk switch: free Starter plan, CSV import, in-app walkthroughs. (3) Transparent pricing and a support line you can call. (4) For hospitals: IPD, TPA, EMR tokens, ABDM.
- **Headline candidates (A/B):**
  - A: "Bill faster at the counter. Stop losing money to expiry."
  - B: "The pharmacy software your staff learns in a day — and your expiry list finally trusts."
  - C: "Switch your medical store software without stopping a single bill."

---

## 12. What still needs real-world validation

- Interview 8–10 store owners (5 single, 3 chain, 2 hospital) to validate the pain ranking.
- Collect 3+ verified testimonials with store name, city and permission.
- Confirm the remaining **[confirm]** items. Open `index.html?review` to see them outlined:
  - offline behaviour
  - full data export
  - assisted import help
  - support hours and languages
  - Schedule H1 register report
  - demo callback time
  - the PDF checklist
  - privacy notice and legal URLs
  - a WhatsApp sales number
- Track: demo bookings, WhatsApp clicks, estimator usage, scroll depth to Switching section.
