<?php # -*- coding: utf-8 -*-

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( 'adorable' === get_option( 'avatar_default' ) ) {
	update_option( 'avatar_default', 'mystery' );
}
