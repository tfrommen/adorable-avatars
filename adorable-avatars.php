<?php # -*- coding: utf-8 -*-
/*
 * Plugin Name: Adorable Avatars
 * Plugin URI:  https://wordpress.org/plugins/adorable-avatars/
 * Description: This plugin integrates the Adorable Avatars avatar placeholder service into WordPress.
 * Author:      Thorsten Frommen
 * Author URI:  https://tfrommen.de
 * Version:     2.0.0
 * Text Domain: adorable-avatars
 * License:     MIT
 */

namespace tfrommen\AdorableAvatars;

if ( ! function_exists( 'add_action' ) ) {
	return;
}

/**
 * Bootstraps the plugin.
 *
 * @since   2.0.0
 * @wp-hook plugins_loaded
 *
 * @return void
 */
function bootstrap() {

	/**
	 * Avatar model.
	 */
	require_once __DIR__ . '/src/Avatar.php';

	load_plugin_textdomain( 'adorable-avatars' );

	$avatar = new Avatar();
	add_filter( 'avatar_defaults', [ $avatar, 'add_to_defaults' ] );
	add_filter( 'get_avatar', [ $avatar, 'filter_avatar' ], 10, 6 );
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bootstrap' );
