# Envato Market Contributing Guide

We're really excited that you are interested in contributing to the Envato Market plugin. Before submitting your contribution though, please make sure to take a moment and read through the following guidelines.

## Issue Reporting Guidelines

- The issue list of this repo is **exclusively** for bug reports and feature requests.
- Try to search for your issue, it may have already been answered or even fixed in the `develop` branch.
- Check if the issue is reproducible with the latest stable version. If you are using a pre-release, please indicate the specific version you are using.
- It is **required** that you clearly describe the steps necessary to reproduce the issue you are running into. Issues without clear reproducible steps will be closed immediately.
- If your issue is resolved but still open, don't hesitate to close it. In case you found a solution by yourself, it could be helpful to explain how you fixed it.

## Pull Request Guidelines

- Checkout a topic branch from `develop` and merge back against `develop`.
    - If you are not familiar with branching please read [_A successful Git branching model_](http://nvie.com/posts/a-successful-git-branching-model/) before you go any further.
- **DO NOT** check-in the `dist` directory in your commits.
- Follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/coding-standards/).
- Make sure the default grunt task passes. (see [development setup](#development-setup))
- If adding a new feature:
    - Add accompanying test case.
    - Provide convincing reason to add this feature. Ideally you should open a suggestion issue first and have it green-lit before working on it.
- If fixing a bug:
    - Provide detailed description of the bug in the PR. Live demo preferred.
    - Add appropriate test coverage if applicable.

## Development Setup

#### Install VaryingVagrantVagrants

- Install Vagrant/VirtualBox/Plugins by following [these instructions](https://varyingvagrantvagrants.org/docs/en-US/installation/software-requirements/) then:
- `git clone -b master git://github.com/Varying-Vagrant-Vagrants/VVV.git ~/vagrant-local/`
- `cd ~/vagrant-local/`
- `vagrant up` (can take 30-60mins first time)
- Test you can access `http://src.wordpress-develop.test/` from your browser.
- Note: If you see `phpcs not found` or composer errors during setup it means you need a Github token, try running a manual provision to get prompted for a Github token: `vagrant ssh` then once connected run `sudo /vagrant/provision/provision.sh` and follow the prompts.

#### Setup a new WordPress site for local development.

- `cd ~/vagrant-local/`
- `cp vvv-config.yml vvv-custom.yml`
- edit `vvv-custom.yml` and add a new entry under `sites:` like this:
    ```
     envato-market:
        repo: https://github.com/dtbaker/vvv.envato-market.test.git
        hosts:
          - vvv.envato-market.test
    ```
- `vagrant reload --provision`
- Confirm you can access the new site here: `http://vvv.envato-market.test/`
- Confirm you can login here: `http://vvv.envato-market.test/wp-admin/` (default login is dev/dev)
- Confirm the WordPress plugin is activated.

#### Confirm grunt works:

- `cd ~/vagrant-local/www/envato-market/docroot/wp-content/plugins/envato-market/`
- `grunt`
- Note: If you receive any 'node rebuild' error, try running `npm rebuild`

#### Confirm tests run via VVV

- `cd ~/vagrant-local/www/envato-market/docroot/wp-content/plugins/envato-market/`
- `grunt phpunit`

#### Make changes:

- Make changes to the github repository at `~/vagrant-local/www/envato-market/docroot/wp-content/plugins/envato-market/`
- Test changes via `http://vvv.envato-market.test`
- Commit changes to a new branch and make a pull request against the `develop` branch.


## Grunt tasks:

To lint:

``` bash
$ grunt jshint
```

To compile Sass:

``` bash
$ grunt css
```

To minify JS:

``` bash
$ grunt uglify
```

To check the text domain:

``` bash
$ grunt checktextdomain
```

To create a pot file:

``` bash
$ grunt makepot
```

The default task (simply running `grunt`) will do the following: `jshint -> css -> uglify`.

## PHPUnit Testing

Run tests:

``` bash
$ phpunit
```

Run tests with an HTML coverage report:

``` bash
$ phpunit --coverage-html /tmp/report
```

To run the Envato API integration unit tests, you will need to create a `.token` file in the root of the plugin directory with your OAuth Personal Token saved to it. The `.token` file is added to `.gitignore` so it will not be tracked, and you should never try to commit it to the repository.

Travis CI will run the unit tests and perform sniffs against the WordPress Coding Standards whenever you push changes to your PR. Tests are required to pass successfully for a merge to be considered.