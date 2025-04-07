<?php
/**
 * Error notice
 *
 * @package Envato_Market
 * @since 2.0.1
 */

?>
<div class="notice notice-error is-dismissible">
	<p><?php esc_html_e( 'Incorrect token permissions, please generate another token or fix the permissions on the existing token.', 'envato-market' ); ?></p>
	<p><?php esc_html_e( 'Please ensure only the following permissions are enabled: ', 'envato-market' ); ?></p>
	<ol>
		<?php foreach ( $this->get_required_permissions() as $permission ) { ?>
				<li><?php echo esc_html( $permission ); ?></li>
		<?php } ?>
	</ol>
</div>
