<?php
/**
 * Import library: write SCF field values by their predictable keys.
 *
 * Field keys follow the theme's acf-json convention field_rx_{group}_{name}
 * (design §5), so a data file only lists names:
 *
 *     rx_write_fields( $page_id, 'pricing', array(
 *         'hero' => array( 'crumb' => 'Pricing', 'h1' => '…' ),        // group: sub-field names
 *         'faq'  => array(                                             // repeater: list of rows
 *             array( 'q' => '…', 'a_html' => '…', 'confirmed' => true ),
 *         ),
 *     ) );
 *     rx_write_fields( 'option', 'settings', array( 'sales_phone' => '+91 …' ) );
 *
 * Value rules:
 * - Group: array of sub-field name => value.
 * - Repeater: list of rows, each an array of sub-field name => value.
 * - Flexible content: list of rows, each with 'acf_fc_layout' => layout name
 *   plus that layout's sub-field names.
 * - Relationship / post object: post IDs, or references resolved by
 *   rx_resolve_post_ref() (a `_rx_source`, a page key or a published slug).
 * - Date picker: 'Y-m-d' or SCF's own 'Ymd'.
 * - Select, radio, button group, checkbox: values must be among the field's
 *   choices (when the field lists any).
 * - Every other field: a single scalar value (or null).
 *
 * Nothing is written silently: an unknown name (anywhere in the tree), a
 * value of the wrong shape or an unresolved reference is reported, and the
 * top-level field holding it is left as it is. Every other field is written.
 *
 * @package pharmacy
 */

// CLI only: a web request to this file prints nothing.
PHP_SAPI === 'cli' || exit;

/**
 * Write SCF values for one field group to a post, page, menu item or the
 * Site_Settings options page.
 *
 * @param int|string $target Post ID (page, post or nav_menu_item), or 'option' for Site_Settings.
 * @param string     $group  Group slug used in the field keys, e.g. 'home', 'feature', 'settings', 'menu_item'.
 * @param array      $fields Field name => value (see the file header for value shapes).
 * @return true|WP_Error True when every field was written. Otherwise a WP_Error
 *                       ('rx_fields') whose data holds 'written' (names saved)
 *                       and 'problems' (one message per problem, with its path).
 */
function rx_write_fields( int|string $target, string $group, array $fields ): bool|WP_Error {
	if ( ! function_exists( 'update_field' ) || ! function_exists( 'acf_get_field' ) ) {
		return new WP_Error( 'rx_scf_inactive', 'Secure Custom Fields is not active, so the "' . $group . '" fields cannot be written. Run wordpress/import/setup.php first.' );
	}
	if ( ! did_action( 'acf/init' ) ) {
		return new WP_Error( 'rx_scf_not_ready', 'Secure Custom Fields has not initialised yet (acf/init has not run), so the "' . $group . '" fields cannot be written.' );
	}
	if ( ! preg_match( '/^[a-z0-9]+(?:_[a-z0-9]+)*$/', $group ) ) {
		return new WP_Error( 'rx_fields_group', 'Invalid field group slug "' . $group . '" (expected lower-case letters, digits and underscores).' );
	}

	$target = rx_fields_target( $target );
	if ( is_wp_error( $target ) ) {
		return $target;
	}

	$written  = array();
	$problems = array();

	foreach ( $fields as $name => $value ) {
		if ( ! is_string( $name ) || ! preg_match( '/^[a-z0-9]+(?:_[a-z0-9]+)*$/', $name ) ) {
			$problems[] = var_export( $name, true ) . ': invalid field name';
			continue;
		}

		$key   = "field_rx_{$group}_{$name}";
		$field = acf_get_field( $key );
		if ( ! is_array( $field ) ) {
			$problems[] = "{$name}: unknown field ({$key} is not defined)";
			continue;
		}
		if ( (string) $field['name'] !== $name ) {
			$problems[] = "{$name}: {$key} is named \"{$field['name']}\", not \"{$name}\"";
			continue;
		}
		if ( ! empty( $field['parent'] ) && is_array( acf_get_field( $field['parent'] ) ) ) {
			$problems[] = "{$name}: {$key} is a sub-field; write it through its parent field";
			continue;
		}

		$issues   = array();
		$prepared = rx_fields_prepare( $field, $value, $name, $issues );
		if ( $issues ) {
			array_push( $problems, ...$issues );
			continue;
		}

		try {
			// update_field() unslashes values (as for $_POST), so slash them once.
			update_field( $key, wp_slash( $prepared ), $target );
			$written[] = $name;
		} catch ( Throwable $e ) {
			$problems[] = "{$name}: " . $e->getMessage();
		}
	}

	if ( $problems ) {
		return new WP_Error(
			'rx_fields',
			sprintf(
				'%d problem(s) writing "%s" fields to %s: %s',
				count( $problems ),
				$group,
				'option' === $target ? 'Site_Settings' : 'post #' . $target,
				implode( '; ', $problems )
			),
			array(
				'written'  => $written,
				'problems' => $problems,
			)
		);
	}
	return true;
}

