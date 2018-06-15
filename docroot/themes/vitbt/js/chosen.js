/**
 * @file
 * chosen.js
 *
 * Adds chosen and settings to specified select elements.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.attachChosenJS = {
    attach: function (context, settings) {

      // Add chosen to view filter select elements.
      var $viewFilterSelect = $('section.views-exposed-form .select-wrapper > select');
      if ($viewFilterSelect.length && $.fn.chosen) {
        $viewFilterSelect.chosen();
      }
      // Add chosen to view filter select multiple elements.
      var $viewFilterMultiSelect = $('section.views-exposed-form .form-item > select[multiple=multiple]');
      if ($viewFilterMultiSelect.length && $.fn.chosen) {
        $viewFilterMultiSelect.chosen();
      }

    }
  };

})(jQuery, Drupal, drupalSettings);