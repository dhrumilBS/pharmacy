<?php
/**
 * Adds 13 more articles (block editor) so the blog has enough posts to paginate: 20 in total with the seven that already
 * exist. Each has a playful category image from content/images/gen-playful-images.py.
 *
 * Facts stay inside the verified product list (PHARMACY-ICP-AND-UX-STRATEGY.md §0): no customer figures, no tax rates,
 * no compliance guarantees; example numbers are labelled illustrative. Re-runnable: a post that exists (same slug) is updated.
 *
 * Usage: D:\xampp\php\php.exe wordpress\import\blog-more-posts.php   (local install only)
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

/* ---- small builders ----------------------------------------------------------------------- */

$take  = static fn( array $items ): string => sb( 'healthray/takeaways', array(), sli( $items ) );
$note  = static fn( string $kind, string $title, string $text ): string => sb( 'healthray/note', array( 'kind' => $kind, 'title' => $title ), sp( $text ) );
$steps = static function ( array $rows ): string {
	$inner = array();
	foreach ( $rows as $r ) {
		$inner[] = sb( 'healthray/step', array( 'title' => $r[0], 'when' => $r[1] ?? '' ), sp( $r[2] ) );
	}
	return sb( 'healthray/steps', array(), implode( "\n", $inner ) );
};
$check = static fn( string $title, array $items ): string => sb( 'healthray/checklist', array( 'title' => $title ), sli( $items ) );
$table = static fn( array $head, array $rows, string $caption, string $style = 'guide' ): string => stable( $head, $rows, $style, $caption );
$cta   = sb( 'healthray/cta', array( 'kind' => 'panel', 'title' => 'Start free, and see what the plans add', 'text' => 'The Starter plan is free to use, with limits. There is no trial: it stays free, and you move up only if you need more. The pricing page always shows the current limits.' ) );

/* ---- the articles ------------------------------------------------------------------------- */