/**
 * Validate a write target.
 *
 * @param int|string $target Post ID, or 'option' / 'options'.
 * @return int|string|WP_Error Post ID, 'option', or an error.
 */
function rx_fields_target( int|string $target ): int|string|WP_Error {
	if ( 'option' === $target || 'options' === $target ) {
		return 'option';
	}
	if ( is_int( $target ) || ctype_digit( $target ) ) {
		$id = (int) $target;
		if ( $id > 0 && get_post( $id ) ) {
			return $id;
		}
	}
	return new WP_Error( 'rx_fields_target', 'Invalid field target ' . var_export( $target, true ) . ' (expected an existing post ID or "option").' );
}

/**
 * Check one field value against its SCF definition and prepare it for
 * update_field(). Problems are appended to $issues with their path.
 *
 * @param array  $field  SCF field (from acf_get_field(), sub-fields loaded).
 * @param mixed  $value  Value from the data file.
 * @param string $path   Path for messages, e.g. 'faq[2].a_html'.
 * @param array  $issues Problems found (by reference).
 * @return mixed Prepared value.
 */
function rx_fields_prepare( array $field, mixed $value, string $path, array &$issues ): mixed {
	$type = (string) ( $field['type'] ?? '' );

	switch ( $type ) {
		case 'tab':
		case 'message':
		case 'accordion':
			$issues[] = "{$path}: a {$type} field holds no value";
			return null;

		case 'group':
			if ( ! is_array( $value ) || ( $value && array_is_list( $value ) ) ) {
				$issues[] = "{$path}: expects an array of sub-field values";
				return null;
			}
			return rx_fields_prepare_row( (array) ( $field['sub_fields'] ?? array() ), $value, $path, $issues );

		case 'repeater':
			if ( null === $value ) {
				return array();
			}
			if ( ! is_array( $value ) || ! array_is_list( $value ) ) {
				$issues[] = "{$path}: expects a list of rows";
				return null;
			}
			$rows = array();
			foreach ( $value as $i => $row ) {
				if ( ! is_array( $row ) || ( $row && array_is_list( $row ) ) ) {
					$issues[] = "{$path}[{$i}]: expects an array of sub-field values";
					continue;
				}
				$rows[] = rx_fields_prepare_row( (array) ( $field['sub_fields'] ?? array() ), $row, "{$path}[{$i}]", $issues );
			}
			return $rows;

		case 'flexible_content':
			if ( null === $value ) {
				return array();
			}
			if ( ! is_array( $value ) || ! array_is_list( $value ) ) {
				$issues[] = "{$path}: expects a list of layout rows";
				return null;
			}
			$layouts = array();
			foreach ( (array) ( $field['layouts'] ?? array() ) as $layout ) {
				if ( is_array( $layout ) && isset( $layout['name'] ) ) {
					$layouts[ (string) $layout['name'] ] = $layout;
				}
			}
			$rows = array();
			foreach ( $value as $i => $row ) {
				$layout = is_array( $row ) && isset( $row['acf_fc_layout'] ) && is_string( $row['acf_fc_layout'] ) ? $row['acf_fc_layout'] : '';
				if ( '' === $layout || ! isset( $layouts[ $layout ] ) ) {
					$issues[] = "{$path}[{$i}]: " . ( '' === $layout ? 'missing acf_fc_layout' : "unknown layout \"{$layout}\"" );
					continue;
				}
				unset( $row['acf_fc_layout'] );
				$rows[] = array( 'acf_fc_layout' => $layout )
					+ rx_fields_prepare_row( (array) ( $layouts[ $layout ]['sub_fields'] ?? array() ), $row, "{$path}[{$i}]({$layout})", $issues );
			}
			return $rows;

		case 'relationship':
		case 'post_object':
			if ( null === $value || '' === $value || array() === $value ) {
				return $value;
			}
			$multiple = 'relationship' === $type || ! empty( $field['multiple'] );
			if ( ! $multiple && is_array( $value ) ) {
				$issues[] = "{$path}: expects a single post";
				return null;
			}
			$types = array_values( array_filter( (array) ( $field['post_type'] ?? array() ), 'is_string' ) );
			$ids   = array();
			foreach ( (array) $value as $ref ) {
				$id = rx_resolve_post_ref( $ref, $types );
				if ( ! $id ) {
					$issues[] = "{$path}: no post found for " . var_export( $ref, true );
					continue;
				}
				$ids[] = $id;
			}
			return $multiple ? $ids : ( $ids[0] ?? null );

		case 'date_picker':
			if ( is_string( $value ) && preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
				$value = $m[1] . $m[2] . $m[3];
			}
			if ( null !== $value && '' !== $value && ! ( is_string( $value ) && preg_match( '/^\d{8}$/', $value ) ) ) {
				$issues[] = "{$path}: expects a date as Y-m-d or Ymd";
				return null;
			}
			return $value;

		case 'select':
		case 'radio':
		case 'button_group':
		case 'checkbox':
			$multiple = 'checkbox' === $type || ( 'select' === $type && ! empty( $field['multiple'] ) );
			if ( ! $multiple && is_array( $value ) ) {
				$issues[] = "{$path}: expects a single choice";
				return null;
			}
			$choices = isset( $field['choices'] ) && is_array( $field['choices'] ) ? array_map( 'strval', array_keys( $field['choices'] ) ) : array();
			foreach ( (array) $value as $choice ) {
				if ( null === $choice || '' === $choice || ! $choices ) {
					continue;
				}
				if ( ! is_scalar( $choice ) || ! in_array( (string) $choice, $choices, true ) ) {
					$issues[] = "{$path}: " . var_export( $choice, true ) . ' is not one of the field\'s choices';
				}
			}
			return $value;

		case 'link':
		case 'gallery':
		case 'taxonomy':
		case 'user':
		case 'google_map':
			// SCF accepts arrays for these types as they are.
			return $value;

		default:
			if ( null !== $value && ! is_scalar( $value ) ) {
				$issues[] = "{$path}: expects a single value, not " . gettype( $value );
				return null;
			}
			return $value;
	}
}

