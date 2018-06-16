<?php

namespace Drupal\query_filter_cards;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Component\Utility\Xss;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;

/**
 * Class QueryFilterCards.
 *
 * @package Drupal\query_filter_cards
 */
class QueryFilterCards {

  /**
   * Returns a filter cards Drupal render array.
   *
   * @param object $view
   *   An object reference to the view.
   *
   * @return array
   *   A Drupal renderable array.
   */
  public function render($view = NULL) {
    $cards = $this->generateCards($view);
    return [
      '#prefix' => !empty($cards) ? '<div class="query-filter-cards">' : '',
      '#markup' => $this->generateCards($view),
      '#suffix' => !empty($cards) ? '</div>' : '',
      '#attached' => [
        'library' => [
          'query_filter_cards/filter-cards',
        ],
      ],
    ];
  }

  /**
   * Generates the cards based off of the url query.
   *
   * Can take an optional view to determine text based off of whether search
   * is an nid or tid.
   *
   * @param object $view
   *   An object reference to the view.
   *
   * @return string
   *   A string of rendered html.
   */
  protected function generateCards($view = NULL) {
    $cards = '';
    $view_query = (!empty($view) && method_exists($view, 'getQuery')) ? $view->getQuery() : [];
    if (method_exists($view_query, 'getSearchApiQuery')) {
      $cards = $this->searchApiGenerate($view_query);
    }
    elseif (isset($view_query->where)) {
      $cards = $this->regularSearchGenerate($view_query->where);
    }
    return $cards;
  }

  /**
   * Checks the Search API Where query, to check entity type parameter context.
   *
   * @param \Drupal\search_api\Plugin\views\query\SearchApiQuery $view_query
   *   A view query object.
   * @param string $param
   *   A parameter name from the uri.
   *
   * @return mixed
   *   Returns an entity type string or FALSE.
   */
  public function checkBundleType(SearchApiQuery $view_query, $param) {
    $bundle = '';
    $match = FALSE;
    foreach ($view_query->getWhere() as $idx => $where) {
      if ($match && $bundle) {
        break;
      }
      foreach ($where['conditions'] as $condition) {
        if (!empty($condition[0])) {
          if ($condition[0] == 'type') {
            $bundle = key($condition[1]);
            $match = TRUE;
          }
          elseif ($condition[0] == $param) {
            $match = TRUE;
          }
        }
        if ($match && $bundle) {
          break;
        }
      }
    }

    // @todo check taxonomy or other possible entity types here in future.

    if ($match) {
      // Check to see if the bundle type is a Node.
      $node_bundle_info = \Drupal::service("entity_type.bundle.info")
        ->getBundleInfo('node');
      if (isset($node_bundle_info[$bundle])) {
        return 'node';
      }
      // Check to see if the bundle type is a Taxonomy Term.
      $taxonomy_bundle_info = \Drupal::service("entity_type.bundle.info")
        ->getBundleInfo('taxonomy_term');
      if (isset($taxonomy_bundle_info[$bundle])) {
        return 'taxonomy_term';
      }
    }

    return $match;
  }

  /**
   * Check to see what type of field is identitfied by a bundle and field name.
   *
   * @param string $bundle
   *   A bundle string.
   * @param string $param
   *   A field machine name.
   *
   * @return array
   *   An array specifying the type and target of the field.
   */
  protected function checkFieldType($bundle, $param) {
    $type_info = [];
    if ($bundle && $param) {
      $field_info = FieldStorageConfig::loadByName($bundle, $param);
      if (!empty($field_info)) {
        $settings = $field_info->get('settings');
        $type_info['type'] = $field_info->get('type');
        $type_info['target'] = isset($settings['target_type']) ? $settings['target_type'] : '';
      }
    }
    return $type_info;
  }

