# Implementation Plan: WordPress migration

## Overview

The tasks follow the requirements in order, Requirement 1 to 13, with checkpoints in between.

- **Languages:** PHP 8.3 for the theme, setup and import (`D:\xampp\php\php.exe`), PowerShell for the PHP-site baseline, and Node 24 ESM for tests and the parity check.
- **Where files go:** every new repository file goes under `d:\xampp\htdocs\pharmacy\wordpress\`. No PHP_Site file is edited, including the root `.gitignore`.
- **What is off limits:** live sites and the Healthray WordPress connection or its `mcp_healthray_wordpress_*` tools are never used.
- **Execution order:** some tasks use code from a later requirement. For example, templates (3) use SCF fields (7) and pricing (9). The dependency graph at the end gives the execution order.

## Tasks

- [x] 1. Keep the PHP site safe and the work local (Requirement 1)
  - [x] 1.1 Create `wordpress/tools/baseline.ps1` and record the baseline
    - `-Record`: SHA-256 of every PHP_Site file, excluding `.git/`, `.kiro/`, `wordpress/` and `partials/data/cache/`, written to `wordpress/tools/out/php-site-baseline.txt`
    - `-Verify`: re-hashes and lists added, changed and removed files, exit code 1 on any difference
    - Run `-Record` once before any other task
    - _Requirements: 1.1, 1.2_
  - [x] 1.2 Create `wordpress/.gitignore` and `wordpress/.htaccess`
    - `.gitignore`: `tools/node_modules/`, `tools/out/`, `*.sql`, `*.sql.gz`, `*.zip`, `*.log`, `wp-config*.php`, `.env*`, `uploads/`
    - `.htaccess`: `Require all denied`. Check that `http://localhost/pharmacy/wordpress/` returns 403 while `http://localhost/pharmacy/` still works
    - _Requirements: 1.1, 1.2, 2.4_
  - [x] 1.3 Create `wordpress/import/lib/guard.php`
    - `rx_bootstrap_wp()`: exits unless `PHP_SAPI === 'cli'`, sets `HTTP_HOST`/`REQUEST_URI`, requires `dirname( __DIR__, 4 ) . '/pharmacy-wp/wp-load.php'`
    - `rx_assert_local()`: aborts unless `DB_HOST` is `localhost`/`127.0.0.1`, the `siteurl` and `home` hosts pass `rx_is_local_host()`, and `ABSPATH` is `…\htdocs\pharmacy-wp\`. It requires the theme's `inc/core/http.php` only when `rx_is_local_host()` doesn't exist yet
    - Creates no users and changes no WordPress install settings beyond the ones this spec lists
    - _Requirements: 1.3, 1.5_
  - [x] 1.4 Implement the outbound HTTP allow-list
    - `wordpress/themes/pharmacy/inc/core/http.php`: `rx_is_local_host( string $host ): bool` (`localhost`, `127.0.0.1`, `192.168.x.x` as a full IPv4 match) and `rx_host_allowed( string $url, string $api_url ): bool` (local host or the exact Pricing_API host, compared on `wp_parse_url`-style host parsing that ignores userinfo)
    - `inc/http.php`: a `pre_http_request` filter that returns `new WP_Error( 'rx_blocked', … )` for any URL `rx_host_allowed()` rejects, using `rx_setting( 'pricing_api' )`
    - _Requirements: 1.4_
  - [x]* 1.5 Write unit tests for the host checks
    - `wordpress/tools/test/http.test.mjs` through the PHP bridge
    - Allowed: `localhost`, `127.0.0.1`, `192.168.0.25`, the pricing host. Blocked: `localhost.evil.com`, `192.168.evil.com`, `http://localhost@evil.com/`, `api.wordpress.org`, `pharmacy.healthray.com`
    - _Requirements: 1.3, 1.4_

