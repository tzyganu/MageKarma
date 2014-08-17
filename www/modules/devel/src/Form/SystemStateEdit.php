<?php

/**
 * @file
 * Contains \Drupal\devel\Form\SystemStateEdit.
 */

namespace Drupal\devel\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form API form to edit a state.
 */
class SystemStateEdit extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'devel_state_system_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $key = '') {
    // Get the old value
    $old_value = \Drupal::state()->get($key);
    // First we will show the user the content of the variable about to be edited
    $form['old_value'] = array(
      '#type' => 'item',
      '#title' => t('Old value for %name', array('%name' => $key)),
      '#markup' => kprint_r($old_value, TRUE),
    );
    // Store in the form the name of the state variable
    $form['state_name'] = array(
      '#type' => 'hidden',
      '#value' => $key,
    );

    // Only simple structures are allowed to be edited.
    $disabled = !$this->checkObject($old_value);
    // Set the transport format for the new value. Values:
    //  - plain
    //  - yaml
    $form['transport'] = array(
      '#type' => 'hidden',
      '#value' => 'plain',
    );
    if (is_array($old_value)) {
      $dumper = new Dumper();
      // Set Yaml\Dumper's default indentation for nested nodes/collections to
      // 2 spaces for consistency with Drupal coding standards.
      $dumper->setIndentation(2);
      // The level where you switch to inline YAML is set to PHP_INT_MAX to
      // ensure this does not occur.
      $old_value = $dumper->dump($old_value, PHP_INT_MAX);
      $form['transport']['#value'] = 'yaml';
    }
    $form['new_value'] = array(
      '#type' => 'textarea',
      '#title' => t('New value'),
      '#default_value' => $disabled ? '' : $old_value,
      '#disabled' => $disabled,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => 'devel/state',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the state
    $name = $form_state['values']['state_name'];
    switch ($form_state['values']['transport']) {
      case 'yaml':
        $parser = new Parser();
        $new_value = $parser->parse($form_state['values']['new_value']);
        break;

      default:
        $new_value = $form_state['values']['new_value'];
        break;
    }
    \Drupal::state()->set($name, $new_value);
    $form_state['redirect'] = 'devel/state';
    drupal_set_message(t('Variable %var was successfully edited.', array('%var' => $name)));
  }

  /**
   * Helper function to determine if a variable is or contains an object.
   *
   * @param $data
   *   Input data to check
   *
   * @return bool
   *   TRUE if the variable is not an object and does not contain one.
   */
  protected function checkObject($data) {
    if (is_object($data)) {
      return FALSE;
    }
    if (is_array($data)) {
      // If the current object is an array, then check recursively.
      foreach ($data as $value) {
        // If there is an object the whole container is "contaminated"
        if (!$this->checkObject($value)) {
          return FALSE;
        }
      }
    }
    // All checks pass
    return TRUE;
  }

}
