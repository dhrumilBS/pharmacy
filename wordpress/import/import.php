<?php
/**
 * Import: fill the local WordPress install (pharmacy-wp) with the PHP_Site
 * content. Re-runnable: every run updates the same objects (found by their
 * `_rx_source` meta), so IDs stay the same and nothing is duplicated.
 *
 * Usage, from the repository root with XAMPP's PHP:
 *
 *     D:\xampp\php\php.exe wordpress\import\import.php             import
 *     D:\xampp\php\php.exe wordpress\import\import.php --dry-run   check only, write nothing
 *
 * --content=DIR reads the data files from another folder with the same
 * layout (settings.php, menus.php, pages/, posts/), e.g. to check drafts.
 *
 * Steps (design §13):
 *  1. Guard: CLI only, this PC's pharmacy-wp only (lib/guard.php). A real
 *     run also needs the pharmacy theme and Secure Custom Fields active
 *     (wordpress/import/setup.php does that) and stops before any write
 *     otherwise.
 *     Then every data file in content/ is loaded and checked.
 *  2. Site_Settings from content/settings.php.
 *  3. Pages from content/pages/*.php, parents first, each followed by its
 *     SCF fields.
 *  4. Posts from content/posts/*.php, each followed by its SCF fields. Fields
 *     that reference posts (the Blog page's start_here) are written after
 *     all posts exist.
 *  5. Reading settings: show_on_front = page, page_on_front = page key
 *     "home", page_for_posts = "blog", wp_page_for_privacy_policy =
 *     "privacy". Each is set only when that page exists and is published.
 *  6. Menus from content/menus.php with their items and menu-item fields,
 *     then the theme menu locations.
 *  7. Logo attachment for Yoast's Organization: the theme's
 *     assets/img/healthray-logo.svg as an attachment (found by `_rx_source`,
 *     created once), then Yoast's company_logo and company_logo_id.
 *  8. The WordPress sample content, deleted once: post "Hello world!" (and
 *     its comments), page "Sample Page" and the draft page "Privacy Policy",
 *     each only when it has no `_rx_source`. Option rx_import_samples_deleted
 *     records what was handled, so later runs touch nothing. This is the only
 *     deletion in the import (stale menu items aside, which rx_upsert_menu()
 *     removes to keep each menu equal to its data, and a logo file step 7
 *     copied in the same run when registering it as an attachment fails).
 *  9. flush_rewrite_rules( false ), then a summary of created, updated,
 *     unchanged, deleted, failed, missing and skipped counts per type.
 *
 * A data file that does not exist yet is reported and counted as missing;
 * the run goes on with the files that exist. An item that fails is reported
 * with its file, and the run goes on with the next item.
 *
 * --dry-run loads and checks every data file (item shape, duplicates, parents,
 * templates, page keys and slugs already taken, field names and values,
 * menus) and prints what a real run would do. It writes nothing: no step
 * writes in a dry run, and a `query` filter blocks (and reports) any
 * database write that would still be attempted. Field names are checked with
 * SCF when it is active, else against the theme's acf-json/ files.
 *
 * Exit code: 0 when every step succeeded and every expected data file
 * exists, 1 otherwise (2 for a usage error).
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

require_once __DIR__ . '/lib/guard.php';
require_once __DIR__ . '/lib/upsert.php';

/** Default data files folder. */
const RX_IMPORT_CONTENT = __DIR__ . '/content';

/** Theme field groups, used when SCF is not active (dry run). */
const RX_IMPORT_ACF_JSON = __DIR__ . '/../themes/pharmacy/acf-json';

/** Option recording which WordPress sample items step 8 has handled. */
const RX_IMPORT_SAMPLES_OPTION = 'rx_import_samples_deleted';

/** Parent theme file the logo attachment is made from; also its `_rx_source` (step 7). */
const RX_IMPORT_LOGO_SOURCE = 'assets/img/healthray-logo.svg';

/** MIME type of the logo attachment. */
const RX_IMPORT_LOGO_MIME = 'image/svg+xml';

/*
 * -------------------------------------------------------------------------
 * Run state and output
 * -------------------------------------------------------------------------
 */

/**
 * Counts and messages of one import run.
 */
final class Rx_Import_Run {

	/** Summary rows: key => label. */
	public const ROWS = array(
		'settings'   => 'Site settings',
		'pages'      => 'Pages',
		'posts'      => 'Posts',
		'fields'     => 'Page/post fields',
		'reading'    => 'Reading settings',
		'menus'      => 'Menus',
		'menu_items' => 'Menu items',
		'locations'  => 'Menu locations',
		'logo'       => 'Logo attachment',
		'samples'    => 'Sample content',
		'rewrite'    => 'Rewrite rules',
	);

	/** Summary columns. */
	public const COLUMNS = array( 'created', 'updated', 'unchanged', 'deleted', 'failed', 'missing', 'skipped' );

	/** @var array<string, array<string, int>> Row => column => count. */
	public array $counts = array();

	/** @var array<int, array{label: string, problems: string[]}> Failures in run order. */
	public array $failures = array();

	/** @var string[] Expected data files that do not exist. */
	public array $missing = array();

	/** @var string[] Database writes blocked during a dry run. */
	public array $blocked = array();

	/**
	 * @param bool $dry Dry run: check only, write nothing.
	 */
	public function __construct( public readonly bool $dry ) {
		foreach ( array_keys( self::ROWS ) as $row ) {
			$this->counts[ $row ] = array_fill_keys( self::COLUMNS, 0 );
		}
	}

	/**
	 * Add to a count.
	 *
	 * @param string $row    Row key.
	 * @param string $column Column.
	 * @param int    $n      Amount.
	 */
	public function add( string $row, string $column, int $n = 1 ): void {
		$this->counts[ $row ][ $column ] += $n;
	}

	/**
	 * Record and print a failure.
	 *
	 * @param string          $row      Row key.
	 * @param string          $label    What failed, e.g. the data file.
	 * @param string|string[] $problems One message per problem.
	 */
	public function fail( string $row, string $label, string|array $problems ): void {
		$problems = array_values( array_filter( array_map( 'strval', (array) $problems ), static fn( $p ) => '' !== trim( $p ) ) );
		if ( ! $problems ) {
			$problems = array( 'failed' );
		}
		$this->add( $row, 'failed' );
		$this->failures[] = array(
			'label'    => $label,
			'problems' => $problems,
		);
		rx_import_line( 'FAILED', $label );
		foreach ( $problems as $problem ) {
			rx_import_detail( '- ' . $problem );
		}
	}

	/**
	 * Record an expected data file that does not exist.
	 *
	 * @param string $row  Row key.
	 * @param string $file File, relative to wordpress/import/.
	 */
	public function missing( string $row, string $file ): void {
		$this->add( $row, 'missing' );
		$this->missing[] = $file;
	}

	/**
	 * Status word for the log: "would …" in a dry run.
	 *
	 * @param string $done Word for a real run, e.g. 'created'.
	 */
	public function word( string $done ): string {
		if ( ! $this->dry ) {
			return $done;
		}
		$would = array(
			'created' => 'would create',
			'updated' => 'would update',
			'deleted' => 'would delete',
			'set'     => 'would set',
			'written' => 'would write',
			'flushed' => 'would flush',
		);
		return $would[ $done ] ?? $done;
	}

	/**
	 * Total of one column over all rows.
	 *
	 * @param string $column Column.
	 */
	public function total( string $column ): int {
		return array_sum( array_column( $this->counts, $column ) );
	}
}

/**
 * Print a step heading.
 *
 * @param string $number Step number, e.g. '3/9', or '' for an unnumbered block.
 * @param string $title  Heading.
 */
function rx_import_step( string $number, string $title ): void {
	echo PHP_EOL, '' !== $number ? "[{$number}] " : '', $title, PHP_EOL;
}

/**
 * Print one log line: a padded status word and a text.
 *
 * @param string $status Status word, e.g. 'created'.
 * @param string $text   Text.
 */
function rx_import_line( string $status, string $text ): void {
	echo '  ', str_pad( $status, 13 ), ' ', $text, PHP_EOL;
}

/**
 * Print an indented detail line under the previous log line, wrapped.
 *
 * @param string $text Text.
 */
function rx_import_detail( string $text ): void {
	$indent = str_repeat( ' ', 18 );
	echo $indent, wordwrap( $text, 110, PHP_EOL . $indent . '  ', true ), PHP_EOL;
}

/**
 * Usage text.
 */
function rx_import_usage(): string {
	return 'Usage: php wordpress/import/import.php [--dry-run] [--content=DIR]' . PHP_EOL . PHP_EOL
		. '  --dry-run, -n   Load and check every data file and print what would happen; write nothing.' . PHP_EOL
		. '  --content=DIR   Read the data files from DIR (same layout as wordpress/import/content/).' . PHP_EOL
		. '  --help, -h      Show this help.' . PHP_EOL;
}

/**
 * Parse the command line.
 *
 * @param string[] $argv Arguments, script name first.
 * @return array{dry: bool, help: bool, content: string}|string Options, or an error message.
 */
function rx_import_parse_args( array $argv ): array|string {
	$args = array(
		'dry'     => false,
		'help'    => false,
		'content' => RX_IMPORT_CONTENT,
	);
	foreach ( array_slice( $argv, 1 ) as $arg ) {
		if ( str_starts_with( (string) $arg, '--content=' ) ) {
			$dir = realpath( substr( (string) $arg, strlen( '--content=' ) ) );
			if ( false === $dir || ! is_dir( $dir ) ) {
				return 'Not a folder: ' . substr( (string) $arg, strlen( '--content=' ) );
			}
			$args['content'] = $dir;
			continue;
		}
		switch ( $arg ) {
			case '--dry-run':
			case '-n':
				$args['dry'] = true;
				break;
			case '--help':
			case '-h':
				$args['help'] = true;
				break;
			default:
				return 'Unknown argument: ' . $arg;
		}
	}
	return $args;
}

/**
 * Folder the data files are read from.
 *
 * @param string|null $set Folder to use from now on (main() sets it once).
 */
function rx_import_content_dir( ?string $set = null ): string {
	static $dir = RX_IMPORT_CONTENT;
	if ( null !== $set ) {
		$dir = rtrim( str_replace( '\\', '/', $set ), '/' );
	}
	return $dir;
}

/**
 * Dry run safety net: block every database write from here on and record it.
 *
 * No dry-run code path writes; this makes sure of it, including writes by
 * hooks the checks might trigger.
 *
 * @param Rx_Import_Run $run Run.
 */
function rx_import_block_writes( Rx_Import_Run $run ): void {
	add_filter(
		'query',
		static function ( $query ) use ( $run ) {
			if ( is_string( $query ) && preg_match( '/^\s*(?:INSERT|UPDATE|DELETE|REPLACE|ALTER|CREATE|DROP|TRUNCATE|RENAME)\b/i', $query ) ) {
				$run->blocked[] = mb_substr( (string) preg_replace( '/\s+/', ' ', trim( $query ) ), 0, 140 );
				return '';
			}
			return $query;
		},
		PHP_INT_MAX
	);
}

/**
 * Run a step, turning an unexpected exception into a failure so the
 * remaining steps still run.
 *
 * @param Rx_Import_Run $run   Run.
 * @param string        $row   Row key for a failure.
 * @param string        $label Step label for a failure.
 * @param callable      $step  Step.
 * @return mixed The step's return value, or null after an exception.
 */
