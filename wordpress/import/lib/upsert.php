<?php
/**
 * Import library: create or update pages, posts and menus, idempotently.
 *
 * Re-running the import updates the same objects instead of creating new
 * ones, so IDs stay the same:
 * - Pages and posts are found by their `_rx_source` meta (the PHP_Site file).
 * - Menus are found by name; their items by an `_rx_source` meta of the
 *   form "menu:{menu-slug}/{item id}".
 *
 * Functions return a WP_Error on failure and never exit. Load order:
 *
 *     require_once __DIR__ . '/lib/guard.php';
 *     require_once __DIR__ . '/lib/upsert.php';   // also loads fields.php
 *     rx_bootstrap_wp();
 *     rx_assert_local();
 *
 * Page / post item ($item, one per content/pages/*.php or content/posts/*.php):
 *
 *     array(
 *         'source'   => 'features/billing.php',  // required: PHP_Site file → _rx_source (lookup key)
 *         'key'      => 'billing',               // page key → _rx_key; required for pages, optional for posts
 *         'type'     => 'page',                  // 'page' (default) or 'post'
 *         'title'    => 'Billing & counter',     // required: post_title (today's breadcrumb label)
 *         'slug'     => 'billing',               // required: post_name, [a-z0-9-]
 *         'parent'   => 'features',              // optional, pages: parent page key (imported first)
 *         'template' => 'templates/feature.php', // optional, pages: page template; omitted = 'default'
 *         'status'   => 'publish',               // optional: publish (default), draft, pending, private
 *         'order'    => 0,                       // optional: menu_order (default 0)
 *         'date'     => '2026-10-14',            // optional (posts): 'Y-m-d' or 'Y-m-d H:i:s', site time
 *         'content'  => '',                      // optional: post_content (default '')
 *         'excerpt'  => '',                      // optional: post_excerpt (default '')
 *         'seo'      => array(                   // optional; when omitted the Yoast meta is left alone
 *             'title'   => 'Billing … %%sep%% %%sitename%%', // → _yoast_wpseo_title ('' deletes it)
 *             'desc'    => '…',                             // → _yoast_wpseo_metadesc ('' deletes it)
 *             'noindex' => false,                           // true → _yoast_wpseo_meta-robots-noindex = 1, false deletes it
 *         ),
 *         'group'    => 'feature',               // optional: SCF group slug, else rx_item_field_group()
 *         'fields'   => array( … ),              // SCF values, written by import.php with rx_write_fields()
 *     )
 *
 * Menu ($menu, one per menu in content/menus.php):
 *
 *     array(
 *         'name'     => 'Primary',               // required: menu name (lookup key)
 *         'location' => 'primary',               // optional: location(s) for rx_assign_menu_locations()
 *         'items'    => array(
 *             array(
 *                 'title'       => 'Features',            // required: label
 *                 'url'         => '#',                   // custom link: '#', '#rx:{key}', '/path/' or a URL
 *                 'id'          => 'features',            // optional: stable id, unique in the menu
 *                                                         // (default: the page key, else sanitize_title( title ))
 *                 'children'    => array(
 *                     array(
 *                         'title'    => 'Run the counter',
 *                         'url'      => '#',
 *                         'children' => array(
 *                             array(
 *                                 'title'       => 'Billing & counter',
 *                                 'page'        => 'billing',       // page link by page key (instead of 'url')
 *                                 'icon'        => 'i-receipt',     // optional SCF field (group_rx_menu_item)
 *                                 'description' => 'Bill in seconds, nearest expiry first', // optional SCF field
 *                             ),
 *                         ),
 *                     ),
 *                 ),
 *             ),
 *         ),
 *     )
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

require_once __DIR__ . '/fields.php';

/*
 * -------------------------------------------------------------------------
 * Shared helpers
 * -------------------------------------------------------------------------
 */

/** Pattern for slugs, page keys and menu item ids. */
const RX_IMPORT_SLUG = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

/**
 * Run a write with WordPress' kses save filters switched off.
 *
 * Under the CLI there is no current user, so WordPress filters titles and
 * content as for a user without unfiltered_html ("&" is stored as "&amp;").
 * The import is a trusted local script writing repository content, so it
 * stores the text exactly as written, like an administrator would. The
 * filters are restored afterwards.
 *
 * @param callable $write Callback doing the write.
 * @return mixed The callback's return value.
 */
