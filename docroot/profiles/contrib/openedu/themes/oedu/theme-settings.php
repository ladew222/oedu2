<?php

/**
 * @file
 * theme-settings.php
 *
 * Provides theme settings for the OpenEDU sub-theme.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function oedu_form_system_theme_settings_alter(&$form, FormStateInterface $form_state, $form_id = NULL) {

  /*
   * General "alters" use a form id. Settings should not be set here. The only
   * thing useful about this is if you need to alter the form for the running
   * theme and *not* the theme setting.
   * @see http://drupal.org/node/943212
   */
  if (isset($form_id)) {
    return;
  }

  // Change collapsible fieldsets (now details) to default #open => FALSE.
  $form['theme_settings']['#open'] = FALSE;
  $form['logo']['#open'] = FALSE;
  $form['favicon']['#open'] = FALSE;

  // Vertical tabs.
  $form['oedu_settings'] = [
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('OpenEDU Settings') . '</small></h2>',
    '#weight' => -10,
  ];

  // Navbar.
  $form['navbar'] = [
    '#type' => 'details',
    '#title' => t('Navbar'),
    '#group' => 'oedu_settings',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];

  $form['navbar']['navbar_toggle'] = [
    '#type' => 'select',
    '#title' => t('Navbar toggle size'),
    '#description' => t('Select size for navbar to collapse.'),
    '#default_value' => theme_get_setting('navbar_toggle'),
    '#options' => [
      'xl' => t('Extra Large'),
      'lg' => t('Large'),
      'md' => t('Medium'),
      'sm' => t('Small'),
    ],
  ];

  $form['navbar']['navbar_position'] = [
    '#type' => 'select',
    '#title' => t('Position'),
    '#description' => t('Select your Navbar position. <br/><strong>Note: Sticky Top uses position: sticky, which isn’t fully supported in every browser.</strong>'),
    '#default_value' => theme_get_setting('navbar_position'),
    '#options' => [
      'sticky-top' => t('Sticky Top'),
      'fixed-top' => t('Fixed Top'),
      'fixed-bottom' => t('Fixed Bottom'),
    ],
    '#empty_option' => t('Normal'),
  ];

  $form['navbar']['navbar_colour'] = [
    '#type' => 'select',
    '#title' => t('Colour'),
    '#description' => t('Select your Navbar colour.'),
    '#default_value' => theme_get_setting('navbar_colour'),
    '#options' => [
      'light' => t('Light'),
      'dark' => t('Dark'),
    ],
    '#empty_option' => t('Transparent'),
  ];

}
