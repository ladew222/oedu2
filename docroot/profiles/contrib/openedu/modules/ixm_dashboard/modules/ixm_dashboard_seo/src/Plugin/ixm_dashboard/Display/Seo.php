<?php

namespace Drupal\ixm_dashboard_seo\Plugin\ixm_dashboard\Display;

use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\ixm_dashboard\DisplayBase;
use Drupal\ixm_dashboard\DisplayInterface;
use Drupal\node\Entity\Node;
use Drupal\yoast_seo\EntityAnalyser;
use Drupal\yoast_seo\SeoManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a IXM dashboard display/widget for GA information.
 *
 * @IxmDashboardDisplay(
 *   id = "seo",
 *   label = @Translation("SEO"),
 *   description = @Translation("Shows SEO information from Yoast."),
 *   icon="search",
 *   status=TRUE,
 *   widget=TRUE,
 * )
 */
class Seo extends DisplayBase implements ContainerFactoryPluginInterface, DisplayInterface {

  use StringTranslationTrait;

  /**
   * The Yoast Manager.
   *
   * @var \Drupal\yoast_seo\SeoManager
   */
  protected $seoManager;

  /**
   * The Yoast Analyzer.
   *
   * @var \Drupal\yoast_seo\EntityAnalyser
   */
  protected $entityAnalyzer;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Path that submitted ajax call.
   *
   * @var string
   */
  protected $requestedPage;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SeoManager $seoManager, EntityAnalyser $entityAnalyser, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->seoManager = $seoManager;
    $this->entityAnalyzer = $entityAnalyser;
    $this->requestedPage = \Drupal::request()->get('dashboardPage');
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('yoast_seo.manager'),
      $container->get('yoast_seo.entity_analyser'),
      $container->get('database')
    );
  }

  /**
   * Load a node based on the request path.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null|static
   *   A node or FALSE
   */
  protected function getNodeFromPath() {
    $parts = explode('/', $this->requestedPage);

    if ($parts[0] == 'node') {
      return Node::load($parts[1]);
    }
    elseif ($parts[1] == 'node') {
      return Node::load($parts[2]);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_root;

    if ($node = $this->getNodeFromPath()) {

      if ($field = $this->seoManager->getSeoField($node)) {
        $values = $field->getValue();

        if (!empty($values[0]['focus_keyword'])) {

          // Convert status to Bootstrap colors.
          $bs_class = $this->getClassFromScore($values[0]['status']);

          // Status from Yoast.
          $status = $this->seoManager->getScoreStatus($values[0]['status']);

          // @TODO: Template?.
          $output['row1'] = [
            'score' => [
              '#title' => $this->t('SEO Score'),
              '#markup' => '
              <div class="stat">' . $values[0]['status'] . '</div>
              <div class="seo-status"><i class="material-icons text-' . $bs_class . '">brightness_1</i><span class="status">' . $status . '</span></div>
            ',
            ],
            'avg' => [
              '#theme' => 'ixd_seo_compare',
              '#type' => str_replace('_', ' ', $node->bundle()),
              '#typeCompare' => number_format($values[0]['status'] / $this->getAverageScore($node->bundle()) * 100),
              '#siteCompare' => number_format($values[0]['status'] / $this->getAverageScore() * 100),
            ],
          ];

          $output['row2'] = [
            'focus' => [
              '#title' => $this->t("Focus Keyword"),
              '#markup' => '
              <div class="focus-keyword-container">
                <div class="focus-keyword"><span>' . $values[0]['focus_keyword'] . '&nbsp;</span><i class="float-right material-icons">search</i></div>
                <div class="focus-desc">' . $this->t("The main keyword or phrase this page is about.") . '</div>
              </div>
            ',
            ],
          ];

          // Get the node info as if we're previewing.
          $preview = $this->entityAnalyzer->createEntityPreview($node);

          // Setup the live JS data which can also be used for the snippet.
          $js_data = [
            'preview' => $preview,
            'keyword' => $values['0']['focus_keyword'],
            'root' => $base_root,
          ];

          $output['row3'] = [
            'snippet' => [
              '#title' => $this->t("Snippet Preview"),
              '#theme' => 'ixd_seo_snippet',
            ],
          ];

          // Add theme variables.
          foreach ($js_data as $key => $value) {
            $output['row3']['snippet']['#' . $key] = $value;
          }

          $output['row4'] = [
            'analysis' => [
              '#title' => $this->t("Content Analysis"),
              '#markup' => '<div id="analytis-output"></div>',
              '#attached' => [
                'drupalSettings' => [
                  'ixd_seo' => $js_data,
                ],
              ],
            ],
          ];

          return $output;
        }
        else {
          return [
            '#markup' => $this->t("No SEO data is available for this page, make sure you have set the focus keyword in the edit screen."),
          ];
        }
      }

    }

    if (empty($output)) {
      return [
        '#markup' => $this->t("SEO data is not available for pages of this type."),
      ];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildWidget() {
    $bundles = $this->getEnabledBundles();

    // Split into two groups for better layout.
    $content_types1 = [];
    $content_types2 = [];
    $count = 0;
    foreach ($bundles as $bundle) {
      $score = $this->getAverageScore($bundle) ?: 'N/A';
      $bs_class = $this->getClassFromScore($score);
      $markup = [
        '#markup' => '
          <span class="type-title">' . ucfirst($bundle) . ':</span>&nbsp;
          <span class="status-text badge badge-pill badge-' . $bs_class . '">' . $score . '</span>
        ',
      ];

      if ($count % 2) {
        $content_types1[] = $markup;
      }
      else {
        $content_types2[] = $markup;
      }
      $count++;
    }

    $site_score = $this->getAverageScore();
    $bs_class = $this->getClassFromScore($site_score);
    $icon_val = $this->getIconFromScore($site_score);

    // @TODO: This should probably be a template.
    $output['row'] = [
      'overview' => [
        'row' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['row'],
          ],
          'col1' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['col'],
            ],
            'site_overview' => [
              '#markup' => '
                <h2 class="title">' . $this->t("Site Average") . '</h2>
                <div class="stat">' . $site_score . '</div>
                <div class="seo-status">
                  <i class="material-icons text-' . $bs_class . '">' . $icon_val . '</i>
                  <span class="status">' . $this->seoManager->getScoreStatus($site_score) . '</span>
                </div>
              ',
            ],
          ],
          'col2' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['col'],
            ],
            'top_offenders' => [
              '#markup' => '<h2 class="title">' . $this->t("5 Lowest Scores.") . '</h2>',
              'links' => [
                '#theme' => 'item_list',
                '#items' => $this->getTopFive(),
              ],
            ],
          ],
        ],
      ],
      'by_type' => [
        '#title' => $this->t("AVG Score by Type"),
        'row' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['row'],
          ],
          'col1' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['col'],
            ],
            'list' => [
              '#theme' => 'item_list',
              '#items' => $content_types1,
            ],
          ],
          'col2' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['col'],
            ],
            'list' => [
              '#theme' => 'item_list',
              '#items' => $content_types2,
            ],
          ],
        ],
      ],
    ];

    return $output;
  }

  /**
   * Helper to get overall scores.
   *
   * @param bool $contentType
   *   A content type to filter by.
   *
   * @return int
   *   The overall score.
   */
  protected function getAverageScore($contentType = FALSE) {
    // @TODO: This is assuming the field, could potentially change?.
    $query = $this->database->select('node__field_seo', 's')
      ->fields('s', ['field_seo_status']);
    if ($contentType) {
      $query->condition('s.bundle', $contentType);
    }
    $results = $query->execute()->fetchAll();

    $score = 0;
    foreach ($results as $result) {
      $score += $result->field_seo_status;
    }
    return count($results) ? number_format($score / count($results), 1) : $score;
  }

  /**
   * Builds the top 5 markup.
   *
   * @TODO: Assuming field here, this could change.
   *
   * @return array
   *   Items list.
   */
  protected function getTopFive() {
    $query = $this->database->select('node__field_seo', 's')
      ->fields('s', ['field_seo_status', 'entity_id'])
      ->orderBy('s.field_seo_status', 'asc')
      ->range(0, 5);
    $results = $query->execute()->fetchAll();

    $top_five = [];
    $alias_manager = \Drupal::service('path.alias_manager');
    $count = 0;
    foreach ($results as $result) {

      $url_obj = Url::fromUserInput('/node/' . $result->entity_id, ['attributes' => ['target' => '_blank']]);
      $alias = $alias_manager->getAliasByPath('/node/' . $result->entity_id);

      $bs_class = $this->getClassFromScore($result->field_seo_status);
      $icon_val = $this->getIconFromScore($result->field_seo_status);

      $top_five[] = [
        '#markup' => '<span class="seo-status"><i class="material-icons text-' . $bs_class . '">' . $icon_val . '</i></span>&nbsp;' . $this->t('@score - @link', [
          '@score' => $result->field_seo_status,
          '@link' => Link::fromTextAndUrl($alias, $url_obj)
            ->toString(),
        ]),
      ];
      $count++;
    }

    return $top_five;
  }

  /**
   * Helper to get all bundles with enabled SEO fields.
   *
   * @return array
   *   Enabled bundles.
   */
  protected function getEnabledBundles() {
    $enabled = [];
    $field_manager = \Drupal::service('entity_field.manager');
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();

    /** @var \Drupal\node\Entity\NodeType $definition */
    foreach ($types as $bundle => $definition) {
      $fields = $field_manager->getFieldDefinitions('node', $bundle);
      foreach ($fields as $field) {
        if ($field->getType() === 'yoast_seo') {
          $enabled[] = $bundle;
        }
      }
    }

    return $enabled;
  }

  /**
   * Helper to convert seo status to bootstrap class.
   *
   * @param int $score
   *   The score.
   *
   * @return string
   *   The class.
   */
  protected function getClassFromScore($score) {
    $status = $this->seoManager->getScoreStatus($score);
    switch ($status) {
      case 'good':
        $bs_class = 'success';
        break;

      case 'ok':
        $bs_class = 'warning';
        break;

      case 'bad':
        $bs_class = 'danger';
        break;

      default:
        $bs_class = 'info';
    }

    return $bs_class;
  }

  /**
   * Helper to convert seo status to material design icon.
   *
   * @param int $score
   *   The score.
   *
   * @return string
   *   The icon value.
   */
  protected function getIconFromScore($score) {
    $status = $this->seoManager->getScoreStatus($score);
    switch ($status) {
      case 'good':
        $icon_val = 'check_circle';
        break;

      case 'ok':
        $icon_val = 'new_releases';
        break;

      case 'bad':
        $icon_val = 'warning';
        break;

      default:
        $icon_val = 'brightness_1';
    }

    return $icon_val;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [
      'ixm_dashboard_seo/ixd_seo',
    ];
  }

}
