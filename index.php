<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Pharmacy software for medical stores: billing, expiry & stock');
$description = 'Pharmacy billing software for medical stores: batch-wise FEFO billing, stock expiry reports, CSV and OCR purchase import, and GST reports. Book a free demo or start free.';
$page_key    = 'home';
$extra_css   = ['css/home.css', 'css/estimator.css'];
$extra_js    = ['js/tour.js'];
$brand       = e(BRAND ?: 'our software');
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>

  <main id="main">
    <!-- HERO: "Is this for a store like mine, and what will it fix?" -->
    <!-- HERO (CRO): one goal, a demo call. Promise → proof screen → 2-field slip → low-commitment exits.
         Answers "Is this for a store like mine, what will it fix, and what do I do now?" -->
    <section class="hero3" id="top">
      <div class="container hero3-grid">
        <div class="hero3-copy">
          <p class="eyebrow">For medical stores &amp; pharmacies</p>
          <h1>Bill faster at the counter. <span class="hl">Stop losing money to <span class="scribble">expiry</span>.</span></h1>
          <p class="hero-lead"><?= $brand ?> picks the batch that expires first on every bill, imports distributor bills
            from CSV, and keeps GST and stock right as you sell. Leave your number and we’ll show you, with medicines
            from your own shelf.</p>
          <p class="hero3-alt">
            <a href="#tour"><svg class="icon" aria-hidden="true"><use href="#i-play"/></svg>Watch the 40-second tour</a>
            <a href="<?= e(SITE['app_signup']) ?>">Or start free yourself <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
          </p>
        </div>
        <div class="hero3-form">
<?php $lead_id = 'hl1'; require __DIR__ . '/partials/hero-lead.php'; ?>
        </div>
      </div>
    </section>

<?php require __DIR__ . '/partials/trusted-strip.php'; ?>

<?php require __DIR__ . '/partials/product-tour.php'; ?>

