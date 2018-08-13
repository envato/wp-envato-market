<?php
/**
 * Tabs partial
 *
 * @package Envato_Market
 * @since 1.0.0
 */

$tab     = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
$themes  = envato_market()->items()->themes( 'purchased' );
$plugins = envato_market()->items()->plugins( 'purchased' );

?>
<h2 class="nav-tab-wrapper">
	<?php
	// Themes tab.
	$theme_class = array();
	if ( ! empty( $themes ) ) {
		if ( empty( $tab ) ) {
			$tab = 'themes';
		}
		if ( 'themes' === $tab ) {
			$theme_class[] = 'nav-tab-active';
		}
	} else {
		$theme_class[] = 'hidden';
	}
	echo '<a href="#themes" data-id="theme" class="nav-tab ' . esc_attr( implode( ' ', $theme_class ) ) . '">' . esc_html__( 'Themes', 'envato-market' ) . '</a>';

	// Plugins tab.
	$plugin_class = array();
	if ( ! empty( $plugins ) ) {
		if ( empty( $tab ) ) {
			$tab = 'plugins';
		}
		if ( 'plugins' === $tab ) {
			$plugin_class[] = 'nav-tab-active';
		}
	} else {
		$plugin_class[] = 'hidden';
	}
	echo '<a href="#plugins" data-id="plugin" class="nav-tab ' . esc_attr( implode( ' ', $plugin_class ) ) . '">' . esc_html__( 'Plugins', 'envato-market' ) . '</a>';

	// Settings tab.
	echo '<a href="#settings" class="nav-tab ' . esc_attr( 'settings' === $tab || empty( $tab ) ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Settings', 'envato-market' ) . '</a>';

	// Help tab.
	echo '<a href="#help" class="nav-tab ' . esc_attr( 'help' === $tab ? 'nav-tab-active' : '' ) . '">' . esc_html__( 'Help', 'envato-market' ) . '</a>';
	?>
</h2>
