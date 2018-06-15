/**
 * @file script.js
 *
 * Simple helpers for the OpenEDU Profile
 */
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.openEduNews = {
    attach: function (context, settings) {
      // Removes featured news when search has been performed
      if ($('.news-changed', context).length) {
        $('.block-views-blocknews-featured-news-block').addClass('hidden');
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
