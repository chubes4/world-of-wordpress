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
add_action( 'rest_api_init', 'world_of_wordpress_register_runtime_weather_rest_route' );
add_shortcode( 'world_runtime_weather', 'world_of_wordpress_render_runtime_weather_shortcode' );
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
 *
 * @param array<int,array<string,mixed>> $rows Rows about to be written.
 * @param string                         $table_suffix Table name without prefix.
 * @param string                         $table Full table name.
 * @param array<string,mixed>|bool|null  $policy Table persistence policy.
 * @return array<int,array<string,mixed>> Compacted rows.
 */
function world_of_wordpress_filter_markdown_db_runtime_rows( array $rows, string $table_suffix, string $table, $policy ): array {
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
 * Return safe public runtime weather data.
 *
 * This is the shared source for the shortcode and REST endpoint. Keep the
 * boundary intentionally boring: public engine facts, selected active tool
 * labels, and explicit privacy limits only.
 *
 * @return array<string,mixed> Public runtime weather data.
 */
function world_of_wordpress_get_runtime_weather_data(): array {
	$theme          = wp_get_theme();
	$active_plugins = array_map( 'strval', (array) get_option( 'active_plugins', array() ) );
	$tool_map       = array(
		'agents-api/agents-api.php'                                      => 'Agents API',
		'data-machine/data-machine.php'                                  => 'Data Machine',
		'data-machine-code/data-machine-code.php'                        => 'Data Machine Code',
		'markdown-database-integration/markdown-database-integration.php' => 'Markdown Database Integration',
		'ai-provider-for-openai/plugin.php'                              => 'OpenAI provider',
		'world-of-wordpress/world-of-wordpress.php'                       => 'World plugin',
	);
	$active_tools   = array();

	foreach ( $tool_map as $plugin_file => $label ) {
		if ( in_array( $plugin_file, $active_plugins, true ) ) {
			$active_tools[] = $label;
		}
	}

	return array(
		'engine'     => array(
			'wordpress' => get_bloginfo( 'version' ),
			'php'       => PHP_VERSION,
		),
		'theme'      => array(
			'name'       => $theme->exists() ? $theme->get( 'Name' ) : get_stylesheet(),
			'version'    => $theme->exists() ? $theme->get( 'Version' ) : '',
			'stylesheet' => get_stylesheet(),
		),
		'drop_in'    => ( defined( 'WP_CONTENT_DIR' ) && file_exists( WP_CONTENT_DIR . '/db.php' ) ) ? 'db.php present' : 'standard database loader',
		'debug'      => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'WP_DEBUG on' : 'WP_DEBUG off',
		'tools'      => $active_tools,
		'boundaries' => array(
			'public facts only',
			'no visitor tracking',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public runtime weather REST route.
 */
function world_of_wordpress_register_runtime_weather_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/runtime-weather',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_runtime_weather_data',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Render public runtime weather for visitors.
 *
 * The shortcode exposes only broad, non-secret runtime facts that are already
 * visible through the public WordPress environment or repository-owned policy:
 * engine versions, active theme, selected world tools, debug posture, and the
 * repo-backed database drop-in. It does not read visitor state, credentials,
 * uploads, logs, options beyond active plugin slugs, or private agent payloads.
 *
 * @return string Safe runtime weather markup.
 */
function world_of_wordpress_render_runtime_weather_shortcode(): string {
	static $instance = 0;

	++$instance;

	$weather      = world_of_wordpress_get_runtime_weather_data();
	$theme        = is_array( $weather['theme'] ?? null ) ? $weather['theme'] : array();
	$engine       = is_array( $weather['engine'] ?? null ) ? $weather['engine'] : array();
	$active_tools = is_array( $weather['tools'] ?? null ) ? $weather['tools'] : array();
	$theme_label  = trim( (string) ( $theme['name'] ?? '' ) . ' ' . (string) ( $theme['version'] ?? '' ) );
	$rest_url     = rest_url( 'world-of-wordpress/v1/runtime-weather' );
	$readout_id   = 'world-runtime-weather-rest-echo-' . $instance;

	ob_start();
	?>
	<div class="day-cycle-runtime-weather-live" aria-label="Live public WordPress runtime readout">
		<div class="weather-grid">
			<section class="weather-card">
				<h3><?php echo esc_html__( 'Live engine', 'world-of-wordpress' ); ?></h3>
				<p><?php echo esc_html( sprintf( 'WordPress %1$s, PHP %2$s.', (string) ( $engine['wordpress'] ?? '' ), (string) ( $engine['php'] ?? '' ) ) ); ?></p>
				<div class="weather-meter" aria-label="Live engine facts">
					<span><?php echo esc_html( $theme_label ); ?></span>
					<span><?php echo esc_html( (string) ( $weather['drop_in'] ?? '' ) ); ?></span>
					<span><?php echo esc_html( (string) ( $weather['debug'] ?? '' ) ); ?></span>
				</div>
			</section>
			<section class="weather-card">
				<h3><?php echo esc_html__( 'Live tool substrate', 'world-of-wordpress' ); ?></h3>
				<p><?php echo esc_html__( 'Repository-owned application code confirms the active public tools that let this terrarium think, write, persist, and return.', 'world-of-wordpress' ); ?></p>
				<div class="weather-meter" aria-label="Active world tools">
					<?php foreach ( $active_tools as $tool_label ) : ?>
						<span><?php echo esc_html( $tool_label ); ?></span>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="weather-card">
				<h3><?php echo esc_html__( 'Live operating boundary', 'world-of-wordpress' ); ?></h3>
				<p><?php echo esc_html__( 'This readout is public weather only: no visitor tracking, no private mailbox payloads, no credentials, and no hidden agent memory.', 'world-of-wordpress' ); ?></p>
				<div class="weather-meter" aria-label="Runtime readout boundaries">
					<span><?php echo esc_html__( 'public facts', 'world-of-wordpress' ); ?></span>
					<span><?php echo esc_html__( 'no cookies', 'world-of-wordpress' ); ?></span>
					<span><?php echo esc_html__( 'no database writes', 'world-of-wordpress' ); ?></span>
				</div>
			</section>
		</div>
		<section class="weather-rest-echo" aria-label="Public runtime weather REST echo">
			<h3><?php echo esc_html__( 'REST echo', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html__( 'The same public weather is fetched back through the read-only REST surface, proving the dashboard is an application interface instead of static decoration.', 'world-of-wordpress' ); ?></p>
			<pre id="<?php echo esc_attr( $readout_id ); ?>" data-runtime-weather-endpoint="<?php echo esc_url( $rest_url ); ?>"><?php echo esc_html__( 'Waiting for public runtime weather…', 'world-of-wordpress' ); ?></pre>
		</section>
		<script>
		(function () {
			const readout = document.getElementById( <?php echo wp_json_encode( $readout_id ); ?> );
			if ( ! readout || ! window.fetch ) {
				return;
			}

			fetch( readout.dataset.runtimeWeatherEndpoint, { credentials: 'same-origin' } )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Runtime weather unavailable' );
					}

					return response.json();
				} )
				.then( ( weather ) => {
					readout.textContent = JSON.stringify( {
						engine: weather.engine,
						theme: weather.theme && weather.theme.name,
						tools: weather.tools,
						boundaries: weather.boundaries
					}, null, 2 );
				} )
				.catch( () => {
					readout.textContent = 'The server-rendered weather remains visible; the REST echo could not be fetched in this runtime.';
				} );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
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
	$theme_slug  = 'world-of-wordpress';
	$source      = __DIR__ . '/themes/' . $theme_slug;
	$destination = WP_CONTENT_DIR . '/themes/' . $theme_slug;
	$stylesheet  = $destination . '/style.css';

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

	$comments = get_comments( array( 'status' => 'all' ) );
	if ( is_array( $comments ) ) {
		foreach ( $comments as $comment ) {
			if ( ! $comment instanceof WP_Comment ) {
				continue;
			}

			wp_delete_comment( (int) $comment->comment_ID, true );
		}
	}

	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
}
