<?php
/**
 * Public chronicle surface for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the likely durable markdown source path for a public post object.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function world_of_wordpress_get_chronicle_source_path( WP_Post $post ): string {
	$directory = 'post';

	if ( 'page' === $post->post_type ) {
		$directory = 'page';
	}

	return sprintf( 'content/%1$s/%2$s.md', $directory, $post->post_name );
}

/**
 * Gather a small public chronicle of settled WordPress artifacts.
 *
 * This exposes only already-public published posts and pages, plus the durable
 * markdown path where this world convention stores their source.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_chronicle(): array {
	$posts = get_posts(
		array(
			'post_type'        => array( 'post', 'page' ),
			'post_status'      => 'publish',
			'posts_per_page'   => 12,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	$entries = array();

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		$type_label       = $post_type_object && isset( $post_type_object->labels->singular_name ) ? $post_type_object->labels->singular_name : $post->post_type;

		$entries[] = array(
			'id'          => (int) $post->ID,
			'title'       => get_the_title( $post ),
			'type'        => $post->post_type,
			'type_label'  => $type_label,
			'slug'        => $post->post_name,
			'url'         => get_permalink( $post ),
			'published'   => get_post_time( DATE_ATOM, true, $post ),
			'source_path' => world_of_wordpress_get_chronicle_source_path( $post ),
		);
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'count'        => count( $entries ),
		'entries'      => $entries,
	);
}

/**
 * Register the public world chronicle REST route.
 */
function world_of_wordpress_register_chronicle_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/chronicle',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_chronicle() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_chronicle_route' );

/**
 * Render a compact public chronicle panel for pages and posts.
 *
 * @return string
 */
function world_of_wordpress_render_chronicle_shortcode(): string {
	$chronicle = world_of_wordpress_get_chronicle();

	ob_start();
	?>
	<div class="world-chronicle" aria-label="<?php echo esc_attr__( 'World chronicle', 'world-of-wordpress' ); ?>">
		<?php if ( empty( $chronicle['entries'] ) ) : ?>
			<p><?php esc_html_e( 'No settled public artifacts have appeared yet.', 'world-of-wordpress' ); ?></p>
		<?php else : ?>
			<ol class="world-chronicle__list">
				<?php foreach ( $chronicle['entries'] as $entry ) : ?>
					<li class="world-chronicle__entry">
						<a href="<?php echo esc_url( $entry['url'] ); ?>"><?php echo esc_html( $entry['title'] ); ?></a>
						<span><?php echo esc_html( $entry['type_label'] ); ?> · <?php echo esc_html( gmdate( 'Y-m-d H:i', strtotime( $entry['published'] ) ) ); ?> UTC</span>
						<code><?php echo esc_html( $entry['source_path'] ); ?></code>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
		<p class="world-chronicle__endpoint">
			<?php esc_html_e( 'Machine-readable chronicle:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/chronicle</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_chronicle', 'world_of_wordpress_render_chronicle_shortcode' );
