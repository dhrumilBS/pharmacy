<?php
/**
 * Creates or updates the pillar listicle "Pharmacy inventory management: 12 ways to manage medicine stock efficiently"
 * (slug pharmacy-inventory-management-tips).
 *
 * SEO plan (from keyword research, India, Ahrefs, 22 Oct 2026):
 *   primary    how to manage pharmacy inventory efficiently (about 400 a month, the largest informational term in the cluster)
 *   supporting pharmacy inventory management (150, KD 28) · inventory control in pharmacy (200, KD 0) · inventory management in
 *              pharmacy (100) · medicine stock register / format / in excel (200 / 150 / 90, KD 0) · near expiry and short expiry
 *              medicine (150 / 80) · pharmacy inventory management software (150, KD 6)
 * How the page is built for them: the answer in the first 80 words and in the short version, one H2 per way (each worded as a
 * search phrase), a definition section for "what is inventory control in pharmacy", a stock-register table for the "format"
 * searches, a comparison table (register, spreadsheet, software) for the software searches, ten FAQ questions as FAQPage data,
 * internal links to 14 other articles and 4 site pages, and links back from the six related articles (blog-link-pillar.php).
 *
 * Facts stay inside the verified product list (PHARMACY-ICP-AND-UX-STRATEGY.md §0). No customer figures, no tax rates, no claims
 * about what any rule requires: the stock-register and disposal sections tell the reader to check their own rules.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-inventory-listicle.php   (local install only, re-runnable)
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

$slug  = 'pharmacy-inventory-management-tips';
$title = 'Pharmacy inventory management: 12 ways to manage medicine stock efficiently';

/** A link to another article (by slug) or a site page (by page key), as a plain anchor. */
$lk_post = static function ( string $post_slug, string $anchor ): string {
	$p   = get_page_by_path( $post_slug, OBJECT, 'post' );
	$url = $p ? (string) get_permalink( $p ) : '';
	return '' !== $url ? '<a href="' . esc_url( $url ) . '">' . esc_html( $anchor ) . '</a>' : esc_html( $anchor );
};
$lk_page = static fn( string $key, string $anchor ): string => '<a href="' . esc_url( rx_url( $key ) ) . '">' . esc_html( $anchor ) . '</a>';
$faq  = static fn( string $q, string $a ): string => sb( 'healthray/faq-item', array( 'question' => $q ), sp( $a ) );

