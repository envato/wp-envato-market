<?php
/**
 * Test Envato Market Admin UI class.
 *
 * @package Envato_Market
 * @group functions
 */

/**
 * Class Tests_Envato_Market_Admin
 */
class Tests_Envato_Market_Admin extends WP_UnitTestCase {

	/**
	 * @var Envato_Market_Admin
	 */
	public $admin;

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		$this->admin = envato_market()->admin();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * @see Envato_Market_Admin::init_actions()
	 */
	function test_init_actions() {
		$this->admin->init_actions();
		$this->assertEquals( 99, has_action( 'upgrader_package_options', array( $this->admin, 'maybe_deferred_download' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_upgrade-theme', array( $this->admin, 'ajax_upgrade_theme' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_' . Envato_Market_Admin::AJAX_ACTION . '_add_item', array( $this->admin, 'ajax_add_item' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_' . Envato_Market_Admin::AJAX_ACTION . '_remove_item', array( $this->admin, 'ajax_remove_item' ) ) );
		$this->assertEquals( 11, has_action( 'init', array( $this->admin, 'maybe_delete_transients' ) ) );
		$this->assertEquals( 10, has_action( 'admin_head', array( $this->admin, 'add_menu_icon' ) ) );
		$this->assertEquals( 10, has_action( 'admin_menu', array( $this->admin, 'add_menu_page' ) ) );
		$this->assertEquals( 10, has_action( 'admin_init', array( $this->admin, 'register_settings' ) ) );
		$this->assertEquals( 10, has_action( 'current_screen', array( $this->admin, 'maybe_redirect' ) ) );
		$this->assertEquals( 10, has_action( 'current_screen', array( $this->admin, 'add_notices' ) ) );
		$this->assertEquals( 10, has_action( 'current_screen', array( $this->admin, 'set_items' ) ) );
	}

	/**
	 * @see Envato_Market_Admin::maybe_deferred_download()
	 */
	function test_maybe_deferred_download() {
		$options = envato_market()->get_options();

		update_option(
			envato_market()->get_option_name(),
			array(
				'items' => array(
					array(
						'name'       => 'Envato Market',
						'token'      => 'TOKEN12345',
						'id'         => 12345,
						'type'       => 'plugin',
						'authorized' => 'success',
					),
				),
			)
		);

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'download' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'download' )
			->will( $this->returnValue( 'http://sample.org/?download=it' ) );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$options = array(
			'package' => 'http://sample.org/?deferred_download=1&item_id=12345',
		);
		$expected = array(
			'package' => 'http://sample.org/?download=it',
		);
		$this->assertEquals( $expected, $this->admin->maybe_deferred_download( $options ) );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );

		update_option( envato_market()->get_option_name(), $options );
	}

	/**
	 * @see Envato_Market_Admin::set_bearer_args()
	 */
	function test_set_bearer_args() {
		$options = envato_market()->get_options();

		update_option(
			envato_market()->get_option_name(),
			array(
				'items' => array(
					array(
						'name'       => 'Envato Market',
						'token'      => 'TOKEN12345',
						'id'         => 12345,
						'type'       => 'plugin',
						'authorized' => 'success',
					),
				),
			)
		);

		$expected = array(
			'headers' => array(
				'Authorization' => 'Bearer TOKEN12345',
			),
		);
		$this->assertEquals( $expected, $this->admin->set_bearer_args( 12345 ) );
		$this->assertEquals( array(), $this->admin->set_bearer_args( 12346 ) );

		update_option( envato_market()->get_option_name(), $options );
	}

	/**
	 * Add a font based menu icon
	 */
	function test_add_menu_icon() {
		ob_start();
		$this->admin->add_menu_icon();
		$contents = ob_get_clean();
		$this->assertContains( '<style type="text/css">', $contents );
	}

	/**
	 * Enqueue admin css
	 */
	function test_admin_enqueue_script() {
		$this->admin->admin_enqueue_script();
		$this->assertTrue( wp_script_is( envato_market()->get_slug(), 'enqueued' ) );
		$this->assertTrue( wp_script_is( envato_market()->get_slug() . '-updates', 'enqueued' ) );
	}

	/**
	 * Enqueue admin css
	 */
	function test_admin_enqueue_style() {
		$this->admin->admin_enqueue_style();
		$this->assertTrue( wp_style_is( envato_market()->get_slug(), 'enqueued' ) );
	}

	/**
	 * Check authorization success notice
	 */
	function test_add_notices_success() {
		update_option( envato_market()->get_option_name(), array( 'notices' => array( 'success' ) ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		$this->admin->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Check authorization success no-items notice
	 */
	function test_add_notices_success_no_items() {
		update_option( envato_market()->get_option_name(), array( 'notices' => array( 'success-no-items' ) ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		$this->admin->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Check authorization success single-use notice
	 */
	function test_add_notices_success_single_use() {
		update_option( envato_market()->get_option_name(), array( 'notices' => array( 'success-single-use' ) ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		$this->admin->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Check authorization error notice
	 */
	function test_add_notices_error() {
		update_option( envato_market()->get_option_name(), array( 'notices' => array( 'error' ) ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		$this->admin->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}

	/**
	 * Check authorization error single-use notice
	 */
	function test_add_notices_error_single_use() {
		update_option( envato_market()->get_option_name(), array( 'notices' => array( 'error-single-use' ) ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		$this->admin->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}

	/**
	 * Check authorization total items error
	 */
	function test_authorization_total_items_error() {
		envato_market()->api()->token = '12345';
		$this->admin->authorization();
		$this->assertEquals( array( 'error' ), envato_market()->get_option( 'notices' ) );
	}

	/**
	 * Check authorization themes error
	 */
	function test_authorization_themes_error() {
		envato_market()->api()->token = '12345';
		
		$mock = $this->getMockBuilder( 'Envato_Market_Admin' )
			->setMethods( array( 'authorize_total_items' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'authorize_total_items' )
			->will( $this->returnValue( 'success' ) );

		$mock->authorization();
		$this->assertEquals( array( 'error' ), envato_market()->get_option( 'notices' ) );
	}

	/**
	 * Check authorization plugins error
	 */
	function test_authorization_plugins_error() {
		envato_market()->api()->token = '12345';

		$mock = $this->getMockBuilder( 'Envato_Market_Admin' )
			->setMethods( array( 'authorize_total_items', 'authorize_themes' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'authorize_total_items' )
			->will( $this->returnValue( 'success' ) );

		$mock->expects( $this->any() )
			->method( 'authorize_themes' )
			->will( $this->returnValue( 'success' ) );

		$mock->authorization();
		$this->assertEquals( array( 'error' ), envato_market()->get_option( 'notices' ) );
	}

	/**
	 * Check authorization success but no items
	 */
	function test_authorization_success_no_items() {
		envato_market()->api()->token = '12345';

		$mock = $this->getMockBuilder( 'Envato_Market_Admin' )
			->setMethods( array( 'authorize_total_items', 'authorize_themes', 'authorize_plugins' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'authorize_total_items' )
			->will( $this->returnValue( 'success' ) );

		$mock->expects( $this->any() )
			->method( 'authorize_themes' )
			->will( $this->returnValue( 'success-no-themes' ) );

		$mock->expects( $this->any() )
			->method( 'authorize_plugins' )
			->will( $this->returnValue( 'success-no-plugins' ) );

		$mock->authorization();
		$this->assertEquals( array( 'success-no-items' ), envato_market()->get_option( 'notices' ) );
	}

	/**
	 * Check authorization passed
	 */
	function test_authorization_success() {
		envato_market()->api()->token = '12345';

		$mock = $this->getMockBuilder( 'Envato_Market_Admin' )
			->setMethods( array( 'authorize_total_items', 'authorize_themes', 'authorize_plugins' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'authorize_total_items' )
			->will( $this->returnValue( 'success' ) );

		$mock->expects( $this->any() )
			->method( 'authorize_themes' )
			->will( $this->returnValue( 'success' ) );

		$mock->expects( $this->any() )
			->method( 'authorize_plugins' )
			->will( $this->returnValue( 'success' ) );

		$mock->authorization();
		$this->assertEquals( array( 'success' ), envato_market()->get_option( 'notices' ) );
	}

	/**
	 * Check authorization success without items
	 */
	function test_authorize_items_themes_success_no_results() {
		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( array( 'results' => array() ) ) );

		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$this->assertEquals( 'success-no-themes', $this->admin->authorize_items( 'themes' ) );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * Check authorization fails when download is empty
	 */
	function test_authorize_items_themes_error_no_download() {
		$contents = file_get_contents( TESTS_DATA_DIR . '/themes.json' );
		$json = json_decode( $contents, true );

		$mock = $this->getMockBuilder( 'Envato_Market_API' )
			->setMethods( array( 'request', 'download' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'request' )
			->will( $this->returnValue( $json ) );

		$mock->expects( $this->any() )
			->method( 'download' )
			->will( $this->returnValue( false ) );
			
		// Replace private _instance reference with mock object
		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, $mock );

		$this->assertEquals( 'error', $this->admin->authorize_items( 'themes' ) );

		$ref = new ReflectionProperty( 'Envato_Market_API', '_instance' );
		$ref->setAccessible( true );
		$ref->setValue( null, null );
	}

	/**
	 * Render admin callback
	 */
	function test_render_admin_callback() {
		do_action( 'admin_menu' );
		do_action( 'admin_init' );
		ob_start();
		$this->admin->render_admin_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="wrap about-wrap">', $contents );
		$this->assertContains( '<form method="POST" action="options.php"', $contents );
	}

	/**
	 * Render OAuth section callback
	 */
	function test_render_oauth_section_callback() {
		ob_start();
		$this->admin->render_oauth_section_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">generate a personal token</a>', $contents );
	}

	/**
	 * Render Items section callback
	 */
	function test_render_items_section_callback() {
		ob_start();
		$this->admin->render_items_section_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<p>', $contents );
		$this->assertContains( '</p>', $contents );
	}

	/**
	 * Render Token setting callback
	 */
	function test_render_token_setting_callback() {
		ob_start();
		$this->admin->render_token_setting_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<input type="text" name="' . envato_market()->get_option_name() . '[token]"', $contents );
	}

	/**
	 * Render Items setting callback
	 */
	function test_render_items_setting_callback() {
		ob_start();
		$this->admin->render_items_setting_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<ul id="envato-market-items">', $contents );
	}

	/**
	 * Render Intro partial
	 */
	function test_render_intro_partial() {
		ob_start();
		$this->admin->render_intro_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<h1 class="about-title"><strong>Envato Market</strong> <sup>' . envato_market()->get_version() . '</sup></h1>', $contents );
	}

	/**
	 * Render Tabs partial
	 */
	function test_render_tabs_partial() {
		ob_start();
		$this->admin->render_tabs_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<a href="#settings"', $contents );
	}

	/**
	 * Render Settings Panel partial
	 */
	function test_render_settings_panel_partial() {
		ob_start();
		$this->admin->render_settings_panel_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<div id="settings" class="two-col panel">', $contents );
	}

	/**
	 * Render Success notice
	 */
	function test_render_success_notice() {
		ob_start();
		$this->admin->render_success_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Render Success no-items notice
	 */
	function test_render_success_no_items_notice() {
		ob_start();
		$this->admin->render_success_no_items_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Render Success single-use notice
	 */
	function test_render_success_single_use_notice() {
		ob_start();
		$this->admin->render_success_single_use_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Render Error notice
	 */
	function test_render_error_notice() {
		ob_start();
		$this->admin->render_error_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}

	/**
	 * Render Error single-use notice
	 */
	function test_render_error_single_use_notice() {
		ob_start();
		$this->admin->render_error_single_use_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}
}
