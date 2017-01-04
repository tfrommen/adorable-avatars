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
	 * Tests if the avatar is not replaced for a wrong default.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::replace_avatar()
	 * @since  2.1.0
	 *
	 * @return void
	 */
	public function test_not_replacing_avatar_for_wrong_default() {

		$avatar = 'some-avatar-here';

		$this->assertSame( $avatar, ( new Testee() )->replace_avatar( $avatar, null, [ 'default' => 'wrong' ] ) );
	}

	/**
	 * Tests if the avatar is not replaced when it is not forced to.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::replace_avatar()
	 * @since  2.1.0
	 *
	 * @return void
	 */
	public function test_not_replacing_avatar_when_not_forced() {

		Monkey\WP\Filters::expectApplied( 'adorable_avatars.force' )
			->andReturn( false );

		$avatar = 'some-avatar-here';

		$this->assertSame( $avatar, ( new Testee() )->replace_avatar( $avatar, null, [ 'default' => Testee::NAME ] ) );
	}

	/**
	 * Tests if the avatar is replaced as expected.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::replace_avatar()
	 * @since  2.0.0
	 *
	 * @return void
	 */
	public function test_replace_avatar() {

		Monkey\Functions::when( 'is_ssl' )
			->justReturn();

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
			'size'       => $size,
			'alt'        => $alt,
			'default'    => Testee::NAME,
		];

		Monkey\WP\Filters::expectApplied( 'adorable_avatars.force' )
			->with( false, $id_or_email, $args )
			->andReturn( true );

		$avatar = ( new Testee() )->replace_avatar( null, $id_or_email, $args );

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
	 * @covers       \tfrommen\AdorableAvatars\Avatar::replace_avatar()
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

		Monkey\WP\Filters::expectApplied( 'adorable_avatars.force' )
			->andReturn( true );

		$is_ssl = (bool) rand( 0, 1 );

		Monkey\Functions::when( 'is_ssl' )
			->justReturn( $is_ssl );

		Monkey\Functions::when( 'esc_url' )
			->returnArg();

		Monkey\Functions::when( 'esc_attr' )
			->returnArg();

		Monkey\Functions::expect( 'get_user_by' )
			->withArgs( [ 'id', $user->ID ] )
			->andReturn( $user );

		$size = rand( 0, 400 );

		$avatar = ( new Testee() )->replace_avatar( null, $id_or_email, [
			'size'    => $size,
			'default' => Testee::NAME,
			'alt'     => null,
		] );

		$this->assertRegExp( '~ src="[^"]+/' . $size . '/' . $identifier . '\.png"~', $avatar );

		$this->assertRegExp( '~ srcset="[^"]+/' . ( 2 * $size ) . '/' . $identifier . '\.png 2x"~', $avatar );

		$this->assertSame( $is_ssl, false !== strpos( $avatar, ' src="https://' ) );
		$this->assertSame( $is_ssl, false === strpos( $avatar, ' src="http://' ) );

		$this->assertSame( $is_ssl, false !== strpos( $avatar, ' srcset="https://' ) );
		$this->assertSame( $is_ssl, false === strpos( $avatar, ' srcset="http://' ) );
	}

	/**
	 * Data provider for test_return_avatar_with_expected_urls().
	 *
	 * @return array[]
	 */
	public function identifier_data_provider() {

		$email = 'someone@example.com';

		$post = Mockery::mock( 'WP_Post' );

		$post->post_author = 42;

		$comment = Mockery::mock( 'WP_Comment' );

		$comment->user_id = 42;

		$guest_comment = Mockery::mock( 'WP_Comment' );

		$guest_comment->comment_author_email = $email;

		$guest_comment->user_id = 0;

		$user = Mockery::mock( 'WP_User' );

		$user->ID = 42;

		$user->user_email = $email;

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
			'guest_comment'  => [
				'id_or_email' => $guest_comment,
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
	 * @covers       \tfrommen\AdorableAvatars\Avatar::replace_avatar()
	 * @dataProvider avatar_with_default_class_args_provider
	 * @since        2.0.0
	 *
	 * @param array $args Array args.
	 *
	 * @return void
	 */
	public function test_return_avatar_with_default_class( array $args ) {

		Monkey\WP\Filters::expectApplied( 'adorable_avatars.force' )
			->andReturn( true );

		Monkey\Functions::when( 'is_ssl' )
			->justReturn();

		Monkey\Functions::when( 'esc_url' )
			->returnArg();

		Monkey\Functions::when( 'esc_attr' )
			->returnArg();

		$avatar = ( new Testee() )->replace_avatar( null, null, array_merge( $args, [
			'default' => Testee::NAME,
			'size'    => null,
			'alt'     => null,
		] ) );

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

	/**
	 * Tests if the default argument is not replaced for a wrong default.
	 *
	 * @covers \tfrommen\AdorableAvatars\Avatar::replace_default()
	 * @since  2.1.0
	 *
	 * @return void
	 */
	public function test_not_replacing_default_argument_for_wrong_default() {

		$this->assertSame( null, ( new Testee() )->replace_default( null, null, null, 'wrong' ) );
	}

	/**
	 * Tests if the returned avatar has the expected default URL.
	 *
	 * @covers       \tfrommen\AdorableAvatars\Avatar::replace_default()
	 * @dataProvider identifier_data_provider
	 * @since        2.1.0
	 *
	 * @param mixed  $id_or_email User identifier.
	 * @param string $identifier  User identifier string.
	 * @param object $user        User object.
	 *
	 * @return void
	 */
	public function test_return_avatar_with_expected_default_url( $id_or_email, $identifier, $user ) {

		$is_ssl = (bool) rand( 0, 1 );

		Monkey\Functions::when( 'is_ssl' )
			->justReturn( $is_ssl );

		Monkey\Functions::expect( 'get_user_by' )
			->withArgs( [ 'id', $user->ID ] )
			->andReturn( $user );

		$size = rand( 0, 400 );

		$avatar = ( new Testee() )->replace_default(
			'https://example.com/?foo=bar&d=' . Testee::NAME . '&baz=qux',
			$id_or_email,
			$size,
			Testee::NAME
		);

		$protocol = preg_quote( urlencode( ( $is_ssl ? 'https' : 'http' ) . '://' ) );

		$url = preg_quote( urlencode( "/{$size}/{$identifier}.png" ) );

		$this->assertRegExp( "~&d={$protocol}[^&]+{$url}&~", $avatar );
	}
}
