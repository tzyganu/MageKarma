<?php
/**
 * @file
 * Implements tests for devel_generate module.
 */

namespace Drupal\devel_generate\Tests;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\simpletest\WebTestBase;
use Drupal\Core\Language\Language;

/**
 * Tests the logic to generate data.
 *
 * @group devel_generate
 */
class DevelGenerateTest extends WebTestBase {

  protected $vocabulary;

  protected $admin_user;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('menu_ui', 'node', 'comment', 'taxonomy', 'devel_generate');

  /**
   * Prepares the testing environment
   */
  public function setUp() {
    parent::setUp();

    $this->admin_user = $this->drupalCreateUser(array('administer devel_generate'));

    // Creating a vocabulary to associate taxonomy terms generated.
    $this->vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      'vid' => drupal_strtolower($this->randomName()),
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'weight' => mt_rand(0, 10),
    ));
    $this->vocabulary->save();

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic Page'));
      $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    }

    // Copied from /core/modules/taxonomy/src/Tests/TermTest.php::setup()
    $field_name = 'taxonomy_' . $this->vocabulary->id();
    entity_create('field_storage_config', array(
      'name' => $field_name,
      'entity_type' => 'node',
      'type' => 'taxonomy_term_reference',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => array(
        'allowed_values' => array(
          array(
            'vocabulary' => $this->vocabulary->id(),
            'parent' => 0,
          ),
        ),
      ),
    ))->save();

    $this->instance = entity_create('field_instance_config', array(
      'field_name' => $field_name,
      'bundle' => 'article',
      'entity_type' => 'node',
    ));
    $this->instance->save();
    entity_get_form_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => 'options_select',
      ))
      ->save();
    entity_get_display('node', 'article', 'default')
      ->setComponent($field_name, array(
        'type' => 'taxonomy_term_reference_link',
      ))
      ->save();

  }

  /**
   * Tests generate commands
   */
  public function testDevelGenerate() {

    $this->drupalLogin($this->admin_user);

    //Creating users.
    $edit = array(
      'num' => 4,
    );
    $this->drupalPostForm('admin/config/development/generate/user', $edit, t('Generate'));
    $this->assertText(t('4 users created.'));
    $this->assertText(t('Generate process complete.'));

    // Creating content.
    // First we create a node in order to test the Delete content checkbox.
    $this->drupalCreateNode(array('type' => 'article'));

    $edit = array(
      'num' => 4,
      'kill' => TRUE,
      'node_types[article]' => TRUE,
      'time_range' => 604800,
      'max_comments' => 3,
      'title_length' => 4,
    );
    $this->drupalPostForm('admin/config/development/generate/content', $edit, t('Generate'));
    $this->assertText(t('Deleted 1 nodes.'));
    $this->assertText(t('Finished creating 4 nodes'));
    $this->assertText(t('Generate process complete.'));

    //Creating terms.
    $edit = array(
      'vids[]' => $this->vocabulary->vid,
      'num' => 5,
      'title_length' => 12,
    );
    $this->drupalPostForm('admin/config/development/generate/term', $edit, t('Generate'));
    $this->assertText(t('Created the following new terms: '));
    $this->assertText(t('Generate process complete.'));

    //Creating vocabularies.
    $edit = array(
      'num' => 5,
      'title_length' => 12,
      'kill' => TRUE,
    );
    $this->drupalPostForm('admin/config/development/generate/vocabs', $edit, t('Generate'));
    $this->assertText(t('Created the following new vocabularies: '));
    $this->assertText(t('Generate process complete.'));

    //Creating menus.
    $edit = array(
      'num_menus' => 5,
      'num_links' => 7,
      'title_length' => 12,
      'link_types[node]' => 1,
      'link_types[front]' => 1,
      'link_types[external]' => 1,
      'max_depth' => 4,
      'max_width' => 6,
      'kill' => 1,
    );
    $this->drupalPostForm('admin/config/development/generate/menu', $edit, t('Generate'));
    $this->assertText(t('Created the following new menus: '));
    $this->assertText(t('Created 7 new menu links'));
    $this->assertText(t('Generate process complete.'));
  }

}
