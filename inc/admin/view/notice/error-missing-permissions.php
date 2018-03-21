<?php
/**
 * Error notice
 *
 * @package Envato_Market
 * @since 2.0.1
 */

?>
<div class="notice notice-error is-dismissible">
	<p><?php printf( esc_html__( 'Not enough Personal Token permissions. Please ensure %s permissions are enabled.', 'envato-market' ), sprintf( '"%s"', implode( '", "', $this->get_required_permissions() ) ) ); ?></p>
</div>
