<?php
/**
 * Plugin Name: Envato Market
 * Plugin URI: http://envato.github.io/wp-envato-market/
 * Description: WordPress Theme & Plugin management for the Envato Market.
 * Version: 1.0.1
 * Author: Envato
 * Author URI: https://github.com/envato/wp-envato-market
 * Requires at least: 4.9
 * Tested up to: 4.9.4
 * Text Domain: envato-market
 * Domain Path: /languages/
 *
 * @package Envato_Market
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/* Set plugin version constant. */
define( 'ENVATO_MARKET_VERSION', '1.0.1' );

/* Debug output control. */
define( 'ENVATO_MARKET_DEBUG_OUTPUT', 0 );

/* Set constant path to the plugin directory. */
define( 'ENVATO_MARKET_SLUG', basename( plugin_dir_path( __FILE__ ) ) );

/* Set constant path to the plugin directory. */
define( 'ENVATO_MARKET_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

/* Set the constant path to the plugin directory URI. */
define( 'ENVATO_MARKET_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );


if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	add_action( 'admin_notices', 'envato_market_fail_php_version' );
} else {

	/* Envato_Market Class */
	require_once ENVATO_MARKET_PATH . 'inc/class-envato-market.php';


	if ( ! function_exists( 'envato_market' ) ) :
		/**
		 * The main function responsible for returning the one true
		 * Envato_Market Instance to functions everywhere.
		 *
		 * Use this function like you would a global variable, except
		 * without needing to declare the global.
		 *
		 * Example: <?php $envato_market = envato_market(); ?>
		 *
		 * @since 1.0.0
		 * @return Envato_Market The one true Envato_Market Instance
		 */
		function envato_market() {
			return Envato_Market::instance();
		}
	endif;

	/**
	 * Loads the main instance of Envato_Market to prevent
	 * the need to use globals.
	 *
	 * @since 1.0.0
	 * @return object Envato_Market
	 */
	add_action( 'after_setup_theme', 'envato_market', 11 );

}

if ( ! function_exists( 'envato_market_fail_php_version' ) ) {

	/**
	 * Show in WP Dashboard notice about the plugin is not activated.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function envato_market_fail_php_version() {
		$message      = esc_html__( 'The Envato Market plugin requires PHP version 5.4+, plugin is currently NOT ACTIVE.', 'envato-market' );
		$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
		echo wp_kses_post( $html_message );
	}
}


