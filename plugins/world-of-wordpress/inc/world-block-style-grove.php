<?php
/**
 * Public block style grove for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register world-owned block style variations.
 */
function world_of_wordpress_register_block_style_grove_styles(): void {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}

	register_block_style(
		'core/group',
		array(
			'name'  => 'world-glass-card',
			'label' => __( 'World glass card', 'world-of-wordpress' ),
		)
	);

	register_block_style(
		'core/button',
		array(
			'name'  => 'world-signal-button',
			'label' => __( 'World signal button', 'world-of-wordpress' ),
		)
	);
}
add_action( 'init', 'world_of_wordpress_register_block_style_grove_styles' );

/**
 * Normalize one block style for public display.
 *
 * The grove deliberately omits inline style bodies and style handles. It only
 * names the block, style slug, human label, default status, and whether the
 * style is world-owned.
 *
 * @param string               $block_name Registered block name.
 * @param array<string, mixed> $style      Registered block style metadata.
 * @return array<string, mixed>
 */
function world_of_wordpress_normalize_block_style( string $block_name, array $style ): array {
	$name = isset( $style['name'] ) ? sanitize_key( (string) $style['name'] ) : '';

	return array(
		'block'       => $block_name,
		'name'        => $name,
		'label'       => isset( $style['label'] ) ? wp_strip_all_tags( (string) $style['label'] ) : $name,
		'is_default'  => ! empty( $style['is_default'] ),
		'world_owned' => str_starts_with( $name, 'world-' ),
	);
}

/**
 * Build a public inventory of registered block style variations.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_block_style_grove(): array {
	$registry = class_exists( 'WP_Block_Styles_Registry' ) ? WP_Block_Styles_Registry::get_instance() : null;
	$all      = ( $registry && method_exists( $registry, 'get_all_registered' ) ) ? $registry->get_all_registered() : array();

	$block_counts  = array();
	$samples       = array();
	$world_styles  = array();
	$total_styles  = 0;
	$sample_blocks = array( 'core/group', 'core/button', 'core/query', 'core/image', 'core/separator' );

	foreach ( $all as $block_name => $styles ) {
		if ( ! is_string( $block_name ) || ! is_array( $styles ) ) {
			continue;
		}

		foreach ( $styles as $style ) {
			if ( ! is_array( $style ) ) {
				continue;
			}

			$normalized = world_of_wordpress_normalize_block_style( $block_name, $style );
			if ( '' === $normalized['name'] ) {
				continue;
			}

			++$total_styles;
			if ( ! isset( $block_counts[ $block_name ] ) ) {
				$block_counts[ $block_name ] = 0;
			}
			++$block_counts[ $block_name ];

			if ( $normalized['world_owned'] ) {
				$world_styles[] = $normalized;
			}

			if ( in_array( $block_name, $sample_blocks, true ) && count( $samples ) < 12 ) {
				$samples[] = $normalized;
			}
		}
	}

	ksort( $block_counts );
	usort(
		$world_styles,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['block'] . (string) $a['name'], (string) $b['block'] . (string) $b['name'] );
		}
	);

	$blocks = array();
	foreach ( $block_counts as $block_name => $count ) {
		$blocks[] = array(
			'name'  => $block_name,
			'count' => $count,
		);
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Block style grove', 'world-of-wordpress' ),
		'purpose'      => __( 'A public reading of registered block style variations: small editor-facing choices that change how familiar blocks dress themselves without changing their content.', 'world-of-wordpress' ),
		'counts'       => array(
			'total_styles' => $total_styles,
			'blocks'       => count( $blocks ),
			'world_styles' => count( $world_styles ),
		),
		'blocks'       => $blocks,
		'world_styles' => $world_styles,
		'samples'      => $samples,
	);
}

/**
 * Register the public block-style-grove REST route.
 */
function world_of_wordpress_register_block_style_grove_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/block-style-grove',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_block_style_grove() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_block_style_grove_route' );

/**
 * Render the public block style grove.
 *
 * @return string
 */
function world_of_wordpress_render_block_style_grove_shortcode(): string {
	$grove  = world_of_wordpress_get_block_style_grove();
	$styles = ! empty( $grove['world_styles'] ) ? $grove['world_styles'] : $grove['samples'];

	ob_start();
	?>
	<div class="world-block-style-grove" aria-label="<?php echo esc_attr__( 'World block style grove', 'world-of-wordpress' ); ?>">
		<p class="world-block-style-grove__purpose"><?php echo esc_html( $grove['purpose'] ); ?></p>

		<div class="world-block-style-grove__summary">
			<div>
				<strong><?php echo esc_html( (string) $grove['counts']['total_styles'] ); ?></strong>
				<span><?php esc_html_e( 'registered style variations', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $grove['counts']['blocks'] ); ?></strong>
				<span><?php esc_html_e( 'blocks with styles', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $grove['counts']['world_styles'] ); ?></strong>
				<span><?php esc_html_e( 'world-owned styles', 'world-of-wordpress' ); ?></span>
			</div>
		</div>

		<section class="world-block-style-grove__section">
			<h3><?php echo empty( $grove['world_styles'] ) ? esc_html__( 'Core style samples', 'world-of-wordpress' ) : esc_html__( 'World-owned style variations', 'world-of-wordpress' ); ?></h3>
			<div class="world-block-style-grove__styles">
				<?php foreach ( $styles as $style ) : ?>
					<article class="world-block-style-grove__style">
						<strong><?php echo esc_html( $style['label'] ); ?></strong>
						<code><?php echo esc_html( $style['block'] ); ?> · <?php echo esc_html( $style['name'] ); ?></code>
						<span><?php echo $style['is_default'] ? esc_html__( 'default variation', 'world-of-wordpress' ) : esc_html__( 'optional variation', 'world-of-wordpress' ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-block-style-grove__section">
			<h3><?php esc_html_e( 'Blocks carrying style choices', 'world-of-wordpress' ); ?></h3>
			<div class="world-block-style-grove__blocks">
				<?php foreach ( array_slice( $grove['blocks'], 0, 12 ) as $block ) : ?>
					<article class="world-block-style-grove__block">
						<code><?php echo esc_html( $block['name'] ); ?></code>
						<span><?php echo esc_html( (string) $block['count'] ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<p class="world-block-style-grove__endpoint">
			<?php esc_html_e( 'Machine-readable block style grove:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/block-style-grove</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_block_style_grove', 'world_of_wordpress_render_block_style_grove_shortcode' );
