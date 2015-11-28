<?php
/**
 * OAuth section
 *
 * @package Envato_Market
 * @since 1.0.0
 */

?>
<p><?php esc_html_e( 'OAuth is a protocol that lets external apps request authorization to private details in a user\'s Envato Market account without entering their password. This is preferred over Basic Authentication because tokens can be limited to specific types of data, and can be revoked by users at any time.', 'envato-market' ); ?></p>

<p><?php printf( esc_html__( 'You will need to %s, and then insert it below.', 'envato-market' ), '<a href="https://build.envato.com/create-token/?purchase:download=t&purchase:verify=t&purchase:list=t" target="_blank">' . esc_html__( 'generate a personal token', 'envato-market' ) . '</a>' ); ?></p>
