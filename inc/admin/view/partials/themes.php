<?php
/**
 * Themes panel partial
 *
 * @package Envato_Market
 * @since 1.0.0
 */

$themes = envato_market()->items()->themes( 'purchased' );

?>
<div id="themes" class="panel <?php echo empty( $themes ) ? 'hidden' : ''; ?>">
	<div class="envato-market-blocks">
		<?php
		if ( ! empty( $themes ) ) {
			envato_market_themes_column( 'active' );
			envato_market_themes_column( 'installed' );
			envato_market_themes_column( 'install' );
		}
		?>
	</div>
</div>
