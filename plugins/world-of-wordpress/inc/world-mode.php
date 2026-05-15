<?php
/**
 * World mode registration.
 *
 * Registers the `world` execution mode with Data Machine's
	 * AgentModeRegistry. World creator runs declare both `pipeline` and
	 * `world` modes so pipeline tools stay available while world-specific
	 * directives and memory files remain scoped to this agent.
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
