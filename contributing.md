# Envato Market Contributing Guide

We're really excited that you are interested in contributing to the Envato Market plugin. Before submitting your contribution though, please make sure to take a moment and read through the following guidelines.

## Issue Reporting Guidelines

- The issue list of this repo is **exclusively** for bug reports and feature requests. For simple questions, please use [Gitter](https://gitter.im/envato/wp-envato-market).
- Try to search for your issue, it may have already been answered or even fixed in the `wip` (Work in Progress) branch.
- Check if the issue is reproducible with the latest stable version. If you are using a pre-release, please indicate the specific version you are using.
- It is **required** that you clearly describe the steps necessary to reproduce the issue you are running into. Issues without clear reproducible steps will be closed immediately.
- If your issue is resolved but still open, don't hesitate to close it. In case you found a solution by yourself, it could be helpful to explain how you fixed it.

## Pull Request Guidelines

- Checkout a topic branch from `wip` and merge back against `wip`.
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

You will need [Node.js](http://nodejs.org), [Grunt](http://gruntjs.com), & [PHPUnit](https://phpunit.de/getting-started.html) installed on your system. To run the unit tests you must be developing within the WordPress Core. The simplest method to get a testing environment up is by using [Varying Vagrant Vagrants](https://github.com/Varying-Vagrant-Vagrants/VVV). However, if you are using MAMP then the following command will clone `trunk`.

To clone the WordPress Core

``` bash
$ git clone git://develop.git.wordpress.org/trunk/
```

To clone this repository
``` bash
$ git clone --recursive git@github.com:envato/wp-envato-market.git envato-market
```

To install packages

``` bash
# npm install -g grunt-cli
$ npm install
```

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

### PHPUnit Testing

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