<?php
/**
 * Public block registry garden for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a compact list of enabled support groups for a registered block type.
 *
 * @param WP_Block_Type $block_type Registered block type object.
 * @return array<int, string>
 */
function world_of_wordpress_get_block_support_groups( WP_Block_Type $block_type ): array {
	$supports = is_array( $block_type->supports ) ? $block_type->supports : array();
	$groups   = array();

	foreach ( array( 'align', 'anchor', 'color', 'spacing', 'typography', 'layout', 'shadow', 'dimensions', 'position', 'interactivity' ) as $key ) {
		if ( ! empty( $supports[ $key ] ) ) {
			$groups[] = $key;
		}
	}

	return $groups;
}

/**
 * Normalize a registered block type for public display.
 *
 * The garden only exposes public block metadata: name, title, category,
 * API version, selected supports, and attribute count. It deliberately avoids
 * callbacks, render functions, editor scripts, handles, file paths, and raw
 * attribute schemas.
 *
 * @param WP_Block_Type $block_type Registered block type object.
 * @return array<string, mixed>
 */
function world_of_wordpress_normalize_block_type( WP_Block_Type $block_type ): array {
	$name      = (string) $block_type->name;
	$namespace = str_contains( $name, '/' ) ? (string) strtok( $name, '/' ) : 'unknown';
	$title     = isset( $block_type->title ) && '' !== $block_type->title ? wp_strip_all_tags( (string) $block_type->title ) : $name;
	$category  = isset( $block_type->category ) ? sanitize_key( (string) $block_type->category ) : '';
	$attributes = is_array( $block_type->attributes ) ? $block_type->attributes : array();

	return array(
		'name'            => $name,
		'namespace'       => $namespace,
		'title'           => $title,
		'category'        => $category,
		'api_version'     => isset( $block_type->api_version ) ? (int) $block_type->api_version : null,
		'attribute_count' => count( $attributes ),
		'supports'        => world_of_wordpress_get_block_support_groups( $block_type ),
	);
}

/**
 * Build a public inventory of the registered block-type surface.
 *
 * @return array<string, mixed>
 */
function world_of_wordpress_get_block_garden(): array {
	$registry = class_exists( 'WP_Block_Type_Registry' ) ? WP_Block_Type_Registry::get_instance() : null;
	$blocks   = $registry ? $registry->get_all_registered() : array();

	$namespace_counts = array();
	$core_samples     = array();
	$world_blocks     = array();

	foreach ( $blocks as $block_type ) {
		if ( ! $block_type instanceof WP_Block_Type ) {
			continue;
		}

		$normalized = world_of_wordpress_normalize_block_type( $block_type );
		$namespace  = (string) $normalized['namespace'];

		if ( ! isset( $namespace_counts[ $namespace ] ) ) {
			$namespace_counts[ $namespace ] = 0;
		}
		++$namespace_counts[ $namespace ];

		if ( 'world-of-wordpress' === $namespace ) {
			$world_blocks[] = $normalized;
			continue;
		}

		if ( 'core' === $namespace && in_array( $normalized['name'], array( 'core/group', 'core/query', 'core/template-part', 'core/pattern', 'core/navigation', 'core/post-content' ), true ) ) {
			$core_samples[] = $normalized;
		}
	}

	ksort( $namespace_counts );
	usort(
		$core_samples,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['name'], (string) $b['name'] );
		}
	);
	usort(
		$world_blocks,
		static function ( array $a, array $b ): int {
			return strcmp( (string) $a['name'], (string) $b['name'] );
		}
	);

	$namespaces = array();
	foreach ( $namespace_counts as $namespace => $count ) {
		$namespaces[] = array(
			'name'  => $namespace,
			'count' => $count,
		);
	}

	return array(
		'generated_at' => current_time( 'mysql', true ),
		'name'         => __( 'Block garden', 'world-of-wordpress' ),
		'purpose'      => __( 'A public reading of the registered block-type surface that powers the editor and the visible theme without exposing callbacks, scripts, or raw schemas.', 'world-of-wordpress' ),
		'counts'       => array(
			'total_blocks' => count( $blocks ),
			'namespaces'   => count( $namespaces ),
			'world_blocks' => count( $world_blocks ),
		),
		'namespaces'   => $namespaces,
		'core_samples' => $core_samples,
		'world_blocks' => $world_blocks,
	);
}

/**
 * Register the public block-garden REST route.
 */
function world_of_wordpress_register_block_garden_route(): void {
	register_rest_route(
		'world-of-wordpress/v1',
		'/block-garden',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => static function (): WP_REST_Response {
				return rest_ensure_response( world_of_wordpress_get_block_garden() );
			},
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'world_of_wordpress_register_block_garden_route' );

/**
 * Render the public block garden.
 *
 * @return string
 */
function world_of_wordpress_render_block_garden_shortcode(): string {
	$garden = world_of_wordpress_get_block_garden();
	$blocks = ! empty( $garden['world_blocks'] ) ? $garden['world_blocks'] : $garden['core_samples'];

	ob_start();
	?>
	<div class="world-block-garden" aria-label="<?php echo esc_attr__( 'World block garden', 'world-of-wordpress' ); ?>">
		<p class="world-block-garden__purpose"><?php echo esc_html( $garden['purpose'] ); ?></p>

		<div class="world-block-garden__summary">
			<div>
				<strong><?php echo esc_html( (string) $garden['counts']['total_blocks'] ); ?></strong>
				<span><?php esc_html_e( 'registered block types', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $garden['counts']['namespaces'] ); ?></strong>
				<span><?php esc_html_e( 'block namespaces', 'world-of-wordpress' ); ?></span>
			</div>
			<div>
				<strong><?php echo esc_html( (string) $garden['counts']['world_blocks'] ); ?></strong>
				<span><?php esc_html_e( 'world-owned blocks', 'world-of-wordpress' ); ?></span>
			</div>
		</div>

		<section class="world-block-garden__section">
			<h3><?php esc_html_e( 'Namespaces', 'world-of-wordpress' ); ?></h3>
			<div class="world-block-garden__namespaces">
				<?php foreach ( $garden['namespaces'] as $namespace ) : ?>
					<article class="world-block-garden__namespace">
						<code><?php echo esc_html( $namespace['name'] ); ?></code>
						<span><?php echo esc_html( (string) $namespace['count'] ); ?></span>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="world-block-garden__section">
			<h3><?php echo empty( $garden['world_blocks'] ) ? esc_html__( 'Core composition samples', 'world-of-wordpress' ) : esc_html__( 'World-owned blocks', 'world-of-wordpress' ); ?></h3>
			<div class="world-block-garden__blocks">
				<?php foreach ( $blocks as $block ) : ?>
					<article class="world-block-garden__block">
						<strong><?php echo esc_html( $block['title'] ); ?></strong>
						<code><?php echo esc_html( $block['name'] ); ?></code>
						<span><?php echo esc_html( $block['category'] ); ?> · <?php echo esc_html( (string) $block['attribute_count'] ); ?> <?php esc_html_e( 'attributes', 'world-of-wordpress' ); ?></span>
						<?php if ( ! empty( $block['supports'] ) ) : ?>
							<small><?php echo esc_html( implode( ', ', $block['supports'] ) ); ?></small>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<p class="world-block-garden__endpoint">
			<?php esc_html_e( 'Machine-readable block garden:', 'world-of-wordpress' ); ?>
			<code>/wp-json/world-of-wordpress/v1/block-garden</code>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'world_block_garden', 'world_of_wordpress_render_block_garden_shortcode' );
