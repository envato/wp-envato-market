<?php
/**
 * Admin UI class.
 *
 * @package Envato_Market
 */

if ( ! class_exists( 'Envato_Market_Admin' ) && class_exists( 'Envato_Market' ) ) :

	/**
	 * Creates an admin page to save the Envato API OAuth token.
	 *
	 * @class Envato_Market_Admin
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_Admin {

		/**
		 * Action nonce.
		 *
		 * @type string
		 */
		const AJAX_ACTION = 'envato_market';

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
		 * Main Envato_Market_Admin Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Envato_Market_Admin()
		 * @uses Envato_Market_Admin::init_actions() Setup hooks and actions.
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Envato_Market_Admin.
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
		 * @see Envato_Market_Admin::instance()
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
			// @codeCoverageIgnoreStart
			if ( false === envato_market()->get_data( 'admin' ) && false === envato_market()->get_option( 'is_plugin_active' ) ) { // Turns the UI off if allowed.
				return;
			}
			// @codeCoverageIgnoreEnd
			// Deferred Download.
			add_action( 'upgrader_package_options', array( $this, 'maybe_deferred_download' ), 99 );

			// Theme upgrade AJAX handler.
			add_action( 'wp_ajax_upgrade-theme', array( $this, 'ajax_upgrade_theme' ) );

			// Add item AJAX handler.
			add_action( 'wp_ajax_' . self::AJAX_ACTION . '_add_item', array( $this, 'ajax_add_item' ) );

			// Remove item AJAX handler.
			add_action( 'wp_ajax_' . self::AJAX_ACTION . '_remove_item', array( $this, 'ajax_remove_item' ) );

			// Maybe delete the site transients.
			add_action( 'init', array( $this, 'maybe_delete_transients' ), 11 );

			// Add the menu icon.
			add_action( 'admin_head', array( $this, 'add_menu_icon' ) );

			// Add the menu.
			add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

			// Register the settings.
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// We may need to redirect after an item is enabled.
			add_action( 'current_screen', array( $this, 'maybe_redirect' ) );

			// Add authorization notices.
			add_action( 'current_screen', array( $this, 'add_notices' ) );

			// Set the API values.
			add_action( 'current_screen', array( $this, 'set_items' ) );
		}

		/**
		 * Defers building the API download url until the last responsible moment to limit file requests.
		 *
		 * Filter the package options before running an update.
		 *
		 * @since 1.0.0
		 *
		 * @param array $options {
		 *     Options used by the upgrader.
		 *
		 *     @type string $package                     Package for update.
		 *     @type string $destination                 Update location.
		 *     @type bool   $clear_destination           Clear the destination resource.
		 *     @type bool   $clear_working               Clear the working resource.
		 *     @type bool   $abort_if_destination_exists Abort if the Destination directory exists.
		 *     @type bool   $is_multi                    Whether the upgrader is running multiple times.
		 *     @type array  $hook_extra                  Extra hook arguments.
		 * }
		 */
		function maybe_deferred_download( $options ) {
			$package = $options['package'];
			if ( false !== strrpos( $package, 'deferred_download' ) && false !== strrpos( $package, 'item_id' ) ) {
				parse_str( parse_url( $package, PHP_URL_QUERY ), $vars );
				if ( $vars['item_id'] ) {
					$args = $this->set_bearer_args( $vars['item_id'] );
					$options['package'] = envato_market()->api()->download( $vars['item_id'], $args );
				}
			}
			return $options;
		}

		/**
		 * Returns the bearer arguments for a request with a single use API Token.
		 *
		 * @since 1.0.0
		 *
		 * @param int $id The item ID.
		 * @return array
		 */
		public function set_bearer_args( $id ) {
			$token = '';
			$args = array();
			foreach ( envato_market()->get_option( 'items', array() ) as $item ) {
				if ( $item['id'] === $id ) {
					$token = $item['token'];
					break;
				}
			}
			if ( ! empty( $token ) ) {
				$args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
					),
				);
			}
			return $args;
		}

		/**
		 * Maybe delete the site transients.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function maybe_delete_transients() {
			if ( isset( $_POST[ envato_market()->get_option_name() ] ) ) {

				// Nonce check.
				if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( $_POST['_wpnonce'], envato_market()->get_slug() . '-options' ) ) {
		 			wp_die( __( 'You do not have sufficient permissions to delete transients.', 'envato-market' ) );
				}

				self::delete_transients();
			}
		}

		/**
		 * Delete the site transients.
		 *
		 * @since 1.0.0
		 * @access private
		 */
		private function delete_transients() {
			delete_site_transient( envato_market()->get_option_name() . '_themes' );
			delete_site_transient( envato_market()->get_option_name() . '_plugins' );
		}

		/**
		 * Prints out all settings sections added to a particular settings page in columns.
		 *
		 * @global array $wp_settings_sections Storage array of all settings sections added to admin pages
		 * @global array $wp_settings_fields Storage array of settings fields and info about their pages/sections
		 * @since 1.0.0
		 *
		 * @param string $page The slug name of the page whos settings sections you want to output.
		 * @param int    $columns The number of columns in each row.
		 */
		public static function do_settings_sections( $page, $columns = 2 ) {
			global $wp_settings_sections, $wp_settings_fields;

			// @codeCoverageIgnoreStart
			if ( ! isset( $wp_settings_sections[ $page ] ) ) {
				return;
			}
			// @codeCoverageIgnoreEnd
			$index = 0;

			foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
				// @codeCoverageIgnoreStart
				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
					continue;
				}
				// @codeCoverageIgnoreEnd
				$index++;

				// Set the column class.
				$class = 'col col-' . $index;
				if ( $columns === $index ) {
					$class .= ' last-feature';
					$index = 0;
				}
				?>
				<div class="<?php echo esc_attr( $class ); ?>">
					<?php
					if ( ! empty( $section['title'] ) ) {
						echo '<h3>' . esc_html( $section['title'] ) . '</h3>' . "\n";
					}
					if ( ! empty( $section['callback'] ) ) {
						call_user_func( $section['callback'], $section );
					}
					?>
					<table class="form-table">
						<?php do_settings_fields( $page, $section['id'] ); ?>
					</table>
				</div>
				<?php
			}
		}

		/**
		 * Add a font based menu icon
		 *
		 * @since 1.0.0
		 */
		public function add_menu_icon() {
			// Fonts directory URL.
			$fonts_dir_url = envato_market()->get_plugin_url() . 'fonts/';

			// Create font styles.
			$style = '<style type="text/css">
				/*<![CDATA[*/
				@font-face {
					font-family: "envato-market";
					src:url("' . $fonts_dir_url . 'envato-market.eot?20150626");
					src:url("' . $fonts_dir_url . 'envato-market.eot?#iefix20150626") format("embedded-opentype"),
					url("' . $fonts_dir_url . 'envato-market.woff?20150626") format("woff"),
					url("' . $fonts_dir_url . 'envato-market.ttf?20150626") format("truetype"),
					url("' . $fonts_dir_url . 'envato-market.svg?20150626#envato") format("svg");
					font-weight: normal;
					font-style: normal;
				}
				#adminmenu .toplevel_page_' . envato_market()->get_slug() . ' .menu-icon-generic div.wp-menu-image:before {
					font: normal 20px/1 "envato-market" !important;
					content: "\e600";
					speak: none;
					padding: 6px 0;
					height: 34px;
					width: 20px;
					display: inline-block;
					-webkit-font-smoothing: antialiased;
					-moz-osx-font-smoothing: grayscale;
					-webkit-transition: all .1s ease-in-out;
					-moz-transition:    all .1s ease-in-out;
					transition:         all .1s ease-in-out;
				}
				/*]]>*/
			</style>';

			// Remove space after colons.
			$style = str_replace( ': ', ':', $style );

			// Remove whitespace.
			echo str_replace( array( "\r\n", "\r", "\n", "\t", '	', '		', '		', '  ', '    ' ), '', $style );
		}

		/**
		 * Adds the menu.
		 *
		 * @since 1.0.0
		 */
		public function add_menu_page() {
			$page = add_menu_page( __( 'Envato Market', 'envato-market' ), __( 'Envato Market', 'envato-market' ), 'manage_options', envato_market()->get_slug(), array( $this, 'render_admin_callback' ) );

			// Enqueue admin CSS.
			add_action( 'admin_print_styles-' . $page, array( $this, 'admin_enqueue_style' ) );

			// Enqueue admin JavaScript.
			add_action( 'admin_print_scripts-' . $page, array( $this, 'admin_enqueue_script' ) );

			// Add Underscore.js templates.
			add_action( 'admin_footer-' . $page, array( $this, 'render_templates' ) );
		}

		/**
		 * Enqueue admin css.
		 *
		 * @since  1.0.0
		 */
		public function admin_enqueue_style() {
			$file_url = envato_market()->get_plugin_url() . 'css/envato-market' . ( is_rtl() ? '-rtl' : '' ) . '.css';
			wp_enqueue_style( envato_market()->get_slug(), $file_url, array( 'wp-jquery-ui-dialog' ), envato_market()->get_version() );
		}

		/**
		 * Enqueue admin script.
		 *
		 * @since  1.0.0
		 */
		public function admin_enqueue_script() {
			$min = ( WP_DEBUG ? '' : '.min' );
			$slug = envato_market()->get_slug();
			$version = envato_market()->get_version();
			$plugin_url = envato_market()->get_plugin_url();

			wp_enqueue_script( $slug, $plugin_url . 'js/envato-market' . $min . '.js', array( 'jquery', 'jquery-ui-dialog', 'wp-util' ), $version, true );
			wp_enqueue_script( $slug . '-updates', $plugin_url . 'js/updates' . $min . '.js', array( 'jquery', 'updates', 'wp-a11y', 'wp-util' ), $version, true );

			// Script data array.
			$exports = array(
				'nonce' => wp_create_nonce( self::AJAX_ACTION ),
				'action' => self::AJAX_ACTION,
				'i18n' => array(
					'save' => __( 'Save', 'envato-market' ),
					'remove' => __( 'Remove', 'envato-market' ),
					'cancel' => __( 'Cancel', 'envato-market' ),
					'error' => __( 'An unknown error occurred. Try again.', 'envato-market' ),
				),
			);

			// Export data to JS.
			wp_scripts()->add_data(
				$slug,
				'data',
				sprintf( 'var _envatoMarket = %s;', wp_json_encode( $exports ) )
			);
		}

		/**
		 * Underscore (JS) templates for dialog windows.
		 *
		 * @codeCoverageIgnore
		 */
		public function render_templates() {
			?>
			<script type="text/html" id="tmpl-envato-market-auth-check-button">
				<a href="<?php echo esc_url( add_query_arg( array( 'authorization' => 'check' ), envato_market()->get_page_url() ) ); ?>" class="button button-secondary auth-check-button" style="margin:0 5px"><?php esc_html_e( 'Test API Connection', 'envato-market' ); ?></a>
			</script>

			<script type="text/html" id="tmpl-envato-market-item">
				<li data-id="{{ data.id }}">
					<span class="item-name"><?php esc_html_e( 'ID', 'envato-market' ); ?>: {{ data.id }} - {{ data.name }}</span>
					<button class="item-delete dashicons dashicons-dismiss">
						<span class="screen-reader-text"><?php esc_html_e( 'Delete', 'envato-market' ) ?></span>
					</button>
					<input type="hidden" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[items][{{ data.key }}][name]" value="{{ data.name }}" />
					<input type="hidden" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[items][{{ data.key }}][token]" value="{{ data.token }}" />
					<input type="hidden" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[items][{{ data.key }}][id]" value="{{ data.id }}" />
					<input type="hidden" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[items][{{ data.key }}][type]" value="{{ data.type }}" />
					<input type="hidden" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[items][{{ data.key }}][authorized]" value="{{ data.authorized }}" />
				</li>
			</script>

			<script type="text/html" id="tmpl-envato-market-dialog-remove">
				<div id="envato-market-dialog-remove" title="<?php esc_html_e( 'Remove Item', 'envato-market' ) ?>">
					<p><?php esc_html_e( 'You are about to remove the connection between the Envato Market API and this item. You cannot undo this action.', 'envato-market' ) ?></p>
				</div>
			</script>

			<script type="text/html" id="tmpl-envato-market-dialog-form">
				<div id="envato-market-dialog-form" title="<?php esc_html_e( 'Add Item', 'envato-market' ) ?>">
					<form>
						<fieldset>
							<label for="token"><?php esc_html_e( 'Token', 'envato-market' ) ?></label>
							<input type="text" name="token" class="widefat" value="" />
							<p class="description"><?php esc_html_e( 'Enter the Envato API Personal Token.', 'envato-market' ); ?></p>
							<label for="id"><?php esc_html_e( 'Item ID', 'envato-market' ) ?></label>
							<input type="text" name="id" class="widefat" value="" />
							<p class="description"><?php esc_html_e( 'Enter the Envato Item ID.', 'envato-market' ); ?></p>
							<input type="submit" tabindex="-1" style="position:absolute; top:-5000px" />
						</fieldset>
					</form>
				</div>
			</script>
			
			<script type="text/html" id="tmpl-envato-market-dialog-error">
				<div class="notice notice-error">
					<p>{{ data.message }}</p>
				</div>
			</script>
			
			<script type="text/html" id="tmpl-envato-market-card">
				<div class="col" data-id="{{ data.id }}">
					<div class="envato-card {{ data.type }}">
						<div class="envato-card-top">
							<a href="{{ data.url }}" class="column-icon">
								<img src="{{ data.thumbnail_url }}"/>
							</a>
							<div class="column-name">
								<h4>
									<a href="{{ data.url }}">{{ data.name }}</a>
									<span class="version" aria-label="<?php esc_attr_e( 'Version %s', 'envato-market' ); ?>"><?php esc_html_e( 'Version', 'envato-market' ); ?> {{ data.version }}</span>
								</h4>
							</div>
							<div class="column-description">
								<div class="description">
									<p>{{ data.description }}</p>
								</div>
								<p class="author">
									<cite><?php esc_html_e( 'By', 'envato-market' ); ?> {{ data.author }}</cite>
								</p>
							</div>
						</div>
						<div class="envato-card-bottom">
							<div class="column-actions">
								<a href="{{{ data.install }}}" class="button button-primary">
									<span aria-hidden="true"><?php esc_html_e( 'Install', 'envato-market' ); ?></span>
									<span class="screen-reader-text"><?php esc_html_e( 'Install', 'envato-market' ); ?> {{ data.name }}</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</script>
			<?php
		}

		/**
		 * Registers the settings.
		 *
		 * @since 1.0.0
		 */
		public function register_settings() {
			// Setting.
			register_setting( envato_market()->get_slug(), envato_market()->get_option_name() );

			// OAuth section.
			add_settings_section(
				envato_market()->get_option_name() . '_oauth_section',
				__( 'Global OAuth Personal Token', 'envato-market' ),
				array( $this, 'render_oauth_section_callback' ),
				envato_market()->get_slug()
			);

			// Token setting.
			add_settings_field(
				'token',
				__( 'Token', 'envato-market' ),
				array( $this, 'render_token_setting_callback' ),
				envato_market()->get_slug(),
				envato_market()->get_option_name() . '_oauth_section'
			);

			// Items section.
			add_settings_section(
				envato_market()->get_option_name() . '_items_section',
				__( 'Single Use OAuth Personal Tokens', 'envato-market' ),
				array( $this, 'render_items_section_callback' ),
				envato_market()->get_slug()
			);

			// Items setting.
			add_settings_field(
				'items',
				__( 'Envato Market Items', 'envato-market' ),
				array( $this, 'render_items_setting_callback' ),
				envato_market()->get_slug(),
				envato_market()->get_option_name() . '_items_section'
			);
		}

		/**
		 * Redirect after the enable action runs.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function maybe_redirect() {
			if ( 'toplevel_page_' . envato_market()->get_slug() === get_current_screen()->id ) {

				if ( ! empty( $_GET['action'] ) && 'install-theme' === $_GET['action'] && ! empty( $_GET['enabled'] ) ) {
					wp_safe_redirect( esc_url( envato_market()->get_page_url() ) );
					exit;
				}
			}
		}

		/**
		 * Add authorization notices.
		 *
		 * @since 1.0.0
		 */
		public function add_notices() {
			if ( 'toplevel_page_' . envato_market()->get_slug() === get_current_screen()->id ) {

				// @codeCoverageIgnoreStart
				if ( isset( $_GET['authorization'] ) && 'check' === $_GET['authorization'] ) {
					self::authorization_redirect();
				}
				// @codeCoverageIgnoreEnd
				// Get the option array.
				$option = envato_market()->get_options();

				// Display success/error notices.
				if ( ! empty( $option['notices'] ) ) {
					self::delete_transients();

					// Show succes notice.
					if ( in_array( 'success', $option['notices'] ) ) {
						add_action( 'admin_notices', array( $this, 'render_success_notice' ) );
					}

					// Show succes no-items notice.
					if ( in_array( 'success-no-items', $option['notices'] ) ) {
						add_action( 'admin_notices', array( $this, 'render_success_no_items_notice' ) );
					}

					// Show single-use succes notice.
					if ( in_array( 'success-single-use', $option['notices'] ) ) {
						add_action( 'admin_notices', array( $this, 'render_success_single_use_notice' ) );
					}

					// Show error notice.
					if ( in_array( 'error', $option['notices'] ) ) {
						add_action( 'admin_notices', array( $this, 'render_error_notice' ) );
					}

					// Show single-use error notice.
					if ( in_array( 'error-single-use',$option['notices'] ) ) {
						add_action( 'admin_notices', array( $this, 'render_error_single_use_notice' ) );
					}

					// Update the saved data so the notice disappears on the next page load.
					unset( $option['notices'] );
					update_option( envato_market()->get_option_name(), $option );
				}
			}
		}

		/**
		 * Set the API values.
		 *
		 * @since 1.0.0
		 */
		public function set_items() {
			if ( 'toplevel_page_' . envato_market()->get_slug() === get_current_screen()->id ) {
				envato_market()->items()->set_themes();
				envato_market()->items()->set_plugins();
			}
		}

		/**
		 * Check for authorization and redirect.
		 *
		 * @since 1.0.0
		 * @access private
		 * @codeCoverageIgnore
		 */
		private function authorization_redirect() {
			self::authorization();
			wp_safe_redirect( esc_url( envato_market()->get_page_url() . '#settings' ) );
			exit;
		}

		/**
		 * Set the Envato API authorization value.
		 *
		 * @since 1.0.0
		 */
		public function authorization() {
			// Get the option array.
			$option = envato_market()->get_options();

			// Check for global token.
			if ( envato_market()->get_option( 'token' ) || envato_market()->api()->token ) {
				$failed = false;
				$option['notices'] = array();

				if ( 'error' === $this->authorize_total_items() ) {
					$failed = true;
				}

				if ( false === $failed ) {
					$themes_notice = $this->authorize_themes();
					if ( 'success-no-themes' === $themes_notice ) {
						$themes_empty = true;
					} elseif ( 'error' === $themes_notice ) {
						$failed = true;
					}
				}

				if ( false === $failed ) {
					$plugins_notice = $this->authorize_plugins();
					if ( 'success-no-plugins' === $plugins_notice ) {
						$plugins_empty = true;
					} elseif ( 'error' === $plugins_notice ) {
						$failed = true;
					}
				}

				if ( true === $failed ) {
					$option['notices'][] = 'error';
				} else {
					if ( false === $failed && isset( $themes_empty ) && isset( $plugins_empty ) ) {
						$option['notices'][] = 'success-no-items';
					} else {
						$option['notices'][] = 'success';
					}
				}
			}

			// Check for single-use token.
			if ( ! empty( $option['items'] ) ) {
				$failed = false;

				foreach ( $option['items'] as $key => $item ) {
					if ( empty( $item['name'] ) || empty( $item['token'] ) || empty( $item['id'] ) || empty( $item['type'] ) || empty( $item['authorized'] ) ) {
						continue;
					}

					$request_args = array(
						'headers' => array(
							'Authorization' => 'Bearer ' . $item['token'],
						),
					);

					// Uncached API response with single-use token.
					$response = envato_market()->api()->item( $item['id'], $request_args );

					if ( ! is_wp_error( $response ) && isset( $response['id'] ) ) {
						$option['items'][ $key ]['authorized'] = 'success';
					} else {
						$failed = true;
						$option['items'][ $key ]['authorized'] = 'failed';
					}
				}

				if ( true === $failed ) {
					$option['notices'][] = 'error-single-use';
				} else {
					$option['notices'][] = 'success-single-use';
				}
			}

			// Set the option array.
			if ( isset( $option['notices'] ) ) {
				update_option( envato_market()->get_option_name(), $option );
			}
		}

		/**
		 * Check that themes are authorized.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function authorize_total_items() {
			$response = envato_market()->api()->request( 'https://api.envato.com/v1/market/total-items.json' );
			$notice = 'success';

			if ( is_wp_error( $response ) || ! isset( $response['total-items'] ) ) {
				$notice = 'error';
			}

			return $notice;
		}

		/**
		 * Check that themes or plugins are authorized and downloadable.
		 *
		 * @since 1.0.0
		 *
		 * @param string $type The filter type, either 'themes' or 'plugins'. Default 'themes'.
		 * @return bool|null
		 */
		public function authorize_items( $type = 'themes' ) {
			$api_url = 'https://api.envato.com/v2/market/buyer/list-purchases?filter_by=wordpress-' . $type;
			$response = envato_market()->api()->request( $api_url );
			$notice = 'success';

			if ( is_wp_error( $response ) || empty( $response ) ) {
				$notice = 'error';
			} elseif ( empty( $response['results'] ) ) {
				$notice = 'success-no-' . $type;
			} else {
				shuffle( $response['results'] );
				$item = array_shift( $response['results'] );
				if ( ! isset( $item['item']['id'] ) || ! envato_market()->api()->download( $item['item']['id'] ) ) {
					$notice = 'error';
				}
			}

			return $notice;
		}

		/**
		 * Check that themes are authorized.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function authorize_themes() {
			return $this->authorize_items( 'themes' );
		}

		/**
		 * Check that plugins are authorized.
		 *
		 * @since 1.0.0
		 *
		 * @return bool
		 */
		public function authorize_plugins() {
			return $this->authorize_items( 'plugins' );
		}

		/**
		 * Install plugin.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 *
		 * @param string $plugin The plugin item ID.
		 */
		public function install_plugin( $plugin ) {
			if ( ! current_user_can( 'install_plugins' ) ) {
				$msg = '
				<div class="wrap">
					<h1>' . __( 'Installing Plugin...', 'envato-market' ) . '</h1>
					<p>' . __( 'You do not have sufficient permissions to install plugins on this site.', 'envato-market' ) . '</p>
					<a href="' . esc_url( 'admin.php?page=' . envato_market()->get_slug() . '&tab=plugins' ) . '">' . __( 'Return to Plugin Installer', 'envato-market' ) . '</a>
				</div>';
				wp_die( $msg );
			}

			check_admin_referer( 'install-plugin_' . $plugin );

			envato_market()->items()->set_plugins( true );
			$install = envato_market()->items()->plugins( 'install' );
			$api = new stdClass();

			foreach ( $install as $value ) {
				if ( absint( $value['id'] ) === absint( $plugin ) ) {
					$api->name = $value['name'];
					$api->version = $value['version'];
				}
			}

			$array_api = (array) $api;

			if ( empty( $array_api ) ) {
				$msg = '
				<div class="wrap">
					<h1>' . __( 'Installing Plugin...', 'envato-market' ) . '</h1>
					<p>' . __( 'An error occurred, please check that the item ID is correct.', 'envato-market' ) . '</p>
					<a href="' . esc_url( 'admin.php?page=' . envato_market()->get_slug() . '&tab=plugins' ) . '">' . __( 'Return to Plugin Installer', 'envato-market' ) . '</a>
				</div>';
				wp_die( $msg );
			}

			$title = sprintf( __( 'Installing Plugin: %s', 'envato-market' ), $api->name . ' ' . $api->version );
			$nonce = 'install-plugin_' . $plugin;
			$url = 'admin.php?page=' . envato_market()->get_slug() . '&action=install-plugin&plugin=' . urlencode( $plugin );
			$type = 'web'; // Install plugin type, From Web or an Upload.
			$api->download_link = envato_market()->api()->download( $plugin, $this->set_bearer_args( $plugin ) );

			// Must have the upgrader & skin.
			require envato_market()->get_plugin_path() . '/inc/admin/upgrader.php';
			require envato_market()->get_plugin_path() . '/inc/admin/upgrader-skins.php';

			$upgrader = new Envato_Market_Plugin_Upgrader( new Envato_Market_Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$upgrader->install( $api->download_link );
		}

		/**
		 * Install theme.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 *
		 * @param string $theme The theme item ID.
		 */
		public function install_theme( $theme ) {
			if ( ! current_user_can( 'install_themes' ) ) {
				$msg = '
				<div class="wrap">
					<h1>' . __( 'Installing Theme...', 'envato-market' ) . '</h1>
					<p>' . __( 'You do not have sufficient permissions to install themes on this site.', 'envato-market' ) . '</p>
					<a href="' . esc_url( 'admin.php?page=' . envato_market()->get_slug() . '&tab=themes' ) . '">' . __( 'Return to Theme Installer', 'envato-market' ) . '</a>
				</div>';
				wp_die( $msg );
			}

			check_admin_referer( 'install-theme_' . $theme );

			envato_market()->items()->set_themes( true );
			$install = envato_market()->items()->themes( 'install' );
			$api = new stdClass();

			foreach ( $install as $value ) {
				if ( absint( $value['id'] ) === absint( $theme ) ) {
					$api->name = $value['name'];
					$api->version = $value['version'];
				}
			}

			$array_api = (array) $api;

			if ( empty( $array_api ) ) {
				$msg = '
				<div class="wrap">
					<h1>' . __( 'Installing Theme...', 'envato-market' ) . '</h1>
					<p>' . __( 'An error occurred, please check that the item ID is correct.', 'envato-market' ) . '</p>
					<a href="' . esc_url( 'admin.php?page=' . envato_market()->get_slug() . '&tab=themes' ) . '">' . __( 'Return to Plugin Installer', 'envato-market' ) . '</a>
				</div>';
				wp_die( $msg );
			}

			wp_enqueue_script( 'customize-loader' );

			$title = sprintf( __( 'Installing Theme: %s', 'envato-market' ), $api->name . ' ' . $api->version );
			$nonce = 'install-theme_' . $theme;
			$url = 'admin.php?page=' . envato_market()->get_slug() . '&action=install-theme&theme=' . urlencode( $theme );
			$type = 'web'; // Install theme type, From Web or an Upload.
			$api->download_link = envato_market()->api()->download( $theme, $this->set_bearer_args( $theme ) );

			// Must have the upgrader & skin.
			require_once envato_market()->get_plugin_path() . '/inc/admin/upgrader.php';
			require_once envato_market()->get_plugin_path() . '/inc/admin/upgrader-skins.php';

			$upgrader = new Envato_Market_Theme_Upgrader( new Envato_Market_Theme_Installer_Skin( compact( 'title', 'url', 'nonce', 'api' ) ) );
			$upgrader->install( $api->download_link );
		}

		/**
		 * AJAX handler for upgrading a theme.
		 *
		 * @since 1.0.0
		 *
		 * @see Theme_Upgrader
		 * @codeCoverageIgnore
		 */
		public function ajax_upgrade_theme() {
			check_ajax_referer( 'updates' );

			global $wp_filesystem;

			$theme = urldecode( sanitize_file_name( trim( $_POST['theme'] ) ) );

			$status = array(
				'update'     => 'theme',
				'slug'       => $theme,
				'oldVersion' => '',
				'newVersion' => '',
			);

			$theme_data = wp_get_theme( $theme );
			if ( $theme_data->exists() && $theme_data->get( 'Version' ) ) {
				$status['oldVersion'] = sprintf( __( 'Version %s', 'envato-market' ), $theme_data->get( 'Version' ) );
			}

			if ( ! current_user_can( 'update_themes' ) ) {
				$status['error'] = __( 'You do not have sufficient permissions to update themes for this site.', 'envato-market' );
		 		wp_send_json_error( $status );
			}

			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

			$skin = new Automatic_Upgrader_Skin();
			$upgrader = new Theme_Upgrader( $skin );
			$result = $upgrader->bulk_upgrade( array( $theme ) );

			if ( is_array( $result ) && empty( $result[ $theme ] ) && is_wp_error( $skin->result ) ) {
				$result = $skin->result;
			}

			if ( is_array( $result ) && ! empty( $result[ $theme ] ) ) {
				$theme_update_data = current( $result );

				/*
				 * If the `update_themes` site transient is empty (e.g. when you update
				 * two themes in quick succession before the transient repopulates),
				 * this may be the return.
				 *
				 * Preferably something can be done to ensure `update_themes` isn't empty.
				 * For now, surface some sort of error here.
				 */
				if ( true === $theme_update_data ) {
		 			wp_send_json_error( $result );
				}

				$theme_data = wp_get_theme( $result[ $theme ]['destination_name'] );

				if ( $theme_data->exists() && $theme_data->get( 'Version' ) ) {
					$status['newVersion'] = sprintf( __( 'Version %s', 'envato-market' ), $theme_data->get( 'Version' ) );
				}

				wp_send_json_success( $status );
			} elseif ( is_wp_error( $result ) ) {
				$status['error'] = $result->get_error_message();
		 		wp_send_json_error( $status );

		 	} elseif ( is_bool( $result ) && ! $result ) {
				$status['errorCode'] = 'unable_to_connect_to_filesystem';
				$status['error'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'envato-market' );

				// Pass through the error from WP_Filesystem if one was raised.
				if ( is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
					$status['error'] = $wp_filesystem->errors->get_error_message();
				}

				wp_send_json_error( $status );
			}
		}

		/**
		 * AJAX handler for adding items that use a non global token.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function ajax_add_item() {
			if ( ! check_ajax_referer( self::AJAX_ACTION, 'nonce', false ) ) {
				status_header( 400 );
				wp_send_json_error( 'bad_nonce' );
			} elseif ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
				status_header( 405 );
				wp_send_json_error( 'bad_method' );
			} elseif ( empty( $_POST['token'] ) ) {
				wp_send_json_error( array( 'message' => __( 'The Token is missing.', 'envato-market' ) ) );
			} elseif ( empty( $_POST['id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'The Item ID is missing.', 'envato-market' ) ) );
			}

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $_POST['token'],
				),
			);

			$request = envato_market()->api()->item( $_POST['id'], $args );
			if ( false === $request ) {
				wp_send_json_error( array( 'message' => __( 'The Token or Item ID is incorrect.', 'envato-market' ) ) );
			}

			if ( false === envato_market()->api()->download( $_POST['id'], $args ) ) {
				wp_send_json_error( array( 'message' => __( 'The item cannot be downloaded.', 'envato-market' ) ) );
			}

			if ( isset( $request['number_of_sales'] ) ) {
				$type = 'plugin';
			} else {
				$type = 'theme';
			}

			if ( isset( $type ) ) {
				$response = array(
					'name'       => $request['name'],
					'token'      => $_POST['token'],
					'id'         => $_POST['id'],
					'type'       => $type,
					'authorized' => 'success',
				);

				$options = get_option( envato_market()->get_option_name(), array() );

				if ( ! empty( $options['items'] ) ) {
					$options['items'] = array_values( $options['items'] );
					$key = count( $options['items'] );
				} else {
					$options['items'] = array();
					$key = 0;
				}

				$options['items'][] = $response;

				update_option( envato_market()->get_option_name(), $options );

				// Rebuild the theme cache.
				if ( 'theme' === $type ) {
					envato_market()->items()->set_themes( true, false );

					$install_link = add_query_arg( array(
						'page'   => envato_market()->get_slug(),
						'action' => 'install-theme',
						'id'     => $_POST['id'],
					), self_admin_url( 'admin.php' ) );

					$request['install'] = wp_nonce_url( $install_link, 'install-theme_' . $_POST['id'] );
				}

				// Rebuild the plugin cache.
				if ( 'plugin' === $type ) {
					envato_market()->items()->set_plugins( true, false );

					$install_link = add_query_arg( array(
						'page'   => envato_market()->get_slug(),
						'action' => 'install-plugin',
						'id'     => $_POST['id'],
					), self_admin_url( 'admin.php' ) );

					$request['install'] = wp_nonce_url( $install_link, 'install-plugin_' . $_POST['id'] );
				}

				$response['key'] = $key;
				$response['item'] = $request;
				wp_send_json_success( $response );
			}

			wp_send_json_error( array( 'message' => __( 'An unknown error occurred.', 'envato-market' ) ) );
		}

		/**
		 * AJAX handler for removing items that use a non global token.
		 *
		 * @since 1.0.0
		 * @codeCoverageIgnore
		 */
		public function ajax_remove_item() {
			if ( ! check_ajax_referer( self::AJAX_ACTION, 'nonce', false ) ) {
				status_header( 400 );
				wp_send_json_error( 'bad_nonce' );
			} elseif ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
				status_header( 405 );
				wp_send_json_error( 'bad_method' );
			} elseif ( empty( $_POST['id'] ) ) {
				wp_send_json_error( array( 'message' => __( 'The Item ID is missing.', 'envato-market' ) ) );
			}

			$options = get_option( envato_market()->get_option_name(), array() );
			$type = '';

			foreach ( $options['items'] as $key => $item ) {
				if ( $item['id'] === $_POST['id'] ) {
					$type = $item['type'];
					unset( $options['items'][ $key ] );
					break;
				}
			}
			$options['items'] = array_values( $options['items'] );

			update_option( envato_market()->get_option_name(), $options );

			// Rebuild the theme cache.
			if ( 'theme' === $type ) {
				envato_market()->items()->set_themes( true, false );
			}

			// Rebuild the plugin cache.
			if ( 'plugin' === $type ) {
				envato_market()->items()->set_plugins( true, false );
			}

			wp_send_json_success();
		}

		/**
		 * Admin page callback.
		 *
		 * @since 1.0.0
		 */
		public function render_admin_callback() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/callback/admin.php' );
		}

		/**
		 * OAuth section callback.
		 *
		 * @since 1.0.0
		 */
		public function render_oauth_section_callback() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/callback/section/oauth.php' );
		}

		/**
		 * Items section callback.
		 *
		 * @since 1.0.0
		 */
		public function render_items_section_callback() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/callback/section/items.php' );
		}

		/**
		 * Token setting callback.
		 *
		 * @since 1.0.0
		 */
		public function render_token_setting_callback() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/callback/setting/token.php' );
		}

		/**
		 * Items setting callback.
		 *
		 * @since 1.0.0
		 */
		public function render_items_setting_callback() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/callback/setting/items.php' );
		}

		/**
		 * Intro
		 *
		 * @since 1.0.0
		 */
		public function render_intro_partial() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/partials/intro.php' );
		}

		/**
		 * Tabs
		 *
		 * @since 1.0.0
		 */
		public function render_tabs_partial() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/partials/tabs.php' );
		}

		/**
		 * Settings panel
		 *
		 * @since 1.0.0
		 */
		public function render_settings_panel_partial() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/partials/settings.php' );
		}

		/**
		 * Themes panel
		 *
		 * @since 1.0.0
		 */
		public function render_themes_panel_partial() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/partials/themes.php' );
		}

		/**
		 * Plugins panel
		 *
		 * @since 1.0.0
		 */
		public function render_plugins_panel_partial() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/partials/plugins.php' );
		}

		/**
		 * Success notice.
		 *
		 * @since 1.0.0
		 */
		public function render_success_notice() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/notice/success.php' );
		}

		/**
		 * Success no-items notice.
		 *
		 * @since 1.0.0
		 */
		public function render_success_no_items_notice() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/notice/success-no-items.php' );
		}

		/**
		 * Success single-use notice.
		 *
		 * @since 1.0.0
		 */
		public function render_success_single_use_notice() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/notice/success-single-use.php' );
		}

		/**
		 * Error notice.
		 *
		 * @since 1.0.0
		 */
		public function render_error_notice() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/notice/error.php' );
		}

		/**
		 * Error single-use notice.
		 *
		 * @since 1.0.0
		 */
		public function render_error_single_use_notice() {
			require( envato_market()->get_plugin_path() . 'inc/admin/view/notice/error-single-use.php' );
		}
	}

endif;
