<?php
/**
 * Plugin Name: Envato Market
 * Plugin URI: https://envato.com/market-plugin/
 * Description: WordPress Theme & Plugin management for the Envato Market.
 * Version: 2.0.4
 * Author: Envato
 * Author URI: https://envato.com
 * Requires at least: 5.1
 * Tested up to: 5.5
 * Text Domain: envato-market
 * Domain Path: /languages/
 *
 * @package Envato_Market
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/* Set plugin version constant. */
define( 'ENVATO_MARKET_VERSION', '2.0.4' );

/* Debug output control. */
define( 'ENVATO_MARKET_DEBUG_OUTPUT', 0 );

/* Set constant path to the plugin directory. */
define( 'ENVATO_MARKET_SLUG', basename( plugin_dir_path( __FILE__ ) ) );

/* Set constant path to the main file for activation call */
define( 'ENVATO_MARKET_CORE_FILE', __FILE__ );

/* Set constant path to the plugin directory. */
define( 'ENVATO_MARKET_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

/* Set the constant path to the plugin directory URI. */
define( 'ENVATO_MARKET_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );


if ( ! version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	add_action( 'admin_notices', 'envato_market_fail_php_version' );
} elseif ( ENVATO_MARKET_SLUG !== 'envato-market' ) {
	add_action( 'admin_notices', 'envato_market_fail_installation_method' );
} else {

	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		// Makes sure the plugin functions are defined before trying to use them.
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	define( 'ENVATO_MARKET_NETWORK_ACTIVATED', is_plugin_active_for_network( ENVATO_MARKET_SLUG . '/envato-market.php' ) );

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
	 * This doesn't fire the activation hook correctly if done in 'after_setup_theme' hook.
	 *
	 * @since 1.0.0
	 * @return object Envato_Market
	 */
	envato_market();

}

if ( ! function_exists( 'envato_market_fail_php_version' ) ) {

	/**
	 * Show in WP Dashboard notice about the plugin is not activated.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function envato_market_fail_php_version() {
		$message      = esc_html__( 'The Envato Market plugin requires PHP version 5.4+, plugin is currently NOT ACTIVE. Please contact the hosting provider to upgrade the version of PHP.', 'envato-market' );
		$html_message = sprintf( '<div class="notice notice-error">%s</div>', wpautop( $message ) );
		echo wp_kses_post( $html_message );
	}
}



if ( ! function_exists( 'envato_market_fail_installation_method' ) ) {

	/**
	 * The plugin needs to be installed into the `envato-market/` folder otherwise it will not work correctly.
	 * This alert will display if someone has installed it into the incorrect folder (i.e. github download zip).
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	function envato_market_fail_installation_method() {
		$message      = sprintf( esc_html__( 'Envato Market plugin is not installed correctly. Please delete this plugin and get the correct zip file from %s.', 'envato-market' ), '<a href="https://envato.com/market-plugin/" target="_blank">https://envato.com/market-plugin/</a>' );
		$html_message = sprintf( '<div class="notice notice-error">%s</div>', wpautop( $message ) );
		echo wp_kses_post( $html_message );
	}
}

