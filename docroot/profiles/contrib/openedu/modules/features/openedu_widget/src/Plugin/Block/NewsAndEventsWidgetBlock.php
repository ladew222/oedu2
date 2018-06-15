<?php

namespace Drupal\openedu_widget\Plugin\Block;

use Drupal\Core\Render\Markup;
use Drupal\views\Views;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class NewsAndEventsWidgetBlock.
 *
 * @Block(
 *   id = "news_events_widget",
 *   admin_label = @Translation("News & Events Widget"),
 * )
 */
class NewsAndEventsWidgetBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme'  => '',
      '#title'  => $this->getTitle(),
      '#markup' => $this->getRenderable(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $news_categories = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('news_categories');
    $event_categories = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadTree('event_categories');

    // Default form values.
    $widget_content_type_display = $config['widget_content_type_display'];
    $widget_content_news_categories = $config['widget_content_news_categories'];
    $widget_content_event_categories = $config['widget_content_event_categories'];

    $form['widget_content_type_display'] = [
      '#type' => 'select',
      '#title' => t('Content Types'),
      '#default_value' => !empty($widget_content_type_display) ? $widget_content_type_display : '',
      '#required' => TRUE,
      '#options' => [
        'news_events' => t('News & Events'),
        'news' => t('News'),
        'events' => t('Events'),
      ],
    ];

    if (count($news_categories)) {
      $options = [];
      foreach ($news_categories as $category) {
        $options[$category->tid] = $category->name;
      }
      $form['widget_content_news_categories'] = [
        '#type' => 'select',
        '#default_value' => !empty($widget_content_news_categories) ? $widget_content_news_categories : '',
        '#multiple' => TRUE,
        '#title' => t('News Categories'),
        '#options' => $options,
      ];
    }

    if (count($event_categories)) {
      $options = [];
      foreach ($event_categories as $category) {
        $options[$category->tid] = $category->name;
      }
      $form['widget_content_event_categories'] = [
        '#type' => 'select',
        '#default_value' => !empty($widget_content_event_categories) ? $widget_content_event_categories : '',
        '#multiple' => TRUE,
        '#title' => t('Event Categories'),
        '#options' => $options,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['widget_content_type_display'] = $form_state->getValue('widget_content_type_display');
    $this->configuration['widget_content_news_categories'] = $form_state->getValue('widget_content_news_categories');
    $this->configuration['widget_content_event_categories'] = $form_state->getValue('widget_content_event_categories');
  }

  /**
   * Helper function to get the block title.
   */
  protected function getTitle() {
    // Get config values.
    $config = $this->getConfiguration();
    switch ($config['widget_content_type_display']) {

      case 'news':
        return t('News');

      case 'events':
        return t('Events');

      default:
        return t('News and Events');

    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getRenderable() {
    // Get config values.
    $config = $this->getConfiguration();
    $widget_content_type_display = $config['widget_content_type_display'];
    $widget_content_news_categories = $config['widget_content_news_categories'];
    $widget_content_event_categories = $config['widget_content_event_categories'];

    switch ($widget_content_type_display) {
      case 'events':
        $filters = ['field_events_category' => $widget_content_event_categories];
        $rendered = $this->getNewsEventsView('events_widget_block', $filters);
        break;

      case 'news':
        $filters = ['field_news_categories' => $widget_content_news_categories];
        $rendered = $this->getNewsEventsView('news_widget_block', $filters);
        break;

      default:
        $filters = [
          'field_events_category' => $widget_content_event_categories,
          'field_news_categories' => $widget_content_news_categories,
        ];
        $rendered = $this->getNewsEventsView('news_events_widget_block', $filters);
        break;
    }

    return Markup::create($rendered);
  }

  /**
   * Helper function to get the rendered News & Events view blocks.
   *
   * @param string $block_type
   *   View block type to render, defaults to news.
   * @param array $filter_values
   *   An keyword array of filter values, the key being the field name.
   *
   * @return string
   *   A rendered view.
   */
  protected function getNewsEventsView($block_type = 'news_widget_block', array $filter_values = []) {
    $view = Views::getView('news_and_events');
    $view->setDisplay($block_type);

    $filters = $view->display_handler->getOption('filters');
    foreach ($filter_values as $field_name => $values) {
      $filters[$field_name]['value'] = $values;
    }

    $view->display_handler->setOption('filters', $filters);
    $view->execute();

    $renderable = [
      '#theme' => 'openedu_news_events_widget',
      '#block_type' => $block_type,
      '#results' => $view->render(),
    ];

    return \Drupal::service('renderer')->render($renderable);
  }

}
