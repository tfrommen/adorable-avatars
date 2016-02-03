<?php # -*- coding: utf-8 -*-

namespace tfrommen\Tests\AdorableAvatars\Avatar;

use Mockery;
use tfrommen\AdorableAvatars\Avatar\AvatarDefault as Testee;
use WP_Mock;

/**
 * Test case for the avatar default model.
 */
class AvatarDefaultTest extends WP_Mock\Tools\TestCase {

	/**
	 * @var Testee
	 */
	private $testee;

	/**
	 * Sets up the testee.
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();

		$this->testee = new Testee();
	}

	/**
	 * Tests if the correct default data is added to the avatar defaults.
	 *
	 * @covers       AdorableAvatars\Avatar\AvatarDefault::add
	 * @dataProvider add_adds_correct_default_data_provider
	 *
	 * @param string[] $avatar_defaults
	 *
	 * @return void
	 */
	public function test_add_adds_correct_default_data( $avatar_defaults ) {

		WP_Mock::wpPassthruFunction( '__', array(
			'args'  => array(
				Mockery::type( 'string' ),
				'adorable-avatars',
			),
			'times' => 1,
		) );

		$avatar_defaults = $this->testee->add( $avatar_defaults );

		$this->assertArrayHasKey( 'adorable', $avatar_defaults );

		$this->assertSame( 'Adorable Avatar (Generated)', $avatar_defaults['adorable'] );
	}

	/**
	 * Data provider for test_add_adds_correct_default_data().
	 *
	 * @return array[]
	 */
	public function add_adds_correct_default_data_provider() {

		return array(
			'empty'    => array(
				'avatar_defaults' => array(),
			),
			'mystery'  => array(
				'avatar_defaults' => array( 'mystery' => 'Mystery Person' ),
			),
			'adorable' => array(
				'avatar_defaults' => array( 'adorable' => 'Wrong text' ),
			),
		);
	}

	/**
	 * Tests if the correct default value is returned.
	 *
	 * @covers AdorableAvatars\Avatar\AvatarDefault::get_value
	 *
	 * @return void
	 */
	public function test_get_value_returns_correct_default_value() {

		$this->assertSame( 'adorable', $this->testee->get_value() );
	}
}
