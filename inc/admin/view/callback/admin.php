<?php
/**
 * Admin UI
 *
 * @package Envato_Market
 * @since 1.0.0
 */

if ( isset( $_GET['action'] ) ) {
	$id = ! empty( $_GET['id'] ) ? absint( trim( $_GET['id'] ) ) : '';

	if ( 'install-plugin' === $_GET['action'] ) {
		Envato_Market_Admin::install_plugin( $id );
	} elseif ( 'install-theme' === $_GET['action'] ) {
		Envato_Market_Admin::install_theme( $id );
	}
} else {
	add_thickbox();
	?>
	<div class="wrap about-wrap full-width-layout">
		<?php Envato_Market_Admin::render_intro_partial(); ?>
		<?php Envato_Market_Admin::render_tabs_partial(); ?>
		<form method="POST" action="<?php echo esc_url( ENVATO_MARKET_NETWORK_ACTIVATED ? network_admin_url( 'edit.php?action=envato_market_network_settings' ) : admin_url( 'options.php' ) ); ?>">
			<?php Envato_Market_Admin::render_themes_panel_partial(); ?>
			<?php Envato_Market_Admin::render_plugins_panel_partial(); ?>
			<?php Envato_Market_Admin::render_settings_panel_partial(); ?>
			<?php Envato_Market_Admin::render_help_panel_partial(); ?>
		</form>
	</div>
	<?php
}
