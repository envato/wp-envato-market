<?php
/**
 * Error details
 *
 * @package Envato_Market
 * @since 2.0.2
 */

?>
<div class="notice notice-error is-dismissible">
	<p><?php printf( '<strong>Additional Error Details:</strong><br/>%s.<br/> %s <br/> %s', esc_html( $title ), esc_html( $message ), esc_html( json_encode( $data ) ) ); ?></p>
</div>
