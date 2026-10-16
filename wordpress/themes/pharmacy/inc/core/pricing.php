<?php
/**
 * Pure pricing core: validation, plan and prose helpers, rx_term_total,
 * rx_pricing_resolve.
 *
 * Ports of the PHP site's partials/pricing-data.php helpers and the page
 * helpers in pricing.php, with the same output. Differences from the PHP site:
 *
 * - Every helper takes the pricing document $d (the decoded Pricing_API JSON)
 *   instead of reading pricing_data(), and nothing is memoised, so the same
 *   $d always gives the same result.
 * - No WordPress calls, no HTTP, no file reads and no globals: the test bridge
 *   loads this file on its own. Fetching and caching live in inc/pricing.php,
 *   which hands its sources to rx_pricing_resolve().
 * - Prices are only ever read from $d (Requirement 8.4).
 *
 * Needs rx_inr() and rx_join() from inc/core/format.php, which is loaded
 * first (functions.php and the bridge both load it before this file).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;

/**
 * Whether $d has the shape the pricing pages need. Port of pricing_valid().
 *
 * Needs array keys plans, terms, groups, features and statuses, at least one
 * plan and one term, and an id, name and price on every plan.
 *
 * @param mixed $d Decoded pricing document, or anything else.
 */
function rx_pricing_valid( mixed $d ): bool {
	if ( ! is_array( $d ) ) {
		return false;
	}
	foreach ( array( 'plans', 'terms', 'groups', 'features', 'statuses' ) as $key ) {
		if ( ! isset( $d[ $key ] ) || ! is_array( $d[ $key ] ) ) {
			return false;
		}
	}
	if ( ! $d['plans'] || ! $d['terms'] ) {
		return false;
	}
	foreach ( $d['plans'] as $plan ) {
		if ( ! isset( $plan['id'], $plan['name'], $plan['price'] ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Plans sorted by the backend's sort_order, keyed by id. Port of pricing_plans().
 *
 * @param array $d Valid pricing document.
 * @return array<int|string, array> Plans keyed by id.
 */
function rx_pricing_plans( array $d ): array {
	$plans = $d['plans'];
	usort( $plans, static fn( $a, $b ) => ( $a['sort_order'] ?? 0 ) <=> ( $b['sort_order'] ?? 0 ) );
	return array_column( $plans, null, 'id' );
}

/**
 * One plan by id, or null. Port of pricing_plan().
 *
 * @param array $d  Valid pricing document.
 * @param int   $id Plan id (1 Starter, 3 Basic, 2 Advance, 5 Enterprise in the snapshot).
 */
function rx_pricing_plan( array $d, int $id ): ?array {
	return rx_pricing_plans( $d )[ $id ] ?? null;
}

/**
 * "₹4,999" for a paid plan, "Free" for a free one, "Custom" for a quoted one,
 * '' for an unknown id. Port of plan_price().
 *
 * @param array $d  Valid pricing document.
 * @param int   $id Plan id.
 */
function rx_plan_price( array $d, int $id ): string {
	$plan = rx_pricing_plan( $d, $id );
	if ( ! $plan ) {
		return '';
	}
	if ( ! empty( $plan['is_custom'] ) ) {
		return 'Custom';
	}
	return $plan['price'] > 0 ? rx_inr( $plan['price'] ) : 'Free';
}

/**
 * Monthly bill limits of a plan: [ 'sales' => 10, 'purchase' => 25 ].
 * A null entry means unlimited (also for an unknown id). Port of plan_bill_limits().
 *
 * @param array $d  Valid pricing document.
 * @param int   $id Plan id.
 * @return array{sales: ?int, purchase: ?int}
 */
function rx_plan_bill_limits( array $d, int $id ): array {
	$limits = rx_pricing_plan( $d, $id )['bill_limit'] ?? null;
	$limits = is_array( $limits ) ? $limits : array();
	return array(
		'sales'    => isset( $limits['INE'] ) ? (int) $limits['INE'] : null,
		'purchase' => isset( $limits['PUR'] ) ? (int) $limits['PUR'] : null,
	);
}

/**
 * Term lengths as prose: "1, 3 or 5 years", or "1 year" for a single
 * one-year term. Port of pricing_terms_text().
 *
 * @param array $d Valid pricing document.
 */
function rx_pricing_terms_text( array $d ): string {
	$years = array_map( static fn( $term ) => (int) $term['years'], $d['terms'] );
	sort( $years );
	if ( 1 === count( $years ) ) {
		return $years[0] . ( 1 === $years[0] ? ' year' : ' years' );
	}
	$last = array_pop( $years );
	return implode( ', ', $years ) . ' or ' . $last . ' years';
}

/**
 * Plans split into free, paid and custom lists, each in sort_order.
 * Port of pricing_split().
 *
 * @param array $d Valid pricing document.
 * @return array{free: array[], paid: array[], custom: array[]}
 */
function rx_pricing_split( array $d ): array {
	$out = array(
		'free'   => array(),
		'paid'   => array(),
		'custom' => array(),
	);
	foreach ( rx_pricing_plans( $d ) as $plan ) {
		$kind           = ! empty( $plan['is_custom'] ) ? 'custom' : ( $plan['price'] > 0 ? 'paid' : 'free' );
		$out[ $kind ][] = $plan;
	}
	return $out;
}

/**
 * Cost answer: "Starter is free. Basic is ₹4,999 a year and Advance is
 * ₹9,999 a year, plus 18% GST. Enterprise for chains is quoted."
 * Port of pricing_cost_text().
 *
 * @param array $d Valid pricing document.
 */
function rx_pricing_cost_text( array $d ): string {
	$split = rx_pricing_split( $d );
	$out   = array();
	if ( $split['free'] ) {
		$out[] = rx_join( array_column( $split['free'], 'name' ) ) . ( count( $split['free'] ) > 1 ? ' are' : ' is' ) . ' free.';
	}
	if ( $split['paid'] ) {
		$out[] = rx_join( array_map( static fn( $plan ) => $plan['name'] . ' is ' . rx_inr( $plan['price'] ) . ' a year', $split['paid'] ) ) . ', plus 18% GST.';
	}
	if ( $split['custom'] ) {
		$out[] = rx_join( array_column( $split['custom'], 'name' ) ) . ' for chains is quoted.';
	}
	return implode( ' ', $out );
}

/**
 * Comparison cell: "Free Starter; ₹4,999 or ₹9,999 a year, published".
 * Port of pricing_compare_text().
 *
 * @param array $d Valid pricing document.
 */
function rx_pricing_compare_text( array $d ): string {
	$split = rx_pricing_split( $d );
	$out   = array();
	if ( $split['free'] ) {
		$out[] = 'Free ' . rx_join( array_column( $split['free'], 'name' ) );
	}
	if ( $split['paid'] ) {
		$out[] = rx_join( array_map( static fn( $plan ) => rx_inr( $plan['price'] ), $split['paid'] ), 'or' ) . ' a year, published';
	}
	return implode( '; ', $out );
}

/**
 * Lowest plan (by sort_order) that includes a feature, looked up by the API's
 * unique_name (for example 'feat_purchase_order'). Null when the API doesn't
 * list the feature or no plan includes it. Port of feature_plan(), without
 * its static index.
 *
 * @param array  $d      Valid pricing document.
 * @param string $unique Feature unique_name.
 */
function rx_feature_plan( array $d, string $unique ): ?array {
	// Same index as feature_plan(): unique_name => plan ids listed in statuses.
	$index = array();
	$by_id = array_column( $d['features'], 'unique_name', 'id' );
	foreach ( $d['statuses'] as $status ) {
		if ( isset( $by_id[ $status['pricing_feature_id'] ] ) ) {
			$index[ $by_id[ $status['pricing_feature_id'] ] ][] = (int) $status['plan_template_id'];
		}
	}
	if ( empty( $index[ $unique ] ) ) {
		return null;
	}
	// Plans are in sort_order, so the first match is the lowest plan.
	foreach ( rx_pricing_plans( $d ) as $id => $plan ) {
		if ( in_array( (int) $id, $index[ $unique ], true ) ) {
			return $plan;
		}
	}
	return null;
}

/**
 * Plan badge text for a feature: "Every plan" (first plan), "Enterprise"
 * (last plan's name) or "Basic and up". Null when not mapped.
 * Port of feature_plan_label().
 *
 * @param array  $d      Valid pricing document.
 * @param string $unique Feature unique_name.
 */
function rx_feature_plan_label( array $d, string $unique ): ?string {
	$plan = rx_feature_plan( $d, $unique );
	if ( ! $plan ) {
		return null;
	}
	$plans = rx_pricing_plans( $d );
	$first = reset( $plans );
	$last  = end( $plans );
	if ( (int) $plan['id'] === (int) $first['id'] ) {
		return 'Every plan';
	}
	if ( (int) $plan['id'] === (int) $last['id'] ) {
		return $plan['name'];
	}
	return $plan['name'] . ' and up';
}

/**
 * Users per plan as prose: "Starter 1, Basic 3, Advance 10 and Enterprise
 * unlimited". Port of plan_users_text().
 *
 * @param array $d Valid pricing document.
 */
function rx_plan_users_text( array $d ): string {
	return rx_join( array_map( static fn( $plan ) => $plan['name'] . ' ' . ( $plan['user_limit'] ?? 'unlimited' ), array_values( rx_pricing_plans( $d ) ) ) );
}

/**
 * Included message credits per plan: "Starter 10 WhatsApp, 10 SMS and 10
 * email; Basic …". Every plan in sort_order, custom plans included.
 * Port of plan_credits_text().
 *
 * @param array $d Valid pricing document.
 */
function rx_plan_credits_text( array $d ): string {
	$out = array();
	foreach ( rx_pricing_plans( $d ) as $plan ) {
		$credits = $plan['message_service'] ?? array();
		$out[]   = $plan['name'] . ' ' . rx_join(
			array(
				(int) ( $credits['whatsapp'] ?? 0 ) . ' WhatsApp',
				(int) ( $credits['sms'] ?? 0 ) . ' SMS',
				(int) ( $credits['email'] ?? 0 ) . ' email',
			)
		);
	}
	return implode( '; ', $out );
}

/**
 * Term line under a price: "About ₹417 a month" for one year, "Billed ₹14,997
 * for 3 years" otherwise. Yearly price × years, less the term's
 * discount_percent: the same rule as assets/js/pricing.js. Port of
 * term_total() in pricing.php. Needs only the price and one term of $d.
 *
 * @param int   $price Yearly plan price in rupees.
 * @param array $term  One entry of $d['terms'] (years, discount_percent).
 */
function rx_term_total( int $price, array $term ): string {
	$years = max( 1, (int) $term['years'] );
	$total = $price * $years * ( 1 - (float) ( $term['discount_percent'] ?? 0 ) / 100 );
	return 1 === $years
		? 'About ' . rx_inr( $total / 12 ) . ' a month'
		: 'Billed ' . rx_inr( $total ) . ' for ' . $years . ' years';
}

/**
 * Plan phrases for the pricing meta description, in sort_order:
 * [ 'Starter free forever', 'Basic ₹4,999/year', …, 'Enterprise for chains' ].
 * The caller joins them as pricing.php does:
 * 'Simple yearly plans for medical stores: ' . implode( ', ', $bits ) . '. Prices exclude 18% GST. Compare every feature.'
 *
 * @param array $d Valid pricing document.
 * @return string[]
 */
function rx_pricing_meta_bits( array $d ): array {
	$bits = array();
	foreach ( rx_pricing_plans( $d ) as $plan ) {
		if ( ! empty( $plan['is_custom'] ) ) {
			$bits[] = $plan['name'] . ' for chains';
		} elseif ( $plan['price'] > 0 ) {
			$bits[] = $plan['name'] . ' ' . rx_inr( $plan['price'] ) . '/year';
		} else {
			$bits[] = $plan['name'] . ' free forever';
		}
	}
	return $bits;
}

/**
 * Pick the pricing document from the first usable source.
 *
 * Sources are tried in the fixed order cache → live → stale → snapshot,
 * whatever the order of the keys in $sources. Each source is a callable that
 * takes no arguments and returns the decoded document (or anything else when
 * it has none). It is called only when every earlier source failed, so live
 * is never called while the cache is valid. A missing or non-callable source
 * is skipped. A result that fails rx_pricing_valid(), or a source that
 * throws, counts as no data and the next source is tried.
 *
 * Hooks, each called with the chosen document:
 * - on_live:  only when the live source is used (refresh the cache and the stale copy).
 * - on_stale: only when the stale source is used (cache it briefly so the API is retried later).
 * A hook that throws is ignored; the document is still returned.
 *
 * Never throws.
 *
 * @param array<string, callable> $sources Keys 'cache', 'live', 'stale', 'snapshot'.
 * @param array<string, callable> $hooks   Keys 'on_live', 'on_stale' (both optional).
 * @return array{0: ?array, 1: string} [ document, source name ], or [ null, 'none' ]
 *         when no source gave a valid document.
 */
function rx_pricing_resolve( array $sources, array $hooks = array() ): array {
	$hook_for = array(
		'live'  => 'on_live',
		'stale' => 'on_stale',
	);

	foreach ( array( 'cache', 'live', 'stale', 'snapshot' ) as $name ) {
		$source = $sources[ $name ] ?? null;
		if ( ! is_callable( $source ) ) {
			continue;
		}

		try {
			$d = $source();
		} catch ( Throwable ) {
			continue;
		}
		if ( ! rx_pricing_valid( $d ) ) {
			continue;
		}

		$hook = isset( $hook_for[ $name ] ) ? ( $hooks[ $hook_for[ $name ] ] ?? null ) : null;
		if ( is_callable( $hook ) ) {
			try {
				$hook( $d );
			} catch ( Throwable ) {
				// A failed cache write must not hide good data.
			}
		}

		return array( $d, $name );
	}

	return array( null, 'none' );
}
