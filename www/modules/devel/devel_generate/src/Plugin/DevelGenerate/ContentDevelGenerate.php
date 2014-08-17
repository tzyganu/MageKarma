<?php

/**
 * @file
 * Contains \Drupal\devel_generate\Plugin\DevelGenerate\ContentDevelGenerate.
 */

namespace Drupal\devel_generate\Plugin\DevelGenerate;

use Drupal\Component\Utility\String;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\comment\CommentManagerInterface;
use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\devel_generate\DevelGenerateFieldBase;
use Drupal\node\Entity\NodeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a ContentDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "content",
 *   label = @Translation("content"),
 *   description = @Translation("Generate a given number of content. Optionally delete current content."),
 *   url = "content",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 50,
 *     "kill" = FALSE,
 *     "max_comments" = 0,
 *     "title_length" = 4
 *   }
 * )
 */
class ContentDevelGenerate extends DevelGenerateBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The comment manager service.
   *
   * @var \Drupal\comment\CommentManagerInterface
   */
  protected $commentManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\comment\CommentManagerInterface $comment_manager
   *   The comment manager service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, CommentManagerInterface $comment_manager = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->commentManager = $comment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('module_handler'),
      $container->has('comment.manager') ? $container->get('comment.manager') : NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = array();

    $types = NodeType::loadMultiple();
    $comment_fields = ($this->commentManager) ? $this->commentManager->getFields('node') : array();
    $map = array(t('Hidden'), t('Closed'), t('Open'));
    foreach ($types as $type) {
      $options[$type->type] = array(
        'type' => array('#markup' => t($type->name)),
      );
      if ($this->commentManager) {
        $fields = array();
        foreach ($comment_fields as $field_name => $info) {
          // Find all comment fields for the bundle.
          if (in_array($type->type, $info['bundles'])) {
            $instance = FieldInstanceConfig::loadByName('node', $type->type, $field_name);
            $default_mode = reset($instance->default_value);
            $fields[] = String::format('@field: !state', array(
              '@field' => $instance->label(),
              '!state' => $map[$default_mode['status']],
            ));
          }
        }
        // @todo Refactor display of comment fields.
        if (!empty($fields)) {
          $options[$type->type]['comments'] = array('data' => array(
            '#theme' => 'item_list',
            '#items' => $fields,
          ));
        }
        else {
          $options[$type->type]['comments'] = t('No comment fields');
        }
      }
    }

    if (empty($options)) {
      $this->setMessage(t('You do not have any content types that can be generated. <a href="@create-type">Go create a new content type</a> already!</a>', array('@create-type' => url('admin/structure/types/add'))), 'error', FALSE);
      return;
    }

    $header = array(
      'type' => t('Content type'),
    );
    if ($this->commentManager) {
      $header['comments'] = array(
        'data' => t('Comments'),
        'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
      );
    }

    $form['node_types'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $options,
    );

    $form['kill'] = array(
      '#type' => 'checkbox',
      '#title' => t('<strong>Delete all content</strong> in these content types before generating new content.'),
      '#default_value' => $this->getSetting('kill'),
    );
    $form['num'] = array(
      '#type' => 'textfield',
      '#title' => t('How many nodes would you like to generate?'),
      '#default_value' => $this->getSetting('num'),
      '#size' => 10,
    );

    $options = array(1 => t('Now'));
    foreach (array(3600, 86400, 604800, 2592000, 31536000) as $interval) {
      $options[$interval] = \Drupal::service('date.formatter')->formatInterval($interval, 1) . ' ' . t('ago');
    }
    $form['time_range'] = array(
      '#type' => 'select',
      '#title' => t('How far back in time should the nodes be dated?'),
      '#description' => t('Node creation dates will be distributed randomly from the current time, back to the selected time.'),
      '#options' => $options,
      '#default_value' => 604800,
    );

    $form['max_comments'] = array(
      '#type' => $this->moduleHandler->moduleExists('comment') ? 'textfield' : 'value',
      '#title' => t('Maximum number of comments per node.'),
      '#description' => t('You must also enable comments for the content types you are generating. Note that some nodes will randomly receive zero comments. Some will receive the max.'),
      '#default_value' => $this->getSetting('max_comments'),
      '#size' => 3,
      '#access' => $this->moduleHandler->moduleExists('comment'),
    );
    $form['title_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum number of words in titles'),
      '#default_value' => $this->getSetting('title_length'),
      '#size' => 10,
    );
    $form['add_alias'] = array(
      '#type' => 'checkbox',
      '#disabled' => !$this->moduleHandler->moduleExists('path'),
      '#description' => t('Requires path.module'),
      '#title' => t('Add an url alias for each node.'),
      '#default_value' => FALSE,
    );
    $form['add_statistics'] = array(
      '#type' => 'checkbox',
      '#title' => t('Add statistics for each node (node_counter table).'),
      '#default_value' => TRUE,
      '#access' => $this->moduleHandler->moduleExists('statistics'),
    );

    $options = array(Language::LANGCODE_NOT_SPECIFIED => t('Language neutral'));
    if ($this->moduleHandler->moduleExists('locale')) {
      $languages = language_list();
      foreach ($languages as $langcode => $language) {
        $options[$langcode] = $language->name;
      }
    }
    $form['add_language'] = array(
      '#type' => 'select',
      '#title' => t('Set language on nodes'),
      '#multiple' => TRUE,
      '#disabled' => !$this->moduleHandler->moduleExists('locale'),
      '#description' => t('Requires locale.module'),
      '#options' => $options,
      '#default_value' => array(Language::LANGCODE_NOT_SPECIFIED),
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Generate'),
      '#tableselect' => TRUE,
    );
    $form['#redirect'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function generateElements(array $values) {
    if ($values['num'] <= 50 && $values['max_comments'] <= 10) {
      $this->generateContent($values);
    }
    else {
      $this->generateBatchContent($values);
    }
  }

  /**
   * Method responsible for creating content when
   * the number of elements is less than 50.
   */
  private function generateContent($values) {
    $values['node_types'] = array_filter($values['node_types']);
    if (!empty($values['kill']) && $values['node_types']) {
      $this->contentKill($values);
    }

    if (!empty($values['node_types'])) {
      // Generate nodes.
      $this->develGenerateContentPreNode($values);
      $start = time();
      for ($i = 1; $i <= $values['num']; $i++) {
        $this->develGenerateContentAddNode($values);
        if (function_exists('drush_log') && $i % drush_get_option('feedback', 1000) == 0) {
          $now = time();
          drush_log(dt('Completed !feedback nodes (!rate nodes/min)', array('!feedback' => drush_get_option('feedback', 1000), '!rate' => (drush_get_option('feedback', 1000)*60)/($now-$start))), 'ok');
          $start = $now;
        }
      }
    }
    $this->setMessage(\Drupal::translation()->formatPlural($values['num'], '1 node created.', 'Finished creating @count nodes'));
  }

  /**
   * Method responsible for creating content when
   * the number of elements is greater than 50.
   */
  private function generateBatchContent($values) {
    // Setup the batch operations and save the variables.
    $operations[] = array('devel_generate_operation', array($this, 'batchContentPreNode', $values));

    // add the kill operation
    if ($values['kill']) {
      $operations[] = array('devel_generate_operation', array($this, 'batchContentKill', $values));
    }

    // add the operations to create the nodes
    for ($num = 0; $num < $values['num']; $num ++) {
      $operations[] = array('devel_generate_operation', array($this, 'batchContentAddNode', $values));
    }

    // start the batch
    $batch = array(
      'title' => t('Generating Content'),
      'operations' => $operations,
      'finished' => 'devel_generate_batch_finished',
      'file' => drupal_get_path('module', 'devel_generate') . '/devel_generate.batch.inc',
    );
    batch_set($batch);
  }

  public function batchContentPreNode($vars, &$context) {
    $context['results'] = $vars;
    $context['results']['num'] = 0;
    $this->develGenerateContentPreNode($context['results']);
  }

  public function batchContentAddNode($vars, &$context) {
    $this->develGenerateContentAddNode($context['results']);
    $context['results']['num']++;
  }

  public function batchContentKill($vars, &$context) {
    $this->contentKill($context['results']);
  }

  /**
   * {@inheritdoc}
   */
  public function validateDrushParams($args) {
    $add_language = drush_get_option('languages');
    if (!empty($add_language)) {
      $add_language = explode(',', str_replace(' ', '', $add_language));
      // Intersect with the enabled languages to make sure the language args
      // passed are actually enabled.
      $values['values']['add_language'] = array_intersect($add_language, array_keys(locale_language_list()));
    }

    $values['kill'] = drush_get_option('kill');
    $values['title_length'] = 6;
    $values['num'] = array_shift($args);
    $values['max_comments'] = array_shift($args);
    $all_types = array_keys(node_type_get_names());
    $default_types = array_intersect(array('page', 'article'), $all_types);
    $selected_types = _convert_csv_to_array(drush_get_option('types', $default_types));

    if (empty($selected_types)) {
      return drush_set_error('DEVEL_GENERATE_NO_CONTENT_TYPES', dt('No content types available'));
    }

    $values['node_types'] = array_combine($selected_types, $selected_types);
    $node_types = array_filter($values['node_types']);

    if (!empty($values['kill']) && empty($node_types)) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Please provide content type (--types) in which you want to delete the content.'));
    }

    return $values;
  }

  protected function contentKill($values) {
    $results = db_select('node', 'n')
      ->fields('n', array('nid'))
      ->condition('type', $values['node_types'], 'IN')
      ->execute();
    foreach ($results as $result) {
      $nids[] = $result->nid;
    }

    if (!empty($nids)) {
      entity_delete_multiple('node', $nids);
      $this->setMessage(t('Deleted %count nodes.', array('%count' => count($nids))));
    }
  }

  /**
   * Return the same array passed as parameter
   * but with an array of uids for the key 'users'.
   */
  protected function develGenerateContentPreNode(&$results) {
    // Get user id.
    $users = $this->getUsers();
    $users = array_merge($users, array('0'));
    $results['users'] = $users;
  }

  /**
   * Create one node. Used by both batch and non-batch code branches.
   */
  protected function develGenerateContentAddNode(&$results) {
    if (!isset($results['time_range'])) {
      $results['time_range'] = 0;
    }
    $users = $results['users'];

    $node_type = array_rand(array_filter($results['node_types']));
    $uid = $users[array_rand($users)];

    $edit_node = array(
      'nid' => NULL,
      'type' => $node_type,
      'uid' => $uid,
      'revision' => mt_rand(0, 1),
      'status' => TRUE,
      'promote' => mt_rand(0, 1),
      'created' => REQUEST_TIME - mt_rand(0, $results['time_range']),
      'langcode' => $this->getLangcode($results),
    );

    // We should not use the random function if the value is not random
    if ($results['title_length'] < 2) {
      $edit_node['title'] = $this->createGreeking(1, TRUE);
    }
    else {
      $edit_node['title'] = $this->createGreeking(mt_rand(1, $results['title_length']), TRUE);
    }
    $node = entity_create('node', $edit_node);

    // A flag to let hook_node_insert() implementations know that this is a
    // generated node.
    $node->devel_generate = $results;

    // Populate all core fields on behalf of field.module
    DevelGenerateFieldBase::generateFields($node, 'node', $node->bundle());

    // See devel_generate_node_insert() for actions that happen before and after
    // this save.
    $node->save();
  }

  /**
   * Determine language based on $results.
   */
  protected function getLangcode($results) {
    if (isset($results['add_language'])) {
      $langcodes = $results['add_language'];
      $langcode = $langcodes[array_rand($langcodes)];
    }
    else {
      $langcode = language_default()->id;
    }
    return $langcode == 'en' ? Language::LANGCODE_NOT_SPECIFIED : $langcode;
  }

  /**
   * Retrive 50 uids from the database.
   */
  protected function getUsers() {
    $users = array();
    $result = db_query_range("SELECT uid FROM {users}", 0, 50);
    foreach ($result as $record) {
      $users[] = $record->uid;
    }
    return $users;
  }

}
