<?php

namespace Drupal\ixm_dashboard_analytics\Plugin\ixm_dashboard\Display;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ixm_dashboard\DisplayBase;
use Drupal\ixm_dashboard\DisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a IXM dashboard display/widget for GA information.
 *
 * @IxmDashboardDisplay(
 *   id = "analytics",
 *   label = @Translation("Analytics"),
 *   description = @Translation("Shows google analytics information."),
 *   icon="graphic_eq",
 *   status=TRUE,
 *   widget=TRUE,
 *   settings={
 *    "tray"={
 *      "showPageViews"=TRUE,
 *      "showBounceRate"=TRUE,
 *      "showChannels"=TRUE,
 *      "showSessions"=TRUE,
 *      "showAvgTime"=TRUE,
 *    },
 *    "dashboard"={
 *      "showPageViews"=TRUE,
 *      "showBounceRate"=TRUE,
 *      "showChannels"=TRUE,
 *      "showSessions"=TRUE,
 *      "showAvgTime"=TRUE,
 *    }
 *   },
 * )
 */
class Analytics extends DisplayBase implements ContainerFactoryPluginInterface, DisplayInterface {

  use StringTranslationTrait;

  /**
   * Custom settings for this plugin.
   *
   * @var array
   */
  protected $settings;

  /**
   * The alias of the current page.
   *
   * @var string
   */
  protected $currentAlias;

  /**
   * The start date for reports.
   *
   * @var int
   */
  protected $startDate;

  /**
   * The end date for reports.
   *
   * @var int
   */
  protected $endDate;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AliasManagerInterface $aliasManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->settings = $this->getSettings();

    // Check the current path from the AJAX call.
    $current_path = \Drupal::request()->get('dashboardPage');

    // If this is the homepage, we need a lil magic here.
    $config = \Drupal::config('system.site');
    $front_uri = $config->get('page.front');
    if ('/' . $current_path == $front_uri) {
      $this->currentAlias = '/';
    }
    // Just get the alias.
    else {
      $this->currentAlias = $aliasManager->getAliasByPath('/' . $current_path);
    }

    // Change dates if ajaxed, otherwise default.
    $start = \Drupal::request()->get('startDate');
    $end = \Drupal::request()->get('endDate');

