<?php

namespace Drupal\ixm_dashboard_user\Plugin\ixm_dashboard\Display;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ixm_dashboard\DisplayBase;
use Drupal\views\Views;

/**
 * Provides a IXM dashboard display/widget for user information.
 *
 * @IxmDashboardDisplay(
 *   id = "user",
 *   label = @Translation("User"),
 *   description = @Translation("Shows basic information about site users."),
 *   settings={
 *    "show_online"=TRUE,
 *    "show_new"=TRUE,
 *   },
 *   icon="person"
 * )
 */
class User extends DisplayBase {

  use StringTranslationTrait;

  /**
   * The show_online setting.
   *
   * @var bool
   */
  protected $showOnline;

  /**
   * The show_new setting.
   *
   * @var bool
   */
  protected $showNew;

  /**
   * User constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $settings = $this->getSettings();
    $this->showOnline = $settings['show_online'];
    $this->showNew = $settings['show_new'];
  }

  /**
   * Simple helper to build views for rendering.
   *
   * @return array
   *   A renderable array.
   */
  protected function processViews() {
    $views = [];

    if ($this->showOnline) {
      $view = Views::getView('who_s_online');
      $view->setDisplay('who_s_online_block');

      $views['online'] = [
        '#title' => $view->getTitle(),
        'content' => $view->render(),
      ];
    }

    if ($this->showNew) {
      $view = Views::getView('who_s_new');
      $view->setDisplay('block_1');

      $views['new'] = [
        '#title' => $view->getTitle(),
        'content' => $view->render(),
      ];
    }

    return $views;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Just theme as widgets.
    $output = ['row' => $this->processViews()];
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function buildWidget() {
    return ['row' => $this->processViews()];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form['show_online'] = [
      '#title' => $this->t("Show Who's Online block"),
      '#type' => 'checkbox',
      '#default_value' => $this->showOnline,
    ];

    $form['show_new'] = [
      '#title' => $this->t("Show Who's New block"),
      '#type' => 'checkbox',
      '#default_value' => $this->showNew,
    ];

    return $form;
  }

}
