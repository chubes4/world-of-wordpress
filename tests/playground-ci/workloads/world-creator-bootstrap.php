<?php
/**
 * World-specific bootstrap for the day-cycle terrarium.
 */

if ( function_exists( 'wp_set_current_user' ) ) {
	wp_set_current_user( 1 );
}
if ( function_exists( 'world_of_wordpress_seed_world' ) ) {
	world_of_wordpress_seed_world();
}

$stylesheet = function_exists( 'get_stylesheet' ) ? (string) get_stylesheet() : '';
if ( 'world-of-wordpress' !== $stylesheet ) {
	throw new RuntimeException( 'World of WordPress theme was not active before the day cycle; active stylesheet: ' . $stylesheet );
}

update_option( 'datamachine_persist_pipeline_transcripts', true, false );

return array(
	'metrics'  => array(
		'world_bootstrap_succeeded' => 1,
	),
	'metadata' => array(
		'world_theme_stylesheet' => $stylesheet,
		'terrarium_contract'     => 'world_creator_day_cycle',
		'world_seeded'           => function_exists( 'world_of_wordpress_seed_world' ),
	),
);
