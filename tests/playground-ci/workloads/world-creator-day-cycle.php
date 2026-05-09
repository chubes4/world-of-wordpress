<?php
/**
 * Imports and runs the World Creator agent bundle for one manual day cycle.
 */

use DataMachine\Core\Database\Agents\Agents;
use DataMachine\Core\Database\Chat\ConversationStoreFactory;
use DataMachine\Core\Database\Flows\Flows;
use DataMachine\Core\Database\Jobs\Jobs;
use DataMachine\Core\Database\Pipelines\Pipelines;
use DataMachine\Core\PluginSettings;
use DataMachine\Engine\AI\WpAiClientProviderAdmin;
use DataMachineCode\Tools\GitHubPullRequestTool;

if ( ! function_exists( 'world_creator_result' ) ) {
	function world_creator_result( array $metrics, array $metadata, ?string $error = null ): array {
		if ( null !== $error ) {
			$metadata['error'] = $error;
		}

		return array(
			'metrics'  => $metrics,
			'metadata' => $metadata,
		);
	}
}

if ( ! function_exists( 'world_creator_inputs' ) ) {
	function world_creator_inputs(): array {
		return array(
			'github_token'   => trim( (string) ( getenv( 'GITHUB_TOKEN' ) ?: getenv( 'GH_TOKEN' ) ?: '' ) ),
			'openai_api_key' => trim( (string) getenv( 'OPENAI_API_KEY' ) ),
			'openai_model'   => trim( (string) ( getenv( 'WORLD_CREATOR_OPENAI_MODEL' ) ?: 'gpt-5.5' ) ),
			'target_repo'    => trim( (string) ( getenv( 'WORLD_CREATOR_TARGET_REPO' ) ?: 'chubes4/world-of-wordpress' ) ),
			'prompt'         => trim( (string) getenv( 'WORLD_CREATOR_PROMPT' ) ),
			'transcript_dir' => trim( (string) getenv( 'WORLD_CREATOR_TRANSCRIPT_DIR' ) ),
		);
	}
}

if ( ! function_exists( 'world_creator_configure_settings' ) ) {
	function world_creator_configure_settings( array $inputs ): array {
		$settings = function_exists( 'get_option' ) ? (array) get_option( 'datamachine_settings', array() ) : array();
		$settings['github_credential_profiles'] = array(
			array(
				'id'            => 'world-creator-ci',
				'label'         => 'World Creator CI token',
				'mode'          => 'pat',
				'pat'           => $inputs['github_token'],
				'default_repo'  => $inputs['target_repo'],
				'allowed_repos' => array( $inputs['target_repo'] ),
			),
		);
		$settings['github_default_profile_id'] = 'world-creator-ci';
		$settings['github_default_repo']       = $inputs['target_repo'];
		$settings['default_provider']          = 'openai';
		$settings['default_model']             = $inputs['openai_model'];
		$settings['daily_memory_enabled']      = true;
		$settings['mode_models']               = array(
			'pipeline' => array( 'provider' => 'openai', 'model' => $inputs['openai_model'] ),
			'chat'     => array( 'provider' => 'openai', 'model' => $inputs['openai_model'] ),
			'system'   => array( 'provider' => 'openai', 'model' => $inputs['openai_model'] ),
		);
		$settings['max_turns']                 = 16;
		$settings['wp_ai_client_connect_timeout'] = 30;

		update_option( 'datamachine_settings', $settings, false );
		update_option( 'connectors_ai_openai_api_key', $inputs['openai_api_key'], false );
		update_option( 'datamachine_persist_pipeline_transcripts', true, false );
		PluginSettings::clearCache();

		return $settings;
	}
}

if ( ! function_exists( 'world_creator_bootstrap_abilities' ) ) {
	function world_creator_bootstrap_abilities(): ?array {
		if ( function_exists( 'WordPress\\OpenAiAiProvider\\register_provider' ) ) {
			WordPress\OpenAiAiProvider\register_provider();
		}
		if ( ! did_action( 'wp_abilities_api_categories_init' ) ) {
			do_action( 'wp_abilities_api_categories_init' );
		}
		if ( ! did_action( 'wp_abilities_api_init' ) ) {
			do_action( 'wp_abilities_api_init' );
		}

		if ( ! function_exists( 'wp_get_ability' ) ) {
			return world_creator_result( array( 'has_abilities_api' => 0 ), array(), 'Abilities API not loaded' );
		}

		foreach ( array( 'datamachine/import-agent', 'datamachine/run-flow', 'datamachine/drain-job', 'datamachine/create-or-update-github-file', 'datamachine/daily-memory-write' ) as $ability_name ) {
			if ( ! wp_get_ability( $ability_name ) ) {
				return world_creator_result( array( 'required_abilities_resolved' => 0 ), array(), $ability_name . ' not registered' );
			}
		}

		return null;
	}
}

