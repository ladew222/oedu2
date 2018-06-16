(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.ixmDashboard = {
    attach: function attach(context, settings) {

      var $dashboard = $('#ixm-dashboard', context);
      var $dashboardTray = $('#ixm-dashboard-tray', context);

      // Ensure both elements exist.
      if ($dashboard.length && $dashboardTray.length) {

        // Add body margin.
        $('body').addClass('ixm-dashboard');

        /**
         * Dashboard Links.
         */
        var $dashboardLinks = $dashboard.find('.dashboard-bar-link a.ajax-display');
        if ($dashboardLinks.length) {

          // Setup Ajax Calls.
          $dashboardLinks.once('ixm-ajax').each(function () {
            Drupal.ajax({
              element: this,
              base: this.id,
              url: $(this).attr('href'),
              event: 'ixm-ajax-display',
              progress: false,
              submit: {
                dashboardPage: drupalSettings.path.currentPath
              }
            });
          });

          // Click event.
          $dashboardLinks.click(function (event) {
            event.preventDefault();
            event.stopPropagation();

            //Close existing accessibility issue tabs
            $("div.sa11y-tip").each( function(){
              $(this).fadeOut('400', function () {
                $(this).remove();
              });
            });

            var $this = $(this);

            // Just close it
            if ($this.parent().hasClass('active')) {
              $this.parent().removeClass('active');
              $dashboardTray.removeClass('open');
              $dashboard.find('.dashboard-bar-link.active-trail').addClass('active');
            }
            else {
              // Reset all and set active
              $dashboardTray.find('.tray-content').html("");
              $dashboard.find('.dashboard-bar-link').removeClass('active');
              $this.parent().addClass('active');

              // Open the tray.
              $dashboardTray.addClass('loading open');

              // Delay Ajax to allow tray to open.
              setTimeout(function(){
                $this.trigger('ixm-ajax-display');
              }, 250);

            }

          });
        }

        /**
         * Tray Close.
         */
        $dashboardTray.find('.close-tray').click(function(){
          $dashboard.find('.dashboard-bar-link').removeClass('active');
          $dashboardTray.removeClass('open');
          $dashboard.find('.dashboard-bar-link.active-trail').addClass('active');
        });

      }

      // Fit text on statistics.
      $('.widget-box .stat', context).fitText(0.40, {'maxFontSize': 36});

    }
  };

  /**
   * Watch the body for attribute changes.
   *
   * This is to detect padding changes from the admin toolbar
   * or other tools that may manipulate the top position
   * of the dashboard.
   */
  var observerConfig = {attributes: true, subtree: false, childList: false, characterData: false};
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === "style") {
        updateDashboardTop();
      }
    });
  });
  observer.observe(document.getElementsByTagName("BODY")[0], observerConfig);

  /**
   * Helper for when the admin toolbar is enabled.
   */
  function updateDashboardTop() {
    var $windowsize = $(window).width();
    var $bodyPadding = $('body').css('padding-top');
    var $dashboard = $('#ixm-dashboard');
    var $dashboardTray = $('#ixm-dashboard-tray');

    if ($windowsize > 610) {
      $dashboard.css('top', $bodyPadding);
      $dashboardTray.css({'top': $bodyPadding, 'height': 'calc(100% - ' + $bodyPadding + ')'});
    } else {
      $dashboard.removeAttr('style');
      $('.dashboard-region-content').css('padding-bottom', $bodyPadding);
    }
  }

  $(window).resize(updateDashboardTop);

})(jQuery, Drupal, drupalSettings);