function rx_import_without_kses( callable $write ): mixed {
	$active = false !== has_filter( 'content_save_pre', 'wp_filter_post_kses' )
		|| false !== has_filter( 'title_save_pre', 'wp_filter_kses' );
	if ( $active ) {
		kses_remove_filters();
	}
	try {
		return $write();
	} finally {
		if ( $active ) {
			kses_init_filters();
		}
	}
}

/**
 * Author for new posts and pages: the oldest administrator account
 * (the one the user created). No user is ever created.
 *
 * @return int User ID, or 0 when there is no administrator.
 */
function rx_import_author_id(): int {
	static $id = null;
	if ( null === $id ) {
		$ids = get_users(
			array(
				'role'    => 'administrator',
				'orderby' => 'ID',
				'order'   => 'ASC',
				'number'  => 1,
				'fields'  => 'ID',
			)
		);
		$id  = $ids ? (int) $ids[0] : 0;
	}
	return $id;
}

/**
 * Re-label a WP_Error with the item it belongs to.
 *
 * @param string   $label Item label, e.g. the source file.
 * @param WP_Error $error Error.
 * @param array    $data  Extra error data.
 */
function rx_import_error( string $label, WP_Error $error, array $data = array() ): WP_Error {
	return new WP_Error( $error->get_error_code(), $label . ': ' . implode( ' ', $error->get_error_messages() ), $data );
}

/*
 * -------------------------------------------------------------------------
 * Pages and posts
 * -------------------------------------------------------------------------
 */

/**
 * Keys an import item may have (see the file header).
 *
 * @return string[]
 */
function rx_import_item_keys(): array {
	return array( 'source', 'key', 'type', 'title', 'slug', 'parent', 'template', 'status', 'order', 'date', 'content', 'excerpt', 'seo', 'group', 'fields' );
}

/**
 * Validate an import item and fill in defaults.
 *
 * @param array $item Import item.
 * @return array|WP_Error Normalised item, or 'rx_item_invalid' listing every problem.
 */
