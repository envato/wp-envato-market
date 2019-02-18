=== Envato Market ===
Website: https://envato.com/market-plugin/
Contributors: valendesigns, dtbaker, aaronrutley
Requires at least: 4.9
Tested up to: 5.1
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Theme & Plugin management for the Envato Market.

== Description ==

The Envato Market plugin can install WordPress themes and plugins purchased from ThemeForest & CodeCanyon by connecting with the Envato Market API using a secure OAuth personal token. Once your themes & plugins are installed WordPress will periodically check for updates, so keeping your items up to date is as simple as a few clicks.

You can add a global token to connect all your items from your account, and/or connect directly with a specific item using a singe-use token & item ID. When the global token and single-use token are set for the same item, the single-use token will be used to communicate with the API.

== Installation ==

The latest version of the Envato Market plugin ZIP file can be [**downloaded**](https://envato.github.io/wp-envato-market/dist/envato-market.zip) from https://envato.com/market-plugin/

Installing the Envato Market plugin.

1. Download the `envato-market.zip` to your computer.
1. Login to WordPress and go to the 'Plugins' menu.
1. Click 'Add New' and upload the `envato-market.zip` file, then activate.
1. Click the 'Envato Market' menu in WordPress and connect to the API.

Alternatively you can manually install the item via FTP

1. Download the `envato-market.zip` and unpack the archive.
1. Upload `envato-market` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Click the 'Envato Market' menu in WordPress and connect to the API.

Required token permissions:

* View and search Envato sites (default)
* Download your purchased items
* List purchases you've made


== Support ==

Please see https://envato.com/market-plugin/ for more details.



== Change Log ==

### v2.0.1
- Token permission checks
- Fix for showing missing ratings
- Multisite/Network mode support
- Fix self-update check
- Improved token generation feature

#### v2.0.0
- Various bug fixes
- Compatibility with the new Envato API response format

#### v1.0.0
- Original release