<?php
/**
 * Native runtime pulse card block for the World of WordPress.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the first world-owned dynamic block type.
 */
function world_of_wordpress_register_runtime_pulse_card_block(): void {
	register_block_type(
		'world-of-wordpress/runtime-pulse-card',
		array(
			'api_version'     => 3,
			'title'           => __( 'Runtime Pulse Card', 'world-of-wordpress' ),
			'category'        => 'widgets',
			'description'     => __( 'A server-rendered world block that turns the public runtime pulse into a reusable card.', 'world-of-wordpress' ),
			'keywords'        => array(
				__( 'world', 'world-of-wordpress' ),
				__( 'runtime', 'world-of-wordpress' ),
				__( 'pulse', 'world-of-wordpress' ),
			),
			'attributes'      => array(
				'heading' => array(
					'type'    => 'string',
					'default' => __( 'Live runtime pulse', 'world-of-wordpress' ),
				),
				'note'    => array(
					'type'    => 'string',
					'default' => __( 'This is a native dynamic block owned by the terrarium plugin. It reuses the same public pulse data as the REST route and shortcode.', 'world-of-wordpress' ),
				),
			),
			'supports'        => array(
				'align'      => array( 'wide', 'full' ),
				'anchor'     => true,
				'color'      => array(
					'background' => true,
					'text'       => true,
				),
				'spacing'    => array(
					'margin'  => true,
					'padding' => true,
				),
				'typography' => array(
					'fontSize' => true,
				),
			),
			'render_callback' => 'world_of_wordpress_render_runtime_pulse_card_block',
		)
	);
}
add_action( 'init', 'world_of_wordpress_register_runtime_pulse_card_block' );

/**
 * Render the runtime pulse card block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param string              $content    Saved block content.
 * @param WP_Block|null       $block      Parsed block instance.
 * @return string
 */
function world_of_wordpress_render_runtime_pulse_card_block( array $attributes, string $content = '', ?WP_Block $block = null ): string {
	if ( ! function_exists( 'world_of_wordpress_get_pulse' ) ) {
		return '';
	}

	$pulse   = world_of_wordpress_get_pulse();
	$heading = isset( $attributes['heading'] ) && '' !== $attributes['heading'] ? wp_strip_all_tags( (string) $attributes['heading'] ) : __( 'Live runtime pulse', 'world-of-wordpress' );
	$note    = isset( $attributes['note'] ) && '' !== $attributes['note'] ? wp_strip_all_tags( (string) $attributes['note'] ) : __( 'This native block is rendered by the world plugin.', 'world-of-wordpress' );
	$wrapper = get_block_wrapper_attributes(
		array(
			'class' => 'world-runtime-pulse-card',
		)
	);

	$readings = array(
		__( 'WordPress', 'world-of-wordpress' ) => sprintf(
			/* translators: 1: WordPress version, 2: PHP version. */
			__( '%1$s / PHP %2$s', 'world-of-wordpress' ),
			$pulse['wordpress_version'],
			$pulse['php_version']
		),
		__( 'Theme', 'world-of-wordpress' )     => sprintf(
			/* translators: 1: theme name, 2: theme slug. */
			__( '%1$s (%2$s)', 'world-of-wordpress' ),
			$pulse['active_theme'],
			$pulse['active_theme_slug']
		),
		__( 'Archive', 'world-of-wordpress' )   => sprintf(
			/* translators: 1: post count, 2: page count. */
			__( '%1$d posts / %2$d pages', 'world-of-wordpress' ),
			$pulse['published_posts'],
			$pulse['published_pages']
		),
		__( 'REST', 'world-of-wordpress' )      => sprintf(
			/* translators: %d: REST namespace count. */
			__( '%d namespaces awake', 'world-of-wordpress' ),
			$pulse['rest_namespace_count']
		),
	);

	ob_start();
	?>
	<section <?php echo wp_kses_data( $wrapper ); ?>>
		<div class="world-runtime-pulse-card__header">
			<p><?php esc_html_e( 'World-owned block', 'world-of-wordpress' ); ?></p>
			<h3><?php echo esc_html( $heading ); ?></h3>
			<span><?php echo esc_html( $note ); ?></span>
		</div>

		<dl class="world-runtime-pulse-card__readings">
			<?php foreach ( $readings as $label => $value ) : ?>
				<div>
					<dt><?php echo esc_html( $label ); ?></dt>
					<dd><?php echo esc_html( (string) $value ); ?></dd>
				</div>
			<?php endforeach; ?>
		</dl>

		<p class="world-runtime-pulse-card__source">
			<?php esc_html_e( 'Block type:', 'world-of-wordpress' ); ?>
			<code>world-of-wordpress/runtime-pulse-card</code>
		</p>
	</section>
	<?php

	return (string) ob_get_clean();
}
