<?php
/* Feature page: Security & roles ("Run the business"). Data only; layout in partials/feature-page.php.
   Facts: PHARMACY-ICP-AND-UX-STRATEGY.md §0 and the pricing API (staff group). Hosting and data handling live on
   data-security.php; don't repeat or invent them here. Screen: screenshots/v2/src/09-security.html. */
require_once __DIR__ . '/../partials/config.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$title       = page_title('Pharmacy software security: staff roles, two-step login and IP whitelisting');
$description = 'Control who can do what at your medical store: one login per staff member, role management, two-step OTP login, IP whitelisting, force logout, locked invoices and an activity report.';
$page_key    = 'security';

$tfa = feature_plan_label('feat_2fa');
$ip  = feature_plan_label('feat_ip_whitelist');
$act = feature_plan_label('feat_activity_report');
$users = plan_users_text();

$fp = [
  'hero' => ['crumb' => 'Security & roles', 'parent' => 'Features', 'eyebrow' => 'Security & roles',
    'h1' => 'Every person on their own login, with only the access they need',
    'lead' => 'Give each staff member a login and a role, turn on two-step OTP login, allow logins only from your store’s IP addresses, and see who did what in the activity report. Written for owners and the administrators who look after the store’s accounts.'],
  'ctas'   => ['demo' => 'See the controls in a demo', 'free' => 'Start free'],
  'assure' => array_values(array_filter(['Own login and role for every user', $tfa ? 'Two-step OTP login (' . $tfa . ')' : null, $ip ? 'IP whitelisting (' . $ip . ')' : null])),
  'screen' => ['v2-09-security', 'Users and security settings with roles, two-step login, IP whitelist, force logout and the activity report'],

  'scenes' => ['eyebrow' => 'Sound familiar?', 'title' => 'Where store access goes wrong',
    'intro' => 'Most problems aren’t break-ins. They’re shared passwords and nobody being sure who did what.',
    'items' => [
      ['A staff member leaves', 'The password leaves with them', 'Everyone at the counter used one login, and the person who just left still knows it.', 'sales and patient records open to someone who no longer works for you.'],
      ['Checking a discount', 'Who gave that discount?', 'One shared login means every bill shows the same name.', 'discounts and voids nobody can be asked about.'],
      ['Late evening', 'A login from somewhere unexpected', 'The store is closed, but someone opens the sales report from outside.', 'your numbers in hands you didn’t choose.'],
      ['Month-end', 'An old bill has changed', 'A saved invoice was edited after the day closed, and the totals no longer match.', 'reports you can’t trust, and an awkward talk with your CA.'],
    ]],

  'leaks' => ['eyebrow' => 'Leak register', 'title' => 'Where access leaks, and what closes it',
    'intro' => 'Each leak, then the control that closes it and the plan that includes it.',
    'rows' => [
      ['Shared logins', 'The whole counter uses one username and password.', '<b>One login per person.</b> Users included: ' . e($users) . '.', 'feat_user_limit'],
      ['Everyone sees everything', 'New staff can open reports, rates and settings on day one.', '<b>Role management:</b> give each role only the screens it needs.', 'feat_role_management'],
      ['Passwords that get passed around', 'A password alone is easy to share or guess.', '<b>Two-step OTP login</b> on top of the password.', 'feat_2fa'],
      ['Logins from outside the store', 'Anyone with a password can log in from anywhere.', '<b>IP whitelisting</b> allows logins only from the IP addresses you choose.', 'feat_ip_whitelist'],
      ['Ex-staff still logged in', 'Someone leaves, but a browser is still signed in.', '<b>Force logout</b> ends their sessions straight away.', 'feat_force_logout'],
      ['Changes nobody owns', 'A rate, a stock figure or a setting changes and nobody knows who did it.', '<b>Activity report</b> shows who did what, and when.', 'feat_activity_report'],
      ['Bills edited after the fact', 'Saved invoices are changed to cover a mistake.', '<b>Lock invoices</b>, and <b>discount verification</b> for item discounts.', 'feat_sale_lock_invoices'],
    ]],

  'steps' => ['eyebrow' => 'How to set it up', 'title' => 'An afternoon of setup, then it runs itself',
    'intro' => 'Do this once when you start, and again whenever someone joins or leaves.',
    'items' => [
      ['Add a user for each person', 'Add every staff member in Users with their own login. Users included: ' . e($users) . '.', 'One person, one login'],
      ['Give each one a role', 'Decide what a cashier, a pharmacist and a manager should see, and assign roles to match.', 'Role management'],
      ['Turn on the extra locks', 'Switch on two-step OTP login' . ($tfa ? ' (' . e($tfa) . ')' : '') . ' and add your store’s IP addresses to the whitelist' . ($ip ? ' (' . e($ip) . ')' : '') . '.', 'OTP · IP whitelist'],
      ['Review now and then', 'Check the activity report' . ($act ? ' (' . e($act) . ')' : '') . ' each week, and force logout anyone who leaves on their last day.', 'Activity report'],
    ]],

  'caps' => ['eyebrow' => 'The controls', 'title' => 'Access control that fits a medical store',
    'intro' => 'Where a control depends on your plan, the plan it starts on is named.',
    'items' => [
      ['i-users', 'Own login per user', 'Users included: ' . e($users) . '.', 'feat_user_limit'],
      ['i-register', 'Role management', 'Roles and privileges, so each person sees only their screens.', 'feat_role_management'],
      ['i-mobile', 'Two-step OTP login', 'An OTP on top of the password at login.', 'feat_2fa'],
      ['i-shield', 'IP whitelisting', 'Logins allowed only from the IP addresses you choose.', 'feat_ip_whitelist'],
      ['i-x', 'Force logout', 'End someone’s sessions at once, for example on their last day.', 'feat_force_logout'],
      ['i-file', 'Activity report', 'Who did what, and when.', 'feat_activity_report'],
      ['i-lock', 'Lock invoices', 'Saved sale and purchase bills can’t be quietly changed.', 'feat_sale_lock_invoices'],
      ['i-rupee', 'Discount verification', 'A check on item discounts given at the counter.', 'feat_discount_verification'],
      ['i-stores', 'Central administration', 'For chains: run linked outlets from one central account.', 'feat_central_admin'],
    ]],

  'guide' => ['eyebrow' => 'A checklist', 'title' => 'When a staff member leaves',
    'intro' => 'The same day someone leaves, run through this. It takes ten minutes and closes most gaps.',
    'items' => [
      ['Force logout their sessions', 'Do it before they walk out, so no browser stays signed in.'],
      ['Remove their access in Users', 'Their login should stop working today, not at month-end.'],
      ['Change any password they knew', 'Shared device or Wi-Fi passwords included, not only the software.'],
      ['Look at their last week in the activity report', 'Check unusual discounts, voids or edits while the details are fresh.'],
      ['Check the credit they gave', 'Patient dues they recorded should match what customers say they owe.'],
      ['Hand their role to someone else', 'Give the new person their own login, never the old one.'],
    ],
    'note' => 'Good practice for any store system. Plan availability for each control is shown above.'],

  'proof' => ['eyebrow' => 'Check it yourself', 'title' => 'Ask us to show you these in the demo',
    'intro' => 'Bring whoever manages your staff. We’ll set up a sample store with two roles and try each control.',
    'items' => [
      'Add a user with a cashier role',
      'Log in as that user and see which screens are hidden',
      'Turn on two-step OTP login',
      'Add an IP address to the whitelist',
      'Force logout a user',
      'Find a change in the activity report',
    ]],

  'related' => ['reports', 'gst', 'retention'],
  'related_title' => 'Also in the back office',

  'faq' => [
    ['Can each staff member have their own login?', 'Yes. Users included: ' . e($users) . '. See <a href="' . url('pricing') . '#compare">plans</a>.', true],
    ['Can I limit what staff can see?', 'Yes. Role management gives each role only the screens and actions it needs' . (($rm = feature_plan_label('feat_role_management')) ? ' (' . e($rm) . ')' : '') . '.', true],
    ['Is two-step login available?', 'Yes. Two-step OTP login is on ' . e($tfa ?: 'higher plans') . '.', true],
    ['Can I stop logins from outside the store?', 'Yes. IP whitelisting allows logins only from the IP addresses you choose' . ($ip ? ' (' . e($ip) . ')' : '') . '.', true],
    ['What do I do when someone leaves?', 'Force logout their sessions and remove their access in Users the same day, then check their recent activity.', true],
    ['Where is my data stored, and who can see it?', 'See <a href="' . url('data-security') . '">data &amp; security</a> for how data is handled.', true],
  ],
];
require __DIR__ . '/../partials/feature-page.php';