if ( ! function_exists( 'world_creator_export_transcript' ) ) {
	function world_creator_export_transcript( int $job_id, array $engine_data, string $transcript_dir ): array {
		$session_id = (string) ( $engine_data['transcript_session_id'] ?? '' );
		if ( '' === $session_id || '' === $transcript_dir || ! class_exists( ConversationStoreFactory::class ) ) {
			return array();
		}

		$store   = ConversationStoreFactory::get();
		$session = $store->get_session( $session_id );
		if ( ! $session ) {
			return array( 'session_id' => $session_id, 'error' => 'Transcript session missing' );
		}

		if ( ! is_dir( $transcript_dir ) && ! wp_mkdir_p( $transcript_dir ) ) {
			return array( 'session_id' => $session_id, 'error' => 'Transcript directory could not be created' );
		}

		$base_path    = rtrim( $transcript_dir, '/' ) . '/job-' . $job_id . '-transcript';
		$json_path    = $base_path . '.json';
		$summary_path = $base_path . '-summary.json';
		$messages     = is_array( $session['messages'] ?? null ) ? $session['messages'] : array();
		$metadata     = is_array( $session['metadata'] ?? null ) ? $session['metadata'] : array();

		file_put_contents(
			$json_path,
			wp_json_encode(
				array(
					'job_id'     => $job_id,
					'session_id' => $session_id,
					'provider'   => $session['provider'] ?? null,
					'model'      => $session['model'] ?? null,
					'metadata'   => $metadata,
					'messages'   => $messages,
				),
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
		file_put_contents(
			$summary_path,
			wp_json_encode(
				array(
					'job_id'        => $job_id,
					'session_id'    => $session_id,
					'message_count' => count( $messages ),
					'metadata'      => $metadata,
				),
				JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);

		return array(
			'session_id'    => $session_id,
			'json'          => $json_path,
			'summary'       => $summary_path,
			'message_count' => count( $messages ),
		);
	}
}

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

$inputs = world_creator_inputs();
$metadata = array(
	'target_repo'                 => $inputs['target_repo'],
	'openai_model'                => $inputs['openai_model'],
	'github_token_present'        => '' !== $inputs['github_token'],
	'openai_key_present'          => '' !== $inputs['openai_api_key'],
	'openai_provider_registered'  => class_exists( WpAiClientProviderAdmin::class ) && WpAiClientProviderAdmin::isProviderRegistered( 'openai' ),
	'world_theme_stylesheet'      => function_exists( 'get_stylesheet' ) ? (string) get_stylesheet() : '',
	'markdown_db_dropin'          => defined( 'MARKDOWN_DB_DROPIN' ),
	'markdown_db_mode'            => defined( 'MARKDOWN_DB_MODE' ) ? MARKDOWN_DB_MODE : '',
	'markdown_db_content_dir'     => defined( 'MARKDOWN_DB_CONTENT_DIR' ) ? MARKDOWN_DB_CONTENT_DIR : '',
);

if ( '' === $inputs['github_token'] || '' === $inputs['openai_api_key'] ) {
	return world_creator_result( array( 'credentials_present' => 0 ), $metadata, 'GITHUB_TOKEN and OPENAI_API_KEY are required' );
}
if ( '' === $inputs['prompt'] ) {
	return world_creator_result( array( 'prompt_present' => 0 ), $metadata, 'WORLD_CREATOR_PROMPT is required' );
}
if ( 'world-of-wordpress' !== $metadata['world_theme_stylesheet'] ) {
	return world_creator_result( array( 'world_theme_active' => 0 ), $metadata, 'World of WordPress theme was not active before the day cycle' );
}

$bootstrap_error = world_creator_bootstrap_abilities();
if ( null !== $bootstrap_error ) {
	return $bootstrap_error;
}

$settings = world_creator_configure_settings( $inputs );

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

$bundle_path = '/wordpress/wp-content/plugins/world-of-wordpress-ci-driver/bundles/world-creator';
if ( ! is_dir( $bundle_path ) || ! is_file( $bundle_path . '/manifest.json' ) ) {
	return world_creator_result( array( 'bundle_exists' => 0 ), $metadata, 'World Creator bundle missing' );
}

$start_ns = hrtime( true );
$import_result = wp_get_ability( 'datamachine/import-agent' )->execute(
	array(
		'source'      => $bundle_path,
		'on_conflict' => 'skip',
	)
);
$import_elapsed_ms = ( hrtime( true ) - $start_ns ) / 1000000;
$metadata['import_result'] = $import_result;
if ( ! is_array( $import_result ) || empty( $import_result['success'] ) ) {
	return world_creator_result( array( 'import_succeeded' => 0, 'import_elapsed_ms' => $import_elapsed_ms ), $metadata, 'datamachine/import-agent did not succeed' );
}

$agents    = new Agents();
$pipelines = new Pipelines();
$flows     = new Flows();
$jobs      = new Jobs();

$agent = $agents->get_by_slug( 'world-creator' );
if ( ! $agent ) {
	return world_creator_result( array( 'agent_resolved' => 0 ), $metadata, 'Imported World Creator agent was not found' );
}

$agent_id = (int) $agent['agent_id'];
$agent_config = is_array( $agent['agent_config'] ?? null ) ? $agent['agent_config'] : array();
$agent_config['default_provider'] = 'openai';
$agent_config['default_model']    = $inputs['openai_model'];
$agent_config['mode_models']      = $settings['mode_models'];
$agents->update_agent( $agent_id, array( 'agent_config' => $agent_config ) );
PluginSettings::clearCache();

$pipeline = $pipelines->get_by_portable_slug( $agent_id, 'world-creator-pipeline' );
if ( ! $pipeline ) {
	return world_creator_result( array( 'pipeline_resolved' => 0 ), $metadata + array( 'agent_id' => $agent_id ), 'Imported World Creator pipeline was not found' );
}

$flow = $flows->get_by_portable_slug( (int) $pipeline['pipeline_id'], 'world-creator-manual-flow' );
if ( ! $flow ) {
	return world_creator_result( array( 'flow_resolved' => 0 ), $metadata + array( 'agent_id' => $agent_id ), 'Imported World Creator manual flow was not found' );
}

$flow_id     = (int) $flow['flow_id'];
$flow_config = is_array( $flow['flow_config'] ?? null ) ? $flow['flow_config'] : array();
foreach ( $flow_config as &$step_config ) {
	if ( 'ai' === (string) ( $step_config['step_type'] ?? '' ) ) {
		$step_config['prompt_queue'] = array(
			array(
				'prompt'   => $inputs['prompt'],
				'added_at' => gmdate( 'c' ),
			),
		);
		$step_config['queue_mode'] = 'static';
	}
}
unset( $step_config );
$flows->update_flow( $flow_id, array( 'flow_config' => $flow_config, 'agent_id' => $agent_id ) );

$start_ns = hrtime( true );
$run_result = wp_get_ability( 'datamachine/run-flow' )->execute( array( 'flow_id' => $flow_id ) );
$run_elapsed_ms = ( hrtime( true ) - $start_ns ) / 1000000;
$metadata['run_result'] = $run_result;
$job_id = is_array( $run_result ) ? (int) ( $run_result['job_id'] ?? 0 ) : 0;
if ( ! is_array( $run_result ) || empty( $run_result['success'] ) || $job_id <= 0 ) {
	return world_creator_result( array( 'run_flow_succeeded' => 0, 'run_elapsed_ms' => $run_elapsed_ms ), $metadata, 'datamachine/run-flow failed or returned no job_id' );
}

$start_ns = hrtime( true );
$drain_result = wp_get_ability( 'datamachine/drain-job' )->execute(
	array(
		'job_id'         => $job_id,
		'step_budget'    => 20,
		'time_budget_ms' => 900000,
	)
);
$drain_elapsed_ms = ( hrtime( true ) - $start_ns ) / 1000000;
$metadata['drain_result'] = $drain_result;

$job = $jobs->get_job( $job_id );
$job_status = is_array( $job ) ? (string) ( $job['status'] ?? '' ) : '';
$engine_data = function_exists( 'datamachine_get_engine_data' ) ? datamachine_get_engine_data( $job_id ) : array();
$transcript_artifacts = world_creator_export_transcript( $job_id, $engine_data, $inputs['transcript_dir'] );
$token_usage = is_array( $engine_data['token_usage'] ?? null ) ? $engine_data['token_usage'] : array();
$world_creator_result = is_array( $engine_data['world_creator'] ?? null ) ? $engine_data['world_creator'] : array();
$pr_url = (string) ( $world_creator_result['pr_url'] ?? '' );

$metadata += array(
	'agent_id'             => $agent_id,
	'pipeline_id'          => (int) $pipeline['pipeline_id'],
	'flow_id'              => $flow_id,
	'job_id'               => $job_id,
	'job_status'           => $job_status,
	'transcript_session_id' => (string) ( $engine_data['transcript_session_id'] ?? '' ),
	'transcript_artifacts'  => $transcript_artifacts,
	'token_usage'           => $token_usage,
	'world_creator_result'  => $world_creator_result,
	'world_creator_pr_url'  => $pr_url,
	'error_message'         => (string) ( $engine_data['error_message'] ?? '' ),
);

return world_creator_result(
	array(
		'credentials_present'       => 1,
		'prompt_present'            => 1,
		'import_succeeded'          => 1,
		'import_elapsed_ms'         => $import_elapsed_ms,
		'agent_resolved'            => 1,
		'pipeline_resolved'         => 1,
		'flow_resolved'             => 1,
		'run_flow_succeeded'        => 1,
		'run_elapsed_ms'            => $run_elapsed_ms,
		'drain_succeeded'           => is_array( $drain_result ) && ! empty( $drain_result['success'] ) ? 1 : 0,
		'drain_elapsed_ms'          => $drain_elapsed_ms,
		'job_completed'             => 'completed' === $job_status ? 1 : 0,
		'pull_request_opened'       => '' !== $pr_url ? 1 : 0,
		'transcript_exported'       => ! empty( $transcript_artifacts['json'] ) ? 1 : 0,
		'openai_total_tokens'       => (int) ( $token_usage['total_tokens'] ?? 0 ),
	),
	$metadata
);
