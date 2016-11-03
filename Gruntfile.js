const loadGrundModules = require( 'load-grunt-tasks' );

module.exports = function ( grunt ) {
	grunt.initConfig( {
		config: {
			assets: {
				src: 'resources/assets/',
				dest: 'svn-assets/'
			},

			slug: 'adorable-avatars',

			src: 'src/',

			tests: {
				php: 'tests/php/'
			}
		},

		/**
		 * @see {@link https://github.com/sindresorhus/grunt-eslint grunt-eslint}
		 * @see {@link https://github.com/eslint/eslint ESLint}
		 */
		eslint: {
			gruntfile: {
				src: [ 'Gruntfile.js' ]
			}
		},

		/**
		 * @see {@link https://github.com/gruntjs/grunt-contrib-imagemin grunt-contrib-imagemin}
		 * @see {@link https://github.com/imagemin/imagemin imagemin}
		 */
		imagemin: {
			options: {
				optimizationLevel: 7
			},

			assets: {
				expand: true,
				cwd: '<%= config.assets.src %>',
				src: [ '*.{gif,jpeg,jpg,png}' ],
				dest: '<%= config.assets.dest %>'
			}
		},

		/**
		 * @see {@link https://github.com/brandonramirez/grunt-jsonlint grunt-jsonlint}
		 * @see {@link https://github.com/zaach/jsonlint JSON Lint}
		 */
		jsonlint: {
			options: {
				indent: 2
			},

			configs: {
				src: [ '.*rc' ]
			},

			json: {
				src: [ '*.json' ]
			}
		},

		/**
		 * @see {@link https://github.com/suisho/grunt-lineending grunt-lineending}
		 */
		lineending: {
			options: {
				eol: 'lf',
				overwrite: true
			},

			github: {
				src: [ '.github/*' ]
			},

			root: {
				src: [ '*' ]
			},

			src: {
				src: [ '<%= config.src %>**/*.php' ]
			},

			tests: {
				src: [
					'<%= config.tests.php %>**/*.php'
				]
			}
		},

		/**
		 * @see {@link https://github.com/jgable/grunt-phplint grunt-phplint}
		 */
		phplint: {
			root: {
				src: [ '*.php' ]
			},

			src: {
				src: [ '<%= config.src %>**/*.php' ]
			},

			tests: {
				src: [ '<%= config.tests.php %>**/*.php' ]
			}
		},

		/**
		 * @see {@link https://github.com/sindresorhus/grunt-shell grunt-shell}
		 */
		shell: {
			phpunit: {
				command: 'phpunit'
			}
		},

		/**
		 * @see {@link https://github.com/twolfson/grunt-zip grunt-zip}
		 */
		zip: {
			release: {
				src: [
					'*.{php,txt}',
					'<%= config.src %>**/*.php'
				],
				dest: '<%= config.slug %>.zip',
				router( filepath ) {
					return grunt.template.process( `<%= config.slug %>/${filepath}` );
				}
			}
		}
	} );

	/**
	 * @see {@link https://github.com/sindresorhus/load-grunt-tasks load-grunt-tasks}
	 */
	loadGrundModules( grunt );

	grunt.registerTask( 'common', [
		'jsonlint',
		'phplint',
		'shell:phpunit',
		'eslint'
	] );

	grunt.registerTask( 'ci', [
		'common'
	] );

	grunt.registerTask( 'develop', [
		'newer:jsonlint',
		'newer:eslint',
		'newer:phplint:src',
		'newer:lineending'
	] );

	grunt.registerTask( 'pre-commit', [
		'newer-clean',
		'imagemin',
		'common',
		'lineending'
	] );

	grunt.registerTask( 'release', [
		'pre-commit',
		'zip:release'
	] );

	grunt.registerTask( 'default', 'develop' );
};