- [x] 2. Parent and child theme scaffold (Requirement 2)
  - [x] 2.1 Create the parent theme skeleton in `wordpress/themes/pharmacy/`
    - `style.css` header: Theme Name "Pharmacy", Text Domain `pharmacy`, Version, Requires PHP 8.1
    - `functions.php`: defines `RX_VERSION`, then requires `inc/core/{format,http,pricing,blog,schema}.php` and `inc/{http,setup,helpers,enqueue,cleanup,nav,shortcodes,pricing,blog,seo}.php`
    - Create every listed `inc` file as a stub holding only the guard. Core files use `defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;`; all others use `defined( 'ABSPATH' ) || exit;`
    - Minimal `index.php`
    - _Requirements: 2.1, 2.2, 2.6_
  - [x] 2.2 Create the child theme in `wordpress/themes/pharmacy-child/`
    - `style.css` with `Template: pharmacy` and Text Domain `pharmacy-child`
    - `functions.php` with the guard and a comment on where site-specific overrides go. Nothing is enqueued
    - _Requirements: 2.1, 2.2_
  - [x] 2.3 Copy the assets and the pricing snapshot into the parent theme
    - Copy `assets/css`, `assets/js`, `assets/fonts/campton` and `assets/img` (including `screens/` and `trusted/`) to `wordpress/themes/pharmacy/assets/`, and `partials/data/pricing.json` to `wordpress/themes/pharmacy/data/pricing.json`
    - The sources are read only. Compare hashes after copying
    - _Requirements: 2.5, 8.1, 10.1_
  - [x] 2.4 Link the themes and copy the plugins (local only, nothing in Git)
    - `cmd /c mklink /J "D:\xampp\htdocs\pharmacy-wp\wp-content\themes\pharmacy" "D:\xampp\htdocs\pharmacy\wordpress\themes\pharmacy"`, and the same for `pharmacy-child`
    - `robocopy` `secure-custom-fields`, `wordpress-seo` and `contact-form-7` from `D:\xampp\htdocs\labora-wp\wp-content\plugins\` into `D:\xampp\htdocs\pharmacy-wp\wp-content\plugins\`. Copy only; never modify `labora-wp`
    - _Requirements: 1.2, 2.3_
  - [x] 2.5 Write and run `wordpress/import/setup.php`
    - Uses `rx_bootstrap_wp()` and `rx_assert_local()`, and checks that the plugin folders and junctions exist (prints the needed command if not)
    - `activate_plugin()` for the three plugins, `switch_theme( 'pharmacy-child' )`
    - Options: `permalink_structure=/blog/%postname%/`, `default_comment_status=closed`, `default_ping_status=closed`
    - Yoast through `WPSEO_Options::set()`: `separator=sc-pipe`, `title-page`/`title-post` = `%%title%% %%sep%% %%sitename%%`, `company_or_person=company`, `company_name=''`, `disable-author`, `disable-date`, `noindex-tax-category`, `noindex-tax-post_tag`, `breadcrumbs-enable=false`, `tracking=false`
    - Then `flush_rewrite_rules( false )`
    - Run it once and confirm `get_stylesheet() === 'pharmacy-child'` and `get_template() === 'pharmacy'`
    - _Requirements: 1.5, 2.1, 2.3, 12.1_
  - [x] 2.6 Port the `config.php` helpers
    - `inc/core/format.php`: `rx_inr()`, `rx_join()` (port of `pricing_join`), `rx_barcode_svg()` (same bars as `barcode_svg`), `rx_legal_mark()` (same regex as `legal-layout.php`)
    - `inc/helpers.php`:
      - Settings and fields: `rx_brand()`, `rx_setting()`, `rx_field()`
      - `rx_url()`: `_rx_key` map with the fallback keys `retail`/`chain`/`hospital` → `/#solutions`, `new-pharmacy` → `/#switching`, `calculator` → `/#estimator`, `checklist` → `/#checklist`, and `guides` → Blog
      - `rx_page_key()`
      - Markup: `rx_asset()`, `rx_img()`, `rx_svg_img()`, `rx_logo()`, `rx_text()`, `rx_html()`, `rx_feature_nav()`
    - Same arguments and markup as `img_tag()`, `svg_img()` and `logo()`
    - _Requirements: 2.2, 2.6, 4.5, 6.3_
  - [x] 2.7 Implement theme setup and asset loading
    - `inc/setup.php`:
      - `after_setup_theme`: `title-tag`, `html5`, `register_nav_menus()` for `primary`, `footer_features`, `footer_solutions`, `footer_resources`, `footer_company` and `legal`, and `load_theme_textdomain( 'pharmacy' )`
      - `acf/settings/save_json` and `acf/settings/load_json` pointed at `acf-json/`
      - On `acf/init`: `acf_add_options_page()` "Site settings" (`rx-settings`)
    - `inc/enqueue.php`:
      - `rx_asset_map()` and `rx_enqueue_assets()` implementing the handle table in design §2, with `strategy => defer` and `in_footer => true`
      - A `wp_preload_resources` filter for Campton 400 and 700
      - A `wp_head` callback that prints the SVG favicon
    - _Requirements: 2.5, 2.6, 6.7, 11.2, 11.3_
  - [x] 2.8 Set up the test harness in `wordpress/tools/`
    - `package.json`: `"type": "module"`, exact versions of `fast-check`, `playwright-core`, `pixelmatch` and `pngjs`, and scripts `test` (`node --test test/`) and `parity` (`node parity/check.mjs`). Run `npm install` in `wordpress/tools/`
    - `test/php/bridge.php`: CLI only, defines `RX_TESTING`, requires `themes/pharmacy/inc/core/*.php`, reads a JSON batch `[{fn, args}]` from stdin and prints JSON results
    - `test/lib/php.mjs`: `callPhp( calls )` and `runPhp( script, input )` helpers
    - _Requirements: 13.1_
  - [x]* 2.9 Write unit tests for the ported format helpers
    - `wordpress/tools/test/format.test.mjs`: `rx_inr()` examples (`0`, `999`, `1000`, `100000`, `1092480`), `rx_barcode_svg()` equal to the PHP site's `barcode_svg()` output for sample texts (run from a temp copy of `config.php`), and `rx_legal_mark()` examples
    - _Requirements: 2.6, 5.3_

