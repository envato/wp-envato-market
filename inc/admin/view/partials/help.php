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
			<h3><?php esc_html_e( 'Troubleshooting:', 'envato-market' ); ?></h3>
			<p><?php esc_html_e( 'If you\'re having trouble with the plugin, please', 'envato-market' ); ?></p>
			<ol>
				<li><?php printf(esc_html__( 'Confirm the old %1$sEnvato Toolkit%2$s plugin is not installed.', 'envato-market' ),'<code>','</code>'); ?></li>
				<li><?php esc_html_e( 'Confirm the latest version of WordPress is installed.', 'envato-market' ); ?></li>
				<li><?php printf(esc_html__( 'Confirm the latest version of the %1$sEnvato Market%2$s plugin is installed.', 'envato-market' ),'<a href="https://envato.com/market-plugin/" target="_blank">','</a>'); ?></li>
				<li><?php printf(esc_html__( 'Try creating a new API token has from the %1$sbuild.envato.com%2$s website - ensure only the following permissions have been granted', 'envato-market' ),'<a href="' . esc_url( envato_market()->admin()->get_generate_token_url() ) . '" target="_blank">','</a>'); ?>
					<ul>
						<li><?php esc_html_e( 'View and search Envato sites', 'envato-market' ); ?></li>
						<li><?php esc_html_e( 'Download your purchased items', 'envato-market' ); ?></li>
						<li><?php esc_html_e( 'List purchases you\'ve made', 'envato-market' ); ?></li>
					</ul>
				</li>
				<li><?php printf(esc_html__( 'Check with the hosting provider to ensure the API connection to %1$sapi.envato.com%2$s is not blocked.', 'envato-market' ),'<code>','</code>'); ?></li>
				<li><?php esc_html_e( 'Check with the hosting provider that the minimum TLS version is 1.2 or above on the server.', 'envato-market' ); ?></li>
				<li><?php esc_html_e( 'If you can\'t see your items - check with the item author to confirm the Theme or Plugin is compatible with the Envato Market plugin.', 'envato-market' ); ?></li>
				<li><?php printf(esc_html__( 'Confirm your Envato account is still active and the items are still visible from %1$syour downloads page%2$s.', 'envato-market' ),'<a href="https://themeforest.net/downloads" target="_blank">','</a>'); ?></li>
				<li><?php esc_html_e( 'Note - if an item has been recently updated, it may take up to 24 hours for the latest version to appear in the Envato Market plugin.', 'envato-market' ); ?></li>
			</ol>
		</div>
		<div class="envato-market-block">
			<h3><?php esc_html_e( 'Health Check:', 'envato-market' ); ?></h3>
			<div class="envato-market-healthcheck">
				<?php esc_html_e( 'Problem starting healthcheck. Please check JavaScript console for errors.', 'envato-market' ); ?>
			</div>
			<h3><?php esc_html_e( 'Support:', 'envato-market' ); ?></h3>
			<p><?php esc_html_e( 'The Envato Market plugin is maintained - we ensure it works best on the latest version of WordPress and on a modern hosting platform, however we can\'t guarantee it\'ll work on all WordPress sites or hosting environments.', 'envato-market' ); ?></p>
			<p><?php esc_html_e( 'If you\'ve tried all the troubleshooting steps and you’re still unable to get the Envato Market plugin to work on your site/hosting, at this time, our advice is to remove the Envato Market plugin and instead visit the Downloads section of ThemeForest/CodeCanyon to download the latest version of your items.', 'envato-market' ); ?></p>
			<p><?php esc_html_e( 'If you\'re having trouble with a specific item from ThemeForest or CodeCanyon, it’s best you browse to the Theme or Plugin item page, visit the ‘support’ tab and follow the next steps.', 'envato-market' ); ?>
			</p>
		</div>
	</div>
</div>
