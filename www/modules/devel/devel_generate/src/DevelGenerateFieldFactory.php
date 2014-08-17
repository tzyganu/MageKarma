<?php

namespace Drupal\devel_generate;

class DevelGenerateFieldFactory {

  public function createInstance($fieldType, $namespace) {
    $fieldType = ucfirst($fieldType);
    $class = 'Drupal\\' . $namespace . '\\DevelGenerateField' . $fieldType . 'Custom';

    if (!class_exists($class)) {
      $class = 'Drupal\\' . $namespace . '\\DevelGenerateField' . $fieldType;
      if (!class_exists($class)) {
        throw new DevelGenerateException(sprintf('The field type (%s) did not specify an instance class.', $fieldType));
      }
    }

    return new $class;
  }

}