- [ ] 3. Theme files: templates and parts (Requirement 3)
  - [x] 3.1 Write `header.php` and `footer.php`
    - `header.php`:
      - Doctype, `<html <?php language_attributes(); ?>>`, charset and viewport meta, `wp_head()`
      - `<body <?php body_class(); ?>>`, `wp_body_open()`, `parts/sprite`, skip link, review banner
      - The `.hd` markup of `partials/header.php`, with `wp_nav_menu()` (`primary`, `Rx_Mega_Walker`, `items_wrap` `<ul class="hd-menu">%3$s</ul>`, `fallback_cb` false), the mobile actions and `.hd-actions`
    - `footer.php`:
      - `parts/cta-band` unless `$args['hide_cta']`
      - The `.ft` block with the four footer menus (`Rx_Footer_Walker`, headings from menu names), contacts and social from Site_Settings, the `legal` menu, `.ft-word` and the mobile bar
      - Then `wp_footer()`
    - _Requirements: 3.1, 4.3, 4.4, 10.2_
  - [x] 3.2 Write the shared parts in `parts/`
    - Port each partial with `$args` replacing include-time variables: `page-hero.php`, `cta-band.php`, `faq-list.php`, `demo-form.php`, `hero-lead.php`, `estimator.php`, `trusted-strip.php`, `problems-chat.php`, `product-tour.php`, `sprite.php`
    - Keep every class, id, ARIA and data attribute. Tool UI strings use `esc_html__( …, 'pharmacy' )`
    - `faq-list` takes `items`, `ids` (answer and button formats plus a start index), `confirm_on` (`item` or `answer`) and skips items that render empty
    - `demo-form` takes `title`, `sub` and `success_title`
    - `trusted-strip` reads the child theme's `assets/img/trusted/` first, then the parent's
    - _Requirements: 3.6, 5.3, 6.6, 9.1, 9.2, 10.2, 11.5_
  - [x] 3.3 Write `front-page.php`
    - Sections in today's order, from `group_rx_home` fields: `#top` hero + `hero-lead` (`hl1`), trusted strip, `#tour`, `#problems`, `#estimator` (`cta` `#demo`), `#day`, `#features` tabs (`data-scroll-tabs`), `#switching`, `#solutions`, `#checklist`, `#support`, `#faq` (ids `faq-%d`/`faq-b%d`, start 1), `#demo`
    - End with `get_footer( null, [ 'hide_cta' => true ] )`
    - _Requirements: 3.2, 5.1, 5.2, 5.3_
  - [x] 3.4 Write `templates/feature.php`
    - Port of `partials/feature-page.php` reading `group_rx_feature`
    - Plan lines use `rx_feature_plan_label()` with the fixed `plan` text as fallback, and "Plan: not listed separately" otherwise
    - Related row from `rx_feature_nav()` plus the switching link
    - Estimator when `leaks_estimator` is on
    - FAQ ids `{key}q%d`/`{key}q%db` from 0
    - Each section is skipped when empty
    - Template Name "Feature"
    - _Requirements: 3.5, 6.4, 8.1_
  - [x] 3.5 Write `templates/legal.php`
    - `page-hero` with the meta line, TOC from `sections`, notice (`notice_html` or the default draft notice, with `data-confirm`)
    - Each body goes through `rx_html()` and then `rx_legal_mark()`, followed by the optional screen and sample caption
    - Template Name "Legal"
    - _Requirements: 3.5, 5.3, 6.4_
  - [x] 3.6 Write `templates/pricing.php` and `templates/features-hub.php`
    - Pricing: term bar, plan cards (Most popular tab, `plan_fit` lines, `rx_term_total()`), comparison table with collapsible groups and `feature_labels`, credits table, FAQ (`pq%d`, `confirm_on` `answer`)
    - Features hub: CTAs + assure, problem index, two module groups with live plan lines, hospital/chain block, plans table, FAQ
    - When `rx_pricing_data()` is null, both print `rx_pricing_notice()` where prices would be
    - _Requirements: 3.5, 8.2, 8.3, 8.4_
  - [x] 3.7 Write the company and guide templates
    - `templates/switch.php`, `templates/faq.php`, `templates/about.php`, `templates/contact.php`, `templates/support.php` and `templates/demo.php`, each with the same sections and markup as its PHP file
    - Contact and demo use `demo-form`; demo ends with `get_footer( null, [ 'hide_cta' => true ] )`
    - _Requirements: 3.5, 5.1_
  - [~] 3.8 Write `page.php`, `index.php` and `404.php`
    - `page.php` and `index.php`: `page-hero` + `the_content()`
    - `404.php`: `page-hero`-style heading, a short message and links to home, features, pricing and the blog through `rx_url()`, in the design system
    - _Requirements: 3.4_

