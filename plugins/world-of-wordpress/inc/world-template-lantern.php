<?php
/**
 * Public block-theme template lantern for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read bounded HTML files from a block-theme subdirectory.
 *
 * Only the active theme's template and template-part paths are inspected. The
 * response exposes filenames, durable source paths, sizes, and modified times;
 * it never returns template contents.
 *
 * @param string $theme_dir Active theme directory.
 * @param string $stylesheet Active theme stylesheet slug.
 * @param string $subdir Theme subdirectory to inspect.
 * @return array<int, array<string, mixed>>
 */
function world_of_wordpress_get_template_lantern_files( string $theme_dir, string $stylesheet, string $subdir ): array {
	$directory = trailingslashit( $theme_dir ) . $subdir;
	if ( ! is_dir( $directory ) || ! is_readable( $directory ) ) {
		return array();
	}

	$items = scandir( $directory );
	if ( false === $items ) {
		return array();
	}

	$files = array();
	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item || ! str_ends_with( $item, '.html' ) ) {
			continue;
		}

		$path = $directory . '/' . $item;
		if ( ! is_file( $path ) ) {
			continue;
		}

		$modified = filemtime( $path );
		$files[]  = array(
			'slug'        => sanitize_title( basename( $item, '.html' ) ),
			'file'        => $item,
			'source_path' => 'themes/' . $stylesheet . '/' . $subdir . '/' . $item,
			'bytes'       => filesize( $path ),
			'modified_at' => $modified ? gmdate( 'c', $modified ) : null,
		);
	}

	usort(
		$files,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['file'], (string) $b['file'] );
		}
	);

	return $files;
}

/**
 * Build a public inventory of the active block theme's visible structure.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_template_lantern(): array {
	$theme       = wp_get_theme();
	$stylesheet  = get_stylesheet();
	$theme_dir   = get_stylesheet_directory();
	$theme_json  = trailingslashit( $theme_dir ) . 'theme.json';
	$style_css   = trailingslashit( $theme_dir ) . 'style.css';
	$templates   = world_of_wordpress_get_template_lantern_files( $theme_dir, $stylesheet, 'templates' );
	$parts       = world_of_wordpress_get_template_lantern_files( $theme_dir, $stylesheet, 'parts' );
	$theme_meta  = array();
	$theme_data  = array();

	if ( is_readable( $theme_json ) ) {
		$decoded = json_decode( (string) file_get_contents( $theme_json ), true );
		if ( is_array( $decoded ) ) {
			$theme_data = $decoded;
		}
	}

	$settings = isset( $theme_data['settings'] ) && is_array( $theme_data['settings'] ) ? $theme_data['settings'] : array();
	$layout   = isset( $settings['layout'] ) && is_array( $settings['layout'] ) ? $settings['layout'] : array();
	$palette  = isset( $settings['color']['palette'] ) && is_array( $settings['color']['palette'] ) ? $settings['color']['palette'] : array();
	$duotones = isset( $settings['color']['duotone'] ) && is_array( $settings['color']['duotone'] ) ? $settings['color']['duotone'] : array();
	$fonts    = isset( $settings['typography']['fontFamilies'] ) && is_array( $settings['typography']['fontFamilies'] ) ? $settings['typography']['fontFamilies'] : array();

	$theme_meta['theme_json'] = array(
		'source_path'  => 'themes/' . $stylesheet . '/theme.json',
		'exists'       => is_readable( $theme_json ),
		'bytes'        => is_readable( $theme_json ) ? filesize( $theme_json ) : 0,
		'palette'      => count( $palette ),
		'duotones'     => count( $duotones ),
		'fontFamilies' => count( $fonts ),
		'contentSize'  => isset( $layout['contentSize'] ) ? (string) $layout['contentSize'] : null,
		'wideSize'     => isset( $layout['wideSize'] ) ? (string) $layout['wideSize'] : null,
	);
	$theme_meta['style_css']  = array(
		'source_path' => 'themes/' . $stylesheet . '/style.css',
		'exists'      => is_readable( $style_css ),
		'bytes'       => is_readable( $style_css ) ? filesize( $style_css ) : 0,
	);

	return array(
		'generated_at'   => current_time( 'mysql', true ),
		'name'           => __( 'Template lantern', 'world-of-wordpress' ),
		'purpose'        => __( 'A public, non-private reading of the active block theme: templates, template parts, and the theme.json signals that shape the visible glass.', 'world-of-wordpress' ),
		'active_theme'   => array(
			'name'       => $theme->get( 'Name' ),
			'stylesheet' => $stylesheet,
			'template'   => get_template(),
			'version'    => $theme->get( 'Version' ),
		),
		'counts'         => array(
			'templates'      => count( $templates ),
			'template_parts' => count( $parts ),
		),
		'templates'      => $templates,
		'template_parts' => $parts,
		'theme_files'    => $theme_meta,
	);
}

/**
 * Register the public template-lantern REST route.
 */
