<?php
/**
 * Test Envato Market Github class.
 *
 * @package Envato_Market
 * @group github
 */

/**
 * Class Tests_Envato_Market_Github
 */
class Tests_Envato_Market_Github extends WP_UnitTestCase {

	/**
	 * @var Envato_Market_Github
	 */
	public $github;

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		$this->github = envato_market_github();
	}

	/**
	 * @see envato_market_github()
	 */
	function test_envato_market_github() {
		$this->assertInstanceOf( 'Envato_Market_Github', $this->github );
	}

	/**
	 * @see Envato_Market_Github::init_actions()
	 */
	function test_init_actions() {
		$this->github->init_actions();
		$this->assertEquals( 5, has_action( 'http_request_args', array( $this->github, 'update_check' ) ) );
		$this->assertEquals( 10, has_action( 'plugins_api', array( $this->github, 'plugins_api' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_site_transient_update_plugins', array( $this->github, 'update_plugins' ) ) );
		$this->assertEquals( 10, has_action( 'pre_set_transient_update_plugins', array( $this->github, 'update_plugins' ) ) );
		$this->assertEquals( 10, has_action( 'site_transient_update_plugins', array( $this->github, 'update_state' ) ) );
		$this->assertEquals( 10, has_action( 'transient_update_plugins', array( $this->github, 'update_state' ) ) );
		$this->assertEquals( 10, has_action( 'admin_notices', array( $this->github, 'notice' ) ) );
	}

	/**
	 * @see Envato_Market_Github::api_check()
	 */
	function test_api_check() {
		$response = $this->github->api_check();
		$this->assertObjectHasAttribute( 'name', $response );
		$this->assertObjectHasAttribute( 'slug', $response );
		$this->assertObjectHasAttribute( 'version', $response );
		$this->assertEquals( 'envato-market', $response->slug );

		// Replace private api_url reference
		$ref = new ReflectionProperty( 'Envato_Market_Github', 'api_url' );
		$ref->setAccessible( true );
		$ref->setValue( null, 'update-check.json' );

		$response = $this->github->api_check();
		$this->assertFalse( $response );
		
		$ref = new ReflectionProperty( 'Envato_Market_Github', 'api_url' );
		$ref->setAccessible( true );
		$ref->setValue( null, 'http://envato.github.io/wp-envato-market/dist/update-check.json' );
	}

	/**
	 * @see Envato_Market_Github::plugins_api()
	 */
	function test_plugins_api() {
		$api = false;
		$action = 'update';
		$args = new stdClass();
		$this->assertFalse( $this->github->plugins_api( $api, $action, $args ) );
		$args->slug = 'envato-market';
		$this->assertObjectHasAttribute( 'slug', $this->github->plugins_api( $api, $action, $args ) );
	}

	/**
	 * @see Envato_Market_Github::update_plugins()
	 */
	function test_update_plugins_failed() {
		$transient = new stdClass();

		$mock = $this->getMockBuilder( 'Envato_Market_Github' )
			->setMethods( array( 'state' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'state' )
			->will( $this->returnValue( 'activated' ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_Github', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$this->assertEquals( 'activated', $mock->state() );
		$this->assertEquals( $transient, $this->github->update_plugins( $transient ) );

		$ref = new ReflectionProperty( 'Envato_Market_Github', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * @see Envato_Market_Github::update_plugins()
	 */
	function test_update_plugins() {
		$transient = new stdClass();
		$api = new stdClass();
		$api->name = 'Envato Market';
		$api->slug = 'envato-market';
		$api->version = '10.0.0';
		$api->download_link = 'https://envato.github.io/wp-envato-market/dist/envato-market.zip';

		$this->assertEquals( $transient, $this->github->update_plugins( $transient ) );

		$mock = $this->getMockBuilder( 'Envato_Market_Github' )
			->setMethods( array( 'api_check', 'state' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'api_check' )
			->will( $this->returnValue( $api ) );

		$mock->expects( $this->any() )
			->method( 'state' )
			->will( $this->returnValue( 'activated' ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_Github', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$this->assertEquals( $api, $mock->api_check() );
		$this->assertEquals( 'activated', $mock->state() );

		$transient = $mock->update_plugins( $transient );
		$this->assertObjectHasAttribute( 'slug', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'plugin', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'new_version', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'url', $transient->response['envato-market/envato-market.php'] );
		$this->assertObjectHasAttribute( 'package', $transient->response['envato-market/envato-market.php'] );
		$this->assertEquals( '10.0.0', $transient->response['envato-market/envato-market.php']->new_version );

		$ref = new ReflectionProperty( 'Envato_Market_Github', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * @see Envato_Market_Github::notice()
	 */
	function test_notice_activated() {
		update_option( 'envato_market_state', 'activated' );
		ob_start();
		$this->github->notice();
		$buffer = ob_get_clean();
		$this->assertEmpty( $buffer );
	}

	/**
	 * @see Envato_Market_Github::notice()
	 */
	function test_notice_deactivated() {
		update_option( 'envato_market_state', 'deactivated' );
		ob_start();
		set_current_screen( 'plugins' );
		$this->github->notice();
		$buffer = ob_get_clean();
		$this->assertContains( 'Activate the Envato Market plugin', $buffer );
	}

	/**
	 * @see Envato_Market_Github::notice()
	 */
	function test_notice_install() {
		update_option( 'envato_market_state', 'install' );
		ob_start();
		set_current_screen( 'plugins' );
		$this->github->notice();
		$buffer = ob_get_clean();
		$this->assertContains( 'Install the Envato Market plugin', $buffer );
	}
}
