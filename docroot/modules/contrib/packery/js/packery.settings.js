/**
 * @file
 * packery.settings.js
 */

(function (Drupal) {

  Drupal.behaviors.packery = {
    attach: function (context, settings) {      
      for (var group in settings.packery) {
        var element = document.querySelector('.' + group + '.packery');

        if (element) {
          var packery = new Packery(element, settings.packery[group].settings);

          // imagesLoaded support.
          if (settings.packery[group].extend.images_loaded) {
            new imagesLoaded(element, function() {
              packery.layout();
            });
          }
        }
      }
    }
  }

})(Drupal);