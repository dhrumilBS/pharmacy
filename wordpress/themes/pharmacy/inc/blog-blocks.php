<?php
/**
 * Guides written in the block editor: components as blocks, no custom fields for content.
 *
 * A new guide is an ordinary post. The writer uses core blocks (paragraph, heading, list, table, image,
 * quote) and the "Healthray blog" blocks below for the parts that are components. Nothing is typed into
 * custom fields: the standfirst, topic, cover and updated date are in the "Guide settings" panel of the
 * post sidebar (plain post meta), and the read time is counted from the body.
 *
 * Blocks (category "Healthray blog", editor side in assets/js/blog-editor.js):
 *
 *   healthray/takeaways     In short. The first one on a guide is shown in the hero as "The short version".
 *   healthray/note          Tip, Note, Watch out, Example.
 *   healthray/rule          Rule of thumb, set large.
 *   healthray/checklist     Printable tick list.
 *   healthray/steps         (+ healthray/step)         Numbered steps with an optional "when" line.
 *   healthray/compare       (+ healthray/compare-row)  Two options side by side, optional winner and verdict.
 *   healthray/sum           (+ healthray/sum-line)     A worked sum, written out.
 *   healthray/cta           Inline link, "How the software handles this" feature card, or the demo panel.
 *   healthray/faq           (+ healthray/faq-item)     Questions and answers, with FAQPage data.
 *   core/table              Block styles "Plain table …": plain, numbers, and a highlighted column.
 *
 * All of them are dynamic blocks: the saved post holds only the content, and the markup comes from
 * rx_blog_components() (inc/blog-components.php), the same renderers the six imported guides use. Change
 * a component's look or markup there (and in assets/css/blog.css) and every guide follows.
 *
 * Guides written the old way (an SCF `body` field) keep working: rx_blog_is_legacy().
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------------------------
 * Which guides use the block editor
 * ------------------------------------------------------------------------------------------- */

/**
 * True for the guides written before blocks (content in the SCF `body` field) that have no editor content yet.
 *
 * @param int|WP_Post|null $post Post.
 */
function rx_blog_is_legacy( $post = null ): bool {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	$body = get_post_meta( $post->ID, 'body', true );
	// Content written in the editor wins: once the post has any, it is a block guide.
	return ! empty( $body ) && '' === trim( $post->post_content );
}

/* ---------------------------------------------------------------------------------------------
 * Guide settings: plain post meta (also what the SCF fields of the old guides store)
 * ------------------------------------------------------------------------------------------- */

/**
 * Registers the guide's meta so the editor panel can read and write it.
 */
function rx_blog_register_meta(): void {
	$auth = static function (): bool {
		return current_user_can( 'edit_posts' );
	};
	foreach ( array( 'topic', 'dek', 'updated', 'cover_screen' ) as $key ) {
		register_post_meta(
			'post',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
	}
	register_post_meta(
		'post',
		'featured',
		array(
			'type'          => 'boolean',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => $auth,
		)
	);
}
add_action( 'init', 'rx_blog_register_meta' );

/**
 * The old SCF box (dek, topic, cover, body rows…) only shows on the guides that use it, so a new guide
 * has one place for its settings (the sidebar panel) and the two never write the same meta.
 */
function rx_blog_hide_scf_box(): void {
	global $post;
	if ( $post instanceof WP_Post && ! rx_blog_is_legacy( $post ) ) {
		remove_meta_box( 'acf-group_rx_post', 'post', 'normal' );
		remove_meta_box( 'acf-group_rx_post', 'post', 'side' );
		remove_meta_box( 'acf-group_rx_post', 'post', 'advanced' );
	}
}
add_action( 'add_meta_boxes_post', 'rx_blog_hide_scf_box', 99 );

/* ---------------------------------------------------------------------------------------------
 * Read time, counted from the body
 * ------------------------------------------------------------------------------------------- */

/** Reading speed used for the read time. */
const RX_BLOG_WPM = 200;

/**
 * Minutes to read a guide, counted from its body (never typed in).
 *
 * The count is kept in the `_rx_read_mins` meta so lists of guides (cards, home) do not render every
 * body: it is made when a block guide is saved, and on first use for the older guides (whose body is the
 * SCF `body` field). The guide's own page recounts and corrects it, so the two always agree.
 *
 * @param int|WP_Post|null $post Post.
 */
function rx_blog_read_mins( $post = null ): int {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return 0;
	}
	$cached = (int) get_post_meta( $post->ID, '_rx_read_mins', true );
	if ( $cached > 0 ) {
		return $cached;
	}
	if ( rx_blog_is_legacy( $post ) ) {
		ob_start();
		get_template_part( 'parts/blog-layouts', null, array( 'rows' => rx_field( 'body', (int) $post->ID ) ) );
		$mins = max( 1, (int) ceil( rx_blog_words( (string) ob_get_clean() ) / RX_BLOG_WPM ) );
	} else {
		$mins = rx_blog_count_mins( (string) $post->post_content );
	}
	update_post_meta( $post->ID, '_rx_read_mins', $mins );
	return $mins;
}
/**
 * Minutes for some block content.
 *
 * @param string $content Post content (block markup).
 */
