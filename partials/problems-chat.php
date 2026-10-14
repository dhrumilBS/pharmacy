<?php
/**
 * "Sound familiar?" as a message inbox. Left: six messages owners typically send (WAI-ARIA tabs,
 * handled by the generic tabs block in site.js). Right: the conversation, with the owner's message, a short
 * typing indicator, then the reply with what changes. Roles only, no invented names: this is an illustrative conversation, not testimonials.
 */
$pc_items = [
  ['i-alert',  'Expired stock',      'We found expired strips behind the new stock. Again.', 'Money already paid to the distributor, now dead stock, and we only find out during a shelf check.', 'Billing picks the batch that expires first, and the stock expiry report shows what’s expiring, item-wise or supplier-wise.', 'Store owner', '9:12'],
  ['i-users',  'Counter rush',       'Customers walk out while staff search for the medicine.', 'Evening rush, five people waiting, and the new staff member can’t find the item or the right batch.', 'Search by brand or generic name, the batch fills in by itself, and keyboard shortcuts save the bill without the mouse.', 'Pharmacist', '19:40'],
  ['i-truck',  'Distributor bills',  'Every night I sit typing distributor bills, line by line.', 'Batch, expiry, MRP, PTR, scheme, free quantity. Forty lines a bill, every bill.', 'Import the bill as a CSV file, or scan it with OCR import. Margin, PTR and GST are worked out when you save.', 'Store owner', '22:05'],
  ['i-box',    'Stock mismatch',     'The software says five boxes. The shelf has two.', 'Wrong entries, missed returns, or worse. Either way, I’ve stopped trusting the stock numbers.', 'Stock is tracked batch-wise, adjustments are recorded, and each user has their own login and role, so you can see who did what.', 'Chain operations head', '11:30'],
  ['i-rupee',  'Month-end GST',      'Month-end GST is a mess for me and my CA.', 'Wrong rates mean wrong invoices, and a weekend spent fixing entries.', 'HSN codes and GST rates are applied on every bill from the item master, and the GST report is ready for your accountant.', 'Store owner', '18:15'],
  ['i-mobile', 'Away from the shop', 'I can’t leave the shop without worrying.', 'Sales, cash, credit customers, distributor dues: it’s all in the shop, or in a notebook.', 'Get the daily report on WhatsApp, and log in from any browser to check collections, patient dues and supplier dues.', 'Store owner', '21:50'],
];
$reply_from = BRAND ?: 'Us';
?>
<section class="section bg-soft" id="problems" aria-labelledby="pc-title">
  <div class="container">
    <div class="pc-head">
      <div>
        <p class="eyebrow">Sound familiar?</p>
        <h2 id="pc-title">Six messages every pharmacy owner has typed. Here’s our reply.</h2>
      </div>
      <p>Pick a message to see what changes. <span class="pc-note">Illustrative conversation</span></p>
    </div>

    <div class="pc">
      <div class="pc-list" role="tablist" aria-label="Common pharmacy problems" aria-orientation="vertical">
        <?php foreach ($pc_items as $i => [$icon, $topic, $quote, , , $who, $time]): $n = $i + 1; ?>
          <button class="pc-tab" role="tab" id="pc-t<?= $n ?>" aria-controls="pc-p<?= $n ?>" aria-selected="<?= $n === 1 ? 'true' : 'false' ?>"<?= $n === 1 ? '' : ' tabindex="-1"' ?>>
            <span class="pc-av"><svg class="icon" aria-hidden="true"><use href="#<?= $icon ?>"/></svg></span>
            <span class="pc-meta"><b><?= e($topic) ?></b><small><?= e($quote) ?></small></span>
            <time><?= e($time) ?></time>
          </button>
        <?php endforeach; ?>
      </div>

      <div class="pc-thread">
        <?php foreach ($pc_items as $i => [$icon, $topic, $quote, $why, $fix, $who, $time]): $n = $i + 1; ?>
          <div class="pc-panel" role="tabpanel" id="pc-p<?= $n ?>" aria-labelledby="pc-t<?= $n ?>"<?= $n === 1 ? '' : ' hidden' ?>>
            <p class="pc-day"><span><?= e($topic) ?></span></p>
            <div class="bub in"><p>“<?= e($quote) ?>”</p><p class="bub-why"><?= e($why) ?></p><span class="bub-meta"><?= e($who) ?> · <?= e($time) ?></span></div>
            <div class="pc-typing" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="bub out"><span class="bub-label">What changes</span><p><?= e($fix) ?></p><span class="bub-meta"><?= e($reply_from) ?> · read</span></div>
            <a class="link-arrow pc-cta" href="#demo" data-cta="demo">See this on a demo call <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
