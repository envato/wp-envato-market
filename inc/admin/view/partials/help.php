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
			<h3>Troubleshooting:</h3>
			<p>If you’re having trouble with the plugin, please</p>
			<ol>
				<li>Confirm the old <code>Envato Toolkit</code> plugin is not installed.</li>
				<li>Confirm the latest version of WordPress is installed.</li>
				<li>Confirm the latest version of the <a href="https://envato.com/market-plugin/" target="_blank">Envato Market</a> plugin is installed.</li>
				<li>Try creating a new API token has from the <a href="<?php echo envato_market()->admin()->get_generate_token_url(); ?>" target="_blank">build.envato.com</a> website - ensure only the following permissions have been granted
					<ul>
						<li>View and search Envato sites</li>
						<li>Download your purchased items</li>
						<li>List purchases you've made</li>
					</ul>
				</li>
				<li>Check with the hosting provider to ensure the API connection to <code>api.envato.com</code> is not blocked.</li>
				<li>Check with the hosting provider that the minimum TLS version is 1.2 or above on the server.</li>
				<li>If you can’t see your items - check with the item author to confirm the Theme or Plugin is compatible with the Envato Market plugin.</li>
				<li>Confirm your Envato account is still active and the items are still visible from <a href="https://themeforest.net/downloads" target="_blank">your downloads page</a>.</li>
				<li>Note - if an item has been recently updated, it may take up to 24 hours for the latest version to appear in the Envato Market plugin.</li>
			</ol>
		</div>
		<div class="envato-market-block">
			<h3>Health Check:</h3>
			<div class="envato-market-healthcheck">
				Problem starting healthcheck. Please check javascript console for errors.
			</div>
			<h3>Support:</h3>
			<p>The Envato Market plugin is maintained - we ensure it works best on the latest version of WordPress and on a modern hosting platform, however we can’t guarantee it’ll work on all WordPress sites or hosting environments.</p>
			<p>If you’ve tried all the troubleshooting steps and you’re still unable to get the Envato Market plugin to work on your site/hosting, at this time, our advice is to remove the Envato Market plugin and instead visit the Downloads section of ThemeForest/CodeCanyon to download the latest version of your items.</p>
			<p>If you’re having trouble with a specific item from ThemeForest or CodeCanyon, it’s best you browse to the Theme or Plugin item page, visit the ‘support’ tab and follow the next steps.
			</p>
		</div>
	</div>
</div>
