# Requirements Document

## Introduction

Convert the existing PHP marketing site (`d:\xampp\htdocs\pharmacy\`, served at `/pharmacy/`) into a WordPress theme for the local install the user already set up at `d:\xampp\htdocs\pharmacy-wp\`. This is a straight conversion: same pages, same sections, same copy, same images, same design and same behaviour, rebuilt the WordPress way. No redesign and no new features. The PHP site stays exactly as it is.

**Decisions already made by the user**
- WordPress is installed and set up by the user (database `wp_pharmacy`, admin `pharmacy`, Site Title "Rxtiva"). It is not reinstalled or reconfigured beyond what this spec needs.
- Plugins: Yoast SEO, Secure Custom Fields (SCF) and Contact Form 7. CF7 stays installed but unused.
- Parent theme `pharmacy` and child theme `pharmacy-child`, kept in this repository under `wordpress/themes/`.
- Content fields are added through SCF, not hard-coded.
- Posts live at `/blog/{post-slug}/` and topic views at `/blog/{topic-slug}/`, without WordPress categories.
- Yoast handles titles, meta descriptions, social tags, the sitemap and its own schema. The theme adds only the schema Yoast doesn't produce (FAQPage, Blog, SoftwareApplication).
- Forms keep sending nothing. Archive pages are not migrated. Everything stays on this PC.

## Glossary

- **PHP_Site**: The existing site in `d:\xampp\htdocs\pharmacy\`.
- **WP_Site**: The WordPress site in `d:\xampp\htdocs\pharmacy-wp\`.
- **Theme**: The parent theme `pharmacy` and child theme `pharmacy-child` together.
- **SCF**: The Secure Custom Fields plugin.
- **Site_Settings**: The SCF options page holding what `partials/config.php` holds today (contacts, links, pricing API settings, hero pattern switch).
- **Pricing_API**: The public pricing endpoint the PHP_Site already reads.
- **Editor**: A logged-in WordPress user who edits content.

## Requirements

### Requirement 1: Keep the PHP site and stay local

**User Story:** As the site owner, I want the conversion to leave the current site alone and stay on my PC, so that nothing existing or live can break.

#### Acceptance Criteria

1. THE conversion SHALL leave every existing PHP_Site file unchanged.
2. THE conversion SHALL add new files only under `wordpress/` in this repository, plus the theme links and plugins in the WP_Site.
3. THE conversion SHALL work only on this PC and SHALL NOT use any live website, production database or the configured Healthray WordPress connection.
4. THE WP_Site SHALL make no outside request except the same read-only Pricing_API request the PHP_Site makes.
5. THE conversion SHALL use the WordPress install, database and admin account the user created, without reinstalling or changing them.

### Requirement 2: Parent and child theme

**User Story:** As a developer, I want a parent theme with a child theme, so that site-specific changes survive updates to the main theme.

#### Acceptance Criteria

1. THE Theme SHALL consist of a parent theme `pharmacy` and a child theme `pharmacy-child`, with the child theme active.
2. THE parent theme SHALL hold all templates, styles, scripts and logic; THE child theme SHALL start almost empty, ready for site-specific overrides.
3. THE Theme source SHALL live in this repository under `wordpress/themes/` and SHALL be linked into `pharmacy-wp/wp-content/themes/`.
4. THE repository SHALL track only theme source: no WordPress core, plugins, uploads, `wp-config.php` or database files.
5. THE Theme SHALL include the site's CSS, JavaScript, Campton fonts and images as theme assets.
6. THE Theme SHALL follow WordPress coding standards: escaped output, `wp_enqueue_style` and `wp_enqueue_script` for assets, translatable strings, and no PHP warnings with `WP_DEBUG` on.

### Requirement 3: Theme files

**User Story:** As a developer, I want the usual WordPress template files, so that the theme is easy to maintain.

#### Acceptance Criteria

1. THE Theme SHALL render the header from `header.php` and the footer from `footer.php`.
2. THE Theme SHALL render the home page from `front-page.php`.
3. THE Theme SHALL render the blog hub from the page set as the WordPress posts page ("Blog"), and each post from `single.php`.
4. THE Theme SHALL provide a `404.php` in the site's design system, with a heading, a short message and links to the home page, features, pricing and the blog.
5. THE Theme SHALL provide one shared page template per repeated layout: a Feature template (the 8 feature pages), a Legal template (privacy, terms, refund, cookies, data & security), plus templates for Pricing, Features hub, Switch, FAQ, About, Contact, Support and Demo.
6. THE Theme SHALL keep repeated parts (page hero, CTA band, FAQ list, demo form, demo slip, estimator, product tour, trusted strip, problems inbox) as template parts.

### Requirement 4: Header and mega menu

**User Story:** As a visitor, I want the same header and mega menu, so that I find my way around as before.

#### Acceptance Criteria

1. THE header SHALL output the main navigation with `wp_nav_menu()`, rendering the same mega menu: Features (two groups, "Run the counter" and "Run the business", with icon and one-line description per item, plus the promo panel), Solutions, Pricing, Switch and Resources.
2. THE menu item icon and description SHALL be SCF fields on each menu item.
3. THE header SHALL keep Log in, Start free and Book a free demo, the active state per page, the full-screen mobile menu below 1024px, and Escape to close.
4. THE footer columns and mobile sticky bar SHALL use WordPress menus and Site_Settings, with the same links as today.
5. Links to pages not built yet (solutions, calculator, checklist) SHALL keep pointing to today's home page anchors.

### Requirement 5: Pages and content

**User Story:** As the owner, I want every page and section moved across exactly, so that nothing is lost.

| PHP page | WordPress | Template |
|---|---|---|
| `index.php` | Front page | `front-page.php` |
| `pricing.php` | Page "Pricing" | Pricing |
| `features/index.php` | Page "Features" | Features hub |
| 8 `features/*.php` | 8 child pages of Features | Feature |
| `switch.php`, `faq.php`, `about.php`, `contact.php`, `support.php`, `demo.php` | One page each | Own template |
| `privacy.php`, `terms.php`, `refund-policy.php`, `cookie-policy.php`, `data-security.php` | One page each | Legal |
| `blog/index.php` | Page "Blog" (posts page) | Blog hub |
| 6 `blog/*.php` posts | 6 posts | `single.php` |

#### Acceptance Criteria

1. THE WP_Site SHALL have every page and post in the table above, with the same sections in the same order, the same copy and the same images as the PHP_Site.
2. THE WP_Site SHALL keep the home page sections in order: hero with demo slip, trusted logos, product tour, "Sound familiar?" inbox, expiry estimator, day at the pharmacy, feature tabs, switching, who it's for, buyer's checklist, support, FAQ and the demo section.
3. THE WP_Site SHALL keep the review mode (`?review`) and every unconfirmed item flagged as today.
4. THE content SHALL be moved by an import script kept in the repository, which can be run again without creating duplicates.
5. THE WP_Site SHALL use page URLs at `/{page-slug}/` with today's names (for example `/pricing/`, `/features/billing/`, `/refund-policy/`).

### Requirement 6: Editable content with SCF

**User Story:** As an Editor, I want to change copy in the WordPress admin, so that small changes need no developer.

#### Acceptance Criteria

1. THE Theme SHALL get page content from SCF fields, with templates holding only layout markup.
2. THE Theme SHALL provide Site_Settings for contacts, phone numbers, links, social profiles, the hero pattern switch and the pricing API settings.
3. THE brand name SHALL come from the WordPress Site Title.
4. THE Feature template SHALL have one field group that covers every section of a feature page; THE Legal template SHALL have one field group for its sections.
5. THE post field group SHALL hold the summary line, topic, read time, cover, featured flag and FAQ, and THE blog article pieces (notes, tables, comparisons, worked sums, rules, checklists, steps, CTAs) SHALL be editable through SCF.
6. THE FAQ items SHALL have a "confirmed" switch that controls the unconfirmed flag and the FAQ schema.
7. THE SCF field groups SHALL be saved as JSON files in the Theme, so they are versioned in Git.
8. Live values inside editable text (plan names, prices, phone numbers, internal links) SHALL be inserted with shortcodes, so they stay live.

### Requirement 7: Blog

**User Story:** As a reader, I want the blog to work as today, so that I can browse guides by topic.

#### Acceptance Criteria

1. THE blog hub SHALL keep the topic links with counts, the featured guide, the "Start here" path, the card grid and the tools strip.
2. THE posts SHALL be at `/blog/{post-slug}/` and topic views at `/blog/{topic-slug}/`, with topics stored as an SCF field, not WordPress categories.
3. THE single post SHALL keep the reading progress bar, the "On this page" list, copy link, print checklist, byline, FAQ and "Keep reading" posts.
4. A post slug and a topic slug SHALL never be the same, and an unknown `/blog/` URL SHALL show the 404 page.

### Requirement 8: Live pricing

**User Story:** As the owner, I want prices to keep coming from the backend, so that a price change still updates the site by itself.

#### Acceptance Criteria

1. THE Theme SHALL read plans, prices, terms, discounts, credits and plan labels from the Pricing_API, with a 5-minute cache and a saved snapshot as backup, as today.
2. THE pricing page SHALL keep the term switch, plan cards with the "Most popular" tab, the collapsible comparison table and the credits table.
3. IF the Pricing_API, the cache and the snapshot all fail, THEN THE page SHALL still load and show a short notice where the prices would be.
4. THE Theme SHALL never hard-code a price.

### Requirement 9: Scripts, interactions and forms

**User Story:** As a visitor, I want everything that moves or responds today to work the same, so that the site feels unchanged.

#### Acceptance Criteria

1. THE Theme SHALL keep every interaction: menus, feature tabs, FAQ accordion, estimator, pricing term switch and table toggles, product tour (play, pause, full screen, autoplay rules, `?tour-at=`), and the blog post tools.
2. THE demo slip and demo form SHALL validate as today and send nothing anywhere.
3. THE WP_Site SHALL have no Contact Form 7 form on any page, and no CF7 script or style on the front end.

### Requirement 10: Design, accessibility and responsive layout

**User Story:** As the owner, I want the site to look and behave exactly as approved, so that the conversion changes nothing visible.

#### Acceptance Criteria

1. THE WP_Site SHALL use the same stylesheets, class names, colours, Campton fonts and layouts as the PHP_Site, including the latest approved UI fixes.
2. THE WP_Site SHALL keep today's accessibility features: skip link, keyboard-friendly tabs and menus, FAQ buttons with `aria-expanded`, visible focus and reduced-motion support.
3. THE WP_Site SHALL have no sideways scrolling at 360, 375, 390, 414, 768 and 1024px wide.
4. THE word "Healthray" SHALL appear nowhere in visible copy, titles or schema.

### Requirement 11: Performance

**User Story:** As a visitor on a slow phone, I want pages as light as today, so that they load quickly.

#### Acceptance Criteria

1. THE WP_Site SHALL load no jQuery on the front end.
2. THE Theme SHALL load JavaScript with `defer`, and each page-specific stylesheet and script only on the pages that need it.
3. THE Theme SHALL preload Campton 400 and 700 and load no other web font.
4. THE Theme SHALL switch off WordPress front-end extras the site doesn't use (emoji script, block library styles, embed script).
5. THE Theme SHALL output images with width, height and lazy loading as today, with product screens kept as sharp SVG files.

### Requirement 12: SEO and schema

**User Story:** As the owner, I want search engines to see clean, complete data, so that the move doesn't hurt SEO.

#### Acceptance Criteria

1. Yoast SHALL output each page's title, meta description, robots, canonical and social tags, and the XML sitemap, with each page's title and description the same as today.
2. THE legal pages (privacy, terms, refund, cookies) SHALL stay `noindex`, as today.
3. Yoast SHALL output its schema (website, organisation, web page, breadcrumbs, article), with the organisation and article author named after the brand.
4. THE Theme SHALL add only schema Yoast doesn't produce: FAQPage (confirmed answers only) on pages with FAQs, Blog on the blog hub, and SoftwareApplication with live plan prices on the pricing page.
5. THE WP_Site SHALL output each meta tag and schema type once per page, with no duplicates between Yoast and the Theme.

### Requirement 13: Check before handover

**User Story:** As the owner, I want proof that the WordPress pages match the PHP pages, so that I can approve the conversion.

#### Acceptance Criteria

1. THE conversion SHALL check every page and post loads with no PHP error and no browser console error.
2. THE conversion SHALL compare each WordPress page with its PHP page in screenshots at desktop and phone width, and list any difference.
3. THE conversion SHALL check that every link works and that structured data is valid.
4. THE conversion SHALL report the results to the user page by page.