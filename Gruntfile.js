module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		// Data from package.json
		pkg: grunt.file.readJSON('package.json'),

		// Compile markdown
		markdown: {
			docIndex: {
				files: {'doc/index.html': 'README.md'},
				options: {
					template: 'doc/assets/index-template.html',
					markdownOptions: {
						gfm: false, // Github flavored markdown
						highlight: function () {}
					},
					preCompile: function( src, context ) {
						return src.replace( /\s?\[!\[devDep.+devDependencies\)/, '' );
					}
				}
			},
			doc: {
				files: [{
					expand: true,
					flatten: true,
					src: 'doc/markdown/*.md',
					dest: 'doc/html/',
					ext: '.html'
				}],
				options: {
					template: 'doc/assets/template.html',
					markdownOptions: {
						gfm: false, // Github flavored markdown
						highlight: function () {}
					}
				}
			}
		},

		// JSHint
		jshint: {
			options: {
				'bitwise'  : true,
				'browser'  : true,
				'curly  '  : true,
				'eqeqeq'   : true,
				'eqnull'   : true,
				'es3'      : true,
				'forin'    : true,
				'immed'    : true,
				'indent'   : false,
				'jquery'   : true,
				'latedef'  : true,
				'newcap'   : true,
				'noarg'    : true,
				'noempty'  : true,
				'nonew'    : true,
				'node'     : true,
				'smarttabs': true,
				'strict'   : true,
				'trailing' : true,
				'undef'    : true,
				'unused'   : true,

				'globals': {
					'jQuery': true,
					'alert': true
				},

				reporter: require('jshint-stylish')
			},
			doc: {
				src: [
					'doc/assets/doc.js'
				]
			},
			grunt: {
				src: ['Gruntfile.js']
			}
		},

		// JavaScript concatenation and minification
		uglify: {
			doc: {
				options: {
					report: 'min',
					banner: '/*! <%= pkg.title %> - Readme script */\n'
				},
				files: [{src: ['doc/assets/doc.js'], dest: 'doc/assets/doc.min.js'}]
			}
		},

		// CSS concatenation and minification
		cssmin: {
			doc: {
				options: {
					banner: '/*! <%= pkg.title %> - Readme style */'
				},
				files: [{src: ['doc/assets/doc.css'], dest: 'doc/assets/doc.min.css'}]
			}
		}

	});

	// Load tasks
	require('load-grunt-tasks')(grunt);

	// Register tasks.
	// Default, documentation: 'grunt'
	grunt.registerTask('default', [
		'markdown:docIndex',
		'markdown:doc',
		'jshint:doc',
		'uglify:doc',
		'cssmin:doc'
	]);

};