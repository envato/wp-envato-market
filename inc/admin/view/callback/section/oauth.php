<?php
/**
 * OAuth section
 *
 * @package Envato_Market
 * @since 1.0.0
 */

?>

<p>
	<?php printf( esc_html__( 'This area enables WordPress Theme &amp; Plugin updates from Envato Market. Read more about how this process works at %s. Please follow the steps below:', 'envato-market' ), '<a href="https://envato.com/market-plugin/" target="_blank">' . esc_html__( 'envato.com', 'envato-market' ) . '</a>' ); ?>
</p>
<ol>
	<li><?php printf( esc_html__( 'Generate an Envato API Personal Token by %s.', 'envato-market' ), '<a href="' . envato_market()->admin()->get_generate_token_url() . '" target="_blank">' . esc_html__( 'clicking this link', 'envato-market' ) . '</a>' ); ?></li>
	<li><?php esc_html_e( 'Copy the token into the box below.', 'envato-market' ); ?></li>
	<li><?php esc_html_e( 'Click the "Save Changes" button.', 'envato-market' ); ?></li>
	<li><?php esc_html_e( 'A list of purchased Themes &amp; Plugins from Envato Market will appear.', 'envato-market' ); ?></li>
</ol>
