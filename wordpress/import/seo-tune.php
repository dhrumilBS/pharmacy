<?php
/**
 * Tunes the SEO titles and descriptions of the pages and posts, and sets the
 * default social share image, in the local WordPress install.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\seo-tune.php             apply
 *     D:\xampp\php\php.exe wordpress\import\seo-tune.php --dry-run   report only
 *
 * Re-runnable. What it does:
 *
 *  1. Titles: at most 51 characters before " | {site}" (60 in all), so Google
 *     does not cut them. Descriptions: 120-160 characters. The values are in
 *     $tune below, keyed by page path (blog posts: "blog/{slug}").
 *  2. Writes them to Yoast (`_yoast_wpseo_title`, `_yoast_wpseo_metadesc`) and
 *     rebuilds the indexable, and edits the same strings in content/pages and
 *     content/posts so a re-run of import.php does not bring the old ones back.
 *     The Pricing description is left alone: it is built live from the plans.
 *  3. Uploads theme/assets/img/og-default.png (1200x630) to the media library
 *     once and sets it as Yoast's default social image, used by Facebook,
 *     LinkedIn, WhatsApp, X and any page without an image of its own.
 *
 * Local only (lib/guard.php).
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

$dry = in_array( '--dry-run', $argv ?? array(), true ) || in_array( '-n', $argv ?? array(), true );

rx_bootstrap_wp();
rx_assert_local();

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

const RX_SEO_SUFFIX = ' %%sep%% %%sitename%%';

/** path => [ title (before the site name), description or null to keep ]. */
$tune = array(
	''                          => array( 'Pharmacy software: billing, expiry and stock', 'Pharmacy software for medical stores: batch-wise FEFO billing, expiry reports, CSV and OCR purchase import and GST reports. Book a free demo.' ),
	'features'                  => array( 'Pharmacy software features: billing to reports', 'One browser-based system for a medical store: FEFO counter billing, expiry and stock control, bill import, GST, reports, retention and staff security.' ),
	'features/billing'          => array( 'Medical store billing software with FEFO batches', 'Pharmacy billing for the counter rush: search by brand or generic, nearest-expiry batch picked first, cash, card, UPI or credit, bills on WhatsApp.' ),
	'features/expiry'           => array( 'Pharmacy expiry software: batches and reports', 'Track every medicine by batch and expiry: item-wise or supplier-wise expiry reports, first-expiry-first-out billing and purchase returns.' ),
	'features/inventory'        => array( 'Pharmacy inventory software: stock alerts, reorder', 'Medical store stock control: min and max levels per item, stock alerts, a short book with purchase orders, non-moving items and barcode labels.' ),
	'features/purchase'         => array( 'Pharmacy purchase software: CSV and OCR bill import', 'Stop typing distributor bills line by line: import by CSV or OCR, record scheme and free quantity, see PTR and margin on save, keep supplier dues.' ),
	'features/reports'          => array( 'Pharmacy reports: dashboard, margins, daily report', 'Reports without standing at the counter: an executive dashboard, 25+ reports including margin and non-moving stock, and a daily WhatsApp report.' ),
	'features/gst'              => array( 'GST billing software for medical stores', 'HSN and GST rate on every bill, GST and bill-wise tax reports for your CA, ledgers and vouchers, with Tally XML export on Enterprise.' ),
	'features/retention'        => array( 'Pharmacy customer retention: reminders and loyalty', 'Bring regular patients back: bills on WhatsApp, SMS or email, medicine reminders, loyalty cards, and patient history and dues on every bill.' ),
	'features/security'         => array( 'Pharmacy security: staff roles and two-step login', 'Control who does what at your medical store: a login per staff member, roles, two-step OTP login, IP whitelisting and an activity report.' ),
	'about'                     => array( 'About us: the team behind our pharmacy software', null ),
	'support'                   => array( 'Pharmacy software support: phone and help centre', null ),
	'demo'                      => array( 'Book a free pharmacy software demo', null ),
	'data-security'             => array( 'Data and security: how we protect store data', 'How we protect store and patient data: individual logins, roles, two-step OTP login, IP whitelisting, activity reports, ABDM consent and incident handling.' ),
	'pricing'                   => array( 'Pricing: pharmacy software plans from free', null ),
	'switch'                    => array( 'Switching from your current pharmacy software', 'Change pharmacy software without stopping a bill: set up free, import items and stock from CSV, train staff with walkthroughs, go live on your day.' ),
	'faq'                       => array( 'FAQ: pharmacy software questions, answered', 'Straight answers on the free plan, switching, FEFO billing, expiry, purchase import, GST and Tally, hospital pharmacy, security and pricing.' ),
	'privacy'                   => array( null, 'How our pharmacy software collects, uses, shares and protects store, staff, patient and payment information, and the rights you may have.' ),
	'thank-you'                 => array( null, 'We have your request and will call you to book your demo. Keep billing as you do today; nothing changes until you choose.' ),
	'blog'                      => array( 'Practical articles for running a medical store', 'Practical articles for pharmacy owners: cutting expiry loss, setting stock levels, checking distributor bills, switching safely and staff logins.' ),
	'blog/cut-expiry-loss'      => array( 'Cut expiry loss in a medical store: weekly routine', 'Expiry is the leak most owners never measure. A simple weekly routine, the maths behind it, and what to do with each near-expiry batch.' ),
	'blog/switch-pharmacy-software' => array( 'Switch pharmacy software without stopping billing', 'The real risk in switching is the first week at the counter. A week-by-week plan to run both systems and go live on your own day.' ),
	'blog/min-max-stock'        => array( 'Set minimum and maximum stock for a medical store', 'Too little stock loses the sale; too much ties up cash. A plain rule for setting levels, with worked numbers for fast and slow movers.' ),
	'blog/desktop-vs-browser'   => array( 'Desktop vs browser-based pharmacy software', 'Weighing a switch? Where your data lives, what happens when a PC fails, how updates and pricing work, and what to ask any vendor.' ),
	'blog/staff-logins-roles'   => array( 'Staff logins and roles: pharmacy security checklist', null ),
);

