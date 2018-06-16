/**
 * @file script.js
 *
 * Simple helpers for the OpenEDU Profile
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.openEduCore = {
    attach: function (context, settings) {

      // Fittext on statistics or specific tag
      //$('.openedu-stat, .fittext', context).fitText(0.40);
      // Fittext on statistics or specific tag
      $('.openedu-stat, .fittext', context).fitText(0.80);
      // Fittext on statistics or specific tag
      $('.openedu-stat-small, .fittext', context).fitText(0.95);
      $('.openedu-stat-large, .fittext', context).fitText(0.40);
    }
  };

  // @see: https://github.com/davatron5000/FitText.js
  $.fn.fitText = function (ratio, options) {
    var compressor = ratio || 1,
      settings = $.extend({
        'minFontSize': Number.NEGATIVE_INFINITY,
        'maxFontSize': Number.POSITIVE_INFINITY
      }, options);

    return this.each(function () {
      var $this = $(this);

      // Resizer() resizes items based on the object width divided by the compressor * 10
      var resizer = function () {
        $this.css('font-size', Math.max(Math.min($this.width() / (compressor * 10), parseFloat(settings.maxFontSize)), parseFloat(settings.minFontSize)));
      };

      // Call once to set.
      resizer();

      // Call on resize. Opera debounces their resize by default.
      $(window).on('resize.fittext orientationchange.fittext', resizer);
    });
  };

}) (jQuery, Drupal, drupalSettings);
