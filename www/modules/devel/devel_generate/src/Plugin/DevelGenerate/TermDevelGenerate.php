<?php

/**
 * @file
 * Contains \Drupal\devel_generate\Plugin\DevelGenerate\TermDevelGenerate.
 */

namespace Drupal\devel_generate\Plugin\DevelGenerate;

use Drupal\devel_generate\DevelGenerateBase;
use Drupal\Core\Language\Language;
use Drupal\devel_generate\DevelGenerateFieldBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a TermDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "term",
 *   label = @Translation("terms"),
 *   description = @Translation("Generate a given number of terms. Optionally delete current terms."),
 *   url = "term",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 10,
 *     "title_length" = 12,
 *     "kill" = FALSE,
 *   }
 * )
 */
class TermDevelGenerate extends DevelGenerateBase {

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = array();
    foreach (entity_load_multiple('taxonomy_vocabulary') as $vid => $vocab) {
      $options[$vid] = $vocab->vid;
    }
    $form['vids'] = array(
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => t('Vocabularies'),
      '#required' => TRUE,
      '#default_value' => 'tags',
      '#options' => $options,
      '#description' => t('Restrict terms to these vocabularies.'),
    );
    $form['num'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of terms?'),
      '#default_value' => $this->getSetting('num'),
      '#size' => 10,
    );
    $form['title_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum number of characters in term names'),
      '#default_value' => $this->getSetting('title_length'),
      '#size' => 10,
    );
    $form['kill'] = array(
      '#type' => 'checkbox',
      '#title' => t('Delete existing terms in specified vocabularies before generating new terms.'),
      '#default_value' => $this->getSetting('kill'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function generateElements(array $values) {

    if ($values['kill']) {
      foreach ($values['vids'] as $vid) {
        $this->deleteVocabularyTerms($vid);
      }
      $this->setMessage(t('Deleted existing terms.'));
    }
    $vocabs = entity_load_multiple('taxonomy_vocabulary', $values['vids']);
    $new_terms = $this->generateTerms($values['num'], $vocabs, $values['title_length']);
    if (!empty($new_terms)) {
      $this->setMessage(t('Created the following new terms: !terms', array('!terms' => implode(', ', $new_terms))));
    }
  }

  /**
   * Deletes all terms of a vocabulary.
   *
   * @param $vid
   *   int a vocabulary vid.
   */
  protected function deleteVocabularyTerms($vid) {
    $tids = array();
    foreach (taxonomy_get_tree($vid) as $term) {
      $tids[] = $term->tid;
    }
    entity_delete_multiple('taxonomy_term', $tids);;
  }

  /**
   * Generates taxonomy terms for a list of given vocabularies.
   *
   * @param $records
   *   int number of terms to create in total.
   * @param $vocabs
   *   array list of vocabs to populate.
   * @param $maxlength
   *   int maximum length per term.
   * @return
   *   array the list of names of the created terms.
   */
  function generateTerms($records, $vocabs, $maxlength = 12) {
    $terms = array();

    // Insert new data:
    $max = db_query('SELECT MAX(tid) FROM {taxonomy_term_data}')->fetchField();
    $start = time();
    for ($i = 1; $i <= $records; $i++) {
      $values = array();
      switch ($i % 2) {
        case 1:
          // Set vid and vocabulary_machine_name properties.
          $vocab = $vocabs[array_rand($vocabs)];
          $values['vid'] = $vocab->vid;
          $values['vocabulary_machine_name'] = $vocab->vid;
          $values['parent'] = array(0);
          break;
        default:
          while (TRUE) {
            // Keep trying to find a random parent.
            $candidate = mt_rand(1, $max);
            $query = db_select('taxonomy_term_data', 't');
            $parent = $query
              ->fields('t', array('tid', 'vid'))
              ->condition('t.vid', array_keys($vocabs), 'IN')
              ->condition('t.tid', $candidate, '>=')
              ->range(0,1)
              ->execute()
              ->fetchAssoc();
            if ($parent['tid']) {
              break;
            }
          }
          $values['parent'] = array($parent['tid']);
          // Slight speedup due to this property being set.
          $values['vocabulary_machine_name'] = $parent['vid'];
          $values['vid'] = $parent['vid'];
          break;
      }

      $values['name'] = $this->generateWord(mt_rand(2, $maxlength));
      $values['description'] = "description of " . $values['name'];
      $values['format'] = filter_fallback_format();
      $values['weight'] = mt_rand(0, 10);
      $values['langcode'] = Language::LANGCODE_NOT_SPECIFIED;
      $term = entity_create('taxonomy_term', $values);

      // Populate all core fields on behalf of field.module
      DevelGenerateFieldBase::generateFields($term, 'taxonomy_term', $values['vocabulary_machine_name']);

      if ($status = $term->save()) {
        $max += 1;
        if (function_exists('drush_log')) {

          $feedback = drush_get_option('feedback', 1000);
          if ($i % $feedback == 0) {
            $now = time();
            drush_log(dt('Completed !feedback terms (!rate terms/min)', array('!feedback' => $feedback, '!rate' => $feedback*60 / ($now-$start) )), 'ok');
            $start = $now;
          }
        }

        // Limit memory usage. Only report first 20 created terms.
        if ($i < 20) {
          $terms[] = $term->name->value;
        }

        unset($term);
      }
    }
    return $terms;
  }

  public function validateDrushParams($args) {

    $vname = array_shift($args);
    $values = array(
      'num' => array_shift($args),
      'kill' => drush_get_option('kill'),
      'title_length' => 12,
    );
    // Try to convert machine name to a vocab ID
    if (!$vocab = entity_load('taxonomy_vocabulary', $vname)) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid vocabulary name: !name', array('!name' => $vname)));
    }
    if ($this->isNumber($values['num']) == FALSE) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid number of terms: !num', array('!num' => $values['num'])));
    }

    $values['vids'] = array($vocab->vid);

    return $values;
 }

}
