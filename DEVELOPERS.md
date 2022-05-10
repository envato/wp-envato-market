## Developers

### Publishing a new version

This WordPress plugin is hosted on Gibhub pages:
- The url for the latest zip file is: https://envato.github.io/wp-envato-market/dist/envato-market.zip
- The url that clients check for updates is: https://envato.github.io/wp-envato-market/dist/update-check.json
To release a new version we have to update these two files, the steps for doing so are below:

1. Create a new branch based on the version number, e.g. `v2.0.7`
1. Implement and commit any improvements to the plugin (e.g. bug fixes or new features)
1. Update the version numbers in these files:
    1. `envato-market.php` in the comment at the top, and in the `ENVATO_MARKET_VERSION` const
    1. `docs/dist/update-check.json`
    1. `package.json`
    1. `readme.txt` stable tag
    1. (Do not edit `readme.md` or `envato-market.pot` these files are auto generated)
1. Update the changelog in these files:
    1. `readme.txt` add a new changelog entry for version number
    1. `docs/dist/update-check.json` add a changelog entry like: `<h4>v2.0.7</h4><ul><li>Fix global notice hidden bug</li></ul>`
1. Update the "tested up to" tag in `envato-market.php` e.g. `Tested up to: 5.9`
1. Commit the changes ^^
1. Run the build script:
    1. `npm install`
    1. `npm run deploy`
    1. This updates a few files including `readme.md` and the new zip file in `docs/dist/envato-market.zip`
1. Commit the changes ^^
1. Open a PR based on this branch
1. Get approval and merge the PR
1. Publish a new github release https://github.com/envato/wp-envato-market/releases/new to match the new version number.
1. The update will now be live for everyone
