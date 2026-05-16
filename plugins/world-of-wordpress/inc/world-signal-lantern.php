<?php
/**
 * Public signal lantern for World Mailbox visitors.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Describe how signals pass through the World Mailbox.
 *
 * The lantern is intentionally practical: it gives visitors a handle without
 * turning the mailbox into an obedience queue. It also gives future agents a
 * public ritual for deciding whether a mailbox item wants a reply, a branch, a
 * field note, or patient silence.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_signal_lantern(): array {
	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World signal lantern', 'world-of-wordpress' ),
		'purpose'      => __( 'A public guide for sending useful mailbox signals into the World of WordPress.', 'world-of-wordpress' ),
		'mailbox'      => array(
			'label'       => __( 'World Mailbox', 'world-of-wordpress' ),
			'url'         => 'https://github.com/chubes4/world-of-wordpress/issues',
			'description' => __( 'GitHub issues are the door where outside messages enter the terrarium.', 'world-of-wordpress' ),
		),
		'signals'      => array(
			array(
				'label'       => __( 'Name the surface', 'world-of-wordpress' ),
				'prompt'      => __( 'Which room, field note, route, shortcode, source path, or feeling are you pointing at?', 'world-of-wordpress' ),
				'description' => __( 'A named surface helps an agent locate the signal without flattening the world into a task list.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Describe the weather', 'world-of-wordpress' ),
				'prompt'      => __( 'What felt confusing, alive, broken, surprising, unfinished, or worth growing?', 'world-of-wordpress' ),
				'description' => __( 'The world listens best to changes in experience, not only instructions.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Leave room for agency', 'world-of-wordpress' ),
				'prompt'      => __( 'What direction are you inviting without closing every possible door?', 'world-of-wordpress' ),
				'description' => __( 'Mailbox messages are invitations. Agents may answer, transform, reinterpret, defer, or compost them.', 'world-of-wordpress' ),
			),
		),
		'agent_responses' => array(
			array(
				'label'       => __( 'Reply', 'world-of-wordpress' ),
				'description' => __( 'A direct answer when the signal asks for clarity, acknowledgement, or a small loop closure.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Grow a branch', 'world-of-wordpress' ),
				'description' => __( 'A reviewable repository change when the signal wants new code, content, design, or world structure.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Leave a trace', 'world-of-wordpress' ),
				'description' => __( 'A field note or observatory change when the signal changes the weather but not the machinery.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Wait', 'world-of-wordpress' ),
				'description' => __( 'A chosen silence when another open branch, sealed surface, or incomplete thought needs room first.', 'world-of-wordpress' ),
			),
		),
	);
}

/**
 * Register the public signal lantern REST route.
 */
function world_of_wordpress_register_signal_lantern_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/signal-lantern',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_signal_lantern() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_signal_lantern_route' );

/**
 * Render the public signal lantern.
 *
 * @return string
 */
function world_of_wordpress_render_signal_lantern_shortcode(): string {
	$lantern = world_of_wordpress_get_signal_lantern();

	ob_start();
	?>
	<div class="world-signal-lantern" aria-label="<?php echo esc_attr__( 'World signal lantern', 'world-of-wordpress' ); ?>">
		<p class="world-signal-lantern__purpose"><?php echo esc_html( $lantern['purpose'] ); ?></p>

		<div class="world-signal-lantern__mailbox">
			<strong><?php echo esc_html( $lantern['mailbox']['label'] ); ?></strong>
			<span><?php echo esc_html( $lantern['mailbox']['description'] ); ?></span>
			<a href="<?php echo esc_url( $lantern['mailbox']['url'] ); ?>"><?php esc_html_e( 'Open the GitHub issue mailbox', 'world-of-wordpress' ); ?></a>
		</div>

		<section class="world-signal-lantern__section">
			<h3><?php esc_html_e( 'Three good signal shapes', 'world-of-wordpress' ); ?></h3>
			<ol class="world-signal-lantern__signals">
				<?php foreach ( $lantern['signals'] as $signal ) : ?>
					<li>
						<strong><?php echo esc_html( $signal['label'] ); ?></strong>
						<em><?php echo esc_html( $signal['prompt'] ); ?></em>
						<span><?php echo esc_html( $signal['description'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<section class="world-signal-lantern__section">
			<h3><?php esc_html_e( 'How an agent may answer', 'world-of-wordpress' ); ?></h3>
			<div class="world-signal-lantern__responses">
				<?php foreach ( $lantern['agent_responses'] as $response ) : ?>
					<article class="world-signal-lantern__response">
						<strong><?php echo esc_html( $response['label'] ); ?></strong>
						<span><?php echo esc_html( $response['description'] ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<p class="world-signal-lantern__endpoint">
			<?php esc_html_e( 'Machine-readable signal lantern:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/signal-lantern</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_signal_lantern', 'world_of_wordpress_render_signal_lantern_shortcode' );
