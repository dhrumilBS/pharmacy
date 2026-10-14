<?php
/**
 * Hero lead capture: a 2-field "demo slip" styled like a counter bill (perforation + barcode).
 * Set $lead_id (unique per page, e.g. 'hl1') before including; it prefixes every id.
 * Behaviour in assets/js/site.js ([data-lead-form]): validates, shows the inline thank-you, and copies
 * name + mobile into the full #demo form. Local-only: nothing is sent until the endpoint is agreed.
 */
$lead_id = $lead_id ?? 'hl';
$slip_code = strtoupper(substr(BRAND ?: 'RX', 0, 3)) . ' · DEMO · 0001';   // follows BRAND, no hard-coded name
?>
<form class="slip" data-lead-form novalidate aria-labelledby="<?= $lead_id ?>-t">
  <div class="slip-body">
    <div class="slip-top">
      <h2 class="slip-title" id="<?= $lead_id ?>-t">Get a free demo call</h2>
      <span class="slip-no" aria-hidden="true">DEMO&nbsp;SLIP · No.&nbsp;0001</span>
    </div>
    <div class="slip-fields">
      <div class="fld"><label for="<?= $lead_id ?>-name">Your name</label>
        <input id="<?= $lead_id ?>-name" name="name" autocomplete="name" required>
        <span class="err">Please enter your name.</span></div>
      <div class="fld"><label for="<?= $lead_id ?>-phone">Mobile number</label>
        <span class="slip-tel"><span aria-hidden="true">+91</span><input id="<?= $lead_id ?>-phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" placeholder="98xxxxxx21" required pattern="[6-9][0-9]{9}"></span>
        <span class="err">Please enter a 10-digit mobile number.</span></div>
    </div>
    <button class="btn btn-primary slip-btn" type="submit">Call me for a free demo <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></button>
    <ul class="slip-terms">
      <li>30-minute call</li><li>Shown with your own medicines</li><li>No payment, no obligation</li>
    </ul>
  </div>
  <div class="slip-done" hidden tabindex="-1">
    <svg class="icon" aria-hidden="true"><use href="#i-check-c"/></svg>
    <div><b>Done<span data-lead-name></span>, we’ll call you.</b>
      <span data-confirm>Usually within one working day.</span> Want a better demo? <a href="#demo">Add your store details</a>.
      <small>Prototype: nothing was sent.</small></div>
  </div>
  <div class="slip-foot" aria-hidden="true">
    <?= barcode_svg($slip_code, 44, 'slip-bc') ?>
    <span><?= e($slip_code) ?></span>
  </div>
</form>
