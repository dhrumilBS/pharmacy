<?php
/**
 * Product tour "video": a scripted HTML animation in a browser frame (assets/js/tour.js + home.css).
 * Crisp at any size (it's DOM, scaled), no audio, no captions. Only two controls: play/pause and full screen.
 * The stage is decorative (aria-hidden); #tour-desc describes it for screen readers.
 * Every number is sample data and matches the screenshots (Shree Sai Medical Store, INV-26-04813…).
 * Features shown are verified in the app: FEFO batch pick, barcode reader, bill share on WhatsApp
 * (message credits), CSV purchase import, stock expiry report, purchase returns.
 */
$slug = strtolower(BRAND ?: 'app');
?>
<section class="section tour" id="tour" aria-labelledby="tour-t">
  <div class="container">
    <div class="tour-head">
      <p class="eyebrow">Product tour · 40 seconds</p>
      <h2 id="tour-t">Watch one bill go from <span class="scribble-u">scan to print</span></h2>
      <p>Then a distributor bill comes in, and the expiry report catches what’s about to go to waste. Same screens your counter staff would use.</p>
    </div>

    <div class="tour-player" data-tour aria-describedby="tour-desc">
      <div class="tour-fit">
        <div class="tour-stage" aria-hidden="true">
          <div class="bw">
            <div class="bw-bar">
              <span class="bw-dots"><i></i><i></i><i></i></span>
              <span class="bw-tab"><svg class="brand-logo"><use href="#logo-mark"/></svg><span data-tab-title>Dashboard</span><b>×</b></span>
              <span class="bw-url"><svg viewBox="0 0 24 24"><path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v10H5z"/></svg><b><?= e($slug) ?></b><span data-url>/dashboard</span></span>
            </div>
            <div class="app">
              <aside class="app-side">
                <svg class="brand-logo"><use href="#logo-mark"/></svg>
                <span class="nav-i" data-nav="dash"><svg><use href="#i-chart"/></svg>Home</span>
                <span class="nav-i" data-nav="bill"><svg><use href="#i-receipt"/></svg>Sales</span>
                <span class="nav-i" data-nav="buy"><svg><use href="#i-truck"/></svg>Purchase</span>
                <span class="nav-i" data-nav="exp"><svg><use href="#i-alert"/></svg>Expiry</span>
                <span class="nav-i"><svg><use href="#i-box"/></svg>Stock</span>
              </aside>
              <div class="app-main">
                <header class="app-top">
                  <span class="store"><b>Shree Sai Medical Store</b><small>Main branch · Ahmedabad</small></span>
                  <span class="t-who"><i>P</i>Priya · Counter 1</span>
                </header>

                <div class="scenes">
                  <!-- 1 · Dashboard -->
                  <section class="sc" data-scene="dash">
                    <div class="sc-head"><div><h3>Good evening, Priya</h3><small>Monday, 12 Oct</small></div>
                      <span class="t-btn t-new"><svg><use href="#i-plus"/></svg>New sale</span></div>
                    <div class="kpis">
                      <div class="kpi"><small>Today’s sales</small><b>₹42,380</b><span>186 bills</span></div>
                      <div class="kpi"><small>Collected</small><b>₹39,910</b><span>UPI 58% · Cash 37%</span></div>
                      <div class="kpi kpi-warn"><small>Expiring in 60 days</small><b>₹18,640</b><span>23 batches</span></div>
                      <div class="kpi"><small>Short book</small><b>14 items</b><span>below minimum</span></div>
                    </div>
                    <div class="dash-row">
                      <div class="dash-chart"><small>Sales · last 7 days</small>
                        <div class="tbars"><i style="--h:52%"></i><i style="--h:66%"></i><i style="--h:58%"></i><i style="--h:71%"></i><i style="--h:64%"></i><i style="--h:93%"></i><i style="--h:79%"></i></div>
                        <div class="tdays"><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span><span>Mon</span></div>
                      </div>
                      <div class="dash-todo"><small>Needs attention today</small>
                        <p><b>Azithral 500 · AZ1189</b><span>Expires in 28 days · 14 strips</span></p>
                        <p><b>Telma 40 Tab</b><span>Below minimum · 6 left</span></p>
                        <p><b>Shree Krishna Pharma</b><span>Payment due Thursday · ₹24,180</span></p>
                      </div>
                    </div>
                  </section>

                  <!-- 2 · Billing -->
                  <section class="sc" data-scene="bill">
                    <div class="sc-head"><div><h3>New sale <em>INV-26-04813</em></h3></div><span class="chip">FEFO on</span></div>
                    <div class="bill">
                      <div class="bill-l">
                        <div class="t-search"><svg><use href="#i-search"/></svg><span class="t-typed" data-typed="search"></span><span class="caret"></span><span class="ph">Scan or search item</span><kbd>F2</kbd>
                          <div class="t-dd">
                            <div class="dd-o dd-pick"><b>Dolo 650 Tab</b><small>Paracetamol 650 mg · 3 batches</small></div>
                            <div class="dd-o"><b>Dolo 500 Tab</b><small>Paracetamol 500 mg · 1 batch</small></div>
                          </div>
                          <div class="t-batches">
                            <small>Batches · nearest expiry first</small>
                            <div class="bt bt-pick"><b>DL4521</b><span>Exp Nov 2026</span><span>45 strips</span><em>✓ Sell first</em></div>
                            <div class="bt"><b>DL4790</b><span>Exp Jun 2028</span><span>55 strips</span><em>Later</em></div>
                            <div class="bt"><b>DL5013</b><span>Exp Jan 2029</span><span>30 strips</span><em>Later</em></div>
                          </div>
                        </div>
                        <table class="tlines">
                          <thead><tr><th>Item</th><th>Batch</th><th>Exp</th><th class="n">Qty</th><th class="n">Amount</th></tr></thead>
                          <tbody>
                            <tr class="t-row t-row1"><td><b>Shelcal 500 Tab</b><small>Calcium + D3 · strip of 15</small></td><td>SC7731</td><td>12/26</td><td class="n">1</td><td class="n">₹128.40</td></tr>
                            <tr class="t-row t-row2"><td><b>Dolo 650 Tab</b><small>Paracetamol 650 mg · strip of 15</small></td><td>DL4521</td><td>11/26</td><td class="n"><span class="t-qty">1</span></td><td class="n t-amt2">₹30.91</td></tr>
                            <tr class="t-empty"><td colspan="5">Scan a barcode or press F2 to search</td></tr>
                          </tbody>
                        </table>
                      </div>
                      <div class="bill-r">
                        <label>Patient</label>
                        <div class="t-field t-patient"><span class="t-typed" data-typed="patient"></span><span class="caret"></span><span class="ph">Name or mobile</span></div>
                        <div class="t-pchip">Ramesh Patel · 98xxxxxx21 · no dues</div>
                        <label>Payment</label>
                        <div class="seg"><span>Cash</span><span class="t-upi">UPI</span><span>Card</span><span>Credit</span></div>
                        <div class="tot"><span><span class="t-items">0 items</span> · GST incl.</span><b class="t-total">₹0.00</b></div>
                        <span class="t-btn t-save">Save &amp; print <kbd>Ctrl+S</kbd></span>
                      </div>
                    </div>
                  </section>

                  <!-- 3 · Purchase import -->
                  <section class="sc" data-scene="buy">
                    <div class="sc-head"><div><h3>Purchase <em>Import distributor bill</em></h3></div><span class="chip">Shree Krishna Pharma</span></div>
                    <div class="buy">
                      <div class="t-drop"><svg><use href="#i-upload"/></svg><b class="t-droplabel">Drop the distributor’s CSV here</b><small>Columns matched once, remembered for next time</small>
                        <div class="t-prog"><i></i></div></div>
                      <table class="tlines plines">
                        <thead><tr><th>Item</th><th>Batch</th><th>Exp</th><th class="n">Qty + free</th><th class="n">PTR</th><th></th></tr></thead>
                        <tbody>
                          <tr class="t-prow"><td>Pan 40 Tab</td><td>PN2291</td><td>08/28</td><td class="n">20 + 2</td><td class="n">₹89.60</td><td class="ok">✓</td></tr>
                          <tr class="t-prow"><td>Augmentin 625 Duo</td><td>AG7710</td><td>03/28</td><td class="n">10</td><td class="n">₹171.20</td><td class="ok">✓</td></tr>
                          <tr class="t-prow"><td>Telma 40 Tab</td><td>TL0932</td><td>11/27</td><td class="n">30 + 3</td><td class="n">₹151.80</td><td class="ok">✓</td></tr>
                          <tr class="t-prow"><td>Glycomet GP 1</td><td>GM5521</td><td>06/28</td><td class="n">20</td><td class="n">₹96.40</td><td class="ok">✓</td></tr>
                          <tr class="t-prow"><td>Montair LC Tab</td><td>ML0412</td><td>01/28</td><td class="n">15 + 1</td><td class="n">₹178.10</td><td class="ok">✓</td></tr>
                        </tbody>
                      </table>
                      <div class="t-psum"><b>38 / 38 lines read</b><span>batch · expiry · MRP · PTR · GST</span><em>Stock updated</em></div>
                    </div>
                    <div class="t-file"><svg><use href="#i-file"/></svg><span><b>SKP-2026-4471.csv</b><small>38 rows · 6 KB</small></span></div>
                  </section>

                  <!-- 4 · Expiry report -->
                  <section class="sc" data-scene="exp">
                    <div class="sc-head"><div><h3>Stock expiry report <em>Supplier-wise</em></h3></div>
                      <span class="filters"><span>All</span><span>30 days</span><span class="t-f60">60 days</span><span>90 days</span></span></div>
                    <div class="exp">
                      <div class="erow t-erow"><span><b>Azithral 500 Tab</b><small>AZ1189 · 14 strips · Shree Krishna Pharma</small></span><span class="days"><i style="--d:31%"></i>28 days</span><span class="ev">₹1,652</span><span class="t-btn t-ret">Purchase return</span></div>
                      <div class="erow t-erow"><span><b>Montair LC Tab</b><small>ML0388 · 9 strips · Mahavir Distributors</small></span><span class="days"><i style="--d:50%"></i>45 days</span><span class="ev">₹1,603</span><span class="ghost">Return</span></div>
                      <div class="erow t-erow"><span><b>Shelcal 500 Tab</b><small>SC7731 · 22 strips · Shree Krishna Pharma</small></span><span class="days"><i style="--d:64%"></i>58 days</span><span class="ev">₹2,825</span><span class="ghost">Return</span></div>
                      <div class="erow t-erow"><span><b>Becosules Cap</b><small>BC2280 · 31 strips · Om Sai Agencies</small></span><span class="days"><i style="--d:66%"></i>59 days</span><span class="ev">₹1,488</span><span class="ghost">Return</span></div>
                      <div class="e-sum t-esum"><span>23 batches in the next 60 days</span><b>₹18,640 at risk</b></div>
                    </div>
                  </section>
                </div>
              </div>
            </div>
            <div class="t-toast" data-toast></div>
          </div>

          <!-- Barcode scanner and printed receipt (billing scene) -->
          <div class="t-scanner"><div class="pack"><b>SHELCAL 500</b><small>Calcium + Vitamin D3</small><?= barcode_svg('8901234567890', 44, 'pack-bc') ?><span>8 901234 567890</span></div><i class="beam"></i></div>
          <div class="t-receipt">
            <b>Shree Sai Medical Store</b><small>INV-26-04813 · 12 Oct 2026</small>
            <p><span>Shelcal 500 Tab ×1</span><span>₹128.40</span></p>
            <p><span>Dolo 650 Tab ×2</span><span>₹61.82</span></p>
            <p class="r-tot"><span>Total</span><span>₹190.22</span></p>
            <?= barcode_svg('INV-26-04813', 26, 'r-bc') ?>
          </div>

          <!-- Cursor: shadow drawn inside the SVG (no CSS filter, which left ghost trails while moving) -->
          <svg class="t-cursor" viewBox="0 0 24 24"><g class="t-arrow"><path class="t-sh" d="M4 2l15 11-6.5 1.2 3.8 7.3-3 1.5-3.8-7.4L4 20z" transform="translate(.7 1.3)"/><path d="M4 2l15 11-6.5 1.2 3.8 7.3-3 1.5-3.8-7.4L4 20z"/></g></svg>
          <span class="t-ripple"></span>
        </div>

        <!-- End card sits outside the aria-hidden stage so its link is reachable -->
        <div class="tour-end" hidden>
          <p class="end-k">That’s the counter, sorted.</p>
          <p class="end-s">Want to see it with medicines from your own shelf?</p>
          <a class="btn btn-primary" href="#top" data-cta="demo" data-focus-lead>Book a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
      </div>

      <div class="tour-ui">
        <button class="tour-btn" type="button" data-tour-play aria-label="Play product tour">
          <svg class="i-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15l13-7.5z"/></svg>
          <svg class="i-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
        </button>
        <span class="tour-progress" aria-hidden="true"><i></i></span>
        <button class="tour-btn" type="button" data-tour-fs aria-label="Full screen">
          <svg class="i-fs" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/></svg>
          <svg class="i-fs-x" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4v5H4M20 9h-5V4M15 20v-5h5M4 15h5v5"/></svg>
        </button>
      </div>
    </div>
    <p class="sr-only" id="tour-desc">Animated product tour, about 40 seconds, no sound. A counter staff member scans
      Shelcal 500 and its nearest-expiry batch is added; searches “dolo”, picks Dolo 650 and the batch expiring first
      fills in; sets quantity 2, adds patient Ramesh Patel, chooses UPI and saves, and the bill prints and is shared on
      WhatsApp. Next, a distributor’s CSV file is dropped on the purchase screen and 38 lines are read with batch,
      expiry and PTR. Finally the stock expiry report is filtered to 60 days, showing ₹18,640 at risk, and a purchase
      return is drafted. All data is sample data.</p>
    <span class="sample-tag tour-tag">Animated walkthrough · sample data</span>
  </div>
</section>
