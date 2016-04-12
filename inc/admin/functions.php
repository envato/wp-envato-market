<?php
/**
 * Functions
 *
 * @package Envato_Market
 */

/**
 * Interate over the themes array and displays each theme.
 *
 * @since 1.0.0
 *
 * @param string $group The theme group. Options are 'purchased', 'active', 'installed', or 'install'.
 */
function envato_market_themes_column( $group = 'install' ) {
	$premium = envato_market()->items()->themes( $group );
	if ( empty( $premium ) ) {
		return;
	}

	foreach ( $premium as $slug => $theme ) :
		$name = $theme['name'];
		$author = $theme['author'];
		$version = $theme['version'];
		$description = $theme['description'];
		$url = $theme['url'];
		$author_url = $theme['author_url'];
		$theme['hasUpdate'] = false;

		if ( 'active' === $group || 'installed' === $group ) {
			$get_theme = wp_get_theme( $slug );
			if ( $get_theme->exists() ) {
				$name = $get_theme->get( 'Name' );
				$author = $get_theme->get( 'Author' );
				$version = $get_theme->get( 'Version' );
				$description = $get_theme->get( 'Description' );
				$url = $get_theme->get( 'ThemeURI' );
				$author_url = $get_theme->get( 'AuthorURI' );
				if ( version_compare( $version, $theme['version'], '<' ) ) {
					$theme['hasUpdate'] = true;
				}
			}
		}

		// Setup the column CSS classes.
		$classes = array( 'envato-card', 'theme' );

		if ( 'active' === $group ) {
			$classes[] = 'active';
		}

		// Setup the update action links.
		$updateActions = array();

		if ( true === $theme['hasUpdate'] ) {
			$classes[] = 'update';
			$classes[] = 'envato-card-' . esc_attr( $slug );

			if ( current_user_can( 'update_themes' ) ) {
				// Upgrade link.
				$upgrade_link = add_query_arg( array(
					'action' => 'upgrade-theme',
					'theme'  => esc_attr( $slug ),
				), self_admin_url( 'update.php' ) );

				$updateActions['update'] = sprintf(
					'<a class="update-now" href="%1$s" aria-label="%2$s" data-name="%3$s %5$s" data-slug="%4$s" data-version="%5$s">%6$s</a>',
					wp_nonce_url( $upgrade_link, 'upgrade-theme_' . $slug ),
					esc_attr__( 'Update %s now', 'envato-market' ),
					esc_attr( $name ),
					esc_attr( $slug ),
					esc_attr( $theme['version'] ),
					esc_html__( 'Update Available', 'envato-market' )
				);

				// Details link.
				$details_link = add_query_arg( array(
					'TB_iframe' => 'true',
					'width'     => 640,
					'height'    => 662,
				), $url );

				$updateActions['details'] = sprintf(
					'<a href="%1$s" class="thickbox details" title="%2$s">%3$s</a>',
					esc_url( $details_link ),
					esc_attr( $name ),
					sprintf(
						__( 'View version %1$s details.', 'envato-market' ),
						$theme['version']
					)
				);
			}
		}

		// Setup the action links.
		$actions = array();

		if ( 'active' === $group && current_user_can( 'edit_theme_options' ) && current_user_can( 'customize' ) ) {
			// Customize theme.
			$customize_url = admin_url( 'customize.php' );
			$customize_url .= '?theme=' . urlencode( $slug );
			$customize_url .= '&return=' . urlencode( envato_market()->get_page_url() . '#themes' );
			$actions['customize'] = '<a href="' . esc_url( $customize_url ) . '" class="button button-primary load-customize hide-if-no-customize"><span aria-hidden="true">' . __( 'Customize', 'envato-market' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Customize &#8220;%s&#8221;', 'envato-market' ), $name ) . '</span></a>';
		} elseif ( 'installed' === $group ) {
			$can_activate = true;

			// @codeCoverageIgnoreStart
			// Multisite needs special attention.
			if ( is_multisite() && ! $get_theme->is_allowed( 'both' ) && current_user_can( 'manage_sites' ) ) {
				$can_activate = false;

				if ( current_user_can( 'manage_sites' ) ) {
					$actions['site_enable'] = '<a href="' . esc_url( network_admin_url( wp_nonce_url( 'site-themes.php?id=' . get_current_blog_id() . '&amp;action=enable&amp;theme=' . urlencode( $slug ), 'enable-theme_' . $slug ) ) ) . '" class="button"><span aria-hidden="true">' . __( 'Site Enable', 'envato-market' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Site Enable &#8220;%s&#8221;', 'envato-market' ), $name ) . '</span></a>';
				}

				if ( current_user_can( 'manage_network_themes' ) ) {
					$actions['network_enable'] = '<a href="' . esc_url( network_admin_url( wp_nonce_url( 'themes.php?action=enable&amp;theme=' . urlencode( $slug ) . '&amp;paged=1&amp;s', 'enable-theme_' . $slug ) ) ) . '" class="button"><span aria-hidden="true">' . __( 'Network Enable', 'envato-market' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Network Enable &#8220;%s&#8221;', 'envato-market' ), $name ) . '</span></a>';
				}
			}
			// @codeCoverageIgnoreEnd
			// Can activate theme.
			if ( $can_activate && current_user_can( 'switch_themes' ) ) {
				$activate_link = add_query_arg( array(
					'action'     => 'activate',
					'stylesheet' => urlencode( $slug ),
				), admin_url( 'themes.php' ) );
				$activate_link = wp_nonce_url( $activate_link, 'switch-theme_' . $slug );

				// Activate link.
				$actions['activate'] = '<a href="' . esc_url( $activate_link ) . '" class="button"><span aria-hidden="true">' . __( 'Activate', 'envato-market' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Activate &#8220;%s&#8221;', 'envato-market' ), $name ) . '</span></a>';

				// Preview theme.
				if ( current_user_can( 'edit_theme_options' ) && current_user_can( 'customize' ) ) {
					$preview_url = admin_url( 'customize.php' );
					$preview_url .= '?theme=' . urlencode( $slug );
					$preview_url .= '&return=' . urlencode( envato_market()->get_page_url() . '#themes' );
					$actions['customize_preview'] = '<a href="' . esc_url( $preview_url ) . '" class="button button-primary load-customize hide-if-no-customize"><span aria-hidden="true">' . __( 'Live Preview', 'envato-market' ) . '</span><span class="screen-reader-text">' . sprintf( __( 'Live Preview &#8220;%s&#8221;', 'envato-market' ), $name ) . '</span></a>';
				}
			}
		} elseif ( 'install' === $group && current_user_can( 'install_themes' ) ) {
			// Install link.
			$install_link = add_query_arg( array(
				'page'   => envato_market()->get_slug(),
				'action' => 'install-theme',
				'id'     => $theme['id'],
			), self_admin_url( 'admin.php' ) );

			$actions['install'] = '
			<a href="' . wp_nonce_url( $install_link, 'install-theme_' . $theme['id'] ) . '" class="button button-primary">
				<span aria-hidden="true">' . __( 'Install', 'envato-market' ) . '</span>
				<span class="screen-reader-text">' . sprintf( __( 'Install %s', 'envato-market' ), $name ) . '</span>
			</a>';
		}
		if ( 0 === strrpos( html_entity_decode( $author ), '<a ' ) ) {
			$author_link = $author;
		} else {
			$author_link = '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author ) . '</a>';
		}
		?>
		<div class="col" data-id="<?php echo esc_attr( $theme['id'] ); ?>">
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<div class="envato-card-top">
					<a href="<?php echo esc_url( $url ); ?>" class="column-icon">
						<img src="<?php echo esc_url( $theme['thumbnail_url'] ); ?>"/>
					</a>
					<div class="column-name">
						<h4>
							<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
							<span class="version" aria-label="<?php esc_attr_e( 'Version %s', 'envato-market' ); ?>"><?php echo esc_html( sprintf( __( 'Version %s', 'envato-market' ), $version ) ); ?></span>
						</h4>
					</div>
					<div class="column-description">
						<div class="description">
							<?php echo wp_kses_post( wpautop( $description ) ); ?>
						</div>
						<p class="author">
							<cite><?php esc_html_e( 'By', 'envato-market' ); ?> <?php echo wp_kses_post( $author_link ); ?></cite>
						</p>
					</div>
					<?php if ( ! empty( $updateActions ) ) { ?>
					<div class="column-update">
						<?php echo implode( "\n", $updateActions ); ?>
					</div>
					<?php } ?>
				</div>
				<div class="envato-card-bottom">
					<div class="column-rating">
						<?php wp_star_rating( array( 'rating' => ( $theme['rating']['rating'] / 5 * 100 ), 'type' => 'percent', 'number' => $theme['rating']['count'] ) ); ?>
						<span class="num-ratings">(<?php echo esc_html( number_format_i18n( $theme['rating']['count'] ) ); ?>)</span>
					</div>
					<div class="column-actions">
						<?php echo implode( "\n", $actions ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach;
}

/**
 * Interate over the plugins array and displays each plugin.
 *
 * @since 1.0.0
 *
 * @param string $group The plugin group. Options are 'purchased', 'active', 'installed', or 'install'.
 */
function envato_market_plugins_column( $group = 'install' ) {
	$premium = envato_market()->items()->plugins( $group );
	if ( empty( $premium ) ) {
		return;
	}

	$plugins = envato_market()->items()->wp_plugins();

	foreach ( $premium as $slug => $plugin ) :
		$name = $plugin['name'];
		$author = $plugin['author'];
		$version = $plugin['version'];
		$description = $plugin['description'];
		$url = $plugin['url'];
		$author_url = $plugin['author_url'];
		$plugin['hasUpdate'] = false;

		// Setup the column CSS classes.
		$classes = array( 'envato-card', 'plugin' );

		if ( 'active' === $group ) {
			$classes[] = 'active';
		}

		// Setup the update action links.
		$updateActions = array();

		// Check for an update.
		if ( isset( $plugins[ $slug ] ) && version_compare( $plugins[ $slug ]['Version'], $plugin['version'], '<' ) ) {
			$plugin['hasUpdate'] = true;

			$classes[] = 'update';
			$classes[] = 'envato-card-' . sanitize_key( dirname( $slug ) );

			if ( current_user_can( 'update_plugins' ) ) {
				// Upgrade link.
				$upgrade_link = add_query_arg( array(
					'action' => 'upgrade-plugin',
					'plugin' => $slug,
				), self_admin_url( 'update.php' ) );

				// Details link.
				$details_link = add_query_arg( array(
					'action'    => 'upgrade-plugin',
					'tab'       => 'plugin-information',
					'plugin'    => dirname( $slug ),
					'section'   => 'changelog',
					'TB_iframe' => 'true',
					'width'     => 640,
					'height'    => 662,
				), self_admin_url( 'plugin-install.php' ) );

				$updateActions['update'] = sprintf(
					'<a class="update-now" href="%1$s" aria-label="%2$s" data-name="%3$s %6$s" data-plugin="%4$s" data-slug="%5$s" data-version="%6$s">%7$s</a>',
					wp_nonce_url( $upgrade_link, 'upgrade-plugin_' . $slug ),
					esc_attr__( 'Update %s now', 'envato-market' ),
					esc_attr( $name ),
					esc_attr( $slug ),
					sanitize_key( dirname( $slug ) ),
					esc_attr( $version ),
					esc_html__( 'Update Available', 'envato-market' )
				);

				$updateActions['details'] = sprintf(
					'<a href="%1$s" class="thickbox details" title="%2$s">%3$s</a>',
					esc_url( $details_link ),
					esc_attr( $name ),
					sprintf(
						__( 'View version %1$s details.', 'envato-market' ),
						$version
					)
				);
			}
		}

		// Setup the action links.
		$actions = array();

		if ( 'active' === $group ) {
			// Deactivate link.
			$deactivate_link = add_query_arg( array(
				'action'        => 'deactivate',
				'plugin'        => $slug,
			), self_admin_url( 'plugins.php' ) );

			$actions['deactivate'] = '
			<a href="' . wp_nonce_url( $deactivate_link, 'deactivate-plugin_' . $slug ) . '" class="button">
				<span aria-hidden="true">' . __( 'Deactivate', 'envato-market' ) . '</span>
				<span class="screen-reader-text">' . sprintf( __( 'Deactivate %s', 'envato-market' ), $name ) . '</span>
			</a>';
		} elseif ( 'installed' === $group ) {
			if ( ! is_multisite() && current_user_can( 'delete_plugins' ) ) {
				// Delete link.
				$delete_link = add_query_arg( array(
					'action'        => 'delete-selected',
					'checked[]'     => $slug,
				), self_admin_url( 'plugins.php' ) );

				$actions['delete'] = '
				<a href="' . wp_nonce_url( $delete_link, 'bulk-plugins' ) . '" class="button-delete">
					<span aria-hidden="true">' . __( 'Delete', 'envato-market' ) . '</span>
					<span class="screen-reader-text">' . sprintf( __( 'Delete %s', 'envato-market' ), $name ) . '</span>
				</a>';
			}

			if ( ! is_multisite() && current_user_can( 'activate_plugins' ) ) {
				// Activate link.
				$activate_link = add_query_arg( array(
					'action'        => 'activate',
					'plugin'        => $slug,
				), self_admin_url( 'plugins.php' ) );

				$actions['activate'] = '
				<a href="' . wp_nonce_url( $activate_link, 'activate-plugin_' . $slug ) . '" class="button">
					<span aria-hidden="true">' . __( 'Activate', 'envato-market' ) . '</span>
					<span class="screen-reader-text">' . sprintf( __( 'Activate %s', 'envato-market' ), $name ) . '</span>
				</a>';
			}

			// @codeCoverageIgnoreStart
			// Multisite needs special attention.
			if ( is_multisite() ) {
				if ( current_user_can( 'manage_sites' ) ) {
					$actions['site_activate'] = '
					<a href="' . esc_url( admin_url( wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $slug ), 'activate-plugin_' . $slug ) ) ) . '" class="button">
						<span aria-hidden="true">' . __( 'Site Activate', 'envato-market' ) . '</span>
						<span class="screen-reader-text">' . sprintf( __( 'Site Activate %s', 'envato-market' ), $name ) . '</span>
					</a>';
				}

				if ( current_user_can( 'manage_network_plugins' ) ) {
					$actions['network_activate'] = '
					<a href="' . esc_url( network_admin_url( wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $slug ), 'activate-plugin_' . $slug ) ) ) . '" class="button">
						<span aria-hidden="true">' . __( 'Network Activate', 'envato-market' ) . '</span>
						<span class="screen-reader-text">' . sprintf( __( 'Network Activate %s', 'envato-market' ), $name ) . '</span>
					</a>';
				}
			}
			// @codeCoverageIgnoreEnd
		} elseif ( 'install' === $group && current_user_can( 'install_plugins' ) ) {
			// Install link.
			$install_link = add_query_arg( array(
				'page'   => envato_market()->get_slug(),
				'action' => 'install-plugin',
				'id'     => $plugin['id'],
			), self_admin_url( 'admin.php' ) );

			$actions['install'] = '
			<a href="' . wp_nonce_url( $install_link, 'install-plugin_' . $plugin['id'] ) . '" class="button button-primary">
				<span aria-hidden="true">' . __( 'Install', 'envato-market' ) . '</span>
				<span class="screen-reader-text">' . sprintf( __( 'Install %s', 'envato-market' ), $name ) . '</span>
			</a>';
		}
		if ( 0 === strrpos( html_entity_decode( $author ), '<a ' ) ) {
			$author_link = $author;
		} else {
			$author_link = '<a href="' . esc_url( $author_url ) . '">' . esc_html( $author ) . '</a>';
		}
		?>
		<div class="col" data-id="<?php echo esc_attr( $plugin['id'] ); ?>">
			<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<div class="envato-card-top">
					<a href="<?php echo esc_url( $url ); ?>" class="column-icon">
						<img src="<?php echo esc_url( $plugin['thumbnail_url'] ); ?>"/>
					</a>
					<div class="column-name">
						<h4>
							<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
							<span class="version" aria-label="<?php esc_attr_e( 'Version %s', 'envato-market' ); ?>"><?php echo esc_html( sprintf( __( 'Version %s', 'envato-market' ), ( isset( $plugins[ $slug ] ) ? $plugins[ $slug ]['Version'] : $version ) ) ); ?></span>
						</h4>
					</div>
					<div class="column-description">
						<div class="description">
							<?php echo wp_kses_post( wpautop( $description ) ); ?>
						</div>
						<p class="author">
							<cite><?php esc_html_e( 'By', 'envato-market' ); ?> <?php echo wp_kses_post( $author_link ); ?></cite>
						</p>
					</div>
					<?php if ( ! empty( $updateActions ) ) { ?>
					<div class="column-update">
						<?php echo implode( "\n", $updateActions ); ?>
					</div>
					<?php } ?>
				</div>
				<div class="envato-card-bottom">
					<div class="column-rating">
						<?php wp_star_rating( array( 'rating' => ( $plugin['rating']['rating'] / 5 * 100 ), 'type' => 'percent', 'number' => $plugin['rating']['count'] ) ); ?>
						<span class="num-ratings">(<?php echo esc_html( number_format_i18n( $plugin['rating']['count'] ) ); ?>)</span>
					</div>
					<div class="column-actions">
						<?php echo implode( "\n", $actions ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach;
}
