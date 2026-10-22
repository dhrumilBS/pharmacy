<?php
/**
 * Creates or updates the long-form article "Running a multi-branch pharmacy: one system for every store"
 * (slug multi-branch-pharmacy-chains), written for this site on the same subject and outline as Healthray's article
 * "Multi-Branch Pharmacy Systems: Managing Chain Stores Seamlessly" (healthray.com): a summary, an introduction, the
 * challenges of running many stores, what to look for, records and rules, a rollout, a conclusion and a FAQ.
 * The words are original, not a copy.
 *
 * Built only from headings, paragraphs, plain lists, two notes and a FAQ (no checklist block and no steps block, by request).
 * Facts stay inside the verified product list (PHARMACY-ICP-AND-UX-STRATEGY.md §0): central admin with linked outlets, stock
 * transfer, reports across stores, roles, supplier rate contracts, Tally export, the executive dashboard, expiry reports, GST
 * reports, online orders and Schedule H/H1 tagging. No ABDM or controlled-drug claims for chains, no customer figures.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-multi-branch.php   (local install only, re-runnable)
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

$slug  = 'multi-branch-pharmacy-chains';
$title = 'Running a multi-branch pharmacy: one system for every store';

$faq = static function ( string $q, string $a ): string {
	return sb( 'healthray/faq-item', array( 'question' => $q ), sp( $a ) );
};

/** Uploads an image once per key (found again by `_rx_src`) and replaces the file on re-runs. */
function rx_mb_attachment( string $file, string $key, string $title, string $alt ): int {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_value' => $key ) );
	$id    = $found ? (int) $found[0] : 0;
	// A different file type (an illustration replaced by a photo or a render) gets a fresh attachment, so the file name matches its content.
	if ( $id && strtolower( pathinfo( (string) get_attached_file( $id ), PATHINFO_EXTENSION ) ) !== strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
		wp_delete_attachment( $id, true );
		$id = 0;
	}
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

/** A link to one of the site's pages, for a table cell. */
$lnk = static fn( string $key, string $label ): string => '<a href="' . esc_url( rx_url( $key ) ) . '">' . esc_html( $label ) . '</a>';

/* The article has no inner pictures any more (dropped by request): remove the ones uploaded earlier from the media library. */
foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_rx_src', 'meta_compare' => 'LIKE', 'meta_value' => $slug . '-inner-' ) ) as $rx_old_inner ) {
	wp_delete_attachment( $rx_old_inner, true );
}

