<?php # -*- coding: utf-8 -*-

namespace tfrommen\AdorableAvatars;

use WP_Comment;
use WP_Post;
use WP_User;

/**
 * The avatar model.
 *
 * @package tfrommen\AdorableAvatars
 * @since   2.0.0
 */
class Avatar {

	/**
	 * Avatar name.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	const NAME = 'adorable';

	/**
	 * @var string
	 */
	private $url = '//api.adorable.io/avatars/';

	/**
	 * Adds "Adorable Avatar" to the default avatars.
	 *
	 * @since   2.0.0
	 * @wp-hook avatar_defaults
	 *
	 * @param string[] $defaults Array of default avatars.
	 *
	 * @return string[] Array of default avatars, includeing "Adorable Avatar".
	 */
	public function add_to_defaults( array $defaults ) {

		$defaults = [ self::NAME => __( 'Adorable Avatar (Generated)', 'adorable-avatars' ) ] + $defaults;

		return $defaults;
	}

	/**
	 * Filters the avatar image tag.
	 *
	 * @since   2.0.0
	 * @wp-hook get_avatar
	 *
	 * @param string $avatar      Avatar image tag.
	 * @param mixed  $id_or_email User identifier.
	 * @param int    $size        Avatar size.
	 * @param string $default     Avatar key.
	 * @param string $alt         Alternative text to use in the avatar image tag.
	 * @param array  $args        Avatar args.
	 *
	 * @return string Filtered avatar image tag.
	 */
	public function filter_avatar( $avatar, $id_or_email, $size, $default, $alt, array $args ) {

		if ( self::NAME !== $default ) {
			return $avatar;
		}

		if ( ! ( empty( $args['found_avatar'] ) || $args['force_default'] ) ) {
			return $avatar;
		}

		$urls = $this->get_urls( $id_or_email, $size );

		$avatar = sprintf(
			'<img src="%1$s" srcset="%2$s 2x" width="%3$d" height="%3$d" class="%4$s" alt="%5$s" %6$s>',
			esc_url( $urls[0] ),
			esc_url( $urls[1] ),
			esc_attr( $size ),
			esc_attr( $this->get_class_value( $size, $args ) ),
			esc_attr( $alt ),
			isset( $args['extra_attr'] ) ? $args['extra_attr'] : ''
		);

		return $avatar;
	}

	/**
	 * Returns the URLs of the standard and retina quality avatar images for the given user identifier and size.
	 *
	 * @param mixed $identifier User identifier.
	 * @param int   $size       Avatar size.
	 *
	 * @return string[] The URLs of the standard and retina quality avatar images.
	 */
	private function get_urls( $identifier, $size ) {

		$identifier = $this->get_identifier( $identifier );

		$urls = array_map( function ( $factor ) use ( $identifier, $size ) {

			return $this->url . ( $size * $factor ) . "/$identifier.png";
		}, [ 1, 2 ] );

		return $urls;
	}

	/**
	 * Returns the identifier string for the given user identifier.
	 *
	 * @param mixed $identifier User identifier.
	 *
	 * @return string The identifier string for the given user identifier.
	 */
	private function get_identifier( $identifier ) {

		if ( is_numeric( $identifier ) ) {
			$identifier = get_user_by( 'id', $identifier );
		} elseif ( $identifier instanceof WP_Post ) {
			$identifier = get_user_by( 'id', $identifier->post_author );
		} elseif ( $identifier instanceof WP_Comment ) {
			$identifier = get_user_by( 'id', $identifier->user_id );
		}

		if ( $identifier instanceof WP_User ) {
			$identifier = $identifier->user_email;
		} elseif ( ! is_string( $identifier ) ) {
			return '';
		}

		$identifier = md5( strtolower( $identifier ) );

		return $identifier;
	}

	/**
	 * Returns the avatar HTML class attribute value for the given avatar size and args.
	 *
	 * @param int   $size Avatar size.
	 * @param array $args Avatar args.
	 *
	 * @return string The avatar HTML class attribute value
	 */
	private function get_class_value( $size, array $args ) {

		$class = [
			'avatar',
			"avatar-$size",
			'adorable-avatar',
			'photo',
		];

		if ( empty( $args['found_avatar'] ) || $args['force_default'] ) {
			$class[] = 'avatar-default';
		}

		if ( ! empty( $args['class'] ) ) {
			$class = array_unique( array_merge( $class, (array) $args['class'] ) );
		}

		return join( ' ', $class );
	}
}
