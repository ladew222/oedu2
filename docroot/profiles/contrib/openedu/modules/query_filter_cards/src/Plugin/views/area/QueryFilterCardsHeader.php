<?php

namespace Drupal\query_filter_cards\Plugin\views\area;

use Drupal\query_filter_cards\QueryFilterCards;
use Drupal\views\Plugin\views\area\TokenizeAreaPluginBase;

/**
 * Views area Query Filter Cards Header area.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("query_filter_cards")
 */
class QueryFilterCardsHeader extends TokenizeAreaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Override defaults to from parent.
    $options['tokenize']['default'] = TRUE;
    $options['empty']['default'] = TRUE;
    // Provide our own defaults.
    $options['content'] = ['default' => ''];
    $options['pager_embed'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $filter_cards = new QueryFilterCards();
    return $filter_cards->render($this->view);
  }

  /**
   * Render a text area with \Drupal\Component\Utility\Xss::filterAdmin().
   */
  public function renderTextField($value) {
    if ($value) {
      return $this->sanitizeValue($this->tokenizeValue($value), 'xss_admin');
    }
    return '';
  }

}