- [x] 4. Header and mega menu (Requirement 4)
  - [x] 4.1 Implement `Rx_Mega_Walker` in `inc/nav.php`
    - Override `display_element()`, `start_lvl()`, `end_lvl()`, `start_el()` and `end_el()` to print the `partials/header.php` markup:
      - `hd-has` + button with `aria-controls="mm-{slug}"`
      - `.mm` / `.mm-wide`, `.mm-cols` and `.mm-col` + `p.mm-h` for groups
      - `a.mm-item` with icon and description from the SCF menu-item fields
      - The `.mm-promo` aside from Site_Settings
      - Plain `a.hd-link` for top items without children
    - `is-active` when `rx_page_key()` matches the `_rx_key` of the item or of a descendant
    - _Requirements: 4.1, 4.2, 4.3_
  - [x] 4.2 Implement `Rx_Footer_Walker`, the legal links and the `#rx:` resolver in `inc/nav.php`
    - `Rx_Footer_Walker` prints `<li><a href>label</a></li>`. The legal menu prints bare `<a>` links
    - A `nav_menu_link_attributes` filter resolves `#rx:{key}` to `rx_setting()` for `app_login`, `app_signup` and `help_url`, and to `rx_url( $key )` otherwise
    - _Requirements: 4.4, 4.5_
  - [x] 4.3 Write `wordpress/import/content/menus.php`
    - `primary`: Features (groups "Run the counter" and "Run the business"), Solutions, Pricing, Switch and Resources. Labels, descriptions and icons come from `NAV_FEATURES`, `NAV_SOLUTIONS` and `NAV_RESOURCES`
    - Menus "Features", "Solutions", "Resources", "Company" and "Legal", with the same labels and targets as `partials/footer.php`. The Help centre, Log in and Sign up free links use `#rx:` URLs
    - _Requirements: 4.1, 4.2, 4.4, 4.5_
  - [ ]* 4.4 Write an example test for the header navigation
    - `wordpress/tools/test/php/wp-nav.php` (read-only, loads WordPress) + `wordpress/tools/test/nav.test.mjs`
    - The normalised `#hd-nav` markup equals the PHP site's for the same page, and the `is-active` item matches the PHP site for every page key
    - _Requirements: 4.1, 4.3_

- [~] 5. Checkpoint: theme scaffold, templates and menus
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Pages, content and the import (Requirement 5)
  - [x] 6.1 Write the import library in `wordpress/import/lib/`
    - `upsert.php`:
      - `rx_upsert_post( array $item ): int|WP_Error`: finds by `_rx_source`, then calls `wp_insert_post()`/`wp_update_post()` with `post_status`, `comment_status=closed`, `page_template`, the parent found by `_rx_key`, `post_date` for posts, and `meta_input` for `_rx_key`, `_rx_source` and the Yoast meta
      - `rx_upsert_menu( array $menu ): int|WP_Error`
    - `fields.php`: `rx_write_fields( int|string $target, string $group, array $fields )` calling `update_field( "field_rx_{$group}_{$name}", $value, $target )`; reports unknown keys
    - _Requirements: 5.4_
  - [x] 6.2 Write `wordpress/import/import.php`
    - Steps 1–9 of design §13: guard; settings; pages (parents first); posts; `show_on_front`, `page_on_front`, `page_for_posts`, `wp_page_for_privacy_policy`; menus with locations and menu-item fields; logo attachment (task 14.3); deleting the WordPress sample content once ("Hello world!", "Sample Page", draft "Privacy Policy", only items without `_rx_source`); `flush_rewrite_rules( false )`
    - Prints created, updated and failed counts and exits non-zero on failures
    - _Requirements: 1.5, 5.4, 5.5_
  - [x] 6.3 Write `content/settings.php` and `content/pages/home.php`
    - Settings: values of `SITE`, `HERO_PATTERN` and `PRICING_*` from `partials/config.php`, plus the CTA band, footer blurb, footer note and promo panel copy from `footer.php`/`header.php`
    - Home: every section of `index.php` as `group_rx_home` values, with exact text, `confirmed => false` wherever `data-confirm` is used today, and `[rx_brand]` where `$brand` is used
    - SEO title and description from `$title`/`$description`
    - _Requirements: 5.1, 5.2, 5.3, 6.2, 6.3_
  - [x] 6.4 Write the features hub and feature page data files
    - `content/pages/features.php` and `content/pages/features-{billing,expiry,inventory,purchase,reports,gst,retention,security}.php` (parent `features`)
    - `$fp` mapped onto `group_rx_feature`. Every `feature_plan_label()`, `pricing_split()` and `plan_bill_limits()` use becomes `[rx_plan]`, `[rx_free_plan]` or `[rx_if]`, and every `url()` link becomes `[rx_link]`
    - Worked-example numbers stay literal
    - _Requirements: 5.1, 5.3, 5.5, 6.4, 6.8, 8.4_
  - [x] 6.5 Write the data files for pricing, switch, FAQ and the company pages
    - `content/pages/{pricing,switch,faq,about,contact,support,demo}.php`
    - Live values as shortcodes (`[rx_cost_text]`, `[rx_compare_text]`, `[rx_terms_text]`, `[rx_free_plan]`, `[rx_tel]`, `[rx_site]`)
    - Pricing `plan_fit` and `feature_labels` from `pricing.php`
    - _Requirements: 5.1, 5.3, 5.5, 6.8, 8.4_
  - [x] 6.6 Write the five legal data files
    - `content/pages/{privacy,terms,refund-policy,cookie-policy,data-security}.php`
    - Sections with anchors, headings and body HTML. `{$company}`, `{$address}` and the phone numbers become `[rx_site]`, and links become `[rx_link]`
    - `noindex => true` except data-security. The data-security screen goes in the section's `screen` field
    - _Requirements: 5.1, 5.3, 5.5, 12.2_
  - [x] 6.7 Write the blog data files
    - `content/pages/blog.php`: hub hero copy, Start here posts (`desktop-vs-browser`, `switch-pharmacy-software`, `staff-logins-roles`) and tools
    - `content/posts/{cut-expiry-loss,switch-pharmacy-software,min-max-stock,check-distributor-bill,desktop-vs-browser,staff-logins-roles}.php`: metadata from `partials/data/blog.php`, `body` layouts mapped one-to-one from the `<h2>`, `<p>` and `bk_*` calls, and FAQ from `blog_end()`
    - _Requirements: 5.1, 5.5, 6.5, 7.2_
  - [~] 6.8 Run the import twice
    - Run `php wordpress/import/import.php` after the field groups (7), blog code (8) and pricing (9) exist
    - Run it a second time and confirm that page, post and menu-item counts and IDs are unchanged and no item failed
    - _Requirements: 5.4, 5.5_

