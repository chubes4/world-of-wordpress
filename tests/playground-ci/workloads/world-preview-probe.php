<?php
/**
 * Verify the World Creator terrarium boots into the expected visible world.
 */

if ( function_exists( 'wp_set_current_user' ) ) {
	wp_set_current_user( 1 );
}

$metadata = array( 'terrarium_contract' => 'world_creator_preview' );

if ( function_exists( 'world_of_wordpress_seed_world' ) ) {
	world_of_wordpress_seed_world();
}

$hello_world    = get_page_by_path( 'hello-world', OBJECT, 'post' );
$sample_page    = get_page_by_path( 'sample-page', OBJECT, 'page' );
$privacy_policy = get_page_by_path( 'privacy-policy', OBJECT, 'page' );
$comment_count  = count( get_comments( array( 'status' => 'all' ) ) );
$site_title     = (string) get_option( 'blogname' );
$tagline        = (string) get_option( 'blogdescription' );
$stylesheet     = (string) get_stylesheet();
$show_on_front  = (string) get_option( 'show_on_front' );

$metadata['front_page_id']     = (int) get_option( 'page_on_front' );
$metadata['hello_world_id']    = $hello_world ? (int) $hello_world->ID : 0;
$metadata['sample_page_id']    = $sample_page ? (int) $sample_page->ID : 0;
$metadata['privacy_policy_id'] = $privacy_policy ? (int) $privacy_policy->ID : 0;
$metadata['comment_count']     = $comment_count;
$metadata['site_title']        = $site_title;
$metadata['tagline']           = $tagline;
$metadata['stylesheet']        = $stylesheet;
$metadata['show_on_front']     = $show_on_front;

return array(
	'metrics'  => array(
		'world_theme_active'       => 'world-of-wordpress' === $stylesheet ? 1 : 0,
		'posts_front_page'         => 'posts' === $show_on_front ? 1 : 0,
		'site_title_seeded'        => 'World of WordPress' === $site_title ? 1 : 0,
		'tagline_seeded'           => 'A living WordPress Playground terrarium.' === $tagline ? 1 : 0,
		'sample_content_removed'   => ( ! $hello_world && ! $sample_page && ! $privacy_policy ) ? 1 : 0,
		'default_comments_removed' => 0 === $comment_count ? 1 : 0,
	),
	'metadata' => $metadata,
);
