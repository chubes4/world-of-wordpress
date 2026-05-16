<?php
/**
 * Public field-note constellation for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather the latest published field notes as a public constellation.
 *
 * Field notes are ordinary WordPress posts whose durable markdown files live in
 * content/post/. This instrument gives the visible journal stream a focused
 * machine-readable surface instead of leaving it only inside the homepage query
 * loop or the broader chronicle.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_field_notes(): array {
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => 10,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	$notes = array();

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$notes[] = array(
			'id'          => (int) $post->ID,
			'title'       => get_the_title( $post ),
			'slug'        => $post->post_name,
			'url'         => get_permalink( $post ),
			'published'   => get_post_time( DATE_ATOM, true, $post ),
			'excerpt'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'source_path' => sprintf( 'content/post/%s.md', $post->post_name ),
		);
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Field-note constellation', 'world-of-wordpress' ),
		'purpose'      => __( 'A focused public index of the latest day-cycle notes that have settled into the terrarium.', 'world-of-wordpress' ),
		'count'        => count( $notes ),
		'notes'        => $notes,
	);
}

/**
 * Register the public field-notes REST route.
 */
function world_of_wordpress_register_field_notes_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/field-notes',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_field_notes() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_field_notes_route' );

/**
 * Render the public field-note constellation.
 *
 * @return string
 */
function world_of_wordpress_render_field_notes_shortcode(): string {
	$field_notes = world_of_wordpress_get_field_notes();

	ob_start();
	?>
	<div class="world-field-notes" aria-label="<?php echo esc_attr__( 'Field-note constellation', 'world-of-wordpress' ); ?>">
		<p class="world-field-notes__purpose"><?php echo esc_html( $field_notes['purpose'] ); ?></p>

		<?php if ( empty( $field_notes['notes'] ) ) : ?>
			<p><?php esc_html_e( 'No field notes have settled yet.', 'world-of-wordpress' ); ?></p>
		<?php else : ?>
			<ol class="world-field-notes__list">
				<?php foreach ( $field_notes['notes'] as $note ) : ?>
					<li class="world-field-notes__note">
						<a href="<?php echo esc_url( $note['url'] ); ?>"><?php echo esc_html( $note['title'] ); ?></a>
						<span><?php echo esc_html( gmdate( 'Y-m-d H:i', strtotime( $note['published'] ) ) ); ?> UTC</span>
						<?php if ( ! empty( $note['excerpt'] ) ) : ?>
							<p><?php echo esc_html( $note['excerpt'] ); ?></p>
						<?php endif; ?>
						<code><?php echo esc_html( $note['source_path'] ); ?></code>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<p class="world-field-notes__endpoint">
			<?php esc_html_e( 'Machine-readable field notes:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/field-notes</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_field_notes', 'world_of_wordpress_render_field_notes_shortcode' );
