<?php
/**
 * Test Envato Market class.
 *
 * @package Envato_Market
 * @group base
 */

/**
 * Class Tests_Envato_Market
 */
class Tests_Envato_Market extends WP_UnitTestCase {

	/**
	 * The plugin metadata.
	 *
	 * @var array
	 */
	public $plugin_data;

	/**
	 * Set up a test case.
	 *
	 * @see WP_UnitTestCase::setup()
	 */
	function setUp() {
		parent::setUp();
		$path = str_replace( 'tests/phpunit/tests/', '', plugin_dir_path( __FILE__ ) ) . 'envato-market.php';
		$this->plugin_data = get_plugin_data( $path, $markup = false, $translate = false );
	}

	/**
	 * Check that `Envato_Market::activate` works.
	 */
	function test_envato_market_activate() {
		do_action( 'activate_envato-market/envato-market.php' );
		$this->assertEquals( 1, envato_market()->get_option( 'is_plugin_active' ) );
	}

	/**
	 * Check that `Envato_Market::deactivate` works.
	 */
	function test_envato_market_deactivate() {
		do_action( 'deactivate_envato-market/envato-market.php' );
		$this->assertEquals( '', envato_market()->get_option( 'is_plugin_active' ) );
	}

	/**
	 * Check for get data.
	 */
	function test_envato_market_get_data() {
		$this->assertEquals( '', envato_market()->get_data( 'test_key' ) );
	}

	/**
	 * Check that data is set to a string.
	 */
	function test_envato_market_set_data_string() {
		envato_market()->set_data( 'test_key', '1234567' );
		$this->assertEquals( '1234567', envato_market()->get_data( 'test_key' ) );
	}

	/**
	 * Check that array is converted to object.
	 */
	function test_envato_market_set_data_array() {
		envato_market()->set_data( 'test_key', array( 'sub_key' => '1234567' ) );
		$this->assertEquals( '1234567', envato_market()->get_data( 'test_key' )->sub_key );
	}

	/**
	 * Check that array is converted to object recursively.
	 */
	function test_envato_market_set_data_arrays() {
		$data = array(
			'post' => array(
				'post_id' => 20,
				'post_title' => 'Hello',
				'post_type' => 'post',
			),
		);
		envato_market()->set_data( 'test_key', $data );
		$this->assertEquals( 'Hello', envato_market()->get_data( 'test_key' )->post->post_title );
	}

	/**
	 * Check for correct slug global.
	 */
	function test_envato_market_get_slug() {
		$this->assertEquals( 'envato-market', envato_market()->get_slug() );
	}

	/**
	 * Check for correct version global.
	 */
	function test_envato_market_get_version() {
		$this->assertEquals( $this->plugin_data['Version'], envato_market()->get_version() );
	}

	/**
	 * Check for correct version plugin URL.
	 */
	function test_envato_market_get_plugin_url() {
		$this->assertEquals( str_replace( 'tests/phpunit/tests/', '', plugin_dir_url( __FILE__ ) ), envato_market()->get_plugin_url() );
	}

	/**
	 * Check for correct version plugin path.
	 */
	function test_envato_market_get_plugin_path() {
		$this->assertEquals( str_replace( 'tests/phpunit/tests/', '', plugin_dir_path( __FILE__ ) ), envato_market()->get_plugin_path() );
	}

	/**
	 * Check for correct plugin page URL.
	 */
	function test_envato_market_get_page_url() {
		$this->assertEquals( admin_url( 'admin.php?page=' . envato_market()->get_slug() ), envato_market()->get_page_url() );
	}

	/**
	 * Check for correct option name.
	 */
	function test_envato_market_get_option_name() {
		$option_name = preg_replace( '/[^A-Za-z0-9\_]/i', '', str_replace( array( '-', ':' ), '_', envato_market()->get_slug() ) );
		$this->assertEquals( $option_name, envato_market()->get_option_name() );
	}

	/**
	 * Check for admin class.
	 */
	function test_envato_market_admin() {
		$this->assertInstanceOf( 'Envato_Market_Admin', envato_market()->admin() );
	}
}