function rx_import_normalize_item( array $item ): array|WP_Error {
	$errors = array();
	$label  = isset( $item['source'] ) && is_string( $item['source'] ) && '' !== trim( $item['source'] ) ? trim( $item['source'] ) : '(item without source)';

	$unknown = array_diff( array_map( 'strval', array_keys( $item ) ), rx_import_item_keys() );
	if ( $unknown ) {
		$errors[] = 'unknown key(s) ' . implode( ', ', $unknown );
	}

	$source = isset( $item['source'] ) && is_string( $item['source'] ) ? trim( $item['source'] ) : '';
	if ( '' === $source ) {
		$errors[] = "'source' is required (the PHP_Site file, e.g. 'pricing.php')";
	}

	$type = $item['type'] ?? 'page';
	if ( ! in_array( $type, array( 'page', 'post' ), true ) ) {
		$errors[] = "'type' must be 'page' or 'post'";
		$type     = 'page';
	}

	$key = $item['key'] ?? '';
	if ( ! is_string( $key ) || ( '' !== $key && ! preg_match( RX_IMPORT_SLUG, $key ) ) ) {
		$errors[] = "'key' must be a page key such as 'data-security'";
		$key      = '';
	} elseif ( 'page' === $type && '' === $key ) {
		$errors[] = "'key' is required for pages";
	}

	$title = $item['title'] ?? '';
	if ( ! is_string( $title ) || '' === trim( $title ) ) {
		$errors[] = "'title' is required";
		$title    = '';
	}

	$slug = $item['slug'] ?? '';
	if ( ! is_string( $slug ) || ! preg_match( RX_IMPORT_SLUG, $slug ) ) {
		$errors[] = "'slug' is required and may hold only a-z, 0-9 and single hyphens";
		$slug     = '';
	}

	$parent = $item['parent'] ?? '';
	if ( ! is_string( $parent ) || ( '' !== $parent && ! preg_match( RX_IMPORT_SLUG, $parent ) ) ) {
		$errors[] = "'parent' must be a page key";
		$parent   = '';
	} elseif ( '' !== $parent && 'page' !== $type ) {
		$errors[] = "'parent' is only allowed on pages";
	} elseif ( '' !== $parent && $parent === $key ) {
		$errors[] = "'parent' cannot be the page itself";
	}

	$template = $item['template'] ?? '';
	if ( ! is_string( $template ) || ( '' !== $template && 'default' !== $template && ! preg_match( '#^[a-z0-9_-]+(?:/[a-z0-9_-]+)*\.php$#', $template ) ) ) {
		$errors[] = "'template' must be 'default' or a theme file such as 'templates/feature.php'";
		$template = '';
	}
	if ( '' === $template && 'page' === $type ) {
		$template = 'default';
	}

	$status = $item['status'] ?? 'publish';
	if ( ! in_array( $status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
		$errors[] = "'status' must be publish, draft, pending or private";
		$status   = 'publish';
	}

	$order = $item['order'] ?? 0;
	if ( ! is_int( $order ) ) {
		$errors[] = "'order' must be an integer";
		$order    = 0;
	}

	$date = $item['date'] ?? '';
	if ( ! is_string( $date ) ) {
		$date = null;
	} elseif ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
		$date .= ' 00:00:00';
	}
	if ( null === $date || ( '' !== $date && ! rx_import_valid_datetime( $date ) ) ) {
		$errors[] = "'date' must be 'Y-m-d' or 'Y-m-d H:i:s'";
		$date     = '';
	}

	$content = $item['content'] ?? '';
	$excerpt = $item['excerpt'] ?? '';
	if ( ! is_string( $content ) ) {
		$errors[] = "'content' must be a string";
		$content  = '';
	}
	if ( ! is_string( $excerpt ) ) {
		$errors[] = "'excerpt' must be a string";
		$excerpt  = '';
	}

	$seo = null;
	if ( array_key_exists( 'seo', $item ) ) {
		if ( ! is_array( $item['seo'] ) ) {
			$errors[] = "'seo' must be an array";
		} else {
			$seo     = $item['seo'];
			$unknown = array_diff( array_map( 'strval', array_keys( $seo ) ), array( 'title', 'desc', 'noindex' ) );
			if ( $unknown ) {
				$errors[] = "unknown 'seo' key(s) " . implode( ', ', $unknown );
			}
			foreach ( array( 'title', 'desc' ) as $k ) {
				if ( isset( $seo[ $k ] ) && ! is_string( $seo[ $k ] ) ) {
					$errors[] = "'seo.{$k}' must be a string";
				}
			}
			if ( isset( $seo['noindex'] ) && ! is_bool( $seo['noindex'] ) ) {
				$errors[] = "'seo.noindex' must be true or false";
			}
		}
	}

	if ( $errors ) {
		return new WP_Error( 'rx_item_invalid', $label . ': ' . implode( '; ', $errors ) );
	}

	return array(
		'source'   => $source,
		'key'      => $key,
		'type'     => $type,
		'title'    => $title,
		'slug'     => $slug,
		'parent'   => $parent,
		'template' => $template,
		'status'   => $status,
		'order'    => $order,
		'date'     => $date,
		'content'  => $content,
		'excerpt'  => $excerpt,
		'seo'      => $seo,
	);
}

/**
 * Whether a string is a real 'Y-m-d H:i:s' date and time.
 *
 * @param string $value Date and time.
 */
function rx_import_valid_datetime( string $value ): bool {
	$date = DateTime::createFromFormat( '!Y-m-d H:i:s', $value );
	return false !== $date && $date->format( 'Y-m-d H:i:s' ) === $value;
}

/**
 * Create or update one page or post from an import item.
 *
 * - Finds the existing object by `_rx_source` (any status but auto-draft,
 *   so a page in the trash is restored rather than duplicated), else creates it.
 * - Saves title, slug, status, content, excerpt, menu order, parent (found
 *   by its `_rx_key`), page template, `post_date` (posts), closed comments
 *   and pings, and through meta_input `_rx_key`, `_rx_source` and the Yoast
 *   title, description and noindex.
 * - New objects get the oldest administrator as author.
 * - Checks before writing: valid item, the page key not used by another
 *   page, the parent exists, the template exists in the active theme.
 *   Nothing is written when a check fails.
 * - Checks after writing: WordPress kept the slug and status (a taken slug
 *   or a future date would change them). The object is saved; the error
 *   carries its ID in data['id'].
 *
 * SCF fields are not written here: call rx_write_fields() with the ID.
 *
 * @param array      $item   Import item (see the file header).
 * @param array|null $report Filled with [ 'action' => 'created'|'updated'|'', 'id' => int ].
 * @return int|WP_Error Post ID, or an error.
 */
