(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.ixdSa11y = {
    attach: function attach(context, settings) {

      // Toggle results & close dashboard
      $('#ixd-sa11y-toggle', context).click(function(){
        $('#sa11y-result').click();
        $('.close-tray').click();
      });

      // Toggle impact.
      $('.impact-toggles input', context).click(function () {
        var $this = $(this);
        var impact = $this.val();
        $(this).parent().parent().siblings('.rule-results')
          .find('.violation-' + impact)
          .slideToggle('fast');
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
