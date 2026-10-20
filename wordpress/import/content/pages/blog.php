<?php
/**
 * Import data: the Blog page (WordPress posts page, /blog/).
 *
 * Source: blog/index.php in the PHP_Site (hub hero, "Start here" path and the
 * tools strip), copied exactly. The posts themselves are content/posts/*.php;
 * the topic links, counts, featured guide and card grid are built by home.php
 * from the posts, as blog/index.php builds them from partials/data/blog.php.
 *
 * - No page template: WordPress renders the posts page with home.php. The
 *   fields are group_rx_blog (located on the posts page).
 * - Slug 'blog': posts live at /blog/{post-slug}/ and topic views at
 *   /blog/{topic}/ (the rewrite rule in inc/blog.php hard-codes /blog/).
 * - start_here lists posts by slug, in reading order; the import writes it
 *   after the posts exist.
 * - The hero crumb is the page title ("Blog & guides"), as on the PHP hub.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

return array(
	'source' => 'blog/index.php',
	'key'    => 'blog',
	'title'  => 'Blog & guides',
	'slug'   => 'blog',
	'status' => 'publish',
	'group'  => 'blog',
	'seo'    => array(
		'title'   => 'Blog & guides for running a medical store %%sep%% %%sitename%%',
		'desc'    => 'Practical guides for pharmacy owners: cutting expiry loss, setting stock levels, checking distributor bills, switching safely and staff logins.',
		'noindex' => false,
	),
	'fields' => array(
		'hero'             => array(
			'eyebrow' => 'Blog & guides',
			'h1'      => 'Practical guides for running a medical store',
			'lead'    => 'Written for owners and pharmacists at the counter: what to check, what to count and what to change, with worked numbers. No hype, and no tax or legal advice dressed up as a blog post.',
		),
		'start_title'      => 'Thinking of switching? Read these in order',
		'start_here'       => array(
			'desktop-vs-browser',
			'switch-pharmacy-software',
			'staff-logins-roles',
		),
		'start_link_label' => 'The full switching guide',
		'tools_head'       => array(
			'eyebrow' => '',
			'title'   => 'Tools that go with the guides',
			'intro'   => '',
		),
		'tools'            => array(
			array(
				'icon'     => 'i-chart',
				'title'    => 'Expiry loss calculator',
				'text'     => 'Put a number on what expiry costs your store in a year.',
				'link_key' => 'calculator',
			),
			array(
				'icon'     => 'i-file',
				'title'    => 'Buyer’s checklist',
				'text'     => 'Eight questions to ask any pharmacy software vendor.',
				'link_key' => 'checklist',
			),
			array(
				'icon'     => 'i-box',
				'title'    => 'All features',
				'text'     => 'What each part of the software does, with the plan it’s on.',
				'link_key' => 'features',
			),
		),
	),
);
