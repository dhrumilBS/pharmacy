<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Data & security');
$description = 'How our pharmacy software protects your store and patient data: individual logins, roles, two-step OTP login, IP whitelisting, activity reports, ABDM consent and how we handle incidents.';
$page_key    = 'data-security';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Data & security', 'parent' => 'Legal', 'eyebrow' => 'Trust',
  'h1' => 'Your data, protected at the counter and in the cloud',
  'lead' => 'Pharmacy records include patient details, prescriptions and money. Here is how the software keeps them safe, and the controls you have.',
  'meta' => 'Last updated: [date]'];

$support = e(SITE['support_phone']);
$u_priv  = url('privacy');
$u_terms = url('terms');
/* Product screen for the controls table (screenshots/v2/src/09-security.html) */
$shot = '<figure class="shot" style="margin:20px 0 6px">' . svg_img('v2-09-security', 1440, 900, 'Sample screen: users and security settings with roles, two-step login, IP whitelist, force logout and the activity report') . '</figure>'
      . '<p class="meta-line" style="margin:0 0 12px">Sample screen · illustrative data</p>';

$sections = [
 ['controls', 'Controls in the app', <<<HTML
  <p>These controls are available in the app today. Most are switched on from settings by the store owner.</p>
  <table><thead><tr><th>Control</th><th>What it does</th><th>Plan</th></tr></thead><tbody>
   <tr><td>Individual logins</td><td>Every staff member has their own login. No shared passwords.</td><td>All plans</td></tr>
   <tr><td>Email OTP at sign-up</td><td>New accounts are verified with a one-time password sent to the owner’s email.</td><td>All plans</td></tr>
   <tr><td>Roles &amp; privileges</td><td>Choose which screens and actions each user can access.</td><td>Basic and above</td></tr>
   <tr><td>Force logout</td><td>Sign a user out of every device at once, for example when staff leave.</td><td>Basic and above</td></tr>
   <tr><td>Two-step login (OTP)</td><td>A one-time password is needed in addition to the password.</td><td>Advance and above</td></tr>
   <tr><td>IP whitelisting</td><td>Allow logins only from your store’s network.</td><td>Advance and above</td></tr>
   <tr><td>Activity report</td><td>See who created, edited or deleted bills and records, and the IP address they logged in from.</td><td>Advance and above</td></tr>
   <tr><td>Lock invoices &amp; discount verification</td><td>Prevent edits to finalised bills and require approval for discounts.</td><td>Advance and above</td></tr>
  </tbody></table>
  {$shot}
HTML],

 ['access', 'Access control', <<<HTML
  <ul>
   <li><b>Least privilege.</b> Give each user only the screens they need. For example, a counter assistant can be limited to billing, without access to purchase or profit reports.</li>
   <li><b>Clear accountability.</b> Because logins are individual, every bill, edit and deletion is tied to a person.</li>
   <li><b>Quick removal.</b> Deactivate a user and use force logout the same day someone leaves.</li>
   <li><b>Our staff.</b> Our support team accesses customer data only to resolve an issue you raise, and access is limited to people who need it [confirm internal access controls and logging].</li>
  </ul>
HTML],

 ['auth', 'Authentication', <<<HTML
  <ul>
   <li>Email OTP verification when an account is created.</li>
   <li>Optional two-step login: after the password, the user enters a one-time password, so a stolen password alone is not enough.</li>
   <li>Passwords are stored in a protected, non-readable form [confirm hashing method]. Our staff will never ask for your password or OTP.</li>
   <li>[Confirm password rules, failed-login lockout and session time-out.]</li>
  </ul>
HTML],

 ['network', 'Network restrictions', <<<HTML
  <p>With IP whitelisting, logins are accepted only from IP addresses you approve, such as your store’s broadband connection. Staff cannot sign in from home or an unknown network unless you allow it. If your internet provider changes your IP address, the store owner can update the list.</p>
HTML],

 ['audit', 'Audit and monitoring', <<<HTML
  <ul>
   <li>The activity report records logins with IP address, and changes to bills and records.</li>
   <li>Review it after a stock or cash mismatch, a disputed bill, or a change of staff.</li>
   <li>We monitor our systems for unusual activity and errors [confirm tools and alerting].</li>
  </ul>
HTML],

 ['protection', 'Protecting stored data', <<<HTML
  <ul>
   <li>The app is used in your browser over HTTPS, so data is encrypted between your counter and our servers.</li>
   <li>Data is encrypted at rest [confirm with engineering, including encryption of backups].</li>
   <li>Each store’s data is kept logically separate from other customers’ data [confirm].</li>
   <li>Card, UPI and bank details are entered on our payment gateway’s pages and are not stored by us [confirm].</li>
  </ul>
HTML],

 ['infra', 'Hosting and backups', <<<HTML
  <ul>
   <li>Hosting: [Hosting provider and region(s), and whether customers can choose a region.]</li>
   <li>Backups: [frequency, how long backups are kept, and whether they are stored in a separate location].</li>
   <li>Recovery: [how restores are tested, and target recovery time].</li>
   <li>Availability: [any uptime commitment, if one will be offered].</li>
  </ul>
  <p>We do not claim certifications we do not hold. [List any certification or audit, for example ISO 27001, only once it is in place.]</p>
HTML],

 ['appsec', 'Application security', <<<HTML
  <ul>
   <li>Updates are released centrally, so every store gets security fixes without installing anything.</li>
   <li>[Confirm: code review, dependency updates, vulnerability scanning, and how often penetration tests are done and by whom.]</li>
  </ul>
HTML],

 ['patients', 'Patient data and ABDM consent', <<<HTML
  <ul>
   <li>Patient details you record (name, phone, address, UHID, doctor, dues, deposits and medicine reminders) are used only to provide the service to your pharmacy.</li>
   <li>An ABHA is created or linked only after the patient confirms with an OTP through the ABDM flow. The patient can manage or withdraw consent through ABDM.</li>
   <li>Prescriptions from a connected hospital EMR arrive only at the pharmacy linked to that hospital [confirm].</li>
   <li>See our <a href="{$u_priv}">privacy notice</a> for how patient data is handled.</li>
  </ul>
HTML],

 ['incidents', 'Incident response and breach notification', <<<HTML
  <p>Security-incident handling follows the notification duties of the laws that apply in each market where we operate and where your customers are. These may include:</p>
  <ul>
   <li><b>GDPR and UK GDPR.</b> May include notifying the supervisory authority within 72 hours of becoming aware of a personal data breach, and affected people where the risk is high.</li>
   <li><b>US state breach laws.</b> May include notifying affected residents and, in some states, a state regulator.</li>
   <li><b>India (CERT-In and DPDP Act).</b> May include reporting certain incidents to CERT-In and personal data breaches to the Data Protection Board of India and affected people.</li>
  </ul>
  <p>[Counsel to confirm which duties apply and our reporting timelines.]</p>
  <p>Our process: [confirm with engineering and counsel: who leads incident response, how incidents are contained and investigated, how and how quickly affected stores are told, and how we support stores with their own notification duties for patient data].</p>
  <p>If you notice something wrong, such as logins you don’t recognise, call support on {$support} straight away.</p>
HTML],

 ['disclosure', 'Reporting a security issue', <<<HTML
  <p>If you find a security weakness, please email [security contact email] with the steps to reproduce it. We ask that you:</p>
  <ul>
   <li>Do not access, change or delete other customers’ data. Use only your own test account.</li>
   <li>Do not disrupt the service, for example with load or denial-of-service testing.</li>
   <li>Give us reasonable time to fix the issue before sharing it publicly.</li>
  </ul>
  <p>We will acknowledge your report within [number] working days and keep you updated. [Confirm whether reporters acting in good faith will be thanked or rewarded.]</p>
HTML],

 ['yours', 'What you can do', <<<HTML
  <ul>
   <li>Give every staff member their own login, and never share passwords or OTPs.</li>
   <li>Use strong passwords, and turn on two-step login if your plan includes it.</li>
   <li>Remove leavers and use force logout on the same day.</li>
   <li>Restrict logins to your store network with IP whitelisting where possible.</li>
   <li>Keep the counter computer’s browser and operating system updated, and lock the screen when away.</li>
   <li>Check the activity report regularly.</li>
   <li>Export reports regularly, and before closing your account.</li>
  </ul>
HTML],

 ['export', 'Your data stays yours', <<<HTML
  <p>Reports, item lists and ledgers can be exported to Excel or CSV at any time. Tally XML export is available on Enterprise. [Confirm the full-account export process.] Our <a href="{$u_terms}">terms of service</a> explain what happens to your data when you leave.</p>
HTML],
];
$legal_notice = '<b>Draft for review.</b> The controls in the first table are confirmed in the app. Items in [brackets] need confirmation from the engineering team before publishing.';
?>
<main id="main">
<?php require __DIR__ . '/partials/legal-layout.php'; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
