<?php
/**
 * Plugin Name: World of WordPress
 * Description: Repository-root shim. The real plugin lives in plugins/world-of-wordpress/. This file exists so CI dep-loaders that scan the repo root for a plugin entry file can still discover the plugin.
 * Version: 0.2.0
 * Author: Chris Huber
 * License: GPL v2 or later
 * Text Domain: world-of-wordpress
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/plugins/world-of-wordpress/world-of-wordpress.php';