function rx_blog_count_mins( string $content ): int {
	return max( 1, (int) ceil( rx_blog_words( do_blocks( $content ) ) / RX_BLOG_WPM ) );
}

/**
 * Number of words in some HTML (tags are ignored).
 *
 * @param string $html HTML.
 */
function rx_blog_words( string $html ): int {
	return (int) preg_match_all( '/[\p{L}\p{N}][\p{L}\p{N}\'’.,%₹\/-]*/u', wp_strip_all_tags( $html ) );
}

/**
 * Keeps the read time of a block guide up to date when it is saved.
 *
 * @param int     $post_id Post id.
 * @param WP_Post $post    Post.
 */
function rx_blog_save_read_mins( int $post_id, WP_Post $post ): void {
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( rx_blog_is_legacy( $post ) ) {
		delete_post_meta( $post_id, '_rx_read_mins' ); // Recounted from the body on next use.
		return;
	}
	update_post_meta( $post_id, '_rx_read_mins', rx_blog_count_mins( (string) $post->post_content ) );
}
add_action( 'save_post_post', 'rx_blog_save_read_mins', 20, 2 );

/* ---------------------------------------------------------------------------------------------
 * Blocks
 * ------------------------------------------------------------------------------------------- */

/**
 * Adds the "Healthray blog" block category.
 *
 * @param array $categories Categories.
 * @return array
 */
function rx_blog_block_category( $categories ): array {
	$categories   = is_array( $categories ) ? $categories : array();
	$categories[] = array(
		'slug'  => 'healthray-guide',
		'title' => __( 'Healthray blog', 'pharmacy' ),
	);
	return $categories;
}
add_filter( 'block_categories_all', 'rx_blog_block_category' );

/**
 * The text inside every <li> of some HTML, in order.
 *
 * @param string $html Rendered inner blocks (a core/list).
 * @return string[]
 */
function rx_bb_items( string $html ): array {
	preg_match_all( '~<li\b[^>]*>(.*?)</li>~s', $html, $m );
	return array_values( array_filter( array_map( 'trim', $m[1] ?? array() ), static fn( $t ) => '' !== trim( wp_strip_all_tags( $t ) ) ) );
}

/**
 * The inside of the first paragraph of some HTML.
 *
 * @param string $html Rendered inner blocks.
 */
function rx_bb_paragraph( string $html ): string {
	return preg_match( '~<p\b[^>]*>(.*?)</p>~s', $html, $m ) ? trim( $m[1] ) : trim( $html );
}

/**
 * Plain text from an attribute that the editor stores as rich text.
 *
 * @param mixed $value Attribute value.
 */
function rx_bb_plain( $value ): string {
	return is_string( $value ) ? trim( wp_strip_all_tags( $value ) ) : '';
}

/**
 * Child blocks of a block as [ attributes, rendered inner HTML ].
 *
 * @param WP_Block $block Parent block.
 * @param string   $name  Child block name.
 * @return array<int, array{0: array, 1: string}>
 */
function rx_bb_children( WP_Block $block, string $name ): array {
	$out = array();
	foreach ( $block->parsed_block['innerBlocks'] ?? array() as $child ) {
		if ( ( $child['blockName'] ?? '' ) !== $name ) {
			continue;
		}
		$inner = '';
		foreach ( $child['innerBlocks'] ?? array() as $grand ) {
			$inner .= render_block( $grand );
		}
		$out[] = array( (array) ( $child['attrs'] ?? array() ), $inner );
	}
	return $out;
}

/**
 * Registers the blocks (server side: attributes and the markup they print).
 */
