<?php
/**
 * Test Envato Market API class.
 *
 * @package Envato_Market
 * @group functions
 */

/**
 * Class Tests_Envato_Market_API_Class
 */
class Tests_Envato_Market_API_Class extends WP_UnitTestCase {

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
}