function rx_upsert_post( array $item, ?array &$report = null ): int|WP_Error {
	$report = array(
		'action' => '',
		'id'     => 0,
	);

	try {
		$item = rx_import_normalize_item( $item );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$label = $item['source'];

		$existing = rx_import_find_by_meta( '_rx_source', $item['source'], array( $item['type'] ), array( 'auto-draft' ) );

		$other_type = 'page' === $item['type'] ? 'post' : 'page';
		$elsewhere  = rx_import_find_by_meta( '_rx_source', $item['source'], array( $other_type ), array( 'auto-draft' ) );
		if ( ! $existing && $elsewhere ) {
			return new WP_Error( 'rx_source_type', "{$label}: already imported as {$other_type} #{$elsewhere}; change it there or delete it first." );
		}

		if ( 'page' === $item['type'] ) {
			$holder = rx_import_find_by_meta( '_rx_key', $item['key'], array( 'page' ) );
			if ( $holder && $holder !== $existing ) {
				$holder_source = (string) get_post_meta( $holder, '_rx_source', true );
				return new WP_Error( 'rx_key_taken', "{$label}: page key \"{$item['key']}\" is already used by page #{$holder}" . ( '' !== $holder_source ? " ({$holder_source})" : '' ) . '.' );
			}
		}

		$parent_id = 0;
		if ( '' !== $item['parent'] ) {
			$parent_id = rx_import_find_by_meta( '_rx_key', $item['parent'], array( 'page' ) );
			if ( ! $parent_id ) {
				return new WP_Error( 'rx_parent_missing', "{$label}: parent page \"{$item['parent']}\" not found; import it first." );
			}
			if ( $existing && $parent_id === $existing ) {
				return new WP_Error( 'rx_parent_self', "{$label}: a page cannot be its own parent." );
			}
		}

		if ( '' !== $item['template'] && 'default' !== $item['template'] ) {
			$templates = wp_get_theme()->get_page_templates( null, $item['type'] );
			if ( ! isset( $templates[ $item['template'] ] ) ) {
				return new WP_Error( 'rx_template_missing', "{$label}: template \"{$item['template']}\" is not a {$item['type']} template of the active theme (" . get_stylesheet() . ').' );
			}
		}

		// Meta written through meta_input, so save_post hooks (Yoast) see it.
		$meta = array( '_rx_source' => $item['source'] );
		if ( '' !== $item['key'] ) {
			$meta['_rx_key'] = $item['key'];
		}
		$delete = array();
		if ( null !== $item['seo'] ) {
			$seo_meta = array(
				'title' => '_yoast_wpseo_title',
				'desc'  => '_yoast_wpseo_metadesc',
			);
			foreach ( $seo_meta as $k => $meta_key ) {
				$value = isset( $item['seo'][ $k ] ) ? trim( $item['seo'][ $k ] ) : '';
				if ( '' !== $value ) {
					$meta[ $meta_key ] = $value;
				} else {
					$delete[] = $meta_key;
				}
			}
			if ( ! empty( $item['seo']['noindex'] ) ) {
				$meta['_yoast_wpseo_meta-robots-noindex'] = '1';
			} else {
				$delete[] = '_yoast_wpseo_meta-robots-noindex';
			}
		}
		// Remove cleared values before saving, so the save hooks index the final state.
		if ( $existing ) {
			foreach ( $delete as $meta_key ) {
				delete_post_meta( $existing, $meta_key );
			}
		}

		$postarr = array(
			'post_type'      => $item['type'],
			'post_title'     => $item['title'],
			'post_name'      => $item['slug'],
			'post_status'    => $item['status'],
			'post_content'   => $item['content'],
			'post_excerpt'   => $item['excerpt'],
			'post_parent'    => $parent_id,
			'menu_order'     => $item['order'],
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input'     => $meta,
		);
		if ( '' !== $item['template'] ) {
			$postarr['page_template'] = $item['template'];
		}
		if ( '' !== $item['date'] ) {
			$postarr['post_date']     = $item['date'];
			$postarr['post_date_gmt'] = get_gmt_from_date( $item['date'] );
			$postarr['edit_date']     = true;
		}

		if ( $existing ) {
			$postarr['ID'] = $existing;
		} else {
			$postarr['post_author'] = rx_import_author_id();
		}

		// wp_insert_post() and wp_update_post() expect slashed data.
		$result = rx_import_without_kses(
			static fn() => $existing
				? wp_update_post( wp_slash( $postarr ), true )
				: wp_insert_post( wp_slash( $postarr ), true )
		);
		if ( is_wp_error( $result ) ) {
			return rx_import_error( $label, $result );
		}
		$id = (int) $result;
		if ( $id <= 0 ) {
			return new WP_Error( 'rx_save_failed', "{$label}: WordPress did not save the {$item['type']}." );
		}

		$report = array(
			'action' => $existing ? 'updated' : 'created',
			'id'     => $id,
		);

		$saved    = get_post( $id );
		$problems = array();
		if ( ! $saved ) {
			$problems[] = 'it cannot be read back';
		} else {
			if ( $saved->post_name !== $item['slug'] ) {
				$problems[] = "its slug is \"{$saved->post_name}\", not \"{$item['slug']}\" (the slug is taken)";
			}
			if ( $saved->post_status !== $item['status'] ) {
				$problems[] = "its status is \"{$saved->post_status}\", not \"{$item['status']}\"" . ( 'future' === $saved->post_status ? ' (the date is in the future)' : '' );
			}
		}
		if ( $problems ) {
			return new WP_Error( 'rx_item_mismatch', "{$label}: saved as {$item['type']} #{$id}, but " . implode( '; ', $problems ) . '.', array( 'id' => $id ) );
		}

		return $id;
	} catch ( Throwable $e ) {
		return new WP_Error( 'rx_exception', ( $item['source'] ?? '(item)' ) . ': ' . $e->getMessage() );
	}
}

