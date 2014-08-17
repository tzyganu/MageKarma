<?php

/**
 * @file
 * Contains \Drupal\entityform\Entity\EntityformType.
 */

namespace Drupal\entityform\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
/**
 * Defines the Entityform type configuration entity.
 *
 * @EntityType(
 *   id = "entityform_type",
 *   label = @Translation("Entityform type"),
 *   module = "entityform",
 *   controllers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "access" = "Drupal\Core\Entity\EntityAccessController",
 *     "form" = {
 *       "add" = "Drupal\entityform\EntityformTypeFormController",
 *       "edit" = "Drupal\entityform\EntityformTypeFormController",
 *     },
 *     "list" = "Drupal\entityform\EntityformTypeListController",
 *   },
 *   admin_permission = "administer entityform types",
 *   config_prefix = "entityform.type",
 *   bundle_of = "entityform_submission",
 *   entity_keys = {
 *     "id" = "type",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "admin/structure/entityform-types/manage/{entityform_type}"
 *   }
 * )
 */
class EntityformType extends ConfigEntityBase {

  /**
   * The machine name of this entityform type.
   *
   * @var string
   *
   * @todo Rename to $id.
   */
  public $type;

  /**
   * The UUID of the entityform type.
   *
   * @var string
   */
  public $uuid;

  /**
   * The human-readable name of the entityform type.
   *
   * @var string
   *
   * @todo Rename to $label.
   */
  public $label;

  /**
   * The title to use for the form.
   *
   * @var string

   */
  public $form_title = 'Title';

  /**
   * A brief description of this entityform type.
   *
   * @var string
   */
  public $description;

  /**
   * Help information shown to the user when creating a Entityform of this type.
   *
   * @var string
   */
  public $help;

  /**
   * Module-specific settings for this entityform type, keyed by module name.
   *
   * @var array
   *
   * @todo Pluginify.
   */
  public $settings = array();

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleSettings($module) {
    if (isset($this->settings[$module]) && is_array($this->settings[$module])) {
      return $this->settings[$module];
    }
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    $locked = \Drupal::state()->get('entityform.type.locked');
    return isset($locked[$this->id()]) ? $locked[$this->id()] : FALSE;
  }
  /**
   * {@inheritdoc}
   *
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    parent::postSave($storage_controller, $update);

    if (!$update) {
      // Clear the entityform type cache, so the new type appears.
      \Drupal::cache()->deleteTags(array('entityform_types' => TRUE));

      entity_invoke_bundle_hook('create', 'entityform', $this->id());

      // Unless disabled, automatically create a Body field for new entityform types.
      if ($this->get('create_body')) {
        $label = $this->get('create_body_label');
        entityform_add_body_field($this, $label);
      }
    }
    elseif ($this->getOriginalID() != $this->id()) {
      // Clear the entityform type cache to reflect the rename.
      \Drupal::cache()->deleteTags(array('entityform_types' => TRUE));

      $update_count = entityform_type_update_entityforms($this->getOriginalID(), $this->id());
      if ($update_count) {
        drupal_set_message(format_plural($update_count,
          'Changed the entityform type of 1 post from %old-type to %type.',
          'Changed the entityform type of @count posts from %old-type to %type.',
          array(
            '%old-type' => $this->getOriginalID(),
            '%type' => $this->id(),
          )));
      }
      entity_invoke_bundle_hook('rename', 'entityform', $this->getOriginalID(), $this->id());
    }
    else {
      // Invalidate the cache tag of the updated entityform type only.
      cache()->invalidateTags(array('entityform_type' => $this->id()));
    }
  }

  **
   * {@inheritdoc}
   *
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);

    // Clear the entityform type cache to reflect the removal.
    $storage_controller->resetCache(array_keys($entities));
    foreach ($entities as $entity) {
      entity_invoke_bundle_hook('delete', 'entityform', $entity->id());
    }
  }
*/
}

