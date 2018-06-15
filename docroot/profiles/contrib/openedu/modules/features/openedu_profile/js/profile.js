/**
 * @file
 * profile.js
 *
 * Adds profile page and listing page specific javascript.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.OELProfile = {
    attach: function (context, settings) {
      var $viewFilters;
      var $viewFilterSubmit;

      // Automatically update select filters on Profile Listing page on change.
      $viewFilters = $('#views-exposed-form-profile-list-profile-listing-block').find('select.form-select');
      $viewFilterSubmit = $('#views-exposed-form-profile-list-profile-listing-block').find('#edit-submit-profile-list');
      if ($viewFilters.length && $viewFilterSubmit.length) {
        $viewFilters.on('change', function() {
          var $this = $(this);
          $this.parents('form').find('#edit-submit-profile-list').trigger('click');
        });
      }


    }
  };

})(jQuery, Drupal, drupalSettings);
