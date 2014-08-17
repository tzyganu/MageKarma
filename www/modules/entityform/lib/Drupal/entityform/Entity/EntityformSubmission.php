<?php

/**
 * @file
 * Definition of Drupal\entityform\Entity\EntityformSubmission.
 */

namespace Drupal\entityform\Entity;

use Drupal\Core\Entity\EntityformEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\ContentEntityBase;

/**
 * Defines the entityform entity class.
 *
 *
 * @EntityType(
 *   id = "entityform_submission",
 *   label = @Translation("Entityform Submission"),
 *   bundle_label = @Translation("Entityform type"),
 *   module = "entityform",
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\FieldableDatabaseStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\Core\Entity\EntityAccessController",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\Form\EntityFormController",
 *       "delete" = "Drupal\Core\Entity\Form\Entity\DeleteForm",
 *       "edit" = "Drupal\Core\Entity\Form\EntityFormController"
 *     },
 *   },
 *   base_table = "entityform_submission",
 *   data_table = "entityform_submission_field_data",
 *   revision_table = "entityform_submission_revision",
 *   revision_data_table = "entityform_submission_field_revision",
 *   uri_callback = "entityform_submission_uri",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   render_cache = FALSE,
 *   entity_keys = {
 *     "id" = "eid",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "uuid" = "uuid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "type"
 *   },
 *   route_base_path = "admin/structure/entityform-types/manage/{bundle}",
 *   permission_granularity = "bundle",
 *   links = {
 *     "canonical" = "/entityform-submission/{entityform_submission}",
 *     "edit-form" = "/entityform-submission/{entityform_submission}/edit",
 *     "version-history" = "/entityform-submission/{entityform_submission}/revisions"
 *   }
 * )
 */
class EntityformSubmission extends ContentEntityBase {

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('nid')->value;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::getRevisionId().
   */
  public function getRevisionId() {
    return $this->get('vid')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation = 'view', AccountInterface $account = NULL) {
    if ($operation == 'create') {
      return parent::access($operation, $account);
    }

    return \Drupal::entityManager()
      ->getAccessController($this->entityType)
      ->access($this, $operation, $this->prepareLangcode(), $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }


  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthor() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getAuthorId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setAuthorId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->set('revision_uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions($entity_type) {
    $properties['eid'] = array(
      'label' => t('Entityform Submission ID'),
      'description' => t('The entityform submission ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The Entityform Submission UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['vid'] = array(
      'label' => t('Revision ID'),
      'description' => t('The Entityform Submission revision ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['type'] = array(
      'label' => t('Type'),
      'description' => t('The Entityform Submission type.'),
      'type' => 'string_field',
      'read-only' => TRUE,
    );
    $properties['langcode'] = array(
      'label' => t('Language code'),
      'description' => t('The Entityform Submission language code.'),
      'type' => 'language_field',
    );
    $properties['uid'] = array(
      'label' => t('User ID'),
      'description' => t('The user ID of the Entityform Submission author.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the Entityform Submission was created.'),
      'type' => 'integer_field',
    );
    $properties['changed'] = array(
      'label' => t('Changed'),
      'description' => t('The time that the Entityform Submission was last edited.'),
      'type' => 'integer_field',
      'property_constraints' => array(
        'value' => array('EntityChanged' => array()),
      ),
    );

    $properties['revision_timestamp'] = array(
      'label' => t('Revision timestamp'),
      'description' => t('The time that the current revision was created.'),
      'type' => 'integer_field',
      'queryable' => FALSE,
    );
    $properties['revision_uid'] = array(
      'label' => t('Revision user ID'),
      'description' => t('The user ID of the author of the current revision.'),
      'type' => 'entity_reference_field',
      'settings' => array('target_type' => 'user'),
      'queryable' => FALSE,
    );
    $properties['log'] = array(
      'label' => t('Log'),
      'description' => t('The log entry explaining the changes in this version.'),
      'type' => 'string_field',
    );
    return $properties;
  }

}
