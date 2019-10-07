<?php
/**
 * Envato Market Github class.
 *
 * @package Envato_Market
 */

if ( ! class_exists( 'Envato_Market_Github' ) ) :

	/**
	 * Creates the connection between Github to install & update the Envato Market plugin.
	 *
	 * @class Envato_Market_Github
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_Github {

		/**
		 * Action nonce.
		 *
		 * @type string
		 */
		const AJAX_ACTION = 'envato_market_dismiss_notice';

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
		 * The API URL.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var string
		 */
		private static $api_url = 'http://envato.github.io/wp-envato-market/dist/update-check.json';

		/**
		 * The Envato_Market_Items Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Envato_Market_Github()
		 * @uses Envato_Market_Github::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Envato_Market_Github.
		 * @codeCoverageIgnore
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::$_instance->init_actions();
			}
			return self::$_instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Market_Github::instance()
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
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'envato-market' ), '1.0.0' );
		}

		/**
		 * You cannot unserialize instances of this class.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'envato-market' ), '1.0.0' );
		}

		/**
		 * Setup the actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.0.0
		 */
		public function init_actions() {

			// Bail outside of the WP Admin panel.
			if ( ! is_admin() ) {
				return;
			}

			add_filter( 'http_request_args', array( $this, 'update_check' ), 5, 2 );
			add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ) );
			add_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ) );
			add_filter( 'site_transient_update_plugins', array( $this, 'update_state' ) );
			add_filter( 'transient_update_plugins', array( $this, 'update_state' ) );
			add_action( 'admin_notices', array( $this, 'notice' ) );
			add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'dismiss_notice' ) );
		}

		/**
		 * Check Github for an update.
		 *
		 * @since 1.0.0
		 *
		 * @return false|object
		 */
		public function api_check() {
			$raw_response = wp_remote_get( self::$api_url );
			if ( is_wp_error( $raw_response ) ) {
				return false;
			}

			if ( ! empty( $raw_response['body'] ) ) {
				$raw_body = json_decode( $raw_response['body'], true );
				if ( $raw_body ) {
					return (object) $raw_body;
				}
			}

			return false;
		}

		/**
		 * Disables requests to the wp.org repository for Envato Market.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $request An array of HTTP request arguments.
		 * @param string $url The request URL.
		 * @return array
		 */
		public function update_check( $request, $url ) {

			// Plugin update request.
			if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' ) ) {

				// Decode JSON so we can manipulate the array.
				$data = json_decode( $request['body']['plugins'] );

				// Remove the Envato Market.
				unset( $data->plugins->{'envato-market/envato-market.php'} );

				// Encode back into JSON and update the response.
				$request['body']['plugins'] = wp_json_encode( $data );
			}

			return $request;
		}

		/**
		 * API check.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $api Always false.
		 * @param string $action The API action being performed.
		 * @param object $args Plugin arguments.
		 * @return mixed $api The plugin info or false.
		 */
		public function plugins_api( $api, $action, $args ) {
			if ( isset( $args->slug ) && 'envato-market' === $args->slug ) {
				$api_check = $this->api_check();
				if ( is_object( $api_check ) ) {
					$api = $api_check;
				}
			}
			return $api;
		}

		/**
		 * Update check.
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient The pre-saved value of the `update_plugins` site transient.
		 * @return object
		 */
		public function update_plugins( $transient ) {
			$state = $this->state();
			if ( 'activated' === $state ) {
				$api_check = $this->api_check();
				if ( is_object( $api_check ) && version_compare( envato_market()->get_version(), $api_check->version, '<' ) ) {
					$transient->response['envato-market/envato-market.php'] = (object) array(
						'slug'        => 'envato-market',
						'plugin'      => 'envato-market/envato-market.php',
						'new_version' => $api_check->version,
						'url'         => 'https://github.com/envato/wp-envato-market',
						'package'     => $api_check->download_link,
					);
				}
			}
			return $transient;
		}

		/**
		 * Set the plugin state.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function state() {
			$option         = 'envato_market_state';
			$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
			// We also have to check network activated plugins. Otherwise this plugin won't update on multisite.
			$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
			if ( ! is_array( $active_plugins ) ) {
				$active_plugins = array();
			}
			if ( ! is_array( $active_sitewide_plugins ) ) {
				$active_sitewide_plugins = array();
			}
			$active_plugins = array_merge( $active_plugins, array_keys( $active_sitewide_plugins ) );
			if ( in_array( 'envato-market/envato-market.php', $active_plugins ) ) {
				$state = 'activated';
				update_option( $option, $state );
			} else {
				$state = 'install';
				update_option( $option, $state );
				foreach ( array_keys( get_plugins() ) as $plugin ) {
					if ( strpos( $plugin, 'envato-market.php' ) !== false ) {
						$state = 'deactivated';
						update_option( $option, $state );
					}
				}
			}
			return $state;
		}

		/**
		 * Force the plugin state to be updated.
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient The saved value of the `update_plugins` site transient.
		 * @return object
		 */
		public function update_state( $transient ) {
			$state = $this->state();
			return $transient;
		}

		/**
		 * Admin notices.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function notice() {
			$screen = get_current_screen();
			$slug   = 'envato-market';
			$state  = get_option( 'envato_market_state' );
			$notice = get_option( self::AJAX_ACTION );

			if ( empty( $state ) ) {
				$state = $this->state();
			}

			if (
				'activated' === $state ||
				'update-core' === $screen->id ||
				'update' === $screen->id ||
				'plugins' === $screen->id && isset( $_GET['action'] ) && 'delete-selected' === $_GET['action'] ||
				'dismissed' === $notice
				) {
				return;
			}

			if ( 'deactivated' === $state ) {
				$activate_url = add_query_arg(
					array(
						'action'   => 'activate',
						'plugin'   => urlencode( "$slug/$slug.php" ),
						'_wpnonce' => urlencode( wp_create_nonce( "activate-plugin_$slug/$slug.php" ) ),
					),
					self_admin_url( 'plugins.php' )
				);

				$message = sprintf(
					esc_html__( '%1$sActivate the Envato Market plugin%2$s to get updates for your ThemeForest and CodeCanyon items.', 'envato-market' ),
					'<a href="' . esc_url( $activate_url ) . '">',
					'</a>'
				);
			} elseif ( 'install' === $state ) {
				$install_url = add_query_arg(
					array(
						'action' => 'install-plugin',
						'plugin' => $slug,
					),
					self_admin_url( 'update.php' )
				);

				$message = sprintf(
					esc_html__( '%1$sInstall the Envato Market plugin%2$s to get updates for your ThemeForest and CodeCanyon items.', 'envato-market' ),
					'<a href="' . esc_url( wp_nonce_url( $install_url, 'install-plugin_' . $slug ) ) . '">',
					'</a>'
				);
			}

			if ( isset( $message ) ) {
				?>
				<div class="updated envato-market-notice notice is-dismissible">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<script>
				jQuery( document ).ready( function( $ ) {
					$( document ).on( 'click', '.envato-market-notice .notice-dismiss', function() {
						$.ajax( {
							url: ajaxurl,
							data: {
								action: '<?php echo self::AJAX_ACTION; ?>',
								nonce: '<?php echo wp_create_nonce( self::AJAX_ACTION ); ?>'
							}
						} );
					} );
				} );
				</script>
				<?php
			}
		}

		/**
		 * Dismiss admin notice.
		 *
		 * @since 1.0.0
		 */
		public function dismiss_notice() {
			check_ajax_referer( self::AJAX_ACTION, 'nonce' );

			update_option( self::AJAX_ACTION, 'dismissed' );
			wp_send_json_success();
		}
	}

	if ( ! function_exists( 'envato_market_github' ) ) :
		/**
		 * Envato_Market_Github Instance
		 *
		 * @since 1.0.0
		 *
		 * @return Envato_Market_Github
		 */
		function envato_market_github() {
			return Envato_Market_Github::instance();
		}
	endif;

	/**
	 * Loads the main instance of Envato_Market_Github
	 *
	 * @since 1.0.0
	 */
	add_action( 'after_setup_theme', 'envato_market_github', 99 );

endif;
