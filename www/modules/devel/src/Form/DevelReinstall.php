<?php

/**
 * @file
 * Contains \Drupal\devel\Form\DevelReinstall.
 */

namespace Drupal\devel\Form;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Display a dropdown of installed modules with the option to reinstall them.
 */
class DevelReinstall extends FormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new DevelReinstall form.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'devel_reinstall_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $modules = array_keys($this->moduleHandler->getModuleList());
    sort($modules);
    $form['list'] = array(
      '#type' => 'checkboxes',
      '#options' => array_combine($modules, $modules),
      '#description' => t('Uninstall and then install the selected modules. <code>hook_uninstall()</code> and <code>hook_install()</code> will be executed and the schema version number will be set to the most recent update number. You may have to manually clear out any existing tables first if the module doesn\'t implement <code>hook_uninstall()</code>.'),
    );
    $form['submit'] = array(
      '#value' => t('Reinstall'),
      '#type' => 'submit',
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $modules = array_filter($form_state['values']['list']);
    $this->moduleHandler->uninstall($modules, FALSE);
    $this->moduleHandler->install($modules, FALSE);
    drupal_set_message(t('Uninstalled and installed: %names.', array('%names' => implode(', ', $modules))));
  }

}
