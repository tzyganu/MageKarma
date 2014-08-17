<?php

/**
 * @file
 * Contains \Drupal\devel_generate\Plugin\DevelGenerate\UserDevelGenerate.
 */

namespace Drupal\devel_generate\Plugin\DevelGenerate;

use Drupal\devel_generate\DevelGenerateBase;
use Drupal\devel_generate\DevelGenerateFieldBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a UserDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "user",
 *   label = @Translation("users"),
 *   description = @Translation("Generate a given number of users. Optionally delete current users."),
 *   url = "user",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *     "pass" = ""
 *   }
 * )
 */
class UserDevelGenerate extends DevelGenerateBase {

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['num'] = array(
      '#type' => 'textfield',
      '#title' => t('How many users would you like to generate?'),
      '#default_value' => $this->getSetting('num'),
      '#size' => 10,
    );

    $form['kill'] = array(
      '#type' => 'checkbox',
      '#title' => t('Delete all users (except user id 1) before generating new users.'),
      '#default_value' => $this->getSetting('kill'),
    );

    $options = user_role_names(TRUE);
    unset($options[DRUPAL_AUTHENTICATED_RID]);
    $form['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Which roles should the users receive?'),
      '#description' => t('Users always receive the <em>authenticated user</em> role.'),
      '#options' => $options,
    );

    $form['pass'] = array(
      '#type' => 'textfield',
      '#title' => t('Password to be set'),
      '#default_value' => $this->getSetting('pass'),
      '#size' => 32,
      '#description' => t('Leave this field empty if you do not need to set a password'),
    );

    $options = array(1 => t('Now'));
    foreach (array(3600, 86400, 604800, 2592000, 31536000) as $interval) {
      $options[$interval] = \Drupal::service('date.formatter')->formatInterval($interval, 1) . ' ' . t('ago');
    }
    $form['time_range'] = array(
      '#type' => 'select',
      '#title' => t('How old should user accounts be?'),
      '#description' => t('User ages will be distributed randomly from the current time, back to the selected time.'),
      '#options' => $options,
      '#default_value' => 604800,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function generateElements(array $values) {
    $num = $values['num'];
    $kill = $values['kill'];
    $pass = $values['pass'];
    $age = $values['time_range'];
    $roles = $values['roles'];
    $url = parse_url($GLOBALS['base_url']);
    if ($kill) {
      $uids = db_select('users', 'u')
        ->fields('u', array('uid'))
        ->condition('uid', 1, '>')
        ->execute()
        ->fetchAllAssoc('uid');
      user_delete_multiple(array_keys($uids));
      $this->setMessage(\Drupal::translation()->formatPlural(count($uids), '1 user deleted', '@count users deleted.'));
    }

    if ($num > 0) {
      $names = array();
      while (count($names) < $num) {
        //@todo add suport for devel_generate_word(mt_rand(6, 12)) in a class method
        $name = $this->generateWord(mt_rand(6, 12));
        $names[$name] = '';
      }

      if (empty($roles)) {
        $roles = array(DRUPAL_AUTHENTICATED_RID);
      }
      foreach ($names as $name => $value) {
        $edit = array(
          'uid'     => NULL,
          'name'    => $name,
          'pass'    => $pass,
          'mail'    => $name . '@example.com',
          'status'  => 1,
          'created' => REQUEST_TIME - mt_rand(0, $age),
          'roles' => array_combine($roles, $roles),
          'devel_generate' => TRUE // A flag to let hook_user_* know that this is a generated user.
        );
        $account = entity_create('user', $edit);

        // Populate all core fields on behalf of field.module
        DevelGenerateFieldBase::generateFields($account, 'user', $account->bundle());
        $account->save();
      }
    }
    $this->setMessage(t('!num_users created.', array('!num_users' => format_plural($num, '1 user', '@count users'))));
  }

  public function validateDrushParams($args) {
    $values = array(
      'num' => array_shift($args),
      'roles' => drush_get_option('roles') ? explode(',', drush_get_option('roles')) : array(),
      'kill' => drush_get_option('kill'),
      'pass' => drush_get_option('pass', NULL),
      'time_range' => 0,
    );
    return $values;
  }

}
