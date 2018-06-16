<?php

namespace Drupal\ixm_dashboard;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DisplayBase.
 *
 * @package Drupal\ixm_dashboard
 */
abstract class DisplayBase extends PluginBase implements DisplayInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurationValue($key, $value) {
    $this->configuration[$key] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $definition = $this->getPluginDefinition();
    return [
      'weight' => isset($definition['weight']) ? $definition['weight'] : 0,
      'status' => isset($definition['status']) ? $definition['status'] : FALSE,
      'widget' => isset($definition['widget']) ? $definition['widget'] : FALSE,
      'settings' => isset($definition['settings']) ? $definition['settings'] : [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->getPluginDefinition()['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $plugin_definition = $this->getPluginDefinition();
    return $plugin_definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $plugin_definition = $this->getPluginDefinition();
    return isset($plugin_definition['description']) ? $plugin_definition['description'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getIcon() {
    $plugin_definition = $this->getPluginDefinition();
    return isset($plugin_definition['icon']) ? $plugin_definition['icon'] : 'turned_in';
  }

  /**
   * {@inheritdoc}
   */
  public function isAjax() {
    $plugin_definition = $this->getPluginDefinition();
    return isset($plugin_definition['ajax']) ? $plugin_definition['ajax'] : TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlias() {
    $plugin_definition = $this->getPluginDefinition();
    return isset($plugin_definition['alias']) ? $plugin_definition['alias'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    return $this->configuration['status'];
  }

  /**
   * {@inheritdoc}
   */
  public function showWidget() {
    return $this->configuration['widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->configuration['weight'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->configuration['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildWidget() {
    return [];
  }

}
