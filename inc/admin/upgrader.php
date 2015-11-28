<?php
/**
 * Theme Upgrader class.
 *
 * @package Envato_Market
 */

// Include the WP_Upgrader class.
if ( ! class_exists( 'WP_Upgrader', false ) ) :
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
endif;

if ( ! class_exists( 'Envato_Market_Theme_Upgrader' ) ) :

	/**
	 * Extends the WordPress Theme_Upgrader class.
	 *
	 * This class makes modifications to the strings during install & upgrade.
	 *
	 * @class Envato_Market_Plugin_Upgrader
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_Theme_Upgrader extends Theme_Upgrader {

		/**
		 * Initialize the upgrade strings.
		 *
		 * @since 1.0.0
		 */
		public function upgrade_strings() {
			parent::upgrade_strings();

			$this->strings['downloading_package'] = __( 'Downloading the Envato Market upgrade package&#8230;', 'envato-market' );
		}

		/**
		 * Initialize the install strings.
		 *
		 * @since 1.0.0
		 */
		public function install_strings() {
			parent::install_strings();

			$this->strings['downloading_package'] = __( 'Downloading the Envato Market install package&#8230;', 'envato-market' );
		}
	}

endif;

if ( ! class_exists( 'Envato_Market_Plugin_Upgrader' ) ) :

	/**
	 * Extends the WordPress Plugin_Upgrader class.
	 *
	 * This class makes modifications to the strings during install & upgrade.
	 *
	 * @class Envato_Market_Plugin_Upgrader
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	class Envato_Market_Plugin_Upgrader extends Plugin_Upgrader {

		/**
		 * Initialize the upgrade strings.
		 *
		 * @since 1.0.0
		 */
		public function upgrade_strings() {
			parent::upgrade_strings();

			$this->strings['downloading_package'] = __( 'Downloading the Envato Market upgrade package&#8230;', 'envato-market' );
		}

		/**
		 * Initialize the install strings.
		 *
		 * @since 1.0.0
		 */
		public function install_strings() {
			parent::install_strings();

			$this->strings['downloading_package'] = __( 'Downloading the Envato Market install package&#8230;', 'envato-market' );
		}
	}

endif;
