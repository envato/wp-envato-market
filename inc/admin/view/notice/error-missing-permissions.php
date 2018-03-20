<?php
/**
 * Error notice
 *
 * @package Envato_Market
 * @since 2.0.1
 */

?>
<div class="notice notice-error is-dismissible">
	<p><?php esc_html_e( sprintf( 'Not enough Personal Token permissions. Please ensure %s permissions are enabled.', sprintf('"%s"', implode('", "', $this->get_required_permissions() ) ) ), 'envato-market' ); ?></p>
</div>
