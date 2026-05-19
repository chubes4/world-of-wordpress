<?php
/**
 * Public design-token spring for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalize a theme.json color-like preset for public display.
 *
 * @param array<string, mixed> $preset Raw theme.json preset.
 * @param string              $value_key Value key to expose.
 * @return array<string, string>
 */
function world_of_wordpress_normalize_style_preset( array $preset, string $value_key ): array {
	$name  = isset( $preset['name'] ) ? wp_strip_all_tags( (string) $preset['name'] ) : '';
	$slug  = isset( $preset['slug'] ) ? sanitize_title( (string) $preset['slug'] ) : sanitize_title( $name );
	$value = isset( $preset[ $value_key ] ) ? (string) $preset[ $value_key ] : '';

	return array(
		'name'  => '' !== $name ? $name : $slug,
		'slug'  => $slug,
		'value' => $value,
	);
}

/**
 * Build a public, non-private inventory of active theme design tokens.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_style_spring(): array {
	$theme      = wp_get_theme();
	$stylesheet = get_stylesheet();
	$theme_json = trailingslashit( get_stylesheet_directory() ) . 'theme.json';
	$theme_data = array();

	if ( is_readable( $theme_json ) ) {
		$decoded = json_decode( (string) file_get_contents( $theme_json ), true );
		if ( is_array( $decoded ) ) {
			$theme_data = $decoded;
		}
	}

	$settings  = isset( $theme_data['settings'] ) && is_array( $theme_data['settings'] ) ? $theme_data['settings'] : array();
	$styles    = isset( $theme_data['styles'] ) && is_array( $theme_data['styles'] ) ? $theme_data['styles'] : array();
	$layout    = isset( $settings['layout'] ) && is_array( $settings['layout'] ) ? $settings['layout'] : array();
	$color     = isset( $settings['color'] ) && is_array( $settings['color'] ) ? $settings['color'] : array();
	$typography = isset( $settings['typography'] ) && is_array( $settings['typography'] ) ? $settings['typography'] : array();
	$spacing   = isset( $styles['spacing'] ) && is_array( $styles['spacing'] ) ? $styles['spacing'] : array();

	$palette = array();
	if ( isset( $color['palette'] ) && is_array( $color['palette'] ) ) {
		foreach ( $color['palette'] as $preset ) {
			if ( is_array( $preset ) ) {
				$palette[] = world_of_wordpress_normalize_style_preset( $preset, 'color' );
			}
		}
	}

	$gradients = array();
	if ( isset( $color['gradients'] ) && is_array( $color['gradients'] ) ) {
		foreach ( $color['gradients'] as $preset ) {
			if ( is_array( $preset ) ) {
				$gradients[] = world_of_wordpress_normalize_style_preset( $preset, 'gradient' );
			}
		}
	}

	$fonts = array();
	if ( isset( $typography['fontFamilies'] ) && is_array( $typography['fontFamilies'] ) ) {
		foreach ( $typography['fontFamilies'] as $font ) {
			if ( ! is_array( $font ) ) {
				continue;
			}

			$name   = isset( $font['name'] ) ? wp_strip_all_tags( (string) $font['name'] ) : '';
			$slug   = isset( $font['slug'] ) ? sanitize_title( (string) $font['slug'] ) : sanitize_title( $name );
			$family = isset( $font['fontFamily'] ) ? (string) $font['fontFamily'] : '';

			$fonts[] = array(
				'name'        => '' !== $name ? $name : $slug,
				'slug'        => $slug,
				'font_family' => $family,
			);
		}
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Style spring', 'world-of-wordpress' ),
		'purpose'      => __( 'A public reading of the active block theme design tokens: colors, gradients, font families, layout sizes, and global spacing.', 'world-of-wordpress' ),
		'active_theme' => array(
			'name'       => $theme->get( 'Name' ),
			'stylesheet' => $stylesheet,
			'version'    => $theme->get( 'Version' ),
		),
		'source_path'  => 'themes/' . $stylesheet . '/theme.json',
		'counts'       => array(
			'palette'   => count( $palette ),
			'gradients' => count( $gradients ),
			'fonts'     => count( $fonts ),
		),
		'layout'       => array(
			'contentSize' => isset( $layout['contentSize'] ) ? (string) $layout['contentSize'] : null,
			'wideSize'    => isset( $layout['wideSize'] ) ? (string) $layout['wideSize'] : null,
		),
		'spacing'      => array(
			'blockGap' => isset( $spacing['blockGap'] ) ? (string) $spacing['blockGap'] : null,
			'padding'  => isset( $spacing['padding'] ) && is_array( $spacing['padding'] ) ? $spacing['padding'] : array(),
		),
		'palette'      => $palette,
		'gradients'    => $gradients,
		'fonts'        => $fonts,
	);
}

/**
 * Register the public style-spring REST route.
 */
function world_of_wordpress_register_style_spring_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/style-spring',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_style_spring() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_style_spring_route' );

/**
 * Render the public style spring.
 *
 * @return string
 */
function world_of_wordpress_render_style_spring_shortcode(): string {
	$spring = world_of_wordpress_get_style_spring();

	ob_start();
	?>
	<div class="world-style-spring" aria-label="<?php echo esc_attr__( 'World style spring', 'world-of-wordpress' ); ?>">
		<p class="world-style-spring__purpose"><?php echo esc_html( $spring['purpose'] ); ?></p>

		<div class="world-style-spring__summary">
			<div>
				<strong><?php echo esc_html( (string) $spring['counts']['palette'] ); ?></strong>
				<span><?php esc_html_e( 'palette colors', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $spring['counts']['gradients'] ); ?></strong>
				<span><?php esc_html_e( 'gradients', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $spring['counts']['fonts'] ); ?></strong>
				<span><?php esc_html_e( 'font families', 'world-of-wordpress' ); ?></span>
			</div>
		</div>

		<section class="world-style-spring__section">
			<h3><?php esc_html_e( 'Palette', 'world-of-wordpress' ); ?></h3>
			<div class="world-style-spring__swatches">
				<?php foreach ( $spring['palette'] as $color ) : ?>
					<article class="world-style-spring__swatch">
						<span class="world-style-spring__chip" style="--world-style-spring-color: <?php echo esc_attr( $color['value'] ); ?>"></span>
						<strong><?php echo esc_html( $color['name'] ); ?></strong>
						<code><?php echo esc_html( $color['slug'] ); ?></code>
						<small><?php echo esc_html( $color['value'] ); ?></small>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-style-spring__section">
			<h3><?php esc_html_e( 'Typography and layout', 'world-of-wordpress' ); ?></h3>
			<div class="world-style-spring__cards">
				<?php foreach ( $spring['fonts'] as $font ) : ?>
					<article class="world-style-spring__card">
						<strong><?php echo esc_html( $font['name'] ); ?></strong>
						<code><?php echo esc_html( $font['slug'] ); ?></code>
						<span><?php echo esc_html( $font['font_family'] ); ?></span>
					</article>
				<?php endforeach; ?>
				<article class="world-style-spring__card">
					<strong><?php esc_html_e( 'Layout', 'world-of-wordpress' ); ?></strong>
					<span><?php echo esc_html( (string) $spring['layout']['contentSize'] ); ?> / <?php echo esc_html( (string) $spring['layout']['wideSize'] ); ?></span>
					<code><?php esc_html_e( 'content / wide', 'world-of-wordpress' ); ?></code>
				</article>
			</div>
		</section>

		<p class="world-style-spring__endpoint">
			<?php esc_html_e( 'Machine-readable style spring:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/style-spring</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_style_spring', 'world_of_wordpress_render_style_spring_shortcode' );
