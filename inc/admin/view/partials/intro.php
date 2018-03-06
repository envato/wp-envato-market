<?php
/**
 * Intro partial
 *
 * @package Envato_Market
 * @since 1.0.0
 */

?>
<div class="col">

	<h1 class="about-title"><img class="about-logo" src="<?php echo envato_market()->get_plugin_url(); ?>images/envato-market-logo.svg" alt="Envato Market"><sup><?php echo esc_html( envato_market()->get_version() ); ?></sup></h1>
	<p><?php esc_html_e( 'Welcome!', 'envato-market' ); ?></p>
	<p><?php esc_html_e( 'This plugin can install WordPress themes and plugins purchased from ThemeForest & CodeCanyon by connecting with the Envato Market API using a secure OAuth personal token. Once your themes & plugins are installed WordPress will periodically check for updates, so keeping your items up to date is as simple as a few clicks.', 'envato-market' ); ?></p>
	<p><strong><?php printf( esc_html__( 'Find out more at %1$senvato.com%2$s.', 'envato-market' ), '<a href="https://envato.com/market-plugin/" target="_blank">', '</a>' ); ?></strong></p>
</div>
