<?php

/**
 * @file
 * Lists available colors and color schemes for the OpenEdu theme.
 */

$info = [
  // Available colors and color labels used in theme.
  'fields' => [
    'primary' => t('Primary color'),
    'secondary' => t('Secondary color'),
    'tertiary' => t('Tertiary color'),
    'link' => t('Link color'),
    'text' => t('Text color'),
    'button' => t('Button color'),
    'buttonlink' => t('Button link color'),
  ],
  // Pre-defined color schemes.
  'schemes' => [
    // If changed colors for default scheme please
    // add same changes to oedu/css/colors.css file.
    'default' => [
      'title' => t('Blue sky (default)'),
      'colors' => [
        'primary' => '#024b77',
        'secondary' => '#27658b',
        'tertiary' => '#f7f7f7',
        'link' => '#777',
        'text' => '#4a4a4a',
        'button' => '#fefefe',
        'buttonlink' => '#ea772e',
      ],
    ],
  ],

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => [
    'css/colors.css',
  ],

  // Copy the logo file from the theme. Color module forces this behavior.
  'copy' => [
    'logo.svg',
  ],

  // Preview files.
  'preview_library' => 'oedu/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'openedu'),
      ],
    ],
  ],
];
