<?php # -*- coding: utf-8 -*-

namespace tfrommen\AdorableAvatars\Avatar;

/**
 * The avatar default model.
 *
 * @package tfrommen\AdorableAvatars
 */
class AvatarDefault {

	/**
	 * @var string
	 */
	private $value = 'adorable';

	/**
	 * Adds Adorable Avatar to the default avatars.
	 *
	 * @wp-hook avatar_defaults
	 *
	 * @param string[] $avatar_defaults Array of default avatars.
	 *
	 * @return string[]
	 */
	public function add( array $avatar_defaults ) {

		$avatar_defaults[ $this->value ] = __( 'Adorable Avatar (Generated)', 'adorable-avatars' );

		return $avatar_defaults;
	}

	/**
	 * Returns the default avatar value.
	 *
	 * @return string
	 */
	public function get_value() {

		return $this->value;
	}
}
