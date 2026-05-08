<?php
/**
 * World of WordPress db.php drop-in.
 *
 * This is a repository-local copy of Markdown Database Integration's
 * Playground-aware drop-in shape so Homeboy mounts it as
 * /wordpress/wp-content/db.php when this repo is the component under test.
 */

define( 'SQLITE_DB_DROPIN_VERSION', '1.8.0' );
define( 'MARKDOWN_DB_DROPIN', true );

if ( ! defined( 'MARKDOWN_DB_MODE' ) ) {
	define( 'MARKDOWN_DB_MODE', 'primary' );
}
if ( ! defined( 'MARKDOWN_DB_CONTENT_DIR' ) ) {
	define( 'MARKDOWN_DB_CONTENT_DIR', WP_CONTENT_DIR . '/plugins/world-of-wordpress/content/markdown' );
}

$sqlite_plugin_implementation_folder_path = realpath( __DIR__ . '/mu-plugins/sqlite-database-integration' );
if ( ! $sqlite_plugin_implementation_folder_path || ! file_exists( $sqlite_plugin_implementation_folder_path ) ) {
	$sqlite_plugin_implementation_folder_path = realpath( __DIR__ . '/plugins/sqlite-database-integration' );
}
if ( ! $sqlite_plugin_implementation_folder_path || ! file_exists( $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/db.php' ) ) {
	$playground_sqlite = '/internal/shared/sqlite-database-integration';
	if ( file_exists( $playground_sqlite . '/wp-includes/sqlite/db.php' ) ) {
		$sqlite_plugin_implementation_folder_path = $playground_sqlite;
	}
}

if ( ! $sqlite_plugin_implementation_folder_path || ! file_exists( $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/db.php' ) ) {
	return;
}

if ( ! defined( 'DATABASE_TYPE' ) ) {
	define( 'DATABASE_TYPE', 'sqlite' );
}
if ( ! defined( 'DB_ENGINE' ) ) {
	define( 'DB_ENGINE', 'sqlite' );
}
if ( ! defined( 'WP_SQLITE_AST_DRIVER' ) ) {
	define( 'WP_SQLITE_AST_DRIVER', true );
}

if ( defined( 'MARKDOWN_DB_MODE' ) && 'primary' === MARKDOWN_DB_MODE ) {
	$markdown_db_content_dir = defined( 'MARKDOWN_DB_CONTENT_DIR' )
		? MARKDOWN_DB_CONTENT_DIR
		: WP_CONTENT_DIR . '/markdown';
	$markdown_db_index_path  = dirname( rtrim( $markdown_db_content_dir, '/\\' ) ) . '/markdown-index.sqlite';
	if ( file_exists( '/internal/shared/sqlite-database-integration/wp-includes/sqlite/db.php' ) ) {
		$markdown_db_index_path = rtrim( sys_get_temp_dir(), '/\\' ) . '/markdown-index-' . substr( md5( $markdown_db_index_path ), 0, 12 ) . '.sqlite';
	}

	if ( ! defined( 'MARKDOWN_DB_INDEX_PATH' ) ) {
		define( 'MARKDOWN_DB_INDEX_PATH', $markdown_db_index_path );
	}
	if ( ! defined( 'FQDBDIR' ) ) {
		define( 'FQDBDIR', rtrim( dirname( MARKDOWN_DB_INDEX_PATH ), '/\\' ) . '/' );
	}
	if ( ! defined( 'FQDB' ) ) {
		define( 'FQDB', MARKDOWN_DB_INDEX_PATH );
	}
}

require_once $sqlite_plugin_implementation_folder_path . '/wp-includes/database/version.php';
require_once $sqlite_plugin_implementation_folder_path . '/constants.php';

if ( ! extension_loaded( 'pdo' ) || ! extension_loaded( 'pdo_sqlite' ) ) {
	return;
}

require_once $sqlite_plugin_implementation_folder_path . '/wp-includes/database/load.php';
require_once $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/class-wp-sqlite-db.php';
require_once $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/install-functions.php';

if ( ! function_exists( 'world_of_wordpress_store_has_siteurl' ) ) {
	function world_of_wordpress_store_has_siteurl( string $content_dir ): bool {
		$siteurl_file = rtrim( $content_dir, '/\\' ) . '/_options/siteurl.json';
		if ( file_exists( $siteurl_file ) ) {
			return true;
		}

		$legacy_file = rtrim( $content_dir, '/\\' ) . '/options.json';
		if ( ! file_exists( $legacy_file ) ) {
			return false;
		}

		$decoded = json_decode( (string) file_get_contents( $legacy_file ), true );
		if ( ! is_array( $decoded ) ) {
			return false;
		}

		foreach ( $decoded as $row ) {
			if ( is_array( $row ) && isset( $row['option_name'] ) && 'siteurl' === $row['option_name'] ) {
				return true;
			}
		}

		return false;
	}
}

if ( defined( 'MARKDOWN_DB_MODE' ) && 'primary' === MARKDOWN_DB_MODE ) {
	if ( ! world_of_wordpress_store_has_siteurl( MARKDOWN_DB_CONTENT_DIR ) ) {
		if ( ! defined( 'MARKDOWN_DB_INSTALL_FALLBACK' ) ) {
			define( 'MARKDOWN_DB_INSTALL_FALLBACK', true );
		}
		$db_name          = defined( 'DB_NAME' ) && '' !== DB_NAME ? DB_NAME : 'database_name_here';
		$GLOBALS['wpdb'] = new WP_SQLite_DB( $db_name );
		return;
	}
}

$markdown_plugin_dir = null;
$possible_paths      = array(
	__DIR__ . '/mu-plugins/markdown-database-integration',
	__DIR__ . '/plugins/markdown-database-integration',
);

foreach ( $possible_paths as $path ) {
	if ( is_dir( $path ) && file_exists( $path . '/inc/class-wp-markdown-storage.php' ) ) {
		$markdown_plugin_dir = $path;
		break;
	}
}

if ( ! $markdown_plugin_dir ) {
	$db_name          = defined( 'DB_NAME' ) && '' !== DB_NAME ? DB_NAME : 'database_name_here';
	$GLOBALS['wpdb'] = new WP_SQLite_DB( $db_name );
	return;
}

$composer_autoload = $markdown_plugin_dir . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
	require_once $composer_autoload;
}

require_once $markdown_plugin_dir . '/inc/class-wp-markdown-storage.php';
require_once $markdown_plugin_dir . '/inc/class-wp-markdown-driver.php';
require_once $markdown_plugin_dir . '/inc/class-wp-markdown-search.php';
require_once $markdown_plugin_dir . '/inc/class-wp-markdown-write-engine.php';
require_once $markdown_plugin_dir . '/inc/class-wp-markdown-loader.php';
require_once $markdown_plugin_dir . '/inc/class-wp-markdown-db.php';

if ( ! defined( 'MARKDOWN_DB_VERSION' ) ) {
	require_once $markdown_plugin_dir . '/markdown-database-integration.php';
}

$db_name          = defined( 'DB_NAME' ) && '' !== DB_NAME ? DB_NAME : 'database_name_here';
$GLOBALS['wpdb'] = new WP_Markdown_DB( $db_name );

$qm_boot = $sqlite_plugin_implementation_folder_path . '/integrations/query-monitor/boot.php';
if ( file_exists( $qm_boot ) ) {
	require_once $qm_boot;
}
