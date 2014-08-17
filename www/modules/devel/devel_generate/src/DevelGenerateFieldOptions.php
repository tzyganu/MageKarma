<?php

namespace Drupal\devel_generate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldInstanceConfig;

class DevelGenerateFieldOptions extends DevelGenerateFieldBase {

  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    $field_name = $instance->getFieldStorageDefinition()->getName();
    $definition = $entity->getFieldDefinition($field_name);
    if ($allowed_values = options_allowed_values($definition, $entity)) {

      $keys = array_keys($allowed_values);
      $object_field['value'] = $keys[mt_rand(0, count($allowed_values) - 1)];
    }
    return $object_field;
  }

}
