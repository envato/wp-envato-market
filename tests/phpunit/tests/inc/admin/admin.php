<?php
/**
 * Test Envato Market Admin UI class.
 *
 * @package Envato_Market
 * @group functions
 */

/**
 * Class Tests_Envato_Market_Admin_Class
 */
class Tests_Envato_Market_Admin_Class extends WP_UnitTestCase {

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
	}

	/**
	 * Add a font based menu icon
	 */
	function test_add_menu_icon() {
		ob_start();
		envato_market()->admin()->add_menu_icon();
		$contents = ob_get_clean();
		$this->assertContains( '<style type="text/css">', $contents );
	}

	/**
	 * Enqueue admin css
	 */
	function test_admin_enqueue_script() {
		envato_market()->admin()->admin_enqueue_script();
		$this->assertTrue( wp_script_is( envato_market()->get_slug(), 'enqueued' ) );
		$this->assertTrue( wp_script_is( envato_market()->get_slug() . '-updates', 'enqueued' ) );
	}

	/**
	 * Enqueue admin css
	 */
	function test_admin_enqueue_style() {
		envato_market()->admin()->admin_enqueue_style();
		$this->assertTrue( wp_style_is( envato_market()->get_slug(), 'enqueued' ) );
	}

	/**
	 * Check authorization error notice
	 */
	function test_add_notices_error() {
		update_option( envato_market()->get_option_name(), array( 'authorization' => 'failed' ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		envato_market()->admin()->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}

	/**
	 * Check authorization success notice
	 */
	function test_add_notices_success() {
		update_option( envato_market()->get_option_name(), array( 'authorization' => 'passed' ) );
		set_current_screen( 'toplevel_page_' . envato_market()->get_slug() );
		envato_market()->admin()->add_notices();
		ob_start();
		do_action( 'admin_notices' );
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Check authorization failed
	 */
	function test_authorization_failed() {
		envato_market()->api()->token = '12345';
		envato_market()->admin()->authorization();
		$this->assertEquals( 'failed', envato_market()->get_option( 'authorization' ) );
	}

	/**
	 * Check authorization failed from WP_Error
	 */
	function test_authorization_failed_by_wp_error() {
		envato_market()->api()->url = 'https://';
		envato_market()->api()->token = '12345';
		envato_market()->admin()->authorization();
		$this->assertEquals( 'failed', envato_market()->get_option( 'authorization' ) );
		envato_market()->api()->url = 'https://api.envato.com/v1/market/';
	}

	/**
	 * Check authorization passed
	 */
	function test_authorization_passed() {
		if ( '' !== TOKEN ) {
			envato_market()->api()->token = TOKEN;
			envato_market()->admin()->authorization();
			$this->assertEquals( 'passed', envato_market()->get_option( 'authorization' ) );
		}
	}

	/**
	 * Render admin callback
	 */
	function test_render_admin_callback() {
		do_action( 'admin_menu' );
		do_action( 'admin_init' );
		ob_start();
		envato_market()->admin()->render_admin_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="wrap about-wrap">', $contents );
		$this->assertContains( '<form method="POST" action="options.php"', $contents );
	}

	/**
	 * Render OAuth section callback
	 */
	function test_render_oauth_section_callback() {
		ob_start();
		envato_market()->admin()->render_oauth_section_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">generate a personal token</a>', $contents );
	}

	/**
	 * Render Items section callback
	 */
	function test_render_items_section_callback() {
		ob_start();
		envato_market()->admin()->render_items_section_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<p>', $contents );
		$this->assertContains( '</p>', $contents );
	}

	/**
	 * Render Token setting callback
	 */
	function test_render_token_setting_callback() {
		ob_start();
		envato_market()->admin()->render_token_setting_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<input type="text" name="' . envato_market()->get_option_name() . '[token]"', $contents );
	}

	/**
	 * Render Items setting callback
	 */
	function test_render_items_setting_callback() {
		ob_start();
		envato_market()->admin()->render_items_setting_callback();
		$contents = ob_get_clean();
		$this->assertContains( '<ul id="envato-market-items">', $contents );
	}

	/**
	 * Render Intro partial
	 */
	function test_render_intro_partial() {
		ob_start();
		envato_market()->admin()->render_intro_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<h1 class="about-title"><strong>Envato Market</strong> <sup>' . envato_market()->get_version() . '</sup></h1>', $contents );
	}

	/**
	 * Render Tabs partial
	 */
	function test_render_tabs_partial() {
		ob_start();
		envato_market()->admin()->render_tabs_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<a href="#settings"', $contents );
	}

	/**
	 * Render Settings Panel partial
	 */
	function test_render_settings_panel_partial() {
		ob_start();
		envato_market()->admin()->render_settings_panel_partial();
		$contents = ob_get_clean();
		$this->assertContains( '<div id="settings" class="two-col panel">', $contents );
	}

	/**
	 * Render Success notice
	 */
	function test_render_success_notice() {
		ob_start();
		envato_market()->admin()->render_success_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-success is-dismissible">', $contents );
	}

	/**
	 * Render Error notice
	 */
	function test_render_error_notice() {
		ob_start();
		envato_market()->admin()->render_error_notice();
		$contents = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error is-dismissible">', $contents );
	}
}
