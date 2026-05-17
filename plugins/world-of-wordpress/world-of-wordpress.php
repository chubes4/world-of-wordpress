<?php
/**
 * Plugin Name: World of WordPress
 * Description: The plugin substrate of the World of WordPress terrarium. The World Creator grows this plugin as the world calls for new capability.
 * Version: 0.3.0
 * Author: Chris Huber
 * License: GPL v2 or later
 * Text Domain: world-of-wordpress
 */

defined( 'ABSPATH' ) || exit;

define( 'WORLD_OF_WORDPRESS_PLUGIN_FILE', __FILE__ );
define( 'WORLD_OF_WORDPRESS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR', WP_CONTENT_DIR . '/world-content' );

require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-mode.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-mode-guidance.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/class-world-perception-directive.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-pulse.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-map.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-chronicle.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-agent-handbook.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-glossary.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-seed-bank.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-field-notes.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-signal-lantern.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-weather-vane.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-ability-atlas.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-route-canopy.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-source-compass.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-archive-rings.php';

add_action( 'datamachine_memory_files', 'world_of_wordpress_register_memory_files' );

/**
 * Register WORLD.md as shared memory for every agent on this site.
 *
 * The Blueprint (public Playground) or the CI bootstrap places `WORLD.md`
 * at the world-content path; this hook tells Data Machine to inject it as
 * shared agent context.
 */
function world_of_wordpress_register_memory_files(): void {
	if ( ! class_exists( '\DataMachine\Engine\AI\MemoryFileRegistry' ) ) {
		return;
	}

	\DataMachine\Engine\AI\MemoryFileRegistry::register(
		'WORLD.md',
		18,
		array(
			'layer'       => \DataMachine\Engine\AI\MemoryFileRegistry::LAYER_SHARED,
			'protected'   => true,
			'modes'       => array( \DataMachine\Engine\AI\MemoryFileRegistry::MODE_ALL ),
			'label'       => 'World Context',
			'description' => 'Shared World of WordPress context for every agent on the site.',
		)
	);
}

/**
 * Copy WORLD.md into Data Machine shared memory.
 */
function world_of_wordpress_seed_shared_memory(): void {
	if ( ! class_exists( '\DataMachine\Core\FilesRepository\DirectoryManager' ) ) {
		return;
	}

	$source = WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR . '/WORLD.md';
	if ( ! is_readable( $source ) ) {
		return;
	}

	$directory_manager = new \DataMachine\Core\FilesRepository\DirectoryManager();
	$shared_dir        = $directory_manager->get_shared_directory();
	if ( ! $directory_manager->ensure_directory_exists( $shared_dir ) ) {
		return;
	}

	copy( $source, $shared_dir . '/WORLD.md' );
}

/**
 * Recursively copy a directory.
 */
function world_of_wordpress_copy_directory( string $source, string $destination ): void {
	if ( ! is_dir( $source ) ) {
		return;
	}

	if ( ! is_dir( $destination ) ) {
		wp_mkdir_p( $destination );
	}

	$items = scandir( $source );
	if ( false === $items ) {
		return;
	}

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}

		$source_path      = $source . '/' . $item;
		$destination_path = $destination . '/' . $item;

		if ( is_dir( $source_path ) ) {
			world_of_wordpress_copy_directory( $source_path, $destination_path );
			continue;
		}

		copy( $source_path, $destination_path );
	}
}

/**
 * Stage the repo-bundled theme, content, and WORLD.md into runtime locations.
 *
 * In public Playground the Blueprint installs the theme separately, writes
 * content to the world-content dir, and writes WORLD.md there. In the agent
 * CI runtime the entire repo is bind-mounted at the plugin path; this
 * function detects that mount and copies the bundled files into the
 * locations the rest of the world expects.
 *
 * Idempotent: if the destination already has content, files are overwritten
 * with the bundled versions. If the source isn't found, nothing happens.
 */
function world_of_wordpress_stage_world_files(): void {
	$plugin_dir       = WORLD_OF_WORDPRESS_PLUGIN_DIR;
	$repo_root_in_ci  = dirname( $plugin_dir, 2 );

	$theme_source = $repo_root_in_ci . '/themes/world-of-wordpress';
	if ( is_dir( $theme_source ) ) {
		$theme_destination = WP_CONTENT_DIR . '/themes/world-of-wordpress';
		world_of_wordpress_copy_directory( $theme_source, $theme_destination );
		if ( file_exists( $theme_destination . '/style.css' ) ) {
			wp_clean_themes_cache( false );
			switch_theme( 'world-of-wordpress' );
		}
	}

	$content_source = $repo_root_in_ci . '/content';
	if ( is_dir( $content_source ) && ! is_dir( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR ) ) {
		world_of_wordpress_copy_directory( $content_source, WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR );
	}

	$world_md_source = $repo_root_in_ci . '/WORLD.md';
	$world_md_dest   = WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR . '/WORLD.md';
	if ( file_exists( $world_md_source ) && ! file_exists( $world_md_dest ) ) {
		if ( ! is_dir( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR ) ) {
			wp_mkdir_p( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR );
		}
		copy( $world_md_source, $world_md_dest );
	}
}

/**
 * Seed the world: stage files, register shared memory, import content, clean defaults.
 */
function world_of_wordpress_seed_world(): void {
	world_of_wordpress_stage_world_files();
	world_of_wordpress_seed_shared_memory();

	if ( ! function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		$mdi_plugin = WP_PLUGIN_DIR . '/markdown-database-integration/markdown-database-integration.php';
		if ( file_exists( $mdi_plugin ) ) {
			require_once $mdi_plugin;
		}
	}

	if ( function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		markdown_database_integration_import_seed_posts_after_install();
	}

	update_option( 'blogname', 'World of WordPress' );
	update_option( 'blogdescription', 'A living WordPress Playground terrarium.' );

	foreach ( array( 'hello-world', 'sample-page', 'privacy-policy' ) as $slug ) {
		foreach ( array( 'post', 'page' ) as $post_type ) {
			$sample = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $sample ) {
				wp_delete_post( (int) $sample->ID, true );
			}
		}
	}

	$comments = get_comments( array( 'status' => 'all' ) );
	if ( is_array( $comments ) ) {
		foreach ( $comments as $comment ) {
			if ( $comment instanceof WP_Comment ) {
				wp_delete_comment( (int) $comment->comment_ID, true );
			}
		}
	}

	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
}
