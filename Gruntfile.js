module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({

		// Data from package.json
		pkg: grunt.file.readJSON('package.json'),

		// Compile markdown
		markdown: {
			doc: {
				files: ['doc/markdown/*.md'],
				template: 'doc/assets/template.html',
				dest: 'doc/html',
				options: {
					gfm: false, // Github flavored markdown
					highlight: function(code, lang) {
						return code; // No code highlighting
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
				'es5'      : true,
				'esnext'   : true,
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
				}
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
					banner: '/*! <%= pkg.title %> <%= pkg.version %> - Readme script */\n'
				},
				files: [{src: ['doc/assets/doc.js'], dest: 'doc/assets/doc.min.js'}]
			}
		},

		// CSS concatenation and minification
		cssmin: {
			doc: {
				options: {
					banner: '/*! <%= pkg.title %> <%= pkg.version %> - Readme style */'
				},
				files: [{src: ['doc/assets/doc.css'], dest: 'doc/assets/doc.min.css'}]
			}
		}

	});

	// Load tasks
	grunt.loadNpmTasks('grunt-markdown');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	// Register tasks.
	// Documentation: 'grunt doc'
	grunt.registerTask('doc', [
		'markdown:doc',
		'jshint:doc',
		'uglify:doc',
		'cssmin:doc'
	]);

};