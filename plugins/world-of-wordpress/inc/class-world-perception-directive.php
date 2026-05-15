<?php
/**
 * World Perception Directive.
 *
 * Injects a fresh snapshot of the world into every AI request made in
 * `world` mode. The snapshot replaces the manual reconnaissance
 * (workspace_show, workspace_ls, workspace_read, list_github_tree)
 * the agent would otherwise burn turns on at the start of each cycle.
 *
 * The directive runs server-side at request build time inside
 * Playground. It only includes information available locally
 * (filesystem, runtime, Data Machine substrate). Mailbox and pull
 * request state remain accessible through the GitHub tools the agent
 * already has — those calls are cheap and produce a tiny payload.
 *
 * @package WorldOfWordPress
 */

defined( 'ABSPATH' ) || exit;

if ( ! interface_exists( '\DataMachine\Engine\AI\Directives\DirectiveInterface' ) ) {
	return;
}

/**
 * @phpstan-import-type DirectiveOutput from \DataMachine\Engine\AI\Directives\DirectiveInterface
 */
final class World_Perception_Directive implements \DataMachine\Engine\AI\Directives\DirectiveInterface {

	/**
	 * Maximum tree depth from the repo root included in the perception.
	 *
	 * Set deep enough to expose theme template, plugin file, and content
	 * organisation without leaking every fossil filename into ambient
	 * context.
	 */
	private const TREE_MAX_DEPTH = 3;

	/**
	 * Number of recent merged pull requests included in the recent_history
	 * section. Pulled from local git log; no GitHub API call required.
	 */
	private const RECENT_HISTORY_LIMIT = 10;

	/**
	 * Build the world snapshot.
	 *
	 * @param string      $provider_name AI provider name.
	 * @param array       $tools         Available tools (provider-agnostic).
	 * @param string|null $step_id       Pipeline step identifier.
	 * @param array       $payload       Execution payload.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_outputs( string $provider_name, array $tools, ?string $step_id = null, array $payload = array() ): array {
		$repo_root = self::resolve_repo_root();
		if ( '' === $repo_root ) {
			return array();
		}

		$sections = array(
			self::section_header(),
			self::section_tree( $repo_root ),
			self::section_runtime(),
			self::section_recent_history( $repo_root ),
			self::section_substrate(),
		);

		$content = trim( implode( "\n\n", array_filter( $sections, static fn( string $part ): bool => '' !== $part ) ) );
		if ( '' === $content ) {
			return array();
		}

		return array(
			array(
				'type'    => 'system_text',
				'content' => $content,
			),
		);
	}

	/**
	 * Resolve the repository root inside the Playground runtime.
	 *
	 * In CI the entire repo is bind-mounted at `wp-content/plugins/world-of-wordpress/`.
	 * In the public Playground the plugin is installed alone, so the repo
	 * root is not present in the runtime — fall back gracefully.
	 *
	 * @return string Repo root path or empty string when unavailable.
	 */
	private static function resolve_repo_root(): string {
		$plugin_dir = defined( 'WORLD_OF_WORDPRESS_PLUGIN_DIR' )
			? WORLD_OF_WORDPRESS_PLUGIN_DIR
			: plugin_dir_path( dirname( __FILE__ ) );

		// In CI the bind-mounted repo root lives two directories above
		// the active plugin file (wp-content/plugins/world-of-wordpress/
		// itself contains a `plugins/world-of-wordpress/` subtree).
		$candidate = dirname( rtrim( $plugin_dir, '/\\' ), 2 );
		if ( is_dir( $candidate ) && is_file( $candidate . '/WORLD.md' ) ) {
			return $candidate;
		}

		return '';
	}

	private static function section_header(): string {
		$lines   = array();
		$lines[] = '# World Perception';
		$lines[] = '';
		$lines[] = 'Live snapshot of the World of WordPress at the start of this cycle. Generated server-side from the repository, the WordPress runtime, and the Data Machine substrate. Use it as your starting awareness; tools remain available for any deeper inspection a specific move calls for.';

		return implode( "\n", $lines );
	}