function world_of_wordpress_register_template_lantern_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/template-lantern',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_template_lantern() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_template_lantern_route' );

/**
 * Render the public template lantern.
 *
 * @return string
 */
function world_of_wordpress_render_template_lantern_shortcode(): string {
	$lantern = world_of_wordpress_get_template_lantern();

	ob_start();
	?>
	<div class="world-template-lantern" aria-label="<?php echo esc_attr__( 'World template lantern', 'world-of-wordpress' ); ?>">
		<p class="world-template-lantern__purpose"><?php echo esc_html( $lantern['purpose'] ); ?></p>

		<div class="world-template-lantern__summary">
			<div>
				<strong><?php echo esc_html( $lantern['active_theme']['name'] ); ?></strong>
				<span><?php esc_html_e( 'active block theme', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $lantern['counts']['templates'] ); ?></strong>
				<span><?php esc_html_e( 'templates', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $lantern['counts']['template_parts'] ); ?></strong>
				<span><?php esc_html_e( 'template parts', 'world-of-wordpress' ); ?></span>
			</div>
		</div>

		<section class="world-template-lantern__section">
			<h3><?php esc_html_e( 'Templates', 'world-of-wordpress' ); ?></h3>
			<div class="world-template-lantern__grid">
				<?php foreach ( $lantern['templates'] as $template ) : ?>
					<article class="world-template-lantern__card">
						<strong><?php echo esc_html( $template['slug'] ); ?></strong>
						<code><?php echo esc_html( $template['source_path'] ); ?></code>
						<span><?php echo esc_html( size_format( (int) $template['bytes'] ) ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-template-lantern__section">
			<h3><?php esc_html_e( 'Template parts', 'world-of-wordpress' ); ?></h3>
			<div class="world-template-lantern__grid">
				<?php foreach ( $lantern['template_parts'] as $part ) : ?>
					<article class="world-template-lantern__card">
						<strong><?php echo esc_html( $part['slug'] ); ?></strong>
						<code><?php echo esc_html( $part['source_path'] ); ?></code>
						<span><?php echo esc_html( size_format( (int) $part['bytes'] ) ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-template-lantern__section">
			<h3><?php esc_html_e( 'Theme.json signals', 'world-of-wordpress' ); ?></h3>
			<div class="world-template-lantern__grid">
				<article class="world-template-lantern__card">
					<strong><?php esc_html_e( 'Palette', 'world-of-wordpress' ); ?></strong>
					<span><?php echo esc_html( (string) $lantern['theme_files']['theme_json']['palette'] ); ?> <?php esc_html_e( 'colors', 'world-of-wordpress' ); ?></span>
					<code><?php echo esc_html( $lantern['theme_files']['theme_json']['source_path'] ); ?></code>
				</article>
				<article class="world-template-lantern__card">
					<strong><?php esc_html_e( 'Layout', 'world-of-wordpress' ); ?></strong>
					<span><?php echo esc_html( (string) $lantern['theme_files']['theme_json']['contentSize'] ); ?> / <?php echo esc_html( (string) $lantern['theme_files']['theme_json']['wideSize'] ); ?></span>
					<code><?php esc_html_e( 'content / wide', 'world-of-wordpress' ); ?></code>
				</article>
			</div>
		</section>

		<p class="world-template-lantern__endpoint">
			<?php esc_html_e( 'Machine-readable template lantern:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/template-lantern</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_template_lantern', 'world_of_wordpress_render_template_lantern_shortcode' );