- [x] 7. Editable content with SCF (Requirement 6)
  - [x] 7.1 Create `acf-json/group_rx_settings.json`
    - Tabs Contacts, Links, Social, Layout and Pricing with the fields in design (Data Models), on the options page `rx-settings`
    - Keys follow `field_rx_settings_{name}`
    - _Requirements: 6.2, 6.7_
  - [x] 7.2 Create `acf-json/group_rx_home.json`
    - One tab per home section with the fields and repeaters in design (Data Models), on the front page
    - _Requirements: 5.2, 6.1, 6.7_
  - [x] 7.3 Create `acf-json/group_rx_feature.json` and `acf-json/group_rx_legal.json`
    - Feature mirrors every `$fp` key: hero, CTAs, assure, screen, scenes, leaks with `feat`/`plan` and the example or estimator, steps + keys, caps, guide, proof, related, `related_title`, FAQ with `confirmed`
    - Legal: hero with meta, `notice_html`, `sections` [anchor, heading, body_html, screen, screen_alt]
    - Located on their page templates
    - _Requirements: 6.1, 6.4, 6.6, 6.7_
  - [x] 7.4 Create the remaining page groups
    - `group_rx_pricing`, `group_rx_features_hub`, `group_rx_switch`, `group_rx_faq`, `group_rx_about`, `group_rx_contact`, `group_rx_support` and `group_rx_demo`, located on their templates
    - _Requirements: 6.1, 6.6, 6.7_
  - [x] 7.5 Create the post, blog page and menu-item groups
    - `group_rx_post`: dek, topic select, read time, cover screen/zoom/focus, featured, updated, FAQ with `confirmed`, and `body` flexible content with the 11 layouts (text, heading, takeaways, note, table, compare, sum, rule, checklist, steps, cta)
    - `group_rx_blog` on the posts page; `group_rx_menu_item` on primary menu items (icon select, description)
    - _Requirements: 4.2, 6.5, 6.6, 6.7_
  - [x] 7.6 Implement `rx_faq_schema( array $items ): ?array` in `inc/core/schema.php`
    - Builds a FAQPage from items with `confirmed` true: Question name and Answer text `trim( strip_tags() )`. Returns null when no item is confirmed
    - _Requirements: 6.6, 12.4_
  - [x]* 7.7 Write a property test for FAQ schema
    - `wordpress/tools/test/prop-faq-schema.test.mjs`, `numRuns >= 100`
    - **Property 3: FAQ schema holds only confirmed answers**
    - **Validates: Requirements 6.6, 12.4**
  - [x] 7.8 Implement the shortcodes in `inc/shortcodes.php`
    - `rx_brand`, `rx_site`, `rx_tel`, `rx_url`, `rx_link`, `rx_plan`, `rx_free_plan`, `rx_cost_text`, `rx_compare_text`, `rx_terms_text`, `rx_users_text`, `rx_credits_text` and `rx_if`, with the attributes in design §6
    - Each reads `rx_pricing_data()` once and returns '' (or `fallback`) when pricing is null
    - _Requirements: 6.3, 6.8, 8.3, 8.4_
  - [ ]* 7.9 Write example tests for the shortcodes
    - `wordpress/tools/test/php/wp-shortcodes.php` + `wordpress/tools/test/shortcodes.test.mjs`
    - Each shortcode with the snapshot fixture equals the matching core helper. `[rx_link]` and `[rx_tel]` markup is correct. Unregistered `[placeholder]` text is left unchanged
    - _Requirements: 6.8_

