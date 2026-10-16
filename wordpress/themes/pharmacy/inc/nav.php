<?php
/**
 * Navigation: Rx_Mega_Walker, Rx_Footer_Walker, #rx: link resolver.
 *
 * - Menu item helpers (rx_menu_item_*): link target, import id, own page
 *   key, label, icon, description and optional link attributes of a nav
 *   menu item. Shared by every walker in this file.
 * - Rx_Mega_Walker prints the primary menu with the exact markup of the
 *   PHP_Site's partials/header.php (mega menu panels, groups, promo).
 * - Rx_Footer_Walker prints the footer columns (`<li><a>` items) and, in
 *   its bare mode, the legal links (`<a>` only), with the markup of
 *   partials/footer.php. Wrapped by rx_footer_menu() and rx_legal_links();
 *   rx_menu_name() gives a column heading.
 * - `#rx:{key}` custom links: rx_menu_resolve_url() turns them into the
 *   real URL. The theme's walkers call it directly; the
 *   nav_menu_link_attributes filter does the same for any other walker
 *   (core Walker_Nav_Menu, plugins).
 * - Admin guard: on the screens that edit menus, `#rx` is added to the
 *   allowed URL protocols, so esc_url()/sanitize_url() keep `#rx:` links
 *   instead of blanking them (see rx_menu_rx_scheme_init()).
 *
 * Usage (header.php):
 *
 *     wp_nav_menu( array(
 *         'theme_location' => 'primary',
 *         'container'      => false,
 *         'items_wrap'     => '<ul class="hd-menu">%3$s</ul>',
 *         'depth'          => 3,
 *         'fallback_cb'    => false,
 *         'walker'         => new Rx_Mega_Walker(),
 *     ) );
 *
 * Usage (footer.php): see the "Footer menus" section below.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------
 * Menu item helpers
 * -------------------------------------------------------------------------
 */

/**
 * `#rx:{key}` keys that point at a Site_Settings URL instead of a page.
 *
 * @return string[]
 */
function rx_menu_setting_keys(): array {
	return array( 'app_login', 'app_signup', 'help_url' );
}

/**
 * Link target of a menu item, unescaped (escape with esc_url() on output).
 *
 * - `#rx:app_login`, `#rx:app_signup`, `#rx:help_url`: the Site_Settings URL
 *   ('' when the setting is empty).
 * - Any other `#rx:{key}`: rx_url( $key ), so pages not built yet keep
 *   pointing at today's home page anchors.
 * - Page items: their permalink (set by wp_setup_nav_menu_item()).
 * - Other custom links: the URL as stored.
 *
 * esc_url() blanks `#rx:` URLs, so the raw URL is read here: the item's
 * `url` property, or its `_menu_item_url` meta when a filter emptied it.
 *
 * @param object $item Menu item from wp_get_nav_menu_items() or a nav walker.
 * @return string URL, unescaped.
 */
function rx_menu_item_href( $item ): string {
	if ( ! is_object( $item ) ) {
		return '';
	}

	$url = isset( $item->url ) && is_string( $item->url ) ? $item->url : '';
	if ( '' === $url && isset( $item->type ) && 'custom' === $item->type ) {
		$meta = get_post_meta( rx_menu_item_db_id( $item ), '_menu_item_url', true );
		$url  = is_string( $meta ) ? $meta : '';
	}

	return rx_menu_resolve_url( $url );
}

/**
 * Resolves a `#rx:{key}` link; any other URL is returned unchanged.
 *
 * - `#rx:app_login`, `#rx:app_signup`, `#rx:help_url`: the Site_Settings URL
 *   ('' when the setting is empty).
 * - Any other `#rx:{key}`: rx_url( $key ) (a page with that key, a home page
 *   anchor for pages not built yet, else the home page).
 *
 * @param string $url Stored menu item URL.
 * @return string URL, unescaped (escape with esc_url() on output).
 */
function rx_menu_resolve_url( string $url ): string {
	if ( ! str_starts_with( $url, '#rx:' ) ) {
		return $url;
	}

	$key = substr( $url, 4 );
	if ( in_array( $key, rx_menu_setting_keys(), true ) ) {
		$value = rx_setting( $key );
		return is_string( $value ) ? $value : '';
	}
	return rx_url( $key );
}

/**
 * Database ID of a menu item (the nav_menu_item post).
 *
 * @param object $item Menu item.
 */
