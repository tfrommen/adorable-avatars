<?php # -*- coding: utf-8 -*-

namespace tfrommen\AdorableAvatars;

/**
 * Main controller.
 *
 * @package tfrommen\AdorableAvatars
 */
class Plugin {

	/**
	 * @var string[]
	 */
	private $plugin_data;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param string $file Main plugin file.
	 */
	public function __construct( $file ) {

		$this->plugin_data = get_file_data( $file, array(
			'text_domain' => 'Text Domain',
		) );
	}

	/**
	 * Initializes the plugin.
	 *
	 * @return void
	 */
	public function initialize() {

		load_plugin_textdomain( $this->plugin_data['text_domain'] );

		$avatar_default = new Avatar\AvatarDefault();
		add_filter( 'avatar_defaults', array( $avatar_default, 'add' ) );

		$avatar = new Avatar\Avatar( $avatar_default );
		add_filter( 'get_avatar', array( $avatar, 'get' ), 10, 6 );
	}
}
