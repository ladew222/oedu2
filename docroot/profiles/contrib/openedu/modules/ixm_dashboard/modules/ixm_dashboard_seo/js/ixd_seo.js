(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.ixdSeo = {
    attach: function attach(context, settings) {

      if ($('.display-container', context).length) {

        if (typeof settings.ixd_seo === 'undefined') {
          throw 'No settings specified for the SEO Display.';
        }


        var config = {
          targets: {
            output: "analytis-output",
          },
          callbacks: {
            getData: function () {

              var defaultData = {
                meta: '',
                metaTitle: '',
                locale: 'en_US',
                keyword: settings.ixd_seo.keyword,
                permalink: settings.ixd_seo.root + settings.ixd_seo.preview.url,
                titleWidth: 311 //@TODO
              };

              return Object.assign({}, defaultData, settings.ixd_seo.preview);
            },
            saveScores: function() {
              //@TODO
            },
            saveContentScore: function () {
              //@TODO
            }
          }
        };

        var app = new RealTimeSEO.App(config);
        app.analyze()
      }

    }
  };

})(jQuery, Drupal, drupalSettings);
