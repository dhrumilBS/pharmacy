<?php
/**
 * Pure host checks: rx_is_local_host, rx_host_allowed.
 *
 * No WordPress calls: the test bridge and the import guard load this file
 * without the theme. inc/http.php uses these checks in its pre_http_request
 * filter, so the only hosts the site may call are this PC's local hosts and
 * the Pricing_API host (Requirement 1.4).
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || defined( 'RX_TESTING' ) || exit;

/**
 * Normalise a host name for comparison: lower case, one trailing dot removed
 * ("Example.com." and "example.com" name the same host).
 *
 * @param string $host Host name.
 * @return string Normalised host, or '' when nothing is left.
 */
function rx_normalise_host( string $host ): string {
	$host = strtolower( $host );
	if ( str_ends_with( $host, '.' ) ) {
		$host = substr( $host, 0, -1 );
	}
	return $host;
}

/**
 * Host of a URL, parsed the way wp_parse_url() does.
 *
 * - Protocol-relative URLs ("//host/path") give their host.
 * - Userinfo is ignored: "http://localhost@evil.com/" gives "evil.com".
 * - The host is normalised with rx_normalise_host().
 * - Fails closed: a URL holding whitespace, control characters or a
 *   backslash (which other URL parsers read differently), or one that
 *   parse_url() cannot read or that has no host, gives ''.
 *
 * @param string $url Absolute or protocol-relative URL.
 * @return string Host, or '' when there is no usable host.
 */
function rx_parse_host( string $url ): string {
	if ( '' === $url || preg_match( '/[\x00-\x20\x7f\\\\]/', $url ) ) {
		return '';
	}

	// Same placeholder trick as wp_parse_url() for protocol-relative URLs.
	$relative = str_starts_with( $url, '//' );
	$parts    = parse_url( $relative ? 'placeholder:' . $url : $url );
	if ( ! is_array( $parts ) || ! isset( $parts['host'] ) || ! is_string( $parts['host'] ) ) {
		return '';
	}

	return rx_normalise_host( $parts['host'] );
}

/**
 * Whether a host is on this PC or its local network.
 *
 * Local means exactly "localhost", "127.0.0.1", or a full dotted-quad
 * 192.168.x.x address with each of the last two octets 0–255 and no leading
 * zeros. "localhost.evil.com", "192.168.evil.com" and "192.168.0.256" are not
 * local. IPv6 hosts (for example "[::1]") are not local.
 *
 * Case-insensitive; one trailing dot is ignored.
 *
 * @param string $host Host name, without scheme, port or path.
 */
function rx_is_local_host( string $host ): bool {
	$host = rx_normalise_host( $host );

	if ( 'localhost' === $host || '127.0.0.1' === $host ) {
		return true;
	}

	$octet = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])';
	return 1 === preg_match( '/^192\.168\.' . $octet . '\.' . $octet . '$/D', $host );
}

/**
 * Whether an outbound request to $url is allowed.
 *
 * Allowed: a local host (rx_is_local_host()) or exactly the host of $api_url
 * (the Pricing_API), compared case-insensitively. Port, scheme and path are
 * not compared. When $api_url is empty or has no usable host, only local
 * hosts are allowed. A URL without a usable host is never allowed.
 *
 * @param string $url     URL of the outbound request.
 * @param string $api_url Pricing_API URL from Site_Settings ('' when not set).
 */
function rx_host_allowed( string $url, string $api_url ): bool {
	$host = rx_parse_host( $url );
	if ( '' === $host ) {
		return false;
	}

	if ( rx_is_local_host( $host ) ) {
		return true;
	}

	$api_host = rx_parse_host( trim( $api_url ) );
	return '' !== $api_host && $host === $api_host;
}
