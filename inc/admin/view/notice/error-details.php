<?php
/**
 * Error details
 *
 * @package Envato_Market
 * @since 2.0.2
 */

?>
<div class="notice notice-error is-dismissible">
	<p>
		<strong><?php esc_html_e( 'Additional Error Details:', 'envato-market' ); ?></strong>
		<?php if ( ! empty( $title ) ) : ?>
			<br/><?php printf( esc_html__( 'Title: %s', 'envato-market' ), esc_html( $title ) ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $message ) ) : ?>
			<br/><?php printf( esc_html__( 'Message: %s', 'envato-market' ), esc_html( $message ) ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $data ) ) : ?>
			<br/><?php printf( esc_html__( 'Data: %s', 'envato-market' ), esc_html( json_encode( $data ) ) ); ?>
		<?php endif; ?>
	</p>
</div>
