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
add_action( 'init', 'world_of_wordpress_register_application_blocks' );
add_action( 'rest_api_init', 'world_of_wordpress_register_runtime_weather_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_manifest_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_registry_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_surface_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_surface_map_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_route_suggestions_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_route_itinerary_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_route_brief_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_action_launcher_rest_route' );
add_action( 'rest_api_init', 'world_of_wordpress_register_application_action_cards_rest_route' );
add_action( 'wp_head', 'world_of_wordpress_render_system_dark_mode_styles' );
add_action( 'wp_footer', 'world_of_wordpress_render_action_launcher_footer' );
add_shortcode( 'world_runtime_weather', 'world_of_wordpress_render_runtime_weather_shortcode' );
add_shortcode( 'world_application_manifest', 'world_of_wordpress_render_application_manifest_shortcode' );
add_shortcode( 'world_application_registry', 'world_of_wordpress_render_application_registry_shortcode' );
add_shortcode( 'world_application_surface_explorer', 'world_of_wordpress_render_application_surface_explorer_shortcode' );
add_shortcode( 'world_application_surface_map', 'world_of_wordpress_render_application_surface_map_shortcode' );
add_shortcode( 'world_application_route_suggestions', 'world_of_wordpress_render_application_route_suggestions_shortcode' );
add_shortcode( 'world_application_route_itinerary', 'world_of_wordpress_render_application_route_itinerary_shortcode' );
add_shortcode( 'world_application_route_brief', 'world_of_wordpress_render_application_route_brief_shortcode' );
add_shortcode( 'world_application_action_cards', 'world_of_wordpress_render_application_action_cards_shortcode' );
add_filter( 'markdown_db_table_persistence_policy', 'world_of_wordpress_markdown_db_table_persistence_policy' );
add_filter( 'markdown_db_persistent_table_rows', 'world_of_wordpress_filter_markdown_db_runtime_rows', 10, 4 );

/**
 * Register modern dynamic blocks for public application surfaces.
 *
 * The world still supports shortcodes because durable markdown content can use
 * them, but public action surfaces should also exist as first-class Block API
 * objects. These blocks render the same safe, read-only data as the shortcode
 * layer: no visitor tracking, no private mailbox payloads, no credentials, no
 * hidden agent memory, and no database writes.
 */
function world_of_wordpress_register_application_blocks(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		'world-of-wordpress/application-action-cards',
		array(
			'api_version'     => 3,
			'title'           => __( 'World Application Action Cards', 'world-of-wordpress' ),
			'category'        => 'widgets',
			'description'     => __( 'Renders immediate World of WordPress action cards from the public action-card API.', 'world-of-wordpress' ),
			'attributes'      => array(
				'heading'       => array(
					'type'    => 'string',
					'default' => __( 'Do something in the world', 'world-of-wordpress' ),
				),
				'showRouteMeta' => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'showRestEcho'  => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
			'render_callback' => 'world_of_wordpress_render_application_action_cards_block',
			'supports'        => array(
				'align'   => true,
				'html'    => false,
				'spacing' => array(
					'margin'  => true,
					'padding' => true,
				),
			),
		)
	);
}

/**
 * Render the application action cards block.
 *
 * @return string Safe block markup.
 */
function world_of_wordpress_render_application_action_cards_block( array $attributes = array(), string $content = '', ?WP_Block $block = null ): string {
	unset( $content, $block );

	$heading         = is_string( $attributes['heading'] ?? null ) && '' !== trim( (string) $attributes['heading'] ) ? (string) $attributes['heading'] : __( 'Do something in the world', 'world-of-wordpress' );
	$show_route_meta = isset( $attributes['showRouteMeta'] ) ? (bool) $attributes['showRouteMeta'] : false;
	$show_rest_echo  = isset( $attributes['showRestEcho'] ) ? (bool) $attributes['showRestEcho'] : false;

	return world_of_wordpress_render_application_action_cards_shortcode(
		array(
			'heading'         => $heading,
			'show_rest_echo'  => $show_rest_echo ? 'true' : 'false',
			'show_route_meta' => $show_route_meta ? 'true' : 'false',
			'surface'         => 'block',
		)
	);
}

/**
 * Render a system-preference dark mode layer for the public world.
 *
 * The active theme is copied into the runtime at install time and may not be
 * present in the compact review workspace, so this repository-owned plugin
 * carries the immediate accessibility repair. The layer only responds to the
 * visitor's operating-system color preference; it stores nothing, tracks
 * nothing, writes nothing, and does not require cookies or accounts.
 *
 * @return void
 */
function world_of_wordpress_render_system_dark_mode_styles(): void {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return;
	}

	?>
	<style id="world-of-wordpress-system-dark-mode">
		@media (prefers-color-scheme: dark) {
			:root {
				color-scheme: dark;
				--wp--preset--color--ink: #f8fafc;
				--wp--preset--color--paper: #080b12;
				--wp--preset--color--wordpress-blue: #93c5fd;
			}

			html,
			body {
				background: #080b12 !important;
				color: #e5e7eb !important;
			}

			body {
				background-image:
					radial-gradient(circle at 16% 12%, rgba(56, 88, 233, 0.22), transparent 32rem),
					radial-gradient(circle at 85% 0%, rgba(16, 185, 129, 0.16), transparent 28rem),
					linear-gradient(180deg, #0f172a 0%, #080b12 48%, #030712 100%) !important;
			}

			.wp-site-blocks,
			main,
			.entry-content,
			.is-root-container {
				background: transparent !important;
				color: #e5e7eb !important;
			}

			.wp-block-group,
			.wp-block-post,
			.wp-block-query,
			.wp-block-columns,
			.wp-block-column,
			[class*="-card"],
			[class*="-console"],
			[class*="-panel"],
			[class*="-manifest"],
			[class*="-registry"],
			[class*="-map"],
			[class*="-weather"] {
				border-color: rgba(148, 163, 184, 0.26) !important;
				background-color: rgba(15, 23, 42, 0.78) !important;
				color: #e5e7eb !important;
			}

			.wp-block-group.has-background,
			.wp-block-cover,
			.wp-block-cover__background {
				background-color: rgba(15, 23, 42, 0.84) !important;
			}

			h1,
			h2,
			h3,
			h4,
			h5,
			h6,
			strong,
			.wp-block-site-title,
			.wp-block-post-title {
				color: #f8fafc !important;
			}

			p,
			li,
			dt,
			dd,
			figcaption,
			.wp-block-post-excerpt,
			.wp-block-post-date,
			.wp-block-navigation-item__label {
				color: #cbd5e1 !important;
			}

			a,
			.wp-block-navigation a {
				color: #93c5fd !important;
			}

			a:hover,
			a:focus,
			.wp-block-navigation a:hover,
			.wp-block-navigation a:focus {
				color: #bfdbfe !important;
			}

			.wp-block-button__link,
			button,
			input[type="button"],
			input[type="submit"] {
				border-color: rgba(147, 197, 253, 0.42) !important;
				background: #93c5fd !important;
				color: #020617 !important;
			}

			input,
			textarea,
			select,
			pre,
			code {
				border-color: rgba(148, 163, 184, 0.32) !important;
				background: rgba(2, 6, 23, 0.82) !important;
				color: #e5e7eb !important;
			}

			hr,
			.wp-block-separator {
				border-color: rgba(148, 163, 184, 0.28) !important;
			}
		}
	</style>
	<?php
}

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
 * Return a safe public manifest for the world as an application.
 *
 * The manifest is intentionally hand-authored and repository-owned. It names
 * public surfaces, routes, live interfaces, and privacy promises without
 * reading visitor state, private issue bodies, hidden memory, credentials, or
 * logs.
 *
 * @return array<string,mixed> Public application manifest data.
 */
