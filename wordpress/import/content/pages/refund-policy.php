<?php
/**
 * Import data: the Refund policy page (legal template).
 *
 * Source in the PHP_Site: refund-policy.php ($hero, $sections; it uses the
 * default draft notice of partials/legal-layout.php, so notice_html is
 * empty). Copied exactly, curly quotes included. The Starter limits in
 * "Try before you pay" are literal text on the PHP page (no pricing
 * helper), so they stay literal here.
 *
 * The PHP <title> is "Refund policy | {brand}", which the Yoast default
 * "%%title%% %%sep%% %%sitename%%" already gives, so seo.title is not set.
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
	'source'   => 'refund-policy.php',
	'key'      => 'refund',
	'title'    => 'Refund policy',
	'slug'     => 'refund-policy',
	'template' => 'templates/legal.php',
	'seo'      => array(
		'desc'    => 'Refunds and cancellations for pharmacy software subscriptions, plan changes and message or OCR credits.',
		'noindex' => true, // Draft until finance/legal sign-off.
	),
	'fields'   => array(
		'hero'        => array(
			'crumb'   => 'Refund policy',
			'parent'  => 'Legal',
			'eyebrow' => '',
			'h1'      => 'Refund & cancellation policy',
			'lead'    => 'What happens to your payment if you cancel, change your plan, or are charged by mistake.',
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
   <li>Try the free Starter plan or book a demo before you pay.</li>
   <li>You can switch or cancel a plan at any time.</li>
   <li>Duplicate or failed payments are always refunded [confirm].</li>
   <li>Refunds on paid terms and credits follow the rules below [to be confirmed by finance].</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'free',
				'heading'   => 'Try before you pay',
				'body_html' => <<<'HTML'
  <p>The Starter plan is free, with 10 sales bills and 25 purchase bills a month for one user. We recommend trying billing, purchase entry and reports on Starter, or booking a [rx_link key="demo"]free demo[/rx_link], before buying a paid plan.</p>
HTML,
			),
			array(
				'anchor'    => 'cancel',
				'heading'   => 'Cancelling a paid plan',
				'body_html' => <<<'HTML'
  <p>You can cancel at any time from the subscription page in the app, or by calling us. After you cancel, [confirm: access continues until the end of the paid term / ends immediately], and your account then [confirm: moves to Starter limits / becomes read-only for a set period].</p>
  <p>Export your reports and records before your access changes. See the [rx_link key="terms"]terms of service[/rx_link] for how long data stays available after an account closes.</p>
HTML,
			),
			array(
				'anchor'    => 'refunds',
				'heading'   => 'Refunds on subscriptions',
				'body_html' => <<<'HTML'
  <table><thead><tr><th>Situation</th><th>Refund</th></tr></thead><tbody>
   <tr><td>Cancel within [number] days of first payment</td><td>[Full / partial / none]</td></tr>
   <tr><td>Cancel later in a 1-year term</td><td>[Confirm, for example no refund for the current year]</td></tr>
   <tr><td>Cancel during a 3- or 5-year term</td><td>[Confirm, for example refund of unused full years at the standard 1-year rate]</td></tr>
   <tr><td>Service not provided as described and we cannot fix it</td><td>[Confirm, for example pro-rated refund of the unused term]</td></tr>
  </tbody></table>
  <p>Any one-time onboarding, data migration or training fees are [confirm: refundable / non-refundable once the work has started].</p>
HTML,
			),
			array(
				'anchor'    => 'changes',
				'heading'   => 'Upgrades and downgrades',
				'body_html' => <<<'HTML'
  <ul>
   <li><b>Upgrade</b>: [confirm, for example you pay the difference for the rest of your term and the new plan starts immediately].</li>
   <li><b>Downgrade</b>: [confirm, for example the lower plan starts at your next renewal, with no refund of the difference]. Check that the lower plan’s user limit and features suit you, because extra users may be deactivated.</li>
  </ul>
HTML,
			),
			array(
				'anchor'    => 'credits',
				'heading'   => 'Message and OCR credits',
				'body_html' => <<<'HTML'
  <p>Credits for WhatsApp, SMS and email messages and for OCR imports are used as soon as a message is sent or a file is processed, so used credits cannot be refunded. For unused credits: [confirm whether purchased credits are refundable, whether they expire, and whether they carry over at renewal].</p>
HTML,
			),
			array(
				'anchor'    => 'errors',
				'heading'   => 'Duplicate or failed payments',
				'body_html' => <<<'HTML'
  <p>If you are charged twice for the same invoice, or money is deducted but the payment fails, tell us with the payment reference. We will refund the extra or failed amount to the original payment method once our payment gateway confirms it [confirm timeline, for example within 7 working days].</p>
HTML,
			),
			array(
				'anchor'    => 'gst',
				'heading'   => 'Taxes on refunds',
				'body_html' => <<<'HTML'
  <p>Where a refund is approved, any tax charged on the refunded amount (such as GST, VAT or sales tax) is adjusted as the tax rules that apply require, usually through a credit note issued to you. [Confirm with the finance team, per market.]</p>
HTML,
			),
			array(
				'anchor'    => 'how',
				'heading'   => 'How to request a refund',
				'body_html' => <<<'HTML'
  <ol>
   <li>Call [rx_site key="sales_phone"] or email [billing email], from or quoting your registered email.</li>
   <li>Share your store name, invoice number and the reason for the request.</li>
   <li>We confirm the decision within [number] working days.</li>
   <li>Approved refunds are paid to the original payment method within [number] working days. Your bank may take a few more days to show it.</li>
  </ol>
  <p>Mandatory consumer or statutory rights in your country are not affected by this policy. [Counsel to confirm.]</p>
  <p>Please contact us before raising a chargeback with your bank. It is usually faster, and a chargeback may pause your account while it is reviewed [confirm].</p>
HTML,
			),
			array(
				'anchor'    => 'contact',
				'heading'   => 'Contact',
				'body_html' => <<<'HTML'
  <p>Billing questions: [rx_site key="sales_phone"]. Support: [rx_site key="support_phone"]. Email: [billing email].</p>
  <p>[rx_site key="company"], [rx_site key="address"].</p>
HTML,
			),
		),
	),
);
