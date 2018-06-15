/**
 * @file
 *
 * All global scripts for OpenEDU theme
 */
(function ($, window, Drupal) {

  'use strict';

  // Replaces ajax throbbers with an SVG version.
  Drupal.behaviors.oeduThrobber = {
    attach: function (context, settings) {
      $('body', context).once('progress_throbber').each(function () {
        var progress_indicator = $('<svg class="progress-throbber" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>');
        $('input.form-autocomplete').after(progress_indicator);
      });
    }
  }

})(jQuery, window, Drupal);