/*
 * -------------------------------------------------------------------------
 * Menus
 * -------------------------------------------------------------------------
 */

/**
 * Validate a menu and flatten its item tree in menu order.
 *
 * @param array $menu Menu (see the file header).
 * @return array|WP_Error [ 'name', 'slug', 'locations', 'items' ] with items as
 *                        [ id, parent, source, label, title, page, page_id, url, rx, description, fields ],
 *                        or 'rx_menu_invalid' listing every problem.
 */
function rx_import_normalize_menu( array $menu ): array|WP_Error {
	$errors = array();

	$name = isset( $menu['name'] ) && is_string( $menu['name'] ) ? trim( $menu['name'] ) : '';
	if ( '' === $name ) {
		$errors[] = "'name' is required";
	}
	$label = 'Menu "' . ( '' !== $name ? $name : '?' ) . '"';

	$unknown = array_diff( array_map( 'strval', array_keys( $menu ) ), array( 'name', 'location', 'items' ) );
	if ( $unknown ) {
		$errors[] = 'unknown key(s) ' . implode( ', ', $unknown );
	}

	$locations = $menu['location'] ?? array();
	$locations = is_string( $locations ) ? array( $locations ) : $locations;
	if ( ! is_array( $locations ) || array_filter( $locations, static fn( $l ) => ! is_string( $l ) || ! preg_match( '/^[a-z0-9_]+$/', $l ) ) ) {
		$errors[] = "'location' must be a menu location id or a list of them";
		$locations = array();
	}

	$items = $menu['items'] ?? null;
	if ( ! is_array( $items ) ) {
		$errors[] = "'items' must be a list";
		$items    = array();
	}

	$slug = sanitize_title( $name );
	$flat = array();
	$seen = array();
	rx_import_flatten_menu_items( $items, '', $label, $slug, $flat, $errors, $seen );

	if ( $errors ) {
		return new WP_Error( 'rx_menu_invalid', $label . ': ' . implode( '; ', $errors ) );
	}

	return array(
		'name'      => $name,
		'slug'      => $slug,
		'locations' => array_values( array_unique( $locations ) ),
		'items'     => $flat,
	);
}

/**
 * Walk a menu item list depth first, validating each item.
 *
 * @param array  $items  Items (list).
 * @param string $parent Data id of the parent item ('' at the top).
 * @param string $trail  Label path for messages.
 * @param string $slug   Menu slug (for the _rx_source value).
 * @param array  $flat   Flattened items (by reference).
 * @param array  $errors Problems (by reference).
 * @param array  $seen   Item ids used so far (by reference).
 */
