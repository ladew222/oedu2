module.exports = function(grunt) {
  "use strict";
  require('time-grunt')(grunt);
  var theme_name = 'oedu';

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
          'scss/_init.scss': [
            'scss/presentation/**/*.scss'
          ],
          'scss/_base.scss': [
            'scss/fonts/**/*.scss',
            'scss/component/**/*.scss',
            'scss/jquery-ui/**/*.scss',
            'scss/_overrides.scss',
            'scss/layout/**/*.scss',
            'scss/node/**/**/*.scss',
            'scss/block/**/*.scss'
          ]
        }
      }
    },

    sass: {
      modules: {
        options: {
          includePaths: [
            'scss'
          ],
          outputStyle: 'nested',
          sourceMap: true
        },
        files: {
          '../../modules/ixm_dashboard/css/style.css': '../../modules/ixm_dashboard/scss/style.scss'
        }
      },
      dist: {
        options: {
          includePaths: [
            'node_modules/bootstrap/scss/',
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

    webfont: {
      icons: {
        src: 'images/icons/*.svg',
        dest: 'fonts',
        destScss: 'scss/fonts',
        options: {
          stylesheet: 'scss',
          syntax: 'bem',
          relativeFontPath: '../fonts',
          fontFilename: 'material-icons-social',
          fontFamilyName: 'Material Icons Social',
          fontHeight: 700,
          templateOptions: {
            baseClass: 'material-icons-social',
            classPrefix: 'mis_'
          }
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

    copy: {
      main: {
        files: [{
          expand: true,
          src: 'node_modules/bootstrap/dist/js/bootstrap.min.js',
          dest: 'js/',
          flatten: true
        }]
      }
    },

    sasslint: {
      options: {
        configFile: '.sass-lint.yml'
      },
      files: ['scss/**/*.scss']
    },

    jshint: {
      options: {
        curly: true,
        eqeqeq: true,
        eqnull: true,
        browser: true,
        globals: {
          jQuery: true
        }
      },
      files: ['Gruntfile.js', 'js/chosen.js', 'js/scripts.js']
    },

    watch: {
      grunt: { files: ['Gruntfile.js'] },
      sass: {
        files: ['<%= sasslint.files %>'],
        tasks: ['sass', 'autoprefixer', 'sasslint'],
        options: {
          livereload: true
        }
      },
      js: {
        files: ['<%= jshint.files %>'],
        tasks: ['jshint']
      }
    }

  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-sass-globbing');
  grunt.loadNpmTasks('grunt-sass-lint');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-webfont');

  grunt.registerTask('build', ['sasslint', 'sass_globbing', 'sass', 'autoprefixer', 'copy']);
  grunt.registerTask('default', ['build', 'watch']);
};
