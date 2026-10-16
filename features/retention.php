<?php
/* Feature page: Customer retention ("Run the business"). Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (sale screen, customer retention). How reminders are set up (channel,
   timing) is not verified, so it stays general and the FAQ answer is flagged. Plans and credits come live from the API. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy customer retention: medicine reminders, bills on WhatsApp and loyalty');
$description = 'Bring regular patients back to your counter: bills on WhatsApp, SMS or email, medicine reminders, loyalty cards, online orders for pick-up or delivery, and patient history and dues on every bill.';
$page_key    = 'retention';

$rem = feature_plan_label('feat_medicine_reminder');
/* Worked example (illustrative): regular patients who drift to another chemist */
$rg = ['regulars' => 10, 'bill' => 900, 'months' => 12];
$rg_year = $rg['regulars'] * $rg['bill'] * $rg['months'];

$fp = [
  'hero' => ['crumb' => 'Customer retention', 'parent' => 'Features', 'eyebrow' => 'Customer retention',
    'h1' => 'Bring regulars back for their next refill',
    'lead' => 'Share bills on WhatsApp, SMS or email, send medicine reminders, keep loyalty cards and take online orders for pick-up or delivery. Every bill shows the patient’s history and dues, so your counter remembers them.'],
  'ctas'   => ['demo' => 'See it in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['Bills on WhatsApp, SMS or email', $rem ? 'Medicine reminders (' . $rem . ')' : null, 'Online orders for pick-up or delivery'])),
  'screen' => ['v2-08-retention', 'Patient profile with purchase history, medicine reminders, loyalty card, online orders and bill sharing'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'How regular patients slip away',
    'intro' => 'Regulars on monthly medicine are the steadiest part of a medical store’s sales. They rarely leave in one go.',
    'items' => [
      ['First week of the month', 'A regular doesn’t come in', 'A patient on monthly BP medicine runs out while near another chemist, and buys there.', 'that month’s sale, and often the months after.'],
      ['8 pm, a phone call', '“Can you keep it ready?”', 'A customer wants their medicines packed for pick-up. The order goes on a sticky note that falls behind the counter.', 'a forgotten order, and a customer who stops calling.'],
      ['At the counter', '“I lost the bill”', 'A patient needs last month’s bill for a claim. Someone digs through the bill book while the queue waits.', 'counter time, and a frustrated patient.'],
      ['Festival season', 'Ten years loyal, treated like a walk-in', 'The chain store nearby gives points. Your oldest regulars get exactly what a stranger gets.', 'loyalty you’ve earned but don’t use.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where regulars leak away, and what keeps them',
    'intro' => 'Each leak, then what the software does about it and the plan that includes it.',
    'rows' => [
      ['Refills bought elsewhere', 'Patients on regular medicine forget, then buy wherever they are.', '<b>Medicine reminders</b> nudge them to come back to your counter.', 'feat_medicine_reminder'],
      ['Lost paper bills', 'Patients ask for copies weeks later.', '<b>Bills shared on WhatsApp, SMS or email</b>, so the patient keeps their own copy. Uses message credits.', 'feat_bill_sharing'],
      ['Phone orders forgotten', 'Orders for pick-up or delivery live on paper.', '<b>Online sales orders</b> for pick-up or delivery, in the software instead of on paper.', null],
      ['Regulars treated like walk-ins', 'Long-standing customers get nothing for staying.', '<b>Loyalty cards</b> for the customers you want to keep.', null],
      ['“What did you take last time?”', 'Staff guess the brand or strength from memory.', '<b>Purchase history on the bill</b> when you pick the patient.', null, 'Every plan'],
      ['Credit customers who drift', 'Dues pile up and the customer starts avoiding the store.', '<b>Dues shown on every bill</b>, a <b>patient ledger</b> and <b>patient vouchers</b> for part payments.', 'feat_patient_voucher'],
      ['Messages that cost more than planned', 'Every bill sent and reminder uses a credit.', '<b>Included message credits</b> on every plan, and more as an add-on when you need them.', null, 'Varies by plan'],
    ],
    'example' => ['title' => 'Ten regulars drift away',
      'lines' => [['Regular patients who move to another chemist', (string) $rg['regulars']], ['Their average monthly bill', inr($rg['bill'])], ['Months in a year', (string) $rg['months']]],
      'result' => ['Sales lost in a year', inr($rg_year)],
      'note' => 'Change the numbers to match your store. That’s sales, not profit, and it doesn’t count the family members who follow them.'],
  ],

  'steps' => ['eyebrow' => 'How it works', 'title' => 'It starts with the patient on the bill',
    'intro' => 'Everything here builds on one habit: picking the patient when you bill.',
    'items' => [
      ['Pick the patient at billing', 'Pick the patient on the bill (OPD or IPD, with UHID search). Their purchase history and dues show on screen.', 'History on every bill'],
      ['Send the bill', 'Share it on WhatsApp, SMS or email instead of printing, or as well as printing.', 'Uses message credits'],
      ['Remind regulars', 'Medicine reminders bring patients on regular medicine back for their refill' . ($rem ? ' (' . e($rem) . ')' : '') . '.', 'For regular medicine'],
      ['Orders and loyalty', 'Take online orders for pick-up or delivery, and give loyalty cards to the customers you want to keep.', 'Pick-up or delivery'],
    ]],

  'caps' => ['eyebrow' => 'Retention tools', 'title' => 'Small things that keep customers coming back',
    'intro' => 'Where a feature depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-calendar', 'Medicine reminders', 'For patients on regular medicine, so the refill comes back to you.', 'feat_medicine_reminder'],
      ['i-chat', 'Bills on WhatsApp, SMS or email', 'The patient keeps a copy; you print less. Uses message credits.', 'feat_bill_sharing'],
      ['i-store', 'Online orders', 'Sales orders for pick-up or delivery, kept in the software instead of on paper.', null],
      ['i-shield', 'Loyalty cards', 'Recognise the customers who have stayed with you.', null],
      ['i-users', 'Patient history and dues', 'Shown on the bill when you pick the patient.', null, 'Every plan'],
      ['i-register', 'Patient ledger and vouchers', 'Credit, part payments and dues per patient.', 'feat_patient_voucher'],
      ['i-mail', 'Included message credits', 'Each plan includes some: ' . e(plan_credits_text()) . '. More as an add-on.', null],
      ['i-hospital', 'For hospital pharmacies', 'IPD and OPD patients with UHID search on the same counter screen.', null],
      ['i-receipt', 'Doctor and GST details', 'Doctor name and the patient’s GST number on the bill when needed.', null],
    ]],

  'guide' => ['eyebrow' => 'Good practice', 'title' => 'Keeping regulars without annoying them',
    'intro' => 'Messages work when they’re useful and expected. A few habits keep them that way.',
    'items' => [
      ['Ask before you message', 'Ask the patient if they’d like their bill and reminders on WhatsApp, and note it. People answer messages they agreed to.'],
      ['Get the mobile number right on the first bill', 'One wrong digit and every bill and reminder after that goes nowhere.'],
      ['Use reminders for regular medicine only', 'BP, diabetes and thyroid refills are welcome reminders. Promotions every week are not.'],
      ['Keep messages short and from the store’s name', 'Patients should know who it’s from at a glance.'],
      ['Talk about dues in person', 'Check the patient ledger weekly, then raise dues quietly at the counter, not in front of the queue.'],
      ['Reward the long-standing regulars', 'A loyalty card for someone who has bought from you for years says more than any discount.'],
    ],
    'note' => 'General good practice. Follow the messaging and privacy rules that apply to your store.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring this list to a 30-minute demo. We’ll try each one with a sample patient.',
    'items' => [
      'Pick a patient and see their purchase history on the bill',
      'Send the bill to your own phone on WhatsApp',
      'Set up a medicine reminder for a regular patient',
      'Take an online order for pick-up',
      'Give a patient a loyalty card',
      'See how many message credits your plan includes',
    ]],

  'related' => ['reports', 'gst', 'security'],
  'related_title' => 'Also in the back office',

  'faq' => [
    ['Can I send bills on WhatsApp?', 'Yes. Bills can be shared on WhatsApp, SMS or email. Sending uses message credits, and each plan includes some. See <a href="' . url('pricing') . '">pricing</a>.', true],
    ['How do medicine reminders work?', 'Medicine reminders bring patients on regular medicine back for their refill' . ($rem ? ' (' . e($rem) . ')' : '') . '. [Confirm how reminders are set up and how they reach the patient.]', false],
    ['Can customers order for pick-up or delivery?', 'Yes. Online sales orders can be for pick-up or delivery.', true],
    ['How many message credits are included?', 'Each plan includes some: ' . e(plan_credits_text()) . '. You can buy more as an add-on. See <a href="' . url('pricing') . '">pricing</a>.', true],
    ['Can I see what a patient bought before?', 'Yes. When you pick the patient on a bill, their purchase history and dues show on screen.', true],
    ['Is patient information kept safe?', 'Each staff member has their own login and role, with more controls on higher plans. See <a href="' . url('security') . '">security &amp; roles</a> and <a href="' . url('data-security') . '">data &amp; security</a>.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