    // @TODO: Put the defaults as settings?.
    $this->startDate = $start ? strtotime($start) : strtotime('31 days ago');
    $this->endDate = $end ? strtotime($end) : strtotime('-1 day');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.alias_manager')
    );
  }

  /**
   * Default chart configuration for all google charts.
   *
   * @TODO: Use moduleInvokeAll to allow hooks/alters here.
   *
   * @return array
   *   Set chart config based on type of chart needed
   */
  protected function getChartConfig($chartPreset) {
    if ($chartPreset == "bouncerate") {
      $config = [
        'chartArea' => [
          'width' => '90%',
          'height' => '65%',
        ],
        'backgroundColor' => 'transparent',
        'legend' => [
          'position' => 'none',
        ],
        'hAxis' => [
          'textStyle' => [
            'color' => '#707070',
          ],
        ],
        'vAxis' => [
          'textStyle' => [
            'color' => '#707070',
          ],
        ],
        'lineWidth' => '2',
        'baselineColor' => '#cbcbcb',
        'pointSize' => '6',
        'colors' => ['#ed7732'],
      ];
    }
    elseif ($chartPreset == "sessions") {
      $config = [
        'chartArea' => [
          'width' => '90%',
          'height' => '65%',
        ],
        'backgroundColor' => 'transparent',
        'legend' => [
          'position' => 'none',
        ],
        'lineWidth' => '2',
        'hAxis' => [
          'textPosition' => 'none',
        ],
        'vAxis' => [
          'gridlines' => [
            'color' => 'none',
          ],
          'textPosition' => 'none',
        ],
        'baselineColor' => 'none',
        'colors' => ['#ed7732'],
      ];
    }
    elseif ($chartPreset == "averagetime") {
      $config = [
        'chartArea' => [
          'width' => '95%',
          'height' => '100%',
        ],
        'backgroundColor' => 'transparent',
        'legend' => [
          'position' => 'none',
        ],
        'lineWidth' => '2',
        'vAxis' => [
          'gridlines' => [
            'color' => 'none',
          ],
        ],
        'baselineColor' => 'none',
        'colors' => ['#ed7732'],
      ];
    }
    elseif ($chartPreset == "pageviews") {
      $config = [
        'chartArea' => [
          'width' => '90%',
          'height' => '65%',
        ],
        'backgroundColor' => 'transparent',
        'legend' => [
          'position' => 'top',
          'textStyle' => [
            'color' => '#909090',
          ],
        ],
        'hAxis' => [
          'textStyle' => [
            'color' => '#707070',
          ],
        ],
        'vAxis' => [
          'textStyle' => [
            'color' => '#707070',
          ],
        ],
        'lineWidth' => '2',
        'baselineColor' => '#cbcbcb',
        'series' => [
          0 => [
            'pointShape' => 'circle',
            'pointSize' => '6',
          ],
          1 => [
            'pointShape' => 'diamond',
            'pointSize' => '8',
          ],
        ],
        'colors' => ['#ed7732', '#992796'],
      ];
    }
    elseif ($chartPreset == "channels") {
      $config = [
        'chartArea' => [
          'width' => '90%',
          'height' => '90%',
        ],
        'backgroundColor' => 'transparent',
        'legend' => 'none',
        'pieSliceText' => 'label',
        'pieStartAngle' => '0',
        'colors' => ['#ed582f', '#ed7732'],
      ];
    }
    return $config;
  }

  /**
   * Build out the page views widget.
   *
   * @return mixed
   *   A renderable array or false.
   */
  protected function buildPageViewsGoogle($widget = FALSE) {

    // Not on.
    if (!$widget && empty($this->settings['tray']['showPageViews'])) {
      return FALSE;
    }

    // Not on.
    if ($widget && empty($this->settings['dashboard']['showPageViews'])) {
      return FALSE;
    }

    $params = [
      'metrics' => [
        'ga:pageviews',
        'ga:uniquePageviews',
      ],
      'dimensions' => 'ga:date',
      'sort_metric' => 'ga:date',
      'start_date' => $this->startDate,
      'end_date' => $this->endDate,
    ];

    $params['filters'] = 'ga:pagePath==' . $this->currentAlias;

    $daily = google_analytics_reports_api_report_data($params);

    if (!empty($daily->results->rows)) {
      $block = [];
      $total = $daily->results->totalsForAllResults['pageviews'];
      $results = $daily->results->rows;

      $block['header'] = [
        '#markup' => '
          <div>
            <div class="title">' . $this->t('Page Views') . '</div>
            <div class="stat">' . $total . '</div>
          </div>',
      ];

      $block['chart'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'gchart_pageviews_div',
          'data-chart-id' => 'pageViews',
          'class' => ['ixd-analytic-google-chart'],
        ],
      ];

      $csv = [0 => ["x", "Recurrent", "Unique"]];
      foreach ($results as $id => $row) {
        $csv[] = [
          date('M j', $row['date']),
          $row['pageviews'],
          $row['uniquePageviews'],
        ];
      }

      $config = $this->getChartConfig('pageviews');
      $block['#attached']['drupalSettings']['ixd_analytics'] = [
        'pageViews' => [
          'csv' => JSON::encode($csv),
          'divid' => 'gchart_pageviews_div',
          'optionSet' => 4,
          'chartCfg' => JSON::encode($config),
        ],
      ];

      return $block;
    }

    return FALSE;
  }

  /**
   * Build out the bounce rate widget.
   *
   * @return mixed
   *   A renderable array or false.
   */
  protected function buildBounceRateGoogle($widget = FALSE) {

    // Not on.
    if (!$widget && empty($this->settings['tray']['showBounceRate'])) {
      return FALSE;
    }

    // Not on.
    if ($widget && empty($this->settings['dashboard']['showBounceRate'])) {
      return FALSE;
    }

    $params = [
      'metrics' => 'ga:bounceRate',
      'dimensions' => 'ga:date',
      'sort_metric' => 'ga:date',
      'start_date' => $this->startDate,
      'end_date' => $this->endDate,
    ];

    if (!$widget) {
      $params['filters'] = 'ga:pagePath==' . $this->currentAlias;
    }

    $daily = google_analytics_reports_api_report_data($params);

    if (!empty($daily->results->rows)) {
      $block = [];
      $total = $daily->results->totalsForAllResults['bounceRate'];
      $results = $daily->results->rows;

      $csv = [0 => ["x", "Rate"]];
      foreach ($results as $id => $row) {
        $csv[] = [
          date('M j', $row['date']),
          number_format($row['bounceRate'], 2),
        ];
      }

      $block['header'] = [
        '#markup' => '
          <div>
            <div class="title">' . $this->t('Bounce Rate') . '</div>
            <div class="stat">' . number_format($total, 2) . '%</div>
          </div>',
      ];

      $block['chart'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'gchart_bouncerate_div',
          'data-chart-id' => 'bounceRateGoogle',
          'class' => ['ixd-analytic-google-chart'],
        ],
      ];

      $config = $this->getChartConfig('bouncerate');
      $block['#attached']['drupalSettings']['ixd_analytics'] = [
        'bounceRateGoogle' => [
          'csv' => JSON::encode($csv),
          'divid' => 'gchart_bouncerate_div',
          'optionSet' => 1,
          'chartCfg' => JSON::encode($config),
        ],
      ];

      return $block;
    }

    return FALSE;
  }

  /**
   * Build out the channels widget.
   *
   * @return mixed
   *   A renderable array or false.
   */
  protected function buildChannelsGoogle($widget = FALSE) {

    // Not on.
    if (!$widget && empty($this->settings['tray']['showChannels'])) {
      return FALSE;
    }

    // Not on.
    if ($widget && empty($this->settings['dashboard']['showChannels'])) {
      return FALSE;
    }

    $params = [
      'metrics' => 'ga:users',
      'dimensions' => 'ga:channelGrouping',
      'start_date' => $this->startDate,
      'end_date' => $this->endDate,
    ];

    if (!$widget) {
      $params['filters'] = 'ga:pagePath==' . $this->currentAlias;
    }

    $totals = google_analytics_reports_api_report_data($params);

    if (!empty($totals->results->rows)) {
      $block = [];

      $block['header'] = [
        '#markup' => '<div class="title">' . $this->t('Channels') . '</div>',
      ];

      $block['chart'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'gchart_channels_div',
          'data-chart-id' => 'channels',
          'class' => ['ixd-analytic-google-chart'],
          'style' => ['padding-top:25px'],
        ],
      ];

      $csv = [0 => ["x", "Users"]];
      foreach ($totals->results->rows as $id => $row) {
        $csv[] = [$row['channelGrouping'], $row['users']];
      }

      $config = $this->getChartConfig('channels');
      $block['#attached']['drupalSettings']['ixd_analytics'] = [
        'channels' => [
          'csv' => JSON::encode($csv),
          'divid' => 'gchart_channels_div',
          'optionSet' => 5,
          'chartCfg' => JSON::encode($config),
        ],
      ];

      return $block;

    }
    return FALSE;
  }

  /**
   * Build out the sessions widget.
   *
   * @return mixed
   *   A renderable array or false.
   */
  protected function buildSessionsGoogle($widget = FALSE) {

    // Not on.
    if (!$widget && empty($this->settings['tray']['showSessions'])) {
      return FALSE;
    }

    // Not on.
    if ($widget && empty($this->settings['dashboard']['showSessions'])) {
      return FALSE;
    }

    $params = [
      'metrics' => 'ga:sessions',
      'dimensions' => 'ga:date',
      'start_date' => $this->startDate,
      'end_date' => $this->endDate,
      'sort_metric' => 'ga:date',
    ];

    if (!$widget) {
      $params['filters'] = 'ga:pagePath==' . $this->currentAlias;
    }

    $totals = google_analytics_reports_api_report_data($params);

    if (!empty($totals->results->rows)) {
      $block = [];

      $session_count = 0;
      $csv = [0 => ["x", "Sessions"]];
      foreach ($totals->results->rows as $id => $row) {
        $csv[] = [date('M j', $row['date']), $row['sessions']];
        $session_count += $row['sessions'];
      }

      $block['container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['row', 'no-gutters'],
          'style' => ['padding-top:15px;padding-bottom:15px'],
        ],
      ];

      // Wrap in widget box on dashboard page.
      if ($widget) {
        $block['container']['#prefix'] = '<div class="widget-content widget-box">';
        $block['container']['#suffix'] = '</div>';
      }

      $block['container']['chart'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'gchart_sessions_div',
          'data-chart-id' => 'sessions',
          'class' => ['col-md-6 ixd-analytic-google-chart'],
          'style' => ['min-height:50px;min-width:50px'],
        ],
      ];

      $block['container']['info'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['col-md-6'],
        ],
        'markup' => [
          '#markup' => '
            <div class="title">' . $this->t("Sessions") . '</div>
            <div class="stat">' . $session_count . '</div>
            <p>' . $this->t("Unique sessions in the selected date range.") . '</p>
          ',
        ],
      ];

      $config = $this->getChartConfig('sessions');
      $block['#attached']['drupalSettings']['ixd_analytics'] = [
        'sessions' => [
          'csv' => JSON::encode($csv),
          'divid' => 'gchart_sessions_div',
          'optionSet' => 2,
          'chartCfg' => JSON::encode($config),
        ],
      ];

      return $block;

    }
    return FALSE;
  }

  /**
   * Build out the avg time widget.
   *
   * @return mixed
   *   A renderable array or false.
   */
  protected function buildAvgTimeGoogle($widget = FALSE) {

    // Not on.
    if (!$widget && empty($this->settings['tray']['showAvgTime'])) {
      return FALSE;
    }

    // Not on.
    if ($widget && empty($this->settings['dashboard']['showAvgTime'])) {
      return FALSE;
    }

    $params = [
      'metrics' => 'ga:avgTimeOnPage',
      'start_date' => $this->startDate,
      'end_date' => $this->endDate,
      'dimensions' => 'ga:date',
      'sort_metric' => 'ga:date',
    ];

    if (!$widget) {
      $params['filters'] = 'ga:pagePath==' . $this->currentAlias;
    }

    $daily = google_analytics_reports_api_report_data($params);

    if (!empty($daily->results->rows)) {
      $page_total = $daily->results->totalsForAllResults['avgTimeOnPage'];

      $block = [];

      $csv = [0 => ["x", "avg"]];
      foreach ($daily->results->rows as $id => $row) {
        $csv[] = [
          date('M j', $row['date']),
          number_format($row['avgTimeOnPage'], 2),
        ];
      }

      $block['container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['row', 'no-gutters'],
          'style' => ['padding-top:24px;padding-bottom:24px;'],
        ],
      ];

      // Wrap in widget-box if on dashboard page.
      if ($widget) {
        $block['container']['#prefix'] = '<div class="widget-box">';
        $block['container']['#suffix'] = '</div>';
      }

      $block['container']['chart'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'gchart_avgtime_div',
          'data-chart-id' => 'avgTimeOnPage',
          'class' => ['col-md-6 ixd-analytic-google-chart'],
          'style' => ['min-height:50px;min-width:50px'],
        ],
      ];

      $block['container']['info'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['col-md-6'],
        ],
        'markup' => [
          '#markup' => '
            <div class="title">' . $this->t("Avg Time on Page") . '</div>
            <div class="stat">' . gmdate('H:i:s', $page_total) . '</div>
          ',
        ],
      ];

      // Add in the site-wide average.
      if (!$widget) {
        unset($params['filters']);

        $totals = google_analytics_reports_api_report_data($params);
        if (!empty($totals->results->rows)) {
          $site_total = $totals->results->totalsForAllResults['avgTimeOnPage'];
          $block['container']['info']['markup']['#markup'] .= '<p>' . $this->t("Site Avg: @avg", ['@avg' => gmdate('H:i:s', $site_total)]) . '</p>';
        }
      }

      $config = $this->getChartConfig('averagetime');
      $block['#attached']['drupalSettings']['ixd_analytics'] = [
        'avgTimeOnPage' => [
          'csv' => JSON::encode($csv),
          'divid' => 'gchart_avgtime_div',
          'optionSet' => 3,
          'chartCfg' => JSON::encode($config),
        ],
      ];

      return $block;
    }
    return FALSE;
  }

  /**
   * Simple date picker array.
   *
   * @return array
   *   a render array.
   */
  protected function buildDatepicker($widget = FALSE) {
    $widget_class = $widget ? 'reload-dashboard' : '';

    return [
      '#markup' => '
        <div class="analytics-date-selector text-align-right">
          <div class="analytics-date-range-box">
            <div class="analytics-date-range-title">' . $this->t('Date Range:') . '</div>
            <a href="#" class="date-selector d-block w-100">
              <span class="start">' . date('j M Y', $this->startDate) . '</span> - 
              <span class="end">' . date('j M Y', $this->endDate) . '</span>
              <i class="material-icons float-right">keyboard_arrow_down</i>
            </a>
            <div id="analytics-date" class="date-calendar mt-3 ' . $widget_class . '"></div>
          </div>
        </div>
      ',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    if ($page_views = $this->buildPageViewsGoogle(TRUE)) {
      $output['row1']['page-views-google'] = $page_views;
    }

    if ($channels = $this->buildChannelsGoogle()) {
      $output['row2']['channels-google'] = $channels;
    }

    if ($page_views = $this->buildBounceRateGoogle()) {
      $output['row3']['sessions-google'] = $page_views;
    }

    if ($sessions = $this->buildSessionsGoogle()) {
      $output['row4']['sessions_google'] = $sessions;
    }

    if ($avg_time = $this->buildAvgTimeGoogle()) {
      $output['row4']['avg_time_google'] = $avg_time;
    }

    // No data. @TODO: Add API check as well.
    if (empty($output)) {
      $output = [
        '#markup' => $this->t('There is currently no analytic data for this page.'),
      ];
    }
    else {
      $datepicker['datepicker'] = $this->buildDatepicker();
      $output = array_merge($datepicker, $output);
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function buildWidget() {

    if ($page_views = $this->buildBounceRateGoogle(TRUE)) {
      $output['row1']['bounce-rate-google'] = $page_views;
    }

    if ($sessions = $this->buildSessionsGoogle(TRUE)) {
      $output['row1']['group']['sessions-google'] = $sessions;
    }

    if ($avg_time = $this->buildAvgTimeGoogle(TRUE)) {
      $output['row1']['group']['avg_time-google'] = $avg_time;
    }

    if (isset($output['row1']['group'])) {
      $output['row1']['group']['#no_widget'] = TRUE;
    }

    if ($page_views = $this->buildPageViewsGoogle(TRUE)) {
      $output['row2']['page-views-google'] = $page_views;
    }

    if ($channels = $this->buildChannelsGoogle(TRUE)) {
      $output['row2']['channels-google'] = $channels;
    }

    // No data.
    if (empty($output)) {
      $output = [
        '#markup' => $this->t('There is currently no analytic data for this page.'),
      ];
    }
    else {
      $datepicker['datepicker'] = $this->buildDatepicker(TRUE);
      $output = array_merge($datepicker, $output);
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // @TODO: Once widgets are on their own, this will be just for the tray.
    $form['tray'] = [
      '#title' => $this->t('Analytics Display Widgets'),
      '#type' => 'details',
      '#open' => TRUE,
    ];

    $tray = isset($this->settings['tray']) ? $this->settings['tray'] : [];
    $form['tray']['showPageViews'] = [
      '#title' => $this->t('Show Page Views'),
      '#type' => 'checkbox',
      '#default_value' => isset($tray['showPageViews']) ? $tray['showPageViews'] : NULL,
    ];

    $form['tray']['showChannels'] = [
      '#title' => $this->t('Show Channels'),
      '#type' => 'checkbox',
      '#default_value' => isset($tray['showChannels']) ? $tray['showChannels'] : NULL,
    ];

    $form['tray']['showBounceRate'] = [
      '#title' => $this->t('Show Bounce Rate'),
      '#type' => 'checkbox',
      '#default_value' => isset($tray['showBounceRate']) ? $tray['showBounceRate'] : NULL,
    ];

    $form['tray']['showSessions'] = [
      '#title' => $this->t('Show Sessions'),
      '#type' => 'checkbox',
      '#default_value' => isset($tray['showSessions']) ? $tray['showSessions'] : NULL,
    ];

    $form['tray']['showAvgTime'] = [
      '#title' => $this->t('Show Average Time on Page'),
      '#type' => 'checkbox',
      '#default_value' => isset($tray['showAvgTime']) ? $tray['showAvgTime'] : NULL,
    ];

    // Dashboard widgets.
    $form['dashboard'] = [
      '#title' => $this->t('Dashboard Widgets'),
      '#type' => 'details',
      '#open' => TRUE,
    ];

    $dashboard = isset($this->settings['dashboard']) ? $this->settings['dashboard'] : [];
    $form['dashboard']['showPageViews'] = [
      '#title' => $this->t('Show Page Views'),
      '#type' => 'checkbox',
      '#default_value' => isset($dashboard['showPageViews']) ? $dashboard['showPageViews'] : NULL,
    ];

    $form['dashboard']['showChannels'] = [
      '#title' => $this->t('Show Channels'),
      '#type' => 'checkbox',
      '#default_value' => isset($dashboard['showChannels']) ? $dashboard['showChannels'] : NULL,
    ];

    $form['dashboard']['showBounceRate'] = [
      '#title' => $this->t('Show Bounce Rate'),
      '#type' => 'checkbox',
      '#default_value' => isset($dashboard['showBounceRate']) ? $dashboard['showBounceRate'] : NULL,
    ];

    $form['dashboard']['showSessions'] = [
      '#title' => $this->t('Show Sessions'),
      '#type' => 'checkbox',
      '#default_value' => isset($dashboard['showSessions']) ? $dashboard['showSessions'] : NULL,
    ];

    $form['dashboard']['showAvgTime'] = [
      '#title' => $this->t('Show Average Time on Page'),
      '#type' => 'checkbox',
      '#default_value' => isset($dashboard['showAvgTime']) ? $dashboard['showAvgTime'] : NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [
      'ixm_dashboard_analytics/ixd_analytics',
    ];
  }

}
