<?php
/**
 * Replaces the six guides from the first import (SCF `body` posts) with new block-editor blogs on the same topics,
 * gives every blog a playful, category-wise feature image, and keeps the Blog page's "Start here" list pointing at live posts.
 *
 * - Deleted: cut-expiry-loss, min-max-stock, check-distributor-bill, switch-pharmacy-software, desktop-vs-browser,
 *   staff-logins-roles (the old content can be re-imported from import/content/posts/ if it is ever wanted again).
 *   The component sample (guide-components-sample) is not touched.
 * - Created: cut-expiry-loss, min-max-stock, switch-pharmacy-software, staff-logins-roles, desktop-vs-browser.
 *   check-distributor-bill is covered by check-distributor-bill-before-entry (blog-distributor-bill.php), which is kept.
 * - Images: content/images/gen-playful-images.py makes a flat "sticker" illustration per topic: cat-<topic>.png (the
 *   category image, also copied to the theme for the topic pages) and <slug>-feature.png (title + illustration, 2:1).
 *
 * Facts stay inside the verified list (PHARMACY-ICP-AND-UX-STRATEGY.md §0): no customer figures, no tax rates,
 * no compliance guarantees; numbers in examples are labelled as illustrative.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-playful-posts.php   (local install only, re-runnable)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';

rx_bootstrap_wp();
rx_assert_local();

require_once __DIR__ . '/lib/guide-blocks.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$img_dir = __DIR__ . '/content/images/';

/* ---- remove the old guides and their images ---------------------------------------------- */

foreach ( array( 'cut-expiry-loss', 'min-max-stock', 'check-distributor-bill', 'switch-pharmacy-software', 'desktop-vs-browser', 'staff-logins-roles' ) as $old ) {
	foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'name' => $old ) ) as $p ) {
		wp_delete_post( $p->ID, true );
		echo "deleted post {$old} (#{$p->ID})" . PHP_EOL;
	}
	foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $old . '-feature' ) ) as $a ) {
		wp_delete_attachment( $a, true );
	}
}

/** Uploads an image once per key (found again by `_rx_src`), replacing the file on re-runs. */
function rx_pp_attachment( string $file, string $key, string $title, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $key ) );
	$id    = $found ? (int) $found[0] : 0;
	if ( ! $id ) {
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $title );
		if ( is_wp_error( $id ) ) {
			fwrite( STDERR, 'Upload failed: ' . $id->get_error_message() . PHP_EOL );
			return 0;
		}
		update_post_meta( $id, '_rx_src', $key );
	} else {
		$path = get_attached_file( $id );
		copy( $file, $path );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $path ) );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	wp_update_post( array( 'ID' => $id, 'post_title' => $title ) );
	return (int) $id;
}

/** A closing "Start free" block, the same on every blog. */
$cta = sb( 'healthray/cta', array( 'kind' => 'panel', 'title' => 'Start free, and see what the plans add', 'text' => 'The Starter plan is free to use, with limits. There is no trial: it stays free, and you move up only if you need more. The pricing page always shows the current limits.' ) );

/* ---- the five new blogs --------------------------------------------------------------------- */

