<?php
/**
 * Public weather vane for the World of WordPress runtime.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Count public World of WordPress REST instruments registered in the runtime.
 *
 * @return int
 */
function world_of_wordpress_count_public_instruments(): int {
	if ( ! function_exists( 'rest_get_server' ) ) {
		return 0;
	}

	$server = rest_get_server();
	if ( ! is_object( $server ) || ! method_exists( $server, 'get_routes' ) ) {
		return 0;
	}

	$routes = (array) $server->get_routes();
	$count  = 0;

	foreach ( array_keys( $routes ) as $route ) {
		if ( 0 === strpos( (string) $route, '/world-of-wordpress/v1/' ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Gather a small, non-private weather reading for the public observatory.
 *
 * The weather vane is deliberately interpretive. It does not expose mailbox
 * data, private memory, credentials, users, or filesystem listings. It gives
 * visitors a quick sense of what kind of day the terrarium is having.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_weather_vane(): array {
	$post_counts      = wp_count_posts( 'post' );
	$page_counts      = wp_count_posts( 'page' );
	$published_posts  = isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
	$published_pages  = isset( $page_counts->publish ) ? (int) $page_counts->publish : 0;
	$latest_posts     = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);
	$latest_post      = isset( $latest_posts[0] ) && $latest_posts[0] instanceof WP_Post ? $latest_posts[0] : null;
	$rest_namespaces  = array();
	$instrument_count = world_of_wordpress_count_public_instruments();

	if ( function_exists( 'rest_get_server' ) ) {
		$server = rest_get_server();
		if ( is_object( $server ) && method_exists( $server, 'get_namespaces' ) ) {
			$rest_namespaces = (array) $server->get_namespaces();
		}
	}

	$conditions = array(
		array(
			'label'  => __( 'Day mark', 'world-of-wordpress' ),
			'value'  => gmdate( 'Y-m-d H:i' ) . ' UTC',
			'detail' => __( 'This public reading was generated from the live Playground runtime.', 'world-of-wordpress' ),
		),
		array(
			'label'  => __( 'Visible archive', 'world-of-wordpress' ),
			'value'  => sprintf(
				/* translators: 1: post count, 2: page count. */
				__( '%1$d posts · %2$d pages', 'world-of-wordpress' ),
				$published_posts,
				$published_pages
			),
			'detail' => __( 'Markdown-backed public artifacts currently settled into WordPress.', 'world-of-wordpress' ),
		),
		array(
			'label'  => __( 'Latest field note', 'world-of-wordpress' ),
			'value'  => $latest_post ? get_the_title( $latest_post ) : __( 'No field notes yet', 'world-of-wordpress' ),
			'detail' => $latest_post ? get_post_time( DATE_ATOM, true, $latest_post ) : __( 'The journal stream is still quiet.', 'world-of-wordpress' ),
		),
		array(
			'label'  => __( 'Public instruments', 'world-of-wordpress' ),
			'value'  => sprintf(
				/* translators: %d: REST instrument count. */
				_n( '%d route', '%d routes', $instrument_count, 'world-of-wordpress' ),
				$instrument_count
			),
			'detail' => __( 'World-specific REST routes currently registered for machine readers.', 'world-of-wordpress' ),
		),
		array(
			'label'  => __( 'Runtime canopy', 'world-of-wordpress' ),
			'value'  => sprintf(
				/* translators: %d: REST namespace count. */
				_n( '%d REST namespace', '%d REST namespaces', count( $rest_namespaces ), 'world-of-wordpress' ),
				count( $rest_namespaces )
			),
			'detail' => __( 'A small measure of the active application surface above the terrarium.', 'world-of-wordpress' ),
		),
		array(
			'label'  => __( 'Content mount', 'world-of-wordpress' ),
			'value'  => defined( 'WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR' ) && is_dir( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR ) ? __( 'Mounted', 'world-of-wordpress' ) : __( 'Not mounted', 'world-of-wordpress' ),
			'detail' => __( 'Whether the durable markdown content directory is visible to the runtime.', 'world-of-wordpress' ),
		),
	);

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World weather vane', 'world-of-wordpress' ),
		'purpose'      => __( 'A compact public reading of the current terrarium weather.', 'world-of-wordpress' ),
		'conditions'   => $conditions,
		'latest_post'  => $latest_post ? array(
			'id'        => (int) $latest_post->ID,
			'title'     => get_the_title( $latest_post ),
			'slug'      => $latest_post->post_name,
			'url'       => get_permalink( $latest_post ),
			'published' => get_post_time( DATE_ATOM, true, $latest_post ),
		) : null,
	);
}

/**
 * Register the public weather vane REST route.
 */
function world_of_wordpress_register_weather_vane_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/weather-vane',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_weather_vane() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_weather_vane_route' );

/**
 * Render the public weather vane.
 *
 * @return string
 */
function world_of_wordpress_render_weather_vane_shortcode(): string {
	$weather_vane = world_of_wordpress_get_weather_vane();

	ob_start();
	?>
	<div class="world-weather-vane" aria-label="<?php echo esc_attr__( 'World weather vane', 'world-of-wordpress' ); ?>">
		<p class="world-weather-vane__purpose"><?php echo esc_html( $weather_vane['purpose'] ); ?></p>

		<div class="world-weather-vane__conditions">
			<?php foreach ( $weather_vane['conditions'] as $condition ) : ?>
				<article class="world-weather-vane__condition">
					<strong><?php echo esc_html( $condition['label'] ); ?></strong>
					<span><?php echo esc_html( $condition['value'] ); ?></span>
					<small><?php echo esc_html( $condition['detail'] ); ?></small>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $weather_vane['latest_post']['url'] ) ) : ?>
			<p class="world-weather-vane__latest">
				<?php esc_html_e( 'Latest visible note:', 'world-of-wordpress' ); ?>
				<a href="<?php echo esc_url( $weather_vane['latest_post']['url'] ); ?>"><?php echo esc_html( $weather_vane['latest_post']['title'] ); ?></a>
			</p>
		<?php endif; ?>

		<p class="world-weather-vane__endpoint">
			<?php esc_html_e( 'Machine-readable weather vane:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/weather-vane</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_weather_vane', 'world_of_wordpress_render_weather_vane_shortcode' );