  /**
   * Handles Search API view filter card generation.
   *
   * @param \Drupal\search_api\Plugin\views\query\SearchApiQuery $view_query
   *   A view query object.
   *
   * @return string
   *   A rendered html string.
   */
  protected function searchApiGenerate(SearchApiQuery $view_query) {
    $html = '';
    $query = $view_query->view->exposed_raw_input;
    $current_uri = \Drupal::request()->getRequestUri();
    $relative_uri = '';
    $is_ajax = FALSE;

    // If the uri is an AJAX uri, everything becomes relative.
    if (strpos($current_uri, '/views/ajax') !== FALSE) {
      $relative_uri = '?';
      foreach ($view_query->view->exposed_raw_input as $field => $val) {
        $relative_uri .= $field . '=' . $val . '&';
      }
      $is_ajax = TRUE;
    }

    foreach ($query as $param => $item) {
      // Skip any filters that do not belong to this view.
      $ignore_params = ['items_per_page', 'page'];
      if (!in_array($item, $view_query->view->exposed_raw_input) || in_array($param, $ignore_params)) {
        continue;
      }

      $is_date_pattern = '/^[0-9]+\-[0-9]+\-[0-9]+ [0-9]+\:[0-9]+\:[0-9]+$/';
      $link_title = $item;
      if (preg_match($is_date_pattern, $item)) {
        $date = date_create($item);
        $link_title = date_format($date, 'M j, Y');
      }
      $type = $this->checkBundleType($view_query, $param);

      // If the param is actually an identifier, we need to get the field id.
      $field_id = $param;
      if (isset($view_query->displayHandler->options['filters'])) {
        foreach ($view_query->displayHandler->options['filters'] as $filter) {
          if (!empty($filter['expose']['identifier']) && $filter['expose']['identifier'] == $field_id) {
            $field_id = $filter['id'];
          }
        }
      }
      $type_info = $this->checkFieldType($type, $field_id);

      // If the parameter points to a node, get the title from the node.
      if ($type == 'node') {
        if (!empty($type_info) && $type_info['type'] == 'entity_reference' && $type_info['target'] == 'taxonomy_term') {
          // Skip if we're using a taxonomy dropdown and have all selected.
          if ($link_title == t('All')) {
            continue;
          }
          if ($term = Term::load($item)) {
            $link_title = $term->get('name')->value;
          }
        }
        elseif ($node = Node::load($item)) {
          $link_title = $node->getTitle();
        }
      }

      if (!empty($link_title)) {
        if (!$is_ajax) {
          $context_uri = str_replace($param . '=' . $item, '', $current_uri);
          $html .= '<div class="card">' . XSS::filter($link_title) . Link::fromTextAndUrl('x', Url::fromUri('internal:' . $context_uri, []))
            ->toString() . '</div>';
        }
        else {
          $context_uri = str_replace($param . '=' . $item, '', $relative_uri);
          $html .= '<div class="card">' . $link_title . '<a href="' . $context_uri . '">x</a></div>';
        }
      }
    }

    if (!empty($html)) {
      $path = Url::fromRoute('<current>')->toString();
      $link_title = new TranslatableMarkup('Clear All', []);
      if (!$is_ajax) {
        $html .= '<div class="card clear">' . Link::fromTextAndUrl($link_title, Url::fromUri('internal:' . $path, []))
          ->toString() . '</div>';
      }
      else {
        $html .= '<div class="card clear"><a href="?">Clear All</a></div>';
      }
    }
    return $html;
  }

  /**
   * Handles regular view filter card generation.
   *
   * @param \Drupal\search_api\Plugin\views\query\SearchApiQuery $view_query
   *   A view query object.
   *
   * @return string
   *   A rendered html string.
   */
  protected function regularSearchGenerate(SearchApiQuery $view_query) {
    $html = '';
    $query = $view_query->view->exposed_raw_input;
    $current_uri = \Drupal::request()->getRequestUri();
    foreach ($query as $param => $item) {
      if (!empty($item)) {
        $is_date_pattern = '/^[0-9]+\-[0-9]+\-[0-9]+ [0-9]+\:[0-9]+\:[0-9]+$/';
        $link_title = $item;
        if (preg_match($is_date_pattern, $item)) {
          $date = date_create($item);
          $link_title = date_format($date, 'M j, Y');
        }
        $found = FALSE;
        foreach ($view_query as $where) {
          foreach ($where['conditions'] as $condition) {
            if (strpos($condition['field'], $param) !== FALSE) {
              if (preg_match('/^node__/', $condition['field']) && $item == $condition['value']) {
                // If the value is an integer and a node reference field,
                // get the title of the node as the link text.
                if ($node = Node::load($item)) {
                  $link_title = $node->getTitle();
                }
                $found = TRUE;
                break;
              }
            }
          }
          if ($found) {
            break;
          }
        }
        $context_uri = str_replace($param . '=' . $item, '', $current_uri);
        $html .= '<div class="card">' . XSS::filter($link_title) . Link::fromTextAndUrl('x', Url::fromUri('internal:' . $context_uri, []))
          ->toString() . '</div>';
      }
    }
    if (!empty($html)) {
      $path = Url::fromRoute('<current>')->toString();
      $link_title = new TranslatableMarkup('Clear All', []);
      $html .= '<div class="card clear">' . Link::fromTextAndUrl($link_title, Url::fromUri('internal:' . $path, []))
        ->toString() . '</div>';
    }
    return $html;
  }

}
