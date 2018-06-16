/**
 * @file
 *
 * All global scripts for OpenEDU theme
 */
(function ($, window, Drupal) {

  'use strict';

  Drupal.behaviors.oeduMenuToggle = {
    attach: function (context, settings) {
      var $togglerIcon = $('header .navbar-toggler', context);
      if ($togglerIcon.length) {
        $togglerIcon.click(function(e) {
          e.preventDefault();
          var el = $(this).find('i');
          if (el.text() === el.data("text-alter")) {
            el.text(el.data("text-original"));
          } else {
            el.data("text-original", el.text());
            el.text(el.data("text-alter"));
          }
        });
      }
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
          $profileSidebar.find('.block .field .field__items').addClass('collapse show');
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

      // Captions for event images.
      $("article.node--type-event .panel-oedu-container .field--name-field-event-image img", context).addCaption();
    }
  };

  /**
   * Events special behaviours.
   * @type {{attach: Drupal.behaviors.oeduEvents.attach}}
   */
  Drupal.behaviors.oeduEvents = {
    attach: function (context, settings) {
      var $eventsFilterBlock = $('.panel-oedu-container', context).find('#views-exposed-form-event-latest-events-block');
      if ($eventsFilterBlock.length) {
        // @TODO English only currently, needs multilingual support.
        // Add a datepicker to the event filter block.
        var $eventDatepicker = $('<div class="events-filter-datepicker"></div>');
        $eventDatepicker.append($('<div class="responsive-dropdown"><i class="material-icons" aria-hidden="true">date_range</i><p>View Events Calendar</p></div>'));
        $eventDatepicker.insertAfter($eventsFilterBlock);
        $eventDatepicker.once('events-datepicker').datepicker({
          'dateFormat': 'yyyy-mm-dd 00:00:00',
          'range': true,
          'inline': true,
          'language': 'en',
          'onSelect': function(inst, animationCompleted) {
            var components = inst.split(',');
            var from = (0 in components) ? components[0] : '';
            var to = (1 in components) ? components[1] : from;
            $eventsFilterBlock.find('[name="field_event_date_value"]').val(to.replace('00:00:00', '23:59:49'));
            $eventsFilterBlock.find('[name="field_event_date_end_value"]').val(from);
            $eventsFilterBlock.find('.form-submit').click();
          }
        });

        $(document).ajaxStop(function() {
          $('.ajax-progress').remove();
        });

        $('.responsive-dropdown').on('click', function(event) {
          $(this).toggleClass('open').siblings('.datepicker-inline').toggleClass('open');
        });
      }

      var $addToAny = $('.page-node-type-event .block-addtoany-block');
      if ($addToAny.length) {
        $addToAny.once('events--addtoany')
            .prepend('<label>' + Drupal.t('Share article on') + ':</label>')
            .append('<a href="#" class="top">' + Drupal.t('Back to top') + ' ^</a>');
      }

      // Calender icon click to toggle addtocal.
      var $dateField = $('.field--name-field-event-date');
      if ($dateField.length) {
        $dateField.once('.material-icons').click(function() {
          var $addToCal = $('.field--name-field-event-date .addtocal');
          $addToCal.click();
        });
      }

      //Hide flickity arrow buttons
      Drupal.behaviors.oeduHideFlickityArrows.attach();
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

  // Add class to the parent form when focusing on the newsletter input field.
  Drupal.behaviors.oeduFocusClass = {
    attach: function (context, settings) {
      $(".contact-message-openedu-newsletter-form input").on('focus', function() {
        $(this).parents("form").toggleClass('form-focusing');
      });
    }
  };

  // Match Height on GridBlock items.
  Drupal.behaviors.oeduGridBlockMatchHeight = {
    attach: function (context, settings) {
      $('div[class^="grid-block-"]').matchHeight();
    }
  };

  // Hide Flickity Arrows when less than 2 imgs in slide
  var flickHide = false;
  Drupal.behaviors.oeduHideFlickityArrows = {
    attach: function (context, settings) {
      if(!flickHide) {
        var $gallery = $('.flickity');
        var $galleryCells = $gallery.find('.gallery-cell');
        if ($galleryCells.length < 2) {
          $gallery.addClass('flickity-hide-nav');
          $gallery.find('.flickity-prev-next-button').hide();
          flickHide = true;
        }
      }
    }
  };

  Drupal.behaviors.oeduHideArrows = {
    attach: function (context, settings) {
      $("input[type='password'][data-eye]").each(function (i) {
        var $this = $(this);

        $this.wrap($("<div/>", {
          style: 'position:relative'
        }));

        $this.css({
          paddingRight: 60
        });

        $this.after($("<div/>", {
          html: 'Show',
          class: 'btn btn-primary btn-sm',
          id: 'passeye-toggle-' + i,
          style: 'position:absolute;right:10px;top:50%;transform:translate(0,-50%);padding: 2px 7px;font-size:12px;cursor:pointer;'
        }));

        $this.after($("<input/>", {
          type: 'hidden',
          id: 'passeye-' + i
        }));

        $this.on("keyup paste", function () {
          $("#passeye-" + i).val($(this).val());
        });

        $("#passeye-toggle-" + i).on("click", function () {
          if ($this.hasClass("show")) {
            $this.attr('type', 'password');
            $this.removeClass("show");
            $(this).removeClass("btn-outline-primary");
          } else {
            $this.attr('type', 'text');
            $this.val($("#passeye-" + i).val());
            $this.addClass("show");
            $(this).addClass("btn-outline-primary");
          }
        });

      });
    }
  };

})(jQuery, window, Drupal);
