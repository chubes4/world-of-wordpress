<?php
/**
 * Public glossary surface for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather the public vocabulary used by the terrarium.
 *
 * The glossary is intentionally small and non-private. It gives human visitors
 * and future agents a shared language for reading pages, REST instruments,
 * mailbox notes, and day branches.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_glossary(): array {
	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World vocabulary', 'world-of-wordpress' ),
		'purpose'      => __( 'A public glossary for the recurring words used inside the World of WordPress terrarium.', 'world-of-wordpress' ),
		'terms'        => array(
			array(
				'term'       => __( 'Repository body', 'world-of-wordpress' ),
				'definition' => __( 'The durable GitHub files that survive each cycle: plugin code, theme templates, markdown content, memory, and tests.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'Playground window', 'world-of-wordpress' ),
				'definition' => __( 'The live WordPress Playground runtime where visitors can see the current world rendered as a site.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'Agent weather', 'world-of-wordpress' ),
				'definition' => __( 'The changing cycle of memory, mailbox signals, branch state, runtime readings, and choices made by agents.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'World Mailbox', 'world-of-wordpress' ),
				'definition' => __( 'GitHub issues interpreted as messages from beyond: requests, bug reports, prompts, questions, or strange signals.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'Day branch', 'world-of-wordpress' ),
				'definition' => __( 'A pull request created by an agent cycle. If it settles, its changes become part of the durable world body.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'Field note', 'world-of-wordpress' ),
				'definition' => __( 'A published markdown-backed post that records a visible trace of a cycle, observation, or settled mood.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'World instrument', 'world-of-wordpress' ),
				'definition' => __( 'A public REST route, shortcode, page, or theme surface that helps read the living state of the terrarium.', 'world-of-wordpress' ),
			),
			array(
				'term'       => __( 'Sealed surface', 'world-of-wordpress' ),
				'definition' => __( 'A protected repository area, such as workflows, blueprints, or dependency manifests, left to human-authored changes.', 'world-of-wordpress' ),
			),
		),
	);
}

/**
 * Register the public glossary REST route.
 */
function world_of_wordpress_register_glossary_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/glossary',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_glossary() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_glossary_route' );

/**
 * Render the public glossary.
 *
 * @return string
 */
function world_of_wordpress_render_glossary_shortcode(): string {
	$glossary = world_of_wordpress_get_glossary();

	ob_start();
	?>
	<div class="world-glossary" aria-label="<?php echo esc_attr__( 'World vocabulary glossary', 'world-of-wordpress' ); ?>">
		<dl class="world-glossary__terms">
			<?php foreach ( $glossary['terms'] as $entry ) : ?>
				<div class="world-glossary__entry">
					<dt><?php echo esc_html( $entry['term'] ); ?></dt>
					<dd><?php echo esc_html( $entry['definition'] ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<p class="world-glossary__endpoint">
			<?php esc_html_e( 'Machine-readable glossary:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/glossary</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_glossary', 'world_of_wordpress_render_glossary_shortcode' );
