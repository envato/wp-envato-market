<?php
/**
 * Settings panel partial
 *
 * @package Envato_Market
 * @since 1.0.0
 */

$token = envato_market()->get_option( 'token' );
$items = envato_market()->get_option( 'items', array() );

?>
<div id="settings" class="two-col panel">
	<?php settings_fields( envato_market()->get_slug() ); ?>
	<?php Envato_Market_Admin::do_settings_sections( envato_market()->get_slug(), 2 ); ?>
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'envato-market' ); ?>" />
		<?php if ( ( '' !== $token || ! empty( $items ) ) && 10 !== has_action( 'admin_notices', array( $this, 'error_notice' ) ) ) { ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'authorization' => 'check' ), envato_market()->get_page_url() ) ); ?>" class="button button-secondary auth-check-button" style="margin:0 5px"><?php esc_html_e( 'Test API Connection', 'envato-market' ); ?></a>
		<?php } ?>
	</p>
</div>
