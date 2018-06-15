/**
 * @file
 *
 * All global scripts for OpenEDU theme
 */
(function ($, window, Drupal) {

  'use strict';

  Drupal.behaviors.oeduMobileNav = {
    attach: function (context, settings) {
      // Navigation toggle
      $('#mobile-nav .navbar-toggle', context).click(function () {
        $('#navbar-hamburger').toggleClass('hidden');
        $('#navbar-close').toggleClass('hidden');
        $('#headerbar').toggleClass('open');
      });
      // Mobile menu dropdown toggle.
      $('.mobile-dropdown-toggle', context).click(function () {
        $(this).parent().toggleClass('open');
      });

    }
  };
  Drupal.behaviors.drop_click = {
    attach: function (context, settings) {
      // course toggle
      $('.course-title a', context).click(function () {
        if($(this).hasClass('minimized')) {
          $(this).removeClass('minimized').addClass('expanded');
          $(this).parent().parent().parent().find( ".details" ).show();
        }
        else {
          $(this).removeClass('expanded').addClass('minimized');
          $(this).parent().parent().parent().find( ".details" ).hide();
        }
      });
    }
  };

  Drupal.behaviors.oeduAccordions = {
    attach: function (context, settings) {
      var $profileSidebar = $('.node--type-profile .block-region-right', context);
      if ($profileSidebar.length) {
        $profileSidebar.find('.block .field .field--label').click(function() {
          $(this).toggleClass('active').siblings('.field__items').collapse('toggle');
        });
        // Set expand on default when greater than mobile, and collapsed when mobile.
        if ($(window).width() > 768) {
          $profileSidebar.find('.block .field .field--label').addClass('active');
          $profileSidebar.find('.block .field .field__items').addClass('collapse in');
        }
        else {
          $profileSidebar.find('.block .field .field--label').addClass('active');
          $profileSidebar.find('.block .field .field__items').addClass('collapse');
        }
      }
    }
  };

  Drupal.behaviors.oeduImages = {
    attach: function (context, settings) {
      // Display image caption from alt tags for all body fields
      $(".main-container .field--name-body img", context).addCaption();

      // Captions for news images.
      $("article.node--type-news .panel-oedu-container .field--name-field-news-image img", context).addCaption();
    }
  };

  // Custom function for adding captions to images
  $.fn.extend({
    addCaption: function (imgCaption) {
      return this.each(function () {
        var img = $(this);
        var imgClass = img.attr('class');
        var imgStyle = img.attr('style');
        var imgAlt = img.attr('alt');

        img.removeAttr('class');
        img.removeAttr('style');

        if (imgCaption === undefined) {
          imgCaption = imgAlt;
        }

        if (imgCaption) {
          img.wrap('<figure></figure>');
          img.after('<figcaption>' + imgCaption + '</figcaption>');
          img.parent('figure').attr('class', imgClass);
          img.parent('figure').attr('style', imgStyle);
        }
      });
    }
  });

    // Match Height on GridBlock items.
    Drupal.behaviors.oeduGridBlockMatchHeight = {
      attach: function (context, settings) {
        $('div[class^="grid-block-"]').matchHeight();
      }
    }

    // start packery on gridblocks
    Drupal.behaviors.vitTileBlockPackery = {
    attach: function (context, settings) {
      var $grid = $('.grid').packery({
        itemSelector: '.grid-item',
        gutter: '.gutter-sizer',
        columnWidth: '.grid-sizer'

      });
    /*  $grid.imagesLoaded({ background: true }).progress( function() {
        $grid.packery();
      });*/

      $('.grid-itemz', context).click(function (event) {
        var $item = $(event.currentTarget);
        //alert('aaa');
        var isExpanded = $item.hasClass('is-expanded');
        //$item.find('.field--name-field-extended-body').toggle();
        if ( isExpanded ) {
          // if shrinking, shiftLayout
          var orig = $item.data('css');
          //$item.animate({opacity: orig.opacity, width: orig.width}, 0);
          $item.width( orig.width );
          $item.toggleClass('is-expanded');
          $grid.packery('shiftLayout');
        } else {
          // if expanding, fit it
          $item.data('css', { opacity: $item.css('opacity'),
            width: $item.css('width') });
          $item.width( '96%' );
          $item.height( '600px' );
          $item.toggleClass('is-expanded');
          $grid.packery( 'fit', event.currentTarget );
        }
      });
    }
  }


})(jQuery, window, Drupal);