	/**
	 * @param string $repo_root Repository root path.
	 */
	private static function section_tree( string $repo_root ): string {
		$entries = self::scan_tree( $repo_root, $repo_root, 0 );
		if ( empty( $entries ) ) {
			return '';
		}

		sort( $entries, SORT_STRING );

		$lines   = array();
		$lines[] = '## Repository Tree';
		$lines[] = '';
		$lines[] = sprintf( 'Active surfaces (depth %d). The `fossils/` directory exists in the tree as inert archival material; expand it with workspace tools only when a deliberate dig is warranted.', self::TREE_MAX_DEPTH );
		$lines[] = '';
		$lines[] = '```';
		foreach ( $entries as $entry ) {
			$lines[] = $entry;
		}
		$lines[] = '```';

		return implode( "\n", $lines );
	}

	/**
	 * Recursively scan the repo tree to a bounded depth.
	 *
	 * @param string $base    Repo root path used for relative-path computation.
	 * @param string $current Current directory being scanned.
	 * @param int    $depth   Current depth.
	 *
	 * @return array<int, string>
	 */
	private static function scan_tree( string $base, string $current, int $depth ): array {
		if ( $depth > self::TREE_MAX_DEPTH ) {
			return array();
		}

		$ignored_top_level = array(
			'.git',
			'.github',
			'.claude',
			'.opencode',
			'node_modules',
			'vendor',
		);

		$entries = scandir( $current );
		if ( false === $entries ) {
			return array();
		}

		$results = array();
		foreach ( $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}
			if ( 0 === $depth && in_array( $entry, $ignored_top_level, true ) ) {
				continue;
			}

			$path     = $current . '/' . $entry;
			$relative = ltrim( substr( $path, strlen( $base ) ), '/' );

			if ( is_dir( $path ) ) {
				$results[] = $relative . '/';

				// `fossils/` collapses to a single directory line; the
				// agent can dig with workspace tools when needed.
				if ( 'fossils' === $entry && 0 === $depth ) {
					continue;
				}

				$results = array_merge( $results, self::scan_tree( $base, $path, $depth + 1 ) );
				continue;
			}

			$results[] = $relative;
		}

