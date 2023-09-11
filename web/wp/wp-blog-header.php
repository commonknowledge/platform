<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

if ( ! isset( $wp_did_header ) ) {

	$wp_did_header = true;

	// Load the WordPress library.
	require_once __DIR__ . '/wp-load.php';

	print_execution_time("WP load");

	// Set up the WordPress query.
	wp();

	print_execution_time("WP query");

	// Load the theme template.
	require_once ABSPATH . WPINC . '/template-loader.php';

}
