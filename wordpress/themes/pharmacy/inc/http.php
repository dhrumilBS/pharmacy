<?php
/**
 * Outbound HTTP allow-list (pre_http_request).
 *
 * Every request made through the WordPress HTTP API (wp_remote_get() and
 * friends, including core, plugin and Yoast update checks) is checked with
 * rx_host_allowed() from inc/core/http.php. Only local hosts and the
 * Pricing_API host pass; anything else gets a WP_Error and no connection is
 * made (Requirement 1.4).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * Runs last, so a later filter cannot turn a block back into a request.
 * Allowed URLs keep the incoming $preempt, so earlier filters still work.
 */
add_filter( 'pre_http_request', 'rx_http_allow_list', PHP_INT_MAX, 3 );

/**
 * Block outbound requests to hosts outside the allow-list.
 *
 * Fails closed: when rx_setting() is not available, the Pricing_API host is
 * unknown and only local hosts are allowed.
 *
 * @param false|array|WP_Error $preempt     Response from earlier filters, or false.
 * @param array                $parsed_args HTTP request arguments.
 * @param string               $url         Request URL.
 * @return false|array|WP_Error $preempt unchanged when allowed, else a WP_Error.
 */
function rx_http_allow_list( $preempt, $parsed_args, $url ) {
	$url     = is_string( $url ) ? $url : '';
	$api_url = function_exists( 'rx_setting' ) ? (string) rx_setting( 'pricing_api' ) : '';

	if ( rx_host_allowed( $url, $api_url ) ) {
		return $preempt;
	}

	$host = rx_parse_host( $url );

	return new WP_Error(
		'rx_blocked',
		sprintf(
			/* translators: %s: host name of the blocked request. */
			__( 'Outbound request to %s blocked: only local hosts and the pricing API are allowed.', 'pharmacy' ),
			'' !== $host ? $host : __( 'an invalid URL', 'pharmacy' )
		)
	);
}
