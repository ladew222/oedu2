<?php

namespace Drupal\ixm_dashboard;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * Provides a container for lazily loading Display plugins.
 */
class DisplayPluginCollection extends DefaultLazyPluginCollection {

  /**
   * Provides uasort() callback to sort plugins.
   */
  public function sortHelper($a, $b) {
    $weight_a = $this->get($a)->getWeight();
    $weight_b = $this->get($b)->getWeight();

    return $weight_a < $weight_b ? -1 : 1;

  }

}
