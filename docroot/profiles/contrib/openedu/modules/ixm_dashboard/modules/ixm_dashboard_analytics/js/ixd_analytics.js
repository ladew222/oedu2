(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.ixdAnalytics = {
    attach: function attach(context, settings) {

      //Google Charts
      var $gcharts = $('.ixd-analytic-google-chart', context);
      if($gcharts.length)
      {
        $gcharts.each(function () {
          var gid = $(this).attr('data-chart-id');
          if (settings.ixd_analytics[gid] !== undefined) {
            var gChartSettings = settings.ixd_analytics[gid];

            var divID = gChartSettings.divid;
            var optionSet = gChartSettings.optionSet;
            var chartCfg = JSON.parse(gChartSettings.chartCfg);
            var csv = JSON.parse(gChartSettings.csv);
            var csvArr = [];
            for(var x in csv){
              csvArr.push(csv[x]);
            }

            google.charts.load('current', {packages: ['corechart','bar']});
            if(optionSet == 1 || optionSet == 2) //Line Chart
              google.charts.setOnLoadCallback(function(){drawGoogleLineChart(csvArr,divID,optionSet,false,chartCfg)});
            else if(optionSet == 3) //Column Bar Chart
              google.charts.setOnLoadCallback(function(){drawGoogleBarChart(csvArr,divID,optionSet,chartCfg)});
            else if(optionSet == 4)//Dual Line Chart
              google.charts.setOnLoadCallback(function(){drawGoogleLineChart(csvArr,divID,optionSet,true,chartCfg)});
            else if(optionSet == 5)//Pie Chart
              google.charts.setOnLoadCallback(function(){drawGooglePieChart(csvArr,divID,optionSet,chartCfg)});
          }
        });
      }

      //Renders a -Line- type chart (Google Charts)
      function drawGoogleLineChart(chartData,divID,optionSet,dualLines,chartCfg) {
          var data = new google.visualization.DataTable();
          data.addColumn('string', '');
          if(optionSet==1)
            data.addColumn('number', 'Rate');
          else if(optionSet==2)
            data.addColumn('number', 'Sessions');
          else if(optionSet==4)
          {
            data.addColumn('number', 'Recurrent');
            data.addColumn('number', 'Unique');
          }

          var tempStr = "";
          var tempArr = new Array();
          for(var i=1;i<chartData.length;i++)
          {
            tempStr = String(chartData[i]);
            tempArr = tempStr.split(',');

            if(!dualLines)
            {
              data.addRows([
                  [tempArr[0],parseFloat(tempArr[1])],
              ]);
            }
            else if(dualLines)
            {
              data.addRows([
                [tempArr[0],parseFloat(tempArr[1]),parseFloat(tempArr[2])],
              ]);
            }
          }

          var chart = new google.visualization.LineChart(document.getElementById(divID));

          if(optionSet == 1)
            chart.draw(data, chartCfg);
          else if(optionSet == 2)
            chart.draw(data, chartCfg);
          else if(optionSet == 4)
            chart.draw(data, chartCfg);
      }

      //Render a -Bar Graph- Chart (Google Charts)
      function drawGoogleBarChart(chartData,divID,optionSet,chartCfg) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Year');
        data.addColumn('number', 'Score');

        var tempStr = "";
        var tempArr = new Array();
        for(var i=1;i<chartData.length;i++)
        {
          tempStr = String(chartData[i]);
          tempArr = tempStr.split(',');

          data.addRows([
            [tempArr[0],parseFloat(tempArr[1])],
          ]);
        }

        var barsVisualization = new google.visualization.ColumnChart(document.getElementById(divID));
        barsVisualization.draw(data, chartCfg);
      }

      //Render a -Pie Graph- Chart (Google Charts)
      function drawGooglePieChart(chartData,divID,optionSet,chartCfg) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', '');
        data.addColumn('number', 'Users');

        var tempStr = "";
        var tempArr = new Array();
        for(var i=1;i<chartData.length;i++) {
          tempStr = String(chartData[i]);
          tempArr = tempStr.split(',');

          data.addRows([
            [tempArr[0], parseFloat(tempArr[1])],
          ]);
        }

        var chart = new google.visualization.PieChart(document.getElementById(divID));
        chart.draw(data, chartCfg);
      }

      // Datepicker
      var $dateSelector = $('.date-selector', context);
      if ($dateSelector.length) {
        var $picker = $('#analytics-date', context);

        $picker.hide().datepicker({
          'dateFormat': 'yyyy-mm-dd',
          'range': true,
          'inline': true,
          'language': 'en',
          'position': 'bottom right',
          'clearButton': true,
          'onSelect': function (inst, animationCompleted) {
            var components = inst.split(',');
            var from = (0 in components) ? components[0] : false;
            var to = (1 in components) ? components[1] : false;

            if (to && from) {

              var ajaxer = false;
              var selector = $picker.hasClass('reload-dashboard') ? '#display-dashboard' : '#display-analytics';
              $.each(Drupal.ajax.instances, function (idx, item) {
                if (item.selector === selector) {
                  ajaxer = item;
                  return false;
                }
              });

              if (ajaxer) {
                ajaxer.submit.startDate = from;
                ajaxer.submit.endDate = to;

                // Clear the charts.
                var $dashboardTray = $('#ixm-dashboard-tray');
                $dashboardTray.find('.tray-content').html("");
                $dashboardTray.addClass('loading');

                $(selector).trigger('ixm-ajax-display');
              }
            }
          }
        });

        // Show/hide.
        $dateSelector.click(function () {
          $picker.slideToggle();
          return false;
        });

        // Add some BS4 classes to the Clear button
        var $datePickerButtons = $('.datepicker--buttons', context);
        var $datePickerButton = $('.datepicker--button', context);
        $datePickerButtons.addClass('d-flex flex-column flex-md-row justify-content-start justify-content-md-end');
        $datePickerButton.addClass('btn btn-purple');

      }
    }
  };

})(jQuery, Drupal, drupalSettings);