$posts = array(

	'read-the-expiry-report' => array(
		'title' => 'How to read the stock expiry report without getting lost',
		'topic' => 'expiry', 'date' => '2026-10-20 10:00:00',
		'dek'   => 'The expiry report holds everything you need to cut expiry loss. Where to look first, what to ignore and the three settings worth changing.',
		'seo'   => 'How to read the stock expiry report in a medical store: group by supplier, filter by days, and decide what to return, move or leave.',
		'body'  => array(
			$take( array( 'Read the report <strong>by supplier</strong> when you plan returns and <strong>by item</strong> when you plan the shelf.', 'Filter to the next 60 days; anything further out is noise this week.', 'Finish with a short list: what to return, what to move, what to leave.' ) ),
			sp( 'The stock expiry report can show every batch you hold. That is useful once a quarter and overwhelming every week. A few choices make it a five-minute page instead of a long one.' ),
			sh( 'Three choices that make it short' ),
			$table( array( 'Choice', 'Set it to', 'Why' ), array(
				array( 'Window', 'Next 60 days', 'Beyond that, nothing can be done yet.' ),
				array( 'Grouping', 'By supplier for returns, by item for shelves', 'A return goes to one supplier at a time.' ),
				array( 'Sort', 'Value, highest first', 'Start where the money is.' ),
			), 'Three settings for a short report.' ),
			sh( 'Reading it' ),
			$steps( array(
				array( 'Look at the top of the list', '1 minute', 'The batches with the most value inside the next 30 days.' ),
				array( 'Mark the returns', '2 minutes', 'Anything the supplier will still take back.' ),
				array( 'Mark the moves', '2 minutes', 'What cannot be returned goes to the front of the shelf.' ),
			) ),
			$note( 'tip', 'Export a copy for your records', 'Keep the list you acted on, with the date. It makes the next week’s comparison easy.' ),
			$cta,
		),
	),

	'monthly-stock-count' => array(
		'title' => 'A monthly stock count without closing the shop',
		'topic' => 'expiry', 'date' => '2026-08-06 10:00:00',
		'dek'   => 'A full count does not need a closed shop or a long weekend. Count a section at a time, and let the software tell you where to look first.',
		'seo'   => 'How to count the stock of a medical store a section at a time, without closing, and what to do with the differences you find.',
		'body'  => array(
			$take( array( 'Count <strong>one section at a time</strong>, a little every week, instead of everything on one night.', 'Start with the items where a wrong number costs most: fast movers and expensive items.', 'Write down every difference with its cause before you change a number.' ) ),
			sp( 'Most owners avoid a stock count because it sounds like a shut shop and a lost weekend. It does not have to. A count that never stops billing is just a matter of how you slice the shelves.' ),
			sh( 'Slice the shelves' ),
			sp( 'Divide the shop into sections that a person can count in about half an hour: a rack, a fridge, the counter drawers. Give each section a day of the month and keep the same order every month.' ),
			$steps( array(
				array( 'Print or open the stock list for the section', 'Before you start', 'Use the software’s list for just that rack, so you are counting against something.' ),
				array( 'Count by batch, not by item', 'At the shelf', 'The same medicine can sit in two batches with different expiry dates; count each one.' ),
				array( 'Mark each difference', 'At the shelf', 'Write the item, the batch, the expected and the counted numbers.' ),
				array( 'Find the cause, then adjust', 'Same day', 'Check the day’s bills and the last purchase before you change anything.' ),
			) ),
			$note( 'caution', 'Do not adjust first and ask later', 'A stock adjustment that hides a missing bill or an unentered purchase just moves the problem. Look for the cause, then adjust.' ),
			sh( 'Where to start' ),
			$table( array( 'Count first', 'Why' ), array(
				array( 'Fast movers', 'A small error repeats on every sale.' ),
				array( 'High-value items', 'One missing box is real money.' ),
				array( 'Items that went “out of stock” while the software showed stock', 'These point to a process problem, not a counting problem.' ),
			), 'Where a count pays most.' ),
			$note( 'tip', 'Let staff count, you review', 'The person who sells an item should not be the only one who counts it. Swap sections between two people.' ),
			$cta,
		),
	),

	'non-moving-stock' => array(
		'title' => 'Non-moving stock: how to find it and what to do with it',
		'topic' => 'expiry', 'date' => '2026-08-13 10:00:00',
		'dek'   => 'Stock that has stopped selling ties up cash and heads for expiry. How to spot it early and the four things you can do about it.',
		'seo'   => 'How to find non-moving stock in a medical store and four ways to deal with it before it expires.',
		'body'  => array(
			$take( array( 'Non-moving stock is cash on a shelf that is <strong>not coming back</strong> unless you act.', 'Look at it every month: items with no sale in the last 90 days.', 'You can return it, discount it, move it to the front or stop reordering. Pick one per item.' ) ),
			sp( 'An item that sells slowly is not a problem on its own. The trouble is when it sells not at all and nobody notices until it expires. A short list, looked at regularly, is all it takes.' ),
			sh( 'How to find it' ),
			sp( 'The software has a non-moving items report. Run it once a month for items with no sale in 90 days, and keep the list short by acting on it.' ),
			sh( 'Four things you can do' ),
			$table( array( 'Option', 'When it fits', 'Watch out for' ), array(
				array( 'Return to the supplier', 'There is still time before expiry.', 'Return windows differ by distributor.' ),
				array( 'Move to the front', 'It sells, just slowly.', 'It only helps if staff mention it.' ),
				array( 'Discount', 'Expiry is close and a return is not possible.', 'Set a floor so you do not sell below cost by habit.' ),
				array( 'Stop reordering', 'You find you rarely sell it.', 'Remove it from the short book so it does not come back.' ),
			), 'Four options for a slow item.' ),
			$note( 'caution', 'Check before you reorder', 'A scheme on a slow item is how the pile grows. Look at the last sale before you accept free quantity.' ),
			$check( 'Monthly non-moving check', array( 'Open the non-moving items report for the last 90 days.', 'Sort it by value, highest first.', 'Choose one of the four options for each of the top ten.', 'Remove what you will not reorder from your short book.' ) ),
			$cta,
		),
	),

	'fefo-explained' => array(
		'title' => 'FEFO explained: why the earliest expiry goes out first',
		'topic' => 'expiry', 'date' => '2026-08-20 10:00:00',
		'dek'   => 'First expiry, first out is the simplest habit in a pharmacy and the one that is hardest to keep when you are busy. Why it matters and how to make it automatic.',
		'seo'   => 'What FEFO (first expiry, first out) means in a medical store, how it differs from first in first out and how to make it automatic at the counter.',
		'body'  => array(
			$take( array( 'FEFO means the batch that <strong>expires first is sold first</strong>, whatever order it arrived in.', 'It matters because two batches of one medicine rarely expire on the same day.', 'It only works if the counter picks the batch for you; do not depend on memory in a queue.' ) ),
			sp( 'First in, first out is how most shops stack shelves. It breaks down the moment a newer batch has a shorter expiry than an older one, which happens more often than you would expect.' ),
			sh( 'FIFO and FEFO, side by side' ),
			$table( array( 'Batch', 'Arrived', 'Expires', 'FIFO sells', 'FEFO sells' ), array(
				array( 'A', '1 Aug', 'Mar 2028', 'First', 'Second' ),
				array( 'B', '12 Aug', 'Nov 2026', 'Second', 'First' ),
			), 'Illustrative example: the newer batch expires first.', 'guide' ),
			sp( 'With FIFO you would sell batch A and let batch B run out of date. FEFO sells B first, and the shelf stays honest.' ),
			sh( 'Making it automatic' ),
			sp( 'The sale screen in the software picks the batch that expires first for you, so the counter does not have to read every strip. You can still choose another batch when a customer asks for a specific one.' ),
			$note( 'tip', 'Keep the shelf in the same order', 'Put the earliest expiry at the front of each slot. The software does the sums; the shelf does the rest.' ),
			$note( 'note', 'Expired items', 'There is a setting that controls whether expired items can be sold at all. Check it once, so it is set the way you want.' ),
			$cta,
		),
	),

	'short-book' => array(
		'title' => 'A short book that builds itself: stock alerts for a medical store',
		'topic' => 'expiry', 'date' => '2026-08-27 10:00:00',
		'dek'   => 'The short book is the list of what to order today. How to stop writing it by hand and let minimum levels build it for you.',
		'seo'   => 'How minimum stock levels, stock alerts and a short book help a medical store reorder before an item runs out.',
		'body'  => array(
			$take( array( 'A short book is the list of items that have <strong>fallen below their minimum</strong>.', 'It only works if each item has a minimum; start with your top 100.', 'Check it once a day, at the same time, and order from it.' ) ),
			sp( 'A paper short book depends on someone remembering to write in it when the last strip goes. A short book built from minimum levels does not depend on anyone remembering.' ),
			sh( 'What you need first' ),
			sp( 'Minimum levels, which are the stock at which you reorder. If you have not set them yet, the article on minimum and maximum stock takes ten minutes to read and an afternoon to apply to your top 100 items.' ),
			sh( 'How the day goes' ),
			$steps( array(
				array( 'Open the short book', 'Morning', 'Look at what has fallen below its minimum since yesterday.' ),
				array( 'Group by supplier', 'Morning', 'Order from each supplier once, not once per item.' ),
				array( 'Check for scheme and rate', 'Before you order', 'Compare against your last purchase before you accept a scheme.' ),
				array( 'Record what you ordered', 'After the call', 'So tomorrow’s book does not repeat today’s.' ),
			) ),
			$note( 'caution', 'Do not reorder what you already ordered', 'The most common mistake with a short book is ordering the same item twice because yesterday’s order has not arrived. Mark what is on its way.' ),
			$note( 'tip', 'Review the minimum when you see a pattern', 'If the same item is always in the book, its minimum is too low. If it never is, it may be too high.' ),
			$cta,
		),
	),

	'compare-distributor-rates' => array(
		'title' => 'How to compare distributor rates without a spreadsheet',
		'topic' => 'purchase', 'date' => '2026-09-03 10:00:00',
		'dek'   => 'Two distributors, one medicine, two rates. How to tell which is really cheaper once scheme, free quantity and payment terms are counted.',
		'seo'   => 'How to compare distributor rates for a medical store by effective rate after scheme and free quantity, without building a spreadsheet.',
		'body'  => array(
			$take( array( 'The rate on the bill is not the rate you pay. <strong>Scheme and free quantity</strong> change it.', 'Compare the <strong>effective rate per strip</strong>, after the free quantity.', 'Compare against your last purchase of the same item, not against memory.' ) ),
			sp( 'A distributor who looks cheaper on the rate line can be dearer once the free quantity is counted, and the other way round. The way to tell is to work out what you pay for each strip you actually get.' ),
			sh( 'Effective rate, in one line' ),
			sb( 'healthray/sum', array( 'title' => 'What one strip really costs', 'resultLabel' => 'Effective rate per strip', 'resultValue' => '₹90.00', 'note' => 'Illustrative numbers. Use the figures from your own bill.' ),
				sb( 'healthray/sum-line', array( 'label' => 'Amount on the bill for 10 strips bought, 1 free', 'value' => '₹990', 'op' => 'none' ) ) . "\n" .
				sb( 'healthray/sum-line', array( 'label' => 'Strips you receive', 'value' => '11', 'op' => '÷' ) )
			),
			sh( 'Compare two offers' ),
			$table( array( 'Distributor', 'Rate on the bill', 'Free quantity', 'Effective rate' ), array(
				array( 'Distributor A', '₹99.00', '1 on 10', '₹90.00' ),
				array( 'Distributor B', '₹92.00', 'None', '₹92.00' ),
			), 'Illustrative numbers: the dearer-looking bill is the cheaper one.', 'guide-hl-last' ),
			$note( 'note', 'Where the software helps', 'When you enter a purchase, scheme and free quantity are recorded, and the purchase screen shows a Diff % column: your margin against the last purchase or the purchase average.' ),
			$note( 'caution', 'Count the terms too', 'A better rate with a shorter credit period can still cost you more. Write the terms next to the rate.' ),
			$cta,
		),
	),

	'scheme-free-quantity' => array(
		'title' => 'What a scheme really saves: free quantity on a purchase bill',
		'topic' => 'purchase', 'date' => '2026-09-08 10:00:00',
		'dek'   => 'A free quantity looks like a gift. Whether it saves money depends on one question: will you sell the extra before it expires?',
		'seo'   => 'How to judge a scheme or free quantity on a purchase bill for a medical store, with a worked example and a simple test.',
		'body'  => array(
			$take( array( 'A scheme saves money only if you <strong>sell the extra stock before it expires</strong>.', 'Check how many days of sales the free quantity adds.', 'Record the scheme on the bill, so the rate you remember matches the rate you paid.' ) ),
			sp( 'A distributor’s scheme is a good deal for the distributor too. It moves stock and it moves your decision from “do I need this” to “it is free”. The test is simple.' ),
			sh( 'The test' ),
			$steps( array(
				array( 'Work out days of sales', '', 'Divide the quantity you would receive, free quantity included, by your daily sales.' ),
				array( 'Compare with the shelf life', '', 'If those days are longer than the time left to expiry, the extra will not sell.' ),
				array( 'Compare with your cash', '', 'Even a good scheme on a slow item ties up cash you may need elsewhere.' ),
			) ),
			sh( 'An example' ),
			sb( 'healthray/sum', array( 'title' => 'Days of sales a scheme adds', 'resultLabel' => 'Days of stock after the order', 'resultValue' => '55 days', 'note' => 'Illustrative numbers.' ),
				sb( 'healthray/sum-line', array( 'label' => 'Strips received, free quantity included', 'value' => '220', 'op' => 'none' ) ) . "\n" .
				sb( 'healthray/sum-line', array( 'label' => 'Sales a day', 'value' => '4', 'op' => '÷' ) )
			),
			sp( 'With 55 days of stock and a batch that expires in 40, the scheme loses money, however generous it looks on the bill.' ),
			$note( 'tip', 'Ask for the same scheme in smaller lots', 'Many distributors will split a scheme across two deliveries if you ask. You get the saving without the pile.' ),
			$cta,
		),
	),

	'purchase-return-credit-note' => array(
		'title' => 'Purchase returns and credit notes: a step-by-step routine',
		'topic' => 'purchase', 'date' => '2026-09-15 10:00:00',
		'dek'   => 'A return only helps if the credit note comes back and is matched to the supplier’s account. A routine that makes sure it does.',
		'seo'   => 'A step-by-step routine for returning stock to a supplier and matching the credit note in a medical store.',
		'body'  => array(
			$take( array( 'A return is finished when the <strong>credit note</strong> is entered and the supplier’s account shows it.', 'Return one supplier at a time, from the expiry report grouped by supplier.', 'Follow up on any credit note that has not arrived after a fortnight.' ) ),
			sp( 'Stock that goes back to a distributor is only half the job. The other half is making sure the credit reaches your account, which is where returns most often go missing.' ),
			$steps( array(
				array( 'Pick the batches to return', 'Weekly', 'Use the stock expiry report, grouped by supplier, inside the return window.' ),
				array( 'Prepare one return per supplier', 'Weekly', 'List item, batch, expiry and quantity, and keep a copy.' ),
				array( 'Record the purchase return', 'Same day', 'Enter it in the software so your stock matches what left the shop.' ),
				array( 'Enter the credit note when it arrives', 'On receipt', 'Match it to the return; the supplier’s ledger and dues then show the right balance.' ),
				array( 'Chase what is missing', 'After two weeks', 'A return with no credit note is an unpaid debt.' ),
			) ),
			$note( 'caution', 'Do not net it off in your head', 'Returns netted against the next bill without a credit note are the ones that get disputed. Ask for the note.' ),
			$check( 'Return checklist', array( 'The batch is inside the supplier’s return window.', 'The return is recorded in the software the same day.', 'A copy of the list is kept with the supplier’s name and date.', 'The credit note is entered and matched when it arrives.' ) ),
			$cta,
		),
	),

	'shift-handover' => array(
		'title' => 'A shift handover routine for the pharmacy counter',
		'topic' => 'staff', 'date' => '2026-09-22 10:00:00',
		'dek'   => 'Most mistakes at a counter happen at the changeover. A five-minute routine that keeps the next shift out of trouble.',
		'seo'   => 'A five-minute shift handover routine for a pharmacy counter: cash, pending bills, held items and what the next shift needs to know.',
		'body'  => array(
			$take( array( 'Most errors cluster at the <strong>changeover</strong>: half-finished bills, cash that is not counted, a customer who is “coming back”.', 'A five-minute handover with the same five points every time removes most of them.', 'Each person signs in as themselves, so the handover shows who did what.' ) ),
			sp( 'A counter does not stop when the shift ends. Customers come back, bills are half done and cash is in the drawer. A short, fixed handover stops the day’s loose ends from becoming tomorrow’s argument.' ),
			sh( 'The five points' ),
			$table( array( 'Point', 'What to say or show' ), array(
				array( 'Cash', 'Count it together and note the number.' ),
				array( 'Pending bills', 'Any bills saved as drafts, and for whom.' ),
				array( 'Held items', 'Medicines put aside for a customer who is coming back.' ),
				array( 'Orders and deliveries', 'What is on its way and what has been promised.' ),
				array( 'Anything unusual', 'A difficult customer, a price query, a machine that misbehaved.' ),
			), 'Five things to hand over, every shift.' ),
			$note( 'note', 'Drafts help', 'Saving an unfinished bill as a draft, rather than leaving it on the screen, means the next person can pick it up.' ),
			$note( 'tip', 'Write it down', 'Keep a handover book next to the counter. Two lines are enough, and it is the first thing you look at when something goes wrong.' ),
			$cta,
		),
	),

	'train-counter-assistant' => array(
		'title' => 'How to train a new counter assistant in a week',
		'topic' => 'staff', 'date' => '2026-09-29 10:00:00',
		'dek'   => 'A new assistant can be useful by the end of the first week if you teach in the right order. A day-by-day plan.',
		'seo'   => 'A day-by-day plan to train a new counter assistant in a pharmacy in a week, from the shelf layout to billing and handover.',
		'body'  => array(
			$take( array( 'Teach in the order the work happens: <strong>shelves, billing, then exceptions</strong>.', 'Let them watch a day, do a day with you beside them, then do a day alone with you nearby.', 'Give them their own login on day one.' ) ),
			sp( 'The usual way to train a new assistant is to let them stand next to the busiest person and watch. It works, slowly. A little structure makes it a week instead of a month.' ),
			$steps( array(
				array( 'Shelves and sections', 'Day 1', 'Where things live, how the shelf is ordered, and why the earliest expiry sits at the front.' ),
				array( 'Watch billing', 'Day 2', 'Sit next to someone and follow a full bill from search to print. The walkthroughs inside the app cover sales, purchase, the item master and the account master.' ),
				array( 'Bill with you beside them', 'Day 3', 'They bill, you watch, and you only step in to ask a question.' ),
				array( 'Exceptions', 'Day 4', 'Returns, a wrong batch, a customer who disputes the price, a card that fails.' ),
				array( 'A shift on their own', 'Day 5', 'You are nearby, not beside. Review the day’s bills together at the end.' ),
			) ),
			$note( 'caution', 'Do not share your login', 'A trainee using the owner’s login hides who did what. Create their own, with a role that fits.' ),
			$check( 'End of week one', array( 'They can find the top 50 items without asking.', 'They have billed at least 20 customers on their own login.', 'They know what to do when a batch is wrong.', 'They have done a handover with you.' ) ),
			$cta,
		),
	),

	'after-the-switch-old-data' => array(
		'title' => 'What to do with your old data after you switch pharmacy software',
		'topic' => 'switching', 'date' => '2026-10-05 10:00:00',
		'dek'   => 'Once you are live on the new system, the old one is still holding years of records. What to keep, what to export and when to switch it off.',
		'seo'   => 'What to do with the records in your old pharmacy software after you switch: what to keep, what to export, and when it is safe to stop using it.',
		'body'  => array(
			$take( array( 'Do not switch the old system off the day you go live; keep it <strong>for reference</strong>.', 'Export the reports you may need later, while you can still open them.', 'Decide how long you will keep it, and write the date down.' ) ),
			sp( 'The new software starts with your items and opening stock. Your old sales and purchase history stays behind, and it is the thing people regret not keeping when a question comes up a year later.' ),
			sh( 'What to keep' ),
			$table( array( 'Keep', 'Why', 'How' ), array(
				array( 'Sales and purchase history', 'For your accountant and for any dispute.', 'Export the reports to files and store them with the date.' ),
				array( 'Supplier and customer balances', 'The opening figures on the day you moved.', 'Print the ledger and keep a copy.' ),
				array( 'Item list', 'To compare with the new item master.', 'Keep the file you imported from.' ),
			), 'Three things to keep.' ),
			$note( 'caution', 'Do not assume the old vendor will help later', 'Support for an old system can end when you stop paying. Ask for your exports before you stop, not after.' ),
			sh( 'When to switch it off' ),
			sp( 'When the new system has been live for a full cycle, usually a month-end and a purchase cycle, and the exports are stored. Until then, keep the old system read-only.' ),
			$note( 'tip', 'Write the date in a diary', 'A date to review the old system stops it being kept for years out of habit.' ),
			$cta,
		),
	),

	'from-excel-to-software' => array(
		'title' => 'Switching from Excel and registers to pharmacy software',
		'topic' => 'switching', 'date' => '2026-10-10 10:00:00',
		'dek'   => 'If your stock lives in a register or a spreadsheet, the move is smaller than it looks. A plan that starts with one CSV.',
		'seo'   => 'How to move a medical store from Excel and paper registers to pharmacy software, starting with one CSV of items and opening stock.',
		'body'  => array(
			$take( array( 'A spreadsheet of your items is <strong>most of the work already</strong>.', 'Add batch, expiry and quantity, then import it as initial data.', 'Keep billing as you do today until the numbers agree.' ) ),
			sp( 'Moving from Excel or a register is, in one way, easier than moving from other software: there is nothing to export from and nothing to be locked into. The work is getting what you have into rows.' ),
			sh( 'Getting your data into rows' ),
			$table( array( 'Column', 'Example' ), array(
				array( 'Item name', 'Pantoprazole 40 tablet' ),
				array( 'Batch', 'PT4411' ),
				array( 'Expiry', '2027-06' ),
				array( 'Quantity', '30 strips' ),
				array( 'MRP and purchase rate', 'As on your last bill' ),
			), 'Illustrative rows for a CSV.' ),
			sp( 'Add initial data takes a CSV like this, gives you a sample sheet to follow and shows errors line by line, so a mistyped date does not hide in the middle of the file.' ),
			sh( 'The first two weeks' ),
			$steps( array(
				array( 'Import, then check ten items', 'Day 1', 'Compare ten items against the shelf before you trust the rest.' ),
				array( 'Bill in both for a few days', 'Week 1', 'Keep your register going while you learn the screens.' ),
				array( 'Stop the register', 'Week 2', 'Once the totals agree, bill on the software only.' ),
			) ),
			$note( 'caution', 'Do not skip the check', 'Importing is quick. Trusting an import you have not checked is how a wrong stock number becomes a wrong order.' ),
			$cta,
		),
	),

	'questions-for-vendors' => array(
		'title' => 'Eight questions to ask any pharmacy software vendor',
		'topic' => 'buying', 'date' => '2026-10-16 10:00:00',
		'dek'   => 'Demos are polished. These questions are harder to answer with a script, and the answers tell you what daily work will be like.',
		'seo'   => 'Eight questions to ask any pharmacy software vendor before you buy: data, support, pricing, importing, expiry and staff access.',
		'body'  => array(
			$take( array( 'Ask every vendor the <strong>same eight questions</strong> and compare the answers in writing.', 'Prefer an answer you can see on the screen to an answer you are told.', 'A good vendor will tell you what the software does not do.' ) ),
			sp( 'A demo is built to look good. These questions are the ones that do not fit a script, and they are the ones you will care about in month six.' ),
			$table( array( 'Ask', 'Why it matters', 'A good answer' ), array(
				array( 'Show me a bill from search to print.', 'It tests speed at the counter.', 'Few steps, and the batch is picked for you.' ),
				array( 'How does it handle expiry?', 'Expiry is where stores lose money.', 'A report by item or supplier, and early warning.' ),
				array( 'How do I bring my items and stock?', 'A slow import delays your start.', 'A CSV import with a sample sheet and clear errors.' ),
				array( 'What does it cost in year two and three?', 'Renewals are where surprises are.', 'Published prices, and what is included.' ),
				array( 'Who can see and change what?', 'Staff access protects your records.', 'Roles per person, with extra protection on higher plans.' ),
				array( 'How do I get my data out?', 'You should be able to leave.', 'A clear answer, in writing.' ),
				array( 'How do I get help?', 'You will need it.', 'A phone number and a help centre, not just a form.' ),
				array( 'What does it not do?', 'It shows honesty.', 'A short, specific list.' ),
			), 'Eight questions for any vendor.' ),
			$note( 'caution', 'Get the answers in writing', 'A promise made in a demo is not a promise. Ask for the answer in an email.' ),
			$note( 'tip', 'Bring ten real items', 'Ask the vendor to show them in the software. Real items expose gaps that sample data hides.' ),
			$cta,
		),
	),

	'run-a-good-demo' => array(
		'title' => 'How to get more out of a pharmacy software demo',
		'topic' => 'buying', 'date' => '2026-10-19 10:00:00',
		'dek'   => 'A demo is a short window to see the software with your problems in it. How to prepare, what to ask for and what to write down.',
		'seo'   => 'How to prepare for a pharmacy software demo, what to ask the vendor to show with your own items, and what to write down afterwards.',
		'body'  => array(
			$take( array( 'Go in with <strong>three problems</strong> you want solved, and ask to see each one.', 'Bring real items: a fast mover, a slow one and a near-expiry batch.', 'Write down what you saw, not what you were told.' ) ),
			sp( 'The usual demo goes through the screens in order and finishes with a price. You learn more if you steer it.' ),
			sh( 'Before' ),
			$check( 'Before the demo', array( 'Write your three biggest daily problems on one page.', 'Pick ten real items, including one near expiry.', 'Decide who else should be there: the person who bills the most.', 'Note what you pay today and what the renewal costs.' ) ),
			sh( 'During' ),
			$steps( array(
				array( 'Ask to see your problems first', '', 'Not the tour. Start with the thing that hurts.' ),
				array( 'Bill one of your items from search to print', '', 'Count the steps.' ),
				array( 'Open the expiry report', '', 'Ask how you would return near-expiry stock.' ),
				array( 'Ask about the first month', '', 'Importing, training and what to expect.' ),
			) ),
			sh( 'After' ),
			sp( 'Write down three things you saw work, and three things you were told but did not see. Compare those lists, not the price lists.' ),
			$note( 'tip', 'The best demo is your own data', 'If the vendor will load your items into a test account, you see your store, not a sample.' ),
			$cta,
		),
	),
);