function rx_import_guarded( Rx_Import_Run $run, string $row, string $label, callable $step ): mixed {
	try {
		return $step();
	} catch ( Throwable $e ) {
		$run->fail( $row, $label, get_class( $e ) . ': ' . $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ')' );
		return null;
	}
}

/*
 * -------------------------------------------------------------------------
 * Data files
 * -------------------------------------------------------------------------
 */

/**
 * Data files the import expects (tasks 4.3 and 6.3–6.7). Files in the
 * folders that are not listed here are imported too.
 *
 * @return array{settings: string[], menus: string[], pages: string[], posts: string[]}
 */
function rx_import_expected(): array {
	return array(
		'settings' => array( 'settings.php' ),
		'menus'    => array( 'menus.php' ),
		'pages'    => array(
			'about.php',
			'blog.php',
			'contact.php',
			'cookie-policy.php',
			'data-security.php',
			'demo.php',
			'faq.php',
			'features-billing.php',
			'features-expiry.php',
			'features-gst.php',
			'features-inventory.php',
			'features-purchase.php',
			'features-reports.php',
			'features-retention.php',
			'features-security.php',
			'features.php',
			'home.php',
			'pricing.php',
			'privacy.php',
			'refund-policy.php',
			'support.php',
			'switch.php',
			'terms.php',
		),
		'posts'    => array(
			'check-distributor-bill.php',
			'cut-expiry-loss.php',
			'desktop-vs-browser.php',
			'min-max-stock.php',
			'staff-logins-roles.php',
			'switch-pharmacy-software.php',
		),
	);
}

/**
 * Load one data file in its own scope.
 *
 * @param string $file Absolute path.
 * @return array{value: mixed, output: string}|WP_Error The returned value and
 *                                                    anything it printed, or an error.
 */
function rx_import_load_file( string $file ): array|WP_Error {
	ob_start();
	try {
		$value = ( static function () {
			return require func_get_arg( 0 );
		} )( $file );
	} catch ( Throwable $e ) {
		ob_end_clean();
		return new WP_Error( 'rx_data_load', get_class( $e ) . ': ' . $e->getMessage() . ' (line ' . $e->getLine() . ')' );
	}
	return array(
		'value'  => $value,
		'output' => (string) ob_get_clean(),
	);
}

/**
 * Load content/settings.php or content/menus.php.
 *
 * @param Rx_Import_Run $run   Run.
 * @param string        $row   Row key.
 * @param string        $name  File name in content/.
 * @param string        $shape 'map' (field name => value) or 'list' (list of arrays).
 * @return array|null The data, or null when the file is missing or unusable.
 */
function rx_import_collect_file( Rx_Import_Run $run, string $row, string $name, string $shape ): ?array {
	$label = 'content/' . $name;
	$path  = rx_import_content_dir() . '/' . $name;
	if ( ! is_file( $path ) ) {
		$run->missing( $row, $label );
		rx_import_line( 'missing', $label );
		return null;
	}

	$loaded = rx_import_load_file( $path );
	if ( is_wp_error( $loaded ) ) {
		$run->fail( $row, $label, $loaded->get_error_message() );
		return null;
	}

	$value = $loaded['value'];
	if ( 'map' === $shape && ( ! is_array( $value ) || ! $value || array_is_list( $value ) ) ) {
		$run->fail( $row, $label, 'must return a non-empty array of field name => value' );
		return null;
	}
	if ( 'list' === $shape && ( ! is_array( $value ) || ! array_is_list( $value ) || array_filter( $value, static fn( $v ) => ! is_array( $v ) ) ) ) {
		$run->fail( $row, $label, 'must return a list of arrays' );
		return null;
	}

	rx_import_line( 'ok', $label . ' (' . count( $value ) . ( 'map' === $shape ? ' field(s))' : ' entries)' ) );
	if ( '' !== trim( $loaded['output'] ) ) {
		rx_import_detail( 'note: the file printed output, which was ignored' );
	}
	return $value;
}

/**
 * Load every data file of content/pages/ or content/posts/.
 *
 * @param Rx_Import_Run $run  Run.
 * @param string        $kind 'pages' or 'posts' (also the row key).
 * @return array<string, array> 'pages/home.php' => item, sorted by file name.
 */
function rx_import_collect_dir( Rx_Import_Run $run, string $kind ): array {
	$dir      = rx_import_content_dir() . '/' . $kind;
	$expected = rx_import_expected()[ $kind ];
	$names    = array();
	if ( is_dir( $dir ) ) {
		foreach ( glob( $dir . '/*.php' ) ?: array() as $file ) {
			$names[] = basename( $file );
		}
		sort( $names, SORT_STRING );
	}

	$missing = array_values( array_diff( $expected, $names ) );
	$extra   = array_values( array_diff( $names, $expected ) );
	foreach ( $missing as $name ) {
		$run->missing( $kind, "content/{$kind}/{$name}" );
	}

	rx_import_line(
		$missing ? 'missing' : 'ok',
		sprintf(
			'content/%s/: %d of %d expected file(s) found%s',
			$kind,
			count( $expected ) - count( $missing ),
			count( $expected ),
			$extra ? ', plus ' . count( $extra ) . ' more (' . implode( ', ', $extra ) . ')' : ''
		)
	);
	if ( ! is_dir( $dir ) ) {
		rx_import_detail( 'the folder does not exist yet' );
	}
	if ( $missing ) {
		rx_import_detail( 'missing: ' . implode( ', ', $missing ) );
	}

	$items = array();
	foreach ( $names as $name ) {
		$rel    = "{$kind}/{$name}";
		$loaded = rx_import_load_file( "{$dir}/{$name}" );
		if ( is_wp_error( $loaded ) ) {
			$run->fail( $kind, $rel, $loaded->get_error_message() );
			continue;
		}
		$value = $loaded['value'];
		if ( ! is_array( $value ) || ! $value || array_is_list( $value ) ) {
			$run->fail( $kind, $rel, 'must return one import item (an array with source, title, slug, ...)' );
			continue;
		}
		if ( '' !== trim( $loaded['output'] ) ) {
			rx_import_line( 'note', "{$rel} printed output, which was ignored" );
		}
		$items[ $rel ] = $value;
	}
	return $items;
}

/*
 * -------------------------------------------------------------------------
 * Plan: validate items, order pages parents first
 * -------------------------------------------------------------------------
 */

/**
 * Validate every page and post item and put them in import order.
 *
 * Problems found here (invalid item, duplicate source, page key or slug,
 * parent loop, parent with problems, fields without a group) keep the item
 * out of the run; it is reported as failed in its step.
 *
 * @param array<string, array> $pages 'pages/x.php' => item.
 * @param array<string, array> $posts 'posts/x.php' => item.
 * @return array{pages: array[], posts: array[], keys: array<string, string>, refs: array[]}
 *         Entries in import order, page key => status of the valid pages, and
 *         the references (type, source, key, slug) of every valid item.
 */
function rx_import_plan( array $pages, array $posts ): array {
	$entries = array();
	foreach ( array(
		'pages' => array( 'page', $pages ),
		'posts' => array( 'post', $posts ),
	) as $kind => [ $type, $items ] ) {
		foreach ( $items as $rel => $item ) {
			$problems = array();
			if ( 'post' === $type && ! array_key_exists( 'type', $item ) ) {
				$item['type'] = 'post';
			}

			$norm = rx_import_normalize_item( $item );
			if ( is_wp_error( $norm ) ) {
				$problems[] = $norm->get_error_message();
				$norm       = null;
			} elseif ( $norm['type'] !== $type ) {
				$problems[] = "files in content/{$kind}/ must hold a {$type} ('type' => '{$type}')";
			}

			$fields = $item['fields'] ?? array();
			if ( null === $fields ) {
				$fields = array();
			}
			if ( ! is_array( $fields ) || ( $fields && array_is_list( $fields ) ) ) {
				$problems[] = "'fields' must be an array of field name => value";
				$fields     = array();
			}
			$group = rx_item_field_group( $item );
			if ( $fields && '' === $group ) {
				$problems[] = "'fields' is set, but the item has no field group (set 'group' or a 'templates/*.php' template)";
			}

			$entries[] = array(
				'file'     => $rel,
				'row'      => $kind,
				'item'     => $item,
				'norm'     => $norm,
				'group'    => $group,
				'fields'   => $fields,
				'problems' => $problems,
				'depth'    => 0,
			);
		}
	}

	// Duplicates: source (all items), page key, slug among siblings, post slug.
	$seen = array();
	foreach ( $entries as $i => $e ) {
		$n = $e['norm'];
		if ( null === $n ) {
			continue;
		}
		$seen['source "%s"'][ $n['source'] ][] = $i;
		if ( 'page' === $n['type'] ) {
			$seen['page key "%s"'][ $n['key'] ][] = $i;
			$seen['slug "%s" under the same parent'][ $n['parent'] . '/' . $n['slug'] ][] = $i;
		} else {
			$seen['post slug "%s"'][ $n['slug'] ][] = $i;
		}
	}
	foreach ( $seen as $format => $groups ) {
		foreach ( $groups as $value => $indexes ) {
			if ( count( $indexes ) < 2 ) {
				continue;
			}
			$shown = str_contains( (string) $value, '/' ) && str_contains( $format, 'parent' ) ? substr( (string) $value, strrpos( (string) $value, '/' ) + 1 ) : (string) $value;
			foreach ( $indexes as $i ) {
				$others                     = array_map( static fn( $j ) => $entries[ $j ]['file'], array_diff( $indexes, array( $i ) ) );
				$entries[ $i ]['problems'][] = sprintf( $format, $shown ) . ' is also used by ' . implode( ', ', $others );
			}
		}
	}

	// Parent chains: depth for the order, and loops.
	$by_key = array();
	foreach ( $entries as $i => $e ) {
		if ( $e['norm'] && 'page' === $e['norm']['type'] && ! isset( $by_key[ $e['norm']['key'] ] ) ) {
			$by_key[ $e['norm']['key'] ] = $i;
		}
	}
	foreach ( $entries as $i => $e ) {
		if ( ! $e['norm'] || 'page' !== $e['norm']['type'] ) {
			continue;
		}
		$depth  = 0;
		$chain  = array( $e['norm']['key'] => true );
		$parent = $e['norm']['parent'];
		while ( '' !== $parent && isset( $by_key[ $parent ] ) ) {
			if ( isset( $chain[ $parent ] ) ) {
				$entries[ $i ]['problems'][] = 'parent pages form a loop (' . implode( ' > ', array_keys( $chain ) ) . " > {$parent})";
				break;
			}
			$chain[ $parent ] = true;
			++$depth;
			$parent = $entries[ $by_key[ $parent ] ]['norm']['parent'];
		}
		$entries[ $i ]['depth'] = $depth;
	}

	// A page whose parent has problems is not imported either.
	do {
		$changed = false;
		foreach ( $entries as $i => $e ) {
			if ( $e['problems'] || ! $e['norm'] || '' === $e['norm']['parent'] ) {
				continue;
			}
			$p = $by_key[ $e['norm']['parent'] ] ?? null;
			if ( null !== $p && $entries[ $p ]['problems'] ) {
				$entries[ $i ]['problems'][] = "its parent page \"{$e['norm']['parent']}\" ({$entries[ $p ]['file']}) has problems";
				$changed                    = true;
			}
		}
	} while ( $changed );

	$plan = array(
		'pages' => array(),
		'posts' => array(),
		'keys'  => array(),
		'refs'  => array(),
	);
	foreach ( $entries as $e ) {
		$plan[ $e['row'] ][] = $e;
		if ( $e['problems'] || ! $e['norm'] ) {
			continue;
		}
		$n              = $e['norm'];
		$plan['refs'][] = array(
			'type'   => $n['type'],
			'source' => $n['source'],
			'key'    => $n['key'],
			'slug'   => $n['slug'],
		);
		if ( 'page' === $n['type'] ) {
			$plan['keys'][ $n['key'] ] = $n['status'];
		}
	}

	// Pages: parents first, then by source and slug. Posts: oldest first.
	$sort_key = static fn( array $e ): array => array(
		$e['depth'],
		$e['norm']['source'] ?? $e['file'],
		$e['norm']['slug'] ?? '',
		$e['file'],
	);
	usort( $plan['pages'], static fn( $a, $b ) => $sort_key( $a ) <=> $sort_key( $b ) );

	$post_key = static fn( array $e ): array => array(
		'' !== ( $e['norm']['date'] ?? '' ) ? $e['norm']['date'] : '9999-12-31 23:59:59',
		$e['norm']['source'] ?? $e['file'],
		$e['file'],
	);
	usort( $plan['posts'], static fn( $a, $b ) => $post_key( $a ) <=> $post_key( $b ) );

	return $plan;
}