$content = implode(
	"\n\n",
	array(
		sb( 'healthray/takeaways', array(), sli( array(
			'To manage pharmacy inventory efficiently, keep a <strong>minimum and maximum for every item</strong>, sell the <strong>earliest expiry first</strong>, check expiry <strong>every week</strong>, and count stock <strong>a section at a time</strong>.',
			'Most of the money lost in a medical store is lost to <strong>expiry, over-buying and entry mistakes</strong>, not to theft. Each of the twelve ways below closes one of those leaks.',
			'You can run all twelve on paper or a spreadsheet. Pharmacy software just does the sums, picks the batch and builds the lists for you.',
		) ) ),

		sp( 'Pharmacy inventory management is the work of knowing what medicine you hold, when it expires, how fast it sells and when to order more. Do it well and shelves stay full of what sells, near-expiry stock is returned in time, and cash is not tied up in boxes nobody buys. Do it badly and you find out at month-end, when it is too late to fix.' ),
		sp( 'This article lists twelve practical ways to manage medicine stock efficiently in a medical store or a small chain. Each one is a habit, a number or a report, not a theory. Use them in order if you are starting from a register, or pick the three that fit your biggest problem today.' ),

		sh( 'What is inventory control in a pharmacy?' ),
		sp( 'Inventory control in a pharmacy means keeping the right amount of every medicine: enough that a customer is never sent away, not so much that it expires on the shelf. In practice it rests on four facts about every item: how many you hold, which batch and expiry date each unit belongs to, how fast it sells, and what it cost you.' ),
		sp( 'From those four facts come the numbers that run the store: the minimum level at which you reorder, the maximum you should hold, the batches to sell first, and the list of items that have stopped moving. Everything below is a way of keeping those facts true and acting on them.' ),

		/* ---- 1 ---- */
		sh( '1. Set a minimum and a maximum for every item' ),
		sp( 'A minimum is the stock at which you reorder; a maximum is the most you should hold. Without them, ordering is a feeling, and feelings are wrong most often on the fastest and the slowest items. Start with your top 100 sellers: they are most of your sales and most of your cash.' ),
		sh( 'How to work out the minimum', 3 ),
		sp( 'The minimum has to last until the next delivery arrives, plus a cushion for a busy day. Write it as a sum and reuse it for every item.' ),
		sb( 'healthray/sum', array( 'title' => 'Reorder level for one item', 'resultLabel' => 'Minimum stock', 'resultValue' => '18 strips', 'note' => 'Illustrative numbers. Use your own daily sales and delivery time.' ),
			sb( 'healthray/sum-line', array( 'label' => 'Strips sold a day', 'value' => '6', 'op' => 'none' ) ) . "\n" .
			sb( 'healthray/sum-line', array( 'label' => 'Days until the next delivery', 'value' => '2 days', 'op' => '×' ) ) . "\n" .
			sb( 'healthray/sum-line', array( 'label' => 'Cushion for a busy day', 'value' => '6 strips', 'op' => '+' ) )
		),
		sp( 'The maximum is the quantity you can sell before the batch expires, so shelf life, not a distributor’s scheme, sets it. Our article on ' . $lk_post( 'min-max-stock', 'setting minimum and maximum stock' ) . ' walks through the sum with a worked table.' ),

		/* ---- 2 ---- */
		sh( '2. Sell the earliest expiry first (FEFO)' ),
		sp( 'FEFO means first expiry, first out: the batch that expires first is the batch you sell first, whatever order it arrived in. It differs from first in, first out, because a newer delivery can carry a shorter expiry than an older one, and that happens more often than owners expect.' ),
		sp( 'The habit is simple on the shelf (earliest expiry at the front) and hard at a busy counter. That is why the sale screen in pharmacy software picks the earliest-expiry batch for you. See ' . $lk_post( 'fefo-explained', 'how FEFO works and how to make it automatic' ) . ' for a side-by-side example.' ),

		/* ---- 3 ---- */
		sh( '3. Check near-expiry medicine every week' ),
		sp( 'Near-expiry, or short-expiry, medicine is stock that will expire within the next one to two months. Checking it weekly takes about a quarter of the time a monthly check takes, and every batch is looked at while the distributor will still take it back.' ),
		stable(
			array( 'When it expires', 'What to do', 'Why' ),
			array(
				array( 'Within 30 days', 'Return it if the supplier will take it, otherwise put it at the front.', 'The last chance to recover the money.' ),
				array( '31 to 60 days', 'Plan the return and stop reordering it.', 'Reordering on top of old stock is how the pile grows.' ),
				array( 'More than 60 days', 'Leave it alone.', 'Time is better spent where it matters.' ),
			),
			'guide',
			'A simple way to sort near-expiry stock. Return windows differ by distributor, so ask each one.'
		),
		sp( 'Run the stock expiry report grouped by supplier, because a return goes to one distributor at a time. ' . $lk_post( 'cut-expiry-loss', 'A 15-minute weekly routine' ) . ' and ' . $lk_post( 'read-the-expiry-report', 'how to read the expiry report' ) . ' show the steps.' ),

		/* ---- 4 ---- */
		sh( '4. Count stock a section at a time, not all at once' ),
		sp( 'A full stock count does not need a closed shop. Divide the store into sections a person can count in half an hour (a rack, the fridge, the counter drawers), give each a day of the month and keep the same order every month. Count by batch, not by item, because one medicine often sits in two batches with different expiry dates.' ),
		sp( 'Write down every difference before you change a number, and find the cause first: a missing bill, an unentered purchase or a wrong batch. Adjusting without looking only moves the problem. Details are in ' . $lk_post( 'monthly-stock-count', 'how to run a monthly stock count without closing the shop' ) . '.' ),

		/* ---- 5 ---- */
		sh( '5. Find the stock that has stopped moving' ),
		sp( 'Non-moving stock is medicine with no sale in the last 90 days. It ties up cash and heads for expiry. Look at it once a month, sort it by value and give each of the top ten items one of four fates: return it, move it to the front, discount it, or stop reordering it.' ),
		sp( 'A scheme on a slow item is how the pile grows, so check the last sale before you accept free quantity. See ' . $lk_post( 'non-moving-stock', 'how to find non-moving stock and what to do with it' ) . '.' ),

		/* ---- 6 ---- */
		sh( '6. Keep a short book that builds itself' ),
		sp( 'The short book is the list of what to order today. A paper short book depends on someone writing in it when the last strip goes. One built from minimum levels does not: any item that has fallen below its minimum goes on the list by itself. Check it once a day at the same time, group the items by supplier and order from each supplier once.' ),
		sp( 'Mark what you have ordered, so tomorrow’s book does not repeat today’s. ' . $lk_post( 'short-book', 'A short book that builds itself' ) . ' explains the daily routine.' ),

		sb( 'healthray/cta', array( 'kind' => 'form', 'title' => 'See your own stock on one screen', 'text' => 'A 30-minute call with items from your own shelf: set minimum levels, read the expiry report and build a short book, without changing how you bill today.' ) ),

		/* ---- 7 ---- */
		sh( '7. Check every distributor bill before you enter it' ),
		sp( 'Stock errors rarely start at the counter. They start when a delivery is entered: a batch typed with one wrong digit, a free quantity left out, a rate that rose a little. Check six things on every line before you save: batch, expiry, quantity, free quantity and scheme, rate and MRP.' ),
		sp( 'The check that pays most is the rate against your last purchase, because a small rise repeats on every strip you buy. Query a difference before you post the bill, not after. The full list is in ' . $lk_post( 'check-distributor-bill-before-entry', 'checking a distributor bill before you enter it' ) . '.' ),

		/* ---- 8 ---- */
		sh( '8. Buy at agreed rates, and judge a scheme by days of stock' ),
		sp( 'A scheme looks like a gift, but it saves money only if you sell the extra before it expires. Divide the quantity you would receive, free quantity included, by your daily sales. If those days are longer than the time left to expiry, the scheme loses money however generous it looks.' ),
		sp( 'Compare distributors on the effective rate per strip, after the free quantity, not on the rate printed on the bill. ' . $lk_post( 'compare-distributor-rates', 'Comparing distributor rates' ) . ' and ' . $lk_post( 'scheme-free-quantity', 'what a scheme really saves' ) . ' have the sums.' ),

		/* ---- 9 ---- */
		sh( '9. Move stock between stores or counters before it expires' ),
		sp( 'In a chain, one store is often overstocked while another is out. A stock transfer fixes both: the first store sends items, the second receives them, and both stock lists change. The same idea works inside one store with a back room and a front counter.' ),
		sp( 'Read the expiry report across all your stores each week and move what a faster store can sell. ' . $lk_post( 'multi-branch-pharmacy-chains', 'Running a multi-branch pharmacy' ) . ' covers what to look for in a multi-store system.' ),

		/* ---- 10 ---- */
		sh( '10. Keep a clean medicine stock register' ),
		sp( 'A stock register records what came in, what went out and what is left, item by item and batch by batch. Many stores keep one on paper or in Excel. Whatever format you use, keep it in the same layout every time, fill it the day things happen, and make it easy to compare against the shelf.' ),
		stable(
			array( 'Item and batch', 'Expiry', 'In and out', 'Balance' ),
			array(
				array( 'Paracetamol 650 · PX4412', 'Mar 2028', 'In 40 strips · Out 6', '34 strips' ),
				array( 'Azithromycin 500 · AZ1189', 'Nov 2026', 'In 20 strips · Out 12', '8 strips' ),
				array( 'Pantoprazole 40 · PT7731', 'Jun 2027', 'In 30 strips · Out 4', '26 strips' ),
			),
			'guide',
			'A sample stock register layout with made-up entries. Adapt the columns to your store.'
		),
		sb( 'healthray/note', array( 'kind' => 'note', 'title' => 'Check what your rules require' ), sp( 'What a stock register must contain, and what you must keep for how long, depends on where you trade and on the kind of medicine. Treat the layout above as a starting point and confirm the requirements with your adviser.' ) ),

		/* ---- 11 ---- */
		sh( '11. Give everyone their own login, and control who can adjust stock' ),
		sp( 'When everyone uses the same login, you cannot tell who adjusted a quantity, edited a rate or deleted an item. Give each person their own login and a role that fits the job: billing at the counter, stock for the pharmacist, reports for the owner. Stock adjustments in particular should sit with the few people who can explain them.' ),
		sp( 'Remove a leaver’s login the same day. ' . $lk_post( 'staff-logins-roles', 'Staff logins and roles' ) . ' has a role table to start from, and ' . $lk_post( 'shift-handover', 'a shift handover routine' ) . ' keeps the changeover from losing track of held items and drafts.' ),

		/* ---- 12 ---- */
		sh( '12. Use pharmacy inventory software that does the sums for you' ),
		sp( 'Everything above can be done by hand, and many good stores do. The reason to move to software is not that paper is wrong. It is that three jobs never stop: picking the right batch on every bill, noticing what has dropped below its minimum, and reading expiry across every item. Software does those the moment a bill is saved.' ),
		stable(
			array( 'What you need', 'Paper register', 'Spreadsheet', 'Pharmacy software' ),
			array(
				array( 'Earliest expiry picked at the counter', 'No, by memory', 'No, you sort and check', 'Yes, on the sale screen' ),
				array( 'Reorder list from minimum levels', 'No, by hand', 'With formulas you build', 'Yes, stock alerts and a short book' ),
				array( 'Expiry report by item or supplier', 'No', 'With filters you maintain', 'Yes, a stock expiry report' ),
				array( 'Stock after every bill', 'Updated by hand', 'Updated by hand', 'Updated on every bill' ),
				array( 'Who changed what', 'Handwriting', 'Hard to trace', 'One login per person' ),
			),
			'guide',
			'Three ways to manage pharmacy inventory, side by side.'
		),
		sp( 'If you are choosing software, our ' . $lk_page( 'inventory', 'stock and reorder page' ) . ' and ' . $lk_page( 'expiry', 'expiry and batch page' ) . ' show the screens, and ' . $lk_post( 'questions-for-vendors', 'eight questions to ask any vendor' ) . ' help you compare. The ' . $lk_page( 'retail', 'retail medical store page' ) . ' explains how a single store gets started, and ' . $lk_page( 'pricing', 'pricing' ) . ' lists what the free plan covers. Moving from a spreadsheet? Read ' . $lk_post( 'from-excel-to-software', 'switching from Excel and registers' ) . ' first.' ),

		/* ---- routine ---- */
		sh( 'A weekly and monthly pharmacy inventory routine' ),
		sp( 'Twelve ways are easier to keep when they have a calendar. This is a routine a small store can follow in under an hour a week.' ),
		sh( 'Every day', 3 ),
		sli( array(
			'Look at the short book and order from each supplier once.',
			'Enter every delivery against its bill before you shelve it.',
			'Put the earliest expiry at the front of each slot.',
		) ),
		sh( 'Every week', 3 ),
		sli( array(
			'Read the stock expiry report for the next 60 days, grouped by supplier.',
			'Prepare one return list per supplier and record the returns the same day.',
			'Check the credit notes that should have arrived.',
		) ),
		sh( 'Every month', 3 ),
		sli( array(
			'Count one section a week, so the whole store is counted by month-end.',
			'Run the non-moving items report and decide a fate for the top ten.',
			'Review the minimum and maximum of the items that ran out or piled up.',
			'Look at who has access and remove what is not needed.',
		) ),

		/* ---- mistakes ---- */
		sh( 'Five common pharmacy inventory mistakes' ),
		sli( array(
			'<strong>Ordering by feel.</strong> Without minimum levels, the fast movers run out and the slow ones pile up.',
			'<strong>Accepting every scheme.</strong> Free quantity on a slow item turns into near-expiry stock.',
			'<strong>Adjusting stock to match the shelf.</strong> The difference has a cause; find it before you change the number.',
			'<strong>Entering bills late.</strong> By the time a bill is typed in, nobody remembers which batch came in.',
			'<strong>Sharing one login.</strong> You cannot fix what you cannot trace.',
		) ),

		sh( 'In short' ),
		sp( 'Efficient pharmacy inventory management is not a single clever trick. It is twelve small habits that make stock visible: numbers for what to hold, a rule for what to sell first, a weekly look at what is about to expire, and records that match the shelf. Start with the one that fixes your biggest leak, and add the next when it feels routine.' ),

		sb( 'healthray/cta', array( 'kind' => 'inline', 'text' => 'Opening a store or tidying an existing one? See how a retail medical store runs on one screen.', 'linkLabel' => 'See the retail medical store page', 'linkKey' => 'retail' ) ),

		sb( 'healthray/faq', array( 'heading' => 'Asked about pharmacy inventory management' ),
			$faq( 'What is inventory management in a pharmacy?', 'It is the work of knowing what medicine you hold, which batch and expiry date each unit has, how fast it sells and when to reorder. The aim is to keep every item available without holding so much that it expires unsold.' ) . "\n" .
			$faq( 'How do you manage pharmacy inventory efficiently?', 'Set a minimum and a maximum for each item, sell the earliest expiry first, read the expiry report weekly, count stock a section at a time, check distributor bills before entering them, and keep records that match the shelf. Software can automate the batch picking and reorder lists.' ) . "\n" .
			$faq( 'What is FEFO in a pharmacy?', 'FEFO means first expiry, first out: the batch that expires first is sold first, regardless of when it arrived. It prevents newer stock with a shorter expiry from being left to run out of date.' ) . "\n" .
			$faq( 'How do you calculate the minimum stock level for a medicine?', 'Multiply the strips sold per day by the days until the next delivery, then add a cushion for a busy day. For example, 6 strips a day over 2 days plus a 6-strip cushion gives a minimum of 18 strips. Use your own figures.' ) . "\n" .
			$faq( 'How often should a medical store count its stock?', 'Count a little every week rather than everything at once. Dividing the store into sections and counting one a week covers the whole shop each month without closing it.' ) . "\n" .
			$faq( 'What is a medicine stock register?', 'It is a record of what came in, what went out and what is left for each item and batch. Many stores keep one on paper or in a spreadsheet. What it must contain depends on your rules, so confirm that with your adviser.' ) . "\n" .
			$faq( 'How do you deal with near-expiry medicine?', 'Check it weekly, return what the distributor will still take back, move the rest to the front of the shelf and stop reordering it. Return windows differ by distributor, so ask each one and write the answer down.' ) . "\n" .
			$faq( 'What should I do with expired medicine?', 'Take it off the shelf and out of sale straight away and keep it apart until it is disposed of. How to dispose of it, and whether the supplier takes it back, depends on your rules and your distributor, so check both.' ) . "\n" .
			$faq( 'Which is better for pharmacy inventory: a register, a spreadsheet or software?', 'A register or a spreadsheet works for a very small store, but it will not pick the earliest-expiry batch or build a reorder list for you. Software does those on every bill, which matters as the item list and the number of bills grow.' ) . "\n" .
			$faq( 'Is there free pharmacy inventory software?', 'There is a free plan, with limits, that covers core sales and purchase, a CSV import and basic reports. There is no trial; the pricing page shows what each paid plan adds.' )
		),
	)
);

