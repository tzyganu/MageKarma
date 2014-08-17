<?php

namespace Drupal\devel_generate;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\field\Field;

abstract class DevelGenerateFieldBase implements DevelGenerateFieldBaseInterface {

  /**
   * Implements Drupal\devel_generate\DevelGenerateFieldBaseInterface::generate().
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\field\Entity\FieldInstanceConfig $instance
   * @param $plugin_definition
   * @param $form_display_options
   * @return array
   */
  public function generate(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    if (isset($plugin_definition['multiple_values']) && $plugin_definition['multiple_values'] === TRUE) {
      return $this->generateMultiple($entity, $instance, $plugin_definition, $form_display_options);
    }
    else {
      return $this->generateValues($entity, $instance, $plugin_definition, $form_display_options);
    }

  }

  /**
   * A simple function to return multiple values for fields that use
   * custom multiple value widgets but don't need any other special multiple
   * values handling. This will call the field generation function
   * a random number of times and compile the results into a node array.
   */
  protected function generateMultiple(EntityInterface $entity, FieldInstanceConfig $instance, $plugin_definition, $form_display_options) {
    $object_field = array();
    $cardinality = $instance->getFieldStorageDefinition()->getCardinality();
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED;
        $max = rand(0, 3); //just an arbitrary number for 'unlimited'
        break;
      default:
        $max = $cardinality - 1;
        break;
    }
    for ($i = 0; $i <= $max; $i++) {
      $result = $this->generateValues($entity, $instance, $plugin_definition, $form_display_options);
      if (!empty($result)) {
        $object_field[$i] = $result;
      }
    }
    return $object_field;
  }

  /**
   * Enrich the $entity that is about to be saved with arbitrary
   * information in each of its fields.
   */
  public static function generateFields(EntityInterface &$entity, $entity_type, $bundle_name, $form_mode = 'default', $namespace = 'devel_generate') {
    $instances = entity_load_multiple_by_properties('field_instance_config', array('entity_type' => $entity_type, 'bundle' => $bundle_name));
    $field_types = \Drupal::service('plugin.manager.field.field_type')->getDefinitions();
    $skips = function_exists('drush_get_option') ? drush_get_option('skip-fields', '') : @$_REQUEST['skip-fields'];
    foreach (explode(',', $skips) as $skip) {
      unset($instances[$skip]);
    }

    foreach ($instances as $instance) {
      /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
      $field_storage = $instance->getFieldStorageDefinition();
      $cardinality = $field_storage->getCardinality();
      $field_name = $field_storage->getName();
      $object_field = array();

      // If module handles own multiples, then only call its hook once.
      $form_display_options = entity_get_form_display($entity_type, $bundle_name, $form_mode)->getComponent($field_name);
      $plugin_definition = \Drupal::service('plugin.manager.field.widget')->getDefinition($form_display_options['type']);
      if (isset($plugin_definition['multiple_values']) && $plugin_definition['multiple_values'] === TRUE) {
        $max = 0;
      }
      else {
        switch ($cardinality) {
          case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED;
            $max = rand(0, 3); //just an arbitrary number for 'unlimited'
            break;
          default:
            $max = $cardinality - 1;
            break;
        }
      }
      $field_storage_type = $field_storage->getType();
      for ($i = 0; $i <= $max; $i++) {
        $provider = $field_types[$field_storage_type]['provider'];
        // Workaround to keep numeric fields working once moved to core.
        if ($field_storage_type == 'float' || $field_storage_type == 'decimal' || $field_storage_type == 'integer') {
          $provider = 'number';
        }
        if (!in_array($provider, array('file', 'image', 'taxonomy', 'number', 'text', 'options', 'email', 'link', 'entity_reference', 'datetime'))) {
          continue;
        }
        $devel_generate_field_factory = new DevelGenerateFieldFactory();
        $devel_generate_field_object = $devel_generate_field_factory->createInstance($provider, $namespace);

        if ($devel_generate_field_object instanceof DevelGenerateFieldBaseInterface) {

          if ($result = $devel_generate_field_object->generate($entity, $instance, $plugin_definition, $form_display_options)) {

            if (isset($plugin_definition['multiple_values']) && $plugin_definition['multiple_values'] === TRUE) {
              // Fields that handle their own multiples will add their own deltas.
              $object_field = $result;
            }
            else {
              // When multiples are handled by the content module, add a delta for each result.
              $object_field[$i] = $result;
            }

          }

        }

      }

      $entity->{$field_name} = $object_field;
    }
  }

}