		return $results;
	}

	private static function section_runtime(): string {
		$active_theme    = wp_get_theme();
		$active_plugins  = (array) get_option( 'active_plugins', array() );
		$wp_version      = get_bloginfo( 'version' );
		$post_count      = (int) wp_count_posts( 'post' )->publish;
		$page_count      = (int) wp_count_posts( 'page' )->publish;
		$mdi_mode        = defined( 'MARKDOWN_DB_MODE' ) ? MARKDOWN_DB_MODE : 'unset';
		$mdi_content_dir = defined( 'MARKDOWN_DB_CONTENT_DIR' ) ? MARKDOWN_DB_CONTENT_DIR : 'unset';

		$plugin_lines = array();
		foreach ( $active_plugins as $plugin_file ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( function_exists( 'get_plugin_data' ) && file_exists( $plugin_path ) ) {
				$data           = get_plugin_data( $plugin_path, false, false );
				$plugin_lines[] = sprintf( '- %s (%s)', $data['Name'] ?? $plugin_file, dirname( $plugin_file ) );
				continue;
			}
			$plugin_lines[] = '- ' . $plugin_file;
		}

		$lines   = array();
		$lines[] = '## Runtime';
		$lines[] = '';
		$lines[] = sprintf( '- WordPress: %s', $wp_version );
		$lines[] = sprintf( '- PHP: %s', PHP_VERSION );
		$lines[] = sprintf( '- Active theme: %s (%s)', $active_theme->get( 'Name' ), $active_theme->get_stylesheet() );
		$lines[] = sprintf( '- Markdown Database Integration mode: %s', $mdi_mode );
		$lines[] = sprintf( '- Content directory: %s', $mdi_content_dir );
		$lines[] = sprintf( '- Published posts: %d', $post_count );
		$lines[] = sprintf( '- Published pages: %d', $page_count );
		if ( ! empty( $plugin_lines ) ) {
			$lines[] = '';
			$lines[] = 'Active plugins:';
			$lines = array_merge( $lines, $plugin_lines );
		}

		return implode( "\n", $lines );
	}

	/**
	 * @param string $repo_root Repository root path.
	 */
	private static function section_recent_history( string $repo_root ): string {
		$git = self::find_git();
		if ( '' === $git ) {
			return '';
		}

		$cwd = getcwd();
		if ( false === chdir( $repo_root ) ) {
			return '';
		}

		$cmd = sprintf(
			'%s log -n %d --merges --pretty=format:%%s 2>/dev/null',
			escapeshellcmd( $git ),
			(int) self::RECENT_HISTORY_LIMIT
		);
		$output = shell_exec( $cmd );
		if ( false !== $cwd ) {
			chdir( $cwd );
		}

		if ( ! is_string( $output ) || '' === trim( $output ) ) {
			return '';
		}

		$titles = array_values( array_filter( array_map( 'trim', explode( "\n", $output ) ) ) );
		if ( empty( $titles ) ) {
			return '';
		}

		$lines   = array();
		$lines[] = '## Recent Durable Changes';
		$lines[] = '';
		$lines[] = 'Most recent merged pull requests on `main` (newest first):';
		$lines[] = '';
		foreach ( $titles as $title ) {
			$lines[] = '- ' . $title;
		}

		return implode( "\n", $lines );
	}

	/**
	 * Locate a git binary the request can shell out to.
	 *
	 * Playground ships a minimal environment; not every container has
	 * git on PATH. Returns the resolved path or empty string.
	 */
	private static function find_git(): string {
		$candidates = array( '/usr/bin/git', '/usr/local/bin/git', '/opt/homebrew/bin/git' );
		foreach ( $candidates as $candidate ) {
			if ( is_file( $candidate ) && is_executable( $candidate ) ) {
				return $candidate;
			}
		}

		$which = shell_exec( 'command -v git 2>/dev/null' );
		if ( is_string( $which ) ) {
			$which = trim( $which );
			if ( '' !== $which && is_executable( $which ) ) {
				return $which;
			}
		}

		return '';
	}

	private static function section_substrate(): string {
		$abilities = array();
		if ( function_exists( 'wp_get_abilities' ) ) {
			$registry = wp_get_abilities();
			if ( is_array( $registry ) ) {
				$abilities = array_keys( $registry );
			}
		}

		$ability_count_total = count( $abilities );
		$ability_namespaces  = array();
		foreach ( $abilities as $ability_name ) {
			if ( ! is_string( $ability_name ) ) {
				continue;
			}
			$namespace = strtok( $ability_name, '/' );
			if ( ! is_string( $namespace ) || '' === $namespace ) {
				continue;
			}
			$ability_namespaces[ $namespace ] = ( $ability_namespaces[ $namespace ] ?? 0 ) + 1;
		}
		ksort( $ability_namespaces );

		$rest_namespaces = array();
		if ( function_exists( 'rest_get_server' ) ) {
			$server     = rest_get_server();
			$namespaces = $server ? $server->get_namespaces() : array();
			$rest_namespaces = is_array( $namespaces ) ? $namespaces : array();
			sort( $rest_namespaces, SORT_STRING );
		}

		$lines   = array();
		$lines[] = '## Substrate';
		$lines[] = '';
		$lines[] = 'You are an autonomous agent hosted on Data Machine, running inside a WordPress Playground instance. The host substrate exposes:';
		$lines[] = '';
		$lines[] = sprintf( '- Abilities API: %d registered abilities (across %d namespaces)', $ability_count_total, count( $ability_namespaces ) );
		if ( ! empty( $ability_namespaces ) ) {
			foreach ( $ability_namespaces as $ns => $count ) {
				$lines[] = sprintf( '  - `%s/*` — %d abilities', $ns, $count );
			}
		}
		$lines[] = sprintf( '- REST API: %d registered namespaces', count( $rest_namespaces ) );
		if ( ! empty( $rest_namespaces ) ) {
			$preview = array_slice( $rest_namespaces, 0, 12 );
			foreach ( $preview as $ns ) {
				$lines[] = sprintf( '  - `%s`', $ns );
			}
			if ( count( $rest_namespaces ) > count( $preview ) ) {
				$lines[] = sprintf( '  - ...and %d more', count( $rest_namespaces ) - count( $preview ) );
			}
		}
		$lines[] = '';
		$lines[] = 'You can extend any of this. Register new abilities, register REST routes, register block types, register CPTs, schedule cron, write migrations. The world plugin (`plugins/world-of-wordpress/`) is yours to grow.';

		return implode( "\n", $lines );
	}
}

// Self-register in the directive system. Priority 25 places the
// perception just after AgentModeDirective (22) so the mode framing
// reads first, but before AgentDailyMemoryDirective (35) so the
// agent has structural context before it sees yesterday's notes.
add_filter(
	'datamachine_directives',
	static function ( array $directives ): array {
		$directives[] = array(
			'class'    => 'World_Perception_Directive',
			'priority' => 25,
			'modes'    => array( 'world' ),
		);
		return $directives;
	}
);
