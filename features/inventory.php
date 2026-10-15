<?php
/* Feature page: Stock & reorder. Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (expiry and stock). Plan badges come live from the pricing API. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy inventory management software: stock alerts, short book and reorder');
$description = 'Medical store stock management: min and max stock per item, stock alerts and a short book, purchase orders from the short book, non-moving items, recorded stock adjustments and barcode labels.';
$page_key    = 'inventory';

/* Worked example (illustrative): one fast mover out of stock */
$so = ['strips' => 6, 'price' => 120, 'days' => 3];
$so_total = $so['strips'] * $so['price'] * $so['days'];
$po = feature_plan_label('feat_purchase_order');

$fp = [
  'hero' => ['crumb' => 'Stock & reorder', 'parent' => 'Features', 'eyebrow' => 'Stock & reorder',
    'h1' => 'Know what’s on the shelf, what’s running out and what isn’t moving',
    'lead' => 'Set a minimum and maximum for each item. The stock alert and short book fill in as you bill, slow stock shows up in its own report, and every correction is recorded, so the number on screen is one you can trust.'],
  'ctas'   => ['demo' => 'See the short book in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['Min and max stock per item', 'Short book that builds as you bill', $po ? 'Purchase orders from the short book (' . $po . ')' : null])),
  'screen' => ['v2-05-reorder', 'Stock alert and short book grouped by supplier'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Stock problems cost you twice',
    'intro' => 'Too little and you lose the sale. Too much and your money sits on the shelf.',
    'items' => [
      ['Sunday morning', 'Out of a fast mover', 'A regular asks for their monthly medicine. You sold the last strip yesterday and nobody noted it.', 'the sale today, and maybe the regular customer.'],
      ['Stock check', 'Screen says 5, shelf says 2', 'Nobody knows if it was a wrong entry, a return that wasn’t recorded, or something worse.', 'money you can’t account for, and doubt about your staff.'],
      ['Month-end', 'Cash stuck in slow stock', 'Shelves are full, but the bank balance isn’t. Half the stock hasn’t moved in months.', 'working capital you could have used for fast movers.'],
      ['Order time', 'Ordering from memory', 'You call the distributor at night and try to remember what’s low. Two items get missed, one gets ordered twice.', 'stock-outs next week and extra stock you didn’t need.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where stock money leaks, and what plugs it',
    'intro' => 'Each leak, then what plugs it and the plan that includes it.',
    'rows' => [
      ['Running out of fast movers', 'Nobody notices stock falling until the shelf is empty.', '<b>Minimum stock per item</b> with a <b>stock alert</b>, so low items show up before they run out.', null],
      ['Forgotten items on the order', 'The order list lives in someone’s head.', '<b>Short book</b> of items below minimum, ready to order from.', null],
      ['Ordering by phone and paper', 'Orders are retyped and items get missed.', '<b>Reorder processing</b> turns the short book into <b>purchase orders</b>.', 'feat_reorder_processing'],
      ['Money stuck in slow stock', 'Items that don’t sell keep getting reordered.', '<b>Non-moving items report</b> and <b>top-selling items report</b>, so you buy more of what sells.', 'feat_non_moving_item'],
      ['Overstock of slow items', 'Orders go beyond what the shelf can sell.', '<b>Maximum stock per item</b>, so you know when there is already enough.', null],
      ['Stock gaps nobody explains', 'Corrections are made by changing the number.', '<b>Stock adjustment</b> records every correction, and the <b>activity report</b> shows who did what.', 'feat_stock_adjustment'],
      ['Wrong item picked by hand', 'Look-alike packs are picked by eye.', '<b>Barcode label printing</b>, so stock scans at the counter.', 'feat_label_print'],
    ],
    'example' => ['title' => 'One fast mover out of stock',
      'lines' => [['Strips sold on a normal day', (string) $so['strips']], ['Price per strip', inr($so['price'])], ['Days until the next delivery', (string) $so['days']]],
      'result' => ['Sales missed on one item', inr($so_total)],
      'note' => 'Change the numbers to match your store, then multiply by the items you run out of in a month.'],
  ],

  'steps' => ['eyebrow' => 'How reordering works', 'title' => 'From empty shelf to purchase order',
    'intro' => 'Set the rules once. After that the short book keeps itself up to date.',
    'items' => [
      ['Set min and max per item', 'Start with your top sellers. The minimum is when to reorder; the maximum is how much is enough.', 'Once per item'],
      ['Bill as usual', 'Every sale reduces stock by batch. Items that drop below minimum appear in the stock alert.', 'Live with every bill'],
      ['Order from the short book', 'Open the short book grouped by supplier.' . ($po ? ' On ' . e($po) . ', reorder processing turns it into purchase orders.' : ''), 'Grouped by supplier'],
      ['Count and adjust', 'Do a quick physical count of fast movers and record any difference as a stock adjustment.', 'Every change on record'],
    ]],

  'caps' => ['eyebrow' => 'Stock tools', 'title' => 'Numbers you can act on',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-box', 'Min and max stock', 'Set reorder and maximum levels for each item.', null],
      ['i-alert', 'Stock alert and short book', 'Items below minimum, ready to order, grouped by supplier.', null],
      ['i-truck', 'Reorder processing', 'Turn the short book into an order without retyping it.', 'feat_reorder_processing'],
      ['i-file', 'Purchase orders', 'Make a proper purchase order for the distributor instead of a late-night phone list.', 'feat_purchase_order'],
      ['i-chart', 'Non-moving and top-selling items', 'See what’s blocking your money and what deserves more shelf space.', 'feat_non_moving_item'],
      ['i-register', 'Stock adjustment', 'Record breakage, write-offs and count differences, so stock stays true.', 'feat_stock_adjustment'],
      ['i-scan', 'Barcode labels', 'Print barcode labels for stock that has none.', 'feat_label_print'],
      ['i-shield', 'Activity report', 'See who changed what, so stock differences can be traced.', 'feat_activity_report'],
      ['i-stores', 'Transfers between stores', 'Move stock between linked outlets and see central reports across stores.', 'feat_central_admin'],
    ]],

  'guide' => ['eyebrow' => 'A simple rule', 'title' => 'How to set min and max without overthinking it',
    'intro' => 'A rule of thumb used by many stores. Start here, then adjust as you learn each item.',
    'items' => [
      ['Start with your top 100 items', 'They’re most of your sales. Set levels for them first; the long tail can wait.'],
      ['Minimum: sales until the next delivery, plus a little', 'If you sell 6 strips a day and the distributor delivers in 2 days, a minimum of about 15 strips gives a small cushion.'],
      ['Maximum: minimum plus one order’s worth', 'Add what you’d normally order in one go. Going above that just ties up money.'],
      ['Keep slow items lean', 'For items that sell a few strips a month, keep a low maximum and check the non-moving items report before reordering.'],
      ['Review after a month', 'Look at what ran out and what piled up, and adjust those items’ levels.'],
    ],
    'note' => 'Illustrative numbers. Your delivery days and sales decide the right levels.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring this list to a 30-minute demo and see stock behave the way your shelf does.',
    'items' => [
      'Set a minimum on an item and watch it appear in the stock alert',
      'Open the short book grouped by supplier',
      'Turn the short book into a purchase order' . ($po ? ' (' . $po . ')' : ''),
      'Open the non-moving items report',
      'Record a stock adjustment and find it in the activity report',
      'Print a barcode label for an item',
    ]],

  'related' => ['billing', 'expiry', 'purchase'],

  'faq' => [
    ['How does reordering work?', 'Set minimum and maximum stock per item. Items below minimum appear in the stock alert and short book.' . ($po ? ' On ' . e($po) . ' you can turn the short book into purchase orders.' : ''), true],
    ['Can I see which items aren’t selling?', 'Yes. The non-moving items report shows stock that isn’t selling, and the top-selling items report shows what is.', true],
    ['How do I fix stock that doesn’t match the shelf?', 'Record a stock adjustment. It keeps the correction on record instead of overwriting the number.', true],
    ['Does stock update as soon as I bill?', 'Yes. Each sale reduces stock by batch, and each saved purchase bill adds stock straight away.', true],
    ['Can I move stock between my stores?', 'Yes, on Enterprise: central administration with linked outlets, stock transfers between stores, and central reports.', true],
    ['Can I print barcode labels?', 'Yes. Print barcode labels for stock that doesn’t have one.' . (($lp = feature_plan_label('feat_label_print')) ? ' Label printing is on ' . e($lp) . '.' : ''), true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