function world_of_wordpress_get_application_manifest_data(): array {
	return array(
		'name'        => 'World of WordPress',
		'tagline'     => 'A living WordPress Playground terrarium.',
		'purpose'     => 'Show WordPress as a durable, inspectable application substrate for agentic world-building.',
		'updated_by'  => 'repository-owned world plugin',
		'entrypoints' => array(
			array(
				'label'       => 'Front door',
				'kind'        => 'template pattern',
				'slug'        => 'front-door-introduction',
				'description' => 'Orients visitors to the visible terrarium.',
			),
			array(
				'label'       => 'Visitor choice chamber',
				'kind'        => 'playable route',
				'slug'        => 'visitor-choice-dial',
				'description' => 'Lets visitors choose a public posture without accounts or tracking.',
			),
			array(
				'label'       => 'Day-cycle console',
				'kind'        => 'operator protocol',
				'slug'        => 'day-cycle-flow-console',
				'description' => 'Explains how a waking run becomes reviewable change.',
			),
			array(
				'label'       => 'Runtime weather',
				'kind'        => 'REST-backed dashboard',
				'slug'        => 'day-cycle-runtime-weather',
				'description' => 'Publishes safe engine, theme, tool, and privacy-boundary facts.',
			),
			array(
				'label'       => 'Application registry',
				'kind'        => 'REST-backed surface index',
				'slug'        => 'world-application-registry',
				'description' => 'Indexes public world surfaces so future panels and agents can discover what exists.',
			),
			array(
				'label'       => 'Application surface explorer',
				'kind'        => 'REST-backed discovery panel',
				'slug'        => 'world-application-surface-explorer',
				'description' => 'Fetches one registered public surface at a time from the focused detail API.',
			),
			array(
				'label'       => 'Application surface map',
				'kind'        => 'REST-backed navigation map',
				'slug'        => 'world-application-surface-map',
				'description' => 'Groups registered public surfaces into a navigable map for visitors and future panels.',
			),
			array(
				'label'       => 'Application route suggestions',
				'kind'        => 'REST-backed route helper',
				'slug'        => 'world-application-route-suggestions',
				'description' => 'Suggests next public surfaces by visitor intent using the grouped surface map.',
			),
			array(
				'label'       => 'Application route itinerary',
				'kind'        => 'REST-backed ordered journey',
				'slug'        => 'world-application-route-itinerary',
				'description' => 'Turns intent-based route suggestions into an ordered public journey with focused surface detail.',
			),
			array(
				'label'       => 'Application route brief',
				'kind'        => 'REST-backed compact guide',
				'slug'        => 'world-application-route-brief',
				'description' => 'Compresses an ordered route into a small public brief that panels and agents can read quickly.',
			),
			array(
				'label'       => 'Application action launcher',
				'kind'        => 'global REST-backed action dock',
				'slug'        => 'world-application-action-launcher',
				'description' => 'Adds a persistent do-something dock with public actions that fetch immediate route briefs without accounts or tracking.',
			),
			array(
				'label'       => 'Application action cards',
				'kind'        => 'REST-backed action cards',
				'slug'        => 'world-application-action-cards',
				'description' => 'Turns the launcher actions into compact UI cards with verbs, route targets, and first stops so visitors can act without reading a console.',
			),
		),
		'interfaces'  => array(
			array(
				'label'       => 'Runtime weather API',
				'route'       => '/wp-json/world-of-wordpress/v1/runtime-weather',
				'method'      => 'GET',
				'description' => 'Read-only public runtime facts.',
			),
			array(
				'label'       => 'Application manifest API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-manifest',
				'method'      => 'GET',
				'description' => 'Read-only public map of world surfaces and promises.',
			),
			array(
				'label'       => 'Application registry API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-registry',
				'method'      => 'GET',
				'description' => 'Read-only public index of visible world surfaces and REST interfaces.',
			),
			array(
				'label'       => 'Application surface API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-surface/{slug}',
				'method'      => 'GET',
				'description' => 'Read-only public detail for one registered world surface.',
			),
			array(
				'label'       => 'Application surface map API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-surface-map',
				'method'      => 'GET',
				'description' => 'Read-only grouped map of registered world surfaces.',
			),
			array(
				'label'       => 'Application route suggestions API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-route-suggestions',
				'method'      => 'GET',
				'description' => 'Read-only public route suggestions derived from the grouped surface map.',
			),
			array(
				'label'       => 'Application route itinerary API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-route-itinerary',
				'method'      => 'GET',
				'description' => 'Read-only ordered public itinerary assembled from route suggestions and surface detail.',
			),
			array(
				'label'       => 'Application route brief API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-route-brief',
				'method'      => 'GET',
				'description' => 'Read-only compact route brief assembled from the public itinerary.',
			),
			array(
				'label'       => 'Application action launcher API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-action-launcher',
				'method'      => 'GET',
				'description' => 'Read-only public action launcher that turns a visible action into an immediate route brief.',
			),
			array(
				'label'       => 'Application action cards API',
				'route'       => '/wp-json/world-of-wordpress/v1/application-action-cards',
				'method'      => 'GET',
				'description' => 'Read-only public cards for launcher actions with direct verbs, route targets, and first stops.',
			),
		),
		'promises'    => array(
			'Public surfaces should be reviewable in the repository.',
			'Interactive visitor posture should remain visible and voluntary.',
			'Live dashboards should expose safe public facts only.',
			'Private mailbox payloads, credentials, hidden memory, logs, and visitor identity do not belong in public readouts.',
		),
		'boundaries'  => array(
			'no visitor tracking',
			'no cookies required',
			'no private mailbox contents',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application manifest REST route.
 */
function world_of_wordpress_register_application_manifest_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-manifest',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_manifest_data',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Render the public application manifest for visitors.
 *
 * @return string Safe manifest markup.
 */
function world_of_wordpress_render_application_manifest_shortcode(): string {
	static $instance = 0;

	++$instance;

	$manifest   = world_of_wordpress_get_application_manifest_data();
	$rest_url   = rest_url( 'world-of-wordpress/v1/application-manifest' );
	$readout_id = 'world-application-manifest-rest-echo-' . $instance;

	ob_start();
	?>
	<div class="world-application-manifest-live" aria-label="World of WordPress public application manifest">
		<div class="manifest-grid">
			<section class="manifest-card manifest-card-primary">
				<h3><?php echo esc_html__( 'Application identity', 'world-of-wordpress' ); ?></h3>
				<p><strong><?php echo esc_html( (string) ( $manifest['name'] ?? '' ) ); ?></strong> — <?php echo esc_html( (string) ( $manifest['tagline'] ?? '' ) ); ?></p>
				<p><?php echo esc_html( (string) ( $manifest['purpose'] ?? '' ) ); ?></p>
			</section>
			<section class="manifest-card">
				<h3><?php echo esc_html__( 'Public entrypoints', 'world-of-wordpress' ); ?></h3>
				<ul>
					<?php foreach ( (array) ( $manifest['entrypoints'] ?? array() ) as $entrypoint ) : ?>
						<?php $entrypoint = is_array( $entrypoint ) ? $entrypoint : array(); ?>
						<li><code><?php echo esc_html( (string) ( $entrypoint['slug'] ?? '' ) ); ?></code> <?php echo esc_html( (string) ( $entrypoint['description'] ?? '' ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
			<section class="manifest-card">
				<h3><?php echo esc_html__( 'Read-only interfaces', 'world-of-wordpress' ); ?></h3>
				<ul>
					<?php foreach ( (array) ( $manifest['interfaces'] ?? array() ) as $interface ) : ?>
						<?php $interface = is_array( $interface ) ? $interface : array(); ?>
						<li><code><?php echo esc_html( (string) ( $interface['route'] ?? '' ) ); ?></code> <?php echo esc_html( (string) ( $interface['description'] ?? '' ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<section class="manifest-promises" aria-label="World application promises">
			<h3><?php echo esc_html__( 'Promises and boundaries', 'world-of-wordpress' ); ?></h3>
			<div class="manifest-pill-row">
				<?php foreach ( (array) ( $manifest['promises'] ?? array() ) as $promise ) : ?>
					<span><?php echo esc_html( (string) $promise ); ?></span>
				<?php endforeach; ?>
			</div>
			<p><?php echo esc_html__( 'The manifest refuses hidden state as proof. It describes only public, reviewable surfaces and the privacy boundary those surfaces promise to keep.', 'world-of-wordpress' ); ?></p>
		</section>
		<section class="manifest-rest-echo" aria-label="Public application manifest REST echo">
			<h3><?php echo esc_html__( 'Manifest REST echo', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html__( 'The same world map is fetched back through the public REST endpoint so other panels, agents, and visitors can consume the application contract directly.', 'world-of-wordpress' ); ?></p>
			<pre id="<?php echo esc_attr( $readout_id ); ?>" data-application-manifest-endpoint="<?php echo esc_url( $rest_url ); ?>"><?php echo esc_html__( 'Waiting for public application manifest…', 'world-of-wordpress' ); ?></pre>
		</section>
		<script>
		(function () {
			const readout = document.getElementById( <?php echo wp_json_encode( $readout_id ); ?> );
			if ( ! readout || ! window.fetch ) {
				return;
			}

			fetch( readout.dataset.applicationManifestEndpoint, { credentials: 'same-origin' } )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Application manifest unavailable' );
					}

					return response.json();
				} )
				.then( ( manifest ) => {
					readout.textContent = JSON.stringify( {
						name: manifest.name,
						entrypoints: Array.isArray( manifest.entrypoints ) ? manifest.entrypoints.map( ( entrypoint ) => entrypoint.slug ) : [],
						interfaces: Array.isArray( manifest.interfaces ) ? manifest.interfaces.map( ( apiInterface ) => apiInterface.route ) : [],
						boundaries: manifest.boundaries
					}, null, 2 );
				} )
				.catch( () => {
					readout.textContent = 'The server-rendered manifest remains visible; the REST echo could not be fetched in this runtime.';
				} );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return a safe public registry of visible world application surfaces.
 *
 * The registry is a deliberately boring discovery layer: it is hand-authored,
 * repository-owned, and limited to public pattern slugs, shortcode names, and
 * REST interface routes. It does not crawl content, inspect visitors, read
 * mailbox payloads, or write runtime state.
 *
 * @return array<string,mixed> Public application registry data.
 */
function world_of_wordpress_get_application_registry_data(): array {
	$manifest = world_of_wordpress_get_application_manifest_data();

	return array(
		'name'       => 'World of WordPress application registry',
		'purpose'    => 'A public index of living world surfaces that can be rendered, reused, or consumed by future panels and agents.',
		'updated_by' => 'repository-owned world plugin',
		'counts'     => array(
			'pattern_surfaces' => 35,
			'shortcodes'       => 9,
			'dynamic_blocks'   => 1,
			'rest_interfaces'  => 10,
		),
		'surfaces'   => array(
			array( 'slug' => 'front-door-introduction', 'group' => 'orientation', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'world-wayfinder', 'group' => 'orientation', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'wayfinder-guidance', 'group' => 'orientation', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'world-status-panel', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'day-cycle-loop', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'day-cycle-flow-console', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'day-cycle-runtime-weather', 'group' => 'world senses', 'kind' => 'REST-backed pattern', 'public' => true ),
			array( 'slug' => 'world-application-manifest', 'group' => 'world senses', 'kind' => 'REST-backed pattern', 'public' => true ),
			array( 'slug' => 'world-application-registry', 'group' => 'world senses', 'kind' => 'REST-backed pattern', 'public' => true ),
			array( 'slug' => 'world-application-surface-explorer', 'group' => 'world senses', 'kind' => 'REST-backed discovery pattern', 'public' => true ),
			array( 'slug' => 'world-application-surface-map', 'group' => 'world senses', 'kind' => 'REST-backed navigation pattern', 'public' => true ),
			array( 'slug' => 'world-application-route-suggestions', 'group' => 'world senses', 'kind' => 'REST-backed route helper', 'public' => true ),
			array( 'slug' => 'world-application-route-itinerary', 'group' => 'world senses', 'kind' => 'REST-backed ordered journey', 'public' => true ),
			array( 'slug' => 'world-application-route-brief', 'group' => 'world senses', 'kind' => 'REST-backed compact guide', 'public' => true ),
			array( 'slug' => 'world-application-action-launcher', 'group' => 'world senses', 'kind' => 'global REST-backed action dock', 'public' => true ),
			array( 'slug' => 'world-application-action-cards', 'group' => 'world senses', 'kind' => 'REST-backed action cards', 'public' => true ),
			array( 'slug' => 'world-signal-console', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'world-observatory-console', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'world-atlas-compass', 'group' => 'world senses', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'cartographer-office-ledger', 'group' => 'civic offices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'librarian-shelf-ledger', 'group' => 'civic offices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'field-note-route', 'group' => 'routes and feeds', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'latest-field-notes-intro', 'group' => 'routes and feeds', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'latest-field-notes-query', 'group' => 'routes and feeds', 'kind' => 'query pattern', 'public' => true ),
			array( 'slug' => 'civic-ritual-board', 'group' => 'rituals and festivals', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'civic-front-desk-audit', 'group' => 'rituals and festivals', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'festival-lantern-grid', 'group' => 'rituals and festivals', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'visitor-choice-dial', 'group' => 'visitor choices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'visitor-token-switchboard', 'group' => 'visitor choices', 'kind' => 'fragment-target pattern', 'public' => true ),
			array( 'slug' => 'visitor-consequence-gate', 'group' => 'visitor choices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'visitor-token-badges', 'group' => 'visitor choices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'visitor-token-trail', 'group' => 'visitor choices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'visitor-return-stamps', 'group' => 'visitor choices', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'stranger-signal-route', 'group' => 'stranger signals', 'kind' => 'theme pattern', 'public' => true ),
			array( 'slug' => 'civic-critic-dispute-ledger', 'group' => 'dispute surfaces', 'kind' => 'theme pattern', 'public' => true ),
		),
		'shortcodes' => array(
			array( 'tag' => 'world_runtime_weather', 'description' => 'Renders safe runtime weather and fetches its public REST echo.' ),
			array( 'tag' => 'world_application_manifest', 'description' => 'Renders the world application manifest and fetches its public REST echo.' ),
			array( 'tag' => 'world_application_registry', 'description' => 'Renders this public surface registry and fetches its public REST echo.' ),
			array( 'tag' => 'world_application_surface_explorer', 'description' => 'Renders a small client-side explorer for fetching one registered public surface by slug.' ),
			array( 'tag' => 'world_application_surface_map', 'description' => 'Renders a grouped navigation map of registered public surfaces and fetches its public REST echo.' ),
			array( 'tag' => 'world_application_route_suggestions', 'description' => 'Renders intent-based public route suggestions derived from the grouped surface map.' ),
			array( 'tag' => 'world_application_route_itinerary', 'description' => 'Renders an ordered public route itinerary assembled from suggestions and focused surface detail.' ),
			array( 'tag' => 'world_application_route_brief', 'description' => 'Renders a compact public route brief assembled from the ordered itinerary.' ),
			array( 'tag' => 'world_application_action_cards', 'description' => 'Renders compact launcher-derived action cards with verbs, first stops, and a public REST echo.' ),
		),
		'blocks'     => array(
			array( 'name' => 'world-of-wordpress/application-action-cards', 'description' => 'Dynamic Block API renderer for the same immediate public action cards.' ),
		),
		'interfaces' => $manifest['interfaces'] ?? array(),
		'boundaries' => array(
			'public repository-owned surface names only',
			'no visitor tracking',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application registry REST route.
 */
function world_of_wordpress_register_application_registry_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-registry',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_registry_data',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Render the public application registry for visitors.
 *
 * @return string Safe registry markup.
 */
function world_of_wordpress_render_application_registry_shortcode(): string {
	static $instance = 0;

	++$instance;

	$registry   = world_of_wordpress_get_application_registry_data();
	$rest_url   = rest_url( 'world-of-wordpress/v1/application-registry' );
	$readout_id = 'world-application-registry-rest-echo-' . $instance;
	$groups     = array();

	foreach ( (array) ( $registry['surfaces'] ?? array() ) as $surface ) {
		if ( ! is_array( $surface ) ) {
			continue;
		}

		$group              = (string) ( $surface['group'] ?? 'uncategorized' );
		$groups[ $group ][] = $surface;
	}

	ob_start();
	?>
	<div class="world-application-registry-live" aria-label="World of WordPress public application registry">
		<div class="registry-grid">
			<section class="registry-card registry-card-primary">
				<h3><?php echo esc_html__( 'Registry identity', 'world-of-wordpress' ); ?></h3>
				<p><strong><?php echo esc_html( (string) ( $registry['name'] ?? '' ) ); ?></strong></p>
				<p><?php echo esc_html( (string) ( $registry['purpose'] ?? '' ) ); ?></p>
			</section>
			<section class="registry-card">
				<h3><?php echo esc_html__( 'Surface count', 'world-of-wordpress' ); ?></h3>
				<div class="registry-pill-row">
					<?php foreach ( (array) ( $registry['counts'] ?? array() ) as $label => $count ) : ?>
						<span><?php echo esc_html( str_replace( '_', ' ', (string) $label ) . ': ' . (string) $count ); ?></span>
					<?php endforeach; ?>
				</div>
			</section>
			<section class="registry-card">
				<h3><?php echo esc_html__( 'REST interfaces', 'world-of-wordpress' ); ?></h3>
				<ul>
					<?php foreach ( (array) ( $registry['interfaces'] ?? array() ) as $interface ) : ?>
						<?php $interface = is_array( $interface ) ? $interface : array(); ?>
						<li><code><?php echo esc_html( (string) ( $interface['route'] ?? '' ) ); ?></code></li>
					<?php endforeach; ?>
				</ul>
			</section>
		</div>
		<div class="registry-groups" aria-label="Grouped public world surfaces">
			<?php foreach ( $groups as $group_label => $surfaces ) : ?>
				<section class="registry-group-card">
					<h3><?php echo esc_html( ucwords( $group_label ) ); ?></h3>
					<ul>
						<?php foreach ( $surfaces as $surface ) : ?>
							<li><code><?php echo esc_html( (string) ( $surface['slug'] ?? '' ) ); ?></code> <span><?php echo esc_html( (string) ( $surface['kind'] ?? '' ) ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>
		<section class="registry-rest-echo" aria-label="Public application registry REST echo">
			<h3><?php echo esc_html__( 'Registry REST echo', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html__( 'The same surface index is fetched through the public REST endpoint so future world instruments can discover existing panels without scraping the page.', 'world-of-wordpress' ); ?></p>
			<pre id="<?php echo esc_attr( $readout_id ); ?>" data-application-registry-endpoint="<?php echo esc_url( $rest_url ); ?>"><?php echo esc_html__( 'Waiting for public application registry…', 'world-of-wordpress' ); ?></pre>
		</section>
		<script>
		(function () {
			const readout = document.getElementById( <?php echo wp_json_encode( $readout_id ); ?> );
			if ( ! readout || ! window.fetch ) {
				return;
			}

			fetch( readout.dataset.applicationRegistryEndpoint, { credentials: 'same-origin' } )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Application registry unavailable' );
					}

					return response.json();
				} )
				.then( ( registry ) => {
					readout.textContent = JSON.stringify( {
						name: registry.name,
						counts: registry.counts,
						first_surfaces: Array.isArray( registry.surfaces ) ? registry.surfaces.slice( 0, 8 ).map( ( surface ) => surface.slug ) : [],
						interfaces: Array.isArray( registry.interfaces ) ? registry.interfaces.map( ( apiInterface ) => apiInterface.route ) : [],
						boundaries: registry.boundaries
					}, null, 2 );
				} )
				.catch( () => {
					readout.textContent = 'The server-rendered registry remains visible; the REST echo could not be fetched in this runtime.';
				} );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Find one public application surface in the hand-authored registry.
 *
 * @param string $slug Surface slug.
 * @return array<string,mixed>|null Public surface data, or null when absent.
 */
function world_of_wordpress_find_application_surface_data( string $slug ): ?array {
	$slug     = sanitize_title( $slug );
	$registry = world_of_wordpress_get_application_registry_data();
	$surfaces = (array) ( $registry['surfaces'] ?? array() );

	foreach ( $surfaces as $surface ) {
		if ( ! is_array( $surface ) || $slug !== (string) ( $surface['slug'] ?? '' ) ) {
			continue;
		}

		$group    = (string) ( $surface['group'] ?? '' );
		$siblings = array();

		foreach ( $surfaces as $candidate ) {
			if ( ! is_array( $candidate ) || $slug === (string) ( $candidate['slug'] ?? '' ) ) {
				continue;
			}

			if ( $group === (string) ( $candidate['group'] ?? '' ) ) {
				$siblings[] = (string) ( $candidate['slug'] ?? '' );
			}
		}

		$surface['detail'] = array(
			'endpoint'    => '/wp-json/world-of-wordpress/v1/application-surface/' . $slug,
			'discovered'  => 'application registry',
			'use'         => 'Use this public detail object to route visitors, assemble dashboards, or help future agents understand one surface without scraping rendered pages.',
			'siblings'    => array_values( array_slice( array_filter( $siblings ), 0, 8 ) ),
			'privacy'     => array(
				'public surface metadata only',
				'no visitor tracking',
				'no private mailbox payloads',
				'no credentials',
				'no hidden agent memory',
				'no database writes',
			),
		);

		return $surface;
	}

	return null;
}

/**
 * Register the public application surface detail REST route.
 */
function world_of_wordpress_register_application_surface_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-surface/(?P<slug>[a-z0-9-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_surface_rest_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'slug' => array(
					'description'       => 'Registered public surface slug.',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
				),
			),
		)
	);
}

/**
 * Return one registered public application surface for REST consumers.
 *
 * @param WP_REST_Request $request REST request.
 * @return array<string,mixed>|WP_Error Public surface detail or not-found error.
 */
function world_of_wordpress_get_application_surface_rest_response( WP_REST_Request $request ) {
	$slug    = (string) $request->get_param( 'slug' );
	$surface = world_of_wordpress_find_application_surface_data( $slug );

	if ( null === $surface ) {
		return new WP_Error(
			'world_application_surface_not_found',
			__( 'That public world surface is not registered.', 'world-of-wordpress' ),
			array( 'status' => 404 )
		);
	}

	return $surface;
}

/**
 * Render a small public explorer for individual registered surfaces.
 *
 * @return string Safe surface explorer markup.
 */
function world_of_wordpress_render_application_surface_explorer_shortcode(): string {
	static $instance = 0;

	++$instance;

	$registry     = world_of_wordpress_get_application_registry_data();
	$surfaces     = array_values( array_filter( (array) ( $registry['surfaces'] ?? array() ), 'is_array' ) );
	$initial_slug = (string) ( $surfaces[0]['slug'] ?? 'front-door-introduction' );
	$endpoint     = rest_url( 'world-of-wordpress/v1/application-surface/' );
	$readout_id   = 'world-application-surface-readout-' . $instance;
	$buttons_id   = 'world-application-surface-buttons-' . $instance;

	ob_start();
	?>
	<div class="world-application-surface-explorer" aria-label="World of WordPress public surface explorer">
		<section class="surface-explorer-card surface-explorer-card-primary">
			<h3><?php echo esc_html__( 'Application surface explorer', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html__( 'Choose a registered public surface and the terrarium fetches its detail object through a focused REST route. This is discovery without scraping, tracking, accounts, or hidden memory.', 'world-of-wordpress' ); ?></p>
		</section>
		<div id="<?php echo esc_attr( $buttons_id ); ?>" class="surface-explorer-buttons" data-surface-endpoint="<?php echo esc_url( $endpoint ); ?>" data-surface-readout="<?php echo esc_attr( $readout_id ); ?>">
			<?php foreach ( array_slice( $surfaces, 0, 12 ) as $surface ) : ?>
				<?php $surface_slug = (string) ( $surface['slug'] ?? '' ); ?>
				<button type="button" data-surface-slug="<?php echo esc_attr( $surface_slug ); ?>"><?php echo esc_html( $surface_slug ); ?></button>
			<?php endforeach; ?>
		</div>
		<pre id="<?php echo esc_attr( $readout_id ); ?>" data-initial-surface="<?php echo esc_attr( $initial_slug ); ?>"><?php echo esc_html__( 'Waiting for a public surface detail…', 'world-of-wordpress' ); ?></pre>
		<script>
		(function () {
			const buttons = document.getElementById( <?php echo wp_json_encode( $buttons_id ); ?> );
			if ( ! buttons || ! window.fetch ) {
				return;
			}

			const readout = document.getElementById( buttons.dataset.surfaceReadout );
			if ( ! readout ) {
				return;
			}

			const loadSurface = ( slug ) => {
				if ( ! slug ) {
					return;
				}

				readout.textContent = 'Fetching ' + slug + '…';
				fetch( buttons.dataset.surfaceEndpoint + encodeURIComponent( slug ), { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Application surface unavailable' );
						}

						return response.json();
					} )
					.then( ( surface ) => {
						readout.textContent = JSON.stringify( {
							slug: surface.slug,
							group: surface.group,
							kind: surface.kind,
							detail: surface.detail
						}, null, 2 );
					} )
					.catch( () => {
						readout.textContent = 'The surface detail could not be fetched in this runtime.';
					} );
			};

			buttons.addEventListener( 'click', ( event ) => {
				const button = event.target.closest( 'button[data-surface-slug]' );
				if ( button ) {
					loadSurface( button.dataset.surfaceSlug );
				}
			} );

			loadSurface( readout.dataset.initialSurface );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return a grouped public map of registered application surfaces.
 *
 * @return array<string,mixed> Public surface map data.
 */
function world_of_wordpress_get_application_surface_map_data(): array {
	$registry = world_of_wordpress_get_application_registry_data();
	$groups   = array();

	foreach ( (array) ( $registry['surfaces'] ?? array() ) as $surface ) {
		if ( ! is_array( $surface ) ) {
			continue;
		}

		$group = (string) ( $surface['group'] ?? 'uncategorized' );
		if ( ! isset( $groups[ $group ] ) ) {
			$groups[ $group ] = array(
				'label'    => ucwords( $group ),
				'count'    => 0,
				'surfaces' => array(),
			);
		}

		$groups[ $group ]['count']++;
		$groups[ $group ]['surfaces'][] = array(
			'slug'     => (string) ( $surface['slug'] ?? '' ),
			'kind'     => (string) ( $surface['kind'] ?? '' ),
			'endpoint' => '/wp-json/world-of-wordpress/v1/application-surface/' . (string) ( $surface['slug'] ?? '' ),
		);
	}

	return array(
		'name'       => 'World of WordPress application surface map',
		'purpose'    => 'A grouped navigation layer over the public application registry for visitors, dashboards, and future agents.',
		'source'     => '/wp-json/world-of-wordpress/v1/application-registry',
		'groups'     => array_values( $groups ),
		'boundaries' => array(
			'public surface metadata only',
			'no visitor tracking',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application surface map REST route.
 */
function world_of_wordpress_register_application_surface_map_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-surface-map',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_surface_map_data',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Render a grouped public map of registered application surfaces.
 *
 * @return string Safe surface map markup.
 */
function world_of_wordpress_render_application_surface_map_shortcode(): string {
	static $instance = 0;

	++$instance;

	$map        = world_of_wordpress_get_application_surface_map_data();
	$rest_url   = rest_url( 'world-of-wordpress/v1/application-surface-map' );
	$readout_id = 'world-application-surface-map-rest-echo-' . $instance;

	ob_start();
	?>
	<div class="world-application-surface-map-live" aria-label="World of WordPress public surface map">
		<section class="surface-map-card surface-map-card-primary">
			<h3><?php echo esc_html__( 'Application surface map', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html( (string) ( $map['purpose'] ?? '' ) ); ?></p>
		</section>
		<div class="surface-map-grid" aria-label="Grouped public application surfaces">
			<?php foreach ( (array) ( $map['groups'] ?? array() ) as $group ) : ?>
				<?php $group = is_array( $group ) ? $group : array(); ?>
				<section class="surface-map-group">
					<h3><?php echo esc_html( (string) ( $group['label'] ?? '' ) ); ?> <span><?php echo esc_html( (string) ( $group['count'] ?? 0 ) ); ?></span></h3>
					<ul>
						<?php foreach ( (array) ( $group['surfaces'] ?? array() ) as $surface ) : ?>
							<?php $surface = is_array( $surface ) ? $surface : array(); ?>
							<li><code><?php echo esc_html( (string) ( $surface['slug'] ?? '' ) ); ?></code> <span><?php echo esc_html( (string) ( $surface['kind'] ?? '' ) ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
		</div>
		<section class="surface-map-rest-echo" aria-label="Public application surface map REST echo">
			<h3><?php echo esc_html__( 'Surface map REST echo', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html__( 'The same grouped map is fetched through the public REST endpoint so future panels can route by role instead of hard-coding the shelf.', 'world-of-wordpress' ); ?></p>
			<pre id="<?php echo esc_attr( $readout_id ); ?>" data-application-surface-map-endpoint="<?php echo esc_url( $rest_url ); ?>"><?php echo esc_html__( 'Waiting for public surface map…', 'world-of-wordpress' ); ?></pre>
		</section>
		<script>
		(function () {
			const readout = document.getElementById( <?php echo wp_json_encode( $readout_id ); ?> );
			if ( ! readout || ! window.fetch ) {
				return;
			}

			fetch( readout.dataset.applicationSurfaceMapEndpoint, { credentials: 'same-origin' } )
				.then( ( response ) => {
					if ( ! response.ok ) {
						throw new Error( 'Application surface map unavailable' );
					}

					return response.json();
				} )
				.then( ( map ) => {
					readout.textContent = JSON.stringify( {
						name: map.name,
						groups: Array.isArray( map.groups ) ? map.groups.map( ( group ) => ( { label: group.label, count: group.count } ) ) : [],
						boundaries: map.boundaries
					}, null, 2 );
				} )
				.catch( () => {
					readout.textContent = 'The server-rendered surface map remains visible; the REST echo could not be fetched in this runtime.';
				} );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return public route suggestions derived from the grouped application surface map.
 *
 * @param string $intent Optional visitor or operator intent.
 * @return array<string,mixed> Public route suggestion data.
 */
function world_of_wordpress_get_application_route_suggestions_data( string $intent = 'overview' ): array {
	$intent = sanitize_key( $intent );
	if ( '' === $intent ) {
		$intent = 'overview';
	}

	$map       = world_of_wordpress_get_application_surface_map_data();
	$by_group  = array();
	$supported = array( 'overview', 'visitor', 'operator', 'runtime', 'content', 'signals' );

	foreach ( (array) ( $map['groups'] ?? array() ) as $group ) {
		if ( ! is_array( $group ) ) {
			continue;
		}

		$label              = strtolower( (string) ( $group['label'] ?? '' ) );
		$by_group[ $label ] = array_values( array_filter( (array) ( $group['surfaces'] ?? array() ), 'is_array' ) );
	}

	$recipes = array(
		'overview' => array(
			'world senses',
			'orientation',
			'visitor choices',
		),
		'visitor'  => array(
			'visitor choices',
			'orientation',
			'routes and feeds',
		),
		'operator' => array(
			'world senses',
			'civic offices',
			'dispute surfaces',
		),
		'runtime'  => array(
			'world senses',
		),
		'content'  => array(
			'routes and feeds',
			'civic offices',
			'rituals and festivals',
		),
		'signals'  => array(
			'stranger signals',
			'dispute surfaces',
			'world senses',
		),
	);

	$selected_intent = in_array( $intent, $supported, true ) ? $intent : 'overview';
	$suggestions     = array();

	foreach ( $recipes[ $selected_intent ] as $group_key ) {
		foreach ( (array) ( $by_group[ $group_key ] ?? array() ) as $surface ) {
			$suggestions[] = array(
				'slug'     => (string) ( $surface['slug'] ?? '' ),
				'kind'     => (string) ( $surface['kind'] ?? '' ),
				'group'    => $group_key,
				'endpoint' => (string) ( $surface['endpoint'] ?? ( '/wp-json/world-of-wordpress/v1/application-surface/' . (string) ( $surface['slug'] ?? '' ) ) ),
			);
		}
	}

	$suggestions = array_values(
		array_filter(
			array_slice( $suggestions, 0, 10 ),
			static function ( array $suggestion ): bool {
				return '' !== (string) ( $suggestion['slug'] ?? '' );
			}
		)
	);

	return array(
		'name'              => 'World of WordPress application route suggestions',
		'purpose'           => 'Suggest public next surfaces by intent using the grouped application surface map as the source of truth.',
		'intent'            => $selected_intent,
		'supported_intents' => $supported,
		'source'            => '/wp-json/world-of-wordpress/v1/application-surface-map',
		'suggestions'       => $suggestions,
		'boundaries'        => array(
			'public surface metadata only',
			'no visitor tracking',
			'no cookies required',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application route suggestions REST route.
 */
function world_of_wordpress_register_application_route_suggestions_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-route-suggestions',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_route_suggestions_rest_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'intent' => array(
					'description'       => 'Public route intent: overview, visitor, operator, runtime, content, or signals.',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);
}

/**
 * Return route suggestions for REST consumers.
 *
 * @param WP_REST_Request $request REST request.
 * @return array<string,mixed> Public route suggestions.
 */
function world_of_wordpress_get_application_route_suggestions_rest_response( WP_REST_Request $request ): array {
	return world_of_wordpress_get_application_route_suggestions_data( (string) $request->get_param( 'intent' ) );
}

/**
 * Render public route suggestions for visitors and future panels.
 *
 * @return string Safe route suggestion markup.
 */
function world_of_wordpress_render_application_route_suggestions_shortcode(): string {
	static $instance = 0;

	++$instance;

	$suggestions = world_of_wordpress_get_application_route_suggestions_data();
	$rest_url    = rest_url( 'world-of-wordpress/v1/application-route-suggestions' );
	$readout_id  = 'world-application-route-suggestions-readout-' . $instance;
	$buttons_id  = 'world-application-route-suggestions-buttons-' . $instance;

	ob_start();
	?>
	<div class="world-application-route-suggestions" aria-label="World of WordPress public route suggestions">
		<section class="route-suggestions-card route-suggestions-card-primary">
			<h3><?php echo esc_html__( 'Application route suggestions', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html( (string) ( $suggestions['purpose'] ?? '' ) ); ?></p>
		</section>
		<div id="<?php echo esc_attr( $buttons_id ); ?>" class="route-suggestions-buttons" data-route-suggestions-endpoint="<?php echo esc_url( $rest_url ); ?>" data-route-suggestions-readout="<?php echo esc_attr( $readout_id ); ?>">
			<?php foreach ( (array) ( $suggestions['supported_intents'] ?? array() ) as $intent ) : ?>
				<button type="button" data-route-intent="<?php echo esc_attr( (string) $intent ); ?>"><?php echo esc_html( (string) $intent ); ?></button>
			<?php endforeach; ?>
		</div>
		<pre id="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Waiting for public route suggestions…', 'world-of-wordpress' ); ?></pre>
		<script>
		(function () {
			const buttons = document.getElementById( <?php echo wp_json_encode( $buttons_id ); ?> );
			if ( ! buttons || ! window.fetch ) {
				return;
			}

			const readout = document.getElementById( buttons.dataset.routeSuggestionsReadout );
			if ( ! readout ) {
				return;
			}

			const loadSuggestions = ( intent ) => {
				const endpoint = buttons.dataset.routeSuggestionsEndpoint + '?intent=' + encodeURIComponent( intent || 'overview' );
				readout.textContent = 'Fetching ' + ( intent || 'overview' ) + ' route suggestions…';
				fetch( endpoint, { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Application route suggestions unavailable' );
						}

						return response.json();
					} )
					.then( ( routeData ) => {
						readout.textContent = JSON.stringify( {
							intent: routeData.intent,
							suggestions: Array.isArray( routeData.suggestions ) ? routeData.suggestions.map( ( suggestion ) => ( { slug: suggestion.slug, group: suggestion.group, endpoint: suggestion.endpoint } ) ) : [],
							boundaries: routeData.boundaries
						}, null, 2 );
					} )
					.catch( () => {
						readout.textContent = 'The route suggestions could not be fetched in this runtime.';
					} );
			};

			buttons.addEventListener( 'click', ( event ) => {
				const button = event.target.closest( 'button[data-route-intent]' );
				if ( button ) {
					loadSuggestions( button.dataset.routeIntent );
				}
			} );

			loadSuggestions( 'overview' );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return an ordered public itinerary assembled from route suggestions and surface detail.
 *
 * @param string $intent Optional visitor or operator intent.
 * @return array<string,mixed> Public itinerary data.
 */
function world_of_wordpress_get_application_route_itinerary_data( string $intent = 'overview' ): array {
	$route_data = world_of_wordpress_get_application_route_suggestions_data( $intent );
	$steps      = array();
	$position   = 0;

	foreach ( (array) ( $route_data['suggestions'] ?? array() ) as $suggestion ) {
		if ( ! is_array( $suggestion ) ) {
			continue;
		}

		$slug    = (string) ( $suggestion['slug'] ?? '' );
		$surface = '' !== $slug ? world_of_wordpress_find_application_surface_data( $slug ) : null;
		if ( null === $surface ) {
			continue;
		}

		++$position;
		$steps[] = array(
			'position' => $position,
			'slug'     => $slug,
			'group'    => (string) ( $surface['group'] ?? $suggestion['group'] ?? '' ),
			'kind'     => (string) ( $surface['kind'] ?? $suggestion['kind'] ?? '' ),
			'why'      => sprintf(
				/* translators: 1: route intent, 2: surface group. */
				__( 'Step %1$d appears because the %2$s intent visits the %3$s shelf.', 'world-of-wordpress' ),
				$position,
				(string) ( $route_data['intent'] ?? 'overview' ),
				(string) ( $surface['group'] ?? $suggestion['group'] ?? 'public surface' )
			),
			'detail'   => array(
				'endpoint' => (string) ( $surface['detail']['endpoint'] ?? $suggestion['endpoint'] ?? '' ),
				'siblings' => (array) ( $surface['detail']['siblings'] ?? array() ),
			),
		);
	}

	return array(
		'name'              => 'World of WordPress application route itinerary',
		'purpose'           => 'Turns route suggestions into an ordered public journey with focused surface detail for each stop.',
		'intent'            => (string) ( $route_data['intent'] ?? 'overview' ),
		'supported_intents' => (array) ( $route_data['supported_intents'] ?? array() ),
		'source'            => '/wp-json/world-of-wordpress/v1/application-route-suggestions',
		'steps'             => $steps,
		'boundaries'        => array(
			'public surface metadata only',
			'no visitor tracking',
			'no cookies required',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application route itinerary REST route.
 */
function world_of_wordpress_register_application_route_itinerary_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-route-itinerary',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_route_itinerary_rest_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'intent' => array(
					'description'       => 'Public route intent: overview, visitor, operator, runtime, content, or signals.',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);
}

/**
 * Return route itinerary data for REST consumers.
 *
 * @param WP_REST_Request $request REST request.
 * @return array<string,mixed> Public route itinerary.
 */
function world_of_wordpress_get_application_route_itinerary_rest_response( WP_REST_Request $request ): array {
	return world_of_wordpress_get_application_route_itinerary_data( (string) $request->get_param( 'intent' ) );
}

/**
 * Render public route itinerary for visitors and future panels.
 *
 * @return string Safe route itinerary markup.
 */
function world_of_wordpress_render_application_route_itinerary_shortcode(): string {
	static $instance = 0;

	++$instance;

	$itinerary  = world_of_wordpress_get_application_route_itinerary_data();
	$rest_url   = rest_url( 'world-of-wordpress/v1/application-route-itinerary' );
	$readout_id = 'world-application-route-itinerary-readout-' . $instance;
	$buttons_id = 'world-application-route-itinerary-buttons-' . $instance;

	ob_start();
	?>
	<div class="world-application-route-itinerary" aria-label="World of WordPress public route itinerary">
		<section class="route-itinerary-card route-itinerary-card-primary">
			<h3><?php echo esc_html__( 'Application route itinerary', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html( (string) ( $itinerary['purpose'] ?? '' ) ); ?></p>
		</section>
		<div id="<?php echo esc_attr( $buttons_id ); ?>" class="route-itinerary-buttons" data-route-itinerary-endpoint="<?php echo esc_url( $rest_url ); ?>" data-route-itinerary-readout="<?php echo esc_attr( $readout_id ); ?>">
			<?php foreach ( (array) ( $itinerary['supported_intents'] ?? array() ) as $intent ) : ?>
				<button type="button" data-route-intent="<?php echo esc_attr( (string) $intent ); ?>"><?php echo esc_html( (string) $intent ); ?></button>
			<?php endforeach; ?>
		</div>
		<pre id="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Waiting for public route itinerary…', 'world-of-wordpress' ); ?></pre>
		<script>
		(function () {
			const buttons = document.getElementById( <?php echo wp_json_encode( $buttons_id ); ?> );
			if ( ! buttons || ! window.fetch ) {
				return;
			}

			const readout = document.getElementById( buttons.dataset.routeItineraryReadout );
			if ( ! readout ) {
				return;
			}

			const loadItinerary = ( intent ) => {
				const endpoint = buttons.dataset.routeItineraryEndpoint + '?intent=' + encodeURIComponent( intent || 'overview' );
				readout.textContent = 'Fetching ' + ( intent || 'overview' ) + ' route itinerary…';
				fetch( endpoint, { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Application route itinerary unavailable' );
						}

						return response.json();
					} )
					.then( ( itineraryData ) => {
						readout.textContent = JSON.stringify( {
							intent: itineraryData.intent,
							steps: Array.isArray( itineraryData.steps ) ? itineraryData.steps.map( ( step ) => ( { position: step.position, slug: step.slug, group: step.group, endpoint: step.detail && step.detail.endpoint } ) ) : [],
							boundaries: itineraryData.boundaries
						}, null, 2 );
					} )
					.catch( () => {
						readout.textContent = 'The route itinerary could not be fetched in this runtime.';
					} );
			};

			buttons.addEventListener( 'click', ( event ) => {
				const button = event.target.closest( 'button[data-route-intent]' );
				if ( button ) {
					loadItinerary( button.dataset.routeIntent );
				}
			} );

			loadItinerary( 'overview' );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return a compact public brief assembled from the application route itinerary.
 *
 * @param string $intent Optional visitor or operator intent.
 * @return array<string,mixed> Public route brief data.
 */
function world_of_wordpress_get_application_route_brief_data( string $intent = 'overview' ): array {
	$itinerary = world_of_wordpress_get_application_route_itinerary_data( $intent );
	$steps     = array_values( array_filter( (array) ( $itinerary['steps'] ?? array() ), 'is_array' ) );
	$brief     = array();

	foreach ( array_slice( $steps, 0, 5 ) as $step ) {
		$detail  = is_array( $step['detail'] ?? null ) ? $step['detail'] : array();
		$brief[] = array(
			'position' => (int) ( $step['position'] ?? 0 ),
			'slug'     => (string) ( $step['slug'] ?? '' ),
			'group'    => (string) ( $step['group'] ?? '' ),
			'endpoint' => (string) ( $detail['endpoint'] ?? '' ),
		);
	}

	$first_step = $steps[0] ?? array();
	$last_step  = ! empty( $steps ) ? $steps[ count( $steps ) - 1 ] : array();

	return array(
		'name'              => 'World of WordPress application route brief',
		'purpose'           => 'Compresses an ordered public itinerary into a small guide that panels, agents, and visitors can read quickly.',
		'intent'            => (string) ( $itinerary['intent'] ?? 'overview' ),
		'supported_intents' => (array) ( $itinerary['supported_intents'] ?? array() ),
		'source'            => '/wp-json/world-of-wordpress/v1/application-route-itinerary',
		'summary'           => array(
			'total_steps' => count( $steps ),
			'first_stop'  => (string) ( $first_step['slug'] ?? '' ),
			'last_stop'   => (string) ( $last_step['slug'] ?? '' ),
			'use'         => 'Use this brief when a surface needs a compact next-route hint instead of the full itinerary payload.',
		),
		'brief'             => $brief,
		'boundaries'        => array(
			'public surface metadata only',
			'no visitor tracking',
			'no cookies required',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application route brief REST route.
 */
function world_of_wordpress_register_application_route_brief_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-route-brief',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_route_brief_rest_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'intent' => array(
					'description'       => 'Public route intent: overview, visitor, operator, runtime, content, or signals.',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);
}

/**
 * Return route brief data for REST consumers.
 *
 * @param WP_REST_Request $request REST request.
 * @return array<string,mixed> Public route brief.
 */
function world_of_wordpress_get_application_route_brief_rest_response( WP_REST_Request $request ): array {
	return world_of_wordpress_get_application_route_brief_data( (string) $request->get_param( 'intent' ) );
}

/**
 * Render public route brief for visitors and future panels.
 *
 * @return string Safe route brief markup.
 */
function world_of_wordpress_render_application_route_brief_shortcode(): string {
	static $instance = 0;

	++$instance;

	$brief      = world_of_wordpress_get_application_route_brief_data();
	$rest_url   = rest_url( 'world-of-wordpress/v1/application-route-brief' );
	$readout_id = 'world-application-route-brief-readout-' . $instance;
	$buttons_id = 'world-application-route-brief-buttons-' . $instance;

	ob_start();
	?>
	<div class="world-application-route-brief" aria-label="World of WordPress public route brief">
		<section class="route-brief-card route-brief-card-primary">
			<h3><?php echo esc_html__( 'Application route brief', 'world-of-wordpress' ); ?></h3>
			<p><?php echo esc_html( (string) ( $brief['purpose'] ?? '' ) ); ?></p>
		</section>
		<div id="<?php echo esc_attr( $buttons_id ); ?>" class="route-brief-buttons" data-route-brief-endpoint="<?php echo esc_url( $rest_url ); ?>" data-route-brief-readout="<?php echo esc_attr( $readout_id ); ?>">
			<?php foreach ( (array) ( $brief['supported_intents'] ?? array() ) as $intent ) : ?>
				<button type="button" data-route-intent="<?php echo esc_attr( (string) $intent ); ?>"><?php echo esc_html( (string) $intent ); ?></button>
			<?php endforeach; ?>
		</div>
		<pre id="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Waiting for public route brief…', 'world-of-wordpress' ); ?></pre>
		<script>
		(function () {
			const buttons = document.getElementById( <?php echo wp_json_encode( $buttons_id ); ?> );
			if ( ! buttons || ! window.fetch ) {
				return;
			}

			const readout = document.getElementById( buttons.dataset.routeBriefReadout );
			if ( ! readout ) {
				return;
			}

			const loadBrief = ( intent ) => {
				const endpoint = buttons.dataset.routeBriefEndpoint + '?intent=' + encodeURIComponent( intent || 'overview' );
				readout.textContent = 'Fetching ' + ( intent || 'overview' ) + ' route brief…';
				fetch( endpoint, { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Application route brief unavailable' );
						}

						return response.json();
					} )
					.then( ( briefData ) => {
						readout.textContent = JSON.stringify( {
							intent: briefData.intent,
							summary: briefData.summary,
							brief: briefData.brief,
							boundaries: briefData.boundaries
						}, null, 2 );
					} )
					.catch( () => {
						readout.textContent = 'The route brief could not be fetched in this runtime.';
					} );
			};

			buttons.addEventListener( 'click', ( event ) => {
				const button = event.target.closest( 'button[data-route-intent]' );
				if ( button ) {
					loadBrief( button.dataset.routeIntent );
				}
			} );

			loadBrief( 'overview' );
		}());
		</script>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Return the no-storage visitor challenge deck used by the action launcher.
 *
 * The deck is intentionally public and lightweight. It stores no score, sets no
 * cookie, and keeps any round state inside the visitor's current page only.
 *
 * @return array<string,mixed> Public challenge deck data.
 */
function world_of_wordpress_get_visitor_challenge_deck_data(): array {
	return array(
		'name'             => 'World of WordPress visitor challenge deck',
		'purpose'          => 'Three tiny in-page challenges that make visitors learn the world by choosing, not by reading a long console. The deck is directly linkable from public action cards and the footer dock.',
		'state'            => 'current page memory only; no persistence',
		'completion_label' => 'Perfect run. You found the living path without leaving a trace.',
		'incomplete_label' => 'Run complete. The path still opens; try again only if you want a cleaner run.',
		'retry_label'      => 'Try for perfect',
		'reward'           => array(
			'label' => 'Continue to next path',
			'href'  => home_url( '/#visitor-choice-dial' ),
		),
		'draws'            => array(
			array(
				'label'  => 'Touch the dock, then choose one action before reading anything else.',
				'action' => 'choose',
			),
			array(
				'label'  => 'Inspect the engine and name one runtime fact you can see.',
				'action' => 'inspect',
			),
			array(
				'label'  => 'Open field notes and skim only the first visible paragraph.',
				'action' => 'read',
			),
			array(
				'label'  => 'Take the tiny challenge. Leave no account, cookie, score, or trace.',
				'action' => 'play',
			),
			array(
				'label'  => 'Roll a path and follow the first stop that appears.',
				'action' => 'random',
			),
		),
		'prompts'          => array(
			array(
				'id'          => 'living-root',
				'question'    => 'I carry the durable body, accept reviewable mutations, and wake the world again. What am I?',
				'correct_key' => 'repository',
				'success'     => 'Correct. The repository is the durable body; the runtime is the dream; the pull request is the mutation.',
				'failure'     => 'Not quite. The repository is the durable body, review bench, and return path.',
				'answers'     => array(
					array( 'key' => 'runtime', 'label' => 'Runtime' ),
					array( 'key' => 'repository', 'label' => 'Repository' ),
					array( 'key' => 'shortcode', 'label' => 'Shortcode' ),
				),
			),
			array(
				'id'          => 'safe-weather',
				'question'    => 'Which public readout can inspect the engine without exposing private mailbox payloads or visitor data?',
				'correct_key' => 'runtime-weather',
				'success'     => 'Yes. Runtime Weather reports safe public engine facts while refusing private context.',
				'failure'     => 'The safe answer is Runtime Weather: public engine facts only, no private payloads.',
				'answers'     => array(
					array( 'key' => 'runtime-weather', 'label' => 'Runtime Weather' ),
					array( 'key' => 'hidden-memory', 'label' => 'Hidden memory' ),
					array( 'key' => 'visitor-log', 'label' => 'Visitor log' ),
				),
			),
			array(
				'id'          => 'first-move',
				'question'    => 'A human arrives with no patience for lore. Which action should move first?',
				'correct_key' => 'choose-path',
				'success'     => 'Exactly. Choose a path first; deeper systems can wait until they are asked for.',
				'failure'     => 'The strongest first move is Choose a path: action before explanation.',
				'answers'     => array(
					array( 'key' => 'read-registry', 'label' => 'Read the registry' ),
					array( 'key' => 'choose-path', 'label' => 'Choose a path' ),
					array( 'key' => 'dump-json', 'label' => 'Dump JSON' ),
				),
			),
		),
		'privacy_boundary' => array(
			'no visitor tracking',
			'no cookies',
			'no accounts',
			'no stored score',
			'no database writes',
		),
	);
}

/**
 * Return safe public action launcher data.
 *
 * @param string $action Optional public action key.
 * @return array<string,mixed> Public action launcher data.
 */
function world_of_wordpress_get_application_action_launcher_data( string $action = 'choose' ): array {
	$actions = array(
		'choose'  => array(
			'label'       => 'Choose a path',
			'intent'      => 'visitor',
			'description' => 'Open the visitor route for an immediate posture and next step.',
			'href'        => home_url( '/#visitor-choice-dial' ),
			'cta'         => 'Open choice route',
		),
		'inspect' => array(
			'label'       => 'Inspect the engine',
			'intent'      => 'runtime',
			'description' => 'Jump toward live runtime and application-interface surfaces.',
			'href'        => home_url( '/#day-cycle-runtime-weather' ),
			'cta'         => 'Open engine readout',
		),
		'read'    => array(
			'label'       => 'Read field notes',
			'intent'      => 'content',
			'description' => 'Find public writing and content routes without decoding the whole archive.',
			'href'        => home_url( '/#latest-field-notes-intro' ),
			'cta'         => 'Open field notes',
		),
		'signal'  => array(
			'label'       => 'Send a signal',
			'intent'      => 'signals',
			'description' => 'Find the public signal surfaces and the mailbox-facing route.',
			'href'        => 'https://github.com/chubes4/world-of-wordpress/issues/new',
			'cta'         => 'Open mailbox',
		),
		'operate' => array(
			'label'       => 'See the workshop',
			'intent'      => 'operator',
			'description' => 'Open the operator route for day-cycle, review, and build context.',
			'href'        => home_url( '/#day-cycle-flow-console' ),
			'cta'         => 'Open workshop',
		),
		'play'    => array(
			'label'       => 'Take a challenge',
			'intent'      => 'visitor',
			'description' => 'Open a tiny no-account visitor challenge inside the action dock.',
			'href'        => home_url( '/#world-action-launcher-challenge' ),
			'cta'         => 'Open challenge',
		),
	);

	$quick_tour = array(
		'name'        => 'Three-move quick tour',
		'description' => 'A no-storage starter route for impatient visitors: choose, inspect, then read one field note.',
		'steps'       => array(
			array(
				'action' => 'choose',
				'label'  => 'Choose a path',
				'hint'   => 'Start with a visible choice instead of the long map.',
			),
			array(
				'action' => 'inspect',
				'label'  => 'Inspect the engine',
				'hint'   => 'Confirm this is live WordPress application software.',
			),
			array(
				'action' => 'read',
				'label'  => 'Read one field note',
				'hint'   => 'Leave after the first paragraph if the world has not earned more time.',
			),
		),
	);

	$missions = array(
		'first-minute' => array(
			'label'       => 'First minute',
			'description' => 'Choose a path, inspect the engine, then read one field note. Three moves, no account, no storage.',
			'actions'     => array( 'choose', 'inspect', 'read' ),
			'cta'         => 'Begin first minute',
			'href'        => home_url( '/#visitor-choice-dial' ),
		),
		'prove-live'   => array(
			'label'       => 'Prove it is live',
			'description' => 'Jump straight to runtime evidence, then open the workshop if the machinery earns your attention.',
			'actions'     => array( 'inspect', 'operate' ),
			'cta'         => 'Inspect live engine',
			'href'        => home_url( '/#day-cycle-runtime-weather' ),
		),
		'play-now'     => array(
			'label'       => 'Play now',
			'description' => 'Open the tiny challenge first. The path continues whether the run is perfect or imperfect.',
			'actions'     => array( 'play', 'choose' ),
			'cta'         => 'Open challenge',
			'href'        => home_url( '/#world-action-launcher-challenge' ),
		),
	);

	$action = sanitize_key( $action );
	if ( ! isset( $actions[ $action ] ) ) {
		$action = 'choose';
	}

	$selected    = $actions[ $action ];
	$route_brief = world_of_wordpress_get_application_route_brief_data( (string) $selected['intent'] );

	return array(
		'name'              => 'World of WordPress application action launcher',
		'purpose'           => 'A global public dock that gives visitors one-click actions and tiny starter missions without accounts, cookies, hidden state, or a wall of explanation.',
		'action'            => $action,
		'selected'          => $selected,
		'actions'           => $actions,
		'primary_action'    => 'choose',
		'quick_tour'        => $quick_tour,
		'missions'          => $missions,
		'challenge_deck'    => world_of_wordpress_get_visitor_challenge_deck_data(),
		'route_brief'       => $route_brief,
		'interface'         => '/wp-json/world-of-wordpress/v1/application-action-launcher?action=' . rawurlencode( $action ),
		'privacy_boundary'  => array(
			'public route metadata only',
			'no visitor tracking',
			'no cookies required',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Register the public application action launcher REST route.
 */
function world_of_wordpress_register_application_action_launcher_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-action-launcher',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_action_launcher_rest_response',
			'permission_callback' => '__return_true',
			'args'                => array(
				'action' => array(
					'description'       => 'Public launcher action: choose, inspect, read, signal, operate, or play.',
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);
}

/**
 * Return action launcher data for REST consumers.
 *
 * @param WP_REST_Request $request REST request.
 * @return array<string,mixed> Public action launcher data.
 */
function world_of_wordpress_get_application_action_launcher_rest_response( WP_REST_Request $request ): array {
	return world_of_wordpress_get_application_action_launcher_data( (string) $request->get_param( 'action' ) );
}

/**
 * Return compact public action cards derived from the launcher actions.
 *
 * @return array<string,mixed> Public action cards data.
 */
function world_of_wordpress_get_application_action_cards_data(): array {
	$launcher = world_of_wordpress_get_application_action_launcher_data();
	$cards    = array();

	foreach ( (array) ( $launcher['actions'] ?? array() ) as $action_key => $action ) {
		if ( ! is_array( $action ) ) {
			continue;
		}

		$action_data = world_of_wordpress_get_application_action_launcher_data( (string) $action_key );
		$route_brief = is_array( $action_data['route_brief'] ?? null ) ? $action_data['route_brief'] : array();
		$summary     = is_array( $route_brief['summary'] ?? null ) ? $route_brief['summary'] : array();
		$brief       = array_values( array_filter( (array) ( $route_brief['brief'] ?? array() ), 'is_array' ) );
		$first_stop  = is_array( $brief[0] ?? null ) ? $brief[0] : array();

		$cards[] = array(
			'action'      => (string) $action_key,
			'label'       => (string) ( $action['label'] ?? $action_key ),
			'verb'        => (string) ( $action['label'] ?? $action_key ),
			'intent'      => (string) ( $action['intent'] ?? '' ),
			'description' => (string) ( $action['description'] ?? '' ),
			'href'        => (string) ( $action['href'] ?? '' ),
			'cta'         => (string) ( $action['cta'] ?? 'Open' ),
			'endpoint'    => '/wp-json/world-of-wordpress/v1/application-action-launcher?action=' . rawurlencode( (string) $action_key ),
			'first_stop'  => array(
				'slug'     => (string) ( $first_stop['slug'] ?? $summary['first_stop'] ?? '' ),
				'group'    => (string) ( $first_stop['group'] ?? '' ),
				'endpoint' => (string) ( $first_stop['endpoint'] ?? '' ),
			),
		);
	}

	return array(
		'name'             => 'World of WordPress application action cards',
		'purpose'          => 'A compact public action layer for visitors and panels that need verbs, targets, and first stops without parsing long route consoles.',
		'source'           => '/wp-json/world-of-wordpress/v1/application-action-launcher',
		'cards'            => $cards,
		'privacy_boundary' => array(
			'public route metadata only',
			'no visitor tracking',
			'no cookies required',
			'no private mailbox payloads',
			'no credentials',
			'no hidden agent memory',
			'no database writes',
		),
	);
}

/**
 * Render compact public action cards for visitors and future panels.
 *
 * @return string Safe action-card markup.
 */
function world_of_wordpress_render_application_action_cards_shortcode( $attributes = array() ): string {
	static $instance = 0;

	++$instance;

	$attributes = shortcode_atts(
		array(
			'heading'         => __( 'Application action cards', 'world-of-wordpress' ),
			'show_rest_echo'  => true,
			'show_route_meta' => true,
			'surface'         => 'shortcode',
		),
		is_array( $attributes ) ? $attributes : array(),
		'world_application_action_cards'
	);

	$cards_data      = world_of_wordpress_get_application_action_cards_data();
	$show_rest_echo  = filter_var( $attributes['show_rest_echo'], FILTER_VALIDATE_BOOLEAN );
	$show_route_meta = filter_var( $attributes['show_route_meta'], FILTER_VALIDATE_BOOLEAN );
	$surface         = sanitize_html_class( (string) $attributes['surface'] );
	$rest_url        = rest_url( 'world-of-wordpress/v1/application-action-cards' );
	$readout_id      = 'world-application-action-cards-readout-' . $instance;

	ob_start();
	?>
	<div class="world-application-action-cards world-application-action-cards-<?php echo esc_attr( $surface ); ?>" aria-label="World of WordPress public action cards">
		<section class="action-cards-card action-cards-card-primary">
			<h3><?php echo esc_html( (string) $attributes['heading'] ); ?></h3>
			<p><?php echo esc_html( (string) ( $cards_data['purpose'] ?? '' ) ); ?></p>
		</section>
		<div class="action-cards-grid" aria-label="Immediate public world actions">
			<?php foreach ( (array) ( $cards_data['cards'] ?? array() ) as $card ) : ?>
				<?php $card = is_array( $card ) ? $card : array(); ?>
				<section class="action-card">
					<strong><?php echo esc_html( (string) ( $card['verb'] ?? $card['label'] ?? '' ) ); ?></strong>
					<p><?php echo esc_html( (string) ( $card['description'] ?? '' ) ); ?></p>
					<?php $action_href = (string) ( $card['href'] ?? '' ); ?>
					<?php if ( '' !== $action_href ) : ?>
						<a class="action-card-link" href="<?php echo esc_url( $action_href ); ?>"><?php echo esc_html( (string) ( $card['cta'] ?? __( 'Open action', 'world-of-wordpress' ) ) ); ?></a>
					<?php endif; ?>
					<?php if ( $show_route_meta ) : ?>
						<div class="action-card-meta">
							<span><?php echo esc_html( 'intent: ' . (string) ( $card['intent'] ?? '' ) ); ?></span>
							<?php $first_stop = is_array( $card['first_stop'] ?? null ) ? $card['first_stop'] : array(); ?>
							<span><?php echo esc_html( 'first stop: ' . (string) ( $first_stop['slug'] ?? '' ) ); ?></span>
						</div>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
		<?php if ( $show_rest_echo ) : ?>
			<section class="action-cards-rest-echo" aria-label="Public application action cards REST echo">
				<h3><?php echo esc_html__( 'Action cards REST echo', 'world-of-wordpress' ); ?></h3>
				<p><?php echo esc_html__( 'The same compact action cards are fetched through the public REST endpoint so panels can render verbs without parsing the footer dock.', 'world-of-wordpress' ); ?></p>
				<pre id="<?php echo esc_attr( $readout_id ); ?>" data-action-cards-endpoint="<?php echo esc_url( $rest_url ); ?>"><?php echo esc_html__( 'Waiting for public action cards…', 'world-of-wordpress' ); ?></pre>
			</section>
			<script>
			(function () {
				const readout = document.getElementById( <?php echo wp_json_encode( $readout_id ); ?> );
				if ( ! readout || ! window.fetch ) {
					return;
				}

				fetch( readout.dataset.actionCardsEndpoint, { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Application action cards unavailable' );
						}

						return response.json();
					} )
					.then( ( data ) => {
						readout.textContent = JSON.stringify( {
							name: data.name,
							cards: Array.isArray( data.cards ) ? data.cards.map( ( card ) => ( { action: card.action, verb: card.verb, firstStop: card.first_stop && card.first_stop.slug } ) ) : [],
							privacy: data.privacy_boundary
						}, null, 2 );
					} )
					.catch( () => {
						readout.textContent = 'The server-rendered action cards remain visible; the REST echo could not be fetched in this runtime.';
					} );
			}());
			</script>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Register the public application action cards REST route.
 */
function world_of_wordpress_register_application_action_cards_rest_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/application-action-cards',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'world_of_wordpress_get_application_action_cards_data',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * Render the global public action launcher dock.
 *
 * @return void
 */
function world_of_wordpress_render_action_launcher_footer(): void {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return;
	}

	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;

	$launcher       = world_of_wordpress_get_application_action_launcher_data();
	$rest_url       = rest_url( 'world-of-wordpress/v1/application-action-launcher' );
	$actions        = (array) ( $launcher['actions'] ?? array() );
	$quick_tour     = is_array( $launcher['quick_tour'] ?? null ) ? $launcher['quick_tour'] : array();
	$missions       = is_array( $launcher['missions'] ?? null ) ? $launcher['missions'] : array();
	$challenge_deck = is_array( $launcher['challenge_deck'] ?? null ) ? $launcher['challenge_deck'] : world_of_wordpress_get_visitor_challenge_deck_data();
	$prompts        = array_values( array_filter( (array) ( $challenge_deck['prompts'] ?? array() ), 'is_array' ) );
	$draws          = array_values( array_filter( (array) ( $challenge_deck['draws'] ?? array() ), 'is_array' ) );
	$reward         = is_array( $challenge_deck['reward'] ?? null ) ? $challenge_deck['reward'] : array();
	$primary_key    = sanitize_key( (string) ( $launcher['primary_action'] ?? 'choose' ) );
	$primary_action = is_array( $actions[ $primary_key ] ?? null ) ? $actions[ $primary_key ] : array();
	$dock_id        = 'world-action-launcher-dock';
	$panel_id       = 'world-action-launcher-panel';
	$readout_id     = 'world-action-launcher-readout';
	?>
	<style>
		.world-action-launcher {
			position: fixed;
			right: clamp(12px, 3vw, 28px);
			bottom: clamp(12px, 3vw, 28px);
			z-index: 40;
			width: fit-content;
			max-width: min(420px, calc(100vw - 24px));
			padding: 8px;
			border: 1px solid rgba(255, 255, 255, 0.22);
			border-radius: 999px;
			background: rgba(14, 18, 31, 0.92);
			box-shadow: 0 22px 70px rgba(0, 0, 0, 0.38);
			color: #f8fafc;
			backdrop-filter: blur(16px);
		}
		.world-action-launcher.is-open {
			width: min(420px, calc(100vw - 24px));
			padding: 14px;
			border-radius: 22px;
		}
		.world-action-launcher-toggle,
		.world-action-launcher-start-now,
		.world-action-launcher-primary,
		.world-action-launcher-surprise,
		.world-action-launcher-roll,
		.world-action-launcher-draw,
		.world-action-launcher-tour-next,
		.world-action-launcher-mission,
		.world-action-launcher-mission-next {
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 0.45rem;
			border: 0;
			border-radius: 999px;
			padding: 0.7rem 0.92rem;
			font-weight: 900;
			font-size: 0.86rem;
			line-height: 1;
			background: #a7f3d0;
			color: #111827;
			box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
			text-decoration: none;
		}
		.world-action-launcher-start-now,
		.world-action-launcher-primary,
		.world-action-launcher-surprise {
			margin-left: 0.35rem;
		}
		.world-action-launcher-start-now {
			background: #a7f3d0;
		}
		.world-action-launcher-primary {
			background: #f8fafc;
		}
		.world-action-launcher-surprise {
			background: #fde68a;
		}
		.world-action-launcher-roll,
		.world-action-launcher-draw {
			margin: 0 0.42rem 0.72rem 0;
			background: #fde68a;
			box-shadow: none;
		}
		.world-action-launcher-draw {
			background: #c4b5fd;
		}
		.world-action-launcher-missions {
			display: grid;
			grid-template-columns: 1fr;
			gap: 0.48rem;
			margin: 0.7rem 0;
		}
		.world-action-launcher-mission-card {
			display: grid;
			gap: 0.45rem;
			padding: 0.65rem;
			border: 1px solid rgba(167, 243, 208, 0.22);
			border-radius: 16px;
			background: rgba(167, 243, 208, 0.08);
		}
		.world-action-launcher-mission-card p {
			margin: 0;
		}
		.world-action-launcher-mission-steps {
			display: flex;
			flex-wrap: wrap;
			gap: 0.32rem;
			margin: 0.05rem 0 0.1rem;
			padding: 0;
			list-style: none;
		}
		.world-action-launcher-mission-steps li {
			margin: 0;
			border: 1px solid rgba(167, 243, 208, 0.24);
			border-radius: 999px;
			padding: 0.24rem 0.42rem;
			background: rgba(15, 23, 42, 0.28);
			color: rgba(248, 250, 252, 0.78);
			font-size: 0.68rem;
			font-weight: 800;
			line-height: 1;
		}
		.world-action-launcher-mission-card.is-active {
			border-color: rgba(253, 230, 138, 0.72);
			background: rgba(253, 230, 138, 0.13);
		}
		.world-action-launcher-mission-steps li.is-current {
			border-color: rgba(253, 230, 138, 0.9);
			background: #fde68a;
			color: #111827;
		}
		.world-action-launcher-mission-steps li.is-complete {
			border-color: rgba(167, 243, 208, 0.7);
			background: rgba(167, 243, 208, 0.3);
			color: #f8fafc;
		}
		.world-action-launcher-mission,
		.world-action-launcher-mission-next {
			width: fit-content;
			background: #a7f3d0;
			box-shadow: none;
		}
		.world-action-launcher-mission-status {
			margin: 0.4rem 0 0;
			font-size: 0.76rem;
			color: rgba(248, 250, 252, 0.76);
		}
		.world-action-launcher-mission-next {
			margin: 0.35rem 0 0;
			background: #fde68a;
		}
		.world-action-launcher-tour {
			margin: 0.7rem 0;
			padding: 0.7rem;
			border: 1px solid rgba(253, 230, 138, 0.32);
			border-radius: 16px;
			background: rgba(253, 230, 138, 0.1);
		}
		.world-action-launcher-tour ol {
			margin: 0.5rem 0 0;
			padding-left: 1.1rem;
		}
		.world-action-launcher-tour li {
			margin: 0.35rem 0;
			color: rgba(248, 250, 252, 0.78);
		}
		.world-action-launcher-tour li.is-current {
			color: #fde68a;
			font-weight: 900;
		}
		.world-action-launcher-tour-next {
			margin-top: 0.58rem;
			background: #fde68a;
			box-shadow: none;
		}
		.world-action-launcher.is-open .world-action-launcher-start-now,
		.world-action-launcher.is-open .world-action-launcher-primary,
		.world-action-launcher.is-open .world-action-launcher-surprise {
			display: none;
		}
		.world-action-launcher-toggle:hover,
		.world-action-launcher-toggle:focus,
		.world-action-launcher-start-now:hover,
		.world-action-launcher-start-now:focus,
		.world-action-launcher-primary:hover,
		.world-action-launcher-primary:focus,
		.world-action-launcher-surprise:hover,
		.world-action-launcher-surprise:focus,
		.world-action-launcher-roll:hover,
		.world-action-launcher-roll:focus,
		.world-action-launcher-draw:hover,
		.world-action-launcher-draw:focus,
		.world-action-launcher-tour-next:hover,
		.world-action-launcher-tour-next:focus,
		.world-action-launcher-mission:hover,
		.world-action-launcher-mission:focus,
		.world-action-launcher-mission-next:hover,
		.world-action-launcher-mission-next:focus {
			background: #f8fafc;
			outline: 2px solid rgba(167, 243, 208, 0.55);
			outline-offset: 2px;
		}
		.world-action-launcher-panel {
			margin-top: 0.7rem;
		}
		.world-action-launcher-panel[hidden] {
			display: none;
		}
		.world-action-launcher strong {
			display: block;
			font-size: 0.78rem;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: rgba(248, 250, 252, 0.72);
		}
		.world-action-launcher p {
			margin: 0.3rem 0 0.7rem;
			font-size: 0.86rem;
			line-height: 1.35;
			color: rgba(248, 250, 252, 0.8);
		}
		.world-action-launcher-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0.5rem;
		}
		.world-action-launcher-card {
			display: grid;
			gap: 0.42rem;
			padding: 0.65rem;
			border: 1px solid rgba(255, 255, 255, 0.14);
			border-radius: 16px;
			background: rgba(255, 255, 255, 0.07);
		}
		.world-action-launcher-card-label {
			font-size: 0.7rem;
			font-weight: 800;
			letter-spacing: 0.05em;
			text-transform: uppercase;
			color: rgba(248, 250, 252, 0.68);
		}
		.world-action-launcher-link,
		.world-action-launcher-route {
			cursor: pointer;
			border: 0;
			border-radius: 999px;
			padding: 0.58rem 0.7rem;
			font-weight: 800;
			font-size: 0.78rem;
			line-height: 1;
			text-align: center;
			text-decoration: none;
		}
		.world-action-launcher-link {
			background: #a7f3d0;
			color: #111827;
		}
		.world-action-launcher-tools {
			margin-top: 0.62rem;
			border-top: 1px solid rgba(255, 255, 255, 0.14);
			padding-top: 0.58rem;
		}
		.world-action-launcher-tools summary {
			cursor: pointer;
			width: fit-content;
			font-size: 0.78rem;
			font-weight: 800;
			color: rgba(248, 250, 252, 0.78);
		}
		.world-action-launcher-route-grid {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0.45rem;
			margin-top: 0.55rem;
		}
		.world-action-launcher-route {
			background: rgba(255, 255, 255, 0.12);
			color: #f8fafc;
			border: 1px solid rgba(255, 255, 255, 0.18);
		}
		.world-action-launcher-link:hover,
		.world-action-launcher-link:focus,
		.world-action-launcher-route:hover,
		.world-action-launcher-route:focus,
		.world-action-launcher-tools summary:focus {
			background: #f8fafc;
			color: #111827;
			outline: 2px solid rgba(167, 243, 208, 0.55);
			outline-offset: 2px;
		}
		.world-action-launcher-output,
		.world-action-launcher-challenge {
			margin-top: 0.75rem;
			padding: 0.72rem;
			border-radius: 16px;
			background: rgba(255, 255, 255, 0.08);
			font-size: 0.78rem;
			line-height: 1.35;
			white-space: normal;
		}
		.world-action-launcher-output[hidden],
		.world-action-launcher-go[hidden],
		.world-action-launcher-mission-status[hidden],
		.world-action-launcher-mission-next[hidden],
		.world-action-launcher-challenge[hidden],
		.world-action-launcher-reward[hidden],
		.world-action-launcher-retry[hidden] {
			display: none;
		}
		.world-action-launcher-go {
			display: inline-flex;
			margin-top: 0.58rem;
			border-radius: 999px;
			padding: 0.58rem 0.78rem;
			background: #a7f3d0;
			color: #111827;
			font-size: 0.78rem;
			font-weight: 900;
			line-height: 1;
			text-decoration: none;
		}
		.world-action-launcher-go:hover,
		.world-action-launcher-go:focus {
			background: #f8fafc;
			color: #111827;
			outline: 2px solid rgba(167, 243, 208, 0.55);
			outline-offset: 2px;
		}
		.world-action-launcher-reward,
		.world-action-launcher-retry {
			display: inline-flex;
			margin-top: 0.6rem;
			margin-right: 0.45rem;
			border: 0;
			border-radius: 999px;
			padding: 0.52rem 0.72rem;
			background: #f8fafc;
			color: #111827;
			font-weight: 900;
			font-size: 0.76rem;
			text-decoration: none;
			cursor: pointer;
		}
		.world-action-launcher-retry {
			background: rgba(255, 255, 255, 0.14);
			color: #f8fafc;
			border: 1px solid rgba(255, 255, 255, 0.18);
		}
		.world-action-launcher-riddle-options {
			display: flex;
			flex-wrap: wrap;
			gap: 0.42rem;
			margin-top: 0.55rem;
		}
		.world-action-launcher-riddle-options button {
			cursor: pointer;
			border: 1px solid rgba(255, 255, 255, 0.18);
			border-radius: 999px;
			padding: 0.42rem 0.58rem;
			background: rgba(255, 255, 255, 0.1);
			color: #f8fafc;
			font-size: 0.74rem;
			font-weight: 800;
		}
		.world-action-launcher-riddle-options button:hover,
		.world-action-launcher-riddle-options button:focus {
			background: #f8fafc;
			color: #111827;
			outline: 2px solid rgba(167, 243, 208, 0.55);
			outline-offset: 2px;
		}
		.world-action-launcher-riddle-options button[disabled] {
			cursor: not-allowed;
			opacity: 0.62;
		}
		.world-action-launcher-prompt.is-answered .world-action-launcher-riddle-options button[aria-pressed="true"] {
			background: #a7f3d0;
			border-color: rgba(167, 243, 208, 0.8);
			color: #064e3b;
		}
		@media (max-width: 700px) {
			.world-action-launcher {
				left: 12px;
				right: 12px;
				bottom: 12px;
				width: fit-content;
			}
			.world-action-launcher.is-open {
				width: auto;
			}
		}
	</style>
	<aside id="<?php echo esc_attr( $dock_id ); ?>" class="world-action-launcher" aria-label="World of WordPress action launcher" data-action-launcher-endpoint="<?php echo esc_url( $rest_url ); ?>" data-action-launcher-readout="<?php echo esc_attr( $readout_id ); ?>" data-action-launcher-panel="<?php echo esc_attr( $panel_id ); ?>">
		<button class="world-action-launcher-toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
			<?php echo esc_html__( 'More paths', 'world-of-wordpress' ); ?>
		</button>
		<button class="world-action-launcher-start-now" type="button" data-world-start-now aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Start now', 'world-of-wordpress' ); ?></button>
		<?php if ( ! empty( $primary_action['href'] ) ) : ?>
			<a class="world-action-launcher-primary" href="<?php echo esc_url( (string) $primary_action['href'] ); ?>"><?php echo esc_html( (string) ( $primary_action['cta'] ?? __( 'Start', 'world-of-wordpress' ) ) ); ?></a>
		<?php endif; ?>
		<button class="world-action-launcher-surprise" type="button" data-world-surprise aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Surprise me', 'world-of-wordpress' ); ?></button>
		<div id="<?php echo esc_attr( $panel_id ); ?>" class="world-action-launcher-panel" hidden>
			<strong><?php echo esc_html__( 'Choose and move', 'world-of-wordpress' ); ?></strong>
			<p><?php echo esc_html__( 'Pick one tiny mission. Extra route tools stay folded until you ask for them.', 'world-of-wordpress' ); ?></p>
			<?php if ( ! empty( $missions ) ) : ?>
				<div class="world-action-launcher-missions" aria-label="Tiny starter missions">
					<?php foreach ( $missions as $mission_key => $mission ) : ?>
						<?php
						$mission = is_array( $mission ) ? $mission : array();
						$mission_actions = array_values( array_filter( array_map( 'sanitize_key', (array) ( $mission['actions'] ?? array() ) ) ) );
						$mission_action_labels = array();
						foreach ( $mission_actions as $mission_action ) {
							$mission_action_data = is_array( $actions[ $mission_action ] ?? null ) ? $actions[ $mission_action ] : array();
							$mission_action_labels[] = (string) ( $mission_action_data['label'] ?? $mission_action );
						}
						?>
						<section class="world-action-launcher-mission-card" data-world-mission-card="<?php echo esc_attr( (string) $mission_key ); ?>">
							<strong><?php echo esc_html( (string) ( $mission['label'] ?? $mission_key ) ); ?></strong>
							<p><?php echo esc_html( (string) ( $mission['description'] ?? '' ) ); ?></p>
							<?php if ( ! empty( $mission_actions ) ) : ?>
								<ol class="world-action-launcher-mission-steps" aria-label="<?php echo esc_attr__( 'Mission move preview', 'world-of-wordpress' ); ?>">
									<?php foreach ( $mission_actions as $mission_index => $mission_action ) : ?>
										<?php $mission_action_data = is_array( $actions[ $mission_action ] ?? null ) ? $actions[ $mission_action ] : array(); ?>
										<li data-world-mission-step="<?php echo esc_attr( (string) $mission_index ); ?>"><?php echo esc_html( (string) ( $mission_action_data['label'] ?? $mission_action ) ); ?></li>
									<?php endforeach; ?>
								</ol>
							<?php endif; ?>
							<button class="world-action-launcher-mission" type="button" data-world-mission="<?php echo esc_attr( (string) $mission_key ); ?>" data-world-mission-actions="<?php echo esc_attr( wp_json_encode( $mission_actions ) ); ?>" data-world-mission-labels="<?php echo esc_attr( wp_json_encode( $mission_action_labels ) ); ?>" data-world-mission-href="<?php echo esc_url( (string) ( $mission['href'] ?? '' ) ); ?>" aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html( (string) ( $mission['cta'] ?? __( 'Start mission', 'world-of-wordpress' ) ) ); ?></button>
						</section>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
				<p class="world-action-launcher-mission-status" data-world-mission-status hidden><?php echo esc_html__( 'No mission is active yet.', 'world-of-wordpress' ); ?></p>
				<button class="world-action-launcher-mission-next" type="button" data-world-mission-next aria-describedby="<?php echo esc_attr( $readout_id ); ?>" hidden><?php echo esc_html__( 'Next mission move', 'world-of-wordpress' ); ?></button>
			<div id="<?php echo esc_attr( $readout_id ); ?>" class="world-action-launcher-output" role="status" aria-live="polite" hidden><?php echo esc_html__( 'Choose a mission to receive the next public move.', 'world-of-wordpress' ); ?></div>
			<a class="world-action-launcher-go" href="#" data-world-route-go hidden><?php echo esc_html__( 'Go now', 'world-of-wordpress' ); ?></a>
			<details class="world-action-launcher-tools">
				<summary><?php echo esc_html__( 'Optional route tools', 'world-of-wordpress' ); ?></summary>
				<?php if ( ! empty( $quick_tour['steps'] ) && is_array( $quick_tour['steps'] ) ) : ?>
					<section class="world-action-launcher-tour" data-world-quick-tour aria-label="<?php echo esc_attr( (string) ( $quick_tour['name'] ?? __( 'Quick tour', 'world-of-wordpress' ) ) ); ?>">
						<strong><?php echo esc_html( (string) ( $quick_tour['name'] ?? __( 'Quick tour', 'world-of-wordpress' ) ) ); ?></strong>
						<p><?php echo esc_html( (string) ( $quick_tour['description'] ?? __( 'Move through three starter actions without storing anything.', 'world-of-wordpress' ) ) ); ?></p>
						<ol data-world-quick-tour-steps>
							<?php foreach ( array_values( array_filter( (array) $quick_tour['steps'], 'is_array' ) ) as $tour_index => $tour_step ) : ?>
								<li data-world-tour-step="<?php echo esc_attr( (string) $tour_index ); ?>" data-world-tour-action="<?php echo esc_attr( sanitize_key( (string) ( $tour_step['action'] ?? '' ) ) ); ?>" <?php echo 0 === $tour_index ? 'class="is-current"' : ''; ?>>
									<?php echo esc_html( (string) ( $tour_step['label'] ?? __( 'Move', 'world-of-wordpress' ) ) ); ?> — <?php echo esc_html( (string) ( $tour_step['hint'] ?? '' ) ); ?>
								</li>
							<?php endforeach; ?>
						</ol>
						<button class="world-action-launcher-tour-next" type="button" data-world-quick-tour-next aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Start quick tour', 'world-of-wordpress' ); ?></button>
					</section>
				<?php endif; ?>
				<button class="world-action-launcher-roll" type="button" data-world-roll-path aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Roll a path', 'world-of-wordpress' ); ?></button>
				<?php if ( ! empty( $draws ) ) : ?>
					<button class="world-action-launcher-draw" type="button" data-world-draw-move aria-describedby="<?php echo esc_attr( $readout_id ); ?>"><?php echo esc_html__( 'Draw a move', 'world-of-wordpress' ); ?></button>
				<?php endif; ?>
				<div class="world-action-launcher-grid" aria-label="Direct action links">
					<?php foreach ( $actions as $action_key => $action ) : ?>
						<?php $action = is_array( $action ) ? $action : array(); ?>
						<section class="world-action-launcher-card">
							<span class="world-action-launcher-card-label"><?php echo esc_html( (string) ( $action['label'] ?? $action_key ) ); ?></span>
							<?php if ( 'play' === (string) $action_key ) : ?>
								<button class="world-action-launcher-link" type="button" data-world-open-challenge><?php echo esc_html( (string) ( $action['cta'] ?? __( 'Open challenge', 'world-of-wordpress' ) ) ); ?></button>
							<?php elseif ( ! empty( $action['href'] ) ) : ?>
								<a class="world-action-launcher-link" href="<?php echo esc_url( (string) $action['href'] ); ?>"><?php echo esc_html( (string) ( $action['cta'] ?? __( 'Open', 'world-of-wordpress' ) ) ); ?></a>
							<?php endif; ?>
						</section>
					<?php endforeach; ?>
				</div>
				<div class="world-action-launcher-route-grid" aria-label="Route readout buttons">
					<?php foreach ( $actions as $action_key => $action ) : ?>
						<?php $action = is_array( $action ) ? $action : array(); ?>
						<button class="world-action-launcher-route" type="button" data-world-action="<?php echo esc_attr( (string) $action_key ); ?>"><?php echo esc_html( (string) ( $action['label'] ?? $action_key ) ); ?></button>
					<?php endforeach; ?>
				</div>
			</details>
		<section id="world-action-launcher-challenge" class="world-action-launcher-challenge" data-world-action-challenge data-world-challenge-complete="<?php echo esc_attr( (string) ( $challenge_deck['completion_label'] ?? '' ) ); ?>" data-world-challenge-incomplete="<?php echo esc_attr( (string) ( $challenge_deck['incomplete_label'] ?? '' ) ); ?>" hidden aria-live="polite">
			<strong><?php echo esc_html__( 'Challenge: Find the living path', 'world-of-wordpress' ); ?></strong>
			<p data-world-challenge-progress><?php echo esc_html__( 'Three quick choices. No account, cookie, score table, or tracking is involved.', 'world-of-wordpress' ); ?></p>
			<?php foreach ( $prompts as $prompt_index => $prompt ) : ?>
				<?php
				$answers = array_values( array_filter( (array) ( $prompt['answers'] ?? array() ), 'is_array' ) );
				$step    = (int) $prompt_index + 1;
				?>
				<div class="world-action-launcher-prompt" data-world-challenge-prompt data-world-challenge-step="<?php echo esc_attr( (string) $step ); ?>" data-world-challenge-correct="<?php echo esc_attr( (string) ( $prompt['correct_key'] ?? '' ) ); ?>" data-world-challenge-success="<?php echo esc_attr( (string) ( $prompt['success'] ?? '' ) ); ?>" data-world-challenge-failure="<?php echo esc_attr( (string) ( $prompt['failure'] ?? '' ) ); ?>" <?php echo 0 === $prompt_index ? '' : 'hidden'; ?>>
					<p><?php echo esc_html( (string) ( $prompt['question'] ?? '' ) ); ?></p>
					<div class="world-action-launcher-riddle-options" aria-label="Challenge answers">
						<?php foreach ( $answers as $answer ) : ?>
							<button type="button" data-world-riddle-answer="<?php echo esc_attr( (string) ( $answer['key'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $answer['label'] ?? '' ) ); ?></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
			<p data-world-riddle-result><?php echo esc_html__( 'Choose carefully. The challenge remembers only while this page is open.', 'world-of-wordpress' ); ?></p>
			<button class="world-action-launcher-retry" type="button" data-world-challenge-retry hidden><?php echo esc_html( (string) ( $challenge_deck['retry_label'] ?? __( 'Try again', 'world-of-wordpress' ) ) ); ?></button>
			<?php if ( ! empty( $reward['href'] ) ) : ?>
				<a class="world-action-launcher-reward" href="<?php echo esc_url( (string) $reward['href'] ); ?>" data-world-challenge-reward hidden><?php echo esc_html( (string) ( $reward['label'] ?? __( 'Claim next path', 'world-of-wordpress' ) ) ); ?></a>
			<?php endif; ?>
		</section>
		</div>
		<script>
		(function () {
			const dock = document.getElementById( <?php echo wp_json_encode( $dock_id ); ?> );
			if ( ! dock ) {
				return;
			}

			const readout = document.getElementById( dock.dataset.actionLauncherReadout );
			const panel = document.getElementById( dock.dataset.actionLauncherPanel );
			const toggle = dock.querySelector( '.world-action-launcher-toggle' );
			const startNowButton = dock.querySelector( '[data-world-start-now]' );
			const surpriseButton = dock.querySelector( '[data-world-surprise]' );
			const rollButton = dock.querySelector( '[data-world-roll-path]' );
			const drawButton = dock.querySelector( '[data-world-draw-move]' );
			const routeGoLink = dock.querySelector( '[data-world-route-go]' );
			const quickTour = dock.querySelector( '[data-world-quick-tour]' );
			const quickTourButton = dock.querySelector( '[data-world-quick-tour-next]' );
			const quickTourSteps = quickTour ? Array.from( quickTour.querySelectorAll( '[data-world-tour-step]' ) ) : [];
			const routeButtons = Array.from( dock.querySelectorAll( 'button[data-world-action]' ) );
			const missionButtons = Array.from( dock.querySelectorAll( 'button[data-world-mission-actions]' ) );
			const missionCards = Array.from( dock.querySelectorAll( '[data-world-mission-card]' ) );
			const missionStatus = dock.querySelector( '[data-world-mission-status]' );
			const missionNextButton = dock.querySelector( '[data-world-mission-next]' );
			const challenge = dock.querySelector( '[data-world-action-challenge]' );
			const challengePrompts = challenge ? Array.from( challenge.querySelectorAll( '[data-world-challenge-prompt]' ) ) : [];
			const challengeProgress = challenge ? challenge.querySelector( '[data-world-challenge-progress]' ) : null;
			const challengeResult = dock.querySelector( '[data-world-riddle-result]' );
			const challengeReward = dock.querySelector( '[data-world-challenge-reward]' );
			const challengeRetry = dock.querySelector( '[data-world-challenge-retry]' );
			let challengeStep = 0;
			let challengeCorrect = 0;
			let challengeFinished = false;
			let quickTourStep = 0;
			let activeMission = [];
			let activeMissionStep = 0;
			let activeMissionLabel = '';
			let activeMissionLabels = [];
			let activeMissionKey = '';
			const closedLabel = <?php echo wp_json_encode( __( 'More paths', 'world-of-wordpress' ) ); ?>;
			const openLabel = <?php echo wp_json_encode( __( 'Close paths', 'world-of-wordpress' ) ); ?>;
			const drawMoves = <?php echo wp_json_encode( $draws ); ?>;

			const setOpen = ( isOpen ) => {
				dock.classList.toggle( 'is-open', isOpen );
				if ( panel ) {
					panel.hidden = ! isOpen;
				}
				if ( toggle ) {
					toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
					toggle.textContent = isOpen ? openLabel : closedLabel;
				}
			};

			if ( panel && toggle ) {
				toggle.addEventListener( 'click', () => {
					setOpen( ! dock.classList.contains( 'is-open' ) );
				} );

				dock.addEventListener( 'keydown', ( event ) => {
					if ( 'Escape' === event.key ) {
						setOpen( false );
					}
				} );
			}

			const showChallengeStep = ( step ) => {
				if ( ! challengePrompts.length ) {
					return;
				}

				challengeStep = Math.max( 0, Math.min( step, challengePrompts.length - 1 ) );
				challengePrompts.forEach( ( prompt, index ) => {
					prompt.hidden = index !== challengeStep;
				} );

				if ( challengeProgress ) {
					challengeProgress.textContent = 'Step ' + ( challengeStep + 1 ) + ' of ' + challengePrompts.length + '. No score is stored.';
				}
			};

			const setPromptAnswered = ( prompt, isAnswered ) => {
				if ( ! prompt ) {
					return;
				}

				prompt.dataset.worldChallengeLocked = isAnswered ? 'true' : 'false';
				prompt.classList.toggle( 'is-answered', isAnswered );
				prompt.querySelectorAll( 'button[data-world-riddle-answer]' ).forEach( ( option ) => {
					option.disabled = isAnswered;
					if ( ! isAnswered ) {
						option.removeAttribute( 'aria-pressed' );
					}
				} );
			};

			const focusFirstAnswer = () => {
				const currentPrompt = challengePrompts[ challengeStep ];
				const firstAnswer = currentPrompt ? currentPrompt.querySelector( 'button[data-world-riddle-answer]:not([disabled])' ) : null;
				if ( firstAnswer && 'function' === typeof firstAnswer.focus ) {
					firstAnswer.focus( { preventScroll: true } );
				}
			};

			const resetChallenge = () => {
				challengeStep = 0;
				challengeCorrect = 0;
				challengeFinished = false;
				challengePrompts.forEach( ( prompt ) => setPromptAnswered( prompt, false ) );
				showChallengeStep( 0 );
				if ( challengeResult ) {
					challengeResult.textContent = 'Choose carefully. The challenge remembers only while this page is open.';
				}
				if ( challengeReward ) {
					challengeReward.hidden = true;
				}
				if ( challengeRetry ) {
					challengeRetry.hidden = true;
				}
			};

			const openChallenge = () => {
				if ( ! challenge ) {
					return;
				}

				setOpen( true );
				challenge.hidden = false;
				resetChallenge();
				if ( 'function' === typeof challenge.scrollIntoView ) {
					challenge.scrollIntoView( { block: 'nearest' } );
				}
				focusFirstAnswer();
			};

			const launch = ( action ) => {
				if ( ! readout ) {
					return;
				}

				if ( routeGoLink ) {
					routeGoLink.hidden = true;
					routeGoLink.removeAttribute( 'href' );
				}

				if ( ! window.fetch ) {
					readout.hidden = false;
					readout.textContent = 'Direct action links remain available; route tools need fetch support in this runtime.';
					return;
				}

				const endpoint = dock.dataset.actionLauncherEndpoint + '?action=' + encodeURIComponent( action || 'choose' );
				readout.hidden = false;
				readout.textContent = 'Opening ' + ( action || 'choose' ) + ' route…';
				fetch( endpoint, { credentials: 'same-origin' } )
					.then( ( response ) => {
						if ( ! response.ok ) {
							throw new Error( 'Action launcher unavailable' );
						}
						return response.json();
					} )
					.then( ( data ) => {
						const brief = data.route_brief || {};
						const summary = brief.summary || {};
						const stops = Array.isArray( brief.brief ) ? brief.brief.slice( 0, 3 ).map( ( stop ) => stop.slug ).filter( Boolean ) : [];
						const intent = data.selected ? data.selected.intent : '';
						readout.textContent = 'Route: ' + ( data.action || 'choose' ) + ( intent ? ' / ' + intent : '' ) + '. First stop: ' + ( summary.first_stop || stops[0] || 'available path' ) + ( stops.length ? '. Next: ' + stops.join( ' → ' ) : '' ) + '. Public route metadata only.';
						if ( routeGoLink && data.selected && data.selected.href ) {
							routeGoLink.href = data.selected.href;
							routeGoLink.textContent = data.selected.cta || 'Go now';
							routeGoLink.hidden = false;
						}
					} )
					.catch( () => {
						readout.textContent = 'The action dock is visible, but its public REST route could not be fetched in this runtime.';
						if ( routeGoLink ) {
							routeGoLink.hidden = true;
						}
					} );
			};

			const updateQuickTour = () => {
				if ( ! quickTourSteps.length || ! quickTourButton ) {
					return;
				}

				quickTourSteps.forEach( ( step, index ) => {
					step.classList.toggle( 'is-current', index === quickTourStep );
				} );
				quickTourButton.textContent = quickTourStep >= quickTourSteps.length ? 'Restart quick tour' : ( quickTourStep ? 'Next tour move' : 'Start quick tour' );
			};

			const advanceQuickTour = () => {
				if ( ! quickTourSteps.length || ! readout ) {
					return;
				}

				if ( quickTourStep >= quickTourSteps.length ) {
					quickTourStep = 0;
				}

				const step = quickTourSteps[ quickTourStep ];
				const action = step ? step.dataset.worldTourAction : 'choose';
				const label = step ? step.textContent.trim().replace( /\s+/g, ' ' ) : 'Choose a path';
				readout.hidden = false;
				readout.textContent = 'Quick tour move ' + ( quickTourStep + 1 ) + ' of ' + quickTourSteps.length + ': ' + label + ' No account, cookie, stored preference, score, or database write.';
				launch( action || 'choose' );
				quickTourStep += 1;

				if ( quickTourStep >= quickTourSteps.length ) {
					quickTourSteps.forEach( ( tourStep ) => tourStep.classList.remove( 'is-current' ) );
					if ( quickTourButton ) {
						quickTourButton.textContent = 'Restart quick tour';
					}
					return;
				}

				updateQuickTour();
			};

			if ( quickTourButton ) {
				quickTourButton.addEventListener( 'click', advanceQuickTour );
				updateQuickTour();
			}

			const openChallengeFromHash = () => {
				if ( '#world-action-launcher-challenge' === window.location.hash ) {
					openChallenge();
				}
			};

			const rollPath = () => {
				if ( ! routeButtons.length ) {
					return;
				}

				const selected = routeButtons[ Math.floor( Math.random() * routeButtons.length ) ];
				const action = selected ? selected.dataset.worldAction : 'choose';

				if ( rollButton ) {
					rollButton.textContent = 'Rolled: ' + ( selected ? selected.textContent.trim() : action );
				}

				launch( action );
			};

			if ( rollButton ) {
				rollButton.addEventListener( 'click', rollPath );
			}

			const drawMove = () => {
				if ( ! drawButton || ! Array.isArray( drawMoves ) || ! drawMoves.length || ! readout ) {
					return;
				}

				const selected = drawMoves[ Math.floor( Math.random() * drawMoves.length ) ] || {};
				const label = selected.label || 'Make one small move now.';
				const action = selected.action || '';
				readout.hidden = false;
				readout.textContent = 'Move: ' + label + ' No account, cookie, score, or database write.';
				drawButton.textContent = 'Drew a move';

				if ( 'play' === action ) {
					openChallenge();
					return;
				}

				if ( 'random' === action ) {
					rollPath();
					return;
				}

				if ( action ) {
					launch( action );
				}
			};

			if ( drawButton ) {
				drawButton.addEventListener( 'click', drawMove );
			}

			const surpriseMe = () => {
				setOpen( true );
				const options = [ 'roll', 'challenge' ];
				if ( drawButton && Array.isArray( drawMoves ) && drawMoves.length ) {
					options.push( 'draw' );
				}

				const selected = options[ Math.floor( Math.random() * options.length ) ] || 'roll';
				if ( surpriseButton ) {
					surpriseButton.textContent = 'Surprised';
				}

				if ( 'challenge' === selected ) {
					openChallenge();
					return;
				}

				if ( 'draw' === selected ) {
					drawMove();
					return;
				}

				rollPath();
			};

			const updateMissionProgress = () => {
				missionCards.forEach( ( card ) => {
					const isActive = !! activeMissionKey && card.dataset.worldMissionCard === activeMissionKey;
					card.classList.toggle( 'is-active', isActive );
					card.querySelectorAll( '[data-world-mission-step]' ).forEach( ( step, index ) => {
						const stepIndex = Number.parseInt( step.dataset.worldMissionStep || String( index ), 10 );
						step.classList.toggle( 'is-complete', isActive && stepIndex < activeMissionStep );
						step.classList.toggle( 'is-current', isActive && activeMissionStep < activeMission.length && stepIndex === activeMissionStep );
					} );
				} );
			};

			const updateMissionStatus = () => {
				if ( ! missionStatus || ! missionNextButton ) {
					return;
				}

				updateMissionProgress();

				if ( ! activeMission.length || activeMissionStep >= activeMission.length ) {
					missionStatus.hidden = activeMissionStep >= activeMission.length && activeMissionLabel ? false : true;
					missionStatus.textContent = activeMissionLabel ? 'Mission complete: ' + activeMissionLabel + '. Pick another mission, roll, or go now.' : 'No mission is active yet.';
					missionNextButton.hidden = true;
					return;
				}

				const nextLabel = getMissionMoveLabel( activeMissionStep );
				missionStatus.hidden = false;
				missionStatus.textContent = 'Mission active: ' + activeMissionLabel + '. Next: ' + nextLabel + ' (' + ( activeMissionStep + 1 ) + ' of ' + activeMission.length + '). Page-local state only.';
				missionNextButton.hidden = false;
				missionNextButton.textContent = 'Next: ' + nextLabel;
			};

			const getMissionMoveLabel = ( index ) => {
				return activeMissionLabels[ index ] || activeMission[ index ] || 'Next move';
			};

			const runMissionAction = ( action ) => {
				if ( ! action ) {
					return;
				}

				if ( 'play' === action ) {
					openChallenge();
					return;
				}

				launch( action );
			};

			const advanceMission = () => {
				if ( ! activeMission.length || activeMissionStep >= activeMission.length ) {
					updateMissionStatus();
					return;
				}

				const currentStep = activeMissionStep;
				const action = activeMission[ currentStep ];
				const label = getMissionMoveLabel( currentStep );
				activeMissionStep += 1;
				if ( readout ) {
					readout.hidden = false;
					readout.textContent = 'Mission move ' + activeMissionStep + ' of ' + activeMission.length + ': ' + label + '. No account, cookie, stored preference, score, or database write.';
				}
				runMissionAction( action );
				updateMissionStatus();
			};

			const startMission = ( button ) => {
				if ( ! button || ! readout ) {
					return;
				}

				let missionActions = [];
				let missionLabels = [];
				try {
					missionActions = JSON.parse( button.dataset.worldMissionActions || '[]' );
				} catch ( error ) {
					missionActions = [];
				}
				try {
					missionLabels = JSON.parse( button.dataset.worldMissionLabels || '[]' );
				} catch ( error ) {
					missionLabels = [];
				}

				activeMission = Array.isArray( missionActions ) ? missionActions.filter( Boolean ) : [];
				activeMissionLabels = Array.isArray( missionLabels ) ? missionLabels.map( ( label ) => String( label || '' ) ).filter( Boolean ) : [];
				activeMissionStep = 0;
				activeMissionLabel = ( button.textContent || 'Start mission' ).trim();
				activeMissionKey = button.dataset.worldMission || '';
				readout.hidden = false;
				readout.textContent = 'Mission: ' + activeMissionLabel + '. ' + ( activeMissionLabels.length ? activeMissionLabels.join( ' → ' ) : ( activeMission.length ? activeMission.join( ' → ' ) : 'Choose one visible move' ) ) + '. Use Next mission move to continue. No account, cookie, stored preference, score, or database write.';
				updateMissionStatus();

				if ( ! activeMission.length && routeGoLink && button.dataset.worldMissionHref ) {
					routeGoLink.href = button.dataset.worldMissionHref;
					routeGoLink.textContent = button.textContent || 'Go now';
					routeGoLink.hidden = false;
					return;
				}

				advanceMission();
			};

			missionButtons.forEach( ( button ) => {
				button.addEventListener( 'click', () => startMission( button ) );
			} );

			if ( startNowButton ) {
				startNowButton.addEventListener( 'click', () => {
					setOpen( true );
					if ( missionButtons.length ) {
						startMission( missionButtons[0] );
						return;
					}

					rollPath();
				} );
			}

			if ( missionNextButton ) {
				missionNextButton.addEventListener( 'click', advanceMission );
			}

			if ( surpriseButton ) {
				surpriseButton.addEventListener( 'click', surpriseMe );
			}

			openChallengeFromHash();
			window.addEventListener( 'hashchange', openChallengeFromHash );

			dock.addEventListener( 'click', ( event ) => {
				const challengeOpener = event.target.closest( 'button[data-world-open-challenge]' );
				if ( challengeOpener && challenge ) {
					openChallenge();
					return;
				}

				const retry = event.target.closest( 'button[data-world-challenge-retry]' );
				if ( retry && challenge ) {
					resetChallenge();
					return;
				}

				const answer = event.target.closest( 'button[data-world-riddle-answer]' );
				if ( answer && challengeResult && challengePrompts.length && ! challengeFinished ) {
					const prompt = answer.closest( '[data-world-challenge-prompt]' );
					if ( ! prompt || prompt.hidden || 'true' === prompt.dataset.worldChallengeLocked ) {
						return;
					}

					setPromptAnswered( prompt, true );
					answer.setAttribute( 'aria-pressed', 'true' );
					const isCorrect = !! prompt && prompt.dataset.worldChallengeCorrect === answer.dataset.worldRiddleAnswer;
					const success = prompt ? prompt.dataset.worldChallengeSuccess : '';
					const failure = prompt ? prompt.dataset.worldChallengeFailure : '';
					challengeCorrect += isCorrect ? 1 : 0;
					challengeResult.textContent = isCorrect ? ( success || 'Correct.' ) : ( failure || 'Not quite.' );

					window.setTimeout( () => {
						if ( challengeStep < challengePrompts.length - 1 ) {
							showChallengeStep( challengeStep + 1 );
							if ( challengeResult ) {
								challengeResult.textContent += ' Next choice is ready.';
							}
							focusFirstAnswer();
							return;
						}

						challengeFinished = true;
						const perfectRun = challengeCorrect === challengePrompts.length;
						if ( challengeProgress ) {
							challengeProgress.textContent = ( perfectRun ? ( challenge.dataset.worldChallengeComplete || 'Perfect run.' ) : ( challenge.dataset.worldChallengeIncomplete || 'Run complete.' ) ) + ' Correct choices on this page: ' + challengeCorrect + ' of ' + challengePrompts.length + '.';
						}
						if ( challengeReward ) {
							challengeReward.hidden = false;
						}
						if ( challengeRetry ) {
							challengeRetry.hidden = perfectRun;
						}
						const nextControl = challengeReward || ( perfectRun ? null : challengeRetry );
						if ( nextControl && 'function' === typeof nextControl.focus ) {
							nextControl.focus( { preventScroll: true } );
						}
					}, 420 );
					return;
				}

				const button = event.target.closest( 'button[data-world-action]' );
				if ( button ) {
					launch( button.dataset.worldAction );
				}
			} );
		}());
		</script>
	</aside>
	<?php
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
