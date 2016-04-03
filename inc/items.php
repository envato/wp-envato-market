<?php
/**
 * Items class.
 *
 * @package Envato_Market
 */

if ( ! class_exists( 'Envato_Market_Items' ) ) :

	/**
	 * Creates the theme & plugin arrays & injects API results.
	 *
	 * @class Envato_Market_Items
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_Items {

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
		 * Premium themes.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var array
		 */
		private static $themes = array();

		/**
		 * Premium plugins.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var array
		 */
		private static $plugins = array();

		/**
		 * WordPress plugins.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @var array
		 */
		private static $wp_plugins = array();

		/**
		 * The Envato_Market_Items Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Envato_Market_Items()
		 * @uses Envato_Market_Items::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Envato_Market_Items.
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
		 * @see Envato_Market_Items::instance()
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
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.0.0
		 */
		public function init_actions() {
			// Check for theme & plugin updates.
			add_filter( 'http_request_args', array( $this, 'update_check' ), 5, 2 );

			// Inject plugin updates into the response array.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'update_plugins' ) );
			add_filter( 'pre_set_transient_update_plugins', array( $this, 'update_plugins' ) );

			// Inject theme updates into the response array.
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'update_themes' ) );
			add_filter( 'pre_set_transient_update_themes', array( $this, 'update_themes' ) );

			// Inject plugin information into the API calls.
			add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );

			// Rebuild the saved theme data.
			add_action( 'after_switch_theme', array( $this, 'rebuild_themes' ) );

			// Rebuild the saved plugin data.
			add_action( 'activated_plugin', array( $this, 'rebuild_plugins' ) );
			add_action( 'deactivated_plugin', array( $this, 'rebuild_plugins' ) );
		}

		/**
		 * Get the premium plugins list.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The plugin group. Options are 'purchased', 'active', 'installed', or 'install'.
		 * @return array
		 */
		public function plugins( $group = '' ) {
			if ( ! empty( $group ) ) {
				if ( isset( self::$plugins[ $group ] ) ) {
					return self::$plugins[ $group ];
				} else {
					return array();
				}
			}

			return self::$plugins;
		}

		/**
		 * Get the premium themes list.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The theme group. Options are 'purchased', 'active', 'installed', or 'install'.
		 * @return array
		 */
		public function themes( $group = '' ) {
			if ( ! empty( $group ) ) {
				if ( isset( self::$themes[ $group ] ) ) {
					return self::$themes[ $group ];
				} else {
					return array();
				}
			}

			return self::$themes;
		}

		/**
		 * Get the list of WordPress plugins
		 *
		 * @since 1.0.0
		 *
		 * @param bool $flush Forces a cache flush. Default is 'false'.
		 * @return array
		 */
		public function wp_plugins( $flush = false ) {
			if ( empty( self::$wp_plugins ) || true === $flush ) {
				wp_cache_flush();
				self::$wp_plugins = get_plugins();
			}

			return self::$wp_plugins;
		}

		/**
		 * Disables requests to the wp.org repository for premium themes.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $request An array of HTTP request arguments.
		 * @param string $url The request URL.
		 * @return array
		 */
		public function update_check( $request, $url ) {

			// Theme update request.
			if ( false !== strpos( $url, '//api.wordpress.org/themes/update-check/1.1/' ) ) {

				/**
				 * Excluded theme slugs that should never ping the WordPress API.
				 * We don't need the extra http requests for themes we know are premium.
				 */
				self::set_themes();
				$installed = self::$themes['installed'];

				// Decode JSON so we can manipulate the array.
				$data = json_decode( $request['body']['themes'] );

				// Remove the excluded themes.
				foreach ( $installed as $slug => $id ) {
					unset( $data->themes->$slug );
				}

				// Encode back into JSON and update the response.
				$request['body']['themes'] = wp_json_encode( $data );
			}

			// Plugin update request.
			if ( false !== strpos( $url, '//api.wordpress.org/plugins/update-check/1.1/' ) ) {

				/**
				 * Excluded theme slugs that should never ping the WordPress API.
				 * We don't need the extra http requests for themes we know are premium.
				 */
				self::set_plugins();
				$installed = self::$plugins['installed'];

				// Decode JSON so we can manipulate the array.
				$data = json_decode( $request['body']['plugins'] );

				// Remove the excluded themes.
				foreach ( $installed as $slug => $id ) {
					unset( $data->plugins->$slug );
				}

				// Encode back into JSON and update the response.
				$request['body']['plugins'] = wp_json_encode( $data );
			}

			return $request;
		}

		/**
		 * Inject update data for premium themes.
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient The pre-saved value of the `update_themes` site transient.
		 * @return object
		 */
		public function update_themes( $transient ) {
			// Process premium theme updates.
			if ( isset( $transient->checked ) ) {
				self::set_themes( true );
				$installed = array_merge( self::$themes['active'], self::$themes['installed'] );

				foreach ( $installed as $slug => $premium ) {
					$theme = wp_get_theme( $slug );
					if ( $theme->exists() && version_compare( $theme->get( 'Version' ), $premium['version'], '<' ) ) {
						$transient->response[ $slug ] = array(
							'theme'       => $slug,
							'new_version' => $premium['version'],
							'url'         => $premium['url'],
							'package'     => envato_market()->api()->deferred_download( $premium['id'] ),
						);
					}
				}
			}

			return $transient;
		}

		/**
		 * Inject update data for premium plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param object $transient The pre-saved value of the `update_plugins` site transient.
		 * @return object
		 */
		public function update_plugins( $transient ) {
			self::set_plugins( true );

			// Process premium plugin updates.
			$installed = array_merge( self::$plugins['active'], self::$plugins['installed'] );
			$plugins = self::wp_plugins();

			foreach ( $installed as $plugin => $premium ) {
				if ( isset( $plugins[ $plugin ] ) && version_compare( $plugins[ $plugin ]['Version'], $premium['version'], '<' ) ) {
					$_plugin = array(
						'slug'        => dirname( $plugin ),
						'plugin'      => $plugin,
						'new_version' => $premium['version'],
						'url'         => $premium['url'],
						'package'     => envato_market()->api()->deferred_download( $premium['id'] ),
					);
					$transient->response[ $plugin ] = (object) $_plugin;
				}
			}

			return $transient;
		}

		/**
		 * Inject API data for premium plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param bool   $response Always false.
		 * @param string $action The API action being performed.
		 * @param object $args Plugin arguments.
		 * @return bool|object $response The plugin info or false.
		 */
		public function plugins_api( $response, $action, $args ) {
			self::set_plugins( true );

			// Process premium theme updates.
			if ( 'plugin_information' === $action && isset( $args->slug ) ) {
				$installed = array_merge( self::$plugins['active'], self::$plugins['installed'] );
				foreach ( $installed as $slug => $plugin ) {
					if ( dirname( $slug ) === $args->slug ) {
						$response = new stdClass();
						$response->slug = $args->slug;
						$response->plugin = $slug;
						$response->plugin_name = $plugin['name'];
						$response->name = $plugin['name'];
						$response->version = $plugin['version'];
						$response->author = $plugin['author'];
						$response->homepage = $plugin['url'];
						$response->requires = $plugin['requires'];
						$response->tested = $plugin['tested'];
						$response->downloaded = $plugin['number_of_sales'];
						$response->last_updated = $plugin['updated_at'];
						$response->sections = array( 'description' => $plugin['description'] );
						$response->banners['low'] = $plugin['landscape_url'];
						$response->rating = $plugin['rating']['rating'] / 5 * 100;
						$response->num_ratings = $plugin['rating']['count'];
						$response->download_link = envato_market()->api()->deferred_download( $plugin['id'] );
						break;
					}
				}
			}

			return $response;
		}

		/**
		 * Set the list of themes
		 *
		 * @since 1.0.0
		 *
		 * @param bool $forced Forces an API request. Default is 'false'.
		 * @param bool $use_cache Attempts to rebuild from the cache before making an API request.
		 */
		public function set_themes( $forced = false, $use_cache = false ) {
			self::$themes = get_site_transient( envato_market()->get_option_name() . '_themes' );

			if ( false === self::$themes || true === $forced ) {
				$themes = envato_market()->api()->themes();
				foreach ( envato_market()->get_option( 'items', array() ) as $item ) {
					if ( empty( $item ) ) {
						continue;
					}
					if ( 'theme' === $item['type'] ) {
						$request_args = array(
							'headers' => array(
								'Authorization' => 'Bearer ' . $item['token'],
							),
						);
						$request = envato_market()->api()->item( $item['id'], $request_args );
						if ( false !== $request ) {
							$themes[] = $request;
						}
					}
				}
				self::process_themes( $themes );
			} elseif ( true === $use_cache ) {
				self::process_themes( self::$themes['purchased'] );
			}
		}

		/**
		 * Set the list of plugins
		 *
		 * @since 1.0.0
		 *
		 * @param bool  $forced Forces an API request. Default is 'false'.
		 * @param bool  $use_cache Attempts to rebuild from the cache before making an API request.
		 * @param array $args Used to remove or add a plugin during activate and deactivate routines.
		 */
		public function set_plugins( $forced = false, $use_cache = false, $args = array() ) {
			self::$plugins = get_site_transient( envato_market()->get_option_name() . '_plugins' );

			if ( false === self::$plugins || true === $forced ) {
				$plugins = envato_market()->api()->plugins();
				foreach ( envato_market()->get_option( 'items', array() ) as $item ) {
					if ( empty( $item ) ) {
						continue;
					}
					if ( 'plugin' === $item['type'] ) {
						$request_args = array(
							'headers' => array(
								'Authorization' => 'Bearer ' . $item['token'],
							),
						);
						$request = envato_market()->api()->item( $item['id'], $request_args );
						if ( false !== $request ) {
							$plugins[] = $request;
						}
					}
				}
				self::process_plugins( $plugins, $args );
			} elseif ( true === $use_cache ) {
				self::process_plugins( self::$plugins['purchased'], $args );
			}
		}

		/**
		 * Rebuild the themes array using the cache value if possible.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $filter Any data being filtered.
		 * @return mixed
		 */
		public function rebuild_themes( $filter ) {
			self::set_themes( false, true );

			return $filter;
		}

		/**
		 * Rebuild the plugins array using the cache value if possible.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin The plugin to add or remove.
		 */
		public function rebuild_plugins( $plugin ) {
			$remove = ( 'deactivated_plugin' === current_filter() ) ? true : false;
			self::set_plugins( false, true, array( 'plugin' => $plugin, 'remove' => $remove ) );
		}

		/**
		 * Normalizes a string to do a value check against.
		 *
		 * Strip all HTML tags including script and style & then decode the
		 * HTML entities so `&amp;` will equal `&` in the value check and
		 * finally lower case the entire string. This is required becuase some
		 * themes & plugins add a link to the Author field or ambersands to the
		 * names, or change the case of their files or names, which will not match
		 * the saved value in the database causing a false negative.
		 *
		 * @since 1.0.0
		 *
		 * @param string $string The string to normalize.
		 * @return string
		 */
		public function normalize( $string ) {
			return strtolower( html_entity_decode( wp_strip_all_tags( $string ) ) );
		}

		/**
		 * Process the themes and save the transient.
		 *
		 * @since 1.0.0
		 *
		 * @param array $purchased The purchased themes array.
		 */
		private function process_themes( $purchased ) {
			if ( is_wp_error( $purchased ) ) {
				$purchased = array();
			}

			$current   = wp_get_theme()->get_template();
			$active    = array();
			$installed = array();
			$install   = $purchased;

			if ( ! empty( $purchased ) ) {
				foreach ( wp_get_themes() as $theme ) {

					/**
					 * WP_Theme object.
					 *
					 * @var WP_Theme $theme
					 */
					$template = $theme->get_template();
					$title    = $theme->get( 'Name' );
					$author   = $theme->get( 'Author' );

					foreach ( $install as $key => $value ) {
						if ( $this->normalize( $value['name'] ) === $this->normalize( $title ) && $this->normalize( $value['author'] ) === $this->normalize( $author ) ) {
							$installed[ $template ] = $value;
							unset( $install[ $key ] );
						}
					}
				}
			}

			if ( isset( $installed[ $current ] ) ) {
				$active[ $current ] = $installed[ $current ];
				unset( $installed[ $current ] );
			}

			self::$themes['purchased'] = array_unique( $purchased, SORT_REGULAR );
			self::$themes['active']    = array_unique( $active, SORT_REGULAR );
			self::$themes['installed'] = array_unique( $installed, SORT_REGULAR );
			self::$themes['install']   = array_unique( array_values( $install ), SORT_REGULAR );

			set_site_transient( envato_market()->get_option_name() . '_themes', self::$themes, HOUR_IN_SECONDS );
		}

		/**
		 * Process the plugins and save the transient.
		 *
		 * @since 1.0.0
		 *
		 * @param array $purchased The purchased plugins array.
		 * @param array $args Used to remove or add a plugin during activate and deactivate routines.
		 */
		private function process_plugins( $purchased, $args = array() ) {
			if ( is_wp_error( $purchased ) ) {
				$purchased = array();
			}

			$active    = array();
			$installed = array();
			$install   = $purchased;

			if ( ! empty( $purchased ) ) {
				foreach ( self::wp_plugins( true ) as $slug => $plugin ) {
					foreach ( $install as $key => $value ) {
						if ( $this->normalize( $value['name'] ) === $this->normalize( $plugin['Name'] ) && $this->normalize( $value['author'] ) === $this->normalize( $plugin['Author'] ) && file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
							$installed[ $slug ] = $value;
							unset( $install[ $key ] );
						}
					}
				}
			}

			foreach ( $installed as $slug => $plugin ) {
				$condition = false;
				if ( ! empty( $args ) && $slug === $args['plugin'] ) {
					if ( true === $args['remove'] ) {
						continue;
					}
					$condition = true;
				}
				if ( $condition || is_plugin_active( $slug ) ) {
					$active[ $slug ] = $plugin;
					unset( $installed[ $slug ] );
				}
			}

			self::$plugins['purchased'] = array_unique( $purchased, SORT_REGULAR );
			self::$plugins['active']    = array_unique( $active, SORT_REGULAR );
			self::$plugins['installed'] = array_unique( $installed, SORT_REGULAR );
			self::$plugins['install']   = array_unique( array_values( $install ), SORT_REGULAR );

			set_site_transient( envato_market()->get_option_name() . '_plugins', self::$plugins, HOUR_IN_SECONDS );
		}
	}

endif;