/**
 * Site path an entry will have, for the log.
 *
 * @param array $entry Plan entry.
 * @param array $plan  Plan.
 */
function rx_import_entry_path( array $entry, array $plan ): string {
	$n = $entry['norm'];
	if ( ! $n ) {
		return '';
	}
	if ( 'post' === $n['type'] ) {
		return '/blog/' . $n['slug'] . '/';
	}
	if ( 'home' === $n['key'] ) {
		return '/ (front page)';
	}

	// Parents from the data files, else an already imported parent's path.
	$pages = array();
	foreach ( $plan['pages'] as $p ) {
		if ( $p['norm'] && ! isset( $pages[ $p['norm']['key'] ] ) ) {
			$pages[ $p['norm']['key'] ] = array( $p['norm']['slug'], $p['norm']['parent'] );
		}
	}
	$parts  = array( $n['slug'] );
	$parent = $n['parent'];
	for ( $i = 0; '' !== $parent && $i < 10; $i++ ) {
		if ( isset( $pages[ $parent ] ) ) {
			array_unshift( $parts, $pages[ $parent ][0] );
			$parent = $pages[ $parent ][1];
			continue;
		}
		$id = rx_import_find_by_meta( '_rx_key', $parent, array( 'page' ) );
		array_unshift( $parts, $id ? (string) get_page_uri( $id ) : $parent );
		break;
	}
	return '/' . implode( '/', $parts ) . '/';
}

/*
 * -------------------------------------------------------------------------
 * Field definitions and dry-run checks
 * -------------------------------------------------------------------------
 */

/**
 * Field definitions from the theme's acf-json/ files.
 *
 * @return array{top: array<string, array>, sub: array<string, array>, groups: array<string, array>}
 *         Top-level fields by key, sub-fields by key, and each group's field list.
 */
function rx_import_json_index(): array {
	static $index = null;
	if ( null !== $index ) {
		return $index;
	}

	$index = array(
		'top'    => array(),
		'sub'    => array(),
		'groups' => array(),
	);
	$files = glob( RX_IMPORT_ACF_JSON . '/group_rx_*.json' ) ?: array();
	sort( $files, SORT_STRING );
	foreach ( $files as $file ) {
		$json = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $json ) || ! isset( $json['key'], $json['fields'] ) || ! is_array( $json['fields'] ) ) {
			continue;
		}
		$index['groups'][ (string) $json['key'] ] = $json['fields'];
		foreach ( $json['fields'] as $field ) {
			if ( is_array( $field ) && isset( $field['key'] ) ) {
				$index['top'][ (string) $field['key'] ] = $field;
				rx_import_json_index_subs( $field, $index['sub'] );
			}
		}
	}
	return $index;
}

/**
 * Collect the sub-fields (and flexible layout sub-fields) of a field, recursively.
 *
 * @param array $field Field.
 * @param array $subs  Sub-fields by key (by reference).
 */
function rx_import_json_index_subs( array $field, array &$subs ): void {
	$children = (array) ( $field['sub_fields'] ?? array() );
	foreach ( (array) ( $field['layouts'] ?? array() ) as $layout ) {
		if ( is_array( $layout ) ) {
			array_push( $children, ...array_values( (array) ( $layout['sub_fields'] ?? array() ) ) );
		}
	}
	foreach ( $children as $sub ) {
		if ( is_array( $sub ) && isset( $sub['key'] ) ) {
			$subs[ (string) $sub['key'] ] = $sub;
			rx_import_json_index_subs( $sub, $subs );
		}
	}
}

/**
 * Whether SCF is loaded and initialised.
 */
function rx_import_scf_ready(): bool {
	return function_exists( 'acf_get_field' ) && function_exists( 'update_field' ) && did_action( 'acf/init' ) > 0;
}

/**
 * Definition of a top-level field by key: from SCF when it is ready, else
 * from the acf-json files.
 *
 * @param string $key Field key.
 * @return array{field: array|null, sub: bool} The field (null when unknown) and
 *                                             whether it is a sub-field.
 */
function rx_import_field_def( string $key ): array {
	if ( rx_import_scf_ready() ) {
		$field = acf_get_field( $key );
		if ( ! is_array( $field ) ) {
			return array(
				'field' => null,
				'sub'   => false,
			);
		}
		return array(
			'field' => $field,
			'sub'   => ! empty( $field['parent'] ) && is_array( acf_get_field( $field['parent'] ) ),
		);
	}

	$index = rx_import_json_index();
	if ( isset( $index['top'][ $key ] ) ) {
		return array(
			'field' => $index['top'][ $key ],
			'sub'   => false,
		);
	}
	return array(
		'field' => $index['sub'][ $key ] ?? null,
		'sub'   => isset( $index['sub'][ $key ] ),
	);
}

/**
 * Whether a field group holds a field that references posts (relationship,
 * post object, page link), at any depth. Such fields are written after the
 * posts exist.
 *
 * @param string $group Group slug, e.g. 'blog'.
 */
function rx_import_group_has_refs( string $group ): bool {
	static $cache = array();
	if ( ! isset( $cache[ $group ] ) ) {
		$cache[ $group ] = rx_import_fields_have_refs( rx_import_json_index()['groups'][ 'group_rx_' . $group ] ?? array() );
	}
	return $cache[ $group ];
}

/**
 * Whether a list of field definitions holds a post reference field.
 *
 * @param array $fields Field definitions.
 */
