<?php
/**
 * Import data: the Terms page (legal template).
 *
 * Source in the PHP_Site: terms.php ($hero, $sections; it uses the default
 * draft notice of partials/legal-layout.php, so notice_html is empty).
 * Copied exactly, curly quotes included. The plan table is literal text
 * on the PHP page (no pricing helper), so it stays literal here.
 *
 * Live values: {$company}, {$address}, {$sales} and {$support} are
 * [rx_site] shortcodes; url() links are [rx_link]. The bracketed legal
 * placeholders stay literal: templates/legal.php runs rx_legal_mark() after
 * the shortcodes, so each one (and the company placeholder from
 * Site_Settings) is wrapped in <span class="tbc" data-confirm> as today.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source'   => 'terms.php',
	'key'      => 'terms',
	'title'    => 'Terms',
	'slug'     => 'terms',
	'template' => 'templates/legal.php',
	'seo'      => array(
		'title'   => 'Terms of service %%sep%% %%sitename%%',
		'desc'    => 'Terms for using our pharmacy software: accounts, plans and billing, acceptable use, your data, your regulatory responsibilities, liability and disputes.',
		'noindex' => true, // Draft until legal sign-off.
	),
	'fields'   => array(
		'hero'        => array(
			'crumb'   => 'Terms',
			'parent'  => 'Legal',
			'eyebrow' => '',
			'h1'      => 'Terms of service',
			'lead'    => 'The agreement between your pharmacy and us for using the pharmacy software.',
			'meta'    => 'Effective date: [date to be set on approval] · Last updated: [date]',
			'pattern' => 'inherit',
		),
		'notice_html' => '',
		'sections'    => array(
			array(
				'anchor'    => 'agreement',
				'heading'   => 'The agreement',
				'body_html' => <<<'HTML'
  <p>These terms are an agreement between <b>[rx_site key="company"]</b>, [rx_site key="address"] ("we", "us") and the business that creates an account or signs an order form ("you"). The person who accepts them confirms that they are at least 18 and authorised to bind that business.</p>
  <p>You accept these terms by creating an account, ticking the acceptance box at sign-up, paying for a plan, or using the service. If you do not agree, please do not use the service.</p>
  <p>These terms are an electronic agreement and do not need a physical signature [Counsel to confirm electronic acceptance wording for each market]. If you sign a separate order form or Enterprise agreement with us, that document prevails where it conflicts with these terms.</p>
HTML,
			),
			array(
				'anchor'    => 'definitions',
				'heading'   => 'Words we use',
				'body_html' => <<<'HTML'
  <ul>
   <li><b>Service</b>: the browser-based pharmacy software, its features and updates, and related support.</li>
   <li><b>Users</b>: you and the staff you add, each with their own login.</li>
   <li><b>Customer data</b>: everything you or your users enter, import or receive in the service, including business records and patient records.</li>
   <li><b>Plan</b>: the subscription you choose, with its price, user limit, features and credits.</li>
   <li><b>Credits</b>: prepaid units used for WhatsApp, SMS and email messages and for OCR imports.</li>
   <li><b>Term</b>: the 1-, 3- or 5-year period you pay for.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'accounts',
				'heading'   => 'Accounts and users',
				'body_html' => <<<'HTML'
  <ul>
   <li>Give accurate store details at sign-up (store name and address, user name, email) and keep them up to date. We verify your email with a one-time password.</li>
   <li>Each user must have their own login. Do not share passwords or OTPs.</li>
   <li>You control who has access. Use roles and privileges, remove staff who leave, and use force logout if a device is lost.</li>
   <li>You are responsible for everything done under your users’ logins, unless it results from a failure of our security.</li>
   <li>Tell us promptly at [rx_site key="support_phone"] if you suspect unauthorised access.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'plans',
				'heading'   => 'Plans, prices and credits',
				'body_html' => <<<'HTML'
  <table><thead><tr><th>Plan</th><th>Price (excl. taxes)</th><th>Users</th><th>Bill limits</th></tr></thead><tbody>
   <tr><td>Starter</td><td>Free</td><td>1</td><td>10 sales bills and 25 purchase bills a month</td></tr>
   <tr><td>Basic</td><td>Yearly price on the [rx_link key="pricing"]pricing page[/rx_link]</td><td>3</td><td>Unlimited</td></tr>
   <tr><td>Advance</td><td>Yearly price on the [rx_link key="pricing"]pricing page[/rx_link]</td><td>10</td><td>Unlimited</td></tr>
   <tr><td>Enterprise</td><td>Custom quote</td><td>As agreed</td><td>Unlimited, multi-store</td></tr>
  </tbody></table>
  <ul>
   <li>Prices are shown in the currency displayed on the pricing page or at checkout and exclude applicable taxes (such as GST, VAT or sales tax), which are added to your invoice where required. [Confirm currencies offered and tax handling per market.]</li>
   <li>Paid plans are bought for a 1-, 3- or 5-year term and paid in advance.</li>
   <li>The features in each plan are those shown on the [rx_link key="pricing"]pricing page[/rx_link] and in the app when you subscribe. Tally XML export is part of Enterprise.</li>
   <li>Messages and OCR imports use credits. Included credits vary by plan, and you can buy more as add-ons. [Confirm whether unused credits expire or carry over.]</li>
   <li>We may change prices for future terms. We will tell you at least [number] days before a new price applies to your renewal. Prices for a term you have already paid for will not change.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'billing',
				'heading'   => 'Payment, renewal and plan changes',
				'body_html' => <<<'HTML'
  <ul>
   <li>Payments are made online through our payment gateway. Its own terms apply to the payment itself.</li>
   <li>We issue an invoice for every payment. Give us your tax registration details before payment if you want them shown on the invoice, where your local tax rules allow you to claim tax credit [confirm per market].</li>
   <li>You can switch plans or cancel at any time. [Confirm how upgrades are charged, for example pro-rated for the rest of the term, and how downgrades take effect.]</li>
   <li>Renewal: [confirm whether plans renew automatically or must be renewed manually, and what reminders we send].</li>
   <li>If a paid plan ends without renewal, [confirm what happens, for example a move to Starter limits with records kept read-only for a set period].</li>
   <li>Refunds are covered by the [rx_link key="refund"]refund policy[/rx_link].</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'free',
				'heading'   => 'Free plan and trials',
				'body_html' => <<<'HTML'
  <p>The Starter plan is free, with the limits shown above. Any free trial of a paid plan lasts for the period shown when you start it [confirm trial length and whether a card is needed]. We may change or end the free plan or trials with [number] days’ notice. If we do, you will be able to export your data or move to a paid plan.</p>
HTML,
			),
			array(
				'anchor'    => 'use',
				'heading'   => 'Acceptable use',
				'body_html' => <<<'HTML'
  <p>Use the service only for the lawful business of a pharmacy, clinic, hospital or similar healthcare provider. You must not:</p>
  <ul>
   <li>Use it to sell or record medicines in breach of the medicines laws that apply to you, or to keep false bills or records.</li>
   <li>Send messages that the recipient has not agreed to receive, or that break the telecom, marketing and messaging rules that apply in your country or the messaging provider’s policies.</li>
   <li>Try to access other customers’ data, test our security without permission, or bypass plan limits.</li>
   <li>Copy, resell, frame or reverse-engineer the software, or use automated tools that overload it.</li>
   <li>Upload malware, or content you have no right to use.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'data',
				'heading'   => 'Your data',
				'body_html' => <<<'HTML'
  <ul>
   <li><b>You own your customer data.</b> We get only the rights needed to host, process, back up and display it so the service works for you, and to provide support you ask for.</li>
   <li>We handle personal data as described in our [rx_link key="privacy"]privacy notice[/rx_link]. For patient records, we process data on your behalf and on your instructions.</li>
   <li>You are responsible for having the right to enter patient data, for telling patients how it is used, and for getting any consent the law requires, including for reminders and messages.</li>
   <li>We may use aggregated statistics that do not identify you, your patients or your store to run and improve the service [confirm].</li>
   <li>You can export reports, item lists and ledgers to Excel or CSV at any time.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'responsibilities',
				'heading'   => 'Your professional and regulatory responsibilities',
				'body_html' => <<<'HTML'
  <p>The software helps you keep records. It does not replace a registered pharmacist’s judgement, and it does not guarantee that your business meets the law. You remain responsible for:</p>
  <ul>
   <li>Dispensing decisions, checking prescriptions, and the medicines you sell.</li>
   <li>Complying with the pharmacy, medicines, tax and record-keeping laws that apply to your business in your country, including your licences and their conditions and any controlled-substance or prescription-only record duties.</li>
   <li>Your tax registration, the tax codes and rates you apply, your invoices and returns, and e-invoicing where required.</li>
   <li>Clinical decisions. The software gives no clinical advice; anything it shows, such as alerts or tags on items, supports your own checks and does not replace them.</li>
   <li>Checking reports before you file or rely on them. Features such as FEFO batch selection, expiry alerts and tax reports depend on the data you enter. The software helps keep records but does not guarantee compliance.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'third',
				'heading'   => 'Third-party services',
				'body_html' => <<<'HTML'
  <p>Some features rely on services run by others: WhatsApp, SMS and email providers for messages, our payment gateway for payments, national health-record integrations where offered, and hospital systems you connect. Their terms and availability are outside our control. We are not responsible for their outages, rejected messages, or changes to their rules, but we will help you where we can.</p>
HTML,
			),
			array(
				'anchor'    => 'service',
				'heading'   => 'Availability and support',
				'body_html' => <<<'HTML'
  <ul>
   <li>We work to keep the service available and to fix problems quickly. Planned maintenance will be scheduled outside common counter hours where possible, with notice in the app [confirm maintenance windows].</li>
   <li>Support is available by phone at [rx_site key="support_phone"] and through the help centre. Enterprise plans include priority support. [Confirm support hours, languages and any service levels.]</li>
   <li>We may update features to improve the service or meet legal changes. We will not remove a core feature of your paid plan during a term you have paid for without offering a fair alternative or refund [confirm].</li>
   <li>The service needs a working internet connection and a supported browser. [Confirm behaviour when the internet is down.]</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'ip',
				'heading'   => 'Intellectual property',
				'body_html' => <<<'HTML'
  <p>We own the software, design, documentation and content of the service. During your term we give you a non-exclusive, non-transferable right to use the service for your own business. If you send us suggestions, we may use them without obligation to you.</p>
HTML,
			),
			array(
				'anchor'    => 'confidentiality',
				'heading'   => 'Confidentiality',
				'body_html' => <<<'HTML'
  <p>Each side will keep the other’s non-public business information confidential, use it only for this agreement, and protect it with reasonable care. This does not apply to information that is public, already known, independently developed, or that must be disclosed by law (after notice where allowed).</p>
HTML,
			),
			array(
				'anchor'    => 'warranties',
				'heading'   => 'What we promise, and what we don’t',
				'body_html' => <<<'HTML'
  <p>We will provide the service with reasonable skill and care, and as described on the pricing page for your plan. Apart from that, and to the extent the law allows, the service is provided "as is" and "as available". We do not promise that it will be error-free or uninterrupted, or that it will make your business compliant with any law. [Counsel to review.]</p>
HTML,
			),
			array(
				'anchor'    => 'liability',
				'heading'   => 'Limitation of liability',
				'body_html' => <<<'HTML'
  <p>[To be finalised by counsel. Suggested structure:]</p>
  <ul>
   <li>Neither side is liable for indirect or consequential loss, or for loss of profit, revenue or goodwill.</li>
   <li>Our total liability in any 12 months is limited to [cap, for example the fees you paid in the 12 months before the claim].</li>
   <li>These limits do not apply to [carve-outs, for example fraud, or liability that cannot be limited by law].</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'indemnity',
				'heading'   => 'Indemnity',
				'body_html' => <<<'HTML'
  <p>You will compensate us for claims by third parties, including patients and authorities, that arise from your breach of these terms, your dispensing or business decisions, or data you entered without the right to do so. We will compensate you for third-party claims that the service, used as allowed, infringes their intellectual property rights. [Counsel to review scope and procedure.]</p>
HTML,
			),
			array(
				'anchor'    => 'termination',
				'heading'   => 'Suspension and termination',
				'body_html' => <<<'HTML'
  <ul>
   <li>You can stop using the service and cancel at any time from the app or by contacting us.</li>
   <li>We may suspend access if invoices remain unpaid, if we reasonably believe the service is being misused, or to protect security. We will give notice first unless there is an urgent risk.</li>
   <li>We may end this agreement if you seriously breach these terms and do not fix the breach within [number] days of notice.</li>
   <li>Sections on your data, confidentiality, liability, indemnity and disputes continue after termination.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'exit',
				'heading'   => 'Getting your data when you leave',
				'body_html' => <<<'HTML'
  <p>For [number] days after your account closes, you can log in to export your records [confirm the full-account export format and process]. After that period we delete customer data from live systems, and from backups within [period], unless the law requires us to keep it. Remember that you may have to keep certain records for longer under medicines and tax laws, so export before you close.</p>
HTML,
			),
			array(
				'anchor'    => 'law',
				'heading'   => 'Governing law and disputes',
				'body_html' => <<<'HTML'
  <p>These terms are governed by the laws of [governing law and jurisdiction, to be set by counsel]. Please contact us first so we can try to resolve any dispute within [30] days. [Counsel to decide dispute resolution: courts, or arbitration, and the seat and language.]</p>
HTML,
			),
			array(
				'anchor'    => 'general',
				'heading'   => 'General',
				'body_html' => <<<'HTML'
  <ul>
   <li><b>Changes to these terms.</b> We will give at least [number] days’ notice of material changes in the app or by email. If you do not accept them, you may cancel before they take effect [confirm refund treatment]. Other changes take effect when posted.</li>
   <li><b>Events outside our control.</b> Neither side is liable for delays caused by events beyond reasonable control, such as natural disasters, power or internet failures, or government action.</li>
   <li><b>Assignment.</b> You may not transfer this agreement without our consent. We may transfer it as part of a merger or sale of our business, with notice to you.</li>
   <li><b>Severability and waiver.</b> If a provision is found invalid, the rest stays in force. Not enforcing a right is not a waiver of it.</li>
   <li><b>Entire agreement.</b> These terms, the privacy notice, refund policy and any order form are the whole agreement about the service.</li>
   <li><b>Notices.</b> We send notices to the account owner’s registered email. You send notices to [legal notices email] and our postal address.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'contact',
				'heading'   => 'Contact',
				'body_html' => <<<'HTML'
  <p>[rx_site key="company"], [rx_site key="address"].</p>
  <p>Sales: [rx_site key="sales_phone"]. Support: [rx_site key="support_phone"]. Email: [to be confirmed].</p>
  <p>See also: [rx_link key="privacy"]Privacy notice[/rx_link] · [rx_link key="refund"]Refund policy[/rx_link] · [rx_link key="data-security"]Data &amp; security[/rx_link].</p>
HTML,
			),
		),
	),
);
