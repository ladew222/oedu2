<?php

namespace Drupal\ixm_dashboard\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ixm_dashboard\DisplayManager;
use Drupal\ixm_dashboard\Utility\DisplayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines a dynamic path based off of the enabled plugins.
 */
class IxmDashboardRoutes implements ContainerInjectionInterface {

  /**
   * The Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Dashboard Display Helper.
   *
   * @var \Drupal\ixm_dashboard\Utility\DisplayHelper
   */
  protected $displayHelper;

  /**
   * Class constructor.
   *
   * @param \Drupal\ixm_dashboard\Utility\DisplayHelper $displayHelper
   *   The display plugin manager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface service.
   */
  public function __construct(DisplayHelper $displayHelper, ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('ixm_dashboard.settings');
    $this->displayHelper = $displayHelper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ixm_dashboard.display_helper'),
      $container->get('config.factory')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    $displays = $this->displayHelper->getDisplaysByStatus();

    $routes = [];
    foreach ($displays as $id => $display) {
      /** @var \Drupal\ixm_dashboard\DisplayInterface $display */

      if (!$display->isAjax()) {
        $display_route = !empty($display->getAlias()) ? $display->getAlias() : '/ixm/display/' . $id;

        $routes[$id] = new Route(
          $display_route,
          [
            '_controller' => '\Drupal\ixm_dashboard\Controller\IxmDashboardController::displayPage',
            '_title' => $display->label()->render(),
          ],
          ['_permission' => 'access ixm dashboard']
        );
      }

    }
    return $routes;
  }

}
