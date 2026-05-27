<?php
/**
 * Verify the WP Codebox World of WordPress recipe assembled the runtime.
 */

if ( function_exists( 'wp_set_current_user' ) ) {
	wp_set_current_user( 1 );
}

$failures = array();

$assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
	if ( ! $condition ) {
		$failures[] = $message;
	}
};

$active_plugins = get_option( 'active_plugins', array() );
if ( ! is_array( $active_plugins ) ) {
	$active_plugins = array();
}

$assert( in_array( 'world-of-wordpress/world-of-wordpress.php', $active_plugins, true ), 'World of WordPress plugin is not active.' );
$assert( in_array( 'markdown-database-integration/markdown-database-integration.php', $active_plugins, true ), 'Markdown Database Integration plugin is not active.' );
$assert( in_array( 'agents-api/agents-api.php', $active_plugins, true ), 'Agents API plugin is not active.' );
$assert( in_array( 'data-machine/data-machine.php', $active_plugins, true ), 'Data Machine plugin is not active.' );
$assert( in_array( 'data-machine-code/data-machine-code.php', $active_plugins, true ), 'Data Machine Code plugin is not active.' );
$assert( 'world-of-wordpress' === get_stylesheet(), 'World of WordPress theme is not active.' );
$assert( 'World of WordPress' === get_option( 'blogname' ), 'World site title was not seeded.' );
$assert( 'A living WordPress Playground terrarium.' === get_option( 'blogdescription' ), 'World tagline was not seeded.' );
$assert( defined( 'MARKDOWN_DB_MODE' ) && 'primary' === MARKDOWN_DB_MODE, 'Markdown Database Integration primary mode is not configured.' );
$assert( defined( 'MARKDOWN_DB_CONTENT_DIR' ) && '/wordpress/wp-content/world-content' === MARKDOWN_DB_CONTENT_DIR, 'Markdown Database Integration content directory is not configured.' );
$assert( is_readable( WP_CONTENT_DIR . '/world-content/WORLD.md' ), 'WORLD.md was not staged into world-content.' );

$posts = get_posts(
	array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'any',
		'posts_per_page' => 1,
	)
);
$assert( count( $posts ) > 0, 'No seeded world posts or pages were found.' );

global $wpdb;
$agents_table        = $wpdb->base_prefix . 'datamachine_agents';
$world_creator_agent = $wpdb->get_row(
	$wpdb->prepare( "SELECT agent_id, agent_slug FROM {$agents_table} WHERE agent_slug = %s LIMIT 1", 'world-creator' ),
	ARRAY_A
);

$assert( is_array( $world_creator_agent ) && 'world-creator' === (string) ( $world_creator_agent['agent_slug'] ?? '' ), 'World Creator agent bundle was not imported.' );

if ( $failures ) {
	throw new RuntimeException( implode( "\n", $failures ) );
}

return array(
	'metrics'  => array(
		'world_recipe_verified' => 1,
	),
	'metadata' => array(
		'active_plugins'      => array_values( $active_plugins ),
		'stylesheet'          => get_stylesheet(),
		'site_title'          => get_option( 'blogname' ),
		'seeded_content'      => count( $posts ),
		'world_creator_agent' => is_array( $world_creator_agent ) ? (string) ( $world_creator_agent['agent_slug'] ?? '' ) : '',
	),
);