- [ ] 8. Blog (Requirement 7)
  - [x] 8.1 Implement the pure blog helpers in `inc/core/blog.php`
    - `rx_safe_post_slug( string $slug, array $topics ): string` appends `-guide` to a topic key
    - `rx_blog_toc( string $html ): array` returns `[ html, toc ]`, using the same regex and slug rule as `blog_end()` (de-duplicating with `-2`, `-3`, and `section` when empty)
    - _Requirements: 7.3, 7.4_
  - [x]* 8.2 Write a property test for the slug guard
    - `wordpress/tools/test/prop-slug.test.mjs`, `numRuns >= 100`
    - **Property 4: Post slugs never equal a topic**
    - **Validates: Requirements 7.4**
  - [x]* 8.3 Write a property test for the TOC builder
    - `wordpress/tools/test/prop-toc.test.mjs`, `numRuns >= 100`
    - **Property 5: The "On this page" list matches the article headings**
    - **Validates: Requirements 7.3**
  - [x] 8.4 Implement blog routing and queries in `inc/blog.php`
    - `RX_TOPICS` constant; the `rx_topic` query var; `add_rewrite_rule()` built from the topic keys (`top`)
    - A `wp_unique_post_slug` filter using `rx_safe_post_slug()`, with a numeric suffix when taken
    - A `template_redirect` callback that sends category, tag, date, author, search, feed and attachment views to 404
    - `rx_blog_posts()`, `rx_blog_topic_counts()`, `rx_blog_date()` (`j M Y`) and `rx_blog_author()` ("The {brand} team")
    - _Requirements: 7.2, 7.4_
  - [x] 8.5 Write the blog parts
    - `parts/blog-cover.php` and `parts/blog-card.php`, with variants `card`, `feature` and `row`, same markup as `blog_cover()`/`blog_card()`. Topic pills link to `/blog/{topic}/`
    - `parts/blog-layouts.php` renders the 11 `body` layouts with the markup of `bk_*()`
    - _Requirements: 3.6, 6.5, 7.1_
  - [~] 8.6 Write `home.php` (blog hub and topic views)
    - Hero (topic views change crumb, parent link and h1), topic links with counts and `aria-current`, featured guide + Start here only without a topic, grid with the "Latest guides"/"N guides" heading and empty message, tools strip
    - _Requirements: 3.3, 7.1, 7.2_
  - [~] 8.7 Write `single.php`
    - Progress bar, hero, meta bar with topic link, author, dates and read time, copy link (`data-copy` = root-relative permalink), cover, aside TOC + demo box from `rx_blog_toc()`, article layouts, byline, FAQ (`bq%d`), Keep reading (same topic first, then newest, 3 cards)
    - _Requirements: 3.3, 7.3_
  - [ ]* 8.8 Write example tests for blog routes
    - `wordpress/tools/test/blog-routes.test.mjs` (local HTTP only)
    - `/blog/` and the 5 topic views return 200 with the PHP site's counts, each post URL returns 200, and `/blog/not-a-post/`, `/blog/category/uncategorized/` and `/?s=x` return 404
    - _Requirements: 7.2, 7.4_

- [x] 9. Live pricing (Requirement 8)
  - [x] 9.1 Implement the pure pricing core in `inc/core/pricing.php`
    - `rx_pricing_valid`, `rx_pricing_plans`, `rx_pricing_plan`, `rx_plan_price`, `rx_plan_bill_limits`, `rx_pricing_terms_text`, `rx_pricing_split`, `rx_pricing_cost_text`, `rx_pricing_compare_text`, `rx_feature_plan`, `rx_feature_plan_label`, `rx_plan_users_text`, `rx_plan_credits_text`, `rx_term_total` and `rx_pricing_meta_bits`. All take `$d` and port `partials/pricing-data.php` and `pricing.php` exactly, with no static memo
    - `rx_pricing_resolve( array $sources, array $hooks ): array` (order cache → live → stale → snapshot, returns `[ data|null, source ]`)
    - _Requirements: 8.1, 8.4_
  - [x]* 9.2 Write a property test for pricing parity
    - `wordpress/tools/test/php/oracle.php` + `wordpress/tools/test/prop-pricing-parity.test.mjs`, `numRuns >= 100`
    - The oracle runs the PHP site's `config.php` and `pricing-data.php` from a temp copy whose `PRICING_API` is an unreachable local address and whose cache file holds the generated document, in one process per case
    - **Property 1: Pricing helpers match the PHP site**
    - **Validates: Requirements 8.1, 8.4, 6.8**
  - [x]* 9.3 Write a property test for the pricing source order
    - `wordpress/tools/test/prop-pricing-resolve.test.mjs`, `numRuns >= 100`
    - **Property 2: Pricing sources are tried in order**
    - **Validates: Requirements 8.1, 8.3**
  - [x] 9.4 Implement the WordPress pricing service in `inc/pricing.php`
    - `rx_pricing_data(): ?array`, memoised per request: transient `rx_pricing`; `wp_remote_get()` with timeout 5 and `Accept: application/json`; `set_transient()` with `pricing_ttl` plus `update_option( 'rx_pricing_stale', …, false )` on success; on stale use, `set_transient()` with `pricing_retry`; snapshot via `get_theme_file_path( 'data/pricing.json' )`
    - `rx_pricing_cell()` and `rx_pricing_notice()`
    - _Requirements: 8.1, 8.3_
  - [ ]* 9.5 Write an example test for the pricing fallback
    - `wordpress/tools/test/php/wp-pricing.php` + `wordpress/tools/test/pricing-fallback.test.mjs`
    - A test-only filter forces `rx_pricing_data()` to null. Pricing, a feature page and the features hub then render with HTTP 200, the notice, "Plan: not listed separately" and no SoftwareApplication
    - _Requirements: 8.3_

