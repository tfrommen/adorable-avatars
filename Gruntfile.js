/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	var configObject = {
		config: {
			assets: {
				src: 'resources/assets/',
				dest: 'assets/'
			},
			src: 'src/',
			tests: {
				phpunit: 'tests/phpunit/'
			}
		},

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

		jscs: {
			options: {
				config: true
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			}
		},

		jshint: {
			options: {
				jshintrc: true,
				reporter: require( 'jshint-stylish' )
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			}
		},

		jsonlint: {
			configs: {
				src: [ '.{jscs,jshint}rc' ]
			},
			json: {
				src: [ '*.json' ]
			}
		},

		jsvalidate: {
			options: {
				globals: {},
				esprimaOptions: {},
				verbose: false
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			}
		},

		lineending: {
			options: {
				eol: 'lf',
				overwrite: true
			},
			grunt: {
				src: [ 'Gruntfile.js' ]
			}
		},

		phplint: {
			src: {
				src: [ '<%= config.src %>**/*.php' ]
			},
			tests: {
				src: [ '<%= config.tests.phpunit %>**/*.php' ]
			}
		},

		watch: {
			options: {
				dot: true,
				spawn: true,
				interval: 2000
			},

			assets: {
				files: [ '<%= config.assets.src %>*.{gif,jpeg,jpg,png}' ],
				tasks: [
					'imagemin:assets'
				]
			},

			configs: {
				files: [ '.{jscs,jshint}rc' ],
				tasks: [
					'newer:jsonlint:configs'
				]
			},

			grunt: {
				files: [ 'Gruntfile.js' ],
				tasks: [
					'newer:jscs:grunt',
					'newer:jshint:grunt',
					'newer:lineending:grunt',
					'newer:jsvalidate:grunt'
				]
			},

			json: {
				files: [ '*.json' ],
				tasks: [
					'newer:jsonlint:json'
				]
			},

			php: {
				files: [
					'<%= config.src %>**/*.php',
					'<%= config.tests.phpunit %>**/*.php'
				],
				tasks: [
					'newer:phplint',
					'phpunit'
				]
			},

			travis: {
				files: [ '.travis.yml' ],
				tasks: [
					'travis-lint'
				]
			}
		}
	};

	require( 'load-grunt-tasks' )( grunt );

	grunt.initConfig( configObject );

	// PHPUnit task.
	grunt.registerTask( 'phpunit', function() {
		grunt.util.spawn( {
			cmd: 'phpunit',
			opts: {
				stdio: 'inherit'
			}
		}, this.async() );
	} );

	grunt.registerTask( 'assets', configObject.watch.assets.tasks );

	grunt.registerTask( 'configs', configObject.watch.configs.tasks );

	grunt.registerTask( 'grunt', configObject.watch.grunt.tasks );

	grunt.registerTask( 'json', configObject.watch.json.tasks );

	grunt.registerTask( 'php', configObject.watch.php.tasks );

	grunt.registerTask( 'travis', configObject.watch.travis.tasks );

	grunt.registerTask( 'common', [
		'configs',
		'grunt',
		'json',
		'php',
		'travis'
	] );

	grunt.registerTask( 'develop', [
		'common'
	] );

	grunt.registerTask( 'pre-commit', [
		'newer-clean',
		'common',
		'assets'
	] );

	grunt.registerTask( 'default', 'develop' );
};
