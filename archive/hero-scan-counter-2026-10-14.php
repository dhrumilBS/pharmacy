<?php
/**
 * ARCHIVED 14 Oct 2026: the "live counter" card from the right column of the barcode-scan hero
 * (partials/variants/hero-scan.php). Replaced by the demo slip on the right. Reference only, not included anywhere.
 * Its cycling behaviour lived in assets/js/variants.js ("Element 1": items[], show(), the Pause button) and
 * its styles in assets/css/variants.css (.v-counter, .v-c-top, .v-scanfield, .v-bt…), which are still there.
 */
?>
    <div class="v-counter" role="group" aria-label="Sample: counter screen picking the nearest-expiry batch">
      <div class="v-c-top">
        <b><span class="v-live" aria-hidden="true"></span>Counter 1 · live</b>
        <span class="v-c-tools">
          <button type="button" class="v-pause" aria-pressed="false">Pause</button>
          <span class="sample-tag">Sample data</span>
        </span>
      </div>
      <div class="v-scanfield"><span class="v-bc" aria-hidden="true"></span><span class="v-item">SHELCAL 500 TAB</span><kbd>F2</kbd></div>
      <p class="v-gen">Calcium 500 mg + Vitamin D3 · strip of 15</p>
      <div class="v-batches"></div>
      <div class="v-c-foot"><span>Bill <b>INV-26-04812</b></span><span>Total <b class="v-tot">₹657.72</b></span></div>
    </div>