/** Uploads an image once per key (found again by `_rx_src`), replacing the file on re-runs. */
function rx_mp_attachment( string $file, string $key, string $title, string $alt ): int {
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

$alts = array(
	'expiry'    => 'A calendar with a clock and a blister pack',
	'purchase'  => 'A bill with a tick, a price tag and delivery boxes',
	'staff'     => 'Three people with a key and a lock',
	'switching' => 'Two browser windows with arrows swapping between them',
	'buying'    => 'A magnifying glass over a checklist',
);

foreach ( $posts as $slug => $p ) {
	$existing = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids', 'name' => $slug ) );
	$data     = array(
		'post_type'     => 'post',
		'post_status'   => 'publish',
		'post_title'    => $p['title'],
		'post_name'     => $slug,
		'post_content'  => implode( "\n\n", $p['body'] ),
		'post_date'     => $p['date'],
		'post_date_gmt' => get_gmt_from_date( $p['date'] ),
	);
	if ( $existing ) {
		$data['ID'] = (int) $existing[0];
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
	}
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, "FAILED {$slug}: " . $id->get_error_message() . PHP_EOL );
		continue;
	}
	update_post_meta( $id, 'topic', $p['topic'] );
	update_post_meta( $id, 'dek', $p['dek'] );
	update_post_meta( $id, 'updated', substr( $p['date'], 0, 10 ) );
	update_post_meta( $id, '_yoast_wpseo_metadesc', $p['seo'] );

	$att = rx_mp_attachment( $img_dir . $slug . '-feature.png', $slug . '-feature-playful', $p['title'], $alts[ $p['topic'] ] );
	if ( $att ) {
		set_post_thumbnail( $id, $att );
	}
	delete_post_meta( $id, '_rx_read_mins' );
	wp_update_post( array( 'ID' => $id ) );
	echo ( $existing ? 'updated ' : 'created ' ) . "{$slug} (#{$id}), " . rx_blog_read_mins( $id ) . ' min' . PHP_EOL;
}
