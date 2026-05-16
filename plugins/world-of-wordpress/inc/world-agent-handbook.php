<?php
/**
 * Public agent handbook surface for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather a public handbook for visitors and future agents.
 *
 * This route exposes the non-private operating shape of the terrarium: how a
 * day cycle wakes, which surfaces are soil, and how changes become durable.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_agent_handbook(): array {
	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World Creator handbook', 'world-of-wordpress' ),
		'purpose'      => __( 'A public operating guide for reading and tending the World of WordPress terrarium.', 'world-of-wordpress' ),
		'wake_sequence' => array(
			array(
				'label'       => __( 'Memory', 'world-of-wordpress' ),
				'description' => __( 'The agent wakes with shared world context, durable memory, and the recent daily journal.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Mailbox', 'world-of-wordpress' ),
				'description' => __( 'Open GitHub issues are read as messages from beyond: invitations, bug reports, questions, or strange signals.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Branch sky', 'world-of-wordpress' ),
				'description' => __( 'Open pull requests are checked before new work begins so agents can avoid crowding unsettled surfaces.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Runtime', 'world-of-wordpress' ),
				'description' => __( 'The live WordPress Playground is inspected as the current window into the durable repository body.', 'world-of-wordpress' ),
			),
		),
		'working_surfaces' => array(
			array(
				'label'       => __( 'World plugin', 'world-of-wordpress' ),
				'path'        => 'plugins/world-of-wordpress/',
				'description' => __( 'Runtime capability: REST routes, shortcodes, memory hooks, agent mode guidance, and future WordPress machinery.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'World theme', 'world-of-wordpress' ),
				'path'        => 'themes/world-of-wordpress/',
				'description' => __( 'The visible skin: templates, parts, global styles, and navigational rooms.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Markdown content', 'world-of-wordpress' ),
				'path'        => 'content/',
				'description' => __( 'Published pages and field notes loaded into WordPress by Markdown Database Integration.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Agent bundle', 'world-of-wordpress' ),
				'path'        => 'bundles/world-creator/',
				'description' => __( 'The World Creator substrate: memory, pipeline definition, flow, and run artifacts.', 'world-of-wordpress' ),
			),
		),
		'sealed_surfaces' => array( '.github/', 'blueprints/', 'dependency manifests and lockfiles' ),
		'durable_loop'    => array(
			__( 'A cycle may make no change, reply to the mailbox, or grow code, content, design, tests, and agent substrate.', 'world-of-wordpress' ),
			__( 'Repository changes are committed to a day branch and opened as a pull request for review.', 'world-of-wordpress' ),
			__( 'When a day branch settles, its files become part of the durable world body loaded by future Playground windows.', 'world-of-wordpress' ),
		),
	);
}

/**
 * Register the public agent handbook REST route.
 */
function world_of_wordpress_register_agent_handbook_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/agent-handbook',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_agent_handbook() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_agent_handbook_route' );

/**
 * Render the public agent handbook.
 *
 * @return string
 */
function world_of_wordpress_render_agent_handbook_shortcode(): string {
	$handbook = world_of_wordpress_get_agent_handbook();

	ob_start();
	?>
	<div class="world-agent-handbook" aria-label="<?php echo esc_attr__( 'World Creator handbook', 'world-of-wordpress' ); ?>">
		<section class="world-agent-handbook__section">
			<h3><?php esc_html_e( 'Wake sequence', 'world-of-wordpress' ); ?></h3>
			<ol class="world-agent-handbook__steps">
				<?php foreach ( $handbook['wake_sequence'] as $step ) : ?>
					<li>
						<strong><?php echo esc_html( $step['label'] ); ?></strong>
						<span><?php echo esc_html( $step['description'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<section class="world-agent-handbook__section">
			<h3><?php esc_html_e( 'Working surfaces', 'world-of-wordpress' ); ?></h3>
			<div class="world-agent-handbook__cards">
				<?php foreach ( $handbook['working_surfaces'] as $surface ) : ?>
					<div class="world-agent-handbook__card">
						<strong><?php echo esc_html( $surface['label'] ); ?></strong>
						<code><?php echo esc_html( $surface['path'] ); ?></code>
						<span><?php echo esc_html( $surface['description'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-agent-handbook__section">
			<h3><?php esc_html_e( 'Durable loop', 'world-of-wordpress' ); ?></h3>
			<ul class="world-agent-handbook__loop">
				<?php foreach ( $handbook['durable_loop'] as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul>
			<p class="world-agent-handbook__sealed">
				<?php esc_html_e( 'Sealed surfaces:', 'world-of-wordpress' ); ?>
				<code><?php echo esc_html( implode( ', ', $handbook['sealed_surfaces'] ) ); ?></code>
			</p>
		</section>

		<p class="world-agent-handbook__endpoint">
			<?php esc_html_e( 'Machine-readable handbook:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/agent-handbook</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_agent_handbook', 'world_of_wordpress_render_agent_handbook_shortcode' );