$content = implode(
	"\n\n",
	array(
		sb( 'healthray/takeaways', array(), sli( array(
			'A pharmacy chain starts losing money when <strong>each store keeps its own stock, bills and reports</strong>, because nobody sees the whole business.',
			'One system for every store gives you one picture: the same items, the same roles, the same rules, and reports that cover every counter.',
			'The sensible way in is slow: start with one store, link the others one at a time, and check the numbers before you rely on them.',
		) ) ),

		/* ---- Introduction ---- */
		sp( 'Opening a second store feels like growth, and it is. A third and a fourth feel like proof that the idea works. What the owner does not see at first is how quietly the work changes. With one store you can stand at the counter and know. With five, you know only what somebody tells you, and what they tell you depends on how each store keeps its books.' ),
		sp( 'Most chains start the way most single stores run: a system at each counter, a register or a spreadsheet in the back, and a manager who knows the shelf by heart. That works for one location. Across several, it produces the same set of problems again and again. Stock piles up in one place and runs out in another. Each manager orders on their own, so the same medicine is bought at three different rates. Month-end means collecting files from every store and hoping they agree.' ),
		sp( 'None of this is a failure of the people. It is what happens when stores do not share one picture. This article walks through where a chain loses money, what a multi-store system should actually give you, how to think about records and rules without panic, and how to bring several stores onto one system without stopping a single bill.' ),

		/* ---- Challenges ---- */
		sh( 'Why running many stores is hard' ),
		sp( 'The difficulties of a chain are not exotic. They are the ordinary difficulties of a single store, multiplied by the number of stores and made harder to see. Here are the five we hear about most.' ),

		sh( 'Stock piles up in one store and runs out in another', 3 ),
		sp( 'Demand is never even. A cough syrup sells in the store near the school and sits untouched in the one near the office. Without a way to see stock across stores, nobody notices until the shelf in one is empty and the shelf in another is heading for its expiry date. The first is a lost sale. The second is money you have already paid, going to waste. Both come from the same cause: each store only knows about itself.' ),

		sh( 'Every store orders for itself', 3 ),
		sp( 'When each manager calls their own distributor, the chain pays for it in three ways. Rates differ for the same item, because nobody is comparing. Schemes and free quantities are accepted store by store, so the saving is real in one place and an overstock in another. And when something runs out, an emergency order at a worse rate fills the gap. A chain should have more buying power than a single store, and without a shared record of what was agreed, it does not.' ),

		sh( 'No single view of how the business is doing', 3 ),
		sp( 'Ask an owner of several stores how last week went and you will often hear a pause. The numbers exist, but they sit in different places, in different formats, and arrive at different times. By the time they are put together they describe a week that is over. Decisions about what to stock, which store needs help and which supplier to push are made on feel rather than on the numbers.' ),

		sh( 'Counters that work in different ways', 3 ),
		sp( 'Each store develops its own habits: how a bill is made out, who is allowed to give a discount, how a return is handled, how a new assistant is taught. A customer who shops at two of your stores notices. So does a manager who moves between them. Uneven service is rarely about effort. It is about there being no common way of doing things that everyone can see and follow.' ),

		sh( 'Records that live in many places', 3 ),
		sp( 'Sales in one system, purchases in a spreadsheet, supplier dues in a notebook, and an accountant who asks for all of it on the fifth of the month. A chain multiplies the number of places records can hide, and each extra place is another chance for a number not to match. The month-end that was tolerable with one store becomes a tense week with five.' ),

		/* ---- What to look for ---- */
		sh( 'What a multi-store system should give you' ),
		sp( 'Software will not fix how you buy or how you train people, but it can give every store the same picture and the same tools. When you compare systems, these are the things worth asking to see on the screen, not just reading about on a features page.' ),

		sh( 'One place to manage every store', 3 ),
		sp( 'The core of a multi-store setup is a central admin account with each outlet linked to it. From there you can see what each store is doing without phoning anyone. The difference from running separate systems is that adding a store becomes a matter of linking it, not of building another set of records.' ),

		sh( 'Stock you can move and see', 3 ),
		sp( 'A stock transfer lets one store send items to another, and both stock lists change. That is what turns overstock in one place into availability in another instead of expiry. Ask any vendor to move a strip from one store to another and show you both lists afterwards. It takes a minute and tells you whether the feature is real.' ),

		sh( 'Expiry watched in every store', 3 ),
		sp( 'The stock expiry report, by item or by supplier, shows what is close to expiry and where it sits. In a chain, that matters twice: you can return what the supplier will still take back, and you can move what a faster store could sell. Reading it weekly across your stores costs less than the loss it prevents.' ),

		sh( 'Roles that follow the person, not the store', 3 ),
		sp( 'Each person should have their own login and a role that fits their job: billing at the counter, stock for the pharmacist, reports for the owner. The same roles should work the same way in every store. That is how you can tell who gave a discount or changed a rate, and it is why a trainee in store four can be taught the same way as one in store one.' ),

		sh( 'Rates agreed once', 3 ),
		sp( 'A supplier rate contract records the rates you have agreed with a supplier. Instead of each manager remembering the deal, the deal is written down where everyone can see it. When a bill arrives at a different rate, someone notices, and the conversation with the distributor starts from the contract rather than from memory.' ),

		sh( 'Reports across stores, and a dashboard for the owner', 3 ),
		sp( 'Central reports cover your outlets together, so you are not stitching one file per store. An executive dashboard gives the owner a single view of sales and performance. Margin and top-selling reports show what moves and what does not. The test is simple: can you see yesterday across all stores before lunch?' ),

		sh( 'Accounts your accountant can use', 3 ),
		sp( 'HSN and GST come from the item master, and there are GST and tax reports. If your accountant works in Tally, an XML export means the books move across instead of being re-typed. For a chain, where the volume of entries is larger and a typing error is harder to find, that is worth asking about early.' ),

		sh( 'Online orders for pick-up or delivery', 3 ),
		sp( 'Customers increasingly want to order ahead. The software supports online orders for pick-up or delivery, alongside medicine reminders and loyalty cards. For a chain, the value is that a customer can collect from whichever of your stores is nearest, with the same records behind it.' ),

		sb( 'healthray/note', array( 'kind' => 'tip', 'title' => 'Ask to see it with your own data' ), sp( 'A demo on sample data shows what the software can do. A demo with items from one of your stores shows whether it can do it for you. Ask for the second.' ) ),

		sh( 'The five problems and what answers them', 3 ),
		sp( 'Here is the whole section on one page: each problem a chain runs into, what it costs, what puts it right, and where to read more.' ),
		stable(
			array( 'The problem', 'What it costs', 'What answers it', 'Read more' ),
			array(
				array( 'Stock piles up in one store and runs out in another', 'Expiry in one, lost sales in the other', 'Stock transfer, and the expiry report across stores', $lnk( 'expiry', 'Expiry and batch' ) ),
				array( 'Every store orders for itself', 'Different rates and emergency orders', 'Supplier rate contracts, and reports on what you bought', $lnk( 'purchase', 'Purchase and suppliers' ) ),
				array( 'No single view of the business', 'Decisions made late and by feel', 'Reports across stores and an owner’s dashboard', $lnk( 'reports', 'Reports and dashboard' ) ),
				array( 'Counters that work differently', 'Uneven service and avoidable mistakes', 'One login per person, and the same roles in every store', $lnk( 'security', 'Security and roles' ) ),
				array( 'Records in many places', 'A slow, tense month-end', 'GST reports, and an export for Tally', $lnk( 'gst', 'GST and accounts' ) ),
			),
			'guide',
			'Five chain problems, what each costs and what answers it.'
		),

		sb( 'healthray/cta', array( 'kind' => 'form', 'title' => 'See a stock transfer between two stores', 'text' => 'A 30-minute call using items from one of your own stores: move stock from one outlet to another, read the expiry report across both and see the roles side by side.' ) ),

		/* ---- Records and rules ---- */
		sh( 'Records and rules, kept calm' ),
		sp( 'Every store in a chain has to keep its records straight, and a chain has more of them. It is tempting to treat this as a source of fear. It does not need to be. Good software keeps records in one tidy format and makes them easy to find. What it cannot do is decide the rules for you.' ),

		sh( 'GST records', 3 ),
		sp( 'HSN and GST come from the item master, flow into the bill, and appear in the GST report and in the bill-wise and date-wise tax reports. That means your accountant has clean data for each store, and the data for the chain adds up. Rates and rules change, so the item master is worth reviewing regularly.' ),

		sh( 'Prescription-only medicines', 3 ),
		sp( 'Items can be tagged as Schedule H or H1, so they stand out at the counter. That is a help to staff, not a guarantee about compliance. The rules that apply to your stores, and the records you must keep for them, are for you and your adviser to confirm.' ),

		sb( 'healthray/note', array( 'kind' => 'note', 'title' => 'The software helps with the records' ), sp( 'It does not replace checking the rules that apply to each of your stores. Treat it as the place where records are kept tidily, and keep your adviser in the loop.' ) ),

		sh( 'Staying audit-ready without a scramble', 3 ),
		sp( 'The best preparation is also the simplest: one login per person, roles that fit the job, and records that are entered at the time rather than reconstructed later. On the plans that include them, an activity report, two-step login and IP whitelisting add to that. None of this means an inspection is easy. It means that when someone asks a question, the answer is in the system, not in somebody’s memory.' ),

		/* ---- Rollout ---- */
		sh( 'How to bring a chain onto one system' ),
		sp( 'The fear behind most delayed projects is the same: billing stops, a queue forms, a customer leaves. The way to remove that fear is to make the move slow and dull. A calm rollout looks like this.' ),

		sh( 'Begin with a single store', 3 ),
		sp( 'Pick the store with the steadiest routine, not the busiest one. Load its items and opening stock from a CSV, with a sample sheet to follow and errors shown line by line. Bill on it for a few days and compare the stock against the shelf. Small mistakes are cheap to fix on one store and expensive to fix on five.' ),

		sh( 'Link the others, one at a time', 3 ),
		sp( 'Once the first store is steady, add the next outlet to the central admin. Give it its own items and stock, and set the same roles you used in the first. Leave a few days between each, so you can see the difference between a problem with the software and a problem with the store.' ),

		sh( 'Agree the roles early', 3 ),
		sp( 'It is easier to decide who can discount, who can adjust stock and who sees which report before the second store goes live than after. Write the roles down, apply them the same way everywhere, and review them once a month. The walkthroughs inside the app, covering sales, purchase, the item master and the account master, make training new staff far quicker.' ),

		sh( 'Check the numbers before you rely on them', 3 ),
		sp( 'Before you trust the reports across stores, compare them with your own books for a month. Look for sales that do not match, stock that is off, and rates that differ from what you agreed. When the numbers agree, retire the old registers. Until then, keep them.' ),

		/* ---- Conclusion ---- */
		sh( 'In conclusion' ),
		sp( 'A chain does not need a bigger system as much as it needs a shared one. The same items, the same rules and the same reports at every counter turn five small stores into one business you can see. Stock moves instead of expiring. Orders follow agreed rates instead of habit. Roles tell you who did what. Records line up at month-end because they were never apart.' ),
		sp( 'None of that arrives on the day you switch. It arrives store by store, over a few weeks, if you move calmly and check as you go. If you run several stores and want to see how it would look with your own items, the pharmacy chain page and a demo are the easiest places to start.' ),

		sb( 'healthray/cta', array( 'kind' => 'inline', 'text' => 'Run several stores and want to see it with your own data?', 'linkLabel' => 'See the pharmacy chain page', 'linkKey' => 'chain' ) ),

		/* ---- FAQ ---- */
		sb( 'healthray/faq', array( 'heading' => 'Asked about this' ),
			$faq( 'What does centralised inventory mean for a pharmacy chain?', 'It means every store’s stock can be seen from one admin account, and stock can be moved between stores so that both lists stay right. Each outlet is linked to the central admin, so you do not need to phone a store to find out what it holds.' ) . "\n" .
			$faq( 'Can it help me find the fast-moving medicines across my stores?', 'Yes. Margin and top-selling item reports show what moves and what does not, and with reports across stores you can compare how the same item sells in different places. Use them to decide what each store should hold.' ) . "\n" .
			$faq( 'How does it handle expiry across several stores?', 'The stock expiry report shows items or suppliers close to expiry. In a chain you can use it to plan returns to a supplier while they will still take stock back, and to move near-expiry items to a store that can sell them.' ) . "\n" .
			$faq( 'Does it support GST billing for each store?', 'HSN and GST come from the item master and flow into every bill. There is a GST report and a bill-wise and date-wise tax report, so each store’s data is ready for your accountant. Rules vary, so confirm what applies to your stores with your adviser.' ) . "\n" .
			$faq( 'Can I give each store its own staff logins and roles?', 'Yes. Each person has their own login and a role that fits the job. You can set the same roles in every store, so access works the same way everywhere.' ) . "\n" .
			$faq( 'How can I keep track of who changed what?', 'With one login per person, bills and changes carry a name. On the plans that include it, an activity report gives you a record of what was done, which helps when something does not add up.' ) . "\n" .
			$faq( 'Is there ABDM or ABHA support for a chain?', 'ABDM and ABHA integration is part of the hospital pharmacy features. If you run a hospital pharmacy alongside your stores, the hospital pharmacy page explains what is included.' ) . "\n" .
			$faq( 'Does it export to Tally?', 'Tally XML export is available on the plan that includes it. The pricing page shows which plan that is.' ) . "\n" .
			$faq( 'How can a chain buy more cost-effectively?', 'Record the rates you agree with a supplier as a supplier rate contract, so every store works from the same deal. Compare each new bill against what you agreed, and use stock transfer to balance stores before you place another order.' ) . "\n" .
			$faq( 'How is a chain priced, and can I see it with my own data?', 'Chain pricing is a quote, based on how you run. The pricing page shows what each plan includes. A demo can use items from one of your stores, so you see your own stock rather than sample data.' ) . "\n" .
			$faq( 'Can I start with just one store?', 'Yes. Many chains begin with one store, and link the others once the first is steady. The free plan is free to use, with limits, and there is no trial.' )
		),
	)
);

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'name' => $slug ) );
$data     = array(
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'post_title'    => $title,
	'post_name'     => $slug,
	'post_content'  => $content,
	'post_date'     => '2026-10-21 18:00:00', // In the past, so the post publishes at once whatever the time of day.
	'post_date_gmt' => get_gmt_from_date( '2026-10-21 18:00:00' ),
);
if ( $existing ) {
	$data['ID'] = (int) $existing[0];
	$id         = wp_update_post( wp_slash( $data ), true );
} else {
	$id = wp_insert_post( wp_slash( $data ), true );
}
if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'FAILED: ' . $id->get_error_message() . PHP_EOL );
	exit( 1 );
}

update_post_meta( $id, 'topic', 'buying' );
update_post_meta( $id, 'dek', 'What goes wrong when each store keeps its own books, what a multi-store system should give you, how to think about records and rules, and a calm way to bring a chain onto one system.' );
update_post_meta( $id, 'updated', '2026-10-22' );
update_post_meta( $id, '_yoast_wpseo_title', 'Running a multi-branch pharmacy: one system for every store %%sep%% %%sitename%%' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'What goes wrong when each pharmacy store keeps its own stock, bills and reports, what a multi-store system should give you, and how to roll it out calmly.' );

$att = rx_mb_attachment( __DIR__ . '/content/images/' . $slug . '-feature.png', $slug . '-feature-playful', $title, 'A dashboard connected to three pharmacy stores' );
if ( $att ) {
	set_post_thumbnail( $id, $att );
}
delete_post_meta( $id, '_rx_read_mins' );
wp_update_post( array( 'ID' => $id ) );

$words = str_word_count( wp_strip_all_tags( $content ) );
echo ( $existing ? 'Updated' : 'Created' ) . " post #{$id}: " . get_permalink( $id ) . PHP_EOL;
echo "Words: {$words}, read time: " . rx_blog_read_mins( $id ) . ' min' . PHP_EOL;
