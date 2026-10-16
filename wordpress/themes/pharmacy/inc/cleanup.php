<?php
/**
 * Front-end cleanup: WordPress extras, jQuery and CF7 assets off.
 *
 * @package pharmacy
 */

defined( 'ABSPATH' ) || exit;

/*
 * Contact Form 7
 * --------------
 * CF7 stays active but unused: no page or field holds a CF7 form shortcode,
 * and the theme's forms are plain markup driven by site.js. CF7 enqueues its
 * script (with the swv dependency and the inline `wpcf7` config) and its
 * stylesheet on every front-end page unless these filters say no. The admin
 * screens enqueue their own assets and are not affected.
 */
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );
