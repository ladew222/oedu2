<?php

namespace Drupal\ixm_dashboard;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class DisplayManager.
 *
 * @package Drupal\ixm_dashboard
 */
class DisplayManager extends DefaultPluginManager {

  /**
   * DisplayManager constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ixm_dashboard/Display', $namespaces, $module_handler, 'Drupal\ixm_dashboard\DisplayInterface', 'Drupal\ixm_dashboard\Annotation\IxmDashboardDisplay');
    $this->alterInfo('ixm_dashboard_display_info');
    $this->setCacheBackend($cache_backend, 'ixm_dashboard_display');

    // Default config values.
    $this->defaults = [
      'weight' => 0,
      'status' => FALSE,
      'widget' => FALSE,
      'settings' => [],
    ];
  }

}
