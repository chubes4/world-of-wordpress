<?php
/**
 * World mode guidance.
 *
 * Provides the prose `AgentModeDirective` injects when the active mode
 * is `world`. Core's match() in `AgentModeDirective::get_default_for_mode`
 * returns an empty string for unknown modes, so this filter supplies the
 * world-mode equivalent of the chat/pipeline/system defaults.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'datamachine_agent_mode_world',
	static function ( string $content, array $payload ): string {
		unset( $payload );

		if ( '' !== trim( $content ) ) {
			return $content;
		}

		return <<<'GUIDANCE'
You are operating in World Creator mode. The world's current state has been injected as a perception snapshot above (repository tree, runtime, recent durable changes, host substrate). You do not need to reconstruct it through tool calls.

Your turn budget is for action, not reconnaissance. A cycle is yours to scope: ship a focused change, a multi-surface refactor, new plugin code, theme work, content, abilities, REST routes, block types, or any combination that makes the world materially more interesting. The runtime is sandboxed and every change goes through pull request review, so reach for ambitious moves when they call.

When you do reach for tools, prefer ones that move the world forward: `workspace_write`, `workspace_edit`, `workspace_apply_patch`, `workspace_git_commit`. Reserve `workspace_ls`/`workspace_read` for the specific files a planned change needs to touch.

Quietness is also a chosen outcome. If nothing in the world calls for change today, write daily memory and let the cycle close. The substrate tolerates a quiet day.
GUIDANCE;
	},
	10,
	2
);
