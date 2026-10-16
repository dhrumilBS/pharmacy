<?php
/* Feature page: Reports & dashboard ("Run the business"). Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 (accounts and reporting). Don't claim an owner mobile app.
   Plans come live from the pricing API ('feat_*'). */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy reports software: dashboard, margin reports and a daily WhatsApp report');
$description = 'Medical store reports without standing at the counter: executive dashboard, 25+ reports including margin, top-selling, non-moving, collection and supplier due reports, and the day’s report on WhatsApp.';
$page_key    = 'reports';

$dash = feature_plan_label('feat_exec_dashboard');
/* Worked example (illustrative): one point of margin that slips unnoticed */
$mg = ['sales' => 800000, 'slip' => 1];
$mg_month = (int) round($mg['sales'] * $mg['slip'] / 100);

$fp = [
  'hero' => ['crumb' => 'Reports & dashboard', 'parent' => 'Features', 'eyebrow' => 'Reports & dashboard',
    'h1' => 'Know how the store did without standing at the counter',
    'lead' => 'An executive dashboard, 25+ reports and the day’s report on WhatsApp show sales, collections, margin, slow stock and supplier dues. Check the store from wherever you are, in a web browser.'],
  'ctas'   => ['demo' => 'See the reports in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['25+ reports, export to Excel', 'The day’s report on WhatsApp (uses credits)', $dash ? 'Executive dashboard (' . $dash . ')' : null])),
  'screen' => ['v2-02-dashboard', 'Pharmacy dashboard with sales, collections, expiry and stock alerts'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Running a store on guesses',
    'intro' => 'Most owners know the day’s total. Fewer know where the margin went.',
    'items' => [
      ['Sunday, at a family function', 'How did the store do today?', 'You call the counter twice for the total and get a rounded number between customers.', 'decisions made on guesses, and a day off that isn’t one.'],
      ['Month-end', 'Sales are up, the bank balance isn’t', 'Turnover looks fine, but margin slipped on a few fast movers after a rate change nobody spotted.', 'a little on every strip, every day it goes unnoticed.'],
      ['Payment week', 'Supplier dues arrive together', 'Three distributors want payment in the same week, and you only find out when the reps walk in.', 'cash-flow stress, or a late payment that strains a good supplier.'],
      ['Staff review', 'Who billed what?', 'Collections and discounts by staff member are a feeling, not a number.', 'no fair way to reward good staff or correct mistakes.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where blind spots cost you, and what shows them',
    'intro' => 'Each blind spot, then the report that shows it and the plan that includes it.',
    'rows' => [
      ['No view of the day from outside', 'You only know the numbers if you’re in the store.', '<b>The day’s report on WhatsApp</b> after closing. It uses WhatsApp credits; included credits vary by plan.', 'feat_daily_alerts'],
      ['Margin slipping quietly', 'A rate change or a big discount eats margin on a fast mover.', '<b>Margin report</b>, so a drop shows up before month-end.', 'feat_margin_report'],
      ['Buying the wrong things', 'Slow items get reordered; fast movers run short.', '<b>Top-selling and non-moving items reports</b>, side by side.', 'feat_top_selling_item'],
      ['Supplier dues as surprises', 'Payment dates live in the reps’ heads.', '<b>Supplier due report</b> for every distributor, with their ledger.', 'feat_supplier_due_report'],
      ['A drawer that doesn’t add up', 'Several people take payments across a shift.', '<b>Collection and operator-wise collection reports</b> by payment method and by staff member.', null],
      ['Too many numbers, no overview', 'Reports exist, but nobody opens ten of them every morning.', '<b>Executive dashboard</b> with sales, collections, expiry and stock alerts on one screen.', 'feat_exec_dashboard'],
      ['Changes nobody can explain', 'A figure changed and nobody remembers why.', '<b>Activity report</b> shows who did what, and when.', 'feat_activity_report'],
    ],
    'example' => ['title' => 'One point of margin',
      'lines' => [['Monthly sales', inr($mg['sales'])], ['Margin that slipped unnoticed', $mg['slip'] . '%']],
      'result' => ['Lost in a month', inr($mg_month)],
      'note' => 'Change the numbers to match your store. A margin report doesn’t stop the slip; it shows it while you can still act.'],
  ],

  'steps' => ['eyebrow' => 'How reporting works', 'title' => 'Bill as usual, and the reports fill themselves',
    'intro' => 'There’s nothing extra to enter. Every bill, purchase and return you save feeds the reports.',
    'items' => [
      ['Every entry counts', 'Sales, purchases and returns go into the reports as they’re saved, along with who billed and how each bill was paid.', 'Nothing to re-enter'],
      ['Morning: one screen', 'The executive dashboard shows sales, collections, expiry and stock alerts on one screen' . ($dash ? ' (' . e($dash) . ')' : '') . '.', 'Executive dashboard'],
      ['Evening: on WhatsApp', 'After closing, the day’s report can arrive on WhatsApp, so you see the numbers even when you’re away.', 'Uses WhatsApp credits'],
      ['Month-end: for your CA', 'Margin, GST, collection and supplier due reports export to Excel for your accountant.', 'Export to Excel'],
    ]],

  'caps' => ['eyebrow' => 'The reports', 'title' => '25+ reports, the ones owners actually open',
    'intro' => 'Where a report depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-chart', 'Executive dashboard', 'Sales, collections, expiry and stock alerts on one screen, updated with every bill.', 'feat_exec_dashboard'],
      ['i-receipt', 'Sales, purchase and return reports', 'Sales, purchases and returns for the dates you choose, ready to export.', 'feat_spr_reports'],
      ['i-rupee', 'Margin report', 'See the margin on what you sell, so a slip shows up early.', 'feat_margin_report'],
      ['i-box', 'Top-selling and non-moving items', 'What deserves shelf space, and what’s blocking your money.', 'feat_top_selling_item'],
      ['i-users', 'Collection and operator-wise collection', 'The day split by payment method and by staff member.', null],
      ['i-truck', 'Supplier due report', 'What you owe each distributor, from their ledger.', 'feat_supplier_due_report'],
      ['i-chat', 'The day’s report on WhatsApp', 'Daily numbers sent to your phone after closing. Uses WhatsApp credits.', 'feat_daily_alerts'],
      ['i-file', 'Analytics reports', 'Deeper analysis, with more depth on higher plans.', 'feat_analytics_reports'],
      ['i-stores', 'Central reports across stores', 'One view of every linked outlet for chains.', 'feat_central_reports'],
    ]],

  'guide' => ['eyebrow' => 'A simple routine', 'title' => 'Ten minutes with your reports, every morning',
    'intro' => 'Habits that careful owners keep. The reports give you the numbers; the routine turns them into decisions.',
    'items' => [
      ['Match yesterday’s collections to the cash', 'Check cash, card and UPI in the collection report against the drawer and the bank before the day starts.'],
      ['Look at collections by staff member', 'Big differences between people on similar shifts are worth a quiet conversation.'],
      ['Read the short book and the expiry list', 'Two minutes on what to order and what to push today saves a stock-out and a write-off.'],
      ['Once a week, check margin on your top 20 items', 'That’s where a rate change or a discount habit hurts most.'],
      ['Before payment week, open the supplier due report', 'Plan payments by date instead of by who calls first.'],
      ['Send the month to your CA in one go', 'Export the GST, sales and purchase reports together, from the same month, so the numbers agree.'],
    ],
    'note' => 'Report names and depth vary by plan; see the plan comparison.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring this list to a 30-minute demo. We’ll open each report with sample data.',
    'items' => [
      'Open the executive dashboard for today',
      'Export yesterday’s sales report to Excel',
      'Open the margin report and find a fast-moving item',
      'Split a day’s collections by staff member',
      'Open the supplier due report',
      'See what the day’s report on WhatsApp looks like',
    ]],

  'related' => ['gst', 'retention', 'security'],
  'related_title' => 'Also in the back office',

  'faq' => [
    ['Can I check the store when I’m not there?', 'Yes. It runs in a web browser, so you can log in from anywhere. The day’s report can also arrive on WhatsApp, which uses WhatsApp credits.', true],
    ['How many reports are there?', 'More than 25, including sales, purchase, return, margin, top-selling, non-moving, collection, operator-wise collection, GST and supplier due reports.', true],
    ['Can I export reports for my accountant?', 'Yes. Reports, item lists and ledgers export to Excel or CSV.', true],
    ['Which plan has the executive dashboard?', $dash ? 'The executive dashboard is on ' . e($dash) . '. See the <a href="' . url('pricing') . '#compare">plan comparison</a>.' : 'See the <a href="' . url('pricing') . '#compare">plan comparison</a>.', true],
    ['Can I see collections for each staff member?', 'Yes. The operator-wise collection report splits the day’s collections by the person who billed.', true],
    ['Can I see reports across several stores?', 'Yes, on Enterprise: a central reports dashboard covers every linked outlet.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
