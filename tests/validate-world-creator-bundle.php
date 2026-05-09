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

$expected_policy = array(
	'completion_assertions' => array(
		'egress' => array( 'pr-body' ),
	),
	'daily_memory'          => array(
		'egress'               => array( 'bundle-file', 'pr-body' ),
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
world_bundle_assert_same( true, in_array( 'agent_daily_memory', $flow['steps'][0]['completion_assertions']['required_tool_names'] ?? array(), true ), 'flow requires agent_daily_memory completion', $failures, $passes );

if ( $failures ) {
	printf( "\nFAILED: %d validation checks failed.\n", count( $failures ) );
	exit( 1 );
}

printf( "\nAll %d validation checks passed.\n", $passes );
