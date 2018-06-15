<?php

namespace Drupal\ixm_dashboard\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\ixm_dashboard\Utility\DisplayHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a simple table of Displays for editing.
 */
class IxmDashboardDisplayForm extends ConfigFormBase {

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
   *   The Dashboard Display Manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
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
    return 'ixm_dashboard_displays';
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
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['display-table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('Description'),
        $this->t('Alias'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('There are no items yet.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'plugin-weight',
        ],
      ],
    ];

    // Load/sort our plugins, regardless if configured.
    $displays = $this->displayHelper->getDisplays();
    foreach ($displays as $id => $display) {
      /** @var \Drupal\ixm_dashboard\DisplayInterface $display */

      // TableDrag: Mark the table row as draggable.
      $form['display-table'][$id]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['display-table'][$id]['#weight'] = $display->getWeight();

      $form['display-table'][$id]['label'] = [
        '#plain_text' => $display->label(),
      ];

      $form['display-table'][$id]['description'] = [
        '#plain_text' => $display->getDescription(),
      ];

      $form['display-table'][$id]['alias'] = [
        '#markup' => $display->isAjax() ? $this->t('Not applicable') : Link::fromTextAndUrl($display->getAlias(), Url::fromUri('internal:' . $display->getAlias()))
          ->toString(),
      ];

      $form['display-table'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $display->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $display->getWeight(),
        '#attributes' => ['class' => ['plugin-weight']],
      ];

      // Operations (dropbutton) column.
      $form['display-table'][$id]['operations'] = [
        '#type' => 'operations',
        '#links' => [],
      ];

      // Enable/disable
      $form['display-table'][$id]['operations']['#links']['status'] = [
        'title' => $display->getStatus() ? $this->t('Disable') : $this->t('Enable'),
        'url' => Url::fromRoute('ixm_dashboard.display.toggle', ['id' => $id]),
      ];

      // Display Settings
      $form['display-table'][$id]['operations']['#links']['settings'] = [
        'title' => $this->t('Configure'),
        'url' => Url::fromRoute('ixm_dashboard.plugin.edit', ['id' => $id]),
      ];

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
    $config = $this->config('ixm_dashboard.settings');
    $values = $form_state->getValue('display-table');

    foreach ($values as $id => $item) {
      /** @var \Drupal\ixm_dashboard\DisplayInterface $display */
      $display = $this->displayHelper->getDisplays($id);
      $display->setConfigurationValue('weight', $item['weight']);

      // Update the collection.
      $this->displayHelper->setDisplayConfig($id, $display->getConfiguration());

      // Save to base config.
      $config
        ->set('displays.' . $id, $display->getConfiguration())
        ->save();
    }

    drupal_set_message($this->t('Settings have been saved.'));
  }

}
