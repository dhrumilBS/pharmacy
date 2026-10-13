<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Privacy notice');
$description = 'How our pharmacy software collects, uses, shares and protects store, staff, patient and payment information, and the rights you have under Indian law.';
$page_key    = 'privacy';
$noindex     = true; // draft until legal sign-off
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Privacy', 'parent' => 'Legal', 'h1' => 'Privacy notice',
  'lead' => 'How our pharmacy software handles information about your store, your staff and your patients, and what you can ask us to do with it.',
  'meta' => 'Effective date: [date to be set on approval] · Last updated: [date]'];

$company  = e(SITE['company']);
$address  = e(SITE['address']);
$sales    = e(SITE['sales_phone']);
$support  = e(SITE['support_phone']);
$u_sec    = url('data-security');
$u_cookie = url('cookies');
$u_terms  = url('terms');

$sections = [
 ['summary', 'In short', <<<HTML
  <ul>
   <li>We collect what we need to run your pharmacy account, bill your subscription and answer your enquiries.</li>
   <li>Patient records you enter belong to your pharmacy. We store and process them only so the service works for you.</li>
   <li>We do not sell or rent personal information, and we do not use patient records for advertising.</li>
   <li>You can see, correct and export your records, and you can contact our Grievance Officer if something goes wrong.</li>
  </ul>
  <p>The rest of this notice explains each point in detail.</p>
HTML],

 ['who', 'Who we are', <<<HTML
  <p>The pharmacy software and this website are provided by <b>{$company}</b>, {$address} ("we", "us", "our").</p>
  <p>This notice is an electronic record under the Information Technology Act, 2000 and the rules made under it. It is published to meet the disclosure requirements of the Information Technology (Reasonable Security Practices and Procedures and Sensitive Personal Data or Information) Rules, 2011 ("SPDI Rules") and to give the notice required by the Digital Personal Data Protection Act, 2023 ("DPDP Act") and its rules, as they apply to us.</p>
HTML],

 ['scope', 'What this notice covers', <<<HTML
  <p>This notice applies to:</p>
  <ul>
   <li><b>The pharmacy web app</b>: the browser-based software used for billing, stock, purchase, reports and patient records.</li>
   <li><b>This website</b>: the pages you are reading now, including the demo and contact forms.</li>
   <li><b>Support and sales contact</b>: calls, WhatsApp messages and emails with our team.</li>
  </ul>
  <p>It does not cover websites or services run by others, even if we link to them, such as a payment gateway page, WhatsApp, or a hospital system connected to your pharmacy. Their own privacy notices apply.</p>
  <p>[Confirm whether a company-wide privacy policy also applies, and which document prevails if they differ.]</p>
HTML],

 ['collect', 'Information we collect', <<<HTML
  <table><thead><tr><th>Category</th><th>Examples</th><th>Where it comes from</th></tr></thead><tbody>
  <tr><td>Store account</td><td>Store name and address, user names, email addresses, passwords (stored in a protected, non-readable form [confirm hashing]), one-time passwords for sign-up and two-step login</td><td>You, when you register or add users</td></tr>
  <tr><td>Business records</td><td>Items, batches, expiry dates, stock, suppliers and distributors, purchase and sales bills, ledgers, expenses, GST data, drug-licence and GST numbers you enter</td><td>You and your staff, or files you import (CSV, OCR of distributor bills)</td></tr>
  <tr><td>Patient and customer records</td><td>Name, phone number, address, UHID, doctor name, purchase history, dues and deposits, medicine reminders; ABHA details where you link them; prescriptions received from a connected hospital EMR</td><td>Your pharmacy, a connected hospital system, or ABDM with the patient’s consent</td></tr>
  <tr><td>Usage and security data</td><td>Login times, IP address (used for IP whitelisting and the activity report), records of who created, edited or deleted entries, device and browser type, error logs</td><td>Automatically, when the app is used</td></tr>
  <tr><td>Messages you send</td><td>Recipient phone number or email, bill or report content, delivery status, credits used</td><td>Created when you share a bill, send a reminder or receive the daily WhatsApp report</td></tr>
  <tr><td>Payments</td><td>Plan, term, invoices, GSTIN for invoicing, payment status and reference. Card, UPI and bank details are entered on our payment gateway’s pages and are not stored by us [confirm]</td><td>You and our payment gateway</td></tr>
  <tr><td>Website enquiries</td><td>Name, mobile number, store name, city, current software and what you would like (demo, trial, quote or checklist)</td><td>Forms on this website [the form currently sends nothing; update this row when it goes live]</td></tr>
  <tr><td>Support conversations</td><td>Call notes, WhatsApp and email messages, screenshots you share</td><td>You, when you contact support</td></tr>
  </tbody></table>
  <p><b>Sensitive information.</b> Passwords, financial information, and health information such as prescriptions and medicine history are treated as sensitive personal data under the SPDI Rules. We apply the stricter handling described in this notice to them.</p>
  <p><b>What we do not ask for.</b> We do not need Aadhaar numbers, card numbers or bank passwords to run the service. Please do not enter them in free-text fields.</p>
HTML],

 ['roles', 'Your pharmacy’s role and ours', <<<HTML
  <p>Two kinds of personal data pass through the service, and responsibility differs:</p>
  <table><thead><tr><th>Data</th><th>Who decides why and how it is used</th><th>Our role</th></tr></thead><tbody>
   <tr><td>Your account, staff logins, billing and enquiries</td><td>Us</td><td>Data fiduciary: we are responsible to you directly under this notice</td></tr>
   <tr><td>Patient and customer records your pharmacy enters or receives</td><td>Your pharmacy</td><td>Data processor: we process it only on your pharmacy’s instructions, to run the service</td></tr>
  </tbody></table>
  <p>For patient records, your pharmacy is responsible for telling patients what it collects, having a valid reason or consent, and answering their requests. We help your pharmacy do this. [Confirm the data fiduciary / data processor roles with counsel under the DPDP Act, 2023 and its rules, and whether a separate data processing agreement will be offered.]</p>
HTML],

 ['purposes', 'How we use information, and why', <<<HTML
  <table><thead><tr><th>Purpose</th><th>Basis under Indian law</th></tr></thead><tbody>
   <tr><td>Create and run your account: billing, stock, purchase, reports, and the features of your plan</td><td>Your consent and the contract you accept when you sign up</td></tr>
   <tr><td>Send messages you trigger: bills and daily reports on WhatsApp, SMS or email, and medicine reminders</td><td>Your instruction (for patient messages, your pharmacy’s basis)</td></tr>
   <tr><td>Keep accounts secure: OTP login, IP whitelisting, activity logs, fraud and misuse prevention</td><td>Legitimate use for the service you asked for, and our legal security duties</td></tr>
   <tr><td>Answer support requests and fix problems</td><td>Your consent, given when you contact us</td></tr>
   <tr><td>Bill your subscription, issue GST invoices and keep accounts</td><td>Legal obligations under tax and company law</td></tr>
   <tr><td>Reply to website enquiries and arrange demos</td><td>Your consent, given when you submit the form</td></tr>
   <tr><td>Tell you about product updates, offers and events</td><td>Your separate consent, which you can withdraw at any time [confirm whether marketing messages will be sent]</td></tr>
   <tr><td>Improve the product using aggregated usage patterns that do not identify patients</td><td>[Confirm basis with counsel]</td></tr>
   <tr><td>Respond to lawful requests from courts, regulators or police</td><td>Legal obligation</td></tr>
  </tbody></table>
  <ul>
   <li>We do not sell or rent personal information.</li>
   <li>We do not use patient records to market our own products, and we do not show advertising in the app.</li>
   <li>We do not make decisions about people using only automated processing or profiling.</li>
  </ul>
HTML],

 ['sharing', 'Who we share it with', <<<HTML
  <p>We share personal information only as needed for the purposes above:</p>
  <ul>
   <li><b>Service providers</b> who help us run the service, under written contracts that limit their use of the data to our instructions. These include hosting, email and SMS delivery, WhatsApp messaging, OCR processing and our payment gateway. [Publish the list of sub-processors, what each one does and where it processes data.]</li>
   <li><b>A connected hospital system</b> that your pharmacy chooses to use, for example when EMR prescriptions arrive at your counter.</li>
   <li><b>ABDM</b> (Ayushman Bharat Digital Mission), only when a patient’s ABHA is created or linked with their OTP consent.</li>
   <li><b>Your accountant’s software</b>, only when you export data yourself, for example Tally XML on Enterprise.</li>
   <li><b>Authorities</b>, when the law requires it or to protect the safety of people or the service.</li>
   <li><b>A buyer or successor</b>, if our business is merged or sold. This notice, or one at least as protective, will continue to apply, and we will tell you before it changes.</li>
  </ul>
HTML],

 ['transfers', 'Where information is stored', <<<HTML
  <p>Your data is stored at [hosting location, for example data centres in India]. Some service providers, such as email or messaging platforms, may process data outside India. Where that happens, we transfer data only to countries not restricted by the Government of India under the DPDP Act, and under contracts that require equal protection. [Confirm hosting region and any cross-border processing.]</p>
HTML],

 ['retention', 'How long we keep it', <<<HTML
  <table><thead><tr><th>Data</th><th>How long</th></tr></thead><tbody>
   <tr><td>Store account and business records</td><td>While your account is active, and [period] after it closes so you can export them, unless you ask us to delete them sooner</td></tr>
   <tr><td>Patient records</td><td>As instructed by your pharmacy, and deleted or returned when your account closes, after [period]</td></tr>
   <tr><td>Security and activity logs</td><td>[period; note that CERT-In directions require some system logs to be kept for 180 days in India]</td></tr>
   <tr><td>Invoices and payment records</td><td>For the period required by tax and accounting law [confirm, for example 8 years]</td></tr>
   <tr><td>Website enquiries</td><td>[period, for example 12 months after the last contact] unless you become a customer</td></tr>
   <tr><td>Backups</td><td>Deleted on a rolling cycle of [period]</td></tr>
  </tbody></table>
  <p>Your pharmacy may have its own duty to keep records for longer, for example under drug or GST law. Export a copy before you close your account.</p>
HTML],

 ['security', 'How we protect it', <<<HTML
  <p>We use reasonable security practices proportionate to the sensitivity of pharmacy data, in line with the SPDI Rules. These include:</p>
  <ul>
   <li>Access over HTTPS, and encryption of data at rest [confirm with engineering].</li>
   <li>Individual logins, roles and privileges, force logout, two-step OTP login and IP whitelisting.</li>
   <li>An activity report showing who did what, and from which IP address.</li>
   <li>Limited staff access to customer data, only for support and on a need-to-know basis [confirm].</li>
  </ul>
  <p>No system is completely secure. If a breach affects your personal data, we will tell you and the authorities as the law requires. See <a href="{$u_sec}">Data &amp; security</a> for details.</p>
HTML],

 ['children', 'Children’s data', <<<HTML
  <p>The service is for businesses and is not meant for use by anyone under 18. A pharmacy may record a minor as a patient, for example when a parent buys medicine for a child. In that case the pharmacy is responsible for obtaining any consent the law requires from the parent or guardian. We do not use children’s data for tracking, behavioural monitoring or targeted advertising.</p>
HTML],

 ['abdm', 'ABHA and ABDM linking', <<<HTML
  <ul>
   <li>An ABHA (Ayushman Bharat Health Account) is created or linked only after the patient confirms with an OTP sent to them.</li>
   <li>The data shared through ABDM is limited to what the patient has consented to.</li>
   <li>The patient can manage or withdraw their consent through ABDM’s own apps and processes. Withdrawal does not undo records already lawfully shared.</li>
   <li>ABDM’s own health data management policy also applies to these exchanges.</li>
  </ul>
HTML],

 ['rights', 'Your rights', <<<HTML
  <p>Under the DPDP Act and SPDI Rules, you have the right to:</p>
  <ul>
   <li>Get a summary of the personal data we process about you and who we have shared it with.</li>
   <li>Have inaccurate or incomplete data corrected or updated.</li>
   <li>Have your data erased when it is no longer needed, unless the law requires us to keep it.</li>
   <li>Withdraw consent at any time. This affects future processing only, and some features may stop working.</li>
   <li>Have a grievance addressed by us, and then escalate it to the Data Protection Board of India.</li>
   <li>Nominate someone to act for you if you die or become unable to act.</li>
  </ul>
  <h3>How to make a request</h3>
  <ul>
   <li><b>Store owners and staff</b>: update most account and business details directly in the app, and export reports to Excel or CSV. For anything else, email [privacy contact email] from your registered email address.</li>
   <li><b>Patients</b>: please contact the pharmacy that holds your record first, because the pharmacy decides how it is used. If you contact us, we will pass your request to the pharmacy and help it respond.</li>
  </ul>
  <p>We may need to verify your identity before acting on a request. We reply within [number] days [confirm against the timelines in the DPDP Rules].</p>
HTML],

 ['grievance', 'Grievance Officer', <<<HTML
  <p>If you have a concern about how we handle personal data, contact our Grievance Officer:</p>
  <ul>
   <li>Name and designation: [name, designation]</li>
   <li>Email: [grievance email]</li>
   <li>Post: {$company}, {$address}</li>
   <li>Hours: [working days and hours]</li>
  </ul>
  <p>We acknowledge complaints within [number] working days and aim to resolve them within one month, as the SPDI Rules require, or sooner where the DPDP Rules set a shorter period [confirm timelines with counsel]. If you are not satisfied with our response, you may complain to the Data Protection Board of India once that route is available to you.</p>
HTML],

 ['cookies', 'Cookies', <<<HTML
  <p>This website does not currently set analytics or advertising cookies. Read our <a href="{$u_cookie}">Cookie policy</a> for what the site and the app store in your browser.</p>
HTML],

 ['links', 'Links to other services', <<<HTML
  <p>The website and app link to services we do not control, such as WhatsApp, our payment gateway and ABDM. Their privacy notices apply when you use them.</p>
HTML],

 ['changes', 'Changes to this notice', <<<HTML
  <p>We will update this notice when our practices or the law change, and show the new date at the top. If a change materially affects how we use your data, we will tell account owners in the app or by email before it takes effect, and ask for fresh consent where the law requires it.</p>
HTML],

 ['contact', 'Contact us', <<<HTML
  <p>Privacy questions: [privacy contact email]. Sales: {$sales}. Support: {$support}.</p>
  <p>{$company}, {$address}.</p>
  <p>This notice should be read with our <a href="{$u_terms}">Terms of service</a>.</p>
HTML],
];
?>
<main id="main">
<?php require __DIR__ . '/partials/legal-layout.php'; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