/**
 * Check and prepare one row of sub-field values (group value, repeater row
 * or flexible layout row). Keys may be sub-field names or keys; the result
 * is keyed by name.
 *
 * @param array  $sub_fields SCF sub-fields.
 * @param array  $row        Sub-field name => value.
 * @param string $path       Path for messages.
 * @param array  $issues     Problems found (by reference).
 * @return array Prepared row.
 */
function rx_fields_prepare_row( array $sub_fields, array $row, string $path, array &$issues ): array {
	$by_name = array();
	$by_key  = array();
	foreach ( $sub_fields as $sub_field ) {
		if ( ! is_array( $sub_field ) ) {
			continue;
		}
		if ( isset( $sub_field['name'] ) && '' !== (string) $sub_field['name'] ) {
			$by_name[ (string) $sub_field['name'] ] = $sub_field;
		}
		if ( isset( $sub_field['key'] ) ) {
			$by_key[ (string) $sub_field['key'] ] = $sub_field;
		}
	}

	$out = array();
	foreach ( $row as $name => $value ) {
		$sub_field = $by_name[ (string) $name ] ?? $by_key[ (string) $name ] ?? null;
		if ( null === $sub_field ) {
			$issues[] = "{$path}.{$name}: unknown sub-field";
			continue;
		}
		$out[ (string) $sub_field['name'] ] = rx_fields_prepare( $sub_field, $value, "{$path}.{$name}", $issues );
	}
	return $out;
}

