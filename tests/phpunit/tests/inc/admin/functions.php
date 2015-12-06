<?php
/**
 * Test Envato Market Functions.
 *
 * @package Envato_Market
 * @group functions
 */

/**
 * Class Tests_Envato_Market_Functions
 */
class Tests_Envato_Market_Functions extends WP_UnitTestCase {

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
	 * Themes column is empty
	 */
	function test_envato_market_themes_column_empty() {
		ob_start();
		envato_market_themes_column();
		$contents = ob_get_clean();
		$this->assertEmpty( $contents );
	}

	/**
	 * Themes column active
	 */
	function test_envato_market_themes_column_active() {
		$themes = array(
			'active' => array(
				array(
					'id' => 12345,
					'name' => 'Twenty Fifteen',
					'author' => 'the WordPress team',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/twentyfifteen/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, $themes );

		ob_start();
		envato_market_themes_column( 'active' );
		$contents = ob_get_clean();
		$this->assertContains( 'Customize &#8220;Twenty Fifteen&#8221;', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Themes column installed
	 */
	function test_envato_market_themes_column_installed() {
		$themes = array(
			'installed' => array(
				array(
					'id' => 12345,
					'name' => 'Twenty Fifteen',
					'author' => 'the WordPress team',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/twentyfifteen/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, $themes );

		ob_start();
		envato_market_themes_column( 'installed' );
		$contents = ob_get_clean();
		$this->assertContains( 'Activate &#8220;Twenty Fifteen&#8221;', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Themes column install
	 */
	function test_envato_market_themes_column_install() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Cannot properly test envato_market_themes_column() install if on multisite.' );
		}

		$themes = array(
			'install' => array(
				array(
					'id' => 12345,
					'name' => 'Twenty Fifteen',
					'author' => 'the WordPress team',
					'version' => '10.0.0',
					'description' => '',
					'url' => 'http://sample.org/twentyfifteen/',
					'author_url' => 'http://sample.org/',
					'thumbnail_url' => 'http://sample.org/thumb.png',
					'rating' => array(
						'rating' => 4.79,
						'count' => 4457,
					),
				),
			),
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, $themes );

		ob_start();
		envato_market_themes_column( 'install' );
		$contents = ob_get_clean();
		$this->assertContains( 'Install Twenty Fifteen', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'themes' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Plugins column is empty
	 */
	function test_envato_market_plugins_column_empty() {
		ob_start();
		envato_market_plugins_column();
		$contents = ob_get_clean();
		$this->assertEmpty( $contents );
	}

	/**
	 * Plugins column active
	 */
	function test_envato_market_plugins_column_active() {
		$plugins = array(
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
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, $plugins );

		ob_start();
		envato_market_plugins_column( 'active' );
		$contents = ob_get_clean();
		$this->assertContains( 'Deactivate Envato Market', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Plugins column installed
	 */
	function test_envato_market_plugins_column_installed() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Cannot properly test envato_market_plugins_column() delete if on multisite.' );
		}

		$plugins = array(
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
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, $plugins );

		ob_start();
		envato_market_plugins_column( 'installed' );
		$contents = ob_get_clean();
		$this->assertContains( 'Delete Envato Market', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Plugins column install
	 */
	function test_envato_market_plugins_column_install() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Cannot properly test envato_market_plugins_column() install if on multisite.' );
		}

		$plugins = array(
			'install' => array(
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
		);

		// Replace private themes reference
		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, $plugins );

		ob_start();
		envato_market_plugins_column( 'install' );
		$contents = ob_get_clean();
		$this->assertContains( 'Install Envato Market', $contents );

		$ref = new ReflectionProperty( 'Envato_Market_Items', 'plugins' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}
}
