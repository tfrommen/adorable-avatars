<?php # -*- coding: utf-8 -*-

namespace tfrommen\AdorableAvatars\Tests\Unit;

use Brain\Monkey;
use Mockery;
use tfrommen\AdorableAvatars\Avatar as Testee;
use tfrommen\AdorableAvatars\Tests\TestCase;

/**
 * Test case for the avatar model.
 *
 * @package tfrommen\AdorableAvatars\Tests\Unit
 * @since   2.0.0
 */
class AvatarTest extends TestCase {

	/**
	 * Tests if the correct data is added to the avatar defaults.
	 *
	 * @covers       \tfrommen\AdorableAvatars\Avatar::add_to_defaults()
	 * @dataProvider adding_to_defaults_provider
	 * @since        2.0.0
	 *
	 * @param string[] $defaults Avatar defaults data.
	 *
	 * @return void
	 */
	public function test_adding_to_defaults( array $defaults ) {

		$text = 'Adorable Avatar (Generated)';

		Monkey\Functions::expect( '__' )
			->once()
			->withArgs( [
				Mockery::type( 'string' ),
				'adorable-avatars',
			] )
			->andReturn( $text );

		$filtered_defaults = ( new Testee() )->add_to_defaults( $defaults );

		$this->assertSame( $text, $filtered_defaults[ Testee::NAME ] );
	}

	/**
	 * Data provider for test_adding_to_defaults().
	 *
	 * @return array[]
	 */
	public function adding_to_defaults_provider() {

		return [
			'empty'     => [
				'defaults' => [],
			],
			'mystery'   => [
				'defaults' => [ 'mystery' => 'Mystery Person' ],
			],
			'overwrite' => [
				'defaults' => [ Testee::NAME => null ],
			],
		];
	}

	/**
	 * Tests if the passed avatar is returned for a wrong default.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::filter_avatar()
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function test_return_passed_avatar_for_wrong_default() {

		$avatar = 'some-avatar-here';

		$this->assertSame( $avatar, ( new Testee() )->filter_avatar( $avatar, null, null, null, null, [] ) );
	}

	/**
	 * Tests if a found avatar is returned when not forcing the default.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::filter_avatar()
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function test_return_found_avatar_when_not_forcing_default() {

		$avatar = 'some-avatar-here';

		$this->assertSame( $avatar, ( new Testee() )->filter_avatar( $avatar, null, null, Testee::NAME, null, [
			'force_default' => false,
			'found_avatar'  => true,
		] ) );
	}

	/**
	 * Tests if an avatar with the expected data is returned.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::filter_avatar()
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function test_return_avatar_with_expected_data() {

		Monkey\Functions::when( 'esc_url' )
			->returnArg();

		Monkey\Functions::when( 'esc_attr' )
			->returnArg();

		$id_or_email = null;

		$size = rand( 0, 400 );

		$alt = 'some alt text';

		$args = [
			'class'      => 'some classes here',
			'extra_attr' => 'some="attr"',
		];

		$avatar = ( new Testee() )->filter_avatar( null, $id_or_email, $size, Testee::NAME, $alt, $args );

		$this->assertNotFalse( strpos( $avatar, ' width="' . $size . '"' ) );

		$this->assertNotFalse( strpos( $avatar, ' height="' . $size . '"' ) );

		$matches = [];
		preg_match( '/ class="([^"]+)"/', $avatar, $matches );
		$classes = explode( ' ', $matches[1] );

		$default_classes = [
			'avatar',
			"avatar-$size",
			'adorable-avatar',
			'photo',
		];
		array_walk( $default_classes, function ( $class ) use ( $classes ) {

			$this->assertContains( $class, $classes );
		} );

		$custom_classes = explode( ' ', $args['class'] );
		array_walk( $custom_classes, function ( $class ) use ( $classes ) {

			$this->assertContains( $class, $classes );
		} );

		$this->assertNotFalse( strpos( $avatar, ' alt="' . $alt . '"' ) );

		$this->assertNotFalse( strpos( $avatar, " {$args['extra_attr']}" ) );
	}

	/**
	 * Tests if an avatar with the expected URLs is returned.
	 *
	 * @covers       \tfrommen\AdorableAvatars\Avatar::filter_avatar()
	 * @dataProvider identifier_data_provider
	 * @since        2.0.0
	 *
	 * @param mixed  $id_or_email User identifier.
	 * @param string $identifier  User identifier string.
	 * @param object $user        User object.
	 *
	 * @return void
	 */
	public function test_return_avatar_with_expected_urls( $id_or_email, $identifier, $user ) {

		Monkey\Functions::when( 'esc_url' )
			->returnArg();

		Monkey\Functions::when( 'esc_attr' )
			->returnArg();

		Monkey\Functions::expect( 'get_user_by' )
			->withArgs( [ 'id', $user->ID ] )
			->andReturn( $user );

		$size = rand( 0, 400 );

		$avatar = ( new Testee() )->filter_avatar( null, $id_or_email, $size, Testee::NAME, null, [] );

		$this->assertRegExp( '~ src="[^"]+/' . $size . '/' . $identifier . '\.png"~', $avatar );

		$this->assertRegExp( '~ srcset="[^"]+/' . ( 2 * $size ) . '/' . $identifier . '\.png 2x"~', $avatar );
	}