function rx_import_flatten_menu_items( array $items, string $parent, string $trail, string $slug, array &$flat, array &$errors, array &$seen ): void {
	if ( ! array_is_list( $items ) ) {
		$errors[] = "{$trail}: items must be a list";
		return;
	}

	foreach ( $items as $i => $item ) {
		$where = $trail . ' > item ' . ( $i + 1 );
		if ( ! is_array( $item ) ) {
			$errors[] = "{$where}: must be an array";
			continue;
		}

		$title = isset( $item['title'] ) && is_string( $item['title'] ) ? trim( $item['title'] ) : '';
		if ( '' === $title ) {
			$errors[] = "{$where}: 'title' is required";
		} else {
			$where = $trail . ' > ' . $title;
		}

		$unknown = array_diff( array_map( 'strval', array_keys( $item ) ), array( 'id', 'title', 'page', 'url', 'icon', 'description', 'children' ) );
		if ( $unknown ) {
			$errors[] = "{$where}: unknown key(s) " . implode( ', ', $unknown );
		}

		$page    = $item['page'] ?? '';
		$url     = $item['url'] ?? '';
		$page_id = 0;
		$rx      = false;
		if ( ! is_string( $page ) || ! is_string( $url ) ) {
			$errors[] = "{$where}: 'page' and 'url' must be strings";
			$page     = '';
			$url      = '';
		} elseif ( ( '' === $page ) === ( '' === trim( $url ) ) ) {
			$errors[] = "{$where}: set exactly one of 'page' (a page key) or 'url'";
		} elseif ( '' !== $page ) {
			$page_id = rx_import_find_by_meta( '_rx_key', $page, array( 'page' ) );
			if ( ! $page_id ) {
				$errors[] = "{$where}: no page with key \"{$page}\"; import the pages first";
			} elseif ( 'publish' !== get_post_status( $page_id ) ) {
				$errors[] = "{$where}: page \"{$page}\" (#{$page_id}) is not published";
			}
		} else {
			$url = trim( $url );
			$rx  = str_starts_with( $url, '#rx:' );
			if ( $rx && ! preg_match( '/^#rx:[a-z0-9]+(?:[_-][a-z0-9]+)*$/', $url ) ) {
				$errors[] = "{$where}: \"{$url}\" is not a valid #rx:{key} link";
			} elseif ( ! $rx && '' === sanitize_url( $url ) ) {
				$errors[] = "{$where}: WordPress rejects the URL \"{$url}\"";
			}
		}

		$id = $item['id'] ?? ( '' !== $page ? $page : sanitize_title( $title ) );
		if ( ! is_string( $id ) || ! preg_match( '/^[a-z0-9]+(?:[_-][a-z0-9]+)*$/', $id ) ) {
			$errors[] = "{$where}: 'id' must hold only a-z, 0-9, '-' and '_'";
			$id       = '';
		} elseif ( isset( $seen[ $id ] ) ) {
			$errors[] = "{$where}: id \"{$id}\" is used twice in this menu; set a unique 'id'";
		}
		$seen[ $id ] = true;

		$fields = array();
		foreach ( array( 'icon', 'description' ) as $k ) {
			if ( array_key_exists( $k, $item ) ) {
				if ( null !== $item[ $k ] && ! is_string( $item[ $k ] ) ) {
					$errors[] = "{$where}: '{$k}' must be a string";
					continue;
				}
				$fields[ $k ] = $item[ $k ];
			}
		}

		$flat[] = array(
			'id'          => $id,
			'parent'      => $parent,
			'source'      => 'menu:' . $slug . '/' . $id,
			'label'       => $where,
			'title'       => $title,
			'page'        => $page,
			'page_id'     => $page_id,
			'url'         => $url,
			'rx'          => $rx,
			'description' => isset( $fields['description'] ) ? (string) $fields['description'] : '',
			'fields'      => $fields,
		);

		if ( array_key_exists( 'children', $item ) ) {
			if ( ! is_array( $item['children'] ) ) {
				$errors[] = "{$where}: 'children' must be a list";
				continue;
			}
			rx_import_flatten_menu_items( $item['children'], $id, $where, $slug, $flat, $errors, $seen );
		}
	}
}

