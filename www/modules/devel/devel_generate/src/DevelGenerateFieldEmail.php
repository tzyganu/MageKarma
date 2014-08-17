<?php

namespace Drupal\devel_generate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldInstanceConfig;

class DevelGenerateFieldEmail extends DevelGenerateFieldBase {

  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();

    // Set of possible top-level domains.
    $tlds = array('com', 'net', 'gov', 'org', 'edu', 'biz', 'info');

    // Set random lengths for the user and domain as the email field doesn't have
    // any setting for length.
    $user_length = mt_rand(5, 10);
    $domain_length = mt_rand(7, 15);

    $object_field['value'] = DevelGenerateBase::generateWord($user_length) . '@' . DevelGenerateBase::generateWord($domain_length) . '.' . $tlds[mt_rand(0, (sizeof($tlds)-1))];
    return $object_field;
  }

}
