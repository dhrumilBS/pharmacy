<?php
/**
 * Import data: the Cookie policy page (legal template).
 *
 * Source in the PHP_Site: cookie-policy.php ($hero, $sections; it uses the
 * default draft notice of partials/legal-layout.php, so notice_html is
 * empty). Copied exactly, curly quotes and dashes included.
 *
 * Live values: {$company} and {$address} are [rx_site] shortcodes; url()
 * links are [rx_link]. The bracketed legal placeholders stay literal:
 * templates/legal.php runs rx_legal_mark() after the shortcodes, so each
 * one (and the company placeholder from Site_Settings) is wrapped in
 * <span class="tbc" data-confirm> as today.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'cookie-policy.php',
	'key'      => 'cookies',
	'title'    => 'Cookies',
	'slug'     => 'cookie-policy',
	'template' => 'templates/legal.php',
	'seo'      => array(
		'title'   => 'Cookie policy %%sep%% %%sitename%%',
		'desc'    => 'What this website and our pharmacy software store in your browser, why, and how to control it. No analytics or advertising cookies are used today.',
		'noindex' => true, // Draft until legal sign-off.
	),
	'fields'   => array(
		'hero'        => array(
			'crumb'   => 'Cookies',
			'parent'  => 'Legal',
			'eyebrow' => '',
			'h1'      => 'Cookie policy',
			'lead'    => 'What this website and the pharmacy app store in your browser, and how you can control it.',
			'meta'    => 'Effective date: [date to be set on approval] · Last updated: [date]',
			'pattern' => 'inherit',
		),
		'notice_html' => '',
		'sections'    => array(
			array(
				'anchor'    => 'summary',
				'heading'   => 'In short',
				'body_html' => <<<'HTML'
  <ul>
   <li>This website does not set analytics, advertising or social media cookies today [confirm before launch].</li>
   <li>Its fonts are hosted on this website, so loading them does not send your details to a third party.</li>
   <li>It does not currently use your browser’s local storage or session storage either [confirm before launch].</li>
   <li>If we add analytics or marketing tools later, we will ask for your consent first where the law requires it, and update this page.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'what',
				'heading'   => 'What cookies and local storage are',
				'body_html' => <<<'HTML'
  <p><b>Cookies</b> are small text files a website asks your browser to keep, so it can recognise the browser on a later visit. <b>First-party</b> cookies are set by the site you are on; <b>third-party</b> cookies are set by another service the page uses.</p>
  <p><b>Local storage</b> and <b>session storage</b> do a similar job: they let a page save small pieces of information in your browser. Local storage stays until it is cleared; session storage ends when you close the tab. We treat these technologies the same way as cookies in this policy.</p>
HTML,
			),
			array(
				'anchor'    => 'website',
				'heading'   => 'What this website uses today',
				'body_html' => <<<'HTML'
  <table><thead><tr><th>Name or service</th><th>Type</th><th>Purpose</th><th>How long</th></tr></thead><tbody>
   <tr><td>None set today</td><td>—</td><td>This website does not set cookies or use local storage at the moment. Fonts and images are served from this website itself.</td><td>—</td></tr>
  </tbody></table>
  <p>The demo and contact forms do not set cookies. [Update this section if the forms start using spam protection such as a CAPTCHA, which may set its own cookies.]</p>
HTML,
			),
			array(
				'anchor'    => 'categories',
				'heading'   => 'Categories we may use',
				'body_html' => <<<'HTML'
  <table><thead><tr><th>Category</th><th>What it does</th><th>Used now?</th><th>Consent needed?</th></tr></thead><tbody>
   <tr><td>Strictly necessary</td><td>Keeps you signed in, protects forms from misuse, remembers your cookie choices</td><td>Not on this website; in the app to keep you signed in [confirm]</td><td>No, because the service cannot work without them</td></tr>
   <tr><td>Preferences</td><td>Remembers choices such as layout or language</td><td>Local storage for the review tool only</td><td>[Confirm with counsel]</td></tr>
   <tr><td>Analytics</td><td>Counts visits and shows which pages are useful</td><td>No [confirm before launch]</td><td>Where the law requires it, we will ask first</td></tr>
   <tr><td>Marketing</td><td>Measures ads or shows you our ads on other sites</td><td>No</td><td>Where the law requires it, we will ask first</td></tr>
  </tbody></table>
HTML,
			),
			array(
				'anchor'    => 'app',
				'heading'   => 'Cookies in the pharmacy app',
				'body_html' => <<<'HTML'
  <p>When you sign in to the pharmacy app, it uses strictly necessary storage to keep you signed in, remember your session securely, and support security features such as two-step login and force logout. [Confirm the exact cookies or storage keys the app sets, their duration, and whether any third-party scripts run inside the app.]</p>
  <p>These items are needed for the app to work, so they cannot be switched off while you use it. They are not used for advertising.</p>
HTML,
			),
			array(
				'anchor'    => 'control',
				'heading'   => 'How to control cookies',
				'body_html' => <<<'HTML'
  <ul>
   <li><b>Browser settings.</b> Every major browser lets you see, block and delete cookies and site data, usually under Settings, then Privacy or Site settings.</li>
   <li><b>Clear local storage.</b> Clearing "site data" or "cookies and other site data" for this website also removes the design-review setting.</li>
   <li><b>Private windows.</b> Storage is deleted when you close a private or incognito window.</li>
   <li><b>Blocking all cookies</b> will not affect this website today, but it will stop you signing in to the app.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'consent',
				'heading'   => 'Our approach to consent',
				'body_html' => <<<'HTML'
  <p>Some countries, for example those in the EEA and the UK, require your consent before non-essential cookies are set. Where consent is required by law, we will add a consent banner before we add any analytics or marketing tool. It will let you accept or reject each optional category, with rejecting as easy as accepting. [Counsel to confirm which regimes apply and where a banner is required.] Optional cookies will not load until you agree, and you will be able to change your choice at any time from a link in the footer. [Confirm banner design and tool before launch.]</p>
HTML,
			),
			array(
				'anchor'    => 'changes',
				'heading'   => 'Changes to this policy',
				'body_html' => <<<'HTML'
  <p>We will update this page, and the table above, whenever we add or remove a cookie or tracking tool. The date at the top shows the latest version.</p>
HTML,
			),
			array(
				'anchor'    => 'contact',
				'heading'   => 'Contact',
				'body_html' => <<<'HTML'
  <p>Questions about cookies: [privacy contact email]. [rx_site key="company"], [rx_site key="address"].</p>
  <p>Our [rx_link key="privacy"]privacy notice[/rx_link] explains how we handle personal data more generally.</p>
HTML,
			),
		),
	),
);
