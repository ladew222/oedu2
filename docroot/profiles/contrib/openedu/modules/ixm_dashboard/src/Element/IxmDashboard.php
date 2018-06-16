<?php

namespace Drupal\ixm_dashboard\Element;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Provides a render element for the default Drupal toolbar.
 *
 * @RenderElement("ixm_dashboard")
 */
class IxmDashboard extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [
        [$class, 'preRenderIxmDashboard'],
      ],
      '#theme' => 'ixm_dashboard',
      '#attached' => [
        'library' => [
          'ixm_dashboard/dashboard',
        ],
      ],
      '#attributes' => [
        'id' => 'ixm-dashboard',
        'role' => 'group',
        'aria-label' => $this->t('IXM Dashboard toolbar'),
      ],
      // Metadata for the navbar.
      '#bar' => [
        '#attributes' => [
          'id' => 'dashboard-bar',
          'role' => 'navigation',
          'aria-label' => $this->t('Dashboard items'),
        ],
      ],
    ];
  }

  /**
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   A renderable array.
   *
   * @see ixm_dashboard_page_top()
   */
  public static function preRenderIxmDashboard($element) {

    // Load up any enabled displays.
    $displays = static::displayHelper()->getDisplaysByStatus();

    $items = [];
    foreach ($displays as $id => $display) {
      /** @var \Drupal\ixm_dashboard\DisplayInterface $display */
      $items[$id] = [
        'item' => [
          '#type' => 'link',
          '#title' => Markup::create('<i class="material-icons">' . $display->getIcon() . '</i><span class="text-truncate">' . $display->label() . '</span>'),
          '#url' => $display->isAjax() ? url::fromRoute('ixm_dashboard.displayAjax', ['id' => $id]) : Url::fromUri('internal:' . $display->getAlias()),
          '#attributes' => [
            'title' => $display->label(),
            'class' => [
              $display->isAjax() ? 'ajax-display' : 'page-display',
            ],
            'id' => Html::getId('display-' . $id),
          ],
        ],
      ];
    }

    // Merge in the original values.
    $element = array_merge($element, $items);

    // Merge in libs.
    $element['#attached'] = BubbleableMetadata::mergeAttachments($element['#attached'], static::displayHelper()
      ->getAttachments());

    return $element;
  }

  /**
   * Wraps the display manager.
   *
   * @return \Drupal\ixm_dashboard\Utility\DisplayHelper
   */
  protected static function displayHelper() {
    return \Drupal::service('ixm_dashboard.display_helper');
  }

}
