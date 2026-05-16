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

	return array(
		'generated_at' => current_time( 'mysql', true ),
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
