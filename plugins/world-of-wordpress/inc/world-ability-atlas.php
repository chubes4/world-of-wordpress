<?php
/**
 * Public ability atlas for the World of WordPress runtime.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return known ability namespace descriptions for the public atlas.
 *
 * @return array<string, string>
 */
function world_of_wordpress_get_ability_namespace_descriptions(): array {
	return array(
		'agents'              => __( 'Agent identities, sessions, and agent-shaped runtime operations.', 'world-of-wordpress' ),
		'block-format-bridge' => __( 'Block editor format bridges for moving between markup and structured blocks.', 'world-of-wordpress' ),
		'datamachine'         => __( 'Data Machine substrate abilities for pipelines, memory, files, and automation.', 'world-of-wordpress' ),
		'datamachine-code'    => __( 'Repository, GitHub, and workspace hands used by coding agents.', 'world-of-wordpress' ),
		'markdown-db'         => __( 'Markdown Database Integration abilities that bind durable markdown to WordPress content.', 'world-of-wordpress' ),
	);
}

/**
 * Gather public counts of the registered Abilities API surface.
 *
 * The atlas intentionally exposes only namespace-level counts and plain-language
 * descriptions. It does not list ability arguments, credentials, users, memory,
 * or private runtime data.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_ability_atlas(): array {
	$ability_names = array();
	if ( function_exists( 'wp_get_abilities' ) ) {
		$registry = wp_get_abilities();
		if ( is_array( $registry ) ) {
			$ability_names = array_filter( array_keys( $registry ), 'is_string' );
		}
	}

	$namespace_counts = array();
	foreach ( $ability_names as $ability_name ) {
		$parts     = explode( '/', $ability_name, 2 );
		$namespace = sanitize_key( (string) $parts[0] );
		if ( '' === $namespace ) {
			continue;
		}

		if ( ! isset( $namespace_counts[ $namespace ] ) ) {
			$namespace_counts[ $namespace ] = 0;
		}
		++$namespace_counts[ $namespace ];
	}

	ksort( $namespace_counts );
	$descriptions = world_of_wordpress_get_ability_namespace_descriptions();
	$namespaces   = array();

	foreach ( $namespace_counts as $namespace => $count ) {
		$namespaces[] = array(
			'namespace'   => $namespace,
			'count'       => (int) $count,
			'description' => $descriptions[ $namespace ] ?? __( 'A registered ability namespace available to the running WordPress application.', 'world-of-wordpress' ),
		);
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'World ability atlas', 'world-of-wordpress' ),
		'purpose'      => __( 'A namespace-level map of the Abilities API surface available inside the terrarium.', 'world-of-wordpress' ),
		'total'        => count( $ability_names ),
		'namespaces'   => $namespaces,
		'available'    => function_exists( 'wp_get_abilities' ),
	);
}

/**
 * Register the public ability atlas REST route.
 */
function world_of_wordpress_register_ability_atlas_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/ability-atlas',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_ability_atlas() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_ability_atlas_route' );

/**
 * Render the public ability atlas.
 *
 * @return string
 */
function world_of_wordpress_render_ability_atlas_shortcode(): string {
	$atlas = world_of_wordpress_get_ability_atlas();

	ob_start();
	?>
	<div class="world-ability-atlas" aria-label="<?php echo esc_attr__( 'World ability atlas', 'world-of-wordpress' ); ?>">
		<p class="world-ability-atlas__purpose"><?php echo esc_html( $atlas['purpose'] ); ?></p>

		<div class="world-ability-atlas__summary">
			<strong><?php echo esc_html( (string) $atlas['total'] ); ?></strong>
			<span><?php esc_html_e( 'registered abilities across the live WordPress runtime', 'world-of-wordpress' ); ?></span>
		</div>

		<?php if ( ! empty( $atlas['namespaces'] ) ) : ?>
			<div class="world-ability-atlas__namespaces">
				<?php foreach ( $atlas['namespaces'] as $namespace ) : ?>
					<article class="world-ability-atlas__namespace">
						<strong><code><?php echo esc_html( $namespace['namespace'] ); ?>/*</code></strong>
						<span>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d: ability count. */
									_n( '%d ability', '%d abilities', (int) $namespace['count'], 'world-of-wordpress' ),
									(int) $namespace['count']
								)
							);
							?>
						</span>
						<small><?php echo esc_html( $namespace['description'] ); ?></small>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="world-ability-atlas__empty"><?php esc_html_e( 'No public ability registry is visible in this runtime yet.', 'world-of-wordpress' ); ?></p>
		<?php endif; ?>

		<p class="world-ability-atlas__endpoint">
			<?php esc_html_e( 'Machine-readable ability atlas:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/ability-atlas</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_ability_atlas', 'world_of_wordpress_render_ability_atlas_shortcode' );
