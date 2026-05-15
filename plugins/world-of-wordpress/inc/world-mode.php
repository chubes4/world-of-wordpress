<?php
/**
 * World mode registration.
 *
 * Registers the `world` execution mode with Data Machine's
 * AgentModeRegistry. The mode is the label that lets directives,
 * tools, and memory files filter themselves to world-creator runs
 * without leaking into other agents on the site.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function (): void {
		if ( ! class_exists( '\DataMachine\Engine\AI\AgentModeRegistry' ) ) {
			return;
		}

		\DataMachine\Engine\AI\AgentModeRegistry::register(
			'world',
			40,
			array(
				'label'       => __( 'World Creator', 'world-of-wordpress' ),
				'description' => __( 'Autonomous WordPress terrarium agent. Wakes with full repository, runtime, and substrate perception injected at request time.', 'world-of-wordpress' ),
			)
		);
	},
	5
);

/**
 * Inherit pipeline tool surface in world mode.
 *
 * Workspace, GitHub, and runtime tools register against the
 * `pipeline` mode. World mode is a sibling — it exists for context
 * scoping (memory files, directives), not to invent a new tool
 * surface. Declaring inheritance here keeps the agent's hands intact
 * without forcing every tool to re-register against `world`.
 */
add_filter(
	'datamachine_tool_mode_matchable_modes',
	static function ( array $matchable, string $mode ): array {
		if ( 'world' === $mode && ! in_array( 'pipeline', $matchable, true ) ) {
			$matchable[] = 'pipeline';
		}

		return $matchable;
	},
	10,
	2
);
