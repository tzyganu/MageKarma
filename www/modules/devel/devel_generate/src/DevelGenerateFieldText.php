<?php

namespace Drupal\devel_generate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldInstanceConfig;

class DevelGenerateFieldText extends DevelGenerateFieldBase {

  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    $settings = $instance->getSettings();
    if (!empty($settings['text_processing'])) {
      $formats = filter_formats();
      $format = array_rand($formats);
    }
    else {
      $format = filter_fallback_format();
    }

    if (empty($settings['max_length'])) {
      // Textarea handling
      $object_field['value'] = DevelGenerateBase::createContent($format);
      if ($form_display_options['type'] == 'text_textarea_with_summary' && !empty($settings['display_summary'])) {
        $object_field['summary'] = DevelGenerateBase::createContent($format);
      }
    }
    else {
      // Textfield handling.
      $object_field['value'] = substr(DevelGenerateBase::createGreeking(mt_rand(1, $settings['max_length'] / 6), FALSE), 0, $settings['max_length']);
    }
    $object_field['format'] = $format;
    return $object_field;
  }

}
