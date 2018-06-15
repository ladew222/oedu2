<?php

namespace Drupal\ixm_dashboard\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DashboardDisplay annotation object.
 *
 * @Annotation
 */
class IxmDashboardDisplay extends Plugin {

  /**
   * The plugin's internal ID, in machine name format.
   *
   * @var string
   */
  public $id;

  /**
   * The display label/name of the meta tag plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A longer explanation of what the plugin is for.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The string for the Material Design Icon font.
   *
   * @var string optional
   */
  public $icon;

  /**
   * Determines if the plugin is loaded via ajax or not.
   *
   * @var bool
   */
  public $ajax;

  /**
   * The URL alias to the display if not loaded by ajax.
   *
   * @var string optional
   */
  public $alias;

  /**
   * Weight of the plugin.
   *
   * @var int
   */
  public $weight;

  /**
   * Whether this plugin is enabled or disabled by default.
   *
   * @var bool (optional)
   */
  public $status;

  /**
   * Whether this plugin's widget is enabled or disabled by default.
   *
   * @var bool (optional)
   */
  public $widget;

  /**
   * The default settings for the filter.
   *
   * @var array (optional)
   */
  public $settings;

}
