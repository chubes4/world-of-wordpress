<?php
/**
 * Plugin Name: World of WordPress
 * Description: A self-contained WordPress Playground terrarium where an agent evolves software and content.
 * Version: 0.1.0
 * Author: Chris Huber
 * License: GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
	// The first scaffold intentionally registers no product behavior. The world
	// starts with storage/runtime physics; the agent gets the first creative move.
} );

/**
 * Seed the visible World of WordPress state from repository content.
 *
 * MDI stays generic and non-destructive; this plugin owns the terrarium policy
 * that repo-backed content should be the visible world instead of stock install
 * samples.
 */
function world_of_wordpress_seed_world(): void {
	if ( ! function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		foreach ( array( WP_PLUGIN_DIR . '/markdown-database-integration/markdown-database-integration.php' ) as $mdi_plugin ) {
			if ( file_exists( $mdi_plugin ) ) {
				require_once $mdi_plugin;
				break;
			}
		}
	}

	if ( function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		markdown_database_integration_import_seed_posts_after_install();
	}

	foreach ( array( 'hello-world', 'sample-page', 'privacy-policy' ) as $slug ) {
		foreach ( array( 'post', 'page' ) as $post_type ) {
			$sample = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $sample ) {
				wp_delete_post( (int) $sample->ID, true );
			}
		}
	}

	$home = get_page_by_path( 'home', OBJECT, 'page' );
	if ( $home ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home->ID );
	}
}
