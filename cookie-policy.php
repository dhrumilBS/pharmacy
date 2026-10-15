<?php
require_once __DIR__ . '/partials/config.php';
$title       = page_title('Cookie policy');
$description = 'What this website and our pharmacy software store in your browser, why, and how to control it. No analytics or advertising cookies are used today.';
$page_key    = 'cookies';
$noindex     = true; // draft until legal sign-off
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
$hero = ['crumb' => 'Cookies', 'parent' => 'Legal', 'h1' => 'Cookie policy',
  'lead' => 'What this website and the pharmacy app store in your browser, and how you can control it.',
  'meta' => 'Effective date: [date to be set on approval] · Last updated: [date]'];

$company = e(SITE['company']);
$address = e(SITE['address']);
$u_priv  = url('privacy');

$sections = [
 ['summary', 'In short', <<<HTML
  <ul>
   <li>This website does not set analytics, advertising or social media cookies today [confirm before launch].</li>
   <li>Its fonts are hosted on this website, so loading them does not send your details to a third party.</li>
   <li>It does not currently use your browser’s local storage or session storage either [confirm before launch].</li>
   <li>If we add analytics or marketing tools later, we will ask for your consent first where the law requires it, and update this page.</li>
  </ul>
HTML],

 ['what', 'What cookies and local storage are', <<<HTML
  <p><b>Cookies</b> are small text files a website asks your browser to keep, so it can recognise the browser on a later visit. <b>First-party</b> cookies are set by the site you are on; <b>third-party</b> cookies are set by another service the page uses.</p>
  <p><b>Local storage</b> and <b>session storage</b> do a similar job: they let a page save small pieces of information in your browser. Local storage stays until it is cleared; session storage ends when you close the tab. We treat these technologies the same way as cookies in this policy.</p>
HTML],

 ['website', 'What this website uses today', <<<HTML
  <table><thead><tr><th>Name or service</th><th>Type</th><th>Purpose</th><th>How long</th></tr></thead><tbody>
   <tr><td>None set today</td><td>—</td><td>This website does not set cookies or use local storage at the moment. Fonts and images are served from this website itself.</td><td>—</td></tr>
  </tbody></table>
  <p>The demo and contact forms do not set cookies. [Update this section if the forms start using spam protection such as a CAPTCHA, which may set its own cookies.]</p>
HTML],

 ['categories', 'Categories we may use', <<<HTML
  <table><thead><tr><th>Category</th><th>What it does</th><th>Used now?</th><th>Consent needed?</th></tr></thead><tbody>
   <tr><td>Strictly necessary</td><td>Keeps you signed in, protects forms from misuse, remembers your cookie choices</td><td>Not on this website; in the app to keep you signed in [confirm]</td><td>No, because the service cannot work without them</td></tr>
   <tr><td>Preferences</td><td>Remembers choices such as layout or language</td><td>Local storage for the review tool only</td><td>[Confirm with counsel]</td></tr>
   <tr><td>Analytics</td><td>Counts visits and shows which pages are useful</td><td>No [confirm before launch]</td><td>Where the law requires it, we will ask first</td></tr>
   <tr><td>Marketing</td><td>Measures ads or shows you our ads on other sites</td><td>No</td><td>Where the law requires it, we will ask first</td></tr>
  </tbody></table>
HTML],

 ['app', 'Cookies in the pharmacy app', <<<HTML
  <p>When you sign in to the pharmacy app, it uses strictly necessary storage to keep you signed in, remember your session securely, and support security features such as two-step login and force logout. [Confirm the exact cookies or storage keys the app sets, their duration, and whether any third-party scripts run inside the app.]</p>
  <p>These items are needed for the app to work, so they cannot be switched off while you use it. They are not used for advertising.</p>
HTML],

 ['control', 'How to control cookies', <<<HTML
  <ul>
   <li><b>Browser settings.</b> Every major browser lets you see, block and delete cookies and site data, usually under Settings, then Privacy or Site settings.</li>
   <li><b>Clear local storage.</b> Clearing "site data" or "cookies and other site data" for this website also removes the design-review setting.</li>
   <li><b>Private windows.</b> Storage is deleted when you close a private or incognito window.</li>
   <li><b>Blocking all cookies</b> will not affect this website today, but it will stop you signing in to the app.</li>
  </ul>
HTML],

 ['consent', 'Our approach to consent', <<<HTML
  <p>Some countries, for example those in the EEA and the UK, require your consent before non-essential cookies are set. Where consent is required by law, we will add a consent banner before we add any analytics or marketing tool. It will let you accept or reject each optional category, with rejecting as easy as accepting. [Counsel to confirm which regimes apply and where a banner is required.] Optional cookies will not load until you agree, and you will be able to change your choice at any time from a link in the footer. [Confirm banner design and tool before launch.]</p>
HTML],

 ['changes', 'Changes to this policy', <<<HTML
  <p>We will update this page, and the table above, whenever we add or remove a cookie or tracking tool. The date at the top shows the latest version.</p>
HTML],

 ['contact', 'Contact', <<<HTML
  <p>Questions about cookies: [privacy contact email]. {$company}, {$address}.</p>
  <p>Our <a href="{$u_priv}">privacy notice</a> explains how we handle personal data more generally.</p>
HTML],
];
?>
<main id="main">
<?php require __DIR__ . '/partials/legal-layout.php'; ?>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>
