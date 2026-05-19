<?php
/**
 * Public block-pattern loom for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Infer a durable source path for a world-owned pattern slug.
 *
 * @param string $slug Registered block pattern slug.
 * @return string|null
 */
function world_of_wordpress_get_pattern_source_path( string $slug ): ?string {
	if ( ! str_starts_with( $slug, 'world-of-wordpress/' ) ) {
		return null;
	}

	$pattern_file = 'world-' . sanitize_file_name( substr( $slug, strlen( 'world-of-wordpress/' ) ) ) . '.php';

	return 'themes/world-of-wordpress/patterns/' . $pattern_file;
}

/**
 * Build a public inventory of world-owned block patterns.
 *
 * This reads the registered pattern metadata only. Pattern content is
 * deliberately omitted so the loom describes reusable shapes without echoing
 * every block byte into REST responses.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_pattern_loom(): array {
	$patterns = array();

	if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
		$registered = WP_Block_Patterns_Registry::get_instance()->get_all_registered();

		foreach ( $registered as $fallback_slug => $pattern ) {
			if ( ! is_array( $pattern ) ) {
				continue;
			}

			$slug       = isset( $pattern['name'] ) ? (string) $pattern['name'] : (string) $fallback_slug;
			$categories = isset( $pattern['categories'] ) && is_array( $pattern['categories'] ) ? array_values( $pattern['categories'] ) : array();
			$is_world   = str_starts_with( $slug, 'world-of-wordpress/' ) || in_array( 'world', $categories, true );

			if ( ! $is_world ) {
				continue;
			}

			$patterns[] = array(
				'slug'           => $slug,
				'title'          => isset( $pattern['title'] ) ? wp_strip_all_tags( (string) $pattern['title'] ) : $slug,
				'description'    => isset( $pattern['description'] ) ? wp_strip_all_tags( (string) $pattern['description'] ) : '',
				'categories'     => $categories,
				'block_types'    => isset( $pattern['blockTypes'] ) && is_array( $pattern['blockTypes'] ) ? array_values( $pattern['blockTypes'] ) : array(),
				'viewport_width' => isset( $pattern['viewportWidth'] ) ? (int) $pattern['viewportWidth'] : null,
				'source_path'    => world_of_wordpress_get_pattern_source_path( $slug ),
			);
		}
	}

	usort(
		$patterns,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['slug'], (string) $b['slug'] );
		}
	);

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Pattern loom', 'world-of-wordpress' ),
		'purpose'      => __( 'A public inventory of the world-owned block patterns that can be woven into posts, pages, and future rooms.', 'world-of-wordpress' ),
		'category'     => 'world',
		'count'        => count( $patterns ),
		'patterns'     => $patterns,
	);
}

/**
 * Register the public pattern-loom REST route.
 */
function world_of_wordpress_register_pattern_loom_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/pattern-loom',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_pattern_loom() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_pattern_loom_route' );

/**
 * Render the public pattern loom.
 *
 * @return string
 */
function world_of_wordpress_render_pattern_loom_shortcode(): string {
	$loom = world_of_wordpress_get_pattern_loom();

	ob_start();
	?>
	<div class="world-pattern-loom" aria-label="<?php echo esc_attr__( 'World pattern loom', 'world-of-wordpress' ); ?>">
		<p class="world-pattern-loom__purpose"><?php echo esc_html( $loom['purpose'] ); ?></p>

		<div class="world-pattern-loom__summary">
			<strong><?php echo esc_html( (string) $loom['count'] ); ?></strong>
			<span><?php esc_html_e( 'world-owned block patterns registered in category', 'world-of-wordpress' ); ?> <code><?php echo esc_html( $loom['category'] ); ?></code></span>
		</div>

		<?php if ( empty( $loom['patterns'] ) ) : ?>
			<p class="world-pattern-loom__empty"><?php esc_html_e( 'No world-owned block patterns are visible yet.', 'world-of-wordpress' ); ?></p>
		<?php else : ?>
			<div class="world-pattern-loom__patterns">
				<?php foreach ( $loom['patterns'] as $pattern ) : ?>
					<article class="world-pattern-loom__pattern">
						<strong><?php echo esc_html( $pattern['title'] ); ?></strong>
						<code><?php echo esc_html( $pattern['slug'] ); ?></code>
						<?php if ( ! empty( $pattern['description'] ) ) : ?>
							<p><?php echo esc_html( $pattern['description'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $pattern['source_path'] ) ) : ?>
							<span><?php echo esc_html( $pattern['source_path'] ); ?></span>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p class="world-pattern-loom__endpoint">
			<?php esc_html_e( 'Machine-readable pattern loom:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/pattern-loom</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_pattern_loom', 'world_of_wordpress_render_pattern_loom_shortcode' );
