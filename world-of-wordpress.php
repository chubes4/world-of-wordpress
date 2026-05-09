<?php
/**
 * Plugin Name: World of WordPress
 * Description: A self-contained WordPress Playground terrarium where an agent evolves software and content.
 * Version: 0.1.0
 * Author: Chris Huber
 * License: GPL v2 or later
 * Text Domain: world-of-wordpress
 */

defined( 'ABSPATH' ) || exit;

add_action( 'datamachine_memory_files', 'world_of_wordpress_register_memory_files' );
add_filter( 'markdown_db_table_persistence_policy', 'world_of_wordpress_markdown_db_table_persistence_policy' );
add_filter( 'markdown_db_persistent_table_rows', 'world_of_wordpress_filter_markdown_db_runtime_rows', 10, 4 );

/**
 * Configure which runtime database tables belong in the repo-backed world.
 *
 * MDI owns the disk persistence mechanism. This plugin owns the site policy:
 * keep just enough Data Machine job history for recurring flow due checks, and
 * keep ephemeral queues, logs, chats, and credential tables out of the repo.
 */
function world_of_wordpress_markdown_db_table_persistence_policy( array $policy ): array {
	$policy['datamachine_jobs'] = array(
		'persist'            => true,
		'latest_per_flow'    => 10,
		'redact_engine_data' => true,
	);

	foreach (
		array(
			'actionscheduler_actions',
			'actionscheduler_claims',
			'actionscheduler_groups',
			'actionscheduler_logs',
			'datamachine_agent_access',
			'datamachine_agent_tokens',
			'datamachine_bundle_artifacts',
			'datamachine_chat_sessions',
			'datamachine_flows',
			'datamachine_logs',
			'datamachine_pipelines',
			'datamachine_processed_items',
			'datamachine_code_cleanup_items',
			'datamachine_code_cleanup_runs',
			'datamachine_code_locks',
			'datamachine_code_worktrees',
		) as $table
	) {
		$policy[ $table ] = false;
	}

	return $policy;
}

/**
 * Compact persisted Data Machine jobs to scheduler-relevant history.
 *
 * Data Machine derives flow last-run time from the latest job row for each
 * flow. The full job row includes large execution payloads that are useful
 * during the request but should not become durable world state.
 */
function world_of_wordpress_filter_markdown_db_runtime_rows( array $rows, string $table_suffix, string $table, mixed $policy ): array {
	unset( $table );

	if ( 'datamachine_jobs' !== $table_suffix ) {
		return $rows;
	}

	$table_policy = is_array( $policy ) ? $policy : array();
	$keep         = max( 1, (int) ( $table_policy['latest_per_flow'] ?? 10 ) );
	usort(
		$rows,
		static function ( array $a, array $b ): int {
			return (int) ( $b['job_id'] ?? 0 ) <=> (int) ( $a['job_id'] ?? 0 );
		}
	);

	$counts = array();
	$kept   = array();
	foreach ( $rows as $row ) {
		$flow_id = (string) ( $row['flow_id'] ?? '' );
		if ( '' === $flow_id || ! is_numeric( $flow_id ) || (int) $flow_id <= 0 ) {
			continue;
		}

		$counts[ $flow_id ] = ( $counts[ $flow_id ] ?? 0 ) + 1;
		if ( $counts[ $flow_id ] > $keep ) {
			continue;
		}

		if ( ! empty( $table_policy['redact_engine_data'] ) ) {
			$row['engine_data'] = null;
		}

		$kept[] = $row;
	}

	usort(
		$kept,
		static function ( array $a, array $b ): int {
			return (int) ( $a['job_id'] ?? 0 ) <=> (int) ( $b['job_id'] ?? 0 );
		}
	);

	return $kept;
}

/**
 * Register the world model as shared memory for every agent in this site.
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
 * Copy the repository world model into Data Machine shared memory.
 */
function world_of_wordpress_seed_shared_memory(): void {
	if ( ! class_exists( '\DataMachine\Core\FilesRepository\DirectoryManager' ) ) {
		return;
	}

	$source = __DIR__ . '/WORLD.md';
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
 * Copy a directory recursively.
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
 * Seed and activate the repository-owned starter block theme.
 */
function world_of_wordpress_seed_theme(): void {
	$theme_slug   = 'world-of-wordpress';
	$source       = __DIR__ . '/themes/' . $theme_slug;
	$destination  = WP_CONTENT_DIR . '/themes/' . $theme_slug;
	$stylesheet   = $destination . '/style.css';

	world_of_wordpress_copy_directory( $source, $destination );

	if ( file_exists( $stylesheet ) ) {
		wp_clean_themes_cache( false );
		switch_theme( $theme_slug );
	}
}

/**
 * Seed the visible World of WordPress state from repository content.
 *
 * MDI stays generic and non-destructive; this plugin owns the terrarium policy
 * that repo-backed content should be the visible world.
 */
function world_of_wordpress_seed_world(): void {
	world_of_wordpress_seed_shared_memory();
	world_of_wordpress_seed_theme();

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

	foreach ( get_comments( array( 'status' => 'all' ) ) as $comment ) {
		wp_delete_comment( (int) $comment->comment_ID, true );
	}

	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
}
