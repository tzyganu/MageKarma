<?php

namespace Drupal\devel_generate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldInstanceConfig;

class DevelGenerateFieldEntity_reference extends DevelGenerateFieldBase {

  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    if ($referenceble = \Drupal::service('plugin.manager.entity_reference.selection')->getSelectionHandler($instance, $entity)->getReferenceableEntities()) {
      $group = array_rand($referenceble);
      $object_field['target_id'] = array_rand($referenceble[$group]);
    }
    return $object_field;
  }

}
