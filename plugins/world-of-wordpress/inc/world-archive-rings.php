<?php
/**
 * Public archive rings for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather published field notes into day rings.
 *
 * Archive rings make the growing journal stream easier to scan as the world
 * accumulates many small day-cycle traces. The instrument only exposes public
 * post metadata, public permalinks, and durable markdown source paths.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_archive_rings(): array {
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'posts_per_page'   => 120,
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => false,
		)
	);

	$rings = array();

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$published = get_post_time( DATE_ATOM, true, $post );
		$day       = gmdate( 'Y-m-d', strtotime( $published ) );

		if ( ! isset( $rings[ $day ] ) ) {
			$rings[ $day ] = array(
				'day'        => $day,
				'count'      => 0,
				'first_seen' => $published,
				'last_seen'  => $published,
				'notes'      => array(),
			);
		}

		$rings[ $day ]['count']++;
		$rings[ $day ]['last_seen'] = max( $rings[ $day ]['last_seen'], $published );
		$rings[ $day ]['first_seen'] = min( $rings[ $day ]['first_seen'], $published );

		if ( count( $rings[ $day ]['notes'] ) < 5 ) {
			$rings[ $day ]['notes'][] = array(
				'title'       => get_the_title( $post ),
				'slug'        => $post->post_name,
				'url'         => get_permalink( $post ),
				'published'   => $published,
				'source_path' => sprintf( 'content/post/%s.md', $post->post_name ),
			);
		}
	}

	$archive_rings = array_values( $rings );
	usort(
		$archive_rings,
		static function ( array $left, array $right ): int {
			return strcmp( $right['day'], $left['day'] );
		}
	);

	$total_notes = array_sum( array_map( 'intval', wp_list_pluck( $archive_rings, 'count' ) ) );

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Archive rings', 'world-of-wordpress' ),
		'purpose'      => __( 'A public day-by-day ring map of the field notes that have settled into the terrarium.', 'world-of-wordpress' ),
		'total_notes'  => (int) $total_notes,
		'day_count'    => count( $archive_rings ),
		'latest_day'   => $archive_rings[0]['day'] ?? null,
		'rings'        => $archive_rings,
	);
}

/**
 * Register the public archive rings REST route.
 */
function world_of_wordpress_register_archive_rings_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/archive-rings',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_archive_rings() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_archive_rings_route' );

/**
 * Render the public archive rings.
 *
 * @return string
 */
function world_of_wordpress_render_archive_rings_shortcode(): string {
	$archive = world_of_wordpress_get_archive_rings();

	ob_start();
	?>
	<div class="world-archive-rings" aria-label="<?php echo esc_attr__( 'Archive rings', 'world-of-wordpress' ); ?>">
		<p class="world-archive-rings__purpose"><?php echo esc_html( $archive['purpose'] ); ?></p>

		<div class="world-archive-rings__summary">
			<strong><?php echo esc_html( (string) $archive['total_notes'] ); ?></strong>
			<span><?php echo esc_html( sprintf( _n( 'field note across %d day ring', 'field notes across %d day rings', (int) $archive['day_count'], 'world-of-wordpress' ), (int) $archive['day_count'] ) ); ?></span>
		</div>

		<?php if ( empty( $archive['rings'] ) ) : ?>
			<p class="world-archive-rings__empty"><?php esc_html_e( 'No archive rings have formed yet.', 'world-of-wordpress' ); ?></p>
		<?php else : ?>
			<ol class="world-archive-rings__list">
				<?php foreach ( $archive['rings'] as $ring ) : ?>
					<li class="world-archive-rings__ring">
						<div class="world-archive-rings__ring-head">
							<strong><?php echo esc_html( $ring['day'] ); ?></strong>
							<span><?php echo esc_html( sprintf( _n( '%d note', '%d notes', (int) $ring['count'], 'world-of-wordpress' ), (int) $ring['count'] ) ); ?></span>
						</div>
						<?php if ( ! empty( $ring['notes'] ) ) : ?>
							<ul>
								<?php foreach ( $ring['notes'] as $note ) : ?>
									<li>
										<a href="<?php echo esc_url( $note['url'] ); ?>"><?php echo esc_html( $note['title'] ); ?></a>
										<code><?php echo esc_html( $note['source_path'] ); ?></code>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<p class="world-archive-rings__endpoint">
			<?php esc_html_e( 'Machine-readable archive rings:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/archive-rings</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_archive_rings', 'world_of_wordpress_render_archive_rings_shortcode' );
