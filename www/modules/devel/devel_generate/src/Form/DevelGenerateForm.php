<?php

/**
 * @file
 * Contains \Drupal\devel_generate\Form\GenerateForm.
 */

namespace Drupal\devel_generate\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\devel_generate\DevelGenerateException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that allows privileged users to generate entities.
 */
class DevelGenerateForm extends FormBase {

  /**
   * Constructs a new DevelGenerateForm object.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $devel_generate_manager
   *   The manager to be used for instantiating plugins.
   */
  public function __construct(PluginManagerInterface $devel_generate_manager) {
    $this->DevelGenerateManager = $devel_generate_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.develgenerate')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'devel_generate_form_' . $this->getPluginIdFromRequest();
  }

  /**
   * Returns the value of the param _plugin_id
   * for the current request.
   *
   * @see \Drupal\devel_generate\Routing\DevelGenerateRouteSubscriber
   */
  protected function getPluginIdFromRequest() {
    $request = $this->getRequest();
    return $request->get('_plugin_id');
  }

  /**
   * Returns a DevelGenerate plugin instance for a given plugin id.
   *
   * @param string $plugin_id
   *
   * @return \Drupal\devel_generate\DevelGenerateBaseInterface
   *   A DevelGenerate plugin instance.
   */
  public function getPluginInstance($plugin_id) {
    $instance = $this->DevelGenerateManager->createInstance($plugin_id, array());
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $plugin_id = $this->getPluginIdFromRequest();
    $instance = $this->getPluginInstance($plugin_id);
    $form = $instance->settingsForm($form, $form_state);
    $form_state['instance'] = $instance;
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Generate'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $values = $form_state['values'];
      $instance = $form_state['instance'];
      $instance->generate($values);
    }
    catch (DevelGenerateException $e) {
      watchdog('DevelGenerate', 'Failed to generate elements due to "%error".', array('%error' => $e->getMessage()), WATCHDOG_WARNING);
      drupal_set_message($this->t('Failed to generate elements due to "%error".', array('%error' => $e->getMessage())));
    }
  }

}
