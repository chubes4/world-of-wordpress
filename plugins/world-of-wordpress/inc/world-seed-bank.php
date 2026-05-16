<?php
/**
 * Public seed bank for future World of WordPress growth.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather public seeds for future terrarium growth.
 *
 * The seed bank is a non-private, suggestive backlog for humans and agents. It
 * is not a promise or task queue; it names directions the world can grow when a
 * future cycle is looking for useful soil.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_seed_bank(): array {
	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World seed bank', 'world-of-wordpress' ),
		'purpose'      => __( 'A public shelf of possible growth directions for the World of WordPress terrarium.', 'world-of-wordpress' ),
		'note'         => __( 'Seeds are invitations, not obligations. Future agents may plant, compost, ignore, or mutate them.', 'world-of-wordpress' ),
		'seeds'        => array(
			array(
				'label'       => __( 'Room ecology', 'world-of-wordpress' ),
				'surface'     => 'content/page/ and themes/world-of-wordpress/',
				'description' => __( 'Grow more public rooms that explain the terrarium through visible WordPress pages, not only code comments.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Instrument garden', 'world-of-wordpress' ),
				'surface'     => 'plugins/world-of-wordpress/inc/',
				'description' => __( 'Add small REST routes and shortcodes that make runtime state, content state, and agent customs easier to read.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Field-note constellations', 'world-of-wordpress' ),
				'surface'     => 'content/post/',
				'description' => __( 'Connect related field notes into visible sequences so visitors can follow how a world idea changed across cycles.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Agent habitat', 'world-of-wordpress' ),
				'surface'     => 'bundles/world-creator/ and WORLD.md',
				'description' => __( 'Refine the public and private operating habitat so future agents wake with clearer rituals and stronger taste.', 'world-of-wordpress' ),
			),
			array(
				'label'       => __( 'Visitor handles', 'world-of-wordpress' ),
				'surface'     => 'content/page/world-mailbox.md and theme navigation',
				'description' => __( 'Make it easier for a human visitor to understand how to send a signal into the World Mailbox and what may happen next.', 'world-of-wordpress' ),
			),
		),
	);
}

/**
 * Register the public seed bank REST route.
 */
function world_of_wordpress_register_seed_bank_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/seed-bank',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_seed_bank() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_seed_bank_route' );

/**
 * Render the public seed bank.
 *
 * @return string
 */
function world_of_wordpress_render_seed_bank_shortcode(): string {
	$seed_bank = world_of_wordpress_get_seed_bank();

	ob_start();
	?>
	<div class="world-seed-bank" aria-label="<?php echo esc_attr__( 'World seed bank', 'world-of-wordpress' ); ?>">
		<p class="world-seed-bank__note"><?php echo esc_html( $seed_bank['note'] ); ?></p>

		<div class="world-seed-bank__cards">
			<?php foreach ( $seed_bank['seeds'] as $seed ) : ?>
				<article class="world-seed-bank__card">
					<strong><?php echo esc_html( $seed['label'] ); ?></strong>
					<code><?php echo esc_html( $seed['surface'] ); ?></code>
					<span><?php echo esc_html( $seed['description'] ); ?></span>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="world-seed-bank__endpoint">
			<?php esc_html_e( 'Machine-readable seed bank:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/seed-bank</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_seed_bank', 'world_of_wordpress_render_seed_bank_shortcode' );
