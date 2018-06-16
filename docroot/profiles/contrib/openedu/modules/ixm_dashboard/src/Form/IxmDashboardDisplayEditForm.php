<?php

namespace Drupal\ixm_dashboard\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ixm_dashboard\Utility\DisplayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a wrapping form for editing Dashboard Display settings.
 */
class IxmDashboardDisplayEditForm extends ConfigFormBase {

  /**
   * The Dashboard Display Helper.
   *
   * @var \Drupal\ixm_dashboard\Utility\DisplayHelper
   */
  protected $displayHelper;

  /**
   * Constructs a new IxmDashboardPluginForm.
   *
   * @param \Drupal\ixm_dashboard\Utility\DisplayHelper $displayHelper
   *   The Dashboard Display Manager Helper.
   */
  public function __construct(DisplayHelper $displayHelper, ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ixm_dashboard_display_edit';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ixm_dashboard.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

    /** @var \Drupal\ixm_dashboard\DisplayInterface $display */
    $display = $this->displayHelper->getDisplays($id);

    $form['display_id'] = [
      '#type' => 'value',
      '#value' => $id,
    ];

    $form['status'] = [
      '#title' => $this->t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => $display->getStatus(),
    ];

    $form['widget'] = [
      '#title' => $this->t('Show Dashboard Widget'),
      '#type' => 'checkbox',
      '#default_value' => $display->showWidget(),
      '#disabled' => empty($display->buildWidget()),
    ];

    $settings_form = $display->settingsForm([], $form_state);

    if (!empty($settings_form)) {
      $form['plugin-settings'] = [
        '#type' => 'details',
        '#title' => $this->t("Display Configuration"),
        '#open' => TRUE,
      ];

      $form['plugin-settings']['settings'] = $settings_form;
      $form['plugin-settings']['settings']['#tree'] = TRUE;
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save changes'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('ixm_dashboard.settings');
    $id = $form_state->getValue('display_id');

    // Only set specific values.
    $display = $this->displayHelper->getDisplays($id);
    $display->setConfigurationValue('status', $form_state->getValue('status'));
    $display->setConfigurationValue('widget', $form_state->getValue('widget'));
    $display->setConfigurationValue('settings', $form_state->getValue('settings'));

    // Update the collection.
    $this->displayHelper->setDisplayConfig($id, $display->getConfiguration());

    // Save to main Config.
    $config
      ->set('displays.' . $id, $display->getConfiguration())
      ->save();

    // Rebuild routes if display provided an alias.
    if (!$display->isAjax()) {
      /** \Drupal\Core\Routing\RouteBuilderInterface */
      \Drupal::service("router.builder")->setRebuildNeeded();
    }

    $form_state->setRedirect('ixm_dashboard.displays');
  }

}
