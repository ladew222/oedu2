<?php

namespace Drupal\ixm_dashboard\Utility;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ixm_dashboard\DisplayManager;
use Drupal\ixm_dashboard\DisplayPluginCollection;

/**
 * Class DisplayHelper.
 *
 * @package Drupal\ixm_dashboard\Utility
 */
class DisplayHelper {

  /**
   * The Dashboard Display Manager.
   *
   * @var \Drupal\ixm_dashboard\DisplayManager
   */
  protected $displayManager;

  /**
   * The Dashboard Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $dashboardConfig;

  /**
   * Configured displays.
   *
   * An associative array of displays, keyed by the
   * instance ID of each display and using the properties:
   * - id: The plugin ID of the display plugin instance.
   * - provider: The name of the provider that owns the display.
   * - status: (optional) A Boolean indicating whether the display is
   *   enabled. Defaults to FALSE.
   * - widget: (optional) A Boolean indicating whether the display is showing
   *   a widget in the dashboard page. Defaults to FALSE.
   * - weight: (optional) The weight of the display. Defaults to 0.
   * - settings: (optional) An array of configured settings for the display.
   *
   * Use DisplayHelper::getDisplays() to access the actual displays.
   *
   * @var array
   */
  protected $displays = [];

  /**
   * Holds the collection of displays.
   *
   * @var \Drupal\ixm_dashboard\DisplayPluginCollection
   *   The plugin collection.
   */
  protected $displayCollection;

  /**
   * Constructs a DisplayHelper.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Drupal\ixm_dashboard\DisplayManager $displayManager
   *   The display plugin manager.
   */
  public function __construct(ConfigFactoryInterface $config, DisplayManager $displayManager) {
    $this->displayManager = $displayManager;
    $this->dashboardConfig = $config->getEditable('ixm_dashboard.settings');

    $this->initializeDisplayConfiguration();
  }

  /**
   * Initializes the Display Configuration.
   *
   * Since we use all Plugins regardless if in config yet, we need to merge
   * our config with the definitions prior to creating a collection.
   */
  protected function initializeDisplayConfiguration() {
    if (empty($this->displays)) {
      $display_config = $this->dashboardConfig->get('displays');
      $definitions = $this->displayManager->getDefinitions();
      foreach ($definitions as $display_id => $definition) {

        // Ensure no missing plugins.
        if (!$definition['class'] || !class_exists($definition['class'])) {
          continue;
        }

        if (isset($display_config[$display_id])) {
          $definition = NestedArray::mergeDeep($definition, $display_config[$display_id]);
        }
        $this->displays[$display_id] = $definition;
      }
    }
  }

  /**
   * Returns the ordered collection of displays or an individual display.
   *
   * @param string $instance_id
   *   (optional) The ID of a display plugin instance to return.
   *
   * @return \Drupal\ixm_dashboard\DisplayPluginCollection|\Drupal\ixm_dashboard\DisplayInterface
   *   Either the display collection or a specific display plugin instance.
   */
  public function getDisplays($instance_id = NULL) {
    if (empty($this->displayCollection)) {
      $this->displayCollection = new DisplayPluginCollection($this->displayManager, $this->displays);
      $this->displayCollection->sort();
    }
    if (isset($instance_id)) {
      return $this->displayCollection->get($instance_id);
    }
    return $this->displayCollection;
  }

  /**
   * Gets a plugin collection for a specific status.
   *
   * @param bool $status
   *   The display status to filter by.
   * @param bool $onlyWidgets
   *   Only return plugins with widgets enabled.
   *
   * @return \Drupal\ixm_dashboard\DisplayPluginCollection
   *   The display collection.
   */
  public function getDisplaysByStatus($status = TRUE, $onlyWidgets = FALSE) {
    // Filter displayes.
    $filtered = array_filter($this->displays, function ($definition) use ($status, $onlyWidgets) {
      if ($onlyWidgets) {
        return $definition['status'] == $status && $definition['widget'];
      }
      return $definition['status'] == $status;
    });

    // @TODO: This should just filter result of getDisplays() for perf.
    $filtered_collection = new DisplayPluginCollection($this->displayManager, $filtered);
    $filtered_collection->sort();

    return $filtered_collection;
  }

  /**
   * Get the librarys from a the display collection or a single instance.
   *
   * @param int $instance_id
   *   The display instance id.
   *
   * @return array
   *   And array of libraries.
   */
  public function getAttachments($instance_id = NULL) {
    $attachments = ['library' => []];

    $displays = $this->getDisplays($instance_id);
    foreach ($displays as $display) {
      $attachments['library'] = array_merge($attachments['library'], $display->getLibraries());
    }

    if (empty($attachments['library'])) {
      return [];
    }

    return $attachments;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplayConfig($instance_id, array $configuration) {
    $this->displays[$instance_id] = $configuration;
    if (isset($this->displayCollection)) {
      $this->displayCollection->setInstanceConfiguration($instance_id, $configuration);
    }
    return $this;
  }

  /**
   * Determines whether the active route is a dashboard one.
   *
   * @param string $path
   *   The path to check against.
   *
   * @return bool
   *   Returns TRUE if the route is an dashboard one, otherwise FALSE.
   */
  public function isDashboardRoute($path = FALSE) {
    if (!$path) {
      $path = \Drupal::service('path.current')->getPath();
    }

    $alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);
    foreach ($this->displays as $id => $definition) {
      if (isset($definition['alias']) && $definition['alias'] == $alias) {
        return TRUE;
      }
    }

    return FALSE;

  }

}
