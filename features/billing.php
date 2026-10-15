<?php
/* Feature page: Billing & counter. Data only; layout in partials/feature-page.php, styles in assets/css/feature.css.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (sale screen). Plan badges come live from the pricing API ('feat_*'). */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Medical store billing software: fast counter billing with FEFO batches');
$description = 'Pharmacy billing software for the counter rush: search by brand or generic name, nearest-expiry batch picked automatically, cash, card, UPI or credit, and bills on WhatsApp. Book a free demo or start free.';
$page_key    = 'billing';

$free   = pricing_split()['free'][0] ?? null;
$limits = $free ? plan_bill_limits((int) $free['id']) : ['sales' => null, 'purchase' => null];

/* Worked example (illustrative): sales lost to rush-hour walk-outs */
$wo = ['per_day' => 2, 'bill' => 350, 'days' => 30];
$wo_month = $wo['per_day'] * $wo['bill'] * $wo['days'];

$fp = [
  'hero' => ['crumb' => 'Billing & counter', 'parent' => 'Features', 'eyebrow' => 'Billing & counter',
    'h1' => 'Bill the evening rush without losing the queue',
    'lead' => 'Search by brand or generic name, let the nearest-expiry batch fill in by itself, take cash, card, UPI or credit, then print or share the bill. A counter screen built for medicine, not a general shop.'],
  'ctas'   => ['demo' => 'See the counter in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter([
    'Batch picked by nearest expiry (FEFO)',
    'F2 to search, Ctrl+S to save',
    $free && $limits['sales'] !== null ? $free['name'] . ' plan: ' . $limits['sales'] . ' sales bills a month, free' : null,
  ])),
  'screen' => ['v2-01-counter-billing', 'Counter billing screen with batch picker and bill summary'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Where the counter loses money',
    'intro' => 'Nobody notices these on the day. They add up over a month.',
    'items' => [
      ['7:45 pm, five people waiting', 'The customer asks for the brand', 'You stock it under its generic name. A new staff member scrolls the list while the queue grows, and two people walk out to the shop next door.', 'sales that went to someone else, and a regular who may not come back.'],
      ['Saturday rush', 'The front box goes out first', 'Staff grab the strip that’s easiest to reach, not the batch that expires first. The older batch slides to the back of the shelf.', 'stock you already paid for, written off months later.'],
      ['End of the month', 'Udhaar in a notebook', '“Write it down, I’ll pay on the 1st.” The entry has a name and an amount, but no bill number and no items. By month-end nobody is sure what was taken.', 'credit you can’t chase with confidence.'],
      ['10 pm, closing', 'The drawer doesn’t add up', 'Cash, card and UPI were taken by three people across two counters. The total is short and there’s no way to see where.', 'money you can’t trace, and trust between you and your staff.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Every leak at the counter, and what plugs it',
    'intro' => 'Read it like a register: each leak, then what plugs it and the plan that includes it.',
    'rows' => [
      ['Walk-outs while staff search', 'Customers ask by brand, salt or generic name; staff don’t know where you’ve filed it.', '<b>Search by brand or generic name</b>, or scan the strip with a USB barcode reader. F2 opens search from anywhere on the bill.', null, 'Every plan'],
      ['The wrong batch goes out', 'The newest strip is sold while an older batch sits behind it until it expires.', '<b>FEFO batch selection:</b> when you add an item, the batch that expires first fills in. Staff can still pick another.', null, 'Every plan'],
      ['Credit nobody can trace', 'Udhaar written in a notebook, without bill or items.', '<b>Credit as a payment method</b>, with the patient’s purchase history and dues shown on the bill.', null, 'Every plan'],
      ['Discounts given too freely', 'Each staff member decides the discount at the counter.', '<b>Discount verification</b> adds a check on item discounts. Ask us to show how it works in your demo.', 'feat_discount_verification'],
      ['Bills changed after the fact', 'A saved bill is edited later and nobody can tell.', '<b>Lock invoices</b> so saved bills can’t be quietly changed.', 'feat_sale_lock_invoices'],
      ['Refills that go elsewhere', 'A regular on monthly medicine forgets, then buys wherever they are.', '<b>Medicine reminders</b> nudge the patient to come back to your counter.', 'feat_medicine_reminder'],
      ['A drawer that doesn’t match', 'Several people take payments across a shift.', '<b>Collection and operator-wise collection reports</b> split the day by payment method and by staff member.', null],
    ],
    'example' => ['title' => 'Two walk-outs a day',
      'lines' => [['Customers who leave the queue', $wo['per_day'] . ' a day'], ['Average bill', inr($wo['bill'])], ['Days open', (string) $wo['days']]],
      'result' => ['Sales lost in a month', inr($wo_month)],
      'note' => 'Change the numbers to match your store. That’s sales, not profit, and it doesn’t count the customer who stops coming back.'],
  ],

  'steps' => ['eyebrow' => 'How a bill works', 'title' => 'Search, pick, take payment, save',
    'intro' => 'Four moves for a counter bill. Built-in walkthroughs show new staff each screen.',
    'items' => [
      ['Find the medicine', 'Type a brand or generic name, or scan the barcode. Search opens with <b>F2</b>, so hands stay on the keyboard.', 'Brand, generic or barcode'],
      ['The batch fills in', 'The batch that expires first is selected automatically (FEFO), with its expiry and MRP. Change it if the customer needs another.', 'Nearest expiry first'],
      ['Patient and payment', 'Add the patient (OPD or IPD, search by UHID), doctor name and GST number if needed. Take cash, card, UPI or credit.', 'Cash · card · UPI · credit'],
      ['Save, print or share', 'Choose <b>Done</b> or <b>Save &amp; print</b>' . (($dp = feature_plan_label('feat_sale_draft')) ? ', or save a draft (' . e($dp) . ')' : '') . '. Bills print on thermal or A4/A5, or go out on WhatsApp, SMS or email.', 'Ctrl+S saves the bill'],
    ],
    'keys' => [['F2', 'open item search'], ['Ctrl + S', 'save the bill']]],

  'caps' => ['eyebrow' => 'What’s on the counter screen', 'title' => 'Everything a medicine bill needs',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-search', 'Brand and generic search', 'Find items by brand or generic name, so substitutes on your shelf are easy to see.', null, 'Every plan'],
      ['i-scan', 'USB barcode readers', 'Scan the strip to add it to the bill instead of searching for it.', null, 'Every plan'],
      ['i-alert', 'FEFO batch selection', 'The earliest-expiry batch fills in first, with expiry and MRP on the line.', null, 'Every plan'],
      ['i-rupee', 'Cash, card, UPI or credit', 'Record how each bill was paid, with patient history and dues on screen.', null, 'Every plan'],
      ['i-chat', 'Bills on WhatsApp, SMS or email', 'Share the bill instead of printing it. Sending uses message credits; included credits vary by plan.', 'feat_bill_sharing'],
      ['i-printer', 'Thermal, A4 and A5 bills', 'Print in the format your counter uses.' . (($lp = feature_plan_label('feat_label_print')) ? ' Barcode label printing for stock is on ' . e($lp) . '.' : ''), null, 'Every plan'],
      ['i-file', 'Draft bills', 'Park a bill while the customer decides, then finish it later.', 'feat_sale_draft'],
      ['i-lock', 'Lock invoices', 'Saved bills can’t be edited quietly afterwards.', 'feat_sale_lock_invoices'],
      ['i-users', 'Patient vouchers and reminders', 'Patient vouchers for payments against credit, and medicine reminders for regulars.', 'feat_patient_voucher'],
    ]],

  'guide' => ['eyebrow' => 'Before you go live', 'title' => 'Set up a counter that stays fast in the rush',
    'intro' => 'Practical steps from how busy stores run, not product rules. Do them once and the counter stays quick.',
    'items' => [
      ['Clean up item names first', 'Duplicate items (“Paracetamol 500”, “PCM 500 tab”) slow search more than anything else. Merge them before you import, and keep the generic name filled in.'],
      ['Give every staff member their own login', 'Shared logins make the operator-wise collection report useless. One person, one login, one role.'],
      ['Test your printer on day one', 'Print a few real bills in thermal and A5 before the first rush, and check the paper width and your store details.'],
      ['Plug in the barcode reader', 'Most USB barcode readers type into the search box like a keyboard. Scan a few strips and check they find the right item.'],
      ['Decide your credit rules', 'Agree who can bill on credit and up to how much, before customers start asking. Then check dues on the patient ledger weekly.'],
    ],
    'note' => 'Billing runs in a web browser on the computer you already use at the counter.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'We don’t have customer quotes to show yet, so here’s a better test. Bring this list to a 30-minute demo and tick each one off with medicines from your own shelf.',
    'items' => [
      'Find a medicine by its generic name, then by brand',
      'Add an item with two batches and watch the earlier expiry fill in',
      'Scan a strip with a USB barcode reader',
      'Bill a regular on credit and see their dues on the bill',
      'Print the same bill on thermal and A5',
      'Send the bill to your own phone on WhatsApp',
      'Open today’s operator-wise collection report',
    ]],

  'related' => ['expiry', 'inventory', 'purchase'],

  'faq' => [
    ['Can staff bill by generic name?', 'Yes. Search by brand or generic name, or scan the barcode with a USB reader. F2 opens search from the bill.', true],
    ['Does it pick the batch by itself?', 'Yes. When you add an item, the batch that expires first is selected automatically (FEFO). Staff can still choose another batch.', true],
    ['Which payment methods can I record?', 'Cash, card, UPI and credit (udhaar), with the patient’s purchase history and dues on the bill.', true],
    ['Can I send bills on WhatsApp?', 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits, and each plan includes some. See <a href="' . url('pricing') . '">pricing</a>.', true],
    ['Will it work with my printer and barcode scanner?', 'Bills print in thermal and A4/A5 formats, and USB barcode readers work for search. Tell us your printer model during the demo and we’ll check it.', true],
    ...($free && $limits['sales'] !== null ? [['How many bills can I make on the free plan?', 'The ' . e($free['name']) . ' plan includes ' . $limits['sales'] . ' sales bills a month for one user. Paid plans have unlimited bills. See <a href="' . url('pricing') . '">pricing</a>.', true]] : []),
    ['What if the internet goes down during billing?', 'It runs in your web browser. [Confirm offline behaviour before publishing.]', false],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
