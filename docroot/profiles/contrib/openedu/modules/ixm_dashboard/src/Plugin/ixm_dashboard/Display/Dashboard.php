<?php

namespace Drupal\ixm_dashboard\Plugin\ixm_dashboard\Display;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\ixm_dashboard\DisplayBase;
use Drupal\ixm_dashboard\Utility\DisplayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides The main IXM dashboard display.
 *
 * @IxmDashboardDisplay(
 *   id = "dashboard",
 *   label = @Translation("Dashboard"),
 *   description = @Translation("Shows a summary of available display widgets."),
 *   icon="dashboard",
 *   status=TRUE,
 *   widget=FALSE,
 *   weight=-10,
 * )
 */
class Dashboard extends DisplayBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The Dashboard Display Helper.
   *
   * @var \Drupal\ixm_dashboard\Utility\DisplayHelper
   */
  protected $displayHelper;

  /**
   * Constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DisplayHelper $displayHelper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->displayHelper = $displayHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ixm_dashboard.display_helper')
    );
  }

  /**
   * Returns the output of the plugin as a renderable array.
   *
   * @return array
   *   Returns a render array of the plugin display.
   */
  public function build() {
    $output = [
      'widgets' => [
        '#displays' => [],
        '#theme' => 'ixm_dashboard_widgets',
      ],
    ];

    // Add Configuration Button.
    $output['widgets']['#displays']['ixm_config'] = [
      'widget' => [
        '#markup' => '<div class="mb-3"><p>' .
        $this->t('The IXM Dashboard can display widgets from any active display.') . '<br/>' .
        $this->t('Displays may provide their own settings as well.') .
        '</p></div>',
        'configButton' => [
          '#type' => 'link',
          '#url' => Url::fromRoute('ixm_dashboard.overview'),
          '#title' => Markup::create('<div class="button btn btn-primary">' . $this->t('Configure Dashboard') . '</div>'),
        ],
      ],
      '#type' => 'block',
      'description' => 'IXM Dashboard Settings',
      '#attributes' => [
        'id' => 'widget-ixm-config',
      ],
    ];

    // Get all the active widgets.
    $widgetDisplays = $this->displayHelper->getDisplaysByStatus(TRUE, TRUE);
    /** @var \Drupal\ixm_dashboard\DisplayInterface $widgetDisplay */
    foreach ($widgetDisplays as $id => $widgetDisplay) {
      $widget = $widgetDisplay->buildWidget();

      // Default theme.
      if (!isset($widget['#theme'])) {
        $widget = [
          '#theme' => 'ixm_dashboard_widget',
          '#widget' => $widget,
          '#display_id' => $id,
        ];
      }

      // For display.
      $output['widgets']['#displays'][$id] = [
        'widget' => $widget,
        'label' => $widgetDisplay->label(),
        'description' => $widgetDisplay->getDescription(),
        '#attributes' => [
          'id' => Html::getId('widget-' . $id),
        ],
      ];
    }

    return $output;
  }

}
