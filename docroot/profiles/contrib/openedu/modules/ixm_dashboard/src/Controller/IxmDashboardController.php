<?php

namespace Drupal\ixm_dashboard\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\Element;
use Drupal\ixm_dashboard\Utility\DisplayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IxmDashboardController.
 */
class IxmDashboardController extends ControllerBase {

  /**
   * The Dashboard Display Helper.
   *
   * @var \Drupal\ixm_dashboard\Utility\DisplayHelper
   */
  protected $displayHelper;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The settings data.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $dashboardSettings;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ixm_dashboard.display_helper'),
      $container->get('form_builder'),
      $container->get('config.factory')
    );
  }

  /**
   * Constructs a IxmDashboardController object.
   *
   * @param \Drupal\ixm_dashboard\Utility\DisplayHelper $displayHelper
   *   The Dashboard Display Manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(DisplayHelper $displayHelper, FormBuilderInterface $formBuilder, ConfigFactoryInterface $config_factory) {
    $this->displayHelper = $displayHelper;
    $this->formBuilder = $formBuilder;
    $this->dashboardSettings = $config_factory->getEditable('ixm_dashboard.settings');
  }

  /**
   * The main dashboard.
   *
   * @return array
   *   A renderable array to be displayed.
   */
  public function dashboard() {
    $output = [
      'widgets' => [
        '#displays' => [],
        '#theme' => 'ixm_dashboard_widgets',
      ],
    ];

    // Get all the active widgets.
    $widgetDisplays = $this->displayHelper->getDisplaysByStatus(TRUE, TRUE);
    /** @var \Drupal\ixm_dashboard\DisplayInterface $widgetDisplay */
    foreach ($widgetDisplays as $id => $widgetDisplay) {
      $widget = $widgetDisplay->buildWidget();

      // Default theme.
      if (!isset($widget['#theme'])) {
        $widget = ['#theme' => 'ixm_dashboard_widget', '#widget' => $widget, '#display_id' => $id];
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

  /**
   * Displays a page display.
   *
   * @TODO: This may not be needed, providing simply to fallback for now.
   *
   * @param null $id
   *   The ID of the display.
   *
   * @return array
   *   A renderable array.
   */
  public function displayPage($id = NULL) {
    if ($id) {
      $current_display = $this->displayHelper->getDisplays($id);
    }
    else {
      // Get the current alias.
      $path = \Drupal::service('path.current')->getPath();
      $alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);

      // Get all the active displays and compare path.
      $displays = $this->displayHelper->getDisplaysByStatus();
      foreach ($displays as $id => $display) {
        if ($display->getAlias() === $alias) {
          $current_display = $display;
          break;
        }
      }
    }

    // Build the display.
    $output = $current_display->build();

    // Add default theme if not provided.
    if (!isset($output['#theme'])) {
      $output = ['#theme' => 'ixm_dashboard_display', '#display' => $output, '#display_id' => $current_display->id()];

      // Also set title.
      if (!isset($output['#title'])) {
        $output['#title'] = $current_display->label();
      }
    }

    return $output;
  }

  /**
   * Ajax response for building a tray item.
   *
   * @param $id
   *   The display to render.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The command replacement of the tray content.
   */
  public function displayAjax($id) {
    $response = new AjaxResponse();
    $display = $this->displayHelper->getDisplays($id);

    $output = $display->build();

    // Add default theme if not provided.
    if (!isset($output['#theme'])) {

      // Apply widget theme if no children or no themed children as well.
      $make_widget = TRUE;
      foreach (Element::children($output) as $key) {
        if (isset($output[$key]['#theme'])) {
          $make_widget = FALSE;
          break;
        }
      }

      if ($make_widget) {
        $output = [
          'widget' => [
            '#theme' => 'ixm_dashboard_widget',
            '#widget' => $output,
          ],
        ];
      }

      $output = ['#theme' => 'ixm_dashboard_display', '#display' => $output, '#display_id' => $display->id()];

      // Also set title.
      if (!isset($output['#title'])) {
        $output['#title'] = $display->label();
      }
    }

    $response->addCommand(new InvokeCommand('#ixm-dashboard-tray', 'removeClass', ['loading']));
    $response->addCommand(new HtmlCommand('#ixm-dashboard-tray .tray-content', $output));

    return $response;
  }

  // @TODO: is this even needed?.
  public function overview() {
    $output = [];

    $displays = $this->displayHelper->getDisplays();

    $enabled = [];
    $disabled = [];
    $widgets = [];
    foreach ($displays as $display) {
      /** @var \Drupal\ixm_dashboard\DisplayInterface $display */
      if ($display->getStatus()) {
        $enabled[] = $display->label();

        if ($display->showWidget()) {
          $widgets[] = $display->label();
        }

      }
      else {
        $disabled[] = $display->label();
      }
    }

    $output['enabled'] = [
      '#type' => 'details',
      '#title' => $this->t('Enabled Displays'),
      '#open' => TRUE,
      'items' => [
        '#theme' => 'item_list',
        '#items' => $enabled,
      ],
    ];

    $output['widgets'] = [
      '#type' => 'details',
      '#title' => $this->t('Enabled Dashboard Widgets'),
      '#open' => TRUE,
      'items' => [
        '#theme' => 'item_list',
        '#items' => $widgets,
      ],
    ];

    $output['disabled'] = [
      '#type' => 'details',
      '#title' => $this->t('Disabled Displays'),
      '#open' => TRUE,
      'items' => [
        '#theme' => 'item_list',
        '#items' => $disabled,
      ],
    ];

    return $output;
  }

  /**
   * Toggles a Display status.
   *
   * @param $id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function toggleDisplay($id) {
    $display = $this->displayHelper->getDisplays($id);

    // Toggle the status.
    $display->setConfigurationValue('status', !$display->getStatus());

    // Update the collection.
    $this->displayHelper->setDisplayConfig($id, $display->getConfiguration());

    $this->dashboardSettings
      ->set('displays.' . $id, $display->getConfiguration())
      ->save();

    // Rebuild routes if display provided an alias.
    if (!$display->isAjax()) {
      \Drupal::service("router.builder")->setRebuildNeeded();
    }

    drupal_set_message($this->t('The display settings have been updated.'));

    // Go back to main display page.
    return $this->redirect('ixm_dashboard.displays');
  }

}
