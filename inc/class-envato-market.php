<?php
/**
 * Envato Market class.
 *
 * @package Envato_Market
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'Envato_Market' ) ) :

	/**
	 * It's the main class that does all the things.
	 *
	 * @class Envato_Market
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	final class Envato_Market {

		/**
		 * The single class instance.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var object
		 */
		private static $_instance = null;

		/**
		 * Plugin data.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var object
		 */
		private $data;

		/**
		 * The slug.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $slug;

		/**
		 * The version number.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $version;

		/**
		 * The web URL to the plugin directory.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $plugin_url;

		/**
		 * The server path to the plugin directory.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $plugin_path;

		/**
		 * The web URL to the plugin admin page.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $page_url;

		/**
		 * The setting option name.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private $option_name;

		/**
		 * Main Envato_Market Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Envato_Market()
		 * @uses Envato_Market::init_globals() Setup class globals.
		 * @uses Envato_Market::init_includes() Include required files.
		 * @uses Envato_Market::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return Envato_Market.
		 * @codeCoverageIgnore
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::$_instance->init_globals();
				self::$_instance->init_includes();
				self::$_instance->init_actions();
			}
			return self::$_instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Market::instance()
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function __construct() {
			/* We do nothing here! */
		}

		/**
		 * You cannot clone this class.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'envato-market' ), '1.0.0' );
		}

		/**
		 * You cannot unserialize instances of this class.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'envato-market' ), '1.0.0' );
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function init_globals() {
			$this->data        = new stdClass();
			$this->version     = ENVATO_MARKET_VERSION;
			$this->slug        = 'envato-market';
			$this->option_name = self::sanitize_key( $this->slug );
			$this->plugin_url  = ENVATO_MARKET_URI;
			$this->plugin_path = ENVATO_MARKET_PATH;
			$this->page_url    = ENVATO_MARKET_NETWORK_ACTIVATED ? network_admin_url( 'admin.php?page=' . $this->slug ) : admin_url( 'admin.php?page=' . $this->slug );
			$this->data->admin = true;

		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function init_includes() {
			require $this->plugin_path . '/inc/admin/class-envato-market-admin.php';
			require $this->plugin_path . '/inc/admin/functions.php';
			require $this->plugin_path . '/inc/class-envato-market-api.php';
			require $this->plugin_path . '/inc/class-envato-market-items.php';
			require $this->plugin_path . '/inc/class-envato-market-github.php';
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function init_actions() {
			// Activate plugin.
			register_activation_hook( ENVATO_MARKET_CORE_FILE, array( $this, 'activate' ) );

			// Deactivate plugin.
			register_deactivation_hook( ENVATO_MARKET_CORE_FILE, array( $this, 'deactivate' ) );

			// Load the textdomain.
			add_action( 'init', array( $this, 'load_textdomain' ) );

			// Load OAuth.
			add_action( 'init', array( $this, 'admin' ) );

			// Load Upgrader.
			add_action( 'init', array( $this, 'items' ) );
		}

		/**
		 * Activate plugin.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function activate() {
			self::set_plugin_state( true );
		}

		/**
		 * Deactivate plugin.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function deactivate() {
			self::set_plugin_state( false );
		}

		/**
		 * Loads the plugin's translated strings.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'envato-market', false, ENVATO_MARKET_PATH . 'languages/' );
		}

		/**
		 * Sanitize data key.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param string $key An alpha numeric string to sanitize.
		 * @return string
		 */
		private function sanitize_key( $key ) {
			return preg_replace( '/[^A-Za-z0-9\_]/i', '', str_replace( array( '-', ':' ), '_', $key ) );
		}

		/**
		 * Recursively converts data arrays to objects.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param array $array An array of data.
		 * @return object
		 */
		private function convert_data( $array ) {
			foreach ( (array) $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = self::convert_data( $value );
				}
			}
			return (object) $array;
		}

		/**
		 * Set the `is_plugin_active` option.
		 *
		 * This setting helps determine context. Since the plugin can be included in your theme root you
		 * might want to hide the admin UI when the plugin is not activated and implement your own.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @param bool $value Whether or not the plugin is active.
		 */
		private function set_plugin_state( $value ) {
			self::set_option( 'is_plugin_active', $value );
		}

		/**
		 * Set option value.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Option name.
		 * @param mixed  $option Option data.
		 */
		public function set_option( $name, $option ) {
			$options          = self::get_options();
			$name             = self::sanitize_key( $name );
			$options[ $name ] = esc_html( $option );
			$this->set_options( $options );
		}


		/**
		 * Set option.
		 *
		 * @since 2.0.0
		 *
		 * @param mixed $options Option data.
		 */
		public function set_options( $options ) {
			ENVATO_MARKET_NETWORK_ACTIVATED ? update_site_option( $this->option_name, $options ) : update_option( $this->option_name, $options );
		}

		/**
		 * Return the option settings array.
		 *
		 * @since 1.0.0
		 */
		public function get_options() {
			return ENVATO_MARKET_NETWORK_ACTIVATED ? get_site_option( $this->option_name, array() ) : get_option( $this->option_name, array() );
		}

		/**
		 * Return a value from the option settings array.
		 *
		 * @since 1.0.0
		 *
		 * @param string $name Option name.
		 * @param mixed  $default The default value if nothing is set.
		 * @return mixed
		 */
		public function get_option( $name, $default = '' ) {
			$options = self::get_options();
			$name    = self::sanitize_key( $name );
			return isset( $options[ $name ] ) ? $options[ $name ] : $default;
		}

		/**
		 * Set data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $key Unique object key.
		 * @param mixed  $data Any kind of data.
		 */
		public function set_data( $key, $data ) {
			if ( ! empty( $key ) ) {
				if ( is_array( $data ) ) {
					$data = self::convert_data( $data );
				}
				$key = self::sanitize_key( $key );
				// @codingStandardsIgnoreStart
				$this->data->$key = $data;
				// @codingStandardsIgnoreEnd
			}
		}

		/**
		 * Get data.
		 *
		 * @since 1.0.0
		 *
		 * @param string $key Unique object key.
		 * @return string|object
		 */
		public function get_data( $key ) {
			return isset( $this->data->$key ) ? $this->data->$key : '';
		}

		/**
		 * Return the plugin slug.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_slug() {
			return $this->slug;
		}

		/**
		 * Return the plugin version number.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Return the plugin URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_plugin_url() {
			return $this->plugin_url;
		}

		/**
		 * Return the plugin path.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_plugin_path() {
			return $this->plugin_path;
		}

		/**
		 * Return the plugin page URL.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_page_url() {
			return $this->page_url;
		}

		/**
		 * Return the option settings name.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_option_name() {
			return $this->option_name;
		}

		/**
		 * Admin UI class.
		 *
		 * @since 1.0.0
		 *
		 * @return Envato_Market_Admin
		 */
		public function admin() {
			return Envato_Market_Admin::instance();
		}

		/**
		 * Envato API class.
		 *
		 * @since 1.0.0
		 *
		 * @return Envato_Market_API
		 */
		public function api() {
			return Envato_Market_API::instance();
		}

		/**
		 * Items class.
		 *
		 * @since 1.0.0
		 *
		 * @return Envato_Market_Items
		 */
		public function items() {
			return Envato_Market_Items::instance();
		}
	}

endif;