/**
 * Finds a page or post by path ('' = front page).
 *
 * @param string $path Path such as 'features/billing' or 'blog/min-max-stock'.
 */
function rx_seo_find( string $path ): ?WP_Post {
	if ( '' === $path ) {
		$id = (int) get_option( 'page_on_front' );
		return $id ? get_post( $id ) : null;
	}
	$page = get_page_by_path( $path );
	if ( $page ) {
		return $page;
	}
	$posts = get_posts(
		array(
			'name'           => basename( $path ),
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		)
	);
	return $posts ? $posts[0] : null;
}

/**
 * Replaces a string in the import content files (pages and posts).
 *
 * @param string $old Old text.
 * @param string $new New text.
 * @return int Files changed.
 */
function rx_seo_patch_content( string $old, string $new ): int {
	$changed = 0;
	foreach ( array_merge( glob( __DIR__ . '/content/pages/*.php' ), glob( __DIR__ . '/content/posts/*.php' ) ) as $file ) {
		$src = (string) file_get_contents( $file );
		$old_e = addcslashes( $old, "'\\" );
		$new_e = addcslashes( $new, "'\\" );
		if ( '' !== $old && false !== strpos( $src, "'" . $old_e . "'" ) ) {
			file_put_contents( $file, str_replace( "'" . $old_e . "'", "'" . $new_e . "'", $src ) );
			++$changed;
		}
	}
	return $changed;
}

foreach ( $tune as $path => $pair ) {
	$post = rx_seo_find( $path );
	if ( ! $post ) {
		echo str_pad( '/' . $path, 36 ) . 'NOT FOUND' . PHP_EOL;
		continue;
	}
	list( $title, $desc ) = $pair;
	$notes                = array();

	if ( null !== $title ) {
		$new_title = $title . RX_SEO_SUFFIX;
		$old_title = (string) get_post_meta( $post->ID, '_yoast_wpseo_title', true );
		if ( mb_strlen( $title ) > 51 ) {
			$notes[] = 'TITLE TOO LONG (' . mb_strlen( $title ) . ')';
		}
		if ( $old_title !== $new_title ) {
			if ( ! $dry ) {
				update_post_meta( $post->ID, '_yoast_wpseo_title', $new_title );
				if ( '' !== $old_title ) {
					rx_seo_patch_content( $old_title, $new_title );
				}
			}
			$notes[] = 'title';
		}
	}
	if ( null !== $desc ) {
		$old_desc = (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
		$len      = mb_strlen( $desc );
		if ( $len < 120 || $len > 160 ) {
			$notes[] = "DESC LENGTH $len";
		}
		if ( $old_desc !== $desc ) {
			if ( ! $dry ) {
				update_post_meta( $post->ID, '_yoast_wpseo_metadesc', $desc );
				if ( '' !== $old_desc ) {
					rx_seo_patch_content( $old_desc, $desc );
				}
			}
			$notes[] = 'desc';
		}
	}
	if ( ! $dry && $notes ) {
		wp_update_post( array( 'ID' => $post->ID ) ); // Lets Yoast rebuild its indexable from the new meta.
	}
	echo str_pad( '/' . $path, 36 ) . ( $notes ? implode( ', ', $notes ) : 'unchanged' ) . PHP_EOL;
}

/*
 * Default social image.
 */
$file = dirname( __DIR__ ) . '/themes/pharmacy/assets/img/og-default.png';
if ( ! file_exists( $file ) ) {
	echo 'og image   MISSING ' . $file . PHP_EOL;
} elseif ( $dry ) {
	echo 'og image   would upload and set as the Yoast default' . PHP_EOL;
} elseif ( class_exists( 'WPSEO_Options' ) ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_rx_og_default',
			'meta_value'     => '1',
		)
	);
	$att_id   = $existing ? (int) $existing[0] : 0;
	if ( ! $att_id ) {
		$tmp = wp_tempnam( 'og-default.png' );
		copy( $file, $tmp );
		$att_id = media_handle_sideload( array( 'name' => 'og-default.png', 'tmp_name' => $tmp ), 0, 'Pharmacy software for medical stores' );
		if ( is_wp_error( $att_id ) ) {
			fwrite( STDERR, 'og image   FAILED: ' . $att_id->get_error_message() . PHP_EOL );
			exit( 1 );
		}
		update_post_meta( $att_id, '_rx_og_default', '1' );
		update_post_meta( $att_id, '_wp_attachment_image_alt', 'A medical store billing screen next to the headline: pharmacy software for medical stores' );
	}
	$url = (string) wp_get_attachment_url( $att_id );
	WPSEO_Options::set( 'og_default_image', $url );
	WPSEO_Options::set( 'og_default_image_id', $att_id );
	WPSEO_Options::set( 'opengraph', true );
	WPSEO_Options::set( 'twitter', true );
	echo "og image   attachment #$att_id set as the default: $url" . PHP_EOL;
}

echo ( $dry ? 'Dry run: nothing written.' : 'Done.' ) . PHP_EOL;