	/**
	 * Data provider for test_return_avatar_with_expected_urls().
	 *
	 * @return array[]
	 */
	public function identifier_data_provider() {

		$post = Mockery::mock( 'WP_Post' );

		$post->post_author = 42;

		$comment = Mockery::mock( 'WP_Comment' );

		$comment->user_id = 42;

		$user = Mockery::mock( 'WP_User' );

		$user->ID = 42;

		$user->user_email = 'someone@example.com';

		$identifier = md5( strtolower( $user->user_email ) );

		return [
			'null'           => [
				'id_or_email' => null,
				'identifier'  => '',
				'user'        => $user,
			],
			'empty_array'    => [
				'id_or_email' => [],
				'identifier'  => '',
				'user'        => $user,
			],
			'empty_object'   => [
				'id_or_email' => (object) [],
				'identifier'  => '',
				'user'        => $user,
			],
			'numeric_string' => [
				'id_or_email' => (string) $user->ID,
				'identifier'  => $identifier,
				'user'        => $user,
			],
			'int'            => [
				'id_or_email' => $user->ID,
				'identifier'  => $identifier,
				'user'        => $user,
			],
			'post'           => [
				'id_or_email' => $post,
				'identifier'  => $identifier,
				'user'        => $user,
			],
			'comment'        => [
				'id_or_email' => $comment,
				'identifier'  => $identifier,
				'user'        => $user,
			],
			'user'           => [
				'id_or_email' => $user,
				'identifier'  => $identifier,
				'user'        => $user,
			],
		];
	}

	/**
	 * Tests if the returned avatar has the default class.
	 *
	 * @covers       \tfrommen\AdorableAvatars\Avatar::filter_avatar()
	 * @dataProvider avatar_with_default_class_args_provider
	 * @since        2.0.0
	 *
	 * @param array $args Array args.
	 *
	 * @return void
	 */
	public function test_return_avatar_with_default_class( array $args ) {

		Monkey\Functions::when( 'esc_url' )
			->returnArg();

		Monkey\Functions::when( 'esc_attr' )
			->returnArg();

		$avatar = ( new Testee() )->filter_avatar( null, null, null, Testee::NAME, null, $args );

		$this->assertSame( 1, preg_match( '~ class="(?:[^"]*\s)?avatar-default(?:\s|")~', $avatar ) );
	}

	/**
	 * Data provider for test_return_avatar_with_default_class().
	 *
	 * @return array[]
	 */
	public function avatar_with_default_class_args_provider() {

		return [
			'empty_args'      => [
				'args' => [],
			],
			'no_avatar_found' => [
				'args' => [
					'found_avatar' => false,
				],
			],
			'force_default'   => [
				'args' => [
					'force_default' => true,
				],
			],
		];
	}
}