function rx_import_fields_have_refs( array $fields ): bool {
	foreach ( $fields as $field ) {
		if ( ! is_array( $field ) ) {
			continue;
		}
		if ( in_array( $field['type'] ?? '', array( 'relationship', 'post_object', 'page_link' ), true ) ) {
			return true;
		}
		if ( rx_import_fields_have_refs( (array) ( $field['sub_fields'] ?? array() ) ) ) {
			return true;
		}
		foreach ( (array) ( $field['layouts'] ?? array() ) as $layout ) {
			if ( is_array( $layout ) && rx_import_fields_have_refs( (array) ( $layout['sub_fields'] ?? array() ) ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Whether a post reference points at an item this run imports.
 *
 * Same lookups as rx_resolve_post_ref(): `_rx_source`, page key, slug.
 *
 * @param mixed    $ref   Reference.
 * @param string[] $types Allowed post types.
 * @param array    $plan  Plan.
 */
function rx_import_planned_ref( mixed $ref, array $types, array $plan ): bool {
	if ( ! is_string( $ref ) || '' === trim( $ref ) ) {
		return false;
	}
	foreach ( $plan['refs'] as $r ) {
		if ( in_array( $r['type'], $types, true ) && ( $ref === $r['source'] || $ref === $r['slug'] || ( 'page' === $r['type'] && $ref === $r['key'] ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Check field values the way rx_write_fields() would, without writing.
 *
 * Post references to items imported in the same run are accepted (they
 * resolve once those items exist). Only top-level reference fields are
 * looked up this way; the theme has no nested ones.
 *
 * @param string $group  Group slug.
 * @param array  $fields Field name => value.
 * @param array  $plan   Plan.
 * @return string[] Problems, each with its field path.
 */
function rx_import_check_fields( string $group, array $fields, array $plan ): array {
	if ( ! preg_match( '/^[a-z0-9]+(?:_[a-z0-9]+)*$/', $group ) ) {
		return array( "invalid field group slug \"{$group}\"" );
	}

	$problems = array();
	foreach ( $fields as $name => $value ) {
		if ( ! is_string( $name ) || ! preg_match( '/^[a-z0-9]+(?:_[a-z0-9]+)*$/', $name ) ) {
			$problems[] = var_export( $name, true ) . ': invalid field name';
			continue;
		}

		$key   = "field_rx_{$group}_{$name}";
		$def   = rx_import_field_def( $key );
		$field = $def['field'];
		if ( null === $field ) {
			$problems[] = "{$name}: unknown field ({$key} is not defined)";
			continue;
		}
		if ( (string) ( $field['name'] ?? '' ) !== $name ) {
			$problems[] = "{$name}: {$key} is named \"" . ( $field['name'] ?? '' ) . "\", not \"{$name}\"";
			continue;
		}
		if ( $def['sub'] ) {
			$problems[] = "{$name}: {$key} is a sub-field; write it through its parent field";
			continue;
		}

		$type = (string) ( $field['type'] ?? '' );
		if ( in_array( $type, array( 'relationship', 'post_object' ), true ) && null !== $value && '' !== $value && array() !== $value ) {
			$multiple = 'relationship' === $type || ! empty( $field['multiple'] );
			if ( $multiple || ! is_array( $value ) ) {
				$types = array_values( array_filter( (array) ( $field['post_type'] ?? array() ), 'is_string' ) ) ?: array( 'post', 'page' );
				$keep  = array();
				foreach ( (array) $value as $ref ) {
					if ( ! rx_resolve_post_ref( $ref, $types ) && rx_import_planned_ref( $ref, $types, $plan ) ) {
						continue; // Imported in this run.
					}
					$keep[] = $ref;
				}
				$value = is_array( $value ) ? $keep : ( $keep[0] ?? null );
			}
			$max = isset( $field['max'] ) && is_numeric( $field['max'] ) ? (int) $field['max'] : 0;
			if ( $max > 0 && is_array( $fields[ $name ] ) && count( $fields[ $name ] ) > $max ) {
				$problems[] = "{$name}: at most {$max} post(s), got " . count( $fields[ $name ] );
			}
		}

		$issues = array();
		rx_fields_prepare( $field, $value, $name, $issues );
		array_push( $problems, ...$issues );
	}
	return $problems;
}

/**
 * ID of a post that already holds a slug where an item wants it, using the
 * same scope as wp_unique_post_slug(): pages under the same parent, posts
 * among posts.
 *
 * @param string $type      'page' or 'post'.
 * @param string $slug      Slug.
 * @param int    $parent_id Parent page ID (pages).
 * @param int    $self      ID of the item itself (0 when new).
 * @return int Post ID, or 0.
 */
function rx_import_slug_holder( string $type, string $slug, int $parent_id, int $self ): int {
	global $wpdb;
	if ( 'page' === $type ) {
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page' AND post_parent = %d AND ID != %d ORDER BY ID ASC LIMIT 1",
				$slug,
				$parent_id,
				$self
			)
		);
	}
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s AND ID != %d ORDER BY ID ASC LIMIT 1",
			$slug,
			$type,
			$self
		)
	);
}

/**
 * Dry-run checks of one item against the database, mirroring what
 * rx_upsert_post() checks before and after writing.
 *
 * @param array $entry Plan entry (valid).
 * @param array $plan  Plan.
 * @return array{id: int, problems: string[], field_problems: string[]}
 *         Existing post ID (0 = would be created) and problems.
 */
function rx_import_check_item( array $entry, array $plan ): array {
	$n        = $entry['norm'];
	$problems = array();
	$type     = $n['type'];
	$other    = 'page' === $type ? 'post' : 'page';

	$existing  = rx_import_find_by_meta( '_rx_source', $n['source'], array( $type ), array( 'auto-draft' ) );
	$elsewhere = rx_import_find_by_meta( '_rx_source', $n['source'], array( $other ), array( 'auto-draft' ) );
	if ( ! $existing && $elsewhere ) {
		$problems[] = "already imported as {$other} #{$elsewhere}; change it there or delete it first";
	}

	$parent_id = 0;
	if ( 'page' === $type ) {
		$holder = rx_import_find_by_meta( '_rx_key', $n['key'], array( 'page' ) );
		if ( $holder && $holder !== $existing ) {
			$problems[] = "page key \"{$n['key']}\" is already used by page #{$holder}";
		}
		if ( '' !== $n['parent'] ) {
			$parent_id = rx_import_find_by_meta( '_rx_key', $n['parent'], array( 'page' ) );
			if ( ! $parent_id && ! isset( $plan['keys'][ $n['parent'] ] ) ) {
				$problems[] = "parent page \"{$n['parent']}\" is neither imported yet nor in the data files";
			}
		}
	}

	if ( '' !== $n['template'] && 'default' !== $n['template'] ) {
		$templates = wp_get_theme()->get_page_templates( null, $type );
		if ( ! isset( $templates[ $n['template'] ] ) ) {
			$problems[] = "template \"{$n['template']}\" is not a {$type} template of the active theme (" . get_stylesheet() . ')';
		}
	}

	// The slug check needs the parent's ID; a parent created in this run has none yet.
	if ( '' === $n['parent'] || $parent_id ) {
		$holder = rx_import_slug_holder( $type, $n['slug'], $parent_id, $existing );
		if ( $holder ) {
			$problems[] = "slug \"{$n['slug']}\" is already taken by {$type} #{$holder} (" . get_post_field( 'post_title', $holder ) . ')';
		}
	}

	if ( 'post' === $type && defined( 'RX_TOPICS' ) && is_array( RX_TOPICS ) && array_key_exists( $n['slug'], RX_TOPICS ) ) {
		$problems[] = "slug \"{$n['slug']}\" equals a blog topic; WordPress would change it to \"{$n['slug']}-guide\"";
	}

	if ( 'publish' === $n['status'] && '' !== $n['date'] && $n['date'] > current_time( 'mysql' ) ) {
		$problems[] = "date {$n['date']} is in the future, so WordPress would schedule the post instead of publishing it";
	}

	$field_problems = $entry['fields'] ? rx_import_check_fields( $entry['group'], $entry['fields'], $plan ) : array();

	return array(
		'id'             => $existing,
		'problems'       => $problems,
		'field_problems' => $field_problems,
	);
}

/*
 * -------------------------------------------------------------------------
 * Steps
 * -------------------------------------------------------------------------
 */

/**
 * Step 1: report the install and, for a real run, check that the theme and
 * SCF are ready (rx_assert_local() has already run).
 *
 * @param Rx_Import_Run $run Run.
 * @return bool False when a real run must stop before writing.
 */
function rx_import_preflight( Rx_Import_Run $run ): bool {
	global $wpdb;

	rx_import_line( 'ok', 'local install ' . ABSPATH . ' (DB ' . DB_HOST . '/' . DB_NAME . ', prefix ' . $wpdb->prefix . ')' );
	rx_import_line( 'ok', 'site "' . get_bloginfo( 'name' ) . '" at ' . home_url( '/' ) );

	$theme_ok = 'pharmacy' === get_template();
	$scf_ok   = rx_import_scf_ready();
	$yoast_ok = defined( 'WPSEO_VERSION' );

	rx_import_line( $theme_ok ? 'ok' : 'note', 'theme ' . get_stylesheet() . ' (parent ' . get_template() . ')' );
	rx_import_line( $scf_ok ? 'ok' : 'note', 'Secure Custom Fields ' . ( $scf_ok ? 'active' : 'not active' ) );
	rx_import_line( $yoast_ok ? 'ok' : 'note', 'Yoast SEO ' . ( $yoast_ok ? 'active' : 'not active (the Yoast meta is still saved)' ) );

	if ( $run->dry ) {
		if ( ! $scf_ok ) {
			rx_import_detail( 'dry run: field names and values are checked against ' . realpath( RX_IMPORT_ACF_JSON ) );
		}
		return true;
	}

	$errors = array();
	if ( ! $theme_ok ) {
		$errors[] = 'the active theme is not pharmacy-child on the pharmacy parent (get_template() is "' . get_template() . '")';
	}
	if ( ! $scf_ok ) {
		$errors[] = 'Secure Custom Fields is not active';
	}
	if ( $errors ) {
		foreach ( $errors as $error ) {
			fwrite( STDERR, '[rx import] ' . $error . PHP_EOL );
		}
		fwrite( STDERR, '[rx import] Run D:\xampp\php\php.exe wordpress\import\setup.php first. Nothing was written.' . PHP_EOL );
		return false;
	}
	return true;
}

/**
 * Write SCF values and record the result.
 *
 * @param Rx_Import_Run $run    Run.
 * @param string        $row    Row key ('fields' or 'settings').
 * @param int|string    $target Post ID or 'option'.
 * @param string        $group  Group slug.
 * @param array         $fields Field name => value.
 * @param string        $label  Label for messages.
 */
function rx_import_write_fields( Rx_Import_Run $run, string $row, int|string $target, string $group, array $fields, string $label ): void {
	$result = rx_write_fields( $target, $group, $fields );
	if ( is_wp_error( $result ) ) {
		$data     = $result->get_error_data();
		$problems = is_array( $data ) && ! empty( $data['problems'] ) ? $data['problems'] : array( $result->get_error_message() );
		$written  = is_array( $data ) && isset( $data['written'] ) ? count( $data['written'] ) : 0;
		$run->fail( $row, "{$label}: fields ({$written} of " . count( $fields ) . ' written)', $problems );
		return;
	}
	$run->add( $row, 'updated' );
	rx_import_detail( 'fields: ' . count( $fields ) . ' written (group "' . $group . '")' );
}

/**
 * Step 2: Site_Settings.
 *
 * @param Rx_Import_Run $run      Run.
 * @param array|null    $settings Field name => value, or null when unavailable.
 * @param array         $plan     Plan.
 */
function rx_import_settings_step( Rx_Import_Run $run, ?array $settings, array $plan ): void {
	if ( null === $settings ) {
		rx_import_line( 'skipped', 'content/settings.php is missing or unusable (see Data files)' );
		return;
	}

	if ( $run->dry ) {
		$problems = rx_import_check_fields( 'settings', $settings, $plan );
		if ( $problems ) {
			$run->fail( 'settings', 'content/settings.php', $problems );
			return;
		}
		$run->add( 'settings', 'updated' );
		rx_import_line( $run->word( 'written' ), 'Site_Settings: ' . count( $settings ) . ' field(s)' );
		return;
	}

	rx_import_line( 'writing', 'Site_Settings: ' . count( $settings ) . ' field(s)' );
	rx_import_write_fields( $run, 'settings', 'option', 'settings', $settings, 'content/settings.php' );
}

/**
 * Steps 3 and 4: pages or posts, each followed by its fields.
 *
 * @param Rx_Import_Run $run      Run.
 * @param array[]       $entries  Plan entries in import order.
 * @param array         $plan     Plan.
 * @param array         $deferred Entries whose fields wait for the posts (by reference).
 */
function rx_import_items_step( Rx_Import_Run $run, array $entries, array $plan, array &$deferred ): void {
	if ( ! $entries ) {
		rx_import_line( 'skipped', 'no data files to import' );
		return;
	}

	// Dry run: page keys that fail above, so their children would fail too.
	$failed_keys = array();

	foreach ( $entries as $e ) {
		$row  = $e['row'];
		$path = rx_import_entry_path( $e, $plan );
		$text = $e['file'] . ( '' !== $path ? ' -> ' . $path : '' );

		if ( $e['problems'] ) {
			$run->fail( $row, $text, $e['problems'] );
			continue;
		}

		$later = $e['fields'] && rx_import_group_has_refs( $e['group'] );

		if ( $run->dry ) {
			$check  = rx_import_check_item( $e, $plan );
			$parent = $e['norm']['parent'];
			if ( '' !== $parent && isset( $failed_keys[ $parent ] ) && ! rx_import_find_by_meta( '_rx_key', $parent, array( 'page' ) ) ) {
				$check['problems'][] = "its parent page \"{$parent}\" fails above, so this page cannot be created";
			}
			if ( $check['problems'] ) {
				if ( 'page' === $e['norm']['type'] ) {
					$failed_keys[ $e['norm']['key'] ] = true;
				}
				$run->fail( $row, $text, $check['problems'] );
			} else {
				$action = $check['id'] ? 'updated' : 'created';
				$run->add( $row, $action );
				rx_import_line( $run->word( $action ), $text . ( $check['id'] ? " (#{$check['id']})" : '' ) );
			}
			if ( $e['fields'] ) {
				if ( $check['field_problems'] ) {
					$run->fail( 'fields', "{$e['file']}: fields (group \"{$e['group']}\")", $check['field_problems'] );
				} else {
					$run->add( 'fields', 'updated' );
					rx_import_detail( 'fields: ' . count( $e['fields'] ) . ' checked (group "' . $e['group'] . '")' . ( $later ? ', written after the posts' : '' ) );
				}
			}
			continue;
		}

		$report = null;
		$result = rx_upsert_post( $e['item'], $report );
		$id     = rx_import_result_id( $result );
		if ( is_wp_error( $result ) ) {
			$run->fail( $row, $text . ( $id ? " (#{$id})" : '' ), $result->get_error_message() );
		} else {
			$run->add( $row, $report['action'] );
			rx_import_line( $report['action'], "{$text} (#{$id})" );
		}

		if ( $id && $e['fields'] ) {
			if ( $later ) {
				$deferred[] = array(
					'id'    => $id,
					'entry' => $e,
				);
				rx_import_detail( 'fields: written after the posts (they reference posts)' );
			} else {
				rx_import_write_fields( $run, 'fields', $id, $e['group'], $e['fields'], $e['file'] );
			}
		}
	}
}

/**
 * Step 4, second pass: fields that reference posts.
 *
 * @param Rx_Import_Run $run      Run.
 * @param array[]       $deferred [ 'id' => int, 'entry' => array ] items.
 */
function rx_import_deferred_fields_step( Rx_Import_Run $run, array $deferred ): void {
	if ( ! $deferred ) {
		return;
	}
	echo '  Fields that reference posts:', PHP_EOL;
	foreach ( $deferred as $d ) {
		rx_import_line( 'writing', "{$d['entry']['file']} (#{$d['id']})" );
		rx_import_write_fields( $run, 'fields', $d['id'], $d['entry']['group'], $d['entry']['fields'], $d['entry']['file'] );
	}
}

/**
 * ID from an upsert result. Errors that saved the object anyway
 * (rx_item_mismatch, rx_menu_items) carry it in data['id'].
 *
 * @param int|WP_Error $result Upsert result.
 */
function rx_import_result_id( int|WP_Error $result ): int {
	if ( ! is_wp_error( $result ) ) {
		return $result;
	}
	$data = $result->get_error_data();
	return is_array( $data ) && isset( $data['id'] ) ? (int) $data['id'] : 0;
}

/**
 * Step 5: front page, posts page and privacy policy page.
 *
 * @param Rx_Import_Run $run  Run.
 * @param array         $plan Plan.
 * @return int The privacy policy page ID after this step (-1 in a dry run when
 *             it will be a page this run creates).
 */
function rx_import_reading_step( Rx_Import_Run $run, array $plan ): int {
	$ids = array();
	foreach ( array( 'home', 'blog', 'privacy' ) as $key ) {
		$id        = rx_import_find_by_meta( '_rx_key', $key, array( 'page' ) );
		$published = $id && 'publish' === get_post_status( $id );
		if ( $run->dry && isset( $plan['keys'][ $key ] ) ) {
			// The page is imported first; its data file decides the status.
			$ids[ $key ] = 'publish' === $plan['keys'][ $key ] ? ( $id ?: -1 ) : 0;
		} else {
			$ids[ $key ] = $published ? $id : 0;
		}
	}

	$options = array(
		array( 'show_on_front', 'home', 'page' ),
		array( 'page_on_front', 'home', null ),
		array( 'page_for_posts', 'blog', null ),
		array( 'wp_page_for_privacy_policy', 'privacy', null ),
	);
	foreach ( $options as [ $option, $key, $fixed ] ) {
		$id = $ids[ $key ];
		if ( ! $id ) {
			$run->add( 'reading', 'skipped' );
			rx_import_line( 'skipped', "{$option}: no published page with key \"{$key}\"" );
			continue;
		}

		$value   = $fixed ?? $id;
		$current = get_option( $option );
		$shown   = -1 === $value ? "the new \"{$key}\" page" : (string) $value;
		if ( -1 !== $value && (string) $current === (string) $value ) {
			$run->add( 'reading', 'unchanged' );
			rx_import_line( 'unchanged', "{$option} = {$shown}" );
			continue;
		}

		if ( $run->dry ) {
			$run->add( 'reading', 'updated' );
			rx_import_line( $run->word( 'set' ), "{$option} = {$shown} (now " . var_export( $current, true ) . ')' );
			continue;
		}

		update_option( $option, $value );
		if ( (string) get_option( $option ) === (string) $value ) {
			$run->add( 'reading', 'updated' );
			rx_import_line( 'set', "{$option} = {$shown} (was " . var_export( $current, true ) . ')' );
		} else {
			$run->fail( 'reading', $option, 'reads back as ' . var_export( get_option( $option ), true ) . ', not ' . var_export( $value, true ) );
		}
	}

	return $ids['privacy'] ? $ids['privacy'] : (int) get_option( 'wp_page_for_privacy_policy' );
}

/**
 * Check a menu's page links and turn them into placeholder links, so a dry
 * run can validate the rest of the menu with rx_import_normalize_menu()
 * before the pages exist. Item ids stay the same (a page link's default id
 * is its page key).
 *
 * A page link is fine when this run imports a published page with that key,
 * or when such a page is already imported and published.
 *
 * @param array                 $items      Menu items.
 * @param array<string, string> $keys       Planned page key => status.
 * @param string[]              $problems   Problems (by reference).
 * @param array<string, true>   $unresolved Page keys found nowhere (by reference).
 * @return array Items to validate.
 */
function rx_import_menu_for_check( array $items, array $keys, array &$problems, array &$unresolved ): array {
	foreach ( $items as $i => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$page = $item['page'] ?? '';
		$url  = $item['url'] ?? '';
		if ( is_string( $page ) && '' !== $page && ( ! is_string( $url ) || '' === trim( $url ) ) ) {
			if ( isset( $keys[ $page ] ) ) {
				if ( 'publish' !== $keys[ $page ] ) {
					$problems[] = "link to page \"{$page}\": its data file sets status \"{$keys[ $page ]}\", but menu links need a published page";
				}
			} else {
				$id = rx_import_find_by_meta( '_rx_key', $page, array( 'page' ) );
				if ( ! $id ) {
					$unresolved[ $page ] = true;
				} elseif ( 'publish' !== get_post_status( $id ) ) {
					$problems[] = "link to page \"{$page}\": page #{$id} is not published";
				}
			}
			$item['id'] = $item['id'] ?? $page;
			unset( $item['page'] );
			$item['url'] = '/';
		}
		if ( isset( $item['children'] ) && is_array( $item['children'] ) ) {
			$item['children'] = rx_import_menu_for_check( $item['children'], $keys, $problems, $unresolved );
		}
		$items[ $i ] = $item;
	}
	return $items;
}

/**
 * Remove a leading "{label}: " from an error message that repeats its label.
 *
 * @param string $message Message.
 * @param string $label   Label.
 */
function rx_import_strip_label( string $message, string $label ): string {
	return str_starts_with( $message, $label . ': ' ) ? substr( $message, strlen( $label ) + 2 ) : $message;
}

/**
 * Dry run of one menu: validate it and count what would change.
 *
 * @param Rx_Import_Run $run   Run.
 * @param array         $menu  Menu data.
 * @param array         $plan  Plan.
 * @param string        $label Label.
 * @return array<string, int>|null Location => menu ID (0 = new menu), or null on problems.
 */
function rx_import_dry_menu( Rx_Import_Run $run, array $menu, array $plan, string $label ): ?array {
	$problems   = array();
	$unresolved = array();
	$check      = $menu;
	if ( isset( $check['items'] ) && is_array( $check['items'] ) ) {
		$check['items'] = rx_import_menu_for_check( $check['items'], $plan['keys'], $problems, $unresolved );
	}
	if ( $unresolved ) {
		$problems[] = 'links to page key(s) that are neither imported nor in content/pages/: ' . implode( ', ', array_keys( $unresolved ) );
	}

	$data = rx_import_normalize_menu( $check );
	if ( is_wp_error( $data ) ) {
		$problems[] = rx_import_strip_label( $data->get_error_message(), $label );
	} else {
		foreach ( $data['items'] as $it ) {
			if ( $it['fields'] ) {
				foreach ( rx_import_check_fields( 'menu_item', $it['fields'], $plan ) as $problem ) {
					$problems[] = "{$it['label']}: {$problem}";
				}
			}
		}
	}
	if ( $problems ) {
		$run->fail( 'menus', $label, $problems );
		if ( ! is_wp_error( $data ) && count( $problems ) === ( $unresolved ? 1 : 0 ) ) {
			rx_import_detail( 'apart from that the menu is valid: ' . count( $data['items'] ) . ' item(s), ids, links and fields checked' );
		}
		return null;
	}

	$term    = get_term_by( 'name', $data['name'], 'nav_menu' );
	$menu_id = $term instanceof WP_Term ? (int) $term->term_id : 0;

	$existing = $menu_id ? rx_import_menu_items( $menu_id ) : array();
	$keep     = array();
	foreach ( $existing as $item_id => $source ) {
		if ( '' !== $source && ! isset( $keep[ $source ] ) ) {
			$keep[ $source ] = $item_id;
		}
	}
	$create = 0;
	$update = 0;
	foreach ( $data['items'] as $it ) {
		isset( $keep[ $it['source'] ] ) ? ++$update : ++$create;
	}
	$wanted = array_flip( array_column( $data['items'], 'source' ) );
	$delete = 0;
	foreach ( $existing as $item_id => $source ) {
		if ( ! ( '' !== $source && isset( $wanted[ $source ] ) && $keep[ $source ] === $item_id ) ) {
			++$delete;
		}
	}

	$action = $menu_id ? 'updated' : 'created';
	$run->add( 'menus', $action );
	$run->add( 'menu_items', 'created', $create );
	$run->add( 'menu_items', 'updated', $update );
	$run->add( 'menu_items', 'deleted', $delete );
	rx_import_line( $run->word( $action ), $label . ( $menu_id ? " (#{$menu_id})" : '' ) );
	rx_import_detail(
		sprintf(
			'items: %d (%d to create, %d to update, %d to delete), %d with icon/description fields',
			count( $data['items'] ),
			$create,
			$update,
			$delete,
			count( array_filter( $data['items'], static fn( $it ) => (bool) $it['fields'] ) )
		)
	);

	return array_fill_keys( $data['locations'], $menu_id );
}

/**
 * Step 6: menus, their items and fields, then the menu locations.
 *
 * @param Rx_Import_Run $run   Run.
 * @param array|null    $menus Menus from content/menus.php, or null when unavailable.
 * @param array         $plan  Plan.
 */
function rx_import_menus_step( Rx_Import_Run $run, ?array $menus, array $plan ): void {
	if ( null === $menus ) {
		rx_import_line( 'skipped', 'content/menus.php is missing or unusable (see Data files)' );
		return;
	}

	// Menu names and locations must be unique across the data.
	$problems   = array();
	$names      = array();
	$claimed    = array();
	$registered = get_registered_nav_menus();
	foreach ( $menus as $i => $menu ) {
		$name = is_string( $menu['name'] ?? null ) ? trim( $menu['name'] ) : '';
		if ( '' !== $name ) {
			$names[ strtolower( $name ) ][] = $i;
		}
		$locations = $menu['location'] ?? array();
		foreach ( is_string( $locations ) ? array( $locations ) : ( is_array( $locations ) ? $locations : array() ) as $location ) {
			if ( ! is_string( $location ) ) {
				continue;
			}
			$claimed[ $location ][] = $i;
			if ( ! isset( $registered[ $location ] ) ) {
				$problems[ $i ][] = "location \"{$location}\" is not registered by the active theme (" . get_stylesheet() . ')';
			}
		}
	}
	foreach ( $names as $indexes ) {
		if ( count( $indexes ) > 1 ) {
			foreach ( $indexes as $i ) {
				$problems[ $i ][] = 'another menu in content/menus.php has the same name';
			}
		}
	}
	foreach ( $claimed as $location => $indexes ) {
		if ( count( $indexes ) > 1 ) {
			foreach ( $indexes as $i ) {
				$problems[ $i ][] = "location \"{$location}\" is claimed by more than one menu";
			}
		}
	}

	$assign = array();
	foreach ( $menus as $i => $menu ) {
		$label = 'Menu "' . ( is_string( $menu['name'] ?? null ) && '' !== trim( $menu['name'] ) ? trim( $menu['name'] ) : '#' . ( $i + 1 ) ) . '"';
		if ( ! empty( $problems[ $i ] ) ) {
			$run->fail( 'menus', $label, $problems[ $i ] );
			continue;
		}

		if ( $run->dry ) {
			$locations = rx_import_dry_menu( $run, $menu, $plan, $label );
			foreach ( $locations ?? array() as $location => $menu_id ) {
				$assign[ $location ] = $menu_id;
			}
			continue;
		}

		$report = null;
		$result = rx_upsert_menu( $menu, $report );
		$id     = rx_import_result_id( $result );
		if ( is_wp_error( $result ) ) {
			$run->fail( 'menus', $label . ( $id ? " (#{$id})" : '' ), rx_import_strip_label( $result->get_error_message(), $label ) );
		} else {
			$run->add( 'menus', $report['action'] );
			rx_import_line( $report['action'], "{$label} (#{$id})" );
		}
		$run->add( 'menu_items', 'created', (int) $report['items_created'] );
		$run->add( 'menu_items', 'updated', (int) $report['items_updated'] );
		$run->add( 'menu_items', 'deleted', (int) $report['items_deleted'] );
		if ( $report['action'] ) {
			rx_import_detail( sprintf( 'items: %d created, %d updated, %d deleted', $report['items_created'], $report['items_updated'], $report['items_deleted'] ) );
		}
		if ( $id ) {
			foreach ( $report['locations'] as $location ) {
				$assign[ $location ] = $id;
			}
		}
	}

	rx_import_locations( $run, $assign );
}

/**
 * Step 6, end: assign the menu locations.
 *
 * @param Rx_Import_Run      $run    Run.
 * @param array<string, int> $assign Location => menu ID (0 in a dry run for a new menu).
 */
function rx_import_locations( Rx_Import_Run $run, array $assign ): void {
	if ( ! $assign ) {
		rx_import_line( 'skipped', 'menu locations: none to assign' );
		return;
	}

	$current = get_theme_mod( 'nav_menu_locations' );
	$current = is_array( $current ) ? $current : array();
	$changes = array();
	$same    = array();
	foreach ( $assign as $location => $menu_id ) {
		if ( $menu_id && (int) ( $current[ $location ] ?? 0 ) === $menu_id ) {
			$same[] = $location;
		} else {
			$changes[] = $location . ' -> ' . ( $menu_id ? "#{$menu_id}" : 'new menu' );
		}
	}

	if ( ! $run->dry ) {
		$result = rx_assign_menu_locations( $assign );
		if ( is_wp_error( $result ) ) {
			$run->fail( 'locations', 'Menu locations', $result->get_error_message() );
			return;
		}
	}

	$run->add( 'locations', 'updated', count( $changes ) );
	$run->add( 'locations', 'unchanged', count( $same ) );
	if ( $changes ) {
		rx_import_line( $run->word( 'set' ), 'menu locations: ' . implode( ', ', $changes ) );
	}
	if ( $same ) {
		rx_import_line( 'unchanged', 'menu locations: ' . implode( ', ', $same ) );
	}
}

/**
 * Step 7: the logo attachment Yoast needs to output Organization (design §12).
 *
 * 1. Attachment, found by `_rx_source` = RX_IMPORT_LOGO_SOURCE:
 *    - None yet: the parent theme's assets/img/healthray-logo.svg is copied into
 *      the uploads folder of wp_upload_dir(). The name stays healthray-logo.svg
 *      unless that file exists; wp_unique_filename() then adds a number, so
 *      no other file is overwritten. wp_insert_attachment() registers it as
 *      image/svg+xml, and its metadata gets width and height from the SVG
 *      viewBox. No image editor runs and wp_generate_attachment_metadata()
 *      is not called (it cannot read SVG).
 *    - Found: it is reused, so the ID stays the same on every run. A file
 *      that went missing is copied back from the theme, and metadata that
 *      does not match the file is corrected.
 * 2. Yoast: company_logo (the attachment URL) and company_logo_id are set
 *    with WPSEO_Options::set() when they differ, then read back. Yoast's
 *    cached logo data (company_logo_meta) is reset at the same time, as
 *    Yoast's own site representation settings do, so Yoast rebuilds it from
 *    the new attachment. When Yoast is not loaded this part fails and the
 *    attachment is kept.
 *
 * A dry run reports what would happen and writes nothing: no file is copied
 * and no folder is created.
 *
 * @param bool $dry Dry run: check only, write nothing.
 * @return array<int, array{status: string, message: string, id?: int, word?: string, details?: string[]}>
 *         One result per part (attachment, then Yoast). status is one of
 *         'created', 'updated', 'unchanged', 'skipped' or 'failed'; word is
 *         the log word when it differs from status; details are printed
 *         under the line (for a failure: the problems).
 */
function rx_import_logo( bool $dry ): array {
	$attachment = rx_import_logo_attachment( $dry );
	return array(
		array_diff_key( $attachment, array( 'url' => true ) ),
		rx_import_logo_yoast( $dry, $attachment ),
	);
}

/**
 * Width and height of an SVG: from the root element's viewBox, else from
 * its width and height attributes (unitless or px).
 *
 * @param string $svg SVG markup.
 * @return int[]|null [ width, height ] rounded to whole pixels, or null when unknown.
 */
function rx_import_svg_size( string $svg ): ?array {
	if ( ! preg_match( '/<svg\b[^>]*>/i', $svg, $tag ) ) {
		return null;
	}
	$tag = $tag[0];
	$num = '[-+]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][-+]?\d+)?';
	$sep = '(?:\s*,\s*|\s+)';

	if ( preg_match( '/\sviewBox\s*=\s*(["\'])\s*(' . $num . ')' . $sep . '(' . $num . ')' . $sep . '(' . $num . ')' . $sep . '(' . $num . ')\s*\1/i', $tag, $m ) ) {
		$width  = (int) round( (float) $m[4] );
		$height = (int) round( (float) $m[5] );
		if ( $width > 0 && $height > 0 ) {
			return array( $width, $height );
		}
	}

	$size = array();
	foreach ( array( 'width', 'height' ) as $attr ) {
		if ( ! preg_match( '/\s' . $attr . '\s*=\s*(["\'])\s*(' . $num . ')\s*(?:px)?\s*\1/i', $tag, $m ) ) {
			return null;
		}
		$size[] = (int) round( (float) $m[2] );
	}
	return $size[0] > 0 && $size[1] > 0 ? $size : null;
}

/**
 * The logo file in the parent theme: the active pharmacy theme's copy, else
 * (a dry run with another theme active) the repository's.
 */
function rx_import_logo_source_file(): string {
	$theme = 'pharmacy' === get_template() ? get_template_directory() : dirname( __DIR__ ) . '/themes/pharmacy';
	return wp_normalize_path( $theme . '/' . RX_IMPORT_LOGO_SOURCE );
}

/**
 * Path of a file relative to the uploads folder, e.g. '2026/10/healthray-logo.svg'.
 *
 * Compares normalised paths, so mixed slashes (and letter case on Windows)
 * do not matter. WordPress' _wp_relative_upload_path() compares the raw
 * strings instead.
 *
 * @param string $path    File path.
 * @param string $basedir Uploads base folder.
 * @return string Relative path, or '' when the file is not inside the
 *                uploads folder (or the path has a ".." segment).
 */
function rx_import_upload_rel( string $path, string $basedir ): string {
	$path = wp_normalize_path( $path );
	$base = untrailingslashit( wp_normalize_path( $basedir ) );
	if ( '' === $base || preg_match( '#(?:^|/)\.\.(?:/|$)#', $path ) || ! str_starts_with( rx_guard_norm_path( $path ), rx_guard_norm_path( $base ) . '/' ) ) {
		return '';
	}
	return substr( $path, strlen( $base ) + 1 );
}

/**
 * Copy the theme logo to a path in the uploads folder. Never overwrites a
 * file: it fails when something is already there.
 *
 * @param string $source Theme file.
 * @param string $dest   Target path.
 * @return string '' on success, else the problem.
 */
function rx_import_logo_copy( string $source, string $dest ): string {
	if ( file_exists( $dest ) ) {
		return 'not copied: ' . $dest . ' already exists';
	}
	if ( ! wp_mkdir_p( dirname( $dest ) ) ) {
		return 'cannot create the folder ' . dirname( $dest );
	}
	if ( ! copy( $source, $dest ) ) {
		$error = error_get_last();
		return 'cannot copy ' . $source . ' to ' . $dest . ( $error ? ': ' . $error['message'] : '' );
	}
	if ( ! hash_equals( (string) md5_file( $source ), (string) md5_file( $dest ) ) ) {
		return 'the copy at ' . $dest . ' differs from ' . $source;
	}
	return '';
}

/**
 * Attachment metadata for the logo file: what WordPress stores for an image
 * without sub-sizes.
 *
 * @param string $rel      Path relative to the uploads folder.
 * @param int[]  $size     [ width, height ].
 * @param int    $filesize File size in bytes.
 */
function rx_import_logo_metadata( string $rel, array $size, int $filesize ): array {
	return array(
		'width'    => $size[0],
		'height'   => $size[1],
		'file'     => $rel,
		'filesize' => $filesize,
		'sizes'    => array(),
	);
}

/**
 * Problems with a saved logo attachment, read back from the database.
 *
 * @param int    $id      Attachment ID.
 * @param string $rel     Expected file, relative to the uploads folder.
 * @param int[]  $size    Expected [ width, height ].
 * @param string $basedir Uploads base folder.
 * @return string[] Problems (empty when the attachment is as expected).
 */
function rx_import_logo_verify( int $id, string $rel, array $size, string $basedir ): array {
	clean_post_cache( $id );
	$problems = array();
	if ( 'attachment' !== get_post_type( $id ) || RX_IMPORT_LOGO_MIME !== get_post_mime_type( $id ) ) {
		$problems[] = "#{$id} is not an " . RX_IMPORT_LOGO_MIME . ' attachment';
	}
	if ( RX_IMPORT_LOGO_SOURCE !== get_post_meta( $id, '_rx_source', true ) ) {
		$problems[] = '_rx_source reads back as ' . var_export( get_post_meta( $id, '_rx_source', true ), true );
	}
	$attached = (string) get_attached_file( $id, true );
	if ( rx_import_upload_rel( $attached, $basedir ) !== $rel ) {
		$problems[] = '_wp_attached_file reads back as ' . var_export( get_post_meta( $id, '_wp_attached_file', true ), true ) . ", not {$rel}";
	} elseif ( ! is_file( $attached ) ) {
		$problems[] = 'the file ' . wp_normalize_path( $attached ) . ' does not exist';
	}
	$meta = wp_get_attachment_metadata( $id, true );
	if ( ! is_array( $meta ) || (int) ( $meta['width'] ?? 0 ) !== $size[0] || (int) ( $meta['height'] ?? 0 ) !== $size[1] || ( $meta['file'] ?? '' ) !== $rel ) {
		$problems[] = 'metadata reads back as ' . wp_json_encode( $meta ) . ", not {$size[0]}x{$size[1]} for {$rel}";
	}
	return $problems;
}

/**
 * Step 7, part 1: find, create or repair the logo attachment.
 *
 * @param bool $dry Dry run.
 * @return array Result (see rx_import_logo()) plus 'url': the attachment URL
 *               for the Yoast part (in a dry run, the URL it would get).
 */
function rx_import_logo_attachment( bool $dry ): array {
	$label  = 'logo attachment ' . RX_IMPORT_LOGO_SOURCE;
	$source = rx_import_logo_source_file();
	$fail   = static fn( array $problems, int $id = 0 ): array => array(
		'status'  => 'failed',
		'message' => $label . ( $id ? " (#{$id})" : '' ),
		'details' => $problems,
		'url'     => '',
	);

	$svg = is_file( $source ) ? file_get_contents( $source ) : false;
	if ( ! is_string( $svg ) || '' === $svg ) {
		return $fail( array( 'cannot read the theme file ' . $source ) );
	}
	$size = rx_import_svg_size( $svg );
	if ( null === $size ) {
		return $fail( array( 'no width and height in the viewBox (or width/height attributes) of ' . $source ) );
	}

	// Paths and URLs only: false = do not create the month folder here.
	$uploads = wp_upload_dir( null, false );
	if ( ! empty( $uploads['error'] ) ) {
		return $fail( array( 'uploads folder: ' . wp_strip_all_tags( (string) $uploads['error'] ) ) );
	}

	$id = rx_import_find_by_meta( '_rx_source', RX_IMPORT_LOGO_SOURCE, array( 'attachment' ) );
	if ( ! $id ) {
		return rx_import_logo_create( $dry, $label, $source, $size, $uploads, $fail );
	}

	// Existing attachment: reuse it, repairing its file and metadata if needed.
	if ( RX_IMPORT_LOGO_MIME !== get_post_mime_type( $id ) ) {
		return $fail( array( 'it has _rx_source ' . RX_IMPORT_LOGO_SOURCE . ' but MIME type ' . var_export( get_post_mime_type( $id ), true ) . '; fix or delete it, then run again' ), $id );
	}

	$recorded = '' !== (string) get_post_meta( $id, '_wp_attached_file', true );
	$changes  = array();
	$copy     = false;

	if ( ! $recorded ) {
		$rel       = rx_import_logo_new_rel( $uploads );
		$copy      = true;
		$changes[] = "no file recorded: copy the theme logo to uploads/{$rel} and record it";
	} else {
		$attached = (string) get_attached_file( $id, true );
		$rel      = rx_import_upload_rel( $attached, $uploads['basedir'] );
		if ( '' === $rel ) {
			return $fail( array( 'its file ' . wp_normalize_path( $attached ) . ' is outside the uploads folder ' . wp_normalize_path( $uploads['basedir'] ) . '; nothing was changed' ), $id );
		}
		if ( ! file_exists( $uploads['basedir'] . '/' . $rel ) ) {
			$copy      = true;
			$changes[] = "file missing: copy the theme logo back to uploads/{$rel}";
		}
	}
	$abs = $uploads['basedir'] . '/' . $rel;

	// Size and file size of the file the attachment will have: the theme's
	// when it is copied now, else the uploaded one.
	if ( ! $copy ) {
		$content = file_get_contents( $abs );
		$size    = is_string( $content ) ? rx_import_svg_size( $content ) : null;
		if ( null === $size ) {
			return $fail( array( 'no width and height in the viewBox of ' . wp_normalize_path( $abs ) ), $id );
		}
	}
	$want = rx_import_logo_metadata( $rel, $size, (int) wp_filesize( $copy ? $source : $abs ) );
	$meta = wp_get_attachment_metadata( $id, true );
	$have = is_array( $meta ) ? array(
		'width'    => (int) ( $meta['width'] ?? 0 ),
		'height'   => (int) ( $meta['height'] ?? 0 ),
		'file'     => (string) ( $meta['file'] ?? '' ),
		'filesize' => (int) ( $meta['filesize'] ?? -1 ),
	) : null;
	$compare = array_diff_key( $want, array( 'sizes' => true ) );
	if ( $have !== $compare ) {
		$changes[] = 'metadata ' . wp_json_encode( $have ) . ' -> ' . wp_json_encode( $compare );
	}

	$text = "{$label}: uploads/{$rel}";
	$url  = $recorded ? (string) wp_get_attachment_url( $id ) : $uploads['baseurl'] . '/' . $rel;
	if ( ! $changes || $dry ) {
		return array(
			'status'  => $changes ? 'updated' : 'unchanged',
			'message' => $text,
			'id'      => $id,
			'details' => $changes,
			'url'     => $url,
		);
	}

	if ( $copy ) {
		$problem = rx_import_logo_copy( $source, $abs );
		if ( '' !== $problem ) {
			return $fail( array( $problem ), $id );
		}
		if ( ! $recorded ) {
			update_attached_file( $id, $abs );
		}
	}
	wp_update_attachment_metadata( $id, array_merge( is_array( $meta ) ? $meta : array(), $want ) );

	$problems = rx_import_logo_verify( $id, $rel, $size, $uploads['basedir'] );
	if ( $problems ) {
		return $fail( $problems, $id );
	}
	return array(
		'status'  => 'updated',
		'message' => $text,
		'id'      => $id,
		'details' => $changes,
		'url'     => (string) wp_get_attachment_url( $id ),
	);
}

/**
 * Path, relative to the uploads folder, for a new copy of the logo: the
 * current uploads month folder and logo-mark.svg, numbered by
 * wp_unique_filename() when a file of that name exists.
 *
 * @param array $uploads wp_upload_dir() result.
 */
function rx_import_logo_new_rel( array $uploads ): string {
	$subdir = trim( (string) $uploads['subdir'], '/' );
	$name   = wp_unique_filename( $uploads['path'], wp_basename( RX_IMPORT_LOGO_SOURCE ) );
	return ( '' !== $subdir ? $subdir . '/' : '' ) . $name;
}

/**
 * Step 7, part 1 for a new attachment: copy the file and register it.
 *
 * @param bool     $dry     Dry run.
 * @param string   $label   Log label.
 * @param string   $source  Theme file.
 * @param int[]    $size    [ width, height ] from the viewBox.
 * @param array    $uploads wp_upload_dir() result.
 * @param callable $fail    Builds a failure result from problems and an ID.
 * @return array Result (see rx_import_logo_attachment()).
 */
function rx_import_logo_create( bool $dry, string $label, string $source, array $size, array $uploads, callable $fail ): array {
	$rel  = rx_import_logo_new_rel( $uploads );
	$abs  = $uploads['basedir'] . '/' . $rel;
	$url  = $uploads['baseurl'] . '/' . $rel;
	$text = "{$label} -> uploads/{$rel} ({$size[0]}x{$size[1]}, " . RX_IMPORT_LOGO_MIME . ')';

	if ( $dry ) {
		return array(
			'status'  => 'created',
			'message' => $text,
			'url'     => $url,
		);
	}

	$problem = rx_import_logo_copy( $source, $abs );
	if ( '' !== $problem ) {
		return $fail( array( $problem ) );
	}

	$brand  = trim( (string) get_bloginfo( 'name' ) );
	$result = rx_import_without_kses(
		static fn() => wp_insert_attachment(
			array(
				'post_title'     => '' !== $brand ? $brand . ' logo' : 'Logo',
				'post_content'   => '',
				'post_excerpt'   => '',
				'post_status'    => 'inherit',
				'post_mime_type' => RX_IMPORT_LOGO_MIME,
				'post_author'    => rx_import_author_id(),
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
				'guid'           => $url,
				'meta_input'     => array( '_rx_source' => RX_IMPORT_LOGO_SOURCE ),
			),
			$abs,
			0,
			true
		)
	);
	if ( is_wp_error( $result ) || ! $result ) {
		// Remove the copy this run just made, so the next run starts clean.
		wp_delete_file( $abs );
		return $fail( array( 'wp_insert_attachment(): ' . ( is_wp_error( $result ) ? $result->get_error_message() : 'returned 0' ) ) );
	}

	$id = (int) $result;
	wp_update_attachment_metadata( $id, rx_import_logo_metadata( $rel, $size, (int) wp_filesize( $abs ) ) );

	$problems = rx_import_logo_verify( $id, $rel, $size, $uploads['basedir'] );
	if ( $problems ) {
		return $fail( $problems, $id );
	}
	return array(
		'status'  => 'created',
		'message' => $text,
		'id'      => $id,
		'url'     => (string) wp_get_attachment_url( $id ),
	);
}

/**
 * Step 7, part 2: point Yoast's company logo at the attachment.
 *
 * @param bool  $dry        Dry run.
 * @param array $attachment Result of rx_import_logo_attachment().
 * @return array Result (see rx_import_logo()).
 */
function rx_import_logo_yoast( bool $dry, array $attachment ): array {
	$label = 'Yoast company_logo and company_logo_id';
	if ( 'failed' === $attachment['status'] ) {
		return array(
			'status'  => 'skipped',
			'message' => "{$label}: left as they are, because the logo attachment failed",
		);
	}
	if ( ! class_exists( 'WPSEO_Options' ) || ! defined( 'WPSEO_VERSION' ) ) {
		return array(
			'status'  => 'failed',
			'message' => $label,
			'details' => array( 'Yoast SEO is not loaded, so the company logo is not set (the attachment is kept). Activate Yoast with wordpress/import/setup.php, then run the import again.' ),
		);
	}

	$id   = (int) ( $attachment['id'] ?? 0 );
	$want = array(
		'company_logo'    => (string) ( $attachment['url'] ?? '' ),
		'company_logo_id' => $id,
	);
	if ( '' === $want['company_logo'] ) {
		return array(
			'status'  => 'failed',
			'message' => $label,
			'details' => array( 'the logo attachment has no URL' ),
		);
	}

	$read = static function (): array {
		WPSEO_Options::clear_cache();
		return array(
			'company_logo'    => (string) WPSEO_Options::get( 'company_logo', '' ),
			'company_logo_id' => (int) WPSEO_Options::get( 'company_logo_id', 0 ),
		);
	};
	$show = static fn( array $v, string $new_id = '' ): string => 'company_logo = ' . ( '' !== $v['company_logo'] ? $v['company_logo'] : "''" )
		. ', company_logo_id = ' . ( '' !== $new_id ? $new_id : $v['company_logo_id'] );

	$before = $read();
	if ( $id && $before === $want ) {
		return array(
			'status'  => 'unchanged',
			'message' => 'Yoast ' . $show( $want ),
		);
	}

	$message = 'Yoast ' . $show( $want, $id ? '' : 'ID of the new attachment' );
	$details = array( 'was: ' . $show( $before ) );
	if ( $dry ) {
		$details[] = 'company_logo_meta (Yoast\'s cached logo data) would be reset to false';
		return array(
			'status'  => 'updated',
			'word'    => 'set',
			'message' => $message,
			'details' => $details,
		);
	}

	foreach ( $want as $key => $value ) {
		if ( $before[ $key ] !== $value ) {
			WPSEO_Options::set( $key, $value, 'wpseo_titles' );
		}
	}
	WPSEO_Options::clear_cache();
	if ( false !== WPSEO_Options::get( 'company_logo_meta', false ) ) {
		WPSEO_Options::set( 'company_logo_meta', false, 'wpseo_titles' );
		$details[] = 'company_logo_meta (Yoast\'s cached logo data) reset to false';
	}

	$after = $read();
	if ( $after !== $want ) {
		return array(
			'status'  => 'failed',
			'message' => $label,
			'details' => array( 'read back as ' . $show( $after ) . ', expected ' . $show( $want ) ),
		);
	}
	return array(
		'status'  => 'updated',
		'word'    => 'set',
		'message' => $message,
		'details' => $details,
	);
}

/**
 * WordPress sample content removed by step 8.
 *
 * @return array<string, array{type: string, slug: string, title: string, status: string}>
 *         Key => match rules ('' status = any status but trash).
 */
function rx_import_sample_specs(): array {
	return array(
		'hello-world'    => array(
			'type'   => 'post',
			'slug'   => 'hello-world',
			'title'  => 'Hello world!',
			'status' => '',
		),
		'sample-page'    => array(
			'type'   => 'page',
			'slug'   => 'sample-page',
			'title'  => 'Sample Page',
			'status' => '',
		),
		'privacy-policy' => array(
			'type'   => 'page',
			'slug'   => 'privacy-policy',
			'title'  => 'Privacy Policy',
			'status' => 'draft',
		),
	);
}

/**
 * Step 8: delete the WordPress sample content, once.
 *
 * Each sample item is matched by type, slug, title (and status for the
 * privacy page) and must have no `_rx_source`. Once handled (deleted, or not
 * found), it is recorded in the rx_import_samples_deleted option and never
 * looked at again. The draft "Privacy Policy" waits while it is still the
 * site's privacy policy page (the imported Privacy page replaces it in step 5).
 *
 * @param Rx_Import_Run $run            Run.
 * @param int           $privacy_policy Privacy policy page ID after step 5.
 */
function rx_import_samples_step( Rx_Import_Run $run, int $privacy_policy ): void {
	global $wpdb;

	$done = get_option( RX_IMPORT_SAMPLES_OPTION, array() );
	$done = is_array( $done ) ? $done : array();
	$now  = current_time( 'mysql' );
	$save = false;

	foreach ( rx_import_sample_specs() as $sample => $spec ) {
		$label = "{$spec['type']} \"{$spec['title']}\" ({$spec['slug']})";
		if ( isset( $done[ $sample ] ) ) {
			$run->add( 'samples', 'skipped' );
			rx_import_line( 'skipped', "{$label}: already handled" . ( is_array( $done[ $sample ] ) && isset( $done[ $sample ]['at'] ) ? " on {$done[ $sample ]['at']}" : '' ) );
			continue;
		}

		$rows    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s AND post_status NOT IN ('trash', 'auto-draft', 'inherit') ORDER BY ID ASC",
				$spec['type'],
				$spec['slug']
			)
		);
		$matches = array();
		foreach ( (array) $rows as $r ) {
			if ( $r->post_title === $spec['title']
				&& ( '' === $spec['status'] || $r->post_status === $spec['status'] )
				&& ! metadata_exists( 'post', (int) $r->ID, '_rx_source' ) ) {
				$matches[] = (int) $r->ID;
			}
		}

		if ( ! $matches ) {
			$run->add( 'samples', 'skipped' );
			rx_import_line( 'skipped', "{$label}: not found, nothing to delete" );
			if ( ! $run->dry ) {
				$done[ $sample ] = array(
					'ids' => array(),
					'at'  => $now,
				);
				$save            = true;
			}
			continue;
		}

		if ( 'page' === $spec['type'] && in_array( $privacy_policy, $matches, true ) ) {
			$run->add( 'samples', 'skipped' );
			rx_import_line( 'skipped', "{$label} #{$privacy_policy}: still the privacy policy page; deleted on a later run, after the Privacy page is imported" );
			continue;
		}

		$deleted = array();
		foreach ( $matches as $id ) {
			$comments = (int) get_comments(
				array(
					'post_id' => $id,
					'count'   => true,
					'status'  => 'all',
				)
			);
			$what     = "{$label} #{$id}" . ( $comments ? " and its {$comments} comment(s)" : '' );
			if ( $run->dry ) {
				$run->add( 'samples', 'deleted' );
				rx_import_line( $run->word( 'deleted' ), $what );
				continue;
			}
			if ( wp_delete_post( $id, true ) ) {
				$deleted[] = $id;
				$run->add( 'samples', 'deleted' );
				rx_import_line( 'deleted', $what );
			} else {
				$run->fail( 'samples', $what, 'wp_delete_post() did not delete it' );
			}
		}

		if ( ! $run->dry && count( $deleted ) === count( $matches ) ) {
			$done[ $sample ] = array(
				'ids' => $deleted,
				'at'  => $now,
			);
			$save            = true;
		}
	}

	if ( $save ) {
		update_option( RX_IMPORT_SAMPLES_OPTION, $done, false );
	}
}

/**
 * Print the summary table and return the exit code.
 *
 * @param Rx_Import_Run $run Run.
 * @return int 0 when nothing failed and no data file is missing, else 1.
 */
function rx_import_summary( Rx_Import_Run $run ): int {
	echo PHP_EOL, 'Summary', $run->dry ? ' (dry run: what a real run would do; nothing was written)' : '', PHP_EOL;

	$width = 20;
	echo '  ', str_pad( '', $width );
	foreach ( Rx_Import_Run::COLUMNS as $column ) {
		echo str_pad( $column, 10, ' ', STR_PAD_LEFT );
	}
	echo PHP_EOL;
	foreach ( Rx_Import_Run::ROWS as $row => $label ) {
		echo '  ', str_pad( $label, $width );
		foreach ( Rx_Import_Run::COLUMNS as $column ) {
			echo str_pad( (string) $run->counts[ $row ][ $column ], 10, ' ', STR_PAD_LEFT );
		}
		echo PHP_EOL;
	}
	echo '  ', str_pad( 'Total', $width );
	foreach ( Rx_Import_Run::COLUMNS as $column ) {
		echo str_pad( (string) $run->total( $column ), 10, ' ', STR_PAD_LEFT );
	}
	echo PHP_EOL;
	echo '  Pages and posts are saved on every run, so "updated" includes items whose content did not change.', PHP_EOL;

	if ( $run->failures ) {
		echo PHP_EOL, 'Failed (', count( $run->failures ), '):', PHP_EOL;
		foreach ( $run->failures as $failure ) {
			$first = $failure['problems'][0];
			$more  = count( $failure['problems'] ) - 1;
			if ( mb_strlen( $first ) > 150 ) {
				$first = rtrim( mb_substr( $first, 0, 147 ) ) . '...';
			}
			echo '  - ', $failure['label'], ': ', $first, $more > 0 ? " (+{$more} more)" : '', PHP_EOL;
		}
		echo '  Full messages are in the step log above.', PHP_EOL;
	}
	if ( $run->missing ) {
		echo PHP_EOL, 'Missing data files (', count( $run->missing ), '):', PHP_EOL;
		echo '  ', wordwrap( implode( ', ', $run->missing ), 110, PHP_EOL . '  ' ), PHP_EOL;
	}
	if ( $run->blocked ) {
		echo PHP_EOL, 'Database writes blocked by the dry run (', count( $run->blocked ), '):', PHP_EOL;
		foreach ( array_slice( $run->blocked, 0, 10 ) as $query ) {
			echo '  - ', $query, PHP_EOL;
		}
	}

	$failed  = $run->total( 'failed' );
	$missing = $run->total( 'missing' );
	echo PHP_EOL;
	if ( $failed || $missing ) {
		echo 'Result: ', $failed, ' failed, ', $missing, ' missing data file(s). Exit code 1.', PHP_EOL;
		return 1;
	}
	echo 'Result: OK. Exit code 0.', PHP_EOL;
	return 0;
}

/*
 * -------------------------------------------------------------------------
 * Main
 * -------------------------------------------------------------------------
 */

/**
 * Run the import.
 *
 * @param string[] $argv Command line, script name first.
 * @return int Exit code.
 */
function rx_import_main( array $argv ): int {
	$args = rx_import_parse_args( $argv );
	if ( is_string( $args ) ) {
		fwrite( STDERR, $args . PHP_EOL . PHP_EOL . rx_import_usage() );
		return 2;
	}
	if ( $args['help'] ) {
		echo rx_import_usage();
		return 0;
	}

	rx_bootstrap_wp();
	rx_assert_local();

	$run = new Rx_Import_Run( $args['dry'] );
	if ( $run->dry ) {
		rx_import_block_writes( $run );
	}
	rx_import_content_dir( $args['content'] );

	echo 'Pharmacy import (wordpress/import/import.php)', $run->dry ? ' - DRY RUN: nothing is written' : '', PHP_EOL;

	// 1. Guard and data files.
	rx_import_step( '1/9', 'Guard' );
	if ( ! rx_import_preflight( $run ) ) {
		return 1;
	}

	rx_import_step( '', 'Data files (' . ( RX_IMPORT_CONTENT === $args['content'] ? 'wordpress/import/content/' : $args['content'] ) . ')' );
	$settings = rx_import_collect_file( $run, 'settings', 'settings.php', 'map' );
	$menus    = rx_import_collect_file( $run, 'menus', 'menus.php', 'list' );
	$pages    = rx_import_collect_dir( $run, 'pages' );
	$posts    = rx_import_collect_dir( $run, 'posts' );
	$plan     = rx_import_plan( $pages, $posts );

	// 2. Site_Settings.
	rx_import_step( '2/9', 'Site settings' );
	rx_import_guarded( $run, 'settings', 'Site settings', static fn() => rx_import_settings_step( $run, $settings, $plan ) );

	// 3. Pages, parents first.
	$deferred = array();
	rx_import_step( '3/9', 'Pages (parents first)' );
	rx_import_guarded(
		$run,
		'pages',
		'Pages',
		static function () use ( $run, $plan, &$deferred ) {
			rx_import_items_step( $run, $plan['pages'], $plan, $deferred );
		}
	);

	// 4. Posts, then fields that reference posts.
	rx_import_step( '4/9', 'Posts' );
	rx_import_guarded(
		$run,
		'posts',
		'Posts',
		static function () use ( $run, $plan, &$deferred ) {
			rx_import_items_step( $run, $plan['posts'], $plan, $deferred );
			rx_import_deferred_fields_step( $run, $deferred );
		}
	);

	// 5. Reading settings.
	rx_import_step( '5/9', 'Front page, posts page and privacy policy page' );
	$privacy = rx_import_guarded( $run, 'reading', 'Reading settings', static fn() => rx_import_reading_step( $run, $plan ) );

	// 6. Menus and locations.
	rx_import_step( '6/9', 'Menus' );
	rx_import_guarded( $run, 'menus', 'Menus', static fn() => rx_import_menus_step( $run, $menus, $plan ) );

	// 7. Logo attachment, then Yoast's company logo (one summary count each).
	rx_import_step( '7/9', 'Logo attachment' );
	rx_import_guarded(
		$run,
		'logo',
		'Logo attachment',
		static function () use ( $run ) {
			foreach ( rx_import_logo( $run->dry ) as $logo ) {
				$status  = (string) ( $logo['status'] ?? 'failed' );
				$text    = (string) ( $logo['message'] ?? '' ) . ( ! empty( $logo['id'] ) && 'failed' !== $status ? " (#{$logo['id']})" : '' );
				$details = array_values( array_filter( (array) ( $logo['details'] ?? array() ), 'is_string' ) );
				if ( 'failed' === $status || ! in_array( $status, Rx_Import_Run::COLUMNS, true ) ) {
					$run->fail( 'logo', '' !== $text ? $text : 'Logo attachment', $details ? $details : ( 'failed' === $status ? 'failed' : 'unknown status ' . var_export( $status, true ) ) );
					continue;
				}
				$run->add( 'logo', $status );
				rx_import_line( $run->word( (string) ( $logo['word'] ?? $status ) ), $text );
				foreach ( $details as $detail ) {
					rx_import_detail( $detail );
				}
			}
		}
	);

	// 8. WordPress sample content, once.
	rx_import_step( '8/9', 'WordPress sample content' );
	rx_import_guarded(
		$run,
		'samples',
		'Sample content',
		static fn() => rx_import_samples_step( $run, $run->dry && is_int( $privacy ) ? $privacy : (int) get_option( 'wp_page_for_privacy_policy' ) )
	);

	// 9. Rewrite rules.
	rx_import_step( '9/9', 'Rewrite rules' );
	rx_import_guarded(
		$run,
		'rewrite',
		'Rewrite rules',
		static function () use ( $run ) {
			if ( ! $run->dry ) {
				flush_rewrite_rules( false );
			}
			$run->add( 'rewrite', 'updated' );
			rx_import_line( $run->word( 'flushed' ), 'rewrite rules (soft flush; .htaccess is not touched)' );
		}
	);

	return rx_import_summary( $run );
}

/*
 * Run only when this file is the script being executed, so a test script can
 * require it for its functions without starting the import.
 */
$rx_import_entry = realpath( (string) ( $_SERVER['SCRIPT_FILENAME'] ?? '' ) );
if ( false === $rx_import_entry || rx_guard_norm_path( $rx_import_entry ) !== rx_guard_norm_path( __FILE__ ) ) {
	return;
}

exit( rx_import_main( $_SERVER['argv'] ?? array() ) );
