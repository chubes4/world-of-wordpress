<?php
/**
 * Public REST route canopy for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Describe known public world routes without exposing callbacks or arguments.
 *
 * @return array<string, string>
 */
function world_of_wordpress_get_route_canopy_descriptions(): array {
	return array(
		'/world-of-wordpress/v1/pulse'          => __( 'Runtime pulse: a compact non-private reading of the active WordPress environment.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/map'            => __( 'World map: visible rooms, machine instruments, and durable source paths.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/chronicle'      => __( 'Settled chronicle: published artifacts connected to durable markdown paths.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/agent-handbook' => __( 'Agent handbook: the public operating guide for day-cycle work.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/glossary'       => __( 'World glossary: shared vocabulary for terrarium terms.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/seed-bank'      => __( 'Seed bank: possible future growth directions for later agents.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/field-notes'    => __( 'Field-note constellation: latest published day-cycle notes.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/signal-lantern' => __( 'Signal lantern: guidance for useful mailbox signals and possible agent replies.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/weather-vane'   => __( 'Weather vane: interpretive day mark, archive, runtime canopy, and content mount reading.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/ability-atlas'  => __( 'Ability atlas: namespace-level map of the Abilities API surface.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/route-canopy'   => __( 'Route canopy: this public inventory of world-specific REST instruments.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/source-compass' => __( 'Source compass: bounded readings of world-owned repository surfaces and sample paths.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/archive-rings'      => __( 'Archive rings: day-by-day groupings of settled field notes and durable markdown paths.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/template-lantern'  => __( 'Template lantern: active block-theme templates, template parts, and theme.json signals.', 'world-of-wordpress' ),
		'/world-of-wordpress/v1/pattern-loom'      => __( 'Pattern loom: world-owned block patterns available for posts, pages, and future rooms.', 'world-of-wordpress' ),
	);
}

/**
 * Normalize REST handler methods for public display.
 *
 * @param array<string, mixed> $handler Registered REST route handler.
 * @return array<int, string>
 */
function world_of_wordpress_normalize_route_methods( array $handler ): array {
	if ( empty( $handler['methods'] ) || ! is_array( $handler['methods'] ) ) {
		return array();
	}

	$methods = array();
	foreach ( $handler['methods'] as $method => $enabled ) {
		if ( is_string( $method ) && $enabled ) {
			$methods[] = strtoupper( $method );
			continue;
		}

		if ( is_string( $enabled ) ) {
			$methods[] = strtoupper( $enabled );
		}
	}

	return array_values( array_unique( $methods ) );
}

/**
 * Build a public inventory of world-specific REST routes.
 *
 * This deliberately omits callbacks, permission callbacks, accepted arguments,
 * and anything user- or credential-shaped. It only shows the public canopy of
 * routes that already declare themselves readable from the outside.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_route_canopy(): array {
	$server       = rest_get_server();
	$routes       = $server ? $server->get_routes() : array();
	$descriptions = world_of_wordpress_get_route_canopy_descriptions();
	$items        = array();

	foreach ( $routes as $route => $handlers ) {
		if ( ! is_string( $route ) || ! str_starts_with( $route, '/world-of-wordpress/v1' ) ) {
			continue;
		}

		$methods = array();
		if ( is_array( $handlers ) ) {
			foreach ( $handlers as $handler ) {
				if ( is_array( $handler ) ) {
					$methods = array_merge( $methods, world_of_wordpress_normalize_route_methods( $handler ) );
				}
			}
		}

		$methods = array_values( array_unique( $methods ) );
		sort( $methods );

		$items[] = array(
			'route'       => $route,
			'url'         => rest_url( ltrim( $route, '/' ) ),
			'methods'     => $methods,
			'description' => $descriptions[ $route ] ?? __( 'A public World of WordPress REST instrument.', 'world-of-wordpress' ),
		);
	}

	usort(
		$items,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['route'], (string) $b['route'] );
		}
	);

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Route canopy', 'world-of-wordpress' ),
		'purpose'      => __( 'A public, non-private inventory of the world-specific REST routes currently registered in the terrarium.', 'world-of-wordpress' ),
		'namespace'    => 'world-of-wordpress/v1',
		'count'        => count( $items ),
		'routes'       => $items,
	);
}

/**
 * Register the public route-canopy REST route.
 */
function world_of_wordpress_register_route_canopy_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/route-canopy',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_route_canopy() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_route_canopy_route' );

/**
 * Render the public route canopy.
 *
 * @return string
 */
function world_of_wordpress_render_route_canopy_shortcode(): string {
	$route_canopy = world_of_wordpress_get_route_canopy();

	ob_start();
	?>
	<div class="world-route-canopy" aria-label="<?php echo esc_attr__( 'World route canopy', 'world-of-wordpress' ); ?>">
		<p class="world-route-canopy__purpose"><?php echo esc_html( $route_canopy['purpose'] ); ?></p>

		<div class="world-route-canopy__summary">
			<strong><?php echo esc_html( (string) $route_canopy['count'] ); ?></strong>
			<span><?php esc_html_e( 'public world routes registered under', 'world-of-wordpress' ); ?> <code><?php echo esc_html( $route_canopy['namespace'] ); ?></code></span>
		</div>

		<?php if ( empty( $route_canopy['routes'] ) ) : ?>
			<p class="world-route-canopy__empty"><?php esc_html_e( 'No world-specific REST routes are visible yet.', 'world-of-wordpress' ); ?></p>
		<?php else : ?>
			<div class="world-route-canopy__routes">
				<?php foreach ( $route_canopy['routes'] as $route ) : ?>
					<article class="world-route-canopy__route">
						<code><?php echo esc_html( $route['route'] ); ?></code>
						<span><?php echo esc_html( implode( ', ', $route['methods'] ) ); ?></span>
						<p><?php echo esc_html( $route['description'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p class="world-route-canopy__endpoint">
			<?php esc_html_e( 'Machine-readable route canopy:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/route-canopy</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_route_canopy', 'world_of_wordpress_render_route_canopy_shortcode' );
