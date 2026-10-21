<?php
/**
 * Sets the site timezone to India Standard Time (GMT +5:30) and a clean date/time format.
 *
 * Run: php wordpress/import/timezone-setup.php   (local install only; safe to re-run)
 *
 * @package pharmacy
 */

require_once __DIR__ . '/lib/guard.php';
rx_bootstrap_wp();
rx_assert_local();

update_option( 'timezone_string', 'Asia/Kolkata' );
update_option( 'gmt_offset', '' ); // the named zone wins; an offset would conflict
update_option( 'date_format', 'j M Y' );
update_option( 'time_format', 'g:i a' );
update_option( 'start_of_week', 1 );

echo 'Timezone: ' . get_option( 'timezone_string' ) . ' (' . wp_timezone()->getOffset( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) ) / 3600 . "h)\n";
echo 'Now: ' . wp_date( 'j M Y, g:i a' ) . "\n";
