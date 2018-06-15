<?php

namespace Drupal\ixm_dashboard;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for DashboardDisplay plugins.
 */
interface DisplayInterface extends ConfigurablePluginInterface, PluginInspectionInterface {

  /**
   * Returns the id of this plugin.
   *
   * @return string
   *   Plugin id.
   */
  public function id();

  /**
   * Returns the label for use on the administration pages.
   *
   * @return string
   *   The administration label.
   */
  public function label();

  /**
   * Returns the plugin's description.
   *
   * @return string
   *   A string describing the plugin. Might contain HTML and should be already
   *   sanitized for output.
   */
  public function getDescription();

  /**
   * Returns the Material Design icon string for this plugin.
   *
   * @return string
   *   The icon string.
   */
  public function getIcon();

  /**
   * Returns whether the plugin uses AJAX to render or not.
   *
   * @return bool
   *   The plugin ajax property.
   */
  public function isAjax();

  /**
   * Returns the URL alias the plugin will use if not using AJAX.
   *
   * @return string
   *   A URL alias to render the plugin page.
   */
  public function getAlias();

  /**
   * Returns the status of the plugin.
   *
   * @return bool
   *   The plugin status property.
   */
  public function getStatus();

  /**
   * Returns if the plugin is to show on the dashboard.
   *
   * @return bool
   *   The plugin widget status property.
   */
  public function showWidget();

  /**
   * Returns the plugins weight property.
   *
   * @return int
   *   The plugin weight property.
   */
  public function getWeight();

  /**
   * Returns the plugins settings array.
   *
   * @return array
   *   The custom plugin settings.
   */
  public function getSettings();

  /**
   * Generates a plugin's settings form.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of this
   *   filter. The submitted form values should match $this->settings.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

  /**
   * Returns an array of libraries to include with the display.
   *
   * @return array
   *   An array of libraries..
   */
  public function getLibraries();

  /**
   * Returns the output of the plugin as a renderable array.
   *
   * @return array
   *   Returns a render array of the plugin display.
   */
  public function build();

  /**
   * Returns the output of the dasboard widget as a renderable array.
   *
   * @TODO: This should be it's own plugin system for the dashboard, but in
   * the interest of time is going to be a simple ON/OFF setting to provide
   * widgets to the dashboard.
   *
   * Since we are assuming bootstrap, we assume row->col->content structure
   * here is a simple example of rows/cols as well as just sending
   * plain output on a top level.
   *
   * $output['row1'] = [
   *   'col1' => [
   *     '#markup' => 'row1 col1',
   *     '#attributes' => [
   *       'class' => ['colmn-class'],
   *     ],
   *   ],
   *   'col2' => [
   *     '#title' => 'Title for Column2 in row 1',
   *     '#markup' => 'row1 col2',
   *   ],
   * ];
   * $output['row2'] = [
   *   'col1' => [
   *     '#markup' => 'row2 col1',
   *   ],
   *   'col2' => [
   *     '#markup' => 'row2 col2',
   *     '#title' => 'Title for Column2 in row 2',
   *     '#attributes' => [
   *       'id' => ['some-id'],
   *     ],
   *   ],
   *   'col3' => [
   *   '#markup' => 'row2 col3',
   *   ],
   * ];
   *
   * $output['other'] = [
   *   '#markup' => 'Not in a row, top level markup',
   *   '#title' => 'Some title',
   *   '#attributes' => [
   *     'class' => ['other'],
   *   ],
   * ];
   *
   * @return array
   *   Returns a render array of the plugin widget.
   */
  public function buildWidget();

}