$posts = array(

	'cut-expiry-loss'          => array(
		'title'    => 'How to cut expiry loss in a medical store: a 15-minute weekly routine',
		'topic'    => 'expiry',
		'date'     => '2026-10-14 10:00:00',
		'featured' => true,
		'dek'      => 'Expiry is the leak most owners never put a number on. A simple weekly routine, the maths behind it, and what to do with each near-expiry batch.',
		'seo_desc' => 'A 15-minute weekly expiry routine for a medical store: what to check, what to return, what to move to the front, and what a month of expiry can cost.',
		'content'  => implode(
			"\n\n",
			array(
				sb( 'healthray/takeaways', array(), sli( array(
					'Expiry loss is money you already paid, sitting on the shelf. Most stores have never added it up.',
					'A <strong>15-minute check once a week</strong> catches most of it while a batch can still go back to the distributor.',
					'What decides the outcome is the <strong>return window</strong>, so group near-expiry stock by supplier and ask each one how long theirs is.',
				) ) ),
				sp( 'Nobody notices expiry on the day it happens. It shows up months later as a box you cannot sell and a distributor who will not take it back. The cure is not a bigger effort; it is a smaller, regular one.' ),
				sh( 'Why a weekly check beats a monthly one' ),
				sp( 'A monthly check takes an hour and still misses the batches that expire in between. A weekly check takes a quarter of that, and every batch is looked at while there is time to act. Pick a slow part of the week and keep the same slot.' ),
				sh( 'The routine, in four steps' ),
				sb( 'healthray/steps', array(),
					sb( 'healthray/step', array( 'title' => 'Open the stock expiry report for the next 60 days', 'when' => '3 minutes' ), sp( 'Set it to show every batch, not just the items you remember.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Group it by supplier', 'when' => '2 minutes' ), sp( 'A return goes back to one distributor at a time, so group the list the same way.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Mark what is inside the return window', 'when' => '5 minutes' ), sp( 'Anything the distributor will still take back goes on this week’s return list.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Move the rest to the front of the shelf', 'when' => '5 minutes' ), sp( 'Stock that cannot be returned must be sold first, so put it where hands go.' ) )
				),
				sh( 'What to do with each batch' ),
				stable(
					array( 'When it expires', 'What to do', 'Why' ),
					array(
						array( 'Within 30 days', 'Return it if the supplier will take it; if not, put it at the front and mention it at the counter.', 'This is the last chance to get the money back.' ),
						array( '31 to 60 days', 'Plan the return, and stop reordering until it has moved.', 'Reordering on top of old stock is how the pile grows.' ),
						array( 'Beyond 60 days', 'Leave it alone.', 'Time spent here is time not spent where it matters.' ),
					),
					'guide',
					'A simple way to sort near-expiry stock.'
				),
				sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'Return windows differ' ), sp( 'Each distributor sets its own. Ask every supplier once, write it down next to their name, and use that number, not a guess.' ) ),
				sh( 'What a month of expiry can cost' ),
				sp( 'Put a number on it once and the routine stops feeling like a chore. This is an example, so replace the figures with your own.' ),
				sb( 'healthray/sum', array( 'title' => 'Batches lost to expiry in a month', 'resultLabel' => 'Lost in a month', 'resultValue' => '₹5,100', 'note' => 'Illustrative numbers. Use your own count and purchase value.' ),
					sb( 'healthray/sum-line', array( 'label' => 'Batches that expired unsold', 'value' => '6', 'op' => 'none' ) ) . "\n" .
					sb( 'healthray/sum-line', array( 'label' => 'Average purchase value of a batch', 'value' => '₹850', 'op' => '×' ) )
				),
				sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Let the report do the looking' ), sp( 'The stock expiry report in the software can show items or suppliers, so the grouping step takes seconds instead of a page of notes.' ) ),
				$cta,
			)
		),
	),

	'min-max-stock'            => array(
		'title'    => 'How to set minimum and maximum stock for a medical store',
		'topic'    => 'expiry',
		'date'     => '2026-10-02 10:00:00',
		'featured' => false,
		'dek'      => 'Too little and you lose the sale; too much and your cash sits on the shelf. A plain rule for setting levels, and how to keep them right.',
		'seo_desc' => 'How to set minimum and maximum stock levels for a medical store with a plain rule, a worked example and a monthly review.',
		'content'  => implode(
			"\n\n",
			array(
				sb( 'healthray/takeaways', array(), sli( array(
					'A <strong>minimum</strong> is the stock at which you reorder; a <strong>maximum</strong> is the most you should hold.',
					'Start with the 100 items you sell most. They are most of your sales and most of your cash.',
					'Review the numbers once a month: look at what ran out and what piled up, and move them.',
				) ) ),
				sp( 'Most stores order by feel. That works until a busy week, a delayed delivery or a medicine that suddenly sells. Levels written down take the guesswork out, and they are easier to hand to a new member of staff.' ),
				sh( 'Start with the items that matter' ),
				sp( 'You do not need levels for every item on day one. Take your top 100 sellers first and write a minimum and a maximum for each. The slow movers can follow later.' ),
				sh( 'The minimum' ),
				sp( 'The minimum has to last until the next delivery arrives. Work it out from how fast an item sells and how long your distributor takes.' ),
				sb( 'healthray/sum', array( 'title' => 'How many strips to keep', 'resultLabel' => 'Minimum stock', 'resultValue' => '14 strips', 'note' => 'Illustrative numbers. Use your own daily sales and delivery time.' ),
					sb( 'healthray/sum-line', array( 'label' => 'Daily sales', 'value' => '4 strips', 'op' => 'none' ) ) . "\n" .
					sb( 'healthray/sum-line', array( 'label' => 'Days to the next delivery', 'value' => '3 days', 'op' => '×' ) ) . "\n" .
					sb( 'healthray/sum-line', array( 'label' => 'Cushion, one extra day', 'value' => '4 strips', 'op' => '+' ) )
				),
				sh( 'The maximum' ),
				sp( 'The maximum is what you can sell before the batch expires, not what the distributor’s scheme tempts you to take. For an item that sells slowly, shelf life sets the limit, so the maximum is often lower than you expect.' ),
				stable(
					array( 'Item', 'Sells a day', 'Delivery', 'Minimum', 'Maximum' ),
					array(
						array( 'Paracetamol 650', '10 strips', '2 days', '24', '60' ),
						array( 'Azithromycin 500', '2 strips', '3 days', '8', '20' ),
						array( 'Pantoprazole 40', '4 strips', '3 days', '14', '36' ),
					),
					'guide-num',
					'Illustrative numbers, not a recommendation for any item.'
				),
				sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'A scheme is not a reason to hold more' ), sp( 'A free quantity is only a saving if you sell the extra before it expires. Check the maximum before you accept it.' ) ),
				sh( 'Keeping the numbers right' ),
				sp( 'Levels drift. A medicine that sells in monsoon will not sell in summer. Once a month, look at two lists: what ran out, and what piled up. Raise the minimum of the first, lower the maximum of the second.' ),
				sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Let the software remind you' ), sp( 'With minimum and maximum levels set in the software, stock alerts and a short book show what has fallen below its minimum, so the reorder list builds itself.' ) ),
				$cta,
			)
		),
	),

	'switch-pharmacy-software'  => array(
		'title'    => 'Switching pharmacy software without stopping billing: a four-week plan',
		'topic'    => 'switching',
		'date'     => '2026-10-09 10:00:00',
		'featured' => false,
		'dek'      => 'The real risk in switching is not the new software, it is the first week at the counter. A week-by-week plan to run both systems and go live on your own day.',
		'seo_desc' => 'A four-week plan for switching pharmacy software without stopping billing: load your items, run both systems, move one counter, then go live.',
		'content'  => implode(
			"\n\n",
			array(
				sb( 'healthray/takeaways', array(), sli( array(
					'The fear is not the new software. It is the day the counter stops and a queue forms.',
					'Never switch on a busy day. <strong>Run both systems side by side</strong> and move one counter at a time.',
					'Go live on a day you choose, after the stock and the totals have agreed for a few days.',
				) ) ),
				sp( 'Most owners who put off switching are not afraid of learning something new. They are afraid of billing stopping for a day. The way around that is to make the switch slow and boring.' ),
				sh( 'Week by week' ),
				sb( 'healthray/steps', array(),
					sb( 'healthray/step', array( 'title' => 'Load your items and opening stock', 'when' => 'Week 1' ), sp( 'Prepare a CSV of your items and stock with batch and expiry. The new software takes it as initial data, gives you a sample sheet and shows any errors line by line. Check a few items against the shelf.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Run one counter alongside the old system', 'when' => 'Weeks 1 and 2' ), sp( 'Bill every sale in both for a few days. Compare the totals at the end of each day.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Move one counter', 'when' => 'Weeks 2 and 3' ), sp( 'Choose the quietest counter and the quietest day. Keep the old system there as a fallback for the first week.' ) ) . "\n" .
					sb( 'healthray/step', array( 'title' => 'Go live', 'when' => 'Week 4' ), sp( 'Once the stock and the totals have agreed for several days, move the rest and keep the old system, read-only, for reference.' ) )
				),
				sh( 'What to bring across' ),
				stable(
					array( 'What', 'How', 'Check' ),
					array(
						array( 'Items', 'CSV of your item master', 'Open a few items and compare with the shelf.' ),
						array( 'Opening stock', 'Quantity with batch and expiry', 'Count a shelf and compare.' ),
						array( 'Old reports', 'Export from the old system', 'Keep a copy; do not rely on re-keying it.' ),
					),
					'guide',
					'The three things most stores need on the first day.'
				),
				sb( 'healthray/note', array( 'kind' => 'caution', 'title' => 'Do not rely on help you have not been promised' ), sp( 'Assume you will prepare the CSV yourself. Ask your old vendor for an export early, because it can take longer than you expect.' ) ),
				sh( 'The end-of-day check' ),
				sp( 'During the parallel run, finish each day with the same short list.' ),
				sb( 'healthray/checklist', array( 'title' => 'End-of-day checks during the switch' ), sli( array(
					'Today’s totals match between the old and the new system.',
					'Three items counted on the shelf match the new stock.',
					'Every bill printed correctly at the counter.',
					'Any difference is written down, with its cause.',
				) ) ),
				$cta,
			)
		),
	),

	'staff-logins-roles'        => array(
		'title'    => 'Staff logins and roles: a security checklist for pharmacy owners',
		'topic'    => 'staff',
		'date'     => '2026-09-11 10:00:00',
		'featured' => false,
		'dek'      => 'Most store problems are not break-ins, they are shared passwords. How to set up logins and roles so you can see who did what.',
		'seo_desc' => 'A security checklist for pharmacy owners: one login per person, roles by job, removing leavers, and keeping the owner-only reports owner-only.',
		'content'  => implode(
			"\n\n",
			array(
				sb( 'healthray/takeaways', array(), sli( array(
					'Most problems at a counter come from a <strong>shared login</strong>, not an outsider.',
					'Give each person their own login and a role that fits the job. It takes minutes.',
					'Remove a leaver’s login the same day, and keep the owner-only reports owner-only.',
				) ) ),
				sp( 'When everyone uses the same login, you cannot tell who gave a discount, who edited a bill or who deleted an item. That is not about trust. It is about being able to answer a simple question later.' ),
				sh( 'One login for each person' ),
				sp( 'It feels slower for the first week and faster ever after. Each person signs in as themselves, so every bill and every change has a name next to it.' ),
				sh( 'Roles that fit the job' ),
				sp( 'Give people what their work needs and no more. A starting point:' ),
				stable(
					array( 'Role', 'Should be able to', 'Should not be able to' ),
					array(
						array( 'Owner', 'See everything, including reports and settings.', '—' ),
						array( 'Pharmacist', 'Bill, check items, manage stock.', 'Change roles or see the owner’s reports.' ),
						array( 'Counter staff', 'Bill and take payment.', 'Edit stock, rates or other people’s bills.' ),
						array( 'Accountant', 'Read reports and exports.', 'Bill or change stock.' ),
					),
					'guide',
					'A starting point; adjust it to how your store works.'
				),
				sb( 'healthray/note', array( 'kind' => 'note', 'title' => 'Roles depend on the plan' ), sp( 'Role management is part of the paid plans, and extra protections such as two-factor sign-in and IP whitelisting are on higher plans. The pricing page shows which plan includes which.' ) ),
				sh( 'A checklist to keep' ),
				sb( 'healthray/checklist', array( 'title' => 'Staff access checklist' ), sli( array(
					'Every person has their own login. Nobody shares one.',
					'Each login has a role that fits the job.',
					'Owner-only reports are visible only to the owner.',
					'A leaver’s login is removed on their last day.',
					'Once a month, look at who has access and remove what is not needed.',
				) ) ),
				sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Make it the first thing a new person does' ), sp( 'Create their login before they start. It tells them the rule on day one, and nobody has to borrow a password.' ) ),
				$cta,
			)
		),
	),

	'desktop-vs-browser'        => array(
		'title'    => 'Desktop pharmacy software or browser-based: what actually changes',
		'topic'    => 'buying',
		'date'     => '2026-09-18 10:00:00',
		'featured' => false,
		'dek'      => 'An honest comparison for owners weighing the switch: where the data lives, what happens when a PC fails, and how updates and pricing work.',
		'seo_desc' => 'Desktop or browser-based pharmacy software: where the data lives, what happens if a PC fails, updates, access from elsewhere and what stays the same at the counter.',
		'content'  => implode(
			"\n\n",
			array(
				sb( 'healthray/takeaways', array(), sli( array(
					'The biggest differences are <strong>where the data lives</strong> and <strong>what happens when a PC fails</strong>.',
					'The counter itself, with its printer and barcode reader, looks much the same either way.',
					'Ask both kinds of vendor the same five questions before you decide.',
				) ) ),
				sp( 'This is a comparison of two kinds of software, not of two products. Both can run a good medical store. What differs is who looks after the computer, the data and the updates.' ),
				sh( 'Side by side' ),
				stable(
					array( 'Question', 'Desktop software', 'Browser-based' ),
					array(
						array( 'Where does the data live?', 'On one PC, or a server in your store.', 'Online, with the provider.' ),
						array( 'If a PC fails', 'Restore from your last backup.', 'Sign in from another device.' ),
						array( 'Updates', 'Installed by hand, or by a visit.', 'Applied by the provider.' ),
						array( 'Using it away from the store', 'Usually not possible.', 'From any browser you sign in to.' ),
						array( 'Printers and barcode readers', 'Connected to the counter PC.', 'Connected to the counter PC.' ),
					),
					'guide',
					'A general comparison, not about one product.'
				),
				sh( 'What stays the same' ),
				sp( 'Billing still happens at a counter, with a printer and, if you use one, a barcode reader. Your staff still need training, and your stock still needs counting. Moving to a browser does not remove those jobs.' ),
				sh( 'Five questions for any vendor' ),
				sb( 'healthray/checklist', array( 'title' => 'Ask every vendor the same five' ), sli( array(
					'Where is my data stored, and who can see it?',
					'What happens to my records if I leave?',
					'How are updates delivered, and do they cost extra?',
					'What does renewal cost in year two and year three?',
					'What do I need at the counter to start billing?',
				) ) ),
				sb( 'healthray/note', array( 'kind' => 'verdict', 'title' => 'Verdict' ), sp( 'If a failed PC would stop your business, or you want to see the store from elsewhere, browser-based is the safer choice. If your store has no reliable connection, think carefully before you give up a local system.' ) ),
				$cta,
			)
		),
	),
);

