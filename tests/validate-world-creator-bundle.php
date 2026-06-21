<?php
/**
 * Validates the World Creator bundle contract used by CI.
 *
 * Run with: php tests/validate-world-creator-bundle.php
 */

$repo_root     = dirname( __DIR__ );
$manifest_path = $repo_root . '/bundles/world-creator/manifest.json';
$flow_path     = $repo_root . '/bundles/world-creator/flows/world-creator-day-cycle-flow.json';
$failures      = array();
$passes        = 0;

function world_bundle_assert_same( $expected, $actual, string $label, array &$failures, int &$passes ): void {
	if ( $expected === $actual ) {
		++$passes;
		printf( "  PASS %s\n", $label );
		return;
	}

	$failures[] = $label;
	printf( "  FAIL %s\n", $label );
	printf( "    expected: %s\n", var_export( $expected, true ) );
	printf( "    actual:   %s\n", var_export( $actual, true ) );
}

function world_bundle_read_json( string $path, array &$failures ): array {
	if ( ! is_file( $path ) ) {
		$failures[] = "Missing JSON file: {$path}";
		return array();
	}

	$decoded = json_decode( (string) file_get_contents( $path ), true );
	if ( ! is_array( $decoded ) ) {
		$failures[] = "Invalid JSON file: {$path}";
		return array();
	}

	return $decoded;
}

echo "world-creator-bundle validation\n";

$manifest = world_bundle_read_json( $manifest_path, $failures );
$flow     = world_bundle_read_json( $flow_path, $failures );
$workflow_path = $repo_root . '/.github/workflows/world-creator.yml';
$workflow      = is_file( $workflow_path ) ? (string) file_get_contents( $workflow_path ) : '';

$expected_policy = array(
	'completion_assertions' => array(
		'egress' => array( 'pr-body' ),
	),
	'daily_memory'          => array(
		'egress'               => array( 'bundle-file' ),
		'bundle_relative_path' => 'memory/agent/daily/{yyyy}/{mm}/{dd}.md',
	),
	'transcript_summary'    => array(
		'egress' => array( 'artifact' ),
	),
);

world_bundle_assert_same( $expected_policy, $manifest['run_artifacts'] ?? null, 'manifest declares run artifact egress policy', $failures, $passes );
world_bundle_assert_same( true, (bool) ( $manifest['agent']['agent_config']['daily_memory']['enabled'] ?? false ), 'agent daily memory is enabled', $failures, $passes );

$enabled_tools = $flow['steps'][0]['enabled_tools'] ?? array();
sort( $enabled_tools, SORT_STRING );
world_bundle_assert_same( true, in_array( 'agent_daily_memory', $enabled_tools, true ), 'flow enables agent_daily_memory tool', $failures, $passes );
$required_tool_names = $flow['steps'][0]['completion_assertions']['required_tool_names'] ?? array();
world_bundle_assert_same( true, in_array( 'agent_daily_memory', $required_tool_names, true ), 'flow requires agent_daily_memory completion', $failures, $passes );
world_bundle_assert_same( array( 'agent_daily_memory' ), $required_tool_names, 'flow preserves broad day-cycle completion contract', $failures, $passes );
world_bundle_assert_same( false, str_contains( (string) ( $flow['steps'][0]['prompt_queue'][0]['prompt'] ?? '' ), 'bundles/world-creator/run-artifacts/' ), 'flow prompt has no deterministic heartbeat fallback', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'This run must open a new repo-contained world-day pull request' ), 'workflow does not override the bundled flow prompt', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, "- cron: '17 * * * *'" ), 'workflow remains paused unless manually dispatched', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'step_budget: 40' ), 'workflow gives the day cycle enough tool budget for PR creation', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'datamachine-agent-ci.yml@main' ), 'workflow uses the Homeboy Data Machine Agent CI wrapper', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'bundle_path: bundles/world-creator' ), 'workflow declares the World Creator bundle path', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'agent_slug: world-creator' ), 'workflow declares the World Creator agent', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'flow_slug: world-creator-day-cycle-flow' ), 'workflow declares the World Creator day-cycle flow', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'runtime_profiles:' ), 'workflow does not inline runtime profile internals', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'runtime_dependencies:' ), 'workflow does not inline runtime dependency manifests', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'required_abilities:' ), 'workflow does not assert low-level Data Machine abilities', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'runtime_execution:' ), 'workflow does not expose low-level runtime execution payloads', $failures, $passes );
world_bundle_assert_same( false, str_contains( $workflow, 'wp_codebox_wordpress_version:' ), 'workflow does not use deprecated WP Codebox WordPress version input', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'engine_data_outputs:' ), 'workflow projects World Creator PR URL through the wrapper output contract', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'tool_recorders:' ), 'workflow declares PR capture through the wrapper recorder contract', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'does not point to a world-day branch' ), 'workflow rejects artifact PR output before merging', $failures, $passes );
world_bundle_assert_same( true, str_contains( $workflow, 'latest_world_day_pr' ), 'workflow can fall back to the opened world-day PR', $failures, $passes );

$bootstrap = is_file( $repo_root . '/tests/playground-ci/workloads/world-creator-bootstrap.php' ) ? (string) file_get_contents( $repo_root . '/tests/playground-ci/workloads/world-creator-bootstrap.php' ) : '';
$preview   = is_file( $repo_root . '/tests/playground-ci/workloads/world-preview-probe.php' ) ? (string) file_get_contents( $repo_root . '/tests/playground-ci/workloads/world-preview-probe.php' ) : '';
$readme    = is_file( $repo_root . '/README.md' ) ? (string) file_get_contents( $repo_root . '/README.md' ) : '';

world_bundle_assert_same( false, str_contains( $bootstrap, 'DataMachineCode\\Tools\\GitHubPullRequestTool' ), 'bootstrap does not instantiate Data Machine Code internals', $failures, $passes );
world_bundle_assert_same( true, str_contains( $bootstrap, 'terrarium_contract' ), 'bootstrap reports the day-cycle terrarium contract', $failures, $passes );
world_bundle_assert_same( true, str_contains( $preview, 'world_creator_preview' ), 'preview probe reports the terrarium preview contract', $failures, $passes );
world_bundle_assert_same( true, str_contains( $readme, 'public preview recipe' ), 'README documents the Playground blueprint as a preview recipe', $failures, $passes );

if ( $failures ) {
	printf( "\nFAILED: %d validation checks failed.\n", count( $failures ) );
	exit( 1 );
}

printf( "\nAll %d validation checks passed.\n", $passes );
