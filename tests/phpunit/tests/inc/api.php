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
		$this->api->token = '';
		$this->assertInstanceOf( 'WP_Error', $this->api->request( 'https://api.envato.com/v1/market/total-items.json' ) );

		$this->api->token = '12345';
		$this->assertInstanceOf( 'WP_Error', $this->api->request( 'https://api.envato.com/v1/market/total-items.json' ) );
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
			$this->assertArrayHasKey( 'total-items', $this->api->request( 'https://api.envato.com/v1/market/total-items.json', $args ) );
		}
	}

	/**
	 * Builds a deferred download url.
	 */
	function test_deferred_download() {
		$dowload = $this->api->deferred_download( 12345 );
		$this->assertContains( 'deferred_download=1', $dowload );
		$this->assertContains( 'item_id=12345', $dowload );
	}

	/**
	 * @see Envato_Market_API::deferred_download()
	 */
	function test_deferred_download_empty() {
		$this->assertEmpty( $this->api->deferred_download( false ) );
		$this->assertEmpty( $this->api->deferred_download( 0 ) );
	}

	/**
	 * @see Envato_Market_API::download()
	 */
	function test_download_empty_id() {
		$this->assertFalse( $this->api->download( false ) );
		$this->assertFalse( $this->api->download( 0 ) );
	}

	/**
	 * @see Envato_Market_API::download()
	 */
	function test_download_error() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( new WP_Error( 'broken' ) ) );

		$this->assertFalse( $mock->download( 1234 ) );
	}

	/**
	 * @see Envato_Market_API::download()
	 */
	function test_download_false_from_error_message() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( 'Requests failed' ) );

		$this->assertFalse( $mock->download( 1234 ) );
	}

	/**
	 * Builds a theme download url
	 */
	function test_download_theme() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( array( 'wordpress_theme' => 'http://sample.org/download/12345.zip' ) ) );

		$this->assertEquals( 'http://sample.org/download/12345.zip', $mock->download( 12345 ) );
	}

	/**
	 * Builds a plugin download url
	 */
	function test_download_plugin() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( array( 'wordpress_plugin' => 'http://sample.org/download/12345.zip' ) ) );

		$this->assertEquals( 'http://sample.org/download/12345.zip', $mock->download( 12345 ) );
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

		$item = $mock->item( 12345 );
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

		$item = $mock->item( 12345 );
		$this->assertFalse( $item );
	}

	/**
	 * @see Envato_Market_API::item()
	 */
	function test_item_false_from_error_message() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( 'Requests failed' ) );

		$item = $mock->item( 12345 );
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

	/**
	 * @see Envato_Market_API::themes()
	 */
	function test_themes() {
		$contents = file_get_contents( TESTS_DATA_DIR . '/themes.json' );
		$json = json_decode( $contents, true );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( $json ) );

		$items = $mock->themes( 548199 );
		$item = array_shift( $items );
		$this->assertArrayHasKey( 'id', $item );
		$this->assertArrayHasKey( 'name', $item );
		$this->assertArrayHasKey( 'author', $item );
		$this->assertArrayHasKey( 'version', $item );
	}

	/**
	 * @see Envato_Market_API::plugins()
	 */
	function test_plugins() {
		$contents = file_get_contents( TESTS_DATA_DIR . '/plugins.json' );
		$json = json_decode( $contents, true );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( $json ) );

		$items = $mock->plugins( 2751380 );
		$item = array_shift( $items );
		$this->assertArrayHasKey( 'id', $item );
		$this->assertArrayHasKey( 'name', $item );
		$this->assertArrayHasKey( 'author', $item );
		$this->assertArrayHasKey( 'version', $item );
	}
}