$ids = array();
foreach ( $posts as $slug => $p ) {
	$data = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $p['title'],
		'post_name'     => $slug,
		'post_content'  => $p['content'],
		'post_date'     => $p['date'],
		'post_date_gmt' => get_gmt_from_date( $p['date'] ),
	);
	$id = wp_insert_post( wp_slash( $data ), true );
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, "FAILED {$slug}: " . $id->get_error_message() . PHP_EOL );
		continue;
	}
	$ids[ $slug ] = (int) $id;
	update_post_meta( $id, 'topic', $p['topic'] );
	update_post_meta( $id, 'dek', $p['dek'] );
	update_post_meta( $id, 'updated', substr( $p['date'], 0, 10 ) );
	if ( $p['featured'] ) {
		update_post_meta( $id, 'featured', 1 );
	}
	update_post_meta( $id, '_yoast_wpseo_metadesc', $p['seo_desc'] );
	echo "created {$slug} (#{$id})" . PHP_EOL;
}

/* ---- feature images: one per blog, made category-wise --------------------------------------- */

$alts = array(
	'cut-expiry-loss'                     => 'A calendar with a clock and a blister pack: a weekly expiry check',
	'min-max-stock'                       => 'A calendar with a clock and a blister pack: stock levels',
	'check-distributor-bill-before-entry' => 'A bill with a tick, a price tag and delivery boxes',
	'switch-pharmacy-software'            => 'Two browser windows with arrows swapping between them',
	'staff-logins-roles'                  => 'Three people with a key and a lock',
	'desktop-vs-browser'                  => 'A magnifying glass over a checklist',
	'hospital-pharmacy-software-ipd-opd'  => 'A magnifying glass over a checklist',
);
foreach ( $alts as $slug => $alt ) {
	$post = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'name' => $slug ) );
	if ( ! $post ) {
		echo "no post for image: {$slug}" . PHP_EOL;
		continue;
	}
	$post = $post[0];
	$old  = (int) get_post_thumbnail_id( $post->ID );
	$id   = rx_pp_attachment( $img_dir . $slug . '-feature.png', $slug . '-feature-playful', $post->post_title, $alt );
	if ( $id ) {
		set_post_thumbnail( $post->ID, $id );
		if ( $old && $old !== $id ) {
			wp_delete_attachment( $old, true );
		}
		delete_post_meta( $post->ID, '_rx_read_mins' );
		wp_update_post( array( 'ID' => $post->ID ) );
		echo "image ok: {$slug}" . PHP_EOL;
	}
}

/* ---- the Blog page's "Start here" list ------------------------------------------------------ */

$start = array_filter( array( $ids['desktop-vs-browser'] ?? 0, $ids['switch-pharmacy-software'] ?? 0, $ids['staff-logins-roles'] ?? 0 ) );
if ( $start ) {
	update_post_meta( 760, 'start_here', array_map( 'strval', array_values( $start ) ) );
	echo 'start here: ' . implode( ', ', $start ) . PHP_EOL;
}

// Category images, for the topic pages (copied into the theme).
foreach ( array( 'expiry', 'purchase', 'switching', 'staff', 'buying' ) as $topic ) {
	$dest = dirname( __DIR__ ) . '/themes/pharmacy/assets/img/topics/';
	if ( ! is_dir( $dest ) ) {
		mkdir( $dest, 0775, true );
	}
	copy( $img_dir . 'cat-' . $topic . '.png', $dest . $topic . '.png' );
}
echo 'category images copied' . PHP_EOL;