function rx_menu_item_db_id( $item ): int {
	if ( isset( $item->db_id ) && (int) $item->db_id > 0 ) {
		return (int) $item->db_id;
	}
	return isset( $item->ID ) ? (int) $item->ID : 0;
}

/**
 * Import id of a menu item: the part after the slash of its `_rx_source`
 * meta ("menu:{menu-slug}/{id}", written by the import). '' for items an
 * editor added in the admin.
 *
 * @param object $item Menu item.
 */
function rx_menu_item_id( $item ): string {
	$id = rx_menu_item_db_id( $item );
	if ( $id <= 0 ) {
		return '';
	}
	$source = get_post_meta( $id, '_rx_source', true );
	if ( is_string( $source ) && preg_match( '#^menu:[^/]+/([a-z0-9]+(?:[_-][a-z0-9]+)*)$#', $source, $m ) ) {
		return $m[1];
	}
	return '';
}

/**
 * Page key of the menu item itself, for the active state.
 *
 * rx_menu_item_key() (a `#rx:{key}` link or the linked page's `_rx_key`),
 * else the import id. The import id covers group items such as Features
 * (custom link "#", id `features`), which today's header marks active on
 * the Features hub.
 *
 * @param object $item Menu item.
 */
function rx_menu_item_own_key( $item ): string {
	$key = rx_menu_item_key( $item );
	return '' !== $key ? $key : rx_menu_item_id( $item );
}

/**
 * Menu item label as plain text (entities decoded; escape on output).
 *
 * @param object $item Menu item.
 */
