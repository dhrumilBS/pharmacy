<?php
/**
 * Expiry loss estimator (home #estimator, expiry page). Styles: assets/css/estimator.css. Behaviour: site.js (#purchase / #expiry).
 * Optional: $est_cta = button href (default '#demo'). Use once per page (fixed ids).
 */
$est_cta = $est_cta ?? '#demo';
?>
<div class="est">
  <div class="est-in">
    <p class="eyebrow">Expiry loss estimator</p>
    <h2 id="est-title">How much is expiry costing your store?</h2>
    <p class="est-lead">Move the two sliders to match your store. Most owners have never put a number on this.</p>
    <div class="field">
      <div class="field-top"><label for="purchase">Monthly purchase from distributors</label><output
          id="purchase-out" for="purchase">₹8,00,000</output></div>
      <input type="range" id="purchase" min="100000" max="5000000" step="50000" value="800000">
      <small>Roughly what you pay distributors in a month.</small>
    </div>
    <div class="field">
      <div class="field-top"><label for="expiry">Stock that expires before it sells</label><output
          id="expiry-out" for="expiry">1.0%</output></div>
      <input type="range" id="expiry" min="0.2" max="5" step="0.1" value="1">
      <small>Your best guess. Include stock you couldn’t return in time.</small>
    </div>
  </div>
  <div class="est-out" aria-live="polite">
    <span class="est-label">Estimated expiry loss in a year</span>
    <span class="est-big" id="loss-year">₹96,000</span>
    <div class="est-months" aria-hidden="true">
      <i style="--m:1"></i><i style="--m:2"></i><i style="--m:3"></i><i style="--m:4"></i><i style="--m:5"></i><i style="--m:6"></i>
      <i style="--m:7"></i><i style="--m:8"></i><i style="--m:9"></i><i style="--m:10"></i><i style="--m:11"></i><i style="--m:12"></i>
    </div>
    <span class="est-axis" aria-hidden="true"><span>Month 1</span><span>Month 12</span></span>
    <dl class="est-split">
      <div><dt>Every month</dt><dd id="loss-month">₹8,000</dd></div>
      <div><dt>Back if you catch half in time</dt><dd id="loss-half">₹48,000</dd></div>
    </dl>
    <p class="est-note">An estimate from the numbers you enter, not a promise of savings. Your result depends on your
      stock, distributors and return terms.</p>
    <a class="btn btn-primary" href="<?= e($est_cta) ?>" data-cta="demo">See the expiry report in a demo</a>
  </div>
</div>
