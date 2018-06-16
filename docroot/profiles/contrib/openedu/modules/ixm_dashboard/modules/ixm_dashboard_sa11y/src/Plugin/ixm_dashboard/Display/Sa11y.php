<?php

namespace Drupal\ixm_dashboard_sa11y\Plugin\ixm_dashboard\Display;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\ixm_dashboard\DisplayBase;
use Drupal\ixm_dashboard\DisplayInterface;
use Drupal\node\Entity\Node;
use Drupal\sa11y\Sa11yInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a IXM dashboard display/widget for user information.
 *
 * @IxmDashboardDisplay(
 *   id = "sa11y",
 *   label = @Translation("Accessibility"),
 *   description = @Translation("Shows in-page a11y issues from the Sa11y
 *   module."),
 *   icon="accessibility",
 *   status=TRUE,
 *   widget=TRUE,
 * )
 */
class Sa11y extends DisplayBase implements ContainerFactoryPluginInterface, DisplayInterface {

  use StringTranslationTrait;

  /**
   * The Sa11y service.
   *
   * @var \Drupal\sa11y\Sa11y
   */
  protected $sa11y;

  /**
   * Sa11y constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Sa11yInterface $sa11y) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $settings = $this->getSettings();
    $this->sa11y = $sa11y;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('sa11y.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildWidget() {
    $output = ['rows' => []];
    $violations = FALSE;

    $sitemap = \Drupal::request()->getSchemeAndHttpHost() . '/sitemap.xml';
    if ($latest = $this->sa11y->getLatestByUrl($sitemap)) {
      $violations = $this->sa11y->getViolations($latest->id);
    }

    if ($violations) {
      $violation_count = count($violations);
      $count = new FormattableMarkup('<em>@count</em>', ['@count' => count($violation_count)]);
      $output['rows']['violation_summary'] = [
        '#title' => $this->t('Sa11y Violations'),
        '#markup' => '<div class="stat">' . $violation_count . '</div>' . $this->t("There are @count violations on your site. To see the full report, @report", [
          '@count' => $count,
          '@report' => Link::createFromRoute("Click Here", "sa11y.report", ["report_id" => $latest->id])
            ->toString(),
        ]),
      ];

      $sally_settings = \Drupal::config('sa11y.settings');
      $sally_rules = $sally_settings->get('rules');

      $rules = '<div>';
      foreach ($sally_rules as $id => $on) {
        $class = $on ? 'success' : 'danger';
        $rules .= '<span class="badge badge-' . $class . '">' . $id . '</span>&nbsp;';
      }
      $rules .= '</div>';

      $output['rows']['violation_summary']['info'] = [
        '#type' => 'container',
        'markup' => [
          '#markup' => '<h3>' . $this->t('Current Threshold') . '</h3>',
          'rules' => [
            '#markup' => $rules,
          ],
          'settings' => [
            '#markup' => '<p>' . $this->t("@settings to change these settings.", [
              '@settings' => Link::createFromRoute("Click Here", "sa11y.admin_settings")
                ->toString(),
            ]) . '</p>',
          ],
        ],
        '#attributes' => [
          'class' => ['section'],
        ],
      ];

      // Sort by URL.
      $urls = [];
      foreach ($violations as $id => $violation) {
        $urls[$id] = $violation->url;
      }
      $top_hits = array_count_values($urls);
      arsort($top_hits);

      $top_5 = [];
      $base_url = \Drupal::request()->getSchemeAndHttpHost();
      $count = 0;
      foreach ($top_hits as $url => $hits) {
        // Only want top 5.
        if ($count == 5) {
          break;
        }

        $url_obj = Url::fromUri($url, ['attributes' => ['target' => '_blank']]);
        $top_5[] = [
          '#markup' => $this->t('[@count] - @link', [
            '@count' => $hits,
            '@link' => Link::fromTextAndUrl(str_replace($base_url, '', $url), $url_obj)
              ->toString(),
          ]),
        ];
        $count++;
      }

      $output['rows']['top_5'] = [
        '#title' => $this->t("Top 5 Sa11y Violations by page."),
        'links' => [
          '#theme' => 'item_list',
          '#items' => $top_5,
        ],
      ];

    }
    else {
      $output['rows']['no_violations'] = [
        '#title' => $this->t('Sa11y Violations'),
        '#markup' => $latest ? $this->t("There are currently no accessibilty issues on your site.") : $this->t("No report found, you may want to check your @settings.", [
          "@settings" => Link::createFromRoute("Sa11y Settings", "sa11y.admin_settings")
            ->toString(),
        ]),
      ];
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $output = [];

    // Only nodes supported at this time.
    $current_path = \Drupal::request()->get('dashboardPage');
    $url_object = Url::fromUserInput('/' . $current_path);
    $route_name = $url_object->getRouteName();

    if ($route_name !== 'entity.node.canonical') {
      return [
        '#markup' => $this->t("Accessibility data is not available for pages of this type."),
      ];
    }

    // Grab the node.
    $route_parameters = $url_object->getrouteParameters();
    $node = Node::load($route_parameters['node']);

    // See if we have a report for this node.
    $report = $this->sa11y->getLatestByNode($node);

    if ($report && $report->status == Sa11yInterface::COMPLETE) {

      // If this is the homepage, we need a lil magic here.
      $config = \Drupal::config('system.site');
      $front_uri = $config->get('page.front');
      if ('/node/' . $node->id() == $front_uri) {
        $node_url = \Drupal::request()->getSchemeAndHttpHost() . '/';
      }
      else {
        $node_url = $node->toUrl()->setAbsolute()->toString();
      }

      // Only pass paths for sitemaps.
      $url = $report->type != 'single' ? $node_url : NULL;

      $violations = $this->sa11y->getViolations($report->id, $url);
      $sorted_violations = [];
      $counts = [];
      foreach ($violations as $violation) {
        $rules = explode(',', $violation->rule);
        foreach ($rules as $rule) {
          $sorted_violations[$rule][$violation->impact][] = $violation;
          isset($counts['rules'][$rule]) ? $counts['rules'][$rule]++ : $counts['rules'][$rule] = 1;
          isset($counts['impact'][$rule][$violation->impact]) ? $counts['impact'][$rule][$violation->impact]++ : $counts['impact'][$rule][$violation->impact] = 1;
        }
      }

      // Grab the node form and set the action to that page.
      $new_report_form = \Drupal::formBuilder()
        ->getForm('Drupal\sa11y\Form\Sa11yNodeForm', $node);
      $new_report_form['#action'] = url::fromRoute('sa11y.node', ['node' => $node->id()])
        ->toString();

      $output['results'] = [
        '#theme' => 'ixd_sa11y_results',
        '#report' => $report,
        '#violations' => ['counts' => $counts, 'sorted' => $sorted_violations],
        '#form' => $new_report_form,
      ];

    }
    elseif ($report && in_array($report->status, [Sa11yInterface::CREATED, Sa11yInterface::RUNNING])) {
      $output['running'] = [
        '#markup' => $this->t("A report is currently running for this page, please check back later."),
      ];
    }
    else {
      $output['empty'] = [
        '#markup' => $this->t('No accessibility data exists for this page.'),
        'form' => \Drupal::formBuilder()
          ->getForm('Drupal\sa11y\Form\Sa11yNodeForm', $node),
      ];
      // Set the form action to the normal triggering of a node report.
      $output['empty']['form']['#action'] = url::fromRoute('sa11y.node', ['node' => $node->id()])
        ->toString();
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [
      'ixm_dashboard_sa11y/ixd_sa11y',
    ];
  }

}
