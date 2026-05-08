<?php
/**
 * Plugin Name: World of WordPress
 * Description: A self-contained WordPress Playground terrarium where an agent evolves software and content.
 * Version: 0.1.0
 * Author: Chris Huber
 * License: GPL v2 or later
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
	// The first scaffold intentionally registers no product behavior. The world
	// starts with storage/runtime physics; the agent gets the first creative move.
} );
