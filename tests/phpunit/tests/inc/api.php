<?php
/**
 * Test Envato Market API class.
 *
 * @package Envato_Market
 * @group functions
 */

/**
 * Class Tests_Envato_Market_API
 */
class Tests_Envato_Market_API extends WP_UnitTestCase {

	/**
	 * @var Envato_Market_Items
	 */
	public $api;

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->api = envato_market()->api();
	}

	/**
	 * Request will error with a non-working token.
	 */
	function test_request_is_wp_error() {
		envato_market()->api()->token = '';
		$this->assertInstanceOf( 'WP_Error', envato_market()->api()->request( 'https://api.envato.com/v1/market/total-items.json' ) );

		envato_market()->api()->token = '12345';
		$this->assertInstanceOf( 'WP_Error', envato_market()->api()->request( 'https://api.envato.com/v1/market/total-items.json' ) );
	}

	/**
	 * Request succeeded with working token and being passed to args.
	 */
	function test_request_success() {
		if ( '' !== TOKEN ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . TOKEN,
				),
			);
			$this->assertArrayHasKey( 'total-items', envato_market()->api()->request( 'https://api.envato.com/v1/market/total-items.json', $args ) );
		}
	}

	/**
	 * Builds a deferred download url.
	 */
	function test_deferred_download() {
		$dowload = envato_market()->api()->deferred_download( 12345 );
		$this->assertContains( 'deferred_download=1', $dowload );
		$this->assertContains( 'item_id=12345', $dowload );
	}

	/**
	 * Builds a theme download url
	 */
	function test_deferred_download_theme() {
		if ( '' !== TOKEN ) {
			envato_market()->api()->token = TOKEN;
			$themes = envato_market()->api()->themes();
			shuffle( $themes );
			$item = array_shift( $themes );
			$download = envato_market()->api()->download( $item['id'] );
			$this->assertNotEmpty( $download );
		}
	}

	/**
	 * Builds a plugin download url
	 */
	function test_deferred_download_plugin() {
		if ( '' !== TOKEN ) {
			envato_market()->api()->token = TOKEN;
			$plugins = envato_market()->api()->plugins();
			shuffle( $plugins );
			$item = array_shift( $plugins );
			$download = envato_market()->api()->download( $item['id'] );
			$this->assertNotEmpty( $download );
		}
	}

	/**
	 * @see Envato_Market_API::item()
	 */
	function test_item_false_from_error() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( new WP_Error( 'broken' ) ) );

		$item = $mock->item( 2751380 );
		$this->assertFalse( $item );
	}

	/**
	 * @see Envato_Market_API::item()
	 */
	function test_item_false_from_empty() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( '' ) );

		$item = $mock->item( 2751380 );
		$this->assertFalse( $item );
	}

	/**
	 * @see Envato_Market_API::item()
	 */
	function test_item_theme() {
		$contents = file_get_contents( TESTS_DATA_DIR . '/theme.json' );
		$json = json_decode( $contents, true );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( $json ) );

		$item = $mock->item( 548199 );
		$this->assertArrayHasKey( 'id', $item );
		$this->assertArrayHasKey( 'name', $item );
		$this->assertArrayHasKey( 'author', $item );
		$this->assertArrayHasKey( 'version', $item );
	}

	/**
	 * @see Envato_Market_API::item()
	 */
	function test_item_plugin() {
		$contents = file_get_contents( TESTS_DATA_DIR . '/plugin.json' );
		$json = json_decode( $contents, true );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( $json ) );

		$item = $mock->item( 2751380 );
		$this->assertArrayHasKey( 'id', $item );
		$this->assertArrayHasKey( 'name', $item );
		$this->assertArrayHasKey( 'author', $item );
		$this->assertArrayHasKey( 'version', $item );
	}
}
