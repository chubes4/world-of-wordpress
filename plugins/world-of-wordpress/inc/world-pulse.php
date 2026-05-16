<?php
/**
 * Runtime pulse surface for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gather a small public pulse from the current WordPress runtime.
 *
 * This intentionally avoids private data. It exposes only site-level
 * terrarium readings that are already visible through the public world.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_pulse(): array {
	$post_counts = wp_count_posts( 'post' );
	$page_counts = wp_count_posts( 'page' );
	$theme       = wp_get_theme();
	$plugins     = (array) get_option( 'active_plugins', array() );
	$namespaces  = array();

	if ( function_exists( 'rest_get_server' ) ) {
		$server = rest_get_server();
		if ( is_object( $server ) && method_exists( $server, 'get_namespaces' ) ) {
			$namespaces = (array) $server->get_namespaces();
		}
	}

	return array(
		'generated_at'        => current_time( 'mysql', true ),
		'wordpress_version'   => get_bloginfo( 'version' ),
		'php_version'         => PHP_VERSION,
		'active_theme'        => $theme->get( 'Name' ),
		'active_theme_slug'   => get_stylesheet(),
		'published_posts'     => isset( $post_counts->publish ) ? (int) $post_counts->publish : 0,
		'published_pages'     => isset( $page_counts->publish ) ? (int) $page_counts->publish : 0,
		'active_plugin_count' => count( $plugins ),
		'rest_namespace_count' => count( $namespaces ),
		'world_content_live'   => defined( 'WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR' ) && is_dir( WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR ),
	);
}

/**
 * Register the public world pulse REST route.
 */
function world_of_wordpress_register_pulse_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/pulse',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_pulse() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_pulse_route' );

/**
 * Render a compact public pulse panel for pages and posts.
 *
 * @return string
 */
function world_of_wordpress_render_pulse_shortcode(): string {
	$pulse = world_of_wordpress_get_pulse();
	$rows  = array(
		__( 'WordPress', 'world-of-wordpress' )      => sprintf(
			/* translators: 1: WordPress version, 2: PHP version. */
			__( '%1$s on PHP %2$s', 'world-of-wordpress' ),
			$pulse['wordpress_version'],
			$pulse['php_version']
		),
		__( 'Theme', 'world-of-wordpress' )          => sprintf(
			/* translators: 1: theme name, 2: theme slug. */
			__( '%1$s (%2$s)', 'world-of-wordpress' ),
			$pulse['active_theme'],
			$pulse['active_theme_slug']
		),
		__( 'Visible archive', 'world-of-wordpress' )=> sprintf(
			/* translators: 1: post count, 2: page count. */
			__( '%1$d posts, %2$d pages', 'world-of-wordpress' ),
			$pulse['published_posts'],
			$pulse['published_pages']
		),
		__( 'Runtime surface', 'world-of-wordpress' )=> sprintf(
			/* translators: 1: plugin count, 2: REST namespace count. */
			__( '%1$d active plugins, %2$d REST namespaces', 'world-of-wordpress' ),
			$pulse['active_plugin_count'],
			$pulse['rest_namespace_count']
		),
		__( 'World content', 'world-of-wordpress' )  => $pulse['world_content_live'] ? __( 'Markdown content directory is mounted.', 'world-of-wordpress' ) : __( 'Markdown content directory has not mounted yet.', 'world-of-wordpress' ),
	);

	ob_start();
	?>
	<div class="world-pulse" aria-label="<?php echo esc_attr__( 'World runtime pulse', 'world-of-wordpress' ); ?>">
		<?php foreach ( $rows as $label => $value ) : ?>
			<div class="world-pulse__row">
				<strong><?php echo esc_html( $label ); ?></strong>
				<span><?php echo esc_html( (string) $value ); ?></span>
			</div>
		<?php endforeach; ?>
		<p class="world-pulse__endpoint">
			<?php esc_html_e( 'Machine-readable pulse:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/pulse</code>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'world_pulse', 'world_of_wordpress_render_pulse_shortcode' );
