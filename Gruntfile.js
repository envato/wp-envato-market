/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'js/*.js',
				'!js/*.min.js'
			]
		},

		// Minify .js files.
		uglify: {
			options: {
				preserveComments: false
			},
			core: {
				files: [ {
					expand: true,
					cwd: 'js/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: 'js/',
					ext: '.min.js'
				} ]
			}
		},

		// Compile all .scss files.
		sass: {
			options: {
				require: 'susy',
				sourcemap: 'none',
				includePaths: require( 'node-bourbon' ).includePaths
			},
			core: {
				files: [ {
					'css/envato-market.css': 'sass/envato-market.scss'
				} ]
			}
		},

		// Create RTL .css files
		rtlcss: {
			options: {
				config: {
					swapLeftRightInUrl: false,
					swapLtrRtlInUrl: false,
					autoRename: false,
					preserveDirectives: true
				},
				saveUnmodified: true
			},
			core: {
				expand: true,
				ext: '-rtl.css',
				src: [
					'css/envato-market.css'
				]
			}
		},

		// Minify all .css files.
		cssmin: {
			core: {
				files: [ {
					expand: true,
					cwd: 'css/',
					src: [ '*.css' ],
					dest: 'css/',
					ext: '.css'
				} ]
			}
		},

		// Watch changes for assets.
		watch: {
			css: {
				files: [
					'sass/*.scss'
				],
				tasks: [
					'sass',
					'rtlcss',
					'cssmin',
					'clean:core'
				]
			},
			js: {
				files: [
					'js/*js',
					'!js/*.min.js'
				],
				tasks: [ 'uglify' ]
			}
		},

		// Generate POT files.
		makepot: {
			target: {
				options: {
					potFilename: '<%= pkg.name %>.pot',
					exclude: [
						'dist/<%= pkg.name %>/.*' // Exclude deploy directory
					],
					processPot: function( pot ) {
						pot.headers['project-id-version'];
						return pot;
					},
					type: 'wp-plugin',
					domainPath: 'languages',
					potHeaders: {
						'report-msgid-bugs-to': 'Envato Support Team <support@envato.com>',
						'last-translator': 'Envato Support Team <support@envato.com>',
						'language-team': 'Envato Support Team <support@envato.com>'
					}
				}
			}
		},

		// Check textdomain errors.
		checktextdomain: {
			options:{
				text_domain: '<%= pkg.name %>',
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src:	[
					'**/*.php', // Include all files
					'!node_modules/**' // Exclude node_modules/
				],
				expand: true
			}
		},

		// Creates deploy-able plugin
		copy: {
			deploy: {
				src: [
					'**',
					'!.*',
					'!.*/**',
					'!.DS_Store',
					'!code_of_conduct.md',
					'!composer.json',
					'!contributing.md',
					'!dev-lib/**',
					'!dist/**',
					'!Gruntfile.js',
					'!node_modules/**',
					'!npm-debug.log',
					'!package.json',
					'!phpcs.ruleset.xml',
					'!phpunit.xml.dist',
					'!readme.md',
					'!sass/**',
					'!tests/**',
					'!vendor/**'
				],
				dest: 'dist/<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		// Compress distribution package into a ZIP
		compress: {
			deploy: {
				options: {
					archive: 'dist/<%= pkg.name %>.zip',
					mode: 'zip'
				},
				files: [ {
					expand: true,
					cwd: 'dist/<%= pkg.name %>/',
					src: [ '**/*' ],
					dest: '<%= pkg.name %>'
				} ]
			}
		},

		// Clean up
		clean: {
			deploy: {
				src: [
					'dist/<%= pkg.name %>'
				]
			}
		},

		// VVV (Varying Vagrant Vagrants) Paths
		vvv: {
			'plugin': '/srv/www/wordpress-develop/src/wp-content/plugins/<%= pkg.name %>',
			'coverage': '/srv/www/default/coverage/<%= pkg.name %>'
		},

		// Shell actions
		shell: {
			options: {
				stdout: true,
				stderr: true
			},
			readme: {
				command: 'cd ./dev-lib && ./generate-markdown-readme' // Genrate the readme.md
			},
			phpunit: {
				command: 'vagrant ssh -c "cd <%= vvv.plugin %> && phpunit"'
			},
			phpunit_c: {
				command: 'vagrant ssh -c "cd <%= vvv.plugin %> && phpunit --coverage-html <%= vvv.coverage %>"'
			}
		}

	});

	// Load tasks
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-checktextdomain' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );
	grunt.loadNpmTasks( 'grunt-contrib-clean' );
	grunt.loadNpmTasks( 'grunt-shell' );

	// Register tasks
	grunt.registerTask( 'default', [
		'jshint',
		'css',
		'uglify'
	] );

	grunt.registerTask( 'css', [
		'sass',
		'rtlcss',
		'cssmin'
	] );

	grunt.registerTask( 'readme', [
		'shell:readme'
	] );

	grunt.registerTask( 'phpunit', [
		'shell:phpunit'
	] );

	grunt.registerTask( 'phpunit_c', [
		'shell:phpunit_c'
	] );

	grunt.registerTask( 'dev', [
		'default',
		'makepot',
		'readme',
		'phpunit'
	] );

	grunt.registerTask( 'deploy', [
		'copy',
		'compress',
		'clean'
	] );

};
