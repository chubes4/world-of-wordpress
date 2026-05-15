<?php
/**
 * Plugin Name: World of WordPress
 * Description: A self-contained WordPress Playground terrarium where an agent evolves software and content.
 * Version: 0.1.0
 * Author: Chris Huber
 * License: GPL v2 or later
 * Text Domain: world-of-wordpress
 */

defined( 'ABSPATH' ) || exit;

add_action( 'datamachine_memory_files', 'world_of_wordpress_register_memory_files' );
add_action( 'wp_footer', 'world_of_wordpress_render_challenge_beacon' );

/**
 * Register the world model as shared memory for every agent in this site.
 */
function world_of_wordpress_register_memory_files(): void {
	if ( ! class_exists( '\DataMachine\Engine\AI\MemoryFileRegistry' ) ) {
		return;
	}

	\DataMachine\Engine\AI\MemoryFileRegistry::register(
		'WORLD.md',
		18,
		array(
			'layer'       => \DataMachine\Engine\AI\MemoryFileRegistry::LAYER_SHARED,
			'protected'   => true,
			'modes'       => array( \DataMachine\Engine\AI\MemoryFileRegistry::MODE_ALL ),
			'label'       => 'World Context',
			'description' => 'Shared World of WordPress context for every agent on the site.',
		)
	);
}

/**
 * Copy the repository world model into Data Machine shared memory.
 */
function world_of_wordpress_seed_shared_memory(): void {
	if ( ! class_exists( '\DataMachine\Core\FilesRepository\DirectoryManager' ) ) {
		return;
	}

	$source = __DIR__ . '/WORLD.md';
	if ( ! is_readable( $source ) ) {
		return;
	}

	$directory_manager = new \DataMachine\Core\FilesRepository\DirectoryManager();
	$shared_dir        = $directory_manager->get_shared_directory();
	if ( ! $directory_manager->ensure_directory_exists( $shared_dir ) ) {
		return;
	}

	copy( $source, $shared_dir . '/WORLD.md' );
}

/**
 * Copy a directory recursively.
 */
function world_of_wordpress_copy_directory( string $source, string $destination ): void {
	if ( ! is_dir( $source ) ) {
		return;
	}

	if ( ! is_dir( $destination ) ) {
		wp_mkdir_p( $destination );
	}

	$items = scandir( $source );
	if ( false === $items ) {
		return;
	}

	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item ) {
			continue;
		}

		$source_path      = $source . '/' . $item;
		$destination_path = $destination . '/' . $item;

		if ( is_dir( $source_path ) ) {
			world_of_wordpress_copy_directory( $source_path, $destination_path );
			continue;
		}

		copy( $source_path, $destination_path );
	}
}

/**
 * Seed and activate the repository-owned starter block theme.
 */
function world_of_wordpress_seed_theme(): void {
	$theme_slug  = 'world-of-wordpress';
	$source      = __DIR__ . '/themes/' . $theme_slug;
	$destination = WP_CONTENT_DIR . '/themes/' . $theme_slug;
	$stylesheet  = $destination . '/style.css';

	world_of_wordpress_copy_directory( $source, $destination );

	if ( file_exists( $stylesheet ) ) {
		wp_clean_themes_cache( false );
		switch_theme( $theme_slug );
	}
}

/**
 * Seed the visible World of WordPress state from repository content.
 */
function world_of_wordpress_seed_world(): void {
	world_of_wordpress_seed_shared_memory();
	world_of_wordpress_seed_theme();

	if ( ! function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		$mdi_plugin = WP_PLUGIN_DIR . '/markdown-database-integration/markdown-database-integration.php';
		if ( file_exists( $mdi_plugin ) ) {
			require_once $mdi_plugin;
		}
	}

	if ( function_exists( 'markdown_database_integration_import_seed_posts_after_install' ) ) {
		markdown_database_integration_import_seed_posts_after_install();
	}

	update_option( 'blogname', 'World of WordPress' );
	update_option( 'blogdescription', 'A living WordPress Playground terrarium.' );

	foreach ( array( 'hello-world', 'sample-page', 'privacy-policy' ) as $slug ) {
		foreach ( array( 'post', 'page' ) as $post_type ) {
			$sample = get_page_by_path( $slug, OBJECT, $post_type );
			if ( $sample ) {
				wp_delete_post( (int) $sample->ID, true );
			}
		}
	}

	$comments = get_comments( array( 'status' => 'all' ) );
	if ( is_array( $comments ) ) {
		foreach ( $comments as $comment ) {
			if ( $comment instanceof WP_Comment ) {
				wp_delete_comment( (int) $comment->comment_ID, true );
			}
		}
	}

	update_option( 'show_on_front', 'posts' );
	update_option( 'page_on_front', 0 );
}

