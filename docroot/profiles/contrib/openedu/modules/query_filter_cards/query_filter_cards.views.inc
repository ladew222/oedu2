<?php

/**
 * @file
 * Query Filter Cards View.
 */

/**
 * Implements hook_views_data_alter().
 */
function query_filter_cards_views_data_alter(array &$data) {
  $data['views']['query_filter_cards'] = [
    'title' => t('Query Filter Cards'),
    'help' => t('Breaks out URL query into card display.'),
    'area' => [
      'id' => 'query_filter_cards',
    ],
  ];
}
