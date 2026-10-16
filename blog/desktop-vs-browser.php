<?php
/* Blog post: category comparison, no named competitors (PHARMACY-SITE-STRUCTURE.md §4.6). Offline behaviour,
   backups and hosting are unconfirmed for our app: never claim them, point to the demo and data-security page. */
require __DIR__ . '/../partials/blog.php';
require_once __DIR__ . '/../partials/pricing-data.php';
blog_start('desktop-vs-browser');
?>
<?= bk_takeaways([
  'The real difference is <b>where your data lives</b>: on one PC in the store, or on the vendor’s servers.',
  'Neither is risk-free. Ask every vendor the same questions and compare the answers.',
  'Published, yearly pricing is easier to plan for than licence fees plus maintenance by quote.',
  'Whatever you choose, make sure you can get your data out if you leave.',
]) ?>

<p>Many medical stores run software installed on one PC, bought through a local dealer years ago. Browser-based software works differently. Neither is automatically better. Here’s what actually changes, so you can judge for your store.</p>

<h2>What changes when you move</h2>
<?= bk_compare([
  'title' => 'Typical desktop software vs typical browser-based software',
  'a' => ['Installed on a PC', 'Desktop'],
  'b' => ['Runs in a web browser', 'Browser-based'],
  'win' => null,
  'rows' => [
    ['Where the data lives', 'On the store PC (and whatever backups you keep)', 'On the vendor’s servers'],
    ['If the PC fails', 'Data is only as safe as your last backup', 'Log in from another computer'],
    ['Checking the store from outside', 'Usually needs remote-desktop tools', 'Log in from any browser'],
    ['Updates', 'Installed by a dealer or by you', 'Arrive without visiting the store'],
    ['If the internet drops', 'Billing carries on', 'Depends on the product: ask'],
    ['Pricing', 'Often a licence plus yearly maintenance, by quote', 'Often a published yearly or monthly fee'],
  ],
  'verdict' => 'Browser-based software removes the single-PC risk and lets you see the store from anywhere. In exchange, you depend on the internet and the vendor. Ask about both before you decide.',
]) ?>
<?= bk_note('caution', 'Ask about the internet, every time', '<p>Ask any browser-based vendor what happens at the counter when the connection drops, and how long billing can continue. Get the answer in writing. For our software, ask us in the demo.</p>') ?>

<h2>Eight questions to ask any vendor</h2>
<?= bk_table(
  ['Question', 'Why it matters', 'A good answer sounds like'],
  [
    ['<b>Where is my data stored?</b>', 'You are responsible for patient records', 'A clear answer, in writing'],
    ['<b>Can I export all my data if I leave?</b>', 'You shouldn’t be locked in', 'Yes, in a common format such as Excel or CSV'],
    ['<b>What happens if the internet drops?</b>', 'The counter can’t stop', 'A specific answer, not “it rarely happens”'],
    ['<b>Does it pick the earliest-expiry batch?</b>', 'Expiry is your biggest silent loss', 'Yes, at billing, automatically'],
    ['<b>How do I bring my items and stock across?</b>', 'Switching is the risky part', 'A sample sheet and a clear import'],
    ['<b>Does every user get their own login?</b>', 'Shared logins hide mistakes', 'Yes, with roles'],
    ['<b>What will year two cost?</b>', 'Renewals are where surprises hide', 'A published price'],
    ['<b>Who do I call at 8 pm?</b>', 'Problems don’t keep office hours', 'A named number and stated hours'],
  ],
  'Ask every vendor the same questions and compare the answers side by side.',
  ['hl' => 2]
) ?>

<h2>What it costs, honestly</h2>
<p>Compare the total over three years, not the first invoice: licence, yearly maintenance, per-user charges and any fees to move your data. Our plans are published: <?= e(pricing_cost_text()) ?></p>
<?= bk_cta('inline', 'Compare every feature by plan', 'See pricing', url('pricing')) ?>

<h2>If you decide to switch</h2>
<p>The software matters less than how you switch. Run the old and new systems side by side for a couple of weeks and go live on a quiet day.</p>
<?= bk_cta('feature', 'switch', 'A four-step switch: set up free, import items and stock by CSV, train staff, and go live on your day.') ?>
<?= bk_cta('panel', 'Ask us the eight questions', 'Book a demo and go through the list above. We’ll answer each one, including the ones we’re still confirming.') ?>
<?php
blog_end([
  ['Do I need to install anything?', 'No. It runs in your web browser. You sign up with your store name and email, verify with an OTP, and log in from any computer.'],
  ['Can I take my reports with me?', 'Reports, item lists and ledgers export to Excel or CSV.'],
]);