function rx_blog_register_blocks(): void {
	$str = array( 'type' => 'string' );
	$def = static function ( string $default ): array {
		return array(
			'type'    => 'string',
			'default' => $default,
		);
	};

	$blocks = array(
		'healthray/takeaways'    => array(
			'attributes' => array(),
			'render'     => static function ( array $a, string $content ): string {
				$items = array();
				foreach ( rx_bb_items( $content ) as $html ) {
					$items[] = array( 'html' => $html );
				}
				return rx_blog_render_component( 'takeaways', array( 'items' => $items ) );
			},
		),
		'healthray/note'         => array(
			'attributes' => array(
				'kind'  => $def( 'tip' ),
				'title' => $str,
			),
			'render'     => static function ( array $a, string $content ): string {
				return rx_blog_render_component(
					'note',
					array(
						'kind'  => $a['kind'] ?? 'tip',
						'title' => rx_bb_plain( $a['title'] ?? '' ),
						'html'  => $content,
					)
				);
			},
		),
		'healthray/rule'         => array(
			'attributes' => array( 'label' => $str ),
			'render'     => static function ( array $a, string $content ): string {
				return rx_blog_render_component(
					'rule',
					array(
						'label' => rx_bb_plain( $a['label'] ?? '' ),
						'html'  => rx_bb_paragraph( $content ),
					)
				);
			},
		),
		'healthray/checklist'    => array(
			'attributes' => array( 'title' => $str ),
			'render'     => static function ( array $a, string $content ): string {
				$items = array();
				foreach ( rx_bb_items( $content ) as $html ) {
					$items[] = array( 'html' => $html );
				}
				return rx_blog_render_component(
					'checklist',
					array(
						'title' => rx_bb_plain( $a['title'] ?? '' ),
						'items' => $items,
					)
				);
			},
		),
		'healthray/steps'        => array(
			'attributes' => array(),
			'render'     => static function ( array $a, string $content, WP_Block $block ): string {
				$items = array();
				foreach ( rx_bb_children( $block, 'healthray/step' ) as list( $at, $inner ) ) {
					$items[] = array(
						'title' => rx_bb_plain( $at['title'] ?? '' ),
						'when'  => rx_bb_plain( $at['when'] ?? '' ),
						'html'  => $inner,
					);
				}
				return rx_blog_render_component( 'steps', array( 'items' => $items ) );
			},
		),
		'healthray/step'         => array(
			'attributes' => array(
				'title' => $str,
				'when'  => $str,
			),
			'render'     => static function (): string {
				return '';
			},
		),
		'healthray/compare'      => array(
			'attributes' => array(
				'title'   => $str,
				'aName'   => $str,
				'aTag'    => $str,
				'bName'   => $str,
				'bTag'    => $str,
				'winner'  => $str,
				'verdict' => $str,
			),
			'render'     => static function ( array $a, string $content, WP_Block $block ): string {
				$rows = array();
				foreach ( rx_bb_children( $block, 'healthray/compare-row' ) as list( $at ) ) {
					$rows[] = array(
						'label'  => rx_bb_plain( $at['label'] ?? '' ),
						'a_html' => (string) ( $at['a'] ?? '' ),
						'b_html' => (string) ( $at['b'] ?? '' ),
					);
				}
				return rx_blog_render_component(
					'compare',
					array(
						'title'        => rx_bb_plain( $a['title'] ?? '' ),
						'a_name'       => rx_bb_plain( $a['aName'] ?? '' ),
						'a_tag'        => rx_bb_plain( $a['aTag'] ?? '' ),
						'b_name'       => rx_bb_plain( $a['bName'] ?? '' ),
						'b_tag'        => rx_bb_plain( $a['bTag'] ?? '' ),
						'winner'       => in_array( $a['winner'] ?? '', array( 'a', 'b' ), true ) ? $a['winner'] : '',
						'verdict_html' => (string) ( $a['verdict'] ?? '' ),
						'rows'         => $rows,
					)
				);
			},
		),
		'healthray/compare-row'  => array(
			'attributes' => array(
				'label' => $str,
				'a'     => $str,
				'b'     => $str,
			),
			'render'     => static function (): string {
				return '';
			},
		),
		'healthray/sum'          => array(
			'attributes' => array(
				'title'       => $str,
				'resultLabel' => $str,
				'resultValue' => $str,
				'note'        => $str,
			),
			'render'     => static function ( array $a, string $content, WP_Block $block ): string {
				$lines = array();
				foreach ( rx_bb_children( $block, 'healthray/sum-line' ) as list( $at ) ) {
					$lines[] = array(
						'label' => rx_bb_plain( $at['label'] ?? '' ),
						'value' => rx_bb_plain( $at['value'] ?? '' ),
						'op'    => (string) ( $at['op'] ?? 'auto' ),
					);
				}
				return rx_blog_render_component(
					'sum',
					array(
						'title'        => rx_bb_plain( $a['title'] ?? '' ),
						'result_label' => rx_bb_plain( $a['resultLabel'] ?? '' ),
						'result_value' => rx_bb_plain( $a['resultValue'] ?? '' ),
						'note'         => rx_bb_plain( $a['note'] ?? '' ),
						'lines'        => $lines,
					)
				);
			},
		),
		'healthray/sum-line'     => array(
			'attributes' => array(
				'label' => $str,
				'value' => $str,
				'op'    => $def( 'auto' ),
			),
			'render'     => static function (): string {
				return '';
			},
		),
		'healthray/cta'          => array(
			'attributes' => array(
				'kind'       => $def( 'inline' ),
				'title'      => $str,
				'text'       => $str,
				'linkLabel'  => $str,
				'linkKey'    => $str,
				'featureKey' => $str,
			),
			'render'     => static function ( array $a ): string {
				return rx_blog_render_component(
					'cta',
					array(
						'kind'        => $a['kind'] ?? 'inline',
						'title'       => rx_bb_plain( $a['title'] ?? '' ),
						'text'        => rx_bb_plain( $a['text'] ?? '' ),
						'link_label'  => rx_bb_plain( $a['linkLabel'] ?? '' ),
						'link_key'    => (string) ( $a['linkKey'] ?? '' ),
						'feature_key' => (string) ( $a['featureKey'] ?? '' ),
					)
				);
			},
		),
		'healthray/faq'          => array(
			'attributes' => array( 'heading' => $def( 'Asked about this' ) ),
			'render'     => 'rx_blog_render_faq',
		),
		'healthray/faq-item'     => array(
			'attributes' => array( 'question' => $str ),
			'render'     => static function (): string {
				return '';
			},
		),
	);

	foreach ( $blocks as $name => $def_block ) {
		register_block_type(
			$name,
			array(
				'api_version'     => 3,
				'category'        => 'healthray-guide',
				'attributes'      => $def_block['attributes'],
				'render_callback' => $def_block['render'],
				'editor_script'   => 'rx-blog-editor',
			)
		);
	}
}
add_action( 'init', 'rx_blog_register_blocks' );

