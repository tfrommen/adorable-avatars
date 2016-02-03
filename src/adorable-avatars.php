<?php # -*- coding: utf-8 -*-
/**
 * Plugin Name: Adorable Avatars
 * Plugin URI:  https://wordpress.org/plugins/adorable-avatars/
 * Description: This plugin integrates the Adorable Avatars avatar placeholder service into WordPress.
 * Author:      Thorsten Frommen
 * Author URI:  http://tfrommen.de
 * Version:     1.1.0
 * Text Domain: adorable-avatars
 * License:     MIT
 */

namespace tfrommen\AdorableAvatars;

if ( ! function_exists( 'add_action' ) ) {
	return;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\initialize' );

/**
 * Initializes the plugin.
 *
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function initialize() {

	require __DIR__ . '/autoload.php';

	$plugin = new Plugin( __FILE__ );
	$plugin->initialize();
}
