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
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-template-lantern.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-pattern-loom.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-style-spring.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-block-garden.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-block-style-grove.php';
require_once WORLD_OF_WORDPRESS_PLUGIN_DIR . 'inc/world-runtime-pulse-card.php';

add_action( 'datamachine_memory_files', 'world_of_wordpress_register_memory_files' );

/**
 * Resolve the repository-bundled source root when the runtime provides one.
 */
function world_of_wordpress_resolve_source_root(): string {
	$candidates = array();

	if ( defined( 'WORLD_OF_WORDPRESS_SOURCE_ROOT' ) ) {
		$candidates[] = WORLD_OF_WORDPRESS_SOURCE_ROOT;
	}

	$candidates[] = dirname( WORLD_OF_WORDPRESS_PLUGIN_DIR, 2 );

	/**
	 * Filters possible source roots for repo-bundled world files.
	 *
	 * Runtime wrappers can provide named input paths without requiring this plugin
	 * to know the sandbox mount layout.
	 *
	 * @param array<int, string> $candidates Candidate absolute paths.
	 */
	$candidates = apply_filters( 'world_of_wordpress_source_root_candidates', $candidates );

	foreach ( $candidates as $candidate ) {
		if ( ! is_string( $candidate ) || '' === trim( $candidate ) ) {
			continue;
		}

		$candidate = rtrim( $candidate, '/\\' );
		if ( is_dir( $candidate ) && is_file( $candidate . '/WORLD.md' ) ) {
			return $candidate;
		}
	}

	return '';
}

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

	$metadata = array(
		'layer'       => \DataMachine\Engine\AI\MemoryFileRegistry::LAYER_SHARED,
		'protected'   => true,
		'modes'       => array( \DataMachine\Engine\AI\MemoryFileRegistry::MODE_ALL ),
		'label'       => 'World Context',
		'description' => 'Shared World of WordPress context for every agent on the site.',
	);

	/**
	 * Filters the Data Machine memory registration metadata for WORLD.md.
	 *
	 * Runtime wrappers can narrow modes or adjust labels without replacing the
	 * world plugin's bootstrap path.
	 *
	 * @param array<string, mixed> $metadata Memory file metadata.
	 */
	$metadata = apply_filters( 'world_of_wordpress_memory_file_metadata', $metadata );

	\DataMachine\Engine\AI\MemoryFileRegistry::register(
		'WORLD.md',
		18,
		is_array( $metadata ) ? $metadata : array()
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
 * content to the world-content dir, and writes WORLD.md there. Agent runtimes
 * can expose the repo-bundled source root through WORLD_OF_WORDPRESS_SOURCE_ROOT
 * or the world_of_wordpress_source_root_candidates filter.
 *
 * Idempotent: if the destination already has content, files are overwritten
 * with the bundled versions. If the source isn't found, nothing happens.
 */
function world_of_wordpress_stage_world_files(): void {
	$source_root = world_of_wordpress_resolve_source_root();
	if ( '' === $source_root ) {
		return;
	}

	$theme_source = $source_root . '/themes/world-of-wordpress';
	if ( is_dir( $theme_source ) ) {
		$theme_destination = WP_CONTENT_DIR . '/themes/world-of-wordpress';
		world_of_wordpress_copy_directory( $theme_source, $theme_destination );
		if ( file_exists( $theme_destination . '/style.css' ) ) {
			wp_clean_themes_cache( false );
			switch_theme( 'world-of-wordpress' );
		}
	}

	$content_source = $source_root . '/content';
	if ( is_dir( $content_source ) && ! is_dir( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR ) ) {
		world_of_wordpress_copy_directory( $content_source, WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR );
	}

	$world_md_source = $source_root . '/WORLD.md';
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
