<?php # -*- coding: utf-8 -*-

use tfrommen\AdorableAvatars\Avatar;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( Avatar::NAME === get_option( 'avatar_default' ) ) {
	update_option( 'avatar_default', 'mystery' );
}