/**
 * Resolve a post reference from a data file to a post ID.
 *
 * Tried in order:
 * 1. A positive integer: that post, when it exists.
 * 2. A `_rx_source` value (e.g. 'blog/cut-expiry-loss.php').
 * 3. A page key (`_rx_key`), when pages are allowed.
 * 4. The slug of a published post of an allowed type (e.g. 'cut-expiry-loss').
 *
 * @param mixed    $ref        Reference.
 * @param string[] $post_types Allowed post types; empty means post and page.
 * @return int Post ID, or 0 when nothing matches.
 */
function rx_resolve_post_ref( mixed $ref, array $post_types = array() ): int {
	global $wpdb;

	$types = $post_types ? array_values( $post_types ) : array( 'post', 'page' );

	if ( is_int( $ref ) || ( is_string( $ref ) && ctype_digit( $ref ) ) ) {
		$post = get_post( (int) $ref );
		return $post && in_array( $post->post_type, $types, true ) ? (int) $post->ID : 0;
	}
	if ( ! is_string( $ref ) || '' === trim( $ref ) ) {
		return 0;
	}

	$id = rx_import_find_by_meta( '_rx_source', $ref, $types );
	if ( ! $id && in_array( 'page', $types, true ) ) {
		$id = rx_import_find_by_meta( '_rx_key', $ref, array( 'page' ) );
	}
	if ( $id ) {
		return $id;
	}

	$in = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $in holds only %s placeholders.
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type IN ($in) AND post_status = 'publish' ORDER BY ID ASC LIMIT 1",
			array_merge( array( $ref ), $types )
		)
	);
}

/**
 * Oldest post of the given types whose meta $meta_key equals $value.
 *
 * Used for the import's own markers: `_rx_source` (source file of a page,
 * post, menu item or attachment) and `_rx_key` (page key).
 *
 * @param string   $meta_key         Meta key, e.g. '_rx_source'.
 * @param string   $value            Exact meta value.
 * @param string[] $post_types       Post types to search.
 * @param string[] $exclude_statuses Post statuses to skip (default trash and auto-draft).
 * @return int Post ID, or 0.
 */
function rx_import_find_by_meta( string $meta_key, string $value, array $post_types, array $exclude_statuses = array( 'trash', 'auto-draft' ) ): int {
	global $wpdb;

	$types = array_values( array_filter( $post_types, 'is_string' ) );
	if ( '' === $value || ! $types ) {
		return 0;
	}
	$statuses = array_values( array_filter( $exclude_statuses, 'is_string' ) ) ?: array( 'auto-draft' );

	$in_types    = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
	$in_statuses = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- the IN lists hold only %s placeholders.
			"SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
			WHERE m.meta_key = %s AND m.meta_value = %s AND p.post_type IN ($in_types) AND p.post_status NOT IN ($in_statuses)
			ORDER BY p.ID ASC LIMIT 1",
			array_merge( array( $meta_key, $value ), $types, $statuses )
		)
	);
}

/**
 * SCF group slug for an import item ($item shape: see upsert.php).
 *
 * - $item['group'] when set.
 * - Posts: 'post'.
 * - Pages with a template 'templates/{name}.php': {name} with '-' → '_'
 *   (feature, legal, pricing, features_hub, switch, faq, about, contact,
 *   support, demo).
 * - Pages keyed 'home' (front page) or 'blog' (posts page): the key.
 * - Anything else: '' (the item has no field group).
 *
 * @param array $item Import item.
 * @return string Group slug or ''.
 */
function rx_item_field_group( array $item ): string {
	if ( isset( $item['group'] ) && is_string( $item['group'] ) && '' !== $item['group'] ) {
		return $item['group'];
	}
	if ( 'post' === ( $item['type'] ?? 'page' ) ) {
		return 'post';
	}
	$template = isset( $item['template'] ) && is_string( $item['template'] ) ? $item['template'] : '';
	if ( preg_match( '#^templates/([a-z0-9-]+)\.php$#', $template, $m ) ) {
		return str_replace( '-', '_', $m[1] );
	}
	$key = isset( $item['key'] ) && is_string( $item['key'] ) ? $item['key'] : '';
	return in_array( $key, array( 'home', 'blog' ), true ) ? $key : '';
}
