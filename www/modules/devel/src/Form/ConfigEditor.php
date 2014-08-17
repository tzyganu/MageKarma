<?php

/**
 * @file
 * Contains \Drupal\devel\Form\ConfigEditor.
 */

namespace Drupal\devel\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Drupal\Core\Form\FormStateInterface;

/**
 * Edit config variable form.
 */
class ConfigEditor extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'devel_config_system_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config_name = '') {
    $data = $this->config($config_name)->get();
    if ($data === FALSE) {
      drupal_set_message(t('Config !name does not exist in the system.', array('!name' => $config_name)), 'error');
      return;
    }
    if (empty($data)) {
      drupal_set_message(t('Config !name exists but has no data.', array('!name' => $config_name)), 'warning');
      return;
    }
    $dumper = new Dumper();
    // Set Yaml\Dumper's default indentation for nested nodes/collections to
    // 2 spaces for consistency with Drupal coding standards.
    $dumper->setIndentation(2);
    // The level where you switch to inline YAML is set to PHP_INT_MAX to
    // ensure this does not occur.
    $output = $dumper->dump($data, PHP_INT_MAX);

    $form['name'] = array(
      '#type' => 'value',
      '#value' => $config_name,
    );

    $form['value'] = array(
      '#type' => 'item',
      '#title' => t('Old value for %variable', array('%variable' => $config_name)),
      '#markup' => dpr($output, TRUE),
    );

    $form['new'] = array(
      '#type' => 'textarea',
      '#title' => t('New value'),
      '#default_value' => $output,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => 'devel/config',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $parser = new Parser();
    $new_config = $parser->parse($form_state['values']['new']);
    $this->config($form_state['values']['name'])->setData($new_config)->save();

    drupal_set_message(t('Configuration variable %variable was successfully saved.', array('%variable' => $form_state['values']['name'])));

    $form_state['redirect'] = 'devel/config';
  }

}
