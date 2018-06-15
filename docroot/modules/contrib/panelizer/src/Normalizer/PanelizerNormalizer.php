<?php
/**
 * @file
 * Contains \Drupal\panelizer\Normalizer\PanelizerNormalizer
 */
namespace Drupal\panelizer\Normalizer;

use Drupal\hal\Normalizer\FieldItemNormalizer;
use Drupal\panelizer\Plugin\Field\FieldType\PanelizerFieldType;

/**
 * Adds Panelizer info to the Rest JSON output of an entity.
 */
class PanelizerNormalizer extends FieldItemNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = PanelizerFieldType::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    // Grab default output from FieldItemNormalizer so that panelizer data will
    // be formatted correctly on both export/import.
    $values = parent::normalize($field_item, $format, $context);
    $panels_manager = $field_item->getPanelsDisplayManager();
    $panels_display_config = $field_item->get('panels_display')->getValue();
    // If our field has custom panelizer display config data.
    if (!empty($panels_display_config) && is_array($panels_display_config)) {
      $panels_display = $panels_manager->importDisplay($panels_display_config, FALSE);
    }
    
    $values['panelizer'][0]['panels_display'] = $this->serializer->normalize($panels_manager->exportDisplay($panels_display), $format, $context);
    return $values;
  }

}
