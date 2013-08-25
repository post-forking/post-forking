/*global module:false*/
module.exports = function(grunt) {
	var path = require('path');
	var SOURCE_DIR = '';
	var BUILD_DIR = '';

	// Project configuration.
	grunt.initConfig({
		clean: {
            admin:['css/admin.min.css', 'js/admin.min.js']
		},
		cssmin: {
			admin: {
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				ext: '.min.css',
				src: [
					'css/admin.css'
				]
			}
		},
		uglify: {
			admin: {
				expand: true,
				cwd: SOURCE_DIR,
				dest: BUILD_DIR,
				ext: '.min.js',
				src: [
					'js/admin.js'
				]
			},
		},
		watch: {
			all: {
				files: [
                    'js/admin.js',
                    'css/admin.css',
				],
				tasks: ['build'],
				options: {
					dot: true,
					spawn: false,
					interval: 2000
				}
			}
		}
	});
    // Load tasks
    require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks ); 


	// Register tasks.
	grunt.registerTask('build', ['clean:admin', 'cssmin:admin','uglify:admin']);

	// Default task.
	grunt.registerTask('default', ['build']);

};
