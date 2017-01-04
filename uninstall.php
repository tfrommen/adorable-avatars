<?php # -*- coding: utf-8 -*-

use tfrommen\AdorableAvatars\Avatar;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

/**
 * Avatar model.
 */
require_once __DIR__ . '/src/Avatar.php';

if ( Avatar::NAME === get_option( 'avatar_default' ) ) {
	update_option( 'avatar_default', 'mystery' );
}