function rx_menu_item_label( $item ): string {
	$title = isset( $item->title ) && is_scalar( $item->title ) ? (string) $item->title : '';
	return trim( html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Sprite icon id of a menu item (SCF field `icon`, group_rx_menu_item),
 * e.g. 'i-receipt'. '' when not set or not a valid symbol id.
 *
 * @param object $item Menu item.
 */
function rx_menu_item_icon( $item ): string {
	$icon = rx_field( 'icon', rx_menu_item_db_id( $item ) );
	return is_string( $icon ) && preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $icon ) ? $icon : '';
}

/**
 * One-line description of a menu item: the SCF field `description`, else
 * the core menu item description. Raw text; print it with rx_text().
 *
 * @param object $item Menu item.
 */
function rx_menu_item_description( $item ): string {
	$description = rx_field( 'description', rx_menu_item_db_id( $item ) );
	if ( ! is_string( $description ) || '' === trim( $description ) ) {
		$description = isset( $item->description ) && is_string( $item->description ) ? $item->description : '';
	}
	return trim( $description );
}

/**
 * Optional link attributes an editor can set on a menu item (title
 * attribute, target, rel), as an attribute string with a leading space.
 * '' when they are empty, as for every imported item. A `_blank` target
 * without a rel gets rel="noopener".
 *
 * @param object $item Menu item.
 */
function rx_menu_item_link_attributes( $item ): string {
	$title  = isset( $item->attr_title ) && is_string( $item->attr_title ) ? trim( $item->attr_title ) : '';
	$target = isset( $item->target ) && is_string( $item->target ) ? trim( $item->target ) : '';
	$rel    = isset( $item->xfn ) && is_string( $item->xfn ) ? trim( $item->xfn ) : '';
	if ( '_blank' === $target && '' === $rel ) {
		$rel = 'noopener';
	}

	return ( '' !== $title ? ' title="' . esc_attr( $title ) . '"' : '' )
		. ( '' !== $target ? ' target="' . esc_attr( $target ) . '"' : '' )
		. ( '' !== $rel ? ' rel="' . esc_attr( $rel ) . '"' : '' );
}

/*
 * -------------------------------------------------------------------------
 * Header mega menu
 * -------------------------------------------------------------------------
 */

/**
 * Primary menu walker: prints the markup of partials/header.php.
 *
 * Menu shape and output:
 *
 * - Top item with children → `<li class="hd-has">` with a
 *   `<button class="hd-link" aria-controls="mm-{slug}">` and a panel
 *   `<div class="mm" id="mm-{slug}" hidden>`. {slug} is the item's import
 *   id (features, solutions, resources), else the slug of its label.
 *   - Wide panel (some child has children of its own, like Features):
 *     `.mm.mm-wide > .mm-cols`, one `.mm-col` with a `p.mm-h` heading per
 *     group, the group's children as `a.mm-item`, then the `.mm-promo`
 *     aside built from Site_Settings.
 *   - List panel (Solutions, Resources): `.mm > .mm-col` with the children
 *     as `a.mm-item`.
 * - Top item without children → `<li><a class="hd-link" href>`.
 * - `a.mm-item` holds the SCF icon (`span.mm-ic`, omitted without an icon),
 *   the label in `<b>` and the description in `<small>`.
 * - `is-active` is added to a top item's `.hd-link` when rx_page_key()
 *   equals the own key (rx_menu_item_own_key()) of the item or of any of
 *   its descendants.
 *
 * No WordPress menu classes or ids are printed. Items deeper than the
 * layout shows (below a group's links) are skipped.
 */
class Rx_Mega_Walker extends Walker_Nav_Menu {

	/**
	 * Layout of the top item being printed: 'wide', 'list' or '' (no panel).
	 *
	 * @var string
	 */
	private string $panel = '';

	/**
	 * Panel id of the top item being printed, e.g. 'mm-features'.
	 *
	 * @var string
	 */
	private string $panel_id = '';

	/**
	 * Whether the top item being printed is active.
	 *
	 * @var bool
	 */
	private bool $active = false;

	/**
	 * Panel ids printed so far, to keep them unique in the document.
	 *
	 * @var array<string, true>
	 */
	private array $panel_ids = array();

	/**
	 * Page key of the current request, read once.
	 *
	 * @var string|null
	 */
	private ?string $page_key = null;

	/**
	 * Works out the layout and active state of each top item, drops items
	 * deeper than the layout shows, then displays the element.
	 *
	 * @param object $element           Menu item.
	 * @param array  $children_elements Children by parent ID (by reference).
	 * @param int    $max_depth         Max depth to traverse (0 = all).
	 * @param int    $depth             Depth of the element.
	 * @param array  $args              Walker arguments.
	 * @param string $output            Output (by reference).
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element ) {
			return;
		}

		$max_depth = (int) $max_depth;
		$depth     = (int) $depth;
		$id        = $element->{$this->db_fields['id']};

		if ( 0 === $depth ) {
			$children = $children_elements[ $id ] ?? array();
			$can_open = $children && ( 0 === $max_depth || $max_depth > 1 );
			$wide     = false;
			if ( $can_open && ( 0 === $max_depth || $max_depth > 2 ) ) {
				foreach ( $children as $child ) {
					if ( ! empty( $children_elements[ $child->{$this->db_fields['id']} ] ) ) {
						$wide = true;
						break;
					}
				}
			}

			$this->panel    = $can_open ? ( $wide ? 'wide' : 'list' ) : '';
			$this->panel_id = $can_open ? $this->unique_panel_id( $element ) : '';
			$this->active   = in_array( $this->page_key(), $this->keys( $element, $children_elements ), true );
		}

		// A list panel shows one level below the top item, a wide panel two.
		if ( $depth >= ( 'wide' === $this->panel ? 2 : 1 ) ) {
			$this->drop_descendants( $id, $children_elements );
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Opens a panel (below a top item). Deeper levels print nothing: a
	 * group's column is opened by the group item itself.
	 *
	 * @param string        $output Output (by reference).
	 * @param int           $depth  Depth of the parent item.
	 * @param stdClass|null $args   wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 !== (int) $depth || '' === $this->panel ) {
			return;
		}
		$id = esc_attr( $this->panel_id );
		if ( 'wide' === $this->panel ) {
			$output .= "\n" . '<div class="mm mm-wide" id="' . $id . '" hidden>' . "\n" . '<div class="mm-cols">' . "\n";
		} else {
			$output .= "\n" . '<div class="mm" id="' . $id . '" hidden>' . "\n" . '<div class="mm-col">';
		}
	}

	/**
	 * Closes a panel; a wide panel ends with the promo aside.
	 *
	 * @param string        $output Output (by reference).
	 * @param int           $depth  Depth of the parent item.
	 * @param stdClass|null $args   wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 !== (int) $depth || '' === $this->panel ) {
			return;
		}
		if ( 'wide' === $this->panel ) {
			$output .= $this->promo() . "</div>\n</div>\n";
		} else {
			$output .= "</div>\n</div>\n";
		}
	}

	/**
	 * Opens an item: a top-level button or link, a group column, or a
	 * panel link.
	 *
	 * @param string        $output            Output (by reference).
	 * @param WP_Post       $data_object       Menu item.
	 * @param int           $depth             Depth of the item.
	 * @param stdClass|null $args              wp_nav_menu() arguments.
	 * @param int           $current_object_id ID of the current item.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item  = $data_object;
		$depth = (int) $depth;
		$label = esc_html( rx_menu_item_label( $item ) );

		if ( 0 === $depth ) {
			$class = 'hd-link' . ( $this->active ? ' is-active' : '' );
			if ( '' !== $this->panel ) {
				$output .= '<li class="hd-has">' . "\n"
					. '<button class="' . $class . '" type="button" aria-expanded="false" aria-controls="' . esc_attr( $this->panel_id ) . '">'
					. $label . '<svg class="icon chev" aria-hidden="true"><use href="#i-chev"/></svg></button>';
			} else {
				$output .= '<li><a class="' . $class . '" href="' . esc_url( rx_menu_item_href( $item ) ) . '"' . $this->link_attributes( $item ) . '>' . $label . '</a>';
			}
			return;
		}

		if ( 'wide' === $this->panel && 1 === $depth ) {
			// A group opens a column with its heading; a link placed straight
			// in a wide panel gets a column of its own, without a heading.
			$output .= '<div class="mm-col">' . "\n"
				. ( $this->has_children ? '<p class="mm-h">' . $label . '</p>' . "\n" : $this->item_link( $item ) );
			return;
		}

		$output .= $this->item_link( $item );
	}

	/**
	 * Closes an item.
	 *
	 * @param string        $output      Output (by reference).
	 * @param WP_Post       $data_object Menu item.
	 * @param int           $depth       Depth of the item.
	 * @param stdClass|null $args        wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$depth = (int) $depth;
		if ( 0 === $depth ) {
			$output .= "</li>\n";
		} elseif ( 'wide' === $this->panel && 1 === $depth ) {
			$output .= "\n</div>\n";
		}
	}

	/**
	 * Panel link: `a.mm-item` with icon, label and description.
	 *
	 * @param object $item Menu item.
	 */
	private function item_link( $item ): string {
		$icon        = rx_menu_item_icon( $item );
		$description = rx_text( rx_menu_item_description( $item ) );

		return '<a class="mm-item" href="' . esc_url( rx_menu_item_href( $item ) ) . '"' . $this->link_attributes( $item ) . '>'
			. ( '' !== $icon ? '<span class="mm-ic"><svg class="icon" aria-hidden="true"><use href="#' . esc_attr( $icon ) . '"/></svg></span>' : '' )
			. '<span><b>' . esc_html( rx_menu_item_label( $item ) ) . '</b>'
			. ( '' !== $description ? '<small>' . $description . '</small>' : '' )
			. '</span></a>';
	}

	/**
	 * Optional link attributes an editor can set on a menu item (title
	 * attribute, target, rel). Nothing is printed when they are empty, as
	 * for every imported item.
	 *
	 * @param object $item Menu item.
	 */
	private function link_attributes( $item ): string {
		return rx_menu_item_link_attributes( $item );
	}

	/**
	 * The `.mm-promo` aside of a wide panel, from Site_Settings: the image
	 * (assets/img/{promo_image}.jpg, printed like today's header), the title
	 * and text, and the link to the Features page. Each piece is skipped
	 * when its setting is empty; nothing is printed when all are.
	 */
	private function promo(): string {
		$image = rx_setting( 'promo_image' );
		$alt   = rx_setting( 'promo_alt' );
		$title = rx_text( rx_setting( 'promo_title' ) );
		$text  = rx_text( rx_setting( 'promo_text' ) );
		$link  = rx_text( rx_setting( 'promo_link_label' ) );

		$parts = array();
		if ( is_string( $image ) && preg_match( '/^[a-z0-9]+(?:[-_@.][a-z0-9]+)*$/i', $image ) ) {
			$parts[] = '<img src="' . esc_url( rx_asset( 'img/' . $image . '.jpg' ) ) . '" alt="' . esc_attr( is_string( $alt ) ? do_shortcode( $alt ) : '' ) . '" loading="lazy" width="640" height="400">';
		}
		if ( '' !== $title || '' !== $text ) {
			$parts[] = '<p>' . ( '' !== $title ? '<b>' . $title . '</b>' : '' ) . $text . '</p>';
		}
		if ( '' !== $link ) {
			$parts[] = '<a class="link-arrow" href="' . esc_url( rx_url( 'features' ) ) . '">' . $link . ' <svg class="icon" aria-hidden="true"><use href="#i-arrow"/></svg></a>';
		}

		return $parts ? '<aside class="mm-promo">' . "\n" . implode( "\n", $parts ) . "\n" . '</aside>' . "\n" : '';
	}

	/**
	 * Unique panel id for a top item: 'mm-' + its import id, else the slug
	 * of its label, else 'item-{ID}'. A repeated id gets '-2', '-3', ….
	 *
	 * @param object $item Top menu item.
	 */
	private function unique_panel_id( $item ): string {
		$slug = rx_menu_item_id( $item );
		if ( '' === $slug ) {
			$slug = sanitize_title( rx_menu_item_label( $item ) );
		}
		if ( '' === $slug ) {
			$slug = 'item-' . rx_menu_item_db_id( $item );
		}

		$id = 'mm-' . $slug;
		for ( $n = 2; isset( $this->panel_ids[ $id ] ); $n++ ) {
			$id = 'mm-' . $slug . '-' . $n;
		}
		$this->panel_ids[ $id ] = true;
		return $id;
	}

	/**
	 * Own keys of an item and all of its descendants.
	 *
	 * @param object $item              Menu item.
	 * @param array  $children_elements Children by parent ID.
	 * @return string[]
	 */
	private function keys( $item, array $children_elements ): array {
		$keys  = array();
		$stack = array( $item );
		while ( $stack ) {
			$current = array_pop( $stack );
			$key     = rx_menu_item_own_key( $current );
			if ( '' !== $key ) {
				$keys[] = $key;
			}
			foreach ( $children_elements[ $current->{$this->db_fields['id']} ] ?? array() as $child ) {
				$stack[] = $child;
			}
		}
		return $keys;
	}

	/**
	 * Removes all descendants of an item from the children list, so they
	 * are neither displayed nor printed later as orphans.
	 *
	 * @param int|string $id                Item ID.
	 * @param array      $children_elements Children by parent ID (by reference).
	 */
	private function drop_descendants( $id, array &$children_elements ): void {
		$stack = array( $id );
		while ( $stack ) {
			$current = array_pop( $stack );
			foreach ( $children_elements[ $current ] ?? array() as $child ) {
				$stack[] = $child->{$this->db_fields['id']};
			}
			unset( $children_elements[ $current ] );
		}
	}

	/**
	 * Page key of the current request (rx_page_key()), read once per walker.
	 */
	private function page_key(): string {
		if ( null === $this->page_key ) {
			$this->page_key = rx_page_key();
		}
		return $this->page_key;
	}
}

/*
 * -------------------------------------------------------------------------
 * Footer menus
 * -------------------------------------------------------------------------
 *
 * Markup of partials/footer.php. footer.php prints, per column:
 *
 *     <nav aria-labelledby="ft-h-{id}"><h3 id="ft-h-{id}">{menu name}</h3><ul class="links"><li><a href="…">Label</a></li>…</ul></nav>
 *
 * and in the bottom row:
 *
 *     <nav aria-label="Legal"><a href="…">Privacy</a><a href="…">Terms</a>…</nav>
 *
 * with {id} = features, solutions, resources, company for the locations
 * footer_features, footer_solutions, footer_resources, footer_company:
 *
 *     $rx_links = rx_footer_menu( 'footer_features' );   // '' → skip the column
 *     echo esc_html( rx_menu_name( 'footer_features' ) ); // heading
 *     $rx_legal = rx_legal_links();                       // '' → skip the nav
 */

/**
 * Footer walker: one `<li><a href>label</a></li>` per item (or a bare
 * `<a href>label</a>` in bare mode, for the legal links), with no
 * whitespace between items and no WordPress menu classes or ids, as
 * partials/footer.php prints them.
 *
 * - Footer columns are flat lists: an item nested in the admin is listed
 *   after its parent, in menu order.
 * - Links go through rx_menu_item_href(), so `#rx:{key}` items resolve to
 *   a Site_Settings URL or rx_url( $key ).
 * - An item whose link or label is empty (for example an empty help_url
 *   setting) is not printed, as the PHP_Site hides elements whose setting
 *   is empty.
 * - Optional title, target and rel attributes set in the admin are printed
 *   as in the header (rx_menu_item_link_attributes()).
 */
class Rx_Footer_Walker extends Walker_Nav_Menu {

	/**
	 * Whether each link is wrapped in `<li>` (footer columns) or printed bare
	 * (legal links).
	 *
	 * @var bool
	 */
	private bool $list;

	/**
	 * @param bool $list True for `<li><a>` items, false for bare `<a>` links.
	 */
	public function __construct( bool $list = true ) {
		$this->list = $list;
	}

	/**
	 * Nested levels print no wrapper: the list stays flat.
	 *
	 * @param string        $output Output (by reference).
	 * @param int           $depth  Depth of the parent item.
	 * @param stdClass|null $args   wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Nested levels print no wrapper: the list stays flat.
	 *
	 * @param string        $output Output (by reference).
	 * @param int           $depth  Depth of the parent item.
	 * @param stdClass|null $args   wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Prints the whole item, so nested items follow as siblings.
	 *
	 * @param string        $output            Output (by reference).
	 * @param WP_Post       $data_object       Menu item.
	 * @param int           $depth             Depth of the item.
	 * @param stdClass|null $args              wp_nav_menu() arguments.
	 * @param int           $current_object_id ID of the current item.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$href  = esc_url( rx_menu_item_href( $data_object ) );
		$label = rx_menu_item_label( $data_object );
		if ( '' === $href || '' === $label ) {
			return;
		}

		$link    = '<a href="' . $href . '"' . rx_menu_item_link_attributes( $data_object ) . '>' . esc_html( $label ) . '</a>';
		$output .= $this->list ? '<li>' . $link . '</li>' : $link;
	}

	/**
	 * Nothing to close: start_el() printed the whole item.
	 *
	 * @param string        $output      Output (by reference).
	 * @param WP_Post       $data_object Menu item.
	 * @param int           $depth       Depth of the item.
	 * @param stdClass|null $args        wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}
}

/**
 * Name of the menu assigned to a location, as plain text (entities
 * decoded; escape on output). Used as the footer column heading, so an
 * editor renames a column by renaming its menu. '' when the location has
 * no menu.
 *
 * @param string $location Menu location, e.g. 'footer_features'.
 */
function rx_menu_name( string $location ): string {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	if ( ! is_object( $menu ) || ! isset( $menu->name ) || ! is_scalar( $menu->name ) ) {
		return '';
	}
	return trim( html_entity_decode( (string) $menu->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * A footer column's link list: `<ul class="links">` with one `<li><a>` per
 * item (Rx_Footer_Walker). '' when the location has no menu or the menu
 * has no items, so footer.php can skip the whole column.
 *
 * @param string $location Menu location, e.g. 'footer_features'.
 * @return string Escaped HTML.
 */
function rx_footer_menu( string $location ): string {
	$html = wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'items_wrap'     => '<ul class="links">%3$s</ul>',
			'depth'          => 0,
			'fallback_cb'    => false,
			'echo'           => false,
			'walker'         => new Rx_Footer_Walker(),
		)
	);
	return is_string( $html ) ? $html : '';
}

/**
 * The legal links of the footer's bottom row: bare `<a>` links with no
 * separators (Rx_Footer_Walker in bare mode), for footer.php to wrap in
 * `<nav aria-label="Legal">`. '' when the `legal` location has no menu or
 * the menu has no items.
 *
 * @return string Escaped HTML.
 */
function rx_legal_links(): string {
	$html = wp_nav_menu(
		array(
			'theme_location' => 'legal',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 0,
			'fallback_cb'    => false,
			'echo'           => false,
			'walker'         => new Rx_Footer_Walker( false ),
		)
	);
	return is_string( $html ) ? $html : '';
}

/*
 * -------------------------------------------------------------------------
 * `#rx:{key}` links in other walkers
 * -------------------------------------------------------------------------
 */

/**
 * Resolves `#rx:{key}` links for walkers that print `href` from the item
 * URL (core Walker_Nav_Menu, plugins). Without it they print an empty
 * href, because esc_url() treats `#rx` as a bad protocol. The theme's own
 * walkers resolve links themselves and don't apply this filter.
 *
 * Core sets href to the item's raw URL (wp_setup_nav_menu_item() reads the
 * `_menu_item_url` meta unescaped), so a `#rx:{key}` href is resolved here
 * with rx_menu_resolve_url(). Runs first (priority 1), so later filters see
 * the real URL; any other href is left alone. A setting key whose setting
 * is empty gives an empty href, which core then omits.
 *
 * @param array $atts Link attributes (href, title, target, rel, …).
 * @return array
 */
function rx_nav_menu_link_attributes( $atts ) {
	if ( is_array( $atts ) && isset( $atts['href'] ) && is_string( $atts['href'] ) && str_starts_with( $atts['href'], '#rx:' ) ) {
		$atts['href'] = rx_menu_resolve_url( $atts['href'] );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'rx_nav_menu_link_attributes', 1 );

/*
 * -------------------------------------------------------------------------
 * Admin: keep `#rx:` links when menus are edited
 * -------------------------------------------------------------------------
 *
 * WordPress blanks `#rx:` links in two places on the menu screens:
 * - Appearance → Menus prints each custom link's URL field with esc_url(),
 *   so the field shows empty, and saving the menu then stores '' (and
 *   wp_update_nav_menu_item() also stores sanitize_url( $url )).
 * - The Customizer's menu item setting runs sanitize_url() and rejects an
 *   empty result as an invalid URL.
 *
 * Both compare the part before the first ':' (lower-cased) with
 * wp_allowed_protocols(), so the guard adds the "scheme" '#rx' to that
 * list, but only on requests that edit menus:
 * - wp-admin/nav-menus.php (the screen, and its save);
 * - admin-ajax.php with action=add-menu-item (adding an item);
 * - Customizer requests (the screen, its preview and its save), detected
 *   by the Customizer manager that core loads for those requests only.
 *
 * Timing: wp_allowed_protocols() caches its list and applies the
 * kses_allowed_protocols filter only until wp_loaded. A filter added later
 * (for example on admin_init or inside a save handler) has no effect, so
 * the guard registers on init and calls wp_allowed_protocols() itself to
 * put '#rx' into the cached list before wp_loaded.
 *
 * Trade-off: on those requests every esc_url()/kses check accepts a
 * `#rx:…` value, not only menu URLs. That is harmless for browsers: a URL
 * scheme must start with a letter, so `#rx:…` is always a same-page
 * fragment link, and real bad protocols (javascript:, data:, …) are still
 * blanked. Requests outside the menu screens, including every front-end
 * page, keep WordPress' default list. The REST menu-items endpoint (block
 * navigation) is not covered; this classic theme edits menus in
 * Appearance → Menus. The import writes `#rx:` URLs itself (lib/upsert.php).
 */

/**
 * Whether the current request edits menus (see the section comment).
 */
function rx_menu_rx_scheme_in_scope(): bool {
	if ( isset( $GLOBALS['wp_customize'] ) && $GLOBALS['wp_customize'] instanceof WP_Customize_Manager ) {
		return true;
	}
	if ( ! is_admin() ) {
		return false;
	}

	$page = isset( $GLOBALS['pagenow'] ) && is_string( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	if ( 'nav-menus.php' === $page ) {
		return true;
	}
	if ( 'admin-ajax.php' !== $page ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only to decide the scope; core checks the nonce before it saves anything.
	$action = isset( $_REQUEST['action'] ) && is_string( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
	return 'add-menu-item' === $action;
}

/**
 * kses_allowed_protocols filter: adds the `#rx` scheme.
 *
 * @param string[] $protocols Allowed protocols.
 * @return string[]
 */
function rx_menu_allow_rx_scheme( $protocols ) {
	$protocols   = is_array( $protocols ) ? $protocols : array();
	$protocols[] = '#rx';
	return $protocols;
}

/**
 * On menu-editing requests, adds the `#rx` scheme and refreshes the cached
 * protocol list while it can still change (before wp_loaded).
 */
function rx_menu_rx_scheme_init(): void {
	if ( did_action( 'wp_loaded' ) || ! rx_menu_rx_scheme_in_scope() ) {
		return;
	}
	add_filter( 'kses_allowed_protocols', 'rx_menu_allow_rx_scheme' );
	wp_allowed_protocols();
}
add_action( 'init', 'rx_menu_rx_scheme_init', PHP_INT_MAX );