/** Uploads an image once (found again by `_rx_src`), replacing the file on re-runs. */
function rx_il_attachment( string $file, string $key, string $title, string $alt ): int {
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

$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'name' => $slug ) );
$data     = array(
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'post_title'    => $title,
	'post_name'     => $slug,
	'post_content'  => $content,
	'post_date'     => '2026-10-21 20:00:00', // In the past, so it publishes at once whatever the time of day.
	'post_date_gmt' => get_gmt_from_date( '2026-10-21 20:00:00' ),
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

update_post_meta( $id, 'topic', 'expiry' );
update_post_meta( $id, 'dek', 'Twelve habits, numbers and reports that keep medicine stock visible: minimum levels, earliest expiry first, a weekly expiry check, a clean stock register and a routine you can keep.' );
update_post_meta( $id, 'updated', '2026-10-22' );
update_post_meta( $id, '_yoast_wpseo_focuskw', 'pharmacy inventory management' );
update_post_meta( $id, '_yoast_wpseo_title', 'Pharmacy inventory management: 12 ways to manage stock %%sep%% %%sitename%%' );
update_post_meta( $id, '_yoast_wpseo_metadesc', 'How to manage pharmacy inventory efficiently: 12 practical ways, from minimum levels and FEFO to expiry checks, a stock register format and a weekly routine.' );

$att = rx_il_attachment( __DIR__ . '/content/images/' . $slug . '-feature.png', $slug . '-feature-playful', $title, 'A shelf of medicine boxes beside a checklist and a clock: 12 ways to manage pharmacy stock' );
if ( $att ) {
	set_post_thumbnail( $id, $att );
}
delete_post_meta( $id, '_rx_read_mins' );
wp_update_post( array( 'ID' => $id ) );

$words = str_word_count( wp_strip_all_tags( $content ) );
echo ( $existing ? 'Updated' : 'Created' ) . " post #{$id}: " . get_permalink( $id ) . PHP_EOL;
echo "Words: {$words}, read time: " . rx_blog_read_mins( $id ) . ' min, title ' . mb_strlen( 'Pharmacy inventory management: 12 ways to manage stock | Healthray' ) . ' chars' . PHP_EOL;
