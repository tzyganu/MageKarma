<?php

/**
 * @file
 * Contains \Drupal\devel_generate\DevelGenerateFieldBaseInterface.
 */

namespace Drupal\devel_generate;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldInstanceConfig;

/**
 * Base interface definition for generating fields for plugins functionality.
 *
 * This interface details base wrapping methods.
 * Most implementations will want to directly inherit generate()
 * from Drupal\devel_generate\DevelGenerateFieldBase.
 *
 */
interface DevelGenerateFieldBaseInterface {

  /**
   * Wrapper function for generateValues()which
   * most implementations will want to directly inherit
   * from Drupal\devel_generate\DevelGenerateFieldBase.
   *
   * @see generateFields().
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\field\Entity\FieldInstanceConfig $instance
   * @param $plugin_definition
   * @param $form_display_options
   * @return
   */
  public function generate(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options);

  /**
   * Business logic to add values to some field.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\field\Entity\FieldInstanceConfig $instance
   * @param $plugin_definition
   * @param $form_display_options
   * @return
   */
  public function generateValues(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options);

}
