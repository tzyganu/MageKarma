<?php

namespace Drupal\devel_generate;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\Core\Entity\EntityInterface;

class DevelGenerateFieldDatetime extends DevelGenerateFieldBase {

  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    $type = $instance->getSetting('datetime_type');

    // Just pick a date in the past year. No guidance is provided by this Field type.
    $timestamp = time()-mt_rand(0, 86400*365);
    if ($type == DateTimeItem::DATETIME_TYPE_DATE) {
     $value = gmdate(DATETIME_DATE_STORAGE_FORMAT, $timestamp);
    }
    else {
      $value = gmdate(DATETIME_DATETIME_STORAGE_FORMAT, $timestamp);
    }

    $object_field['value'] = $value;
    return $object_field;
  }

}