<?php require __DIR__ . '/partials/problems-chat.php'; ?>

    <!-- ESTIMATOR: loss-aversion, their own numbers -->
    <section class="section" id="estimator" aria-labelledby="est-title">
      <div class="container">
        <?php $est_cta = '#demo'; require __DIR__ . '/partials/estimator.php'; ?>
      </div>
    </section>

    <!-- DAY: "How would it fit my routine?" A shift register: time, what happens, in plain sentences. -->
    <section class="section" id="day">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">A day at your pharmacy</p>
          <h2>Built around how a medical store actually runs</h2>
          <p>Not a generic shop billing app. Here’s a normal day, from shutter up to shutter down.</p>
        </div>
        <ol class="shift">
          <li>
            <time>9:00<small>am</small></time>
            <h3>Opening</h3>
            <p>Before the first customer, the dashboard shows what’s expiring soon, what’s below minimum stock in the
              short book, and which supplier payments are due.</p>
          </li>
          <li>
            <time>3:00<small>pm</small></time>
            <h3>Distributor delivery</h3>
            <p>The bill comes in as a CSV or a scanned page, so batch, expiry, scheme and free quantity go in once.
              PTR and margin are worked out when you save.</p>
          </li>
          <li>
            <time>7:30<small>pm</small></time>
            <h3>Counter rush</h3>
            <p>Staff search by brand or generic name, the earliest-expiry batch fills in, and the bill is paid by cash,
              card, UPI or credit.</p>
          </li>
          <li>
            <time>10:00<small>pm</small></time>
            <h3>Closing</h3>
            <p>Collections are split by payment method and by staff member, so the drawer adds up. The day’s report
              arrives on WhatsApp.</p>
          </li>
        </ol>
      </div>
    </section>

    <!-- FEATURES: "Does it have what I need?" -->
    <section class="section" id="features">
      <div class="container">
        <div class="section-head">
          <span class="eyebrow">Features</span>
          <h2>Everything a medical store needs. Nothing it doesn’t.</h2>
          <p>Keep scrolling to walk through each part of the store, or pick one. Some features depend on your plan —
            see <a href="<?= url('pricing') ?>">plans</a>.</p>
        </div>
        <!-- data-scroll-tabs: on wide screens the tabs stick and advance as you scroll (site.js) -->
        <div class="feat-track" data-scroll-tabs>
        <div class="tabs">
          <div class="tablist" role="tablist" aria-label="Product features">
            <button class="tab" role="tab" id="tab-billing" aria-controls="panel-billing" aria-selected="true"><svg
                class="icon" aria-hidden="true">
                <use href="#i-receipt" />
              </svg>Billing &amp; counter</button>
            <button class="tab" role="tab" id="tab-expiry" aria-controls="panel-expiry" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-alert" />
              </svg>Expiry &amp; batch tracking</button>
            <button class="tab" role="tab" id="tab-stock" aria-controls="panel-stock" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-box" />
              </svg>Stock &amp; reorder</button>
            <button class="tab" role="tab" id="tab-purchase" aria-controls="panel-purchase" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-truck" />
              </svg>Purchase &amp; suppliers</button>
            <button class="tab" role="tab" id="tab-gst" aria-controls="panel-gst" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-rupee" />
              </svg>GST &amp; accounts</button>
            <button class="tab" role="tab" id="tab-patients" aria-controls="panel-patients" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-hospital" />
              </svg>Patients &amp; hospital pharmacy</button>
            <button class="tab" role="tab" id="tab-reports" aria-controls="panel-reports" aria-selected="false"
              tabindex="-1"><svg class="icon" aria-hidden="true">
                <use href="#i-chart" />
              </svg>Reports &amp; control</button>
          </div>

          <div>
            <div class="panel panel-shot" role="tabpanel" id="panel-billing" aria-labelledby="tab-billing">
              <div>
                <h3>A counter screen built for the evening rush</h3>
                <p>Search, pick, save, print — with step-by-step walkthroughs for new staff built into the app.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Fast item search</b> — by brand or generic name, or with a barcode reader.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Batch auto-selection (FEFO)</b> — the earliest-expiry batch fills in first.</span>
                  </li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Cash, card, UPI or credit</b> — with patient history and dues on the bill.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Print or share</b> — send the bill by WhatsApp, SMS or email (uses message
                      credits).</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Bring customers back</b> — medicine reminders, loyalty cards and online orders for
                      pick-up or delivery.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-01-counter-billing', 1440, 900, 'Sample screen: Counter billing screen with batch picker and bill summary') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-expiry" aria-labelledby="tab-expiry" hidden>
              <div>
                <h3>Catch expiring stock while you can still act</h3>
                <p>Every item is tracked by batch and expiry from the moment the purchase bill is saved.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Stock expiry report</b> — filter by date, item-wise or supplier-wise.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>First-expiry-first-out billing</b> — older batches go out first.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Purchase returns &amp; credit notes</b> — send short-expiry stock back to the
                      supplier, on record.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Stop expired sales</b> — a store setting controls whether expired items can be
                      billed.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-03-expiry', 1440, 900, 'Sample screen: Stock expiry report, supplier-wise, next 60 days') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-stock" aria-labelledby="tab-stock" hidden>
              <div>
                <h3>Stock numbers you can finally trust</h3>
                <p>Know what you have, what’s running low and what isn’t moving.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Min and max stock per item</b> — stock alerts and a short book of what to
                      order.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Purchase orders &amp; reorder processing</b> — turn the short book into an order
                      (Advance plan).</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Non-moving items report</b> — see what’s blocking your money.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Barcode label printing</b> and recorded stock adjustments.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-05-reorder', 1440, 900, 'Sample screen: Stock alert and short book grouped by supplier') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-purchase" aria-labelledby="tab-purchase" hidden>
              <div>
                <h3>Distributor bills in, without the late nights</h3>
                <p>Record purchases, schemes, returns and payments in one place.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>CSV import</b> — map the distributor’s columns once, then import.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>OCR import</b> — scan a printed bill into a purchase entry (uses OCR
                      credits).</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>PTR, margin and GST input</b> — calculated when you save; stock goes live
                      instantly.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Supplier ledger &amp; dues</b> — plus supplier rate contracts on
                      Enterprise.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-04-purchase-import', 1440, 900, 'Sample screen: Distributor bill imported from a CSV file') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-gst" aria-labelledby="tab-gst" hidden>
              <div>
                <h3>GST-ready bills and clean accounts</h3>
                <p>Stop fixing entries at month-end. Hand your accountant clean data.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>HSN and GST rate on every line</b> — applied from the item master.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>GST report and bill-wise tax report</b> — export to Excel for your CA.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Tally XML export</b> — sales, purchases, returns and payments (Enterprise
                      plan).</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Ledgers, vouchers &amp; expenses</b> — patient, supplier and account
                      ledgers.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-06-gst', 1440, 900, 'Sample screen: September GST report with HSN-wise summary') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-patients" aria-labelledby="tab-patients" hidden>
              <div>
                <h3>Built for hospital pharmacies too</h3>
                <p>Built by a team that also makes hospital software, so it handles patients, not just customers.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>IPD and discharge billing</b> — with patient deposits, dues and ledgers.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Payer and TPA companies</b> — split TPA and non-TPA sales.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Prescriptions from the hospital EMR</b> — arrive at the counter as tokens, shown on a
                      token board.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>ABDM integrated</b> — create or link a patient’s ABHA.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Departments, patient kits &amp; packages</b> — plus Schedule H / H1 item
                      tagging.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-07-hospital', 1440, 900, 'Sample screen: Hospital pharmacy token board with EMR prescription') ?></figure>
            </div>

            <div class="panel panel-shot" role="tabpanel" id="panel-reports" aria-labelledby="tab-reports" hidden>
              <div>
                <h3>See the business, control who does what</h3>
                <p>Reports that answer real questions, and controls that protect your stock and cash.</p>
                <ul class="checks">
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Margin, top-selling and collection reports</b> — plus an executive dashboard on
                      Advance.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Daily report on WhatsApp</b> — the day’s numbers without visiting the
                      shop.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Roles and privileges</b> — staff see only what they need to.</span></li>
                  <li><svg class="icon" aria-hidden="true">
                      <use href="#i-check-c" />
                    </svg><span><b>Two-step login, IP whitelisting and activity report</b> — on the Advance
                      plan.</span></li>
                </ul>
              </div>
              <figure class="shot"><?= svg_img('v2-02-dashboard', 1440, 900, 'Sample screen: Dashboard with sales, expiry and stock alerts') ?></figure>
            </div>
          </div>
        </div>
        </div>
      </div>
    </section>

    <!-- SWITCHING: the #1 objection -->
    <section class="section bg-soft" id="switching">
      <div class="container switch-grid">
        <div class="switch-copy">
          <span class="eyebrow">Switching made safe</span>
          <h2>Change your software without stopping a single bill</h2>
          <p>The biggest worry about switching is the first week. Your old system can keep running while you set up
            the new software on the free plan, then switch over when you’re ready.</p>
          <div class="moved">
            <h3>What you can bring in from a CSV file</h3>
            <ul>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Items with generic name &amp; HSN</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Opening stock with batch &amp; expiry</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>MRP, sale rate &amp; pack size</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Supplier for each stock line</li>
            </ul>
          </div>
          <p class="from">Export from your current software, Excel or registers. A sample sheet is provided, and you
            match your columns to ours before importing.</p>
          <p class="from" data-confirm>Want help? Our team can review your file with you before you import.</p>
          <p class="switch-cta">
            <a class="btn btn-primary" href="#demo" data-cta="demo">Book a free demo <svg class="icon"
                aria-hidden="true">
                <use href="#i-arrow" />
              </svg></a>
          </p>
        </div>
        <ol class="steps">
          <li class="step">
            <div>
              <h3>Create your store account</h3>
              <p>Sign up with your store name and email, and verify with an OTP. The Starter plan is free.</p><span
                class="when">A few minutes</span>
            </div>
          </li>
          <li class="step">
            <div>
              <h3>Import your items and stock</h3>
              <p>Upload a CSV of your items and opening stock. Errors are flagged line by line so you can fix them
                before going live.</p><span class="when">Same day</span>
            </div>
          </li>
          <li class="step">
            <div>
              <h3>Let your staff learn</h3>
              <p>Built-in walkthroughs for sales, purchase and item master show each step on screen. Keyboard
                shortcuts keep billing quick.</p><span class="when">A short session</span>
            </div>
          </li>
          <li class="step">
            <div>
              <h3>Go live, then upgrade if you need to</h3>
              <p>Run both systems side by side if you like. Move to Basic or Advance when you need unlimited bills
                and more users — switch or cancel anytime.</p><span class="when">You choose the day</span>
            </div>
          </li>
        </ol>
      </div>
    </section>

    <!-- SOLUTIONS: "Will it grow with me?" -->
    <section class="section" id="solutions">
      <div class="container">
        <div class="section-head center">
          <span class="eyebrow">Who it’s for</span>
          <h2>One store, a chain, or a hospital — it fits the way you work</h2>
        </div>
        <div class="sol-grid">
          <article class="sol featured">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-store" />
              </svg></div>
            <p class="who">Basic or Advance plan</p>
            <h3>Single medical store</h3>
            <p>For the owner-pharmacist who wants faster billing, fewer expiry losses and less paperwork.</p>
            <ul class="checks">
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Billing, expiry, purchase &amp; GST</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>3 to 10 users with roles</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Start free, upgrade anytime</li>
            </ul>
          </article>
          <article class="sol">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-stores" />
              </svg></div>
            <p class="who">Enterprise plan</p>
            <h3>Pharmacy chains</h3>
            <p>For owners and operations heads who need every store to follow the same rules.</p>
            <ul class="checks">
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Central administration &amp; linked outlets</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Stock transfers between stores</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Central &amp; multi-store reports</li>
            </ul>
          </article>
          <article class="sol">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-hospital" />
              </svg></div>
            <p class="who">Hospitals &amp; clinics</p>
            <h3>In-house pharmacy</h3>
            <p>For hospitals and nursing homes that run a pharmacy for OPD and admitted patients.</p>
            <ul class="checks">
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>IPD, discharge &amp; TPA billing</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Department stock transfers</li>
              <li><svg class="icon" aria-hidden="true">
                  <use href="#i-check" />
                </svg>Works with hospital EMR &amp; ABDM</li>
            </ul>
          </article>
        </div>
      </div>
    </section>

    <!-- CHECKLIST: selection criteria, honest guide -->
    <section class="section bg-soft" id="checklist">
      <div class="container">
        <div class="section-head">
          <span class="eyebrow">Before you choose</span>
          <h2>8 questions to ask any pharmacy software company</h2>
          <p>Whoever you buy from, get clear answers to these. Here are ours.</p>
        </div>
        <table class="check-table">
          <thead>
            <tr>
              <th scope="col">Ask this</th>
              <th scope="col">Why it matters</th>
              <th scope="col">Our answer</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Can a new staff member bill on day one?</td>
              <td>Staff change often. Training shouldn’t take weeks.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Built-in walkthroughs show each step on screen, and shortcuts keep billing quick.</td>
            </tr>
            <tr>
              <td>Does billing pick the earliest-expiry batch?</td>
              <td>This is where most expiry losses start.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Yes — batch auto-selection (FEFO), plus a stock expiry report.</td>
            </tr>
            <tr>
              <td>How do I get my data in?</td>
              <td>Re-typing thousands of items is not an option.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Import items and opening stock from CSV, with column matching and line-by-line error checks.</td>
            </tr>
            <tr data-confirm>
              <td>What happens when the internet goes down?</td>
              <td>The counter can’t stop in the evening rush.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>It runs in your web browser. [Confirm offline behaviour before publishing.]</td>
            </tr>
            <tr>
              <td>What will I pay in year two?</td>
              <td>Low first-year prices and costly renewals are common.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Prices are published on our <a href="<?= url('pricing') ?>">pricing page</a>. Pay for 1, 3 or 5 years; switch or cancel anytime.</td>
            </tr>
            <tr data-confirm>
              <td>Who do I call when billing stops?</td>
              <td>Support hours should match shop hours.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Call support on +91 89808 80819 or raise a ticket at the help centre.</td>
            </tr>
            <tr data-confirm>
              <td>Can I take my data with me if I leave?</td>
              <td>It’s your business record, not the vendor’s.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Item lists and reports export to CSV or Excel. [Confirm full data export.]</td>
            </tr>
            <tr>
              <td>Will it work with my printer and scanner?</td>
              <td>New hardware is an unplanned cost.</td>
              <td><svg class="icon" aria-hidden="true">
                  <use href="#i-check-c" />
                </svg>Thermal and A4/A5 bill formats, barcode label printing, and USB barcode readers.</td>
            </tr>
          </tbody>
        </table>
        <div class="check-foot">
          <p><b>Comparing a few options?</b> Get this checklist as a one-page PDF to use in every demo.</p>
          <a class="btn btn-secondary" href="#demo" data-cta="checklist" data-confirm><svg class="icon"
              aria-hidden="true">
              <use href="#i-file" />
            </svg>Send me the checklist</a>
        </div>
      </div>
    </section>

    <!-- SUPPORT: "Who helps me when it breaks?" -->
    <section class="section" id="support">
      <div class="container support-grid">
        <div class="support-copy">
          <span class="eyebrow">Support</span>
          <h2>Real people who understand pharmacies</h2>
          <p>When something goes wrong at the counter, you need an answer fast — from people who build software for
            pharmacies and hospitals every day.</p>
          <ul class="checks">
            <li><svg class="icon" aria-hidden="true">
                <use href="#i-check-c" />
              </svg><span><b>Phone support</b> on +91 89808 80819</span></li>
            <li><svg class="icon" aria-hidden="true">
                <use href="#i-check-c" />
              </svg><span><b>Priority support</b> on the Enterprise plan</span></li>
            <li data-confirm><svg class="icon" aria-hidden="true">
                <use href="#i-check-c" />
              </svg><span><b>Support hours and languages</b> — [confirm before publishing]</span></li>
          </ul>
        </div>
        <div class="channels">
          <article class="channel">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-headset" />
              </svg></div>
            <h3>Phone</h3>
            <p>Call <a href="tel:<?= SITE['support_tel'] ?>">+91 89808 80819</a> and talk it through.</p>
          </article>
          <article class="channel">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-chat" />
              </svg></div>
            <h3>Help centre</h3>
            <p>Raise and track a ticket at the <a href="<?= e(SITE['help_url']) ?>">help centre</a>.</p>
          </article>
          <article class="channel">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-play" />
              </svg></div>
            <h3>In-app walkthroughs</h3>
            <p>Step-by-step guides for sales, purchase, items and accounts, inside the app.</p>
          </article>
          <article class="channel">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-screen" />
              </svg></div>
            <h3>Keyboard shortcuts</h3>
            <p>A shortcuts panel in the app shows the keys for every screen.</p>
          </article>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section" id="faq">
      <div class="container faq-grid">
        <div class="faq-intro">
          <span class="eyebrow">FAQ</span>
          <h2>Questions pharmacy owners ask us</h2>
          <p>Don’t see yours? Call us — you’ll get a straight answer.</p>
          <a class="btn btn-secondary" href="tel:<?= SITE['support_tel'] ?>"><svg class="icon" aria-hidden="true">
              <use href="#i-phone" />
            </svg>Call +91 89808 80819</a>
        </div>
        <div class="faq-list">
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-1" id="faq-b1">Is there really a free
                plan?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-1" role="region" aria-labelledby="faq-b1" hidden>
              <p>Yes. The Starter plan is free forever, for one user, with 10 sales bills and 25 purchase bills a month.
                It’s enough to set up your items and try billing. When you need unlimited bills, move to Basic or
                Advance.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-2" id="faq-b2">Will my billing stop
                while we switch?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-2" role="region" aria-labelledby="faq-b2" hidden>
              <p>No. Keep your current software running while you set up the new software and import your items and
                stock. Go live on the day you choose.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-3" id="faq-b3">Can I bring over data
                from my current software?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-3" role="region" aria-labelledby="faq-b3" hidden>
              <p>Yes, through CSV import. Export your items and stock from your current software or Excel, match your
                columns to ours using the sample sheet, and import. Rows with errors are flagged so you can fix them.</p>
            </div>
          </div>
          <div class="faq-item" data-confirm>
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-4" id="faq-b4">What if the internet
                goes down?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-4" role="region" aria-labelledby="faq-b4" hidden>
              <p>It runs in your web browser. [To confirm with the product team: describe exactly how
                billing behaves offline. Do not publish until confirmed.]</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-5" id="faq-b5">Can I export to
                Tally?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-5" role="region" aria-labelledby="faq-b5" hidden>
              <p>Tally XML export is part of the Enterprise plan. On Basic and Advance, the GST report and other reports
                export to Excel for your accountant.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-6" id="faq-b6">Does it handle
                Schedule H and H1 medicines?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-6" role="region" aria-labelledby="faq-b6" hidden>
              <p>Items can be tagged as Schedule H or H1 in the item master, and bills can carry patient and doctor
                details. <span data-confirm>[Confirm whether a dedicated H1 register report is available.]</span>
                Your licence obligations stay with your pharmacy — check current rules with your compliance adviser.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-7" id="faq-b7">Does it work with my
                printer and barcode scanner?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-7" role="region" aria-labelledby="faq-b7" hidden>
              <p>Bills print in thermal and A4/A5 formats, and you can print barcode labels for your stock. USB barcode
                readers work for item entry. Tell us your models during the demo and we’ll check them.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-8" id="faq-b8">Is my login
                secure?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-8" role="region" aria-labelledby="faq-b8" hidden>
              <p>Every user has their own login and role. On Advance and above you can turn on two-step OTP login,
                limit access to your store’s IP addresses, and force-logout a user.</p>
            </div>
          </div>
          <div class="faq-item">
            <h3><button class="faq-btn" aria-expanded="false" aria-controls="faq-9" id="faq-b9">Can I use it for more
                than one store?<svg class="icon" aria-hidden="true">
                  <use href="#i-plus" />
                </svg></button></h3>
            <div class="faq-ans" id="faq-9" role="region" aria-labelledby="faq-b9" hidden>
              <p>Yes, on the Enterprise plan: central administration with linked outlets, stock transfers between
                stores, and central and multi-store reports.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FINAL CTA + FORM -->
    <section class="final" id="demo" aria-labelledby="demo-title">
      <div class="container final-grid">
        <div class="final-copy">
          <span class="eyebrow light">Free demo</span>
          <h2 id="demo-title">See it working with medicines from your own shelf</h2>
          <p>A short demo on a call, at a time that suits you. No pressure, no obligation. Prefer to try it yourself?
            <a href="<?= e(SITE['app_signup']) ?>" style="color:#fff">Start free on the Starter plan</a>.</p>
          <ol class="next" data-confirm>
            <li>
              <div><b>We call you within one working day</b>To understand your store and current software.</div>
            </li>
            <li>
              <div><b>You see your own items in the demo</b>Billing, expiry report and purchase entry — the way you’d
                use them.</div>
            </li>
            <li>
              <div><b>You pick the right plan</b>From the free Starter plan to Enterprise for chains.</div>
            </li>
          </ol>
        </div>

        <div class="form-card">
          <form id="demo-form" novalidate>
            <h3>Book your free demo</h3>
            <p>Tell us about your store and we’ll call you.</p>
            <div class="form-grid">
              <div class="fld"><label for="f-name">Your name</label><input id="f-name" name="name" autocomplete="name"
                  required><span class="err">Please enter your name.</span></div>
              <div class="fld"><label for="f-phone">Mobile number</label><input id="f-phone" name="phone" type="tel"
                  inputmode="numeric" autocomplete="tel" placeholder="10-digit mobile" required
                  pattern="[6-9][0-9]{9}"><span class="err">Please enter a 10-digit mobile number.</span></div>
              <div class="fld"><label for="f-store">Store or hospital name</label><input id="f-store" name="store"
                  autocomplete="organization" required><span class="err">Please enter your store name.</span></div>
              <div class="fld"><label for="f-city">City</label><input id="f-city" name="city"
                  autocomplete="address-level2" required><span class="err">Please enter your city.</span></div>
              <div class="fld full"><label for="f-current">What do you use today?</label>
                <select id="f-current" name="current">
                  <option value="">Select one</option>
                  <option>Desktop pharmacy software</option>
                  <option>General billing app</option>
                  <option>Excel or paper registers</option>
                  <option>Hospital software</option>
                  <option>Opening a new store</option>
                </select>
              </div>
              <div class="fld full"><label for="f-want">I’d like to</label>
                <select id="f-want" name="want">
                  <option value="demo">Book a free demo</option>
                  <option value="quote">Get Enterprise pricing for my stores</option>
                  <option value="checklist">Get the buyer’s checklist</option>
                </select>
              </div>
            </div>
            <button class="btn btn-primary" type="submit"><span id="submit-label">Book my free demo</span> <svg
                class="icon" aria-hidden="true">
                <use href="#i-arrow" />
              </svg></button>
            <p class="form-small" data-confirm>We’ll only use your details to contact you about this request. See our
              <a href="<?= url('privacy') ?>">privacy policy</a>.</p>
          </form>
          <div class="form-success" id="form-success" hidden tabindex="-1">
            <div class="sol-ic"><svg class="icon" aria-hidden="true">
                <use href="#i-check" />
              </svg></div>
            <h3>Thank you — we’ll call you soon</h3>
            <p>Prototype only: nothing was sent.</p>
          </div>
        </div>
      </div>
    </section>

  </main>

<?php $hide_cta = true; require __DIR__ . '/partials/footer.php'; ?>
