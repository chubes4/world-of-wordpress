<?php
/**
 * World-specific bootstrap for the generic Data Machine agent runner.
 */

use DataMachineCode\Tools\GitHubPullRequestTool;

if ( ! class_exists( 'World_Creator_Pull_Request_Recorder' ) ) {
	class World_Creator_Pull_Request_Recorder {
		public function handle_tool_call( array $parameters, array $tool_def = array() ): array {
			$tool   = new GitHubPullRequestTool();
			$result = $tool->handle_tool_call( $parameters, $tool_def );

			$job_id = (int) ( $parameters['job_id'] ?? 0 );
			if ( $job_id <= 0 || ! function_exists( 'datamachine_merge_engine_data' ) ) {
				return $result;
			}

			$data    = is_array( $result['data'] ?? null ) ? $result['data'] : $result;
			$success = is_array( $result ) && ! empty( $result['success'] );
			$pr_url  = (string) ( $data['html_url'] ?? $data['url'] ?? '' );

			datamachine_merge_engine_data(
				$job_id,
				array(
					'world_creator' => array(
						'success'     => $success,
						'pr_url'      => $pr_url,
						'head'        => (string) ( $data['head'] ?? ( $parameters['head'] ?? '' ) ),
						'pull_number' => (int) ( $data['pull_number'] ?? $data['number'] ?? 0 ),
						'title'       => (string) ( $data['title'] ?? ( $parameters['title'] ?? '' ) ),
						'error'       => $success ? null : (string) ( $result['error'] ?? 'create_github_pull_request failed' ),
					),
				)
			);

			return $result;
		}
	}
}

if ( function_exists( 'wp_set_current_user' ) ) {
	wp_set_current_user( 1 );
}
if ( function_exists( 'world_of_wordpress_ci_seed_shared_memory' ) ) {
	world_of_wordpress_ci_seed_shared_memory();
}
if ( function_exists( 'world_of_wordpress_seed_world' ) ) {
	world_of_wordpress_seed_world();
}

$stylesheet = function_exists( 'get_stylesheet' ) ? (string) get_stylesheet() : '';
if ( 'world-of-wordpress' !== $stylesheet ) {
	throw new RuntimeException( 'World of WordPress theme was not active before the day cycle; active stylesheet: ' . $stylesheet );
}

add_filter(
	'datamachine_resolved_tools',
	static function ( array $tools ): array {
		if ( isset( $tools['create_github_pull_request'] ) ) {
			$tools['create_github_pull_request']['class']  = 'World_Creator_Pull_Request_Recorder';
			$tools['create_github_pull_request']['method'] = 'handle_tool_call';
		}
		return $tools;
	},
	100,
	1
);

update_option( 'datamachine_persist_pipeline_transcripts', true, false );

return array(
	'metrics'  => array(
		'world_bootstrap_succeeded' => 1,
	),
	'metadata' => array(
		'world_theme_stylesheet'  => $stylesheet,
		'markdown_db_dropin'      => defined( 'MARKDOWN_DB_DROPIN' ),
		'markdown_db_mode'        => defined( 'MARKDOWN_DB_MODE' ) ? MARKDOWN_DB_MODE : '',
		'markdown_db_content_dir' => defined( 'MARKDOWN_DB_CONTENT_DIR' ) ? MARKDOWN_DB_CONTENT_DIR : '',
	),
);
