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
			<h3>Help:</h3>
			<p>When help is required using this plugin please follow these steps:</p>
			<ol>
				<li>First check the FAQ to see if the problem can be solved.</li>
				<li>Confirm the old <code>Envato Toolkit</code> plugin is not installed.</li>
				<li>Confirm the latest version of WordPress is installed.</li>
				<li>Confirm the latest version of the <a href="https://envato.com/market-plugin/" target="_blank">Envato Market</a> plugin is installed.</li>
				<li>Check with the hosting provider to ensure the API connection to <code>api.envato.com</code> is not blocked.</li>
				<li>Check with the item author to confirm the Theme or Plugin is compatible with the Envato Market plugin.</li>
				<li>Confirm a valid API token has been generated from the <a href="<?php echo envato_market()->admin()->get_generate_token_url();?>" target="_blank">build.envato.com</a> website.</li>
				<li>Confirm your Envato account is still active and the items are still visible from <a href="https://themeforest.net/downloads" target="_blank">your downloads page</a>.</li>
				<li>If this still doesn't solve the issue, please send us an email to <a href="mailto:marketpluginsupport@envato.com">marketpluginsupport@envato.com</a>. We may ask for your Envato username, the item you are trying to install/update, and some temporary WordPress login details so we can debug any issues. </li>
			</ol>
		</div>
		<div class="envato-market-block">
			<h3>Common Problems:</h3>
			<p>Common problems and how to solve them.</p>
			<ol>
				<li>
					<strong>Purchased items do not display in the Themes or Plugins area:</strong> <br/>
					Go to <a href="https://build.envato.com" target="_blank">build.envato.com</a> and log out, then log back in, and then generate a new API token.
				</li>
				<li>
					<strong>An item will not install or update:</strong> <br/>
					Please install or update the item manually. The item can be downloaded from <a href="https://themeforest.net/downloads" target="_blank">your downloads page</a>. Please contact the item author for instructions on how to install or update the item manually.
				</li>
				<li>
					<strong>It says my hosting provider is blocked:</strong> <br/>
					Please contact your hosting provider and ask if connections to <code>api.envato.com</code> are blocked.
				</li>
			</ol>
		</div>
	</div>
</div>
