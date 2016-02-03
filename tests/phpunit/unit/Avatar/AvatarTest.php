<?php # -*- coding: utf-8 -*-

namespace tfrommen\Tests\AdorableAvatars\Avatar;

use Mockery;
use tfrommen\AdorableAvatars\Avatar\Avatar as Testee;
use tfrommen\AdorableAvatars\Avatar\AvatarDefault;
use WP_Mock;

/**
 * Test case for the avatar model.
 */
class AvatarTest extends WP_Mock\Tools\TestCase {

	/**
	 * @var Testee
	 */
	private $testee;

	/**
	 * @var string
	 */
	private $default = 'avatar_default';

	/**
	 * Sets up the testee.
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();

		/** @var AvatarDefault $avatar_default */
		$avatar_default = Mockery::mock( 'tfrommen\AdorableAvatars\Avatar\AvatarDefault' )
			->shouldReceive( 'get_value' )
			->andReturn( $this->default )
			->getMock();

		$this->testee = new Testee( $avatar_default );
	}

	/**
	 * Tests if the passed avatar is returned for the wrong default value.
	 *
	 * @covers AdorableAvatars\Avatar\Avatar::get
	 *
	 * @return void
	 */
	public function test_get_returns_passed_avatar_for_wrong_default_value() {

		$avatar = md5( time() );

		$this->assertSame( $avatar, $this->testee->get( $avatar, 0, '', 'wrong_default', '', array() ) );
	}

	/**
	 * Tests if the correct avatar is returned.
	 *
	 * @covers AdorableAvatars\Avatar\Avatar::get
	 *
	 * @return void
	 */
	public function test_get_returns_correct_avatar() {

		WP_Mock::wpPassthruFunction( 'esc_url', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		WP_Mock::wpPassthruFunction( 'esc_attr', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		WP_Mock::wpFunction( 'get_user_by', array(
			'args'  => array(
				'id',
				Mockery::type( 'int' ),
			),
			'times' => 0,
		) );

		$size = rand( 0, 400 );

		$alt = 'alt';

		$avatar = $this->testee->get( '', md5( time() ), $size, $this->default, $alt, $this->get_default_get_args() );

		$matches = array();
		preg_match(
			'/^<img src="([^"]+)" srcset="([^"]+) 2x" width="(\d*)" height="(\d*)" class="([^"]+)" alt="([^"]+)"/',
			$avatar,
			$matches
		);

		$this->assertCount( 7, $matches );

		$this->assertRegExp( '~/' . $size . '/[0-9a-f]*\.png$~', $matches[1] );

		$this->assertRegExp( '~/' . ( 2 * $size ) . '/[0-9a-f]*\.png$~', $matches[2] );

		$this->assertEquals( $matches[3], $size );

		$this->assertEquals( $matches[4], $size );

		$this->assertContains( "avatar-$size", $matches[5] );

		$this->assertSame( $matches[6], $alt );
	}

	/**
	 * Tests if for an invalid ID or email address an avatar with an empty identifier is returned.
	 *
	 * @covers       AdorableAvatars\Avatar\Avatar::get
	 * @dataProvider get_returns_avatar_with_empty_identifier_for_invalid_id_or_email_provider
	 *
	 * @param mixed $id_or_email
	 *
	 * @return void
	 */
	public function test_get_returns_avatar_with_empty_identifier_for_invalid_id_or_email( $id_or_email ) {

		WP_Mock::wpPassthruFunction( 'esc_url', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		WP_Mock::wpPassthruFunction( 'esc_attr', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		$avatar = $this->testee->get( '', $id_or_email, 0, $this->default, '', $this->get_default_get_args() );

		$this->assertSame( 1, preg_match( '~ src="[^"]+/\.png" srcset="[^"]+/\.png ~', $avatar ) );
	}

	/**
	 * Data provider for test_get_returns_avatar_with_empty_identifier_for_invalid_id_or_email().
	 *
	 * @return array[]
	 */
	public function get_returns_avatar_with_empty_identifier_for_invalid_id_or_email_provider() {

		return array(
			'null'     => array(
				'id_or_email' => null,
			),
			'array'    => array(
				'id_or_email' => array(),
			),
			'stdClass' => array(
				'id_or_email' => new \stdClass(),
			),
		);
	}

	/**
	 * Tests if an avatar with the avatar-default HTML class is returned.
	 *
	 * @covers       AdorableAvatars\Avatar\Avatar::get
	 * @dataProvider get_returns_avatar_with_avatar_default_class_provider
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function test_get_returns_avatar_with_avatar_default_class( array $args ) {

		WP_Mock::wpPassthruFunction( 'esc_url', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		WP_Mock::wpPassthruFunction( 'esc_attr', array(
			'args'  => array(
				Mockery::type( 'string' ),
			),
			'times' => 2,
		) );

		$args = array_replace( $this->get_default_get_args(), $args );

		$avatar = $this->testee->get( '', '', 0, $this->default, '', $args );

		$this->assertSame( 1, preg_match( '~ class="[^"]+ avatar-default(?:\s|")~', $avatar ) );
	}

	/**
	 * Data provider for test_get_returns_avatar_with_avatar_default_class().
	 *
	 * @return array[]
	 */
	public function get_returns_avatar_with_avatar_default_class_provider() {

		return array(
			'no_avatar_found' => array(
				'args' => array( 'found_avatar' => false ),
			),
			'force_default'   => array(
				'args' => array( 'force_default' => true ),
			),
		);
	}

	/**
	 * Returns the default args for AdorableAvatars\Avatar\Avatar::get().
	 *
	 * @return array
	 */
	private function get_default_get_args() {

		return array(
			'class'         => '',
			'extra_attr'    => '',
			'force_default' => false,
			'found_avatar'  => true,
		);
	}
}
