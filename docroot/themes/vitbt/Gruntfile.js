module.exports = function(grunt) {
  "use strict";
  require('time-grunt')(grunt);
  var theme_name = 'openedu';

  var global_vars = {
    theme_name: theme_name,
    theme_css: 'css',
    theme_scss: 'scss'
  };

  grunt.initConfig({
    global_vars: global_vars,
    pkg: grunt.file.readJSON('package.json'),
    sass_globbing: {
      dist: {
        files: {
          'scss/_importMap.scss': ['scss/component/**/*.scss', 'scss/jquery-ui/**/*.scss']
        }
      }
    },
    sass: {
      dist: {
        options: {
          includePaths: [
            'node_modules/bootstrap-sass/assets/stylesheets',
            'node_modules/breakpoint-sass/stylesheets'
          ],
          outputStyle: 'nested',
          sourceMap: true
        },
        files: {
          'css/style.css': 'scss/style.scss'
        }
      }
    },

    autoprefixer: {
      dist: {
        options: {
          map: true
        },
        expand: true,
        flatten: true,
        src: [
          'css/*.css'
        ],
        dest: 'css'
      }
    },

    watch: {
      grunt: { files: ['Gruntfile.js'] },

      sass: {
        files: 'scss/**/*.scss',
        tasks: ['sass', 'autoprefixer'],
        options: {
          livereload: true

        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-sass-globbing');


  grunt.registerTask('build', ['sass_globbing', 'sass', 'autoprefixer']);
  grunt.registerTask('default', ['build', 'watch']);
}
