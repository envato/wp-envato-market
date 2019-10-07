<?php
/**
 * Envato API class.
 *
 * @package Envato_Market
 */

if ( ! class_exists( 'Envato_Market_API' ) && class_exists( 'Envato_Market' ) ) :

	/**
	 * Creates the Envato API connection.
	 *
	 * @class Envato_Market_API
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_API {

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
		 * The Envato API personal token.
		 *
		 * @since 1.0.0
		 *
		 * @var string
		 */
		public $token;

		/**
		 * Main Envato_Market_API Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Envato_Market_API()
		 * @uses Envato_Market_API::init_globals() Setup class globals.
		 * @uses Envato_Market_API::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Envato_Market_API.
		 * @codeCoverageIgnore
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::$_instance->init_globals();
			}
			return self::$_instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Market_API::instance()
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
		 * Setup the class globals.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function init_globals() {
			// Envato API token.
			$this->token = envato_market()->get_option( 'token' );
		}

		/**
		 * Query the Envato API.
		 *
		 * @uses wp_remote_get() To perform an HTTP request.
		 *
		 * @since 1.0.0
		 *
		 * @param  string $url API request URL, including the request method, parameters, & file type.
		 * @param  array  $args The arguments passed to `wp_remote_get`.
		 * @return array|WP_Error  The HTTP response.
		 */
		public function request( $url, $args = array() ) {
			$defaults = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->token,
					'User-Agent'    => 'WordPress - Envato Market ' . envato_market()->get_version(),
				),
				'timeout' => 14,
			);
			$args     = wp_parse_args( $args, $defaults );

			$token = trim( str_replace( 'Bearer', '', $args['headers']['Authorization'] ) );
			if ( empty( $token ) ) {
				return new WP_Error( 'api_token_error', __( 'An API token is required.', 'envato-market' ) );
			}

			$debugging_information = [
				'request_url' => $url,
			];

			// Make an API request.
			$response = wp_remote_get( esc_url_raw( $url ), $args );

			// Check the response code.
			$response_code    = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );

			$debugging_information['response_code']   = $response_code;
			$debugging_information['response_cf_ray'] = wp_remote_retrieve_header( $response, 'cf-ray' );
			$debugging_information['response_server'] = wp_remote_retrieve_header( $response, 'server' );

			if ( ! empty( $response->errors ) && isset( $response->errors['http_request_failed'] ) ) {
				// API connectivity issue, inject notice into transient with more details.
				$option = envato_market()->get_options();
				if ( empty( $option['notices'] ) ) {
					$option['notices'] = [];
				}
				$option['notices']['http_error'] = current( $response->errors['http_request_failed'] );
				envato_market()->set_options( $option );
				return new WP_Error( 'http_error', esc_html( current( $response->errors['http_request_failed'] ) ), $debugging_information );
			}

			if ( 200 !== $response_code && ! empty( $response_message ) ) {
				return new WP_Error( $response_code, $response_message, $debugging_information );
			} elseif ( 200 !== $response_code ) {
				return new WP_Error( $response_code, __( 'An unknown API error occurred.', 'envato-market' ), $debugging_information );
			} else {
				$return = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( null === $return ) {
					return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'envato-market' ), $debugging_information );
				}
				return $return;
			}
		}

		/**
		 * Deferred item download URL.
		 *
		 * @since 1.0.0
		 *
		 * @param int $id The item ID.
		 * @return string.
		 */
		public function deferred_download( $id ) {
			if ( empty( $id ) ) {
				return '';
			}

			$args = array(
				'deferred_download' => true,
				'item_id'           => $id,
			);
			return add_query_arg( $args, esc_url( envato_market()->get_page_url() ) );
		}

		/**
		 * Get the item download.
		 *
		 * @since 1.0.0
		 *
		 * @param  int   $id The item ID.
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return bool|array The HTTP response.
		 */
		public function download( $id, $args = array() ) {
			if ( empty( $id ) ) {
				return false;
			}

			$url      = 'https://api.envato.com/v2/market/buyer/download?item_id=' . $id . '&shorten_url=true';
			$response = $this->request( $url, $args );

			// @todo Find out which errors could be returned & handle them in the UI.
			if ( is_wp_error( $response ) || empty( $response ) || ! empty( $response['error'] ) ) {
				return false;
			}

			if ( ! empty( $response['wordpress_theme'] ) ) {
				return $response['wordpress_theme'];
			}

			if ( ! empty( $response['wordpress_plugin'] ) ) {
				return $response['wordpress_plugin'];
			}

			// Missing a WordPress theme and plugin, report an error.
			$option = envato_market()->get_options();
			if ( ! isset( $option['notices'] ) ) {
				$option['notices'] = [];
			}
			$option['notices']['missing-package-zip'] = true;
			envato_market()->set_options( $option );

			return false;
		}

		/**
		 * Get an item by ID and type.
		 *
		 * @since 1.0.0
		 *
		 * @param  int   $id The item ID.
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function item( $id, $args = array() ) {
			$url      = 'https://api.envato.com/v2/market/catalog/item?id=' . $id;
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) ) {
				return false;
			}

			if ( ! empty( $response['wordpress_theme_metadata'] ) ) {
				return $this->normalize_theme( $response );
			}

			if ( ! empty( $response['wordpress_plugin_metadata'] ) ) {
				return $this->normalize_plugin( $response );
			}

			return false;
		}

		/**
		 * Get the list of available themes.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function themes( $args = array() ) {
			$themes = array();

			$url      = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-themes';
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) || empty( $response['results'] ) ) {
				return $themes;
			}

			foreach ( $response['results'] as $theme ) {
				$themes[] = $this->normalize_theme( $theme['item'] );
			}

			return $themes;
		}

		/**
		 * Normalize a theme.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $theme An array of API request values.
		 * @return array A normalized array of values.
		 */
		public function normalize_theme( $theme ) {
			$normalized_theme = array(
				'id'            => $theme['id'],
				'name'          => ( ! empty( $theme['wordpress_theme_metadata']['theme_name'] ) ? $theme['wordpress_theme_metadata']['theme_name'] : '' ),
				'author'        => ( ! empty( $theme['wordpress_theme_metadata']['author_name'] ) ? $theme['wordpress_theme_metadata']['author_name'] : '' ),
				'version'       => ( ! empty( $theme['wordpress_theme_metadata']['version'] ) ? $theme['wordpress_theme_metadata']['version'] : '' ),
				'description'   => self::remove_non_unicode( strip_tags( $theme['wordpress_theme_metadata']['description'] ) ),
				'url'           => ( ! empty( $theme['url'] ) ? $theme['url'] : '' ),
				'author_url'    => ( ! empty( $theme['author_url'] ) ? $theme['author_url'] : '' ),
				'thumbnail_url' => ( ! empty( $theme['thumbnail_url'] ) ? $theme['thumbnail_url'] : '' ),
				'rating'        => ( ! empty( $theme['rating'] ) ? $theme['rating'] : '' ),
				'landscape_url' => '',
			);

			// No main thumbnail in API response, so we grab it from the preview array.
			if ( empty( $normalized_theme['thumbnail_url'] ) && ! empty( $theme['previews'] ) && is_array( $theme['previews'] ) ) {
				foreach ( $theme['previews'] as $possible_preview ) {
					if ( ! empty( $possible_preview['landscape_url'] ) ) {
						$normalized_theme['landscape_url'] = $possible_preview['landscape_url'];
						break;
					}
				}
			}
			if ( empty( $normalized_theme['thumbnail_url'] ) && ! empty( $theme['previews'] ) && is_array( $theme['previews'] ) ) {
				foreach ( $theme['previews'] as $possible_preview ) {
					if ( ! empty( $possible_preview['icon_url'] ) ) {
						$normalized_theme['thumbnail_url'] = $possible_preview['icon_url'];
						break;
					}
				}
			}

			return $normalized_theme;
		}

		/**
		 * Get the list of available plugins.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $args The arguments passed to `wp_remote_get`.
		 * @return array The HTTP response.
		 */
		public function plugins( $args = array() ) {
			$plugins = array();

			$url      = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-plugins';
			$response = $this->request( $url, $args );

			if ( is_wp_error( $response ) || empty( $response ) || empty( $response['results'] ) ) {
				return $plugins;
			}

			foreach ( $response['results'] as $plugin ) {
				$plugins[] = $this->normalize_plugin( $plugin['item'] );
			}

			return $plugins;
		}

		/**
		 * Normalize a plugin.
		 *
		 * @since 1.0.0
		 *
		 * @param  array $plugin An array of API request values.
		 * @return array A normalized array of values.
		 */
		public function normalize_plugin( $plugin ) {
			$requires = null;
			$tested   = null;
			$versions = array();

			// Set the required and tested WordPress version numbers.
			foreach ( $plugin['attributes'] as $k => $v ) {
				if ( ! empty( $v['name'] ) && 'compatible-software' === $v['name'] && ! empty( $v['value'] ) && is_array( $v['value'] ) ) {
					foreach ( $v['value'] as $version ) {
						$versions[] = str_replace( 'WordPress ', '', trim( $version ) );
					}
					if ( ! empty( $versions ) ) {
						$requires = $versions[ count( $versions ) - 1 ];
						$tested   = $versions[0];
					}
					break;
				}
			}

			$plugin_normalized = array(
				'id'              => $plugin['id'],
				'name'            => ( ! empty( $plugin['wordpress_plugin_metadata']['plugin_name'] ) ? $plugin['wordpress_plugin_metadata']['plugin_name'] : '' ),
				'author'          => ( ! empty( $plugin['wordpress_plugin_metadata']['author'] ) ? $plugin['wordpress_plugin_metadata']['author'] : '' ),
				'version'         => ( ! empty( $plugin['wordpress_plugin_metadata']['version'] ) ? $plugin['wordpress_plugin_metadata']['version'] : '' ),
				'description'     => self::remove_non_unicode( strip_tags( $plugin['wordpress_plugin_metadata']['description'] ) ),
				'url'             => ( ! empty( $plugin['url'] ) ? $plugin['url'] : '' ),
				'author_url'      => ( ! empty( $plugin['author_url'] ) ? $plugin['author_url'] : '' ),
				'thumbnail_url'   => ( ! empty( $plugin['thumbnail_url'] ) ? $plugin['thumbnail_url'] : '' ),
				'landscape_url'   => ( ! empty( $plugin['previews']['landscape_preview']['landscape_url'] ) ? $plugin['previews']['landscape_preview']['landscape_url'] : '' ),
				'requires'        => $requires,
				'tested'          => $tested,
				'number_of_sales' => ( ! empty( $plugin['number_of_sales'] ) ? $plugin['number_of_sales'] : '' ),
				'updated_at'      => ( ! empty( $plugin['updated_at'] ) ? $plugin['updated_at'] : '' ),
				'rating'          => ( ! empty( $plugin['rating'] ) ? $plugin['rating'] : '' ),
			);

			// No main thumbnail in API response, so we grab it from the preview array.
			if ( empty( $plugin_normalized['landscape_url'] ) && ! empty( $plugin['previews'] ) && is_array( $plugin['previews'] ) ) {
				foreach ( $plugin['previews'] as $possible_preview ) {
					if ( ! empty( $possible_preview['landscape_url'] ) ) {
						$plugin_normalized['landscape_url'] = $possible_preview['landscape_url'];
						break;
					}
				}
			}
			if ( empty( $plugin_normalized['thumbnail_url'] ) && ! empty( $plugin['previews'] ) && is_array( $plugin['previews'] ) ) {
				foreach ( $plugin['previews'] as $possible_preview ) {
					if ( ! empty( $possible_preview['icon_url'] ) ) {
						$plugin_normalized['thumbnail_url'] = $possible_preview['icon_url'];
						break;
					}
				}
			}

			return $plugin_normalized;
		}

		/**
		 * Remove all non unicode characters in a string
		 *
		 * @since 1.0.0
		 *
		 * @param string $retval The string to fix.
		 * @return string
		 */
		static private function remove_non_unicode( $retval ) {
			return preg_replace( '/[\x00-\x1F\x80-\xFF]/', '', $retval );
		}
	}

endif;
