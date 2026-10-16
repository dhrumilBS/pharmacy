<?php
/**
 * Product tour v2 ("video"): a scripted HTML animation in a browser frame (assets/js/tour.js + assets/css/tour.css).
 * Layout follows the real app about 70/30: its top menu, dashboard tiles, sale grid + patient panel, purchase grid
 * with free qty / PTR / margin diff, stock expiry report with item-wise and supplier-wise tabs, and the bottom
 * action bar (Save draft · Done · Save & print). The 30% new: Campton type, softer surfaces, a segmented payment
 * picker, a CSV drop, and the scanner / receipt / toast overlays.
 * Data: products, batches, packs, expiry dates, MRP and PTR come from the app's test account (15 Oct 2026). The store,
 * staff, patient and supplier names are our sample names (no real people or businesses on a public page). The second
 * Zerodol batch (ZS2611) is added so the FEFO pick has two batches to choose from.
 * Previous version: archive/video/v1/. Only two controls: play/pause and full screen. No audio.
 */
$slug = strtolower(BRAND ?: 'app');
?>
<section class="section tour" id="tour" aria-labelledby="tour-t">
  <div class="container">
    <div class="tour-head">
      <p class="eyebrow">Product tour · 40 seconds</p>
      <h2 id="tour-t">Watch one bill go from <span class="scribble-u">scan to print</span></h2>
      <p>Then a distributor bill comes in with its margins checked, and the expiry report lines up what to send back. The same screens your counter staff use.</p>
    </div>

    <div class="tour-player" data-tour aria-describedby="tour-desc">
      <div class="tour-fit">
        <div class="tour-stage" aria-hidden="true">
          <div class="bw">
            <div class="bw-bar">
              <span class="bw-dots"><i></i><i></i><i></i></span>
              <span class="bw-tab"><svg class="brand-logo"><use href="#logo-mark"/></svg><span data-tab-title>Dashboard</span><b>×</b></span>
              <span class="bw-url"><svg viewBox="0 0 24 24"><path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v10H5z"/></svg><b><?= e($slug) ?></b><span data-url>/pharmacy/dashboard</span></span>
            </div>
            <div class="ap">
              <header class="ap-top">
                <svg class="brand-logo"><use href="#logo-mark"/></svg>
                <nav class="ap-nav">
                  <span class="ap-n" data-nav="dash"><svg><use href="#i-chart"/></svg>Dashboard</span>
                  <span class="ap-n"><svg><use href="#i-users"/></svg>Account Master</span>
                  <span class="ap-n t-nav-sale" data-nav="bill"><svg><use href="#i-receipt"/></svg>Sale</span>
                  <span class="ap-n t-nav-buy" data-nav="buy"><svg><use href="#i-truck"/></svg>Purchase</span>
                  <span class="ap-n"><svg><use href="#i-box"/></svg>Item Master</span>
                  <span class="ap-n"><svg><use href="#i-upload"/></svg>Initialization</span>
                  <span class="ap-n t-nav-rep" data-nav="exp"><svg><use href="#i-file"/></svg>Reports<i>▾</i>
                    <span class="t-rep-dd">
                      <span>Stock Alert Report</span>
                      <span class="t-rep-exp">Stock Expiry Report</span>
                      <span>Margin Report</span>
                      <span>GST Report</span>
                    </span>
                  </span>
                </nav>
                <span class="ap-right">
                  <span class="ap-plan">Advance · 31-03-2027</span>
                  <span class="ap-ic"><svg><use href="#i-search"/></svg></span>
                  <span class="ap-me">P</span>
                </span>
              </header>

              <div class="scenes">
                <!-- 1 · Dashboard (today's tiles, collection by method, stock alert: values from the test account) -->
                <section class="sc" data-scene="dash">
                  <div class="d-head">
                    <b class="d-store"><svg><use href="#i-store"/></svg>Shree Sai Medical Store</b>
                    <span class="d-f"><small>Select store</small><b>Main branch ▾</b></span>
                    <span class="d-f"><small>Date</small><b>15-10-2026 → 15-10-2026</b></span>
                    <span class="d-f"><small>User</small><b>All ▾</b></span>
                    <span class="ab g"><svg><use href="#i-upload"/></svg>Download</span>
                  </div>
                  <div class="d-grid">
                    <div class="d-kpis">
                      <div class="kp"><small>Sales</small><b class="up t-k1">₹731.00</b><span>2 invoices</span></div>
                      <div class="kp"><small>Sales return</small><b class="dn">₹0.00</b><span>0 invoices</span></div>
                      <div class="kp"><small>Purchase</small><b class="dn t-k2">₹1,561.00</b><span>1 invoice</span></div>
                      <div class="kp"><small>Purchase return</small><b class="up">₹0.00</b><span>0 invoices</span></div>
                      <div class="kp"><small>Profit</small><b class="up t-k3">₹213.35</b><span>Sales profit</span></div>
                      <div class="kp"><small>Expense</small><b class="dn t-k4">₹1,561.00</b><span>Purchase expense</span></div>
                    </div>
                    <div class="d-card">
                      <div class="d-tabs"><span class="on">Overall collection</span><span>Deposit collection</span></div>
                      <table class="d-tbl">
                        <thead><tr><th>Method</th><th class="n">Sales</th><th class="n">Return</th><th class="n">Total</th></tr></thead>
                        <tbody>
                          <tr><td><b>Cash</b></td><td class="n">₹731.00</td><td class="n">₹0.00</td><td class="n"><b>₹731.00</b></td></tr>
                          <tr><td><b>Bank</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>UPI</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Card</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Rokda</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Razorpay</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Outstanding</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr class="d-tot"><td><b>Total</b></td><td class="n"><b>₹731.00</b></td><td class="n">₹0.00</td><td class="n"><b>₹731.00</b></td></tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-card">
                      <div class="d-tabs"><span>Stock expiry</span><span class="on">Stock alert</span></div>
                      <table class="d-tbl">
                        <thead><tr><th>Product</th><th class="n">Stock</th><th class="n">Min</th></tr></thead>
                        <tbody>
                          <tr><td><b>THYRONORM 100MCG TAB</b></td><td class="n lo">3</td><td class="n">6</td></tr>
                          <tr><td><b>RABLET 20MG TAB</b></td><td class="n lo">9</td><td class="n">120</td></tr>
                          <tr><td><b>TRENTAL 400MG TAB</b></td><td class="n lo">19</td><td class="n">38</td></tr>
                          <tr><td><b>TELMA AMH 40MG TAB</b></td><td class="n lo">15</td><td class="n">45</td></tr>
                          <tr><td><b>UPRISE D3 60K CAP</b></td><td class="n lo">16</td><td class="n">46</td></tr>
                          <tr><td><b>ZINCOVIT TAB</b></td><td class="n lo">32</td><td class="n">71</td></tr>
                          <tr><td><b>CETZINE TAB</b></td><td class="n lo">81</td><td class="n">102</td></tr>
                          <tr><td><b>STAMLO 2.5MG TAB</b></td><td class="n lo">149</td><td class="n">210</td></tr>
                        </tbody>
                      </table>
                      <p class="d-note">8 items below minimum · added to the short book</p>
                    </div>
                  </div>
                </section>

                <!-- 2 · Sale -->
                <section class="sc" data-scene="bill">
                  <div class="s-bar">
                    <span>System discount <span class="tgl">ON</span></span>
                    <span>Sales on <b>MRP ▾</b></span>
                    <span class="chip">FEFO on</span>
                    <span class="s-sp"></span>
                    <span class="ab g"><svg><use href="#i-users"/></svg>Patient list</span>
                    <span class="ab"><svg><use href="#i-chart"/></svg>Sales today</span>
                    <span class="ab"><svg><use href="#i-file"/></svg>Drafts</span>
                    <span class="ab r"><svg><use href="#i-x"/></svg>Clear screen</span>
                  </div>
                  <div class="s-body">
                    <div class="s-main">
                      <table class="g-tbl">
                        <thead><tr><th class="sr">Sr</th><th>Item name</th><th>Pack</th><th>Batch</th><th>Expiry</th><th class="n">MRP</th><th class="n">Qty</th><th class="n">GST %</th><th class="n">Margin %</th><th class="n">Amount</th></tr></thead>
                        <tbody>
                          <tr class="t-row t-row1"><td class="sr">1</td><td><b>GLUCONORM VG 2 FORTE TAB</b></td><td>1×15</td><td>SGZL0125</td><td>04/28</td><td class="n">421.05</td><td class="n">1</td><td class="n">5</td><td class="n">23.81</td><td class="n"><b>421.05</b></td></tr>
                          <tr class="t-row t-row2"><td class="sr">2</td><td><b>ZERODOL SP TABLET</b></td><td>1×10</td><td>420</td><td>12/27</td><td class="n">139.68</td><td class="n"><span class="t-qty">1</span></td><td class="n">5</td><td class="n">23.80</td><td class="n"><b class="t-amt2">139.68</b></td></tr>
                          <tr class="t-srow"><td class="sr t-srn">1</td><td colspan="9">
                            <div class="t-search"><svg><use href="#i-search"/></svg><span class="t-typed" data-typed="search"></span><span class="caret"></span><span class="ph">Search items or scan a barcode</span><kbd>F2</kbd>
                              <div class="t-dd">
                                <div class="dd-o dd-pick"><b>ZERODOL SP TABLET (10 TABLET)</b><small>Aceclofenac + Paracetamol + Serratiopeptidase · 2 batches</small></div>
                              </div>
                              <div class="t-batches">
                                <small>Batches · nearest expiry first</small>
                                <div class="bt bt-pick"><b>420</b><span>Exp 12/27</span><span>100 in stock</span><em>✓ Sell first</em></div>
                                <div class="bt"><b>ZS2611</b><span>Exp 09/28</span><span>60 in stock</span><em>Later</em></div>
                              </div>
                            </div>
                          </td></tr>
                        </tbody>
                      </table>
                      <p class="s-info"><span>Generic <b class="t-gen">—</b></span><span>Category <b>Tablet</b></span><span>Location <b>Rack B2</b></span></p>
                    </div>
                    <aside class="s-pat">
                      <p class="s-radio"><span class="lbl">Search type</span><span class="on">OPD</span><span>IPD</span></p>
                      <label>Patient name</label>
                      <div class="pf t-patient"><span class="t-typed" data-typed="patient"></span><span class="caret"></span><span class="ph">Search patient or UHID</span></div>
                      <div class="t-pchip">Ramesh Patel · HR-104582 · no dues</div>
                      <label>Contact no.</label>
                      <div class="pf"><span class="t-phone ph">+91 Contact no.</span></div>
                      <label>Doctor name</label>
                      <div class="pf"><span class="t-doc ph">Search doctor</span></div>
                      <label>GST no.</label>
                      <div class="pf"><span class="ph">GST no.</span></div>
                    </aside>
                  </div>
                  <div class="s-foot">
                    <div class="sf"><small>Invoice date</small><b>15/10/2026</b></div>
                    <div class="sf sf-pay"><small>Payment method</small><span class="seg"><span>Cash</span><span class="t-upi">UPI</span><span>Card</span><span>Credit</span></span></div>
                    <div class="sf"><small>Total GST</small><b class="t-gst">₹0.00</b></div>
                    <div class="sf"><small>Net total</small><b class="t-total">₹0.00</b></div>
                    <span class="fb"><svg><use href="#i-file"/></svg>Save draft</span>
                    <span class="fb b"><svg><use href="#i-check"/></svg>Done</span>
                    <span class="fb g t-save"><svg><use href="#i-printer"/></svg>Save &amp; print</span>
                  </div>
                </section>

                <!-- 3 · Purchase: distributor CSV, margin compared with the last purchase -->
                <section class="sc" data-scene="buy">
                  <div class="s-bar">
                    <span>Search by <b>Invoice ▾</b></span>
                    <span class="s-sp"></span>
                    <span class="ab t-upload"><svg><use href="#i-upload"/></svg>Upload</span>
                    <span class="ab"><svg><use href="#i-calendar"/></svg>Monthly purchase</span>
                    <span class="ab g"><svg><use href="#i-plus"/></svg>Item master</span>
                    <span class="ab"><svg><use href="#i-file"/></svg>Drafts</span>
                    <span class="ab g"><svg><use href="#i-register"/></svg>PO</span>
                  </div>
                  <div class="p-row">
                    <span class="pfl"><small>Supplier *</small><b>Shree Krishna Pharma</b></span>
                    <span class="pfl"><small>Bill no.</small><b>SKP-4471</b></span>
                    <span class="p-md"><small>Margin diff on</small><span class="s-radio"><span class="on">Last purchase</span><span>Purchase avg</span></span></span>
                  </div>
                  <div class="s-main p-main">
                    <table class="g-tbl plines">
                      <thead><tr><th>Item name</th><th>Batch</th><th>Expiry</th><th class="n">MRP</th><th class="n">Qty</th><th class="n">Free</th><th class="n">PTR</th><th class="n">Margin %</th><th class="n">Diff %</th><th class="n">Amount</th></tr></thead>
                      <tbody>
                        <tr class="t-prow"><td><b>NUROKIND HS INJ</b></td><td>B3EEX003</td><td>02/30</td><td class="n">124.99</td><td class="n">10</td><td class="n">0</td><td class="n">89.28</td><td class="n">28.57</td><td class="n up">+3.57</td><td class="n"><b>892.80</b></td></tr>
                        <tr class="t-prow t-pwarn"><td><b>PAN 40MG TAB</b></td><td>25415</td><td>06/27</td><td class="n">192.00</td><td class="n">20</td><td class="n">2</td><td class="n">140.00</td><td class="n">27.08</td><td class="n dn">−1.72</td><td class="n"><b>2,800.00</b></td></tr>
                        <tr class="t-prow"><td><b>CANDIFORCE 100 CAP</b></td><td>G45Y007</td><td>03/27</td><td class="n">147.11</td><td class="n">10</td><td class="n">0</td><td class="n">105.08</td><td class="n">28.57</td><td class="n up">+3.57</td><td class="n"><b>1,050.80</b></td></tr>
                        <tr class="t-prow t-pwarn"><td><b>PAN D CAP</b></td><td>123</td><td>06/27</td><td class="n">238.12</td><td class="n">15</td><td class="n">0</td><td class="n">181.43</td><td class="n">23.81</td><td class="n dn">−1.79</td><td class="n"><b>2,721.45</b></td></tr>
                        <tr class="t-prow"><td><b>ZERODOL SP TABLET</b></td><td>420</td><td>12/27</td><td class="n">139.68</td><td class="n">30</td><td class="n">3</td><td class="n">106.43</td><td class="n">23.80</td><td class="n up">+3.81</td><td class="n"><b>3,192.90</b></td></tr>
                        <tr class="t-dropr"><td colspan="10">
                          <div class="t-drop"><svg><use href="#i-upload"/></svg><b class="t-droplabel">Drop the distributor’s CSV here</b><small>Columns matched once, remembered for next time</small><div class="t-prog"><i></i></div></div>
                        </td></tr>
                      </tbody>
                    </table>
                    <div class="t-psum"><b>5 / 5 lines read</b><span>batch · expiry · MRP · PTR · free qty</span><em>2 lines: margin below last purchase</em></div>
                  </div>
                  <div class="s-foot">
                    <div class="sf"><small>Invoice date</small><b>15/10/2026</b></div>
                    <div class="sf"><small>Payment method</small><b>Credit ▾</b></div>
                    <div class="sf"><small>Total gross</small><b class="t-pgross">₹0.00</b></div>
                    <div class="sf"><small>Amount due</small><b class="t-pdue">₹0.00</b></div>
                    <span class="fb"><svg><use href="#i-file"/></svg>Save draft</span>
                    <span class="fb b"><svg><use href="#i-arrow"/></svg>Save</span>
                    <span class="fb g"><svg><use href="#i-printer"/></svg>Save &amp; print</span>
                  </div>
                  <div class="t-file"><svg><use href="#i-file"/></svg><span><b>SKP-2026-4471.csv</b><small>5 rows · 2 KB</small></span></div>
                </section>

                <!-- 4 · Stock expiry report: item-wise, then supplier-wise for returns -->
                <section class="sc" data-scene="exp">
                  <div class="e-head">
                    <h3>Stock expiry report</h3>
                    <span class="e-by">Expiring by <b>31-10-2026 ▾</b></span>
                    <span class="s-sp"></span>
                    <span class="ab"><svg><use href="#i-search"/></svg>Filter data</span>
                    <span class="ab o"><svg><use href="#i-file"/></svg>Export</span>
                  </div>
                  <div class="e-tabs"><span class="t-tab-item on">Item wise</span><span class="t-tab-sup">Supplier wise</span></div>
                  <div class="e-wrap">
                    <table class="g-tbl e-tbl t-eitem">
                      <thead><tr><th class="sr">Sr</th><th>Product name</th><th class="n">Unit</th><th>Batch no</th><th class="n">Unit qty</th><th>Pack</th><th>Expiry</th><th>Supplier</th></tr></thead>
                      <tbody>
                        <tr class="t-erow"><td class="sr">1</td><td><b>AZITHRAL XL 200 SYP 200ML</b></td><td class="n">10</td><td>24NVHIK</td><td class="n">69</td><td>1×10</td><td class="ex">10/26</td><td>Om Sai Medical Agency</td></tr>
                        <tr class="t-erow"><td class="sr">2</td><td><b>ACILOC 150 TABLET 30S</b></td><td class="n">10</td><td>34233</td><td class="n">151</td><td>10</td><td class="ex">10/26</td><td>Mahavir Distributors</td></tr>
                        <tr class="t-erow"><td class="sr">3</td><td><b>MET XL AM 50/5 TAB</b></td><td class="n">15</td><td>GT21694</td><td class="n">124</td><td>1×15</td><td class="ex">10/26</td><td>Om Sai Medical Agency</td></tr>
                        <tr class="t-erow"><td class="sr">4</td><td><b>BISONEXT 5 TAB</b></td><td class="n">15</td><td>SGXL0290</td><td class="n">43</td><td>1×15</td><td class="ex">10/26</td><td>Shree Krishna Pharma</td></tr>
                        <tr class="t-erow"><td class="sr">5</td><td><b>THYROX 50 TAB</b></td><td class="n">100</td><td>16240684A</td><td class="n">100</td><td>1×100</td><td class="ex">10/26</td><td>Om Sai Medical Agency</td></tr>
                        <tr class="t-erow"><td class="sr">6</td><td><b>DEPRAN-L</b></td><td class="n">15</td><td>K2402358</td><td class="n">75</td><td>1×15</td><td class="ex">10/26</td><td>Mahavir Distributors</td></tr>
                      </tbody>
                    </table>
                    <table class="g-tbl e-tbl t-esup">
                      <thead><tr><th>Product name</th><th>Batch no</th><th class="n">Unit qty</th><th>Pack</th><th>Expiry</th><th class="n"></th></tr></thead>
                      <tbody>
                        <tr class="e-grp t-srow2"><td colspan="5"><b>Om Sai Medical Agency</b><small>3 batches · return before the rep’s next visit</small></td><td class="n"><span class="ab t-ret"><svg><use href="#i-return"/></svg>Purchase return</span></td></tr>
                        <tr class="t-srow2"><td><b>AZITHRAL XL 200 SYP 200ML</b></td><td>24NVHIK</td><td class="n">69</td><td>1×10</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>MET XL AM 50/5 TAB</b></td><td>GT21694</td><td class="n">124</td><td>1×15</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>THYROX 50 TAB</b></td><td>16240684A</td><td class="n">100</td><td>1×100</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="e-grp t-srow2"><td colspan="5"><b>Mahavir Distributors</b><small>2 batches</small></td><td class="n"><span class="ab o">Purchase return</span></td></tr>
                        <tr class="t-srow2"><td><b>ACILOC 150 TABLET 30S</b></td><td>34233</td><td class="n">151</td><td>10</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>DEPRAN-L</b></td><td>K2402358</td><td class="n">75</td><td>1×15</td><td class="ex">10/26</td><td></td></tr>
                      </tbody>
                    </table>
                  </div>
                  <p class="e-foot"><span>Page 1 of 5 · 20 batches a page</span><span>Short-dated stock goes back on record, with a credit note</span></p>
                </section>
              </div>
            </div>
            <div class="t-toast" data-toast></div>
          </div>

          <!-- Barcode scanner and printed receipt (sale scene) -->
          <div class="t-scanner"><div class="pack"><b>GLUCONORM VG 2 FORTE</b><small>Glimepiride + Voglibose + Metformin</small><?= barcode_svg('8901234567890', 44, 'pack-bc') ?><span>8 901234 567890</span></div><i class="beam"></i></div>
          <div class="t-receipt">
            <b>Shree Sai Medical Store</b><small>INV-26-04813 · 15 Oct 2026</small>
            <p><span>Gluconorm VG 2 Forte ×1</span><span>₹421.05</span></p>
            <p><span>Zerodol SP Tab ×2</span><span>₹279.36</span></p>
            <p class="r-tot"><span>Total (GST incl.)</span><span>₹700.41</span></p>
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
    <p class="sr-only" id="tour-desc">Animated product tour, about 40 seconds, no sound. On the dashboard, today’s
      sales, purchase and profit tiles fill in and a stock alert lists items below minimum. On the sale screen, a staff
      member scans Gluconorm VG 2 Forte, searches “zerodol” and the batch expiring first (12/27) is picked, sets the
      quantity to 2, adds patient Ramesh Patel, pays by UPI and saves: the bill prints and is shared on WhatsApp. On the
      purchase screen, a distributor’s CSV is dropped in, five lines are read with batch, expiry, PTR and free
      quantity, and two lines are flagged because their margin is below the last purchase. Finally the stock expiry
      report switches to supplier-wise and a purchase return is drafted. Product data is from a test account; names are
      sample names.</p>
    <span class="sample-tag tour-tag">Animated walkthrough · demo data</span>
  </div>
</section>