/**
 * The FAQ block: questions as native <details>, plus the FAQPage data for search engines.
 *
 * @param array    $a       Attributes.
 * @param string   $content Rendered inner blocks (unused).
 * @param WP_Block $block   The block.
 */
function rx_blog_render_faq( array $a, string $content, WP_Block $block ): string {
	$qa  = array();
	$out = '';
	foreach ( rx_bb_children( $block, 'healthray/faq-item' ) as list( $at, $answer ) ) {
		$q = rx_bb_plain( $at['question'] ?? '' );
		if ( '' === $q || '' === trim( wp_strip_all_tags( $answer ) ) ) {
			continue;
		}
		$qa[] = array(
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => trim( wp_strip_all_tags( $answer ) ),
			),
		);
		$out .= '<details class="bk-q"><summary>' . esc_html( $q ) . '</summary><div class="bk-q-a">' . wp_kses_post( $answer ) . '</div></details>';
	}
	if ( '' === $out ) {
		return '';
	}
	$heading = rx_bb_plain( $a['heading'] ?? '' );
	$json    = wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $qa,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
	);
	return '<section class="bk-faq">' . ( '' !== $heading ? '<h2>' . esc_html( $heading ) . '</h2>' : '' ) . $out . '</section><script type="application/ld+json">' . $json . '</script>';
}

/* ---------------------------------------------------------------------------------------------
 * Table styles
 * ------------------------------------------------------------------------------------------- */

/**
 * The core Table block gets the guide's table looks as block styles (pick one in the sidebar).
 */
