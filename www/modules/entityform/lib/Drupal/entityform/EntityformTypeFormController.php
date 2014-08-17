<?php

/**
 * @file
 * Contains \Drupal\entityform\EntityformTypeFormController.
 */

namespace Drupal\entityform;

use Drupal\Core\Entity\EntityFormController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for entityform type forms.
 */
class EntityformTypeFormController extends EntityFormController {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    \Drupal::request()->attributes->geti;

    $type = $this->entity;
    if ($this->operation == 'add') {
      drupal_set_title(t('Add entityform type'));
    }
    elseif ($this->operation == 'edit') {
      drupal_set_title(t('Edit %label entityform type', array('%label' => $type->label())), PASS_THROUGH);
    }

    $entityform_type_settings = $type->getModuleSettings('entityform');
    // Ensure default settings.

    $form['label'] = array(
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#default_value' => $type->label,
      '#description' => t('The human-readable name of this entityform type. This text will be displayed as part of the list on the <em>Add new entityform</em> page. It is recommended that this name begin with a capital letter and contain only letters, numbers, and spaces. This name must be unique.'),
      '#required' => TRUE,
      '#size' => 30,
    );

    $form['type'] = array(
      '#type' => 'machine_name',
      '#default_value' => $type->id(),
      '#maxlength' => 32,
      '#disabled' => $type->isLocked(),
      '#machine_name' => array(
        //'exists' => 'node_type_load',
        'source' => array('label'),
      ),
      '#description' => t('A unique machine-readable name for this entityform type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the %node-add page, in which underscores will be converted into hyphens.', array(
        '%node-add' => t('Add new entityform type'),
      )),
    );

    $form['description'] = array(
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $type->description,
      '#description' => t('Describe this entityform type. The text will be displayed on the <em>Add new entityform type</em> page.'),
    );

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs',
    );

    $form['submission'] = array(
      '#type' => 'details',
      '#title' => t('Submission form settings'),
      '#group' => 'additional_settings',
    );
    $form['submission']['form_title'] = array(
      '#title' => t('Title field label'),
      '#type' => 'textfield',
      '#default_value' => $type->form_title,
      '#required' => TRUE,
    );

    $form['submission']['help']  = array(
      '#type' => 'textarea',
      '#title' => t('Explanation or submission guidelines'),
      '#default_value' => $type->help,
      '#description' => t('This text will be displayed at the top of the page when creating or editing entityform type of this type.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save entityform type');
    $actions['delete']['#value'] = t('Delete entityform type');
    $actions['delete']['#access'] = $this->entity->access('delete');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);

    $id = trim($form_state['values']['type']);
    // '0' is invalid, since elsewhere we check it using empty().
    if ($id == '0') {
      form_set_error('type', t("Invalid machine-readable name. Enter a name other than %invalid.", array('%invalid' => $id)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $type = $this->entity;
    $type->type = trim($type->id());
    $type->name = trim($type->name);


    $variables = $form_state['values'];

    // Do not save settings from vertical tabs.
    // @todo Fix vertical_tabs.
    unset($variables['additional_settings__active_tab']);

    // @todo Remove the entire following code after converting node settings of
    //   Comment and Menu module. https://drupal.org/node/2026165
    // Remove all node type entity properties.
    foreach (get_class_vars(get_class($type)) as $key => $value) {
      unset($variables[$key]);
    }
    // Save or reset persistent variable values.
    foreach ($variables as $key => $value) {
      $variable_new = $key . '_' . $type->id();
      $variable_old = $key . '_' . $type->getOriginalID();
      if (is_array($value)) {
        $value = array_keys(array_filter($value));
      }
      variable_set($variable_new, $value);
      if ($variable_new != $variable_old) {
        variable_del($variable_old);
      }
    }
    // Saving the entityform type after saving the variables allows modules to act
    // on those variables via hook_node_type_insert().
    $status = $type->save();

    $t_args = array('%name' => $type->label());

    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('The entityform type %name has been updated.', $t_args));
    }
    elseif ($status == SAVED_NEW) {
      drupal_set_message(t('The entityform type %name has been added.', $t_args));
      watchdog('node', 'Added entityform type %name.', $t_args, WATCHDOG_NOTICE, l(t('view'), 'admin/structure/types'));
    }

    $form_state['redirect'] = 'admin/structure/entityform-types';
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $form_state['redirect'] = 'admin/structure/entityform-types/manage/' . $this->entity->id() . '/delete';
  }

}
