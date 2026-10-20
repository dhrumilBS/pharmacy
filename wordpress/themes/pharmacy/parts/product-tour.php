<?php
/**
 * Product tour v2 ("video", home #tour): a scripted HTML animation in a
 * browser frame. Port of partials/product-tour.php; behaviour in
 * assets/js/tour.js, styles in assets/css/tour.css.
 *
 * The mock follows the real app (top menu, dashboard tiles, sale grid +
 * patient panel, purchase grid with free qty / PTR / margin diff, stock
 * expiry report) and is tied to tour.js, which types into it, moves the
 * cursor over it and restores it on replay. Its labels are translatable
 * strings; product names, batches, prices and the sample store, staff,
 * patient and supplier names are demo data and stay as they are. Only two
 * controls: play/pause and full screen. No audio.
 *
 * $args (all optional; missing or null uses today's text, '' hides the element):
 * - eyebrow    string Heading eyebrow (Home `tour_eyebrow`). Default "Product tour · 40 seconds".
 * - title_html string Heading HTML (Home `tour_title_html`), printed with rx_html().
 *                     Default: Watch one bill go from <span class="scribble-u">scan to print</span>.
 * - intro      string Paragraph under the heading (Home `tour_intro`).
 * - end_cta    string Target of the end card's "Book a free demo" button. Default '#top'
 *                     (the hero's demo slip, which [data-focus-lead] focuses).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- values are escaped by rx_text() / rx_html() / esc_*() where they are built; rx_barcode_svg() returns escaped markup.

$rx_tour_args    = isset( $args ) && is_array( $args ) ? $args : array();
$rx_tour_eyebrow = trim( rx_text( $rx_tour_args['eyebrow'] ?? __( 'Product tour · 40 seconds', 'pharmacy' ) ) );
$rx_tour_title   = trim( rx_html( $rx_tour_args['title_html'] ?? __( 'Watch one bill go from <span class="scribble-u">scan to print</span>', 'pharmacy' ) ) );
$rx_tour_intro   = trim( rx_text( $rx_tour_args['intro'] ?? __( 'Then a distributor bill comes in with its margins checked, and the expiry report lines up what to send back. The same screens your counter staff use.', 'pharmacy' ) ) );
$rx_tour_end     = isset( $rx_tour_args['end_cta'] ) && is_string( $rx_tour_args['end_cta'] ) && '' !== $rx_tour_args['end_cta'] ? $rx_tour_args['end_cta'] : '#top';
?>
<section class="section tour" id="tour"<?php echo '' !== $rx_tour_title ? ' aria-labelledby="tour-t"' : ''; ?>>
  <div class="container">
    <div class="tour-head">
      <?php if ( '' !== $rx_tour_eyebrow ) : ?><p class="eyebrow"><?php echo $rx_tour_eyebrow; ?></p><?php endif; ?>
      <?php if ( '' !== $rx_tour_title ) : ?><h2 id="tour-t"><?php echo $rx_tour_title; ?></h2><?php endif; ?>
      <?php if ( '' !== $rx_tour_intro ) : ?><p><?php echo $rx_tour_intro; ?></p><?php endif; ?>
    </div>

    <div class="tour-player" data-tour aria-describedby="tour-desc">
      <div class="tour-fit">
        <div class="tour-stage" aria-hidden="true">
          <div class="bw">
            <div class="ap">
              <header class="ap-top">
                <img class="brand-logo" src="<?php echo esc_url( get_theme_file_uri( 'assets/img/trusted/universal-pharmacy.webp' ) ); ?>" width="32" height="32" alt=""><?php /* White-label: the app bar shows the hospital's own logo */ ?>
                <nav class="ap-nav">
                  <span class="ap-n" data-nav="dash"><svg><use href="#i-chart"/></svg><?php esc_html_e( 'Dashboard', 'pharmacy' ); ?></span>
                  <span class="ap-n"><svg><use href="#i-users"/></svg><?php esc_html_e( 'Account Master', 'pharmacy' ); ?></span>
                  <span class="ap-n t-nav-sale" data-nav="bill"><svg><use href="#i-receipt"/></svg><?php esc_html_e( 'Sale', 'pharmacy' ); ?></span>
                  <span class="ap-n t-nav-buy" data-nav="buy"><svg><use href="#i-truck"/></svg><?php esc_html_e( 'Purchase', 'pharmacy' ); ?></span>
                  <span class="ap-n"><svg><use href="#i-box"/></svg><?php esc_html_e( 'Item Master', 'pharmacy' ); ?></span>
                  <span class="ap-n"><svg><use href="#i-upload"/></svg><?php esc_html_e( 'Initialization', 'pharmacy' ); ?></span>
                  <span class="ap-n t-nav-rep" data-nav="exp"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Reports', 'pharmacy' ); ?><i>▾</i>
                    <span class="t-rep-dd">
                      <span><?php esc_html_e( 'Stock Alert Report', 'pharmacy' ); ?></span>
                      <span class="t-rep-exp"><?php esc_html_e( 'Stock Expiry Report', 'pharmacy' ); ?></span>
                      <span><?php esc_html_e( 'Margin Report', 'pharmacy' ); ?></span>
                      <span><?php esc_html_e( 'GST Report', 'pharmacy' ); ?></span>
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
                    <span class="d-f"><small><?php esc_html_e( 'Select store', 'pharmacy' ); ?></small><b><?php esc_html_e( 'Main branch', 'pharmacy' ); ?> ▾</b></span>
                    <span class="d-f"><small><?php esc_html_e( 'Date', 'pharmacy' ); ?></small><b>15-10-2026 → 15-10-2026</b></span>
                    <span class="d-f"><small><?php esc_html_e( 'User', 'pharmacy' ); ?></small><b><?php esc_html_e( 'All', 'pharmacy' ); ?> ▾</b></span>
                    <span class="ab g"><svg><use href="#i-upload"/></svg><?php esc_html_e( 'Download', 'pharmacy' ); ?></span>
                  </div>
                  <div class="d-grid">
                    <div class="d-kpis">
                      <div class="kp"><small><?php esc_html_e( 'Sales', 'pharmacy' ); ?></small><b class="up t-k1">₹731.00</b><span><?php esc_html_e( '2 invoices', 'pharmacy' ); ?></span></div>
                      <div class="kp"><small><?php esc_html_e( 'Sales return', 'pharmacy' ); ?></small><b class="dn">₹0.00</b><span><?php esc_html_e( '0 invoices', 'pharmacy' ); ?></span></div>
                      <div class="kp"><small><?php esc_html_e( 'Purchase', 'pharmacy' ); ?></small><b class="dn t-k2">₹1,561.00</b><span><?php esc_html_e( '1 invoice', 'pharmacy' ); ?></span></div>
                      <div class="kp"><small><?php esc_html_e( 'Purchase return', 'pharmacy' ); ?></small><b class="up">₹0.00</b><span><?php esc_html_e( '0 invoices', 'pharmacy' ); ?></span></div>
                      <div class="kp"><small><?php esc_html_e( 'Profit', 'pharmacy' ); ?></small><b class="up t-k3">₹213.35</b><span><?php esc_html_e( 'Sales profit', 'pharmacy' ); ?></span></div>
                      <div class="kp"><small><?php esc_html_e( 'Expense', 'pharmacy' ); ?></small><b class="dn t-k4">₹1,561.00</b><span><?php esc_html_e( 'Purchase expense', 'pharmacy' ); ?></span></div>
                    </div>
                    <div class="d-card">
                      <div class="d-tabs"><span class="on"><?php esc_html_e( 'Overall collection', 'pharmacy' ); ?></span><span><?php esc_html_e( 'Deposit collection', 'pharmacy' ); ?></span></div>
                      <table class="d-tbl">
                        <thead><tr><th><?php esc_html_e( 'Method', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Sales', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Return', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Total', 'pharmacy' ); ?></th></tr></thead>
                        <tbody>
                          <tr><td><b><?php esc_html_e( 'Cash', 'pharmacy' ); ?></b></td><td class="n">₹731.00</td><td class="n">₹0.00</td><td class="n"><b>₹731.00</b></td></tr>
                          <tr><td><b><?php esc_html_e( 'Bank', 'pharmacy' ); ?></b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>UPI</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b><?php esc_html_e( 'Card', 'pharmacy' ); ?></b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Rokda</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b>Razorpay</b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr><td><b><?php esc_html_e( 'Outstanding', 'pharmacy' ); ?></b></td><td class="n">₹0.00</td><td class="n">₹0.00</td><td class="n">₹0.00</td></tr>
                          <tr class="d-tot"><td><b><?php esc_html_e( 'Total', 'pharmacy' ); ?></b></td><td class="n"><b>₹731.00</b></td><td class="n">₹0.00</td><td class="n"><b>₹731.00</b></td></tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-card">
                      <div class="d-tabs"><span><?php esc_html_e( 'Stock expiry', 'pharmacy' ); ?></span><span class="on"><?php esc_html_e( 'Stock alert', 'pharmacy' ); ?></span></div>
                      <table class="d-tbl">
                        <thead><tr><th><?php esc_html_e( 'Product', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Stock', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Min', 'pharmacy' ); ?></th></tr></thead>
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
                      <p class="d-note"><?php esc_html_e( '8 items below minimum · added to the short book', 'pharmacy' ); ?></p>
                    </div>
                  </div>
                </section>

                <!-- 2 · Sale -->
                <section class="sc" data-scene="bill">
                  <div class="s-bar">
                    <span><?php esc_html_e( 'System discount', 'pharmacy' ); ?> <span class="tgl"><?php esc_html_e( 'ON', 'pharmacy' ); ?></span></span>
                    <span><?php esc_html_e( 'Sales on', 'pharmacy' ); ?> <b>MRP ▾</b></span>
                    <span class="chip"><?php esc_html_e( 'FEFO on', 'pharmacy' ); ?></span>
                    <span class="s-sp"></span>
                    <span class="ab g"><svg><use href="#i-users"/></svg><?php esc_html_e( 'Patient list', 'pharmacy' ); ?></span>
                    <span class="ab"><svg><use href="#i-chart"/></svg><?php esc_html_e( 'Sales today', 'pharmacy' ); ?></span>
                    <span class="ab"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Drafts', 'pharmacy' ); ?></span>
                    <span class="ab r"><svg><use href="#i-x"/></svg><?php esc_html_e( 'Clear screen', 'pharmacy' ); ?></span>
                  </div>
                  <div class="s-body">
                    <div class="s-main">
                      <table class="g-tbl">
                        <thead><tr><th class="sr"><?php esc_html_e( 'Sr', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Item name', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Pack', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Batch', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Expiry', 'pharmacy' ); ?></th><th class="n">MRP</th><th class="n"><?php esc_html_e( 'Qty', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'GST %', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Margin %', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Amount', 'pharmacy' ); ?></th></tr></thead>
                        <tbody>
                          <tr class="t-row t-row1"><td class="sr">1</td><td><b>GLUCONORM VG 2 FORTE TAB</b></td><td>1×15</td><td>SGZL0125</td><td>04/28</td><td class="n">421.05</td><td class="n">1</td><td class="n">5</td><td class="n">23.81</td><td class="n"><b>421.05</b></td></tr>
                          <tr class="t-row t-row2"><td class="sr">2</td><td><b>ZERODOL SP TABLET</b></td><td>1×10</td><td>420</td><td>12/27</td><td class="n">139.68</td><td class="n"><span class="t-qty">1</span></td><td class="n">5</td><td class="n">23.80</td><td class="n"><b class="t-amt2">139.68</b></td></tr>
                          <tr class="t-srow"><td class="sr t-srn">1</td><td colspan="9">
                            <div class="t-search"><svg><use href="#i-search"/></svg><span class="t-typed" data-typed="search"></span><span class="caret"></span><span class="ph"><?php esc_html_e( 'Search items or scan a barcode', 'pharmacy' ); ?></span><kbd>F2</kbd>
                              <div class="t-dd">
                                <div class="dd-o dd-pick"><b>ZERODOL SP TABLET (10 TABLET)</b><small>Aceclofenac + Paracetamol + Serratiopeptidase · <?php esc_html_e( '2 batches', 'pharmacy' ); ?></small></div>
                              </div>
                              <div class="t-batches">
                                <small><?php esc_html_e( 'Batches · nearest expiry first', 'pharmacy' ); ?></small>
                                <div class="bt bt-pick"><b>420</b><span><?php esc_html_e( 'Exp', 'pharmacy' ); ?> 12/27</span><span><?php esc_html_e( '100 in stock', 'pharmacy' ); ?></span><em>✓ <?php esc_html_e( 'Sell first', 'pharmacy' ); ?></em></div>
                                <div class="bt"><b>ZS2611</b><span><?php esc_html_e( 'Exp', 'pharmacy' ); ?> 09/28</span><span><?php esc_html_e( '60 in stock', 'pharmacy' ); ?></span><em><?php esc_html_e( 'Later', 'pharmacy' ); ?></em></div>
                              </div>
                            </div>
                          </td></tr>
                        </tbody>
                      </table>
                      <p class="s-info"><span><?php esc_html_e( 'Generic', 'pharmacy' ); ?> <b class="t-gen">—</b></span><span><?php esc_html_e( 'Category', 'pharmacy' ); ?> <b><?php esc_html_e( 'Tablet', 'pharmacy' ); ?></b></span><span><?php esc_html_e( 'Location', 'pharmacy' ); ?> <b>Rack B2</b></span></p>
                    </div>
                    <aside class="s-pat">
                      <p class="s-radio"><span class="lbl"><?php esc_html_e( 'Search type', 'pharmacy' ); ?></span><span class="on">OPD</span><span>IPD</span></p>
                      <label><?php esc_html_e( 'Patient name', 'pharmacy' ); ?></label>
                      <div class="pf t-patient"><span class="t-typed" data-typed="patient"></span><span class="caret"></span><span class="ph"><?php esc_html_e( 'Search patient or UHID', 'pharmacy' ); ?></span></div>
                      <div class="t-pchip">Ramesh Patel · HR-104582 · <?php esc_html_e( 'no dues', 'pharmacy' ); ?></div>
                      <label><?php esc_html_e( 'Contact no.', 'pharmacy' ); ?></label>
                      <div class="pf"><span class="t-phone ph">+91 <?php esc_html_e( 'Contact no.', 'pharmacy' ); ?></span></div>
                      <label><?php esc_html_e( 'Doctor name', 'pharmacy' ); ?></label>
                      <div class="pf"><span class="t-doc ph"><?php esc_html_e( 'Search doctor', 'pharmacy' ); ?></span></div>
                      <label><?php esc_html_e( 'GST no.', 'pharmacy' ); ?></label>
                      <div class="pf"><span class="ph"><?php esc_html_e( 'GST no.', 'pharmacy' ); ?></span></div>
                    </aside>
                  </div>
                  <div class="s-foot">
                    <div class="sf"><small><?php esc_html_e( 'Invoice date', 'pharmacy' ); ?></small><b>15/10/2026</b></div>
                    <div class="sf sf-pay"><small><?php esc_html_e( 'Payment method', 'pharmacy' ); ?></small><span class="seg"><span><?php esc_html_e( 'Cash', 'pharmacy' ); ?></span><span class="t-upi">UPI</span><span><?php esc_html_e( 'Card', 'pharmacy' ); ?></span><span><?php esc_html_e( 'Credit', 'pharmacy' ); ?></span></span></div>
                    <div class="sf"><small><?php esc_html_e( 'Total GST', 'pharmacy' ); ?></small><b class="t-gst">₹0.00</b></div>
                    <div class="sf"><small><?php esc_html_e( 'Net total', 'pharmacy' ); ?></small><b class="t-total">₹0.00</b></div>
                    <span class="fb"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Save draft', 'pharmacy' ); ?></span>
                    <span class="fb b"><svg><use href="#i-check"/></svg><?php esc_html_e( 'Done', 'pharmacy' ); ?></span>
                    <span class="fb g t-save"><svg><use href="#i-printer"/></svg><?php esc_html_e( 'Save & print', 'pharmacy' ); ?></span>
                  </div>
                </section>

                <!-- 3 · Purchase: distributor CSV, margin compared with the last purchase -->
                <section class="sc" data-scene="buy">
                  <div class="s-bar">
                    <span><?php esc_html_e( 'Search by', 'pharmacy' ); ?> <b><?php esc_html_e( 'Invoice', 'pharmacy' ); ?> ▾</b></span>
                    <span class="s-sp"></span>
                    <span class="ab t-upload"><svg><use href="#i-upload"/></svg><?php esc_html_e( 'Upload', 'pharmacy' ); ?></span>
                    <span class="ab"><svg><use href="#i-calendar"/></svg><?php esc_html_e( 'Monthly purchase', 'pharmacy' ); ?></span>
                    <span class="ab g"><svg><use href="#i-plus"/></svg><?php esc_html_e( 'Item master', 'pharmacy' ); ?></span>
                    <span class="ab"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Drafts', 'pharmacy' ); ?></span>
                    <span class="ab g"><svg><use href="#i-register"/></svg>PO</span>
                  </div>
                  <div class="p-row">
                    <span class="pfl"><small><?php esc_html_e( 'Supplier *', 'pharmacy' ); ?></small><b>Shree Krishna Pharma</b></span>
                    <span class="pfl"><small><?php esc_html_e( 'Bill no.', 'pharmacy' ); ?></small><b>SKP-4471</b></span>
                    <span class="p-md"><small><?php esc_html_e( 'Margin diff on', 'pharmacy' ); ?></small><span class="s-radio"><span class="on"><?php esc_html_e( 'Last purchase', 'pharmacy' ); ?></span><span><?php esc_html_e( 'Purchase avg', 'pharmacy' ); ?></span></span></span>
                  </div>
                  <div class="s-main p-main">
                    <table class="g-tbl plines">
                      <thead><tr><th><?php esc_html_e( 'Item name', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Batch', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Expiry', 'pharmacy' ); ?></th><th class="n">MRP</th><th class="n"><?php esc_html_e( 'Qty', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Free', 'pharmacy' ); ?></th><th class="n">PTR</th><th class="n"><?php esc_html_e( 'Margin %', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Diff %', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Amount', 'pharmacy' ); ?></th></tr></thead>
                      <tbody>
                        <tr class="t-prow"><td><b>NUROKIND HS INJ</b></td><td>B3EEX003</td><td>02/30</td><td class="n">124.99</td><td class="n">10</td><td class="n">0</td><td class="n">89.28</td><td class="n">28.57</td><td class="n up">+3.57</td><td class="n"><b>892.80</b></td></tr>
                        <tr class="t-prow t-pwarn"><td><b>PAN 40MG TAB</b></td><td>25415</td><td>06/27</td><td class="n">192.00</td><td class="n">20</td><td class="n">2</td><td class="n">140.00</td><td class="n">27.08</td><td class="n dn">−1.72</td><td class="n"><b>2,800.00</b></td></tr>
                        <tr class="t-prow"><td><b>CANDIFORCE 100 CAP</b></td><td>G45Y007</td><td>03/27</td><td class="n">147.11</td><td class="n">10</td><td class="n">0</td><td class="n">105.08</td><td class="n">28.57</td><td class="n up">+3.57</td><td class="n"><b>1,050.80</b></td></tr>
                        <tr class="t-prow t-pwarn"><td><b>PAN D CAP</b></td><td>123</td><td>06/27</td><td class="n">238.12</td><td class="n">15</td><td class="n">0</td><td class="n">181.43</td><td class="n">23.81</td><td class="n dn">−1.79</td><td class="n"><b>2,721.45</b></td></tr>
                        <tr class="t-prow"><td><b>ZERODOL SP TABLET</b></td><td>420</td><td>12/27</td><td class="n">139.68</td><td class="n">30</td><td class="n">3</td><td class="n">106.43</td><td class="n">23.80</td><td class="n up">+3.81</td><td class="n"><b>3,192.90</b></td></tr>
                        <tr class="t-dropr"><td colspan="10">
                          <div class="t-drop"><svg><use href="#i-upload"/></svg><b class="t-droplabel"><?php esc_html_e( 'Drop the distributor’s CSV here', 'pharmacy' ); ?></b><small><?php esc_html_e( 'Columns matched once, remembered for next time', 'pharmacy' ); ?></small><div class="t-prog"><i></i></div></div>
                        </td></tr>
                      </tbody>
                    </table>
                    <div class="t-psum"><b><?php esc_html_e( '5 / 5 lines read', 'pharmacy' ); ?></b><span><?php esc_html_e( 'batch · expiry · MRP · PTR · free qty', 'pharmacy' ); ?></span><em><?php esc_html_e( '2 lines: margin below last purchase', 'pharmacy' ); ?></em></div>
                  </div>
                  <div class="s-foot">
                    <div class="sf"><small><?php esc_html_e( 'Invoice date', 'pharmacy' ); ?></small><b>15/10/2026</b></div>
                    <div class="sf"><small><?php esc_html_e( 'Payment method', 'pharmacy' ); ?></small><b><?php esc_html_e( 'Credit', 'pharmacy' ); ?> ▾</b></div>
                    <div class="sf"><small><?php esc_html_e( 'Total gross', 'pharmacy' ); ?></small><b class="t-pgross">₹0.00</b></div>
                    <div class="sf"><small><?php esc_html_e( 'Amount due', 'pharmacy' ); ?></small><b class="t-pdue">₹0.00</b></div>
                    <span class="fb"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Save draft', 'pharmacy' ); ?></span>
                    <span class="fb b"><svg><use href="#i-arrow"/></svg><?php esc_html_e( 'Save', 'pharmacy' ); ?></span>
                    <span class="fb g"><svg><use href="#i-printer"/></svg><?php esc_html_e( 'Save & print', 'pharmacy' ); ?></span>
                  </div>
                  <div class="t-file"><svg><use href="#i-file"/></svg><span><b>SKP-2026-4471.csv</b><small><?php esc_html_e( '5 rows · 2 KB', 'pharmacy' ); ?></small></span></div>
                </section>

                <!-- 4 · Stock expiry report: item-wise, then supplier-wise for returns -->
                <section class="sc" data-scene="exp">
                  <div class="e-head">
                    <h3><?php esc_html_e( 'Stock expiry report', 'pharmacy' ); ?></h3>
                    <span class="e-by"><?php esc_html_e( 'Expiring by', 'pharmacy' ); ?> <b>31-10-2026 ▾</b></span>
                    <span class="s-sp"></span>
                    <span class="ab"><svg><use href="#i-search"/></svg><?php esc_html_e( 'Filter data', 'pharmacy' ); ?></span>
                    <span class="ab o"><svg><use href="#i-file"/></svg><?php esc_html_e( 'Export', 'pharmacy' ); ?></span>
                  </div>
                  <div class="e-tabs"><span class="t-tab-item on"><?php esc_html_e( 'Item wise', 'pharmacy' ); ?></span><span class="t-tab-sup"><?php esc_html_e( 'Supplier wise', 'pharmacy' ); ?></span></div>
                  <div class="e-wrap">
                    <table class="g-tbl e-tbl t-eitem">
                      <thead><tr><th class="sr"><?php esc_html_e( 'Sr', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Product name', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Unit', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Batch no', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Unit qty', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Pack', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Expiry', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Supplier', 'pharmacy' ); ?></th></tr></thead>
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
                      <thead><tr><th><?php esc_html_e( 'Product name', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Batch no', 'pharmacy' ); ?></th><th class="n"><?php esc_html_e( 'Unit qty', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Pack', 'pharmacy' ); ?></th><th><?php esc_html_e( 'Expiry', 'pharmacy' ); ?></th><th class="n"></th></tr></thead>
                      <tbody>
                        <tr class="e-grp t-srow2"><td colspan="5"><b>Om Sai Medical Agency</b><small><?php esc_html_e( '3 batches · return before the rep’s next visit', 'pharmacy' ); ?></small></td><td class="n"><span class="ab t-ret"><svg><use href="#i-return"/></svg><?php esc_html_e( 'Purchase return', 'pharmacy' ); ?></span></td></tr>
                        <tr class="t-srow2"><td><b>AZITHRAL XL 200 SYP 200ML</b></td><td>24NVHIK</td><td class="n">69</td><td>1×10</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>MET XL AM 50/5 TAB</b></td><td>GT21694</td><td class="n">124</td><td>1×15</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>THYROX 50 TAB</b></td><td>16240684A</td><td class="n">100</td><td>1×100</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="e-grp t-srow2"><td colspan="5"><b>Mahavir Distributors</b><small><?php esc_html_e( '2 batches', 'pharmacy' ); ?></small></td><td class="n"><span class="ab o"><?php esc_html_e( 'Purchase return', 'pharmacy' ); ?></span></td></tr>
                        <tr class="t-srow2"><td><b>ACILOC 150 TABLET 30S</b></td><td>34233</td><td class="n">151</td><td>10</td><td class="ex">10/26</td><td></td></tr>
                        <tr class="t-srow2"><td><b>DEPRAN-L</b></td><td>K2402358</td><td class="n">75</td><td>1×15</td><td class="ex">10/26</td><td></td></tr>
                      </tbody>
                    </table>
                  </div>
                  <p class="e-foot"><span><?php esc_html_e( 'Page 1 of 5 · 20 batches a page', 'pharmacy' ); ?></span><span><?php esc_html_e( 'Short-dated stock goes back on record, with a credit note', 'pharmacy' ); ?></span></p>
                </section>
              </div>
            </div>
            <div class="t-toast" data-toast></div>
          </div>

          <!-- Barcode scanner and printed receipt (sale scene) -->
          <div class="t-scanner"><div class="pack"><b>GLUCONORM VG 2 FORTE</b><small>Glimepiride + Voglibose + Metformin</small><?php echo rx_barcode_svg( '8901234567890', 44, 'pack-bc' ); ?><span>8 901234 567890</span></div><i class="beam"></i></div>
          <div class="t-receipt">
            <b>Shree Sai Medical Store</b><small>INV-26-04813 · 15 Oct 2026</small>
            <p><span>Gluconorm VG 2 Forte ×1</span><span>₹421.05</span></p>
            <p><span>Zerodol SP Tab ×2</span><span>₹279.36</span></p>
            <p class="r-tot"><span><?php esc_html_e( 'Total (GST incl.)', 'pharmacy' ); ?></span><span>₹700.41</span></p>
            <?php echo rx_barcode_svg( 'INV-26-04813', 26, 'r-bc' ); ?>
          </div>

          <!-- Cursor: shadow drawn inside the SVG (no CSS filter, which left ghost trails while moving) -->
          <svg class="t-cursor" viewBox="0 0 24 24"><g class="t-arrow"><path class="t-sh" d="M4 2l15 11-6.5 1.2 3.8 7.3-3 1.5-3.8-7.4L4 20z" transform="translate(.7 1.3)"/><path d="M4 2l15 11-6.5 1.2 3.8 7.3-3 1.5-3.8-7.4L4 20z"/></g></svg>
          <span class="t-ripple"></span>
        </div>

        <!-- End card sits outside the aria-hidden stage so its link is reachable -->
        <div class="tour-end" hidden>
          <p class="end-k"><?php esc_html_e( 'That’s the counter, sorted.', 'pharmacy' ); ?></p>
          <p class="end-s"><?php esc_html_e( 'Want to see it with medicines from your own shelf?', 'pharmacy' ); ?></p>
          <a class="btn btn-primary" href="<?php echo esc_url( $rx_tour_end ); ?>" data-cta="demo" data-focus-lead><?php esc_html_e( 'Book a free demo', 'pharmacy' ); ?> <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
      </div>

      <div class="tour-ui">
        <button class="tour-btn" type="button" data-tour-play aria-label="<?php esc_attr_e( 'Play product tour', 'pharmacy' ); ?>">
          <svg class="i-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15l13-7.5z"/></svg>
          <svg class="i-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>
        </button>
        <span class="tour-progress" aria-hidden="true"><i></i></span>
        <button class="tour-btn" type="button" data-tour-fs aria-label="<?php esc_attr_e( 'Full screen', 'pharmacy' ); ?>">
          <svg class="i-fs" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/></svg>
          <svg class="i-fs-x" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4v5H4M20 9h-5V4M15 20v-5h5M4 15h5v5"/></svg>
        </button>
      </div>
    </div>
    <p class="sr-only" id="tour-desc"><?php esc_html_e( 'Animated product tour, about 40 seconds, no sound. On the dashboard, today’s sales, purchase and profit tiles fill in and a stock alert lists items below minimum. On the sale screen, a staff member scans Gluconorm VG 2 Forte, searches “zerodol” and the batch expiring first (12/27) is picked, sets the quantity to 2, adds patient Ramesh Patel, pays by UPI and saves: the bill prints and is shared on WhatsApp. On the purchase screen, a distributor’s CSV is dropped in, five lines are read with batch, expiry, PTR and free quantity, and two lines are flagged because their margin is below the last purchase. Finally the stock expiry report switches to supplier-wise and a purchase return is drafted. Product data is from a test account; names are sample names.', 'pharmacy' ); ?></p>
  </div>
</section>
<?php
// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
