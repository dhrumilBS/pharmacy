<?php
/* Blog post. Facts: pricing API staff group (role management, 2FA, IP whitelist, force logout, activity report).
   Plans come live from the API. The role table is an illustrative setup, labelled as such. */
require __DIR__ . '/../partials/blog.php';
require_once __DIR__ . '/../partials/pricing-data.php';
$rm = feature_plan_label('feat_role_management');
$tfa = feature_plan_label('feat_2fa');
$ip = feature_plan_label('feat_ip_whitelist');
$act = feature_plan_label('feat_activity_report');
blog_start('staff-logins-roles');
?>
<?= bk_takeaways([
  'One person, one login. Shared logins make every report about “who did it” useless.',
  'Give each role only the screens it needs, starting from nothing and adding.',
  'Two-step OTP login and IP whitelisting close most of the remaining gaps.',
  'The day someone leaves, force logout and remove their access before they walk out.',
]) ?>

<p>When owners think about security, they picture a break-in. In practice, the problems are quieter: a shared password that an ex-employee still knows, a discount nobody can explain, a bill edited after the day closed. Most of them are fixed by setting up logins properly once.</p>

<h2>Why shared logins cost you</h2>
<?= bk_compare([
  'a' => ['One shared login', 'Common'],
  'b' => ['A login per person', 'Recommended'],
  'win' => 'b',
  'rows' => [
    ['Who gave that discount?', 'Unknown', 'Shown on the bill and in the activity report'],
    ['Collections by staff member', 'Impossible', 'Operator-wise collection report'],
    ['Someone leaves', 'Change the password for everyone', 'Remove one user'],
    ['What new staff can see', 'Everything', 'Only their screens'],
  ],
  'verdict' => 'Give everyone their own login from day one. It’s the single change that makes every other control work.',
]) ?>

<h2>Set up roles in an afternoon</h2>
<?= bk_steps([
  ['List who does what', '<p>Write down each person and the jobs they do: billing, purchase entry, reports, settings.</p>', '15 minutes'],
  ['Create a few roles', '<p>Start from nothing and add screens to each role.' . ($rm ? ' Role management is on ' . e($rm) . '.' : '') . '</p>', '30 minutes'],
  ['Add each person as a user', '<p>One login each, with the role that matches their job. Users per plan: ' . e(plan_users_text()) . '.</p>', '15 minutes'],
  ['Turn on the extra locks', '<p>Switch on two-step OTP login' . ($tfa ? ' (' . e($tfa) . ')' : '') . ', and allow logins only from your store’s IP addresses' . ($ip ? ' (' . e($ip) . ')' : '') . '.</p>', '10 minutes'],
]) ?>

<h2>A starting set of roles</h2>
<?= bk_table(
  ['Screen', 'Cashier', 'Pharmacist', 'Purchase clerk', 'Manager'],
  [
    ['<b>Sale</b>', '✓', '✓', '—', '✓'],
    ['<b>Purchase entry and import</b>', '—', '—', '✓', '✓'],
    ['<b>Stock and short book</b>', '—', '✓', '✓', '✓'],
    ['<b>Reports and margins</b>', '—', '—', '—', '✓'],
    ['<b>Item rates and settings</b>', '—', '—', '—', '✓'],
  ],
  'An illustrative setup. Adjust it to how your store divides the work.',
  ['hl' => 4]
) ?>
<?= bk_note('tip', 'Start strict, then loosen', '<p>It’s easier to add a screen when someone asks for it than to take one away later. Begin each role with the least it needs.</p>') ?>

<h2>The day someone leaves</h2>
<?= bk_checklist('Before they walk out', [
  'Force logout all their sessions',
  'Remove or deactivate their user',
  'Change any password they knew, including shared device and Wi-Fi passwords',
  'Look through their last week in the activity report' . ($act ? ' (' . e($act) . ')' : ''),
  'Check the credit they gave against what customers say they owe',
  'Give their replacement a new login, never the old one',
]) ?>
<?= bk_note('caution', 'Don’t wait until month-end', '<p>Access left open for a few weeks is how most problems start. Do this on the person’s last day, not when you next have time.</p>') ?>
<?= bk_cta('feature', 'security', 'Own login per user, roles, two-step OTP login, IP whitelisting, force logout and an activity report.') ?>
<?= bk_cta('inline', 'How the software handles store data', 'Read data & security', url('data-security')) ?>
<?= bk_cta('panel', 'Set up a sample store in a demo', 'We’ll create two roles, log in as each, and show what they can and can’t see.') ?>
<?php
blog_end([
  ['Can each staff member have their own login?', 'Yes. Users included: ' . e(plan_users_text()) . '.'],
  ['Is two-step login available?', 'Yes. Two-step OTP login is on ' . e($tfa ?: 'higher plans') . '.'],
  ['Can I stop logins from outside the store?', 'Yes. IP whitelisting allows logins only from the IP addresses you choose' . ($ip ? ' (' . e($ip) . ')' : '') . '.'],
]);
