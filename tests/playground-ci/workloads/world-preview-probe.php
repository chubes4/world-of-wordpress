<?php
/**
 * Verify the World of WordPress terrarium boots with MDI-backed content.
 */

if ( function_exists( 'wp_set_current_user' ) ) {
	wp_set_current_user( 1 );
}

$metadata = array(
	'markdown_db_dropin'      => defined( 'MARKDOWN_DB_DROPIN' ),
	'markdown_db_mode'        => defined( 'MARKDOWN_DB_MODE' ) ? MARKDOWN_DB_MODE : '',
	'markdown_db_content_dir' => defined( 'MARKDOWN_DB_CONTENT_DIR' ) ? MARKDOWN_DB_CONTENT_DIR : '',
);

if ( function_exists( 'world_of_wordpress_seed_world' ) ) {
	world_of_wordpress_seed_world();
}

$home           = get_page_by_path( 'home', OBJECT, 'page' );
$hello_world    = get_page_by_path( 'hello-world', OBJECT, 'post' );
$sample_page    = get_page_by_path( 'sample-page', OBJECT, 'page' );
$privacy_policy = get_page_by_path( 'privacy-policy', OBJECT, 'page' );

$metadata['home_page_found']   = (bool) $home;
$metadata['home_page_id']      = $home ? (int) $home->ID : 0;
$metadata['front_page_id']     = (int) get_option( 'page_on_front' );
$metadata['hello_world_id']    = $hello_world ? (int) $hello_world->ID : 0;
$metadata['sample_page_id']    = $sample_page ? (int) $sample_page->ID : 0;
$metadata['privacy_policy_id'] = $privacy_policy ? (int) $privacy_policy->ID : 0;

$content_has_seed = $home && str_contains( (string) $home->post_content, 'World of WordPress' );

return array(
	'metrics'  => array(
		'markdown_db_dropin_loaded' => defined( 'MARKDOWN_DB_DROPIN' ) ? 1 : 0,
		'markdown_db_primary_mode'  => defined( 'MARKDOWN_DB_MODE' ) && 'primary' === MARKDOWN_DB_MODE ? 1 : 0,
		'home_page_found'          => $home ? 1 : 0,
		'home_content_seeded'      => $content_has_seed ? 1 : 0,
		'sample_content_removed'   => ( ! $hello_world && ! $sample_page && ! $privacy_policy ) ? 1 : 0,
	),
	'metadata' => $metadata,
);
