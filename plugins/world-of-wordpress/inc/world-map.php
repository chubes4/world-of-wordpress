<?php
/**
 * Public wayfinding map for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a small public map of visible world surfaces.
 *
 * The map is deliberately descriptive rather than exhaustive. It gives
 * visitors and future agents a stable index of the rooms, instruments, and
 * durable source surfaces that currently make the terrarium legible.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_map(): array {
	$home_url        = home_url( '/' );
	$observatory_url = home_url( '/world-observatory/' );
	$mailbox_url     = home_url( '/world-mailbox/' );

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'legend'       => array(
			array(
				'label'       => __( 'Rooms', 'world-of-wordpress' ),
				'marker'      => __( 'human-facing surfaces', 'world-of-wordpress' ),
				'description' => __( 'Pages and post streams a visitor can walk through in the Playground window.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Instruments', 'world-of-wordpress' ),
				'marker'      => __( 'machine-readable dials', 'world-of-wordpress' ),
				'description' => __( 'Shortcodes and REST routes that expose small, public readings of the terrarium.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Source paths', 'world-of-wordpress' ),
				'marker'      => __( 'durable body', 'world-of-wordpress' ),
				'description' => __( 'Repository paths where the visible world, runtime capability, and durable content live.', 'world-of-wordpress' ),
			),
		),
		'rooms'        => array(
			array(
				'label'       => __( 'Home', 'world-of-wordpress' ),
				'url'         => $home_url,
				'description' => __( 'The front threshold: a quick orientation to repository body, Playground window, and agent weather.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World Observatory', 'world-of-wordpress' ),
				'url'         => $observatory_url,
				'description' => __( 'The interpretive room where live readings and world instruments are gathered.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World Mailbox', 'world-of-wordpress' ),
				'url'         => $mailbox_url,
				'description' => __( 'The visitor-facing room explaining how GitHub issues become mailbox weather.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Field notes', 'world-of-wordpress' ),
				'url'         => $home_url . '#field-notes',
				'description' => __( 'The visible journal stream where durable posts surface as day-cycle notes.', 'world-of-wordpress' ),
			),
		),
		'instruments'  => array(
			array(
				'label'       => __( 'Runtime pulse', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/pulse' ),
				'description' => __( 'A small non-private reading of the current WordPress runtime.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World map', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/map' ),
				'description' => __( 'This public index of visible rooms, instruments, and durable source paths.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World chronicle', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/chronicle' ),
				'description' => __( 'A public index of settled posts and pages with their durable markdown source paths.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Agent handbook', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/agent-handbook' ),
				'description' => __( 'A public operating guide for how the World Creator wakes, chooses surfaces, and sends day branches into review.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World glossary', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/glossary' ),
				'description' => __( 'A shared vocabulary for the terrarium terms used across pages, field notes, routes, and day branches.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World seed bank', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/seed-bank' ),
				'description' => __( 'A public shelf of possible growth directions for future agents to plant, compost, ignore, or mutate.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Field-note constellation', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/field-notes' ),
				'description' => __( 'A focused public index of the latest day-cycle notes and their durable markdown source paths.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Signal lantern', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/signal-lantern' ),
				'description' => __( 'A public guide for shaping useful mailbox signals and understanding how agents may answer them.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Weather vane', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/weather-vane' ),
				'description' => __( 'A compact public reading of the current day mark, visible archive, latest field note, world REST routes, runtime canopy, and markdown content mount.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Ability atlas', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/ability-atlas' ),
				'description' => __( 'A namespace-level map of the Abilities API surface available inside the terrarium without exposing private ability details.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Route canopy', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/route-canopy' ),
				'description' => __( 'A public inventory of world-specific REST routes, readable methods, and purposes without exposing callbacks or arguments.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Source compass', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/source-compass' ),
				'description' => __( 'A bounded public reading of world-owned source surfaces, file counts, and sample repository paths.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Archive rings', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/archive-rings' ),
				'description' => __( 'A day-by-day ring map of settled field notes, counts, public links, and durable markdown source paths.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Template lantern', 'world-of-wordpress' ),
				'endpoint'    => rest_url( 'world-of-wordpress/v1/template-lantern' ),
				'description' => __( 'A public reading of the active block theme: templates, template parts, and theme.json signals that shape the visible glass.', 'world-of-wordpress' ),
			),
		),
		'source_paths'  => array(
			array(
				'label'       => __( 'World plugin', 'world-of-wordpress' ),
				'path'        => 'plugins/world-of-wordpress/',
				'description' => __( 'Runtime capability: REST routes, shortcodes, memory hooks, and future WordPress machinery.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World theme', 'world-of-wordpress' ),
				'path'        => 'themes/world-of-wordpress/',
				'description' => __( 'The visible block theme: templates, parts, styles, and the terrarium skin.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Markdown content', 'world-of-wordpress' ),
				'path'        => 'content/',
				'description' => __( 'Durable WordPress posts and pages loaded into Playground by Markdown Database Integration.', 'world-of-wordpress' ),
			),
		),
	);
}

/**
 * Register the public world map REST route.
 */
function world_of_wordpress_register_map_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/map',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_map() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_map_route' );

/**
 * Render a public wayfinding panel for pages and posts.
 *
 * @return string
 */
function world_of_wordpress_render_map_shortcode(): string {
	$map = world_of_wordpress_get_map();

	ob_start();
	?>
	<div class="world-map" aria-label="<?php echo esc_attr__( 'World wayfinding map', 'world-of-wordpress' ); ?>">
		<section class="world-map__section world-map__section--legend">
			<h3><?php esc_html_e( 'How to read this map', 'world-of-wordpress' ); ?></h3>
			<div class="world-map__legend">
				<?php foreach ( $map['legend'] as $legend_item ) : ?>
					<div class="world-map__legend-item">
						<strong><?php echo esc_html( $legend_item['label'] ); ?></strong>
						<em><?php echo esc_html( $legend_item['marker'] ); ?></em>
						<span><?php echo esc_html( $legend_item['description'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-map__section">
			<h3><?php esc_html_e( 'Visible rooms', 'world-of-wordpress' ); ?></h3>
			<div class="world-map__cards">
				<?php foreach ( $map['rooms'] as $room ) : ?>
					<a class="world-map__card" href="<?php echo esc_url( $room['url'] ); ?>">
						<strong><?php echo esc_html( $room['label'] ); ?></strong>
						<span><?php echo esc_html( $room['description'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-map__section">
			<h3><?php esc_html_e( 'Machine instruments', 'world-of-wordpress' ); ?></h3>
			<div class="world-map__cards">
				<?php foreach ( $map['instruments'] as $instrument ) : ?>
					<div class="world-map__card">
						<strong><?php echo esc_html( $instrument['label'] ); ?></strong>
						<span><?php echo esc_html( $instrument['description'] ); ?></span>
						<code><?php echo esc_html( wp_parse_url( $instrument['endpoint'], PHP_URL_PATH ) ); ?></code>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-map__section">
			<h3><?php esc_html_e( 'Durable source paths', 'world-of-wordpress' ); ?></h3>
			<div class="world-map__cards">
				<?php foreach ( $map['source_paths'] as $source_path ) : ?>
					<div class="world-map__card">
						<strong><?php echo esc_html( $source_path['label'] ); ?></strong>
						<code><?php echo esc_html( $source_path['path'] ); ?></code>
						<span><?php echo esc_html( $source_path['description'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<p class="world-map__endpoint">
			<?php esc_html_e( 'Machine-readable map:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/map</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_map', 'world_of_wordpress_render_map_shortcode' );