- [~] 10. Checkpoint: content imported, pricing and blog working
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Scripts, interactions and forms (Requirement 9)
  - [~] 11.1 Write the interaction check `wordpress/tools/parity/interactions.mjs` (playwright-core, Edge)
    - Menus: mega menus open and close, Escape closes them, and the burger opens the full-screen menu at 375 px
    - Feature tabs: arrow, Home and End keys; scroll tabs at 1440
    - FAQ: `aria-expanded` toggles
    - Estimator: outputs change with the sliders
    - Pricing: the term switch updates `.term-total`, and the group and Expand-all toggles work
    - Tour: play/pause, full screen and `?tour-at=15`
    - Blog: progress bar, TOC, copy link and print
    - Forms: the demo slip and demo form show errors when empty and the thank-you when valid, with no network request on submit
    - Exported as `runInteractions( page, url )` for `check.mjs`
    - _Requirements: 9.1, 9.2, 10.2_
  - [x] 11.2 Switch off Contact Form 7 on the front end in `inc/cleanup.php`
    - `add_filter( 'wpcf7_load_js', '__return_false' )` and `add_filter( 'wpcf7_load_css', '__return_false' )`. Confirm that no import content contains a `[contact-form-7` shortcode
    - _Requirements: 9.3_

- [ ] 12. Design, accessibility and responsive layout (Requirement 10)
  - [x] 12.1 Add the `lang` and body class filters in `inc/setup.php`
    - `language_attributes` → `lang="en-IN"`
    - `body_class` → `page-{rx_page_key()}`, keeping only `admin-bar` and `logged-in` when present
    - _Requirements: 10.1, 10.2_
  - [~] 12.2 Audit the templates and parts against the partials
    - Every template has `<main id="main">`. The order is sprite, skip link, review banner
    - Every `role`, `aria-*`, `hidden`, `tabindex`, `data-confirm` and `data-cta` from the PHP markup is present
    - No wrapper elements were added around `.hd`, `.container` or sections
    - Fix any template that differs
    - _Requirements: 10.1, 10.2_
  - [ ]* 12.3 Write a static content test
    - `wordpress/tools/test/static-content.test.mjs` scans `wordpress/themes/` and `wordpress/import/content/`
    - No "Healthray" outside URL values in `settings.php`, and none of the snapshot's paid plan prices (for example `4,999`, `9,999`) outside `data/pricing.json`
    - _Requirements: 8.4, 10.4_

- [x] 13. Performance (Requirement 11)
  - [x] 13.1 Implement the front-end cleanup in `inc/cleanup.php`
    - Deregister `jquery`, `wp-embed`
    - Remove emoji scripts, styles and filters
    - Dequeue `wp-block-library`, `wp-block-library-theme`, `classic-theme-styles` and `global-styles`, and remove `wp_enqueue_global_styles`
    - Remove the image auto-sizes CSS fix
    - `wp_speculation_rules_configuration` → null
    - Remove `wp_generator`, `rsd_link`, `wlwmanifest_link`, `wp_shortlink_wp_head`, `rest_output_link_wp_head`, feed links, adjacent-post links, and oEmbed discovery and host JS
    - All of this runs only for non-admin requests
    - _Requirements: 11.1, 11.2, 11.4_
  - [ ]* 13.2 Write a front-end assets test
    - `wordpress/tools/test/frontend-assets.test.mjs` fetches every WordPress page from `parity/pages.json`
    - No jQuery, emoji, block library, `wp-embed`, `speculationrules` or CF7 assets; exactly two Campton preloads and no other font; every script has `defer`; stylesheet and script handles equal `rx_asset_map()` for the page; every `img` has width, height and the same `loading` as the PHP page
    - _Requirements: 9.3, 11.1, 11.2, 11.3, 11.4, 11.5_