/**
 * Items of a menu with their `_rx_source`, oldest first (any status).
 *
 * @param int $menu_id Menu term ID.
 * @return array<int, string> Item ID => _rx_source ('' when missing).
 */
function rx_import_menu_items( int $menu_id ): array {
	$ids = get_posts(
		array(
			'post_type'              => 'nav_menu_item',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts'            => -1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- one menu.
				array(
					'taxonomy' => 'nav_menu',
					'field'    => 'term_id',
					'terms'    => $menu_id,
				),
			),
		)
	);

	$out = array();
	foreach ( $ids as $id ) {
		$source           = get_post_meta( (int) $id, '_rx_source', true );
		$out[ (int) $id ] = is_string( $source ) ? $source : '';
	}
	return $out;
}

/**
 * Create or update a menu and its items from import data.
 *
 * - The menu is found by name, else created. Its name is never changed.
 * - Each item is found by its `_rx_source` ("menu:{menu-slug}/{item id}")
 *   among the menu's items and updated in place, else created, so item IDs
 *   stay the same between runs. Order, parent, title, link and description
 *   follow the data.
 * - `#rx:{key}` links are stored as they are. WordPress' sanitize_url()
 *   would blank them, so the item is saved with a '#' placeholder and the
 *   `_menu_item_url` meta is then written directly and read back. Only the
 *   strict pattern #rx:[a-z0-9_-] is stored this way; every other URL goes
 *   through sanitize_url() as usual.
 * - `icon` and `description` are written to the SCF menu-item fields
 *   (group menu_item); the description also fills the core description.
 * - Items of the menu that the data no longer lists (and duplicates) are
 *   deleted, so the menu matches the data (design §13, step 6).
 * - Nothing is written when the data is invalid or a page key is unknown.
 *
 * Locations are not assigned here: pass $report['locations'] and the ID to
 * rx_assign_menu_locations().
 *
 * @param array      $menu   Menu (see the file header).
 * @param array|null $report Filled with action ('created'|'updated'), id, locations,
 *                           items_created, items_updated, items_deleted and
 *                           item_ids (item id => nav_menu_item ID).
 * @return int|WP_Error Menu term ID, or an error. When only some items failed
 *                      the error is 'rx_menu_items' with the menu ID in data['id'].
 */
