<?php # -*- coding: utf-8 -*-

namespace tfrommen\AdorableAvatars\Avatar;

/**
 * The avatar model.
 *
 * @package tfrommen\AdorableAvatars
 */
class Avatar {

	/**
	 * @var string
	 */
	private $default;

	/**
	 * @var string
	 */
	private $url = 'http://api.adorable.io/avatars/';

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @param AvatarDefault $avatar_default Avatar default model.
	 */
	public function __construct( AvatarDefault $avatar_default ) {

		$this->default = $avatar_default->get_value();
	}

	/**
	 * Returns the avatar image tag.
	 *
	 * @wp-hook get_avatar
	 *
	 * @param string $avatar      Avatar image tag.
	 * @param mixed  $id_or_email User identifier.
	 * @param int    $size        Avatar size.
	 * @param string $default     Avatar key.
	 * @param string $alt         Alternative text to use in the avatar image tag.
	 * @param array  $args        Avatar args.
	 *
	 * @return mixed
	 */
	public function get( $avatar, $id_or_email, $size, $default, $alt, array $args ) {

		if ( $this->default !== $default ) {
			return $avatar;
		}

		$urls = $this->get_urls( $id_or_email, $size );

		$avatar = sprintf(
			'<img src="%1$s" srcset="%2$s 2x" width="%3$d" height="%3$d" class="%4$s" alt="%5$s" %6$s>',
			$urls[0],
			$urls[1],
			$size,
			$this->get_class_value( $size, $args ),
			esc_attr( $alt ),
			$args['extra_attr']
		);

		return $avatar;
	}

	/**
	 * Returns the URLs of the standard and retina quality avatar images for the given user identifier and size.
	 *
	 * @param mixed $identifier User identifier.
	 * @param int   $size       Avatar size.
	 *
	 * @return string[]
	 */
	private function get_urls( $identifier, $size ) {

		$urls = array();

		$identifier = $this->get_identifier( $identifier );

		foreach ( array( 1, 2 ) as $factor ) {
			$urls[] = esc_url( $this->url . ( $size * $factor ) . "/$identifier.png" );
		}

		return $urls;
	}

	/**
	 * Returns the identifier string for the given user identifier.
	 *
	 * @param mixed $identifier User identifier.
	 *
	 * @return string
	 */
	private function get_identifier( $identifier ) {

		if ( is_numeric( $identifier ) ) {
			$identifier = get_user_by( 'id', $identifier );
		} elseif ( $identifier instanceof \WP_Post ) {
			$identifier = get_user_by( 'id', $identifier->post_author );
		} elseif ( $identifier instanceof \WP_Comment ) {
			$identifier = get_user_by( 'id', $identifier->user_id );
		}

		if ( $identifier instanceof \WP_User ) {
			$identifier = $identifier->user_email;
		} elseif ( ! is_string( $identifier ) ) {
			return '';
		}

		$identifier = strtolower( $identifier );
		$identifier = md5( $identifier );

		return $identifier;
	}

	/**
	 * Returns the avatar HTML class attribute value for the given avatar size and args.
	 *
	 * @param int   $size Avatar size.
	 * @param array $args Avatar args.
	 *
	 * @return string
	 */
	private function get_class_value( $size, array $args ) {

		$class = array(
			'avatar',
			"avatar-$size",
			'adorable-avatar',
			'photo',
		);

		if ( ! $args['found_avatar'] || $args['force_default'] ) {
			$class[] = 'avatar-default';
		}

		if ( $args['class'] ) {
			$class = array_merge( $class, (array) $args['class'] );
		}

		$class_value = join( ' ', $class );
		$class_value = esc_attr( $class_value );

		return $class_value;
	}
}
