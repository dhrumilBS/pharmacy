<?php
/**
 * Article body: renders a post's `body` flexible content, one row per
 * block, with the markup of the PHP site's article components
 * (bk_*() in partials/blog.php) and the plain HTML between them.
 *
 * $args:
 * - rows array The `body` value: rx_field( 'body' ) / get_field( 'body' ),
 *              a list of rows, each with 'acf_fc_layout' and its sub-fields.
 *              Anything else (false, null, '') prints nothing.
 *
 * Echoes the article HTML, one block per line. Rows with an unknown layout,
 * and blocks whose content renders empty, print nothing.
 *
 * Headings come out as plain <h2>…</h2>, without an id: single.php buffers
 * this part and passes the whole article through rx_blog_toc(), which adds
 * the ids and builds the "On this page" list, as blog_end() did:
 *
 *   ob_start();
 *   get_template_part( 'parts/blog-layouts', null, array( 'rows' => rx_field( 'body' ) ) );
 *   [ $rx_body, $rx_toc ] = rx_blog_toc( (string) ob_get_clean() );
 *
 * Layouts and the component each one reproduces:
 *
 *   text       html                                           paragraphs and lists as written
 *   heading    text                                           <h2>
 *   takeaways  items [html]                                   bk_takeaways( items )
 *   note       kind, title, html                              bk_note( kind, title, html )
 *   table      head, rows [cells], caption, highlight_col,    bk_table( head, rows, caption,
 *              numeric_cols                                     [ 'hl' => …, 'num' => [ … ] ] )
 *   compare    title, a_name, a_tag, b_name, b_tag, winner,   bk_compare( spec )
 *              rows [label, a_html, b_html], verdict_html
 *   sum        title, lines [label, value, op],               bk_sum( title, lines, result, note )
 *              result_label, result_value, note
 *   rule       html, label                                    bk_rule( html, label )
 *   checklist  title, items [html]                            bk_checklist( title, items )
 *   steps      items [title, html, when]                      bk_steps( items )
 *   cta        kind, title, text, link_label, link_key,       bk_cta( kind, … )
 *              feature_key
 *
 * Field rules:
 * - Fields ending in `html` (and table cells) are HTML: rx_html(), so
 *   shortcodes run and the markup is filtered with wp_kses. Every other
 *   field is plain text: rx_text() (shortcodes, then escaped).
 * - table `head` holds one column heading per line, starting with the
 *   heading above the row labels; each row's `cells` holds one cell per
 *   line, starting with the row label (printed as <th scope="row">).
 *   Trailing blank lines are ignored; a blank line in between is an empty
 *   cell. `highlight_col` and `numeric_cols` count columns the way bk_table()
 *   does: 0 is the label column, 1 the first column after it.
 * - sum `op`: 'auto' (default) prints × before every line but the first,
 *   as bk_sum() does when no operator is given; 'none' prints no operator;
 *   ×, +, −, ÷ or = print that sign.
 * - cta `kind`: 'inline' (text + link_label → rx_url( link_key )),
 *   'feature' (text + a button to feature_key: label, icon and URL from the
 *   Features menu, rx_feature_nav()), anything else is the demo 'panel'
 *   (title + text, "Book a free demo" + "Start free").
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

$rx_bl_args = isset( $args ) && is_array( $args ) ? $args : array();
$rx_bl_rows = isset( $rx_bl_args['rows'] ) && is_array( $rx_bl_args['rows'] ) ? $rx_bl_args['rows'] : array();

// The renderers live in inc/blog-components.php (shared with the Gutenberg blocks).
foreach ( $rx_bl_rows as $rx_bl_row ) {
	if ( ! is_array( $rx_bl_row ) ) {
		continue;
	}
	$rx_bl_layout = is_scalar( $rx_bl_row['acf_fc_layout'] ?? null ) ? (string) $rx_bl_row['acf_fc_layout'] : '';
	$rx_bl_html   = rx_blog_render_component( $rx_bl_layout, $rx_bl_row );
	if ( '' !== $rx_bl_html ) {
		echo $rx_bl_html, "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where built.
	}
}
