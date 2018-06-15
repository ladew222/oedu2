# Change this to :production when ready to deploy the CSS to the live server.
environment = :development
#environment = :production


http_path = '/thmes/vitbt'
css_dir = 'css'
sass_dir = 'assets/sass'
fonts_dir = "bootstrap/fonts/bootstrap"
images_dir = "assets/images"
javascripts_dir = 'js'
generated_images_dir = 'img'
http_images_path = http_path + "/" + generated_images_dir

firesass = false

require 'breakpoint'

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed
output_style = (environment == :development) ? :expanded : :compressed


# To enable relative paths to assets via compass helper functions. Since Drupal
# themes can be installed in multiple locations, we don't need to worry about
# the absolute path to the theme from the server root.
relative_assets = true


# Require any additional compass plugins installed on your system.
require 'compass-normalize'
#require 'rgbapng'
require 'toolkit'
require 'sass-globbing'


# CSS Sourcemaps
enable_sourcemaps = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
line_comments = false

# Pass options to sass. For development, we turn on the FireSass-compatible
# debug_info if the firesass config variable above is true.
#sass_options = (environment == :development && firesass == true) ? {:debug_info => true} : {}

# Add the 'sass' directory itself as an import path to ease imports.
add_import_path 'sass'