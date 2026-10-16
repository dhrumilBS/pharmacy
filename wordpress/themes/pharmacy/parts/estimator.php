<?php
/**
 * Expiry loss estimator (home #estimator, expiry feature page). Port of
 * partials/estimator.php. Styles: assets/css/estimator.css. Behaviour:
 * site.js (#purchase / #expiry and their outputs).
 *
 * Use once per page (fixed ids). The template prints the surrounding
 * <section id="estimator"> / container.
 *
 * $args (all optional):
 * - cta string Button target (today's $est_cta). Default '#demo'; feature
 *              pages pass rx_url( 'demo' ).
 *
 * The copy is a set of translatable strings: it is tool UI tied to the
 * script's ids. The starting figures match the sliders' starting values and
 * are replaced by the script.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_est_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_est_cta  = isset( $rx_est_args['cta'] ) && is_string( $rx_est_args['cta'] ) && '' !== $rx_est_args['cta'] ? $rx_est_args['cta'] : '#demo';
?>
<div class="est">
  <div class="est-in">
    <p class="eyebrow"><?php esc_html_e( 'Expiry loss estimator', 'pharmacy' ); ?></p>
    <h2 id="est-title"><?php esc_html_e( 'How much is expiry costing your store?', 'pharmacy' ); ?></h2>
    <p class="est-lead"><?php esc_html_e( 'Move the two sliders to match your store. Most owners have never put a number on this.', 'pharmacy' ); ?></p>
    <div class="field">
      <div class="field-top"><label for="purchase"><?php esc_html_e( 'Monthly purchase from distributors', 'pharmacy' ); ?></label><output
          id="purchase-out" for="purchase">₹8,00,000</output></div>
      <input type="range" id="purchase" min="100000" max="5000000" step="50000" value="800000">
      <small><?php esc_html_e( 'Roughly what you pay distributors in a month.', 'pharmacy' ); ?></small>
    </div>
    <div class="field">
      <div class="field-top"><label for="expiry"><?php esc_html_e( 'Stock that expires before it sells', 'pharmacy' ); ?></label><output
          id="expiry-out" for="expiry">1.0%</output></div>
      <input type="range" id="expiry" min="0.2" max="5" step="0.1" value="1">
      <small><?php esc_html_e( 'Your best guess. Include stock you couldn’t return in time.', 'pharmacy' ); ?></small>
    </div>
  </div>
  <div class="est-out" aria-live="polite">
    <span class="est-label"><?php esc_html_e( 'Estimated expiry loss in a year', 'pharmacy' ); ?></span>
    <span class="est-big" id="loss-year">₹96,000</span>
    <div class="est-months" aria-hidden="true">
      <i style="--m:1"></i><i style="--m:2"></i><i style="--m:3"></i><i style="--m:4"></i><i style="--m:5"></i><i style="--m:6"></i>
      <i style="--m:7"></i><i style="--m:8"></i><i style="--m:9"></i><i style="--m:10"></i><i style="--m:11"></i><i style="--m:12"></i>
    </div>
    <span class="est-axis" aria-hidden="true"><span><?php esc_html_e( 'Month 1', 'pharmacy' ); ?></span><span><?php esc_html_e( 'Month 12', 'pharmacy' ); ?></span></span>
    <dl class="est-split">
      <div><dt><?php esc_html_e( 'Every month', 'pharmacy' ); ?></dt><dd id="loss-month">₹8,000</dd></div>
      <div><dt><?php esc_html_e( 'Back if you catch half in time', 'pharmacy' ); ?></dt><dd id="loss-half">₹48,000</dd></div>
    </dl>
    <p class="est-note"><?php esc_html_e( 'An estimate from the numbers you enter, not a promise of savings. Your result depends on your stock, distributors and return terms.', 'pharmacy' ); ?></p>
    <a class="btn btn-primary" href="<?php echo esc_url( $rx_est_cta ); ?>" data-cta="demo"><?php esc_html_e( 'See the expiry report in a demo', 'pharmacy' ); ?></a>
  </div>
</div>
