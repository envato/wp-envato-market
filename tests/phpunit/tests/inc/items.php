<?php
/**
 * Test Envato Market Items class.
 *
 * @package Envato_Market
 * @group items
 */

/**
 * Class Tests_Envato_Market_Items
 */
class Tests_Envato_Market_Items extends WP_UnitTestCase {

	/**
	 * @var Envato_Market_Items
	 */
	public $items;

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		$this->items = envato_market()->items();
	}

	/**
	 * @see Envato_Market::items()
	 */
	function test_envato_market_items() {
		$this->assertInstanceOf( 'Envato_Market_Items', $this->items );
	}

	/**
	 * @see Envato_Market_Items::init_actions()
	 */
	function test_init_actions() {
		$this->items->init_actions();
		$this->assertEquals( 5, has_action( 'http_request_args', array( $this->items, 'update_check' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_site_transient_update_plugins', array( $this->items, 'update_plugins' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_transient_update_plugins', array( $this->items, 'update_plugins' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_site_transient_update_themes', array( $this->items, 'update_themes' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_transient_update_themes', array( $this->items, 'update_themes' ) ) );
		$this->assertEquals( 10, has_action( 'plugins_api', array( $this->items, 'plugins_api' ) ) );
		$this->assertEquals( 10, has_action( 'after_switch_theme', array( $this->items, 'rebuild_themes' ) ) );
		$this->assertEquals( 10, has_action( 'activated_plugin', array( $this->items, 'rebuild_plugins' ) ) );
		$this->assertEquals( 10, has_action( 'deactivated_plugin', array( $this->items, 'rebuild_plugins' ) ) );
	}

	/**
	 * @see Envato_Market_Items::plugins()
	 */
	function test_plugins() {
		$this->assertEmpty( $this->items->plugins( 'invalid' ) );

		// Replace private plugins reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$plugins = $ref->getValue();
		$ref->setValue( null, array() );

		$this->assertEmpty( $this->items->plugins() );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, $plugins ); 
	}

	/**
	 * @see Envato_Market_Items::themes()
	 */
	function test_themes() {
		$this->assertEmpty( $this->items->themes( 'invalid' ) );
		
		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$themes = $ref->getValue();
		$ref->setValue( null, array() );

		$this->assertEmpty( $this->items->themes() );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, $themes );
	}

	/**
	 * @see Envato_Market_Items::update_check()
	 */
	function test_update_check_themes() {
		$url = '//api.wordpress.org/themes/update-check/1.1/';
		$request = array(
			'body' => array( 
				'themes' => '{"active":"twentyfifteen","themes":{"custom":{"Name":"Custom"},"twentyfifteen":{"Name":"Twenty Fifteen"}}}',
			),
		);
		$transient_name = envato_market()->get_option_name() . '_themes';
		$themes = get_site_transient( $transient_name );
		$_themes = $themes;
		$_themes['installed'] = array(
			'custom' => 12345,
		);

		$response = $this->items->update_check( $request, $url );
		$this->assertObjectHasAttribute( 'custom', json_decode( $response['body']['themes'] )->themes );

		set_site_transient( $transient_name, $_themes );
		$response = $this->items->update_check( $request, $url );
		$this->assertFalse( isset( json_decode( $response['body']['themes'] )->themes->custom ) );
		set_site_transient( $transient_name, $themes );
	}

	/**
	 * @see Envato_Market_Items::update_check()
	 */
	function test_update_check_plugins() {
		$url = '//api.wordpress.org/plugins/update-check/1.1/';
		$request = array(
			'body' => array( 
				'plugins' => '{"plugins":{"custom\/custom.php":{"Name":"Custom"}}}',
			),
		);
		$transient_name = envato_market()->get_option_name() . '_plugins';
		$plugins = get_site_transient( $transient_name );
		$_plugins = $plugins;
		$_plugins['installed'] = array(
			'custom/custom.php' => 12345,
		);

		$response = $this->items->update_check( $request, $url );
		$this->assertObjectHasAttribute( 'custom/custom.php', json_decode( $response['body']['plugins'] )->plugins );

		set_site_transient( $transient_name, $_plugins );
		$response = $this->items->update_check( $request, $url );
		$this->assertEmpty( (array) json_decode( $response['body']['plugins'] )->plugins );
		set_site_transient( $transient_name, $plugins );
	}

	/**
	 * @see Envato_Market_Items::update_themes()
	 */
	function test_update_themes() {
		$_themes = array(
			array(
				'id' => 12345,
				'name' => 'Twenty Fifteen',
				'author' => 'the WordPress team',
				'version' => '10.0.0',
				'description' => '',
				'url' => 'http://sample.org/twentyfifteen/',
				'author_url' => 'http://sample.org/',
				'thumbnail_url' => 'http://sample.org/thumb.png',
				'rating' => '',
			),
		);
		$options = envato_market()->get_options();
		if ( ! isset( $options['items'] ) ) {
			$options['items'] = array();
		}
		$options['items'][] = array(
			'name'       => 'Twenty Fifteen',
			'token'      => 'TOKEN12345',
			'id'         => 12345,
			'type'       => 'theme',
			'authorized' => 'success',
		);
		update_option( envato_market()->get_option_name(), $options );
		$transient = new stdClass();

		$this->assertEquals( $transient, $this->items->update_themes( $transient ) );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'themes', 'normalize_theme', 'item' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'themes' )
			->will( $this->returnValue( $_themes ) );

		$mock->expects( $this->any() )
			->method( 'normalize_theme' )
			->will( $this->returnValue( $_themes[0] ) );

		$mock->expects( $this->any() )
			->method( 'item' )
			->will( $this->returnValue( $_themes[0] ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$transient->checked = true;
		$transient = $this->items->update_themes( $transient );
		$this->assertArrayHasKey( 'theme', $transient->response['twentyfifteen'] );
		$this->assertArrayHasKey( 'new_version', $transient->response['twentyfifteen'] );
		$this->assertArrayHasKey( 'url', $transient->response['twentyfifteen'] );
		$this->assertArrayHasKey( 'package', $transient->response['twentyfifteen'] );
		$this->assertEquals( '10.0.0', $transient->response['twentyfifteen']['new_version'] );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * @see Envato_Market_Items::update_plugins()
	 */
	function test_update_plugins() {
		$_plugins = array(
			array(
				'id' => 12345,
				'name' => 'Envato Market',
				'author' => 'Derek Herman',
				'version' => '10.0.0',
				'description' => '',
				'url' => 'http://sample.org/custom/',
				'author_url' => 'http://sample.org/',
				'thumbnail_url' => 'http://sample.org/thumb.png',
				'landscape_url' => 'http://sample.org/landscape.png',
				'requires' => '4.2',
				'tested' => '4.4',
				'number_of_sales' => 25000,
				'updated_at' => '',
				'rating' => '',
			),
		);
		$options = envato_market()->get_options();
		if ( ! isset( $options['items'] ) ) {
			$options['items'] = array();
		}
		$options['items'][] = array(
			'name'       => 'Envato Market',
			'token'      => 'TOKEN12345',
			'id'         => 12345,
			'type'       => 'plugin',
			'authorized' => 'success',
		);
		update_option( envato_market()->get_option_name(), $options );

		$transient = new stdClass();

		$this->assertEquals( $transient, $this->items->update_plugins( $transient ) );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'plugins', 'normalize_plugin', 'item' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'plugins' )
			->will( $this->returnValue( $_plugins ) );

		$mock->expects( $this->any() )
			->method( 'normalize_plugin' )
			->will( $this->returnValue( $_plugins[0] ) );

		$mock->expects( $this->any() )
			->method( 'item' )
			->will( $this->returnValue( $_plugins[0] ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$transient = $this->items->update_plugins( $transient );
		$this->assertInternalType( 'object', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'slug', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'plugin', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'new_version', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'url', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'package', $transient->response['envato-market/envato-market.php'] );
		$this->assertEquals( '10.0.0', $transient->response['envato-market/envato-market.php']->new_version );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * @see Envato_Market_Items::plugins_api()
	 */
	function test_plugins_api() {
		$_plugins = array(
			array(
				'id' => 12345,
				'name' => 'Envato Market',
				'author' => 'Derek Herman',
				'version' => '10.0.0',
				'description' => '',
				'url' => 'http://sample.org/custom/',
				'author_url' => 'http://sample.org/',
				'thumbnail_url' => 'http://sample.org/thumb.png',
				'landscape_url' => 'http://sample.org/landscape.png',
				'requires' => '4.2',
				'tested' => '4.4',
				'number_of_sales' => 25000,
				'updated_at' => '',
				'rating' => array(
					'rating' => 4.79,
					'count' => 4457,
				),
			),
		);

		$args = new stdClass();
		$this->assertFalse( $this->items->plugins_api( false, 'plugin_information', $args ) );
		$args->slug = 'envato-market';

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'plugins', 'normalize_plugin' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'plugins' )
			->will( $this->returnValue( $_plugins ) );

		$mock->expects( $this->any() )
			->method( 'normalize_plugin' )
			->will( $this->returnValue( $_plugins[0] ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$response = $this->items->plugins_api( false, 'plugin_information', $args );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'slug', $response );
		$this->assertEquals( 'envato-market', $response->slug );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * @see Envato_Market_Items::rebuild_themes()
	 */
	function test_rebuild_themes() {
		delete_site_transient( envato_market()->get_option_name() . '_themes' );
		$this->assertEquals( 'testing', $this->items->rebuild_themes( 'testing' ) );
		$this->assertNotEmpty( get_site_transient( envato_market()->get_option_name() . '_themes' ) );
	}

	/**
	 * @see Envato_Market_Items::rebuild_plugins()
	 */
	function test_rebuild_plugins() {
		$plugins = array(
			'purchased' => array(
				array(
					'id' => 12345,
					'name' => 'Envato Market',
					'author' => 'Derek Herman',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/custom/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'landscape_url' => 'http://sample.org/landscape.png',
					'requires' => '4.2',
					'tested' => '4.4',
					'number_of_sales' => 25000,
					'updated_at' => '',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
			'active' => array(),
			'installed' => array(
				'envato-market/envato-market.php' => array(
					'id' => 12345,
					'name' => 'Envato Market',
					'author' => 'Derek Herman',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/custom/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'landscape_url' => 'http://sample.org/landscape.png',
					'requires' => '4.2',
					'tested' => '4.4',
					'number_of_sales' => 25000,
					'updated_at' => '',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
			'install' => array(),
		);
		
		$expected = array(
			'purchased' => array(
				array(
					'id' => 12345,
					'name' => 'Envato Market',
					'author' => 'Derek Herman',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/custom/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'landscape_url' => 'http://sample.org/landscape.png',
					'requires' => '4.2',
					'tested' => '4.4',
					'number_of_sales' => 25000,
					'updated_at' => '',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
			'active' => array(
				'envato-market/envato-market.php' => array(
					'id' => 12345,
					'name' => 'Envato Market',
					'author' => 'Derek Herman',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/custom/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'landscape_url' => 'http://sample.org/landscape.png',
					'requires' => '4.2',
					'tested' => '4.4',
					'number_of_sales' => 25000,
					'updated_at' => '',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
			'installed' => array(),
			'install' => array(),
		);

		set_site_transient( envato_market()->get_option_name() . '_plugins', $plugins );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, $plugins );

		$this->assertEquals( $plugins, $this->items->plugins() );
		$this->items->rebuild_plugins( 'envato-market/envato-market.php' );
		$this->assertEquals( $expected, $this->items->plugins() );
		
		global $wp_current_filter;
		$wp_current_filter[] = 'deactivated_plugin';
		$this->items->rebuild_plugins( 'envato-market/envato-market.php' );
		$this->assertEquals( $plugins, $this->items->plugins() );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}
}
