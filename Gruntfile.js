module.exports = function(grunt) {
	'use strict';

	var tasks_css = [
		'sass:dist',
		'cssmin:dist'
	],

	tasks_js = [
		'jshint:dist',
		'uglify:dist'
	];

	grunt.initConfig({

		// Data from package.json
		pkg: grunt.file.readJSON('package.json'),

		// Compile markdown
		markdown: {
			docIndex: {
				options: {
					template: 'assets/html/doc-index-template.html',
					gfm: false, // Github flavored markdown
					preCompile: function (src, context) {
						// Remove David badge
						return src.replace(/\[!\[devDependency.+Dependencies\)/, '');
					},
				},
				files: {'doc/index.html': ['README.md']}
			},
			doc: {
				options: {
					template: 'assets/html/doc-template.html',
					gfm: false // Github flavored markdown
				},
				files: [{
					expand: true,
					src: 'doc/markdown/*.md',
					dest: 'doc/html/',
					ext: '.html',
					rename: function (dest, src) {
						var file = src.split('/').pop()
						return dest + file;
					}
				}]
			}
		},

		// JSHint
		jshint: {
			options: {
				jshintrc: 'assets/.jshintrc',
				reporter: require('jshint-stylish')
			},
			dist: {
				src: [
					'assets/js/*.js',
					'!assets/js/*.min.js',
					'!assets/js/doc.js'
				]
			},
			doc: {
				src: [
					'assets/js/doc.js'
				]
			}
		},

		// JavaScript concatenation and minification
		uglify: {
			dist: {
				options: { report: 'min' },
				files: [{
					expand: true,
					cwd: 'assets/js',
					src: ['*.js', '!doc.js', '!*.min.js'],
					dest: 'assets/js',
					ext: '.min.js'
				}]
			},
			doc: {
				options: { report: 'min' },
				src: ['assets/js/doc.js'],
				dest: 'doc/assets/doc.min.js'
			}
		},

		// Sass compilation
		sass: {
			dist: {
				options: { outputStyle: 'expanded' },
				files: [{
					expand: true,
					cwd: 'assets/css',
					src: ['*.scss', '!doc.scss'],
					dest: 'assets/css',
					ext: '.css'
				}]
			},
			doc: {
				src: ['assets/css/doc.scss'],
				dest: 'doc/assets/doc.css'
			}
		},

		// CSS concatenation and minification
		cssmin: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/css',
					src: ['*.css', '!doc.css', '!*.min.css'],
					dest: 'assets/css',
					ext: '.min.css'
				}]
			},
			doc: {
				src: ['doc/assets/doc.css'],
				dest: 'doc/assets/doc.min.css'
			}
		},

		// Watch for file changes
		watch: {
			options: {
				spawn: false
			},
			css: {
				files: ['assets/css/*.scss'],
				tasks: tasks_css
			},
			js: {
				files: ['assets/js/*.js'],
				tasks: tasks_js
			}
		}

	});

	// Load tasks
	require('load-grunt-tasks')(grunt);

	// Register tasks.
	// Default: 'grunt'
	grunt.registerTask('default', [
		'watch'
	]);

	// CSS: 'grunt css'
	grunt.registerTask('css', tasks_css);

	// JavaScript: 'grunt js'
	grunt.registerTask('js', tasks_js);

	// Documentation: 'grunt doc'
	grunt.registerTask('doc', [
		'markdown:docIndex',
		'markdown:doc',
		'jshint:doc',
		'uglify:doc',
		'sass',
		'cssmin:doc'
	]);

};