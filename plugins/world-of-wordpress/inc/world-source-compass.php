<?php
/**
 * Public source compass for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a bounded file inventory from a public world source surface.
 *
 * The source compass intentionally returns only counts and a few repository-
 * relative paths for known world-owned surfaces. It does not read file
 * contents, callbacks, credentials, uploads, logs, database files, or memory.
 *
 * @param string $base_path      Runtime directory path to inspect.
 * @param string $relative_root  Public repository-style root for display.
 * @return array<string, mixed>
 */
function world_of_wordpress_count_source_surface( string $base_path, string $relative_root ): array {
	$result = array(
		'exists'       => is_dir( $base_path ),
		'file_count'  => 0,
		'sample_paths' => array(),
	);

	if ( ! $result['exists'] ) {
		return $result;
	}

	$base_real = realpath( $base_path );
	if ( false === $base_real ) {
		$result['exists'] = false;
		return $result;
	}

	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $base_real, FilesystemIterator::SKIP_DOTS )
		);
	} catch ( UnexpectedValueException $exception ) {
		return $result;
	}

	foreach ( $iterator as $file ) {
		if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
			continue;
		}

		$result['file_count']++;

		if ( count( $result['sample_paths'] ) >= 6 ) {
			continue;
		}

		$path = $file->getPathname();
		if ( 0 !== strpos( $path, $base_real ) ) {
			continue;
		}

		$relative_path            = ltrim( str_replace( DIRECTORY_SEPARATOR, '/', substr( $path, strlen( $base_real ) ) ), '/' );
		$result['sample_paths'][] = trailingslashit( $relative_root ) . $relative_path;
	}

	sort( $result['sample_paths'] );

	return $result;
}

/**
 * Gather public source surface readings.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_source_compass(): array {
	$repo_root_in_ci = dirname( WORLD_OF_WORDPRESS_PLUGIN_DIR, 2 );
	$theme_runtime   = get_theme_root( 'world-of-wordpress' ) . '/world-of-wordpress';

	$surfaces = array(
		array(
			'label'         => __( 'World plugin', 'world-of-wordpress' ),
			'path'          => 'plugins/world-of-wordpress/',
			'description'   => __( 'Runtime capability: public REST routes, shortcodes, world seeding, and agent-facing instruments.', 'world-of-wordpress' ),
			'runtime_path'  => WORLD_OF_WORDPRESS_PLUGIN_DIR,
		),
		array(
			'label'         => __( 'World theme', 'world-of-wordpress' ),
			'path'          => 'themes/world-of-wordpress/',
			'description'   => __( 'The visible block theme: templates, parts, styles, and the terrarium skin.', 'world-of-wordpress' ),
			'runtime_path'  => is_dir( $repo_root_in_ci . '/themes/world-of-wordpress' ) ? $repo_root_in_ci . '/themes/world-of-wordpress' : $theme_runtime,
		),
		array(
			'label'         => __( 'Markdown content', 'world-of-wordpress' ),
			'path'          => 'content/',
			'description'   => __( 'Durable WordPress posts and pages loaded into Playground by Markdown Database Integration.', 'world-of-wordpress' ),
			'runtime_path'  => is_dir( $repo_root_in_ci . '/content' ) ? $repo_root_in_ci . '/content' : WORLD_OF_WORDPRESS_WORLD_CONTENT_DIR,
		),
	);

	foreach ( $surfaces as &$surface ) {
		$inventory = world_of_wordpress_count_source_surface( $surface['runtime_path'], $surface['path'] );

		$surface['exists']       = (bool) $inventory['exists'];
		$surface['file_count']   = (int) $inventory['file_count'];
		$surface['sample_paths'] = $inventory['sample_paths'];

		unset( $surface['runtime_path'] );
	}
	unset( $surface );

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Source compass', 'world-of-wordpress' ),
		'purpose'      => __( 'A bounded public reading of the world-owned source surfaces that make the terrarium durable.', 'world-of-wordpress' ),
		'surfaces'     => $surfaces,
	);
}

/**
 * Register the public source compass REST route.
 */
function world_of_wordpress_register_source_compass_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/source-compass',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_source_compass() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_source_compass_route' );

/**
 * Render the public source compass.
 *
 * @return string
 */
function world_of_wordpress_render_source_compass_shortcode(): string {
	$compass = world_of_wordpress_get_source_compass();

	ob_start();
	?>
	<div class="world-source-compass" aria-label="<?php echo esc_attr__( 'Source compass', 'world-of-wordpress' ); ?>">
		<p class="world-source-compass__purpose"><?php echo esc_html( $compass['purpose'] ); ?></p>

		<div class="world-source-compass__cards">
			<?php foreach ( $compass['surfaces'] as $surface ) : ?>
				<article class="world-source-compass__card">
					<div class="world-source-compass__card-head">
						<strong><?php echo esc_html( $surface['label'] ); ?></strong>
						<span><?php echo esc_html( sprintf( _n( '%d file', '%d files', (int) $surface['file_count'], 'world-of-wordpress' ), (int) $surface['file_count'] ) ); ?></span>
					</div>
					<code><?php echo esc_html( $surface['path'] ); ?></code>
					<p><?php echo esc_html( $surface['description'] ); ?></p>
					<?php if ( ! empty( $surface['sample_paths'] ) ) : ?>
						<ul>
							<?php foreach ( $surface['sample_paths'] as $sample_path ) : ?>
								<li><code><?php echo esc_html( $sample_path ); ?></code></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="world-source-compass__endpoint">
			<?php esc_html_e( 'Machine-readable source compass:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/source-compass</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_source_compass', 'world_of_wordpress_render_source_compass_shortcode' );