function rx_blog_register_styles(): void {
	$styles = array(
		'guide'         => __( 'Plain table', 'pharmacy' ),
		'guide-num'     => __( 'Plain table, numbers right', 'pharmacy' ),
		'guide-hl1'     => __( 'Plain table, highlight column 1', 'pharmacy' ),
		'guide-hl2'     => __( 'Plain table, highlight column 2', 'pharmacy' ),
		'guide-hl-last' => __( 'Plain table, highlight last column', 'pharmacy' ),
	);
	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/table',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'rx_blog_register_styles' );

/* ---------------------------------------------------------------------------------------------
 * Editor: script, data and styles
 * ------------------------------------------------------------------------------------------- */

/**
 * Registers the editor script and styles, with the lists the blocks need (topics, screens, pages).
 */
function rx_blog_editor_assets(): void {
	wp_register_script(
		'rx-blog-editor',
		get_theme_file_uri( 'assets/js/blog-editor.js' ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data', 'wp-core-data', 'wp-plugins', 'wp-edit-post', 'wp-editor' ),
		RX_VERSION,
		true
	);

	$screens = array();
	foreach ( glob( get_theme_file_path( 'assets/img/screens/v2-*.svg' ) ) ?: array() as $file ) {
		$slug             = basename( $file, '.svg' );
		$screens[ $slug ] = ucwords( str_replace( '-', ' ', preg_replace( '/^v2-\d+-/', '', $slug ) ) );
	}

	$pages = array();
	foreach ( array_keys( rx_page_map() ) as $key ) {
		$pages[ $key ] = ucwords( str_replace( '-', ' ', $key ) );
	}

	$features = array();
	foreach ( rx_feature_nav() as $key => $item ) {
		$features[ $key ] = $item['label'];
	}

	$post = get_post();
	wp_localize_script(
		'rx-blog-editor',
		'rxBlogEditor',
		array(
			'topics'   => array_map( static fn( $t ) => $t[0], RX_TOPICS ),
			'screens'  => $screens,
			'pages'    => $pages,
			'features' => $features,
			'legacy'   => $post instanceof WP_Post && rx_blog_is_legacy( $post ),
			'wpm'      => RX_BLOG_WPM,
		)
	);

	wp_enqueue_script( 'rx-blog-editor' );
}
add_action( 'enqueue_block_editor_assets', 'rx_blog_editor_assets' );

/**
 * The editor shows the guide as it will look: the site styles plus the article components.
 */
function rx_blog_editor_styles(): void {
	add_editor_style(
		array(
			'assets/css/blog.css',
			'assets/css/blog-editor.css',
		)
	);
}
add_action( 'after_setup_theme', 'rx_blog_editor_styles', 20 );

/* ---------------------------------------------------------------------------------------------
 * Pattern: a guide skeleton to start from
 * ------------------------------------------------------------------------------------------- */

/**
 * Registers a starter pattern ("In short", a heading and the common components).
 */
function rx_blog_register_pattern(): void {
	register_block_pattern_category( 'healthray-guide', array( 'label' => __( 'Healthray blog', 'pharmacy' ) ) );
	$li = static function ( string $text ): string {
		return "<!-- wp:list-item -->\n<li>" . $text . "</li>\n<!-- /wp:list-item -->";
	};
	$p  = static function ( string $text ): string {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->";
	};
	register_block_pattern(
		'healthray/guide-starter',
		array(
			'title'      => __( 'Guide: starter', 'pharmacy' ),
			'categories' => array( 'healthray-guide' ),
			'content'    =>
				"<!-- wp:healthray/takeaways -->\n<!-- wp:list {\"ordered\":true} -->\n<ol class=\"wp-block-list\">" . $li( 'The first thing a reader should remember.' ) . $li( 'The second.' ) . $li( 'The third.' ) . "</ol>\n<!-- /wp:list -->\n<!-- /wp:healthray/takeaways -->\n\n"
				. $p( 'Open with the problem in the reader\'s own words, in two or three sentences.' ) . "\n\n"
				. "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">First section</h2>\n<!-- /wp:heading -->\n\n"
				. $p( 'Write the section here.' ) . "\n\n"
				. "<!-- wp:healthray/note {\"kind\":\"tip\"} -->\n" . $p( 'A short, practical tip.' ) . "\n<!-- /wp:healthray/note -->\n\n"
				. "<!-- wp:healthray/cta {\"kind\":\"panel\",\"title\":\"See it with your own stock\",\"text\":\"A 30-minute demo using items from your shelf.\"} /-->",
		)
	);
}
add_action( 'init', 'rx_blog_register_pattern' );

/* ---------------------------------------------------------------------------------------------
 * Featured image
 * ------------------------------------------------------------------------------------------- */

/**
 * Turns on the Featured image box for posts and adds the crops the guides use:
 * 1200x900 (4:3) beside the headline, 1200x560 (wide) under the older headers, and 640x400 for the blog hub cards.
 * A guide without a featured image still works: cards show the cover screen, the page shows no photo.
 */
function rx_blog_thumbnails(): void {
	add_theme_support( 'post-thumbnails', array( 'post' ) );
	add_image_size( 'rx-guide-hero', 1200, 560, true );
	add_image_size( 'rx-guide-card', 640, 320, true );
	add_image_size( 'rx-guide-feature', 1200, 600, true ); // The picture beside the headline (v6): 4:3.
}
add_action( 'after_setup_theme', 'rx_blog_thumbnails' );
