/**
 * @file
 * Preview for the OpenEdu theme.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.color = {
    logoChanged: false,
    callback: function (context, settings, $form) {
      // Change the logo to be the real one.
      if (!this.logoChanged) {
        $('.color-preview-logo').css('background-image', 'url(' + drupalSettings.color.logo + ')');
        this.logoChanged = true;
      }

      var $colorPreview = $form.find('.color-preview');
      var $colorPalette = $form.find('.js-color-palette');

      // Solid background.
      $colorPreview.css('backgroundColor', $colorPalette.find('input[name="palette[bg]"]').val());

      // Header.
      $colorPreview.find('.color-preview-header').css('background-color', $colorPalette.find('input[name="palette[headerbg]"]').val());
      $colorPreview.find('.color-preview-menu .hover').css('background-color', $colorPalette.find('input[name="palette[headerhover]"]').val());
      $colorPreview.find('.color-preview-menu-link').css('color', $colorPalette.find('input[name="palette[headerlink]"]').val());
      $colorPreview.find('.color-preview-menu-link.hover').css('color', $colorPalette.find('input[name="palette[headerlinkhover]"]').val());

      // Text preview.
      $colorPreview.find('.color-preview-main').css('color', $colorPalette.find('input[name="palette[text]"]').val());
      $colorPreview.find('.color-preview-main a').css('color', $colorPalette.find('input[name="palette[link]"]').val());
      $colorPreview.find('.color-preview-main h3').css('color', $colorPalette.find('input[name="palette[primaryhighlight]"]').val());
      $colorPreview.find('.color-preview-main h4').css('color', $colorPalette.find('input[name="palette[secondaryhighlight]"]').val());
      $colorPreview.find('.preview-button').css('background-color', $colorPalette.find('input[name="palette[button]"]').val());
      $colorPreview.find('.preview-button').css('color', $colorPalette.find('input[name="palette[buttonlink]"]').val());
    }
  };
})(jQuery, Drupal, drupalSettings);