function rx_upsert_menu( array $menu, ?array &$report = null ): int|WP_Error {
	$report = array(
		'action'        => '',
		'id'            => 0,
		'locations'     => array(),
		'items_created' => 0,
		'items_updated' => 0,
		'items_deleted' => 0,
		'item_ids'      => array(),
	);

	try {
		$data = rx_import_normalize_menu( $menu );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$label = 'Menu "' . $data['name'] . '"';

		$term = get_term_by( 'name', $data['name'], 'nav_menu' );
		if ( $term instanceof WP_Term ) {
			$menu_id = (int) $term->term_id;
			$action  = 'updated';
		} else {
			$created = wp_create_nav_menu( wp_slash( $data['name'] ) );
			if ( is_wp_error( $created ) ) {
				return rx_import_error( $label, $created );
			}
			$menu_id = (int) $created;
			$action  = 'created';
		}
		$report['action']    = $action;
		$report['id']        = $menu_id;
		$report['locations'] = $data['locations'];

		// Existing items: the oldest item per source is kept and updated.
		$existing = rx_import_menu_items( $menu_id );
		$keep     = array();
		foreach ( $existing as $item_id => $source ) {
			if ( '' !== $source && ! isset( $keep[ $source ] ) ) {
				$keep[ $source ] = $item_id;
			}
		}

		$errors   = array();
		$db_ids   = array();
		$position = 0;

		foreach ( $data['items'] as $it ) {
			++$position;

			$parent_db = 0;
			if ( '' !== $it['parent'] ) {
				if ( empty( $db_ids[ $it['parent'] ] ) ) {
					$errors[] = "{$it['label']}: skipped because its parent item failed";
					continue;
				}
				$parent_db = $db_ids[ $it['parent'] ];
			}

			$item_id = $keep[ $it['source'] ] ?? 0;
			$args    = array(
				'menu-item-title'       => wp_slash( $it['title'] ),
				'menu-item-description' => wp_slash( $it['description'] ),
				'menu-item-parent-id'   => $parent_db,
				'menu-item-position'    => $position,
				'menu-item-status'      => 'publish',
			);
			if ( '' !== $it['page'] ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = 'page';
				$args['menu-item-object-id'] = $it['page_id'];
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = $it['rx'] ? '#' : $it['url'];
			}

			$saved = rx_import_without_kses( static fn() => wp_update_nav_menu_item( $menu_id, $item_id, $args ) );
			if ( is_wp_error( $saved ) || ! $saved ) {
				$errors[] = $it['label'] . ': ' . ( is_wp_error( $saved ) ? $saved->get_error_message() : 'WordPress did not save the item' );
				continue;
			}
			$saved_id = (int) $saved;

			update_post_meta( $saved_id, '_rx_source', wp_slash( $it['source'] ) );

			if ( '' === $it['page'] ) {
				$expected = $it['url'];
				if ( $it['rx'] ) {
					update_post_meta( $saved_id, '_menu_item_url', wp_slash( $it['url'] ) );
				} else {
					$expected = sanitize_url( $it['url'] );
				}
				$stored = get_post_meta( $saved_id, '_menu_item_url', true );
				if ( $stored !== $expected ) {
					$errors[] = "{$it['label']}: URL reads back as " . var_export( $stored, true ) . ', not ' . var_export( $expected, true );
				}
			}

			if ( $it['fields'] ) {
				$written = rx_write_fields( $saved_id, 'menu_item', $it['fields'] );
				if ( is_wp_error( $written ) ) {
					$errors[] = $it['label'] . ': ' . $written->get_error_message();
				}
			}

			$db_ids[ $it['id'] ]             = $saved_id;
			$report['item_ids'][ $it['id'] ] = $saved_id;
			++$report[ $item_id ? 'items_updated' : 'items_created' ];
		}

		// Items the data no longer lists, items without a source and duplicates.
		$wanted = array_flip( array_column( $data['items'], 'source' ) );
		foreach ( $existing as $item_id => $source ) {
			if ( '' !== $source && isset( $wanted[ $source ] ) && $keep[ $source ] === $item_id ) {
				continue;
			}
			if ( wp_delete_post( $item_id, true ) ) {
				++$report['items_deleted'];
			} else {
				$errors[] = "{$label}: could not delete stale item #{$item_id}";
			}
		}

		if ( $errors ) {
			return new WP_Error( 'rx_menu_items', $label . ': ' . count( $errors ) . ' item problem(s): ' . implode( '; ', $errors ), array( 'id' => $menu_id ) );
		}
		return $menu_id;
	} catch ( Throwable $e ) {
		return new WP_Error( 'rx_exception', 'Menu "' . ( is_string( $menu['name'] ?? null ) ? $menu['name'] : '?' ) . '": ' . $e->getMessage() );
	}
}

/**
 * Assign menus to theme menu locations (theme mod nav_menu_locations of
 * the active theme). Other locations keep their menus.
 *
 * Not called by this library: import.php calls it after rx_upsert_menu(),
 * with the locations from each menu's report.
 *
 * @param array<string, int> $locations Location id => menu term ID.
 * @return true|WP_Error True, or an error listing unknown locations or menus (nothing is saved then).
 */
function rx_assign_menu_locations( array $locations ): bool|WP_Error {
	$registered = get_registered_nav_menus();
	$errors     = array();
	foreach ( $locations as $location => $menu_id ) {
		if ( ! is_string( $location ) || ! isset( $registered[ $location ] ) ) {
			$errors[] = 'location "' . $location . '" is not registered by the active theme (' . get_stylesheet() . ')';
		}
		if ( ! is_int( $menu_id ) || ! is_nav_menu( $menu_id ) ) {
			$errors[] = 'menu ' . var_export( $menu_id, true ) . ' for "' . $location . '" does not exist';
		}
	}
	if ( $errors ) {
		return new WP_Error( 'rx_menu_locations', 'Menu locations not saved: ' . implode( '; ', $errors ) . '.' );
	}

	$current = get_theme_mod( 'nav_menu_locations' );
	$current = is_array( $current ) ? $current : array();
	foreach ( $locations as $location => $menu_id ) {
		$current[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $current );
	return true;
}