- [ ] 14. SEO and schema (Requirement 12)
  - [x] 14.1 Add the Yoast head filters in `inc/seo.php`
    - Topic views: `wpseo_title` ("{Topic} guides for medical stores %%sep%% %%sitename%%" resolved), `wpseo_canonical` and `wpseo_opengraph_url` → `/blog/{topic}/`
    - Pricing: `wpseo_metadesc` built from `rx_pricing_meta_bits()`
    - `wpseo_locale` → `en_IN`; `wpseo_debug_markers` → false
    - _Requirements: 12.1, 12.5_
  - [x] 14.2 Add the schema pieces with `wpseo_schema_graph` in `inc/seo.php`
    - Article `author` → Organization `@id`, and Person nodes removed
    - FAQPage from `rx_faq_schema()` on the features hub, feature pages, FAQ page and posts
    - Blog + BlogPosting on the blog hub and topic views
    - SoftwareApplication with one Offer per non-custom plan on pricing, omitted when pricing is null
    - _Requirements: 12.3, 12.4, 12.5_
  - [x] 14.3 Add the logo attachment step to `wordpress/import/import.php`
    - Copy the theme's `assets/img/logo-mark.svg` into uploads once (found by `_rx_source`), `wp_insert_attachment()` with mime `image/svg+xml` and width/height from the viewBox
    - Set Yoast `company_logo` and `company_logo_id` through `WPSEO_Options::set()`
    - _Requirements: 12.3_
  - [ ]* 14.4 Write an example test for SEO output
    - `wordpress/tools/test/seo.test.mjs`
    - For every pair in `parity/pages.json`, `<title>` and meta description equal the PHP page
    - `noindex` on privacy, terms, refund-policy and cookie-policy only
    - The set of JSON-LD `@type` values per page equals the expected set; Organization name = Site Title; no Person; no duplicate meta or type
    - `sitemap_index.xml` lists all pages and posts
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ] 15. Check before handover (Requirement 13)
  - [~] 15.1 Write `wordpress/tools/parity/pages.json`
    - 34 pairs `{ name, php, wp, expectTypes, motion }`: home, pricing, features hub, 8 feature pages, switch, faq, about, contact, support, demo, 5 legal pages, blog hub, 5 topic views and 6 posts. Plus the WordPress-only 404
    - PHP URLs on `http://localhost/pharmacy/`, WordPress URLs on `http://192.168.0.25/pharmacy-wp/`
    - _Requirements: 13.1, 13.4_
  - [~] 15.2 Write `wordpress/tools/parity/check.mjs`
    - Implements checks 1–8 of design Testing Strategy: status and console/page errors; screenshots at 1440 and 375 with a pixelmatch percentage; visible text, section order, `data-confirm`, image and asset diffs; `scrollWidth` at 360, 375, 390, 414, 768 and 1024; title, description, robots, duplicate meta and JSON-LD validity and types; "Healthray" scan; local link and anchor crawl with external links only listed; network log
    - Calls `runInteractions()`. Reads new `debug.log` lines per page
    - Writes `tools/out/report.md` (per-page table + list of differences) and screenshots to `tools/out/`
    - _Requirements: 10.3, 10.4, 12.5, 13.1, 13.2, 13.3, 13.4_
  - [~] 15.3 Run the full check and share the report
    - Switch on `WP_DEBUG` and `WP_DEBUG_LOG` (with `WP_DEBUG_DISPLAY` false) in `pharmacy-wp/wp-config.php`, run `npm run parity` and `npm test`, then restore `wp-config.php` exactly
    - Run `php -l` on every PHP file under `wordpress/`, `baseline.ps1 -Verify` and `git status`
    - Share the per-page results from `report.md` with the user
    - _Requirements: 1.1, 1.2, 2.6, 13.1, 13.2, 13.3, 13.4_
  - [~] 15.4 Fix the differences and re-run
    - Fix template, part or data-file differences found in 15.3, re-import if data changed, and re-run until only differences the user accepts remain (for example the new 404 page and `/blog/{topic}/` URLs)
    - _Requirements: 5.1, 10.1, 10.3, 13.2_

- [~] 16. Final checkpoint: ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP. Core tasks are never optional.
- Every task references requirement clauses for traceability. Property tests implement design Properties 1–5, each as a single fast-check test with at least 100 runs and a tag comment `// Feature: wordpress-migration, Property N: <title>`.
- The only deletion is the WordPress sample content (task 6.2). The only temporary edit to the WordPress install is the `WP_DEBUG` switch in task 15.3, which is restored afterwards.
- Never write to PHP_Site files or to `labora-wp`. Never call any live site or the Healthray WordPress connection.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1"] },
    { "id": 1, "tasks": ["1.2", "1.3", "2.1", "2.2", "2.3", "2.8"] },
    { "id": 2, "tasks": ["1.4", "2.4", "2.6", "2.7", "4.3", "6.1", "7.1", "7.2", "7.3", "7.4", "7.5", "7.6", "8.1", "9.1"] },
    { "id": 3, "tasks": ["1.5", "2.5", "2.9", "4.1", "6.2", "7.7", "8.2", "8.3", "8.4", "9.2", "9.3", "9.4"] },
    { "id": 4, "tasks": ["3.2", "4.2", "6.3", "6.4", "6.5", "6.6", "6.7", "7.8", "8.5", "11.2", "12.1", "14.1"] },
    { "id": 5, "tasks": ["3.1", "3.3", "3.4", "3.5", "3.6", "3.7", "3.8", "7.9", "8.6", "8.7", "13.1", "14.2", "14.3"] },
    { "id": 6, "tasks": ["6.8", "11.1", "15.1"] },
    { "id": 7, "tasks": ["4.4", "8.8", "9.5", "12.2", "12.3", "13.2", "14.4", "15.2"] },
    { "id": 8, "tasks": ["15.3"] },
    { "id": 9, "tasks": ["15.4"] }
  ]
}
```
