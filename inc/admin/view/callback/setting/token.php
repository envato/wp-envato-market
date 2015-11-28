<?php
/**
 * Token setting
 *
 * @package Envato_Market
 * @since 1.0.0
 */

?>
<input type="text" name="<?php echo esc_attr( envato_market()->get_option_name() ); ?>[token]" class="widefat" value="<?php echo esc_html( envato_market()->get_option( 'token' ) ); ?>" autocomplete="off">

<p class="description"><?php esc_html_e( 'Enter your Envato API Personal Token.', 'envato-market' ); ?></p>
