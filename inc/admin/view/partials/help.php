<?php
/**
 * Help panel partial
 *
 * @package Envato_Market
 * @since 2.0.1
 */


?>
<div id="help" class="panel">
	<div class="envato-market-blocks">
		<div class="envato-market-block">
			<h3><?php esc_html_e( 'Help:', 'envato-market' ); ?></h3>
			<p><?php esc_html_e( 'When help is required using this plugin please follow these steps:', 'envato-market' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'First check the FAQ to see if the problem can be solved.', 'envato-market' ); ?></li>
				<li><?php printf( esc_html__( 'Confirm the old %1$sEnvato Toolkit%2$s plugin is not installed.', 'envato-market' ), '<code>', '</code>' ); ?></li>
				<li><?php esc_html_e( 'Confirm the latest version of WordPress is installed.', 'envato-market' ); ?></li>
				<li><?php printf( esc_html__( 'Confirm the latest version of the %1$sEnvato Market%2$s plugin is installed.', 'envato-market' ), '<a href="https://envato.com/market-plugin/" target="_blank">', '</a>' ); ?></li>
				<li><?php printf( esc_html__( 'Check with the hosting provider to ensure the API connection to %1$sapi.envato.com%2$s is not blocked.', 'envato-market' ), '<code>', '</code>' ); ?></li>
				<li><?php esc_html_e( 'Check with the item author to confirm the Theme or Plugin is compatible with the Envato Market plugin.', 'envato-market' ); ?></li>
				<li><?php printf( esc_html__( 'Confirm a valid API token has been generated from the %1$sbuild.envato.com%2$s website.', 'envato-market' ), '<a href="'. envato_market()->admin()->get_generate_token_url() .'" target="_blank">', '</a>' ); ?></li>
				<li><?php printf( esc_html__( 'Confirm your Envato account is still active and the items are still visible from %1$syour downloads page%2$s.', 'envato-market' ), '<a href="https://themeforest.net/downloads" target="_blank">', '</a>' ); ?></li>
				<li><?php printf( esc_html__( 'If this still doesn\'t solve the issue, please send us an email to %1$smarketpluginsupport@envato.com%2$s. We may ask for your Envato username, the item you are trying to install/update, and some temporary WordPress login details so we can debug any issues.', 'envato-market' ), '<a href="mailto:marketpluginsupport@envato.com">', '</a>' ); ?></li>
			</ol>
		</div>
		<div class="envato-market-block">
			<h3><?php esc_html_e( 'Common Problems:', 'envato-market' ); ?></h3>
			<p><?php esc_html_e( 'Common problems and how to solve them.', 'envato-market' ); ?></p>
			<ol>
				<li>
					<strong><?php esc_html_e( 'Purchased items do not display in the Themes or Plugins area:', 'envato-market' ); ?></strong><br/>
					<?php printf( esc_html__( 'Go to %1$sbuild.envato.com%2$s and log out, then log back in, and then generate a new API token.', 'envato-market' ), '<a href="https://build.envato.com" target="_blank">', '</a>' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'An item will not install or update:', 'envato-market' ); ?></strong> <br/>
					<?php printf( esc_html__( 'Please install or update the item manually. The item can be downloaded from %1$syour downloads page%2$s. Please contact the item author for instructions on how to install or update the item manually.', 'envato-market' ), '<a href="https://themeforest.net/downloads" target="_blank">', '</a>' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'It says my hosting provider is blocked:', 'envato-market' ); ?></strong> <br/>
					<?php printf( esc_html__( 'Please contact your hosting provider and ask if connections to %1$sapi.envato.com%2$s are blocked.', 'envato-market' ), '<code>', '</code>' ); ?>
				</li>
			</ol>
		</div>
	</div>
</div>
