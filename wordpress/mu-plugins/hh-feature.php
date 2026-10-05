<?php
/**
 * Plugin Name: H&H Feature Layout
 * Description: Posts in the Featured category get long-form article styling and a white page on a gray desk, with a Letter-size print layout.
 * Version:     1.1.1
 * Author:      Mark Aaron Harris
 *
 * Install as a must-use plugin:
 *   wp-content/mu-plugins/hh-feature.php
 *   wp-content/mu-plugins/hh-feature/hh-feature.css
 */

defined( 'ABSPATH' ) || exit;

/** Category slug that switches the layout on. */
const HH_FEATURE_CATEGORY = 'featured';

function hh_feature_is_active() {
	return is_singular( 'post' )
		&& has_category( HH_FEATURE_CATEGORY, get_queried_object_id() );
}

/** Stable, theme-independent body class for the stylesheet to hang off. */
add_filter( 'body_class', function ( $classes ) {
	if ( hh_feature_is_active() ) {
		$classes[] = 'hh-feature';
	}
	return $classes;
} );

/** Load the stylesheet only on feature posts, after the theme's own CSS. */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! hh_feature_is_active() ) {
		return;
	}
	$rel  = 'hh-feature/hh-feature.css';
	$path = __DIR__ . '/' . $rel;
	wp_enqueue_style(
		'hh-feature',
		plugins_url( $rel, __FILE__ ),
		array(),
		file_exists( $path ) ? filemtime( $path ) : '1.1.1'
	);
}, 20 );
