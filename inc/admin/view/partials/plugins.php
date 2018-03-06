<?php
/**
 * Plugins panel partial
 *
 * @package Envato_Market
 * @since 1.0.0
 */

$plugins = envato_market()->items()->plugins( 'purchased' );

?>
<div id="plugins" class="panel <?php echo empty( $plugins ) ? 'hidden' : ''; ?>">
	<div class="envato-market-blocks">
		<?php
		if ( ! empty( $plugins ) ) {
			envato_market_plugins_column( 'active' );
			envato_market_plugins_column( 'installed' );
			envato_market_plugins_column( 'install' );
		}
		?>
	</div>
</div>