/**
 * Render a tiny no-storage visitor challenge in the page footer.
 */
function world_of_wordpress_render_challenge_beacon(): void {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return;
	}

	$challenge = array(
		'prompt' => 'Find the first three substrates named in WORLD.md, then type the word made by their initials in environment order.',
		'hint'   => 'Repository. Issues. Pull requests. Actions. Playground. Markdown. Data Machine. Code. Validation.',
		'answer' => 'rip',
	);
	?>
	<style>
		.world-challenge-beacon {
			position: fixed;
			right: 1rem;
			bottom: 1rem;
			z-index: 9999;
			width: min(24rem, calc(100vw - 2rem));
			padding: 1rem;
			border: 1px solid rgba(15, 23, 42, 0.18);
			border-radius: 1rem;
			background: rgba(255, 255, 255, 0.95);
			box-shadow: 0 1rem 2.5rem rgba(15, 23, 42, 0.18);
			color: #0f172a;
			font: 14px/1.4 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
		}

		.world-challenge-beacon[hidden] {
			display: none;
		}

		.world-challenge-beacon strong {
			display: block;
			font-size: 0.85rem;
			letter-spacing: 0.08em;
			margin-bottom: 0.35rem;
			text-transform: uppercase;
		}

		.world-challenge-beacon p {
			margin: 0 0 0.75rem;
		}

		.world-challenge-beacon details {
			margin: 0 0 0.75rem;
		}

		.world-challenge-beacon input,
		.world-challenge-beacon button {
			border-radius: 999px;
			font: inherit;
			padding: 0.55rem 0.8rem;
		}

		.world-challenge-beacon input {
			box-sizing: border-box;
			width: 7rem;
			border: 1px solid rgba(15, 23, 42, 0.24);
			margin-right: 0.35rem;
		}

		.world-challenge-beacon button {
			border: 1px solid #0f172a;
			background: #0f172a;
			color: #fff;
			cursor: pointer;
		}

		.world-challenge-beacon .world-challenge-close {
			position: absolute;
			top: 0.5rem;
			right: 0.5rem;
			border: 0;
			background: transparent;
			color: inherit;
			padding: 0.25rem 0.45rem;
		}

		.world-challenge-status {
			margin-top: 0.75rem;
			min-height: 1.2em;
			font-weight: 700;
		}

		@media (prefers-color-scheme: dark) {
			.world-challenge-beacon {
				border-color: rgba(148, 163, 184, 0.28);
				background: rgba(15, 23, 42, 0.95);
				box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.45);
				color: #e2e8f0;
			}

			.world-challenge-beacon input {
				border-color: rgba(148, 163, 184, 0.36);
				background: #020617;
				color: #f8fafc;
			}

			.world-challenge-beacon button:not(.world-challenge-close) {
				border-color: #38bdf8;
				background: #38bdf8;
				color: #082f49;
			}
		}
	</style>
	<aside class="world-challenge-beacon" aria-label="World challenge">
		<button class="world-challenge-close" type="button" aria-label="Hide challenge">×</button>
		<strong>Challenge beacon</strong>
		<p><?php echo esc_html( $challenge['prompt'] ); ?></p>
		<details>
			<summary>Need a hint?</summary>
			<p><?php echo esc_html( $challenge['hint'] ); ?></p>
		</details>
		<label>
			<span class="screen-reader-text">Answer</span>
			<input class="world-challenge-answer" type="text" autocomplete="off" maxlength="12" placeholder="codeword">
		</label>
		<button class="world-challenge-submit" type="button">Test answer</button>
		<p class="world-challenge-status" role="status" aria-live="polite"></p>
	</aside>
	<script>
		(function () {
			var beacon = document.querySelector('.world-challenge-beacon');
			if (!beacon) {
				return;
			}

			var answer = <?php echo wp_json_encode( $challenge['answer'] ); ?>;
			var input = beacon.querySelector('.world-challenge-answer');
			var submit = beacon.querySelector('.world-challenge-submit');
			var close = beacon.querySelector('.world-challenge-close');
			var status = beacon.querySelector('.world-challenge-status');

			function testAnswer() {
				var value = (input.value || '').trim().toLowerCase();
				if (value === answer) {
					status.textContent = 'Gate opens: GitHub, issues, and pull requests are the first three living substrates.';
					return;
				}

				status.textContent = 'Not yet. Read the environment model like a map, not a paragraph.';
			}

			submit.addEventListener('click', testAnswer);
			input.addEventListener('keydown', function (event) {
				if (event.key === 'Enter') {
					testAnswer();
				}
			});
			close.addEventListener('click', function () {
				beacon.hidden = true;
			});
		}());
	</script>
	<?php
}
