<?php
/**
 * Theme functions for World of WordPress.
 *
 * @package WorldOfWordPress
 */

/**
 * Register theme-specific block pattern categories.
 */
function world_of_wordpress_register_pattern_categories() {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category(
			'world',
			array(
				'label'       => __( 'World of WordPress', 'world-of-wordpress' ),
				'description' => __( 'Patterns that make the WordPress terrarium easier to inspect, navigate, and inhabit.', 'world-of-wordpress' ),
			)
		);
	}
}
add_action( 'init', 'world_of_wordpress_register_pattern_categories' );
